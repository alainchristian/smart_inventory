<?php

namespace Tests\Feature\Reports\CustomReports;

use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Reports\MetricRegistry;
use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;
use App\Services\Reports\ReportPeriod;
use App\Services\Reports\ReportRunner;
use App\Services\Reports\ReportTemplates;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Every custom-report metric honours the MetricResult contract, and
 * nothing a saved report or template refers to has gone missing.
 */
class MetricContractTest extends TestCase
{
    use DatabaseTransactions;

    /** Saved reports store these ids: they must never disappear */
    private const STORED_IDS = [
        'sales_revenue', 'sales_gross_profit', 'sales_transaction_count', 'sales_avg_basket', 'sales_by_shop',
        'sales_top_products', 'sales_payment_methods', 'sales_revenue_trend', 'sales_voided',
        'inventory_cost_value', 'inventory_retail_value', 'inventory_fill_rate', 'inventory_aging',
        'inventory_dead_stock', 'inventory_abc_summary', 'inventory_top_by_value',
        'inventory_category_concentration', 'inventory_by_location',
        'replenishment_critical', 'replenishment_days_on_hand',
        'loss_total', 'loss_return_rate', 'loss_damaged_value', 'loss_shrinkage', 'loss_by_product',
        'transfers_kpis', 'transfers_discrepancies', 'transfers_routes',
        'ops_low_stock_count', 'ops_damaged_pending', 'ops_stock_turnover',
        'finance_expense_summary', 'finance_expense_trend', 'finance_withdrawal_summary',
        'finance_cash_variance', 'finance_net_operating', 'text_block',
    ];

    private const TYPES = ['money', 'count', 'percent', 'ratio', 'days', 'date', 'datetime', 'text', 'bool'];

    private function registry(): MetricRegistry
    {
        return app(MetricRegistry::class);
    }

    public function test_every_stored_id_still_resolves(): void
    {
        $ids = array_column($this->registry()->catalogue(), 'id');

        $this->assertSame(count($ids), count(array_unique($ids)), 'metric ids must be unique');
        foreach (self::STORED_IDS as $id) {
            $this->assertNotNull($this->registry()->find($id), "$id is referenced by saved reports");
        }
    }

    public function test_metric_meta_is_well_formed(): void
    {
        foreach ($this->registry()->catalogue() as $m) {
            $this->assertContains($m['default_viz'], $m['viz_options'], $m['id']);
            $this->assertContains($m['locations'], ['none', 'shop', 'any'], $m['id']);
            $this->assertContains($m['good'], ['up', 'down', 'neutral'], $m['id']);
            $this->assertNotSame('', $m['label']);
            $this->assertNotSame('', $m['description']);
        }
    }

    public function test_templates_only_use_existing_metrics_and_viz(): void
    {
        $templates = app(ReportTemplates::class);
        foreach ($templates->list() as $t) {
            foreach ($templates->get($t['key'])['blocks'] as $block) {
                $meta = $this->registry()->find($block['metric_id']);
                $this->assertNotNull($meta, "{$t['key']}: {$block['metric_id']}");
                $this->assertContains($block['viz'] ?? $meta['default_viz'], $meta['viz_options'], "{$t['key']}: {$block['metric_id']}");
            }
        }
    }

    public function test_every_metric_returns_a_valid_result_for_each_location_kind(): void
    {
        [$from, $to] = ReportPeriod::resolve('month');
        $locations = ['all'];
        if ($shop = Shop::value('id'))      $locations[] = "shop:$shop";
        if ($wh = Warehouse::value('id'))   $locations[] = "warehouse:$wh";

        foreach ($this->registry()->metrics() as $metric) {
            foreach ($locations as $loc) {
                [, $r] = app(ReportRunner::class)->runBlock($metric, ['id' => 'b', 'metric_id' => $metric->id()], $from, $to, $loc);
                $this->assertValidResult($metric, $r, $loc);
            }
        }
    }

    private function assertValidResult(Metric $metric, MetricResult $r, string $loc): void
    {
        $at = $metric->id() . " @ $loc";

        $this->assertNull($r->error, "$at failed: {$r->error}");
        $this->assertNotNull($r->headline, "$at has no headline");
        $this->assertContains($r->headline['type'], self::TYPES, $at);
        $this->assertTrue(is_numeric($r->headline['value']), "$at headline must be a number");

        foreach ($r->stats as $s) {
            $this->assertContains($s['type'], self::TYPES, "$at stat {$s['label']}");
        }

        $keys = array_column($r->columns, 'key');
        foreach ($r->columns as $c) {
            $this->assertContains($c['type'], self::TYPES, "$at column {$c['key']}");
        }
        foreach ($r->rows as $row) {
            $this->assertSame($keys, array_keys($row), "$at rows must have exactly the column keys");
        }
        if ($r->totals !== null) {
            $this->assertSame($keys, array_keys($r->totals), $at);
        }
        if ($r->series !== null) {
            $this->assertCount(count($r->rows), $r->series['labels'], $at);
            foreach ($r->series['datasets'] as $d) {
                $this->assertCount(count($r->rows), $d['data'], $at);
            }
        }
        if ($r->insight !== null) {
            $this->assertContains($r->insight['tone'], ['good', 'warn', 'bad', 'neutral'], $at);
        }

        // A warehouse filter on a shop-only metric falls back to all shops, and says so
        if (str_starts_with($loc, 'warehouse:') && $metric->locations() === 'shop') {
            $this->assertTrue(collect($r->notes)->contains(fn ($n) => str_contains($n, 'all shops')), "$at should explain the fallback");
        }
        if ($loc !== 'all' && $metric->locations() === 'none') {
            $this->assertTrue(collect($r->notes)->contains(fn ($n) => str_contains($n, 'every location')), "$at should explain it ignores location");
        }
    }

    public function test_current_viewer_renders_a_report_with_every_metric(): void
    {
        $owner = \App\Models\User::forceCreate([
            'name' => 'Owner', 'email' => 'o' . uniqid() . '@example.test', 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);
        $blocks = [];
        foreach ($this->registry()->catalogue() as $i => $m) {
            foreach ($m['viz_options'] as $viz) {
                $blocks[] = ['id' => "b{$i}_{$viz}", 'metric_id' => $m['id'], 'viz' => $viz, 'title' => $m['label'], 'width' => 'half', 'content' => 'Note'];
            }
        }
        $report = \App\Models\SavedReport::create([
            'name' => 'Everything', 'created_by' => $owner->id,
            'config' => ['date_range' => 'month', 'comparison_mode' => 'prior_period', 'blocks' => $blocks],
        ]);

        \Livewire\Livewire::actingAs($owner)
            ->test(\App\Livewire\Owner\Reports\ReportViewer::class, ['reportId' => $report->id])
            ->call('run')
            ->assertOk()
            ->assertSee('Stock Value by Location');
    }

    public function test_grouped_service_shapes_become_tables(): void
    {
        // Both used to render empty: the services return grouped objects, not row lists
        [$from, $to] = ReportPeriod::resolve('month');
        $ctx = new ReportContext($from, $to);

        [, $abc] = $this->registry()->metric('inventory_abc_summary')->evaluate($ctx);
        $this->assertSame(['A: top sellers', 'B: steady sellers', 'C: slow sellers', 'Not selling'], array_column($abc->rows, 'class'));

        [$raw, $loc] = $this->registry()->metric('inventory_by_location')->evaluate($ctx);
        $this->assertCount(count($raw['warehouses'] ?? []) + count($raw['shops'] ?? []), $loc->rows);

        [$raw, $exp] = $this->registry()->metric('finance_expense_summary')->evaluate($ctx);
        $this->assertCount(count($raw['by_category'] ?? []), $exp->rows);
    }
}
