<?php

namespace Tests\Feature\Sales;

use App\Models\Box;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSellUnit;
use App\Models\User;
use App\Services\SettingsService;
use Database\Seeders\SellUnitsDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * SellUnitsDemoSeeder is meant to be run on the live server too, so it must
 * stay safe there: valid pack prices, Footwear never ticked for loose sales,
 * idempotent. Runs on smart_inventory_test only.
 */
class SellUnitsDemoSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_seeds_non_shoe_products_with_valid_packs_and_stock_once(): void
    {
        $u   = uniqid();
        $now = now()->toDateTimeString();
        User::forceCreate(['name' => 'Owner', 'email' => "ds$u@example.test", 'password' => 'x', 'is_active' => true,
            'must_change_password' => false, 'role' => 'owner']);
        $wh = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => 'W' . substr($u, -8), 'created_at' => $now, 'updated_at' => $now]);
        foreach (['Nyamirambo', 'Kimironko'] as $name) {
            DB::table('shops')->insert(['name' => "Test $u — $name", 'code' => substr($name, 0, 2) . substr($u, -7), 'is_active' => true,
                'default_warehouse_id' => $wh, 'created_at' => $now, 'updated_at' => $now]);
        }
        $footwear = Category::firstOrCreate(['name' => 'Footwear'], ['code' => 'Footwear', 'is_active' => true]);
        $boxesBefore = Box::where('batch_number', 'DEMO-UNITS')->count();

        $this->seed(SellUnitsDemoSeeder::class);

        $skus = ['KIT-PLATE-DIN', 'KIT-GLASS-300', 'KIT-SPOON-TEA', 'STA-BOOK-96', 'STA-PEN-BLUE', 'CAR-SOAP-200', 'CAR-PASTE-100'];
        $this->assertSame(7, Product::whereIn('sku', $skus)->count());
        $this->assertFalse(Product::where('sku', 'FW-SOCK-COT')->where('created_at', '>=', $now)->exists());

        // Every pack passes the price ladder (the old Plate / Glass dozens didn't)
        foreach (ProductSellUnit::with('product')->whereHas('product', fn ($q) => $q->whereIn('sku', $skus))->get() as $unit) {
            $p = $unit->product;
            $this->assertNull(
                ProductSellUnit::problemFor($unit->name, $unit->size, $unit->price, (int) $p->selling_price, (int) $p->box_selling_price, $p->items_per_box),
                "{$p->name} {$unit->name}"
            );
        }

        $ticked = array_map('intval', app(SettingsService::class)->individualSaleCategoryIds());
        $this->assertNotContains($footwear->id, $ticked, 'shoes stay box-only');
        $this->assertContains(Category::where('code', 'KITCHEN')->value('id'), $ticked);

        // Stock is created once; a second run only refreshes products / packs
        $created = Box::where('batch_number', 'DEMO-UNITS')->count() - $boxesBefore;
        if ($boxesBefore === 0) {
            $this->assertGreaterThan(0, $created);
        }
        $this->seed(SellUnitsDemoSeeder::class);
        $this->assertSame($boxesBefore + $created, Box::where('batch_number', 'DEMO-UNITS')->count());
    }
}
