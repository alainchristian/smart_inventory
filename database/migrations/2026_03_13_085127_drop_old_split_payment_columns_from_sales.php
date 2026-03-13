<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop columns that were added by the old migration
            if (Schema::hasColumn('sales', 'is_split_payment')) {
                $table->dropColumn('is_split_payment');
            }
            if (Schema::hasColumn('sales', 'amount_paid')) {
                $table->dropColumn('amount_paid');
            }
            if (Schema::hasColumn('sales', 'credit_amount')) {
                $table->dropColumn('credit_amount');
            }
            if (Schema::hasColumn('sales', 'has_credit')) {
                $table->dropColumn('has_credit');
            }
        });
    }

    public function down(): void
    {
        // No need to recreate them - the next migration will add them back
    }
};
