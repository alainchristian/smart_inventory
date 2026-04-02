<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeldSale extends Model
{
    protected $fillable = [
        'seller_id',
        'shop_id',
        'hold_reference',
        'cart_data',
        'cart_total',
        'item_count',
        'customer_id',
        'customer_name',
        'customer_phone',
        'payment_data',
        'needs_price_approval',
        'notes',
        'override_approved_at',
        'override_approved_by',
        'approval_note',
        'override_rejected_at',
        'override_rejected_by',
        'rejected_reason',
    ];

    protected $casts = [
        'cart_data'            => 'array',
        'payment_data'         => 'array',
        'needs_price_approval' => 'boolean',
        'override_approved_at' => 'datetime',
        'override_rejected_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_rejected_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePendingApproval($query)
    {
        return $query->where('needs_price_approval', true)
                     ->whereNull('override_approved_at')
                     ->whereNull('override_rejected_at');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isApproved(): bool
    {
        return $this->override_approved_at !== null;
    }

    public function isRejected(): bool
    {
        return $this->override_rejected_at !== null;
    }

    public function isPending(): bool
    {
        return ! $this->isApproved() && ! $this->isRejected();
    }
}
