<?php

namespace App\Livewire\Layout;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Topbar extends Component
{
    public $searchQuery = '';
    public $pageTitle;

    /**
     * Topbar title per page, by route name. The layout renders
     * <livewire:layout.topbar /> without a title, so without this every page
     * read "Dashboard". mount() runs on every full page load, including
     * wire:navigate visits; the value then persists through the bell's polls.
     */
    public const TITLES = [
        'dashboard'                          => 'Dashboard',
        'profile'                            => 'My Profile',
        'password.change'                    => 'Change Password',

        // Owner
        'owner.dashboard'                    => 'Dashboard',
        'owner.products.index'               => 'Products',
        'owner.products.create'              => 'New Product',
        'owner.products.edit'                => 'Edit Product',
        'owner.categories.index'             => 'Product Categories',
        'owner.expense-categories.index'     => 'Expense Categories',
        'owner.boxes.index'                  => 'All Boxes',
        'owner.boxes.show'                   => 'Box Details',
        'owner.inventory.receive'            => 'Receive Stock',
        'owner.customers.index'              => 'Customers',
        'owner.users.index'                  => 'Users',
        'owner.shops.index'                  => 'Shops',
        'owner.warehouses.index'             => 'Warehouses',
        'owner.transporters.index'           => 'Transporters',
        'owner.transfers.index'              => 'Transfers',
        'owner.transfers.show'               => 'Transfer Details',
        'owner.sales.index'                  => 'Sales',
        'owner.sales.show'                   => 'Sale Details',
        'owner.returns.index'                => 'Returns',
        'owner.damaged-goods.index'          => 'Damaged Goods',
        'owner.credit.writeoffs'             => 'Credit Write-offs',
        'owner.finance.overview'             => 'Finance Overview',
        'owner.finance.daily'                => 'Daily Finance',
        'owner.finance.income-statement'     => 'Income Statement',
        'owner.reports.sales'                => 'Sales Analytics',
        'owner.reports.inventory'            => 'Inventory Valuation',
        'owner.reports.transfers'            => 'Transfer Performance',
        'owner.reports.losses'               => 'Loss Analysis',
        'owner.reports.payment-methods'      => 'Payment Methods',
        'owner.reports.customer-credit'      => 'Customer Credit',
        'owner.reports.daily'                => 'Daily Report',
        'owner.reports.custom.library'       => 'Custom Reports',
        'owner.reports.custom.builder'       => 'Report Builder',
        'owner.reports.custom.view'          => 'Custom Report',
        'owner.settings'                     => 'Settings',
        'owner.system'                       => 'System',
        'owner.alerts.index'                 => 'Alerts',
        'owner.activity-logs.index'          => 'Activity Logs',

        // Shop
        'shop.dashboard'                     => 'Dashboard',
        'shop.pos'                           => 'Point of Sale',
        'shop.warehouse-sale'                => 'Warehouse Sale',
        'shop.sales.index'                   => 'Sales History',
        'shop.receipts'                      => 'Receipts',
        'shop.returns.index'                 => 'Returns',
        'shop.returns.create'                => 'New Return',
        'shop.damaged-goods.index'           => 'Damaged Goods',
        'shop.inventory.stock'               => 'Stock Levels',
        'shop.transfers.index'               => 'Transfers',
        'shop.transfers.request'             => 'Request Stock',
        'shop.transfers.show'                => 'Transfer Details',
        'shop.transfers.receive'             => 'Receive Transfer',
        'shop.transfers.returns'             => 'Return to Warehouse',
        'shop.credit-repayments'             => 'Credit Repayments',
        'shop.day-close.index'               => 'Cash Register',
        'shop.session.open'                  => 'Cash Register',
        'shop.day-close.close'               => 'Close Register',
        'shop.session.close'                 => 'Close Register',
        'shop.session.history'               => 'Session History',
        'shop.session.requests'              => 'Expense Requests',
        'shop.expense-requests'              => 'Expense Requests',
        'shop.expenses.add'                  => 'Record Expense',
        'shop.withdrawals.add'               => 'Owner Withdrawal',
        'shop.bank-deposits'                 => 'Bank Deposits',
        'shop.reports.daily'                 => 'Daily Report',

        // Warehouse
        'warehouse.dashboard'                => 'Dashboard',
        'warehouse.inventory.boxes'          => 'Boxes',
        'warehouse.inventory.stock-levels'   => 'Stock Levels',
        'warehouse.transfers.index'          => 'Transfers',
        'warehouse.transfers.show'           => 'Transfer Details',
        'warehouse.transfers.pack'           => 'Pack Transfer',
        'warehouse.sales.fulfillment'        => 'Fulfillment',
        'warehouse.stock-returns'            => 'Returns from Shops',
        'warehouse.expense-requests.index'   => 'Expense Requests',
    ];

    public function mount($pageTitle = null)
    {
        $this->pageTitle = $pageTitle ?? self::TITLES[request()->route()?->getName()] ?? 'Dashboard';
    }

    // ── Owner "act as admin" toggle ──────────────────────────────────────────

    /**
     * Flip the session between owner and admin permissions. Only a real owner
     * may call this — the stored role never changes, only the session flag.
     */
    public function toggleAdminMode()
    {
        $user = Auth::user();

        abort_unless($user && $user->isRealOwner(), 403);

        $enable = !$user->isActingAsAdmin();

        if ($enable) {
            session([User::ACTING_AS_ADMIN_SESSION_KEY => true]);
        } else {
            session()->forget(User::ACTING_AS_ADMIN_SESSION_KEY);
        }

        AuditLogger::log([
            'actor'               => $user,
            'actor_role_snapshot' => 'owner',
            'action'              => $enable ? 'role_switched_to_admin' : 'role_switched_to_owner',
            'module'              => 'auth',
            'entity_type'         => 'User',
            'entity_id'           => $user->id,
            'entity_identifier'   => $user->name,
            'details'             => ['from' => $enable ? 'owner' : 'admin', 'to' => $enable ? 'admin' : 'owner'],
        ]);

        return $this->redirect(route('owner.dashboard'), navigate: false);
    }

    /**
     * Handle search
     */
    public function search()
    {
        // Implement global search logic
        $this->dispatch('global-search', query: $this->searchQuery);
    }

    public function render()
    {
        return view('livewire.layout.topbar', [
            'currentMonth' => now()->translatedFormat('M Y'),
            'currentDate' => now()->translatedFormat('l, F j, Y'),
        ]);
    }
}
