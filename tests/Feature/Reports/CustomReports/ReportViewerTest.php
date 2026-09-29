<?php

namespace Tests\Feature\Reports\CustomReports;

use App\Livewire\Owner\Reports\ReportViewer;
use App\Models\ReportRunHistory;
use App\Models\SavedReport;
use App\Models\Shop;
use App\Models\User;
use App\Services\Reports\ReportPeriod;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The rebuilt report page (phase 3): filter bar, summary cards, typed
 * tables, details sheet, run history.
 */
class ReportViewerTest extends TestCase
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
    }

    private function report(array $config = []): SavedReport
    {
        return SavedReport::create([
            'name'       => 'Weekly pack',
            'created_by' => $this->owner->id,
            'config'     => array_merge([
                'date_range'      => 'month',
                'location_filter' => 'all',
                'comparison_mode' => 'none',
                'blocks'          => [
                    ['id' => 'k1', 'metric_id' => 'sales_revenue', 'title' => 'Revenue', 'viz' => 'kpi_card', 'width' => 'half'],
                    ['id' => 't1', 'metric_id' => 'sales_revenue', 'title' => 'Revenue side by side', 'viz' => 'table', 'width' => 'full'],
                    ['id' => 'c1', 'metric_id' => 'sales_revenue_trend', 'title' => 'Daily revenue', 'viz' => 'line_chart', 'width' => 'full'],
                    ['id' => 'x1', 'metric_id' => 'text_block', 'title' => 'Commentary', 'viz' => 'text', 'width' => 'full', 'content' => 'Owner notes here'],
                ],
            ], $config),
        ]);
    }

    private function viewer(SavedReport $report, array $query = [])
    {
        return Livewire::withQueryParams($query)->actingAs($this->owner)
            ->test(ReportViewer::class, ['reportId' => $report->id]);
    }

    public function test_page_paints_first_then_runs_the_report(): void
    {
        $report = $this->report();
        $c = $this->viewer($report)->assertSee('Weekly pack')->assertDontSee('Revenue side by side');
        $this->assertSame(0, ReportRunHistory::where('report_id', $report->id)->count());

        $c->call('load')
            ->assertSee('Revenue side by side')
            ->assertSee('Revenue (RWF)')          // typed money header
            ->assertSee('Previous period')
            ->assertSee('Owner notes here')
            ->assertSee('data-chart', false);

        $this->assertSame(1, ReportRunHistory::where('report_id', $report->id)->count());
    }

    public function test_results_are_cached_per_filter_set(): void
    {
        $report = $this->report();
        $c = $this->viewer($report)->call('load');
        $c->call('toggleHistory')->call('toggleHistory');   // re-renders without re-running
        $this->assertSame(1, ReportRunHistory::where('report_id', $report->id)->count());

        $c->call('setPreset', 'last_month');                 // new filters → new run
        $this->assertSame(2, ReportRunHistory::where('report_id', $report->id)->count());

        $c->call('refresh');                                 // forced
        $this->assertSame(3, ReportRunHistory::where('report_id', $report->id)->count());
    }

    public function test_a_run_does_not_invalidate_its_own_cache(): void
    {
        // markRun() used to bump updated_at, which was part of the cache key,
        // so every later request (a second or more on) re-ran the report
        $report  = $this->report();
        $updated = $report->updated_at->toDateTimeString();

        $this->travel(5)->seconds();   // the report was saved a while before it's viewed
        $c = $this->viewer($report)->call('load');
        $this->travel(5)->seconds();
        $c->call('toggleHistory')->call('toggleHistory')->call('openDetails', 'k1');

        $this->assertSame(1, ReportRunHistory::where('report_id', $report->id)->count());
        $this->assertSame($updated, $report->fresh()->updated_at->toDateTimeString());
        $this->assertSame(1, $report->fresh()->run_count);
    }

    public function test_history_keeps_headlines_not_full_results(): void
    {
        $report = $this->report();
        $this->viewer($report)->call('load');

        $run = ReportRunHistory::where('report_id', $report->id)->first();
        $this->assertNull($run->results);
        $this->assertSame(['k1', 't1', 'c1'], array_column($run->summary, 'block_id'));
        $this->assertArrayNotHasKey('blocks', $run->config_snapshot);
        $this->assertSame(1, $report->fresh()->run_count);
        $this->assertNull($report->fresh()->last_results);
    }

    public function test_filters_change_the_view_not_the_report(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-29 10:00:00', 'UTC'));
        try {
            $report = $this->report();
            $shop   = Shop::value('id');
            $c = $this->viewer($report)
                ->assertSet('preset', 'month')
                ->assertSet('dateFrom', '2026-09-01')
                ->call('setPreset', 'last_month')
                ->assertSet('dateFrom', '2026-08-01')
                ->assertSet('dateTo', '2026-08-31')
                ->set('dateFrom', '2026-08-10')
                ->assertSet('preset', 'custom')
                ->set('location', "shop:1' OR 1=1")
                ->assertSet('location', 'all');

            if ($shop) {
                $c->set('location', "shop:$shop")->assertSet('location', "shop:$shop");
            }

            $this->assertSame('month', $report->fresh()->config['date_range']);

            $c->call('resetFilters')
                ->assertSet('preset', 'month')
                ->assertSet('dateFrom', '2026-09-01')
                ->assertSet('location', 'all');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_url_filters_are_validated(): void
    {
        $report = $this->report();
        $this->viewer($report, ['period' => 'nonsense', 'loc' => 'drop table', 'compare' => 'x'])
            ->assertSet('preset', 'month')
            ->assertSet('location', 'all')
            ->assertSet('comparison', 'none');

        $this->viewer($report, ['period' => 'custom', 'from' => '2026-03-01', 'to' => '2026-03-15'])
            ->assertSet('preset', 'custom')
            ->assertSet('dateFrom', '2026-03-01')
            ->assertSet('dateTo', '2026-03-15');
    }

    public function test_details_sheet_shows_related_blocks(): void
    {
        $report = $this->report();
        $this->viewer($report)->call('load')
            ->call('openDetails', 'k1')
            ->assertSee('All figures')
            ->assertSee('Revenue by Shop')
            ->assertSee('Payment Method Breakdown')
            ->call('closeDetails')
            ->assertDontSee('All figures');
    }

    public function test_history_run_restores_its_filters(): void
    {
        $report = $this->report();
        $c = $this->viewer($report)->call('load')->call('setPreset', 'yesterday');
        [$from] = ReportPeriod::resolve('yesterday');

        $c->call('setPreset', 'month');
        $run = ReportRunHistory::where('report_id', $report->id)->orderByDesc('id')->get()
            ->first(fn ($r) => ($r->config_snapshot['date_from'] ?? null) === $from);

        $c->call('applyHistoryRun', $run->id)
            ->assertSet('dateFrom', $from)
            ->assertSet('showHistory', false);
    }

    public function test_comparison_badge_and_csv_export(): void
    {
        $report = $this->report(['comparison_mode' => 'prior_period']);
        $c = $this->viewer($report)->call('load')->assertSee('Compared with');

        // Export links carry the filters on screen
        $c->call('setPreset', 'last_month');
        [$from, $to] = ReportPeriod::resolve('last_month');
        foreach (['pdf', 'xlsx', 'csv'] as $format) {
            $c->assertSee(route('owner.reports.custom.export', [$report->id, $format]), false);
        }
        $c->assertSee("date_from={$from}&amp;date_to={$to}", false);
    }

    public function test_empty_report_explains_itself(): void
    {
        $report = $this->report(['blocks' => []]);
        $this->viewer($report)->call('load')->assertSee('This report has no blocks yet');
    }
}
