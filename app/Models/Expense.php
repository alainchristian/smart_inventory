<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'daily_session_id',
        'expense_category_id',
        'amount',
        'description',
        'payment_method',
        'receipt_reference',
        'recorded_by',
        'recorded_at',
        'expense_request_id',
        'is_system_generated',
    ];

    protected $casts = [
        'recorded_at'         => 'datetime',
        'amount'              => 'integer',
        'is_system_generated' => 'boolean',
    ];

    // Relationships

    public function dailySession(): BelongsTo
    {
        return $this->belongsTo(DailySession::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function expenseRequest(): BelongsTo
    {
        return $this->belongsTo(ExpenseRequest::class);
    }

    public function canBeVoided(): bool
    {
        return ! $this->is_system_generated && $this->deleted_at === null;
    }
}
