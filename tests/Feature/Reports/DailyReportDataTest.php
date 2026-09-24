<?php

namespace Tests\Feature\Reports;

use App\Services\DayClose\DailySessionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Characterization + extension tests for the Daily Report data layer.
 *
 * SAFETY: phpunit.xml defines no separate test database (Postgres native enums
 * rule out sqlite), so these run against the configured DB. They therefore use
 * DatabaseTransactions (everything is rolled back) and NEVER RefreshDatabase.
 * All data is created under a brand-new shop on a far-past date so pre-existing
 * rows can never leak into shop-scoped assertions.
 */
class DailyReportDataTest extends TestCase
{
    use DatabaseTransactions;

    private const DAY = '2020-03-10';

    private int $shopId;
    private int $userId;
    private int $sessionId;
    private int $customerId;
    private int $categoryId;
    private int $productId;
    private array $saleIds = [];

    private function tz(): string
    {
        return config('tenant.timezone');
    }

    /** UTC timestamp string for a business-timezone local time on $date. */
    private function utc(string $date, string $time = '12:00:00'): string
    {
        return Carbon::parse("$date $time", $this->tz())->utc()->toDateTimeString();
    }

    private function insert(string $table, array $row): int
    {
        $now = now()->toDateTimeString();

        return DB::table($table)->insertGetId($row + ['created_at' => $now, 'updated_at' => $now]);
    }

    private function makeShopAndBasics(): void
    {
        $u = uniqid();
        $this->shopId = $this->insert('shops', ['name' => "Test Shop $u", 'code' => 'T' . substr($u, -8), 'is_active' => true]);
        $this->userId = $this->insert('users', ['name' => 'Tester', 'email' => "t$u@example.test", 'password' => 'x']);
        $this->customerId = $this->insert('customers', [
            'name' => 'Alice', 'phone' => '0788000' . random_int(100, 999), 'registered_by' => $this->userId,
            'shop_id' => $this->shopId, 'outstanding_balance' => 30000,
        ]);
        $this->categoryId = $this->insert('categories', ['name' => "Cat $u", 'code' => 'C' . substr($u, -8)]);
        $this->productId = $this->insert('products', [
            'sku' => "SKU$u", 'name' => "Prod $u", 'items_per_box' => 24, 'category_id' => $this->categoryId,
            'purchase_price' => 1000, 'selling_price' => 2500,
        ]);
    }

    private function makeSale(string $number, string $customer, int $total, array $payments, array $extra = []): int
    {
        $id = $this->insert('sales', $extra + [
            'sale_number' => $number . '-' . uniqid(), 'shop_id' => $this->shopId, 'sold_by' => $this->userId,
            'sale_date' => $this->utc(self::DAY), 'type' => 'full_box', 'payment_method' => 'cash',
            'customer_name' => $customer, 'subtotal' => $total, 'total' => $total,
        ]);
        foreach ($payments as $method => $amount) {
            $this->insert('sale_payments', ['sale_id' => $id, 'payment_method' => $method, 'amount' => $amount]);
        }
        $this->saleIds[$number] = $id;

        return $id;
    }

    /**
     * One shop, one day (2020-03-10), one open session (opening 100,000):
     *  A  cash 50,000 (1 full box, 24 items)      B  momo 30,000
     *  C  split cash 10k + momo 20k + credit 30k  (price override, 1 loose item x2)
     *  D  voided cash 99,999
     *  repayments cash 20k + momo 15k; expenses cash 5k + momo 7k;
     *  withdrawal cash 8k; bank deposit cash 40k.
     */
    private function seedScenario(): void
    {
        $this->makeShopAndBasics();

        $this->sessionId = $this->insert('daily_sessions', [
            'session_date' => self::DAY, 'shop_id' => $this->shopId, 'opening_balance' => 100000,
            'opened_by' => $this->userId, 'opened_at' => $this->utc(self::DAY, '08:00:00'),
        ]);

        $a = $this->makeSale('A', 'Alice', 50000, ['cash' => 50000], ['sale_date' => $this->utc(self::DAY, '09:00:00')]);
        $this->makeSale('B', 'Bob', 30000, ['mobile_money' => 30000], ['sale_date' => $this->utc(self::DAY, '10:00:00')]);
        $c = $this->makeSale('C', 'Alice', 60000, ['cash' => 10000, 'mobile_money' => 20000, 'credit' => 30000], [
            'sale_date' => $this->utc(self::DAY, '11:00:00'), 'type' => 'mixed', 'is_split_payment' => true,
            'has_price_override' => true, 'has_credit' => true, 'credit_amount' => 30000,
        ]);
        $this->makeSale('D', 'Dan', 99999, ['cash' => 99999], [
            'sale_date' => $this->utc(self::DAY, '13:00:00'), 'voided_at' => $this->utc(self::DAY, '13:30:00'),
        ]);

        $this->insert('sale_items', [
            'sale_id' => $a, 'product_id' => $this->productId, 'quantity_sold' => 24, 'is_full_box' => true,
            'original_unit_price' => 50000, 'actual_unit_price' => 50000, 'line_total' => 50000,
        ]);
        $this->insert('sale_items', [
            'sale_id' => $c, 'product_id' => $this->productId, 'quantity_sold' => 2, 'is_full_box' => false,
            'original_unit_price' => 2500, 'actual_unit_price' => 2000, 'line_total' => 4000, 'price_was_modified' => true,
        ]);

        foreach (['cash' => 20000, 'mobile_money' => 15000] as $method => $amount) {
            $this->insert('credit_repayments', [
                'customer_id' => $this->customerId, 'shop_id' => $this->shopId, 'daily_session_id' => $this->sessionId,
                'amount' => $amount, 'payment_method' => $method, 'recorded_by' => $this->userId,
                'repayment_date' => $this->utc(self::DAY, '14:00:00'),
            ]);
        }

        $catName = 'Transport ' . uniqid();
        $expCat = $this->insert('expense_categories', ['name' => $catName]);
        foreach (['cash' => 5000, 'mobile_money' => 7000] as $method => $amount) {
            $this->insert('expenses', [
                'daily_session_id' => $this->sessionId, 'expense_category_id' => $expCat, 'amount' => $amount,
                'description' => "$method expense", 'payment_method' => $method, 'recorded_by' => $this->userId,
                'recorded_at' => $this->utc(self::DAY, '15:00:00'),
            ]);
        }

        $this->insert('owner_withdrawals', [
            'daily_session_id' => $this->sessionId, 'shop_id' => $this->shopId, 'amount' => 8000,
            'reason' => 'household', 'method' => 'cash', 'recorded_by' => $this->userId,
            'recorded_at' => $this->utc(self::DAY, '16:00:00'),
        ]);

        $this->insert('bank_deposits', [
            'daily_session_id' => $this->sessionId, 'shop_id' => $this->shopId, 'amount' => 40000, 'source' => 'cash',
            'bank_reference' => 'BK-1', 'deposited_by' => $this->userId, 'deposited_at' => $this->utc(self::DAY, '17:00:00'),
        ]);
    }

    private function service(): DailySessionService
    {
        return app(DailySessionService::class);
    }

    public function test_characterization_existing_keys_of_compute_range_summary(): void
    {
        $this->seedScenario();

        $s = $this->service()->computeRangeSummary($this->shopId, self::DAY, self::DAY);

        // Sales by channel (voided sale D excluded)
        $this->assertSame(60000, $s['total_sales_cash']);
        $this->assertSame(50000, $s['total_sales_momo']);
        $this->assertSame(0, $s['total_sales_card']);
        $this->assertSame(0, $s['total_sales_bank_transfer']);
        $this->assertSame(30000, $s['total_sales_credit']);
        $this->assertSame(0, $s['total_sales_other']);
        $this->assertSame(140000, $s['total_sales']);
        $this->assertSame(3, $s['transaction_count']);

        // Boxes
        $this->assertSame(1, $s['total_boxes_sold']);
        $this->assertCount(1, $s['boxes_by_product']);
        $this->assertSame(1, (int) $s['boxes_by_product'][0]->boxes);
        $this->assertSame(50000, (int) $s['boxes_by_product'][0]->amount);

        // all_sales
        $this->assertCount(3, $s['all_sales']);
        $this->assertSame([50000, 30000, 60000], $s['all_sales']->map(fn ($r) => (int) $r->total)->all());
        $this->assertSame([1, 0, 0], $s['all_sales']->map(fn ($r) => (int) $r->boxes)->all());

        // Expenses
        $this->assertSame(12000, $s['total_expenses']);
        $this->assertCount(2, $s['expenses_detailed']);
        $this->assertSame(12000, (int) $s['expenses_detailed']->sum('amount'));

        // Bank deposits (existing keys: total_deposits / deposits_detailed)
        $this->assertSame(40000, $s['total_deposits']);
        $this->assertCount(1, $s['deposits_detailed']);
        $this->assertSame('cash', $s['deposits_detailed'][0]->source);
        $this->assertSame('BK-1', $s['deposits_detailed'][0]->bank_reference);

        // Credit + repayments
        $this->assertCount(1, $s['credits_by_customer']);
        $this->assertSame(30000, (int) $s['credits_by_customer'][0]->amount);
        $this->assertSame(35000, $s['total_repayments']);
        $this->assertCount(1, $s['repayments_by_customer']);
        $this->assertSame('Alice', $s['repayments_by_customer'][0]->customer_name);
        $this->assertSame(35000, (int) $s['repayments_by_customer'][0]->amount);
        $this->assertSame(2, (int) $s['repayments_by_customer'][0]->repayment_count);

        // By customer per channel
        $this->assertSame(['Alice' => 60000], $s['cash_by_customer']->pluck('amount', 'customer_name')->map(fn ($v) => (int) $v)->all());
        $this->assertSame(['Bob' => 30000, 'Alice' => 20000], $s['momo_by_customer']->pluck('amount', 'customer_name')->map(fn ($v) => (int) $v)->all());
        $this->assertCount(0, $s['card_by_customer']);
        $this->assertCount(0, $s['bank_by_customer']);

        // Receivables / profit
        $this->assertSame(30000, $s['outstanding_receivables']);
        $this->assertSame(1, $s['customers_owing_count']);
        $this->assertSame(26000, $s['total_cogs']);       // (24 + 2) items x 1,000
        $this->assertSame(114000, $s['gross_profit']);    // 140,000 - 26,000
        $this->assertSame(102000, $s['net_for_period']);  // 114,000 - 12,000
    }

    /** Close the seeded session the way closeSession() stores it (no alerts/expenses side effects). */
    private function closeSeededSession(int $counted): void
    {
        $session = \App\Models\DailySession::find($this->sessionId);
        $live = $this->service()->computeLiveSummary($session);

        DB::table('daily_sessions')->where('id', $this->sessionId)->update([
            'total_sales'            => $live['total_sales'],
            'total_sales_cash'       => $live['total_sales_cash'],
            'total_refunds_cash'     => $live['total_refunds_cash'],
            'total_expenses'         => $live['total_expenses'],
            'total_expenses_cash'    => $live['total_expenses_cash'],
            'total_withdrawals'      => $live['total_withdrawals'],
            'total_withdrawals_cash' => $live['total_withdrawals_cash'],
            'total_bank_deposits'    => $live['total_bank_deposits'],
            'cash_deposits'          => $live['cash_deposits'],
            'total_repayments'       => $live['total_repayments'],
            'total_repayments_cash'  => $live['total_repayments_cash'],
            'expected_cash'          => $live['expected_cash'],
            'actual_cash_counted'    => $counted,
            'cash_variance'          => $counted - $live['expected_cash'],
            'cash_retained'          => $counted,
            'status'                 => 'closed',
            'closed_by'              => $this->userId,
            'closed_at'              => $this->utc(self::DAY, '20:00:00'),
        ]);
    }

    private function codes(array $checks): array
    {
        return array_column($checks, 'code');
    }

    public function test_new_split_totals_add_up_to_existing_parents(): void
    {
        $this->seedScenario();
        $s = $this->service()->computeRangeSummary($this->shopId, self::DAY, self::DAY);

        // repayments: cash 20k + momo 15k
        $this->assertSame(20000, $s['total_repayments_cash']);
        $this->assertSame(15000, $s['total_repayments_momo']);
        $this->assertSame(0, $s['total_repayments_bank']);
        $this->assertSame($s['total_repayments'], $s['total_repayments_cash'] + $s['total_repayments_momo'] + $s['total_repayments_bank']);
        $row = $s['repayments_by_customer'][0];
        $this->assertSame((int) $row->amount, (int) $row->cash + (int) $row->momo + (int) $row->bank);

        // expenses: cash 5k + momo 7k
        $this->assertSame(5000, $s['total_expenses_cash']);
        $this->assertSame(7000, $s['total_expenses_momo']);
        $this->assertSame($s['total_expenses'], $s['total_expenses_cash'] + $s['total_expenses_momo'] + $s['total_expenses_bank']);
        $this->assertCount(1, $s['expenses_by_category']);
        $cat = $s['expenses_by_category'][0];
        $this->assertSame([2, 5000, 7000, 0, 12000], [$cat->count, $cat->cash, $cat->momo, $cat->bank, $cat->total]);
        $this->assertSame('Tester', $s['expenses_detailed'][0]->recorded_by_name);
        $this->assertNotEmpty((string) $s['expenses_detailed'][0]->payment_method);

        // withdrawals / deposits / refunds
        $this->assertSame([8000, 8000, 0], [$s['total_withdrawals'], $s['total_withdrawals_cash'], $s['total_withdrawals_momo']]);
        $this->assertCount(1, $s['withdrawals_detailed']);
        $this->assertSame('household', $s['withdrawals_detailed'][0]->reason);
        $this->assertSame([40000, 40000, 0], [$s['total_bank_deposits'], $s['bank_deposits_from_cash'], $s['bank_deposits_from_momo']]);
        $this->assertSame('Tester', $s['bank_deposits_detailed'][0]->deposited_by_name);
        $this->assertSame([0, 0], [$s['total_refunds'], $s['total_refunds_cash']]);
        $this->assertCount(0, $s['refunds_detailed']);
    }

    public function test_refunds_are_reported_and_exchanges_excluded(): void
    {
        $this->seedScenario();
        foreach ([['R1', 'cash', 3000, false], ['R2', 'mobile_money', 2000, false], ['R3', 'cash', 9999, true]] as [$n, $m, $amt, $ex]) {
            $this->insert('returns', [
                'return_number' => $n . uniqid(), 'shop_id' => $this->shopId, 'customer_name' => 'Alice', 'refund_amount' => $amt,
                'refund_method' => $m, 'is_exchange' => $ex, 'processed_by' => $this->userId,
                'processed_at' => $this->utc(self::DAY, '12:30:00'), 'reason' => 'other',
            ]);
        }

        $s = $this->service()->computeRangeSummary($this->shopId, self::DAY, self::DAY);
        $this->assertSame(5000, $s['total_refunds']);
        $this->assertSame(3000, $s['total_refunds_cash']);
        $this->assertCount(2, $s['refunds_detailed']);
        $this->assertSame(self::DAY, $s['refunds_detailed'][0]->date);
    }

    public function test_all_sales_per_method_columns_add_up_for_split_sale(): void
    {
        $this->seedScenario();
        $s = $this->service()->computeRangeSummary($this->shopId, self::DAY, self::DAY);

        $split = $s['all_sales']->firstWhere('total', 60000);
        $this->assertSame([10000, 20000, 0, 0, 30000], [(int) $split->cash, (int) $split->momo, (int) $split->card, (int) $split->bank_transfer, (int) $split->credit]);
        $this->assertTrue((bool) $split->is_split);
        $this->assertTrue((bool) $split->has_price_override);

        foreach ($s['all_sales'] as $r) {
            $this->assertSame((int) $r->total, (int) $r->cash + (int) $r->momo + (int) $r->card + (int) $r->bank_transfer + (int) $r->credit, "sale {$r->sale_number}");
            $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $r->sale_time);
        }
        $this->assertFalse((bool) $s['all_sales'][0]->is_split);
        $this->assertSame('11:00', $s['all_sales']->firstWhere('total', 60000)->sale_time);
    }

    public function test_cash_reconciliation_open_session_uses_live_summary(): void
    {
        $this->seedScenario();
        $live = $this->service()->computeLiveSummary(\App\Models\DailySession::find($this->sessionId));

        $rows = $this->service()->getCashReconciliation($this->shopId, self::DAY, self::DAY);
        $this->assertCount(1, $rows);
        $r = $rows[0];

        $this->assertSame('live', $r->source);
        $this->assertSame($live['expected_cash'], $r->expected);
        $this->assertSame(127000, $r->expected); // 100k + 60k + 20k - 5k - 8k - 40k
        $this->assertSame(0, $r->other_adjustments);
        $this->assertNull($r->counted);
        $this->assertNull($r->variance);
        $this->assertSame([100000, 60000, 20000, 0, 5000, 8000, 40000],
            [$r->opening, $r->cash_sales, $r->cash_repayments, $r->cash_refunds, $r->cash_expenses, $r->cash_withdrawals, $r->cash_deposits]);
    }

    public function test_cash_reconciliation_closed_session_uses_stored_expected(): void
    {
        $this->seedScenario();
        $this->closeSeededSession(126000); // 1,000 short
        DB::table('daily_sessions')->where('id', $this->sessionId)->update(['expected_cash' => 127000]);

        $r = $this->service()->getCashReconciliation($this->shopId, self::DAY, self::DAY)[0];
        $this->assertSame('stored', $r->source);
        $this->assertSame(127000, $r->expected);
        $this->assertSame(126000, $r->counted);
        $this->assertSame(-1000, $r->variance);
        $this->assertSame(0, $r->other_adjustments);
        $this->assertSame(126000, $r->cash_retained);
    }

    public function test_comparison_single_day_uses_same_weekday_last_week(): void
    {
        $this->seedScenario();
        // 2020-03-03 is the Tuesday a week before 2020-03-10; 2020-03-04 must be ignored.
        $this->makeSale('W1', 'Zed', 1000, ['cash' => 1000], ['sale_date' => $this->utc('2020-03-03')]);
        $this->makeSale('W2', 'Zed', 9000, ['cash' => 9000], ['sale_date' => $this->utc('2020-03-04')]);

        $c = $this->service()->computeComparisonTotals($this->shopId, self::DAY, self::DAY);
        $this->assertSame('2020-03-03', $c['date_from']);
        $this->assertSame('2020-03-03', $c['date_to']);
        $this->assertSame('Tue 3 Mar', $c['label']);
        $this->assertSame([1000, 1000, 0, 0], [$c['total_sales'], $c['total_sales_cash'], $c['total_sales_momo'], $c['total_sales_credit']]);
        $this->assertSame([0, 0], [$c['total_expenses'], $c['total_repayments']]);
    }

    public function test_comparison_range_uses_previous_equal_length_period(): void
    {
        $this->seedScenario();
        // Range 8–10 Mar (3 days) => previous period 5–7 Mar.
        $this->makeSale('P1', 'Zed', 7000, ['cash' => 7000], ['sale_date' => $this->utc('2020-03-06')]);
        $this->makeSale('P2', 'Zed', 500, ['mobile_money' => 500], ['sale_date' => $this->utc('2020-03-05')]);
        $this->makeSale('P3', 'Zed', 9000, ['cash' => 9000], ['sale_date' => $this->utc('2020-03-04')]); // outside

        $c = $this->service()->computeComparisonTotals($this->shopId, '2020-03-08', self::DAY);
        $this->assertSame(['2020-03-05', '2020-03-07', '5–7 Mar'], [$c['date_from'], $c['date_to'], $c['label']]);
        $this->assertSame([7500, 7000, 500], [$c['total_sales'], $c['total_sales_cash'], $c['total_sales_momo']]);
    }

    public function test_report_checks_session_open(): void
    {
        $this->seedScenario();
        $checks = $this->service()->getReportChecks($this->shopId, self::DAY, self::DAY);

        $codes = $this->codes($checks);
        $this->assertContains('session_open', $codes);
        $this->assertContains('price_overrides', $codes);
        $this->assertContains('voided_sales', $codes);
        $this->assertNotContains('payments_mismatch', $codes);
        $this->assertNotContains('stored_vs_live', $codes);
        $this->assertNotContains('unlinked_sales', $codes);

        $voided = collect($checks)->firstWhere('code', 'voided_sales');
        $this->assertSame(99999, $voided['amount']);
    }

    public function test_report_checks_stored_vs_live_after_tampering_with_closed_session(): void
    {
        $this->seedScenario();
        $this->closeSeededSession(127000);

        $clean = $this->codes($this->service()->getReportChecks($this->shopId, self::DAY, self::DAY));
        $this->assertNotContains('stored_vs_live', $clean);
        $this->assertNotContains('session_open', $clean);
        $this->assertNotContains('cash_variance', $clean);

        DB::table('daily_sessions')->where('id', $this->sessionId)->update(['total_sales' => 100000]);
        $checks = $this->service()->getReportChecks($this->shopId, self::DAY, self::DAY);
        $item = collect($checks)->firstWhere('code', 'stored_vs_live');
        $this->assertNotNull($item);
        $this->assertSame('critical', $item['severity']);
        $this->assertStringContainsString('total_sales stored 100,000, live 140,000, Δ 40,000', $item['message']);
    }

    public function test_report_checks_cash_variance_on_closed_session(): void
    {
        $this->seedScenario();
        $this->closeSeededSession(126000);

        $item = collect($this->service()->getReportChecks($this->shopId, self::DAY, self::DAY))->firstWhere('code', 'cash_variance');
        $this->assertNotNull($item);
        $this->assertSame(-1000, $item['amount']);
    }

    public function test_report_checks_payments_mismatch_after_deleting_a_payment(): void
    {
        $this->seedScenario();
        DB::table('sale_payments')->where('sale_id', $this->saleIds['A'])->delete();

        $item = collect($this->service()->getReportChecks($this->shopId, self::DAY, self::DAY))->firstWhere('code', 'payments_mismatch');
        $this->assertNotNull($item);
        $this->assertSame('critical', $item['severity']);
        $this->assertStringContainsString(DB::table('sales')->where('id', $this->saleIds['A'])->value('sale_number'), $item['message']);
    }

    /**
     * Query count for computeRangeSummary(), single shop, single day.
     * Before EXT_01: 16. After: +2 (owner_withdrawals, returns); the per-method
     * splits, category totals and the extra user-name joins add no queries.
     */
    public function test_query_count_single_shop_single_day(): void
    {
        $this->seedScenario();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->service()->computeRangeSummary($this->shopId, self::DAY, self::DAY);
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(19, $count, 'query count: 16 originally, +2 for EXT_01, +1 for unassigned_receivables');
    }
}
