{{-- ┌─────────────────────────────────────────────────────────────────────────┐
    │  Owner · Inventory Report                                               │
    │  Tabs: Overview · Valuation · Stock Health · Replenishment             │
    │  Design system: var(--*) tokens, .bkpi pattern                        │
    └─────────────────────────────────────────────────────────────────────────┘ --}}
<div>
<style>
.iv-page-title { font-size:24px;font-weight:700;color:var(--text);letter-spacing:-0.5px;margin:0 0 4px }
.iv-page-subtitle { font-size:13px;color:var(--text-dim);font-family:var(--mono) }
.iv-section-title { font-size:15px;font-weight:700;color:var(--text);margin:0 0 14px }
.iv-section-sub { font-size:12px;color:var(--text-dim);margin:-10px 0 14px }
.iv-table { width:100%;border-collapse:collapse }
.iv-table thead th { font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;padding:10px 12px;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap }
.iv-table thead th.r { text-align:right }
.iv-table tbody td { font-size:13px;color:var(--text);padding:10px 12px;border-bottom:1px solid var(--border);vertical-align:middle;white-space:nowrap }
.iv-table tbody td.r { text-align:right }
.iv-table tbody tr:last-child td { border-bottom:none }
.iv-table tfoot td { font-size:13px;font-weight:700;color:var(--text);padding:10px 12px;border-top:1px solid var(--border);white-space:nowrap }
.iv-table tfoot td.r { text-align:right }
.iv-card { background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);padding:20px }
.iv-two-col { display:grid;grid-template-columns:1fr 1fr;gap:20px }
.iv-stat-block { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:16px 20px }
.iv-abc-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px }
.iv-badge { display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.3px }
.iv-chip { display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700 }
.iv-table-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch }
/* Period filter card */
.iv-filters  { background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);margin-bottom:20px;min-width:0;max-width:100% }
.iv-presets  { display:flex;gap:4px;overflow-x:auto;-webkit-overflow-scrolling:touch;padding:12px 16px;border-bottom:1px solid var(--border);scrollbar-width:none;flex-wrap:nowrap }
.iv-presets::-webkit-scrollbar { display:none }
.iv-preset   { padding:6px 14px;border-radius:7px;font-size:12px;font-weight:600;border:1px solid transparent;background:transparent;color:var(--text-dim);cursor:pointer;white-space:nowrap;flex-shrink:0;font-family:var(--font);transition:all var(--tr) }
.iv-preset:hover  { background:var(--surface2);color:var(--text);border-color:var(--border) }
.iv-preset.active { background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 2px 8px var(--accent-glow) }
.iv-controls  { display:flex;align-items:center;gap:0;flex-wrap:wrap }
.iv-ctrl-seg  { display:flex;align-items:center;gap:8px;padding:10px 16px;border-right:1px solid var(--border);flex-shrink:0 }
.iv-ctrl-seg:last-child { border-right:none }
.iv-ctrl-grow { flex:1;min-width:0 }
.iv-date-in   { background:transparent;border:none;outline:none;font-size:14px;font-weight:600;font-family:var(--mono);color:var(--text);width:110px;cursor:pointer;min-width:0 }
.iv-loc-sel   { background:transparent;border:none;outline:none;font-size:14px;font-weight:600;font-family:var(--font);color:var(--text);cursor:pointer;max-width:200px }
/* Table sort helpers */
.iv-sort-th { cursor:pointer;user-select:none;white-space:nowrap }
.iv-sort-th:hover { color:var(--accent) }
.iv-sort-arrow { display:inline-block;margin-left:4px;font-size:10px;opacity:.5 }
.iv-sort-th.active .iv-sort-arrow { opacity:1;color:var(--accent) }
/* KPI cards — mirrors sa-kpi-* */
.iv-kpis      { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px }
.iv-kpi       { background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card);padding:22px 20px;display:flex;flex-direction:column;gap:16px;transition:box-shadow var(--tr) }
.iv-kpi:hover { box-shadow:var(--shadow-card-hover) }
.iv-kpi-row   { display:flex;align-items:center;gap:12px }
.iv-kpi-icon  { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.iv-kpi-body  { flex:1;min-width:0 }
.iv-kpi-label { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);line-height:1.2 }
.iv-kpi-sub   { font-size:12px;color:var(--text-dim);margin-top:2px }
.iv-kpi-val   { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;line-height:1 }
.iv-kpi-bar   { height:3px;border-radius:3px }
.iv-kpi-divider { height:1px;background:var(--border) }
.iv-kpi-footer  { display:flex;flex-direction:column;gap:0 }
.iv-kpi-stat    { display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid var(--border);min-width:0 }
.iv-kpi-stat:last-child { border-bottom:none }
.iv-kpi-stat-v  { font-size:13px;font-weight:700;font-family:var(--mono);letter-spacing:-0.3px;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap }
.iv-kpi-stat-l  { font-size:11px;color:var(--text-dim);flex-shrink:0;margin-right:8px }
/* Full-width tab strip — mirrors sa-tabs */
.iv-tabs { display:grid;grid-template-columns:repeat(4,1fr);background:var(--surface);box-shadow:var(--shadow-card);border-radius:var(--r);margin-bottom:24px;overflow:hidden }
.iv-tab  { display:flex;align-items:center;justify-content:center;gap:6px;padding:12px 10px;border:none;border-radius:0;border-bottom:2.5px solid transparent;border-right:1px solid var(--border);cursor:pointer;font-size:12px;font-weight:600;font-family:var(--font);background:transparent;color:var(--text-dim);transition:all var(--tr);white-space:nowrap }
.iv-tab:last-child { border-right:none }
.iv-tab:hover  { background:var(--surface2);color:var(--text);border-bottom-color:var(--border-hi) }
.iv-tab.active { background:var(--accent-dim);color:var(--accent);border-bottom-color:var(--accent) }
@@media(max-width:640px) {
    .iv-tabs { display:flex;overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;flex-wrap:nowrap;border-radius:var(--r) }
    .iv-tabs::-webkit-scrollbar { display:none }
    .iv-tab  { flex-shrink:0;min-width:100px;padding:11px 14px;font-size:12px;border-radius:0 }
    .iv-tab svg { display:none }
}
/* Inventory alert strip (Overview tab) */
.iv-alert-strip { display:grid;grid-template-columns:repeat(4,1fr);background:var(--surface);box-shadow:var(--shadow-card);border-radius:var(--r);overflow:hidden;margin-bottom:24px }
.iv-alert-cell  { padding:16px 18px;border-right:1px solid var(--border);cursor:pointer;transition:background var(--tr) }
.iv-alert-cell:last-child { border-right:none }
.iv-alert-cell:hover { background:var(--surface2) }
.iv-alert-lbl   { font-size:10px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px }
.iv-alert-val   { font-size:22px;font-weight:800;font-family:var(--mono);letter-spacing:-0.5px;line-height:1 }
.iv-alert-sub   { font-size:11px;color:var(--text-dim);margin-top:4px }
/* ABC Classification alpine togglers */
.iv-abc-toggle { display:flex;align-items:center;gap:10px;padding:12px 16px;cursor:pointer;border-top:1px solid var(--border);transition:background var(--tr) }
.iv-abc-toggle:first-of-type { border-top:none }
.iv-abc-toggle:hover { background:var(--surface2) }
.iv-abc-chevron { margin-left:auto;flex-shrink:0;color:var(--text-dim);transition:transform .2s }
.iv-abc-chevron.rotated { transform:rotate(180deg) }
/* Replenishment 3-cell strip */
.iv-repl-strip { display:grid;grid-template-columns:repeat(3,1fr);background:var(--surface);box-shadow:var(--shadow-card);border-radius:var(--r);overflow:hidden;margin-bottom:16px }

@@media(max-width:900px) {
    .iv-two-col  { grid-template-columns:1fr !important }
    .iv-abc-grid { grid-template-columns:1fr 1fr !important }
}
@@media(max-width:768px) {
    .iv-kpis    { grid-template-columns:1fr 1fr;gap:10px }
    .iv-kpi     { padding:14px }
    .iv-kpi-val { font-size:20px }
    .iv-presets { padding:10px 12px }
    .iv-preset  { padding:5px 10px;font-size:11px }
    .iv-alert-strip { grid-template-columns:1fr 1fr }
    .iv-alert-cell:nth-child(2) { border-right:none }
    .iv-alert-cell:nth-child(3) { border-top:1px solid var(--border) }
    .iv-alert-cell:nth-child(4) { border-top:1px solid var(--border);border-right:none }
    .iv-repl-strip { grid-template-columns:1fr 1fr }
    .iv-repl-strip .iv-alert-cell:nth-child(2) { border-right:none }
    .iv-repl-strip .iv-alert-cell:nth-child(3) { border-top:1px solid var(--border);grid-column:1/-1 }
    /* Stack filter controls vertically so date inputs don't overflow */
    .iv-controls  { flex-direction:column;align-items:stretch }
    .iv-ctrl-seg  { border-right:none;border-bottom:1px solid var(--border);flex-wrap:wrap }
    .iv-ctrl-seg:last-child { border-bottom:none }
    .iv-ctrl-grow { flex:1 1 auto }
    .iv-date-in   { flex:1;width:auto;min-width:80px;max-width:none }
    .iv-loc-sel   { max-width:none;width:100% }
}
@@media(max-width:600px) {
    .iv-kpis    { grid-template-columns:1fr 1fr;gap:8px }
    .iv-kpi     { padding:14px 12px }
    .iv-kpi-val { font-size:18px }
    .iv-alert-strip { grid-template-columns:1fr 1fr }
}
@@media(max-width:480px) {
    .iv-kpis    { grid-template-columns:1fr }
    .iv-kpi-stat-v { font-size:12px }
    .iv-kpi-stat-l { font-size:10px }
    .iv-abc-grid { grid-template-columns:1fr !important }
    .iv-alert-strip { grid-template-columns:1fr }
    .iv-alert-cell  { border-right:none;border-bottom:1px solid var(--border) }
    .iv-alert-cell:last-child { border-bottom:none }
    .iv-repl-strip { grid-template-columns:1fr }
    .iv-repl-strip .iv-alert-cell { grid-column:auto }
    .iv-card     { padding:14px }
    .iv-ctrl-seg:last-child { display:none }
}
</style>

@php
    $isOwner     = auth()->user()->isOwner();
    $lookbackDays = max(1, \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1);
    $isWarehouse = str_starts_with($locationFilter, 'warehouse:');

    // Period preset detection
    $ivPeriods = ['week'=>'This Week','month'=>'This Month','quarter'=>'This Quarter','year'=>'This Year','last_30'=>'Last 30 Days','last_90'=>'Last 90 Days'];
    $ivStarts  = ['week'=>now()->startOfWeek()->toDateString(),'month'=>now()->startOfMonth()->toDateString(),'quarter'=>now()->startOfQuarter()->toDateString(),'year'=>now()->startOfYear()->toDateString(),'last_30'=>now()->subDays(29)->toDateString(),'last_90'=>now()->subDays(89)->toDateString()];
    $currentPeriod = 'custom';
    foreach ($ivStarts as $k => $s) { if ($dateFrom === $s && $dateTo === now()->toDateString()) { $currentPeriod = $k; break; } }

    // KPI data
    $kpis      = $this->inventoryKpis;
    $fillRate  = $this->portfolioFillRate;
    $shrinkage = $this->shrinkageStats;

    $cfmt = function (int $v): string {
        if ($v >= 1_000_000_000) return number_format($v / 1_000_000_000, 1) . 'B';
        if ($v >= 1_000_000)     return number_format($v / 1_000_000, 1) . 'M';
        if ($v >= 1_000)         return number_format($v / 1_000, 0) . 'K';
        return (string) $v;
    };

    $fillColor = match(true) {
        $fillRate === null   => 'var(--text-dim)',
        $fillRate >= 70      => 'var(--success)',
        $fillRate >= 40      => 'var(--amber)',
        default              => 'var(--red)',
    };
    $shrinkColor = match(true) {
        $shrinkage['shrinkage_pct'] > 2    => 'var(--red)',
        $shrinkage['shrinkage_pct'] >= 0.5 => 'var(--amber)',
        default                             => 'var(--success)',
    };
    $marginPct = $kpis['retail_value'] > 0
        ? round(($kpis['potential_profit'] / $kpis['retail_value']) * 100, 1)
        : 0;
@endphp

{{-- ══════════════════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════════════════ --}}
<div style="margin-bottom:16px">
    <h1 class="iv-page-title">Inventory Report</h1>
    <div class="iv-page-subtitle">Valuation · stock health · velocity · replenishment</div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     HEADLINE KPI ROW (always visible)
══════════════════════════════════════════════════════════════════════════ --}}
<div class="iv-kpis">

    {{-- Card 1: Cost Value --}}
    <div class="iv-kpi">
        <div class="iv-kpi-row">
            <div class="iv-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
            <div class="iv-kpi-body">
                <div class="iv-kpi-label">Cost Value</div>
                <div class="iv-kpi-sub">RWF · capital invested</div>
            </div>
        </div>
        <div class="iv-kpi-val" style="color:var(--text)">{{ number_format($kpis['purchase_value']) }}</div>
        <div class="iv-kpi-divider"></div>
        <div class="iv-kpi-footer">
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">Retail Value</span>
                <span class="iv-kpi-stat-v" style="color:var(--text-sub)">{{ number_format($kpis['retail_value']) }}</span>
            </div>
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">Margin</span>
                <span class="iv-kpi-stat-v" style="color:var(--text-sub)">{{ number_format($kpis['potential_profit']) }}</span>
            </div>
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">Return %</span>
                <span class="iv-kpi-stat-v" style="color:{{ $marginPct >= 20 ? 'var(--green)' : ($marginPct >= 10 ? 'var(--amber)' : 'var(--red)') }}">{{ $marginPct }}%</span>
            </div>
        </div>
    </div>

    {{-- Card 2: Fill Rate --}}
    <div class="iv-kpi">
        <div class="iv-kpi-row">
            <div class="iv-kpi-icon" style="background:color-mix(in srgb, {{ $fillColor }} 12%, transparent);color:{{ $fillColor }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            </div>
            <div class="iv-kpi-body">
                <div class="iv-kpi-label">Fill Rate</div>
                <div class="iv-kpi-sub">@if($fillRate === null) No stock @else items vs capacity @endif</div>
            </div>
        </div>
        <div class="iv-kpi-val" style="color:{{ $fillColor }}">
            @if($fillRate !== null) {{ $fillRate }}% @else — @endif
        </div>
        <div class="iv-kpi-divider"></div>
        <div class="iv-kpi-footer">
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">Full boxes</span>
                <span class="iv-kpi-stat-v" style="color:var(--success)">{{ number_format($kpis['box_full_count']) }}</span>
            </div>
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">Partial boxes</span>
                <span class="iv-kpi-stat-v" style="color:var(--amber)">{{ number_format($kpis['box_partial_count']) }}</span>
            </div>
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">Damaged</span>
                <span class="iv-kpi-stat-v" style="color:var(--red)">{{ number_format($kpis['box_damaged_count']) }}</span>
            </div>
        </div>
    </div>

    {{-- Card 3: Stock Turnover --}}
    <div class="iv-kpi">
        <div class="iv-kpi-row">
            <div class="iv-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
            </div>
            <div class="iv-kpi-body">
                <div class="iv-kpi-label">Stock Turnover</div>
                <div class="iv-kpi-sub">@if($isWarehouse) N/A for warehouses @else annual COGS ÷ inventory @endif</div>
            </div>
        </div>
        <div class="iv-kpi-val" style="color:var(--violet)">
            @if($isWarehouse) — @else {{ number_format($kpis['turnover_rate'], 2) }}× @endif
        </div>
        <div class="iv-kpi-divider"></div>
        <div class="iv-kpi-footer">
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">Products tracked</span>
                <span class="iv-kpi-stat-v" style="color:var(--text-sub)">{{ number_format($kpis['product_count']) }}</span>
            </div>
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">COGS window</span>
                <span class="iv-kpi-stat-v" style="color:var(--text-dim)">365 days</span>
            </div>
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">Annual rate</span>
                <span class="iv-kpi-stat-v" style="color:var(--violet)">{{ $isWarehouse ? '—' : number_format($kpis['turnover_rate'], 2).'×' }}</span>
            </div>
        </div>
    </div>

    {{-- Card 4: Shrinkage Rate --}}
    <div class="iv-kpi">
        <div class="iv-kpi-row">
            <div class="iv-kpi-icon" style="background:color-mix(in srgb, {{ $shrinkColor }} 12%, transparent);color:{{ $shrinkColor }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="iv-kpi-body">
                <div class="iv-kpi-label">Shrinkage Rate</div>
                <div class="iv-kpi-sub">{{ $lookbackDays }}d window</div>
            </div>
        </div>
        <div class="iv-kpi-val" style="color:{{ $shrinkColor }}">{{ $shrinkage['shrinkage_pct'] }}%</div>
        <div class="iv-kpi-divider"></div>
        <div class="iv-kpi-footer">
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">Units damaged</span>
                <span class="iv-kpi-stat-v" style="color:var(--text-sub)">{{ number_format($shrinkage['items_damaged_90d']) }}</span>
            </div>
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">Est. loss (RWF)</span>
                <span class="iv-kpi-stat-v" style="color:{{ $shrinkColor }}">{{ number_format($shrinkage['estimated_loss']) }}</span>
            </div>
            <div class="iv-kpi-stat">
                <span class="iv-kpi-stat-l">Period</span>
                <span class="iv-kpi-stat-v" style="color:var(--text-dim)">{{ $lookbackDays }} days</span>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     FILTER BAR
══════════════════════════════════════════════════════════════════════════ --}}
<div class="iv-filters">
    <div class="iv-presets">
        @foreach($ivPeriods as $key => $label)
        <button type="button" wire:key="iv-preset-{{ $key }}" wire:click="setDateRange('{{ $key }}')"
                class="iv-preset {{ $currentPeriod === $key ? 'active' : '' }}">{{ $label }}</button>
        @endforeach
    </div>
    <div class="iv-controls">
        <div class="iv-ctrl-seg iv-ctrl-grow">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" style="flex-shrink:0;color:var(--text-dim)">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <input type="date" wire:model="dateFrom" max="{{ $dateTo }}" class="iv-date-in">
            <span style="font-size:13px;color:var(--text-dim);flex-shrink:0">→</span>
            <input type="date" wire:model="dateTo" min="{{ $dateFrom }}" max="{{ now()->toDateString() }}" class="iv-date-in">
        </div>
        <div class="iv-ctrl-seg">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" style="color:var(--text-dim);flex-shrink:0">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                <circle cx="12" cy="10" r="3"/>
            </svg>
            <select wire:model.live="locationFilter" class="iv-loc-sel">
                <option value="all">All Locations</option>
                <option value="warehouses">All Warehouses</option>
                <option value="shops">All Shops</option>
                @foreach($this->warehouses as $wh)
                    <option value="warehouse:{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
                @foreach($this->shops as $shop)
                    <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="iv-ctrl-seg" style="gap:6px">
            <span style="width:7px;height:7px;border-radius:50%;background:var(--green);flex-shrink:0"></span>
            <span style="font-size:12px;color:var(--text-dim)">{{ $lookbackDays }}d window</span>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB BAR — full-width grid
══════════════════════════════════════════════════════════════════════════ --}}
@php
    $tabs = [
        'overview'      => ['label' => 'Overview',      'icon' => 'M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 0h7v7h-7z'],
        'valuation'     => ['label' => 'Valuation',     'icon' => 'M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6'],
        'health'        => ['label' => 'Stock Health',  'icon' => 'M22 12h-4l-3 9L9 3l-3 9H2'],
        'replenishment' => ['label' => 'Replenishment', 'icon' => 'M4 4h16v16H4zM4 10h16M10 4v16'],
    ];
@endphp
<div class="iv-tabs">
    @foreach($tabs as $key => $tab)
    <button type="button" wire:click="setTab('{{ $key }}')"
            class="iv-tab {{ $activeTab === $key ? 'active' : '' }}">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="{{ $tab['icon'] }}"/>
        </svg>
        <span class="iv-tab-lbl">{{ $tab['label'] }}</span>
    </button>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: OVERVIEW
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'overview')

    {{-- ── Inventory Alert Strip ────────────────────────────────────────── --}}
    @php
        $vel = $this->velocityClassification;
        $vs  = $vel['summary'] ?? [];
        $criticalCount = collect($this->daysOnHandPerProduct)
            ->filter(fn($p) => isset($p['days_on_hand']) && $p['days_on_hand'] !== null && $p['days_on_hand'] <= 7)
            ->count();
        $deadCapital   = (int) ($vs['Dead_cost_value'] ?? 0);
        $expiringCount = count($this->expiringStock);
        $shrinkageLoss = (int) ($shrinkage['estimated_loss'] ?? 0);
    @endphp
    <div class="iv-alert-strip">
        <div class="iv-alert-cell" wire:click="setTab('replenishment')">
            <div class="iv-alert-lbl">Critical Reorder</div>
            <div class="iv-alert-val" style="color:{{ $criticalCount > 0 ? 'var(--red)' : 'var(--success)' }}">{{ $criticalCount }}</div>
            <div class="iv-alert-sub">{{ $criticalCount === 1 ? 'product' : 'products' }} ≤ 7d stock · <span style="color:var(--accent)">view →</span></div>
        </div>
        <div class="iv-alert-cell" wire:click="setTab('replenishment')">
            <div class="iv-alert-lbl">Dead Capital</div>
            <div class="iv-alert-val" style="color:{{ $deadCapital > 0 ? 'var(--red)' : 'var(--success)' }}">{{ $cfmt($deadCapital) }}</div>
            <div class="iv-alert-sub">RWF locked in dead stock · <span style="color:var(--accent)">view →</span></div>
        </div>
        <div class="iv-alert-cell" wire:click="setTab('health')">
            <div class="iv-alert-lbl">Expiring Soon</div>
            <div class="iv-alert-val" style="color:{{ $expiringCount > 0 ? 'var(--amber)' : 'var(--success)' }}">{{ $expiringCount }}</div>
            <div class="iv-alert-sub">{{ $expiringCount === 1 ? 'product' : 'products' }} within 30d · <span style="color:var(--accent)">view →</span></div>
        </div>
        <div class="iv-alert-cell" wire:click="setTab('health')">
            <div class="iv-alert-lbl">Shrinkage Loss</div>
            <div class="iv-alert-val" style="color:{{ $shrinkageLoss > 0 ? 'var(--amber)' : 'var(--success)' }}">{{ $cfmt($shrinkageLoss) }}</div>
            <div class="iv-alert-sub">RWF est. loss · {{ $lookbackDays }}d window · <span style="color:var(--accent)">view →</span></div>
        </div>
    </div>

    {{-- ── Movement Trend Chart ──────────────────────────────────────────── --}}
    <div class="iv-card" style="margin-bottom:20px">
        <div class="iv-section-title">Stock Movement — {{ $dateFrom }} → {{ $dateTo }}</div>
        <div class="iv-section-sub">Boxes received vs items consumed per week</div>
        <div id="inv-mov-chart-wrap"
             style="position:relative;min-height:280px"
             data-chart='@json($this->inventoryMovementTrend)'></div>
    </div>

    {{-- ── Category Concentration ───────────────────────────────────────── --}}
    <div class="iv-card">
        <div class="iv-section-title">Category Concentration</div>
        <div class="iv-table-scroll">
            <table class="iv-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="r">Products</th>
                        <th class="r">Boxes</th>
                        <th class="r">Cost Value</th>
                        <th style="min-width:180px">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->categoryConcentration as $i => $cat)
                        <tr>
                            <td style="font-weight:600">{{ $cat['category_name'] }}</td>
                            <td class="r" style="color:var(--text-dim)">{{ $cat['product_count'] }}</td>
                            <td class="r">{{ number_format($cat['box_count']) }}</td>
                            <td class="r" style="font-family:var(--mono)">{{ number_format($cat['cost_value']) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div style="flex:1;height:6px;background:var(--surface2);border-radius:3px;overflow:hidden">
                                        <div style="height:100%;width:{{ $cat['pct_of_total'] }}%;background:var(--accent);opacity:{{ max(0.3, 1 - $i * 0.12) }};border-radius:3px"></div>
                                    </div>
                                    <span style="font-size:12px;color:var(--text-sub);min-width:36px;text-align:right">{{ $cat['pct_of_total'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--text-dim);padding:24px">No category data available</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: VALUATION
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'valuation')

    {{-- ── Value by Location ────────────────────────────────────────────── --}}
    @php $byLoc = $this->inventoryByLocation; @endphp
    <div style="margin-bottom:24px">
        <div class="iv-section-title">Value by Location</div>
        <div class="iv-two-col">

            {{-- Warehouses --}}
            <div class="iv-card">
                <div style="font-size:13px;font-weight:700;color:var(--text-sub);margin-bottom:12px;display:flex;align-items:center;gap:6px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Warehouses
                </div>
                <div class="iv-table-scroll">
                    <table class="iv-table">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th class="r">Boxes</th>
                                <th class="r">Cost Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byLoc['warehouses'] as $row)
                                <tr>
                                    <td>{{ $row['location_name'] }}</td>
                                    <td class="r">{{ number_format($row['box_count']) }}</td>
                                    <td class="r" style="font-family:var(--mono)">{{ number_format($row['value']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center;color:var(--text-dim)">No warehouse stock</td></tr>
                            @endforelse
                        </tbody>
                        @if(count($byLoc['warehouses']) > 1)
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td class="r">{{ number_format(collect($byLoc['warehouses'])->sum('box_count')) }}</td>
                                <td class="r" style="font-family:var(--mono)">{{ number_format(collect($byLoc['warehouses'])->sum('value')) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Shops --}}
            <div class="iv-card">
                <div style="font-size:13px;font-weight:700;color:var(--text-sub);margin-bottom:12px;display:flex;align-items:center;gap:6px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    Shops
                </div>
                <div class="iv-table-scroll">
                    <table class="iv-table">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th class="r">Boxes</th>
                                <th class="r">Cost Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byLoc['shops'] as $row)
                                <tr>
                                    <td>{{ $row['location_name'] }}</td>
                                    <td class="r">{{ number_format($row['box_count']) }}</td>
                                    <td class="r" style="font-family:var(--mono)">{{ number_format($row['value']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center;color:var(--text-dim)">No shop stock</td></tr>
                            @endforelse
                        </tbody>
                        @if(count($byLoc['shops']) > 1)
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td class="r">{{ number_format(collect($byLoc['shops'])->sum('box_count')) }}</td>
                                <td class="r" style="font-family:var(--mono)">{{ number_format(collect($byLoc['shops'])->sum('value')) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Top 20 Products by Capital Value ────────────────────────────── --}}
    @php
        $topProds = $this->topProductsByValue;
        $maxVal   = collect($topProds)->max('purchase_value') ?: 1;
        $vSortArrow = fn($col) => $valSortBy === $col ? ($valSortDir === 'asc' ? '↑' : '↓') : '↕';
    @endphp
    <div class="iv-card">
        <div class="iv-section-title">Top 20 Products by Capital Value</div>
        <div class="iv-table-scroll">
            <table class="iv-table">
                <thead>
                    <tr>
                        <th style="width:36px">#</th>
                        <th wire:click="sortValuation('product_name')" class="iv-sort-th {{ $valSortBy === 'product_name' ? 'active' : '' }}">
                            Product <span class="iv-sort-arrow">{{ $vSortArrow('product_name') }}</span>
                        </th>
                        <th wire:click="sortValuation('box_count')" class="iv-sort-th r {{ $valSortBy === 'box_count' ? 'active' : '' }}">
                            Boxes <span class="iv-sort-arrow">{{ $vSortArrow('box_count') }}</span>
                        </th>
                        <th wire:click="sortValuation('purchase_value')" class="iv-sort-th r {{ $valSortBy === 'purchase_value' ? 'active' : '' }}">
                            Cost Value <span class="iv-sort-arrow">{{ $vSortArrow('purchase_value') }}</span>
                        </th>
                        <th wire:click="sortValuation('retail_value')" class="iv-sort-th r {{ $valSortBy === 'retail_value' ? 'active' : '' }}">
                            Retail Value <span class="iv-sort-arrow">{{ $vSortArrow('retail_value') }}</span>
                        </th>
                        <th class="r">Locations</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProds as $i => $p)
                        <tr style="{{ $i < 3 ? 'background:color-mix(in srgb, var(--accent) 6%, transparent)' : '' }}">
                            <td style="color:var(--text-dim);font-size:12px;font-family:var(--mono)">{{ $i + 1 }}</td>
                            <td>
                                <div style="font-weight:600">{{ $p['product_name'] }}</div>
                                <div style="margin-top:4px;height:3px;background:var(--surface2);border-radius:2px;max-width:160px;overflow:hidden">
                                    <div style="height:100%;width:{{ round($p['purchase_value'] / $maxVal * 100) }}%;background:var(--accent);border-radius:2px"></div>
                                </div>
                            </td>
                            <td class="r">{{ number_format($p['box_count']) }}</td>
                            <td class="r" style="font-family:var(--mono)">{{ number_format($p['purchase_value']) }}</td>
                            <td class="r" style="font-family:var(--mono);color:var(--text-dim)">{{ number_format($p['retail_value']) }}</td>
                            <td class="r" style="color:var(--text-dim)">{{ $p['location_count'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--text-dim);padding:24px">No products with stock found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: STOCK HEALTH
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'health')

    @php
        $health   = $this->stockHealth;
        $aging    = $this->agingAnalysis;
        $expiring = $this->expiringStock;
        $vel      = $this->velocityClassification;
        $vs       = $vel['summary'] ?? [];

        $agingOrder  = ['0-30 days', '31-60 days', '61-90 days', '90+ days'];
        $agingColors = [
            '0-30 days'  => 'var(--success)',
            '31-60 days' => 'var(--accent)',
            '61-90 days' => 'var(--amber)',
            '90+ days'   => 'var(--red)',
        ];
        $agingKeyed = collect($aging)->keyBy('age_bracket');
        $agingTotal = collect($aging)->sum('box_count') ?: 1;
    @endphp

    {{-- ── Health Summary Cards ─────────────────────────────────────────── --}}
    @php
        $expiringColor  = count($expiring) > 0 ? 'var(--red)' : 'var(--success)';
        $expiringCost   = collect($expiring)->sum('value');
        $deadColor      = ($health['dead_stock_count'] ?? 0) > 0 ? 'var(--red)' : 'var(--success)';
        $deadLocked     = (int) ($vs['Dead_cost_value'] ?? 0);
        $damagedColor   = $shrinkage['items_damaged_90d'] > 0 ? 'var(--amber)' : 'var(--success)';
    @endphp
    <div class="iv-kpis" style="margin-bottom:24px">

        {{-- Low Stock --}}
        <div class="iv-kpi">
            <div class="iv-kpi-row">
                <div class="iv-kpi-icon" style="background:color-mix(in srgb,var(--amber) 12%,transparent);color:var(--amber)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <div class="iv-kpi-body">
                    <div class="iv-kpi-label">Low Stock</div>
                    <div class="iv-kpi-sub">products below threshold</div>
                </div>
            </div>
            <div class="iv-kpi-val" style="color:{{ ($health['low_stock_count'] ?? 0) > 0 ? 'var(--amber)' : 'var(--success)' }}">
                {{ number_format($health['low_stock_count'] ?? 0) }}
            </div>
            <div class="iv-kpi-divider"></div>
            <div class="iv-kpi-footer">
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Products at risk</span>
                    <span class="iv-kpi-stat-v" style="color:var(--amber)">{{ number_format($health['low_stock_count'] ?? 0) }}</span>
                </div>
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Tracking basis</span>
                    <span class="iv-kpi-stat-v" style="color:var(--text-dim)">item threshold</span>
                </div>
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Scope</span>
                    <span class="iv-kpi-stat-v" style="color:var(--text-dim)">all locations</span>
                </div>
            </div>
        </div>

        {{-- Dead Stock --}}
        <div class="iv-kpi">
            <div class="iv-kpi-row">
                <div class="iv-kpi-icon" style="background:color-mix(in srgb,{{ $deadColor }} 12%,transparent);color:{{ $deadColor }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                </div>
                <div class="iv-kpi-body">
                    <div class="iv-kpi-label">Dead Stock</div>
                    <div class="iv-kpi-sub">no sales in 90 days</div>
                </div>
            </div>
            <div class="iv-kpi-val" style="color:{{ $deadColor }}">
                {{ number_format($health['dead_stock_count'] ?? 0) }}
            </div>
            <div class="iv-kpi-divider"></div>
            <div class="iv-kpi-footer">
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Products affected</span>
                    <span class="iv-kpi-stat-v" style="color:{{ $deadColor }}">{{ number_format($health['dead_stock_count'] ?? 0) }}</span>
                </div>
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Capital locked (RWF)</span>
                    <span class="iv-kpi-stat-v" style="color:var(--red)">{{ number_format($deadLocked) }}</span>
                </div>
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Action</span>
                    <span class="iv-kpi-stat-v" style="color:var(--accent);cursor:pointer" wire:click="setTab('replenishment')">review →</span>
                </div>
            </div>
        </div>

        {{-- Expiring Soon --}}
        <div class="iv-kpi">
            <div class="iv-kpi-row">
                <div class="iv-kpi-icon" style="background:color-mix(in srgb,{{ $expiringColor }} 12%,transparent);color:{{ $expiringColor }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="iv-kpi-body">
                    <div class="iv-kpi-label">Expiring Soon</div>
                    <div class="iv-kpi-sub">batches within 30 days</div>
                </div>
            </div>
            <div class="iv-kpi-val" style="color:{{ $expiringColor }}">
                {{ count($expiring) }}
            </div>
            <div class="iv-kpi-divider"></div>
            <div class="iv-kpi-footer">
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Batches expiring</span>
                    <span class="iv-kpi-stat-v" style="color:{{ $expiringColor }}">{{ count($expiring) }}</span>
                </div>
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Cost at risk (RWF)</span>
                    <span class="iv-kpi-stat-v" style="color:{{ $expiringColor }}">{{ number_format($expiringCost) }}</span>
                </div>
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Window</span>
                    <span class="iv-kpi-stat-v" style="color:var(--text-dim)">30 days</span>
                </div>
            </div>
        </div>

        {{-- Shrinkage --}}
        <div class="iv-kpi">
            <div class="iv-kpi-row">
                <div class="iv-kpi-icon" style="background:color-mix(in srgb,{{ $damagedColor }} 12%,transparent);color:{{ $damagedColor }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <div class="iv-kpi-body">
                    <div class="iv-kpi-label">Shrinkage</div>
                    <div class="iv-kpi-sub">{{ $lookbackDays }}d window</div>
                </div>
            </div>
            <div class="iv-kpi-val" style="color:{{ $damagedColor }}">{{ $shrinkage['shrinkage_pct'] }}%</div>
            <div class="iv-kpi-divider"></div>
            <div class="iv-kpi-footer">
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Units damaged</span>
                    <span class="iv-kpi-stat-v" style="color:{{ $damagedColor }}">{{ number_format($shrinkage['items_damaged_90d']) }}</span>
                </div>
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Est. loss (RWF)</span>
                    <span class="iv-kpi-stat-v" style="color:{{ $damagedColor }}">{{ number_format($shrinkage['estimated_loss']) }}</span>
                </div>
                <div class="iv-kpi-stat">
                    <span class="iv-kpi-stat-l">Period</span>
                    <span class="iv-kpi-stat-v" style="color:var(--text-dim)">{{ $lookbackDays }} days</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Stock Aging ──────────────────────────────────────────────────── --}}
    <div class="iv-card" style="margin-bottom:20px">
        <div class="iv-section-title">Stock Aging</div>
        {{-- Stacked bar --}}
        <div style="height:20px;border-radius:6px;overflow:hidden;display:flex;margin-bottom:10px">
            @foreach($agingOrder as $bracket)
                @php
                    $bData = $agingKeyed->get($bracket);
                    $pct   = $bData ? round($bData['box_count'] / $agingTotal * 100) : 0;
                @endphp
                @if($pct > 0)
                <div style="width:{{ $pct }}%;background:{{ $agingColors[$bracket] }}" title="{{ $bracket }}: {{ $pct }}%"></div>
                @endif
            @endforeach
        </div>
        {{-- Legend --}}
        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px">
            @foreach($agingOrder as $bracket)
                <div style="display:flex;align-items:center;gap:5px">
                    <div style="width:10px;height:10px;border-radius:2px;background:{{ $agingColors[$bracket] }}"></div>
                    <span style="font-size:11px;color:var(--text-dim)">{{ $bracket }}</span>
                </div>
            @endforeach
        </div>
        {{-- Table --}}
        <div class="iv-table-scroll">
            <table class="iv-table">
                <thead>
                    <tr>
                        <th>Age Bracket</th>
                        <th class="r">Boxes</th>
                        <th class="r">Cost Value</th>
                        <th class="r">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agingOrder as $bracket)
                        @php $bData = $agingKeyed->get($bracket); @endphp
                        @if($bData)
                        <tr>
                            <td>
                                <span class="iv-chip" style="background:{{ $agingColors[$bracket] }}20;color:{{ $agingColors[$bracket] }}">
                                    {{ $bData['age_bracket'] }}
                                </span>
                            </td>
                            <td class="r">{{ number_format($bData['box_count']) }}</td>
                            <td class="r" style="font-family:var(--mono)">{{ number_format($bData['value']) }}</td>
                            <td class="r">{{ round($bData['box_count'] / $agingTotal * 100, 1) }}%</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── ABC Classification Detail ─────────────────────────────────────── --}}
    <div class="iv-card" style="margin-bottom:20px;padding:0;overflow:hidden">
        <div class="iv-section-title" style="padding:20px 20px 14px">ABC Classification Detail</div>
        @foreach(['A' => ['label' => 'A — Fast Movers', 'color' => 'var(--success)'], 'B' => ['label' => 'B — Medium Movers', 'color' => 'var(--amber)'], 'C' => ['label' => 'C — Slow Movers', 'color' => 'var(--amber)']] as $cls => $meta)
            @if(count($vel[$cls] ?? []) > 0)
            <div x-data="{ open: false }">
                <div class="iv-abc-toggle" @click="open = !open">
                    <span class="iv-badge" style="background:{{ $meta['color'] }}20;color:{{ $meta['color'] }}">{{ $meta['label'] }}</span>
                    <span style="font-size:12px;color:var(--text-dim)">{{ count($vel[$cls]) }} products</span>
                    <svg class="iv-abc-chevron" :class="open ? 'rotated' : ''" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div x-show="open" style="display:none">
                    <div class="iv-table-scroll" style="border-top:1px solid var(--border)">
                        <table class="iv-table">
                            <thead>
                                <tr>
                                    <th style="padding-left:20px">Product</th>
                                    <th class="r">Boxes in Stock</th>
                                    <th class="r">Revenue (90d)</th>
                                    <th class="r">Rev %</th>
                                    <th class="r">Cost Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vel[$cls] as $p)
                                <tr>
                                    <td style="padding-left:20px">{{ $p['product_name'] }}</td>
                                    <td class="r">{{ number_format($p['box_count']) }}</td>
                                    <td class="r" style="font-family:var(--mono)">{{ number_format($p['revenue_90d']) }}</td>
                                    <td class="r" style="color:var(--text-dim)">{{ $p['revenue_pct'] }}%</td>
                                    <td class="r" style="font-family:var(--mono)">{{ number_format($p['cost_value']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        @endforeach

        {{-- Dead stock toggler --}}
        @if(count($vel['Dead'] ?? []) > 0)
        <div x-data="{ open: false }" style="border-top:1px solid var(--red)">
            <div class="iv-abc-toggle" style="background:color-mix(in srgb,var(--red) 5%,transparent)" @click="open = !open">
                <span class="iv-badge" style="background:color-mix(in srgb,var(--red) 15%,transparent);color:var(--red)">Dead Stock — No Sales in 90 Days</span>
                <span style="font-size:12px;color:var(--text-dim)">{{ count($vel['Dead']) }} products</span>
                <svg class="iv-abc-chevron" :class="open ? 'rotated' : ''" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--red)"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <div x-show="open" style="display:none">
                <p style="font-size:12px;color:var(--text-dim);padding:8px 20px 4px;margin:0;border-top:1px solid var(--border)">These products hold capital but generate no revenue.</p>
                <div class="iv-table-scroll">
                    <table class="iv-table">
                        <thead>
                            <tr>
                                <th style="padding-left:20px">Product</th>
                                <th class="r">Boxes in Stock</th>
                                <th class="r">Cost Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vel['Dead'] as $p)
                            <tr>
                                <td style="padding-left:20px">{{ $p['product_name'] }}</td>
                                <td class="r">{{ number_format($p['box_count']) }}</td>
                                <td class="r" style="font-family:var(--mono);color:var(--red)">{{ number_format($p['cost_value']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Expiring Stock ───────────────────────────────────────────────── --}}
    <div class="iv-card">
        <div class="iv-section-title">Expiring Stock (Next 30 Days)</div>
        @if(empty($expiring))
            <div style="text-align:center;padding:28px 0">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" style="margin-bottom:8px"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <div style="font-size:14px;font-weight:600;color:var(--success)">No expiring stock</div>
                <div style="font-size:12px;color:var(--text-dim);margin-top:4px">All stocked items are within safe expiry range</div>
            </div>
        @else
            <div class="iv-table-scroll">
                <table class="iv-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="r">Expiry Date</th>
                            <th class="r">Days Left</th>
                            <th class="r">Boxes</th>
                            <th class="r">Cost Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiring as $item)
                            @php
                                $expiryCarbon = \Carbon\Carbon::parse($item['expiry_date'])->startOfDay();
                                $daysLeft     = (int) now()->startOfDay()->diffInDays($expiryCarbon, false);
                                $daysLeft     = max(0, $daysLeft);
                                $expColor     = $daysLeft <= 7  ? 'var(--red)'
                                              : ($daysLeft <= 14 ? 'var(--amber)' : 'var(--amber)');
                            @endphp
                            <tr>
                                <td style="font-weight:600">{{ $item['product_name'] }}</td>
                                <td class="r" style="color:{{ $expColor }};font-family:var(--mono)">
                                    {{ \Carbon\Carbon::parse($item['expiry_date'])->format('d M Y') }}
                                </td>
                                <td class="r">
                                    <span class="iv-chip" style="background:{{ $expColor }}20;color:{{ $expColor }}">{{ $daysLeft }}d</span>
                                </td>
                                <td class="r">{{ number_format($item['box_count']) }}</td>
                                <td class="r" style="font-family:var(--mono)">{{ number_format($item['value']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: REPLENISHMENT
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'replenishment')

    @php
        $doh = $this->daysOnHandPerProduct;
        $vel = $this->velocityClassification;

        $criticalCount = collect($doh)->filter(fn($p) => $p['is_critical'])->count();
        $reorderCount  = collect($doh)->filter(fn($p) => $p['is_low'] && !$p['is_critical'])->count();
        $healthyCount  = collect($doh)->filter(fn($p) => !$p['is_low'] && !$p['is_critical'])->count();

        $filteredDoh = collect($doh);
        if ($urgencyFilter === 'critical') {
            $filteredDoh = $filteredDoh->filter(fn($p) => $p['is_critical']);
        } elseif ($urgencyFilter === 'reorder') {
            $filteredDoh = $filteredDoh->filter(fn($p) => $p['is_low'] && !$p['is_critical']);
        }
    @endphp

    {{-- ── Reorder Summary Strip ───────────────────────────────────────── --}}
    <div class="iv-repl-strip">
        <div class="iv-alert-cell" wire:click="$set('urgencyFilter','critical')"
             style="{{ $urgencyFilter === 'critical' ? 'border-bottom:2.5px solid var(--red)' : '' }}">
            <div class="iv-alert-lbl" style="color:var(--red)">Critical Reorder</div>
            <div class="iv-alert-val" style="color:var(--red)">{{ $criticalCount }}</div>
            <div class="iv-alert-sub">≤ 7 days on hand</div>
        </div>
        <div class="iv-alert-cell" wire:click="$set('urgencyFilter','reorder')"
             style="{{ $urgencyFilter === 'reorder' ? 'border-bottom:2.5px solid var(--amber)' : '' }}">
            <div class="iv-alert-lbl" style="color:var(--amber)">Reorder Soon</div>
            <div class="iv-alert-val" style="color:var(--amber)">{{ $reorderCount }}</div>
            <div class="iv-alert-sub">8 – 14 days on hand</div>
        </div>
        <div class="iv-alert-cell" wire:click="$set('urgencyFilter','all')"
             style="{{ $urgencyFilter === 'all' ? 'border-bottom:2.5px solid var(--success)' : '' }}">
            <div class="iv-alert-lbl" style="color:var(--success)">Healthy</div>
            <div class="iv-alert-val" style="color:var(--success)">{{ $healthyCount }}</div>
            <div class="iv-alert-sub">> 14 days on hand</div>
        </div>
    </div>

    {{-- ── Urgency pill filter ──────────────────────────────────────────── --}}
    <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:12px">
        <button wire:click="$set('urgencyFilter','all')"      class="iv-preset {{ $urgencyFilter === 'all'      ? 'active' : '' }}">All Products</button>
        <button wire:click="$set('urgencyFilter','critical')" class="iv-preset {{ $urgencyFilter === 'critical' ? 'active' : '' }}">Critical only</button>
        <button wire:click="$set('urgencyFilter','reorder')"  class="iv-preset {{ $urgencyFilter === 'reorder'  ? 'active' : '' }}">Reorder soon</button>
        <span style="font-size:12px;color:var(--text-dim);padding:6px 8px;align-self:center">{{ $filteredDoh->count() }} products</span>
    </div>

    {{-- ── Replenishment Urgency Table ──────────────────────────────────── --}}
    @php $rSortArrow = fn($col) => $replSortBy === $col ? ($replSortDir === 'asc' ? '↑' : '↓') : '↕'; @endphp
    <div class="iv-card" style="margin-bottom:24px">
        <div class="iv-section-title">Products Requiring Action — Sorted by Urgency</div>

        {{-- Lead-time input --}}
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-dim);padding:0 0 14px">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Lead time:
            <input type="number" wire:model.live="leadTimeDays" min="1" max="365"
                   style="width:52px;text-align:center;border:1px solid var(--border);border-radius:5px;padding:3px 8px;font-size:13px;font-weight:600;font-family:var(--mono);background:var(--surface);color:var(--text)">
            days &middot; Reorder date = days on hand &minus; lead time
        </div>

        @if($filteredDoh->isEmpty())
            <div style="text-align:center;padding:28px 0;color:var(--success)">
                <div style="font-size:14px;font-weight:600">
                    @if($urgencyFilter === 'critical') No critical products
                    @elseif($urgencyFilter === 'reorder') No products needing reorder
                    @else No products with stock data
                    @endif
                </div>
            </div>
        @else
            <div class="iv-table-scroll">
                <table class="iv-table" style="min-width:880px;table-layout:fixed">
                    <colgroup>
                        <col style="width:220px">
                        <col style="width:110px">
                        <col style="width:130px">
                        <col style="width:90px">
                        <col style="width:120px">
                        <col style="width:90px">
                        <col style="width:120px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th wire:click="sortReplenishment('product_name')" class="iv-sort-th {{ $replSortBy === 'product_name' ? 'active' : '' }}">
                                Product <span class="iv-sort-arrow">{{ $rSortArrow('product_name') }}</span>
                            </th>
                            <th wire:click="sortReplenishment('box_count')" class="iv-sort-th r {{ $replSortBy === 'box_count' ? 'active' : '' }}">
                                Stock (boxes) <span class="iv-sort-arrow">{{ $rSortArrow('box_count') }}</span>
                            </th>
                            <th wire:click="sortReplenishment('boxes_sold_period')" class="iv-sort-th r {{ $replSortBy === 'boxes_sold_period' ? 'active' : '' }}">
                                Boxes Sold ({{ $lookbackDays }}d) <span class="iv-sort-arrow">{{ $rSortArrow('boxes_sold_period') }}</span>
                            </th>
                            <th class="r">Avg/Day</th>
                            <th wire:click="sortReplenishment('days_on_hand')" class="iv-sort-th r {{ $replSortBy === 'days_on_hand' ? 'active' : '' }}">
                                Days on Hand <span class="iv-sort-arrow">{{ $rSortArrow('days_on_hand') }}</span>
                            </th>
                            <th class="r">Status</th>
                            <th class="r">Reorder By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filteredDoh as $p)
                            @php
                                $dohColor = match(true) {
                                    $p['days_on_hand'] === null => 'var(--text-dim)',
                                    $p['days_on_hand'] <= 7    => 'var(--red)',
                                    $p['days_on_hand'] <= 14   => 'var(--amber)',
                                    default                    => 'var(--success)',
                                };
                                $statusLabel = match(true) {
                                    $p['days_on_hand'] === null => ['text' => 'No data',  'color' => 'var(--text-dim)'],
                                    $p['is_critical']           => ['text' => 'Critical', 'color' => 'var(--red)'],
                                    $p['is_low']                => ['text' => 'Reorder',  'color' => 'var(--amber)'],
                                    default                     => ['text' => 'OK',        'color' => 'var(--success)'],
                                };
                                if ($p['days_on_hand'] !== null) {
                                    $gap = $p['days_on_hand'] - $leadTimeDays;
                                    $reorderBy = $gap <= 0
                                        ? ['label' => 'OVERDUE',                                          'color' => 'var(--red)']
                                        : ($gap <= 7
                                            ? ['label' => 'Order by '.now()->addDays($gap)->format('d M'), 'color' => 'var(--amber)']
                                            : ['label' => now()->addDays($gap)->format('d M'),             'color' => 'var(--text-dim)']);
                                } else {
                                    $reorderBy = null;
                                }
                                $rowBg = $p['is_critical']
                                    ? 'background:color-mix(in srgb,var(--red) 5%,transparent)'
                                    : ($p['is_low'] ? 'background:color-mix(in srgb,var(--amber) 5%,transparent)' : '');
                            @endphp
                            <tr style="{{ $rowBg }}">
                                <td style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $p['product_name'] }}">{{ $p['product_name'] }}</td>
                                <td class="r">{{ number_format($p['box_count']) }}</td>
                                <td class="r" style="color:var(--text-dim)">{{ number_format($p['boxes_sold_period']) }}</td>
                                <td class="r" style="color:var(--text-dim)">{{ $p['avg_daily_sales'] > 0 ? number_format($p['avg_daily_sales'], 1) : '—' }}</td>
                                <td class="r">
                                    @if($p['days_on_hand'] !== null)
                                        <span class="iv-chip" style="background:{{ $dohColor }}20;color:{{ $dohColor }}">{{ $p['days_on_hand'] }}d</span>
                                    @else
                                        <span style="color:var(--text-dim);white-space:nowrap">—</span>
                                    @endif
                                </td>
                                <td class="r">
                                    <span class="iv-badge" style="background:{{ $statusLabel['color'] }}20;color:{{ $statusLabel['color'] }}">{{ $statusLabel['text'] }}</span>
                                </td>
                                <td class="r" style="white-space:nowrap;font-family:var(--mono);font-size:12px;font-weight:700;color:{{ $reorderBy ? $reorderBy['color'] : 'var(--text-dim)' }}">{{ $reorderBy ? $reorderBy['label'] : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ── Dead Stock Capital Lock ───────────────────────────────────────── --}}
    @php
        $deadItems   = $vel['Dead'] ?? [];
        $deadCapital = $vel['summary']['Dead_cost_value'] ?? 0;
    @endphp
    @if(count($deadItems) > 0)
    <div class="iv-card" style="box-shadow:var(--shadow-card),0 0 0 1.5px var(--red)">
        <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:16px">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2" style="flex-shrink:0;margin-top:2px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>
                <div class="iv-section-title" style="color:var(--red);margin-bottom:4px">Dead Stock Capital Lock</div>
                <div style="font-size:13px;color:var(--text-sub)">
                    The following products have inventory but zero sales in 90 days.
                    They represent <strong>{{ number_format($deadCapital) }} RWF</strong> in locked capital.
                </div>
            </div>
        </div>
        <div class="iv-table-scroll">
            <table class="iv-table" style="min-width:610px;table-layout:fixed">
                <colgroup>
                    <col style="width:220px">
                    <col style="width:130px">
                    <col style="width:160px">
                    <col style="width:100px">
                </colgroup>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="r">Boxes in Stock</th>
                        <th class="r" style="white-space:nowrap">Cost Value (RWF)</th>
                        <th class="r">Last Sale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deadItems as $p)
                    <tr>
                        <td style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $p['product_name'] }}">{{ $p['product_name'] }}</td>
                        <td class="r">{{ number_format($p['box_count']) }}</td>
                        <td class="r" style="font-family:var(--mono);color:var(--red);white-space:nowrap">{{ number_format($p['cost_value']) }}</td>
                        <td class="r" style="color:var(--text-dim)">90d+</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="iv-card" style="text-align:center;padding:28px">
        <div style="font-size:14px;font-weight:600;color:var(--success)">No dead stock detected</div>
        <div style="font-size:12px;color:var(--text-dim);margin-top:4px">All products have had sales activity in the last 90 days</div>
    </div>
    @endif

@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     CHART.JS — Inventory Movement Trend
══════════════════════════════════════════════════════════════════════════ --}}
@script
<script>
    (() => {
        const initChart = () => {
            const wrap = document.getElementById('inv-mov-chart-wrap');
            if (!wrap) return;
            const raw = JSON.parse(wrap.dataset.chart || '[]');
            wrap.innerHTML = '';
            if (!raw.length) return;
            const canvas = document.createElement('canvas');
            canvas.style.maxHeight = '280px';
            wrap.appendChild(canvas);
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: raw.map(d => d.week_label),
                    datasets: [
                        { label:'Boxes Received', data:raw.map(d=>d.boxes_received),
                          backgroundColor:'rgba(59,130,246,0.75)', borderRadius:3, borderSkipped:false },
                        { label:'Boxes Consumed',  data:raw.map(d=>d.boxes_consumed),
                          backgroundColor:'rgba(245,158,11,0.75)', borderRadius:3, borderSkipped:false }
                    ]
                },
                options: {
                    responsive:true, maintainAspectRatio:false, animation:false,
                    plugins: {
                        legend:{ position:'top', labels:{ font:{size:12}, padding:16, usePointStyle:true } },
                        tooltip:{ mode:'index', intersect:false }
                    },
                    scales: {
                        x:{ grid:{display:false} },
                        y:{ beginAtZero:true, grid:{color:'rgba(128,128,128,.08)'} }
                    }
                }
            });
        };

        initChart();

        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                if (document.getElementById('inv-mov-chart-wrap')) requestAnimationFrame(initChart);
            });
        });
    })();
</script>
@endscript

</div>{{-- end root div --}}
