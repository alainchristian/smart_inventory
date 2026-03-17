<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_credit_accounts')) {
            return;
        }

        Schema::create('customer_credit_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('customer_phone', 20)->unique();
            $table->string('customer_name')->nullable();
            $table->foreignId('shop_id')->nullable()->nullOnDelete()->constrained('shops');
            $table->unsignedBigInteger('total_credit_given')->default(0);
            $table->unsignedBigInteger('total_repaid')->default(0);
            $table->unsignedBigInteger('outstanding_balance')->default(0);
            $table->timestamp('last_credit_at')->nullable();
            $table->timestamp('last_repayment_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_phone');
            $table->index('outstanding_balance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_credit_accounts');
    }
};
