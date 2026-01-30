// database/migrations/2026_01_23_000020_create_inventory_snapshots_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_snapshots', function (Blueprint $table) {
            $table->id();
            
            // Snapshot metadata
            $table->date('snapshot_date');
            $table->unsignedBigInteger('location_id');
            
            // Product
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            
            // Quantities
            $table->unsignedInteger('full_boxes_count')->default(0);
            $table->unsignedInteger('partial_boxes_count')->default(0);
            $table->unsignedInteger('total_items')->default(0);
            
            // Financial values (in cents)
            $table->unsignedBigInteger('total_cost_value')->default(0);
            $table->unsignedBigInteger('total_retail_value')->default(0);
            
            // Variance from previous snapshot
            $table->integer('items_variance')->default(0);
            $table->text('variance_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('snapshot_date');
        });

        // Add enum column
        DB::statement("ALTER TABLE inventory_snapshots ADD COLUMN location_type location_type NOT NULL");
        DB::statement('CREATE UNIQUE INDEX snapshot_unique ON inventory_snapshots(snapshot_date, product_id, location_type, location_id)');
        DB::statement('CREATE INDEX inventory_snapshots_location_type_location_id_snapshot_date_idx ON inventory_snapshots(location_type, location_id, snapshot_date)');
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_snapshots');
    }
};