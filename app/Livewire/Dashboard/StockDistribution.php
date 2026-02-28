<?php

namespace App\Livewire\Dashboard;

use App\Enums\BoxStatus;
use App\Models\Box;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StockDistribution extends Component
{
    /**
     * Get stock distribution data by location
     */
    public function getStockDistributionProperty(): array
    {
        // Get boxes count grouped by location
        $distribution = Box::whereIn('status', [
            BoxStatus::FULL,
            BoxStatus::PARTIAL,
            BoxStatus::DAMAGED
        ])
        ->with('location')
        ->select('location_type', 'location_id', DB::raw('COUNT(*) as box_count'))
        ->groupBy('location_type', 'location_id')
        ->get()
        ->map(function ($item) {
            return [
                'location_name' => $item->location ? $item->location->name : 'Unknown',
                'location_type' => $item->location_type->value,
                'box_count' => $item->box_count,
                'color' => $this->getLocationColor($item->location_type->value),
            ];
        })
        ->sortByDesc('box_count')
        ->values()
        ->toArray();

        return $distribution;
    }

    /**
     * Get total boxes count
     */
    public function getTotalBoxesProperty(): int
    {
        return Box::whereIn('status', [
            BoxStatus::FULL,
            BoxStatus::PARTIAL,
            BoxStatus::DAMAGED
        ])->count();
    }

    /**
     * Get location type color
     */
    private function getLocationColor(string $locationType): string
    {
        return match($locationType) {
            'warehouse' => '#4f7cff', // accent blue
            'shop' => '#00d4aa',      // green
            default => '#8b5cf6',      // violet
        };
    }

    public function render()
    {
        return view('livewire.dashboard.stock-distribution');
    }
}
