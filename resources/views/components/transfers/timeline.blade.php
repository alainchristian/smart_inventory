{{--
    Transfer progress: Requested → Approved → Packed → Shipped → Delivered →
    Received, with who did each step and when (Transfer::timeline()).
    Horizontal on desktop, vertical on phones.
--}}
@props(['transfer'])
@php
    $steps = $transfer->timeline();
    $count = count($steps);
@endphp
<x-transfers.styles />
<ol {{ $attributes->class('tf-steps') }} style="--tf-steps:{{ $count }}" aria-label="Transfer progress">
    @foreach($steps as $i => $step)
        @php $next = $steps[$i + 1]['state'] ?? null; @endphp
        <li @class(['tf-step', $step['state'], 'next-done' => in_array($next, ['done', 'current'], true), 'next-stopped' => $next === 'stopped'])
            @if($step['state'] === 'current') aria-current="step" @endif>
            <span class="tf-step-dot">
                @if($step['state'] === 'done')
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                @elseif($step['state'] === 'stopped')
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                @endif
            </span>
            <div class="tf-step-label">{{ $step['label'] }}</div>
            @if($step['who'])<div class="tf-step-who" title="{{ $step['who'] }}">{{ $step['who'] }}</div>@endif
            @if($step['at'])<div class="tf-step-when">{{ local_time($step['at'])->format('d M · H:i') }}</div>@endif
        </li>
    @endforeach
</ol>
