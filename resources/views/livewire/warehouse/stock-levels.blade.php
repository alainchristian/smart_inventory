<div style="font-family:var(--font)">
<style>
/* ══ Warehouse Stock Levels ══════════════════════ prefix: wsl- ══════════ */

.wsl-page { padding:0 0 80px; }

/* ── Header ─────────────────────────────────────────────────────────────── */
.wsl-header       { display:flex;align-items:flex-start;justify-content:space-between;
                    gap:16px;margin-bottom:24px;flex-wrap:wrap; }
.wsl-header-title { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px; }
.wsl-header-sub   { font-size:13px;color:var(--text-dim);margin:0; }

/* ── Warehouse selector (owner) ─────────────────────────────────────────── */
.wsl-wh-bar    { background:var(--surface);border:none;border-radius:var(--r);
                 box-shadow:var(--shadow-card);margin-bottom:20px;
                 display:flex;align-items:center;gap:10px;padding:12px 16px; }
.wsl-wh-label  { font-size:12px;font-weight:700;color:var(--text-dim);white-space:nowrap; }
.wsl-wh-select { flex:1;padding:0;border:none;background:transparent;color:var(--text);
                 font-size:14px;font-weight:600;font-family:var(--font);
                 cursor:pointer;outline:none; }

/* ── Filter bar ─────────────────────────────────────────────────────────── */
.wsl-bar         { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:20px; }
.wsl-search-wrap { flex:1;min-width:220px;position:relative; }
.wsl-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);
                   width:14px;height:14px;color:var(--text-dim);pointer-events:none; }
.wsl-search      { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);
                   border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);
                   outline:none;box-sizing:border-box;font-family:var(--font);
                   transition:border-color var(--tr); }
.wsl-search::placeholder { color:var(--text-dim); }
.wsl-search:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.wsl-select      { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;
                   font-size:13px;font-weight:600;background:var(--surface);color:var(--text);
                   outline:none;cursor:pointer;font-family:var(--font);
                   transition:border-color var(--tr); }
.wsl-select:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }

/* ── KPI grid ───────────────────────────────────────────────────────────── */
.wsl-kpis    { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px; }
.wsl-kpi     { background:var(--surface);border:none;border-radius:var(--r);
               box-shadow:var(--shadow-card);padding:20px;
               display:flex;flex-direction:column;gap:14px;transition:box-shadow var(--tr); }
.wsl-kpi:hover { box-shadow:var(--shadow-card-hover); }
.wsl-kpi-row  { display:flex;align-items:center;gap:12px; }
.wsl-kpi-icon { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;
                justify-content:center;flex-shrink:0; }
.wsl-kpi-body { flex:1;min-width:0; }
.wsl-kpi-label { font-size:11px;font-weight:700;letter-spacing:.5px;
                 text-transform:uppercase;color:var(--text-dim); }
.wsl-kpi-val  { font-size:24px;font-weight:800;font-family:var(--mono);
                letter-spacing:-1px;line-height:1; }
.wsl-kpi-divider { height:1px;background:var(--border); }
.wsl-kpi-footer { display:grid;grid-template-columns:repeat(3,1fr); }
.wsl-kpi-stat   { display:flex;flex-direction:column;align-items:center;gap:3px;padding:4px 0; }
.wsl-kpi-stat-v { font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text-sub); }
.wsl-kpi-stat-l { font-size:10px;color:var(--text-dim);letter-spacing:.3px; }

/* ── Table ──────────────────────────────────────────────────────────────── */
.wsl-table-wrap  { background:var(--surface);border:none;border-radius:var(--r);
                   box-shadow:var(--shadow-card); }
.wsl-table-head  { padding:14px 16px;border-bottom:1px solid var(--border);
                   display:flex;align-items:center;justify-content:space-between;gap:12px; }
.wsl-table-title { font-size:13px;font-weight:700;color:var(--text);margin:0; }
.wsl-scroll      { overflow-x:auto;-webkit-overflow-scrolling:touch; }
.wsl-table       { width:100%;border-collapse:collapse;table-layout:fixed;min-width:960px; }
.wsl-table thead tr { border-bottom:2px solid var(--border); }
.wsl-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;
                      letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);
                      white-space:nowrap; }
.wsl-table thead th.c { text-align:center; }
.wsl-table thead th.r { text-align:right; }
.wsl-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr); }
.wsl-table tbody tr:last-child { border-bottom:none; }
.wsl-table tbody tr:hover     { background:var(--surface2); }
.wsl-table tbody tr.wsl-low   { background:rgba(217,119,6,.04); }
.wsl-table tbody tr.wsl-low:hover { background:rgba(217,119,6,.09); }
.wsl-table td   { padding:12px 16px;font-size:13px;vertical-align:middle;color:var(--text-sub); }
.wsl-table td.c { text-align:center; }
.wsl-table td.r { text-align:right; }

/* ── Badges ─────────────────────────────────────────────────────────────── */
.wsl-badge     { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;
                 padding:3px 9px;border-radius:6px;white-space:nowrap; }
.wsl-badge-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0; }

/* ── Empty state ────────────────────────────────────────────────────────── */
.wsl-empty       { padding:60px 20px;text-align:center; }
.wsl-empty-icon  { width:44px;height:44px;border-radius:12px;background:var(--surface2);
                   display:flex;align-items:center;justify-content:center;margin:0 auto 14px; }
.wsl-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px; }
.wsl-empty-sub   { font-size:13px;color:var(--text-dim); }

/* ── Pagination ─────────────────────────────────────────────────────────── */
.wsl-pagination { padding:12px 16px;border-top:1px solid var(--border); }

/* ── Responsive ─────────────────────────────────────────────────────────── */
@media(max-width:900px) { .wsl-kpis { grid-template-columns:repeat(2,1fr); } }
@media(max-width:480px) { .wsl-kpis { grid-template-columns:1fr; } }
@media(max-width:640px) {
    .wsl-bar { flex-direction:column;align-items:stretch; }
    .wsl-search-wrap { min-width:0; }
    .wsl-select { width:100%; }
}
</style>

<div class="wsl-page">

{{-- ── Page header ──────────────────────────────────────────────────────── --}}
<div class="wsl-header">
    <div>
        <h1 class="wsl-header-title">Warehouse Stock Levels</h1>
        <p class="wsl-header-sub">
            @if(auth()->user()->isOwner() && $warehouseId)
                Viewing {{ $warehouses->firstWhere('id', $warehouseId)?->name ?? 'Warehouse' }}
            @elseif(auth()->user()->isWarehouseManager())
                {{ auth()->user()->location?->name ?? 'Your Warehouse' }}
            @else
                Select a warehouse to view inventory
            @endif
        </p>
    </div>
</div>

{{-- ── Warehouse selector (owners only) ────────────────────────────────── --}}
@if(auth()->user()->isOwner())
<div class="wsl-wh-bar">
    <svg width="14" height="14" fill="none" stroke="var(--text-dim)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
    </svg>
    <span class="wsl-wh-label">Warehouse</span>
    <select wire:model.live="warehouseId" class="wsl-wh-select">
        <option value="">— Select a warehouse —</option>
        @foreach($warehouses as $wh)
            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
        @endforeach
    </select>
</div>
@endif

@if($needsWarehouseSelection ?? false)

{{-- ── No warehouse selected ────────────────────────────────────────────── --}}
<div class="wsl-table-wrap">
    <div class="wsl-empty">
        <div class="wsl-empty-icon">
            <svg width="20" height="20" fill="none" stroke="var(--text-dim)" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div class="wsl-empty-title">Select a warehouse</div>
        <div class="wsl-empty-sub">Choose a warehouse above to view its current stock levels.</div>
    </div>
</div>

@else

@php
    $sdCol        = collect($stockData);
    $totalBoxes   = $sdCol->sum('full_boxes') + $sdCol->sum('partial_boxes');
    $fullBoxes    = (int) $sdCol->sum('full_boxes');
    $partialBoxes = (int) $sdCol->sum('partial_boxes');
    $totalItems   = (int) $sdCol->sum('total_items');
    $lowCount     = $sdCol->where('is_low_stock', true)->count();
    $outCount     = $sdCol->where('total_items', 0)->count();
    $inStockCount = $sdCol->where('total_items', '>', 0)->where('is_low_stock', false)->count();
    $productCount = count($stockData);
    $avgBoxes     = $productCount > 0 ? round($totalBoxes / $productCount, 1) : 0;
@endphp

{{-- ── Filter bar ───────────────────────────────────────────────────────── --}}
<div class="wsl-bar">
    <div class="wsl-search-wrap">
        <svg class="wsl-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
        </svg>
        <input type="text"
               wire:model.live.debounce.300ms="search"
               placeholder="Search by name, SKU, or barcode…"
               class="wsl-search">
    </div>
    <select wire:model.live="statusFilter" class="wsl-select">
        <option value="all">All Products</option>
        <option value="low">Low Stock</option>
        <option value="out">Out of Stock</option>
    </select>
</div>

{{-- ── KPI cards ─────────────────────────────────────────────────────────── --}}
<div class="wsl-kpis">

    {{-- Products --}}
    <div class="wsl-kpi">
        <div class="wsl-kpi-row">
            <div class="wsl-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div class="wsl-kpi-body">
                <div class="wsl-kpi-label">Products</div>
            </div>
        </div>
        <div class="wsl-kpi-val" style="color:var(--accent)">{{ number_format($productCount) }}</div>
        <div class="wsl-kpi-divider"></div>
        <div class="wsl-kpi-footer">
            <div class="wsl-kpi-stat">
                <span class="wsl-kpi-stat-v" style="color:var(--green)">{{ $inStockCount }}</span>
                <span class="wsl-kpi-stat-l">In Stock</span>
            </div>
            <div class="wsl-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="wsl-kpi-stat-v" style="color:var(--amber)">{{ $lowCount }}</span>
                <span class="wsl-kpi-stat-l">Low</span>
            </div>
            <div class="wsl-kpi-stat">
                <span class="wsl-kpi-stat-v" style="color:var(--red)">{{ $outCount }}</span>
                <span class="wsl-kpi-stat-l">Out</span>
            </div>
        </div>
    </div>

    {{-- Total Boxes --}}
    <div class="wsl-kpi">
        <div class="wsl-kpi-row">
            <div class="wsl-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <div class="wsl-kpi-body">
                <div class="wsl-kpi-label">Total Boxes</div>
            </div>
        </div>
        <div class="wsl-kpi-val" style="color:var(--green)">{{ number_format($totalBoxes) }}</div>
        <div class="wsl-kpi-divider"></div>
        <div class="wsl-kpi-footer">
            <div class="wsl-kpi-stat">
                <span class="wsl-kpi-stat-v">{{ number_format($fullBoxes) }}</span>
                <span class="wsl-kpi-stat-l">Full</span>
            </div>
            <div class="wsl-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="wsl-kpi-stat-v" style="color:var(--amber)">{{ number_format($partialBoxes) }}</span>
                <span class="wsl-kpi-stat-l">Partial</span>
            </div>
            <div class="wsl-kpi-stat">
                <span class="wsl-kpi-stat-v">{{ $avgBoxes }}</span>
                <span class="wsl-kpi-stat-l">Avg / Product</span>
            </div>
        </div>
    </div>

    {{-- Low Stock --}}
    <div class="wsl-kpi">
        <div class="wsl-kpi-row">
            <div class="wsl-kpi-icon" style="background:{{ $lowCount > 0 ? 'var(--amber-dim)' : 'var(--green-dim)' }};color:{{ $lowCount > 0 ? 'var(--amber)' : 'var(--green)' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="wsl-kpi-body">
                <div class="wsl-kpi-label">Low Stock</div>
            </div>
        </div>
        <div class="wsl-kpi-val" style="color:{{ $lowCount > 0 ? 'var(--amber)' : 'var(--green)' }}">{{ number_format($lowCount) }}</div>
        <div class="wsl-kpi-divider"></div>
        <div class="wsl-kpi-footer">
            <div class="wsl-kpi-stat">
                <span class="wsl-kpi-stat-v" style="color:var(--red)">{{ $outCount }}</span>
                <span class="wsl-kpi-stat-l">Out of Stock</span>
            </div>
            <div class="wsl-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="wsl-kpi-stat-v" style="color:var(--amber)">{{ $lowCount }}</span>
                <span class="wsl-kpi-stat-l">Low</span>
            </div>
            <div class="wsl-kpi-stat">
                <span class="wsl-kpi-stat-v" style="color:var(--green)">{{ $inStockCount }}</span>
                <span class="wsl-kpi-stat-l">OK</span>
            </div>
        </div>
    </div>

    {{-- Total Items --}}
    <div class="wsl-kpi">
        <div class="wsl-kpi-row">
            <div class="wsl-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <div class="wsl-kpi-body">
                <div class="wsl-kpi-label">Total Items</div>
            </div>
        </div>
        <div class="wsl-kpi-val" style="color:var(--violet)">{{ number_format($totalItems) }}</div>
        <div class="wsl-kpi-divider"></div>
        <div class="wsl-kpi-footer">
            <div class="wsl-kpi-stat">
                <span class="wsl-kpi-stat-v">{{ number_format($fullBoxes) }}</span>
                <span class="wsl-kpi-stat-l">Full Boxes</span>
            </div>
            <div class="wsl-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="wsl-kpi-stat-v">{{ number_format($partialBoxes) }}</span>
                <span class="wsl-kpi-stat-l">Partial Boxes</span>
            </div>
            <div class="wsl-kpi-stat">
                <span class="wsl-kpi-stat-v">{{ $productCount > 0 ? number_format(round($totalItems / $productCount)) : 0 }}</span>
                <span class="wsl-kpi-stat-l">Avg / Product</span>
            </div>
        </div>
    </div>

</div>

{{-- ── Stock Table ────────────────────────────────────────────────────────── --}}
<div class="wsl-table-wrap">
    <div class="wsl-table-head">
        <span class="wsl-table-title">Inventory</span>
        <span style="font-size:12px;color:var(--text-dim)">
            {{ number_format($productCount) }} {{ $productCount === 1 ? 'product' : 'products' }}
        </span>
    </div>

    <div class="wsl-scroll">
        <table class="wsl-table">
            <colgroup>
                <col style="width:240px">
                <col style="width:150px">
                <col style="width:120px">
                <col style="width:100px">
                <col style="width:100px">
                <col style="width:130px">
                <col style="width:120px">
            </colgroup>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Barcode / SKU</th>
                    <th class="c">Total Boxes</th>
                    <th class="c">Full</th>
                    <th class="c">Partial</th>
                    <th class="r">Total Items</th>
                    <th class="c">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockData as $data)
                <tr class="{{ $data['is_low_stock'] ? 'wsl-low' : '' }}">
                    <td>
                        <span style="font-size:13px;font-weight:600;color:var(--text);
                                     white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
                                     display:block;max-width:220px"
                              title="{{ $data['product']->name }}">
                            {{ $data['product']->name }}
                        </span>
                    </td>
                    <td>
                        <span style="font-family:var(--mono);font-size:12px;color:var(--text-dim)">
                            {{ $data['product']->barcode ?? ($data['product']->sku ?? '—') }}
                        </span>
                    </td>
                    <td class="c">
                        <span style="font-family:var(--mono);font-weight:700;font-size:14px;color:var(--text)">
                            {{ $data['full_boxes'] + $data['partial_boxes'] }}
                        </span>
                    </td>
                    <td class="c">
                        <span style="font-family:var(--mono);font-weight:600;color:var(--green)">
                            {{ $data['full_boxes'] }}
                        </span>
                    </td>
                    <td class="c">
                        <span style="font-family:var(--mono);font-weight:600;color:var(--amber)">
                            {{ $data['partial_boxes'] }}
                        </span>
                    </td>
                    <td class="r">
                        <span style="font-family:var(--mono);font-size:13px;color:var(--text-sub);white-space:nowrap">
                            {{ number_format($data['total_items']) }}
                        </span>
                    </td>
                    <td class="c">
                        @if($data['total_items'] == 0)
                            <span class="wsl-badge" style="background:var(--red-dim);color:var(--red)">
                                <span class="wsl-badge-dot" style="background:var(--red)"></span>Out of Stock
                            </span>
                        @elseif($data['is_low_stock'])
                            <span class="wsl-badge" style="background:var(--amber-dim);color:var(--amber)">
                                <span class="wsl-badge-dot" style="background:var(--amber)"></span>Low Stock
                            </span>
                        @else
                            <span class="wsl-badge" style="background:var(--green-dim);color:var(--green)">
                                <span class="wsl-badge-dot" style="background:var(--green)"></span>In Stock
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="wsl-empty">
                            <div class="wsl-empty-icon">
                                <svg width="20" height="20" fill="none" stroke="var(--text-dim)" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                            </div>
                            <div class="wsl-empty-title">No products found</div>
                            <div class="wsl-empty-sub">Try adjusting your search or filter.</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="wsl-pagination">
        {{ $products->links() }}
    </div>
    @endif
</div>

@endif

</div>
</div>
