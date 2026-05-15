<?php

namespace App\Livewire\Shop\Sales;

use App\Livewire\Concerns\RequiresOpenSession;
use App\Models\ActivityLog;
use App\Models\Box;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Transporter;
use App\Services\Sales\SaleService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class WarehouseSale extends Component
{
    use RequiresOpenSession;
    public int $shopId;
    public string $shopName = '';
    public int $warehouseId;
    public string $warehouseName = '';

    public string $tab = 'sale'; // 'sale' | 'history'
    public array $cart = [];
    public string $step = 'cart'; // 'cart' | 'checkout' | 'done'

    // Add-to-cart staging
    public ?int $stagingProductId = null;
    public int $stagingBoxes = 1;
    public int $stagingBoxPrice = 0;       // editable box price
    public bool $stagingPriceModified = false;
    public string $stagingPriceReason = '';
    public bool $showAddModal = false;

    // Fulfillment
    public string $fulfillmentMethod = 'transporter';
    public ?int $fulfillmentTransporterId = null;
    public string $fulfillmentNotes = '';

    // Payment
    public int $payAmt_cash = 0;
    public int $payAmt_mobile_money = 0;
    public int $payAmt_card = 0;
    public int $payAmt_bank_transfer = 0;
    public int $payAmt_credit = 0;

    // Customer — selected state
    public ?int   $customerId                    = null;
    public string $customerName                  = '';
    public string $customerPhone                 = '';
    public int    $customerOutstandingBalance     = 0;
    // Customer — search/create UI state
    public string $customerSearch               = '';
    public array  $customerResults              = [];
    public bool   $showCustomerSearch           = false;
    public bool   $showNewCustomerForm          = false;
    public string $newCustomerName              = '';
    public string $newCustomerPhone             = '';
    public string $newCustomerEmail             = '';

    // Credit warning state
    public bool   $creditWarningVisible = false;
    public string $creditWarningMessage = '';

    // Settings
    public bool $settingAllowPriceOverride     = true;
    public int  $settingPriceOverrideThreshold = 20;
    public bool $settingAllowCardPayment       = false;
    public bool $settingAllowBankTransfer      = false;
    public bool $settingAllowCreditSales       = true;
    public bool $settingCreditRequiresCustomer = true;
    public int  $settingMaxCreditPerCustomer   = 0;

    // Result
    public ?Sale $completedSale = null;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->isOwner()) {
            abort(403, 'Warehouse sales are managed by shop managers.');
        }

        $shop = Shop::find($user->location_id);

        if (!$shop) {
            abort(403, 'No shop assigned.');
        }

        if (!$shop->default_warehouse_id) {
            abort(422, 'This shop has no default warehouse configured.');
        }

        $this->shopId        = $shop->id;
        $this->shopName      = $shop->name;
        $this->warehouseId   = $shop->default_warehouse_id;
        $this->warehouseName = $shop->defaultWarehouse->name ?? '';

        $settings = app(SettingsService::class);
        $this->settingAllowPriceOverride     = $settings->allowPriceOverride();
        $this->settingPriceOverrideThreshold = $settings->priceOverrideThreshold();
        $this->settingAllowCardPayment       = $settings->allowCardPayment();
        $this->settingAllowBankTransfer      = $settings->allowBankTransferPayment();
        $this->settingAllowCreditSales       = $settings->allowCreditSales();
        $this->settingCreditRequiresCustomer = $settings->creditRequiresCustomer();
        $this->settingMaxCreditPerCustomer   = $settings->maxCreditPerCustomer();

        $this->checkSession($shop->id);
    }

    public function getWarehouseStockProperty(): \Illuminate\Support\Collection
    {
        return DB::table('boxes')
            ->join('products', 'products.id', '=', 'boxes.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('boxes.location_type', 'warehouse')
            ->where('boxes.location_id', $this->warehouseId)
            ->whereIn('boxes.status', ['full', 'partial'])
            ->where('boxes.items_remaining', '>', 0)
            ->where('products.is_active', true)
            ->groupBy(
                'products.id', 'products.name', 'products.sku',
                'products.items_per_box', 'products.selling_price',
                'products.box_selling_price', 'categories.name'
            )
            ->selectRaw('
                products.id,
                products.name,
                products.sku,
                products.items_per_box,
                products.selling_price,
                products.box_selling_price,
                categories.name as category_name,
                COUNT(boxes.id) as box_count,
                SUM(boxes.items_remaining) as total_items
            ')
            ->orderBy('products.name')
            ->get();
    }

    public function getTransportersProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return Transporter::active()->orderBy('name')->get();
    }

    public function getSaleHistoryProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return Sale::warehouseDirect()
            ->forShop($this->shopId)
            ->with(['items.product', 'fulfillmentTransporter'])
            ->latest('sale_date')
            ->limit(50)
            ->get();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function getCartTotalProperty(): int
    {
        return array_sum(array_column($this->cart, 'line_total'));
    }

    public function getPaymentTotalProperty(): int
    {
        return $this->payAmt_cash
            + $this->payAmt_mobile_money
            + $this->payAmt_card
            + $this->payAmt_bank_transfer
            + $this->payAmt_credit;
    }

    public function openAddModal(int $productId): void
    {
        $this->stagingProductId = $productId;
        $this->stagingBoxes     = 1;

        $stock = $this->warehouseStock->firstWhere('id', $productId);
        $this->stagingBoxPrice      = $stock
            ? (int) ($stock->box_selling_price ?? ($stock->selling_price * $stock->items_per_box))
            : 0;
        $this->stagingPriceModified = false;
        $this->stagingPriceReason   = '';

        $this->showAddModal = true;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal         = false;
        $this->stagingProductId     = null;
        $this->stagingBoxes         = 1;
        $this->stagingBoxPrice      = 0;
        $this->stagingPriceModified = false;
        $this->stagingPriceReason   = '';
    }

    public function updatedStagingBoxPrice(): void
    {
        if (!$this->stagingProductId) {
            return;
        }

        $stock = $this->warehouseStock->firstWhere('id', $this->stagingProductId);
        if (!$stock) {
            return;
        }

        $originalPrice = (int) ($stock->box_selling_price ?? ($stock->selling_price * $stock->items_per_box));
        $this->stagingPriceModified = ((int) $this->stagingBoxPrice !== $originalPrice);

        if (!$this->stagingPriceModified) {
            $this->stagingPriceReason = '';
        }
    }

    public function incrementStagingBoxes(): void
    {
        $stock = $this->warehouseStock->firstWhere('id', $this->stagingProductId);
        $max   = $stock ? (int) $stock->box_count : 999;
        $this->stagingBoxes = min($max, $this->stagingBoxes + 1);
    }

    public function decrementStagingBoxes(): void
    {
        $this->stagingBoxes = max(1, $this->stagingBoxes - 1);
    }

    public function confirmAddToCart(): void
    {
        if (!$this->stagingProductId || $this->stagingBoxes < 1) {
            return;
        }

        // Check warehouse has enough boxes
        $stock = $this->warehouseStock->firstWhere('id', $this->stagingProductId);
        if (!$stock || $stock->box_count < $this->stagingBoxes) {
            session()->flash('error', 'Not enough boxes available at warehouse.');
            $this->closeAddModal();
            return;
        }

        $boxPrice = (int) $this->stagingBoxPrice;

        if ($boxPrice <= 0) {
            session()->flash('error', 'Box price must be greater than zero.');
            return;
        }

        if ($this->stagingPriceModified && empty($this->stagingPriceReason)) {
            session()->flash('error', 'Please provide a reason for the price change.');
            return;
        }

        // Check if already in cart — update quantity
        foreach ($this->cart as $i => $item) {
            if ($item['product_id'] === $this->stagingProductId) {
                $newBoxes = $item['boxes'] + $this->stagingBoxes;
                if ($newBoxes > $stock->box_count) {
                    session()->flash('error', 'Cart quantity exceeds available warehouse boxes.');
                    $this->closeAddModal();
                    return;
                }
                $this->cart[$i]['boxes']                     = $newBoxes;
                $this->cart[$i]['box_price']                 = $boxPrice;
                $this->cart[$i]['line_total']                = $newBoxes * $boxPrice;
                $this->cart[$i]['price_modified']            = $this->stagingPriceModified;
                $this->cart[$i]['price_modification_reason'] = $this->stagingPriceReason ?: null;
                $this->closeAddModal();
                return;
            }
        }

        $this->cart[] = [
            'product_id'                 => (int) $stock->id,
            'product_name'               => $stock->name,
            'sku'                        => $stock->sku,
            'items_per_box'              => (int) $stock->items_per_box,
            'boxes'                      => $this->stagingBoxes,
            'box_price'                  => $boxPrice,
            'line_total'                 => $this->stagingBoxes * $boxPrice,
            'price_modified'             => $this->stagingPriceModified,
            'price_modification_reason'  => $this->stagingPriceReason ?: null,
        ];

        $this->closeAddModal();
    }

    public function removeFromCart(int $index): void
    {
        array_splice($this->cart, $index, 1);
        $this->cart = array_values($this->cart);
    }

    public function goToCheckout(): void
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Add at least one product to the cart.');
            return;
        }
        $this->step = 'checkout';
    }

    public function backToCart(): void
    {
        $this->step = 'cart';
    }

    public function openCustomerSearch(): void
    {
        $this->showCustomerSearch = true;
        if (empty($this->customerResults)) {
            $this->customerResults = \App\Models\Customer::orderBy('name')
                ->limit(20)
                ->get(['id', 'name', 'phone', 'outstanding_balance'])
                ->toArray();
        }
    }

    public function updatedCustomerSearch(): void
    {
        $q = trim($this->customerSearch);
        if (strlen($q) < 1) {
            $this->customerResults = \App\Models\Customer::orderBy('name')
                ->limit(20)
                ->get(['id', 'name', 'phone', 'outstanding_balance'])
                ->toArray();
        } else {
            $this->customerResults = \App\Models\Customer::search($q)->toArray();
        }
        $this->showCustomerSearch = true;
    }

    public function selectCustomer(int $id): void
    {
        $customer = \App\Models\Customer::find($id);
        if (!$customer) {
            return;
        }
        $this->customerId                = $customer->id;
        $this->customerName              = $customer->name;
        $this->customerPhone             = $customer->phone;
        $this->customerOutstandingBalance = $customer->outstanding_balance;
        $this->customerSearch            = '';
        $this->customerResults           = [];
        $this->showCustomerSearch        = false;
        $this->showNewCustomerForm       = false;

        if ($this->payAmt_credit > 0) {
            $this->evaluateCreditWarning();
        }
    }

    public function clearCustomer(): void
    {
        $this->customerId                = null;
        $this->customerName              = '';
        $this->customerPhone             = '';
        $this->customerOutstandingBalance = 0;
        $this->customerSearch            = '';
        $this->customerResults           = [];
        $this->showCustomerSearch        = false;
        $this->showNewCustomerForm       = false;
        if ($this->payAmt_credit > 0) {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
        }
    }

    public function showCreateCustomerForm(): void
    {
        $this->showNewCustomerForm  = true;
        $this->showCustomerSearch   = false;
        $this->customerResults      = [];
        if (preg_match('/^\d+$/', trim($this->customerSearch))) {
            $this->newCustomerPhone = trim($this->customerSearch);
        }
    }

    public function cancelNewCustomer(): void
    {
        $this->showNewCustomerForm = false;
        $this->newCustomerName     = '';
        $this->newCustomerPhone    = '';
        $this->newCustomerEmail    = '';
    }

    public function saveNewCustomer(): void
    {
        $this->validate([
            'newCustomerName'  => 'required|string|min:2|max:100',
            'newCustomerPhone' => 'required|string|min:10|max:20|unique:customers,phone',
            'newCustomerEmail' => 'nullable|email|max:100',
        ]);

        $customer = (new \App\Services\Sales\CustomerService())->create([
            'name'  => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail ?: null,
            'notes' => null,
        ], $this->shopId);

        $this->selectCustomer($customer->id);

        $this->newCustomerName  = '';
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
    }

    public function updatedPayAmtCredit(): void
    {
        if ($this->payAmt_credit <= 0) {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
            return;
        }

        if (!$this->settingAllowCreditSales) {
            $this->payAmt_credit = 0;
            $this->dispatch('notification', ['type' => 'error', 'message' => 'Credit sales are disabled by the owner']);
            return;
        }

        if ($this->settingCreditRequiresCustomer && !$this->customerId) {
            $this->payAmt_credit = 0;
            $this->dispatch('notification', ['type' => 'warning', 'message' => 'A registered customer must be selected before using credit']);
            return;
        }

        if ($this->settingMaxCreditPerCustomer > 0 && $this->customerId) {
            $customer = \App\Models\Customer::find($this->customerId);
            if ($customer) {
                $projected = $customer->outstanding_balance + $this->payAmt_credit;
                if ($projected > $this->settingMaxCreditPerCustomer) {
                    $remaining = max(0, $this->settingMaxCreditPerCustomer - $customer->outstanding_balance);
                    $this->payAmt_credit = $remaining;
                    $this->dispatch('notification', [
                        'type'    => 'warning',
                        'message' => 'Credit limit reached. Maximum remaining: ' . number_format($remaining) . ' RWF',
                    ]);
                }
            }
        }

        $this->evaluateCreditWarning();
    }

    private function evaluateCreditWarning(): void
    {
        if (!$this->customerId || $this->payAmt_credit <= 0) {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
            return;
        }

        $customer = \App\Models\Customer::find($this->customerId);
        if ($customer && $customer->outstanding_balance > 0) {
            $this->creditWarningVisible = true;
            $this->creditWarningMessage = 'Customer has outstanding credit balance of '
                . number_format($customer->outstanding_balance) . ' RWF';
        } else {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
        }
    }

    public function processPayment(int $cash, int $momo, int $card, int $bank, int $credit): void
    {
        $this->payAmt_cash          = $cash;
        $this->payAmt_mobile_money  = $momo;
        $this->payAmt_card          = $card;
        $this->payAmt_bank_transfer = $bank;
        $this->payAmt_credit        = $credit;
        $this->completeSale();
    }

    public function completeSale(): void
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        $total = $this->cartTotal;

        if ($this->paymentTotal < $total) {
            session()->flash('error', 'Payment does not cover the total.');
            return;
        }

        if ($this->fulfillmentMethod === 'transporter' && !$this->fulfillmentTransporterId) {
            session()->flash('error', 'Please select a transporter.');
            return;
        }

        // Belt-and-braces: enforce credit settings before committing
        if ($this->payAmt_credit > 0 && !$this->settingAllowCreditSales) {
            $this->payAmt_credit = 0;
        }
        if ($this->payAmt_credit > 0 && $this->settingCreditRequiresCustomer && !$this->customerId) {
            session()->flash('error', 'A registered customer must be selected to use credit.');
            return;
        }

        try {
            $payments = [];
            if ($this->payAmt_cash > 0)                                               $payments[] = ['method' => 'cash',          'amount' => $this->payAmt_cash,         'reference' => null];
            if ($this->payAmt_mobile_money > 0)                                        $payments[] = ['method' => 'mobile_money',  'amount' => $this->payAmt_mobile_money,  'reference' => null];
            if ($this->payAmt_card > 0 && $this->settingAllowCardPayment)              $payments[] = ['method' => 'card',          'amount' => $this->payAmt_card,          'reference' => null];
            if ($this->payAmt_bank_transfer > 0 && $this->settingAllowBankTransfer)    $payments[] = ['method' => 'bank_transfer', 'amount' => $this->payAmt_bank_transfer, 'reference' => null];
            if ($this->payAmt_credit > 0)                                              $payments[] = ['method' => 'credit',        'amount' => $this->payAmt_credit,        'reference' => null];

            if (empty($payments)) {
                $payments[] = ['method' => 'cash', 'amount' => $total, 'reference' => null];
            }

            $items = array_map(fn ($item) => [
                'product_id'                 => $item['product_id'],
                'boxes'                      => $item['boxes'],
                'price'                      => $item['box_price'],
                'price_modified'             => $item['price_modified'] ?? false,
                'price_modification_reason'  => $item['price_modification_reason'] ?? null,
            ], $this->cart);

            $service = app(SaleService::class);
            $sale = $service->createWarehouseSale([
                'shop_id'                    => $this->shopId,
                'source_warehouse_id'        => $this->warehouseId,
                'fulfillment_method'         => $this->fulfillmentMethod,
                'fulfillment_transporter_id' => $this->fulfillmentMethod === 'transporter'
                                                    ? $this->fulfillmentTransporterId
                                                    : null,
                'fulfillment_notes'          => $this->fulfillmentNotes ?: null,
                'payments'                   => $payments,
                'customer_id'                => $this->customerId ?: null,
                'customer_name'              => $this->customerName ?: null,
                'customer_phone'             => $this->customerPhone ?: null,
                'items'                      => $items,
            ]);

            $this->completedSale = $sale->load(['items.product', 'items.box', 'fulfillmentTransporter', 'shop']);
            $this->step = 'done';

        } catch (\Exception $e) {
            session()->flash('error', 'Sale failed: ' . $e->getMessage());
        }
    }

    public function newSale(): void
    {
        $this->reset([
            'cart', 'step', 'fulfillmentMethod', 'fulfillmentTransporterId',
            'fulfillmentNotes', 'payAmt_cash', 'payAmt_mobile_money',
            'payAmt_card', 'payAmt_bank_transfer', 'payAmt_credit',
            'customerId', 'customerName', 'customerPhone', 'customerOutstandingBalance',
            'customerSearch', 'customerResults', 'showCustomerSearch', 'showNewCustomerForm',
            'newCustomerName', 'newCustomerPhone', 'newCustomerEmail',
            'completedSale', 'stagingProductId', 'stagingBoxes', 'stagingBoxPrice',
            'stagingPriceModified', 'stagingPriceReason', 'showAddModal',
            'creditWarningVisible', 'creditWarningMessage',
        ]);
        $this->fulfillmentMethod = 'transporter';
        $this->step = 'cart';
    }

    public function render()
    {
        return view('livewire.shop.sales.warehouse-sale', [
            'warehouseStock' => $this->warehouseStock,
            'transporters'   => $this->transporters,
            'cartTotal'      => $this->cartTotal,
            'paymentTotal'   => $this->paymentTotal,
            'saleHistory'    => $this->tab === 'history' ? $this->saleHistory : collect(),
        ]);
    }
}
