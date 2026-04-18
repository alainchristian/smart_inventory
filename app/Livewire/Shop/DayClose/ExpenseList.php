<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\Expense;
use App\Services\DayClose\ExpenseService;
use Livewire\Attributes\On;
use Livewire\Component;

class ExpenseList extends Component
{
    public int $dailySessionId = 0;

    public function mount(int $dailySessionId): void
    {
        $this->dailySessionId = $dailySessionId;
    }

    #[On('expense-added')]
    #[On('expense-voided')]
    public function refresh(): void
    {
        // Re-render loads fresh data from render()
    }

    public function voidExpense(int $expenseId): void
    {
        $expense = Expense::where('id', $expenseId)
            ->where('daily_session_id', $this->dailySessionId)
            ->firstOrFail();

        try {
            app(ExpenseService::class)->voidExpense($expense, auth()->user());
            $this->dispatch('expense-voided');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $expenses = Expense::with('category')
            ->where('daily_session_id', $this->dailySessionId)
            ->whereNull('deleted_at')
            ->orderByDesc('recorded_at')
            ->get();

        return view('livewire.shop.day-close.expense-list', compact('expenses'));
    }
}
