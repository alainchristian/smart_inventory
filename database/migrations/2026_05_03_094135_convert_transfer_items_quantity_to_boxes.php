<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE transfer_items
            SET quantity_requested = ROUND(transfer_items.quantity_requested::numeric / NULLIF(products.items_per_box::numeric, 1))
            FROM products
            WHERE transfer_items.product_id = products.id
        ');
    }

    public function down(): void
    {
        DB::statement('
            UPDATE transfer_items
            SET quantity_requested = transfer_items.quantity_requested * products.items_per_box
            FROM products
            WHERE transfer_items.product_id = products.id
        ');
    }
};
