<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Models\User;
use App\Services\DayClose\DailySessionService;
use Livewire\Component;
use Livewire\WithPagination;

class SessionHistory extends Component
{
    use WithPagination;

    public ?int $expandedId = null;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isShopManager() && ! $user->isOwner()) {
            abort(403);
        }
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function lockSession(int $sessionId): void
    {
        $user    = auth()->user();
        $session = DailySession::findOrFail($sessionId);

        try {
            app(DailySessionService::class)->lockSession($session, $user);
            session()->flash('success', 'Session locked successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $user   = auth()->user();
        $shopId = $user->isOwner()
            ? (request()->query('shop_id') ? (int) request()->query('shop_id') : null)
            : $user->location_id;

        $query = DailySession::query();

        if ($shopId !== null) {
            $query->forShop($shopId);
        }

        $sessions = $query
            ->with(['openedBy', 'closedBy', 'lockedBy', 'expenses.category', 'ownerWithdrawals.recordedBy', 'shop'])
            ->orderByDesc('session_date')
            ->paginate(20);

        return view('livewire.shop.day-close.session-history', compact('sessions'));
    }
}
