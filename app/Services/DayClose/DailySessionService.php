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
            // Per-method split: one aggregated row per sale (no per-row queries).
            ->leftJoinSub(
                DB::table('sale_payments')
                    ->selectRaw("
                        sale_id,
                        SUM(CASE WHEN payment_method = 'cash'          THEN amount ELSE 0 END) as cash,
                        SUM(CASE WHEN payment_method = 'mobile_money'  THEN amount ELSE 0 END) as momo,
                        SUM(CASE WHEN payment_method = 'card'          THEN amount ELSE 0 END) as card,
                        SUM(CASE WHEN payment_method = 'bank_transfer' THEN amount ELSE 0 END) as bank_transfer,
                        SUM(CASE WHEN payment_method = 'credit'        THEN amount ELSE 0 END) as credit
                    ")
                    ->groupBy('sale_id'),
                'sp',
                'sp.sale_id', '=', 'sales.id'
            )
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
                DB::raw('COUNT(sale_items.id) as boxes'),
                DB::raw('COALESCE(MAX(sp.cash), 0) as cash'),
                DB::raw('COALESCE(MAX(sp.momo), 0) as momo'),
                DB::raw('COALESCE(MAX(sp.card), 0) as card'),
                DB::raw('COALESCE(MAX(sp.bank_transfer), 0) as bank_transfer'),
                DB::raw('COALESCE(MAX(sp.credit), 0) as credit'),
                'sales.is_split_payment as is_split',
                'sales.has_price_override'
            )
            ->get()
            // Local (business-timezone) time of the sale, for the notebook-style invoice list.
            ->each(function ($row) {
                $row->sale_time = \Carbon\Carbon::parse($row->sale_date, 'UTC')
                    ->setTimezone(config('tenant.timezone'))->format('H:i');
            });

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
            ->leftJoin('users as expense_recorder', 'expenses.recorded_by', '=', 'expense_recorder.id')
            ->when($shopId !== null, fn ($q) => $q->where('daily_sessions.shop_id', $shopId))
            ->whereBetween('daily_sessions.session_date', [$dateFrom, $dateTo])
            ->whereNull('expenses.deleted_at')
            ->orderBy('daily_sessions.session_date')
            ->select(
                'daily_sessions.session_date',
                'expense_categories.name as category',
                'expenses.description',
                'expenses.amount',
                'expenses.payment_method',
                'shops.name as shop_name',
                'expense_recorder.name as recorded_by_name'
            )
            ->get();

        // Expense split by payment method and by category — derived from the
        // itemized rows above (same session_date scope as total_expenses, so the
        // parts always add up to it; no extra queries). "bank" = everything that
        // isn't cash or mobile money (bank_transfer + other) so cash+momo+bank
        // always equals the total.
        $expenseMethod = fn ($r) => (string) $r->payment_method === 'cash' ? 'cash'
            : ((string) $r->payment_method === 'mobile_money' ? 'momo' : 'bank');
        $expensesByCategory = $expensesDetailed
            ->groupBy('category')
            ->map(function ($rows, $category) use ($expenseMethod) {
                $by = fn (string $m) => (int) $rows->filter(fn ($r) => $expenseMethod($r) === $m)->sum('amount');

                return (object) [
                    'category' => $category,
                    'count'    => $rows->count(),
                    'cash'     => $by('cash'),
                    'momo'     => $by('momo'),
                    'bank'     => $by('bank'),
                    'total'    => (int) $rows->sum('amount'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Bank deposits, itemized per line (drives the "Bank Deposits" table) —
        // a deposit is cash or MoMo physically moved into the bank account,
        // already reflected in Cash Register's Bank Movement net figure, but
        // was previously never itemized anywhere on the report, unlike
        // Detailed Expenses above.
        $depositsDetailed = DB::table('bank_deposits')
            ->join('daily_sessions', 'bank_deposits.daily_session_id', '=', 'daily_sessions.id')
            ->join('shops', 'daily_sessions.shop_id', '=', 'shops.id')
            ->leftJoin('users as deposit_recorder', 'bank_deposits.deposited_by', '=', 'deposit_recorder.id')
            ->when($shopId !== null, fn ($q) => $q->where('daily_sessions.shop_id', $shopId))
            ->whereBetween('daily_sessions.session_date', [$dateFrom, $dateTo])
            ->whereNull('bank_deposits.deleted_at')
            ->orderBy('daily_sessions.session_date')
            ->select(
                'daily_sessions.session_date',
                'bank_deposits.source',
                'bank_deposits.bank_reference',
                'bank_deposits.notes',
                'bank_deposits.amount',
                'shops.name as shop_name',
                'deposit_recorder.name as deposited_by_name'
            )
            ->get();
        $totalDeposits = (int) $depositsDetailed->sum('amount');

        // Notebook "versement" view of the same rows (new keys; deposits_detailed unchanged).
        $bankDepositsDetailed = $depositsDetailed->map(fn ($r) => (object) [
            'date'             => $r->session_date,
            'shop_name'        => $r->shop_name,
            'source'           => $r->source,
            'bank_reference'   => $r->bank_reference,
            'amount'           => (int) $r->amount,
            'deposited_by_name'=> $r->deposited_by_name,
        ])->values();

        // Owner withdrawals (household spending), scoped by session_date like expenses.
        // method is a PG native enum — selected as-is, compared via string cast in PHP.
        $withdrawalsDetailed = DB::table('owner_withdrawals')
            ->join('daily_sessions', 'owner_withdrawals.daily_session_id', '=', 'daily_sessions.id')
            ->join('shops', 'daily_sessions.shop_id', '=', 'shops.id')
            ->leftJoin('users as withdrawal_recorder', 'owner_withdrawals.recorded_by', '=', 'withdrawal_recorder.id')
            ->when($shopId !== null, fn ($q) => $q->where('daily_sessions.shop_id', $shopId))
            ->whereBetween('daily_sessions.session_date', [$dateFrom, $dateTo])
            ->whereNull('owner_withdrawals.deleted_at')
            ->orderBy('daily_sessions.session_date')
            ->orderBy('owner_withdrawals.recorded_at')
            ->select(
                'daily_sessions.session_date as date',
                'shops.name as shop_name',
                'owner_withdrawals.reason',
                'owner_withdrawals.method',
                'owner_withdrawals.amount',
                'withdrawal_recorder.name as recorded_by_name'
            )
            ->get();

        // Refunds: returns processed in the business-day window (same basis as
        // computeLiveSummary()'s cash refunds). Exchanges are not money out, so
        // they are excluded from every refund figure, as in the live summary.
        $refundsDetailed = DB::table('returns')
            ->join('shops', 'returns.shop_id', '=', 'shops.id')
            ->when($shopId !== null, fn ($q) => $q->where('returns.shop_id', $shopId))
            ->whereBetween('returns.processed_at', [$start, $end])
            ->whereNull('returns.deleted_at')
            ->where('returns.is_exchange', false)
            ->orderBy('returns.processed_at')
            ->select(
                'returns.processed_at as date',
                'shops.name as shop_name',
                'returns.return_number',
                'returns.customer_name',
                'returns.refund_method as method',
                'returns.refund_amount as amount'
            )
            ->get()
            ->each(function ($row) {
                $row->date = \Carbon\Carbon::parse($row->date, 'UTC')->setTimezone(config('tenant.timezone'))->toDateString();
                $row->amount = (int) $row->amount;
            });

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
                DB::raw('COUNT(*) as repayment_count'),
                // Per-method split; "bank" = everything that isn't cash/MoMo so
                // cash + momo + bank always equals amount.
                DB::raw("SUM(CASE WHEN credit_repayments.payment_method = 'cash' THEN credit_repayments.amount ELSE 0 END) as cash"),
                DB::raw("SUM(CASE WHEN credit_repayments.payment_method = 'mobile_money' THEN credit_repayments.amount ELSE 0 END) as momo"),
                DB::raw("SUM(CASE WHEN credit_repayments.payment_method NOT IN ('cash','mobile_money') THEN credit_repayments.amount ELSE 0 END) as bank")
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

        // Cost of Goods Sold — what the sold stock cost the business, so the
        // report can show real gross/net profit instead of just top-line
        // revenue minus operating expenses. Uses each product's CURRENT
        // purchase_price: sale_items doesn't snapshot cost-at-time-of-sale,
        // so if a product's cost changed mid-period this is a good-faith
        // estimate, not an exact historical figure. quantity_sold already
        // holds the correct item count for both full-box rows (it equals
        // items_per_box on those) and individual-item rows, so
        // purchase_price * quantity_sold is a uniform per-row cost
        // regardless of box/item sale mode — no separate box-cost lookup
        // needed.
        $totalCogs = (int) DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->sum(DB::raw('products.purchase_price * sale_items.quantity_sold'));

        $grossProfit = (int) ($saleTotals->total ?? 0) - $totalCogs;

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
            'total_deposits'           => $totalDeposits,
            'deposits_detailed'        => $depositsDetailed,
            'credits_by_customer'      => $creditsByCustomer,
            'total_repayments'         => $totalRepayments,
            'repayments_by_customer'   => $repaymentsByCustomer,
            'cash_by_customer'         => $cashByCustomer,
            'momo_by_customer'         => $momoByCustomer,
            'card_by_customer'         => $cardByCustomer,
            'bank_by_customer'         => $bankByCustomer,
            'outstanding_receivables'  => $outstandingReceivables,
            'customers_owing_count'    => $customersOwingCount,
            // Credit owed by customers not tied to any shop (customers.shop_id NULL). A single-shop
            // 'outstanding_receivables' excludes these, the all-shops figure includes them.
            'unassigned_receivables'   => (int) Customer::whereNull('shop_id')->sum('outstanding_balance'),
            // total_cogs/gross_profit: what the sold stock cost, and revenue
            // minus that cost — see the estimate caveat on $totalCogs above.
            'total_cogs'               => $totalCogs,
            'gross_profit'             => $grossProfit,
            // Real net profit for the period: revenue (including credit
            // sales, since they're still counted as a sale) minus what that
            // stock cost minus operating expenses. This is period activity,
            // not a cash-in-hand figure — unlike Business Position above, it
            // DOES move with the date filter, and unlike a plain revenue
            // figure it does NOT mean "money currently in hand" (some of the
            // revenue may still be uncollected credit — see total_sales_credit).
            'net_for_period'           => $grossProfit - $totalExpenses,

            // ---- Additive keys (DAILY_REPORT_EXT_01) ----
            'total_repayments_cash'    => (int) $repaymentsByCustomer->sum('cash'),
            'total_repayments_momo'    => (int) $repaymentsByCustomer->sum('momo'),
            'total_repayments_bank'    => (int) $repaymentsByCustomer->sum('bank'),
            'total_expenses_cash'      => (int) $expensesByCategory->sum('cash'),
            'total_expenses_momo'      => (int) $expensesByCategory->sum('momo'),
            'total_expenses_bank'      => (int) $expensesByCategory->sum('bank'),
            'expenses_by_category'     => $expensesByCategory,
            'total_withdrawals'        => (int) $withdrawalsDetailed->sum('amount'),
            'total_withdrawals_cash'   => (int) $withdrawalsDetailed->filter(fn ($r) => (string) $r->method === 'cash')->sum('amount'),
            'total_withdrawals_momo'   => (int) $withdrawalsDetailed->filter(fn ($r) => (string) $r->method === 'mobile_money')->sum('amount'),
            'withdrawals_detailed'     => $withdrawalsDetailed,
            'total_bank_deposits'      => $totalDeposits,
            'bank_deposits_from_cash'  => (int) $depositsDetailed->where('source', 'cash')->sum('amount'),
            'bank_deposits_from_momo'  => (int) $depositsDetailed->where('source', 'mobile_money')->sum('amount'),
            'bank_deposits_detailed'   => $bankDepositsDetailed,
            'total_refunds'            => (int) $refundsDetailed->sum('amount'),
            'total_refunds_cash'       => (int) $refundsDetailed->filter(fn ($r) => $r->method === 'cash')->sum('amount'),
            'refunds_detailed'         => $refundsDetailed,
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
     * Cash/MoMo/Bank register position per day for a shop over a date range —
     * the opening/closing balance report. One row per DailySession that
     * actually exists in range (a day never opened is simply absent, same
     * convention as every other breakdown on this report); a still-open
     * session (e.g. today, mid-range) reports its live expected_cash rather
     * than a final count, flagged via is_open.
     *
     * MoMo/Bank have no "opening balance you count" the way cash does — there's
     * no drawer to physically verify — so momo/bank are always the computed
     * net movement for that day (computeLiveSummary()), regardless of whether
     * the session is open or closed; only cash prefers the closed session's
     * actual_cash_counted (the physically-verified figure) once one exists.
     */
    public function getCashRegisterByDay(int $shopId, string $dateFrom, string $dateTo): \Illuminate\Support\Collection
    {
        return DailySession::forShop($shopId)
            ->whereBetween('session_date', [$dateFrom, $dateTo])
            ->orderBy('session_date')
            ->get()
            ->map(function (DailySession $session) {
                $isOpen  = $session->isOpen();
                $summary = $this->computeLiveSummary($session);

                return (object) [
                    'date'     => $session->session_date,
                    'opening'  => (int) $session->opening_balance,
                    'closing'  => $isOpen
                        ? (int) $summary['expected_cash']
                        : (int) $session->actual_cash_counted,
                    'variance' => $isOpen ? null : (int) $session->cash_variance,
                    'is_open'  => $isOpen,
                    'momo'     => (int) $summary['momo_available'],
                    'bank'     => (int) $summary['bank_available'],
                ];
            });
    }

    /**
     * Cash/MoMo/Bank register position over a date range, one row per shop —
     * the owner's "All Shops" equivalent of getCashRegisterByDay(). A single
     * cash drawer can't be merged across shops, so this bookends each
     * shop's own opening (its first session in range) and closing (its last
     * session in range, live if still open) and sums that shop's variances
     * across the range. MoMo/Bank are summed across every day in range (they're
     * daily net-movement figures, not a running balance, so summing days is
     * the period total — unlike cash, which only has one meaningful opening
     * and one meaningful closing). Shops with no session at all in range are
     * omitted, same "no data, no row" convention as the rest of this report.
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
                    'momo'      => (int) $days->sum('momo'),
                    'bank'      => (int) $days->sum('bank'),
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
     *
     * Cash-only, deliberately. MoMo/Bank have no persisted opening-balance or
     * physical-count field the way cash does (opening_balance + actual_cash_
     * counted/cash_variance at close) — computeLiveSummary()'s momo/bank
     * figures are only ever a single session's one-day net movement, never a
     * real running balance. Labeling that "MoMo/Bank Balance" here would
     * claim a reconciled figure the system doesn't actually track; see
     * getCashRegisterByDay()/getCashRegisterByShop() for the (differently
     * scoped, period-movement, not balance) momo/bank figures this report
     * does support.
     *
     * 'stale' flags when the contributing session is open AND not from
     * today — i.e. nobody has closed/physically counted that shop's drawer
     * since a prior business day, so this "as of now" cash figure includes
     * a session that was never reconciled. The combined ($shopId = null)
     * form also lists which shop(s) via 'stale_shops'.
     */
    public function getCurrentCashPosition(?int $shopId, ?\Illuminate\Support\Collection $shops = null): array
    {
        if ($shopId !== null) {
            $session = DailySession::forShop($shopId)->orderByDesc('session_date')->orderByDesc('id')->first();

            if (! $session) {
                return ['cash' => 0, 'as_of' => null, 'is_open' => false, 'stale' => false];
            }

            $isOpen = $session->isOpen();

            return [
                'cash'    => $isOpen
                    ? (int) $this->computeLiveSummary($session)['expected_cash']
                    : (int) $session->actual_cash_counted,
                'as_of'   => $session->session_date,
                'is_open' => $isOpen,
                'stale'   => $isOpen && ! $session->session_date->isSameDay(business_today()),
            ];
        }

        $perShop = ($shops ?? collect())->map(function ($shop) {
            $position = $this->getCurrentCashPosition($shop->id);
            $position['shop_name'] = $shop->name;

            return $position;
        });

        return [
            'cash'        => (int) $perShop->sum('cash'),
            'as_of'       => $perShop->pluck('as_of')->filter()->sort()->last(),
            'is_open'     => $perShop->contains('is_open', true),
            'stale'       => $perShop->contains('stale', true),
            'stale_shops' => $perShop->where('stale', true)
                ->map(fn ($p) => ['shop_name' => $p['shop_name'], 'as_of' => $p['as_of']])
                ->values(),
        ];
    }

    /**
     * Owner-only profit breakdown: every sold item line per product (full boxes
     * and loose items alike), so the rows add up to the whole period's sales.
     * cost = purchase_price × quantity_sold at the product's CURRENT purchase
     * price (sale_items has no cost snapshot) — an estimate. Never call this
     * for shop managers.
     */
    public function getProfitByProduct(?int $shopId, string $dateFrom, string $dateTo): \Illuminate\Support\Collection
    {
        $start = \Carbon\Carbon::parse($dateFrom, config('tenant.timezone'))->startOfDay()->utc();
        $end   = \Carbon\Carbon::parse($dateTo, config('tenant.timezone'))->endOfDay()->utc();

        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc(DB::raw('SUM(sale_items.line_total)'))
            ->selectRaw('products.name as product_name,
                SUM(CASE WHEN sale_items.is_full_box THEN 1 ELSE 0 END) as boxes,
                SUM(sale_items.line_total) as revenue,
                SUM(products.purchase_price * sale_items.quantity_sold) as cost')
            ->get()
            ->map(function ($r) {
                $r->boxes   = (int) $r->boxes;
                $r->revenue = (int) $r->revenue;
                $r->cost    = (int) $r->cost;
                $r->profit  = $r->revenue - $r->cost;

                return $r;
            });
    }

    /**
     * Caisse reconciliation per session over a date range (all shops when
     * $shopId is null): opening + cash in − cash out = expected, vs counted.
     * Open sessions use computeLiveSummary(); closed/locked use the stored
     * columns. The expected-cash formula is NOT re-implemented here —
     * 'expected' always comes from one of those two sources, and
     * 'other_adjustments' absorbs any term the listed lines don't cover so
     * the displayed lines always add up to 'expected'.
     */
    public function getCashReconciliation(?int $shopId, string $dateFrom, string $dateTo): \Illuminate\Support\Collection
    {
        return DailySession::with('shop')
            ->when($shopId !== null, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween('session_date', [$dateFrom, $dateTo])
            ->orderBy('session_date')
            ->orderBy('shop_id')
            ->get()
            ->map(function (DailySession $s) {
                $isOpen = $s->isOpen();
                $live   = $isOpen ? $this->computeLiveSummary($s) : null;
                $pick   = fn (string $liveKey, string $col) => (int) ($isOpen ? ($live[$liveKey] ?? 0) : ($s->{$col} ?? 0));

                $opening     = (int) $s->opening_balance;
                $cashSales   = $pick('total_sales_cash', 'total_sales_cash');
                $cashRepay   = $pick('total_repayments_cash', 'total_repayments_cash');
                $cashRefunds = $pick('total_refunds_cash', 'total_refunds_cash');
                $cashExp     = $pick('total_expenses_cash', 'total_expenses_cash');
                $cashWd      = $pick('total_withdrawals_cash', 'total_withdrawals_cash');
                $cashDep     = $pick('cash_deposits', 'cash_deposits');
                $expected    = $pick('expected_cash', 'expected_cash');

                return (object) [
                    'date'                => $s->session_date,
                    'shop_id'             => $s->shop_id,
                    'shop_name'           => $s->shop->name ?? "Shop #{$s->shop_id}",
                    'status'              => (string) $s->status,
                    'source'              => $isOpen ? 'live' : 'stored',
                    'opening'             => $opening,
                    'cash_sales'          => $cashSales,
                    'cash_repayments'     => $cashRepay,
                    'cash_refunds'        => $cashRefunds,
                    'cash_expenses'       => $cashExp,
                    'cash_withdrawals'    => $cashWd,
                    'cash_deposits'       => $cashDep,
                    'other_adjustments'   => $expected - ($opening + $cashSales + $cashRepay - $cashRefunds - $cashExp - $cashWd - $cashDep),
                    'expected'            => $expected,
                    'counted'             => $isOpen ? null : (int) $s->actual_cash_counted,
                    'variance'            => $isOpen ? null : (int) $s->cash_variance,
                    'cash_to_owner_momo'  => (int) ($s->cash_to_owner_momo ?? 0),
                    'cash_retained'       => (int) ($s->cash_retained ?? 0),
                ];
            })
            ->values();
    }

    /**
     * Headline totals for the period to compare against: the same weekday one
     * week earlier for a single day, otherwise the previous period of the same
     * length ending the day before $dateFrom. At most 3 aggregate queries, same
     * filters as computeRangeSummary() (which is deliberately not called — too heavy).
     */
    public function computeComparisonTotals(?int $shopId, string $dateFrom, string $dateTo): array
    {
        $tz   = config('tenant.timezone');
        $from = \Carbon\Carbon::parse($dateFrom, $tz)->startOfDay();
        $to   = \Carbon\Carbon::parse($dateTo, $tz)->startOfDay();

        if ($from->isSameDay($to)) {
            $prevFrom = $from->copy()->subWeek();
            $prevTo   = $prevFrom->copy();
            $label    = $prevFrom->format('D j M');
        } else {
            $days     = $from->diffInDays($to) + 1;
            $prevTo   = $from->copy()->subDay();
            $prevFrom = $prevTo->copy()->subDays($days - 1);
            $label    = $prevFrom->format('Y-m') === $prevTo->format('Y-m')
                ? $prevFrom->format('j') . '–' . $prevTo->format('j M')
                : $prevFrom->format('j M') . ' – ' . $prevTo->format('j M');
        }

        $pFrom = $prevFrom->toDateString();
        $pTo   = $prevTo->toDateString();
        $start = $prevFrom->copy()->startOfDay()->utc();
        $end   = $prevTo->copy()->endOfDay()->utc();

        $sales = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->selectRaw("
                COALESCE(SUM(sale_payments.amount), 0) as total,
                COALESCE(SUM(CASE WHEN sale_payments.payment_method = 'cash'         THEN sale_payments.amount ELSE 0 END), 0) as cash,
                COALESCE(SUM(CASE WHEN sale_payments.payment_method = 'mobile_money' THEN sale_payments.amount ELSE 0 END), 0) as momo,
                COALESCE(SUM(CASE WHEN sale_payments.payment_method = 'credit'       THEN sale_payments.amount ELSE 0 END), 0) as credit
            ")->first();

        // Same scope as computeRangeSummary(): expenses by daily_sessions.session_date.
        $expenses = (int) DB::table('expenses')
            ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
            ->when($shopId !== null, fn ($q) => $q->where('daily_sessions.shop_id', $shopId))
            ->whereBetween('daily_sessions.session_date', [$pFrom, $pTo])
            ->whereNull('expenses.deleted_at')
            ->sum('expenses.amount');

        $repayments = (int) DB::table('credit_repayments')
            ->when($shopId !== null, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween('repayment_date', [$start, $end])
            ->sum('amount');

        return [
            'label'             => $label,
            'date_from'         => $pFrom,
            'date_to'           => $pTo,
            'total_sales'       => (int) $sales->total,
            'total_sales_cash'  => (int) $sales->cash,
            'total_sales_momo'  => (int) $sales->momo,
            'total_sales_credit'=> (int) $sales->credit,
            'total_expenses'    => $expenses,
            'total_repayments'  => $repayments,
        ];
    }

    /**
     * Data-quality checks for the report period. Each item: code, severity
     * (info|warning|critical), message, shop_name, date, amount (nullable).
     * `unlinked_sales` is intentionally absent: sales has no daily_session_id
     * column (V8), so a sale can never be "unlinked".
     *
     * price_overrides items also carry `details` (one row per modified sale line: list price,
     * sold price, discount, % off). $withCost adds cost and margin per line — owner/admin ONLY,
     * never pass true for a shop manager (purchase price must not reach them).
     */
    public function getReportChecks(?int $shopId, string $dateFrom, string $dateTo, bool $withCost = false): array
    {
        $checks = [];
        $add = function (string $code, string $severity, string $message, ?string $shopName = null, $date = null, ?int $amount = null) use (&$checks) {
            $checks[] = compact('code', 'severity', 'message') + [
                'shop_name' => $shopName,
                'date'      => $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date,
                'amount'    => $amount,
            ];
        };

        $tz    = config('tenant.timezone');
        $start = \Carbon\Carbon::parse($dateFrom, $tz)->startOfDay()->utc();
        $end   = \Carbon\Carbon::parse($dateTo, $tz)->endOfDay()->utc();
        $fmt   = fn ($n) => number_format((int) $n);
        $localDate = fn ($ts) => \Carbon\Carbon::parse($ts, 'UTC')->setTimezone($tz)->toDateString();

        $sessions = DailySession::with('shop')
            ->when($shopId !== null, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween('session_date', [$dateFrom, $dateTo])
            ->orderBy('session_date')->orderBy('shop_id')
            ->get();

        // session_open
        foreach ($sessions->filter->isOpen() as $s) {
            $add('session_open', 'warning',
                'Session is still open — figures are provisional until the day is closed.',
                $s->shop->name ?? null, $s->session_date);
        }

        // payments_mismatch — one item per shop + business day so each carries shop, date and the gap
        $mismatch = DB::table('sales')
            ->join('shops', 'sales.shop_id', '=', 'shops.id')
            ->leftJoin('sale_payments', 'sale_payments.sale_id', '=', 'sales.id')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->groupBy('sales.id', 'sales.sale_number', 'sales.total', 'sales.sale_date', 'shops.name')
            ->havingRaw('sales.total <> COALESCE(SUM(sale_payments.amount), 0)')
            ->orderBy('sales.sale_date')->orderBy('sales.sale_number')
            ->select('sales.sale_number', 'sales.sale_date', 'shops.name as shop_name',
                DB::raw('sales.total - COALESCE(SUM(sale_payments.amount), 0) as diff'))
            ->get();
        foreach ($mismatch->groupBy(fn ($r) => $r->shop_name . '|' . $localDate($r->sale_date)) as $rows) {
            $add('payments_mismatch', 'critical',
                $rows->count() . ' sale(s) whose payments do not add up to the sale total: '
                . $rows->take(10)->pluck('sale_number')->implode(', ') . ($rows->count() > 10 ? ', …' : '') . '.',
                $rows->first()->shop_name, $localDate($rows->first()->sale_date), (int) $rows->sum('diff'));
        }

        // stored_vs_live (closed/locked sessions only, capped)
        $closed = $sessions->reject->isOpen()->values();
        foreach ($closed->take(62) as $s) {
            $live = $this->computeLiveSummary($s);
            $diffs = [];
            foreach (['total_sales', 'total_expenses', 'total_repayments', 'total_bank_deposits', 'total_withdrawals', 'expected_cash'] as $f) {
                $stored = (int) ($s->{$f} ?? 0);
                if ($stored !== (int) $live[$f]) {
                    $diffs[] = "$f stored {$fmt($stored)}, live {$fmt($live[$f])}, Δ {$fmt($live[$f] - $stored)}";
                }
            }
            if ($diffs) {
                $add('stored_vs_live', 'critical',
                    'Closed session no longer matches live data: ' . implode('; ', $diffs) . '.',
                    $s->shop->name ?? null, $s->session_date);
            }
        }
        if ($closed->count() > 62) {
            $add('stored_vs_live', 'info', 'Stored-vs-live check limited to the first 62 closed sessions in range.');
        }

        // cash_variance
        foreach ($closed as $s) {
            if ((int) $s->cash_variance !== 0) {
                $add('cash_variance', 'warning',
                    'Cash counted differs from expected by ' . $fmt($s->cash_variance) . ' RWF.',
                    $s->shop->name ?? null, $s->session_date, (int) $s->cash_variance);
            }
        }

        // price_overrides — one item per shop + business day. `details` = every modified sale line with
        // list price vs sold price; amount = revenue given up (list − sold) so the effect is visible at a glance.
        // Box lines store box-total prices, item lines per-item prices (see sale_items convention).
        $overrideLines = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('shops', 'sales.shop_id', '=', 'shops.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->where('sale_items.price_was_modified', true)
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->orderBy('sales.sale_date')->orderBy('sales.sale_number')->orderBy('sale_items.id')
            ->select('sales.sale_number', 'sales.sale_date', 'shops.name as shop_name', 'products.name as product_name',
                'products.purchase_price', 'sale_items.quantity_sold', 'sale_items.is_full_box',
                'sale_items.original_unit_price', 'sale_items.line_total', 'sale_items.price_modification_reason')
            ->get()
            ->map(function ($r) use ($withCost) {
                $list = (int) ($r->is_full_box ? $r->original_unit_price : $r->original_unit_price * $r->quantity_sold);
                $sold = (int) $r->line_total;
                $line = [
                    'sale_number' => $r->sale_number,
                    'product'     => $r->product_name,
                    'boxes'       => $r->is_full_box ? 1 : 0,
                    'items'       => $r->is_full_box ? 0 : (int) $r->quantity_sold,
                    'list'        => $list,
                    'sold'        => $sold,
                    'discount'    => $list - $sold,
                    'pct'         => $list > 0 ? round(($list - $sold) / $list * 100, 1) : 0,
                    'reason'      => $r->price_modification_reason,
                    'shop_name'   => $r->shop_name,
                    'sale_date'   => $r->sale_date,
                ];
                if ($withCost) {
                    $cost = (int) ($r->purchase_price * $r->quantity_sold);
                    $line['cost']           = $cost;
                    $line['profit_at_list'] = $list - $cost;
                    $line['profit_sold']    = $sold - $cost;
                }

                return (object) $line;
            });
        foreach ($overrideLines->groupBy(fn ($r) => $r->shop_name . '|' . $localDate($r->sale_date)) as $rows) {
            $sales = $rows->pluck('sale_number')->unique();
            $discount = (int) $rows->sum('discount');
            $add('price_overrides', 'info',
                $sales->count() . ' sale(s) with a price override: '
                . $sales->take(10)->implode(', ') . ($sales->count() > 10 ? ', …' : '')
                . '. Revenue given up: ' . $fmt($discount) . ' RWF.',
                $rows->first()->shop_name, $localDate($rows->first()->sale_date), $discount);
            // Aggregate identical products within a sale into one line (2 boxes of X = one row).
            $checks[array_key_last($checks)]['details'] = $rows->groupBy(fn ($r) => $r->sale_number . '|' . $r->product)->map(function ($g) use ($withCost) {
                $boxes = (int) $g->sum('boxes');
                $items = (int) $g->sum('items');
                $list  = (int) $g->sum('list');
                $sold  = (int) $g->sum('sold');
                $row = [
                    'sale_number' => $g->first()->sale_number,
                    'product'     => $g->first()->product,
                    'qty'         => trim(($boxes ? $boxes . ' ' . \Illuminate\Support\Str::plural('box', $boxes) : '')
                                     . ($boxes && $items ? ' + ' : '')
                                     . ($items ? $items . ' ' . \Illuminate\Support\Str::plural('item', $items) : '')),
                    'list'        => $list,
                    'sold'        => $sold,
                    'discount'    => $list - $sold,
                    'pct'         => $list > 0 ? round(($list - $sold) / $list * 100, 1) : 0,
                ];
                if ($withCost) {
                    $row['cost']           = (int) $g->sum('cost');
                    $row['profit_at_list'] = $list - $row['cost'];
                    $row['profit_sold']    = $sold - $row['cost'];
                }

                return $row;
            })->values()->all();
        }

        // voided_sales — one item per shop + business day
        $voided = DB::table('sales')
            ->join('shops', 'sales.shop_id', '=', 'shops.id')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->whereNotNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->orderBy('sales.sale_date')
            ->select('sales.sale_number', 'sales.sale_date', 'sales.total', 'shops.name as shop_name')
            ->get();
        foreach ($voided->groupBy(fn ($r) => $r->shop_name . '|' . $localDate($r->sale_date)) as $rows) {
            $add('voided_sales', 'info',
                $rows->count() . ' voided sale(s) totalling ' . $fmt($rows->sum('total')) . ' RWF: '
                . $rows->take(10)->pluck('sale_number')->implode(', ') . ($rows->count() > 10 ? ', …' : '') . '.',
                $rows->first()->shop_name, $localDate($rows->first()->sale_date), (int) $rows->sum('total'));
        }

        return $checks;
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
