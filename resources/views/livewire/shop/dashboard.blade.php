<div style="font-family:var(--font)">
<style>
/* ══════════════════════════════════════════════════════
   PAGE SHELL
══════════════════════════════════════════════════════ */

main { background-color: var(--surface) !important; }

.db-page {
    display:flex; flex-direction:column; gap:20px; padding-bottom:32px;
    margin-left:-12px; margin-right:-12px;
}
@media(min-width:1024px){ .db-page { margin-left:-20px; margin-right:-20px; } }
@media(min-width:1280px){ .db-page { margin-left:-28px; margin-right:-28px; } }

/* ══════════════════════════════════════════════════════
   KPI CARDS ROW
══════════════════════════════════════════════════════ */
.db-kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
@media(max-width:900px){ .db-kpi-row { grid-template-columns:repeat(2,1fr); } }
@media(max-width:520px){ .db-kpi-row { grid-template-columns:1fr; } }

.db-kpi {
    background:var(--surface); border:none; border-radius:var(--r);
    box-shadow:var(--shadow-card); padding:20px;
    display:flex; flex-direction:column; gap:14px;
    transition:box-shadow var(--tr);
}
.db-kpi:hover { box-shadow:var(--shadow-card-hover); }

.db-kpi-top    { display:flex; align-items:center; gap:14px; }
.db-kpi-circle {
    width:44px; height:44px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
}
.db-kpi-circle svg { width:20px; height:20px; }
.db-kpi-meta   { display:flex; flex-direction:column; gap:2px; }
.db-kpi-label  { font-size:11px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; color:var(--text-dim); }
.db-kpi-val    { font-size:24px; font-weight:800; font-family:var(--mono); color:var(--text); letter-spacing:-0.5px; line-height:1.1; }
.db-kpi-unit   { font-size:11px; font-weight:600; color:var(--text-dim); margin-left:4px; text-transform:uppercase; }
.db-kpi-bottom { display:flex; align-items:flex-end; justify-content:space-between; }
.db-kpi-stats  { display:flex; flex-direction:column; gap:4px; margin-bottom:2px; }
.db-change-text { font-size:13px; font-weight:600; display:flex; align-items:center; gap:4px; }
.db-change-text.up   { color:var(--green); }
.db-change-text.down { color:var(--red); }
.db-change-text.warn { color:var(--amber); }
.db-kpi-vs   { font-size:10px; color:var(--text-dim); }
.db-kpi-spark { flex-shrink:0; display:flex; align-items:flex-end; }
.db-kpi-spark canvas { width:90px !important; height:36px !important; display:block; }

/* ══════════════════════════════════════════════════════
   TWO-COLUMN ROW
══════════════════════════════════════════════════════ */
.db-row-60-40 { display:grid; grid-template-columns:1.5fr 1fr; gap:16px; }
@media(max-width:900px){ .db-row-60-40 { grid-template-columns:1fr; } }

/* ══════════════════════════════════════════════════════
   CARD SHELL
══════════════════════════════════════════════════════ */
.db-card {
    background:var(--surface); border:none; border-radius:var(--r);
    box-shadow:var(--shadow-card); padding:20px;
    transition:box-shadow var(--tr);
}
.db-card-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
.db-card-title { font-size:14px; font-weight:600; color:var(--text); }
.db-view-all   { font-size:12px; color:var(--accent); text-decoration:none; font-weight:500; }
.db-view-all:hover { text-decoration:underline; }

.db-trend-legend { display:flex; align-items:center; gap:14px; font-size:11px; color:var(--text-dim); }
.db-legend-dot-solid { display:inline-block; width:22px; height:3px; background:var(--accent); border-radius:2px; vertical-align:middle; }
.db-legend-dot-dash  { display:inline-block; width:22px; height:0; border-top:2px dashed var(--border-hi); vertical-align:middle; }

/* ── Top Products ── */
.db-prod-row { display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:0.5px solid var(--border); }
.db-prod-row:last-child { border-bottom:none; }
.db-prod-thumb { width:36px; height:36px; border-radius:8px; background:var(--accent-dim); flex-shrink:0; display:flex; align-items:center; justify-content:center; color:var(--accent); }
.db-prod-thumb svg { width:20px; height:20px; }
.db-prod-name { font-size:12px; font-weight:500; color:var(--text); width:110px; flex-shrink:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.db-prod-bar-wrap { flex:1; min-width:0; }
.db-prod-bar-bg   { height:7px; border-radius:10px; background:var(--surface2); overflow:hidden; }
.db-prod-bar-fill { height:100%; border-radius:10px; background:var(--accent); }
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
.db-ded--red  { color:var(--red); }
.db-ded--amber{ color:var(--amber); }

.db-inhand {
    display:flex; align-items:center; justify-content:space-between;
    margin-top:10px; padding:8px 12px; border-radius:9px;
    border:1px solid transparent;
}
.db-inhand--pos { background:var(--green-dim); border-color:var(--green); }
.db-inhand--neg { background:var(--red-dim);   border-color:var(--red);   }
.db-inhand-left { display:flex; align-items:center; gap:6px; }
.db-inhand--pos .db-inhand-left { color:var(--green); }
.db-inhand--neg .db-inhand-left { color:var(--red); }
.db-inhand-label  { font-size:11px; font-weight:600; }
.db-inhand-amount { font-size:14px; font-weight:800; font-family:var(--mono); }
.db-inhand--pos .db-inhand-amount { color:var(--green); }
.db-inhand--neg .db-inhand-amount { color:var(--red); }
.db-inhand-amount small { font-size:9px; font-weight:600; opacity:.7; }

@media(max-width:600px){
    .db-donut-body { flex-direction:column; align-items:center; }
    .db-donut-right { width:100%; }
}

/* ══════════════════════════════════════════════════════
   THREE-COLUMN ROW (CF + Low Stock + Transactions)
══════════════════════════════════════════════════════ */
.db-row-cf-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
.db-row-cf-side {
    display:grid;
    grid-template-columns:1.8fr 1fr 1fr;
    gap:16px;
    min-width:700px; /* horizontal scroll only kicks in below this */
    align-items:start;
}

/* ── Low Stock ── */
.db-stock-row { padding:8px 0; border-bottom:0.5px solid var(--border); }
.db-stock-row:last-child { border-bottom:none; }
.db-stock-info { display:flex; flex-direction:column; gap:5px; }
.db-stock-name-row { display:flex; align-items:center; justify-content:space-between; gap:8px; }
.db-stock-name  { font-size:12px; font-weight:500; color:var(--text); min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; flex:1; }
.db-stock-count { font-size:11px; font-weight:700; color:var(--red); white-space:nowrap; flex-shrink:0; }
.db-stock-unit  { font-size:10px; font-weight:400; color:var(--text-dim); }
.db-stock-bar-bg   { height:4px; border-radius:4px; background:var(--surface2); overflow:hidden; }
.db-stock-bar-fill { height:100%; border-radius:4px; transition:width .3s; }

/* ── Fixed equal-height columns: overflow:hidden hard-caps the card ── */
.db-row-cf-side > .db-card {
    height:380px; min-height:380px; max-height:380px;
    overflow:hidden;
    display:flex; flex-direction:column;
}
.db-card-head     { flex-shrink:0; }
.db-card-scroll-body { flex:1; overflow-y:auto; min-height:0; }
.db-inhand        { flex-shrink:0; }

/* ── Recent Transactions ── */
.db-txn-search {
    width:100%; padding:6px 10px; border:1px solid var(--border);
    border-radius:7px; font-size:12px; color:var(--text); background:var(--surface);
    margin-bottom:8px; outline:none; box-sizing:border-box;
}
.db-txn-search:focus { border-color:var(--accent); }
.db-txn-row { display:flex; align-items:center; gap:12px; height:54px; min-height:54px; max-height:54px; overflow:hidden; border-bottom:0.5px solid var(--border); }
.db-txn-row:last-child { border-bottom:none; }
.db-txn-icon { width:34px; height:34px; border-radius:8px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.db-txn-icon svg { width:16px; height:16px; }
.db-txn-icon.sale   { background:var(--accent-dim); color:var(--accent); }
.db-txn-icon.return { background:var(--red-dim);    color:var(--red);    }
.db-txn-info  { flex:1; min-width:0; overflow:hidden; }
.db-txn-title { font-size:13px; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.db-txn-date  { font-size:11px; color:var(--text-dim); margin-top:1px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.db-txn-products { color:var(--text-dim); font-style:italic; }
.db-txn-amount { font-size:13px; font-weight:700; font-family:var(--mono); white-space:nowrap; flex-shrink:0; }
.db-txn-amount.credit { color:var(--green); }
.db-txn-amount.debit  { color:var(--red); }

/* ══════════════════════════════════════════════════════
   BUSINESS INSIGHTS
══════════════════════════════════════════════════════ */
.db-insights-wrap  { display:flex; align-items:stretch; gap:16px; }
.db-insights-left  { flex:1; }
.db-insights-head  { display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
.db-insights-star  { width:32px; height:32px; border-radius:8px; background:var(--accent-dim); color:var(--accent); flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.db-insights-star svg { width:16px; height:16px; }
.db-insights-title { font-size:14px; font-weight:600; color:var(--text); }
.db-insight-line   { font-size:13px; color:var(--text-sub); line-height:1.65; padding:4px 0; border-bottom:0.5px solid var(--border); }
.db-insight-line:last-child { border-bottom:none; }
.db-insights-right { width:140px; flex-shrink:0; display:flex; align-items:flex-end; justify-content:flex-end; }
</style>

<div class="db-page" x-data="dbShopDashboard()" x-destroy="teardown()">

{{-- ════════════════════════════════════════════
     PERIOD FILTER BAR
════════════════════════════════════════════ --}}
<div class="db-period-bar">

    {{-- Row 1: preset pills --}}
    <div class="db-period-pills">
        @foreach([
            'today'      => 'Today',
            'yesterday'  => 'Yesterday',
            'week'       => 'This Week',
            'month'      => 'This Month',
            'last_month' => 'Last Month',
            'last_30'    => 'Last 30 Days',
        ] as $key => $label)
        <button wire:click="setPreset('{{ $key }}')"
                class="db-period-pill {{ $preset === $key ? 'active' : '' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Row 2: date inputs + sync dot --}}
    <div class="db-period-controls">
        <div class="db-period-ctrl-seg db-period-ctrl-grow">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--text-dim)"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
            <input type="date" wire:model="dateFrom" class="db-date-input">
            <span style="font-size:13px;color:var(--text-dim);flex-shrink:0;">→</span>
            <input type="date" wire:model="dateTo" class="db-date-input">
        </div>
        <div class="db-period-ctrl-seg">
            <span class="db-sync-dot {{ $lastSync->diffInMinutes(now()) < 5 ? 'green' : 'amber' }}"></span>
            <span style="font-size:12px;color:var(--text-dim);">Live</span>
        </div>
    </div>

</div>

{{-- Hidden chart data — updated by Livewire on each render --}}
<div id="db-chart-data" style="display:none"
     data-spark-sales='@json($sparklineSales)'
     data-spark-txns='@json($sparklineTxns)'
     data-spark-returns='@json($sparklineReturns)'
     data-trend-labels='@json($trendLabels)'
     data-trend-current='@json($trendCurrent)'
     data-trend-prev='@json($trendPrev)'
     data-top-products='@json($topProducts)'
     data-cf-cash="{{ $cfCash }}"
     data-cf-momo="{{ $cfMomo }}"
     data-cf-bank="{{ $allowBankTransfer ? $cfBank : -1 }}"
     data-cf-card="{{ $allowCard ? $cfCard : -1 }}"
     data-cf-credit="{{ $cfCredit }}"
></div>

{{-- ════════════════════════════════════════════
     KPI ROW
════════════════════════════════════════════ --}}
<div class="db-kpi-row">

    {{-- KPI 1: Total Sales --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:var(--accent-dim);color:var(--accent)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">Total Sales</span>
            </div>
        </div>
        <div class="db-kpi-val">{{ number_format($totalSales) }}<span class="db-kpi-unit">RWF</span></div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                <span class="db-change-text {{ $salesChange >= 0 ? 'up' : 'down' }}">
                    {{ $salesChange >= 0 ? '↑' : '↓' }} {{ number_format(abs($salesChange), 1) }}%
                </span>
                <span class="db-kpi-vs">vs previous period</span>
            </div>
            <div class="db-kpi-spark"><canvas id="sp-sales" width="90" height="36"></canvas></div>
        </div>
    </div>

    {{-- KPI 2: Transactions --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:var(--green-dim);color:var(--green)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">Transactions</span>
            </div>
        </div>
        <div class="db-kpi-val">{{ number_format($txnCount) }}</div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                <span class="db-change-text {{ $txnChange >= 0 ? 'up' : 'down' }}">
                    {{ $txnChange >= 0 ? '↑' : '↓' }} {{ number_format(abs($txnChange), 1) }}%
                </span>
                <span class="db-kpi-vs">vs previous period</span>
            </div>
            <div class="db-kpi-spark"><canvas id="sp-txns" width="90" height="36"></canvas></div>
        </div>
    </div>

    {{-- KPI 3: Returns --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:var(--amber-dim);color:var(--amber)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">Returns</span>
            </div>
        </div>
        <div class="db-kpi-val">{{ number_format($periodReturns) }}<span class="db-kpi-unit">RWF</span></div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                <span class="db-change-text {{ $returnsChange <= 0 ? 'up' : 'down' }}">
                    {{ $returnsChange <= 0 ? '↓' : '↑' }} {{ number_format(abs($returnsChange), 1) }}%
                </span>
                <span class="db-kpi-vs">vs previous period</span>
            </div>
            <div class="db-kpi-spark"><canvas id="sp-returns" width="90" height="36"></canvas></div>
        </div>
    </div>

    {{-- KPI 4: Stock --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:var(--violet-dim);color:var(--violet)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">In Stock</span>
            </div>
        </div>
        <div class="db-kpi-val">{{ number_format($stockBoxes) }}<span class="db-kpi-unit">{{ Str::plural('box', $stockBoxes) }}</span></div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                <span class="db-change-text" style="color:var(--text-dim);">
                    {{ number_format($stockItems) }} items remaining
                </span>
            </div>
            <div style="height:36px;display:flex;align-items:center;flex:1;margin-left:12px;">
                <div style="height:4px;width:100%;border-radius:2px;background:var(--surface2);overflow:hidden;">
                    <div style="height:100%;width:{{ min(100, ($stockBoxes > 0 ? 70 : 0)) }}%;background:var(--violet);border-radius:2px;"></div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /kpi-row --}}

{{-- ════════════════════════════════════════════
     SALES TREND + TOP PRODUCTS
════════════════════════════════════════════ --}}
<div class="db-row-60-40">

    <div class="db-card">
        <div class="db-card-head">
            <span class="db-card-title">Sales Trend</span>
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="db-trend-legend">
                    <span class="db-legend-dot-solid"></span> This Period
                    <span class="db-legend-dot-dash"></span> Previous Period
                </div>
            </div>
        </div>
        <div style="position:relative;height:220px;">
            <canvas id="salesTrendChart" wire:ignore></canvas>
        </div>
    </div>

    <div class="db-card">
        <div class="db-card-head">
            <span class="db-card-title">Top Products</span>
            <a href="{{ route('shop.inventory.stock') }}" class="db-view-all">View all</a>
        </div>
        @php $maxRev = $topProducts->max('revenue') ?: 1; @endphp
        @forelse($topProducts as $p)
        <div class="db-prod-row">
            <div class="db-prod-thumb">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 18c.5-3 2.5-5 5-6l3-1.5 2-1.5c1-.8 2-1 3-1 2 0 4 1.5 4.5 3.5L21 18H3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 18h18"/></svg>
            </div>
            <span class="db-prod-name" title="{{ $p->name }}">{{ $p->name }}</span>
            <div class="db-prod-bar-wrap">
                <div class="db-prod-bar-bg">
                    <div class="db-prod-bar-fill" style="width:{{ round(($p->revenue / $maxRev) * 100) }}%;"></div>
                </div>
            </div>
            <span class="db-prod-val">{{ number_format($p->revenue) }} RWF</span>
        </div>
        @empty
        <div style="text-align:center;padding:40px 0;color:var(--text-dim);font-size:13px;">No sales data for this period</div>
        @endforelse
    </div>

</div>

{{-- ════════════════════════════════════════════
     CASH FLOW + LOW STOCK + RECENT TRANSACTIONS
════════════════════════════════════════════ --}}
<div class="db-row-cf-wrap">
<div class="db-row-cf-side">

{{-- LEFT: Cash Flow Donut --}}
<div class="db-card">
    <div class="db-card-head">
        <span class="db-card-title">Cash Flow</span>
        <span style="font-size:11px;color:var(--text-dim);">{{ $periodLabel }}</span>
    </div>

    <div class="db-card-scroll-body">
    <div class="db-donut-body">
        {{-- Donut --}}
        <div class="db-donut-wrap">
            <canvas id="cfDonutChart" width="150" height="150" wire:ignore></canvas>
            <div class="db-donut-center">
                <div class="db-donut-lbl">INFLOW</div>
                <div class="db-donut-val">{{ number_format($cfTotal) }}</div>
                <div class="db-donut-unit">RWF</div>
            </div>
        </div>

        {{-- Right column: legend + deductions --}}
        <div class="db-donut-right">
            <div class="db-donut-legend">
                <div class="db-donut-leg-row">
                    <span class="db-donut-dot" style="background:#1d9e75;"></span>
                    <span class="db-donut-leg-name">Cash</span>
                    <span class="db-donut-leg-amt">{{ number_format($cfCash) }}</span>
                </div>
                <div class="db-donut-leg-row">
                    <span class="db-donut-dot" style="background:#3b6bd4;"></span>
                    <span class="db-donut-leg-name">MoMo</span>
                    <span class="db-donut-leg-amt">{{ number_format($cfMomo) }}</span>
                </div>
                @if($allowBankTransfer)
                <div class="db-donut-leg-row">
                    <span class="db-donut-dot" style="background:#8b5cf6;"></span>
                    <span class="db-donut-leg-name">Bank</span>
                    <span class="db-donut-leg-amt">{{ number_format($cfBank) }}</span>
                </div>
                @endif
                @if($allowCard)
                <div class="db-donut-leg-row">
                    <span class="db-donut-dot" style="background:#f59e0b;"></span>
                    <span class="db-donut-leg-name">Card</span>
                    <span class="db-donut-leg-amt">{{ number_format($cfCard) }}</span>
                </div>
                @endif
                @if($cfCredit > 0)
                <div class="db-donut-leg-row">
                    <span class="db-donut-dot" style="background:#f97316;"></span>
                    <span class="db-donut-leg-name">Credit</span>
                    <span class="db-donut-leg-amt">{{ number_format($cfCredit) }}</span>
                </div>
                @endif
            </div>

            <div class="db-deductions">
                <div class="db-ded-item">
                    <span class="db-ded-label">Refunds</span>
                    <span class="db-ded-val db-ded--red">−{{ number_format($cfReturns) }}</span>
                </div>
                <div class="db-ded-item">
                    <span class="db-ded-label">Withdrawals</span>
                    <span class="db-ded-val db-ded--red">−{{ number_format($cfWithdrawals) }}</span>
                </div>
                <div class="db-ded-item">
                    <span class="db-ded-label">Expenses</span>
                    <span class="db-ded-val db-ded--red">−{{ number_format($cfExpenses) }}</span>
                </div>
                <div class="db-ded-item">
                    <span class="db-ded-label">Credit</span>
                    <span class="db-ded-val db-ded--amber">{{ number_format($cfCredit) }}</span>
                </div>
            </div>
        </div>
    </div>
    </div>{{-- /db-card-scroll-body --}}

    {{-- Net in hand --}}
    <div class="db-inhand {{ $cfNet >= 0 ? 'db-inhand--pos' : 'db-inhand--neg' }}">
        <div class="db-inhand-left">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span class="db-inhand-label">Net In Hand</span>
        </div>
        <span class="db-inhand-amount">{{ number_format($cfNet) }} <small>RWF</small></span>
    </div>
</div>

{{-- Low Stock Alerts --}}
<div class="db-card">
    <div class="db-card-head">
        <span class="db-card-title">Low Stock Alerts</span>
        <a href="{{ route('shop.inventory.stock') }}" class="db-view-all">View all</a>
    </div>
    <div class="db-card-scroll-body">
    @forelse($lowStockProducts as $product)
    @php
        $pct = $shopLowStockThreshold > 0 ? min(100, round(($product->current_stock / $shopLowStockThreshold) * 100)) : 0;
        $barColor = $pct <= 25 ? 'var(--red)' : ($pct <= 50 ? 'var(--amber)' : 'var(--accent)');
    @endphp
    <div class="db-stock-row">
        <div class="db-stock-info">
            <div class="db-stock-name-row">
                <span class="db-stock-name" title="{{ $product->name }}">{{ $product->name }}</span>
                <span class="db-stock-count">{{ $product->current_stock }}<span class="db-stock-unit">/{{ $shopLowStockThreshold }} box{{ $shopLowStockThreshold != 1 ? 'es' : '' }}</span></span>
            </div>
            <div class="db-stock-bar-bg">
                <div class="db-stock-bar-fill" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
            </div>
        </div>
    </div>
    @empty
    <div style="padding:30px 0;text-align:center;color:var(--text-dim);font-size:13px;">All products well stocked</div>
    @endforelse
    </div>{{-- /db-card-scroll-body --}}
</div>

{{-- Recent Transactions --}}
@php
$txnList = collect();
foreach ($recentSales as $sale) {
    $productNames = $sale->items->pluck('product.name')->filter()->join(', ');
    $txnList->push([
        'type'     => 'sale',
        'title'    => 'Sale #'.$sale->sale_number,
        'date'     => $sale->sale_date,
        'amount'   => $sale->total,
        'credit'   => true,
        'products' => $productNames,
    ]);
}
foreach ($recentReturns as $ret) {
    $txnList->push([
        'type'     => 'return',
        'title'    => 'Return #'.($ret->return_number ?? $ret->id),
        'date'     => $ret->created_at,
        'amount'   => $ret->refund_amount,
        'credit'   => false,
        'products' => '',
    ]);
}
$txnList = $txnList->sortByDesc('date')->values();
@endphp

<div class="db-card" x-data="{ txnSearch: '' }">
    <div class="db-card-head">
        <span class="db-card-title">Recent Transactions</span>
        <a href="{{ route('shop.sales.index') }}" class="db-view-all">View all</a>
    </div>
    <input type="text" x-model="txnSearch" placeholder="Search sale # or product…" class="db-txn-search">
    <div class="db-card-scroll-body">
    @forelse($txnList as $txn)
    <div class="db-txn-row"
         x-show="txnSearch === '' ||
                 '{{ addslashes(strtolower($txn['title'])) }}'.includes(txnSearch.toLowerCase()) ||
                 '{{ addslashes(strtolower($txn['products'])) }}'.includes(txnSearch.toLowerCase())">
        <div class="db-txn-icon {{ $txn['type'] === 'sale' ? 'sale' : 'return' }}">
            @if($txn['type'] === 'sale')
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            @else
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            @endif
        </div>
        <div class="db-txn-info">
            <div class="db-txn-title">{{ $txn['title'] }}</div>
            <div class="db-txn-date">
                {{ \Carbon\Carbon::parse($txn['date'])->format('M j, Y g:i A') }}
                @if($txn['products'])
                <span class="db-txn-products">· {{ Str::limit($txn['products'], 40) }}</span>
                @endif
            </div>
        </div>
        <span class="db-txn-amount {{ $txn['credit'] ? 'credit' : 'debit' }}">
            {{ $txn['credit'] ? '+' : '-' }}{{ number_format($txn['amount']) }} RWF
        </span>
    </div>
    @empty
    <div style="padding:30px 0;text-align:center;color:var(--text-dim);font-size:13px;">No transactions in this period</div>
    @endforelse
    </div>{{-- /db-card-scroll-body --}}
</div>

</div>{{-- /row-cf-side --}}
</div>{{-- /row-cf-wrap --}}

{{-- ════════════════════════════════════════════
     BUSINESS INSIGHTS
════════════════════════════════════════════ --}}
<div class="db-card">
    <div class="db-insights-wrap">
        <div class="db-insights-left">
            <div class="db-insights-head">
                <div class="db-insights-star">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l1.5 4.5L2 10l4.5 1.5L8 17l2.5-4.5L15 14l-3-3.5L15 7l-4.5 1L8.5 3.5 6 7.5 5 3z"/></svg>
                </div>
                <span class="db-insights-title">Business Insights</span>
                <span style="font-size:11px;color:var(--text-dim);margin-left:6px;">{{ $periodLabel }}</span>
            </div>
            @php
                if ($salesChange > 0) {
                    $insightSales = 'Sales are up ' . number_format($salesChange, 1) . '% vs the previous period';
                    if ($topProducts->isNotEmpty()) {
                        $insightSales .= ', driven by ' . $topProducts->first()->name;
                        if ($topProducts->count() > 1) $insightSales .= ' and ' . $topProducts->skip(1)->first()->name;
                    }
                    $insightSales .= '.';
                } elseif ($salesChange < 0) {
                    $insightSales = 'Sales dropped ' . number_format(abs($salesChange), 1) . '% vs the previous period — consider promotions on slow-moving products.';
                } else {
                    $insightSales = 'Sales are tracking flat with the previous period.';
                }
            @endphp
            <div class="db-insight-line">{{ $insightSales }}</div>
            <div class="db-insight-line">
                @if($returnsChange <= 0)
                    Returns are stable or lower compared to the previous period.
                @else
                    Returns have risen {{ number_format($returnsChange, 1) }}% — review refund patterns for this period.
                @endif
            </div>
            <div class="db-insight-line">
                @if($cfCredit > 0)
                    {{ number_format($cfCredit) }} RWF in credit sales this period — follow up with customers on pending payments.
                @else
                    No credit sales this period. All transactions were settled immediately.
                @endif
            </div>
        </div>
        <div class="db-insights-right">
            <svg width="130" height="100" viewBox="0 0 130 100" fill="none" style="opacity:.85;">
                <rect x="5"  y="75" width="16" height="20" rx="3" fill="#5dcaa5"/>
                <rect x="26" y="60" width="16" height="35" rx="3" fill="#1d9e75"/>
                <rect x="47" y="42" width="16" height="53" rx="3" fill="#1d9e75"/>
                <rect x="68" y="25" width="16" height="70" rx="3" fill="#0f6e56"/>
                <rect x="89" y="10" width="16" height="85" rx="3" fill="#085041"/>
                <polyline points="13,72 34,56 55,38 76,21 97,7" stroke="#9fe1cb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                <circle cx="112" cy="30" r="14" stroke="#1d9e75" stroke-width="1.5" fill="none"/>
                <circle cx="112" cy="30" r="9"  stroke="#1d9e75" stroke-width="1.5" fill="none"/>
                <circle cx="112" cy="30" r="4"  fill="#1d9e75"/>
            </svg>
        </div>
    </div>
</div>

</div>{{-- /db-page --}}
</div>{{-- /font-family wrapper --}}

@script
<script>
Alpine.data('dbShopDashboard', () => ({

    init() {
        var self = this;

        // Watch for period filter changes (PHP increments chartVersion on every preset/date change)
        this.$wire.$watch('chartVersion', function() {
            self._scheduleRedraw();
        });

        // Safety net: draw after Alpine and Livewire have fully settled
        setTimeout(function() { self._scheduleRedraw(); }, 400);
    },

    teardown() {
        ['salesTrendChart', 'cfDonutChart'].forEach(function(id) {
            var canvas = document.getElementById(id);
            if (canvas) {
                var inst = Chart.getChart(canvas);
                if (inst) inst.destroy();
            }
        });
    },

    _scheduleRedraw() {
        var self = this;
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                self._draw();
            });
        });
    },

    _draw() {
        var el = document.getElementById('db-chart-data');
        if (!el) return;

        var sparkSales   = JSON.parse(el.dataset.sparkSales   || '[]');
        var sparkTxns    = JSON.parse(el.dataset.sparkTxns    || '[]');
        var sparkReturns = JSON.parse(el.dataset.sparkReturns || '[]');
        var trendLabels  = JSON.parse(el.dataset.trendLabels  || '[]');
        var trendCurrent = JSON.parse(el.dataset.trendCurrent || '[]');
        var trendPrev    = JSON.parse(el.dataset.trendPrev    || '[]');
        var cfCash   = parseFloat(el.dataset.cfCash   || '0');
        var cfMomo   = parseFloat(el.dataset.cfMomo   || '0');
        var cfBank   = parseFloat(el.dataset.cfBank   || '-1');
        var cfCard   = parseFloat(el.dataset.cfCard   || '-1');
        var cfCredit = parseFloat(el.dataset.cfCredit || '0');

        this._sparkline('sp-sales',   sparkSales,   '#3b82f6');
        this._sparkline('sp-txns',    sparkTxns,    '#10b981');
        this._sparkline('sp-returns', sparkReturns, '#f97316');
        this._trendChart(trendLabels, trendCurrent, trendPrev);
        this._cfDonut(cfCash, cfMomo, cfBank, cfCard, cfCredit);
    },

    _sparkline(id, data, color) {
        var canvas = document.getElementById(id);
        if (!canvas) return;
        var W = canvas.width  = canvas.offsetWidth  || 90;
        var H = canvas.height = canvas.offsetHeight || 36;
        var ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, W, H);
        if (!data || data.length < 2) return;
        var max = Math.max.apply(null, data.concat([1]));
        var min = Math.min.apply(null, data.concat([0]));
        var range = max - min || 1;
        var pts = data.map(function(v, i) {
            return {
                x: (i / (data.length - 1)) * W,
                y: H - 4 - ((v - min) / range) * (H - 8),
            };
        });
        var grad = ctx.createLinearGradient(0, 0, 0, H);
        grad.addColorStop(0, color + '44');
        grad.addColorStop(1, color + '00');
        ctx.beginPath();
        ctx.moveTo(pts[0].x, H);
        pts.forEach(function(p) { ctx.lineTo(p.x, p.y); });
        ctx.lineTo(pts[pts.length - 1].x, H);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();
        ctx.beginPath();
        pts.forEach(function(p, i) { i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y); });
        ctx.strokeStyle = color;
        ctx.lineWidth = 2;
        ctx.lineJoin = ctx.lineCap = 'round';
        ctx.stroke();
        canvas.classList.remove('db-spark-refresh');
        void canvas.offsetWidth;
        canvas.classList.add('db-spark-refresh');
    },

    _trendChart(labels, current, prev) {
        var el = document.getElementById('salesTrendChart');
        if (!el || typeof Chart === 'undefined') return;

        var hasPrev = prev && prev.length > 0;
        var yFmt = function(v) { return v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v; };
        var tooltip = {
            backgroundColor:'#fff', titleColor:'#3d3d3a', bodyColor:'#73726c',
            borderColor:'#d3d1c7', borderWidth:0.5, padding:10,
            callbacks: { label: function(c) { return ' ' + Number(c.raw).toLocaleString() + ' RWF'; } },
        };

        var existing = Chart.getChart(el);
        if (existing) {
            existing.data.labels             = labels;
            existing.data.datasets[0].data   = current;
            existing.data.datasets[1].data   = hasPrev ? prev : [];
            existing.data.datasets[1].hidden = !hasPrev;
            existing.update('none');
            return;
        }

        new Chart(el, {
            type: 'line',
            data: {
                labels: labels,
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
                plugins: { legend: { display: false }, tooltip: tooltip },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#888780' } },
                    y: { grid: { color: 'rgba(0,0,0,.05)' }, ticks: { font: { size: 11 }, color: '#888780', callback: yFmt } },
                },
            },
        });
    },

    _cfDonut(cash, momo, rawBank, rawCard, credit) {
        var el = document.getElementById('cfDonutChart');
        if (!el || typeof Chart === 'undefined') return;
        el.width  = 150;
        el.height = 150;

        var segments = [
            { label:'Cash',   value: cash,   color:'#1d9e75' },
            { label:'MoMo',   value: momo,   color:'#3b6bd4' },
        ];
        if (rawBank >= 0) segments.push({ label:'Bank',   value: rawBank,  color:'#8b5cf6' });
        if (rawCard >= 0) segments.push({ label:'Card',   value: rawCard,  color:'#f59e0b' });
        if (credit  >  0) segments.push({ label:'Credit', value: credit,   color:'#f97316' });

        var total  = segments.reduce(function(s, x) { return s + x.value; }, 0);
        var data   = total > 0 ? segments.map(function(x) { return x.value; }) : segments.map(function() { return 1; });
        var colors = total > 0 ? segments.map(function(x) { return x.color; }) : segments.map(function() { return '#e5e4e0'; });
        var labels = segments.map(function(x) { return x.label; });

        var existing = Chart.getChart(el);
        if (existing) {
            existing.data.labels                      = labels;
            existing.data.datasets[0].data            = data;
            existing.data.datasets[0].backgroundColor = colors;
            existing.update('none');
            return;
        }

        new Chart(el, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: data, backgroundColor: colors, borderColor:'#fff', borderWidth:3, hoverBorderWidth:3 }] },
            options: {
                animation: false, responsive: false, cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0,
                        backgroundColor:'#fff', titleColor:'#3d3d3a', bodyColor:'#73726c',
                        borderColor:'#d3d1c7', borderWidth:0.5, padding:10,
                        callbacks: { label: function(c) { return ' ' + Number(c.raw).toLocaleString() + ' RWF'; } },
                    },
                },
            },
        });
    },

}));
</script>
@endscript
