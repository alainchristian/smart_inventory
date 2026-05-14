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

    // Customer
    public ?int $customerId = null;
    public string $customerName = '';
    public string $customerPhone = '';

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
        $this->showAddModal     = true;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal     = false;
        $this->stagingProductId = null;
        $this->stagingBoxes     = 1;
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

        $boxPrice = $stock->box_selling_price ?? ($stock->selling_price * $stock->items_per_box);

        // Check if already in cart — update quantity
        foreach ($this->cart as $i => $item) {
            if ($item['product_id'] === $this->stagingProductId) {
                $newBoxes = $item['boxes'] + $this->stagingBoxes;
                if ($newBoxes > $stock->box_count) {
                    session()->flash('error', 'Cart quantity exceeds available warehouse boxes.');
                    $this->closeAddModal();
                    return;
                }
                $this->cart[$i]['boxes']      = $newBoxes;
                $this->cart[$i]['line_total'] = $newBoxes * $boxPrice;
                $this->closeAddModal();
                return;
            }
        }

        $this->cart[] = [
            'product_id'    => (int) $stock->id,
            'product_name'  => $stock->name,
            'sku'           => $stock->sku,
            'items_per_box' => (int) $stock->items_per_box,
            'boxes'         => $this->stagingBoxes,
            'box_price'     => $boxPrice,
            'line_total'    => $this->stagingBoxes * $boxPrice,
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

        try {
            $payments = [];
            if ($this->payAmt_cash > 0)         $payments[] = ['method' => 'cash',           'amount' => $this->payAmt_cash,         'reference' => null];
            if ($this->payAmt_mobile_money > 0)  $payments[] = ['method' => 'mobile_money',   'amount' => $this->payAmt_mobile_money,  'reference' => null];
            if ($this->payAmt_card > 0)          $payments[] = ['method' => 'card',            'amount' => $this->payAmt_card,          'reference' => null];
            if ($this->payAmt_bank_transfer > 0) $payments[] = ['method' => 'bank_transfer',  'amount' => $this->payAmt_bank_transfer, 'reference' => null];
            if ($this->payAmt_credit > 0)        $payments[] = ['method' => 'credit',          'amount' => $this->payAmt_credit,        'reference' => null];

            if (empty($payments)) {
                $payments[] = ['method' => 'cash', 'amount' => $total, 'reference' => null];
            }

            $items = array_map(fn ($item) => [
                'product_id' => $item['product_id'],
                'boxes'      => $item['boxes'],
                'price'      => $item['box_price'],
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
            'customerId', 'customerName', 'customerPhone', 'completedSale',
            'stagingProductId', 'stagingBoxes', 'showAddModal',
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
