<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('daily_session_id')->constrained('daily_sessions')->restrictOnDelete();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->bigInteger('amount')->notNull()->comment('RWF integer');
            $table->text('description')->notNull();
            $table->string('receipt_reference', 100)->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('recorded_at')->notNull();
            $table->unsignedBigInteger('expense_request_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE expenses ADD COLUMN payment_method expense_payment_method NOT NULL DEFAULT 'cash'");

        DB::statement('CREATE INDEX expenses_session_recorded_idx ON expenses(daily_session_id, recorded_at)');
        DB::statement('CREATE INDEX expenses_expense_request_id_idx ON expenses(expense_request_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
