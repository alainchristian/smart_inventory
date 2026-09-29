<?php

use App\Services\Reports\ReportSchedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Custom report email schedules move from a free-text cron string to a
 * daily / weekly / monthly picker (saved_reports.schedule, see
 * App\Services\Reports\ReportSchedule). Existing crons that fit are
 * converted; anything else is left off, and the old value stays in
 * schedule_cron for reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saved_reports', function (Blueprint $table) {
            $table->jsonb('schedule')->nullable()->after('schedule_cron');
        });

        DB::table('saved_reports')->whereNotNull('schedule_cron')->orderBy('id')->each(function ($row) {
            $schedule = ReportSchedule::fromCron($row->schedule_cron);
            if ($schedule) {
                $schedule['since'] = now()->utc()->toIso8601String();
                DB::table('saved_reports')->where('id', $row->id)->update(['schedule' => json_encode($schedule)]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('saved_reports', function (Blueprint $table) {
            $table->dropColumn('schedule');
        });
    }
};
