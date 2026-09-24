{{-- Daily Report PDF (DAILY_REPORT_EXT_03) — shared by owner + shop controllers.
     dompdf = CSS 2.1: layout is tables only (no flex/grid/JS). Colours use the
     design tokens via var(): dompdf 3.1.4 resolves :root custom properties
     (verified — see CLAUDE.md, P1). Token values = light theme in resources/css/app.css. --}}
@php
    $isAll      = (bool) ($isAllShops ?? false);
    $shopLabel  = $isAll ? 'All shops' : ($shopName ?? ($shop->name ?? ''));
    $tenant     = config('tenant.name');
    $tz         = config('tenant.timezone');
    $mode       = $viewMode === 'transactions' ? 'Transactions' : 'Summary';

    $from = \Carbon\Carbon::parse($dateFrom);
    $to   = \Carbon\Carbon::parse($dateTo);
    if ($from->isSameDay($to)) {
        $period = $from->format('l j F Y');
    } elseif ($from->format('Y-m') === $to->format('Y-m')) {
        $period = $from->format('j') . ' – ' . $to->format('j F Y');
    } elseif ($from->year === $to->year) {
        $period = $from->format('j M') . ' – ' . $to->format('j M Y');
    } else {
        $period = $from->format('j M Y') . ' – ' . $to->format('j M Y');
    }
    $isSingleDay = $from->isSameDay($to);
    $generated   = now($tz)->format('j M Y, H:i');

    // Amount helpers — no decimals, U+2212 for negatives, zero shown as "0".
    $n   = fn ($v) => (($v ?? 0) < 0 ? '−' : '') . number_format(abs((int) ($v ?? 0)));
    $sg  = fn ($v) => (($v ?? 0) > 0 ? '+' : '') . $n($v);
    $vcol = fn ($v) => $v === null ? 'var(--text-dim)' : ($v < 0 ? 'var(--red)' : ($v > 0 ? 'var(--amber)' : 'var(--green)'));

    $cmpLabel = $comparison['label'] ?? '';
    $delta = function (?string $key, int $now, bool $inverse = false) use ($comparison, $cmpLabel, $n): ?array {
        if ($key === null) {
            return null;
        }
        $prev = (int) ($comparison[$key] ?? 0);
        if ($prev === 0) {
            return ['no data for ' . $cmpLabel, 'var(--text-dim)'];
        }
        $d    = $now - $prev;
        $good = $inverse ? $d < 0 : $d > 0;

        return [($d > 0 ? '+' : '') . $n($d) . ' vs ' . $cmpLabel, $d === 0 ? 'var(--text-dim)' : ($good ? 'var(--green)' : 'var(--red)')];
    };

    $attn        = collect($checks)->filter(fn ($c) => in_array($c['severity'], ['critical', 'warning'], true));
    $provisional = collect($checks)->contains('code', 'session_open');

    $payLabels = ['cash' => 'Cash', 'mobile_money' => 'MoMo', 'bank_transfer' => 'Bank', 'other' => 'Other'];

    $cashDiff = $reconciliation->whereNotNull('variance');
    $cashDiffTotal = $cashDiff->isEmpty() ? null : (int) $cashDiff->sum('variance');

    $bankSales = (int) $summary['total_sales'] - (int) $summary['total_sales_cash'] - (int) $summary['total_sales_momo'] - (int) $summary['total_sales_credit'];
    $bankNote  = ((int) $summary['total_sales_card'] + (int) $summary['total_sales_other']) > 0 ? 'incl. card / other' : null;

    $cells = [
        ['Sales', (int) $summary['total_sales'], $summary['transaction_count'] . ' ' . \Illuminate\Support\Str::plural('transaction', $summary['transaction_count']) . ' · ' . $summary['total_boxes_sold'] . ' ' . \Illuminate\Support\Str::plural('box', $summary['total_boxes_sold'], ), 'total_sales', false, 'var(--text)'],
        ['Cash', (int) $summary['total_sales_cash'], null, 'total_sales_cash', false, 'var(--text)'],
        ['Mobile Money', (int) $summary['total_sales_momo'], null, 'total_sales_momo', false, 'var(--text)'],
        ['Bank', $bankSales, $bankNote, null, false, 'var(--text)'],
        ['Credit', (int) $summary['total_sales_credit'], 'not yet collected', 'total_sales_credit', false, 'var(--amber)'],
        ['Credit repaid', (int) $summary['total_repayments'], 'earlier credit, not sales', 'total_repayments', false, 'var(--green)'],
        ['Expenses', (int) $summary['total_expenses'], null, 'total_expenses', true, 'var(--red)'],
        ['Owner withdrawals', (int) $summary['total_withdrawals'], 'not a running cost', null, false, 'var(--text)'],
        ['Cash difference', $cashDiffTotal, $cashDiffTotal === null ? 'no closed session' : 'counted − expected', null, false, $vcol($cashDiffTotal)],
    ];

    $paid = [['Cash', (int) $summary['total_sales_cash'], true], ['Mobile Money', (int) $summary['total_sales_momo'], true],
             ['Card', (int) $summary['total_sales_card'], false], ['Bank transfer', (int) $summary['total_sales_bank_transfer'], false],
             ['Credit (not yet collected)', (int) $summary['total_sales_credit'], false], ['Other', (int) $summary['total_sales_other'], false]];

    $saleCard = collect($summary['all_sales'])->sum('card') > 0;
    $saleBank = collect($summary['all_sales'])->sum('bank_transfer') > 0;
    // Column weights normalised to 100% so the fixed-layout table always fits the page.
    $saleCols = ['sale' => 31, 'shop' => $isAll ? 22 : 0, 'date' => 16, 'cust' => 24, 'cash' => 17, 'momo' => 16,
                 'card' => $saleCard ? 16 : 0, 'bank' => $saleBank ? 16 : 0, 'credit' => 16, 'boxes' => 8, 'amount' => 19];
    $saleW = array_map(fn ($w) => round($w / array_sum($saleCols) * 100, 2), $saleCols);
    $sevWord  = ['critical' => 'Critical', 'warning' => 'Warning', 'info' => 'Info'];
    $sevCol   = ['critical' => 'var(--red)', 'warning' => 'var(--amber)', 'info' => 'var(--text-dim)'];
    $sevOrder = ['critical' => 0, 'warning' => 1, 'info' => 2];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Daily report — {{ $shopLabel }} — {{ $period }}</title>
<style>
/* Token values = light theme from resources/css/app.css :root (P3). */
:root {
    --surface:#ffffff; --surface2:#f0f2f8; --border:#e2e6f3;
    --text:#1a1f36; --text-sub:#4a5372; --text-dim:#7a81a0;
    --accent:#3b6fd4; --green:#0e9e86; --amber:#d97706; --red:#e11d48;
}
@page { margin:14mm 14mm 18mm 14mm; }
body { font-family:'DejaVu Sans', sans-serif; font-size:7.5pt; color:var(--text); background:var(--surface); margin:0; }
table { width:100%; border-collapse:collapse; }
td, th { padding:1.5mm 2.4mm; vertical-align:middle; }
.r { text-align:right; }
.nw { white-space:nowrap; }
.fixed { table-layout:fixed; }
.fixed td, .fixed th { padding:1.3mm 1.5mm; font-size:6.8pt; }
.dim { color:var(--text-dim); }
.sub { color:var(--text-sub); }
.b { font-weight:bold; }

.sec { margin-top:5mm; page-break-inside:avoid; }
.sec-title { font-size:8.5pt; font-weight:bold; color:var(--text); padding:0 0 1.2mm 0; border-bottom:1.5px solid var(--accent); }
.sec-note { font-size:6.8pt; color:var(--text-dim); padding:1mm 0 0 0; }

.data th { font-size:6.5pt; text-transform:uppercase; color:var(--text-dim); text-align:left; border-bottom:1.5px solid var(--border); }
.data th.r { text-align:right; }
.data td { border-bottom:1px solid var(--border); }
.data tr.tot td { font-weight:bold; border-top:1.5px solid var(--border); border-bottom:none; }
.data tr.alt td { background:var(--surface2); }
thead { display:table-header-group; }
tr { page-break-inside:avoid; }

.head td { padding:0; vertical-align:top; }
.brand { font-size:12pt; font-weight:bold; color:var(--text); }
.title { font-size:9pt; color:var(--text-sub); padding-top:0.8mm; }
.tag { font-size:7pt; font-weight:bold; color:var(--accent); border:1px solid var(--accent); padding:0.8mm 2.6mm; }
.meta { font-size:7pt; color:var(--text-dim); padding-top:1mm; }
.rule { border-top:2px solid var(--border); margin:3.5mm 0 0 0; }

.prov { margin-top:4mm; border:1px solid var(--amber); border-left:3px solid var(--amber); color:var(--amber); font-weight:bold; padding:2mm 3mm; font-size:7.5pt; }

.kf td { border:1px solid var(--border); padding:2.4mm 3mm; width:33.33%; vertical-align:top; }
.kf-l { font-size:6.5pt; text-transform:uppercase; color:var(--text-dim); font-weight:bold; }
.kf-v { font-size:12pt; font-weight:bold; padding-top:0.8mm; }
.kf-s { font-size:6.5pt; color:var(--text-dim); padding-top:0.5mm; }
.kf-d { font-size:6.5pt; padding-top:0.5mm; }

.bar { width:100%; border-collapse:collapse; table-layout:fixed; }
.bar td { padding:0; height:2.2mm; border:none; }
.pill { font-weight:bold; }
.blk { margin-top:2mm; page-break-inside:avoid; }
.blk-h td { padding:1.4mm 2.4mm; font-weight:bold; border-bottom:1.5px solid var(--border); }
.half { width:49%; vertical-align:top; padding:0; }
.gap  { width:2%; padding:0; }
.appx { page-break-before:always; }
</style>
</head>
<body>

{{-- Footer on every page (left text here; "Page X of Y" is drawn by the canvas, bottom-right) --}}
<div style="position:fixed; left:0; right:0; bottom:-11mm; height:6mm;">
    <table style="border-top:1px solid var(--border);"><tr>
        <td style="padding:1.5mm 0 0 0; font-size:6.5pt; color:var(--text-dim);">{{ $tenant }}, {{ $shopLabel }}, {{ $period }}</td>
        <td style="width:28%;"></td>
    </tr></table>
</div>

{{-- Header --}}
<table class="head">
    <tr>
        <td>
            <div class="brand">{{ $tenant }}</div>
            <div class="title">Daily report · {{ $shopLabel }}</div>
            <div class="title b" style="color:var(--text);">{{ $period }}</div>
            <div class="meta">Generated {{ $generated }} by {{ $generatedBy }}</div>
        </td>
        <td class="r"><span class="tag">{{ strtoupper($mode) }}</span></td>
    </tr>
</table>
<div class="rule"></div>

@if($provisional)
<div class="prov">Provisional: some sessions are still open. Figures can change.</div>
@endif

{{-- 1. Key figures --}}
<div class="sec">
    <div class="sec-title">Key figures</div>
    <table class="kf" style="margin-top:2mm;">
        @foreach(array_chunk($cells, 3) as $row)
        <tr>
            @foreach($row as [$label, $value, $subline, $dKey, $inv, $col])
            @php $dl = $value === null ? null : $delta($dKey, $value, $inv); @endphp
            <td>
                <div class="kf-l">{{ $label }}</div>
                <div class="kf-v" style="color:{{ $col }};">{{ $value === null ? '—' : $n($value) }}@if($value !== null) <span class="dim" style="font-size:6.5pt; font-weight:normal;">RWF</span>@endif</div>
                @if($subline)<div class="kf-s">{{ $subline }}</div>@endif
                @if($dl)<div class="kf-d" style="color:{{ $dl[1] }};">{{ $dl[0] }}</div>@endif
            </td>
            @endforeach
        </tr>
        @endforeach
    </table>
</div>

{{-- 2. Cash reconciliation (each session block avoids breaking; the section as a whole may flow across pages) --}}
<div class="sec" style="page-break-inside:auto;">
    <div class="sec-title">Cash reconciliation</div>
    <div class="sec-note">Opening cash + cash in − cash out = expected, compared with the cash counted at close.</div>
    @if($reconciliation->isEmpty())
        <div class="sec-note">No cash session in this period.</div>
    @elseif($isSingleDay)
        @foreach($reconciliation as $rc)
        @php $open = $rc->status === 'open'; @endphp
        <table class="data blk">
            <tr class="blk-h"><td>@if($isAll){{ $rc->shop_name }} · @endif{{ ucfirst($rc->status) }}</td><td class="r dim" style="font-weight:normal;">{{ $open ? 'live' : 'closed' }}</td></tr>
            <tr><td>Opening cash</td><td class="r">{{ $n($rc->opening) }}</td></tr>
            <tr><td>+ Cash sales</td><td class="r">{{ $n($rc->cash_sales) }}</td></tr>
            <tr><td>+ Credit repaid in cash</td><td class="r">{{ $n($rc->cash_repayments) }}</td></tr>
            @if($rc->cash_refunds)<tr><td>− Cash refunds</td><td class="r">{{ $n($rc->cash_refunds) }}</td></tr>@endif
            <tr><td>− Cash expenses</td><td class="r">{{ $n($rc->cash_expenses) }}</td></tr>
            @if($rc->cash_withdrawals)<tr><td>− Owner withdrawals (cash)</td><td class="r">{{ $n($rc->cash_withdrawals) }}</td></tr>@endif
            @if($rc->cash_deposits)<tr><td>− Cash deposited to bank</td><td class="r">{{ $n($rc->cash_deposits) }}</td></tr>@endif
            @if($rc->other_adjustments)<tr><td style="color:var(--amber);">± Other adjustments</td><td class="r" style="color:var(--amber);">{{ $sg($rc->other_adjustments) }}</td></tr>@endif
            <tr class="tot"><td>= Expected cash</td><td class="r">{{ $n($rc->expected) }}</td></tr>
            <tr><td>Counted cash</td><td class="r">{{ $rc->counted !== null ? $n($rc->counted) : 'Session open' }}</td></tr>
            <tr><td class="b">Difference</td><td class="r b" style="color:{{ $vcol($rc->variance) }};">{{ $rc->variance === null ? '—' : $sg($rc->variance) }}</td></tr>
        </table>
        @endforeach
    @else
        <table class="data" style="margin-top:2mm;">
            <thead><tr>
                <th>Date</th>@if($isAll)<th>Shop</th>@endif
                <th class="r">Opening</th><th class="r">Cash in</th><th class="r">Cash out</th>
                <th class="r">Expected</th><th class="r">Counted</th><th class="r">Difference</th>
            </tr></thead>
            <tbody>
            @foreach($reconciliation as $rc)
            <tr>
                <td class="nw">{{ \Carbon\Carbon::parse($rc->date)->format('d M Y') }}</td>@if($isAll)<td>{{ $rc->shop_name }}</td>@endif
                <td class="r">{{ $n($rc->opening) }}</td>
                <td class="r">{{ $n($rc->cash_sales + $rc->cash_repayments) }}</td>
                <td class="r">{{ $n($rc->cash_refunds + $rc->cash_expenses + $rc->cash_withdrawals + $rc->cash_deposits) }}</td>
                <td class="r">{{ $n($rc->expected) }}</td>
                <td class="r">{{ $rc->counted !== null ? $n($rc->counted) : 'Open' }}</td>
                <td class="r" style="color:{{ $vcol($rc->variance) }};">{{ $rc->variance === null ? '—' : $sg($rc->variance) }}</td>
            </tr>
            @endforeach
            <tr class="tot">
                <td colspan="{{ $isAll ? 3 : 2 }}">Total</td>
                <td class="r">{{ $n($reconciliation->sum(fn ($r) => $r->cash_sales + $r->cash_repayments)) }}</td>
                <td class="r">{{ $n($reconciliation->sum(fn ($r) => $r->cash_refunds + $r->cash_expenses + $r->cash_withdrawals + $r->cash_deposits)) }}</td>
                <td colspan="2"></td>
                <td class="r" style="color:{{ $vcol($cashDiffTotal) }};">{{ $cashDiffTotal === null ? '—' : $sg($cashDiffTotal) }}</td>
            </tr>
            </tbody>
        </table>
    @endif
</div>

{{-- 3. How sales were paid --}}
<div class="sec">
    <div class="sec-title">How sales were paid</div>
    <table class="data" style="margin-top:2mm;">
        <thead><tr><th style="width:30%;">Channel</th><th class="r" style="width:16%;">Amount</th><th class="r" style="width:9%;">Share</th><th style="width:45%;"></th></tr></thead>
        <tbody>
        @foreach($paid as [$lbl, $amt, $always])
            @if($amt !== 0 || $always)
            @php $pct = $summary['total_sales'] > 0 ? ($amt / $summary['total_sales']) * 100 : 0; @endphp
            <tr>
                <td>{{ $lbl }}</td>
                <td class="r">{{ $n($amt) }}</td>
                <td class="r dim">{{ number_format($pct, 1) }}%</td>
                <td>
                    <table class="bar"><tr>
                        @if($pct > 0)<td style="width:{{ round($pct, 1) }}%; background-color:var(--accent);"></td><td></td>@else<td></td>@endif
                    </tr></table>
                </td>
            </tr>
            @endif
        @endforeach
            <tr class="tot"><td>Total</td><td class="r">{{ $n($summary['total_sales']) }}</td><td class="r">{{ $summary['total_sales'] > 0 ? '100.0%' : '0.0%' }}</td><td></td></tr>
        </tbody>
    </table>
</div>

{{-- 4. Credit --}}
@php
    $soldCredit = collect($summary['credits_by_customer']);
    $repaidCred = collect($summary['repayments_by_customer']);
@endphp
<div class="sec">
    <div class="sec-title">Credit</div>
    @if($soldCredit->isEmpty() && $repaidCred->isEmpty())
        <div class="sec-note">No credit sold or repaid in this period.</div>
    @else
    <table style="margin-top:2mm;"><tr>
        <td class="half">
            <div class="b" style="padding-bottom:1mm;">Sold on credit</div>
            @if($soldCredit->isEmpty())
                <div class="dim">None in this period.</div>
            @else
            <table class="data">
                <thead><tr><th>Customer</th><th class="r">Sales</th><th class="r">Amount</th></tr></thead>
                <tbody>
                @foreach($soldCredit->take(10) as $row)
                <tr><td>{{ $row->customer_name }}</td><td class="r">{{ $n($row->sales_count) }}</td><td class="r">{{ $n($row->amount) }}</td></tr>
                @endforeach
                @if($soldCredit->count() > 10)<tr><td colspan="3" class="dim">+ {{ $soldCredit->count() - 10 }} more {{ \Illuminate\Support\Str::plural('customer', $soldCredit->count() - 10) }}</td></tr>@endif
                <tr class="tot"><td>Total</td><td class="r">{{ $n($soldCredit->sum('sales_count')) }}</td><td class="r">{{ $n($summary['total_sales_credit']) }}</td></tr>
                </tbody>
            </table>
            @endif
        </td>
        <td class="gap"></td>
        <td class="half">
            <div class="b" style="padding-bottom:1mm;">Credit repaid</div>
            @if($repaidCred->isEmpty())
                <div class="dim">None in this period.</div>
            @else
            <table class="data">
                <thead><tr><th>Customer</th><th class="r">Cash</th><th class="r">MoMo</th><th class="r">Bank</th><th class="r">Total</th></tr></thead>
                <tbody>
                @foreach($repaidCred->take(10) as $row)
                <tr><td>{{ $row->customer_name }}</td><td class="r">{{ $n($row->cash) }}</td><td class="r">{{ $n($row->momo) }}</td><td class="r">{{ $n($row->bank) }}</td><td class="r">{{ $n($row->amount) }}</td></tr>
                @endforeach
                @if($repaidCred->count() > 10)<tr><td colspan="5" class="dim">+ {{ $repaidCred->count() - 10 }} more {{ \Illuminate\Support\Str::plural('customer', $repaidCred->count() - 10) }}</td></tr>@endif
                <tr class="tot"><td>Total</td><td class="r">{{ $n($summary['total_repayments_cash']) }}</td><td class="r">{{ $n($summary['total_repayments_momo']) }}</td><td class="r">{{ $n($summary['total_repayments_bank']) }}</td><td class="r">{{ $n($summary['total_repayments']) }}</td></tr>
                </tbody>
            </table>
            @endif
        </td>
    </tr></table>
    @endif
</div>

{{-- 5. Expenses by category + owner withdrawals --}}
@php
    $expCats  = collect($summary['expenses_by_category']);
    $expBank  = $expCats->sum('bank') > 0;
@endphp
<div class="sec">
    <div class="sec-title">Expenses by category</div>
    @if($expCats->isEmpty())
        <div class="sec-note">No expenses in this period.</div>
    @else
    <table class="data" style="margin-top:2mm;">
        <thead><tr>
            <th>Category</th><th class="r">Items</th><th class="r">Cash</th><th class="r">MoMo</th>@if($expBank)<th class="r">Bank</th>@endif<th class="r">Total</th><th class="r">Share</th>
        </tr></thead>
        <tbody>
        @foreach($expCats as $row)
        <tr>
            <td>{{ $row->category }}</td><td class="r">{{ $n($row->count) }}</td><td class="r">{{ $n($row->cash) }}</td><td class="r">{{ $n($row->momo) }}</td>
            @if($expBank)<td class="r">{{ $n($row->bank) }}</td>@endif
            <td class="r">{{ $n($row->total) }}</td><td class="r dim">{{ number_format($summary['total_expenses'] > 0 ? ($row->total / $summary['total_expenses']) * 100 : 0, 1) }}%</td>
        </tr>
        @endforeach
        <tr class="tot">
            <td>Total</td><td class="r">{{ $n($expCats->sum('count')) }}</td><td class="r">{{ $n($summary['total_expenses_cash']) }}</td><td class="r">{{ $n($summary['total_expenses_momo']) }}</td>
            @if($expBank)<td class="r">{{ $n($summary['total_expenses_bank']) }}</td>@endif
            <td class="r">{{ $n($summary['total_expenses']) }}</td><td class="r">100.0%</td>
        </tr>
        </tbody>
    </table>
    @endif
</div>
@if($summary['total_withdrawals'] > 0)
<div class="sec">
    <div class="sec-title">Owner withdrawals</div>
    <div class="sec-note">Not a shop running cost</div>
    <table class="data" style="margin-top:1.5mm;">
        <thead><tr><th class="r">Cash</th><th class="r">MoMo</th><th class="r">Total</th></tr></thead>
        <tbody><tr>
            <td class="r">{{ $n($summary['total_withdrawals_cash']) }}</td><td class="r">{{ $n($summary['total_withdrawals_momo']) }}</td><td class="r b">{{ $n($summary['total_withdrawals']) }}</td>
        </tr></tbody>
    </table>
</div>
@endif

{{-- 6. Cash deposited to bank / Refunds (only if non-zero) --}}
@if($summary['total_bank_deposits'] > 0)
<div class="sec">
    <div class="sec-title">Cash deposited to bank</div>
    <table class="data" style="margin-top:1.5mm;">
        <thead><tr><th class="r">From cash</th><th class="r">From MoMo</th><th class="r">Total</th></tr></thead>
        <tbody><tr>
            <td class="r">{{ $n($summary['bank_deposits_from_cash']) }}</td><td class="r">{{ $n($summary['bank_deposits_from_momo']) }}</td><td class="r b">{{ $n($summary['total_bank_deposits']) }}</td>
        </tr></tbody>
    </table>
</div>
@endif
@if($summary['total_refunds'] > 0)
<div class="sec">
    <div class="sec-title">Refunds</div>
    <table class="data" style="margin-top:1.5mm;">
        <thead><tr><th class="r">Cash</th><th class="r">Other methods</th><th class="r">Total</th></tr></thead>
        <tbody><tr>
            <td class="r">{{ $n($summary['total_refunds_cash']) }}</td><td class="r">{{ $n($summary['total_refunds'] - $summary['total_refunds_cash']) }}</td><td class="r b">{{ $n($summary['total_refunds']) }}</td>
        </tr></tbody>
    </table>
</div>
@endif

{{-- 7. Checks --}}
<div class="sec">
    <div class="sec-title">Checks</div>
    @if(empty($checks))
        <div class="sec-note" style="font-size:7.5pt;">All checks passed.</div>
    @else
    <table class="data" style="margin-top:2mm;">
        <thead><tr><th style="width:11%;">Severity</th><th>Message</th>@if($isAll)<th style="width:14%;">Shop</th>@endif<th style="width:12%;">Date</th><th class="r" style="width:11%;">Amount</th></tr></thead>
        <tbody>
        @foreach(collect($checks)->sortBy(fn ($c) => $sevOrder[$c['severity']] ?? 3) as $c)
        <tr>
            <td class="pill" style="color:{{ $sevCol[$c['severity']] ?? 'var(--text-dim)' }};">{{ $sevWord[$c['severity']] ?? ucfirst($c['severity']) }}</td>
            <td>{{ $c['message'] }}
                @if(!empty($c['details']))
                @php $withCost = isset($c['details'][0]['cost']); @endphp
                <table class="data" style="margin-top:1.5mm;">
                    <thead><tr><th>Sale</th><th>Product</th><th>Qty</th><th class="r">List</th><th class="r">Sold</th><th class="r">Discount</th><th class="r">% off</th>@if($withCost)<th class="r">Profit list</th><th class="r">Profit sold</th>@endif</tr></thead>
                    <tbody>
                    @foreach($c['details'] as $d)
                    <tr>
                        <td class="nw">{{ $d['sale_number'] }}</td><td>{{ $d['product'] }}</td><td class="nw">{{ $d['qty'] }}</td>
                        <td class="r">{{ $n($d['list']) }}</td><td class="r">{{ $n($d['sold']) }}</td>
                        <td class="r" style="color:var(--red);">{{ $d['discount'] > 0 ? '−' : '' }}{{ number_format(abs($d['discount'])) }}</td>
                        <td class="r dim">{{ number_format($d['pct'], 1) }}%</td>
                        @if($withCost)<td class="r">{{ $n($d['profit_at_list']) }}</td><td class="r">{{ $n($d['profit_sold']) }}</td>@endif
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </td>
            @if($isAll)<td class="dim">{{ $c['shop_name'] ?? '' }}</td>@endif
            <td class="dim">{{ !empty($c['date']) ? \Carbon\Carbon::parse($c['date'])->format('d M Y') : '' }}</td>
            <td class="r">{{ $c['amount'] !== null ? ($c['code'] === 'cash_variance' ? $sg($c['amount']) : $n($c['amount'])) : '' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Appendix — Transactions view only, new page --}}
@if($viewMode === 'transactions')
<div class="appx">
    <div class="sec" style="margin-top:0; page-break-inside:auto;">
        <div class="sec-title">Appendix — All sales</div>
        @if(count($summary['all_sales']) === 0)
            <div class="sec-note">No sales in this period.</div>
        @else
        <table class="data fixed" style="margin-top:2mm;">
            <thead><tr>
                <th style="width:{{ $saleW['sale'] }}%;">Sale #</th>@if($isAll)<th style="width:{{ $saleW['shop'] }}%;">Shop</th>@endif<th style="width:{{ $saleW['date'] }}%;">Date</th><th style="width:{{ $saleW['cust'] }}%;">Customer</th>
                <th class="r" style="width:{{ $saleW['cash'] }}%;">Cash</th><th class="r" style="width:{{ $saleW['momo'] }}%;">MoMo</th>@if($saleCard)<th class="r" style="width:{{ $saleW['card'] }}%;">Card</th>@endif @if($saleBank)<th class="r" style="width:{{ $saleW['bank'] }}%;">Bank</th>@endif<th class="r" style="width:{{ $saleW['credit'] }}%;">Credit</th>
                <th class="r" style="width:{{ $saleW['boxes'] }}%;">Box</th><th class="r" style="width:{{ $saleW['amount'] }}%;">Amount</th>
            </tr></thead>
            <tbody>
            @foreach($summary['all_sales'] as $row)
            <tr>
                <td class="nw">{{ $row->sale_number }}@if($row->has_price_override)<span style="color:var(--amber);"> *</span>@endif</td>
                @if($isAll)<td class="dim">{{ $row->shop_name }}</td>@endif
                <td class="dim nw">{{ \Carbon\Carbon::parse($row->sale_date)->timezone($tz)->format('d M y') }}</td>
                <td>{{ $row->customer_name ?? '—' }}</td>
                <td class="r">{{ $n($row->cash) }}</td><td class="r">{{ $n($row->momo) }}</td>@if($saleCard)<td class="r">{{ $n($row->card) }}</td>@endif @if($saleBank)<td class="r">{{ $n($row->bank_transfer) }}</td>@endif<td class="r">{{ $n($row->credit) }}</td>
                <td class="r">{{ $n($row->boxes) }}</td><td class="r">{{ $n($row->total) }}</td>
            </tr>
            @endforeach
            <tr class="tot">
                <td colspan="{{ 3 + ($isAll ? 1 : 0) }}">Grand total</td>
                <td class="r">{{ $n(collect($summary['all_sales'])->sum('cash')) }}</td><td class="r">{{ $n(collect($summary['all_sales'])->sum('momo')) }}</td>
                @if($saleCard)<td class="r">{{ $n(collect($summary['all_sales'])->sum('card')) }}</td>@endif
                @if($saleBank)<td class="r">{{ $n(collect($summary['all_sales'])->sum('bank_transfer')) }}</td>@endif
                <td class="r">{{ $n(collect($summary['all_sales'])->sum('credit')) }}</td>
                <td class="r">{{ $n(collect($summary['all_sales'])->sum('boxes')) }}</td><td class="r">{{ $n(collect($summary['all_sales'])->sum('total')) }}</td>
            </tr>
            </tbody>
        </table>
        <div class="sec-note">* sale had a price override</div>
        @endif
    </div>

    @foreach([['Mobile Money — by customer', $summary['momo_by_customer']], ['Cash — by customer', $summary['cash_by_customer']], ['Bank transfer — by customer', $summary['bank_by_customer']]] as [$ttl, $rows])
    @if(count($rows) > 0)
    <div class="sec" style="page-break-inside:auto;">
        <div class="sec-title">{{ $ttl }}</div>
        <table class="data" style="margin-top:2mm;">
            <thead><tr><th>Customer</th><th class="r">Sales</th><th class="r">Amount</th></tr></thead>
            <tbody>
            @foreach($rows as $row)
            <tr><td>{{ $row->customer_name }}</td><td class="r">{{ $n($row->sales_count) }}</td><td class="r">{{ $n($row->amount) }}</td></tr>
            @endforeach
            <tr class="tot"><td>Total</td><td class="r">{{ $n(collect($rows)->sum('sales_count')) }}</td><td class="r">{{ $n(collect($rows)->sum('amount')) }}</td></tr>
            </tbody>
        </table>
    </div>
    @endif
    @endforeach

    @if(count($summary['expenses_detailed']) > 0)
    <div class="sec" style="page-break-inside:auto;">
        <div class="sec-title">Expenses — detailed</div>
        <table class="data" style="margin-top:2mm;">
            <thead><tr><th>Date</th>@if($isAll)<th>Shop</th>@endif<th>Category</th><th>Description</th><th>Method</th><th class="r">Amount</th></tr></thead>
            <tbody>
            @foreach($summary['expenses_detailed'] as $row)
            <tr>
                <td class="dim nw">{{ \Carbon\Carbon::parse($row->session_date)->format('d M Y') }}</td>@if($isAll)<td class="dim">{{ $row->shop_name }}</td>@endif
                <td>{{ $row->category }}</td><td class="dim">{{ $row->description ?: '—' }}</td>
                <td>{{ $payLabels[(string) $row->payment_method] ?? ucfirst((string) $row->payment_method) }}</td><td class="r">{{ $n($row->amount) }}</td>
            </tr>
            @endforeach
            <tr class="tot"><td colspan="{{ $isAll ? 5 : 4 }}">Total</td><td class="r">{{ $n($summary['total_expenses']) }}</td></tr>
            </tbody>
        </table>
    </div>
    @endif

    @if(count($summary['withdrawals_detailed']) > 0)
    <div class="sec" style="page-break-inside:auto;">
        <div class="sec-title">Owner withdrawals — detailed</div>
        <table class="data" style="margin-top:2mm;">
            <thead><tr><th>Date</th>@if($isAll)<th>Shop</th>@endif<th>Reason</th><th>Method</th><th class="r">Amount</th><th>Recorded by</th></tr></thead>
            <tbody>
            @foreach($summary['withdrawals_detailed'] as $row)
            <tr>
                <td class="dim nw">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>@if($isAll)<td class="dim">{{ $row->shop_name }}</td>@endif
                <td>{{ $row->reason ?: '—' }}</td><td>{{ $payLabels[(string) $row->method] ?? ucfirst((string) $row->method) }}</td>
                <td class="r">{{ $n($row->amount) }}</td><td class="dim">{{ $row->recorded_by_name ?: '—' }}</td>
            </tr>
            @endforeach
            <tr class="tot"><td colspan="{{ $isAll ? 4 : 3 }}">Total</td><td class="r">{{ $n($summary['total_withdrawals']) }}</td><td></td></tr>
            </tbody>
        </table>
    </div>
    @endif

    @if(count($summary['bank_deposits_detailed']) > 0)
    <div class="sec" style="page-break-inside:auto;">
        <div class="sec-title">Bank deposits — detailed</div>
        <table class="data" style="margin-top:2mm;">
            <thead><tr><th>Date</th>@if($isAll)<th>Shop</th>@endif<th>Source</th><th>Reference</th><th class="r">Amount</th><th>Deposited by</th></tr></thead>
            <tbody>
            @foreach($summary['bank_deposits_detailed'] as $row)
            <tr>
                <td class="dim nw">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>@if($isAll)<td class="dim">{{ $row->shop_name }}</td>@endif
                <td>{{ ['cash' => 'Cash', 'mobile_money' => 'MoMo'][$row->source] ?? ucfirst((string) $row->source) }}</td><td class="dim">{{ $row->bank_reference ?: '—' }}</td>
                <td class="r">{{ $n($row->amount) }}</td><td class="dim">{{ $row->deposited_by_name ?: '—' }}</td>
            </tr>
            @endforeach
            <tr class="tot"><td colspan="{{ $isAll ? 4 : 3 }}">Total</td><td class="r">{{ $n($summary['total_bank_deposits']) }}</td><td></td></tr>
            </tbody>
        </table>
    </div>
    @endif

    @if(count($summary['refunds_detailed']) > 0)
    <div class="sec" style="page-break-inside:auto;">
        <div class="sec-title">Refunds — detailed</div>
        <table class="data" style="margin-top:2mm;">
            <thead><tr><th>Date</th>@if($isAll)<th>Shop</th>@endif<th>Return #</th><th>Customer</th><th>Method</th><th class="r">Amount</th></tr></thead>
            <tbody>
            @foreach($summary['refunds_detailed'] as $row)
            <tr>
                <td class="dim nw">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>@if($isAll)<td class="dim">{{ $row->shop_name }}</td>@endif
                <td>{{ $row->return_number }}</td><td>{{ $row->customer_name ?: '—' }}</td>
                <td>{{ $row->method ? ($payLabels[$row->method] ?? ucfirst($row->method)) : '—' }}</td><td class="r">{{ $n($row->amount) }}</td>
            </tr>
            @endforeach
            <tr class="tot"><td colspan="{{ $isAll ? 5 : 4 }}">Total</td><td class="r">{{ $n($summary['total_refunds']) }}</td></tr>
            </tbody>
        </table>
    </div>
    @endif
</div>
@endif

</body>
</html>
