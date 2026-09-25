<?php

namespace Tests\Feature\Warehouse;

use App\Livewire\Warehouse\Sales\FulfillmentQueue;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Warehouse fulfillment — KPIs, modal dispatch flow, paid-in-full rule.
 * Runs on smart_inventory_test only (phpunit.xml + TestCase guard).
 */
class FulfillmentQueueTest extends TestCase
{
    use DatabaseTransactions;

    private int $warehouseId;
    private int $shopId;
    private int $productId;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $u   = uniqid();
        $now = now()->toDateTimeString();

        $this->warehouseId = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => 'W' . substr($u, -8), 'created_at' => $now, 'updated_at' => $now]);
        $this->shopId      = DB::table('shops')->insertGetId(['name' => "Shop $u", 'code' => 'S' . substr($u, -8), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $categoryId        = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => 'C' . substr($u, -8), 'created_at' => $now, 'updated_at' => $now]);
        $this->productId   = DB::table('products')->insertGetId([
            'sku' => "SKU$u", 'name' => "Air Max $u", 'items_per_box' => 12, 'category_id' => $categoryId,
            'purchase_price' => 1000, 'selling_price' => 2500, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->manager = User::forceCreate([
            'name' => 'WH Manager', 'email' => "wm$u@example.test", 'password' => bcrypt('x'),
            'role' => 'warehouse_manager', 'location_type' => 'warehouse', 'location_id' => $this->warehouseId, 'must_change_password' => false, 'is_active' => true,
        ]);
    }

    /** A pending warehouse-direct sale with $boxes warehouse boxes and the given payments. */
    private function pendingSale(int $boxes, array $payments, string $minutesAgo = '10', array $extra = []): Sale
    {
        $now  = now()->toDateTimeString();
        $total = array_sum($payments);
        $saleId = DB::table('sales')->insertGetId($extra + [
            'sale_number' => 'WS-' . strtoupper(substr(uniqid(), -6)), 'shop_id' => $this->shopId, 'sold_by' => $this->manager->id,
            'sale_date' => now()->subMinutes((int) $minutesAgo)->toDateTimeString(), 'type' => 'full_box', 'payment_method' => array_key_first($payments),
            'total' => $total, 'subtotal' => $total, 'customer_name' => 'Aline Uwase',
            'fulfillment_type' => 'warehouse_direct', 'fulfillment_status' => 'pending', 'fulfillment_method' => 'pickup',
            'source_warehouse_id' => $this->warehouseId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        for ($i = 0; $i < $boxes; $i++) {
            $boxId = DB::table('boxes')->insertGetId([
                'product_id' => $this->productId, 'box_code' => 'BX' . uniqid(), 'items_total' => 12, 'items_remaining' => 0,
                'location_type' => 'warehouse', 'location_id' => $this->warehouseId, 'received_by' => $this->manager->id,
                'received_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('sale_items')->insert([
                'sale_id' => $saleId, 'product_id' => $this->productId, 'box_id' => $boxId, 'quantity_sold' => 12, 'is_full_box' => true,
                'original_unit_price' => 30000, 'actual_unit_price' => 30000, 'line_total' => 30000, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        foreach ($payments as $method => $amount) {
            DB::table('sale_payments')->insert(['sale_id' => $saleId, 'payment_method' => $method, 'amount' => $amount, 'created_at' => $now, 'updated_at' => $now]);
        }

        return Sale::findOrFail($saleId);
    }

    public function test_kpis_and_queue_rows(): void
    {
        $this->pendingSale(2, ['cash' => 60000], '10');
        $this->pendingSale(1, ['credit' => 30000], '150');   // old + on credit

        Livewire::actingAs($this->manager)
            ->test(FulfillmentQueue::class)
            ->set('dispatchMethod', 'queue')
            ->assertSee('Fulfillment')
            ->assertSee('Awaiting dispatch')
            ->assertViewHas('stats', fn ($s) => $s['pending'] === 2 && $s['pending_boxes'] === 3 && $s['over_2h'] === 1 && $s['pending_transport'] === 0)
            // Warehouse staff never see payment status (credit, balances)
            ->assertDontSee('Balance due')
            ->assertDontSee('balance due')
            ->assertSee('Dispatch');
    }


    public function test_dispatch_via_modal_requires_name_and_signature(): void
    {
        $sale = $this->pendingSale(2, ['cash' => 60000]);

        $c = Livewire::actingAs($this->manager)
            ->test(FulfillmentQueue::class)
            ->set('dispatchMethod', 'queue')
            ->call('requestFulfillment', $sale->id, 'queue')
            ->assertSet('confirmingFulfillmentId', $sale->id)
            ->assertSee('Dispatch ' . $sale->sale_number)
            ->assertSee('Hand over 2 boxes');

        $c->call('markFulfilled', $sale->id)
            ->assertHasErrors(['recipientName', 'signatureData']);

        $c->set('recipientName', 'Jean Bosco')
            ->set('signatureData', 'data:image/png;base64,iVBORw0KGgo=')
            ->call('markFulfilled', $sale->id)
            ->assertHasNoErrors()
            ->assertSet('confirmingFulfillmentId', null)
            ->assertDispatched('notification');

        $sale->refresh();
        $this->assertSame('fulfilled', $sale->fulfillment_status);
        $this->assertSame('Jean Bosco', $sale->fulfillment_recipient_name);
        $this->assertSame($this->manager->id, (int) $sale->fulfillment_confirmed_by);

        // Shows up in history and today's KPI; the detail drawer opens on click
        $c->call('setTab', 'history')
            ->assertViewHas('stats', fn ($s) => $s['today'] === 1 && $s['today_boxes'] === 2 && $s['pending'] === 0)
            ->assertSee($sale->sale_number)
            ->call('toggleHistory', $sale->id)
            ->assertSee('Handover')
            ->assertSee('Jean Bosco')
            ->call('closeHistory')
            ->assertSet('expandedHistoryId', null);
    }

    public function test_signature_is_optional_when_the_setting_is_off(): void
    {
        app(\App\Services\SettingsService::class)->set('fulfillment_require_signature', false);
        $sale = $this->pendingSale(1, ['credit' => 30000]);

        $c = Livewire::actingAs($this->manager)
            ->test(FulfillmentQueue::class)
            ->set('dispatchMethod', 'queue')
            ->assertSet('requireSignature', false)
            ->call('requestFulfillment', $sale->id, 'queue')
            ->assertDontSee('data-sig-canvas', false)
            ->assertDontSee('balance due');

        $c->call('markFulfilled', $sale->id)->assertHasErrors('recipientName')->assertHasNoErrors('signatureData');
        $c->set('recipientName', 'Claudine Umutoni')->call('markFulfilled', $sale->id)->assertHasNoErrors();

        $sale->refresh();
        $this->assertSame('fulfilled', $sale->fulfillment_status);
        $this->assertNull($sale->fulfillment_signature);
    }

    public function test_signature_required_by_default(): void
    {
        $this->assertTrue(app(\App\Services\SettingsService::class)->fulfillmentRequireSignature());
        $sale = $this->pendingSale(1, ['cash' => 30000]);

        Livewire::actingAs($this->manager)
            ->test(FulfillmentQueue::class)
            ->call('requestFulfillment', $sale->id)
            ->assertSee('data-sig-canvas', false)
            ->set('recipientName', 'Jean Bosco')
            ->call('markFulfilled', $sale->id)
            ->assertHasErrors('signatureData');
    }

    public function test_picking_slip_has_no_price_note_or_payment_info(): void
    {
        $sale = $this->pendingSale(1, ['credit' => 30000]);

        $this->actingAs($this->manager)
            ->get(route('warehouse.sales.fulfillment.picking-slip', $sale->id))
            ->assertOk()
            ->assertDontSee('Prices are not shown')
            ->assertDontSee('customer receipt if needed')
            ->assertDontSee('Credit recorded')
            ->assertDontSee('30,000')
            ->assertSee($sale->sale_number);
    }

    public function test_shop_manager_cannot_dispatch(): void
    {
        $sale = $this->pendingSale(1, ['cash' => 30000]);
        $owner = User::forceCreate(['name' => 'Owner', 'email' => 'o' . uniqid() . '@example.test', 'password' => bcrypt('x'), 'role' => 'owner']);
        $shopManager = User::forceCreate([
            'name' => 'Shop Mgr', 'email' => 'sm' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopId,
        ]);

        // Mount as the owner (who can see a warehouse), then act as a shop manager
        $c = Livewire::actingAs($owner)->test(FulfillmentQueue::class);
        $c->set('warehouseId', $this->warehouseId);
        $this->actingAs($shopManager);
        $c->call('requestFulfillment', $sale->id)
            ->assertSet('confirmingFulfillmentId', null)
            ->assertDispatched('notification');
    }

    public function test_scan_lookup_states(): void
    {
        $pending = $this->pendingSale(1, ['cash' => 30000], '5', ['fulfillment_pickup_code' => 'ABC123DEF456']);

        $c = Livewire::actingAs($this->manager)
            ->test(FulfillmentQueue::class)
            ->set('dispatchMethod', 'scan')
            ->set('receiptCode', 'abc-123-def-456')
            ->call('scanReceipt')
            ->assertSet('scannedCode', 'ABC123DEF456')
            ->assertSee('Ready to dispatch')
            ->assertSee($pending->sale_number);

        $c->set('receiptCode', 'ZZZ999')->call('scanReceipt')->assertSee('No order found');

        DB::table('sales')->where('id', $pending->id)->update(['fulfillment_status' => 'cancelled']);
        $c->set('receiptCode', 'ABC123DEF456')->call('scanReceipt')->assertSee('Cancelled — do not dispatch', false);
    }
}
