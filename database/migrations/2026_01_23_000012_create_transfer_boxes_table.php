// database/migrations/2026_01_23_000012_create_transfer_boxes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('box_id')->constrained()->restrictOnDelete();
            
            // Scanning workflow
            $table->foreignId('scanned_out_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('scanned_out_at')->nullable();
            
            $table->foreignId('scanned_in_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('scanned_in_at')->nullable();
            
            $table->boolean('is_received')->default(false);
            $table->boolean('is_damaged')->default(false);
            $table->text('damage_notes')->nullable();
            
            $table->timestamps();
            
            // A box can only be in one active transfer at a time
            $table->unique(['transfer_id', 'box_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_boxes');
    }
};