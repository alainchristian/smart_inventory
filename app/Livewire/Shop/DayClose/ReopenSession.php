<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Services\DayClose\DailySessionService;
use Livewire\Component;

/**
 * "Re-open" button + reason modal for a shop manager's own closed day.
 * Rules live in DailySessionService::reopenSession(); this only decides
 * whether to show the button and collects the reason.
 */
class ReopenSession extends Component
{
    public int $sessionId;
    public string $reason = '';
    public bool $showModal = false;

    public function mount(int $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function reopen(): void
    {
        $this->validate(['reason' => 'required|string|min:5|max:500'], [
            'reason.required' => 'Say why the day needs reopening.',
            'reason.min'      => 'Say why the day needs reopening (at least 5 characters).',
        ]);

        $session = DailySession::findOrFail($this->sessionId);

        try {
            app(DailySessionService::class)->reopenSession($session, auth()->user(), $this->reason);
        } catch (\Exception $e) {
            $this->addError('reason', $e->getMessage());
            return;
        }

        session()->flash('success', 'Register reopened — make your corrections, then close it again.');

        $this->redirect($session->session_date->isSameDay(business_today())
            ? route('shop.day-close.index')
            : route('shop.session.close', ['session' => $session->id]));
    }

    public function render()
    {
        $user    = auth()->user();
        $session = DailySession::find($this->sessionId);

        $canReopen = $session
            && $user->isShopManager()
            && $user->location_id === $session->shop_id
            && $session->status === 'closed'
            && ! DailySession::forShop($session->shop_id)
                ->where('session_date', '>', $session->session_date->toDateString())->exists();

        return view('livewire.shop.day-close.reopen-session', [
            'canReopen' => $canReopen,
            'session'   => $session,
        ]);
    }
}
