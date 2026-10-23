<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Postgres can't run ALTER TYPE … ADD VALUE inside a transaction.
    public $withinTransaction = false;

    public function up(): void
    {
        DB::statement("ALTER TYPE user_role ADD VALUE IF NOT EXISTS 'super_admin'");
    }

    public function down(): void
    {
        // PostgreSQL does not support removing enum values.
    }
};
