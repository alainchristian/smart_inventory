<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add shop_owner to the user_role enum
        DB::statement("ALTER TYPE user_role ADD VALUE IF NOT EXISTS 'shop_owner'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot remove enum value in PostgreSQL easily
        // Would need to recreate the enum type
        // For safety, we'll leave it
    }
};
