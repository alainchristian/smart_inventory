<?php

namespace App\Livewire\Dashboard;

use App\Models\Box;
use App\Models\Transfer;
use App\Models\Alert;
use App\Models\Sale;
use Livewire\Component;
use Livewire\Attributes\On;

class OpsKpiRow extends Component
{
    public string $period = 'today';

    // Box counts — split clearly so the blade can label them honestly
    public int $sellableBoxes   = 0;   // full/partial + items_remaining > 0 (same as Inventory Health)
    public int $warehouseBoxes  = 0;
    public int $shopBoxes       = 0;
    public int $damagedBoxes    = 0;   // shown separately, not mixed in

    public int   $activeTransfers = 0;
    public int   $inTransitCount  = 0;
    public int   $pendingCount    = 0;
    public int   $lowStockTotal   = 0;
    public int   $lowStockCritical = 0;
    public int   $todayCount      = 0;
    public float $todayRevenue    = 0;
    public float $revenueGrowth   = 0;

    public function mount(): void
    {
        $this->loadData();
    }

    // Livewire 3 named-parameter listener
    #[On('time-filter-changed')]
    public function refresh(string $period, ?string $from = null, ?string $to = null): void
    {
        $this->period = $period;
        $this->loadData();
    }

    private function loadData(): void
    {
        // ── Box counts ────────────────────────────────────────────────────────
        // Box::available() = status IN (full, partial) AND items_remaining > 0
        // This is the SAME filter used by BusinessKpiRow and Inventory Health,
        // so all three sections show the same number for "sellable boxes".
        $this->sellableBoxes  = Box::available()->count();
        $this->warehouseBoxes = Box::available()->where('location_type', 'warehouse')->count();
        $this->shopBoxes      = Box::available()->where('location_type', 'shop')->count();

        // Damaged boxes shown separately — never mixed into the sellable count
        $this->damagedBoxes = Box::where('status', 'damaged')->count();

        // ── Transfers ─────────────────────────────────────────────────────────
        $this->activeTransfers = Transfer::whereIn('status',
            ['pending', 'approved', 'in_transit', 'delivered'])->count();
        $this->inTransitCount  = Transfer::whereIn('status', ['in_transit', 'delivered'])->count();
        $this->pendingCount    = Transfer::where('status', 'pending')->count();

        // ── Alerts ───────────────────────────────────────────────────────────
        $this->lowStockTotal    = Alert::where('title', 'Low Stock Alert')
                                       ->where('is_resolved', false)->count();
        $this->lowStockCritical = Alert::where('title', 'Low Stock Alert')
                                       ->where('severity', 'critical')
                                       ->where('is_resolved', false)->count();

        // ── Today's transactions ──────────────────────────────────────────────
        $this->todayCount   = Sale::notVoided()->whereDate('sale_date', today())->count();
        $this->todayRevenue = Sale::notVoided()->whereDate('sale_date', today())->sum('total') / 100;
        $yesterday          = Sale::notVoided()->whereDate('sale_date', today()->subDay())->sum('total') / 100;

        $this->revenueGrowth = $yesterday > 0
            ? round((($this->todayRevenue - $yesterday) / $yesterday) * 100, 1)
            : 0;
    }

    public function render()
    {
        return view('livewire.dashboard.ops-kpi-row');
    }
}