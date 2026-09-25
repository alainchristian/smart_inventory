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
 * Per-category individual-item sales: opt-in categories sell by item,
 * every other category by full box only (incl. when only opened boxes remain).
 * Runs on smart_inventory_test only (phpunit.xml + TestCase guard).
 */
class IndividualItemSalesTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private User $manager;
    private int $catLoose;     // ticked → sold by item
    private int $catBoxOnly;   // not ticked → full box only
    private int $catParent;    // ticked parent…
    private int $catChild;     // …covers this child

    protected function setUp(): void
    {
        parent::setUp();

        $u   = uniqid();
        $now = now()->toDateTimeString();

        $this->shopId = DB::table('shops')->insertGetId(['name' => "Shop $u", 'code' => 'I' . substr($u, -8), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $this->manager = User::forceCreate([
            'name' => 'Seller', 'email' => "s$u@example.test", 'password' => bcrypt('x'), 'is_active' => true, 'must_change_password' => false,
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopId,
        ]);
        DailySession::forceCreate([
            'shop_id' => $this->shopId, 'session_date' => business_today()->toDateString(), 'status' => 'open',
            'opening_balance' => 0, 'opened_by' => $this->manager->id, 'opened_at' => now(),
        ]);

        $cat = fn ($name, $parent = null) => DB::table('categories')->insertGetId([
            'name' => "$name $u", 'code' => strtoupper(substr($name, 0, 2)) . substr($u, -7), 'parent_id' => $parent, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->catLoose   = $cat('Socks');
        $this->catBoxOnly = $cat('Shoes');
        $this->catParent  = $cat('Accessories');
        $this->catChild   = $cat('Laces', $this->catParent);

        $settings = app(SettingsService::class);
        $settings->set('allow_individual_item_sales', true);
        $settings->set('individual_sale_category_ids', [$this->catLoose, $this->catParent]);
    }

    private function product(int $categoryId, string $name): int
    {
        $now = now()->toDateTimeString();

        return DB::table('products')->insertGetId([
            'sku' => 'SKU' . uniqid(), 'name' => $name, 'items_per_box' => 10, 'category_id' => $categoryId,
            'purchase_price' => 1000, 'selling_price' => 3000, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function box(int $productId, string $status, int $remaining): void
    {
        $now = now()->toDateTimeString();
        DB::table('boxes')->insert([
            'product_id' => $productId, 'box_code' => 'B' . uniqid(), 'items_total' => 10, 'items_remaining' => $remaining,
            'status' => $status, 'location_type' => 'shop', 'location_id' => $this->shopId,
            'received_by' => $this->manager->id, 'received_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    public function test_rule_is_opt_in_and_covers_subcategories(): void
    {
        $s = app(SettingsService::class);

        $this->assertTrue($s->categoryAllowsIndividualSales($this->catLoose));
        $this->assertFalse($s->categoryAllowsIndividualSales($this->catBoxOnly));
        $this->assertTrue($s->categoryAllowsIndividualSales($this->catChild), 'ticking a parent covers its subcategories');
        $this->assertFalse($s->categoryAllowsIndividualSales(null));

        $s->set('individual_sale_category_ids', []);
        $this->assertFalse($s->categoryAllowsIndividualSales($this->catLoose), 'nothing ticked = everything by full box');

        $s->set('individual_sale_category_ids', [$this->catLoose]);
        $s->set('allow_individual_item_sales', false);
        $this->assertFalse($s->categoryAllowsIndividualSales($this->catLoose), 'master switch off = full box only');
    }

    public function test_migration_keeps_old_all_categories_behaviour(): void
    {
        $s = app(SettingsService::class);
        $s->set('individual_sale_category_ids', []);   // old meaning: "all categories"

        $migration = require base_path('database/migrations/2026_09_25_000002_make_individual_sale_categories_opt_in.php');
        $migration->up();
        $s->clearCache();

        foreach ([$this->catLoose, $this->catBoxOnly, $this->catChild] as $id) {
            $this->assertTrue($s->categoryAllowsIndividualSales($id));
        }
    }

    public function test_pos_blocks_opened_boxes_of_a_box_only_category(): void
    {
        $shoe = $this->product($this->catBoxOnly, 'Air Max opened');
        $this->box($shoe, 'partial', 4);

        Livewire::actingAs($this->manager)->test(UnifiedPos::class)
            ->call('selectProduct', $shoe, 'shop')
            ->assertSet('showAddModal', false)
            ->assertDispatched('notification', fn ($name, $params) => str_contains(json_encode($params), 'sold by full box only'));
    }

    public function test_pos_allows_opened_boxes_of_a_ticked_category_by_item(): void
    {
        $socks = $this->product($this->catLoose, 'Socks opened');
        $this->box($socks, 'partial', 4);

        Livewire::actingAs($this->manager)->test(UnifiedPos::class)
            ->call('selectProduct', $socks, 'shop')
            ->assertSet('showAddModal', true)
            ->assertSet('stagingMode', 'item')
            ->assertSet('stagingProduct.individual_sale_allowed', true);
    }

    public function test_box_only_category_cannot_be_switched_to_items_on_add_or_edit(): void
    {
        $shoe = $this->product($this->catBoxOnly, 'Air Max full');
        $this->box($shoe, 'full', 10);

        $pos = Livewire::actingAs($this->manager)->test(UnifiedPos::class)
            ->call('selectProduct', $shoe, 'shop')
            ->assertSet('showAddModal', true)
            ->assertSet('stagingMode', 'box')
            ->assertSet('stagingProduct.individual_sale_allowed', false)
            ->assertDontSee('Individual Items');

        // Forcing item mode (client-mutable state) is refused
        $pos->set('stagingMode', 'item')->set('stagingQty', 2)->call('confirmAddToCart')
            ->assertCount('cart', 0);

        // Add as a box, then editing the line must not unlock item mode
        $pos->set('stagingMode', 'box')->set('stagingQty', 1)->call('confirmAddToCart')->assertCount('cart', 1)
            ->call('openEditItem', 0)
            ->assertSet('stagingProduct.individual_sale_allowed', false);
    }

    public function test_sale_service_rejects_loose_items_of_a_box_only_category(): void
    {
        $shoe = $this->product($this->catBoxOnly, 'Air Max server');
        $this->actingAs($this->manager);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('can only be sold by full box');

        (new SaleService())->createMixedSale([
            'shop_id'  => $this->shopId,
            'payments' => [['method' => 'cash', 'amount' => 6000, 'reference' => null]],
            'items'    => [[
                'product_id' => $shoe, 'source' => 'shop', 'source_id' => $this->shopId, 'mode' => 'item',
                'qty' => 2, 'items_per_box' => 10, 'price' => 3000, 'price_modified' => false,
            ]],
        ]);
    }
}
