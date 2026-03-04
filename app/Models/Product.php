<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'items_per_box',
        'purchase_price',
        'selling_price',
        'box_selling_price',
        'low_stock_threshold',
        'reorder_point',
        'unit_of_measure',
        'weight_per_item',
        'supplier',
        'is_active',
    ];

    protected $casts = [
        'items_per_box' => 'integer',
        'purchase_price' => 'integer',
        'selling_price' => 'integer',
        'box_selling_price' => 'integer',
        'low_stock_threshold' => 'integer',
        'reorder_point' => 'integer',
        'weight_per_item' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'box_purchase_price',
        'effective_box_selling_price',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function boxes(): HasMany
    {
        return $this->hasMany(Box::class);
    }

    public function transferItems(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    // Pricing (in RWF — whole number, no cents)
    // Business logic
    public function calculateBoxPrice(): int
    {
        return $this->box_selling_price ?? ($this->selling_price * $this->items_per_box);
    }

    /**
     * Box purchase price — always computed from item price.
     * Never stored: purchase_price × items_per_box.
     */
    public function getBoxPurchasePriceAttribute(): int
    {
        return $this->purchase_price * $this->items_per_box;
    }

    /**
     * Effective box selling price.
     * Uses the stored override if set, otherwise computes from item price.
     */
    public function getEffectiveBoxSellingPriceAttribute(): int
    {
        return $this->box_selling_price ?? ($this->selling_price * $this->items_per_box);
    }

    public function isLowStock(string $locationType, int $locationId): bool
    {
        $totalItems = $this->boxes()
            ->where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->where('status', '!=', 'empty')
            ->sum('items_remaining');

        return $totalItems <= $this->low_stock_threshold;
    }

    public function getCurrentStock(string $locationType, int $locationId): array
    {
        return [
            'full_boxes' => $this->boxes()
                ->where('location_type', $locationType)
                ->where('location_id', $locationId)
                ->where('status', 'full')
                ->count(),
            'partial_boxes' => $this->boxes()
                ->where('location_type', $locationType)
                ->where('location_id', $locationId)
                ->where('status', 'partial')
                ->count(),
            'total_items' => $this->boxes()
                ->where('location_type', $locationType)
                ->where('location_id', $locationId)
                ->sum('items_remaining'),
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query, string $locationType, int $locationId)
    {
        return $query->whereHas('boxes', function ($q) use ($locationType, $locationId) {
            $q->where('location_type', $locationType)
              ->where('location_id', $locationId);
        })->get()->filter(function ($product) use ($locationType, $locationId) {
            return $product->isLowStock($locationType, $locationId);
        });
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
              ->orWhere('sku', 'ILIKE', "%{$search}%")
              ->orWhere('barcode', 'ILIKE', "%{$search}%");
        });
    }
}
