<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Sale;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesPerformance extends Component
{
    public string $chartPeriod    = 'week';
    public int    $activePeriodCol = 0;
    public array  $chartData       = [];

    public string  $period = 'today';
    public ?string $from   = null;
    public ?string $to     = null;

    public function mount(): void
    {
        $this->loadChartData();
    }

    #[On('time-filter-changed')]
    public function refresh(string $period, ?string $from = null, ?string $to = null): void
    {
        $this->period = $period;
        $this->from   = $from;
        $this->to     = $to;
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
            'today'   => Sale::notVoided()->whereDate('sale_date', today())->sum('total') / 100,
            'week'    => Sale::notVoided()->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfDay()])->sum('total') / 100,
            'month'   => Sale::notVoided()->whereBetween('sale_date', [now()->startOfMonth(), now()->endOfDay()])->sum('total') / 100,
            'quarter' => Sale::notVoided()->whereBetween('sale_date', [now()->startOfQuarter(), now()->endOfDay()])->sum('total') / 100,
        ];
    }

    private function loadChartData(): void
    {
        $labels      = [];
        $revenueData = [];
        $countData   = [];

        match ($this->chartPeriod) {
            'today'   => $this->loadHourlyData($labels, $revenueData, $countData),
            'week'    => $this->loadWeeklyData($labels, $revenueData, $countData),
            'month'   => $this->loadMonthlyData($labels, $revenueData, $countData),
            'quarter' => $this->loadQuarterlyData($labels, $revenueData, $countData),
            default   => $this->loadWeeklyData($labels, $revenueData, $countData),
        };

        $this->chartData = [
            'labels'      => $labels,
            'revenueData' => $revenueData,
            'countData'   => $countData,
        ];
    }

    // Single query - group by hour
    private function loadHourlyData(array &$labels, array &$revenueData, array &$countData): void
    {
        $rows = Sale::notVoided()
            ->whereDate('sale_date', today())
            ->select(
                DB::raw('EXTRACT(HOUR FROM sale_date)::int AS hour'),
                DB::raw('SUM(total) AS revenue'),
                DB::raw('COUNT(*) AS cnt')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        for ($h = 0; $h < 24; $h++) {
            $row           = $rows->get($h);
            $labels[]      = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
            $revenueData[] = $row ? round($row->revenue / 100, 0) : 0;
            $countData[]   = $row ? (int) $row->cnt : 0;
        }
    }

    // Single query - group by day of week
    private function loadWeeklyData(array &$labels, array &$revenueData, array &$countData): void
    {
        $startOfWeek = now()->startOfWeek();

        $rows = Sale::notVoided()
            ->whereBetween('sale_date', [$startOfWeek, now()->endOfWeek()])
            ->select(
                DB::raw('DATE(sale_date) AS day'),
                DB::raw('SUM(total) AS revenue'),
                DB::raw('COUNT(*) AS cnt')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        for ($d = 0; $d < 7; $d++) {
            $date          = $startOfWeek->copy()->addDays($d);
            $row           = $rows->get($date->format('Y-m-d'));
            $labels[]      = $date->format('D');
            $revenueData[] = $row ? round($row->revenue / 100, 0) : 0;
            $countData[]   = $row ? (int) $row->cnt : 0;
        }
    }

    // Single query - group by day of month
    private function loadMonthlyData(array &$labels, array &$revenueData, array &$countData): void
    {
        $startOfMonth = now()->startOfMonth();
        $daysInMonth  = now()->daysInMonth;

        $rows = Sale::notVoided()
            ->whereBetween('sale_date', [$startOfMonth, now()->endOfMonth()])
            ->select(
                DB::raw('EXTRACT(DAY FROM sale_date)::int AS day_num'),
                DB::raw('SUM(total) AS revenue'),
                DB::raw('COUNT(*) AS cnt')
            )
            ->groupBy('day_num')
            ->orderBy('day_num')
            ->get()
            ->keyBy('day_num');

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $row           = $rows->get($d);
            $labels[]      = (string) $d;
            $revenueData[] = $row ? round($row->revenue / 100, 0) : 0;
            $countData[]   = $row ? (int) $row->cnt : 0;
        }
    }

    // Single query - group by week number within the quarter
    // Shows 13 weekly buckets (one per week of the quarter).
    private function loadQuarterlyData(array &$labels, array &$revenueData, array &$countData): void
    {
        $startOfQuarter = now()->startOfQuarter();
        $endOfQuarter   = now()->endOfQuarter();

        $rows = Sale::notVoided()
            ->whereBetween('sale_date', [$startOfQuarter, $endOfQuarter])
            ->select(
                DB::raw('DATE_TRUNC(\'week\', sale_date)::date AS week_start'),
                DB::raw('SUM(total) AS revenue'),
                DB::raw('COUNT(*) AS cnt')
            )
            ->groupBy('week_start')
            ->orderBy('week_start')
            ->get()
            ->keyBy('week_start');

        // Walk every week in the quarter, fill zeros for empty weeks
        $current = $startOfQuarter->copy()->startOfWeek();
        $weekNum = 1;

        while ($current->lte($endOfQuarter)) {
            $key           = $current->format('Y-m-d');
            $row           = $rows->get($key);
            $labels[]      = 'W' . $weekNum;
            $revenueData[] = $row ? round($row->revenue / 100, 0) : 0;
            $countData[]   = $row ? (int) $row->cnt : 0;
            $current->addWeek();
            $weekNum++;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.sales-performance');
    }
}