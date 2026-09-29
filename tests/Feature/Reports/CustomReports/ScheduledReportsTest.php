<?php

namespace Tests\Feature\Reports\CustomReports;

use App\Livewire\Owner\Reports\ReportBuilder;
use App\Mail\ScheduledReportMail;
use App\Models\ReportRunHistory;
use App\Models\SavedReport;
use App\Models\User;
use App\Services\Reports\ReportSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Emailed custom reports (phase 7) */
class ScheduledReportsTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::forceCreate([
            'name' => 'Owner', 'email' => 'o' . uniqid() . '@example.test', 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);
        // Only this test's reports are scheduled
        SavedReport::query()->update(['schedule' => null]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Freeze the clock at a Kigali wall-clock time */
    private function at(string $kigali): void
    {
        Carbon::setTestNow(Carbon::parse($kigali, config('tenant.timezone'))->utc());
    }

    private function schedule(array $s): ReportSchedule
    {
        return ReportSchedule::fromArray($s + ['format' => 'pdf', 'since' => '2026-01-01T00:00:00Z']);
    }

    private function report(array $schedule, array $attrs = []): SavedReport
    {
        return SavedReport::create(array_merge([
            'name'                => 'Weekly brief',
            'created_by'          => $this->owner->id,
            'config'              => ['date_range' => 'week', 'blocks' => [
                ['id' => 'k1', 'metric_id' => 'sales_revenue', 'title' => 'Revenue', 'viz' => 'kpi_card'],
                ['id' => 't1', 'metric_id' => 'sales_payment_methods', 'title' => 'How customers paid', 'viz' => 'table'],
            ]],
            'schedule'            => $schedule + ['format' => 'pdf', 'since' => now()->subDays(10)->toIso8601String()],
            'schedule_recipients' => ['boss@example.com', 'accounts@example.com'],
        ], $attrs));
    }

    // ── ReportSchedule ───────────────────────────────────────────────────

    public function test_occurrences_and_labels(): void
    {
        $this->at('2026-09-30 10:00');   // Wednesday

        $daily = $this->schedule(['frequency' => 'daily', 'time' => '08:00']);
        $this->assertSame('2026-09-30 08:00', $daily->latestOccurrence()->format('Y-m-d H:i'));
        $this->assertSame('2026-10-01 08:00', $daily->nextOccurrence()->format('Y-m-d H:i'));
        $this->assertSame('Every day at 08:00 · PDF', $daily->label());

        $weekly = $this->schedule(['frequency' => 'weekly', 'day' => 5, 'time' => '17:30', 'format' => 'xlsx']);
        $this->assertSame('2026-09-25 17:30', $weekly->latestOccurrence()->format('Y-m-d H:i'));   // last Friday
        $this->assertSame('2026-10-02 17:30', $weekly->nextOccurrence()->format('Y-m-d H:i'));
        $this->assertSame('Every Friday at 17:30 · Excel', $weekly->label());

        $monthly = $this->schedule(['frequency' => 'monthly', 'day' => 1, 'time' => '07:00']);
        $this->assertSame('2026-09-01 07:00', $monthly->latestOccurrence()->format('Y-m-d H:i'));
        $this->assertSame('2026-10-01 07:00', $monthly->nextOccurrence()->format('Y-m-d H:i'));
        $this->assertSame('Monthly on the 1st at 07:00 · PDF', $monthly->label());

        $late = $this->schedule(['frequency' => 'monthly', 'day' => 30]);   // clamped: not every month has it
        $this->assertSame(1, $late->day);
    }

    public function test_due_logic(): void
    {
        $this->at('2026-09-30 08:10');
        $s = ReportSchedule::fromArray(['frequency' => 'daily', 'time' => '08:00', 'since' => now()->subMinutes(30)->toIso8601String()]);

        $this->assertTrue($s->isDue(null), 'first 08:00 after the schedule was set');
        $this->assertFalse($s->isDue(now()), 'already sent today');

        // A schedule set after today's time waits for tomorrow instead of sending at once
        $fresh = ReportSchedule::fromArray(['frequency' => 'daily', 'time' => '08:00', 'since' => now()->toIso8601String()]);
        $this->assertFalse($fresh->isDue(null));

        // Server down for three days: one catch-up email, not three
        $this->at('2026-10-03 09:00');
        $this->assertTrue($s->isDue(Carbon::parse('2026-09-30 06:10', 'UTC')));
        $this->assertFalse($s->isDue(now()));
    }

    public function test_old_cron_values_convert_when_they_fit(): void
    {
        $this->assertSame(['frequency' => 'daily', 'day' => 0, 'time' => '08:00', 'format' => 'pdf'], ReportSchedule::fromCron('0 8 * * *'));
        $this->assertSame(7, ReportSchedule::fromCron('30 18 * * 0')['day']);        // cron Sunday = 0
        $this->assertSame('monthly', ReportSchedule::fromCron('0 6 1 * *')['frequency']);
        $this->assertNull(ReportSchedule::fromCron('*/15 * * * *'));
        $this->assertNull(ReportSchedule::fromCron('every monday'));
    }

    // ── The command ──────────────────────────────────────────────────────

    public function test_due_report_is_emailed_with_the_attachment(): void
    {
        Mail::fake();
        $this->at('2026-09-28 08:05');   // Monday
        $report = $this->report(['frequency' => 'weekly', 'day' => 1, 'time' => '08:00', 'format' => 'xlsx']);

        $this->artisan('reports:run-scheduled')->assertSuccessful();

        Mail::assertSent(ScheduledReportMail::class, 2);
        Mail::assertSent(ScheduledReportMail::class, function (ScheduledReportMail $m) {
            $file = $m->attachments()[0];
            return $m->hasTo('boss@example.com') && $m->format === 'xlsx'
                && $file->as === 'weekly-brief_2026-09-28.xlsx'   // the week so far: Monday only
                && $file->mime === \App\Services\Reports\ReportExporter::FORMATS['xlsx'];
        });

        $report->refresh();
        $this->assertNotNull($report->last_scheduled_run_at);
        $this->assertSame(0, $report->run_count, 'scheduled sends are not manual runs');
        $run = ReportRunHistory::where('report_id', $report->id)->sole();
        $this->assertTrue($run->was_scheduled);
        $this->assertNull($run->results);
        $this->assertNotEmpty($run->summary);

        // 15 minutes later: nothing new
        $this->at('2026-09-28 08:20');
        $this->artisan('reports:run-scheduled')->assertSuccessful();
        Mail::assertSent(ScheduledReportMail::class, 2);
    }

    public function test_not_due_off_and_inactive_owner_are_skipped(): void
    {
        Mail::fake();
        $this->at('2026-09-28 07:55');
        $this->report(['frequency' => 'weekly', 'day' => 1, 'time' => '08:00', 'since' => now()->subHour()->toIso8601String()]);   // not yet
        $this->report(['frequency' => 'daily', 'time' => '06:00'], ['schedule_recipients' => null]);     // nobody to send to

        $inactive = User::forceCreate([
            'name' => 'Former', 'email' => 'f' . uniqid() . '@example.test', 'password' => 'x',
            'role' => 'owner', 'is_active' => false, 'must_change_password' => false,
        ]);
        $this->report(['frequency' => 'daily', 'time' => '06:00'], ['created_by' => $inactive->id]);

        $this->artisan('reports:run-scheduled')->assertSuccessful();
        Mail::assertNothingSent();
    }

    public function test_email_body(): void
    {
        $report = $this->report(['frequency' => 'daily', 'time' => '08:00']);
        $doc  = new \App\Services\Reports\ReportDocument($report);
        $html = (new ScheduledReportMail($doc, 'pdf', '%PDF'))->render();

        $this->assertStringContainsString('Weekly brief', $html);
        $this->assertStringContainsString('Revenue', $html);
        $this->assertStringContainsString('attached as a PDF', $html);
        $this->assertStringContainsString(route('owner.reports.custom.view', $report->id), $html);
        $this->assertStringContainsString('Every day at 08:00', $html);
    }

    // ── The builder ──────────────────────────────────────────────────────

    public function test_builder_saves_and_edits_the_schedule(): void
    {
        $this->at('2026-09-29 12:00');
        $c = Livewire::actingAs($this->owner)->test(ReportBuilder::class)
            ->set('reportName', 'Emailed pack')
            ->call('addBlock', 'sales_revenue')
            ->set('scheduleFrequency', 'weekly')
            ->set('scheduleDay', 1)
            ->set('scheduleTime', '07:30')
            ->set('scheduleRecipients', 'Boss@Example.com; accounts@example.com, boss@example.com')
            ->assertSee('Next email Mon 5 Oct at 07:30')
            ->call('save')
            ->assertHasNoErrors();

        $report = SavedReport::where('name', 'Emailed pack')->firstOrFail();
        $this->assertSame(['boss@example.com', 'accounts@example.com'], $report->schedule_recipients);
        $this->assertSame('Every Monday at 07:30 · PDF', $report->emailSchedule()->label());
        $since = $report->schedule['since'];

        // Only the recipients change: the schedule keeps its start
        $this->at('2026-09-30 12:00');
        Livewire::actingAs($this->owner)->test(ReportBuilder::class, ['reportId' => $report->id])
            ->assertSet('scheduleFrequency', 'weekly')
            ->set('scheduleRecipients', 'boss@example.com')->call('save');
        $this->assertSame($since, $report->fresh()->schedule['since']);

        // The time changes: it starts afresh
        Livewire::actingAs($this->owner)->test(ReportBuilder::class, ['reportId' => $report->id])
            ->set('scheduleTime', '09:00')->call('save');
        $this->assertNotSame($since, $report->fresh()->schedule['since']);

        // Off clears it
        Livewire::actingAs($this->owner)->test(ReportBuilder::class, ['reportId' => $report->id])
            ->set('scheduleFrequency', '')->call('save');
        $this->assertNull($report->fresh()->schedule);
        $this->assertNull($report->fresh()->emailSchedule());
    }
}
