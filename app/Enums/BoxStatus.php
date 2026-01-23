<?php

namespace App\Enums;

enum BoxStatus: string
{
    case FULL = 'full';
    case PARTIAL = 'partial';
    case DAMAGED = 'damaged';
    case EMPTY = 'empty';

    public function label(): string
    {
        return match($this) {
            self::FULL => 'Full',
            self::PARTIAL => 'Partial',
            self::DAMAGED => 'Damaged',
            self::EMPTY => 'Empty',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::FULL => 'green',
            self::PARTIAL => 'yellow',
            self::DAMAGED => 'red',
            self::EMPTY => 'gray',
        };
    }
}