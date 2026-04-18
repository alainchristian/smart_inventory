<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_sessions', 'transaction_count')) {
                $table->integer('transaction_count')->nullable()->after('total_sales');
            }
            if (! Schema::hasColumn('daily_sessions', 'total_expenses_momo')) {
                $table->bigInteger('total_expenses_momo')->nullable()->after('total_expenses_cash');
            }
            if (! Schema::hasColumn('daily_sessions', 'total_withdrawals_cash')) {
                $table->bigInteger('total_withdrawals_cash')->nullable()->after('total_withdrawals');
            }
            if (! Schema::hasColumn('daily_sessions', 'total_withdrawals_momo')) {
                $table->bigInteger('total_withdrawals_momo')->nullable()->after('total_withdrawals_cash');
            }
            if (! Schema::hasColumn('daily_sessions', 'total_bank_deposits')) {
                $table->bigInteger('total_bank_deposits')->nullable()->after('total_withdrawals_momo');
            }
            if (! Schema::hasColumn('daily_sessions', 'bank_deposit_count')) {
                $table->integer('bank_deposit_count')->nullable()->after('total_bank_deposits');
            }
            if (! Schema::hasColumn('daily_sessions', 'cash_to_owner_momo')) {
                $table->bigInteger('cash_to_owner_momo')->nullable()->default(0)->after('cash_variance');
            }
            if (! Schema::hasColumn('daily_sessions', 'owner_momo_reference')) {
                $table->string('owner_momo_reference', 100)->nullable()->after('cash_to_owner_momo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_count',
                'total_expenses_momo',
                'total_withdrawals_cash',
                'total_withdrawals_momo',
                'total_bank_deposits',
                'bank_deposit_count',
                'cash_to_owner_momo',
                'owner_momo_reference',
            ]);
        });
    }
};
