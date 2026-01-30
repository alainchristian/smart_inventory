<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'product_id',
        'quantity_returned',
        'quantity_damaged',
        'quantity_good',
        'original_sale_item_id',
        'is_replacement',
        'replacement_box_id',
        'condition_notes',
    ];

    protected $casts = [
        'quantity_returned' => 'integer',
        'quantity_damaged' => 'integer',
        'quantity_good' => 'integer',
        'is_replacement' => 'boolean',
    ];

    // Relationships
    public function return(): BelongsTo
    {
        return $this->belongsTo(ReturnModel::class, 'return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function originalSaleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class, 'original_sale_item_id');
    }

    public function replacementBox(): BelongsTo
    {
        return $this->belongsTo(Box::class, 'replacement_box_id');
    }

    // Helper methods
    public function getDamagePercentage(): float
    {
        if ($this->quantity_returned === 0) {
            return 0;
        }

        return ($this->quantity_damaged / $this->quantity_returned) * 100;
    }
}
