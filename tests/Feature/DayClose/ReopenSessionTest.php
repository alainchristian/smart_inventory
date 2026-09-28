<?php

namespace Tests\Feature\DayClose;

use App\Livewire\Shop\DayClose\ReopenSession;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\DailySession;
use App\Models\Expense;
use App\Models\User;
use App\Services\DayClose\DailySessionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Shop managers may reopen their own shop's most recent closed day, with a
 * reason; the owner is notified and the old shortage expense is removed.
 */
class ReopenSessionTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private User $manager;
    private DailySessionService $svc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->svc = app(DailySessionService::class);
        $u = uniqid();

        $this->shopId = DB::table('shops')->insertGetId([
            'name' => "Reopen Test $u", 'code' => 'O' . substr($u, -8), 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->manager = $this->user('shop_manager', $this->shopId);
    }

    private function user(string $role, ?int $shopId = null): User
    {
        $u = uniqid();

        return User::forceCreate([
            'name' => "Reopen $role", 'email' => "ro$u@example.test", 'password' => bcrypt('x'),
            'role' => $role, 'location_type' => $shopId ? 'shop' : null, 'location_id' => $shopId,
        ]);
    }

    /** Opens a session with 50,000 and closes it counting $counted (short → shortage expense). */
    private function closedDay(string $date, int $counted = 45000): DailySession
    {
        $session = $this->svc->openSession($this->manager, $this->shopId, 50000, $date);

        return $this->svc->closeSession($session, ['actual_cash_counted' => $counted], $this->manager);
    }

    public function test_manager_reopens_own_latest_day_with_reason(): void
    {
        $session = $this->closedDay(business_today()->toDateString());
        $this->assertSame(-5000, (int) $session->cash_variance);
        $this->assertSame(1, $session->expenses()->where('is_system_generated', true)->count());

        $reopened = $this->svc->reopenSession($session, $this->manager, 'Miscounted the 5,000 notes');

        $this->assertTrue($reopened->isOpen());
        $this->assertNull($reopened->closed_at);
        // Shortage expense removed, so the drawer is expected to hold the full 50,000 again
        $this->assertSame(0, $reopened->expenses()->where('is_system_generated', true)->count());
        $this->assertSame(1, Expense::onlyTrashed()->where('daily_session_id', $session->id)->count());
        $this->assertSame(50000, $this->svc->computeLiveSummary($reopened)['expected_cash']);

        $log = ActivityLog::where('action', 'daily_session_reopened')->where('entity_id', $session->id)->latest('id')->first();
        $this->assertSame('Miscounted the 5,000 notes', $log->details['reason']);
        $this->assertSame(-5000, (int) $log->details['previous_close']['cash_variance']);
        $this->assertSame(5000, $log->details['shortage_removed']);

        // Old shortage alert resolved, owner told about the reopen
        $this->assertSame(0, Alert::where('entity_type', 'daily_session')->where('entity_id', $session->id)
            ->where('title', 'like', 'Cash Shortage%')->where('is_resolved', false)->count());
        $this->assertSame(1, Alert::where('entity_id', $session->id)->where('title', 'like', 'Register reopened%')->count());

        // Re-close with the correct count: balanced, no new shortage
        $closed = $this->svc->closeSession($reopened, ['actual_cash_counted' => 50000], $this->manager);
        $this->assertSame(0, (int) $closed->cash_variance);
        $this->assertSame(0, $closed->expenses()->where('is_system_generated', true)->count());
    }

    public function test_manager_must_give_a_reason(): void
    {
        $session = $this->closedDay(business_today()->toDateString());

        $this->expectExceptionMessage('Give a reason');
        $this->svc->reopenSession($session, $this->manager, '  ');
    }

    public function test_manager_cannot_reopen_once_a_later_day_exists_but_owner_can(): void
    {
        $old = $this->closedDay(business_today()->subDay()->toDateString());
        $this->closedDay(business_today()->toDateString());

        try {
            $this->svc->reopenSession($old, $this->manager, 'Fix yesterday');
            $this->fail('Manager reopened an older day');
        } catch (\Exception $e) {
            $this->assertStringContainsString('later day', $e->getMessage());
        }

        $this->assertTrue($this->svc->reopenSession($old->fresh(), $this->user('owner'))->isOpen());
    }

    public function test_other_shops_manager_and_locked_days_are_refused(): void
    {
        $session = $this->closedDay(business_today()->toDateString());

        $otherShop = DB::table('shops')->insertGetId([
            'name' => 'Other ' . uniqid(), 'code' => 'X' . substr(uniqid(), -8), 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        try {
            $this->svc->reopenSession($session, $this->user('shop_manager', $otherShop), 'Not mine');
            $this->fail('Another shop reopened this day');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->svc->lockSession($session, $this->user('owner'));
        $this->expectExceptionMessage('Locked sessions cannot be reopened');
        $this->svc->reopenSession($session->fresh(), $this->manager, 'Too late now');
    }

    public function test_component_shows_button_and_reopens(): void
    {
        $session = $this->closedDay(business_today()->toDateString());

        Livewire::actingAs($this->manager)
            ->test(ReopenSession::class, ['sessionId' => $session->id])
            ->assertSee('Re-open')
            ->set('showModal', true)
            ->call('reopen')
            ->assertHasErrors('reason')
            ->set('reason', 'Expense recorded twice')
            ->call('reopen')
            ->assertHasNoErrors()
            ->assertRedirect(route('shop.day-close.index'));

        $this->assertTrue($session->fresh()->isOpen());

        // Owner and open sessions get no button
        Livewire::actingAs($this->user('owner'))
            ->test(ReopenSession::class, ['sessionId' => $session->id])
            ->assertDontSee('Re-open');
    }
}
