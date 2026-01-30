<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'box_id',
        'scanned_out_by',
        'scanned_out_at',
        'scanned_in_by',
        'scanned_in_at',
        'is_received',
        'is_damaged',
        'damage_notes',
    ];

    protected $casts = [
        'scanned_out_at' => 'datetime',
        'scanned_in_at' => 'datetime',
        'is_received' => 'boolean',
        'is_damaged' => 'boolean',
    ];

    // Relationships
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function box(): BelongsTo
    {
        return $this->belongsTo(Box::class);
    }

    public function scannedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_out_by');
    }

    public function scannedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_in_by');
    }

    // Helper methods
    public function isScannedOut(): bool
    {
        return $this->scanned_out_at !== null;
    }

    public function isScannedIn(): bool
    {
        return $this->scanned_in_at !== null;
    }
}
