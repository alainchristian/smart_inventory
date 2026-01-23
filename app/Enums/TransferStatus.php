<?php

namespace App\Enums;

enum TransferStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::IN_TRANSIT => 'In Transit',
            self::DELIVERED => 'Delivered',
            self::RECEIVED => 'Received',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::APPROVED => 'blue',
            self::REJECTED => 'red',
            self::IN_TRANSIT => 'purple',
            self::DELIVERED => 'indigo',
            self::RECEIVED => 'green',
            self::CANCELLED => 'gray',
        };
    }
}