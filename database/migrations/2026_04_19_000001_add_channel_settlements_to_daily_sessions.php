<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_sessions', 'momo_settled')) {
                $table->bigInteger('momo_settled')->nullable()->after('owner_momo_reference');
            }
            if (! Schema::hasColumn('daily_sessions', 'momo_settled_ref')) {
                $table->string('momo_settled_ref', 100)->nullable()->after('momo_settled');
            }
            if (! Schema::hasColumn('daily_sessions', 'card_settled')) {
                $table->bigInteger('card_settled')->nullable()->after('momo_settled_ref');
            }
            if (! Schema::hasColumn('daily_sessions', 'card_settled_ref')) {
                $table->string('card_settled_ref', 100)->nullable()->after('card_settled');
            }
            if (! Schema::hasColumn('daily_sessions', 'other_settled')) {
                $table->bigInteger('other_settled')->nullable()->after('card_settled_ref');
            }
            if (! Schema::hasColumn('daily_sessions', 'other_settled_ref')) {
                $table->string('other_settled_ref', 100)->nullable()->after('other_settled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'momo_settled',
                'momo_settled_ref',
                'card_settled',
                'card_settled_ref',
                'other_settled',
                'other_settled_ref',
            ]);
        });
    }
};
