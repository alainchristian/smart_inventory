<?php

namespace App\Livewire\Inventory\Boxes;

use App\Enums\BoxStatus;
use App\Models\Box;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BoxList extends Component
{
    public string  $search          = '';
    public ?string $locationType    = null;
    public ?int    $locationId      = null;
    public ?int    $categoryId      = null;
    public ?string $status          = null;
    public bool    $expiringOnly    = false;
    public int     $expiringDays    = 30;
    public bool    $lowStockOnly    = false;
    public string  $sortBy          = 'last_received';
    public string  $sortDirection   = 'desc';
    public int     $perPage         = 25;
    public ?int    $expandedProductId = null;

    // Period filter — scopes "Sold"/"Revenue produced" to a date range, and
    // (when the range ends in the past) reconstructs "Remaining" as of that
    // date from the box_movements ledger instead of using live stock.
    public string  $periodPreset    = 'all_time';
    public string  $dateFrom        = '';
    public string  $dateTo          = '';

    protected $queryString = [
        'search'        => ['except' => ''],
        'locationType'  => ['except' => null],
        'locationId'    => ['except' => null],
        'categoryId'    => ['except' => null],
        'status'        => ['except' => null],
        'lowStockOnly'  => ['except' => false],
        'sortBy'        => ['except' => 'last_received'],
        'sortDirection' => ['except' => 'desc'],
        'periodPreset'  => ['except' => 'all_time'],
        'dateFrom'      => ['except' => ''],
        'dateTo'        => ['except' => ''],
    ];

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->isOwner() || $user->isAdmin()) {
            return;
        }

        if ($user->isWarehouseManager()) {
            $this->locationType = 'warehouse';
            $this->locationId   = $user->location_id;
            return;
        }

        abort(403);
    }

    public function updatingLocationType(): void
    {
        $this->locationId         = null;
        $this->perPage            = 25;
        $this->expandedProductId  = null;
    }

    public function updatingSearch(): void       { $this->perPage = 25; $this->expandedProductId = null; }
    public function updatingCategoryId(): void   { $this->perPage = 25; $this->expandedProductId = null; }
    public function updatingStatus(): void       { $this->perPage = 25; $this->expandedProductId = null; }
    public function updatingExpiringOnly(): void { $this->perPage = 25; $this->expandedProductId = null; }
    public function updatingLowStockOnly(): void { $this->perPage = 25; $this->expandedProductId = null; }

    public function loadMore(): void { $this->perPage += 25; }

    public function toggleExpand(int $productId): void
    {
        $this->expandedProductId = $this->expandedProductId === $productId ? null : $productId;
    }

    public function selectProductSuggestion(int $productId): void
    {
        $product = Product::find($productId);
        if ($product) {
            $this->search = $product->name;
        }
        $this->perPage           = 25;
        $this->expandedProductId = null;
    }

    public function setPeriodPreset(string $key): void
    {
        $this->periodPreset = $key;
        $this->resolvePeriodDates();
        $this->afterPeriodChange();
    }

    public function updatedDateFrom(): void { $this->periodPreset = 'custom'; $this->afterPeriodChange(); }
    public function updatedDateTo(): void   { $this->periodPreset = 'custom'; $this->afterPeriodChange(); }

    private function resolvePeriodDates(): void
    {
        match ($this->periodPreset) {
            'today'    => [$this->dateFrom, $this->dateTo] = [today()->toDateString(), today()->toDateString()],
            'week'     => [$this->dateFrom, $this->dateTo] = [today()->startOfWeek()->toDateString(), today()->toDateString()],
            'month'    => [$this->dateFrom, $this->dateTo] = [today()->startOfMonth()->toDateString(), today()->toDateString()],
            'last_30'  => [$this->dateFrom, $this->dateTo] = [today()->subDays(29)->toDateString(), today()->toDateString()],
            'all_time' => [$this->dateFrom, $this->dateTo] = ['', ''],
            default    => null, // 'custom' — dateFrom/dateTo were just set directly by the user
        };
    }

    private function afterPeriodChange(): void
    {
        $this->perPage           = 25;
        $this->expandedProductId = null;

        // A historical "as of" view is reconstructed from the box_movements
        // ledger, which doesn't reliably capture *when* a box was marked
        // damaged — so a Status filter would silently fail to mean anything.
        if ($this->isHistoricalPeriod()) {
            $this->status = null;
        }
    }

    private function isHistoricalPeriod(): bool
    {
        if ($this->periodPreset === 'all_time' || empty($this->dateFrom) || empty($this->dateTo)) {
            return false;
        }

        return Carbon::parse($this->dateTo)->endOfDay()->lt(now());
    }

    public function sortColumn(string $field): void
    {
        $allowed = [
            'name', 'box_count', 'sellable_box_count', 'sold_qty',
            'revenue_produced', 'revenue_expected', 'cost_value',
            'last_received', 'expiry_date',
        ];
        if (!in_array($field, $allowed)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy        = $field;
            $this->sortDirection = 'desc';
        }

        $this->perPage = 25;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'categoryId', 'status', 'expiringOnly', 'lowStockOnly', 'periodPreset', 'dateFrom', 'dateTo']);
        $this->perPage           = 25;
        $this->expandedProductId = null;
    }

    public function render()
    {
        $user    = auth()->user();
        $isOwner = $user->isOwner() || $user->isAdmin();

        // Defense in depth: a warehouse manager is locked to their own
        // warehouse regardless of the location filter's current property
        // state — mount() only sets the initial value.
        if (!$isOwner && $user->isWarehouseManager()) {
            $this->locationType = 'warehouse';
            $this->locationId   = $user->location_id;
        }

        $settings = app(SettingsService::class);

        $periodActive = $this->periodPreset !== 'all_time' && !empty($this->dateFrom) && !empty($this->dateTo);
        $periodFrom   = $periodActive ? Carbon::parse($this->dateFrom)->startOfDay() : null;
        $periodTo     = $periodActive ? Carbon::parse($this->dateTo)->endOfDay() : null;
        $isHistorical = $periodActive && $periodTo->lt(now());

        // ── Sold quantity & revenue per product, scoped to the period (if
        //    any) and to the same location filter (via the box the sale
        //    line consumed), excluding voided/soft-deleted sales ──────────
        $soldSub = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('boxes as sib', 'sale_items.box_id', '=', 'sib.id')
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->when($periodActive, fn ($q) => $q->whereBetween('sales.sale_date', [$periodFrom, $periodTo]))
            ->when($this->locationType && $this->locationId, fn ($q) =>
                $q->where('sib.location_type', $this->locationType)
                  ->where('sib.location_id', $this->locationId)
            )
            ->when($this->locationType && !$this->locationId, fn ($q) =>
                $q->where('sib.location_type', $this->locationType)
            )
            ->groupBy('sale_items.product_id')
            ->selectRaw('sale_items.product_id as product_id, SUM(sale_items.quantity_sold) as sold_qty, SUM(sale_items.line_total) as revenue_produced');

        $rowsQuery = $isHistorical
            ? $this->buildHistoricalRowsQuery($periodTo, $soldSub)
            : $this->buildLiveRowsQuery($soldSub);

        $sortColumnMap = [
            'name'               => 'product_name',
            'box_count'          => 'box_count',
            'sellable_box_count' => 'sellable_box_count',
            'sold_qty'           => 'sold_qty',
            'revenue_produced'   => 'revenue_produced',
            'revenue_expected'   => 'revenue_expected',
            'cost_value'         => 'cost_value',
            'last_received'      => 'last_received_at',
            'expiry_date'        => 'soonest_expiry',
        ];
        $rowsQuery->orderBy($sortColumnMap[$this->sortBy] ?? 'last_received_at', $this->sortDirection);

        $rows = $rowsQuery->get();

        if ($this->lowStockOnly) {
            $rows = $rows->filter(fn ($r) => $r->reorder_point > 0 && $r->items_remaining <= $r->reorder_point)->values();
        }

        $filteredCount     = $rows->count();
        $filteredCostValue = $isOwner ? (int) $rows->sum('cost_value') : 0;
        $pagedRows         = $rows->slice(0, $this->perPage)->values();

        // Product filter suggestions — reuses the already-filtered/sorted
        // $rows (so it respects every other active filter) rather than a
        // separate query; capped for a compact dropdown.
        $suggestions = $rows->take(8)->values();

        // Per row, whether this product's category is full-box-only — if so,
        // "Sold" is displayed as a clean box count (every sale is a whole
        // box); otherwise items are sellable individually and a box-count
        // would usually be fractional, so items stay the unit shown.
        foreach ($pagedRows as $row) {
            $row->box_only_sales = $row->category_id
                ? !$settings->categoryAllowsIndividualSales((int) $row->category_id)
                : false;
        }

        // ── Expanded product's individual boxes (drill-down panel) — always
        //    live/current detail, even inside a historical period view ────
        $expandedBoxes = collect();
        if ($this->expandedProductId) {
            $expandedBoxes = Box::with(['location', 'receivedBy'])
                ->where('product_id', $this->expandedProductId)
                ->where('status', '!=', 'empty')
                ->when($this->locationType && $this->locationId, fn ($q) =>
                    $q->where('location_type', $this->locationType)->where('location_id', $this->locationId)
                )
                ->when($this->locationType && !$this->locationId, fn ($q) =>
                    $q->where('location_type', $this->locationType)
                )
                ->when(!$isHistorical && $this->status, fn ($q) => $q->where('status', $this->status))
                ->when($this->expiringOnly, fn ($q) => $q->expiringSoon($this->expiringDays))
                ->orderByDesc('received_at')
                ->get();
        }

        // ── Org-wide KPI strip — always live/current, regardless of the
        //    period filter (it's an operational snapshot, not a report) ───
        $statsQuery = DB::table('boxes')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->when($this->locationType && $this->locationId, fn ($q) =>
                $q->where('boxes.location_type', $this->locationType)
                  ->where('boxes.location_id', $this->locationId)
            )
            ->when($this->locationType && !$this->locationId, fn ($q) =>
                $q->where('boxes.location_type', $this->locationType)
            );

        $stats = (clone $statsQuery)->selectRaw("
            COUNT(*)                                                          AS total,
            SUM(CASE WHEN boxes.status = 'full'    THEN 1 ELSE 0 END)        AS full_count,
            SUM(CASE WHEN boxes.status = 'partial' THEN 1 ELSE 0 END)        AS partial_count,
            SUM(CASE WHEN boxes.status = 'empty'   THEN 1 ELSE 0 END)        AS empty_count,
            SUM(CASE WHEN boxes.status = 'damaged' THEN 1 ELSE 0 END)        AS damaged_count,
            SUM(CASE WHEN boxes.status != 'empty' THEN boxes.items_remaining ELSE 0 END) AS total_items,
            SUM(CASE WHEN boxes.status != 'empty' THEN boxes.items_total     ELSE 0 END) AS total_capacity,
            SUM(CASE WHEN boxes.status IN ('full','partial') AND boxes.items_remaining > 0
                     THEN boxes.items_remaining * products.purchase_price ELSE 0 END) AS cost_value,
            SUM(CASE WHEN boxes.status IN ('full','partial') AND boxes.items_remaining > 0
                     THEN boxes.items_remaining * products.selling_price  ELSE 0 END) AS retail_value,
            SUM(CASE WHEN boxes.status != 'empty'
                     AND boxes.expiry_date IS NOT NULL
                     AND boxes.expiry_date <= NOW() + INTERVAL '30 days'
                     AND boxes.expiry_date >= NOW()
                     THEN 1 ELSE 0 END)                                        AS expiring_soon
        ")->first();

        $fillableBases = (clone $statsQuery)
            ->whereIn('boxes.status', ['full', 'partial'])
            ->selectRaw('SUM(boxes.items_remaining) as remaining, SUM(boxes.items_total) as total')
            ->first();

        $fillRate = ($fillableBases->total > 0)
            ? round(($fillableBases->remaining / $fillableBases->total) * 100, 1)
            : null;

        $distinctProducts = (int) (clone $statsQuery)
            ->where('boxes.status', '!=', 'empty')
            ->selectRaw('COUNT(DISTINCT boxes.product_id) as cnt')
            ->value('cnt');

        return view('livewire.inventory.boxes.box-list', [
            'rows'               => $pagedRows,
            'hasMore'            => $filteredCount > $this->perPage,
            'stats'              => $stats,
            'fillRate'           => $fillRate,
            'distinctProducts'   => $distinctProducts,
            'filteredCount'      => $filteredCount,
            'filteredCostValue'  => $filteredCostValue,
            'isOwner'            => $isOwner,
            'locationLocked'     => !$isOwner,
            'categories'         => Category::active()->orderBy('name')->get(),
            'warehouses'         => Warehouse::active()->orderBy('name')->get(),
            'shops'              => Shop::active()->orderBy('name')->get(),
            'statuses'           => array_filter(BoxStatus::cases(), fn ($s) => $s !== BoxStatus::EMPTY),
            'expandedBoxes'      => $expandedBoxes,
            'suggestions'        => $suggestions,
            'isHistorical'       => $isHistorical,
            'periodActive'       => $periodActive,
            'asOfDate'           => $periodTo,
        ]);
    }

    /**
     * Live grouped-by-product query — current box state, straight from the
     * boxes table.
     */
    private function buildLiveRowsQuery($soldSub)
    {
        return DB::table('boxes')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoinSub($soldSub, 'sold', fn ($join) => $join->on('sold.product_id', '=', 'boxes.product_id'))
            ->where('boxes.status', '!=', 'empty')
            ->when($this->search, fn ($q) => $q->where(function ($qq) {
                $qq->where('products.name', 'ILIKE', "%{$this->search}%")
                   ->orWhere('products.sku', 'ILIKE', "%{$this->search}%")
                   ->orWhere('products.barcode', 'ILIKE', "%{$this->search}%");
            }))
            ->when($this->locationType && $this->locationId, fn ($q) =>
                $q->where('boxes.location_type', $this->locationType)
                  ->where('boxes.location_id', $this->locationId)
            )
            ->when($this->locationType && !$this->locationId, fn ($q) =>
                $q->where('boxes.location_type', $this->locationType)
            )
            ->when($this->categoryId, fn ($q) =>
                $q->where('products.category_id', $this->categoryId)
            )
            ->when($this->status, fn ($q) =>
                $q->where('boxes.status', $this->status)
            )
            ->when($this->expiringOnly, fn ($q) =>
                $q->whereNotNull('boxes.expiry_date')
                  ->where('boxes.expiry_date', '<=', now()->addDays($this->expiringDays))
                  ->where('boxes.expiry_date', '>=', now())
            )
            ->groupBy(
                'boxes.product_id', 'products.name', 'products.sku', 'products.barcode',
                'products.purchase_price', 'products.selling_price', 'products.reorder_point',
                'products.items_per_box', 'products.category_id', 'categories.name'
            )
            ->selectRaw("
                boxes.product_id                                                          as product_id,
                products.name                                                              as product_name,
                products.sku                                                               as product_sku,
                products.barcode                                                           as product_barcode,
                products.reorder_point                                                     as reorder_point,
                products.items_per_box                                                     as items_per_box,
                products.category_id                                                       as category_id,
                categories.name                                                            as category_name,
                COUNT(*)                                                                   as box_count,
                SUM(CASE WHEN boxes.status = 'full'    THEN 1 ELSE 0 END)                  as full_count,
                SUM(CASE WHEN boxes.status = 'partial' THEN 1 ELSE 0 END)                  as partial_count,
                SUM(CASE WHEN boxes.status = 'damaged' THEN 1 ELSE 0 END)                  as damaged_count,
                SUM(CASE WHEN boxes.status IN ('full','partial') THEN 1 ELSE 0 END)        as sellable_box_count,
                SUM(boxes.items_remaining)                                                 as items_remaining,
                SUM(boxes.items_total)                                                     as items_total,
                SUM(CASE WHEN boxes.status IN ('full','partial') AND boxes.items_remaining > 0
                         THEN boxes.items_remaining * products.purchase_price ELSE 0 END)   as cost_value,
                SUM(CASE WHEN boxes.status IN ('full','partial')
                         THEN boxes.items_remaining ELSE 0 END) * MAX(products.selling_price) as revenue_expected,
                MAX(boxes.received_at)                                                     as last_received_at,
                MIN(CASE WHEN boxes.expiry_date >= CURRENT_DATE THEN boxes.expiry_date END) as soonest_expiry,
                COALESCE(MAX(sold.sold_qty), 0)                                            as sold_qty,
                COALESCE(MAX(sold.revenue_produced), 0)                                    as revenue_produced
            ");
    }

    /**
     * Historical grouped-by-product query — reconstructs each box's location
     * and remaining items as of a past cutoff by replaying box_movements
     * (received/consumption/direct_sale/return/transfer are all logged with
     * a timestamp). A box's "as of" location is the destination of its most
     * recent movement at or before the cutoff; a box with no such movement
     * didn't exist yet and is excluded.
     *
     * Damage isn't reliably reconstructable this way (marking a box damaged
     * doesn't log a dedicated, timestamped movement), so historical rows
     * only distinguish Full/Partial — never Damaged.
     */
    private function buildHistoricalRowsQuery(Carbon $asOf, $soldSub)
    {
        $locationAsOf = DB::table('box_movements')
            ->selectRaw('DISTINCT ON (box_id) box_id, to_location_type, to_location_id')
            ->where('moved_at', '<=', $asOf)
            ->orderBy('box_id')
            ->orderByDesc('moved_at')
            ->orderByDesc('id');

        $netChange = DB::table('box_movements')
            ->where('moved_at', '<=', $asOf)
            ->groupBy('box_id')
            ->selectRaw("box_id, SUM(CASE
                WHEN movement_type IN ('consumption','direct_sale') THEN -items_moved
                WHEN movement_type = 'return' THEN items_moved
                ELSE 0 END) as net_change");

        $boxesAsOf = DB::table('boxes')
            ->joinSub($locationAsOf, 'loc', fn ($join) => $join->on('loc.box_id', '=', 'boxes.id'))
            ->leftJoinSub($netChange, 'net', fn ($join) => $join->on('net.box_id', '=', 'boxes.id'))
            ->selectRaw('
                boxes.id                                                          as box_id,
                boxes.product_id                                                  as product_id,
                boxes.items_total                                                 as items_total,
                boxes.received_at                                                 as received_at,
                boxes.expiry_date                                                 as expiry_date,
                loc.to_location_type                                             as location_type,
                loc.to_location_id                                               as location_id,
                GREATEST(boxes.items_total + COALESCE(net.net_change, 0), 0)      as items_remaining_asof
            ');

        return DB::query()->fromSub($boxesAsOf, 'basof')
            ->join('products', 'basof.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoinSub($soldSub, 'sold', fn ($join) => $join->on('sold.product_id', '=', 'basof.product_id'))
            ->where('basof.items_remaining_asof', '>', 0)
            ->when($this->search, fn ($q) => $q->where(function ($qq) {
                $qq->where('products.name', 'ILIKE', "%{$this->search}%")
                   ->orWhere('products.sku', 'ILIKE', "%{$this->search}%")
                   ->orWhere('products.barcode', 'ILIKE', "%{$this->search}%");
            }))
            ->when($this->locationType && $this->locationId, fn ($q) =>
                $q->where('basof.location_type', $this->locationType)
                  ->where('basof.location_id', $this->locationId)
            )
            ->when($this->locationType && !$this->locationId, fn ($q) =>
                $q->where('basof.location_type', $this->locationType)
            )
            ->when($this->categoryId, fn ($q) =>
                $q->where('products.category_id', $this->categoryId)
            )
            ->when($this->expiringOnly, fn ($q) =>
                $q->whereNotNull('basof.expiry_date')
                  ->where('basof.expiry_date', '<=', now()->addDays($this->expiringDays))
                  ->where('basof.expiry_date', '>=', now())
            )
            ->groupBy(
                'basof.product_id', 'products.name', 'products.sku', 'products.barcode',
                'products.purchase_price', 'products.selling_price', 'products.reorder_point',
                'products.items_per_box', 'products.category_id', 'categories.name'
            )
            ->selectRaw("
                basof.product_id                                                          as product_id,
                products.name                                                              as product_name,
                products.sku                                                               as product_sku,
                products.barcode                                                           as product_barcode,
                products.reorder_point                                                     as reorder_point,
                products.items_per_box                                                     as items_per_box,
                products.category_id                                                       as category_id,
                categories.name                                                            as category_name,
                COUNT(*)                                                                   as box_count,
                SUM(CASE WHEN basof.items_remaining_asof >= basof.items_total THEN 1 ELSE 0 END) as full_count,
                SUM(CASE WHEN basof.items_remaining_asof <  basof.items_total THEN 1 ELSE 0 END) as partial_count,
                0                                                                          as damaged_count,
                COUNT(*)                                                                   as sellable_box_count,
                SUM(basof.items_remaining_asof)                                            as items_remaining,
                SUM(basof.items_total)                                                     as items_total,
                SUM(basof.items_remaining_asof * products.purchase_price)                  as cost_value,
                SUM(basof.items_remaining_asof) * MAX(products.selling_price)              as revenue_expected,
                MAX(basof.received_at)                                                     as last_received_at,
                MIN(CASE WHEN basof.expiry_date >= CURRENT_DATE THEN basof.expiry_date END) as soonest_expiry,
                COALESCE(MAX(sold.sold_qty), 0)                                            as sold_qty,
                COALESCE(MAX(sold.revenue_produced), 0)                                    as revenue_produced
            ");
    }
}
