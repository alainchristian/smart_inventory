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
    protected $queryString = ['reportDate' => ['except' => '', 'as' => 'date']];

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

        // If no date was bound from URL, pick the most useful default
        if ($this->reportDate === '') {
            $todayHasSessions = DailySession::forDate(today()->toDateString())->exists();
            if ($todayHasSessions) {
                $this->reportDate = today()->toDateString();
            } else {
                $latest = DailySession::orderByDesc('session_date')->value('session_date');
                $this->reportDate = $latest ? \Carbon\Carbon::parse($latest)->toDateString() : today()->toDateString();
            }
        }

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

    public function reopenSession(int $id): void
    {
        $session = DailySession::findOrFail($id);

        try {
            app(DailySessionService::class)->reopenSession($session, auth()->user());
            session()->flash('success', 'Session reopened — the shop manager can now edit it.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->expandedSessionId = null;
        $this->loadSessions();
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedSessionId = $this->expandedSessionId === $id ? null : $id;
    }

    public function render()
    {
        $computed = $this->computeSummary();

        return view('livewire.owner.finance.daily-close-report', $computed);
    }

    private function computeSummary(): array
    {
        if ($this->sessions->isEmpty()) {
            return [];
        }

        // Day-level aggregates
        $dayOpening     = (int) $this->sessions->sum('opening_balance');
        $dayRevenue     = (int) $this->sessions->sum('total_sales');
        $dayRepayments  = (int) $this->sessions->sum('total_repayments');
        $dayRefunds     = (int) $this->sessions->sum('total_refunds_cash');
        $dayExpenses    = (int) $this->sessions->sum('total_expenses');
        $dayWithdrawals = (int) $this->sessions->sum('total_withdrawals');
        $dayBanked      = (int) $this->sessions->sum('total_bank_deposits');
        $dayVariance    = (int) $this->sessions->sum('cash_variance');
        $totalSessions  = $this->sessions->count();
        $closedSessions = $this->sessions->whereIn('status', ['closed', 'locked'])->count();
        $allClosed      = $closedSessions === $totalSessions;
        $operatingProfit = $dayRevenue - $dayRefunds - $dayExpenses;
        $netOperating    = $operatingProfit - $dayWithdrawals;

        // Payment channel sales
        $pCash   = (int) $this->sessions->sum('total_sales_cash');
        $pMomo   = (int) $this->sessions->sum('total_sales_momo');
        $pCard   = (int) $this->sessions->sum('total_sales_card');
        $pBank   = (int) $this->sessions->sum('total_sales_bank_transfer');
        $pCredit = (int) $this->sessions->sum('total_sales_credit');

        // Visibility flags
        $showCard = $this->settingAllowCard || $pCard > 0;
        $showBank = $this->settingAllowBankTransfer || $pBank > 0;

        // Money position
        $cashRetained  = (int) $this->sessions->sum(fn ($s) => $s->cash_retained ?? $s->actual_cash_counted ?? $s->expected_cash ?? 0);
        $momoAvailable = (int) (
            $this->sessions->sum('total_sales_momo')
            + $this->sessions->sum('total_repayments_momo')
            - $this->sessions->sum('total_expenses_momo')
            - $this->sessions->sum('total_withdrawals_momo')
            - $this->sessions->sum('momo_deposits')
        );
        $creditOutstanding = (int) $this->overdueCustomers->sum('outstanding_balance');

        // Expenses by category
        $allExpenses   = $this->sessions->flatMap(fn ($s) => $s->expenses ?? collect());
        $expByCategory = $allExpenses
            ->groupBy(fn ($e) => $e->category->name ?? 'Other')
            ->map(fn ($g) => $g->sum('amount'))
            ->sortDesc();
        $maxExpCat = $expByCategory->max() ?: 1;

        // Sales analytics
        $saleCount    = $this->todaySales->count();
        $avgBasket    = $saleCount > 0 ? round($dayRevenue / $saleCount) : 0;
        $hourlyCounts = $this->todaySales->groupBy(fn ($s) => (int) $s->sale_date->format('G'))->map->count();
        $maxHourCount = $hourlyCounts->max() ?: 1;
        $peakHour     = $hourlyCounts->sortDesc()->keys()->first();
        $peakHourFmt  = $peakHour !== null ? str_pad($peakHour, 2, '0', STR_PAD_LEFT) . ':00' : '—';
        $pTotal       = $dayRevenue ?: 1;

        // Balance statement
        $totalIn  = $dayOpening + $pCash + $pMomo + $pCard + $pBank + $pCredit + $dayRepayments;
        $totalOut = $dayRefunds + $dayExpenses + $dayWithdrawals + $dayBanked
                  + $cashRetained + $momoAvailable + $pCredit;
        $balanceDiff = $totalIn - $totalOut;
        $isBalanced  = abs($balanceDiff) <= 1;

        $inRows = [
            ['Opening balance',      $dayOpening, 'var(--text-dim)', 'Cash float at start of day'],
            ['Sales — Cash',         $pCash,      'var(--accent)',   'Cash collected at point of sale'],
            ['Sales — Mobile Money', $pMomo,      'var(--accent)',   'MoMo payments received'],
        ];
        if ($showCard || $pCard > 0) {
            $inRows[] = ['Sales — Card',         $pCard, 'var(--accent)', 'Card payments received'];
        }
        if ($showBank || $pBank > 0) {
            $inRows[] = ['Sales — Bank Transfer', $pBank, 'var(--accent)', 'Bank transfer payments'];
        }
        if ($pCredit > 0) {
            $inRows[] = ['Sales — Credit (owed)', $pCredit, 'var(--amber)', 'Goods sold on credit today'];
        }
        if ($dayRepayments > 0) {
            $inRows[] = ['Credit repayments in',  $dayRepayments, 'var(--accent)', 'Debt collected from customers'];
        }

        $outRows = [
            ['Refunds paid out',   $dayRefunds,    'var(--amber)',  'Cash returned to customers'],
            ['Expenses paid',      $dayExpenses,   'var(--red)',    'Operational costs'],
            ['Owner withdrawals',  $dayWithdrawals,'var(--accent)', 'Cash + MoMo taken by owner'],
            ['Deposited to bank',  $dayBanked,     'var(--accent)', 'Sent to bank during the day'],
            ['Cash on hand',       $cashRetained,  'var(--accent)', 'Physical cash remaining in shop'],
            ['MoMo on hand',       $momoAvailable, 'var(--accent)', 'Mobile money wallet balance'],
            ['Credit outstanding', $pCredit,       'var(--amber)',  'Sold on credit — awaiting collection'],
        ];

        // Transaction feed
        $expCount  = $this->sessions->sum(fn ($s) => $s->expenses->count());
        $drawCount = $this->sessions->sum(fn ($s) => $s->ownerWithdrawals->count());
        $txFeed    = collect();
        foreach ($this->todaySales as $s) {
            $txFeed->push([
                'time'   => $s->sale_date,
                'desc'   => $s->customer_name ? 'Sale — ' . $s->customer_name : 'Sale',
                'method' => $s->is_split_payment ? 'Split' : ucfirst(str_replace('_', ' ', $s->payment_method?->value ?? 'cash')),
                'amount' => $s->total,
                'type'   => 'sale',
            ]);
        }
        foreach ($this->sessions as $sess) {
            foreach ($sess->expenses as $exp) {
                $txFeed->push([
                    'time'   => $exp->created_at,
                    'desc'   => ($exp->category->name ?? 'Expense') . ($exp->description ? ' — ' . $exp->description : ''),
                    'method' => ucfirst(str_replace('_', ' ', $exp->payment_method ?? 'cash')),
                    'amount' => $exp->amount,
                    'type'   => 'expense',
                ]);
            }
            foreach ($sess->ownerWithdrawals as $wd) {
                $txFeed->push([
                    'time'   => $wd->created_at,
                    'desc'   => 'Owner draw' . ($wd->reason ? ' — ' . $wd->reason : ''),
                    'method' => ucfirst($wd->method ?? 'Cash'),
                    'amount' => $wd->amount,
                    'type'   => 'withdrawal',
                ]);
            }
        }
        $txFeed = $txFeed->sortByDesc('time')->values()->take(30);

        return compact(
            'dayOpening', 'dayRevenue', 'dayRepayments', 'dayRefunds', 'dayExpenses',
            'dayWithdrawals', 'dayBanked', 'dayVariance', 'totalSessions', 'closedSessions',
            'allClosed', 'operatingProfit', 'netOperating',
            'pCash', 'pMomo', 'pCard', 'pBank', 'pCredit',
            'showCard', 'showBank',
            'cashRetained', 'momoAvailable', 'creditOutstanding',
            'expByCategory', 'maxExpCat',
            'saleCount', 'avgBasket', 'hourlyCounts', 'maxHourCount', 'peakHour', 'peakHourFmt', 'pTotal',
            'totalIn', 'totalOut', 'balanceDiff', 'isBalanced',
            'inRows', 'outRows',
            'expCount', 'drawCount', 'txFeed'
        );
    }
}
