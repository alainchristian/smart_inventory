<div>
<style>
/* ── Page ── */
.fo-wrap { font-family:var(--font); }

/* ── Header ── */
.fo-page-head {
    display:flex;align-items:flex-start;justify-content:space-between;
    gap:16px;flex-wrap:wrap;margin-bottom:20px;
}
.fo-page-title { font-size:22px;font-weight:800;color:var(--text);letter-spacing:-0.3px; }
.fo-page-sub   { font-size:13px;color:var(--text-dim);margin-top:2px; }

/* ── Filter bar ── */
.fo-filters {
    display:flex;flex-direction:column;gap:8px;margin-bottom:20px;
}
.fo-presets-row {
    display:flex;gap:6px;overflow-x:auto;-webkit-overflow-scrolling:touch;padding-bottom:2px;
}
.fo-presets-row::-webkit-scrollbar { display:none; }
.fo-controls-row {
    display:flex;gap:8px;align-items:center;flex-wrap:wrap;
}
.fo-preset-btn {
    padding:6px 12px;border-radius:8px;border:1px solid var(--border);
    background:var(--surface2);color:var(--text-dim);font-size:12px;
    font-weight:600;cursor:pointer;font-family:var(--font);transition:all 0.15s;
    white-space:nowrap;flex-shrink:0;
}
.fo-preset-btn.fo-active,
.fo-preset-btn:hover {
    background:var(--accent);color:white;border-color:var(--accent);
}
.fo-date-input, .fo-shop-select {
    padding:7px 10px;border-radius:8px;border:1.5px solid var(--border);
    background:var(--surface2);color:var(--text);font-size:13px;
    font-family:var(--font);cursor:pointer;
}
.fo-date-input:focus, .fo-shop-select:focus {
    outline:none;border-color:var(--accent);
}

/* ── KPI strip ── */
.fo-kpis {
    display:grid;grid-template-columns:repeat(6,1fr);
    gap:1px;background:var(--border);
    border-radius:16px;overflow:hidden;border:1px solid var(--border);
    margin-bottom:20px;
}
.fo-kpi {
    background:var(--surface2);padding:16px 14px;
}
.fo-kpi-label {
    font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:0.6px;color:var(--text-dim);margin-bottom:6px;
}
.fo-kpi-val {
    font-size:18px;font-weight:800;font-family:var(--mono);
    line-height:1;letter-spacing:-0.5px;
}
.fo-kpi-sub { font-size:10px;color:var(--text-dim);margin-top:4px; }

/* ── Shop ranking ── */
.fo-ranking { margin-bottom:20px; }
.fo-ranking-label {
    font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:0.6px;color:var(--text-dim);margin-bottom:10px;
}
.fo-rank-row {
    display:flex;align-items:center;gap:12px;padding:12px 16px;
    background:var(--surface2);border:1px solid var(--border);
    border-radius:12px;margin-bottom:8px;
}
.fo-rank-num {
    width:22px;height:22px;border-radius:50%;font-size:11px;font-weight:800;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
    background:var(--surface);color:var(--text-dim);border:1px solid var(--border);
}
.fo-rank-num.fo-rank-1 { background:var(--accent);color:white;border-color:var(--accent); }
.fo-rank-shop { font-size:13px;font-weight:600;color:var(--text);flex:1; }
.fo-rank-bar-wrap { flex:2;height:5px;background:var(--border);border-radius:3px;overflow:hidden; }
.fo-rank-bar { height:100%;background:var(--accent);border-radius:3px; }
.fo-rank-val { font-size:13px;font-weight:800;font-family:var(--mono);flex-shrink:0; }
.fo-rank-margin { font-size:10px;color:var(--text-dim);white-space:nowrap; }

/* ── Chart grid ── */
.fo-charts {
    display:grid;grid-template-columns:1.6fr 1fr;
    gap:16px;margin-bottom:20px;
}
.fo-chart-card {
    background:var(--surface2);border:1px solid var(--border);
    border-radius:14px;padding:18px 20px;
}
.fo-chart-title { font-size:13px;font-weight:700;color:var(--text);margin-bottom:4px; }
.fo-chart-sub   { font-size:11px;color:var(--text-dim);margin-bottom:14px; }
.fo-chart-legend {
    display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px;
}
.fo-legend-dot {
    width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:4px;flex-shrink:0;
}
.fo-legend-item { font-size:11px;color:var(--text-dim);display:flex;align-items:center; }

/* ── Sessions table ── */
.fo-table-wrap {
    border:1px solid var(--border);border-radius:14px;overflow:hidden;
}
.fo-table-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch; }
.fo-table {
    width:100%;border-collapse:collapse;min-width:820px;font-size:12px;
}
.fo-table thead tr {
    background:var(--surface);border-bottom:2px solid var(--border);
}
.fo-table thead th {
    padding:10px 14px;font-size:10px;font-weight:700;
    text-transform:uppercase;letter-spacing:0.5px;color:var(--text-dim);
    text-align:left;white-space:nowrap;
}
.fo-table thead th.fo-num { text-align:right; }
.fo-table tbody tr {
    border-bottom:1px solid var(--border);transition:background 0.1s;cursor:pointer;
}
.fo-table tbody tr:last-child { border-bottom:none; }
.fo-table tbody tr:hover { background:var(--surface); }
.fo-table td { padding:11px 14px;color:var(--text);vertical-align:middle; }
.fo-table td.fo-num {
    text-align:right;font-family:var(--mono);font-weight:600;white-space:nowrap;
}
.fo-table tfoot tr { background:var(--surface);border-top:2px solid var(--border); }
.fo-table tfoot td {
    padding:10px 14px;font-size:13px;font-weight:700;
    font-family:var(--mono);
}

/* Expanded detail — 3-column layout (Revenue | Expenses+Withdrawals | Cash Reconciliation) */
.fo-expanded-detail {
    display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;
}
.fo-exp-col {
    padding:20px 22px;
}
.fo-exp-col:not(:last-child) { border-right:1px solid var(--border); }
.fo-exp-col-title {
    font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:0.5px;color:var(--text-dim);margin-bottom:10px;
}
.fo-exp-line {
    display:flex;justify-content:space-between;align-items:baseline;
    font-size:12px;padding:5px 0;border-bottom:1px solid var(--border);gap:12px;
}
.fo-exp-line:last-child { border-bottom:none; }
.fo-exp-line-label { color:var(--text-dim);overflow:hidden;text-overflow:ellipsis; }
.fo-exp-line-val { font-family:var(--mono);font-weight:600;white-space:nowrap;flex-shrink:0; }

/* Status badges */
.fo-badge {
    padding:2px 8px;border-radius:5px;font-size:10px;font-weight:700;display:inline-block;
}
.fo-badge-open   { background:var(--green-dim);color:var(--green); }
.fo-badge-closed { background:var(--amber-dim);color:var(--amber); }
.fo-badge-locked { background:var(--surface);color:var(--text-dim);border:1px solid var(--border); }

/* Details button */
.fo-detail-btn {
    padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;
    border:1px solid var(--accent);color:var(--accent);background:var(--accent-dim);
    cursor:pointer;font-family:var(--font);white-space:nowrap;
}

/* ── Detail modal ── */
.fo-modal-wrap {
    position:fixed;inset:0;z-index:100;
    display:flex;align-items:center;justify-content:center;padding:20px;
    background:rgba(10,15,30,0.5);backdrop-filter:blur(3px);
}
.fo-modal {
    background:var(--surface);border-radius:16px;
    box-shadow:0 24px 80px rgba(0,0,0,0.25);
    width:100%;max-width:900px;max-height:88vh;
    display:flex;flex-direction:column;overflow:hidden;
}
.fo-modal-header {
    display:flex;align-items:center;justify-content:space-between;gap:12px;
    padding:14px 20px;border-bottom:1px solid var(--border);
    background:var(--surface);flex-shrink:0;
}
.fo-modal-body { overflow-y:auto;flex:1;overscroll-behavior:contain;background:var(--surface); }
.fo-modal-close {
    display:flex;align-items:center;justify-content:center;
    width:30px;height:30px;border-radius:8px;border:1px solid var(--border);
    background:var(--surface2);color:var(--text-dim);font-size:20px;
    cursor:pointer;font-family:var(--font);line-height:1;flex-shrink:0;transition:all 0.15s;
}
.fo-modal-close:hover { border-color:var(--red);color:var(--red); }

/* Empty shop row */
.fo-rank-row-empty { opacity:0.55; }

/* ── Modal verdict + formula strip ── */
.fo-verdict {
    display:flex;align-items:center;gap:10px;padding:10px 20px;
    font-size:12px;font-weight:600;border-bottom:1px solid var(--border);flex-shrink:0;
}
.fo-verdict-ok   { background:var(--green-dim);color:var(--green); }
.fo-verdict-err  { background:var(--red-dim);color:var(--red); }
.fo-verdict-warn { background:var(--amber-dim);color:var(--amber); }
.fo-verdict-seal { background:var(--surface2);color:var(--text-dim); }
.fo-verdict-live { background:var(--accent-dim);color:var(--accent); }
.fo-recon-strip {
    padding:10px 20px 12px;background:var(--surface2);
    border-bottom:1px solid var(--border);
    overflow-x:auto;-webkit-overflow-scrolling:touch;
}
.fo-recon-strip::-webkit-scrollbar { height:3px; }
.fo-recon-strip::-webkit-scrollbar-thumb { background:var(--border);border-radius:2px; }
.fo-recon-eq {
    display:flex;align-items:flex-end;gap:6px;min-width:max-content;
}
.fo-recon-item { display:flex;flex-direction:column;align-items:center;gap:2px; }
.fo-recon-label {
    font-size:9px;font-weight:700;text-transform:uppercase;
    letter-spacing:0.4px;color:var(--text-dim);font-family:var(--font);
}
.fo-recon-val { font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text); }
.fo-recon-op { font-size:14px;font-weight:600;color:var(--text-dim);padding-bottom:2px;flex-shrink:0; }
.fo-recon-eq-sign { font-size:16px;font-weight:700;padding-bottom:2px;flex-shrink:0; }
/* Variance highlight inside the 3-col grid */
.fo-variance-alert {
    display:flex;justify-content:space-between;align-items:baseline;
    padding:6px 10px;margin:3px -10px;border-radius:6px;font-size:12px;
}

/* ── Responsive ── */
@media(max-width:1100px) {
    .fo-kpis { grid-template-columns:repeat(3,1fr); }
}
@media(max-width:900px) {
    .fo-charts { grid-template-columns:1fr; }
    .fo-kpis   { grid-template-columns:repeat(3,1fr); }
}
@media(max-width:640px) {
    .fo-kpis { grid-template-columns:repeat(2,1fr); }
    .fo-kpi  { padding:12px; }
    .fo-kpi-val { font-size:16px; }
    .fo-rank-bar-wrap { display:none; }
    /* Modal 3-col → single column stack on mobile */
    .fo-expanded-detail { grid-template-columns:1fr; }
    .fo-exp-col { border-right:none !important;padding:14px 16px; }
    .fo-exp-col:not(:last-child) { border-right:none;border-bottom:1px solid var(--border); }
    .fo-controls-row { flex-direction:column;align-items:stretch; }
    .fo-date-input, .fo-shop-select { width:100%;box-sizing:border-box;flex:none; }
    .fo-preset-btn { font-size:11px;padding:5px 9px; }
    .fo-page-title { font-size:18px; }
    .fo-modal-wrap { padding:0;align-items:flex-end; }
    .fo-modal { border-radius:16px 16px 0 0;max-height:90vh;max-width:100%; }
    .fo-modal-header { padding:12px 16px; }
}
</style>

<div class="fo-wrap">

{{-- ── Period sub-header ── --}}
<div class="fo-page-sub" style="margin-bottom:16px;">
    {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
    – {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
    @if($shopFilter !== 'all')
        · {{ $shops->firstWhere('id', $shopFilter)?->name ?? 'Shop' }}
    @endif
</div>

{{-- ── Filter bar ── --}}
<div class="fo-filters">
    {{-- Period presets — horizontally scrollable, no wrap --}}
    <div class="fo-presets-row">
        @foreach([
            'today'      => 'Today',
            'yesterday'  => 'Yesterday',
            'this_week'  => 'This Week',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'last_30'    => 'Last 30 Days',
        ] as $key => $label)
            <button class="fo-preset-btn {{ $preset === $key ? 'fo-active' : '' }}"
                    wire:click="setPreset('{{ $key }}')">
                {{ $label }}
            </button>
        @endforeach
    </div>
    {{-- Date range + shop — stacks vertically on mobile --}}
    <div class="fo-controls-row">
        <input type="date" wire:model.live="dateFrom" class="fo-date-input">
        <span style="font-size:12px;color:var(--text-dim);flex-shrink:0;">to</span>
        <input type="date" wire:model.live="dateTo" class="fo-date-input">
        <select wire:model.live="shopFilter" class="fo-shop-select">
            <option value="all">All Shops</option>
            @foreach ($shops as $shop)
                <option value="{{ $shop->id }}">{{ $shop->name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- ── Computation block ── --}}
@php
    $rows_col            = collect($rows);
    $totalOpeningBalance = $rows_col->sum('opening_balance');
    $totalRevenue        = $rows_col->sum('revenue');
    $totalRepayments     = $rows_col->sum('repayments');
    $totalRefunds        = $rows_col->sum('refunds');
    $totalExpenses       = $rows_col->sum('expenses');
    $totalWithdrawals    = $rows_col->sum('withdrawals');
    $totalBanked         = $rows_col->sum('cash_banked');
    $totalVariance       = $rows_col->sum('total_variance');
    $sessionCount        = $rows_col->sum('session_count');
    $closedCount         = $rows_col->sum('closed_count');
    $days                = $rows_col->pluck('session_date')->unique()->count() ?: 1;
    $avgDailyVariance    = $days > 0 ? round($totalVariance / $days) : 0;
    $totalCashIn         = $totalOpeningBalance + $totalRevenue + $totalRepayments;
    // Use service data for KPI cards (cached, consistent with report builder)
    $svcRevenue     = $netResult['revenue']        ?? $totalRevenue;
    $svcGross       = $netResult['gross_profit']   ?? 0;
    $svcExpenses    = $netResult['total_expenses'] ?? $totalExpenses;
    $svcNetResult   = $netResult['net_result']     ?? 0;
    $svcWithdrawals = $withdrawalSummary['total_withdrawals'] ?? $totalWithdrawals;
    $svcVariance    = $cashVariance['net_variance']   ?? $totalVariance;
    $svcShortage    = $cashVariance['total_shortage'] ?? 0;
    // Table totals (from raw query — per-row detail, session breakdown)
    $totalOpProfit  = $totalRevenue - $totalRefunds - $totalExpenses;
    $totalNetResult = $totalOpProfit - $totalWithdrawals;
    // Expense ratio KPI
    $expenseRatio = $totalRevenue > 0
        ? round(($totalExpenses / $totalRevenue) * 100, 1)
        : 0;
    $expenseRatioColor = $expenseRatio < 15 ? 'var(--green)'
        : ($expenseRatio < 30 ? 'var(--amber)' : 'var(--red)');
@endphp

{{-- ── KPI strip (6 cards) ── --}}
<div class="fo-kpis">
    @foreach([
        ['Revenue',          $totalRevenue,    'var(--accent)',  'Total sales · RWF'],
        ['Operating Profit', $totalOpProfit,   $totalOpProfit  >= 0 ? 'var(--green)' : 'var(--red)', 'Revenue − refunds − expenses · RWF'],
        ['Net Result',       $totalNetResult,  $totalNetResult >= 0 ? 'var(--green)' : 'var(--red)', 'After owner withdrawals · RWF'],
        ['Cash Banked',      $totalBanked,     'var(--accent)', 'Deposited to bank · RWF'],
        ['Withdrawals',      $totalWithdrawals,'var(--amber)',   'Owner drawings · RWF'],
        ['Variance',         $totalVariance,   $totalVariance < 0 ? 'var(--red)' : ($totalVariance > 0 ? 'var(--amber)' : 'var(--text-dim)'), 'Cash shortages / surpluses'],
    ] as [$kl, $kv, $kc, $ks])
    <div class="fo-kpi">
        <div class="fo-kpi-label">{{ $kl }}</div>
        <div class="fo-kpi-val" style="color:{{ $kc }};">{{ number_format($kv) }}</div>
        <div class="fo-kpi-sub">{{ $ks }}</div>
    </div>
    @endforeach
</div>

{{-- ── Cross-shop ranking (always shown — all shops) ── --}}
@php
    $shopRanked = $shops->map(function ($shop) use ($rows) {
        $shopRows = collect($rows)->where('shop_id', $shop->id);
        $rev = (int) $shopRows->sum('revenue');
        $ref = (int) $shopRows->sum('refunds');
        $exp = (int) $shopRows->sum('expenses');
        $wd  = (int) $shopRows->sum('withdrawals');
        $op  = $rev - $ref - $exp;
        return [
            'name'     => $shop->name,
            'shop_id'  => $shop->id,
            'revenue'  => $rev,
            'op'       => $op,
            'net'      => $op - $wd,
            'margin'   => $rev > 0 ? round(($op / $rev) * 100, 1) : 0,
            'has_data' => $rev > 0 || $exp > 0,
        ];
    })->sortByDesc('op')->values();
    $maxRev = max($shopRanked->max('revenue'), 1);
@endphp
<div class="fo-ranking">
    <div class="fo-ranking-label">Shop Performance</div>
    @foreach($shopRanked as $i => $sh)
    <div class="fo-rank-row {{ !$sh['has_data'] ? 'fo-rank-row-empty' : '' }}">
        <div class="fo-rank-num {{ $i === 0 && $sh['has_data'] ? 'fo-rank-1' : '' }}">{{ $i + 1 }}</div>
        <div class="fo-rank-shop">{{ $sh['name'] }}</div>
        <div class="fo-rank-bar-wrap">
            <div class="fo-rank-bar" style="width:{{ $sh['has_data'] ? round(($sh['revenue'] / $maxRev) * 100) : 0 }}%;"></div>
        </div>
        @if($sh['has_data'])
            <div class="fo-rank-val" style="color:{{ $sh['op'] >= 0 ? 'var(--accent)' : 'var(--red)' }};">
                {{ number_format($sh['op']) }}
            </div>
            <div class="fo-rank-margin">{{ $sh['margin'] }}% margin</div>
        @else
            <div class="fo-rank-val" style="color:var(--text-dim);">—</div>
            <div class="fo-rank-margin" style="color:var(--text-dim);">No sessions</div>
        @endif
    </div>
    @endforeach
</div>

{{-- ── Dual Chart Row ── --}}
@if(!empty($chartData['labels']))
<div class="fo-charts">
    {{-- Line trend chart --}}
    <div class="fo-chart-card">
        <div class="fo-chart-title">Revenue & Expense Trend</div>
        <div class="fo-chart-sub">Daily movement across all shops · RWF</div>
        <div class="fo-chart-legend">
            <span class="fo-legend-item">
                <span class="fo-legend-dot" style="background:var(--accent);"></span>Revenue
            </span>
            <span class="fo-legend-item">
                <span class="fo-legend-dot" style="background:var(--accent);border:1px dashed var(--accent);"></span>Repayments
            </span>
            <span class="fo-legend-item">
                <span class="fo-legend-dot" style="background:var(--red);"></span>Expenses
            </span>
            <span class="fo-legend-item">
                <span class="fo-legend-dot" style="background:var(--green);"></span>Net
            </span>
        </div>
        <div style="position:relative;height:200px;">
            <canvas id="finance-trend-chart"
                    data-chart='@json($chartData)'></canvas>
        </div>
    </div>

    {{-- Grouped bar chart --}}
    <div class="fo-chart-card">
        <div class="fo-chart-title">Revenue vs Expenses</div>
        <div class="fo-chart-sub">Grouped by period · RWF</div>
        <div class="fo-chart-legend">
            <span class="fo-legend-item">
                <span class="fo-legend-dot" style="background:var(--accent);"></span>Revenue
            </span>
            <span class="fo-legend-item">
                <span class="fo-legend-dot" style="background:var(--red-dim);border:1px solid var(--red);"></span>Expenses
            </span>
        </div>
        <div style="position:relative;height:200px;">
            <canvas id="finance-bar-chart"
                    data-chart='@json($chartData)'></canvas>
        </div>
    </div>
</div>

@script
<script>
(function() {
    let trendChart = null;
    let barChart   = null;

    function getChartData() {
        const el = document.getElementById('finance-trend-chart');
        if (!el) return null;
        try { return JSON.parse(el.getAttribute('data-chart') || 'null'); }
        catch (e) { return null; }
    }

    function drawCharts() {
        const trendCanvas = document.getElementById('finance-trend-chart');
        const barCanvas   = document.getElementById('finance-bar-chart');

        // Canvas gone (filter hid the chart block) — clean up instances
        if (!trendCanvas) { trendChart = null; barChart = null; return; }

        const data = getChartData();
        if (!data || !data.labels || !data.labels.length) return;

        const cs     = getComputedStyle(document.documentElement);
        const accent = cs.getPropertyValue('--accent').trim() || '#0f766e';
        const red    = cs.getPropertyValue('--red').trim()    || '#e11d48';
        const redDim = cs.getPropertyValue('--red-dim').trim()|| '#fee2e2';
        const green  = cs.getPropertyValue('--green').trim()  || '#10b981';
        const grid   = 'rgba(0,0,0,0.04)';
        const txt    = 'var(--text-dim)';

        // Destroy stale instances whose canvas was replaced by Livewire's DOM morph
        if (trendChart && !trendChart.canvas.isConnected) { trendChart.destroy(); trendChart = null; }
        if (barChart   && !barChart.canvas.isConnected)   { barChart.destroy();   barChart   = null; }

        // ── Trend line chart ──────────────────────────────────────────
        if (trendChart) {
            trendChart.data.labels            = data.labels;
            trendChart.data.datasets[0].data  = data.revenue;
            trendChart.data.datasets[1].data  = data.repayments || [];
            trendChart.data.datasets[2].data  = data.expenses;
            trendChart.data.datasets[3].data  = data.net || [];
            trendChart.update('none');
        } else {
            trendChart = new Chart(trendCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label:'Revenue',    data: data.revenue,          borderColor: accent, backgroundColor:'rgba(15,118,110,0.05)', borderWidth:2.5, pointBackgroundColor:accent, pointRadius:3, tension:0.4, fill:true },
                        { label:'Repayments', data: data.repayments || [], borderColor: accent, borderWidth:2, pointBackgroundColor:accent, pointRadius:3, tension:0.4, borderDash:[6,3], fill:false },
                        { label:'Expenses',   data: data.expenses,         borderColor: red,    borderWidth:2, pointBackgroundColor:red,    pointRadius:3, tension:0.4, borderDash:[4,3], fill:false },
                        { label:'Net',        data: data.net || [],        borderColor: green,  borderWidth:2, pointBackgroundColor:green,  pointRadius:3, tension:0.4, borderDash:[2,3], fill:false },
                    ]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    plugins: {
                        legend: { display:false },
                        tooltip: { mode:'index', intersect:false, backgroundColor:'rgba(15,23,42,0.9)', titleFont:{size:13}, bodyFont:{size:12}, padding:10, cornerRadius:8 }
                    },
                    scales: {
                        x: { grid:{display:false}, ticks:{color:txt, font:{size:11}} },
                        y: { grid:{color:grid}, border:{display:false}, ticks:{color:txt, font:{size:11}} }
                    }
                }
            });
        }

        // ── Bar chart ─────────────────────────────────────────────────
        if (!barCanvas) return;
        if (barChart) {
            barChart.data.labels           = data.labels;
            barChart.data.datasets[0].data = data.revenue;
            barChart.data.datasets[1].data = data.expenses;
            barChart.update('none');
        } else {
            barChart = new Chart(barCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label:'Revenue',  data:data.revenue,  backgroundColor:accent, borderRadius:4, categoryPercentage:0.75, barPercentage:0.6 },
                        { label:'Expenses', data:data.expenses, backgroundColor:redDim, borderColor:red, borderWidth:1, borderRadius:4, categoryPercentage:0.75, barPercentage:0.6 },
                    ]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    plugins: {
                        legend: { display:false },
                        tooltip: { backgroundColor:'rgba(15,23,42,0.9)', padding:10, cornerRadius:8, mode:'index' }
                    },
                    scales: {
                        x: { grid:{display:false}, border:{display:false}, ticks:{color:txt, font:{size:11}} },
                        y: { grid:{color:grid},    border:{display:false}, ticks:{color:txt, font:{size:11}} }
                    }
                }
            });
        }
    }

    drawCharts();
    Livewire.hook('commit', ({ succeed }) => { succeed(() => { setTimeout(drawCharts, 50); }); });
})();
</script>
@endscript
@endif

{{-- ── No data ── --}}
@if(empty($rows))
    <div class="fo-table-wrap" style="border:1px solid var(--border);border-radius:14px;">
        <div style="padding:48px;text-align:center;">
            <div style="font-size:32px;margin-bottom:12px;">📊</div>
            <div style="font-size:14px;font-weight:600;color:var(--text);">No data for this period</div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:4px;">
                Try a different date range or shop filter.
            </div>
        </div>
    </div>
@else

{{-- ── Sessions table ── --}}
<div class="fo-table-wrap">
    <div class="fo-table-scroll">
        <table class="fo-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Shop</th>
                    <th class="fo-num">Revenue</th>
                    <th class="fo-num" style="color:var(--amber);">Refunds</th>
                    <th class="fo-num" style="color:var(--red);">Expenses</th>
                    <th class="fo-num">Withdrawals</th>
                    <th class="fo-num">Banked</th>
                    <th class="fo-num" style="color:var(--accent);">Op. Profit</th>
                    <th class="fo-num" style="color:var(--green);">Net Result</th>
                    <th class="fo-num">Variance</th>
                    <th style="text-align:center;">Sessions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                @php
                    $rowKey     = $row['session_date'] . ':' . $row['shop_id'];
                    $isExpanded = $expandedKey === $rowKey;
                    $rv           = (int)($row['total_variance'] ?? 0);
                    $rowOpProfit  = (int)$row['revenue'] - (int)$row['refunds'] - (int)$row['expenses'];
                    $rowNetResult = $rowOpProfit - (int)$row['withdrawals'];
                @endphp
                <tr wire:click="toggleRow('{{ $row['session_date'] }}', {{ $row['shop_id'] }})">
                    <td style="white-space:nowrap;">
                        <div style="font-size:12px;font-weight:600;color:var(--text);">{{ \Carbon\Carbon::parse($row['session_date'])->format('d M Y') }}</div>
                        <div style="font-size:11px;color:var(--text-dim);">{{ \Carbon\Carbon::parse($row['session_date'])->format('D') }}</div>
                    </td>
                    <td style="font-size:12px;font-weight:500;">{{ $row['shop_name'] }}</td>
                    <td class="fo-num" style="color:var(--accent);">{{ number_format($row['revenue']) }}</td>
                    <td class="fo-num" style="color:{{ $row['refunds'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }};">{{ number_format($row['refunds']) }}</td>
                    <td class="fo-num" style="color:var(--red);">{{ number_format($row['expenses']) }}</td>
                    <td class="fo-num" style="color:var(--accent);">{{ number_format($row['withdrawals']) }}</td>
                    <td class="fo-num" style="color:var(--accent);">{{ number_format($row['cash_banked']) }}</td>
                    <td class="fo-num" style="color:{{ $rowOpProfit >= 0 ? 'var(--accent)' : 'var(--red)' }};">{{ number_format($rowOpProfit) }}</td>
                    <td class="fo-num" style="color:{{ $rowNetResult >= 0 ? 'var(--green)' : 'var(--red)' }};">{{ number_format($rowNetResult) }}</td>
                    <td class="fo-num" style="color:{{ $rv < 0 ? 'var(--red)' : ($rv > 0 ? 'var(--amber)' : 'var(--text-dim)') }};">
                        {{ $rv >= 0 ? '+' : '' }}{{ number_format($rv) }}
                    </td>
                    <td style="text-align:center;font-size:12px;">
                        <span style="color:{{ $row['closed_count'] >= $row['session_count'] ? 'var(--green)' : 'var(--amber)' }};">{{ $row['closed_count'] }}</span><span style="color:var(--text-dim);">/{{ $row['session_count'] }}</span>
                    </td>
                    <td style="text-align:right;">
                        <button class="fo-detail-btn"
                                wire:click.stop="toggleRow('{{ $row['session_date'] }}', {{ $row['shop_id'] }})">
                            {{ $isExpanded ? '↑ Hide' : '↓ Details' }}
                        </button>
                    </td>
                </tr>

                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="padding:10px 14px;font-size:12px;font-weight:600;color:var(--text-dim);">
                        Totals · {{ $days }} day{{ $days !== 1 ? 's' : '' }}
                    </td>
                    <td class="fo-num" style="color:var(--accent);">{{ number_format($totalRevenue) }}</td>
                    <td class="fo-num" style="color:var(--amber);">{{ number_format($rows_col->sum('refunds')) }}</td>
                    <td class="fo-num" style="color:var(--red);">{{ number_format($totalExpenses) }}</td>
                    <td class="fo-num" style="color:var(--amber);">{{ number_format($totalWithdrawals) }}</td>
                    <td class="fo-num" style="color:var(--accent);">{{ number_format($totalBanked) }}</td>
                    <td class="fo-num" style="color:{{ $totalOpProfit >= 0 ? 'var(--accent)' : 'var(--red)' }};">{{ number_format($totalOpProfit) }}</td>
                    <td class="fo-num" style="color:{{ $totalNetResult >= 0 ? 'var(--green)' : 'var(--red)' }};">{{ number_format($totalNetResult) }}</td>
                    <td class="fo-num">
                        <div style="color:{{ $totalVariance < 0 ? 'var(--red)' : ($totalVariance > 0 ? 'var(--amber)' : 'var(--text-dim)') }};">
                            {{ $totalVariance >= 0 ? '+' : '' }}{{ number_format($totalVariance) }}
                        </div>
                        <div style="font-size:10px;color:var(--text-dim);margin-top:2px;font-weight:400;">
                            avg/day {{ $avgDailyVariance >= 0 ? '+' : '' }}{{ number_format($avgDailyVariance) }}
                        </div>
                    </td>
                    <td style="text-align:center;font-size:12px;font-weight:600;color:var(--text-dim);">{{ $closedCount }}/{{ $sessionCount }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ── Session detail modal ── --}}
@if($expandedKey && $expandedSessions->isNotEmpty())
@php
    [$_mDate, $_mShopId] = explode(':', $expandedKey, 2);
    $_mRow      = collect($rows)->first(fn($r) => $r['session_date'] === $_mDate && (string)$r['shop_id'] === $_mShopId);
    $_mAnyOpen  = $expandedSessions->contains(fn($s) => $s->isOpen());
    $_mAllLocked= $expandedSessions->every(fn($s) => $s->isLocked());
    $_mTotalVar = (int) $expandedSessions->sum('cash_variance');
    $_mHasVar   = $expandedSessions->contains(fn($s) => !$s->isOpen() && ($s->cash_variance ?? 0) !== 0);
@endphp
<div class="fo-modal-wrap" wire:click="closeExpanded">
    <div class="fo-modal" wire:click.stop>

        {{-- ── Header ── --}}
        <div class="fo-modal-header">
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--text);letter-spacing:-0.2px;">
                    {{ $_mRow['shop_name'] ?? '' }}
                </div>
                <div style="font-size:12px;color:var(--text-dim);margin-top:4px;
                            display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span>{{ \Carbon\Carbon::parse($_mDate)->format('d M Y') }}</span>
                    <span>·</span>
                    <span>{{ $expandedSessions->count() }} session{{ $expandedSessions->count() !== 1 ? 's' : '' }}</span>
                </div>
            </div>
            <button class="fo-modal-close" wire:click="closeExpanded">×</button>
        </div>

        {{-- ── Aggregate verdict ── --}}
        @if($_mAnyOpen)
            <div class="fo-verdict fo-verdict-live">
                <svg style="width:13px;height:13px;flex-shrink:0;" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="6"/></svg>
                One or more sessions still open — figures are live and may change
            </div>
        @elseif($_mHasVar && $_mTotalVar < 0)
            <div class="fo-verdict fo-verdict-err">
                <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                Cash shortage of {{ number_format(abs($_mTotalVar)) }} RWF — counted less than expected
            </div>
        @elseif($_mHasVar && $_mTotalVar > 0)
            <div class="fo-verdict fo-verdict-warn">
                <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                Cash surplus of {{ number_format($_mTotalVar) }} RWF — counted more than expected
            </div>
        @elseif($_mAllLocked)
            <div class="fo-verdict fo-verdict-seal">
                🔒 All sessions sealed and balanced — records are immutable
            </div>
        @else
            <div class="fo-verdict fo-verdict-ok">
                <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Cash balanced — all money accounted for
            </div>
        @endif

        {{-- ── Modal body — one block per session ── --}}
        <div class="fo-modal-body">
            @foreach($expandedSessions as $sess)
            @php
                $sv       = $sess->cash_variance ?? 0;
                $sessOpen = $sess->isOpen();
            @endphp

            {{-- Session separator (multi-session days) --}}
            @if($expandedSessions->count() > 1)
            <div style="display:flex;align-items:center;gap:10px;
                        padding:9px 20px;font-size:10px;font-weight:700;
                        text-transform:uppercase;letter-spacing:0.5px;
                        color:var(--text-dim);background:var(--surface2);
                        border-bottom:1px solid var(--border);">
                <span>Session {{ $loop->iteration }} — opened {{ $sess->opened_at?->format('H:i') }}</span>
                <span class="fo-badge fo-badge-{{ $sess->status }}">{{ ucfirst($sess->status) }}</span>
                @if(!$sessOpen && $sv !== 0)
                    <span style="margin-left:auto;font-family:var(--mono);font-weight:700;
                                 color:{{ $sv < 0 ? 'var(--red)' : 'var(--amber)' }};">
                        {{ $sv > 0 ? '+' : '' }}{{ number_format($sv) }} RWF
                    </span>
                @elseif(!$sessOpen)
                    <span style="margin-left:auto;color:var(--green);font-weight:600;">✓ Balanced</span>
                @endif
            </div>
            @endif

            {{-- Cash drawer formula strip (closed & locked sessions only) --}}
            @if(!$sessOpen)
            @php
                $fCashSales = $sess->total_sales_cash      ?? 0;
                $fCashRep   = $sess->total_repayments_cash  ?? 0;
                $fCashRef   = $sess->total_refunds_cash     ?? 0;
                $fCashExp   = $sess->total_expenses_cash    ?? 0;
                $fCashWd    = $sess->total_withdrawals_cash ?? 0;
                $fCashDep   = $sess->cash_deposits          ?? 0;
                $fExpected  = $sess->expected_cash          ?? 0;
                $fCounted   = $sess->actual_cash_counted;
                $fOpening   = $sess->opening_balance        ?? 0;
            @endphp
            <div class="fo-recon-strip">
                <div style="font-size:9px;font-weight:700;text-transform:uppercase;
                            letter-spacing:0.5px;color:var(--text-dim);margin-bottom:8px;">
                    Cash drawer formula
                </div>
                <div class="fo-recon-eq">
                    <div class="fo-recon-item">
                        <span class="fo-recon-label">Opening</span>
                        <span class="fo-recon-val">{{ number_format($fOpening) }}</span>
                    </div>
                    @if($fCashSales > 0)
                        <span class="fo-recon-op" style="color:var(--accent);">+</span>
                        <div class="fo-recon-item">
                            <span class="fo-recon-label">Cash Sales</span>
                            <span class="fo-recon-val" style="color:var(--accent);">{{ number_format($fCashSales) }}</span>
                        </div>
                    @endif
                    @if($fCashRep > 0)
                        <span class="fo-recon-op" style="color:var(--accent);">+</span>
                        <div class="fo-recon-item">
                            <span class="fo-recon-label">Repayments</span>
                            <span class="fo-recon-val" style="color:var(--accent);">{{ number_format($fCashRep) }}</span>
                        </div>
                    @endif
                    @if($fCashRef > 0)
                        <span class="fo-recon-op" style="color:var(--amber);">−</span>
                        <div class="fo-recon-item">
                            <span class="fo-recon-label">Refunds</span>
                            <span class="fo-recon-val" style="color:var(--amber);">{{ number_format($fCashRef) }}</span>
                        </div>
                    @endif
                    @if($fCashExp > 0)
                        <span class="fo-recon-op" style="color:var(--red);">−</span>
                        <div class="fo-recon-item">
                            <span class="fo-recon-label">Expenses</span>
                            <span class="fo-recon-val" style="color:var(--red);">{{ number_format($fCashExp) }}</span>
                        </div>
                    @endif
                    @if($fCashWd > 0)
                        <span class="fo-recon-op" style="color:var(--amber);">−</span>
                        <div class="fo-recon-item">
                            <span class="fo-recon-label">Withdrawals</span>
                            <span class="fo-recon-val" style="color:var(--amber);">{{ number_format($fCashWd) }}</span>
                        </div>
                    @endif
                    @if($fCashDep > 0)
                        <span class="fo-recon-op" style="color:var(--accent);">−</span>
                        <div class="fo-recon-item">
                            <span class="fo-recon-label">Banked</span>
                            <span class="fo-recon-val" style="color:var(--accent);">{{ number_format($fCashDep) }}</span>
                        </div>
                    @endif
                    <span class="fo-recon-eq-sign" style="color:var(--text-dim);">=</span>
                    <div class="fo-recon-item">
                        <span class="fo-recon-label">Expected</span>
                        <span class="fo-recon-val" style="font-size:13px;">{{ number_format($fExpected) }}</span>
                    </div>
                    <span class="fo-recon-eq-sign"
                          style="color:{{ $sv === 0 ? 'var(--green)' : ($sv < 0 ? 'var(--red)' : 'var(--amber)') }};">
                        {{ $sv === 0 ? '=' : '≠' }}
                    </span>
                    <div class="fo-recon-item">
                        <span class="fo-recon-label">Counted</span>
                        <span class="fo-recon-val" style="font-size:13px;
                              color:{{ $sv === 0 ? 'var(--green)' : ($sv < 0 ? 'var(--red)' : 'var(--amber)') }};">
                            {{ $fCounted !== null ? number_format($fCounted) : '—' }}
                        </span>
                    </div>
                    @if($sv !== 0)
                        <span class="fo-recon-eq-sign" style="color:var(--text-dim);">·</span>
                        <div class="fo-recon-item">
                            <span class="fo-recon-label">{{ $sv < 0 ? 'Shortage' : 'Surplus' }}</span>
                            <span class="fo-recon-val" style="font-size:13px;font-weight:800;
                                  color:{{ $sv < 0 ? 'var(--red)' : 'var(--amber)' }};">
                                {{ ($sv > 0 ? '+' : '') . number_format($sv) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── 3-column detail grid ── --}}
            <div class="fo-expanded-detail"
                 style="{{ !$loop->last ? 'border-bottom:2px solid var(--border);' : '' }}">

                {{-- Revenue by channel --}}
                <div class="fo-exp-col">
                    <div class="fo-exp-col-title">Revenue by Channel</div>
                    @foreach([
                        ['Cash',          $sess->total_sales_cash          ?? 0],
                        ['Mobile Money',  $sess->total_sales_momo          ?? 0],
                        ['Card',          $sess->total_sales_card          ?? 0],
                        ['Credit',        $sess->total_sales_credit        ?? 0],
                        ['Bank Transfer', $sess->total_sales_bank_transfer ?? 0],
                    ] as [$ch, $chv])
                    @if($chv > 0)
                    <div class="fo-exp-line">
                        <span style="color:var(--text-dim);">{{ $ch }}</span>
                        <span style="font-weight:600;font-family:var(--mono);color:var(--accent);">{{ number_format($chv) }}</span>
                    </div>
                    @endif
                    @endforeach
                    <div class="fo-exp-line" style="border-top:1.5px solid var(--border);margin-top:4px;">
                        <span style="font-weight:700;color:var(--text);">Total</span>
                        <span style="font-family:var(--mono);font-weight:700;color:var(--accent);">{{ number_format($sess->total_sales ?? 0) }}</span>
                    </div>
                </div>

                {{-- Expenses + Owner Withdrawals --}}
                <div class="fo-exp-col">
                    <div class="fo-exp-col-title">Expenses</div>
                    @forelse($sess->expenses->whereNull('deleted_at') as $exp)
                    <div class="fo-exp-line">
                        <span style="color:var(--text-dim);">
                            @if($exp->is_system_generated)
                                <span style="font-size:9px;padding:1px 4px;border-radius:3px;
                                             background:var(--amber-dim);color:var(--amber);margin-right:2px;">auto</span>
                            @endif
                            {{ $exp->category->name ?? '—' }}
                            @if($exp->description)
                                <span> — {{ Str::limit($exp->description, 18) }}</span>
                            @endif
                        </span>
                        <span style="font-weight:600;font-family:var(--mono);color:var(--red);">{{ number_format($exp->amount) }}</span>
                    </div>
                    @empty
                    <div style="font-size:11px;color:var(--text-dim);padding:4px 0;">None recorded</div>
                    @endforelse
                    @if($sess->expenses->whereNull('deleted_at')->count() > 0)
                    <div class="fo-exp-line" style="border-top:1.5px solid var(--border);margin-top:4px;">
                        <span style="font-weight:700;color:var(--text);">Total</span>
                        <span style="font-family:var(--mono);font-weight:700;color:var(--red);">{{ number_format($sess->total_expenses ?? 0) }}</span>
                    </div>
                    @endif

                    <div class="fo-exp-col-title" style="margin-top:18px;">Owner Withdrawals</div>
                    @forelse($sess->ownerWithdrawals->whereNull('deleted_at') as $wd)
                    <div class="fo-exp-line">
                        <span style="color:var(--text-dim);">
                            {{ Str::limit($wd->reason ?? '—', 22) }}
                            <span style="font-size:10px;"> ({{ ucfirst($wd->isCash() ? 'Cash' : 'MoMo') }})</span>
                        </span>
                        <span style="font-weight:600;font-family:var(--mono);color:var(--amber);">{{ number_format($wd->amount) }}</span>
                    </div>
                    @empty
                    <div style="font-size:11px;color:var(--text-dim);padding:4px 0;">None recorded</div>
                    @endforelse
                    @if($sess->ownerWithdrawals->whereNull('deleted_at')->count() > 0)
                    <div class="fo-exp-line" style="border-top:1.5px solid var(--border);margin-top:4px;">
                        <span style="font-weight:700;color:var(--text);">Total</span>
                        <span style="font-family:var(--mono);font-weight:700;color:var(--amber);">{{ number_format($sess->total_withdrawals ?? 0) }}</span>
                    </div>
                    @endif
                </div>

                {{-- Cash Reconciliation --}}
                <div class="fo-exp-col">
                    <div class="fo-exp-col-title">Cash Reconciliation</div>
                    @foreach([
                        ['Opening',  $sess->opening_balance     ?? null, 'var(--text-dim)', false],
                        ['Expected', $sess->expected_cash       ?? null, 'var(--text)',     false],
                        ['Counted',  $sess->actual_cash_counted ?? null, 'var(--text)',     false],
                        ['Variance', $sv, $sv < 0 ? 'var(--red)' : ($sv > 0 ? 'var(--amber)' : 'var(--text-dim)'), $sv !== 0],
                        ['Banked',   $sess->total_bank_deposits ?? 0,   'var(--accent)',   false],
                        ['Retained', $sess->cash_retained       ?? 0,   'var(--text)',     false],
                    ] as [$rl, $rlv, $rlc, $isAlert])
                        @if($isAlert)
                            <div class="fo-variance-alert"
                                 style="background:{{ $sv < 0 ? 'var(--red-dim)' : 'var(--amber-dim)' }};">
                                <span style="font-weight:700;color:{{ $rlc }};">{{ $rl }}</span>
                                <span style="font-weight:700;font-family:var(--mono);font-size:13px;color:{{ $rlc }};">
                                    {{ ($sv > 0 ? '+' : '') . number_format($sv) }}
                                    <span style="font-size:10px;font-weight:500;"> RWF</span>
                                </span>
                            </div>
                        @else
                            <div class="fo-exp-line">
                                <span style="color:var(--text-dim);">{{ $rl }}</span>
                                <span style="font-weight:600;font-family:var(--mono);color:{{ $rlc }};">
                                    {{ $rlv !== null ? number_format($rlv) : '—' }}
                                    @if($rlv !== null)<span style="font-size:10px;font-weight:400;color:var(--text-dim);"> RWF</span>@endif
                                </span>
                            </div>
                        @endif
                    @endforeach

                    @if(isset($sess->bankDeposits) && $sess->bankDeposits->isNotEmpty())
                    <div class="fo-exp-col-title" style="margin-top:14px;">Bank Deposits</div>
                    @foreach($sess->bankDeposits as $dep)
                    <div class="fo-exp-line">
                        <span style="color:var(--text-dim);">{{ $dep->deposited_at?->format('H:i') ?? '—' }}</span>
                        <span style="font-weight:600;font-family:var(--mono);color:var(--accent);">
                            {{ number_format($dep->amount) }}
                            <span style="font-size:10px;font-weight:400;color:var(--text-dim);"> RWF</span>
                        </span>
                    </div>
                    @endforeach
                    @endif

                    @if($sess->isLocked())
                    <div style="margin-top:12px;padding:8px 10px;border-radius:7px;
                                background:var(--surface2);border:1px solid var(--border);">
                        <div style="font-size:9px;font-weight:700;text-transform:uppercase;
                                    letter-spacing:0.4px;color:var(--text-dim);">Locked by</div>
                        <div style="font-size:12px;color:var(--text);margin-top:3px;">
                            {{ $sess->lockedBy->name ?? '—' }}
                            · {{ $sess->locked_at?->format('d M Y H:i') }}
                        </div>
                    </div>
                    @endif
                </div>

            </div>
            @endforeach
        </div>

    </div>
</div>
@endif

@endif {{-- end empty($rows) --}}

</div>
</div>
