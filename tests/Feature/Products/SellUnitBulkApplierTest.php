<?php

namespace Tests\Feature\Products;

use App\Livewire\Products\ProductList;
use App\Models\Product;
use App\Models\User;
use App\Services\Products\SellUnitBulkApplier;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Products list → "Apply packs": the same packs on many products at once.
 * Runs on smart_inventory_test only (phpunit.xml + TestCase guard).
 */
class SellUnitBulkApplierTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;
    private User $seller;
    private Product $plates;    // 24 a box, box 11,000 (458.33 a piece), piece 500
    private Product $pots;      // 10 a box, box-only category
    private Product $odd;       // 15 a box: no Half box

    protected function setUp(): void
    {
        parent::setUp();
        $u   = uniqid();
        $now = now()->toDateTimeString();

        $shopId = DB::table('shops')->insertGetId(['name' => "Shop $u", 'code' => 'S' . substr($u, -8), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $this->owner = User::forceCreate([
            'name' => 'Owner', 'email' => "bo$u@example.test", 'password' => 'x', 'is_active' => true, 'must_change_password' => false, 'role' => 'owner',
        ]);
        $this->seller = User::forceCreate([
            'name' => 'Seller', 'email' => "bs$u@example.test", 'password' => 'x', 'is_active' => true, 'must_change_password' => false,
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $shopId,
        ]);

        $loose   = DB::table('categories')->insertGetId(['name' => "Kitchen $u", 'code' => 'KB' . substr($u, -7), 'created_at' => $now, 'updated_at' => $now]);
        $boxOnly = DB::table('categories')->insertGetId(['name' => "Pots $u", 'code' => 'PB' . substr($u, -7), 'created_at' => $now, 'updated_at' => $now]);
        $s = app(SettingsService::class);
        $s->set('allow_individual_item_sales', true);
        $s->set('individual_sale_category_ids', [$loose]);

        $make = fn (string $name, int $ipb, int $piece, int $box, int $cat) => Product::forceCreate([
            'sku' => strtoupper(substr($name, 0, 3)) . $u, 'name' => "$name $u", 'items_per_box' => $ipb, 'category_id' => $cat,
            'purchase_price' => 1, 'selling_price' => $piece, 'box_selling_price' => $box, 'is_active' => true,
            'low_stock_threshold' => 1, 'reorder_point' => 1,
        ]);
        $this->plates = $make('Plate', 24, 500, 11000, $loose);
        $this->pots   = $make('Pot', 10, 22000, 200000, $boxOnly);
        $this->odd    = $make('Odd', 15, 100, 1500, $loose);
    }

    private function ids(): array
    {
        return [$this->plates->id, $this->pots->id, $this->odd->id];
    }

    public function test_preview_sizes_packs_per_box_and_explains_skips(): void
    {
        $this->plates->sellUnits()->create(['name' => 'Dozen', 'size' => 12, 'price' => 5500]);

        $rows = collect(app(SellUnitBulkApplier::class)->preview($this->ids(), [
            ['key' => 'half_dozen'], ['key' => 'dozen'], ['key' => 'half_box'],
        ]))->keyBy('id');

        $plates = $rows[$this->plates->id];
        $this->assertSame([['Half-dozen', 6, 3000]], array_map(fn ($a) => [$a['name'], $a['size'], $a['price']], $plates['add']));
        $this->assertStringContainsString('Already sold as Dozen', $plates['skip'][0]['reason']);
        $this->assertSame('Half box', $plates['skip'][1]['name']);                          // Half box of 24 is 12 too
        $this->assertStringContainsString('Already sold as Dozen', $plates['skip'][1]['reason']);

        $pots = $rows[$this->pots->id];
        $this->assertFalse($pots['sells_loose']);
        $this->assertSame([['Half-dozen', 6], ['Half box', 5]], array_map(fn ($a) => [$a['name'], $a['size']], $pots['add']));
        $this->assertStringContainsString("Doesn't fit in a box of 10", $pots['skip'][0]['reason']);

        $odd = $rows[$this->odd->id];
        $this->assertSame(['Half-dozen', 'Dozen'], array_column($odd['add'], 'name'));
        $this->assertSame('Half box', $odd['skip'][0]['name']);                              // 15 is odd
    }

    public function test_prices_are_clamped_to_the_ladder(): void
    {
        $rows = collect(app(SellUnitBulkApplier::class)->preview([$this->plates->id], [['key' => 'dozen']], [
            'price' => 'discount', 'discount' => 50,
        ]))->keyBy('id');

        // 50% off 6,000 = 3,000 — below the box rate, so the dozen sits at 11,000 ÷ 24 × 12 = 5,500
        $this->assertSame(5500, $rows[$this->plates->id]['add'][0]['price']);
    }

    public function test_apply_writes_packs_switches_single_pieces_and_logs_once(): void
    {
        $this->actingAs($this->owner);
        $before = DB::table('activity_logs')->where('action', 'sell_units_bulk_applied')->count();

        $done = app(SellUnitBulkApplier::class)->apply($this->ids(), [
            ['key' => 'dozen'], ['key' => 'custom', 'name' => 'Pack of 4', 'size' => 4],
        ], ['single' => 'off', 'existing' => 'skip']);

        $this->assertSame(['products' => 3, 'packs' => 5], $done);   // plates 2, pots 1 (Pack of 4), odd 2
        $this->assertSame([4, 12], $this->plates->sellUnits()->pluck('size')->all());
        $this->assertSame(2000, (int) $this->plates->sellUnits()->where('size', 4)->value('price'));
        $this->assertFalse((bool) $this->plates->refresh()->sell_single_pieces);
        $this->assertSame($before + 1, DB::table('activity_logs')->where('action', 'sell_units_bulk_applied')->count());

        // Replace updates name and price of the same size
        app(SellUnitBulkApplier::class)->apply([$this->plates->id], [['key' => 'custom', 'name' => 'Four-pack', 'size' => 4]],
            ['existing' => 'replace', 'price' => 'discount', 'discount' => 5]);
        $this->assertSame(['Four-pack', 1900], [
            $this->plates->sellUnits()->where('size', 4)->value('name'),
            (int) $this->plates->sellUnits()->where('size', 4)->value('price'),
        ]);
    }

    public function test_owner_applies_from_the_products_list(): void
    {
        Livewire::actingAs($this->owner)->test(ProductList::class)
            ->set('activeOnly', false)
            ->set('selected', [(string) $this->plates->id, (string) $this->odd->id])
            ->call('openPacks')
            ->assertSet('showPacks', true)
            ->set('packKeys', ['dozen'])
            ->assertSee('Add Dozen (12)')
            ->call('applyPacks')
            ->assertHasNoErrors()
            ->assertSet('showPacks', false)
            ->assertSet('selected', []);

        $this->assertSame(6000, (int) $this->plates->sellUnits()->where('size', 12)->value('price'));
        $this->assertSame(1200, (int) $this->odd->sellUnits()->where('size', 12)->value('price'));
    }

    public function test_shop_manager_cannot_apply_packs(): void
    {
        Livewire::actingAs($this->seller)->test(ProductList::class)
            ->set('selected', [(string) $this->plates->id])
            ->set('packKeys', ['dozen'])
            ->call('applyPacks');
        $this->assertSame(0, $this->plates->sellUnits()->count());

        $this->actingAs($this->seller);
        $this->expectException(\DomainException::class);
        app(SellUnitBulkApplier::class)->apply([$this->plates->id], [['key' => 'dozen']]);
    }
}
