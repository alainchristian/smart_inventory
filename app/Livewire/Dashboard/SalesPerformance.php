<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Sale;
use App\Models\SaleItem;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;

class SalesPerformance extends Component
{
    public string $chartPeriod = 'week';
    public int $activePeriodCol = 0;
    public array $chartData = [];
    public string $period = 'today';
    public ?string $from = null;
    public ?string $to = null;

    public function mount(): void
    {
        $this->loadChartData();
    }

    #[On('time-filter-changed')]
    public function refresh(array $payload): void
    {
        $this->period = $payload['period'] ?? 'today';
        $this->from = $payload['from'] ?? null;
        $this->to = $payload['to'] ?? null;
        $this->loadChartData();
    }

    public function setChartPeriod(string $period): void
    {
        $this->chartPeriod = $period;
        $this->loadChartData();
    }

    public function setActivePeriodCol(int $col): void
    {
        $this->activePeriodCol = $col;
    }

    public function getPeriodSummaries(): array
    {
        return [
            'today' => Sale::whereDate('sale_date', today())->sum('total') / 100,
            'week' => Sale::whereBetween('sale_date', [now()->startOfWeek(), now()])->sum('total') / 100,
            'month' => Sale::whereBetween('sale_date', [now()->startOfMonth(), now()])->sum('total') / 100,
        ];
    }

    private function loadChartData(): void
    {
        [$start, $end] = $this->getChartRange();

        // Generate labels and fetch data based on the period
        $labels = [];
        $fullBoxData = [];
        $itemsData = [];

        if ($this->chartPeriod === 'today') {
            // Hourly data for today
            for ($hour = 0; $hour < 24; $hour++) {
                $labels[] = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';

                $hourStart = Carbon::today()->setHour($hour);
                $hourEnd = $hourStart->copy()->addHour();

                $fullBoxData[] = Sale::whereBetween('sale_date', [$hourStart, $hourEnd])->count();
                $itemsData[] = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereBetween('sales.sale_date', [$hourStart, $hourEnd])
                    ->sum('sale_items.quantity_sold');
            }
        } elseif ($this->chartPeriod === 'week') {
            // Daily data for the week
            $startOfWeek = now()->startOfWeek();
            for ($day = 0; $day < 7; $day++) {
                $date = $startOfWeek->copy()->addDays($day);
                $labels[] = $date->format('D');

                $fullBoxData[] = Sale::whereDate('sale_date', $date)->count();
                $itemsData[] = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereDate('sales.sale_date', $date)
                    ->sum('sale_items.quantity_sold');
            }
        } elseif ($this->chartPeriod === 'month') {
            // Daily data for the month
            $startOfMonth = now()->startOfMonth();
            $daysInMonth = now()->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $startOfMonth->copy()->addDays($day - 1);
                $labels[] = $date->format('j');

                $fullBoxData[] = Sale::whereDate('sale_date', $date)->count();
                $itemsData[] = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereDate('sales.sale_date', $date)
                    ->sum('sale_items.quantity_sold');
            }
        }

        $this->chartData = [
            'labels' => $labels,
            'fullBoxData' => $fullBoxData,
            'itemsData' => $itemsData,
        ];
    }

    private function getChartRange(): array
    {
        return match($this->chartPeriod) {
            'today' => [today(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [today(), now()->endOfDay()],
        };
    }

    public function render()
    {
        return view('livewire.dashboard.sales-performance');
    }
}
