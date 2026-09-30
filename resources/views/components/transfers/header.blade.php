{{--
    Transfer page header: optional back button, title (mono for a transfer
    number), subtitle line, and actions on the right.

    <x-transfers.header title="Transfers" sub="All shops · all warehouses">
        <x-slot:actions><a class="tf-btn tf-btn-primary" href="…">New request</a></x-slot:actions>
    </x-transfers.header>
--}}
@props(['title', 'sub' => null, 'back' => null, 'mono' => false, 'dupTitle' => false])
<x-transfers.styles />
<div {{ $attributes->class(['tf-head', 'm-page-head']) }}>
    <div class="tf-head-main">
        @if($back)
            <a href="{{ $back }}" wire:navigate class="tf-back m-tap" aria-label="Back">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
        @endif
        <div style="min-width:0">
            <h1 @class(['tf-title', 'tf-title-mono' => $mono, 'm-dup-title' => $dupTitle])>{{ $title }}</h1>
            @if($sub || isset($meta))
                <div class="tf-sub">{{ $meta ?? $sub }}</div>
            @endif
        </div>
    </div>
    @isset($actions)
        <div class="tf-head-actions">{{ $actions }}</div>
    @endisset
</div>
