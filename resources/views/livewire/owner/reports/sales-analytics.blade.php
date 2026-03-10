{{-- ┌─────────────────────────────────────────────────────────────────────────┐
    │  Owner · Sales Analytics                                               │
    │  Tabs: Overview · Ledger · Audit · Sellers                            │
    │  Consistent with .bkpi design system (app.css)                        │
    └─────────────────────────────────────────────────────────────────────────┘ --}}
<div wire:poll.60s>

{{-- ══════════════════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:var(--text);letter-spacing:-0.5px;margin:0 0 4px">
            Sales Analytics
        </h1>
        <div style="font-size:13px;color:var(--text-dim);font-family:var(--mono)">
            {{ $this->activeDateRangeLabel }}
            @if($locationFilter !== 'all')
                · {{ $this->selectedShopName }}
            @endif
            · auto-refreshes every 60s
        </div>
    </div>

    {{-- Date range quick-select --}}
    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
        @foreach(['today' => 'Today', 'week' => 'Week', 'month' => 'Month', 'quarter' => 'Quarter', 'year' => 'Year'] as $k => $lbl)
            @php
                $startDate = match($k) {
                    'today'   => now()->startOfDay()->toDateString(),
                    'week'    => now()->startOfWeek()->toDateString(),
                    'month'   => now()->startOfMonth()->toDateString(),
                    'quarter' => now()->startOfQuarter()->toDateString(),
                    'year'    => now()->startOfYear()->toDateString(),
                    default   => now()->startOfMonth()->toDateString(),
                };
                $active = $dateFrom === $startDate && $dateTo === now()->toDateString();
            @endphp
            <button wire:click="setDateRange('{{ $k }}')"
                style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid {{ $active ? 'var(--accent)' : 'var(--border)' }};background:{{ $active ? 'var(--accent)' : 'var(--surface)' }};color:{{ $active ? '#fff' : 'var(--text-sub)' }};cursor:pointer;transition:all .15s">
                {{ $lbl }}
            </button>
        @endforeach

        {{-- Custom date range --}}
        <div style="display:flex;gap:6px;align-items:center">
            <input type="date" wire:model.live="dateFrom" max="{{ $dateTo }}"
                style="padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:12px;font-family:var(--mono)">
            <span style="color:var(--text-dim);font-size:12px">→</span>
            <input type="date" wire:model.live="dateTo" min="{{ $dateFrom }}" max="{{ now()->toDateString() }}"
                style="padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:12px;font-family:var(--mono)">
        </div>

        {{-- Shop filter --}}
        <select wire:model.live="locationFilter"
            style="padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:12px">
            <option value="all">All Shops</option>
            @foreach($this->shops as $shop)
                <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB BAR
══════════════════════════════════════════════════════════════════════════ --}}
<div style="display:flex;gap:2px;border-bottom:2px solid var(--border);margin-bottom:28px">
    @php
        $tabs = [
            'overview' => ['label' => 'Overview',  'icon' => 'M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 0h7v7h-7z'],
            'ledger'   => ['label' => 'Sales Ledger','icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            'audit'    => ['label' => 'Price Audit', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            'sellers'  => ['label' => 'Sellers',    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ];
    @endphp
    @foreach($tabs as $key => $tab)
        <button wire:click="setTab('{{ $key }}')"
            style="display:flex;align-items:center;gap:7px;padding:10px 18px;border:none;background:none;cursor:pointer;font-size:13px;font-weight:600;
                   color:{{ $activeTab === $key ? 'var(--accent)' : 'var(--text-sub)' }};
                   border-bottom:2px solid {{ $activeTab === $key ? 'var(--accent)' : 'transparent' }};
                   margin-bottom:-2px;transition:color .15s">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="{{ $tab['icon'] }}"/>
            </svg>
            {{ $tab['label'] }}
        </button>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: OVERVIEW
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'overview')

    {{-- ── KPI Row 1: Revenue · Gross Profit · Margin · Items ─────────────── --}}
    @php
        $rev  = $this->revenueKpis;
        $gp   = $this->grossProfitKpis;
        $iss  = $this->itemsSoldKpi;
        $ret  = $this->returnsImpact;
        $vo   = $this->voidedSalesStats;
        $ov   = $this->priceOverrideStats;
    @endphp

    <div class="biz-kpi-grid" style="margin-bottom:24px">

        {{-- Revenue --}}
        <div class="bkpi pink" style="animation:fadeUp .35s ease .05s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon pink">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <span class="bkpi-name">Revenue</span>
                </div>
                @php $rg = $rev['growth_percentage'] @endphp
                <span class="bkpi-pct {{ $rg >= 0 ? 'up' : 'down' }}">
                    {{ $rg >= 0 ? '↑' : '↓' }} {{ abs($rg) }}%
                </span>
            </div>
            <div class="bkpi-value">{{ number_format($rev['total_revenue']) }}</div>
            <div class="bkpi-meta">{{ number_format($rev['transactions_count']) }} transactions · RWF</div>
            <div style="display:flex;gap:0;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--pink);font-family:var(--mono)">{{ number_format($rev['avg_transaction_value']) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Avg Order</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:{{ $rev['total_discount'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ number_format($rev['total_discount']) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Discounts</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ number_format($rev['previous_revenue']) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Prev Period</div>
                </div>
            </div>
        </div>

        {{-- Gross Profit --}}
        <div class="bkpi green" style="animation:fadeUp .35s ease .10s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    </div>
                    <span class="bkpi-name">Gross Profit</span>
                </div>
                @php $gg = $gp['gross_profit_growth'] @endphp
                <span class="bkpi-pct {{ $gg >= 0 ? 'up' : 'down' }}">
                    {{ $gg >= 0 ? '↑' : '↓' }} {{ abs($gg) }}%
                </span>
            </div>
            <div class="bkpi-value" style="color:var(--green)">{{ number_format($gp['gross_profit']) }}</div>
            <div class="bkpi-meta">After cost of goods · RWF</div>
            <div style="display:flex;gap:0;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--green);font-family:var(--mono)">{{ $gp['margin_pct'] }}%</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Margin</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ number_format($gp['total_cost']) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Total Cost</div>
                </div>
                <div style="flex:1;text-align:center">
                    @php $md = $gp['margin_delta'] @endphp
                    <div style="font-size:11px;font-weight:700;color:{{ $md >= 0 ? 'var(--green)' : 'var(--red)' }};font-family:var(--mono)">
                        {{ $md >= 0 ? '+' : '' }}{{ $md }}pp
                    </div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">vs Prev</div>
                </div>
            </div>
        </div>

        {{-- Net Revenue (after returns) --}}
        <div class="bkpi blue" style="animation:fadeUp .35s ease .15s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <span class="bkpi-name">Net Revenue</span>
                </div>
                @php $rr = $ret['return_rate'] @endphp
                <span class="bkpi-pct {{ $rr > 5 ? 'down' : 'up' }}">
                    {{ $rr }}% returned
                </span>
            </div>
            <div class="bkpi-value">{{ number_format($gp['net_revenue']) }}</div>
            <div class="bkpi-meta">After {{ $ret['returns_count'] }} returns · RWF</div>
            <div style="display:flex;gap:0;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:{{ $ret['returned_revenue'] > 0 ? 'var(--red)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ number_format($ret['returned_revenue']) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Refunded</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ $ret['exchange_count'] }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Exchanges</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ $ret['items_returned'] }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Items Back</div>
                </div>
            </div>
        </div>

        {{-- Items sold + overrides --}}
        <div class="bkpi violet" style="animation:fadeUp .35s ease .20s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon violet">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    </div>
                    <span class="bkpi-name">Items Sold</span>
                </div>
                @php $ig = $iss['growth'] @endphp
                <span class="bkpi-pct {{ $ig >= 0 ? 'up' : 'down' }}">
                    {{ $ig >= 0 ? '↑' : '↓' }} {{ abs($ig) }}%
                </span>
            </div>
            <div class="bkpi-value">{{ number_format($iss['items_sold']) }}</div>
            <div class="bkpi-meta">Units in period</div>
            <div style="display:flex;gap:0;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:{{ $ov['override_sales_count'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ $ov['override_sales_count'] }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Overrides</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:{{ $vo['voided_count'] > 0 ? 'var(--red)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ $vo['voided_count'] }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Voided</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ $ov['override_rate'] }}%</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Override %</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Revenue Trend Chart ──────────────────────────────────────────────── --}}
    @php $trend = $this->revenueTrend @endphp
    @if(count($trend))
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:22px 24px;margin-bottom:24px;animation:fadeUp .4s ease .25s both">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text)">Revenue Trend</div>
                <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Daily revenue · {{ $this->activeDateRangeLabel }}</div>
            </div>
        </div>
        <div id="rev-trend-chart" style="min-height:180px"></div>
    </div>
    @endif

    {{-- ── Daily Scorecard ──────────────────────────────────────────────────── --}}
    @php $scorecard = $this->dailyScorecard; @endphp
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);margin-bottom:24px;animation:fadeUp .4s ease .30s both;overflow:hidden">
        <div style="padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text)">Daily Scorecard</div>
                <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Day-by-day breakdown · revenue, profit, returns</div>
            </div>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="border-bottom:1px solid var(--border);background:var(--bg)">
                        <th style="padding:9px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Date</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Revenue</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Transactions</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Items</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Gross Profit</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Margin</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Discounts</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--red);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Returns</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Net Rev.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(array_reverse($scorecard) as $day)
                    <tr style="border-bottom:1px solid var(--border);{{ $day['is_today'] ? 'background:rgba(59,111,212,.04)' : '' }};transition:background .1s" onmouseover="this.style.background='rgba(59,111,212,.03)'" onmouseout="this.style.background='{{ $day['is_today'] ? 'rgba(59,111,212,.04)' : 'transparent' }}'">
                        <td style="padding:9px 16px;white-space:nowrap">
                            <span style="font-weight:600;color:{{ $day['is_today'] ? 'var(--accent)' : 'var(--text)' }}">{{ $day['day_label'] }}</span>
                            @if($day['is_today'])<span style="margin-left:6px;font-size:10px;font-weight:700;color:var(--accent);background:var(--accent-dim);padding:1px 6px;border-radius:10px">TODAY</span>@endif
                        </td>
                        <td style="padding:9px 14px;text-align:right;font-family:var(--mono);font-weight:600;color:{{ $day['revenue'] > 0 ? 'var(--text)' : 'var(--text-dim)' }}">
                            {{ $day['revenue'] > 0 ? number_format($day['revenue']) : '—' }}
                        </td>
                        <td style="padding:9px 14px;text-align:right;font-family:var(--mono);color:var(--text-sub)">
                            {{ $day['transactions'] > 0 ? $day['transactions'] : '—' }}
                        </td>
                        <td style="padding:9px 14px;text-align:right;font-family:var(--mono);color:var(--text-sub)">
                            {{ $day['items_sold'] > 0 ? number_format($day['items_sold']) : '—' }}
                        </td>
                        <td style="padding:9px 14px;text-align:right;font-family:var(--mono);font-weight:600;color:var(--green)">
                            {{ $day['gross_profit'] > 0 ? number_format($day['gross_profit']) : '—' }}
                        </td>
                        <td style="padding:9px 14px;text-align:right">
                            @if($day['margin_pct'] > 0)
                                <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;font-family:var(--mono);
                                    background:{{ $day['margin_pct'] >= 30 ? 'var(--green-glow)' : ($day['margin_pct'] >= 15 ? 'rgba(251,191,36,.12)' : 'rgba(225,29,72,.08)') }};
                                    color:{{ $day['margin_pct'] >= 30 ? 'var(--green)' : ($day['margin_pct'] >= 15 ? 'var(--amber)' : 'var(--red)') }}">
                                    {{ $day['margin_pct'] }}%
                                </span>
                            @else
                                <span style="color:var(--text-dim)">—</span>
                            @endif
                        </td>
                        <td style="padding:9px 14px;text-align:right;font-family:var(--mono);color:{{ $day['discounts'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }}">
                            {{ $day['discounts'] > 0 ? number_format($day['discounts']) : '—' }}
                        </td>
                        <td style="padding:9px 14px;text-align:right;font-family:var(--mono);color:{{ $day['returns_count'] > 0 ? 'var(--red)' : 'var(--text-dim)' }}">
                            {{ $day['returns_count'] > 0 ? $day['returns_count'] . ' · ' . number_format($day['returned_amount']) : '—' }}
                        </td>
                        <td style="padding:9px 14px;text-align:right;font-family:var(--mono);font-weight:600;color:var(--text)">
                            {{ $day['net_revenue'] > 0 ? number_format($day['net_revenue']) : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="padding:32px;text-align:center;color:var(--text-dim)">No sales in this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Bottom Row: Top Products + Shop Breakdown + Payment Methods ─────── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

        {{-- Top products --}}
        @php $topProducts = $this->topProducts @endphp
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;animation:fadeUp .4s ease .35s both">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                <div style="font-size:13px;font-weight:700;color:var(--text)">Top Products</div>
                <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">By revenue · with margin</div>
            </div>
            <div style="overflow:auto;max-height:340px">
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                    <thead style="position:sticky;top:0;background:var(--bg);z-index:1">
                        <tr style="border-bottom:1px solid var(--border)">
                            <th style="padding:8px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Product</th>
                            <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Revenue</th>
                            <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $i => $p)
                        <tr style="border-bottom:1px solid var(--border)" onmouseover="this.style.background='rgba(59,111,212,.03)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:8px 16px">
                                <div style="font-weight:600;color:var(--text)">{{ $p['product_name'] }}</div>
                                <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono);margin-top:1px">{{ number_format($p['quantity_sold']) }} units · {{ $p['revenue_share'] }}% share</div>
                            </td>
                            <td style="padding:8px 10px;text-align:right;font-family:var(--mono);font-size:11px;font-weight:600;color:var(--text)">{{ number_format($p['revenue']) }}</td>
                            <td style="padding:8px 10px;text-align:right">
                                <span style="font-size:11px;font-weight:700;font-family:var(--mono);
                                    color:{{ $p['margin_pct'] >= 30 ? 'var(--green)' : ($p['margin_pct'] >= 15 ? 'var(--amber)' : 'var(--red)') }}">
                                    {{ $p['margin_pct'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Shop performance + payment methods stacked --}}
        <div style="display:flex;flex-direction:column;gap:20px">

            {{-- Shop performance --}}
            @php $shops = $this->shopPerformance @endphp
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;animation:fadeUp .4s ease .40s both">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                    <div style="font-size:13px;font-weight:700;color:var(--text)">Shop Performance</div>
                    <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Revenue share and growth per shop</div>
                </div>
                @php $maxShopRev = max(array_column($shops, 'revenue') ?: [1]) @endphp
                @foreach($shops as $shop)
                <div style="padding:12px 20px;border-bottom:1px solid var(--border)">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                        <span style="font-size:12px;font-weight:600;color:var(--text)">{{ $shop['shop_name'] }}</span>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-size:11px;font-family:var(--mono);font-weight:600;color:var(--text)">{{ number_format($shop['revenue']) }}</span>
                            @if($shop['growth'] !== null)
                                <span style="font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;font-family:var(--mono);
                                    background:{{ $shop['growth'] >= 0 ? 'var(--green-glow)' : 'rgba(225,29,72,.08)' }};
                                    color:{{ $shop['growth'] >= 0 ? 'var(--green)' : 'var(--red)' }}">
                                    {{ $shop['growth'] >= 0 ? '+' : '' }}{{ $shop['growth'] }}%
                                </span>
                            @endif
                        </div>
                    </div>
                    <div style="height:5px;background:var(--bg);border-radius:3px;overflow:hidden">
                        <div style="height:100%;width:{{ $maxShopRev > 0 ? round($shop['revenue'] / $maxShopRev * 100) : 0 }}%;background:linear-gradient(90deg,var(--accent),var(--violet));border-radius:3px;transition:width .6s ease"></div>
                    </div>
                    <div style="display:flex;gap:12px;margin-top:5px">
                        <span style="font-size:10px;color:var(--text-dim)">{{ $shop['transactions'] }} txn</span>
                        <span style="font-size:10px;color:var(--text-dim)">{{ $shop['revenue_share'] }}% share</span>
                        @if($shop['override_count'] > 0)
                        <span style="font-size:10px;color:var(--amber)">{{ $shop['override_count'] }} overrides</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Payment methods --}}
            @php $methods = $this->paymentMethods @endphp
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;animation:fadeUp .4s ease .45s both">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                    <div style="font-size:13px;font-weight:700;color:var(--text)">Payment Methods</div>
                </div>
                <div style="padding:12px 20px">
                    @foreach($methods as $m)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:8px;height:8px;border-radius:50%;background:{{ match($loop->index % 4) { 0=>'var(--accent)', 1=>'var(--green)', 2=>'var(--pink)', default=>'var(--violet)' } }}"></div>
                            <span style="font-size:12px;font-weight:600;color:var(--text)">{{ $m['label'] }}</span>
                        </div>
                        <div style="text-align:right">
                            <div style="font-size:12px;font-family:var(--mono);font-weight:600;color:var(--text)">{{ number_format($m['revenue']) }}</div>
                            <div style="font-size:10px;color:var(--text-dim)">{{ $m['count'] }} · {{ $m['revenue_share'] }}%</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- ── Returns Impact detail ────────────────────────────────────────────── --}}
    @if(count($ret['top_returned_products']))
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:24px;animation:fadeUp .4s ease .50s both">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(225,29,72,.10);display:grid;place-items:center;color:var(--red)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text)">Returns Impact</div>
                <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono)">{{ $ret['returns_count'] }} returns · {{ number_format($ret['returned_revenue']) }} RWF refunded · {{ $ret['return_rate'] }}% of gross revenue</div>
            </div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:0">
            @foreach($ret['top_returned_products'] as $rp)
            <div style="padding:10px 20px;border-right:1px solid var(--border);border-bottom:1px solid var(--border);min-width:160px">
                <div style="font-size:12px;font-weight:600;color:var(--text)">{{ $rp['product_name'] }}</div>
                <div style="font-size:11px;color:var(--red);font-family:var(--mono);margin-top:2px">{{ $rp['qty_returned'] }} units returned</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: SALES LEDGER
══════════════════════════════════════════════════════════════════════════ --}}
@elseif($activeTab === 'ledger')

    @php
        $gp   = $this->grossProfitKpis;
        $iss  = $this->itemsSoldKpi;
        $rev  = $this->revenueKpis;
        $topP = $this->topProducts;
    @endphp

    {{-- Summary strip --}}
    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px">
        @foreach([
            ['label'=>'Gross Revenue',  'value'=>number_format($gp['revenue']),      'color'=>'var(--accent)',  'sub'=>$rev['transactions_count'].' transactions'],
            ['label'=>'Total Cost',     'value'=>number_format($gp['total_cost']),   'color'=>'var(--text-sub)','sub'=>'Cost of goods'],
            ['label'=>'Gross Profit',   'value'=>number_format($gp['gross_profit']), 'color'=>'var(--green)',   'sub'=>$gp['margin_pct'].'% margin'],
            ['label'=>'Items Sold',     'value'=>number_format($iss['items_sold']),  'color'=>'var(--violet)',  'sub'=>'Units'],
        ] as $strip)
        <div style="flex:1;min-width:140px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:14px 18px">
            <div style="font-size:11px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;margin-bottom:6px">{{ $strip['label'] }}</div>
            <div style="font-size:22px;font-weight:700;letter-spacing:-0.5px;color:{{ $strip['color'] }}">{{ $strip['value'] }}</div>
            <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:3px">{{ $strip['sub'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Full product ledger table --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text)">Product Sales Ledger</div>
                <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Revenue, cost, and gross profit per product · {{ $this->activeDateRangeLabel }}</div>
            </div>
            <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ count($topP) }} products</span>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:var(--bg);border-bottom:1px solid var(--border)">
                        <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">#</th>
                        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Product</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Units</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Txns</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Avg Price</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Revenue</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Share</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Gross Profit</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Margin %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topP as $i => $p)
                    <tr style="border-bottom:1px solid var(--border);transition:background .1s" onmouseover="this.style.background='rgba(59,111,212,.03)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:10px 16px;font-size:11px;color:var(--text-dim);font-family:var(--mono)">{{ $i + 1 }}</td>
                        <td style="padding:10px 14px">
                            <div style="font-weight:600;color:var(--text)">{{ $p['product_name'] }}</div>
                        </td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ number_format($p['quantity_sold']) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ $p['transaction_count'] }}</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ number_format($p['avg_selling_price']) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--text)">{{ number_format($p['revenue']) }}</td>
                        <td style="padding:10px 14px;text-align:right">
                            <div style="height:4px;background:var(--bg);border-radius:2px;width:60px;display:inline-block;vertical-align:middle;margin-right:6px">
                                <div style="height:100%;width:{{ $p['revenue_share'] }}%;background:var(--accent);border-radius:2px"></div>
                            </div>
                            <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $p['revenue_share'] }}%</span>
                        </td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--green)">{{ number_format($p['gross_profit']) }}</td>
                        <td style="padding:10px 14px;text-align:right">
                            <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;font-family:var(--mono);
                                background:{{ $p['margin_pct'] >= 30 ? 'var(--green-glow)' : ($p['margin_pct'] >= 15 ? 'rgba(251,191,36,.12)' : 'rgba(225,29,72,.08)') }};
                                color:{{ $p['margin_pct'] >= 30 ? 'var(--green)' : ($p['margin_pct'] >= 15 ? 'var(--amber)' : 'var(--red)') }}">
                                {{ $p['margin_pct'] }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="padding:40px;text-align:center;color:var(--text-dim)">No sales in this period</td></tr>
                    @endforelse
                </tbody>
                @if(count($topP))
                <tfoot>
                    <tr style="background:var(--bg);border-top:2px solid var(--border)">
                        <td colspan="5" style="padding:10px 14px;font-size:12px;font-weight:700;color:var(--text-sub)">TOTALS</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--text)">{{ number_format(array_sum(array_column($topP, 'revenue'))) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:11px;font-family:var(--mono);color:var(--text-dim)">100%</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--green)">{{ number_format(array_sum(array_column($topP, 'gross_profit'))) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:12px;font-weight:700;font-family:var(--mono);color:var(--green)">{{ $gp['margin_pct'] }}%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: PRICE AUDIT
══════════════════════════════════════════════════════════════════════════ --}}
@elseif($activeTab === 'audit')

    @php
        $auditLog  = $this->priceAuditLog;
        $overStat  = $this->priceOverrideStats;
        $totalDisc = array_sum(array_column($auditLog, 'total_discount'));
    @endphp

    {{-- Summary strip --}}
    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px">
        @foreach([
            ['label'=>'Overridden Sales',    'value'=>$overStat['override_sales_count'],  'color'=>'var(--amber)',   'sub'=>'Sales with price changes'],
            ['label'=>'Overridden Items',    'value'=>$overStat['override_items_count'],  'color'=>'var(--text)',    'sub'=>'Line items modified'],
            ['label'=>'Total Discount Given','value'=>number_format($overStat['total_discount_given']),'color'=>'var(--red)','sub'=>'Revenue given away · RWF'],
            ['label'=>'Override Rate',       'value'=>$overStat['override_rate'].'%',     'color'=>'var(--text-sub)','sub'=>'Of all non-voided sales'],
        ] as $strip)
        <div style="flex:1;min-width:140px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:14px 18px">
            <div style="font-size:11px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;margin-bottom:6px">{{ $strip['label'] }}</div>
            <div style="font-size:22px;font-weight:700;letter-spacing:-0.5px;color:{{ $strip['color'] }}">{{ $strip['value'] }}</div>
            <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:3px">{{ $strip['sub'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Audit table --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text)">Price Modification Audit Trail</div>
                <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Every price change in the period · who, what, when, how much</div>
            </div>
            <span style="font-size:11px;font-family:var(--mono);color:{{ count($auditLog) > 0 ? 'var(--amber)' : 'var(--text-dim)' }}">
                {{ count($auditLog) }} modifications
            </span>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:var(--bg);border-bottom:1px solid var(--border)">
                        <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Date & Time</th>
                        <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Sale #</th>
                        <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Shop</th>
                        <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Seller</th>
                        <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Product</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Qty</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Original</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Actual</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--red);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Discount</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Margin</th>
                        <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Reason</th>
                        <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Approved</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLog as $entry)
                    <tr style="border-bottom:1px solid var(--border);transition:background .1s" onmouseover="this.style.background='rgba(59,111,212,.03)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:9px 16px;white-space:nowrap;font-family:var(--mono);font-size:11px;color:var(--text-sub)">
                            {{ \Carbon\Carbon::parse($entry['sale_date'])->format('M d, H:i') }}
                        </td>
                        <td style="padding:9px 12px;white-space:nowrap">
                            <span style="font-size:11px;font-family:var(--mono);font-weight:600;color:var(--accent)">{{ $entry['sale_number'] }}</span>
                        </td>
                        <td style="padding:9px 12px;font-size:11px;color:var(--text-sub);white-space:nowrap">{{ $entry['shop_name'] }}</td>
                        <td style="padding:9px 12px;font-size:11px;font-weight:600;color:var(--text);white-space:nowrap">{{ $entry['seller_name'] }}</td>
                        <td style="padding:9px 12px;font-size:11px;color:var(--text)">
                            {{ $entry['product_name'] }}
                            @if($entry['line_count'] > 1)
                                <span style="margin-left:4px;padding:1px 6px;background:var(--accent-glow);color:var(--accent);border-radius:10px;font-size:10px;font-weight:700;font-family:var(--mono)">×{{ $entry['line_count'] }}</span>
                            @endif
                        </td>
                        <td style="padding:9px 12px;text-align:right;font-family:var(--mono);font-size:11px;color:var(--text-sub)">{{ $entry['quantity_display'] }}</td>
                        <td style="padding:9px 12px;text-align:right;font-family:var(--mono);font-size:11px;color:var(--text-sub)">{{ number_format($entry['original_unit_price']) }}</td>
                        <td style="padding:9px 12px;text-align:right;font-family:var(--mono);font-size:11px;font-weight:700;color:var(--text)">{{ number_format($entry['actual_unit_price']) }}</td>
                        <td style="padding:9px 12px;text-align:right">
                            <div style="font-family:var(--mono);font-size:11px;font-weight:700;color:var(--red)">{{ number_format($entry['total_discount']) }}</div>
                            <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono)">{{ $entry['discount_pct'] }}% off</div>
                        </td>
                        <td style="padding:9px 12px;text-align:right">
                            @if($entry['margin_at_sale'] > 0)
                                <span style="font-size:11px;font-weight:700;font-family:var(--mono);
                                    color:{{ $entry['margin_at_sale'] >= 20 ? 'var(--green)' : ($entry['margin_at_sale'] >= 5 ? 'var(--amber)' : 'var(--red)') }}">
                                    {{ $entry['margin_at_sale'] }}%
                                </span>
                            @else
                                <span style="color:var(--red);font-size:11px;font-family:var(--mono);font-weight:700">{{ $entry['margin_at_sale'] }}%</span>
                            @endif
                        </td>
                        <td style="padding:9px 12px;font-size:11px;color:var(--text-sub);max-width:180px">
                            {{ $entry['reason'] ?? '—' }}
                            @if($entry['reference'])
                                <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono)">Ref: {{ $entry['reference'] }}</div>
                            @endif
                        </td>
                        <td style="padding:9px 12px;white-space:nowrap">
                            @if($entry['is_approved'])
                                <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;background:var(--green-glow);color:var(--green)">✓ {{ $entry['approved_by'] }}</span>
                            @else
                                <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;background:rgba(251,191,36,.12);color:var(--amber)">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12" style="padding:48px;text-align:center;color:var(--text-dim)">No price modifications in this period</td></tr>
                    @endforelse
                </tbody>
                @if(count($auditLog))
                <tfoot>
                    <tr style="background:var(--bg);border-top:2px solid var(--border)">
                        <td colspan="8" style="padding:10px 14px;font-size:12px;font-weight:700;color:var(--text-sub)">TOTAL DISCOUNT GIVEN</td>
                        <td style="padding:10px 12px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--red)">{{ number_format($totalDisc) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: SELLERS
══════════════════════════════════════════════════════════════════════════ --}}
@elseif($activeTab === 'sellers')

    @php
        $sellers   = $this->sellerPerformance;
        $customers = $this->customerRepeatAnalysis;
        $ret       = $this->returnsImpact;
    @endphp

    {{-- Seller performance table --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:24px">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text)">Seller Performance</div>
                <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">{{ $this->activeDateRangeLabel }} · ranked by revenue</div>
            </div>
            <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ count($sellers) }} sellers</span>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:var(--bg);border-bottom:1px solid var(--border)">
                        <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">#</th>
                        <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Seller</th>
                        <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Shop</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Txns</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Revenue</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Share</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Avg Order</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Items</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">GP</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Margin</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--amber);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Discounts</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--amber);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Overrides</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--red);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Voided</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sellers as $i => $s)
                    @php
                        $isTop = $i === 0;
                        $hasWarnings = $s['override_count'] > 3 || $s['void_count'] > 2;
                    @endphp
                    <tr style="border-bottom:1px solid var(--border);{{ $isTop ? 'background:rgba(14,158,134,.03)' : '' }};transition:background .1s"
                        onmouseover="this.style.background='rgba(59,111,212,.03)'"
                        onmouseout="this.style.background='{{ $isTop ? 'rgba(14,158,134,.03)' : 'transparent' }}'">
                        <td style="padding:10px 16px;font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $i + 1 }}</td>
                        <td style="padding:10px 12px">
                            <div style="display:flex;align-items:center;gap:7px">
                                <div style="width:28px;height:28px;border-radius:50%;display:grid;place-items:center;font-size:11px;font-weight:700;
                                    background:{{ $isTop ? 'var(--green-glow)' : 'var(--bg)' }};
                                    color:{{ $isTop ? 'var(--green)' : 'var(--text-sub)' }};border:1px solid var(--border)">
                                    {{ strtoupper(substr($s['seller_name'], 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;color:var(--text);font-size:12px">{{ $s['seller_name'] }}</div>
                                    @if($isTop)<div style="font-size:10px;color:var(--green)">Top seller</div>@endif
                                </div>
                            </div>
                        </td>
                        <td style="padding:10px 12px;font-size:11px;color:var(--text-sub)">{{ $s['shop_name'] }}</td>
                        <td style="padding:10px 12px;text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ $s['transactions'] }}</td>
                        <td style="padding:10px 12px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--text)">{{ number_format($s['revenue']) }}</td>
                        <td style="padding:10px 12px;text-align:right">
                            <div style="height:4px;background:var(--bg);border-radius:2px;width:50px;display:inline-block;vertical-align:middle;margin-right:5px">
                                <div style="height:100%;width:{{ min($s['revenue_share'], 100) }}%;background:var(--accent);border-radius:2px"></div>
                            </div>
                            <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $s['revenue_share'] }}%</span>
                        </td>
                        <td style="padding:10px 12px;text-align:right;font-family:var(--mono);color:var(--text-sub);font-size:11px">{{ number_format($s['avg_order']) }}</td>
                        <td style="padding:10px 12px;text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ number_format($s['items_sold']) }}</td>
                        <td style="padding:10px 12px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--green)">{{ number_format($s['gross_profit']) }}</td>
                        <td style="padding:10px 12px;text-align:right">
                            <span style="font-size:11px;font-weight:700;padding:2px 7px;border-radius:10px;font-family:var(--mono);
                                background:{{ $s['margin_pct'] >= 30 ? 'var(--green-glow)' : ($s['margin_pct'] >= 15 ? 'rgba(251,191,36,.12)' : 'rgba(225,29,72,.08)') }};
                                color:{{ $s['margin_pct'] >= 30 ? 'var(--green)' : ($s['margin_pct'] >= 15 ? 'var(--amber)' : 'var(--red)') }}">
                                {{ $s['margin_pct'] }}%
                            </span>
                        </td>
                        <td style="padding:10px 12px;text-align:right;font-family:var(--mono);color:{{ $s['total_discount'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }};font-size:11px">
                            {{ $s['total_discount'] > 0 ? number_format($s['total_discount']) : '—' }}
                        </td>
                        <td style="padding:10px 12px;text-align:right">
                            @if($s['override_count'] > 0)
                                <span style="font-size:11px;font-weight:700;font-family:var(--mono);padding:2px 7px;border-radius:10px;
                                    background:{{ $s['override_count'] > 3 ? 'rgba(217,119,6,.12)' : 'transparent' }};
                                    color:{{ $s['override_count'] > 3 ? 'var(--amber)' : 'var(--text-sub)' }}">
                                    {{ $s['override_count'] }}
                                </span>
                            @else
                                <span style="color:var(--text-dim)">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 12px;text-align:right">
                            @if($s['void_count'] > 0)
                                <span style="font-size:11px;font-weight:700;font-family:var(--mono);padding:2px 7px;border-radius:10px;
                                    background:rgba(225,29,72,.08);color:var(--red)">
                                    {{ $s['void_count'] }}
                                </span>
                            @else
                                <span style="color:var(--text-dim)">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="13" style="padding:48px;text-align:center;color:var(--text-dim)">No sales data for this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Customer Repeat Analysis + Returns side-by-side --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

        {{-- Customer analysis --}}
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                <div style="font-size:13px;font-weight:700;color:var(--text)">Customer Analysis</div>
                <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Repeat rate and top spenders</div>
            </div>

            {{-- Stats strip --}}
            <div style="display:flex;border-bottom:1px solid var(--border)">
                @foreach([
                    ['label' => 'Known Customers', 'value' => $customers['total_customers'],                      'color' => 'var(--accent)'],
                    ['label' => 'Repeat',           'value' => $customers['repeat_customers'],                     'color' => 'var(--green)'],
                    ['label' => 'Repeat Rate',      'value' => $customers['repeat_rate'].'%',                     'color' => $customers['repeat_rate'] >= 30 ? 'var(--green)' : 'var(--text-sub)'],
                    ['label' => 'Walk-ins',         'value' => $customers['walkin_count'],                        'color' => 'var(--text-dim)'],
                ] as $stat)
                <div style="flex:1;padding:12px 14px;text-align:center;border-right:1px solid var(--border)">
                    <div style="font-size:18px;font-weight:700;color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:2px">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>

            {{-- Top customers --}}
            <div style="overflow:auto;max-height:360px">
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                    <thead style="position:sticky;top:0;background:var(--bg);z-index:1">
                        <tr style="border-bottom:1px solid var(--border)">
                            <th style="padding:8px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Customer</th>
                            <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Visits</th>
                            <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Total Spent</th>
                            <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Avg</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers['top_customers'] as $c)
                        <tr style="border-bottom:1px solid var(--border)" onmouseover="this.style.background='rgba(59,111,212,.03)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:8px 16px">
                                <div style="display:flex;align-items:center;gap:7px">
                                    @if($c['is_repeat'])
                                    <span style="font-size:9px;font-weight:700;padding:1px 5px;border-radius:8px;background:var(--green-glow);color:var(--green)">↩</span>
                                    @endif
                                    <div>
                                        <div style="font-weight:600;color:var(--text)">{{ $c['name'] }}</div>
                                        @if($c['phone'])<div style="font-size:10px;color:var(--text-dim);font-family:var(--mono)">{{ $c['phone'] }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td style="padding:8px 10px;text-align:right;font-family:var(--mono);color:{{ $c['purchase_count'] > 1 ? 'var(--green)' : 'var(--text-sub)' }};font-weight:{{ $c['purchase_count'] > 1 ? '700' : '400' }}">
                                {{ $c['purchase_count'] }}
                            </td>
                            <td style="padding:8px 10px;text-align:right;font-family:var(--mono);font-weight:600;color:var(--text);font-size:11px">{{ number_format($c['total_spent']) }}</td>
                            <td style="padding:8px 10px;text-align:right;font-family:var(--mono);font-size:11px;color:var(--text-sub)">{{ number_format($c['avg_order']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="padding:32px;text-align:center;color:var(--text-dim)">No named customers in this period</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Returns detail for sellers tab --}}
        <div style="display:flex;flex-direction:column;gap:20px">

            <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                    <div style="font-size:13px;font-weight:700;color:var(--text)">Returns Summary</div>
                    <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Impact on net revenue</div>
                </div>
                <div style="display:flex;flex-wrap:wrap;border-bottom:1px solid var(--border)">
                    @foreach([
                        ['label' => 'Returns',      'value' => $ret['returns_count'],              'color' => 'var(--text)'],
                        ['label' => 'Refunded',     'value' => number_format($ret['returned_revenue']), 'color' => 'var(--red)'],
                        ['label' => 'Exchanges',    'value' => $ret['exchange_count'],              'color' => 'var(--amber)'],
                        ['label' => 'Return Rate',  'value' => $ret['return_rate'].'%',             'color' => $ret['return_rate'] > 5 ? 'var(--red)' : 'var(--green)'],
                    ] as $stat)
                    <div style="flex:1;min-width:80px;padding:12px 14px;text-align:center;border-right:1px solid var(--border)">
                        <div style="font-size:18px;font-weight:700;color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                        <div style="font-size:10px;color:var(--text-dim);margin-top:2px">{{ $stat['label'] }}</div>
                    </div>
                    @endforeach
                </div>

                @if(count($ret['top_returned_products']))
                <div style="padding:14px 20px">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;margin-bottom:10px">Most Returned Products</div>
                    @foreach($ret['top_returned_products'] as $rp)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--border)">
                        <span style="font-size:12px;color:var(--text)">{{ $rp['product_name'] }}</span>
                        <span style="font-size:11px;font-family:var(--mono);font-weight:700;color:var(--red)">{{ $rp['qty_returned'] }} units</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>
    </div>

@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     APEXCHARTS — Revenue Trend (Overview tab only)
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'overview' && count($this->revenueTrend))
@php $trend = $this->revenueTrend @endphp
<script>
    (function() {
        const dates    = @json(array_column($trend, 'date'));
        const revenues = @json(array_column($trend, 'revenue'));
        const profits  = @json(array_column($trend, 'revenue')); // use gross profit if available via separate prop

        const el = document.getElementById('rev-trend-chart');
        if (!el || typeof ApexCharts === 'undefined') return;

        new ApexCharts(el, {
            series: [{
                name: 'Revenue',
                data: revenues,
            }],
            chart: {
                type: 'area',
                height: 180,
                sparkline: { enabled: false },
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
                fontFamily: "'DM Mono', monospace",
            },
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.0, stops: [0, 100] }
            },
            colors: ['#3b6fd4'],
            xaxis: {
                categories: dates,
                labels: {
                    style: { fontSize: '10px', colors: '#7a81a0', fontFamily: "'DM Mono', monospace" },
                    rotate: -30,
                    formatter: (val) => {
                        const d = new Date(val);
                        return d.toLocaleDateString('en-GB', { month: 'short', day: 'numeric' });
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { fontSize: '10px', colors: '#7a81a0', fontFamily: "'DM Mono', monospace" },
                    formatter: (v) => v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v,
                }
            },
            grid: {
                borderColor: '#e2e6f3',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } }
            },
            dataLabels: { enabled: false },
            tooltip: {
                y: { formatter: (v) => new Intl.NumberFormat().format(v) + ' RWF' },
                theme: 'light',
            }
        }).render();
    })();
</script>
@endif

</div>