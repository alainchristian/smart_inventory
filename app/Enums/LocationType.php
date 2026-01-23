<?php

namespace App\Enums;

enum LocationType: string
{
    case WAREHOUSE = 'warehouse';
    case SHOP = 'shop';

    public function label(): string
    {
        return match($this) {
            self::WAREHOUSE => 'Warehouse',
            self::SHOP => 'Shop',
        };
    }

    public function modelClass(): string
    {
        return match($this) {
            self::WAREHOUSE => \App\Models\Warehouse::class,
            self::SHOP => \App\Models\Shop::class,
        };
    }
}
