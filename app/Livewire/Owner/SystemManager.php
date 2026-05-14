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

    // ── Product categories ────────────────────────────
    public bool   $showCategoryForm    = false;
    public string $catName             = '';
    public string $catCode             = '';
    public string $catDescription      = '';
    public ?int   $catConfirmDelete    = null;

    // ── Expense categories ────────────────────────────
    public bool   $showExpCatForm      = false;
    public string $expCatName          = '';
    public string $expCatDescription   = '';
    public string $expCatAppliesTo     = 'shop';
    public ?int   $expCatConfirmDelete = null;

    // ── Transporters ─────────────────────────────────
    public bool   $showTransporterForm = false;
    public string $trName              = '';
    public string $trCompany           = '';
    public string $trPhone             = '';
    public string $trVehicle           = '';
    public ?int   $trConfirmDelete     = null;

    // ── Data wipe ─────────────────────────────────────
    public array  $selected            = [];
    public string $confirmText         = '';
    public bool   $showConfirm         = false;
    public bool   $wipeDone            = false;
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

    // ── Product categories ─────────────────────────────
    public function saveProdCategory(): void
    {
        $this->validate([
            'catName' => 'required|string|max:120',
            'catCode' => 'nullable|string|max:20',
        ], [], ['catName' => 'name', 'catCode' => 'code']);

        Category::create([
            'name'        => trim($this->catName),
            'code'        => trim($this->catCode) ?: null,
            'description' => trim($this->catDescription) ?: null,
            'is_active'   => true,
        ]);

        $this->reset(['catName', 'catCode', 'catDescription', 'showCategoryForm']);
        $this->resetErrorBag();
    }

    public function toggleProdCategory(int $id): void
    {
        $cat = Category::findOrFail($id);
        $cat->update(['is_active' => ! $cat->is_active]);
    }

    public function confirmDeleteProdCategory(int $id): void
    {
        $this->catConfirmDelete = $id;
    }

    public function deleteProdCategory(): void
    {
        if (! $this->catConfirmDelete) return;
        $cat = Category::withTrashed()->findOrFail($this->catConfirmDelete);
        if ($cat->products()->count() > 0) {
            $this->addError('catDelete', 'Cannot delete — category has products assigned to it.');
            $this->catConfirmDelete = null;
            return;
        }
        $cat->forceDelete();
        $this->catConfirmDelete = null;
    }

    // ── Expense categories ─────────────────────────────
    public function saveExpenseCategory(): void
    {
        $this->validate([
            'expCatName'       => 'required|string|max:120',
            'expCatAppliesTo'  => 'required|in:shop,warehouse,both',
        ], [], ['expCatName' => 'name']);

        ExpenseCategory::create([
            'name'        => trim($this->expCatName),
            'description' => trim($this->expCatDescription) ?: null,
            'applies_to'  => $this->expCatAppliesTo,
            'is_active'   => true,
            'sort_order'  => ExpenseCategory::max('sort_order') + 10,
        ]);

        $this->reset(['expCatName', 'expCatDescription', 'expCatAppliesTo', 'showExpCatForm']);
        $this->expCatAppliesTo = 'shop';
        $this->resetErrorBag();
    }

    public function toggleExpenseCategory(int $id): void
    {
        $cat = ExpenseCategory::findOrFail($id);
        if ($cat->name === 'Cash Shortage') return;
        $cat->update(['is_active' => ! $cat->is_active]);
    }

    public function confirmDeleteExpCat(int $id): void
    {
        $this->expCatConfirmDelete = $id;
    }

    public function deleteExpenseCategory(): void
    {
        if (! $this->expCatConfirmDelete) return;
        $cat = ExpenseCategory::findOrFail($this->expCatConfirmDelete);
        if ($cat->name === 'Cash Shortage') return;
        if ($cat->expenses()->count() > 0) {
            $this->addError('expCatDelete', 'Cannot delete — category has recorded expenses.');
            $this->expCatConfirmDelete = null;
            return;
        }
        $cat->delete();
        $this->expCatConfirmDelete = null;
    }

    // ── Transporters ───────────────────────────────────
    public function saveTransporter(): void
    {
        $this->validate([
            'trName'  => 'required|string|max:120',
            'trPhone' => 'nullable|string|max:20',
        ], [], ['trName' => 'name']);

        Transporter::create([
            'name'           => trim($this->trName),
            'company_name'   => trim($this->trCompany) ?: null,
            'phone'          => trim($this->trPhone) ?: null,
            'vehicle_number' => trim($this->trVehicle) ?: null,
        ]);

        $this->reset(['trName', 'trCompany', 'trPhone', 'trVehicle', 'showTransporterForm']);
        $this->resetErrorBag();
    }

    public function confirmDeleteTransporter(int $id): void
    {
        $this->trConfirmDelete = $id;
    }

    public function deleteTransporter(): void
    {
        if (! $this->trConfirmDelete) return;
        $tr = Transporter::findOrFail($this->trConfirmDelete);
        if ($tr->transfers()->count() > 0) {
            $this->addError('trDelete', 'Cannot delete — transporter has transfer records.');
            $this->trConfirmDelete = null;
            return;
        }
        $tr->forceDelete();
        $this->trConfirmDelete = null;
    }

    // ── Data wipe ──────────────────────────────────────
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
            'prodCategories' => Category::withTrashed(false)->orderBy('name')->get(),
            'expCategories'  => ExpenseCategory::orderBy('sort_order')->orderBy('name')->get(),
            'transporters'   => Transporter::orderBy('name')->get(),
        ]);
    }
}
