{{-- ┌─────────────────────────────────────────────────────────────────────────┐
    │  Owner · Payment Methods Report                                        │
    │  Track revenue by payment method and split payment analysis           │
    │  KPI cards follow the .iv-kpi/.sa-kpi canonical structure              │
    └─────────────────────────────────────────────────────────────────────────┘ --}}
<div wire:poll.30s>
<style>
/* ── Font size increases for better readability ───────────────────── */
.pm-page-title { font-size:26px !important; }
.pm-page-subtitle { font-size:14px !important; }
.pm-date-btn { font-size:14px !important; padding:6px 16px !important; }
.pm-date-input, .pm-shop-select { font-size:14px !important; }
.pm-section-title { font-size:16px !important; }
.pm-section-subtitle { font-size:13px !important; }
.pm-table thead th { font-size:12px !important; white-space:nowrap }
.pm-table tbody td { font-size:14px !important; white-space:nowrap }

/* ── Mobile responsive ───────────────────────────── */
@media(max-width:640px) {
    .pm-header-controls { flex-direction:column !important; align-items:stretch !important; gap:10px !important; }
    .pm-header-controls > div { flex-wrap:wrap; }
    .pm-header-controls input[type=date] { flex:1; min-width:0; }
    .pm-header-controls select { width:100%; }
    .pm-date-sep { display:none; }

    .pm-page-title { font-size:24px !important; }
    .pm-page-subtitle { font-size:13px !important; }
    .pm-date-btn { font-size:13px !important; }
}

/* ── KPI cards — same anatomy as inventory-valuation's .iv-kpi ──────── */
.pm-kpis { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px }
.pm-kpi  { background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card);
           padding:22px 20px;display:flex;flex-direction:column;gap:16px;transition:box-shadow var(--tr) }
.pm-kpi:hover { box-shadow:var(--shadow-card-hover) }
.pm-kpi-row  { display:flex;align-items:center;gap:12px }
.pm-kpi-icon { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;
               justify-content:center;flex-shrink:0 }
.pm-kpi-body { flex:1;min-width:0 }
.pm-kpi-label { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                color:var(--text-dim) }
.pm-kpi-sub  { font-size:12px;color:var(--text-dim);margin-top:2px }
.pm-kpi-val  { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px }
.pm-kpi-divider { height:1px;background:var(--border) }
.pm-kpi-footer  { display:flex;flex-direction:column;gap:0 }
.pm-kpi-stat    { display:flex;flex-direction:row-reverse;justify-content:space-between;
                  align-items:center;padding:5px 0;border-bottom:1px solid var(--border);min-width:0 }
.pm-kpi-stat:last-child { border-bottom:none }
.pm-kpi-stat-v  { font-size:13px;font-weight:700;font-family:var(--mono);letter-spacing:-.3px;
                  max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap }
.pm-kpi-stat-l  { font-size:11px;color:var(--text-dim);flex-shrink:0;margin-right:8px }
.pm-growth   { font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;
               font-family:var(--mono);white-space:nowrap;flex-shrink:0 }
.pm-growth.up      { background:var(--green-dim);color:var(--green) }
.pm-growth.neutral { background:var(--surface2);color:var(--text-dim) }

@media(max-width:900px) { .pm-kpis { grid-template-columns:1fr 1fr;gap:10px } }
@media(max-width:640px) {
    .pm-kpis { grid-template-columns:1fr;gap:8px }
    .pm-kpi  { padding:14px }
    .pm-kpi-val { font-size:20px }
}

</style>

{{-- ══════════════════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap">
    <div>
        <h1 class="pm-page-title" style="font-size:22px;font-weight:700;color:var(--text);letter-spacing:-0.5px;margin:0 0 4px">
            Payment Methods Report
        </h1>
        <div class="pm-page-subtitle" style="font-size:13px;color:var(--text-dim);font-family:var(--mono)">
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
<div class="pm-kpis">
    {{-- Total Revenue --}}
    <div class="pm-kpi">
        <div class="pm-kpi-row">
            <div class="pm-kpi-icon" style="background:var(--pink-dim);color:var(--pink)">
                <x-icon name="dollar-sign" size="16" />
            </div>
            <div class="pm-kpi-body">
                <div class="pm-kpi-label">Total Revenue</div>
                <div class="pm-kpi-sub">{{ number_format($this->totalTransactions) }} transactions</div>
            </div>
        </div>
        <div class="pm-kpi-val" style="color:var(--text)">{{ number_format($this->totalRevenue / 100) }}</div>
        <div class="pm-kpi-divider"></div>
        <div class="pm-kpi-footer">
            <div class="pm-kpi-stat"><span class="pm-kpi-stat-v">{{ $this->splitPaymentStats['split'] }}</span><span class="pm-kpi-stat-l">Split</span></div>
            <div class="pm-kpi-stat" ><span class="pm-kpi-stat-v">{{ $this->creditSalesStats['count'] }}</span><span class="pm-kpi-stat-l">Credit</span></div>
            <div class="pm-kpi-stat"><span class="pm-kpi-stat-v">{{ number_format($this->totalTransactions) }}</span><span class="pm-kpi-stat-l">Total Txns</span></div>
        </div>
    </div>

    {{-- Total Transactions --}}
    <div class="pm-kpi">
        <div class="pm-kpi-row">
            <div class="pm-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <x-icon name="clipboard" size="16" />
            </div>
            <div class="pm-kpi-body">
                <div class="pm-kpi-label">Transactions</div>
                <div class="pm-kpi-sub">Non-voided sales</div>
            </div>
        </div>
        <div class="pm-kpi-val" style="color:var(--text)">{{ number_format($this->totalTransactions) }}</div>
        <div class="pm-kpi-divider"></div>
        <div class="pm-kpi-footer">
            <div class="pm-kpi-stat"><span class="pm-kpi-stat-v">{{ $this->splitPaymentStats['single'] }}</span><span class="pm-kpi-stat-l">Single</span></div>
            <div class="pm-kpi-stat" ><span class="pm-kpi-stat-v">{{ $this->splitPaymentStats['split'] }}</span><span class="pm-kpi-stat-l">Split</span></div>
            <div class="pm-kpi-stat"><span class="pm-kpi-stat-v">{{ number_format($this->totalRevenue / 100) }}</span><span class="pm-kpi-stat-l">Revenue</span></div>
        </div>
    </div>

    {{-- Split Payments --}}
    <div class="pm-kpi">
        <div class="pm-kpi-row">
            <div class="pm-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <x-icon name="credit-card" size="16" />
            </div>
            <div class="pm-kpi-body">
                <div class="pm-kpi-label">Split Payments</div>
                <div class="pm-kpi-sub">Multiple methods per sale</div>
            </div>
            <span class="pm-growth {{ $this->splitPaymentStats['split_percentage'] >= 20 ? 'up' : 'neutral' }}">{{ $this->splitPaymentStats['split_percentage'] }}%</span>
        </div>
        <div class="pm-kpi-val" style="color:var(--violet)">{{ $this->splitPaymentStats['split'] }}</div>
        <div class="pm-kpi-divider"></div>
        <div class="pm-kpi-footer">
            <div class="pm-kpi-stat"><span class="pm-kpi-stat-v">{{ $this->splitPaymentStats['single'] }}</span><span class="pm-kpi-stat-l">Single</span></div>
            <div class="pm-kpi-stat" ><span class="pm-kpi-stat-v">{{ $this->splitPaymentStats['total'] }}</span><span class="pm-kpi-stat-l">Total</span></div>
            <div class="pm-kpi-stat"><span class="pm-kpi-stat-v">{{ $this->splitPaymentStats['split_percentage'] }}%</span><span class="pm-kpi-stat-l">Rate</span></div>
        </div>
    </div>

    {{-- Credit Sales --}}
    <div class="pm-kpi">
        <div class="pm-kpi-row">
            <div class="pm-kpi-icon" style="background:var(--red-dim);color:var(--red)">
                <x-icon name="tag" size="16" />
            </div>
            <div class="pm-kpi-body">
                <div class="pm-kpi-label">Credit Given</div>
                <div class="pm-kpi-sub">{{ $this->creditSalesStats['count'] }} credit sales</div>
            </div>
        </div>
        <div class="pm-kpi-val" style="color:var(--red)">{{ number_format($this->creditSalesStats['total_credit_given'] / 100) }}</div>
        <div class="pm-kpi-divider"></div>
        <div class="pm-kpi-footer">
            <div class="pm-kpi-stat"><span class="pm-kpi-stat-v">{{ $this->creditSalesStats['count'] }}</span><span class="pm-kpi-stat-l">Sales</span></div>
            <div class="pm-kpi-stat" ><span class="pm-kpi-stat-v">{{ number_format($this->totalTransactions) }}</span><span class="pm-kpi-stat-l">Total Txns</span></div>
            <div class="pm-kpi-stat"><span class="pm-kpi-stat-v">{{ number_format($this->totalRevenue / 100) }}</span><span class="pm-kpi-stat-l">Revenue</span></div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     PAYMENT METHOD BREAKDOWN
══════════════════════════════════════════════════════════════════════════ --}}
<div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:32px">
    <h2 class="pm-section-title" style="font-size:18px;font-weight:700;color:var(--text);margin:0 0 20px">
        Revenue by Payment Method
    </h2>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
        @foreach($this->paymentMethodSummary as $method => $data)
            @php
                $percentage = $this->totalRevenue > 0 ? round(($data['total'] / $this->totalRevenue) * 100, 1) : 0;
                $colors = [
                    'cash' => ['bg' => '#10b981', 'light' => 'rgba(16,185,129,0.1)'],
                    'card' => ['bg' => '#3b82f6', 'light' => 'rgba(59,130,246,0.1)'],
                    'mobile_money' => ['bg' => '#8b5cf6', 'light' => 'rgba(139,92,246,0.1)'],
                    'bank_transfer' => ['bg' => '#f59e0b', 'light' => 'rgba(245,158,11,0.1)'],
                    'credit' => ['bg' => '#ef4444', 'light' => 'rgba(239,68,68,0.1)'],
                ];
                $color = $colors[$method] ?? ['bg' => '#6b7280', 'light' => 'rgba(107,114,128,0.1)'];
            @endphp
            <div style="padding:16px;border-radius:10px;background:{{ $color['light'] }};border:2px solid {{ $color['bg'] }}20">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <div style="font-size:13px;font-weight:600;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px">
                        {{ $data['label'] }}
                    </div>
                    <div style="font-size:11px;font-weight:700;color:{{ $color['bg'] }};background:white;padding:3px 8px;border-radius:6px">
                        {{ $percentage }}%
                    </div>
                </div>
                <div style="font-size:22px;font-weight:800;color:{{ $color['bg'] }};font-family:var(--mono);margin-bottom:4px">
                    {{ number_format($data['total'] / 100, 0) }} RWF
                </div>
                <div style="font-size:11px;color:var(--text-sub);font-weight:500">
                    {{ number_format($data['count']) }} transaction{{ $data['count'] != 1 ? 's' : '' }}
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     RECENT TRANSACTIONS
══════════════════════════════════════════════════════════════════════════ --}}
<div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:24px">
    <h2 class="pm-section-title" style="font-size:18px;font-weight:700;color:var(--text);margin:0 0 16px">
        Recent Transactions
    </h2>

    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
        <table class="pm-table" style="min-width:680px;width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="border-bottom:2px solid var(--border)">
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Sale #</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Date</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Shop</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Customer</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Payment Methods</th>
                    <th style="text-align:right;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->recentTransactions as $sale)
                    <tr style="border-bottom:1px solid var(--border)">
                        <td data-label="Sale #" style="padding:12px 16px;font-family:var(--mono);font-weight:600;color:var(--text)">{{ $sale->sale_number }}</td>
                        <td data-label="Date" style="padding:12px 16px;color:var(--text-sub);font-size:12px">{{ $sale->sale_date->format('M d, Y h:i A') }}</td>
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
                                            {{ $payment->payment_method->label() }}: {{ number_format($payment->amount / 100, 0) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span style="font-size:11px;font-weight:600;padding:4px 10px;border-radius:6px;background:var(--surface);border:1px solid var(--border);color:var(--text);text-transform:uppercase">
                                    {{ $sale->payment_method->label() }}
                                </span>
                            @endif
                        </td>
                        <td data-label="Total" style="text-align:right;padding:12px 16px;font-family:var(--mono);font-weight:700;color:var(--text)">
                            {{ number_format($sale->total / 100, 0) }} RWF
                            @if($sale->has_credit)
                                <span style="font-size:10px;color:var(--red);margin-left:4px">({{ number_format($sale->credit_amount / 100, 0) }} credit)</span>
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
