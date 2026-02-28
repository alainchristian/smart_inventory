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

            $currentRevenue = $query->sum('total');
            $currentTransactions = $query->count();

            // Calculate previous period for growth
            $daysDiff = now()->parse($dateFrom)->diffInDays(now()->parse($dateTo));
            $previousDateFrom = now()->parse($dateFrom)->subDays($daysDiff)->format('Y-m-d');
            $previousDateTo = now()->parse($dateFrom)->subDay()->format('Y-m-d');

            $previousQuery = Sale::notVoided()
                ->whereBetween('sale_date', [$previousDateFrom, $previousDateTo]);
            $previousQuery = $this->applyLocationFilter($previousQuery, $locationFilter);

            $previousRevenue = $previousQuery->sum('total');

            $avgValue = $currentTransactions > 0 ? $currentRevenue / $currentTransactions : 0;
            $growth = $previousRevenue > 0
                ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
                : 0;

            return [
                'total_revenue' => $currentRevenue,
                'transactions_count' => $currentTransactions,
                'avg_transaction_value' => (int) $avgValue,
                'growth_percentage' => round($growth, 1),
                'previous_revenue' => $previousRevenue,
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
                ->selectRaw('DATE(sale_date) as date, SUM(total) as revenue, COUNT(*) as transactions')
                ->groupBy('date')
                ->orderBy('date');

            $query = $this->applyLocationFilter($query, $locationFilter);

            return $query->get()->map(function ($item) {
                return [
                    'date' => $item->date,
                    'revenue' => $item->revenue,
                    'transactions' => $item->transactions,
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
                ->selectRaw('payment_method, SUM(total) as revenue, COUNT(*) as count')
                ->groupBy('payment_method');

            $query = $this->applyLocationFilter($query, $locationFilter);

            return $query->get()->map(function ($item) {
                return [
                    'method' => $item->payment_method->value ?? 'Unknown',
                    'revenue' => $item->revenue,
                    'count' => $item->count,
                ];
            })->toArray();
        });
    }

    /**
     * Get top performing products
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
                    SUM(sale_items.quantity_sold) as quantity_sold,
                    SUM(sale_items.line_total) as revenue
                ')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('revenue')
                ->limit($limit);

            if ($locationFilter !== 'all') {
                $query = $this->applyLocationFilterToJoin($query, $locationFilter);
            }

            return $query->get()->map(function ($item) {
                return [
                    'product_id' => $item->id,
                    'product_name' => $item->name,
                    'quantity_sold' => $item->quantity_sold,
                    'revenue' => $item->revenue,
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
            return Sale::notVoided()
                ->join('shops', 'sales.shop_id', '=', 'shops.id')
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
                ->selectRaw('
                    shops.id,
                    shops.name,
                    SUM(sales.total) as revenue,
                    COUNT(sales.id) as transactions,
                    AVG(sales.total) as avg_transaction
                ')
                ->groupBy('shops.id', 'shops.name')
                ->orderByDesc('revenue')
                ->get()
                ->map(function ($item) {
                    return [
                        'shop_id' => $item->id,
                        'shop_name' => $item->name,
                        'revenue' => $item->revenue,
                        'transactions' => $item->transactions,
                        'avg_transaction' => (int) $item->avg_transaction,
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

            return $query->get()->map(function ($item) {
                return [
                    'hour' => (int) $item->hour,
                    'count' => $item->count,
                    'revenue' => $item->revenue,
                ];
            })->toArray();
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
