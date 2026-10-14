<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Credit a customer owed a shop before the system was in use.
 * Written only by App\Services\Sales\OpeningCreditService.
 */
class OpeningCredit extends Model
{
    protected $fillable = [
        'customer_id', 'shop_id', 'amount', 'taken_on', 'reference', 'notes',
        'source', 'customer_created', 'recorded_by',
    ];

    protected $casts = [
        'amount'           => 'integer',
        'taken_on'         => 'date',
        'customer_created' => 'boolean',
        'removed_at'       => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function removedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNull('removed_at');
    }
}
