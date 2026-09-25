<?php

namespace App\Enums;

enum BoxStatus: string
{
    case FULL = 'full';
    case PARTIAL = 'partial';
    case DAMAGED = 'damaged';
    case EMPTY = 'empty';
    // On its way back to the warehouse (Return to warehouse) — not sellable anywhere
    case IN_TRANSIT = 'in_transit';

    public function label(): string
    {
        return match($this) {
            self::FULL => 'Full',
            self::PARTIAL => 'Partial',
            self::DAMAGED => 'Damaged',
            self::EMPTY => 'Empty',
            self::IN_TRANSIT => 'In transit',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::FULL => 'green',
            self::PARTIAL => 'yellow',
            self::DAMAGED => 'red',
            self::EMPTY => 'gray',
            self::IN_TRANSIT => 'blue',
        };
    }
}