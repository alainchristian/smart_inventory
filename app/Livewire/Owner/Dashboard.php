<?php

namespace App\Livewire\Owner;

use App\Models\Alert;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Transfer;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public $dateRange = '30'; // days
    public $refreshInterval = 60000; // milliseconds (1 minute)

    protected $listeners = ['refreshDashboard' => '$refresh'];

    public function mount()
    {
        // Ensure only owners can access
        if (!auth()->user()->isOwner()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function getDailySalesData()
    {
        // Get sales data for the last 7 days
        $days = 7;
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $total = Sale::notVoided()
                ->whereDate('sale_date', $date)
                ->sum('total') / 100;

            $data[] = [
                'date' => $date->format('M d'),
                'total' => $total,
            ];
        }

        return $data;
    }

    public function getTransferStatusData()
    {
        return [
            'pending' => Transfer::where('status', 'pending')->count(),
            'approved' => Transfer::where('status', 'approved')->count(),
            'in_transit' => Transfer::where('status', 'in_transit')->count(),
            'delivered' => Transfer::where('status', 'delivered')->count(),
            'received' => Transfer::where('status', 'received')->count(),
        ];
    }

    public function getCriticalAlerts()
    {
        return Alert::critical()
            ->unresolved()
            ->notDismissed()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function getRecentUsers()
    {
        return User::with('location')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function resolveAlert($alertId)
    {
        $alert = Alert::findOrFail($alertId);
        $alert->markAsResolved();

        $this->dispatch('alert-resolved');
        session()->flash('success', 'Alert resolved successfully.');
    }

    public function dismissAlert($alertId)
    {
        $alert = Alert::findOrFail($alertId);
        $alert->markAsDismissed();

        $this->dispatch('alert-dismissed');
        session()->flash('info', 'Alert dismissed.');
    }

    public function render()
    {
        return view('livewire.owner.dashboard', [
            'dailySalesData' => $this->getDailySalesData(),
            'transferStatusData' => $this->getTransferStatusData(),
            'criticalAlerts' => $this->getCriticalAlerts(),
            'recentUsers' => $this->getRecentUsers(),
        ]);
    }
}
