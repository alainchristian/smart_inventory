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

/**
 * Controller responsible for displaying the Shop Manager dashboard.
 *
 * This implementation extends the default dashboard by computing additional
 * metrics required for the enhanced dashboard view. Specifically, it
 * calculates change percentages for today's KPIs relative to yesterday and
 * includes a last sync timestamp so the UI can display recency information.
 */
class DashboardController extends Controller
{
    /**
     * Display the shop dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // For shop managers, use their assigned shop
        if ($user->isShopManager()) {
            $shopId = $user->location_id;
        }

        // For owners, they can select or use first shop
        if ($user->isOwner()) {
            $shopId = request()->get('shop_id') ?? session('selected_shop_id') ?? Shop::first()?->id;

            if (!$shopId) {
                return redirect()->route('owner.dashboard')
                    ->with('error', 'No shop found. Please create a shop first.');
            }

            // Store selected shop in session
            session(['selected_shop_id' => $shopId]);
        }

        $shop = Shop::with('defaultWarehouse')->findOrFail($shopId);

        // ------------------------------------------------------------------
        // Today's sales statistics
        // ------------------------------------------------------------------
        $todaySales = [
            'total_sales'       => Sale::notVoided()
                ->where('shop_id', $shopId)
                ->whereDate('sale_date', today())
                ->sum('total') / 100,
            'transaction_count' => Sale::notVoided()
                ->where('shop_id', $shopId)
                ->whereDate('sale_date', today())
                ->count(),
            'items_sold'        => Sale::notVoided()
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

        // ------------------------------------------------------------------
        // Yesterday's sales statistics (for change calculation)
        // ------------------------------------------------------------------
        $yesterdaySales = [
            'total_sales'       => Sale::notVoided()
                ->where('shop_id', $shopId)
                ->whereDate('sale_date', today()->subDay())
                ->sum('total') / 100,
            'transaction_count' => Sale::notVoided()
                ->where('shop_id', $shopId)
                ->whereDate('sale_date', today()->subDay())
                ->count(),
            'items_sold'        => Sale::notVoided()
                ->where('shop_id', $shopId)
                ->whereDate('sale_date', today()->subDay())
                ->with('items')
                ->get()
                ->sum(function ($sale) {
                    return $sale->items->sum('quantity_sold');
                }),
            'average_transaction' => 0,
        ];

        if ($yesterdaySales['transaction_count'] > 0) {
            $yesterdaySales['average_transaction'] = $yesterdaySales['total_sales'] / $yesterdaySales['transaction_count'];
        }

        // ------------------------------------------------------------------
        // Compute change percentages (vs yesterday). If yesterday's metric
        // is zero, the change defaults to 100% (either positive or negative)
        // depending on whether there was activity today. This avoids division
        // by zero and still provides meaningful feedback in the UI.
        // ------------------------------------------------------------------
        $todaySalesChange        = $yesterdaySales['total_sales'] > 0
            ? (($todaySales['total_sales'] - $yesterdaySales['total_sales']) / $yesterdaySales['total_sales']) * 100
            : ($todaySales['total_sales'] > 0 ? 100 : 0);
        $todayTransactionsChange = $yesterdaySales['transaction_count'] > 0
            ? (($todaySales['transaction_count'] - $yesterdaySales['transaction_count']) / $yesterdaySales['transaction_count']) * 100
            : ($todaySales['transaction_count'] > 0 ? 100 : 0);
        $todayItemsChange        = $yesterdaySales['items_sold'] > 0
            ? (($todaySales['items_sold'] - $yesterdaySales['items_sold']) / $yesterdaySales['items_sold']) * 100
            : ($todaySales['items_sold'] > 0 ? 100 : 0);
        $todayAvgTxnChange       = $yesterdaySales['average_transaction'] > 0
            ? (($todaySales['average_transaction'] - $yesterdaySales['average_transaction']) / $yesterdaySales['average_transaction']) * 100
            : ($todaySales['average_transaction'] > 0 ? 100 : 0);

        // ------------------------------------------------------------------
        // This week's and month's sales totals (for summary cards)
        // ------------------------------------------------------------------
        $weekSales = Sale::notVoided()
            ->where('shop_id', $shopId)
            ->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('total') / 100;

        $monthSales = Sale::notVoided()
            ->where('shop_id', $shopId)
            ->whereYear('sale_date', now()->year)
            ->whereMonth('sale_date', now()->month)
            ->sum('total') / 100;

        // ------------------------------------------------------------------
        // Stock levels for this shop
        // ------------------------------------------------------------------
        $stockStats = [
            'total_boxes'   => Box::where('location_type', 'shop')
                ->where('location_id', $shopId)
                ->count(),
            'full_boxes'    => Box::where('location_type', 'shop')
                ->where('location_id', $shopId)
                ->where('status', 'full')
                ->count(),
            'partial_boxes' => Box::where('location_type', 'shop')
                ->where('location_id', $shopId)
                ->where('status', 'partial')
                ->count(),
            'total_items'   => Box::where('location_type', 'shop')
                ->where('location_id', $shopId)
                ->sum('items_remaining'),
        ];

        // ------------------------------------------------------------------
        // Low stock products
        // ------------------------------------------------------------------
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

        // ------------------------------------------------------------------
        // Pending transfer requests destined for this shop
        // ------------------------------------------------------------------
        $pendingTransfers = Transfer::where('to_shop_id', $shopId)
            ->where('status', TransferStatus::PENDING)
            ->with(['fromWarehouse', 'requestedBy', 'items.product'])
            ->orderBy('requested_at', 'desc')
            ->limit(5)
            ->get();

        // ------------------------------------------------------------------
        // Transfers currently en route to this shop
        // ------------------------------------------------------------------
        $incomingTransfers = Transfer::where('to_shop_id', $shopId)
            ->whereIn('status', [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED])
            ->with(['fromWarehouse', 'transporter'])
            ->orderBy('shipped_at', 'desc')
            ->limit(5)
            ->get();

        // ------------------------------------------------------------------
        // Recent sales and returns
        // ------------------------------------------------------------------
        $recentSales = Sale::notVoided()
            ->where('shop_id', $shopId)
            ->with(['soldBy', 'items'])
            ->orderBy('sale_date', 'desc')
            ->limit(10)
            ->get();

        $recentReturns = ReturnModel::where('shop_id', $shopId)
            ->with(['processedBy', 'items.product'])
            ->orderBy('processed_at', 'desc')
            ->limit(5)
            ->get();

        // ------------------------------------------------------------------
        // Pending returns awaiting approval
        // ------------------------------------------------------------------
        $pendingReturns = ReturnModel::where('shop_id', $shopId)
            ->pendingApproval()
            ->with(['items.product'])
            ->orderBy('processed_at', 'asc')
            ->get();

        // ------------------------------------------------------------------
        // Alerts for this shop or general alerts relevant to this user
        // ------------------------------------------------------------------
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

        // ------------------------------------------------------------------
        // Top selling products for this month
        // ------------------------------------------------------------------
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

        // ------------------------------------------------------------------
        // Determine the last synchronization time. If you have a sync log or
        // event store, you should retrieve the actual last sync timestamp here.
        // For now, we default to the current time to indicate that data is
        // freshly generated.
        // ------------------------------------------------------------------
        $lastSync = now();

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
            'topProducts',
            'todaySalesChange',
            'todayTransactionsChange',
            'todayItemsChange',
            'todayAvgTxnChange',
            'lastSync'
        ));
    }
}