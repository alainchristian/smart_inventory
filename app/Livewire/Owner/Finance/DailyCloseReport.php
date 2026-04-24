<?php

namespace App\Livewire\Owner\Finance;

use App\Models\Customer;
use App\Models\CreditRepayment;
use App\Models\DailySession;
use App\Models\Sale;
use App\Services\DayClose\DailySessionService;
use App\Services\SettingsService;
use Illuminate\Support\Collection;
use Livewire\Component;

class DailyCloseReport extends Component
{
    public string     $reportDate        = '';
    public Collection $sessions;
    public ?int       $expandedSessionId = null;
    public Collection $overdueCustomers;
    public Collection $todaySales;
    public int        $creditRepaidToday = 0;

    // Payment method settings — controls channel visibility everywhere
    public bool $settingAllowCard         = false;
    public bool $settingAllowBankTransfer = false;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isAdmin()) {
            abort(403);
        }

        $svc = app(SettingsService::class);
        $this->settingAllowCard         = $svc->allowCardPayment();
        $this->settingAllowBankTransfer = $svc->allowBankTransferPayment();

        $this->reportDate       = today()->toDateString();
        $this->sessions         = new Collection();
        $this->overdueCustomers = new Collection();
        $this->todaySales       = new Collection();
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
            return;
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
        $sessionService = app(DailySessionService::class);

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
            ->get()
            ->map(function ($session) use ($sessionService) {
                if ($session->isOpen()) {
                    foreach ($sessionService->computeLiveSummary($session) as $key => $value) {
                        $session->$key = $value;
                    }
                }
                return $session;
            });

        $shopIds = $this->sessions->pluck('shop_id')->filter()->unique();

        $this->overdueCustomers = Customer::where('outstanding_balance', '>', 0)
            ->orderByDesc('outstanding_balance')
            ->limit(15)
            ->get();

        if ($shopIds->isNotEmpty()) {
            $this->todaySales = Sale::whereIn('shop_id', $shopIds)
                ->whereDate('sale_date', $this->reportDate)
                ->whereNull('voided_at')
                ->orderByDesc('sale_date')
                ->limit(50)
                ->get();
        } else {
            $this->todaySales = new Collection();
        }

        $this->creditRepaidToday = (int) CreditRepayment::when($shopIds->isNotEmpty(), fn ($q) => $q->whereIn('shop_id', $shopIds))
            ->whereDate('created_at', $this->reportDate)
            ->sum('amount');
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
