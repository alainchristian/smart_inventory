<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Services\DayClose\DailySessionService;
use Livewire\Attributes\On;
use Livewire\Component;

class SessionSnapshot extends Component
{
    public int $sessionId;

    #[On('session-selected')]
    public function switchSession(int $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    #[On('expense-added')]
    #[On('expense-voided')]
    #[On('withdrawal-added')]
    #[On('withdrawal-voided')]
    #[On('deposit-added')]
    #[On('deposit-voided')]
    public function refresh(): void
    {
        // triggers re-render
    }

    public function render()
    {
        $session = DailySession::find($this->sessionId);

        $snap = $session
            ? app(DailySessionService::class)->computeLiveSummary($session)
            : [];

        return view('livewire.shop.day-close.session-snapshot', compact('snap', 'session'));
    }
}
