<?php

namespace App\Livewire\Dashboard;

use App\Models\Shop;
use App\Models\Box;
use Livewire\Component;
use Livewire\Attributes\On;

class TopShops extends Component
{
    public array  $shops      = [];
    public int    $maxRevenue = 1;
    public string $period     = 'today';

    public function mount(): void
    {
        $this->loadData();
    }

    #[On('time-filter-changed')]
    public function refresh(array $payload): void
    {
        $this->period = $payload['period'] ?? 'today';
        $this->loadData();
    }

    private function loadData(): void
    {
        [$start, $end] = $this->periodRange();

        $this->shops = Shop::withSum(
                ['sales as revenue' => fn($q) => $q->whereBetween('sale_date', [$start, $end])],
                'total'
            )
            ->orderByDesc('revenue')
            ->take(5)
            ->get()
            ->map(function ($shop, $idx) {
                $fill = Box::where('location_type', 'shop')
                    ->where('location_id', $shop->id)
                    ->selectRaw('SUM(items_remaining) as rem, SUM(items_total) as tot')
                    ->first();
                $fillPct = ($fill && $fill->tot > 0)
                    ? round(($fill->rem / $fill->tot) * 100) : 0;
                return [
                    'name'     => $shop->name,
                    'revenue'  => ($shop->revenue ?? 0) / 100,
                    'fill_pct' => $fillPct,
                    'rank_css' => ['r1','r2','r3'][$idx] ?? 'r3',
                    'rank'     => $idx + 1,
                ];
            })
            ->toArray();

        $this->maxRevenue = max(collect($this->shops)->max('revenue') ?? 0, 1);
    }

    private function periodRange(): array
    {
        return match($this->period) {
            'week'    => [now()->startOfWeek(),    now()->endOfDay()],
            'month'   => [now()->startOfMonth(),   now()->endOfDay()],
            'quarter' => [now()->startOfQuarter(), now()->endOfDay()],
            'year'    => [now()->startOfYear(),    now()->endOfDay()],
            default   => [today(),                 now()->endOfDay()],
        };
    }

    public function render()
    {
        return view('livewire.dashboard.top-shops');
    }
}
