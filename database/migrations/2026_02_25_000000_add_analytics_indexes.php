<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sales table indexes for analytics
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['sale_date', 'shop_id', 'voided_at'], 'idx_sales_analytics');
            $table->index(['shop_id', 'sale_date'], 'idx_sales_shop_date');
        });

        // Sale items table indexes for product performance
        Schema::table('sale_items', function (Blueprint $table) {
            $table->index(['product_id', 'sale_id'], 'idx_sale_items_analytics');
        });

        // Boxes table indexes for inventory analytics
        Schema::table('boxes', function (Blueprint $table) {
            $table->index(['location_type', 'location_id', 'status'], 'idx_boxes_location_status');
            $table->index(['received_at'], 'idx_boxes_received_at');
            $table->index(['expiry_date'], 'idx_boxes_expiry_date');
            $table->index(['status', 'items_remaining'], 'idx_boxes_available');
        });

        // Returns table indexes for loss analytics
        Schema::table('returns', function (Blueprint $table) {
            $table->index(['processed_at', 'shop_id'], 'idx_returns_analytics');
            $table->index(['shop_id', 'processed_at'], 'idx_returns_shop_date');
            $table->index(['is_exchange', 'processed_at'], 'idx_returns_type_date');
        });

        // Damaged goods table indexes for loss analytics
        Schema::table('damaged_goods', function (Blueprint $table) {
            $table->index(['recorded_at', 'disposition'], 'idx_damaged_goods_analytics');
            $table->index(['location_type', 'location_id', 'recorded_at'], 'idx_damaged_goods_location');
        });

        // Transfers table indexes for transfer analytics
        Schema::table('transfers', function (Blueprint $table) {
            $table->index(['created_at', 'status'], 'idx_transfers_analytics');
            $table->index(['status'], 'idx_transfers_status');
            $table->index(['has_discrepancy', 'created_at'], 'idx_transfers_discrepancy');
            $table->index(['from_warehouse_id', 'to_shop_id', 'created_at'], 'idx_transfers_route');
        });

        // Transfer items table for product transfer analytics
        Schema::table('transfer_items', function (Blueprint $table) {
            $table->index(['product_id', 'transfer_id'], 'idx_transfer_items_analytics');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop sales indexes
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_analytics');
            $table->dropIndex('idx_sales_shop_date');
        });

        // Drop sale items indexes
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('idx_sale_items_analytics');
        });

        // Drop boxes indexes
        Schema::table('boxes', function (Blueprint $table) {
            $table->dropIndex('idx_boxes_location_status');
            $table->dropIndex('idx_boxes_received_at');
            $table->dropIndex('idx_boxes_expiry_date');
            $table->dropIndex('idx_boxes_available');
        });

        // Drop returns indexes
        Schema::table('returns', function (Blueprint $table) {
            $table->dropIndex('idx_returns_analytics');
            $table->dropIndex('idx_returns_shop_date');
            $table->dropIndex('idx_returns_type_date');
        });

        // Drop damaged goods indexes
        Schema::table('damaged_goods', function (Blueprint $table) {
            $table->dropIndex('idx_damaged_goods_analytics');
            $table->dropIndex('idx_damaged_goods_location');
        });

        // Drop transfers indexes
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropIndex('idx_transfers_analytics');
            $table->dropIndex('idx_transfers_status');
            $table->dropIndex('idx_transfers_discrepancy');
            $table->dropIndex('idx_transfers_route');
        });

        // Drop transfer items indexes
        Schema::table('transfer_items', function (Blueprint $table) {
            $table->dropIndex('idx_transfer_items_analytics');
        });
    }
};
