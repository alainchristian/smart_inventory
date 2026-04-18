<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Services\DayClose\OwnerWithdrawalService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AddWithdrawal extends Component
{
    public int    $dailySessionId = 0;
    public int    $cashAmount     = 0;
    public int    $momoAmount     = 0;
    public string $momoReference  = '';
    public string $reason         = '';

    public function mount(int $dailySessionId): void
    {
        $user    = auth()->user();
        $session = DailySession::findOrFail($dailySessionId);

        if ($session->shop_id !== $user->location_id || ! $session->isEditable()) {
            abort(403);
        }

        $this->dailySessionId = $dailySessionId;
    }

    public function saveWithdrawal(): void
    {
        $this->validate([
            'cashAmount'    => 'integer|min:0',
            'momoAmount'    => 'integer|min:0',
            'momoReference' => 'nullable|string|max:100',
            'reason'        => 'required|string|max:500',
        ]);

        if ($this->cashAmount === 0 && $this->momoAmount === 0) {
            $this->addError('cashAmount', 'Enter at least one amount (cash or MoMo).');
            return;
        }

        $user    = auth()->user();
        $session = DailySession::findOrFail($this->dailySessionId);
        $svc     = app(OwnerWithdrawalService::class);

        try {
            DB::transaction(function () use ($session, $user, $svc) {
                if ($this->cashAmount > 0) {
                    $svc->recordWithdrawal($session, [
                        'amount' => $this->cashAmount,
                        'reason' => $this->reason,
                        'method' => 'cash',
                    ], $user);
                }

                if ($this->momoAmount > 0) {
                    $svc->recordWithdrawal($session, [
                        'amount'         => $this->momoAmount,
                        'reason'         => $this->reason,
                        'method'         => 'mobile_money',
                        'momo_reference' => $this->momoReference ?: null,
                    ], $user);
                }
            });

            $this->reset(['cashAmount', 'momoAmount', 'momoReference', 'reason']);
            $this->dispatch('withdrawal-added');
            session()->flash('success', 'Withdrawal recorded.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.shop.day-close.add-withdrawal');
    }
}
