<?php

namespace Tests\Feature\Transfers;

use App\Models\Shop;
use App\Models\Transfer;
use App\Models\TransferBox;
use App\Models\User;
use App\Services\Inventory\TransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Transfer module bug fixes (2026-09-30): request notes are no longer
 * overwritten on approve / reject / cancel, the owner detail doesn't flag
 * an in-transit transfer as a discrepancy, the warehouse can open any of
 * its transfers, and the delivery note counts boxes as boxes.
 */
class TransferFixesTest extends TestCase
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
        $this->barcode = '98' . random_int(10000000000, 99999999999);
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
        foreach (range(1, 6) as $i) {
            DB::table('boxes')->insert([
                'product_id' => $this->productId, 'box_code' => 'B' . uniqid() . $i, 'items_total' => 10, 'items_remaining' => 10,
                'status' => 'full', 'location_type' => 'warehouse', 'location_id' => $this->warehouseId,
                'received_by' => $this->owner->id, 'received_at' => $now->copy()->subDay(), 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    private function requested(int $boxes = 3, ?string $note = 'Weekend restock'): Transfer
    {
        $this->actingAs($this->shopMgr);

        return $this->svc->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id, 'notes' => $note,
            'items' => [['product_id' => $this->productId, 'quantity' => $boxes]],
        ]);
    }

    private function shipped(): Transfer
    {
        $t = $this->requested();
        $this->actingAs($this->whMgr);
        $this->svc->approveTransfer($t);
        $this->svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, 3);
        $this->svc->markAsShipped($t->fresh());

        return $t->fresh();
    }

    public function test_approve_keeps_the_shops_note_and_stores_the_approval_note_apart(): void
    {
        $t = $this->requested();
        $this->actingAs($this->whMgr);
        $this->svc->approveTransfer($t, 'Pack the blue ones first');

        $t->refresh();
        $this->assertSame('Weekend restock', $t->notes);
        $this->assertSame('Pack the blue ones first', $t->review_notes);
    }

    public function test_reject_and_cancel_keep_the_shops_note(): void
    {
        $this->actingAs($this->whMgr);
        $rejected = $this->svc->rejectTransfer($this->requested(), 'Out of stock this week');
        $this->assertSame('Weekend restock', $rejected->fresh()->notes);
        $this->assertSame('Out of stock this week', $rejected->fresh()->review_notes);

        $cancelled = $this->svc->cancelTransfer($this->requested(), 'Duplicate request');
        $this->assertSame('Weekend restock', $cancelled->fresh()->notes);
        $this->assertSame('Duplicate request', $cancelled->fresh()->review_notes);
    }

    public function test_backfill_splits_legacy_rejection_and_cancellation_notes(): void
    {
        $rejected = $this->requested(1, null);
        $cancelled = $this->requested(1, null);
        $bare = $this->requested(1, null);
        DB::table('transfers')->where('id', $rejected->id)->update(['status' => 'rejected', 'notes' => 'No stock', 'review_notes' => null]);
        DB::table('transfers')->where('id', $cancelled->id)->update(['status' => 'cancelled', 'notes' => "Weekend restock\n\nCancelled: Shop closed", 'review_notes' => null]);
        DB::table('transfers')->where('id', $bare->id)->update(['status' => 'cancelled', 'notes' => 'Cancelled: Wrong shop', 'review_notes' => null]);

        $migration = require database_path('migrations/2026_09_30_000001_add_review_notes_to_transfers.php');
        $migration->backfill();
        $migration->backfill(); // idempotent

        $this->assertSame([null, 'No stock'], [$rejected->fresh()->notes, $rejected->fresh()->review_notes]);
        $this->assertSame(['Weekend restock', 'Shop closed'], [$cancelled->fresh()->notes, $cancelled->fresh()->review_notes]);
        $this->assertSame([null, 'Wrong shop'], [$bare->fresh()->notes, $bare->fresh()->review_notes]);
    }

    public function test_owner_detail_shows_no_discrepancy_while_in_transit(): void
    {
        $t = $this->shipped();

        $this->actingAs($this->owner)->get(route('owner.transfers.show', $t))
            ->assertOk()
            ->assertDontSee('<th>Discrepancy</th>', false)
            ->assertSee("Shop's request")
            ->assertSee('Weekend restock');
    }

    public function test_owner_detail_shows_damage_notes_after_receiving(): void
    {
        $t = $this->shipped();
        $this->actingAs($this->whMgr);
        $this->svc->markAsDelivered($t);
        $boxIds = TransferBox::where('transfer_id', $t->id)->pluck('box_id');
        $this->actingAs($this->shopMgr);
        $this->svc->receiveTransfer($t->fresh(), $boxIds->map(fn ($id, $i) => [
            'box_id' => $id, 'is_damaged' => $i === 0, 'damage_notes' => $i === 0 ? 'Crushed corner' : null,
        ])->all());

        $this->actingAs($this->owner)->get(route('owner.transfers.show', $t))
            ->assertOk()
            ->assertSee('Crushed corner');
    }

    public function test_warehouse_can_open_a_transfer_that_is_no_longer_pending(): void
    {
        $t = $this->shipped();

        $this->actingAs($this->whMgr)->get(route('warehouse.transfers.show', $t))
            ->assertOk()
            ->assertSee($t->transfer_number)
            ->assertDontSee('Approve Transfer');
    }

    public function test_delivery_note_counts_boxes_as_boxes_and_uses_local_time(): void
    {
        $t = $this->shipped();

        $html = $this->actingAs($this->whMgr)->get(route('warehouse.transfers.delivery-note', $t))->assertOk()->getContent();

        $this->assertStringContainsString('Boxes shipped', $html);
        $this->assertStringContainsString('Items shipped', $html);
        $this->assertStringContainsString(local_time($t->shipped_at)->format('d M Y, H:i'), $html);
        // Boxes requested 3, boxes shipped 3, items shipped 30.
        $this->assertMatchesRegularExpression('/>3<\/td>\s*<td[^>]*>3<\/td>\s*<td[^>]*>30<\/td>/', $html);
    }
}
