<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Models\ExpenseCategory;
use App\Services\DayClose\DailySessionService;
use App\Services\DayClose\ExpenseService;
use Livewire\Attributes\On;
use Livewire\Component;

class AddExpense extends Component
{
    public int $dailySessionId = 0;
    public int $categoryId = 0;
    public string $amount = '';
    public string $description = '';
    public string $paymentMethod = 'cash';
    public string $receiptReference = '';
    public bool $inDrawer = false;

    // Category name last copied into the description, so a category change
    // can swap it without wiping what the user typed after it.
    public string $autoDescription = '';

    public function mount(int $dailySessionId): void
    {
        $user = auth()->user();
        $session = DailySession::findOrFail($dailySessionId);

        if (! $user->isOwner() && ($session->shop_id !== $user->location_id || ! $session->isEditable())) {
            abort(403);
        }
        if (! $session->isEditable()) {
            abort(403);
        }

        $this->dailySessionId = $dailySessionId;
    }

    public function updatedCategoryId(): void
    {
        $name = (string) ExpenseCategory::whereKey($this->categoryId)->value('name');
        $old  = $this->autoDescription;
        $desc = $this->description;

        if (trim($desc) === '' || $desc === $old) {
            $this->description = $name;
        } elseif ($old !== '' && str_starts_with($desc, $old)) {
            $this->description = $name . substr($desc, strlen($old));
        }

        $this->autoDescription = $name;
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

            $this->reset(['categoryId', 'amount', 'description', 'receiptReference', 'autoDescription']);
            $this->paymentMethod = 'cash';
            $this->dispatch('expense-added');
            $this->dispatch('notification', ['type' => 'success', 'message' => 'Expense recorded.']);
        } catch (\Exception $e) {
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    #[On('expense-added')]
    #[On('expense-voided')]
    #[On('expense-updated')]
    #[On('withdrawal-added')]
    #[On('withdrawal-voided')]
    #[On('withdrawal-updated')]
    #[On('deposit-added')]
    #[On('deposit-voided')]
    #[On('sale-completed')]
    public function refreshBalances(): void
    {
        // Available balances are recomputed in render()
    }

    public function render()
    {
        $categories = ExpenseCategory::userSelectable()->forLocation('shop')->get();

        $session = DailySession::findOrFail($this->dailySessionId);
        $summary = app(DailySessionService::class)->computeLiveSummary($session);

        return view('livewire.shop.day-close.add-expense', compact('categories', 'summary'));
    }
}
