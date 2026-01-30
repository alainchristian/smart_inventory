// database/migrations/2026_01_23_000014_create_sale_items_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            
            // Box tracking (recommended for traceability)
            $table->foreignId('box_id')->nullable()->constrained()->restrictOnDelete();
            
            // Quantity and pricing
            $table->unsignedInteger('quantity_sold');
            $table->boolean('is_full_box')->default(false);
            
            // Pricing (in cents)
            $table->unsignedBigInteger('original_unit_price');
            $table->unsignedBigInteger('actual_unit_price');
            $table->unsignedBigInteger('line_total');
            
            // Price modification tracking
            $table->boolean('price_was_modified')->default(false);
            $table->string('price_modification_reference')->nullable();
            $table->text('price_modification_reason')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};