<?php

namespace App\Livewire\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class RevenueByCategory extends Component
{
    public string  $period     = 'today';
    public ?string $from       = null;
    public ?string $to         = null;
    public array   $categories = [];
    public int     $total      = 0;

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

        $rows = DB::select("
            SELECT
                COALESCE(c.name, 'Uncategorized') AS name,
                SUM(si.line_total)::bigint         AS revenue
            FROM sale_items si
            JOIN  products p ON p.id = si.product_id
            JOIN  sales    s ON s.id = si.sale_id
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE s.voided_at IS NULL AND s.deleted_at IS NULL
              AND s.sale_date BETWEEN ? AND ?
            GROUP BY c.id, c.name
            ORDER BY revenue DESC
            LIMIT 6
        ", [$start, $end]);

        $palette = ['#3b6fd4','#0e9e86','#8b5cf6','#d97706','#e11d48','#6b7494'];
        $total   = array_sum(array_column($rows, 'revenue'));
        $this->total = (int) $total;

        $this->categories = array_map(function ($row, $i) use ($total, $palette) {
            return [
                'name'    => $row->name,
                'revenue' => (int) $row->revenue,
                'pct'     => $total > 0 ? round($row->revenue / $total * 100, 1) : 0,
                'color'   => $palette[$i] ?? '#6b7494',
            ];
        }, $rows, array_keys($rows));
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

    public function render() { return view('livewire.dashboard.revenue-by-category'); }
}
