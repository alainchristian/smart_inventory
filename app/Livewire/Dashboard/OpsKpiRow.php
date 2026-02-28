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
    public int $activeBoxes = 0;
    public int $warehouseBoxes = 0;
    public int $shopBoxes = 0;
    public int $activeTransfers = 0;
    public int $inTransitCount = 0;
    public int $pendingCount = 0;
    public int $lowStockTotal = 0;
    public int $lowStockCritical = 0;
    public int $todayCount = 0;
    public int $todayRevenue = 0;
    public float $revenueGrowth = 0;

    public function mount(): void
    {
        $this->loadData();
    }

    #[On('time-filter-changed')]
    public function refresh(array $payload): void
    {
        $this->period = $payload['period'] ?? 'today';
        $this->loadData();
    }

    private function loadData(): void
    {
        $this->activeBoxes    = Box::where('items_remaining', '>', 0)->count();
        $this->warehouseBoxes = Box::where('items_remaining', '>', 0)
                                   ->where('location_type', 'warehouse')->count();
        $this->shopBoxes      = $this->activeBoxes - $this->warehouseBoxes;

        $this->activeTransfers = Transfer::whereIn('status',
            ['pending','approved','in_transit','delivered'])->count();
        $this->inTransitCount  = Transfer::whereIn('status', ['in_transit','delivered'])->count();
        $this->pendingCount    = Transfer::where('status', 'pending')->count();

        $this->lowStockTotal    = Alert::where('title', 'Low Stock Alert')
                                       ->where('is_resolved', false)->count();
        $this->lowStockCritical = Alert::where('title', 'Low Stock Alert')
                                       ->where('severity', 'critical')
                                       ->where('is_resolved', false)->count();

        $this->todayCount    = Sale::whereDate('sale_date', today())->count();
        $this->todayRevenue  = Sale::whereDate('sale_date', today())->sum('total') / 100;
        $yesterday           = Sale::whereDate('sale_date', today()->subDay())->sum('total') / 100;
        $this->revenueGrowth = $yesterday > 0
            ? round((($this->todayRevenue - $yesterday) / $yesterday) * 100, 1)
            : 0;
    }

    public function render()
    {
        return view('livewire.dashboard.ops-kpi-row');
    }
}
