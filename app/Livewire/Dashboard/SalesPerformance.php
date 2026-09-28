<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\ResolvesBusinessPeriod;
use Livewire\Component;
use App\Models\Sale;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesPerformance extends Component
{
    use ResolvesBusinessPeriod;

    public string  $chartPeriod = 'daily'; // daily | weekly | monthly
    public array   $chartData   = [];
    public bool    $loaded      = false;

    public string  $period = 'today'; // follows TimeFilter's default
    public ?string $from   = null;
    public ?string $to     = null;

    public function mount(): void
    {
        // Data loaded via wire:init="loadChart" after first render
    }

    public function loadChart(): void
    {
        $this->loadChartData();
        $this->loaded = true;
    }

    #[On('time-filter-changed')]
    public function refresh(string $period, ?string $from = null, ?string $to = null): void
    {
        $this->period = $period;
        $this->from   = $from;
        $this->to     = $to;
        $this->loadChartData();
        $this->loaded = true;
    }

    public function setChartPeriod(string $period): void
    {
        $this->chartPeriod = $period;
        $this->loadChartData();
    }

    private function loadChartData(): void
    {
        // Local (business-timezone) calendar days of the selected period. All
        // bucketing and labels use these; queries on the UTC sale_date column
        // get UTC bounds and group by the sale's LOCAL date/week/month.
        [$from, $to] = $this->businessPeriodDates();

        $labels      = [];
        $revenueData = [];
        $profitData  = [];
        $netData     = [];

        match ($this->chartPeriod) {
            'weekly'  => $this->loadByWeek($from, $to, $labels, $revenueData, $profitData, $netData),
            'monthly' => $this->loadByMonth($from, $to, $labels, $revenueData, $profitData, $netData),
            default   => $from->isSameDay($to)
                // A single day as one daily point is no trend — show it by hour
                ? $this->loadByHour($from, $labels, $revenueData, $profitData, $netData)
                : $this->loadByDay($from, $to, $labels, $revenueData, $profitData, $netData),
        };

        $this->chartData = [
            'labels'      => $labels,
            'revenueData' => $revenueData,
            'profitData'  => $profitData,
            'netData'     => $netData,
        ];
    }

    /** SQL: sale_date converted to its business-timezone wall time. */
    private function localTs(string $column): string
    {
        return "({$column} AT TIME ZONE 'UTC' AT TIME ZONE '" . config('tenant.timezone') . "')";
    }

    /**
     * Revenue, gross profit and expenses per bucket between two local days.
     * $bucketSql turns a local timestamp/date SQL expression into the bucket key.
     */
    private function series(Carbon $from, Carbon $to, callable $bucketSql): array
    {
        $utcFrom = $from->copy()->startOfDay()->utc();
        $utcTo   = $to->copy()->endOfDay()->utc();
        $saleKey = $bucketSql($this->localTs('sale_date'));
        $itemKey = $bucketSql($this->localTs('sales.sale_date'));

        $rev = Sale::notVoided()
            ->whereBetween('sale_date', [$utcFrom, $utcTo])
            ->selectRaw("{$saleKey} AS k, SUM(total) AS v")
            ->groupBy('k')->pluck('v', 'k');

        $profit = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [$utcFrom, $utcTo])
            ->selectRaw("{$itemKey} AS k, SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) AS v")
            ->groupBy('k')->pluck('v', 'k');

        // session_date is already a local DATE
        $exp = DB::table('expenses')
            ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
            ->whereNull('expenses.deleted_at')
            ->where('expenses.is_system_generated', false)
            ->whereBetween('daily_sessions.session_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw($bucketSql('daily_sessions.session_date') . ' AS k, SUM(expenses.amount) AS v')
            ->groupBy('k')->pluck('v', 'k');

        return [$rev, $profit, $exp];
    }

    private function push(string $key, string $label, array $series, array &$labels, array &$rev, array &$profit, array &$net): void
    {
        [$r, $p, $e] = [(float) ($series[0][$key] ?? 0), (float) ($series[1][$key] ?? 0), (float) ($series[2][$key] ?? 0)];
        $labels[] = $label;
        $rev[]    = round($r);
        $profit[] = round($p);
        $net[]    = round($p - $e);
    }

    private function loadByHour(Carbon $day, array &$labels, array &$rev, array &$profit, array &$net): void
    {
        $series = $this->series($day, $day, fn ($x) => "TO_CHAR(({$x})::timestamp, 'HH24')");
        // Expenses belong to the day, not an hour: the hourly view plots sales and
        // gross profit only (net equals gross); daily/weekly/monthly subtract expenses.
        $series[2] = collect();

        for ($h = 0; $h < 24; $h++) {
            $key = sprintf('%02d', $h);
            $this->push($key, $key . ':00', $series, $labels, $rev, $profit, $net);
        }
    }

    private function loadByDay(Carbon $from, Carbon $to, array &$labels, array &$rev, array &$profit, array &$net): void
    {
        // At most the last 31 days of the period
        $from = $from->diffInDays($to) > 30 ? $to->copy()->subDays(30) : $from->copy();
        $series = $this->series($from, $to, fn ($x) => "TO_CHAR(({$x})::date, 'YYYY-MM-DD')");

        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $this->push($d->format('Y-m-d'), $d->format('M j'), $series, $labels, $rev, $profit, $net);
        }
    }

    private function loadByWeek(Carbon $from, Carbon $to, array &$labels, array &$rev, array &$profit, array &$net): void
    {
        // Weeks (Mon–Sun) touching the period, at most the last 12
        $first = $from->copy()->startOfWeek();
        $last  = $to->copy()->startOfWeek();
        if ($first->diffInWeeks($last) > 11) {
            $first = $last->copy()->subWeeks(11);
        }
        $series = $this->series(max($first, $from), $to, fn ($x) => "TO_CHAR(DATE_TRUNC('week', ({$x})::timestamp), 'YYYY-MM-DD')");

        for ($w = $first->copy(); $w->lte($last); $w->addWeek()) {
            $this->push($w->format('Y-m-d'), $w->format('M j'), $series, $labels, $rev, $profit, $net);
        }
    }

    private function loadByMonth(Carbon $from, Carbon $to, array &$labels, array &$rev, array &$profit, array &$net): void
    {
        // Months touching the period, at most the last 12
        $first = $from->copy()->startOfMonth();
        $last  = $to->copy()->startOfMonth();
        if ($first->diffInMonths($last) > 11) {
            $first = $last->copy()->subMonths(11);
        }
        $series = $this->series(max($first, $from), $to, fn ($x) => "TO_CHAR(({$x})::date, 'YYYY-MM')");

        for ($m = $first->copy(); $m->lte($last); $m->addMonth()) {
            $this->push($m->format('Y-m'), $m->format('M Y'), $series, $labels, $rev, $profit, $net);
        }
    }

    public function render()
    {
        return view('livewire.dashboard.sales-performance');
    }
}
