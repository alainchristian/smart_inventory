<?php

namespace App\Livewire\Shop;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use App\Models\Box;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class StockLevels extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

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

        // Get all products with their stock at this shop
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
            $stock = $product->getCurrentStock(LocationType::SHOP->value, $user->location_id);

            // Skip products with no stock at this shop (unless filtering for out of stock)
            if ($this->statusFilter !== 'out' && $stock['total_items'] === 0) {
                continue;
            }

            // Apply status filter
            if ($this->statusFilter === 'low' && !$product->isLowStock(LocationType::SHOP->value, $user->location_id)) {
                continue;
            } elseif ($this->statusFilter === 'out' && $stock['total_items'] > 0) {
                continue;
            }

            $stockData[] = [
                'product' => $product,
                'full_boxes' => $stock['full_boxes'],
                'partial_boxes' => $stock['partial_boxes'],
                'total_items' => $stock['total_items'],
                'is_low_stock' => $product->isLowStock(LocationType::SHOP->value, $user->location_id),
            ];
        }

        return view('livewire.shop.stock-levels', [
            'stockData' => $stockData,
            'products' => $products,
        ]);
    }
}
