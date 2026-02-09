<?php

namespace App\Livewire\Warehouse;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use App\Models\Box;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class StockLevels extends Component
{
    use WithPagination;

    public ?int $warehouseId = null;
    public string $search = '';
    public string $statusFilter = 'all';

    public function mount()
    {
        $user = auth()->user();

        // Auto-select warehouse for warehouse managers
        if ($user->isWarehouseManager()) {
            $this->warehouseId = $user->location_id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        // For Owners, require warehouse selection
        if ($user->isOwner() && !$this->warehouseId) {
            return view('livewire.warehouse.stock-levels', [
                'stockData' => [],
                'products' => collect(),
                'warehouses' => \App\Models\Warehouse::orderBy('name')->get(),
                'needsWarehouseSelection' => true,
            ]);
        }

        // Validate warehouse access for warehouse managers
        if ($user->isWarehouseManager() && $user->location_id !== $this->warehouseId) {
            abort(403, 'You do not have access to this warehouse.');
        }

        // Get all products with their stock at this warehouse
        $products = Product::query()
            ->where('is_active', true)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('sku', 'like', "%{$this->search}%")
                      ->orWhere('barcode', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);

        // Get stock levels for each product
        $stockData = [];
        foreach ($products as $product) {
            $stock = $product->getCurrentStock(LocationType::WAREHOUSE->value, $this->warehouseId);

            // Apply status filter
            if ($this->statusFilter === 'low' && !$product->isLowStock(LocationType::WAREHOUSE->value, $this->warehouseId)) {
                continue;
            } elseif ($this->statusFilter === 'out' && $stock['total_items'] > 0) {
                continue;
            }

            $stockData[] = [
                'product' => $product,
                'full_boxes' => $stock['full_boxes'],
                'partial_boxes' => $stock['partial_boxes'],
                'total_items' => $stock['total_items'],
                'is_low_stock' => $product->isLowStock(LocationType::WAREHOUSE->value, $this->warehouseId),
            ];
        }

        return view('livewire.warehouse.stock-levels', [
            'stockData' => $stockData,
            'products' => $products,
            'warehouses' => $user->isOwner()
                ? \App\Models\Warehouse::orderBy('name')->get()
                : collect(),
            'needsWarehouseSelection' => false,
        ]);
    }
}
