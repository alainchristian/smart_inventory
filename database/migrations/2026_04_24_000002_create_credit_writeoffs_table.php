<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_writeoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->bigInteger('amount');
            $table->bigInteger('balance_before');
            $table->bigInteger('balance_after');
            $table->text('reason');
            $table->foreignId('written_off_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('written_off_at');
            $table->timestamps();
            // No soft deletes — write-offs are permanent records
        });

        DB::statement('CREATE INDEX credit_writeoffs_customer_id_idx ON credit_writeoffs(customer_id)');
        DB::statement('CREATE INDEX credit_writeoffs_shop_id_written_off_at_idx ON credit_writeoffs(shop_id, written_off_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_writeoffs');
    }
};
