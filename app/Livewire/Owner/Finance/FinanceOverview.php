<?php

namespace App\Livewire\Owner\Finance;

use App\Models\DailySession;
use App\Models\Shop;
use App\Services\Analytics\FinanceAnalyticsService;
use App\Services\DayClose\DailySessionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FinanceOverview extends Component
{
    public string $dateFrom   = '';
    public string $dateTo     = '';
    public string $shopFilter = 'all';
    public string $preset     = 'last_30';

    public array      $rows             = [];
    public array      $chartData        = [];
    public Collection $shops;

    public ?string    $expandedKey      = null;
    public Collection $expandedSessions;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isAdmin()) {
            abort(403);
        }

        $this->shops            = Shop::active()->orderBy('name')->get();
        $this->expandedSessions = new Collection();
        $this->applyPreset();
    }

    public function setPreset(string $preset): void
    {
        $this->preset = $preset;
        $this->applyPreset();
    }

    private function applyPreset(): void
    {
        match ($this->preset) {
            'today'      => [$this->dateFrom, $this->dateTo] = [today()->toDateString(), today()->toDateString()],
            'yesterday'  => [$this->dateFrom, $this->dateTo] = [today()->subDay()->toDateString(), today()->subDay()->toDateString()],
            'this_week'  => [$this->dateFrom, $this->dateTo] = [today()->startOfWeek()->toDateString(), today()->toDateString()],
            'this_month' => [$this->dateFrom, $this->dateTo] = [today()->startOfMonth()->toDateString(), today()->toDateString()],
            'last_month' => [$this->dateFrom, $this->dateTo] = [
                today()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                today()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ],
            default      => [$this->dateFrom, $this->dateTo] = [now()->subDays(29)->toDateString(), today()->toDateString()],
        };

        $this->loadData();
    }

    public function updatedDateFrom(): void
    {
        $this->preset = 'custom';
        $this->loadData();
    }

    public function updatedDateTo(): void
    {
        $this->preset = 'custom';
        $this->loadData();
    }

    public function updatedShopFilter(): void
    {
        $this->loadData();
    }

    public function toggleRow(string $date, int $shopId): void
    {
        $key = $date . ':' . $shopId;

        if ($this->expandedKey === $key) {
            $this->expandedKey      = null;
            $this->expandedSessions = new Collection();
            return;
        }

        $this->expandedKey = $key;
        $this->expandedSessions = DailySession::with([
            'openedBy', 'closedBy', 'lockedBy', 'shop',
            'expenses.category', 'ownerWithdrawals.recordedBy', 'bankDeposits',
        ])
            ->where('shop_id', $shopId)
            ->forDate($date)
            ->orderBy('opened_at')
            ->get();
    }

    public function closeExpanded(): void
    {
        $this->expandedKey      = null;
        $this->expandedSessions = new Collection();
    }

    /**
     * Refresh all open sessions in the date range with live computed totals
     * so FinanceOverview always shows accurate data even before sessions close.
     */
    private function refreshOpenSessions(): void
    {
        $openSessions = DailySession::open()
            ->whereBetween('session_date', [$this->dateFrom, $this->dateTo])
            ->get();

        if ($openSessions->isEmpty()) return;

        $svc = app(DailySessionService::class);
        foreach ($openSessions as $session) {
            $s = $svc->computeLiveSummary($session);
            $session->update([
                'total_sales_cash'          => $s['total_sales_cash'],
                'total_sales_momo'          => $s['total_sales_momo'],
                'total_sales_card'          => $s['total_sales_card'],
                'total_sales_bank_transfer' => $s['total_sales_bank_transfer'],
                'total_sales_credit'        => $s['total_sales_credit'],
                'total_sales_other'         => $s['total_sales_other'],
                'total_sales'               => $s['total_sales'],
                'transaction_count'         => $s['transaction_count'],
                'total_refunds_cash'        => $s['total_refunds_cash'],
                'total_expenses'            => $s['total_expenses'],
                'total_expenses_cash'       => $s['total_expenses_cash'],
                'total_expenses_momo'       => $s['total_expenses_momo'],
                'total_withdrawals'         => $s['total_withdrawals'],
                'total_withdrawals_cash'    => $s['total_withdrawals_cash'],
                'total_withdrawals_momo'    => $s['total_withdrawals_momo'],
                'total_bank_deposits'       => $s['total_bank_deposits'],
                'cash_deposits'             => $s['cash_deposits'],
                'momo_deposits'             => $s['momo_deposits'],
                'total_repayments'          => $s['total_repayments'],
                'total_repayments_cash'     => $s['total_repayments_cash'],
                'total_repayments_momo'     => $s['total_repayments_momo'],
                'expected_cash'             => $s['expected_cash'],
            ]);
        }
    }

    public function loadData(): void
    {
        // Populate open session columns with live data before querying
        $this->refreshOpenSessions();

        // Reset drill-down when data reloads
        $this->expandedKey      = null;
        $this->expandedSessions = new Collection();

        $query = DB::table('daily_sessions')
            ->join('shops', 'daily_sessions.shop_id', '=', 'shops.id')
            ->whereBetween('session_date', [$this->dateFrom, $this->dateTo])
            ->select(
                'daily_sessions.session_date',
                'shops.name as shop_name',
                'shops.id as shop_id',
                DB::raw('SUM(COALESCE(daily_sessions.opening_balance, 0))        as opening_balance'),
                DB::raw('SUM(COALESCE(daily_sessions.total_sales, 0))            as revenue'),
                DB::raw('SUM(COALESCE(daily_sessions.total_repayments, 0))       as repayments'),
                DB::raw('SUM(COALESCE(daily_sessions.total_refunds_cash, 0))     as refunds'),
                DB::raw('SUM(COALESCE(daily_sessions.total_expenses, 0))         as expenses'),
                DB::raw('SUM(COALESCE(daily_sessions.total_withdrawals, 0))      as withdrawals'),
                DB::raw('SUM(COALESCE(daily_sessions.total_bank_deposits, 0))    as cash_banked'),
                DB::raw('SUM(COALESCE(daily_sessions.cash_variance, 0))          as total_variance'),
                DB::raw('SUM(COALESCE(daily_sessions.total_sales_cash, 0))       as sales_cash'),
                DB::raw('SUM(COALESCE(daily_sessions.total_sales_momo, 0))       as sales_momo'),
                DB::raw('SUM(COALESCE(daily_sessions.total_sales_card, 0))       as sales_card'),
                DB::raw('SUM(COALESCE(daily_sessions.total_sales_credit, 0))     as sales_credit'),
                DB::raw('COUNT(*) as session_count'),
                DB::raw("SUM(CASE WHEN daily_sessions.status IN ('closed','locked') THEN 1 ELSE 0 END) as closed_count"),
                DB::raw("(SELECT COALESCE(SUM(p.purchase_price * si.quantity_sold), 0)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    JOIN products p ON si.product_id = p.id
                    WHERE s.shop_id = shops.id
                      AND s.sale_date::date = daily_sessions.session_date
                      AND s.voided_at IS NULL AND s.deleted_at IS NULL
                ) as total_cogs"),
            )
            ->groupBy('daily_sessions.session_date', 'shops.id', 'shops.name')
            ->orderByDesc('daily_sessions.session_date')
            ->orderBy('shops.name');

        if ($this->shopFilter !== 'all') {
            $query->where('daily_sessions.shop_id', $this->shopFilter);
        }

        $results = $query->get();

        $this->rows = $results->map(fn ($r) => (array) $r)->toArray();

        // Chart — aggregate by date across all shops
        $byDate     = $results->groupBy('session_date');
        $labels     = [];
        $revenue    = [];
        $repayments = [];
        $refunds    = [];
        $expenses   = [];
        $net        = [];

        foreach ($byDate->sortKeys() as $date => $rows) {
            $labels[]     = \Carbon\Carbon::parse($date)->format('d M');
            $rev          = (int) $rows->sum('revenue');
            $rep          = (int) $rows->sum('repayments');
            $ref          = (int) $rows->sum('refunds');
            $exp          = (int) $rows->sum('expenses');
            $wdl          = (int) $rows->sum('withdrawals');
            $revenue[]    = $rev;
            $repayments[] = $rep;
            $refunds[]    = $ref;
            $expenses[]   = $exp;
            $net[]        = $rev - $ref - $exp - $wdl;
        }

        $this->chartData = compact('labels', 'revenue', 'repayments', 'refunds', 'expenses', 'net');
    }

    private function locationFilter(): string
    {
        return $this->shopFilter === 'all' ? 'all' : 'shop:' . $this->shopFilter;
    }

    public function getExpenseSummaryProperty(): array
    {
        return app(FinanceAnalyticsService::class)
            ->getExpenseSummary($this->dateFrom, $this->dateTo, $this->locationFilter());
    }

    public function getWithdrawalSummaryProperty(): array
    {
        return app(FinanceAnalyticsService::class)
            ->getWithdrawalSummary($this->dateFrom, $this->dateTo, $this->locationFilter());
    }

    public function getCashVarianceProperty(): array
    {
        return app(FinanceAnalyticsService::class)
            ->getCashVarianceSummary($this->dateFrom, $this->dateTo, $this->locationFilter());
    }

    public function getNetResultProperty(): array
    {
        return app(FinanceAnalyticsService::class)
            ->getNetOperatingResult($this->dateFrom, $this->dateTo, $this->locationFilter());
    }

    public function render()
    {
        return view('livewire.owner.finance.finance-overview', [
            'expenseSummary'    => $this->expenseSummary,
            'withdrawalSummary' => $this->withdrawalSummary,
            'cashVariance'      => $this->cashVariance,
            'netResult'         => $this->netResult,
        ]);
    }
}
