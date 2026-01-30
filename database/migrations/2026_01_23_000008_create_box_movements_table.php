// database/migrations/2026_01_23_000008_create_box_movements_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('box_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('box_id')->constrained()->restrictOnDelete();

            // From location
            $table->unsignedBigInteger('from_location_id')->nullable();

            // To location
            $table->unsignedBigInteger('to_location_id')->nullable();
            
            // Movement metadata
            $table->string('movement_type', 50); // 'transfer', 'sale', 'return', 'damage', 'adjustment'
            $table->foreignId('moved_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('moved_at');
            
            // Reference to source transaction
            $table->string('reference_type', 50)->nullable(); // 'transfer', 'sale', etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            
            // Quantity at time of movement (for partial box movements)
            $table->unsignedInteger('items_moved')->nullable();
            
            $table->timestamps();
            
            // No soft deletes - movements are immutable audit trail
            
            // Indexes
            $table->index(['box_id', 'moved_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('moved_at');
        });

        // Add enum columns
        DB::statement("ALTER TABLE box_movements ADD COLUMN from_location_type location_type");
        DB::statement("ALTER TABLE box_movements ADD COLUMN to_location_type location_type");
    }

    public function down(): void
    {
        Schema::dropIfExists('box_movements');
    }
};