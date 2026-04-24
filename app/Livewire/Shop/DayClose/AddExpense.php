<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Models\ExpenseCategory;
use App\Services\DayClose\ExpenseService;
use Livewire\Component;

class AddExpense extends Component
{
    public int $dailySessionId = 0;
    public int $categoryId = 0;
    public string $amount = '';
    public string $description = '';
    public string $paymentMethod = 'cash';
    public string $receiptReference = '';

    public function mount(int $dailySessionId): void
    {
        $user = auth()->user();
        $session = DailySession::findOrFail($dailySessionId);

        if ($session->shop_id !== $user->location_id || ! $session->isEditable()) {
            abort(403);
        }

        $this->dailySessionId = $dailySessionId;
    }

    public function saveExpense(): void
    {
        $this->validate([
            'categoryId'    => 'required|integer|min:1',
            'amount'        => 'required|numeric|min:1',
            'description'   => 'required|string|max:500',
            'paymentMethod' => 'required|in:cash,mobile_money,bank_transfer,other',
        ]);

        $user    = auth()->user();
        $session = DailySession::findOrFail($this->dailySessionId);

        try {
            app(ExpenseService::class)->addExpense($session, [
                'expense_category_id' => $this->categoryId,
                'amount'              => (int) $this->amount,
                'description'         => $this->description,
                'payment_method'      => $this->paymentMethod,
                'receipt_reference'   => $this->receiptReference,
            ], $user);

            $this->reset(['categoryId', 'amount', 'description', 'receiptReference']);
            $this->paymentMethod = 'cash';
            $this->dispatch('expense-added');
            session()->flash('success', 'Expense recorded.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $categories = ExpenseCategory::userSelectable()
            ->forLocation('shop')
            ->get();

        return view('livewire.shop.day-close.add-expense', compact('categories'));
    }
}
