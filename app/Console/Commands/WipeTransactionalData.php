<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WipeTransactionalData extends Command
{
    protected $signature = 'db:wipe-data {--force : Skip confirmation}';
    protected $description = 'Wipe all transactional data (keeps users and locations)';

    public function handle()
    {
        $this->warn('⚠️  WARNING: This will DELETE ALL transactional data!');
        $this->newLine();

        $this->info('📋 What will be DELETED:');
        $this->line('  - All customers and credit records');
        $this->line('  - All products and categories');
        $this->line('  - All boxes and inventory');
        $this->line('  - All sales and sale items');
        $this->line('  - All transfers');
        $this->line('  - All returns and damaged goods');
        $this->line('  - All transporters');
        $this->line('  - All activity logs and alerts');
        $this->newLine();

        $this->info('✅ What will be KEPT:');
        $this->line('  - Users (login accounts)');
        $this->line('  - Warehouses and Shops (locations)');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Are you ABSOLUTELY SURE you want to proceed?', false)) {
                $this->error('Operation cancelled.');
                return 1;
            }

            if (!$this->confirm('This action CANNOT be undone. Final confirmation?', false)) {
                $this->error('Operation cancelled.');
                return 1;
            }
        }

        $this->newLine();
        $this->info('🗑️  Starting data deletion...');
        $this->newLine();

        DB::statement('SET CONSTRAINTS ALL DEFERRED');

        // Track counts before deletion
        $counts = [
            'customers' => DB::table('customers')->count(),
            'sale_items' => DB::table('sale_items')->count(),
            'sales' => DB::table('sales')->count(),
            'boxes' => DB::table('boxes')->count(),
            'products' => DB::table('products')->count(),
            'transfers' => DB::table('transfers')->count(),
        ];

        $this->info('1️⃣  Deleting sale and return details...');
        DB::table('sale_items')->delete();
        DB::table('sale_payments')->delete();
        DB::table('return_items')->delete();
        DB::table('transfer_items')->delete();
        DB::table('transfer_boxes')->delete();

        $this->info('2️⃣  Deleting transactional headers...');
        DB::table('returns')->delete();
        DB::table('sales')->delete();
        DB::table('transfers')->delete();
        DB::table('damaged_goods')->delete();

        $this->info('3️⃣  Deleting inventory records...');
        DB::table('box_movements')->delete();
        DB::table('boxes')->delete();
        DB::table('inventory_snapshots')->delete();

        $this->info('4️⃣  Deleting product data...');
        DB::table('product_barcodes')->delete();
        DB::table('products')->delete();
        DB::table('categories')->delete();

        $this->info('5️⃣  Deleting customers...');
        DB::table('customers')->delete();

        $this->info('6️⃣  Deleting transporters...');
        DB::table('transporters')->delete();

        $this->info('7️⃣  Deleting logs and alerts...');
        DB::table('alerts')->delete();
        DB::table('activity_logs')->delete();

        $this->info('8️⃣  Resetting ID sequences...');
        $tables = [
            'sale_items', 'sale_payments', 'return_items', 'transfer_items', 'transfer_boxes',
            'sales', 'returns', 'transfers', 'damaged_goods',
            'box_movements', 'boxes', 'inventory_snapshots',
            'product_barcodes', 'products', 'categories',
            'customers', 'transporters', 'alerts', 'activity_logs'
        ];

        foreach ($tables as $table) {
            try {
                DB::statement("ALTER SEQUENCE {$table}_id_seq RESTART WITH 1");
            } catch (\Exception $e) {
                // Some sequences might not exist, that's ok
            }
        }

        $this->newLine();
        $this->info('✅ Data deletion completed!');
        $this->newLine();

        $this->table(
            ['Data Type', 'Records Deleted'],
            [
                ['Customers', number_format($counts['customers'])],
                ['Sales', number_format($counts['sales'])],
                ['Sale Items', number_format($counts['sale_items'])],
                ['Boxes', number_format($counts['boxes'])],
                ['Products', number_format($counts['products'])],
                ['Transfers', number_format($counts['transfers'])],
            ]
        );

        $this->newLine();
        $this->info('🎯 Next steps:');
        $this->line('  1. Upload your data file (products, inventory, etc.)');
        $this->line('  2. Verify all data imported correctly');
        $this->line('  3. Test a sample sale to confirm everything works');
        $this->newLine();

        return 0;
    }
}
