{{-- One pending warehouse-direct order as a list row. Expects: $sale.
     Optional: $source ('queue'|'scan') — tags how this row was reached, carried
     through to the ActivityLog entry on confirm. Defaults to 'scan'.
     Dispatch confirmation happens in the component's modal, not inline. --}}
@php
    $source    = $source ?? 'scan';
    $whItems   = \App\Livewire\Warehouse\Sales\FulfillmentQueue::warehouseBoxes($sale);
    $byProduct = $whItems->groupBy(fn ($i) => $i->product_id)->map(fn ($g) => [
        'name'  => $g->first()->product?->name ?? '—',
        'boxes' => $g->count(),
    ]);
    $paidFull  = \App\Livewire\Warehouse\Sales\FulfillmentQueue::isPaidInFull($sale);
    $ageMin    = (int) $sale->sale_date->diffInMinutes(now());
    $ageTone   = $ageMin >= 120 ? 'red' : ($ageMin >= 30 ? 'amber' : 'dim');
    $ageLabel  = $ageMin < 60 ? "{$ageMin}m" : floor($ageMin / 60) . 'h ' . str_pad($ageMin % 60, 2, '0', STR_PAD_LEFT) . 'm';
@endphp

<div class="fq-row" wire:key="row-{{ $sale->id }}">
    <div class="fq-c-ref">
        <div class="fq-ref">
            <span class="fq-dot" style="background:var(--{{ $ageTone === 'dim' ? 'border-hi' : $ageTone }})"></span>
            {{ $sale->sale_number }}
        </div>
        <div class="fq-sub">
            {{ local_time($sale->sale_date)->format('d M, H:i') }} ·
            <span style="color:var(--{{ $ageTone === 'dim' ? 'text-dim' : $ageTone }});font-weight:{{ $ageTone === 'dim' ? 500 : 700 }}">{{ $ageLabel }} waiting</span>
        </div>
    </div>

    <div class="fq-c-cust">
        <div class="fq-main fq-ellip">{{ $sale->customer_name ?: 'Walk-in customer' }}</div>
        <div class="fq-sub fq-ellip">
            {{ $sale->shop?->name ?? '—' }}@if($sale->customer_phone) · <span style="font-family:var(--mono)">{{ $sale->customer_phone }}</span>@endif
        </div>
    </div>

    <div class="fq-c-items">
        <div class="fq-main fq-ellip" title="{{ $byProduct->map(fn ($p) => $p['name'] . ' ×' . $p['boxes'])->implode(', ') }}">
            @foreach($byProduct as $prod){{ $prod['name'] }}@if($prod['boxes'] > 1) <span class="fq-x">×{{ $prod['boxes'] }}</span>@endif{{ $loop->last ? '' : ', ' }}@endforeach
        </div>
        <div class="fq-sub">
            {{ $whItems->count() }} {{ $whItems->count() === 1 ? 'box' : 'boxes' }}
            @if($sale->fulfillment_notes) · <span class="fq-note" title="{{ $sale->fulfillment_notes }}">{{ $sale->fulfillment_notes }}</span>@endif
        </div>
    </div>

    <div class="fq-c-via">
        @if($sale->fulfillment_method === 'transporter')
            <span class="fq-badge" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                {{ $sale->fulfillmentTransporter?->name ?? 'Transporter' }}
            </span>
        @else
            <span class="fq-badge" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Customer pickup
            </span>
        @endif
        @unless($paidFull)
            <span class="fq-badge" style="background:var(--amber-dim);color:var(--amber)" title="Money received doesn't cover the total — includes credit sales">Balance due</span>
        @endunless
    </div>

    <div class="fq-c-act">
        <a class="fq-icon-btn" href="{{ route('warehouse.sales.fulfillment.picking-slip', $sale->id) }}" target="_blank" rel="noopener"
           title="Print picking slip" aria-label="Print picking slip for {{ $sale->sale_number }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        </a>
        <button type="button" class="fq-btn fq-btn-primary fq-btn-sm" wire:click="requestFulfillment({{ $sale->id }}, '{{ $source }}')">
            Dispatch
        </button>
    </div>
</div>
