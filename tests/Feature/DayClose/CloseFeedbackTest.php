<?php

namespace Tests\Feature\DayClose;

use App\Livewire\Shop\Dashboard;
use App\Livewire\Shop\DayClose\AddExpense;
use App\Livewire\Shop\DayClose\CloseWizard;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Feedback round on the close flow (2026-09-28): expense category as default
 * description, non-cash settlement differences must be explained, and the
 * shop dashboard counts expenses/withdrawals by their session's business day.
 */
class CloseFeedbackTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $u   = uniqid();
        $now = now()->toDateTimeString();

        $this->shopId = DB::table('shops')->insertGetId([
            'name' => "Feedback Test $u", 'code' => 'F' . substr($u, -8), 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->manager = User::forceCreate([
            'name' => 'Feedback Tester', 'email' => "fb$u@example.test", 'password' => bcrypt('x'),
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopId,
        ]);
    }

    private function category(string $name): int
    {
        return DB::table('expense_categories')->insertGetId([
            'name' => $name . ' ' . uniqid(), 'applies_to' => 'both', 'is_active' => true, 'sort_order' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function makeSession(string $date, string $status = 'open'): int
    {
        return DB::table('daily_sessions')->insertGetId([
            'shop_id' => $this->shopId, 'session_date' => $date, 'status' => $status,
            'opened_by' => $this->manager->id, 'opened_at' => now(), 'opening_balance' => 1000000,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_category_becomes_default_description_and_keeps_typed_detail(): void
    {
        $transport = $this->category('Staff Transport');
        $meals     = $this->category('Staff Meals');
        $tName     = DB::table('expense_categories')->where('id', $transport)->value('name');
        $mName     = DB::table('expense_categories')->where('id', $meals)->value('name');

        $c = Livewire::actingAs($this->manager)
            ->test(AddExpense::class, ['dailySessionId' => $this->makeSession(business_today()->toDateString())])
            ->set('categoryId', $transport)
            ->assertSet('description', $tName);

        // User adds detail, then switches category — only the prefix changes
        $c->set('description', $tName . ' - to Kicukiro')
            ->set('categoryId', $meals)
            ->assertSet('description', $mName . ' - to Kicukiro');

        // A fully custom description is left alone
        $c->set('description', 'Lunch for 3 staff')
            ->set('categoryId', $transport)
            ->assertSet('description', 'Lunch for 3 staff');
    }

    public function test_settlement_difference_requires_a_note(): void
    {
        $sessionId = $this->makeSession(business_today()->toDateString());

        $w = Livewire::actingAs($this->manager)
            ->test(CloseWizard::class, ['dailySessionId' => $sessionId])
            ->set('currentStep', 4)
            ->set('actualCashCounted', '1000000')
            ->set('momoSettled', 5000); // nothing collected by MoMo

        $w->call('openConfirm')->assertHasErrors('notes')->assertSet('showConfirm', false);

        $w->set('notes', 'Owner sent 5,000 by mistake')
            ->call('openConfirm')->assertHasNoErrors()->assertSet('showConfirm', true);
    }

    public function test_dashboard_counts_expenses_on_their_session_day(): void
    {
        $yesterday = business_today()->subDay()->toDateString();
        $sessionId = $this->makeSession($yesterday);
        $cat       = $this->category('Meals');

        // Recorded right now (today), but in yesterday's still-open register
        DB::table('expenses')->insert([
            'daily_session_id' => $sessionId, 'expense_category_id' => $cat, 'amount' => 700000,
            'description' => 'Staff meals', 'payment_method' => 'cash',
            'recorded_by' => $this->manager->id, 'recorded_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('owner_withdrawals')->insert([
            'daily_session_id' => $sessionId, 'shop_id' => $this->shopId, 'amount' => 30000,
            'method' => 'cash', 'reason' => 'Test', 'recorded_by' => $this->manager->id,
            'recorded_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $c = Livewire::actingAs($this->manager)->test(Dashboard::class);

        $c->call('setPreset', 'today')
            ->assertViewHas('cfExpenses', 0.0)
            ->assertViewHas('cfWithdrawals', 0.0);

        $c->call('setPreset', 'yesterday')
            ->assertViewHas('cfExpenses', 700000.0)
            ->assertViewHas('cfWithdrawals', 30000.0);
    }
}
