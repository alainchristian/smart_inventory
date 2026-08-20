<?php

namespace App\Livewire\Layout;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\HeldSale;
use App\Models\Transfer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Topbar extends Component
{
    public $searchQuery = '';
    public $pageTitle;

    public function mount($pageTitle = 'Dashboard')
    {
        $this->pageTitle = $pageTitle;
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

        if ($user->isOwner()) {
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
        if (!Auth::check() || !Auth::user()->isOwner()) {
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
            'currentMonth' => now()->format('M Y'),
            'currentDate' => now()->format('l, F j, Y'),
        ]);
    }
}
