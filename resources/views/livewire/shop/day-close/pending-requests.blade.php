<div class="pr-card" style="font-family:var(--font)">
<style>
.pr-card  { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0; }
.pr-head  { padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px; }
.pr-title { font-size:13px;font-weight:700;color:var(--text);margin:0;display:flex;align-items:center;gap:8px; }
.pr-count { font-size:11px;font-weight:700;padding:1px 7px;border-radius:20px;font-family:var(--mono);background:var(--amber-dim);color:var(--amber); }
.pr-sub   { font-size:12px;color:var(--text-dim);margin-top:2px; }
.pr-note  { margin:12px 20px 0;padding:8px 12px;border-radius:var(--rsm);border-left:3px solid var(--amber);font-size:12px;color:var(--text-sub);background:var(--amber-dim); }

.pr-row      { padding:12px 20px;border-bottom:1px solid var(--border);display:flex;gap:12px;align-items:flex-start;transition:background var(--tr); }
.pr-row:last-child { border-bottom:none; }
.pr-row:hover { background:var(--surface2); }
.pr-main     { flex:1;min-width:0; }
.pr-reason   { font-size:13px;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.pr-meta     { font-size:12px;color:var(--text-dim);margin-top:2px;display:flex;gap:6px;flex-wrap:wrap;align-items:center; }
.pr-ref      { font-family:var(--mono);color:var(--accent);font-weight:600; }
.pr-age      { font-size:11px;font-weight:700;padding:1px 7px;border-radius:5px;background:var(--amber-dim);color:var(--amber); }
.pr-right    { text-align:right;flex-shrink:0;display:flex;flex-direction:column;align-items:flex-end;gap:6px; }
.pr-amt      { font-size:13px;font-weight:800;font-family:var(--mono);color:var(--text);white-space:nowrap; }
.pr-btns     { display:flex;gap:4px; }
.pr-action   { padding:4px 10px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;
               cursor:pointer;font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap; }
.pr-action:hover { border-color:var(--accent);color:var(--accent); }
.pr-action.reject:hover { border-color:var(--red);color:var(--red); }
.pr-action:disabled { opacity:.45;cursor:not-allowed;border-color:var(--border);color:var(--text-sub); }

.pr-empty { padding:28px 20px;text-align:center;font-size:13px;color:var(--text-dim); }

.pr-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;padding:16px; }
.pr-modal   { background:var(--surface);border-radius:var(--r);box-shadow:0 24px 60px rgba(26,31,54,.25);width:100%;max-width:420px; }
.pr-modal-head  { padding:20px 22px 0; }
.pr-modal-title { font-size:16px;font-weight:800;color:var(--text);margin:0; }
.pr-modal-sub   { font-size:13px;color:var(--text-dim);margin:4px 0 0;line-height:1.5; }
.pr-modal-body  { padding:18px 22px; }
.pr-modal-foot  { padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px; }
.pr-sum-row { display:flex;justify-content:space-between;gap:12px;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px; }
.pr-sum-row:last-child { border-bottom:none; }
.pr-sum-row span:first-child { color:var(--text-dim); }
.pr-sum-row span:last-child  { color:var(--text);font-weight:600;text-align:right; }
.pr-label { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px; }
.pr-input { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);
            outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr); }
.pr-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.pr-error { font-size:12px;color:var(--red);margin-top:6px; }
.pr-btn   { padding:9px 16px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);
            display:inline-flex;align-items:center;gap:6px;white-space:nowrap;border:1px solid var(--border);background:var(--surface);color:var(--text-sub); }
.pr-btn:hover { background:var(--surface2);color:var(--text); }
.pr-btn-go  { background:var(--accent);border-color:var(--accent);color:#fff; }
.pr-btn-go:hover { background:var(--accent);color:#fff;opacity:.88; }
.pr-btn-red { background:var(--red);border-color:var(--red);color:#fff; }
.pr-btn-red:hover { background:var(--red);color:#fff;opacity:.9; }

@media (max-width:640px) {
    .pr-action { min-height:30px !important;min-width:0 !important;padding:4px 10px !important; }
}
@media (max-width:480px) {
    .pr-head, .pr-row { padding-left:14px;padding-right:14px; }
}
</style>

<div class="pr-head">
    <div>
        <div class="pr-title">Pending requests @if ($requests->count())<span class="pr-count">{{ $requests->count() }}</span>@endif</div>
        <div class="pr-sub">Expense requests from the warehouse</div>
    </div>
</div>

@if (! $canAct && $requests->isNotEmpty())
    <div class="pr-note">Open today's register to pay or reject requests.</div>
@endif

@if ($requests->isEmpty())
    <div class="pr-empty">No pending requests</div>
@else
    <div style="padding-top:{{ $canAct ? 0 : 8 }}px">
        @foreach ($requests as $request)
            @php $ageDays = (int) $request->created_at->diffInDays(now()); @endphp
            <div class="pr-row" wire:key="pr-{{ $request->id }}">
                <div class="pr-main">
                    <div class="pr-reason" title="{{ $request->reason }}">{{ $request->reason }}</div>
                    <div class="pr-meta">
                        <span class="pr-ref">{{ $request->reference_number }}</span>
                        <span>· {{ $request->warehouse->name ?? 'Warehouse' }}</span>
                        @if ($ageDays >= 1)
                            <span class="pr-age">{{ $ageDays }}d waiting</span>
                        @else
                            <span>· {{ $request->created_at->diffForHumans(null, true) }} ago</span>
                        @endif
                    </div>
                </div>
                <div class="pr-right">
                    <span class="pr-amt">{{ number_format($request->amount) }} <span style="font-size:10px;color:var(--text-dim);font-weight:500">RWF</span></span>
                    <div class="pr-btns">
                        <button type="button" class="pr-action" wire:click="confirmPay({{ $request->id }})" @disabled(! $canAct)>Pay</button>
                        <button type="button" class="pr-action reject" wire:click="showRejectForm({{ $request->id }})" @disabled(! $canAct)>Reject</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Pay confirmation --}}
@if ($payingId && ($paying = $requests->firstWhere('id', $payingId)))
    <div class="pr-overlay" wire:key="pr-pay-modal" x-data @keydown.escape.window="$wire.cancelPay()" @click.self="$wire.cancelPay()">
        <div class="pr-modal" role="dialog" aria-modal="true">
            <div class="pr-modal-head">
                <h2 class="pr-modal-title">Pay this request?</h2>
                <p class="pr-modal-sub">The amount is paid from today's cash and recorded as an expense.</p>
            </div>
            <div class="pr-modal-body">
                <div class="pr-sum-row"><span>Reference</span><span style="font-family:var(--mono)">{{ $paying->reference_number }}</span></div>
                <div class="pr-sum-row"><span>Reason</span><span>{{ $paying->reason }}</span></div>
                <div class="pr-sum-row"><span>Requested by</span><span>{{ $paying->requestedBy->name ?? '—' }} · {{ $paying->warehouse->name ?? 'Warehouse' }}</span></div>
                <div class="pr-sum-row"><span>Amount</span><span style="font-family:var(--mono);font-weight:800">{{ number_format($paying->amount) }} RWF</span></div>
            </div>
            <div class="pr-modal-foot">
                <button type="button" class="pr-btn" wire:click="cancelPay">Cancel</button>
                <button type="button" class="pr-btn pr-btn-go" wire:click="payRequest" wire:loading.attr="disabled" wire:target="payRequest">
                    <span wire:loading.remove wire:target="payRequest">Pay {{ number_format($paying->amount) }} RWF</span>
                    <span wire:loading wire:target="payRequest">Paying…</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- Reject with reason --}}
@if ($rejectingId && ($rejecting = $requests->firstWhere('id', $rejectingId)))
    <div class="pr-overlay" wire:key="pr-reject-modal" x-data @keydown.escape.window="$wire.cancelReject()" @click.self="$wire.cancelReject()">
        <div class="pr-modal" role="dialog" aria-modal="true">
            <div class="pr-modal-head">
                <h2 class="pr-modal-title">Reject request</h2>
                <p class="pr-modal-sub">{{ $rejecting->reference_number }} · {{ number_format($rejecting->amount) }} RWF — the warehouse will see your reason.</p>
            </div>
            <form class="pr-modal-body" wire:submit="submitRejection" id="pr-reject-form">
                <label class="pr-label" for="pr-reason">Reason</label>
                <input id="pr-reason" type="text" class="pr-input" wire:model="rejectionReason" placeholder="Why is this being rejected?" x-init="$nextTick(() => $el.focus())">
                @error('rejectionReason') <div class="pr-error">{{ $message }}</div> @enderror
            </form>
            <div class="pr-modal-foot">
                <button type="button" class="pr-btn" wire:click="cancelReject">Cancel</button>
                <button type="submit" form="pr-reject-form" class="pr-btn pr-btn-red" wire:loading.attr="disabled" wire:target="submitRejection">
                    <span wire:loading.remove wire:target="submitRejection">Reject request</span>
                    <span wire:loading wire:target="submitRejection">Rejecting…</span>
                </button>
            </div>
        </div>
    </div>
@endif
</div>
