<?php

namespace Tests\Feature\Reports\CustomReports;

use App\Livewire\Owner\Reports\ReportBuilder;
use App\Models\SavedReport;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The rebuilt report builder (phase 4): templates, canvas editing, the
 * block drawer, live previews, and server-side sanitising of everything
 * the browser sends.
 */
class ReportBuilderTest extends TestCase
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

    private function builder(array $params = [])
    {
        return Livewire::actingAs($this->owner)->test(ReportBuilder::class, $params);
    }

    public function test_new_report_starts_with_templates(): void
    {
        $c = $this->builder()->assertSee('Start from a template')->assertSee('Monthly Operations Overview');

        $c->call('loadTemplate', 'monthly_ops')
            ->assertSet('reportName', 'Monthly Operations Overview')
            ->assertDontSee('Start from a template');
        $this->assertGreaterThan(3, count($c->get('canvas')));
    }

    public function test_drawer_edits_are_written_back_and_checked(): void
    {
        $c = $this->builder()->call('addBlock', 'sales_top_products')->call('loadPreviews');
        $id = $c->get('canvas')[0]['id'];

        $c->call('selectBlock', $id)
            ->assertSee('Sort by')
            ->set('edit.title', 'Best sellers')
            ->set('edit.viz', 'bar_chart')
            ->set('edit.width', 'half')
            ->set('edit.limit', '10')
            ->set('edit.location', 'shop:5');

        $b = $c->get('canvas')[0];
        $this->assertSame('Best sellers', $b['title']);
        $this->assertSame('bar_chart', $b['viz']);
        $this->assertSame(10, $b['block_options']['limit']);
        $this->assertSame('shop:5', $b['location_filter_override']);

        // A display this metric doesn't offer is refused
        $c->set('edit.viz', 'kpi_card');
        $this->assertSame('bar_chart', $c->get('canvas')[0]['viz']);
    }

    public function test_settings_that_cannot_apply_are_dropped(): void
    {
        $c = $this->builder()
            ->call('addBlock', 'inventory_cost_value')     // stock snapshot: no period
            ->call('addBlock', 'sales_revenue')            // shop-only: no warehouse
            ->call('addBlock', 'inventory_by_location');   // company-wide: no location
        [$snap, $sales, $company] = array_column($c->get('canvas'), 'id');

        $c->call('selectBlock', $snap)->set('edit.date_range', 'last_month')->set('edit.threshold_warning', '5');
        $c->call('selectBlock', $sales)->set('edit.location', 'warehouse:1');
        $c->call('selectBlock', $company)->set('edit.location', 'shop:2')->set('edit.threshold_warning', '5');

        [$a, $b, $d] = $c->get('canvas');
        $this->assertArrayNotHasKey('date_range_override', $a);
        $this->assertEquals(5, $a['threshold_warning']);                // summary card keeps thresholds
        $this->assertArrayNotHasKey('location_filter_override', $b);
        $this->assertArrayNotHasKey('location_filter_override', $d);
        $this->assertArrayNotHasKey('threshold_warning', $d);           // tables don't
    }

    public function test_tampered_canvas_is_cleaned_on_save(): void
    {
        $this->builder()
            ->set('reportName', 'Tampered')
            ->set('canvas', [
                ['id' => 'x"><script>', 'metric_id' => 'sales_revenue', 'viz' => 'pie', 'width' => 'huge', 'title' => str_repeat('A', 300), 'evil' => 1],
                ['id' => 'b2', 'metric_id' => 'drop_table', 'viz' => 'table'],
                ['id' => 'b3', 'metric_id' => 'sales_top_products', 'viz' => 'table',
                 'location_filter_override' => "shop:1' OR 1=1", 'block_options' => ['sort_by' => 'revenue; DROP', 'limit' => 9999]],
                ['id' => 'b4', 'metric_id' => 'text_block', 'content' => str_repeat('x', 9000)],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $blocks = SavedReport::where('name', 'Tampered')->firstOrFail()->config['blocks'];
        $this->assertCount(3, $blocks, 'unknown metric dropped');

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_]+$/', $blocks[0]['id']);
        $this->assertSame('kpi_card', $blocks[0]['viz']);
        $this->assertSame('half', $blocks[0]['width']);
        $this->assertSame(120, mb_strlen($blocks[0]['title']));
        $this->assertArrayNotHasKey('evil', $blocks[0]);

        $this->assertArrayNotHasKey('location_filter_override', $blocks[1]);
        $this->assertSame(['limit' => 100], $blocks[1]['block_options']);

        $this->assertSame(5000, mb_strlen($blocks[2]['content']));
    }

    public function test_reorder_move_duplicate_remove(): void
    {
        $c = $this->builder()->call('addBlock', 'sales_revenue')->call('addBlock', 'loss_total')->call('addBlock', 'sales_voided');
        [$a, $b, $d] = array_column($c->get('canvas'), 'id');

        $c->call('reorderBlocks', [$d, 'nope', $a]);
        $this->assertSame([$d, $a, $b], array_column($c->get('canvas'), 'id'));

        $c->call('moveBlock', $d, 1);
        $this->assertSame([$a, $d, $b], array_column($c->get('canvas'), 'id'));

        $c->call('duplicateBlock', $a);
        $this->assertCount(4, $c->get('canvas'));
        $this->assertSame('Total Revenue (copy)', $c->get('canvas')[1]['title']);

        $c->call('selectBlock', $a)->call('removeBlock', $a)->assertSet('selectedBlockId', '');
        $this->assertCount(3, $c->get('canvas'));
    }

    public function test_block_limit(): void
    {
        $c = $this->builder();
        for ($i = 0; $i < ReportBuilder::MAX_BLOCKS + 3; $i++) {
            $c->call('addBlock', 'sales_revenue');
        }
        $this->assertCount(ReportBuilder::MAX_BLOCKS, $c->get('canvas'));
    }

    public function test_save_validation(): void
    {
        $this->builder()->call('save')->assertHasErrors(['reportName', 'canvas']);

        $this->builder()->set('reportName', 'Dates')->call('addBlock', 'sales_revenue')
            ->set('dateRange', 'custom')->call('save')
            ->assertHasErrors(['dateFrom', 'dateTo']);

        $this->builder()->set('reportName', 'Mail')->call('addBlock', 'sales_revenue')
            ->set('scheduleRecipients', 'ok@example.com, not-an-email')->call('save')
            ->assertHasErrors('scheduleRecipients');

        $this->builder()->set('reportName', 'No one')->call('addBlock', 'sales_revenue')
            ->set('scheduleFrequency', 'weekly')->call('save')
            ->assertHasErrors('scheduleRecipients');

        $this->builder()->set('reportName', 'Bad time')->call('addBlock', 'sales_revenue')
            ->set('scheduleFrequency', 'daily')->set('scheduleRecipients', 'a@example.com')
            ->set('scheduleTime', '25:00')->call('save')
            ->assertHasErrors('scheduleTime');
    }

    public function test_save_creates_and_edit_updates(): void
    {
        $c = $this->builder()
            ->set('reportName', '  Shop review ')
            ->set('dateRange', 'last_month')
            ->set('comparisonMode', 'prior_period')
            ->set('isShared', true)
            ->call('addBlock', 'sales_revenue')
            ->call('save')
            ->assertHasNoErrors();

        $report = SavedReport::where('name', 'Shop review')->firstOrFail();
        $c->assertRedirect(route('owner.reports.custom.view', $report->id));
        $this->assertSame('last_month', $report->config['date_range']);
        $this->assertSame('prior_period', $report->config['comparison_mode']);
        $this->assertTrue($report->is_shared);

        $this->builder(['reportId' => $report->id])
            ->assertSet('reportName', 'Shop review')
            ->assertSee('Edit report')
            ->call('addBlock', 'loss_total')
            ->call('save');
        $this->assertCount(2, $report->fresh()->config['blocks']);
        $this->assertSame(1, SavedReport::where('name', 'Shop review')->count());
    }

    public function test_previews_run_per_block_after_load(): void
    {
        $c = $this->builder()->call('addBlock', 'sales_revenue')->call('addBlock', 'text_block')->call('addBlock', 'sales_top_products');
        $this->assertSame([], $c->instance()->previews);

        $c->call('loadPreviews');
        $previews = $c->instance()->previews;
        [$rev, , $top] = array_column($c->get('canvas'), 'id');

        $this->assertStringContainsString('RWF', $previews[$rev]['headline']);
        $this->assertContains('revenue', array_column($previews[$top]['columns'], 'key'));
        $this->assertCount(2, $previews, 'text blocks have no preview');
    }
}
