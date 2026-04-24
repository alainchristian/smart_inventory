<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditRepayment extends Model
{
    protected $fillable = [
        'customer_id',
        'shop_id',
        'daily_session_id',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'recorded_by',
        'repayment_date',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'amount' => 'integer',
        'repayment_date' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function dailySession(): BelongsTo
    {
        return $this->belongsTo(DailySession::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
