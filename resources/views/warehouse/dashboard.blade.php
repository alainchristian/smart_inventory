{{-- Warehouse Dashboard --}}
<x-app-layout>

@push('styles')
<style>
/* ══ PERIOD BAR ═══════════════════════════════════════════════════════ */
.db-period-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
.db-period-pills { display:flex; align-items:center; background:var(--surface); border:1px solid var(--border); border-radius:8px; padding:3px; gap:2px; overflow-x:auto; max-width:100%; scrollbar-width:none; }
.db-period-pills::-webkit-scrollbar { display:none; }
.db-period-pill { font-size:13px; font-weight:500; color:var(--text-sub,var(--text-dim)); padding:6px 14px; border-radius:6px; border:none; background:transparent; cursor:pointer; transition:all .15s; white-space:nowrap; line-height:1.4; flex-shrink:0; }
.db-period-pill:hover { color:var(--text); background:rgba(0,0,0,.04); }
.db-period-pill.active { background:#3b6bd4; color:#fff; font-weight:600; }
.db-period-custom { display:flex; align-items:center; gap:6px; font-size:13px; font-weight:500; color:var(--text-sub,var(--text-dim)); padding:7px 14px; border-radius:8px; border:1px solid var(--border); background:var(--surface); cursor:pointer; transition:all .15s; white-space:nowrap; line-height:1.4; flex-shrink:0; }
.db-period-custom.active { background:#3b6bd4; color:#fff; border-color:#3b6bd4; }
.db-custom-picker { display:flex; align-items:center; gap:8px; flex-wrap:wrap; padding:8px 12px; border-radius:10px; background:var(--surface); border:1px solid var(--border); width:100%; }
.db-date-input { font-size:12px; color:var(--text); padding:5px 8px; border:1px solid var(--border); border-radius:6px; background:var(--surface); outline:none; }
.db-date-input:focus { border-color:#3b6bd4; }
.db-period-label { margin-left:auto; display:flex; align-items:center; gap:6px; font-size:12px; color:var(--text-dim); white-space:nowrap; }
.db-sync-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.db-sync-dot.green { background:#10b981; }
.db-sync-dot.amber { background:#f59e0b; }
.db-icon-btn { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text-dim); cursor:pointer; transition:all .15s; text-decoration:none; flex-shrink:0; }
.db-icon-btn:hover { color:var(--text); border-color:#3b6bd4; }

/* ══ KPI CARDS (shared with shop dashboard) ══════════════════════════ */
.db-kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
@media(max-width:900px){ .db-kpi-row{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:520px){ .db-kpi-row{ grid-template-columns:1fr; } }

.db-kpi {
    background:var(--surface); border:1px solid rgba(0,0,0,0.06);
    border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.02);
    display:flex; flex-direction:column; gap:20px;
}
.db-kpi--warn { border-color:var(--amber); }
.db-kpi-top  { display:flex; align-items:center; gap:14px; }
.db-kpi-circle {
    width:48px; height:48px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
}
.db-kpi-circle svg { width:22px; height:22px; }
.db-kpi-meta   { display:flex; flex-direction:column; gap:2px; }
.db-kpi-label  { font-size:12px; color:var(--text-sub,var(--text-dim)); font-weight:500; }
.db-kpi-value  { font-size:22px; font-weight:700; color:var(--text); line-height:1.2; }
.db-kpi-unit   { font-size:11px; font-weight:600; color:var(--text-dim); margin-left:4px; text-transform:uppercase; }
.db-kpi-bottom { display:flex; align-items:flex-end; justify-content:space-between; }
.db-kpi-stats  { display:flex; flex-direction:column; gap:4px; margin-bottom:2px; }
.db-change-text { font-size:13px; font-weight:600; display:flex; align-items:center; gap:4px; }
.db-change-text.up   { color:#10b981; }
.db-change-text.down { color:#ef4444; }
.db-change-text.warn { color:#f59e0b; }
.db-kpi-vs   { font-size:10px; color:var(--text-dim); }
.db-kpi-spark { flex-shrink:0; display:flex; align-items:flex-end; }
.db-kpi-spark canvas { width:90px !important; height:36px !important; display:block; }

@keyframes wdb-spark-pop {
    0%  { opacity:.2; transform:scaleY(.75); }
    100%{ opacity:1;  transform:scaleY(1);  }
}
.wdb-spark-refresh { animation:wdb-spark-pop .3s ease-out; transform-origin:bottom; }

/* ══ CARD SHELL (shared with shop dashboard) ══════════════════════════ */
.db-card { background:var(--surface); border:0.5px solid var(--border); border-radius:14px; padding:20px; }
.db-card-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
.db-card-title { font-size:14px; font-weight:600; color:var(--text); }
.db-view-all   { font-size:12px; color:var(--accent,#3b6bd4); text-decoration:none; font-weight:500; }
.db-view-all:hover { text-decoration:underline; }

.db-trend-legend { display:flex; align-items:center; gap:14px; font-size:11px; color:var(--text-sub,var(--text-dim)); }
.db-legend-dot-solid { display:inline-block; width:22px; height:3px; background:#3b6bd4; border-radius:2px; vertical-align:middle; }
.db-legend-dot-dash  { display:inline-block; width:22px; height:0; border-top:2px dashed #b4b2a9; vertical-align:middle; }

/* ══ CHARTS ROW ═══════════════════════════════════════════════════════ */
.db-row-60-40 { display:grid; grid-template-columns:1.5fr 1fr; gap:16px; }

.wdb-cat-layout { display:flex; align-items:center; gap:16px; }
.wdb-cat-row    { display:flex; align-items:center; gap:7px; }
.wdb-cat-dot    { display:inline-block; width:10px; height:10px; border-radius:50%; flex-shrink:0; }

/* ══ BOTTOM 3-COLUMN ROW ══════════════════════════════════════════════ */
.db-row-cf-side { display:grid; grid-template-columns:1.1fr 1fr 1fr; gap:16px; align-items:start; }
.db-row-cf-side > .db-card { height:420px; overflow:hidden; display:flex; flex-direction:column; }
.db-row-cf-side > .db-card > .db-card-head { flex-shrink:0; }
.db-scroll-body { flex:1; overflow-y:auto; min-height:0; }
.db-scroll-body::-webkit-scrollbar { width:4px; }
.db-scroll-body::-webkit-scrollbar-track { background:transparent; }
.db-scroll-body::-webkit-scrollbar-thumb { background:var(--border); border-radius:4px; }

/* ── Low Stock ── */
.db-stock-row { display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:0.5px solid var(--border); }
.db-stock-row:last-child { border-bottom:none; }
.db-stock-thumb { width:36px; height:36px; border-radius:8px; background:var(--surface2); flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.db-stock-thumb svg { width:18px; height:18px; color:var(--text-dim); }
.db-stock-name  { flex:1; font-size:13px; font-weight:500; color:var(--text); min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.db-stock-count { font-size:13px; font-weight:700; color:#e24b4a; white-space:nowrap; }
.db-stock-unit  { font-size:11px; font-weight:400; color:var(--text-dim); }

/* ── Recent Activities ── */
.db-txn-row { display:flex; align-items:center; gap:12px; padding:9px 0; border-bottom:0.5px solid var(--border); }
.db-txn-row:last-child { border-bottom:none; }
.db-txn-icon { width:34px; height:34px; border-radius:8px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.db-txn-icon svg { width:16px; height:16px; }
.db-txn-info  { flex:1; min-width:0; }
.db-txn-title { font-size:13px; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.db-txn-date  { font-size:11px; color:var(--text-dim); margin-top:1px; }

/* ══ INSIGHTS STRIP ═══════════════════════════════════════════════════ */
.db-insights-wrap  { display:flex; align-items:stretch; gap:16px; }
.db-insights-left  { flex:1; }
.db-insights-head  { display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
.db-insights-star  { width:32px; height:32px; border-radius:8px; background:rgba(83,74,183,.12); flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.db-insights-star svg { width:16px; height:16px; color:var(--accent,#534ab7); }
.db-insights-title { font-size:14px; font-weight:600; color:var(--text); }
.db-insight-line   { font-size:13px; color:var(--text-sub,var(--text-dim)); line-height:1.65; padding:4px 0; border-bottom:0.5px solid var(--border); }
.db-insight-line:last-child { border-bottom:none; }
.db-insights-right { width:160px; flex-shrink:0; display:flex; align-items:flex-end; justify-content:flex-end; }

/* ══ RESPONSIVE ═══════════════════════════════════════════════════════ */

/* 1200px — charts row stacks, bottom row still 3-col */
@media(max-width:1200px) {
    .db-row-60-40 { grid-template-columns:1fr; }
}

/* 1100px — bottom row collapses to 2-col; 3rd card spans full width */
@media(max-width:1100px) {
    .db-row-cf-side { grid-template-columns:1fr 1fr; }
    .db-row-cf-side > .db-card:nth-child(3) { grid-column:1 / -1; height:380px; }
}

/* 900px — KPIs 2-col, bottom row single column */
@media(max-width:900px) {
    .db-kpi-row     { grid-template-columns:repeat(2,1fr); }
    .db-row-cf-side { grid-template-columns:1fr; }
    .db-row-cf-side > .db-card:nth-child(3) { grid-column:auto; height:420px; }
    .wdb-cat-layout { flex-direction:column; align-items:flex-start; }
    .db-insights-right { display:none; }
}

/* 680px — period bar: pills scroll, label hidden, warehouse selector wraps */
@media(max-width:680px) {
    .db-period-bar  { gap:7px; }
    .db-period-pills { max-width:calc(100vw - 150px); }
    .db-period-label { display:none; }
    .db-period-custom span { display:none; }   /* hide "Custom Range" text, keep icon */
}

/* 520px — KPIs go single-column, cards height reduced */
@media(max-width:520px) {
    .db-kpi-row { grid-template-columns:1fr; }
    .db-row-cf-side > .db-card { height:360px; }
    .db-period-bar { flex-wrap:wrap; }
    .db-period-pills { max-width:100%; }
    .db-period-custom { padding:7px 10px; }
}

[x-cloak] { display:none !important; }
</style>
@endpush

@livewire('warehouse-manager.dashboard', ['warehouseId' => $warehouseId])

@push('scripts')
<script>
(function () {
    const CAT_COLORS = ['#3b6bd4','#10b981','#f59e0b','#8b5cf6','#f97316','#06b6d4','#ef4444','#84cc16'];

    let trendChart = null;
    let donutChart = null;
    let flowChart  = null;

    function getEl(id) { return document.getElementById(id); }

    /* ── Sparklines ───────────────────────────────────────────────── */
    function buildSpark(canvasId, data, color) {
        const el = getEl(canvasId);
        if (!el) return;

        el.classList.remove('wdb-spark-refresh');
        void el.offsetWidth;
        el.classList.add('wdb-spark-refresh');

        const existing = Chart.getChart(el);
        if (existing) {
            existing.data.labels = data.map((_,i) => i);
            existing.data.datasets[0].data = data;
            existing.update('none');
            return;
        }

        new Chart(el, {
            type: 'line',
            data: {
                labels: data.map((_,i) => i),
                datasets: [{
                    data,
                    borderColor: color,
                    borderWidth: 1.8,
                    pointRadius: 0,
                    tension: 0.4,
                    fill: false
                }]
            },
            options: {
                animation: false, responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display:false }, tooltip: { enabled:false } },
                scales: { x: { display:false }, y: { display:false } }
            }
        });
    }

    /* ── Trend chart ──────────────────────────────────────────────── */
    function buildTrend(labels, current, prev) {
        const el = getEl('wdbTrendChart');
        if (!el) return;

        if (trendChart) {
            trendChart.data.labels              = labels;
            trendChart.data.datasets[0].data    = current;
            trendChart.data.datasets[1].data    = prev;
            trendChart.update('none');
            trendChart.resize();
            return;
        }

        trendChart = new Chart(el, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'This Period',
                        data: current,
                        borderColor: '#3b6bd4',
                        backgroundColor: 'rgba(59,107,212,.06)',
                        borderWidth: 2.5,
                        pointRadius: 4, pointHoverRadius: 6,
                        pointBackgroundColor: '#3b6bd4',
                        tension: 0.4, fill: true
                    },
                    {
                        label: 'Previous Period',
                        data: prev,
                        borderColor: '#94a3b8',
                        backgroundColor: 'transparent',
                        borderWidth: 1.8,
                        borderDash: [5, 5],
                        pointRadius: 0, pointHoverRadius: 4,
                        tension: 0.4, fill: false
                    }
                ]
            },
            options: {
                animation: false, responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,.85)',
                        padding: 10, cornerRadius: 8,
                        titleFont: { size: 12 }, bodyFont: { size: 12 }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(148,163,184,.1)' },
                        ticks: { font: { size: 11 }, color: '#94a3b8' }
                    },
                    y: {
                        grid: { color: 'rgba(148,163,184,.1)' },
                        ticks: {
                            font: { size: 11 }, color: '#94a3b8',
                            callback: v => v >= 1000 ? (v/1000).toFixed(v % 1000 === 0 ? 0 : 1) + 'K' : v
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        /* Track container size changes and resize the chart to fit */
        const container = el.closest('.db-card') || el.parentElement;
        if (container && typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(() => { if (trendChart) trendChart.resize(); })
                .observe(container);
        }
    }

    /* ── Stock Breakdown donut (full / partial / damaged boxes) ─── */
    function buildFlowDonut(full, partial, damaged) {
        const el = getEl('wdbFlowDonut');
        if (!el) return;

        const total  = full + partial + damaged;
        const data   = total === 0 ? [1, 1, 1] : [full, partial, damaged];
        const colors = total === 0
            ? ['rgba(148,163,184,.2)','rgba(148,163,184,.2)','rgba(148,163,184,.2)']
            : ['#3b6bd4', '#f59e0b', '#ef4444'];

        if (flowChart) {
            flowChart.data.datasets[0].data            = data;
            flowChart.data.datasets[0].backgroundColor = colors;
            flowChart.update('none');
            return;
        }

        el.width  = 210;
        el.height = 210;

        flowChart = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: ['Full', 'Partial', 'Damaged'],
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverBorderWidth: 0
                }]
            },
            options: {
                animation: false,
                responsive: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,.85)',
                        callbacks: {
                            label: ctx => total === 0
                                ? ' No data'
                                : ' ' + ctx.label + ': ' + Number(ctx.raw).toLocaleString() + ' boxes'
                        }
                    }
                }
            }
        });
    }

    /* ── Category donut ───────────────────────────────────────────── */
    function buildDonut(labels, values) {
        const el = getEl('wdbCategoryDonut');
        if (!el) return;

        // Color the legend dots
        document.querySelectorAll('.wdb-cat-dot').forEach((dot, i) => {
            dot.style.background = CAT_COLORS[i % CAT_COLORS.length];
        });

        const colors = labels.map((_,i) => CAT_COLORS[i % CAT_COLORS.length]);

        if (donutChart) {
            donutChart.data.labels = labels;
            donutChart.data.datasets[0].data = values;
            donutChart.data.datasets[0].backgroundColor = colors;
            donutChart.update('none');
            return;
        }

        el.width  = 160;
        el.height = 160;

        donutChart = new Chart(el, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverBorderWidth: 0
                }]
            },
            options: {
                animation: false, responsive: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,.85)',
                        callbacks: { label: ctx => ' ' + ctx.label + ': ' + Number(ctx.raw).toLocaleString() + ' boxes' }
                    }
                }
            }
        });
    }

    /* ── Init ─────────────────────────────────────────────────────── */
    function initWarehouseDashboard() {
        const d = getEl('wdb-data');
        if (!d) return;

        const sparkIn   = JSON.parse(d.dataset.sparkInbound  || '[]');
        const sparkOut  = JSON.parse(d.dataset.sparkOutbound || '[]');
        const tLabels   = JSON.parse(d.dataset.trendLabels   || '[]');
        const tCurrent  = JSON.parse(d.dataset.trendCurrent  || '[]');
        const tPrev     = JSON.parse(d.dataset.trendPrev     || '[]');
        const catLabels = JSON.parse(d.dataset.catLabels     || '[]');
        const catValues = JSON.parse(d.dataset.catValues     || '[]');

        buildSpark('wdb-spark-0', sparkIn,  'rgb(59,107,212)');
        buildSpark('wdb-spark-1', sparkIn,  'rgb(16,185,129)');
        buildSpark('wdb-spark-2', sparkOut, 'rgb(249,115,22)');

        const fullBoxes    = parseInt(d.dataset.fullBoxes    || '0');
        const partialBoxes = parseInt(d.dataset.partialBoxes || '0');
        const damagedBoxes = parseInt(d.dataset.damagedBoxes || '0');

        buildTrend(tLabels, tCurrent, tPrev);
        buildFlowDonut(fullBoxes, partialBoxes, damagedBoxes);

        if (catLabels.length) buildDonut(catLabels, catValues);
    }

    function scheduleInit() {
        requestAnimationFrame(() => requestAnimationFrame(initWarehouseDashboard));
    }

    /* Resize trend chart whenever the window size changes */
    window.addEventListener('resize', () => { if (trendChart) trendChart.resize(); });

    document.addEventListener('livewire:initialized', scheduleInit);
    document.addEventListener('livewire:navigated',   scheduleInit);
    document.addEventListener('livewire:init', () => {
        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => { if (getEl('wdb-data')) scheduleInit(); });
        });
    });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleInit);
    } else {
        scheduleInit();
    }
})();
</script>
@endpush

</x-app-layout>
