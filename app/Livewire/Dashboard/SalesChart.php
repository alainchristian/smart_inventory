<?php

namespace App\Livewire\Dashboard;

use App\Enums\SaleType;
use App\Models\Sale;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SalesChart extends Component
{
    /**
     * Get sales data for the last 7 days grouped by sale type
     */
    public function getSalesDataProperty(): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(6);

        // Get sales grouped by date and type
        $salesByType = Sale::notVoided()
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(sale_date) as date'),
                'type',
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date', 'type')
            ->get()
            ->groupBy('date');

        // Create array of all dates in range
        $period = CarbonPeriod::create($startDate, $endDate);
        $dates = [];
        $fullBoxData = [];
        $individualItemsData = [];

        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            $dates[] = $date->format('M d'); // Format for display

            $dayData = $salesByType->get($dateKey, collect());

            // Get full box sales
            $fullBoxSale = $dayData->first(function($sale) {
                return $sale->type === SaleType::FULL_BOX;
            });
            $fullBoxData[] = $fullBoxSale ? round($fullBoxSale->revenue / 100, 2) : 0;

            // Get individual items sales
            $individualSale = $dayData->first(function($sale) {
                return $sale->type === SaleType::INDIVIDUAL_ITEMS;
            });
            $individualItemsData[] = $individualSale ? round($individualSale->revenue / 100, 2) : 0;
        }

        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Full Box',
                    'data' => $fullBoxData,
                    'backgroundColor' => '#4f7cff',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Individual Items',
                    'data' => $individualItemsData,
                    'backgroundColor' => '#00d4aa',
                    'borderRadius' => 4,
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.sales-chart');
    }
}
