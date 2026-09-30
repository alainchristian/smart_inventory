{{--
    Numeric input, filled from the right with thousand separators while typing.
    The box shows "12,500"; the bound property receives "12500" ('' when empty),
    exactly what a type="number" input sent, so PHP rules and casts don't change.
    Money fields use <x-money-input> (same component).

    Bind it like a normal input — the binding stays on the tag:
        <x-number-input wire:model="lowStockThreshold" class="pf-input" />
        <x-number-input wire:model.live.debounce.300ms="send.{{ $id }}" max="{{ $row->boxes }}" />
        <x-number-input x-model.number="qty" align="center" />
        <x-number-input wire:model="edit.threshold" decimals="2" signed />

    - decimals: digits allowed after "." (default 0)
    - signed:   allow a leading "-"
    - max:      clamped while typing (min is left to validation: typing 12 passes through 1)
    - align:    "right" (default) or "center" for steppers
    - .live.debounce.Nms is handled here: x-modelable skips x-model's own debounce,
      so the model is bound without it and the input commits once typing pauses.
    - Alpine bindings are written x-bind:attr / x-on:event (a bare :attr is PHP here).
--}}
@props(['decimals' => 0, 'signed' => false, 'align' => 'right'])
@php
    $options = ['decimals' => (int) $decimals, 'signed' => (bool) $signed];
    $max = $attributes->get('max');
    if (is_numeric($max)) {
        $options['max'] = $max + 0;
    }

    $attrs = [];
    foreach ($attributes->except(['type', 'min', 'max', 'step', 'inputmode'])->getAttributes() as $key => $value) {
        if (str_starts_with($key, 'wire:model') && preg_match('/\.debounce(?:\.(\d+)(ms|s)?)?/', $key, $m)) {
            $options['live'] = isset($m[1]) ? (int) $m[1] * (($m[2] ?? 'ms') === 's' ? 1000 : 1) : 150;
            $key = preg_replace('/\.(live|debounce)(\.\d+m?s)?/', '', $key);
        } elseif (str_starts_with($key, 'x-model')) {
            $key = preg_replace('/\.debounce(\.\d+m?s)?/', '', $key);
        }
        $attrs[$key] = $value;
    }
    $attributes = new \Illuminate\View\ComponentAttributeBag($attrs);
@endphp
<input type="text" inputmode="{{ $decimals > 0 ? 'decimal' : 'numeric' }}" autocomplete="off"
       x-data="numberInput({{ \Illuminate\Support\Js::from($options) }})" x-modelable="numValue"
       {{ $attributes->class(['num-input', 'num-center' => $align === 'center']) }}>
