<?php

namespace App\Livewire\Owner;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DangerZone extends Component
{
    public string $confirmText = '';
    public bool $showConfirm = false;
    public array $wipeLog = [];
    public bool $done = false;

    public function requestWipe(): void
    {
        $this->resetErrorBag();
        if ($this->confirmText !== 'DELETE EVERYTHING') {
            $this->addError('confirmText', 'Type exactly: DELETE EVERYTHING');
            return;
        }
        $this->showConfirm = true;
    }

    public function cancelWipe(): void
    {
        $this->showConfirm = false;
        $this->confirmText = '';
    }

    public function executeWipe(): void
    {
        abort_unless(auth()->user()->isOwner(), 403);

        $ownerId = auth()->id();

        $tables = [
            'report_view_logs',
            'report_annotations',
            'report_run_history',
            'saved_reports',
            'sale_payments',
            'return_items',
            'sale_items',
            'held_sales',
            'returns',
            'transfer_boxes',
            'transfer_items',
            'transfers',
            'credit_repayments',
            'credit_writeoffs',
            'damaged_goods',
            'box_movements',
            'boxes',
            'product_barcodes',
            'inventory_snapshots',
            'bank_deposits',
            'owner_withdrawals',
            'expense_requests',
            'expenses',
            'daily_sessions',
            'activity_logs',
            'alerts',
            'scanner_sessions',
            'customer_credit_accounts',
            'customers',
            'transporters',
        ];

        DB::transaction(function () use ($ownerId, $tables) {
            foreach ($tables as $table) {
                DB::table($table)->delete();
            }

            DB::table('users')->where('id', '!=', $ownerId)->delete();

            DB::table('products')->delete();
            DB::table('categories')->delete();

            DB::table('shops')->delete();
            DB::table('warehouses')->delete();
        });

        $this->done = true;
        $this->showConfirm = false;
        $this->confirmText = '';
    }

    public function render()
    {
        return view('livewire.owner.danger-zone');
    }
}
