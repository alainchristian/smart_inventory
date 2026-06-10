<?php

namespace App\Livewire\Owner\Reports;

use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Analytics\InventoryAnalyticsService;
use Carbon\Carbon;
use Livewire\Component;

class InventoryValuation extends Component
{
    // ─── Filters ──────────────────────────────────────────────────────────────
    public string $locationFilter = 'all';
    public string $activeTab      = 'overview'; // overview | valuation | health | replenishment
    public string $urgencyFilter  = 'all';      // all | critical | reorder
    public string $dateFrom       = '';
    public string $dateTo         = '';
    public int    $leadTimeDays   = 14;

    // ─── Sort state ───────────────────────────────────────────────────────────
    public string $valSortBy   = 'purchase_value';
    public string $valSortDir  = 'desc';
    public string $replSortBy  = 'days_on_hand';
    public string $replSortDir = 'asc';

    protected $queryString = [
        'locationFilter' => ['except' => 'all'],
        'activeTab'      => ['except' => 'overview'],
        'urgencyFilter'  => ['except' => 'all'],
        'dateFrom'       => ['except' => ''],
        'dateTo'         => ['except' => ''],
        'valSortBy'      => ['except' => 'purchase_value'],
        'valSortDir'     => ['except' => 'desc'],
        'replSortBy'     => ['except' => 'days_on_hand'],
        'replSortDir'    => ['except' => 'asc'],
        'leadTimeDays'   => ['except' => 14],
    ];

    // ─── Lifecycle ────────────────────────────────────────────────────────────
    public function mount(): void
    {
        if (! auth()->user()->isOwner() && ! auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if (! $this->dateFrom) $this->dateFrom = now()->startOfMonth()->toDateString();
        if (! $this->dateTo)   $this->dateTo   = now()->toDateString();
    }

    // ─── Actions ──────────────────────────────────────────────────────────────
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function setDateRange(string $range): void
    {
        $this->dateTo   = now()->toDateString();
        $this->dateFrom = match ($range) {
            'week'    => now()->startOfWeek()->toDateString(),
            'month'   => now()->startOfMonth()->toDateString(),
            'quarter' => now()->startOfQuarter()->toDateString(),
            'year'    => now()->startOfYear()->toDateString(),
            'last_30' => now()->subDays(29)->toDateString(),
            'last_90' => now()->subDays(89)->toDateString(),
            default   => now()->startOfMonth()->toDateString(),
        };
    }

    public function sortValuation(string $col): void
    {
        $allowed = ['purchase_value', 'retail_value', 'product_name', 'box_count'];
        if (! in_array($col, $allowed)) return;
        if ($this->valSortBy === $col) {
            $this->valSortDir = $this->valSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->valSortBy  = $col;
            $this->valSortDir = 'desc';
        }
    }

    public function sortReplenishment(string $col): void
    {
        $allowed = ['days_on_hand', 'product_name', 'boxes_sold_period', 'box_count'];
        if (! in_array($col, $allowed)) return;
        if ($this->replSortBy === $col) {
            $this->replSortDir = $this->replSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->replSortBy  = $col;
            $this->replSortDir = $col === 'days_on_hand' ? 'asc' : 'desc';
        }
    }

    // ─── Derived: lookback window in days ────────────────────────────────────
    protected function lookbackDays(): int
    {
        return max(1, Carbon::parse($this->dateFrom)->diffInDays(Carbon::parse($this->dateTo)) + 1);
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
            ->getShrinkageStats($this->locationFilter, $this->lookbackDays());
    }

    // ─── Computed: Valuation tab ───────────────────────────────────────────────
    public function getInventoryByLocationProperty(): array
    {
        return app(InventoryAnalyticsService::class)->getInventoryByLocation();
    }

    public function getTopProductsByValueProperty(): array
    {
        $products = app(InventoryAnalyticsService::class)
            ->getTopProductsByValue($this->locationFilter, 20);

        $sorted = $this->valSortDir === 'asc'
            ? collect($products)->sortBy($this->valSortBy)
            : collect($products)->sortByDesc($this->valSortBy);

        return $sorted->values()->toArray();
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
            ->getVelocityClassification($this->locationFilter, $this->lookbackDays());
    }

    public function getInventoryMovementTrendProperty(): array
    {
        return app(InventoryAnalyticsService::class)
            ->getInventoryMovementTrend($this->locationFilter, $this->lookbackDays());
    }

    // ─── Computed: Replenishment tab ──────────────────────────────────────────
    public function getDaysOnHandPerProductProperty(): array
    {
        $products = app(InventoryAnalyticsService::class)
            ->getDaysOnHandPerProduct($this->locationFilter, 50, $this->lookbackDays());

        $collection = collect($products);

        if ($this->replSortBy === 'days_on_hand') {
            $sorted = $this->replSortDir === 'asc'
                ? $collection->sortBy(fn ($p) => $p['days_on_hand'] ?? PHP_INT_MAX)
                : $collection->sortByDesc(fn ($p) => $p['days_on_hand'] ?? -1);
        } else {
            $sorted = $this->replSortDir === 'asc'
                ? $collection->sortBy($this->replSortBy)
                : $collection->sortByDesc($this->replSortBy);
        }

        return $sorted->values()->toArray();
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
