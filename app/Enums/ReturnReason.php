<?php

namespace App\Enums;

enum ReturnReason: string
{
    case DEFECTIVE = 'defective';
    case WRONG_ITEM = 'wrong_item';
    case DAMAGED = 'damaged';
    case EXPIRED = 'expired';
    case CUSTOMER_REQUEST = 'customer_request';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::DEFECTIVE => 'Defective',
            self::WRONG_ITEM => 'Wrong Item',
            self::DAMAGED => 'Damaged',
            self::EXPIRED => 'Expired',
            self::CUSTOMER_REQUEST => 'Customer Request',
            self::OTHER => 'Other',
        };
    }
}