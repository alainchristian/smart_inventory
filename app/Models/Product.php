<?php

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'items_per_box',
        'sell_single_pieces',
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
        'sell_single_pieces' => 'boolean',
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

    /** Packs this product can be sold in loose (Dozen, Pair…), smallest first. */
    public function sellUnits(): HasMany
    {
        return $this->hasMany(ProductSellUnit::class)->orderBy('size');
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

    /**
     * sale_items.quantity_sold is always stored in item units, even for
     * full-box sales — convert to a box count for display when the line
     * was actually sold by the box.
     */
    public function itemsToDisplayQty(int $totalItems, bool $isFullBox): int
    {
        return $isFullBox ? (int) round($totalItems / max(1, $this->items_per_box)) : $totalItems;
    }

    /**
     * Companion to itemsToDisplayQty() — converts a per-item price into a
     * per-box price when the line was sold by the box, so qty × unit price
     * displayed together stay consistent.
     */
    public function displayUnitPrice(int $perItemPrice, bool $isFullBox): int
    {
        return $isFullBox ? $perItemPrice * max(1, $this->items_per_box) : $perItemPrice;
    }

    public function isLowStock(string $locationType, int $locationId, int $boxThreshold = 2): bool
    {
        $boxCount = $this->boxes()
            ->where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->whereRaw("status::text != 'empty'")
            ->where('items_remaining', '>', 0)
            ->count();

        return $boxCount <= $boxThreshold;
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
            // Pieces that can actually be sold (full + opened boxes; not damaged / in transit)
            'sellable_items' => (int) $this->boxes()
                ->where('location_type', $locationType)
                ->where('location_id', $locationId)
                ->whereIn('status', ['full', 'partial'])
                ->sum('items_remaining'),
        ];
    }

    /**
     * getCurrentStock() + the isLowStock() box count for many products in ONE
     * query, keyed by product id (products with no boxes get zeros).
     * Same rules as the per-product methods:
     *   full_boxes / partial_boxes: status full / partial
     *   total_items:  items_remaining over ALL statuses at the location
     *   sellable_items: items_remaining in full + partial boxes (what can be sold)
     *   stocked_boxes: status != empty AND items_remaining > 0 (isLowStock)
     */
    public static function stockSummaryFor(string $locationType, int $locationId, iterable $productIds): array
    {
        $ids = collect($productIds)->map(fn ($id) => (int) $id)->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        $rows = \Illuminate\Support\Facades\DB::table('boxes')
            ->where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->whereIn('product_id', $ids)
            ->groupBy('product_id')
            ->selectRaw("
                product_id,
                COUNT(*) FILTER (WHERE status = 'full')    AS full_boxes,
                COUNT(*) FILTER (WHERE status = 'partial') AS partial_boxes,
                COALESCE(SUM(items_remaining), 0)          AS total_items,
                COALESCE(SUM(items_remaining) FILTER (WHERE status IN ('full', 'partial')), 0) AS sellable_items,
                COUNT(*) FILTER (WHERE status::text != 'empty' AND items_remaining > 0) AS stocked_boxes
            ")
            ->get()
            ->keyBy('product_id');

        return $ids->mapWithKeys(fn ($id) => [$id => [
            'full_boxes'    => (int) ($rows[$id]->full_boxes ?? 0),
            'partial_boxes' => (int) ($rows[$id]->partial_boxes ?? 0),
            'total_items'   => (int) ($rows[$id]->total_items ?? 0),
            'sellable_items' => (int) ($rows[$id]->sellable_items ?? 0),
            'stocked_boxes' => (int) ($rows[$id]->stocked_boxes ?? 0),
        ]])->all();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query, string $locationType, int $locationId, int $boxThreshold = 2)
    {
        return $query->whereHas('boxes', function ($q) use ($locationType, $locationId) {
            $q->where('location_type', $locationType)
              ->where('location_id', $locationId);
        })->get()->filter(function ($product) use ($locationType, $locationId, $boxThreshold) {
            return $product->isLowStock($locationType, $locationId, $boxThreshold);
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
