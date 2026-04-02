<?php

namespace App\Livewire\Owner\Reports;

use App\Models\ActivityLog;
use App\Models\Sale;
use App\Models\Shop;
use App\Services\Analytics\SalesAnalyticsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SalesAnalytics extends Component
{
    // ─── Filters ──────────────────────────────────────────────────────────────
    public string $dateFrom    = '';
    public string $dateTo      = '';
    public string $locationFilter = 'all';
    public string $activeTab   = 'overview';   // overview | ledger | audit | sellers | payments | credit

    protected $queryString = [
        'dateFrom'       => ['except' => ''],
        'dateTo'         => ['except' => ''],
        'locationFilter' => ['except' => 'all'],
        'activeTab'      => ['except' => 'overview'],
    ];

    // ─── Lifecycle ────────────────────────────────────────────────────────────
    public function mount(): void
    {
        if (! $this->dateFrom) {
            $this->dateFrom = now()->startOfMonth()->toDateString();
        }
        if (! $this->dateTo) {
            $this->dateTo = now()->toDateString();
        }
    }

    // ─── Actions ──────────────────────────────────────────────────────────────
    public function setDateRange(string $range): void
    {
        $this->dateTo = now()->toDateString();
        $this->dateFrom = match ($range) {
            'today'   => now()->startOfDay()->toDateString(),
            'week'    => now()->startOfWeek()->toDateString(),
            'month'   => now()->startOfMonth()->toDateString(),
            'quarter' => now()->startOfQuarter()->toDateString(),
            'year'    => now()->startOfYear()->toDateString(),
            default   => now()->startOfMonth()->toDateString(),
        };
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ─── Actions ──────────────────────────────────────────────────────────────
    public function approvePriceOverride(int $saleId): void
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isAdmin()) {
            return;
        }

        $sale = Sale::find($saleId);
        if (! $sale || $sale->price_override_approved_at !== null) {
            return; // already approved or not found
        }

        $sale->update([
            'price_override_approved_at' => now(),
            'price_override_approved_by' => $user->id,
        ]);

        ActivityLog::create([
            'user_id'           => $user->id,
            'user_name'         => $user->name,
            'action'            => 'price_override_approved',
            'entity_type'       => 'Sale',
            'entity_id'         => $sale->id,
            'entity_identifier' => $sale->sale_number,
            'details'           => [
                'approved_at' => now()->toDateTimeString(),
                'seller'      => $sale->soldBy?->name,
            ],
            'ip_address'        => request()->ip(),
        ]);

        // Bust the cached audit log for the current filter window
        Cache::forget("analytics_price_audit_{$this->dateFrom}_{$this->dateTo}_{$this->locationFilter}");

        $this->dispatch('notification', [
            'type'    => 'success',
            'message' => "Price override on {$sale->sale_number} approved.",
        ]);
    }

    // ─── Computed: meta ───────────────────────────────────────────────────────
    public function getActiveDateRangeLabelProperty(): string
    {
        $from = Carbon::parse($this->dateFrom);
        $to   = Carbon::parse($this->dateTo);

        // Check if it's today
        if ($from->isToday() && $to->isToday()) {
            return 'Today';
        }

        // Check if it's current week (Monday to today)
        if ($from->isSameDay(now()->startOfWeek()) && $to->isToday()) {
            return 'This Week';
        }

        // Check if it's current month (1st to today)
        if ($from->isSameDay(now()->startOfMonth()) && $to->isToday()) {
            return 'This Month';
        }

        // Check if it's current quarter
        if ($from->isSameDay(now()->startOfQuarter()) && $to->isToday()) {
            return 'This Quarter';
        }

        // Check if it's current year
        if ($from->isSameDay(now()->startOfYear()) && $to->isToday()) {
            return 'This Year';
        }

        // Custom range
        return $from->format('M d') . ' – ' . $to->format('M d, Y');
    }

    public function getShopsProperty()
    {
        return Shop::orderBy('name')->get(['id', 'name']);
    }

    public function getSelectedShopNameProperty(): string
    {
        if ($this->locationFilter === 'all') {
            return 'All Shops';
        }
        if (str_starts_with($this->locationFilter, 'shop:')) {
            $id = (int) explode(':', $this->locationFilter)[1];
            $shop = $this->shops->firstWhere('id', $id);
            return $shop ? $shop->name : 'Shop';
        }
        return 'All Shops';
    }

    // ─── Computed: Overview tab ───────────────────────────────────────────────
    public function getRevenueKpisProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getRevenueKpis($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getGrossProfitKpisProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getGrossProfitKpis($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getItemsSoldKpiProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getItemsSoldKpi($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getReturnsImpactProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getReturnsImpact($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getRevenueTrendProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getRevenueTrend($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getPaymentMethodsProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getPaymentMethodBreakdown($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getShopPerformanceProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getShopPerformance($this->dateFrom, $this->dateTo);
    }

    public function getSaleTypeBreakdownProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getSaleTypeBreakdown($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getVoidedSalesStatsProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getVoidedSalesStats($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getSalesByHourProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getSalesByHour($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getTopProductsProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getTopProducts($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getPriceOverrideStatsProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getPriceOverrideStats($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    // ─── Computed: Daily Scorecard (Overview) ─────────────────────────────────
    public function getDailyScorecardProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getDailyScorecard($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    // ─── Computed: Sellers tab ────────────────────────────────────────────────
    public function getSellerPerformanceProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getSellerPerformance($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getCustomerRepeatAnalysisProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getCustomerRepeatAnalysis($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    // ─── Computed: Audit tab ──────────────────────────────────────────────────
    public function getPriceAuditLogProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getPriceAuditLog($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    // ─── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        return view('livewire.owner.reports.sales-analytics')
            ->layout('layouts.app');
    }
}