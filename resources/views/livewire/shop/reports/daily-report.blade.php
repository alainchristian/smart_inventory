<div style="font-family:var(--font)">
<style>
/* ── Shop Daily Report ── dr- ──────────────────────────────────── */

.dr-header       { display:flex;align-items:flex-start;justify-content:space-between;
                   gap:16px;margin-bottom:24px;flex-wrap:wrap; }
.dr-header-title { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px; }
.dr-header-sub   { font-size:13px;color:var(--text-dim);margin:0; }

/* Filters */
.dr-filters     { background:var(--surface);border:none;border-radius:var(--r);
                  box-shadow:var(--shadow-card);margin-bottom:20px;
                  min-width:0;max-width:100%; }
.dr-presets-row { display:flex;gap:4px;overflow-x:auto;-webkit-overflow-scrolling:touch;
                  padding:10px 14px;border-bottom:1px solid var(--border);
                  scrollbar-width:none;flex-wrap:nowrap;min-width:0; }
.dr-presets-row::-webkit-scrollbar { display:none; }
.dr-preset-btn  { padding:5px 11px;border-radius:6px;font-size:12px;font-weight:600;
                  border:1px solid transparent;background:transparent;color:var(--text-dim);
                  cursor:pointer;white-space:nowrap;flex-shrink:0;transition:all var(--tr);
                  font-family:var(--font); }
.dr-preset-btn:hover  { background:var(--surface2);color:var(--text);border-color:var(--border); }
.dr-preset-btn.active { background:var(--accent);color:#fff;border-color:var(--accent);
                        box-shadow:0 2px 8px rgba(0,0,0,.12); }
.dr-filter-row  { display:flex;align-items:center;flex-wrap:wrap; }
.dr-filter-seg  { display:flex;align-items:center;gap:6px;padding:8px 14px;
                  border-right:1px solid var(--border);flex-shrink:0; }
.dr-filter-seg:last-child { border-right:none; }
.dr-date-input  { padding:0;border:none;background:transparent;color:var(--text);
                  font-size:13px;font-weight:600;font-family:var(--font);
                  cursor:pointer;width:130px;outline:none; }
.dr-date-input:focus { color:var(--accent); }

/* Report grid — pairs narrower tables side by side on wide screens to cut
   down on dead white space; wide/many-column tables span both columns. */
.dr-grid    { display:grid;grid-template-columns:repeat(2, minmax(0,1fr));gap:20px;margin-bottom:20px; }
.dr-span-2  { grid-column:span 2; }

@media(max-width:900px) {
    .dr-grid   { grid-template-columns:1fr; }
    .dr-span-2 { grid-column:span 1; }
}

/* Tables */
.dr-table-wrap { background:var(--surface);border:none;border-radius:var(--r);
                 box-shadow:var(--shadow-card);min-width:0; }
.dr-table-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch; }
.dr-table { width:100%;border-collapse:collapse; }
.dr-table thead tr { border-bottom:2px solid var(--border); }
.dr-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;
                     letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);
                     white-space:nowrap; }
.dr-table tbody tr { border-bottom:1px solid var(--border); }
.dr-table tbody tr:last-child { border-bottom:none; }
.dr-table td { padding:13px 16px;font-size:13px;vertical-align:middle; }
.dr-table tbody tr.dr-total-row td { font-weight:700;border-top:2px solid var(--border); }
.dr-table tbody tr.dr-sub-row td { color:var(--text-dim);font-size:12px; }
.dr-table tbody tr.dr-sub-row td:first-child { padding-left:32px; }
.dr-empty       { padding:40px 20px;text-align:center; }
.dr-empty-title { font-size:14px;font-weight:700;color:var(--text-sub);margin-bottom:4px; }
.dr-empty-sub   { font-size:12px;color:var(--text-dim); }

.dr-btn-primary { padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;
                  cursor:pointer;font-family:var(--font);transition:all var(--tr);
                  display:inline-flex;align-items:center;gap:6px;white-space:nowrap;
                  background:var(--accent);color:#fff;border:none;text-decoration:none;
                  box-shadow:0 3px 10px rgba(59,111,212,.25); }
.dr-btn-primary:hover { opacity:.88; }
</style>

@php
    $daysInPeriod   = \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1;
    $avgBoxesPerTxn = $summary['transaction_count'] > 0 ? $summary['total_boxes_sold'] / $summary['transaction_count'] : 0;
    $creditSharePct = $summary['total_sales'] > 0 ? ($summary['total_sales_credit'] / $summary['total_sales']) * 100 : 0;
    $expenseSharePct= $summary['total_sales'] > 0 ? ($summary['total_expenses'] / $summary['total_sales']) * 100 : 0;
    $avgExpensePerDay = $daysInPeriod > 0 ? $summary['total_expenses'] / $daysInPeriod : 0;

    $channels = [
        ['label' => 'Cash',          'amount' => $summary['total_sales_cash']],
        ['label' => 'Mobile Money',  'amount' => $summary['total_sales_momo']],
    ];
    if ($settingAllowCard) {
        $channels[] = ['label' => 'Card', 'amount' => $summary['total_sales_card']];
    }
    if ($settingAllowBankTransfer) {
        $channels[] = ['label' => 'Bank Transfer', 'amount' => $summary['total_sales_bank_transfer']];
    }
    $channels[] = ['label' => 'Credit', 'amount' => $summary['total_sales_credit']];
    if ($summary['total_sales_other'] > 0) {
        $channels[] = ['label' => 'Other', 'amount' => $summary['total_sales_other']];
    }

    // One "<channel> — by Customer" table per payment method — each only
    // renders when it has rows, so a disabled or unused channel simply
    // doesn't appear on the report.
    $paymentByCustomerTables = [
        ['label' => 'Cash — by Customer',          'data' => $summary['cash_by_customer']],
        ['label' => 'Mobile Money — by Customer',  'data' => $summary['momo_by_customer']],
        ['label' => 'Card — by Customer',          'data' => $summary['card_by_customer']],
        ['label' => 'Bank Transfer — by Customer', 'data' => $summary['bank_by_customer']],
    ];
@endphp

<div class="dr-header">
    <div>
        <h1 class="dr-header-title">Daily Report</h1>
        <p class="dr-header-sub">{{ $this->activeDateRangeLabel }} · {{ $summary['transaction_count'] }} {{ Str::plural('transaction', $summary['transaction_count']) }}</p>
    </div>
    <a class="dr-btn-primary" href="{{ route('shop.reports.daily.print', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" target="_blank" rel="noopener">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print Report
    </a>
</div>

{{-- Filters --}}
<div class="dr-filters">
    <div class="dr-presets-row">
        @foreach(['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_quarter' => 'This Quarter', 'this_year' => 'This Year'] as $key => $label)
            <button class="dr-preset-btn {{ $preset === $key ? 'active' : '' }}" wire:click="setPreset('{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>
    <div class="dr-filter-row">
        <div class="dr-filter-seg">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--text-dim)">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <input type="date" wire:model.live="dateFrom" class="dr-date-input">
            <span style="font-size:13px;color:var(--text-dim);flex-shrink:0;">→</span>
            <input type="date" wire:model.live="dateTo" class="dr-date-input">
        </div>
    </div>
</div>

<div class="dr-grid">

{{-- Summary --}}
<div class="dr-table-wrap dr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Summary</div>
    </div>
    <div class="dr-table-scroll">
    <table class="dr-table">
        <thead>
            <tr>
                <th>Metric</th>
                <th style="text-align:right">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Boxes Sold</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700">{{ number_format($summary['total_boxes_sold']) }}</span></td>
            </tr>
            <tr class="dr-sub-row">
                <td>Transactions</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['transaction_count']) }}</td>
            </tr>
            <tr class="dr-sub-row">
                <td>Avg / Sale</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($avgBoxesPerTxn, 1) }}</td>
            </tr>

            <tr>
                <td>Total Sales</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:var(--green)">{{ number_format($summary['total_sales']) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
            <tr class="dr-sub-row">
                <td>Avg / Sale</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['transaction_count'] > 0 ? $summary['total_sales'] / $summary['transaction_count'] : 0) }} RWF</td>
            </tr>

            <tr>
                <td>Total Credits</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:var(--amber)">{{ number_format($summary['total_sales_credit']) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
            <tr class="dr-sub-row">
                <td>% of Sales</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($creditSharePct, 1) }}%</td>
            </tr>
            <tr class="dr-sub-row">
                <td>Non-Credit Sales</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_sales'] - $summary['total_sales_credit']) }} RWF</td>
            </tr>

            <tr>
                <td>Total Expenses</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:var(--red)">{{ number_format($summary['total_expenses']) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
            <tr class="dr-sub-row">
                <td>% of Sales</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($expenseSharePct, 1) }}%</td>
            </tr>
            <tr class="dr-sub-row">
                <td>Avg / Day</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($avgExpensePerDay) }} RWF</td>
            </tr>
            <tr class="dr-sub-row">
                <td>Days in Period</td>
                <td style="text-align:right;font-family:var(--mono)">{{ $daysInPeriod }}</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

{{-- Boxes Sold by Product --}}
<div class="dr-table-wrap dr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Boxes Sold by Product</div>
    </div>
    @if(count($summary['boxes_by_product']) > 0)
    <div class="dr-table-scroll">
    <table class="dr-table">
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align:right">Boxes Sold</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['boxes_by_product'] as $row)
            <tr>
                <td>{{ $row->product_name }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->boxes) }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
            </tr>
            @endforeach
            <tr class="dr-total-row">
                <td>Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_boxes_sold']) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($summary['boxes_by_product'])->sum('amount')) }} RWF</td>
            </tr>
        </tbody>
    </table>
    </div>
    @else
    <div class="dr-empty">
        <div class="dr-empty-title">No boxes sold in this period</div>
        <div class="dr-empty-sub">Try a different date range.</div>
    </div>
    @endif
</div>

{{-- Payment channel distribution --}}
<div class="dr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Distribution by Payment Channel</div>
    </div>
    @if($summary['total_sales'] > 0)
    <div class="dr-table-scroll">
    <table class="dr-table">
        <thead>
            <tr>
                <th>Channel</th>
                <th style="text-align:right">Amount</th>
                <th style="text-align:right">Share</th>
            </tr>
        </thead>
        <tbody>
            @foreach($channels as $channel)
            <tr>
                <td>{{ $channel['label'] }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono)">{{ number_format($channel['amount']) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
                <td style="text-align:right;font-family:var(--mono);color:var(--text-dim)">
                    {{ number_format($summary['total_sales'] > 0 ? ($channel['amount'] / $summary['total_sales']) * 100 : 0, 1) }}%
                </td>
            </tr>
            @endforeach
            <tr class="dr-total-row">
                <td>Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_sales']) }} RWF</td>
                <td style="text-align:right;font-family:var(--mono)">100.0%</td>
            </tr>
        </tbody>
    </table>
    </div>
    @else
    <div class="dr-empty">
        <div class="dr-empty-title">No sales in this period</div>
        <div class="dr-empty-sub">Try a different date range.</div>
    </div>
    @endif
</div>

{{-- Payment method breakdowns — one per channel, only when it has data --}}
@foreach($paymentByCustomerTables as $table)
    @if(count($table['data']) > 0)
    <div class="dr-table-wrap">
        <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
            <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $table['label'] }}</div>
        </div>
        <div class="dr-table-scroll">
        <table class="dr-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th style="text-align:right">Sales</th>
                    <th style="text-align:right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($table['data'] as $row)
                <tr>
                    <td>{{ $row->customer_name }}</td>
                    <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->sales_count) }}</td>
                    <td style="text-align:right;white-space:nowrap">
                        <span style="font-family:var(--mono)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                    </td>
                </tr>
                @endforeach
                <tr class="dr-total-row">
                    <td>Total</td>
                    <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($table['data'])->sum('sales_count')) }}</td>
                    <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($table['data'])->sum('amount')) }} RWF</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
    @endif
@endforeach

{{-- Detailed Credits (Amadeni — new credit issued) --}}
@if(count($summary['credits_by_customer']) > 0)
<div class="dr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Detailed Credits — by Customer</div>
    </div>
    <div class="dr-table-scroll">
    <table class="dr-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th style="text-align:right">Sales</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['credits_by_customer'] as $row)
            <tr>
                <td>{{ $row->customer_name }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->sales_count) }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono);color:var(--amber)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
            </tr>
            @endforeach
            <tr class="dr-total-row">
                <td>Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($summary['credits_by_customer'])->sum('sales_count')) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_sales_credit']) }} RWF</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Credit Repayments (ABISHYUVE — repayments received) --}}
@if(count($summary['repayments_by_customer']) > 0)
<div class="dr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Credit Repayments — by Customer</div>
    </div>
    <div class="dr-table-scroll">
    <table class="dr-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th style="text-align:right">Repayments</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['repayments_by_customer'] as $row)
            <tr>
                <td>{{ $row->customer_name }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->repayment_count) }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono);color:var(--green)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
            </tr>
            @endforeach
            <tr class="dr-total-row">
                <td>Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($summary['repayments_by_customer'])->sum('repayment_count')) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_repayments']) }} RWF</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Detailed Expenses --}}
<div class="dr-table-wrap dr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Detailed Expenses</div>
    </div>
    @if(count($summary['expenses_detailed']) > 0)
    <div class="dr-table-scroll">
    <table class="dr-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['expenses_detailed'] as $row)
            <tr>
                <td style="color:var(--text-dim)">{{ \Carbon\Carbon::parse($row->session_date)->format('d M Y') }}</td>
                <td>{{ $row->category }}</td>
                <td style="color:var(--text-dim)">{{ $row->description ?: '—' }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono);color:var(--red)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
            </tr>
            @endforeach
            <tr class="dr-total-row">
                <td colspan="3">Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_expenses']) }} RWF</td>
            </tr>
        </tbody>
    </table>
    </div>
    @else
    <div class="dr-empty">
        <div class="dr-empty-title">No expenses in this period</div>
        <div class="dr-empty-sub">Try a different date range.</div>
    </div>
    @endif
</div>

</div>
</div>
