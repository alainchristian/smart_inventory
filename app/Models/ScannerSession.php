<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ScannerSession extends Model
{
    protected $fillable = [
        'session_code',
        'user_id',
        'page_type',
        'transfer_id',
        'last_scanned_barcode',
        'last_scan_at',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'last_scan_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('session_code', $code)->exists());

        return $code;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function recordScan(string $barcode): void
    {
        $this->update([
            'last_scanned_barcode' => $barcode,
            'last_scan_at' => now(),
        ]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('expires_at', '>', now());
    }

    /**
     * Check if session is active and not expired
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->expires_at->isFuture();
    }

    /**
     * Check if phone has been active recently
     */
    public function hasRecentActivity(): bool
    {
        if (!$this->last_scan_at) {
            return false;
        }

        return $this->last_scan_at->isAfter(now()->subSeconds(30));
    }

    /**
     * Mark session as having recent activity
     */
    public function recordActivity(): void
    {
        $this->update([
            'last_scan_at' => now()
        ]);
    }
}
