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
            'toDate'
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