# Shop Stock Levels — Table Layout (Replaces Card Layout)
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read SHOP_STOCK_TABLE.md and follow every step in order."

---

## Read first

```bash
cat app/Livewire/Shop/StockLevels.php
cat resources/views/livewire/shop/stock-levels.blade.php
```

The PHP component from SHOP_STOCK_LEVELS.md is already correct — do not
touch it. Only replace the blade view.

---

## STEP 1 — Replace the blade view only

**File:** `resources/views/livewire/shop/stock-levels.blade.php`

Replace the entire file content with:

```blade
<div style="font-family:var(--font)">
<style>
/* ── KPI bar ─────────────────────────────────────────────────── */
.ss-kpis {
    display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;
}
.ss-kpi {
    background:var(--surface);border:1px solid var(--border);
    border-radius:var(--r);padding:14px 18px;
}
.ss-kpi-label {
    font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
    color:var(--text-dim);margin-bottom:6px;
}
.ss-kpi-value {
    font-size:22px;font-weight:800;font-family:var(--mono);
    letter-spacing:-1px;color:var(--text);line-height:1;
}
.ss-kpi-sub {font-size:11px;color:var(--text-dim);margin-top:3px;font-family:var(--mono)}

/* ── Controls ────────────────────────────────────────────────── */
.ss-controls {
    display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px;
}
.ss-search-wrap { flex:1;min-width:200px;position:relative }
.ss-search-icon {
    position:absolute;left:11px;top:50%;transform:translateY(-50%);
    width:14px;height:14px;color:var(--text-dim);pointer-events:none;
}
.ss-search {
    width:100%;padding:8px 11px 8px 33px;border:1.5px solid var(--border);
    border-radius:10px;font-size:13px;background:var(--surface);
    color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);
    transition:border-color var(--tr);
}
.ss-search:focus { border-color:var(--accent) }

/* Tab pills */
.ss-tabs {
    display:flex;gap:4px;background:var(--surface2);
    border-radius:10px;padding:3px;border:1px solid var(--border);
}
.ss-tab {
    padding:6px 14px;border-radius:8px;border:none;cursor:pointer;
    font-size:12px;font-weight:600;font-family:var(--font);
    background:transparent;color:var(--text-sub);transition:all var(--tr);
    white-space:nowrap;
}
.ss-tab.active {
    background:var(--surface);color:var(--text);
    box-shadow:0 1px 4px rgba(26,31,54,.10);
}

/* ── Table ───────────────────────────────────────────────────── */
.ss-table-wrap {
    background:var(--surface);border:1px solid var(--border);
    border-radius:var(--r);overflow:hidden;
}
.ss-table {
    width:100%;border-collapse:collapse;font-size:13px;
}
.ss-table thead tr {
    background:var(--bg);border-bottom:1px solid var(--border);
}
.ss-table thead th {
    padding:10px 16px;text-align:left;font-size:10px;font-weight:700;
    letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);
    white-space:nowrap;
}
.ss-table thead th.right { text-align:right }
.ss-table thead th.center { text-align:center }

.ss-table tbody tr {
    border-bottom:1px solid var(--border);
    transition:background var(--tr);
}
.ss-table tbody tr:last-child { border-bottom:none }
.ss-table tbody tr:hover { background:var(--surface2) }

/* Status accent on left edge */
.ss-table tbody tr.low    td:first-child { border-left:3px solid var(--amber) }
.ss-table tbody tr.crit   td:first-child { border-left:3px solid var(--red)   }
.ss-table tbody tr.ok     td:first-child { border-left:3px solid transparent  }

.ss-table td {
    padding:11px 16px;color:var(--text);vertical-align:middle;
}
.ss-table td.right  { text-align:right }
.ss-table td.center { text-align:center }
.ss-table td.mono   {
    font-family:var(--mono);font-weight:700;font-size:13px;
}

/* Product name cell */
.ss-name  { font-size:13px;font-weight:600;color:var(--text);line-height:1.2 }
.ss-meta  { font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:1px }

/* Box pill split */
.ss-boxes-wrap { display:flex;align-items:center;gap:6px;justify-content:flex-end }
.ss-box-pill {
    font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;
    white-space:nowrap;
}

/* Stock bar inline */
.ss-bar-wrap { width:60px;height:5px;background:var(--surface2);border-radius:4px;overflow:hidden;display:inline-block;vertical-align:middle;margin-left:6px }
.ss-bar-fill { height:100%;border-radius:4px }

/* Status badge */
.ss-badge {
    display:inline-flex;align-items:center;gap:4px;
    font-size:10px;font-weight:700;padding:3px 8px;
    border-radius:20px;white-space:nowrap;
}

/* Previously stocked table */
.ss-prev-row td { color:var(--text-sub) }
.ss-prev-name { font-size:13px;font-weight:600;color:var(--text-sub) }

/* Request button */
.ss-req-btn {
    padding:5px 12px;border-radius:7px;border:1.5px solid var(--accent);
    background:transparent;color:var(--accent);font-size:11px;font-weight:700;
    cursor:pointer;white-space:nowrap;font-family:var(--font);
    transition:all var(--tr);
}
.ss-req-btn:hover { background:var(--accent);color:#fff }

/* Empty */
.ss-empty {
    padding:60px 20px;text-align:center;color:var(--text-dim);
}
.ss-empty-icon  { font-size:36px;margin-bottom:10px }
.ss-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:5px }
.ss-empty-sub   { font-size:12px;color:var(--text-dim) }

/* Pagination */
.ss-pagination { padding:12px 16px;border-top:1px solid var(--border) }

/* ── Mobile ──────────────────────────────────────────────────── */
@media(max-width:640px) {
    .ss-kpis { grid-template-columns:1fr 1fr;gap:8px }
    .ss-kpi  { padding:10px 12px }
    .ss-kpi-value { font-size:18px }
    .ss-controls { flex-direction:column;align-items:stretch }
    .ss-tabs { overflow-x:auto }

    /* On mobile: hide less critical columns */
    .ss-hide-mob { display:none !important }

    /* Compact table */
    .ss-table td,
    .ss-table th { padding:9px 10px }

    .ss-bar-wrap { width:40px }
}
</style>

{{-- ── Page header ─────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;
            gap:12px;margin-bottom:20px;flex-wrap:wrap">
    <div>
        <div style="font-size:20px;font-weight:800;color:var(--text);letter-spacing:-.3px">
            Stock at {{ auth()->user()->location?->name ?? 'This Shop' }}
        </div>
        <div style="font-size:12px;color:var(--text-dim);margin-top:3px;font-family:var(--mono)">
            Live box inventory · updates as sales and transfers happen
        </div>
    </div>
</div>

{{-- ── KPI bar ──────────────────────────────────────────────────────── --}}
<div class="ss-kpis">
    <div class="ss-kpi">
        <div class="ss-kpi-label">Products</div>
        <div class="ss-kpi-value" style="color:var(--accent)">
            {{ number_format($kpis->product_count ?? 0) }}
        </div>
        <div class="ss-kpi-sub">in stock now</div>
    </div>
    <div class="ss-kpi">
        <div class="ss-kpi-label">Total Boxes</div>
        <div class="ss-kpi-value">{{ number_format($kpis->total_boxes ?? 0) }}</div>
        <div class="ss-kpi-sub">at this shop</div>
    </div>
    <div class="ss-kpi">
        <div class="ss-kpi-label">Sellable Items</div>
        <div class="ss-kpi-value" style="color:var(--green)">
            {{ number_format($kpis->total_items ?? 0) }}
        </div>
        <div class="ss-kpi-sub">units ready to sell</div>
    </div>
    <div class="ss-kpi">
        <div class="ss-kpi-label">Low Stock</div>
        <div class="ss-kpi-value"
             style="color:{{ ($kpis->low_stock_count ?? 0) > 0 ? 'var(--amber)' : 'var(--green)' }}">
            {{ number_format($kpis->low_stock_count ?? 0) }}
        </div>
        <div class="ss-kpi-sub">need replenishment</div>
    </div>
</div>

{{-- ── Controls ─────────────────────────────────────────────────────── --}}
<div class="ss-controls">
    <div class="ss-search-wrap">
        <svg class="ss-search-icon" fill="none" stroke="currentColor"
             stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input wire:model.live.debounce.250ms="search"
               class="ss-search" type="text"
               placeholder="Search product, SKU, barcode…">
    </div>
    <div class="ss-tabs">
        <button wire:click="$set('statusFilter','in_stock')"
                class="ss-tab {{ $statusFilter==='in_stock' ? 'active' : '' }}">
            In Stock
        </button>
        <button wire:click="$set('statusFilter','low')"
                class="ss-tab {{ $statusFilter==='low' ? 'active' : '' }}">
            @if(($kpis->low_stock_count ?? 0) > 0)
                ⚠ Low ({{ $kpis->low_stock_count }})
            @else
                Low Stock
            @endif
        </button>
        <button wire:click="$set('statusFilter','previously_stocked')"
                class="ss-tab {{ $statusFilter==='previously_stocked' ? 'active' : '' }}">
            Previously Stocked
        </button>
    </div>
</div>

{{-- ── In Stock / Low Stock table ───────────────────────────────────── --}}
@if($statusFilter !== 'previously_stocked')

<div class="ss-table-wrap">
    <div style="overflow-x:auto">
    <table class="ss-table">
        <thead>
            <tr>
                <th style="width:36px;padding-right:0"></th>
                <th>Product</th>
                <th class="ss-hide-mob">Category</th>
                <th class="right">Boxes</th>
                <th class="right">Items</th>
                <th class="center ss-hide-mob">Stock Level</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stockData as $row)
            @php
                $threshold = max(1, $row->low_stock_threshold);
                $pct       = min(100, round(($row->total_items / $threshold) * 100));
                $isCrit    = $row->total_items <= max(1, $threshold * 0.25);
                $isLow     = $row->total_items <= $threshold;
                $rowClass  = $isCrit ? 'crit' : ($isLow ? 'low' : 'ok');
                $barColor  = $isCrit ? 'var(--red)' : ($isLow ? 'var(--amber)' : 'var(--green)');
            @endphp
            <tr class="{{ $rowClass }}">

                {{-- Status dot --}}
                <td style="padding-right:0;width:36px">
                    <div style="width:8px;height:8px;border-radius:50%;
                                background:{{ $barColor }};margin:0 auto"></div>
                </td>

                {{-- Product name + SKU --}}
                <td>
                    <div class="ss-name">{{ $row->name }}</div>
                    <div class="ss-meta">{{ $row->sku }}</div>
                </td>

                {{-- Category --}}
                <td class="ss-hide-mob" style="color:var(--text-dim);font-size:12px">
                    {{ $row->category_name ?? '—' }}
                </td>

                {{-- Boxes --}}
                <td class="right">
                    <div class="ss-boxes-wrap">
                        @if($row->full_boxes > 0)
                        <span class="ss-box-pill"
                              style="background:var(--green-dim);color:var(--green)">
                            {{ $row->full_boxes }}F
                        </span>
                        @endif
                        @if($row->partial_boxes > 0)
                        <span class="ss-box-pill"
                              style="background:var(--amber-dim);color:var(--amber)">
                            {{ $row->partial_boxes }}P
                        </span>
                        @endif
                    </div>
                </td>

                {{-- Items --}}
                <td class="right mono"
                    style="color:{{ $barColor }}">
                    {{ number_format($row->total_items) }}
                </td>

                {{-- Stock bar --}}
                <td class="center ss-hide-mob">
                    <div style="display:flex;align-items:center;justify-content:center;gap:8px">
                        <div class="ss-bar-wrap" style="width:80px">
                            <div class="ss-bar-fill"
                                 style="width:{{ $pct }}%;background:{{ $barColor }}">
                            </div>
                        </div>
                        <span style="font-size:11px;font-family:var(--mono);
                                     color:var(--text-dim);width:36px;text-align:right">
                            {{ $pct }}%
                        </span>
                    </div>
                </td>

                {{-- Status badge --}}
                <td class="center">
                    @if($isCrit)
                        <span class="ss-badge"
                              style="background:var(--red-dim);color:var(--red)">
                            ⚠ Critical
                        </span>
                    @elseif($isLow)
                        <span class="ss-badge"
                              style="background:var(--amber-dim);color:var(--amber)">
                            ↓ Low
                        </span>
                    @else
                        <span class="ss-badge"
                              style="background:var(--green-dim);color:var(--green)">
                            ✓ OK
                        </span>
                    @endif
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="ss-empty">
                        <div class="ss-empty-icon">📦</div>
                        <div class="ss-empty-title">
                            @if($statusFilter === 'low')
                                No low stock products
                            @elseif($search)
                                No products match "{{ $search }}"
                            @else
                                No stock at this shop yet
                            @endif
                        </div>
                        <div class="ss-empty-sub">
                            @if($statusFilter === 'low')
                                All products are well stocked.
                            @elseif(!$search)
                                Stock arrives here via warehouse transfers.
                                Check "Previously Stocked" to request more.
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    {{-- Pagination --}}
    @if($stockData->hasPages())
    <div class="ss-pagination">{{ $stockData->links() }}</div>
    @endif
</div>

{{-- ── Previously stocked ───────────────────────────────────────────── --}}
@else

<div class="ss-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:13px;font-weight:700;color:var(--text)">
                Previously Stocked Products
            </div>
            <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                Transferred here before but now fully sold out
            </div>
        </div>
        <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">
            {{ count($previouslyStocked) }} products
        </span>
    </div>
    <div style="overflow-x:auto">
    <table class="ss-table">
        <thead>
            <tr>
                <th>Product</th>
                <th class="ss-hide-mob">Category</th>
                <th class="ss-hide-mob">SKU</th>
                <th>Last Received</th>
                <th class="right">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($previouslyStocked as $p)
            <tr>
                <td>
                    <div class="ss-prev-name">{{ $p->name }}</div>
                </td>
                <td class="ss-hide-mob"
                    style="font-size:12px;color:var(--text-dim)">
                    {{ $p->category_name ?? '—' }}
                </td>
                <td class="ss-hide-mob"
                    style="font-size:11px;color:var(--text-dim);font-family:var(--mono)">
                    {{ $p->sku }}
                </td>
                <td style="font-size:12px;color:var(--text-dim)">
                    @if($p->last_received_at)
                        {{ \Carbon\Carbon::parse($p->last_received_at)->diffForHumans() }}
                    @else
                        —
                    @endif
                </td>
                <td class="right">
                    <a href="{{ route('shop.transfers.request') }}"
                       class="ss-req-btn">
                        Request Stock
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <div class="ss-empty">
                        <div class="ss-empty-icon">✅</div>
                        <div class="ss-empty-title">No history yet</div>
                        <div class="ss-empty-sub">
                            Products appear here once fully sold out after a transfer.
                        </div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

@endif

</div>
```

---

## STEP 2 — Clear caches

```bash
php artisan view:clear && php artisan cache:clear
```

---

## Do NOT touch

- `app/Livewire/Shop/StockLevels.php` — the PHP is already correct
- Any other files

---

## Verification

1. Open `/shop/inventory/stock` — a clean table appears, one row per product
2. Each row: colored dot · product name + SKU · category · box pills (F/P) ·
   item count (colored by health) · mini progress bar · status badge
3. On mobile: Category and progress bar columns hide, table stays readable
4. Low Stock tab shows only products below threshold with row counts in the tab
5. Previously Stocked shows a simple table with "Request Stock" links
6. Pagination appears below the table when products exceed the page size
