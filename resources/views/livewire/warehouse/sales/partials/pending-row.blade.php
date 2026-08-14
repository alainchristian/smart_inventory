{{-- Pending warehouse-direct sale card + confirm strip. Expects: $sale --}}
@php
    $whItems    = $sale->items->filter(fn($i) => $i->box?->location_type?->value === 'warehouse');
    $byProduct  = $whItems->groupBy(fn($i) => $i->product_id)->map(fn($g) => [
        'name'  => $g->first()->product?->name ?? '—',
        'boxes' => $g->count(),
    ]);
    $paidFull   = $sale->total > 0 && $sale->payments->sum('amount') >= $sale->total;
    $ageMin     = (int) $sale->sale_date->diffInMinutes(now());
    $urgency    = $ageMin >= 120 ? 'red' : ($ageMin >= 30 ? 'amber' : '');
    $ageBadge   = $ageMin >= 120 ? 'fq-age-red' : ($ageMin >= 30 ? 'fq-age-amber' : 'fq-age-ok');
    $ageLabel   = $ageMin < 60
        ? "{$ageMin}m ago"
        : floor($ageMin / 60).'h '.str_pad($ageMin % 60, 2, '0').'m';
    $confirming = $confirmingFulfillmentId === $sale->id;
@endphp

<div class="fq-card" wire:key="card-{{ $sale->id }}">
    <div class="fq-urgency {{ $urgency }}"></div>

    {{-- Main body (always visible) --}}
    <div class="fq-body">
        <div class="fq-row-top">
            <span class="fq-ref">{{ $sale->sale_number }}</span>
            <span class="fq-shop">
                {{ $sale->shop?->name ?? '—' }}
                @if($sale->customer_name)
                    &middot; {{ $sale->customer_name }}
                @endif
                &middot; {{ local_time($sale->sale_date)->format('d M, H:i') }}
            </span>
            <span class="fq-age {{ $ageBadge }}">{{ $ageLabel }}</span>
        </div>

        <div class="fq-prods">
            @foreach($byProduct as $prod)
                <span style="font-weight:600">{{ $prod['name'] }}</span>@if($prod['boxes'] > 1)<span class="fq-prod-qty"> &times;{{ $prod['boxes'] }}</span>@endif@if(!$loop->last)<span class="fq-dot">&middot;</span>@endif
            @endforeach
        </div>

        @if(!$confirming)
        <div class="fq-act">
            <div class="fq-via">
                @if($sale->fulfillment_method === 'transporter')
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    Via&nbsp;<b>{{ $sale->fulfillmentTransporter?->name ?? 'Transporter' }}</b>
                @else
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <b>Customer Pickup</b>
                @endif
                @if(!$paidFull)
                    <span class="fq-outstanding" style="margin-left:6px">Balance outstanding</span>
                @endif
            </div>
            <button class="fq-btn-dispatch" wire:click="requestFulfillment({{ $sale->id }})">
                Confirm Dispatch
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
        @endif
    </div>

    {{-- Confirm strip --}}
    @if($confirming)
    <div class="fq-confirm">
        <div class="fq-confirm-body">
            <div>
                <div class="fq-confirm-msg">
                    Hand over <strong>{{ $whItems->count() }} {{ $whItems->count() === 1 ? 'box' : 'boxes' }}</strong>
                    to {{ $sale->fulfillment_method === 'transporter'
                        ? ($sale->fulfillmentTransporter?->name ?? 'transporter')
                        : 'customer' }}?
                </div>
                <div class="fq-confirm-sub">This action is permanent and cannot be undone.</div>
            </div>
            <div class="fq-confirm-field">
                <label class="fq-confirm-label">
                    {{ $sale->fulfillment_method === 'transporter' ? 'Transporter rep. name' : 'Who is picking this up?' }}
                </label>
                <input type="text" class="fq-confirm-input" wire:model="recipientName"
                       placeholder="{{ $sale->fulfillment_method === 'transporter' ? 'Name of driver/agent collecting' : 'Name of person collecting' }}"
                       wire:key="recipient-{{ $sale->id }}">
                @error('recipientName') <span class="fq-confirm-error">{{ $message }}</span> @enderror
            </div>
            <div class="fq-confirm-field">
                <label class="fq-confirm-label">Signature</label>
                <div wire:ignore wire:key="sig-wrap-{{ $sale->id }}">
                    <canvas id="sig-{{ $sale->id }}" class="fq-sig-canvas"
                            data-sig-canvas width="400" height="140"></canvas>
                    <button type="button" class="fq-sig-clear" data-sig-clear="sig-{{ $sale->id }}">Clear</button>
                </div>
                {{-- Outside the wire:ignore subtree — a validation error must still
                     render even though the canvas above is frozen from re-renders. --}}
                @error('signatureData') <span class="fq-confirm-error">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="fq-confirm-btns">
            <button class="fq-btn-cancel" wire:click="cancelFulfillment">Cancel</button>
            <button class="fq-btn-yes" wire:click="markFulfilled({{ $sale->id }})">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Yes, Dispatched
            </button>
        </div>
    </div>
    @endif

</div>
