// database/migrations/2026_01_23_000010_create_transfers_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 30)->unique();
            
            // Locations
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('to_shop_id')->constrained('shops')->restrictOnDelete();

            // Workflow timestamps and actors
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('requested_at');
            
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            
            $table->foreignId('packed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('packed_at')->nullable();
            
            $table->foreignId('transporter_id')->nullable()->constrained('transporters')->nullOnDelete();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->foreignId('received_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('received_at')->nullable();
            
            // Discrepancy tracking
            $table->boolean('has_discrepancy')->default(false);
            $table->text('discrepancy_notes')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('from_warehouse_id');
            $table->index('to_shop_id');
        });

        // Add enum column
        DB::statement("ALTER TABLE transfers ADD COLUMN status transfer_status DEFAULT 'pending'");
        DB::statement('CREATE INDEX transfers_status_created_at_idx ON transfers(status, created_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};