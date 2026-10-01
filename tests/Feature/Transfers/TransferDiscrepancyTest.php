<?php

namespace Tests\Feature\Transfers;

use App\Enums\BoxStatus;
use App\Livewire\Transfers\TransferDetail;
use App\Livewire\WarehouseManager\Transfers\TransfersList as WarehouseList;
use App\Models\Box;
use App\Models\DamagedGood;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\TransferBox;
use App\Models\User;
use App\Services\Inventory\TransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/** Transfer process phase 5: damaged → Damaged Goods, missing → held until resolved, then closed. */
class TransferDiscrepancyTest extends TestCase
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
        $this->barcode = '90' . random_int(10000000000, 99999999999);
        $this->productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId, 'sku' => "SKU$u", 'name' => "Loafer $u", 'barcode' => $this->barcode, 'items_per_box' => 10, 'is_active' => true,
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
        foreach (range(1, 5) as $i) {
            DB::table('boxes')->insert([
                'product_id' => $this->productId, 'box_code' => 'DIS' . uniqid() . $i, 'items_total' => 10, 'items_remaining' => 10,
                'status' => 'full', 'location_type' => 'warehouse', 'location_id' => $this->warehouseId,
                'received_by' => $this->owner->id, 'received_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    /** Ship $boxes boxes; the shop receives the first $good, marks $damaged damaged, the rest never arrive. */
    private function received(int $boxes, int $good, int $damaged = 0): array
    {
        $this->actingAs($this->shopMgr);
        $t = $this->svc->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id,
            'items' => [['product_id' => $this->productId, 'quantity' => $boxes]],
        ]);
        $this->actingAs($this->whMgr);
        $this->svc->approveTransfer($t);
        $this->svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, $boxes);
        $this->svc->dispatch($t->fresh(), null, ['handed_to_name' => 'Jean Driver']);
        $ids = TransferBox::where('transfer_id', $t->id)->orderBy('id')->pluck('box_id')->values();

        $this->actingAs($this->shopMgr);
        $rows = $ids->take($good)->map(fn ($id) => ['box_id' => $id])
            ->merge($ids->slice($good, $damaged)->map(fn ($id) => ['box_id' => $id, 'is_damaged' => true, 'damage_notes' => 'Crushed']))->values()->all();
        $this->svc->receiveTransfer($t->fresh(), $rows);

        return [$t->fresh(), $ids];
    }

    public function test_damaged_boxes_go_to_damaged_goods_at_the_shop_and_the_transfer_closes(): void
    {
        [$t, $ids] = $this->received(2, 1, 1);
        $damagedId = $ids[1];

        $box = Box::find($damagedId);
        $this->assertSame(BoxStatus::DAMAGED, $box->status);
        $this->assertSame('shop', $box->location_type->value);
        $dg = DamagedGood::where('box_id', $damagedId)->firstOrFail();
        $this->assertSame(['transfer', $t->id, 'pending', 30000], [$dg->source_type, $dg->source_id, $dg->disposition->value, $dg->estimated_loss]);
        $this->assertTrue($t->has_discrepancy);
        $this->assertNotNull($t->closed_at, 'nothing missing: closed');
    }

    public function test_missing_boxes_stay_held_and_each_resolution_does_the_right_thing(): void
    {
        [$t, $ids] = $this->received(3, 0);
        [$a, $b, $c] = $ids->all();
        foreach ([$a, $b, $c] as $id) {
            $this->assertSame(BoxStatus::IN_TRANSIT, Box::find($id)->status, 'held, off sale');
        }
        $this->assertNull($t->closed_at);

        $detail = Livewire::actingAs($this->whMgr)->test(TransferDetail::class, ['transfer' => $t])
            ->assertSee('To resolve')
            ->call('openResolve', $a, 'found')->call('resolve')->assertHasErrors('resolveNote')
            ->set('resolveNote', 'Was on the loading bay')->call('resolve')->assertHasNoErrors()
            ->call('openResolve', $b, 'received_late')->set('resolveNote', 'Came with the next delivery')->call('resolve')
            ->call('openResolve', $c, 'lost')->set('resolveNote', 'Driver cannot account for it')->call('resolve');

        $this->assertSame([BoxStatus::FULL, 'warehouse'], [Box::find($a)->status, Box::find($a)->location_type->value]);
        $this->assertSame([BoxStatus::FULL, 'shop'], [Box::find($b)->status, Box::find($b)->location_type->value]);
        $this->assertSame(BoxStatus::DAMAGED, Box::find($c)->status);

        $lost = DamagedGood::where('box_id', $c)->firstOrFail();
        $this->assertSame(['transfer_lost', 'write_off'], [$lost->source_type, $lost->disposition->value]);
        $this->assertStringContainsString('Jean Driver', $lost->damage_description);

        $t->refresh();
        $this->assertNotNull($t->closed_at);
        $this->assertSame(10, (int) $t->items()->value('quantity_received'));   // the late box
        $this->assertSame(3, $t->events()->where('action', 'issue_resolved')->count());
        $this->assertSame('closed', $t->events()->get()->last()->action);
    }

    public function test_the_shop_cannot_resolve(): void
    {
        [$t, $ids] = $this->received(1, 0);

        Livewire::actingAs($this->shopMgr)->test(TransferDetail::class, ['transfer' => $t])
            ->assertSee('To resolve')->assertDontSee('Found at warehouse')
            ->call('openResolve', $ids[0], 'found')->set('resolveNote', 'Found it')->call('resolve');

        $this->assertNull(TransferBox::where('box_id', $ids[0])->value('resolution'));
    }

    public function test_open_issues_show_in_the_warehouse_lists(): void
    {
        [$t] = $this->received(2, 1);

        Livewire::actingAs($this->whMgr)->test(WarehouseList::class)
            ->assertViewHas('needs', fn ($n) => $n->pluck('id')->contains($t->id))
            ->assertSee('Resolve')
            ->call('setStatus', 'to_resolve')
            ->assertViewHas('counts', fn ($c) => $c['to_resolve'] >= 1)
            ->assertViewHas('transfers', fn ($p) => $p->pluck('id')->contains($t->id));
    }

    public function test_legacy_transfers_are_resolved_and_closed_by_the_backfill(): void
    {
        [$t, $ids] = $this->received(2, 1);
        DB::table('transfer_boxes')->where('transfer_id', $t->id)->update(['resolution' => null]);
        DB::table('transfers')->where('id', $t->id)->update(['closed_at' => null]);

        $migration = require database_path('migrations/2026_10_01_000004_backfill_transfer_box_resolutions.php');
        $migration->backfill();

        $this->assertSame('found', TransferBox::where('box_id', $ids[1])->value('resolution'));
        $this->assertNotNull($t->fresh()->closed_at);
    }
}
