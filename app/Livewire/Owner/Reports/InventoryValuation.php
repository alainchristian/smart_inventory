<?php

namespace App\Livewire\Owner\Reports;

use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Analytics\InventoryAnalyticsService;
use Livewire\Component;

class InventoryValuation extends Component
{
    // ─── Filters ──────────────────────────────────────────────────────────────
    public string $locationFilter  = 'all';
    public string $activeTab       = 'overview'; // overview | valuation | health | replenishment
    public string $urgencyFilter   = 'all';      // all | critical | reorder

    protected $queryString = [
        'locationFilter' => ['except' => 'all'],
        'activeTab'      => ['except' => 'overview'],
    ];

    // ─── Lifecycle ────────────────────────────────────────────────────────────
    public function mount(): void
    {
        if (! auth()->user()->isOwner() && ! auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    // ─── Actions ──────────────────────────────────────────────────────────────
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ─── Computed: shared (loaded on every tab) ────────────────────────────────
    public function getInventoryKpisProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getInventoryKpis($this->locationFilter);
    }

    public function getPortfolioFillRateProperty(): ?float
    {
        return app(InventoryAnalyticsService::class)
            ->getPortfolioFillRate($this->locationFilter);
    }

    public function getShrinkageStatsProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getShrinkageStats($this->locationFilter);
    }

    // ─── Computed: Valuation tab ───────────────────────────────────────────────
    public function getInventoryByLocationProperty(): array
    {
        return app(InventoryAnalyticsService::class)->getInventoryByLocation();
    }

    public function getTopProductsByValueProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getTopProductsByValue($this->locationFilter, 20);
    }

    public function getCategoryConcentrationProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getCategoryConcentration($this->locationFilter);
    }

    // ─── Computed: Health tab ─────────────────────────────────────────────────
    public function getStockHealthProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getStockHealth($this->locationFilter);
    }

    public function getAgingAnalysisProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getAgingAnalysis($this->locationFilter);
    }

    public function getExpiringStockProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getExpiringStock($this->locationFilter, 30);
    }

    public function getVelocityClassificationProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getVelocityClassification($this->locationFilter);
    }

    public function getInventoryMovementTrendProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getInventoryMovementTrend($this->locationFilter);
    }

    // ─── Computed: Replenishment tab ──────────────────────────────────────────
    public function getDaysOnHandPerProductProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getDaysOnHandPerProduct($this->locationFilter, 50);
    }

    // ─── Supporting data ──────────────────────────────────────────────────────
    public function getWarehousesProperty()
    {
        return Warehouse::orderBy('name')->get();
    }

    public function getShopsProperty()
    {
        return Shop::orderBy('name')->get();
    }

    // ─── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        return view('livewire.owner.reports.inventory-valuation');
    }
}
