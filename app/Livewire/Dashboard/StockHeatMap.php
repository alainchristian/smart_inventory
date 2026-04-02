<?php

namespace App\Livewire\Dashboard;

use App\Models\Box;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;

class StockHeatMap extends Component
{
    public array $matrix    = [];
    public array $products  = [];
    public array $locations = [];
    public array $summary   = ['crit' => 0, 'warn' => 0, 'good' => 0, 'overstock' => 0];

    public function mount(): void
    {
        $this->loadHeatMap();
    }

    #[On('time-filter-changed')]
    public function loadHeatMap(): void
    {
        // Columns = active warehouses (first) then active shops
        $warehouses = \App\Models\Warehouse::where('is_active', true)->get()
            ->map(fn($w) => ['id' => $w->id, 'type' => 'warehouse', 'name' => $w->name])
            ->toArray();

        $shops = \App\Models\Shop::where('is_active', true)->get()
            ->map(fn($s) => ['id' => $s->id, 'type' => 'shop', 'name' => $s->name])
            ->toArray();

        $this->locations = array_merge($warehouses, $shops);

        // Top 10 products by REVENUE in the last 30 days, with revenue stored
        $topProductsData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->where('sales.sale_date', '>=', now()->subDays(30))
            ->select('sale_items.product_id', DB::raw('SUM(sale_items.line_total) as total_revenue'))
            ->groupBy('sale_items.product_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $topProductIds = $topProductsData->pluck('product_id');
        $revenueMap    = $topProductsData->pluck('total_revenue', 'product_id');

        // Fall back to box count if no sales data
        if ($topProductIds->isEmpty()) {
            $topProductIds = Box::available()
                ->select('product_id', DB::raw('COUNT(*) as box_count'))
                ->groupBy('product_id')
                ->orderByDesc('box_count')
                ->limit(10)
                ->pluck('product_id');
            $revenueMap = collect();
        }

        if ($topProductIds->isEmpty()) {
            $this->products = [];
            $this->matrix   = [];
            $this->summary  = ['crit' => 0, 'warn' => 0, 'good' => 0, 'overstock' => 0];
            return;
        }

        $this->products = Product::whereIn('id', $topProductIds)
            ->orderByRaw('array_position(ARRAY[' . $topProductIds->implode(',') . ']::int[], id::int)')
            ->get()
            ->map(fn($p) => [
                'id'      => $p->id,
                'name'    => $p->name,
                'revenue' => (int) ($revenueMap[$p->id] ?? 0),
            ])
            ->toArray();

        // Box counts per product × location
        $boxCounts = Box::available()
            ->whereIn('product_id', $topProductIds)
            ->select('product_id', 'location_type', 'location_id', DB::raw('COUNT(*) as box_count'))
            ->groupBy('product_id', 'location_type', 'location_id')
            ->get();

        // Items remaining per product × location
        $itemsRemaining = Box::available()
            ->whereIn('product_id', $topProductIds)
            ->select('product_id', 'location_type', 'location_id', DB::raw('SUM(items_remaining) as items_total'))
            ->groupBy('product_id', 'location_type', 'location_id')
            ->get();

        // Average daily sales velocity (last 14 days) per shop × product
        $salesVelocity = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereIn('sale_items.product_id', $topProductIds)
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->where('sales.sale_date', '>=', now()->subDays(14))
            ->select('sale_items.product_id', 'sales.shop_id', DB::raw('SUM(sale_items.quantity_sold) as total_sold'))
            ->groupBy('sale_items.product_id', 'sales.shop_id')
            ->get();

        // Incoming stock: Items coming via Pending/In-Transit transfers
        $incomingTransfers = DB::table('transfer_items')
            ->join('transfers', 'transfer_items.transfer_id', '=', 'transfers.id')
            ->whereIn('transfer_items.product_id', $topProductIds)
            ->whereIn('transfers.status', ['pending', 'in_transit'])
            ->whereNull('transfers.deleted_at')
            ->select('transfer_items.product_id', 'transfers.to_shop_id', DB::raw('SUM(transfer_items.quantity_requested) as incoming_qty'))
            ->groupBy('transfer_items.product_id', 'transfers.to_shop_id')
            ->get();

        // Matrix: $matrix[$pi][$li] — product rows × location columns
        $this->matrix  = [];
        $summaryCounts = ['crit' => 0, 'warn' => 0, 'good' => 0, 'overstock' => 0];

        foreach ($this->products as $product) {
            $row = [];
            foreach ($this->locations as $location) {
                // 1. Current stock (box count)
                $count = $boxCounts
                    ->where('product_id', $product['id'])
                    ->where('location_type', $location['type'])
                    ->where('location_id', $location['id'])
                    ->first();
                $boxes = $count ? (int) $count->box_count : 0;

                // 2. Total items remaining
                $itemsInfo = $itemsRemaining
                    ->where('product_id', $product['id'])
                    ->where('location_type', $location['type'])
                    ->where('location_id', $location['id'])
                    ->first();
                $items = $itemsInfo ? (int) $itemsInfo->items_total : 0;

                $incomingQty   = 0;
                $level         = 'good';
                $daysRemaining = null;

                if ($location['type'] === 'warehouse') {
                    $level = $boxes > 0 ? 'good' : 'crit';
                } else {
                    // For shops, calculate velocity
                    $velocityInfo = $salesVelocity
                        ->where('product_id', $product['id'])
                        ->where('shop_id', $location['id'])
                        ->first();

                    $totalSold14Days = $velocityInfo ? (float) $velocityInfo->total_sold : 0;
                    $dailySales      = $totalSold14Days / 14;

                    // Incoming inventory for this shop
                    $incomingInfo = $incomingTransfers
                        ->where('product_id', $product['id'])
                        ->where('to_shop_id', $location['id'])
                        ->first();
                    $incomingQty = $incomingInfo ? (int) $incomingInfo->incoming_qty : 0;

                    if ($dailySales > 0) {
                        $daysRemaining = round($boxes / $dailySales);

                        if ($daysRemaining < 3) {
                            $level = 'crit';
                        } elseif ($daysRemaining <= 7) {
                            $level = 'warn';
                        } elseif ($daysRemaining > 21) {
                            $level = 'overstock';
                        } else {
                            $level = 'good';
                        }
                    } else {
                        $daysRemaining = 999;
                        $level         = $boxes > 0 ? 'overstock' : 'crit';
                    }
                }

                $summaryCounts[$level] = ($summaryCounts[$level] ?? 0) + 1;

                $row[] = [
                    'boxes'    => $boxes,
                    'items'    => $items,
                    'incoming' => $incomingQty,
                    'days'     => $daysRemaining,
                    'level'    => $level,
                ];
            }
            $this->matrix[] = $row;
        }

        $this->summary = $summaryCounts;
    }

    public function render()
    {
        return view('livewire.dashboard.stock-heat-map');
    }
}
