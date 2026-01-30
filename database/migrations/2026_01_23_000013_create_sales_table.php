// database/migrations/2026_01_23_000013_create_sales_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number', 30)->unique();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();

            // Financial totals (in cents)
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            
            // Customer information (optional)
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 20)->nullable();
            
            // Tracking
            $table->foreignId('sold_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('sale_date');
            
            // Void handling
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('void_reason')->nullable();
            
            // Price override approval
            $table->boolean('has_price_override')->default(false);
            $table->foreignId('price_override_approved_by')->nullable()->constrained('users');
            $table->timestamp('price_override_approved_at')->nullable();
            $table->text('price_override_reason')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['shop_id', 'sale_date']);
            $table->index('sale_date');
        });

        // Add enum columns
        DB::statement("ALTER TABLE sales ADD COLUMN type sale_type NOT NULL");
        DB::statement("ALTER TABLE sales ADD COLUMN payment_method payment_method NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};