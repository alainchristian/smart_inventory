<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_sessions', 'cash_deposits')) {
                $table->bigInteger('cash_deposits')->nullable()->after('bank_deposit_count');
            }
            if (! Schema::hasColumn('daily_sessions', 'momo_deposits')) {
                $table->bigInteger('momo_deposits')->nullable()->after('cash_deposits');
            }
            if (! Schema::hasColumn('daily_sessions', 'total_repayments')) {
                $table->bigInteger('total_repayments')->nullable()->after('momo_deposits');
            }
            if (! Schema::hasColumn('daily_sessions', 'total_repayments_cash')) {
                $table->bigInteger('total_repayments_cash')->nullable()->after('total_repayments');
            }
            if (! Schema::hasColumn('daily_sessions', 'total_repayments_momo')) {
                $table->bigInteger('total_repayments_momo')->nullable()->after('total_repayments_cash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_sessions', function (Blueprint $table) {
            $table->dropColumnIfExists('cash_deposits');
            $table->dropColumnIfExists('momo_deposits');
            $table->dropColumnIfExists('total_repayments');
            $table->dropColumnIfExists('total_repayments_cash');
            $table->dropColumnIfExists('total_repayments_momo');
        });
    }
};
