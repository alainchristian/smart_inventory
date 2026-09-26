<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A pack a product can be sold in (Pair = 2, Dozen = 12, Pack of 10…).
 * Stock is always counted in single pieces; selling one unit takes `size`
 * pieces at `price`.
 */
class ProductSellUnit extends Model
{
    /** Quick-add presets offered in the product form: key => [name, size]. */
    public const PRESETS = [
        'pair'       => ['Pair', 2],
        'half_dozen' => ['Half-dozen', 6],
        'dozen'      => ['Dozen', 12],
        'gross'      => ['Gross', 144],
    ];

    protected $fillable = ['product_id', 'name', 'size', 'price'];

    protected $casts = [
        'size'  => 'integer',
        'price' => 'integer',
    ];

    /**
     * "2 boxes", "1 item", "3 Dozen", "2 Pairs" — how a quantity reads on
     * receipts and sale screens. Dozen/Gross don't take a plural "s".
     */
    public static function quantityLabel(int $qty, bool $isBox, ?string $unitName): string
    {
        if ($isBox) {
            return $qty . ' ' . ($qty === 1 ? 'box' : 'boxes');
        }
        if (! $unitName) {
            return $qty . ' ' . ($qty === 1 ? 'item' : 'items');
        }
        $invariant = in_array(strtolower($unitName), ['dozen', 'gross', 'half-dozen'], true) || str_ends_with(strtolower($unitName), 's');

        return $qty . ' ' . ($qty === 1 || $invariant ? $unitName : $unitName . 's');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
