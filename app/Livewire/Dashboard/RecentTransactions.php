<?php

namespace App\Livewire\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class RecentTransactions extends Component
{
    public string  $period       = 'today';
    public ?string $from         = null;
    public ?string $to           = null;
    public array   $transactions = [];

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

        $sales = DB::table('sales')
            ->join('shops', 'sales.shop_id', '=', 'shops.id')
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->select('sales.sale_number', 'shops.name as shop_name', 'sales.total', 'sales.sale_date as ts')
            ->orderByDesc('sales.sale_date')
            ->limit(8)
            ->get()
            ->map(fn($r) => [
                'label'  => 'Sale '.$r->sale_number.' · '.$r->shop_name,
                'ts'     => $r->ts,
                'amount' => (int) $r->total,
                'sign'   => '+',
                'color'  => '#0e9e86',
                'bg'     => 'rgba(14,158,134,.12)',
                'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            ]);

        $repayments = DB::table('credit_repayments')
            ->join('customers', 'credit_repayments.customer_id', '=', 'customers.id')
            ->whereBetween('credit_repayments.repayment_date', [$start->toDateString(), $end->toDateString()])
            ->select('customers.name', 'credit_repayments.amount', 'credit_repayments.repayment_date as ts')
            ->orderByDesc('credit_repayments.repayment_date')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'label'  => 'Payment Received · '.$r->name,
                'ts'     => $r->ts,
                'amount' => (int) $r->amount,
                'sign'   => '+',
                'color'  => '#3b6fd4',
                'bg'     => 'rgba(59,111,212,.12)',
                'icon'   => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
            ]);

        $expenses = DB::table('expenses')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->whereNull('expenses.deleted_at')
            ->where('expenses.is_system_generated', false)
            ->whereBetween('expenses.created_at', [$start, $end])
            ->select('expense_categories.name', 'expenses.amount', 'expenses.created_at as ts')
            ->orderByDesc('expenses.created_at')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'label'  => 'Expense · '.$r->name,
                'ts'     => $r->ts,
                'amount' => (int) $r->amount,
                'sign'   => '-',
                'color'  => '#e11d48',
                'bg'     => 'rgba(225,29,72,.12)',
                'icon'   => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            ]);

        $this->transactions = collect()
            ->merge($sales)
            ->merge($repayments)
            ->merge($expenses)
            ->sortByDesc('ts')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function periodRange(): array
    {
        return match ($this->period) {
            'today'   => [today()->startOfDay(),   now()->endOfDay()],
            'week'    => [now()->startOfWeek(),     now()->endOfDay()],
            'month'   => [now()->startOfMonth(),    now()->endOfDay()],
            'quarter' => [now()->startOfQuarter(),  now()->endOfDay()],
            'year'    => [now()->startOfYear(),     now()->endOfDay()],
            'custom'  => [
                Carbon::parse($this->from ?? today())->startOfDay(),
                Carbon::parse($this->to   ?? today())->endOfDay(),
            ],
            default   => [today()->startOfDay(), now()->endOfDay()],
        };
    }

    public function render() { return view('livewire.dashboard.recent-transactions'); }
}
