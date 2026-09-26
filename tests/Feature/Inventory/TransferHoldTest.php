<?php

namespace Tests\Feature\Inventory;

use App\Enums\BoxStatus;
use App\Models\Box;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\TransferBox;
use App\Models\User;
use App\Services\Inventory\TransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Packed transfer boxes are held (box status in_transit) so the warehouse
 * can't sell them or pack them onto another transfer before the shop
 * receives them. Runs on smart_inventory_test only.
 */
class TransferHoldTest extends TestCase
{
    use DatabaseTransactions;

    private int $warehouseId;
    private Shop $shop;
    private int $productId;
    private string $barcode;
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
        $this->barcode = '99' . random_int(10000000000, 99999999999);
        $categoryId = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => "C$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId, 'sku' => "SKU$u", 'name' => "Runner $u", 'barcode' => $this->barcode, 'items_per_box' => 10, 'is_active' => true,
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
    }

    private function box(string $status = 'full', int $remaining = 10): int
    {
        return DB::table('boxes')->insertGetId([
            'product_id' => $this->productId, 'box_code' => 'B' . uniqid(), 'items_total' => 10, 'items_remaining' => $remaining,
            'status' => $status, 'location_type' => 'warehouse', 'location_id' => $this->warehouseId,
            'received_by' => $this->owner->id, 'received_at' => now()->subDay(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** Request → approve → pack $boxes boxes; returns the transfer. */
    private function packed(int $boxes): Transfer
    {
        $this->actingAs($this->shopMgr);
        $t = $this->svc->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id,
            'items' => [['product_id' => $this->productId, 'quantity' => $boxes]],
        ]);
        $this->actingAs($this->owner);
        $this->svc->approveTransfer($t);
        $this->actingAs($this->whMgr);
        $this->svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, $boxes);

        return $t->fresh();
    }

    private function boxStatus(int $boxId): string
    {
        return DB::table('boxes')->where('id', $boxId)->value('status');
    }

    public function test_packing_takes_boxes_off_sale_and_away_from_other_transfers(): void
    {
        $opened = $this->box('partial', 4);
        $sealed = $this->box();
        $spare  = $this->box();

        $this->packed(2);   // oldest first: $opened, $sealed

        $this->assertSame('in_transit', $this->boxStatus($opened));
        $this->assertSame('in_transit', $this->boxStatus($sealed));
        $this->assertSame('full', $this->boxStatus($spare));
        $this->assertSame('partial', TransferBox::where('box_id', $opened)->value('box_status_before'));
        $this->assertSame(1, Box::available()->where('product_id', $this->productId)->count(), 'only the spare box is sellable');

        // A second transfer can't grab the held boxes
        $this->expectExceptionMessage('Only 1 box(es) available');
        $this->packed(2);
    }

    public function test_receipt_puts_boxes_on_sale_at_the_shop_and_returns_missing_ones(): void
    {
        $opened = $this->box('partial', 4);
        $sealed = $this->box();
        $sealed2 = $this->box();
        $t = $this->packed(3);

        $this->actingAs($this->whMgr);
        $this->svc->markAsShipped($t);
        $this->svc->markAsDelivered($t->fresh());

        // Shop only finds the opened box; both sealed ones never arrived
        $this->actingAs($this->shopMgr);
        $this->svc->receiveTransfer($t->fresh(), [['box_id' => $opened]]);

        $boxA = Box::find($opened);
        $this->assertSame('shop', $boxA->location_type->value);
        $this->assertSame(BoxStatus::PARTIAL, $boxA->status, 'status before packing restored');

        $boxB = Box::find($sealed);
        $this->assertSame('warehouse', $boxB->location_type->value);
        $this->assertSame(BoxStatus::FULL, $boxB->status, 'missing box goes back on sale at the warehouse');
        $this->assertSame('full', $this->boxStatus($sealed2), 'every missing box, not just the first');
        $this->assertTrue($t->fresh()->has_discrepancy);
    }

    public function test_damaged_on_arrival_stays_off_sale(): void
    {
        $box = $this->box();
        $t = $this->packed(1);
        $this->actingAs($this->whMgr);
        $this->svc->markAsShipped($t);
        $this->svc->markAsDelivered($t->fresh());
        $this->actingAs($this->shopMgr);
        $this->svc->receiveTransfer($t->fresh(), [['box_id' => $box, 'is_damaged' => true]]);

        $this->assertSame('damaged', $this->boxStatus($box));
    }

    public function test_migration_holds_boxes_on_transfers_already_packed(): void
    {
        $inFlight = $this->box('partial', 6);
        $delivered = $this->box();
        $t = $this->packed(2);
        $receivedTransferBox = $this->box();   // on an older, received transfer — must stay as is

        // Recreate the pre-migration state (Postgres DDL is transactional, so this rolls back)
        \Illuminate\Support\Facades\Schema::table('transfer_boxes', fn ($tbl) => $tbl->dropColumn('box_status_before'));
        DB::table('boxes')->whereIn('id', [$inFlight, $delivered])->update(['status' => DB::raw("CASE WHEN items_remaining < items_total THEN 'partial'::box_status ELSE 'full'::box_status END")]);
        $old = Transfer::forceCreate([
            'transfer_number' => 'OLD-' . uniqid(), 'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id,
            'status' => 'received', 'requested_by' => $this->owner->id, 'requested_at' => now(),
        ]);
        DB::table('transfer_boxes')->insert(['transfer_id' => $old->id, 'box_id' => $receivedTransferBox, 'is_received' => false, 'created_at' => now(), 'updated_at' => now()]);

        (require base_path('database/migrations/2026_09_26_000001_hold_packed_transfer_boxes.php'))->up();

        $this->assertSame('in_transit', $this->boxStatus($inFlight));
        $this->assertSame('in_transit', $this->boxStatus($delivered));
        $this->assertSame('partial', DB::table('transfer_boxes')->where('transfer_id', $t->id)->where('box_id', $inFlight)->value('box_status_before'));
        $this->assertSame('full', $this->boxStatus($receivedTransferBox));
    }

    public function test_cancel_puts_packed_boxes_back_on_sale(): void
    {
        $opened = $this->box('partial', 4);
        $sealed = $this->box();
        $t = $this->packed(2);

        $this->actingAs($this->owner);
        $this->svc->cancelTransfer($t, 'Shop closed');

        $this->assertSame('partial', $this->boxStatus($opened));
        $this->assertSame('full', $this->boxStatus($sealed));
        $this->assertSame(0, TransferBox::where('transfer_id', $t->id)->count());
    }
}
