<?php

namespace App\Livewire\Owner\Finance;

use App\Models\Shop;
use App\Services\Analytics\FinanceAnalyticsService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class IncomeStatement extends Component
{
    public string $period         = 'this_month';
    public string $dateFrom       = '';
    public string $dateTo         = '';
    public string $locationFilter = 'all';
    public array  $shops          = [];

    public function mount(): void
    {
        $this->shops = Shop::active()->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])
            ->toArray();

        $this->applyPreset();
    }

    // ── Period actions ────────────────────────────────────────────────────────

    public function setPreset(string $preset): void
    {
        $this->period = $preset;
        if ($preset !== 'custom') {
            $this->applyPreset();
        }
    }

    public function updatedDateFrom(): void
    {
        $this->period = 'custom';
    }

    public function updatedDateTo(): void
    {
        $this->period = 'custom';
    }

    public function updatedLocationFilter(): void
    {
        // statement is #[Computed] — re-renders automatically
    }

    private function applyPreset(): void
    {
        [$this->dateFrom, $this->dateTo] = match ($this->period) {
            'today'      => [today()->toDateString(), today()->toDateString()],
            'yesterday'  => [today()->subDay()->toDateString(), today()->subDay()->toDateString()],
            'this_week'  => [today()->startOfWeek()->toDateString(), today()->toDateString()],
            'this_month' => [today()->startOfMonth()->toDateString(), today()->toDateString()],
            'last_month' => [
                today()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                today()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ],
            'this_year'  => [today()->startOfYear()->toDateString(), today()->toDateString()],
            'last_year'  => [
                today()->subYear()->startOfYear()->toDateString(),
                today()->subYear()->endOfYear()->toDateString(),
            ],
            default      => [$this->dateFrom, $this->dateTo],
        };
    }

    // ── Computed — NOT stored in Livewire snapshot ────────────────────────────

    #[Computed]
    public function statement(): array
    {
        if (! $this->dateFrom || ! $this->dateTo) {
            return [];
        }

        return app(FinanceAnalyticsService::class)->getIncomeStatement(
            $this->dateFrom,
            $this->dateTo,
            $this->locationFilter
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function priorPeriodLabel(): string
    {
        if (! $this->dateFrom || ! $this->dateTo) {
            return 'prior period';
        }
        $from    = \Carbon\Carbon::parse($this->dateFrom);
        $to      = \Carbon\Carbon::parse($this->dateTo);
        $daySpan = $from->diffInDays($to) + 1;
        $prevFrom = $from->copy()->subDays($daySpan);
        $prevTo   = $from->copy()->subDay();

        if ($prevFrom->month === $prevTo->month && $prevFrom->year === $prevTo->year) {
            return $prevFrom->format('M Y');
        }
        return $prevFrom->format('d M') . ' – ' . $prevTo->format('d M Y');
    }

    public function periodLabel(): string
    {
        if (! $this->dateFrom || ! $this->dateTo) {
            return '—';
        }

        $from = Carbon::parse($this->dateFrom);
        $to   = Carbon::parse($this->dateTo);

        if ($from->equalTo($to))                                          return $from->format('d M Y');
        if ($from->month === $to->month && $from->year === $to->year)     return $from->format('M Y');
        if ($from->year === $to->year)                                    return $from->format('M') . ' – ' . $to->format('M Y');
        return $from->format('M Y') . ' – ' . $to->format('M Y');
    }

    public function render()
    {
        $shopName = $this->locationFilter === 'all'
            ? 'All Shops'
            : (collect($this->shops)->firstWhere('id', (int) explode(':', $this->locationFilter)[1])['name'] ?? '—');

        return view('livewire.owner.finance.income-statement', [
            'statement'        => $this->statement,
            'periodLabel'      => $this->periodLabel(),
            'priorPeriodLabel' => $this->priorPeriodLabel(),
            'shopName'         => $shopName,
        ]);
    }
}
