<?php

namespace App\Console\Commands;

use App\Mail\ScheduledReportMail;
use App\Models\SavedReport;
use App\Services\Reports\ReportDocument;
use App\Services\Reports\ReportExporter;
use App\Services\Reports\ReportRunner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails custom reports whose schedule (daily / weekly / monthly at a
 * time, business timezone) has come round. Runs every 15 minutes; see
 * ReportSchedule::isDue() for why a late or missed run is sent once and
 * a newly set schedule never sends straight away.
 */
class RunScheduledReports extends Command
{
    protected $signature   = 'reports:run-scheduled {--report= : Only this report id} {--force : Send even if not due}';
    protected $description = 'Email saved custom reports whose schedule is due';

    public function handle(ReportRunner $runner, ReportExporter $exporter): int
    {
        $reports = SavedReport::with('creator')
            ->whereNotNull('schedule')
            ->whereNotNull('schedule_recipients')
            ->when($this->option('report'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        $sent = 0;
        foreach ($reports as $report) {
            $schedule = $report->emailSchedule();
            if (! $schedule || (! $this->option('force') && ! $schedule->isDue($report->last_scheduled_run_at))) {
                continue;
            }
            if (! $report->creator || ! $report->creator->is_active) {
                $this->warn("Skipped {$report->name}: its owner's account is inactive.");
                continue;
            }

            try {
                $started = microtime(true);
                $results = $runner->run($report->resolvedConfig(), null, false);
                $runner->recordRun($report->id, $report->resolvedConfig(), $results, (int) round((microtime(true) - $started) * 1000), scheduled: true);

                $doc  = new ReportDocument($report, [], null, $results);
                $file = $exporter->render($doc, $schedule->format);
            } catch (\Throwable $e) {
                report($e);
                $this->error("Could not run {$report->name}: {$e->getMessage()}");
                continue;   // retried on the next tick
            }

            // Mark it sent before mailing, so a mail failure can't cause a flood of repeats
            $report->forceFill(['last_scheduled_run_at' => now()])->saveQuietly();

            foreach ($report->schedule_recipients as $email) {
                try {
                    Mail::to($email)->send(new ScheduledReportMail($doc, $schedule->format, $file));
                } catch (\Throwable $e) {
                    Log::warning('Scheduled report email failed', ['report_id' => $report->id, 'to' => $email, 'error' => $e->getMessage()]);
                    $this->warn("Could not email {$email}: {$e->getMessage()}");
                }
            }

            $this->info("Sent {$report->name} to " . count($report->schedule_recipients) . ' recipient(s).');
            $sent++;
        }

        $this->info("Done. {$sent} report(s) sent.");

        return self::SUCCESS;
    }
}
