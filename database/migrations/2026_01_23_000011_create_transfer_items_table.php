// database/migrations/2026_01_23_000011_create_transfer_items_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            
            // Requested vs actual quantities
            $table->unsignedInteger('quantity_requested');
            $table->unsignedInteger('quantity_shipped')->default(0);
            $table->unsignedInteger('quantity_received')->default(0);
            
            // Discrepancy for this line item
            $table->integer('discrepancy_quantity')->default(0);
            $table->text('discrepancy_reason')->nullable();
            
            $table->timestamps();
            
            // Unique constraint: one product per transfer
            $table->unique(['transfer_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_items');
    }
};