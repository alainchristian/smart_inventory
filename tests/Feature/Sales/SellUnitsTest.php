<?php

namespace Tests\Feature\Sales;

use App\Livewire\Products\EditProduct;
use App\Livewire\Shop\Sales\UnifiedPos;
use App\Models\DailySession;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\Sales\SaleService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Selling loose in packs (Dozen, Pair…) — stock stays counted in pieces.
 * Runs on smart_inventory_test only (phpunit.xml + TestCase guard).
 */
class SellUnitsTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private int $warehouseId;
    private User $seller;
    private User $owner;
    private int $catLoose;
    private Product $plates;   // 24 per box, 500 a piece, Dozen = 5,000

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
            'name' => 'Seller', 'email' => "su$u@example.test", 'password' => 'x', 'is_active' => true, 'must_change_password' => false,
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopId,
        ]);
        $this->owner = User::forceCreate([
            'name' => 'Owner', 'email' => "so$u@example.test", 'password' => 'x', 'is_active' => true, 'must_change_password' => false,
            'role' => 'owner',
        ]);
        DailySession::forceCreate([
            'shop_id' => $this->shopId, 'session_date' => business_today()->toDateString(), 'status' => 'open',
            'opening_balance' => 0, 'opened_by' => $this->seller->id, 'opened_at' => now(),
        ]);

        $this->catLoose = DB::table('categories')->insertGetId(['name' => "Kitchen $u", 'code' => 'KI' . substr($u, -7), 'created_at' => $now, 'updated_at' => $now]);
        $s = app(SettingsService::class);
        $s->set('allow_individual_item_sales', true);
        $s->set('individual_sale_category_ids', [$this->catLoose]);
        $s->set('price_override_threshold', 20);

        $this->plates = Product::forceCreate([
            'sku' => 'PL' . $u, 'name' => "Plate $u", 'items_per_box' => 24, 'category_id' => $this->catLoose,
            'purchase_price' => 300, 'selling_price' => 500, 'box_selling_price' => 11000, 'is_active' => true,
            'low_stock_threshold' => 10, 'reorder_point' => 20,
        ]);
        $this->plates->sellUnits()->create(['name' => 'Dozen', 'size' => 12, 'price' => 5000]);
    }

    private function box(string $loc, int $locId, int $remaining, string $receivedAt = '2026-01-01'): int
    {
        return DB::table('boxes')->insertGetId([
            'product_id' => $this->plates->id, 'box_code' => 'B' . uniqid(), 'items_total' => 24, 'items_remaining' => $remaining,
            'status' => $remaining < 24 ? 'partial' : 'full', 'location_type' => $loc, 'location_id' => $locId,
            'received_by' => $this->seller->id, 'received_at' => $receivedAt, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function sell(array $line, string $source = 'shop'): Sale
    {
        $this->actingAs($this->seller);

        return (new SaleService())->createMixedSale([
            'shop_id'             => $this->shopId,
            'source_warehouse_id' => $source === 'warehouse' ? $this->warehouseId : null,
            'fulfillment_method'  => $source === 'warehouse' ? 'pickup' : null,
            'payments'            => [['method' => 'cash', 'amount' => $line['qty'] * $line['price'], 'reference' => null]],
            'items'               => [$line + [
                'product_id' => $this->plates->id, 'source' => $source,
                'source_id' => $source === 'warehouse' ? $this->warehouseId : $this->shopId,
                'mode' => 'item', 'items_per_box' => 24, 'price_modified' => false,
            ]],
        ]);
    }

    // ── Product form ─────────────────────────────────────────────────────

    public function test_owner_adds_packs_in_the_product_form(): void
    {
        $this->plates->sellUnits()->delete();

        Livewire::actingAs($this->owner)->test(EditProduct::class, ['product' => $this->plates])
            ->assertSee('Selling Loose')
            ->call('addSellUnit', 'dozen')
            ->assertSet('sellUnits.0.size', 12)
            ->assertSet('sellUnits.0.price', 12 * 458)             // 11,000 / 24 ≈ 458 a piece
            ->set('sellUnits.0.price', 5000)
            ->call('addSellUnit', 'half_dozen')
            ->set('sellSinglePieces', false)
            ->call('update')
            ->assertHasNoErrors();

        $this->plates->refresh();
        $this->assertFalse($this->plates->sell_single_pieces);
        $this->assertSame([[6, 'Half-dozen'], [12, 'Dozen']], $this->plates->sellUnits->map(fn ($x) => [$x->size, $x->name])->all());
        $this->assertSame(5000, $this->plates->sellUnits->firstWhere('size', 12)->price);
    }

    public function test_a_pack_must_be_smaller_than_a_box(): void
    {
        Livewire::actingAs($this->owner)->test(EditProduct::class, ['product' => $this->plates])
            ->call('addSellUnit', 'custom')
            ->set('sellUnits.1.name', 'Crate')
            ->set('sellUnits.1.size', 24)
            ->call('update')
            ->assertHasErrors('sellUnits.1.size');
    }

    // ── Selling ──────────────────────────────────────────────────────────

    public function test_a_dozen_spanning_two_boxes_takes_12_pieces_at_the_dozen_price(): void
    {
        $opened = $this->box('shop', $this->shopId, 5, '2026-01-01');
        $sealed = $this->box('shop', $this->shopId, 24, '2026-02-01');

        $sale = $this->sell(['qty' => 1, 'unit_size' => 12, 'price' => 5000]);

        $this->assertSame(5000, (int) $sale->total);
        $this->assertFalse((bool) $sale->has_price_override);
        $rows = $sale->items()->orderBy('id')->get();
        $this->assertSame([5, 7], $rows->pluck('quantity_sold')->all());
        $this->assertSame(5000, (int) $rows->sum('line_total'), 'rows add up to the dozen price');
        $this->assertSame(['Dozen', 'Dozen'], $rows->pluck('sell_unit_name')->all());
        $this->assertSame(0, DB::table('boxes')->where('id', $opened)->value('items_remaining'));
        $this->assertSame(17, DB::table('boxes')->where('id', $sealed)->value('items_remaining'));

        $line = $sale->groupedItems()->first();
        $this->assertSame(1, $line['quantity']);
        $this->assertSame('1 Dozen', $line['qty_label']);
        $this->assertSame(5000, $line['unit_price']);
        // Returns refund per piece from the line itself, not the dozen price
        $this->assertEqualsWithDelta(416.67, $rows->sum('line_total') / $rows->sum('quantity_sold'), 0.01);
        $this->assertEqualsWithDelta(416.6, $rows->first()->pricePerPiece(), 0.01);
    }

    public function test_packs_from_warehouse_stock(): void
    {
        $this->box('warehouse', $this->warehouseId, 24);

        $sale = $this->sell(['qty' => 2, 'unit_size' => 12, 'price' => 5000], 'warehouse');

        $this->assertSame(10000, (int) $sale->total);
        $this->assertSame(24, (int) $sale->items()->sum('quantity_sold'));
        $this->assertSame(12, (int) $sale->items()->value('sell_unit_size'));
    }

    public function test_server_refuses_single_pieces_when_switched_off_and_unknown_packs(): void
    {
        $this->box('shop', $this->shopId, 24);
        $this->plates->update(['sell_single_pieces' => false]);

        try {
            $this->sell(['qty' => 3, 'unit_size' => 1, 'price' => 500]);
            $this->fail('single pieces sold');
        } catch (\DomainException $e) {
            $this->assertStringContainsString("isn't sold by the single piece", $e->getMessage());
        }

        $this->expectException(\DomainException::class);
        $this->sell(['qty' => 1, 'unit_size' => 10, 'price' => 4000]);   // no pack of 10
    }

    // ── POS ──────────────────────────────────────────────────────────────

    public function test_pos_cart_holds_boxes_dozens_and_pieces_of_one_product(): void
    {
        $this->box('shop', $this->shopId, 24, '2026-01-01');
        $this->box('shop', $this->shopId, 24, '2026-02-01');
        $this->box('shop', $this->shopId, 24, '2026-03-01');   // 72 pieces

        $pos = Livewire::actingAs($this->seller)->test(UnifiedPos::class)
            ->call('selectProduct', $this->plates->id, 'shop')
            ->set('stagingMode', 'item')
            ->assertSee('Single piece')->assertSee('Dozen')
            ->set('stagingUnitSize', 12)
            ->assertSet('stagingPrice', 5000)
            ->set('stagingQty', 2)
            ->call('confirmAddToCart')
            // + 3 single pieces
            ->call('selectProduct', $this->plates->id, 'shop')
            ->set('stagingMode', 'item')->set('stagingUnitSize', 1)->set('stagingQty', 3)
            ->call('confirmAddToCart')
            // + 1 box
            ->call('selectProduct', $this->plates->id, 'shop')
            ->set('stagingMode', 'box')->set('stagingQty', 1)
            ->call('confirmAddToCart')
            ->assertCount('cart', 3)
            ->assertSet('cart.0.unit_size', 12)
            ->assertSet('cart.0.unit_name', 'Dozen')
            ->assertSet('cart.0.line_total', 10000)
            ->assertSet('cart.1.unit_size', 1);

        // 24 + 3 + 24 = 51 of 72 pieces: 2 more dozen (75) don't fit, 1 more (63) does
        $pos->call('selectProduct', $this->plates->id, 'shop')
            ->set('stagingMode', 'item')->set('stagingUnitSize', 12)->set('stagingQty', 2)
            ->call('confirmAddToCart')
            ->assertSet('cart.0.qty', 2)
            ->call('selectProduct', $this->plates->id, 'shop')
            ->set('stagingMode', 'item')->set('stagingUnitSize', 12)->set('stagingQty', 1)
            ->call('confirmAddToCart')
            ->assertCount('cart', 3)
            ->assertSet('cart.0.qty', 3);                              // merged into the Dozen line
    }

    public function test_pack_discount_over_threshold_needs_owner_approval(): void
    {
        $this->box('shop', $this->shopId, 24);

        Livewire::actingAs($this->seller)->test(UnifiedPos::class)
            ->call('selectProduct', $this->plates->id, 'shop')
            ->set('stagingMode', 'item')->set('stagingUnitSize', 12)
            ->set('stagingPrice', 3500)                                  // 30% off the dozen price
            ->set('stagingPriceReason', 'Regular customer')
            ->call('confirmAddToCart')
            ->assertSet('cart.0.original_price', 5000)
            ->assertSet('cart.0.requires_owner_approval', true);
    }

    public function test_pieces_are_not_offered_when_switched_off(): void
    {
        $this->box('shop', $this->shopId, 24);
        $this->plates->update(['sell_single_pieces' => false]);

        Livewire::actingAs($this->seller)->test(UnifiedPos::class)
            ->call('selectProduct', $this->plates->id, 'shop')
            ->set('stagingMode', 'item')
            ->assertSet('stagingUnitSize', 12)                           // starts on the only pack
            ->assertSet('stagingPrice', 5000)
            ->set('stagingUnitSize', 1)                                  // tampered: falls back to the pack
            ->assertSet('stagingUnitSize', 12);
    }
}
