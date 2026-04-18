<?php

namespace App\Livewire\Owner\Finance;

use App\Models\DailySession;
use App\Services\DayClose\DailySessionService;
use Illuminate\Support\Collection;
use Livewire\Component;

class DailyCloseReport extends Component
{
    public string     $reportDate       = '';
    public Collection $sessions;
    public ?int       $expandedSessionId = null;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isAdmin()) {
            abort(403);
        }

        $this->reportDate = today()->toDateString();
        $this->sessions   = new Collection();
        $this->loadSessions();
    }

    public function updatedReportDate(): void
    {
        $this->expandedSessionId = null;
        $this->loadSessions();
    }

    public function previousDay(): void
    {
        $this->reportDate        = \Carbon\Carbon::parse($this->reportDate)->subDay()->toDateString();
        $this->expandedSessionId = null;
        $this->loadSessions();
    }

    public function nextDay(): void
    {
        $next = \Carbon\Carbon::parse($this->reportDate)->addDay();
        if ($next->isFuture()) {
            return; // don't navigate into the future
        }
        $this->reportDate        = $next->toDateString();
        $this->expandedSessionId = null;
        $this->loadSessions();
    }

    public function goToToday(): void
    {
        $this->reportDate        = today()->toDateString();
        $this->expandedSessionId = null;
        $this->loadSessions();
    }

    public function loadSessions(): void
    {
        $this->sessions = DailySession::with([
            'shop',
            'openedBy',
            'closedBy',
            'lockedBy',
            'expenses.category',
            'ownerWithdrawals.recordedBy',
            'bankDeposits.depositedBy',
        ])
            ->forDate($this->reportDate)
            ->orderBy('shop_id')
            ->get();
    }

    public function lockSession(int $id): void
    {
        $session = DailySession::findOrFail($id);

        try {
            app(DailySessionService::class)->lockSession($session, auth()->user());
            session()->flash('success', 'Session locked successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->loadSessions();
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedSessionId = $this->expandedSessionId === $id ? null : $id;
    }

    public function render()
    {
        return view('livewire.owner.finance.daily-close-report');
    }
}
