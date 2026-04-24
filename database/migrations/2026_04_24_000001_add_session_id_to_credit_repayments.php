<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_repayments', function (Blueprint $table) {
            if (! Schema::hasColumn('credit_repayments', 'daily_session_id')) {
                $table->foreignId('daily_session_id')
                    ->nullable()
                    ->after('shop_id')
                    ->constrained('daily_sessions')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('credit_repayments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('daily_session_id');
        });
    }
};
