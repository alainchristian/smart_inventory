<?php

namespace Tests\Feature\Inventory;

use App\Livewire\Warehouse\StockLevels;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Product::stockSummaryFor() replaces per-product getCurrentStock() /
 * isLowStock() loops (3–5 queries per product) on the POS, warehouse stock
 * levels and transfer review. It must give exactly the same numbers for
 * every box status, including empty / damaged / in-transit boxes and boxes
 * at other locations.
 * Runs on smart_inventory_test only (phpunit.xml + TestCase guard).
 */
class StockSummaryTest extends TestCase
{
    use DatabaseTransactions;

    private int $warehouseId;
    private int $otherWarehouseId;
    private User $manager;
    private ?int $categoryId = null;

    protected function setUp(): void
    {
        parent::setUp();

        $u   = uniqid();
        $now = now()->toDateTimeString();

        $this->warehouseId      = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => 'W' . substr($u, -8), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $this->otherWarehouseId = DB::table('warehouses')->insertGetId(['name' => "WH2 $u", 'code' => 'X' . substr($u, -8), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $this->manager = User::forceCreate([
            'name' => 'WH Manager', 'email' => "wm$u@example.test", 'password' => bcrypt('x'), 'is_active' => true,
            'must_change_password' => false, 'role' => 'warehouse_manager', 'location_type' => 'warehouse', 'location_id' => $this->warehouseId,
        ]);
    }

    private function product(string $name): int
    {
        $this->categoryId ??= DB::table('categories')->insertGetId([
            'name' => 'Stock ' . uniqid(), 'code' => 'ST' . substr(uniqid(), -8), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('products')->insertGetId([
            'sku' => 'SKU' . uniqid(), 'name' => $name, 'items_per_box' => 12, 'category_id' => $this->categoryId,
            'purchase_price' => 1000, 'selling_price' => 2000, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function box(int $productId, string $status, int $items, ?int $warehouseId = null): void
    {
        DB::table('boxes')->insert([
            'product_id' => $productId, 'box_code' => 'B' . uniqid(), 'items_total' => 12, 'items_remaining' => $items,
            'status' => $status, 'location_type' => 'warehouse', 'location_id' => $warehouseId ?? $this->warehouseId,
            'received_by' => $this->manager->id, 'received_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_summary_matches_per_product_methods_for_every_status(): void
    {
        $mixed = $this->product('Mixed statuses');
        foreach ([['full', 12], ['full', 12], ['partial', 5], ['partial', 0], ['empty', 0], ['damaged', 7], ['in_transit', 12]] as [$s, $n]) {
            $this->box($mixed, $s, $n);
        }
        $this->box($mixed, 'full', 12, $this->otherWarehouseId); // other location: must not count

        $emptyOnly = $this->product('Only empty boxes');
        $this->box($emptyOnly, 'empty', 0);

        $none = $this->product('No boxes at all');

        $summary = Product::stockSummaryFor('warehouse', $this->warehouseId, [$mixed, $emptyOnly, $none]);

        foreach ([$mixed, $emptyOnly, $none] as $id) {
            $p   = Product::find($id);
            $old = $p->getCurrentStock('warehouse', $this->warehouseId);

            $this->assertSame((int) $old['full_boxes'], $summary[$id]['full_boxes'], "full_boxes #$id");
            $this->assertSame((int) $old['partial_boxes'], $summary[$id]['partial_boxes'], "partial_boxes #$id");
            $this->assertSame((int) $old['total_items'], $summary[$id]['total_items'], "total_items #$id");

            // isLowStock(threshold) <=> stocked_boxes <= threshold, for every threshold
            foreach ([0, 1, 2, 3, 5] as $t) {
                $this->assertSame($p->isLowStock('warehouse', $this->warehouseId, $t), $summary[$id]['stocked_boxes'] <= $t, "low stock #$id at $t");
            }
        }

        // Spot-check the rules explicitly for the mixed product
        $this->assertSame(2, $summary[$mixed]['full_boxes']);
        $this->assertSame(2, $summary[$mixed]['partial_boxes']);
        $this->assertSame(12 + 12 + 5 + 7 + 12, $summary[$mixed]['total_items']); // all statuses, this location only
        $this->assertSame(0, $summary[$none]['total_items']);                      // int 0, like getCurrentStock()
    }

    public function test_stock_levels_page_runs_a_fixed_number_of_queries(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $id = $this->product("Stock page product $i");
            $this->box($id, 'full', 12);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::actingAs($this->manager)->test(StockLevels::class)->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Was ~5 per product on the page (100+ for a full page of 20)
        $this->assertLessThan(20, $queries);
    }
}
