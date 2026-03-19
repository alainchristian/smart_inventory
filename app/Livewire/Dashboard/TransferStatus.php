<?php

namespace App\Livewire\Dashboard;

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
        $this->pendingApproval = Transfer::where('status', 'pending')->count();
        $this->inTransit       = Transfer::whereIn('status', ['in_transit','delivered'])->count();
        $this->discrepancies   = Transfer::where('has_discrepancy', true)
                                         ->whereNot('status', 'cancelled')->count();
        $this->deliveredToday  = Transfer::where(function($query) {
                                             $query->where('status', 'delivered')
                                                   ->orWhere('status', 'received');
                                         })
                                         ->whereDate('delivered_at', today())
                                         ->count();

        $this->recentTransfers = \App\Models\Transfer::with(['fromWarehouse', 'toShop'])
            ->whereIn('status', ['pending', 'approved', 'in_transit', 'delivered'])
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
