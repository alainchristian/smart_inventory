<?php

namespace App\Livewire\Inventory\Transfers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Inventory\TransferService;
use Livewire\Component;

class RequestTransfer extends Component
{
    public ?int $fromWarehouseId = null;
    public ?int $toShopId = null;
    public array $items = [];
    public ?string $notes = null;

    protected $rules = [
        'fromWarehouseId' => 'required|exists:warehouses,id',
        'toShopId' => 'required|exists:shops,id',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'notes' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'fromWarehouseId.required' => 'Please select a source warehouse.',
        'toShopId.required' => 'Please select a destination shop.',
        'items.*.product_id.required' => 'Please select a product for each item.',
        'items.*.quantity.required' => 'Please enter a quantity for each item.',
        'items.*.quantity.min' => 'Quantity must be at least 1.',
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

        $this->addItem();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => null,
            'quantity' => 1,
        ];
    }

    public function removeItem($index)
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        } else {
            session()->flash('error', 'Transfer request must have at least one item.');
        }
    }

    public function duplicateItem($index)
    {
        if (isset($this->items[$index])) {
            $newItem = $this->items[$index];
            $this->items[] = $newItem;
        }
    }

    public function updatedFromWarehouseId()
    {
        // Could add logic to check warehouse stock levels
    }

    public function updatedToShopId()
    {
        // Could add logic to show shop's current stock levels
    }

    public function submit()
    {
        $this->validate();

        // Remove empty items
        $this->items = array_filter($this->items, function ($item) {
            return isset($item['product_id']) && isset($item['quantity']) && $item['quantity'] > 0;
        });

        if (empty($this->items)) {
            session()->flash('error', 'Please add at least one item to the transfer.');
            return;
        }

        // Check for duplicate products
        $productIds = array_column($this->items, 'product_id');
        if (count($productIds) !== count(array_unique($productIds))) {
            session()->flash('error', 'Cannot add the same product multiple times. Please combine quantities.');
            return;
        }

        try {
            $transferService = app(TransferService::class);

            $transfer = $transferService->createTransferRequest([
                'from_warehouse_id' => $this->fromWarehouseId,
                'to_shop_id' => $this->toShopId,
                'items' => $this->items,
                'notes' => $this->notes,
            ]);

            session()->flash('success', "Transfer request {$transfer->transfer_number} created successfully.");

            return redirect()->route('transfers.show', $transfer);
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating transfer: ' . $e->getMessage());
        }
    }

    public function getWarehouseStockLevels($productId)
    {
        if (!$this->fromWarehouseId || !$productId) {
            return null;
        }

        $product = Product::find($productId);
        if (!$product) {
            return null;
        }

        return $product->getCurrentStock('warehouse', $this->fromWarehouseId);
    }

    public function render()
    {
        $warehouses = Warehouse::active()->get();
        $shops = Shop::active()->get();
        $products = Product::active()->with('category')->orderBy('name')->get();

        // Get stock levels for selected warehouse
        $stockLevels = [];
        if ($this->fromWarehouseId) {
            foreach ($products as $product) {
                $stockLevels[$product->id] = $product->getCurrentStock('warehouse', $this->fromWarehouseId);
            }
        }

        return view('livewire.inventory.transfers.request-transfer', [
            'warehouses' => $warehouses,
            'shops' => $shops,
            'products' => $products,
            'stockLevels' => $stockLevels,
        ]);
    }
}
