<?php

namespace App\Livewire\Layout;

use App\Models\Alert;
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

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountProperty(): int
    {
        return Alert::unresolved()
            ->notDismissed()
            ->critical()
            ->count();
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
        ];
    }

    /**
     * Get total pending actions count
     */
    public function getTotalPendingActionsProperty(): int
    {
        return collect($this->pendingActions)->sum('count');
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
            'currentMonth' => now()->format('M Y'),
            'currentDate' => now()->format('l, F j, Y'),
        ]);
    }
}
