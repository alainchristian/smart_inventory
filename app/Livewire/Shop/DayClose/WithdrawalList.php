<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Models\OwnerWithdrawal;
use App\Services\DayClose\OwnerWithdrawalService;
use Livewire\Attributes\On;
use Livewire\Component;

class WithdrawalList extends Component
{
    public int $dailySessionId = 0;

    // Edit state
    public ?int   $editingId     = null;
    public int    $editAmount    = 0;
    public string $editReason    = '';
    public string $editMethod    = 'cash';
    public string $editMomoRef   = '';

    public function mount(int $dailySessionId): void
    {
        $this->dailySessionId = $dailySessionId;
    }

    #[On('withdrawal-added')]
    #[On('withdrawal-voided')]
    #[On('withdrawal-updated')]
    public function refresh(): void
    {
        // Re-render loads fresh data from render()
    }

    public function editWithdrawal(int $withdrawalId): void
    {
        $withdrawal = OwnerWithdrawal::where('id', $withdrawalId)
            ->where('daily_session_id', $this->dailySessionId)
            ->firstOrFail();

        $this->editingId   = $withdrawalId;
        $this->editAmount  = $withdrawal->amount;
        $this->editReason  = $withdrawal->reason;
        $this->editMethod  = $withdrawal->method;
        $this->editMomoRef = $withdrawal->momo_reference ?? '';
        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function saveWithdrawal(): void
    {
        $this->validate([
            'editAmount' => 'required|integer|min:1',
            'editReason' => 'required|string|max:500',
            'editMethod' => 'required|in:cash,mobile_money',
        ]);

        $withdrawal = OwnerWithdrawal::where('id', $this->editingId)
            ->where('daily_session_id', $this->dailySessionId)
            ->firstOrFail();

        try {
            app(OwnerWithdrawalService::class)->updateWithdrawal($withdrawal, [
                'amount'         => $this->editAmount,
                'reason'         => $this->editReason,
                'method'         => $this->editMethod,
                'momo_reference' => $this->editMomoRef ?: null,
            ], auth()->user());

            $this->editingId = null;
            $this->dispatch('withdrawal-updated');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
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
        $session = DailySession::find($this->dailySessionId);

        $withdrawals = OwnerWithdrawal::where('daily_session_id', $this->dailySessionId)
            ->whereNull('deleted_at')
            ->orderByDesc('recorded_at')
            ->get();

        return view('livewire.shop.day-close.withdrawal-list', compact('withdrawals', 'session'));
    }
}
