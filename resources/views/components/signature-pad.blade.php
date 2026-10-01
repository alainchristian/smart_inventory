{{--
    Signature pad. The PNG data URL lands in the Livewire property `model`
    ('' when cleared). Alpine: signaturePad in resources/js/app.js.

        <x-signature-pad model="handoverSignature" label="Driver signs here" />
--}}
@props(['model', 'label' => 'Sign here'])
@once
<style>
.sigpad { position:relative }
.sigpad-canvas { width:100%;height:140px;display:block;border:1.5px dashed var(--border-hi);border-radius:9px;background:var(--surface);
                 touch-action:none;cursor:crosshair }
.sigpad-hint { position:absolute;left:12px;bottom:8px;font-size:11px;color:var(--text-dim);pointer-events:none }
.sigpad-clear { position:absolute;top:8px;right:8px;padding:3px 9px;border-radius:6px;border:1px solid var(--border);background:var(--surface);
                color:var(--text-sub);font:600 11px var(--font);cursor:pointer }
.sigpad-clear:hover { border-color:var(--red);color:var(--red) }
</style>
@endonce
<div {{ $attributes->class('sigpad') }} wire:ignore x-data="signaturePad(@js($model))">
    <canvas x-ref="canvas" class="sigpad-canvas" width="880" height="280" aria-label="{{ $label }}"
            x-on:pointerdown="start($event)" x-on:pointermove="move($event)" x-on:pointerup="end()" x-on:pointerleave="end()"></canvas>
    <span class="sigpad-hint" x-show="empty">{{ $label }}</span>
    <button type="button" class="sigpad-clear" x-on:click="clear()" x-show="!empty">Clear</button>
</div>
