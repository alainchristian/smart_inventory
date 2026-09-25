<?php

namespace Tests\Feature\Sales;

use App\Livewire\Shop\Sales\UnifiedPos;
use App\Livewire\Warehouse\Sales\FulfillmentQueue;
use App\Models\DailySession;
use App\Models\Sale;
use App\Models\User;
use App\Services\Sales\SaleService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Loose-item sales from WAREHOUSE stock for ticked categories.
 * Runs on smart_inventory_test only (phpunit.xml + TestCase guard).
 */
class WarehouseItemSalesTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private int $warehouseId;
    private User $seller;
    private int $catLoose;
    private int $catBoxOnly;

    protected function setUp(): void
    {
        parent::setUp();

        $u   = uniqid();
        $now = now()->toDateTimeString();

        $this->warehouseId = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => 'W' . substr($u, -8), 'created_at' => $now, 'updated_at' => $now]);
        $this->shopId = DB::table('shops')->insertGetId([
            'name' => "Shop $u", 'code' => 'S' . substr($u, -8), 'is_active' => true,
            'default_warehouse_id' => $this->warehouseId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->seller = User::forceCreate([
            'name' => 'Seller', 'email' => "ws$u@example.test", 'password' => bcrypt('x'), 'is_active' => true, 'must_change_password' => false,
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopId,
        ]);
        DailySession::forceCreate([
            'shop_id' => $this->shopId, 'session_date' => business_today()->toDateString(), 'status' => 'open',
            'opening_balance' => 0, 'opened_by' => $this->seller->id, 'opened_at' => now(),
        ]);

        $this->catLoose   = DB::table('categories')->insertGetId(['name' => "Socks $u", 'code' => 'SO' . substr($u, -7), 'created_at' => $now, 'updated_at' => $now]);
        $this->catBoxOnly = DB::table('categories')->insertGetId(['name' => "Shoes $u", 'code' => 'SH' . substr($u, -7), 'created_at' => $now, 'updated_at' => $now]);

        $s = app(SettingsService::class);
        $s->set('allow_individual_item_sales', true);
        $s->set('individual_sale_category_ids', [$this->catLoose]);
    }

    private function product(int $categoryId, string $name): int
    {
        $now = now()->toDateTimeString();

        return DB::table('products')->insertGetId([
            'sku' => 'SKU' . uniqid(), 'name' => $name, 'items_per_box' => 10, 'category_id' => $categoryId,
            'purchase_price' => 1000, 'selling_price' => 3000, 'box_selling_price' => 28000, 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function whBox(int $productId, string $status, int $remaining, string $receivedAt): int
    {
        return DB::table('boxes')->insertGetId([
            'product_id' => $productId, 'box_code' => 'B' . uniqid(), 'items_total' => 10, 'items_remaining' => $remaining,
            'status' => $status, 'location_type' => 'warehouse', 'location_id' => $this->warehouseId,
            'received_by' => $this->seller->id, 'received_at' => $receivedAt, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function sell(array $line): Sale
    {
        $this->actingAs($this->seller);
        $amount = $line['mode'] === 'item' ? $line['qty'] * $line['price'] : $line['qty'] * $line['price'];

        return (new SaleService())->createMixedSale([
            'shop_id'             => $this->shopId,
            'source_warehouse_id' => $this->warehouseId,
            'fulfillment_method'  => 'pickup',
            'payments'            => [['method' => 'cash', 'amount' => $amount, 'reference' => null]],
            'items'               => [$line + ['source' => 'warehouse', 'source_id' => $this->warehouseId, 'items_per_box' => 10, 'price_modified' => false]],
        ]);
    }

    public function test_pos_offers_item_mode_for_ticked_warehouse_category(): void
    {
        $socks = $this->product($this->catLoose, 'WH Socks');
        $this->whBox($socks, 'full', 10, '2026-01-01');

        Livewire::actingAs($this->seller)->test(UnifiedPos::class)
            ->call('selectProduct', $socks, 'warehouse')
            ->assertSet('showAddModal', true)
            ->assertSet('stagingProduct.individual_sale_allowed', true)
            ->assertSee('Individual Items')
            ->set('stagingMode', 'item')->set('stagingQty', 4)->set('stagingPrice', 3000)
            ->call('confirmAddToCart')
            ->assertCount('cart', 1)
            ->assertSet('cart.0.mode', 'item')
            ->assertSet('cart.0.source', 'warehouse');
    }

    public function test_pos_box_only_warehouse_category_has_no_item_mode_and_blocks_opened_boxes(): void
    {
        $shoe = $this->product($this->catBoxOnly, 'WH Shoe');
        $this->whBox($shoe, 'full', 10, '2026-01-01');

        Livewire::actingAs($this->seller)->test(UnifiedPos::class)
            ->call('selectProduct', $shoe, 'warehouse')
            ->assertSet('stagingProduct.individual_sale_allowed', false)
            ->assertDontSee('Individual Items')
            ->set('stagingMode', 'item')->set('stagingQty', 2)
            ->call('confirmAddToCart')
            ->assertCount('cart', 0);

        $opened = $this->product($this->catBoxOnly, 'WH Shoe opened');
        $this->whBox($opened, 'partial', 6, '2026-01-01');

        Livewire::actingAs($this->seller)->test(UnifiedPos::class)
            ->call('selectProduct', $opened, 'warehouse')
            ->assertSet('showAddModal', false)
            ->assertDispatched('notification', fn ($n, $p) => str_contains(json_encode($p), 'sold by full box only'));
    }

    public function test_loose_items_come_from_opened_boxes_first(): void
    {
        $socks  = $this->product($this->catLoose, 'WH Socks FIFO');
        $sealed = $this->whBox($socks, 'full', 10, '2026-01-01');     // older, but sealed
        $opened = $this->whBox($socks, 'partial', 3, '2026-02-01');   // newer, already opened

        $sale = $this->sell(['product_id' => $socks, 'mode' => 'item', 'qty' => 5, 'price' => 3000]);

        $this->assertSame(0, (int) DB::table('boxes')->where('id', $opened)->value('items_remaining'), 'opened box emptied first');
        $this->assertSame('empty', DB::table('boxes')->where('id', $opened)->value('status'));
        $this->assertSame(8, (int) DB::table('boxes')->where('id', $sealed)->value('items_remaining'), 'then 2 from the sealed box');
        $this->assertSame('partial', DB::table('boxes')->where('id', $sealed)->value('status'));

        $this->assertSame(15000, (int) $sale->fresh()->total);
        $this->assertSame(0, $sale->items()->where('is_full_box', true)->count());
        $this->assertSame(5, (int) $sale->items()->sum('quantity_sold'));
        $this->assertSame(2, DB::table('box_movements')->where('reference_id', $sale->id)->where('movement_type', 'direct_sale')->count());
        $this->assertSame('pending', $sale->fresh()->fulfillment_status);

        // Fulfillment shows loose items, not "2 boxes"
        $sale->load('items.box', 'items.product');
        $this->assertSame([0, 5], FulfillmentQueue::packTotals($sale));
        $this->assertSame('5 items', FulfillmentQueue::packLabel(...FulfillmentQueue::packTotals($sale)));
    }

    public function test_full_box_sale_never_takes_an_opened_box(): void
    {
        $socks = $this->product($this->catLoose, 'WH Socks sealed');
        $this->whBox($socks, 'partial', 4, '2026-01-01');   // older but opened

        $this->expectExceptionMessage('full boxes');
        $this->sell(['product_id' => $socks, 'mode' => 'box', 'qty' => 1, 'price' => 28000]);
    }

    public function test_server_rejects_loose_warehouse_items_for_box_only_category(): void
    {
        $shoe = $this->product($this->catBoxOnly, 'WH Shoe server');
        $this->whBox($shoe, 'full', 10, '2026-01-01');

        $this->expectException(\DomainException::class);
        $this->sell(['product_id' => $shoe, 'mode' => 'item', 'qty' => 2, 'price' => 3000]);
    }

    public function test_pack_label_mixes_boxes_and_items(): void
    {
        $this->assertSame('1 box + 5 items', FulfillmentQueue::packLabel(1, 5));
        $this->assertSame('2 boxes', FulfillmentQueue::packLabel(2, 0));
        $this->assertSame('×2 + 1 item', FulfillmentQueue::packQty(['boxes' => 2, 'items' => 1]));
    }
}
