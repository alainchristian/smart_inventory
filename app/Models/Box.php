<?php

namespace App\Models;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Box extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'box_code',
        'status',
        'items_total',
        'items_remaining',
        'location_type',
        'location_id',
        'received_by',
        'received_at',
        'batch_number',
        'expiry_date',
        'damage_notes',
    ];

    protected $casts = [
        'status' => BoxStatus::class,
        'location_type' => LocationType::class,
        'items_total' => 'integer',
        'items_remaining' => 'integer',
        'received_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): MorphTo
    {
        return $this->morphTo('location', 'location_type', 'location_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(BoxMovement::class)->orderBy('moved_at', 'desc');
    }

    public function transferBoxes(): HasMany
    {
        return $this->hasMany(TransferBox::class);
    }

    // Business logic
    public function isFull(): bool
    {
        return $this->items_remaining === $this->items_total;
    }

    public function isEmpty(): bool
    {
        return $this->items_remaining === 0;
    }

    public function getFilledPercentage(): float
    {
        if ($this->items_total === 0) {
            return 0;
        }

        return ($this->items_remaining / $this->items_total) * 100;
    }

    public function consumeItems(int $quantity, string $reason, ?int $referenceId = null, ?string $referenceType = null): void
    {
        if ($quantity > $this->items_remaining) {
            throw new \Exception('Cannot consume more items than remaining in box');
        }

        $oldRemaining = $this->items_remaining;
        $this->items_remaining -= $quantity;

        // Update status
        if ($this->items_remaining === 0) {
            $this->status = BoxStatus::EMPTY;
        } elseif ($this->items_remaining < $this->items_total) {
            $this->status = BoxStatus::PARTIAL;
        }

        $this->save();

        // Log movement
        BoxMovement::create([
            'box_id' => $this->id,
            'from_location_type' => $this->location_type,
            'from_location_id' => $this->location_id,
            'to_location_type' => null,
            'to_location_id' => null,
            'movement_type' => 'consumption',
            'moved_by' => auth()->id(),
            'moved_at' => now(),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reason' => $reason,
            'items_moved' => $quantity,
            'notes' => "Consumed {$quantity} items. Remaining: {$oldRemaining} → {$this->items_remaining}",
        ]);
    }

    public function moveTo(LocationType $locationType, int $locationId, string $reason, ?int $referenceId = null, ?string $referenceType = null): void
    {
        $oldLocationType = $this->location_type;
        $oldLocationId = $this->location_id;

        // Log movement before updating
        BoxMovement::create([
            'box_id' => $this->id,
            'from_location_type' => $oldLocationType,
            'from_location_id' => $oldLocationId,
            'to_location_type' => $locationType,
            'to_location_id' => $locationId,
            'movement_type' => 'transfer',
            'moved_by' => auth()->id(),
            'moved_at' => now(),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reason' => $reason,
            'items_moved' => $this->items_remaining,
        ]);

        // Update location
        $this->location_type = $locationType;
        $this->location_id = $locationId;
        $this->save();
    }

    // Scopes
    public function scopeAtLocation($query, LocationType $locationType, int $locationId)
    {
        return $query->where('location_type', $locationType)
                    ->where('location_id', $locationId);
    }

    public function scopeAvailable($query)
    {
        return $query->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
                    ->where('items_remaining', '>', 0);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>=', now());
    }
}
