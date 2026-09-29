<?php

namespace Tests\Feature\Reports\CustomReports;

use App\Livewire\Owner\Reports\ReportLibrary;
use App\Models\SavedReport;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/** The rebuilt report library (phase 5) */
class ReportLibraryTest extends TestCase
{
    use DatabaseTransactions;

    private User $me;
    private User $other;

    protected function setUp(): void
    {
        parent::setUp();
        SavedReport::query()->forceDelete();   // inside the test transaction: start from an empty list
        $this->me    = $this->owner('Me');
        $this->other = $this->owner('Other Owner');
    }

    private function owner(string $name): User
    {
        return User::forceCreate([
            'name' => $name, 'email' => 'o' . uniqid() . '@example.test', 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);
    }

    private function report(User $by, string $name, array $attrs = []): SavedReport
    {
        return SavedReport::create(array_merge([
            'name' => $name, 'created_by' => $by->id, 'is_shared' => false,
            'config' => ['date_range' => 'last_month', 'blocks' => [['id' => 'b1', 'metric_id' => 'sales_revenue', 'viz' => 'kpi_card']]],
        ], $attrs));
    }

    private function library()
    {
        return Livewire::actingAs($this->me)->test(ReportLibrary::class);
    }

    public function test_lists_own_and_shared_reports_only(): void
    {
        $this->report($this->me, 'My private');
        $this->report($this->other, 'Their shared', ['is_shared' => true]);
        $this->report($this->other, 'Their private');

        $this->library()
            ->assertSee('My private')
            ->assertSee('Their shared')
            ->assertDontSee('Their private')
            ->assertSee('Last month')           // period shown as a label, not a key
            ->assertSee('Other Owner')
            ->call('setFilter', 'mine')
            ->assertSee('My private')->assertDontSee('Their shared')
            ->call('setFilter', 'shared')
            ->assertSee('Their shared')->assertDontSee('My private');
    }

    public function test_search_matches_name_and_description_literally(): void
    {
        $this->report($this->me, 'Cash audit', ['description' => 'For the bank']);
        $this->report($this->me, '100% sales');

        $this->library()
            ->set('search', 'bank')->assertSee('Cash audit')->assertDontSee('100% sales')
            ->set('search', '%')->assertSee('100% sales')->assertDontSee('Cash audit')
            ->set('search', 'nothing here')->assertSee('No reports match');
    }

    public function test_delete_asks_first_and_is_creator_only(): void
    {
        $mine   = $this->report($this->me, 'Mine');
        $shared = $this->report($this->other, 'Shared', ['is_shared' => true]);

        $this->library()
            ->call('askDelete', $mine->id)->assertSee('Its run history goes with it')
            ->call('cancelDelete')->assertDontSee('Its run history goes with it')
            ->call('askDelete', $mine->id)->call('deleteReport', $mine->id)
            ->assertDispatched('notification');
        $this->assertSoftDeleted($mine);

        $this->library()->call('deleteReport', $shared->id)->assertForbidden();
        $this->assertNotSoftDeleted($shared);
    }

    public function test_copy_is_private_and_mine(): void
    {
        $shared = $this->report($this->other, 'Team pack', ['is_shared' => true]);
        $hidden = $this->report($this->other, 'Hidden');

        $this->library()->call('duplicateReport', $shared->id);
        $copy = SavedReport::where('name', 'Team pack (copy)')->firstOrFail();
        $this->assertSame($this->me->id, $copy->created_by);
        $this->assertFalse($copy->is_shared);
        $this->assertEquals($shared->resolvedConfig()['blocks'], $copy->config['blocks']);  // jsonb reorders keys

        $this->library()->call('duplicateReport', $hidden->id)->assertForbidden();
    }

    public function test_share_toggle_is_creator_only(): void
    {
        $mine   = $this->report($this->me, 'Mine');
        $theirs = $this->report($this->other, 'Theirs', ['is_shared' => true]);

        $this->library()->call('toggleShare', $mine->id);
        $this->assertTrue($mine->fresh()->is_shared);

        $this->library()->call('toggleShare', $theirs->id)->assertForbidden();
        $this->assertTrue($theirs->fresh()->is_shared);
    }

    public function test_counts_and_empty_states(): void
    {
        $this->library()->assertSee('No reports yet')
            ->call('setFilter', 'shared')->assertSee('Nothing shared with you yet');

        $this->report($this->me, 'A');
        $this->report($this->other, 'B', ['is_shared' => true]);
        $c = $this->library();
        $counts = $c->viewData('counts');
        $this->assertSame([2, 1, 1], [(int) $counts->all_count, (int) $counts->mine_count, (int) $counts->shared_count]);
    }

    public function test_recently_run_puts_never_run_last(): void
    {
        $this->report($this->me, 'Never run');
        $this->report($this->me, 'Ran yesterday', ['last_run_at' => now()->subDay()]);
        $this->report($this->me, 'Ran today', ['last_run_at' => now()]);

        $names = $this->library()->viewData('reports')->pluck('name')->all();
        $this->assertSame(['Ran today', 'Ran yesterday', 'Never run'], $names);
    }
}
