// database/migrations/2026_01_23_000015_create_returns_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 30)->unique();
            $table->foreignId('sale_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();

            // Return metadata
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 20)->nullable();
            
            // Financial
            $table->unsignedBigInteger('refund_amount')->default(0)->comment('Amount in cents');
            $table->boolean('is_exchange')->default(false);
            
            // Approval workflow
            $table->foreignId('processed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('processed_at');
            
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['shop_id', 'processed_at']);
        });

        // Add enum column
        DB::statement("ALTER TABLE returns ADD COLUMN reason return_reason NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};