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
    public array   $selected            = [];
    public string  $confirmText         = '';
    public bool    $showConfirm         = false;
    public bool    $wipeDone            = false;
    public ?string $wipeError          = null;

    // ── Group definitions (label + description for UI) ─
    public function groups(): array
    {
        return [
            'reports'      => ['label' => 'Reports & History',      'desc' => 'Saved reports, run history, annotations, view logs'],
            'logs'         => ['label' => 'Alerts & Activity Logs', 'desc' => 'System alerts and full activity audit trail'],
            'sessions'     => ['label' => 'Daily Sessions',         'desc' => 'Sessions, expenses, withdrawals, bank deposits'],
            'sales'        => ['label' => 'Sales & Payments',       'desc' => 'All sale transactions and split-payment records'],
            'returns'      => ['label' => 'Returns',                'desc' => 'Customer return records and return items'],
            'transfers'    => ['label' => 'Transfers',              'desc' => 'Transfer orders and assigned box records'],
            'credit'       => ['label' => 'Credit & Repayments',   'desc' => 'Repayment history and credit write-offs'],
            'boxes'        => ['label' => 'Boxes & Stock',          'desc' => 'All boxes, movements, damaged goods, snapshots'],
            'customers'    => ['label' => 'Customers',              'desc' => 'Customer profiles and credit accounts'],
            'users'        => ['label' => 'Users',                  'desc' => 'All staff accounts — your account is kept'],
            'transporters' => ['label' => 'Transporters',           'desc' => 'Delivery drivers and transport records'],
            'products'     => ['label' => 'Products',               'desc' => 'Full product catalogue and barcodes'],
            'categories'   => ['label' => 'Product Categories',     'desc' => 'Product category hierarchy'],
            'locations'    => ['label' => 'Warehouses & Shops',     'desc' => 'All warehouse and shop location records'],
        ];
    }

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
