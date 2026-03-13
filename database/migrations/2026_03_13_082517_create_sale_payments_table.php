<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sale_payments')) {
            Schema::create('sale_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('amount');             // amount in cents
                $table->string('reference', 100)->nullable();     // MoMo tx ID, card auth, etc.
                $table->text('notes')->nullable();
                $table->timestamps();
            });

            DB::statement("ALTER TABLE sale_payments ADD COLUMN payment_method payment_method NOT NULL");
            DB::statement("CREATE INDEX sale_payments_sale_id_idx ON sale_payments(sale_id)");
            DB::statement("CREATE INDEX sale_payments_method_idx ON sale_payments(payment_method)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
