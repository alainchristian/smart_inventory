<?php

namespace App\Http\Controllers\Owner;

use App\Enums\TransferStatus;
use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Box;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\ReturnModel;
use App\Models\DamagedGood;
use App\Services\Analytics\InventoryAnalyticsService;
use App\Services\Analytics\TransferAnalyticsService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $filter = $request->get('filter', 'today');
        $fromDate = $request->get('from');
        $toDate = $request->get('to');

        // Determine date range based on filter
        $dateRange = $this->getDateRange($filter, $fromDate, $toDate);

        // System-wide analytics (not date-dependent)
        $stats = [
            'total_users' => User::active()->count(),
            'total_warehouses' => Warehouse::active()->count(),
            'total_shops' => Shop::active()->count(),
            'total_products' => Product::active()->count(),
            'total_boxes' => Box::count(),
            'total_items_in_stock' => Box::sum('items_remaining'),
            'warehouse_boxes' => Box::where('location_type', 'warehouse')->count(),
            'shop_boxes' => Box::where('location_type', 'shop')->count(),
        ];

        // Inventory value (calculated in cents, convert to dollars)
        $inventoryValue = Box::whereIn('status', ['full', 'partial'])
            ->with('product')
            ->get()
            ->sum(function ($box) {
                return $box->items_remaining * $box->product->purchase_price;
            });
        $stats['inventory_value'] = $inventoryValue / 100;

        // Potential retail value
        $retailValue = Box::whereIn('status', ['full', 'partial'])
            ->with('product')
            ->get()
            ->sum(function ($box) {
                return $box->items_remaining * $box->product->selling_price;
            });
        $stats['retail_value'] = $retailValue / 100;

        // Potential profit
        $stats['potential_profit'] = $stats['retail_value'] - $stats['inventory_value'];

        // Recent transfers
        $recentTransfers = Transfer::with(['fromWarehouse', 'toShop', 'requestedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Transfers by status
        $transferStats = [
            'pending' => Transfer::where('status', TransferStatus::PENDING)->count(),
            'in_transit' => Transfer::where('status', TransferStatus::IN_TRANSIT)->count(),
            'with_discrepancies' => Transfer::where('has_discrepancy', true)
                ->where('status', TransferStatus::RECEIVED)
                ->count(),
        ];

        // Delivered Today count (Problem 4)
        $deliveredToday = Transfer::where(function($query) {
                $query->where('status', TransferStatus::DELIVERED)
                      ->orWhere('status', TransferStatus::RECEIVED);
            })
            ->whereDate('delivered_at', Carbon::today())
            ->count();

        // Sales statistics - FILTERED BY DATE RANGE
        $salesQuery = Sale::notVoided();

        // Get yesterday's sales for trend comparison
        $yesterdaySales = Sale::notVoided()
            ->whereDate('sale_date', today()->subDay())
            ->sum('total') / 100;

        if ($filter === 'today') {
            $todaySales = $salesQuery->whereDate('sale_date', today())->sum('total') / 100;
            $salesStats = [
                'today' => $todaySales,
                'this_week' => Sale::notVoided()
                    ->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->sum('total') / 100,
                'this_month' => Sale::notVoided()
                    ->whereYear('sale_date', now()->year)
                    ->whereMonth('sale_date', now()->month)
                    ->sum('total') / 100,
                'total_count_today' => $salesQuery->whereDate('sale_date', today())->count(),
                'yesterday' => $yesterdaySales,
                'trend_percentage' => $yesterdaySales > 0
                    ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100
                    : ($todaySales > 0 ? 100 : 0),
            ];
        } elseif ($filter === 'week') {
            $weekStart = now()->startOfWeek();
            $weekEnd = now()->endOfWeek();
            
            $salesStats = [
                'today' => $salesQuery->whereBetween('sale_date', [$weekStart, $weekEnd])->sum('total') / 100,
                'this_week' => $salesQuery->whereBetween('sale_date', [$weekStart, $weekEnd])->sum('total') / 100,
                'this_month' => Sale::notVoided()
                    ->whereYear('sale_date', now()->year)
                    ->whereMonth('sale_date', now()->month)
                    ->sum('total') / 100,
                'total_count_today' => $salesQuery->whereBetween('sale_date', [$weekStart, $weekEnd])->count(),
            ];
        } elseif ($filter === 'month') {
            $salesStats = [
                'today' => $salesQuery->whereYear('sale_date', now()->year)
                    ->whereMonth('sale_date', now()->month)
                    ->sum('total') / 100,
                'this_week' => Sale::notVoided()
                    ->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->sum('total') / 100,
                'this_month' => $salesQuery->whereYear('sale_date', now()->year)
                    ->whereMonth('sale_date', now()->month)
                    ->sum('total') / 100,
                'total_count_today' => $salesQuery->whereYear('sale_date', now()->year)
                    ->whereMonth('sale_date', now()->month)
                    ->count(),
            ];
        } elseif ($filter === 'custom' && $fromDate && $toDate) {
            $from = Carbon::parse($fromDate)->startOfDay();
            $to = Carbon::parse($toDate)->endOfDay();
            
            $salesStats = [
                'today' => $salesQuery->whereBetween('sale_date', [$from, $to])->sum('total') / 100,
                'this_week' => $salesQuery->whereBetween('sale_date', [$from, $to])->sum('total') / 100,
                'this_month' => $salesQuery->whereBetween('sale_date', [$from, $to])->sum('total') / 100,
                'total_count_today' => $salesQuery->whereBetween('sale_date', [$from, $to])->count(),
            ];
        } else {
            // Default to today
            $salesStats = [
                'today' => $salesQuery->whereDate('sale_date', today())->sum('total') / 100,
                'this_week' => Sale::notVoided()
                    ->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->sum('total') / 100,
                'this_month' => Sale::notVoided()
                    ->whereYear('sale_date', now()->year)
                    ->whereMonth('sale_date', now()->month)
                    ->sum('total') / 100,
                'total_count_today' => $salesQuery->whereDate('sale_date', today())->count(),
            ];
        }

        // Top performing shops (by sales in selected date range)
        $topShopsQuery = Shop::withCount(['sales' => function ($query) use ($filter, $fromDate, $toDate) {
            $query->notVoided();
            
            if ($filter === 'today') {
                $query->whereDate('sale_date', today());
            } elseif ($filter === 'week') {
                $query->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($filter === 'month') {
                $query->whereYear('sale_date', now()->year)
                      ->whereMonth('sale_date', now()->month);
            } elseif ($filter === 'custom' && $fromDate && $toDate) {
                $from = Carbon::parse($fromDate)->startOfDay();
                $to = Carbon::parse($toDate)->endOfDay();
                $query->whereBetween('sale_date', [$from, $to]);
            } else {
                // Default to this month
                $query->whereYear('sale_date', now()->year)
                      ->whereMonth('sale_date', now()->month);
            }
        }])
        ->with(['sales' => function ($query) use ($filter, $fromDate, $toDate) {
            $query->notVoided();
            
            if ($filter === 'today') {
                $query->whereDate('sale_date', today());
            } elseif ($filter === 'week') {
                $query->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($filter === 'month') {
                $query->whereYear('sale_date', now()->year)
                      ->whereMonth('sale_date', now()->month);
            } elseif ($filter === 'custom' && $fromDate && $toDate) {
                $from = Carbon::parse($fromDate)->startOfDay();
                $to = Carbon::parse($toDate)->endOfDay();
                $query->whereBetween('sale_date', [$from, $to]);
            } else {
                // Default to this month
                $query->whereYear('sale_date', now()->year)
                      ->whereMonth('sale_date', now()->month);
            }
        }]);

        $topShops = $topShopsQuery->get()
            ->map(function ($shop) {
                $shop->total_sales = $shop->sales->sum('total') / 100;
                return $shop;
            })
            ->sortByDesc('total_sales')
            ->take(5);

        // Recent users
        $recentUsers = User::with('location')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Critical alerts
        $criticalAlerts = Alert::critical()
            ->unresolved()
            ->notDismissed()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Low stock products across all locations
        $lowStockProducts = Product::active()
            ->with('boxes')
            ->get()
            ->filter(function ($product) {
                // Check all locations
                $totalStock = $product->boxes()
                    ->whereIn('status', ['full', 'partial'])
                    ->sum('items_remaining');
                return $totalStock <= $product->low_stock_threshold;
            })
            ->take(10);

        // At-Risk Inventory Summary
        $atRiskInventory = [
            'low_stock_count' => $lowStockProducts->count(),
            'expiring_soon_count' => Box::whereIn('status', ['full', 'partial'])
                ->where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>=', now())
                ->count(),
            'pending_transfers' => $transferStats['pending'],
            'items_in_transit' => $transferStats['in_transit'],
            'critical_alerts' => $criticalAlerts->count(),
        ];

        // System health/last updated
        $systemHealth = [
            'last_sync' => now(),
            'status' => 'operational',
        ];

        // Quick Analytics Data
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Returns this month
        $returns = ReturnModel::whereBetween('processed_at', [$startOfMonth, $endOfMonth])
            ->where('is_exchange', false)
            ->selectRaw('COUNT(*) as count, SUM(refund_amount) as amount')
            ->first();

        $returnsThisMonth = [
            'count' => $returns->count ?? 0,
            'amount' => $returns->amount ?? 0,
        ];

        // Damaged goods loss this month
        $damagedGoodsLoss = DamagedGood::whereBetween('recorded_at', [$startOfMonth, $endOfMonth])
            ->sum('estimated_loss') ?? 0;

        // Stock turnover
        $inventoryService = app(InventoryAnalyticsService::class);
        $stockTurnover = $inventoryService->calculateStockTurnover('all');

        // Transfer efficiency
        $transferService = app(TransferAnalyticsService::class);
        $last30Days = now()->subDays(30)->format('Y-m-d');
        $today = now()->format('Y-m-d');
        $transferKpis = $transferService->getTransferKpis($last30Days, $today, null);

        $transferEfficiency = [
            'avg_hours' => $transferKpis['avg_completion_hours'] ?? 0,
            'discrepancy_rate' => $transferKpis['discrepancy_rate'] ?? 0,
        ];

        // Sales Chart Data (Problem 3) - 7 days of data
        $salesChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayTotal = Sale::notVoided()
                ->whereDate('sale_date', $date)
                ->sum('total');  // stored in cents
            $dayCount = Sale::notVoided()
                ->whereDate('sale_date', $date)
                ->count();
            $salesChartData[] = [
                'label'        => $date->format('D'),   // Mon, Tue…
                'date'         => $date->toDateString(),
                'revenue'      => $dayTotal / 100,
                'transactions' => $dayCount,
            ];
        }

        // Shop Stock Fill Data (Problem 5)
        $shopStockFill = Shop::active()->get()->map(function($shop) {
            $boxes = Box::where('location_type', 'shop')
                ->where('location_id', $shop->id)
                ->whereIn('status', ['full', 'partial'])
                ->selectRaw('SUM(items_remaining) as remaining, SUM(items_total) as total')
                ->first();

            $fillPct = ($boxes && $boxes->total > 0)
                ? round(($boxes->remaining / $boxes->total) * 100)
                : 0;

            return [
                'name' => $shop->name,
                'pct' => $fillPct,
            ];
        })->sortByDesc('pct')->values()->all();

        return view('owner.dashboard', compact(
            'stats',
            'recentTransfers',
            'transferStats',
            'salesStats',
            'topShops',
            'recentUsers',
            'criticalAlerts',
            'lowStockProducts',
            'atRiskInventory',
            'systemHealth',
            'filter',
            'fromDate',
            'toDate',
            'returnsThisMonth',
            'damagedGoodsLoss',
            'stockTurnover',
            'transferEfficiency',
            'deliveredToday',
            'salesChartData',
            'shopStockFill'
        ));
    }

    /**
     * Helper method to get date range based on filter
     */
    private function getDateRange($filter, $fromDate = null, $toDate = null)
    {
        switch ($filter) {
            case 'today':
                return [
                    'start' => Carbon::today(),
                    'end' => Carbon::today()->endOfDay(),
                ];
            
            case 'week':
                return [
                    'start' => Carbon::now()->startOfWeek(),
                    'end' => Carbon::now()->endOfWeek(),
                ];
            
            case 'month':
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth(),
                ];
            
            case 'custom':
                if ($fromDate && $toDate) {
                    return [
                        'start' => Carbon::parse($fromDate)->startOfDay(),
                        'end' => Carbon::parse($toDate)->endOfDay(),
                    ];
                }
                // Fallback to today if custom dates are invalid
                return [
                    'start' => Carbon::today(),
                    'end' => Carbon::today()->endOfDay(),
                ];
            
            default:
                return [
                    'start' => Carbon::today(),
                    'end' => Carbon::today()->endOfDay(),
                ];
        }
    }
}