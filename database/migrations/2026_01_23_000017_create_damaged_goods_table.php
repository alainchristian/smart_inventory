// database/migrations/2026_01_23_000017_create_damaged_goods_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damaged_goods', function (Blueprint $table) {
            $table->id();
            $table->string('damage_reference', 30)->unique();
            
            // Source tracking
            $table->string('source_type', 50); // 'return', 'transfer', 'audit', 'warehouse'
            $table->unsignedBigInteger('source_id')->nullable();
            
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity_damaged');
            
            // Original box reference
            $table->foreignId('box_id')->nullable()->constrained()->restrictOnDelete();

            // Storage location
            $table->unsignedBigInteger('location_id');

            // Disposition decision
            $table->foreignId('disposition_decided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('disposition_decided_at')->nullable();
            $table->text('disposition_notes')->nullable();
            
            // Documentation
            $table->text('damage_description');
            $table->json('photos')->nullable();
            $table->unsignedBigInteger('estimated_loss')->default(0)->comment('Loss value in cents');
            
            // Tracking
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('recorded_at');

            $table->timestamps();
            $table->softDeletes();
        });

        // Add enum columns
        DB::statement("ALTER TABLE damaged_goods ADD COLUMN location_type location_type NOT NULL");
        DB::statement("ALTER TABLE damaged_goods ADD COLUMN disposition disposition_type DEFAULT 'pending'");
        DB::statement('CREATE INDEX damaged_goods_location_type_location_id_idx ON damaged_goods(location_type, location_id)');
        DB::statement('CREATE INDEX damaged_goods_disposition_idx ON damaged_goods(disposition)');
    }

    public function down(): void
    {
        Schema::dropIfExists('damaged_goods');
    }
};