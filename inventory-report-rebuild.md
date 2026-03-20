# Inventory Report — Professional Rebuild
## Instructions for Claude Code

> Read this file completely before touching any code.
> Work through every step in the order written. Do not skip ahead.
> When finished, update CLAUDE.md as described in the final step.

---

## Context

The target page is `/owner/reports/inventory`.

The Livewire component is `app/Livewire/Owner/Reports/InventoryValuation.php`.
The analytics service is `app/Services/Analytics/InventoryAnalyticsService.php`.
The blade is `resources/views/livewire/owner/reports/inventory-valuation.blade.php`.

**Read all three files in full before writing any code.**

Also read these files for design patterns to match:
- `resources/views/livewire/owner/reports/sales-analytics.blade.php` — tab structure pattern
- `app/Livewire/Owner/Reports/SalesAnalytics.php` — tab + date range component pattern
- `resources/views/livewire/products/product-list.blade.php` — table pattern
- Any existing KPI card in the codebase for card shell pattern

Design tokens — use only these CSS variables, never hardcoded colours:
```
var(--surface)  var(--surface2)  var(--surface3)
var(--border)
var(--text)  var(--text-sub)  var(--text-dim)
var(--accent)  var(--accent-dim)
var(--success)  var(--success-glow)
var(--warn)     var(--warn-glow)
var(--amber)    var(--amber-dim)
var(--danger)   var(--danger-glow)
var(--red)      var(--red-dim)
var(--violet)   var(--violet-dim)
var(--r)  var(--rsm)
```

---

## Step 1 — Fix bugs in `InventoryAnalyticsService.php`

These must be fixed before any new features are added, because the bugs
produce incorrect data that would cascade into every new section.

### Bug 1 — `getTopProductsByValue` duplicates products across locations

**Current problem:** The query groups by `products.id, products.name,
boxes.location_type, boxes.location_id`. A product stocked at 3 locations
appears 3 times in the list.

**Fix:** Change the `getTopProductsByValue` method. Replace the `selectRaw`
and `groupBy` to group only by `products.id` and `products.name`, and add
a `COUNT(DISTINCT ...)` for location spread:

```php
->selectRaw('
    products.id,
    products.name,
    SUM(boxes.items_remaining) as items_count,
    SUM(boxes.items_remaining * products.purchase_price) as purchase_value,
    SUM(boxes.items_remaining * products.selling_price) as retail_value,
    COUNT(DISTINCT CONCAT(boxes.location_type::text, \':\', boxes.location_id::text)) as location_count
')
->groupBy('products.id', 'products.name')
->orderByDesc('purchase_value')
->limit($limit)
```

Update the returned array to include `location_count`:
```php
'location_count' => (int) $item->location_count,
```

### Bug 2 — `calculateStockTurnover` ignores warehouse filter

**Current problem:** The COGS query only applies a location filter when
`locationFilter` starts with `shop:`. When a warehouse filter is active,
the denominator (inventory) is warehouse-filtered but the numerator (COGS)
is not, producing a meaningless ratio.

**Fix:** When `locationFilter` starts with `warehouse:`, set `$cogs = 0`
and return `0.0` — turnover cannot be calculated for a warehouse location
because sales happen at shops, not warehouses. Add a comment explaining this.

```php
if (str_starts_with($locationFilter, 'warehouse:')) {
    // Turnover cannot be computed per warehouse — sales occur at shops.
    return 0.0;
}
```

### Bug 3 — `getAgingAnalysis` uses `received_at` (wrong date for transferred boxes)

**Current problem:** A box received 120 days ago and transferred to a shop
5 days ago is shown as `90+ days` old even though it just arrived at the shop.

**Fix:** Replace the age calculation to use the latest `box_movements.moved_at`
for the current location if one exists, falling back to `boxes.received_at`:

```php
$query = DB::table('boxes')
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->leftJoin(DB::raw('(
        SELECT box_id, MAX(moved_at) as last_moved_at
        FROM box_movements
        GROUP BY box_id
    ) as last_move'), 'last_move.box_id', '=', 'boxes.id')
    ->whereIn('boxes.status', [BoxStatus::FULL->value, BoxStatus::PARTIAL->value])
    ->where('boxes.items_remaining', '>', 0)
    ->selectRaw("
        CASE
            WHEN DATE_PART('day', NOW() - COALESCE(last_move.last_moved_at, boxes.received_at)) <= 30
                THEN '0-30 days'
            WHEN DATE_PART('day', NOW() - COALESCE(last_move.last_moved_at, boxes.received_at)) <= 60
                THEN '31-60 days'
            WHEN DATE_PART('day', NOW() - COALESCE(last_move.last_moved_at, boxes.received_at)) <= 90
                THEN '61-90 days'
            ELSE '90+ days'
        END as age_bracket,
        COUNT(*) as box_count,
        SUM(boxes.items_remaining) as items_count,
        SUM(boxes.items_remaining * products.purchase_price) as value
    ")
    ->groupBy('age_bracket');
```

Remove the old `$query = $this->applyLocationFilter(...)` line and replace
with the new query above, still applying `applyLocationFilter` to it.

After making all three fixes, clear the cache:
```bash
php artisan cache:clear
```

---

## Step 2 — Add new methods to `InventoryAnalyticsService.php`

Add the following five new public methods to the service class.
Place them after the existing methods, before the private `applyLocationFilter`.

### Method A — `getPortfolioFillRate`

```php
public function getPortfolioFillRate(?string $locationFilter = 'all'): ?float
{
    $result = DB::table('boxes')
        ->when($locationFilter !== 'all', function ($q) use ($locationFilter) {
            if (str_starts_with($locationFilter, 'shop:')) {
                $q->where('location_type', 'shop')
                  ->where('location_id', (int) explode(':', $locationFilter)[1]);
            } elseif (str_starts_with($locationFilter, 'warehouse:')) {
                $q->where('location_type', 'warehouse')
                  ->where('location_id', (int) explode(':', $locationFilter)[1]);
            } elseif ($locationFilter === 'shops') {
                $q->where('location_type', 'shop');
            } elseif ($locationFilter === 'warehouses') {
                $q->where('location_type', 'warehouse');
            }
        })
        ->whereIn('status', ['full', 'partial'])
        ->where('items_remaining', '>', 0)
        ->selectRaw('SUM(items_remaining) as remaining, SUM(items_total) as total')
        ->first();

    return ($result && $result->total > 0)
        ? round(($result->remaining / $result->total) * 100, 1)
        : null;
}
```

### Method B — `getVelocityClassification`

Classifies every active product with stock into A / B / C / Dead based on
trailing 90-day sales revenue contribution.

```php
public function getVelocityClassification(?string $locationFilter = 'all'): array
{
    // Products with current stock
    $stockQuery = DB::table('boxes')
        ->join('products', 'boxes.product_id', '=', 'products.id')
        ->whereIn('boxes.status', ['full', 'partial'])
        ->where('boxes.items_remaining', '>', 0)
        ->whereNull('products.deleted_at')
        ->where('products.is_active', true);

    $stockQuery = $this->applyLocationFilter($stockQuery, $locationFilter);

    $stockedProducts = $stockQuery
        ->selectRaw('
            products.id,
            products.name,
            SUM(boxes.items_remaining) as items_in_stock,
            SUM(boxes.items_remaining * products.purchase_price) as cost_value
        ')
        ->groupBy('products.id', 'products.name')
        ->get()
        ->keyBy('id');

    if ($stockedProducts->isEmpty()) {
        return ['A' => [], 'B' => [], 'C' => [], 'Dead' => [], 'summary' => []];
    }

    // 90-day revenue per product
    $salesQuery = DB::table('sale_items')
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->whereIn('sale_items.product_id', $stockedProducts->keys()->toArray())
        ->whereNull('sales.voided_at')
        ->where('sales.sale_date', '>=', now()->subDays(90));

    if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
        $salesQuery->where('sales.shop_id', (int) explode(':', $locationFilter)[1]);
    }

    $revenue = $salesQuery
        ->selectRaw('sale_items.product_id, SUM(sale_items.line_total) as revenue')
        ->groupBy('sale_items.product_id')
        ->pluck('revenue', 'product_id');

    $totalRevenue = $revenue->sum();

    // Merge and sort by revenue desc
    $classified = $stockedProducts->map(function ($p) use ($revenue) {
        return [
            'product_id'    => $p->id,
            'product_name'  => $p->name,
            'items_in_stock'=> (int) $p->items_in_stock,
            'cost_value'    => (int) $p->cost_value,
            'revenue_90d'   => (float) ($revenue[$p->id] ?? 0),
        ];
    })->sortByDesc('revenue_90d')->values();

    // Build cumulative revenue %
    $cumulative = 0;
    $result = ['A' => [], 'B' => [], 'C' => [], 'Dead' => []];

    foreach ($classified as $item) {
        if ($item['revenue_90d'] == 0) {
            $item['class']      = 'Dead';
            $item['revenue_pct'] = 0;
            $result['Dead'][]   = $item;
            continue;
        }
        $pct = $totalRevenue > 0 ? ($item['revenue_90d'] / $totalRevenue) * 100 : 0;
        $cumulative += $pct;
        $item['revenue_pct'] = round($pct, 1);
        $item['cumulative_pct'] = round($cumulative, 1);

        if ($cumulative <= 70) {
            $item['class'] = 'A';
        } elseif ($cumulative <= 90) {
            $item['class'] = 'B';
        } else {
            $item['class'] = 'C';
        }
        $result[$item['class']][] = $item;
    }

    $result['summary'] = [
        'A_count'         => count($result['A']),
        'B_count'         => count($result['B']),
        'C_count'         => count($result['C']),
        'Dead_count'      => count($result['Dead']),
        'A_cost_value'    => collect($result['A'])->sum('cost_value'),
        'B_cost_value'    => collect($result['B'])->sum('cost_value'),
        'C_cost_value'    => collect($result['C'])->sum('cost_value'),
        'Dead_cost_value' => collect($result['Dead'])->sum('cost_value'),
    ];

    return $result;
}
```

### Method C — `getDaysOnHandPerProduct`

```php
public function getDaysOnHandPerProduct(?string $locationFilter = 'all', int $limit = 30): array
{
    // Avg daily sales per product (last 30 days)
    $salesQuery = DB::table('sale_items')
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->whereNull('sales.voided_at')
        ->whereNull('products.deleted_at')
        ->where('products.is_active', true)
        ->where('sales.sale_date', '>=', now()->subDays(30));

    if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
        $salesQuery->where('sales.shop_id', (int) explode(':', $locationFilter)[1]);
    }

    $salesData = $salesQuery
        ->selectRaw('
            sale_items.product_id,
            SUM(sale_items.quantity_sold) as units_sold_30d
        ')
        ->groupBy('sale_items.product_id')
        ->pluck('units_sold_30d', 'product_id');

    // Current stock per product
    $stockQuery = DB::table('boxes')
        ->join('products', 'boxes.product_id', '=', 'products.id')
        ->whereIn('boxes.status', ['full', 'partial'])
        ->where('boxes.items_remaining', '>', 0)
        ->whereNull('products.deleted_at')
        ->where('products.is_active', true);

    $stockQuery = $this->applyLocationFilter($stockQuery, $locationFilter);

    $stock = $stockQuery
        ->selectRaw('
            products.id,
            products.name,
            products.low_stock_threshold,
            SUM(boxes.items_remaining) as items_remaining,
            SUM(boxes.items_remaining * products.purchase_price) as cost_value
        ')
        ->groupBy('products.id', 'products.name', 'products.low_stock_threshold')
        ->get();

    return $stock->map(function ($p) use ($salesData) {
        $units30d    = (float) ($salesData[$p->id] ?? 0);
        $avgDaily    = $units30d / 30;
        $daysOnHand  = $avgDaily > 0
            ? (int) round($p->items_remaining / $avgDaily)
            : null; // null = no velocity, stock won't run out by sales

        return [
            'product_id'          => $p->id,
            'product_name'        => $p->name,
            'items_remaining'     => (int) $p->items_remaining,
            'cost_value'          => (int) $p->cost_value,
            'units_sold_30d'      => (int) $units30d,
            'avg_daily_sales'     => round($avgDaily, 2),
            'days_on_hand'        => $daysOnHand,
            'low_stock_threshold' => (int) $p->low_stock_threshold,
            'is_critical'         => $daysOnHand !== null && $daysOnHand <= 7,
            'is_low'              => $daysOnHand !== null && $daysOnHand <= 14,
        ];
    })
    ->sortBy(fn ($p) => $p['days_on_hand'] ?? PHP_INT_MAX)
    ->values()
    ->take($limit)
    ->toArray();
}
```

### Method D — `getCategoryConcentration`

```php
public function getCategoryConcentration(?string $locationFilter = 'all'): array
{
    $query = DB::table('boxes')
        ->join('products', 'boxes.product_id', '=', 'products.id')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->whereIn('boxes.status', ['full', 'partial'])
        ->where('boxes.items_remaining', '>', 0)
        ->whereNull('products.deleted_at')
        ->where('products.is_active', true);

    $query = $this->applyLocationFilter($query, $locationFilter);

    $rows = $query->selectRaw('
            categories.id,
            categories.name as category_name,
            COUNT(DISTINCT products.id) as product_count,
            SUM(boxes.items_remaining) as total_items,
            SUM(boxes.items_remaining * products.purchase_price) as cost_value,
            SUM(boxes.items_remaining * products.selling_price) as retail_value
        ')
        ->groupBy('categories.id', 'categories.name')
        ->orderByDesc('cost_value')
        ->get();

    $totalCost = $rows->sum('cost_value');

    return $rows->map(function ($row) use ($totalCost) {
        return [
            'category_id'    => $row->id,
            'category_name'  => $row->category_name,
            'product_count'  => (int) $row->product_count,
            'total_items'    => (int) $row->total_items,
            'cost_value'     => (int) $row->cost_value,
            'retail_value'   => (int) $row->retail_value,
            'pct_of_total'   => $totalCost > 0
                ? round(($row->cost_value / $totalCost) * 100, 1)
                : 0,
        ];
    })->toArray();
}
```

### Method E — `getInventoryMovementTrend`

Weekly boxes-received vs items-consumed for the last 12 weeks.

```php
public function getInventoryMovementTrend(?string $locationFilter = 'all'): array
{
    $weeks = [];
    for ($i = 11; $i >= 0; $i--) {
        $start = now()->startOfWeek()->subWeeks($i);
        $end   = $start->copy()->endOfWeek();

        $receivedQuery = DB::table('box_movements')
            ->where('movement_type', 'received')
            ->whereBetween('moved_at', [$start, $end]);

        $consumedQuery = DB::table('box_movements')
            ->where('movement_type', 'consumption')
            ->whereBetween('moved_at', [$start, $end]);

        // Apply location filter to movements
        if ($locationFilter !== 'all') {
            if (str_starts_with($locationFilter, 'shop:')) {
                $shopId = (int) explode(':', $locationFilter)[1];
                $receivedQuery->where('to_location_type', 'shop')
                              ->where('to_location_id', $shopId);
                $consumedQuery->where('from_location_type', 'shop')
                              ->where('from_location_id', $shopId);
            } elseif (str_starts_with($locationFilter, 'warehouse:')) {
                $whId = (int) explode(':', $locationFilter)[1];
                $receivedQuery->where('to_location_type', 'warehouse')
                              ->where('to_location_id', $whId);
            }
        }

        $weeks[] = [
            'week_label'     => $start->format('M d'),
            'week_start'     => $start->toDateString(),
            'boxes_received' => (int) $receivedQuery->count(),
            'items_consumed' => (int) $consumedQuery->sum('items_moved'),
        ];
    }

    return $weeks;
}
```

### Method F — `getShrinkageStats`

```php
public function getShrinkageStats(?string $locationFilter = 'all'): array
{
    // Total items received in last 90 days (via box_movements)
    $receivedQuery = DB::table('box_movements')
        ->join('boxes', 'box_movements.box_id', '=', 'boxes.id')
        ->where('box_movements.movement_type', 'received')
        ->where('box_movements.moved_at', '>=', now()->subDays(90));

    // Total damaged goods recorded in last 90 days
    $damagedQuery = DB::table('damaged_goods')
        ->whereNull('deleted_at')
        ->where('recorded_at', '>=', now()->subDays(90));

    if ($locationFilter !== 'all') {
        if (str_starts_with($locationFilter, 'shop:')) {
            $id = (int) explode(':', $locationFilter)[1];
            $damagedQuery->where('location_type', 'shop')->where('location_id', $id);
        } elseif (str_starts_with($locationFilter, 'warehouse:')) {
            $id = (int) explode(':', $locationFilter)[1];
            $damagedQuery->where('location_type', 'warehouse')->where('location_id', $id);
        }
    }

    $itemsReceived = (int) $receivedQuery->sum('boxes.items_total');
    $itemsDamaged  = (int) $damagedQuery->sum('quantity_damaged');
    $estimatedLoss = (int) $damagedQuery->sum('estimated_loss');

    return [
        'items_received_90d' => $itemsReceived,
        'items_damaged_90d'  => $itemsDamaged,
        'estimated_loss'     => $estimatedLoss,
        'shrinkage_pct'      => $itemsReceived > 0
            ? round(($itemsDamaged / $itemsReceived) * 100, 2)
            : 0,
    ];
}
```

After adding all methods, verify the file compiles:
```bash
php artisan tinker --execute="app(\App\Services\Analytics\InventoryAnalyticsService::class)->getPortfolioFillRate(); echo 'OK';"
```
If this throws, fix the error before continuing.

---

## Step 3 — Rebuild `InventoryValuation.php`

Replace the entire component with this structure. Keep the `mount()` guard.

```php
<?php

namespace App\Livewire\Owner\Reports;

use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Analytics\InventoryAnalyticsService;
use Livewire\Component;

class InventoryValuation extends Component
{
    // ─── Filters ──────────────────────────────────────────────────────────────
    public string $locationFilter = 'all';
    public string $activeTab      = 'overview'; // overview | valuation | health | replenishment

    protected $queryString = [
        'locationFilter' => ['except' => 'all'],
        'activeTab'      => ['except' => 'overview'],
    ];

    // ─── Lifecycle ────────────────────────────────────────────────────────────
    public function mount(): void
    {
        if (! auth()->user()->isOwner() && ! auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    // ─── Actions ──────────────────────────────────────────────────────────────
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ─── Computed: shared (loaded on every tab) ────────────────────────────────
    public function getInventoryKpisProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getInventoryKpis($this->locationFilter);
    }

    public function getPortfolioFillRateProperty(): ?float
    {
        return app(InventoryAnalyticsService::class)
            ->getPortfolioFillRate($this->locationFilter);
    }

    public function getShrinkageStatsProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getShrinkageStats($this->locationFilter);
    }

    // ─── Computed: Valuation tab ───────────────────────────────────────────────
    public function getInventoryByLocationProperty(): array
    {
        return app(InventoryAnalyticsService::class)->getInventoryByLocation();
    }

    public function getTopProductsByValueProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getTopProductsByValue($this->locationFilter, 20);
    }

    public function getCategoryConcentrationProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getCategoryConcentration($this->locationFilter);
    }

    // ─── Computed: Health tab ─────────────────────────────────────────────────
    public function getStockHealthProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getStockHealth($this->locationFilter);
    }

    public function getAgingAnalysisProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getAgingAnalysis($this->locationFilter);
    }

    public function getExpiringStockProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getExpiringStock($this->locationFilter, 30);
    }

    public function getVelocityClassificationProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getVelocityClassification($this->locationFilter);
    }

    public function getInventoryMovementTrendProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getInventoryMovementTrend($this->locationFilter);
    }

    // ─── Computed: Replenishment tab ──────────────────────────────────────────
    public function getDaysOnHandPerProductProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getDaysOnHandPerProduct($this->locationFilter, 50);
    }

    // ─── Supporting data ──────────────────────────────────────────────────────
    public function getWarehousesProperty()
    {
        return Warehouse::orderBy('name')->get();
    }

    public function getShopsProperty()
    {
        return Shop::orderBy('name')->get();
    }

    // ─── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        return view('livewire.owner.reports.inventory-valuation');
    }
}
```

---

## Step 4 — Rebuild the blade view

Replace `resources/views/livewire/owner/reports/inventory-valuation.blade.php`
entirely. Build it section by section as described below.

Read `sales-analytics.blade.php` first — match its tab bar HTML, filter bar
HTML, and page header structure exactly. Do not invent new patterns.

### 4A — Page header

```blade
<div class="dashboard-page-header">
    <div>
        <h1>Inventory Report</h1>
        <p>Valuation, stock health, velocity classification, and replenishment intelligence</p>
    </div>
</div>
```

### 4B — Filter bar

A horizontal bar with:

1. **Location filter** — `<select wire:model.live="locationFilter">`:
   - `<option value="all">All Locations</option>`
   - `<option value="warehouses">All Warehouses</option>`
   - `<option value="shops">All Shops</option>`
   - Loop `$warehouses`: `<option value="warehouse:{{ $wh->id }}">{{ $wh->name }}</option>`
   - Loop `$shops`: `<option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>`

Style the select to match the filter bar pattern from `sales-analytics.blade.php`.

### 4C — Headline KPI row (always visible, above tabs)

Six cards in a responsive CSS grid (`repeat(auto-fill, minmax(170px, 1fr))`).
Match the card shell pattern from existing KPI cards exactly.

**Card 1 — Cost Value**
Main: `number_format($inventoryKpis['purchase_value'])` RWF
Sub: "Capital invested"
Colour: accent

**Card 2 — Retail Value**
Main: `number_format($inventoryKpis['retail_value'])` RWF
Sub: "At selling price"
Colour: success

**Card 3 — Potential Margin**
Main: `number_format($inventoryKpis['potential_profit'])` RWF
Sub: Margin %: `{{ $inventoryKpis['purchase_value'] > 0 ? round(($inventoryKpis['potential_profit'] / $inventoryKpis['retail_value']) * 100, 1) : 0 }}%`
Colour: violet

**Card 4 — Fill Rate**
Main: `{{ $portfolioFillRate ?? '—' }}%`
Sub: "Items remaining vs capacity"
Colour: green ≥ 70%, amber 40–69%, red < 40%. Show "No stock" if null.

**Card 5 — Stock Turnover**
Main: `{{ number_format($inventoryKpis['turnover_rate'], 2) }}×`
Sub: "Annual (COGS ÷ inventory)"
Show "—" and "N/A for warehouses" if turnover_rate is 0 and locationFilter starts with "warehouse:"

**Card 6 — Shrinkage Rate**
Main: `{{ $shrinkageStats['shrinkage_pct'] }}%`
Sub: `{{ number_format($shrinkageStats['items_damaged_90d']) }} units damaged in 90d`
Colour: red > 2%, amber 0.5–2%, success < 0.5%

### 4D — Tab bar

Four tabs. Match the `sales-analytics.blade.php` tab bar markup exactly:

```
Overview    |    Valuation    |    Stock Health    |    Replenishment
```

Each tab: `wire:click="setTab('overview')"` etc.
Active tab highlighted with `var(--accent)` border-bottom and text colour.

---

### 4E — Tab: Overview

Show when `$activeTab === 'overview'`.

**Section: Inventory Movement Trend (12 weeks)**

A section label "Stock Movement — Last 12 Weeks".

Render a Chart.js bar chart using data from `$inventoryMovementTrend`.
Use `<canvas id="movementChart">` and a `<script>` block that initialises
a grouped bar chart with:
- x-axis: `week_label` values
- Dataset 1: `boxes_received` — bar colour `var(--accent)`
- Dataset 2: `items_consumed` — bar colour `var(--amber)` (line/bar)

Use the Chart.js pattern already used in other blades in the project — read an
existing blade that renders Chart.js to copy the initialisation pattern.

**Section: ABC Velocity Summary**

Four stat blocks side by side:

| Class | Count | Capital | Description |
|-------|-------|---------|-------------|
| A — Fast movers | `$velocityClassification['summary']['A_count']` | cost_value | Top 70% of revenue |
| B — Medium movers | B_count | B_cost_value | 70–90% of revenue |
| C — Slow movers | C_count | C_cost_value | Bottom 10% of revenue |
| Dead stock | Dead_count | Dead_cost_value | No sales in 90 days |

Colour: A = success, B = amber, C = warn, Dead = red.

**Section: Category Concentration**

Table with columns: Category | Products | Items | Cost Value | % of Total.
Render a small horizontal bar for `pct_of_total` next to the percentage figure.
Colour the bar: first category = accent, subsequent = accent at decreasing opacity.

---

### 4F — Tab: Valuation

Show when `$activeTab === 'valuation'`.

**Section: Value by Location**

Two side-by-side tables (warehouses | shops).
Each table: Location name | Items | Cost Value | Retail Value.
Show total row at bottom.

**Section: Top 20 Products by Capital Value**

Table: Rank | Product | Items in Stock | Cost Value | Retail Value | Locations (count).
Add a visual percentage bar in the Cost Value column showing each product's
share of total cost value.
Highlight the top 3 rows with a subtle `var(--accent-dim)` background.

---

### 4G — Tab: Stock Health

Show when `$activeTab === 'health'`.

**Section: Health Summary**

Four stat cards: Low Stock | Dead Stock (90d) | Expiring (30d) | Damaged.
Use existing `$stockHealth['low_stock_count']`, `$stockHealth['dead_stock_count']`,
`count($expiringStock)`, and the `$shrinkageStats['items_damaged_90d']`.

**Section: Stock Aging**

Render the 4 age brackets as a horizontal stacked bar, then a detail table:
Bracket | Boxes | Items | Cost Value | % of Total.
Colour: 0–30 = success, 31–60 = accent, 61–90 = amber, 90+ = red.

**Section: ABC Classification Detail**

Three collapsible sections (A / B / C), each showing a table of the products
in that class: Product | Items in Stock | Revenue 90d | Revenue % | Cost Value.
Dead stock section rendered prominently with red header and a note
"These products hold capital but generate no revenue".

**Section: Expiring Stock**

If `$expiringStock` is empty, show a green "No expiring stock" message.
Otherwise, a table: Product | Expiry Date | Days Until Expiry | Items | Cost Value.
Colour expiry dates: ≤7 days = red, ≤14 days = amber, else warn.

---

### 4H — Tab: Replenishment

Show when `$activeTab === 'replenishment'`.

**Section: Replenishment Urgency Table**

Section label "Products Requiring Action — Sorted by Urgency".

Table from `$daysOnHandPerProduct`, sorted by `days_on_hand` ascending:

| Product | Stock | Sold (30d) | Avg/Day | Days on Hand | Status | Suggested Order |
|---------|-------|-----------|---------|--------------|--------|-----------------|

- **Days on Hand** column: red chip if ≤ 7, amber if ≤ 14, green otherwise.
  If `null` (no recent sales), show "— No velocity" in `var(--text-dim)`.
- **Status** column: "Critical" (red) / "Reorder" (amber) / "OK" (success) / "No data" (dim).
- **Suggested Order** column (owner only): `max(0, (30 * avg_daily_sales) - items_remaining)`.
  Show "—" if no velocity.

Add a filter strip above the table: All | Critical only | Needs reorder.
Implement this as a local `$urgencyFilter` Livewire property
(`all` | `critical` | `reorder`) with `wire:model.live`.
Filter the `$daysOnHandPerProduct` array in the blade using `@php` and
`collect()` — do not add a new computed property for this.

**Section: Dead Stock Capital Lock**

Below the urgency table, a separate card listing the Dead class from
`$velocityClassification['Dead']`:
"The following products have inventory but zero sales in 90 days.
They represent {{ number_format($velocityClassification['summary']['Dead_cost_value']) }} RWF
in locked capital."

Table: Product | Items | Cost Value | Days Since Any Sale (compute from last `sale_items.created_at` if available, else "90d+").

---

## Step 5 — Verify everything works

Run these in order and fix any errors before proceeding:

```bash
php artisan view:clear
php artisan cache:clear
php artisan view:cache 2>&1 | grep -i "error\|exception" | head -30
```

Then open a tinker session to verify each new service method returns data
without throwing:

```bash
php artisan tinker
```

```php
$s = app(\App\Services\Analytics\InventoryAnalyticsService::class);
$s->getPortfolioFillRate();
$s->getVelocityClassification();
$s->getDaysOnHandPerProduct();
$s->getCategoryConcentration();
$s->getInventoryMovementTrend();
$s->getShrinkageStats();
echo "All OK";
```

Fix any exception before finishing.

Open `/owner/reports/inventory` in the browser and confirm:
1. All 4 tabs render without errors
2. The Chart.js movement trend chart draws correctly
3. The ABC classification table is populated
4. The replenishment urgency table is sortable by urgency
5. The location filter changes all data correctly

---

## Step 6 — Update CLAUDE.md

After all the above is working, open `CLAUDE.md` in the project root and
append the following block at the end. Do not remove or modify anything
already in CLAUDE.md.

```markdown
---

## Inventory Report (owner/reports/inventory) — Completed Rebuild

The following files were rebuilt as part of the inventory report upgrade:

- `app/Services/Analytics/InventoryAnalyticsService.php`
  — Bugs fixed in: getTopProductsByValue (grouping), calculateStockTurnover (warehouse filter), getAgingAnalysis (moved_at vs received_at)
  — New methods: getPortfolioFillRate, getVelocityClassification, getDaysOnHandPerProduct, getCategoryConcentration, getInventoryMovementTrend, getShrinkageStats

- `app/Livewire/Owner/Reports/InventoryValuation.php`
  — Added activeTab property (overview | valuation | health | replenishment)
  — Added setTab() action
  — Added computed properties for all new service methods
  — Auth guard now also accepts admin role

- `resources/views/livewire/owner/reports/inventory-valuation.blade.php`
  — Full rebuild: 4 tabs, 6 headline KPIs, Chart.js movement trend,
    ABC velocity classification, category concentration, aging analysis,
    expiry warning, replenishment urgency table with days-on-hand,
    dead stock capital lock section

Do NOT revert or partially edit these files without reading the inventory
report analysis notes in the project knowledge base.
```
