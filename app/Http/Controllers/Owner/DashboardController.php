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

class DashboardController extends Controller
{
    public function index()
    {
        // System-wide analytics
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

        // Sales statistics (last 30 days)
        $salesStats = [
            'today' => Sale::notVoided()->whereDate('sale_date', today())->sum('total') / 100,
            'this_week' => Sale::notVoided()
                ->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('total') / 100,
            'this_month' => Sale::notVoided()
                ->whereYear('sale_date', now()->year)
                ->whereMonth('sale_date', now()->month)
                ->sum('total') / 100,
            'total_count_today' => Sale::notVoided()->whereDate('sale_date', today())->count(),
        ];

        // Top performing shops (by sales this month)
        $topShops = Shop::withCount(['sales' => function ($query) {
            $query->notVoided()
                ->whereYear('sale_date', now()->year)
                ->whereMonth('sale_date', now()->month);
        }])
            ->with(['sales' => function ($query) {
                $query->notVoided()
                    ->whereYear('sale_date', now()->year)
                    ->whereMonth('sale_date', now()->month);
            }])
            ->get()
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

        return view('owner.dashboard', compact(
            'stats',
            'recentTransfers',
            'transferStats',
            'salesStats',
            'topShops',
            'recentUsers',
            'criticalAlerts',
            'lowStockProducts'
        ));
    }
}
