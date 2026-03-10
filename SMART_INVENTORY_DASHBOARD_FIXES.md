# Smart Inventory — Owner Dashboard: All Fixes & Enhancements
## Claude Code Master Instructions

> Drop in project root. Tell Claude Code:
> "Read SMART_INVENTORY_DASHBOARD_FIXES.md and follow every task in order. Do not skip any step."

---

## Overview of Changes

| # | Task | Files Affected | Type |
|---|------|---------------|------|
| 1 | Fix profit margin SQL (560% bug) | `BusinessKpiRow.php` | Bug fix |
| 2 | Fix chart typo (`val0`) | `sales-performance.blade.php` | Bug fix |
| 3 | Fix Alpine reactive chartInstance crash (`fullSize undefined`) | `sales-performance.blade.php`, `stock-distribution.blade.php` | Bug fix |
| 4 | Fix Live Activity showing raw enum strings | `owner/dashboard.blade.php`, `DashboardController.php` | Enhancement |
| 5 | Fix "TODAY" KPI showing "OK" instead of revenue | `owner/dashboard.blade.php` | Bug fix |
| 6 | Fix Sales chart blank on Week view | `owner/dashboard.blade.php`, `DashboardController.php` | Bug fix |
| 7 | Fix "Delivered Today" showing "—" | `owner/dashboard.blade.php`, `DashboardController.php` | Bug fix |
| 8 | Fix stock fill bars — no urgency signal | `owner/dashboard.blade.php` | Enhancement |
| 9 | Fix KPI sub-labels truncating | `owner/dashboard.blade.php`, `DashboardController.php` | Bug fix |
| 10 | Add Pending Actions banner | `owner/dashboard.blade.php` | New feature |
| 11 | Add Inventory Health row | `owner/dashboard.blade.php` | New feature |

---

## TASK 1 — Fix Profit Margin Bug (560% → Correct %)

**File:** `app/Livewire/Dashboard/BusinessKpiRow.php`

### Root Cause

Unit mismatch in the margin SQL. When a full box is sold:
- `sale_items.actual_unit_price` = **box price** (e.g. 55,000 RWF)
- `sale_items.quantity_sold` = **item count** (e.g. 6)
- `products.purchase_price` = **per-item cost** (e.g. 5,833 RWF)

The old SQL does `(55,000 − 5,833) × 6 = 295,002` margin against 55,000 revenue = 536%.
The fix uses `line_total` which is always the correct revenue per row.

### Step 1.1 — Diagnose (verify before changing)

```bash
php artisan tinker --execute="
\$si = App\Models\SaleItem::with('product')->where('is_full_box', true)->latest()->first();
if (\$si) {
    echo 'actual_unit_price : ' . number_format(\$si->actual_unit_price) . ' RWF' . PHP_EOL;
    echo 'quantity_sold     : ' . \$si->quantity_sold . ' items' . PHP_EOL;
    echo 'line_total        : ' . number_format(\$si->line_total) . ' RWF' . PHP_EOL;
    echo 'purchase_price    : ' . number_format(\$si->product->purchase_price) . ' RWF/item' . PHP_EOL;
    echo 'Wrong margin: ' . number_format((\$si->actual_unit_price - \$si->product->purchase_price) * \$si->quantity_sold) . PHP_EOL;
    echo 'Correct margin: ' . number_format(\$si->line_total - (\$si->product->purchase_price * \$si->quantity_sold)) . PHP_EOL;
}
"
```

### Step 1.2 — Fix all four margin queries in `loadData()`

In `app/Livewire/Dashboard/BusinessKpiRow.php`, find and replace the following SQL pattern in **all four occurrences** (period margin, `$todayMargin`, `$weekMargin`, `$monthMargin`):

**WRONG pattern (appears 4 times):**
```php
->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                * sale_items.quantity_sold) as margin')
```

**CORRECT replacement (use for all 4):**
```php
->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
```

### Step 1.3 — Check for same bug in other files

```bash
grep -rn "actual_unit_price - products.purchase_price" app/ --include="*.php"
```

For every hit outside `BusinessKpiRow.php`, apply the same replacement.

### Step 1.4 — Verify fix

```bash
php artisan tinker --execute="
use App\Models\SaleItem;
\$margin = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->whereNull('sales.voided_at')
    ->whereBetween('sales.sale_date', [now()->startOfMonth(), now()])
    ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
    ->value('margin') ?? 0;
\$revenue = App\Models\Sale::notVoided()->whereBetween('sale_date', [now()->startOfMonth(), now()])->sum('total');
echo 'Revenue: RWF ' . number_format(\$revenue) . PHP_EOL;
echo 'Margin:  RWF ' . number_format(\$margin) . PHP_EOL;
echo 'Margin%: ' . (\$revenue > 0 ? round(\$margin / \$revenue * 100, 1) : 0) . '%' . PHP_EOL;
"
```

Expected: margin % between 20–45% for a shoe retail business.

---

## TASK 2 — Fix Chart Y-Axis Typo (`val0` → `val / 1000`)

**File:** `resources/views/livewire/dashboard/sales-performance.blade.php`

### The Bug

`ReferenceError: val0 is not defined` — crashes the entire Chart.js render pipeline, leaving the sales chart blank.

### Fix

Find the `yRevenue` axis tick callback:

**BEFORE:**
```js
callback: function(val) {
    return val >= 1000 ? Math.round(val0) + 'K' : val;
}
```

**AFTER:**
```js
callback: function(val) {
    return val >= 1000 ? Math.round(val / 1000) + 'K' : val;
}
```

That is the only change in this task.

---

## TASK 3 — Fix Alpine Reactive Chart Crash (`fullSize undefined`)

**Error:** `TypeError: can't access property "fullSize", e is undefined`

**Root Cause:** Chart.js instances stored as Alpine reactive properties get wrapped in a Proxy. When Livewire morphs, `toRaw()` recurses into Chart.js's circular reference (`chart.canvas.chart = chart`) infinitely, corrupting the instance. The fix is to store chart instances on the DOM element, not in Alpine state.

### Fix 3A — `resources/views/livewire/dashboard/sales-performance.blade.php`

In the `updateChart()` method, wrap the update call in a try/catch that falls back to a full redraw:

**BEFORE:**
```js
canvas._chartInstance.data.labels = data.labels;
canvas._chartInstance.data.datasets[0].data = data.revenueData;
canvas._chartInstance.data.datasets[1].data = data.countData;
canvas._chartInstance.update('none');
```

**AFTER:**
```js
canvas._chartInstance.data.labels = data.labels;
canvas._chartInstance.data.datasets[0].data = data.revenueData;
canvas._chartInstance.data.datasets[1].data = data.countData;
try {
    canvas._chartInstance.update('none');
} catch (e) {
    canvas._chartInstance.destroy();
    delete canvas._chartInstance;
    this.draw();
}
```

### Fix 3B — `resources/views/livewire/dashboard/stock-distribution.blade.php`

**Problem 1:** `chartInstance` is declared inside the Alpine `return {}` object, making it reactive — this is what causes the crash.

**BEFORE:**
```js
function stockDistChart() {
    return {
        chartInstance: null,
        morphHook: null,
```

**AFTER** — remove `chartInstance` from reactive state entirely:
```js
function stockDistChart() {
    return {
        morphHook: null,
```

**Problem 2:** Every `this.chartInstance` reference must be replaced with DOM-stored references. Make these replacements throughout the same file:

Replace all writes:
```js
// BEFORE:
this.chartInstance = new Chart(canvas, { ... });

// AFTER:
canvas._chartInstance = new Chart(canvas, { ... });
```

Replace all reads in `updateChart()` — add a canvas lookup at the top and replace `this.chartInstance`:
```js
updateChart() {
    var canvas = document.getElementById('stockDistributionChart');
    if (!canvas || !canvas._chartInstance) {
        this.draw();
        return;
    }
    var raw = this.$el.dataset.chart;
    var stockData = raw ? JSON.parse(raw) : [];
    if (!stockData.length) return;

    canvas._chartInstance.data.labels = stockData.map(function(i) { return i.location_name; });
    canvas._chartInstance.data.datasets[0].data = stockData.map(function(i) { return i.box_count; });
    canvas._chartInstance.data.datasets[0].backgroundColor = stockData.map(function(i) { return i.color; });
    try {
        canvas._chartInstance.update('none');
    } catch (e) {
        canvas._chartInstance.destroy();
        delete canvas._chartInstance;
        this.draw();
    }

    var totalEl = document.getElementById('stockDistTotal');
    if (totalEl) {
        totalEl.textContent = parseInt(this.$el.dataset.total || 0).toLocaleString();
    }
},
```

**Problem 3:** Fix `teardown()` — `canvas` is used before it is defined:

**BEFORE:**
```js
teardown() {
    if (this.chartInstance) {
        this.chartInstance.destroy();
        this.chartInstance = null;
    }
    var orphan = Chart.getChart(canvas);   // ← canvas is undefined here!
    if (orphan) orphan.destroy();
    if (typeof this.morphHook === 'function') {
        this.morphHook();
    }
},
```

**AFTER:**
```js
teardown() {
    var canvas = document.getElementById('stockDistributionChart');
    if (canvas && canvas._chartInstance) {
        canvas._chartInstance.destroy();
        delete canvas._chartInstance;
    }
    if (canvas) {
        var orphan = Chart.getChart(canvas);
        if (orphan) orphan.destroy();
    }
    if (typeof this.morphHook === 'function') {
        this.morphHook();
    }
},
```

After Tasks 1–3, run:
```bash
php artisan view:clear
php artisan cache:clear
```

Reload the dashboard. The chart should render, no console errors, and margin % should be realistic.

---

## TASK 4 — Fix Live Activity Feed (Raw Enum Strings → Human-Readable)

**Files:** `resources/views/owner/dashboard.blade.php`, `app/Http/Controllers/Owner/DashboardController.php`

### Step 4.1 — Add `$recentActivities` to the controller

In `DashboardController::index()`, add before the `return view(...)` call:

```php
use App\Models\ActivityLog; // add to imports if not present

$recentActivities = ActivityLog::with('user')
    ->orderByDesc('created_at')
    ->limit(10)
    ->get();
```

Pass it to the view: add `'recentActivities' => $recentActivities` to the view data array.

> **Note:** Check the actual model name with `ls app/Models/ | grep -i activity`. It may be `ActivityLog` or `Activity`. Adjust the class reference accordingly.

### Step 4.2 — Replace the Live Activity card in the blade

Find the "Live Activity" card in `resources/views/owner/dashboard.blade.php` and replace the inner loop with:

```blade
@foreach($recentActivities as $activity)
<div class="flex items-start space-x-3 py-2.5 border-b border-gray-50 last:border-0">
    {{-- Icon badge --}}
    <div class="mt-0.5 flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center
        {{ match(true) {
            str_contains($activity->action ?? '', 'transfer') || str_contains($activity->action ?? '', 'approved') => 'bg-blue-100',
            str_contains($activity->action ?? '', 'sale')    => 'bg-green-100',
            str_contains($activity->action ?? '', 'return')  => 'bg-amber-100',
            str_contains($activity->action ?? '', 'damage')  => 'bg-red-100',
            default                                          => 'bg-gray-100',
        } }}">
        @if(str_contains($activity->action ?? '', 'transfer') || str_contains($activity->action ?? '', 'approved'))
            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
        @elseif(str_contains($activity->action ?? '', 'sale'))
            <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        @else
            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        @endif
    </div>

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        <p class="text-xs font-semibold text-gray-900 capitalize">
            {{ str_replace(['_', '-'], ' ', $activity->action ?? 'Unknown action') }}
        </p>
        @if(($activity->entity_type ?? null) && ($activity->entity_id ?? null))
            <p class="text-xs text-gray-500 truncate">
                {{ class_basename($activity->entity_type) }} #{{ $activity->entity_id }}
                @if($activity->user) · {{ $activity->user->name }} @endif
            </p>
        @endif
        <p class="text-xs text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
    </div>
</div>
@endforeach
```

---

## TASK 5 — Fix "TODAY" KPI Showing "OK"

**File:** `resources/views/owner/dashboard.blade.php`

Find the sales summary row that renders `TODAY` / `THIS WEEK` / `THIS MONTH` values. Look for any conditional that outputs `'OK'` as a placeholder when the value is zero (pattern: `$salesStats['today'] == 0 ? 'OK' : ...` or similar in the blade).

Replace any such conditionals with proper zero-safe number formatting:

```blade
{{-- TODAY --}}
<p class="text-xl font-bold text-gray-900">
    {{ $salesStats['today'] > 0 ? number_format($salesStats['today']) . ' RWF' : '0 RWF' }}
</p>

{{-- THIS WEEK --}}
<p class="text-xl font-bold text-gray-900">
    {{ $salesStats['this_week'] > 0 ? number_format($salesStats['this_week']) . ' RWF' : '0 RWF' }}
</p>

{{-- THIS MONTH --}}
<p class="text-xl font-bold text-gray-900">
    {{ $salesStats['this_month'] > 0 ? number_format($salesStats['this_month']) . ' RWF' : '0 RWF' }}
</p>
```

> Also check the blade file for the pattern `'var(--accent)' : 'var(--success)' }};...{{ $sales[$k] > 0 ? number_format($sales[$k] / 1000, 0).'K' : 'OK' }}` inside the dashboard template — replace the `'OK'` fallback with `'0'` there as well.

---

## TASK 6 — Fix Sales Chart Blank on Week View

**Files:** `resources/views/owner/dashboard.blade.php`, `app/Http/Controllers/Owner/DashboardController.php`

### Step 6.1 — Add 7-day chart data to the controller

In `DashboardController::index()`, add before `return view(...)`:

```php
$salesChartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = now()->subDays($i);
    $salesChartData[] = [
        'label'        => $date->format('D'),
        'date'         => $date->toDateString(),
        'revenue'      => Sale::notVoided()->whereDate('sale_date', $date)->sum('total'),
        'transactions' => Sale::notVoided()->whereDate('sale_date', $date)->count(),
    ];
}
```

Pass it to the view: `'salesChartData' => $salesChartData`.

### Step 6.2 — Wire data to the Alpine chart toggle in the blade

Find the Alpine component that handles the chart period toggle. Ensure the `data-chart` attribute on the chart wrapper is updated reactively. The `SalesPerformance` Livewire component handles this via `wire:click="setChartPeriod()"` — verify the `chartData` property on `app/Livewire/Dashboard/SalesPerformance.php` is computed correctly for each period and returns a non-empty dataset for the `week` period.

In `SalesPerformance.php`, check the `loadChartData()` / `getChartDataProperty()` method and confirm it queries `Sale` records for the correct date range when `$chartPeriod === 'week'`. If the query has a wrong date range or missing `notVoided()` scope, fix it to match:

```php
case 'week':
    $start = now()->startOfWeek();
    $end   = now()->endOfDay();
    break;
```

---

## TASK 7 — Fix "Delivered Today" Showing "—"

**Files:** `resources/views/owner/dashboard.blade.php`, `app/Http/Controllers/Owner/DashboardController.php`

### Step 7.1 — Add count to controller

In `DashboardController::index()`:

```php
use Carbon\Carbon;
// Add to imports if not present: use App\Enums\TransferStatus;

$deliveredToday = Transfer::whereIn('status', [
        TransferStatus::DELIVERED,
        TransferStatus::RECEIVED,
    ])
    ->whereDate('delivered_at', Carbon::today())
    ->count();
```

Pass to view: `'deliveredToday' => $deliveredToday`.

### Step 7.2 — Update blade

Find the "Delivered Today" row in the Transfer Status card and replace the dash with:

```blade
<span class="font-bold text-gray-900">{{ $deliveredToday }}</span>
```

---

## TASK 8 — Fix Stock Fill Bars (No Urgency Signal)

**File:** `resources/views/owner/dashboard.blade.php`

Find the "Stock fill per shop" loop in the Top Performing Shops card. Replace it with colour-coded status:

```blade
@foreach($shopStockFill as $shopFill)
<div class="flex items-center justify-between text-xs mb-2">
    <span class="text-gray-600 truncate w-32">{{ $shopFill['name'] }}</span>
    <div class="flex-1 mx-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
        <div class="h-full rounded-full transition-all duration-500
            {{ $shopFill['pct'] >= 50 ? 'bg-emerald-500' :
               ($shopFill['pct'] >= 20 ? 'bg-amber-400' : 'bg-red-500') }}"
             style="width: {{ $shopFill['pct'] }}%"></div>
    </div>
    <span class="font-semibold w-8 text-right
        {{ $shopFill['pct'] >= 50 ? 'text-emerald-600' :
           ($shopFill['pct'] >= 20 ? 'text-amber-600' : 'text-red-600') }}">
        {{ $shopFill['pct'] }}%
    </span>
    @if($shopFill['pct'] < 20)
        <span class="ml-1 text-red-500" title="Critical stock level">⚠</span>
    @endif
</div>
@endforeach
```

Ensure `$shopStockFill` is passed from the controller as an array of `['name' => ..., 'pct' => ...]`. If it isn't already computed, add to `DashboardController::index()`:

```php
$shopStockFill = Shop::active()->get()->map(function ($shop) {
    $maxCapacity = Product::active()->sum('low_stock_threshold') ?: 1;
    $currentItems = Box::where('location_type', 'shop')
        ->where('location_id', $shop->id)
        ->whereIn('status', ['full', 'partial'])
        ->sum('items_remaining');
    // Use box count ratio instead if threshold data is sparse
    $totalBoxes = Box::where('location_type', 'shop')->where('location_id', $shop->id)->count() ?: 0;
    $warehouseBoxes = Box::where('location_type', 'warehouse')->sum('items_remaining') ?: 1;
    $shopBoxes = Box::where('location_type', 'shop')->where('location_id', $shop->id)->sum('items_remaining');
    $pct = $warehouseBoxes > 0 ? min(100, round(($shopBoxes / ($shopBoxes + $warehouseBoxes)) * 100)) : 0;
    return ['name' => $shop->name, 'pct' => $pct];
})->toArray();
```

Pass to view: `'shopStockFill' => $shopStockFill`.

---

## TASK 9 — Fix KPI Sub-Labels Truncating

**Files:** `resources/views/owner/dashboard.blade.php`, `app/Http/Controllers/Owner/DashboardController.php`

### Step 9.1 — Add warehouse/shop box split to controller

In `DashboardController::index()`, ensure these two keys exist in `$stats`:

```php
use App\Enums\LocationType;

$stats['warehouse_boxes'] = Box::where('location_type', LocationType::WAREHOUSE->value)->count();
$stats['shop_boxes']      = Box::where('location_type', LocationType::SHOP->value)->count();
```

### Step 9.2 — Fix the Active Boxes sub-label in blade

Find the Active Boxes KPI card sub-label (currently truncating with `Warehouse: 318 · Shops:…`) and replace the sub-text element with:

```blade
<p class="text-xs text-gray-500 mt-0.5">
    WH: {{ number_format($stats['warehouse_boxes']) }} &nbsp;·&nbsp; Shops: {{ number_format($stats['shop_boxes']) }}
</p>
```

Find the Active Transfers sub-label (truncating `In transit: 6 · Pending…`) and ensure it uses a two-line structure without `truncate` / `overflow-hidden`:

```blade
<p class="text-xs text-gray-500 mt-0.5">
    In transit: {{ $transferStats['in_transit'] }} &nbsp;·&nbsp; Pending: {{ $transferStats['pending'] }}
</p>
```

---

## TASK 10 — Add Pending Actions Banner

**File:** `resources/views/owner/dashboard.blade.php`

Add the following block **immediately below the page header `<div>` and date filter row**, before the Priority KPIs / Inventory Health section:

```blade
@php
    $pendingApprovalCount = \App\Models\Transfer::where('status', 'pending')->count();
    $discrepancyCount     = \App\Models\Transfer::where('has_discrepancy', true)->count();
    $criticalAlertsCount  = \App\Models\Alert::where('severity', 'critical')
                                ->whereNull('resolved_at')
                                ->where('is_dismissed', false)
                                ->count();

    // Damaged goods — check actual model name with: ls app/Models | grep -i damage
    // Adjust class name below if different (e.g. DamagedGoods, DamagedGood)
    $damagedPendingCount = 0;
    if (class_exists(\App\Models\DamagedGood::class)) {
        $damagedPendingCount = \App\Models\DamagedGood::where('disposition', 'pending')->count();
    }

    $hasPendingActions = ($pendingApprovalCount + $discrepancyCount + $criticalAlertsCount + $damagedPendingCount) > 0;
@endphp

@if($hasPendingActions)
<div class="mb-4 bg-amber-50 border border-amber-200 rounded-lg p-3">
    <p class="text-xs font-bold text-amber-800 uppercase tracking-wide mb-2">⚡ Requires Your Attention</p>
    <div class="flex flex-wrap gap-2">
        @if($pendingApprovalCount > 0)
        <a href="{{ route('warehouse.transfers.index') }}"
           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white border border-amber-300 rounded-lg text-xs font-semibold text-amber-800 hover:bg-amber-100 transition-colors">
            <span class="w-5 h-5 bg-amber-500 text-white rounded-full flex items-center justify-center text-xs font-bold">{{ $pendingApprovalCount }}</span>
            <span>Transfer{{ $pendingApprovalCount > 1 ? 's' : '' }} Awaiting Approval</span>
        </a>
        @endif
        @if($discrepancyCount > 0)
        <a href="{{ route('warehouse.transfers.index') }}"
           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white border border-red-300 rounded-lg text-xs font-semibold text-red-800 hover:bg-red-50 transition-colors">
            <span class="w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs font-bold">{{ $discrepancyCount }}</span>
            <span>Unresolved Discrepanc{{ $discrepancyCount > 1 ? 'ies' : 'y' }}</span>
        </a>
        @endif
        @if($damagedPendingCount > 0)
        <a href="#"
           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white border border-orange-300 rounded-lg text-xs font-semibold text-orange-800 hover:bg-orange-50 transition-colors">
            <span class="w-5 h-5 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs font-bold">{{ $damagedPendingCount }}</span>
            <span>Damaged Goods Pending Decision</span>
        </a>
        @endif
        @if($criticalAlertsCount > 0)
        <a href="#"
           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white border border-red-300 rounded-lg text-xs font-semibold text-red-800 hover:bg-red-50 transition-colors">
            <span class="w-5 h-5 bg-red-600 text-white rounded-full flex items-center justify-center text-xs font-bold">{{ $criticalAlertsCount }}</span>
            <span>Critical Alert{{ $criticalAlertsCount > 1 ? 's' : '' }}</span>
        </a>
        @endif
    </div>
</div>
@endif
```

---

## TASK 11 — Add Inventory Health Row

**File:** `resources/views/owner/dashboard.blade.php`

Ensure the controller already passes `$stats['inventory_value']`, `$stats['retail_value']`, `$stats['potential_profit']`, and `$stats['total_items_in_stock']`. These should be present in `DashboardController` — if any are missing, add:

```php
// In DashboardController::index(), already should exist but verify:
$inventoryValue = Box::whereIn('status', ['full', 'partial'])
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->selectRaw('SUM(boxes.items_remaining * products.purchase_price) as cost')
    ->value('cost') ?? 0;

$retailValue = Box::whereIn('status', ['full', 'partial'])
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->selectRaw('SUM(boxes.items_remaining * products.selling_price) as retail')
    ->value('retail') ?? 0;

$stats['inventory_value']    = $inventoryValue;    // already RWF — no /100 needed
$stats['retail_value']       = $retailValue;
$stats['potential_profit']   = $retailValue - $inventoryValue;
$stats['total_items_in_stock'] = Box::whereIn('status', ['full', 'partial'])->sum('items_remaining');
```

Then add this 3-card row in the blade, placed **between the Priority KPIs section and the Sales Performance section**:

```blade
<!-- Inventory Health Row -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
    <!-- Stock Cost Value (Owner-only) -->
    <div class="bg-gradient-to-br from-slate-700 to-slate-900 rounded-lg p-4 text-white">
        <div class="flex items-center justify-between mb-1">
            <p class="text-xs font-bold text-slate-300 uppercase tracking-wide">Stock Cost Value</p>
            <span class="text-xs bg-slate-600 text-slate-200 px-1.5 py-0.5 rounded font-semibold">Owner</span>
        </div>
        <p class="text-2xl font-bold mt-2">{{ number_format($stats['inventory_value']) }} <span class="text-sm font-normal text-slate-300">RWF</span></p>
        <p class="text-xs text-slate-400 mt-1">What you paid · purchase price basis</p>
    </div>

    <!-- Potential Retail Value -->
    <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg p-4 text-white">
        <p class="text-xs font-bold text-blue-200 uppercase tracking-wide mb-1">Potential Retail Value</p>
        <p class="text-2xl font-bold mt-2">{{ number_format($stats['retail_value']) }} <span class="text-sm font-normal text-blue-200">RWF</span></p>
        <p class="text-xs text-blue-300 mt-1">{{ number_format($stats['total_items_in_stock']) }} sellable items in stock</p>
    </div>

    <!-- Potential Gross Margin -->
    <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-lg p-4 text-white">
        <p class="text-xs font-bold text-emerald-200 uppercase tracking-wide mb-1">Potential Gross Margin</p>
        <p class="text-2xl font-bold mt-2">{{ number_format($stats['potential_profit']) }} <span class="text-sm font-normal text-emerald-200">RWF</span></p>
        @php
            $marginPct = $stats['retail_value'] > 0
                ? round(($stats['potential_profit'] / $stats['retail_value']) * 100, 1)
                : 0;
        @endphp
        <p class="text-xs text-emerald-300 mt-1">{{ $marginPct }}% potential margin on current stock</p>
    </div>
</div>
```

---

## Final Steps — Run After All Tasks Complete

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

Reload the Owner Dashboard and verify the full checklist:

- [ ] Profit card shows realistic margin % (20–45%), not 560%
- [ ] Sales chart renders on all period tabs (Today / Week / Month / Quarter) with no console errors
- [ ] No `ReferenceError: val0 is not defined` in console
- [ ] No `TypeError: can't access property "fullSize"` in console
- [ ] Stock Distribution doughnut chart renders and updates without errors
- [ ] Live Activity shows human-readable action labels with colour-coded icons
- [ ] "TODAY" KPI shows `0 RWF` (not "OK") when no sales recorded today
- [ ] "Delivered Today" shows a numeric count
- [ ] Low-stock shop bars are red at < 20%, amber at 20–49%, green at ≥ 50%
- [ ] Active Boxes sub-label shows full `WH: X · Shops: Y` without truncation
- [ ] Pending Actions amber banner appears when transfers/alerts/damage are pending
- [ ] Inventory Health row (3 gradient cards) renders with correct RWF values

---

## File Summary

| File | Tasks |
|------|-------|
| `app/Livewire/Dashboard/BusinessKpiRow.php` | Task 1 (margin SQL ×4) |
| `resources/views/livewire/dashboard/sales-performance.blade.php` | Task 2 (val0 typo), Task 3A (try/catch) |
| `resources/views/livewire/dashboard/stock-distribution.blade.php` | Task 3B (reactive chartInstance + teardown fix) |
| `app/Http/Controllers/Owner/DashboardController.php` | Tasks 4, 6, 7, 8, 9, 11 (controller variables) |
| `resources/views/owner/dashboard.blade.php` | Tasks 4–11 (all blade changes) |
| Any other file matching `grep "actual_unit_price - products.purchase_price"` | Task 1 extension |
