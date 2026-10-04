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
    /**
     * Quick-add presets offered in the product form and the bulk "Apply packs"
     * tool: key => [name, size]. A null size depends on the box (Half box).
     */
    public const PRESETS = [
        'pair'       => ['Pair', 2],
        'half_dozen' => ['Half-dozen', 6],
        'dozen'      => ['Dozen', 12],
        'half_box'   => ['Half box', null],
        'gross'      => ['Gross', 144],
    ];

    /** A preset's size for a box of $itemsPerBox, or null when it doesn't fit (too big, or an odd box for Half box). */
    public static function presetSize(string $key, int $itemsPerBox): ?int
    {
        if (! isset(self::PRESETS[$key])) {
            return null;
        }
        $size = self::PRESETS[$key][1] ?? ($itemsPerBox % 2 === 0 ? intdiv($itemsPerBox, 2) : null);

        return $size !== null && $size >= 2 && $size < $itemsPerBox ? $size : null;
    }

    /**
     * Box price per piece, rounded down — the cheapest a piece may ever cost
     * in a pack. Rounded down so a pack priced at exactly size × box rate
     * isn't refused over a fraction of a franc.
     */
    public static function boxRate(int $boxPrice, int $itemsPerBox): float
    {
        return $itemsPerBox > 0 ? $boxPrice / $itemsPerBox : 0;
    }

    /** Lowest allowed price for a pack of $size: not cheaper per piece than buying the box. */
    public static function minPrice(int $size, int $boxPrice, int $itemsPerBox): int
    {
        return (int) floor(self::boxRate($boxPrice, $itemsPerBox) * $size);
    }

    /**
     * What's wrong with a pack, or null. One rule set for the product form
     * and the bulk tool: smaller than a box, and priced between the box rate
     * (cheapest) and single pieces (dearest) per piece.
     */
    public static function problemFor(string $name, int $size, int $price, int $piecePrice, int $boxPrice, int $itemsPerBox): ?string
    {
        $name = trim($name) !== '' ? trim($name) : 'pack';
        if ($size < 2) {
            return 'A pack holds at least 2 pieces.';
        }
        if ($size >= $itemsPerBox) {
            return "A pack must hold fewer pieces than a box ({$itemsPerBox}).";
        }
        if ($boxPrice > 0 && $price < self::minPrice($size, $boxPrice, $itemsPerBox)) {
            return "A {$name} can't cost less per piece than a full box ("
                . number_format(self::boxRate($boxPrice, $itemsPerBox)) . ' a piece, so at least '
                . number_format(self::minPrice($size, $boxPrice, $itemsPerBox)) . ' RWF).';
        }
        // Never below the exact box rate: a 458.33 box rate rounds the piece
        // price to 458, and 12 × 458 = 5,496 must not forbid a 5,500 dozen.
        $max = max($piecePrice * $size, (int) ceil(self::boxRate($boxPrice, $itemsPerBox) * $size));
        if ($piecePrice > 0 && $price > $max) {
            return "A {$name} can't cost more per piece than single pieces (at most "
                . number_format($max) . ' RWF).';
        }

        return null;
    }

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
        // "Pack of 3" can't take a plural "s" ("3 Pack of 3s") — use "3 × Pack of 3"
        if (preg_match('/\d|\bof\b/i', $unitName)) {
            return $qty . ' × ' . $unitName;
        }
        $invariant = in_array(strtolower($unitName), ['dozen', 'gross', 'half-dozen'], true) || str_ends_with(strtolower($unitName), 's');

        return $qty . ' ' . ($qty === 1 || $invariant ? $unitName : $unitName . 's');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
