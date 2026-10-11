<?php

namespace Tests\Feature\DayClose;

use App\Livewire\Shop\DayClose\AddExpense;
use App\Livewire\Shop\DayClose\CloseWizard;
use App\Livewire\Shop\DayClose\DepositList;
use App\Livewire\Shop\DayClose\PendingRequests;
use App\Livewire\Shop\DayClose\Register;
use App\Models\DailySession;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cash Register / Close Register redesign — end-to-end flow through the
 * Livewire components (open → record → count → confirm → close).
 *
 * SAFETY: runs against the configured DB (no separate test DB — see
 * DailyReportDataTest), so DatabaseTransactions only, never RefreshDatabase.
 * Everything lives under a brand-new shop so existing rows can't interfere.
 */
class RegisterRedesignTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private User $manager;
    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();

        $u   = uniqid();
        $now = now()->toDateTimeString();

        $this->shopId = DB::table('shops')->insertGetId([
            'name' => "Register Test $u", 'code' => 'R' . substr($u, -8), 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->manager = User::forceCreate([
            'name' => 'Register Tester', 'email' => "reg$u@example.test", 'password' => bcrypt('x'),
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopId,
        ]);

        $this->categoryId = DB::table('expense_categories')->insertGetId([
            'name' => "Test Cat $u", 'applies_to' => 'both', 'is_active' => true, 'sort_order' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function openSession(int $opening = 50000): DailySession
    {
        Livewire::actingAs($this->manager)
            ->test(Register::class)
            ->set('openingBalance', (string) $opening)
            ->call('openRegister')
            ->assertHasNoErrors()
            ->assertSet('showOpenModal', false)
            ->assertDispatched('session-opened');

        return DailySession::forShop($this->shopId)->forDate(business_today()->toDateString())->firstOrFail();
    }

    public function test_register_shows_not_opened_state_then_opens(): void
    {
        Livewire::actingAs($this->manager)
            ->test(Register::class)
            ->assertSee('Cash Register')
            ->assertSee('Not opened')
            ->assertSee("The register isn't open yet", false);

        $session = $this->openSession(50000);

        $this->assertTrue($session->isOpen());
        $this->assertSame(50000, (int) $session->opening_balance);

        Livewire::actingAs($this->manager)
            ->test(Register::class)
            ->assertSee('Open')
            ->assertSee('Cash in drawer')
            ->assertSee('Expected in drawer')
            ->assertSee('50,000');
    }

    public function test_opening_balance_is_validated(): void
    {
        Livewire::actingAs($this->manager)
            ->test(Register::class)
            ->set('openingBalance', '')
            ->call('openRegister')
            ->assertHasErrors('openingBalance');
    }

    public function test_non_shop_manager_is_forbidden(): void
    {
        $owner = User::forceCreate([
            'name' => 'Owner Tester', 'email' => 'own' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'role' => 'owner',
        ]);

        Livewire::actingAs($owner)->test(Register::class)->assertForbidden();
    }

    public function test_full_close_flow_with_expense_denomination_total_and_notes(): void
    {
        $session = $this->openSession(50000);

        // Record a cash expense from the drawer form
        Livewire::actingAs($this->manager)
            ->test(AddExpense::class, ['dailySessionId' => $session->id, 'inDrawer' => true])
            ->set('categoryId', $this->categoryId)
            ->set('amount', '10000')
            ->set('description', 'Cleaning supplies')
            ->set('paymentMethod', 'cash')
            ->call('saveExpense')
            ->assertHasNoErrors()
            ->assertDispatched('expense-added')
            ->assertDispatched('notification');

        $wizard = Livewire::actingAs($this->manager)
            ->test(CloseWizard::class, ['dailySessionId' => $session->id])
            ->assertSee('Close register')
            ->assertSee('Sales review')
            ->assertSet('summary.expected_cash', 40000);

        // Step 1 → 2 → 3
        $wizard->call('nextStep')->assertSet('currentStep', 2)->assertSee('Cleaning supplies');
        $wizard->call('nextStep')->assertSet('currentStep', 3)->assertSee('By denomination');

        // Cash count is required before leaving step 3
        $wizard->call('nextStep')->assertHasErrors('actualCashCounted')->assertSet('currentStep', 3);

        // Denomination modal writes the total via $wire.set — 7×5,000 + 2×2,000 = 39,000 (1,000 short)
        $wizard->set('actualCashCounted', '39000')
            ->assertSee('Short')
            ->call('nextStep')
            ->assertSet('currentStep', 4)
            ->assertSet('cashVariance', -1000);

        // Stepper can jump back to a completed step, never forward
        $wizard->call('goToStep', 1)->assertSet('currentStep', 1);
        $wizard->call('goToStep', 4)->assertSet('currentStep', 1);
        $wizard->set('currentStep', 4);

        // Cannot send more to the owner than was counted
        $wizard->set('cashToOwnerMomo', '50000')
            ->call('openConfirm')
            ->assertHasErrors('cashToOwnerMomo')
            ->assertSet('showConfirm', false);

        // Empty "send to owner" is treated as 0, then the confirm modal opens
        $wizard->set('cashToOwnerMomo', '')
            ->set('notes', 'Short by 1,000 — change given twice')
            ->call('openConfirm')
            ->assertHasNoErrors()
            ->assertSet('showConfirm', true)
            ->assertSee('Close the register?')
            ->assertSee('1,000 RWF will be recorded as a cash-shortage expense', false);

        $wizard->call('submitClose')->assertRedirect(route('shop.dashboard'));

        $session->refresh();
        $this->assertFalse($session->isOpen());
        $this->assertSame(39000, (int) $session->actual_cash_counted);
        $this->assertSame(-1000, (int) $session->cash_variance);
        $this->assertSame('Short by 1,000 — change given twice', $session->notes);

        // Register now shows the closed summary
        Livewire::actingAs($this->manager)
            ->test(Register::class)
            ->assertSee("Today's register is closed", false)
            ->assertSee('39,000');
    }

    public function test_deposit_list_voids_a_deposit(): void
    {
        $session = $this->openSession(50000);
        $now = now()->toDateTimeString();

        $depositId = DB::table('bank_deposits')->insertGetId([
            'daily_session_id' => $session->id, 'shop_id' => $this->shopId, 'amount' => 20000, 'source' => 'cash',
            'bank_reference' => 'SLIP-1', 'deposited_by' => $this->manager->id, 'deposited_at' => $now,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        Livewire::actingAs($this->manager)
            ->test(DepositList::class, ['dailySessionId' => $session->id])
            ->assertSee('SLIP-1')
            ->call('voidDeposit', $depositId)
            ->assertDispatched('deposit-voided')
            ->assertDontSee('SLIP-1');
    }

    public function test_pending_requests_pay_needs_confirmation_step(): void
    {
        $this->openSession(50000);

        Livewire::actingAs($this->manager)
            ->test(PendingRequests::class)
            ->assertSee('No pending requests')
            ->call('confirmPay', 999999)
            ->assertSet('payingId', 999999)
            ->call('cancelPay')
            ->assertSet('payingId', null);
    }

    public function test_closing_an_old_day_from_the_register_returns_to_opening_today(): void
    {
        $yesterday = DailySession::forceCreate([
            'shop_id' => $this->shopId, 'session_date' => business_today()->subDay()->toDateString(), 'status' => 'open',
            'opening_balance' => 20000, 'opened_by' => $this->manager->id, 'opened_at' => now()->subDay(),
        ]);

        // Register links "Close <date> first" with the intent to open afterwards
        Livewire::actingAs($this->manager)->test(Register::class)
            ->assertSee(route('shop.session.close', ['session' => $yesterday->id, 'then' => 'open']), false);

        // ?open=1 while that day is still open: the form must not pop up
        Livewire::withQueryParams(['open' => 1])->actingAs($this->manager)->test(Register::class)
            ->assertSet('showOpenModal', false);

        $wizard = Livewire::withQueryParams(['then' => 'open'])->actingAs($this->manager)
            ->test(CloseWizard::class, ['dailySessionId' => $yesterday->id])
            ->assertSet('thenOpen', true);
        $wizard->call('nextStep')->call('nextStep')
            ->set('actualCashCounted', '20000')->call('nextStep')->assertSet('currentStep', 4)
            ->call('openConfirm')->assertSet('showConfirm', true)
            ->call('submitClose')
            ->assertRedirect(route('shop.session.open', ['open' => 1]));

        // Back on the Register: the open form is up, pre-filled with the cash kept yesterday
        Livewire::withQueryParams(['open' => 1])->actingAs($this->manager)->test(Register::class)
            ->assertSet('showOpenModal', true)
            ->assertSet('openingBalance', (string) (int) $yesterday->fresh()->cash_retained);
    }

    public function test_opening_from_another_page_goes_back_to_it(): void
    {
        // The POS gate button carries the POS address
        $this->manager->forceFill(['is_active' => true, 'must_change_password' => false])->save();
        $this->actingAs($this->manager)->get(route('shop.pos'))
            ->assertOk()
            ->assertSee(e(route('shop.session.open', ['return' => '/shop/pos'])), false);

        Livewire::withQueryParams(['return' => '/shop/pos'])->actingAs($this->manager)->test(Register::class)
            ->assertSet('returnTo', '/shop/pos')
            ->set('openingBalance', '10000')
            ->call('openRegister')
            ->assertHasNoErrors()
            ->assertRedirect('/shop/pos');
    }

    public function test_a_return_address_outside_the_app_is_ignored(): void
    {
        foreach (['https://evil.example/x', '//evil.example', '/livewire/update'] as $bad) {
            Livewire::withQueryParams(['return' => $bad])->actingAs($this->manager)->test(Register::class)
                ->assertSet('returnTo', '');
        }

        Livewire::withQueryParams(['return' => 'https://evil.example/x'])->actingAs($this->manager)->test(Register::class)
            ->set('openingBalance', '10000')
            ->call('openRegister')
            ->assertNoRedirect()
            ->assertDispatched('session-opened');
    }

    public function test_pos_close_old_day_open_today_then_back_to_pos(): void
    {
        $yesterday = DailySession::forceCreate([
            'shop_id' => $this->shopId, 'session_date' => business_today()->subDay()->toDateString(), 'status' => 'open',
            'opening_balance' => 15000, 'opened_by' => $this->manager->id, 'opened_at' => now()->subDay(),
        ]);

        // Register (reached from POS) keeps the address on its "Close … first" link
        Livewire::withQueryParams(['return' => '/shop/pos'])->actingAs($this->manager)->test(Register::class)
            ->assertSee(e(route('shop.session.close', ['session' => $yesterday->id, 'then' => 'open', 'return' => '/shop/pos'])), false);

        Livewire::withQueryParams(['then' => 'open', 'return' => '/shop/pos'])->actingAs($this->manager)
            ->test(CloseWizard::class, ['dailySessionId' => $yesterday->id])
            ->call('nextStep')->call('nextStep')
            ->set('actualCashCounted', '15000')->call('nextStep')
            ->call('openConfirm')
            ->call('submitClose')
            ->assertRedirect(route('shop.session.open', ['open' => 1, 'return' => '/shop/pos']));

        Livewire::withQueryParams(['open' => 1, 'return' => '/shop/pos'])->actingAs($this->manager)->test(Register::class)
            ->assertSet('showOpenModal', true)
            ->call('openRegister')
            ->assertRedirect('/shop/pos');

        $this->assertTrue(DailySession::forShop($this->shopId)->forDate(business_today()->toDateString())->firstOrFail()->isOpen());
    }
}
