<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_sessions', 'total_sales_bank_transfer')) {
                $table->bigInteger('total_sales_bank_transfer')->nullable()->after('total_sales_card');
            }
            if (! Schema::hasColumn('daily_sessions', 'total_sales_credit')) {
                $table->bigInteger('total_sales_credit')->nullable()->after('total_sales_bank_transfer');
            }
            if (! Schema::hasColumn('daily_sessions', 'bank_transfer_settled')) {
                $table->bigInteger('bank_transfer_settled')->nullable()->after('other_settled_ref');
            }
            if (! Schema::hasColumn('daily_sessions', 'bank_transfer_settled_ref')) {
                $table->string('bank_transfer_settled_ref', 100)->nullable()->after('bank_transfer_settled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'total_sales_bank_transfer',
                'total_sales_credit',
                'bank_transfer_settled',
                'bank_transfer_settled_ref',
            ]);
        });
    }
};
