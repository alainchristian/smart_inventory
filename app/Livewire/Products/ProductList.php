<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Services\SettingsService;
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

    // "Apply packs" (owner): ticked product ids + the drawer's choices
    public array   $selected      = [];
    public bool    $showPacks     = false;
    public array   $packKeys      = [];          // preset keys (dozen, half_box…)
    public string  $customName    = '';
    public string  $customSize    = '';
    public string  $packPrice     = 'piece';     // piece | discount
    public string  $packDiscount  = '';
    public string  $packExisting  = 'skip';      // skip | replace
    public string  $packSingle    = 'keep';      // keep | on | off

    public string  $period = 'today'; // matches TimeFilter's default preset
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

    // Match the named-parameter format that TimeFilter.php dispatches
    // $this->dispatch('time-filter-changed', period: ..., from: ..., to: ...)
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

    public function selectProductSuggestion(int $productId): void
    {
        $product = Product::find($productId);
        if ($product) {
            $this->search = $product->name;
        }
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

        if (! auth()->user()->isOwner() && ! auth()->user()->isAdmin()) {
            session()->flash('error', 'Only owners and admins can delete products.');
            return;
        }

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

        // Only owners and admins can toggle — check role directly as a safeguard
        if (! auth()->user()->isOwner() && ! auth()->user()->isAdmin()) {
            session()->flash('error', 'Only owners and admins can change product status.');
            return;
        }

        $product->update(['is_active' => ! $product->is_active]);

        // Re-fetch to get updated value
        $product->refresh();
        session()->flash('success', $product->is_active ? 'Product activated.' : 'Product deactivated.');
    }

    // ── Apply packs to many products ────────────────────────────────────

    private function assertCanManagePacks(): bool
    {
        $user = auth()->user();
        if ($user->isOwner() || $user->isAdmin()) {
            return true;
        }
        $this->dispatch('notification', ['type' => 'error', 'message' => 'Only the owner can change how products are sold.']);

        return false;
    }

    /** Header checkbox: tick every product on this page, or untick them if all are ticked. */
    public function toggleSelectPage(array $ids): void
    {
        // Strings, like the values wire:model puts in $selected from the row checkboxes
        $ids = array_map('strval', $ids);
        $sel = array_map('strval', $this->selected);
        $this->selected = array_values(array_diff($ids, $sel) === []
            ? array_diff($sel, $ids)
            : array_unique(array_merge($sel, $ids)));
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function openPacks(): void
    {
        if (! $this->assertCanManagePacks() || ! $this->selected) {
            return;
        }
        $this->resetValidation();
        $this->showPacks = true;
    }

    public function closePacks(): void
    {
        $this->showPacks = false;
    }

    /** The drawer's pack choices in SellUnitBulkApplier's shape. */
    private function packChoices(): array
    {
        $packs = collect($this->packKeys)
            ->filter(fn ($k) => isset(\App\Models\ProductSellUnit::PRESETS[$k]))
            ->map(fn ($k) => ['key' => $k])->values()->all();
        if ((int) $this->customSize >= 2) {
            $packs[] = ['key' => 'custom', 'size' => (int) $this->customSize,
                        'name' => trim($this->customName) !== '' ? trim($this->customName) : 'Pack of ' . (int) $this->customSize];
        }

        return $packs;
    }

    private function packOptions(): array
    {
        return [
            'price'    => $this->packPrice === 'discount' ? 'discount' : 'piece',
            'discount' => (float) $this->packDiscount,
            'existing' => $this->packExisting === 'replace' ? 'replace' : 'skip',
            'single'   => in_array($this->packSingle, ['on', 'off'], true) ? $this->packSingle : 'keep',
        ];
    }

    public function getPackPreviewProperty(): array
    {
        if (! $this->showPacks || ! $this->selected || ! $this->packChoices()) {
            return [];
        }

        return app(\App\Services\Products\SellUnitBulkApplier::class)
            ->preview(array_map('intval', $this->selected), $this->packChoices(), $this->packOptions());
    }

    public function applyPacks(): void
    {
        if (! $this->assertCanManagePacks()) {
            return;
        }
        $this->validate([
            'selected'     => 'required|array|min:1',
            'customName'   => 'nullable|string|max:40',
            'customSize'   => 'nullable|integer|min:2|max:9999',
            'packDiscount' => 'nullable|numeric|min:0|max:100',
        ], ['selected.required' => 'Tick at least one product.']);

        if (! $this->packChoices() && $this->packSingle === 'keep') {
            $this->addError('packKeys', 'Choose at least one pack.');
            return;
        }

        $done = app(\App\Services\Products\SellUnitBulkApplier::class)
            ->apply(array_map('intval', $this->selected), $this->packChoices(), $this->packOptions());

        $this->showPacks = false;
        $this->selected  = [];
        $this->reset(['packKeys', 'customName', 'customSize', 'packDiscount']);
        $this->dispatch('notification', ['type' => 'success', 'message' => $done['products']
            ? "Updated {$done['products']} " . ($done['products'] === 1 ? 'product' : 'products') . " ({$done['packs']} packs)."
            : 'Nothing to change — every pack was skipped.']);
    }

    private function periodRange(): array
    {
        // TimeFilter.php already resolves the correct [from, to] for every
        // preset it supports (today/yesterday/week/month/last_month/last_30)
        // and sends both via the 'time-filter-changed' event — trust those
        // directly instead of re-deriving from $this->period, whose preset
        // names here (today/week/quarter/year/custom) didn't actually match
        // what TimeFilter dispatches, so every preset except Today/This Week
        // silently fell through to a hardcoded "this month" range.
        if ($this->from && $this->to) {
            $tz = config('tenant.timezone'); // business-timezone days → UTC bounds (sale_date is UTC)
            return [\Carbon\Carbon::parse($this->from, $tz)->startOfDay()->utc(), \Carbon\Carbon::parse($this->to, $tz)->endOfDay()->utc()];
        }

        // Before TimeFilter has dispatched anything yet — matches its own
        // default selected preset ('today').
        return [business_now()->startOfDay()->utc(), business_now()->endOfDay()->utc()];
    }

    public function render()
    {
        $user     = auth()->user();
        $isOwner  = $user->isOwner() || $user->isAdmin();
        $settings = app(SettingsService::class);
        [$start, $end] = $this->periodRange();

        // Base product query
        $query = Product::query()
            ->with('category')
            ->when($this->search,     fn ($q) => $q->search($this->search))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->activeOnly, fn ($q) => $q->active());

        // Low stock filter
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
        }

        // DB-level sorting
        $dbFields = ['name', 'sku', 'created_at', 'selling_price', 'purchase_price'];
        $query->orderBy(
            in_array($this->sortBy, $dbFields) ? $this->sortBy : 'name',
            $this->sortDirection
        );

        $products   = $query->paginate(50);
        $productIds = $products->pluck('id')->toArray();

        // Sales enrichment (owner only - single grouped query)
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

        // Stock enrichment (single grouped query)
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

        // Post-filter: manager low-stock (can't do efficiently in DB)
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

        // Search suggestions dropdown — reuses the already-filtered page of
        // $products (so it respects category/active/low-stock too) instead
        // of a separate query; capped for a compact dropdown.
        $suggestions = collect($products->items())->take(8)->map(fn ($p) => (object) [
            'id'            => $p->id,
            'name'          => $p->name,
            'sku'           => $p->sku,
            'category_name' => $p->category->name ?? null,
            'total_boxes'   => $stockData[$p->id]->total_boxes ?? 0,
        ]);

        return view('livewire.products.product-list', [
            'products'    => $products,
            'categories'  => Category::active()->orderBy('name')->get(),
            'salesStats'  => $salesStats,
            'stockData'   => $stockData,
            'isOwner'     => $isOwner,
            'periodLabel' => [
                'today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week', 'month' => 'This Month',
                'last_month' => 'Last Month', 'last_30' => 'Last 30 Days',
            ][$this->period] ?? 'Custom Range',
            'suggestions' => $suggestions,
            'settings'    => $settings,
        ]);
    }
}