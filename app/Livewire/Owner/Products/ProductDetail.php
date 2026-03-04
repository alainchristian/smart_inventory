<?php

namespace App\Livewire\Owner\Products;

use App\Models\Box;
use App\Models\BoxMovement;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductDetail extends Component
{
    public ?int $productId = null;

    #[On('open-product-detail')]
    public function openFor(int $productId): void
    {
        $this->productId = $productId;
    }

    public function close(): void
    {
        $this->productId = null;
    }

    public function render()
    {
        if (! $this->productId) {
            return view('livewire.owner.products.product-detail', [
                'product'       => null,
                'chartData'     => null,
                'stockByLoc'    => [],
                'overrideLog'   => [],
                'recentMoves'   => [],
            ]);
        }

        $product = Product::with('category')->findOrFail($this->productId);

        // -- 1. 30-day revenue trend (daily buckets) ----
        $chartRaw = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sale_items.product_id', $this->productId)
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->where('sales.sale_date', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw("DATE(sales.sale_date) as day, SUM(sale_items.line_total) as revenue, SUM(sale_items.quantity_sold) as units")
            ->groupByRaw("DATE(sales.sale_date)")
            ->orderByRaw("DATE(sales.sale_date)")
            ->get()
            ->keyBy('day');

        // Build 30-day array filling gaps with 0
        $chartLabels  = [];
        $chartRevenue = [];
        $chartUnits   = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $chartLabels[]  = now()->subDays($i)->format('M d');
            $chartRevenue[] = isset($chartRaw[$day]) ? (int) $chartRaw[$day]->revenue : 0;
            $chartUnits[]   = isset($chartRaw[$day]) ? (int) $chartRaw[$day]->units   : 0;
        }

        $chartData = [
            'labels'  => $chartLabels,
            'revenue' => $chartRevenue,
            'units'   => $chartUnits,
        ];

        // -- 2. Stock by location (each warehouse + each shop) ----
        // Aggregate boxes at each location
        $boxAgg = DB::table('boxes')
            ->where('product_id', $this->productId)
            ->whereIn('status', ['full', 'partial'])
            ->where('items_remaining', '>', 0)
            ->selectRaw("location_type::text as loc_type, location_id, SUM(items_remaining) as items, COUNT(*) as boxes")
            ->groupBy('location_type', 'location_id')
            ->get();

        $warehouses = Warehouse::pluck('name', 'id');
        $shops      = Shop::pluck('name', 'id');

        $stockByLoc = $boxAgg->map(function ($row) use ($warehouses, $shops) {
            $name = $row->loc_type === 'warehouse'
                ? ($warehouses[$row->location_id] ?? 'Warehouse #' . $row->location_id)
                : ($shops[$row->location_id]      ?? 'Shop #'      . $row->location_id);

            return [
                'type'  => $row->loc_type,
                'name'  => $name,
                'items' => (int) $row->items,
                'boxes' => (int) $row->boxes,
            ];
        })->sortBy('name')->values()->toArray();

        // -- 3. Price modification log (last 30 sales with override for this product)
        $overrideLog = SaleItem::with(['sale' => fn ($q) => $q->with(['shop', 'soldBy'])])
            ->where('product_id', $this->productId)
            ->where('price_was_modified', true)
            ->whereHas('sale', fn ($q) => $q->whereNull('voided_at')->whereNull('deleted_at'))
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($si) => [
                'date'          => $si->sale->sale_date->format('M d, Y'),
                'shop'          => $si->sale->shop->name ?? '--',
                'sold_by'       => $si->sale->soldBy->name ?? '--',
                'original'      => number_format($si->original_unit_price),
                'actual'        => number_format($si->actual_unit_price),
                'diff_pct'      => $si->original_unit_price > 0
                    ? round(($si->original_unit_price - $si->actual_unit_price) / $si->original_unit_price * 100, 1)
                    : 0,
                'reason'        => $si->price_modification_reason ?? '--',
                'reference'     => $si->price_modification_reference ?? '--',
            ])
            ->toArray();

        // -- 4. Recent box movements for this product (last 15) ----
        $recentMoves = BoxMovement::with(['box.product', 'movedBy'])
            ->whereHas('box', fn ($q) => $q->where('product_id', $this->productId))
            ->orderByDesc('moved_at')
            ->limit(15)
            ->get()
            ->map(fn ($m) => [
                'date'          => $m->moved_at ? $m->moved_at->diffForHumans() : 'Unknown',
                'box_code'      => $m->box->box_code ?? '--',
                'type'          => $m->movement_type,
                'from'          => $this->locationLabel($m->from_location_type?->value, $m->from_location_id, $warehouses, $shops),
                'to'            => $this->locationLabel($m->to_location_type?->value,   $m->to_location_id,   $warehouses, $shops),
                'items'         => $m->items_moved ?? 0,
                'moved_by'      => $m->movedBy->name ?? '--',
            ])
            ->toArray();

        return view('livewire.owner.products.product-detail', [
            'product'     => $product,
            'chartData'   => $chartData,
            'stockByLoc'  => $stockByLoc,
            'overrideLog' => $overrideLog,
            'recentMoves' => $recentMoves,
        ]);
    }

    private function locationLabel(?string $type, ?int $id, $warehouses, $shops): string
    {
        if (! $type || ! $id) {
            return '--';
        }

        return $type === 'warehouse'
            ? ($warehouses[$id] ?? 'Warehouse #' . $id)
            : ($shops[$id]      ?? 'Shop #'      . $id);
    }
}