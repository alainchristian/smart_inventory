<?php

namespace App\Livewire\Layout;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\HeldSale;
use App\Models\Transfer;
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

    // ── Notification feed ────────────────────────────────────────────────────

    private function notifiableActions(): array
    {
        return [
            'sale_created', 'mixed_sale_created', 'warehouse_direct_sale', 'sale_voided', 'price_modified',
            'transfer_requested', 'transfer_approved', 'transfer_rejected',
            'transfer_packed', 'transfer_received', 'transfer_discrepancy',
            'daily_session_opened', 'daily_session_closed',
            'return', 'return_approved',
            'box_damaged', 'box_adjustment',
            'credit_writeoff',
            'held_sale_approved', 'held_sale_rejected',
        ];
    }

    public function getActivityNotificationsProperty(): array
    {
        if (!Auth::check()) return [];

        $user = Auth::user();

        $query = ActivityLog::query()
            ->whereIn('action', $this->notifiableActions())
            ->where('user_id', '!=', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('created_at')
            ->limit(25);

        if ($user->isOwner() || $user->isAdmin()) {
            $query->whereHas('user', fn($q) => $q->whereIn('role', ['shop_manager', 'warehouse_manager']));
        } elseif ($user->isWarehouseManager()) {
            $shopIds = Transfer::where('from_warehouse_id', $user->location_id)->pluck('to_shop_id')->unique();
            $query->where('action', 'transfer_requested')
                  ->where('entity_type', 'Transfer')
                  ->whereIn('entity_id', Transfer::where('from_warehouse_id', $user->location_id)->pluck('id'));
        } elseif ($user->isShopManager()) {
            $shopTransferIds = Transfer::where('to_shop_id', $user->location_id)->pluck('id');
            // Sellers only see the decision on holds THEY created — not every
            // price-override decision made for the shop.
            $ownHeldSaleIds  = HeldSale::where('seller_id', $user->id)->pluck('id');

            $query->where(function ($q) use ($shopTransferIds, $ownHeldSaleIds) {
                $q->where(function ($q2) use ($shopTransferIds) {
                    $q2->whereIn('action', ['transfer_approved', 'transfer_rejected', 'transfer_packed'])
                       ->where('entity_type', 'Transfer')
                       ->whereIn('entity_id', $shopTransferIds);
                })->orWhere(function ($q2) use ($ownHeldSaleIds) {
                    $q2->whereIn('action', ['held_sale_approved', 'held_sale_rejected'])
                       ->where('entity_type', 'HeldSale')
                       ->whereIn('entity_id', $ownHeldSaleIds);
                });
            });
        } else {
            return [];
        }

        $readAt = $user->notifications_read_at;

        return $query->get()
            ->map(fn($log) => [
                'id'       => $log->id,
                'label'    => $log->humanLabel(),
                'subtitle' => $log->subtitle(),
                'icon'     => $log->iconKey(),
                'color'    => $log->colorKey(),
                'url'      => $log->actionUrl($user),
                'age'      => $log->created_at->diffForHumans(),
                'unread'   => $readAt === null || $log->created_at > $readAt,
            ])
            ->toArray();
    }

    public function getUnreadActivityCountProperty(): int
    {
        if (!Auth::check()) return 0;

        $user = Auth::user();
        $readAt = $user->notifications_read_at;

        if ($readAt === null) {
            return min(collect($this->activityNotifications)->count(), 9);
        }

        return collect($this->activityNotifications)
            ->filter(fn($n) => $n['unread'])
            ->count();
    }

    public function markActivityRead(): void
    {
        if (!Auth::check()) return;

        Auth::user()->update(['notifications_read_at' => now()]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountProperty(): int
    {
        return $this->totalPendingActions + $this->unreadActivityCount;
    }

    /**
     * Get pending actions for owner
     */
    public function getPendingActionsProperty(): array
    {
        if (!Auth::check() || (!Auth::user()->isOwner() && !Auth::user()->isAdmin())) {
            return [];
        }

        return [
            [
                'type' => 'transfer_approval',
                'count' => \App\Models\Transfer::where('status', 'pending')->count(),
                'label' => 'Transfer Approvals',
                'icon' => 'clock',
                'color' => 'amber',
                'route' => 'owner.transfers.index',
            ],
            [
                'type' => 'discrepancy',
                'count' => \App\Models\Transfer::where('has_discrepancy', true)
                    ->where('status', 'received')
                    ->count(),
                'label' => 'Transfer Discrepancies',
                'icon' => 'alert',
                'color' => 'red',
                'route' => 'owner.transfers.index',
            ],
            [
                'type' => 'damaged_goods',
                'count' => \App\Models\DamagedGood::where('disposition', 'pending')->count(),
                'label' => 'Damaged Goods Decisions',
                'icon' => 'box',
                'color' => 'orange',
                'route' => null,
            ],
            [
                'type' => 'critical_alert',
                'count' => Alert::critical()->unresolved()->notDismissed()->count(),
                'label' => 'Critical Alerts',
                'icon' => 'alert-circle',
                'color' => 'red',
                'route' => null,
                'url'   => route('owner.alerts.index') . '?filterStatus=unresolved&filterSeverity=critical',
            ],
            [
                // Completed sales with has_price_override are NOT counted here —
                // per the price_override_threshold setting, a sale only completes
                // directly when it's within policy, so it needs no owner action.
                // Only HeldSale rows (blocked pre-checkout, over threshold) do.
                'type'  => 'price_approval',
                'count' => HeldSale::where('needs_price_approval', true)
                    ->whereNull('override_approved_at')
                    ->whereNull('override_rejected_at')
                    ->count(),
                'label' => 'Price Override Approvals',
                'icon'  => 'tag',
                'color' => 'amber',
                'route' => null,
                'url'   => route('owner.reports.sales') . '?activeTab=audit',
            ],
        ];
    }

    /**
     * Get total pending actions count
     */
    public function getTotalPendingActionsProperty(): int
    {
        return collect($this->pendingActions)->sum('count');
    }

    // Held-sale and completed-sale price-override approve/reject now live in
    // the Price Audit module (App\Livewire\Owner\Reports\SalesAnalytics) —
    // this bell only links there (see the 'price_approval' action above).

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
