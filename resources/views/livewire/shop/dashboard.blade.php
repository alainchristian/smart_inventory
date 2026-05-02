<div class="db-page">

{{-- ════════════════════════════════════════════
     PERIOD FILTER BAR
════════════════════════════════════════════ --}}
<div class="db-period-bar">
    <div class="db-period-pills">
        @foreach([
            'today'      => 'Today',
            'yesterday'  => 'Yesterday',
            'this_week'  => 'This Week',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'last_30'    => 'Last 30 Days',
        ] as $key => $label)
        <button wire:click="setPeriod('{{ $key }}')"
                class="db-period-pill {{ $period === $key ? 'active' : '' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <button wire:click="setPeriod('custom')"
            class="db-period-custom {{ $period === 'custom' ? 'active' : '' }}">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/></svg>
        Custom Range
    </button>

    @if($showCustomPicker)
    <div class="db-custom-picker" x-data x-on:click.outside="$wire.cancelCustomPicker()">
        <span style="font-size:12px;color:var(--text-dim);white-space:nowrap;">From</span>
        <input type="date" wire:model="customFrom" class="db-date-input">
        <span style="font-size:12px;color:var(--text-dim);">to</span>
        <input type="date" wire:model="customTo"   class="db-date-input">
        <button wire:click="applyCustomRange" class="db-period-custom active">Apply</button>
        <button wire:click="cancelCustomPicker" class="db-period-custom">✕</button>
    </div>
    @endif

    <div class="db-period-label">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        {{ $periodLabel }}
        <span class="db-sync-dot {{ $lastSync->diffInMinutes(now()) < 5 ? 'green' : 'amber' }}"></span>
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
     data-cf-card="{{ $allowCard ? 1 : -1 }}"
></div>

{{-- ════════════════════════════════════════════
     KPI ROW
════════════════════════════════════════════ --}}
<div class="db-kpi-row">

    {{-- KPI 1: Total Sales --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:#eff6ff;">
                <svg fill="none" stroke="#3b82f6" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">Total Sales</span>
                <span class="db-kpi-value">{{ number_format($totalSales) }}<span class="db-kpi-unit">RWF</span></span>
            </div>
        </div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                <span class="db-change-text {{ $salesChange >= 0 ? 'up' : 'down' }}">
                    {{ $salesChange >= 0 ? '↑' : '↓' }} {{ number_format(abs($salesChange), 1) }}%
                </span>
                <span class="db-kpi-vs">vs previous period</span>
            </div>
            <div class="db-kpi-spark"><canvas id="sp-sales" wire:ignore width="90" height="36"></canvas></div>
        </div>
    </div>

    {{-- KPI 2: Transactions --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:#ecfdf5;">
                <svg fill="none" stroke="#10b981" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">Transactions</span>
                <span class="db-kpi-value">{{ number_format($txnCount) }}</span>
            </div>
        </div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                <span class="db-change-text {{ $txnChange >= 0 ? 'up' : 'down' }}">
                    {{ $txnChange >= 0 ? '↑' : '↓' }} {{ number_format(abs($txnChange), 1) }}%
                </span>
                <span class="db-kpi-vs">vs previous period</span>
            </div>
            <div class="db-kpi-spark"><canvas id="sp-txns" wire:ignore width="90" height="36"></canvas></div>
        </div>
    </div>

    {{-- KPI 3: Returns --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:#fff7ed;">
                <svg fill="none" stroke="#f97316" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">Returns</span>
                <span class="db-kpi-value">{{ number_format($periodReturns) }}<span class="db-kpi-unit">RWF</span></span>
            </div>
        </div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                <span class="db-change-text {{ $returnsChange <= 0 ? 'up' : 'down' }}">
                    {{ $returnsChange <= 0 ? '↓' : '↑' }} {{ number_format(abs($returnsChange), 1) }}%
                </span>
                <span class="db-kpi-vs">vs previous period</span>
            </div>
            <div class="db-kpi-spark"><canvas id="sp-returns" wire:ignore width="90" height="36"></canvas></div>
        </div>
    </div>

    {{-- KPI 4: Stock --}}
    <div class="db-kpi">
        <div class="db-kpi-top">
            <div class="db-kpi-circle" style="background:#f5f3ff;">
                <svg fill="none" stroke="#8b5cf6" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div class="db-kpi-meta">
                <span class="db-kpi-label">In Stock</span>
                <span class="db-kpi-value">{{ number_format($stockBoxes) }}<span class="db-kpi-unit">{{ Str::plural('box', $stockBoxes) }}</span></span>
            </div>
        </div>
        <div class="db-kpi-bottom">
            <div class="db-kpi-stats">
                <span class="db-change-text" style="color:var(--text-dim);">
                    {{ number_format($stockItems) }} items remaining
                </span>
            </div>
            <div style="height:36px;display:flex;align-items:center;">
                <div style="height:4px;width:100%;border-radius:2px;background:var(--surface2);overflow:hidden;">
                    <div style="height:100%;width:{{ min(100, ($stockBoxes > 0 ? 70 : 0)) }}%;background:linear-gradient(90deg,#8b5cf6,#6d28d9);border-radius:2px;"></div>
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
<div class="db-row-cf-side">

{{-- LEFT: Cash Flow Donut --}}
@php
    $cfTotalFmt = $cfTotal >= 1000000
        ? number_format($cfTotal / 1000000, 1) . 'M'
        : ($cfTotal >= 1000 ? number_format($cfTotal / 1000, 0) . 'K' : number_format($cfTotal));
@endphp
<div class="db-card">
    <div class="db-card-head">
        <span class="db-card-title">Cash Flow</span>
        <span style="font-size:11px;color:var(--text-dim);">{{ $periodLabel }}</span>
    </div>

    <div class="db-donut-body">
        {{-- Donut --}}
        <div class="db-donut-wrap">
            <canvas id="cfDonutChart" width="150" height="150"></canvas>
            <div class="db-donut-center">
                <div class="db-donut-lbl">INFLOW</div>
                <div class="db-donut-val">{{ $cfTotalFmt }}</div>
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
    @forelse($lowStockProducts as $product)
    <div class="db-stock-row">
        <div class="db-stock-thumb">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 18c.5-3 2.5-5 5-6l3-1.5 2-1.5c1-.8 2-1 3-1 2 0 4 1.5 4.5 3.5L21 18H3z"/></svg>
        </div>
        <span class="db-stock-name" title="{{ $product->name }}">{{ $product->name }}</span>
        <span class="db-stock-count">{{ $product->current_stock }} <span class="db-stock-unit">left</span></span>
    </div>
    @empty
    <div style="padding:30px 0;text-align:center;color:var(--text-dim);font-size:13px;">All products well stocked</div>
    @endforelse
</div>

{{-- Recent Transactions --}}
<div class="db-card">
    <div class="db-card-head">
        <span class="db-card-title">Recent Transactions</span>
        <a href="{{ route('shop.sales.index') }}" class="db-view-all">View all</a>
    </div>

    @php
    $txnList = collect();
    foreach ($recentSales as $sale) {
        $txnList->push(['type'=>'sale','title'=>'Sale #'.$sale->sale_number,'date'=>$sale->sale_date,'amount'=>$sale->total,'credit'=>true]);
    }
    foreach ($recentReturns as $ret) {
        $txnList->push(['type'=>'return','title'=>'Return #'.($ret->return_number ?? $ret->id),'date'=>$ret->created_at,'amount'=>$ret->refund_amount,'credit'=>false]);
    }
    $txnList = $txnList->sortByDesc('date')->take(6)->values();
    @endphp

    @forelse($txnList as $txn)
    <div class="db-txn-row">
        <div class="db-txn-icon" style="background:{{ $txn['type']==='sale' ? 'rgba(59,107,212,.12)' : 'rgba(226,75,74,.12)' }};">
            @if($txn['type'] === 'sale')
            <svg fill="none" stroke="#3b6bd4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            @else
            <svg fill="none" stroke="#e24b4a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            @endif
        </div>
        <div class="db-txn-info">
            <div class="db-txn-title">{{ $txn['title'] }}</div>
            <div class="db-txn-date">{{ \Carbon\Carbon::parse($txn['date'])->format('M j, Y g:i A') }}</div>
        </div>
        <span class="db-txn-amount {{ $txn['credit'] ? 'credit' : 'debit' }}">
            {{ $txn['credit'] ? '+' : '-' }}{{ number_format($txn['amount']) }} RWF
        </span>
    </div>
    @empty
    <div style="padding:30px 0;text-align:center;color:var(--text-dim);font-size:13px;">No transactions in this period</div>
    @endforelse
</div>

</div>{{-- /row-cf-side --}}

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
