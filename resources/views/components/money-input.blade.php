{{--
    Money input: whole RWF, right-aligned, "12,500" while typing; the bound
    property receives "12500". Same component as <x-number-input> (see there
    for the options), kept as its own name so money fields are easy to find.

        <x-money-input wire:model="amount" class="ae-input" placeholder="0" />
        <x-money-input x-model="momo" class="upos-pay-input" />
--}}
<x-number-input {{ $attributes->class('money-input') }} />
