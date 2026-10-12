<?php

namespace Tests\Feature\Shop;

use App\Livewire\Shop\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Shop dashboard: the Sales Trend chart and the KPI sparklines tell the same
 * story, on Kigali (not UTC) hours and days. Runs on smart_inventory_test only.
 */
class DashboardTrendTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private User $manager;
    private string $tz;

    protected function setUp(): void
    {
        parent::setUp();
        $u        = uniqid();
        $this->tz = config('tenant.timezone');
        $now      = now()->toDateTimeString();

        $this->shopId = DB::table('shops')->insertGetId([
            'name' => "Trend Shop $u", 'code' => 'T' . substr($u, -8), 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->manager = User::forceCreate([
            'name' => 'Trend Tester', 'email' => "trend$u@example.test", 'password' => 'x', 'is_active' => true,
            'must_change_password' => false, 'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopId,
        ]);

        // "Now" is 18:30 in Kigali on 10 June 2021. Set as a UTC instant, like the
        // server's clock: a Kigali-zoned test clock makes Carbon read DB times as Kigali.
        Carbon::setTestNow(Carbon::parse('2021-06-10 18:30', $this->tz)->utc());

        $this->sale('2021-06-10 00:30', 1000);       // 22:30 UTC on the 9th — still the 10th locally
        $this->sale('2021-06-10 09:15', 2000);
        $this->sale('2021-06-10 16:25', 1900000);
        $this->sale('2021-06-09 10:00', 500);        // the day before
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function sale(string $localTime, int $total): void
    {
        $at = Carbon::parse($localTime, $this->tz)->utc();
        DB::table('sales')->insert([
            'sale_number' => 'TR-' . uniqid(), 'shop_id' => $this->shopId, 'sold_by' => $this->manager->id,
            'sale_date' => $at, 'type' => 'full_box', 'payment_method' => 'cash',
            'subtotal' => $total, 'total' => $total, 'created_at' => $at, 'updated_at' => $at,
        ]);
    }

    public function test_a_single_day_chart_shows_each_sale_at_its_local_time(): void
    {
        $c = Livewire::actingAs($this->manager)->test(Dashboard::class)
            ->set('dateFrom', '2021-06-10')->set('dateTo', '2021-06-10');

        // One point per sale, at Kigali time (00:30 local is 22:30 UTC the day before)
        $this->assertSame(['12:30 AM', '9:15 AM', '4:25 PM'], $c->viewData('trendLabels'));
        $this->assertEquals([1000, 2000, 1900000], $c->viewData('trendCurrent'));
        $this->assertSame([], $c->viewData('trendPrev'));
    }

    public function test_single_day_sparklines_are_not_running_totals(): void
    {
        $c = Livewire::actingAs($this->manager)->test(Dashboard::class)
            ->set('dateFrom', '2021-06-10')->set('dateTo', '2021-06-10');

        // Total Sales: one point per sale, like the chart
        $this->assertEquals([1000, 2000, 1900000], $c->viewData('sparklineSales'));

        // Transactions: sales in each hour, 00:00 to now (18:30) — not cumulative
        $txns = $c->viewData('sparklineTxns');
        $this->assertCount(19, $txns);
        $this->assertSame(1, $txns[0]);     // 00:30
        $this->assertSame(1, $txns[9]);     // 09:15
        $this->assertSame(1, $txns[16]);    // 16:25
        $this->assertSame(3, array_sum($txns));
    }

    public function test_several_days_are_grouped_by_kigali_day(): void
    {
        $c = Livewire::actingAs($this->manager)->test(Dashboard::class)
            ->set('dateFrom', '2021-06-08')->set('dateTo', '2021-06-10');

        // Labels used to start a day early, and the last day's sales went missing
        $this->assertSame(['Jun 8', 'Jun 9', 'Jun 10'], $c->viewData('trendLabels'));
        $this->assertEquals([0, 500, 1903000], $c->viewData('trendCurrent'));
        $this->assertEquals(1903500, array_sum($c->viewData('sparklineSales')));
    }

    public function test_today_is_compared_with_yesterday_not_the_day_before(): void
    {
        $this->sale('2021-06-08 12:00', 999999);     // the day before yesterday — must not count

        // 1,903,000 today vs 500 yesterday — against the 999,999 day it would be ~90%
        Livewire::actingAs($this->manager)->test(Dashboard::class)
            ->set('dateFrom', '2021-06-10')->set('dateTo', '2021-06-10')
            ->assertViewHas('salesChange', fn ($pct) => $pct > 1000);
    }
}
