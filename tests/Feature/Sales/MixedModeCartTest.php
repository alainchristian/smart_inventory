<?php

namespace Tests\Feature\Sales;

use App\Livewire\Shop\Sales\UnifiedPos;
use App\Models\DailySession;
use App\Models\User;
use App\Services\Sales\SaleService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * One product can sit in the cart as full boxes AND loose items at the same
 * time (separate lines), with stock checked across both lines.
 * Runs on smart_inventory_test only (phpunit.xml + TestCase guard).
 */
class MixedModeCartTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private int $warehouseId;
    private User $seller;
    private int $productId;

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
            'name' => 'Seller', 'email' => "mm$u@example.test", 'password' => bcrypt('x'), 'is_active' => true, 'must_change_password' => false,
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopId,
        ]);
        DailySession::forceCreate([
            'shop_id' => $this->shopId, 'session_date' => business_today()->toDateString(), 'status' => 'open',
            'opening_balance' => 0, 'opened_by' => $this->seller->id, 'opened_at' => now(),
        ]);

        $cat = DB::table('categories')->insertGetId(['name' => "Footwear $u", 'code' => 'F' . substr($u, -8), 'created_at' => $now, 'updated_at' => $now]);
        $this->productId = DB::table('products')->insertGetId([
            'sku' => 'SKU' . $u, 'name' => 'Adidas Ultraboost', 'items_per_box' => 12, 'category_id' => $cat,
            'purchase_price' => 1000, 'selling_price' => 90000, 'box_selling_price' => 1020000, 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $s = app(SettingsService::class);
        $s->set('allow_individual_item_sales', true);
        $s->set('individual_sale_category_ids', [$cat]);
    }

    private function boxes(string $location, int $count): void
    {
        $locId = $location === 'shop' ? $this->shopId : $this->warehouseId;
        for ($i = 0; $i < $count; $i++) {
            DB::table('boxes')->insert([
                'product_id' => $this->productId, 'box_code' => 'B' . uniqid(), 'items_total' => 12, 'items_remaining' => 12,
                'status' => 'full', 'location_type' => $location, 'location_id' => $locId,
                'received_by' => $this->seller->id, 'received_at' => now()->subDays(10 - $i), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function add($pos, string $source, string $mode, int $qty, ?int $price = null)
    {
        $pos->call('selectProduct', $this->productId, $source)
            ->set('stagingMode', $mode)
            ->set('stagingQty', $qty);
        if ($price !== null) {
            $pos->set('stagingPrice', $price);
        }

        return $pos->call('confirmAddToCart');
    }

    public function test_cart_holds_boxes_and_loose_items_of_the_same_product(): void
    {
        $this->boxes('shop', 3);   // 36 items
        $pos = Livewire::actingAs($this->seller)->test(UnifiedPos::class);

        $this->add($pos, 'shop', 'box', 2);
        $this->add($pos, 'shop', 'item', 10);

        $pos->assertCount('cart', 2)
            ->assertSet('cart.0.mode', 'box')->assertSet('cart.0.qty', 2)
            ->assertSet('cart.1.mode', 'item')->assertSet('cart.1.qty', 10);

        // Adding more of the same mode joins that line instead of replacing it
        $this->add($pos, 'shop', 'item', 2);
        $pos->assertCount('cart', 2)->assertSet('cart.1.qty', 12);

        // 3 boxes (36) + 12 items = 48 > 36 in stock → refused, cart unchanged
        $this->add($pos, 'shop', 'box', 1);
        $pos->assertCount('cart', 2)->assertSet('cart.0.qty', 2)
            ->assertDispatched('notification', fn ($n, $p) => str_contains(json_encode($p), 'Not enough stock'));
    }

    public function test_a_different_price_keeps_its_own_line(): void
    {
        $this->boxes('shop', 3);
        $pos = Livewire::actingAs($this->seller)->test(UnifiedPos::class);

        $this->add($pos, 'shop', 'item', 2);            // 90,000 each
        $this->add($pos, 'shop', 'item', 1, 85000);     // discounted

        $pos->assertCount('cart', 2)
            ->assertSet('cart.0.price', 90000)->assertSet('cart.0.qty', 2)
            ->assertSet('cart.1.price', 85000)->assertSet('cart.1.qty', 1);
    }

    public function test_editing_a_line_into_an_existing_mode_merges_them(): void
    {
        $this->boxes('shop', 3);
        $pos = Livewire::actingAs($this->seller)->test(UnifiedPos::class);

        $this->add($pos, 'shop', 'box', 1);
        $this->add($pos, 'shop', 'item', 3);

        // Edit the item line → switch it to boxes (1) → folds into the box line
        $pos->call('openEditItem', 1)
            ->set('stagingMode', 'box')->set('stagingQty', 1)->set('stagingPrice', 1020000)
            ->call('confirmAddToCart')
            ->assertCount('cart', 1)
            ->assertSet('cart.0.mode', 'box')->assertSet('cart.0.qty', 2);
    }

    public function test_receipt_shows_each_cart_line_and_can_be_closed(): void
    {
        $this->boxes('warehouse', 3);
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Eric Niyonzima', 'phone' => '0788' . random_int(100000, 999999), 'registered_by' => $this->seller->id,
            'total_credit_given' => 0, 'total_repaid' => 0, 'outstanding_balance' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $pos = Livewire::actingAs($this->seller)->test(UnifiedPos::class);
        $this->add($pos, 'warehouse', 'box', 2);
        $this->add($pos, 'warehouse', 'item', 10);

        $pos->call('selectCustomer', $customerId)
            ->set('fulfillmentMethod', 'pickup')
            ->set('payAmt_cash', 2 * 1020000 + 10 * 90000)
            ->call('completeSale')
            ->assertSet('showReceiptModal', true)
            ->assertSee('Sale Complete')
            ->assertSee('2 boxes × 1,020,000')
            ->assertSee('10 items × 90,000')
            ->assertSee('2,040,000')
            ->assertSee('900,000')
            ->assertDontSee('×4')
            ->call('closeReceipt')
            ->assertSet('showReceiptModal', false);
    }

    public function test_warehouse_sale_with_boxes_and_items_of_one_product(): void
    {
        $this->boxes('warehouse', 3);   // 36 items, all sealed
        $this->actingAs($this->seller);

        // Item line FIRST: it opens a sealed box, leaving enough sealed ones for the box line
        $sale = (new SaleService())->createMixedSale([
            'shop_id' => $this->shopId, 'source_warehouse_id' => $this->warehouseId, 'fulfillment_method' => 'pickup',
            'payments' => [['method' => 'cash', 'amount' => 10 * 90000 + 2 * 1020000, 'reference' => null]],
            'items' => [
                ['product_id' => $this->productId, 'source' => 'warehouse', 'source_id' => $this->warehouseId, 'mode' => 'item', 'qty' => 10, 'items_per_box' => 12, 'price' => 90000, 'price_modified' => false],
                ['product_id' => $this->productId, 'source' => 'warehouse', 'source_id' => $this->warehouseId, 'mode' => 'box',  'qty' => 2,  'items_per_box' => 12, 'price' => 1020000, 'price_modified' => false],
            ],
        ]);

        $this->assertSame(2, $sale->items()->where('is_full_box', true)->count());
        $this->assertSame(10, (int) $sale->items()->where('is_full_box', false)->sum('quantity_sold'));
        $this->assertSame(2, (int) DB::table('boxes')->where('product_id', $this->productId)->where('status', 'empty')->count());
        $this->assertSame(2, (int) DB::table('boxes')->where('product_id', $this->productId)->where('status', 'partial')->value('items_remaining'));
        $this->assertSame(10 * 90000 + 2 * 1020000, (int) $sale->fresh()->total);
    }
}
