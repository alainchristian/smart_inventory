<?php

namespace App\Livewire\Shop\Sales;

use App\Enums\BoxStatus;
use App\Enums\PaymentMethod;
use App\Enums\SaleType;
use App\Models\Box;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\Sale;
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
    public $showSearchResults = false;

    // Add Item Modal
    public $showAddItemModal = false;
    public $selectedProduct = null;
    public $selectedProductBoxes = [];
    public $addItemMode = 'individual'; // 'individual' or 'full_box'
    public $addItemQuantity = 1;
    public $addItemBoxId = null;

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
    }

    // ==================== SEARCH ====================

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            $this->showSearchResults = false;
            return;
        }

        $this->searchProducts($this->searchQuery);
        $this->showSearchResults = true;
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

        $this->selectedProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category?->name,
            'selling_price' => $product->selling_price,
            'selling_price_display' => number_format($product->selling_price / 100, 0),
            'items_per_box' => $product->items_per_box,
            'box_price' => $product->calculateBoxPrice(),
            'box_price_display' => number_format($product->calculateBoxPrice() / 100, 0),
            'stock' => $stock,
        ];

        // Load available boxes
        $this->selectedProductBoxes = Box::where('product_id', $product->id)
            ->where('location_type', 'shop')
            ->where('location_id', $this->shopId)
            ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
            ->where('items_remaining', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($box) {
                return [
                    'id' => $box->id,
                    'box_code' => $box->box_code,
                    'items_remaining' => $box->items_remaining,
                    'items_total' => $box->items_total,
                    'status' => $box->status->label(),
                    'batch_number' => $box->batch_number,
                    'expiry_date' => $box->expiry_date?->format('Y-m-d'),
                ];
            })
            ->toArray();

        $this->addItemMode = 'individual';
        $this->addItemQuantity = 1;
        $this->addItemBoxId = $this->selectedProductBoxes[0]['id'] ?? null;
        $this->showAddItemModal = true;
        $this->showSearchResults = false;
        $this->searchQuery = '';
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
        if (!$this->scanningEnabled) {
            return;
        }

        // Try to find product by barcode
        $product = $this->findProductByBarcode($barcode);

        if (!$product) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => "Product not found for barcode: {$barcode}"
            ]);
            return;
        }

        // Check stock
        $stock = $product->getCurrentStock('shop', $this->shopId);
        if ($stock['total_items'] === 0) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => "{$product->name} is out of stock"
            ]);
            return;
        }

        // Quick add to cart (1 item from first available box)
        $box = Box::where('product_id', $product->id)
            ->where('location_type', 'shop')
            ->where('location_id', $this->shopId)
            ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
            ->where('items_remaining', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->first();

        if (!$box) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => "No available boxes for {$product->name}"
            ]);
            return;
        }

        $this->addToCart([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'box_id' => $box->id,
            'box_code' => $box->box_code,
            'quantity' => 1,
            'is_full_box' => false,
            'price' => $product->selling_price,
            'line_total' => $product->selling_price,
        ]);

        $this->dispatch('notification', [
            'type' => 'success',
            'message' => "Added {$product->name} to cart"
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

    // ==================== CART MANAGEMENT ====================

    public function addItemToCart()
    {
        $this->validate([
            'addItemMode' => 'required|in:individual,full_box',
            'addItemQuantity' => 'required|integer|min:1',
            'addItemBoxId' => 'required|exists:boxes,id',
        ]);

        $box = Box::findOrFail($this->addItemBoxId);
        $product = Product::findOrFail($this->selectedProduct['id']);

        $isFullBox = $this->addItemMode === 'full_box';
        $quantity = $isFullBox ? $box->items_remaining : $this->addItemQuantity;

        // Validate quantity
        if ($quantity > $box->items_remaining) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => "Only {$box->items_remaining} items available in this box"
            ]);
            return;
        }

        $price = $isFullBox ? $product->calculateBoxPrice() : $product->selling_price;
        $lineTotal = $isFullBox ? $price : ($price * $quantity);

        $this->addToCart([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'box_id' => $box->id,
            'box_code' => $box->box_code,
            'quantity' => $quantity,
            'is_full_box' => $isFullBox,
            'price' => $price,
            'original_price' => $price,
            'line_total' => $lineTotal,
        ]);

        $this->showAddItemModal = false;
        $this->selectedProduct = null;
        $this->dispatch('notification', [
            'type' => 'success',
            'message' => 'Item added to cart'
        ]);
    }

    private function addToCart($item)
    {
        // Check if same product+box already in cart
        $existingIndex = $this->findCartItem($item['product_id'], $item['box_id'], $item['is_full_box']);

        if ($existingIndex !== false) {
            // Update quantity
            $this->cart[$existingIndex]['quantity'] += $item['quantity'];
            $this->cart[$existingIndex]['line_total'] = $this->cart[$existingIndex]['quantity'] * $this->cart[$existingIndex]['price'];
        } else {
            $this->cart[] = $item;
        }

        $this->calculateCartTotal();
    }

    private function findCartItem($productId, $boxId, $isFullBox)
    {
        foreach ($this->cart as $index => $item) {
            if ($item['product_id'] === $productId &&
                $item['box_id'] === $boxId &&
                $item['is_full_box'] === $isFullBox) {
                return $index;
            }
        }
        return false;
    }

    public function updateCartItemQuantity($index, $newQuantity)
    {
        if (!isset($this->cart[$index])) {
            return;
        }

        $box = Box::find($this->cart[$index]['box_id']);
        if (!$box) {
            return;
        }

        if ($newQuantity < 1) {
            $this->removeCartItem($index);
            return;
        }

        if ($newQuantity > $box->items_remaining) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => "Only {$box->items_remaining} items available"
            ]);
            return;
        }

        $this->cart[$index]['quantity'] = $newQuantity;
        $this->cart[$index]['line_total'] = $this->cart[$index]['price'] * $newQuantity;
        $this->calculateCartTotal();
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
                return [
                    'product_id' => $item['product_id'],
                    'box_id' => $item['box_id'],
                    'quantity' => $item['quantity'],
                    'is_full_box' => $item['is_full_box'],
                    'price' => $item['price'],
                    'price_override_reason' => $item['price_modification_reason'] ?? null,
                    'price_override_reference' => $item['price_modification_reference'] ?? null,
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

        } catch (\Exception $e) {
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

    public function closeAddItemModal()
    {
        $this->showAddItemModal = false;
        $this->selectedProduct = null;
    }

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
