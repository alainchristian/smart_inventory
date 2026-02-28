<?php

namespace App\Livewire\Owner\Reports;

use App\Services\Analytics\TransferAnalyticsService;
use App\Enums\TransferStatus;
use Livewire\Component;

class TransferPerformance extends Component
{
    public $dateFrom;
    public $dateTo;
    public $statusFilter = null;

    protected $queryString = ['dateFrom', 'dateTo', 'statusFilter'];

    public function mount()
    {
        // Ensure only owners can access
        if (!auth()->user()->isOwner()) {
            abort(403, 'Unauthorized access.');
        }

        // Set default date range (last 30 days)
        $this->dateFrom = $this->dateFrom ?? now()->subDays(30)->format('Y-m-d');
        $this->dateTo = $this->dateTo ?? now()->format('Y-m-d');
    }

    public function updatedDateFrom()
    {
        $this->validateDates();
    }

    public function updatedDateTo()
    {
        $this->validateDates();
    }

    public function validateDates()
    {
        // Ensure dateFrom is not after dateTo
        if ($this->dateFrom > $this->dateTo) {
            $this->dateTo = $this->dateFrom;
        }
    }

    public function setDateRange($range)
    {
        $this->dateTo = now()->format('Y-m-d');

        switch ($range) {
            case 'today':
                $this->dateFrom = now()->format('Y-m-d');
                break;
            case 'week':
                $this->dateFrom = now()->subDays(7)->format('Y-m-d');
                break;
            case 'month':
                $this->dateFrom = now()->subDays(30)->format('Y-m-d');
                break;
            case 'quarter':
                $this->dateFrom = now()->subDays(90)->format('Y-m-d');
                break;
            case 'year':
                $this->dateFrom = now()->subDays(365)->format('Y-m-d');
                break;
        }
    }

    public function getTransferKpisProperty()
    {
        $service = app(TransferAnalyticsService::class);
        return $service->getTransferKpis($this->dateFrom, $this->dateTo, $this->statusFilter);
    }

    public function getTransferVolumeTrendProperty()
    {
        $service = app(TransferAnalyticsService::class);
        return $service->getTransferVolumeTrend($this->dateFrom, $this->dateTo, $this->statusFilter);
    }

    public function getTransferRoutesProperty()
    {
        $service = app(TransferAnalyticsService::class);
        return $service->getTransferRoutes($this->dateFrom, $this->dateTo);
    }

    public function getStatusDistributionProperty()
    {
        $service = app(TransferAnalyticsService::class);
        return $service->getStatusDistribution($this->dateFrom, $this->dateTo);
    }

    public function getCompletionTimeDistributionProperty()
    {
        $service = app(TransferAnalyticsService::class);
        return $service->getCompletionTimeDistribution($this->dateFrom, $this->dateTo);
    }

    public function getMostTransferredProductsProperty()
    {
        $service = app(TransferAnalyticsService::class);
        return $service->getMostTransferredProducts($this->dateFrom, $this->dateTo, 20);
    }

    public function getRecentDiscrepanciesProperty()
    {
        $service = app(TransferAnalyticsService::class);
        return $service->getRecentDiscrepancies($this->dateFrom, $this->dateTo, 10);
    }

    public function getWarehouseEfficiencyProperty()
    {
        $service = app(TransferAnalyticsService::class);
        return $service->getWarehouseEfficiency($this->dateFrom, $this->dateTo);
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
