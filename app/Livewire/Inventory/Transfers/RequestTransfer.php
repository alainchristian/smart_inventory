<?php

namespace App\Livewire\Inventory\Transfers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Models\Box;
use App\Services\Inventory\TransferService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RequestTransfer extends Component
{
    public ?int $fromWarehouseId = null;
    public ?int $toShopId = null;
    public array $items = [];
    public ?string $notes = null;
    public string $search = '';
    public bool $dropdownOpen = false;

    protected $rules = [
        'fromWarehouseId' => 'required|exists:warehouses,id',
        'toShopId' => 'required|exists:shops,id',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.boxes_requested' => 'required|integer|min:0',
        'notes' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'fromWarehouseId.required' => 'Please select a source warehouse.',
        'toShopId.required' => 'Please select a destination shop.',
        'items.*.product_id.required' => 'Please select a product for each item.',
        'items.*.boxes_requested.required' => 'Please enter number of boxes.',
        'items.*.boxes_requested.min' => 'Number of boxes cannot be negative.',
    ];

    public function mount()
    {
        $user = auth()->user();

        // Pre-fill for shop managers
        if ($user->isShopManager()) {
            $this->toShopId = $user->location_id;
            $shop = Shop::find($user->location_id);
            $this->fromWarehouseId = $shop->default_warehouse_id;
        }
    }

    #[Computed]
    public function warehouses(): \Illuminate\Database\Eloquent\Collection
    {
        return Warehouse::active()->orderBy('name')->get();
    }

    #[Computed]
    public function shops(): \Illuminate\Database\Eloquent\Collection
    {
        return Shop::active()->orderBy('name')->get();
    }

    /**
     * Add product directly to cart (called when clicking product card)
     */
    public function addProductToCart($productId)
    {
        if (!$productId) {
            return;
        }

        // Check if product already exists in cart
        foreach ($this->items as $item) {
            if ($item['product_id'] == $productId) {
                return; // Already in cart, do nothing
            }
        }

        // Add product to cart with quantity 0 (user will set it)
        $this->items[] = [
            'product_id' => $productId,
            'boxes_requested' => 0,
        ];

        // Close dropdown to show transfer cart
        $this->dropdownOpen = false;
    }

    /**
     * Alias for backward compatibility
     */
    public function addProduct($productId)
    {
        return $this->addProductToCart($productId);
    }

    /**
     * Check if a product is already in the cart
     */
    public function isProductInCart($productId): bool
    {
        foreach ($this->items as $item) {
            if ($item['product_id'] == $productId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Toggle dropdown open/closed
     */
    public function toggleDropdown()
    {
        $this->dropdownOpen = !$this->dropdownOpen;
    }

    /**
     * Close dropdown
     */
    public function closeDropdown()
    {
        $this->dropdownOpen = false;
    }

    /**
     * Clear search input
     */
    public function clearSearch()
    {
        $this->search = '';
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedFromWarehouseId()
    {
        // Refresh stock levels when warehouse changes
        $this->dispatch('warehouse-changed');
    }

    public function updatedToShopId()
    {
        // Could add logic to show shop's current stock levels
    }

    /**
     * Validate that requested box quantities don't exceed warehouse stock
     */
    protected function validateWarehouseBoxes()
    {
        if (!$this->fromWarehouseId) {
            return;
        }

        foreach ($this->items as $index => $item) {
            if (isset($item['product_id']) && isset($item['boxes_requested'])) {
                $product = Product::find($item['product_id']);

                if ($product) {
                    // Get actual warehouse box stock
                    $warehouseStock = $product->getCurrentStock('warehouse', $this->fromWarehouseId);
                    $availableBoxes = $warehouseStock['full_boxes'] + $warehouseStock['partial_boxes'];

                    if ($item['boxes_requested'] > $availableBoxes) {
                        $this->addError(
                            "items.{$index}.boxes_requested",
                            "Cannot request {$item['boxes_requested']} boxes. Only {$availableBoxes} boxes available."
                        );
                    }
                }
            }
        }
    }

    public function submit()
    {
        // First validate form structure
        $this->validate();

        if (empty($this->items)) {
            session()->flash('error', 'Please add at least one item to the transfer.');
            return;
        }

        // Check that all items have at least 1 box requested and don't exceed available stock
        $hasErrors = false;
        foreach ($this->items as $index => $item) {
            $product = Product::find($item['product_id']);
            $productName = $product ? $product->name : "Product #{$item['product_id']}";

            // Check if quantity is 0 or not set
            if (!isset($item['boxes_requested']) || $item['boxes_requested'] < 1) {
                $this->addError(
                    "items.{$index}.boxes_requested",
                    "Please enter quantity for \"{$productName}\". Minimum 1 box required."
                );
                $hasErrors = true;
                continue;
            }

            // Check if quantity exceeds available stock
            if ($product) {
                $warehouseStock = $product->getCurrentStock('warehouse', $this->fromWarehouseId);
                $availableBoxes = $warehouseStock['full_boxes'] + $warehouseStock['partial_boxes'];

                if ($item['boxes_requested'] > $availableBoxes) {
                    $this->addError(
                        "items.{$index}.boxes_requested",
                        "\"{$productName}\": Cannot request {$item['boxes_requested']} boxes. Only {$availableBoxes} boxes available in warehouse."
                    );
                    $hasErrors = true;
                }
            }
        }

        if ($hasErrors) {
            session()->flash('error', 'Please fix the validation errors in your cart before submitting.');
            return;
        }

        // Check for duplicate products
        $productIds = array_column($this->items, 'product_id');
        if (count($productIds) !== count(array_unique($productIds))) {
            session()->flash('error', 'Cannot add the same product multiple times. Please combine box counts.');
            return;
        }

        try {
            $transferService = app(TransferService::class);

            // Convert boxes to items (boxes × items_per_box)
            $itemsWithQuantities = [];
            foreach ($this->items as $item) {
                $product = Product::find($item['product_id']);
                $itemsWithQuantities[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['boxes_requested'] * $product->items_per_box,
                ];
            }

            $transfer = $transferService->createTransferRequest([
                'from_warehouse_id' => $this->fromWarehouseId,
                'to_shop_id' => $this->toShopId,
                'items' => $itemsWithQuantities,
                'notes' => $this->notes,
            ]);

            session()->flash('success', "Transfer request {$transfer->transfer_number} created successfully.");

            return redirect()->route('shop.transfers.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating transfer: ' . $e->getMessage());
        }
    }

    /**
     * Get warehouse box stock levels for a specific product
     * This method explicitly queries warehouse boxes only
     */
    public function getWarehouseBoxStockLevels($productId)
    {
        if (!$this->fromWarehouseId || !$productId) {
            return null;
        }

        $product = Product::find($productId);
        if (!$product) {
            return null;
        }

        // Explicitly get warehouse stock only
        return $product->getCurrentStock('warehouse', $this->fromWarehouseId);
    }

    public function render()
    {
        // Get all products and filter based on search
        $productsQuery = Product::active()->with('category')->orderBy('name');

        // Apply search filter if search term exists
        if (strlen(trim($this->search)) > 0) {
            $searchTerm = trim($this->search);
            $productsQuery->where(function($query) use ($searchTerm) {
                $query->where('name', 'ilike', "%{$searchTerm}%")
                    ->orWhere('sku', 'ilike', "%{$searchTerm}%")
                    ->orWhereHas('category', function($q) use ($searchTerm) {
                        $q->where('name', 'ilike', "%{$searchTerm}%");
                    });
            });
        }

        $products = $productsQuery->get();

        // ✅ 1 SQL query instead of 267 — same result shape, no view changes needed
        $stockLevels = [];
        if ($this->fromWarehouseId) {
            $rows = DB::table('boxes')
                ->select(
                    'product_id',
                    DB::raw("SUM(CASE WHEN status = 'full'    THEN 1 ELSE 0 END) AS full_boxes"),
                    DB::raw("SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END) AS partial_boxes"),
                    DB::raw('SUM(items_remaining) AS total_items')
                )
                ->where('location_type', 'warehouse')
                ->where('location_id', $this->fromWarehouseId)
                ->whereIn('status', ['full', 'partial'])
                ->where('items_remaining', '>', 0)
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            $stockLevels = $rows->map(fn ($r) => [
                'full_boxes'    => (int) $r->full_boxes,
                'partial_boxes' => (int) $r->partial_boxes,
                'total_boxes'   => (int) $r->full_boxes + (int) $r->partial_boxes,
                'total_items'   => (int) $r->total_items,
            ])->toArray();
        }

        return view('livewire.inventory.transfers.request-transfer', [
            'products'    => $products,
            'stockLevels' => $stockLevels,
        ]);
    }
}
