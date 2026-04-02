<?php

namespace App\Livewire\Dashboard;

use App\Models\Box;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;

class TopShops extends Component
{
    public array  $shops      = [];
    public int    $maxRevenue = 1;
    public string $period     = 'month';

    public function mount(): void
    {
        $this->loadData();
    }

    #[On('time-filter-changed')]
    public function refresh(string $period, ?string $from = null, ?string $to = null): void
    {
        $this->period = $period;
        $this->loadData();
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->loadData();
    }

    private function loadData(): void
    {
        [$start, $end] = $this->periodRange();

        $shopSales = DB::table('sales')
            ->join('shops', 'sales.shop_id', '=', 'shops.id')
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->select(
                'shops.id',
                'shops.name',
                DB::raw('SUM(sales.total) as total_sales'),
                DB::raw('COUNT(sales.id) as transaction_count')
            )
            ->groupBy('shops.id', 'shops.name')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        // Include shops with 0 sales if fewer than 5 shops have sales
        if ($shopSales->count() < 5) {
            $existingIds = $shopSales->pluck('id');
            $remaining   = DB::table('shops')
                ->whereNotIn('id', $existingIds)
                ->where('is_active', true)
                ->select('id', 'name', DB::raw('0 as total_sales'), DB::raw('0 as transaction_count'))
                ->limit(5 - $shopSales->count())
                ->get();
            $shopSales = $shopSales->merge($remaining);
        }

        $this->shops = $shopSales->values()->map(function ($shop, $idx) {
            // Total available boxes in this shop
            $totalBoxes = Box::available()
                ->where('location_type', 'shop')
                ->where('location_id', $shop->id)
                ->count();

            // Products with total stock below low_stock_threshold
            // Use get()->count() to avoid Laravel's count() subquery wrapper which
            // generates "select * from (...)" — invalid in PostgreSQL with GROUP BY.
            $lowStockCount = DB::table('boxes')
                ->join('products', 'boxes.product_id', '=', 'products.id')
                ->where('boxes.location_type', 'shop')
                ->where('boxes.location_id', $shop->id)
                ->whereIn('boxes.status', ['full', 'partial'])
                ->where('boxes.items_remaining', '>', 0)
                ->whereNull('products.deleted_at')
                ->where('products.is_active', true)
                ->select('boxes.product_id', 'products.low_stock_threshold')
                ->groupBy('boxes.product_id', 'products.low_stock_threshold')
                ->havingRaw('SUM(boxes.items_remaining) < products.low_stock_threshold')
                ->get()
                ->count();

            return [
                'name'              => $shop->name,
                'revenue'           => (int) $shop->total_sales,
                'transaction_count' => (int) $shop->transaction_count,
                'total_boxes'       => $totalBoxes,
                'low_stock_products'=> $lowStockCount,
                'rank_css'          => ['r1', 'r2', 'r3'][$idx] ?? 'r3',
                'rank'              => $idx + 1,
            ];
        })->toArray();

        $this->maxRevenue = max(collect($this->shops)->max('revenue') ?? 0, 1);
    }

    private function periodRange(): array
    {
        return match ($this->period) {
            'week'    => [now()->startOfWeek(),    now()->endOfDay()],
            'month'   => [now()->startOfMonth(),   now()->endOfDay()],
            'quarter' => [now()->startOfQuarter(), now()->endOfDay()],
            'year'    => [now()->startOfYear(),    now()->endOfDay()],
            default   => [today(),                 now()->endOfDay()],
        };
    }

    public function render()
    {
        return view('livewire.dashboard.top-shops');
    }
}
