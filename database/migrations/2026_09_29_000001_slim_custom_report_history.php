<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Custom report run history kept the full results JSON of every run (12
 * per report), and saved_reports kept another copy. The rebuilt viewer
 * re-runs from the (cached) analytics services, so history only needs the
 * headline figures of each block.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_run_history', function (Blueprint $table) {
            $table->jsonb('summary')->nullable()->after('results');
        });

        DB::table('report_run_history')->update(['results' => null]);
        DB::table('saved_reports')->update([
            'last_results'      => null,
            'results_cached_at' => null,
            'results_stale_at'  => null,
        ]);
    }

    public function down(): void
    {
        Schema::table('report_run_history', function (Blueprint $table) {
            $table->dropColumn('summary');
        });
    }
};
