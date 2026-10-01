<?php

namespace Tests\Feature\Transfers;

use App\Livewire\Layout\NotificationBell;
use App\Models\Alert;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\User;
use App\Services\Inventory\TransferService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/** Transfer process phase 6: who hears about which step, and overdue alerts. */
class TransferNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private int $warehouseId;
    private Shop $shop;
    private string $barcode;
    private int $productId;
    private User $owner;
    private User $whMgr;
    private User $shopMgr;
    private TransferService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $u = substr(uniqid(), -8);
        $now = now();

        $this->warehouseId = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => "W$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->shop = Shop::forceCreate(['name' => "Shop $u", 'code' => "S$u", 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => true]);
        $categoryId = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => "C$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->barcode = '89' . random_int(10000000000, 99999999999);
        $this->productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId, 'sku' => "SKU$u", 'name' => "Clog $u", 'barcode' => $this->barcode, 'items_per_box' => 10, 'is_active' => true,
            'purchase_price' => 1000, 'selling_price' => 3000, 'box_selling_price' => 28000, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $user = fn ($role, $type = null, $id = null) => User::forceCreate([
            'name' => ucfirst($role), 'email' => $role . uniqid() . '@example.test', 'password' => 'x', 'is_active' => true,
            'must_change_password' => false, 'role' => $role, 'location_type' => $type, 'location_id' => $id,
        ]);
        $this->owner = $user('owner');
        $this->whMgr = $user('warehouse_manager', 'warehouse', $this->warehouseId);
        $this->shopMgr = $user('shop_manager', 'shop', $this->shop->id);
        $this->svc = app(TransferService::class);
        foreach (range(1, 2) as $i) {
            DB::table('boxes')->insert([
                'product_id' => $this->productId, 'box_code' => 'NTF' . uniqid() . $i, 'items_total' => 10, 'items_remaining' => 10,
                'status' => 'full', 'location_type' => 'warehouse', 'location_id' => $this->warehouseId,
                'received_by' => $this->owner->id, 'received_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    private function approved(): Transfer
    {
        $this->actingAs($this->shopMgr);
        $t = $this->svc->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id,
            'items' => [['product_id' => $this->productId, 'quantity' => 1]],
        ]);
        $this->actingAs($this->owner);
        $this->svc->approveTransfer($t);

        return $t->fresh();
    }

    private function bell(User $user): array
    {
        return collect(Livewire::actingAs($user)->test(NotificationBell::class)->instance()->activityNotifications())
            ->pluck('label', 'subtitle')->all();
    }

    public function test_the_shop_hears_its_transfer_is_on_its_way_with_the_expected_time(): void
    {
        $t = $this->approved();
        $this->actingAs($this->whMgr);
        $this->svc->packBoxesByProductBarcode($t, $this->barcode, 1);
        $this->svc->finishPacking($t->fresh());
        $eta = now()->addHours(5);
        $this->svc->dispatch($t->fresh(), null, ['handed_to_name' => 'Jean Driver', 'expected_arrival_at' => $eta]);

        $shop = $this->bell($this->shopMgr);
        $this->assertContains('Transfer On Its Way', $shop);
        $this->assertContains('Transfer Approved', $shop);       // by the owner
        $this->assertArrayHasKey("{$t->transfer_number} · expected " . local_time($eta)->format('D H:i'), $shop);

        // The warehouse heard about the request and the owner's approval, not its own dispatch.
        $wh = $this->bell($this->whMgr);
        $this->assertContains('Transfer Requested', $wh);
        $this->assertContains('Transfer Approved', $wh);
        $this->assertNotContains('Transfer On Its Way', $wh);
    }

    public function test_the_warehouse_hears_about_cancellations_and_discrepancies(): void
    {
        $t = $this->approved();
        $this->actingAs($this->owner);
        $this->svc->cancelTransfer($t, 'Shop closing');

        $this->assertContains('Transfer Cancelled', $this->bell($this->whMgr));
        $this->assertContains('Transfer Cancelled', $this->bell($this->shopMgr));
    }

    public function test_overdue_alerts_are_raised_and_cleared_when_the_transfer_moves_on(): void
    {
        app(SettingsService::class)->set('transfer_alert_pack_hours', 6);
        $t = $this->approved();
        DB::table('transfers')->where('id', $t->id)->update(['reviewed_at' => now()->subHours(7)]);

        Artisan::call('alerts:generate');
        $alert = Alert::where('entity_id', $t->id)->where('title', 'Transfer Not Packed')->firstOrFail();
        $this->assertNull($alert->resolved_at);
        $this->assertSame(route('owner.transfers.show', $t), $alert->action_url);

        Artisan::call('alerts:generate');   // no duplicates
        $this->assertSame(1, Alert::where('entity_id', $t->id)->where('title', 'Transfer Not Packed')->count());

        // Packing done (approved → ready) clears it.
        $this->actingAs($this->whMgr);
        $this->svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, 1);
        $this->svc->finishPacking($t->fresh());
        $this->assertNotNull($alert->fresh()->resolved_at);

        // Dispatched with an arrival time that has passed → overdue in transit.
        $this->svc->dispatch($t->fresh(), null, ['handed_to_name' => 'Jean', 'expected_arrival_at' => now()->subHour()]);
        Artisan::call('alerts:generate');
        $overdue = Alert::where('entity_id', $t->id)->where('title', 'Transfer Overdue in Transit')->firstOrFail();
        $this->assertSame('critical', $overdue->severity->value);

        $this->actingAs($this->shopMgr);
        $this->svc->markAsDelivered($t->fresh());
        $this->assertNotNull($overdue->fresh()->resolved_at);
    }
}
