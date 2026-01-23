<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case MOBILE_MONEY = 'mobile_money';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT = 'credit';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Cash',
            self::CARD => 'Card',
            self::MOBILE_MONEY => 'Mobile Money',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CREDIT => 'Credit',
        };
    }
}