<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\BankDeposit;
use App\Models\DailySession;
use App\Services\DayClose\BankDepositService;
use Livewire\Attributes\On;
use Livewire\Component;

class DepositList extends Component
{
    public int $dailySessionId = 0;

    public function mount(int $dailySessionId): void
    {
        $this->dailySessionId = $dailySessionId;
    }

    #[On('deposit-added')]
    #[On('deposit-voided')]
    public function refresh(): void
    {
        // Re-render loads fresh data from render()
    }

    public function voidDeposit(int $depositId): void
    {
        $deposit = BankDeposit::where('id', $depositId)
            ->where('daily_session_id', $this->dailySessionId)
            ->firstOrFail();

        try {
            app(BankDepositService::class)->voidDeposit($deposit, auth()->user());
            $this->dispatch('deposit-voided');
            $this->dispatch('notification', ['type' => 'success', 'message' => 'Deposit voided.']);
        } catch (\Exception $e) {
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $session = DailySession::find($this->dailySessionId);

        $deposits = BankDeposit::where('daily_session_id', $this->dailySessionId)
            ->whereNull('deleted_at')
            ->with('depositedBy')
            ->orderByDesc('deposited_at')
            ->get();

        return view('livewire.shop.day-close.deposit-list', compact('deposits', 'session'));
    }
}
