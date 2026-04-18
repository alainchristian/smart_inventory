<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\OwnerWithdrawal;
use App\Services\DayClose\OwnerWithdrawalService;
use Livewire\Attributes\On;
use Livewire\Component;

class WithdrawalList extends Component
{
    public int $dailySessionId = 0;

    public function mount(int $dailySessionId): void
    {
        $this->dailySessionId = $dailySessionId;
    }

    #[On('withdrawal-added')]
    #[On('withdrawal-voided')]
    public function refresh(): void
    {
        // Re-render loads fresh data from render()
    }

    public function voidWithdrawal(int $withdrawalId): void
    {
        $withdrawal = OwnerWithdrawal::where('id', $withdrawalId)
            ->where('daily_session_id', $this->dailySessionId)
            ->firstOrFail();

        try {
            app(OwnerWithdrawalService::class)->voidWithdrawal($withdrawal, auth()->user());
            $this->dispatch('withdrawal-voided');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $withdrawals = OwnerWithdrawal::where('daily_session_id', $this->dailySessionId)
            ->whereNull('deleted_at')
            ->orderByDesc('recorded_at')
            ->get();

        return view('livewire.shop.day-close.withdrawal-list', compact('withdrawals'));
    }
}
