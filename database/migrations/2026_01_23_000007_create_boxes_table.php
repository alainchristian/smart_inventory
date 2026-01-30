// database/migrations/2026_01_23_000007_create_boxes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            
            $table->string('box_code', 50)->unique();

            // Quantities
            $table->unsignedInteger('items_total');
            $table->unsignedInteger('items_remaining');

            // Location (polymorphic-style but with enum)
            $table->unsignedBigInteger('location_id');
            
            // Receipt tracking
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('received_at');
            
            // Optional batch/lot tracking
            $table->string('batch_number', 50)->nullable();
            $table->date('expiry_date')->nullable();
            
            // Damage notes
            $table->text('damage_notes')->nullable();
            
            $table->timestamps();

            // No soft deletes - boxes are permanent records

            // Indexes for common queries
            $table->index('box_code');
        });

        // Add enum columns
        DB::statement("ALTER TABLE boxes ADD COLUMN status box_status DEFAULT 'full'");
        DB::statement("ALTER TABLE boxes ADD COLUMN location_type location_type NOT NULL");
        DB::statement('CREATE INDEX boxes_product_id_status_idx ON boxes(product_id, status)');
        DB::statement('CREATE INDEX boxes_location_type_location_id_idx ON boxes(location_type, location_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('boxes');
    }
};