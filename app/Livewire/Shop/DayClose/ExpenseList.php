<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\DayClose\ExpenseService;
use Livewire\Attributes\On;
use Livewire\Component;

class ExpenseList extends Component
{
    public int $dailySessionId = 0;

    // Edit state
    public ?int $editingId        = null;
    public int  $editCategoryId   = 0;
    public int  $editAmount       = 0;
    public string $editDescription  = '';
    public string $editPaymentMethod = 'cash';

    public function mount(int $dailySessionId): void
    {
        $this->dailySessionId = $dailySessionId;
    }

    #[On('expense-added')]
    #[On('expense-voided')]
    #[On('expense-updated')]
    public function refresh(): void
    {
        // Re-render loads fresh data from render()
    }

    public function editExpense(int $expenseId): void
    {
        $expense = Expense::where('id', $expenseId)
            ->where('daily_session_id', $this->dailySessionId)
            ->firstOrFail();

        $this->editingId          = $expenseId;
        $this->editCategoryId     = $expense->expense_category_id;
        $this->editAmount         = $expense->amount;
        $this->editDescription    = $expense->description;
        $this->editPaymentMethod  = $expense->payment_method;
        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function saveExpense(): void
    {
        $this->validate([
            'editCategoryId'    => 'required|integer|min:1',
            'editAmount'        => 'required|integer|min:1',
            'editDescription'   => 'required|string|max:500',
            'editPaymentMethod' => 'required|string',
        ]);

        $expense = Expense::where('id', $this->editingId)
            ->where('daily_session_id', $this->dailySessionId)
            ->firstOrFail();

        try {
            app(ExpenseService::class)->updateExpense($expense, [
                'expense_category_id' => $this->editCategoryId,
                'amount'              => $this->editAmount,
                'description'         => $this->editDescription,
                'payment_method'      => $this->editPaymentMethod,
            ], auth()->user());

            $this->editingId = null;
            $this->dispatch('expense-updated');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
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
        $session = DailySession::find($this->dailySessionId);

        $expenses = Expense::with('category')
            ->where('daily_session_id', $this->dailySessionId)
            ->whereNull('deleted_at')
            ->orderByDesc('recorded_at')
            ->get();

        $categories = ExpenseCategory::userSelectable()->get();

        return view('livewire.shop.day-close.expense-list', compact('expenses', 'session', 'categories'));
    }
}
