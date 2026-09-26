<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sell units: a product can be sold loose by the pack (pair, dozen, pack of
 * 10…) as well as by the box. Stock stays counted in single pieces — a pack
 * is just "N pieces at the pack's own price".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_sell_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 40);
            $table->unsignedInteger('size');          // pieces per unit, >= 2
            $table->unsignedInteger('price');         // RWF per unit
            $table->timestamps();

            $table->unique(['product_id', 'size']);
            $table->unique(['product_id', 'name']);
        });

        Schema::table('products', function (Blueprint $table) {
            // Owner's per-product switch: may this product also be sold one piece at a time?
            $table->boolean('sell_single_pieces')->default(true)->after('items_per_box');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            // Set on pack lines only: quantity_sold is still pieces,
            // actual/original_unit_price are per pack (like full-box lines are per box).
            $table->string('sell_unit_name', 40)->nullable()->after('is_full_box');
            $table->unsignedInteger('sell_unit_size')->nullable()->after('sell_unit_name');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['sell_unit_name', 'sell_unit_size']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sell_single_pieces');
        });
        Schema::dropIfExists('product_sell_units');
    }
};
