<?php

namespace Tests\Feature\Transfers;

use App\Enums\BoxStatus;
use App\Enums\TransferStatus;
use App\Livewire\Inventory\Transfers\PackTransfer;
use App\Livewire\Shop\Transfers\ReceiveTransfer;
use App\Models\Box;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\TransferBox;
use App\Models\User;
use App\Services\DayClose\DailySessionService;
use App\Services\Inventory\TransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/** Pack and receive scan screens: remove a wrong box, confirm before ship / complete. */
class TransferScanTest extends TestCase
{
    use DatabaseTransactions;

    private const SIG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

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
        $categoryId = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => "C$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->barcode = '95' . random_int(10000000000, 99999999999);
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

        foreach (range(1, 5) as $i) {
            DB::table('boxes')->insert([
                'product_id' => $this->productId, 'box_code' => 'S' . uniqid() . $i, 'items_total' => 10, 'items_remaining' => 10,
                'status' => 'full', 'location_type' => 'warehouse', 'location_id' => $this->warehouseId,
                'received_by' => $this->owner->id, 'received_at' => $now->copy()->subDay(), 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    private function approved(int $boxes = 3): Transfer
    {
        $this->actingAs($this->shopMgr);
        $t = $this->svc->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id,
            'items' => [['product_id' => $this->productId, 'quantity' => $boxes]],
        ]);
        $this->actingAs($this->whMgr);
        $this->svc->approveTransfer($t);

        return $t->fresh();
    }

    public function test_unpacking_a_box_puts_it_back_on_sale(): void
    {
        $t = $this->approved(3);
        $this->svc->packBoxesByProductBarcode($t, $this->barcode, 2);
        $boxId = TransferBox::where('transfer_id', $t->id)->value('box_id');
        $this->assertSame(BoxStatus::IN_TRANSIT, Box::find($boxId)->status);

        $this->svc->unpackBox($t->fresh(), $boxId);

        $this->assertSame(BoxStatus::FULL, Box::find($boxId)->status);
        $this->assertSame(1, TransferBox::where('transfer_id', $t->id)->count());
        $this->assertSame(10, (int) $t->items()->value('quantity_shipped'));
        $this->assertNotNull($t->fresh()->packed_at);

        // Last box off: packing hasn't started any more.
        $this->svc->unpackBox($t->fresh(), TransferBox::where('transfer_id', $t->id)->value('box_id'));
        $this->assertNull($t->fresh()->packed_at);
    }

    public function test_boxes_cannot_be_unpacked_after_shipping(): void
    {
        $t = $this->approved(1);
        $this->svc->packBoxesByProductBarcode($t, $this->barcode, 1);
        $this->svc->markAsShipped($t->fresh());

        $this->expectException(\Exception::class);
        $this->svc->unpackBox($t->fresh(), TransferBox::where('transfer_id', $t->id)->value('box_id'));
    }

    public function test_pack_screen_packs_removes_and_finishes_with_a_short_reason(): void
    {
        $t = $this->approved(3);

        $pack = Livewire::actingAs($this->whMgr)->test(PackTransfer::class, ['transfer' => $t])
            ->set('scanInput', $this->barcode)->call('scanProduct')
            ->assertSet('showQuantityPanel', true)
            ->set('pendingQty', 2)->call('confirmScannedQuantity')
            ->assertCount('packedBoxes', 2);

        $pack->call('removeBox', $pack->get('packedBoxes')[0]['box_id'])->assertCount('packedBoxes', 1);

        $itemId = $t->items()->value('id');
        $pack->call('openFinish')->assertSet('confirmFinish', true)
            ->assertSee('Packed short')                       // 1 of 3 boxes
            ->call('finishPacking')->assertHasErrors("shortReasons.$itemId")
            ->set("shortReasons.$itemId", 'Two boxes were wet')
            ->call('finishPacking')
            ->assertRedirect(route('warehouse.transfers.show', $t));

        $this->assertSame(TransferStatus::READY, $t->fresh()->status);
        $this->assertSame('Two boxes were wet', $t->items()->value('short_reason'));
        $this->assertSame(1, TransferBox::where('transfer_id', $t->id)->count());
    }

    public function test_pack_screen_sends_a_shipped_transfer_to_its_detail_page(): void
    {
        $t = $this->approved(1);
        $this->svc->packBoxesByProductBarcode($t, $this->barcode, 1);
        $this->svc->markAsShipped($t->fresh());

        Livewire::actingAs($this->whMgr)->test(PackTransfer::class, ['transfer' => $t->fresh()])
            ->assertRedirect(route('warehouse.transfers.show', $t));
    }

    public function test_receive_needs_damage_notes_and_records_unscanned_boxes_as_missing(): void
    {
        $t = $this->approved(3);
        $this->svc->packBoxesByProductBarcode($t, $this->barcode, 3);
        $this->svc->markAsShipped($t->fresh());
        app(DailySessionService::class)->openSession($this->shopMgr, $this->shop->id, 0, business_today()->toDateString());
        $codes = TransferBox::where('transfer_id', $t->id)->with('box')->get()->pluck('box.box_code', 'box_id');

        $rx = Livewire::actingAs($this->shopMgr)->test(ReceiveTransfer::class, ['transfer' => $t->fresh()])
            ->call('openComplete')->assertSet('confirmComplete', false);

        foreach ($codes->take(2) as $code) {
            $rx->set('scanInput', $code)->call('scanBox')->set('pendingQty', 1)->call('confirmScannedQuantity');
        }
        $damagedId = $codes->keys()->first();
        $rx->call('markAsDamaged', $damagedId, true)
            ->call('openComplete')->assertSet('confirmComplete', false)   // no damage note yet
            ->call('updateDamageNotes', $damagedId, 'Wet carton')
            ->call('openComplete')->assertSet('confirmComplete', true)
            ->assertSet('receivedByName', $this->shopMgr->name)
            ->assertSee('will be recorded as')
            ->call('completeReceipt')->assertHasErrors('receiptSignature')   // signature setting is on by default
            ->set('receiptSignature', self::SIG)
            ->call('completeReceipt')
            ->assertRedirect(route('shop.transfers.show', $t));

        $t->refresh();
        $this->assertSame(TransferStatus::RECEIVED, $t->status);
        $this->assertTrue((bool) $t->has_discrepancy);
        $this->assertSame('Wet carton', TransferBox::where('transfer_id', $t->id)->where('box_id', $damagedId)->value('damage_notes'));
        $this->assertSame(1, TransferBox::where('transfer_id', $t->id)->where('is_received', false)->count());
        $this->assertSame($this->shopMgr->name, $t->received_by_name);
        $this->assertSame(self::SIG, $t->receipt_signature);

        // Goods received note: everyone on the transfer can print it.
        $this->actingAs($this->shopMgr)->get(route('shop.transfers.received-note', $t))->assertOk()
            ->assertSee('Received with issues')->assertSee('Wet carton')->assertSee('Missing');
        $this->actingAs($this->whMgr)->get(route('warehouse.transfers.received-note', $t))->assertOk();
    }

    public function test_scan_pages_render(): void
    {
        $t = $this->approved(2);
        $this->actingAs($this->whMgr)->get(route('warehouse.transfers.pack', $t))->assertOk()->assertSee('Scan a product barcode');
    }
}
