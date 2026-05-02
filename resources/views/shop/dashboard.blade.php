{{-- Shop Dashboard — Livewire-powered with dynamic period filter --}}
<x-app-layout>

@push('styles')
<style>
/* ══════════════════════════════════════════════════════
   PERIOD FILTER BAR
══════════════════════════════════════════════════════ */
.db-period-bar {
    display:flex; align-items:center; gap:10px; flex-wrap:wrap;
}
/* Grouped pill container — single rounded box */
.db-period-pills {
    display:flex; align-items:center;
    background:var(--surface); border:1px solid var(--border);
    border-radius:8px; padding:3px; gap:2px;
}
.db-period-pill {
    font-size:13px; font-weight:500; color:var(--text-sub);
    padding:6px 14px; border-radius:6px;
    border:none; background:transparent;
    cursor:pointer; transition:all .15s; white-space:nowrap;
    line-height:1.4;
}
.db-period-pill:hover { color:var(--text); background:rgba(0,0,0,.04); }
.db-period-pill.active { background:#3b6bd4; color:#fff; font-weight:600; }
/* Separate Custom Range button */
.db-period-custom {
    display:flex; align-items:center; gap:6px;
    font-size:13px; font-weight:500; color:var(--text-sub);
    padding:7px 14px; border-radius:8px;
    border:1px solid var(--border); background:var(--surface);
    cursor:pointer; transition:all .15s; white-space:nowrap;
    line-height:1.4;
}
.db-period-custom svg { flex-shrink:0; }
.db-period-custom:hover { color:var(--text); border-color:#3b6bd4; }
.db-period-custom.active { background:#3b6bd4; color:#fff; border-color:#3b6bd4; }
.db-custom-picker {
    display:flex; align-items:center; gap:8px; flex-wrap:wrap;
    padding:8px 12px; border-radius:10px;
    background:var(--surface); border:1px solid var(--border);
}
.db-date-input {
    font-size:12px; color:var(--text); padding:5px 8px;
    border:1px solid var(--border); border-radius:6px;
    background:var(--surface); outline:none;
}
.db-date-input:focus { border-color:#3b6bd4; }
.db-period-label {
    margin-left:auto; display:flex; align-items:center; gap:6px;
    font-size:12px; color:var(--text-dim); white-space:nowrap;
}
.db-period-label svg { flex-shrink:0; }
.db-sync-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.db-sync-dot.green { background:#10b981; }
.db-sync-dot.amber { background:#f59e0b; }

@keyframes db-spark-pop { 0%{opacity:.25;transform:scaleY(.85);} 100%{opacity:1;transform:scaleY(1);} }
.db-spark-refresh { animation: db-spark-pop .35s ease-out; transform-origin: bottom; }
@media(max-width:768px) {
    .db-period-bar { flex-wrap:nowrap; overflow-x:auto; padding-bottom:4px; gap:8px; -webkit-overflow-scrolling:touch; scrollbar-width:none; }
    .db-period-bar::-webkit-scrollbar { display:none; }
    .db-period-pills { flex-shrink:0; }
    .db-period-custom { flex-shrink:0; }
    .db-period-label { display:none; }
}
@media(max-width:480px) {
    .db-period-pill { font-size:12px; padding:5px 10px; }
    .db-period-custom { font-size:12px; padding:6px 10px; }
}

/* ══════════════════════════════════════════════════════
   PAGE SHELL
══════════════════════════════════════════════════════ */
.db-page { display:flex; flex-direction:column; gap:20px; padding-bottom:32px; }

/* ══════════════════════════════════════════════════════
   KPI CARDS ROW
══════════════════════════════════════════════════════ */
.db-kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
@media(max-width:900px){ .db-kpi-row{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:520px){ .db-kpi-row{ grid-template-columns:1fr; } }

.db-kpi {
    background:var(--surface); border:1px solid rgba(0,0,0,0.06);
    border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.02);
    display:flex; flex-direction:column; gap:20px;
}
.db-kpi-top  { display:flex; align-items:center; gap:14px; }
.db-kpi-circle {
    width:48px; height:48px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
}
.db-kpi-circle svg { width:22px; height:22px; }
.db-kpi-meta   { display:flex; flex-direction:column; gap:2px; }
.db-kpi-label  { font-size:12px; color:var(--text-sub); font-weight:500; }
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

/* ══════════════════════════════════════════════════════
   TWO-COLUMN ROW
══════════════════════════════════════════════════════ */
.db-row-60-40 { display:grid; grid-template-columns:1.5fr 1fr; gap:16px; }
@media(max-width:900px){ .db-row-60-40{ grid-template-columns:1fr; } }

/* ══════════════════════════════════════════════════════
   CARD SHELL
══════════════════════════════════════════════════════ */
.db-card { background:var(--surface); border:0.5px solid var(--border); border-radius:14px; padding:20px; }
.db-card-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
.db-card-title { font-size:14px; font-weight:600; color:var(--text); }
.db-view-all   { font-size:12px; color:var(--accent); text-decoration:none; font-weight:500; }
.db-view-all:hover { text-decoration:underline; }

.db-trend-legend { display:flex; align-items:center; gap:14px; font-size:11px; color:var(--text-sub); }
.db-legend-dot-solid { display:inline-block; width:22px; height:3px; background:#3b6bd4; border-radius:2px; vertical-align:middle; }
.db-legend-dot-dash  { display:inline-block; width:22px; height:0; border-top:2px dashed #b4b2a9; vertical-align:middle; }

/* ── Top Products ── */
.db-prod-row { display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:0.5px solid var(--border); }
.db-prod-row:last-child { border-bottom:none; }
.db-prod-thumb { width:36px; height:36px; border-radius:8px; background:var(--surface2); flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.db-prod-thumb svg { width:20px; height:20px; color:var(--text-dim); }
.db-prod-name { font-size:12px; font-weight:500; color:var(--text); width:110px; flex-shrink:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.db-prod-bar-wrap { flex:1; min-width:0; }
.db-prod-bar-bg   { height:7px; border-radius:10px; background:var(--surface2); overflow:hidden; }
.db-prod-bar-fill { height:100%; border-radius:10px; background:#3b6bd4; }
.db-prod-val { font-size:12px; font-weight:600; color:var(--text); font-family:var(--mono); white-space:nowrap; width:90px; text-align:right; flex-shrink:0; }

/* ══════════════════════════════════════════════════════
   CASH FLOW DONUT
══════════════════════════════════════════════════════ */
.db-donut-body {
    display:flex; align-items:stretch; gap:16px; padding:4px 0 0;
}
.db-donut-wrap {
    position:relative; flex-shrink:0; width:150px; height:150px;
}
.db-donut-wrap canvas { display:block; }
.db-donut-center {
    position:absolute; top:50%; left:50%;
    transform:translate(-50%,-50%);
    text-align:center; pointer-events:none; width:80px;
}
.db-donut-lbl  { font-size:8px; font-weight:700; letter-spacing:.08em; color:var(--text-dim); text-transform:uppercase; }
.db-donut-val  { font-size:17px; font-weight:800; color:var(--text); line-height:1.1; margin-top:1px; }
.db-donut-unit { font-size:8px; font-weight:600; color:var(--text-dim); text-transform:uppercase; }

/* Right column: legend + deductions stacked */
.db-donut-right {
    flex:1; min-width:0; display:flex; flex-direction:column; gap:0; justify-content:center;
}
.db-donut-legend { display:flex; flex-direction:column; gap:6px; }
.db-donut-leg-row { display:flex; align-items:center; gap:7px; }
.db-donut-dot  { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.db-donut-leg-name { font-size:11px; color:var(--text-dim); flex:1; }
.db-donut-leg-amt  { font-size:11px; font-weight:600; color:var(--text); font-family:var(--mono); white-space:nowrap; }

.db-deductions {
    display:grid; grid-template-columns:repeat(2,1fr);
    gap:5px 10px; border-top:1px solid var(--border);
    padding-top:8px; margin-top:8px;
}
.db-ded-item  { display:flex; flex-direction:column; gap:1px; }
.db-ded-label { font-size:9px; color:var(--text-dim); text-transform:uppercase; letter-spacing:.03em; }
.db-ded-val   { font-size:11px; font-weight:700; font-family:var(--mono); }
.db-ded--red  { color:#e24b4a; }
.db-ded--amber{ color:#ba7517; }

.db-inhand {
    display:flex; align-items:center; justify-content:space-between;
    margin-top:10px; padding:8px 12px; border-radius:9px;
    border:1px solid transparent;
}
.db-inhand--pos { background:rgba(29,158,117,.08); border-color:rgba(29,158,117,.25); }
.db-inhand--neg { background:rgba(226,75,74,.08);  border-color:rgba(226,75,74,.25);  }
.db-inhand-left { display:flex; align-items:center; gap:6px; }
.db-inhand--pos .db-inhand-left { color:#1d9e75; }
.db-inhand--neg .db-inhand-left { color:#e24b4a; }
.db-inhand-label  { font-size:11px; font-weight:600; }
.db-inhand-amount { font-size:14px; font-weight:800; font-family:var(--mono); }
.db-inhand--pos .db-inhand-amount { color:#1d9e75; }
.db-inhand--neg .db-inhand-amount { color:#e24b4a; }
.db-inhand-amount small { font-size:9px; font-weight:600; opacity:.7; }

@media(max-width:600px){
    .db-donut-body { flex-direction:column; align-items:center; }
    .db-donut-right { width:100%; }
}

/* ══════════════════════════════════════════════════════
   THREE-COLUMN ROW (CF + Low Stock + Transactions)
══════════════════════════════════════════════════════ */
.db-row-cf-side { display:grid; grid-template-columns:1.8fr 1fr 1fr; gap:16px; align-items:start; }
@media(max-width:1200px){ .db-row-cf-side{ grid-template-columns:1.35fr 1fr; } }
@media(max-width:900px){ .db-row-cf-side{ grid-template-columns:1fr; } }

/* ── Low Stock ── */
.db-stock-row { display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:0.5px solid var(--border); }
.db-stock-row:last-child { border-bottom:none; }
.db-stock-thumb { width:36px; height:36px; border-radius:8px; background:var(--surface2); flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.db-stock-thumb svg { width:18px; height:18px; color:var(--text-dim); }
.db-stock-name  { flex:1; font-size:13px; font-weight:500; color:var(--text); min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.db-stock-count { font-size:13px; font-weight:700; color:#e24b4a; white-space:nowrap; }
.db-stock-unit  { font-size:11px; font-weight:400; color:var(--text-dim); }

/* ── Recent Transactions ── */
.db-txn-row { display:flex; align-items:center; gap:12px; padding:9px 0; border-bottom:0.5px solid var(--border); }
.db-txn-row:last-child { border-bottom:none; }
.db-txn-icon { width:34px; height:34px; border-radius:8px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.db-txn-icon svg { width:16px; height:16px; }
.db-txn-info  { flex:1; min-width:0; }
.db-txn-title { font-size:13px; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.db-txn-date  { font-size:11px; color:var(--text-dim); margin-top:1px; }
.db-txn-amount { font-size:13px; font-weight:700; font-family:var(--mono); white-space:nowrap; }
.db-txn-amount.credit { color:#0f6e56; }
.db-txn-amount.debit  { color:#a32d2d; }

/* ══════════════════════════════════════════════════════
   BUSINESS INSIGHTS
══════════════════════════════════════════════════════ */
.db-insights-wrap  { display:flex; align-items:stretch; gap:16px; }
.db-insights-left  { flex:1; }
.db-insights-head  { display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
.db-insights-star  { width:32px; height:32px; border-radius:8px; background:rgba(83,74,183,.12); flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.db-insights-star svg { width:16px; height:16px; color:var(--accent); }
.db-insights-title { font-size:14px; font-weight:600; color:var(--text); }
.db-insight-line   { font-size:13px; color:var(--text-sub); line-height:1.65; padding:4px 0; border-bottom:0.5px solid var(--border); }
.db-insight-line:last-child { border-bottom:none; }
.db-insights-right { width:140px; flex-shrink:0; display:flex; align-items:flex-end; justify-content:flex-end; }
</style>
@endpush

<livewire:shop.dashboard :shopId="$shopId" />

@push('scripts')
<script>
(function () {
    // ─── Sparkline renderer ───────────────────────────────────────────────
    function drawSparkline(id, data, color) {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        const W = canvas.width  = canvas.offsetWidth  || 90;
        const H = canvas.height = canvas.offsetHeight || 36;
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, W, H);
        if (!data || data.length < 2) return;
        const max = Math.max(...data, 1);
        const min = Math.min(...data, 0);
        const range = max - min || 1;
        const pts = data.map((v, i) => ({
            x: (i / (data.length - 1)) * W,
            y: H - 4 - ((v - min) / range) * (H - 8),
        }));
        const grad = ctx.createLinearGradient(0, 0, 0, H);
        grad.addColorStop(0, color + '44');
        grad.addColorStop(1, color + '00');
        ctx.beginPath();
        ctx.moveTo(pts[0].x, H);
        pts.forEach(p => ctx.lineTo(p.x, p.y));
        ctx.lineTo(pts[pts.length - 1].x, H);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();
        ctx.beginPath();
        pts.forEach((p, i) => i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y));
        ctx.strokeStyle = color;
        ctx.lineWidth = 2;
        ctx.lineJoin = ctx.lineCap = 'round';
        ctx.stroke();

        // Brief pop-in to confirm the sparkline just refreshed with new data
        canvas.classList.remove('db-spark-refresh');
        void canvas.offsetWidth; // force reflow so animation restarts
        canvas.classList.add('db-spark-refresh');
    }

    // ─── Trend chart instance ─────────────────────────────────────────────
    let trendChart = null;

    function buildTrendChart(labels, current, prev) {
        const el = document.getElementById('salesTrendChart');
        if (!el || typeof Chart === 'undefined') return;

        const yFmt   = v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v;
        const tooltip = {
            backgroundColor:'#fff', titleColor:'#3d3d3a', bodyColor:'#73726c',
            borderColor:'#d3d1c7', borderWidth:0.5, padding:10,
            callbacks: { label: c => ' ' + Number(c.raw).toLocaleString() + ' RWF' },
        };
        const hasPrev = prev && prev.length > 0;

        // Silent in-place update — no re-animation on filter/period change
        if (trendChart) {
            trendChart.data.labels           = labels;
            trendChart.data.datasets[0].data = current;
            trendChart.data.datasets[1].data = hasPrev ? prev : [];
            trendChart.data.datasets[1].hidden = !hasPrev;
            trendChart.update('none');
            return;
        }

        // First render — always a line chart, animations disabled
        trendChart = new Chart(el, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'This Period',
                        data: current,
                        borderColor: '#3b6bd4',
                        backgroundColor: 'rgba(59,107,212,.08)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#3b6bd4',
                        tension: 0.3,
                        fill: true,
                    },
                    {
                        label: 'Previous Period',
                        data: hasPrev ? prev : [],
                        hidden: !hasPrev,
                        borderColor: '#b4b2a9',
                        borderDash: [5, 4],
                        borderWidth: 1.5,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: false,
                    },
                ],
            },
            options: {
                animation: false,
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#888780' } },
                    y: { grid: { color: 'rgba(0,0,0,.05)' }, ticks: { font: { size: 11 }, color: '#888780', callback: yFmt } },
                },
            },
        });
    }

    // ─── Cash Flow donut ─────────────────────────────────────────────────
    let cfDonut = null;

    function buildCfDonut(cash, momo, rawBank, rawCard) {
        const el = document.getElementById('cfDonutChart');
        if (!el || typeof Chart === 'undefined') return;
        el.width  = 150;
        el.height = 150;

        // -1 means the method is disabled — omit from chart
        const segments = [
            { label:'Cash',  value: cash,    color:'#1d9e75' },
            { label:'MoMo',  value: momo,    color:'#3b6bd4' },
            ...(rawBank >= 0 ? [{ label:'Bank', value: rawBank, color:'#8b5cf6' }] : []),
            ...(rawCard >= 0 ? [{ label:'Card', value: rawCard, color:'#f59e0b' }] : []),
        ];
        const total  = segments.reduce((s, x) => s + x.value, 0);
        const data   = total > 0 ? segments.map(x => x.value) : segments.map(() => 1);
        const colors = total > 0 ? segments.map(x => x.color) : segments.map(() => '#e5e4e0');
        const labels = segments.map(x => x.label);

        if (cfDonut) {
            cfDonut.data.labels                      = labels;
            cfDonut.data.datasets[0].data            = data;
            cfDonut.data.datasets[0].backgroundColor = colors;
            cfDonut.update('none');
            return;
        }
        cfDonut = new Chart(el, {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: colors, borderColor:'#fff', borderWidth:3, hoverBorderWidth:3 }] },
            options: {
                animation: false, responsive: false, cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0,
                        backgroundColor:'#fff', titleColor:'#3d3d3a', bodyColor:'#73726c',
                        borderColor:'#d3d1c7', borderWidth:0.5, padding:10,
                        callbacks: { label: c => ' ' + Number(c.raw).toLocaleString() + ' RWF' },
                    },
                },
            },
        });
    }

    // ─── Master init — reads data from the hidden div ─────────────────────
    function initDashboard() {
        const el = document.getElementById('db-chart-data');
        if (!el) return;

        const sparkSales   = JSON.parse(el.dataset.sparkSales   || '[]');
        const sparkTxns    = JSON.parse(el.dataset.sparkTxns    || '[]');
        const sparkReturns = JSON.parse(el.dataset.sparkReturns || '[]');
        const trendLabels  = JSON.parse(el.dataset.trendLabels  || '[]');
        const trendCurrent = JSON.parse(el.dataset.trendCurrent || '[]');
        const trendPrev    = JSON.parse(el.dataset.trendPrev    || '[]');
        const cfCash = parseFloat(el.dataset.cfCash || '0');
        const cfMomo = parseFloat(el.dataset.cfMomo || '0');
        const cfBank = parseFloat(el.dataset.cfBank || '0');
        const cfCard = parseFloat(el.dataset.cfCard || '-1');

        drawSparkline('sp-sales',   sparkSales,   '#3b82f6');
        drawSparkline('sp-txns',    sparkTxns,    '#10b981');
        drawSparkline('sp-returns', sparkReturns, '#f97316');
        buildTrendChart(trendLabels, trendCurrent, trendPrev);
        buildCfDonut(cfCash, cfMomo, cfBank, cfCard);
    }

    // Schedules initDashboard safely past Livewire's DOM morph cycle
    function scheduleInit() {
        requestAnimationFrame(() => requestAnimationFrame(initDashboard));
    }

    // Initial page load — fire after Livewire has hydrated the component
    document.addEventListener('livewire:initialized', scheduleInit);

    // SPA navigation (Livewire navigate) — re-init on every page arrival
    document.addEventListener('livewire:navigated', scheduleInit);

    // Filter changes — re-draw after every Livewire commit
    document.addEventListener('livewire:init', () => {
        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                if (document.getElementById('db-chart-data')) {
                    scheduleInit();
                }
            });
        });
    });

    // Fallback: plain DOMContentLoaded in case livewire:initialized already fired
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleInit);
    } else {
        scheduleInit();
    }

})();
</script>
@endpush

</x-app-layout>
