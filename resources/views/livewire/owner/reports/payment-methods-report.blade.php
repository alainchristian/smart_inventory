{{-- ┌─────────────────────────────────────────────────────────────────────────┐
    │  Owner · Payment Methods Report                                        │
    │  Track revenue by payment method and split payment analysis           │
    │  KPI cards use the shared .ui-kpi card (resources/css/app.css)        │
    └─────────────────────────────────────────────────────────────────────────┘ --}}
<div wire:poll.30s>
<style>
/* ── Font size increases for better readability ───────────────────── */
.pm-page-title { font-size:22px; }
.pm-page-subtitle { font-size:14px !important; }
.pm-date-btn { font-size:14px !important; padding:6px 16px !important; }
.pm-date-input, .pm-shop-select { font-size:14px !important; }
.pm-section-title { font-size:16px !important; }
/* Revenue by payment method — one quiet row per method */
.pm-mix      { display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:4px 32px }
.pm-mix-row  { padding:12px 0;border-bottom:1px solid var(--border);min-width:0 }
.pm-mix-top  { display:flex;align-items:center;gap:8px }
.pm-mix-dot  { width:8px;height:8px;border-radius:50%;flex-shrink:0 }
.pm-mix-name { flex:1;min-width:0;font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.pm-mix-amt  { font-family:var(--mono);font-size:14px;font-weight:700;color:var(--text);white-space:nowrap }
.pm-mix-unit { font-size:10px;font-weight:500;color:var(--text-dim) }
.pm-mix-bar  { height:4px;border-radius:2px;background:var(--border);margin:8px 0 6px;overflow:hidden }
.pm-mix-bar > div { height:100%;border-radius:2px }
.pm-mix-meta { font-size:11px;color:var(--text-dim) }
.pm-mix-zero .pm-mix-name, .pm-mix-zero .pm-mix-amt { color:var(--text-dim) }
.pm-section-subtitle { font-size:13px !important; }
.pm-table thead th { font-size:12px !important; white-space:nowrap }
.pm-table tbody td { font-size:14px !important; white-space:nowrap }
/* keep split-payment chips on one line; the table scrolls sideways instead of growing tall rows */
.pm-table td > div { flex-wrap:nowrap !important; }

/* ── Mobile responsive ───────────────────────────── */
@media(max-width:640px) {
    .pm-header-controls { flex-direction:column !important; align-items:stretch !important; gap:10px !important; width:100%; }
    .pm-header-controls > div { flex-wrap:nowrap !important; }
    .pm-section { padding:16px !important; margin-bottom:20px !important; }
    .pm-header-controls > div { flex-wrap:wrap; }
    .pm-header-controls input[type=date] { flex:1; min-width:0; }
    .pm-header-controls select { width:100%; }
    .pm-date-sep { display:none; }

    .pm-page-title { font-size:20px; }
    .pm-page-subtitle { font-size:13px !important; }
    .pm-date-btn { font-size:13px !important; }
}

/* KPI cards: shared .ui-kpi (app.css) */
.pm-growth   { font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;
               font-family:var(--mono);white-space:nowrap;flex-shrink:0 }
.pm-growth.up      { background:var(--green-dim);color:var(--green) }
.pm-growth.neutral { background:var(--surface2);color:var(--text-dim) }


</style>

{{-- ══════════════════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap">
    <div>
        <h1 class="pm-page-title m-dup-title" style="font-size:22px;font-weight:700;color:var(--text);letter-spacing:-0.5px;margin:0 0 4px">
            Payment Methods Report
        </h1>
        <div class="pm-page-subtitle" style="font-size:13px;color:var(--text-dim)">
            {{ $this->activeDateRangeLabel }}
            @if($locationFilter !== 'all')
                · {{ $this->selectedShopName }}
            @endif
            · auto-refreshes every 30s
        </div>
    </div>

    {{-- Date range quick-select --}}
    <div class="pm-header-controls" style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
        @php
            $currentPeriod = 'custom';
            $periods = [
                'today'   => ['label' => 'Today',        'start' => now()->startOfDay()->toDateString()],
                'week'    => ['label' => 'This Week',    'start' => now()->startOfWeek()->toDateString()],
                'month'   => ['label' => 'This Month',   'start' => now()->startOfMonth()->toDateString()],
                'quarter' => ['label' => 'This Quarter', 'start' => now()->startOfQuarter()->toDateString()],
                'year'    => ['label' => 'This Year',    'start' => now()->startOfYear()->toDateString()],
            ];
            foreach ($periods as $key => $period) {
                if ($dateFrom === $period['start'] && $dateTo === now()->toDateString()) {
                    $currentPeriod = $key;
                    break;
                }
            }
        @endphp
        <select wire:change="setDateRange($event.target.value)" class="pm-date-btn"
            style="padding:6px 16px;border-radius:8px;font-size:14px;font-weight:600;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer">
            <option value="custom" {{ $currentPeriod === 'custom' ? 'selected' : '' }}>Custom Range</option>
            @foreach($periods as $key => $period)
                <option value="{{ $key }}" {{ $currentPeriod === $key ? 'selected' : '' }}>{{ $period['label'] }}</option>
            @endforeach
        </select>

        {{-- Custom date range --}}
        <div style="display:flex;gap:6px;align-items:center">
            <input type="date" wire:model.live="dateFrom" max="{{ $dateTo }}" class="pm-date-input"
                style="padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:12px;font-family:var(--mono)">
            <span class="pm-date-sep" style="color:var(--text-dim);font-weight:600">→</span>
            <input type="date" wire:model.live="dateTo" min="{{ $dateFrom }}" max="{{ now()->toDateString() }}" class="pm-date-input"
                style="padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:12px;font-family:var(--mono)">
        </div>

        {{-- Shop filter --}}
        <select wire:model.live="locationFilter" class="pm-shop-select"
            style="padding:6px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer">
            <option value="all">All Shops</option>
            @foreach($this->shops as $shop)
                <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     SUMMARY KPI GRID
══════════════════════════════════════════════════════════════════════════ --}}
<div class="ui-kpis m-kpis">
    {{-- Total Revenue --}}
    <div class="ui-kpi">
        <div class="ui-kpi-row">
            <div class="ui-kpi-icon" style="background:var(--pink-dim);color:var(--pink)">
                <x-icon name="dollar-sign" size="16" />
            </div>
            <div class="ui-kpi-body">
                <div class="ui-kpi-label">Total Revenue</div>
                <div class="ui-kpi-sub">{{ number_format($this->totalTransactions) }} transactions</div>
            </div>
        </div>
        <div class="ui-kpi-val" style="color:var(--text)">{{ number_format($this->totalRevenue) }}</div>
        <div class="ui-kpi-divider"></div>
        <div class="ui-kpi-footer">
            <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ $this->splitPaymentStats['split'] }}</span><span class="ui-kpi-stat-l">Split</span></div>
            <div class="ui-kpi-stat" ><span class="ui-kpi-stat-v">{{ $this->creditSalesStats['count'] }}</span><span class="ui-kpi-stat-l">Credit</span></div>
            <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ number_format($this->totalTransactions) }}</span><span class="ui-kpi-stat-l">Total Txns</span></div>
        </div>
    </div>

    {{-- Total Transactions --}}
    <div class="ui-kpi">
        <div class="ui-kpi-row">
            <div class="ui-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <x-icon name="clipboard" size="16" />
            </div>
            <div class="ui-kpi-body">
                <div class="ui-kpi-label">Transactions</div>
                <div class="ui-kpi-sub">Non-voided sales</div>
            </div>
        </div>
        <div class="ui-kpi-val" style="color:var(--text)">{{ number_format($this->totalTransactions) }}</div>
        <div class="ui-kpi-divider"></div>
        <div class="ui-kpi-footer">
            <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ $this->splitPaymentStats['single'] }}</span><span class="ui-kpi-stat-l">Single</span></div>
            <div class="ui-kpi-stat" ><span class="ui-kpi-stat-v">{{ $this->splitPaymentStats['split'] }}</span><span class="ui-kpi-stat-l">Split</span></div>
            <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ number_format($this->totalRevenue) }}</span><span class="ui-kpi-stat-l">Revenue</span></div>
        </div>
    </div>

    {{-- Split Payments --}}
    <div class="ui-kpi">
        <div class="ui-kpi-row">
            <div class="ui-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <x-icon name="credit-card" size="16" />
            </div>
            <div class="ui-kpi-body">
                <div class="ui-kpi-label">Split Payments</div>
                <div class="ui-kpi-sub">Multiple methods per sale</div>
            </div>
            <span class="pm-growth {{ $this->splitPaymentStats['split_percentage'] >= 20 ? 'up' : 'neutral' }}">{{ $this->splitPaymentStats['split_percentage'] }}%</span>
        </div>
        <div class="ui-kpi-val" style="color:var(--violet)">{{ $this->splitPaymentStats['split'] }}</div>
        <div class="ui-kpi-divider"></div>
        <div class="ui-kpi-footer">
            <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ $this->splitPaymentStats['single'] }}</span><span class="ui-kpi-stat-l">Single</span></div>
            <div class="ui-kpi-stat" ><span class="ui-kpi-stat-v">{{ $this->splitPaymentStats['total'] }}</span><span class="ui-kpi-stat-l">Total</span></div>
            <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ $this->splitPaymentStats['split_percentage'] }}%</span><span class="ui-kpi-stat-l">Rate</span></div>
        </div>
    </div>

    {{-- Credit Sales --}}
    <div class="ui-kpi">
        <div class="ui-kpi-row">
            <div class="ui-kpi-icon" style="background:var(--red-dim);color:var(--red)">
                <x-icon name="tag" size="16" />
            </div>
            <div class="ui-kpi-body">
                <div class="ui-kpi-label">Credit Given</div>
                <div class="ui-kpi-sub">{{ $this->creditSalesStats['count'] }} credit sales</div>
            </div>
        </div>
        <div class="ui-kpi-val" style="color:var(--red)">{{ number_format($this->creditSalesStats['total_credit_given']) }}</div>
        <div class="ui-kpi-divider"></div>
        <div class="ui-kpi-footer">
            <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ $this->creditSalesStats['count'] }}</span><span class="ui-kpi-stat-l">Sales</span></div>
            <div class="ui-kpi-stat" ><span class="ui-kpi-stat-v">{{ number_format($this->totalTransactions) }}</span><span class="ui-kpi-stat-l">Total Txns</span></div>
            <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ number_format($this->totalRevenue) }}</span><span class="ui-kpi-stat-l">Revenue</span></div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     PAYMENT METHOD BREAKDOWN
══════════════════════════════════════════════════════════════════════════ --}}
<div class="pm-section" style="background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);padding:20px 24px;margin-bottom:24px">
    <h2 class="pm-section-title" style="font-size:18px;font-weight:700;color:var(--text);margin:0 0 20px">
        Revenue by Payment Method
    </h2>

    <div class="pm-mix">
        @foreach($this->paymentMethodSummary as $method => $data)
            @php
                $percentage = $this->totalRevenue > 0 ? round(($data['total'] / $this->totalRevenue) * 100, 1) : 0;
                $color = [
                    'cash'          => 'var(--green)',
                    'card'          => 'var(--accent)',
                    'mobile_money'  => 'var(--violet)',
                    'bank_transfer' => 'var(--amber)',
                    'credit'        => 'var(--red)',
                ][$method] ?? 'var(--text-dim)';
            @endphp
            <div class="pm-mix-row {{ $data['total'] > 0 ? '' : 'pm-mix-zero' }}">
                <div class="pm-mix-top">
                    <span class="pm-mix-dot" style="background:{{ $color }}"></span>
                    <span class="pm-mix-name">{{ $data['label'] }}</span>
                    <span class="pm-mix-amt">{{ number_format($data['total']) }} <span class="pm-mix-unit">RWF</span></span>
                </div>
                <div class="pm-mix-bar"><div style="width:{{ $percentage }}%;background:{{ $color }}"></div></div>
                <div class="pm-mix-meta">{{ number_format($data['count']) }} transaction{{ $data['count'] != 1 ? 's' : '' }} · {{ $percentage }}%</div>
            </div>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     RECENT TRANSACTIONS
══════════════════════════════════════════════════════════════════════════ --}}
<div class="pm-section" style="background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);padding:20px 24px">
    <h2 class="pm-section-title" style="font-size:18px;font-weight:700;color:var(--text);margin:0 0 16px">
        Recent Transactions
    </h2>

    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
        <table class="pm-table" style="min-width:680px;width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="border-bottom:2px solid var(--border)">
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Sale #</th>
                    <th style="text-align:right;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Total</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Date</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Shop</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Customer</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Payment Methods</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->recentTransactions as $sale)
                    <tr style="border-bottom:1px solid var(--border)">
                        <td data-label="Sale #" style="padding:12px 16px;font-family:var(--mono);font-weight:600;color:var(--text)">{{ $sale->sale_number }}</td>
                        <td data-label="Total" style="text-align:right;padding:12px 16px;font-family:var(--mono);font-weight:700;color:var(--text);white-space:nowrap">
                            {{ number_format($sale->total) }} RWF
                            @if($sale->has_credit)
                                <div style="font-size:10px;font-weight:600;color:var(--red);margin-top:2px">{{ number_format($sale->credit_amount) }} on credit</div>
                            @endif
                        </td>
                        <td data-label="Date" style="padding:12px 16px;color:var(--text-sub);font-size:12px">{{ local_time($sale->sale_date)->format('M d, Y h:i A') }}</td>
                        <td data-label="Shop" style="padding:12px 16px;color:var(--text)">{{ $sale->shop->name }}</td>
                        <td data-label="Customer" style="padding:12px 16px;color:var(--text)">
                            @if($sale->customer)
                                {{ $sale->customer->name }}
                            @elseif($sale->customer_name)
                                {{ $sale->customer_name }}
                            @else
                                <span style="color:var(--text-dim);font-style:italic">Walk-in</span>
                            @endif
                        </td>
                        <td data-label="Payment Methods" style="padding:12px 16px">
                            @if($sale->is_split_payment)
                                <div style="display:flex;gap:4px;flex-wrap:wrap">
                                    @foreach($sale->payments as $payment)
                                        <span style="font-size:10px;font-weight:600;padding:3px 8px;border-radius:6px;background:var(--surface);border:1px solid var(--border);color:var(--text-sub);text-transform:uppercase">
                                            {{ $payment->payment_method->label() }}: {{ number_format($payment->amount) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span style="font-size:11px;font-weight:600;padding:4px 10px;border-radius:6px;background:var(--surface);border:1px solid var(--border);color:var(--text);text-transform:uppercase">
                                    {{ $sale->payment_method->label() }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:32px;text-align:center;color:var(--text-dim);font-style:italic">
                            No transactions found for this period
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>
