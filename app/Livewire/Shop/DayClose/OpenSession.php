<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Services\DayClose\DailySessionService;
use Livewire\Component;

class OpenSession extends Component
{
    public int    $openingBalance     = 0;
    public string $openingBalanceHint = '';

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isShopManager()) {
            abort(403);
        }

        $lastClosed = DailySession::forShop($user->location_id)
            ->whereIn('status', ['closed', 'locked'])
            ->orderByDesc('session_date')
            ->first();

        if ($lastClosed && $lastClosed->cash_retained !== null) {
            $this->openingBalance     = $lastClosed->cash_retained;
            $this->openingBalanceHint = 'Carried forward from ' . $lastClosed->session_date->format('d M Y');
        }
    }

    public function openDay(): void
    {
        $this->validate([
            'openingBalance' => 'required|integer|min:0',
        ]);

        $user = auth()->user();

        try {
            app(DailySessionService::class)->openSession(
                $user,
                $user->location_id,
                $this->openingBalance,
                today()->toDateString()
            );

            $this->dispatch('session-opened');
            session()->flash('success', 'Day opened successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();
        $todaySession = DailySession::forShop($user->location_id)
            ->forDate(today()->toDateString())
            ->first();

        return view('livewire.shop.day-close.open-session', compact('todaySession'));
    }
}
