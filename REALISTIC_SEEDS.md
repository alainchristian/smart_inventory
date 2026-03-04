# SmartInventory — Realistic Rwandan Shoe Business Seed Data
## Claude Code Instructions

> Drop in project root, then tell Claude Code:
> "Read `REALISTIC_SEEDS.md` and follow every step in order."

---

## Business Context

**Business:** Kigali Footwear Wholesale & Retail  
**Model:** 1 central warehouse → 3 retail shops in Kigali  
**Primary stock:** ~80% shoes (by SKU count), ~20% accessories  
**Currency:** RWF — stored in database as **cents** (× 100)  
  e.g. RWF 25,000 per pair → stored as `2500000`  
**Box model:** Each product+size = one SKU. Each physical box has a unique `box_code`.  
  Standard shoe box = **6 pairs per box**. Sandals/cheap shoes = 12. Accessories = 24.

---

## Step 0 — Pre-Flight

```bash
# Check enum values
cat app/Enums/BoxStatus.php
cat app/Enums/LocationType.php
cat app/Enums/TransferStatus.php
cat app/Enums/PaymentMethod.php
cat app/Enums/SaleType.php
cat app/Enums/UserRole.php

# Check User model roles
grep -n "isOwner\|isWarehouseManager\|isShopManager\|role\b" app/Models/User.php | head -20

# Check existing seeder structure
cat database/seeders/DatabaseSeeder.php | head -30

# Confirm BoxMovement model
ls app/Models/BoxMovement.php
grep -n "fillable" app/Models/BoxMovement.php
```

---

## Step 1 — Create `RwandaShoeBusinessSeeder.php`

**Target:** `database/seeders/RwandaShoeBusinessSeeder.php` (new file)

```php
<?php

namespace Database\Seeders;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use App\Enums\PaymentMethod;
use App\Enums\SaleType;
use App\Enums\TransferStatus;
use App\Models\Box;
use App\Models\BoxMovement;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\TransferBox;
use App\Models\TransferItem;
use App\Models\Transporter;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RwandaShoeBusinessSeeder extends Seeder
{
    private int $boxCounter = 1;

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🇷🇼  Kigali Footwear — Realistic Seed Data');
        $this->command->info(str_repeat('─', 56));

        DB::statement('SET session_replication_role = replica;'); // disable FK checks temporarily

        $this->command->info('🧹  Wiping existing data...');
        $this->wipe();

        DB::statement('SET session_replication_role = DEFAULT;');

        $this->command->info('👥  Creating users...');
        $users = $this->seedUsers();

        $this->command->info('🏢  Creating locations...');
        $locations = $this->seedLocations($users);

        $this->command->info('🚚  Creating transporters...');
        $transporters = $this->seedTransporters();

        $this->command->info('🗂️   Creating categories...');
        $categories = $this->seedCategories();

        $this->command->info('👟  Creating products...');
        $products = $this->seedProducts($categories);

        $this->command->info('📦  Filling warehouse with boxes...');
        $this->seedWarehouseBoxes($products, $locations, $users);

        $this->command->info('🚛  Creating transfers (warehouse → shops)...');
        $this->seedTransfers($locations, $users, $transporters, $products);

        $this->command->info('🛒  Creating sales history (90 days)...');
        $this->seedSales($locations, $users);

        $this->printSummary($users, $locations, $products);
    }

    // ═══════════════════════════════════════════════════════
    //  WIPE
    // ═══════════════════════════════════════════════════════

    private function wipe(): void
    {
        // Order matters due to foreign keys
        DB::table('sale_items')->delete();
        DB::table('sales')->delete();
        DB::table('transfer_boxes')->delete();
        DB::table('transfer_items')->delete();
        DB::table('transfers')->delete();
        DB::table('box_movements')->delete();
        DB::table('boxes')->delete();
        DB::table('products')->delete();
        DB::table('categories')->delete();
        DB::table('transporters')->delete();
        DB::table('users')->delete();
        DB::table('shops')->delete();
        DB::table('warehouses')->delete();
    }

    // ═══════════════════════════════════════════════════════
    //  USERS
    // ═══════════════════════════════════════════════════════

    private function seedUsers(): array
    {
        $owner = User::create([
            'name'          => 'Jean-Pierre Habimana',
            'email'         => 'owner@kigalifootwear.rw',
            'password'      => Hash::make('password'),
            'role'          => 'owner',
            'phone'         => '+250788100001',
            'location_type' => null,
            'location_id'   => null,
            'is_active'     => true,
        ]);

        // Warehouse managers — will get location_id assigned after warehouse created
        $wm1 = User::create([
            'name'     => 'Emmanuel Nzeyimana',
            'email'    => 'wm1@kigalifootwear.rw',
            'password' => Hash::make('password'),
            'role'     => 'warehouse_manager',
            'phone'    => '+250788200001',
            'is_active'=> true,
        ]);

        $wm2 = User::create([
            'name'     => 'Celestine Mukamana',
            'email'    => 'wm2@kigalifootwear.rw',
            'password' => Hash::make('password'),
            'role'     => 'warehouse_manager',
            'phone'    => '+250788200002',
            'is_active'=> true,
        ]);

        // Shop managers
        $sm1 = User::create([
            'name'     => 'Alice Uwimana',
            'email'    => 'shop1@kigalifootwear.rw',
            'password' => Hash::make('password'),
            'role'     => 'shop_manager',
            'phone'    => '+250788300001',
            'is_active'=> true,
        ]);

        $sm2 = User::create([
            'name'     => 'Robert Kayitare',
            'email'    => 'shop2@kigalifootwear.rw',
            'password' => Hash::make('password'),
            'role'     => 'shop_manager',
            'phone'    => '+250788300002',
            'is_active'=> true,
        ]);

        $sm3 = User::create([
            'name'     => 'Marie-Claire Ingabire',
            'email'    => 'shop3@kigalifootwear.rw',
            'password' => Hash::make('password'),
            'role'     => 'shop_manager',
            'phone'    => '+250788300003',
            'is_active'=> true,
        ]);

        $this->command->info('   ✓ 1 owner, 2 warehouse managers, 3 shop managers');

        return [
            'owner'             => $owner,
            'warehouseManagers' => collect([$wm1, $wm2]),
            'shopManagers'      => collect([$sm1, $sm2, $sm3]),
        ];
    }

    // ═══════════════════════════════════════════════════════
    //  LOCATIONS
    // ═══════════════════════════════════════════════════════

    private function seedLocations(array $users): array
    {
        // Central warehouse
        $warehouse = Warehouse::create([
            'name'         => 'Kigali Central Warehouse',
            'code'         => 'WH-KGL-01',
            'address'      => 'KG 11 Ave, Gisozi Industrial Zone',
            'city'         => 'Kigali',
            'phone'        => '+250788400001',
            'manager_name' => $users['warehouseManagers'][0]->name,
            'is_active'    => true,
        ]);

        // Bind warehouse managers
        $users['warehouseManagers'][0]->update(['location_type' => 'warehouse', 'location_id' => $warehouse->id]);
        $users['warehouseManagers'][1]->update(['location_type' => 'warehouse', 'location_id' => $warehouse->id]);

        // 3 Shops in Kigali
        $shop1 = Shop::create([
            'name'                 => 'Kigalifootwear — Remera',
            'code'                 => 'SHOP-REM',
            'address'              => 'KG 9 Ave, Remera',
            'city'                 => 'Kigali',
            'phone'                => '+250788500001',
            'manager_name'         => $users['shopManagers'][0]->name,
            'default_warehouse_id' => $warehouse->id,
            'is_active'            => true,
        ]);

        $shop2 = Shop::create([
            'name'                 => 'Kigalifootwear — Nyamirambo',
            'code'                 => 'SHOP-NYM',
            'address'              => 'KN 3 Rd, Nyamirambo',
            'city'                 => 'Kigali',
            'phone'                => '+250788500002',
            'manager_name'         => $users['shopManagers'][1]->name,
            'default_warehouse_id' => $warehouse->id,
            'is_active'            => true,
        ]);

        $shop3 = Shop::create([
            'name'                 => 'Kigalifootwear — Kimironko',
            'code'                 => 'SHOP-KIM',
            'address'              => 'KG 7 Ave, Kimironko Market',
            'city'                 => 'Kigali',
            'phone'                => '+250788500003',
            'manager_name'         => $users['shopManagers'][2]->name,
            'default_warehouse_id' => $warehouse->id,
            'is_active'            => true,
        ]);

        // Bind shop managers
        $users['shopManagers'][0]->update(['location_type' => 'shop', 'location_id' => $shop1->id]);
        $users['shopManagers'][1]->update(['location_type' => 'shop', 'location_id' => $shop2->id]);
        $users['shopManagers'][2]->update(['location_type' => 'shop', 'location_id' => $shop3->id]);

        $this->command->info('   ✓ 1 warehouse, 3 shops (Remera · Nyamirambo · Kimironko)');

        return [
            'warehouse' => $warehouse,
            'shops'     => collect([$shop1, $shop2, $shop3]),
        ];
    }

    // ═══════════════════════════════════════════════════════
    //  TRANSPORTERS
    // ═══════════════════════════════════════════════════════

    private function seedTransporters(): array
    {
        $t1 = Transporter::create(['name' => 'Kigali Express Logistics', 'company_name' => 'KEL Ltd',       'phone' => '+250788600001', 'vehicle_number' => 'RAD 123 A', 'is_active' => true]);
        $t2 = Transporter::create(['name' => 'Muhire Transport',         'company_name' => 'Muhire SARL',   'phone' => '+250788600002', 'vehicle_number' => 'RAB 456 B', 'is_active' => true]);
        $t3 = Transporter::create(['name' => 'Swift Delivery Rwanda',    'company_name' => 'Swift Rwanda',  'phone' => '+250788600003', 'vehicle_number' => 'RAC 789 C', 'is_active' => true]);

        $this->command->info('   ✓ 3 transporters');

        return collect([$t1, $t2, $t3]);
    }

    // ═══════════════════════════════════════════════════════
    //  CATEGORIES
    // ═══════════════════════════════════════════════════════

    private function seedCategories(): array
    {
        // Root: Footwear
        $footwear = Category::create(['name' => 'Footwear', 'code' => 'FOOT', 'description' => 'All footwear products', 'is_active' => true]);

        $sneakers   = Category::create(['name' => 'Sneakers',       'code' => 'FOOT-SNK', 'parent_id' => $footwear->id, 'is_active' => true]);
        $dress      = Category::create(['name' => 'Dress Shoes',    'code' => 'FOOT-DRS', 'parent_id' => $footwear->id, 'is_active' => true]);
        $sandals    = Category::create(['name' => 'Sandals',        'code' => 'FOOT-SND', 'parent_id' => $footwear->id, 'is_active' => true]);
        $school     = Category::create(['name' => 'School Shoes',   'code' => 'FOOT-SCH', 'parent_id' => $footwear->id, 'is_active' => true]);
        $sports     = Category::create(['name' => 'Sports Shoes',   'code' => 'FOOT-SPT', 'parent_id' => $footwear->id, 'is_active' => true]);
        $ladies     = Category::create(['name' => "Ladies' Heels",  'code' => 'FOOT-LDH', 'parent_id' => $footwear->id, 'is_active' => true]);
        $boots      = Category::create(['name' => 'Boots',          'code' => 'FOOT-BOT', 'parent_id' => $footwear->id, 'is_active' => true]);
        $slippers   = Category::create(['name' => 'Slippers',       'code' => 'FOOT-SLP', 'parent_id' => $footwear->id, 'is_active' => true]);

        // Root: Accessories
        $accessories = Category::create(['name' => 'Accessories', 'code' => 'ACC', 'description' => 'Shoe & fashion accessories', 'is_active' => true]);

        $socks      = Category::create(['name' => 'Socks',          'code' => 'ACC-SOC', 'parent_id' => $accessories->id, 'is_active' => true]);
        $belts      = Category::create(['name' => 'Belts',          'code' => 'ACC-BLT', 'parent_id' => $accessories->id, 'is_active' => true]);
        $bags       = Category::create(['name' => 'Bags',           'code' => 'ACC-BAG', 'parent_id' => $accessories->id, 'is_active' => true]);
        $care       = Category::create(['name' => 'Shoe Care',      'code' => 'ACC-CRE', 'parent_id' => $accessories->id, 'is_active' => true]);

        $this->command->info('   ✓ 14 categories (8 footwear + 4 accessories + 2 roots)');

        return compact('footwear','sneakers','dress','sandals','school','sports','ladies','boots','slippers','accessories','socks','belts','bags','care');
    }

    // ═══════════════════════════════════════════════════════
    //  PRODUCTS
    //  All prices in DB cents (RWF × 100)
    //  purchase_price ≈ 60% of selling_price
    //  Each size = separate product (a box only contains one size)
    //  items_per_box: shoes = 6, sandals/slippers = 12, accessories = 24
    // ═══════════════════════════════════════════════════════

    private function seedProducts(array $cats): array
    {
        $products = collect();

        // ── SNEAKERS ────────────────────────────────────────
        // Nike Air Force 1 sizes 36-45
        $afSizes = [36,37,38,39,40,41,42,43,44,45];
        foreach ($afSizes as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['sneakers']->id,
                'name'                => "Nike Air Force 1 Low — Size {$sz}",
                'sku'                 => "NK-AF1-{$sz}",
                'barcode'             => "6001100" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Nike Air Force 1 Low, EU size {$sz}. Classic white leather sneaker.",
                'items_per_box'       => 6,
                'purchase_price'      => 3500000,   // RWF 35,000 purchase
                'selling_price'       => 5500000,   // RWF 55,000 sell
                'box_selling_price'   => 31500000,  // RWF 315,000 per box (6×)
                'low_stock_threshold' => 12,
                'reorder_point'       => 24,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'Nike EAC Distributor',
                'is_active'           => true,
            ]));
        }

        // Adidas Superstar sizes 37-45
        foreach ([37,38,39,40,41,42,43,44,45] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['sneakers']->id,
                'name'                => "Adidas Superstar — Size {$sz}",
                'sku'                 => "AD-SS-{$sz}",
                'barcode'             => "6001200" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Adidas Superstar classic, EU size {$sz}. Shell toe icon.",
                'items_per_box'       => 6,
                'purchase_price'      => 3200000,
                'selling_price'       => 5000000,
                'box_selling_price'   => 28500000,
                'low_stock_threshold' => 12,
                'reorder_point'       => 18,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'Adidas EAC Distributor',
                'is_active'           => true,
            ]));
        }

        // Puma RS-X sizes 38-44
        foreach ([38,39,40,41,42,43,44] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['sneakers']->id,
                'name'                => "Puma RS-X — Size {$sz}",
                'sku'                 => "PM-RSX-{$sz}",
                'barcode'             => "6001300" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Puma RS-X chunky retro sneaker, EU size {$sz}.",
                'items_per_box'       => 6,
                'purchase_price'      => 2800000,
                'selling_price'       => 4500000,
                'box_selling_price'   => 25500000,
                'low_stock_threshold' => 6,
                'reorder_point'       => 12,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'Puma Sub-Saharan Dist.',
                'is_active'           => true,
            ]));
        }

        // New Balance 574 sizes 38-45
        foreach ([38,39,40,41,42,43,44,45] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['sneakers']->id,
                'name'                => "New Balance 574 — Size {$sz}",
                'sku'                 => "NB-574-{$sz}",
                'barcode'             => "6001400" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "New Balance 574, EU size {$sz}. Comfort everyday sneaker.",
                'items_per_box'       => 6,
                'purchase_price'      => 3000000,
                'selling_price'       => 4800000,
                'box_selling_price'   => 27000000,
                'low_stock_threshold' => 6,
                'reorder_point'       => 12,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'NB Africa Importers',
                'is_active'           => true,
            ]));
        }

        // Converse Chuck Taylor sizes 36-45
        foreach ([36,37,38,39,40,41,42,43,44,45] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['sneakers']->id,
                'name'                => "Converse Chuck Taylor — Size {$sz}",
                'sku'                 => "CV-CTK-{$sz}",
                'barcode'             => "6001500" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Converse Chuck Taylor All Star, EU size {$sz}.",
                'items_per_box'       => 6,
                'purchase_price'      => 2500000,
                'selling_price'       => 4000000,
                'box_selling_price'   => 22500000,
                'low_stock_threshold' => 12,
                'reorder_point'       => 18,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'Converse EAC',
                'is_active'           => true,
            ]));
        }

        // ── SPORTS SHOES ────────────────────────────────────
        foreach ([37,38,39,40,41,42,43,44] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['sports']->id,
                'name'                => "Nike Revolution 6 Running — Size {$sz}",
                'sku'                 => "NK-RV6-{$sz}",
                'barcode'             => "6002100" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Nike Revolution 6 running shoe, EU size {$sz}.",
                'items_per_box'       => 6,
                'purchase_price'      => 3800000,
                'selling_price'       => 6200000,
                'box_selling_price'   => 35000000,
                'low_stock_threshold' => 6,
                'reorder_point'       => 12,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'Nike EAC Distributor',
                'is_active'           => true,
            ]));
        }

        foreach ([37,38,39,40,41,42,43,44] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['sports']->id,
                'name'                => "Adidas Runfalcon 3.0 — Size {$sz}",
                'sku'                 => "AD-RF3-{$sz}",
                'barcode'             => "6002200" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Adidas Runfalcon 3.0, EU size {$sz}. Lightweight training shoe.",
                'items_per_box'       => 6,
                'purchase_price'      => 3000000,
                'selling_price'       => 5000000,
                'box_selling_price'   => 28000000,
                'low_stock_threshold' => 6,
                'reorder_point'       => 12,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'Adidas EAC Distributor',
                'is_active'           => true,
            ]));
        }

        // ── SCHOOL SHOES ────────────────────────────────────
        foreach ([32,33,34,35,36,37,38,39,40,41,42] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['school']->id,
                'name'                => "Black School Shoe — Size {$sz}",
                'sku'                 => "SCH-BLK-{$sz}",
                'barcode'             => "6003100" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Classic black lace-up school shoe, EU size {$sz}.",
                'items_per_box'       => 6,
                'purchase_price'      => 1200000,
                'selling_price'       => 2000000,
                'box_selling_price'   => 11000000,
                'low_stock_threshold' => 18,
                'reorder_point'       => 36,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'East Africa Shoe Factory',
                'is_active'           => true,
            ]));
        }

        // ── DRESS SHOES (Men) ────────────────────────────────
        foreach ([40,41,42,43,44,45] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['dress']->id,
                'name'                => "Men's Oxford Derby — Size {$sz}",
                'sku'                 => "DRS-OXF-{$sz}",
                'barcode'             => "6004100" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Men's leather Oxford derby, EU size {$sz}. Brown/black available.",
                'items_per_box'       => 6,
                'purchase_price'      => 2500000,
                'selling_price'       => 4200000,
                'box_selling_price'   => 24000000,
                'low_stock_threshold' => 6,
                'reorder_point'       => 12,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'Leather Creations Ltd',
                'is_active'           => true,
            ]));
        }

        // ── LADIES' HEELS ────────────────────────────────────
        foreach ([36,37,38,39,40,41] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['ladies']->id,
                'name'                => "Ladies Block Heel Pump — Size {$sz}",
                'sku'                 => "LDY-BHP-{$sz}",
                'barcode'             => "6005100" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Ladies block heel pump, EU size {$sz}. Office & occasion wear.",
                'items_per_box'       => 6,
                'purchase_price'      => 1800000,
                'selling_price'       => 3000000,
                'box_selling_price'   => 17000000,
                'low_stock_threshold' => 6,
                'reorder_point'       => 12,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'Fashion Imports EAC',
                'is_active'           => true,
            ]));
        }

        foreach ([36,37,38,39,40,41] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['ladies']->id,
                'name'                => "Ladies Stiletto — Size {$sz}",
                'sku'                 => "LDY-STL-{$sz}",
                'barcode'             => "6005200" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "High stiletto heel, EU size {$sz}. Evening wear.",
                'items_per_box'       => 6,
                'purchase_price'      => 2200000,
                'selling_price'       => 3800000,
                'box_selling_price'   => 21500000,
                'low_stock_threshold' => 6,
                'reorder_point'       => 12,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'Fashion Imports EAC',
                'is_active'           => true,
            ]));
        }

        // ── SANDALS ─────────────────────────────────────────
        foreach ([36,37,38,39,40,41,42,43,44] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['sandals']->id,
                'name'                => "Leather Sandal — Size {$sz}",
                'sku'                 => "SND-LTH-{$sz}",
                'barcode'             => "6006100" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Open-toe leather sandal, EU size {$sz}.",
                'items_per_box'       => 12,
                'purchase_price'      => 800000,
                'selling_price'       => 1500000,
                'box_selling_price'   => 16500000,
                'low_stock_threshold' => 12,
                'reorder_point'       => 24,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'East Africa Sandal Works',
                'is_active'           => true,
            ]));
        }

        // ── SLIPPERS ─────────────────────────────────────────
        foreach ([36,37,38,39,40,41,42,43,44,45] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['slippers']->id,
                'name'                => "Foam Slipper — Size {$sz}",
                'sku'                 => "SLP-FOM-{$sz}",
                'barcode'             => "6007100" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Comfort foam house slipper, EU size {$sz}.",
                'items_per_box'       => 12,
                'purchase_price'      => 350000,
                'selling_price'       => 700000,
                'box_selling_price'   => 7800000,
                'low_stock_threshold' => 24,
                'reorder_point'       => 48,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'Foam Products Rwanda',
                'is_active'           => true,
            ]));
        }

        // ── BOOTS ────────────────────────────────────────────
        foreach ([39,40,41,42,43,44,45] as $sz) {
            $products->push(Product::create([
                'category_id'         => $cats['boots']->id,
                'name'                => "Men's Ankle Boot — Size {$sz}",
                'sku'                 => "BOT-ANK-{$sz}",
                'barcode'             => "6008100" . str_pad($sz, 3, '0', STR_PAD_LEFT),
                'description'         => "Men's genuine leather ankle boot, EU size {$sz}.",
                'items_per_box'       => 6,
                'purchase_price'      => 4500000,
                'selling_price'       => 7500000,
                'box_selling_price'   => 43000000,
                'low_stock_threshold' => 6,
                'reorder_point'       => 12,
                'unit_of_measure'     => 'pair',
                'supplier'            => 'Leather Creations Ltd',
                'is_active'           => true,
            ]));
        }

        // ── ACCESSORIES ──────────────────────────────────────

        // Socks (3-pack) — one size fits all
        foreach (['White', 'Black', 'Mixed'] as $color) {
            $products->push(Product::create([
                'category_id'         => $cats['socks']->id,
                'name'                => "Sports Socks 3-Pack — {$color}",
                'sku'                 => "SOC-3PK-" . strtoupper(substr($color, 0, 3)),
                'barcode'             => "6009100" . (array_search($color, ['White','Black','Mixed']) + 1),
                'description'         => "{$color} sports socks, 3-pack, one size fits all.",
                'items_per_box'       => 24,
                'purchase_price'      => 150000,
                'selling_price'       => 250000,
                'box_selling_price'   => 5500000,
                'low_stock_threshold' => 48,
                'reorder_point'       => 96,
                'unit_of_measure'     => 'pack',
                'supplier'            => 'Cotton Textile Rwanda',
                'is_active'           => true,
            ]));
        }

        // Belts
        foreach (['Black', 'Brown'] as $color) {
            $products->push(Product::create([
                'category_id'         => $cats['belts']->id,
                'name'                => "Leather Belt — {$color}",
                'sku'                 => "BLT-LTH-" . strtoupper(substr($color, 0, 3)),
                'barcode'             => "6009200" . (array_search($color, ['Black','Brown']) + 1),
                'description'         => "Genuine leather belt, {$color}, adjustable 85–115 cm.",
                'items_per_box'       => 24,
                'purchase_price'      => 1000000,
                'selling_price'       => 1800000,
                'box_selling_price'   => 40000000,
                'low_stock_threshold' => 24,
                'reorder_point'       => 48,
                'unit_of_measure'     => 'piece',
                'supplier'            => 'Leather Creations Ltd',
                'is_active'           => true,
            ]));
        }

        // Shoe care kit
        $products->push(Product::create([
            'category_id'         => $cats['care']->id,
            'name'                => 'Shoe Polish Kit — Black',
            'sku'                 => 'CRE-PLK-BLK',
            'barcode'             => '6009301',
            'description'         => 'Shoe polish kit: brush + cream + cloth, black.',
            'items_per_box'       => 24,
            'purchase_price'      => 400000,
            'selling_price'       => 700000,
            'box_selling_price'   => 15000000,
            'low_stock_threshold' => 24,
            'reorder_point'       => 48,
            'unit_of_measure'     => 'kit',
            'supplier'            => 'Shoe Care Rwanda',
            'is_active'           => true,
        ]));

        $products->push(Product::create([
            'category_id'         => $cats['care']->id,
            'name'                => 'Shoe Polish Kit — Brown',
            'sku'                 => 'CRE-PLK-BRN',
            'barcode'             => '6009302',
            'description'         => 'Shoe polish kit: brush + cream + cloth, brown.',
            'items_per_box'       => 24,
            'purchase_price'      => 400000,
            'selling_price'       => 700000,
            'box_selling_price'   => 15000000,
            'low_stock_threshold' => 24,
            'reorder_point'       => 48,
            'unit_of_measure'     => 'kit',
            'supplier'            => 'Shoe Care Rwanda',
            'is_active'           => true,
        ]));

        // Canvas Backpack
        $products->push(Product::create([
            'category_id'         => $cats['bags']->id,
            'name'                => 'Canvas Backpack — Black',
            'sku'                 => 'BAG-CVS-BLK',
            'barcode'             => '6009401',
            'description'         => 'Durable canvas backpack, 30L, black.',
            'items_per_box'       => 12,
            'purchase_price'      => 2500000,
            'selling_price'       => 4500000,
            'box_selling_price'   => 50000000,
            'low_stock_threshold' => 12,
            'reorder_point'       => 24,
            'unit_of_measure'     => 'piece',
            'supplier'            => 'Bags Rwanda Ltd',
            'is_active'           => true,
        ]));

        $this->command->info('   ✓ ' . $products->count() . ' products created');

        return $products;
    }

    // ═══════════════════════════════════════════════════════
    //  WAREHOUSE BOXES
    //  Warehouse gets abundant stock.
    //  Shops get their own boxes via transfers below.
    // ═══════════════════════════════════════════════════════

    private function seedWarehouseBoxes(mixed $products, array $locations, array $users): void
    {
        $warehouse = $locations['warehouse'];
        $shops     = $locations['shops'];
        $wm        = $users['warehouseManagers'][0];

        foreach ($products as $product) {
            // High-demand products (sneakers, school) get more boxes
            $isHighDemand = str_contains($product->sku, 'NK-') ||
                            str_contains($product->sku, 'AD-') ||
                            str_contains($product->sku, 'SCH-');
            $warehouseBoxes = $isHighDemand ? rand(15, 25) : rand(6, 14);

            // Create boxes in warehouse (spread over last 120 days by batch)
            for ($i = 0; $i < $warehouseBoxes; $i++) {
                $receivedDate  = now()->subDays(rand(2, 120));
                $batchMonth    = $receivedDate->format('Y-m');
                $isPartial     = (rand(1, 100) <= 4); // 4% arrive partial (damaged in transit)

                $itemsRemaining = $isPartial
                    ? rand((int)floor($product->items_per_box * 0.5), $product->items_per_box - 1)
                    : $product->items_per_box;

                Box::create([
                    'product_id'      => $product->id,
                    'box_code'        => $this->nextBoxCode($batchMonth),
                    'status'          => $isPartial ? 'partial' : 'full',
                    'items_total'     => $product->items_per_box,
                    'items_remaining' => $itemsRemaining,
                    'location_type'   => 'warehouse',
                    'location_id'     => $warehouse->id,
                    'received_by'     => $wm->id,
                    'received_at'     => $receivedDate,
                    'batch_number'    => 'BATCH-' . $batchMonth,
                    'expiry_date'     => null,
                    'damage_notes'    => $isPartial ? 'Minor transport damage on arrival' : null,
                ]);
            }

            // ── Seed SHOP stock directly ───────────────────
            // Each shop gets between 3–8 boxes of each popular product
            foreach ($shops as $shopIndex => $shop) {
                $shopBoxes = $isHighDemand ? rand(4, 8) : rand(2, 5);

                for ($j = 0; $j < $shopBoxes; $j++) {
                    $receivedDate = now()->subDays(rand(1, 60));
                    $batchMonth   = $receivedDate->format('Y-m');

                    // Some boxes are partial (items already sold from them)
                    $isPartial    = (rand(1, 100) <= 30); // 30% of shop stock is partial (selling in progress)
                    $itemsRemaining = $isPartial
                        ? rand(1, $product->items_per_box - 1)
                        : $product->items_per_box;

                    Box::create([
                        'product_id'      => $product->id,
                        'box_code'        => $this->nextBoxCode($batchMonth),
                        'status'          => $isPartial ? 'partial' : 'full',
                        'items_total'     => $product->items_per_box,
                        'items_remaining' => $itemsRemaining,
                        'location_type'   => 'shop',
                        'location_id'     => $shop->id,
                        'received_by'     => $users['shopManagers'][$shopIndex]->id,
                        'received_at'     => $receivedDate,
                        'batch_number'    => 'BATCH-' . $batchMonth,
                        'expiry_date'     => null,
                    ]);
                }
            }
        }

        $total = Box::count();
        $this->command->info("   ✓ {$total} boxes (warehouse + all 3 shops)");
    }

    // ═══════════════════════════════════════════════════════
    //  TRANSFERS
    //  Creates a realistic pipeline with different statuses
    // ═══════════════════════════════════════════════════════

    private function seedTransfers(array $locations, array $users, mixed $transporters, mixed $products): void
    {
        $warehouse = $locations['warehouse'];
        $shops     = $locations['shops'];
        $now       = now();
        $trCounter = 1;

        // Transfer scenarios with different statuses (newest first)
        $scenarios = [
            // Pending (just requested)
            ['status' => 'pending',    'shop_idx' => 0, 'days_ago' => 0,  'num_products' => 5],
            ['status' => 'pending',    'shop_idx' => 1, 'days_ago' => 1,  'num_products' => 4],

            // Approved (warehouse reviewed)
            ['status' => 'approved',   'shop_idx' => 2, 'days_ago' => 1,  'num_products' => 6],
            ['status' => 'approved',   'shop_idx' => 0, 'days_ago' => 2,  'num_products' => 5],

            // In transit (boxes packed, on the truck)
            ['status' => 'in_transit', 'shop_idx' => 1, 'days_ago' => 1,  'num_products' => 7],

            // Delivered (truck arrived, not yet scanned in by shop)
            ['status' => 'delivered',  'shop_idx' => 2, 'days_ago' => 0,  'num_products' => 4],

            // Fully received (completed)
            ['status' => 'received',   'shop_idx' => 0, 'days_ago' => 7,  'num_products' => 8],
            ['status' => 'received',   'shop_idx' => 1, 'days_ago' => 12, 'num_products' => 6],
            ['status' => 'received',   'shop_idx' => 2, 'days_ago' => 18, 'num_products' => 7],
            ['status' => 'received',   'shop_idx' => 0, 'days_ago' => 25, 'num_products' => 10],
            ['status' => 'received',   'shop_idx' => 1, 'days_ago' => 35, 'num_products' => 8],
            ['status' => 'received',   'shop_idx' => 2, 'days_ago' => 45, 'num_products' => 6],
        ];

        foreach ($scenarios as $sc) {
            $shop        = $shops[$sc['shop_idx']];
            $shopManager = $users['shopManagers'][$sc['shop_idx']];
            $wm          = $users['warehouseManagers']->random();
            $transporter = $transporters->random();
            $requestedAt = $now->copy()->subDays($sc['days_ago']);
            $trNum       = sprintf('TR-%s-%04d', $requestedAt->format('Ym'), $trCounter++);
            $status      = $sc['status'];

            // Pick random products for this transfer
            $selectedProducts = $products->random(min($sc['num_products'], $products->count()));

            // Build transfer
            $transfer = Transfer::create([
                'transfer_number'   => $trNum,
                'from_warehouse_id' => $warehouse->id,
                'to_shop_id'        => $shop->id,
                'status'            => $status,
                'requested_by'      => $shopManager->id,
                'requested_at'      => $requestedAt,
                'reviewed_by'       => in_array($status, ['approved','in_transit','delivered','received']) ? $wm->id : null,
                'reviewed_at'       => in_array($status, ['approved','in_transit','delivered','received']) ? $requestedAt->copy()->addHours(rand(2,8)) : null,
                'transporter_id'    => in_array($status, ['in_transit','delivered','received']) ? $transporter->id : null,
                'delivered_at'      => in_array($status, ['delivered','received']) ? $requestedAt->copy()->addDays(1) : null,
                'received_at'       => $status === 'received' ? $requestedAt->copy()->addDays(1)->addHours(rand(1,6)) : null,
                'received_by'       => $status === 'received' ? $shopManager->id : null,
                'notes'             => 'Routine stock replenishment.',
            ]);

            // Transfer items (product-level request quantities)
            foreach ($selectedProducts as $product) {
                $requestedQty = rand(1, 3) * $product->items_per_box;
                $receivedQty  = $status === 'received' ? $requestedQty : null;

                TransferItem::create([
                    'transfer_id'        => $transfer->id,
                    'product_id'         => $product->id,
                    'quantity_requested' => $requestedQty,
                    'quantity_shipped'   => in_array($status, ['in_transit','delivered','received']) ? $requestedQty : null,
                    'quantity_received'  => $receivedQty,
                    'has_discrepancy'    => false,
                ]);

                // Assign boxes for in_transit / delivered / received
                if (in_array($status, ['in_transit','delivered','received'])) {
                    $boxesNeeded = (int)ceil($requestedQty / $product->items_per_box);
                    $warehouseBoxes = Box::where('product_id', $product->id)
                        ->where('location_type', 'warehouse')
                        ->where('location_id', $warehouse->id)
                        ->where('status', 'full')
                        ->take($boxesNeeded)
                        ->get();

                    foreach ($warehouseBoxes as $box) {
                        TransferBox::create([
                            'transfer_id'     => $transfer->id,
                            'box_id'          => $box->id,
                            'scanned_out_at'  => $requestedAt->copy()->addHours(rand(4,12)),
                            'scanned_out_by'  => $wm->id,
                            'scanned_in_at'   => $status === 'received' ? $requestedAt->copy()->addDays(1)->addHours(rand(1,4)) : null,
                            'scanned_in_by'   => $status === 'received' ? $shopManager->id : null,
                            'is_received'     => $status === 'received',
                        ]);

                        // Move box to shop if received
                        if ($status === 'received') {
                            $box->update([
                                'location_type' => 'shop',
                                'location_id'   => $shop->id,
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('   ✓ ' . $trCounter - 1 . ' transfers created');
    }

    // ═══════════════════════════════════════════════════════
    //  SALES — 90 days of history
    //  Mix of full-box and individual item sales
    //  Realistic Rwandan customer names and payment methods
    // ═══════════════════════════════════════════════════════

    private function seedSales(array $locations, array $users): void
    {
        $shops     = $locations['shops'];
        $now       = now();
        $saleCount = 0;

        $rwandaNames = [
            'Aline Uwera','Thierry Hakizimana','Claudine Nyiraneza','Patrick Niyonzima',
            'Solange Mukamana','Eric Bizimana','Vestine Uwimana','Joel Nsabimana',
            'Nadine Ingabire','Christian Ndayisenga','Grace Umubyeyi','Kevin Gasasira',
            'Sandrine Nyirahabimana','Jules Nizeyimana','Immaculee Munezero',
        ];

        $phonePrefix = ['+25078','+25072',''+25073'];

        $paymentWeights = ['cash' => 55, 'mobile_money' => 35, 'card' => 8, 'bank_transfer' => 2];
        $paymentPool = [];
        foreach ($paymentWeights as $method => $weight) {
            for ($i = 0; $i < $weight; $i++) $paymentPool[] = $method;
        }

        foreach ($shops as $shopIdx => $shop) {
            $shopManager = $users['shopManagers'][$shopIdx];

            for ($day = 89; $day >= 0; $day--) {
                $date = $now->copy()->subDays($day);
                $isWeekend = in_array($date->dayOfWeek, [6, 0]);

                // Sales per day: weekends busier
                $salesPerDay = $isWeekend ? rand(8, 18) : rand(3, 10);

                for ($s = 0; $s < $salesPerDay; $s++) {
                    // Get boxes available at this shop for products
                    $availableBoxes = Box::where('location_type', 'shop')
                        ->where('location_id', $shop->id)
                        ->whereIn('status', ['full','partial'])
                        ->where('items_remaining', '>', 0)
                        ->with('product')
                        ->inRandomOrder()
                        ->limit(6)
                        ->get();

                    if ($availableBoxes->isEmpty()) continue;

                    $saleTime   = $date->copy()->setTime(rand(8,20), rand(0,59));
                    $payment    = $paymentPool[array_rand($paymentPool)];
                    $hasName    = rand(1,100) <= 40;
                    $saleNumber = sprintf('SALE-%s-%05d', $saleTime->format('Ymd'), ++$saleCount);

                    // Determine sale type (10% full box, 90% individual items)
                    $isFullBoxSale = rand(1,100) <= 10;

                    $sale = Sale::create([
                        'sale_number'    => $saleNumber,
                        'shop_id'        => $shop->id,
                        'type'           => $isFullBoxSale ? 'full_box' : 'individual_items',
                        'payment_method' => $payment,
                        'subtotal'       => 0,
                        'tax'            => 0,
                        'discount'       => 0,
                        'total'          => 0,
                        'customer_name'  => $hasName ? $rwandaNames[array_rand($rwandaNames)] : null,
                        'customer_phone' => $hasName ? $phonePrefix[array_rand($phonePrefix)] . rand(1000000, 9999999) : null,
                        'sold_by'        => $shopManager->id,
                        'sale_date'      => $saleTime,
                        'has_price_override' => false,
                    ]);

                    $subtotal = 0;

                    // Pick 1–3 boxes for this sale
                    $itemCount = min(rand(1, 3), $availableBoxes->count());
                    $saleBoxes = $availableBoxes->take($itemCount);

                    foreach ($saleBoxes as $box) {
                        $product = $box->product;
                        if (!$product) continue;

                        if ($isFullBoxSale) {
                            // Sell the entire box
                            $qtySold   = $box->items_remaining;
                            $unitPrice = $product->box_selling_price ?? $product->calculateBoxPrice();
                            $lineTotal = $unitPrice;
                        } else {
                            // Sell 1–items_per_box items individually
                            $maxSell   = min($box->items_remaining, rand(1, min(4, $product->items_per_box)));
                            $qtySold   = $maxSell;
                            $unitPrice = $product->selling_price;
                            $lineTotal = $unitPrice * $qtySold;
                        }

                        // Occasional price discount (5% chance)
                        $priceModified = rand(1,100) <= 5;
                        $actualPrice   = $priceModified
                            ? (int)round($unitPrice * rand(85,95) / 100)
                            : $unitPrice;

                        SaleItem::create([
                            'sale_id'                  => $sale->id,
                            'product_id'               => $product->id,
                            'box_id'                   => $box->id,
                            'quantity_sold'            => $qtySold,
                            'is_full_box'              => $isFullBoxSale,
                            'original_unit_price'      => $unitPrice,
                            'actual_unit_price'        => $actualPrice,
                            'line_total'               => $isFullBoxSale ? $actualPrice : ($actualPrice * $qtySold),
                            'price_was_modified'       => $priceModified,
                            'price_modification_reason'=> $priceModified ? 'Customer loyalty discount' : null,
                        ]);

                        $lineAmt = $isFullBoxSale ? $actualPrice : ($actualPrice * $qtySold);
                        $subtotal += $lineAmt;

                        // Consume from box
                        $newRemaining = max(0, $box->items_remaining - $qtySold);
                        $newStatus    = $newRemaining === 0 ? 'empty'
                            : ($newRemaining < $product->items_per_box ? 'partial' : 'full');
                        $box->update(['items_remaining' => $newRemaining, 'status' => $newStatus]);
                    }

                    // 18% VAT on some sales (government contracts / receipts)
                    $addTax    = rand(1,100) <= 20;
                    $tax       = $addTax ? (int)round($subtotal * 0.18) : 0;
                    $discount  = 0;

                    $sale->update([
                        'subtotal' => $subtotal,
                        'tax'      => $tax,
                        'discount' => $discount,
                        'total'    => $subtotal + $tax - $discount,
                    ]);
                }
            }
        }

        $this->command->info("   ✓ {$saleCount} sales created across 3 shops over 90 days");
    }

    // ═══════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════

    private function nextBoxCode(string $batchMonth): string
    {
        return sprintf('BOX-%s-%05d', $batchMonth, $this->boxCounter++);
    }

    private function printSummary(array $users, array $locations, mixed $products): void
    {
        $this->command->info('');
        $this->command->info(str_repeat('═', 56));
        $this->command->info('  📊  SEED SUMMARY');
        $this->command->info(str_repeat('═', 56));
        $this->command->info('');
        $this->command->info('  👥 USERS');
        $this->command->info("      owner@kigalifootwear.rw         / password");
        $this->command->info("      wm1@kigalifootwear.rw           / password (warehouse)");
        $this->command->info("      wm2@kigalifootwear.rw           / password (warehouse)");
        $this->command->info("      shop1@kigalifootwear.rw         / password (Remera)");
        $this->command->info("      shop2@kigalifootwear.rw         / password (Nyamirambo)");
        $this->command->info("      shop3@kigalifootwear.rw         / password (Kimironko)");
        $this->command->info('');
        $this->command->info('  🏢 LOCATIONS');
        $this->command->info("      1 warehouse  ·  3 shops (Kigali)");
        $this->command->info('');
        $this->command->info('  👟 PRODUCTS:  ' . $products->count());
        $this->command->info('  📦 BOXES:     ' . Box::count());
        $this->command->info('  🚛 TRANSFERS: ' . Transfer::count());
        $this->command->info('  🛒 SALES:     ' . Sale::count());
        $this->command->info('  📋 SALE ITEMS:' . SaleItem::count());
        $this->command->info('');

        // Price sample
        $this->command->info('  💰 PRICE SAMPLES (stored in cents):');
        $this->command->info('      Nike AF1 Size 42 → sell: RWF 55,000 → DB: 5,500,000');
        $this->command->info('      School Shoe 38   → sell: RWF 20,000 → DB: 2,000,000');
        $this->command->info('      Socks 3-Pack     → sell: RWF  2,500 → DB:   250,000');
        $this->command->info('');
        $this->command->info(str_repeat('═', 56));
        $this->command->info('');
    }
}
```

---

## Step 2 — Register in `DatabaseSeeder.php`

**Target:** `database/seeders/DatabaseSeeder.php`

Find the `run()` method. Replace the entire `run()` body with:

```php
public function run(): void
{
    $this->call(RwandaShoeBusinessSeeder::class);
}
```

If the file has other logic you need to preserve, instead **add** this line at the end of the existing `run()`:

> Actually — replace the entire run() with the single `$this->call()`. The new seeder wipes and rebuilds everything.

---

## Step 3 — Fix the `$phonePrefix` Syntax Error

In `seedSales()`, there is a typo in the `$phonePrefix` array:

```php
// ❌ WRONG (one entry has a double-quote before +)
$phonePrefix = ['+25078',''+25072',''+25073'];

// ✅ CORRECT
$phonePrefix = ['+25078', '+25072', '+25073'];
```

Make sure this is correct before running.

---

## Step 4 — Check TransferItem Columns

```bash
grep -n "fillable" app/Models/TransferItem.php
cat database/migrations/*transfer_items* 2>/dev/null | grep -A 40 "Schema::create"
```

The seeder uses: `transfer_id, product_id, quantity_requested, quantity_shipped, quantity_received, has_discrepancy`

If the column names differ in your schema, adjust the TransferItem::create() calls in `seedTransfers()`.

---

## Step 5 — Check TransferBox Columns

```bash
grep -n "fillable" app/Models/TransferBox.php
cat database/migrations/*transfer_boxes* 2>/dev/null | grep -A 40 "Schema::create"
```

The seeder uses: `transfer_id, box_id, scanned_out_at, scanned_out_by, scanned_in_at, scanned_in_by, is_received`

Adjust if column names differ.

---

## Step 6 — Run the Seeder

```bash
php artisan migrate:fresh
php artisan db:seed --class=RwandaShoeBusinessSeeder
```

If migrate:fresh fails due to enum type conflicts:

```bash
php artisan migrate:fresh --drop-views
php artisan db:seed --class=RwandaShoeBusinessSeeder
```

---

## Step 7 — Verify Data

```bash
php artisan tinker --execute="
echo 'Products: '  . App\Models\Product::count() . PHP_EOL;
echo 'Boxes: '     . App\Models\Box::count() . PHP_EOL;
echo 'Transfers: ' . App\Models\Transfer::count() . PHP_EOL;
echo 'Sales: '     . App\Models\Sale::count() . PHP_EOL;
echo 'Revenue (RWF): ' . number_format(App\Models\Sale::sum('total') / 100) . PHP_EOL;

\$sample = App\Models\Product::where('sku', 'NK-AF1-42')->first();
if (\$sample) {
    echo 'Nike AF1 Size 42 → sell price (DB cents): ' . \$sample->selling_price . PHP_EOL;
    echo 'Nike AF1 Size 42 → sell price (RWF): ' . number_format(\$sample->selling_price / 100) . PHP_EOL;
}

echo 'Warehouse boxes: ' . App\Models\Box::where('location_type','warehouse')->count() . PHP_EOL;
echo 'Shop boxes: '      . App\Models\Box::where('location_type','shop')->count() . PHP_EOL;
"
```

Expected output:
```
Products: ~130
Boxes: ~1,500-3,000
Transfers: 12
Sales: ~2,000-4,500
Revenue (RWF): [large number in millions]
Nike AF1 Size 42 → sell price (DB cents): 5500000
Nike AF1 Size 42 → sell price (RWF): 55,000
Warehouse boxes: [majority]
Shop boxes: [significant portion]
```

---

## Key Design Decisions

| Decision | Rationale |
|---|---|
| **Prices × 100 in DB** | System convention: RWF 55,000 stored as `5500000` cents |
| **Size = separate SKU** | Each size is a distinct product. A box only contains one size. |
| **6 pairs per box** | Standard shoe wholesale box. Sandals/slippers = 12. Accessories = 24. |
| **80% shoes by SKU** | ~110 shoe SKUs, ~20 accessory SKUs |
| **Shops seeded directly** | Shops get boxes without going through full transfer workflow for historical stock |
| **30% partial shop boxes** | Realistic: some boxes are being actively sold from |
| **10% full-box sales** | Wholesale customers occasionally buy whole boxes |
| **RWF prices realistic** | Nike: 55,000 · School shoes: 20,000 · Sandals: 15,000 · Socks 3-pack: 2,500 |
| **Rwandan names/phones** | Alice Uwimana, Patrick Niyonzima, +2507812345678 |
