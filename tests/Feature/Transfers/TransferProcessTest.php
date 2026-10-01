<?php

namespace Tests\Feature\Transfers;

use App\Enums\TransferStatus;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\TransferBox;
use App\Models\User;
use App\Services\Inventory\TransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The transfer process rules (TransferService::STEPS / can / assertCan) and
 * the history every step writes (transfer_events).
 */
class TransferProcessTest extends TestCase
{
    use DatabaseTransactions;

    private int $warehouseId;
    private Shop $shop;
    private int $productId;
    private string $barcode;
    private User $owner;
    private User $whMgr;
    private User $shopMgr;
    private User $otherShopMgr;
    private TransferService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $u = substr(uniqid(), -8);
        $now = now();

        $this->warehouseId = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => "W$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->shop = Shop::forceCreate(['name' => "Shop $u", 'code' => "S$u", 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => true]);
        $other = Shop::forceCreate(['name' => "Other $u", 'code' => "O$u", 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => true]);
        $categoryId = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => "C$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->barcode = '93' . random_int(10000000000, 99999999999);
        $this->productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId, 'sku' => "SKU$u", 'name' => "Runner $u", 'barcode' => $this->barcode, 'items_per_box' => 10, 'is_active' => true,
            'purchase_price' => 1000, 'selling_price' => 3000, 'box_selling_price' => 28000, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $user = fn ($role, $type = null, $id = null) => User::forceCreate([
            'name' => ucfirst($role) . ' ' . $u, 'email' => $role . uniqid() . '@example.test', 'password' => 'x', 'is_active' => true,
            'must_change_password' => false, 'role' => $role, 'location_type' => $type, 'location_id' => $id,
        ]);
        $this->owner = $user('owner');
        $this->whMgr = $user('warehouse_manager', 'warehouse', $this->warehouseId);
        $this->shopMgr = $user('shop_manager', 'shop', $this->shop->id);
        $this->otherShopMgr = $user('shop_manager', 'shop', $other->id);
        $this->svc = app(TransferService::class);

        foreach (range(1, 6) as $i) {
            DB::table('boxes')->insert([
                'product_id' => $this->productId, 'box_code' => 'P' . uniqid() . $i, 'items_total' => 10, 'items_remaining' => 10,
                'status' => 'full', 'location_type' => 'warehouse', 'location_id' => $this->warehouseId,
                'received_by' => $this->owner->id, 'received_at' => $now->copy()->subDay(), 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    private function requested(int $boxes = 3): Transfer
    {
        $this->actingAs($this->shopMgr);

        return $this->svc->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id, 'needed_by' => now()->addDays(3)->toDateString(),
            'items' => [['product_id' => $this->productId, 'quantity' => $boxes]],
        ]);
    }

    public function test_each_step_is_allowed_only_for_the_right_role_and_status(): void
    {
        $t = $this->requested();

        $this->assertTrue($this->svc->can('approve', $t, $this->owner));
        $this->assertTrue($this->svc->can('approve', $t, $this->whMgr));
        $this->assertFalse($this->svc->can('approve', $t, $this->shopMgr));
        $this->assertTrue($this->svc->can('cancel', $t, $this->shopMgr));      // own request, still pending
        $this->assertFalse($this->svc->can('cancel', $t, $this->otherShopMgr));
        $this->assertFalse($this->svc->can('dispatch', $t, $this->whMgr));    // not approved yet

        $this->actingAs($this->whMgr);
        $this->svc->approveTransfer($t);
        $t->refresh();
        $this->assertFalse($this->svc->can('cancel', $t, $this->shopMgr));   // approved: the shop can't withdraw any more
        $this->assertTrue($this->svc->can('cancel', $t, $this->owner));

        $this->actingAs($this->shopMgr);
        $this->expectException(\DomainException::class);
        $this->svc->rejectTransfer($t, 'nope');
    }

    public function test_approval_keeps_the_request_and_packing_stops_at_the_approved_number(): void
    {
        $t = $this->requested(3);
        $itemId = $t->items()->value('id');
        $this->actingAs($this->whMgr);
        $this->svc->approveTransfer($t, 'Two for now', [$itemId => 2]);

        $item = $t->items()->first();
        $this->assertSame([3, 2], [$item->quantity_requested, $item->quantity_approved]);

        $this->svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, 2);
        $this->expectException(\DomainException::class);
        $this->svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, 1);
    }

    public function test_finishing_packing_short_needs_a_reason(): void
    {
        $t = $this->requested(3);
        $this->actingAs($this->whMgr);
        $this->svc->approveTransfer($t);
        $this->svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, 2);
        $itemId = $t->items()->value('id');

        try {
            $this->svc->finishPacking($t->fresh());
            $this->fail('Packing short without a reason should be refused');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('short (2 of 3 boxes)', $e->getMessage());
        }

        $this->svc->finishPacking($t->fresh(), [$itemId => 'Last box is water-damaged']);
        $t->refresh();
        $this->assertSame(TransferStatus::READY, $t->status);
        $this->assertSame($this->whMgr->id, $t->packing_done_by);
        $this->assertSame('Last box is water-damaged', $t->items()->value('short_reason'));

        $this->svc->reopenPacking($t, 'Swap a box');
        $this->assertSame(TransferStatus::APPROVED, $t->fresh()->status);
        $this->assertNull($t->items()->value('short_reason'));
    }

    public function test_a_full_transfer_records_every_step_with_who_and_when(): void
    {
        $t = $this->requested(2);
        $this->actingAs($this->owner);
        $this->svc->approveTransfer($t, 'OK');
        $this->actingAs($this->whMgr);
        $this->svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, 2);
        $this->svc->finishPacking($t->fresh());
        $this->svc->dispatch($t->fresh(), null, [
            'handed_to_name' => 'Jean Driver', 'transporter_instructions' => 'Keep dry',
            'expected_arrival_at' => now()->addHours(5),
        ]);
        $this->actingAs($this->shopMgr);
        $this->svc->markAsDelivered($t->fresh());
        $boxes = TransferBox::where('transfer_id', $t->id)->pluck('box_id')->map(fn ($id) => ['box_id' => $id])->all();
        $this->svc->receiveTransfer($t->fresh(), $boxes, ['received_by_name' => 'Alice']);

        $t->refresh();
        $this->assertSame(
            ['requested', 'approved', 'packing_started', 'packing_done', 'dispatched', 'arrived', 'received', 'closed'],
            $t->events->pluck('action')->all()
        );
        $this->assertSame($this->whMgr->id, $t->shipped_by);
        $this->assertSame('Jean Driver', $t->handed_to_name);
        $this->assertSame('Keep dry', $t->transporter_instructions);
        $this->assertNotNull($t->expected_arrival_at);
        $this->assertSame($this->shopMgr->id, $t->delivered_by);
        $this->assertSame('Alice', $t->received_by_name);
        $this->assertNotNull($t->closed_at);
        $this->assertNotNull($t->needed_by);
        $this->assertSame([$this->shopMgr->id, $this->owner->id], $t->events->take(2)->pluck('user_id')->all());
    }

    public function test_dispatching_straight_from_approved_finishes_packing_on_the_way(): void
    {
        $t = $this->requested(3);
        $this->actingAs($this->whMgr);
        $this->svc->approveTransfer($t);
        $this->svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, 2);
        $this->svc->markAsShipped($t->fresh());

        $t->refresh();
        $this->assertSame(TransferStatus::IN_TRANSIT, $t->status);
        $this->assertNotNull($t->packing_done_at);
        $this->assertSame('Not packed before dispatch', $t->items()->value('short_reason'));
    }

    public function test_cancelling_records_who_and_why(): void
    {
        $t = $this->requested();
        $this->svc->cancelTransfer($t, 'Ordered by mistake');   // the shop withdraws its own request

        $t->refresh();
        $this->assertSame(TransferStatus::CANCELLED, $t->status);
        $this->assertSame($this->shopMgr->id, $t->cancelled_by);
        $this->assertSame('Ordered by mistake', $t->events()->where('action', 'cancelled')->value('note'));
    }

    public function test_backfill_builds_history_for_existing_transfers(): void
    {
        $t = $this->requested(2);
        DB::table('transfer_events')->where('transfer_id', $t->id)->delete();
        DB::table('transfers')->where('id', $t->id)->update([
            'status' => 'received', 'requested_at' => now()->subDays(3), 'reviewed_at' => now()->subDays(2), 'reviewed_by' => $this->owner->id,
            'shipped_at' => now()->subDay(), 'delivered_at' => now()->subHours(3), 'received_at' => now()->subHours(2),
            'received_by' => $this->shopMgr->id, 'has_discrepancy' => false, 'closed_at' => null,
        ]);
        DB::table('transfer_items')->where('transfer_id', $t->id)->update(['quantity_approved' => null]);

        $migration = require database_path('migrations/2026_10_01_000001_add_transfer_process_fields.php');
        $migration->backfill();
        $migration->backfill();   // idempotent

        $t->refresh();
        $this->assertSame(['requested', 'approved', 'dispatched', 'arrived', 'received'], $t->events->pluck('action')->all());
        $this->assertSame(2, (int) $t->items()->value('quantity_approved'));
        $this->assertNotNull($t->closed_at);
    }
}
