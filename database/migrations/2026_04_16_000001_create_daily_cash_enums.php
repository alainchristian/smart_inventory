<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DO \$\$ BEGIN CREATE TYPE daily_session_status AS ENUM ('open', 'closed', 'locked'); EXCEPTION WHEN duplicate_object THEN null; END \$\$;");
        DB::statement("DO \$\$ BEGIN CREATE TYPE expense_payment_method AS ENUM ('cash', 'mobile_money', 'bank_transfer', 'other'); EXCEPTION WHEN duplicate_object THEN null; END \$\$;");
        DB::statement("DO \$\$ BEGIN CREATE TYPE expense_request_status AS ENUM ('pending', 'approved', 'rejected', 'paid'); EXCEPTION WHEN duplicate_object THEN null; END \$\$;");
    }

    public function down(): void
    {
        DB::statement("DROP TYPE IF EXISTS expense_request_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS expense_payment_method CASCADE");
        DB::statement("DROP TYPE IF EXISTS daily_session_status CASCADE");
    }
};
