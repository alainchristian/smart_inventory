<div style="font-family:var(--font)">
<style>
/* ── Transfer Performance (tp-) ───────────────────────────────── */
.tp-title        { font-size:22px;font-weight:800;color:var(--text);letter-spacing:-.5px }
.tp-subtitle     { font-size:13px;color:var(--text-dim);margin-top:3px }

/* Filters */
.tp-filters      { margin-bottom:20px;min-width:0 }
.tp-ctrl-wrap    { display:flex;align-items:center;gap:8px }
.tp-ctrl-label   { font-size:12px;color:var(--text-dim);flex-shrink:0;white-space:nowrap }
.tp-ctrl-select  { padding:5px 10px;border:1.5px solid var(--border);border-radius:8px;
                   font-size:12px;background:var(--surface);color:var(--text);
                   outline:none;cursor:pointer;font-family:var(--font) }
.tp-ctrl-select:focus { border-color:var(--accent) }

/* KPI strip */
.tp-kpis         { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px }
.tp-kpi          { background:var(--surface);border:none;border-radius:var(--r);
                   box-shadow:var(--shadow-card);padding:20px 18px;
                   display:flex;flex-direction:column;gap:14px;transition:box-shadow var(--tr) }
.tp-kpi:hover    { box-shadow:var(--shadow-card-hover) }
.tp-kpi-row      { display:flex;align-items:center;gap:10px }
.tp-kpi-icon     { width:34px;height:34px;border-radius:9px;display:flex;align-items:center;
                   justify-content:center;flex-shrink:0 }
.tp-kpi-body     { flex:1;min-width:0 }
.tp-kpi-label    { font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                   color:var(--text-dim);line-height:1.2 }
.tp-kpi-sub      { font-size:11px;color:var(--text-dim);margin-top:2px }
.tp-kpi-val      { font-size:22px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;line-height:1 }
.tp-kpi-bar      { height:3px;border-radius:3px }
.tp-kpi-divider  { height:1px;background:var(--border) }
.tp-kpi-footer   { display:grid;grid-template-columns:repeat(3,1fr) }
.tp-kpi-stat     { display:flex;flex-direction:column;align-items:center;gap:2px;padding:3px 0;min-width:0 }
.tp-kpi-stat-v   { font-size:11px;font-weight:700;font-family:var(--mono);color:var(--text-sub);
                   max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap }
.tp-kpi-stat-l   { font-size:10px;color:var(--text-dim);letter-spacing:.3px;text-align:center }

/* Tabs */
.tp-tabs         { display:grid;grid-template-columns:repeat(4,1fr);
                   background:var(--surface);box-shadow:var(--shadow-card);
                   border-radius:var(--r);margin-bottom:20px;overflow:hidden }
.tp-tab          { display:flex;align-items:center;justify-content:center;gap:6px;
                   padding:12px 10px;border:none;border-radius:0;
                   border-bottom:2.5px solid transparent;border-right:1px solid var(--border);
                   cursor:pointer;font-size:12px;font-weight:600;
                   font-family:var(--font);background:transparent;color:var(--text-dim);
                   transition:all var(--tr);white-space:nowrap }
.tp-tab:last-child  { border-right:none }
.tp-tab:hover       { background:var(--surface2);color:var(--text);border-bottom-color:var(--border-hi) }
.tp-tab.active      { background:var(--accent-dim);color:var(--accent);border-bottom-color:var(--accent) }

/* Cards */
.tp-card         { background:var(--surface);border:none;border-radius:var(--r);
                   box-shadow:var(--shadow-card);padding:20px;margin-bottom:16px }
.tp-card-head    { display:flex;align-items:flex-start;justify-content:space-between;
                   margin-bottom:18px;gap:12px }
.tp-card-title   { font-size:14px;font-weight:700;color:var(--text) }
.tp-card-sub     { font-size:12px;color:var(--text-dim);margin-top:2px }

/* 2-col grid */
.tp-grid-2       { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px }

/* Tables */
.tp-tbl-wrap     { overflow-x:auto;-webkit-overflow-scrolling:touch }
.tp-tbl          { width:100%;border-collapse:collapse }
.tp-tbl thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.tp-tbl thead th { padding:9px 14px;text-align:left;font-size:10.5px;font-weight:700;
                   letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.tp-tbl tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.tp-tbl tbody tr:last-child { border-bottom:none }
.tp-tbl tbody tr:hover { background:var(--surface2) }
.tp-tbl tfoot tr { border-top:2px solid var(--border);background:var(--bg) }
.tp-tbl td       { padding:11px 14px;font-size:13px;vertical-align:middle }
.tp-num          { font-family:var(--mono);text-align:right;white-space:nowrap }

/* Route success bar */
.tp-success-bar  { display:flex;align-items:center;gap:8px }
.tp-success-track { flex:1;height:4px;border-radius:4px;background:var(--surface2);min-width:40px }
.tp-success-fill  { height:100%;border-radius:4px }
.tp-success-val   { font-family:var(--mono);font-size:12px;font-weight:700;white-space:nowrap }

/* Status badge */
.tp-status       { display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;
                   padding:2px 8px;border-radius:6px;white-space:nowrap }
.tp-status-dot   { width:5px;height:5px;border-radius:50%;flex-shrink:0 }

/* Pipeline stepper */
.tp-stepper      { display:flex;align-items:center;gap:0;margin-bottom:20px }
.tp-step         { flex:1;display:flex;flex-direction:column;align-items:center;position:relative }
.tp-step-circle  { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;
                   justify-content:center;font-size:11px;font-weight:700;font-family:var(--mono);
                   z-index:1;position:relative }
.tp-step-line    { position:absolute;top:18px;left:50%;width:100%;height:2px;z-index:0 }
.tp-step:last-child .tp-step-line { display:none }
.tp-step-label   { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;
                   color:var(--text-dim);margin-top:6px;text-align:center }
.tp-step-count   { font-size:11px;font-weight:700;font-family:var(--mono);color:var(--text-sub);
                   margin-top:2px }

/* Discrepancy card */
.tp-disc-item    { padding:14px 16px;border-left:3px solid var(--amber);
                   background:var(--amber-dim);border-radius:9px;margin-bottom:10px }
.tp-disc-item:last-child { margin-bottom:0 }
.tp-disc-head    { display:flex;align-items:center;justify-content:space-between;margin-bottom:6px }
.tp-disc-num     { font-size:13px;font-weight:700;color:var(--text);font-family:var(--mono) }
.tp-disc-date    { font-size:11px;color:var(--text-dim) }
.tp-disc-route   { font-size:12px;color:var(--text-sub);margin-bottom:4px }
.tp-disc-note    { font-size:12px;color:var(--text-dim);font-style:italic }

/* Rank badges */
.tp-rank         { width:28px;height:28px;border-radius:8px;display:inline-flex;align-items:center;
                   justify-content:center;font-size:11px;font-weight:700;font-family:var(--mono) }
.tp-rank-1       { background:rgba(217,119,6,.15);color:var(--amber) }
.tp-rank-2       { background:rgba(100,116,139,.12);color:var(--text-sub) }
.tp-rank-3       { background:rgba(180,120,50,.12);color:#c97d3a }
.tp-rank-n       { background:var(--surface2);color:var(--text-dim) }

/* Discrepancy mini-badge in product table */
.tp-disc-badge   { display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;
                   padding:2px 8px;border-radius:6px;white-space:nowrap }

/* Empty state */
.tp-empty        { padding:48px 20px;text-align:center }
.tp-empty-title  { font-size:14px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.tp-empty-sub    { font-size:12px;color:var(--text-dim) }

/* Responsive */
@media(max-width:900px) {
    .tp-kpis    { grid-template-columns:1fr 1fr;gap:10px }
    .tp-grid-2  { grid-template-columns:1fr }
    .tp-stepper { flex-wrap:wrap;gap:12px }
    .tp-step-line { display:none }
}
@media(max-width:640px) {
    .tp-kpis    { grid-template-columns:1fr 1fr;gap:8px }
    .tp-kpi     { padding:14px 12px;gap:10px }
    .tp-kpi-val { font-size:18px }
    .tp-kpi-footer { grid-template-columns:repeat(3,1fr) }
    .tp-tabs    { display:flex;overflow-x:auto;-webkit-overflow-scrolling:touch;
                  scrollbar-width:none;flex-wrap:nowrap }
    .tp-tabs::-webkit-scrollbar { display:none }
    .tp-tab     { flex-shrink:0;min-width:100px;padding:11px 14px }
    .tp-tab svg { display:none }
}
@media(max-width:480px) {
    .tp-hide-mob   { display:none !important }
    .tp-kpis       { grid-template-columns:1fr }
}
</style>

{{-- Page Header --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap">
    <div>
        <div class="tp-title">Transfer Performance</div>
        <div class="tp-subtitle">Logistics efficiency and discrepancy tracking &mdash; {{ $dateFrom }} to {{ $dateTo }}</div>
    </div>
</div>

{{-- Period + Status Filter --}}
<div class="tp-filters">
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
            <div class="db-period-ctrl-seg tp-ctrl-wrap">
                <span class="tp-ctrl-label">Status</span>
                <select wire:model.live="statusFilter" class="tp-ctrl-select">
                    <option value="">All Statuses</option>
                    @foreach($this->transferStatuses as $status)
                        <option value="{{ $status->value }}">{{ ucwords(str_replace('_', ' ', $status->value)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
@php
    $kpis       = $this->transferKpis;
    $statusDist = $this->statusDistribution;
    $statusMap  = collect($statusDist)->pluck('count', 'status');

    $receivedCount  = (int) ($statusMap['received']  ?? 0);
    $inTransitCount = (int) ($statusMap['in_transit'] ?? 0);
    $pendingCount   = (int) ($statusMap['pending']    ?? 0);
    $cancelCount    = (int) ($statusMap['cancelled']  ?? 0);

    $days    = max(1, \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1);
    $avgDaily = $kpis['total_transfers'] > 0 ? round($kpis['total_transfers'] / $days, 1) : 0;

    $compHrs      = $kpis['avg_completion_hours'];
    $compLabel    = $compHrs < 24 ? 'Fast' : ($compHrs < 48 ? 'On Track' : ($compHrs < 72 ? 'Slow' : 'Very Slow'));
    $compColor    = $compHrs < 24 ? 'var(--green)' : ($compHrs < 48 ? 'var(--accent)' : ($compHrs < 72 ? 'var(--amber)' : 'var(--red)'));
    $compIconBg   = $compHrs < 24 ? 'var(--green-dim)' : ($compHrs < 48 ? 'var(--accent-dim)' : ($compHrs < 72 ? 'var(--amber-dim)' : 'var(--red-dim)'));

    $discRate     = $kpis['discrepancy_rate'];
    $discLabel    = $discRate > 10 ? 'High Risk' : ($discRate > 5 ? 'Medium' : 'Low Risk');
    $discColor    = $discRate > 10 ? 'var(--red)' : ($discRate > 5 ? 'var(--amber)' : 'var(--green)');

    $successRate  = $kpis['total_transfers'] > 0
                    ? round(($kpis['total_transfers'] - $kpis['discrepancy_count']) / $kpis['total_transfers'] * 100, 1)
                    : 100;
@endphp
<div class="tp-kpis">

    {{-- Total Transfers --}}
    <div class="tp-kpi">
        <div class="tp-kpi-row">
            <div class="tp-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <div class="tp-kpi-body">
                <div class="tp-kpi-label">Total Transfers</div>
                <div class="tp-kpi-sub">For selected period</div>
            </div>
        </div>
        <div class="tp-kpi-val" style="color:var(--violet)">{{ number_format($kpis['total_transfers']) }}</div>
        <div class="tp-kpi-bar" style="background:var(--violet-dim)">
            <div style="height:100%;border-radius:3px;background:var(--violet);width:100%"></div>
        </div>
        <div class="tp-kpi-divider"></div>
        <div class="tp-kpi-footer">
            <div class="tp-kpi-stat">
                <span class="tp-kpi-stat-v">{{ number_format($avgDaily, 1) }}</span>
                <span class="tp-kpi-stat-l">Per Day</span>
            </div>
            <div class="tp-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="tp-kpi-stat-v">{{ $inTransitCount }}</span>
                <span class="tp-kpi-stat-l">In Transit</span>
            </div>
            <div class="tp-kpi-stat">
                <span class="tp-kpi-stat-v">{{ $kpis['discrepancy_count'] }}</span>
                <span class="tp-kpi-stat-l">Issues</span>
            </div>
        </div>
    </div>

    {{-- Avg Completion Time --}}
    <div class="tp-kpi">
        <div class="tp-kpi-row">
            <div class="tp-kpi-icon" style="background:{{ $compIconBg }};color:{{ $compColor }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="tp-kpi-body">
                <div class="tp-kpi-label">Avg Completion</div>
                <div class="tp-kpi-sub">Request to receipt</div>
            </div>
        </div>
        <div class="tp-kpi-val" style="color:{{ $compColor }}">{{ number_format($compHrs, 1) }}<span style="font-size:14px;font-weight:600">h</span></div>
        <div class="tp-kpi-divider"></div>
        <div class="tp-kpi-footer">
            <div class="tp-kpi-stat">
                <span class="tp-kpi-stat-v">{{ $receivedCount }}</span>
                <span class="tp-kpi-stat-l">Completed</span>
            </div>
            <div class="tp-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="tp-kpi-stat-v" style="color:{{ $compColor }}">{{ $compLabel }}</span>
                <span class="tp-kpi-stat-l">Performance</span>
            </div>
            <div class="tp-kpi-stat">
                <span class="tp-kpi-stat-v">&lt; 48h</span>
                <span class="tp-kpi-stat-l">Target</span>
            </div>
        </div>
    </div>

    {{-- Discrepancy Rate --}}
    <div class="tp-kpi">
        <div class="tp-kpi-row">
            <div class="tp-kpi-icon" style="background:var(--amber-dim);color:var(--amber)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="tp-kpi-body">
                <div class="tp-kpi-label">Discrepancy Rate</div>
                <div class="tp-kpi-sub">Transfers with issues</div>
            </div>
        </div>
        <div class="tp-kpi-val" style="color:{{ $discColor }}">{{ number_format($discRate, 2) }}<span style="font-size:16px;font-weight:700">%</span></div>
        <div class="tp-kpi-divider"></div>
        <div class="tp-kpi-footer">
            <div class="tp-kpi-stat">
                <span class="tp-kpi-stat-v">{{ $kpis['discrepancy_count'] }}</span>
                <span class="tp-kpi-stat-l">Incidents</span>
            </div>
            <div class="tp-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="tp-kpi-stat-v" style="color:{{ $discColor }}">{{ $discLabel }}</span>
                <span class="tp-kpi-stat-l">Risk Level</span>
            </div>
            <div class="tp-kpi-stat">
                <span class="tp-kpi-stat-v">&lt; 5%</span>
                <span class="tp-kpi-stat-l">Target</span>
            </div>
        </div>
    </div>

    {{-- Success Rate --}}
    <div class="tp-kpi">
        <div class="tp-kpi-row">
            <div class="tp-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="tp-kpi-body">
                <div class="tp-kpi-label">Success Rate</div>
                <div class="tp-kpi-sub">Clean transfers</div>
            </div>
        </div>
        <div class="tp-kpi-val" style="color:{{ $successRate >= 95 ? 'var(--green)' : ($successRate >= 80 ? 'var(--accent)' : 'var(--amber)') }}">{{ number_format($successRate, 1) }}<span style="font-size:16px;font-weight:700">%</span></div>
        <div class="tp-kpi-divider"></div>
        <div class="tp-kpi-footer">
            <div class="tp-kpi-stat">
                <span class="tp-kpi-stat-v">{{ $receivedCount }}</span>
                <span class="tp-kpi-stat-l">Received</span>
            </div>
            <div class="tp-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="tp-kpi-stat-v">{{ $pendingCount }}</span>
                <span class="tp-kpi-stat-l">Pending</span>
            </div>
            <div class="tp-kpi-stat">
                <span class="tp-kpi-stat-v">{{ $cancelCount }}</span>
                <span class="tp-kpi-stat-l">Cancelled</span>
            </div>
        </div>
    </div>

</div>

{{-- Tab Strip --}}
<div class="tp-tabs">
    <button class="tp-tab {{ $activeTab === 'overview' ? 'active' : '' }}" wire:click="setTab('overview')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        <span>Trends</span>
    </button>
    <button class="tp-tab {{ $activeTab === 'pipeline' ? 'active' : '' }}" wire:click="setTab('pipeline')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <span>Pipeline</span>
    </button>
    <button class="tp-tab {{ $activeTab === 'routes' ? 'active' : '' }}" wire:click="setTab('routes')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        <span>Routes</span>
    </button>
    <button class="tp-tab {{ $activeTab === 'products' ? 'active' : '' }}" wire:click="setTab('products')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path stroke-linecap="round" d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
        <span>Products &amp; Issues</span>
    </button>
</div>

{{-- ═══ TAB: TRENDS ═══ --}}
@if($activeTab === 'overview')
    @php
        $trend       = $this->transferVolumeTrend;
        $trendDates  = array_map(fn($r) => strtotime($r['date']) * 1000, $trend);
        $trendCounts = array_column($trend, 'count');

        $peakRow   = count($trend) ? collect($trend)->sortByDesc('count')->first() : null;
        $peakDate  = $peakRow ? $peakRow['date'] : null;
        $peakCount = $peakRow ? $peakRow['count'] : 0;
        $daysWithActivity = count(array_filter($trendCounts, fn($v) => $v > 0));
    @endphp
    <div class="tp-card">
        <div class="tp-card-head">
            <div>
                <div class="tp-card-title">Transfer Volume Trend</div>
                <div class="tp-card-sub">Daily transfers requested in the selected period</div>
            </div>
        </div>
        <div wire:key="tp-trend-wrap-{{ $dateFrom }}-{{ $dateTo }}-{{ $statusFilter }}">
            <div id="tp-trend-chart"
                 data-timestamps='@json($trendDates)'
                 data-counts='@json($trendCounts)'
                 style="min-height:300px"></div>
        </div>
    </div>

    @if(count($trend))
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
        <div style="background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card);padding:16px 18px">
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);margin-bottom:8px">Busiest Day</div>
            <div style="font-size:18px;font-weight:800;font-family:var(--mono);color:var(--violet)">
                {{ $peakDate ? \Carbon\Carbon::parse($peakDate)->format('M j') : '—' }}
            </div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:4px">{{ $peakCount }} transfers</div>
        </div>
        <div style="background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card);padding:16px 18px">
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);margin-bottom:8px">Daily Average</div>
            <div style="font-size:18px;font-weight:800;font-family:var(--mono);color:var(--text)">{{ number_format($avgDaily, 1) }}</div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:4px">transfers per day</div>
        </div>
        <div style="background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card);padding:16px 18px">
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);margin-bottom:8px">Active Days</div>
            <div style="font-size:18px;font-weight:800;font-family:var(--mono);color:var(--text)">
                {{ $daysWithActivity }}<span style="font-size:13px;color:var(--text-dim)"> / {{ $days }}</span>
            </div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:4px">
                {{ $days > 0 ? round($daysWithActivity / $days * 100) : 0 }}% of days had transfers
            </div>
        </div>
    </div>
    @endif
@endif

{{-- ═══ TAB: PIPELINE ═══ --}}
@if($activeTab === 'pipeline')
    @php
        $statusColors = [
            'pending'    => ['bg' => 'var(--amber-dim)',  'c' => 'var(--amber)'],
            'approved'   => ['bg' => 'var(--accent-dim)', 'c' => 'var(--accent)'],
            'in_transit' => ['bg' => 'var(--violet-dim)', 'c' => 'var(--violet)'],
            'delivered'  => ['bg' => 'var(--green-dim)',  'c' => 'var(--green)'],
            'received'   => ['bg' => 'var(--green-dim)',  'c' => 'var(--green)'],
            'rejected'   => ['bg' => 'var(--red-dim)',    'c' => 'var(--red)'],
            'cancelled'  => ['bg' => 'var(--surface2)',   'c' => 'var(--text-dim)'],
        ];
        $stagesOrdered = ['pending', 'approved', 'in_transit', 'delivered', 'received'];
        $sideStatuses  = ['rejected', 'cancelled'];
        $compTime      = $this->completionTimeDistribution;
        $compBucketsOrdered = ['0-24 hours', '24-48 hours', '48-72 hours', '72+ hours'];
        $compBucketColors   = ['#0e9e86', '#3b6fd4', '#d97706', '#e11d48'];
        $compMap = collect($compTime)->pluck('count', 'time_bucket');
        $compCounts = array_map(fn($b) => (int) ($compMap[$b] ?? 0), $compBucketsOrdered);
    @endphp

    {{-- Pipeline Stepper --}}
    <div class="tp-card">
        <div class="tp-card-head">
            <div>
                <div class="tp-card-title">Transfer Pipeline</div>
                <div class="tp-card-sub">Active status breakdown across the 5 main stages</div>
            </div>
        </div>
        <div class="tp-stepper">
            @foreach($stagesOrdered as $stage)
                @php
                    $sc = $statusColors[$stage];
                    $cnt = (int) ($statusMap[$stage] ?? 0);
                    $stageLabel = ucwords(str_replace('_', ' ', $stage));
                @endphp
                <div class="tp-step">
                    <div class="tp-step-line" style="background:var(--border)"></div>
                    <div class="tp-step-circle" style="background:{{ $sc['bg'] }};color:{{ $sc['c'] }};border:2px solid {{ $sc['c'] }}">
                        {{ $cnt }}
                    </div>
                    <div class="tp-step-label">{{ $stageLabel }}</div>
                    <div class="tp-step-count">&nbsp;</div>
                </div>
            @endforeach
        </div>
        @if(($statusMap['rejected'] ?? 0) > 0 || ($statusMap['cancelled'] ?? 0) > 0)
        <div style="display:flex;gap:12px;flex-wrap:wrap;padding-top:12px;border-top:1px solid var(--border)">
            @foreach($sideStatuses as $stage)
                @if(($statusMap[$stage] ?? 0) > 0)
                @php $sc = $statusColors[$stage]; $cnt = (int) ($statusMap[$stage] ?? 0); @endphp
                <span class="tp-status" style="background:{{ $sc['bg'] }};color:{{ $sc['c'] }}">
                    <span class="tp-status-dot" style="background:{{ $sc['c'] }}"></span>
                    {{ ucwords(str_replace('_', ' ', $stage)) }}: {{ $cnt }}
                </span>
                @endif
            @endforeach
        </div>
        @endif
    </div>

    <div class="tp-grid-2">
        {{-- Status Distribution Donut --}}
        <div class="tp-card" style="margin-bottom:0">
            <div class="tp-card-head">
                <div>
                    <div class="tp-card-title">Status Distribution</div>
                    <div class="tp-card-sub">All statuses in the selected period</div>
                </div>
            </div>
            @if(count($statusDist))
                <div wire:key="tp-status-wrap-{{ $dateFrom }}-{{ $dateTo }}">
                    <div id="tp-status-chart"
                         data-labels='@json(array_column($statusDist, "status"))'
                         data-counts='@json(array_column($statusDist, "count"))'
                         style="min-height:260px"></div>
                </div>
                <div style="margin-top:12px">
                    <table class="tp-tbl" style="font-size:12px">
                        <thead><tr><th>Status</th><th style="text-align:right">Count</th><th style="text-align:right">Share</th></tr></thead>
                        <tbody>
                            @php $totalDist = collect($statusDist)->sum('count') ?: 1 @endphp
                            @foreach($statusDist as $sd)
                                @php $sc = $statusColors[$sd['status']] ?? ['bg'=>'var(--surface2)','c'=>'var(--text-dim)']; @endphp
                                <tr>
                                    <td>
                                        <span class="tp-status" style="background:{{ $sc['bg'] }};color:{{ $sc['c'] }}">
                                            <span class="tp-status-dot" style="background:{{ $sc['c'] }}"></span>
                                            {{ ucwords(str_replace('_', ' ', $sd['status'])) }}
                                        </span>
                                    </td>
                                    <td class="tp-num">{{ $sd['count'] }}</td>
                                    <td class="tp-num" style="color:var(--text-dim)">{{ round($sd['count'] / $totalDist * 100) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="tp-empty"><div class="tp-empty-title">No Status Data</div></div>
            @endif
        </div>

        {{-- Completion Time Distribution --}}
        <div class="tp-card" style="margin-bottom:0">
            <div class="tp-card-head">
                <div>
                    <div class="tp-card-title">Completion Time</div>
                    <div class="tp-card-sub">How fast completed transfers moved from request to receipt</div>
                </div>
            </div>
            @if(count($compTime))
                <div wire:key="tp-comp-wrap-{{ $dateFrom }}-{{ $dateTo }}">
                    <div id="tp-comp-chart"
                         data-buckets='@json($compBucketsOrdered)'
                         data-counts='@json($compCounts)'
                         style="min-height:260px"></div>
                </div>
                <div style="margin-top:12px">
                    @php $totalComp = max(array_sum($compCounts), 1) @endphp
                    @foreach($compBucketsOrdered as $bi => $bucket)
                        @php $cnt = $compCounts[$bi]; $pct = round($cnt / $totalComp * 100) @endphp
                        @if($cnt > 0)
                        <div style="margin-bottom:10px">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px">
                                <span style="font-size:12px;font-weight:600;color:var(--text-sub)">{{ $bucket }}</span>
                                <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $cnt }} ({{ $pct }}%)</span>
                            </div>
                            <div style="height:5px;border-radius:5px;background:var(--surface2)">
                                <div style="height:100%;border-radius:5px;background:{{ $compBucketColors[$bi] }};width:{{ $pct }}%"></div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="tp-empty">
                    <div class="tp-empty-title">No Completion Data</div>
                    <div class="tp-empty-sub">No transfers have been fully received yet</div>
                </div>
            @endif
        </div>
    </div>
@endif

{{-- ═══ TAB: ROUTES ═══ --}}
@if($activeTab === 'routes')
    @php $routes = $this->transferRoutes @endphp

    {{-- Routes Matrix --}}
    <div class="tp-card">
        <div class="tp-card-head">
            <div>
                <div class="tp-card-title">Transfer Routes — Warehouse → Shop</div>
                <div class="tp-card-sub">Volume and quality per route combination</div>
            </div>
        </div>
        @if(count($routes))
            <div class="tp-tbl-wrap">
                <table class="tp-tbl" style="min-width:640px;table-layout:fixed">
                    <colgroup>
                        <col style="width:180px"><col style="width:180px">
                        <col style="width:100px"><col style="width:100px"><col style="width:230px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>From Warehouse</th>
                            <th>To Shop</th>
                            <th style="text-align:right">Transfers</th>
                            <th style="text-align:right">Issues</th>
                            <th>Success Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routes as $r)
                            @php
                                $sr = $r['transfer_count'] > 0
                                    ? (($r['transfer_count'] - $r['discrepancy_count']) / $r['transfer_count']) * 100
                                    : 100;
                                $srColor = $sr >= 95 ? '#0e9e86' : ($sr >= 80 ? '#d97706' : '#e11d48');
                            @endphp
                            <tr>
                                <td class="td-2l">
                                    <div class="td-2l-main">{{ $r['warehouse_name'] }}</div>
                                    <span class="td-2l-badge" style="background:var(--green-dim);color:var(--green)">Warehouse</span>
                                </td>
                                <td class="td-2l">
                                    <div class="td-2l-main">{{ $r['shop_name'] }}</div>
                                    <span class="td-2l-badge" style="background:var(--accent-dim);color:var(--accent)">Shop</span>
                                </td>
                                <td class="tp-num">{{ number_format($r['transfer_count']) }}</td>
                                <td class="tp-num">
                                    @if($r['discrepancy_count'] > 0)
                                        <span style="background:var(--amber-dim);color:var(--amber);padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700">{{ $r['discrepancy_count'] }}</span>
                                    @else
                                        <span style="color:var(--green);font-weight:700;font-family:var(--mono)">0</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="tp-success-bar">
                                        <div class="tp-success-track">
                                            <div class="tp-success-fill" style="width:{{ min(100, $sr) }}%;background:{{ $srColor }}"></div>
                                        </div>
                                        <span class="tp-success-val" style="color:{{ $srColor }}">{{ number_format($sr, 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="tp-empty">
                <div class="tp-empty-title">No Route Data</div>
                <div class="tp-empty-sub">No transfers recorded for this period</div>
            </div>
        @endif
    </div>

    {{-- Warehouse Efficiency --}}
    @php $warehouseEff = $this->warehouseEfficiency @endphp
    <div class="tp-card">
        <div class="tp-card-head">
            <div>
                <div class="tp-card-title">Warehouse Efficiency</div>
                <div class="tp-card-sub">Per-warehouse metrics for completed transfers (received status only)</div>
            </div>
        </div>
        @if(count($warehouseEff))
            <div class="tp-tbl-wrap">
                <table class="tp-tbl" style="min-width:580px;table-layout:fixed">
                    <colgroup>
                        <col style="width:180px"><col style="width:100px">
                        <col style="width:120px"><col style="width:100px"><col style="width:200px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Warehouse</th>
                            <th style="text-align:right">Completed</th>
                            <th style="text-align:right">Avg Time</th>
                            <th style="text-align:right">Issues</th>
                            <th>Success Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warehouseEff as $wh)
                            @php
                                $wsr = 100 - $wh['discrepancy_rate'];
                                $wsrColor = $wsr >= 95 ? '#0e9e86' : ($wsr >= 80 ? '#d97706' : '#e11d48');
                                $avgHrsColor = $wh['avg_completion_hours'] < 24 ? 'var(--green)' : ($wh['avg_completion_hours'] < 48 ? 'var(--accent)' : ($wh['avg_completion_hours'] < 72 ? 'var(--amber)' : 'var(--red)'));
                            @endphp
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <div style="width:28px;height:28px;border-radius:8px;background:var(--green-dim);color:var(--green);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                                        </div>
                                        <span style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $wh['warehouse_name'] }}</span>
                                    </div>
                                </td>
                                <td class="tp-num">{{ number_format($wh['total_transfers']) }}</td>
                                <td class="tp-num" style="color:{{ $avgHrsColor }};font-weight:700">{{ number_format($wh['avg_completion_hours'], 1) }}h</td>
                                <td class="tp-num">
                                    @if($wh['discrepancy_count'] > 0)
                                        <span style="background:var(--amber-dim);color:var(--amber);padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700">{{ $wh['discrepancy_count'] }}</span>
                                    @else
                                        <span style="color:var(--green);font-weight:700;font-family:var(--mono)">0</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="tp-success-bar">
                                        <div class="tp-success-track">
                                            <div class="tp-success-fill" style="width:{{ min(100, $wsr) }}%;background:{{ $wsrColor }}"></div>
                                        </div>
                                        <span class="tp-success-val" style="color:{{ $wsrColor }}">{{ number_format($wsr, 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="tp-empty">
                <div class="tp-empty-title">No Warehouse Data</div>
                <div class="tp-empty-sub">No completed transfers for this period</div>
            </div>
        @endif
    </div>
@endif

{{-- ═══ TAB: PRODUCTS & ISSUES ═══ --}}
@if($activeTab === 'products')
    @php
        $products = $this->mostTransferredProducts;
        $maxSent  = count($products) ? max(array_column($products, 'total_sent')) : 1;
        $discrepancies = $this->recentDiscrepancies;
    @endphp
    <div class="tp-grid-2">

        {{-- Most Transferred Products --}}
        <div class="tp-card" style="margin-bottom:0">
            <div class="tp-card-head">
                <div>
                    <div class="tp-card-title">Most Transferred Products</div>
                    <div class="tp-card-sub">Top 20 products by quantity shipped</div>
                </div>
            </div>
            @if(count($products))
                <div class="tp-tbl-wrap">
                    <table class="tp-tbl" style="min-width:420px;table-layout:fixed">
                        <colgroup>
                            <col style="width:38px"><col style="width:auto">
                            <col style="width:80px"><col style="width:80px"><col style="width:80px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th style="text-align:center">#</th>
                                <th>Product</th>
                                <th style="text-align:right">Sent</th>
                                <th style="text-align:right">Received</th>
                                <th style="text-align:right">Gap</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $i => $p)
                                @php
                                    $rankClass = match(true) {
                                        $i === 0 => 'tp-rank-1',
                                        $i === 1 => 'tp-rank-2',
                                        $i === 2 => 'tp-rank-3',
                                        default  => 'tp-rank-n',
                                    };
                                    $hasDisc = ($p['total_discrepancy'] ?? 0) > 0;
                                    $disc = (int) ($p['total_discrepancy'] ?? 0);
                                @endphp
                                <tr>
                                    <td style="text-align:center">
                                        <span class="tp-rank {{ $rankClass }}">{{ $i + 1 }}</span>
                                    </td>
                                    <td class="td-2l">
                                        <div class="td-2l-main" title="{{ $p['product_name'] }}">{{ $p['product_name'] }}</div>
                                        <div class="td-2l-sub">{{ $p['transfer_count'] }} {{ Str::plural('transfer', $p['transfer_count']) }}</div>
                                    </td>
                                    <td class="tp-num">{{ number_format($p['total_sent']) }}</td>
                                    <td class="tp-num" style="{{ $hasDisc ? 'color:var(--amber)' : 'color:var(--green)' }}">
                                        {{ number_format($p['total_received']) }}
                                    </td>
                                    <td class="tp-num">
                                        @if($disc > 0)
                                            <span class="tp-disc-badge" style="background:var(--amber-dim);color:var(--amber)">
                                                -{{ $disc }}
                                            </span>
                                        @else
                                            <span style="color:var(--text-dim)">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="tp-empty">
                    <div class="tp-empty-title">No Product Data</div>
                    <div class="tp-empty-sub">No transfer items recorded for this period</div>
                </div>
            @endif
        </div>

        {{-- Recent Discrepancies --}}
        <div class="tp-card" style="margin-bottom:0">
            <div class="tp-card-head">
                <div>
                    <div class="tp-card-title">Recent Discrepancies</div>
                    <div class="tp-card-sub">Last 10 transfers flagged with quantity mismatches</div>
                </div>
                @if(count($discrepancies))
                <span style="background:var(--amber-dim);color:var(--amber);font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap">
                    {{ count($discrepancies) }} found
                </span>
                @endif
            </div>
            @if(count($discrepancies))
                <div style="max-height:480px;overflow-y:auto">
                    @foreach($discrepancies as $d)
                        <div class="tp-disc-item">
                            <div class="tp-disc-head">
                                <span class="tp-disc-num">{{ $d['transfer_number'] }}</span>
                                <span class="tp-disc-date">{{ \Carbon\Carbon::parse($d['created_at'])->format('M j, Y') }}</span>
                            </div>
                            <div class="tp-disc-route">
                                <span style="font-weight:600">{{ $d['from_warehouse'] }}</span>
                                <span style="margin:0 5px;color:var(--text-dim)">→</span>
                                <span style="font-weight:600">{{ $d['to_shop'] }}</span>
                                <span style="margin-left:8px">
                                    @php $sc = $statusColors[$d['status']] ?? ['bg'=>'var(--surface2)','c'=>'var(--text-dim)'] @endphp
                                    <span class="tp-status" style="background:{{ $sc['bg'] }};color:{{ $sc['c'] }};font-size:10px">
                                        {{ ucwords(str_replace('_', ' ', $d['status'])) }}
                                    </span>
                                </span>
                            </div>
                            @if($d['discrepancy_notes'])
                                <div class="tp-disc-note">"{{ Str::limit($d['discrepancy_notes'], 90) }}"</div>
                            @endif
                            @if($d['received_at'])
                                <div style="font-size:11px;color:var(--text-dim);margin-top:4px">
                                    Received: {{ \Carbon\Carbon::parse($d['received_at'])->format('M j, Y H:i') }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="tp-empty">
                    <div class="tp-empty-title">No Discrepancies</div>
                    <div class="tp-empty-sub">All transfers in this period were clean</div>
                </div>
            @endif
        </div>

    </div>
@endif

{{-- ══ ApexCharts — reactive via @script ══ --}}
@script
<script>
let _tpTrend = null, _tpStatus = null, _tpComp = null;

function _tpInitTrend() {
    const el = document.getElementById('tp-trend-chart');
    if (!el) return;
    const ts     = JSON.parse(el.dataset.timestamps || '[]');
    const counts = JSON.parse(el.dataset.counts     || '[]');
    if (_tpTrend) { _tpTrend.destroy(); _tpTrend = null; }
    if (!ts.length) {
        el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:200px;color:var(--text-dim);font-size:13px">No transfer data for this period</div>';
        return;
    }
    const paired = ts.map((t, i) => ({ x: t, y: counts[i] }));
    _tpTrend = new ApexCharts(el, {
        chart: { type: 'area', height: 300, toolbar: { show: false }, animations: { enabled: false }, background: 'transparent' },
        series: [{ name: 'Transfers', data: paired }],
        xaxis: { type: 'datetime', labels: { style: { fontSize: '11px' }, datetimeFormatter: { day: 'MMM d' } } },
        yaxis: { labels: { formatter: v => Math.round(v), style: { fontSize: '11px' } }, min: 0 },
        colors: ['#7c3aed'],
        fill: { type: 'gradient', gradient: { opacityFrom: 0.28, opacityTo: 0.03 } },
        stroke: { curve: 'smooth', width: 2.5 },
        dataLabels: { enabled: false },
        tooltip: { x: { format: 'MMM d, yyyy' }, y: { formatter: v => v + ' transfer' + (v !== 1 ? 's' : '') } },
        grid: { borderColor: '#e2e6f3', strokeDashArray: 3 },
        markers: { size: 3, colors: ['#7c3aed'], strokeWidth: 0 },
    });
    _tpTrend.render();
}

function _tpInitStatus() {
    const el = document.getElementById('tp-status-chart');
    if (!el) return;
    const rawLabels = JSON.parse(el.dataset.labels || '[]');
    const counts    = JSON.parse(el.dataset.counts || '[]');
    if (_tpStatus) { _tpStatus.destroy(); _tpStatus = null; }
    if (!rawLabels.length) return;
    const labels = rawLabels.map(l => l.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
    _tpStatus = new ApexCharts(el, {
        chart: { type: 'donut', height: 260, animations: { enabled: false }, background: 'transparent' },
        series: counts,
        labels: labels,
        colors: ['#d97706', '#3b6fd4', '#7c3aed', '#0e9e86', '#0e9e86', '#e11d48', '#7a81a0'],
        dataLabels: { style: { fontSize: '11px' } },
        legend: { position: 'bottom', fontSize: '12px' },
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '11px', color: '#7a81a0', formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0) } } } } },
    });
    _tpStatus.render();
}

function _tpInitComp() {
    const el = document.getElementById('tp-comp-chart');
    if (!el) return;
    const buckets = JSON.parse(el.dataset.buckets || '[]');
    const counts  = JSON.parse(el.dataset.counts  || '[]');
    if (_tpComp) { _tpComp.destroy(); _tpComp = null; }
    if (!counts.some(v => v > 0)) return;
    _tpComp = new ApexCharts(el, {
        chart: { type: 'bar', height: 260, toolbar: { show: false }, animations: { enabled: false }, background: 'transparent' },
        series: [{ name: 'Transfers', data: counts }],
        xaxis: { categories: buckets, labels: { style: { fontSize: '11px' } } },
        yaxis: { labels: { style: { fontSize: '11px' } }, min: 0 },
        colors: ['#0e9e86', '#3b6fd4', '#d97706', '#e11d48'],
        plotOptions: { bar: { distributed: true, borderRadius: 5, columnWidth: '55%' } },
        dataLabels: { enabled: true, style: { fontSize: '11px' } },
        legend: { show: false },
        grid: { borderColor: '#e2e6f3', strokeDashArray: 3 },
        tooltip: { y: { formatter: v => v + ' transfer' + (v !== 1 ? 's' : '') } },
    });
    _tpComp.render();
}

function _tpInitAll() {
    _tpInitTrend();
    _tpInitStatus();
    _tpInitComp();
}

_tpInitAll();

Livewire.hook('commit', ({ succeed }) => {
    succeed(() => requestAnimationFrame(_tpInitAll));
});
</script>
@endscript

</div>
