<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OwnerWithdrawal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'daily_session_id',
        'shop_id',
        'amount',
        'reason',
        'method',
        'momo_reference',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'amount'      => 'integer',
    ];

    public function dailySession(): BelongsTo
    {
        return $this->belongsTo(DailySession::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isCash(): bool
    {
        return $this->method === 'cash';
    }

    public function isMobileMoney(): bool
    {
        return $this->method === 'mobile_money';
    }
}
