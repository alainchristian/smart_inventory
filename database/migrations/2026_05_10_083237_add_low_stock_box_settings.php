<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'low_stock_boxes_shop'],
            [
                'value'       => '2',
                'type'        => 'integer',
                'group'       => 'inventory',
                'label'       => 'Low Stock Threshold (Shops)',
                'description' => 'Number of boxes at or below which a product is considered low stock at a shop.',
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'low_stock_boxes_warehouse'],
            [
                'value'       => '5',
                'type'        => 'integer',
                'group'       => 'inventory',
                'label'       => 'Low Stock Threshold (Warehouses)',
                'description' => 'Number of boxes at or below which a product is considered low stock at a warehouse.',
            ]
        );
    }

    public function down(): void
    {
        // Safe to leave — settings rows are additive
    }
};
