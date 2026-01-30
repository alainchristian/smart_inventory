<?php

namespace App\Livewire\WarehouseManager;

use App\Models\Alert;
use App\Models\Box;
use App\Models\Transfer;
use App\Services\Inventory\TransferService;
use Livewire\Component;

class Dashboard extends Component
{
    public $warehouseId;
    public $refreshInterval = 60000; // milliseconds

    protected $listeners = [
        'refreshDashboard' => '$refresh',
        'transfer-approved' => '$refresh',
    ];

    public function mount()
    {
        $user = auth()->user();

        if (!$user->isWarehouseManager()) {
            abort(403, 'Unauthorized access.');
        }

        $this->warehouseId = $user->location_id;
    }

    public function approveTransfer($transferId)
    {
        $transfer = Transfer::findOrFail($transferId);

        $this->authorize('approve', $transfer);

        try {
            $transferService = app(TransferService::class);
            $transferService->approveTransfer($transfer);

            session()->flash('success', "Transfer {$transfer->transfer_number} approved successfully.");
            $this->dispatch('transfer-approved');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function rejectTransfer($transferId, $reason)
    {
        $transfer = Transfer::findOrFail($transferId);

        $this->authorize('approve', $transfer);

        try {
            $transferService = app(TransferService::class);
            $transferService->rejectTransfer($transfer, $reason);

            session()->flash('success', "Transfer {$transfer->transfer_number} rejected.");
            $this->dispatch('transfer-rejected');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function getStockByStatus()
    {
        return [
            'full' => Box::where('location_type', 'warehouse')
                ->where('location_id', $this->warehouseId)
                ->where('status', 'full')
                ->count(),
            'partial' => Box::where('location_type', 'warehouse')
                ->where('location_id', $this->warehouseId)
                ->where('status', 'partial')
                ->count(),
            'empty' => Box::where('location_type', 'warehouse')
                ->where('location_id', $this->warehouseId)
                ->where('status', 'empty')
                ->count(),
            'damaged' => Box::where('location_type', 'warehouse')
                ->where('location_id', $this->warehouseId)
                ->where('status', 'damaged')
                ->count(),
        ];
    }

    public function getRecentBoxes()
    {
        return Box::where('location_type', 'warehouse')
            ->where('location_id', $this->warehouseId)
            ->with(['product', 'receivedBy'])
            ->orderBy('received_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function resolveAlert($alertId)
    {
        $alert = Alert::findOrFail($alertId);
        $alert->markAsResolved();

        session()->flash('success', 'Alert resolved.');
    }

    public function render()
    {
        return view('livewire.warehouse-manager.dashboard', [
            'stockByStatus' => $this->getStockByStatus(),
            'recentBoxes' => $this->getRecentBoxes(),
        ]);
    }
}
