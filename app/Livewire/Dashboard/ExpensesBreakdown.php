<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\ResolvesBusinessPeriod;
use App\Services\Analytics\FinanceAnalyticsService;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class ExpensesBreakdown extends Component
{
    use ResolvesBusinessPeriod;

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
        $svc = app(FinanceAnalyticsService::class);
        $data = $svc->getExpenseSummary($this->localDate($start), $this->localDate($end), 'all');

        $this->total = (int) ($data['total_expenses'] ?? 0);

        $palette = ['#3b6fd4','#0e9e86','#8b5cf6','#d97706','#e11d48','#6b7494'];

        $cats = array_slice($data['by_category'] ?? [], 0, 6);
        $this->categories = [];
        foreach ($cats as $i => $cat) {
            $this->categories[] = [
                'name'  => $cat['category'] ?? $cat['name'] ?? '', // the service calls it 'category'
                'total' => (int) ($cat['total'] ?? 0),
                'pct'   => (float) ($cat['pct_of_total'] ?? 0),
                'color' => $palette[$i] ?? '#6b7494',
            ];
        }
    }

    private function periodRange(): array
    {
        return $this->businessPeriodRange();
    }

    public function render() { return view('livewire.dashboard.expenses-breakdown'); }
}
