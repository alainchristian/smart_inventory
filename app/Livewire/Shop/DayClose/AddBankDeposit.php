<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\BankDeposit;
use App\Models\DailySession;
use App\Services\DayClose\BankDepositService;
use App\Services\DayClose\DailySessionService;
use Livewire\Attributes\On;
use Livewire\Component;

class AddBankDeposit extends Component
{
    public int    $dailySessionId = 0;
    public string $amount         = '';
    public string $source         = 'cash';
    public string $bankReference  = '';
    public string $notes          = '';

    public function mount(int $dailySessionId): void
    {
        $user    = auth()->user();
        $session = DailySession::findOrFail($dailySessionId);

        if ($session->shop_id !== $user->location_id || ! $session->isEditable()) {
            abort(403);
        }

        $this->dailySessionId = $dailySessionId;
    }

    public function saveDeposit(): void
    {
        $this->validate([
            'amount'        => 'required|numeric|min:1',
            'source'        => 'required|in:cash,mobile_money',
            'bankReference' => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:500',
        ]);

        $user    = auth()->user();
        $session = DailySession::findOrFail($this->dailySessionId);

        // Balance check — can only deposit from a channel that has funds
        $summary = app(DailySessionService::class)->computeLiveSummary($session);
        $amount  = (int) $this->amount;
        if ($this->source === 'cash' && $amount > $summary['expected_cash']) {
            $this->addError('amount', 'Exceeds cash in drawer (' . number_format($summary['expected_cash']) . ' RWF).');
            return;
        }
        if ($this->source === 'mobile_money' && $amount > $summary['momo_available']) {
            $this->addError('amount', 'Exceeds MoMo balance (' . number_format($summary['momo_available']) . ' RWF).');
            return;
        }

        try {
            app(BankDepositService::class)->recordDeposit($session, [
                'amount'         => (int) $this->amount,
                'source'         => $this->source,
                'bank_reference' => $this->bankReference ?: null,
                'notes'          => $this->notes ?: null,
            ], $user);

            $this->reset(['amount', 'bankReference', 'notes']);
            $this->dispatch('deposit-added');
            session()->flash('success', 'Bank deposit recorded.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    #[On('expense-added')]
    #[On('expense-voided')]
    #[On('withdrawal-added')]
    #[On('withdrawal-voided')]
    #[On('deposit-added')]
    #[On('deposit-voided')]
    #[On('sale-completed')]
    public function refreshBalances(): void
    {
        // Summary is recomputed fresh in render() — this just triggers a re-render.
    }

    public function voidDeposit(int $depositId): void
    {
        $deposit = BankDeposit::findOrFail($depositId);
        $user    = auth()->user();

        try {
            app(BankDepositService::class)->voidDeposit($deposit, $user);
            $this->dispatch('deposit-voided');
            session()->flash('success', 'Deposit voided.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $session  = DailySession::findOrFail($this->dailySessionId);
        $summary  = app(DailySessionService::class)->computeLiveSummary($session);

        $deposits = BankDeposit::where('daily_session_id', $this->dailySessionId)
            ->whereNull('deleted_at')
            ->with('depositedBy')
            ->orderByDesc('deposited_at')
            ->get();

        return view('livewire.shop.day-close.add-bank-deposit', compact('deposits', 'summary'));
    }
}
