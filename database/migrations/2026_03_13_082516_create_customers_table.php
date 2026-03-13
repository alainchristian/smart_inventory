<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->unique();       // primary business identifier
            $table->string('email', 100)->nullable();
            $table->text('notes')->nullable();

            // Credit tracking — kept denormalized for fast access
            $table->unsignedBigInteger('total_credit_given')->default(0);   // lifetime credit extended
            $table->unsignedBigInteger('total_repaid')->default(0);         // lifetime repayments
            $table->unsignedBigInteger('outstanding_balance')->default(0);  // credit_given - repaid

            $table->timestamp('last_purchase_at')->nullable();
            $table->timestamp('last_credit_at')->nullable();

            $table->foreignId('registered_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete(); // shop where first registered

            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
            $table->index(['outstanding_balance']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
