<?php

namespace Tests\Feature\Reports\CustomReports;

use App\Livewire\Owner\Reports\ReportBuilder;
use App\Livewire\Owner\Reports\ReportLibrary;
use App\Livewire\Owner\Reports\ReportViewer;
use App\Models\SavedReport;
use App\Models\User;
use App\Services\Reports\ExportReportAction;
use App\Services\Reports\ReportRunner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Custom Reports, phase 1: bug fixes on the existing module
 * (edit flow, access control, business-tz dates, comparison span,
 * block id collisions, wipe table names, CSV injection).
 */
class CustomReportsPhase1Test extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return User::forceCreate([
            'name' => 'Owner', 'email' => 'o' . uniqid() . '@example.test', 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);
    }

    private function report(User $by, array $attrs = []): SavedReport
    {
        return SavedReport::create(array_merge([
            'name'       => 'Monthly pack',
            'created_by' => $by->id,
            'is_shared'  => false,
            'config'     => [
                'date_range' => 'month', 'location_filter' => 'all',
                'blocks' => [['id' => 'b1', 'metric_id' => 'text_block', 'title' => 'Note', 'viz' => 'text', 'width' => 'full', 'content' => 'x']],
            ],
        ], $attrs));
    }

    public function test_edit_route_loads_the_report_and_save_updates_it(): void
    {
        $owner  = $this->owner();
        $report = $this->report($owner);

        $this->actingAs($owner)
            ->get(route('owner.reports.custom.edit', $report))
            ->assertOk()
            ->assertSee('Monthly pack');

        $count = SavedReport::count();
        Livewire::actingAs($owner)
            ->test(ReportBuilder::class, ['reportId' => $report->id])
            ->set('reportName', 'Renamed pack')
            ->call('save')
            ->assertRedirect(route('owner.reports.custom.view', $report->id));

        $this->assertSame($count, SavedReport::count(), 'editing must not create a copy');
        $this->assertSame('Renamed pack', $report->fresh()->name);
    }

    public function test_old_builder_query_link_redirects_to_edit(): void
    {
        $owner  = $this->owner();
        $report = $this->report($owner);

        $this->actingAs($owner)
            ->get(route('owner.reports.custom.builder') . '?reportId=' . $report->id)
            ->assertRedirect(route('owner.reports.custom.edit', $report->id));
    }

    public function test_another_owners_private_report_is_off_limits(): void
    {
        $alice   = $this->owner();
        $bob     = $this->owner();
        $private = $this->report($alice);

        $this->actingAs($bob)->get(route('owner.reports.custom.view', $private))->assertForbidden();
        $this->actingAs($bob)->get(route('owner.reports.custom.edit', $private))->assertForbidden();
        $this->actingAs($bob)->get(route('owner.reports.custom.print', $private))->assertForbidden();

        Livewire::actingAs($bob)->test(ReportLibrary::class)
            ->call('duplicateReport', $private->id)
            ->assertForbidden();
    }

    public function test_shared_report_can_be_viewed_but_not_edited_by_others(): void
    {
        $alice  = $this->owner();
        $bob    = $this->owner();
        $shared = $this->report($alice, ['is_shared' => true]);

        $this->actingAs($bob)->get(route('owner.reports.custom.view', $shared))->assertOk();
        $this->actingAs($bob)->get(route('owner.reports.custom.edit', $shared))->assertForbidden();
    }

    public function test_report_ids_are_locked(): void
    {
        $alice = $this->owner();
        $bob   = $this->owner();
        $mine  = $this->report($bob);
        $hers  = $this->report($alice);

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::actingAs($bob)
            ->test(ReportBuilder::class, ['reportId' => $mine->id])
            ->set('editingReportId', $hers->id);
    }

    public function test_viewer_report_id_is_locked(): void
    {
        $bob  = $this->owner();
        $mine = $this->report($bob);

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::actingAs($bob)
            ->test(ReportViewer::class, ['reportId' => $mine->id])
            ->set('reportId', $mine->id + 1);
    }

    public function test_date_presets_use_the_business_day(): void
    {
        // 22:30 UTC on 30 Sep = 00:30 on 1 Oct in Kigali (UTC+2)
        Carbon::setTestNow(Carbon::parse('2026-09-30 22:30:00', 'UTC'));
        try {
            $runner = app(ReportRunner::class);
            $this->assertSame(['2026-10-01', '2026-10-01'], $runner->resolveDates(['date_range' => 'today']));
            $this->assertSame(['2026-10-01', '2026-10-01'], $runner->resolveDates(['date_range' => 'month']));
            $this->assertSame(['2026-01-01', '2026-10-01'], $runner->resolveDates(['date_range' => 'year']));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_prior_period_does_not_overlap(): void
    {
        $runner = app(ReportRunner::class);

        $this->assertSame(['2026-08-03', '2026-08-31'],
            $runner->resolvePriorDates(['comparison_mode' => 'prior_period'], '2026-09-01', '2026-09-29'));
        $this->assertSame(['2026-09-28', '2026-09-28'],
            $runner->resolvePriorDates(['comparison_mode' => 'prior_period'], '2026-09-29', '2026-09-29'));
        $this->assertSame(['2025-09-01', '2025-09-29'],
            $runner->resolvePriorDates(['comparison_mode' => 'prior_year'], '2026-09-01', '2026-09-29'));
    }

    public function test_duplicate_block_ids_are_made_unique(): void
    {
        $owner  = $this->owner();
        $report = $this->report($owner, ['config' => ['blocks' => [
            ['id' => 'b1_1', 'metric_id' => 'text_block', 'viz' => 'text'],
            ['id' => 'b1_1', 'metric_id' => 'text_block', 'viz' => 'text'],
            ['metric_id' => 'text_block', 'viz' => 'text'],
        ]]]);

        $ids = array_column($report->resolvedConfig()['blocks'], 'id');
        $this->assertSame(['b1_1', 'b1_1_2', 'b_2'], $ids);

        $results = app(ReportRunner::class)->run($report->resolvedConfig(), null, false);
        $this->assertCount(3, $results);
    }

    public function test_new_blocks_get_unique_ids_after_remove_and_add(): void
    {
        $owner = $this->owner();
        $c = Livewire::actingAs($owner)->test(ReportBuilder::class)
            ->call('addBlock', 'text_block')
            ->call('addBlock', 'text_block');
        $first = $c->get('canvas')[0]['id'];
        $c->call('removeBlock', $first)->call('addBlock', 'text_block');

        $ids = array_column($c->get('canvas'), 'id');
        $this->assertCount(2, array_unique($ids));
    }

    public function test_csv_neutralises_formulas_but_keeps_numbers(): void
    {
        $owner  = $this->owner();
        $report = $this->report($owner, ['name' => '=HYPERLINK("x")']);

        $result = \App\Services\Reports\MetricResult::make()
            ->headline(3, 'count', 'Products')
            ->table(['product_name' => ['Product', 'text'], 'revenue' => ['Revenue', 'money'], 'units' => ['Units', 'count']],
                [['product_name' => '@SUM(A1)', 'revenue' => '-1500.50', 'units' => 3]]);

        $csv = app(ExportReportAction::class)->toCsv($report, [
            'b1' => [
                'block'  => ['id' => 'b1', 'metric_id' => 'sales_top_products', 'viz' => 'table', 'title' => 'Top'],
                'meta'   => ['label' => 'Top', 'default_viz' => 'table'],
                'result' => $result->toArray(),
            ],
        ]);

        $this->assertStringContainsString('"\'=HYPERLINK(""x"")"', $csv);
        $this->assertStringContainsString('"\'@SUM(A1)","-1500.50","3"', $csv);
        $this->assertStringContainsString('"Product","Revenue (RWF)","Units"', $csv);
    }

    public function test_wipe_tools_name_real_report_tables(): void
    {
        $this->assertTrue(Schema::hasTable('report_run_history'));
        $this->assertTrue(Schema::hasTable('report_view_log'));

        foreach (['app/Console/Commands/WipeTransactionalData.php', 'app/Livewire/Owner/DangerZone.php', 'app/Livewire/Owner/SystemManager.php'] as $file) {
            $src = file_get_contents(base_path($file));
            $this->assertStringNotContainsString('report_run_histories', $src, $file);
            $this->assertStringNotContainsString('report_view_logs', $src, $file);
        }
    }
}
