<?php

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankDeposit extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'daily_session_id',
        'shop_id',
        'amount',
        'source',
        'bank_reference',
        'notes',
        'deposited_by',
        'deposited_at',
    ];

    protected $casts = [
        'deposited_at' => 'datetime',
        'amount'       => 'integer',
    ];

    public function dailySession(): BelongsTo
    {
        return $this->belongsTo(DailySession::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function depositedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deposited_by');
    }

    public function activityLogIdentifier(): ?string
    {
        return number_format($this->amount) . ' RWF' . ($this->bank_reference ? ' – ' . $this->bank_reference : '');
    }
}
