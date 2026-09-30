{{--
    Scan field for pack / receive: Enter or the button calls $action; the
    field refocuses after the quantity prompt closes. Feedback comes from the
    component's scan_success / scan_error / info / error flashes.
--}}
@props(['action', 'label', 'placeholder', 'button' => 'Add'])
<x-transfers.scan-styles />
<div class="tfs-card tfs-scan">
    <label class="tfs-scan-label" for="tfs-scan-input">{{ $label }}</label>
    <div class="tfs-scan-row">
        <input id="tfs-scan-input" type="text" class="tfs-scan-input" wire:model="scanInput" wire:keydown.enter="{{ $action }}"
               placeholder="{{ $placeholder }}" autocomplete="off" autofocus
               x-data x-on:quantity-confirmed.window="setTimeout(() => { $el.focus(); $el.select() }, 60)">
        <button type="button" class="tf-btn tf-btn-primary" wire:click="{{ $action }}" wire:loading.attr="disabled" wire:target="{{ $action }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7V5a1 1 0 011-1h2M17 4h2a1 1 0 011 1v2M20 17v2a1 1 0 01-1 1h-2M7 20H5a1 1 0 01-1-1v-2M8 8v8M11 8v8M14 8v8M17 8v8"/></svg>
            {{ $button }}
        </button>
    </div>
    @foreach(['scan_success' => 'ok', 'scan_error' => 'bad', 'error' => 'bad', 'info' => 'info'] as $key => $tone)
        @if(session($key))
            <div class="tfs-feedback {{ $tone }}" role="status">
                @if($tone === 'ok')
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                @else
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v4M12 16h.01"/></svg>
                @endif
                {{ session($key) }}
            </div>
        @endif
    @endforeach
</div>
