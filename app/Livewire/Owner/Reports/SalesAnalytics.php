<?php

namespace App\Livewire\Owner\Reports;

use App\Models\Shop;
use App\Services\Analytics\SalesAnalyticsService;
use Livewire\Component;

class SalesAnalytics extends Component
{
    public $dateFrom;
    public $dateTo;
    public $locationFilter = 'all';

    protected $queryString = ['dateFrom', 'dateTo', 'locationFilter'];

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

    public function getRevenueKpisProperty()
    {
        $service = app(SalesAnalyticsService::class);
        return $service->getRevenueKpis($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getRevenueTrendProperty()
    {
        $service = app(SalesAnalyticsService::class);
        return $service->getRevenueTrend($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getPaymentMethodsProperty()
    {
        $service = app(SalesAnalyticsService::class);
        return $service->getPaymentMethodBreakdown($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getTopProductsProperty()
    {
        $service = app(SalesAnalyticsService::class);
        return $service->getTopProducts($this->dateFrom, $this->dateTo, $this->locationFilter, 20);
    }

    public function getShopPerformanceProperty()
    {
        $service = app(SalesAnalyticsService::class);
        return $service->getShopPerformance($this->dateFrom, $this->dateTo);
    }

    public function getSalesByHourProperty()
    {
        $service = app(SalesAnalyticsService::class);
        return $service->getSalesByHour($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getShopsProperty()
    {
        return Shop::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.owner.reports.sales-analytics');
    }
}
