<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daily Report — {{ $shopName }}</title>
<style>
:root {
    --ink:#161a2e; --dim:#6b7280; --line:#e3e6f0; --line-soft:#eef0f6;
    --surface-2:#f7f8fc; --navy:#1f2740;
    --accent:#3b6fd4; --accent-soft:#eaf0fc;
    --green:#0e9e86;  --green-soft:#e7f7f4;
    --amber:#d97706;  --amber-soft:#fdf1e2;
    --red:#e11d48;    --red-soft:#fdeaf0;
}

* { box-sizing:border-box; margin:0; padding:0; }

body {
    font-family:'Segoe UI', Arial, sans-serif;
    font-size:12.5px;
    color:var(--ink);
    background:#fff;
    -webkit-font-smoothing:antialiased;
}

.page { max-width:840px; margin:0 auto; padding:28px 34px 34px; }

.top-bar { height:5px; border-radius:3px; margin-bottom:22px;
           background:linear-gradient(90deg, var(--accent), var(--green)); }

/* Header */
.doc-header { display:flex; align-items:center; justify-content:space-between; gap:16px;
              padding-bottom:18px; border-bottom:2px solid var(--line); margin-bottom:18px; }
.brand-block { display:flex; align-items:center; gap:12px; }
.brand-badge { width:42px; height:42px; border-radius:10px; background:var(--accent); color:#fff;
               display:flex; align-items:center; justify-content:center; font-size:19px; font-weight:800;
               flex-shrink:0; }
.brand-name { font-size:18px; font-weight:800; letter-spacing:-.3px; color:var(--ink); }
.brand-sub  { font-size:11.5px; color:var(--dim); margin-top:1px; }
.doc-badge  { text-align:right; }
.doc-badge .tag { display:inline-block; background:var(--accent-soft); color:var(--accent);
                  font-size:11px; font-weight:700; letter-spacing:.6px; text-transform:uppercase;
                  padding:5px 13px; border-radius:20px; }
.doc-badge .period { font-size:13px; font-weight:700; color:var(--ink); margin-top:7px; }

/* Meta strip */
.meta-strip { display:flex; flex-wrap:wrap; background:var(--surface-2); border:1px solid var(--line);
              border-radius:8px; margin-bottom:20px; overflow:hidden; }
.meta-cell  { flex:1; min-width:120px; padding:10px 16px; border-right:1px solid var(--line); }
.meta-cell:last-child { border-right:none; }
.meta-label { font-size:9.5px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--dim); }
.meta-value { font-size:13px; font-weight:700; color:var(--ink); margin-top:3px; }
.meta-value .pill { display:inline-block; padding:2px 10px; border-radius:10px; font-size:11px;
                     font-weight:700; background:var(--accent-soft); color:var(--accent); }

/* Section grid — pairs shorter tables side by side to cut down on blank
   space; wide/itemized tables span both columns. */
.grid   { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:16px 18px; grid-auto-flow:dense; }
.span-2 { grid-column:span 2; }

.cell      { border:1px solid var(--line); border-radius:8px; overflow:hidden; break-inside:avoid; }
.cell-head { display:flex; align-items:center; gap:8px; padding:10px 14px; border-bottom:1px solid var(--line);
             background:var(--surface-2); }
.cell-head .dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.dot.c-accent { background:var(--accent); }
.dot.c-green  { background:var(--green); }
.dot.c-amber  { background:var(--amber); }
.dot.c-red    { background:var(--red); }
.cell-title   { font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:var(--ink); }
.cell table   { margin:0; }
.cell p.empty-state { margin:0; padding:22px 14px; text-align:center; color:var(--dim); font-size:12px; }

table { width:100%; border-collapse:collapse; font-size:12px; }
thead th { background:var(--navy); color:#fff; padding:8px 14px; text-align:left;
           font-size:10.5px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; }
thead th:last-child, tbody td:last-child, tfoot td:last-child { text-align:right; }
tbody tr:nth-child(even) { background:var(--surface-2); }
tbody tr:not(:last-child) td { border-bottom:1px solid var(--line-soft); }
tbody td { padding:8px 14px; font-variant-numeric:tabular-nums; }
tfoot td { padding:9px 14px; font-weight:700; border-top:2px solid var(--line); background:var(--surface-2);
           font-variant-numeric:tabular-nums; }

.callout { margin:10px 14px 12px; padding:8px 11px; border-radius:6px; font-size:10.5px;
           border-left:3px solid var(--amber); background:var(--amber-soft); color:#8a5a12; }

.doc-footer { margin-top:22px; padding-top:14px; border-top:1px solid var(--line);
              text-align:center; font-size:11px; color:var(--dim); }

.print-btn { display:block; margin:22px auto 0; padding:10px 30px; background:var(--accent); color:#fff;
             border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; letter-spacing:.2px; }

@media print {
    body { padding:0; }
    .page { padding:16px 20px 20px; }
    .no-print { display:none !important; }
    .top-bar, thead th, .cell-head, tfoot td, .callout, tbody tr:nth-child(even) {
        -webkit-print-color-adjust:exact; print-color-adjust:exact;
    }
    .cell { break-inside:avoid; }
}

@media(max-width:640px) {
    .grid { grid-template-columns:1fr; }
    .span-2 { grid-column:span 1; }
    .meta-strip { flex-direction:column; }
    .meta-cell { border-right:none; border-bottom:1px solid var(--line); }
    .meta-cell:last-child { border-bottom:none; }
    table { display:block; overflow-x:auto; -webkit-overflow-scrolling:touch; }
}
</style>
</head>
<body>
@php
    $from = \Carbon\Carbon::parse($dateFrom);
    $to   = \Carbon\Carbon::parse($dateTo);
    $period = $from->isSameDay($to) ? $from->format('d M Y') : $from->format('d M Y') . ' – ' . $to->format('d M Y');
    $isSingleDay = $dateFrom === $dateTo;

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

    $varianceColor = fn ($v) => $v < 0 ? 'var(--red)' : ($v > 0 ? 'var(--amber)' : 'var(--green)');
@endphp

<div class="page">

    <div class="top-bar"></div>

    <div class="doc-header">
        <div class="brand-block">
            <div class="brand-badge">{{ Str::upper(Str::substr(config('tenant.name'), 0, 1)) }}</div>
            <div>
                <div class="brand-name">{{ config('tenant.name') }}</div>
                <div class="brand-sub">{{ $shopName }}</div>
            </div>
        </div>
        <div class="doc-badge">
            <span class="tag">Daily Report</span>
            <div class="period">{{ $period }}</div>
        </div>
    </div>

    <div class="meta-strip">
        <div class="meta-cell">
            <div class="meta-label">Shop</div>
            <div class="meta-value">{{ $shopName }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Period</div>
            <div class="meta-value">{{ $period }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Transactions</div>
            <div class="meta-value">{{ number_format($summary['transaction_count']) }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">View</div>
            <div class="meta-value"><span class="pill">{{ $viewMode === 'transactions' ? 'Transactions' : 'Summary' }}</span></div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Printed</div>
            <div class="meta-value">{{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>

    {{-- Headline metrics — always shown so the report's core message comes
         across regardless of which detail view was printed. --}}
    <div class="cell" style="margin-bottom:22px">
        <div class="cell-head"><span class="dot c-accent"></span><span class="cell-title">Summary</span></div>
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
                    <td style="font-weight:800">{{ number_format($summary['total_boxes_sold']) }}</td>
                </tr>
                <tr>
                    <td>Total Sales</td>
                    <td style="font-weight:800;color:var(--green)">{{ number_format($summary['total_sales']) }} RWF</td>
                </tr>
                <tr>
                    <td>Total Credits</td>
                    <td style="font-weight:800;color:var(--amber)">{{ number_format($summary['total_sales_credit']) }} RWF</td>
                </tr>
                <tr>
                    <td>Total Expenses</td>
                    <td style="font-weight:800;color:var(--red)">{{ number_format($summary['total_expenses']) }} RWF</td>
                </tr>
                <tr>
                    <td>Net for Period</td>
                    <td style="font-weight:800;color:{{ $summary['net_for_period'] >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $summary['net_for_period'] < 0 ? '−' : '' }}{{ number_format(abs($summary['net_for_period'])) }} RWF</td>
                </tr>
                <tr>
                    <td>
                        Credit Repayments Received
                        <div style="font-size:10px;font-weight:400;color:var(--dim);margin-top:2px">Cash collected on prior credit — not new revenue, not part of Net for Period above</div>
                    </td>
                    <td style="font-weight:800;color:var(--green)">{{ number_format($summary['total_repayments']) }} RWF</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Business Position (Cash on Hand / Outstanding Receivables) — hidden
         per request; the section and its data are still computed above,
         just not rendered. Re-enable by uncommenting this block.
    <div class="cell" style="margin-bottom:22px">
        <div class="cell-head"><span class="dot c-green"></span><span class="cell-title">Business Position — As of Today</span></div>
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
                    <td style="font-weight:800;color:var(--green)">{{ number_format($position['cash']) }} RWF</td>
                </tr>
                <tr>
                    <td>Outstanding Receivables — Assumed Held</td>
                    <td style="font-weight:800;color:var(--amber)">{{ number_format($summary['outstanding_receivables']) }} RWF</td>
                </tr>
            </tbody>
        </table>
    </div>
    --}}

    <div class="grid">

    @if($viewMode === 'summary')
    <div class="cell">
        <div class="cell-head"><span class="dot c-accent"></span><span class="cell-title">Boxes Sold by Product</span></div>
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
        <p class="empty-state">No boxes sold in this period.</p>
        @endif
    </div>
    @endif

    @if($viewMode === 'transactions')
    <div class="cell span-2">
        <div class="cell-head"><span class="dot c-accent"></span><span class="cell-title">All Sales</span></div>
        @if(count($summary['all_sales']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Sale #</th>
                    @if($isAllShops)<th>Shop</th>@endif
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
                    @if($isAllShops)<td>{{ $row->shop_name }}</td>@endif
                    <td>{{ \Carbon\Carbon::parse($row->sale_date)->format('d M Y') }}</td>
                    <td>{{ $row->customer_name ?? '—' }}</td>
                    <td style="text-align:right">{{ number_format($row->boxes) }}</td>
                    <td style="text-align:right">{{ number_format($row->total) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $isAllShops ? 4 : 3 }}">Grand Total</td>
                    <td style="text-align:right">{{ number_format(collect($summary['all_sales'])->sum('boxes')) }}</td>
                    <td style="text-align:right">{{ number_format(collect($summary['all_sales'])->sum('total')) }}</td>
                </tr>
            </tfoot>
        </table>
        @else
        <p class="empty-state">No sales in this period.</p>
        @endif
    </div>
    @endif

    @if($viewMode === 'summary')
    <div class="cell {{ ($isAllShops || !$isSingleDay) ? 'span-2' : '' }}">
        <div class="cell-head"><span class="dot c-green"></span><span class="cell-title">Cash Register</span></div>
        @if($cashRegister->isEmpty())
        <p class="empty-state">No cash session opened {{ $isSingleDay ? 'on this date' : 'in this period' }}.</p>
        @elseif($isAllShops)
        <table>
            <thead>
                <tr>
                    <th>Shop</th>
                    <th>Opening</th>
                    <th>Closing</th>
                    <th>Variance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashRegister as $row)
                <tr>
                    <td>{{ $row->shop_name }}</td>
                    <td style="text-align:right">{{ number_format($row->opening) }}</td>
                    <td style="text-align:right">{{ number_format($row->closing) }}{{ $row->is_open ? ' *' : '' }}</td>
                    <td style="text-align:right;color:{{ $row->is_open ? 'var(--dim)' : $varianceColor($row->variance) }}">{{ $row->is_open ? '—' : (($row->variance > 0 ? '+' : '') . number_format($row->variance)) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total Variance</td>
                    <td style="text-align:right">{{ number_format($cashRegister->sum('variance')) }}</td>
                </tr>
            </tfoot>
        </table>
        @if($cashRegister->contains('is_open', true))
        <p class="callout">* still open — closing shown is a live figure, not a final count. Opening is that shop's first session in range; closing is its last.</p>
        @endif
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
                    <td style="text-align:right;color:{{ $varianceColor($day->variance) }}">{{ $day->variance > 0 ? '+' : '' }}{{ number_format($day->variance) }} RWF</td>
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
                    <td style="text-align:right;color:{{ $day->is_open ? 'var(--dim)' : $varianceColor($day->variance) }}">@if($day->is_open) — @else {{ $day->variance > 0 ? '+' : '' }}{{ number_format($day->variance) }} @endif</td>
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
        <p class="callout">* still open — closing shown is a live figure, not a final count</p>
        @endif
        @endif
    </div>

    <div class="cell">
        <div class="cell-head"><span class="dot c-accent"></span><span class="cell-title">Distribution by Payment Channel</span></div>
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
            <div class="cell-head"><span class="dot c-accent"></span><span class="cell-title">{{ $table['label'] }}</span></div>
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
        <div class="cell-head"><span class="dot c-amber"></span><span class="cell-title">Detailed Credits — by Customer</span></div>
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
        <div class="cell-head"><span class="dot c-green"></span><span class="cell-title">Credit Repayments — by Customer</span></div>
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
        <div class="cell-head"><span class="dot c-red"></span><span class="cell-title">Detailed Expenses</span></div>
        @if(count($summary['expenses_detailed']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    @if($isAllShops)<th>Shop</th>@endif
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount (RWF)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary['expenses_detailed'] as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->session_date)->format('d M Y') }}</td>
                    @if($isAllShops)<td>{{ $row->shop_name }}</td>@endif
                    <td>{{ $row->category }}</td>
                    <td>{{ $row->description ?: '—' }}</td>
                    <td style="text-align:right">{{ number_format($row->amount) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $isAllShops ? 4 : 3 }}">Total</td>
                    <td style="text-align:right">{{ number_format($summary['total_expenses']) }}</td>
                </tr>
            </tfoot>
        </table>
        @else
        <p class="empty-state">No expenses in this period.</p>
        @endif
    </div>
    @endif

    </div>{{-- /.grid --}}

    <div class="doc-footer">
        {{ config('tenant.name') }} — {{ $shopName }} · Generated {{ now()->format('d M Y H:i') }}
    </div>

    <div class="no-print" style="text-align:center;margin-top:24px">
        <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
        <div style="margin-top:10px;font-size:13px;color:var(--dim)">Use your browser's Print function to save as PDF</div>
    </div>

</div>
</body>
</html>
