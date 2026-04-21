<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_deposits', function (Blueprint $table) {
            if (! Schema::hasColumn('bank_deposits', 'source')) {
                // 'cash' = depositing physical cash; 'mobile_money' = sending MoMo balance to bank
                $table->string('source', 20)->default('cash')->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bank_deposits', function (Blueprint $table) {
            if (Schema::hasColumn('bank_deposits', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
