<?php

namespace App\Livewire\Shop\Sales;

use App\Enums\BoxStatus;
use App\Enums\PaymentMethod;
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
use App\Services\Sales\SaleService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PointOfSale extends Component
{
    use \App\Livewire\Concerns\RequiresOpenSession;

    // Shop
    public $shopId;
    public $shopName;
    public $availableShops = [];
    public $isOwner = false;
    public $showShopSelectionModal = false;

    // Cart
    public $cart = [];
    public $cartTotal = 0;

    // Search
    public $searchQuery = '';
    public $searchResults = [];
    public $allStockProducts = [];   // loaded once on focus, all available products
    public $showSearchResults = false;

    // ── Add Item Modal (staging — shown before cart commit) ───────────────────
    public bool   $showAddModal     = false;
    public ?array $stagingProduct   = null;  // the product being staged
    public array  $stagingStock     = [];    // stock info for the product
    public string $stagingMode      = 'box'; // 'box' | 'item'
    public int    $stagingQty       = 1;
    public int    $stagingPrice     = 0;     // cents — editable
    public bool   $stagingPriceModified = false;
    public string $stagingPriceReason   = '';
    public ?int   $stagingCartIndex = null;  // if editing existing cart item

    // Price Modification Modal
    public $showPriceModal = false;
    public $priceModificationCartIndex = null;
    public $newPrice = 0;
    public $priceModificationReason = '';
    public $priceModificationReference = '';
    public $requiresOwnerApproval = false;

    // Checkout Modal
    public $showCheckoutModal = false;
    public $notes = '';

    // ── Customer selection ────────────────────────────────────────────────────
    public string $customerSearch       = '';
    public array  $customerResults      = [];
    public bool   $showCustomerSearch   = false;
    public ?int   $selectedCustomerId   = null;
    public string $selectedCustomerName = '';
    public string $selectedCustomerPhone = '';
    public int    $selectedCustomerOutstandingBalance = 0;
    public bool   $showNewCustomerForm  = false;
    // New customer form fields:
    public string $newCustomerName  = '';
    public string $newCustomerPhone = '';
    public string $newCustomerEmail = '';
    public string $newCustomerNotes = '';

    // ── Split payment — all channels always visible ───────────────────────────
    // Keyed by method name. Amount is in RWF (integer cents).
    public $payAmt_cash          = 0;
    public $payAmt_card          = 0;
    public $payAmt_mobile_money  = 0;
    public $payAmt_bank_transfer = 0;
    public $payAmt_credit        = 0;
    // References (for card, mobile, bank)
    public $payRef_card          = '';
    public $payRef_mobile_money  = '';
    public $payRef_bank_transfer = '';
    // Credit warning state
    public $creditWarningVisible  = false;
    public $creditWarningMessage  = '';

    // ── Settings (loaded once on mount) ──────────────────────────────────────
    public bool  $settingAllowIndividualSales    = true;
    public array $settingIndividualCategoryIds   = [];
    public bool  $settingAllowPriceOverride      = true;
    public int   $settingPriceOverrideThreshold  = 20;
    public bool  $settingAllowCreditSales        = true;
    public bool  $settingCreditRequiresCustomer  = true;
    public int   $settingMaxCreditPerCustomer    = 0;
    public bool  $settingAllowCardPayment        = false;
    public bool  $settingAllowBankTransfer       = false;

    // Held Sales
    public array $heldSales        = [];
    public bool  $showHeldPanel    = false;
    public ?int  $resumingFromHeld = null;

    // Receipt Modal
    public $showReceiptModal = false;
    public $completedSale = null;

    // ── Phone scanner session ─────────────────────────────────────────────────
    public ?ScannerSession $scannerSession = null;
    public bool $showScannerPanel         = false;
    public string $lastProcessedScan      = '';

    // Barcode Scanning
    public $barcodeInput = '';
    public $scanningEnabled = true;

    protected $listeners = [
        'barcode-scanned' => 'handleBarcodeScanned',
    ];

    public function mount()
    {
        $shopId = $this->shopId ?? auth()->user()->location_id;
        if (!$this->checkSession($shopId)) {
            return;
        }

        $user = auth()->user();

        // Allow shop managers and owners
        if (!$user->isShopManager() && !$user->isOwner()) {
            abort(403, 'Access denied. Shop managers and owners only.');
        }

        $this->isOwner = $user->isOwner();

        // For shop managers, use their assigned shop
        if ($user->isShopManager()) {
            $this->shopId = $user->location_id;
        }

        // For owners, they can select a shop
        if ($user->isOwner()) {
            // Load all shops for the dropdown
            $this->availableShops = Shop::orderBy('name')->get()->toArray();

            // Use session or request parameter, but don't auto-select
            $this->shopId = request()->get('shop_id')
                ?? session('selected_shop_id');

            // If no shop selected, show selection modal
            if (!$this->shopId) {
                $this->showShopSelectionModal = true;
                return; // Don't load shop name until selection
            }

            if ($this->shopId) {
                session(['selected_shop_id' => $this->shopId]);
            }
        }

        if (!$this->shopId) {
            // For shop managers, this is an error
            if (!$user->isOwner()) {
                abort(404, 'No shop found. Please contact administrator.');
            }
            return; // For owners, we'll show the modal
        }

        $shop = Shop::find($this->shopId);
        $this->shopName = $shop->name ?? 'Unknown Shop';

        // Re-attach any active scanner session for this user
        $this->scannerSession = ScannerSession::active()
            ->where('user_id', auth()->id())
            ->where('page_type', 'pos')
            ->latest()
            ->first();

        if ($this->scannerSession) {
            $this->showScannerPanel = true;
        }

        // Load operational settings
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

        $this->loadHeldSales();
    }

    // ==================== SEARCH ====================

    /**
     * Called when the search input is focused.
     * Loads ALL products that have stock at this shop and shows the dropdown.
     */
    public function loadAvailableProducts()
    {
        $this->allStockProducts = Product::where('is_active', true)
            ->whereHas('boxes', function ($query) {
                $query->where('location_type', 'shop')
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
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'category' => $product->category?->name,
                    'selling_price' => $product->selling_price,
                    'selling_price_display' => number_format($product->selling_price, 0),
                    'items_per_box' => $product->items_per_box,
                    'box_price' => $product->effective_box_selling_price,
                    'box_price_display' => number_format($product->effective_box_selling_price, 0),
                    'stock' => $stock,
                    'has_stock' => $stock['total_items'] > 0,
                ];
            })
            ->toArray();

        $this->searchResults = $this->allStockProducts;
        $this->showSearchResults = true;
    }

    /**
     * Called on every keystroke in the search input.
     * Filters the already-loaded list — no new DB query needed.
     */
    public function updatedSearchQuery()
    {
        $q = mb_strtolower(trim($this->searchQuery));

        if ($q === '') {
            // Empty search: show everything
            $this->searchResults = $this->allStockProducts;
        } else {
            $this->searchResults = array_values(array_filter(
                $this->allStockProducts,
                function (array $product) use ($q) {
                    return str_contains(mb_strtolower($product['name']), $q)
                        || str_contains(mb_strtolower($product['sku']),  $q)
                        || str_contains(mb_strtolower($product['barcode'] ?? ''), $q);
                }
            ));
        }

        $this->showSearchResults = true;
    }

    public function closeSearch()
    {
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->allStockProducts = [];
        $this->showSearchResults = false;
    }

    public function searchProducts($query)
    {
        $this->searchResults = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(sku) LIKE ?', ['%' . strtolower($query) . '%'])
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->with('category')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $stock = $product->getCurrentStock('shop', $this->shopId);
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'category' => $product->category?->name,
                    'selling_price' => $product->selling_price,
                    'selling_price_display' => number_format($product->selling_price, 0),
                    'items_per_box' => $product->items_per_box,
                    'box_price' => $product->effective_box_selling_price,
                    'box_price_display' => number_format($product->effective_box_selling_price, 0),
                    'stock' => $stock,
                    'has_stock' => $stock['total_items'] > 0,
                ];
            })
            ->toArray();
    }

    public function selectProduct($productId)
    {
        $product = Product::with('category')->findOrFail($productId);
        $stock = $product->getCurrentStock('shop', $this->shopId);

        if ($stock['total_items'] === 0) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Product is out of stock'
            ]);
            return;
        }

        // Stage the product for modal
        $this->openAddModal($product, $stock);

        // Close search dropdown
        $this->showSearchResults = false;
        $this->searchQuery = '';
    }

    /**
     * Open the add modal with a product.
     * If the product already exists in cart, pre-fill with current values.
     */
    private function openAddModal(Product $product, array $stock)
    {
        $this->stagingProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category?->name,
            'category_id' => $product->category_id,
            'selling_price' => $product->selling_price,
            'items_per_box' => $product->items_per_box,
            'box_price' => $product->effective_box_selling_price,
        ];

        // Determine if individual item sales are allowed for this product's category
        $categoryId = $product->category_id;
        $individualAllowed = $this->settingAllowIndividualSales && (
            empty($this->settingIndividualCategoryIds)
            || in_array($categoryId, $this->settingIndividualCategoryIds)
        );
        $this->stagingProduct['individual_sale_allowed'] = $individualAllowed;

        $hasFullBox = Box::where('product_id', $product->id)
            ->where('location_type', 'shop')
            ->where('location_id', $this->shopId)
            ->where('status', BoxStatus::FULL)
            ->exists();
        $this->stagingProduct['has_full_box'] = $hasFullBox;

        $this->stagingStock = $stock;

        // Check if product already in cart
        $existingIndex = $this->findCartItemByProduct($product->id);

        if ($existingIndex !== false) {
            // Pre-fill with existing cart values (edit mode)
            $existingItem = $this->cart[$existingIndex];
            $this->stagingCartIndex = $existingIndex;
            // If item was full-box but no full boxes remain, switch to item mode
            $this->stagingMode = ($existingItem['is_full_box'] && $hasFullBox) ? 'box' : 'item';
            $this->stagingQty = $existingItem['quantity'];
            $this->stagingPrice = $existingItem['price'];
            $this->stagingPriceModified = $existingItem['price_modified'] ?? false;
            $this->stagingPriceReason = $existingItem['price_modification_reason'] ?? '';
        } else {
            // New item defaults — box mode only when full boxes exist
            $this->stagingCartIndex = null;
            $this->stagingMode  = $hasFullBox ? 'box' : 'item';
            $this->stagingQty   = 1;
            $this->stagingPrice = $hasFullBox
                ? $product->effective_box_selling_price
                : $product->selling_price;
            $this->stagingPriceModified = false;
            $this->stagingPriceReason = '';
        }

        $this->showAddModal = true;
    }

    /**
     * Find a cart item by product ID (regardless of box or mode).
     */
    private function findCartItemByProduct($productId)
    {
        foreach ($this->cart as $index => $item) {
            if ($item['product_id'] === $productId) {
                return $index;
            }
        }
        return false;
    }

    // ==================== BARCODE SCANNING ====================

    public function updatedBarcodeInput()
    {
        if (strlen($this->barcodeInput) < 3) {
            return;
        }

        $this->handleBarcodeScanned($this->barcodeInput);
        $this->barcodeInput = '';
    }

    public function handleBarcodeScanned($barcode)
    {
        \Log::info('handleBarcodeScanned called', [
            'barcode' => $barcode,
            'scanningEnabled' => $this->scanningEnabled
        ]);

        if (!$this->scanningEnabled) {
            \Log::warning('Scanning is disabled');
            return;
        }

        // Try to find product by barcode
        $product = $this->findProductByBarcode($barcode);

        \Log::info('Product lookup result', [
            'barcode' => $barcode,
            'found' => ! is_null($product),
            'product_id' => $product?->id ?? null,
            'product_name' => $product?->name ?? null
        ]);

        if (!$product) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => "Product not found for barcode: {$barcode}"
            ]);
            return;
        }

        // Check stock
        $stock = $product->getCurrentStock('shop', $this->shopId);
        \Log::info('Stock check', [
            'product_id' => $product->id,
            'shop_id' => $this->shopId,
            'stock' => $stock
        ]);

        if ($stock['total_items'] === 0) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => "{$product->name} is out of stock"
            ]);
            return;
        }

        // Open modal instead of adding directly
        \Log::info('Opening add modal for product', ['product_id' => $product->id]);
        $this->openAddModal($product, $stock);

        $this->dispatch('notification', [
            'type' => 'info',
            'message' => "Scanned: {$product->name}"
        ]);
    }

    private function findProductByBarcode($barcode)
    {
        // Try ProductBarcode table first
        $productBarcode = ProductBarcode::where('barcode', $barcode)
            ->where('is_active', true)
            ->with('product')
            ->first();

        if ($productBarcode && $productBarcode->product) {
            return $productBarcode->product;
        }

        // Fallback to Product.barcode
        return Product::where('barcode', $barcode)
            ->where('is_active', true)
            ->first();
    }

    // ==================== PHONE SCANNER SESSION ====================

    /**
     * Create a new scanner session and show the QR panel.
     */
    public function enablePhoneScanner(): void
    {
        // Deactivate any previous POS session for this user
        ScannerSession::where('user_id', auth()->id())
            ->where('page_type', 'pos')
            ->update(['is_active' => false]);

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

    /**
     * Deactivate the scanner session.
     */
    public function disablePhoneScanner(): void
    {
        if ($this->scannerSession) {
            $this->scannerSession->deactivate();
            $this->scannerSession = null;
        }
        $this->showScannerPanel = false;
    }

    /**
     * Called every 2 seconds by wire:poll in the blade.
     * Picks up any barcode the phone submitted via the API and processes it.
     */
    public function checkForScans(): void
    {
        // Debug: Check if method is being called
        \Log::info('checkForScans called', [
            'has_session' => ! is_null($this->scannerSession),
            'session_id' => $this->scannerSession?->id ?? null,
        ]);

        if (! $this->scannerSession) {
            \Log::warning('checkForScans: No scanner session');
            return;
        }

        $this->scannerSession->refresh();

        // Log the session state
        \Log::info('Scanner session state', [
            'id' => $this->scannerSession->id,
            'is_active' => $this->scannerSession->is_active,
            'last_scanned_barcode' => $this->scannerSession->last_scanned_barcode,
            'expires_at' => $this->scannerSession->expires_at,
        ]);

        // Only process if a barcode is waiting in the DB
        if (! $this->scannerSession->last_scanned_barcode) {
            return;
        }

        $barcode = $this->scannerSession->last_scanned_barcode;
        \Log::info('Processing barcode from scanner', ['barcode' => $barcode]);

        // Update connected status
        $this->lastProcessedScan = $barcode;

        try {
            // Feed into the existing barcode handling pipeline
            $this->handleBarcodeScanned($barcode);

            // Only clear from DB if processing was successful
            $this->scannerSession->update([
                'last_scanned_barcode' => null,
                'last_scan_at'         => now(),
            ]);

            \Log::info('Barcode processed successfully', ['barcode' => $barcode]);
        } catch (\Throwable $e) {
            \Log::error('Error processing barcode', [
                'barcode' => $barcode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Clear the barcode even on error to prevent infinite retry
            $this->scannerSession->update([
                'last_scanned_barcode' => null,
                'last_scan_at'         => now(),
            ]);

            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Scanner error: ' . $e->getMessage()
            ]);
        }
    }

    // ==================== CART MANAGEMENT ====================

    /**
     * Called when user changes mode in the add modal.
     * Updates the staged price accordingly.
     */
    public function updatedStagingMode()
    {
        if (!$this->stagingProduct) {
            return;
        }

        // Reset price to default for the selected mode
        $this->stagingPrice = $this->stagingMode === 'box'
            ? $this->stagingProduct['box_price']
            : $this->stagingProduct['selling_price'];

        // Price is always original after a mode switch
        $this->stagingPriceModified = false;
        $this->stagingPriceReason   = '';
    }

    /**
     * Reactive update when the price field is edited in the staging modal.
     * Marks the price as modified (or clears the flag if restored to original).
     */
    public function updatedStagingPrice(): void
    {
        if (! $this->stagingProduct) {
            return;
        }

        $originalPrice = $this->stagingMode === 'box'
            ? $this->stagingProduct['box_price']
            : $this->stagingProduct['selling_price'];

        $this->stagingPriceModified = ((int) $this->stagingPrice !== (int) $originalPrice);

        // Clear the reason when price is restored to original
        if (! $this->stagingPriceModified) {
            $this->stagingPriceReason = '';
        }
    }

    /**
     * Reactive update when quantity changes.
     */
    public function updatedStagingQty()
    {
        // Validate max quantity
        if ($this->stagingMode === 'item' && $this->stagingStock) {
            $maxItems = $this->stagingStock['total_items'];
            if ($this->stagingQty > $maxItems) {
                $this->stagingQty = $maxItems;
                $this->dispatch('notification', [
                    'type' => 'warning',
                    'message' => "Only {$maxItems} items available"
                ]);
            }
        }
    }

    /**
     * Commit the staged product to the cart.
     * Called when user clicks "Add to Cart" in the modal.
     */
    public function confirmAddToCart()
    {
        if (!$this->stagingProduct) {
            return;
        }

        // Block individual item sales if setting disallows it
        if (!($this->stagingProduct['individual_sale_allowed'] ?? true) && $this->stagingMode === 'item') {
            $this->dispatch('notification', [
                'type'    => 'error',
                'message' => 'Individual item sales are not allowed for this product category',
            ]);
            return;
        }

        // Block price override if setting disallows it
        if (!$this->settingAllowPriceOverride && $this->stagingPriceModified) {
            $this->dispatch('notification', [
                'type'    => 'error',
                'message' => 'Price modifications are not allowed',
            ]);
            return;
        }

        // Validate quantity
        if ($this->stagingQty < 1) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Quantity must be at least 1'
            ]);
            return;
        }

        // Get the first available box (FIFO)
        $box = Box::where('product_id', $this->stagingProduct['id'])
            ->where('location_type', 'shop')
            ->where('location_id', $this->shopId)
            ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
            ->where('items_remaining', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->first();

        if (!$box) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'No available boxes for this product'
            ]);
            return;
        }

        // Validate quantity against box availability
        if ($this->stagingMode === 'box') {
            // Only full boxes can be sold as a box unit — partial boxes must be sold as individual items
            $availableFullBoxes = Box::where('product_id', $this->stagingProduct['id'])
                ->where('location_type', 'shop')
                ->where('location_id', $this->shopId)
                ->where('status', BoxStatus::FULL)
                ->count();
            if ($this->stagingQty > $availableFullBoxes) {
                $this->dispatch('notification', [
                    'type'    => 'error',
                    'message' => $availableFullBoxes === 0
                        ? 'No full boxes available — sell remaining items individually'
                        : 'Only ' . $availableFullBoxes . ' full box' . ($availableFullBoxes === 1 ? '' : 'es') . ' available',
                ]);
                return;
            }
        } else {
            // For item mode, validate against total available items
            if ($this->stagingQty > $this->stagingStock['total_items']) {
                $this->dispatch('notification', [
                    'type' => 'error',
                    'message' => 'Only ' . $this->stagingStock['total_items'] . ' items available'
                ]);
                return;
            }
        }

        $isFullBox = $this->stagingMode === 'box';
        $lineTotal = $this->stagingPrice * $this->stagingQty;
        $originalPrice = $isFullBox
            ? $this->stagingProduct['box_price']
            : $this->stagingProduct['selling_price'];

        $cartItem = [
            'product_id' => $this->stagingProduct['id'],
            'product_name' => $this->stagingProduct['name'],
            'box_id' => $box->id,
            'box_code' => $box->box_code,
            'quantity' => $this->stagingQty,
            'is_full_box' => $isFullBox,
            'items_per_box' => $this->stagingProduct['items_per_box'],
            'price' => $this->stagingPrice,
            'original_price' => $originalPrice,
            'line_total' => $lineTotal,
            'price_modified' => $this->stagingPriceModified,
            'price_modification_reason' => $this->stagingPriceReason ?: null,
            'requires_owner_approval' => false,
        ];

        // Check if price was modified and requires approval
        if ($this->stagingPriceModified) {
            $percentageChange = (($originalPrice - $this->stagingPrice) / $originalPrice) * 100;
            $cartItem['requires_owner_approval'] = $percentageChange > $this->settingPriceOverrideThreshold;
        }

        // If editing existing cart item, replace it
        if ($this->stagingCartIndex !== null && isset($this->cart[$this->stagingCartIndex])) {
            $this->cart[$this->stagingCartIndex] = $cartItem;
            $message = 'Cart item updated';
        } else {
            // Add new item to cart
            $this->cart[] = $cartItem;
            $message = 'Added to cart';
        }

        $this->calculateCartTotal();
        $this->closeAddModal();

        $this->dispatch('notification', [
            'type' => 'success',
            'message' => $message
        ]);
    }

    /**
     * Open the add modal to edit an existing cart item.
     */
    public function openEditItem($index)
    {
        if (!isset($this->cart[$index])) {
            return;
        }

        $item = $this->cart[$index];
        $product = Product::with('category')->findOrFail($item['product_id']);
        $stock = $product->getCurrentStock('shop', $this->shopId);

        $this->stagingProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category?->name,
            'selling_price' => $product->selling_price,
            'items_per_box' => $product->items_per_box,
            'box_price' => $product->effective_box_selling_price,
        ];

        $this->stagingStock = $stock;
        $this->stagingCartIndex = $index;
        $this->stagingMode = $item['is_full_box'] ? 'box' : 'item';
        $this->stagingQty = $item['quantity'];
        $this->stagingPrice = $item['price'];
        $this->stagingPriceModified = $item['price_modified'] ?? false;
        $this->stagingPriceReason = $item['price_modification_reason'] ?? '';

        $this->showAddModal = true;
    }

    /**
     * Close the add modal and reset staging state.
     */
    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->stagingProduct = null;
        $this->stagingStock = [];
        $this->stagingMode = 'box';
        $this->stagingQty = 1;
        $this->stagingPrice = 0;
        $this->stagingPriceModified = false;
        $this->stagingPriceReason = '';
        $this->stagingCartIndex = null;
    }



    public function removeCartItem($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart); // Re-index
        $this->calculateCartTotal();
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->cartTotal = 0;

        // Reset customer selection
        $this->selectedCustomerId = null;
        $this->selectedCustomerName = '';
        $this->selectedCustomerPhone = '';
        $this->selectedCustomerOutstandingBalance = 0;
        $this->customerSearch = '';
        $this->customerResults = [];
        $this->showCustomerSearch = false;

        // Reset payment amounts
        $this->payAmt_cash = 0;
        $this->payAmt_card = 0;
        $this->payAmt_mobile_money = 0;
        $this->payAmt_bank_transfer = 0;
        $this->payAmt_credit = 0;
        $this->payRef_card = '';
        $this->payRef_mobile_money = '';
        $this->payRef_bank_transfer = '';
        $this->creditWarningVisible = false;
        $this->creditWarningMessage = '';
    }

    private function calculateCartTotal()
    {
        $this->cartTotal = array_sum(array_column($this->cart, 'line_total'));
    }

    // ==================== PRICE MODIFICATION ====================

    public function openPriceModal($index)
    {
        if (!isset($this->cart[$index])) {
            return;
        }

        $this->priceModificationCartIndex = $index;
        $this->newPrice = $this->cart[$index]['price'];
        $this->priceModificationReason = '';
        $this->priceModificationReference = '';
        $this->requiresOwnerApproval = false;
        $this->showPriceModal = true;
    }

    public function applyPriceModification()
    {
        $this->validate([
            'newPrice' => 'required|numeric|min:0',
            'priceModificationReason' => 'required|string|min:3',
            'priceModificationReference' => 'nullable|string',
        ]);

        $index = $this->priceModificationCartIndex;
        $newPrice      = (int) $this->newPrice;
        $originalPrice = $this->cart[$index]['original_price'];

        // Check if price decreased by more than 20%
        $percentageChange = $originalPrice > 0
            ? (($originalPrice - $newPrice) / $originalPrice) * 100
            : 0;
        $requiresApproval = $percentageChange > $this->settingPriceOverrideThreshold;

        $this->cart[$index]['price'] = $newPrice;
        $this->cart[$index]['line_total'] = $newPrice * $this->cart[$index]['quantity'];
        $this->cart[$index]['price_modified'] = true;
        $this->cart[$index]['price_modification_reason'] = $this->priceModificationReason;
        $this->cart[$index]['price_modification_reference'] = $this->priceModificationReference;
        $this->cart[$index]['requires_owner_approval'] = $requiresApproval;

        $this->calculateCartTotal();
        $this->showPriceModal = false;

        if ($requiresApproval) {
            $this->dispatch('notification', [
                'type' => 'warning',
                'message' => 'Price change >' . $this->settingPriceOverrideThreshold . '% requires owner approval'
            ]);
        } else {
            $this->dispatch('notification', [
                'type' => 'success',
                'message' => 'Price modified successfully'
            ]);
        }
    }

    // ==================== CUSTOMER SEARCH & SELECTION ====================

    /**
     * Reactive search for customers by phone or name.
     */
    public function updatedCustomerSearch()
    {
        $q = trim($this->customerSearch);

        if (strlen($q) < 2) {
            $this->customerResults = [];
            return;
        }

        $this->customerResults    = \App\Models\Customer::search($q)->toArray();
        $this->showCustomerSearch = true;
    }

    /**
     * User clicked a customer from the dropdown.
     */
    public function selectCustomer(int $customerId)
    {
        $customer = \App\Models\Customer::find($customerId);
        if (!$customer) {
            return;
        }

        $this->selectedCustomerId = $customer->id;
        $this->selectedCustomerName = $customer->name;
        $this->selectedCustomerPhone = $customer->phone;
        $this->selectedCustomerOutstandingBalance = $customer->outstanding_balance;

        $this->customerSearch = '';
        $this->customerResults = [];
        $this->showCustomerSearch = false;
        $this->showNewCustomerForm = false;

        // If credit was entered and customer has outstanding balance, re-evaluate warning
        if ($this->payAmt_credit > 0) {
            $this->evaluateCreditWarning();
        }
    }

    /**
     * Clear selected customer.
     */
    public function clearCustomer()
    {
        $this->selectedCustomerId = null;
        $this->selectedCustomerName = '';
        $this->selectedCustomerPhone = '';
        $this->selectedCustomerOutstandingBalance = 0;
        $this->customerSearch = '';
        $this->customerResults = [];
        $this->showNewCustomerForm = false;

        // If credit was allocated, clear warning since no customer is selected
        if ($this->payAmt_credit > 0) {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
        }
    }

    /**
     * Show the inline form to create a new customer.
     */
    public function showCreateCustomerForm()
    {
        $this->showNewCustomerForm = true;
        $this->showCustomerSearch = false;
        $this->customerResults = [];

        // Pre-fill phone if user typed a phone number in search
        if (preg_match('/^\d+$/', trim($this->customerSearch))) {
            $this->newCustomerPhone = trim($this->customerSearch);
        }
    }

    /**
     * Create and auto-select a new customer.
     */
    public function saveNewCustomer()
    {
        $this->validate([
            'newCustomerName'  => 'required|string|min:2|max:100',
            'newCustomerPhone' => 'required|string|min:10|max:20|unique:customers,phone',
            'newCustomerEmail' => 'nullable|email|max:100',
            'newCustomerNotes' => 'nullable|string|max:500',
        ]);

        $customerService = new \App\Services\Sales\CustomerService();

        $customer = $customerService->create([
            'name'  => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail,
            'notes' => $this->newCustomerNotes,
        ], $this->shopId);

        // Auto-select the newly created customer
        $this->selectCustomer($customer->id);

        // Reset form
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
        $this->newCustomerNotes = '';
        $this->showNewCustomerForm = false;

        $this->dispatch('notification', [
            'type' => 'success',
            'message' => 'Customer registered successfully'
        ]);
    }

    /**
     * Cancel new customer form.
     */
    public function cancelNewCustomer()
    {
        $this->showNewCustomerForm = false;
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
        $this->newCustomerNotes = '';
    }

    // ==================== PAYMENT PANEL ====================

    /**
     * Reactive hook when credit amount changes.
     * Validates that a customer is selected and checks for outstanding balance.
     */
    public function updatedPayAmtCredit()
    {
        if ($this->payAmt_credit <= 0) {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
            return;
        }

        // Hard block: credit sales disabled by owner
        if (!$this->settingAllowCreditSales) {
            $this->payAmt_credit = 0;
            $this->dispatch('notification', [
                'type'    => 'error',
                'message' => 'Credit sales are disabled by the owner',
            ]);
            return;
        }

        // Hard block: customer required for credit
        if ($this->settingCreditRequiresCustomer && !$this->selectedCustomerId) {
            $this->payAmt_credit = 0;
            $this->dispatch('notification', [
                'type'    => 'warning',
                'message' => 'A registered customer must be selected before using credit',
            ]);
            return;
        }

        // Hard block: max credit per customer
        if ($this->settingMaxCreditPerCustomer > 0 && $this->selectedCustomerId) {
            $customer = \App\Models\Customer::find($this->selectedCustomerId);
            if ($customer) {
                $projectedBalance = $customer->outstanding_balance + $this->payAmt_credit;
                if ($projectedBalance > $this->settingMaxCreditPerCustomer) {
                    $remaining = max(0, $this->settingMaxCreditPerCustomer - $customer->outstanding_balance);
                    $this->payAmt_credit = $remaining;
                    $this->dispatch('notification', [
                        'type'    => 'warning',
                        'message' => 'Credit limit reached. Maximum remaining credit for this customer: '
                            . number_format($remaining) . ' RWF',
                    ]);
                }
            }
        }

        $this->evaluateCreditWarning();
        $this->autoAdjustCash();
    }

    /**
     * Check if selected customer has outstanding balance and show warning.
     */
    private function evaluateCreditWarning()
    {
        if (!$this->selectedCustomerId || $this->payAmt_credit <= 0) {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
            return;
        }

        $customer = \App\Models\Customer::find($this->selectedCustomerId);
        if (!$customer) {
            return;
        }

        if ($customer->outstanding_balance > 0) {
            $this->creditWarningVisible = true;
            $this->creditWarningMessage = "Customer has outstanding credit balance of " .
                number_format($customer->outstanding_balance, 0) . " RWF";
        } else {
            $this->creditWarningVisible = false;
            $this->creditWarningMessage = '';
        }
    }

    /**
     * Auto-set cash to cover whatever non-cash methods don't cover.
     * Called every time card, MoMo, bank, or credit changes.
     */
    private function autoAdjustCash(): void
    {
        $nonCash = (int) $this->payAmt_card
            + (int) $this->payAmt_mobile_money
            + (int) $this->payAmt_bank_transfer
            + (int) $this->payAmt_credit;

        $this->payAmt_cash = max(0, $this->cartTotal - $nonCash);
    }

    public function updatedPayAmtCard(): void
    {
        $this->autoAdjustCash();
    }

    public function updatedPayAmtMobileMoney(): void
    {
        $this->autoAdjustCash();
    }

    public function updatedPayAmtBankTransfer(): void
    {
        $this->autoAdjustCash();
    }

    /**
     * Single-request checkout: sync Alpine payment amounts then complete the sale.
     */
    public function checkout(int $card, int $momo, int $bank, int $credit): void
    {
        $this->payAmt_card          = max(0, $card);
        $this->payAmt_mobile_money  = max(0, $momo);
        $this->payAmt_bank_transfer = max(0, $bank);
        $this->payAmt_credit        = max(0, $credit);
        $this->autoAdjustCash();
        $this->completeSale();
    }

    /**
     * Computed: total amount allocated across all payment channels.
     */
    public function getTotalAllocatedProperty(): int
    {
        return $this->payAmt_cash
            + $this->payAmt_card
            + $this->payAmt_mobile_money
            + $this->payAmt_bank_transfer
            + $this->payAmt_credit;
    }

    /**
     * Computed: how much is left to pay.
     */
    public function getRemainingBalanceProperty(): int
    {
        return max(0, $this->cartTotal - $this->totalAllocated);
    }

    // ==================== CHECKOUT ====================

    public function openCheckout()
    {
        if (empty($this->cart)) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Cart is empty'
            ]);
            return;
        }

        // Check if any items require owner approval
        $needsApproval = collect($this->cart)->contains('requires_owner_approval', true);

        if ($needsApproval) {
            // Check whether the held sale being resumed was actually approved by the owner
            $resumeApproved = false;
            if ($this->resumingFromHeld !== null) {
                $heldRecord = HeldSale::find($this->resumingFromHeld);
                $resumeApproved = $heldRecord && $heldRecord->isApproved();
            }

            if (! $resumeApproved) {
                $this->dispatch('notification', [
                    'type'    => 'warning',
                    'message' => $this->resumingFromHeld
                        ? 'This sale is still waiting for owner approval. You will be notified when it is approved.'
                        : 'This sale has price overrides that need owner approval. Use "Hold for Approval".',
                ]);
                return;
            }
        }

        // Reset payment panel — default cash to full total
        $this->payAmt_cash = $this->cartTotal;
        $this->payAmt_card = 0;
        $this->payAmt_mobile_money = 0;
        $this->payAmt_bank_transfer = 0;
        $this->payAmt_credit = 0;

        // Enforce settings
        if (!$this->settingAllowCreditSales) {
            $this->payAmt_credit = 0;
        }
        $this->payRef_card = '';
        $this->payRef_mobile_money = '';
        $this->payRef_bank_transfer = '';
        $this->creditWarningVisible = false;
        $this->creditWarningMessage = '';
        $this->notes = '';

        $this->showCheckoutModal = true;
    }

    public function completeSale()
    {
        $this->validate([
            'notes' => 'nullable|string',
        ]);

        if (empty($this->cart)) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Cart is empty'
            ]);
            return;
        }

        // Belt-and-braces: block if approval-required items haven't been approved
        $needsApproval = collect($this->cart)->contains('requires_owner_approval', true);
        if ($needsApproval) {
            $resumeApproved = false;
            if ($this->resumingFromHeld !== null) {
                $heldRecord = HeldSale::find($this->resumingFromHeld);
                $resumeApproved = $heldRecord && $heldRecord->isApproved();
            }
            if (! $resumeApproved) {
                $this->dispatch('notification', [
                    'type'    => 'error',
                    'message' => 'Cannot complete sale: price override has not been approved by the owner.',
                ]);
                return;
            }
        }

        // Validate payment coverage
        if ($this->totalAllocated < $this->cartTotal) {
            $shortfall = $this->cartTotal - $this->totalAllocated;
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Payment does not cover total. Missing ' . number_format($shortfall, 0) . ' RWF'
            ]);
            return;
        }

        if ($this->totalAllocated > $this->cartTotal) {
            $overpayment = $this->totalAllocated - $this->cartTotal;
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Payment exceeds total by ' . number_format($overpayment, 0) . ' RWF'
            ]);
            return;
        }

        // Belt-and-braces: credit disabled by setting
        if ($this->payAmt_credit > 0 && !$this->settingAllowCreditSales) {
            $this->dispatch('notification', [
                'type' => 'error', 'message' => 'Credit sales are disabled',
            ]);
            return;
        }

        // Belt-and-braces: customer required for credit
        if ($this->payAmt_credit > 0
            && $this->settingCreditRequiresCustomer
            && !$this->selectedCustomerId) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'A registered customer must be selected for credit sales',
            ]);
            return;
        }

        // Belt-and-braces: max credit per customer
        if ($this->payAmt_credit > 0
            && $this->settingMaxCreditPerCustomer > 0
            && $this->selectedCustomerId) {
            $customer = \App\Models\Customer::find($this->selectedCustomerId);
            if ($customer) {
                $projected = $customer->outstanding_balance + $this->payAmt_credit;
                if ($projected > $this->settingMaxCreditPerCustomer) {
                    $this->dispatch('notification', [
                        'type' => 'error',
                        'message' => 'This sale would exceed the customer\'s credit limit of '
                            . number_format($this->settingMaxCreditPerCustomer) . ' RWF',
                    ]);
                    return;
                }
            }
        }

        try {
            $saleService = new SaleService();

            // Determine sale type
            $hasFullBox = collect($this->cart)->contains('is_full_box', true);
            $hasIndividual = collect($this->cart)->contains('is_full_box', false);

            if ($hasFullBox && $hasIndividual) {
                $saleType = SaleType::MIXED;
            } elseif ($hasFullBox) {
                $saleType = SaleType::FULL_BOX;
            } else {
                $saleType = SaleType::INDIVIDUAL_ITEMS;
            }

            // Build payment channels array from the five input fields
            $payments = [];

            if ($this->payAmt_cash > 0) {
                $payments[] = ['method' => 'cash', 'amount' => $this->payAmt_cash, 'reference' => null];
            }
            if ($this->payAmt_card > 0) {
                $payments[] = ['method' => 'card', 'amount' => $this->payAmt_card, 'reference' => $this->payRef_card];
            }
            if ($this->payAmt_mobile_money > 0) {
                $payments[] = ['method' => 'mobile_money', 'amount' => $this->payAmt_mobile_money, 'reference' => $this->payRef_mobile_money];
            }
            if ($this->payAmt_bank_transfer > 0) {
                $payments[] = ['method' => 'bank_transfer', 'amount' => $this->payAmt_bank_transfer, 'reference' => $this->payRef_bank_transfer];
            }
            if ($this->payAmt_credit > 0) {
                $payments[] = ['method' => 'credit', 'amount' => $this->payAmt_credit, 'reference' => null];
            }

            // Prepare items for SaleService
            $items = collect($this->cart)->map(function ($item) {
                $isFullBox   = $item['is_full_box'];
                $itemsToSell = $isFullBox
                    ? (int) $item['quantity'] * (int) ($item['items_per_box'] ?? 1)
                    : (int) $item['quantity'];

                return [
                    'product_id'                   => $item['product_id'],
                    'quantity'                     => $itemsToSell,
                    'is_full_box'                  => $isFullBox,
                    'price'                        => (int) $item['price'],
                    'price_modification_reason'    => $item['price_modification_reason'] ?? null,
                    'price_modification_reference' => $item['price_modification_reference'] ?? null,
                ];
            })->toArray();

            $sale = $saleService->createSale([
                'shop_id'       => $this->shopId,
                'type'          => $saleType,
                'payments'      => $payments,
                'payment_method' => count($payments) === 1 ? $payments[0]['method'] : 'cash',
                'customer_id'   => $this->selectedCustomerId,
                'customer_name' => $this->selectedCustomerName ?: null,
                'customer_phone' => $this->selectedCustomerPhone ?: null,
                'notes'         => $this->notes ?: null,
                'items'         => $items,
            ]);

            $this->completedSale = Sale::with(['items.product', 'items.box', 'soldBy', 'shop', 'payments'])
                ->find($sale->id);

            // Clean up held sale record if this was a resume; copy approval onto sale
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

            // Refresh the product list so sold-out products disappear immediately
            $this->allStockProducts = [];
            $this->searchResults    = [];

            $this->clearCart();
            $this->showCheckoutModal = false;
            $this->showReceiptModal = true;

            $this->dispatch('notification', [
                'type' => 'success',
                'message' => 'Sale completed successfully!'
            ]);

        } catch (\Throwable $e) {
            \Log::error('Error completing sale', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Error completing sale: ' . $e->getMessage()
            ]);
        }
    }

    public function printReceipt()
    {
        if (! $this->completedSale) {
            return;
        }
        $url = route('shop.receipt.print', $this->completedSale->id);
        $this->dispatch('open-print-window', url: $url);
    }

    public function closeReceipt()
    {
        $this->showReceiptModal = false;
        $this->completedSale = null;
    }

    // ==================== SHOP SELECTION (Owners only) ====================

    public function selectShopFromModal()
    {
        if (!$this->isOwner || !$this->shopId) {
            return;
        }

        // Update session
        session(['selected_shop_id' => $this->shopId]);

        // Update shop name
        $shop = Shop::find($this->shopId);
        $this->shopName = $shop->name ?? 'Unknown Shop';

        // Close modal
        $this->showShopSelectionModal = false;

        $this->dispatch('notification', [
            'type' => 'success',
            'message' => "Now operating at {$this->shopName}"
        ]);
    }

    public function changeShop()
    {
        if (!$this->isOwner) {
            return;
        }

        // Clear cart when changing shops
        if (!empty($this->cart)) {
            $this->clearCart();
        }

        // Update session
        session(['selected_shop_id' => $this->shopId]);

        // Update shop name
        $shop = Shop::find($this->shopId);
        $this->shopName = $shop->name ?? 'Unknown Shop';

        $this->dispatch('notification', [
            'type' => 'success',
            'message' => "Switched to {$this->shopName}"
        ]);
    }

    // ==================== HELD SALES ====================

    public function loadHeldSales(): void
    {
        if (! $this->shopId) {
            $this->heldSales = [];
            return;
        }

        $this->heldSales = HeldSale::where('shop_id', $this->shopId)
            ->whereNull('override_rejected_at')
            ->with(['seller', 'approvedBy'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($h) => [
                'id'              => $h->id,
                'reference'       => $h->hold_reference,
                'item_count'      => $h->item_count,
                'cart_total'      => $h->cart_total,
                'customer_name'   => $h->customer_name,
                'seller_name'     => $h->seller->name,
                'needs_approval'  => $h->needs_price_approval,
                'is_approved'     => $h->isApproved(),
                'approved_by'     => $h->approvedBy?->name,
                'approval_note'   => $h->approval_note,
                'age'             => $h->created_at->diffForHumans(),
                'is_mine'         => $h->seller_id === auth()->id(),
                'cart_preview'  => collect($h->cart_data ?? [])->take(3)->map(fn($item) => [
                    'name'           => $item['product_name'] ?? '—',
                    'qty'            => $item['quantity'] ?? 1,
                    'is_full_box'    => $item['is_full_box'] ?? false,
                    'price'          => $item['price'] ?? 0,
                    'original_price' => $item['original_price'] ?? $item['price'] ?? 0,
                    'modified'       => $item['price_modified'] ?? false,
                ])->toArray(),
                'cart_extra'    => max(0, count($h->cart_data ?? []) - 3),
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
        $this->resumingFromHeld = null;
        $this->loadHeldSales();
        $this->dispatch('notification', ['type' => 'success', 'message' => 'Cart saved — resume it from the held sales panel.']);
    }

    public function holdSale(): void
    {
        if (empty($this->cart)) return;

        $needsApproval = collect($this->cart)->contains('requires_owner_approval', true);

        $nextId = (HeldSale::max('id') ?? 0) + 1;
        $held = HeldSale::create([
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
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()->name,
            'action'            => 'sale_held',
            'entity_type'       => 'HeldSale',
            'entity_id'         => $held->id,
            'entity_identifier' => $held->hold_reference,
            'details'           => ['cart_total' => $this->cartTotal, 'needs_approval' => $needsApproval],
            'ip_address'        => request()->ip(),
        ]);

        // Create a persistent alert so the owner is notified even outside the dashboard
        if ($needsApproval) {
            Alert::create([
                'title'        => 'Price Override Needs Approval',
                'message'      => "{$held->hold_reference} · " . auth()->user()->name
                                . " · {$this->shopName} · " . number_format($this->cartTotal) . ' RWF',
                'severity'     => 'warning',
                'entity_type'  => 'HeldSale',
                'entity_id'    => $held->id,
                'is_resolved'  => false,
                'is_dismissed' => false,
                'action_url'   => route('owner.dashboard'),
                'action_label' => 'Review on Dashboard',
            ]);
        }

        $this->cart              = [];
        $this->cartTotal         = 0;
        $this->resumingFromHeld  = null;
        $this->showCheckoutModal = false;
        $this->clearCart();
        $this->loadHeldSales();

        $msg = $needsApproval
            ? "Sale held ({$held->hold_reference}). Owner has been notified for price approval."
            : "Sale held ({$held->hold_reference}). Resume it anytime.";

        $this->dispatch('notification', ['type' => 'success', 'message' => $msg]);
    }

    public function resumeHeldSale(int $id): void
    {
        $held = HeldSale::find($id);
        if (! $held || $held->shop_id != $this->shopId) return;

        if ($held->isRejected()) {
            $this->dispatch('notification', [
                'type'    => 'error',
                'message' => "Sale {$held->hold_reference} was rejected: {$held->rejected_reason}",
            ]);
            return;
        }

        $cart = $held->cart_data;
        if ($held->isApproved()) {
            $cart = array_map(function ($item) {
                $item['requires_owner_approval'] = false;
                return $item;
            }, $cart);
        }

        $this->cart                 = $cart;
        $this->cartTotal            = $held->cart_total;
        $this->resumingFromHeld     = $held->id;
        $this->selectedCustomerName  = $held->customer_name ?? '';
        $this->selectedCustomerPhone = $held->customer_phone ?? '';
        if ($held->customer_id) {
            $this->selectedCustomerId = $held->customer_id;
        }
        $this->showHeldPanel = false;
        $this->loadHeldSales();
    }

    public function discardHeldSale(int $id): void
    {
        $held = HeldSale::find($id);
        if (! $held || $held->shop_id != $this->shopId) return;

        $held->delete();

        if ($this->resumingFromHeld === $id) {
            $this->resumingFromHeld = null;
            $this->cart             = [];
            $this->cartTotal        = 0;
        }

        $this->loadHeldSales();
        $this->dispatch('notification', ['type' => 'info', 'message' => 'Held sale discarded.']);
    }

    public function checkApprovals(): void
    {
        $prevIds = collect($this->heldSales)
            ->filter(fn($h) => ! $h['is_approved'])
            ->pluck('id')
            ->toArray();

        $this->loadHeldSales();

        $nowApproved = collect($this->heldSales)
            ->filter(fn($h) => $h['is_approved'] && in_array($h['id'], $prevIds));

        foreach ($nowApproved as $h) {
            $this->dispatch('notification', [
                'type'    => 'success',
                'message' => "{$h['reference']} approved by {$h['approved_by']}! Tap to resume.",
            ]);
        }
    }

    // ==================== UI HELPERS ====================

    public function closePriceModal()
    {
        $this->showPriceModal = false;
    }

    public function closeCheckoutModal()
    {
        $this->showCheckoutModal = false;
    }

    public function render()
    {
        return view('livewire.shop.sales.point-of-sale')
            ->layout('components.layouts.app');
    }
}
