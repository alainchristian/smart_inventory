<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create withdrawal_method enum type if it doesn't exist
        DB::statement("DO \$\$ BEGIN
            CREATE TYPE withdrawal_method AS ENUM ('cash', 'mobile_money');
        EXCEPTION WHEN duplicate_object THEN null; END \$\$;");

        // expenses: add is_system_generated flag
        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'is_system_generated')) {
                $table->boolean('is_system_generated')->default(false)->after('expense_request_id');
            }
        });

        // owner_withdrawals: add method enum + momo reference
        if (! Schema::hasColumn('owner_withdrawals', 'method')) {
            DB::statement("ALTER TABLE owner_withdrawals ADD COLUMN method withdrawal_method NOT NULL DEFAULT 'cash'");
        }

        Schema::table('owner_withdrawals', function (Blueprint $table) {
            if (! Schema::hasColumn('owner_withdrawals', 'momo_reference')) {
                $table->string('momo_reference', 100)->nullable()->after('reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('is_system_generated');
        });

        Schema::table('owner_withdrawals', function (Blueprint $table) {
            $table->dropColumn(['method', 'momo_reference']);
        });
    }
};
