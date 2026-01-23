<?php

namespace App\Enums;

enum DispositionType: string
{
    case PENDING = 'pending';
    case RETURN_TO_SUPPLIER = 'return_to_supplier';
    case DISPOSE = 'dispose';
    case DISCOUNT_SALE = 'discount_sale';
    case WRITE_OFF = 'write_off';
    case REPAIR = 'repair';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::RETURN_TO_SUPPLIER => 'Return to Supplier',
            self::DISPOSE => 'Dispose',
            self::DISCOUNT_SALE => 'Discount Sale',
            self::WRITE_OFF => 'Write Off',
            self::REPAIR => 'Repair',
        };
    }
}