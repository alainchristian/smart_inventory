<?php

namespace App\Models;

use App\Enums\LocationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoxMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'box_id',
        'from_location_type',
        'from_location_id',
        'to_location_type',
        'to_location_id',
        'movement_type',
        'moved_by',
        'moved_at',
        'reference_type',
        'reference_id',
        'reason',
        'notes',
        'items_moved',
    ];

    protected $casts = [
        'from_location_type' => LocationType::class,
        'to_location_type' => LocationType::class,
        'moved_at' => 'datetime',
        'items_moved' => 'integer',
    ];

    // No soft deletes - movements are immutable audit trail

    // Relationships
    public function box(): BelongsTo
    {
        return $this->belongsTo(Box::class);
    }

    public function movedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }

    // Scopes
    public function scopeForBox($query, int $boxId)
    {
        return $query->where('box_id', $boxId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('moved_at', '>=', now()->subDays($days));
    }
}
