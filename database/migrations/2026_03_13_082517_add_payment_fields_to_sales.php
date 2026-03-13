<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Link to customer registry (replaces free-text customer_name / customer_phone
            // but keep those columns for backward compat / denorm receipts)
            if (!Schema::hasColumn('sales', 'customer_id')) {
                $table->foreignId('customer_id')
                      ->nullable()
                      ->constrained('customers')
                      ->nullOnDelete()
                      ->after('customer_phone');
            }

            // Split payment summary
            if (!Schema::hasColumn('sales', 'is_split_payment')) {
                $table->boolean('is_split_payment')->default(false)->after('customer_id');
            }
            if (!Schema::hasColumn('sales', 'amount_paid')) {
                $table->unsignedBigInteger('amount_paid')->default(0)->after('is_split_payment');
            }
            if (!Schema::hasColumn('sales', 'credit_amount')) {
                $table->unsignedBigInteger('credit_amount')->default(0)->after('amount_paid');
            }
            if (!Schema::hasColumn('sales', 'has_credit')) {
                $table->boolean('has_credit')->default(false)->after('credit_amount');
            }
        });

        // Create indexes if they don't exist
        $indexExists = DB::select("SELECT 1 FROM pg_indexes WHERE indexname = 'sales_customer_id_idx'");
        if (empty($indexExists)) {
            DB::statement("CREATE INDEX sales_customer_id_idx ON sales(customer_id)");
        }

        $indexExists = DB::select("SELECT 1 FROM pg_indexes WHERE indexname = 'sales_has_credit_idx'");
        if (empty($indexExists)) {
            DB::statement("CREATE INDEX sales_has_credit_idx ON sales(has_credit)");
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn([
                'customer_id',
                'is_split_payment',
                'amount_paid',
                'credit_amount',
                'has_credit',
            ]);
        });
    }
};
