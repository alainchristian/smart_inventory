<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\ResolvesBusinessPeriod;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Box;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Warehouse;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;

class BusinessKpiRow extends Component
{
    use ResolvesBusinessPeriod;

    public string  $period = 'today';
    public ?string $from   = null;
    public ?string $to     = null;
    public array   $sales          = [];
    public array   $profit         = [];
    public array   $inventory      = [];
    public array   $locations      = [];
    public array   $salesSparkline    = [];
    public array   $profitSparkline   = [];
    public array   $expenseSparkline  = [];
    public array   $creditSparkline   = [];
    public array   $expenses          = [];
    public array   $credit            = [];

    public function mount(): void
    {
        $this->loadData();
    }

    // Livewire 3: each named dispatch argument maps to a typed parameter.
    // Do NOT use (array $payload) — that receives the entire array as one blob.
    #[On('time-filter-changed')]
    public function refresh(string $period, ?string $from = null, ?string $to = null): void
    {
        $this->period = $period;
        $this->from   = $from;
        $this->to     = $to;
        $this->loadData();
    }

    private function loadData(): void
    {
        [$start, $end]         = $this->periodRange();
        [$prevStart, $prevEnd] = $this->previousRange();

        // Always-visible sub-row reference points (not period-dependent)
        // Business-timezone day/week/month starts, as UTC bounds (sale_date is UTC)
        $bn         = business_now();
        $nowUtc     = now();
        $dayStart   = $bn->copy()->startOfDay()->utc();
        $weekStart  = $bn->copy()->startOfWeek()->utc();
        $monthStart = $bn->copy()->startOfMonth()->utc();

        // One query per metric: each window is a SUM(CASE WHEN … BETWEEN …),
        // same bounds as a separate whereBetween()->sum() would use
        $windows = [
            'current'  => [$start, $end],
            'previous' => [$prevStart, $prevEnd],
            'today'    => [$dayStart, $nowUtc],
            'week'     => [$weekStart, $nowUtc],
            'month'    => [$monthStart, $nowUtc],
        ];

        $rev = $this->windowSums(Sale::notVoided(), 'sale_date', 'total', $windows, countKey: 'current');
        $current  = $rev['current'];
        $previous = $rev['previous'];

        $this->sales = [
            'today'   => $rev['today'],
            'week'    => $rev['week'],
            'month'   => $rev['month'],
            'current' => $current,
            'growth'  => $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 0.0,
            'count'   => $rev['count'],
        ];

        // Profit margin for the selected period + sub-row reference points
        $marginWindows = $windows;
        unset($marginWindows['previous']);
        $margins = $this->windowSums(
            $this->marginQuery(),
            'sales.sale_date',
            'sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)',
            $marginWindows
        );
        $margin      = $margins['current'];
        $todayMargin = $margins['today'];
        $weekMargin  = $margins['week'];
        $monthMargin = $margins['month'];

        $this->profit = [
            'today'        => $todayMargin,
            'week'         => $weekMargin,
            'month'        => $monthMargin,
            'margin_rwf'   => $margin,
            'margin_pct'   => $current > 0 ? round(($margin / $current) * 100, 1) : 0,
            'margin_label' => 'Realised margin',
        ];

        // Inventory: Box::available() = status IN (full, partial) AND items_remaining > 0
        // This is the single source of truth used across all dashboard sections.
        // Every product exists (restrictOnDelete FK), so the inner join to
        // products never drops a box from the item/box counts below
        $inv = Box::available()
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw("
                SUM(boxes.items_remaining * products.purchase_price) AS cost_value,
                SUM(boxes.items_remaining * products.selling_price)  AS retail_value,
                SUM(CASE WHEN boxes.location_type = 'warehouse' THEN boxes.items_remaining * products.selling_price END) AS wh_retail,
                SUM(CASE WHEN boxes.location_type = 'shop'      THEN boxes.items_remaining * products.selling_price END) AS shop_retail,
                SUM(CASE WHEN boxes.location_type = 'warehouse' THEN boxes.items_remaining END) AS wh_items,
                SUM(CASE WHEN boxes.location_type = 'shop'      THEN boxes.items_remaining END) AS shop_items,
                COUNT(*) FILTER (WHERE boxes.location_type = 'warehouse') AS wh_boxes,
                COUNT(*) FILTER (WHERE boxes.location_type = 'shop')      AS shop_boxes,
                SUM(boxes.items_remaining) AS remaining,
                SUM(boxes.items_total)     AS capacity
            ")
            ->first();

        $cost   = ($inv->cost_value   ?? 0);
        $retail = ($inv->retail_value ?? 0);

        $whRetail   = (int) ($inv->wh_retail ?? 0);
        $shopRetail = (int) ($inv->shop_retail ?? 0);
        $whItems    = $inv->wh_items ?? 0;
        $shopItems  = $inv->shop_items ?? 0;
        $whBoxes    = (int) ($inv->wh_boxes ?? 0);
        $shopBoxes  = (int) ($inv->shop_boxes ?? 0);
        $totalBoxes = $whBoxes + $shopBoxes;

        $fillRate = ($inv && $inv->capacity > 0)
            ? round(($inv->remaining / $inv->capacity) * 100, 1)
            : 0;

        $this->inventory = [
            'cost'         => $cost,
            'retail'       => $retail,
            'markup_pct'   => $cost > 0 ? round((($retail - $cost) / $cost) * 100, 1) : 0,
            'markup_label' => 'Potential markup',
            'warehouse'    => $whRetail,
            'shop'         => $shopRetail,
            'wh_items'     => $whItems,
            'shop_items'   => $shopItems,
            'wh_boxes'     => $whBoxes,
            'shop_boxes'   => $shopBoxes,
            'total_boxes'  => $totalBoxes,
            'fill_rate'    => $fillRate,
        ];

        $wh   = Warehouse::selectRaw('COUNT(*) AS total, COUNT(*) FILTER (WHERE is_active) AS active')->first();
        $shop = Shop::selectRaw('COUNT(*) AS total, COUNT(*) FILTER (WHERE is_active) AS active')->first();

        $this->locations = [
            'warehouses'        => (int) $wh->total,
            'shops'             => (int) $shop->total,
            'users'             => User::count(),
            'active_warehouses' => (int) $wh->active,
            'active_shops'      => (int) $shop->active,
        ];

        // Expenses for selected period (daily_sessions.session_date is DATE)
        // session_date is a local DATE — compare with local dates, never ->toDateString() on a UTC bound
        $startDate = $this->localDate($start);
        $endDate   = $this->localDate($end);
        $prevStart_date = $this->localDate($prevStart);
        $prevEnd_date   = $this->localDate($prevEnd);

        $exp = $this->windowSums($this->expenseQuery(), 'daily_sessions.session_date', 'expenses.amount', [
            'current'  => [$startDate, $endDate],
            'previous' => [$prevStart_date, $prevEnd_date],
        ]);
        $expCurrent  = (int) $exp['current'];
        $expPrevious = (int) $exp['previous'];

        $this->expenses = [
            'current'   => $expCurrent,
            'growth'    => $expPrevious > 0 ? round((($expCurrent - $expPrevious) / $expPrevious) * 100, 1) : 0.0,
            'net_op'    => (int)$margin - $expCurrent, // Gross Profit − Expenses = true net operating profit
        ];

        // Credit Outstanding — always current balance (not period-dependent)
        $cr = Customer::selectRaw('COALESCE(SUM(outstanding_balance), 0) AS outstanding, COUNT(*) FILTER (WHERE outstanding_balance > 0) AS owing')->first();
        $this->credit = [
            'outstanding' => (int) $cr->outstanding,
            'count'       => (int) $cr->owing,
        ];

        $this->salesSparkline   = $this->generateSalesSparkline($start, $end);
        $this->profitSparkline  = $this->generateProfitSparkline($start, $end);
        $this->expenseSparkline = $this->generateExpenseSparkline($start, $end);
        $this->creditSparkline  = $this->generateCreditSparkline($start, $end);
    }

    // Buckets are always confined to [$start, $end] — never extend outside the
    // selected period, or the sparkline stops reflecting the period picker.
    private function sparkBuckets(Carbon $start, Carbon $end): array
    {
        // Build the buckets on business-timezone days, then hand back UTC bounds
        // (queries bind wall-clock time, so they must be UTC).
        $tz    = config('tenant.timezone');
        $start = $start->copy()->setTimezone($tz);
        $end   = $end->copy()->setTimezone($tz);

        return array_map(fn ($b) => [$b[0]->copy()->utc(), $b[1]->copy()->utc()], $this->localSparkBuckets($start, $end));
    }

    private function localSparkBuckets(Carbon $start, Carbon $end): array
    {
        if ($start->isSameDay($end)) {
            // Single-day period: 7 fixed hourly slots covering that day's full 24h
            // (mirrors App\Livewire\Shop\Dashboard's single-day sparkline bucketing).
            $day = $start->copy()->startOfDay();
            $hourSlots = [[0, 3], [4, 7], [8, 10], [11, 13], [14, 16], [17, 19], [20, 23]];
            return array_map(fn ($slot) => [
                $day->copy()->setTime($slot[0], 0, 0),
                $day->copy()->setTime($slot[1], 59, 59),
            ], $hourSlots);
        }

        $days = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;

        // At most 14 points spread over the WHOLE period. (It used to make one
        // bucket per day for up to 31 days and then keep only the first 14, so
        // "Last 30 days" plotted the oldest two weeks and dropped the recent ones.)
        $step = max(1, (int) ceil($days / 14));

        $buckets = [];
        $cur = $start->copy()->startOfDay();
        while ($cur->lte($end)) {
            $bucketEnd = $cur->copy()->addDays($step - 1)->endOfDay();
            if ($bucketEnd->gt($end)) $bucketEnd = $end->copy();
            $buckets[] = [$cur->copy(), $bucketEnd];
            $cur->addDays($step);
        }
        return array_slice($buckets, 0, 14);
    }

    private function generateSalesSparkline(Carbon $start, Carbon $end): array
    {
        return $this->floats($this->windowSums(
            Sale::notVoided(), 'sale_date', 'total', $this->sparkBuckets($start, $end)
        ));
    }

    private function generateProfitSparkline(Carbon $start, Carbon $end): array
    {
        return $this->floats($this->windowSums(
            $this->marginQuery(),
            'sales.sale_date',
            'sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)',
            $this->sparkBuckets($start, $end)
        ));
    }

    private function generateExpenseSparkline(Carbon $start, Carbon $end): array
    {
        $buckets = array_map(
            fn ($b) => [$this->localDate($b[0]), $this->localDate($b[1])],
            $this->sparkBuckets($start, $end)
        );

        return $this->floats($this->windowSums(
            $this->expenseQuery(), 'daily_sessions.session_date', 'expenses.amount', $buckets
        ));
    }

    // Receivables' KPI value is the current OUTSTANDING BALANCE (a stock), not
    // repayments collected (a flow) — so the sparkline must trend the balance
    // itself. Reconstruct it per bucket (credit issued − repaid − written off),
    // running-sum it, then anchor the final point to the live outstanding total
    // so the chart always agrees with the number shown above it.
    private function generateCreditSparkline(Carbon $start, Carbon $end): array
    {
        $buckets = $this->sparkBuckets($start, $end);

        $issued     = $this->windowSums(Sale::notVoided()->where('has_credit', true), 'sale_date', 'credit_amount', $buckets);
        $repaid     = $this->windowSums(DB::table('credit_repayments'), 'repayment_date', 'amount', $buckets);
        $writtenOff = $this->windowSums(DB::table('credit_writeoffs'), 'written_off_at', 'amount', $buckets);

        $deltas = array_map(
            fn ($i) => (float) $issued[$i] - (float) $repaid[$i] - (float) $writtenOff[$i],
            array_keys($buckets)
        );

        $running = [];
        $sum = 0.0;
        foreach ($deltas as $d) {
            $sum += $d;
            $running[] = $sum;
        }

        $target       = (float) ($this->credit['outstanding'] ?? 0);
        $lastRunning  = end($running) ?: 0;
        $offset       = $target - $lastRunning;

        return array_map(fn ($v) => max($v + $offset, 0), $running);
    }

    /**
     * SUM($expr) for each [from, to] window (inclusive, like whereBetween) in
     * ONE query instead of one query per window. Keys of $windows are kept;
     * $countKey also returns COUNT(*) for that window under 'count'.
     * $column / $expr are code constants, never user input.
     */
    private function windowSums($query, string $column, string $expr, array $windows, ?string $countKey = null): array
    {
        if (empty($windows)) {
            return [];
        }

        $selects  = [];
        $bindings = [];
        $aliases  = [];
        foreach (array_keys($windows) as $n => $key) {
            $aliases[$key] = "w{$n}";
            $selects[]     = "COALESCE(SUM(CASE WHEN {$column} BETWEEN ? AND ? THEN {$expr} END), 0) AS w{$n}";
            array_push($bindings, $windows[$key][0], $windows[$key][1]);
        }
        if ($countKey !== null) {
            $selects[] = "COUNT(*) FILTER (WHERE {$column} BETWEEN ? AND ?) AS wcount";
            array_push($bindings, $windows[$countKey][0], $windows[$countKey][1]);
        }

        // Only scan rows inside some window (keeps the date index usable)
        $row = $query
            ->where(function ($q) use ($column, $windows) {
                foreach ($windows as [$from, $to]) {
                    $q->orWhereBetween($column, [$from, $to]);
                }
            })
            ->selectRaw(implode(', ', $selects), $bindings)
            ->first();

        $out = [];
        foreach ($aliases as $key => $alias) {
            $out[$key] = $row->{$alias} ?? 0;
        }
        if ($countKey !== null) {
            $out['count'] = (int) ($row->wcount ?? 0);
        }

        return $out;
    }

    private function floats(array $values): array
    {
        return array_map(fn ($v) => (float) $v, array_values($values));
    }

    private function marginQuery()
    {
        return SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at');
    }

    private function expenseQuery()
    {
        return Expense::whereNull('expenses.deleted_at')
            ->where('expenses.is_system_generated', false)
            ->join('daily_sessions', 'expenses.daily_session_id', '=', 'daily_sessions.id');
    }

    private function periodRange(): array
    {
        return $this->businessPeriodRange();
    }

    private function previousRange(): array
    {
        [$start, $end] = $this->periodRange();
        $diff = max($start->diffInDays($end), 1);
        return [
            $start->copy()->subDays($diff),
            $end->copy()->subDays($diff),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.business-kpi-row');
    }
}