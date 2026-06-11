<?php

namespace App\Livewire\Owner\Reports;

use App\Enums\TransferStatus;
use App\Services\Analytics\TransferAnalyticsService;
use Livewire\Component;

class TransferPerformance extends Component
{
    public string $preset        = 'last_30';
    public string $dateFrom      = '';
    public string $dateTo        = '';
    public ?string $statusFilter = null;
    public string $activeTab     = 'overview';

    protected $queryString = ['preset', 'dateFrom', 'dateTo', 'statusFilter', 'activeTab'];

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

    public function getTransferKpisProperty(): array
    {
        return app(TransferAnalyticsService::class)->getTransferKpis($this->dateFrom, $this->dateTo, $this->statusFilter);
    }

    public function getTransferVolumeTrendProperty(): array
    {
        return app(TransferAnalyticsService::class)->getTransferVolumeTrend($this->dateFrom, $this->dateTo, $this->statusFilter);
    }

    public function getTransferRoutesProperty(): array
    {
        return app(TransferAnalyticsService::class)->getTransferRoutes($this->dateFrom, $this->dateTo);
    }

    public function getStatusDistributionProperty(): array
    {
        return app(TransferAnalyticsService::class)->getStatusDistribution($this->dateFrom, $this->dateTo);
    }

    public function getCompletionTimeDistributionProperty(): array
    {
        return app(TransferAnalyticsService::class)->getCompletionTimeDistribution($this->dateFrom, $this->dateTo);
    }

    public function getMostTransferredProductsProperty(): array
    {
        return app(TransferAnalyticsService::class)->getMostTransferredProducts($this->dateFrom, $this->dateTo, 20);
    }

    public function getRecentDiscrepanciesProperty(): array
    {
        return app(TransferAnalyticsService::class)->getRecentDiscrepancies($this->dateFrom, $this->dateTo, 10);
    }

    public function getWarehouseEfficiencyProperty(): array
    {
        return app(TransferAnalyticsService::class)->getWarehouseEfficiency($this->dateFrom, $this->dateTo);
    }

    public function getTransferStatusesProperty()
    {
        return TransferStatus::cases();
    }

    public function render()
    {
        return view('livewire.owner.reports.transfer-performance');
    }
}
