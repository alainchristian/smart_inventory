<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where an action happened. activity_logs is append-only at the database
     * level (triggers refuse UPDATE/DELETE), so existing rows are NOT back-filled:
     * the screen falls back to the actor's assigned location for those.
     * These columns are outside the hash chain, so `audit:verify` is unaffected.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('location_type', 20)->nullable();           // 'shop' | 'warehouse'
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('location_name', 150)->nullable();          // snapshot: survives a rename
            $table->index(['location_type', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['location_type', 'location_id']);
            $table->dropColumn(['location_type', 'location_id', 'location_name']);
        });
    }
};
