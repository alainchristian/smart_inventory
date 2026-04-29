<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Services\DayClose\DailySessionService;
use Livewire\Component;

class OpenSession extends Component
{
    public int    $openingBalance     = 0;
    public string $openingBalanceHint = '';
    public string $errorMessage       = '';
    public ?int   $suggestedBalance   = null;

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
            $retained = (int) $lastClosed->cash_retained;
            $this->openingBalance     = $retained;
            $this->suggestedBalance   = $retained;
            $this->openingBalanceHint = 'Carried forward from ' . $lastClosed->session_date->format('d M Y') . ' — ' . number_format($retained) . ' RWF';
        }
    }

    public function openDay(): void
    {
        $this->validate([
            'openingBalance' => 'required|integer|min:0',
        ]);

        $user = auth()->user();
        $this->errorMessage = '';

        try {
            app(DailySessionService::class)->openSession(
                $user,
                $user->location_id,
                $this->openingBalance,
                today()->toDateString()
            );

            $this->dispatch('session-opened');
            $this->redirect(route('shop.day-close.index'), navigate: true);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        $user = auth()->user();
        $shopId = $user->location_id;

        $todaySession = DailySession::forShop($shopId)
            ->forDate(today()->toDateString())
            ->first();

        // Any open session from a previous date that is blocking new sessions
        $blockerSession = DailySession::forShop($shopId)
            ->open()
            ->where('session_date', '<', today()->toDateString())
            ->orderByDesc('session_date')
            ->first();

        return view('livewire.shop.day-close.open-session', compact('todaySession', 'blockerSession'));
    }
}
