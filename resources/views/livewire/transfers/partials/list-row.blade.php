{{--
    One transfer row. $t (with toShop, fromWarehouse, requestedBy, items_count,
    boxes_requested), $role owner|shop|warehouse, $waiting (bool: "Needs you"
    table shows how long it has waited instead of the last event).
--}}
@use('App\Enums\TransferStatus', 'S')
@php
    $showRoute = [
        'owner'     => route('owner.transfers.show', $t),
        'shop'      => route('shop.transfers.show', $t),
        'warehouse' => route('warehouse.transfers.show', $t),
    ][$role];
    $noteRoute = $role === 'shop' ? null : route($role === 'owner' ? 'owner.transfers.delivery-note' : 'warehouse.transfers.delivery-note', $t);
    $shipped = in_array($t->status, [S::IN_TRANSIT, S::DELIVERED, S::RECEIVED], true);
    $event = $t->lastEvent();
    $boxes = (int) $t->boxes_requested;
@endphp
<tr wire:key="tfl-{{ $waiting ? 'n' : 'r' }}-{{ $t->id }}">
    <td>
        <a href="{{ $showRoute }}" wire:navigate class="tfl-num">{{ $t->transfer_number }}</a>
        <div class="tfl-sub m-hide">{{ $t->items_count }} {{ Str::plural('product', $t->items_count) }}</div>
        {{-- Phones: the status rides in the frozen first column --}}
        <div class="m-only" style="margin-top:4px"><x-transfers.status :status="$t->status" /></div>
    </td>
    @php
        // The shop is what owner and warehouse scan for; the shop sees where it comes from.
        $place = $role === 'shop' ? ($t->fromWarehouse?->name ?? '—') : ($t->toShop?->name ?? '—');
    @endphp
    <td title="{{ $place }}">
        <span class="tfl-place">{{ $place }}</span>
        @if($role === 'owner')
            <div class="tfl-sub">from {{ $t->fromWarehouse?->name ?? '—' }}</div>
        @endif
    </td>
    <td class="tfl-num-cell"><span class="tfl-mono">{{ number_format($boxes) }}</span> <span class="tfl-unit">{{ Str::plural('box', $boxes) }}</span></td>
    <td>
        <x-transfers.status :status="$t->status" />
        @if($t->has_discrepancy)
            <div class="tfl-sub" style="color:var(--red);font-weight:600" title="Missing or damaged boxes on delivery">Discrepancy</div>
        @endif
    </td>
    <td>
        <div class="tfl-date">{{ local_time($t->requested_at)?->format('d M · H:i') }}</div>
        <div class="tfl-sub">{{ $t->requestedBy?->name ?? '—' }}</div>
    </td>
    <td>
        @if($waiting)
            @php $since = $t->status === S::APPROVED ? ($t->reviewed_at ?? $t->requested_at) : $t->requested_at; @endphp
            <div class="tfl-date">{{ $t->status === S::APPROVED ? ($t->packed_at ? 'Packing started' : 'Waiting to pack') : 'Waiting for approval' }}</div>
            <div class="tfl-sub">for {{ $since->diffForHumans(null, true) }}</div>
        @else
            <div class="tfl-date">{{ $event['label'] }}</div>
            <div class="tfl-sub">{{ local_time($event['at'])?->format('d M · H:i') }}</div>
        @endif
    </td>
    <td>
        <div class="tfl-acts">
            @if($role === 'warehouse' && $t->status === S::PENDING)
                <a href="{{ $showRoute }}" wire:navigate class="tf-btn tf-btn-primary tf-btn-sm">Review</a>
            @elseif($role === 'warehouse' && $t->status === S::APPROVED)
                <a href="{{ route('warehouse.transfers.pack', $t) }}" wire:navigate class="tf-btn tf-btn-primary tf-btn-sm">{{ $t->packed_at ? 'Continue packing' : 'Pack' }}</a>
            @elseif($role === 'shop' && in_array($t->status, [S::IN_TRANSIT, S::DELIVERED], true))
                <a href="{{ route('shop.transfers.receive', $t) }}" wire:navigate class="tf-btn tf-btn-primary tf-btn-sm">Receive</a>
            @else
                <a href="{{ $showRoute }}" wire:navigate class="tfl-action">View</a>
            @endif
            @if($noteRoute && ! $shipped)
                <span class="tfl-ico" aria-hidden="true"></span>{{-- keeps buttons aligned --}}
            @endif
            @if($noteRoute && $shipped)
                <a href="{{ $noteRoute }}" target="_blank" class="tfl-ico m-tap" title="Delivery note" aria-label="Delivery note for {{ $t->transfer_number }}">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </a>
            @endif
        </div>
    </td>
</tr>
