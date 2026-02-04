<?php

namespace App\Livewire\WarehouseManager\Transfers;

use App\Enums\TransferStatus;
use App\Models\Transfer;
use Livewire\Component;
use Livewire\WithPagination;

class TransfersList extends Component
{
    use WithPagination;

    public $statusFilter = 'all';

    public function mount()
    {
        $user = auth()->user();

        // Verify user is a warehouse manager
        if (!$user->isWarehouseManager()) {
            abort(403, 'Only warehouse managers can access this page.');
        }
    }

    public function render()
    {
        $user = auth()->user();
        $warehouseId = $user->location_id;

        // Build query for transfers from this warehouse
        $query = Transfer::with(['toShop', 'requestedBy', 'items.product'])
            ->where('from_warehouse_id', $warehouseId)
            ->orderBy('created_at', 'desc');

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $transfers = $query->paginate(20);

        // Get counts for badges
        $pendingCount = Transfer::where('from_warehouse_id', $warehouseId)
            ->where('status', TransferStatus::PENDING)
            ->count();

        $approvedCount = Transfer::where('from_warehouse_id', $warehouseId)
            ->where('status', TransferStatus::APPROVED)
            ->count();

        $inTransitCount = Transfer::where('from_warehouse_id', $warehouseId)
            ->where('status', TransferStatus::IN_TRANSIT)
            ->count();

        return view('livewire.warehouse-manager.transfers.transfers-list', [
            'transfers' => $transfers,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'inTransitCount' => $inTransitCount,
        ]);
    }
}
