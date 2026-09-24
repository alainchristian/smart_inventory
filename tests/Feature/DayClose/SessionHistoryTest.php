<?php

namespace Tests\Feature\DayClose;

use App\Livewire\Shop\DayClose\SessionHistory;
use App\Models\DailySession;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Session History restyle — scoping, filters, detail drawer, locking.
 * Runs on the dedicated _test DB (see phpunit.xml / TestCase guard).
 */
class SessionHistoryTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopA;
    private int $shopB;
    private User $manager;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $u = uniqid();
        $this->shopA = $this->shop("History A $u", 'HA' . substr($u, -7));
        $this->shopB = $this->shop("History B $u", 'HB' . substr($u, -7));

        $this->manager = User::forceCreate([
            'name' => 'History Manager', 'email' => "hm$u@example.test", 'password' => bcrypt('x'),
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopA,
        ]);
        $this->owner = User::forceCreate([
            'name' => 'History Owner', 'email' => "ho$u@example.test", 'password' => bcrypt('x'), 'role' => 'owner',
        ]);
    }

    private function shop(string $name, string $code): int
    {
        $now = now()->toDateTimeString();

        return DB::table('shops')->insertGetId(['name' => $name, 'code' => $code, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
    }

    private function makeSession(int $shopId, string $date, string $status, array $extra = []): DailySession
    {
        return DailySession::forceCreate($extra + [
            'shop_id' => $shopId, 'session_date' => $date, 'status' => $status,
            'opening_balance' => 10000, 'opened_by' => $this->manager->id, 'opened_at' => "$date 06:00:00",
            'closed_at' => $status === 'open' ? null : "$date 18:00:00",
            'total_sales' => 100000, 'total_sales_cash' => 60000, 'total_sales_momo' => 40000,
            'total_expenses' => 5000, 'total_withdrawals' => 0,
            'expected_cash' => 65000, 'actual_cash_counted' => $status === 'open' ? null : 64000,
            'cash_variance' => $status === 'open' ? null : -1000, 'cash_retained' => 64000,
        ]);
    }

    public function test_manager_sees_only_own_shop_with_totals_across_all_sessions(): void
    {
        $this->makeSession($this->shopA, '2020-05-01', 'locked');
        $this->makeSession($this->shopA, '2020-05-02', 'closed', ['notes' => 'Drawer short, counted twice']);
        $this->makeSession($this->shopB, '2020-05-02', 'closed', ['total_sales' => 999999]);

        Livewire::actingAs($this->manager)
            ->test(SessionHistory::class)
            ->assertSee('Session history')
            ->assertSee('Fri, 01 May 2020')
            ->assertSee('Sat, 02 May 2020')
            ->assertViewHas('stats', fn ($s) => (int) $s->sessions === 2 && (int) $s->sales === 200000 && (int) $s->short === 2)
            ->assertDontSee('999,999');
    }

    public function test_status_filter_and_detail_drawer(): void
    {
        $locked = $this->makeSession($this->shopA, '2020-05-01', 'locked');
        $closed = $this->makeSession($this->shopA, '2020-05-02', 'closed', ['notes' => 'Drawer short, counted twice']);

        $c = Livewire::actingAs($this->manager)->test(SessionHistory::class);

        $c->call('setStatus', 'locked')
            ->assertSet('status', 'locked')
            ->assertSee('Fri, 01 May 2020')
            ->assertDontSee('Sat, 02 May 2020');

        $c->call('setStatus', 'bogus')->assertSet('status', 'all');

        $c->call('toggleExpand', $closed->id)
            ->assertSee('Drawer short, counted twice')
            ->assertSee('Cash drawer')
            ->assertSee('Retained in drawer')
            ->assertDontSee('Lock session');   // managers can't lock

        $c->call('closeDetail')->assertSet('expandedId', null)->assertDontSee('Drawer short, counted twice');
    }

    public function test_manager_cannot_open_another_shops_session(): void
    {
        $other = $this->makeSession($this->shopB, '2020-05-03', 'closed', ['notes' => 'Secret other shop note']);

        Livewire::actingAs($this->manager)
            ->test(SessionHistory::class)
            ->call('toggleExpand', $other->id)
            ->assertDontSee('Secret other shop note');
    }

    public function test_owner_filters_by_shop_and_can_lock(): void
    {
        $closed = $this->makeSession($this->shopA, '2020-05-02', 'closed');
        $this->makeSession($this->shopB, '2020-05-04', 'closed');

        $c = Livewire::actingAs($this->owner)
            ->test(SessionHistory::class)
            ->assertViewHas('showShopColumn', true);

        $c->set('shopId', $this->shopA)
            ->assertViewHas('showShopColumn', false)
            ->assertSee('Sat, 02 May 2020')
            ->assertDontSee('Mon, 04 May 2020');

        $c->call('toggleExpand', $closed->id)
            ->assertSee('Lock session')
            ->call('lockSession', $closed->id)
            ->assertDispatched('notification');

        $this->assertTrue($closed->fresh()->isLocked());
    }

    public function test_open_session_links_to_the_session_specific_close_route(): void
    {
        $open = $this->makeSession($this->shopA, '2020-05-05', 'open');

        Livewire::actingAs($this->manager)
            ->test(SessionHistory::class)
            ->assertSee(route('shop.session.close', ['session' => $open->id]), false);
    }
}
