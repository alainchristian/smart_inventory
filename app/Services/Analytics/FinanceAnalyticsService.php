<?php

namespace App\Services\Analytics;

use App\Models\DailySession;
use App\Models\Expense;
use App\Models\OwnerWithdrawal;
use App\Models\Shop;
use App\Models\SaleItem;
use App\Models\ReturnModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinanceAnalyticsService
{
    // ── Helpers (match SalesAnalyticsService pattern exactly) ────────────────

    private function cacheTtl(string $dateTo): int
    {
        return Carbon::parse($dateTo)->isToday() ? 60 : 900;
    }

    /**
     * Returns the shop_id from a locationFilter string, or null for 'all'.
     * Format is 'shop:N' or 'all'.
     */
    private function shopId(?string $locationFilter): ?int
    {
        if (!$locationFilter || $locationFilter === 'all') {
            return null;
        }
        if (str_starts_with($locationFilter, 'shop:')) {
            return (int) explode(':', $locationFilter)[1];
        }
        return null;
    }

    /**
     * Apply shop filter to a query that has a direct shop_id column.
     */
    private function applyShopFilter($query, ?string $locationFilter, string $column = 'shop_id')
    {
        $id = $this->shopId($locationFilter);
        return $id ? $query->where($column, $id) : $query;
    }

    /**
     * Apply shop filter via daily_sessions join.
     * Use when querying expenses or withdrawals joined to daily_sessions.
     */
    private function applySessionShopFilter($query, ?string $locationFilter)
    {
        $id = $this->shopId($locationFilter);
        return $id ? $query->where('daily_sessions.shop_id', $id) : $query;
    }

    // ── 1. Expense Summary ────────────────────────────────────────────────────

    /**
     * Total expenses by category for the period.
     * Returns: total_expenses, cash_expenses, momo_expenses, expense_count,
     *          by_category (array: name, total, count, pct_of_total, cash, momo)
     *          growth vs prior period.
     */
    public function getExpenseSummary(
        string $dateFrom,
        string $dateTo,
        ?string $locationFilter = 'all'
    ): array {
        $cacheKey = "analytics_finance_expense_summary_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function ()
            use ($dateFrom, $dateTo, $locationFilter) {

            // Current period
            $base = Expense::whereNull('expenses.deleted_at')
                ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
                ->whereBetween('daily_sessions.session_date', [$dateFrom, $dateTo]);
            $base = $this->applySessionShopFilter($base, $locationFilter);

            $totals = (clone $base)
                ->selectRaw("
                    SUM(expenses.amount) as total_expenses,
                    SUM(CASE WHEN expenses.payment_method::text = 'cash'
                        THEN expenses.amount ELSE 0 END) as cash_expenses,
                    SUM(CASE WHEN expenses.payment_method::text = 'mobile_money'
                        THEN expenses.amount ELSE 0 END) as momo_expenses,
                    COUNT(*) as expense_count
                ")
                ->first();

            // By category
            $byCategory = (clone $base)
                ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
                ->where('expenses.is_system_generated', false)
                ->selectRaw("
                    expense_categories.name as category_name,
                    SUM(expenses.amount) as total,
                    COUNT(*) as count,
                    SUM(CASE WHEN expenses.payment_method::text = 'cash'
                        THEN expenses.amount ELSE 0 END) as cash,
                    SUM(CASE WHEN expenses.payment_method::text = 'mobile_money'
                        THEN expenses.amount ELSE 0 END) as momo
                ")
                ->groupBy('expense_categories.name')
                ->orderByDesc('total')
                ->get();

            $grandTotal = (int) ($totals->total_expenses ?? 0);

            // Previous period (same length, immediately before)
            $from     = Carbon::parse($dateFrom);
            $to       = Carbon::parse($dateTo);
            $daySpan  = $from->diffInDays($to) + 1;
            $prevFrom = $from->copy()->subDays($daySpan)->toDateString();
            $prevTo   = $from->copy()->subDay()->toDateString();

            $prevBase = Expense::whereNull('expenses.deleted_at')
                ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
                ->whereBetween('daily_sessions.session_date', [$prevFrom, $prevTo]);
            $prevBase = $this->applySessionShopFilter($prevBase, $locationFilter);
            $prevTotal = (int) ($prevBase->sum('expenses.amount') ?? 0);

            return [
                'total_expenses'  => $grandTotal,
                'cash_expenses'   => (int) ($totals->cash_expenses ?? 0),
                'momo_expenses'   => (int) ($totals->momo_expenses ?? 0),
                'expense_count'   => (int) ($totals->expense_count ?? 0),
                'previous_total'  => $prevTotal,
                'growth_pct'      => $prevTotal > 0
                    ? round((($grandTotal - $prevTotal) / $prevTotal) * 100, 1)
                    : 0,
                'by_category'     => $byCategory->map(fn ($row) => [
                    'category'     => $row->category_name,
                    'total'        => (int) $row->total,
                    'count'        => (int) $row->count,
                    'cash'         => (int) $row->cash,
                    'momo'         => (int) $row->momo,
                    'pct_of_total' => $grandTotal > 0
                        ? round(($row->total / $grandTotal) * 100, 1)
                        : 0,
                ])->toArray(),
            ];
        });
    }

    // ── 2. Expense Trend ──────────────────────────────────────────────────────

    /**
     * Daily expense totals over the period for charting.
     * Returns: array of { date, total_expenses, cash_expenses, momo_expenses }
     */
    public function getExpenseTrend(
        string $dateFrom,
        string $dateTo,
        ?string $locationFilter = 'all'
    ): array {
        $cacheKey = "analytics_finance_expense_trend_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function ()
            use ($dateFrom, $dateTo, $locationFilter) {

            $rows = Expense::whereNull('expenses.deleted_at')
                ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
                ->whereBetween('daily_sessions.session_date', [$dateFrom, $dateTo])
                ->when($this->shopId($locationFilter), fn ($q, $id) =>
                    $q->where('daily_sessions.shop_id', $id))
                ->selectRaw("
                    daily_sessions.session_date::text as date,
                    SUM(expenses.amount) as total_expenses,
                    SUM(CASE WHEN expenses.payment_method::text = 'cash'
                        THEN expenses.amount ELSE 0 END) as cash_expenses,
                    SUM(CASE WHEN expenses.payment_method::text = 'mobile_money'
                        THEN expenses.amount ELSE 0 END) as momo_expenses
                ")
                ->groupBy('daily_sessions.session_date')
                ->orderBy('daily_sessions.session_date')
                ->get();

            return $rows->map(fn ($r) => [
                'date'           => $r->date,
                'total_expenses' => (int) $r->total_expenses,
                'cash_expenses'  => (int) $r->cash_expenses,
                'momo_expenses'  => (int) $r->momo_expenses,
            ])->toArray();
        });
    }

    // ── 3. Withdrawal Summary ─────────────────────────────────────────────────

    /**
     * Owner withdrawal totals for the period.
     * Returns: total_withdrawals, cash_withdrawals, momo_withdrawals,
     *          withdrawal_count, by_shop (array), previous_total, growth_pct
     */
    public function getWithdrawalSummary(
        string $dateFrom,
        string $dateTo,
        ?string $locationFilter = 'all'
    ): array {
        $cacheKey = "analytics_finance_withdrawals_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function ()
            use ($dateFrom, $dateTo, $locationFilter) {

            $base = OwnerWithdrawal::whereNull('owner_withdrawals.deleted_at')
                ->whereBetween('owner_withdrawals.recorded_at', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay(),
                ]);
            $base = $this->applyShopFilter($base, $locationFilter,
                'owner_withdrawals.shop_id');

            $totals = (clone $base)
                ->selectRaw("
                    SUM(amount) as total,
                    SUM(CASE WHEN method::text = 'cash'
                        THEN amount ELSE 0 END) as cash_total,
                    SUM(CASE WHEN method::text = 'mobile_money'
                        THEN amount ELSE 0 END) as momo_total,
                    COUNT(*) as withdrawal_count
                ")
                ->first();

            $byShop = (clone $base)
                ->join('shops', 'owner_withdrawals.shop_id', '=', 'shops.id')
                ->selectRaw("
                    shops.name as shop_name,
                    SUM(owner_withdrawals.amount) as total,
                    COUNT(*) as count
                ")
                ->groupBy('shops.name')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($r) => [
                    'shop'  => $r->shop_name,
                    'total' => (int) $r->total,
                    'count' => (int) $r->count,
                ])->toArray();

            // Previous period
            $from     = Carbon::parse($dateFrom);
            $daySpan  = $from->diffInDays(Carbon::parse($dateTo)) + 1;
            $prevFrom = $from->copy()->subDays($daySpan)->startOfDay();
            $prevTo   = $from->copy()->subDay()->endOfDay();

            $prevBase = OwnerWithdrawal::whereNull('deleted_at')
                ->whereBetween('recorded_at', [$prevFrom, $prevTo]);
            $prevBase  = $this->applyShopFilter($prevBase, $locationFilter, 'shop_id');
            $prevTotal = (int) $prevBase->sum('amount');

            $grandTotal = (int) ($totals->total ?? 0);

            return [
                'total_withdrawals'  => $grandTotal,
                'cash_withdrawals'   => (int) ($totals->cash_total ?? 0),
                'momo_withdrawals'   => (int) ($totals->momo_total ?? 0),
                'withdrawal_count'   => (int) ($totals->withdrawal_count ?? 0),
                'previous_total'     => $prevTotal,
                'growth_pct'         => $prevTotal > 0
                    ? round((($grandTotal - $prevTotal) / $prevTotal) * 100, 1)
                    : 0,
                'by_shop'            => $byShop,
            ];
        });
    }

    // ── 4. Cash Variance Summary ──────────────────────────────────────────────

    /**
     * Cash variance across closed sessions for the period.
     * Returns: total_sessions, sessions_with_shortage, sessions_with_surplus,
     *          total_shortage, total_surplus, net_variance,
     *          worst_shortage_day, by_shop (array)
     */
    public function getCashVarianceSummary(
        string $dateFrom,
        string $dateTo,
        ?string $locationFilter = 'all'
    ): array {
        $cacheKey = "analytics_finance_variance_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function ()
            use ($dateFrom, $dateTo, $locationFilter) {

            $base = DailySession::whereIn('status', ['closed', 'locked'])
                ->whereBetween('session_date', [$dateFrom, $dateTo])
                ->whereNotNull('cash_variance');
            $base = $this->applyShopFilter($base, $locationFilter);

            $agg = (clone $base)
                ->selectRaw("
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN cash_variance < 0 THEN 1 ELSE 0 END) as shortage_count,
                    SUM(CASE WHEN cash_variance > 0 THEN 1 ELSE 0 END) as surplus_count,
                    SUM(CASE WHEN cash_variance < 0 THEN ABS(cash_variance) ELSE 0 END) as total_shortage,
                    SUM(CASE WHEN cash_variance > 0 THEN cash_variance ELSE 0 END) as total_surplus,
                    SUM(cash_variance) as net_variance
                ")
                ->first();

            // Worst shortage day
            $worst = (clone $base)
                ->where('cash_variance', '<', 0)
                ->orderBy('cash_variance')
                ->with('shop')
                ->first();

            // By shop breakdown
            $byShop = (clone $base)
                ->join('shops', 'daily_sessions.shop_id', '=', 'shops.id')
                ->selectRaw("
                    shops.name as shop_name,
                    COUNT(*) as sessions,
                    SUM(CASE WHEN cash_variance < 0 THEN ABS(cash_variance) ELSE 0 END) as shortage,
                    SUM(CASE WHEN cash_variance > 0 THEN cash_variance ELSE 0 END) as surplus
                ")
                ->groupBy('shops.name')
                ->orderByDesc('shortage')
                ->get()
                ->map(fn ($r) => [
                    'shop'     => $r->shop_name,
                    'sessions' => (int) $r->sessions,
                    'shortage' => (int) $r->shortage,
                    'surplus'  => (int) $r->surplus,
                ])->toArray();

            return [
                'total_sessions'         => (int) ($agg->total_sessions ?? 0),
                'sessions_with_shortage' => (int) ($agg->shortage_count ?? 0),
                'sessions_with_surplus'  => (int) ($agg->surplus_count ?? 0),
                'total_shortage'         => (int) ($agg->total_shortage ?? 0),
                'total_surplus'          => (int) ($agg->total_surplus ?? 0),
                'net_variance'           => (int) ($agg->net_variance ?? 0),
                'worst_shortage_day'     => $worst ? [
                    'date'     => $worst->session_date->toDateString(),
                    'shop'     => $worst->shop?->name ?? '—',
                    'variance' => (int) $worst->cash_variance,
                ] : null,
                'by_shop'                => $byShop,
            ];
        });
    }

    // ── 5. Income Statement ───────────────────────────────────────────────────

    /**
     * Full income statement for a period.
     *
     * Lines:
     *   gross_revenue        — total sales (not voided)
     *   total_returns        — approved refunds, non-exchange
     *   net_revenue          — gross − returns
     *   total_cost           — COGS (purchase_price × qty_sold)
     *   gross_profit         — net_revenue − COGS
     *   gross_margin_pct
     *   expenses_by_category — array: category, total, count, pct_of_total
     *   total_expenses       — sum of operating expenses (non-system)
     *   operating_profit     — gross_profit − total_expenses
     *   operating_margin_pct
     *   total_withdrawals    — owner withdrawals
     *   net_result           — operating_profit − withdrawals
     *   net_margin_pct
     *   transaction_count    — number of sales
     *   return_count
     *   prev_*               — same lines for prior period (same length)
     */
    public function getIncomeStatement(
        string $dateFrom,
        string $dateTo,
        ?string $locationFilter = 'all'
    ): array {
        $cacheKey = "analytics_finance_income_statement_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function ()
            use ($dateFrom, $dateTo, $locationFilter) {

            $shopId = $this->shopId($locationFilter);

            // ── Revenue & COGS ────────────────────────────────────────────────
            $itemQ = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay(),
                ])
                ->selectRaw("
                    COUNT(DISTINCT sales.id) as transaction_count,
                    SUM(sale_items.line_total) as gross_revenue,
                    SUM(products.purchase_price * sale_items.quantity_sold) as total_cost
                ");
            if ($shopId) $itemQ->where('sales.shop_id', $shopId);
            $salesData = $itemQ->first();

            // ── Returns ───────────────────────────────────────────────────────
            $returnQ = \App\Models\ReturnModel::where('is_exchange', false)
                ->whereBetween('created_at', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay(),
                ])
                ->selectRaw("SUM(refund_amount) as total_refunds, COUNT(*) as return_count");
            if ($shopId) $returnQ->where('shop_id', $shopId);
            $returnData = $returnQ->first();

            // ── Expenses by category ──────────────────────────────────────────
            $expBase = Expense::whereNull('expenses.deleted_at')
                ->where('expenses.is_system_generated', false)
                ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
                ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
                ->whereBetween('daily_sessions.session_date', [$dateFrom, $dateTo]);
            if ($shopId) $expBase->where('daily_sessions.shop_id', $shopId);

            $expByCategory = (clone $expBase)
                ->selectRaw("
                    expense_categories.name as category_name,
                    SUM(expenses.amount) as total,
                    COUNT(*) as count
                ")
                ->groupBy('expense_categories.name')
                ->orderByDesc('total')
                ->get();

            $totalExpenses = (int) $expByCategory->sum('total');

            // ── Owner withdrawals ─────────────────────────────────────────────
            $wdQ = OwnerWithdrawal::whereNull('owner_withdrawals.deleted_at')
                ->whereBetween('owner_withdrawals.recorded_at', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay(),
                ]);
            if ($shopId) $wdQ->where('owner_withdrawals.shop_id', $shopId);
            $totalWithdrawals = (int) $wdQ->sum('amount');

            // ── Calculations ──────────────────────────────────────────────────
            $grossRevenue       = (int) ($salesData->gross_revenue ?? 0);
            $totalReturns       = (int) ($returnData->total_refunds ?? 0);
            $netRevenue         = $grossRevenue - $totalReturns;
            $totalCost          = (int) ($salesData->total_cost ?? 0);
            $grossProfit        = $netRevenue - $totalCost;
            $operatingProfit    = $grossProfit - $totalExpenses;
            $netResult          = $operatingProfit - $totalWithdrawals;

            $grossMarginPct     = $netRevenue > 0 ? round(($grossProfit / $netRevenue) * 100, 1) : 0;
            $operatingMarginPct = $netRevenue > 0 ? round(($operatingProfit / $netRevenue) * 100, 1) : 0;
            $netMarginPct       = $netRevenue > 0 ? round(($netResult / $netRevenue) * 100, 1) : 0;

            // ── Previous period ───────────────────────────────────────────────
            $from     = Carbon::parse($dateFrom);
            $daySpan  = $from->diffInDays(Carbon::parse($dateTo)) + 1;
            $prevFrom = $from->copy()->subDays($daySpan)->toDateString();
            $prevTo   = $from->copy()->subDay()->toDateString();

            $prevItemQ = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [
                    Carbon::parse($prevFrom)->startOfDay(),
                    Carbon::parse($prevTo)->endOfDay(),
                ])
                ->selectRaw("
                    SUM(sale_items.line_total) as gross_revenue,
                    SUM(products.purchase_price * sale_items.quantity_sold) as total_cost
                ");
            if ($shopId) $prevItemQ->where('sales.shop_id', $shopId);
            $prevSales = $prevItemQ->first();

            $prevReturnQ = \App\Models\ReturnModel::where('is_exchange', false)
                ->whereBetween('created_at', [
                    Carbon::parse($prevFrom)->startOfDay(),
                    Carbon::parse($prevTo)->endOfDay(),
                ])
                ->selectRaw("SUM(refund_amount) as total_refunds");
            if ($shopId) $prevReturnQ->where('shop_id', $shopId);
            $prevReturns = (int) ($prevReturnQ->first()?->total_refunds ?? 0);

            $prevExpQ = Expense::whereNull('expenses.deleted_at')
                ->where('expenses.is_system_generated', false)
                ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
                ->whereBetween('daily_sessions.session_date', [$prevFrom, $prevTo]);
            if ($shopId) $prevExpQ->where('daily_sessions.shop_id', $shopId);
            $prevExpenses = (int) $prevExpQ->sum('expenses.amount');

            $prevWdQ = OwnerWithdrawal::whereNull('owner_withdrawals.deleted_at')
                ->whereBetween('owner_withdrawals.recorded_at', [
                    Carbon::parse($prevFrom)->startOfDay(),
                    Carbon::parse($prevTo)->endOfDay(),
                ]);
            if ($shopId) $prevWdQ->where('owner_withdrawals.shop_id', $shopId);
            $prevWithdrawals = (int) $prevWdQ->sum('amount');

            $prevGross     = (int) ($prevSales->gross_revenue ?? 0);
            $prevCost      = (int) ($prevSales->total_cost ?? 0);
            $prevNet       = $prevGross - $prevReturns;
            $prevGrossProfit    = $prevNet - $prevCost;
            $prevOperating      = $prevGrossProfit - $prevExpenses;
            $prevNetResult      = $prevOperating - $prevWithdrawals;

            return [
                'transaction_count'    => (int) ($salesData->transaction_count ?? 0),
                'return_count'         => (int) ($returnData->return_count ?? 0),
                'gross_revenue'        => $grossRevenue,
                'total_returns'        => $totalReturns,
                'net_revenue'          => $netRevenue,
                'total_cost'           => $totalCost,
                'gross_profit'         => $grossProfit,
                'gross_margin_pct'     => $grossMarginPct,
                'expenses_by_category' => $expByCategory->map(fn ($r) => [
                    'category'     => $r->category_name,
                    'total'        => (int) $r->total,
                    'count'        => (int) $r->count,
                    'pct_of_total' => $totalExpenses > 0
                        ? round(($r->total / $totalExpenses) * 100, 1) : 0,
                ])->toArray(),
                'total_expenses'       => $totalExpenses,
                'operating_profit'     => $operatingProfit,
                'operating_margin_pct' => $operatingMarginPct,
                'total_withdrawals'    => $totalWithdrawals,
                'net_result'           => $netResult,
                'net_margin_pct'       => $netMarginPct,
                // Previous period
                'prev_gross_revenue'   => $prevGross,
                'prev_total_returns'   => $prevReturns,
                'prev_net_revenue'     => $prevNet,
                'prev_total_cost'      => $prevCost,
                'prev_gross_profit'    => $prevGrossProfit,
                'prev_total_expenses'  => $prevExpenses,
                'prev_operating_profit' => $prevOperating,
                'prev_total_withdrawals' => $prevWithdrawals,
                'prev_net_result'      => $prevNetResult,
            ];
        });
    }

    // ── 6. Net Operating Result ───────────────────────────────────────────────

    /**
     * True business net result: Revenue − COGS − Operating Expenses.
     *
     * Returns:
     *   revenue           — total sales revenue (not voided)
     *   total_cost        — COGS (purchase_price × items_sold)
     *   gross_profit      — revenue − COGS
     *   gross_margin_pct  — gross_profit / revenue × 100
     *   total_expenses    — operating expenses from daily sessions
     *   net_result        — gross_profit − total_expenses
     *   net_margin_pct    — net_result / revenue × 100
     *   previous_net      — previous period net_result for growth
     *   growth_pct        — period-over-period net result growth
     */
    public function getNetOperatingResult(
        string $dateFrom,
        string $dateTo,
        ?string $locationFilter = 'all'
    ): array {
        $cacheKey = "analytics_finance_net_result_{$dateFrom}_{$dateTo}_{$locationFilter}";

        return Cache::remember($cacheKey, $this->cacheTtl($dateTo), function ()
            use ($dateFrom, $dateTo, $locationFilter) {

            $shopId = $this->shopId($locationFilter);

            // Revenue and COGS via SaleItems (same as SalesAnalyticsService::getGrossProfitKpis)
            $itemQ = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay(),
                ])
                ->selectRaw("
                    SUM(sale_items.line_total) as revenue,
                    SUM(products.purchase_price * sale_items.quantity_sold) as total_cost,
                    SUM(sale_items.line_total
                        - (products.purchase_price * sale_items.quantity_sold)) as gross_profit
                ");
            if ($shopId) {
                $itemQ->where('sales.shop_id', $shopId);
            }
            $salesData = $itemQ->first();

            // Operating expenses (exclude system-generated shortage entries)
            $expQ = Expense::whereNull('expenses.deleted_at')
                ->where('expenses.is_system_generated', false)
                ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
                ->whereBetween('daily_sessions.session_date', [$dateFrom, $dateTo]);
            if ($shopId) {
                $expQ->where('daily_sessions.shop_id', $shopId);
            }
            $totalExpenses = (int) $expQ->sum('expenses.amount');

            $revenue      = (int) ($salesData->revenue ?? 0);
            $totalCost    = (int) ($salesData->total_cost ?? 0);
            $grossProfit  = (int) ($salesData->gross_profit ?? 0);
            $netResult    = $grossProfit - $totalExpenses;
            $grossMargin  = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0;
            $netMargin    = $revenue > 0 ? round(($netResult / $revenue) * 100, 1) : 0;

            // Previous period
            $from     = Carbon::parse($dateFrom);
            $daySpan  = $from->diffInDays(Carbon::parse($dateTo)) + 1;
            $prevFrom = $from->copy()->subDays($daySpan)->startOfDay()->toDateString();
            $prevTo   = $from->copy()->subDay()->endOfDay()->toDateString();

            $prevItemQ = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereNull('sales.voided_at')
                ->whereBetween('sales.sale_date', [
                    Carbon::parse($prevFrom)->startOfDay(),
                    Carbon::parse($prevTo)->endOfDay(),
                ])
                ->selectRaw("
                    SUM(sale_items.line_total
                        - (products.purchase_price * sale_items.quantity_sold)) as gross_profit
                ");
            if ($shopId) {
                $prevItemQ->where('sales.shop_id', $shopId);
            }
            $prevGross = (int) ($prevItemQ->first()?->gross_profit ?? 0);

            $prevExpQ = Expense::whereNull('expenses.deleted_at')
                ->where('expenses.is_system_generated', false)
                ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
                ->whereBetween('daily_sessions.session_date', [$prevFrom, $prevTo]);
            if ($shopId) {
                $prevExpQ->where('daily_sessions.shop_id', $shopId);
            }
            $prevExpenses = (int) $prevExpQ->sum('expenses.amount');
            $prevNet      = $prevGross - $prevExpenses;

            return [
                'revenue'          => $revenue,
                'total_cost'       => $totalCost,
                'gross_profit'     => $grossProfit,
                'gross_margin_pct' => $grossMargin,
                'total_expenses'   => $totalExpenses,
                'net_result'       => $netResult,
                'net_margin_pct'   => $netMargin,
                'previous_net'     => $prevNet,
                'growth_pct'       => $prevNet > 0
                    ? round((($netResult - $prevNet) / $prevNet) * 100, 1)
                    : 0,
                'is_profitable'    => $netResult >= 0,
            ];
        });
    }
}
