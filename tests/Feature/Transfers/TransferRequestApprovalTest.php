<?php

namespace Tests\Feature\Transfers;

use App\Enums\BoxStatus;
use App\Enums\TransferStatus;
use App\Livewire\Inventory\Transfers\RequestTransfer;
use App\Livewire\Transfers\TransferDetail;
use App\Models\Box;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\TransferBox;
use App\Models\User;
use App\Services\Inventory\TransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/** Transfer process phase 2: needed-by date, cancel / withdraw with a reason. */
class TransferRequestApprovalTest extends TestCase
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

        $this->warehouseId = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => "W$u", 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $this->shop = Shop::forceCreate(['name' => "Shop $u", 'code' => "S$u", 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => true]);
        $categoryId = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => "C$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->barcode = '92' . random_int(10000000000, 99999999999);
        $this->productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId, 'sku' => "SKU$u", 'name' => "Boot $u", 'barcode' => $this->barcode, 'items_per_box' => 10, 'is_active' => true,
            'purchase_price' => 1000, 'selling_price' => 3000, 'box_selling_price' => 28000, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $user = fn ($role, $type = null, $id = null) => User::forceCreate([
            'name' => ucfirst($role), 'email' => $role . uniqid() . '@example.test', 'password' => 'x', 'is_active' => true,
            'must_change_password' => false, 'role' => $role, 'location_type' => $type, 'location_id' => $id,
        ]);
        $this->owner = $user('owner');
        $this->whMgr = $user('warehouse_manager', 'warehouse', $this->warehouseId);
        $this->shopMgr = $user('shop_manager', 'shop', $this->shop->id);
        foreach (range(1, 3) as $i) {
            DB::table('boxes')->insert([
                'product_id' => $this->productId, 'box_code' => 'Q' . uniqid() . $i, 'items_total' => 10, 'items_remaining' => 10,
                'status' => 'full', 'location_type' => 'warehouse', 'location_id' => $this->warehouseId,
                'received_by' => $this->owner->id, 'received_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function test_the_request_records_a_needed_by_date(): void
    {
        $day = business_today()->addDays(2)->toDateString();

        Livewire::actingAs($this->shopMgr)->test(RequestTransfer::class)
            ->call('addProductToCart', $this->productId)
            ->set('items.0.boxes_requested', '1')
            ->set('neededBy', business_today()->subDay()->toDateString())
            ->call('submit')
            ->assertHasErrors('neededBy')
            ->set('neededBy', $day)
            ->call('submit')
            ->assertHasNoErrors();

        $t = Transfer::where('to_shop_id', $this->shop->id)->latest('id')->firstOrFail();
        $this->assertSame($day, $t->needed_by->toDateString());
        $this->assertSame($day, $t->events()->where('action', 'requested')->first()->meta['needed_by']);
    }

    public function test_the_shop_can_withdraw_only_while_pending(): void
    {
        $this->actingAs($this->shopMgr);
        $t = app(TransferService::class)->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id,
            'items' => [['product_id' => $this->productId, 'quantity' => 1]],
        ]);

        Livewire::actingAs($this->shopMgr)->test(TransferDetail::class, ['transfer' => $t])
            ->assertSee('Withdraw request')
            ->call('openCancel')
            ->call('cancelTransfer')->assertHasErrors('cancelReason')
            ->set('cancelReason', 'Found stock in the back room')
            ->call('cancelTransfer')->assertHasNoErrors();

        $t->refresh();
        $this->assertSame(TransferStatus::CANCELLED, $t->status);
        $this->assertSame($this->shopMgr->id, $t->cancelled_by);

        // An approved transfer: the shop no longer sees the button.
        $this->actingAs($this->shopMgr);
        $t2 = app(TransferService::class)->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id,
            'items' => [['product_id' => $this->productId, 'quantity' => 1]],
        ]);
        $this->actingAs($this->whMgr);
        app(TransferService::class)->approveTransfer($t2);
        Livewire::actingAs($this->shopMgr)->test(TransferDetail::class, ['transfer' => $t2->fresh()])
            ->assertDontSee('Withdraw request');
    }

    public function test_the_owner_can_cancel_after_packing_and_the_boxes_go_back(): void
    {
        $this->actingAs($this->shopMgr);
        $svc = app(TransferService::class);
        $t = $svc->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id,
            'items' => [['product_id' => $this->productId, 'quantity' => 2]],
        ]);
        $this->actingAs($this->whMgr);
        $svc->approveTransfer($t);
        $svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, 2);
        $boxIds = TransferBox::where('transfer_id', $t->id)->pluck('box_id');

        Livewire::actingAs($this->owner)->test(TransferDetail::class, ['transfer' => $t->fresh()])
            ->assertSee('Cancel transfer')
            ->call('openCancel')
            ->set('cancelReason', 'Shop closing for renovation')
            ->call('cancelTransfer');

        $this->assertSame(TransferStatus::CANCELLED, $t->fresh()->status);
        $this->assertSame([BoxStatus::FULL], Box::whereIn('id', $boxIds)->get()->pluck('status')->unique()->values()->all());
    }
}
