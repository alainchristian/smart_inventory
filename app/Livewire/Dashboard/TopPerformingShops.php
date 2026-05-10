<?php

namespace App\Livewire\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class TopPerformingShops extends Component
{
    public string  $period   = 'today';
    public ?string $from     = null;
    public ?string $to       = null;
    public array   $topShops = [];

    public function mount(): void { $this->loadData(); }

    #[On('time-filter-changed')]
    public function refresh(string $period, ?string $from = null, ?string $to = null): void
    {
        $this->period = $period;
        $this->from   = $from;
        $this->to     = $to;
        $this->loadData();
    }

    private function loadData(): void
    {
        [$start, $end] = $this->periodRange();
        $periodDays    = max($start->diffInDays($end), 1);
        $prevStart     = $start->copy()->subDays($periodDays);
        $prevEnd       = $end->copy()->subDays($periodDays);

        $topRaw = DB::table('sales')
            ->join('shops', 'sales.shop_id', '=', 'shops.id')
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->select('shops.id', 'shops.name', DB::raw('SUM(sales.total)::bigint as revenue'))
            ->groupBy('shops.id', 'shops.name')
            ->orderByDesc('revenue')
            ->limit(3)
            ->get();

        $prevRevs = DB::table('sales')
            ->whereNull('voided_at')
            ->whereNull('deleted_at')
            ->whereBetween('sale_date', [$prevStart, $prevEnd])
            ->whereIn('shop_id', $topRaw->pluck('id')->toArray())
            ->select('shop_id', DB::raw('SUM(total)::bigint as revenue'))
            ->groupBy('shop_id')
            ->pluck('revenue', 'shop_id');

        $this->topShops = $topRaw->values()->map(function ($shop, $i) use ($prevRevs) {
            $curr   = (int) $shop->revenue;
            $prev   = (int) ($prevRevs[$shop->id] ?? 0);
            $growth = $prev > 0 ? round(($curr - $prev) / $prev * 100, 1) : ($curr > 0 ? 100.0 : 0.0);
            return [
                'rank'    => $i + 1,
                'name'    => $shop->name,
                'revenue' => $curr,
                'growth'  => $growth,
            ];
        })->toArray();
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

    public function render()
    {
        return view('livewire.dashboard.top-performing-shops');
    }
}
