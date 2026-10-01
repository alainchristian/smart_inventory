<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One step in a transfer's history (requested, approved, packing_done,
 * dispatched, arrived, received, issue_resolved, closed, cancelled, …).
 * Written only by TransferService::record().
 */
class TransferEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['transfer_id', 'action', 'from_status', 'to_status', 'user_id', 'user_name', 'note', 'meta', 'created_at'];

    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
