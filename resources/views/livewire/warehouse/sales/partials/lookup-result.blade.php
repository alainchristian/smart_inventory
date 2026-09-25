{{-- A looked-up order that is NOT pending (already dispatched / cancelled).
     Expects: $sale. Optional: $clearable (show "Clear" for the scan result). --}}
@php $clearable = $clearable ?? false; @endphp

@if($sale->fulfillment_status === 'fulfilled')
    <div class="fq-notice" style="border-left-color:var(--green)" wire:key="res-{{ $sale->id }}">
        <div class="fq-notice-main">
            <div class="fq-notice-t" style="color:var(--green)">Already dispatched</div>
            <div class="fq-notice-s">
                <span style="font-family:var(--mono);font-weight:700;color:var(--text)">{{ $sale->sale_number }}</span>
                went to {{ $sale->fulfillment_recipient_name ?? $sale->fulfillmentTransporter?->name ?? 'the customer' }}
                on {{ local_time($sale->fulfillment_confirmed_at)?->format('d M, H:i') }}@if($sale->fulfillmentConfirmedBy) · confirmed by {{ $sale->fulfillmentConfirmedBy->name }}@endif.
            </div>
        </div>
        @if($sale->fulfillment_signature)
            <img src="{{ $sale->fulfillment_signature }}" alt="Recipient signature" class="fq-notice-sig">
        @endif
        @if($clearable)
            <button type="button" class="fq-link" wire:click="clearScan">Clear</button>
        @endif
    </div>
@else
    <div class="fq-notice" style="border-left-color:var(--red)" wire:key="res-{{ $sale->id }}">
        <div class="fq-notice-main">
            <div class="fq-notice-t" style="color:var(--red)">Cancelled — do not dispatch</div>
            <div class="fq-notice-s"><span style="font-family:var(--mono);font-weight:700;color:var(--text)">{{ $sale->sale_number }}</span> was cancelled after it was sold.</div>
        </div>
        @if($clearable)
            <button type="button" class="fq-link" wire:click="clearScan">Clear</button>
        @endif
    </div>
@endif
