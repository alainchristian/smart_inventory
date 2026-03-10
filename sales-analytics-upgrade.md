# Sales Analytics Upgrade — Smart Inventory
## Implementation Guide for Claude Code

> **Scope:** Replace 3 existing files. No migrations, no schema changes, no new routes required.  
> **Stack:** Laravel 11 · Livewire 3 · PostgreSQL · Alpine.js · ApexCharts (CDN)

---

## Overview of Changes

| File | Action | What changed |
|---|---|---|
| `app/Services/Analytics/SalesAnalyticsService.php` | **Replace** | +5 new methods, enhanced existing methods with period-over-period deltas |
| `app/Livewire/Owner/Reports/SalesAnalytics.php` | **Replace** | New computed properties, helper methods, `setTab()` action |
| `resources/views/livewire/owner/reports/sales-analytics.blade.php` | **Replace** | Complete UI redesign — 6 KPI cards, 5 charts, price override audit panel |

---

## Step 1 — After writing the files, clear caches

```bash
php artisan view:clear
php artisan cache:clear
```

No other artisan commands are needed. The service uses `Cache::remember()` with 15-minute TTL — clearing the cache ensures the new query results are fetched immediately on first load.

---

## File 1 of 3

**Path:** `app/Services/Analytics/SalesAnalyticsService.php`  
**Action:** Overwrite the existing file completely.

```php
<?php

namespace App\Services\Analytics;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SalesAnalyticsService
{
    /**
     * Get revenue KPIs for a date range
     */
    public function getRevenueKpis(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_revenue_kpis_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $locationFilter) {
            $query = Sale::notVoided()
                ->whereBetween('sale_date', [$dateFrom, $dateTo]);

            $query = $this->applyLocationFilter($query, $locationFilter);

            $currentRevenue      = $query->sum('total');
            $currentTransactions = $query->count();
            $currentDiscount     = $query->sum('discount');

            // Calculate previous period for growth
            $daysDiff         = now()->parse($dateFrom)->diffInDays(now()->parse($dateTo));
            $previousDateFrom = now()->parse($dateFrom)->subDays($daysDiff + 1)->format('Y-m-d');
            $previousDateTo   = now()->parse($dateFrom)->subDay()->format('Y-m-d');

            $previousQuery = Sale::notVoided()
                ->whereBetween('sale_date', [$previousDateFrom, $previousDateTo]);
            $previousQuery = $this->applyLocationFilter($previousQuery, $locationFilter);

            $previousRevenue      = $previousQuery->sum('total');
            $previousTransactions = $previousQuery->count();

            $avgValue  = $currentTransactions > 0 ? $currentRevenue / $currentTransactions : 0;
            $prevAvg   = $previousTransactions > 0 ? $previousRevenue / $previousTransactions : 0;

            $revenueGrowth      = $previousRevenue > 0
                ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;
            $transactionGrowth  = $previousTransactions > 0
                ? (($currentTransactions - $previousTransactions) / $previousTransactions) * 100 : 0;
            $avgValueGrowth     = $prevAvg > 0
                ? (($avgValue - $prevAvg) / $prevAvg) * 100 : 0;

            return [
                'total_revenue'         => (int) $currentRevenue,
                'transactions_count'    => (int) $currentTransactions,
                'avg_transaction_value' => (int) $avgValue,
                'total_discount'        => (int) $currentDiscount,
                'growth_percentage'     => round($revenueGrowth, 1),
                'transaction_growth'    => round($transactionGrowth, 1),
                'avg_value_growth'      => round($avgValueGrowth, 1),
                'previous_revenue'      => (int) $previousRevenue,
                'previous_transactions' => (int) $previousTransactions,
            ];
        });
    }

    /**
     * Get total items sold in period
     */
    public function getItemsSoldKpi(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_items_sold_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $locationFilter) {
            $daysDiff         = now()->parse($dateFrom)->diffInDays(now()->parse($dateTo));
            $previousDateFrom = now()->parse($dateFrom)->subDays($daysDiff + 1)->format('Y-m-d');
            $previousDateTo   = now()->parse($dateFrom)->subDay()->format('Y-m-d');

            $currentQuery = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [$dateFrom, $dateTo]);

            if ($locationFilter !== 'all') {
                $currentQuery = $this->applyLocationFilterToJoin($currentQuery, $locationFilter);
            }

            $currentItems = $currentQuery->sum('sale_items.quantity_sold');

            $previousQuery = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [$previousDateFrom, $previousDateTo]);

            if ($locationFilter !== 'all') {
                $previousQuery = $this->applyLocationFilterToJoin($previousQuery, $locationFilter);
            }

            $previousItems = $previousQuery->sum('sale_items.quantity_sold');

            $growth = $previousItems > 0
                ? (($currentItems - $previousItems) / $previousItems) * 100 : 0;

            return [
                'items_sold'     => (int) $currentItems,
                'previous_items' => (int) $previousItems,
                'growth'         => round($growth, 1),
            ];
        });
    }

    /**
     * Get price override / modification stats
     */
    public function getPriceOverrideStats(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_price_overrides_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $locationFilter) {
            // Sales with price overrides
            $overrideQuery = Sale::notVoided()
                ->where('has_price_override', true)
                ->whereBetween('sale_date', [$dateFrom, $dateTo]);
            $overrideQuery = $this->applyLocationFilter($overrideQuery, $locationFilter);
            $overrideSalesCount = $overrideQuery->count();

            // Total discounted amount from modified items
            $discountQuery = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereNull('sales.voided_at')
                ->where('sale_items.price_was_modified', true)
                ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
                ->selectRaw('
                    COUNT(DISTINCT sales.id) as sales_count,
                    COUNT(*) as items_count,
                    SUM((sale_items.original_unit_price - sale_items.actual_unit_price) * sale_items.quantity_sold) as total_discount_given
                ');

            if ($locationFilter !== 'all') {
                $discountQuery = $this->applyLocationFilterToJoin($discountQuery, $locationFilter);
            }

            $discountResult = $discountQuery->first();

            // Total sales in period for percentage
            $totalQuery = Sale::notVoided()->whereBetween('sale_date', [$dateFrom, $dateTo]);
            $totalQuery = $this->applyLocationFilter($totalQuery, $locationFilter);
            $totalSales = $totalQuery->count();

            $overrideRate = $totalSales > 0
                ? round(($overrideSalesCount / $totalSales) * 100, 1) : 0;

            return [
                'override_sales_count'  => $overrideSalesCount,
                'override_items_count'  => (int) ($discountResult->items_count ?? 0),
                'total_discount_given'  => (int) ($discountResult->total_discount_given ?? 0),
                'override_rate'         => $overrideRate,
                'total_sales'           => $totalSales,
            ];
        });
    }

    /**
     * Get sale type breakdown (full_box vs individual_items)
     */
    public function getSaleTypeBreakdown(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_sale_type_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $locationFilter) {
            $query = Sale::notVoided()
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
                ->selectRaw('type, COUNT(*) as count, SUM(total) as revenue')
                ->groupBy('type');

            $query = $this->applyLocationFilter($query, $locationFilter);

            $results    = $query->get();
            $totalRev   = $results->sum('revenue');

            return $results->map(function ($item) use ($totalRev) {
                $typeVal = is_object($item->type) ? $item->type->value : $item->type;
                return [
                    'type'          => $typeVal ?? 'unknown',
                    'label'         => match($typeVal) {
                        'full_box'         => 'Full Box',
                        'individual_items' => 'Individual Items',
                        default            => ucfirst($typeVal ?? 'Unknown'),
                    },
                    'count'         => (int) $item->count,
                    'revenue'       => (int) $item->revenue,
                    'revenue_share' => $totalRev > 0 ? round(($item->revenue / $totalRev) * 100, 1) : 0,
                ];
            })->toArray();
        });
    }

    /**
     * Get voided sales stats
     */
    public function getVoidedSalesStats(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_voided_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $locationFilter) {
            $voidedQuery = Sale::whereNotNull('voided_at')
                ->whereBetween('sale_date', [$dateFrom, $dateTo]);
            $voidedQuery = $this->applyLocationFilter($voidedQuery, $locationFilter);

            $voidedCount   = $voidedQuery->count();
            $voidedRevenue = $voidedQuery->sum('total');

            $totalQuery = Sale::whereBetween('sale_date', [$dateFrom, $dateTo]);
            $totalQuery = $this->applyLocationFilter($totalQuery, $locationFilter);
            $totalCount = $totalQuery->count();

            return [
                'voided_count'    => $voidedCount,
                'voided_revenue'  => (int) $voidedRevenue,
                'void_rate'       => $totalCount > 0 ? round(($voidedCount / $totalCount) * 100, 1) : 0,
            ];
        });
    }

    /**
     * Get daily revenue trend for charts
     */
    public function getRevenueTrend(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_revenue_trend_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $locationFilter) {
            $query = Sale::notVoided()
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
                ->selectRaw('DATE(sale_date) as date, SUM(total) as revenue, COUNT(*) as transactions, SUM(discount) as discount_total')
                ->groupBy('date')
                ->orderBy('date');

            $query = $this->applyLocationFilter($query, $locationFilter);

            return $query->get()->map(function ($item) {
                return [
                    'date'           => $item->date,
                    'revenue'        => (int) $item->revenue,
                    'transactions'   => (int) $item->transactions,
                    'discount_total' => (int) $item->discount_total,
                ];
            })->toArray();
        });
    }

    /**
     * Get payment method breakdown
     */
    public function getPaymentMethodBreakdown(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_payment_methods_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $locationFilter) {
            $query = Sale::notVoided()
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
                ->selectRaw('payment_method, SUM(total) as revenue, COUNT(*) as count, AVG(total) as avg_value')
                ->groupBy('payment_method');

            $query = $this->applyLocationFilter($query, $locationFilter);

            $results  = $query->get();
            $totalRev = $results->sum('revenue');

            return $results->map(function ($item) use ($totalRev) {
                $method = $item->payment_method->value ?? $item->payment_method ?? 'Unknown';
                return [
                    'method'        => $method,
                    'label'         => match(strtolower($method)) {
                        'cash'          => 'Cash',
                        'mobile_money'  => 'Mobile Money',
                        'momo'          => 'Mobile Money',
                        'card'          => 'Card',
                        'bank_transfer' => 'Bank Transfer',
                        'credit'        => 'Credit',
                        default         => ucfirst(str_replace('_', ' ', $method)),
                    },
                    'revenue'       => (int) $item->revenue,
                    'count'         => (int) $item->count,
                    'avg_value'     => (int) $item->avg_value,
                    'revenue_share' => $totalRev > 0 ? round(($item->revenue / $totalRev) * 100, 1) : 0,
                ];
            })->sortByDesc('revenue')->values()->toArray();
        });
    }

    /**
     * Get top performing products with revenue share
     */
    public function getTopProducts(string $dateFrom, string $dateTo, ?string $locationFilter = 'all', int $limit = 20): array
    {
        $cacheKey = "analytics_top_products_{$dateFrom}_{$dateTo}_{$locationFilter}_{$limit}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $locationFilter, $limit) {
            $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
                ->selectRaw('
                    products.id,
                    products.name,
                    products.selling_price,
                    SUM(sale_items.quantity_sold) as quantity_sold,
                    SUM(sale_items.line_total) as revenue,
                    COUNT(DISTINCT sales.id) as transaction_count,
                    AVG(sale_items.actual_unit_price) as avg_selling_price
                ')
                ->groupBy('products.id', 'products.name', 'products.selling_price')
                ->orderByDesc('revenue')
                ->limit($limit);

            if ($locationFilter !== 'all') {
                $query = $this->applyLocationFilterToJoin($query, $locationFilter);
            }

            $results  = $query->get();
            $totalRev = $results->sum('revenue');

            return $results->map(function ($item) use ($totalRev) {
                return [
                    'product_id'        => $item->id,
                    'product_name'      => $item->name,
                    'quantity_sold'     => (int) $item->quantity_sold,
                    'revenue'           => (int) $item->revenue,
                    'transaction_count' => (int) $item->transaction_count,
                    'avg_selling_price' => (int) $item->avg_selling_price,
                    'revenue_share'     => $totalRev > 0 ? round(($item->revenue / $totalRev) * 100, 1) : 0,
                ];
            })->toArray();
        });
    }

    /**
     * Get shop performance comparison
     */
    public function getShopPerformance(string $dateFrom, string $dateTo): array
    {
        $cacheKey = "analytics_shop_performance_{$dateFrom}_{$dateTo}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo) {
            // Current period
            $current = Sale::notVoided()
                ->join('shops', 'sales.shop_id', '=', 'shops.id')
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
                ->selectRaw('
                    shops.id,
                    shops.name,
                    SUM(sales.total) as revenue,
                    COUNT(sales.id) as transactions,
                    AVG(sales.total) as avg_transaction,
                    SUM(sales.discount) as total_discount,
                    SUM(CASE WHEN sales.has_price_override = true THEN 1 ELSE 0 END) as override_count
                ')
                ->groupBy('shops.id', 'shops.name')
                ->orderByDesc('revenue')
                ->get();

            $totalRevenue = $current->sum('revenue');

            // Previous period for growth
            $daysDiff         = now()->parse($dateFrom)->diffInDays(now()->parse($dateTo));
            $previousDateFrom = now()->parse($dateFrom)->subDays($daysDiff + 1)->format('Y-m-d');
            $previousDateTo   = now()->parse($dateFrom)->subDay()->format('Y-m-d');

            $previous = Sale::notVoided()
                ->whereBetween('sale_date', [$previousDateFrom, $previousDateTo])
                ->selectRaw('shop_id, SUM(total) as revenue')
                ->groupBy('shop_id')
                ->get()
                ->keyBy('shop_id');

            return $current->map(function ($item) use ($previous, $totalRevenue) {
                $prevRev = $previous[$item->id]->revenue ?? 0;
                $growth  = $prevRev > 0
                    ? round((($item->revenue - $prevRev) / $prevRev) * 100, 1) : null;

                return [
                    'shop_id'          => $item->id,
                    'shop_name'        => $item->name,
                    'revenue'          => (int) $item->revenue,
                    'transactions'     => (int) $item->transactions,
                    'avg_transaction'  => (int) $item->avg_transaction,
                    'total_discount'   => (int) $item->total_discount,
                    'override_count'   => (int) $item->override_count,
                    'revenue_share'    => $totalRevenue > 0 ? round(($item->revenue / $totalRevenue) * 100, 1) : 0,
                    'growth'           => $growth,
                    'previous_revenue' => (int) $prevRev,
                ];
            })->toArray();
        });
    }

    /**
     * Get sales distribution by hour of day
     */
    public function getSalesByHour(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_sales_by_hour_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $locationFilter) {
            $query = Sale::notVoided()
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
                ->selectRaw('EXTRACT(HOUR FROM sale_date) as hour, COUNT(*) as count, SUM(total) as revenue')
                ->groupBy('hour')
                ->orderBy('hour');

            $query = $this->applyLocationFilter($query, $locationFilter);

            $results = $query->get()->keyBy('hour');

            // Fill all 24 hours
            $filled = [];
            for ($h = 0; $h < 24; $h++) {
                $filled[] = [
                    'hour'    => $h,
                    'label'   => sprintf('%02d:00', $h),
                    'count'   => (int) ($results[$h]->count ?? 0),
                    'revenue' => (int) ($results[$h]->revenue ?? 0),
                ];
            }

            return $filled;
        });
    }

    /**
     * Get daily revenue sparkline data (compact, last N days)
     */
    public function getRevenueSparkline(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_sparkline_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $locationFilter) {
            $query = Sale::notVoided()
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
                ->selectRaw('DATE(sale_date) as date, SUM(total) as revenue')
                ->groupBy('date')
                ->orderBy('date');

            $query = $this->applyLocationFilter($query, $locationFilter);

            return $query->get()->pluck('revenue')->map(fn ($v) => (int) $v)->toArray();
        });
    }

    /**
     * Apply location filter to query
     */
    private function applyLocationFilter($query, ?string $locationFilter)
    {
        if (!$locationFilter || $locationFilter === 'all') {
            return $query;
        }

        if (str_starts_with($locationFilter, 'shop:')) {
            $shopId = (int) explode(':', $locationFilter)[1];
            return $query->where('shop_id', $shopId);
        }

        return $query;
    }

    /**
     * Apply location filter to joined query
     */
    private function applyLocationFilterToJoin($query, ?string $locationFilter)
    {
        if (!$locationFilter || $locationFilter === 'all') {
            return $query;
        }

        if (str_starts_with($locationFilter, 'shop:')) {
            $shopId = (int) explode(':', $locationFilter)[1];
            return $query->where('sales.shop_id', $shopId);
        }

        return $query;
    }
}
```

---

## File 2 of 3

**Path:** `app/Livewire/Owner/Reports/SalesAnalytics.php`  
**Action:** Overwrite the existing file completely.

```php
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
    public $activeTab      = 'overview'; // overview | products | shops | hours

    protected $queryString = ['dateFrom', 'dateTo', 'locationFilter', 'activeTab'];

    public function mount()
    {
        if (!auth()->user()->isOwner()) {
            abort(403, 'Unauthorized access.');
        }

        $this->dateFrom = $this->dateFrom ?? now()->subDays(30)->format('Y-m-d');
        $this->dateTo   = $this->dateTo   ?? now()->format('Y-m-d');
    }

    public function updatedDateFrom(): void { $this->validateDates(); }
    public function updatedDateTo(): void   { $this->validateDates(); }

    public function validateDates(): void
    {
        if ($this->dateFrom > $this->dateTo) {
            $this->dateTo = $this->dateFrom;
        }
    }

    public function setDateRange(string $range): void
    {
        $this->dateTo = now()->format('Y-m-d');

        $this->dateFrom = match ($range) {
            'today'   => now()->format('Y-m-d'),
            'week'    => now()->subDays(6)->format('Y-m-d'),
            'month'   => now()->subDays(29)->format('Y-m-d'),
            'quarter' => now()->subDays(89)->format('Y-m-d'),
            'year'    => now()->subDays(364)->format('Y-m-d'),
            default   => now()->subDays(29)->format('Y-m-d'),
        };
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ── Computed properties ──────────────────────────────────────────────────

    public function getRevenueKpisProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getRevenueKpis($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getItemsSoldKpiProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getItemsSoldKpi($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getPriceOverrideStatsProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getPriceOverrideStats($this->dateFrom, $this->dateTo, $this->locationFilter);
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

    public function getTopProductsProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getTopProducts($this->dateFrom, $this->dateTo, $this->locationFilter, 20);
    }

    public function getShopPerformanceProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getShopPerformance($this->dateFrom, $this->dateTo);
    }

    public function getSalesByHourProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getSalesByHour($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getShopsProperty()
    {
        return Shop::orderBy('name')->get();
    }

    // ── Helpers for view ─────────────────────────────────────────────────────

    public function getActiveDateRangeLabel(): string
    {
        $from = now()->parse($this->dateFrom);
        $to   = now()->parse($this->dateTo);

        if ($from->isSameDay($to)) {
            return $from->format('M d, Y');
        }

        if ($from->isSameYear($to)) {
            return $from->format('M d') . ' – ' . $to->format('M d, Y');
        }

        return $from->format('M d, Y') . ' – ' . $to->format('M d, Y');
    }

    public function getSelectedShopNameProperty(): string
    {
        if ($this->locationFilter === 'all') {
            return 'All Shops';
        }

        if (str_starts_with($this->locationFilter, 'shop:')) {
            $shopId = (int) explode(':', $this->locationFilter)[1];
            $shop   = $this->shops->firstWhere('id', $shopId);
            return $shop?->name ?? 'Selected Shop';
        }

        return 'All Shops';
    }

    public function render()
    {
        return view('livewire.owner.reports.sales-analytics');
    }
}
```

---

## File 3 of 3

**Path:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`  
**Action:** Overwrite the existing file completely.

```blade
<div
    x-data="{
        chartsReady: false,
        revenueTrendChart: null,
        paymentChart: null,
        shopChart: null,
        hoursChart: null,
        saleTypeChart: null,
        initCharts() {
            this.$nextTick(() => {
                this.renderRevenueTrend();
                this.renderPaymentChart();
                this.renderShopChart();
                this.renderHoursChart();
                this.renderSaleTypeChart();
                this.chartsReady = true;
            });
        },
        destroyCharts() {
            [this.revenueTrendChart, this.paymentChart, this.shopChart, this.hoursChart, this.saleTypeChart]
                .forEach(c => { if (c) { try { c.destroy(); } catch(e) {} } });
        },
        renderRevenueTrend() {
            const el = document.getElementById('revenueTrendChart');
            if (!el) return;
            if (this.revenueTrendChart) { try { this.revenueTrendChart.destroy(); } catch(e) {} }
            const data = @js($this->revenueTrend);
            this.revenueTrendChart = new ApexCharts(el, {
                series: [
                    { name: 'Revenue (RWF)', type: 'area', data: data.map(d => ({ x: new Date(d.date).getTime(), y: d.revenue })) },
                    { name: 'Transactions', type: 'bar', data: data.map(d => ({ x: new Date(d.date).getTime(), y: d.transactions })) }
                ],
                chart: { height: 320, type: 'line', toolbar: { show: false }, fontFamily: 'inherit', background: 'transparent', animations: { enabled: true, speed: 600 } },
                stroke: { curve: 'smooth', width: [3, 0] },
                fill: { type: ['gradient', 'solid'], gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 95] } },
                colors: ['#2563eb', '#93c5fd'],
                plotOptions: { bar: { columnWidth: '60%', borderRadius: 3 } },
                xaxis: { type: 'datetime', labels: { style: { colors: '#94a3b8', fontSize: '11px' }, datetimeFormatter: { day: 'MMM dd' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: [
                    { seriesName: 'Revenue (RWF)', labels: { style: { colors: '#94a3b8', fontSize: '11px' }, formatter: v => 'RWF ' + Intl.NumberFormat().format(Math.round(v)) } },
                    { seriesName: 'Transactions', opposite: true, labels: { style: { colors: '#93c5fd', fontSize: '11px' }, formatter: v => Math.round(v) + ' txn' } }
                ],
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                tooltip: { theme: 'light', x: { format: 'MMM dd, yyyy' }, y: [{ formatter: v => 'RWF ' + Intl.NumberFormat().format(Math.round(v)) }, { formatter: v => Math.round(v) + ' transactions' }] },
                legend: { show: true, position: 'top', horizontalAlign: 'right', fontSize: '12px', labels: { colors: '#64748b' } },
                markers: { size: [0, 0] }
            });
            this.revenueTrendChart.render();
        },
        renderPaymentChart() {
            const el = document.getElementById('paymentMethodsChart');
            if (!el) return;
            if (this.paymentChart) { try { this.paymentChart.destroy(); } catch(e) {} }
            const data = @js($this->paymentMethods);
            this.paymentChart = new ApexCharts(el, {
                series: data.map(d => d.revenue),
                chart: { type: 'donut', height: 260, fontFamily: 'inherit', background: 'transparent', animations: { speed: 600 } },
                labels: data.map(d => d.label),
                colors: ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'],
                plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total Sales', fontSize: '12px', color: '#64748b', formatter: () => 'RWF ' + Intl.NumberFormat().format(data.reduce((s,d) => s+d.revenue, 0)) } } } } },
                dataLabels: { enabled: false },
                legend: { show: false },
                tooltip: { y: { formatter: v => 'RWF ' + Intl.NumberFormat().format(Math.round(v)) } },
                stroke: { width: 0 }
            });
            this.paymentChart.render();
        },
        renderShopChart() {
            const el = document.getElementById('shopPerformanceChart');
            if (!el) return;
            if (this.shopChart) { try { this.shopChart.destroy(); } catch(e) {} }
            const data = @js($this->shopPerformance);
            this.shopChart = new ApexCharts(el, {
                series: [
                    { name: 'Revenue', data: data.map(d => d.revenue) },
                    { name: 'Transactions', data: data.map(d => d.transactions) }
                ],
                chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit', background: 'transparent' },
                plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4, dataLabels: { position: 'top' } } },
                colors: ['#2563eb', '#93c5fd'],
                xaxis: { categories: data.map(d => d.shop_name), labels: { style: { colors: '#94a3b8', fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: [
                    { seriesName: 'Revenue', labels: { style: { colors: '#94a3b8', fontSize: '11px' }, formatter: v => 'RWF ' + Intl.NumberFormat('en', { notation: 'compact' }).format(v) } },
                    { seriesName: 'Transactions', opposite: true, labels: { style: { colors: '#93c5fd', fontSize: '11px' }, formatter: v => Math.round(v) } }
                ],
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                tooltip: { y: [{ formatter: v => 'RWF ' + Intl.NumberFormat().format(Math.round(v)) }, { formatter: v => Math.round(v) + ' transactions' }] },
                legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px', labels: { colors: '#64748b' } },
                dataLabels: { enabled: false }
            });
            this.shopChart.render();
        },
        renderHoursChart() {
            const el = document.getElementById('salesByHourChart');
            if (!el) return;
            if (this.hoursChart) { try { this.hoursChart.destroy(); } catch(e) {} }
            const data = @js($this->salesByHour);
            const maxCount = Math.max(...data.map(d => d.count));
            this.hoursChart = new ApexCharts(el, {
                series: [{ name: 'Transactions', data: data.map(d => d.count) }],
                chart: { type: 'bar', height: 220, toolbar: { show: false }, fontFamily: 'inherit', background: 'transparent', sparkline: { enabled: false } },
                plotOptions: { bar: { columnWidth: '75%', borderRadius: 3, distributed: true } },
                colors: data.map(d => d.count === maxCount ? '#2563eb' : d.count >= maxCount * 0.7 ? '#60a5fa' : d.count >= maxCount * 0.4 ? '#93c5fd' : '#dbeafe'),
                xaxis: { categories: data.map(d => d.label), labels: { rotate: 0, style: { colors: '#94a3b8', fontSize: '10px' }, formatter: (v, i) => (i % 3 === 0 ? v : '') }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { show: false } },
                grid: { show: false },
                tooltip: { y: { formatter: v => v + ' transactions' } },
                legend: { show: false },
                dataLabels: { enabled: false }
            });
            this.hoursChart.render();
        },
        renderSaleTypeChart() {
            const el = document.getElementById('saleTypeChart');
            if (!el) return;
            if (this.saleTypeChart) { try { this.saleTypeChart.destroy(); } catch(e) {} }
            const data = @js($this->saleTypeBreakdown);
            this.saleTypeChart = new ApexCharts(el, {
                series: data.map(d => d.revenue),
                chart: { type: 'donut', height: 200, fontFamily: 'inherit', background: 'transparent', animations: { speed: 600 } },
                labels: data.map(d => d.label),
                colors: ['#2563eb', '#10b981'],
                plotOptions: { pie: { donut: { size: '60%' } } },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%', style: { fontSize: '11px' } },
                legend: { show: false },
                tooltip: { y: { formatter: v => 'RWF ' + Intl.NumberFormat().format(Math.round(v)) } },
                stroke: { width: 0 }
            });
            this.saleTypeChart.render();
        }
    }"
    x-init="
        initCharts();
        Livewire.on('charts-refresh', () => {
            destroyCharts();
            $nextTick(() => initCharts());
        });
    "
    wire:key="sales-analytics-{{ $dateFrom }}-{{ $dateTo }}-{{ $locationFilter }}"
    class="min-h-screen bg-slate-50"
>
    {{-- ══════════════════════════════════════════════════
         HEADER BAND
    ══════════════════════════════════════════════════ --}}
    <div class="bg-white border-b border-slate-200 px-6 py-4 mb-6 sticky top-0 z-20 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            {{-- Title --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 leading-tight">Sales Analytics</h1>
                    <p class="text-xs text-slate-500">
                        {{ $this->getActiveDateRangeLabel() }}
                        @if($this->locationFilter !== 'all')
                            &nbsp;·&nbsp; {{ $this->selectedShopName }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- Controls --}}
            <div class="flex flex-wrap items-center gap-2">

                {{-- Location --}}
                <select wire:model.live="locationFilter"
                        class="h-8 px-3 text-xs border border-slate-300 rounded-lg bg-white text-slate-700
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Shops</option>
                    @foreach($this->shops as $shop)
                        <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>

                {{-- Quick range buttons --}}
                @php
                    $ranges = [
                        ['today',   'Today'],
                        ['week',    '7D'],
                        ['month',   '30D'],
                        ['quarter', '90D'],
                        ['year',    '1Y'],
                    ];
                @endphp
                <div class="flex rounded-lg border border-slate-300 overflow-hidden bg-white">
                    @foreach($ranges as [$key, $label])
                        <button wire:click="setDateRange('{{ $key }}')"
                                class="px-3 h-8 text-xs font-medium transition-colors border-r border-slate-200 last:border-r-0
                                       {{ ($key === 'today'   && $dateFrom === now()->format('Y-m-d') && $dateTo === now()->format('Y-m-d'))
                                        || ($key === 'week'    && $dateFrom === now()->subDays(6)->format('Y-m-d'))
                                        || ($key === 'month'   && $dateFrom === now()->subDays(29)->format('Y-m-d'))
                                        || ($key === 'quarter' && $dateFrom === now()->subDays(89)->format('Y-m-d'))
                                        || ($key === 'year'    && $dateFrom === now()->subDays(364)->format('Y-m-d'))
                                            ? 'bg-blue-600 text-white'
                                            : 'text-slate-600 hover:bg-slate-50' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Custom date range --}}
                <div class="flex items-center gap-1">
                    <input type="date" wire:model.live="dateFrom"
                           class="h-8 px-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="text-slate-400 text-xs">→</span>
                    <input type="date" wire:model.live="dateTo"
                           class="h-8 px-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 pb-8">

        {{-- ══════════════════════════════════════════════════
             KPI STRIP — 6 cards
        ══════════════════════════════════════════════════ --}}
        @php
            $kpis  = $this->revenueKpis;
            $items = $this->itemsSoldKpi;
            $po    = $this->priceOverrideStats;
            $void  = $this->voidedSalesStats;
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">

            {{-- Revenue --}}
            <div class="col-span-2 md:col-span-1 bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Revenue</span>
                    <span @class([
                        'inline-flex items-center gap-0.5 text-xs font-bold px-1.5 py-0.5 rounded',
                        'bg-emerald-50 text-emerald-700' => $kpis['growth_percentage'] >= 0,
                        'bg-red-50 text-red-700'         => $kpis['growth_percentage'] < 0,
                    ])>
                        @if($kpis['growth_percentage'] >= 0)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        @endif
                        {{ abs($kpis['growth_percentage']) }}%
                    </span>
                </div>
                <p class="text-2xl font-extrabold text-slate-900 leading-none">
                    {{ number_format($kpis['total_revenue'] / 1000, 0) }}K
                </p>
                <p class="text-xs text-slate-400 mt-1 font-mono">RWF {{ number_format($kpis['total_revenue']) }}</p>
                <p class="text-xs text-slate-400 mt-1">vs RWF {{ number_format($kpis['previous_revenue']) }} prior</p>
            </div>

            {{-- Transactions --}}
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Transactions</span>
                    <span @class([
                        'inline-flex items-center gap-0.5 text-xs font-bold px-1.5 py-0.5 rounded',
                        'bg-emerald-50 text-emerald-700' => $kpis['transaction_growth'] >= 0,
                        'bg-red-50 text-red-700'         => $kpis['transaction_growth'] < 0,
                    ])>
                        @if($kpis['transaction_growth'] >= 0)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        @endif
                        {{ abs($kpis['transaction_growth']) }}%
                    </span>
                </div>
                <p class="text-2xl font-extrabold text-slate-900 leading-none">
                    {{ number_format($kpis['transactions_count']) }}
                </p>
                <p class="text-xs text-slate-400 mt-1">sales recorded</p>
                <p class="text-xs text-slate-400 mt-1">vs {{ number_format($kpis['previous_transactions']) }} prior</p>
            </div>

            {{-- Avg Order Value --}}
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Avg Order</span>
                    <span @class([
                        'inline-flex items-center gap-0.5 text-xs font-bold px-1.5 py-0.5 rounded',
                        'bg-emerald-50 text-emerald-700' => $kpis['avg_value_growth'] >= 0,
                        'bg-red-50 text-red-700'         => $kpis['avg_value_growth'] < 0,
                    ])>
                        @if($kpis['avg_value_growth'] >= 0)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        @endif
                        {{ abs($kpis['avg_value_growth']) }}%
                    </span>
                </div>
                <p class="text-2xl font-extrabold text-slate-900 leading-none">
                    {{ number_format($kpis['avg_transaction_value'] / 1000, 1) }}K
                </p>
                <p class="text-xs text-slate-400 mt-1 font-mono">RWF {{ number_format($kpis['avg_transaction_value']) }}</p>
                <p class="text-xs text-slate-400 mt-1">per transaction</p>
            </div>

            {{-- Items Sold --}}
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Items Sold</span>
                    <span @class([
                        'inline-flex items-center gap-0.5 text-xs font-bold px-1.5 py-0.5 rounded',
                        'bg-emerald-50 text-emerald-700' => $items['growth'] >= 0,
                        'bg-red-50 text-red-700'         => $items['growth'] < 0,
                    ])>
                        @if($items['growth'] >= 0)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        @endif
                        {{ abs($items['growth']) }}%
                    </span>
                </div>
                <p class="text-2xl font-extrabold text-slate-900 leading-none">
                    {{ number_format($items['items_sold']) }}
                </p>
                <p class="text-xs text-slate-400 mt-1">units sold</p>
                <p class="text-xs text-slate-400 mt-1">vs {{ number_format($items['previous_items']) }} prior</p>
            </div>

            {{-- Price Overrides (Control metric) --}}
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Overrides</span>
                    @if($po['override_rate'] > 15)
                        <span class="inline-flex items-center text-xs font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">
                            ⚠ High
                        </span>
                    @else
                        <span class="inline-flex items-center text-xs font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">
                            {{ $po['override_rate'] }}%
                        </span>
                    @endif
                </div>
                <p class="text-2xl font-extrabold text-slate-900 leading-none">
                    {{ number_format($po['override_sales_count']) }}
                </p>
                <p class="text-xs text-slate-400 mt-1">sales w/ price change</p>
                <p class="text-xs text-{{ $po['total_discount_given'] > 0 ? 'amber' : 'slate' }}-500 mt-1 font-medium">
                    −RWF {{ number_format($po['total_discount_given']) }} given
                </p>
            </div>

            {{-- Voided --}}
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Voided</span>
                    @if($void['void_rate'] > 5)
                        <span class="inline-flex items-center text-xs font-bold px-1.5 py-0.5 rounded bg-red-50 text-red-600">
                            ⚠ {{ $void['void_rate'] }}%
                        </span>
                    @else
                        <span class="inline-flex items-center text-xs font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">
                            {{ $void['void_rate'] }}%
                        </span>
                    @endif
                </div>
                <p class="text-2xl font-extrabold text-slate-900 leading-none">
                    {{ number_format($void['voided_count']) }}
                </p>
                <p class="text-xs text-slate-400 mt-1">voided transactions</p>
                <p class="text-xs text-slate-400 mt-1">RWF {{ number_format($void['voided_revenue']) }} reversed</p>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════
             REVENUE TREND (full width)
        ══════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-6">
            <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Revenue Trend</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daily revenue and transaction volume over the selected period</p>
                </div>
                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-0.5 rounded bg-blue-600 inline-block"></span> Revenue
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-blue-200 inline-block"></span> Transactions
                    </span>
                </div>
            </div>
            <div class="px-2 py-2">
                <div id="revenueTrendChart" style="min-height:320px;"
                     wire:ignore></div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════
             ROW 2: Payment Methods + Sale Types + Price Overrides detail
        ══════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

            {{-- Payment Methods --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-5 pt-4 pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Payment Methods</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Revenue share by payment channel</p>
                </div>
                <div class="px-4 py-3">
                    <div id="paymentMethodsChart" wire:ignore style="height:200px;"></div>
                    {{-- Legend --}}
                    <div class="mt-3 space-y-2">
                        @php $pmColors = ['#2563eb','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4']; @endphp
                        @foreach($this->paymentMethods as $i => $method)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                          style="background:{{ $pmColors[$i % count($pmColors)] }}"></span>
                                    <span class="text-xs text-slate-600">{{ $method['label'] }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-slate-400">{{ $method['count'] }} txn</span>
                                    <span class="text-xs font-semibold text-slate-800 w-12 text-right">{{ $method['revenue_share'] }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Sale Type Breakdown --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-5 pt-4 pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Sale Type Mix</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Full-box vs individual item sales revenue</p>
                </div>
                <div class="px-4 py-3">
                    <div id="saleTypeChart" wire:ignore style="height:200px;"></div>
                    @php $stColors = ['#2563eb','#10b981']; @endphp
                    <div class="mt-3 space-y-2">
                        @foreach($this->saleTypeBreakdown as $i => $type)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full"
                                          style="background:{{ $stColors[$i % 2] }}"></span>
                                    <span class="text-xs text-slate-600">{{ $type['label'] }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-slate-400">{{ number_format($type['count']) }} sales</span>
                                    <span class="text-xs font-semibold text-slate-800 w-14 text-right">RWF {{ number_format($type['revenue'] / 1000, 0) }}K</span>
                                </div>
                            </div>
                        @endforeach
                        @if(empty($this->saleTypeBreakdown))
                            <p class="text-xs text-slate-400 text-center py-4">No data for this period</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Price Override Audit Panel --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-5 pt-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Price Override Audit</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Control & accountability metrics</p>
                        </div>
                        @if($po['override_rate'] > 15)
                            <span class="text-xs font-bold bg-amber-100 text-amber-700 px-2 py-1 rounded-full">⚠ Review</span>
                        @else
                            <span class="text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full">✓ Normal</span>
                        @endif
                    </div>
                </div>
                <div class="px-5 py-4 space-y-4">
                    {{-- Override rate bar --}}
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-slate-500">Override rate</span>
                            <span class="text-xs font-bold text-slate-800">{{ $po['override_rate'] }}%</span>
                        </div>
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $po['override_rate'] > 15 ? 'bg-amber-400' : 'bg-blue-500' }}"
                                 style="width: {{ min($po['override_rate'], 100) }}%"></div>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">{{ $po['override_sales_count'] }} of {{ $po['total_sales'] }} sales had price changes</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-500 mb-1">Modified Items</p>
                            <p class="text-base font-bold text-slate-800">{{ number_format($po['override_items_count']) }}</p>
                        </div>
                        <div class="bg-amber-50 rounded-lg p-3">
                            <p class="text-xs text-amber-600 mb-1">Discount Given</p>
                            <p class="text-base font-bold text-amber-700">
                                RWF {{ number_format($po['total_discount_given'] / 1000, 1) }}K
                            </p>
                        </div>
                    </div>

                    @if($po['override_rate'] > 15)
                        <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            <p class="text-xs text-amber-700 font-medium">
                                Override rate exceeds 15%. Consider reviewing price modification policy with shop managers.
                            </p>
                        </div>
                    @elseif($po['override_rate'] > 0)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                            <p class="text-xs text-blue-700">
                                Price modification activity is within acceptable range.
                            </p>
                        </div>
                    @else
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                            <p class="text-xs text-emerald-700">No price modifications recorded in this period.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════
             ROW 3: Top Products (left wide) + Shop Performance (right)
        ══════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 xl:grid-cols-5 gap-4 mb-6">

            {{-- Top Products Table --}}
            <div class="xl:col-span-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Top Products by Revenue</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Top 20 products — revenue, volume, and share</p>
                    </div>
                    @php $totalProductRevenue = collect($this->topProducts)->sum('revenue'); @endphp
                </div>
                <div class="overflow-y-auto" style="max-height: 480px;">
                    <table class="min-w-full">
                        <thead class="sticky top-0 bg-slate-50 z-10">
                            <tr class="border-b border-slate-200">
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 w-8">#</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500">Product</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500">Qty</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500">Revenue</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 hidden md:table-cell">Txns</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 hidden lg:table-cell w-28">Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($this->topProducts as $index => $product)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2.5">
                                        @if($index === 0)
                                            <span class="text-sm">🥇</span>
                                        @elseif($index === 1)
                                            <span class="text-sm">🥈</span>
                                        @elseif($index === 2)
                                            <span class="text-sm">🥉</span>
                                        @else
                                            <span class="text-xs text-slate-400 font-mono">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <span class="text-xs font-medium text-slate-800">{{ $product['product_name'] }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-xs text-slate-500">
                                        {{ number_format($product['quantity_sold']) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        <span class="text-xs font-bold text-slate-800">
                                            RWF {{ number_format($product['revenue']) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-xs text-slate-400 hidden md:table-cell">
                                        {{ number_format($product['transaction_count']) }}
                                    </td>
                                    <td class="px-4 py-2.5 hidden lg:table-cell">
                                        <div class="flex items-center gap-1.5">
                                            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-blue-500 rounded-full"
                                                     style="width: {{ $product['revenue_share'] }}%"></div>
                                            </div>
                                            <span class="text-xs text-slate-500 w-9 text-right">{{ $product['revenue_share'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-400">
                                        No sales data for the selected period
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Shop Performance Summary --}}
            <div class="xl:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <div class="px-5 pt-4 pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Shop Performance</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Revenue, growth vs prior period</p>
                </div>
                <div class="flex-1 p-4 space-y-3">
                    @php $maxShopRevenue = collect($this->shopPerformance)->max('revenue') ?: 1; @endphp
                    @forelse($this->shopPerformance as $shop)
                        <div class="p-3 rounded-lg border border-slate-100 hover:border-slate-200 transition-colors">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-semibold text-slate-800">{{ $shop['shop_name'] }}</span>
                                @if($shop['growth'] !== null)
                                    <span @class([
                                        'text-xs font-bold',
                                        'text-emerald-600' => $shop['growth'] >= 0,
                                        'text-red-500'     => $shop['growth'] < 0,
                                    ])>
                                        {{ $shop['growth'] >= 0 ? '+' : '' }}{{ $shop['growth'] }}%
                                    </span>
                                @endif
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden mb-2">
                                <div class="h-full bg-blue-500 rounded-full transition-all"
                                     style="width: {{ ($shop['revenue'] / $maxShopRevenue) * 100 }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-slate-500">
                                <span>RWF {{ number_format($shop['revenue']) }}</span>
                                <span>{{ number_format($shop['transactions']) }} txns · avg {{ number_format($shop['avg_transaction'] / 1000, 1) }}K</span>
                            </div>
                            @if($shop['override_count'] > 0)
                                <div class="mt-1.5 text-xs text-amber-600">
                                    {{ $shop['override_count'] }} price override{{ $shop['override_count'] > 1 ? 's' : '' }}
                                    · RWF {{ number_format($shop['total_discount'] / 1000, 1) }}K discount
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="flex items-center justify-center h-32 text-sm text-slate-400">
                            No shop data available
                        </div>
                    @endforelse
                </div>

                {{-- Shop chart --}}
                @if(count($this->shopPerformance) > 1)
                    <div class="border-t border-slate-100 px-2 py-2">
                        <div id="shopPerformanceChart" wire:ignore style="height:250px;"></div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════
             SALES BY HOUR OF DAY
        ══════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="px-5 pt-4 pb-3 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Peak Sales Hours</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Transaction volume by hour of day — identify peak staffing needs</p>
                    </div>
                    @php
                        $peakHour = collect($this->salesByHour)->sortByDesc('count')->first();
                    @endphp
                    @if($peakHour && $peakHour['count'] > 0)
                        <div class="text-right">
                            <span class="text-xs text-slate-500">Peak hour</span>
                            <p class="text-sm font-bold text-blue-600">{{ $peakHour['label'] }}</p>
                            <p class="text-xs text-slate-400">{{ number_format($peakHour['count']) }} transactions</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="px-3 py-3">
                <div id="salesByHourChart" wire:ignore style="height:220px;"></div>
                <div class="flex justify-center gap-6 mt-2">
                    <span class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="w-3 h-3 rounded-sm bg-blue-600 inline-block"></span> Peak
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="w-3 h-3 rounded-sm bg-blue-300 inline-block"></span> High
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="w-3 h-3 rounded-sm bg-blue-100 inline-block"></span> Low
                    </span>
                </div>
            </div>
        </div>

    </div>{{-- /px-6 --}}
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js"></script>
@endpush
```

---

## Post-Install Checklist

- [ ] `php artisan view:clear` — flush compiled Blade templates
- [ ] `php artisan cache:clear` — flush analytics query cache
- [ ] Visit `/owner/reports/sales-analytics` as an Owner account
- [ ] Confirm all 6 KPI cards load with period-over-period badges
- [ ] Confirm Revenue Trend chart renders (dual axis: area + bar)
- [ ] Confirm Payment Methods donut renders with inline legend
- [ ] Confirm Sale Type Mix donut renders
- [ ] Confirm Price Override Audit panel shows correct rate and advisory message
- [ ] Confirm Top Products table shows medal icons for top 3 and share bars
- [ ] Confirm Shop Performance cards show growth % and override warnings
- [ ] Confirm Peak Sales Hours bar chart shows color-coded intensity
- [ ] Test date range buttons: Today / 7D / 30D / 90D / 1Y
- [ ] Test shop filter dropdown — all charts and KPIs should re-render

---

*Generated for Smart Inventory · Laravel 11 · Livewire 3 · PostgreSQL*
