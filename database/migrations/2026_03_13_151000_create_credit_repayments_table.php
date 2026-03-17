<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('credit_repayments')) {
            return;
        }

        Schema::create('credit_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_account_id')->constrained('customer_credit_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->foreignId('sale_id')->nullable()->nullOnDelete()->constrained('sales');
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('repaid_at');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE credit_repayments ADD COLUMN payment_method payment_method NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE credit_repayments ALTER COLUMN payment_method DROP DEFAULT");

        DB::statement('CREATE INDEX credit_repayments_account_id_idx ON credit_repayments(credit_account_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_repayments');
    }
};
