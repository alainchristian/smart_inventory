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
}
