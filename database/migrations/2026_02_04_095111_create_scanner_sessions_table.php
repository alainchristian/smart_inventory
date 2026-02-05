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
        Schema::create('scanner_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_code', 10)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('page_type'); // 'pack_transfer' or 'receive_transfer'
            $table->foreignId('transfer_id')->constrained()->onDelete('cascade');
            $table->string('last_scanned_barcode')->nullable();
            $table->timestamp('last_scan_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['session_code', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scanner_sessions');
    }
};
