<?php

namespace App\Livewire\Shop\Transfers;

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

        // Verify user is a shop manager
        if (!$user->isShopManager()) {
            abort(403, 'Only shop managers can access this page.');
        }
    }

    public function render()
    {
        $user = auth()->user();
        $shopId = $user->location_id;

        // Build query for transfers to this shop
        $query = Transfer::with(['fromWarehouse', 'requestedBy', 'items.product'])
            ->where('to_shop_id', $shopId)
            ->orderBy('created_at', 'desc');

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $transfers = $query->paginate(20);

        // Get counts for badges
        $pendingCount = Transfer::where('to_shop_id', $shopId)
            ->where('status', TransferStatus::PENDING)
            ->count();

        $approvedCount = Transfer::where('to_shop_id', $shopId)
            ->where('status', TransferStatus::APPROVED)
            ->count();

        $inTransitCount = Transfer::where('to_shop_id', $shopId)
            ->where('status', TransferStatus::IN_TRANSIT)
            ->count();

        $deliveredCount = Transfer::where('to_shop_id', $shopId)
            ->where('status', TransferStatus::DELIVERED)
            ->count();

        return view('livewire.shop.transfers.transfers-list', [
            'transfers' => $transfers,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'inTransitCount' => $inTransitCount,
            'deliveredCount' => $deliveredCount,
        ]);
    }
}
