<?php

namespace App\Livewire\Shop\Sales;

use App\Enums\BoxStatus;
use App\Enums\PaymentMethod;
use App\Enums\SaleType;
use App\Models\Box;
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
    // Shop
    public $shopId;
    public $shopName;
    public $availableShops = [];
    public $isOwner = false;

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
    public $paymentMethod = 'cash';
    public $customerName = '';
    public $customerPhone = '';
    public $amountReceived = 0;
    public $changeAmount = 0;
    public $notes = '';

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

            // Use session, request parameter, or first shop
            $this->shopId = request()->get('shop_id')
                ?? session('selected_shop_id')
                ?? Shop::first()?->id;

            if ($this->shopId) {
                session(['selected_shop_id' => $this->shopId]);
            }
        }

        if (!$this->shopId) {
            abort(404, 'No shop found. Please create a shop first.');
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
                    'selling_price_display' => number_format($product->selling_price / 100, 0),
                    'items_per_box' => $product->items_per_box,
                    'box_price' => $product->calculateBoxPrice(),
                    'box_price_display' => number_format($product->calculateBoxPrice() / 100, 0),
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
                    'selling_price_display' => number_format($product->selling_price / 100, 0),
                    'items_per_box' => $product->items_per_box,
                    'box_price' => $product->calculateBoxPrice(),
                    'box_price_display' => number_format($product->calculateBoxPrice() / 100, 0),
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
            'selling_price' => $product->selling_price,
            'items_per_box' => $product->items_per_box,
            'box_price' => $product->calculateBoxPrice(),
        ];

        $this->stagingStock = $stock;

        // Check if product already in cart
        $existingIndex = $this->findCartItemByProduct($product->id);

        if ($existingIndex !== false) {
            // Pre-fill with existing cart values (edit mode)
            $existingItem = $this->cart[$existingIndex];
            $this->stagingCartIndex = $existingIndex;
            $this->stagingMode = $existingItem['is_full_box'] ? 'box' : 'item';
            $this->stagingQty = $existingItem['quantity'];
            $this->stagingPrice = $existingItem['price'];
            $this->stagingPriceModified = $existingItem['price_modified'] ?? false;
            $this->stagingPriceReason = $existingItem['price_modification_reason'] ?? '';
        } else {
            // New item defaults
            $this->stagingCartIndex = null;
            $this->stagingMode = 'box'; // default to boxes
            $this->stagingQty = 1;
            $this->stagingPrice = $product->calculateBoxPrice();
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

        // Only update price if it hasn't been manually modified
        if (!$this->stagingPriceModified) {
            if ($this->stagingMode === 'box') {
                $this->stagingPrice = $this->stagingProduct['box_price'];
            } else {
                $this->stagingPrice = $this->stagingProduct['selling_price'];
            }
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
            // For box mode, quantity represents number of full boxes
            $totalItemsNeeded = $this->stagingQty * $this->stagingProduct['items_per_box'];
            if ($totalItemsNeeded > $this->stagingStock['total_items']) {
                $this->dispatch('notification', [
                    'type' => 'error',
                    'message' => 'Insufficient stock for requested boxes'
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
            $cartItem['requires_owner_approval'] = $percentageChange > 20;
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
            'box_price' => $product->calculateBoxPrice(),
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
        $this->newPrice = $this->cart[$index]['price'] / 100; // Convert to RWF
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
        $newPriceCents = $this->newPrice * 100;
        $originalPrice = $this->cart[$index]['original_price'];

        // Check if price decreased by more than 20%
        $percentageChange = (($originalPrice - $newPriceCents) / $originalPrice) * 100;
        $requiresApproval = $percentageChange > 20;

        $this->cart[$index]['price'] = $newPriceCents;
        $this->cart[$index]['line_total'] = $newPriceCents * $this->cart[$index]['quantity'];
        $this->cart[$index]['price_modified'] = true;
        $this->cart[$index]['price_modification_reason'] = $this->priceModificationReason;
        $this->cart[$index]['price_modification_reference'] = $this->priceModificationReference;
        $this->cart[$index]['requires_owner_approval'] = $requiresApproval;

        $this->calculateCartTotal();
        $this->showPriceModal = false;

        if ($requiresApproval) {
            $this->dispatch('notification', [
                'type' => 'warning',
                'message' => 'Price change >20% requires owner approval'
            ]);
        } else {
            $this->dispatch('notification', [
                'type' => 'success',
                'message' => 'Price modified successfully'
            ]);
        }
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
        $requiresApproval = collect($this->cart)->contains('requires_owner_approval', true);

        if ($requiresApproval) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Some items require owner approval before checkout'
            ]);
            return;
        }

        $this->paymentMethod = 'cash';
        $this->customerName = '';
        $this->customerPhone = '';
        $this->amountReceived = $this->cartTotal / 100;
        $this->notes = '';
        $this->showCheckoutModal = true;
    }

    public function updatedAmountReceived()
    {
        $this->changeAmount = max(0, $this->amountReceived - ($this->cartTotal / 100));
    }

    public function completeSale()
    {
        $this->validate([
            'paymentMethod' => 'required|in:' . implode(',', array_column(PaymentMethod::cases(), 'value')),
            'customerName' => 'nullable|string|max:255',
            'customerPhone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        if (empty($this->cart)) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Cart is empty'
            ]);
            return;
        }

        // Validate payment
        if ($this->paymentMethod === 'cash' && $this->amountReceived < ($this->cartTotal / 100)) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Insufficient amount received'
            ]);
            return;
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
                'shop_id' => $this->shopId,
                'type' => $saleType,
                'payment_method' => PaymentMethod::from($this->paymentMethod),
                'customer_name' => $this->customerName ?: null,
                'customer_phone' => $this->customerPhone ?: null,
                'notes' => $this->notes ?: null,
                'items' => $items,
            ]);

            $this->completedSale = Sale::with(['items.product', 'items.box', 'soldBy', 'shop'])
                ->find($sale->id);

            $this->clearCart();
            $this->showCheckoutModal = false;
            $this->showReceiptModal = true;

            $this->dispatch('notification', [
                'type' => 'success',
                'message' => 'Sale completed successfully!'
            ]);

        } catch (\Throwable $e) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Error completing sale: ' . $e->getMessage()
            ]);
        }
    }

    public function printReceipt()
    {
        // Trigger browser print dialog via JavaScript
        $this->dispatch('print-receipt');
    }

    public function closeReceipt()
    {
        $this->showReceiptModal = false;
        $this->completedSale = null;
    }

    // ==================== SHOP SELECTION (Owners only) ====================

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
