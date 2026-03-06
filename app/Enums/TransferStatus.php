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
            self::PENDING    => 'bg-amber-100 text-amber-800 border border-amber-200',
            self::APPROVED   => 'bg-blue-100 text-blue-800 border border-blue-200',
            self::REJECTED   => 'bg-red-100 text-red-800 border border-red-200',
            self::IN_TRANSIT => 'bg-violet-100 text-violet-800 border border-violet-200',
            self::DELIVERED  => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
            self::RECEIVED   => 'bg-green-100 text-green-800 border border-green-200',
            self::CANCELLED  => 'bg-gray-100 text-gray-600 border border-gray-200',
        };
    }

    public function cssColor(): string
    {
        return match($this) {
            self::PENDING    => 'var(--amber)',
            self::APPROVED   => 'var(--accent)',
            self::REJECTED   => 'var(--red)',
            self::IN_TRANSIT => 'var(--violet)',
            self::DELIVERED  => '#0ea5e9',
            self::RECEIVED   => 'var(--green)',
            self::CANCELLED  => 'var(--text-dim)',
        };
    }
}