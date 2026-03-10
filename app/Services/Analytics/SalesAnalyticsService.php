<?php

namespace App\Services\Analytics;

use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Shop;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SalesAnalyticsService
{
    // ─────────────────────────────────────────────────────────────────────────
    // Date / cache helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * sale_date is a DATETIME column. Passing plain date strings to whereBetween
     * makes PostgreSQL treat them as midnight, excluding all same-day sales.
     * Always stamp startOfDay / endOfDay.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function parseDateRange(string $dateFrom, string $dateTo): array
    {
        return [
            Carbon::parse($dateFrom)->startOfDay(),
            Carbon::parse($dateTo)->endOfDay(),
        ];
    }

    /**
     * Previous period of equal length, immediately before the current window.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function previousPeriod(string $dateFrom, string $dateTo): array
    {
        $from    = Carbon::parse($dateFrom);
        $to      = Carbon::parse($dateTo);
        $daySpan = $from->diffInDays($to) + 1;

        return [
            $from->copy()->subDays($daySpan)->startOfDay(),
            $from->copy()->subDay()->endOfDay(),
        ];
    }

    /** Short TTL when range includes today; longer for historical ranges. */
    private function cacheTtl(string $dateTo): int
    {
        return Carbon::parse($dateTo)->isToday() ? 60 : 900;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Location filters
    // ─────────────────────────────────────────────────────────────────────────

    private function applyLocationFilter($query, ?string $locationFilter)
    {
        if (!$locationFilter || $locationFilter === 'all') {
            return $query;
        }
        if (str_starts_with($locationFilter, 'shop:')) {
            return $query->where('shop_id', (int) explode(':', $locationFilter)[1]);
        }
        return $query;
    }

    private function applyLocationFilterToJoin($query, ?string $locationFilter)
    {
        if (!$locationFilter || $locationFilter === 'all') {
            return $query;
        }
        if (str_starts_with($locationFilter, 'shop:')) {
            return $query->where('sales.shop_id', (int) explode(':', $locationFilter)[1]);
        }
        return $query;
    }

    private function applyReturnLocationFilter($query, ?string $locationFilter)
    {
        if (!$locationFilter || $locationFilter === 'all') {
            return $query;
        }
        if (str_starts_with($locationFilter, 'shop:')) {
            return $query->where('returns.shop_id', (int) explode(':', $locationFilter)[1]);
        }
        return $query;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXISTING METHODS (datetime-corrected)
    // ─────────────────────────────────────────────────────────────────────────

    public function getRevenueKpis(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_revenue_kpis_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to]         = $this->parseDateRange($dateFrom, $dateTo);
            [$prevFrom, $prevTo] = $this->previousPeriod($dateFrom, $dateTo);

            $q = $this->applyLocationFilter(Sale::notVoided()->whereBetween('sale_date', [$from, $to]), $locationFilter);
            $currentRevenue      = $q->sum('total');
            $currentTransactions = $q->count();
            $currentDiscount     = $q->sum('discount');

            $pq = $this->applyLocationFilter(Sale::notVoided()->whereBetween('sale_date', [$prevFrom, $prevTo]), $locationFilter);
            $previousRevenue      = $pq->sum('total');
            $previousTransactions = $pq->count();

            $avgValue = $currentTransactions > 0 ? $currentRevenue / $currentTransactions : 0;
            $prevAvg  = $previousTransactions > 0 ? $previousRevenue / $previousTransactions : 0;

            return [
                'total_revenue'         => (int) $currentRevenue,
                'transactions_count'    => (int) $currentTransactions,
                'avg_transaction_value' => (int) $avgValue,
                'total_discount'        => (int) $currentDiscount,
                'growth_percentage'     => $previousRevenue > 0 ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1) : 0,
                'transaction_growth'    => $previousTransactions > 0 ? round((($currentTransactions - $previousTransactions) / $previousTransactions) * 100, 1) : 0,
                'avg_value_growth'      => $prevAvg > 0 ? round((($avgValue - $prevAvg) / $prevAvg) * 100, 1) : 0,
                'previous_revenue'      => (int) $previousRevenue,
                'previous_transactions' => (int) $previousTransactions,
            ];
        });
    }

    public function getItemsSoldKpi(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_items_sold_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to]         = $this->parseDateRange($dateFrom, $dateTo);
            [$prevFrom, $prevTo] = $this->previousPeriod($dateFrom, $dateTo);

            $base = fn ($f, $t) => SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [$f, $t]);

            $cq = $base($from, $to);
            if ($locationFilter !== 'all') $cq = $this->applyLocationFilterToJoin($cq, $locationFilter);
            $currentItems = $cq->sum('sale_items.quantity_sold');

            $pq = $base($prevFrom, $prevTo);
            if ($locationFilter !== 'all') $pq = $this->applyLocationFilterToJoin($pq, $locationFilter);
            $previousItems = $pq->sum('sale_items.quantity_sold');

            return [
                'items_sold'     => (int) $currentItems,
                'previous_items' => (int) $previousItems,
                'growth'         => $previousItems > 0 ? round((($currentItems - $previousItems) / $previousItems) * 100, 1) : 0,
            ];
        });
    }

    public function getPriceOverrideStats(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_price_overrides_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            $overrideQ = $this->applyLocationFilter(
                Sale::notVoided()->where('has_price_override', true)->whereBetween('sale_date', [$from, $to]),
                $locationFilter
            );
            $overrideSalesCount = $overrideQ->count();

            $discountQ = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereNull('sales.voided_at')
                ->where('sale_items.price_was_modified', true)
                ->whereBetween('sales.sale_date', [$from, $to])
                ->selectRaw('
                    COUNT(*) as items_count,
                    SUM(CASE
                        WHEN sale_items.is_full_box = true THEN sale_items.original_unit_price - sale_items.actual_unit_price
                        ELSE (sale_items.original_unit_price - sale_items.actual_unit_price) * sale_items.quantity_sold
                    END) as total_discount_given
                ');
            if ($locationFilter !== 'all') $discountQ = $this->applyLocationFilterToJoin($discountQ, $locationFilter);
            $dr = $discountQ->first();

            $totalSales = $this->applyLocationFilter(Sale::notVoided()->whereBetween('sale_date', [$from, $to]), $locationFilter)->count();

            return [
                'override_sales_count' => $overrideSalesCount,
                'override_items_count' => (int) ($dr->items_count ?? 0),
                'total_discount_given' => (int) ($dr->total_discount_given ?? 0),
                'override_rate'        => $totalSales > 0 ? round(($overrideSalesCount / $totalSales) * 100, 1) : 0,
                'total_sales'          => $totalSales,
            ];
        });
    }

    public function getSaleTypeBreakdown(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_sale_type_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);
            $q = $this->applyLocationFilter(
                Sale::notVoided()->whereBetween('sale_date', [$from, $to])->selectRaw('type, COUNT(*) as count, SUM(total) as revenue')->groupBy('type'),
                $locationFilter
            );
            $results  = $q->get();
            $totalRev = $results->sum('revenue');

            return $results->map(function ($item) use ($totalRev) {
                $v = is_object($item->type) ? $item->type->value : $item->type;
                return [
                    'type'          => $v ?? 'unknown',
                    'label'         => match ($v) { 'full_box' => 'Full Box', 'individual_items' => 'Individual Items', default => ucfirst($v ?? 'Unknown') },
                    'count'         => (int) $item->count,
                    'revenue'       => (int) $item->revenue,
                    'revenue_share' => $totalRev > 0 ? round(($item->revenue / $totalRev) * 100, 1) : 0,
                ];
            })->toArray();
        });
    }

    public function getVoidedSalesStats(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_voided_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);
            $vq = $this->applyLocationFilter(Sale::whereNotNull('voided_at')->whereBetween('sale_date', [$from, $to]), $locationFilter);
            $totalQ = $this->applyLocationFilter(Sale::whereBetween('sale_date', [$from, $to]), $locationFilter);
            return [
                'voided_count'   => $vq->count(),
                'voided_revenue' => (int) $vq->sum('total'),
                'void_rate'      => $totalQ->count() > 0 ? round(($vq->count() / $totalQ->count()) * 100, 1) : 0,
            ];
        });
    }

    public function getRevenueTrend(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_revenue_trend_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);
            $q = $this->applyLocationFilter(
                Sale::notVoided()->whereBetween('sale_date', [$from, $to])
                    ->selectRaw('DATE(sale_date) as date, SUM(total) as revenue, COUNT(*) as transactions, SUM(discount) as discount_total')
                    ->groupBy('date')->orderBy('date'),
                $locationFilter
            );
            return $q->get()->map(fn ($i) => [
                'date'           => $i->date,
                'revenue'        => (int) $i->revenue,
                'transactions'   => (int) $i->transactions,
                'discount_total' => (int) $i->discount_total,
            ])->toArray();
        });
    }

    public function getPaymentMethodBreakdown(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_payment_methods_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);
            $q = $this->applyLocationFilter(
                Sale::notVoided()->whereBetween('sale_date', [$from, $to])
                    ->selectRaw('payment_method, SUM(total) as revenue, COUNT(*) as count, AVG(total) as avg_value')
                    ->groupBy('payment_method'),
                $locationFilter
            );
            $results  = $q->get();
            $totalRev = $results->sum('revenue');

            return $results->map(function ($item) use ($totalRev) {
                $m = $item->payment_method->value ?? $item->payment_method ?? 'Unknown';
                return [
                    'method'        => $m,
                    'label'         => match (strtolower($m)) {
                        'cash' => 'Cash', 'mobile_money', 'momo' => 'Mobile Money',
                        'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'credit' => 'Credit',
                        default => ucfirst(str_replace('_', ' ', $m)),
                    },
                    'revenue'       => (int) $item->revenue,
                    'count'         => (int) $item->count,
                    'avg_value'     => (int) $item->avg_value,
                    'revenue_share' => $totalRev > 0 ? round(($item->revenue / $totalRev) * 100, 1) : 0,
                ];
            })->sortByDesc('revenue')->values()->toArray();
        });
    }

    public function getTopProducts(string $dateFrom, string $dateTo, ?string $locationFilter = 'all', int $limit = 20): array
    {
        $cacheKey = "analytics_top_products_{$dateFrom}_{$dateTo}_{$locationFilter}_{$limit}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter, $limit) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);
            $q = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [$from, $to])
                ->selectRaw('
                    products.id,
                    products.name,
                    MIN(products.selling_price) as selling_price,
                    MIN(products.purchase_price) as purchase_price,
                    SUM(sale_items.quantity_sold) as quantity_sold,
                    SUM(sale_items.line_total) as revenue,
                    COUNT(DISTINCT sales.id) as transaction_count,
                    SUM(sale_items.line_total) / NULLIF(SUM(sale_items.quantity_sold), 0) as avg_selling_price,
                    SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as gross_profit
                ')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('revenue')->limit($limit);
            if ($locationFilter !== 'all') $q = $this->applyLocationFilterToJoin($q, $locationFilter);

            $results  = $q->get();
            $totalRev = $results->sum('revenue');

            return $results->map(fn ($item) => [
                'product_id'        => $item->id,
                'product_name'      => $item->name,
                'quantity_sold'     => (int) $item->quantity_sold,
                'revenue'           => (int) $item->revenue,
                'gross_profit'      => (int) $item->gross_profit,
                'margin_pct'        => $item->revenue > 0 ? round(($item->gross_profit / $item->revenue) * 100, 1) : 0,
                'transaction_count' => (int) $item->transaction_count,
                'avg_selling_price' => (int) $item->avg_selling_price,
                'revenue_share'     => $totalRev > 0 ? round(($item->revenue / $totalRev) * 100, 1) : 0,
            ])->toArray();
        });
    }

    public function getShopPerformance(string $dateFrom, string $dateTo): array
    {
        $cacheKey = "analytics_shop_performance_{$dateFrom}_{$dateTo}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo) {
            [$from, $to]         = $this->parseDateRange($dateFrom, $dateTo);
            [$prevFrom, $prevTo] = $this->previousPeriod($dateFrom, $dateTo);

            $current = Sale::notVoided()
                ->join('shops', 'sales.shop_id', '=', 'shops.id')
                ->whereBetween('sale_date', [$from, $to])
                ->selectRaw('shops.id, shops.name,
                    SUM(sales.total) as revenue, COUNT(sales.id) as transactions, AVG(sales.total) as avg_transaction,
                    SUM(sales.discount) as total_discount,
                    SUM(CASE WHEN sales.has_price_override = true THEN 1 ELSE 0 END) as override_count')
                ->groupBy('shops.id', 'shops.name')->orderByDesc('revenue')->get();

            $totalRevenue = $current->sum('revenue');
            $previous     = Sale::notVoided()->whereBetween('sale_date', [$prevFrom, $prevTo])
                ->selectRaw('shop_id, SUM(total) as revenue')->groupBy('shop_id')->get()->keyBy('shop_id');

            return $current->map(function ($item) use ($previous, $totalRevenue) {
                $prevRev = $previous[$item->id]->revenue ?? 0;
                return [
                    'shop_id'          => $item->id,
                    'shop_name'        => $item->name,
                    'revenue'          => (int) $item->revenue,
                    'transactions'     => (int) $item->transactions,
                    'avg_transaction'  => (int) $item->avg_transaction,
                    'total_discount'   => (int) $item->total_discount,
                    'override_count'   => (int) $item->override_count,
                    'revenue_share'    => $totalRevenue > 0 ? round(($item->revenue / $totalRevenue) * 100, 1) : 0,
                    'growth'           => $prevRev > 0 ? round((($item->revenue - $prevRev) / $prevRev) * 100, 1) : null,
                    'previous_revenue' => (int) $prevRev,
                ];
            })->toArray();
        });
    }

    public function getSalesByHour(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_sales_by_hour_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);
            $q = $this->applyLocationFilter(
                Sale::notVoided()->whereBetween('sale_date', [$from, $to])
                    ->selectRaw('EXTRACT(HOUR FROM sale_date) as hour, COUNT(*) as count, SUM(total) as revenue')
                    ->groupBy('hour')->orderBy('hour'),
                $locationFilter
            );
            $results = $q->get()->keyBy('hour');
            $filled  = [];
            for ($h = 0; $h < 24; $h++) {
                $filled[] = ['hour' => $h, 'label' => sprintf('%02d:00', $h), 'count' => (int) ($results[$h]->count ?? 0), 'revenue' => (int) ($results[$h]->revenue ?? 0)];
            }
            return $filled;
        });
    }

    public function getRevenueSparkline(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_sparkline_{$dateFrom}_{$dateTo}_{$locationFilter}";
        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);
            $q = $this->applyLocationFilter(
                Sale::notVoided()->whereBetween('sale_date', [$from, $to])->selectRaw('DATE(sale_date) as date, SUM(total) as revenue')->groupBy('date')->orderBy('date'),
                $locationFilter
            );
            return $q->get()->pluck('revenue')->map(fn ($v) => (int) $v)->toArray();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NEW METHODS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Gross profit KPIs: revenue, cost, gross profit, margin %, net revenue
     * (after returns), with period-over-period growth.
     * Owner-sensitive: purchase_price is used in margin calculations.
     */
    public function getGrossProfitKpis(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_gross_profit_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to]         = $this->parseDateRange($dateFrom, $dateTo);
            [$prevFrom, $prevTo] = $this->previousPeriod($dateFrom, $dateTo);

            // Current period item-level aggregates
            $itemQ = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [$from, $to])
                ->selectRaw('
                    SUM(sale_items.line_total) as revenue,
                    SUM(products.purchase_price * sale_items.quantity_sold) as total_cost,
                    SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as gross_profit
                ');
            if ($locationFilter !== 'all') $itemQ = $this->applyLocationFilterToJoin($itemQ, $locationFilter);
            $cur = $itemQ->first();

            // Returns in period (refund_amount reduces net revenue)
            $returnQ = ReturnModel::whereBetween('processed_at', [$from, $to]);
            if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
                $returnQ->where('shop_id', (int) explode(':', $locationFilter)[1]);
            }
            $totalReturned = $returnQ->sum('refund_amount');

            $revenue     = (int) ($cur->revenue ?? 0);
            $totalCost   = (int) ($cur->total_cost ?? 0);
            $grossProfit = (int) ($cur->gross_profit ?? 0);
            $netRevenue  = $revenue - (int) $totalReturned;
            $marginPct   = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0;

            // Previous period
            $prevQ = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [$prevFrom, $prevTo])
                ->selectRaw('SUM(sale_items.line_total) as revenue, SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as gross_profit');
            if ($locationFilter !== 'all') $prevQ = $this->applyLocationFilterToJoin($prevQ, $locationFilter);
            $prev = $prevQ->first();

            $prevRevenue     = (int) ($prev->revenue ?? 0);
            $prevGrossProfit = (int) ($prev->gross_profit ?? 0);
            $prevMarginPct   = $prevRevenue > 0 ? round(($prevGrossProfit / $prevRevenue) * 100, 1) : 0;

            return [
                'revenue'              => $revenue,
                'total_cost'           => $totalCost,
                'gross_profit'         => $grossProfit,
                'margin_pct'           => $marginPct,
                'net_revenue'          => $netRevenue,
                'total_returned'       => (int) $totalReturned,
                'gross_profit_growth'  => $prevGrossProfit > 0 ? round((($grossProfit - $prevGrossProfit) / $prevGrossProfit) * 100, 1) : 0,
                'revenue_growth'       => $prevRevenue > 0 ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0,
                'margin_delta'         => round($marginPct - $prevMarginPct, 1),
                'prev_gross_profit'    => $prevGrossProfit,
                'prev_margin_pct'      => $prevMarginPct,
            ];
        });
    }

    /**
     * Day-by-day scorecard for the selected period.
     * Each row: date, revenue, transactions, items sold, discounts,
     *           returns count/amount, gross profit, margin %.
     */
    public function getDailyScorecard(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_daily_scorecard_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            // Sale-level daily aggregates
            $salesData = $this->applyLocationFilter(
                Sale::notVoided()->whereBetween('sale_date', [$from, $to])
                    ->selectRaw('DATE(sale_date) as day, SUM(total) as revenue, COUNT(*) as transactions, SUM(discount) as discounts')
                    ->groupBy('day')->orderBy('day'),
                $locationFilter
            )->get()->keyBy('day');

            // Item-level daily aggregates (gross profit requires product join)
            $itemQ = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [$from, $to])
                ->selectRaw('
                    DATE(sales.sale_date) as day,
                    SUM(sale_items.quantity_sold) as items_sold,
                    SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as gross_profit
                ')
                ->groupBy('day');
            if ($locationFilter !== 'all') $itemQ = $this->applyLocationFilterToJoin($itemQ, $locationFilter);
            $itemsData = $itemQ->get()->keyBy('day');

            // Returns per day (by processed_at)
            $returnQ = ReturnModel::whereBetween('processed_at', [$from, $to])
                ->selectRaw("DATE(processed_at) as day, COUNT(*) as returns_count, SUM(refund_amount) as returned_amount");
            if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
                $returnQ->where('shop_id', (int) explode(':', $locationFilter)[1]);
            }
            $returnsData = $returnQ->groupBy('day')->get()->keyBy('day');

            // Fill every calendar day in the range
            $result = [];
            foreach (CarbonPeriod::create($from->toDateString(), $to->toDateString()) as $date) {
                $d       = $date->format('Y-m-d');
                $s       = $salesData[$d]  ?? null;
                $i       = $itemsData[$d]  ?? null;
                $r       = $returnsData[$d] ?? null;
                $rev     = $s ? (int) $s->revenue : 0;
                $gp      = $i ? (int) $i->gross_profit : 0;

                $result[] = [
                    'date'            => $d,
                    'day_label'       => $date->format('D, M d'),
                    'is_today'        => $date->isToday(),
                    'revenue'         => $rev,
                    'transactions'    => $s ? (int) $s->transactions : 0,
                    'items_sold'      => $i ? (int) $i->items_sold : 0,
                    'discounts'       => $s ? (int) $s->discounts : 0,
                    'gross_profit'    => $gp,
                    'margin_pct'      => $rev > 0 ? round(($gp / $rev) * 100, 1) : 0,
                    'returns_count'   => $r ? (int) $r->returns_count : 0,
                    'returned_amount' => $r ? (int) $r->returned_amount : 0,
                    'net_revenue'     => $rev - ($r ? (int) $r->returned_amount : 0),
                ];
            }

            return $result;
        });
    }

    /**
     * Returns impact: total returns, refunded revenue, net revenue,
     * return rate, items returned, top returned products.
     */
    public function getReturnsImpact(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_returns_impact_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            $returnQ = ReturnModel::whereBetween('processed_at', [$from, $to]);
            if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
                $returnQ->where('shop_id', (int) explode(':', $locationFilter)[1]);
            }

            $returnsCount    = $returnQ->count();
            $returnedRevenue = (int) $returnQ->sum('refund_amount');
            $exchangeCount   = (clone $returnQ)->where('is_exchange', true)->count();

            // Items returned
            $itemsReturned = (int) DB::table('return_items')
                ->join('returns', 'return_items.return_id', '=', 'returns.id')
                ->whereBetween('returns.processed_at', [$from, $to])
                ->whereNull('returns.deleted_at')
                ->when($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:'), function ($q) use ($locationFilter) {
                    $q->where('returns.shop_id', (int) explode(':', $locationFilter)[1]);
                })
                ->sum('return_items.quantity_returned');

            // Top returned products
            $topReturnedProducts = DB::table('return_items')
                ->join('returns', 'return_items.return_id', '=', 'returns.id')
                ->join('products', 'return_items.product_id', '=', 'products.id')
                ->whereBetween('returns.processed_at', [$from, $to])
                ->whereNull('returns.deleted_at')
                ->when($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:'), function ($q) use ($locationFilter) {
                    $q->where('returns.shop_id', (int) explode(':', $locationFilter)[1]);
                })
                ->selectRaw('products.name as product_name, SUM(return_items.quantity_returned) as qty_returned, COUNT(DISTINCT returns.id) as return_count')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('qty_returned')
                ->limit(10)
                ->get()
                ->map(fn ($r) => ['product_name' => $r->product_name, 'qty_returned' => (int) $r->qty_returned, 'return_count' => (int) $r->return_count])
                ->toArray();

            // Gross sales for return rate
            $grossRevenue = (int) $this->applyLocationFilter(
                Sale::notVoided()->whereBetween('sale_date', [$from, $to]),
                $locationFilter
            )->sum('total');

            return [
                'returns_count'        => $returnsCount,
                'returned_revenue'     => $returnedRevenue,
                'exchange_count'       => $exchangeCount,
                'refund_only_count'    => $returnsCount - $exchangeCount,
                'items_returned'       => $itemsReturned,
                'gross_revenue'        => $grossRevenue,
                'net_revenue'          => $grossRevenue - $returnedRevenue,
                'return_rate'          => $grossRevenue > 0 ? round(($returnedRevenue / $grossRevenue) * 100, 1) : 0,
                'top_returned_products' => $topReturnedProducts,
            ];
        });
    }

    /**
     * Full price modification audit trail: who changed what, when, on which sale,
     * original vs actual price, discount amount, reason, and approval status.
     */
    public function getPriceAuditLog(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_price_audit_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            $q = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('shops', 'sales.shop_id', '=', 'shops.id')
                ->join('users as seller', 'sales.sold_by', '=', 'seller.id')
                ->leftJoin('users as approver', 'sales.price_override_approved_by', '=', 'approver.id')
                ->whereNull('sales.voided_at')
                ->where('sale_items.price_was_modified', true)
                ->whereBetween('sales.sale_date', [$from, $to])
                ->selectRaw('
                    sales.id as sale_id,
                    sales.sale_date,
                    sales.sale_number,
                    shops.name as shop_name,
                    seller.name as seller_name,
                    products.id as product_id,
                    products.name as product_name,
                    products.purchase_price,
                    products.items_per_box,
                    SUM(sale_items.quantity_sold) as quantity_sold,
                    COUNT(sale_items.id) as line_count,
                    MAX(CASE WHEN sale_items.is_full_box = true THEN 1 ELSE 0 END) as has_full_box,
                    MIN(CASE WHEN sale_items.is_full_box = false THEN 1 ELSE 0 END) as has_item_sale,
                    AVG(sale_items.original_unit_price) as original_unit_price,
                    AVG(sale_items.actual_unit_price) as actual_unit_price,
                    SUM(sale_items.line_total) as line_total,
                    SUM(CASE
                        WHEN sale_items.is_full_box = true THEN sale_items.original_unit_price - sale_items.actual_unit_price
                        ELSE (sale_items.original_unit_price - sale_items.actual_unit_price) * sale_items.quantity_sold
                    END) as total_discount,
                    MAX(sale_items.price_modification_reason) as price_modification_reason,
                    MAX(sale_items.price_modification_reference) as price_modification_reference,
                    approver.name as approved_by_name,
                    sales.price_override_approved_at
                ')
                ->groupBy('sales.id', 'sales.sale_date', 'sales.sale_number', 'shops.name', 'seller.name',
                         'products.id', 'products.name', 'products.purchase_price', 'products.items_per_box',
                         'approver.name', 'sales.price_override_approved_at')
                ->orderByDesc('sales.sale_date');

            if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
                $q->where('sales.shop_id', (int) explode(':', $locationFilter)[1]);
            }

            return $q->get()->map(function ($item) {
                $lineCount = (int) $item->line_count;
                $hasFullBox = (bool) $item->has_full_box;
                $hasItemSale = (bool) $item->has_item_sale;
                $totalItems = (int) $item->quantity_sold;
                $itemsPerBox = (int) $item->items_per_box;

                // Calculate number of boxes (if all sales were full boxes)
                $boxCount = $hasFullBox && $itemsPerBox > 0 ? $lineCount : 0;

                // Format quantity display
                if ($hasFullBox && !$hasItemSale) {
                    // All full box sales
                    $quantityDisplay = $boxCount . ' box' . ($boxCount > 1 ? 'es' : '') . ' (' . $totalItems . ' items)';
                } elseif (!$hasFullBox && $hasItemSale) {
                    // All item sales
                    $quantityDisplay = $totalItems . ' item' . ($totalItems > 1 ? 's' : '');
                } else {
                    // Mixed: boxes + items
                    $quantityDisplay = 'Mixed (' . $totalItems . ' items)';
                }

                // Calculate discount percentage based on total line_total
                $originalTotal = $item->line_total + $item->total_discount;
                $discountPct = $originalTotal > 0
                    ? round(($item->total_discount / $originalTotal) * 100, 1)
                    : 0;

                $lineCost = $item->purchase_price * $item->quantity_sold;
                $margin   = $item->line_total > 0
                    ? round((($item->line_total - $lineCost) / $item->line_total) * 100, 1)
                    : 0;

                return [
                    'sale_date'           => $item->sale_date,
                    'sale_number'         => $item->sale_number,
                    'shop_name'           => $item->shop_name,
                    'seller_name'         => $item->seller_name,
                    'product_name'        => $item->product_name,
                    'quantity_sold'       => $totalItems,
                    'quantity_display'    => $quantityDisplay,
                    'line_count'          => $lineCount,
                    'is_full_box'         => $hasFullBox,
                    'original_unit_price' => (int) $item->original_unit_price,
                    'actual_unit_price'   => (int) $item->actual_unit_price,
                    'total_discount'      => (int) $item->total_discount,
                    'discount_pct'        => $discountPct,
                    'margin_at_sale'      => $margin,
                    'line_total'          => (int) $item->line_total,
                    'reason'              => $item->price_modification_reason,
                    'reference'           => $item->price_modification_reference,
                    'approved_by'         => $item->approved_by_name,
                    'approved_at'         => $item->price_override_approved_at,
                    'is_approved'         => $item->price_override_approved_at !== null,
                ];
            })->toArray();
        });
    }

    /**
     * Per-seller performance: revenue, transactions, avg order, items sold,
     * gross profit (owner only), discounts given, override count, void count.
     */
    public function getSellerPerformance(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_seller_performance_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            // Non-voided sales stats per seller
            $salesQ = Sale::notVoided()
                ->join('users', 'sales.sold_by', '=', 'users.id')
                ->join('shops', 'sales.shop_id', '=', 'shops.id')
                ->whereBetween('sale_date', [$from, $to])
                ->selectRaw('
                    users.id as user_id,
                    users.name as seller_name,
                    shops.name as shop_name,
                    COUNT(sales.id) as transactions,
                    SUM(sales.total) as revenue,
                    AVG(sales.total) as avg_order,
                    SUM(sales.discount) as total_discount,
                    SUM(CASE WHEN sales.has_price_override = true THEN 1 ELSE 0 END) as override_count
                ')
                ->groupBy('users.id', 'users.name', 'shops.name');
            if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
                $salesQ->where('sales.shop_id', (int) explode(':', $locationFilter)[1]);
            }
            $salesStats = $salesQ->get()->keyBy('user_id');

            // Voided sales count per seller
            $voidQ = Sale::whereNotNull('voided_at')
                ->join('users', 'sales.sold_by', '=', 'users.id')
                ->whereBetween('sale_date', [$from, $to])
                ->selectRaw('users.id as user_id, COUNT(*) as void_count')
                ->groupBy('users.id');
            if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
                $voidQ->where('sales.shop_id', (int) explode(':', $locationFilter)[1]);
            }
            $voidStats = $voidQ->get()->keyBy('user_id');

            // Gross profit & items per seller (requires product join)
            $gpQ = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('users', 'sales.sold_by', '=', 'users.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [$from, $to])
                ->selectRaw('
                    users.id as user_id,
                    SUM(sale_items.quantity_sold) as items_sold,
                    SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as gross_profit
                ')
                ->groupBy('users.id');
            if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
                $gpQ->where('sales.shop_id', (int) explode(':', $locationFilter)[1]);
            }
            $gpStats = $gpQ->get()->keyBy('user_id');

            $totalRevenue = $salesStats->sum('revenue');

            return $salesStats->map(function ($s) use ($voidStats, $gpStats, $totalRevenue) {
                $gp  = $gpStats[$s->user_id] ?? null;
                $rev = (int) $s->revenue;
                $gpp = $gp ? (int) $gp->gross_profit : 0;

                return [
                    'user_id'        => $s->user_id,
                    'seller_name'    => $s->seller_name,
                    'shop_name'      => $s->shop_name,
                    'transactions'   => (int) $s->transactions,
                    'revenue'        => $rev,
                    'avg_order'      => (int) $s->avg_order,
                    'items_sold'     => $gp ? (int) $gp->items_sold : 0,
                    'total_discount' => (int) $s->total_discount,
                    'override_count' => (int) $s->override_count,
                    'void_count'     => (int) ($voidStats[$s->user_id]->void_count ?? 0),
                    'gross_profit'   => $gpp,
                    'margin_pct'     => $rev > 0 ? round(($gpp / $rev) * 100, 1) : 0,
                    'revenue_share'  => $totalRevenue > 0 ? round(($rev / $totalRevenue) * 100, 1) : 0,
                ];
            })->sortByDesc('revenue')->values()->toArray();
        });
    }

    /**
     * Customer repeat analysis: unique customers, repeat rate, top customers
     * by revenue, purchase frequency.
     */
    public function getCustomerRepeatAnalysis(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_customer_repeat_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            $q = $this->applyLocationFilter(
                Sale::notVoided()
                    ->whereBetween('sale_date', [$from, $to])
                    ->whereNotNull('customer_name')
                    ->selectRaw('
                        customer_name, customer_phone,
                        COUNT(*) as purchase_count,
                        SUM(total) as total_spent,
                        AVG(total) as avg_order,
                        MIN(sale_date) as first_purchase,
                        MAX(sale_date) as last_purchase
                    ')
                    ->groupBy('customer_name', 'customer_phone')
                    ->orderByDesc('total_spent')
                    ->limit(50),
                $locationFilter
            );

            $customers = $q->get();

            // Walk-in (no customer name) totals
            $walkInQ = $this->applyLocationFilter(
                Sale::notVoided()->whereBetween('sale_date', [$from, $to])->whereNull('customer_name'),
                $locationFilter
            );
            $walkInCount   = $walkInQ->count();
            $walkInRevenue = (int) $walkInQ->sum('total');

            $totalCustomers  = $customers->count();
            $repeatCustomers = $customers->where('purchase_count', '>', 1)->count();
            $repeatRate      = $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 1) : 0;

            return [
                'total_customers'   => $totalCustomers,
                'repeat_customers'  => $repeatCustomers,
                'repeat_rate'       => $repeatRate,
                'walkin_count'      => $walkInCount,
                'walkin_revenue'    => $walkInRevenue,
                'top_customers'     => $customers->take(25)->map(fn ($c) => [
                    'name'           => $c->customer_name,
                    'phone'          => $c->customer_phone,
                    'purchase_count' => (int) $c->purchase_count,
                    'total_spent'    => (int) $c->total_spent,
                    'avg_order'      => (int) $c->avg_order,
                    'first_purchase' => $c->first_purchase,
                    'last_purchase'  => $c->last_purchase,
                    'is_repeat'      => $c->purchase_count > 1,
                ])->toArray(),
            ];
        });
    }
}