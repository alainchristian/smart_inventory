<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditRepayment extends Model
{
    protected $fillable = [
        'credit_account_id', 'payment_method', 'amount',
        'sale_id', 'recorded_by', 'reference', 'notes', 'repaid_at',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'amount'         => 'integer',
        'repaid_at'      => 'datetime',
    ];

    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerCreditAccount::class, 'credit_account_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
