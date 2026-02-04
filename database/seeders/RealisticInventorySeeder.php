<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Product;
use App\Models\Box;
use App\Models\Transporter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RealisticInventorySeeder extends Seeder
{
    /**
     * REALISTIC SEED DATA FOR SHOE WHOLESALE BUSINESS
     * 
     * Key Points:
     * - Products include SIZE as part of definition
     * - Same product barcode on all boxes of that product
     * - Box code is unique per physical box (internal tracking)
     * - Mix of footwear and other retail products
     */
    public function run(): void
    {
        $this->command->info('🧹 Clearing existing data...');
        $this->clearData();
        
        $this->command->info('👥 Creating users...');
        $users = $this->createUsers();
        
        $this->command->info('🏢 Creating locations...');
        $locations = $this->createLocations();
        
        $this->command->info('🚚 Creating transporters...');
        $transporters = $this->createTransporters();
        
        $this->command->info('📦 Creating categories...');
        $categories = $this->createCategories();
        
        $this->command->info('👟 Creating products (with sizes)...');
        $products = $this->createProducts($categories);
        
        $this->command->info('📦 Creating boxes in warehouse...');
        $this->createWarehouseBoxes($products, $locations['warehouses'], $users['warehouseManagers']);
        
        $this->command->info('✅ Realistic seed data created successfully!');
        $this->displaySummary($users, $locations, $products);
    }

    private function clearData(): void
    {
        \DB::statement('SET CONSTRAINTS ALL DEFERRED');
        
        \App\Models\Box::truncate();
        \App\Models\Product::truncate();
        \App\Models\Category::truncate();
        \App\Models\Transporter::truncate();
        \App\Models\Shop::truncate();
        \App\Models\Warehouse::truncate();
        \App\Models\User::where('email', '!=', 'admin@example.com')->delete();
        
        \DB::statement('SET CONSTRAINTS ALL IMMEDIATE');
    }

    private function createUsers(): array
    {
        // Owner
        $owner = User::create([
            'name' => 'John Owner',
            'email' => 'owner@smartinventory.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'location_type' => null,
            'location_id' => null,
            'is_active' => true,
        ]);

        // Warehouse Managers
        $warehouseManagers = collect([
            ['name' => 'Sarah Warehouse', 'email' => 'warehouse@smartinventory.com'],
            ['name' => 'Michael Stock', 'email' => 'warehouse2@smartinventory.com'],
        ])->map(fn($data) => User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make('password'),
            'role' => 'warehouse_manager',
            'location_type' => null, // Set after creating warehouses
            'location_id' => null,
            'is_active' => true,
        ]));

        // Shop Managers
        $shopManagers = collect([
            ['name' => 'Alice Shop', 'email' => 'shop1@smartinventory.com'],
            ['name' => 'Bob Retail', 'email' => 'shop2@smartinventory.com'],
            ['name' => 'Carol Store', 'email' => 'shop3@smartinventory.com'],
        ])->map(fn($data) => User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make('password'),
            'role' => 'shop_manager',
            'location_type' => null, // Set after creating shops
            'location_id' => null,
            'is_active' => true,
        ]));

        return [
            'owner' => $owner,
            'warehouseManagers' => $warehouseManagers,
            'shopManagers' => $shopManagers,
        ];
    }

    private function createLocations(): array
    {
        // Warehouses
        $warehouses = collect([
            ['name' => 'Main Warehouse - Kigali', 'code' => 'WH-KGL-001', 'address' => 'KG 15 Ave, Kigali'],
            ['name' => 'Secondary Warehouse - Kigali', 'code' => 'WH-KGL-002', 'address' => 'KG 23 Ave, Kigali'],
        ])->map(fn($data) => Warehouse::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'address' => $data['address'],
            'phone' => '+250788' . rand(100000, 999999),
            'is_active' => true,
        ]));

        // Shops
        $shops = collect([
            ['name' => 'City Center Shop', 'code' => 'SH-CC-001', 'address' => 'KN 4 Ave, Kigali City'],
            ['name' => 'Kimironko Shop', 'code' => 'SH-KM-001', 'address' => 'KG 7 Ave, Kimironko'],
            ['name' => 'Remera Shop', 'code' => 'SH-RM-001', 'address' => 'KG 11 Ave, Remera'],
        ])->map(fn($data, $index) => Shop::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'address' => $data['address'],
            'phone' => '+250788' . rand(100000, 999999),
            'default_warehouse_id' => $warehouses->first()->id,
            'is_active' => true,
        ]));

        // Update users with locations
        User::where('email', 'warehouse@smartinventory.com')->update([
            'location_type' => 'warehouse',
            'location_id' => $warehouses[0]->id,
        ]);
        
        User::where('email', 'warehouse2@smartinventory.com')->update([
            'location_type' => 'warehouse',
            'location_id' => $warehouses[1]->id,
        ]);

        User::where('email', 'shop1@smartinventory.com')->update([
            'location_type' => 'shop',
            'location_id' => $shops[0]->id,
        ]);
        
        User::where('email', 'shop2@smartinventory.com')->update([
            'location_type' => 'shop',
            'location_id' => $shops[1]->id,
        ]);
        
        User::where('email', 'shop3@smartinventory.com')->update([
            'location_type' => 'shop',
            'location_id' => $shops[2]->id,
        ]);

        return [
            'warehouses' => $warehouses,
            'shops' => $shops,
        ];
    }

    private function createTransporters(): array
    {
        return collect([
            ['name' => 'Fast Delivery Ltd', 'phone' => '+250788111111'],
            ['name' => 'Quick Transport', 'phone' => '+250788222222'],
            ['name' => 'Reliable Movers', 'phone' => '+250788333333'],
        ])->map(fn($data) => Transporter::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'vehicle_number' => 'RAC ' . rand(100, 999) . ' ' . chr(rand(65, 90)),
            'is_active' => true,
        ]))->all();
    }

    private function createCategories(): array
    {
        // Main categories
        $footwear = Category::create([
            'name' => 'Footwear',
            'code' => 'FW',
            'description' => 'All types of shoes and sandals',
            'is_active' => true,
        ]);

        $accessories = Category::create([
            'name' => 'Accessories',
            'code' => 'ACC',
            'description' => 'Bags, belts, and other accessories',
            'is_active' => true,
        ]);

        $apparel = Category::create([
            'name' => 'Apparel',
            'code' => 'APP',
            'description' => 'Clothing items',
            'is_active' => true,
        ]);

        // Footwear subcategories
        $sneakers = Category::create([
            'name' => 'Sneakers',
            'code' => 'FW-SNK',
            'description' => 'Athletic and casual sneakers',
            'parent_id' => $footwear->id,
            'is_active' => true,
        ]);

        $formal = Category::create([
            'name' => 'Formal Shoes',
            'code' => 'FW-FOR',
            'description' => 'Dress shoes and formal footwear',
            'parent_id' => $footwear->id,
            'is_active' => true,
        ]);

        $sandals = Category::create([
            'name' => 'Sandals',
            'code' => 'FW-SAN',
            'description' => 'Sandals and flip-flops',
            'parent_id' => $footwear->id,
            'is_active' => true,
        ]);

        return [
            'footwear' => $footwear,
            'sneakers' => $sneakers,
            'formal' => $formal,
            'sandals' => $sandals,
            'accessories' => $accessories,
            'apparel' => $apparel,
        ];
    }

    private function createProducts(array $categories): array
    {
        $products = [];

        // Nike Air Max - Multiple Sizes (Each size is a separate product!)
        // All boxes of same size have SAME barcode
        $sizes = [39, 40, 41, 42, 43, 44, 45];
        $baseBarcode = '847567890';
        
        foreach ($sizes as $size) {
            $products[] = Product::create([
                'category_id' => $categories['sneakers']->id,
                'name' => "Nike Air Max Size {$size}",
                'sku' => "NK-AIR-{$size}",
                'barcode' => $baseBarcode . $size, // e.g., 84756789039, 84756789040, etc.
                'description' => "Nike Air Max athletic sneakers, size {$size}",
                'items_per_box' => 12, // 12 pairs per box
                'purchase_price' => 4500000, // 45,000 RWF in cents
                'selling_price' => 6500000, // 65,000 RWF per pair
                'box_selling_price' => 75000000, // 750,000 RWF per box (12 pairs)
                'low_stock_threshold' => 5,
                'reorder_point' => 10,
                'unit_of_measure' => 'pair',
                'supplier' => 'Nike Distributor Rwanda',
                'is_active' => true,
            ]);
        }

        // Adidas Superstar - Multiple Sizes
        $baseBarcode = '847567891';
        foreach ($sizes as $size) {
            $products[] = Product::create([
                'category_id' => $categories['sneakers']->id,
                'name' => "Adidas Superstar Size {$size}",
                'sku' => "AD-SUP-{$size}",
                'barcode' => $baseBarcode . $size,
                'description' => "Adidas Superstar classic sneakers, size {$size}",
                'items_per_box' => 12,
                'purchase_price' => 4000000, // 40,000 RWF
                'selling_price' => 5800000, // 58,000 RWF per pair
                'box_selling_price' => 68000000, // 680,000 RWF per box
                'low_stock_threshold' => 5,
                'reorder_point' => 10,
                'unit_of_measure' => 'pair',
                'supplier' => 'Adidas Distributor Rwanda',
                'is_active' => true,
            ]);
        }

        // Puma Suede - Multiple Sizes
        $baseBarcode = '847567892';
        foreach ($sizes as $size) {
            $products[] = Product::create([
                'category_id' => $categories['sneakers']->id,
                'name' => "Puma Suede Size {$size}",
                'sku' => "PM-SUD-{$size}",
                'barcode' => $baseBarcode . $size,
                'description' => "Puma Suede casual sneakers, size {$size}",
                'items_per_box' => 10,
                'purchase_price' => 3500000, // 35,000 RWF
                'selling_price' => 5200000, // 52,000 RWF per pair
                'box_selling_price' => 50000000, // 500,000 RWF per box
                'low_stock_threshold' => 5,
                'reorder_point' => 10,
                'unit_of_measure' => 'pair',
                'supplier' => 'Puma Distributor Rwanda',
                'is_active' => true,
            ]);
        }

        // Formal Leather Shoes - Men's Sizes
        $formalSizes = [40, 41, 42, 43, 44, 45];
        $baseBarcode = '847567893';
        
        foreach ($formalSizes as $size) {
            $products[] = Product::create([
                'category_id' => $categories['formal']->id,
                'name' => "Men's Leather Oxford Size {$size}",
                'sku' => "MEN-OXF-{$size}",
                'barcode' => $baseBarcode . $size,
                'description' => "Classic black leather Oxford dress shoes, size {$size}",
                'items_per_box' => 8,
                'purchase_price' => 6000000, // 60,000 RWF
                'selling_price' => 9500000, // 95,000 RWF per pair
                'box_selling_price' => 75000000, // 750,000 RWF per box
                'low_stock_threshold' => 3,
                'reorder_point' => 8,
                'unit_of_measure' => 'pair',
                'supplier' => 'Leather Goods Import Ltd',
                'is_active' => true,
            ]);
        }

        // Sandals - Unisex (fewer size variations)
        $sandalSizes = [38, 39, 40, 41, 42, 43];
        $baseBarcode = '847567894';
        
        foreach ($sandalSizes as $size) {
            $products[] = Product::create([
                'category_id' => $categories['sandals']->id,
                'name' => "Casual Sandals Size {$size}",
                'sku' => "SAN-CAS-{$size}",
                'barcode' => $baseBarcode . $size,
                'description' => "Comfortable casual sandals, size {$size}",
                'items_per_box' => 20, // Sandals packed more per box
                'purchase_price' => 1500000, // 15,000 RWF
                'selling_price' => 2500000, // 25,000 RWF per pair
                'box_selling_price' => 48000000, // 480,000 RWF per box (20 pairs)
                'low_stock_threshold' => 10,
                'reorder_point' => 20,
                'unit_of_measure' => 'pair',
                'supplier' => 'Sandal Imports Rwanda',
                'is_active' => true,
            ]);
        }

        // Non-footwear products
        $products[] = Product::create([
            'category_id' => $categories['accessories']->id,
            'name' => 'Leather Belt - Black',
            'sku' => 'BELT-BLK',
            'barcode' => '847567895001',
            'description' => 'Genuine leather belt, black, adjustable',
            'items_per_box' => 24,
            'purchase_price' => 1200000, // 12,000 RWF
            'selling_price' => 2000000, // 20,000 RWF
            'box_selling_price' => 45000000, // 450,000 RWF per box
            'low_stock_threshold' => 8,
            'reorder_point' => 15,
            'unit_of_measure' => 'piece',
            'supplier' => 'Leather Goods Import Ltd',
            'is_active' => true,
        ]);

        $products[] = Product::create([
            'category_id' => $categories['accessories']->id,
            'name' => 'Canvas Backpack',
            'sku' => 'BAG-BKPK',
            'barcode' => '847567895002',
            'description' => 'Durable canvas backpack, multiple compartments',
            'items_per_box' => 12,
            'purchase_price' => 3500000, // 35,000 RWF
            'selling_price' => 5500000, // 55,000 RWF
            'box_selling_price' => 64000000, // 640,000 RWF per box
            'low_stock_threshold' => 5,
            'reorder_point' => 10,
            'unit_of_measure' => 'piece',
            'supplier' => 'Bags & More Ltd',
            'is_active' => true,
        ]);

        return $products;
    }

    private function createWarehouseBoxes(array $products, $warehouses, $warehouseManagers): void
    {
        $warehouse = $warehouses->first();
        $manager = $warehouseManagers->first();
        $boxCounter = 1;

        foreach ($products as $product) {
            // For popular products (sneakers), create more boxes
            $isPopular = str_contains($product->name, 'Nike') || str_contains($product->name, 'Adidas');
            $boxCount = $isPopular ? rand(15, 25) : rand(5, 12);

            for ($i = 0; $i < $boxCount; $i++) {
                $receivedDate = now()->subDays(rand(1, 90));
                $batchMonth = $receivedDate->format('Y-m');
                
                // Most boxes are FULL (sealed)
                // Only 5% are partial (opened due to damage)
                $isPartial = rand(1, 100) <= 5;
                
                $itemsRemaining = $isPartial 
                    ? rand(floor($product->items_per_box * 0.6), $product->items_per_box - 1)
                    : $product->items_per_box;

                Box::create([
                    'product_id' => $product->id,
                    // Box code is UNIQUE per physical box (for internal tracking)
                    // NOT the product barcode!
                    'box_code' => sprintf('BOX-%s-%04d', $batchMonth, $boxCounter++),
                    'items_total' => $product->items_per_box,
                    'items_remaining' => $itemsRemaining,
                    'location_type' => 'warehouse',
                    'location_id' => $warehouse->id,
                    'status' => $isPartial ? 'partial' : 'full',
                    'received_by' => $manager->id,
                    'received_at' => $receivedDate,
                    'batch_number' => 'BATCH-' . $batchMonth,
                    'expiry_date' => null, // Shoes don't expire
                ]);
            }
        }
    }

    private function displaySummary($users, $locations, $products): void
    {
        $this->command->info("\n" . str_repeat('=', 60));
        $this->command->info('📊 SEED DATA SUMMARY');
        $this->command->info(str_repeat('=', 60));
        
        $this->command->info("\n👥 USERS:");
        $this->command->info("   Owner: {$users['owner']->email} / password");
        $this->command->info("   Warehouse Managers: " . $users['warehouseManagers']->count());
        $this->command->info("   Shop Managers: " . $users['shopManagers']->count());
        
        $this->command->info("\n🏢 LOCATIONS:");
        $this->command->info("   Warehouses: " . $locations['warehouses']->count());
        $this->command->info("   Shops: " . $locations['shops']->count());
        
        $this->command->info("\n📦 PRODUCTS:");
        $this->command->info("   Total Products: " . count($products));
        $this->command->info("   Nike Air Max (sizes): 7 products");
        $this->command->info("   Adidas Superstar (sizes): 7 products");
        $this->command->info("   Puma Suede (sizes): 7 products");
        $this->command->info("   + Formal shoes, sandals, accessories");
        
        $this->command->info("\n📦 BOXES:");
        $totalBoxes = Box::count();
        $sealedBoxes = Box::where('status', 'full')->count();
        $openedBoxes = Box::where('status', 'partial')->count();
        $this->command->info("   Total Boxes: {$totalBoxes}");
        $this->command->info("   Sealed (Full): {$sealedBoxes} (~95%)");
        $this->command->info("   Opened (Partial): {$openedBoxes} (~5%)");
        
        $this->command->info("\n🔑 KEY CONCEPTS:");
        $this->command->info("   ✓ Product barcode: SAME for all boxes of that product");
        $this->command->info("   ✓ Box code: UNIQUE per physical box (internal tracking)");
        $this->command->info("   ✓ Each size is a separate product with own barcode");
        $this->command->info("   ✓ Nike Air Max Size 42 ≠ Nike Air Max Size 43");
        
        $this->command->info("\n" . str_repeat('=', 60));
        
        $this->command->info("\n🔐 LOGIN CREDENTIALS:");
        $this->command->info("   All passwords: password");
        $this->command->info("   Owner: owner@smartinventory.com");
        $this->command->info("   Warehouse: warehouse@smartinventory.com");
        $this->command->info("   Shop 1: shop1@smartinventory.com");
        $this->command->info("   Shop 2: shop2@smartinventory.com");
        $this->command->info("   Shop 3: shop3@smartinventory.com");
        
        $this->command->info("\n✅ Ready to use!\n");
    }
}
