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
    public string $period         = 'today';
    public string $customFrom     = '';
    public string $customTo       = '';
    public bool   $showCustomPicker = false;
    public string $periodLabel    = '';

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

        $this->updatePeriodLabel();
    }

    public function setPeriod(string $period): void
    {
        if ($period === 'custom') {
            $this->showCustomPicker = true;
            if (! $this->customFrom) $this->customFrom = now()->subDays(6)->format('Y-m-d');
            if (! $this->customTo)   $this->customTo   = now()->format('Y-m-d');
            return;
        }

        $this->period = $period;
        $this->showCustomPicker = false;
        $this->updatePeriodLabel();
    }

    public function applyCustomRange(): void
    {
        if ($this->customFrom && $this->customTo) {
            $this->period = 'custom';
            $this->showCustomPicker = false;
            $this->updatePeriodLabel();
        }
    }

    public function cancelCustomPicker(): void
    {
        $this->showCustomPicker = false;
    }

    protected function getDateRange(): array
    {
        return match ($this->period) {
            'today'      => [today()->startOfDay(), today()->endOfDay()],
            'yesterday'  => [today()->subDay()->startOfDay(), today()->subDay()->endOfDay()],
            'this_week'  => [now()->startOfWeek(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfDay()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last_30'    => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'custom'     => [
                Carbon::parse($this->customFrom)->startOfDay(),
                Carbon::parse($this->customTo)->endOfDay(),
            ],
            default => [now()->startOfWeek(), now()->endOfDay()],
        };
    }

    protected function getPrevDateRange(): array
    {
        [$from, $to] = $this->getDateRange();
        $days = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
        return [$from->copy()->subDays($days), $to->copy()->subDays($days)];
    }

    protected function updatePeriodLabel(): void
    {
        [$from, $to] = $this->getDateRange();
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

        // ── Low stock ─────────────────────────────────────────────────
        $lowStockProducts = Product::active()
            ->with(['boxes' => fn($q) => $q->where('location_type', 'shop')
                ->where('location_id', $shopId)->whereIn('status', ['full', 'partial'])])
            ->get()
            ->filter(fn($p) => $p->isLowStock('shop', $shopId))
            ->map(function ($p) use ($shopId) {
                $p->current_stock = $p->getCurrentStock('shop', $shopId)['total_items'];
                return $p;
            })
            ->sortBy('current_stock')
            ->take(5);

        // ── Recent transactions ───────────────────────────────────────
        $recentSales = Sale::notVoided()->where('shop_id', $shopId)
            ->whereBetween('sale_date', [$from, $to])
            ->with(['soldBy', 'items'])->orderBy('sale_date', 'desc')->limit(4)->get();
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
        $diffDays = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay());
        $isSingleDay = $diffDays === 0;
        $sparklineSales = $sparklineTxns = $sparklineReturns = [];
        $trendLabels = $trendCurrent = $trendPrev = [];

        if ($isSingleDay) {
            // Sparklines: 7 buckets covering the full 24h (tiny canvas, buckets are fine)
            $hourSlots = [[0,3],[4,7],[8,10],[11,13],[14,16],[17,19],[20,23]];
            foreach ($hourSlots as [$startH, $endH]) {
                $slotS = $from->copy()->setHour($startH)->setMinute(0)->setSecond(0);
                $slotE = $from->copy()->setHour($endH)->setMinute(59)->setSecond(59);
                $sparklineSales[]   = (float) Sale::notVoided()->where('shop_id',$shopId)->whereBetween('sale_date',[$slotS,$slotE])->sum('total');
                $sparklineTxns[]    = (int)   Sale::notVoided()->where('shop_id',$shopId)->whereBetween('sale_date',[$slotS,$slotE])->count();
                $sparklineReturns[] = (float) ReturnModel::where('shop_id',$shopId)->whereBetween('created_at',[$slotS,$slotE])->sum('refund_amount');
            }

            // Trend chart: one data point per individual sale at its exact time
            $daySales = Sale::notVoided()
                ->where('shop_id', $shopId)
                ->whereBetween('sale_date', [$from, $to])
                ->orderBy('sale_date')
                ->get(['sale_date', 'total']);

            foreach ($daySales as $sale) {
                $trendLabels[]  = $sale->sale_date->format('g:i A');
                $trendCurrent[] = (float) $sale->total;
            }

            if (empty($trendLabels)) {
                $trendLabels  = [$from->format('M j')];
                $trendCurrent = [0];
            }
            $trendPrev = []; // No previous-period overlay for per-sale bar chart
        } elseif ($diffDays < 7) {
            // Short period: one point per day, no duplicates
            for ($i = 0; $i <= $diffDays; $i++) {
                $day  = $from->copy()->addDays($i);
                $pDay = $prevFrom->copy()->addDays($i);
                $d    = $day->format('Y-m-d');
                $sparklineSales[]   = (float) Sale::notVoided()->where('shop_id',$shopId)->whereDate('sale_date',$d)->sum('total');
                $sparklineTxns[]    = (int)   Sale::notVoided()->where('shop_id',$shopId)->whereDate('sale_date',$d)->count();
                $sparklineReturns[] = (float) ReturnModel::where('shop_id',$shopId)->whereDate('created_at',$d)->sum('refund_amount');
                $trendLabels[]  = $day->format('M j');
                $trendCurrent[] = (float) Sale::notVoided()->where('shop_id',$shopId)->whereDate('sale_date',$d)->sum('total');
                $trendPrev[]    = (float) Sale::notVoided()->where('shop_id',$shopId)->whereDate('sale_date',$pDay->format('Y-m-d'))->sum('total');
            }
        } else {
            // Long period: 7 evenly-spaced points
            $step = max(1, (int) round($diffDays / 6));
            for ($i = 0; $i <= 6; $i++) {
                $day  = $from->copy()->addDays($i * $step);
                $pDay = $prevFrom->copy()->addDays($i * $step);
                if ($day->gt($to)) break;
                $d = $day->format('Y-m-d');
                $sparklineSales[]   = (float) Sale::notVoided()->where('shop_id',$shopId)->whereDate('sale_date',$d)->sum('total');
                $sparklineTxns[]    = (int)   Sale::notVoided()->where('shop_id',$shopId)->whereDate('sale_date',$d)->count();
                $sparklineReturns[] = (float) ReturnModel::where('shop_id',$shopId)->whereDate('created_at',$d)->sum('refund_amount');
                $trendLabels[]  = $day->format('M j');
                $trendCurrent[] = (float) Sale::notVoided()->where('shop_id',$shopId)->whereDate('sale_date',$d)->sum('total');
                $trendPrev[]    = (float) Sale::notVoided()->where('shop_id',$shopId)->whereDate('sale_date',$pDay->format('Y-m-d'))->sum('total');
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
        $cfWithdrawals = (float) DB::table('owner_withdrawals')
            ->where('shop_id', $shopId)
            ->whereBetween('recorded_at', [$from, $to])
            ->whereNull('deleted_at')->sum('amount');
        $cfCredit = (float) Sale::where('shop_id', $shopId)
            ->where('has_credit', true)->whereNull('voided_at')
            ->whereBetween('sale_date', [$from, $to])->sum('credit_amount');

        $cfExpenses = (float) DB::table('expenses')
            ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id')
            ->where('daily_sessions.shop_id', $shopId)
            ->whereBetween('expenses.created_at', [$from, $to])
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
            'lowStockProducts', 'recentSales', 'recentReturns',
            'topProducts',
            'sparklineSales', 'sparklineTxns', 'sparklineReturns',
            'isSingleDay', 'trendLabels', 'trendCurrent', 'trendPrev',
            'allowCard', 'allowBankTransfer',
            'cfCash', 'cfMomo', 'cfBank', 'cfCard', 'cfTotal',
            'cfReturns', 'cfWithdrawals', 'cfCredit', 'cfExpenses', 'cfNet',
            'expensesChange', 'lastSync'
        ));
    }
}
