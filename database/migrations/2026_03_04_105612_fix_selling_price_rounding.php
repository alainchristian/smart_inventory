<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Recompute selling_price using ROUND instead of FLOOR
        // Only for products that have a box_selling_price reference
        DB::statement('
            UPDATE products
            SET selling_price = ROUND(box_selling_price::numeric / items_per_box)
            WHERE box_selling_price IS NOT NULL
              AND deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        // Revert to FLOOR (previous state)
        DB::statement('
            UPDATE products
            SET selling_price = FLOOR(box_selling_price::numeric / items_per_box)
            WHERE box_selling_price IS NOT NULL
              AND deleted_at IS NULL
        ');
    }
};
