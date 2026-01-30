<?php

namespace App\Models;

use App\Enums\DispositionType;
use App\Enums\LocationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DamagedGood extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'damage_reference',
        'source_type',
        'source_id',
        'product_id',
        'quantity_damaged',
        'box_id',
        'location_type',
        'location_id',
        'disposition',
        'disposition_decided_by',
        'disposition_decided_at',
        'disposition_notes',
        'damage_description',
        'photos',
        'estimated_loss',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'location_type' => LocationType::class,
        'disposition' => DispositionType::class,
        'quantity_damaged' => 'integer',
        'estimated_loss' => 'integer',
        'photos' => 'array',
        'disposition_decided_at' => 'datetime',
        'recorded_at' => 'datetime',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function box(): BelongsTo
    {
        return $this->belongsTo(Box::class);
    }

    public function location(): MorphTo
    {
        return $this->morphTo('location', 'location_type', 'location_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function dispositionDecidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disposition_decided_by');
    }

    // Accessors for price formatting
    public function getEstimatedLossInDollarsAttribute(): float
    {
        return $this->estimated_loss / 100;
    }

    // Helper methods
    public function hasDisposition(): bool
    {
        return $this->disposition !== DispositionType::PENDING;
    }

    public function isPending(): bool
    {
        return $this->disposition === DispositionType::PENDING;
    }

    // Scopes
    public function scopeAtLocation($query, LocationType $locationType, int $locationId)
    {
        return $query->where('location_type', $locationType)
                    ->where('location_id', $locationId);
    }

    public function scopePendingDisposition($query)
    {
        return $query->where('disposition', DispositionType::PENDING);
    }

    public function scopeByDisposition($query, DispositionType $disposition)
    {
        return $query->where('disposition', $disposition);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }
}
