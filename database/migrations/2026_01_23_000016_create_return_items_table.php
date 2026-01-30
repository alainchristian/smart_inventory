// database/migrations/2026_01_23_000016_create_return_items_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            
            // Quantities
            $table->unsignedInteger('quantity_returned');
            $table->unsignedInteger('quantity_damaged')->default(0);
            $table->unsignedInteger('quantity_good')->default(0);
            
            // Original sale reference
            $table->foreignId('original_sale_item_id')->nullable()->constrained('sale_items')->nullOnDelete();
            
            // Exchange handling
            $table->boolean('is_replacement')->default(false);
            $table->foreignId('replacement_box_id')->nullable()->constrained('boxes')->nullOnDelete();
            
            $table->text('condition_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};