<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Models\ExpenseCategory;
use App\Services\DayClose\DailySessionService;
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

        // Balance check — cannot spend more than available in each channel
        $amount  = (int) $this->amount;
        $summary = app(DailySessionService::class)->computeLiveSummary($session);

        if ($this->paymentMethod === 'cash' && $amount > $summary['expected_cash']) {
            $this->addError('amount', 'Insufficient cash in drawer. Available: ' . number_format($summary['expected_cash']) . ' RWF.');
            return;
        }
        if ($this->paymentMethod === 'mobile_money' && $amount > $summary['momo_available']) {
            $this->addError('amount', 'Insufficient MoMo balance. Available: ' . number_format($summary['momo_available']) . ' RWF.');
            return;
        }
        if ($this->paymentMethod === 'bank_transfer' && $amount > $summary['bank_available']) {
            $this->addError('amount', 'Insufficient bank balance. Available: ' . number_format($summary['bank_available']) . ' RWF.');
            return;
        }

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
        $categories = ExpenseCategory::userSelectable()->forLocation('shop')->get();

        $session = DailySession::findOrFail($this->dailySessionId);
        $summary = app(DailySessionService::class)->computeLiveSummary($session);

        return view('livewire.shop.day-close.add-expense', compact('categories', 'summary'));
    }
}
