@php use App\Enums\TransferStatus; @endphp
<div>
<style>
/* ── Review Transfer ─────────────────────────────────── */
.rt-wrap { display:flex; flex-direction:column; gap:16px; }

/* Alerts */
.rt-alert {
    display:flex; align-items:flex-start; gap:10px;
    padding:10px 14px; border-radius:10px; border:1px solid; font-size:12px; line-height:1.5;
}
.rt-alert.success { background:var(--green-dim);  border-color:rgba(16,185,129,.25); color:var(--green); }
.rt-alert.error   { background:var(--red-dim);    border-color:rgba(225,29,72,.25);  color:var(--red); }
.rt-alert.info    { background:var(--accent-dim); border-color:rgba(99,102,241,.25); color:var(--accent); }

/* Cards */
.rt-card { background:#fff; border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.rt-card-head {
    padding:10px 14px; border-bottom:1px solid var(--border);
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    background:var(--surface2);
}
.rt-card-title { font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:var(--text-dim); }
.rt-card-body  { padding:16px; }

/* Transfer header */
.rt-num   { font-size:17px; font-weight:800; color:var(--text); font-family:var(--mono); letter-spacing:-.3px; }
.rt-pill  {
    display:inline-flex; align-items:center; gap:5px;
    padding:2px 8px; border-radius:999px; font-size:10px; font-weight:700; letter-spacing:.3px;
}
.rt-pill.pending  { background:var(--amber-dim); color:var(--amber); border:1px solid rgba(217,119,6,.25); }
.rt-pill.approved { background:var(--green-dim);  color:var(--green);  border:1px solid rgba(16,185,129,.25); }
.rt-pill.rejected { background:var(--red-dim);    color:var(--red);    border:1px solid rgba(225,29,72,.25); }

/* Route strip */
.rt-route {
    display:flex; align-items:center; gap:0;
    background:var(--surface2); border-radius:10px;
    padding:12px 14px; border:1px solid var(--border);
}
.rt-route-node  { flex:1; }
.rt-route-label { font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); }
.rt-route-name  { font-size:13px; font-weight:700; color:var(--text); margin-top:2px; }
.rt-route-arrow {
    display:flex; align-items:center; justify-content:center;
    width:28px; height:28px; border-radius:50%;
    background:var(--accent-dim); color:var(--accent); flex-shrink:0;
}

/* Meta grid */
.rt-meta-grid  { display:grid; grid-template-columns:repeat(auto-fill, minmax(160px,1fr)); gap:12px; }
.rt-meta-item  { display:flex; flex-direction:column; gap:3px; }
.rt-meta-label { font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); }
.rt-meta-value { font-size:13px; font-weight:600; color:var(--text); }
.rt-meta-sub   { font-size:11px; color:var(--text-dim); }

/* Notes box */
.rt-notes {
    padding:10px 12px; background:var(--surface2);
    border-radius:8px; border:1px solid var(--border);
    font-size:12px; color:var(--text-dim); line-height:1.6;
}

/* Product rows */
.rt-product-row {
    border:1px solid var(--border); border-radius:10px; overflow:hidden;
    transition:border-color .15s;
}
.rt-product-row.has-warning { border-color:var(--red); background:var(--red-dim); }
.rt-product-head {
    display:flex; align-items:center; justify-content:space-between;
    padding:10px 14px; background:var(--surface2); border-bottom:1px solid var(--border);
}
.rt-product-name { font-size:13px; font-weight:700; color:var(--text); }
.rt-product-body {
    padding:14px; display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; align-items:start;
}
@media(max-width:640px) { .rt-product-body { grid-template-columns:1fr; } }

/* Stats inside product row */
.rt-stat       { display:flex; flex-direction:column; gap:3px; }
.rt-stat-label { font-size:10px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:var(--text-dim); }
.rt-stat-value { font-size:22px; font-weight:800; color:var(--text); font-family:var(--mono); line-height:1.1; }
.rt-stat-sub   { font-size:11px; color:var(--text-dim); }
.rt-stat-value.ok  { color:var(--green); }
.rt-stat-value.bad { color:var(--red); }

/* Input */
.rt-input {
    width:100%; padding:8px 12px;
    background:#fff; color:var(--text);
    border:1.5px solid var(--border); border-radius:8px;
    font-size:14px; font-weight:700; font-family:var(--mono);
    transition:border-color .15s, box-shadow .15s; outline:none;
}
.rt-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(99,102,241,.12); }
.rt-input.rt-input-error { border-color:var(--red); box-shadow:0 0 0 3px rgba(225,29,72,.1); }

/* Stock bar */
.rt-stock-bar-wrap { height:5px; border-radius:999px; background:var(--surface2); overflow:hidden; margin-top:6px; }
.rt-stock-bar      { height:100%; border-radius:999px; transition:width .4s; }
.rt-stock-bar.ok   { background:var(--green); }
.rt-stock-bar.bad  { background:var(--red); }

/* Warning chip */
.rt-warn {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 10px; border-radius:7px; margin-top:10px;
    background:var(--red-dim); border:1px solid rgba(225,29,72,.25);
    color:var(--red); font-size:11px; font-weight:600;
}

/* Action footer */
.rt-action-bar {
    display:flex; align-items:center; justify-content:flex-end;
    gap:10px; padding:12px 14px;
    background:var(--surface2); border-top:1px solid var(--border);
    flex-wrap:wrap;
}

/* Buttons */
.rt-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:7px;
    padding:8px 18px; border-radius:9px;
    font-size:12px; font-weight:700;
    border:none; cursor:pointer; transition:all .15s; white-space:nowrap;
}
.rt-btn:active  { transform:scale(.97); }
.rt-btn:disabled { opacity:.45; cursor:not-allowed; transform:none; }
.rt-btn-approve  { background:var(--accent); color:#fff; }
.rt-btn-approve:hover:not(:disabled) { opacity:.88; }
.rt-btn-reject   { background:var(--red-dim); color:var(--red); border:1px solid rgba(225,29,72,.3); }
.rt-btn-reject:hover:not(:disabled)  { background:var(--red); color:#fff; }
.rt-btn-secondary { background:var(--surface2); color:var(--text-dim); border:1px solid var(--border); }
.rt-btn-secondary:hover { background:var(--border); }
.rt-btn-danger   { background:var(--red); color:#fff; }
.rt-btn-danger:hover:not(:disabled) { opacity:.88; }

/* Status banner */
.rt-status-banner {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    gap:10px; padding:24px; text-align:center; border-radius:10px; border:1px solid;
}
.rt-status-banner.approved { background:var(--green-dim); border-color:rgba(16,185,129,.3); color:var(--green); }
.rt-status-banner.rejected { background:var(--red-dim);   border-color:rgba(225,29,72,.3);  color:var(--red); }
.rt-status-banner-icon  { width:44px; height:44px; }
.rt-status-banner-title { font-size:15px; font-weight:800; }
.rt-status-banner-sub   { font-size:12px; opacity:.8; }

/* Pack CTA */
.rt-pack-cta {
    display:inline-flex; align-items:center; gap:7px;
    padding:8px 18px; border-radius:9px;
    background:var(--accent); color:#fff;
    font-size:12px; font-weight:700; text-decoration:none; transition:opacity .15s;
}
.rt-pack-cta:hover { opacity:.88; }

/* Modal */
.rt-modal-overlay {
    position:fixed; inset:0; z-index:50; background:rgba(10,14,26,.6);
    backdrop-filter:blur(4px); display:flex; align-items:center;
    justify-content:center; padding:20px; animation:rtFadeIn .15s ease;
}
@keyframes rtFadeIn { from { opacity:0 } to { opacity:1 } }
.rt-modal {
    background:#fff; border:1px solid var(--border);
    border-radius:14px; width:100%; max-width:480px;
    box-shadow:0 24px 60px rgba(0,0,0,.15);
    animation:rtSlideUp .2s ease;
}
@keyframes rtSlideUp { from { opacity:0; transform:translateY(14px) } to { opacity:1; transform:translateY(0) } }
.rt-modal-head {
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 18px; border-bottom:1px solid var(--border);
}
.rt-modal-title { font-size:14px; font-weight:700; color:var(--text); }
.rt-modal-close {
    width:28px; height:28px; border-radius:7px; background:var(--surface2);
    border:1px solid var(--border); display:flex; align-items:center;
    justify-content:center; cursor:pointer; color:var(--text-dim); transition:background .15s;
}
.rt-modal-close:hover { background:var(--border); }
.rt-modal-body { padding:18px; display:flex; flex-direction:column; gap:14px; }
.rt-modal-foot { display:flex; align-items:center; justify-content:flex-end; gap:8px; padding:14px 18px; border-top:1px solid var(--border); }

/* Field */
.rt-field-label { font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:var(--text-dim); margin-bottom:5px; display:block; }
.rt-field-error { font-size:11px; color:var(--red); margin-top:4px; font-weight:600; }
.rt-textarea {
    width:100%; padding:10px 12px; background:#fff; color:var(--text);
    border:1.5px solid var(--border); border-radius:8px;
    font-size:13px; resize:vertical; min-height:100px; outline:none; transition:border-color .15s;
}
.rt-textarea:focus { border-color:var(--accent); }

.rt-divider { border:none; border-top:1px solid var(--border); margin:0; }

/* Responsive */
@media(max-width:768px) {
    .rt-card-head { flex-wrap:wrap; }
    .rt-product-head { flex-wrap:wrap; gap:6px; }
    .rt-route { flex-direction:column; gap:10px; }
    .rt-route-node:last-child { text-align:left; }
    .rt-route-arrow { transform:rotate(90deg); }
    .rt-meta-grid { grid-template-columns:1fr 1fr; }
}
@media(max-width:640px) {
    .rt-action-bar { flex-direction:column; }
    .rt-btn { width:100%; }
    .rt-meta-grid { grid-template-columns:1fr; }
    .rt-stat-value { font-size:18px; }
}
</style>

<div class="rt-wrap">

    {{-- Flash messages --}}
    @if(session()->has('success'))
    <div class="rt-alert success">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session()->has('error'))
    <div class="rt-alert error">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Transfer header card --}}
    <div class="rt-card">
        <div class="rt-card-head">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span class="rt-num">{{ $transfer->transfer_number }}</span>
                @php
                    $statusClass = match($transfer->status) {
                        TransferStatus::PENDING  => 'pending',
                        TransferStatus::APPROVED => 'approved',
                        TransferStatus::REJECTED => 'rejected',
                        default                  => 'pending',
                    };
                @endphp
                <span class="rt-pill {{ $statusClass }}">
                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor"></span>
                    {{ $transfer->status->label() }}
                </span>
            </div>
            <span style="font-size:11px;color:var(--text-dim);">{{ $transfer->requested_at?->format('d M Y · H:i') }}</span>
        </div>

        <div class="rt-card-body" style="display:flex;flex-direction:column;gap:14px">

            {{-- Route strip --}}
            <div class="rt-route">
                <div class="rt-route-node">
                    <div class="rt-route-label">From Warehouse</div>
                    <div class="rt-route-name">{{ $transfer->fromWarehouse->name }}</div>
                </div>
                <div class="rt-route-arrow">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </div>
                <div class="rt-route-node" style="text-align:right">
                    <div class="rt-route-label">To Shop</div>
                    <div class="rt-route-name">{{ $transfer->toShop->name }}</div>
                </div>
            </div>

            {{-- Meta grid --}}
            <div class="rt-meta-grid">
                <div class="rt-meta-item">
                    <span class="rt-meta-label">Requested By</span>
                    <span class="rt-meta-value">{{ $transfer->requestedBy->name }}</span>
                </div>
                <div class="rt-meta-item">
                    <span class="rt-meta-label">Products</span>
                    <span class="rt-meta-value">{{ count($items) }} {{ count($items) === 1 ? 'product' : 'products' }}</span>
                </div>
                @if($transfer->transporter)
                <div class="rt-meta-item">
                    <span class="rt-meta-label">Transporter</span>
                    <span class="rt-meta-value">{{ $transfer->transporter->name }}</span>
                    @if($transfer->transporter->vehicle_number)
                        <span class="rt-meta-sub">{{ $transfer->transporter->vehicle_number }}</span>
                    @endif
                </div>
                @endif
                @if($transfer->reviewed_by)
                <div class="rt-meta-item">
                    <span class="rt-meta-label">Reviewed By</span>
                    <span class="rt-meta-value">{{ $transfer->reviewedBy?->name ?? '—' }}</span>
                </div>
                @endif
            </div>

            {{-- Notes --}}
            @if($transfer->notes && $transfer->status === TransferStatus::PENDING)
            <div>
                <div class="rt-meta-label" style="margin-bottom:5px">Shop Notes</div>
                <div class="rt-notes">{{ $transfer->notes }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Requested products card --}}
    <div class="rt-card">
        <div class="rt-card-head">
            <span class="rt-card-title">Requested Products</span>
            @if($transfer->status === TransferStatus::PENDING)
            <span style="font-size:11px;color:var(--text-dim);">You may adjust quantities before approving</span>
            @endif
        </div>

        <div class="rt-card-body" style="display:flex;flex-direction:column;gap:12px">
            @foreach($items as $index => $item)
                @php
                    $stock          = $stockLevels[$item['product_id']] ?? null;
                    $availableBoxes = $stock ? (int) $stock['total_boxes'] : 0;
                    $requestedBoxes = (int) ($item['boxes_requested'] ?? 0);
                    $exceedsStock   = $requestedBoxes > $availableBoxes && $requestedBoxes > 0;
                    $totalItems     = $requestedBoxes * (int) $item['items_per_box'];
                    $stockPct       = ($availableBoxes > 0 && $requestedBoxes > 0)
                                        ? min(100, (int) round($requestedBoxes / $availableBoxes * 100))
                                        : 0;
                @endphp
                <div class="rt-product-row {{ $exceedsStock ? 'has-warning' : '' }}">
                    <div class="rt-product-head">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:28px;height:28px;border-radius:7px;background:var(--accent-dim);
                                        display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <svg width="13" height="13" fill="none" stroke="var(--accent)" viewBox="0 0 24 24" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <span class="rt-product-name">{{ $item['product_name'] }}</span>
                        </div>
                        <span style="font-size:11px;color:var(--text-dim);background:var(--surface2);
                                     padding:2px 8px;border-radius:5px;font-weight:600">
                            {{ $item['items_per_box'] }} items/box
                        </span>
                    </div>

                    <div class="rt-product-body">
                        {{-- Boxes Requested --}}
                        <div class="rt-stat">
                            <label class="rt-stat-label">Boxes Requested</label>
                            @if($transfer->status === TransferStatus::PENDING)
                                <input type="number"
                                       value="{{ $item['boxes_requested'] }}"
                                       min="1"
                                       @change="$wire.set('items.{{ $index }}.boxes_requested', parseInt($event.target.value) || 1)"
                                       class="rt-input {{ $exceedsStock ? 'rt-input-error' : '' }}">
                                @error("items.{$index}.boxes_requested")
                                    <span class="rt-field-error">{{ $message }}</span>
                                @enderror
                            @else
                                <span class="rt-stat-value">{{ number_format($requestedBoxes) }}</span>
                                <span class="rt-stat-sub">boxes</span>
                            @endif
                        </div>

                        {{-- Available in Warehouse --}}
                        <div class="rt-stat">
                            <span class="rt-stat-label">Available in Warehouse</span>
                            <span class="rt-stat-value {{ $exceedsStock ? 'bad' : 'ok' }}">
                                {{ number_format($availableBoxes) }}
                            </span>
                            <span class="rt-stat-sub">
                                @if($stock)
                                    {{ $stock['full_boxes'] }} full · {{ $stock['partial_boxes'] }} partial
                                @else
                                    boxes available
                                @endif
                            </span>
                            @if($availableBoxes > 0)
                            <div class="rt-stock-bar-wrap">
                                <div class="rt-stock-bar {{ $exceedsStock ? 'bad' : 'ok' }}" style="width:{{ min(100, $stockPct) }}%"></div>
                            </div>
                            @endif
                        </div>

                        {{-- Total Items --}}
                        <div class="rt-stat">
                            <span class="rt-stat-label">Total Items</span>
                            <span class="rt-stat-value">{{ number_format($totalItems) }}</span>
                            <span class="rt-stat-sub">items total</span>
                        </div>
                    </div>

                    @if($exceedsStock && $requestedBoxes > 0)
                    <div style="padding:0 14px 12px">
                        <div class="rt-warn">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Requested {{ $requestedBoxes }} boxes but only {{ $availableBoxes }} available. Reduce quantity to approve.
                        </div>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Action bar (pending) --}}
        @if($transfer->status === TransferStatus::PENDING)
            <hr class="rt-divider">
            <div class="rt-action-bar">
                <button type="button" @click="$wire.openRejectModal()" class="rt-btn rt-btn-reject">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reject Request
                </button>
                <button type="button"
                        @click="$wire.approve()"
                        class="rt-btn rt-btn-approve">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Approve Transfer</span>
                </button>
            </div>

        {{-- Approved state --}}
        @elseif($transfer->status === TransferStatus::APPROVED)
            <div style="padding:16px">
                <div class="rt-status-banner approved">
                    <svg class="rt-status-banner-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="rt-status-banner-title">Transfer Approved</div>
                    <div class="rt-status-banner-sub">This transfer is approved and ready for packing.</div>
                    <a href="{{ route('warehouse.transfers.pack', $transfer) }}" class="rt-pack-cta">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        Pack Transfer
                    </a>
                </div>
            </div>

        {{-- Rejected state --}}
        @elseif($transfer->status === TransferStatus::REJECTED)
            <div style="padding:16px">
                <div class="rt-status-banner rejected">
                    <svg class="rt-status-banner-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="rt-status-banner-title">Transfer Rejected</div>
                    @if($transfer->notes)
                        <div class="rt-status-banner-sub">Reason: {{ $transfer->notes }}</div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Reject modal --}}
    @if($showRejectModal)
    <div class="rt-modal-overlay" x-data="{ show: @entangle('showRejectModal') }">
        <div class="rt-modal" @click.stop>
            <div class="rt-modal-head">
                <span class="rt-modal-title">Reject Transfer Request</span>
                <button type="button" class="rt-modal-close" @click="$wire.closeRejectModal()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="rt-modal-body">
                <div style="display:flex;align-items:flex-start;gap:10px;padding:12px;
                            background:var(--red-dim);border-radius:8px;border:1px solid rgba(225,29,72,.2)">
                    <svg width="14" height="14" fill="none" stroke="var(--red)" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span style="font-size:12px;color:var(--red);line-height:1.5">
                        This will reject the transfer request. The shop will need to submit a new request.
                    </span>
                </div>
                <div>
                    <label class="rt-field-label">
                        Reason for Rejection <span style="color:var(--red)">*</span>
                    </label>
                    <textarea wire:model="rejectReason"
                              class="rt-textarea"
                              placeholder="Explain why this transfer cannot be fulfilled…"></textarea>
                    @error('rejectReason')
                        <span class="rt-field-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="rt-modal-foot">
                <button type="button" class="rt-btn rt-btn-secondary" @click="$wire.closeRejectModal()">Cancel</button>
                <button type="button" wire:click="reject" class="rt-btn rt-btn-danger"
                        wire:loading.attr="disabled" wire:target="reject">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" wire:loading.remove wire:target="reject"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span wire:loading.remove wire:target="reject">Reject Transfer</span>
                    <span wire:loading wire:target="reject">Rejecting…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
</div>
