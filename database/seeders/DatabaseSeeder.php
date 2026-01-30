<?php

namespace Database\Seeders;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use App\Enums\PaymentMethod;
use App\Enums\SaleType;
use App\Enums\TransferStatus;
use App\Enums\UserRole;
use App\Models\Box;
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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🌱 Starting database seeding...');
            
            $users = $this->seedUsers();
            $warehouses = $this->seedWarehouses($users);
            $shops = $this->seedShops($warehouses, $users);
            $transporters = $this->seedTransporters();
            $categories = $this->seedCategories();
            $products = $this->seedProducts($categories);
            $boxes = $this->seedBoxes($products, $warehouses, $users);
            $this->seedTransfers($warehouses, $shops, $users, $transporters, $products, $boxes);
            $this->seedSales($shops, $users);
            
            $this->command->info('');
            $this->command->info('✅ Database seeded successfully!');
            $this->command->info('');
            $this->command->info('📧 Login Credentials:');
            $this->command->info('   Owner: owner@inventory.com / password');
            $this->command->info('   Warehouse Manager: warehouse1@inventory.com / password');
            $this->command->info('   Shop Manager: shop1@inventory.com / password');
        });
    }

    private function seedUsers(): object
    {
        $this->command->info('👥 Creating users...');

        $owner = User::create([
            'name' => 'System Owner',
            'email' => 'owner@inventory.com',
            'password' => Hash::make('password'),
            'phone' => '+250788123456',
            'role' => UserRole::OWNER,
            'location_type' => null,
            'location_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $warehouseManager1 = User::create([
            'name' => 'John Warehouse',
            'email' => 'warehouse1@inventory.com',
            'password' => Hash::make('password'),
            'phone' => '+250788234567',
            'role' => UserRole::WAREHOUSE_MANAGER,
            'location_type' => null,
            'location_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $warehouseManager2 = User::create([
            'name' => 'Mary Warehouse',
            'email' => 'warehouse2@inventory.com',
            'password' => Hash::make('password'),
            'phone' => '+250788345678',
            'role' => UserRole::WAREHOUSE_MANAGER,
            'location_type' => null,
            'location_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $shopManager1 = User::create([
            'name' => 'Alice Shop',
            'email' => 'shop1@inventory.com',
            'password' => Hash::make('password'),
            'phone' => '+250788456789',
            'role' => UserRole::SHOP_MANAGER,
            'location_type' => null,
            'location_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $shopManager2 = User::create([
            'name' => 'Bob Shop',
            'email' => 'shop2@inventory.com',
            'password' => Hash::make('password'),
            'phone' => '+250788567890',
            'role' => UserRole::SHOP_MANAGER,
            'location_type' => null,
            'location_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $shopManager3 = User::create([
            'name' => 'Carol Shop',
            'email' => 'shop3@inventory.com',
            'password' => Hash::make('password'),
            'phone' => '+250788678901',
            'role' => UserRole::SHOP_MANAGER,
            'location_type' => null,
            'location_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('   ✓ Created 6 users');

        return (object)[
            'owner' => $owner,
            'warehouseManagers' => collect([$warehouseManager1, $warehouseManager2]),
            'shopManagers' => collect([$shopManager1, $shopManager2, $shopManager3]),
        ];
    }

    private function seedWarehouses($users): object
    {
        $this->command->info('🏭 Creating warehouses...');

        // Schema: name, code, address, city, phone, manager_name, is_active
        $warehouse1 = Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'WH-001',
            'address' => 'KG 11 Ave, Kigali',
            'city' => 'Kigali',
            'phone' => '+250788111222',
            'manager_name' => 'John Warehouse',
            'is_active' => true,
        ]);

        $warehouse2 = Warehouse::create([
            'name' => 'Secondary Warehouse',
            'code' => 'WH-002',
            'address' => 'KN 5 Rd, Kigali',
            'city' => 'Kigali',
            'phone' => '+250788333444',
            'manager_name' => 'Mary Warehouse',
            'is_active' => true,
        ]);

        // Assign managers to warehouses
        $users->warehouseManagers[0]->update([
            'location_type' => LocationType::WAREHOUSE,
            'location_id' => $warehouse1->id,
        ]);

        $users->warehouseManagers[1]->update([
            'location_type' => LocationType::WAREHOUSE,
            'location_id' => $warehouse2->id,
        ]);

        $this->command->info('   ✓ Created 2 warehouses');

        return (object)[
            'main' => $warehouse1,
            'secondary' => $warehouse2,
            'all' => collect([$warehouse1, $warehouse2]),
        ];
    }

    private function seedShops($warehouses, $users): object
    {
        $this->command->info('🏪 Creating shops...');

        // Schema: name, code, address, city, phone, manager_name, default_warehouse_id, is_active
        $shopA = Shop::create([
            'name' => 'Shop A - Downtown',
            'code' => 'SHOP-A',
            'address' => 'KN 4 Ave, Kigali City',
            'city' => 'Kigali',
            'phone' => '+250788555666',
            'manager_name' => 'Alice Shop',
            'default_warehouse_id' => $warehouses->main->id,
            'is_active' => true,
        ]);

        $shopB = Shop::create([
            'name' => 'Shop B - Kimironko',
            'code' => 'SHOP-B',
            'address' => 'KG 7 Ave, Kimironko',
            'city' => 'Kigali',
            'phone' => '+250788777888',
            'manager_name' => 'Bob Shop',
            'default_warehouse_id' => $warehouses->main->id,
            'is_active' => true,
        ]);

        $shopC = Shop::create([
            'name' => 'Shop C - Remera',
            'code' => 'SHOP-C',
            'address' => 'KG 15 Rd, Remera',
            'city' => 'Kigali',
            'phone' => '+250788999000',
            'manager_name' => 'Carol Shop',
            'default_warehouse_id' => $warehouses->main->id,
            'is_active' => true,
        ]);

        // Assign managers to shops
        $users->shopManagers[0]->update([
            'location_type' => LocationType::SHOP,
            'location_id' => $shopA->id,
        ]);

        $users->shopManagers[1]->update([
            'location_type' => LocationType::SHOP,
            'location_id' => $shopB->id,
        ]);

        $users->shopManagers[2]->update([
            'location_type' => LocationType::SHOP,
            'location_id' => $shopC->id,
        ]);

        $this->command->info('   ✓ Created 3 shops');

        return (object)[
            'shopA' => $shopA,
            'shopB' => $shopB,
            'shopC' => $shopC,
            'all' => collect([$shopA, $shopB, $shopC]),
        ];
    }

    private function seedTransporters(): object
    {
        $this->command->info('🚚 Creating transporters...');

        $transporter1 = Transporter::create([
            'name' => 'David Transport',
            'phone' => '+250788112233',
            'vehicle_number' => 'RAD 123 A',
            'license_number' => 'LIC-2024-001',
            'is_active' => true,
        ]);

        $transporter2 = Transporter::create([
            'name' => 'Express Delivery Services',
            'phone' => '+250788445566',
            'vehicle_number' => 'RAD 456 B',
            'license_number' => 'LIC-2024-002',
            'is_active' => true,
        ]);

        $transporter3 = Transporter::create([
            'name' => 'Quick Logistics',
            'phone' => '+250788778899',
            'vehicle_number' => 'RAD 789 C',
            'license_number' => 'LIC-2024-003',
            'is_active' => true,
        ]);

        $this->command->info('   ✓ Created 3 transporters');

        return (object)[
            'all' => collect([$transporter1, $transporter2, $transporter3]),
        ];
    }

    private function seedCategories(): object
    {
        $this->command->info('📦 Creating categories...');

        $electronics = Category::create([
            'name' => 'Electronics',
            'code' => 'ELEC',
            'description' => 'Electronic devices and accessories',
            'is_active' => true,
        ]);

        $foodBeverages = Category::create([
            'name' => 'Food & Beverages',
            'code' => 'FOOD',
            'description' => 'Food items and drinks',
            'is_active' => true,
        ]);

        $clothing = Category::create([
            'name' => 'Clothing',
            'code' => 'CLOTH',
            'description' => 'Apparel and fashion items',
            'is_active' => true,
        ]);

        $hardware = Category::create([
            'name' => 'Hardware & Tools',
            'code' => 'HARD',
            'description' => 'Hardware and construction tools',
            'is_active' => true,
        ]);

        $homeGarden = Category::create([
            'name' => 'Home & Garden',
            'code' => 'HOME',
            'description' => 'Home improvement and garden supplies',
            'is_active' => true,
        ]);

        // Subcategories
        Category::create(['name' => 'Mobile Phones', 'code' => 'ELEC-MOBILE', 'parent_id' => $electronics->id, 'is_active' => true]);
        Category::create(['name' => 'Laptops & Computers', 'code' => 'ELEC-COMP', 'parent_id' => $electronics->id, 'is_active' => true]);
        Category::create(['name' => 'Audio & Video', 'code' => 'ELEC-AV', 'parent_id' => $electronics->id, 'is_active' => true]);
        Category::create(['name' => 'Snacks', 'code' => 'FOOD-SNACK', 'parent_id' => $foodBeverages->id, 'is_active' => true]);
        Category::create(['name' => 'Beverages', 'code' => 'FOOD-BEV', 'parent_id' => $foodBeverages->id, 'is_active' => true]);
        Category::create(['name' => 'Men\'s Clothing', 'code' => 'CLOTH-MEN', 'parent_id' => $clothing->id, 'is_active' => true]);
        Category::create(['name' => 'Women\'s Clothing', 'code' => 'CLOTH-WOMEN', 'parent_id' => $clothing->id, 'is_active' => true]);

        $this->command->info('   ✓ Created 12 categories');

        return (object)['all' => Category::all()];
    }

    private function seedProducts($categories): object
    {
        $this->command->info('📱 Creating products...');

        $products = [
            ['category' => 'ELEC-MOBILE', 'name' => 'Samsung Galaxy A54', 'sku' => 'PROD-001', 'barcode' => '8801234567890', 'items_per_box' => 10, 'purchase_price' => 35000000, 'selling_price' => 45000000, 'low_stock_threshold' => 20],
            ['category' => 'ELEC-MOBILE', 'name' => 'iPhone 13', 'sku' => 'PROD-002', 'barcode' => '8802234567891', 'items_per_box' => 5, 'purchase_price' => 70000000, 'selling_price' => 90000000, 'low_stock_threshold' => 10],
            ['category' => 'ELEC-MOBILE', 'name' => 'Tecno Spark 10', 'sku' => 'PROD-003', 'barcode' => '8803234567892', 'items_per_box' => 15, 'purchase_price' => 12000000, 'selling_price' => 16000000, 'low_stock_threshold' => 30],
            ['category' => 'ELEC-COMP', 'name' => 'HP Pavilion 15', 'sku' => 'PROD-004', 'barcode' => '8804234567893', 'items_per_box' => 3, 'purchase_price' => 50000000, 'selling_price' => 65000000, 'low_stock_threshold' => 6],
            ['category' => 'ELEC-COMP', 'name' => 'Dell Inspiron 14', 'sku' => 'PROD-005', 'barcode' => '8805234567894', 'items_per_box' => 3, 'purchase_price' => 48000000, 'selling_price' => 62000000, 'low_stock_threshold' => 6],
            ['category' => 'ELEC-AV', 'name' => 'JBL Bluetooth Speaker', 'sku' => 'PROD-006', 'barcode' => '8806234567895', 'items_per_box' => 20, 'purchase_price' => 3000000, 'selling_price' => 4200000, 'low_stock_threshold' => 40],
            ['category' => 'ELEC-AV', 'name' => 'Sony Headphones WH-1000XM4', 'sku' => 'PROD-007', 'barcode' => '8807234567896', 'items_per_box' => 12, 'purchase_price' => 25000000, 'selling_price' => 32000000, 'low_stock_threshold' => 24],
            ['category' => 'FOOD-SNACK', 'name' => 'Pringles Original 165g', 'sku' => 'PROD-008', 'barcode' => '8808234567897', 'items_per_box' => 48, 'purchase_price' => 250000, 'selling_price' => 350000, 'low_stock_threshold' => 96],
            ['category' => 'FOOD-SNACK', 'name' => 'Oreo Cookies 154g', 'sku' => 'PROD-009', 'barcode' => '8809234567898', 'items_per_box' => 60, 'purchase_price' => 180000, 'selling_price' => 250000, 'low_stock_threshold' => 120],
            ['category' => 'FOOD-SNACK', 'name' => 'Lay\'s Chips 50g', 'sku' => 'PROD-010', 'barcode' => '8810234567899', 'items_per_box' => 100, 'purchase_price' => 80000, 'selling_price' => 120000, 'low_stock_threshold' => 200],
            ['category' => 'FOOD-BEV', 'name' => 'Coca-Cola 500ml', 'sku' => 'PROD-011', 'barcode' => '8811234567800', 'items_per_box' => 24, 'purchase_price' => 60000, 'selling_price' => 100000, 'low_stock_threshold' => 48],
            ['category' => 'FOOD-BEV', 'name' => 'Fanta Orange 500ml', 'sku' => 'PROD-012', 'barcode' => '8812234567801', 'items_per_box' => 24, 'purchase_price' => 60000, 'selling_price' => 100000, 'low_stock_threshold' => 48],
            ['category' => 'CLOTH-MEN', 'name' => 'Men\'s Cotton T-Shirt', 'sku' => 'PROD-013', 'barcode' => '8813234567802', 'items_per_box' => 30, 'purchase_price' => 500000, 'selling_price' => 750000, 'low_stock_threshold' => 60],
            ['category' => 'CLOTH-MEN', 'name' => 'Men\'s Jeans', 'sku' => 'PROD-014', 'barcode' => '8814234567803', 'items_per_box' => 20, 'purchase_price' => 1200000, 'selling_price' => 1800000, 'low_stock_threshold' => 40],
            ['category' => 'CLOTH-WOMEN', 'name' => 'Women\'s Dress', 'sku' => 'PROD-015', 'barcode' => '8815234567804', 'items_per_box' => 20, 'purchase_price' => 1500000, 'selling_price' => 2200000, 'low_stock_threshold' => 40],
            ['category' => 'CLOTH-WOMEN', 'name' => 'Women\'s Blouse', 'sku' => 'PROD-016', 'barcode' => '8816234567805', 'items_per_box' => 25, 'purchase_price' => 800000, 'selling_price' => 1200000, 'low_stock_threshold' => 50],
            ['category' => 'HARD', 'name' => 'Hammer Set', 'sku' => 'PROD-017', 'barcode' => '8817234567806', 'items_per_box' => 12, 'purchase_price' => 1000000, 'selling_price' => 1500000, 'low_stock_threshold' => 24],
            ['category' => 'HARD', 'name' => 'Screwdriver Set', 'sku' => 'PROD-018', 'barcode' => '8818234567807', 'items_per_box' => 15, 'purchase_price' => 800000, 'selling_price' => 1200000, 'low_stock_threshold' => 30],
            ['category' => 'HOME', 'name' => 'LED Light Bulb 15W', 'sku' => 'PROD-019', 'barcode' => '8819234567808', 'items_per_box' => 50, 'purchase_price' => 200000, 'selling_price' => 300000, 'low_stock_threshold' => 100],
            ['category' => 'HOME', 'name' => 'Kitchen Knife Set', 'sku' => 'PROD-020', 'barcode' => '8820234567809', 'items_per_box' => 10, 'purchase_price' => 1500000, 'selling_price' => 2200000, 'low_stock_threshold' => 20],
        ];

        foreach ($products as $productData) {
            $category = Category::where('code', $productData['category'])->first();
            if ($category) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'sku' => $productData['sku'],
                    'barcode' => $productData['barcode'],
                    'description' => "High quality {$productData['name']}",
                    'items_per_box' => $productData['items_per_box'],
                    'purchase_price' => $productData['purchase_price'],
                    'selling_price' => $productData['selling_price'],
                    'low_stock_threshold' => $productData['low_stock_threshold'],
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('   ✓ Created 20 products');

        return (object)['all' => Product::all()];
    }

    private function seedBoxes($products, $warehouses, $users): object
    {
        $this->command->info('📦 Creating boxes...');

        $boxCounter = 1;
        $today = now();
        $allBoxes = collect();

        foreach ($products->all as $product) {
            $numBoxes = rand(2, 5);

            for ($i = 0; $i < $numBoxes; $i++) {
                $warehouse = $warehouses->all->random();
                $manager = $users->warehouseManagers->random();

                $isFull = rand(1, 100) <= 80;
                $status = $isFull ? BoxStatus::FULL : BoxStatus::PARTIAL;
                $itemsTotal = $product->items_per_box;
                $itemsRemaining = $isFull ? $itemsTotal : rand(1, $itemsTotal - 1);

                $boxCode = sprintf('BOX-%s-%05d', $today->format('Ymd'), $boxCounter++);
                $receivedAt = $today->copy()->subDays(rand(0, 30));

                $box = Box::create([
                    'product_id' => $product->id,
                    'box_code' => $boxCode,
                    'status' => $status,
                    'items_total' => $itemsTotal,
                    'items_remaining' => $itemsRemaining,
                    'location_type' => LocationType::WAREHOUSE,
                    'location_id' => $warehouse->id,
                    'received_by' => $manager->id,
                    'received_at' => $receivedAt,
                    'batch_number' => 'BATCH-' . $receivedAt->format('Ym') . '-' . rand(100, 999),
                    'expiry_date' => $receivedAt->copy()->addMonths(rand(6, 24)),
                ]);

                $allBoxes->push($box);
            }
        }

        $this->command->info("   ✓ Created {$boxCounter} boxes");

        return (object)['all' => $allBoxes];
    }

    private function seedTransfers($warehouses, $shops, $users, $transporters, $products, $boxes): void
    {
        $this->command->info('🚛 Creating transfers...');

        $transferCounter = 1;
        $today = now();

        $transfersData = [
            ['status' => TransferStatus::PENDING, 'days_ago' => 1],
            ['status' => TransferStatus::PENDING, 'days_ago' => 0],
            ['status' => TransferStatus::APPROVED, 'days_ago' => 2],
            ['status' => TransferStatus::APPROVED, 'days_ago' => 1],
            ['status' => TransferStatus::IN_TRANSIT, 'days_ago' => 1],
            ['status' => TransferStatus::DELIVERED, 'days_ago' => 0],
            ['status' => TransferStatus::RECEIVED, 'days_ago' => 7],
            ['status' => TransferStatus::RECEIVED, 'days_ago' => 5],
            ['status' => TransferStatus::RECEIVED, 'days_ago' => 3],
            ['status' => TransferStatus::RECEIVED, 'days_ago' => 2],
        ];

        foreach ($transfersData as $data) {
            $warehouse = $warehouses->all->random();
            $shop = $shops->all->random();
            $shopManager = $users->shopManagers->random();
            $warehouseManager = $users->warehouseManagers->random();

            $requestedAt = $today->copy()->subDays($data['days_ago']);
            $transferNumber = sprintf('TR-%s-%05d', $requestedAt->format('Y-m'), $transferCounter++);

            $transfer = Transfer::create([
                'transfer_number' => $transferNumber,
                'from_warehouse_id' => $warehouse->id,
                'to_shop_id' => $shop->id,
                'status' => $data['status'],
                'requested_by' => $shopManager->id,
                'requested_at' => $requestedAt,
                'transporter_id' => $data['status']->value !== 'pending' ? $transporters->all->random()->id : null,
            ]);

            if ($data['status']->value !== 'pending') {
                $transfer->update(['reviewed_by' => $warehouseManager->id, 'reviewed_at' => $requestedAt->copy()->addHours(2)]);
            }
            if (in_array($data['status']->value, ['in_transit', 'delivered', 'received'])) {
                $transfer->update(['shipped_at' => $requestedAt->copy()->addHours(6)]);
            }
            if (in_array($data['status']->value, ['delivered', 'received'])) {
                $transfer->update(['delivered_at' => $requestedAt->copy()->addHours(8)]);
            }
            if ($data['status']->value === 'received') {
                $transfer->update(['received_by' => $shopManager->id, 'received_at' => $requestedAt->copy()->addHours(9)]);
            }

            // Add products
            $numProducts = rand(2, 4);
            $selectedProducts = $products->all->random($numProducts);

            foreach ($selectedProducts as $product) {
                $quantityRequested = rand(1, 3) * $product->items_per_box;

                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'quantity_requested' => $quantityRequested,
                    'quantity_shipped' => in_array($data['status']->value, ['in_transit', 'delivered', 'received']) ? $quantityRequested : 0,
                    'quantity_received' => $data['status']->value === 'received' ? $quantityRequested : 0,
                    'discrepancy_quantity' => 0,
                ]);
            }
        }

        $this->command->info('   ✓ Created 10 transfers');
    }

    private function seedSales($shops, $users): void
    {
        $this->command->info('💰 Creating sales...');

        $saleCounter = 1;
        $today = now();

        for ($i = 0; $i < 15; $i++) {
            $shop = $shops->all->random();
            $shopManager = $users->shopManagers->random();
            $saleDate = $today->copy()->subDays(rand(0, 7));
            $saleNumber = sprintf('SALE-%s-%05d', $saleDate->format('Ymd'), $saleCounter++);

            $availableBoxes = Box::where('location_type', 'shop')
                ->where('location_id', $shop->id)
                ->where('items_remaining', '>', 0)
                ->with('product')
                ->get();

            if ($availableBoxes->isEmpty()) continue;

            $sale = Sale::create([
                'sale_number' => $saleNumber,
                'shop_id' => $shop->id,
                'type' => SaleType::INDIVIDUAL_ITEMS,
                'payment_method' => [PaymentMethod::CASH, PaymentMethod::CARD, PaymentMethod::MOBILE_MONEY][rand(0, 2)],
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'sold_by' => $shopManager->id,
                'sale_date' => $saleDate,
            ]);

            $numItems = rand(1, min(5, $availableBoxes->count()));
            $subtotal = 0;

            for ($j = 0; $j < $numItems; $j++) {
                $box = $availableBoxes->random();
                $product = $box->product;
                $quantity = rand(1, min($box->items_remaining, 10));

                $unitPrice = $product->selling_price;
                $lineTotal = $unitPrice * $quantity;
                $subtotal += $lineTotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'box_id' => $box->id,
                    'quantity_sold' => $quantity,
                    'is_full_box' => false,
                    'original_unit_price' => $unitPrice,
                    'actual_unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'price_was_modified' => false,
                ]);

                $newRemaining = $box->items_remaining - $quantity;
                $box->update([
                    'items_remaining' => $newRemaining,
                    'status' => $newRemaining === 0 ? BoxStatus::EMPTY : BoxStatus::PARTIAL,
                ]);
            }

            $tax = round($subtotal * 0.18);
            $sale->update(['subtotal' => $subtotal, 'tax' => $tax, 'total' => $subtotal + $tax]);
        }

        $this->command->info('   ✓ Created 15 sales');
    }
}