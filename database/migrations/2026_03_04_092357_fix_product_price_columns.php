<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Move box price into box_selling_price, convert both price columns to per-item
        DB::statement('
            UPDATE products SET
                box_selling_price = selling_price,
                selling_price     = ROUND(selling_price  / items_per_box),
                purchase_price    = ROUND(purchase_price / items_per_box)
            WHERE deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        // Reverse: convert item prices back to box prices
        DB::statement('
            UPDATE products SET
                selling_price     = box_selling_price,
                purchase_price    = ROUND(purchase_price * items_per_box),
                box_selling_price = NULL
            WHERE deleted_at IS NULL
        ');
    }
};
