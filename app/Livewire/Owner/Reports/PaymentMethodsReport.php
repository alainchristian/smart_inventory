<?php

namespace App\Livewire\Owner\Reports;

use App\Enums\PaymentMethod;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PaymentMethodsReport extends Component
{
    // ─── Filters ──────────────────────────────────────────────────────────────
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $locationFilter = 'all';

    protected $queryString = [
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'locationFilter' => ['except' => 'all'],
    ];

    // ─── Lifecycle ────────────────────────────────────────────────────────────
    public function mount(): void
    {
        if (!$this->dateFrom) {
            $this->dateFrom = now()->startOfMonth()->toDateString();
        }
        if (!$this->dateTo) {
            $this->dateTo = now()->toDateString();
        }
    }

    // ─── Actions ──────────────────────────────────────────────────────────────
    public function setDateRange(string $range): void
    {
        $this->dateTo = now()->toDateString();
        $this->dateFrom = match ($range) {
            'today' => now()->startOfDay()->toDateString(),
            'week' => now()->startOfWeek()->toDateString(),
            'month' => now()->startOfMonth()->toDateString(),
            'quarter' => now()->startOfQuarter()->toDateString(),
            'year' => now()->startOfYear()->toDateString(),
            default => now()->startOfMonth()->toDateString(),
        };
    }

    // ─── Computed Properties ──────────────────────────────────────────────────
    public function getActiveDateRangeLabelProperty(): string
    {
        $from = Carbon::parse($this->dateFrom);
        $to = Carbon::parse($this->dateTo);

        if ($from->isToday() && $to->isToday()) {
            return 'Today';
        }
        if ($from->isSameDay(now()->startOfWeek()) && $to->isToday()) {
            return 'This Week';
        }
        if ($from->isSameDay(now()->startOfMonth()) && $to->isToday()) {
            return 'This Month';
        }
        if ($from->isSameDay(now()->startOfQuarter()) && $to->isToday()) {
            return 'This Quarter';
        }
        if ($from->isSameDay(now()->startOfYear()) && $to->isToday()) {
            return 'This Year';
        }

        return $from->format('M d') . ' – ' . $to->format('M d, Y');
    }

    public function getShopsProperty()
    {
        return Shop::orderBy('name')->get(['id', 'name']);
    }

    public function getSelectedShopNameProperty(): string
    {
        if ($this->locationFilter === 'all') {
            return 'All Shops';
        }
        $shopId = (int) str_replace('shop:', '', $this->locationFilter);
        $shop = Shop::find($shopId);
        return $shop ? $shop->name : 'Unknown Shop';
    }

    // ─── Payment Analytics ────────────────────────────────────────────────────
    public function getPaymentMethodSummaryProperty(): array
    {
        $query = SalePayment::query()
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [$this->dateFrom, $this->dateTo]);

        if ($this->locationFilter !== 'all') {
            $shopId = (int) str_replace('shop:', '', $this->locationFilter);
            $query->where('sales.shop_id', $shopId);
        }

        $summary = $query
            ->select('sale_payments.payment_method', DB::raw('SUM(sale_payments.amount) as total_amount'), DB::raw('COUNT(DISTINCT sale_payments.sale_id) as transaction_count'))
            ->groupBy('sale_payments.payment_method')
            ->get();

        $result = [];
        foreach (PaymentMethod::cases() as $method) {
            $data = $summary->firstWhere('payment_method', $method->value);
            $result[$method->value] = [
                'label' => $method->label(),
                'total' => $data ? $data->total_amount : 0,
                'count' => $data ? $data->transaction_count : 0,
            ];
        }

        return $result;
    }

    public function getTotalRevenueProperty(): int
    {
        return array_sum(array_column($this->paymentMethodSummary, 'total'));
    }

    public function getTotalTransactionsProperty(): int
    {
        $query = Sale::query()
            ->whereNull('voided_at')
            ->whereBetween('sale_date', [$this->dateFrom, $this->dateTo]);

        if ($this->locationFilter !== 'all') {
            $shopId = (int) str_replace('shop:', '', $this->locationFilter);
            $query->where('shop_id', $shopId);
        }

        return $query->count();
    }

    public function getSplitPaymentStatsProperty(): array
    {
        $query = Sale::query()
            ->whereNull('voided_at')
            ->whereBetween('sale_date', [$this->dateFrom, $this->dateTo]);

        if ($this->locationFilter !== 'all') {
            $shopId = (int) str_replace('shop:', '', $this->locationFilter);
            $query->where('shop_id', $shopId);
        }

        $totalSales = $query->count();
        $splitPaymentSales = (clone $query)->where('is_split_payment', true)->count();
        $singlePaymentSales = $totalSales - $splitPaymentSales;

        return [
            'total' => $totalSales,
            'split' => $splitPaymentSales,
            'single' => $singlePaymentSales,
            'split_percentage' => $totalSales > 0 ? round(($splitPaymentSales / $totalSales) * 100, 1) : 0,
        ];
    }

    public function getCreditSalesStatsProperty(): array
    {
        $query = Sale::query()
            ->whereNull('voided_at')
            ->whereBetween('sale_date', [$this->dateFrom, $this->dateTo]);

        if ($this->locationFilter !== 'all') {
            $shopId = (int) str_replace('shop:', '', $this->locationFilter);
            $query->where('shop_id', $shopId);
        }

        $creditSales = (clone $query)->where('has_credit', true);

        return [
            'count' => $creditSales->count(),
            'total_credit_given' => $creditSales->sum('credit_amount'),
        ];
    }

    public function getRecentTransactionsProperty()
    {
        $query = Sale::query()
            ->with(['shop', 'customer', 'payments'])
            ->whereNull('voided_at')
            ->whereBetween('sale_date', [$this->dateFrom, $this->dateTo]);

        if ($this->locationFilter !== 'all') {
            $shopId = (int) str_replace('shop:', '', $this->locationFilter);
            $query->where('shop_id', $shopId);
        }

        return $query
            ->orderBy('sale_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.owner.reports.payment-methods-report');
    }
}
