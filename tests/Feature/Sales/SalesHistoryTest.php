<?php

namespace Tests\Feature\Sales;

use App\Livewire\Shop\Sales\SalesIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Sales History (shop.sales.index): box/piece totals and shop scoping.
 */
class SalesHistoryTest extends TestCase
{
    use DatabaseTransactions;

    private function shop(): int
    {
        $u = uniqid();

        return DB::table('shops')->insertGetId([
            'name' => "History $u", 'code' => 'S' . substr($u, -8), 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function manager(int $shopId): User
    {
        return User::forceCreate([
            'name' => 'History Tester', 'email' => 'sh' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $shopId,
        ]);
    }

    private function product(): int
    {
        $u = uniqid();
        $categoryId = DB::table('categories')->insertGetId([
            'name' => "Hist Cat $u", 'code' => 'HC-' . $u, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => "Hist Product $u", 'sku' => 'HP-' . $u, 'items_per_box' => 12,
            'selling_price' => 1000, 'purchase_price' => 500, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** $lines: [[is_full_box, quantity_sold], ...] */
    private function sale(int $shopId, User $by, array $lines): int
    {
        $saleId = DB::table('sales')->insertGetId([
            'sale_number' => 'SH-' . uniqid(), 'shop_id' => $shopId, 'sold_by' => $by->id,
            'sale_date' => now(), 'type' => 'full_box', 'payment_method' => 'cash',
            'subtotal' => 1000, 'total' => 1000, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $productId = $this->product();
        foreach ($lines as [$isBox, $qty]) {
            DB::table('sale_items')->insert([
                'sale_id' => $saleId, 'product_id' => $productId, 'quantity_sold' => $qty,
                'is_full_box' => $isBox, 'original_unit_price' => 100, 'actual_unit_price' => 100,
                'line_total' => 100, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $saleId;
    }

    public function test_boxes_and_loose_pieces_are_counted_separately(): void
    {
        $shop = $this->shop();
        $me   = $this->manager($shop);

        // 2 boxes + 8 loose pieces + 3 loose pieces
        $this->sale($shop, $me, [[true, 12], [true, 12], [false, 8], [false, 3]]);

        Livewire::actingAs($me)->test(SalesIndex::class)
            ->assertViewHas('summaryBoxes', 2)
            ->assertViewHas('summaryPieces', 11)
            ->assertSee('2 boxes + 11 pcs');
    }

    public function test_manager_cannot_expand_another_shops_sale(): void
    {
        $mine  = $this->shop();
        $other = $this->shop();
        $me    = $this->manager($mine);

        $otherSale = $this->sale($other, $this->manager($other), [[true, 12]]);
        $ownSale   = $this->sale($mine, $me, [[true, 12]]);

        Livewire::actingAs($me)->test(SalesIndex::class)
            ->call('toggleExpand', $otherSale)
            ->assertViewHas('expandedSale', null)
            ->call('toggleExpand', $ownSale)
            ->assertViewHas('expandedSale', fn ($s) => $s?->id === $ownSale);
    }
}
