<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerCreditAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_phone', 'customer_name', 'shop_id',
        'total_credit_given', 'total_repaid', 'outstanding_balance',
        'last_credit_at', 'last_repayment_at', 'notes',
    ];

    protected $casts = [
        'total_credit_given'  => 'integer',
        'total_repaid'        => 'integer',
        'outstanding_balance' => 'integer',
        'last_credit_at'      => 'datetime',
        'last_repayment_at'   => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'credit_account_id');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(CreditRepayment::class, 'credit_account_id');
    }

    public function hasOutstandingBalance(): bool
    {
        return $this->outstanding_balance > 0;
    }

    // Find or create by phone number
    public static function findOrCreateByPhone(string $phone, ?string $name = null, ?int $shopId = null): self
    {
        return self::firstOrCreate(
            ['customer_phone' => $phone],
            ['customer_name' => $name, 'shop_id' => $shopId]
        );
    }
}
