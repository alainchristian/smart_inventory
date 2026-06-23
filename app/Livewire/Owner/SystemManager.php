<?php

namespace App\Livewire\Owner;

use App\Models\Category;
use App\Models\ExpenseCategory;
use App\Models\Transporter;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SystemManager extends Component
{
    public string $activeTab = 'setup';

    // ── Data wipe ─────────────────────────────────────
    public function toggleAll(): void
    {
        if (count($this->selected) === count($this->groups())) {
            $this->selected = [];
        } else {
            $this->selected = array_keys($this->groups());
        }
    }

    public function requestWipe(): void
    {
        $this->resetErrorBag();
        $this->wipeError = null;

        if (empty($this->selected)) {
            $this->addError('wipe', 'Select at least one data group to wipe.');
            return;
        }
        if ($this->confirmText !== 'DELETE') {
            $this->addError('wipe', 'Type DELETE to confirm.');
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
        $sel     = array_flip($this->selected);

        $map = [
            'reports'      => ['report_view_logs', 'report_annotations', 'report_run_history', 'saved_reports'],
            'logs'         => ['activity_logs', 'alerts', 'scanner_sessions'],
            'sessions'     => ['bank_deposits', 'owner_withdrawals', 'expense_requests', 'expenses', 'daily_sessions'],
            'sales'        => ['sale_payments', 'sale_items', 'held_sales', 'sales'],
            'returns'      => ['return_items', 'returns'],
            'transfers'    => ['transfer_boxes', 'transfer_items', 'transfers'],
            'credit'       => ['credit_repayments', 'credit_writeoffs'],
            'boxes'        => ['box_movements', 'boxes', 'damaged_goods', 'inventory_snapshots', 'product_barcodes'],
            'customers'    => ['customer_credit_accounts', 'customers'],
            'transporters' => ['transporters'],
            'products'     => ['products'],
            'categories'   => ['categories'],
            'locations'    => ['shops', 'warehouses'],
        ];

        try {
            DB::transaction(function () use ($sel, $map, $ownerId) {
                foreach ($map as $group => $tables) {
                    if (! isset($sel[$group])) continue;
                    foreach ($tables as $table) {
                        DB::table($table)->delete();
                    }
                }
                if (isset($sel['users'])) {
                    DB::table('users')->where('id', '!=', $ownerId)->delete();
                }
            });

            $this->wipeDone    = true;
            $this->showConfirm = false;
            $this->confirmText = '';
            $this->selected    = [];
        } catch (\Throwable $e) {
            $this->wipeError   = 'Deletion failed — a dependency may not have been selected. Try including related groups (e.g. Boxes before Products). Error: ' . $e->getMessage();
            $this->showConfirm = false;
        }
    }

    public function render()
    {
        return view('livewire.owner.system-manager', [
            'totalCategories'    => Category::count(),
            'totalExpCategories' => ExpenseCategory::count(),
            'totalTransporters'  => Transporter::count(),
        ]);
    }
}
