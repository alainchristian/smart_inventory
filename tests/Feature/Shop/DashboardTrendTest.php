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

        // "Now" is 18:30 in Kigali on 10 June 2021
        Carbon::setTestNow(Carbon::parse('2021-06-10 18:30', $this->tz));

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

    public function test_a_single_day_is_a_running_total_by_hour_up_to_now(): void
    {
        $c = Livewire::actingAs($this->manager)->test(Dashboard::class)
            ->set('dateFrom', '2021-06-10')->set('dateTo', '2021-06-10');

        $labels  = $c->viewData('trendLabels');
        $current = $c->viewData('trendCurrent');
        $prev    = $c->viewData('trendPrev');

        // Points are "total sold by": from 00:00 (a sale at 00:30), every hour's
        // end, and the last point is now (18:30) — nothing after now
        $this->assertSame('12 AM', $labels[0]);
        $this->assertSame('1 AM', $labels[1]);
        $this->assertSame('6 PM', $labels[18]);
        $this->assertSame('6:30 PM', end($labels));
        $this->assertCount(20, $labels);

        // Running total: starts at 0, never goes down, ends on the day's total
        $this->assertEquals(0, $current[0]);
        $this->assertEquals(1000, $current[1]);           // the 00:30 sale, by 1 AM
        $this->assertEquals(3000, $current[10]);          // + the 09:15 sale, by 10 AM
        $this->assertEquals(1903000, end($current));
        $this->assertSame($current, array_values(collect($current)->sort()->all()));

        // Dashed line: the previous day's running total at the same hours
        $this->assertEquals(500, end($prev));

        // The cards' sparklines follow the same running totals
        $this->assertEquals($current, $c->viewData('sparklineSales'));
        $txns = $c->viewData('sparklineTxns');
        $this->assertEquals(3, end($txns));
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

        Livewire::actingAs($this->manager)->test(Dashboard::class)
            ->set('dateFrom', '2021-06-10')->set('dateTo', '2021-06-10')
            ->assertViewHas('trendPrev', fn ($p) => end($p) == 500);
    }
}
