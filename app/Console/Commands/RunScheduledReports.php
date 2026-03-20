<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunScheduledReports extends Command
{
    protected $signature   = 'reports:run-scheduled';
    protected $description = 'Run all saved reports whose cron schedule is due';

    public function handle(): int
    {
        $reports = \App\Models\SavedReport::whereNotNull('schedule_cron')
            ->whereNotNull('schedule_recipients')
            ->whereNull('deleted_at')
            ->get();

        $ran = 0;
        foreach ($reports as $report) {
            try {
                $cron = new \Cron\CronExpression($report->schedule_cron);
            } catch (\Throwable) {
                $this->warn("Report {$report->id} has invalid cron: {$report->schedule_cron}");
                continue;
            }

            // Run if never scheduled before or cron is due
            if ($report->last_scheduled_run_at && ! $cron->isDue()) {
                continue;
            }

            $runner  = app(\App\Services\Reports\ReportRunner::class);
            $results = $runner->run($report->resolvedConfig(), $report->id, false);
            $report->update(['last_scheduled_run_at' => now()]);

            // Save a history record marked as scheduled
            \App\Models\ReportRunHistory::create([
                'report_id'       => $report->id,
                'run_by'          => $report->created_by,
                'run_at'          => now(),
                'config_snapshot' => $report->resolvedConfig(),
                'results'         => $results,
                'duration_ms'     => 0,
                'was_scheduled'   => true,
            ]);

            // Cache the results
            $report->cacheResults($results);

            // Notify recipients via a simple plain-text email
            foreach (($report->schedule_recipients ?? []) as $email) {
                try {
                    \Illuminate\Support\Facades\Mail::raw(
                        "Your scheduled report \"{$report->name}\" has been run.\n\n"
                        . "View it at: " . route('owner.reports.custom.view', $report->id) . "\n",
                        function ($m) use ($email, $report) {
                            $m->to($email)->subject('Scheduled Report: ' . $report->name);
                        }
                    );
                } catch (\Throwable $e) {
                    $this->warn("Could not email {$email}: " . $e->getMessage());
                }
            }

            $this->info("Ran: {$report->name}");
            $ran++;
        }

        $this->info("Done. {$ran} report(s) ran.");
        return self::SUCCESS;
    }
}
