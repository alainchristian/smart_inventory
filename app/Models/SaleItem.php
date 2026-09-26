<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'box_id',
        'quantity_sold',
        'is_full_box',
        'sell_unit_name',
        'sell_unit_size',
        'original_unit_price',
        'actual_unit_price',
        'line_total',
        'price_was_modified',
        'price_modification_reference',
        'price_modification_reason',
    ];

    protected $casts = [
        'quantity_sold' => 'integer',
        'is_full_box' => 'boolean',
        'sell_unit_size' => 'integer',
        'original_unit_price' => 'integer',
        'actual_unit_price' => 'integer',
        'line_total' => 'integer',
        'price_was_modified' => 'boolean',
    ];

    // Relationships
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function box(): BelongsTo
    {
        return $this->belongsTo(Box::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'original_sale_item_id');
    }

    // Helper methods

    /** True for a line sold by the pack (dozen, pair…): prices are per pack. */
    public function isPackLine(): bool
    {
        return ! $this->is_full_box && (int) $this->sell_unit_size > 1;
    }

    /** Price of one piece on this line, whatever unit it was sold in. */
    public function pricePerPiece(): float
    {
        return $this->quantity_sold > 0 ? $this->line_total / $this->quantity_sold : 0.0;
    }

    public function getPriceDiscountAmount(): int
    {
        return $this->original_unit_price - $this->actual_unit_price;
    }

    public function getPriceDiscountPercentage(): float
    {
        if ($this->original_unit_price === 0) {
            return 0;
        }

        return (($this->original_unit_price - $this->actual_unit_price) / $this->original_unit_price) * 100;
    }
}
