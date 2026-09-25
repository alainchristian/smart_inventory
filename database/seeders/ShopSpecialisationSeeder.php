<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Box;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\Inventory\BarcodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo data for shop specialisation + Return to warehouse.
 *
 *   Remera      → Footwear only (incl. the new "Sandals" sub-category)
 *   Nyamirambo  → Bags & Accessories + Household
 *   Kimironko   → All categories (general store)
 *
 * Each specialised shop is also given a few boxes it no longer sells
 * ("stranded" stock from before it specialised) so the "Not sold at this
 * shop" card and the Return to warehouse flow have something to work on.
 *
 * Idempotent: categories keyed by code, products by SKU, and stock is only
 * created once (boxes tagged batch_number = DEMO-SPEC).
 *
 *   php artisan db:seed --class=ShopSpecialisationSeeder
 */
class ShopSpecialisationSeeder extends Seeder
{
    private const BATCH = 'DEMO-SPEC';

    public function run(): void
    {
        $owner = User::where('role', UserRole::OWNER)->first();
        $shops = Shop::orderBy('id')->get()->keyBy(fn ($s) => str($s->name)->afterLast('—')->trim()->lower()->toString());
        $remera = $shops['remera'] ?? null;
        $nyamirambo = $shops['nyamirambo'] ?? null;
        $kimironko = $shops['kimironko'] ?? null;

        if (! $owner || ! $remera || ! $nyamirambo || ! $kimironko) {
            $this->command->warn('Needs the owner and the Remera / Nyamirambo / Kimironko shops — run BootstrapSeeder first.');
            return;
        }
        $warehouseId = $remera->default_warehouse_id ?? DB::table('warehouses')->value('id');

        // ── Categories ────────────────────────────────────────────────────
        $footwear = Category::firstOrCreate(['name' => 'Footwear'], ['code' => 'Footwear', 'is_active' => true]);
        $sandals = Category::firstOrCreate(['code' => 'FW-SANDALS'], ['name' => 'Sandals', 'parent_id' => $footwear->id, 'is_active' => true]);
        $bags = Category::firstOrCreate(['code' => 'BAGS'], ['name' => 'Bags & Accessories', 'is_active' => true]);
        $household = Category::firstOrCreate(['code' => 'HOUSEHOLD'], ['name' => 'Household', 'is_active' => true]);

        // ── Products ──────────────────────────────────────────────────────
        $product = fn (string $sku, string $name, Category $cat, int $perBox, int $buy, int $sell) => Product::firstOrCreate(
            ['sku' => $sku],
            [
                'name' => $name, 'category_id' => $cat->id, 'items_per_box' => $perBox,
                'purchase_price' => $buy, 'selling_price' => $sell, 'box_selling_price' => $sell * $perBox,
                'low_stock_threshold' => 5, 'reorder_point' => 10, 'unit_of_measure' => 'piece', 'is_active' => true,
            ]
        );

        $slides   = $product('SAN-SLIDE-40', 'Beach Slides Size 40', $sandals, 20, 6000, 9000);
        $handbag  = $product('BAG-HAND-LTH', 'Leather Handbag', $bags, 6, 25000, 38000);
        $backpack = $product('BAG-BACK-SCH', 'School Backpack', $bags, 10, 12000, 18000);
        $basin    = $product('HH-BASIN-SET', 'Plastic Basin Set (3 pcs)', $household, 8, 7000, 11000);
        $pot      = $product('HH-POT-5L', 'Cooking Pot 5L', $household, 6, 15000, 22000);

        $shoes = Product::where('category_id', $footwear->id)->orderBy('id')->get();

        // ── What each shop sells ──────────────────────────────────────────
        $remera->update(['sells_all_categories' => false]);
        $remera->categories()->sync([$footwear->id]);
        $nyamirambo->update(['sells_all_categories' => false]);
        $nyamirambo->categories()->sync([$bags->id, $household->id]);
        $kimironko->update(['sells_all_categories' => true]);
        $kimironko->categories()->sync([]);

        // ── Stock (once) ──────────────────────────────────────────────────
        if (Box::where('batch_number', self::BATCH)->exists()) {
            $this->command->info('Shop categories set. Demo stock already seeded — skipped.');
            return;
        }

        DB::transaction(function () use ($owner, $warehouseId, $remera, $nyamirambo, $kimironko, $shoes, $slides, $handbag, $backpack, $basin, $pot) {
            // Warehouse: sealed boxes of every new product (+ one opened box of each bag)
            foreach ([$slides, $handbag, $backpack, $basin, $pot] as $p) {
                $this->boxes($p, 'warehouse', $warehouseId, 4, $owner);
            }
            $this->boxes($handbag, 'warehouse', $warehouseId, 1, $owner, remaining: 3);
            $this->boxes($backpack, 'warehouse', $warehouseId, 1, $owner, remaining: 4);

            // Remera (Footwear): its own shoes + slides …
            foreach ($shoes as $shoe) {
                $this->boxes($shoe, 'shop', $remera->id, 2, $owner);
            }
            $this->boxes($slides, 'shop', $remera->id, 2, $owner);
            // … and stranded bags/household from before it specialised
            $this->boxes($handbag, 'shop', $remera->id, 2, $owner);
            $this->boxes($handbag, 'shop', $remera->id, 1, $owner, remaining: 2);
            $this->boxes($pot, 'shop', $remera->id, 1, $owner);

            // Nyamirambo (Bags + Household): its own stock …
            $this->boxes($backpack, 'shop', $nyamirambo->id, 3, $owner);
            $this->boxes($basin, 'shop', $nyamirambo->id, 2, $owner);
            $this->boxes($pot, 'shop', $nyamirambo->id, 1, $owner, remaining: 4);
            // … and stranded shoes
            if ($shoes->isNotEmpty()) {
                $this->boxes($shoes->last(), 'shop', $nyamirambo->id, 2, $owner);
            }

            // Kimironko (general store): a bit of everything, nothing stranded
            if ($shoes->isNotEmpty()) {
                $this->boxes($shoes->first(), 'shop', $kimironko->id, 2, $owner);
            }
            $this->boxes($slides, 'shop', $kimironko->id, 1, $owner);
            $this->boxes($backpack, 'shop', $kimironko->id, 1, $owner);
            $this->boxes($basin, 'shop', $kimironko->id, 1, $owner);
        });

        $this->command->info('Remera → Footwear · Nyamirambo → Bags & Accessories, Household · Kimironko → All categories. Stranded stock added at Remera and Nyamirambo.');
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
                'received_by' => $by->id, 'received_at' => now()->subDays(3), 'batch_number' => self::BATCH,
            ]);

            DB::table('box_movements')->insert([
                'box_id' => $box->id, 'movement_type' => $locType === 'warehouse' ? 'received' : 'transfer',
                'from_location_type' => $locType === 'warehouse' ? null : 'warehouse', 'from_location_id' => null,
                'to_location_type' => $locType, 'to_location_id' => $locId,
                'moved_by' => $by->id, 'moved_at' => now()->subDays(3), 'items_moved' => $left,
                'reason' => 'Demo stock (shop specialisation)', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
