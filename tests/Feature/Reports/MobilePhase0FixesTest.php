<?php

namespace Tests\Feature\Reports;

use App\Livewire\Dashboard\Concerns\ResolvesBusinessPeriod;
use App\Livewire\Dashboard\TopShops;
use App\Livewire\Owner\Products\ProductKpiRow;
use App\Livewire\Owner\Reports\PaymentMethodsReport;
use App\Livewire\Products\ProductList;
use App\Models\User;
use App\Services\Analytics\SalesAnalyticsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Bugs found in the owner mobile review (2026-09-28, phase 0).
 */
class MobilePhase0FixesTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $u = uniqid();
        $this->shopId = DB::table('shops')->insertGetId([
            'name' => "Phase0 $u", 'code' => 'P' . substr($u, -8), 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->owner = User::forceCreate([
            'name' => 'Phase0 Owner', 'email' => "p0$u@example.test", 'password' => bcrypt('x'), 'role' => 'owner',
        ]);
    }

    private function sale(Carbon $at, int $total): int
    {
        $id = DB::table('sales')->insertGetId([
            'sale_number' => 'P0-' . uniqid(), 'shop_id' => $this->shopId, 'sold_by' => $this->owner->id,
            'sale_date' => $at, 'type' => 'full_box', 'payment_method' => 'cash',
            'subtotal' => $total, 'total' => $total, 'created_at' => $at, 'updated_at' => $at,
        ]);
        DB::table('sale_payments')->insert([
            'sale_id' => $id, 'payment_method' => 'cash', 'amount' => $total, 'created_at' => $at, 'updated_at' => $at,
        ]);

        return $id;
    }

    public function test_payment_methods_report_shows_whole_amounts_and_includes_the_last_day(): void
    {
        $tz = config('tenant.timezone');
        // 23:30 local on 15 Mar 2021 — after 00:00 UTC, so a plain-date filter used to drop it
        $this->sale(Carbon::parse('2021-03-15 23:30', $tz)->utc(), 45000);
        // 01:00 local on 16 Mar — still the 15th in UTC, must NOT count for the 15th
        $this->sale(Carbon::parse('2021-03-16 01:00', $tz)->utc(), 7000);

        Livewire::actingAs($this->owner)->test(PaymentMethodsReport::class)
            ->set('locationFilter', 'shop:' . $this->shopId)
            ->set('dateFrom', '2021-03-15')->set('dateTo', '2021-03-15')
            ->assertSet('totalRevenue', 45000)
            ->assertSee('45,000')
            ->assertDontSee('450 RWF');
    }

    public function test_sales_by_hour_uses_business_hours(): void
    {
        $tz = config('tenant.timezone');
        $date = '2021-04-0' . random_int(1, 9);
        Cache::flush();
        $this->sale(Carbon::parse("$date 17:30", $tz)->utc(), 1000);

        $hours = app(SalesAnalyticsService::class)->getSalesByHour($date, $date, 'shop:' . $this->shopId);

        $this->assertSame(1, $hours[17]['count']);
        $this->assertSame(0, $hours[Carbon::parse("$date 17:30", $tz)->utc()->hour]['count'] ?? 0);
    }

    public function test_products_period_label_matches_the_default_today_filter(): void
    {
        Livewire::actingAs($this->owner)->test(ProductList::class)
            ->assertSet('period', 'today')
            ->assertViewHas('periodLabel', 'Today');

        Livewire::actingAs($this->owner)->test(ProductKpiRow::class)
            ->assertViewHas('periodLabel', 'Today')
            ->dispatch('time-filter-changed', period: 'last_30', from: '2021-01-01', to: '2021-01-30')
            ->assertViewHas('periodLabel', 'Last 30 Days');
    }

    public function test_dashboard_periods_are_business_days_in_utc(): void
    {
        $w = new class {
            use ResolvesBusinessPeriod;
            public string $period = 'today';
            public ?string $from = null;
            public ?string $to = null;
            public function range(): array { return $this->businessPeriodRange(); }
        };
        $tz = config('tenant.timezone');

        $w->from = '2021-05-10';
        $w->to   = '2021-05-10';
        [$start, $end] = $w->range();
        $this->assertTrue($start->equalTo(Carbon::parse('2021-05-10 00:00', $tz)));
        $this->assertTrue($end->equalTo(Carbon::parse('2021-05-10 23:59:59.999999', $tz)));
        $this->assertSame('UTC', $start->timezoneName);

        // Before TimeFilter's first event: today in the business timezone
        $w->from = $w->to = null;
        [$start] = $w->range();
        $this->assertTrue($start->equalTo(business_today()));
    }

    public function test_top_shops_defaults_to_today_like_the_filter(): void
    {
        Livewire::actingAs($this->owner)->test(TopShops::class)->assertSet('period', 'today');
    }
}
