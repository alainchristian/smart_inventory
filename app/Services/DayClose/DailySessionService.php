<?php

namespace App\Services\DayClose;

use App\Enums\AlertSeverity;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\CreditRepayment;
use App\Models\Customer;
use App\Models\DailySession;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DailySessionService
{
    /**
     * UTC datetime bounds [start, end] for a business-timezone calendar date.
     * session_date is a date-only column, but sale_date/processed_at/repayment_date
     * are UTC-stored timestamps, so a plain whereDate() would compare against the
     * UTC calendar day instead of the business (Kigali) calendar day.
     */
    private function businessDayRangeUtc(string $date): array
    {
        $start = \Carbon\Carbon::parse($date, config('tenant.timezone'))->startOfDay();

        return [$start->copy()->utc(), $start->copy()->endOfDay()->utc()];
    }

    /**
     * Open a new daily session for a shop.
     */
    public function openSession(User $user, int $shopId, int $openingBalance, string $date): DailySession
    {
        if (! $user->isShopManager()) {
            abort(403, 'Only shop managers can open sessions.');
        }

        if ($user->location_id !== $shopId) {
            abort(403, 'You can only open a session for your own shop.');
        }

        if (DailySession::forShop($shopId)->forDate($date)->exists()) {
            throw new \Exception('A session already exists for today.');
        }

        return DB::transaction(function () use ($user, $shopId, $openingBalance, $date) {
            $session = DailySession::create([
                'session_date'    => $date,
                'shop_id'         => $shopId,
                'opening_balance' => $openingBalance,
                'opened_by'       => $user->id,
                'opened_at'       => now(),
                'status'          => 'open',
            ]);

            ActivityLog::create([
                'user_id'           => $user->id,
                'user_name'         => $user->name,
                'action'            => 'daily_session_opened',
                'entity_type'       => 'daily_session',
                'entity_id'         => $session->id,
                'entity_identifier' => $session->session_date->format('Y-m-d'),
            ]);

            return $session;
        });
    }

    /**
     * Compute live summary figures for the close wizard (not stored yet).
     * Uses sale_payments for accurate split-payment support.
     */
    public function computeLiveSummary(DailySession $session): array
    {
        $date   = $session->session_date->toDateString();
        $shopId = $session->shop_id;
        $range  = $this->businessDayRangeUtc($date);

        // Sales breakdown via sale_payments (handles split payments correctly)
        $saleTotals = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->where('sales.shop_id', $shopId)
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', $range)
            ->selectRaw("
                SUM(CASE WHEN sale_payments.payment_method = 'cash'          THEN sale_payments.amount ELSE 0 END) as cash,
                SUM(CASE WHEN sale_payments.payment_method = 'mobile_money'  THEN sale_payments.amount ELSE 0 END) as momo,
                SUM(CASE WHEN sale_payments.payment_method = 'card'          THEN sale_payments.amount ELSE 0 END) as card,
                SUM(CASE WHEN sale_payments.payment_method = 'bank_transfer' THEN sale_payments.amount ELSE 0 END) as bank_transfer,
                SUM(CASE WHEN sale_payments.payment_method = 'credit'        THEN sale_payments.amount ELSE 0 END) as credit_sales,
                SUM(CASE WHEN sale_payments.payment_method NOT IN ('cash','mobile_money','card','bank_transfer','credit') THEN sale_payments.amount ELSE 0 END) as other,
                SUM(sale_payments.amount) as total,
                COUNT(DISTINCT sales.id) as transaction_count
            ")->first();

        // Cash refunds (cash returns only, no exchanges)
        $cashRefunds = ReturnModel::where('shop_id', $shopId)
            ->whereBetween('processed_at', $range)
            ->whereNull('deleted_at')
            ->where('refund_method', 'cash')
            ->where('is_exchange', false)
            ->sum('refund_amount');

        // Expenses in this session
        // payment_method is a PG native enum — use ::text cast to avoid PDO binding type mismatch
        $expensesQuery = $session->expenses()->whereNull('deleted_at');
        $totalExpenses = (int) (clone $expensesQuery)->sum('amount');
        $cashExpenses  = (int) (clone $expensesQuery)->whereRaw("payment_method::text = 'cash'")->sum('amount');
        $momoExpenses  = (int) (clone $expensesQuery)->whereRaw("payment_method::text = 'mobile_money'")->sum('amount');
        $bankExpenses  = (int) (clone $expensesQuery)->whereRaw("payment_method::text = 'bank_transfer'")->sum('amount');
        $expenseCount  = (int) (clone $expensesQuery)->count();

        // Owner withdrawals
        // method is a PG native enum (withdrawal_method) — use ::text cast
        $withdrawalQuery = $session->ownerWithdrawals()->whereNull('deleted_at');
        $totalWithdrawals = (int) $withdrawalQuery->sum('amount');
        $cashWithdrawals  = (int) (clone $withdrawalQuery)->whereRaw("method::text = 'cash'")->sum('amount');
        $momoWithdrawals  = (int) (clone $withdrawalQuery)->whereRaw("method::text = 'mobile_money'")->sum('amount');
        $withdrawalCount  = (int) $withdrawalQuery->count();

        // Bank deposits split by source (cash drawer vs MoMo wallet)
        $depositsQuery = $session->bankDeposits()->whereNull('deleted_at');
        $cashDeposits  = (int) (clone $depositsQuery)->where('source', 'cash')->sum('amount');
        $momoDeposits  = (int) (clone $depositsQuery)->where('source', 'mobile_money')->sum('amount');
        $totalDeposits = $cashDeposits + $momoDeposits;
        $depositCount  = (int) $depositsQuery->count();

        // Credit repayments received today (split by payment method)
        $repaymentQuery  = CreditRepayment::where('shop_id', $shopId)
            ->whereBetween('repayment_date', $range);
        $cashRepayments  = (int) (clone $repaymentQuery)->where('payment_method', 'cash')->sum('amount');
        $momoRepayments  = (int) (clone $repaymentQuery)->where('payment_method', 'mobile_money')->sum('amount');
        $bankRepayments  = (int) (clone $repaymentQuery)->whereIn('payment_method', ['bank_transfer', 'card'])->sum('amount');
        $totalRepayments = (int) $repaymentQuery->sum('amount');

        // expected_cash: cash sales + cash repayments in; refunds/expenses/withdrawals/deposits out
        $expectedCash = $session->opening_balance
            + (int) ($saleTotals->cash ?? 0)
            + $cashRepayments
            - (int) $cashRefunds
            - (int) $cashExpenses
            - (int) $cashWithdrawals
            - (int) $cashDeposits;

        // MoMo available balance
        $momoAvailable = (int) ($saleTotals->momo ?? 0)
            + $momoRepayments
            - $momoExpenses
            - $momoWithdrawals
            - $momoDeposits;

        // Bank available balance = bank transfer sales + card sales + bank/card repayments + deposits - bank expenses
        $bankAvailable = (int) ($saleTotals->bank_transfer ?? 0)
            + $bankRepayments
            + $totalDeposits
            - $bankExpenses;

        return [
            'opening_balance'              => (int) $session->opening_balance,
            'total_sales_cash'             => (int) ($saleTotals->cash          ?? 0),
            'total_sales_momo'             => (int) ($saleTotals->momo          ?? 0),
            'total_sales_card'             => (int) ($saleTotals->card          ?? 0),
            'total_sales_bank_transfer'    => (int) ($saleTotals->bank_transfer ?? 0),
            'total_sales_credit'           => (int) ($saleTotals->credit_sales  ?? 0),
            'total_sales_other'            => (int) ($saleTotals->other         ?? 0),
            'total_sales'                  => (int) ($saleTotals->total         ?? 0),
            'transaction_count'     => (int) ($saleTotals->transaction_count ?? 0),
            'total_refunds_cash'    => (int) $cashRefunds,
            'total_expenses'        => $totalExpenses,
            'total_expenses_cash'   => $cashExpenses,
            'total_expenses_momo'   => $momoExpenses,
            'expense_count'         => $expenseCount,
            'total_withdrawals'     => $totalWithdrawals,
            'total_withdrawals_cash'=> $cashWithdrawals,
            'total_withdrawals_momo'=> $momoWithdrawals,
            'withdrawal_count'      => $withdrawalCount,
            'total_bank_deposits'   => $totalDeposits,
            'cash_deposits'         => $cashDeposits,
            'momo_deposits'         => $momoDeposits,
            'bank_deposit_count'    => $depositCount,
            'total_repayments'      => $totalRepayments,
            'total_repayments_cash' => $cashRepayments,
            'total_repayments_momo' => $momoRepayments,
            'total_repayments_bank' => $bankRepayments,
            'expected_cash'         => (int) $expectedCash,
            'momo_available'        => (int) $momoAvailable,
            'bank_available'        => (int) $bankAvailable,
            'total_expenses_bank'   => $bankExpenses,
        ];
    }

    /**
     * Reporting figures over a date range (business-timezone, inclusive on
     * both ends), for one shop or — when $shopId is null — combined across
     * every shop (the owner's "All Shops" view). Unlike computeLiveSummary(),
     * this is not tied to a single session — it has no opening_balance/
     * expected_cash/momo_available concepts, which only mean something
     * reconciled one cash drawer at a time. This is read-only reporting data.
     */
    public function computeRangeSummary(?int $shopId, string $dateFrom, string $dateTo): array
    {
        $start = \Carbon\Carbon::parse($dateFrom, config('tenant.timezone'))->startOfDay()->utc();
        $end   = \Carbon\Carbon::parse($dateTo, config('tenant.timezone'))->endOfDay()->utc();

        // Sales / payment-channel breakdown via sale_payments (split-payment safe)
        $saleTotals = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->selectRaw("
                SUM(CASE WHEN sale_payments.payment_method = 'cash'          THEN sale_payments.amount ELSE 0 END) as cash,
                SUM(CASE WHEN sale_payments.payment_method = 'mobile_money'  THEN sale_payments.amount ELSE 0 END) as momo,
                SUM(CASE WHEN sale_payments.payment_method = 'card'          THEN sale_payments.amount ELSE 0 END) as card,
                SUM(CASE WHEN sale_payments.payment_method = 'bank_transfer' THEN sale_payments.amount ELSE 0 END) as bank_transfer,
                SUM(CASE WHEN sale_payments.payment_method = 'credit'        THEN sale_payments.amount ELSE 0 END) as credit_sales,
                SUM(CASE WHEN sale_payments.payment_method NOT IN ('cash','mobile_money','card','bank_transfer','credit') THEN sale_payments.amount ELSE 0 END) as other,
                SUM(sale_payments.amount) as total,
                COUNT(DISTINCT sales.id) as transaction_count
            ")->first();

        // Total boxes sold: full-box line items only, on non-voided sales.
        // Each is_full_box=true row IS one box — quantity_sold on that row holds the
        // number of individual items inside the box (items_per_box), not a box count,
        // so this must COUNT rows, never SUM(quantity_sold) (that would inflate the
        // figure by items_per_box, e.g. 23 boxes of 24 showing as "552").
        $totalBoxesSold = (int) SaleItem::whereHas('sale', function ($q) use ($shopId, $start, $end) {
                $q->when($shopId !== null, fn ($qq) => $qq->forShop($shopId))->notVoided()->dateRange($start, $end);
            })
            ->where('is_full_box', true)
            ->count();

        // Boxes sold, itemized per product (drives the "boxes per item" detail table)
        $boxesByProduct = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->where('sale_items.is_full_box', true)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc(DB::raw('SUM(sale_items.line_total)'))
            ->select(
                'products.name as product_name',
                DB::raw('COUNT(*) as boxes'),
                DB::raw('SUM(sale_items.line_total) as amount')
            )
            ->get();

        // Every individual sale in range, with its own box count (full-box
        // line items on that sale) and its own total — drives the "All Sales"
        // detail table, with a grand total row. shop_name is always included
        // (cheap) so the "All Shops" view can show it; a single-shop view
        // simply doesn't render that column.
        $allSales = DB::table('sales')
            ->leftJoin('sale_items', function ($join) {
                $join->on('sale_items.sale_id', '=', 'sales.id')
                     ->where('sale_items.is_full_box', true);
            })
            ->join('shops', 'sales.shop_id', '=', 'shops.id')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->groupBy('sales.id', 'sales.sale_number', 'sales.sale_date', 'sales.customer_name', 'sales.total', 'shops.name')
            ->orderBy('sales.sale_date')
            ->select(
                'sales.sale_number',
                'sales.sale_date',
                'sales.customer_name',
                'sales.total',
                'shops.name as shop_name',
                DB::raw('COUNT(sale_items.id) as boxes')
            )
            ->get();

        // Expenses recorded against daily sessions in range
        // (session_date is a DATE column — plain date-string bounds, no tz conversion)
        $totalExpenses = (int) Expense::whereHas('dailySession', function ($q) use ($shopId, $dateFrom, $dateTo) {
                $q->when($shopId !== null, fn ($qq) => $qq->where('shop_id', $shopId))
                  ->whereBetween('session_date', [$dateFrom, $dateTo]);
            })
            ->whereNull('deleted_at')
            ->sum('amount');

        // Expenses, itemized per line (drives the "detailed expenses" table).
        // There is no structured "paid to" field on Expense — description is the
        // only place a payee name could appear, so it's surfaced as-is per line
        // rather than grouped, since grouping by category would hide it.
        $expensesDetailed = DB::table('expenses')
            ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->join('shops', 'daily_sessions.shop_id', '=', 'shops.id')
            ->when($shopId !== null, fn ($q) => $q->where('daily_sessions.shop_id', $shopId))
            ->whereBetween('daily_sessions.session_date', [$dateFrom, $dateTo])
            ->whereNull('expenses.deleted_at')
            ->orderBy('daily_sessions.session_date')
            ->select(
                'daily_sessions.session_date',
                'expense_categories.name as category',
                'expenses.description',
                'expenses.amount',
                'shops.name as shop_name'
            )
            ->get();

        // Sales per customer, one breakdown per payment channel (Cash / Mobile
        // Money / Card / Bank Transfer / Credit) — each rendered on the report
        // only when it actually has rows, so a disabled or unused channel simply
        // doesn't appear rather than showing an empty table.
        $cashByCustomer   = $this->salesByCustomerForMethod($shopId, 'cash', $start, $end);
        $momoByCustomer   = $this->salesByCustomerForMethod($shopId, 'mobile_money', $start, $end);
        $cardByCustomer   = $this->salesByCustomerForMethod($shopId, 'card', $start, $end);
        $bankByCustomer   = $this->salesByCustomerForMethod($shopId, 'bank_transfer', $start, $end);
        // Credit sales, itemized per customer (drives the "detailed credits" table —
        // new credit issued, i.e. "Amadeni")
        $creditsByCustomer = $this->salesByCustomerForMethod($shopId, 'credit', $start, $end);

        // Credit repayments received, itemized per customer ("ABISHYUVE")
        $repaymentsByCustomer = DB::table('credit_repayments')
            ->join('customers', 'credit_repayments.customer_id', '=', 'customers.id')
            ->when($shopId !== null, fn ($q) => $q->where('credit_repayments.shop_id', $shopId))
            ->whereBetween('credit_repayments.repayment_date', [$start, $end])
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc(DB::raw('SUM(credit_repayments.amount)'))
            ->select(
                'customers.name as customer_name',
                DB::raw('SUM(credit_repayments.amount) as amount'),
                DB::raw('COUNT(*) as repayment_count')
            )
            ->get();
        $totalRepayments = (int) $repaymentsByCustomer->sum('amount');

        // Outstanding customer credit — what customers currently owe, i.e.
        // what the business can assume it will eventually collect. This is a
        // running balance (Customer::outstanding_balance), not scoped to the
        // selected date range like the rest of this method — it reflects the
        // present moment regardless of which period is being viewed.
        $outstandingReceivables = (int) Customer::when($shopId !== null, fn ($q) => $q->where('shop_id', $shopId))
            ->sum('outstanding_balance');
        $customersOwingCount = (int) Customer::when($shopId !== null, fn ($q) => $q->where('shop_id', $shopId))
            ->where('outstanding_balance', '>', 0)
            ->count();

        return [
            'total_sales_cash'          => (int) ($saleTotals->cash          ?? 0),
            'total_sales_momo'          => (int) ($saleTotals->momo          ?? 0),
            'total_sales_card'          => (int) ($saleTotals->card          ?? 0),
            'total_sales_bank_transfer' => (int) ($saleTotals->bank_transfer ?? 0),
            'total_sales_credit'        => (int) ($saleTotals->credit_sales  ?? 0),
            'total_sales_other'        => (int) ($saleTotals->other         ?? 0),
            'total_sales'              => (int) ($saleTotals->total         ?? 0),
            'transaction_count'        => (int) ($saleTotals->transaction_count ?? 0),
            'total_boxes_sold'         => $totalBoxesSold,
            'boxes_by_product'         => $boxesByProduct,
            'all_sales'                => $allSales,
            'total_expenses'           => $totalExpenses,
            'expenses_detailed'        => $expensesDetailed,
            'credits_by_customer'      => $creditsByCustomer,
            'total_repayments'         => $totalRepayments,
            'repayments_by_customer'   => $repaymentsByCustomer,
            'cash_by_customer'         => $cashByCustomer,
            'momo_by_customer'         => $momoByCustomer,
            'card_by_customer'         => $cardByCustomer,
            'bank_by_customer'         => $bankByCustomer,
            'outstanding_receivables'  => $outstandingReceivables,
            'customers_owing_count'    => $customersOwingCount,
        ];
    }

    /**
     * Sales for one payment channel, grouped by customer — shared by every
     * "<channel> — by Customer" breakdown table on the shop daily report.
     */
    private function salesByCustomerForMethod(?int $shopId, string $paymentMethod, $start, $end)
    {
        return DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->where('sale_payments.payment_method', $paymentMethod)
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->groupBy('sales.customer_name')
            ->orderByDesc(DB::raw('SUM(sale_payments.amount)'))
            ->select(
                DB::raw("COALESCE(sales.customer_name, 'Unknown') as customer_name"),
                DB::raw('SUM(sale_payments.amount) as amount'),
                DB::raw('COUNT(DISTINCT sales.id) as sales_count')
            )
            ->get();
    }

    /**
     * Cash register position per day for a shop over a date range — the
     * opening/closing balance report. One row per DailySession that actually
     * exists in range (a day never opened is simply absent, same convention
     * as every other breakdown on this report); a still-open session (e.g.
     * today, mid-range) reports its live expected_cash rather than a final
     * count, flagged via is_open.
     */
    public function getCashRegisterByDay(int $shopId, string $dateFrom, string $dateTo): \Illuminate\Support\Collection
    {
        return DailySession::forShop($shopId)
            ->whereBetween('session_date', [$dateFrom, $dateTo])
            ->orderBy('session_date')
            ->get()
            ->map(function (DailySession $session) {
                $isOpen = $session->isOpen();

                return (object) [
                    'date'     => $session->session_date,
                    'opening'  => (int) $session->opening_balance,
                    'closing'  => $isOpen
                        ? (int) $this->computeLiveSummary($session)['expected_cash']
                        : (int) $session->actual_cash_counted,
                    'variance' => $isOpen ? null : (int) $session->cash_variance,
                    'is_open'  => $isOpen,
                ];
            });
    }

    /**
     * Cash register position over a date range, one row per shop — the
     * owner's "All Shops" equivalent of getCashRegisterByDay(). A single
     * cash drawer can't be merged across shops, so this bookends each
     * shop's own opening (its first session in range) and closing (its last
     * session in range, live if still open) and sums that shop's variances
     * across the range. Shops with no session at all in range are omitted,
     * same "no data, no row" convention as the rest of this report.
     */
    public function getCashRegisterByShop(\Illuminate\Support\Collection $shops, string $dateFrom, string $dateTo): \Illuminate\Support\Collection
    {
        return $shops
            ->map(function ($shop) use ($dateFrom, $dateTo) {
                $days = $this->getCashRegisterByDay($shop->id, $dateFrom, $dateTo);

                if ($days->isEmpty()) {
                    return null;
                }

                return (object) [
                    'shop_id'   => $shop->id,
                    'shop_name' => $shop->name,
                    'opening'   => $days->first()->opening,
                    'closing'   => $days->last()->closing,
                    'variance'  => (int) $days->whereNotNull('variance')->sum('variance'),
                    'is_open'   => $days->last()->is_open,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * What the business currently owns in cash, right now — independent of
     * any report date filter. Uses each shop's most recent session (live
     * expected_cash if still open, otherwise the final actual_cash_counted)
     * rather than anything tied to the selected period, since "what do I
     * currently hold" is a point-in-time question, not a period total.
     * Pass $shopId for a single shop, or null + $shops for the owner's
     * "All Shops" combined figure.
     */
    public function getCurrentCashPosition(?int $shopId, ?\Illuminate\Support\Collection $shops = null): array
    {
        if ($shopId !== null) {
            $session = DailySession::forShop($shopId)->orderByDesc('session_date')->orderByDesc('id')->first();

            if (! $session) {
                return ['cash' => 0, 'as_of' => null, 'is_open' => false];
            }

            return [
                'cash'    => $session->isOpen()
                    ? (int) $this->computeLiveSummary($session)['expected_cash']
                    : (int) $session->actual_cash_counted,
                'as_of'   => $session->session_date,
                'is_open' => $session->isOpen(),
            ];
        }

        $perShop = ($shops ?? collect())->map(fn ($shop) => $this->getCurrentCashPosition($shop->id));

        return [
            'cash'    => (int) $perShop->sum('cash'),
            'as_of'   => $perShop->pluck('as_of')->filter()->sort()->last(),
            'is_open' => $perShop->contains('is_open', true),
        ];
    }

    /**
     * Close a session with the provided closing data.
     */
    public function closeSession(DailySession $session, array $data, User $user): DailySession
    {
        if (! $session->isOpen()) {
            throw new \Exception('Session is not open.');
        }

        if (! $user->isOwner() && (! $user->isShopManager() || $user->location_id !== $session->shop_id)) {
            abort(403, 'You can only close sessions for your own shop.');
        }

        $actualCash      = (int) ($data['actual_cash_counted'] ?? 0);
        $cashToOwnerMomo = (int) ($data['cash_to_owner_momo'] ?? 0);

        if ($actualCash < 0) {
            throw new \Exception('Actual cash counted cannot be negative.');
        }

        if ($cashToOwnerMomo < 0 || $cashToOwnerMomo > $actualCash) {
            throw new \Exception(
                'MoMo transfer to owner (' . number_format($cashToOwnerMomo) . ') cannot exceed actual cash counted (' .
                number_format($actualCash) . ')'
            );
        }

        $cashRetained = $actualCash - $cashToOwnerMomo;

        return DB::transaction(function () use ($session, $data, $user, $actualCash, $cashToOwnerMomo, $cashRetained) {
            $summary  = $this->computeLiveSummary($session);
            $variance = $actualCash - $summary['expected_cash'];

            $session->update([
                'transaction_count'           => $summary['transaction_count'],
                'total_sales_cash'            => $summary['total_sales_cash'],
                'total_sales_momo'            => $summary['total_sales_momo'],
                'total_sales_card'            => $summary['total_sales_card'],
                'total_sales_bank_transfer'   => $summary['total_sales_bank_transfer'],
                'total_sales_credit'          => $summary['total_sales_credit'],
                'total_sales_other'           => $summary['total_sales_other'],
                'total_sales'                 => $summary['total_sales'],
                'total_refunds_cash'     => $summary['total_refunds_cash'],
                'total_expenses'         => $summary['total_expenses'],
                'total_expenses_cash'    => $summary['total_expenses_cash'],
                'total_expenses_momo'    => $summary['total_expenses_momo'],
                'total_withdrawals'      => $summary['total_withdrawals'],
                'total_withdrawals_cash' => $summary['total_withdrawals_cash'],
                'total_withdrawals_momo' => $summary['total_withdrawals_momo'],
                'total_bank_deposits'    => $summary['total_bank_deposits'],
                'bank_deposit_count'     => $summary['bank_deposit_count'],
                'cash_deposits'          => $summary['cash_deposits'],
                'momo_deposits'          => $summary['momo_deposits'],
                'total_repayments'       => $summary['total_repayments'],
                'total_repayments_cash'  => $summary['total_repayments_cash'],
                'total_repayments_momo'  => $summary['total_repayments_momo'],
                'expected_cash'          => $summary['expected_cash'],
                'actual_cash_counted'    => $actualCash,
                'cash_variance'          => $variance,
                'cash_to_owner_momo'     => $cashToOwnerMomo,
                'owner_momo_reference'   => $data['owner_momo_reference'] ?? null,
                'momo_settled'           => (int) ($data['momo_settled'] ?? 0),
                'momo_settled_ref'       => $data['momo_settled_ref'] ?? null,
                'card_settled'           => (int) ($data['card_settled'] ?? 0),
                'card_settled_ref'       => $data['card_settled_ref'] ?? null,
                'other_settled'              => (int) ($data['other_settled'] ?? 0),
                'other_settled_ref'          => $data['other_settled_ref'] ?? null,
                'bank_transfer_settled'      => (int) ($data['bank_transfer_settled'] ?? 0),
                'bank_transfer_settled_ref'  => $data['bank_transfer_settled_ref'] ?? null,
                'cash_retained'              => $cashRetained,
                'notes'                  => $data['notes'] ?? null,
                'status'                 => 'closed',
                'closed_by'              => $user->id,
                'closed_at'              => now(),
            ]);

            $shopName = $session->shop->name ?? "Shop #{$session->shop_id}";
            $owner    = User::where('role', 'owner')->first();

            // Shortage: auto-create locked Expense + alert
            if ($variance < 0) {
                $shortageCategory = ExpenseCategory::firstOrCreate(
                    ['name' => 'Cash Shortage'],
                    [
                        'description' => 'System-generated category for cash shortages recorded on day close.',
                        'applies_to'  => 'shop',
                        'is_active'   => true,
                        'sort_order'  => 9999,
                    ]
                );
                Expense::create([
                    'daily_session_id'    => $session->id,
                    'expense_category_id' => $shortageCategory->id,
                    'amount'              => abs($variance),
                    'description'         => 'Cash shortage recorded on day close — auto-generated by system',
                    'payment_method'      => 'cash',
                    'is_system_generated' => true,
                    'recorded_by'         => $user->id,
                    'recorded_at'         => now(),
                ]);

                Alert::create([
                    'title'        => 'Cash Shortage — ' . $shopName,
                    'message'      => 'Shortage of ' . number_format(abs($variance)) . ' RWF on ' . $session->session_date->format('d M Y'),
                    'severity'     => abs($variance) >= 20000 ? AlertSeverity::CRITICAL : AlertSeverity::WARNING,
                    'entity_type'  => 'daily_session',
                    'entity_id'    => $session->id,
                    'user_id'      => $owner?->id,
                    'action_url'   => route('owner.finance.daily'),
                    'action_label' => 'View Session',
                ]);
            }

            // Surplus: alert only — no accounting entry
            if ($variance > 0) {
                Alert::create([
                    'title'        => 'Cash Surplus — ' . $shopName,
                    'message'      => 'Surplus of ' . number_format($variance) . ' RWF on ' . $session->session_date->format('d M Y') . '. Please review.',
                    'severity'     => AlertSeverity::WARNING,
                    'entity_type'  => 'daily_session',
                    'entity_id'    => $session->id,
                    'user_id'      => $owner?->id,
                    'action_url'   => route('owner.finance.daily'),
                    'action_label' => 'View Session',
                ]);
            }

            ActivityLog::create([
                'user_id'           => $user->id,
                'user_name'         => $user->name,
                'action'            => 'daily_session_closed',
                'entity_type'       => 'daily_session',
                'entity_id'         => $session->id,
                'entity_identifier' => $session->session_date->format('Y-m-d'),
                'details'           => [
                    'variance'          => $variance,
                    'cash_to_owner_momo'=> $cashToOwnerMomo,
                    'retained'          => $cashRetained,
                ],
            ]);

            return $session->fresh();
        });
    }

    /**
     * Reopen a closed session (owner only). Cannot reopen locked sessions.
     */
    public function reopenSession(DailySession $session, User $user): DailySession
    {
        if (! $user->isOwner()) {
            abort(403, 'Only the owner can reopen sessions.');
        }

        if ($session->isLocked()) {
            throw new \Exception('Locked sessions cannot be reopened.');
        }

        if ($session->isOpen()) {
            throw new \Exception('Session is already open.');
        }

        $session->update([
            'status'    => 'open',
            'closed_by' => null,
            'closed_at' => null,
        ]);

        ActivityLog::create([
            'user_id'           => $user->id,
            'user_name'         => $user->name,
            'action'            => 'daily_session_reopened',
            'entity_type'       => 'daily_session',
            'entity_id'         => $session->id,
            'entity_identifier' => $session->session_date->format('Y-m-d'),
            'details'           => ['reason' => 'Owner override reopen'],
        ]);

        return $session->fresh();
    }

    /**
     * Lock a closed session (owner only).
     */
    public function lockSession(DailySession $session, User $user): DailySession
    {
        if (! $user->isOwner()) {
            abort(403, 'Only the owner can lock sessions.');
        }

        if (! $session->isClosed()) {
            throw new \Exception('Only closed sessions can be locked.');
        }

        $session->update([
            'status'    => 'locked',
            'locked_by' => $user->id,
            'locked_at' => now(),
        ]);

        ActivityLog::create([
            'user_id'           => $user->id,
            'user_name'         => $user->name,
            'action'            => 'daily_session_locked',
            'entity_type'       => 'daily_session',
            'entity_id'         => $session->id,
            'entity_identifier' => $session->session_date->format('Y-m-d'),
        ]);

        return $session->fresh();
    }
}
