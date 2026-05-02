<div>
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
    <div class="db-period-pills">
        @foreach(['today'=>'Today','yesterday'=>'Yesterday','this_week'=>'This Week','this_month'=>'This Month','last_month'=>'Last Month','last_30'=>'Last 30 Days'] as $key => $label)
        <button wire:click="setPeriod('{{ $key }}')" class="db-period-pill {{ $period === $key ? 'active' : '' }}">{{ $label }}</button>
        @endforeach
    </div>
    <button wire:click="setPeriod('custom')" class="db-period-custom {{ $period === 'custom' ? 'active' : '' }}">
        <svg style="width:13px;height:13px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Custom Range
    </button>
    @if($showCustomPicker)
    <div class="db-custom-picker" x-data x-on:click.outside="$wire.cancelCustomPicker()">
        From <input type="date" wire:model="customFrom" class="db-date-input">
        to <input type="date" wire:model="customTo" class="db-date-input">
        <button wire:click="applyCustomRange" class="db-period-custom active">Apply</button>
        <button wire:click="cancelCustomPicker" class="db-period-custom">✕</button>
    </div>
    @endif
    <div class="db-period-label">
        <svg style="width:13px;height:13px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        {{ $periodLabel }}
        <span class="db-sync-dot green"></span>
    </div>
    @if(auth()->user()->isOwner())
    <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text-dim);margin-left:4px;padding-left:10px;border-left:1px solid var(--border);">
        <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        <form method="GET" action="{{ route('warehouse.dashboard') }}" style="display:inline;">
            <select name="warehouse_id" onchange="this.form.submit()"
                    style="font-size:12px;font-weight:500;color:var(--text);background:transparent;border:none;cursor:pointer;padding:0 2px;outline:none;">
                @foreach(\App\Models\Warehouse::orderBy('name')->get() as $wh)
                    <option value="{{ $wh->id }}" {{ $wh->id == $warehouseId ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    @endif
</div>

{{-- ══ ROW 1: KPI CARDS ═══════════════════════════════════════════════ --}}
<div class="db-kpi-row" style="margin-bottom:20px;">

    {{-- Total Stock Boxes --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:#eff6ff;">
                <svg fill="none" stroke="#3b6bd4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
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
            <div class="db-kpi-circle" style="background:#ecfdf5;">
                <svg fill="none" stroke="#10b981" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
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
            <div class="db-kpi-circle" style="background:#fff7ed;">
                <svg fill="none" stroke="#f97316" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
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
            <div class="db-kpi-circle" style="background:{{ $lowStockCount > 0 ? 'rgba(245,158,11,.12)' : '#f5f3ff' }};">
                <svg fill="none" stroke="{{ $lowStockCount > 0 ? '#f59e0b' : '#8b5cf6' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">Low Stock Products</span>
                <span class="db-kpi-value" style="{{ $lowStockCount > 0 ? 'color:#f59e0b;' : '' }}">{{ $lowStockCount }}<span class="db-kpi-unit">Products</span></span>
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
    <div class="db-card">
        <div class="db-card-head">
            <span class="db-card-title">Stock by Category</span>
            <a href="{{ route('warehouse.inventory.stock-levels') }}" class="db-view-all">View all</a>
        </div>
        @if($categoryBreakdown->isEmpty())
            <div style="display:flex;align-items:center;justify-content:center;height:180px;font-size:13px;color:var(--text-faint);">No stock data</div>
        @else
        <div class="wdb-cat-layout">
            {{-- Donut --}}
            <div style="position:relative;width:160px;height:160px;flex-shrink:0;">
                <canvas id="wdbCategoryDonut" width="160" height="160"></canvas>
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;text-align:center;">
                    <div style="font-size:18px;font-weight:700;color:var(--text);font-family:var(--mono);line-height:1.2;">
                        {{ number_format($totalBoxes + $damagedBoxes) }}
                    </div>
                    <div style="font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.05em;color:var(--text-dim);margin-top:2px;">Total Boxes</div>
                </div>
            </div>
            {{-- Legend --}}
            <div style="flex:1;display:flex;flex-direction:column;gap:7px;min-width:0;">
                @php $catTotal = max(1, $totalBoxes + $damagedBoxes); @endphp
                @foreach($categoryBreakdown as $idx => $cat)
                <div class="wdb-cat-row">
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
            <span class="db-card-title">Stock Breakdown</span>
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
                        <span style="width:8px;height:8px;border-radius:50%;background:#3b6bd4;flex-shrink:0;display:inline-block;"></span>
                        <span style="font-size:12px;color:var(--text-dim);">Full</span>
                    </div>
                    <span style="font-size:13px;font-weight:600;color:var(--text);font-family:var(--mono);">{{ number_format($fullBoxes) }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:0.5px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:7px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;flex-shrink:0;display:inline-block;"></span>
                        <span style="font-size:12px;color:var(--text-dim);">Partial</span>
                    </div>
                    <span style="font-size:13px;font-weight:600;color:var(--text);font-family:var(--mono);">{{ number_format($partialBoxes) }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:0.5px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:7px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#ef4444;flex-shrink:0;display:inline-block;"></span>
                        <span style="font-size:12px;color:var(--text-dim);">Damaged</span>
                    </div>
                    <span style="font-size:13px;font-weight:600;color:#ef4444;font-family:var(--mono);">{{ number_format($damagedBoxes) }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0 0;">
                    <span style="font-size:11px;color:var(--text-dim);">↑ In <strong style="color:var(--text);">{{ number_format($inboundBoxes) }}</strong> &nbsp; ↓ Out <strong style="color:var(--text);">{{ number_format($outboundBoxes) }}</strong></span>
                    <span style="font-size:10px;color:var(--text-faint);">this period</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Low Stock Alerts --}}
    <div class="db-card">
        <div class="db-card-head">
            <span class="db-card-title">Low Stock Alerts</span>
            <a href="{{ route('warehouse.inventory.stock-levels') }}" class="db-view-all">View all</a>
        </div>
        <div class="db-scroll-body">
            @forelse($lowStockProducts as $product)
            <div class="db-stock-row">
                <div class="db-stock-thumb">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <span class="db-stock-name" title="{{ $product->name }}">{{ $product->name }}</span>
                <span class="db-stock-count">{{ $product->current_stock }} <span class="db-stock-unit">units</span></span>
            </div>
            @empty
            <div style="padding:30px 0;text-align:center;color:var(--text-dim);font-size:13px;">All products well stocked ✓</div>
            @endforelse
        </div>
    </div>

    {{-- Recent Activities --}}
    <div class="db-card">
        <div class="db-card-head">
            <span class="db-card-title">Recent Activities</span>
            <a href="{{ route('warehouse.transfers.index') }}" class="db-view-all">View all</a>
        </div>
        <div class="db-scroll-body">
            @forelse($activityFeed as $event)
            <div class="db-txn-row">
                <div class="db-txn-icon" style="background:{{ $event['color'] === 'green' ? 'rgba(16,185,129,.12)' : ($event['color'] === 'accent' ? 'rgba(59,107,212,.12)' : 'rgba(139,92,246,.12)') }};">
                    @if($event['color'] === 'green')
                    <svg fill="none" stroke="#10b981" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    @elseif($event['color'] === 'accent')
                    <svg fill="none" stroke="#3b6bd4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4"/></svg>
                    @else
                    <svg fill="none" stroke="#8b5cf6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
