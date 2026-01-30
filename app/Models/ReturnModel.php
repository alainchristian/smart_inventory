<?php

namespace App\Models;

use App\Enums\ReturnReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'returns';

    protected $fillable = [
        'return_number',
        'sale_id',
        'shop_id',
        'reason',
        'customer_name',
        'customer_phone',
        'refund_amount',
        'is_exchange',
        'processed_by',
        'processed_at',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'reason' => ReturnReason::class,
        'refund_amount' => 'integer',
        'is_exchange' => 'boolean',
        'processed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    // Accessors for price formatting
    public function getRefundAmountInDollarsAttribute(): float
    {
        return $this->refund_amount / 100;
    }

    // Helper methods
    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function needsApproval(): bool
    {
        return $this->approved_at === null;
    }

    // Scopes
    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopeExchanges($query)
    {
        return $query->where('is_exchange', true);
    }

    public function scopeRefunds($query)
    {
        return $query->where('is_exchange', false);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('processed_at', '>=', now()->subDays($days));
    }
}
