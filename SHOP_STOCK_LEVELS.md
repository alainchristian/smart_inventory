# Shop Stock Levels — Box-Driven Rewrite
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read SHOP_STOCK_LEVELS.md and follow every step in order."

---

## Read these files first

```bash
cat app/Livewire/Shop/StockLevels.php
cat resources/views/livewire/shop/stock-levels.blade.php
```

---

## The problem

`StockLevels.php` queries **all active products** then calls
`getCurrentStock()` per product in a loop. This means:
- Products never transferred to this shop appear with 0 stock
- Out-of-stock products never disappear — they stay as grey rows forever
- N+1 queries (one per product)

The correct model:
- Stock = physical boxes currently at this shop (`location_type = 'shop'`)
- Only show products that **have at least one box** at this shop right now
- When a product's last box is sold/emptied, it disappears from the list
- A separate "Previously stocked" section shows products that used to be
  here (have transfer history) so the manager can quickly request more

---

## STEP 1 — Rewrite StockLevels.php

**File:** `app/Livewire/Shop/StockLevels.php`

Replace the entire file content:

```php
<?php

namespace App\Livewire\Shop;

use App\Enums\LocationType;
use App\Models\Box;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StockLevels extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $statusFilter  = 'in_stock'; // in_stock | low | previously_stocked
    public int    $shopId;

    protected $queryString = [
        'search'       => ['except' => ''],
        'statusFilter' => ['except' => 'in_stock'],
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $this->shopId = $user->isOwner()
            ? (session('selected_shop_id') ?? $user->location_id)
            : $user->location_id;
    }

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function render()
    {
        // ── 1. Products with boxes currently at this shop ─────────────────
        $currentStockQuery = DB::table('boxes')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('boxes.location_type', LocationType::SHOP->value)
            ->where('boxes.location_id', $this->shopId)
            ->whereIn('boxes.status', ['full', 'partial'])
            ->where('boxes.items_remaining', '>', 0)
            ->whereNull('products.deleted_at')
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q2) use ($term) {
                    $q2->where('products.name',    'ilike', $term)
                       ->orWhere('products.sku',    'ilike', $term)
                       ->orWhere('products.barcode', 'ilike', $term);
                });
            })
            ->select(
                'products.id as product_id',
                'products.name',
                'products.sku',
                'products.barcode',
                'products.selling_price',
                'products.low_stock_threshold',
                'products.items_per_box',
                'categories.name as category_name',
                DB::raw("SUM(CASE WHEN boxes.status = 'full'    THEN 1 ELSE 0 END) as full_boxes"),
                DB::raw("SUM(CASE WHEN boxes.status = 'partial' THEN 1 ELSE 0 END) as partial_boxes"),
                DB::raw('COUNT(boxes.id) as total_boxes'),
                DB::raw('SUM(boxes.items_remaining) as total_items')
            )
            ->groupBy(
                'products.id', 'products.name', 'products.sku',
                'products.barcode', 'products.selling_price',
                'products.low_stock_threshold', 'products.items_per_box',
                'categories.name'
            );

        // Apply low stock filter
        if ($this->statusFilter === 'low') {
            $currentStockQuery->havingRaw(
                'SUM(boxes.items_remaining) <= products.low_stock_threshold'
            );
        }

        // ── 2. Summary KPIs (always across full shop, ignoring search) ────
        $kpis = DB::table('boxes')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->where('boxes.location_type', LocationType::SHOP->value)
            ->where('boxes.location_id', $this->shopId)
            ->whereIn('boxes.status', ['full', 'partial'])
            ->where('boxes.items_remaining', '>', 0)
            ->whereNull('products.deleted_at')
            ->selectRaw('
                COUNT(DISTINCT boxes.product_id) as product_count,
                COUNT(boxes.id) as total_boxes,
                SUM(boxes.items_remaining) as total_items,
                COUNT(CASE WHEN boxes.items_remaining <= products.low_stock_threshold
                           THEN 1 END) as low_stock_count
            ')
            ->first();

        // ── 3. Previously stocked products (transferred but now out) ──────
        $previouslyStocked = collect();
        if ($this->statusFilter === 'previously_stocked') {
            // Products that have been received at this shop via transfers
            // but currently have zero boxes here
            $currentProductIds = DB::table('boxes')
                ->where('location_type', LocationType::SHOP->value)
                ->where('location_id', $this->shopId)
                ->whereIn('status', ['full', 'partial'])
                ->where('items_remaining', '>', 0)
                ->pluck('product_id');

            $previouslyStocked = DB::table('transfer_boxes')
                ->join('transfers', 'transfer_boxes.transfer_id', '=', 'transfers.id')
                ->join('boxes', 'transfer_boxes.box_id', '=', 'boxes.id')
                ->join('products', 'boxes.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->where('transfers.to_shop_id', $this->shopId)
                ->where('transfers.status', 'received')
                ->whereNotIn('boxes.product_id', $currentProductIds)
                ->whereNull('products.deleted_at')
                ->when($this->search, function ($q) {
                    $term = '%' . $this->search . '%';
                    $q->where(function ($q2) use ($term) {
                        $q2->where('products.name',    'ilike', $term)
                           ->orWhere('products.sku',    'ilike', $term);
                    });
                })
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.sku',
                    'products.selling_price',
                    'products.items_per_box',
                    'categories.name as category_name',
                    DB::raw('MAX(transfers.received_at) as last_received_at')
                )
                ->groupBy(
                    'products.id', 'products.name', 'products.sku',
                    'products.selling_price', 'products.items_per_box',
                    'categories.name'
                )
                ->orderByDesc('last_received_at')
                ->limit(50)
                ->get();
        }

        // Paginate current stock
        $stockData = $currentStockQuery
            ->orderByRaw('SUM(boxes.items_remaining) / products.low_stock_threshold ASC')
            ->paginate(24);

        return view('livewire.shop.stock-levels', [
            'stockData'         => $stockData,
            'kpis'              => $kpis,
            'previouslyStocked' => $previouslyStocked,
        ]);
    }
}
```

---

## STEP 2 — Rewrite the blade view

**File:** `resources/views/livewire/shop/stock-levels.blade.php`

Replace the entire file content:

```blade
<div style="font-family:var(--font);max-width:1400px">

<style>
.sl-header { margin-bottom:24px }
.sl-title   { font-size:20px;font-weight:800;color:var(--text);letter-spacing:-.3px }
.sl-sub     { font-size:13px;color:var(--text-dim);margin-top:3px;font-family:var(--mono) }

/* KPI bar */
.sl-kpis {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
    margin-bottom:20px;
}
.sl-kpi {
    background:var(--surface);border:1px solid var(--border);
    border-radius:var(--r);padding:14px 18px;
}
.sl-kpi-label {
    font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
    color:var(--text-dim);margin-bottom:6px;
}
.sl-kpi-value {
    font-size:24px;font-weight:800;font-family:var(--mono);
    letter-spacing:-1px;color:var(--text);
}
.sl-kpi-sub { font-size:11px;color:var(--text-dim);margin-top:3px;font-family:var(--mono) }

/* Controls */
.sl-controls {
    display:flex;gap:10px;align-items:center;
    flex-wrap:wrap;margin-bottom:20px;
}
.sl-search-wrap {
    flex:1;min-width:200px;position:relative;
}
.sl-search-icon {
    position:absolute;left:11px;top:50%;transform:translateY(-50%);
    width:14px;height:14px;color:var(--text-dim);pointer-events:none;
}
.sl-search {
    width:100%;padding:9px 11px 9px 33px;border:1.5px solid var(--border);
    border-radius:10px;font-size:13px;background:var(--surface);
    color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);
    transition:border-color var(--tr);
}
.sl-search:focus { border-color:var(--accent) }

/* Tab filter */
.sl-tabs {
    display:flex;gap:4px;background:var(--surface2);
    border-radius:10px;padding:3px;border:1px solid var(--border);
}
.sl-tab {
    padding:6px 14px;border-radius:8px;border:none;cursor:pointer;
    font-size:12px;font-weight:600;font-family:var(--font);
    background:transparent;color:var(--text-sub);transition:all var(--tr);
    white-space:nowrap;
}
.sl-tab.active {
    background:var(--surface);color:var(--text);
    box-shadow:0 1px 4px rgba(26,31,54,.10);
}

/* Product grid */
.sl-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(240px,1fr));
    gap:12px;
}
.sl-card {
    background:var(--surface);border:1px solid var(--border);
    border-radius:var(--r);padding:16px;
    transition:box-shadow var(--tr),border-color var(--tr),transform var(--tr);
    position:relative;overflow:hidden;
}
.sl-card:hover {
    box-shadow:0 6px 20px rgba(26,31,54,.09);
    border-color:var(--border-hi);
    transform:translateY(-1px);
}
.sl-card-cat {
    font-size:10px;font-weight:700;letter-spacing:.5px;
    text-transform:uppercase;color:var(--text-dim);margin-bottom:6px;
}
.sl-card-name {
    font-size:14px;font-weight:700;color:var(--text);
    margin-bottom:2px;line-height:1.3;
}
.sl-card-sku {
    font-size:11px;color:var(--text-dim);font-family:var(--mono);
    margin-bottom:14px;
}
.sl-card-stock {
    display:flex;align-items:flex-end;justify-content:space-between;
    margin-bottom:10px;
}
.sl-card-boxes {
    font-size:28px;font-weight:800;font-family:var(--mono);
    letter-spacing:-1.5px;line-height:1;
}
.sl-card-boxes-label {
    font-size:10px;font-weight:700;color:var(--text-dim);
    text-transform:uppercase;letter-spacing:.5px;margin-top:3px;
}
.sl-card-items {
    text-align:right;
}
.sl-card-items-val {
    font-size:16px;font-weight:700;font-family:var(--mono);color:var(--text-sub);
}
.sl-card-items-label {
    font-size:10px;color:var(--text-dim);margin-top:1px;
}

/* Progress bar */
.sl-bar-wrap {
    height:4px;background:var(--surface2);border-radius:4px;
    overflow:hidden;margin-bottom:10px;
}
.sl-bar-fill { height:100%;border-radius:4px;transition:width .3s }

/* Box detail pills */
.sl-pills { display:flex;gap:6px;flex-wrap:wrap }
.sl-pill {
    font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;
    display:flex;align-items:center;gap:4px;
}

/* Low stock accent line */
.sl-card.low::before {
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:var(--amber);border-radius:var(--r) var(--r) 0 0;
}
.sl-card.critical::before { background:var(--red); }

/* Previously stocked card */
.sl-prev-card {
    background:var(--surface);border:1px dashed var(--border);
    border-radius:var(--r);padding:14px 16px;
    display:flex;align-items:center;justify-content:space-between;
    gap:12px;transition:background var(--tr);
}
.sl-prev-card:hover { background:var(--surface2) }
.sl-prev-name { font-size:13px;font-weight:600;color:var(--text) }
.sl-prev-meta { font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px }

/* Request button */
.sl-request-btn {
    padding:6px 14px;border-radius:8px;border:1.5px solid var(--accent);
    background:transparent;color:var(--accent);font-size:11px;font-weight:700;
    cursor:pointer;white-space:nowrap;font-family:var(--font);
    transition:all var(--tr);
}
.sl-request-btn:hover { background:var(--accent);color:#fff }

/* Empty state */
.sl-empty {
    grid-column:1/-1;text-align:center;padding:60px 20px;
    color:var(--text-dim);
}
.sl-empty-icon { font-size:40px;margin-bottom:12px }
.sl-empty-title { font-size:16px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.sl-empty-sub { font-size:13px;color:var(--text-dim) }

/* Mobile */
@media(max-width:640px) {
    .sl-kpis { grid-template-columns:1fr 1fr;gap:8px }
    .sl-kpi { padding:10px 12px }
    .sl-kpi-value { font-size:20px }
    .sl-controls { flex-direction:column;align-items:stretch }
    .sl-tabs { overflow-x:auto }
    .sl-grid { grid-template-columns:1fr 1fr;gap:8px }
    .sl-card { padding:12px }
    .sl-card-boxes { font-size:22px }
    .sl-card-name { font-size:13px }
}
@media(max-width:400px) {
    .sl-grid { grid-template-columns:1fr }
}
</style>

{{-- Header --}}
<div class="sl-header">
    <div class="sl-title">
        Stock at {{ auth()->user()->location?->name ?? 'This Shop' }}
    </div>
    <div class="sl-sub">
        Live box inventory · updates as sales and transfers happen
    </div>
</div>

{{-- KPI bar --}}
<div class="sl-kpis">
    <div class="sl-kpi">
        <div class="sl-kpi-label">Products</div>
        <div class="sl-kpi-value" style="color:var(--accent)">
            {{ number_format($kpis->product_count ?? 0) }}
        </div>
        <div class="sl-kpi-sub">in stock now</div>
    </div>
    <div class="sl-kpi">
        <div class="sl-kpi-label">Boxes</div>
        <div class="sl-kpi-value">
            {{ number_format($kpis->total_boxes ?? 0) }}
        </div>
        <div class="sl-kpi-sub">physical boxes</div>
    </div>
    <div class="sl-kpi">
        <div class="sl-kpi-label">Items</div>
        <div class="sl-kpi-value" style="color:var(--green)">
            {{ number_format($kpis->total_items ?? 0) }}
        </div>
        <div class="sl-kpi-sub">sellable units</div>
    </div>
    <div class="sl-kpi">
        <div class="sl-kpi-label">Low Stock</div>
        <div class="sl-kpi-value"
             style="color:{{ ($kpis->low_stock_count ?? 0) > 0 ? 'var(--amber)' : 'var(--text)' }}">
            {{ number_format($kpis->low_stock_count ?? 0) }}
        </div>
        <div class="sl-kpi-sub">need replenishment</div>
    </div>
</div>

{{-- Controls --}}
<div class="sl-controls">
    {{-- Search --}}
    <div class="sl-search-wrap">
        <svg class="sl-search-icon" fill="none" stroke="currentColor"
             stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input wire:model.live.debounce.250ms="search"
               class="sl-search" type="text"
               placeholder="Search product, SKU, barcode…">
    </div>

    {{-- Tab filter --}}
    <div class="sl-tabs">
        <button wire:click="$set('statusFilter','in_stock')"
                class="sl-tab {{ $statusFilter === 'in_stock' ? 'active' : '' }}">
            In Stock
        </button>
        <button wire:click="$set('statusFilter','low')"
                class="sl-tab {{ $statusFilter === 'low' ? 'active' : '' }}">
            @if(($kpis->low_stock_count ?? 0) > 0)
                ⚠ Low Stock ({{ $kpis->low_stock_count }})
            @else
                Low Stock
            @endif
        </button>
        <button wire:click="$set('statusFilter','previously_stocked')"
                class="sl-tab {{ $statusFilter === 'previously_stocked' ? 'active' : '' }}">
            Previously Stocked
        </button>
    </div>
</div>

{{-- ── In Stock / Low Stock grid ──────────────────────────────────── --}}
@if($statusFilter !== 'previously_stocked')

<div class="sl-grid">
    @forelse($stockData as $row)
    @php
        $pct      = $row->low_stock_threshold > 0
            ? min(100, round(($row->total_items / $row->low_stock_threshold) * 100))
            : 100;
        $isCrit   = $row->total_items <= max(1, $row->low_stock_threshold * 0.25);
        $isLow    = $row->total_items <= $row->low_stock_threshold;
        $barColor = $isCrit ? 'var(--red)' : ($isLow ? 'var(--amber)' : 'var(--green)');
        $boxColor = $isCrit ? 'var(--red)' : ($isLow ? 'var(--amber)' : 'var(--text)');
        $cardClass = $isCrit ? 'critical' : ($isLow ? 'low' : '');
    @endphp
    <div class="sl-card {{ $cardClass }}">

        {{-- Category --}}
        @if($row->category_name)
        <div class="sl-card-cat">{{ $row->category_name }}</div>
        @endif

        {{-- Name + SKU --}}
        <div class="sl-card-name">{{ $row->name }}</div>
        <div class="sl-card-sku">{{ $row->sku }}</div>

        {{-- Stock numbers --}}
        <div class="sl-card-stock">
            <div>
                <div class="sl-card-boxes" style="color:{{ $boxColor }}">
                    {{ $row->total_boxes }}
                </div>
                <div class="sl-card-boxes-label">
                    box{{ $row->total_boxes === 1 ? '' : 'es' }}
                </div>
            </div>
            <div class="sl-card-items">
                <div class="sl-card-items-val">
                    {{ number_format($row->total_items) }}
                </div>
                <div class="sl-card-items-label">items</div>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="sl-bar-wrap">
            <div class="sl-bar-fill"
                 style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
        </div>

        {{-- Box detail pills --}}
        <div class="sl-pills">
            @if($row->full_boxes > 0)
            <span class="sl-pill"
                  style="background:var(--green-dim);color:var(--green)">
                <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                </svg>
                {{ $row->full_boxes }} full
            </span>
            @endif
            @if($row->partial_boxes > 0)
            <span class="sl-pill"
                  style="background:var(--amber-dim);color:var(--amber)">
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                </svg>
                {{ $row->partial_boxes }} partial
            </span>
            @endif
            @if($isLow)
            <span class="sl-pill"
                  style="background:{{ $isCrit ? 'var(--red-dim)' : 'var(--amber-dim)' }};
                         color:{{ $isCrit ? 'var(--red)' : 'var(--amber)' }}">
                {{ $isCrit ? '⚠ Critical' : '⚠ Low' }}
            </span>
            @endif
        </div>

    </div>
    @empty
    <div class="sl-empty">
        <div class="sl-empty-icon">📦</div>
        <div class="sl-empty-title">
            @if($statusFilter === 'low')
                No low stock products
            @elseif($search)
                No products match "{{ $search }}"
            @else
                No stock at this shop
            @endif
        </div>
        <div class="sl-empty-sub">
            @if($statusFilter === 'low')
                All products are well stocked — great job!
            @elseif(!$search)
                Stock arrives via transfers from the warehouse.
                Switch to "Previously Stocked" to request more.
            @endif
        </div>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($stockData->hasPages())
<div style="margin-top:20px">{{ $stockData->links() }}</div>
@endif

{{-- ── Previously stocked ─────────────────────────────────────────── --}}
@else

<div style="background:var(--surface);border:1px solid var(--border);
            border-radius:var(--r);overflow:hidden">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:13px;font-weight:700;color:var(--text)">
                Previously Stocked Products
            </div>
            <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                Products that were transferred here before but are now out of stock
            </div>
        </div>
        <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">
            {{ count($previouslyStocked) }} products
        </span>
    </div>

    @forelse($previouslyStocked as $p)
    <div class="sl-prev-card"
         style="{{ !$loop->last ? 'border-bottom:1px dashed var(--border)' : '' }}">
        <div style="min-width:0">
            <div class="sl-prev-name">{{ $p->name }}</div>
            <div class="sl-prev-meta">
                {{ $p->sku }}
                @if($p->last_received_at)
                    · Last received {{ \Carbon\Carbon::parse($p->last_received_at)->diffForHumans() }}
                @endif
            </div>
        </div>
        <a href="{{ route('shop.transfers.request') }}"
           class="sl-request-btn">
            Request Stock
        </a>
    </div>
    @empty
    <div class="sl-empty" style="grid-column:unset">
        <div class="sl-empty-icon">✅</div>
        <div class="sl-empty-title">No history yet</div>
        <div class="sl-empty-sub">
            Products will appear here once they have been
            transferred and fully sold out.
        </div>
    </div>
    @endforelse
</div>

@endif

</div>
```

---

## STEP 3 — Clear caches

```bash
php artisan view:clear && php artisan cache:clear
```

---

## Do NOT touch

- Any other Livewire components
- The warehouse StockLevels component
- Any migrations

---

## Verification

1. Open `/shop/inventory/stock` — only products with actual boxes at
   this shop appear. Products never transferred show nothing.
2. Sell out a product via POS → on next page load it disappears
3. Switch to "Previously Stocked" tab → that product now appears
   with a "Request Stock" link pointing to the transfer request page
4. Low Stock tab shows only products at or below their threshold
5. KPI bar shows correct counts matching the grid
6. Mobile: grid becomes 2-column, KPIs become 2-column
