<?php

namespace Tests\Feature\Owner;

use App\Livewire\Owner\Finance\DailyCloseReport;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Daily Close "Daily Balance Statement": every source of money (left) must
 * land somewhere on the right. It showed "Off by 2,000,000" on a day with
 * 2,000,000 of bank-transfer sales, because bank/card money, bank repayments,
 * cash sent to the owner at close and counting variances had no row.
 */
class DailyCloseBalanceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_statement_balances_with_bank_card_owner_transfer_and_shortage(): void
    {
        $owner = User::forceCreate([
            'name' => 'Balance Owner', 'email' => 'bo' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'role' => 'owner', 'is_active' => true,
        ]);
        $u = uniqid();
        $shop = DB::table('shops')->insertGetId([
            'name' => "Balance $u", 'code' => 'B' . substr($u, -8), 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // opening 100k · cash 200k · MoMo 300k · card 100k · bank 500k
        // repayments 70k (cash 20k, MoMo 30k, bank 20k) · cash expense 10k
        // expected cash 310k, counted 307k (3k short), 50k sent to the owner by MoMo
        DB::table('daily_sessions')->insert([
            'shop_id' => $shop, 'session_date' => '2021-07-15', 'status' => 'closed',
            'opened_by' => $owner->id, 'opened_at' => now(), 'closed_at' => now(), 'closed_by' => $owner->id,
            'opening_balance' => 100000,
            'total_sales_cash' => 200000, 'total_sales_momo' => 300000, 'total_sales_card' => 100000,
            'total_sales_bank_transfer' => 500000, 'total_sales_credit' => 0, 'total_sales' => 1100000,
            'total_repayments' => 70000, 'total_repayments_cash' => 20000, 'total_repayments_momo' => 30000,
            'total_expenses' => 10000, 'total_expenses_cash' => 10000, 'total_expenses_momo' => 0,
            'expected_cash' => 310000, 'actual_cash_counted' => 307000, 'cash_variance' => -3000,
            'cash_to_owner_momo' => 50000, 'cash_retained' => 257000,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $c = Livewire::actingAs($owner)->test(DailyCloseReport::class)->set('reportDate', '2021-07-15');

        $this->assertSame(1270000, $c->viewData('totalIn'));
        $this->assertSame(0, $c->viewData('balanceDiff'));
        $this->assertTrue($c->viewData('isBalanced'));

        $labels = array_column($c->viewData('outRows'), 0);
        $this->assertContains('Bank / card received', $labels);
        $this->assertContains('Sent to owner (MoMo)', $labels);
        $this->assertContains('Cash short', $labels);
    }
}
