<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Box;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\Inventory\BarcodeService;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo data for selling loose in packs (Dozen, Half-dozen, Gross, Pack of N)
 * across several categories.
 *
 *   Kitchenware    plates, glasses, teaspoons (dozen only)
 *   Stationery     exercise books (pack of 10), pens (dozen / gross)
 *   Personal Care  bar soap (half-dozen / dozen), toothpaste (pack of 3)
 *   Footwear       cotton socks, counted in pairs (pack of 3)
 *
 * Also ticks these categories for loose sales (Settings → Sales), lets
 * Nyamirambo sell Kitchenware + Personal Care, and puts stock in the
 * warehouse and the shops (incl. opened boxes).
 *
 * Idempotent: categories by code, products by SKU, packs by size; stock is
 * only created once (boxes tagged batch_number = DEMO-UNITS).
 *
 *   php artisan db:seed --class=SellUnitsDemoSeeder
 */
class SellUnitsDemoSeeder extends Seeder
{
    private const BATCH = 'DEMO-UNITS';

    public function run(): void
    {
        $owner = User::where('role', UserRole::OWNER)->first();
        $shops = Shop::orderBy('id')->get()->keyBy(fn ($s) => str($s->name)->afterLast('—')->trim()->lower()->toString());
        if (! $owner || $shops->isEmpty()) {
            $this->command->warn('Needs the owner and shops — run BootstrapSeeder first.');
            return;
        }
        $warehouseId = $shops->first()->default_warehouse_id ?? DB::table('warehouses')->value('id');

        // ── Categories ────────────────────────────────────────────────────
        $kitchen    = Category::firstOrCreate(['code' => 'KITCHEN'], ['name' => 'Kitchenware', 'is_active' => true]);
        $stationery = Category::firstOrCreate(['code' => 'STATIONERY'], ['name' => 'Stationery', 'is_active' => true]);
        $care       = Category::firstOrCreate(['code' => 'PERSONAL-CARE'], ['name' => 'Personal Care', 'is_active' => true]);
        $footwear   = Category::firstOrCreate(['name' => 'Footwear'], ['code' => 'Footwear', 'is_active' => true]);

        // ── Products + packs ──────────────────────────────────────────────
        //  [sku, name, category, per box, piece price, box price, unit, single pieces?, packs [name, size, price]]
        $catalog = [
            ['KIT-PLATE-DIN', 'Dinner Plate (white)',      $kitchen,    24,  1500, 34000, 'piece', true,  [['Half-dozen', 6, 8500], ['Dozen', 12, 16500]]],
            ['KIT-GLASS-300', 'Drinking Glass 300ml',      $kitchen,    36,   800, 27000, 'piece', true,  [['Half-dozen', 6, 4500], ['Dozen', 12, 8800]]],
            ['KIT-SPOON-TEA', 'Teaspoon (stainless)',      $kitchen,   144,   200, 26000, 'piece', false, [['Dozen', 12, 2200]]],
            ['STA-BOOK-96',   'Exercise Book 96 pages',    $stationery, 100,  500, 45000, 'piece', true,  [['Pack of 10', 10, 4700]]],
            ['STA-PEN-BLUE',  'Ballpoint Pen (blue)',      $stationery, 240,  200, 40000, 'piece', true,  [['Dozen', 12, 2000], ['Gross', 144, 24000]]],
            ['CAR-SOAP-200',  'Bar Soap 200g',             $care,       72,   900, 60000, 'piece', true,  [['Half-dozen', 6, 5200], ['Dozen', 12, 10000]]],
            ['CAR-PASTE-100', 'Toothpaste 100ml',          $care,       48,  1800, 80000, 'piece', true,  [['Pack of 3', 3, 5100]]],
            ['FW-SOCK-COT',   'Cotton Socks (pair)',       $footwear,   60,  1500, 80000, 'pair',  true,  [['Pack of 3', 3, 4000]]],
        ];

        $products = [];
        foreach ($catalog as $row) {
            [$sku, $name, $cat, $perBox, $piece, $boxPrice, $uom, $single, $packs] = $row;
            $p = Product::firstOrCreate(['sku' => $sku], [
                'name' => $name, 'category_id' => $cat->id, 'items_per_box' => $perBox,
                'purchase_price' => (int) round($piece * 0.7), 'selling_price' => $piece, 'box_selling_price' => $boxPrice,
                'low_stock_threshold' => (int) ceil($perBox / 2), 'reorder_point' => $perBox,
                'unit_of_measure' => $uom, 'is_active' => true,
            ]);
            $p->update(['sell_single_pieces' => $single]);
            foreach ($packs as $pack) {
                [$packName, $size, $price] = $pack;
                $p->sellUnits()->updateOrCreate(['size' => $size], ['name' => $packName, 'price' => $price]);
            }
            $products[$sku] = $p;
        }

        // ── Loose sales allowed for these categories (keeps what was ticked) ─
        $settings = app(SettingsService::class);
        $settings->set('allow_individual_item_sales', true);
        $settings->set('individual_sale_category_ids', array_values(array_unique(array_map('intval', array_merge(
            $settings->individualSaleCategoryIds(),
            [$kitchen->id, $stationery->id, $care->id, $footwear->id]
        )))));

        // ── Nyamirambo also sells kitchen + personal care (if it's specialised) ─
        $nyamirambo = $shops['nyamirambo'] ?? null;
        if ($nyamirambo && ! $nyamirambo->sells_all_categories) {
            $nyamirambo->categories()->syncWithoutDetaching([$kitchen->id, $care->id]);
        }

        // ── Stock (once) ──────────────────────────────────────────────────
        if (Box::where('batch_number', self::BATCH)->exists()) {
            $this->command->info('Packs, categories and settings updated. Demo stock already seeded — skipped.');
            return;
        }

        DB::transaction(function () use ($products, $owner, $warehouseId, $shops, $nyamirambo) {
            // Warehouse: 4 sealed boxes of everything, plus one opened box
            foreach ($products as $p) {
                $this->boxes($p, 'warehouse', $warehouseId, 4, $owner);
                $this->boxes($p, 'warehouse', $warehouseId, 1, $owner, (int) floor($p->items_per_box * 0.4));
            }

            $shopStock = [
                // shop key    sku => [sealed boxes, opened box remaining|null]
                'nyamirambo' => ['KIT-PLATE-DIN' => [2, 10], 'KIT-GLASS-300' => [1, 20], 'KIT-SPOON-TEA' => [1, null],
                                 'CAR-SOAP-200' => [2, 30], 'CAR-PASTE-100' => [1, 12]],
                'kimironko'  => ['KIT-PLATE-DIN' => [1, null], 'STA-BOOK-96' => [2, 45], 'STA-PEN-BLUE' => [1, 100],
                                 'CAR-SOAP-200' => [1, null], 'FW-SOCK-COT' => [1, 25]],
                'remera'     => ['FW-SOCK-COT' => [2, 20]],
            ];
            foreach ($shopStock as $key => $lines) {
                $shop = $shops[$key] ?? null;
                if (! $shop) {
                    continue;
                }
                foreach ($lines as $sku => $spec) {
                    [$sealed, $opened] = $spec;
                    $p = $products[$sku];
                    if ($shop->sellsProduct($p)) {          // specialised shops only get what they sell
                        $this->boxes($p, 'shop', $shop->id, $sealed, $owner);
                        if ($opened) {
                            $this->boxes($p, 'shop', $shop->id, 1, $owner, $opened);
                        }
                    }
                }
            }
        });

        $this->command->info('Seeded 8 products with packs in Kitchenware, Stationery, Personal Care and Footwear; stock at the warehouse, Nyamirambo, Kimironko and Remera.');
    }

    private function boxes(Product $p, string $locType, int $locId, int $count, User $by, ?int $remaining = null): void
    {
        $codes = app(BarcodeService::class);

        for ($i = 0; $i < $count; $i++) {
            $left = $remaining ?? $p->items_per_box;
            $box = Box::forceCreate([
                'product_id' => $p->id, 'box_code' => $codes->generateBoxCode(),
                'items_total' => $p->items_per_box, 'items_remaining' => $left,
                'status' => $left < $p->items_per_box ? 'partial' : 'full',
                'location_type' => $locType, 'location_id' => $locId,
                'received_by' => $by->id, 'received_at' => now()->subDays(2), 'batch_number' => self::BATCH,
            ]);

            DB::table('box_movements')->insert([
                'box_id' => $box->id, 'movement_type' => $locType === 'warehouse' ? 'received' : 'transfer',
                'from_location_type' => $locType === 'warehouse' ? null : 'warehouse', 'from_location_id' => null,
                'to_location_type' => $locType, 'to_location_id' => $locId,
                'moved_by' => $by->id, 'moved_at' => now()->subDays(2), 'items_moved' => $left,
                'reason' => 'Demo stock (sell units)', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
