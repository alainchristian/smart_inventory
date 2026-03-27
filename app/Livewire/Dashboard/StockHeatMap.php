<?php

namespace App\Livewire\Dashboard;

use App\Models\Box;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;

class StockHeatMap extends Component
{
    public array $matrix    = [];
    public array $products  = [];
    public array $locations = [];

    public function mount(): void
    {
        $this->loadHeatMap();
    }

    #[On('time-filter-changed')]
    public function loadHeatMap(): void
    {
        // Get all active locations
        $warehouses = Warehouse::where('is_active', true)->get()
            ->map(fn($w) => ['id' => $w->id, 'type' => 'warehouse', 'name' => $w->name]);
        $shops = Shop::where('is_active', true)->get()
            ->map(fn($s) => ['id' => $s->id, 'type' => 'shop', 'name' => $s->name]);

        $this->locations = $warehouses->merge($shops)->values()->toArray();

        // Top 10 products by total box count across available stock
        $topProductIds = Box::available()
            ->select('product_id', DB::raw('COUNT(*) as box_count'))
            ->groupBy('product_id')
            ->orderByDesc('box_count')
            ->limit(10)
            ->pluck('product_id');

        if ($topProductIds->isEmpty()) {
            $this->products = [];
            $this->matrix   = [];
            return;
        }

        $this->products = Product::whereIn('id', $topProductIds)
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'                  => $p->id,
                'name'                => $p->name,
                'low_stock_threshold' => $p->low_stock_threshold ?? $p->items_per_box ?? 10,
            ])
            ->toArray();

        // Box counts per product × location
        $boxCounts = Box::available()
            ->whereIn('product_id', $topProductIds)
            ->select('product_id', 'location_type', 'location_id', DB::raw('COUNT(*) as box_count'))
            ->groupBy('product_id', 'location_type', 'location_id')
            ->get();

        // Build array-indexed matrix
        $this->matrix = [];
        foreach ($this->products as $pi => $product) {
            $row = [];
            foreach ($this->locations as $location) {
                $count = $boxCounts
                    ->where('product_id', $product['id'])
                    ->where('location_type', $location['type'])
                    ->where('location_id', $location['id'])
                    ->first();

                $boxes     = $count ? (int) $count->box_count : 0;
                $threshold = max($product['low_stock_threshold'], 1);
                $fillRate  = min(round(($boxes / $threshold) * 100), 100);

                $row[] = [
                    'boxes'     => $boxes,
                    'fill_rate' => $fillRate,
                    'level'     => $fillRate >= 70 ? 'good' : ($fillRate >= 30 ? 'warn' : 'crit'),
                ];
            }
            $this->matrix[] = $row;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.stock-heat-map');
    }
}
