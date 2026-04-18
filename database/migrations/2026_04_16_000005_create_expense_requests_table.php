<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference_number', 30)->unique();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('target_shop_id')->constrained('shops')->restrictOnDelete();
            $table->bigInteger('amount')->notNull();
            $table->text('reason')->notNull();
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->restrictOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE expense_requests ADD COLUMN status expense_request_status NOT NULL DEFAULT 'pending'");

        // Add FK back to expenses.expense_request_id
        DB::statement('ALTER TABLE expenses ADD CONSTRAINT expenses_expense_request_id_fkey FOREIGN KEY (expense_request_id) REFERENCES expense_requests(id) ON DELETE RESTRICT');

        DB::statement('CREATE INDEX expense_requests_warehouse_status_idx ON expense_requests(warehouse_id, status)');
        DB::statement('CREATE INDEX expense_requests_target_shop_status_idx ON expense_requests(target_shop_id, status)');
        DB::statement('CREATE INDEX expense_requests_reference_number_idx ON expense_requests(reference_number)');
    }

    public function down(): void
    {
        // Remove FK from expenses first
        DB::statement('ALTER TABLE expenses DROP CONSTRAINT IF EXISTS expenses_expense_request_id_fkey');
        Schema::dropIfExists('expense_requests');
    }
};
