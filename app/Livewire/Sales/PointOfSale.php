<?php

namespace App\Livewire\Sales;

use App\Enums\LocationType;
use App\Enums\PaymentMethod;
use App\Enums\SaleType;
use App\Models\Box;
use App\Models\Product;
use App\Services\Sales\SaleService;
use Livewire\Component;

class PointOfSale extends Component
{
    // ── Shop context ──────────────────────────────────────────────────────
    public int $shopId;

    // ── Scan / search ────────────────────────────────────────────────────
    public string $scanInput      = '';
    public string $productSearch  = '';
    public array  $allStockProducts = [];   // loaded once on focus, all available products
    public array  $searchResults  = [];     // filtered subset shown in dropdown
    public bool   $showSearchResults = false;

    // ── Cart ──────────────────────────────────────────────────────────────
    public array $cart = [];

    // ── Checkout ──────────────────────────────────────────────────────────
    public string $paymentMethod = 'cash';
    public float $discount = 0;
    public float $tax = 0;
    public bool $showCheckoutModal = false;

    // ── Edit modal ────────────────────────────────────────────────────────
    public bool $showEditModal = false;
    public ?string $editingCartKey = null;

    // ── Receipt ───────────────────────────────────────────────────────────
    public bool $showReceipt = false;
    public ?array $completedSale = null;

    // ── Listeners ─────────────────────────────────────────────────────────
    protected $listeners = [
        'barcode-scanned' => 'handleBarcodeScan',
    ];

    // =========================================================================
    // MOUNT
    // =========================================================================

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->isShopManager()) {
            $this->shopId = $user->location_id;
        } else {
            abort(403, 'Only shop managers can access POS');
        }
    }

    // =========================================================================
    // BARCODE SCANNING
    // =========================================================================

    public function handleBarcodeScan(string $barcode): void
    {
        $this->scanInput = $barcode;
        $this->scanBox();
    }

    /**
     * Scan a box code to identify its product, then add that product to cart.
     * We do NOT track which specific box was scanned — just use it for product lookup.
     */
    public function scanBox(): void
    {
        $code = trim($this->scanInput);
        $this->scanInput = '';

        if (empty($code)) {
            return;
        }

        // 1. Try as a box_code at this shop
        $box = Box::where('box_code', $code)
            ->where('location_type', LocationType::SHOP->value)
            ->where('location_id', $this->shopId)
            ->first();

        if ($box) {
            $this->addProductToCart($box->product_id);
            return;
        }

        // 2. Try as a product barcode (products.barcode column)
        $product = Product::where('barcode', $code)->active()->first();
        if ($product) {
            $this->addProductToCart($product->id);
            return;
        }

        session()->flash('scan_error', "No product found for code: {$code}");
    }

    // =========================================================================
    // PRODUCT SEARCH
    // =========================================================================

    /**
     * Called when the search input is focused.
     * Loads ALL products that have stock at this shop and shows the dropdown.
     */
    public function loadAvailableProducts(): void
    {
        $this->allStockProducts = Product::active()
            ->whereHas('boxes', function ($query) {
                $query->where('location_type', LocationType::SHOP->value)
                      ->where('location_id', $this->shopId)
                      ->whereIn('status', ['full', 'partial'])
                      ->where('items_remaining', '>', 0);
            })
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(function (Product $p) {
                $stock = $p->getCurrentStock(LocationType::SHOP->value, $this->shopId);
                return [
                    'id'              => $p->id,
                    'name'            => $p->name,
                    'sku'             => $p->sku,
                    'category'        => $p->category->name ?? '—',
                    'unit_price'      => $p->selling_price,
                    'box_price'       => $p->calculateBoxPrice(),
                    'items_per_box'   => $p->items_per_box,
                    'available_boxes' => $stock['full_boxes'] + $stock['partial_boxes'],
                    'available_items' => $stock['total_items'],
                ];
            })
            ->toArray();

        $this->searchResults    = $this->allStockProducts;
        $this->showSearchResults = true;
    }

    /**
     * Called on every keystroke in the search input.
     * Filters the already-loaded list — no new DB query needed.
     */
    public function updatedProductSearch(): void
    {
        $q = mb_strtolower(trim($this->productSearch));

        if ($q === '') {
            // Empty search: show everything
            $this->searchResults = $this->allStockProducts;
        } else {
            $this->searchResults = array_values(array_filter(
                $this->allStockProducts,
                function (array $product) use ($q) {
                    return str_contains(mb_strtolower($product['name']), $q)
                        || str_contains(mb_strtolower($product['sku']),  $q);
                }
            ));
        }

        $this->showSearchResults = true;
    }

    public function closeSearch(): void
    {
        $this->productSearch     = '';
        $this->searchResults     = [];
        $this->allStockProducts  = [];
        $this->showSearchResults = false;
    }

    // =========================================================================
    // CART MANAGEMENT
    // =========================================================================

    /**
     * Add a product to the cart (or increment its quantity if already there).
     * Default sell_by is 'box'. The cart is keyed by product_id so the same
     * product is never duplicated — just its quantity increases.
     */
    public function addProductToCart(int $productId, string $sellBy = 'box', int $qty = 1): void
    {
        $product = Product::find($productId);

        if (! $product) {
            session()->flash('error', 'Product not found');
            return;
        }

        $stock          = $product->getCurrentStock(LocationType::SHOP->value, $this->shopId);
        $availableBoxes = $stock['full_boxes'] + $stock['partial_boxes'];
        $availableItems = $stock['total_items'];

        if ($availableItems === 0) {
            session()->flash('error', "No stock available for {$product->name}");
            return;
        }

        $key = "p_{$productId}";

        if (isset($this->cart[$key])) {
            // Already in cart: increment quantity
            $current = $this->cart[$key];

            if ($current['sell_by'] === 'box') {
                $newQty = $current['quantity'] + 1;
                if ($newQty > $availableBoxes) {
                    session()->flash('error', "Only {$availableBoxes} box(es) available for {$product->name}");
                    return;
                }
            } else {
                $newQty = $current['quantity'] + 1;
                if ($newQty > $availableItems) {
                    session()->flash('error', "Only {$availableItems} item(s) available for {$product->name}");
                    return;
                }
            }

            $this->cart[$key]['quantity']       = $newQty;
            $this->cart[$key]['available_boxes'] = $availableBoxes;
            $this->cart[$key]['available_items'] = $availableItems;
        } else {
            $this->cart[$key] = [
                'product_id'      => $product->id,
                'product_name'    => $product->name,
                'sku'             => $product->sku,
                'sell_by'         => $sellBy,
                'quantity'        => $qty,
                'items_per_box'   => $product->items_per_box,
                'unit_price'      => $product->selling_price,
                'box_price'       => $product->calculateBoxPrice(),
                'final_price'     => $sellBy === 'box'
                                       ? $product->calculateBoxPrice()
                                       : $product->selling_price,
                'price_modified'  => false,
                'price_reason'    => null,
                'available_boxes' => $availableBoxes,
                'available_items' => $availableItems,
            ];
        }

        $this->closeSearch();
        $this->dispatch('item-added', productName: $product->name);
    }

    public function openEditItem(string $key): void
    {
        if (isset($this->cart[$key])) {
            $this->editingCartKey = $key;
            $this->showEditModal  = true;
        }
    }

    public function updateCartItem(
        string  $key,
        int     $quantity,
        string  $sellBy,
        int     $finalPrice,
        bool    $priceModified,
        ?string $priceReason
    ): void {
        if (! isset($this->cart[$key])) {
            return;
        }

        if ($quantity <= 0) {
            $this->removeFromCart($key);
            $this->showEditModal  = false;
            $this->editingCartKey = null;
            return;
        }

        $item  = $this->cart[$key];
        $stock = Product::find($item['product_id'])
            ?->getCurrentStock(LocationType::SHOP->value, $this->shopId);

        $maxQty = $sellBy === 'box'
            ? ($stock['full_boxes'] + $stock['partial_boxes'])
            : $stock['total_items'];

        if ($quantity > $maxQty) {
            session()->flash('error', "Only {$maxQty} available");
            return;
        }

        $this->cart[$key]['sell_by']        = $sellBy;
        $this->cart[$key]['quantity']       = $quantity;
        $this->cart[$key]['final_price']    = $finalPrice;
        $this->cart[$key]['price_modified'] = $priceModified;
        $this->cart[$key]['price_reason']   = $priceReason;

        $this->showEditModal  = false;
        $this->editingCartKey = null;
    }

    public function removeFromCart(string $key): void
    {
        unset($this->cart[$key]);
    }

    public function clearCart(): void
    {
        $this->cart     = [];
        $this->discount = 0;
        $this->tax      = 0;
    }

    // =========================================================================
    // COMPUTED TOTALS
    // =========================================================================

    public function getSubtotalProperty(): int
    {
        return collect($this->cart)->sum(fn ($item) => $item['quantity'] * $item['final_price']);
    }

    public function getTotalProperty(): int
    {
        $taxCents      = (int) round($this->tax * 100);
        $discountCents = (int) round($this->discount * 100);
        return $this->subtotal + $taxCents - $discountCents;
    }

    public function getItemCountProperty(): int
    {
        return count($this->cart);
    }

    // =========================================================================
    // CHECKOUT
    // =========================================================================

    public function completeSale(): void
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty');
            return;
        }

        $this->validate([
            'paymentMethod' => 'required|in:cash,card,mobile_money,bank_transfer,credit',
        ]);

        try {
            $saleService = app(SaleService::class);

            $items = collect($this->cart)->map(function ($item) {
                $isFullBox   = $item['sell_by'] === 'box';
                $itemsToSell = $isFullBox
                    ? $item['quantity'] * $item['items_per_box']
                    : $item['quantity'];

                return [
                    'product_id'               => $item['product_id'],
                    'quantity'                 => $itemsToSell,
                    'is_full_box'              => $isFullBox,
                    'price'                    => $item['final_price'],
                    'price_was_modified'       => $item['price_modified'],
                    'price_modification_reason' => $item['price_reason'],
                ];
            })->values()->toArray();

            $sale = $saleService->createSale([
                'shop_id'        => $this->shopId,
                'type'           => SaleType::MIXED,
                'payment_method' => PaymentMethod::from($this->paymentMethod),
                'items'          => $items,
                'tax'            => (int) round($this->tax * 100),
                'discount'       => (int) round($this->discount * 100),
            ]);

            // Capture receipt data before clearing cart
            $this->completedSale = [
                'sale_number'    => $sale->sale_number,
                'total'          => $sale->total,
                'subtotal'       => $this->subtotal,
                'tax'            => (int) round($this->tax * 100),
                'discount'       => (int) round($this->discount * 100),
                'payment_method' => $this->paymentMethod,
                'items'          => collect($this->cart)->values()->toArray(),
            ];

            $this->clearCart();
            $this->showCheckoutModal = false;
            $this->showReceipt       = true;

            $this->dispatch('sale-completed', saleNumber: $sale->sale_number);

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function closeReceipt(): void
    {
        $this->showReceipt   = false;
        $this->completedSale = null;
    }

    // =========================================================================
    // RENDER
    // =========================================================================

    public function render()
    {
        return view('livewire.sales.point-of-sale', [
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }
}
