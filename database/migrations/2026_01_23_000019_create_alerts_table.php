// database/migrations/2026_01_23_000019_create_alerts_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            
            // Alert content
            $table->string('title');
            $table->text('message');

            // Related entity
            $table->string('entity_type', 100)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            
            // Target user
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            
            // Alert state
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            $table->boolean('is_dismissed')->default(false);
            $table->timestamp('dismissed_at')->nullable();
            
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Action link
            $table->string('action_url')->nullable();
            $table->string('action_label')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'is_read', 'created_at']);
        });

        // Add enum column
        DB::statement("ALTER TABLE alerts ADD COLUMN severity alert_severity DEFAULT 'info'");
        DB::statement('CREATE INDEX alerts_severity_is_resolved_idx ON alerts(severity, is_resolved)');
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};