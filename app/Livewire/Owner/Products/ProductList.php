<?php

namespace App\Livewire\Owner\Products;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public string  $search        = '';
    public ?int    $categoryId    = null;
    public bool    $activeOnly    = true;
    public bool    $lowStockOnly  = false;
    public ?int    $locationId    = null;
    public ?string $locationType  = null;
    public string  $sortBy        = 'name';
    public string  $sortDirection = 'asc';

    // Period from global TimeFilter (owner enrichment only)
    public string  $period = 'month';
    public ?string $from   = null;
    public ?string $to     = null;

    protected $queryString = [
        'search'     => ['except' => ''],
        'categoryId' => ['except' => null],
        'activeOnly' => ['except' => true],
    ];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->isWarehouseManager()) {
            $this->locationType = 'warehouse';
            $this->locationId   = $user->location_id;
        } elseif ($user->isShopManager()) {
            $this->locationType = 'shop';
            $this->locationId   = $user->location_id;
        }
    }

    #[On('time-filter-changed')]
    public function refreshPeriod(string $period, ?string $from = null, ?string $to = null): void
    {
        $this->period = $period;
        $this->from   = $from;
        $this->to     = $to;
        $this->resetPage();
    }

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingCategoryId(): void   { $this->resetPage(); }
    public function updatingActiveOnly(): void   { $this->resetPage(); }
    public function updatingLowStockOnly(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['search', 'categoryId', 'activeOnly', 'lowStockOnly']);
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy        = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openDetail(int $productId): void
    {
        $this->dispatch('open-product-detail', productId: $productId);
    }

    public function deleteProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->authorize('delete', $product);
        if ($product->boxes()->exists()) {
            session()->flash('error', 'Cannot delete a product that has boxes in inventory.');
            return;
        }
        $product->delete();
        session()->flash('success', 'Product deleted.');
    }

    public function toggleActive(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->authorize('update', $product);
        $product->update(['is_active' => !$product->is_active]);
        session()->flash('success', $product->is_active ? 'Product activated.' : 'Product deactivated.');
    }

    private function periodRange(): array
    {
        return match ($this->period) {
            'today'   => [today(),                      now()->endOfDay()],
            'week'    => [now()->startOfWeek(),          now()->endOfDay()],
            'quarter' => [now()->startOfQuarter(),       now()->endOfDay()],
            'year'    => [now()->startOfYear(),          now()->endOfDay()],
            'custom'  => [$this->from ?? today(),        $this->to ?? now()->endOfDay()],
            default   => [now()->startOfMonth(),         now()->endOfDay()],
        };
    }

    public function render()
    {
        $user    = auth()->user();
        $isOwner = $user->isOwner();
        [$start, $end] = $this->periodRange();

        // -- Base product query ----
        $query = Product::query()
            ->with('category')
            ->when($this->search,     fn ($q) => $q->search($this->search))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->activeOnly, fn ($q) => $q->active());

        // -- Low stock filter ----
        if ($this->lowStockOnly) {
            if ($isOwner) {
                $lowIds = DB::table('boxes')
                    ->whereIn('status', ['full', 'partial'])
                    ->where('items_remaining', '>', 0)
                    ->groupBy('product_id')
                    ->havingRaw('SUM(items_remaining) <= (SELECT low_stock_threshold FROM products WHERE id = boxes.product_id)')
                    ->pluck('product_id');

                $zeroIds = Product::where('is_active', true)
                    ->whereDoesntHave('boxes', fn ($q) => $q
                        ->whereIn('status', ['full', 'partial'])
                        ->where('items_remaining', '>', 0)
                    )
                    ->pluck('id');

                $query->whereIn('id', $lowIds->merge($zeroIds)->unique());
            }
            // Manager post-filter applied after paginate (see below)
        }

        // -- DB-level sorting ----
        $dbFields = ['name', 'sku', 'created_at', 'selling_price', 'purchase_price'];
        $query->orderBy(
            in_array($this->sortBy, $dbFields) ? $this->sortBy : 'name',
            $this->sortDirection
        );

        $products    = $query->paginate(50);
        $productIds  = $products->pluck('id')->toArray();

        // -- Sales enrichment (owner only - single grouped query) ----
        $salesStats = [];
        if ($isOwner && !empty($productIds)) {
            foreach (
                DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereIn('sale_items.product_id', $productIds)
                    ->whereNull('sales.voided_at')
                    ->whereNull('sales.deleted_at')
                    ->whereBetween('sales.sale_date', [$start, $end])
                    ->groupBy('sale_items.product_id')
                    ->selectRaw(
                        'sale_items.product_id,
                         SUM(sale_items.line_total)    as revenue,
                         SUM(sale_items.quantity_sold) as units_sold,
                         MAX(CASE WHEN sale_items.price_was_modified THEN 1 ELSE 0 END) as has_override,
                         MAX(sales.sale_date) as last_sold_at'
                    )
                    ->get() as $row
            ) {
                $salesStats[$row->product_id] = $row;
            }
        }

        // -- Stock enrichment (single grouped query) ----
        $stockData = [];
        if (!empty($productIds)) {
            $boxQuery = DB::table('boxes')
                ->whereIn('product_id', $productIds)
                ->whereIn('status', ['full', 'partial'])
                ->where('items_remaining', '>', 0);

            if (!$isOwner && $this->locationType && $this->locationId) {
                $boxQuery->where('location_type', $this->locationType)
                         ->where('location_id',   $this->locationId);
            }

            foreach (
                $boxQuery->groupBy('product_id')
                    ->selectRaw(
                        "product_id,
                         SUM(items_remaining) as total_items,
                         SUM(CASE WHEN location_type = 'warehouse' THEN items_remaining ELSE 0 END) as warehouse_items,
                         SUM(CASE WHEN location_type = 'shop'      THEN items_remaining ELSE 0 END) as shop_items,
                         COUNT(*) as total_boxes"
                    )
                    ->get() as $row
            ) {
                $stockData[$row->product_id] = $row;
            }
        }

        // -- Post-filter: manager low-stock ----
        if ($this->lowStockOnly && !$isOwner && $this->locationType && $this->locationId) {
            $filtered = collect($products->items())->filter(function ($p) use ($stockData) {
                return ($stockData[$p->id]->total_items ?? 0) <= $p->low_stock_threshold;
            })->values();

            $products = new \Illuminate\Pagination\LengthAwarePaginator(
                $filtered,
                $filtered->count(),
                50,
                $products->currentPage()
            );
        }

        return view('livewire.products.product-list', [
            'products'    => $products,
            'categories'  => Category::active()->orderBy('name')->get(),
            'salesStats'  => $salesStats,
            'stockData'   => $stockData,
            'isOwner'     => $isOwner,
            'periodLabel' => ucfirst($this->period),
        ]);
    }
}