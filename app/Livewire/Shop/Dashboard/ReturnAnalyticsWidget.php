<?php

namespace App\Livewire\Shop\Dashboard;

use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\Shop;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ReturnAnalyticsWidget extends Component
{
    public $shopId;
    public $period = '30'; // days

    public function mount()
    {
        $user = auth()->user();

        if ($user->isShopManager()) {
            $this->shopId = $user->location_id;
        }
    }

    protected function getAnalytics()
    {
        $startDate = now()->subDays((int) $this->period);

        // Base query for returns in period
        $returnsQuery = ReturnModel::where('shop_id', $this->shopId)
            ->where('processed_at', '>=', $startDate)
            ->whereNull('deleted_at');

        // Total returns
        $totalReturns = $returnsQuery->count();

        // Total sales in same period
        $totalSales = Sale::where('shop_id', $this->shopId)
            ->where('sale_date', '>=', $startDate)
            ->whereNull('voided_at')
            ->count();

        // Return rate
        $returnRate = $totalSales > 0 ? round(($totalReturns / $totalSales) * 100, 1) : 0;

        // Total refund amount
        $totalRefunds = $returnsQuery->sum('refund_amount');

        // Returns by type
        $refundCount = (clone $returnsQuery)->where('is_exchange', false)->count();
        $exchangeCount = (clone $returnsQuery)->where('is_exchange', true)->count();

        // Returns by reason
        $returnsByReason = (clone $returnsQuery)
            ->select('reason', DB::raw('count(*) as count'))
            ->groupBy('reason')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'reason' => \App\Enums\ReturnReason::from($item->reason)->label(),
                    'count' => $item->count,
                ];
            });

        // Most returned products
        $mostReturnedProducts = DB::table('return_items')
            ->join('returns', 'return_items.return_id', '=', 'returns.id')
            ->join('products', 'return_items.product_id', '=', 'products.id')
            ->where('returns.shop_id', $this->shopId)
            ->where('returns.processed_at', '>=', $startDate)
            ->whereNull('returns.deleted_at')
            ->select(
                'products.name',
                DB::raw('SUM(return_items.quantity_returned) as total_returned')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_returned')
            ->limit(5)
            ->get();

        // Pending approvals
        $pendingApprovals = ReturnModel::where('shop_id', $this->shopId)
            ->whereNull('approved_at')
            ->whereNull('deleted_at')
            ->count();

        return [
            'total_returns' => $totalReturns,
            'total_sales' => $totalSales,
            'return_rate' => $returnRate,
            'total_refunds' => $totalRefunds,
            'refund_count' => $refundCount,
            'exchange_count' => $exchangeCount,
            'returns_by_reason' => $returnsByReason,
            'most_returned_products' => $mostReturnedProducts,
            'pending_approvals' => $pendingApprovals,
        ];
    }

    public function render()
    {
        $analytics = $this->getAnalytics();

        return view('livewire.shop.dashboard.return-analytics-widget', [
            'analytics' => $analytics,
        ]);
    }
}
