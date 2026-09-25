<?php

namespace App\Livewire\Shop\Sales;

use App\Enums\BoxStatus;
use App\Enums\SaleType;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Box;
use App\Models\HeldSale;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\Sale;
use App\Models\ScannerSession;
use App\Models\Shop;
use App\Models\Transporter;
use App\Services\Sales\SaleService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UnifiedPos extends Component
{
    use \App\Livewire\Concerns\RequiresOpenSession;

    // ── Shop & Warehouse context ──────────────────────────────────────────────
    public $shopId;
    public string $shopName    = '';
    public ?int $warehouseId   = null;
    public string $warehouseName = '';
    public bool $isOwner       = false;
    public array $availableShops = [];
    public bool $showShopSelectionModal = false;

    // ── Stock browser ─────────────────────────────────────────────────────────
    public string $searchQuery  = '';
    public string $stockFilter  = 'all';  // 'all' | 'shop' | 'warehouse'
    public array $shopStock     = [];
    public array $warehouseStock = [];
    public string $barcodeInput = '';
    public bool $scanningEnabled = true;

    // ── Cart ──────────────────────────────────────────────────────────────────
    public array $cart     = [];
    public int $cartTotal  = 0;

    // ── Staging modal ─────────────────────────────────────────────────────────
    public bool $showAddModal          = false;
    public ?array $stagingProduct      = null;
    public array $stagingStock         = [];
    public string $stagingMode         = 'box';   // 'box' | 'item' (warehouse: always 'box')
    public int $stagingQty             = 1;
    public int $stagingPrice           = 0;
    public bool $stagingPriceModified  = false;
    public string $stagingPriceReason  = '';
    public ?int $stagingCartIndex      = null;

    // ── Fulfillment (checkout — only when warehouse items in cart) ────────────
    public string $fulfillmentMethod       = 'transporter';
    public ?int $fulfillmentTransporterId  = null;
    public string $fulfillmentNotes        = '';
    public bool $showNewTransporterForm    = false;
    public string $newTransporterName      = '';
    public string $newTransporterPhone     = '';
    public string $newTransporterCompany   = '';
    public string $newTransporterVehicle   = '';

    // ── Checkout modal ────────────────────────────────────────────────────────
    public bool $showCheckoutModal = false;
    public string $notes = '';

    // ── Customer ──────────────────────────────────────────────────────────────
    public string $customerSearch               = '';
    public array $customerResults               = [];
    public bool $showCustomerSearch             = false;
    public ?int $selectedCustomerId             = null;
    public string $selectedCustomerName         = '';
    public string $selectedCustomerPhone        = '';
    public int $selectedCustomerOutstandingBalance = 0;
    public bool $showNewCustomerForm            = false;
    public string $newCustomerName              = '';
    public string $newCustomerPhone             = '';
    public string $newCustomerEmail             = '';
    public string $newCustomerNotes             = '';

    // ── Split payment ─────────────────────────────────────────────────────────
    public int $payAmt_cash          = 0;
    public int $payAmt_card          = 0;
    public int $payAmt_mobile_money  = 0;
    public int $payAmt_bank_transfer = 0;
    public int $payAmt_credit        = 0;
    public string $payRef_card          = '';
    public string $payRef_mobile_money  = '';
    public string $payRef_bank_transfer = '';
    public bool $creditWarningVisible   = false;
    public string $creditWarningMessage = '';

    // ── Settings ──────────────────────────────────────────────────────────────
    public bool $settingAllowIndividualSales    = true;
    public array $settingIndividualCategoryIds  = [];
    public bool $settingAllowPriceOverride      = true;
    public int $settingPriceOverrideThreshold   = 20;
    public bool $settingAllowCreditSales        = true;
    public bool $settingCreditRequiresCustomer  = true;
    public int $settingMaxCreditPerCustomer     = 0;
    public bool $settingAllowCardPayment        = false;
    public bool $settingAllowBankTransfer       = false;

    // ── Held sales ────────────────────────────────────────────────────────────
    public array $heldSales       = [];
    public bool $showHeldPanel    = false;
    public ?int $resumingFromHeld = null;

    // ── Receipt ───────────────────────────────────────────────────────────────
    public bool $showReceiptModal = false;
    public $completedSale         = null;

    // ── Phone scanner ─────────────────────────────────────────────────────────
    public ?ScannerSession $scannerSession = null;
    public bool $showScannerPanel          = false;
    public string $lastProcessedScan       = '';

    protected $listeners = [
        'barcode-scanned' => 'handleBarcodeScanned',
    ];

    // ── Computed properties ───────────────────────────────────────────────────

    public function getTotalAllocatedProperty(): int
    {
        return $this->payAmt_cash
            + $this->payAmt_card
            + $this->payAmt_mobile_money
            + $this->payAmt_bank_transfer
            + $this->payAmt_credit;
    }

    public function getRemainingBalanceProperty(): int
    {
        return max(0, $this->cartTotal - $this->totalAllocated);
    }

    public function getHasWarehouseItemsProperty(): bool
    {
        return collect($this->cart)->contains(fn ($i) => ($i['source'] ?? 'shop') === 'warehouse');
    }

    /** Merge + filter stock for the left panel.
     *  Tab filter controls idle browse; a non-empty search always spans both sources. */
    public function getDisplayedStockProperty(): array
    {
        $q          = mb_strtolower(trim($this->searchQuery));
        $searching  = $q !== '';
        $items      = [];

        $showShop       = $searching || in_array($this->stockFilter, ['shop', 'all']);
        $showWarehouse  = $searching || in_array($this->stockFilter, ['warehouse', 'all']);

        if ($showShop) {
            foreach ($this->shopStock as $p) {
                if ($searching && !str_contains(mb_strtolower($p['name']), $q)
                               && !str_contains(mb_strtolower($p['sku']), $q)) {
                    continue;
                }
                $items[] = $p;
            }
        }

        if ($showWarehouse) {
            foreach ($this->warehouseStock as $p) {
                if ($searching && !str_contains(mb_strtolower($p['name']), $q)
                               && !str_contains(mb_strtolower($p['sku']), $q)) {
                    continue;
                }
                $items[] = $p;
            }
        }

        usort($items, fn ($a, $b) => strcmp($a['name'], $b['name']));
        return $items;
    }

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount()
    {
        $user = auth()->user();

        if (!$user->isShopManager() && !$user->isSuperUser()) {
            abort(403, __('Access denied. Shop managers, owners, and admins only.'));
        }

        $this->isOwner = $user->isSuperUser();

        if ($user->isShopManager()) {
            $this->shopId = $user->location_id;
        }

        if ($user->isSuperUser()) {
            $this->availableShops = Shop::orderBy('name')->get()->toArray();
            $this->shopId = request()->get('shop_id') ?? session('selected_shop_id');

            if (!$this->shopId) {
                $this->showShopSelectionModal = true;
                return;
            }
            session(['selected_shop_id' => $this->shopId]);
        }

        if (!$this->shopId) {
            abort(404, 'No shop found.');
        }

        if (!$this->checkSession($this->shopId)) {
            return;
        }

        $shop = Shop::find($this->shopId);
        $this->shopName      = $shop?->name ?? __('Unknown Shop');
        $this->warehouseId   = $shop?->default_warehouse_id;
        $this->warehouseName = $shop?->defaultWarehouse?->name ?? '';

        // Re-attach scanner session
        $this->scannerSession = ScannerSession::active()
            ->where('user_id', auth()->id())
            ->where('page_type', 'pos')
            ->latest()
            ->first();

        if ($this->scannerSession) {
            $this->showScannerPanel = true;
        }

        // Load settings
        $settings = app(\App\Services\SettingsService::class);
        $this->settingAllowIndividualSales   = $settings->allowIndividualItemSales();
        $this->settingIndividualCategoryIds  = $settings->individualSaleCategoryIds();
        $this->settingAllowPriceOverride     = $settings->allowPriceOverride();
        $this->settingPriceOverrideThreshold = $settings->priceOverrideThreshold();
        $this->settingAllowCreditSales       = $settings->allowCreditSales();
        $this->settingCreditRequiresCustomer = $settings->creditRequiresCustomer();
        $this->settingMaxCreditPerCustomer   = $settings->maxCreditPerCustomer();
        $this->settingAllowCardPayment       = $settings->allowCardPayment();
        $this->settingAllowBankTransfer      = $settings->allowBankTransferPayment();

        $this->loadStock();
        $this->loadHeldSales();
    }

    // ── Stock loading ─────────────────────────────────────────────────────────

    public function loadStock(): void
    {
        if (!$this->shopId) return;
        $this->loadShopStock();
        $this->loadWarehouseStock();
    }

    /** Categories this shop sells (Shop::sellableCategoryIds; null = everything). */
    private function shopSellableCategoryIds(): ?array
    {
        return \App\Models\Shop::find($this->shopId)?->sellableCategoryIds();
    }

    private function loadShopStock(): void
    {
        // Specialised shop: stock outside its categories can't be sold here
        // (it's shown on the shop's stock page to send back to the warehouse)
        $sellable = $this->shopSellableCategoryIds();

        $this->shopStock = Product::where('is_active', true)
            ->when($sellable !== null, fn ($q) => $q->whereIn('category_id', $sellable ?: [0]))
            ->whereHas('boxes', function ($q) {
                $q->where('location_type', 'shop')
                  ->where('location_id', $this->shopId)
                  ->whereIn('status', ['full', 'partial'])
                  ->where('items_remaining', '>', 0);
            })
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $stock = $product->getCurrentStock('shop', $this->shopId);
                return [
                    'id'            => $product->id,
                    'name'          => $product->name,
                    'sku'           => $product->sku,
                    'barcode'       => $product->barcode,
                    'category'      => $product->category?->name ?? '',
                    'category_id'   => $product->category_id,
                    'selling_price' => $product->selling_price,
                    'items_per_box' => $product->items_per_box,
                    'box_price'     => $product->effective_box_selling_price,
                    'stock'         => $stock,
                    'source'        => 'shop',
                    'source_id'     => $this->shopId,
                    'source_name'   => $this->shopName,
                ];
            })
            ->toArray();
    }

    private function loadWarehouseStock(): void
    {
        if (!$this->warehouseId) {
            $this->warehouseStock = [];
            return;
        }

        $sellable = $this->shopSellableCategoryIds();

        $this->warehouseStock = DB::table('boxes')
            ->join('products', 'products.id', '=', 'boxes.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->when($sellable !== null, fn ($q) => $q->whereIn('products.category_id', $sellable ?: [0]))
            ->where('boxes.location_type', 'warehouse')
            ->where('boxes.location_id', $this->warehouseId)
            ->whereIn('boxes.status', ['full', 'partial'])
            ->where('boxes.items_remaining', '>', 0)
            ->where('products.is_active', true)
            ->whereNull('products.deleted_at')
            ->groupBy(
                'products.id', 'products.name', 'products.sku',
                'products.items_per_box', 'products.selling_price',
                'products.box_selling_price', 'products.category_id',
                'categories.name'
            )
            ->selectRaw('
                products.id,
                products.name,
                products.sku,
                products.items_per_box,
                products.selling_price,
                products.box_selling_price,
                products.category_id,
                categories.name as category_name,
                COUNT(boxes.id) as box_count,
                COUNT(boxes.id) FILTER (WHERE boxes.status = \'full\') as full_box_count,
                SUM(boxes.items_remaining) as total_items
            ')
            ->orderBy('products.name')
            ->get()
            ->map(fn ($row) => [
                'id'            => $row->id,
                'name'          => $row->name,
                'sku'           => $row->sku,
                'barcode'       => null,
                'category'      => $row->category_name ?? '',
                'category_id'   => $row->category_id,
                'selling_price' => $row->selling_price,
                'items_per_box' => $row->items_per_box,
                'box_price'     => (int) ($row->box_selling_price ?? ($row->selling_price * $row->items_per_box)),
                'stock'         => [
                    'total_items' => (int) $row->total_items,
                    // Only sealed boxes can be sold as a box; opened ones only by item
                    'full_boxes'  => (int) $row->full_box_count,
                    'total_boxes' => (int) $row->box_count,
                ],
                'source'        => 'warehouse',
                'source_id'     => $this->warehouseId,
                'source_name'   => $this->warehouseName,
            ])
            ->toArray();
    }

    public function setStockFilter(string $filter): void
    {
        $this->stockFilter = in_array($filter, ['all', 'shop', 'warehouse']) ? $filter : 'all';
    }

    public function updatedSearchQuery(): void
    {
        // Triggers recompute of displayedStock automatically
    }

    // ── Barcode scanning ──────────────────────────────────────────────────────

    public function updatedBarcodeInput(): void
    {
        if (strlen($this->barcodeInput) < 3) return;
        $this->handleBarcodeScanned($this->barcodeInput);
        $this->barcodeInput = '';
    }

    public function handleBarcodeScanned(string $barcode): void
    {
        if (!$this->scanningEnabled) return;

        $product = $this->findProductByBarcode($barcode);

        if (!$product) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Product not found for barcode: :barcode', ['barcode' => $barcode])]);
            return;
        }

        // Check shop stock first; fall back to warehouse
        $shopStock = $product->getCurrentStock('shop', $this->shopId);
        if ($shopStock['total_items'] > 0) {
            $this->openProductModal($product->id, 'shop');
            $this->dispatch('notification', ['type' => 'info', 'message' => __('Scanned: :name', ['name' => $product->name])]);
            return;
        }

        if ($this->warehouseId) {
            $whStock = collect($this->warehouseStock)->firstWhere('id', $product->id);
            if ($whStock && $whStock['stock']['total_items'] > 0) {
                $this->openProductModal($product->id, 'warehouse');
                $this->dispatch('notification', ['type' => 'info', 'message' => __('Scanned (warehouse): :name', ['name' => $product->name])]);
                return;
            }
        }

        $this->dispatch('notification', ['type' => 'error', 'message' => __(':name is out of stock', ['name' => $product->name])]);
    }

    private function findProductByBarcode(string $barcode): ?Product
    {
        $pb = \App\Models\ProductBarcode::where('barcode', $barcode)->where('is_active', true)->with('product')->first();
        if ($pb?->product) return $pb->product;
        return Product::where('barcode', $barcode)->where('is_active', true)->first();
    }

    // ── Phone scanner session ─────────────────────────────────────────────────

    public function enablePhoneScanner(): void
    {
        ScannerSession::where('user_id', auth()->id())->where('page_type', 'pos')->update(['is_active' => false]);

        $this->scannerSession = ScannerSession::create([
            'session_code' => ScannerSession::generateCode(),
            'user_id'      => auth()->id(),
            'page_type'    => 'pos',
            'transfer_id'  => null,
            'is_active'    => true,
            'expires_at'   => now()->addHours(2),
        ]);
        $this->showScannerPanel = true;
    }

    public function disablePhoneScanner(): void
    {
        $this->scannerSession?->deactivate();
        $this->scannerSession  = null;
        $this->showScannerPanel = false;
    }

    public function checkForScans(): void
    {
        if (!$this->scannerSession) return;
        $this->scannerSession->refresh();
        if (!$this->scannerSession->last_scanned_barcode) return;

        $barcode = $this->scannerSession->last_scanned_barcode;
        $this->lastProcessedScan = $barcode;

        try {
            $this->handleBarcodeScanned($barcode);
            $this->scannerSession->update(['last_scanned_barcode' => null, 'last_scan_at' => now()]);
        } catch (\Throwable $e) {
            $this->scannerSession->update(['last_scanned_barcode' => null, 'last_scan_at' => now()]);
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Scanner error: :error', ['error' => $e->getMessage()])]);
        }
    }

    // ── Product selection & staging modal ─────────────────────────────────────

    /** Called from product tile click */
    public function selectProduct(int $productId, string $source): void
    {
        $this->openProductModal($productId, $source);
    }

    private function openProductModal(int $productId, string $source): void
    {
        $shop = \App\Models\Shop::find($this->shopId);
        if ($shop && ! $shop->sellsProduct($productId)) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __(':shop doesn\'t sell this product\'s category.', ['shop' => $shop->name])]);
            return;
        }

        if ($source === 'shop') {
            $product = Product::with('category')->findOrFail($productId);
            $stock   = $product->getCurrentStock('shop', $this->shopId);

            if ($stock['total_items'] === 0) {
                $this->dispatch('notification', ['type' => 'error', 'message' => __('Product is out of stock at shop')]);
                return;
            }

            // Opt-in per category (owner Settings) — see SettingsService::categoryAllowsIndividualSales()
            $individualAllowed = app(\App\Services\SettingsService::class)->categoryAllowsIndividualSales($product->category_id);

            $hasFullBox = Box::where('product_id', $product->id)
                ->where('location_type', 'shop')
                ->where('location_id', $this->shopId)
                ->where('status', 'full')
                ->exists();

            // Box-only category with only opened boxes left: it can't be sold
            // by item, and there's no full box to sell — block, the owner decides.
            if (!$hasFullBox && !$individualAllowed) {
                $this->dispatch('notification', ['type' => 'error', 'message' => __(
                    'Only opened boxes of :product are left, and :category is sold by full box only.',
                    ['product' => $product->name, 'category' => $product->category?->name ?? __('this category')]
                )]);
                return;
            }

            $this->stagingProduct = [
                'id'                     => $product->id,
                'name'                   => $product->name,
                'sku'                    => $product->sku,
                'category'               => $product->category?->name,
                'category_id'            => $product->category_id,
                'selling_price'          => $product->selling_price,
                'items_per_box'          => $product->items_per_box,
                'box_price'              => $product->effective_box_selling_price,
                'source'                 => 'shop',
                'source_id'              => $this->shopId,
                'source_name'            => $this->shopName,
                'individual_sale_allowed' => $individualAllowed,
                'has_full_box'           => $hasFullBox,
            ];
            $this->stagingStock = $stock;

            // A tile tap always stages a NEW entry — confirmAddToCart() merges it
            // into an existing line of the same product + source + mode, so a
            // cart can hold e.g. 2 full boxes AND 10 loose items of one product.
            // Existing lines are changed with the pencil (openEditItem).
            $this->stagingCartIndex     = null;
            $this->stagingMode          = $hasFullBox ? 'box' : 'item';
            $this->stagingQty           = 1;
            $this->stagingPrice         = $hasFullBox ? $product->effective_box_selling_price : $product->selling_price;
            $this->stagingPriceModified = false;
            $this->stagingPriceReason   = '';

        } else { // warehouse
            $whProduct = collect($this->warehouseStock)->firstWhere('id', $productId);

            if (!$whProduct || $whProduct['stock']['total_items'] === 0) {
                $this->dispatch('notification', ['type' => 'error', 'message' => __('Product is out of stock at warehouse')]);
                return;
            }

            // Same per-category rule as shop stock (owner Settings)
            $individualAllowed = app(\App\Services\SettingsService::class)->categoryAllowsIndividualSales($whProduct['category_id']);
            $hasFullBox        = ($whProduct['stock']['full_boxes'] ?? 0) > 0;

            if (!$hasFullBox && !$individualAllowed) {
                $this->dispatch('notification', ['type' => 'error', 'message' => __(
                    'Only opened boxes of :product are left, and :category is sold by full box only.',
                    ['product' => $whProduct['name'], 'category' => $whProduct['category'] ?: __('this category')]
                )]);
                return;
            }

            $this->stagingProduct = [
                'id'                      => $whProduct['id'],
                'name'                    => $whProduct['name'],
                'sku'                     => $whProduct['sku'],
                'category'                => $whProduct['category'],
                'category_id'             => $whProduct['category_id'],
                'selling_price'           => $whProduct['selling_price'],
                'items_per_box'           => $whProduct['items_per_box'],
                'box_price'               => $whProduct['box_price'],
                'source'                  => 'warehouse',
                'source_id'               => $this->warehouseId,
                'source_name'             => $this->warehouseName,
                'individual_sale_allowed' => $individualAllowed,
                'has_full_box'            => $hasFullBox,
                'box_count'               => $whProduct['stock']['full_boxes'],
            ];
            $this->stagingStock = $whProduct['stock'];

            // New entry — merged on confirm (see the shop branch above)
            $this->stagingCartIndex     = null;
            $this->stagingMode          = $hasFullBox ? 'box' : 'item';
            $this->stagingQty           = 1;
            $this->stagingPrice         = $hasFullBox ? $whProduct['box_price'] : $whProduct['selling_price'];
            $this->stagingPriceModified = false;
            $this->stagingPriceReason   = '';
        }

        $this->showAddModal = true;
    }

    /**
     * The cart line for this product + source + mode (box/item) + unit price,
     * if any. Price is part of the key so merging never silently re-prices
     * quantities the seller already confirmed at a different price.
     */
    private function findCartLine(int $productId, string $source, string $mode, int $price, ?int $exceptIndex = null): int|false
    {
        foreach ($this->cart as $index => $item) {
            if ($index !== $exceptIndex
                && (int) $item['product_id'] === $productId
                && ($item['source'] ?? 'shop') === $source
                && ($item['mode'] ?? 'box') === $mode
                && (int) $item['price'] === $price) {
                return $index;
            }
        }
        return false;
    }

    /**
     * Stock check across the WHOLE cart for one product + source: box lines
     * need sealed boxes, and boxes × items/box + loose items can't exceed
     * the items in stock (a box line and an item line share the same stock).
     * $replaceIndex = the line being edited/merged into (its qty is replaced
     * by the staged totals). Returns an error message or null.
     */
    private function cartStockError(int $productId, string $source, int $boxes, int $items, array $skipIndexes, int $fullBoxes, int $totalItems, int $perBox): ?string
    {
        foreach ($this->cart as $index => $item) {
            if (in_array($index, $skipIndexes, true)
                || (int) $item['product_id'] !== $productId
                || ($item['source'] ?? 'shop') !== $source) {
                continue;
            }
            if (($item['mode'] ?? 'box') === 'box') {
                $boxes += (int) $item['qty'];
            } else {
                $items += (int) $item['qty'];
            }
        }

        $where = $source === 'warehouse' ? __('at the warehouse') : __('in the shop');

        if ($boxes > $fullBoxes) {
            return $fullBoxes === 0
                ? __('No full boxes left :where — sell the remaining items individually', ['where' => $where])
                : __('Only :count full box(es) available :where (the cart already has :cart)', ['count' => $fullBoxes, 'where' => $where, 'cart' => $boxes]);
        }
        if ($boxes * $perBox + $items > $totalItems) {
            return __('Not enough stock :where: :boxes box(es) + :items item(s) = :need items, only :have available', [
                'where' => $where, 'boxes' => $boxes, 'items' => $items, 'need' => $boxes * $perBox + $items, 'have' => $totalItems,
            ]);
        }

        return null;
    }

    public function openEditItem(int $index): void
    {
        if (!isset($this->cart[$index])) return;

        $item   = $this->cart[$index];
        $source = $item['source'] ?? 'shop';

        if ($source === 'shop') {
            $product = Product::with('category')->findOrFail($item['product_id']);
            $stock   = $product->getCurrentStock('shop', $this->shopId);

            $this->stagingProduct = [
                'id'                     => $product->id,
                'name'                   => $product->name,
                'sku'                    => $product->sku,
                'category'               => $product->category?->name,
                'category_id'            => $product->category_id,
                'selling_price'          => $product->selling_price,
                'items_per_box'          => $product->items_per_box,
                'box_price'              => $product->effective_box_selling_price,
                'source'                 => 'shop',
                'source_id'              => $this->shopId,
                'source_name'            => $this->shopName,
                // Same rule as when the line was added — editing must not unlock item mode
                'individual_sale_allowed' => app(\App\Services\SettingsService::class)->categoryAllowsIndividualSales($product->category_id),
                'has_full_box'           => Box::where('product_id', $product->id)
                    ->where('location_type', 'shop')->where('location_id', $this->shopId)
                    ->where('status', BoxStatus::FULL)->exists(),
            ];
            $this->stagingStock = $stock;
        } else {
            $whProduct = collect($this->warehouseStock)->firstWhere('id', $item['product_id']);
            // Use the true catalog prices (from warehouseStock, or a fresh
            // Product lookup if it fell out of the current stock list) —
            // never $item['price'], which is this cart line's already
            //-modified price. Reusing it here would make the price-override
            // threshold check compare the discounted price against itself
            // (0% diff), silently clearing requires_owner_approval on re-edit.
            $trueSellingPrice = $whProduct['selling_price'] ?? null;
            $trueBoxPrice     = $whProduct['box_price'] ?? null;
            if ($trueSellingPrice === null || $trueBoxPrice === null) {
                $product          = Product::find($item['product_id']);
                $trueSellingPrice = $product?->selling_price ?? $item['price'];
                $trueBoxPrice     = $product?->effective_box_selling_price ?? $item['price'];
            }
            $whCategoryId = $whProduct['category_id'] ?? Product::whereKey($item['product_id'])->value('category_id');

            $this->stagingProduct = [
                'id'                      => $item['product_id'],
                'name'                    => $item['product_name'],
                'sku'                     => $item['sku'] ?? '',
                'category'                => $item['category'] ?? '',
                'category_id'             => $whCategoryId,
                'selling_price'           => $trueSellingPrice,
                'items_per_box'           => $item['items_per_box'],
                'box_price'               => $trueBoxPrice,
                'source'                  => 'warehouse',
                'source_id'               => $this->warehouseId,
                'source_name'             => $this->warehouseName,
                'individual_sale_allowed' => app(\App\Services\SettingsService::class)->categoryAllowsIndividualSales($whCategoryId),
                'has_full_box'            => ($whProduct['stock']['full_boxes'] ?? 0) > 0,
                'box_count'               => $whProduct['stock']['full_boxes'] ?? 0,
            ];
            $this->stagingStock = $whProduct['stock'] ?? ['total_items' => 0, 'full_boxes' => 0];
        }

        $this->stagingCartIndex     = $index;
        $this->stagingMode          = $item['mode'];
        $this->stagingQty           = $item['qty'];
        $this->stagingPrice         = $item['price'];
        $this->stagingPriceModified = $item['price_modified'] ?? false;
        $this->stagingPriceReason   = $item['price_modification_reason'] ?? '';
        $this->showAddModal         = true;
    }

    // ── Staging modal updates ─────────────────────────────────────────────────

    public function updatedStagingMode(): void
    {
        if (!$this->stagingProduct) return;
        $this->stagingPrice = $this->stagingMode === 'box'
            ? $this->stagingProduct['box_price']
            : $this->stagingProduct['selling_price'];
        $this->stagingPriceModified = false;
        $this->stagingPriceReason   = '';
    }

    public function updatedStagingPrice(): void
    {
        if (!$this->stagingProduct) return;
        $original = $this->stagingMode === 'box'
            ? $this->stagingProduct['box_price']
            : $this->stagingProduct['selling_price'];
        $this->stagingPriceModified = ((int) $this->stagingPrice !== (int) $original);
        if (!$this->stagingPriceModified) $this->stagingPriceReason = '';
    }

    public function updatedStagingQty(): void
    {
        if ($this->stagingMode === 'item' && $this->stagingStock) {
            $max = $this->stagingStock['total_items'];
            if ($this->stagingQty > $max) {
                $this->stagingQty = $max;
                $this->dispatch('notification', ['type' => 'warning', 'message' => __('Only :max items available', ['max' => $max])]);
            }
        }
    }

    public function incrementStagingQty(): void
    {
        $source = $this->stagingProduct['source'] ?? 'shop';
        if ($source === 'warehouse' && $this->stagingMode === 'box') {
            $max = $this->stagingProduct['box_count'] ?? 0;
            $this->stagingQty = min(max(1, $max), $this->stagingQty + 1);
        } else {
            $this->stagingQty++;
            $this->updatedStagingQty();
        }
    }

    public function decrementStagingQty(): void
    {
        $this->stagingQty = max(1, $this->stagingQty - 1);
    }

    public function confirmAddToCart(): void
    {
        if (!$this->stagingProduct) return;

        $source = $this->stagingProduct['source'] ?? 'shop';

        // Validate individual item sales setting — re-derived from the product's
        // category in the DB, never from the (client-mutable) staging flag
        if ($this->stagingMode === 'item') {
            $categoryId = Product::whereKey($this->stagingProduct['id'])->value('category_id');
            if (! app(\App\Services\SettingsService::class)->categoryAllowsIndividualSales($categoryId)) {
                $this->dispatch('notification', ['type' => 'error', 'message' => __('This category is sold by full box only')]);
                return;
            }
        }

        if (!$this->settingAllowPriceOverride && $this->stagingPriceModified) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Price modifications are not allowed')]);
            return;
        }

        if ($this->stagingQty < 1) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Quantity must be at least 1')]);
            return;
        }

        // Where does this entry land? Editing keeps its line; a new entry (or
        // an edit that switched mode) joins an existing same-mode line.
        $productId  = (int) $this->stagingProduct['id'];
        $mergeIndex = $this->findCartLine($productId, $source, $this->stagingMode, (int) $this->stagingPrice, $this->stagingCartIndex);

        $stagedQty = (int) $this->stagingQty;
        if ($mergeIndex !== false && $this->stagingCartIndex === null) {
            // New entry joining an existing line: quantities add up
            $stagedQty += (int) $this->cart[$mergeIndex]['qty'];
        } elseif ($mergeIndex !== false) {
            // Edited line switched into a mode that already has a line: merge
            $stagedQty += (int) $this->cart[$mergeIndex]['qty'];
        }

        if ($source === 'shop') {
            $fullBoxes = Box::where('product_id', $productId)
                ->where('location_type', 'shop')
                ->where('location_id', $this->shopId)
                ->where('status', 'full')
                ->count();
        } else {
            $fullBoxes = (int) ($this->stagingProduct['box_count'] ?? 0);   // sealed boxes only
        }

        $skip  = array_values(array_filter([$this->stagingCartIndex, $mergeIndex === false ? null : $mergeIndex], fn ($i) => $i !== null));
        $error = $this->cartStockError(
            $productId, $source,
            $this->stagingMode === 'box' ? $stagedQty : 0,
            $this->stagingMode === 'item' ? $stagedQty : 0,
            $skip,
            $fullBoxes,
            (int) ($this->stagingStock['total_items'] ?? 0),
            (int) $this->stagingProduct['items_per_box'],
        );
        if ($error) {
            $this->dispatch('notification', ['type' => 'error', 'message' => $error]);
            return;
        }

        $isFullBox     = $this->stagingMode === 'box';
        $lineTotal     = $this->stagingPrice * $stagedQty;
        $originalPrice = $isFullBox
            ? $this->stagingProduct['box_price']
            : $this->stagingProduct['selling_price'];

        $requiresApproval = false;
        if ($this->stagingPriceModified && $originalPrice > 0) {
            $pct = (($originalPrice - $this->stagingPrice) / $originalPrice) * 100;
            $requiresApproval = $pct > $this->settingPriceOverrideThreshold;
        }

        $cartItem = [
            'product_id'                => $this->stagingProduct['id'],
            'product_name'              => $this->stagingProduct['name'],
            'sku'                       => $this->stagingProduct['sku'] ?? '',
            'category'                  => $this->stagingProduct['category'] ?? '',
            'source'                    => $source,
            'source_id'                 => $this->stagingProduct['source_id'],
            'source_name'               => $this->stagingProduct['source_name'],
            'mode'                      => $this->stagingMode,
            'items_per_box'             => $this->stagingProduct['items_per_box'],
            'qty'                       => $stagedQty,
            'price'                     => $this->stagingPrice,
            'line_total'                => $lineTotal,
            'price_modified'            => $this->stagingPriceModified,
            'price_modification_reason' => $this->stagingPriceReason ?: null,
            // backward-compat fields
            'is_full_box'               => $isFullBox,
            'quantity'                  => $stagedQty,
            'original_price'            => $originalPrice,
            'requires_owner_approval'   => $requiresApproval,
        ];

        if ($mergeIndex !== false) {
            // Same product + source + mode + price already in the cart: one line, summed qty.
            $this->cart[$mergeIndex] = $cartItem;
            if ($this->stagingCartIndex !== null && $this->stagingCartIndex !== $mergeIndex) {
                unset($this->cart[$this->stagingCartIndex]);   // edited line folded into it
                $this->cart = array_values($this->cart);
            }
            $message = __('Cart updated — :qty :unit', [
                'qty'  => $stagedQty,
                'unit' => $isFullBox ? trans_choice('box|boxes', $stagedQty) : trans_choice('item|items', $stagedQty),
            ]);
        } elseif ($this->stagingCartIndex !== null && isset($this->cart[$this->stagingCartIndex])) {
            $this->cart[$this->stagingCartIndex] = $cartItem;
            $message = __('Cart item updated');
        } else {
            $this->cart[] = $cartItem;
            $message = __('Added to cart');
        }

        $this->calculateCartTotal();
        $this->closeAddModal();
        $this->dispatch('notification', ['type' => 'success', 'message' => $message]);
    }

    public function closeAddModal(): void
    {
        $this->showAddModal         = false;
        $this->stagingProduct       = null;
        $this->stagingStock         = [];
        $this->stagingMode          = 'box';
        $this->stagingQty           = 1;
        $this->stagingPrice         = 0;
        $this->stagingPriceModified = false;
        $this->stagingPriceReason   = '';
        $this->stagingCartIndex     = null;
    }

    public function removeCartItem(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateCartTotal();
    }

    public function clearCart(): void
    {
        $this->cart      = [];
        $this->cartTotal = 0;

        $this->selectedCustomerId                    = null;
        $this->selectedCustomerName                  = '';
        $this->selectedCustomerPhone                 = '';
        $this->selectedCustomerOutstandingBalance    = 0;
        $this->customerSearch                        = '';
        $this->customerResults                       = [];
        $this->showCustomerSearch                    = false;

        $this->payAmt_cash          = 0;
        $this->payAmt_card          = 0;
        $this->payAmt_mobile_money  = 0;
        $this->payAmt_bank_transfer = 0;
        $this->payAmt_credit        = 0;
        $this->payRef_card          = '';
        $this->payRef_mobile_money  = '';
        $this->payRef_bank_transfer = '';
        $this->creditWarningVisible = false;
        $this->creditWarningMessage = '';
    }

    private function calculateCartTotal(): void
    {
        $this->cartTotal = (int) array_sum(array_column($this->cart, 'line_total'));
    }

    // ── Customer ──────────────────────────────────────────────────────────────

    public function openCustomerSearch(): void
    {
        $this->showCustomerSearch = true;
        if (count($this->customerResults) === 0) {
            $this->customerResults = \App\Models\Customer::orderBy('name')
                ->limit(20)->get(['id', 'name', 'phone', 'outstanding_balance'])->toArray();
        }
    }

    public function updatedCustomerSearch(): void
    {
        $q = trim($this->customerSearch);
        $this->customerResults = strlen($q) < 1
            ? \App\Models\Customer::orderBy('name')->limit(20)->get(['id', 'name', 'phone', 'outstanding_balance'])->toArray()
            : \App\Models\Customer::search($q)->toArray();
        $this->showCustomerSearch = true;
    }

    public function selectCustomer(int $customerId): void
    {
        $customer = \App\Models\Customer::find($customerId);
        if (!$customer) return;

        $this->selectedCustomerId                 = $customer->id;
        $this->selectedCustomerName               = $customer->name;
        $this->selectedCustomerPhone              = $customer->phone;
        $this->selectedCustomerOutstandingBalance = $customer->outstanding_balance;
        $this->customerSearch                     = '';
        $this->customerResults                    = [];
        $this->showCustomerSearch                 = false;
        $this->showNewCustomerForm                = false;

        if ($this->payAmt_credit > 0) $this->evaluateCreditWarning();
    }

    public function clearCustomer(): void
    {
        $this->selectedCustomerId                 = null;
        $this->selectedCustomerName               = '';
        $this->selectedCustomerPhone              = '';
        $this->selectedCustomerOutstandingBalance = 0;
        $this->customerSearch                     = '';
        $this->customerResults                    = [];
        $this->showNewCustomerForm                = false;
        if ($this->payAmt_credit > 0) {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
        }
    }

    public function showCreateCustomerForm(): void
    {
        $this->showNewCustomerForm = true;
        $this->showCustomerSearch  = false;
        $this->customerResults     = [];
        if (preg_match('/^\d+$/', trim($this->customerSearch))) {
            $this->newCustomerPhone = trim($this->customerSearch);
        }
    }

    public function saveNewCustomer(): void
    {
        $this->validate([
            'newCustomerName'  => 'required|string|min:2|max:100',
            'newCustomerPhone' => 'required|string|min:10|max:20|unique:customers,phone',
            'newCustomerEmail' => 'nullable|email|max:100',
            'newCustomerNotes' => 'nullable|string|max:500',
        ]);

        $customer = (new \App\Services\Sales\CustomerService())->create([
            'name'  => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail,
            'notes' => $this->newCustomerNotes,
        ], $this->shopId);

        $this->selectCustomer($customer->id);
        $this->newCustomerName  = '';
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
        $this->newCustomerNotes = '';
        $this->showNewCustomerForm = false;
        $this->dispatch('notification', ['type' => 'success', 'message' => __('Customer registered successfully')]);
    }

    public function cancelNewCustomer(): void
    {
        $this->showNewCustomerForm = false;
        $this->newCustomerName     = '';
        $this->newCustomerPhone    = '';
        $this->newCustomerEmail    = '';
        $this->newCustomerNotes    = '';
    }

    // ── Transporter ──────────────────────────────────────────────────────────

    public function showCreateTransporterForm(): void
    {
        $this->showNewTransporterForm = true;
    }

    public function cancelNewTransporter(): void
    {
        $this->showNewTransporterForm = false;
        $this->newTransporterName     = '';
        $this->newTransporterPhone    = '';
        $this->newTransporterCompany  = '';
        $this->newTransporterVehicle  = '';
    }

    public function saveNewTransporter(): void
    {
        $this->validate([
            'newTransporterName'    => 'required|string|min:2|max:100',
            'newTransporterPhone'   => 'nullable|string|max:20',
            'newTransporterCompany' => 'nullable|string|max:100',
            'newTransporterVehicle' => 'nullable|string|max:50',
        ]);

        $transporter = Transporter::create([
            'name'           => $this->newTransporterName,
            'phone'          => $this->newTransporterPhone ?: null,
            'company_name'   => $this->newTransporterCompany ?: null,
            'vehicle_number' => $this->newTransporterVehicle ?: null,
            'is_active'      => true,
        ]);

        $this->fulfillmentTransporterId = $transporter->id;
        $this->cancelNewTransporter();
        $this->dispatch('notification', ['type' => 'success', 'message' => __('Transporter registered successfully')]);
    }

    // ── Payment panel ─────────────────────────────────────────────────────────

    public function updatedPayAmtCredit(): void
    {
        if ($this->payAmt_credit <= 0) {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
            return;
        }

        if (!$this->settingAllowCreditSales) {
            $this->payAmt_credit = 0;
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Credit sales are disabled by the owner')]);
            return;
        }

        if ($this->settingCreditRequiresCustomer && !$this->selectedCustomerId) {
            $this->payAmt_credit = 0;
            $this->dispatch('notification', ['type' => 'warning', 'message' => __('A registered customer must be selected before using credit')]);
            return;
        }

        if ($this->settingMaxCreditPerCustomer > 0 && $this->selectedCustomerId) {
            $customer = \App\Models\Customer::find($this->selectedCustomerId);
            if ($customer) {
                $projected = $customer->outstanding_balance + $this->payAmt_credit;
                if ($projected > $this->settingMaxCreditPerCustomer) {
                    $remaining = max(0, $this->settingMaxCreditPerCustomer - $customer->outstanding_balance);
                    $this->payAmt_credit = $remaining;
                    $this->dispatch('notification', ['type' => 'warning', 'message' => __('Credit limit reached. Max remaining: :amount RWF', ['amount' => number_format($remaining)])]);
                }
            }
        }

        $this->evaluateCreditWarning();
        $this->autoAdjustCash();
    }

    private function evaluateCreditWarning(): void
    {
        if (!$this->selectedCustomerId || $this->payAmt_credit <= 0) {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
            return;
        }
        // Credit is per shop — name each shop the customer owes (warn, but allow)
        $owed = app(\App\Services\Sales\CustomerCreditLedger::class)->describeOwed((int) $this->selectedCustomerId, $this->shopId ? (int) $this->shopId : null);
        if ($owed !== '') {
            $this->creditWarningVisible = true;
            $this->creditWarningMessage = $owed;
        } else {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
        }
    }

    private function autoAdjustCash(): void
    {
        $nonCash = (int) $this->payAmt_card
            + (int) $this->payAmt_mobile_money
            + (int) $this->payAmt_bank_transfer
            + (int) $this->payAmt_credit;
        $this->payAmt_cash = max(0, $this->cartTotal - $nonCash);
    }

    public function updatedPayAmtCard(): void          { $this->autoAdjustCash(); }
    public function updatedPayAmtMobileMoney(): void   { $this->autoAdjustCash(); }
    public function updatedPayAmtBankTransfer(): void  { $this->autoAdjustCash(); }

    // ── Checkout ──────────────────────────────────────────────────────────────

    public function openCheckout(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Cart is empty')]);
            return;
        }

        $needsApproval = collect($this->cart)->contains('requires_owner_approval', true);
        if ($needsApproval) {
            $resumeApproved = false;
            if ($this->resumingFromHeld !== null) {
                $heldRecord     = HeldSale::find($this->resumingFromHeld);
                $resumeApproved = $heldRecord && $heldRecord->isApproved();
            }
            if (!$resumeApproved) {
                $this->dispatch('notification', ['type' => 'warning', 'message' =>
                    $this->resumingFromHeld
                        ? __('Waiting for owner approval.')
                        : __('Price overrides require owner approval. Use "Hold for Approval".')
                ]);
                return;
            }
        }

        $this->payAmt_cash          = $this->cartTotal;
        $this->payAmt_card          = 0;
        $this->payAmt_mobile_money  = 0;
        $this->payAmt_bank_transfer = 0;
        $this->payAmt_credit        = 0;
        $this->payRef_card          = '';
        $this->payRef_mobile_money  = '';
        $this->payRef_bank_transfer = '';
        $this->creditWarningVisible = false;
        $this->creditWarningMessage = '';
        $this->notes                = '';
        $this->fulfillmentMethod    = 'transporter';
        $this->fulfillmentTransporterId = null;
        $this->fulfillmentNotes     = '';
        $this->showNewTransporterForm = false;

        $this->showCheckoutModal = true;
    }

    public function closeCheckoutModal(): void
    {
        $this->showCheckoutModal = false;
    }

    /** Called from Alpine to sync payment fields before completing */
    public function checkout(int $card, int $momo, int $bank, int $credit): void
    {
        $this->payAmt_card          = max(0, $card);
        $this->payAmt_mobile_money  = max(0, $momo);
        $this->payAmt_bank_transfer = max(0, $bank);
        $this->payAmt_credit        = max(0, $credit);
        $this->autoAdjustCash();
        $this->completeSale();
    }

    public function completeSale(): void
    {
        $this->validate(['notes' => 'nullable|string']);

        if (empty($this->cart)) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Cart is empty')]);
            return;
        }

        if (!$this->selectedCustomerId) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Please select or register a customer before completing the sale')]);
            return;
        }

        $needsApproval = collect($this->cart)->contains('requires_owner_approval', true);
        if ($needsApproval) {
            $resumeApproved = false;
            if ($this->resumingFromHeld !== null) {
                $heldRecord     = HeldSale::find($this->resumingFromHeld);
                $resumeApproved = $heldRecord && $heldRecord->isApproved();
            }
            if (!$resumeApproved) {
                $this->dispatch('notification', ['type' => 'error', 'message' => __('Cannot complete sale: price override pending owner approval.')]);
                return;
            }
        }

        if ($this->totalAllocated < $this->cartTotal) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Payment does not cover total. Missing :amount RWF', ['amount' => number_format($this->cartTotal - $this->totalAllocated)])]);
            return;
        }

        if ($this->totalAllocated > $this->cartTotal) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Payment exceeds total by :amount RWF', ['amount' => number_format($this->totalAllocated - $this->cartTotal)])]);
            return;
        }

        if ($this->payAmt_credit > 0 && !$this->settingAllowCreditSales) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Credit sales are disabled')]);
            return;
        }

        if ($this->payAmt_credit > 0 && $this->settingCreditRequiresCustomer && !$this->selectedCustomerId) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('A registered customer must be selected for credit sales')]);
            return;
        }

        if ($this->hasWarehouseItems && $this->fulfillmentMethod === 'transporter' && !$this->fulfillmentTransporterId) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Please select a transporter for warehouse items')]);
            return;
        }

        try {
            $payments = [];
            if ($this->payAmt_cash > 0)          $payments[] = ['method' => 'cash',          'amount' => $this->payAmt_cash,          'reference' => null];
            if ($this->payAmt_card > 0)           $payments[] = ['method' => 'card',          'amount' => $this->payAmt_card,          'reference' => $this->payRef_card];
            if ($this->payAmt_mobile_money > 0)   $payments[] = ['method' => 'mobile_money',  'amount' => $this->payAmt_mobile_money,  'reference' => $this->payRef_mobile_money];
            if ($this->payAmt_bank_transfer > 0)  $payments[] = ['method' => 'bank_transfer', 'amount' => $this->payAmt_bank_transfer, 'reference' => $this->payRef_bank_transfer];
            if ($this->payAmt_credit > 0)         $payments[] = ['method' => 'credit',        'amount' => $this->payAmt_credit,        'reference' => null];

            // Map cart to SaleService items
            $items = collect($this->cart)->map(function ($item) {
                return [
                    'product_id'                => $item['product_id'],
                    'source'                    => $item['source'] ?? 'shop',
                    'source_id'                 => $item['source_id'],
                    'mode'                      => $item['mode'],
                    'qty'                       => $item['qty'],
                    'items_per_box'             => $item['items_per_box'],
                    'price'                     => (int) $item['price'],
                    'price_modified'            => $item['price_modified'] ?? false,
                    'price_modification_reason' => $item['price_modification_reason'] ?? null,
                ];
            })->toArray();

            $sale = (new SaleService())->createMixedSale([
                'shop_id'                    => $this->shopId,
                'payments'                   => $payments,
                'customer_id'                => $this->selectedCustomerId,
                'customer_name'              => $this->selectedCustomerName ?: null,
                'customer_phone'             => $this->selectedCustomerPhone ?: null,
                'notes'                      => $this->notes ?: null,
                'items'                      => $items,
                'source_warehouse_id'        => $this->hasWarehouseItems ? $this->warehouseId : null,
                'fulfillment_method'         => $this->hasWarehouseItems ? $this->fulfillmentMethod : null,
                'fulfillment_transporter_id' => ($this->hasWarehouseItems && $this->fulfillmentMethod === 'transporter')
                                                    ? $this->fulfillmentTransporterId : null,
                'fulfillment_notes'          => $this->hasWarehouseItems ? ($this->fulfillmentNotes ?: null) : null,
            ]);

            $this->completedSale = Sale::with(['items.product', 'items.box', 'soldBy', 'shop', 'payments', 'fulfillmentTransporter'])
                ->find($sale->id);

            // Clean up held sale
            if ($this->resumingFromHeld) {
                $heldRecord = HeldSale::find($this->resumingFromHeld);
                if ($heldRecord) {
                    if ($heldRecord->isApproved()) {
                        $sale->update([
                            'price_override_approved_at' => $heldRecord->override_approved_at,
                            'price_override_approved_by' => $heldRecord->override_approved_by,
                        ]);
                    }
                    $heldRecord->delete();
                }
                $this->resumingFromHeld = null;
            }

            // Reload stock so sold-out products disappear
            $this->loadStock();

            $this->clearCart();
            $this->showCheckoutModal = false;
            $this->showReceiptModal  = true;

            $this->dispatch('notification', ['type' => 'success', 'message' => __('Sale completed successfully!')]);
            $this->dispatch('sale-completed');

        } catch (\Throwable $e) {
            \Log::error('UnifiedPos sale error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Error completing sale: :error', ['error' => $e->getMessage()])]);
        }
    }

    public function closeReceipt(): void
    {
        $this->showReceiptModal = false;
        $this->completedSale   = null;
    }

    // ── Shop selection (owners) ───────────────────────────────────────────────

    public function selectShopFromModal(): void
    {
        if (!$this->isOwner || !$this->shopId) return;
        session(['selected_shop_id' => $this->shopId]);
        $shop = Shop::find($this->shopId);
        $this->shopName      = $shop?->name ?? __('Unknown Shop');
        $this->warehouseId   = $shop?->default_warehouse_id;
        $this->warehouseName = $shop?->defaultWarehouse?->name ?? '';
        $this->showShopSelectionModal = false;
        $this->loadStock();
        $this->dispatch('notification', ['type' => 'success', 'message' => __('Now operating at :shop', ['shop' => $this->shopName])]);
    }

    public function changeShop(): void
    {
        if (!$this->isOwner) return;
        if (!empty($this->cart)) $this->clearCart();
        session(['selected_shop_id' => $this->shopId]);
        $shop = Shop::find($this->shopId);
        $this->shopName      = $shop?->name ?? __('Unknown Shop');
        $this->warehouseId   = $shop?->default_warehouse_id;
        $this->warehouseName = $shop?->defaultWarehouse?->name ?? '';
        $this->loadStock();
        $this->dispatch('notification', ['type' => 'success', 'message' => __('Switched to :shop', ['shop' => $this->shopName])]);
    }

    // ── Held sales ────────────────────────────────────────────────────────────

    public function loadHeldSales(): void
    {
        if (!$this->shopId) { $this->heldSales = []; return; }

        $this->heldSales = HeldSale::where('shop_id', $this->shopId)
            ->whereNull('override_rejected_at')
            ->with(['seller', 'approvedBy'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($h) => [
                'id'             => $h->id,
                'reference'      => $h->hold_reference,
                'item_count'     => $h->item_count,
                'cart_total'     => $h->cart_total,
                'customer_name'  => $h->customer_name,
                'seller_name'    => $h->seller->name,
                'needs_approval' => $h->needs_price_approval,
                'is_approved'    => $h->isApproved(),
                'approved_by'    => $h->approvedBy?->name,
                'age'            => $h->created_at->diffForHumans(),
                'is_mine'        => $h->seller_id === auth()->id(),
            ])
            ->toArray();
    }

    public function saveCart(): void
    {
        if (empty($this->cart)) return;
        $nextId = (HeldSale::max('id') ?? 0) + 1;
        HeldSale::create([
            'seller_id'            => auth()->id(),
            'shop_id'              => $this->shopId,
            'hold_reference'       => 'SAVE-' . str_pad($nextId, 4, '0', STR_PAD_LEFT),
            'cart_data'            => $this->cart,
            'cart_total'           => $this->cartTotal,
            'item_count'           => count($this->cart),
            'customer_id'          => $this->selectedCustomerId ?? null,
            'customer_name'        => $this->selectedCustomerName ?: null,
            'customer_phone'       => $this->selectedCustomerPhone ?: null,
            'needs_price_approval' => false,
        ]);
        $this->cart = [];
        $this->cartTotal = 0;
        $this->resumingFromHeld = null;
        $this->loadHeldSales();
        $this->dispatch('notification', ['type' => 'success', 'message' => 'Cart saved.']);
    }

    public function holdSale(): void
    {
        if (empty($this->cart)) return;
        $needsApproval = collect($this->cart)->contains('requires_owner_approval', true);
        $nextId        = (HeldSale::max('id') ?? 0) + 1;
        $held          = HeldSale::create([
            'seller_id'            => auth()->id(),
            'shop_id'              => $this->shopId,
            'hold_reference'       => 'HOLD-' . str_pad($nextId, 4, '0', STR_PAD_LEFT),
            'cart_data'            => $this->cart,
            'cart_total'           => $this->cartTotal,
            'item_count'           => count($this->cart),
            'customer_id'          => $this->selectedCustomerId ?? null,
            'customer_name'        => $this->selectedCustomerName ?: null,
            'customer_phone'       => $this->selectedCustomerPhone ?: null,
            'needs_price_approval' => $needsApproval,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(), 'user_name' => auth()->user()->name,
            'action' => 'sale_held', 'entity_type' => 'HeldSale',
            'entity_id' => $held->id, 'entity_identifier' => $held->hold_reference,
            'details' => ['cart_total' => $this->cartTotal, 'needs_approval' => $needsApproval],
            'ip_address' => request()->ip(),
        ]);

        if ($needsApproval) {
            Alert::create([
                'title'        => 'Price Override Needs Approval',
                'message'      => "{$held->hold_reference} · " . auth()->user()->name . " · {$this->shopName} · " . number_format($this->cartTotal) . ' RWF',
                'severity'     => 'warning',
                'entity_type'  => 'HeldSale',
                'entity_id'    => $held->id,
                'is_resolved'  => false,
                'is_dismissed' => false,
                'action_url'   => route('owner.reports.sales') . '?activeTab=audit',
                'action_label' => 'Review in Price Audit',
            ]);
        }

        $this->clearCart();
        $this->resumingFromHeld  = null;
        $this->showCheckoutModal = false;
        $this->loadHeldSales();

        $msg = $needsApproval
            ? __('Sale held (:ref). Owner notified for price approval.', ['ref' => $held->hold_reference])
            : __('Sale held (:ref).', ['ref' => $held->hold_reference]);

        $this->dispatch('notification', ['type' => 'success', 'message' => $msg]);
    }

    public function resumeHeldSale(int $id): void
    {
        $held = HeldSale::find($id);
        if (!$held || $held->shop_id != $this->shopId) return;

        if ($held->isRejected()) {
            $this->dispatch('notification', ['type' => 'error', 'message' => __('Sale :ref was rejected: :reason', ['ref' => $held->hold_reference, 'reason' => $held->rejected_reason])]);
            return;
        }

        $cart = $held->cart_data;
        if ($held->isApproved()) {
            $cart = array_map(fn ($item) => array_merge($item, ['requires_owner_approval' => false]), $cart);
        }

        $this->cart                  = $cart;
        $this->cartTotal             = $held->cart_total;
        $this->resumingFromHeld      = $held->id;
        $this->selectedCustomerName  = $held->customer_name ?? '';
        $this->selectedCustomerPhone = $held->customer_phone ?? '';
        if ($held->customer_id) $this->selectedCustomerId = $held->customer_id;
        $this->showHeldPanel = false;
        $this->loadHeldSales();
    }

    public function discardHeldSale(int $id): void
    {
        $held = HeldSale::find($id);
        if (!$held || $held->shop_id != $this->shopId) return;
        $held->delete();
        if ($this->resumingFromHeld === $id) {
            $this->resumingFromHeld = null;
            $this->cart             = [];
            $this->cartTotal        = 0;
        }
        $this->loadHeldSales();
        $this->dispatch('notification', ['type' => 'info', 'message' => __('Held sale discarded.')]);
    }

    public function checkApprovals(): void
    {
        $prevIds = collect($this->heldSales)->filter(fn ($h) => !$h['is_approved'])->pluck('id')->toArray();
        $this->loadHeldSales();

        foreach (collect($this->heldSales)->filter(fn ($h) => $h['is_approved'] && in_array($h['id'], $prevIds)) as $h) {
            $this->dispatch('notification', ['type' => 'success', 'message' => __(':ref approved! Tap to resume.', ['ref' => $h['reference']])]);
        }
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.shop.sales.unified-pos', [
            'transporters'        => $this->warehouseId ? Transporter::active()->orderBy('name')->get() : collect(),
            'displayedStock'      => $this->displayedStock,
            'hasWarehouseItems'   => $this->hasWarehouseItems,
        ])->layout('layouts.app');
    }
}
