<div style="font-family:var(--font)">
<style>
/* ── Loss Analysis (la-) ───────────────────────────────────────── */
.la-header      { display:flex;align-items:flex-start;justify-content:space-between;
                  gap:12px;margin-bottom:20px;flex-wrap:wrap }
.la-title       { font-size:22px;font-weight:800;color:var(--text);letter-spacing:-.5px }
.la-subtitle    { font-size:13px;color:var(--text-dim);margin-top:3px }

/* Filters */
.la-filters     { margin-bottom:20px;min-width:0 }
.la-loc-wrap    { display:flex;align-items:center;gap:8px }
.la-loc-label   { font-size:12px;color:var(--text-dim);flex-shrink:0;white-space:nowrap }
.la-loc-select  { padding:5px 10px;border:1.5px solid var(--border);border-radius:8px;
                  font-size:12px;background:var(--surface);color:var(--text);
                  outline:none;cursor:pointer;font-family:var(--font) }
.la-loc-select:focus { border-color:var(--accent) }

/* KPI strip */
.la-kpis        { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px }
.la-kpi         { background:var(--surface);border:none;border-radius:var(--r);
                  box-shadow:var(--shadow-card);padding:20px 18px;
                  display:flex;flex-direction:column;gap:14px;transition:box-shadow var(--tr) }
.la-kpi:hover   { box-shadow:var(--shadow-card-hover) }
.la-kpi-row     { display:flex;align-items:center;gap:10px }
.la-kpi-icon    { width:34px;height:34px;border-radius:9px;display:flex;align-items:center;
                  justify-content:center;flex-shrink:0 }
.la-kpi-body    { flex:1;min-width:0 }
.la-kpi-label   { font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                  color:var(--text-dim);line-height:1.2 }
.la-kpi-sub     { font-size:11px;color:var(--text-dim);margin-top:2px }
.la-kpi-val     { font-size:22px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;line-height:1 }
.la-kpi-bar     { height:3px;border-radius:3px }
.la-kpi-divider { height:1px;background:var(--border) }
.la-kpi-footer  { display:grid;grid-template-columns:repeat(3,1fr) }
.la-kpi-stat    { display:flex;flex-direction:column;align-items:center;gap:2px;padding:3px 0 }
.la-kpi-stat-v  { font-size:11px;font-weight:700;font-family:var(--mono);color:var(--text-sub) }
.la-kpi-stat-l  { font-size:10px;color:var(--text-dim);letter-spacing:.3px;text-align:center }

/* Tabs */
.la-tabs        { display:grid;grid-template-columns:repeat(4,1fr);
                  background:var(--surface);box-shadow:var(--shadow-card);
                  border-radius:var(--r);margin-bottom:20px;overflow:hidden }
.la-tab         { display:flex;align-items:center;justify-content:center;gap:6px;
                  padding:12px 10px;border:none;border-radius:0;
                  border-bottom:2.5px solid transparent;
                  border-right:1px solid var(--border);
                  cursor:pointer;font-size:12px;font-weight:600;
                  font-family:var(--font);background:transparent;color:var(--text-dim);
                  transition:all var(--tr);white-space:nowrap }
.la-tab:last-child  { border-right:none }
.la-tab:hover       { background:var(--surface2);color:var(--text);border-bottom-color:var(--border-hi) }
.la-tab.active      { background:var(--accent-dim);color:var(--accent);border-bottom-color:var(--accent) }

/* Cards */
.la-card        { background:var(--surface);border:none;border-radius:var(--r);
                  box-shadow:var(--shadow-card);padding:20px;margin-bottom:16px }
.la-card-head   { display:flex;align-items:flex-start;justify-content:space-between;
                  margin-bottom:18px;gap:12px }
.la-card-title  { font-size:14px;font-weight:700;color:var(--text) }
.la-card-sub    { font-size:12px;color:var(--text-dim);margin-top:2px }

/* 2-col grid */
.la-grid-2      { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px }

/* Tables */
.la-tbl-wrap    { overflow-x:auto;-webkit-overflow-scrolling:touch }
.la-tbl         { width:100%;border-collapse:collapse }
.la-tbl thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.la-tbl thead th { padding:9px 14px;text-align:left;font-size:10.5px;font-weight:700;
                   letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.la-tbl tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.la-tbl tbody tr:last-child { border-bottom:none }
.la-tbl tbody tr:hover { background:var(--surface2) }
.la-tbl tfoot tr { border-top:2px solid var(--border);background:var(--bg) }
.la-tbl td      { padding:11px 14px;font-size:13px;vertical-align:middle }
.la-num         { font-family:var(--mono);text-align:right;white-space:nowrap }

/* Rank badges */
.la-rank        { width:28px;height:28px;border-radius:8px;display:inline-flex;align-items:center;
                  justify-content:center;font-size:11px;font-weight:700;font-family:var(--mono) }
.la-rank-1      { background:rgba(217,119,6,.15);color:var(--amber) }
.la-rank-2      { background:rgba(100,116,139,.12);color:var(--text-sub) }
.la-rank-3      { background:rgba(180,120,50,.12);color:#c97d3a }
.la-rank-n      { background:var(--surface2);color:var(--text-dim) }

/* Loss impact bar */
.la-impact      { display:flex;align-items:center;gap:8px }
.la-impact-bar  { flex:1;height:4px;border-radius:4px;background:var(--surface2);min-width:40px }
.la-impact-fill { height:100%;border-radius:4px;background:var(--red) }
.la-impact-val  { font-family:var(--mono);font-size:12px;font-weight:700;
                  white-space:nowrap;color:var(--red) }

/* Disposition pill */
.la-disp        { display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;
                  padding:2px 8px;border-radius:6px;white-space:nowrap }
.la-disp-dot    { width:5px;height:5px;border-radius:50%;flex-shrink:0 }

/* Alert strip */
.la-alert       { margin-top:14px;padding:12px 14px;border-radius:9px;
                  border-left:3px solid var(--amber);background:var(--amber-dim) }
.la-alert-title { font-size:12px;font-weight:700;color:var(--amber) }
.la-alert-sub   { font-size:11px;color:var(--text-dim);margin-top:3px }

/* Empty state */
.la-empty       { padding:48px 20px;text-align:center }
.la-empty-title { font-size:14px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.la-empty-sub   { font-size:12px;color:var(--text-dim) }

/* Responsive */
@media(max-width:900px) {
    .la-kpis    { grid-template-columns:1fr 1fr;gap:10px }
    .la-grid-2  { grid-template-columns:1fr }
}
@media(max-width:640px) {
    .la-kpis    { grid-template-columns:1fr 1fr;gap:8px }
    .la-kpi     { padding:14px 12px;gap:10px }
    .la-kpi-val { font-size:18px }
    .la-kpi-footer { grid-template-columns:1fr 1fr }
    .la-tabs    { display:flex;overflow-x:auto;-webkit-overflow-scrolling:touch;
                  scrollbar-width:none;flex-wrap:nowrap }
    .la-tabs::-webkit-scrollbar { display:none }
    .la-tab     { flex-shrink:0;min-width:100px;padding:11px 14px }
    .la-tab svg { display:none }
}
@media(max-width:480px) {
    .la-hide-mob   { display:none !important }
    .la-kpis       { grid-template-columns:1fr }
    .la-kpi-footer { grid-template-columns:repeat(3,1fr) }
}
</style>

{{-- Page Header --}}
<div class="la-header">
    <div>
        <div class="la-title">Loss Analysis</div>
        <div class="la-subtitle">Returns, damaged goods &amp; shrinkage &mdash; {{ $dateFrom }} to {{ $dateTo }}</div>
    </div>
</div>

{{-- Period + Location Filter --}}
<div class="la-filters">
    <div class="db-period-bar">
        <div class="db-period-pills">
            @foreach([
                'today'      => 'Today',
                'yesterday'  => 'Yesterday',
                'week'       => 'This Week',
                'month'      => 'This Month',
                'last_month' => 'Last Month',
                'last_30'    => 'Last 30 Days',
            ] as $key => $label)
            <button class="db-period-pill {{ $preset === $key ? 'active' : '' }}"
                    wire:click="setPreset('{{ $key }}')">{{ $label }}</button>
            @endforeach
        </div>
        <div class="db-period-controls">
            <div class="db-period-ctrl-seg db-period-ctrl-grow">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24" style="flex-shrink:0;color:var(--text-dim)">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                <input type="date" wire:model.live="dateFrom" class="db-date-input">
                <span style="font-size:13px;color:var(--text-dim);flex-shrink:0">→</span>
                <input type="date" wire:model.live="dateTo" class="db-date-input">
            </div>
            <div class="db-period-ctrl-seg la-loc-wrap">
                <span class="la-loc-label">Shop</span>
                <select wire:model.live="locationFilter" class="la-loc-select">
                    <option value="all">All Shops</option>
                    @foreach($this->shops as $shop)
                        <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
@php
    $kpis        = $this->lossKpis;
    $avgRefund   = $kpis['returns_count'] > 0
                    ? (int) round($kpis['total_refunds'] / $kpis['returns_count'])
                    : 0;
    $dispRows    = $this->dispositionBreakdown;
    $damagedCount = collect($dispRows)->sum('count');
    $lossShare   = $kpis['total_loss'] > 0
                    ? (int) round($kpis['total_refunds'] / $kpis['total_loss'] * 100)
                    : 0;
    $dmgShare    = 100 - $lossShare;
    $rateColor   = $kpis['return_rate'] > 5
                    ? 'var(--red)'
                    : ($kpis['return_rate'] > 2 ? 'var(--amber)' : 'var(--green)');
    $rateLabel   = $kpis['return_rate'] > 5 ? 'High' : ($kpis['return_rate'] > 2 ? 'Medium' : 'Low');
@endphp
<div class="la-kpis">

    {{-- Total Loss --}}
    <div class="la-kpi">
        <div class="la-kpi-row">
            <div class="la-kpi-icon" style="background:var(--red-dim);color:var(--red)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div class="la-kpi-body">
                <div class="la-kpi-label">Total Loss</div>
                <div class="la-kpi-sub">Refunds + Damaged</div>
            </div>
        </div>
        <div class="la-kpi-val" style="color:var(--red)">{{ number_format($kpis['total_loss']) }} <span style="font-size:12px;font-weight:600;color:var(--text-dim)">RWF</span></div>
        <div class="la-kpi-bar" style="background:var(--red-dim)">
            <div style="height:100%;border-radius:3px;background:var(--red);width:100%"></div>
        </div>
        <div class="la-kpi-divider"></div>
        <div class="la-kpi-footer">
            <div class="la-kpi-stat">
                <span class="la-kpi-stat-v">{{ number_format($kpis['total_refunds']) }}</span>
                <span class="la-kpi-stat-l">Refunds RWF</span>
            </div>
            <div class="la-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="la-kpi-stat-v">{{ number_format($kpis['damaged_loss']) }}</span>
                <span class="la-kpi-stat-l">Damaged RWF</span>
            </div>
            <div class="la-kpi-stat">
                <span class="la-kpi-stat-v">{{ $lossShare }}%</span>
                <span class="la-kpi-stat-l">Refund Share</span>
            </div>
        </div>
    </div>

    {{-- Total Refunds --}}
    <div class="la-kpi">
        <div class="la-kpi-row">
            <div class="la-kpi-icon" style="background:var(--amber-dim);color:var(--amber)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            </div>
            <div class="la-kpi-body">
                <div class="la-kpi-label">Total Refunds</div>
                <div class="la-kpi-sub">Cash returned to customers</div>
            </div>
        </div>
        <div class="la-kpi-val" style="color:var(--amber)">{{ number_format($kpis['total_refunds']) }} <span style="font-size:12px;font-weight:600;color:var(--text-dim)">RWF</span></div>
        <div class="la-kpi-divider"></div>
        <div class="la-kpi-footer">
            <div class="la-kpi-stat">
                <span class="la-kpi-stat-v">{{ $kpis['returns_count'] }}</span>
                <span class="la-kpi-stat-l">Returns</span>
            </div>
            <div class="la-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="la-kpi-stat-v">{{ number_format($avgRefund) }}</span>
                <span class="la-kpi-stat-l">Avg / Return</span>
            </div>
            <div class="la-kpi-stat">
                <span class="la-kpi-stat-v" style="color:{{ $rateColor }}">{{ number_format($kpis['return_rate'], 1) }}%</span>
                <span class="la-kpi-stat-l">Return Rate</span>
            </div>
        </div>
    </div>

    {{-- Return Rate --}}
    <div class="la-kpi">
        <div class="la-kpi-row">
            <div class="la-kpi-icon" style="background:{{ $kpis['return_rate'] > 5 ? 'var(--red-dim)' : ($kpis['return_rate'] > 2 ? 'var(--amber-dim)' : 'var(--green-dim)') }};color:{{ $rateColor }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="la-kpi-body">
                <div class="la-kpi-label">Return Rate</div>
                <div class="la-kpi-sub">Returns ÷ Total Sales</div>
            </div>
        </div>
        <div class="la-kpi-val" style="color:{{ $rateColor }}">{{ number_format($kpis['return_rate'], 2) }}<span style="font-size:16px;font-weight:700">%</span></div>
        <div class="la-kpi-divider"></div>
        <div class="la-kpi-footer">
            <div class="la-kpi-stat">
                <span class="la-kpi-stat-v">{{ $kpis['returns_count'] }}</span>
                <span class="la-kpi-stat-l">Returns</span>
            </div>
            <div class="la-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="la-kpi-stat-v" style="color:{{ $rateColor }}">{{ $rateLabel }}</span>
                <span class="la-kpi-stat-l">Risk Level</span>
            </div>
            <div class="la-kpi-stat">
                <span class="la-kpi-stat-v">&lt; 2%</span>
                <span class="la-kpi-stat-l">Target</span>
            </div>
        </div>
    </div>

    {{-- Damaged Goods --}}
    <div class="la-kpi">
        <div class="la-kpi-row">
            <div class="la-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="la-kpi-body">
                <div class="la-kpi-label">Damaged Goods</div>
                <div class="la-kpi-sub">Estimated value lost</div>
            </div>
        </div>
        <div class="la-kpi-val" style="color:var(--violet)">{{ number_format($kpis['damaged_loss']) }} <span style="font-size:12px;font-weight:600;color:var(--text-dim)">RWF</span></div>
        <div class="la-kpi-divider"></div>
        <div class="la-kpi-footer">
            <div class="la-kpi-stat">
                <span class="la-kpi-stat-v">{{ $damagedCount }}</span>
                <span class="la-kpi-stat-l">Incidents</span>
            </div>
            <div class="la-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="la-kpi-stat-v">{{ $damagedCount > 0 ? number_format((int) round($kpis['damaged_loss'] / $damagedCount)) : '—' }}</span>
                <span class="la-kpi-stat-l">Avg / Incident</span>
            </div>
            <div class="la-kpi-stat">
                <span class="la-kpi-stat-v">{{ $dmgShare }}%</span>
                <span class="la-kpi-stat-l">Of Total Loss</span>
            </div>
        </div>
    </div>

</div>

{{-- Tab Strip --}}
<div class="la-tabs">
    <button class="la-tab {{ $activeTab === 'overview' ? 'active' : '' }}" wire:click="setTab('overview')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        <span>Trends</span>
    </button>
    <button class="la-tab {{ $activeTab === 'returns' ? 'active' : '' }}" wire:click="setTab('returns')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
        <span>Returns</span>
    </button>
    <button class="la-tab {{ $activeTab === 'damaged' ? 'active' : '' }}" wire:click="setTab('damaged')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Damaged Goods</span>
    </button>
    <button class="la-tab {{ $activeTab === 'products' ? 'active' : '' }}" wire:click="setTab('products')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <span>Problem Products</span>
    </button>
</div>

{{-- ═══ TAB: TRENDS ═══ --}}
@if($activeTab === 'overview')
    @php
        $trend        = $this->lossTrend;
        $trendDates   = array_column($trend, 'date');
        $trendRefunds = array_column($trend, 'refunds');
        $trendDamaged = array_column($trend, 'damaged_loss');
        $trendTotal   = array_column($trend, 'total_loss');
    @endphp
    <div class="la-card">
        <div class="la-card-head">
            <div>
                <div class="la-card-title">Loss Trend Over Time</div>
                <div class="la-card-sub">Daily refunds and damaged goods losses — select a date range above to zoom in</div>
            </div>
        </div>
        <div wire:key="la-trend-wrap-{{ $dateFrom }}-{{ $dateTo }}-{{ $locationFilter }}">
            <div id="la-trend-chart"
                 data-dates='@json($trendDates)'
                 data-refunds='@json($trendRefunds)'
                 data-damaged='@json($trendDamaged)'
                 data-total='@json($trendTotal)'
                 style="min-height:300px"></div>
        </div>
    </div>

    {{-- Summary stats row --}}
    @if(count($trend))
    @php
        $peakDay    = collect($trend)->sortByDesc('total_loss')->first();
        $worstDate  = $peakDay ? $peakDay['date'] : null;
        $worstVal   = $peakDay ? $peakDay['total_loss'] : 0;
        $avgDaily   = count($trend) > 0 ? (int) round(array_sum($trendTotal) / count($trend)) : 0;
        $daysWithLoss = count(array_filter($trendTotal, fn($v) => $v > 0));
    @endphp
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
        <div style="background:var(--surface);border:none;border-radius:var(--r);
                    box-shadow:var(--shadow-card);padding:16px 18px">
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;
                        letter-spacing:.5px;color:var(--text-dim);margin-bottom:8px">Worst Day</div>
            <div style="font-size:18px;font-weight:800;font-family:var(--mono);color:var(--red)">
                {{ $worstDate ? \Carbon\Carbon::parse($worstDate)->format('M j') : '—' }}
            </div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:4px">
                {{ number_format($worstVal) }} RWF total loss
            </div>
        </div>
        <div style="background:var(--surface);border:none;border-radius:var(--r);
                    box-shadow:var(--shadow-card);padding:16px 18px">
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;
                        letter-spacing:.5px;color:var(--text-dim);margin-bottom:8px">Daily Average</div>
            <div style="font-size:18px;font-weight:800;font-family:var(--mono);color:var(--text)">
                {{ number_format($avgDaily) }}
            </div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:4px">RWF lost per day</div>
        </div>
        <div style="background:var(--surface);border:none;border-radius:var(--r);
                    box-shadow:var(--shadow-card);padding:16px 18px">
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;
                        letter-spacing:.5px;color:var(--text-dim);margin-bottom:8px">Days With Loss</div>
            <div style="font-size:18px;font-weight:800;font-family:var(--mono);color:var(--text)">
                {{ $daysWithLoss }}<span style="font-size:13px;color:var(--text-dim)"> / {{ count($trend) }}</span>
            </div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:4px">
                {{ count($trend) > 0 ? round($daysWithLoss / count($trend) * 100) : 0 }}% of period days
            </div>
        </div>
    </div>
    @endif
@endif

{{-- ═══ TAB: RETURNS ═══ --}}
@if($activeTab === 'returns')
    @php
        $reasons = $this->returnReasons;
        $byLoc   = $this->returnsByLocation;
    @endphp
    <div class="la-grid-2">

        {{-- Return Reasons --}}
        <div class="la-card" style="margin-bottom:0">
            <div class="la-card-head">
                <div>
                    <div class="la-card-title">Return Reasons</div>
                    <div class="la-card-sub">Why customers are returning products</div>
                </div>
            </div>
            @if(count($reasons))
                <div wire:key="la-reasons-wrap-{{ $dateFrom }}-{{ $dateTo }}-{{ $locationFilter }}">
                    <div id="la-reasons-chart"
                         data-labels='@json(array_column($reasons, "reason"))'
                         data-counts='@json(array_column($reasons, "count"))'
                         data-amounts='@json(array_column($reasons, "amount"))'
                         style="min-height:220px"></div>
                </div>
                <div style="margin-top:16px">
                    <table class="la-tbl">
                        <thead>
                            <tr>
                                <th>Reason</th>
                                <th style="text-align:right">Count</th>
                                <th style="text-align:right">Refunded (RWF)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalReasonCount = collect($reasons)->sum('count'); @endphp
                            @foreach($reasons as $r)
                                @php $pct = $totalReasonCount > 0 ? round($r['count'] / $totalReasonCount * 100) : 0 @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight:600;font-size:13px">{{ $r['reason'] }}</div>
                                        <div style="margin-top:4px">
                                            <div style="height:3px;border-radius:3px;background:var(--surface2);width:100%">
                                                <div style="height:100%;border-radius:3px;background:var(--amber);width:{{ $pct }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="la-num">
                                        {{ $r['count'] }}
                                        <span style="font-size:10px;color:var(--text-dim);margin-left:4px">({{ $pct }}%)</span>
                                    </td>
                                    <td class="la-num" style="color:var(--amber)">{{ number_format($r['amount']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="la-empty">
                    <div class="la-empty-title">No Return Reasons</div>
                    <div class="la-empty-sub">No returns recorded for this period</div>
                </div>
            @endif
        </div>

        {{-- Returns by Shop --}}
        <div class="la-card" style="margin-bottom:0">
            <div class="la-card-head">
                <div>
                    <div class="la-card-title">Returns by Shop</div>
                    <div class="la-card-sub">Refunds vs exchanges per location</div>
                </div>
            </div>
            @if(count($byLoc))
                <div wire:key="la-location-wrap-{{ $dateFrom }}-{{ $dateTo }}">
                    <div id="la-location-chart"
                         data-shops='@json(array_column($byLoc, "shop_name"))'
                         data-returns='@json(array_column($byLoc, "returns_count"))'
                         data-exchanges='@json(array_column($byLoc, "exchanges_count"))'
                         data-refunds='@json(array_column($byLoc, "refund_amount"))'
                         style="min-height:220px"></div>
                </div>
                <div style="margin-top:16px">
                    <div class="la-tbl-wrap">
                        <table class="la-tbl" style="min-width:380px">
                            <thead>
                                <tr>
                                    <th>Shop</th>
                                    <th style="text-align:right">Returns</th>
                                    <th style="text-align:right">Exchanges</th>
                                    <th style="text-align:right">Refunded (RWF)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byLoc as $loc)
                                    <tr>
                                        <td style="font-weight:500">{{ $loc['shop_name'] }}</td>
                                        <td class="la-num">{{ $loc['returns_count'] }}</td>
                                        <td class="la-num">{{ $loc['exchanges_count'] }}</td>
                                        <td class="la-num" style="color:var(--amber)">{{ number_format($loc['refund_amount']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if(count($byLoc) > 1)
                            <tfoot>
                                <tr>
                                    <td style="font-weight:700;font-size:12px;padding:9px 14px">Total</td>
                                    <td class="la-num" style="font-weight:700;padding:9px 14px">{{ collect($byLoc)->sum('returns_count') }}</td>
                                    <td class="la-num" style="font-weight:700;padding:9px 14px">{{ collect($byLoc)->sum('exchanges_count') }}</td>
                                    <td class="la-num" style="font-weight:700;color:var(--amber);padding:9px 14px">{{ number_format(collect($byLoc)->sum('refund_amount')) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            @else
                <div class="la-empty">
                    <div class="la-empty-title">No Shop Data</div>
                    <div class="la-empty-sub">No returns by location for this period</div>
                </div>
            @endif
        </div>

    </div>
@endif

{{-- ═══ TAB: DAMAGED GOODS ═══ --}}
@if($activeTab === 'damaged')
    @php
        $disposition = $this->dispositionBreakdown;
        $dispColors  = [
            'pending'            => ['bg' => 'var(--amber-dim)', 'c' => 'var(--amber)'],
            'dispose'            => ['bg' => 'var(--red-dim)',   'c' => 'var(--red)'],
            'return_to_supplier' => ['bg' => 'var(--accent-dim)','c' => 'var(--accent)'],
            'discount_sale'      => ['bg' => 'var(--green-dim)', 'c' => 'var(--green)'],
            'write_off'          => ['bg' => 'var(--red-dim)',   'c' => 'var(--red)'],
            'repair'             => ['bg' => 'var(--violet-dim)','c' => 'var(--violet)'],
        ];
        $pendingRow  = collect($disposition)->firstWhere('disposition', 'pending');
    @endphp
    <div class="la-grid-2">

        {{-- Disposition Donut --}}
        <div class="la-card" style="margin-bottom:0">
            <div class="la-card-head">
                <div>
                    <div class="la-card-title">Disposition Breakdown</div>
                    <div class="la-card-sub">How damaged goods are being handled</div>
                </div>
            </div>
            @if(count($disposition))
                <div wire:key="la-disp-wrap-{{ $dateFrom }}-{{ $dateTo }}-{{ $locationFilter }}">
                    <div id="la-disposition-chart"
                         data-labels='@json(array_column($disposition, "disposition"))'
                         data-counts='@json(array_column($disposition, "count"))'
                         data-losses='@json(array_column($disposition, "loss"))'
                         style="min-height:260px"></div>
                </div>
                @if($pendingRow)
                    <div class="la-alert">
                        <div class="la-alert-title">⚠ {{ $pendingRow['count'] }} {{ Str::plural('item', $pendingRow['count']) }} still pending disposition</div>
                        <div class="la-alert-sub">Estimated value: {{ number_format($pendingRow['loss']) }} RWF — visit Damaged Goods to assign dispositions</div>
                    </div>
                @endif
            @else
                <div class="la-empty">
                    <div class="la-empty-title">No Damaged Goods</div>
                    <div class="la-empty-sub">No damaged goods recorded for this period</div>
                </div>
            @endif
        </div>

        {{-- Disposition Table --}}
        <div class="la-card" style="margin-bottom:0">
            <div class="la-card-head">
                <div>
                    <div class="la-card-title">Disposition Detail</div>
                    <div class="la-card-sub">Loss value breakdown by how items are being disposed</div>
                </div>
            </div>
            @if(count($disposition))
                <div class="la-tbl-wrap">
                    <table class="la-tbl" style="min-width:360px">
                        <thead>
                            <tr>
                                <th>Disposition</th>
                                <th style="text-align:right">Incidents</th>
                                <th style="text-align:right">Qty</th>
                                <th style="text-align:right">Est. Loss (RWF)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($disposition as $d)
                                @php
                                    $dc     = $dispColors[$d['disposition']] ?? ['bg' => 'var(--surface2)', 'c' => 'var(--text-dim)'];
                                    $dlabel = ucwords(str_replace('_', ' ', $d['disposition']));
                                @endphp
                                <tr>
                                    <td>
                                        <span class="la-disp" style="background:{{ $dc['bg'] }};color:{{ $dc['c'] }}">
                                            <span class="la-disp-dot" style="background:{{ $dc['c'] }}"></span>
                                            {{ $dlabel }}
                                        </span>
                                    </td>
                                    <td class="la-num">{{ $d['count'] }}</td>
                                    <td class="la-num">{{ number_format($d['quantity']) }}</td>
                                    <td class="la-num" style="color:var(--red)">{{ number_format($d['loss']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td style="font-weight:700;font-size:12px;padding:9px 14px">Total</td>
                                <td class="la-num" style="font-weight:700;padding:9px 14px">{{ collect($disposition)->sum('count') }}</td>
                                <td class="la-num" style="font-weight:700;padding:9px 14px">{{ number_format(collect($disposition)->sum('quantity')) }}</td>
                                <td class="la-num" style="font-weight:700;color:var(--red);padding:9px 14px">{{ number_format(collect($disposition)->sum('loss')) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Loss by disposition bar chart --}}
                <div style="margin-top:16px">
                    @php $totalDispLoss = max(collect($disposition)->sum('loss'), 1) @endphp
                    @foreach($disposition as $d)
                        @php
                            $dc     = $dispColors[$d['disposition']] ?? ['bg' => 'var(--surface2)', 'c' => 'var(--text-dim)'];
                            $dlabel = ucwords(str_replace('_', ' ', $d['disposition']));
                            $pct    = round($d['loss'] / $totalDispLoss * 100);
                        @endphp
                        <div style="margin-bottom:10px">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                                <span style="font-size:12px;font-weight:600;color:var(--text-sub)">{{ $dlabel }}</span>
                                <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ number_format($d['loss']) }} RWF ({{ $pct }}%)</span>
                            </div>
                            <div style="height:6px;border-radius:6px;background:var(--surface2)">
                                <div style="height:100%;border-radius:6px;background:{{ $dc['c'] }};width:{{ $pct }}%;transition:width .3s ease"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="la-empty">
                    <div class="la-empty-title">No Data</div>
                    <div class="la-empty-sub">No damaged goods for this period</div>
                </div>
            @endif
        </div>

    </div>
@endif

{{-- ═══ TAB: PROBLEM PRODUCTS ═══ --}}
@if($activeTab === 'products')
    @php
        $products = $this->problemProducts;
        $maxLoss  = count($products) > 0 ? max(array_column($products, 'total_loss')) : 1;
    @endphp
    <div class="la-card">
        <div class="la-card-head">
            <div>
                <div class="la-card-title">Problem Products — Top 20 by Loss</div>
                <div class="la-card-sub">Products with the highest combined refund and damage loss value</div>
            </div>
        </div>
        @if(count($products))
            <div class="la-tbl-wrap">
                <table class="la-tbl" style="min-width:860px;table-layout:fixed">
                    <colgroup>
                        <col style="width:44px">
                        <col style="width:210px">
                        <col style="width:76px">
                        <col style="width:86px">
                        <col style="width:130px">
                        <col style="width:76px">
                        <col style="width:86px">
                        <col style="width:210px">
                    </colgroup>
                    <thead>
                        <tr style="background:var(--bg)">
                            <th colspan="2" style="border-bottom:1px solid var(--border)"></th>
                            <th colspan="3" style="text-align:center;font-size:9px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--amber);border-bottom:2px solid var(--amber);padding:5px 14px 4px">RETURNS</th>
                            <th colspan="2" style="text-align:center;font-size:9px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--violet);border-bottom:2px solid var(--violet);padding:5px 14px 4px">DAMAGED</th>
                            <th style="text-align:center;font-size:9px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--red);border-bottom:2px solid var(--red);padding:5px 14px 4px">TOTAL LOSS</th>
                        </tr>
                        <tr>
                            <th style="text-align:center">#</th>
                            <th>Product</th>
                            <th style="text-align:right">Returns</th>
                            <th style="text-align:right">Qty Ret.</th>
                            <th style="text-align:right">Refund (RWF)</th>
                            <th style="text-align:right">Incidents</th>
                            <th style="text-align:right">Qty Dmg.</th>
                            <th style="text-align:right">Impact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $i => $p)
                            @php
                                $rankClass = match(true) {
                                    $i === 0 => 'la-rank-1',
                                    $i === 1 => 'la-rank-2',
                                    $i === 2 => 'la-rank-3',
                                    default  => 'la-rank-n',
                                };
                                $pct = $maxLoss > 0 ? min(100, (int) round($p['total_loss'] / $maxLoss * 100)) : 0;
                            @endphp
                            <tr>
                                <td style="text-align:center">
                                    <span class="la-rank {{ $rankClass }}">{{ $i + 1 }}</span>
                                </td>
                                <td class="td-2l">
                                    <div class="td-2l-main" title="{{ $p['product_name'] }}">{{ $p['product_name'] }}</div>
                                </td>
                                <td class="la-num">{{ $p['return_count'] ?: '—' }}</td>
                                <td class="la-num">{{ $p['returned_quantity'] > 0 ? number_format($p['returned_quantity']) : '—' }}</td>
                                <td class="la-num" style="{{ $p['refund_amount'] > 0 ? 'color:var(--amber)' : 'color:var(--text-dim)' }}">{{ $p['refund_amount'] > 0 ? number_format($p['refund_amount']) : '—' }}</td>
                                <td class="la-num">{{ $p['damage_count'] ?: '—' }}</td>
                                <td class="la-num">{{ $p['damaged_quantity'] > 0 ? number_format($p['damaged_quantity']) : '—' }}</td>
                                <td>
                                    <div class="la-impact">
                                        <div class="la-impact-bar">
                                            <div class="la-impact-fill" style="width:{{ $pct }}%"></div>
                                        </div>
                                        <span class="la-impact-val">{{ number_format($p['total_loss']) }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="la-empty">
                <div class="la-empty-title">No Loss Data</div>
                <div class="la-empty-sub">No returns or damaged goods recorded for this period</div>
            </div>
        @endif
    </div>
@endif

{{-- ══ ApexCharts — reactive via @script ══ --}}
@script
<script>
let _laTrend = null, _laReasons = null, _laDisp = null, _laLoc = null;

function _laInitTrend() {
    const el = document.getElementById('la-trend-chart');
    if (!el) return;
    const dates   = JSON.parse(el.dataset.dates   || '[]');
    const refunds = JSON.parse(el.dataset.refunds  || '[]');
    const damaged = JSON.parse(el.dataset.damaged  || '[]');
    const total   = JSON.parse(el.dataset.total    || '[]');
    if (_laTrend) { _laTrend.destroy(); _laTrend = null; }
    if (!dates.length) {
        el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:200px;color:var(--text-dim);font-size:13px">No data for this period</div>';
        return;
    }
    const tsTotal   = dates.map((d, i) => ({ x: new Date(d).getTime(), y: total[i] }));
    const tsRefunds = dates.map((d, i) => ({ x: new Date(d).getTime(), y: refunds[i] }));
    const tsDamaged = dates.map((d, i) => ({ x: new Date(d).getTime(), y: damaged[i] }));
    _laTrend = new ApexCharts(el, {
        chart: { type: 'area', height: 300, toolbar: { show: false }, animations: { enabled: false }, background: 'transparent' },
        series: [
            { name: 'Total Loss', data: tsTotal },
            { name: 'Refunds',    data: tsRefunds },
            { name: 'Damaged',    data: tsDamaged },
        ],
        xaxis: { type: 'datetime', labels: { style: { fontSize: '11px' }, datetimeFormatter: { day: 'MMM d' }, rotate: -30, hideOverlappingLabels: true }, tickAmount: Math.min(dates.length, 10) },
        yaxis: { labels: { formatter: v => v >= 1000 ? Math.round(v/1000)+'k' : Math.round(v), style: { fontSize: '11px' } } },
        colors: ['#e11d48', '#d97706', '#7c3aed'],
        fill: { type: 'gradient', gradient: { opacityFrom: 0.22, opacityTo: 0.01 } },
        stroke: { curve: 'smooth', width: [2.5, 2, 2] },
        dataLabels: { enabled: false },
        tooltip: { x: { format: 'MMM d, yyyy' }, y: { formatter: v => 'RWF ' + Math.round(v).toLocaleString() } },
        grid: { borderColor: '#e2e6f3', strokeDashArray: 3 },
        legend: { position: 'top', fontSize: '12px' },
    });
    _laTrend.render();
}

function _laInitReasons() {
    const el = document.getElementById('la-reasons-chart');
    if (!el) return;
    const labels = JSON.parse(el.dataset.labels || '[]');
    const counts = JSON.parse(el.dataset.counts || '[]');
    if (_laReasons) { _laReasons.destroy(); _laReasons = null; }
    if (!labels.length) return;
    _laReasons = new ApexCharts(el, {
        chart: { type: 'bar', height: 220, toolbar: { show: false }, animations: { enabled: false }, background: 'transparent' },
        series: [{ name: 'Returns', data: counts }],
        xaxis: { categories: labels, labels: { style: { fontSize: '11px' } } },
        yaxis: { labels: { style: { fontSize: '11px' } } },
        colors: ['#e11d48', '#d97706', '#7c3aed', '#3b6fd4', '#0e9e86', '#db2777'],
        plotOptions: { bar: { horizontal: true, borderRadius: 4, distributed: true } },
        dataLabels: { enabled: true, style: { fontSize: '11px' } },
        legend: { show: false },
        grid: { borderColor: '#e2e6f3', strokeDashArray: 3 },
    });
    _laReasons.render();
}

function _laInitDisp() {
    const el = document.getElementById('la-disposition-chart');
    if (!el) return;
    const labels = JSON.parse(el.dataset.labels || '[]');
    const counts = JSON.parse(el.dataset.counts || '[]');
    if (_laDisp) { _laDisp.destroy(); _laDisp = null; }
    if (!labels.length) return;
    const clean = labels.map(l => l.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
    _laDisp = new ApexCharts(el, {
        chart: { type: 'donut', height: 260, animations: { enabled: false }, background: 'transparent' },
        series: counts,
        labels: clean,
        colors: ['#d97706', '#e11d48', '#3b6fd4', '#0e9e86', '#7c3aed', '#db2777'],
        dataLabels: { style: { fontSize: '11px' } },
        legend: { position: 'bottom', fontSize: '12px' },
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Incidents', fontSize: '11px', color: '#7a81a0', formatter: w => w.globals.seriesTotals.reduce((a,b) => a+b, 0) } } } } },
    });
    _laDisp.render();
}

function _laInitLoc() {
    const el = document.getElementById('la-location-chart');
    if (!el) return;
    const shops     = JSON.parse(el.dataset.shops     || '[]');
    const returns_  = JSON.parse(el.dataset.returns   || '[]');
    const exchanges = JSON.parse(el.dataset.exchanges || '[]');
    if (_laLoc) { _laLoc.destroy(); _laLoc = null; }
    if (!shops.length) return;
    _laLoc = new ApexCharts(el, {
        chart: { type: 'bar', height: 220, toolbar: { show: false }, animations: { enabled: false }, background: 'transparent' },
        series: [
            { name: 'Refunds',   data: returns_ },
            { name: 'Exchanges', data: exchanges },
        ],
        xaxis: { categories: shops, labels: { style: { fontSize: '11px' } } },
        yaxis: { labels: { style: { fontSize: '11px' } } },
        colors: ['#e11d48', '#d97706'],
        dataLabels: { enabled: false },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%', grouped: true } },
        legend: { position: 'top', fontSize: '12px' },
        grid: { borderColor: '#e2e6f3', strokeDashArray: 3 },
    });
    _laLoc.render();
}

function _laInitAll() {
    _laInitTrend();
    _laInitReasons();
    _laInitDisp();
    _laInitLoc();
}

_laInitAll();

Livewire.hook('commit', ({ succeed }) => {
    succeed(() => requestAnimationFrame(_laInitAll));
});
</script>
@endscript

</div>
