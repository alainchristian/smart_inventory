<div style="font-family:var(--font)">
<style>
/* ── KPI strip ───────────────────────────────────── */
.bx-kpis {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
    margin-bottom:24px;
}
/* Extend .bkpi with amber + red variants */
.bkpi.amber::after { background:linear-gradient(90deg,var(--amber),transparent) }
.bkpi.red::after   { background:linear-gradient(90deg,var(--red),transparent) }
.bkpi-icon.amber   { background:var(--amber-dim);color:var(--amber) }
.bkpi-icon.red     { background:var(--red-dim);color:var(--red) }
.bkpi-pct.amber    { background:var(--amber-dim);color:var(--amber) }
.bkpi-pct.red      { background:var(--red-dim);color:var(--red) }
.bkpi-pct.down     { background:var(--red-dim);color:var(--red) }

/* ── Filter panel ────────────────────────────────── */
.bx-filter-panel {
    background:var(--surface);border:none;box-shadow:var(--shadow-card);
    border-radius:var(--r);padding:14px 16px;margin-bottom:16px;
    position:sticky;top:var(--topbar-height);z-index:15
}
.bx-filter-panel.bx-open { position:static }

/* Search row — always visible */
.bx-search-row  { display:flex;align-items:center;gap:10px }
.bx-search-wrap { flex:1;min-width:0;position:relative }
.bx-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.bx-search {
    width:100%;padding:9px 34px 9px 33px;
    border:1.5px solid var(--border);border-radius:10px;
    font-size:13px;background:var(--surface);color:var(--text);
    outline:none;box-sizing:border-box;font-family:var(--font);
    transition:border-color var(--tr)
}
.bx-search:focus { border-color:var(--accent) }
.bx-search-clear {
    position:absolute;right:10px;top:50%;transform:translateY(-50%);
    width:20px;height:20px;border:none;background:none;cursor:pointer;
    color:var(--text-dim);display:flex;align-items:center;justify-content:center;border-radius:4px
}
.bx-search-clear:hover { color:var(--text) }
.bx-result-count { font-size:12px;font-weight:600;color:var(--text-dim);white-space:nowrap;flex-shrink:0 }

/* Product suggestions dropdown */
.bx-suggest-panel {
    position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:30;
    background:var(--surface);border-radius:var(--rsm);box-shadow:var(--shadow-card-hover);
    max-height:320px;overflow-y:auto
}
.bx-suggest-item {
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    padding:9px 14px;cursor:pointer;border-bottom:1px solid var(--border);transition:background var(--tr)
}
.bx-suggest-item:last-child { border-bottom:none }
.bx-suggest-item:hover { background:var(--surface2) }
.bx-suggest-main { font-size:13px;font-weight:700;color:var(--text) }
.bx-suggest-sub { display:flex;align-items:center;gap:6px;margin-top:3px }
.bx-suggest-stat { font-size:11px;font-weight:600;color:var(--text-dim);white-space:nowrap;flex-shrink:0 }
.bx-suggest-empty { padding:14px;text-align:center;font-size:12px;color:var(--text-dim) }

/* Mobile bar — active chips + Filters toggle (hidden on desktop) */
.bx-mobile-bar { display:none;align-items:center;justify-content:space-between;gap:8px;margin-top:10px;flex-wrap:wrap }
.bx-active-chips { display:flex;align-items:center;gap:5px;flex-wrap:wrap;flex:1;min-width:0 }
.bx-ac {
    display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;
    font-size:11px;font-weight:700;white-space:nowrap;background:var(--surface2);color:var(--text-sub)
}
.bx-ac--none { color:var(--text-dim);font-weight:500;background:transparent }
.bx-ac--removable {
    border:none;cursor:pointer;font-family:var(--font);transition:all var(--tr)
}
.bx-ac--removable:hover { background:var(--red-dim,rgba(220,38,38,.12));color:var(--red) }
.bx-filter-toggle {
    display:inline-flex;align-items:center;gap:6px;padding:6px 13px;border:1.5px solid var(--border);
    border-radius:9px;background:var(--surface);font-size:12px;font-weight:600;color:var(--text-dim);
    cursor:pointer;transition:all var(--tr);white-space:nowrap;flex-shrink:0;font-family:var(--font)
}
.bx-filter-toggle:hover,.bx-filter-toggle.is-open { border-color:var(--accent);color:var(--accent) }
.bx-chevron { transition:transform .2s }
.bx-chevron--up { transform:rotate(180deg) }

/* Filter rows — always visible on desktop */
.bx-filter-rows { display:flex;flex-wrap:wrap;align-items:center;gap:10px 16px;margin-top:12px }
.bx-filter-row  { display:flex;align-items:center;gap:8px }
.bx-filter-label {
    display:flex;align-items:center;font-size:11px;font-weight:700;text-transform:uppercase;
    letter-spacing:.06em;color:var(--text-dim);white-space:nowrap
}
.bx-pill {
    flex-shrink:0;border:1.5px solid var(--border);border-radius:7px;background:var(--surface);
    cursor:pointer;transition:all var(--tr);white-space:nowrap;line-height:1.4;font-family:var(--font);
    padding:6px 12px;font-size:12px;font-weight:600;color:var(--text-dim);
    display:inline-flex;align-items:center;gap:5px
}
.bx-pill:hover { color:var(--text);background:var(--surface2) }
.bx-pill.active { background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 2px 8px rgba(0,0,0,.12) }

.bx-select {
    padding:8px 12px;border:1.5px solid var(--border);border-radius:10px;
    font-size:13px;background:var(--surface);color:var(--text);
    outline:none;cursor:pointer;font-family:var(--font)
}
.bx-locked-pill { display:inline-flex;align-items:center;padding:6px 12px;border-radius:7px;background:var(--surface2);color:var(--text-dim);font-size:12px;font-weight:600 }
.bx-btn-clear {
    padding:9px 16px;background:transparent;border:1.5px solid var(--border);
    border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;
    font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap
}
.bx-btn-clear:hover { border-color:var(--border-hi);color:var(--text) }

/* Mobile: collapse the filter rows behind the toggle */
@media(max-width:660px) {
    .bx-mobile-bar  { display:flex }
    .bx-result-count { display:none }
    .bx-filter-rows {
        display:none;width:100%;flex-direction:column;align-items:stretch;gap:10px
    }
    .bx-filter-rows.is-open { display:flex;animation:bx-slide-down .18s ease }
    .bx-filter-row  { flex-direction:column;align-items:stretch;gap:6px }
    .bx-select,.bx-locked-pill { width:100%;box-sizing:border-box }
    .bx-select { font-size:14px;padding:10px 12px }
    .bx-btn-clear { width:100%;padding:11px 16px }
}
@keyframes bx-slide-down { from{opacity:0;transform:translateY(-4px)} to{opacity:1;transform:translateY(0)} }

/* ── Table ───────────────────────────────────────── */
.bx-table-wrap { background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);overflow:hidden }
.bx-table { width:100%;border-collapse:collapse;font-size:13px }
.bx-table thead tr { border-bottom:2px solid var(--border) }
.bx-table thead th {
    padding:10px 14px;text-align:left;
    font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
    color:var(--text-dim);white-space:nowrap
}
.bx-table thead th.sortable { cursor:pointer }
.bx-table thead th.sortable:hover { color:var(--text) }
.bx-table tbody tr.bx-row { border-bottom:1px solid var(--border);transition:background var(--tr);cursor:pointer }
.bx-table tbody tr.bx-row:last-child { border-bottom:none }
.bx-table tbody tr.bx-row:hover { background:var(--surface2) }
.bx-table tbody tr.bx-row.is-open { background:var(--surface2) }
.bx-table td { padding:11px 14px;vertical-align:middle }

/* Badges */
.bx-chip { display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap }
.bx-badge-sm { display:inline-flex;align-items:center;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;white-space:nowrap }

/* View / expand button */
.bx-view-btn {
    padding:5px 12px;border-radius:7px;border:1.5px solid var(--border);
    background:transparent;font-size:12px;font-weight:600;cursor:pointer;
    font-family:var(--font);color:var(--text-sub);transition:all var(--tr);
    display:inline-flex;align-items:center;gap:5px
}
.bx-view-btn:hover { border-color:var(--accent);color:var(--accent) }
.bx-view-btn .bx-caret { transition:transform .18s }
.bx-view-btn.is-open .bx-caret { transform:rotate(180deg) }

/* Box code chip */
.bx-code-chip {
    display:inline-block;font-family:var(--mono);font-size:12px;font-weight:700;
    padding:3px 9px;border-radius:7px;cursor:pointer;transition:opacity var(--tr)
}
.bx-code-chip:hover { opacity:.75 }

/* Mini status breakdown (Boxes column) */
.bx-mini-stats { display:flex;gap:10px }
.bx-mini-stat  { text-align:center }
.bx-mini-stat-v { font-size:11px;font-weight:700;font-family:var(--mono) }
.bx-mini-stat-l { font-size:9px;color:var(--text-dim);margin-top:1px }

/* Expand / drill-down panel */
.bx-expand-row td { padding:0;background:var(--surface) }
.bx-expand-inner { padding:16px 20px 18px;border-left:3px solid var(--accent) }
.bx-expand-title { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);margin-bottom:10px }
.bx-sub-table { width:100%;border-collapse:collapse;font-size:12.5px }
.bx-sub-table thead tr { border-bottom:1px solid var(--border) }
.bx-sub-table thead th { padding:6px 10px;text-align:left;font-size:10px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.bx-sub-table tbody tr { border-bottom:1px solid var(--border) }
.bx-sub-table tbody tr:last-child { border-bottom:none }
.bx-sub-table tbody tr:hover { background:var(--surface2) }
.bx-sub-table td { padding:8px 10px;vertical-align:middle }
.bx-sub-empty { padding:16px;text-align:center;font-size:12px;color:var(--text-dim) }

/* Empty state */
.bx-empty { padding:60px 20px;text-align:center }
.bx-empty-icon  { font-size:36px;margin-bottom:10px }
.bx-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.bx-empty-sub   { font-size:13px;color:var(--text-dim);margin-bottom:16px }
.bx-empty-btn   { padding:8px 20px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-weight:600;color:var(--text-sub);cursor:pointer;background:transparent;font-family:var(--font) }

/* Responsive */
@media(max-width:1100px) { .bx-hide-lg { display:none !important } }
@media(max-width:800px)  { .bx-hide-md { display:none !important } }
@media(max-width:768px) {
    .bx-kpis { grid-template-columns:1fr 1fr;gap:8px }
    .bx-table td,.bx-table th { padding:9px 10px }
}
@media(max-width:480px) {
    .bx-kpis { grid-template-columns:1fr }
    .bx-hide-sm { display:none !important }
    .bx-view-btn { padding:4px 8px;font-size:11px }
}
</style>

{{-- ── Page header ─────────────────────────────────────────────── --}}
<div class="dashboard-page-header" style="margin-bottom:20px">
    <div>
        <h1 style="font-size:26px;font-weight:800;color:var(--text);letter-spacing:-.4px;margin:0 0 4px">Boxes</h1>
        <p style="font-size:14px;color:var(--text-dim);margin:0">Grouped by product — stock levels, sales performance, and expected revenue across every box</p>
    </div>
</div>

{{-- ── KPI strip ───────────────────────────────────────────────── --}}
@php
  $sellable    = ($stats->full_count ?? 0) + ($stats->partial_count ?? 0);
  $activeTotal = $sellable + ($stats->damaged_count ?? 0);   // non-empty boxes
@endphp
<div class="bx-kpis">

    {{-- Card 1: Sellable Boxes --}}
    <div class="bkpi blue">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="display:flex;align-items:center;gap:8px">
                <div class="bkpi-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                </div>
                <span class="bkpi-name">Sellable Boxes</span>
            </div>
            <span class="bkpi-pct blue">{{ number_format($distinctProducts) }} products</span>
        </div>
        <div class="bkpi-value" style="color:var(--accent)">{{ number_format($sellable) }}</div>
        <div class="bkpi-meta">Full &amp; partial in stock &middot; boxes</div>
        <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--green);font-family:var(--mono)">{{ number_format($stats->full_count ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Full</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--amber);font-family:var(--mono)">{{ number_format($stats->partial_count ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Partial</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ number_format($distinctProducts) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Products</div>
            </div>
        </div>
    </div>

    {{-- Card 2: Fill Rate --}}
    @php
        $fillRateColor    = $fillRate === null ? 'var(--text-dim)'
            : ($fillRate >= 70 ? 'var(--green)' : ($fillRate >= 40 ? 'var(--amber)' : 'var(--red)'));
        $fillRatePctClass = $fillRate !== null && $fillRate >= 70 ? 'green'
            : ($fillRate !== null && $fillRate >= 40 ? 'amber' : 'down');
    @endphp
    <div class="bkpi green">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="display:flex;align-items:center;gap:8px">
                <div class="bkpi-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                </div>
                <span class="bkpi-name">Fill Rate</span>
            </div>
            <span class="bkpi-pct {{ $fillRatePctClass }}">{{ number_format($stats->total_items ?? 0) }} items left</span>
        </div>
        <div class="bkpi-value" style="color:{{ $fillRateColor }}">
            @if($fillRate !== null){{ $fillRate }}%@else —@endif
        </div>
        <div class="bkpi-meta">Avg. fill across sellable boxes</div>
        <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--green);font-family:var(--mono)">{{ number_format($stats->total_items ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Items left</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-sub);font-family:var(--mono)">{{ number_format($stats->total_capacity ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Max items</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--accent);font-family:var(--mono)">{{ number_format($sellable) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Boxes</div>
            </div>
        </div>
    </div>

    {{-- Card 3: Damaged (clickable → filter) --}}
    @php
        $damagedValColor  = ($stats->damaged_count ?? 0) > 0 ? 'var(--red)' : 'var(--green)';
        $damagedPctClass  = ($stats->damaged_count ?? 0) > 0 ? 'down' : 'green';
        $damagedRate      = $activeTotal > 0
            ? round(($stats->damaged_count ?? 0) / $activeTotal * 100) : 0;
    @endphp
    <div class="bkpi red" style="cursor:pointer" wire:click="$set('status', 'damaged')">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="display:flex;align-items:center;gap:8px">
                <div class="bkpi-icon red">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <span class="bkpi-name">Damaged</span>
            </div>
            <span class="bkpi-pct {{ $damagedPctClass }}">{{ $damagedRate }}%</span>
        </div>
        <div class="bkpi-value" style="color:{{ $damagedValColor }}">{{ number_format($stats->damaged_count ?? 0) }}</div>
        <div class="bkpi-meta">Click to filter &middot; awaiting disposition</div>
        <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:{{ ($stats->damaged_count ?? 0) > 0 ? 'var(--red)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ number_format($stats->damaged_count ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Damaged</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--green);font-family:var(--mono)">{{ number_format(max(0, $activeTotal - ($stats->damaged_count ?? 0))) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Intact</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ $damagedRate }}%</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Rate</div>
            </div>
        </div>
    </div>

    {{-- Card 4: Expiring Soon (clickable → filter) --}}
    @php
        $expValColor  = ($stats->expiring_soon ?? 0) > 0 ? 'var(--amber)' : 'var(--green)';
        $expPctClass  = ($stats->expiring_soon ?? 0) > 0 ? 'amber' : 'green';
    @endphp
    <div class="bkpi amber" style="cursor:pointer" wire:click="$set('expiringOnly', true)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="display:flex;align-items:center;gap:8px">
                <div class="bkpi-icon amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <span class="bkpi-name">Expiring Soon</span>
            </div>
            <span class="bkpi-pct {{ $expPctClass }}">&le;30 days</span>
        </div>
        <div class="bkpi-value" style="color:{{ $expValColor }}">{{ number_format($stats->expiring_soon ?? 0) }}</div>
        <div class="bkpi-meta">Click to filter &middot; within 30 days</div>
        <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:{{ ($stats->expiring_soon ?? 0) > 0 ? 'var(--amber)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ number_format($stats->expiring_soon ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">&le;30 days</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-sub);font-family:var(--mono)">{{ number_format(max(0, ($stats->total ?? 0) - ($stats->expiring_soon ?? 0))) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Safe</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">30d</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Window</div>
            </div>
        </div>
    </div>

</div>

{{-- ── Filter panel ──────────────────────────────────────────────── --}}
@php
    $anyFilterActive = $search || $locationType || $categoryId || $status || $expiringOnly || $lowStockOnly || $periodActive;

    $locationChipLabel = null;
    if (!$locationLocked && $locationType) {
        if ($locationType === 'warehouse') {
            $locationChipLabel = $locationId
                ? $warehouses->firstWhere('id', $locationId)?->name
                : 'All Warehouses';
        } elseif ($locationType === 'shop') {
            $locationChipLabel = $locationId
                ? $shops->firstWhere('id', $locationId)?->name
                : 'All Shops';
        }
    }
    $categoryChipLabel = $categoryId ? $categories->firstWhere('id', $categoryId)?->name : null;
@endphp
<div class="bx-filter-panel" x-data="{ open: false }" :class="{ 'bx-open': open }" @click.outside="open = false">

    {{-- Product filter — by name, SKU, or barcode, with a suggestions
         dropdown on focus (still fully typeable — the dropdown just
         narrows live alongside the same search filter) --}}
    <div class="bx-search-row" x-data="{ suggestOpen: false }" @click.outside="suggestOpen = false">
        <span class="bx-filter-label" style="flex-shrink:0">Product</span>
        <div class="bx-search-wrap">
            <svg class="bx-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input wire:model.live.debounce.300ms="search"
                   class="bx-search" type="text" autocomplete="off"
                   placeholder="Filter by name, SKU, or barcode…"
                   @focus="suggestOpen = true"
                   @keydown.escape="suggestOpen = false">
            @if($search)
            <button wire:click="$set('search','')" @click="suggestOpen = false" class="bx-search-clear" title="Clear">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
            @endif

            <div class="bx-suggest-panel" x-show="suggestOpen" x-cloak style="display:none">
                @forelse($suggestions as $sug)
                <div class="bx-suggest-item"
                     wire:click="selectProductSuggestion({{ $sug->product_id }})"
                     @click="suggestOpen = false"
                     wire:key="sugg-{{ $sug->product_id }}">
                    <div style="min-width:0">
                        <div class="bx-suggest-main">{{ $sug->product_name }}</div>
                        <div class="bx-suggest-sub">
                            @if($sug->category_name)
                            <span class="td-2l-badge" style="background:var(--accent-dim);color:var(--accent)">{{ $sug->category_name }}</span>
                            @endif
                            @if($sug->product_sku)
                            <span style="font-family:var(--mono);font-size:10px;color:var(--text-dim)">{{ $sug->product_sku }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="bx-suggest-stat">{{ number_format($sug->sellable_box_count) }} {{ Str::plural('box', $sug->sellable_box_count) }}</span>
                </div>
                @empty
                <div class="bx-suggest-empty">No products match{{ $search ? " \"{$search}\"" : '' }}</div>
                @endforelse
            </div>
        </div>
        <div class="bx-result-count">{{ number_format($filteredCount) }} product{{ $filteredCount === 1 ? '' : 's' }}</div>
    </div>

    {{-- Mobile bar: active-filter chips + toggle (hidden on desktop) --}}
    <div class="bx-mobile-bar">
        <div class="bx-active-chips">
            @if($locationLocked)
                <span class="bx-ac">{{ $warehouses->firstWhere('id', $locationId)?->name ?? 'My Warehouse' }}</span>
            @endif
            @if($locationChipLabel)
            <button type="button" wire:click="$set('locationType', ''); $set('locationId', '')" class="bx-ac bx-ac--removable">
                {{ $locationChipLabel }}
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
            @endif
            @if($categoryChipLabel)
            <button type="button" wire:click="$set('categoryId', '')" class="bx-ac bx-ac--removable">
                {{ $categoryChipLabel }}
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
            @endif
            @if($status)
            <button type="button" wire:click="$set('status', '')" class="bx-ac bx-ac--removable">
                {{ ucfirst($status) }}
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
            @endif
            @if($expiringOnly)
            <button type="button" wire:click="$set('expiringOnly', false)" class="bx-ac bx-ac--removable">
                Expiring
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
            @endif
            @if($lowStockOnly)
            <button type="button" wire:click="$set('lowStockOnly', false)" class="bx-ac bx-ac--removable">
                Low stock
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
            @endif
            @if($periodActive)
            <button type="button" wire:click="setPeriodPreset('all_time')" class="bx-ac bx-ac--removable">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d M') }}–{{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
            @endif
            @if(!$anyFilterActive)<span class="bx-ac bx-ac--none">No filters applied</span>@endif
        </div>
        <button @click="open = !open" class="bx-filter-toggle" :class="{ 'is-open': open }">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="4" y1="6" x2="20" y2="6" stroke-linecap="round"/><line x1="8" y1="12" x2="20" y2="12" stroke-linecap="round"/><line x1="12" y1="18" x2="20" y2="18" stroke-linecap="round"/></svg>
            Filters
            <svg class="bx-chevron" :class="{ 'bx-chevron--up': open }" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>

    {{-- Filter rows: always visible desktop, collapsible on mobile --}}
    <div class="bx-filter-rows" :class="{ 'is-open': open }">

        {{-- Location --}}
        <div class="bx-filter-row">
            <span class="bx-filter-label">Location</span>
            @if($locationLocked)
                {{-- Warehouse managers are scoped to their own warehouse server-side
                     (see BoxList::render()) — no location filter to show, since
                     switching it wouldn't do anything. --}}
                <span class="bx-locked-pill">{{ $warehouses->firstWhere('id', $locationId)?->name ?? 'My Warehouse' }}</span>
            @else
                <select wire:model.live="locationType" class="bx-select" @change="if (!$event.target.value) open = false">
                    <option value="">All Locations</option>
                    <option value="warehouse">Warehouse</option>
                    <option value="shop">Shop</option>
                </select>
            @endif
        </div>

        {{-- Location (cascades) --}}
        @if(!$locationLocked && $locationType === 'warehouse')
        <div class="bx-filter-row">
            <select wire:model.live="locationId" class="bx-select" @change="open = false">
                <option value="">All Warehouses</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
            </select>
        </div>
        @elseif(!$locationLocked && $locationType === 'shop')
        <div class="bx-filter-row">
            <select wire:model.live="locationId" class="bx-select" @change="open = false">
                <option value="">All Shops</option>
                @foreach($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Category --}}
        <div class="bx-filter-row">
            <span class="bx-filter-label">Category</span>
            <select wire:model.live="categoryId" class="bx-select" @change="open = false">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Period — scopes Sold/Revenue Produced to a date range, and (for
             a range ending in the past) reconstructs Remaining as of that
             date from the box movement history instead of live stock --}}
        <div class="bx-filter-row" style="flex-wrap:wrap">
            <span class="bx-filter-label">Period</span>
            <div style="display:flex;gap:4px;flex-wrap:wrap">
                @foreach(['all_time'=>'All time','today'=>'Today','week'=>'This Week','month'=>'This Month','last_30'=>'Last 30 Days'] as $key => $label)
                <button type="button" class="bx-pill {{ $periodPreset === $key ? 'active' : '' }}"
                        wire:click="setPeriodPreset('{{ $key }}')" @click="open = false">{{ $label }}</button>
                @endforeach
            </div>
            <input type="date" wire:model.live="dateFrom" class="bx-select" style="padding:6px 10px">
            <span style="color:var(--text-dim);font-size:12px">→</span>
            <input type="date" wire:model.live="dateTo" class="bx-select" style="padding:6px 10px">
        </div>

        {{-- Status — not shown for a historical period: box status history
             (particularly "damaged") isn't reliably reconstructable from
             the movement ledger, so the filter wouldn't mean anything --}}
        @if(!$isHistorical)
        <div class="bx-filter-row">
            <span class="bx-filter-label">Status</span>
            <select wire:model.live="status" class="bx-select" @change="open = false">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}">{{ ucfirst($s->value) }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Options: Expiring / Low stock toggles --}}
        <div class="bx-filter-row">
            <span class="bx-filter-label">Options</span>
            <label class="bx-pill {{ $expiringOnly ? 'active' : '' }}" style="cursor:pointer">
                <input type="checkbox" wire:model.live="expiringOnly" @change="open = false" style="display:none">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                Expiring only
            </label>
            <label class="bx-pill {{ $lowStockOnly ? 'active' : '' }}" style="cursor:pointer">
                <input type="checkbox" wire:model.live="lowStockOnly" @change="open = false" style="display:none">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Low stock only
            </label>
        </div>

        {{-- Clear --}}
        @if($anyFilterActive)
        <button wire:click="clearFilters" @click="open = false" class="bx-btn-clear">Clear all filters</button>
        @endif

    </div>

</div>

{{-- ── Historical period banner ──────────────────────────────────── --}}
@if($isHistorical)
<div style="display:flex;align-items:flex-start;gap:10px;background:var(--amber-dim);border-radius:var(--r);padding:12px 16px;margin-bottom:16px">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--amber);flex-shrink:0;margin-top:1px">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <div style="font-size:12.5px;color:var(--text-sub);line-height:1.5">
        <strong>Remaining, Boxes, Cost Value, and Revenue Expected are reconstructed as of {{ $asOfDate->format('d M Y, H:i') }}</strong>
        from the box movement history — not live stock. Damage history isn't tracked, so historical boxes show as Full/Partial only.
        Sold and Revenue Produced reflect sales within {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}–{{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}.
        The KPI cards above always show today's live inventory.
    </div>
</div>
@elseif($periodActive)
<div style="display:flex;align-items:center;gap:10px;background:var(--accent-dim);border-radius:var(--r);padding:10px 16px;margin-bottom:16px;font-size:12.5px;color:var(--text-sub)">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--accent);flex-shrink:0">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
    </svg>
    Sold and Revenue Produced reflect sales within {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}–{{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}. Remaining stock is still today's live figure, since this period includes today.
</div>
@endif

{{-- ── Table ──────────────────────────────────────────────────── --}}
<div class="bx-table-wrap">
    <div style="overflow-x:auto">
    <table class="bx-table">
        <thead>
            <tr>
                {{-- Product — sortable --}}
                <th wire:click="sortColumn('name')" class="sortable">
                    Product
                    @if($sortBy === 'name')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                {{-- Boxes — sortable --}}
                <th wire:click="sortColumn('box_count')" class="sortable">
                    Boxes
                    @if($sortBy === 'box_count')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                {{-- Remaining — sortable --}}
                <th wire:click="sortColumn('sellable_box_count')" class="sortable">
                    Remaining{{ $isHistorical ? ' (as of)' : '' }}
                    @if($sortBy === 'sellable_box_count')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                {{-- Sold — sortable --}}
                <th wire:click="sortColumn('sold_qty')" class="sortable">
                    Sold{{ $periodActive ? ' (period)' : '' }}
                    @if($sortBy === 'sold_qty')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                @if($isOwner)
                {{-- Revenue Produced — sortable --}}
                <th wire:click="sortColumn('revenue_produced')" class="sortable bx-hide-lg" style="text-align:right">
                    Revenue Produced
                    @if($sortBy === 'revenue_produced')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                {{-- Revenue Expected — sortable --}}
                <th wire:click="sortColumn('revenue_expected')" class="sortable bx-hide-lg" style="text-align:right">
                    Revenue Expected
                    @if($sortBy === 'revenue_expected')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                {{-- Cost Value — sortable --}}
                <th wire:click="sortColumn('cost_value')" class="sortable bx-hide-lg" style="text-align:right">
                    Cost Value
                    @if($sortBy === 'cost_value')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>
                @endif

                {{-- Restocked — sortable --}}
                <th wire:click="sortColumn('last_received')" class="sortable bx-hide-md">
                    Restocked
                    @if($sortBy === 'last_received')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                {{-- Expiry — sortable --}}
                <th wire:click="sortColumn('expiry_date')" class="sortable bx-hide-md">
                    Expiry
                    @if($sortBy === 'expiry_date')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                <th style="text-align:right">Action</th>
            </tr>
        </thead>
        <tbody>
            @php $colCount = $isOwner ? 10 : 7; @endphp
            @forelse($rows as $row)
            @php
                $fillPct   = $row->items_total > 0 ? round(($row->items_remaining / $row->items_total) * 100) : 0;
                $fillColor = $fillPct >= 60 ? 'var(--success,var(--green))'
                           : ($fillPct >= 20 ? 'var(--warn,var(--amber))' : 'var(--danger,var(--red))');

                $lastRestockedDays = $row->last_received_at ? (int) \Carbon\Carbon::parse($row->last_received_at)->diffInDays(now()) : null;
                $restockColor = $lastRestockedDays === null ? 'var(--text-dim)'
                               : ($lastRestockedDays <= 30 ? 'var(--success,var(--green))'
                               : ($lastRestockedDays <= 90 ? 'var(--warn,var(--amber))' : 'var(--danger,var(--red))'));

                $soonestExpiry = $row->soonest_expiry ? \Carbon\Carbon::parse($row->soonest_expiry) : null;
                $daysToExpiry  = $soonestExpiry ? (int) now()->diffInDays($soonestExpiry, false) : null;
                $expColor = $daysToExpiry === null ? null
                          : ($daysToExpiry <= 7 ? 'var(--danger,var(--red))'
                          : ($daysToExpiry <= 30 ? 'var(--warn,var(--amber))' : 'var(--success,var(--green))'));

                $isLowStock = $row->reorder_point > 0 && $row->items_remaining <= $row->reorder_point;
                $isExpanded = $expandedProductId === $row->product_id;
            @endphp
            <tr class="bx-row {{ $isExpanded ? 'is-open' : '' }}" wire:click="toggleExpand({{ $row->product_id }})" wire:key="prow-{{ $row->product_id }}">
                {{-- Product --}}
                <td class="td-2l">
                    <div class="td-2l-main">{{ $row->product_name }}</div>
                    <div style="display:flex;align-items:center;gap:6px;margin-top:2px;flex-wrap:wrap">
                        @if($row->category_name)
                        <span class="td-2l-badge" style="background:var(--accent-dim);color:var(--accent)">{{ $row->category_name }}</span>
                        @endif
                        @if($row->product_sku)
                        <span style="font-family:var(--mono);font-size:10px;color:var(--text-dim)">{{ $row->product_sku }}</span>
                        @endif
                        @if($isLowStock)
                        <span class="bx-badge-sm" style="background:var(--amber-dim);color:var(--amber)">Low stock</span>
                        @endif
                    </div>
                </td>

                {{-- Boxes — lifecycle composition --}}
                <td>
                    <div class="bx-mini-stats">
                        <div class="bx-mini-stat">
                            <div class="bx-mini-stat-v" style="color:var(--text)">{{ number_format($row->box_count) }}</div>
                            <div class="bx-mini-stat-l">Total</div>
                        </div>
                        @if($row->damaged_count > 0)
                        <div class="bx-mini-stat">
                            <div class="bx-mini-stat-v" style="color:var(--red)">{{ number_format($row->damaged_count) }}</div>
                            <div class="bx-mini-stat-l">Damaged</div>
                        </div>
                        @endif
                    </div>
                </td>

                {{-- Remaining — sellable boxes (full + partial), boxes-first --}}
                <td style="min-width:140px">
                    <div style="font-size:15px;font-weight:800;font-family:var(--mono);color:{{ $fillColor }};line-height:1">
                        {{ number_format($row->sellable_box_count) }} <span style="font-size:11px;font-weight:600;color:var(--text-dim)">{{ Str::plural('box', $row->sellable_box_count) }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;margin-top:5px">
                        <div style="flex:1;height:4px;background:var(--surface3);border-radius:3px;min-width:40px">
                            <div style="height:100%;width:{{ $fillPct }}%;background:{{ $fillColor }};border-radius:3px"></div>
                        </div>
                        <div style="font-size:10px;color:var(--text-dim);white-space:nowrap">
                            {{ number_format($row->items_remaining) }}/{{ number_format($row->items_total) }} items ({{ $fillPct }}%)
                        </div>
                    </div>
                </td>

                {{-- Sold — box count when the category is full-box-only
                     (every sale is a whole box, so it's an exact figure);
                     items otherwise, since individual sales don't divide
                     cleanly into boxes --}}
                <td>
                    @if($row->box_only_sales)
                        @php $soldBoxes = $row->items_per_box > 0 ? intdiv((int) $row->sold_qty, (int) $row->items_per_box) : 0; @endphp
                        <span style="font-family:var(--mono);font-size:13px;font-weight:700;color:var(--text)">{{ number_format($soldBoxes) }} <span style="font-size:11px;font-weight:600;color:var(--text-dim)">{{ Str::plural('box', $soldBoxes) }}</span></span>
                    @else
                        <span style="font-family:var(--mono);font-size:13px;font-weight:700;color:var(--text)">{{ number_format($row->sold_qty) }} <span style="font-size:11px;font-weight:600;color:var(--text-dim)">{{ Str::plural('item', (int) $row->sold_qty) }}</span></span>
                    @endif
                </td>

                @if($isOwner)
                {{-- Revenue Produced --}}
                <td class="bx-hide-lg" style="text-align:right;white-space:nowrap">
                    @if($row->revenue_produced > 0)
                    <span style="font-family:var(--mono);font-size:12px;font-weight:600;color:var(--green)">{{ number_format($row->revenue_produced) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                    @else
                    <span style="color:var(--text-dim);font-size:12px">—</span>
                    @endif
                </td>

                {{-- Revenue Expected --}}
                <td class="bx-hide-lg" style="text-align:right;white-space:nowrap">
                    @if($row->revenue_expected > 0)
                    <span style="font-family:var(--mono);font-size:12px;font-weight:600;color:var(--accent)">{{ number_format($row->revenue_expected) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                    @else
                    <span style="color:var(--text-dim);font-size:12px">—</span>
                    @endif
                </td>

                {{-- Cost Value --}}
                <td class="bx-hide-lg" style="text-align:right;white-space:nowrap">
                    @if($row->cost_value > 0)
                    <span style="font-family:var(--mono);font-size:12px;font-weight:600;color:var(--text)">{{ number_format($row->cost_value) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                    @else
                    <span style="color:var(--text-dim);font-size:12px">—</span>
                    @endif
                </td>
                @endif

                {{-- Restocked --}}
                <td class="bx-hide-md">
                    <span style="font-size:13px;font-weight:600;color:{{ $restockColor }}">
                        {{ $lastRestockedDays !== null ? $lastRestockedDays . 'd ago' : '—' }}
                    </span>
                </td>

                {{-- Expiry --}}
                <td class="bx-hide-md">
                    @if($soonestExpiry)
                        <span style="font-size:12px;font-weight:600;color:{{ $expColor }};white-space:nowrap">
                            {{ $soonestExpiry->format('d M Y') }}
                        </span>
                    @else
                        <span style="color:var(--text-dim);font-size:13px">—</span>
                    @endif
                </td>

                {{-- Action --}}
                <td style="text-align:right">
                    <button class="bx-view-btn {{ $isExpanded ? 'is-open' : '' }}" wire:click.stop="toggleExpand({{ $row->product_id }})">
                        View
                        <svg class="bx-caret" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </td>
            </tr>

            {{-- Drill-down: individual boxes for this product --}}
            @if($isExpanded)
            <tr class="bx-expand-row" wire:key="pexp-{{ $row->product_id }}">
                <td colspan="{{ $colCount }}">
                    <div class="bx-expand-inner">
                        <div class="bx-expand-title">Individual boxes — {{ $row->product_name }}</div>
                        @if($expandedBoxes->isEmpty())
                            <div class="bx-sub-empty">No boxes match the current filters for this product.</div>
                        @else
                        <div style="overflow-x:auto">
                        <table class="bx-sub-table">
                            <thead>
                                <tr>
                                    <th>Box Code</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Contents</th>
                                    <th>Age</th>
                                    <th>Expiry</th>
                                    <th>Batch</th>
                                    <th style="text-align:right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expandedBoxes as $box)
                                @php
                                    $bStatusColors = match($box->status->value) {
                                        'full'    => ['bg'=>'var(--green-dim,rgba(22,163,74,.12))',  'color'=>'var(--green)'],
                                        'partial' => ['bg'=>'var(--amber-dim,rgba(217,119,6,.12))', 'color'=>'var(--amber)'],
                                        'damaged' => ['bg'=>'var(--red-dim,rgba(220,38,38,.12))',    'color'=>'var(--red)'],
                                        default   => ['bg'=>'var(--surface2)',                       'color'=>'var(--text-dim)'],
                                    };
                                    $bFillPct  = $box->items_total > 0 ? round(($box->items_remaining / $box->items_total) * 100) : 0;
                                    $bAgeDays  = $box->received_at ? (int) $box->received_at->diffInDays(now()) : null;
                                @endphp
                                <tr wire:key="box-{{ $box->id }}">
                                    <td>
                                        <span class="bx-code-chip"
                                              style="background:{{ $bStatusColors['bg'] }};color:{{ $bStatusColors['color'] }}"
                                              wire:click="$dispatch('open-box-detail', {boxId: {{ $box->id }}})">
                                            {{ $box->box_code }}
                                        </span>
                                    </td>
                                    <td>{{ $box->location?->name ?? '—' }}</td>
                                    <td>
                                        <span class="bx-chip" style="background:{{ $bStatusColors['bg'] }};color:{{ $bStatusColors['color'] }}">
                                            {{ ucfirst($box->status->value) }}
                                        </span>
                                    </td>
                                    <td style="white-space:nowrap">{{ $box->items_remaining }} / {{ $box->items_total }} ({{ $bFillPct }}%)</td>
                                    <td>{{ $bAgeDays !== null ? $bAgeDays . 'd' : '—' }}</td>
                                    <td>{{ $box->expiry_date ? $box->expiry_date->format('d M Y') : '—' }}</td>
                                    <td style="font-family:var(--mono);color:var(--text-dim)">{{ $box->batch_number ?? '—' }}</td>
                                    <td style="text-align:right">
                                        <button class="bx-view-btn" wire:click="$dispatch('open-box-detail', {boxId: {{ $box->id }}})">View</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                        @endif
                    </div>
                </td>
            </tr>
            @endif

            @empty
            <tr>
                <td colspan="{{ $colCount }}">
                    <div class="bx-empty">
                        <div class="bx-empty-icon"><x-icon name="box" size="28" /></div>
                        <div class="bx-empty-title">
                            @if($anyFilterActive)
                                No products match your filters
                            @else
                                No boxes in the system yet
                            @endif
                        </div>
                        <div class="bx-empty-sub">
                            @if($anyFilterActive)
                                Try adjusting or clearing your filters
                            @else
                                Boxes will appear here once they are received into a warehouse or shop
                            @endif
                        </div>
                        @if($anyFilterActive)
                        <button wire:click="clearFilters" class="bx-empty-btn">Clear Filters</button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

</div>

{{-- Sticky summary bar --}}
<div style="position:sticky;bottom:0;z-index:20;
            background:var(--surface);border-top:2px solid var(--border);
            box-shadow:0 -4px 16px rgba(26,31,54,.08);
            padding:10px 16px;
            display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
    <span style="font-size:12px;color:var(--text-dim)">
        Showing
        <strong style="color:var(--text-sub);font-family:var(--mono)">{{ number_format($rows->count()) }}</strong>
        of
        <strong style="color:var(--text-sub);font-family:var(--mono)">{{ number_format($filteredCount) }}</strong>
        {{ $filteredCount === 1 ? 'product' : 'products' }}
        @if($anyFilterActive)
            <span style="font-weight:400"> matching filters</span>
        @endif
    </span>
    @if($isOwner && $filteredCostValue > 0)
    <span style="font-size:12px;color:var(--text-dim)">
        Total cost:
        <strong style="color:var(--text);font-family:var(--mono)">{{ number_format($filteredCostValue) }}</strong>
        <span style="font-size:10px">RWF</span>
    </span>
    @endif
</div>

{{-- Infinite scroll sentinel --}}
@if($hasMore)
<div wire:key="scroll-sentinel"
     x-data
     x-init="
         const obs = new IntersectionObserver(entries => {
             if (entries[0].isIntersecting) $wire.loadMore()
         }, { rootMargin: '300px' });
         obs.observe($el);
     "
     style="height:1px;margin-top:4px">
</div>
@else
<div style="padding:12px 0;text-align:center;font-size:12px;color:var(--text-dim)">
    @if($filteredCount > 25) All {{ number_format($filteredCount) }} products loaded @endif
</div>
@endif

{{-- ── Box detail drawer ──────────────────────────────────────── --}}
<livewire:inventory.boxes.box-detail />

</div>
