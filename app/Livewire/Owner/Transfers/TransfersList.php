<?php

namespace App\Livewire\Owner\Transfers;

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

        // Verify user is owner
        if (!$user->isOwner()) {
            abort(403, 'Only owners can access this page.');
        }

        // Check if status filter is passed via query string
        if (request()->has('status')) {
            $this->statusFilter = request('status');
        }
    }

    public function render()
    {
        // Build query for ALL transfers (owner sees everything)
        $query = Transfer::with(['fromWarehouse', 'toShop', 'requestedBy', 'items.product'])
            ->orderBy('created_at', 'desc');

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            if ($this->statusFilter === 'discrepancy') {
                // "discrepancy" is not a real enum value — filter by flag instead
                $query->where('has_discrepancy', true);
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        $transfers = $query->paginate(20);

        // Get counts for all statuses
        $pendingCount = Transfer::where('status', TransferStatus::PENDING)->count();
        $approvedCount = Transfer::where('status', TransferStatus::APPROVED)->count();
        $inTransitCount = Transfer::where('status', TransferStatus::IN_TRANSIT)->count();
        $deliveredCount = Transfer::where('status', TransferStatus::DELIVERED)->count();
        $receivedCount = Transfer::where('status', TransferStatus::RECEIVED)->count();
        $discrepancyCount = Transfer::where('has_discrepancy', true)->count();

        return view('livewire.owner.transfers.transfers-list', [
            'transfers' => $transfers,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'inTransitCount' => $inTransitCount,
            'deliveredCount' => $deliveredCount,
            'receivedCount' => $receivedCount,
            'discrepancyCount' => $discrepancyCount,
        ]);
    }
}
