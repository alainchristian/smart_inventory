<?php

namespace App\Models;

use App\Enums\TransferStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transfer_number',
        'from_warehouse_id',
        'to_shop_id',
        'status',
        'requested_by',
        'requested_at',
        'reviewed_by',
        'reviewed_at',
        'packed_by',
        'packed_at',
        'transporter_id',
        'shipped_at',
        'delivered_at',
        'received_by',
        'received_at',
        'has_discrepancy',
        'discrepancy_notes',
        'notes',
    ];

    protected $casts = [
        'status' => TransferStatus::class,
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'received_at' => 'datetime',
        'has_discrepancy' => 'boolean',
    ];

    // Relationships
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'to_shop_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function packedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packed_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public function boxes(): HasMany
    {
        return $this->hasMany(TransferBox::class);
    }

    // Scopes
    public function scopeStatus($query, TransferStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', TransferStatus::PENDING);
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', TransferStatus::IN_TRANSIT);
    }

    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('from_warehouse_id', $warehouseId);
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('to_shop_id', $shopId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
