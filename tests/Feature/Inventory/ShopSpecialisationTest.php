<?php

namespace Tests\Feature\Inventory;

use App\Enums\BoxStatus;
use App\Livewire\Inventory\Transfers\RequestTransfer;
use App\Livewire\Owner\Locations\LocationList;
use App\Livewire\Shop\Sales\UnifiedPos;
use App\Livewire\Shop\StockLevels;
use App\Livewire\Shop\StockReturns as ShopStockReturns;
use App\Livewire\Warehouse\StockReturns as WarehouseStockReturns;
use App\Models\Box;
use App\Models\DailySession;
use App\Models\Shop;
use App\Models\StockReturn;
use App\Models\User;
use App\Services\Inventory\StockReturnService;
use App\Services\Inventory\TransferService;
use App\Services\Sales\SaleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Specialised shops (only their categories, from their own stock or the
 * warehouse) + Return to warehouse. Runs on smart_inventory_test only.
 */
class ShopSpecialisationTest extends TestCase
{
    use DatabaseTransactions;

    private int $warehouseId;
    private Shop $shoeShop;
    private Shop $homeShop;
    private int $catFootwear;
    private int $catSneakers;   // child of Footwear
    private int $catHousehold;
    private int $shoe;          // in Sneakers
    private int $soap;          // in Household
    private User $owner;
    private User $shoeMgr;
    private User $whMgr;

    protected function setUp(): void
    {
        parent::setUp();

        $u   = uniqid();
        $now = now()->toDateTimeString();

        $this->warehouseId = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => 'W' . substr($u, -8), 'created_at' => $now, 'updated_at' => $now]);
        $this->catFootwear  = DB::table('categories')->insertGetId(['name' => "Footwear $u", 'code' => 'FW' . substr($u, -7), 'created_at' => $now, 'updated_at' => $now]);
        $this->catSneakers  = DB::table('categories')->insertGetId(['name' => "Sneakers $u", 'code' => 'SN' . substr($u, -7), 'parent_id' => $this->catFootwear, 'created_at' => $now, 'updated_at' => $now]);
        $this->catHousehold = DB::table('categories')->insertGetId(['name' => "Household $u", 'code' => 'HH' . substr($u, -7), 'created_at' => $now, 'updated_at' => $now]);

        $this->shoeShop = Shop::forceCreate(['name' => "Shoes $u", 'code' => 'SS' . substr($u, -7), 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => false]);
        $this->homeShop = Shop::forceCreate(['name' => "Home $u", 'code' => 'HS' . substr($u, -7), 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => false]);
        $this->shoeShop->categories()->sync([$this->catFootwear]);
        $this->homeShop->categories()->sync([$this->catHousehold]);

        $product = fn ($name, $cat) => DB::table('products')->insertGetId([
            'sku' => 'SKU' . uniqid(), 'name' => $name, 'items_per_box' => 10, 'category_id' => $cat, 'is_active' => true,
            'purchase_price' => 1000, 'selling_price' => 3000, 'box_selling_price' => 28000, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->shoe = $product("Air Max $u", $this->catSneakers);
        $this->soap = $product("Soap $u", $this->catHousehold);

        $user = fn ($role, $email, $locType = null, $locId = null) => User::forceCreate([
            'name' => ucfirst($role), 'email' => $email . $u . '@example.test', 'password' => bcrypt('x'), 'is_active' => true,
            'must_change_password' => false, 'role' => $role, 'location_type' => $locType, 'location_id' => $locId,
        ]);
        $this->owner   = $user('owner', 'o');
        $this->shoeMgr = $user('shop_manager', 'sm', 'shop', $this->shoeShop->id);
        $this->whMgr   = $user('warehouse_manager', 'wm', 'warehouse', $this->warehouseId);

        DailySession::forceCreate([
            'shop_id' => $this->shoeShop->id, 'session_date' => business_today()->toDateString(), 'status' => 'open',
            'opening_balance' => 0, 'opened_by' => $this->shoeMgr->id, 'opened_at' => now(),
        ]);
    }

    private function box(int $productId, string $locType, int $locId, string $status = 'full', int $remaining = 10): int
    {
        return DB::table('boxes')->insertGetId([
            'product_id' => $productId, 'box_code' => 'B' . uniqid(), 'items_total' => 10, 'items_remaining' => $remaining,
            'status' => $status, 'location_type' => $locType, 'location_id' => $locId,
            'received_by' => $this->owner->id, 'received_at' => now()->subDay(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    // ── Part A: shop categories ──────────────────────────────────────────

    public function test_rule_covers_subcategories_and_general_stores(): void
    {
        $this->assertTrue($this->shoeShop->sellsCategory($this->catSneakers), 'parent covers child');
        $this->assertTrue($this->shoeShop->sellsProduct($this->shoe));
        $this->assertFalse($this->shoeShop->sellsProduct($this->soap));
        $this->assertFalse($this->shoeShop->sellsCategory(null));

        $general = Shop::forceCreate(['name' => 'General', 'code' => 'GN' . substr(uniqid(), -7), 'is_active' => true, 'sells_all_categories' => true]);
        $this->assertNull($general->sellableCategoryIds());
        $this->assertTrue($general->sellsProduct($this->soap));
    }

    public function test_request_list_is_filtered_and_server_refuses_other_categories(): void
    {
        Livewire::actingAs($this->shoeMgr)->test(RequestTransfer::class)
            ->assertViewHas('products', fn ($p) => $p->pluck('id')->contains($this->shoe) && ! $p->pluck('id')->contains($this->soap))
            ->assertSee('Showing only what this shop sells');

        // Owner requesting for the shoe shop: same rule, no override
        $this->actingAs($this->owner);
        $this->expectException(\DomainException::class);
        app(TransferService::class)->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shoeShop->id,
            'items' => [['product_id' => $this->soap, 'quantity' => 1]],
        ]);
    }

    public function test_pos_shows_only_sellable_stock_and_blocks_the_rest(): void
    {
        $this->box($this->shoe, 'shop', $this->shoeShop->id);
        $this->box($this->soap, 'shop', $this->shoeShop->id);      // left over from before specialising
        $this->box($this->shoe, 'warehouse', $this->warehouseId);
        $this->box($this->soap, 'warehouse', $this->warehouseId);

        $pos = Livewire::actingAs($this->shoeMgr)->test(UnifiedPos::class);
        $shopIds = collect($pos->get('shopStock'))->pluck('id');
        $whIds   = collect($pos->get('warehouseStock'))->pluck('id');
        $this->assertTrue($shopIds->contains($this->shoe));
        $this->assertFalse($shopIds->contains($this->soap), 'old out-of-category stock is not sellable');
        $this->assertTrue($whIds->contains($this->shoe));
        $this->assertFalse($whIds->contains($this->soap));

        $pos->call('selectProduct', $this->soap, 'shop')
            ->assertSet('showAddModal', false)
            ->assertDispatched('notification', fn ($n, $p) => str_contains(json_encode($p), "doesn"));
    }

    public function test_sale_service_refuses_other_categories_even_from_own_stock(): void
    {
        $this->box($this->soap, 'shop', $this->shoeShop->id);
        $this->actingAs($this->shoeMgr);

        $this->expectException(\DomainException::class);
        (new SaleService())->createMixedSale([
            'shop_id'  => $this->shoeShop->id,
            'payments' => [['method' => 'cash', 'amount' => 28000, 'reference' => null]],
            'items'    => [['product_id' => $this->soap, 'source' => 'shop', 'source_id' => $this->shoeShop->id, 'mode' => 'box', 'qty' => 1, 'items_per_box' => 10, 'price' => 28000, 'price_modified' => false]],
        ]);
    }

    public function test_locations_form_requires_categories_and_warns_about_stranded_stock(): void
    {
        $this->box($this->soap, 'shop', $this->shoeShop->id);
        $this->shoeShop->update(['sells_all_categories' => true]);

        $lm = Livewire::actingAs($this->owner)->test(LocationList::class)
            ->set('activeTab', 'shops')
            ->call('openEdit', $this->shoeShop->id)
            ->set('form_sells_all', false)
            ->set('form_category_ids', [])
            ->call('save')
            ->assertHasErrors('form_category_ids');

        $lm->call('toggleShopCategory', $this->catFootwear)
            ->call('save')
            ->assertHasNoErrors();
        // assertDispatched's closure only sees the first "notification" (the success toast)
        $this->assertStringContainsString('outside its categories', json_encode($lm->effects['dispatches'] ?? []));

        $this->assertFalse($this->shoeShop->fresh()->sells_all_categories);
        $this->assertSame([$this->catFootwear], $this->shoeShop->fresh()->categories()->pluck('categories.id')->map(fn ($i) => (int) $i)->all());
    }

    public function test_stock_page_separates_stock_not_sold_here(): void
    {
        $this->box($this->shoe, 'shop', $this->shoeShop->id);
        $this->box($this->soap, 'shop', $this->shoeShop->id);

        Livewire::actingAs($this->shoeMgr)->test(StockLevels::class)
            ->assertSee('Not sold at this shop')
            ->assertViewHas('notSold', fn ($n) => (int) $n->boxes === 1)
            ->assertViewHas('kpis', fn ($k) => (int) $k->total_boxes === 1);
    }

    // ── Part B: Return to warehouse ──────────────────────────────────────

    public function test_send_takes_boxes_off_sale_opened_first(): void
    {
        $sealed = $this->box($this->soap, 'shop', $this->shoeShop->id, 'full', 10);
        $opened = $this->box($this->soap, 'shop', $this->shoeShop->id, 'partial', 4);

        $return = app(StockReturnService::class)->send($this->shoeShop, [$this->soap => 1], 'Not sold here', $this->shoeMgr);

        $this->assertStringStartsWith('RTW-', $return->return_number);
        $this->assertSame('in_transit', DB::table('boxes')->where('id', $opened)->value('status'), 'opened box goes first');
        $this->assertSame('full', DB::table('boxes')->where('id', $sealed)->value('status'));
        $this->assertSame(1, DB::table('box_movements')->where('reference_type', 'stock_return')->where('movement_type', 'return_sent')->count());
    }

    public function test_receive_moves_boxes_and_records_damaged_and_missing(): void
    {
        $a = $this->box($this->soap, 'shop', $this->shoeShop->id, 'full', 10);
        $b = $this->box($this->soap, 'shop', $this->shoeShop->id, 'partial', 6);
        $c = $this->box($this->soap, 'shop', $this->shoeShop->id, 'full', 10);
        $return = app(StockReturnService::class)->send($this->shoeShop, [$this->soap => 3], null, $this->shoeMgr);

        // Another warehouse's manager can't receive it
        $otherWh = DB::table('warehouses')->insertGetId(['name' => 'Other', 'code' => 'OT' . substr(uniqid(), -7), 'created_at' => now(), 'updated_at' => now()]);
        $stranger = User::forceCreate(['name' => 'X', 'email' => 'x' . uniqid() . '@example.test', 'password' => 'x', 'role' => 'warehouse_manager', 'location_type' => 'warehouse', 'location_id' => $otherWh]);
        try {
            app(StockReturnService::class)->receive($return, [], null, $stranger);
            $this->fail('stranger received');
        } catch (\DomainException) {
        }

        app(StockReturnService::class)->receive($return, [$b => 'damaged', $c => 'missing'], 'box crushed, one lost', $this->whMgr);

        $boxA = Box::find($a); $boxB = Box::find($b); $boxC = Box::find($c);
        $this->assertSame('warehouse', $boxA->location_type->value);
        $this->assertSame(BoxStatus::FULL, $boxA->status, 'status restored');
        $this->assertSame('warehouse', $boxB->location_type->value);
        $this->assertSame(BoxStatus::DAMAGED, $boxB->status);
        $this->assertSame('shop', $boxC->location_type->value, 'missing box never arrived');
        $this->assertSame(BoxStatus::DAMAGED, $boxC->status);

        $return->refresh();
        $this->assertSame('received', $return->status);
        $this->assertTrue($return->has_discrepancy);
    }

    public function test_cancel_puts_boxes_back_on_sale(): void
    {
        $opened = $this->box($this->shoe, 'shop', $this->shoeShop->id, 'partial', 3);
        $return = app(StockReturnService::class)->send($this->shoeShop, [$this->shoe => 1], null, $this->shoeMgr);
        $this->assertSame('in_transit', DB::table('boxes')->where('id', $opened)->value('status'));

        app(StockReturnService::class)->cancel($return, 'changed my mind', $this->shoeMgr);

        $this->assertSame('partial', DB::table('boxes')->where('id', $opened)->value('status'));
        $this->assertSame('cancelled', $return->fresh()->status);
    }

    public function test_shop_and_warehouse_pages_end_to_end(): void
    {
        $this->box($this->soap, 'shop', $this->shoeShop->id, 'full', 10);
        $this->box($this->soap, 'shop', $this->shoeShop->id, 'full', 10);
        $this->box($this->shoe, 'shop', $this->shoeShop->id, 'full', 10);

        // Not-sold stock is pre-filled with "send all"
        Livewire::actingAs($this->shoeMgr)->test(ShopStockReturns::class)
            ->assertSee('Not sold at this shop')
            ->assertSet("send.{$this->soap}", 2)
            ->call('review')
            ->assertSet('showReview', true)
            ->assertSee('Send 2 boxes back?')
            ->call('confirmSend')
            ->assertDispatched('notification');

        $return = StockReturn::where('shop_id', $this->shoeShop->id)->firstOrFail();
        $this->assertSame(2, $return->boxes()->count());

        Livewire::actingAs($this->whMgr)->test(WarehouseStockReturns::class)
            ->assertSee($return->return_number)
            ->call('openReceive', $return->id)
            ->assertSee('Receive ' . $return->return_number)
            ->call('confirmReceive')
            ->assertDispatched('notification');

        $this->assertSame('received', $return->fresh()->status);
        $this->assertSame(2, Box::where('product_id', $this->soap)->where('location_type', 'warehouse')->where('status', 'full')->count());
    }
}
