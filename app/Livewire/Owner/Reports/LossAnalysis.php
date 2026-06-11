<?php

namespace App\Livewire\Owner\Reports;

use App\Models\Shop;
use App\Services\Analytics\LossAnalyticsService;
use Livewire\Component;

class LossAnalysis extends Component
{
    public string $preset       = 'last_30';
    public string $dateFrom     = '';
    public string $dateTo       = '';
    public string $locationFilter = 'all';
    public string $activeTab    = 'overview';

    protected $queryString = ['preset', 'dateFrom', 'dateTo', 'locationFilter', 'activeTab'];

    public function mount(): void
    {
        if (!auth()->user()->isOwner()) {
            abort(403);
        }
        if (!$this->dateFrom) {
            $this->resolveDates();
        }
    }

    public function setPreset(string $preset): void
    {
        $this->preset = $preset;
        $this->resolveDates();
    }

    public function updatedDateFrom(): void { $this->preset = 'custom'; }
    public function updatedDateTo(): void   { $this->preset = 'custom'; }

    public function setTab(string $tab): void { $this->activeTab = $tab; }

    private function resolveDates(): void
    {
        match ($this->preset) {
            'today'      => [$this->dateFrom, $this->dateTo] = [today()->toDateString(), today()->toDateString()],
            'yesterday'  => [$this->dateFrom, $this->dateTo] = [today()->subDay()->toDateString(), today()->subDay()->toDateString()],
            'week'       => [$this->dateFrom, $this->dateTo] = [today()->startOfWeek()->toDateString(), today()->toDateString()],
            'month'      => [$this->dateFrom, $this->dateTo] = [today()->startOfMonth()->toDateString(), today()->toDateString()],
            'last_month' => [$this->dateFrom, $this->dateTo] = [today()->subMonthNoOverflow()->startOfMonth()->toDateString(), today()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            default      => [$this->dateFrom, $this->dateTo] = [now()->subDays(29)->toDateString(), today()->toDateString()],
        };
    }

    public function getLossKpisProperty(): array
    {
        return app(LossAnalyticsService::class)->getLossKpis($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getLossTrendProperty(): array
    {
        return app(LossAnalyticsService::class)->getLossTrend($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getReturnReasonsProperty(): array
    {
        return app(LossAnalyticsService::class)->getReturnReasonBreakdown($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getDispositionBreakdownProperty(): array
    {
        return app(LossAnalyticsService::class)->getDispositionBreakdown($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getProblemProductsProperty(): array
    {
        return app(LossAnalyticsService::class)->getProblemProducts($this->dateFrom, $this->dateTo, $this->locationFilter, 20);
    }

    public function getReturnsByLocationProperty(): array
    {
        return app(LossAnalyticsService::class)->getReturnsByLocation($this->dateFrom, $this->dateTo);
    }

    public function getShopsProperty()
    {
        return Shop::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.owner.reports.loss-analysis');
    }
}
