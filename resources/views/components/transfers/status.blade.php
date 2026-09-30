{{-- Transfer status badge, coloured by TransferStatus::tone(). --}}
@props(['status'])
@php $status = $status instanceof \App\Enums\TransferStatus ? $status : \App\Enums\TransferStatus::from($status); @endphp
<x-transfers.styles />
<span {{ $attributes->class(['tf-badge', 'tf-tone-' . $status->tone()]) }}>
    <span class="tf-badge-dot"></span>{{ $status->label() }}
</span>
