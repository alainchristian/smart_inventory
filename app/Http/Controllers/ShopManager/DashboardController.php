<?php

namespace App\Http\Controllers\ShopManager;

use App\Enums\TransferStatus;
use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Box;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Transfer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $shopId = $user->location_id;
        $shop = Shop::with('defaultWarehouse')->findOrFail($shopId);

        // Today's sales statistics
        $todaySales = [
            'total_sales' => Sale::notVoided()
                ->where('shop_id', $shopId)
                ->whereDate('sale_date', today())
                ->sum('total') / 100,
            'transaction_count' => Sale::notVoided()
                ->where('shop_id', $shopId)
                ->whereDate('sale_date', today())
                ->count(),
            'items_sold' => Sale::notVoided()
                ->where('shop_id', $shopId)
                ->whereDate('sale_date', today())
                ->with('items')
                ->get()
                ->sum(function ($sale) {
                    return $sale->items->sum('quantity_sold');
                }),
            'average_transaction' => 0,
        ];

        if ($todaySales['transaction_count'] > 0) {
            $todaySales['average_transaction'] = $todaySales['total_sales'] / $todaySales['transaction_count'];
        }

        // This week's sales
        $weekSales = Sale::notVoided()
            ->where('shop_id', $shopId)
            ->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('total') / 100;

        // This month's sales
        $monthSales = Sale::notVoided()
            ->where('shop_id', $shopId)
            ->whereYear('sale_date', now()->year)
            ->whereMonth('sale_date', now()->month)
            ->sum('total') / 100;

        // Current stock levels
        $stockStats = [
            'total_boxes' => Box::where('location_type', 'shop')
                ->where('location_id', $shopId)
                ->count(),
            'full_boxes' => Box::where('location_type', 'shop')
                ->where('location_id', $shopId)
                ->where('status', 'full')
                ->count(),
            'partial_boxes' => Box::where('location_type', 'shop')
                ->where('location_id', $shopId)
                ->where('status', 'partial')
                ->count(),
            'total_items' => Box::where('location_type', 'shop')
                ->where('location_id', $shopId)
                ->sum('items_remaining'),
        ];

        // Low stock products
        $lowStockProducts = Product::active()
            ->with(['boxes' => function ($query) use ($shopId) {
                $query->where('location_type', 'shop')
                    ->where('location_id', $shopId)
                    ->whereIn('status', ['full', 'partial']);
            }])
            ->get()
            ->filter(function ($product) use ($shopId) {
                return $product->isLowStock('shop', $shopId);
            })
            ->map(function ($product) use ($shopId) {
                $stock = $product->getCurrentStock('shop', $shopId);
                $product->current_stock = $stock['total_items'];
                return $product;
            })
            ->sortBy('current_stock')
            ->take(10);

        // Pending transfer requests
        $pendingTransfers = Transfer::where('to_shop_id', $shopId)
            ->where('status', TransferStatus::PENDING)
            ->with(['fromWarehouse', 'requestedBy', 'items.product'])
            ->orderBy('requested_at', 'desc')
            ->limit(5)
            ->get();

        // Transfers in transit to this shop
        $incomingTransfers = Transfer::where('to_shop_id', $shopId)
            ->whereIn('status', [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED])
            ->with(['fromWarehouse', 'transporter'])
            ->orderBy('shipped_at', 'desc')
            ->limit(5)
            ->get();

        // Recent sales
        $recentSales = Sale::notVoided()
            ->where('shop_id', $shopId)
            ->with(['soldBy', 'items'])
            ->orderBy('sale_date', 'desc')
            ->limit(10)
            ->get();

        // Recent returns
        $recentReturns = ReturnModel::where('shop_id', $shopId)
            ->with(['processedBy', 'items.product'])
            ->orderBy('processed_at', 'desc')
            ->limit(5)
            ->get();

        // Pending returns awaiting approval
        $pendingReturns = ReturnModel::where('shop_id', $shopId)
            ->pendingApproval()
            ->with(['items.product'])
            ->orderBy('processed_at', 'asc')
            ->get();

        // Alerts for this shop
        $alerts = Alert::where(function ($query) use ($shopId) {
            $query->whereNull('user_id')
                ->orWhere('user_id', auth()->id());
        })
            ->where(function ($query) use ($shopId) {
                $query->where('entity_type', 'shop')
                    ->where('entity_id', $shopId)
                    ->orWhereNull('entity_type');
            })
            ->unresolved()
            ->notDismissed()
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top selling products (this month)
        $topProducts = Product::withCount(['saleItems' => function ($query) use ($shopId) {
            $query->whereHas('sale', function ($q) use ($shopId) {
                $q->where('shop_id', $shopId)
                    ->notVoided()
                    ->whereYear('sale_date', now()->year)
                    ->whereMonth('sale_date', now()->month);
            });
        }])
            ->get()
            ->filter(function ($product) {
                return $product->sale_items_count > 0;
            })
            ->sortByDesc('sale_items_count')
            ->take(5);

        return view('shop.dashboard', compact(
            'shop',
            'todaySales',
            'weekSales',
            'monthSales',
            'stockStats',
            'lowStockProducts',
            'pendingTransfers',
            'incomingTransfers',
            'recentSales',
            'recentReturns',
            'pendingReturns',
            'alerts',
            'topProducts'
        ));
    }
}
