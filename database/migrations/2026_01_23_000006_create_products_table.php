// database/migrations/2026_01_23_000006_create_products_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            
            $table->string('name');
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->unique()->nullable();
            $table->text('description')->nullable();
            
            // Packaging
            $table->unsignedInteger('items_per_box');
            
            // Pricing (in cents to avoid floating point issues)
            $table->unsignedBigInteger('purchase_price')->comment('Price in cents');
            $table->unsignedBigInteger('selling_price')->comment('Price per item in cents');
            $table->unsignedBigInteger('box_selling_price')->nullable()->comment('Full box price in cents');
            
            // Inventory management
            $table->unsignedInteger('low_stock_threshold')->default(10);
            $table->unsignedInteger('reorder_point')->default(20);
            
            // Metadata
            $table->string('unit_of_measure', 20)->default('piece');
            $table->decimal('weight_per_item', 10, 3)->nullable()->comment('Weight in kg');
            $table->string('supplier', 200)->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Search indexes
            $table->index(['name', 'sku']);
            $table->index('barcode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};