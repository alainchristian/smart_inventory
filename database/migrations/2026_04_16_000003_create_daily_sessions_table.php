<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('session_date')->notNull();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->bigInteger('opening_balance')->default(0)->comment('RWF integer');
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('opened_at')->notNull();

            // Populated at close time
            $table->bigInteger('total_sales_cash')->nullable();
            $table->bigInteger('total_sales_momo')->nullable();
            $table->bigInteger('total_sales_card')->nullable();
            $table->bigInteger('total_sales_other')->nullable();
            $table->bigInteger('total_sales')->nullable();
            $table->bigInteger('total_expenses')->nullable();
            $table->bigInteger('total_expenses_cash')->nullable();
            $table->bigInteger('expected_cash')->nullable();
            $table->bigInteger('actual_cash_counted')->nullable();
            $table->bigInteger('cash_variance')->nullable();

            // Cash disposition
            $table->bigInteger('cash_to_bank')->nullable();
            $table->string('bank_reference', 100)->nullable();
            $table->bigInteger('cash_retained')->nullable();
            $table->text('notes')->nullable();

            // Close tracking
            $table->foreignId('closed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('closed_at')->nullable();

            // Lock tracking
            $table->foreignId('locked_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('locked_at')->nullable();

            $table->timestamps();
        });

        DB::statement("ALTER TABLE daily_sessions ADD COLUMN status daily_session_status NOT NULL DEFAULT 'open'");

        // Unique index: one session per shop per day
        DB::statement('CREATE UNIQUE INDEX daily_sessions_shop_id_session_date_unique ON daily_sessions(shop_id, session_date)');
        DB::statement('CREATE INDEX daily_sessions_session_date_idx ON daily_sessions(session_date)');
        DB::statement('CREATE INDEX daily_sessions_status_idx ON daily_sessions(status)');
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_sessions');
    }
};
