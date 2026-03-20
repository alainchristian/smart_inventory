<?php

namespace App\Services\Analytics;

use App\Models\Box;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Enums\LocationType;
use App\Enums\BoxStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class InventoryAnalyticsService
{
    /**
     * Get inventory valuation KPIs
     */
    public function getInventoryKpis(?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_inventory_kpis_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($locationFilter) {
            $query = Box::available()
                ->join('products', 'boxes.product_id', '=', 'products.id')
                ->selectRaw('
                    SUM(boxes.items_remaining * products.purchase_price) as purchase_value,
                    SUM(boxes.items_remaining * products.selling_price) as retail_value,
                    COUNT(DISTINCT boxes.product_id) as product_count
                ');

            $query = $this->applyLocationFilter($query, $locationFilter);

            $result = $query->first();

            $purchaseValue = $result->purchase_value ?? 0;
            $retailValue = $result->retail_value ?? 0;
            $potentialProfit = $retailValue - $purchaseValue;
            $turnoverRate = $this->calculateStockTurnover($locationFilter);

            return [
                'purchase_value' => (int) $purchaseValue,
                'retail_value' => (int) $retailValue,
                'potential_profit' => (int) $potentialProfit,
                'turnover_rate' => round($turnoverRate, 2),
                'product_count' => $result->product_count ?? 0,
            ];
        });
    }

    /**
     * Get inventory distribution by location
     */
    public function getInventoryByLocation(): array
    {
        $cacheKey = "analytics_inventory_by_location";

        return Cache::remember($cacheKey, 900, function () {
            $warehouses = Box::available()
                ->join('products', 'boxes.product_id', '=', 'products.id')
                ->join('warehouses', function ($join) {
                    $join->on('boxes.location_id', '=', 'warehouses.id')
                        ->where('boxes.location_type', '=', LocationType::WAREHOUSE->value);
                })
                ->selectRaw('
                    warehouses.id,
                    warehouses.name,
                    SUM(boxes.items_remaining * products.purchase_price) as value,
                    SUM(boxes.items_remaining) as items_count
                ')
                ->groupBy('warehouses.id', 'warehouses.name')
                ->get();

            $shops = Box::available()
                ->join('products', 'boxes.product_id', '=', 'products.id')
                ->join('shops', function ($join) {
                    $join->on('boxes.location_id', '=', 'shops.id')
                        ->where('boxes.location_type', '=', LocationType::SHOP->value);
                })
                ->selectRaw('
                    shops.id,
                    shops.name,
                    SUM(boxes.items_remaining * products.purchase_price) as value,
                    SUM(boxes.items_remaining) as items_count
                ')
                ->groupBy('shops.id', 'shops.name')
                ->get();

            return [
                'warehouses' => $warehouses->map(function ($item) {
                    return [
                        'location_id' => $item->id,
                        'location_name' => $item->name,
                        'location_type' => 'warehouse',
                        'value' => (int) $item->value,
                        'items_count' => $item->items_count,
                    ];
                })->toArray(),
                'shops' => $shops->map(function ($item) {
                    return [
                        'location_id' => $item->id,
                        'location_name' => $item->name,
                        'location_type' => 'shop',
                        'value' => (int) $item->value,
                        'items_count' => $item->items_count,
                    ];
                })->toArray(),
            ];
        });
    }

    /**
     * Get stock aging analysis
     */
    public function getAgingAnalysis(?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_aging_analysis_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($locationFilter) {
            $query = DB::table('boxes')
                ->join('products', 'boxes.product_id', '=', 'products.id')
                ->leftJoin(DB::raw('(
                    SELECT box_id, MAX(moved_at) as last_moved_at
                    FROM box_movements
                    GROUP BY box_id
                ) as last_move'), 'last_move.box_id', '=', 'boxes.id')
                ->whereIn('boxes.status', [BoxStatus::FULL->value, BoxStatus::PARTIAL->value])
                ->where('boxes.items_remaining', '>', 0)
                ->selectRaw("
                    CASE
                        WHEN DATE_PART('day', NOW() - COALESCE(last_move.last_moved_at, boxes.received_at)) <= 30
                            THEN '0-30 days'
                        WHEN DATE_PART('day', NOW() - COALESCE(last_move.last_moved_at, boxes.received_at)) <= 60
                            THEN '31-60 days'
                        WHEN DATE_PART('day', NOW() - COALESCE(last_move.last_moved_at, boxes.received_at)) <= 90
                            THEN '61-90 days'
                        ELSE '90+ days'
                    END as age_bracket,
                    COUNT(*) as box_count,
                    SUM(boxes.items_remaining) as items_count,
                    SUM(boxes.items_remaining * products.purchase_price) as value
                ")
                ->groupBy('age_bracket');

            $query = $this->applyLocationFilter($query, $locationFilter);

            return $query->get()->map(function ($item) {
                return [
                    'age_bracket' => $item->age_bracket,
                    'box_count'   => $item->box_count,
                    'items_count' => $item->items_count,
                    'value'       => (int) $item->value,
                ];
            })->toArray();
        });
    }

    /**
     * Get expiring stock (within 30 days)
     */
    public function getExpiringStock(?string $locationFilter = 'all', int $days = 30): array
    {
        $cacheKey = "analytics_expiring_stock_{$locationFilter}_{$days}";

        return Cache::remember($cacheKey, 900, function () use ($locationFilter, $days) {
            $query = Box::available()
                ->expiringSoon($days)
                ->join('products', 'boxes.product_id', '=', 'products.id')
                ->selectRaw('
                    products.id as product_id,
                    products.name as product_name,
                    boxes.expiry_date,
                    boxes.location_type,
                    boxes.location_id,
                    SUM(boxes.items_remaining) as items_count,
                    SUM(boxes.items_remaining * products.purchase_price) as value
                ')
                ->groupBy('products.id', 'products.name', 'boxes.expiry_date', 'boxes.location_type', 'boxes.location_id')
                ->orderBy('boxes.expiry_date');

            $query = $this->applyLocationFilter($query, $locationFilter);

            return $query->get()->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'expiry_date' => $item->expiry_date,
                    'location_type' => is_string($item->location_type) ? $item->location_type : $item->location_type->value,
                    'location_id' => $item->location_id,
                    'items_count' => $item->items_count,
                    'value' => (int) $item->value,
                ];
            })->toArray();
        });
    }

    /**
     * Get stock health indicators
     */
    public function getStockHealth(?string $locationFilter = 'all'): array
    {
        $cacheKey = "analytics_stock_health_{$locationFilter}";

        return Cache::remember($cacheKey, 900, function () use ($locationFilter) {
            // Get products with low stock
            $lowStockQuery = Product::active()
                ->join('boxes', 'products.id', '=', 'boxes.product_id')
                ->whereIn('boxes.status', [BoxStatus::FULL->value, BoxStatus::PARTIAL->value])
                ->selectRaw('
                    products.id,
                    products.low_stock_threshold,
                    SUM(boxes.items_remaining) as total_items
                ')
                ->groupBy('products.id', 'products.low_stock_threshold')
                ->havingRaw('SUM(boxes.items_remaining) <= products.low_stock_threshold');

            if ($locationFilter !== 'all') {
                $lowStockQuery = $this->applyLocationFilter($lowStockQuery, $locationFilter);
            }

            $lowStockCount = $lowStockQuery->count();

            // Get dead stock (no sales in last 90 days)
            $deadStockQuery = Product::active()
                ->join('boxes', 'products.id', '=', 'boxes.product_id')
                ->whereIn('boxes.status', [BoxStatus::FULL->value, BoxStatus::PARTIAL->value])
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('sale_items')
                        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                        ->whereColumn('sale_items.product_id', 'products.id')
                        ->whereNull('sales.voided_at')
                        ->where('sales.sale_date', '>=', now()->subDays(90));
                })
                ->selectRaw('
                    products.id,
                    SUM(boxes.items_remaining) as total_items
                ')
                ->groupBy('products.id');

            if ($locationFilter !== 'all') {
                $deadStockQuery = $this->applyLocationFilter($deadStockQuery, $locationFilter);
            }

            $deadStockCount = $deadStockQuery->count();

            return [
                'low_stock_count' => $lowStockCount,
                'dead_stock_count' => $deadStockCount,
            ];
        });
    }

    /**
     * Get top products by inventory value
     */
    public function getTopProductsByValue(?string $locationFilter = 'all', int $limit = 20): array
    {
        $cacheKey = "analytics_top_products_value_{$locationFilter}_{$limit}";

        return Cache::remember($cacheKey, 900, function () use ($locationFilter, $limit) {
            $query = Box::available()
                ->join('products', 'boxes.product_id', '=', 'products.id')
                ->selectRaw("
                    products.id,
                    products.name,
                    SUM(boxes.items_remaining) as items_count,
                    SUM(boxes.items_remaining * products.purchase_price) as purchase_value,
                    SUM(boxes.items_remaining * products.selling_price) as retail_value,
                    COUNT(DISTINCT CONCAT(boxes.location_type::text, ':', boxes.location_id::text)) as location_count
                ")
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('purchase_value')
                ->limit($limit);

            $query = $this->applyLocationFilter($query, $locationFilter);

            return $query->get()->map(function ($item) {
                return [
                    'product_id'     => $item->id,
                    'product_name'   => $item->name,
                    'items_count'    => $item->items_count,
                    'purchase_value' => (int) $item->purchase_value,
                    'retail_value'   => (int) $item->retail_value,
                    'location_count' => (int) $item->location_count,
                ];
            })->toArray();
        });
    }

    /**
     * Calculate stock turnover rate
     * Formula: COGS / Average Inventory Value (annualized)
     */
    public function calculateStockTurnover(?string $locationFilter = 'all'): float
    {
        if (str_starts_with((string) $locationFilter, 'warehouse:')) {
            // Turnover cannot be computed per warehouse — sales occur at shops.
            return 0.0;
        }

        // Get COGS for last 365 days
        $cogsQuery = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereNull('sales.voided_at')
            ->where('sales.sale_date', '>=', now()->subDays(365))
            ->selectRaw('SUM(sale_items.quantity_sold * products.purchase_price) as cogs');

        if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
            $shopId = (int) explode(':', $locationFilter)[1];
            $cogsQuery->where('sales.shop_id', $shopId);
        }

        $cogs = $cogsQuery->first()->cogs ?? 0;

        // Get current inventory value
        $inventoryQuery = Box::available()
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('SUM(boxes.items_remaining * products.purchase_price) as inventory_value');

        $inventoryQuery = $this->applyLocationFilter($inventoryQuery, $locationFilter);

        $inventoryValue = $inventoryQuery->first()->inventory_value ?? 1;

        return $inventoryValue > 0 ? ($cogs / $inventoryValue) : 0;
    }

    /**
     * Get portfolio fill rate (items_remaining / items_total for sellable boxes)
     */
    public function getPortfolioFillRate(?string $locationFilter = 'all'): ?float
    {
        $result = DB::table('boxes')
            ->when($locationFilter !== 'all', function ($q) use ($locationFilter) {
                if (str_starts_with($locationFilter, 'shop:')) {
                    $q->where('location_type', 'shop')
                      ->where('location_id', (int) explode(':', $locationFilter)[1]);
                } elseif (str_starts_with($locationFilter, 'warehouse:')) {
                    $q->where('location_type', 'warehouse')
                      ->where('location_id', (int) explode(':', $locationFilter)[1]);
                } elseif ($locationFilter === 'shops') {
                    $q->where('location_type', 'shop');
                } elseif ($locationFilter === 'warehouses') {
                    $q->where('location_type', 'warehouse');
                }
            })
            ->whereIn('status', ['full', 'partial'])
            ->where('items_remaining', '>', 0)
            ->selectRaw('SUM(items_remaining) as remaining, SUM(items_total) as total')
            ->first();

        return ($result && $result->total > 0)
            ? round(($result->remaining / $result->total) * 100, 1)
            : null;
    }

    /**
     * Classify every active product with stock into A / B / C / Dead
     * based on trailing 90-day sales revenue contribution.
     */
    public function getVelocityClassification(?string $locationFilter = 'all'): array
    {
        // Products with current stock
        $stockQuery = DB::table('boxes')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->whereIn('boxes.status', ['full', 'partial'])
            ->where('boxes.items_remaining', '>', 0)
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true);

        $stockQuery = $this->applyLocationFilter($stockQuery, $locationFilter);

        $stockedProducts = $stockQuery
            ->selectRaw('
                products.id,
                products.name,
                SUM(boxes.items_remaining) as items_in_stock,
                SUM(boxes.items_remaining * products.purchase_price) as cost_value
            ')
            ->groupBy('products.id', 'products.name')
            ->get()
            ->keyBy('id');

        if ($stockedProducts->isEmpty()) {
            return ['A' => [], 'B' => [], 'C' => [], 'Dead' => [], 'summary' => []];
        }

        // 90-day revenue per product
        $salesQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereIn('sale_items.product_id', $stockedProducts->keys()->toArray())
            ->whereNull('sales.voided_at')
            ->where('sales.sale_date', '>=', now()->subDays(90));

        if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
            $salesQuery->where('sales.shop_id', (int) explode(':', $locationFilter)[1]);
        }

        $revenue = $salesQuery
            ->selectRaw('sale_items.product_id, SUM(sale_items.line_total) as revenue')
            ->groupBy('sale_items.product_id')
            ->pluck('revenue', 'product_id');

        $totalRevenue = $revenue->sum();

        // Merge and sort by revenue desc
        $classified = $stockedProducts->map(function ($p) use ($revenue) {
            return [
                'product_id'     => $p->id,
                'product_name'   => $p->name,
                'items_in_stock' => (int) $p->items_in_stock,
                'cost_value'     => (int) $p->cost_value,
                'revenue_90d'    => (float) ($revenue[$p->id] ?? 0),
            ];
        })->sortByDesc('revenue_90d')->values();

        // Build cumulative revenue %
        $cumulative = 0;
        $result = ['A' => [], 'B' => [], 'C' => [], 'Dead' => []];

        foreach ($classified as $item) {
            if ($item['revenue_90d'] == 0) {
                $item['class']       = 'Dead';
                $item['revenue_pct'] = 0;
                $result['Dead'][]    = $item;
                continue;
            }
            $pct = $totalRevenue > 0 ? ($item['revenue_90d'] / $totalRevenue) * 100 : 0;
            $cumulative += $pct;
            $item['revenue_pct']    = round($pct, 1);
            $item['cumulative_pct'] = round($cumulative, 1);

            if ($cumulative <= 70) {
                $item['class'] = 'A';
            } elseif ($cumulative <= 90) {
                $item['class'] = 'B';
            } else {
                $item['class'] = 'C';
            }
            $result[$item['class']][] = $item;
        }

        $result['summary'] = [
            'A_count'         => count($result['A']),
            'B_count'         => count($result['B']),
            'C_count'         => count($result['C']),
            'Dead_count'      => count($result['Dead']),
            'A_cost_value'    => collect($result['A'])->sum('cost_value'),
            'B_cost_value'    => collect($result['B'])->sum('cost_value'),
            'C_cost_value'    => collect($result['C'])->sum('cost_value'),
            'Dead_cost_value' => collect($result['Dead'])->sum('cost_value'),
        ];

        return $result;
    }

    /**
     * Days on hand per product based on 30-day average sales velocity.
     */
    public function getDaysOnHandPerProduct(?string $locationFilter = 'all', int $limit = 30): array
    {
        // Avg daily sales per product (last 30 days)
        $salesQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereNull('sales.voided_at')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->where('sales.sale_date', '>=', now()->subDays(30));

        if ($locationFilter !== 'all' && str_starts_with($locationFilter, 'shop:')) {
            $salesQuery->where('sales.shop_id', (int) explode(':', $locationFilter)[1]);
        }

        $salesData = $salesQuery
            ->selectRaw('
                sale_items.product_id,
                SUM(sale_items.quantity_sold) as units_sold_30d
            ')
            ->groupBy('sale_items.product_id')
            ->pluck('units_sold_30d', 'product_id');

        // Current stock per product
        $stockQuery = DB::table('boxes')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->whereIn('boxes.status', ['full', 'partial'])
            ->where('boxes.items_remaining', '>', 0)
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true);

        $stockQuery = $this->applyLocationFilter($stockQuery, $locationFilter);

        $stock = $stockQuery
            ->selectRaw('
                products.id,
                products.name,
                products.low_stock_threshold,
                SUM(boxes.items_remaining) as items_remaining,
                SUM(boxes.items_remaining * products.purchase_price) as cost_value
            ')
            ->groupBy('products.id', 'products.name', 'products.low_stock_threshold')
            ->get();

        return $stock->map(function ($p) use ($salesData) {
            $units30d   = (float) ($salesData[$p->id] ?? 0);
            $avgDaily   = $units30d / 30;
            $daysOnHand = $avgDaily > 0
                ? (int) round($p->items_remaining / $avgDaily)
                : null; // null = no velocity, stock won't run out by sales

            return [
                'product_id'          => $p->id,
                'product_name'        => $p->name,
                'items_remaining'     => (int) $p->items_remaining,
                'cost_value'          => (int) $p->cost_value,
                'units_sold_30d'      => (int) $units30d,
                'avg_daily_sales'     => round($avgDaily, 2),
                'days_on_hand'        => $daysOnHand,
                'low_stock_threshold' => (int) $p->low_stock_threshold,
                'is_critical'         => $daysOnHand !== null && $daysOnHand <= 7,
                'is_low'              => $daysOnHand !== null && $daysOnHand <= 14,
            ];
        })
        ->sortBy(fn ($p) => $p['days_on_hand'] ?? PHP_INT_MAX)
        ->values()
        ->take($limit)
        ->toArray();
    }

    /**
     * Category breakdown of current inventory by cost value.
     */
    public function getCategoryConcentration(?string $locationFilter = 'all'): array
    {
        $query = DB::table('boxes')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('boxes.status', ['full', 'partial'])
            ->where('boxes.items_remaining', '>', 0)
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true);

        $query = $this->applyLocationFilter($query, $locationFilter);

        $rows = $query->selectRaw('
                categories.id,
                categories.name as category_name,
                COUNT(DISTINCT products.id) as product_count,
                SUM(boxes.items_remaining) as total_items,
                SUM(boxes.items_remaining * products.purchase_price) as cost_value,
                SUM(boxes.items_remaining * products.selling_price) as retail_value
            ')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('cost_value')
            ->get();

        $totalCost = $rows->sum('cost_value');

        return $rows->map(function ($row) use ($totalCost) {
            return [
                'category_id'   => $row->id,
                'category_name' => $row->category_name,
                'product_count' => (int) $row->product_count,
                'total_items'   => (int) $row->total_items,
                'cost_value'    => (int) $row->cost_value,
                'retail_value'  => (int) $row->retail_value,
                'pct_of_total'  => $totalCost > 0
                    ? round(($row->cost_value / $totalCost) * 100, 1)
                    : 0,
            ];
        })->toArray();
    }

    /**
     * Weekly boxes-received vs items-consumed for the last 12 weeks.
     */
    public function getInventoryMovementTrend(?string $locationFilter = 'all'): array
    {
        $weeks = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->startOfWeek()->subWeeks($i);
            $end   = $start->copy()->endOfWeek();

            $receivedQuery = DB::table('box_movements')
                ->where('movement_type', 'received')
                ->whereBetween('moved_at', [$start, $end]);

            $consumedQuery = DB::table('box_movements')
                ->where('movement_type', 'consumption')
                ->whereBetween('moved_at', [$start, $end]);

            // Apply location filter to movements
            if ($locationFilter !== 'all') {
                if (str_starts_with($locationFilter, 'shop:')) {
                    $shopId = (int) explode(':', $locationFilter)[1];
                    $receivedQuery->where('to_location_type', 'shop')
                                  ->where('to_location_id', $shopId);
                    $consumedQuery->where('from_location_type', 'shop')
                                  ->where('from_location_id', $shopId);
                } elseif (str_starts_with($locationFilter, 'warehouse:')) {
                    $whId = (int) explode(':', $locationFilter)[1];
                    $receivedQuery->where('to_location_type', 'warehouse')
                                  ->where('to_location_id', $whId);
                }
            }

            $weeks[] = [
                'week_label'     => $start->format('M d'),
                'week_start'     => $start->toDateString(),
                'boxes_received' => (int) $receivedQuery->count(),
                'items_consumed' => (int) $consumedQuery->sum('items_moved'),
            ];
        }

        return $weeks;
    }

    /**
     * Shrinkage statistics — damaged items in last 90 days vs received.
     */
    public function getShrinkageStats(?string $locationFilter = 'all'): array
    {
        // Total items received in last 90 days (via box_movements)
        $receivedQuery = DB::table('box_movements')
            ->join('boxes', 'box_movements.box_id', '=', 'boxes.id')
            ->where('box_movements.movement_type', 'received')
            ->where('box_movements.moved_at', '>=', now()->subDays(90));

        // Total damaged goods recorded in last 90 days
        $damagedQuery = DB::table('damaged_goods')
            ->whereNull('deleted_at')
            ->where('recorded_at', '>=', now()->subDays(90));

        if ($locationFilter !== 'all') {
            if (str_starts_with($locationFilter, 'shop:')) {
                $id = (int) explode(':', $locationFilter)[1];
                $damagedQuery->where('location_type', 'shop')->where('location_id', $id);
            } elseif (str_starts_with($locationFilter, 'warehouse:')) {
                $id = (int) explode(':', $locationFilter)[1];
                $damagedQuery->where('location_type', 'warehouse')->where('location_id', $id);
            }
        }

        $itemsReceived = (int) $receivedQuery->sum('boxes.items_total');
        $itemsDamaged  = (int) $damagedQuery->sum('quantity_damaged');
        $estimatedLoss = (int) $damagedQuery->sum('estimated_loss');

        return [
            'items_received_90d' => $itemsReceived,
            'items_damaged_90d'  => $itemsDamaged,
            'estimated_loss'     => $estimatedLoss,
            'shrinkage_pct'      => $itemsReceived > 0
                ? round(($itemsDamaged / $itemsReceived) * 100, 2)
                : 0,
        ];
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
            return $query->where('boxes.location_type', LocationType::SHOP->value)
                ->where('boxes.location_id', $shopId);
        }

        if (str_starts_with($locationFilter, 'warehouse:')) {
            $warehouseId = (int) explode(':', $locationFilter)[1];
            return $query->where('boxes.location_type', LocationType::WAREHOUSE->value)
                ->where('boxes.location_id', $warehouseId);
        }

        if ($locationFilter === 'warehouses') {
            return $query->where('boxes.location_type', LocationType::WAREHOUSE->value);
        }

        if ($locationFilter === 'shops') {
            return $query->where('boxes.location_type', LocationType::SHOP->value);
        }

        return $query;
    }
}
