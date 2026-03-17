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
        Schema::table('credit_repayments', function (Blueprint $table) {
            // Drop old columns that don't match new design
            $table->dropConstrainedForeignId('credit_account_id');
            $table->dropConstrainedForeignId('sale_id');
            $table->dropColumn('repaid_at');

            // Add new columns for customer-based credit tracking
            $table->foreignId('customer_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('shop_id')->after('customer_id')->constrained()->onDelete('cascade');
            $table->timestamp('repayment_date')->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_repayments', function (Blueprint $table) {
            // Restore old columns
            $table->foreignId('credit_account_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_id')->after('amount')->constrained()->onDelete('cascade');
            $table->timestamp('repaid_at')->after('notes');

            // Drop new columns
            $table->dropConstrainedForeignId('customer_id');
            $table->dropConstrainedForeignId('shop_id');
            $table->dropColumn('repayment_date');
        });
    }
};
