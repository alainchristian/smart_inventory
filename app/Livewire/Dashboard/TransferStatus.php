<?php

namespace App\Livewire\Dashboard;

use App\Enums\TransferStatus as TransferStatusEnum;
use App\Models\Transfer;
use Livewire\Component;

class TransferStatus extends Component
{
    public int $pendingApproval  = 0;
    public int $inTransit        = 0;
    public int $discrepancies    = 0;
    public int $deliveredToday   = 0;
    public array $recentTransfers = [];

    public function mount(): void
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $this->pendingApproval = Transfer::where('status', TransferStatusEnum::PENDING)->count();
        $this->inTransit       = Transfer::whereIn('status', [TransferStatusEnum::IN_TRANSIT, TransferStatusEnum::DELIVERED])->count();
        $this->discrepancies   = Transfer::where('has_discrepancy', true)
                                         ->where('status', '!=', TransferStatusEnum::CANCELLED)->count();
        $this->deliveredToday  = Transfer::where(function ($query) {
                                             $query->where('status', TransferStatusEnum::RECEIVED)
                                                   ->whereDate('received_at', today());
                                         })->orWhere(function ($query) {
                                             $query->where('status', TransferStatusEnum::DELIVERED)
                                                   ->whereDate('delivered_at', today());
                                         })->count();

        $this->recentTransfers = Transfer::with(['fromWarehouse', 'toShop'])
            ->whereIn('status', [
                TransferStatusEnum::PENDING,
                TransferStatusEnum::APPROVED,
                TransferStatusEnum::IN_TRANSIT,
                TransferStatusEnum::DELIVERED,
            ])
            ->orderByDesc('created_at')
            ->limit(4)
            ->get()
            ->map(fn($t) => [
                'id'     => $t->id,
                'from'   => $t->fromWarehouse?->name ?? '—',
                'to'     => $t->toShop?->name ?? '—',
                'status' => $t->status->value,
                'age'    => $t->created_at->diffForHumans(),
            ])->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard.transfer-status');
    }
}
