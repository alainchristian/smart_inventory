<?php

namespace App\Livewire\Shop\Sales;

use App\Models\Sale;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SalesIndex extends Component
{
    public int    $shopId;
    public string $search         = '';
    public string $dateFilter     = 'today';
    public string $paymentFilter  = 'all';
    public string $sortBy         = 'sale_date';
    public string $sortDir        = 'desc';
    public ?int   $expandedId     = null;
    public int    $perPage        = 20;
    public bool   $hasMore        = false;

    protected $queryString = [
        'search'        => ['except' => ''],
        'dateFilter'    => ['except' => 'today'],
        'paymentFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $this->shopId = $user->isShopManager()
            ? $user->location_id
            : (request()->get('shop_id') ?? session('selected_shop_id') ?? Shop::first()?->id);
    }

    // Reset to first page whenever filters change
    public function updatingSearch(): void        { $this->perPage = 20; }
    public function updatingDateFilter(): void    { $this->perPage = 20; }
    public function updatingPaymentFilter(): void { $this->perPage = 20; }

    public function sort(string $col): void
    {
        $this->sortDir = $this->sortBy === $col
            ? ($this->sortDir === 'asc' ? 'desc' : 'asc')
            : 'desc';
        $this->sortBy  = $col;
        $this->perPage = 20;
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function loadMore(): void
    {
        if ($this->hasMore) {
            $this->perPage += 20;
        }
    }

    protected function getDateRange(): array
    {
        return match ($this->dateFilter) {
            'today'      => [now()->startOfDay(),           now()->endOfDay()],
            'yesterday'  => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_week'  => [now()->startOfWeek(),          now()->endOfDay()],
            'this_month' => [now()->startOfMonth(),         now()->endOfDay()],
            'last_30'    => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            default      => [null, null],
        };
    }

    // Apply payment/voided filter to any query
    protected function applyPaymentFilter($query)
    {
        if ($this->paymentFilter === 'voided') {
            return $query->whereNotNull('voided_at');
        }
        // Credit is stored as has_credit=true, not as payment_method='credit'
        if ($this->paymentFilter === 'credit') {
            return $query->whereNull('voided_at')->where('has_credit', true);
        }
        if ($this->paymentFilter !== 'all') {
            return $query->whereNull('voided_at')
                         ->where('payment_method', $this->paymentFilter);
        }
        return $query->whereNull('voided_at');
    }

    public function render()
    {
        [$from, $to] = $this->getDateRange();

        // ── Main table query (search + payment + date) ─────────────
        $query = Sale::with(['soldBy', 'items', 'payments', 'customer'])
            ->where('shop_id', $this->shopId);

        if ($from && $to) {
            $query->whereBetween('sale_date', [$from, $to]);
        }

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(fn($q) => $q
                ->where('sale_number',   'ilike', "%{$s}%")
                ->orWhere('customer_name',  'ilike', "%{$s}%")
                ->orWhere('customer_phone', 'ilike', "%{$s}%")
            );
        }

        $this->applyPaymentFilter($query);

        $totalFiltered  = (clone $query)->count();
        $this->hasMore  = $totalFiltered > $this->perPage;
        $sales          = $query->orderBy($this->sortBy, $this->sortDir)
                                ->take($this->perPage)
                                ->get();

        // ── Summary KPIs — date + payment filter, no search ────────
        $sumBase = Sale::where('shop_id', $this->shopId);
        if ($from && $to) {
            $sumBase->whereBetween('sale_date', [$from, $to]);
        }
        $this->applyPaymentFilter($sumBase);

        $summaryTotal  = (int) (clone $sumBase)->sum('total');
        $summaryCount  = (int) (clone $sumBase)->count();
        $summaryAvg    = $summaryCount > 0 ? (int) round($summaryTotal / $summaryCount) : 0;

        // Cash collected — scoped to same payment filter as KPIs
        $summaryCash = (int) DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')->whereNull('sales.deleted_at')
            ->where('sales.shop_id', $this->shopId)
            ->when($from && $to, fn($q) => $q->whereBetween('sales.sale_date', [$from, $to]))
            ->when(
                $this->paymentFilter === 'credit',
                fn($q) => $q->where('sales.has_credit', true)
            )
            ->when(
                $this->paymentFilter !== 'all' && $this->paymentFilter !== 'voided' && $this->paymentFilter !== 'credit',
                fn($q) => $q->where('sales.payment_method', $this->paymentFilter)
            )
            ->when(
                $this->paymentFilter === 'voided',
                fn($q) => $q->whereNotNull('sales.voided_at')
            )
            ->where('sale_payments.payment_method', 'cash')
            ->sum('sale_payments.amount');

        $summaryCredit = (int) (clone $sumBase)->where('has_credit', true)->sum('credit_amount');

        // ── Expanded detail ─────────────────────────────────────────
        $expandedSale = $this->expandedId
            ? Sale::with(['items.product', 'payments', 'soldBy', 'customer'])->find($this->expandedId)
            : null;

        $activePeriodLabel = [
            'today'      => 'Today',
            'yesterday'  => 'Yesterday',
            'this_week'  => 'This Week',
            'this_month' => 'This Month',
            'last_30'    => 'Last 30 Days',
            'all'        => 'All Time',
        ][$this->dateFilter] ?? 'Custom';

        return view('livewire.shop.sales.sales-index', compact(
            'sales', 'totalFiltered',
            'summaryTotal', 'summaryCount', 'summaryAvg', 'summaryCash', 'summaryCredit',
            'expandedSale', 'activePeriodLabel'
        ));
    }
}
