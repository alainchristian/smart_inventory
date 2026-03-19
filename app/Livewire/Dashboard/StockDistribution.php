<?php

namespace App\Livewire\Dashboard;

use App\Models\Box;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StockDistribution extends Component
{
    /**
     * Stock distribution by location.
     *
     * Uses Box::available() — status IN (full, partial) AND items_remaining > 0.
     * This is the SAME filter used by Inventory Health and OpsKpiRow,
     * so the "Total" in the donut chart centre matches "Sellable Boxes" everywhere.
     *
     * Damaged boxes are excluded from the chart because they are not available
     * for sale and would create confusion with the other dashboard sections.
     */
    public function getStockDistributionProperty(): array
    {
        return Box::available()
            ->with('location')
            ->select('location_type', 'location_id', DB::raw('COUNT(*) as box_count'))
            ->groupBy('location_type', 'location_id')
            ->get()
            ->map(function ($item) {
                return [
                    'location_name' => $item->location ? $item->location->name : 'Unknown',
                    'location_type' => $item->location_type->value,
                    'box_count'     => (int) $item->box_count,
                    'color'         => $this->getLocationColor(
                        $item->location_type->value,
                        (int) $item->location_id
                    ),
                ];
            })
            ->sortByDesc('box_count')
            ->values()
            ->toArray();
    }

    /**
     * Total sellable boxes — same definition as all other dashboard sections.
     */
    public function getTotalBoxesProperty(): int
    {
        return Box::available()->count();
    }

    /**
     * Damaged boxes shown as a separate informational figure below the chart.
     * Never mixed into the total.
     */
    public function getDamagedBoxesProperty(): int
    {
        return Box::where('status', 'damaged')->count();
    }

    public function getLocationStatsProperty(): array
    {
        return \Illuminate\Support\Facades\DB::table('boxes')
            ->join('warehouses', function ($j) {
                $j->on('boxes.location_id', '=', 'warehouses.id')
                  ->where('boxes.location_type', '=', 'warehouse');
            })
            ->whereIn('boxes.status', ['full', 'partial'])
            ->where('boxes.items_remaining', '>', 0)
            ->selectRaw("
                warehouses.name as location_name,
                'warehouse' as location_type,
                SUM(CASE WHEN boxes.status = 'full' THEN 1 ELSE 0 END) as full_boxes,
                SUM(CASE WHEN boxes.status = 'partial' THEN 1 ELSE 0 END) as partial_boxes,
                SUM(boxes.items_remaining) as total_items
            ")
            ->groupBy('warehouses.name')
            ->unionAll(
                \Illuminate\Support\Facades\DB::table('boxes')
                    ->join('shops', function ($j) {
                        $j->on('boxes.location_id', '=', 'shops.id')
                          ->where('boxes.location_type', '=', 'shop');
                    })
                    ->whereIn('boxes.status', ['full', 'partial'])
                    ->where('boxes.items_remaining', '>', 0)
                    ->selectRaw("
                        shops.name as location_name,
                        'shop' as location_type,
                        SUM(CASE WHEN boxes.status = 'full' THEN 1 ELSE 0 END) as full_boxes,
                        SUM(CASE WHEN boxes.status = 'partial' THEN 1 ELSE 0 END) as partial_boxes,
                        SUM(boxes.items_remaining) as total_items
                    ")
                    ->groupBy('shops.name')
            )
            ->orderByDesc('total_items')
            ->get()
            ->map(fn($r) => [
                'name'          => $r->location_name,
                'type'          => $r->location_type,
                'full_boxes'    => (int) $r->full_boxes,
                'partial_boxes' => (int) $r->partial_boxes,
                'total_items'   => (int) $r->total_items,
            ])
            ->toArray();
    }

    private function getLocationColor(string $locationType, int $locationId): string
    {
        // Warehouses always get blue family
        $warehousePalette = [
            '#3b6fd4', '#1d4ed8', '#2563eb', '#1e40af', '#3730a3',
        ];

        // Shops get a varied palette so each shop has a distinct color
        $shopPalette = [
            '#0e9e86', // teal
            '#7c3aed', // violet
            '#d97706', // amber
            '#db2777', // pink
            '#0891b2', // cyan
            '#65a30d', // lime
            '#dc2626', // red
            '#9333ea', // purple
            '#0284c7', // sky
            '#16a34a', // green
        ];

        if ($locationType === 'warehouse') {
            // Cycle through blue family based on locationId
            return $warehousePalette[$locationId % count($warehousePalette)];
        }

        // Shops: use locationId to consistently pick a color
        return $shopPalette[$locationId % count($shopPalette)];
    }

    public function render()
    {
        return view('livewire.dashboard.stock-distribution');
    }
}