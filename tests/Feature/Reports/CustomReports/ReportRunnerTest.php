<?php

namespace Tests\Feature\Reports\CustomReports;

use App\Services\Reports\MetricRegistry;
use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;
use App\Services\Reports\ReportPeriod;
use App\Services\Reports\ReportRunner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportRunnerTest extends TestCase
{
    use DatabaseTransactions;

    /** A metric whose headline is a fixed value per start date, to test the runner alone */
    private function fakeMetric(array $valuesByFrom, string $good = 'up', string $locations = 'any'): Metric
    {
        return new class($valuesByFrom, $good, $locations) extends Metric {
            protected string $id = 'fake';
            protected string $label = 'Fake';
            protected string $description = 'Fake';
            protected string $domain = 'sales';

            public function __construct(private array $values, string $good, string $locations)
            {
                $this->good = $good;
                $this->locations = $locations;
            }

            public function fetch(ReportContext $ctx): array
            {
                return [
                    'v'    => $this->values[$ctx->from] ?? 0,
                    'loc'  => $ctx->location,
                    'rows' => [['n' => 'b', 'x' => 2], ['n' => 'a', 'x' => 3], ['n' => 'c', 'x' => 1]],
                ];
            }

            public function present(array $raw, ReportContext $ctx): MetricResult
            {
                return MetricResult::make()
                    ->headline($raw['v'], 'money', 'Value')
                    ->stat('Location', $raw['loc'], 'text')
                    ->table(['n' => ['Name', 'text'], 'x' => ['X', 'count']], $raw['rows'], ['x'])
                    ->chart('n', ['x']);
            }
        };
    }

    private function runner(): ReportRunner
    {
        return app(ReportRunner::class);
    }

    public function test_comparison_uses_the_previous_period_and_direction(): void
    {
        $up = $this->fakeMetric(['2026-09-01' => 150, '2026-08-03' => 100]);
        [, $r] = $this->runner()->runBlock($up, ['id' => 'b'], '2026-09-01', '2026-09-29', 'all', 'prior_period');

        $this->assertSame(100, $r->comparison['value']);
        $this->assertSame(50.0, $r->comparison['pct']);
        $this->assertTrue($r->comparison['good']);
        $this->assertSame(['2026-08-03', '2026-08-31'], [$r->comparison['from'], $r->comparison['to']]);

        // Same rise on a figure that should go down is bad news
        $down = $this->fakeMetric(['2026-09-01' => 150, '2026-08-03' => 100], 'down');
        [, $r] = $this->runner()->runBlock($down, ['id' => 'b'], '2026-09-01', '2026-09-29', 'all', 'prior_period');
        $this->assertFalse($r->comparison['good']);
    }

    public function test_snapshot_metrics_are_not_compared(): void
    {
        $metric = app(MetricRegistry::class)->metric('inventory_cost_value');
        [, $r] = $this->runner()->runBlock($metric, ['id' => 'b'], '2026-09-01', '2026-09-29', 'all', 'prior_period');

        $this->assertNull($r->comparison);
        $this->assertNotEmpty($r->notes);
    }

    public function test_thresholds_respect_which_way_is_good(): void
    {
        $runner = $this->runner();
        $block  = ['threshold_warning' => 200, 'threshold_critical' => 100];

        // Revenue-like: falling to a threshold is the problem
        $up = $this->fakeMetric([]);
        $this->assertSame('ok',   $runner->thresholdStatus($up, 500, $block));
        $this->assertSame('warn', $runner->thresholdStatus($up, 150, $block));
        $this->assertSame('crit', $runner->thresholdStatus($up, 80, $block));

        // Loss-like: rising to a threshold is the problem
        $down  = $this->fakeMetric([], 'down');
        $block = ['threshold_warning' => 100, 'threshold_critical' => 200];
        $this->assertSame('ok',   $runner->thresholdStatus($down, 50, $block));
        $this->assertSame('warn', $runner->thresholdStatus($down, 150, $block));
        $this->assertSame('crit', $runner->thresholdStatus($down, 250, $block));

        $this->assertNull($runner->thresholdStatus($down, 250, []));
    }

    public function test_location_fallbacks_are_explained(): void
    {
        $shopOnly = $this->fakeMetric([], 'up', 'shop');
        [, $r] = $this->runner()->runBlock($shopOnly, ['id' => 'b'], '2026-09-01', '2026-09-29', 'warehouse:1');
        $this->assertSame('all', $r->stats[0]['value']);
        $this->assertStringContainsString('all shops', $r->notes[0]);

        [, $r] = $this->runner()->runBlock($shopOnly, ['id' => 'b'], '2026-09-01', '2026-09-29', 'shop:7');
        $this->assertSame('shop:7', $r->stats[0]['value']);
        $this->assertSame([], $r->notes);

        $companyWide = $this->fakeMetric([], 'up', 'none');
        [, $r] = $this->runner()->runBlock($companyWide, ['id' => 'b'], '2026-09-01', '2026-09-29', 'shop:7');
        $this->assertSame('all', $r->stats[0]['value']);
        $this->assertStringContainsString('every location', $r->notes[0]);
    }

    public function test_block_overrides_win_over_report_filters(): void
    {
        $metric = $this->fakeMetric(['2026-01-05' => 42]);
        [, $r] = $this->runner()->runBlock($metric, [
            'id' => 'b',
            'date_range_override' => 'custom', 'date_from_override' => '2026-01-05', 'date_to_override' => '2026-01-09',
            'location_filter_override' => 'shop:3',
        ], '2026-09-01', '2026-09-29', 'all');

        $this->assertSame(42, $r->value());
        $this->assertSame('shop:3', $r->stats[0]['value']);
    }

    public function test_sort_and_limit_keep_totals_and_chart_in_step(): void
    {
        [, $r] = $this->runner()->runBlock($this->fakeMetric([]), [
            'id' => 'b', 'block_options' => ['sort_by' => 'n', 'sort_direction' => 'asc', 'limit' => '2'],
        ], '2026-09-01', '2026-09-29', 'all');

        $this->assertSame(['a', 'b'], array_column($r->rows, 'n'));
        $this->assertEquals(5, $r->totals['x']);
        $this->assertSame(['a', 'b'], $r->series['labels']);
        $this->assertEquals([3, 2], $r->series['datasets'][0]['data']);
    }

    public function test_unknown_metric_and_failures_show_as_error_blocks(): void
    {
        $results = $this->runner()->run(['date_range' => 'month', 'blocks' => [
            ['id' => 'gone', 'metric_id' => 'no_such_metric', 'title' => 'Old block'],
        ]], null, false);
        $this->assertNotNull($results['gone']['result']['error']);

        $broken = new class extends Metric {
            protected string $id = 'broken';
            protected string $label = 'Broken';
            protected string $description = 'Broken';
            protected string $domain = 'sales';
            public function fetch(ReportContext $ctx): array { throw new \RuntimeException('SQLSTATE secret detail'); }
            public function present(array $raw, ReportContext $ctx): MetricResult { return MetricResult::make(); }
        };
        [, $r] = $this->runner()->runBlock($broken, ['id' => 'b'], '2026-09-01', '2026-09-29', 'all');
        $this->assertNotNull($r->error);
        $this->assertStringNotContainsString('SQLSTATE', $r->error, 'raw exception text must not reach the page');
    }

    public function test_viewer_filters_override_saved_defaults(): void
    {
        $config = $this->runner()->effectiveConfig(
            ['date_range' => 'month', 'location_filter' => 'all', 'comparison_mode' => 'none', 'blocks' => []],
            ['date_range' => 'custom', 'date_from' => '2026-02-01', 'date_to' => '2026-02-10', 'location_filter' => 'shop:4', 'blocks' => ['ignored']],
        );

        $this->assertSame(['2026-02-01', '2026-02-10'], $this->runner()->resolveDates($config));
        $this->assertSame('shop:4', $config['location_filter']);
        $this->assertSame([], $config['blocks']);

        $bad = $this->runner()->effectiveConfig(['blocks' => []], ['location_filter' => "shop:1' OR 1=1"]);
        $this->assertSame('all', $bad['location_filter']);
    }

    public function test_period_presets_in_business_time(): void
    {
        // 00:30 on Thu 1 Oct 2026 in Kigali
        Carbon::setTestNow(Carbon::parse('2026-09-30 22:30:00', 'UTC'));
        try {
            $this->assertSame(['2026-09-30', '2026-09-30'], ReportPeriod::resolve('yesterday'));
            $this->assertSame(['2026-09-01', '2026-09-30'], ReportPeriod::resolve('last_month'));
            $this->assertSame(['2026-09-21', '2026-09-27'], ReportPeriod::resolve('last_week'));
            $this->assertSame(['2026-09-02', '2026-10-01'], ReportPeriod::resolve('last_30'));
            $this->assertSame(['2026-10-01', '2026-10-01'], ReportPeriod::resolve('quarter'));
            $this->assertSame(['2026-02-01', '2026-02-10'], ReportPeriod::resolve('custom', '2026-02-10', '2026-02-01'));
        } finally {
            Carbon::setTestNow();
        }

        $this->assertSame('1 – 29 Sep 2026', ReportPeriod::label('2026-09-01', '2026-09-29'));
        $this->assertSame('28 Aug – 3 Sep 2026', ReportPeriod::label('2026-08-28', '2026-09-03'));
    }
}
