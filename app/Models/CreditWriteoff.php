<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditWriteoff extends Model
{
    protected $fillable = [
        'customer_id', 'shop_id', 'amount', 'balance_before',
        'balance_after', 'reason', 'written_off_by', 'written_off_at',
    ];

    protected $casts = [
        'written_off_at' => 'datetime',
        'amount'         => 'integer',
        'balance_before' => 'integer',
        'balance_after'  => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function writtenOffBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'written_off_by');
    }
}
