{{--
    Pack an approved transfer (Inventory\Transfers\PackTransfer). Shares the
    scan layout with Receive: <x-transfers.scanner>, tfs- styles, sticky bar.
--}}
<div class="tfs-page" style="font-family:var(--font)">
<x-transfers.styles />
<x-transfers.scan-styles />

@php
    $summary = collect($packingSummary)->sortBy('complete')->values();
    $needed = $summary->sum('boxes_needed');
    $packed = $summary->sum('boxes_packed');
    $itemsPacked = collect($packedBoxes)->sum('items');
    $short = $summary->filter(fn ($r) => $r['boxes_packed'] < $r['boxes_needed']);
@endphp

<x-transfers.header :title="'Pack ' . $transfer->transfer_number" mono :back="route('warehouse.transfers.show', $transfer)">
    <x-slot:meta>
        <span style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <x-transfers.status :status="$transfer->status" />
            <x-transfers.route :from="$transfer->fromWarehouse?->name ?? '—'" :to="$transfer->toShop?->name ?? '—'" />
        </span>
    </x-slot:meta>
</x-transfers.header>

<x-transfers.scanner action="scanProduct" label="Scan a product barcode" placeholder="Scan or type a barcode, then Enter" button="Pack" />

{{-- ═══ Progress per product ═══════════════════════════════════════ --}}
<div class="tfs-card">
    <div class="tfs-card-head">
        <div>
            <h2 class="tfs-card-title">To pack</h2>
            <div class="tfs-card-sub">Unfinished products first · the warehouse picks the oldest boxes</div>
        </div>
        <span class="tfs-pill">{{ $packed }} / {{ $needed }} boxes</span>
    </div>
    <div class="m-scroll">
        <table class="tfs-table m-sticky-first" style="min-width:600px">
            <thead><tr><th>Product</th><th class="tfs-r">Packed</th><th>Progress</th><th></th><th>Barcode</th></tr></thead>
            <tbody>
                @foreach($summary as $row)
                    @php $pct = $row['boxes_needed'] > 0 ? min(100, round($row['boxes_packed'] / $row['boxes_needed'] * 100)) : 0; @endphp
                    <tr class="{{ $row['complete'] ? 'done' : '' }}" wire:key="need-{{ $row['product_id'] }}">
                        <td><span class="tfs-prod">{{ $row['product_name'] }}</span></td>
                        <td class="tfs-r"><span class="tfs-n">{{ $row['boxes_packed'] }}</span> <span class="tfs-mono">/ {{ $row['boxes_needed'] }}</span></td>
                        <td style="width:30%"><div class="tfs-bar {{ $row['complete'] ? 'full' : '' }}"><span style="width:{{ $pct }}%"></span></div></td>
                        <td class="tfs-r">
                            @if($row['complete'])
                                <span class="tfs-state" style="background:var(--green-dim);color:var(--green)">Done</span>
                            @else
                                <span class="tfs-state" style="background:var(--amber-dim);color:var(--amber)">{{ $row['boxes_needed'] - $row['boxes_packed'] }} to go</span>
                            @endif
                        </td>
                        <td><span class="tfs-mono">{{ $row['barcode'] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ═══ Packed boxes ═══════════════════════════════════════════════ --}}
<div class="tfs-card">
    <div class="tfs-card-head">
        <div>
            <h2 class="tfs-card-title">Packed boxes</h2>
            <div class="tfs-card-sub">Remove a box that was packed by mistake — it goes back into warehouse stock</div>
        </div>
        <span class="tfs-pill">{{ count($packedBoxes) }}</span>
    </div>
    @if(empty($packedBoxes))
        <div class="tfs-empty">Nothing packed yet. Scan a product barcode above.</div>
    @else
        <div class="m-scroll">
            <table class="tfs-table m-sticky-first" style="min-width:520px">
                <thead><tr><th>Box</th><th>Product</th><th class="tfs-r">Items</th><th></th></tr></thead>
                <tbody>
                    @foreach(array_reverse($packedBoxes) as $box)
                        <tr wire:key="packed-{{ $box['box_id'] }}">
                            <td><span class="tfs-code">{{ $box['box_code'] }}</span></td>
                            <td>{{ $box['product_name'] }}</td>
                            <td class="tfs-r"><span class="tfs-n">{{ number_format($box['items']) }}</span></td>
                            <td class="tfs-r">
                                <button type="button" class="tfs-act danger" wire:click="removeBox({{ $box['box_id'] }})"
                                        wire:loading.attr="disabled" wire:target="removeBox({{ $box['box_id'] }})">Remove</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ═══ Sticky action bar ══════════════════════════════════════════ --}}
<div class="tfs-actionbar">
    <div class="tfs-stats">
        <div><div class="tfs-stat-v">{{ $packed }}<span style="font-size:12px;color:var(--text-dim)"> / {{ $needed }}</span></div><div class="tfs-stat-l">Boxes packed</div></div>
        <div><div class="tfs-stat-v">{{ number_format($itemsPacked) }}</div><div class="tfs-stat-l">Items</div></div>
        <div><div class="tfs-stat-v">{{ $summary->where('complete', true)->count() }}<span style="font-size:12px;color:var(--text-dim)"> / {{ $summary->count() }}</span></div><div class="tfs-stat-l">Products done</div></div>
    </div>
    <div class="tfs-actionbar-go">
        <div>
            <label class="tfs-field-label" for="tfs-transporter">Transporter</label>
            <input id="tfs-transporter" class="tfs-input" wire:model="transporterInput" list="tfs-transporters" placeholder="Choose or type a name" autocomplete="off">
            <datalist id="tfs-transporters">
                @foreach($transporters as $tr)
                    <option value="{{ $tr->name }}">{{ $tr->vehicle_number }}</option>
                @endforeach
            </datalist>
            @error('transporterInput')<div class="tfs-err">{{ $message }}</div>@enderror
        </div>
        <button type="button" class="tf-btn tf-btn-primary" wire:click="openShip" @disabled(empty($packedBoxes))>
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linejoin="round" d="M1 3h15v13H1zM16 8h4l3 3v5h-7"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            Ship transfer
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
            <p class="tfs-sheet-lead">How many boxes? {{ $pendingAlreadyAssigned }} packed so far, <strong style="color:var(--text)">{{ $pendingMaxQty }} still needed</strong>.</p>
            <x-number-input wire:model.live="pendingQty" wire:keydown.enter="confirmScannedQuantity" max="{{ $pendingMaxQty }}"
                            x-init="$nextTick(() => $el.select())" class="tfs-qty" align="center" aria-label="Boxes to pack" />
            @error('pendingQty')<div class="tfs-err" style="text-align:center">{{ $message }}</div>@enderror
            <p class="tfs-sheet-lead" style="text-align:center;margin:12px 0 0"><kbd class="tfs-kbd">Enter</kbd> to pack · <kbd class="tfs-kbd">Esc</kbd> to cancel</p>
        </div>
        <div class="m-sheet-foot">
            <button type="button" class="tf-btn tf-btn-ghost" wire:click="closeQuantityPanel">Cancel</button>
            <button type="button" class="tf-btn tf-btn-primary" wire:click="confirmScannedQuantity">Pack {{ (int) $pendingQty }} {{ Str::plural('box', (int) $pendingQty) }}</button>
        </div>
    </div>
@endif

{{-- ═══ Ship confirmation ══════════════════════════════════════════ --}}
@if($confirmShip)
    <div class="m-sheet-overlay" wire:click="$set('confirmShip', false)"></div>
    <div class="m-sheet" role="dialog" aria-modal="true" aria-labelledby="tfs-ship-title">
        <div class="m-sheet-handle"></div>
        <div class="m-sheet-head">
            <h2 class="m-sheet-title" id="tfs-ship-title">Ship {{ $transfer->transfer_number }}?</h2>
            <button type="button" class="tfs-x m-tap" wire:click="$set('confirmShip', false)" aria-label="Close">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="m-sheet-body">
            <p class="tfs-sheet-lead">{{ $packed }} {{ Str::plural('box', $packed) }} ({{ number_format($itemsPacked) }} items) leave with <strong style="color:var(--text)">{{ $transporterInput }}</strong> for {{ $transfer->toShop?->name }}. Packing can't be changed after this.</p>
            @if($short->isNotEmpty())
                <div class="tfs-feedback bad" style="margin:0 0 6px">Shipping short — the shop will see these as not sent:</div>
                <ul class="tfs-list">
                    @foreach($short as $row)
                        <li><span>{{ $row['product_name'] }}</span><strong style="color:var(--red)">{{ $row['boxes_needed'] - $row['boxes_packed'] }} {{ Str::plural('box', $row['boxes_needed'] - $row['boxes_packed']) }} short</strong></li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="m-sheet-foot">
            <button type="button" class="tf-btn tf-btn-ghost" wire:click="$set('confirmShip', false)">Keep packing</button>
            <button type="button" class="tf-btn tf-btn-primary" wire:click="shipTransfer" wire:loading.attr="disabled" wire:target="shipTransfer">
                <span wire:loading.remove wire:target="shipTransfer">{{ $short->isNotEmpty() ? 'Ship anyway' : 'Ship now' }}</span>
                <span wire:loading wire:target="shipTransfer" style="display:none">Shipping…</span>
            </button>
        </div>
    </div>
@endif
</div>
