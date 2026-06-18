<div style="font-family:var(--font)" x-data="wdbDashboard()" x-destroy="teardown()">
<style>
/* ══ WAREHOUSE ICON BTN ══════════════════════════════════════════════ */
.db-icon-btn { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text-dim); cursor:pointer; transition:all .15s; text-decoration:none; flex-shrink:0; }
.db-icon-btn:hover { color:var(--text); border-color:var(--accent); }

/* ══ KPI CARDS ═══════════════════════════════════════════════════════ */
.db-kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
@media(max-width:900px){ .db-kpi-row{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:520px){ .db-kpi-row{ grid-template-columns:1fr; } }

.db-kpi {
    background:var(--surface); border:none;
    border-radius:var(--r); padding:20px; box-shadow:var(--shadow-card);
    display:flex; flex-direction:column; gap:20px;
}
.db-kpi--warn { box-shadow:var(--shadow-card), 0 0 0 1.5px var(--amber); }
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
.db-change-text.up   { color:var(--green); }
.db-change-text.down { color:var(--red); }
.db-change-text.warn { color:var(--amber); }
.db-kpi-vs   { font-size:10px; color:var(--text-dim); }
.db-kpi-spark { flex-shrink:0; display:flex; align-items:flex-end; }
.db-kpi-spark canvas { width:90px !important; height:36px !important; display:block; }

@keyframes wdb-spark-pop {
    0%  { opacity:.2; transform:scaleY(.75); }
    100%{ opacity:1;  transform:scaleY(1);  }
}
.wdb-spark-refresh { animation:wdb-spark-pop .3s ease-out; transform-origin:bottom; }

/* ══ CARD SHELL ══════════════════════════════════════════════════════ */
.db-card { background:var(--surface); border:none; border-radius:var(--r); padding:20px; box-shadow:var(--shadow-card); }
.db-card-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
.db-card-title { font-size:14px; font-weight:600; color:var(--text); }
.db-view-all   { font-size:12px; color:var(--accent); text-decoration:none; font-weight:500; }
.db-view-all:hover { text-decoration:underline; }

.db-trend-legend { display:flex; align-items:center; gap:14px; font-size:11px; color:var(--text-dim); }
.db-legend-dot-solid { display:inline-block; width:22px; height:3px; background:var(--accent); border-radius:2px; vertical-align:middle; }
.db-legend-dot-dash  { display:inline-block; width:22px; height:0; border-top:2px dashed var(--border); vertical-align:middle; }

/* ══ CHARTS ROW ══════════════════════════════════════════════════════ */
.db-row-60-40 { display:grid; grid-template-columns:1.5fr 1fr; gap:16px; }

.wdb-cat-layout { display:flex; align-items:center; gap:16px; }
.wdb-cat-row    { display:flex; align-items:center; gap:7px; }
.wdb-cat-dot    { display:inline-block; width:10px; height:10px; border-radius:50%; flex-shrink:0; }
/* Legend base — flex-grow beside donut on desktop */
.wdb-cat-legend { flex:1; min-width:0; display:flex; flex-direction:column; gap:7px; }

/* ══ BOTTOM 3-COLUMN ROW ═════════════════════════════════════════════ */
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
.db-stock-count { font-size:13px; font-weight:700; color:var(--red); white-space:nowrap; }
.db-stock-unit  { font-size:11px; font-weight:400; color:var(--text-dim); }

/* ── Recent Activities ── */
.db-txn-row { display:flex; align-items:center; gap:12px; padding:9px 0; border-bottom:0.5px solid var(--border); }
.db-txn-row:last-child { border-bottom:none; }
.db-txn-icon { width:34px; height:34px; border-radius:8px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.db-txn-icon svg { width:16px; height:16px; }
.db-txn-info  { flex:1; min-width:0; }
.db-txn-title { font-size:13px; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.db-txn-date  { font-size:11px; color:var(--text-dim); margin-top:1px; }

/* ══ INSIGHTS STRIP ══════════════════════════════════════════════════ */
.db-insights-wrap  { display:flex; align-items:stretch; gap:16px; }
.db-insights-left  { flex:1; }
.db-insights-head  { display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
.db-insights-star  { width:32px; height:32px; border-radius:8px; background:var(--accent-dim); flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.db-insights-star svg { width:16px; height:16px; color:var(--accent); }
.db-insights-title { font-size:14px; font-weight:600; color:var(--text); }
.db-insight-line   { font-size:13px; color:var(--text-sub); line-height:1.65; padding:4px 0; border-bottom:0.5px solid var(--border); }
.db-insight-line:last-child { border-bottom:none; }
.db-insights-right { width:160px; flex-shrink:0; display:flex; align-items:flex-end; justify-content:flex-end; }

/* ══ RESPONSIVE ══════════════════════════════════════════════════════ */
@media(max-width:1200px) {
    .db-row-60-40 { grid-template-columns:1fr; }
}
@media(max-width:1100px) {
    .db-row-cf-side { grid-template-columns:1fr 1fr; }
    .db-row-cf-side > .db-card:nth-child(3) { grid-column:1 / -1; height:380px; }
}
@media(max-width:900px) {
    .db-kpi-row     { grid-template-columns:repeat(2,1fr); }
    .db-row-cf-side { grid-template-columns:1fr; }
    .db-row-cf-side > .db-card:nth-child(3) { grid-column:auto; height:420px; }
    .db-insights-right { display:none; }

    /* Stock by Category — donut above, legend scrollable on mobile */
    .wdb-cat-card { height:420px; overflow:hidden; display:flex; flex-direction:column; }
    .wdb-cat-card > .db-card-head { flex-shrink:0; }
    .wdb-cat-layout { flex:1; flex-direction:column; align-items:center; gap:10px; min-height:0; overflow:hidden; }
    .wdb-cat-donut-wrap { flex-shrink:0; }
    .wdb-cat-legend { flex:1; width:100%; overflow-y:auto; min-height:0; display:flex; flex-direction:column; gap:0; }
    .wdb-cat-legend::-webkit-scrollbar { width:4px; }
    .wdb-cat-legend::-webkit-scrollbar-track { background:transparent; }
    .wdb-cat-legend::-webkit-scrollbar-thumb { background:var(--border); border-radius:4px; }
}
@media(max-width:520px) {
    .db-kpi-row { grid-template-columns:1fr; }
    .db-row-cf-side > .db-card { height:360px; }
}

[x-cloak] { display:none !important; }
</style>

{{-- Data bridge for Chart.js --}}
<div id="wdb-data"
    data-spark-inbound="{{ json_encode($sparkInbound) }}"
    data-spark-outbound="{{ json_encode($sparkOutbound) }}"
    data-trend-labels="{{ json_encode($trendLabels) }}"
    data-trend-current="{{ json_encode($trendCurrent) }}"
    data-trend-prev="{{ json_encode($trendPrev) }}"
    data-cat-labels="{{ json_encode($categoryBreakdown->pluck('name')) }}"
    data-cat-values="{{ json_encode($categoryBreakdown->pluck('total_items')->map(fn($v)=>(int)$v)) }}"
    data-full-boxes="{{ $fullBoxes }}"
    data-partial-boxes="{{ $partialBoxes }}"
    data-damaged-boxes="{{ $damagedBoxes }}"
    data-total-boxes="{{ $totalBoxes + $damagedBoxes }}"
    data-inbound-boxes="{{ $inboundBoxes }}"
    data-outbound-boxes="{{ $outboundBoxes }}"
    style="display:none"></div>

{{-- Period bar --}}
<div class="db-period-bar">

    {{-- Row 1: preset pills (no Custom button — edit dates directly below) --}}
    <div class="db-period-pills">
        @foreach(['today'=>'Today','yesterday'=>'Yesterday','week'=>'This Week','month'=>'This Month','last_month'=>'Last Month','last_30'=>'Last 30 Days'] as $key => $label)
        <button wire:click="setPreset('{{ $key }}')" wire:key="preset-{{ $key }}" class="db-period-pill {{ $preset === $key ? 'active' : '' }}">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Row 2: always-visible live date inputs + sync dot + warehouse selector --}}
    <div class="db-period-controls">
        <div class="db-period-ctrl-seg db-period-ctrl-grow">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--text-dim)"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
            <input type="date" wire:model="dateFrom" class="db-date-input">
            <span style="font-size:13px;color:var(--text-dim);flex-shrink:0;">→</span>
            <input type="date" wire:model="dateTo" class="db-date-input">
        </div>
        <div class="db-period-ctrl-seg">
            <span class="db-sync-dot green"></span>
            <span style="font-size:12px;color:var(--text-dim);">Live</span>
        </div>
        @if(auth()->user()->isOwner())
        <div class="db-period-ctrl-seg">
            <svg style="width:12px;height:12px;flex-shrink:0;color:var(--text-dim)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <form method="GET" action="{{ route('warehouse.dashboard') }}" style="display:inline;">
                <select name="warehouse_id" onchange="this.form.submit()"
                        style="font-size:12px;font-weight:600;color:var(--text);background:transparent;border:none;cursor:pointer;padding:0;outline:none;font-family:var(--font);">
                    @foreach(\App\Models\Warehouse::orderBy('name')->get() as $wh)
                        <option value="{{ $wh->id }}" {{ $wh->id == $warehouseId ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        @endif
    </div>

</div>

{{-- ══ ROW 1: KPI CARDS ═══════════════════════════════════════════════ --}}
<div class="db-kpi-row" style="margin-bottom:20px;">

    {{-- Total Stock Boxes --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:var(--accent-dim);">
                <svg fill="none" stroke="var(--accent)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">Total Stock Boxes</span>
                <span class="db-kpi-value">{{ number_format($totalBoxes) }}<span class="db-kpi-unit">Boxes</span></span>
            </div>
        </div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                <span class="db-change-text {{ $netStockChange >= 0 ? 'up' : 'down' }}">
                    {{ $netStockChange >= 0 ? '+' : '' }}{{ number_format($netStockChange) }} boxes
                </span>
                <span class="db-kpi-vs">net this period</span>
            </div>
            <div class="db-kpi-spark"><canvas id="wdb-spark-0" wire:ignore width="90" height="36"></canvas></div>
        </div>
    </div>

    {{-- Inbound Boxes --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:var(--green-dim);">
                <svg fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">Inbound Boxes</span>
                <span class="db-kpi-value">{{ number_format($inboundBoxes) }}<span class="db-kpi-unit">Boxes</span></span>
            </div>
        </div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                <span class="db-change-text {{ $inboundChange >= 0 ? 'up' : 'down' }}">
                    {{ $inboundChange >= 0 ? '↑' : '↓' }} {{ abs($inboundChange) }}%
                </span>
                <span class="db-kpi-vs">vs {{ $prevPeriodLabel }}</span>
            </div>
            <div class="db-kpi-spark"><canvas id="wdb-spark-1" wire:ignore width="90" height="36"></canvas></div>
        </div>
    </div>

    {{-- Outbound Boxes --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:var(--amber-dim);">
                <svg fill="none" stroke="var(--amber)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">Outbound Boxes</span>
                <span class="db-kpi-value">{{ number_format($outboundBoxes) }}<span class="db-kpi-unit">Boxes</span></span>
            </div>
        </div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                <span class="db-change-text {{ $outboundBoxesChange >= 0 ? 'up' : 'down' }}">
                    {{ $outboundBoxesChange >= 0 ? '↑' : '↓' }} {{ abs($outboundBoxesChange) }}%
                </span>
                <span class="db-kpi-vs">vs {{ $prevPeriodLabel }}</span>
            </div>
            <div class="db-kpi-spark"><canvas id="wdb-spark-2" wire:ignore width="90" height="36"></canvas></div>
        </div>
    </div>

    {{-- Low Stock Products --}}
    <div class="db-kpi {{ $lowStockCount > 0 ? 'db-kpi--warn' : '' }}">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:{{ $lowStockCount > 0 ? 'var(--amber-dim)' : 'var(--accent-dim)' }};">
                <svg fill="none" stroke="{{ $lowStockCount > 0 ? 'var(--amber)' : 'var(--accent)' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">Low Stock Products</span>
                <span class="db-kpi-value" style="{{ $lowStockCount > 0 ? 'color:var(--amber);' : '' }}">{{ $lowStockCount }}<span class="db-kpi-unit">Products</span></span>
            </div>
        </div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                @if($lowStockCount > 0)
                <span class="db-change-text warn">⚠ Needs Attention</span>
                @else
                <span class="db-change-text up">✓ All Clear</span>
                @endif
                <span class="db-kpi-vs">current stock levels</span>
            </div>
        </div>
    </div>

</div>

{{-- ══ ROW 2: CHARTS ══════════════════════════════════════════════════ --}}
<div class="db-row-60-40" style="margin-bottom:20px;">

    {{-- Stock Trend --}}
    <div class="db-card">
        <div class="db-card-head">
            <span class="db-card-title">Stock Trend</span>
            <div class="db-trend-legend">
                <span class="db-legend-dot-solid"></span> This Period
                <span class="db-legend-dot-dash"></span> Previous Period
            </div>
        </div>
        <div style="position:relative;height:220px;">
            <canvas id="wdbTrendChart" wire:ignore></canvas>
        </div>
    </div>

    {{-- Stock by Category --}}
    <div class="db-card wdb-cat-card">
        <div class="db-card-head">
            <span class="db-card-title">Stock by Category <span style="font-size:10px;font-weight:600;background:var(--green-dim);color:var(--green);padding:1px 6px;border-radius:4px;vertical-align:middle;margin-left:4px;">Live</span></span>
            <a href="{{ route('warehouse.inventory.stock-levels') }}" class="db-view-all">View all</a>
        </div>
        @if($categoryBreakdown->isEmpty())
            <div style="display:flex;align-items:center;justify-content:center;height:180px;font-size:13px;color:var(--text-faint);">No stock data</div>
        @else
        <div class="wdb-cat-layout">
            {{-- Donut --}}
            <div class="wdb-cat-donut-wrap" style="position:relative;width:160px;height:160px;flex-shrink:0;">
                <canvas id="wdbCategoryDonut" width="160" height="160" wire:ignore></canvas>
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;text-align:center;">
                    <div style="font-size:18px;font-weight:700;color:var(--text);font-family:var(--mono);line-height:1.2;">
                        {{ number_format($totalBoxes + $damagedBoxes) }}
                    </div>
                    <div style="font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.05em;color:var(--text-dim);margin-top:2px;">Total Boxes</div>
                </div>
            </div>
            {{-- Legend --}}
            <div class="wdb-cat-legend">
                @php $catTotal = max(1, $totalBoxes + $damagedBoxes); @endphp
                @foreach($categoryBreakdown as $idx => $cat)
                <div class="wdb-cat-row" style="padding:6px 0;border-bottom:0.5px solid var(--border);">
                    <span class="wdb-cat-dot" data-idx="{{ $idx }}"></span>
                    <span style="flex:1;font-size:12px;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $cat->name }}</span>
                    <span style="font-size:12px;font-weight:600;color:var(--text);font-family:var(--mono);">{{ number_format((int)$cat->total_items) }}</span>
                    <span style="font-size:11px;color:var(--text-faint);min-width:38px;text-align:right;">({{ round($cat->total_items / $catTotal * 100, 1) }}%)</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

</div>

{{-- ══ ROW 3: INBOUND/OUTBOUND + LOW STOCK + RECENT ACTIVITIES ═════════ --}}
<div class="db-row-cf-side" style="margin-bottom:20px;">

    {{-- Stock Breakdown Donut --}}
    <div class="db-card">
        <div class="db-card-head">
            <span class="db-card-title">Stock Breakdown <span style="font-size:10px;font-weight:600;background:var(--green-dim);color:var(--green);padding:1px 6px;border-radius:4px;vertical-align:middle;margin-left:4px;">Live</span></span>
            <a href="{{ route('warehouse.inventory.stock-levels') }}" class="db-view-all">View all</a>
        </div>
        <div class="db-scroll-body" style="display:flex;flex-direction:column;align-items:center;">
            @php $allBoxes = $totalBoxes + $damagedBoxes; @endphp
            {{-- Donut --}}
            <div style="position:relative;width:210px;height:210px;flex-shrink:0;">
                <canvas id="wdbFlowDonut" width="210" height="210" wire:ignore></canvas>
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;text-align:center;">
                    <div style="font-size:8px;font-weight:700;letter-spacing:.08em;color:var(--text-dim);text-transform:uppercase;">TOTAL</div>
                    <div style="font-size:22px;font-weight:800;color:var(--text);line-height:1.1;font-family:var(--mono);">
                        {{ number_format($allBoxes) }}
                    </div>
                    <div style="font-size:9px;font-weight:600;color:var(--text-dim);text-transform:uppercase;">boxes</div>
                </div>
            </div>
            {{-- Compact table --}}
            <div style="width:100%;margin-top:12px;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:0.5px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:7px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:var(--accent);flex-shrink:0;display:inline-block;"></span>
                        <span style="font-size:12px;color:var(--text-dim);">Full</span>
                    </div>
                    <span style="font-size:13px;font-weight:600;color:var(--text);font-family:var(--mono);">{{ number_format($fullBoxes) }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:0.5px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:7px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:var(--amber);flex-shrink:0;display:inline-block;"></span>
                        <span style="font-size:12px;color:var(--text-dim);">Partial</span>
                    </div>
                    <span style="font-size:13px;font-weight:600;color:var(--text);font-family:var(--mono);">{{ number_format($partialBoxes) }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:0.5px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:7px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:var(--red);flex-shrink:0;display:inline-block;"></span>
                        <span style="font-size:12px;color:var(--text-dim);">Damaged</span>
                    </div>
                    <span style="font-size:13px;font-weight:600;color:var(--red);font-family:var(--mono);">{{ number_format($damagedBoxes) }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0 0;">
                    <span style="font-size:11px;color:var(--text-dim);">Total items remaining</span>
                    <span style="font-size:13px;font-weight:600;color:var(--text);font-family:var(--mono);">{{ number_format($totalItems) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Low Stock Alerts --}}
    <div class="db-card">
        <div class="db-card-head">
            <span class="db-card-title">Low Stock Alerts <span style="font-size:10px;font-weight:600;background:var(--green-dim);color:var(--green);padding:1px 6px;border-radius:4px;vertical-align:middle;margin-left:4px;">Live</span></span>
            <a href="{{ route('warehouse.inventory.stock-levels') }}" class="db-view-all">View all</a>
        </div>
        <div class="db-scroll-body">
            @forelse($lowStockProducts as $product)
            <div class="db-stock-row">
                <div class="db-stock-thumb">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <span class="db-stock-name" title="{{ $product->name }}">{{ $product->name }}</span>
                <span class="db-stock-count">{{ $product->current_stock }} <span class="db-stock-unit">{{ $product->current_stock == 1 ? 'box' : 'boxes' }}</span></span>
            </div>
            @empty
            <div style="padding:30px 0;text-align:center;color:var(--text-dim);font-size:13px;">All products well stocked ✓</div>
            @endforelse
        </div>
    </div>

    {{-- Recent Activities --}}
    <div class="db-card">
        <div class="db-card-head">
            <span class="db-card-title">Recent Activities <span style="font-size:10px;font-weight:600;background:var(--green-dim);color:var(--green);padding:1px 6px;border-radius:4px;vertical-align:middle;margin-left:4px;">Live</span></span>
            <a href="{{ route('warehouse.transfers.index') }}" class="db-view-all">View all</a>
        </div>
        <div class="db-scroll-body">
            @forelse($activityFeed as $event)
            <div class="db-txn-row">
                <div class="db-txn-icon" style="background:{{ $event['color'] === 'green' ? 'var(--green-dim)' : 'var(--accent-dim)' }};">
                    @if($event['color'] === 'green')
                    <svg fill="none" stroke="var(--green)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    @elseif($event['color'] === 'accent')
                    <svg fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4"/></svg>
                    @else
                    <svg fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>
                <div class="db-txn-info">
                    <div class="db-txn-title">{{ $event['title'] }}</div>
                    <div class="db-txn-date">{{ $event['sub'] }}</div>
                </div>
                <span style="font-size:11px;color:var(--text-dim);white-space:nowrap;flex-shrink:0;">
                    {{ $event['time']?->format('M j, g:i A') ?? '—' }}
                </span>
            </div>
            @empty
            <div style="padding:30px 0;text-align:center;color:var(--text-dim);font-size:13px;">No recent activity</div>
            @endforelse
        </div>
    </div>

</div>

{{-- ══ ROW 4: WAREHOUSE INSIGHTS ══════════════════════════════════════ --}}
<div class="db-card" style="margin-bottom:28px;">
    <div class="db-insights-wrap">
        <div class="db-insights-left">
            <div class="db-insights-head">
                <div class="db-insights-star">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <span class="db-insights-title">Warehouse Insights</span>
            </div>
            @foreach($insights as $insight)
            <div class="db-insight-line">{!! $insight !!}</div>
            @endforeach
        </div>
        <div class="db-insights-right">
            <svg viewBox="0 0 200 120" style="width:100%;max-width:160px;opacity:.7;" xmlns="http://www.w3.org/2000/svg">
                <rect x="10" y="80" width="24" height="36" rx="3" fill="#3b6bd4" opacity=".7"/>
                <rect x="42" y="60" width="24" height="56" rx="3" fill="#3b6bd4" opacity=".8"/>
                <rect x="74" y="40" width="24" height="76" rx="3" fill="#3b6bd4" opacity=".9"/>
                <rect x="106" y="24" width="24" height="92" rx="3" fill="#3b6bd4"/>
                <rect x="138" y="50" width="24" height="66" rx="3" fill="#10b981" opacity=".8"/>
                <path d="M155 22 L170 12 L185 22 L185 50 L155 50 Z" fill="#64748b" opacity=".5"/>
                <rect x="162" y="32" width="8" height="10" rx="1" fill="#94a3b8"/>
                <path d="M8 80 L190 80" stroke="#e2e8f0" stroke-width="1"/>
                <circle cx="185" cy="10" r="3" fill="#f59e0b"/>
            </svg>
        </div>
    </div>
</div>

</div>

@script
<script>
Alpine.data('wdbDashboard', () => ({
    _tChart: null,
    _dChart: null,
    _fChart: null,
    _rafPending: false,

    init() {
        var self = this;
        setTimeout(function() { self._scheduleRedraw(); }, 400);
        this.$wire.$watch('dateFrom', function() { self._scheduleRedraw(); });
        this.$wire.$watch('dateTo',   function() { self._scheduleRedraw(); });
    },

    teardown() {
        ['wdbTrendChart', 'wdbCategoryDonut', 'wdbFlowDonut'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) { var inst = Chart.getChart(el); if (inst) inst.destroy(); }
        });
        this._tChart = null;
        this._dChart = null;
        this._fChart = null;
        this._rafPending = false;
    },

    _scheduleRedraw() {
        if (this._rafPending) return;
        this._rafPending = true;
        var self = this;
        requestAnimationFrame(function() { requestAnimationFrame(function() { self._rafPending = false; self._draw(); }); });
    },

    _draw() {
        var d = document.getElementById('wdb-data');
        if (!d) return;

        var sparkIn   = JSON.parse(d.dataset.sparkInbound  || '[]');
        var sparkOut  = JSON.parse(d.dataset.sparkOutbound || '[]');
        var tLabels   = JSON.parse(d.dataset.trendLabels   || '[]');
        var tCurrent  = JSON.parse(d.dataset.trendCurrent  || '[]');
        var tPrev     = JSON.parse(d.dataset.trendPrev     || '[]');
        var catLabels = JSON.parse(d.dataset.catLabels     || '[]');
        var catValues = JSON.parse(d.dataset.catValues     || '[]');
        var full      = parseInt(d.dataset.fullBoxes    || '0');
        var partial   = parseInt(d.dataset.partialBoxes || '0');
        var damaged   = parseInt(d.dataset.damagedBoxes || '0');

        this._buildSpark('wdb-spark-0', sparkIn,  'rgb(59,107,212)');
        this._buildSpark('wdb-spark-1', sparkIn,  'rgb(16,185,129)');
        this._buildSpark('wdb-spark-2', sparkOut, 'rgb(249,115,22)');
        this._buildTrend(tLabels, tCurrent, tPrev);
        this._buildFlowDonut(full, partial, damaged);
        if (catLabels.length) this._buildCatDonut(catLabels, catValues);
    },

    _buildSpark(canvasId, data, color) {
        var el = document.getElementById(canvasId);
        if (!el) return;
        var existing = Chart.getChart(el);
        if (existing) { existing.destroy(); }
        new Chart(el, {
            type: 'line',
            data: {
                labels: data.map(function(_,i){ return i; }),
                datasets: [{ data: data, borderColor: color, borderWidth: 1.8, pointRadius: 0, tension: 0.4, fill: false }]
            },
            options: {
                animation: false, responsive: false, maintainAspectRatio: false,
                plugins: { legend: { display:false }, tooltip: { enabled:false } },
                scales: { x: { display:false }, y: { display:false } }
            }
        });
    },

    _buildTrend(labels, current, prev) {
        var el = document.getElementById('wdbTrendChart');
        if (!el) return;
        if (this._tChart) { this._tChart.destroy(); this._tChart = null; }
        var cont = el.parentElement;
        el.width  = cont ? cont.clientWidth  : 600;
        el.height = cont ? cont.clientHeight : 220;
        this._tChart = new Chart(el, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'This Period',     data: current, borderColor: '#3b6bd4', borderWidth: 2.5, pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#3b6bd4', tension: 0.4, fill: false },
                    { label: 'Previous Period', data: prev,    borderColor: '#94a3b8', borderWidth: 1.8, borderDash: [5,5], pointRadius: 0, pointHoverRadius: 4, tension: 0.4, fill: false }
                ]
            },
            options: {
                animation: false, responsive: false, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: 'rgba(15,23,42,.85)', padding: 10, cornerRadius: 8, titleFont: { size: 12 }, bodyFont: { size: 12 } }
                },
                scales: {
                    x: { grid: { color: 'rgba(148,163,184,.1)' }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
                    y: { grid: { color: 'rgba(148,163,184,.1)' }, ticks: { font: { size: 11 }, color: '#94a3b8', callback: function(v){ return v >= 1000 ? (v/1000).toFixed(v%1000===0?0:1)+'K' : v; } }, beginAtZero: true }
                }
            }
        });
    },

    _buildFlowDonut(full, partial, damaged) {
        var el = document.getElementById('wdbFlowDonut');
        if (!el) return;
        if (this._fChart) { this._fChart.destroy(); this._fChart = null; }
        var total  = full + partial + damaged;
        var data   = total === 0 ? [1,1,1] : [full, partial, damaged];
        var colors = total === 0 ? ['rgba(148,163,184,.2)','rgba(148,163,184,.2)','rgba(148,163,184,.2)'] : ['#3b6bd4','#f59e0b','#ef4444'];
        el.width = 210; el.height = 210;
        this._fChart = new Chart(el, {
            type: 'doughnut',
            data: { labels: ['Full','Partial','Damaged'], datasets: [{ data: data, backgroundColor: colors, borderWidth: 0, hoverBorderWidth: 0 }] },
            options: {
                animation: false, responsive: false, cutout: '68%',
                plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15,23,42,.85)', callbacks: { label: function(ctx){ return total===0?' No data':' '+ctx.label+': '+Number(ctx.raw).toLocaleString()+' boxes'; } } } }
            }
        });
    },

    _buildCatDonut(labels, values) {
        var CAT_COLORS = ['#3b6bd4','#10b981','#f59e0b','#8b5cf6','#f97316','#06b6d4','#ef4444','#84cc16'];
        var el = document.getElementById('wdbCategoryDonut');
        if (!el) return;
        if (this._dChart) { this._dChart.destroy(); this._dChart = null; }
        document.querySelectorAll('.wdb-cat-dot').forEach(function(dot, i) {
            dot.style.background = CAT_COLORS[i % CAT_COLORS.length];
        });
        var colors = labels.map(function(_,i){ return CAT_COLORS[i % CAT_COLORS.length]; });
        el.width = 160; el.height = 160;
        this._dChart = new Chart(el, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 0, hoverBorderWidth: 0 }] },
            options: {
                animation: false, responsive: false, cutout: '68%',
                plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15,23,42,.85)', callbacks: { label: function(ctx){ return ' '+ctx.label+': '+Number(ctx.raw).toLocaleString()+' boxes'; } } } }
            }
        });
    }
}));
</script>
@endscript
