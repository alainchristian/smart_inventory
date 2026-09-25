<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Shop → warehouse return of boxes (reverse transfer). Change state only
 * through App\Services\Inventory\StockReturnService.
 */
class StockReturn extends Model
{
    public const IN_TRANSIT = 'in_transit';
    public const RECEIVED   = 'received';
    public const CANCELLED  = 'cancelled';

    protected $fillable = [
        'return_number', 'shop_id', 'warehouse_id', 'status', 'reason',
        'sent_by', 'sent_at', 'received_by', 'received_at', 'receipt_notes', 'has_discrepancy',
        'cancelled_by', 'cancelled_at', 'cancel_reason',
    ];

    protected $casts = [
        'sent_at'         => 'datetime',
        'received_at'     => 'datetime',
        'cancelled_at'    => 'datetime',
        'has_discrepancy' => 'boolean',
    ];

    public function shop(): BelongsTo      { return $this->belongsTo(Shop::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function sentBy(): BelongsTo    { return $this->belongsTo(User::class, 'sent_by'); }
    public function receivedBy(): BelongsTo { return $this->belongsTo(User::class, 'received_by'); }
    public function cancelledBy(): BelongsTo { return $this->belongsTo(User::class, 'cancelled_by'); }
    public function boxes(): HasMany       { return $this->hasMany(StockReturnBox::class); }

    public function isInTransit(): bool { return $this->status === self::IN_TRANSIT; }
}
