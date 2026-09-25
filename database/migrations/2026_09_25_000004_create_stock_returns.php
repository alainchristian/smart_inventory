<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Return to warehouse (shop → warehouse), the reverse of a transfer.
 *
 * While a return is on its way, its boxes are `in_transit` — a new
 * box_status value. Every stock query filters on status full/partial, so an
 * in-transit box automatically drops out of the POS, sales, stock pages and
 * transfers without touching those queries.
 */
return new class extends Migration
{
    // ALTER TYPE … ADD VALUE must not run inside a transaction block on older Postgres
    public $withinTransaction = false;

    public function up(): void
    {
        DB::statement("ALTER TYPE box_status ADD VALUE IF NOT EXISTS 'in_transit'");

        Schema::create('stock_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('shop_id')->constrained('shops');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->string('status', 20)->default('in_transit'); // in_transit | received | cancelled
            $table->text('reason')->nullable();
            $table->foreignId('sent_by')->constrained('users');
            $table->timestamp('sent_at');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamp('received_at')->nullable();
            $table->text('receipt_notes')->nullable();
            $table->boolean('has_discrepancy')->default(false);
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'status']);
            $table->index(['shop_id', 'status']);
        });

        Schema::create('stock_return_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_return_id')->constrained('stock_returns')->cascadeOnDelete();
            $table->foreignId('box_id')->constrained('boxes');
            $table->unsignedInteger('items_sent');
            $table->string('previous_status', 20);                // full | partial — restored on cancel
            $table->string('outcome', 20)->nullable();           // received | damaged | missing
            $table->timestamps();

            $table->unique(['stock_return_id', 'box_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_return_boxes');
        Schema::dropIfExists('stock_returns');
        // Postgres can't drop an enum value; 'in_transit' is left in box_status.
    }
};
