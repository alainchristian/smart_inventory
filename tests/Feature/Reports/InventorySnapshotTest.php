<?php

namespace Tests\Feature\Reports;

use App\Livewire\Owner\Reports\DailyReport as OwnerDailyReport;
use App\Livewire\Shop\Reports\DailyReport as ShopDailyReport;
use App\Models\User;
use App\Services\Analytics\InventoryAnalyticsService;
use App\Services\DayClose\DailySessionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Daily Report inventory snapshot + the register rule added with it.
 */
class InventorySnapshotTest extends TestCase
{
    use DatabaseTransactions;

    private int $shop;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $u = uniqid();
        $this->shop = DB::table('shops')->insertGetId([
            'name' => "Snap $u", 'code' => 'N' . substr($u, -8), 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->manager = User::forceCreate([
            'name' => 'Snap Manager', 'email' => "sm$u@example.test", 'password' => bcrypt('x'),
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shop, 'is_active' => true,
        ]);
    }

    private function product(int $sell, int $cost): int
    {
        $u = uniqid();
        $cat = DB::table('categories')->insertGetId(['name' => "Snap cat $u", 'code' => 'SC-' . $u, 'created_at' => now(), 'updated_at' => now()]);

        return DB::table('products')->insertGetId([
            'category_id' => $cat, 'name' => "Snap product $u", 'sku' => 'SN-' . $u, 'items_per_box' => 10,
            'selling_price' => $sell, 'purchase_price' => $cost, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function box(int $product, string $status, int $items): void
    {
        DB::table('boxes')->insert([
            'box_code' => 'SNB-' . uniqid(), 'product_id' => $product, 'status' => $status,
            'items_total' => 10, 'items_remaining' => $items,
            'location_type' => 'shop', 'location_id' => $this->shop,
            'received_by' => $this->manager->id, 'received_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_snapshot_counts_full_and_opened_boxes_and_values_them(): void
    {
        $p = $this->product(1000, 700);
        $this->box($p, 'full', 10);
        $this->box($p, 'full', 10);
        $this->box($p, 'partial', 4);   // opened for a loose sale — ordinary stock
        $this->box($p, 'damaged', 10);  // counted as damaged, not in value
        $this->box($p, 'in_transit', 10);

        $snap = app(InventoryAnalyticsService::class)->getStockSnapshot($this->shop, true);
        $t = $snap['totals'];

        $this->assertSame([2, 1, 20, 4, 24], [$t['full_boxes'], $t['opened_boxes'], $t['full_items'], $t['opened_items'], $t['items']]);
        $this->assertSame([1, 1], [$t['damaged_boxes'], $t['in_transit_boxes']]);
        $this->assertSame(24000, $t['retail_value']);
        $this->assertSame(16800, $t['cost_value']);
        $this->assertSame(7200, $t['margin_value']);

        // no cost for anyone but the owner
        $noCost = app(InventoryAnalyticsService::class)->getStockSnapshot($this->shop, false)['totals'];
        $this->assertNull($noCost['cost_value']);
        $this->assertNull($noCost['margin_value']);
    }

    public function test_shop_report_shows_snapshot_without_cost(): void
    {
        $p = $this->product(1000, 700);
        $this->box($p, 'full', 10);

        Livewire::actingAs($this->manager)->test(ShopDailyReport::class)
            ->assertSee('Inventory Snapshot')
            ->assertSee('10,000')
            ->assertDontSee('At cost')
            ->assertDontSee('Potential margin');
    }

    public function test_owner_report_shows_snapshot_by_location(): void
    {
        $owner = User::forceCreate([
            'name' => 'Snap Owner', 'email' => 'so' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'role' => 'owner', 'is_active' => true,
        ]);
        $this->box($this->product(1000, 700), 'full', 10);

        Livewire::actingAs($owner)->test(OwnerDailyReport::class)
            ->assertSee('Inventory Snapshot')
            ->assertSee('By location')
            ->assertDontSee('cost 7,000')
            ->call('toggleProfit')
            ->assertSee('cost 7,000');
    }

    public function test_print_and_pdf_include_snapshot_with_cost_only_for_owner_profit(): void
    {
        $owner = User::forceCreate([
            'name' => 'Snap Owner', 'email' => 'so' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'role' => 'owner', 'is_active' => true,
        ]);
        $this->box($this->product(1000, 700), 'full', 10);
        $q = ['date_from' => '2021-09-01', 'date_to' => '2021-09-01', 'shop' => 'shop:' . $this->shop];

        $this->actingAs($owner)->get(route('owner.reports.daily.print', $q))
            ->assertOk()->assertSee('Inventory Snapshot')->assertSee('10,000')->assertDontSee('cost 7,000');
        $this->actingAs($owner)->get(route('owner.reports.daily.print', $q + ['profit' => 1]))
            ->assertOk()->assertSee('cost 7,000');

        $shopPrint = $this->actingAs($this->manager)->get(route('shop.reports.daily.print', ['date_from' => '2021-09-01', 'date_to' => '2021-09-01']));
        $shopPrint->assertOk()->assertSee('Inventory Snapshot')->assertSee('10,000')->assertDontSee('cost 7,000');
        $this->assertNull($shopPrint->viewData('inventorySnapshot')['totals']['cost_value']);

        $this->actingAs($this->manager)->get(route('shop.reports.daily.pdf', ['date_from' => '2021-09-01', 'date_to' => '2021-09-01']))
            ->assertOk();
        // same data the PDF controller renders; the PDF bytes themselves are compressed
        $html = view('pdf.daily-report', $this->pdfData($shopPrint))->render();
        $this->assertStringContainsString('Inventory snapshot', $html);
    }

    private function pdfData($res): array
    {
        return collect(['summary', 'cashRegister', 'position', 'shop', 'dateFrom', 'dateTo', 'viewMode',
            'reconciliation', 'comparison', 'checks', 'generatedBy', 'inventorySnapshot'])
            ->mapWithKeys(fn ($k) => [$k => $res->viewData($k)])->all();
    }

    public function test_register_cannot_open_while_an_earlier_day_is_open(): void
    {
        $svc = app(DailySessionService::class);
        $svc->openSession($this->manager, $this->shop, 50000, '2021-09-01');

        $this->expectExceptionMessage('is still open. Close it first');
        $svc->openSession($this->manager, $this->shop, 50000, '2021-09-02');
    }
}
