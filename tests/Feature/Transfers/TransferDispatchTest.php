<?php

namespace Tests\Feature\Transfers;

use App\Enums\TransferStatus;
use App\Livewire\Transfers\TransferDetail;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\User;
use App\Services\Inventory\TransferService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/** Transfer process phase 3: ready → dispatch (hand-over), picking list, delivery note. */
class TransferDispatchTest extends TestCase
{
    use DatabaseTransactions;

    private const SIG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private int $warehouseId;
    private Shop $shop;
    private Shop $otherShop;
    private string $barcode;
    private int $productId;
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
        $this->shop = Shop::forceCreate(['name' => "Shop $u", 'code' => "S$u", 'is_active' => true, 'default_warehouse_id' => $this->warehouseId,
            'sells_all_categories' => true, 'phone' => '0788 000 111', 'manager_name' => 'Alice Shopkeeper']);
        $this->otherShop = Shop::forceCreate(['name' => "Other $u", 'code' => "O$u", 'is_active' => true, 'default_warehouse_id' => $this->warehouseId, 'sells_all_categories' => true]);
        $categoryId = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => "C$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->barcode = '91' . random_int(10000000000, 99999999999);
        $this->productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId, 'sku' => "SKU$u", 'name' => "Sandal $u", 'barcode' => $this->barcode, 'items_per_box' => 10, 'is_active' => true,
            'purchase_price' => 1000, 'selling_price' => 3000, 'box_selling_price' => 28000, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $user = fn ($role, $type = null, $id = null) => User::forceCreate([
            'name' => ucfirst($role) . " $u", 'email' => $role . uniqid() . '@example.test', 'password' => 'x', 'is_active' => true,
            'must_change_password' => false, 'role' => $role, 'location_type' => $type, 'location_id' => $id,
        ]);
        $this->owner = $user('owner');
        $this->whMgr = $user('warehouse_manager', 'warehouse', $this->warehouseId);
        $this->shopMgr = $user('shop_manager', 'shop', $this->shop->id);
        $this->otherShopMgr = $user('shop_manager', 'shop', $this->otherShop->id);
        $this->svc = app(TransferService::class);
        foreach (range(1, 3) as $i) {
            DB::table('boxes')->insert([
                'product_id' => $this->productId, 'box_code' => 'DSP' . uniqid() . $i, 'items_total' => 10, 'items_remaining' => 10,
                'status' => 'full', 'location_type' => 'warehouse', 'location_id' => $this->warehouseId,
                'received_by' => $this->owner->id, 'received_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    private function ready(int $boxes = 2): Transfer
    {
        $this->actingAs($this->shopMgr);
        $t = $this->svc->createTransferRequest([
            'from_warehouse_id' => $this->warehouseId, 'to_shop_id' => $this->shop->id, 'notes' => 'For the weekend',
            'items' => [['product_id' => $this->productId, 'quantity' => $boxes]],
        ]);
        $this->actingAs($this->whMgr);
        $this->svc->approveTransfer($t);
        $this->svc->packBoxesByProductBarcode($t->fresh(), $this->barcode, $boxes);
        $this->svc->finishPacking($t->fresh());

        return $t->fresh();
    }

    public function test_dispatch_records_the_hand_over_and_needs_the_drivers_signature(): void
    {
        app(SettingsService::class)->set('transfer_require_signature', true);
        $t = $this->ready();

        $detail = Livewire::actingAs($this->whMgr)->test(TransferDetail::class, ['transfer' => $t])
            ->assertSee('Dispatch')
            ->call('openDispatch')->assertSet('showDispatch', true)
            ->set('transporterName', 'Kigali Express')
            ->set('handedToName', 'Jean Driver')
            ->set('instructions', 'Keep dry, call the shop on arrival')
            ->set('expectedArrival', local_time(now()->addHours(3))->format('Y-m-d\TH:i'))
            ->call('dispatchTransfer')
            ->assertHasErrors('handoverSignature')
            ->set('handoverSignature', self::SIG)
            ->call('dispatchTransfer')
            ->assertHasNoErrors()
            ->assertSet('justDispatched', true)
            ->assertDispatched('transfer-dispatched');

        $t->refresh();
        $this->assertSame(TransferStatus::IN_TRANSIT, $t->status);
        $this->assertSame('Jean Driver', $t->handed_to_name);
        $this->assertSame(self::SIG, $t->handover_signature);
        $this->assertSame('Kigali Express', $t->transporter->name);
        $this->assertSame($this->whMgr->id, $t->shipped_by);
        $this->assertNotNull($t->expected_arrival_at);
        $this->assertSame('Keep dry, call the shop on arrival', $t->events()->where('action', 'dispatched')->value('note'));
    }

    public function test_the_signature_is_optional_when_the_setting_is_off(): void
    {
        app(SettingsService::class)->set('transfer_require_signature', false);
        $t = $this->ready();

        Livewire::actingAs($this->whMgr)->test(TransferDetail::class, ['transfer' => $t])
            ->call('openDispatch')
            ->set('transporterName', 'Kigali Express')
            ->set('handedToName', 'Jean Driver')
            ->call('dispatchTransfer')
            ->assertHasNoErrors();

        $this->assertSame(TransferStatus::IN_TRANSIT, $t->fresh()->status);
    }

    public function test_the_shop_cannot_dispatch(): void
    {
        $t = $this->ready();

        Livewire::actingAs($this->shopMgr)->test(TransferDetail::class, ['transfer' => $t])
            ->assertDontSee('Dispatch now')
            ->call('openDispatch')->set('transporterName', 'X')->set('handedToName', 'Someone')
            ->set('handoverSignature', self::SIG)
            ->call('dispatchTransfer');

        $this->assertSame(TransferStatus::READY, $t->fresh()->status);
    }

    public function test_documents_are_only_for_people_on_the_transfer(): void
    {
        $t = $this->ready();

        $this->actingAs($this->whMgr)->get(route('warehouse.transfers.picking-list', $t))->assertOk()->assertSee('Picking list')->assertSee($this->barcode);
        $this->actingAs($this->shopMgr)->get(route('shop.transfers.delivery-note', $t))->assertOk();
        $this->actingAs($this->otherShopMgr)->get(route('shop.transfers.delivery-note', $t))->assertForbidden();
        // Nothing received yet: no goods received note.
        $this->actingAs($this->shopMgr)->get(route('shop.transfers.received-note', $t))->assertNotFound();
    }

    public function test_the_delivery_note_carries_the_trail_the_instructions_and_the_receiver(): void
    {
        $t = $this->ready();
        $this->actingAs($this->whMgr);
        $this->svc->dispatch($t, null, ['handed_to_name' => 'Jean Driver', 'transporter_instructions' => 'Handle with care',
            'handover_signature' => self::SIG, 'expected_arrival_at' => now()->addHours(2)]);

        $this->get(route('warehouse.transfers.delivery-note', $t))->assertOk()
            ->assertSee('Requested')->assertSee('Approved')->assertSee('Dispatched')->assertSee('Expected')
            ->assertSee('Handle with care')->assertSee('Jean Driver')
            ->assertSee('Alice Shopkeeper')->assertSee('0788 000 111')
            ->assertSee(self::SIG, false);
    }
}
