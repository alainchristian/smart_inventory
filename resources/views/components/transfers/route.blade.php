{{--
    "Warehouse → Shop" on one line, never clipped (the parent scrolls or
    wraps). The shop is emphasised: it's what everyone scans for.
--}}
@props(['from', 'to'])
<x-transfers.styles />
<span {{ $attributes->class('tf-route') }}>
    <span class="tf-route-node" title="From">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21V8l9-5 9 5v13M9 21v-6h6v6"/></svg>
        {{ $from }}
    </span>
    <svg class="tf-route-arrow" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
    <span class="tf-route-node tf-route-to" title="To">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 9l1-5h14l1 5M4 9v11h16V9M4 9h16M9 20v-6h6v6"/></svg>
        {{ $to }}
    </span>
</span>
