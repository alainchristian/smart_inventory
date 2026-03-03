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
                    'color'         => $this->getLocationColor($item->location_type->value),
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

    private function getLocationColor(string $locationType): string
    {
        return match ($locationType) {
            'warehouse' => '#3b6fd4',
            'shop'      => '#0e9e86',
            default     => '#7c3aed',
        };
    }

    public function render()
    {
        return view('livewire.dashboard.stock-distribution');
    }
}