<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_date',
        'shop_id',
        'opening_balance',
        'opened_by',
        'opened_at',
        'total_sales_cash',
        'total_sales_momo',
        'total_sales_card',
        'total_sales_other',
        'total_sales',
        'total_refunds_cash',
        'transaction_count',
        'total_expenses',
        'total_expenses_cash',
        'total_expenses_momo',
        'total_withdrawals',
        'total_withdrawals_cash',
        'total_withdrawals_momo',
        'total_bank_deposits',
        'bank_deposit_count',
        'expected_cash',
        'actual_cash_counted',
        'cash_variance',
        'cash_to_owner_momo',
        'owner_momo_reference',
        'cash_to_bank',
        'bank_reference',
        'cash_retained',
        'notes',
        'closed_by',
        'closed_at',
        'locked_by',
        'locked_at',
        'status',
    ];

    protected $casts = [
        'session_date' => 'date',
        'opened_at'    => 'datetime',
        'closed_at'    => 'datetime',
        'locked_at'    => 'datetime',
        'opening_balance'      => 'integer',
        'total_sales_cash'     => 'integer',
        'total_sales_momo'     => 'integer',
        'total_sales_card'     => 'integer',
        'total_sales_other'    => 'integer',
        'total_sales'          => 'integer',
        'total_refunds_cash'   => 'integer',
        'transaction_count'        => 'integer',
        'total_expenses'           => 'integer',
        'total_expenses_cash'      => 'integer',
        'total_expenses_momo'      => 'integer',
        'total_withdrawals'        => 'integer',
        'total_withdrawals_cash'   => 'integer',
        'total_withdrawals_momo'   => 'integer',
        'total_bank_deposits'      => 'integer',
        'bank_deposit_count'       => 'integer',
        'expected_cash'            => 'integer',
        'actual_cash_counted'      => 'integer',
        'cash_variance'            => 'integer',
        'cash_to_owner_momo'       => 'integer',
        'cash_to_bank'             => 'integer',
        'cash_retained'            => 'integer',
    ];

    // Relationships

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function ownerWithdrawals(): HasMany
    {
        return $this->hasMany(OwnerWithdrawal::class);
    }

    public function bankDeposits(): HasMany
    {
        return $this->hasMany(BankDeposit::class);
    }

    // Scopes

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('session_date', $date);
    }

    // Helper methods

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function isEditable(): bool
    {
        return $this->status === 'open';
    }
}
