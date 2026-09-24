<?php

namespace Tests\Feature\Reports;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Daily Report PDF download (EXT_03) + regression check that the browser-print
 * routes still return the same views after buildReportData() was extracted.
 *
 * Like DailyReportDataTest: runs against the configured Postgres DB inside
 * DatabaseTransactions (never RefreshDatabase). Everything is created under a
 * brand-new shop on a far-past date.
 */
class DailyReportPdfTest extends TestCase
{
    use DatabaseTransactions;

    private const DAY = '2020-03-10';

    private int $shopId;
    private int $otherShopId;
    private string $shopName;
    private User $owner;
    private User $manager;

    private function insert(string $table, array $row): int
    {
        $now = now()->toDateTimeString();

        return DB::table($table)->insertGetId($row + ['created_at' => $now, 'updated_at' => $now]);
    }

    private function makeUser(string $role, ?int $shopId): User
    {
        $u = uniqid();
        $id = $this->insert('users', [
            'name'          => ucfirst($role) . " $u",
            'email'         => "$role$u@example.test",
            'password'      => 'x',
            'role'          => $role,
            'is_active'     => true,
            'location_type' => $shopId ? 'shop' : null,
            'location_id'   => $shopId,
        ]);

        return User::findOrFail($id);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $u = uniqid();
        $this->shopName    = "Pdf Shop $u";
        $this->shopId      = $this->insert('shops', ['name' => $this->shopName, 'code' => 'P' . substr($u, -8), 'is_active' => true]);
        $this->otherShopId = $this->insert('shops', ['name' => "Other Shop $u", 'code' => 'O' . substr($u, -8), 'is_active' => true]);
        $this->owner       = $this->makeUser('owner', null);
        $this->manager     = $this->makeUser('shop_manager', $this->shopId);
    }

    private function ownerPdf(array $params)
    {
        return $this->actingAs($this->owner)->get(route('owner.reports.daily.pdf', $params));
    }

    // ── 1. Owner access ────────────────────────────────────────────────

    public function test_owner_all_shops_single_day_downloads_pdf(): void
    {
        $res = $this->ownerPdf(['date_from' => self::DAY, 'date_to' => self::DAY, 'shop' => 'all', 'view' => 'summary']);

        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('content-type'));
        $this->assertStringContainsString('all-shops-' . self::DAY, $res->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $res->getContent());
    }

    public function test_owner_one_shop_range_transactions_within_31_days(): void
    {
        $res = $this->ownerPdf([
            'date_from' => '2020-03-01', 'date_to' => '2020-03-31',
            'shop' => 'shop:' . $this->shopId, 'view' => 'transactions',
        ]);

        $res->assertOk();
        $this->assertStringContainsString(Str::slug($this->shopName) . '-2020-03-01_to_2020-03-31', $res->headers->get('content-disposition'));
    }

    public function test_transactions_over_31_days_is_a_validation_error(): void
    {
        $res = $this->ownerPdf(['date_from' => '2020-01-01', 'date_to' => '2020-02-04', 'shop' => 'all', 'view' => 'transactions']);

        $res->assertStatus(302);
        $res->assertSessionHasErrors('view');
        $this->assertStringContainsString('limited to 31 days', session('errors')->first('view'));
    }

    public function test_summary_over_31_days_is_allowed(): void
    {
        $this->ownerPdf(['date_from' => '2020-01-01', 'date_to' => '2020-03-31', 'shop' => 'all', 'view' => 'summary'])->assertOk();
    }

    public function test_unknown_shop_id_is_404(): void
    {
        $this->ownerPdf(['date_from' => self::DAY, 'date_to' => self::DAY, 'shop' => 'shop:999999'])->assertNotFound();
    }

    public function test_malformed_shop_param_is_a_validation_error(): void
    {
        $res = $this->ownerPdf(['date_from' => self::DAY, 'date_to' => self::DAY, 'shop' => 'abc']);

        $res->assertStatus(302);
        $res->assertSessionHasErrors('shop');
    }

    // ── 2. Shop manager access ─────────────────────────────────────────

    public function test_shop_manager_gets_own_shop_and_ignores_shop_param(): void
    {
        $res = $this->actingAs($this->manager)->get(route('shop.reports.daily.pdf', [
            'date_from' => self::DAY, 'date_to' => self::DAY, 'view' => 'summary',
            'shop' => 'shop:' . $this->otherShopId,
        ]));

        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('content-type'));
        $disposition = $res->headers->get('content-disposition');
        $this->assertStringContainsString(Str::slug($this->shopName) . '-' . self::DAY, $disposition);
        $this->assertStringNotContainsString('other-shop', $disposition);
    }

    public function test_shop_manager_cannot_use_the_owner_pdf_route(): void
    {
        $this->actingAs($this->manager)
            ->get(route('owner.reports.daily.pdf', ['date_from' => self::DAY, 'date_to' => self::DAY]))
            ->assertForbidden();
    }

    // ── 3. Logging ─────────────────────────────────────────────────────

    public function test_download_is_logged(): void
    {
        $this->ownerPdf(['date_from' => self::DAY, 'date_to' => self::DAY, 'shop' => 'shop:' . $this->shopId, 'view' => 'summary'])->assertOk();

        $log = ActivityLog::where('action', 'report_pdf_downloaded')->where('user_id', $this->owner->id)->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame('DailyReport', $log->entity_type);
        $this->assertSame(self::DAY, $log->details['date_from']);
        $this->assertSame($this->shopId, $log->details['shop_id']);
        $this->assertSame('summary', $log->details['view']);
        $this->assertFalse($log->details['provisional']);
    }

    // ── 4. Print unchanged ─────────────────────────────────────────────

    public function test_owner_print_route_still_renders_the_same_view(): void
    {
        $res = $this->actingAs($this->owner)->get(route('owner.reports.daily.print', [
            'date_from' => self::DAY, 'date_to' => self::DAY, 'shop' => 'all', 'view' => 'summary',
        ]));

        $res->assertOk();
        $res->assertViewIs('owner.reports.daily-print');
        foreach (['summary', 'cashRegister', 'position', 'isAllShops', 'shopName', 'dateFrom', 'dateTo', 'viewMode'] as $key) {
            $res->assertViewHas($key);
        }
        $res->assertDontSee('Cash reconciliation');
    }

    public function test_shop_print_route_still_renders_the_same_view(): void
    {
        $res = $this->actingAs($this->manager)->get(route('shop.reports.daily.print', [
            'date_from' => self::DAY, 'date_to' => self::DAY, 'view' => 'summary',
        ]));

        $res->assertOk();
        $res->assertViewIs('shop.reports.daily-print');
        foreach (['summary', 'cashRegister', 'position', 'shop', 'dateFrom', 'dateTo', 'viewMode'] as $key) {
            $res->assertViewHas($key);
        }
        $res->assertDontSee('Cash reconciliation');
    }

    // ── 5. Profit visibility ───────────────────────────────────────────

    public function test_owner_print_hides_profit_unless_requested(): void
    {
        $params = ['date_from' => self::DAY, 'date_to' => self::DAY, 'shop' => 'all', 'view' => 'summary'];

        $this->actingAs($this->owner)->get(route('owner.reports.daily.print', $params))
            ->assertOk()->assertDontSee('Gross Profit')->assertDontSee('Net for Period')->assertDontSee('Cost of Goods')
            ->assertSee('Balance');

        $this->actingAs($this->owner)->get(route('owner.reports.daily.print', $params + ['profit' => 1]))
            ->assertOk()->assertViewHas('showProfit', true)->assertViewHas('profitByProduct');
    }

    public function test_shop_manager_never_gets_profit_even_if_asked(): void
    {
        $params = ['date_from' => self::DAY, 'date_to' => self::DAY, 'view' => 'summary', 'profit' => 1];

        $res = $this->actingAs($this->manager)->get(route('shop.reports.daily.print', $params));
        $res->assertOk()->assertDontSee('Gross Profit')->assertDontSee('Net for Period')->assertDontSee('Cost of Goods');
        $this->assertArrayNotHasKey('gross_profit', $res->viewData('summary'));
        $this->assertArrayNotHasKey('total_cogs', $res->viewData('summary'));
    }

    public function test_shop_screen_has_no_profit_and_owner_screen_toggles_it(): void
    {
        \Livewire\Livewire::actingAs($this->manager)->test(\App\Livewire\Shop\Reports\DailyReport::class)
            ->assertDontSee('Gross Profit')->assertDontSee('Net for Period')->assertDontSee('Show profit');

        \Livewire\Livewire::actingAs($this->owner)->test(\App\Livewire\Owner\Reports\DailyReport::class)
            ->assertDontSee('Gross Profit')->assertSee('Show profit')
            ->call('toggleProfit')->assertSet('showProfit', true)->assertSee('Hide profit')
            ->call('toggleProfit')->assertSet('showProfit', false)->assertSee('Show profit');
    }
}
