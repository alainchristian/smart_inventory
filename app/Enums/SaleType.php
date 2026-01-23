<?php

namespace App\Enums;

enum SaleType: string
{
    case FULL_BOX = 'full_box';
    case INDIVIDUAL_ITEMS = 'individual_items';
    case MIXED = 'mixed';

    public function label(): string
    {
        return match($this) {
            self::FULL_BOX => 'Full Box',
            self::INDIVIDUAL_ITEMS => 'Individual Items',
            self::MIXED => 'Mixed',
        };
    }
}