<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Sale;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesPerformance extends Component
{
    public string  $chartPeriod = 'daily'; // daily | weekly | monthly
    public array   $chartData   = [];
    public bool    $loaded      = false;

    public string  $period = 'week';
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

    private function periodRange(): array
    {
        return match ($this->period) {
            'today'      => [today()->startOfDay(), now()->endOfDay()],
            'yesterday'  => [today()->subDay()->startOfDay(), today()->subDay()->endOfDay()],
            'week'       => [now()->startOfWeek(), now()->endOfDay()],
            'month'      => [now()->startOfMonth(), now()->endOfDay()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'last_30'    => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'custom'     => [
                Carbon::parse($this->from ?? today())->startOfDay(),
                Carbon::parse($this->to   ?? today())->endOfDay(),
            ],
            default      => [today()->startOfDay(), now()->endOfDay()],
        };
    }

    private function loadChartData(): void
    {
        [$start, $end] = $this->periodRange();

        $labels      = [];
        $revenueData = [];
        $profitData  = [];
        $netData     = [];

        match ($this->chartPeriod) {
            'weekly'  => $this->loadByWeek($start, $end, $labels, $revenueData, $profitData, $netData),
            'monthly' => $this->loadByMonth($start, $end, $labels, $revenueData, $profitData, $netData),
            default   => $this->loadByDay($start, $end, $labels, $revenueData, $profitData, $netData),
        };

        $this->chartData = [
            'labels'      => $labels,
            'revenueData' => $revenueData,
            'profitData'  => $profitData,
            'netData'     => $netData,
        ];
    }

    private function loadByDay(Carbon $start, Carbon $end, array &$labels, array &$rev, array &$profit, array &$net): void
    {
        $days = (int) $start->diffInDays($end) + 1;
        $days = min($days, 31);
        $s    = $days < 31 ? $start : $end->copy()->subDays(30)->startOfDay();

        $revRows = Sale::notVoided()
            ->whereBetween('sale_date', [$s, $end])
            ->select(DB::raw('DATE(sale_date) AS day'), DB::raw('SUM(total) AS revenue'))
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');

        $profRows = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [$s, $end])
            ->select(
                DB::raw('DATE(sales.sale_date) AS day'),
                DB::raw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) AS profit')
            )
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');

        $expRows = DB::table('expenses')
            ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
            ->whereNull('expenses.deleted_at')
            ->where('expenses.is_system_generated', false)
            ->whereBetween('daily_sessions.session_date', [$s->toDateString(), $end->toDateString()])
            ->select(DB::raw('daily_sessions.session_date AS day'), DB::raw('SUM(expenses.amount) AS expenses'))
            ->groupBy('daily_sessions.session_date')->get()->keyBy('day');

        for ($i = 0; $i < $days; $i++) {
            $date     = $s->copy()->addDays($i);
            $key      = $date->format('Y-m-d');
            $r        = (float) ($revRows->get($key)?->revenue ?? 0);
            $p        = (float) ($profRows->get($key)?->profit ?? 0);
            $e        = (float) ($expRows->get($key)?->expenses ?? 0);
            $labels[] = $date->format('M j');
            $rev[]    = round($r);
            $profit[] = round($p);
            $net[]    = round($p - $e);
        }
    }

    private function loadByWeek(Carbon $start, Carbon $end, array &$labels, array &$rev, array &$profit, array &$net): void
    {
        $s = $end->copy()->subWeeks(11)->startOfWeek();
        if ($s->lt($start)) {
            $s = $start->copy()->startOfWeek();
        }

        $revRows = Sale::notVoided()
            ->whereBetween('sale_date', [$s, $end])
            ->select(DB::raw("DATE_TRUNC('week', sale_date)::date AS week"), DB::raw('SUM(total) AS revenue'))
            ->groupBy('week')->orderBy('week')->get()->keyBy('week');

        $profRows = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [$s, $end])
            ->select(
                DB::raw("DATE_TRUNC('week', sales.sale_date)::date AS week"),
                DB::raw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) AS profit')
            )
            ->groupBy('week')->orderBy('week')->get()->keyBy('week');

        $expRows = DB::table('expenses')
            ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
            ->whereNull('expenses.deleted_at')
            ->where('expenses.is_system_generated', false)
            ->whereBetween('daily_sessions.session_date', [$s->toDateString(), $end->toDateString()])
            ->select(
                DB::raw("DATE_TRUNC('week', daily_sessions.session_date)::date AS week"),
                DB::raw('SUM(expenses.amount) AS expenses')
            )
            ->groupBy('week')->get()->keyBy('week');

        $cur = $s->copy()->startOfWeek();
        while ($cur->lte($end)) {
            $key      = $cur->format('Y-m-d');
            $r        = (float) ($revRows->get($key)?->revenue ?? 0);
            $p        = (float) ($profRows->get($key)?->profit ?? 0);
            $e        = (float) ($expRows->get($key)?->expenses ?? 0);
            $labels[] = $cur->format('M j');
            $rev[]    = round($r);
            $profit[] = round($p);
            $net[]    = round($p - $e);
            $cur->addWeek();
        }
    }

    private function loadByMonth(Carbon $start, Carbon $end, array &$labels, array &$rev, array &$profit, array &$net): void
    {
        $s = $end->copy()->subMonths(11)->startOfMonth();
        if ($s->lt($start)) {
            $s = $start->copy()->startOfMonth();
        }

        $revRows = Sale::notVoided()
            ->whereBetween('sale_date', [$s, $end])
            ->select(DB::raw("TO_CHAR(sale_date, 'YYYY-MM') AS month"), DB::raw('SUM(total) AS revenue'))
            ->groupBy('month')->orderBy('month')->get()->keyBy('month');

        $profRows = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [$s, $end])
            ->select(
                DB::raw("TO_CHAR(sales.sale_date, 'YYYY-MM') AS month"),
                DB::raw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) AS profit')
            )
            ->groupBy('month')->orderBy('month')->get()->keyBy('month');

        $expRows = DB::table('expenses')
            ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
            ->whereNull('expenses.deleted_at')
            ->where('expenses.is_system_generated', false)
            ->whereBetween('daily_sessions.session_date', [$s->toDateString(), $end->toDateString()])
            ->select(
                DB::raw("TO_CHAR(daily_sessions.session_date, 'YYYY-MM') AS month"),
                DB::raw('SUM(expenses.amount) AS expenses')
            )
            ->groupBy('month')->get()->keyBy('month');

        $cur = $s->copy()->startOfMonth();
        while ($cur->lte($end)) {
            $key      = $cur->format('Y-m');
            $r        = (float) ($revRows->get($key)?->revenue ?? 0);
            $p        = (float) ($profRows->get($key)?->profit ?? 0);
            $e        = (float) ($expRows->get($key)?->expenses ?? 0);
            $labels[] = $cur->format('M Y');
            $rev[]    = round($r);
            $profit[] = round($p);
            $net[]    = round($p - $e);
            $cur->addMonth();
        }
    }

    public function render()
    {
        return view('livewire.dashboard.sales-performance');
    }
}
