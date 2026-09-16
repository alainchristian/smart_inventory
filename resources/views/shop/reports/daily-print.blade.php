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
tbody tr.sub-row td { font-size:12px;font-weight:normal; }
tbody tr.sub-row td:first-child { padding-left:28px; }

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
}

@media(max-width:640px) {
    table { display:block; overflow-x:auto; -webkit-overflow-scrolling:touch; }
}
</style>
</head>
<body>
@php
    $from = \Carbon\Carbon::parse($dateFrom);
    $to   = \Carbon\Carbon::parse($dateTo);
    $period = $from->isSameDay($to) ? $from->format('d M Y') : $from->format('d M Y') . ' – ' . $to->format('d M Y');

    $daysInPeriod     = $from->diffInDays($to) + 1;
    $avgBoxesPerTxn   = $summary['transaction_count'] > 0 ? $summary['total_boxes_sold'] / $summary['transaction_count'] : 0;
    $creditSharePct   = $summary['total_sales'] > 0 ? ($summary['total_sales_credit'] / $summary['total_sales']) * 100 : 0;
    $expenseSharePct  = $summary['total_sales'] > 0 ? ($summary['total_expenses'] / $summary['total_sales']) * 100 : 0;
    $avgExpensePerDay = $daysInPeriod > 0 ? $summary['total_expenses'] / $daysInPeriod : 0;

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
    </div>

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
            <tr class="sub-row">
                <td>Transactions</td>
                <td style="text-align:right">{{ number_format($summary['transaction_count']) }}</td>
            </tr>
            <tr class="sub-row">
                <td>Avg / Sale</td>
                <td style="text-align:right">{{ number_format($avgBoxesPerTxn, 1) }}</td>
            </tr>

            <tr>
                <td>Total Sales</td>
                <td style="text-align:right">{{ number_format($summary['total_sales']) }} RWF</td>
            </tr>
            <tr class="sub-row">
                <td>Avg / Sale</td>
                <td style="text-align:right">{{ number_format($summary['transaction_count'] > 0 ? $summary['total_sales'] / $summary['transaction_count'] : 0) }} RWF</td>
            </tr>

            <tr>
                <td>Total Credits</td>
                <td style="text-align:right">{{ number_format($summary['total_sales_credit']) }} RWF</td>
            </tr>
            <tr class="sub-row">
                <td>% of Sales</td>
                <td style="text-align:right">{{ number_format($creditSharePct, 1) }}%</td>
            </tr>
            <tr class="sub-row">
                <td>Non-Credit Sales</td>
                <td style="text-align:right">{{ number_format($summary['total_sales'] - $summary['total_sales_credit']) }} RWF</td>
            </tr>

            <tr>
                <td>Total Expenses</td>
                <td style="text-align:right">{{ number_format($summary['total_expenses']) }} RWF</td>
            </tr>
            <tr class="sub-row">
                <td>% of Sales</td>
                <td style="text-align:right">{{ number_format($expenseSharePct, 1) }}%</td>
            </tr>
            <tr class="sub-row">
                <td>Avg / Day</td>
                <td style="text-align:right">{{ number_format($avgExpensePerDay) }} RWF</td>
            </tr>
            <tr class="sub-row">
                <td>Days in Period</td>
                <td style="text-align:right">{{ $daysInPeriod }}</td>
            </tr>
        </tbody>
    </table>

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
    <p style="margin-bottom:24px;color:#000">No boxes sold in this period.</p>
    @endif

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

    @foreach($paymentByCustomerTables as $table)
        @if(count($table['data']) > 0)
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
        @endif
    @endforeach

    @if(count($summary['credits_by_customer']) > 0)
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
    @endif

    @if(count($summary['repayments_by_customer']) > 0)
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
    @endif

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
    <p style="margin-bottom:24px;color:#000">No expenses in this period.</p>
    @endif

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
