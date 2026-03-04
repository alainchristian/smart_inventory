<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // IMPORTANT: This migration deletes ALL transactional and product data
        // Only users, warehouses, and shops will remain

        DB::statement('SET CONSTRAINTS ALL DEFERRED');

        // 1. Delete all transactional detail records (child tables first)
        DB::table('sale_items')->delete();
        DB::table('return_items')->delete();
        DB::table('transfer_items')->delete();
        DB::table('transfer_boxes')->delete();

        // 2. Delete all transactional header records (returns before sales!)
        DB::table('returns')->delete();
        DB::table('sales')->delete();
        DB::table('transfers')->delete();
        DB::table('damaged_goods')->delete();

        // 3. Delete all inventory records
        DB::table('box_movements')->delete();
        DB::table('boxes')->delete();
        DB::table('inventory_snapshots')->delete();

        // 4. Delete all product-related records
        DB::table('product_barcodes')->delete();
        DB::table('products')->delete();
        DB::table('categories')->delete();

        // 5. Delete transporters
        DB::table('transporters')->delete();

        // 6. Delete alerts and activity logs
        DB::table('alerts')->delete();
        DB::table('activity_logs')->delete();

        // Reset sequences to 1 for all tables
        $tables = [
            'sale_items', 'return_items', 'transfer_items', 'transfer_boxes',
            'sales', 'returns', 'transfers', 'damaged_goods',
            'box_movements', 'boxes', 'inventory_snapshots',
            'product_barcodes', 'products', 'categories',
            'transporters', 'alerts', 'activity_logs'
        ];

        foreach ($tables as $table) {
            DB::statement("ALTER SEQUENCE {$table}_id_seq RESTART WITH 1");
        }
    }

    public function down(): void
    {
        // Cannot reverse data deletion
        // This is a destructive operation with no rollback
    }
};
