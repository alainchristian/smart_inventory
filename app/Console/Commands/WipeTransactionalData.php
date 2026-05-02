<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WipeTransactionalData extends Command
{
    protected $signature = 'db:wipe-data {--force : Skip confirmation}';
    protected $description = 'Wipe all transactional data — keeps users, locations, categories, transporters, customers (credit zeroed), settings, expense_categories';

    public function handle(): int
    {
        $this->warn('WARNING: This will DELETE transactional data. This cannot be undone.');
        $this->newLine();

        $this->info('What will be KEPT:');
        $this->line('  - users, warehouses, shops');
        $this->line('  - categories, transporters');
        $this->line('  - customers (outstanding_balance / credit totals reset to 0)');
        $this->line('  - settings, expense_categories');
        $this->newLine();

        $this->info('What will be DELETED:');
        $this->line('  - products, product_barcodes');
        $this->line('  - boxes, box_movements, inventory_snapshots');
        $this->line('  - transfers, transfer_items, transfer_boxes');
        $this->line('  - sales, sale_items, sale_payments, held_sales');
        $this->line('  - returns, return_items');
        $this->line('  - damaged_goods');
        $this->line('  - daily_sessions, expenses, expense_requests, owner_withdrawals, bank_deposits');
        $this->line('  - credit_repayments, credit_writeoffs, customer_credit_accounts');
        $this->line('  - activity_logs, alerts, scanner_sessions');
        $this->line('  - saved_reports, report_run_histories, report_annotations, report_view_logs');
        $this->newLine();

        if (! $this->option('force')) {
            if (! $this->confirm('Are you ABSOLUTELY SURE you want to proceed?', false)) {
                $this->error('Cancelled.');
                return 1;
            }
            if (! $this->confirm('Final confirmation — this cannot be undone?', false)) {
                $this->error('Cancelled.');
                return 1;
            }
        }

        $this->newLine();
        $this->line('Starting wipe...');

        // Snapshot counts for summary
        $counts = [
            'products'    => DB::table('products')->count(),
            'boxes'       => DB::table('boxes')->count(),
            'transfers'   => DB::table('transfers')->count(),
            'sales'       => DB::table('sales')->count(),
            'sale_items'  => DB::table('sale_items')->count(),
            'customers'   => DB::table('customers')->count(),
        ];

        DB::statement('SET CONSTRAINTS ALL DEFERRED');

        $wipe = function (string $table): void {
            try {
                DB::table($table)->delete();
            } catch (\Exception) {
                // table doesn't exist — skip
            }
        };

        // ── 1. Report system ──────────────────────────────────────────────────
        $this->line('  [1/9] Report history & annotations...');
        $wipe('report_annotations');
        $wipe('report_view_logs');
        $wipe('report_run_histories');
        $wipe('saved_reports');

        // ── 2. Sales ─────────────────────────────────────────────────────────
        $this->line('  [2/9] Sales...');
        $wipe('sale_items');
        $wipe('sale_payments');
        $wipe('held_sales');

        // ── 3. Returns ───────────────────────────────────────────────────────
        $this->line('  [3/9] Returns...');
        $wipe('return_items');
        $wipe('returns');
        $wipe('sales');

        // ── 4. Transfers ─────────────────────────────────────────────────────
        $this->line('  [4/9] Transfers...');
        $wipe('transfer_items');
        $wipe('transfer_boxes');
        $wipe('transfers');

        // ── 5. Inventory ─────────────────────────────────────────────────────
        $this->line('  [5/9] Inventory & boxes...');
        $wipe('damaged_goods');
        $wipe('box_movements');
        $wipe('boxes');
        $wipe('inventory_snapshots');

        // ── 6. Products ──────────────────────────────────────────────────────
        $this->line('  [6/9] Products...');
        $wipe('product_barcodes');
        $wipe('products');

        // ── 7. Finance / day-close ───────────────────────────────────────────
        $this->line('  [7/9] Daily sessions & finance...');
        $wipe('bank_deposits');
        $wipe('owner_withdrawals');
        $wipe('expense_requests');
        $wipe('expenses');
        $wipe('daily_sessions');

        // ── 8. Credit ────────────────────────────────────────────────────────
        $this->line('  [8/9] Credit records...');
        $wipe('credit_writeoffs');
        $wipe('credit_repayments');
        $wipe('customer_credit_accounts');

        // Zero out credit balances on customers (keep the rows)
        DB::table('customers')->update([
            'outstanding_balance' => 0,
            'total_credit_given'  => 0,
            'total_repaid'        => 0,
            'last_repayment_at'   => null,
        ]);

        // ── 9. Logs / alerts / scanner ───────────────────────────────────────
        $this->line('  [9/9] Logs, alerts, scanner sessions...');
        $wipe('alerts');
        $wipe('activity_logs');
        $wipe('scanner_sessions');

        // ── Reset sequences ──────────────────────────────────────────────────
        $sequences = [
            'report_annotations', 'report_view_logs', 'report_run_histories', 'saved_reports',
            'sale_items', 'sale_payments', 'held_sales',
            'return_items', 'returns', 'sales',
            'transfer_items', 'transfer_boxes', 'transfers',
            'damaged_goods', 'box_movements', 'boxes', 'inventory_snapshots',
            'product_barcodes', 'products',
            'bank_deposits', 'owner_withdrawals', 'expense_requests', 'expenses', 'daily_sessions',
            'credit_writeoffs', 'credit_repayments',
            'alerts', 'activity_logs', 'scanner_sessions',
        ];

        foreach ($sequences as $table) {
            try {
                DB::statement("ALTER SEQUENCE {$table}_id_seq RESTART WITH 1");
            } catch (\Exception) {
                // sequence may not exist — safe to ignore
            }
        }

        $this->newLine();
        $this->info('Done.');
        $this->newLine();

        $this->table(
            ['Table', 'Rows deleted'],
            [
                ['products',   number_format($counts['products'])],
                ['boxes',      number_format($counts['boxes'])],
                ['transfers',  number_format($counts['transfers'])],
                ['sales',      number_format($counts['sales'])],
                ['sale_items', number_format($counts['sale_items'])],
                ['customers (credit zeroed)', number_format($counts['customers']) . ' rows kept'],
            ]
        );

        $this->newLine();
        $this->line('Next: import your new products, then run a test sale to verify.');
        $this->newLine();

        return 0;
    }
}
