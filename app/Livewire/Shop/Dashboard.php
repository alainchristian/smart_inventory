<?php

namespace App\Livewire\Shop;

use App\Models\Box;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\Shop;
use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public int    $shopId;
    public string $preset       = 'today';
    public string $dateFrom     = '';
    public string $dateTo       = '';
    public string $periodLabel  = '';
    public int    $chartVersion = 0;

    public function mount(?int $shopId = null): void
    {
        $user = auth()->user();

        if ($user->isShopManager()) {
            $this->shopId = $user->location_id;
        } elseif ($user->isOwner()) {
            $this->shopId = $shopId
                ?? request()->get('shop_id')
                ?? session('selected_shop_id')
                ?? Shop::first()?->id;

            if ($this->shopId) {
                session(['selected_shop_id' => $this->shopId]);
            }
        }

        $this->resolveDates();
        $this->updatePeriodLabel();
    }

    public function setPreset(string $preset): void
    {
        $this->preset = $preset;
        $this->resolveDates();
        $this->updatePeriodLabel();
        $this->chartVersion++;
    }

    public function updatedDateFrom(): void
    {
        $this->preset = 'custom';
        $this->updatePeriodLabel();
        $this->chartVersion++;
    }

    public function updatedDateTo(): void
    {
        $this->preset = 'custom';
        $this->updatePeriodLabel();
        $this->chartVersion++;
    }

    private function resolveDates(): void
    {
        $today = business_today();

        match ($this->preset) {
            'today'      => [$this->dateFrom, $this->dateTo] = [$today->toDateString(), $today->toDateString()],
            'yesterday'  => [$this->dateFrom, $this->dateTo] = [$today->copy()->subDay()->toDateString(), $today->copy()->subDay()->toDateString()],
            'week'       => [$this->dateFrom, $this->dateTo] = [$today->copy()->startOfWeek()->toDateString(), $today->toDateString()],
            'month'      => [$this->dateFrom, $this->dateTo] = [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
            'last_month' => [$this->dateFrom, $this->dateTo] = [
                $today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ],
            default      => [$this->dateFrom, $this->dateTo] = [$today->copy()->subDays(29)->toDateString(), $today->toDateString()],
        };
    }

    // UTC query boundaries for the (business-timezone) dateFrom/dateTo selection —
    // for querying UTC-stored datetime columns (sale_date, created_at). Do not use
    // the returned Carbons for display formatting (see updatePeriodLabel).
    protected function getDateRange(): array
    {
        return [
            Carbon::parse($this->dateFrom, config('tenant.timezone'))->startOfDay()->utc(),
            Carbon::parse($this->dateTo, config('tenant.timezone'))->endOfDay()->utc(),
        ];
    }

    protected function getPrevDateRange(): array
    {
        // Same number of LOCAL days just before. Diffing the UTC boundaries counted
        // one Kigali day (22:00 → 21:59 UTC) as two, so "Today" compared itself
        // with the day before yesterday.
        [$from, $to] = $this->getDateRange();
        $days = (int) Carbon::parse($this->dateFrom)->diffInDays(Carbon::parse($this->dateTo)) + 1;
        return [$from->copy()->subDays($days), $to->copy()->subDays($days)];
    }

    protected function updatePeriodLabel(): void
    {
        // Plain business-timezone calendar dates — not the UTC query boundaries
        // from getDateRange(), which would display the wrong calendar day.
        $from = Carbon::parse($this->dateFrom);
        $to   = Carbon::parse($this->dateTo);
        $this->periodLabel = $from->isSameDay($to)
            ? $from->format('M j, Y')
            : $from->format('M j') . ' – ' . $to->format('M j, Y');
    }

    public function render()
    {
        $shopId = $this->shopId;
        [$from, $to]         = $this->getDateRange();
        [$prevFrom, $prevTo] = $this->getPrevDateRange();

        $settings            = app(SettingsService::class);
        $allowCard           = $settings->allowCardPayment();
        $allowBankTransfer   = $settings->allowBankTransferPayment();

        $shop = Shop::with('defaultWarehouse')->find($shopId);

        // ── Period totals ─────────────────────────────────────────────
        $totalSales = (float) Sale::notVoided()->where('shop_id', $shopId)
            ->whereBetween('sale_date', [$from, $to])->sum('total');
        $txnCount = (int) Sale::notVoided()->where('shop_id', $shopId)
            ->whereBetween('sale_date', [$from, $to])->count();
        $itemsSold = Sale::notVoided()->where('shop_id', $shopId)
            ->whereBetween('sale_date', [$from, $to])
            ->with('items')->get()->sum(fn($s) => $s->items->sum('quantity_sold'));

        // ── Previous period ───────────────────────────────────────────
        $prevTotal   = (float) Sale::notVoided()->where('shop_id', $shopId)
            ->whereBetween('sale_date', [$prevFrom, $prevTo])->sum('total');
        $prevTxn     = (int) Sale::notVoided()->where('shop_id', $shopId)
            ->whereBetween('sale_date', [$prevFrom, $prevTo])->count();
        $prevReturns = (float) ReturnModel::where('shop_id', $shopId)
            ->whereBetween('created_at', [$prevFrom, $prevTo])->sum('refund_amount');

        $salesChange = $prevTotal > 0
            ? round((($totalSales - $prevTotal) / $prevTotal) * 100, 1)
            : ($totalSales > 0 ? 100 : 0);
        $txnChange = $prevTxn > 0
            ? round((($txnCount - $prevTxn) / $prevTxn) * 100, 1)
            : ($txnCount > 0 ? 100 : 0);

        // ── Returns ───────────────────────────────────────────────────
        $periodReturns  = (float) ReturnModel::where('shop_id', $shopId)
            ->whereBetween('created_at', [$from, $to])->sum('refund_amount');
        $returnsChange  = $prevReturns > 0
            ? round((($periodReturns - $prevReturns) / $prevReturns) * 100, 1) : 0;

        // ── Stock (always current — only boxes that still have items) ────
        $stockItems = (int) Box::where('location_type', 'shop')->where('location_id', $shopId)->sum('items_remaining');
        $stockBoxes = (int) Box::where('location_type', 'shop')->where('location_id', $shopId)->where('items_remaining', '>', 0)->count();

        // ── Low stock (box-count policy from settings) ────────────────
        $shopLowStockThreshold = $settings->lowStockBoxesShop();
        $lowStockProducts = DB::table('products as p')
            ->join('boxes as b', function ($join) use ($shopId) {
                $join->on('b.product_id', '=', 'p.id')
                     ->where('b.location_type', 'shop')
                     ->where('b.location_id', $shopId)
                     ->whereRaw("b.status::text != 'empty'")
                     ->where('b.items_remaining', '>', 0);
            })
            ->where('p.is_active', true)
            ->whereNull('p.deleted_at')
            ->select(
                'p.id',
                'p.name',
                DB::raw('COUNT(b.id) as current_stock')
            )
            ->groupBy('p.id', 'p.name')
            ->havingRaw('COUNT(b.id) > 0')
            ->havingRaw('COUNT(b.id) <= ?', [$shopLowStockThreshold])
            ->orderBy('current_stock')
            ->limit(5)
            ->get();

        // ── Recent transactions ───────────────────────────────────────
        $recentSales = Sale::notVoided()->where('shop_id', $shopId)
            ->whereBetween('sale_date', [$from, $to])
            ->with(['soldBy', 'items.product'])->orderBy('sale_date', 'desc')->limit(15)->get();
        $recentReturns = ReturnModel::where('shop_id', $shopId)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')->limit(2)->get();

        // ── Top products ──────────────────────────────────────────────
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereNull('sales.voided_at')->whereNull('sales.deleted_at')
            ->where('sales.shop_id', $shopId)
            ->whereBetween('sales.sale_date', [$from, $to])
            ->select('products.id', 'products.name',
                DB::raw('SUM(sale_items.line_total) as revenue'),
                DB::raw('SUM(sale_items.quantity_sold) as units_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')->limit(5)->get();

        // ── Sparklines + Trend ────────────────────────────────────────
        // Built on LOCAL (business-timezone) days and hours — $from/$to are
        // UTC query boundaries, and a Kigali day runs 22:00–21:59 UTC.
        $tz          = config('tenant.timezone');
        $localDayOf  = fn (string $col) => "DATE({$col} AT TIME ZONE 'UTC' AT TIME ZONE '{$tz}')";
        $localHourOf = fn (string $col) => "EXTRACT(HOUR FROM {$col} AT TIME ZONE 'UTC' AT TIME ZONE '{$tz}')";
        $localFrom   = Carbon::parse($this->dateFrom);
        $diffDays    = (int) $localFrom->diffInDays(Carbon::parse($this->dateTo));
        $isSingleDay = $diffDays === 0;
        $prevLocalFrom = $localFrom->copy()->subDays($diffDays + 1);
        $sparklineSales = $sparklineTxns = $sparklineReturns = $sparklineStock = [];
        $trendLabels = $trendCurrent = $trendPrev = [];

        if ($isSingleDay) {
            // One day: the Sales Trend chart and the Total Sales sparkline show each
            // SALE as its own point, at its local time (owner's choice) — not a
            // running total. No previous-day line: sale times don't pair up.
            $daySales = Sale::notVoided()
                ->where('shop_id', $shopId)
                ->whereBetween('sale_date', [$from, $to])
                ->orderBy('sale_date')
                ->get(['sale_date', 'total']);
            foreach ($daySales as $sale) {
                $trendLabels[]  = local_time($sale->sale_date)->format('g:i A');
                $trendCurrent[] = (float) $sale->total;
            }
            if (! $trendLabels) {
                $trendLabels  = [$localFrom->format('M j')];
                $trendCurrent = [0];
            }
            $sparklineSales = $trendCurrent;

            // The other cards count events, so their sparklines are per HOUR (what
            // happened in that hour, not a running total), from 06:00 (earlier if
            // something happened earlier) to now for today, or 23:00 for a past day.
            $hourly = fn ($rows) => $rows->mapWithKeys(fn ($r) => [(int) $r->h => $r])->all();
            $txnH = $hourly(DB::table('sales')->whereNull('voided_at')->whereNull('deleted_at')
                ->where('shop_id', $shopId)->whereBetween('sale_date', [$from, $to])
                ->selectRaw($localHourOf('sale_date') . ' as h, COUNT(*) as txns')
                ->groupByRaw('1')->get());
            $retH = $hourly(DB::table('returns')->where('shop_id', $shopId)
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw($localHourOf('created_at') . ' as h, SUM(refund_amount) as total')
                ->groupByRaw('1')->get());
            // Boxes received into this shop (Box::moveTo() logs a 'transfer' movement to this location)
            $stkH = $hourly(DB::table('box_movements')->where('movement_type', 'transfer')
                ->where('to_location_type', 'shop')->where('to_location_id', $shopId)
                ->whereBetween('moved_at', [$from, $to])
                ->selectRaw($localHourOf('moved_at') . ' as h, COUNT(*) as boxes')
                ->groupByRaw('1')->get());

            $busy      = array_keys($txnH + $retH + $stkH);
            $firstHour = min(6, $busy ? min($busy) : 6);
            $isToday   = $localFrom->isSameDay(business_today());
            $lastHour  = max($isToday ? business_now()->hour : 23, $firstHour);
            for ($h = $firstHour; $h <= $lastHour; $h++) {
                $sparklineTxns[]    = (int)   ($txnH[$h]->txns  ?? 0);
                $sparklineReturns[] = (float) ($retH[$h]->total ?? 0);
                $sparklineStock[]   = (int)   ($stkH[$h]->boxes ?? 0);
            }
        } else {
            // Multi-day: aggregate by LOCAL calendar day in bulk queries (no N+1 sampling)
            $salesByDay = DB::table('sales')
                ->whereNull('voided_at')->whereNull('deleted_at')
                ->where('shop_id', $shopId)
                ->whereBetween('sale_date', [$from, $to])
                ->selectRaw($localDayOf('sale_date') . ' as d, SUM(total) as revenue, COUNT(*) as txns')
                ->groupByRaw('1')
                ->get()->keyBy('d');

            $prevSalesByDay = DB::table('sales')
                ->whereNull('voided_at')->whereNull('deleted_at')
                ->where('shop_id', $shopId)
                ->whereBetween('sale_date', [$prevFrom, $prevTo])
                ->selectRaw($localDayOf('sale_date') . ' as d, SUM(total) as revenue')
                ->groupByRaw('1')
                ->get()->keyBy('d');

            $returnsByDay = DB::table('returns')
                ->where('shop_id', $shopId)
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw($localDayOf('created_at') . ' as d, SUM(refund_amount) as total')
                ->groupByRaw('1')
                ->get()->keyBy('d');

            // Boxes received into this shop, per day (Box::moveTo() logs a 'transfer'
            // movement to this location — the accurate "stock received" event).
            $stockByDay = DB::table('box_movements')
                ->where('movement_type', 'transfer')
                ->where('to_location_type', 'shop')->where('to_location_id', $shopId)
                ->whereBetween('moved_at', [$from, $to])
                ->selectRaw($localDayOf('moved_at') . ' as d, COUNT(*) as boxes')
                ->groupByRaw('1')
                ->get()->keyBy('d');

            // Sparklines: 7 equal-width buckets of local days — sum all days in each
            // bucket so sparse data (few transactions over a month) still shows variation
            $totalDays  = $diffDays + 1;
            $bucketSize = max(1, (int) ceil($totalDays / 7));
            for ($i = 0; $i < 7; $i++) {
                $first = $i * $bucketSize;
                if ($first > $diffDays) break;
                $last = min($diffDays, ($i + 1) * $bucketSize - 1);
                $rev = 0.0; $txns = 0; $rets = 0.0; $stk = 0;
                for ($n = $first; $n <= $last; $n++) {
                    $dk    = $localFrom->copy()->addDays($n)->format('Y-m-d');
                    $rev  += (float) ($salesByDay[$dk]->revenue   ?? 0);
                    $txns += (int)   ($salesByDay[$dk]->txns      ?? 0);
                    $rets += (float) ($returnsByDay[$dk]->total    ?? 0);
                    $stk  += (int)   ($stockByDay[$dk]->boxes      ?? 0);
                }
                $sparklineSales[]   = $rev;
                $sparklineTxns[]    = $txns;
                $sparklineReturns[] = $rets;
                $sparklineStock[]   = $stk;
            }

            // Trend chart: every day when ≤ 60 days, weekly buckets for longer periods
            if ($diffDays <= 60) {
                for ($i = 0; $i <= $diffDays; $i++) {
                    $day = $localFrom->copy()->addDays($i);
                    $d   = $day->format('Y-m-d');
                    $pd  = $prevLocalFrom->copy()->addDays($i)->format('Y-m-d');
                    $trendLabels[]  = $day->format('M j');
                    $trendCurrent[] = (float) ($salesByDay[$d]->revenue    ?? 0);
                    $trendPrev[]    = (float) ($prevSalesByDay[$pd]->revenue ?? 0);
                }
            } else {
                // Weekly buckets for > 60 day periods
                for ($i = 0; $i <= $diffDays; $i += 7) {
                    $weekRev = $pWeekRev = 0.0;
                    for ($j = 0; $j < 7 && ($i + $j) <= $diffDays; $j++) {
                        $d  = $localFrom->copy()->addDays($i + $j)->format('Y-m-d');
                        $pd = $prevLocalFrom->copy()->addDays($i + $j)->format('Y-m-d');
                        $weekRev  += (float) ($salesByDay[$d]->revenue    ?? 0);
                        $pWeekRev += (float) ($prevSalesByDay[$pd]->revenue ?? 0);
                    }
                    $trendLabels[]  = $localFrom->copy()->addDays($i)->format('M j');
                    $trendCurrent[] = $weekRev;
                    $trendPrev[]    = $pWeekRev;
                }
            }
        }

        // ── Cash flow via sale_payments ───────────────────────────────
        $cfByMethod = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')->whereNull('sales.deleted_at')
            ->where('sales.shop_id', $shopId)
            ->whereBetween('sales.sale_date', [$from, $to])
            ->select(DB::raw("sale_payments.payment_method::text as pm"), DB::raw('SUM(sale_payments.amount) as amount'))
            ->groupBy(DB::raw("sale_payments.payment_method::text"))
            ->pluck('amount', 'pm')->toArray();

        $cfCash  = (float)($cfByMethod['cash'] ?? 0);
        $cfMomo  = (float)($cfByMethod['mobile_money'] ?? 0);
        $cfBank  = (float)($cfByMethod['bank_transfer'] ?? 0);
        $cfCard  = (float)($cfByMethod['card'] ?? 0);
        $cfTotal = $cfCash + $cfMomo + $cfBank + $cfCard;

        $cfReturns     = $periodReturns;
        // Expenses and withdrawals belong to the business day of their register
        // session (same rule as the Daily Report), not the moment they were typed
        // in — so one recorded this morning while closing yesterday's register
        // counts for yesterday.
        $cfWithdrawals = (float) DB::table('owner_withdrawals')
            ->join('daily_sessions', 'owner_withdrawals.daily_session_id', '=', 'daily_sessions.id')
            ->where('daily_sessions.shop_id', $shopId)
            ->whereBetween('daily_sessions.session_date', [$this->dateFrom, $this->dateTo])
            ->whereNull('owner_withdrawals.deleted_at')->sum('owner_withdrawals.amount');
        $cfCredit = (float) Sale::where('shop_id', $shopId)
            ->where('has_credit', true)->whereNull('voided_at')
            ->whereBetween('sale_date', [$from, $to])->sum('credit_amount');

        $cfExpenses = (float) DB::table('expenses')
            ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
            ->where('daily_sessions.shop_id', $shopId)
            ->whereBetween('daily_sessions.session_date', [$this->dateFrom, $this->dateTo])
            ->whereNull('expenses.deleted_at')
            ->sum('expenses.amount');

        // Net in hand = collected - refunds - withdrawals - expenses
        // Credit is already excluded from cfTotal (never collected), shown separately
        $cfNet = $cfTotal - $cfReturns - $cfWithdrawals - $cfExpenses;

        $expensesChange = $returnsChange;
        $lastSync = now();

        return view('livewire.shop.dashboard', compact(
            'shop', 'from', 'to',
            'totalSales', 'txnCount', 'itemsSold',
            'salesChange', 'txnChange',
            'periodReturns', 'returnsChange',
            'stockItems', 'stockBoxes',
            'lowStockProducts', 'shopLowStockThreshold', 'recentSales', 'recentReturns',
            'topProducts',
            'sparklineSales', 'sparklineTxns', 'sparklineReturns', 'sparklineStock',
            'isSingleDay', 'trendLabels', 'trendCurrent', 'trendPrev',
            'allowCard', 'allowBankTransfer',
            'cfCash', 'cfMomo', 'cfBank', 'cfCard', 'cfTotal',
            'cfReturns', 'cfWithdrawals', 'cfCredit', 'cfExpenses', 'cfNet',
            'expensesChange', 'lastSync'
        ));
    }
}
