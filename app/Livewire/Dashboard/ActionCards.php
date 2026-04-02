<?php

namespace App\Livewire\Dashboard;

use App\Models\HeldSale;
use App\Models\Transfer;
use App\Models\Alert;
use App\Models\DamagedGood;
use Livewire\Component;

class ActionCards extends Component
{
    public int $pendingApprovalCount = 0;
    public int $discrepancyCount     = 0;
    public int $damagedPendingCount  = 0;
    public int $criticalAlertsCount  = 0;
    public int $pendingHeldCount     = 0;

    public function mount(): void
    {
        $this->loadCounts();
    }

    public function loadCounts(): void
    {
        $this->pendingApprovalCount = Transfer::where('status', 'pending')->count();
        $this->discrepancyCount     = Transfer::where('has_discrepancy', true)->count();
        $this->criticalAlertsCount  = Alert::where('severity', 'critical')
                                          ->whereNull('resolved_at')
                                          ->where('is_dismissed', false)
                                          ->count();
        $this->damagedPendingCount  = class_exists(DamagedGood::class)
            ? DamagedGood::where('disposition', 'pending')->count()
            : 0;
        $this->pendingHeldCount = HeldSale::where('needs_price_approval', true)
            ->whereNull('override_approved_at')
            ->whereNull('override_rejected_at')
            ->count();
    }

    public function getHasPendingActionsProperty(): bool
    {
        return ($this->pendingApprovalCount
              + $this->discrepancyCount
              + $this->criticalAlertsCount
              + $this->damagedPendingCount
              + $this->pendingHeldCount) > 0;
    }

    public function render()
    {
        return view('livewire.dashboard.action-cards');
    }
}
