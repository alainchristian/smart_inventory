<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TYPE user_role ADD VALUE IF NOT EXISTS 'admin'");

        if (!Schema::hasColumn('users', 'must_change_password')) {
            DB::statement("ALTER TABLE users ADD COLUMN must_change_password BOOLEAN NOT NULL DEFAULT false");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('must_change_password');
            });
        }
        // Note: PostgreSQL does not support removing enum values.
    }
};
