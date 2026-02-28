<?php

namespace App\Livewire\Owner\Reports;

use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Analytics\InventoryAnalyticsService;
use Livewire\Component;

class InventoryValuation extends Component
{
    public $locationFilter = 'all';

    protected $queryString = ['locationFilter'];

    public function mount()
    {
        // Ensure only owners can access
        if (!auth()->user()->isOwner()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function getInventoryKpisProperty()
    {
        $service = app(InventoryAnalyticsService::class);
        return $service->getInventoryKpis($this->locationFilter);
    }

    public function getInventoryByLocationProperty()
    {
        $service = app(InventoryAnalyticsService::class);
        return $service->getInventoryByLocation();
    }

    public function getAgingAnalysisProperty()
    {
        $service = app(InventoryAnalyticsService::class);
        return $service->getAgingAnalysis($this->locationFilter);
    }

    public function getExpiringStockProperty()
    {
        $service = app(InventoryAnalyticsService::class);
        return $service->getExpiringStock($this->locationFilter, 30);
    }

    public function getStockHealthProperty()
    {
        $service = app(InventoryAnalyticsService::class);
        return $service->getStockHealth($this->locationFilter);
    }

    public function getTopProductsByValueProperty()
    {
        $service = app(InventoryAnalyticsService::class);
        return $service->getTopProductsByValue($this->locationFilter, 20);
    }

    public function getWarehousesProperty()
    {
        return Warehouse::orderBy('name')->get();
    }

    public function getShopsProperty()
    {
        return Shop::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.owner.reports.inventory-valuation');
    }
}
