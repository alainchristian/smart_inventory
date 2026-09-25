<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A customer's credit position at one shop. Credit belongs to the shop that
 * gave it and can only be repaid there. Never write to this table directly —
 * go through App\Services\Sales\CustomerCreditLedger so customers.* totals
 * stay in sync.
 */
class CustomerShopBalance extends Model
{
    protected $fillable = [
        'customer_id', 'shop_id',
        'total_credit_given', 'total_repaid', 'total_written_off', 'outstanding_balance',
        'last_credit_at', 'last_repayment_at',
    ];

    protected $casts = [
        'total_credit_given'  => 'integer',
        'total_repaid'        => 'integer',
        'total_written_off'   => 'integer',
        'outstanding_balance' => 'integer',
        'last_credit_at'      => 'datetime',
        'last_repayment_at'   => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
