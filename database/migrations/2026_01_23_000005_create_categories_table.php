// database/migrations/2026_01_23_000005_create_categories_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            
            // Hierarchical categories
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            
            // For efficient tree queries
            $table->unsignedInteger('left')->default(0);
            $table->unsignedInteger('right')->default(0);
            $table->unsignedInteger('depth')->default(0);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Nested set model indexes
            $table->index(['left', 'right']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};