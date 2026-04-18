<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_session_id')->constrained()->restrictOnDelete();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->bigInteger('amount');
            $table->text('reason'); // required — e.g. "school fees", "personal use"
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->softDeletes(); // manager can void before session closes
        });

        DB::statement('CREATE INDEX owner_withdrawals_daily_session_id_idx ON owner_withdrawals(daily_session_id)');
        DB::statement('CREATE INDEX owner_withdrawals_shop_id_recorded_at_idx ON owner_withdrawals(shop_id, recorded_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_withdrawals');
    }
};
