<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shops can be specialised: a shoe shop only requests / sells shoes, a
 * commodities shop only its commodities. `sells_all_categories` = general
 * store (default, so every existing shop keeps selling everything);
 * otherwise `shop_categories` lists what it sells (a parent covers its
 * subcategories). See Shop::sellableCategoryIds().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('sells_all_categories')->default(true)->after('default_warehouse_id');
        });

        Schema::create('shop_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shop_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_categories');
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('sells_all_categories');
        });
    }
};
