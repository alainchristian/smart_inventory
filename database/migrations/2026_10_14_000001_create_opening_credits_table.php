<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Credit customers owed BEFORE the system was in use ("old credit").
 * Each row adds to the lending shop's balance (customer_shop_balances) through
 * CustomerCreditLedger; there is no sale behind it, so it never counts as
 * sales or as credit given on a day.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opening_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('taken_on');
            $table->string('reference', 100)->nullable();
            $table->string('notes', 500)->nullable();
            $table->string('source', 10)->default('manual'); // manual | excel
            $table->boolean('customer_created')->default(false);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('removed_at')->nullable();
            $table->foreignId('removed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('remove_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'removed_at']);
            $table->index(['customer_id', 'shop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_credits');
    }
};
