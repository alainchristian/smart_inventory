<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $categoryId = null;
    public bool $activeOnly = true;
    public bool $lowStockOnly = false;
    public ?int $locationId = null;
    public ?string $locationType = null;
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryId' => ['except' => null],
        'activeOnly' => ['except' => true],
    ];

    public function mount()
    {
        $user = auth()->user();

        // Auto-set location for non-owners
        if ($user->isWarehouseManager()) {
            $this->locationType = 'warehouse';
            $this->locationId = $user->location_id;
        } elseif ($user->isShopManager()) {
            $this->locationType = 'shop';
            $this->locationId = $user->location_id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function updatingActiveOnly()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'categoryId', 'activeOnly', 'lowStockOnly']);
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deleteProduct($productId)
    {
        $product = Product::findOrFail($productId);

        $this->authorize('delete', $product);

        // Check if product has boxes
        if ($product->boxes()->exists()) {
            session()->flash('error', 'Cannot delete product with existing boxes in inventory.');
            return;
        }

        $product->delete();

        session()->flash('success', 'Product deleted successfully.');
    }

    public function toggleActive($productId)
    {
        $product = Product::findOrFail($productId);

        $this->authorize('update', $product);

        $product->update(['is_active' => !$product->is_active]);

        session()->flash('success', $product->is_active ? 'Product activated.' : 'Product deactivated.');
    }

    public function render()
    {
        $query = Product::query()
            ->with('category')
            ->when($this->search, function ($q) {
                $q->search($this->search);
            })
            ->when($this->categoryId, function ($q) {
                $q->where('category_id', $this->categoryId);
            })
            ->when($this->activeOnly, function ($q) {
                $q->active();
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        // Apply low stock filter if location is set
        if ($this->lowStockOnly && $this->locationType && $this->locationId) {
            $products = $query->get()->filter(function ($product) {
                return $product->isLowStock($this->locationType, $this->locationId);
            });

            // Paginate the filtered collection
            $perPage = 50;
            $currentPage = $this->page;
            $products = new \Illuminate\Pagination\LengthAwarePaginator(
                $products->forPage($currentPage, $perPage),
                $products->count(),
                $perPage,
                $currentPage
            );
        } else {
            $products = $query->paginate(50);
        }

        // Get stock levels for each product if location is set
        $stockLevels = [];
        if ($this->locationType && $this->locationId) {
            foreach ($products as $product) {
                $stockLevels[$product->id] = $product->getCurrentStock($this->locationType, $this->locationId);
            }
        }

        return view('livewire.products.product-list', [
            'products' => $products,
            'categories' => Category::active()->orderBy('name')->get(),
            'stockLevels' => $stockLevels,
        ]);
    }
}
