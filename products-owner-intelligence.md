# Products Page — Owner Intelligence Enhancements

> Read this file in full before writing any code.

## Overview

This task adds **8 owner-facing intelligence improvements** to the Products section.
No schema changes are required — all data is already in the database.

### Files to modify

| File | Change type |
|------|-------------|
| `app/Livewire/Owner/Products/ProductKpiRow.php` | Extend data computation |
| `resources/views/livewire/owner/products/product-kpi-row.blade.php` | Extend cards (2 new + 1 expanded) |
| `app/Livewire/Owner/Products/ProductList.php` | Add margin % to salesStats enrichment |
| `resources/views/livewire/products/product-list.blade.php` | Add Margin % column (owner only) |
| `app/Livewire/Owner/Products/ProductDetail.php` | Add 4 new data computations |
| `resources/views/livewire/owner/products/product-detail.blade.php` | Add 4 new drawer sections |

### Files to read first (do NOT modify)

Before writing any code, read these files in full to understand
current structure, existing variable names, and design patterns:

```
app/Livewire/Owner/Products/ProductKpiRow.php
app/Livewire/Owner/Products/ProductList.php
app/Livewire/Owner/Products/ProductDetail.php
resources/views/livewire/owner/products/product-kpi-row.blade.php
resources/views/livewire/products/product-list.blade.php
resources/views/livewire/owner/products/product-detail.blade.php
```

---

## Design Rules (follow exactly — do not invent new patterns)

The entire codebase uses a single CSS variable system. All new HTML
must use only these tokens and follow the patterns below.

### CSS variables in use

```
Colors:    var(--text)  var(--text-sub)  var(--text-dim)
           var(--surface)  var(--surface2)  var(--surface3)
           var(--border)
           var(--accent)  var(--accent-dim)
           var(--success) var(--success-glow)
           var(--warn)    var(--warn-glow)
           var(--danger)  var(--danger-glow)
           var(--amber)   var(--amber-dim)
           var(--violet)  var(--violet-dim)
           var(--red)     var(--red-dim)
Radius:    var(--r)   var(--rsm)
```

### KPI card pattern (match existing cards exactly)

Look at the existing cards in `product-kpi-row.blade.php`. Every card uses:
- Outer wrapper with `background:var(--surface); border:1px solid var(--border); border-radius:var(--r)`
- Icon area, main value in large bold text, label in `var(--text-sub)` uppercase small-caps
- A "sub-row" strip at the bottom using `background:var(--surface2); border-top:1px solid var(--border)`
- Colour accent chips using `background:var(--X-glow); color:var(--X)` pattern

**Copy the exact wrapper classnames and inline styles from the existing cards.
Do not create new class names for the card shells.**

### Table pattern (match existing product list table exactly)

Look at the existing `<table>` in `product-list.blade.php`. New columns must:
- Use the same `<th>` and `<td>` tag structure as adjacent columns
- Owner-only columns: wrap in `@if($isOwner) ... @endif`
- Colour bands for margin: green `var(--success)` / amber `var(--amber)` / red `var(--danger)` using inline style chips

### Drawer pattern (match existing sections in product-detail.blade.php)

The drawer has section blocks. Each section uses a consistent header
(`font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.8px;
color:var(--text-sub)`) followed by content. Match this precisely for new sections.

---

## Task 1 — Stagnant Stock KPI Card (new card)

### PHP — `app/Livewire/Owner/Products/ProductKpiRow.php`

Add a `$stagnantCount` variable. Compute it inside `render()` **after** the
existing `$salesStats` enrichment loop (which is already used for other cards).

The logic: active products that have `total_items > 0` AND have zero
revenue in `$salesStats` for the selected period.

```php
// Add these two imports at top of file if not already present:
use Illuminate\Support\Facades\DB;

// Inside render(), after the existing $salesStats loop:

// ── Stagnant stock: has inventory but no sales in period ──────────────────
$allActiveWithStock = Product::where('is_active', true)
    ->withSum(['boxes as total_items' => fn ($q) =>
        $q->whereIn('status', ['full', 'partial'])
          ->where('items_remaining', '>', 0)
    ], 'items_remaining')
    ->get();

$soldProductIds = DB::table('sale_items')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->whereNull('sales.voided_at')
    ->whereNull('sales.deleted_at')
    ->whereBetween('sales.sale_date', [$start, $end])
    ->pluck('sale_items.product_id')
    ->unique()
    ->values();

$stagnantCount = $allActiveWithStock->filter(function ($p) use ($soldProductIds) {
    return ($p->total_items ?? 0) > 0
        && ! $soldProductIds->contains($p->id);
})->count();

// ── Catalog cost vs retail value ──────────────────────────────────────────
$catalogVal = DB::table('boxes')
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->whereIn('boxes.status', ['full', 'partial'])
    ->where('boxes.items_remaining', '>', 0)
    ->whereNull('products.deleted_at')
    ->where('products.is_active', true)
    ->selectRaw('
        SUM(boxes.items_remaining * products.purchase_price) as cost_value,
        SUM(boxes.items_remaining * products.selling_price)  as retail_value
    ')
    ->first();

$catalogCost   = (int) ($catalogVal->cost_value   ?? 0);
$catalogRetail = (int) ($catalogVal->retail_value ?? 0);
$catalogMarkup = $catalogCost > 0
    ? round((($catalogRetail - $catalogCost) / $catalogCost) * 100, 1)
    : 0;

// ── Portfolio average margin % ────────────────────────────────────────────
$portfolioAgg = DB::table('sale_items')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->join('products', 'sale_items.product_id', '=', 'products.id')
    ->whereNull('sales.voided_at')
    ->whereNull('sales.deleted_at')
    ->whereBetween('sales.sale_date', [$start, $end])
    ->selectRaw('
        SUM(sale_items.line_total) as revenue,
        SUM(products.purchase_price * sale_items.quantity_sold) as cost
    ')
    ->first();

$portfolioRevenue = (float) ($portfolioAgg->revenue ?? 0);
$portfolioCost    = (float) ($portfolioAgg->cost    ?? 0);
$portfolioMargin  = $portfolioRevenue > 0
    ? round((($portfolioRevenue - $portfolioCost) / $portfolioRevenue) * 100, 1)
    : null;
```

Pass all new variables to the view. The final `return view(...)` call must
include these additional keys alongside the existing ones:

```php
return view('livewire.owner.products.product-kpi-row', [
    // ... all existing keys unchanged ...
    'stagnantCount'  => $stagnantCount,
    'catalogCost'    => $catalogCost,
    'catalogRetail'  => $catalogRetail,
    'catalogMarkup'  => $catalogMarkup,
    'portfolioMargin' => $portfolioMargin,
    // bestMarginPct and bestMarginName remain unchanged
]);
```

### Blade — `resources/views/livewire/owner/products/product-kpi-row.blade.php`

Read the blade file fully first. Then make these three changes:

**Change A — Expand the existing "Best Margin" card** to show portfolio
avg margin as the headline, with best single product as the sub-row:

Find the Best Margin card block. Replace its main value display with:

```blade
{{-- Main value: portfolio avg margin --}}
@if($portfolioMargin !== null)
    <div style="font-size:28px;font-weight:800;color:var(--text);line-height:1">
        {{ $portfolioMargin }}%
    </div>
    <div style="font-size:12px;font-weight:600;color:var(--text-sub);text-transform:uppercase;
                letter-spacing:.7px;margin-top:4px">
        Avg Margin · {{ $periodLabel }}
    </div>
@else
    <div style="font-size:22px;font-weight:700;color:var(--text-sub)">No sales yet</div>
@endif
```

Replace its sub-row with:

```blade
{{-- Sub-row: best single product --}}
@if($bestMarginPct !== null)
<div style="border-top:1px solid var(--border);background:var(--surface2);
            padding:8px 16px;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
    <span style="font-size:11px;font-weight:700;color:var(--text-sub);
                 text-transform:uppercase;letter-spacing:.6px">Best:</span>
    <span style="font-size:12px;font-weight:600;color:var(--text);
                 overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px"
          title="{{ $bestMarginName }}">{{ $bestMarginName }}</span>
    <span style="margin-left:auto;font-size:13px;font-weight:800;
                 color:var(--success);background:var(--success-glow);
                 padding:2px 8px;border-radius:20px">{{ $bestMarginPct }}%</span>
</div>
@endif
```

**Change B — Add a new "Stagnant Stock" card** as the 5th card.
Place it immediately after the existing 4th card (Best Margin).
Match the exact wrapper structure of the existing cards:

```blade
{{-- Card 5: Stagnant Stock --}}
<div style="background:var(--surface);border:1px solid var(--border);
            border-radius:var(--r);overflow:hidden;display:flex;flex-direction:column">

    {{-- Card header --}}
    <div style="padding:16px 20px 12px;display:flex;align-items:flex-start;
                justify-content:space-between;gap:12px;flex:1">
        <div style="display:flex;flex-direction:column;gap:6px;flex:1">

            {{-- Icon + label --}}
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:32px;height:32px;border-radius:8px;
                            background:var(--amber-dim);display:flex;
                            align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                         stroke="var(--amber)" stroke-width="2.2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <span style="font-size:11px;font-weight:700;color:var(--text-sub);
                             text-transform:uppercase;letter-spacing:.8px">Stagnant Stock</span>
            </div>

            {{-- Main value --}}
            <div style="display:flex;align-items:baseline;gap:8px;margin-top:4px">
                <span style="font-size:32px;font-weight:800;color:var(--text);line-height:1">
                    {{ $stagnantCount }}
                </span>
                <span style="font-size:13px;font-weight:500;color:var(--text-sub)">products</span>
            </div>

            <div style="font-size:12px;color:var(--text-sub);line-height:1.4">
                Have stock but
                <strong style="color:var(--amber)">no sales</strong>
                this {{ strtolower($periodLabel) }}
            </div>
        </div>

        {{-- Alert badge --}}
        @if($stagnantCount > 0)
        <div style="background:var(--amber-dim);color:var(--amber);font-size:11px;
                    font-weight:800;padding:3px 9px;border-radius:20px;
                    white-space:nowrap;flex-shrink:0">
            Action needed
        </div>
        @else
        <div style="background:var(--success-glow);color:var(--success);font-size:11px;
                    font-weight:800;padding:3px 9px;border-radius:20px;
                    white-space:nowrap;flex-shrink:0">
            All moving
        </div>
        @endif
    </div>

    {{-- Sub-row --}}
    <div style="border-top:1px solid var(--border);background:var(--surface2);
                padding:8px 16px;display:flex;align-items:center;gap:6px">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
             stroke="var(--text-sub)" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span style="font-size:12px;color:var(--text-sub)">
            Capital tied up in unsold inventory
        </span>
    </div>
</div>
```

**Change C — Add a new "Catalog Value" card** as the 6th card,
immediately after the Stagnant Stock card:

```blade
{{-- Card 6: Catalog Value (owner-only financial intelligence) --}}
<div style="background:var(--surface);border:1px solid var(--border);
            border-radius:var(--r);overflow:hidden;display:flex;flex-direction:column">

    <div style="padding:16px 20px 12px;display:flex;align-items:flex-start;
                justify-content:space-between;gap:12px;flex:1">
        <div style="display:flex;flex-direction:column;gap:6px;flex:1">

            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:32px;height:32px;border-radius:8px;
                            background:var(--accent-dim);display:flex;
                            align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                         stroke="var(--accent)" stroke-width="2.2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <span style="font-size:11px;font-weight:700;color:var(--text-sub);
                             text-transform:uppercase;letter-spacing:.8px">Catalog Value</span>
            </div>

            {{-- Retail value headline --}}
            <div style="display:flex;align-items:baseline;gap:8px;margin-top:4px">
                <span style="font-size:26px;font-weight:800;color:var(--text);line-height:1">
                    {{ number_format($catalogRetail) }}
                </span>
                <span style="font-size:12px;font-weight:500;color:var(--text-sub)">RWF retail</span>
            </div>

            {{-- Cost value + markup --}}
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <span style="font-size:13px;color:var(--text-sub)">
                    Cost: <strong style="color:var(--text)">{{ number_format($catalogCost) }} RWF</strong>
                </span>
                @if($catalogMarkup > 0)
                <span style="font-size:12px;font-weight:700;color:var(--success);
                             background:var(--success-glow);padding:1px 7px;border-radius:12px">
                    +{{ $catalogMarkup }}% markup
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Sub-row --}}
    <div style="border-top:1px solid var(--border);background:var(--surface2);
                padding:8px 16px;display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:12px;color:var(--text-sub)">Potential gross profit</span>
        <span style="font-size:13px;font-weight:800;color:var(--accent)">
            {{ number_format($catalogRetail - $catalogCost) }} RWF
        </span>
    </div>
</div>
```

---

## Task 2 — Per-Product Margin % Column in Product List

### PHP — `app/Livewire/Owner/Products/ProductList.php`

Read the file. Find the `$salesStats` enrichment loop that iterates over
a DB query result. Inside that loop, extend each row to also compute
`margin_pct`. The loop currently stores `revenue`, `units_sold`,
`has_override`, `last_sold_at`.

After that loop, add a second enrichment that joins `purchase_price`
from the product objects already paginated:

```php
// After the existing $salesStats loop, still inside the `if ($isOwner && !empty($productIds))` block:
// Build a lookup of purchase_price by product_id from the paginated items
$purchasePrices = collect($products->items())
    ->pluck('purchase_price', 'id');

foreach ($salesStats as $productId => &$row) {
    $purchasePrice = $purchasePrices[$productId] ?? 0;
    $revenue       = (float) ($row->revenue       ?? 0);
    $unitsSold     = (int)   ($row->units_sold     ?? 0);
    $costTotal     = $purchasePrice * $unitsSold;
    $row->margin_pct = $revenue > 0
        ? round((($revenue - $costTotal) / $revenue) * 100, 1)
        : null;
}
unset($row); // break reference
```

No changes needed to the `return view(...)` call — `$salesStats` is already passed.

### Blade — `resources/views/livewire/products/product-list.blade.php`

Read the file carefully. Find the owner-only `Revenue` column header `<th>` and
the corresponding `<td>` in the row loop.

**Add a new `<th>` immediately after the Revenue header, owner-only:**

```blade
@if($isOwner)
<th style="... (copy exact style from adjacent Revenue <th>) ...">
    Margin
    {{-- tiny period label chip --}}
    <span style="font-size:10px;font-weight:600;color:var(--text-dim);
                 background:var(--surface3);padding:1px 5px;
                 border-radius:8px;margin-left:4px">{{ $periodLabel }}</span>
</th>
@endif
```

**Add the corresponding `<td>` immediately after the Revenue `<td>`, owner-only:**

```blade
@if($isOwner)
@php
    $mp = $salesStats[$product->id]->margin_pct ?? null;
@endphp
<td style="... (copy exact style from adjacent Revenue <td>) ...">
    @if($mp !== null)
        @php
            $mpColor  = $mp >= 30 ? 'var(--success)' : ($mp >= 10 ? 'var(--amber)' : 'var(--danger)');
            $mpGlow   = $mp >= 30 ? 'var(--success-glow)' : ($mp >= 10 ? 'var(--amber-dim)' : 'var(--danger-glow)');
        @endphp
        <span style="display:inline-block;font-size:12px;font-weight:700;
                     color:{{ $mpColor }};background:{{ $mpGlow }};
                     padding:2px 8px;border-radius:12px;white-space:nowrap">
            {{ $mp }}%
        </span>
    @else
        <span style="color:var(--text-dim);font-size:13px">—</span>
    @endif
</td>
@endif
```

---

## Task 3 — Product Detail Drawer: 4 New Sections

### PHP — `app/Livewire/Owner/Products/ProductDetail.php`

Read the file fully. The `render()` method currently computes:
`$chartData`, `$stockByLoc`, `$overrideLog`, `$recentMoves`.

Add 4 new computations inside `render()`, after the existing ones.
Add these imports at the top of the file if not already present:

```php
use App\Models\DamagedGood;
use App\Models\ReturnItem;
```

Then, inside `render()` after the existing `$recentMoves` block:

```php
// ── Days of stock remaining ───────────────────────────────────────────────
$avgDailySales = count($chartData['units']) > 0
    ? array_sum($chartData['units']) / 30   // 30-day array always has 30 entries
    : 0;

$totalItemsAllLoc = collect($stockByLoc)->sum('items');

$daysRemaining = ($avgDailySales > 0)
    ? (int) round($totalItemsAllLoc / $avgDailySales)
    : null;   // null = no sales velocity to base estimate on

// ── Return rate ───────────────────────────────────────────────────────────
$totalSold = DB::table('sale_items')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->where('sale_items.product_id', $this->productId)
    ->whereNull('sales.voided_at')
    ->whereNull('sales.deleted_at')
    ->sum('sale_items.quantity_sold');

$totalReturned = DB::table('return_items')
    ->where('product_id', $this->productId)
    ->sum('quantity_returned');

$returnRate = ($totalSold > 0)
    ? round(($totalReturned / $totalSold) * 100, 1)
    : 0;

// ── Pending damaged units ─────────────────────────────────────────────────
$pendingDamagedQty = DamagedGood::where('product_id', $this->productId)
    ->where('disposition', 'pending')
    ->whereNull('deleted_at')
    ->sum('quantity_damaged');

// ── Revenue split by shop (last 30 days) ─────────────────────────────────
$revByShop = DB::table('sale_items')
    ->join('sales',  'sale_items.sale_id', '=', 'sales.id')
    ->join('shops',  'sales.shop_id',      '=', 'shops.id')
    ->where('sale_items.product_id', $this->productId)
    ->whereNull('sales.voided_at')
    ->whereNull('sales.deleted_at')
    ->where('sales.sale_date', '>=', now()->subDays(29)->startOfDay())
    ->groupBy('shops.id', 'shops.name')
    ->selectRaw('shops.name as shop_name,
                 SUM(sale_items.quantity_sold) as units,
                 SUM(sale_items.line_total)    as revenue')
    ->orderByDesc('revenue')
    ->get()
    ->map(fn ($r) => [
        'shop'    => $r->shop_name,
        'units'   => (int)   $r->units,
        'revenue' => (float) $r->revenue,
    ])
    ->toArray();

$totalRevByShop = collect($revByShop)->sum('revenue');
```

Update the `return view(...)` to include the new variables:

```php
return view('livewire.owner.products.product-detail', [
    'product'           => $product,
    'chartData'         => $chartData,
    'stockByLoc'        => $stockByLoc,
    'overrideLog'       => $overrideLog,
    'recentMoves'       => $recentMoves,
    // new:
    'daysRemaining'     => $daysRemaining,
    'avgDailySales'     => round($avgDailySales, 1),
    'returnRate'        => $returnRate,
    'totalSold'         => $totalSold,
    'totalReturned'     => $totalReturned,
    'pendingDamagedQty' => $pendingDamagedQty,
    'revByShop'         => $revByShop,
    'totalRevByShop'    => $totalRevByShop,
]);
```

Also update the early-return (when `$this->productId` is null) to include the new keys
with safe defaults:

```php
return view('livewire.owner.products.product-detail', [
    'product'           => null,
    'chartData'         => null,
    'stockByLoc'        => [],
    'overrideLog'       => [],
    'recentMoves'       => [],
    'daysRemaining'     => null,
    'avgDailySales'     => 0,
    'returnRate'        => 0,
    'totalSold'         => 0,
    'totalReturned'     => 0,
    'pendingDamagedQty' => 0,
    'revByShop'         => [],
    'totalRevByShop'    => 0,
]);
```

### Blade — `resources/views/livewire/owner/products/product-detail.blade.php`

Read the file fully. Identify:
- The section header pattern (uppercase small label + content below)
- Where the existing sections are ordered
- The outer `@if($product)` guard block

Add 4 new sections inside the `@if($product)` block.

#### Section A — Days of Stock (add near the TOP of the drawer, visually prominent)

Find the section where the product name/heading is displayed. After that heading
block but before the 30-day chart, insert this **stock velocity banner**:

```blade
{{-- ── Stock velocity banner ──────────────────────────────────────────── --}}
@php
    $daysColor = $daysRemaining === null ? 'var(--text-sub)'
               : ($daysRemaining <= 7   ? 'var(--danger)'
               : ($daysRemaining <= 21  ? 'var(--amber)'
               :                          'var(--success)'));
    $daysGlow  = $daysRemaining === null ? 'var(--surface3)'
               : ($daysRemaining <= 7   ? 'var(--danger-glow)'
               : ($daysRemaining <= 21  ? 'var(--amber-dim)'
               :                          'var(--success-glow)'));
@endphp
<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;
            padding:12px 16px;background:var(--surface2);border-radius:var(--rsm);
            border:1px solid var(--border);margin-bottom:16px">

    {{-- Days chip --}}
    <div style="display:flex;align-items:center;gap:6px;
                background:{{ $daysGlow }};color:{{ $daysColor }};
                padding:6px 14px;border-radius:20px;flex-shrink:0">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
        </svg>
        @if($daysRemaining !== null)
            <span style="font-size:20px;font-weight:800;line-height:1">{{ $daysRemaining }}</span>
            <span style="font-size:12px;font-weight:600">days left</span>
        @else
            <span style="font-size:13px;font-weight:600">No velocity data</span>
        @endif
    </div>

    {{-- Supporting stats --}}
    <div style="display:flex;flex-direction:column;gap:2px">
        <span style="font-size:13px;font-weight:600;color:var(--text)">
            {{ number_format($totalItemsAllLoc ?? collect($stockByLoc)->sum('items')) }} items in stock
        </span>
        <span style="font-size:12px;color:var(--text-sub)">
            @if($avgDailySales > 0)
                Selling ~{{ $avgDailySales }} units/day (30-day avg)
            @else
                No recent sales velocity
            @endif
        </span>
    </div>

    {{-- Pending damaged warning (show inline here if nonzero) --}}
    @if($pendingDamagedQty > 0)
    <div style="margin-left:auto;display:flex;align-items:center;gap:5px;
                background:var(--danger-glow);color:var(--danger);
                padding:5px 12px;border-radius:20px;flex-shrink:0">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.2">
            <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span style="font-size:12px;font-weight:700">
            {{ $pendingDamagedQty }} units damaged — no decision
        </span>
    </div>
    @endif
</div>
```

#### Section B — Revenue by Shop

Find the existing "Stock by Location" section. After that section, insert:

```blade
{{-- ── Revenue by Shop (last 30 days) ─────────────────────────────────── --}}
@if(count($revByShop) > 0)
<div style="margin-top:20px">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.8px;color:var(--text-sub);margin-bottom:10px">
        Sales by Shop — Last 30 Days
    </div>

    <div style="display:flex;flex-direction:column;gap:6px">
        @foreach($revByShop as $row)
        @php
            $pct = $totalRevByShop > 0
                ? round(($row['revenue'] / $totalRevByShop) * 100)
                : 0;
        @endphp
        <div style="background:var(--surface2);border:1px solid var(--border);
                    border-radius:var(--rsm);padding:10px 14px">
            <div style="display:flex;align-items:center;justify-content:space-between;
                        gap:8px;margin-bottom:6px">
                <span style="font-size:13px;font-weight:600;color:var(--text)">
                    {{ $row['shop'] }}
                </span>
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:12px;color:var(--text-sub)">
                        {{ number_format($row['units']) }} units
                    </span>
                    <span style="font-size:13px;font-weight:700;color:var(--text)">
                        {{ number_format($row['revenue']) }} RWF
                    </span>
                    <span style="font-size:12px;font-weight:700;color:var(--accent);
                                 background:var(--accent-dim);padding:1px 7px;
                                 border-radius:12px;min-width:36px;text-align:center">
                        {{ $pct }}%
                    </span>
                </div>
            </div>
            {{-- Progress bar --}}
            <div style="height:4px;background:var(--surface3);border-radius:4px;overflow:hidden">
                <div style="height:100%;width:{{ $pct }}%;
                            background:var(--accent);border-radius:4px;
                            transition:width .4s ease"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
```

#### Section C — Return Rate

Find the existing price override log section. After that section, insert:

```blade
{{-- ── Return Rate ────────────────────────────────────────────────────── --}}
<div style="margin-top:20px">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.8px;color:var(--text-sub);margin-bottom:10px">
        Return Rate (Lifetime)
    </div>

    @php
        $rrColor = $returnRate === 0  ? 'var(--success)'
                 : ($returnRate <= 5  ? 'var(--success)'
                 : ($returnRate <= 15 ? 'var(--amber)'
                 :                      'var(--danger)'));
        $rrGlow  = $returnRate === 0  ? 'var(--success-glow)'
                 : ($returnRate <= 5  ? 'var(--success-glow)'
                 : ($returnRate <= 15 ? 'var(--amber-dim)'
                 :                      'var(--danger-glow)'));
    @endphp

    <div style="background:var(--surface2);border:1px solid var(--border);
                border-radius:var(--rsm);padding:14px 16px;
                display:flex;align-items:center;gap:16px;flex-wrap:wrap">

        {{-- Rate chip --}}
        <div style="background:{{ $rrGlow }};color:{{ $rrColor }};
                    padding:8px 18px;border-radius:20px;flex-shrink:0;
                    display:flex;align-items:baseline;gap:4px">
            <span style="font-size:26px;font-weight:800;line-height:1">{{ $returnRate }}</span>
            <span style="font-size:14px;font-weight:600">%</span>
        </div>

        {{-- Supporting numbers --}}
        <div style="display:flex;flex-direction:column;gap:3px">
            <span style="font-size:13px;font-weight:600;color:var(--text)">
                {{ number_format($totalReturned) }} returned
                of {{ number_format($totalSold) }} sold
            </span>
            <span style="font-size:12px;color:var(--text-sub)">
                @if($returnRate <= 5)
                    Healthy return rate
                @elseif($returnRate <= 15)
                    Moderate — monitor closely
                @else
                    High return rate — investigate quality
                @endif
            </span>
        </div>
    </div>
</div>
```

#### Section D — Pending Damaged Goods (detailed section)

This section only renders if `$pendingDamagedQty > 0`.
Add it at the **very bottom** of the drawer content, before any closing
`</div>` tags:

```blade
{{-- ── Pending Damaged Goods ──────────────────────────────────────────── --}}
@if($pendingDamagedQty > 0)
<div style="margin-top:20px">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.8px;color:var(--text-sub);margin-bottom:10px">
        Damaged Stock — Awaiting Disposition
    </div>

    <div style="background:var(--danger-glow);border:1px solid var(--danger);
                border-radius:var(--rsm);padding:14px 16px;
                display:flex;align-items:center;gap:14px">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
             stroke="var(--danger)" stroke-width="2">
            <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <div style="flex:1">
            <div style="font-size:15px;font-weight:700;color:var(--danger)">
                {{ number_format($pendingDamagedQty) }} units damaged, no decision yet
            </div>
            <div style="font-size:12px;color:var(--text-sub);margin-top:3px">
                Go to Damaged Goods to assign a disposition (write-off, discount sale, return to supplier)
            </div>
        </div>
        <a href="{{ route('owner.damaged-goods.index') }}"
           style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;
                  font-size:12px;font-weight:700;color:var(--danger);
                  border:1.5px solid var(--danger);border-radius:var(--rsm);
                  padding:6px 12px;text-decoration:none;
                  transition:background .15s"
           onmouseover="this.style.background='var(--surface)'"
           onmouseout="this.style.background='transparent'">
            Review
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</div>
@endif
```

---

## Verification Steps

Run these after all changes, fix any errors before finishing:

```bash
php artisan view:clear
php artisan cache:clear
php artisan view:cache 2>&1 | grep -i "error\|exception" | head -30
```

Then open `php artisan tinker` and verify the new queries run without error:

```php
// Verify stagnant count doesn't throw
\App\Livewire\Owner\Products\ProductKpiRow::class;

// Verify DamagedGood import
\App\Models\DamagedGood::where('disposition', 'pending')->count();

// Verify return_items table name matches your migration
\DB::table('return_items')->limit(1)->get();
// If this throws "table not found", check your migration and use the correct table name
```

**Important:** if `return_items` is not the correct table name in your schema,
find the correct name by running:
```bash
php artisan tinker --execute="\DB::select('SELECT tablename FROM pg_tables WHERE schemaname = \'public\' ORDER BY tablename') |> collect()->pluck('tablename')"
```
Then use the correct table name in the `ProductDetail.php` query.

---

## Summary of Changes

| # | What | Files changed |
|---|------|---------------|
| 1 | Stagnant Stock KPI card (new) | `ProductKpiRow.php` + blade |
| 2 | Catalog cost vs retail KPI card (new) | `ProductKpiRow.php` + blade |
| 3 | Portfolio avg margin on Best Margin card (extend) | `ProductKpiRow.php` + blade |
| 4 | Margin % column in product list (owner only) | `ProductList.php` + blade |
| 5 | Days of stock remaining — velocity banner | `ProductDetail.php` + blade |
| 6 | Revenue split by shop | `ProductDetail.php` + blade |
| 7 | Return rate | `ProductDetail.php` + blade |
| 8 | Pending damaged goods alert | `ProductDetail.php` + blade |
