@php
    $sale->loadMissing(['items.product', 'payments', 'shop', 'soldBy', 'customer', 'voidedBy']);
    $isVoided  = (bool) $sale->voided_at;
    $isDeleted = (bool) $sale->deleted_at;

    // Group items by product + unit price + sale type so repeat box/item
    // lines for the same product show as one aggregated row (matches the
    // grouping used on the printed receipt — see ReceiptController::print).
    $groupedItems = $sale->items
        ->groupBy(fn ($i) => $i->product_id . '_' . $i->actual_unit_price . '_' . ($i->is_full_box ? 'b' : 'i'))
        ->map(function ($grp) {
            $first      = $grp->first();
            $isBox      = $first->is_full_box;
            $product    = $first->product;
            $totalItems = $grp->sum('quantity_sold');

            return [
                'product_name'   => $product->name ?? '—',
                'quantity'       => $product ? $product->itemsToDisplayQty($totalItems, $isBox) : $totalItems,
                // Already at line_total's scale (box-total for full-box lines) —
                // do not route through displayUnitPrice(), see Sale::groupedItems().
                'unit_price'     => $first->actual_unit_price,
                'line_total'     => $grp->sum('line_total'),
                'is_full_box'    => $isBox,
                'price_modified' => $grp->contains('price_was_modified', true),
            ];
        })
        ->values();
@endphp
<x-app-layout>
<style>
/* ── Owner Sale Show ─────────────────────── oss- */
.oss-wrap { display:flex; flex-direction:column; gap:16px; }

/* Back bar */
.oss-back-bar { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
.oss-back {
    display:inline-flex; align-items:center; gap:6px;
    font-size:14px; font-weight:600; color:var(--text-dim);
    text-decoration:none; transition:color .15s;
}
.oss-back:hover { color:var(--accent); }

/* Cards */
.oss-card { background:var(--surface); border:none; border-radius:var(--r); box-shadow:var(--shadow-card); }
.oss-card-head {
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    padding:14px 20px; border-bottom:1px solid var(--border); flex-wrap:wrap;
}
.oss-card-title { font-size:13px; font-weight:700; color:var(--text); }
.oss-card-body  { padding:16px 20px; }

/* Sale header */
.oss-num  { font-size:17px; font-weight:800; color:var(--text); font-family:var(--mono); letter-spacing:-.3px; }
.oss-pill {
    display:inline-flex; align-items:center; gap:5px;
    padding:2px 9px; border-radius:999px; font-size:12px; font-weight:700; letter-spacing:.4px; text-transform:uppercase;
    border:1px solid;
}
.oss-pill.completed { background:var(--green-dim); color:var(--green); border-color:rgba(16,185,129,.25); }
.oss-pill.voided    { background:var(--red-dim);   color:var(--red);   border-color:rgba(225,29,72,.25); }

/* Meta grid */
.oss-meta-grid  { display:flex; flex-wrap:wrap; gap:22px 32px; }
.oss-meta-item  { display:flex; flex-direction:column; gap:3px; flex:1 1 160px; min-width:140px; }
.oss-meta-item.full { flex-basis:100%; }
.oss-meta-label { font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:var(--text-dim); }
.oss-meta-value { font-size:15px; font-weight:600; color:var(--text); }
.oss-meta-sub   { font-size:12px; color:var(--text-dim); }

/* Items table */
.oss-table { width:100%; border-collapse:collapse; min-width:520px; }
.oss-table thead tr { border-bottom:2px solid var(--border); }
.oss-table thead th {
    padding:10px 16px; font-size:11px; font-weight:700; letter-spacing:.5px;
    text-transform:uppercase; color:var(--text-dim); text-align:left; white-space:nowrap;
}
.oss-table tbody tr { border-bottom:1px solid var(--border); transition:background var(--tr); }
.oss-table tbody tr:last-child { border-bottom:none; }
.oss-table tbody tr:hover { background:var(--surface2); }
.oss-table td { padding:12px 16px; font-size:13px; color:var(--text); vertical-align:middle; }
.oss-prod-name { font-weight:600; }
.oss-val       { font-family:var(--mono); font-weight:700; }

/* Payment rows */
.oss-pay-row {
    display:flex; align-items:center; justify-content:space-between;
    padding:9px 0; border-bottom:1px solid var(--border);
}
.oss-pay-row:last-child { border-bottom:none; }
.oss-pay-method { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text-sub); }
.oss-pay-amount { font-family:var(--mono); font-weight:700; font-size:14px; color:var(--text); }
.oss-total-row {
    display:flex; align-items:center; justify-content:space-between;
    padding-top:12px; margin-top:6px; border-top:2px solid var(--border);
}
.oss-total-label { font-size:13px; font-weight:700; color:var(--text); }
.oss-total-value { font-family:var(--mono); font-weight:800; font-size:18px; color:var(--accent); }

/* Void banner */
.oss-void-banner {
    display:flex; align-items:flex-start; gap:10px;
    padding:12px 16px; border-radius:10px; font-size:14px;
    background:var(--red-dim); border:1px solid rgba(225,29,72,.25); color:var(--red); line-height:1.5;
}

/* Print button */
.oss-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 14px; border-radius:8px; font-size:13px; font-weight:600;
    text-decoration:none; border:1px solid var(--border);
    background:var(--surface); color:var(--text-sub); transition:all var(--tr);
}
.oss-btn:hover { border-color:var(--accent); color:var(--accent); }
</style>

<div class="oss-wrap" style="font-family:var(--font)">

    {{-- Back bar --}}
    <div class="oss-back-bar">
        <a href="{{ route('owner.reports.sales') }}" class="oss-back">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Sales Reports
        </a>
        <a href="{{ route('shop.sales.receipt', ['sale' => $sale, 'full' => 1]) }}" target="_blank" class="oss-btn">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
            Print Receipt
        </a>
    </div>

    {{-- Void warning --}}
    @if($isVoided)
    <div class="oss-void-banner">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span>
            This sale was voided by {{ $sale->voidedBy->name ?? '—' }} on {{ $sale->voided_at?->format('d M Y · H:i') }}.
            @if($sale->void_reason) Reason: {{ $sale->void_reason }} @endif
        </span>
    </div>
    @endif

    @if($isDeleted && !$isVoided)
    <div class="oss-void-banner" style="background:var(--surface2);border-color:var(--border);color:var(--text-dim)">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V4a1 1 0 011-1h6a1 1 0 011 1v3"/></svg>
        <span>This sale record has been deleted and is no longer visible in standard reports.</span>
    </div>
    @endif

    {{-- Sale header --}}
    <div class="oss-card">
        <div class="oss-card-head">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span class="oss-num">{{ $sale->sale_number }}</span>
                <span class="oss-pill {{ $isVoided ? 'voided' : 'completed' }}">
                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor"></span>
                    {{ $isVoided ? 'Voided' : 'Completed' }}
                </span>
            </div>
            <span style="font-size:13px;color:var(--text-dim)">{{ $sale->sale_date?->format('d M Y · H:i') }}</span>
        </div>
        <div class="oss-card-body">
            <div class="oss-meta-grid">
                <div class="oss-meta-item">
                    <span class="oss-meta-label">Shop</span>
                    <span class="oss-meta-value">{{ $sale->shop->name ?? '—' }}</span>
                </div>
                <div class="oss-meta-item">
                    <span class="oss-meta-label">Sold By</span>
                    <span class="oss-meta-value">{{ $sale->soldBy->name ?? '—' }}</span>
                </div>
                <div class="oss-meta-item">
                    <span class="oss-meta-label">Sale Type</span>
                    <span class="oss-meta-value">{{ $sale->type?->label() ?? '—' }}</span>
                </div>
                @if($sale->customer)
                <div class="oss-meta-item">
                    <span class="oss-meta-label">Customer</span>
                    <span style="display:flex;align-items:baseline;gap:8px;flex-wrap:wrap">
                        <span class="oss-meta-value">{{ $sale->customer->name }}</span>
                        @if($sale->customer->phone)<span class="oss-meta-sub">{{ $sale->customer->phone }}</span>@endif
                    </span>
                </div>
                @endif
                @if($sale->has_price_override)
                <div class="oss-meta-item full">
                    <span class="oss-meta-label" style="color:var(--amber)">Price Override</span>
                    <span class="oss-meta-value" style="font-weight:500;font-size:13px;color:var(--text-dim)">
                        {{ $sale->price_override_reason ?? 'No reason given' }}
                        @if($sale->priceOverrideApprovedBy) — approved by {{ $sale->priceOverrideApprovedBy->name }} @endif
                    </span>
                </div>
                @endif
                @if($sale->notes)
                <div class="oss-meta-item full">
                    <span class="oss-meta-label">Notes</span>
                    <span class="oss-meta-value" style="font-weight:500;font-size:13px;color:var(--text-dim)">{{ $sale->notes }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="oss-card">
        <div class="oss-card-head">
            <span class="oss-card-title">Items Sold</span>
            <span style="font-size:13px;font-weight:700;font-family:var(--mono);
                         background:var(--accent-dim);color:var(--accent);padding:3px 9px;border-radius:6px">
                {{ $groupedItems->count() }} line{{ $groupedItems->count() === 1 ? '' : 's' }}
            </span>
        </div>
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
            <table class="oss-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th style="text-align:right">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedItems as $line)
                    <tr>
                        <td>
                            <span class="oss-prod-name">{{ $line['product_name'] }}</span>
                            @if($line['price_modified'])
                            <span style="display:inline-flex;align-items:center;gap:3px;margin-left:6px;padding:1px 6px;
                                         border-radius:5px;font-size:10px;font-weight:700;background:var(--amber-dim);color:var(--amber)">Modified</span>
                            @endif
                        </td>
                        <td>{{ $line['quantity'] }} {{ $line['is_full_box'] ? 'box' . ($line['quantity'] === 1 ? '' : 'es') : 'item' . ($line['quantity'] === 1 ? '' : 's') }}</td>
                        <td><span class="oss-val">{{ number_format($line['unit_price']) }}</span> <span style="font-size:11px;color:var(--text-dim)">RWF{{ $line['is_full_box'] ? '/box' : '' }}</span></td>
                        <td style="text-align:right"><span class="oss-val">{{ number_format($line['line_total']) }}</span> <span style="font-size:11px;color:var(--text-dim)">RWF</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payment breakdown --}}
    <div class="oss-card">
        <div class="oss-card-head">
            <span class="oss-card-title">Payment Breakdown</span>
        </div>
        <div class="oss-card-body">
            @forelse($sale->payments as $payment)
            <div class="oss-pay-row">
                <span class="oss-pay-method">{{ $payment->payment_method?->label() ?? '—' }}</span>
                <span class="oss-pay-amount">{{ number_format($payment->amount) }} RWF</span>
            </div>
            @empty
            <div style="padding:12px 0;font-size:13px;color:var(--text-dim)">No payment records found for this sale.</div>
            @endforelse

            @if($sale->discount > 0)
            <div class="oss-pay-row">
                <span class="oss-pay-method" style="color:var(--red)">Discount</span>
                <span class="oss-pay-amount" style="color:var(--red)">−{{ number_format($sale->discount) }} RWF</span>
            </div>
            @endif

            @if($sale->has_credit && $sale->credit_amount > 0)
            <div class="oss-pay-row">
                <span class="oss-pay-method" style="color:var(--amber)">Credit</span>
                <span class="oss-pay-amount" style="color:var(--amber)">{{ number_format($sale->credit_amount) }} RWF</span>
            </div>
            @endif

            <div class="oss-total-row">
                <span class="oss-total-label">Total</span>
                <span class="oss-total-value">{{ number_format($sale->total) }} RWF</span>
            </div>
        </div>
    </div>

</div>
</x-app-layout>
