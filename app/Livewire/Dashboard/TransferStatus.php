<?php

namespace App\Livewire\Dashboard;

use App\Models\Transfer;
use Livewire\Component;

class TransferStatus extends Component
{
    public int $pendingApproval = 0;
    public int $inTransit       = 0;
    public int $discrepancies   = 0;
    public int $deliveredToday  = 0;

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
    }

    public function render()
    {
        return view('livewire.dashboard.transfer-status');
    }
}
