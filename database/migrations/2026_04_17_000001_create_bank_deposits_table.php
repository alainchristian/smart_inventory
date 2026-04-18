<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_session_id')->constrained()->restrictOnDelete();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->bigInteger('amount');
            $table->string('bank_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('deposited_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('deposited_at');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('CREATE INDEX bank_deposits_daily_session_id_idx ON bank_deposits(daily_session_id)');
        DB::statement('CREATE INDEX bank_deposits_shop_id_deposited_at_idx ON bank_deposits(shop_id, deposited_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_deposits');
    }
};
