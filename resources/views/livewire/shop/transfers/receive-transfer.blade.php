{{--
    Receive a shipped transfer (Shop\Transfers\ReceiveTransfer). Shares the
    scan layout with Pack: <x-transfers.scanner>, tfs- styles, sticky bar.
--}}
<div>
@if($sessionBlocked)
    <x-session-gate-blocked
        :reason="$sessionBlockReason"
        :session-date="$blockedSessionDate"
        :session-id="$blockedSessionId"
    />
@else
<div class="tfs-page" style="font-family:var(--font)">
<x-transfers.styles />
<x-transfers.scan-styles />

@php
    $summary = collect($receivingSummary)->sortBy('complete')->values();
    $okCount = $scannedCount - $damagedCount;
@endphp

<x-transfers.header :title="'Receive ' . $transfer->transfer_number" mono :back="route('shop.transfers.show', $transfer)">
    <x-slot:meta>
        <span style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <x-transfers.status :status="$transfer->status" />
            <x-transfers.route :from="$transfer->fromWarehouse?->name ?? '—'" :to="$transfer->toShop?->name ?? '—'" />
        </span>
    </x-slot:meta>
</x-transfers.header>

<x-transfers.scanner action="scanBox" label="Scan a box code or product barcode" placeholder="Scan or type, then Enter" button="Receive" />

{{-- ═══ Progress per product ═══════════════════════════════════════ --}}
<div class="tfs-card">
    <div class="tfs-card-head">
        <div>
            <h2 class="tfs-card-title">Shipped to you</h2>
            <div class="tfs-card-sub">Unfinished products first</div>
        </div>
        <span class="tfs-pill">{{ $scannedCount }} / {{ $expectedBoxes }} boxes</span>
    </div>
    <div class="m-scroll">
        <table class="tfs-table m-sticky-first" style="min-width:600px">
            <thead><tr><th>Product</th><th class="tfs-r">Scanned</th><th>Progress</th><th></th><th>Barcode</th></tr></thead>
            <tbody>
                @foreach($summary as $row)
                    @php $pct = $row['boxes_shipped'] > 0 ? min(100, round($row['boxes_received'] / $row['boxes_shipped'] * 100)) : 0; @endphp
                    <tr class="{{ $row['complete'] ? 'done' : '' }}" wire:key="ship-{{ $row['product_id'] }}">
                        <td><span class="tfs-prod">{{ $row['product_name'] }}</span></td>
                        <td class="tfs-r"><span class="tfs-n">{{ $row['boxes_received'] }}</span> <span class="tfs-mono">/ {{ $row['boxes_shipped'] }}</span></td>
                        <td style="width:30%"><div class="tfs-bar {{ $row['complete'] ? 'full' : '' }}"><span style="width:{{ $pct }}%"></span></div></td>
                        <td class="tfs-r">
                            @if($row['complete'])
                                <span class="tfs-state" style="background:var(--green-dim);color:var(--green)">Done</span>
                            @else
                                <span class="tfs-state" style="background:var(--amber-dim);color:var(--amber)">{{ $row['boxes_shipped'] - $row['boxes_received'] }} to scan</span>
                            @endif
                        </td>
                        <td><span class="tfs-mono">{{ $row['barcode'] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ═══ Scanned this session ═══════════════════════════════════════ --}}
<div class="tfs-card">
    <div class="tfs-card-head">
        <div>
            <h2 class="tfs-card-title">Scanned</h2>
            <div class="tfs-card-sub">Mark a box damaged and say what's wrong; remove one scanned by mistake</div>
        </div>
        <span class="tfs-pill">{{ count($sessionBoxes) }}</span>
    </div>
    @if(empty($sessionBoxes))
        <div class="tfs-empty">Nothing scanned yet. Scan a box label or a product barcode above.</div>
    @else
        <div class="m-scroll">
            <table class="tfs-table m-sticky-first" style="min-width:720px">
                <thead><tr><th>Box</th><th>Product</th><th class="tfs-r">Items</th><th>Condition</th><th></th></tr></thead>
                <tbody>
                    @foreach(array_reverse($sessionBoxes) as $box)
                        <tr class="{{ $box['is_damaged'] ? 'flag' : '' }}" wire:key="scanned-{{ $box['box_id'] }}">
                            <td><span class="tfs-code">{{ $box['box_code'] }}</span></td>
                            <td>{{ $box['product_name'] }}</td>
                            <td class="tfs-r"><span class="tfs-n">{{ number_format($box['items']) }}</span></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <button type="button" class="tfs-act {{ $box['is_damaged'] ? 'on' : '' }}"
                                            wire:click="markAsDamaged({{ $box['box_id'] }}, {{ $box['is_damaged'] ? 'false' : 'true' }})">
                                        {{ $box['is_damaged'] ? 'Damaged ✓' : 'Mark damaged' }}
                                    </button>
                                    @if($box['is_damaged'])
                                        <input type="text" class="tfs-note-input" value="{{ $box['damage_notes'] }}" placeholder="What's wrong? (required)"
                                               x-on:change="$wire.updateDamageNotes({{ $box['box_id'] }}, $event.target.value)" aria-label="Damage on {{ $box['box_code'] }}">
                                    @endif
                                </div>
                            </td>
                            <td class="tfs-r"><button type="button" class="tfs-act danger" wire:click="removeScannedBox({{ $box['box_id'] }})">Remove</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ═══ Still to scan ══════════════════════════════════════════════ --}}
@if(!empty($pendingBoxes))
    <div class="tfs-card">
        <div class="tfs-card-head">
            <div>
                <h2 class="tfs-card-title">Still to scan</h2>
                <div class="tfs-card-sub">Boxes not scanned when you complete are recorded as missing</div>
            </div>
            <span class="tfs-pill">{{ count($pendingBoxes) }}</span>
        </div>
        <div class="m-scroll">
            <table class="tfs-table m-sticky-first" style="min-width:480px">
                <thead><tr><th>Box</th><th>Product</th><th class="tfs-r">Items</th></tr></thead>
                <tbody>
                    @foreach($pendingBoxes as $box)
                        <tr wire:key="pending-{{ $box['box_id'] }}">
                            <td><span class="tfs-code">{{ $box['box_code'] }}</span></td>
                            <td>{{ $box['product_name'] }}</td>
                            <td class="tfs-r"><span class="tfs-n">{{ number_format($box['items']) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if(!empty($receivedBoxes))
    <div class="tfs-card">
        <div class="tfs-card-head"><h2 class="tfs-card-title">Received earlier</h2><span class="tfs-pill">{{ count($receivedBoxes) }}</span></div>
        <div class="m-scroll">
            <table class="tfs-table m-sticky-first" style="min-width:480px">
                <thead><tr><th>Box</th><th>Product</th><th class="tfs-r">Items</th><th>Condition</th></tr></thead>
                <tbody>
                    @foreach($receivedBoxes as $box)
                        <tr class="done" wire:key="received-{{ $box['box_id'] }}">
                            <td><span class="tfs-code">{{ $box['box_code'] }}</span></td>
                            <td>{{ $box['product_name'] }}</td>
                            <td class="tfs-r"><span class="tfs-n">{{ number_format($box['items']) }}</span></td>
                            <td>{{ $box['is_damaged'] ? 'Damaged' : 'Good' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- ═══ Sticky action bar ══════════════════════════════════════════ --}}
<div class="tfs-actionbar">
    <div class="tfs-stats">
        <div><div class="tfs-stat-v">{{ $scannedCount }}<span style="font-size:12px;color:var(--text-dim)"> / {{ $expectedBoxes }}</span></div><div class="tfs-stat-l">Boxes scanned</div></div>
        <div><div class="tfs-stat-v" style="{{ $damagedCount ? 'color:var(--red)' : '' }}">{{ $damagedCount }}</div><div class="tfs-stat-l">Damaged</div></div>
        <div><div class="tfs-stat-v" style="{{ $remainingCount ? 'color:var(--amber)' : '' }}">{{ $remainingCount }}</div><div class="tfs-stat-l">Not scanned</div></div>
    </div>
    <div class="tfs-actionbar-go">
        <button type="button" class="tf-btn tf-btn-primary" wire:click="openComplete" @disabled(empty($sessionBoxes))>
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Complete receiving
        </button>
    </div>
</div>

{{-- ═══ Quantity prompt ════════════════════════════════════════════ --}}
@if($showQuantityPanel)
    <div class="m-sheet-overlay" wire:click="closeQuantityPanel"></div>
    <div class="m-sheet" role="dialog" aria-modal="true" aria-labelledby="tfs-qty-title" x-data x-on:keydown.escape.window="$wire.closeQuantityPanel()">
        <div class="m-sheet-handle"></div>
        <div class="m-sheet-head">
            <h2 class="m-sheet-title" id="tfs-qty-title">{{ $pendingProductName }}</h2>
            <button type="button" class="tfs-x m-tap" wire:click="closeQuantityPanel" aria-label="Close">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="m-sheet-body">
            <p class="tfs-sheet-lead">How many boxes arrived? {{ $pendingAlreadyScanned }} scanned so far, <strong style="color:var(--text)">{{ $pendingMaxQty }} left to scan</strong>.</p>
            <x-number-input wire:model.live="pendingQty" wire:keydown.enter="confirmScannedQuantity" max="{{ $pendingMaxQty }}"
                            x-init="$nextTick(() => $el.select())" class="tfs-qty" align="center" aria-label="Boxes received" />
            @error('pendingQty')<div class="tfs-err" style="text-align:center">{{ $message }}</div>@enderror
            <p class="tfs-sheet-lead" style="text-align:center;margin:12px 0 0"><kbd class="tfs-kbd">Enter</kbd> to confirm · <kbd class="tfs-kbd">Esc</kbd> to cancel</p>
        </div>
        <div class="m-sheet-foot">
            <button type="button" class="tf-btn tf-btn-ghost" wire:click="closeQuantityPanel">Cancel</button>
            <button type="button" class="tf-btn tf-btn-primary" wire:click="confirmScannedQuantity">Confirm {{ (int) $pendingQty }} {{ Str::plural('box', (int) $pendingQty) }}</button>
        </div>
    </div>
@endif

{{-- ═══ Completion summary ═════════════════════════════════════════ --}}
@if($confirmComplete)
    <div class="m-sheet-overlay" wire:click="$set('confirmComplete', false)"></div>
    <div class="m-sheet" role="dialog" aria-modal="true" aria-labelledby="tfs-done-title">
        <div class="m-sheet-handle"></div>
        <div class="m-sheet-head">
            <h2 class="m-sheet-title" id="tfs-done-title">Complete receiving?</h2>
            <button type="button" class="tfs-x m-tap" wire:click="$set('confirmComplete', false)" aria-label="Close">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="m-sheet-body">
            <div class="tfs-sum">
                <div><div class="tfs-stat-v" style="color:var(--green)">{{ $okCount }}</div><div class="tfs-stat-l">Into your stock</div></div>
                <div><div class="tfs-stat-v" style="{{ $damagedCount ? 'color:var(--red)' : '' }}">{{ $damagedCount }}</div><div class="tfs-stat-l">Damaged</div></div>
                <div><div class="tfs-stat-v" style="{{ $remainingCount ? 'color:var(--red)' : '' }}">{{ $remainingCount }}</div><div class="tfs-stat-l">Missing</div></div>
            </div>
            <div style="margin-bottom:12px">
                <label class="tfs-field-label" for="tfs-receiver">Received by <span style="color:var(--red)">*</span></label>
                <input id="tfs-receiver" class="tfs-input" style="width:100%" wire:model="receivedByName" maxlength="120">
                @error('receivedByName')<div class="tfs-err">{{ $message }}</div>@enderror
            </div>
            <div style="margin-bottom:12px">
                <label class="tfs-field-label">Signature
                    @if(app(\App\Services\SettingsService::class)->transferRequireSignature())<span style="color:var(--red)">*</span>@else<span style="font-weight:500;color:var(--text-dim)">(optional)</span>@endif
                </label>
                <x-signature-pad model="receiptSignature" label="Sign to confirm" wire:key="sig-receipt-{{ $transfer->id }}" />
                @error('receiptSignature')<div class="tfs-err">{{ $message }}</div>@enderror
            </div>
            @if($damagedCount || $remainingCount)
                <p class="tfs-sheet-lead" style="margin:0">
                    @if($remainingCount){{ $remainingCount }} {{ Str::plural('box', $remainingCount) }} you didn't scan will be recorded as <strong>missing</strong>. @endif
                    The warehouse is told about the discrepancy.
                </p>
            @else
                <p class="tfs-sheet-lead" style="margin:0">Everything that was shipped arrived in good condition.</p>
            @endif
        </div>
        <div class="m-sheet-foot">
            <button type="button" class="tf-btn tf-btn-ghost" wire:click="$set('confirmComplete', false)">Keep scanning</button>
            <button type="button" class="tf-btn tf-btn-primary" wire:click="completeReceipt" wire:loading.attr="disabled" wire:target="completeReceipt">
                <span wire:loading.remove wire:target="completeReceipt">Complete receiving</span>
                <span wire:loading wire:target="completeReceipt" style="display:none">Saving…</span>
            </button>
        </div>
    </div>
@endif
</div>
@endif
</div>
