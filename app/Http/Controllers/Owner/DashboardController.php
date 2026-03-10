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
use App\Models\ActivityLog;
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

        // ─── System-wide analytics (not date-dependent) ──────────────────────
        $stats = [
            'total_users'       => User::active()->count(),
            'total_warehouses'  => Warehouse::active()->count(),
            'total_shops'       => Shop::active()->count(),
            'total_products'    => Product::active()->count(),

            // FIX #4 — "total_boxes" now counts only operationally active boxes
            // (full / partial with items remaining) for the inventory context.
            // Rename to 'active_boxes' and keep a separate raw count for ops.
            'active_boxes'      => Box::available()->count(),
            'total_boxes'       => Box::count(),                     // kept for ops visibility
            'warehouse_boxes'   => Box::where('location_type', 'warehouse')->available()->count(),
            'shop_boxes'        => Box::where('location_type', 'shop')->available()->count(),
        ];

        // ─── FIX #1 + #3 + #8 — Inventory valuation ─────────────────────────
        // Unified canonical filter: Box::available() ≡ status IN ('full','partial')
        //   AND items_remaining > 0.
        // Single database-level SUM — no PHP-side N+1 loop.
        $inv = Box::available()
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('
                SUM(boxes.items_remaining * products.purchase_price) AS cost_value,
                SUM(boxes.items_remaining * products.selling_price)  AS retail_value,
                SUM(boxes.items_remaining)                           AS total_items
            ')
            ->first();

        $stats['inventory_value']     = $inv->cost_value   ?? 0;
        $stats['retail_value']        = $inv->retail_value ?? 0;
        $stats['potential_profit']    = $stats['retail_value'] - $stats['inventory_value'];

        // FIX #3 — items_in_stock now matches the same filter as the valuation
        $stats['total_items_in_stock'] = (int) ($inv->total_items ?? 0);

        // Separate damaged item count (operational, not financial)
        $stats['damaged_items'] = Box::where('status', 'damaged')
            ->where('items_remaining', '>', 0)
            ->sum('items_remaining');

        // ─── Recent transfers ─────────────────────────────────────────────────
        $recentTransfers = Transfer::with(['fromWarehouse', 'toShop', 'requestedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Transfers by status
        $transferStats = [
            'pending'           => Transfer::where('status', TransferStatus::PENDING)->count(),
            'in_transit'        => Transfer::where('status', TransferStatus::IN_TRANSIT)->count(),
            'with_discrepancies'=> Transfer::where('has_discrepancy', true)
                ->where('status', TransferStatus::RECEIVED)
                ->count(),
        ];

        // ─── FIX #7 — Sales filter branches: fresh query objects every time ──
        // Never reuse the same Eloquent builder reference across multiple
        // chained calls — each call mutates the builder and stacks WHERE clauses.
        if ($filter === 'today') {
            $todaySales    = Sale::notVoided()->whereDate('sale_date', today())->sum('total');
            $yesterdaySales = Sale::notVoided()->whereDate('sale_date', today()->subDay())->sum('total');

            $salesStats = [
                'period_revenue'    => $todaySales,
                'period_count'      => Sale::notVoided()->whereDate('sale_date', today())->count(),
                'this_week'         => Sale::notVoided()->whereBetween('sale_date', [now()->startOfWeek(), now()])->sum('total'),
                'this_month'        => Sale::notVoided()->whereYear('sale_date', now()->year)->whereMonth('sale_date', now()->month)->sum('total'),
                'growth_pct'        => $yesterdaySales > 0
                    ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1)
                    : ($todaySales > 0 ? 100 : 0),
            ];

        } elseif ($filter === 'week') {
            $weekStart = now()->startOfWeek();
            $weekEnd   = now()->endOfWeek();

            $salesStats = [
                // Key renamed: was 'today' but held week values — now honest
                'period_revenue'    => Sale::notVoided()->whereBetween('sale_date', [$weekStart, $weekEnd])->sum('total'),
                'period_count'      => Sale::notVoided()->whereBetween('sale_date', [$weekStart, $weekEnd])->count(),
                'this_week'         => Sale::notVoided()->whereBetween('sale_date', [$weekStart, $weekEnd])->sum('total'),
                'this_month'        => Sale::notVoided()->whereYear('sale_date', now()->year)->whereMonth('sale_date', now()->month)->sum('total'),
                'growth_pct'        => 0, // computed if needed vs. previous week
            ];

        } elseif ($filter === 'month') {
            $salesStats = [
                'period_revenue'    => Sale::notVoided()->whereYear('sale_date', now()->year)->whereMonth('sale_date', now()->month)->sum('total'),
                'period_count'      => Sale::notVoided()->whereYear('sale_date', now()->year)->whereMonth('sale_date', now()->month)->count(),
                'this_week'         => Sale::notVoided()->whereBetween('sale_date', [now()->startOfWeek(), now()])->sum('total'),
                'this_month'        => Sale::notVoided()->whereYear('sale_date', now()->year)->whereMonth('sale_date', now()->month)->sum('total'),
                'growth_pct'        => 0,
            ];

        } elseif ($filter === 'custom' && $fromDate && $toDate) {
            $from = Carbon::parse($fromDate)->startOfDay();
            $to   = Carbon::parse($toDate)->endOfDay();

            $salesStats = [
                'period_revenue'    => Sale::notVoided()->whereBetween('sale_date', [$from, $to])->sum('total'),
                'period_count'      => Sale::notVoided()->whereBetween('sale_date', [$from, $to])->count(),
                'this_week'         => Sale::notVoided()->whereBetween('sale_date', [now()->startOfWeek(), now()])->sum('total'),
                'this_month'        => Sale::notVoided()->whereYear('sale_date', now()->year)->whereMonth('sale_date', now()->month)->sum('total'),
                'growth_pct'        => 0,
            ];

        } else {
            // Fallback: today
            $salesStats = [
                'period_revenue'    => Sale::notVoided()->whereDate('sale_date', today())->sum('total'),
                'period_count'      => Sale::notVoided()->whereDate('sale_date', today())->count(),
                'this_week'         => Sale::notVoided()->whereBetween('sale_date', [now()->startOfWeek(), now()])->sum('total'),
                'this_month'        => Sale::notVoided()->whereYear('sale_date', now()->year)->whereMonth('sale_date', now()->month)->sum('total'),
                'growth_pct'        => 0,
            ];
        }

        // ─── Sales Chart Data — 7 days ────────────────────────────────────────
        $salesChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $salesChartData[] = [
                'label'        => $date->format('D'),
                'date'         => $date->toDateString(),
                'revenue'      => Sale::notVoided()->whereDate('sale_date', $date)->sum('total'),
                'transactions' => Sale::notVoided()->whereDate('sale_date', $date)->count(),
            ];
        }

        // ─── Shop Stock Fill Data ─────────────────────────────────────────────
        $shopStockFill = Shop::active()->get()->map(function ($shop) {
            $boxes = Box::where('location_type', 'shop')
                ->where('location_id', $shop->id)
                ->available()
                ->selectRaw('SUM(items_remaining) as remaining, SUM(items_total) as total')
                ->first();

            $fillPct = ($boxes && $boxes->total > 0)
                ? round(($boxes->remaining / $boxes->total) * 100)
                : 0;

            return [
                'name'     => $shop->name,
                'fill_pct' => $fillPct,
            ];
        });

        // ─── Recent Activities ────────────────────────────────────────────────
        $recentActivities = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('owner.dashboard', compact(
            'stats',
            'recentTransfers',
            'transferStats',
            'salesStats',
            'salesChartData',
            'shopStockFill',
            'recentActivities',
            'filter',
            'fromDate',
            'toDate',
        ));
    }

    /**
     * Compute date range from filter name.
     */
    private function getDateRange(string $filter, ?string $from, ?string $to): array
    {
        return match ($filter) {
            'week'    => [now()->startOfWeek(),    now()->endOfDay()],
            'month'   => [now()->startOfMonth(),   now()->endOfDay()],
            'quarter' => [now()->startOfQuarter(), now()->endOfDay()],
            'year'    => [now()->startOfYear(),    now()->endOfDay()],
            'custom'  => [
                Carbon::parse($from ?? today())->startOfDay(),
                Carbon::parse($to   ?? today())->endOfDay(),
            ],
            default   => [today()->startOfDay(), now()->endOfDay()],
        };
    }
}