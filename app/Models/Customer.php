<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'email', 'notes',
        'total_credit_given', 'total_repaid', 'outstanding_balance',
        'last_purchase_at', 'last_credit_at', 'last_repayment_at',
        'registered_by', 'shop_id',
    ];

    protected $casts = [
        'total_credit_given'  => 'integer',
        'total_repaid'        => 'integer',
        'outstanding_balance' => 'integer',
        'last_purchase_at'    => 'datetime',
        'last_credit_at'      => 'datetime',
        'last_repayment_at'   => 'datetime',
    ];

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function creditRepayments(): HasMany
    {
        return $this->hasMany(CreditRepayment::class);
    }

    public function writeoffs(): HasMany
    {
        return $this->hasMany(CreditWriteoff::class);
    }

    public function hasOutstandingBalance(): bool
    {
        return $this->outstanding_balance > 0;
    }

    /**
     * Search by phone (exact) or name (partial). Returns collection.
     */
    public static function search(string $query): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('phone', 'like', "%{$query}%")
            ->orWhere('name', 'ilike', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'outstanding_balance']);
    }
}
