<?php

namespace Tests\Feature\Transfers;

use App\Enums\TransferStatus;
use App\Livewire\Transfers\TransferDetail;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\User;
use App\Services\Inventory\TransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/** One transfer detail page for owner, shop and warehouse (TransferDetail). */
class TransferDetailTest extends TestCase
{
    use DatabaseTransactions;

    private int $warehouseId;
    private Shop $shop;
    private int $productId;
    private string $barcode;
    private User $owner;
    private User $whMgr;
    private User $shopMgr;

    protected function setUp(): void
    {
        parent::setUp();
        $u = substr(uniqid(), -8);
        $now = now();

        $this->warehouseId = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => "W$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->shop = Shop::forceCreate(['name' => "Shop $u", 'code' => "S$u", 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => true]);
        $categoryId = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => "C$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->barcode = '96' . random_int(10000000000, 99999999999);
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

        foreach (range(1, 4) as $i) {
            DB::table('boxes')->insert([
                'product_id' => $this->productId, 'box_code' => 'D' . uniqid() . $i, 'items_total' => 10, 'items_remaining' => 10,
                'status' => 'full', 'location_type' => 'warehouse', 'location_id' => $this->warehouseId,
                'received_by' => $this->owner->id, 'received_at' => $now->copy()->subDay(), 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    private function pending(int $boxes = 3): Transfer
    {
        $this->actingAs($this->shopMgr);

        return app(TransferService::class)->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id, 'notes' => 'For the weekend',
            'items' => [['product_id' => $this->productId, 'quantity' => $boxes]],
        ]);
    }

    public function test_owner_can_approve_with_adjusted_quantities_and_a_note(): void
    {
        $t = $this->pending(3);
        $itemId = $t->items()->value('id');

        Livewire::actingAs($this->owner)->test(TransferDetail::class, ['transfer' => $t])
            ->assertSet('role', 'owner')
            ->assertSee('Approve transfer')
            ->set("qty.$itemId", '2')
            ->set('approveNote', 'Two now, one next week')
            ->call('approve')
            ->assertHasNoErrors()
            ->assertDispatched('notification');

        $t->refresh();
        $this->assertSame(TransferStatus::APPROVED, $t->status);
        $this->assertSame($this->owner->id, $t->reviewed_by);
        $this->assertSame('For the weekend', $t->notes);
        $this->assertSame('Two now, one next week', $t->review_notes);
        $this->assertSame(2, (int) $t->items()->value('quantity_requested'));
    }

    public function test_approval_is_limited_to_warehouse_stock(): void
    {
        $t = $this->pending(3);
        $itemId = $t->items()->value('id');

        Livewire::actingAs($this->whMgr)->test(TransferDetail::class, ['transfer' => $t])
            ->set("qty.$itemId", '9')
            ->call('approve')
            ->assertHasErrors("qty.$itemId")
            ->set("qty.$itemId", '0')
            ->call('approve')
            ->assertHasErrors("qty.$itemId");

        $this->assertSame(TransferStatus::PENDING, $t->fresh()->status);
    }

    public function test_reject_needs_a_reason_the_shop_can_see(): void
    {
        $t = $this->pending();

        Livewire::actingAs($this->whMgr)->test(TransferDetail::class, ['transfer' => $t])
            ->call('openReject')->assertSet('showReject', true)
            ->call('reject')->assertHasErrors('rejectReason')
            ->set('rejectReason', 'Out of stock until Monday')
            ->call('reject')->assertHasNoErrors()->assertSet('showReject', false);

        $this->assertSame(TransferStatus::REJECTED, $t->fresh()->status);

        $this->actingAs($this->shopMgr)->get(route('shop.transfers.show', $t))
            ->assertOk()->assertSee('Out of stock until Monday')->assertSee('You can send a new request.');
    }

    public function test_shop_cannot_approve_and_sees_a_waiting_notice(): void
    {
        $t = $this->pending();

        Livewire::actingAs($this->shopMgr)->test(TransferDetail::class, ['transfer' => $t])
            ->assertSet('role', 'shop')
            ->assertDontSee('Approve transfer')
            ->assertSee('Waiting for approval')
            ->call('approve');

        $this->assertSame(TransferStatus::PENDING, $t->fresh()->status);
    }

    public function test_other_shops_and_warehouses_are_refused(): void
    {
        $t = $this->pending();
        $u = substr(uniqid(), -6);
        $otherShop = Shop::forceCreate(['name' => "Other $u", 'code' => "O$u", 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => true]);
        $otherWh = DB::table('warehouses')->insertGetId(['name' => "WH2 $u", 'code' => "X$u", 'created_at' => now(), 'updated_at' => now()]);
        $mk = fn ($role, $type, $id) => User::forceCreate(['name' => 'X', 'email' => uniqid() . '@example.test', 'password' => 'x', 'is_active' => true,
            'must_change_password' => false, 'role' => $role, 'location_type' => $type, 'location_id' => $id]);

        $this->actingAs($mk('shop_manager', 'shop', $otherShop->id))->get(route('shop.transfers.show', $t))->assertForbidden();
        $this->actingAs($mk('warehouse_manager', 'warehouse', $otherWh))->get(route('warehouse.transfers.show', $t))->assertForbidden();
    }

    public function test_shop_marks_an_in_transit_transfer_as_arrived(): void
    {
        $t = $this->pending(2);
        $svc = app(TransferService::class);
        $this->actingAs($this->whMgr);
        $svc->approveTransfer($t);
        $svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, 2);
        $svc->markAsShipped($t->fresh());

        Livewire::actingAs($this->shopMgr)->test(TransferDetail::class, ['transfer' => $t])
            ->assertSee('Receive boxes')
            ->call('markAsDelivered')
            ->assertDispatched('transfer-updated');

        $this->assertSame(TransferStatus::DELIVERED, $t->fresh()->status);
    }

    public function test_detail_pages_render_for_every_role(): void
    {
        $t = $this->pending();

        $this->actingAs($this->owner)->get(route('owner.transfers.show', $t))->assertOk()->assertSee($t->transfer_number);
        $this->actingAs($this->whMgr)->get(route('warehouse.transfers.show', $t))->assertOk()->assertSee('Review quantities');
        $this->actingAs($this->shopMgr)->get(route('shop.transfers.show', $t))->assertOk()->assertSee('For the weekend');
    }
}
