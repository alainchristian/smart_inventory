<?php

namespace App\Services\Analytics;

use App\Models\ReturnModel;
use App\Models\ReturnItem;
use App\Models\DamagedGood;
use App\Models\Sale;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LossAnalyticsService
{
    // processed_at / recorded_at are TIMESTAMP columns — always stamp startOfDay/endOfDay.
    private function parseDateRange(string $dateFrom, string $dateTo): array
    {
        return [
            Carbon::parse($dateFrom)->startOfDay(),
            Carbon::parse($dateTo)->endOfDay(),
        ];
    }

    // Use a short TTL when the range includes today so live data appears quickly.
    private function cacheTtl(string $dateTo): int
    {
        return Carbon::parse($dateTo)->isToday() ? 60 : 900;
    }

    /**
     * Get loss KPIs for a date range
     */
    public function getLossKpis(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_loss_kpis_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            // Get returns data
            $returnsQuery = ReturnModel::whereBetween('processed_at', [$from, $to])
                ->where('is_exchange', false); // Only refunds

            $returnsQuery = $this->applyLocationFilter($returnsQuery, $locationFilter);

            $totalRefunds = $returnsQuery->sum('refund_amount');
            $returnsCount = $returnsQuery->count();

            // Damaged goods NOT from returns — return-sourced damage is already captured
            // by refund_amount, counting it again would double the loss figure.
            $damagedQuery = DamagedGood::whereBetween('recorded_at', [$from, $to])
                ->where('source_type', '!=', 'return');
            $damagedQuery = $this->applyLocationFilterForDamaged($damagedQuery, $locationFilter);

            $damagedAgg   = $damagedQuery->selectRaw('COALESCE(SUM(estimated_loss), 0) as total_loss, COUNT(*) as total_count')->first();
            $damagedLoss  = (int) ($damagedAgg->total_loss  ?? 0);
            $damagedCount = (int) ($damagedAgg->total_count ?? 0);

            // Calculate return rate
            $salesQuery = Sale::notVoided()
                ->whereBetween('sale_date', [$from, $to]);
            $salesQuery = $this->applyLocationFilterForSales($salesQuery, $locationFilter);

            $totalSales = $salesQuery->count();
            $returnRate = $totalSales > 0 ? ($returnsCount / $totalSales) * 100 : 0;

            return [
                'total_refunds' => (int) $totalRefunds,
                'return_rate' => round($returnRate, 2),
                'returns_count' => $returnsCount,
                'damaged_loss' => (int) $damagedLoss,
                'damaged_count' => $damagedCount,
                'total_loss' => (int) ($totalRefunds + $damagedLoss),
            ];
        });
    }

    /**
     * Get loss trend over time
     */
    public function getLossTrend(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_loss_trend_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            // Get daily refunds
            $refundsQuery = ReturnModel::whereBetween('processed_at', [$from, $to])
                ->where('is_exchange', false)
                ->selectRaw('DATE(processed_at) as date, SUM(refund_amount) as amount, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date');

            $refundsQuery = $this->applyLocationFilter($refundsQuery, $locationFilter);
            $refunds = $refundsQuery->get()->keyBy('date');

            // Exclude return-sourced damaged goods — already captured by refund_amount above
            $damagedQuery = DamagedGood::whereBetween('recorded_at', [$from, $to])
                ->where('source_type', '!=', 'return')
                ->selectRaw('DATE(recorded_at) as date, SUM(estimated_loss) as amount, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date');

            $damagedQuery = $this->applyLocationFilterForDamaged($damagedQuery, $locationFilter);
            $damaged = $damagedQuery->get()->keyBy('date');

            // Merge and fill gaps
            $startDate = Carbon::parse($dateFrom);
            $endDate   = Carbon::parse($dateTo);
            $result = [];

            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $dateStr = $date->format('Y-m-d');
                $result[] = [
                    'date' => $dateStr,
                    'refunds' => (int) ($refunds[$dateStr]->amount ?? 0),
                    'refunds_count' => $refunds[$dateStr]->count ?? 0,
                    'damaged_loss' => (int) ($damaged[$dateStr]->amount ?? 0),
                    'damaged_count' => $damaged[$dateStr]->count ?? 0,
                    'total_loss' => (int) (($refunds[$dateStr]->amount ?? 0) + ($damaged[$dateStr]->amount ?? 0)),
                ];
            }

            return $result;
        });
    }

    /**
     * Get return reason breakdown
     */
    public function getReturnReasonBreakdown(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_return_reasons_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            $query = ReturnModel::whereBetween('processed_at', [$from, $to])
                ->where('is_exchange', false)
                ->selectRaw('reason, COUNT(*) as count, SUM(refund_amount) as amount')
                ->groupBy('reason')
                ->orderByDesc('count');

            $query = $this->applyLocationFilter($query, $locationFilter);

            return $query->get()->map(function ($item) {
                return [
                    'reason' => $item->reason->value ?? 'Unknown',
                    'count' => $item->count,
                    'amount' => (int) $item->amount,
                ];
            })->toArray();
        });
    }

    /**
     * Get disposition type breakdown for damaged goods
     */
    public function getDispositionBreakdown(string $dateFrom, string $dateTo, ?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_disposition_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            $query = DamagedGood::whereBetween('recorded_at', [$from, $to])
                ->selectRaw('disposition, COUNT(*) as count, SUM(estimated_loss) as loss, SUM(quantity_damaged) as quantity')
                ->groupBy('disposition')
                ->orderByDesc('loss');

            $query = $this->applyLocationFilterForDamaged($query, $locationFilter);

            return $query->get()->map(function ($item) {
                return [
                    'disposition' => $item->disposition->value ?? 'Unknown',
                    'count' => $item->count,
                    'quantity' => $item->quantity,
                    'loss' => (int) $item->loss,
                ];
            })->toArray();
        });
    }

    /**
     * Get products with most returns/damage
     */
    public function getProblemProducts(string $dateFrom, string $dateTo, ?string $locationFilter = 'all', int $limit = 20): array
    {
        $cacheKey = "analytics_problem_products_{$dateFrom}_{$dateTo}_{$locationFilter}_{$limit}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo, $locationFilter, $limit) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            // Get return items - Note: refund_amount is in returns table, not return_items
            $returnItemsQuery = ReturnItem::join('returns', 'return_items.return_id', '=', 'returns.id')
                ->join('products', 'return_items.product_id', '=', 'products.id')
                ->whereBetween('returns.processed_at', [$from, $to])
                ->where('returns.is_exchange', false)
                ->selectRaw('
                    products.id,
                    products.name,
                    COUNT(DISTINCT returns.id) as return_count,
                    SUM(return_items.quantity_returned) as returned_quantity,
                    SUM(return_items.unit_price * return_items.quantity_returned) as refund_amount
                ')
                ->groupBy('products.id', 'products.name');

            if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
                $shopId = (int) explode(':', $locationFilter)[1];
                $returnItemsQuery->where('returns.shop_id', $shopId);
            }

            $returns = $returnItemsQuery->get()->keyBy('id');

            // Exclude return-sourced damaged goods from loss totals
            $damagedQuery = DamagedGood::join('products', 'damaged_goods.product_id', '=', 'products.id')
                ->whereBetween('damaged_goods.recorded_at', [$from, $to])
                ->where('damaged_goods.source_type', '!=', 'return')
                ->selectRaw('
                    products.id,
                    products.name,
                    COUNT(*) as damage_count,
                    SUM(damaged_goods.quantity_damaged) as damaged_quantity,
                    SUM(damaged_goods.estimated_loss) as estimated_loss
                ')
                ->groupBy('products.id', 'products.name');

            $damagedQuery = $this->applyLocationFilterForDamaged($damagedQuery, $locationFilter);
            $damaged = $damagedQuery->get()->keyBy('id');

            // Merge results
            $allProductIds = array_unique(array_merge($returns->keys()->toArray(), $damaged->keys()->toArray()));
            $result = [];

            foreach ($allProductIds as $productId) {
                $returnData = $returns[$productId] ?? null;
                $damagedData = $damaged[$productId] ?? null;

                $result[] = [
                    'product_id' => $productId,
                    'product_name' => $returnData->name ?? $damagedData->name,
                    'return_count' => $returnData->return_count ?? 0,
                    'returned_quantity' => $returnData->returned_quantity ?? 0,
                    'refund_amount' => (int) ($returnData->refund_amount ?? 0),
                    'damage_count' => $damagedData->damage_count ?? 0,
                    'damaged_quantity' => $damagedData->damaged_quantity ?? 0,
                    'damage_loss' => (int) ($damagedData->estimated_loss ?? 0),
                    'total_loss' => (int) (($returnData->refund_amount ?? 0) + ($damagedData->estimated_loss ?? 0)),
                ];
            }

            // Sort by total loss and limit
            usort($result, fn($a, $b) => $b['total_loss'] <=> $a['total_loss']);

            return array_slice($result, 0, $limit);
        });
    }

    /**
     * Get returns by location (shop comparison)
     */
    public function getReturnsByLocation(string $dateFrom, string $dateTo): array
    {
        $cacheKey = "analytics_returns_by_location_{$dateFrom}_{$dateTo}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function () use ($dateFrom, $dateTo) {
            [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

            return ReturnModel::join('shops', 'returns.shop_id', '=', 'shops.id')
                ->whereBetween('returns.processed_at', [$from, $to])
                ->selectRaw('
                    shops.id,
                    shops.name,
                    COUNT(returns.id) as returns_count,
                    COUNT(CASE WHEN returns.is_exchange = false THEN 1 END) as refunds_count,
                    SUM(CASE WHEN returns.is_exchange = false THEN returns.refund_amount ELSE 0 END) as refund_amount,
                    COUNT(CASE WHEN returns.is_exchange = true THEN 1 END) as exchanges_count
                ')
                ->groupBy('shops.id', 'shops.name')
                ->orderByDesc('returns_count')
                ->get()
                ->map(function ($item) {
                    return [
                        'shop_id' => $item->id,
                        'shop_name' => $item->name,
                        'returns_count' => $item->returns_count,
                        'refunds_count' => (int) $item->refunds_count,
                        'refund_amount' => (int) $item->refund_amount,
                        'exchanges_count' => $item->exchanges_count,
                    ];
                })->toArray();
        });
    }

    /**
     * Apply location filter to returns query
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
     * Apply location filter to damaged goods query
     */
    private function applyLocationFilterForDamaged($query, ?string $locationFilter)
    {
        if (!$locationFilter || $locationFilter === 'all') {
            return $query;
        }

        if (str_starts_with($locationFilter, 'shop:')) {
            $shopId = (int) explode(':', $locationFilter)[1];
            return $query->where('damaged_goods.location_type', 'shop')
                ->where('damaged_goods.location_id', $shopId);
        }

        if (str_starts_with($locationFilter, 'warehouse:')) {
            $warehouseId = (int) explode(':', $locationFilter)[1];
            return $query->where('damaged_goods.location_type', 'warehouse')
                ->where('damaged_goods.location_id', $warehouseId);
        }

        return $query;
    }

    /**
     * Apply location filter to sales query
     */
    private function applyLocationFilterForSales($query, ?string $locationFilter)
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
}
