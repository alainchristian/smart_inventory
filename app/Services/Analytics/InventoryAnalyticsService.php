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
            $query = Box::available()
                ->join('products', 'boxes.product_id', '=', 'products.id')
                ->selectRaw("
                    CASE
                        WHEN DATE_PART('day', NOW() - boxes.received_at) <= 30 THEN '0-30 days'
                        WHEN DATE_PART('day', NOW() - boxes.received_at) <= 60 THEN '31-60 days'
                        WHEN DATE_PART('day', NOW() - boxes.received_at) <= 90 THEN '61-90 days'
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
                    'box_count' => $item->box_count,
                    'items_count' => $item->items_count,
                    'value' => (int) $item->value,
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
                ->selectRaw('
                    products.id,
                    products.name,
                    boxes.location_type,
                    boxes.location_id,
                    SUM(boxes.items_remaining) as items_count,
                    SUM(boxes.items_remaining * products.purchase_price) as purchase_value,
                    SUM(boxes.items_remaining * products.selling_price) as retail_value
                ')
                ->groupBy('products.id', 'products.name', 'boxes.location_type', 'boxes.location_id')
                ->orderByDesc('purchase_value')
                ->limit($limit);

            $query = $this->applyLocationFilter($query, $locationFilter);

            return $query->get()->map(function ($item) {
                return [
                    'product_id' => $item->id,
                    'product_name' => $item->name,
                    'location_type' => is_string($item->location_type) ? $item->location_type : $item->location_type->value,
                    'location_id' => $item->location_id,
                    'items_count' => $item->items_count,
                    'purchase_value' => (int) $item->purchase_value,
                    'retail_value' => (int) $item->retail_value,
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
