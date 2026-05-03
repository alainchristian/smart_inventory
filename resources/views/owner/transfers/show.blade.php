@php use App\Enums\TransferStatus; @endphp
<x-app-layout>
<style>
/* ── Owner Transfer Show ─────────────────────── ots- */
.ots-wrap { display:flex; flex-direction:column; gap:16px; }

/* Back bar */
.ots-back-bar {
    display:flex; align-items:center; justify-content:space-between;
    gap:10px; flex-wrap:wrap;
}
.ots-back {
    display:inline-flex; align-items:center; gap:6px;
    font-size:12px; font-weight:600; color:var(--text-dim);
    text-decoration:none; transition:color .15s;
}
.ots-back:hover { color:var(--accent); }

/* Cards */
.ots-card { background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.ots-card-head {
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    padding:10px 14px; border-bottom:1px solid var(--border);
    background:var(--surface2); flex-wrap:wrap;
}
.ots-card-title { font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:var(--text-dim); }
.ots-card-body  { padding:16px; }

/* Transfer header */
.ots-num  { font-size:17px; font-weight:800; color:var(--text); font-family:var(--mono); letter-spacing:-.3px; }
.ots-pill {
    display:inline-flex; align-items:center; gap:5px;
    padding:2px 9px; border-radius:999px; font-size:10px; font-weight:700; letter-spacing:.4px; text-transform:uppercase;
    border:1px solid;
}

/* Route strip */
.ots-route {
    display:flex; align-items:center; gap:0;
    background:var(--surface2); border-radius:10px;
    padding:12px 14px; border:1px solid var(--border);
}
.ots-route-node  { flex:1; }
.ots-route-label { font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); }
.ots-route-name  { font-size:13px; font-weight:700; color:var(--text); margin-top:2px; }
.ots-route-arrow {
    width:28px; height:28px; border-radius:50%;
    background:var(--accent-dim); color:var(--accent);
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}

/* Meta grid */
.ots-meta-grid  { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:12px; }
.ots-meta-item  { display:flex; flex-direction:column; gap:3px; }
.ots-meta-label { font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); }
.ots-meta-value { font-size:13px; font-weight:600; color:var(--text); }
.ots-meta-sub   { font-size:11px; color:var(--text-dim); }

/* Items table */
.ots-table { width:100%; border-collapse:collapse; min-width:500px; }
.ots-table thead th {
    padding:8px 12px; font-size:10px; font-weight:700; letter-spacing:.6px;
    text-transform:uppercase; color:var(--text-dim);
    border-bottom:1px solid var(--border); text-align:left; background:var(--surface2);
}
.ots-table tbody tr { border-bottom:1px solid var(--border); }
.ots-table tbody tr:last-child { border-bottom:none; }
.ots-table tbody tr:hover { background:var(--surface2); }
.ots-table tbody td { padding:10px 12px; font-size:12px; color:var(--text); vertical-align:middle; }
.ots-prod-name  { font-weight:600; }
.ots-val        { font-family:var(--mono); font-weight:700; font-size:13px; }
.ots-prog-wrap  { height:4px; background:var(--surface2); border-radius:4px; overflow:hidden; margin-top:4px; min-width:60px; }
.ots-prog-bar   { height:100%; border-radius:4px; transition:width .3s; }

/* Status badge pill colors */
.ots-pill.pending    { background:var(--amber-dim);  color:var(--amber);  border-color:rgba(217,119,6,.25); }
.ots-pill.approved   { background:var(--accent-dim); color:var(--accent); border-color:rgba(99,102,241,.25); }
.ots-pill.in_transit { background:rgba(139,92,246,.1); color:#8b5cf6;     border-color:rgba(139,92,246,.25); }
.ots-pill.delivered  { background:rgba(14,165,233,.1); color:#0ea5e9;     border-color:rgba(14,165,233,.25); }
.ots-pill.received   { background:var(--green-dim);  color:var(--green);  border-color:rgba(16,185,129,.25); }
.ots-pill.rejected   { background:var(--red-dim);    color:var(--red);    border-color:rgba(225,29,72,.25); }
.ots-pill.cancelled  { background:var(--surface2);   color:var(--text-dim); border-color:var(--border); }

/* Action button */
.ots-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 14px; border-radius:8px; font-size:12px; font-weight:600;
    text-decoration:none; border:1px solid var(--border);
    background:var(--surface2); color:var(--text); transition:all .15s;
}
.ots-btn:hover { border-color:var(--accent); color:var(--accent); }

/* Discrepancy banner */
.ots-discrepancy {
    display:flex; align-items:flex-start; gap:10px;
    padding:10px 14px; border-radius:10px; font-size:12px;
    background:var(--red-dim); border:1px solid rgba(225,29,72,.25); color:var(--red); line-height:1.5;
}

/* Responsive */
@media(max-width:640px) {
    .ots-route { flex-direction:column; gap:10px; }
    .ots-route-node:last-child { text-align:left; }
    .ots-route-arrow { transform:rotate(90deg); }
    .ots-meta-grid { grid-template-columns:1fr 1fr; }
}
@media(max-width:480px) {
    .ots-meta-grid { grid-template-columns:1fr; }
}
</style>

<div class="ots-wrap">

    {{-- Back bar --}}
    <div class="ots-back-bar">
        <a href="{{ route('owner.transfers.index') }}" class="ots-back">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Transfers
        </a>
        @if(in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED, TransferStatus::RECEIVED]))
        <a href="{{ route('owner.transfers.delivery-note', $transfer) }}" target="_blank" class="ots-btn">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Delivery Note
        </a>
        @endif
    </div>

    {{-- Discrepancy warning --}}
    @if($transfer->has_discrepancy)
    <div class="ots-discrepancy">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span>This transfer has a discrepancy — some boxes were missing or damaged on delivery.</span>
    </div>
    @endif

    {{-- Transfer header --}}
    @php
        $statusKey = str_replace('_', '_', $transfer->status->value);
        $pillClass = match($transfer->status->value) {
            'pending'    => 'pending',
            'approved'   => 'approved',
            'in_transit' => 'in_transit',
            'delivered'  => 'delivered',
            'received'   => 'received',
            'rejected'   => 'rejected',
            default      => 'cancelled',
        };
    @endphp
    <div class="ots-card">
        <div class="ots-card-head">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span class="ots-num">{{ $transfer->transfer_number }}</span>
                <span class="ots-pill {{ $pillClass }}">
                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor"></span>
                    {{ $transfer->status->label() }}
                </span>
            </div>
            <span style="font-size:11px;color:var(--text-dim)">{{ $transfer->requested_at?->format('d M Y · H:i') }}</span>
        </div>
        <div class="ots-card-body" style="display:flex;flex-direction:column;gap:14px">

            {{-- Route --}}
            <div class="ots-route">
                <div class="ots-route-node">
                    <div class="ots-route-label">From Warehouse</div>
                    <div class="ots-route-name">{{ $transfer->fromWarehouse->name ?? '—' }}</div>
                </div>
                <div class="ots-route-arrow">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </div>
                <div class="ots-route-node" style="text-align:right">
                    <div class="ots-route-label">To Shop</div>
                    <div class="ots-route-name">{{ $transfer->toShop->name ?? '—' }}</div>
                </div>
            </div>

            {{-- Meta --}}
            <div class="ots-meta-grid">
                <div class="ots-meta-item">
                    <span class="ots-meta-label">Requested By</span>
                    <span class="ots-meta-value">{{ $transfer->requestedBy->name ?? '—' }}</span>
                </div>
                <div class="ots-meta-item">
                    <span class="ots-meta-label">Products</span>
                    <span class="ots-meta-value">{{ $transfer->items->count() }}</span>
                </div>
                @if($transfer->transporter)
                <div class="ots-meta-item">
                    <span class="ots-meta-label">Transporter</span>
                    <span class="ots-meta-value">{{ $transfer->transporter->name }}</span>
                    @if($transfer->transporter->vehicle_number)
                        <span class="ots-meta-sub">{{ $transfer->transporter->vehicle_number }}</span>
                    @endif
                </div>
                @endif
                @if($transfer->shipped_at)
                <div class="ots-meta-item">
                    <span class="ots-meta-label">Shipped</span>
                    <span class="ots-meta-value">{{ $transfer->shipped_at->format('d M Y') }}</span>
                    <span class="ots-meta-sub">{{ $transfer->shipped_at->format('H:i') }}</span>
                </div>
                @endif
                @if($transfer->received_at)
                <div class="ots-meta-item">
                    <span class="ots-meta-label">Received</span>
                    <span class="ots-meta-value">{{ $transfer->received_at->format('d M Y') }}</span>
                    <span class="ots-meta-sub">{{ $transfer->received_at->format('H:i') }}</span>
                </div>
                @endif
                @if($transfer->notes)
                <div class="ots-meta-item" style="grid-column:1/-1">
                    <span class="ots-meta-label">Notes</span>
                    <span class="ots-meta-value" style="font-weight:400;font-size:12px;color:var(--text-dim)">{{ $transfer->notes }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Items --}}
    @php
        $hasAnyDiscrepancy = false;
        foreach ($transfer->items as $item) {
            $ipb = max(1, (int) ($item->product->items_per_box ?? 1));
            $bxShipped  = $item->quantity_shipped  ? (int) round($item->quantity_shipped  / $ipb) : 0;
            $bxReceived = $item->quantity_received ? (int) round($item->quantity_received / $ipb) : 0;
            if ($bxShipped > 0 && $bxReceived < $bxShipped) { $hasAnyDiscrepancy = true; break; }
        }
    @endphp
    <div class="ots-card">
        <div class="ots-card-head">
            <span class="ots-card-title">Transfer Items</span>
            @php $totalBoxes = $transfer->items->sum(fn($i) => (int) $i->quantity_requested); @endphp
            <div style="display:flex;align-items:center;gap:6px">
                <span style="font-size:11px;font-weight:700;font-family:var(--mono);
                             background:var(--accent-dim);color:var(--accent);padding:3px 9px;border-radius:6px">
                    {{ $totalBoxes }} box{{ $totalBoxes === 1 ? '' : 'es' }} requested
                </span>
                @if($hasAnyDiscrepancy)
                <span style="font-size:11px;font-weight:700;background:var(--red-dim);color:var(--red);padding:3px 9px;border-radius:6px">
                    Discrepancy
                </span>
                @endif
            </div>
        </div>
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
            <table class="ots-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Requested</th>
                        <th>Shipped</th>
                        <th>Received</th>
                        @if($hasAnyDiscrepancy)<th>Discrepancy</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($transfer->items as $item)
                    @php
                        $ipb        = max(1, (int) ($item->product->items_per_box ?? 1));
                        $bxReq      = (int) $item->quantity_requested;
                        $bxShipped  = $item->quantity_shipped  ? (int) round($item->quantity_shipped  / $ipb) : 0;
                        $bxReceived = $item->quantity_received ? (int) round($item->quantity_received / $ipb) : 0;
                        $shipPct    = $bxReq    > 0 ? min(100, round($bxShipped  / $bxReq    * 100)) : 0;
                        $recvPct    = $bxShipped > 0 ? min(100, round($bxReceived / $bxShipped * 100)) : 0;
                        $missing    = $bxShipped > 0 ? max(0, $bxShipped - $bxReceived) : 0;
                    @endphp
                    <tr>
                        <td>
                            <span class="ots-prod-name">{{ $item->product->name ?? '—' }}</span>
                            <div style="font-size:10px;color:var(--text-dim);margin-top:1px;font-family:var(--mono)">{{ $item->product->barcode ?? '' }}</div>
                        </td>
                        <td>
                            <span class="ots-val">{{ $bxReq }}</span>
                            <span style="font-size:11px;color:var(--text-dim)"> box{{ $bxReq === 1 ? '' : 'es' }}</span>
                        </td>
                        <td>
                            <span class="ots-val" style="{{ $bxShipped > 0 && $bxShipped < $bxReq ? 'color:var(--amber)' : ($bxShipped >= $bxReq && $bxShipped > 0 ? 'color:var(--green)' : '') }}">
                                {{ $bxShipped ?: '—' }}
                            </span>
                            @if($bxShipped > 0)
                            <span style="font-size:11px;color:var(--text-dim)"> box{{ $bxShipped === 1 ? '' : 'es' }}</span>
                            <div class="ots-prog-wrap">
                                <div class="ots-prog-bar" style="width:{{ $shipPct }}%;background:{{ $bxShipped >= $bxReq ? 'var(--green)' : 'var(--amber)' }}"></div>
                            </div>
                            @endif
                        </td>
                        <td>
                            <span class="ots-val" style="{{ $missing > 0 ? 'color:var(--red)' : ($bxReceived > 0 ? 'color:var(--green)' : '') }}">
                                {{ $bxReceived ?: '—' }}
                            </span>
                            @if($bxReceived > 0)
                            <span style="font-size:11px;color:var(--text-dim)"> box{{ $bxReceived === 1 ? '' : 'es' }}</span>
                            <div class="ots-prog-wrap">
                                <div class="ots-prog-bar" style="width:{{ $recvPct }}%;background:{{ $missing > 0 ? 'var(--red)' : 'var(--green)' }}"></div>
                            </div>
                            @endif
                        </td>
                        @if($hasAnyDiscrepancy)
                        <td>
                            @if($missing > 0)
                                <span style="padding:2px 8px;border-radius:5px;font-size:11px;font-weight:700;
                                             background:var(--red-dim);color:var(--red)">
                                    −{{ $missing }} box{{ $missing === 1 ? '' : 'es' }}
                                </span>
                            @elseif($bxReceived > 0)
                                <svg width="12" height="12" fill="none" stroke="var(--green)" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span style="color:var(--text-dim);font-size:11px">—</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Packed boxes (if shipped or later) --}}
    @if(in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED, TransferStatus::RECEIVED]) && $transfer->boxes->count() > 0)
    <div class="ots-card">
        <div class="ots-card-head">
            <span class="ots-card-title">Boxes</span>
            <span style="font-size:11px;font-weight:700;font-family:var(--mono);
                         background:var(--accent-dim);color:var(--accent);padding:3px 9px;border-radius:6px">
                {{ $transfer->boxes->count() }} box{{ $transfer->boxes->count() === 1 ? '' : 'es' }}
            </span>
        </div>
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
            <table class="ots-table">
                <thead>
                    <tr>
                        <th>Box Code</th>
                        <th>Product</th>
                        <th>Items</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transfer->boxes->sortBy('is_received') as $tb)
                    <tr>
                        <td><span style="font-family:var(--mono);font-weight:700;font-size:12px;
                                         color:var(--accent);background:var(--accent-dim);
                                         padding:2px 7px;border-radius:5px">{{ $tb->box->box_code }}</span></td>
                        <td>{{ $tb->box->product->name ?? '—' }}</td>
                        <td style="font-family:var(--mono);font-size:12px">{{ number_format($tb->box->items_remaining) }}</td>
                        <td>
                            @if($tb->is_damaged)
                                <span style="padding:2px 7px;border-radius:5px;font-size:10px;font-weight:700;background:var(--red-dim);color:var(--red)">Damaged</span>
                            @elseif($tb->is_received)
                                <span style="padding:2px 7px;border-radius:5px;font-size:10px;font-weight:700;background:var(--green-dim);color:var(--green)">Received</span>
                            @else
                                <span style="padding:2px 7px;border-radius:5px;font-size:10px;font-weight:600;background:var(--amber-dim);color:var(--amber)">In Transit</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
</x-app-layout>
