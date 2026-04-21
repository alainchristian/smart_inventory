<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Receipt {{ $sale->sale_number }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
html, body { font-family:'Courier New', Courier, monospace; font-size:12px; color:#000; background:#fff; width:76mm; }
@page { size:80mm auto; margin:2mm; }

.center  { text-align:center; }
.right   { text-align:right; }
.bold    { font-weight:bold; }
.small   { font-size:10px; }
.xsmall  { font-size:9px; }
.large   { font-size:15px; }
.xlarge  { font-size:18px; }

.shop-name { font-size:16px; font-weight:bold; text-align:center; margin-bottom:2px; }
.shop-sub  { font-size:10px; text-align:center; color:#444; }

hr.dashed  { border:none; border-top:1px dashed #999; margin:6px 0; }
hr.solid   { border:none; border-top:1px solid #000; margin:6px 0; }

/* Items table */
.items { width:100%; border-collapse:collapse; margin:4px 0; }
.items td { padding:2px 0; vertical-align:top; }
.items .name { width:60%; font-size:11px; }
.items .qty  { width:15%; text-align:right; font-size:11px; color:#444; }
.items .amt  { width:25%; text-align:right; font-size:11px; font-weight:bold; }
.items .sub  { font-size:9px; color:#555; padding-left:0; }
.items .mod  { font-size:9px; color:#b45309; }

/* Total */
.total-row  { display:flex; justify-content:space-between; align-items:baseline; margin:4px 0; }
.total-label{ font-size:13px; font-weight:bold; }
.total-amt  { font-size:17px; font-weight:bold; }

/* Payments */
.pay-row    { display:flex; justify-content:space-between; font-size:11px; padding:1px 0; }
.pay-label  { color:#333; }
.pay-ref    { font-size:9px; color:#666; margin-left:4px; }

/* Credit badge */
.credit-box { border:1px solid #b45309; border-radius:4px; padding:5px 7px; margin:6px 0; background:#fef3c7; }
.credit-lbl { font-size:10px; font-weight:bold; color:#92400e; }
.credit-amt { font-size:12px; font-weight:bold; color:#92400e; }

/* Customer / meta */
.info-row   { font-size:10px; color:#444; margin:2px 0; }
.footer     { text-align:center; font-size:9px; color:#666; margin-top:6px; }
</style>
</head>
<body>

{{-- Shop header --}}
<div class="shop-name">{{ $sale->shop->name ?? config('app.name') }}</div>
<div class="shop-sub">Receipt</div>

<hr class="dashed">

{{-- Sale meta --}}
<div class="info-row"><span class="bold">Receipt #:</span> {{ $sale->sale_number }}</div>
<div class="info-row"><span class="bold">Date:</span> {{ ($sale->sale_date ?? $sale->created_at)->format('d M Y  H:i') }}</div>
<div class="info-row"><span class="bold">Cashier:</span> {{ $sale->soldBy->name ?? '—' }}</div>
@if($sale->customer_name)
<div class="info-row"><span class="bold">Customer:</span> {{ $sale->customer_name }}{{ $sale->customer_phone ? ' · '.$sale->customer_phone : '' }}</div>
@endif

<hr class="dashed">

{{-- Items --}}
<table class="items">
@foreach($groupedItems as $item)
<tr>
    <td class="name bold">{{ $item['product_name'] }}</td>
    <td class="qty">{{ $item['quantity'] }}{{ $item['is_full_box'] ? 'bx' : 'pc' }}</td>
    <td class="amt">{{ number_format($item['line_total']) }}</td>
</tr>
<tr>
    <td class="sub" colspan="3">
        @if($item['is_full_box'])
            {{ $item['quantity'] }} {{ $item['quantity'] === 1 ? 'box' : 'boxes' }} × {{ number_format($item['unit_price']) }}
        @else
            {{ $item['quantity'] }} items × {{ number_format($item['unit_price']) }}
        @endif
        @if($item['price_modified'])
            <span class="mod"> (orig {{ number_format($item['original_price']) }})</span>
        @endif
    </td>
</tr>
@endforeach
</table>

<hr class="solid">

{{-- Total --}}
<div class="total-row">
    <span class="total-label">TOTAL</span>
    <span class="total-amt">{{ number_format($sale->total) }} RWF</span>
</div>

<hr class="dashed">

{{-- Payment breakdown --}}
@if($sale->payments && $sale->payments->count() > 0)
@foreach($sale->payments as $pmt)
<div class="pay-row">
    <span class="pay-label">
        {{ match($pmt->payment_method->value) {
            'cash'          => 'Cash',
            'card'          => 'Card',
            'mobile_money'  => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'credit'        => 'Credit',
            default         => ucfirst($pmt->payment_method->value)
        } }}
        @if($pmt->reference)<span class="pay-ref">({{ $pmt->reference }})</span>@endif
    </span>
    <span class="bold">{{ number_format($pmt->amount) }} RWF</span>
</div>
@endforeach
@endif

{{-- Credit highlight --}}
@if($sale->has_credit && $sale->credit_amount > 0)
<div class="credit-box">
    <div class="total-row">
        <span class="credit-lbl">Credit recorded</span>
        <span class="credit-amt">{{ number_format($sale->credit_amount) }} RWF</span>
    </div>
</div>
@endif

{{-- Notes --}}
@if($sale->notes)
<hr class="dashed">
<div class="info-row"><em>Note: {{ $sale->notes }}</em></div>
@endif

<hr class="dashed">
<div class="footer">Thank you for your business!</div>
<div class="footer xsmall" style="margin-top:3px;">Printed: {{ now()->format('d M Y H:i') }}</div>

{{-- Auto-print and close --}}
<script>
window.onload = function () {
    window.print();
    window.onafterprint = function () { window.close(); };
};
</script>
</body>
</html>
