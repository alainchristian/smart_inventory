<?php

namespace Tests\Feature\Transfers;

use App\Livewire\Owner\Transfers\TransfersList as OwnerList;
use App\Livewire\Shop\Transfers\TransfersList as ShopList;
use App\Livewire\WarehouseManager\Transfers\TransfersList as WarehouseList;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\User;
use App\Services\Inventory\TransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/** One transfer list for owner, shop and warehouse (ListsTransfers). */
class TransferListTest extends TestCase
{
    use DatabaseTransactions;

    private int $warehouseId;
    private Shop $shopA;
    private Shop $shopB;
    private int $productId;
    private User $owner;
    private User $whMgr;
    private User $mgrA;
    private User $mgrB;
    private TransferService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $u = substr(uniqid(), -8);
        $now = now();

        $this->warehouseId = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => "W$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->shopA = Shop::forceCreate(['name' => "Alpha $u", 'code' => "A$u", 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => true]);
        $this->shopB = Shop::forceCreate(['name' => "Beta $u", 'code' => "B$u", 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => true]);
        $categoryId = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => "C$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId, 'sku' => "SKU$u", 'name' => "Zebra Sneaker $u", 'barcode' => '97' . random_int(10000000000, 99999999999),
            'items_per_box' => 10, 'is_active' => true, 'purchase_price' => 1000, 'selling_price' => 3000, 'box_selling_price' => 28000,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $user = fn ($role, $type = null, $id = null) => User::forceCreate([
            'name' => ucfirst($role), 'email' => $role . uniqid() . '@example.test', 'password' => 'x', 'is_active' => true,
            'must_change_password' => false, 'role' => $role, 'location_type' => $type, 'location_id' => $id,
        ]);
        $this->owner = $user('owner');
        $this->whMgr = $user('warehouse_manager', 'warehouse', $this->warehouseId);
        $this->mgrA = $user('shop_manager', 'shop', $this->shopA->id);
        $this->mgrB = $user('shop_manager', 'shop', $this->shopB->id);
        $this->svc = app(TransferService::class);
    }

    private function request(Shop $shop, User $by, int $boxes = 2): Transfer
    {
        $this->actingAs($by);

        return $this->svc->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $shop->id,
            'items' => [['product_id' => $this->productId, 'quantity' => $boxes]],
        ]);
    }

    public function test_each_role_sees_only_its_own_transfers(): void
    {
        $a = $this->request($this->shopA, $this->mgrA);
        $b = $this->request($this->shopB, $this->mgrB);

        Livewire::actingAs($this->mgrA)->test(ShopList::class)
            ->assertSee($a->transfer_number)->assertDontSee($b->transfer_number);

        Livewire::actingAs($this->whMgr)->test(WarehouseList::class)
            ->assertSee($a->transfer_number)->assertSee($b->transfer_number);

        Livewire::actingAs($this->mgrA)->test(ShopList::class)->assertViewHas('counts', fn ($c) => $c['all'] === 1 && $c['pending'] === 1);
    }

    public function test_status_tabs_count_and_filter(): void
    {
        $pending = $this->request($this->shopA, $this->mgrA);
        $rejected = $this->request($this->shopA, $this->mgrA);
        $this->actingAs($this->whMgr);
        $this->svc->rejectTransfer($rejected, 'No stock');

        Livewire::actingAs($this->owner)->test(OwnerList::class)
            ->assertViewHas('counts', fn ($c) => $c['pending'] >= 1 && $c['rejected'] >= 1)
            ->call('setStatus', 'rejected')
            ->assertSee($rejected->transfer_number)
            ->assertDontSee($pending->transfer_number)
            ->call('setStatus', 'not-a-status')
            ->assertSet('statusFilter', 'all');
    }

    public function test_owner_dashboard_status_links_still_work(): void
    {
        $t = $this->request($this->shopA, $this->mgrA);
        DB::table('transfers')->where('id', $t->id)->update(['has_discrepancy' => true]);
        $other = $this->request($this->shopA, $this->mgrA);

        Livewire::withQueryParams(['status' => 'discrepancy'])->actingAs($this->owner)->test(OwnerList::class)
            ->assertSet('statusFilter', 'discrepancy')
            ->assertSee($t->transfer_number)
            ->assertDontSee($other->transfer_number);
    }

    public function test_search_finds_by_number_shop_and_product(): void
    {
        $a = $this->request($this->shopA, $this->mgrA);
        $b = $this->request($this->shopB, $this->mgrB);

        $list = Livewire::actingAs($this->whMgr)->test(WarehouseList::class);
        $list->set('search', $a->transfer_number)->assertSee($a->transfer_number)->assertDontSee($b->transfer_number);
        $list->set('search', 'Beta')->assertSee($b->transfer_number)->assertDontSee($a->transfer_number);
        $list->set('search', 'zebra sneaker')->assertSee($a->transfer_number)->assertSee($b->transfer_number);
        // LIKE wildcards are literal
        $list->set('search', '%')->assertViewHas('counts', fn ($c) => $c['all'] === 0);
    }

    public function test_warehouse_sees_what_needs_it_first(): void
    {
        $pending = $this->request($this->shopA, $this->mgrA);
        $approved = $this->request($this->shopB, $this->mgrB);
        $this->actingAs($this->whMgr);
        $this->svc->approveTransfer($approved);

        Livewire::actingAs($this->whMgr)->test(WarehouseList::class)
            ->assertSee('Needs you')
            ->assertViewHas('needs', fn ($n) => $n->pluck('id')->sort()->values()->all() === collect([$pending->id, $approved->id])->sort()->values()->all())
            ->assertSee('Review')
            ->assertSee('Pack')
            // the main table holds the rest
            ->assertViewHas('transfers', fn ($p) => $p->isEmpty());

        // Filtering drops the "Needs you" section and lists them normally.
        Livewire::actingAs($this->whMgr)->test(WarehouseList::class)->call('setStatus', 'pending')
            ->assertViewHas('showNeeds', false)
            ->assertViewHas('transfers', fn ($p) => $p->pluck('id')->contains($pending->id));
    }

    public function test_summary_counts_ignore_the_filters(): void
    {
        $this->request($this->shopA, $this->mgrA, 3);
        $this->request($this->shopB, $this->mgrB, 4);

        Livewire::actingAs($this->whMgr)->test(WarehouseList::class)
            ->set('shopFilter', (string) $this->shopA->id)
            ->assertViewHas('summary', fn ($s) => $s['pending'] === 2 && $s['pending_boxes'] === 7 && $s['pending_shops'] === 2)
            ->assertViewHas('counts', fn ($c) => $c['all'] === 1);
    }

    public function test_pages_render_for_each_role(): void
    {
        $this->request($this->shopA, $this->mgrA);

        $this->actingAs($this->owner)->get(route('owner.transfers.index'))->assertOk()->assertSee('Awaiting approval');
        $this->actingAs($this->whMgr)->get(route('warehouse.transfers.index'))->assertOk()->assertSee('To approve');
        $this->actingAs($this->mgrA)->get(route('shop.transfers.index'))->assertOk()->assertSee('New request');
    }
}
