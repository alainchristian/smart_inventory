<?php

namespace App\Livewire\Concerns;

use App\Models\DailySession;

trait RequiresOpenSession
{
    public ?DailySession $activeSession      = null;
    public bool          $sessionBlocked     = false;
    public string        $sessionBlockReason = '';
    public ?string       $blockedSessionDate = null;
    public ?int          $blockedSessionId   = null;

    /**
     * Call this at the very top of mount() in every shop Livewire component.
     * Returns true  -> session is valid, component proceeds normally.
     * Returns false -> component renders the blocked state view.
     */
    public function checkSession(int $shopId): bool
    {
        $user = auth()->user();

        // Owners bypass the gate — read-only visibility across all shops
        if ($user->isOwner()) {
            return true;
        }

        // Only today's session matters for the gate
        $todaySession = DailySession::forShop($shopId)->forDate(today()->toDateString())->first();

        // Case 1: No session at all for today
        if (! $todaySession) {
            $this->sessionBlocked     = true;
            $this->sessionBlockReason = 'no_session';
            return false;
        }

        // Case 2: Today's session exists but is closed or locked
        if (! $todaySession->isOpen()) {
            $this->sessionBlocked     = true;
            $this->sessionBlockReason = 'session_closed';
            return false;
        }

        // All clear — session is open and belongs to today
        $this->activeSession = $todaySession;
        return true;
    }
}
