<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'is_split_payment')) {
                $table->boolean('is_split_payment')->default(false)->after('payment_method');
            }
            if (!Schema::hasColumn('sales', 'amount_paid')) {
                $table->bigInteger('amount_paid')->default(0)->after('is_split_payment');
            }
            if (!Schema::hasColumn('sales', 'credit_amount')) {
                $table->bigInteger('credit_amount')->default(0)->after('amount_paid');
            }
            if (!Schema::hasColumn('sales', 'has_credit')) {
                $table->boolean('has_credit')->default(false)->after('credit_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['is_split_payment', 'amount_paid', 'credit_amount', 'has_credit']);
        });
    }
};
