<?php

namespace Tests\Feature\Transfers;

use App\Livewire\Inventory\Transfers\RequestTransfer;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/** New stock request form (phase 6). */
class TransferRequestTest extends TestCase
{
    use DatabaseTransactions;

    private int $warehouseId;
    private Shop $shop;
    private int $productId;
    private User $shopMgr;

    protected function setUp(): void
    {
        parent::setUp();
        $u = substr(uniqid(), -8);
        $now = now();

        $this->warehouseId = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => "W$u", 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $this->shop = Shop::forceCreate(['name' => "Shop $u", 'code' => "S$u", 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => true]);
        $categoryId = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => "C$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId, 'sku' => "SKU$u", 'name' => "Loafer $u", 'barcode' => '94' . random_int(10000000000, 99999999999),
            'items_per_box' => 12, 'is_active' => true, 'purchase_price' => 1000, 'selling_price' => 3000, 'box_selling_price' => 28000,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->shopMgr = User::forceCreate([
            'name' => 'Shop', 'email' => 's' . uniqid() . '@example.test', 'password' => 'x', 'is_active' => true,
            'must_change_password' => false, 'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shop->id,
        ]);

        $box = fn (string $type, int $id, string $status, int $left) => DB::table('boxes')->insert([
            'product_id' => $this->productId, 'box_code' => 'R' . uniqid() . random_int(10, 99), 'items_total' => 12, 'items_remaining' => $left,
            'status' => $status, 'location_type' => $type, 'location_id' => $id,
            'received_by' => $this->shopMgr->id, 'received_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $box('warehouse', $this->warehouseId, 'full', 12);
        $box('warehouse', $this->warehouseId, 'full', 12);
        $box('warehouse', $this->warehouseId, 'partial', 5);
        $box('shop', $this->shop->id, 'full', 12);
        $box('shop', $this->shop->id, 'partial', 7);
    }

    public function test_the_form_shows_warehouse_and_shop_stock_in_plain_words(): void
    {
        Livewire::actingAs($this->shopMgr)->test(RequestTransfer::class)
            ->assertViewHas('shopStock', fn ($s) => $s[$this->productId] === ['boxes' => 2, 'items' => 19])
            ->assertSee('2 sealed · 1 opened')
            ->assertSee('In your shop')
            ->assertSee('19 items');
    }

    public function test_sending_a_request_opens_the_new_transfer(): void
    {
        $rq = Livewire::actingAs($this->shopMgr)->test(RequestTransfer::class)
            ->call('addProductToCart', $this->productId)
            ->set('items.0.boxes_requested', '2')
            ->set('notes', 'For Saturday')
            ->call('submit');

        $transfer = Transfer::where('to_shop_id', $this->shop->id)->latest('id')->firstOrFail();
        $rq->assertRedirect(route('shop.transfers.show', $transfer));
        $this->assertSame('For Saturday', $transfer->notes);
    }

    public function test_delivery_note_uses_the_business_name(): void
    {
        $this->assertStringNotContainsString('New Shoes Ltd', file_get_contents(resource_path('views/transfers/delivery-note.blade.php')));
    }
}
