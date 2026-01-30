<?php

namespace App\Models;

use App\Enums\LocationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventorySnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'snapshot_date',
        'location_type',
        'location_id',
        'product_id',
        'full_boxes_count',
        'partial_boxes_count',
        'total_items',
        'total_cost_value',
        'total_retail_value',
        'items_variance',
        'variance_notes',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'location_type' => LocationType::class,
        'full_boxes_count' => 'integer',
        'partial_boxes_count' => 'integer',
        'total_items' => 'integer',
        'total_cost_value' => 'integer',
        'total_retail_value' => 'integer',
        'items_variance' => 'integer',
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

    // Accessors for price formatting
    public function getTotalCostValueInDollarsAttribute(): float
    {
        return $this->total_cost_value / 100;
    }

    public function getTotalRetailValueInDollarsAttribute(): float
    {
        return $this->total_retail_value / 100;
    }

    // Helper methods
    public function getTotalBoxesCount(): int
    {
        return $this->full_boxes_count + $this->partial_boxes_count;
    }

    public function hasVariance(): bool
    {
        return $this->items_variance != 0;
    }

    public function getPotentialProfit(): int
    {
        return $this->total_retail_value - $this->total_cost_value;
    }

    // Scopes
    public function scopeForDate($query, $date)
    {
        return $query->where('snapshot_date', $date);
    }

    public function scopeAtLocation($query, LocationType $locationType, int $locationId)
    {
        return $query->where('location_type', $locationType)
                    ->where('location_id', $locationId);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('snapshot_date', [$startDate, $endDate]);
    }

    public function scopeWithVariance($query)
    {
        return $query->where('items_variance', '!=', 0);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('snapshot_date', '>=', now()->subDays($days));
    }
}
