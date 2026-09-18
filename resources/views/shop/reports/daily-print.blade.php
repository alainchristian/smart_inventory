<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daily Report — {{ $shop->name ?? 'Shop' }}</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }

body {
    font-family:'Segoe UI', Arial, sans-serif;
    font-size:13px;
    color:#000;
    background:#fff;
}

.page { max-width:800px; margin:0 auto; padding:32px 36px; }

.doc-header {
    display:flex; justify-content:space-between; align-items:flex-start;
    border-bottom:3px solid #000; padding-bottom:18px; margin-bottom:20px;
}
.doc-header .brand { font-size:24px; font-weight:800; letter-spacing:-.5px; }
.doc-header .sub   { font-size:13px; color:#000; margin-top:4px; }
.doc-header .doc-type { text-align:right; }
.doc-header .doc-type .title  { font-size:20px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
.doc-header .doc-type .period { font-size:14px; color:#000; margin-top:3px; }

.meta-row {
    display:flex; gap:24px; flex-wrap:wrap; padding:12px 16px;
    border:1px solid #000; border-radius:6px; margin-bottom:24px;
}
.meta-item { }
.meta-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#000; }
.meta-value { font-size:15px; font-weight:700; color:#000; margin-top:2px; }

.section-heading {
    font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.8px;
    color:#000; border-bottom:1px solid #000; padding-bottom:6px; margin-bottom:10px;
}

/* Grid — pairs shorter tables side by side to cut down on blank space,
   same idea as the on-screen report. Wide/itemized tables span both
   columns. */
.grid   { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:18px 24px; margin-bottom:20px; grid-auto-flow:dense; }
.span-2 { grid-column:span 2; }
.cell table, .cell p { margin-bottom:0; }

table { width:100%; border-collapse:collapse; font-size:13px; margin-bottom:24px; }
thead th {
    background:#000; color:#fff; padding:9px 12px; text-align:left;
    font-size:12px; font-weight:700; letter-spacing:.4px;
}
thead th:last-child, tbody td:last-child { text-align:right; }
tbody tr:nth-child(even) { background:#f4f4f4; }
tbody td { padding:8px 12px; border-bottom:1px solid #000; }
tfoot td { padding:9px 12px; font-weight:700; border-top:2px solid #000; }
tfoot td:last-child { text-align:right; }

.doc-footer {
    margin-top:28px; padding-top:12px; border-top:1px solid #000;
    text-align:center; font-size:12px; color:#000;
}

.print-btn {
    display:block; margin:20px auto 0; padding:10px 28px; background:#000; color:#fff;
    border:none; border-radius:6px; font-size:14px; font-weight:600; cursor:pointer; letter-spacing:.3px;
}

@media print {
    body { padding:0; }
    .page { padding:18px 22px; }
    .no-print { display:none !important; }
    thead th { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .cell { break-inside:avoid; }
}

@media(max-width:640px) {
    .grid { grid-template-columns:1fr; }
    .span-2 { grid-column:span 1; }
    table { display:block; overflow-x:auto; -webkit-overflow-scrolling:touch; }
}
</style>
</head>
<body>
@php
    $from = \Carbon\Carbon::parse($dateFrom);
    $to   = \Carbon\Carbon::parse($dateTo);
    $period = $from->isSameDay($to) ? $from->format('d M Y') : $from->format('d M Y') . ' – ' . $to->format('d M Y');

    $channels = [
        ['label' => 'Cash',         'amount' => $summary['total_sales_cash']],
        ['label' => 'Mobile Money', 'amount' => $summary['total_sales_momo']],
        ['label' => 'Card',          'amount' => $summary['total_sales_card']],
        ['label' => 'Bank Transfer', 'amount' => $summary['total_sales_bank_transfer']],
        ['label' => 'Credit',        'amount' => $summary['total_sales_credit']],
        ['label' => 'Other',         'amount' => $summary['total_sales_other']],
    ];

    $paymentByCustomerTables = [
        ['label' => 'Cash — by Customer',          'data' => $summary['cash_by_customer']],
        ['label' => 'Mobile Money — by Customer',  'data' => $summary['momo_by_customer']],
        ['label' => 'Card — by Customer',          'data' => $summary['card_by_customer']],
        ['label' => 'Bank Transfer — by Customer', 'data' => $summary['bank_by_customer']],
    ];
@endphp

<div class="page">

    <div class="doc-header">
        <div>
            <div class="brand">{{ config('tenant.name') }}</div>
            <div class="sub">{{ $shop->name ?? '' }}</div>
        </div>
        <div class="doc-type">
            <div class="title">Daily Report</div>
            <div class="period">{{ $period }}</div>
        </div>
    </div>

    <div class="meta-row">
        <div class="meta-item">
            <div class="meta-label">Shop</div>
            <div class="meta-value">{{ $shop->name ?? '—' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Period</div>
            <div class="meta-value">{{ $period }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Transactions</div>
            <div class="meta-value">{{ number_format($summary['transaction_count']) }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Printed</div>
            <div class="meta-value">{{ now()->format('d M Y, H:i') }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">View</div>
            <div class="meta-value">{{ $viewMode === 'transactions' ? 'Transactions' : 'Summary' }}</div>
        </div>
    </div>

    @php $isSingleDay = $dateFrom === $dateTo; @endphp
    <div class="grid">

    {{-- Business Position (Cash on Hand / Outstanding Receivables) — hidden
         per request; $position is still computed and passed to this view,
         just not rendered. Re-enable by uncommenting this block.
    <div class="cell span-2">
        <div class="section-heading">Business Position — As of Today</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cash on Hand — Owned Now</td>
                    <td style="text-align:right">{{ number_format($position['cash']) }} RWF</td>
                </tr>
                <tr>
                    <td>Outstanding Receivables — Assumed Held</td>
                    <td style="text-align:right">{{ number_format($summary['outstanding_receivables']) }} RWF</td>
                </tr>
            </tbody>
        </table>
    </div>
    --}}

    @if($viewMode === 'summary')
    <div class="cell">
        <div class="section-heading">Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Boxes Sold</td>
                    <td style="text-align:right">{{ number_format($summary['total_boxes_sold']) }}</td>
                </tr>
                <tr>
                    <td>Total Sales</td>
                    <td style="text-align:right">{{ number_format($summary['total_sales']) }} RWF</td>
                </tr>
                <tr>
                    <td>Total Credits</td>
                    <td style="text-align:right">{{ number_format($summary['total_sales_credit']) }} RWF</td>
                </tr>
                <tr>
                    <td>Total Expenses</td>
                    <td style="text-align:right">{{ number_format($summary['total_expenses']) }} RWF</td>
                </tr>
                <tr style="font-weight:700">
                    <td>Net for Period</td>
                    <td style="text-align:right">{{ $summary['net_for_period'] < 0 ? '−' : '' }}{{ number_format(abs($summary['net_for_period'])) }} RWF</td>
                </tr>
                <tr>
                    <td>
                        Credit Repayments Received
                        <div style="font-size:10px;font-weight:400;margin-top:2px">Cash collected on prior credit — not new revenue, not part of Net for Period above</div>
                    </td>
                    <td style="text-align:right">{{ number_format($summary['total_repayments']) }} RWF</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    @if($viewMode === 'transactions')
    <div class="cell span-2">
        <div class="section-heading">All Sales</div>
        @if(count($summary['all_sales']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Sale #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Boxes</th>
                    <th>Amount (RWF)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary['all_sales'] as $row)
                <tr>
                    <td>{{ $row->sale_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->sale_date)->format('d M Y') }}</td>
                    <td>{{ $row->customer_name ?? '—' }}</td>
                    <td style="text-align:right">{{ number_format($row->boxes) }}</td>
                    <td style="text-align:right">{{ number_format($row->total) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Grand Total</td>
                    <td style="text-align:right">{{ number_format(collect($summary['all_sales'])->sum('boxes')) }}</td>
                    <td style="text-align:right">{{ number_format(collect($summary['all_sales'])->sum('total')) }}</td>
                </tr>
            </tfoot>
        </table>
        @else
        <p>No sales in this period.</p>
        @endif
    </div>
    @endif

    @if($viewMode === 'summary')
    <div class="cell {{ !$isSingleDay ? 'span-2' : '' }}">
        <div class="section-heading">Cash Register</div>
        @if($cashRegister->isEmpty())
        <p>No cash session opened {{ $isSingleDay ? 'on this date' : 'in this period' }}.</p>
        @elseif($isSingleDay)
        @php $day = $cashRegister->first(); @endphp
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Opening Balance</td>
                    <td style="text-align:right">{{ number_format($day->opening) }} RWF</td>
                </tr>
                <tr>
                    <td>Closing Balance{{ $day->is_open ? ' (as of now)' : '' }}</td>
                    <td style="text-align:right">{{ number_format($day->closing) }} RWF</td>
                </tr>
                @unless($day->is_open)
                <tr>
                    <td>Variance</td>
                    <td style="text-align:right">{{ $day->variance > 0 ? '+' : '' }}{{ number_format($day->variance) }} RWF</td>
                </tr>
                @endunless
            </tbody>
        </table>
        @else
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Opening</th>
                    <th>Closing</th>
                    <th>Variance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashRegister as $day)
                <tr>
                    <td>{{ $day->date->format('d M Y') }}</td>
                    <td style="text-align:right">{{ number_format($day->opening) }}</td>
                    <td style="text-align:right">{{ number_format($day->closing) }}{{ $day->is_open ? ' *' : '' }}</td>
                    <td style="text-align:right">@if($day->is_open) — @else {{ $day->variance > 0 ? '+' : '' }}{{ number_format($day->variance) }} @endif</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total Variance</td>
                    <td style="text-align:right">{{ number_format($cashRegister->whereNotNull('variance')->sum('variance')) }}</td>
                </tr>
            </tfoot>
        </table>
        @if($cashRegister->contains('is_open', true))
        <p style="font-size:11px;margin-top:6px">* still open — closing shown is a live figure, not a final count</p>
        @endif
        @endif
    </div>

    <div class="cell">
        <div class="section-heading">Boxes Sold by Product</div>
        @if(count($summary['boxes_by_product']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Boxes Sold</th>
                    <th>Amount (RWF)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary['boxes_by_product'] as $row)
                <tr>
                    <td>{{ $row->product_name }}</td>
                    <td style="text-align:right">{{ number_format($row->boxes) }}</td>
                    <td style="text-align:right">{{ number_format($row->amount) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td style="text-align:right">{{ number_format($summary['total_boxes_sold']) }}</td>
                    <td style="text-align:right">{{ number_format(collect($summary['boxes_by_product'])->sum('amount')) }}</td>
                </tr>
            </tfoot>
        </table>
        @else
        <p>No boxes sold in this period.</p>
        @endif
    </div>

    <div class="cell">
        <div class="section-heading">Distribution by Payment Channel</div>
        <table>
            <thead>
                <tr>
                    <th>Channel</th>
                    <th>Amount (RWF)</th>
                    <th>Share</th>
                </tr>
            </thead>
            <tbody>
                @foreach($channels as $channel)
                    @if($channel['amount'] > 0)
                    <tr>
                        <td>{{ $channel['label'] }}</td>
                        <td style="text-align:right">{{ number_format($channel['amount']) }}</td>
                        <td style="text-align:right">{{ number_format($summary['total_sales'] > 0 ? ($channel['amount'] / $summary['total_sales']) * 100 : 0, 1) }}%</td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td style="text-align:right">{{ number_format($summary['total_sales']) }}</td>
                    <td style="text-align:right">100.0%</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    @if($viewMode === 'transactions')
    @foreach($paymentByCustomerTables as $table)
        @if(count($table['data']) > 0)
        <div class="cell">
            <div class="section-heading">{{ $table['label'] }}</div>
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Sales</th>
                        <th>Amount (RWF)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($table['data'] as $row)
                    <tr>
                        <td>{{ $row->customer_name }}</td>
                        <td style="text-align:right">{{ number_format($row->sales_count) }}</td>
                        <td style="text-align:right">{{ number_format($row->amount) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td style="text-align:right">{{ number_format(collect($table['data'])->sum('sales_count')) }}</td>
                        <td style="text-align:right">{{ number_format(collect($table['data'])->sum('amount')) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    @endforeach

    @if(count($summary['credits_by_customer']) > 0)
    <div class="cell">
        <div class="section-heading">Detailed Credits — by Customer</div>
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Sales</th>
                    <th>Amount (RWF)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary['credits_by_customer'] as $row)
                <tr>
                    <td>{{ $row->customer_name }}</td>
                    <td style="text-align:right">{{ number_format($row->sales_count) }}</td>
                    <td style="text-align:right">{{ number_format($row->amount) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td style="text-align:right">{{ number_format(collect($summary['credits_by_customer'])->sum('sales_count')) }}</td>
                    <td style="text-align:right">{{ number_format($summary['total_sales_credit']) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    @if(count($summary['repayments_by_customer']) > 0)
    <div class="cell">
        <div class="section-heading">Credit Repayments — by Customer</div>
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Repayments</th>
                    <th>Amount (RWF)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary['repayments_by_customer'] as $row)
                <tr>
                    <td>{{ $row->customer_name }}</td>
                    <td style="text-align:right">{{ number_format($row->repayment_count) }}</td>
                    <td style="text-align:right">{{ number_format($row->amount) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td style="text-align:right">{{ number_format(collect($summary['repayments_by_customer'])->sum('repayment_count')) }}</td>
                    <td style="text-align:right">{{ number_format($summary['total_repayments']) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <div class="cell span-2">
        <div class="section-heading">Detailed Expenses</div>
        @if(count($summary['expenses_detailed']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount (RWF)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary['expenses_detailed'] as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->session_date)->format('d M Y') }}</td>
                    <td>{{ $row->category }}</td>
                    <td>{{ $row->description ?: '—' }}</td>
                    <td style="text-align:right">{{ number_format($row->amount) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total</td>
                    <td style="text-align:right">{{ number_format($summary['total_expenses']) }}</td>
                </tr>
            </tfoot>
        </table>
        @else
        <p>No expenses in this period.</p>
        @endif
    </div>
    @endif

    </div>{{-- /.grid --}}

    <div class="doc-footer">
        {{ config('tenant.name') }} — {{ $shop->name ?? '' }} · Generated {{ now()->format('d M Y H:i') }}
    </div>

    <div class="no-print" style="text-align:center;margin-top:24px">
        <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
        <div style="margin-top:10px;font-size:13px;color:#000">Use your browser's Print function to save as PDF</div>
    </div>

</div>
</body>
</html>
