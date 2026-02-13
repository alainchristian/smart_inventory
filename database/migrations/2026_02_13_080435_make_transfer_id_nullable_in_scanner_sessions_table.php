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
        Schema::table('scanner_sessions', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['transfer_id']);

            // Make transfer_id nullable
            $table->foreignId('transfer_id')->nullable()->change();

            // Re-add foreign key constraint
            $table->foreign('transfer_id')->references('id')->on('transfers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scanner_sessions', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['transfer_id']);

            // Make transfer_id NOT NULL
            $table->foreignId('transfer_id')->nullable(false)->change();

            // Re-add foreign key constraint
            $table->foreign('transfer_id')->references('id')->on('transfers')->onDelete('cascade');
        });
    }
};
