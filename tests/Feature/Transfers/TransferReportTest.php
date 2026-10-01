<?php

namespace Tests\Feature\Transfers;

use App\Livewire\Transfers\TransferDetail;
use App\Models\Shop;
use App\Models\TransferBox;
use App\Models\Transporter;
use App\Models\User;
use App\Services\Analytics\TransferAnalyticsService;
use App\Services\Inventory\TransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/** Transfer process phase 7: transporter performance, the history card, the closed step. */
class TransferReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_transporter_performance_and_history(): void
    {
        $u = substr(uniqid(), -8);
        $now = now();
        $wh = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => "W$u", 'created_at' => $now, 'updated_at' => $now]);
        $shop = Shop::forceCreate(['name' => "Shop $u", 'code' => "S$u", 'is_active' => true, 'default_warehouse_id' => $wh, 'sells_all_categories' => true]);
        $cat = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => "C$u", 'created_at' => $now, 'updated_at' => $now]);
        $barcode = '88' . random_int(10000000000, 99999999999);
        $product = DB::table('products')->insertGetId([
            'category_id' => $cat, 'sku' => "SKU$u", 'name' => "Mule $u", 'barcode' => $barcode, 'items_per_box' => 10, 'is_active' => true,
            'purchase_price' => 1000, 'selling_price' => 3000, 'box_selling_price' => 28000, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $mk = fn ($role, $type = null, $id = null) => User::forceCreate(['name' => ucfirst($role), 'email' => $role . uniqid() . '@example.test',
            'password' => 'x', 'is_active' => true, 'must_change_password' => false, 'role' => $role, 'location_type' => $type, 'location_id' => $id]);
        $owner = $mk('owner');
        $whMgr = $mk('warehouse_manager', 'warehouse', $wh);
        $shopMgr = $mk('shop_manager', 'shop', $shop->id);
        foreach (range(1, 2) as $i) {
            DB::table('boxes')->insert(['product_id' => $product, 'box_code' => 'RPT' . uniqid() . $i, 'items_total' => 10, 'items_remaining' => 10,
                'status' => 'full', 'location_type' => 'warehouse', 'location_id' => $wh, 'received_by' => $owner->id, 'received_at' => $now,
                'created_at' => $now, 'updated_at' => $now]);
        }
        $transporter = Transporter::create(['name' => "Swift $u", 'phone' => '', 'is_active' => true]);
        $svc = app(TransferService::class);

        $this->actingAs($shopMgr);
        $t = $svc->createTransferRequest(['from_warehouse_id' => $wh, 'to_shop_id' => $shop->id, 'items' => [['product_id' => $product, 'quantity' => 2]]]);
        $this->actingAs($whMgr);
        $svc->approveTransfer($t);
        $svc->packBoxesByProductBarcode($t->fresh(), $barcode, 2);
        $svc->finishPacking($t->fresh());
        $svc->dispatch($t->fresh(), $transporter->id, ['handed_to_name' => 'Driver', 'expected_arrival_at' => now()->addHours(4)]);
        $boxes = TransferBox::where('transfer_id', $t->id)->orderBy('id')->pluck('box_id');
        $this->actingAs($shopMgr);
        $svc->receiveTransfer($t->fresh(), [['box_id' => $boxes[0]]]);    // arrives now: on time; second box missing
        $this->actingAs($whMgr);
        $svc->resolveBox($t->fresh(), $boxes[1], 'lost', 'Fell off the truck');

        Cache::flush();
        $day = business_today()->toDateString();
        $perf = app(TransferAnalyticsService::class)->getTransporterPerformance($day, $day);
        $row = collect($perf['transporters'])->firstWhere('name', "Swift $u");
        $this->assertSame(['transfers' => 1, 'boxes' => 2, 'on_time_pct' => 100.0, 'damaged' => 0, 'lost' => 1, 'loss_pct' => 50.0],
            array_intersect_key($row, array_flip(['transfers', 'boxes', 'on_time_pct', 'damaged', 'lost', 'loss_pct'])));

        $t->refresh();
        $this->assertSame('done', collect($t->timeline())->firstWhere('key', 'closed')['state']);

        Livewire::actingAs($owner)->test(TransferDetail::class, ['transfer' => $t])
            ->assertSee('History')
            ->assertSee('Dispatched to Driver')
            ->assertSee(TransferBox::where('box_id', $boxes[1])->first()->box->box_code . ': lost in transit')
            ->assertSee('Closed — all boxes accounted for');
    }
}
