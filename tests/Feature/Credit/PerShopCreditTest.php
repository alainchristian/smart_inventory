<?php

namespace Tests\Feature\Credit;

use App\Livewire\Owner\CreditWriteoffs;
use App\Livewire\Shop\CreditRepayments;
use App\Models\Customer;
use App\Models\CustomerShopBalance;
use App\Models\DailySession;
use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\User;
use App\Services\Returns\ReturnService;
use App\Services\Sales\CustomerCreditLedger;
use App\Services\Sales\CustomerService;
use App\Services\Sales\SaleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Credit belongs to the shop that gave it (customer_shop_balances).
 * Runs on smart_inventory_test only (phpunit.xml + TestCase guard).
 */
class PerShopCreditTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopA;
    private int $shopB;
    private User $managerA;
    private User $managerB;
    private User $owner;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $u = uniqid();
        $this->shopA = $this->shop("Kigalifootwear — Remera $u", 'PA' . substr($u, -7));
        $this->shopB = $this->shop("Kigalifootwear — Kimironko $u", 'PB' . substr($u, -7));

        $this->owner    = User::forceCreate(['name' => 'Owner', 'email' => "o$u@example.test", 'password' => bcrypt('x'), 'role' => 'owner']);
        $this->managerA = $this->manager("a$u", $this->shopA);
        $this->managerB = $this->manager("b$u", $this->shopB);

        // Registered at shop A — registration must NOT decide who can collect
        $this->customer = Customer::forceCreate([
            'name' => 'Innocent Mugenzi', 'phone' => '0788' . random_int(100000, 999999),
            'registered_by' => $this->managerA->id, 'shop_id' => $this->shopA,
            'total_credit_given' => 0, 'total_repaid' => 0, 'outstanding_balance' => 0,
        ]);

        // CreditRepayments requires an open session for shop managers
        foreach ([[$this->shopA, $this->managerA], [$this->shopB, $this->managerB]] as [$shop, $mgr]) {
            DailySession::forceCreate([
                'shop_id' => $shop, 'session_date' => business_today()->toDateString(), 'status' => 'open',
                'opening_balance' => 0, 'opened_by' => $mgr->id, 'opened_at' => now(),
            ]);
        }
    }

    private function shop(string $name, string $code): int
    {
        $now = now()->toDateTimeString();

        return DB::table('shops')->insertGetId(['name' => $name, 'code' => $code, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
    }

    private function manager(string $tag, int $shopId): User
    {
        return User::forceCreate([
            'name' => "Manager $tag", 'email' => "$tag@example.test", 'password' => bcrypt('x'),
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $shopId,
        ]);
    }

    private function ledger(): CustomerCreditLedger
    {
        return app(CustomerCreditLedger::class);
    }

    /** Credit sale rows as SaleService writes them (sale + credit payment). */
    private function creditSale(int $shopId, int $total, int $credit): Sale
    {
        $now = now()->toDateTimeString();
        $saleId = DB::table('sales')->insertGetId([
            'sale_number' => 'T-' . uniqid(), 'shop_id' => $shopId, 'sold_by' => $this->managerA->id,
            'sale_date' => $now, 'type' => 'full_box', 'payment_method' => 'credit',
            'customer_id' => $this->customer->id, 'customer_name' => $this->customer->name,
            'subtotal' => $total, 'total' => $total, 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('sale_payments')->insert(['sale_id' => $saleId, 'payment_method' => 'credit', 'amount' => $credit, 'created_at' => $now, 'updated_at' => $now]);
        if ($total > $credit) {
            DB::table('sale_payments')->insert(['sale_id' => $saleId, 'payment_method' => 'cash', 'amount' => $total - $credit, 'created_at' => $now, 'updated_at' => $now]);
        }

        return Sale::findOrFail($saleId);
    }

    public function test_credit_is_recorded_against_the_selling_shop_and_totals_stay_in_sync(): void
    {
        $svc = new CustomerService();
        $svc->recordSalePurchase($this->customer, 40000, $this->shopB);   // sold at B, registered at A
        $svc->recordSalePurchase($this->customer, 15000, $this->shopA);

        $this->assertSame(15000, $this->ledger()->balanceAt($this->customer, $this->shopA));
        $this->assertSame(40000, $this->ledger()->balanceAt($this->customer, $this->shopB));

        $c = $this->customer->fresh();
        $this->assertSame(55000, $c->outstanding_balance);
        $this->assertSame(55000, $c->total_credit_given);
        $this->assertNotNull($c->last_credit_at);
    }

    public function test_credit_without_a_shop_is_refused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new CustomerService())->recordSalePurchase($this->customer, 1000, null);
    }

    public function test_repayment_only_at_the_lending_shop(): void
    {
        $this->ledger()->extend($this->customer, $this->shopB, 40000);

        // Shop A (where the customer registered) doesn't see B's debt
        Livewire::actingAs($this->managerA)->test(CreditRepayments::class)
            ->assertDontSee('Innocent Mugenzi');

        // Shop B sees exactly what's owed to B and collects it
        Livewire::actingAs($this->managerB)->test(CreditRepayments::class)
            ->assertSee('Innocent Mugenzi')
            ->assertSee('40,000')
            ->call('selectCustomer', $this->customer->id)
            ->set('payAmt_cash', 50000)
            ->call('recordRepayment')
            ->assertHasErrors('total')                      // more than owed to B
            ->set('payAmt_cash', 25000)
            ->call('recordRepayment')
            ->assertHasNoErrors()
            ->assertDispatched('notification');

        $this->assertSame(15000, $this->ledger()->balanceAt($this->customer, $this->shopB));
        $this->assertSame(15000, $this->customer->fresh()->outstanding_balance);
        $this->assertSame(25000, (int) DB::table('credit_repayments')->where('customer_id', $this->customer->id)->where('shop_id', $this->shopB)->sum('amount'));

        // Shop A still can't collect it
        Livewire::actingAs($this->managerA)->test(CreditRepayments::class)
            ->set('selectedCustomerId', $this->customer->id)
            ->set('payAmt_cash', 1000)
            ->call('recordRepayment')
            ->assertHasErrors('total');
        $this->assertSame(15000, $this->customer->fresh()->outstanding_balance);
    }

    public function test_owner_sees_breakdown_but_cannot_record(): void
    {
        $this->ledger()->extend($this->customer, $this->shopA, 10000);
        $this->ledger()->extend($this->customer, $this->shopB, 45000);

        Livewire::actingAs($this->owner)->test(CreditRepayments::class)
            ->assertSee('Innocent Mugenzi')
            ->assertSee('55,000')
            ->assertSee('Kimironko')
            ->assertSee('Collected at shop')
            ->call('selectCustomer', $this->customer->id)
            ->assertSet('selectedCustomerId', null);
    }

    public function test_writeoff_reduces_only_that_shops_balance(): void
    {
        $this->ledger()->extend($this->customer, $this->shopA, 10000);
        $this->ledger()->extend($this->customer, $this->shopB, 45000);

        Livewire::actingAs($this->owner)->test(CreditWriteoffs::class)
            ->assertSee('45,000')
            ->call('startWriteoff', $this->customer->id, $this->shopB)
            ->set('writeoffAmount', 50000)
            ->set('writeoffReason', 'Customer relocated, unreachable')
            ->call('proceedToConfirm')
            ->assertHasErrors('writeoffAmount')
            ->set('writeoffAmount', 45000)
            ->call('proceedToConfirm')
            ->assertSet('confirmStep', true)
            ->call('submitWriteoff');

        $this->assertSame(10000, $this->ledger()->balanceAt($this->customer, $this->shopA));
        $this->assertSame(0, $this->ledger()->balanceAt($this->customer, $this->shopB));
        $this->assertSame(10000, $this->customer->fresh()->outstanding_balance);
        $this->assertDatabaseHas('credit_writeoffs', ['customer_id' => $this->customer->id, 'shop_id' => $this->shopB, 'amount' => 45000]);
    }

    public function test_voiding_a_credit_sale_reverses_the_credit(): void
    {
        $sale = $this->creditSale($this->shopB, 60000, 40000);
        $this->ledger()->extend($this->customer, $this->shopB, 40000);

        $this->actingAs($this->managerB);
        app(SaleService::class)->voidSale($sale, 'Entered twice');

        $row = CustomerShopBalance::where('customer_id', $this->customer->id)->where('shop_id', $this->shopB)->first();
        $this->assertSame(0, $row->outstanding_balance);
        $this->assertSame(0, $row->total_credit_given);
        $this->assertSame(0, $this->customer->fresh()->outstanding_balance);
    }

    public function test_return_refunded_against_debt_reduces_balance_on_approval(): void
    {
        $sale = $this->creditSale($this->shopB, 40000, 40000);
        $this->ledger()->extend($this->customer, $this->shopB, 40000);

        $return = ReturnModel::forceCreate([
            'return_number' => 'RET-' . uniqid(), 'sale_id' => $sale->id, 'shop_id' => $this->shopB,
            'reason' => 'defective', 'refund_amount' => 12000, 'refund_method' => 'credit_balance',
            'is_exchange' => false, 'processed_by' => $this->managerB->id, 'processed_at' => now(),
        ]);

        $this->assertSame(40000, $this->ledger()->balanceAt($this->customer, $this->shopB), 'not applied before approval');

        app(ReturnService::class)->approveReturn($return, $this->owner);

        $this->assertSame(28000, $this->ledger()->balanceAt($this->customer, $this->shopB));
        $this->assertSame(28000, $this->customer->fresh()->outstanding_balance);
    }

    public function test_pos_warning_names_each_shop(): void
    {
        $this->ledger()->extend($this->customer, $this->shopA, 20000);
        $this->ledger()->extend($this->customer, $this->shopB, 45000);

        $msg = $this->ledger()->describeOwed($this->customer, $this->shopA);
        $this->assertStringContainsString('Owes this shop 20,000 RWF', $msg);
        $this->assertStringContainsString('Kimironko', $msg);
        $this->assertStringContainsString('45,000 RWF', $msg);
        $this->assertSame('', $this->ledger()->describeOwed(Customer::forceCreate([
            'name' => 'No Debt', 'phone' => '0799' . random_int(100000, 999999), 'registered_by' => $this->owner->id,
            'total_credit_given' => 0, 'total_repaid' => 0, 'outstanding_balance' => 0,
        ]), $this->shopA));
    }

    public function test_backfill_attributes_history_to_the_lending_shop(): void
    {
        // History as the old code wrote it: credit at B (registered at A),
        // 10,000 repaid wrongly at A, customer total = 30,000 owed.
        $this->creditSale($this->shopB, 40000, 40000);
        DB::table('credit_repayments')->insert([
            'customer_id' => $this->customer->id, 'shop_id' => $this->shopA, 'amount' => 10000,
            'payment_method' => 'cash', 'recorded_by' => $this->managerA->id, 'repayment_date' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->customer->forceFill(['total_credit_given' => 40000, 'total_repaid' => 10000, 'outstanding_balance' => 30000])->save();

        // Re-run the migration against this history (Postgres DDL is transactional → rolled back)
        $migration = require base_path('database/migrations/2026_09_24_000001_create_customer_shop_balances_table.php');
        $migration->down();
        $migration->up();

        $this->assertSame(30000, $this->ledger()->balanceAt($this->customer, $this->shopB), 'debt stays with the lending shop');
        $this->assertSame(0, $this->ledger()->balanceAt($this->customer, $this->shopA));
        $this->assertSame(30000, (int) CustomerShopBalance::where('customer_id', $this->customer->id)->sum('outstanding_balance'));
    }
}
