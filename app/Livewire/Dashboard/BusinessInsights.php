<?php

namespace App\Livewire\Dashboard;

use App\Models\Box;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class BusinessInsights extends Component
{
    public string  $period   = 'today';
    public ?string $from     = null;
    public ?string $to       = null;
    public array   $insights = [];

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
        [$start, $end]         = $this->periodRange();
        [$prevStart, $prevEnd] = $this->previousRange($start, $end);

        $currentRev = (int) Sale::notVoided()->whereBetween('sale_date', [$start, $end])->sum('total');
        $prevRev    = (int) Sale::notVoided()->whereBetween('sale_date', [$prevStart, $prevEnd])->sum('total');
        $revGrowth  = $prevRev > 0 ? round(($currentRev - $prevRev) / $prevRev * 100, 1) : 0.0;

        $margin = (float) (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as m')
            ->value('m') ?? 0);
        $marginPct = $currentRev > 0 ? round($margin / $currentRev * 100, 1) : 0.0;

        $prevMargin = (float) (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [$prevStart, $prevEnd])
            ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as m')
            ->value('m') ?? 0);
        $prevMarginPct  = $prevRev > 0 ? round($prevMargin / $prevRev * 100, 1) : 0.0;
        $marginChange   = round($marginPct - $prevMarginPct, 1);

        $outstanding  = (int) Customer::sum('outstanding_balance');
        $creditCount  = (int) Customer::where('outstanding_balance', '>', 0)->count();

        $invValue = (int) (Box::available()
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('SUM(boxes.items_remaining * products.selling_price) as v')
            ->value('v') ?? 0);

        $this->insights = [
            [
                'color'    => 'var(--accent)',
                'bg'       => 'var(--accent-dim)',
                'icon'     => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                'headline' => ($revGrowth >= 0 ? 'Revenue increased by ' : 'Revenue decreased by ').abs($revGrowth).'% vs last period.',
                'detail'   => $revGrowth >= 0 ? 'Great job! Keep it up 🚀' : 'Review your sales strategy.',
            ],
            [
                'color'    => 'var(--green)',
                'bg'       => 'var(--green-dim)',
                'icon'     => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'headline' => 'Net profit margin is '.$marginPct.'% this period.',
                'detail'   => ($marginChange >= 0 ? '↑ ' : '↓ ').abs($marginChange).'% compared to previous period.',
            ],
            [
                'color'    => 'var(--amber)',
                'bg'       => 'var(--amber-dim)',
                'icon'     => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'headline' => $outstanding > 0
                    ? 'Receivables are High. Follow up with '.$creditCount.' '.($creditCount === 1 ? 'customer' : 'customers').'.'
                    : 'No outstanding receivables. All clear! ✓',
                'detail'   => $outstanding > 0
                    ? 'Total pending: '.number_format($outstanding).' RWF'
                    : 'Excellent credit management.',
            ],
            [
                'color'    => 'var(--violet)',
                'bg'       => 'var(--violet-dim)',
                'icon'     => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                'headline' => 'Inventory value: '.number_format($invValue).' RWF.',
                'detail'   => 'Total stock value at current selling prices.',
            ],
        ];
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

    private function previousRange(Carbon $start, Carbon $end): array
    {
        $diff = max($start->diffInDays($end), 1);
        return [$start->copy()->subDays($diff), $end->copy()->subDays($diff)];
    }

    public function render() { return view('livewire.dashboard.business-insights'); }
}
