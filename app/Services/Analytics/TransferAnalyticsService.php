<?php

namespace App\Services\Analytics;

use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\TransferBox;
use App\Models\Product;
use App\Enums\TransferStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TransferAnalyticsService
{
    /**
     * Get transfer performance KPIs
     */
    public function getTransferKpis(string $dateFrom, string $dateTo, ?string $statusFilter = null): array
    {
        $cacheKey = "analytics_transfer_kpis_{$dateFrom}_{$dateTo}_{$statusFilter}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $statusFilter) {
            $query = Transfer::whereBetween('created_at', [$dateFrom, $dateTo]);

            if ($statusFilter) {
                $query->where('status', $statusFilter);
            }

            $totalTransfers = $query->count();

            // Calculate average completion time (for completed transfers)
            $completedQuery = Transfer::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('received_at')
                ->whereNotNull('requested_at')
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (received_at - requested_at)) / 3600) as avg_hours');

            $avgHours = $completedQuery->first()->avg_hours ?? 0;

            // Calculate discrepancy rate
            $discrepancyQuery = Transfer::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('has_discrepancy', true);

            if ($statusFilter) {
                $discrepancyQuery->where('status', $statusFilter);
            }

            $discrepancyCount = $discrepancyQuery->count();
            $discrepancyRate = $totalTransfers > 0 ? ($discrepancyCount / $totalTransfers) * 100 : 0;

            // Count in-transit transfers
            $inTransitCount = Transfer::where('status', TransferStatus::IN_TRANSIT)->count();

            return [
                'total_transfers' => $totalTransfers,
                'avg_completion_hours' => round($avgHours, 1),
                'discrepancy_rate' => round($discrepancyRate, 2),
                'in_transit_count' => $inTransitCount,
                'discrepancy_count' => $discrepancyCount,
            ];
        });
    }

    /**
     * Get transfer volume trend
     */
    public function getTransferVolumeTrend(string $dateFrom, string $dateTo, ?string $statusFilter = null): array
    {
        $cacheKey = "analytics_transfer_trend_{$dateFrom}_{$dateTo}_{$statusFilter}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $statusFilter) {
            $query = Transfer::whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date');

            if ($statusFilter) {
                $query->where('status', $statusFilter);
            }

            return $query->get()->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            })->toArray();
        });
    }

    /**
     * Get transfer routes (warehouse → shop matrix)
     */
    public function getTransferRoutes(string $dateFrom, string $dateTo): array
    {
        $cacheKey = "analytics_transfer_routes_{$dateFrom}_{$dateTo}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo) {
            return Transfer::join('warehouses', 'transfers.from_warehouse_id', '=', 'warehouses.id')
                ->join('shops', 'transfers.to_shop_id', '=', 'shops.id')
                ->whereBetween('transfers.created_at', [$dateFrom, $dateTo])
                ->selectRaw('
                    warehouses.id as warehouse_id,
                    warehouses.name as warehouse_name,
                    shops.id as shop_id,
                    shops.name as shop_name,
                    COUNT(transfers.id) as transfer_count,
                    SUM(CASE WHEN transfers.has_discrepancy THEN 1 ELSE 0 END) as discrepancy_count
                ')
                ->groupBy('warehouses.id', 'warehouses.name', 'shops.id', 'shops.name')
                ->orderByDesc('transfer_count')
                ->get()
                ->map(function ($item) {
                    return [
                        'warehouse_id' => $item->warehouse_id,
                        'warehouse_name' => $item->warehouse_name,
                        'shop_id' => $item->shop_id,
                        'shop_name' => $item->shop_name,
                        'transfer_count' => $item->transfer_count,
                        'discrepancy_count' => $item->discrepancy_count,
                    ];
                })->toArray();
        });
    }

    /**
     * Get transfer status distribution
     */
    public function getStatusDistribution(string $dateFrom, string $dateTo): array
    {
        $cacheKey = "analytics_transfer_status_{$dateFrom}_{$dateTo}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo) {
            return Transfer::whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->orderByDesc('count')
                ->get()
                ->map(function ($item) {
                    return [
                        'status' => $item->status->value ?? 'Unknown',
                        'count' => $item->count,
                    ];
                })->toArray();
        });
    }

    /**
     * Get completion time distribution
     */
    public function getCompletionTimeDistribution(string $dateFrom, string $dateTo): array
    {
        $cacheKey = "analytics_completion_time_{$dateFrom}_{$dateTo}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo) {
            return Transfer::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('received_at')
                ->whereNotNull('requested_at')
                ->selectRaw("
                    CASE
                        WHEN EXTRACT(EPOCH FROM (received_at - requested_at)) / 3600 <= 24 THEN '0-24 hours'
                        WHEN EXTRACT(EPOCH FROM (received_at - requested_at)) / 3600 <= 48 THEN '24-48 hours'
                        WHEN EXTRACT(EPOCH FROM (received_at - requested_at)) / 3600 <= 72 THEN '48-72 hours'
                        ELSE '72+ hours'
                    END as time_bucket,
                    COUNT(*) as count
                ")
                ->groupBy('time_bucket')
                ->get()
                ->map(function ($item) {
                    return [
                        'time_bucket' => $item->time_bucket,
                        'count' => $item->count,
                    ];
                })->toArray();
        });
    }

    /**
     * Get most transferred products
     */
    public function getMostTransferredProducts(string $dateFrom, string $dateTo, int $limit = 20): array
    {
        $cacheKey = "analytics_most_transferred_{$dateFrom}_{$dateTo}_{$limit}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $limit) {
            return TransferItem::join('transfers', 'transfer_items.transfer_id', '=', 'transfers.id')
                ->join('products', 'transfer_items.product_id', '=', 'products.id')
                ->whereBetween('transfers.created_at', [$dateFrom, $dateTo])
                ->selectRaw('
                    products.id,
                    products.name,
                    COUNT(DISTINCT transfers.id) as transfer_count,
                    SUM(transfer_items.quantity_shipped) as total_sent,
                    SUM(transfer_items.quantity_received) as total_received,
                    SUM(transfer_items.quantity_shipped - transfer_items.quantity_received) as total_discrepancy
                ')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sent')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'product_id' => $item->id,
                        'product_name' => $item->name,
                        'transfer_count' => $item->transfer_count,
                        'total_sent' => $item->total_sent,
                        'total_received' => $item->total_received,
                        'total_discrepancy' => $item->total_discrepancy,
                    ];
                })->toArray();
        });
    }

    /**
     * Get recent transfers with discrepancies
     */
    public function getRecentDiscrepancies(string $dateFrom, string $dateTo, int $limit = 10): array
    {
        $cacheKey = "analytics_recent_discrepancies_{$dateFrom}_{$dateTo}_{$limit}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo, $limit) {
            return Transfer::with(['fromWarehouse', 'toShop'])
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('has_discrepancy', true)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($transfer) {
                    return [
                        'transfer_id' => $transfer->id,
                        'transfer_number' => $transfer->transfer_number,
                        'from_warehouse' => $transfer->fromWarehouse->name ?? 'Unknown',
                        'to_shop' => $transfer->toShop->name ?? 'Unknown',
                        'status' => $transfer->status->value ?? 'Unknown',
                        'created_at' => $transfer->created_at->format('Y-m-d H:i:s'),
                        'received_at' => $transfer->received_at?->format('Y-m-d H:i:s'),
                        'discrepancy_notes' => $transfer->discrepancy_notes,
                    ];
                })->toArray();
        });
    }

    /**
     * Get transfer efficiency by warehouse
     */
    public function getWarehouseEfficiency(string $dateFrom, string $dateTo): array
    {
        $cacheKey = "analytics_warehouse_efficiency_{$dateFrom}_{$dateTo}";

        return Cache::remember($cacheKey, 900, function () use ($dateFrom, $dateTo) {
            return Transfer::join('warehouses', 'transfers.from_warehouse_id', '=', 'warehouses.id')
                ->whereBetween('transfers.created_at', [$dateFrom, $dateTo])
                ->whereNotNull('transfers.received_at')
                ->whereNotNull('transfers.requested_at')
                ->selectRaw('
                    warehouses.id,
                    warehouses.name,
                    COUNT(transfers.id) as total_transfers,
                    AVG(EXTRACT(EPOCH FROM (transfers.received_at - transfers.requested_at)) / 3600) as avg_hours,
                    SUM(CASE WHEN transfers.has_discrepancy THEN 1 ELSE 0 END) as discrepancy_count
                ')
                ->groupBy('warehouses.id', 'warehouses.name')
                ->orderByDesc('total_transfers')
                ->get()
                ->map(function ($item) {
                    $discrepancyRate = $item->total_transfers > 0
                        ? ($item->discrepancy_count / $item->total_transfers) * 100
                        : 0;

                    return [
                        'warehouse_id' => $item->id,
                        'warehouse_name' => $item->name,
                        'total_transfers' => $item->total_transfers,
                        'avg_completion_hours' => round($item->avg_hours, 1),
                        'discrepancy_count' => $item->discrepancy_count,
                        'discrepancy_rate' => round($discrepancyRate, 2),
                    ];
                })->toArray();
        });
    }
}
