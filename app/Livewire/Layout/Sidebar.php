<?php

namespace App\Livewire\Layout;

use App\Enums\TransferStatus;
use App\Models\Alert;
use App\Models\DamagedGood;
use App\Models\Transfer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    /**
     * Get unresolved alerts count
     */
    public function getAlertsCountProperty(): int
    {
        return Alert::unresolved()
            ->notDismissed()
            ->count();
    }

    /**
     * Get pending transfers count
     */
    public function getPendingTransfersCountProperty(): int
    {
        return Transfer::where('status', TransferStatus::PENDING)->count();
    }

    /**
     * Get damaged goods count (pending disposition)
     */
    public function getDamagedGoodsCountProperty(): int
    {
        return DamagedGood::pendingDisposition()->count();
    }

    public function render()
    {
        return view('livewire.layout.sidebar', [
            'user' => Auth::user(),
        ]);
    }
}
