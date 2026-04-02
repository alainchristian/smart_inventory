<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('held_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users');
            $table->foreignId('shop_id')->constrained('shops');
            $table->string('hold_reference')->unique();
            $table->jsonb('cart_data');
            $table->unsignedBigInteger('cart_total')->default(0);
            $table->unsignedSmallInteger('item_count')->default(0);
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->jsonb('payment_data')->nullable();
            $table->boolean('needs_price_approval')->default(true);
            $table->text('notes')->nullable();
            // Approval
            $table->timestamp('override_approved_at')->nullable();
            $table->foreignId('override_approved_by')->nullable()->constrained('users');
            $table->string('approval_note')->nullable();
            // Rejection
            $table->timestamp('override_rejected_at')->nullable();
            $table->foreignId('override_rejected_by')->nullable()->constrained('users');
            $table->string('rejected_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('held_sales');
    }
};
