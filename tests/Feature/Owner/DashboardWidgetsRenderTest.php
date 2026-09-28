<?php

namespace Tests\Feature\Owner;

use App\Livewire\Dashboard\BusinessInsights;
use App\Livewire\Dashboard\BusinessKpiRow;
use App\Livewire\Dashboard\BusinessSnapshot;
use App\Livewire\Dashboard\ExpensesBreakdown;
use App\Livewire\Dashboard\RecentTransactions;
use App\Livewire\Dashboard\RevenueByCategory;
use App\Livewire\Dashboard\SalesPerformance;
use App\Livewire\Dashboard\TopPerformingShops;
use App\Livewire\Dashboard\TopShops;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Every owner-dashboard widget renders for every TimeFilter preset, before and
 * after the filter's first event. Added after the shared business-period trait
 * returned Carbon\Carbon to a widget type-hinting Illuminate\Support\Carbon —
 * a 500 on the dashboard that no test rendered.
 */
class DashboardWidgetsRenderTest extends TestCase
{
    use DatabaseTransactions;

    private const WIDGETS = [
        BusinessInsights::class, BusinessKpiRow::class, BusinessSnapshot::class,
        ExpensesBreakdown::class, RecentTransactions::class, RevenueByCategory::class,
        SalesPerformance::class, TopPerformingShops::class, TopShops::class,
    ];

    public function test_every_widget_renders_for_every_preset(): void
    {
        $owner = User::forceCreate([
            'name' => 'Widget Owner', 'email' => 'wo' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'role' => 'owner',
        ]);
        $today = business_today();
        $ranges = [
            'today'      => [$today, $today],
            'yesterday'  => [$today->copy()->subDay(), $today->copy()->subDay()],
            'week'       => [$today->copy()->startOfWeek(), $today],
            'month'      => [$today->copy()->startOfMonth(), $today],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'last_30'    => [$today->copy()->subDays(29), $today],
            'custom'     => [$today->copy()->subDays(90), $today],
        ];

        foreach (self::WIDGETS as $widget) {
            $c = Livewire::actingAs($owner)->test($widget);
            if ($widget === SalesPerformance::class) {
                $c->call('loadChart'); // wire:init in the dashboard
            }
            $c->assertOk();

            foreach ($ranges as $period => [$from, $to]) {
                $c->dispatch('time-filter-changed', period: $period, from: $from->toDateString(), to: $to->toDateString())
                  ->assertOk();
            }
        }
    }

    public function test_owner_dashboard_page_loads(): void
    {
        $owner = User::forceCreate([
            'name' => 'Page Owner', 'email' => 'po' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'role' => 'owner', 'is_active' => true,
        ]);

        $this->actingAs($owner)->get(route('owner.dashboard'))->assertOk();
    }

    /**
     * The owner saw "yesterday: no sales but 150,000 spent" while the Daily
     * Report showed no expenses: session_date was compared with the UTC
     * bound's date (00:00 Kigali = 22:00 UTC the day before), pulling in the
     * previous register day. And the trend chart shifted days the same way.
     */
    public function test_expenses_and_trend_use_local_days(): void
    {
        $owner = User::forceCreate([
            'name' => 'Days Owner', 'email' => 'do' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'role' => 'owner', 'is_active' => true,
        ]);
        $tz   = config('tenant.timezone');
        $u    = uniqid();
        $shop = DB::table('shops')->insertGetId(['name' => "Days $u", 'code' => 'D' . substr($u, -8), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $cat  = DB::table('expense_categories')->insertGetId(['name' => "Days cat $u", 'applies_to' => 'both', 'is_active' => true, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()]);

        foreach (['2021-06-09' => 150000, '2021-06-10' => 50000] as $date => $amount) {
            $session = DB::table('daily_sessions')->insertGetId([
                'shop_id' => $shop, 'session_date' => $date, 'status' => 'closed', 'opened_by' => $owner->id,
                'opened_at' => now(), 'opening_balance' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('expenses')->insert([
                'daily_session_id' => $session, 'expense_category_id' => $cat, 'amount' => $amount, 'description' => 'x',
                'payment_method' => 'cash', 'recorded_by' => $owner->id, 'recorded_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // 01:30 on 10 June in Kigali = 23:30 UTC on 9 June
        $at = Carbon::parse('2021-06-10 01:30', $tz)->utc();
        DB::table('sales')->insert([
            'sale_number' => 'DAYS-' . $u, 'shop_id' => $shop, 'sold_by' => $owner->id, 'sale_date' => $at,
            'type' => 'full_box', 'payment_method' => 'cash', 'subtotal' => 7000, 'total' => 7000, 'created_at' => $at, 'updated_at' => $at,
        ]);

        $day = fn ($c, $from, $to) => $c->dispatch('time-filter-changed', period: 'custom', from: $from, to: $to);

        // Expenses: only the 10th's register
        $kpi = Livewire::actingAs($owner)->test(BusinessKpiRow::class);
        $day($kpi, '2021-06-10', '2021-06-10');
        $this->assertSame(50000, $kpi->get('expenses')['current']);

        // Sparkline over 30 days ending on the sale's day must include it — it used to
        // keep only the first 14 daily buckets and drop the recent half of the period
        $day($kpi, '2021-05-12', '2021-06-10');
        $spark = $kpi->get('salesSparkline');
        $this->assertLessThanOrEqual(14, count($spark));
        $this->assertEquals(7000, end($spark));

        // Expenses Breakdown legend shows category names (it read a key the service doesn't send)
        $eb = Livewire::actingAs($owner)->test(ExpensesBreakdown::class);
        $day($eb, '2021-06-09', '2021-06-10');
        $this->assertSame("Days cat $u", $eb->get('categories')[0]['name']);

        // Trend, daily: the sale lands on Jun 10, not Jun 9
        $chart = Livewire::actingAs($owner)->test(SalesPerformance::class);
        $day($chart, '2021-06-09', '2021-06-10');
        $data = $chart->get('chartData');
        $this->assertSame(['Jun 9', 'Jun 10'], $data['labels']);
        $this->assertEquals([0, 7000], $data['revenueData']);
        $this->assertEquals([0 - 150000, 0 - 50000], $data['netData']); // no cost price rows → profit 0

        // Single day: hourly, the sale at 01:00
        $day($chart, '2021-06-10', '2021-06-10');
        $data = $chart->get('chartData');
        $this->assertCount(24, $data['labels']);
        $this->assertEquals(7000, $data['revenueData'][1]);

        // Weekly and monthly follow the period too
        $chart->call('setChartPeriod', 'monthly');
        $day($chart, '2021-05-01', '2021-06-30');
        $this->assertSame(['May 2021', 'Jun 2021'], $chart->get('chartData')['labels']);
        $this->assertEquals([0, 7000], $chart->get('chartData')['revenueData']);
    }
}
