<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_sessions', 'total_refunds_cash')) {
                $table->bigInteger('total_refunds_cash')->nullable()->after('total_sales');
            }
            if (! Schema::hasColumn('daily_sessions', 'total_withdrawals')) {
                $table->bigInteger('total_withdrawals')->nullable()->after('total_expenses_cash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_sessions', function (Blueprint $table) {
            $table->dropColumn(['total_refunds_cash', 'total_withdrawals']);
        });
    }
};
