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

    public bool   $showTxModal    = false;
    public string $txFilter       = 'summary';
    public array  $transactions   = [];
    public array  $txSummary      = [];


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

    public function openTxModal(): void
    {
        $this->txFilter    = 'summary';
        $this->loadSummary();
        $this->showTxModal = true;
    }

    public function closeTxModal(): void
    {
        $this->showTxModal = false;
    }

    public function setTxFilter(string $filter): void
    {
        $this->txFilter = $filter;
        if ($filter === 'summary') {
            $this->loadSummary();
        } else {
            $this->loadTransactions();
        }
    }

    private function loadSummary(): void
    {
        $from   = $this->dateFrom;
        $to     = $this->dateTo;
        $shopId = $this->shopFilter !== 'all' ? (int) $this->shopFilter : null;

        // Aggregate session totals
        $sessQ = DB::table('daily_sessions')
            ->whereBetween('session_date', [$from, $to])
            ->select(
                DB::raw('SUM(COALESCE(opening_balance, 0))           as opening_balance'),
                DB::raw('SUM(COALESCE(total_sales, 0))               as total_sales'),
                DB::raw('SUM(COALESCE(total_sales_cash, 0))          as total_sales_cash'),
                DB::raw('SUM(COALESCE(total_sales_momo, 0))          as total_sales_momo'),
                DB::raw('SUM(COALESCE(total_sales_card, 0))          as total_sales_card'),
                DB::raw('SUM(COALESCE(total_sales_credit, 0))        as total_sales_credit'),
                DB::raw('SUM(COALESCE(total_sales_bank_transfer, 0)) as total_sales_bank_transfer'),
                DB::raw('SUM(COALESCE(total_repayments, 0))          as total_repayments'),
                DB::raw('SUM(COALESCE(total_repayments_cash, 0))     as total_repayments_cash'),
                DB::raw('SUM(COALESCE(total_repayments_momo, 0))     as total_repayments_momo'),
                DB::raw('SUM(COALESCE(total_refunds_cash, 0))        as total_refunds_cash'),
                DB::raw('SUM(COALESCE(total_expenses, 0))            as total_expenses'),
                DB::raw('SUM(COALESCE(total_expenses_cash, 0))       as total_expenses_cash'),
                DB::raw('SUM(COALESCE(total_withdrawals, 0))         as total_withdrawals'),
                DB::raw('SUM(COALESCE(total_withdrawals_cash, 0))    as total_withdrawals_cash'),
                DB::raw('SUM(COALESCE(total_bank_deposits, 0))       as total_bank_deposits'),
                DB::raw('SUM(COALESCE(cash_deposits, 0))             as cash_deposits'),
                DB::raw('SUM(COALESCE(expected_cash, 0))             as expected_cash'),
                DB::raw('SUM(COALESCE(actual_cash_counted, 0))       as actual_cash_counted'),
                DB::raw('SUM(COALESCE(cash_variance, 0))             as cash_variance'),
                DB::raw('SUM(COALESCE(cash_retained, 0))             as cash_retained'),
                DB::raw('COUNT(*) as session_count'),
                DB::raw("SUM(CASE WHEN status::text IN ('closed','locked') THEN 1 ELSE 0 END) as closed_count"),
                DB::raw("SUM(CASE WHEN status::text = 'open' THEN 1 ELSE 0 END) as open_count"),
                // Variance breakdown — only closed/locked sessions have meaningful variance
                DB::raw("SUM(CASE WHEN status::text != 'open' AND COALESCE(cash_variance,0) < 0 THEN ABS(COALESCE(cash_variance,0)) ELSE 0 END) as total_shortage"),
                DB::raw("SUM(CASE WHEN status::text != 'open' AND COALESCE(cash_variance,0) > 0 THEN COALESCE(cash_variance,0) ELSE 0 END) as total_surplus"),
                DB::raw("SUM(CASE WHEN status::text != 'open' AND COALESCE(cash_variance,0) != 0 THEN 1 ELSE 0 END) as sessions_with_variance"),
            );
        if ($shopId) {
            $sessQ->where('shop_id', $shopId);
        }
        $sessData = (array) $sessQ->first();

        // Expenses grouped by category
        $expQ = DB::table('expenses')
            ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
            ->leftJoin('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->whereNull('expenses.deleted_at')
            ->whereBetween('daily_sessions.session_date', [$from, $to])
            ->select(
                DB::raw("COALESCE(expense_categories.name, 'Uncategorized') as category_name"),
                DB::raw("expenses.payment_method::text as payment_method"),
                DB::raw('SUM(COALESCE(expenses.amount, 0)) as total_amount'),
            )
            ->groupBy('expense_categories.name', DB::raw('expenses.payment_method::text'))
            ->orderByDesc('total_amount');
        if ($shopId) {
            $expQ->where('daily_sessions.shop_id', $shopId);
        }
        $expensesByCategory = $expQ->get()->map(fn ($r) => (array) $r)->toArray();

        // Owner withdrawals
        $wdQ = DB::table('owner_withdrawals')
            ->join('daily_sessions', 'owner_withdrawals.daily_session_id', '=', 'daily_sessions.id')
            ->whereNull('owner_withdrawals.deleted_at')
            ->whereBetween('daily_sessions.session_date', [$from, $to])
            ->select(
                DB::raw("COALESCE(owner_withdrawals.reason, 'Owner withdrawal') as reason"),
                DB::raw("owner_withdrawals.method::text as method"),
                DB::raw('owner_withdrawals.amount'),
                DB::raw('owner_withdrawals.recorded_at'),
            )
            ->orderByDesc('owner_withdrawals.recorded_at');
        if ($shopId) {
            $wdQ->where('owner_withdrawals.shop_id', $shopId);
        }
        $withdrawals = $wdQ->get()->map(fn ($r) => (array) $r)->toArray();

        // Bank deposits
        $depQ = DB::table('bank_deposits')
            ->join('daily_sessions', 'bank_deposits.daily_session_id', '=', 'daily_sessions.id')
            ->whereBetween('daily_sessions.session_date', [$from, $to])
            ->select(
                DB::raw('bank_deposits.deposited_at'),
                DB::raw('bank_deposits.amount'),
                DB::raw("bank_deposits.source::text as source"),
                DB::raw('bank_deposits.bank_reference'),
            )
            ->orderByDesc('bank_deposits.deposited_at');
        if ($shopId) {
            $depQ->where('bank_deposits.shop_id', $shopId);
        }
        $deposits = $depQ->get()->map(fn ($r) => (array) $r)->toArray();

        $this->txSummary = [
            'sessions'             => $sessData,
            'expenses_by_category' => $expensesByCategory,
            'withdrawals'          => $withdrawals,
            'bank_deposits'        => $deposits,
        ];
    }

    private function loadTransactions(): void
    {
        $from   = $this->dateFrom;
        $to     = $this->dateTo;
        $shopId = $this->shopFilter !== 'all' ? (int) $this->shopFilter : null;
        $types  = $this->txFilter === 'all'
            ? ['sale', 'expense', 'withdrawal', 'deposit', 'repayment']
            : [$this->txFilter];

        $all = collect();

        if (in_array('sale', $types)) {
            $q = DB::table('sales')
                ->join('shops', 'sales.shop_id', '=', 'shops.id')
                ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
                ->whereNull('sales.deleted_at')
                ->whereNull('sales.voided_at')
                ->whereBetween(DB::raw('sales.sale_date::date'), [$from, $to])
                ->select(
                    DB::raw("'sale' as type"),
                    DB::raw('sales.sale_date as occurred_at'),
                    DB::raw('shops.name as shop_name'),
                    DB::raw("COALESCE(customers.name, 'Walk-in') as description"),
                    DB::raw('COALESCE(sales.total, 0) as amount'),
                    DB::raw("sales.payment_method::text as payment_method"),
                    DB::raw('sales.sale_number as reference'),
                );
            if ($shopId) {
                $q->where('sales.shop_id', $shopId);
            }
            $all = $all->merge($q->get());
        }

        if (in_array('expense', $types)) {
            $q = DB::table('expenses')
                ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
                ->join('shops', 'daily_sessions.shop_id', '=', 'shops.id')
                ->leftJoin('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
                ->whereNull('expenses.deleted_at')
                ->whereBetween('daily_sessions.session_date', [$from, $to])
                ->select(
                    DB::raw("'expense' as type"),
                    DB::raw('expenses.recorded_at as occurred_at'),
                    DB::raw('shops.name as shop_name'),
                    DB::raw("COALESCE(expense_categories.name, 'Uncategorized') as description"),
                    DB::raw('COALESCE(expenses.amount, 0) as amount'),
                    DB::raw("expenses.payment_method::text as payment_method"),
                    DB::raw('expenses.receipt_reference as reference'),
                );
            if ($shopId) {
                $q->where('daily_sessions.shop_id', $shopId);
            }
            $all = $all->merge($q->get());
        }

        if (in_array('withdrawal', $types)) {
            $q = DB::table('owner_withdrawals')
                ->join('shops', 'owner_withdrawals.shop_id', '=', 'shops.id')
                ->join('daily_sessions', 'owner_withdrawals.daily_session_id', '=', 'daily_sessions.id')
                ->whereNull('owner_withdrawals.deleted_at')
                ->whereBetween('daily_sessions.session_date', [$from, $to])
                ->select(
                    DB::raw("'withdrawal' as type"),
                    DB::raw('owner_withdrawals.recorded_at as occurred_at'),
                    DB::raw('shops.name as shop_name'),
                    DB::raw("COALESCE(owner_withdrawals.reason, 'Owner withdrawal') as description"),
                    DB::raw('COALESCE(owner_withdrawals.amount, 0) as amount'),
                    DB::raw("owner_withdrawals.method::text as payment_method"),
                    DB::raw('owner_withdrawals.momo_reference as reference'),
                );
            if ($shopId) {
                $q->where('owner_withdrawals.shop_id', $shopId);
            }
            $all = $all->merge($q->get());
        }

        if (in_array('deposit', $types)) {
            $q = DB::table('bank_deposits')
                ->join('shops', 'bank_deposits.shop_id', '=', 'shops.id')
                ->join('daily_sessions', 'bank_deposits.daily_session_id', '=', 'daily_sessions.id')
                ->whereBetween('daily_sessions.session_date', [$from, $to])
                ->select(
                    DB::raw("'deposit' as type"),
                    DB::raw('bank_deposits.deposited_at as occurred_at'),
                    DB::raw('shops.name as shop_name'),
                    DB::raw("CONCAT('Bank deposit — ', bank_deposits.source::text) as description"),
                    DB::raw('COALESCE(bank_deposits.amount, 0) as amount'),
                    DB::raw("bank_deposits.source::text as payment_method"),
                    DB::raw('bank_deposits.bank_reference as reference'),
                );
            if ($shopId) {
                $q->where('bank_deposits.shop_id', $shopId);
            }
            $all = $all->merge($q->get());
        }

        if (in_array('repayment', $types)) {
            $q = DB::table('credit_repayments')
                ->join('shops', 'credit_repayments.shop_id', '=', 'shops.id')
                ->leftJoin('customers', 'credit_repayments.customer_id', '=', 'customers.id')
                ->whereBetween(DB::raw('credit_repayments.repayment_date::date'), [$from, $to])
                ->select(
                    DB::raw("'repayment' as type"),
                    DB::raw('credit_repayments.repayment_date as occurred_at'),
                    DB::raw('shops.name as shop_name'),
                    DB::raw("COALESCE(customers.name, 'Customer') as description"),
                    DB::raw('COALESCE(credit_repayments.amount, 0) as amount'),
                    DB::raw("credit_repayments.payment_method::text as payment_method"),
                    DB::raw('credit_repayments.reference as reference'),
                );
            if ($shopId) {
                $q->where('credit_repayments.shop_id', $shopId);
            }
            $all = $all->merge($q->get());
        }

        $this->transactions = $all
            ->sortByDesc('occurred_at')
            ->values()
            ->take(500)
            ->map(fn ($r) => (array) $r)
            ->toArray();
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
