<?php

use App\Models\ProductSellUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Packs priced cheaper per piece than buying the box (e.g. Drinking Glass
 * Dozen 8,800 while the box rate is 750 a piece = 9,000) break the price
 * ladder added on 2026-10-04, so their product can't be saved. Raise each
 * such pack to the lowest allowed price: box rate × pack size
 * (ProductSellUnit::minPrice). Packs already at or above it are untouched,
 * so running it again changes nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('product_sell_units')
            ->join('products', 'products.id', '=', 'product_sell_units.product_id')
            ->select('product_sell_units.id', 'product_sell_units.size', 'product_sell_units.price',
                'products.items_per_box', 'products.selling_price', 'products.box_selling_price')
            ->get();

        foreach ($rows as $row) {
            $ipb = (int) $row->items_per_box;
            $box = (int) ($row->box_selling_price ?: $row->selling_price * $ipb);
            if ($ipb < 1 || $box < 1) {
                continue;
            }
            $min = ProductSellUnit::minPrice((int) $row->size, $box, $ipb);
            if ((int) $row->price < $min) {
                DB::table('product_sell_units')->where('id', $row->id)
                    ->update(['price' => $min, 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // Old prices aren't kept; they broke the ladder anyway.
    }
};
