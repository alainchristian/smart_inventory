<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'fulfillment_type')) {
                $table->string('fulfillment_type', 30)->default('shop')->after('notes');
            }
            if (!Schema::hasColumn('sales', 'source_warehouse_id')) {
                $table->unsignedBigInteger('source_warehouse_id')->nullable()->after('fulfillment_type');
                $table->foreign('source_warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            }
            if (!Schema::hasColumn('sales', 'fulfillment_status')) {
                $table->string('fulfillment_status', 30)->nullable()->after('source_warehouse_id');
            }
            if (!Schema::hasColumn('sales', 'fulfillment_method')) {
                $table->string('fulfillment_method', 30)->nullable()->after('fulfillment_status');
            }
            if (!Schema::hasColumn('sales', 'fulfillment_transporter_id')) {
                $table->unsignedBigInteger('fulfillment_transporter_id')->nullable()->after('fulfillment_method');
                $table->foreign('fulfillment_transporter_id')->references('id')->on('transporters')->nullOnDelete();
            }
            if (!Schema::hasColumn('sales', 'fulfillment_notes')) {
                $table->text('fulfillment_notes')->nullable()->after('fulfillment_transporter_id');
            }
            if (!Schema::hasColumn('sales', 'fulfillment_confirmed_at')) {
                $table->timestamp('fulfillment_confirmed_at')->nullable()->after('fulfillment_notes');
            }
            if (!Schema::hasColumn('sales', 'fulfillment_confirmed_by')) {
                $table->unsignedBigInteger('fulfillment_confirmed_by')->nullable()->after('fulfillment_confirmed_at');
                $table->foreign('fulfillment_confirmed_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['source_warehouse_id']);
            $table->dropForeign(['fulfillment_transporter_id']);
            $table->dropForeign(['fulfillment_confirmed_by']);
            $table->dropColumns([
                'fulfillment_type', 'source_warehouse_id', 'fulfillment_status',
                'fulfillment_method', 'fulfillment_transporter_id', 'fulfillment_notes',
                'fulfillment_confirmed_at', 'fulfillment_confirmed_by',
            ]);
        });
    }
};
