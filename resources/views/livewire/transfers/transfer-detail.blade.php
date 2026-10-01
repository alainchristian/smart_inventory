{{--
    Transfer detail for owner, shop and warehouse (App\Livewire\Transfers\TransferDetail).
    Prefix tfd-; shared parts from <x-transfers.*> (tf-).
--}}
@use('App\Enums\TransferStatus', 'S')
<div class="tfd-page" style="font-family:var(--font)">
<style>
.tfd-page { padding:0 0 80px }
.tfd-meta { display:flex;align-items:center;gap:10px;flex-wrap:wrap }
.tfd-meta-dim { color:var(--text-dim) }

.tfd-alert { display:flex;gap:12px;align-items:flex-start;padding:14px 18px;margin-bottom:16px;background:var(--surface);
             border-radius:var(--r);box-shadow:var(--shadow-card);border-left:3px solid var(--tfd-tone,var(--accent)) }
.tfd-alert-icon { color:var(--tfd-tone,var(--accent));flex-shrink:0;margin-top:1px }
.tfd-alert-title { font-size:13px;font-weight:700;color:var(--text) }
.tfd-alert-text { font-size:13px;color:var(--text-sub);margin-top:2px;line-height:1.5 }

.tfd-card { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0;margin-bottom:16px }
.tfd-card-head { padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px }
.tfd-card-title { font-size:13px;font-weight:700;color:var(--text);margin:0 }
.tfd-card-sub { font-size:12px;color:var(--text-dim);margin-top:2px }
.tfd-card-body { padding:16px 18px }
.tfd-progress { padding:20px 18px 18px }

.tfd-grid { display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:16px;align-items:start }

.tfd-table { width:100%;border-collapse:collapse }
.tfd-table thead tr { border-bottom:2px solid var(--border) }
.tfd-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.tfd-table td { padding:12px 16px;font-size:13px;color:var(--text-sub);vertical-align:middle;white-space:nowrap }
.tfd-table tbody tr { border-bottom:1px solid var(--border) }
.tfd-table tbody tr:last-child { border-bottom:none }
.tfd-table tfoot td { padding:11px 16px;border-top:2px solid var(--border);font-weight:700;color:var(--text) }
.tfd-r { text-align:right !important }
.tfd-prod { font-weight:600;color:var(--text) }
.tfd-sub { font-size:12px;color:var(--text-dim);margin-top:2px;font-family:var(--mono) }
.tfd-n { font:700 13px var(--mono);color:var(--text) }
.tfd-n.ok { color:var(--green) }
.tfd-n.warn { color:var(--amber) }
.tfd-n.bad { color:var(--red) }
.tfd-dash { color:var(--text-dim) }
.tfd-code { font:700 12px var(--mono);color:var(--accent) }
.tfd-state { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 8px;border-radius:6px }
.tfd-qty { width:88px;padding:7px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;background:var(--surface);
           color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono);font-weight:700 }
.tfd-qty:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.tfd-qty.err { border-color:var(--red) }
.tfd-err { font-size:11px;color:var(--red);margin-top:4px;white-space:normal }

.tfd-review-foot { padding:14px 18px;border-top:1px solid var(--border);display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap }
.tfd-review-note { flex:1;min-width:240px }
.tfd-label { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.tfd-input { width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);
             color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);resize:vertical }
.tfd-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }

.tfd-note + .tfd-note { margin-top:14px;padding-top:14px;border-top:1px solid var(--border) }
.tfd-note-label { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);margin-bottom:4px }
.tfd-note-text { font-size:13px;color:var(--text);line-height:1.5;white-space:pre-line }
.tfd-dl { display:grid;grid-template-columns:auto 1fr;gap:8px 14px;font-size:13px;margin:0 }
.tfd-dl dt { color:var(--text-dim) }
.tfd-dl dd { margin:0;color:var(--text);font-weight:600;text-align:right;min-width:0;overflow-wrap:anywhere }
.tfd-empty { font-size:13px;color:var(--text-dim) }
.tfd-field { margin-bottom:14px }
.tfd-x { width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;border:none;border-radius:8px;background:var(--surface2);color:var(--text-sub);cursor:pointer }
.tfd-x:hover { background:var(--surface3) }

@media (max-width:1100px) { .tfd-grid { grid-template-columns:1fr } }
@media (max-width:640px) {
    .tfd-card-head, .tfd-card-body, .tfd-progress, .tfd-review-foot { padding-left:12px;padding-right:12px }
    .tfd-alert { padding:12px }
    .tfd-review-note { min-width:0;flex-basis:100% }
    .tfd-review-foot > .tf-btn { flex:1;justify-content:center }
    .tfd-input, .tfd-qty { font-size:16px }
}
@keyframes tfd-spin { to { transform:rotate(360deg) } }
</style>

@php
    $status = $t->status;
    $shipped = in_array($status, [S::IN_TRANSIT, S::DELIVERED, S::RECEIVED], true);
@endphp

{{-- ═══ Header ═══════════════════════════════════════════════════════ --}}
<x-transfers.header :title="$t->transfer_number" mono :back="$backUrl">
    <x-slot:meta>
        <span class="tfd-meta">
            <x-transfers.status :status="$status" />
            <x-transfers.route :from="$t->fromWarehouse?->name ?? '—'" :to="$t->toShop?->name ?? '—'" />
            @if($t->needed_by)
                @php $late = ! $t->received_at && $t->needed_by->lt(business_today()); @endphp
                <span style="font-size:12px;font-weight:600;color:{{ $late ? 'var(--red)' : 'var(--text-sub)' }}">
                    Needed by {{ $t->needed_by->format('D d M') }}{{ $late ? ' · overdue' : '' }}
                </span>
            @endif
        </span>
    </x-slot:meta>
    <x-slot:actions>
        @if($canCancel)
            <button type="button" class="tf-btn tf-btn-ghost" wire:click="openCancel">
                {{ $role === 'shop' ? 'Withdraw request' : 'Cancel transfer' }}
            </button>
        @endif
        @if($role === 'warehouse' && $status === S::APPROVED)
            <a href="{{ route('warehouse.transfers.picking-list', $t) }}" target="_blank" class="tf-btn tf-btn-ghost">Picking list</a>
            <a href="{{ route('warehouse.transfers.pack', $t) }}" wire:navigate class="tf-btn tf-btn-primary">{{ $t->packed_at ? 'Continue packing' : 'Pack transfer' }}</a>
        @elseif($canDispatch)
            @if($canReopen && $role === 'warehouse')
                <button type="button" class="tf-btn tf-btn-ghost" wire:click="reopenPacking">Back to packing</button>
            @endif
            <button type="button" class="tf-btn tf-btn-primary" wire:click="openDispatch">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linejoin="round" d="M1 3h15v13H1zM16 8h4l3 3v5h-7"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Dispatch
            </button>
        @elseif($role === 'shop' && in_array($status, [S::IN_TRANSIT, S::DELIVERED], true))
            @if($status === S::IN_TRANSIT)
                <button type="button" class="tf-btn tf-btn-ghost" wire:click="markAsDelivered" wire:loading.attr="disabled" wire:target="markAsDelivered"
                        title="The boxes are here; you'll scan them in later">Mark as arrived</button>
            @endif
            <a href="{{ route('shop.transfers.receive', $t) }}" wire:navigate class="tf-btn tf-btn-primary">Receive boxes</a>
        @endif
        @if($role === 'shop' && $shipped)
            <a href="{{ route('shop.transfers.delivery-note', $t) }}" target="_blank" class="tf-btn tf-btn-ghost">Delivery note</a>
        @endif
        @if($t->received_at)
            <a href="{{ route($role . '.transfers.received-note', $t) }}" target="_blank" class="tf-btn tf-btn-ghost">Received note</a>
        @endif
        @if($noteUrl && $shipped)
            <a href="{{ $noteUrl }}" target="_blank" class="tf-btn tf-btn-ghost">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Delivery note
            </a>
        @endif
    </x-slot:actions>
</x-transfers.header>

{{-- ═══ Alerts (only when there's something to say) ══════════════════ --}}
<div x-data x-on:transfer-dispatched.window="window.open($event.detail.url, '_blank')"></div>
@if($justDispatched && $noteUrl)
    <div class="tfd-alert" style="--tfd-tone:var(--green)">
        <svg class="tfd-alert-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <div style="flex:1">
            <div class="tfd-alert-title">Dispatched — give the delivery note to the driver</div>
            <div class="tfd-alert-text">It lists every box, the instructions and where it goes. The shop scans the boxes in and signs on arrival.</div>
        </div>
        <a href="{{ $noteUrl }}" target="_blank" class="tf-btn tf-btn-primary" style="align-self:center">Print delivery note</a>
    </div>
@endif
@if($status === S::READY && ! $canDispatch)
    <div class="tfd-alert" style="--tfd-tone:var(--accent)">
        <svg class="tfd-alert-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <div>
            <div class="tfd-alert-title">Packed — waiting for the transporter</div>
            <div class="tfd-alert-text">Packed {{ local_time($t->packing_done_at)?->format('d M · H:i') }}{{ $t->packingDoneBy ? ' by ' . $t->packingDoneBy->name : '' }}. It leaves the warehouse once a driver signs for it.</div>
        </div>
    </div>
@endif
@if($status === S::REJECTED || $status === S::CANCELLED)
    <div class="tfd-alert" style="--tfd-tone:var(--red)">
        <svg class="tfd-alert-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M15 9l-6 6M9 9l6 6"/></svg>
        <div>
            <div class="tfd-alert-title">{{ $status === S::REJECTED ? 'Rejected' . ($t->reviewedBy ? ' by ' . $t->reviewedBy->name : '') : 'Cancelled' }}</div>
            <div class="tfd-alert-text">{{ $t->review_notes ?: 'No reason was given.' }}@if($role === 'shop' && $status === S::REJECTED) You can send a new request.@endif</div>
        </div>
    </div>
@elseif($received && ($issues['damaged'] || $issues['missing']))
    <div class="tfd-alert" style="--tfd-tone:var(--red)">
        <svg class="tfd-alert-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linejoin="round" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><path stroke-linecap="round" d="M12 9v4M12 17h.01"/></svg>
        <div>
            <div class="tfd-alert-title">Discrepancy on delivery</div>
            <div class="tfd-alert-text">
                {{ collect([
                    $issues['damaged'] ? $issues['damaged'] . ' ' . Str::plural('box', $issues['damaged']) . ' arrived damaged' : null,
                    $issues['missing'] ? $issues['missing'] . ' ' . Str::plural('box', $issues['missing']) . ' never arrived' : null,
                ])->filter()->implode(' · ') }}.
                @if($openIssues->isNotEmpty())
                    {{ $openIssues->count() }} missing {{ Str::plural('box', $openIssues->count()) }} still to resolve — they stay off sale until then.
                @elseif($t->closed_at)
                    All boxes are accounted for; the transfer is closed.
                @endif
            </div>
        </div>
    </div>
@elseif($status === S::PENDING && ! $canReview)
    <div class="tfd-alert" style="--tfd-tone:var(--amber)">
        <svg class="tfd-alert-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
        <div>
            <div class="tfd-alert-title">Waiting for approval</div>
            <div class="tfd-alert-text">The warehouse checks stock and may adjust the quantities before approving.</div>
        </div>
    </div>
@elseif($role === 'shop' && $status === S::DELIVERED)
    <div class="tfd-alert" style="--tfd-tone:var(--pink)">
        <svg class="tfd-alert-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <div>
            <div class="tfd-alert-title">Arrived — scan the boxes in</div>
            <div class="tfd-alert-text">They join your shop stock once received. Mark any damaged or missing box while scanning.</div>
        </div>
    </div>
@endif

{{-- ═══ Progress ═════════════════════════════════════════════════════ --}}
<div class="tfd-card">
    <div class="tfd-progress"><x-transfers.timeline :transfer="$t" /></div>
</div>

<div class="tfd-grid">
    <div style="min-width:0">
        @if($openIssues->isNotEmpty())
            <div class="tfd-card" style="box-shadow:var(--shadow-card), inset 3px 0 0 var(--red)">
                <div class="tfd-card-head">
                    <div>
                        <h2 class="tfd-card-title">To resolve</h2>
                        <div class="tfd-card-sub">
                            @if($canResolve)
                                Shipped but not received. Find out what happened to each box.
                            @else
                                The warehouse is following up on these boxes.
                            @endif
                        </div>
                    </div>
                </div>
                <div class="m-scroll">
                    <table class="tfd-table m-sticky-first" style="min-width:{{ $canResolve ? 760 : 420 }}px">
                        <thead><tr><th>Box</th><th>Product</th><th class="tfd-r">Items</th>@if($canResolve)<th class="tfd-r">What happened?</th>@endif</tr></thead>
                        <tbody>
                            @foreach($openIssues as $tb)
                                @php $product = $lines->firstWhere('product.id', $tb->box?->product_id)['product'] ?? null; @endphp
                                <tr wire:key="issue-{{ $tb->id }}">
                                    <td><span class="tfd-code">{{ $tb->box?->box_code }}</span></td>
                                    <td>{{ $product?->name ?? '—' }}</td>
                                    <td class="tfd-r"><span class="tfd-n">{{ number_format($tb->box?->items_remaining ?? 0) }}</span></td>
                                    @if($canResolve)
                                        <td class="tfd-r">
                                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                                <button type="button" class="tf-btn tf-btn-ghost tf-btn-sm" wire:click="openResolve({{ $tb->box_id }}, 'found')">Found at warehouse</button>
                                                <button type="button" class="tf-btn tf-btn-ghost tf-btn-sm" wire:click="openResolve({{ $tb->box_id }}, 'received_late')">Arrived late</button>
                                                <button type="button" class="tf-btn tf-btn-danger tf-btn-sm" wire:click="openResolve({{ $tb->box_id }}, 'lost')">Lost</button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        {{-- ═══ Items ════════════════════════════════════════════════ --}}
        <div class="tfd-card">
            <div class="tfd-card-head">
                <div>
                    <h2 class="tfd-card-title">{{ $canReview ? 'Review quantities' : 'Items' }}</h2>
                    <div class="tfd-card-sub">
                        @if($canReview)
                            Lower a quantity if the warehouse can't send it all
                        @else
                            {{ $lines->count() }} {{ Str::plural('product', $lines->count()) }} · {{ number_format($lines->sum('requested')) }} {{ Str::plural('box', $lines->sum('requested')) }} requested
                        @endif
                    </div>
                </div>
            </div>
            <div class="m-scroll">
                <table class="tfd-table m-sticky-first" style="min-width:{{ $canReview ? 620 : 560 }}px">
                    <thead>
                        <tr>
                            <th>Product</th>
                            @if($canReview)
                                <th class="tfd-r">Requested</th>
                                <th class="tfd-r">In warehouse</th>
                                <th>Approve</th>
                                <th class="tfd-r">Items</th>
                            @else
                                <th class="tfd-r">Requested</th>
                                @if($lines->contains(fn ($l) => $l['approved'] !== null))<th class="tfd-r">Approved</th>@endif
                                <th class="tfd-r">Packed</th>
                                <th class="tfd-r">Received</th>
                                @if($received && ($issues['damaged'] || $issues['missing']))<th class="tfd-r">Damaged</th>@endif
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $line)
                            @php
                                $p = $line['product'];
                                $ipb = max(1, (int) ($p?->items_per_box ?? 1));
                            @endphp
                            <tr wire:key="line-{{ $line['item']->id }}">
                                <td>
                                    <div class="tfd-prod">{{ $p?->name ?? '—' }}</div>
                                    <div class="tfd-sub">{{ $p?->sku }} · {{ $ipb }}/box</div>
                                </td>
                                @if($canReview)
                                    @php
                                        $avail = $stock[$p?->id]['boxes'] ?? 0;
                                        $err = $errors->first('qty.' . $line['item']->id);
                                        $want = (int) ($qty[$line['item']->id] ?? 0);
                                    @endphp
                                    <td class="tfd-r"><span class="tfd-n">{{ $line['requested'] }}</span></td>
                                    <td class="tfd-r">
                                        <span class="tfd-n {{ $avail < $line['requested'] ? 'warn' : 'ok' }}">{{ $avail }}</span>
                                        @if(($stock[$p?->id]['partial'] ?? 0) > 0)<div class="tfd-sub">{{ $stock[$p->id]['partial'] }} opened</div>@endif
                                    </td>
                                    <td>
                                        <x-number-input wire:model.live.debounce.300ms="qty.{{ $line['item']->id }}" max="{{ $avail }}"
                                                        class="tfd-qty {{ $err ? 'err' : '' }}" aria-label="Boxes to approve for {{ $p?->name }}" />
                                        @if($err)<div class="tfd-err">{{ $err }}</div>@endif
                                    </td>
                                    <td class="tfd-r"><span class="tfd-n">{{ number_format($want * $ipb) }}</span></td>
                                @else
                                    @php
                                        $toSend = $line['approved'] ?? $line['requested'];
                                        $allIn = $line['packed'] > 0 && $line['packed'] >= $toSend;
                                    @endphp
                                    <td class="tfd-r"><span class="tfd-n">{{ $line['requested'] }}</span></td>
                                    @if($lines->contains(fn ($l) => $l['approved'] !== null))
                                        <td class="tfd-r">
                                            <span class="tfd-n {{ $line['approved'] !== null && $line['approved'] < $line['requested'] ? 'warn' : '' }}">{{ $line['approved'] ?? '—' }}</span>
                                        </td>
                                    @endif
                                    <td class="tfd-r">
                                        @if($line['packed'] || $shipped)
                                            <span class="tfd-n {{ $allIn ? 'ok' : 'warn' }}">{{ $line['packed'] }}</span>
                                            @if($line['short'])<div class="tfd-sub" style="font-family:var(--font);color:var(--amber)" title="{{ $line['short'] }}">Short: {{ Str::limit($line['short'], 28) }}</div>@endif
                                        @else
                                            <span class="tfd-dash">—</span>
                                        @endif
                                    </td>
                                    <td class="tfd-r">
                                        @if($received)
                                            <span class="tfd-n {{ $line['received'] < $line['packed'] ? 'bad' : 'ok' }}">{{ $line['received'] }}</span>
                                        @else
                                            <span class="tfd-dash">—</span>
                                        @endif
                                    </td>
                                    @if($received && ($issues['damaged'] || $issues['missing']))
                                        <td class="tfd-r">@if($line['damaged'])<span class="tfd-n bad">{{ $line['damaged'] }}</span>@else<span class="tfd-dash">—</span>@endif</td>
                                    @endif
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($canReview)
                <div class="tfd-review-foot">
                    <div class="tfd-review-note">
                        <label class="tfd-label" for="tfd-note">Note for the shop <span style="font-weight:500;color:var(--text-dim)">(optional)</span></label>
                        <input id="tfd-note" class="tfd-input" wire:model="approveNote" maxlength="500" placeholder="e.g. Sending 2 of 3 — the rest next week">
                    </div>
                    <button type="button" class="tf-btn tf-btn-danger" wire:click="openReject">Reject</button>
                    <button type="button" class="tf-btn tf-btn-primary" wire:click="approve" wire:loading.attr="disabled" wire:target="approve">
                        <span wire:loading.remove wire:target="approve">Approve transfer</span>
                        <span wire:loading wire:target="approve" style="display:none;align-items:center;gap:6px">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:tfd-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                            Approving…
                        </span>
                    </button>
                </div>
            @endif
        </div>

        {{-- ═══ Boxes ════════════════════════════════════════════════ --}}
        @if($t->boxes->isNotEmpty())
            <div class="tfd-card">
                <div class="tfd-card-head">
                    <div>
                        <h2 class="tfd-card-title">Boxes</h2>
                        <div class="tfd-card-sub">{{ $t->boxes->count() }} {{ Str::plural('box', $t->boxes->count()) }} · {{ number_format($t->boxes->sum(fn ($tb) => $tb->box?->items_remaining ?? 0)) }} items</div>
                    </div>
                </div>
                <div class="m-scroll">
                    <table class="tfd-table m-sticky-first" style="min-width:560px">
                        <thead><tr><th>Box</th><th>Product</th><th class="tfd-r">Items</th><th>State</th></tr></thead>
                        <tbody>
                            @foreach($t->boxes->sortBy(fn ($tb) => [$tb->is_damaged ? 0 : 1, $tb->box?->product_id]) as $tb)
                                @php
                                    [$label, $tone] = match (true) {
                                        (bool) $tb->is_damaged  => ['Damaged · to Damaged Goods', 'red'],
                                        $tb->resolution === 'received_late' => ['Arrived late', 'green'],
                                        (bool) $tb->is_received => ['Received', 'green'],
                                        $tb->resolution === 'found' => ['Missing · found at warehouse', 'amber'],
                                        $tb->resolution === 'lost'  => ['Lost · written off', 'red'],
                                        $received               => ['Missing · to resolve', 'red'],
                                        $shipped                => ['On the road', 'violet'],
                                        default                 => ['Packed', 'accent'],
                                    };
                                    $product = $lines->firstWhere('product.id', $tb->box?->product_id)['product'] ?? null;
                                @endphp
                                <tr wire:key="box-{{ $tb->id }}">
                                    <td><span class="tfd-code">{{ $tb->box?->box_code ?? '—' }}</span></td>
                                    <td>{{ $product?->name ?? '—' }}</td>
                                    <td class="tfd-r"><span class="tfd-n">{{ number_format($tb->box?->items_remaining ?? 0) }}</span></td>
                                    <td>
                                        <span class="tfd-state" style="background:var(--{{ $tone }}-dim);color:var(--{{ $tone }})">{{ $label }}</span>
                                        @if($tb->damage_notes)<span style="margin-left:6px;color:var(--text-sub)">{{ $tb->damage_notes }}</span>@endif
                                        @if($tb->resolution_notes && $tb->resolution !== 'damaged_goods')<span style="margin-left:6px;color:var(--text-sub)" title="Resolved {{ local_time($tb->resolved_at)?->format('d M H:i') }}">{{ $tb->resolution_notes }}</span>@endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <div style="min-width:0">
        {{-- ═══ Notes ════════════════════════════════════════════════ --}}
        <div class="tfd-card">
            <div class="tfd-card-head"><h2 class="tfd-card-title">Notes</h2></div>
            <div class="tfd-card-body">
                @if($t->notes || ($t->review_notes && ! in_array($status, [S::REJECTED, S::CANCELLED], true)))
                    @if($t->notes)
                        <div class="tfd-note">
                            <div class="tfd-note-label">{{ $role === 'shop' ? 'Your request' : "Shop's request" }} · {{ $t->requestedBy?->name }}</div>
                            <div class="tfd-note-text">{{ $t->notes }}</div>
                        </div>
                    @endif
                    @if($t->review_notes && ! in_array($status, [S::REJECTED, S::CANCELLED], true))
                        <div class="tfd-note">
                            <div class="tfd-note-label">Approval note · {{ $t->reviewedBy?->name }}</div>
                            <div class="tfd-note-text">{{ $t->review_notes }}</div>
                        </div>
                    @endif
                @else
                    <div class="tfd-empty">No notes on this transfer.</div>
                @endif
            </div>
        </div>

        {{-- ═══ Transporter ══════════════════════════════════════════ --}}
        @if($t->transporter)
            <div class="tfd-card">
                <div class="tfd-card-head"><h2 class="tfd-card-title">Transporter</h2></div>
                <div class="tfd-card-body">
                    <dl class="tfd-dl">
                        <dt>Name</dt><dd>{{ $t->transporter->name }}</dd>
                        @if($t->transporter->company_name)<dt>Company</dt><dd>{{ $t->transporter->company_name }}</dd>@endif
                        @if($t->transporter->phone)<dt>Phone</dt><dd><a href="tel:{{ $t->transporter->phone }}" style="color:var(--accent);text-decoration:none">{{ $t->transporter->phone }}</a></dd>@endif
                        @if($t->transporter->vehicle_number)<dt>Vehicle</dt><dd style="font-family:var(--mono)">{{ $t->transporter->vehicle_number }}</dd>@endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ═══ Resolve sheet ════════════════════════════════════════════════ --}}
@if($showResolve)
    @php $rb = $t->boxes->firstWhere('box_id', $resolveBoxId); @endphp
    <div class="m-sheet-overlay" wire:click="$set('showResolve', false)"></div>
    <div class="m-sheet" role="dialog" aria-modal="true" aria-labelledby="tfd-resolve-title">
        <div class="m-sheet-handle"></div>
        <div class="m-sheet-head">
            <h2 class="m-sheet-title" id="tfd-resolve-title">{{ $resolutions[$resolveAs] ?? 'Resolve' }}: {{ $rb?->box?->box_code }}</h2>
            <button type="button" class="tfd-x m-tap" wire:click="$set('showResolve', false)" aria-label="Close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="m-sheet-body">
            <p style="font-size:13px;color:var(--text-sub);margin:0 0 14px;line-height:1.5">
                @switch($resolveAs)
                    @case('found') The box goes back on sale at {{ $t->fromWarehouse?->name }}. @break
                    @case('received_late') The box joins {{ $t->toShop?->name }}'s stock now. @break
                    @case('lost') The box is written off as a loss in Damaged Goods, naming {{ $t->transporter?->name ?? 'the transporter' }}{{ $t->handed_to_name ? ' (' . $t->handed_to_name . ')' : '' }}. @break
                @endswitch
            </p>
            <label class="tfd-label" for="tfd-resolve-note">What happened? <span style="color:var(--red)">*</span></label>
            <textarea id="tfd-resolve-note" class="tfd-input" rows="3" wire:model="resolveNote" maxlength="500" placeholder="e.g. Left on the loading bay; driver confirmed only 8 boxes"></textarea>
            @error('resolveNote')<div class="tfd-err">{{ $message }}</div>@enderror
        </div>
        <div class="m-sheet-foot">
            <button type="button" class="tf-btn tf-btn-ghost" wire:click="$set('showResolve', false)">Cancel</button>
            <button type="button" class="tf-btn {{ $resolveAs === 'lost' ? 'tf-btn-danger' : 'tf-btn-primary' }}" wire:click="resolve" wire:loading.attr="disabled" wire:target="resolve">Confirm</button>
        </div>
    </div>
@endif

{{-- ═══ Dispatch sheet ═══════════════════════════════════════════════ --}}
@if($showDispatch)
    <div class="m-sheet-overlay" wire:click="$set('showDispatch', false)"></div>
    <div class="m-sheet" role="dialog" aria-modal="true" aria-labelledby="tfd-dispatch-title">
        <div class="m-sheet-handle"></div>
        <div class="m-sheet-head">
            <h2 class="m-sheet-title" id="tfd-dispatch-title">Dispatch {{ $t->transfer_number }}</h2>
            <button type="button" class="tfd-x m-tap" wire:click="$set('showDispatch', false)" aria-label="Close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="m-sheet-body">
            <p style="font-size:13px;color:var(--text-sub);margin:0 0 14px;line-height:1.5">
                {{ $t->boxes->count() }} {{ Str::plural('box', $t->boxes->count()) }} for {{ $t->toShop?->name }}. The shop is told it's on the way.
            </p>
            <div class="tfd-field">
                <label class="tfd-label" for="tfd-tr">Transporter <span style="color:var(--red)">*</span></label>
                <input id="tfd-tr" class="tfd-input" wire:model="transporterName" list="tfd-transporters" placeholder="Choose or type a company / driver" autocomplete="off">
                <datalist id="tfd-transporters">
                    @foreach($transporters as $tr)<option value="{{ $tr->name }}">{{ $tr->vehicle_number }}</option>@endforeach
                </datalist>
                @error('transporterName')<div class="tfd-err">{{ $message }}</div>@enderror
            </div>
            <div class="tfd-field">
                <label class="tfd-label" for="tfd-driver">Handed to (driver's name) <span style="color:var(--red)">*</span></label>
                <input id="tfd-driver" class="tfd-input" wire:model="handedToName" maxlength="120" placeholder="Who is taking the boxes">
                @error('handedToName')<div class="tfd-err">{{ $message }}</div>@enderror
            </div>
            <div class="tfd-field">
                <label class="tfd-label" for="tfd-eta">Expected at the shop</label>
                <input id="tfd-eta" type="datetime-local" class="tfd-input" wire:model="expectedArrival">
                @error('expectedArrival')<div class="tfd-err">{{ $message }}</div>@enderror
            </div>
            <div class="tfd-field">
                <label class="tfd-label" for="tfd-ins">Instructions for the transporter</label>
                <textarea id="tfd-ins" class="tfd-input" rows="2" wire:model="instructions" maxlength="1000" placeholder="e.g. Keep dry, deliver before 10:00, call the shop on arrival"></textarea>
            </div>
            <div class="tfd-field" style="margin-bottom:0">
                <label class="tfd-label">Driver's signature @if($needsSignature)<span style="color:var(--red)">*</span>@else<span style="font-weight:500;color:var(--text-dim)">(optional)</span>@endif</label>
                <x-signature-pad model="handoverSignature" label="Driver signs here" wire:key="sig-dispatch-{{ $t->id }}" />
                @error('handoverSignature')<div class="tfd-err">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="m-sheet-foot">
            <button type="button" class="tf-btn tf-btn-ghost" wire:click="$set('showDispatch', false)">Cancel</button>
            <button type="button" class="tf-btn tf-btn-primary" wire:click="dispatchTransfer" wire:loading.attr="disabled" wire:target="dispatchTransfer">
                <span wire:loading.remove wire:target="dispatchTransfer">Dispatch now</span>
                <span wire:loading wire:target="dispatchTransfer" style="display:none">Dispatching…</span>
            </button>
        </div>
    </div>
@endif

{{-- ═══ Cancel sheet ═════════════════════════════════════════════════ --}}
@if($showCancel)
    <div class="m-sheet-overlay" wire:click="$set('showCancel', false)"></div>
    <div class="m-sheet" role="dialog" aria-modal="true" aria-labelledby="tfd-cancel-title">
        <div class="m-sheet-handle"></div>
        <div class="m-sheet-head">
            <h2 class="m-sheet-title" id="tfd-cancel-title">{{ $role === 'shop' ? 'Withdraw' : 'Cancel' }} {{ $t->transfer_number }}?</h2>
            <button type="button" class="tfd-x m-tap" wire:click="$set('showCancel', false)" aria-label="Close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="m-sheet-body">
            <p style="font-size:13px;color:var(--text-sub);margin:0 0 14px;line-height:1.5">
                @if($t->boxes->isNotEmpty())
                    The {{ $t->boxes->count() }} packed {{ Str::plural('box', $t->boxes->count()) }} go back into warehouse stock.
                @endif
                {{ $role === 'shop' ? 'The warehouse' : $t->toShop?->name }} will see your reason.
            </p>
            <label class="tfd-label" for="tfd-cancel-reason">Reason <span style="color:var(--red)">*</span></label>
            <textarea id="tfd-cancel-reason" class="tfd-input" rows="3" wire:model="cancelReason" maxlength="500" placeholder="e.g. Ordered twice by mistake"></textarea>
            @error('cancelReason')<div class="tfd-err">{{ $message }}</div>@enderror
        </div>
        <div class="m-sheet-foot">
            <button type="button" class="tf-btn tf-btn-ghost" wire:click="$set('showCancel', false)">Keep it</button>
            <button type="button" class="tf-btn tf-btn-danger" wire:click="cancelTransfer" wire:loading.attr="disabled" wire:target="cancelTransfer">
                {{ $role === 'shop' ? 'Withdraw request' : 'Cancel transfer' }}
            </button>
        </div>
    </div>
@endif

{{-- ═══ Reject sheet ═════════════════════════════════════════════════ --}}
@if($showReject)
    <div class="m-sheet-overlay" wire:click="$set('showReject', false)"></div>
    <div class="m-sheet" role="dialog" aria-modal="true" aria-labelledby="tfd-reject-title">
        <div class="m-sheet-handle"></div>
        <div class="m-sheet-head">
            <h2 class="m-sheet-title" id="tfd-reject-title">Reject {{ $t->transfer_number }}?</h2>
            <button type="button" class="tfd-x m-tap" wire:click="$set('showReject', false)" aria-label="Close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="m-sheet-body">
            <p style="font-size:13px;color:var(--text-sub);margin:0 0 14px;line-height:1.5">{{ $t->toShop?->name }} will see your reason and can send a new request.</p>
            <label class="tfd-label" for="tfd-reason">Reason <span style="color:var(--red)">*</span></label>
            <textarea id="tfd-reason" class="tfd-input" rows="3" wire:model="rejectReason" maxlength="500" placeholder="e.g. Out of stock until next delivery" autofocus></textarea>
            @error('rejectReason')<div class="tfd-err">{{ $message }}</div>@enderror
        </div>
        <div class="m-sheet-foot">
            <button type="button" class="tf-btn tf-btn-ghost" wire:click="$set('showReject', false)">Cancel</button>
            <button type="button" class="tf-btn tf-btn-danger" wire:click="reject" wire:loading.attr="disabled" wire:target="reject">Reject transfer</button>
        </div>
    </div>
@endif
</div>
