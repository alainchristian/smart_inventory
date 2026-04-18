<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->notNull();
            $table->text('description')->nullable();
            $table->string('applies_to', 20)->notNull()->default('both');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('expense_categories')->insert([
            ['name' => 'Staff Transport',       'applies_to' => 'both',      'sort_order' => 1,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Staff Meals / Lunch',   'applies_to' => 'both',      'sort_order' => 2,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Internet / Airtime',    'applies_to' => 'both',      'sort_order' => 3,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cleaning Supplies',     'applies_to' => 'shop',      'sort_order' => 4,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shop Rent',             'applies_to' => 'shop',      'sort_order' => 5,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Equipment Repair',      'applies_to' => 'both',      'sort_order' => 6,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Packaging / Supplies',  'applies_to' => 'warehouse', 'sort_order' => 7,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Delivery / Fuel',       'applies_to' => 'warehouse', 'sort_order' => 8,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Warehouse Support',     'applies_to' => 'shop',      'sort_order' => 9,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other',                 'applies_to' => 'both',      'sort_order' => 99, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
