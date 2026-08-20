<?php

namespace App\Livewire\Owner\Reports;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\HeldSale;
use App\Models\Sale;
use App\Models\Shop;
use App\Services\Analytics\SalesAnalyticsService;
use App\Services\Sales\SaleService;
use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SalesAnalytics extends Component
{
    // ─── Filters ──────────────────────────────────────────────────────────────
    public string $dateFrom    = '';
    public string $dateTo      = '';
    public string $locationFilter = 'all';
    public string $activeTab   = 'overview';   // overview | ledger | audit | sellers | payments | credit

    // ─── Reject held-sale override modal ─────────────────────────────────────
    public bool $showRejectHeldModal = false;
    public ?int $rejectHeldId        = null;
    public string $rejectHeldReason  = '';

    protected $queryString = [
        'dateFrom'       => ['except' => ''],
        'dateTo'         => ['except' => ''],
        'locationFilter' => ['except' => 'all'],
        'activeTab'      => ['except' => 'overview'],
    ];

    // ─── Lifecycle ────────────────────────────────────────────────────────────
    public function mount(): void
    {
        if (! $this->dateFrom) {
            $this->dateFrom = now()->startOfMonth()->toDateString();
        }
        if (! $this->dateTo) {
            $this->dateTo = now()->toDateString();
        }
    }

    // ─── Actions ──────────────────────────────────────────────────────────────
    public function setDateRange(string $range): void
    {
        $this->dateTo = now()->toDateString();
        $this->dateFrom = match ($range) {
            'today'   => now()->startOfDay()->toDateString(),
            'week'    => now()->startOfWeek()->toDateString(),
            'month'   => now()->startOfMonth()->toDateString(),
            'quarter' => now()->startOfQuarter()->toDateString(),
            'year'    => now()->startOfYear()->toDateString(),
            default   => now()->startOfMonth()->toDateString(),
        };
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ─── Export ───────────────────────────────────────────────────────────────
    public function exportLedgerCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $csv = $this->buildCsv('Product Sales Ledger', $this->topProducts, [
            'product_name'      => 'Product',
            'quantity_sold'     => 'Units',
            'transaction_count' => 'Transactions',
            'avg_selling_price' => 'Avg Price (RWF)',
            'revenue'           => 'Revenue (RWF)',
            'gross_profit'      => 'Gross Profit (RWF)',
            'margin_pct'        => 'Margin %',
            'credit_revenue'    => 'Credit Sales (RWF)',
        ]);

        return $this->streamCsv($csv, 'sales-ledger');
    }

    public function exportSellersCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $csv = $this->buildCsv('Seller Performance', $this->sellerPerformance, [
            'seller_name'  => 'Seller',
            'shop_name'    => 'Shop',
            'transactions' => 'Transactions',
            'revenue'      => 'Revenue (RWF)',
            'avg_order'    => 'Avg Order (RWF)',
            'items_sold'   => 'Items Sold',
            'gross_profit' => 'Gross Profit (RWF)',
            'margin_pct'   => 'Margin %',
            'total_discount' => 'Discounts (RWF)',
            'override_count' => 'Overrides',
            'void_count'   => 'Voided',
        ]);

        return $this->streamCsv($csv, 'seller-performance');
    }

    /** @param array<string,string> $columns map of data key => CSV header label */
    private function buildCsv(string $title, array $rows, array $columns): string
    {
        $lines   = [];
        $lines[] = '"' . $title . '"';
        $lines[] = '"Period: ' . $this->dateFrom . ' to ' . $this->dateTo . '"';
        $lines[] = '"Generated: ' . now()->format('d M Y H:i') . '"';
        $lines[] = '';
        $lines[] = implode(',', array_map(fn ($label) => '"' . $label . '"', array_values($columns)));

        foreach ($rows as $row) {
            $lines[] = implode(',', array_map(
                fn ($key) => '"' . str_replace('"', '""', (string) ($row[$key] ?? '')) . '"',
                array_keys($columns)
            ));
        }

        return implode("\r\n", $lines);
    }

    private function streamCsv(string $csv, string $slug): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $slug . '-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // ─── Actions ──────────────────────────────────────────────────────────────
    public function approvePriceOverride(int $saleId): void
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isAdmin()) {
            return;
        }

        $sale = Sale::find($saleId);
        if (! $sale || $sale->price_override_approved_at !== null) {
            return; // already approved or not found
        }

        app(SaleService::class)->approvePriceOverride($sale, '');

        Cache::flush();

        $this->dispatch('notification', [
            'type'    => 'success',
            'message' => "Price override on {$sale->sale_number} approved.",
        ]);
    }

    public function approveHeldSale(int $id): void
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isAdmin()) {
            return;
        }

        $held = HeldSale::find($id);
        if (! $held || $held->override_approved_at) {
            return;
        }

        $held->update([
            'override_approved_at' => now(),
            'override_approved_by' => $user->id,
        ]);

        Alert::where('entity_type', 'HeldSale')
            ->where('entity_id', $held->id)
            ->where('is_resolved', false)
            ->each(fn ($a) => $a->markAsResolved($user->id));

        ActivityLog::create([
            'user_id'           => $user->id,
            'user_name'         => $user->name,
            'action'            => 'held_sale_approved',
            'entity_type'       => 'HeldSale',
            'entity_id'         => $held->id,
            'entity_identifier' => $held->hold_reference,
            'details'           => ['seller' => $held->seller->name, 'cart_total' => $held->cart_total],
            'ip_address'        => request()->ip(),
        ]);

        Cache::flush();

        $this->dispatch('notification', [
            'type'    => 'success',
            'message' => "{$held->hold_reference} approved.",
        ]);
    }

    public function openRejectHeldModal(int $id): void
    {
        $this->rejectHeldId       = $id;
        $this->rejectHeldReason   = '';
        $this->showRejectHeldModal = true;
    }

    public function closeRejectHeldModal(): void
    {
        $this->showRejectHeldModal = false;
        $this->rejectHeldId        = null;
        $this->rejectHeldReason    = '';
    }

    public function rejectHeldSale(): void
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isAdmin()) {
            return;
        }

        if (empty(trim($this->rejectHeldReason))) {
            $this->addError('rejectHeldReason', 'Please provide a reason for rejection.');
            return;
        }

        $held = HeldSale::find($this->rejectHeldId);
        if (! $held || $held->override_approved_at || $held->override_rejected_at) {
            $this->closeRejectHeldModal();
            return;
        }

        $held->update([
            'override_rejected_at' => now(),
            'override_rejected_by' => $user->id,
            'rejected_reason'      => $this->rejectHeldReason,
        ]);

        Alert::where('entity_type', 'HeldSale')
            ->where('entity_id', $held->id)
            ->where('is_resolved', false)
            ->each(fn ($a) => $a->markAsResolved($user->id));

        ActivityLog::create([
            'user_id'           => $user->id,
            'user_name'         => $user->name,
            'action'            => 'held_sale_rejected',
            'entity_type'       => 'HeldSale',
            'entity_id'         => $held->id,
            'entity_identifier' => $held->hold_reference,
            'details'           => ['seller' => $held->seller->name, 'reason' => $this->rejectHeldReason],
            'ip_address'        => request()->ip(),
        ]);

        Cache::flush();

        $reference = $held->hold_reference;
        $this->closeRejectHeldModal();

        $this->dispatch('notification', [
            'type'    => 'success',
            'message' => "{$reference} rejected.",
        ]);
    }

    // ─── Computed: meta ───────────────────────────────────────────────────────
    public function getActiveDateRangeLabelProperty(): string
    {
        $from = Carbon::parse($this->dateFrom);
        $to   = Carbon::parse($this->dateTo);

        // Check if it's today
        if ($from->isToday() && $to->isToday()) {
            return 'Today';
        }

        // Check if it's current week (Monday to today)
        if ($from->isSameDay(now()->startOfWeek()) && $to->isToday()) {
            return 'This Week';
        }

        // Check if it's current month (1st to today)
        if ($from->isSameDay(now()->startOfMonth()) && $to->isToday()) {
            return 'This Month';
        }

        // Check if it's current quarter
        if ($from->isSameDay(now()->startOfQuarter()) && $to->isToday()) {
            return 'This Quarter';
        }

        // Check if it's current year
        if ($from->isSameDay(now()->startOfYear()) && $to->isToday()) {
            return 'This Year';
        }

        // Custom range
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
        if (str_starts_with($this->locationFilter, 'shop:')) {
            $id = (int) explode(':', $this->locationFilter)[1];
            $shop = $this->shops->firstWhere('id', $id);
            return $shop ? $shop->name : 'Shop';
        }
        return 'All Shops';
    }

    // ─── Computed: Overview tab ───────────────────────────────────────────────
    public function getRevenueKpisProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getRevenueKpis($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getGrossProfitKpisProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getGrossProfitKpis($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getItemsSoldKpiProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getItemsSoldKpi($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getReturnsImpactProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getReturnsImpact($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getRevenueTrendProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getRevenueTrend($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getRecentTransactionsProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getRecentTransactions($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getPaymentMethodsProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getPaymentMethodBreakdown($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getShopPerformanceProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getShopPerformance($this->dateFrom, $this->dateTo);
    }

    public function getSaleTypeBreakdownProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getSaleTypeBreakdown($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getVoidedSalesStatsProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getVoidedSalesStats($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getSalesByHourProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getSalesByHour($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getTopProductsProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getTopProducts($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    public function getPriceOverrideStatsProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getPriceOverrideStats($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    // ─── Computed: Daily Scorecard (Overview) ─────────────────────────────────
    public function getDailyScorecardProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getDailyScorecard($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    // ─── Computed: Sellers tab ────────────────────────────────────────────────
    public function getSellerPerformanceProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getSellerPerformance($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    /**
     * Seller performance grouped by shop (shop subtotal + its sellers,
     * both ranked by revenue) — sellerPerformance stays flat for CSV export.
     */
    public function getSellersByShopProperty(): array
    {
        return collect($this->sellerPerformance)
            ->groupBy('shop_name')
            ->map(fn ($sellers, $shopName) => [
                'shop_name'    => $shopName,
                'revenue'      => $sellers->sum('revenue'),
                'transactions' => $sellers->sum('transactions'),
                'seller_count' => $sellers->count(),
                'sellers'      => $sellers->values()->all(),
            ])
            ->sortByDesc('revenue')
            ->values()
            ->all();
    }

    public function getCustomerRepeatAnalysisProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getCustomerRepeatAnalysis($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    // ─── Computed: Audit tab ──────────────────────────────────────────────────
    public function getPriceAuditLogProperty(): array
    {
        return app(SalesAnalyticsService::class)
            ->getPriceAuditLog($this->dateFrom, $this->dateTo, $this->locationFilter);
    }

    /**
     * Business-setting threshold (%) — overrides at or below this need no
     * owner action; only overrides above it are real pending approvals.
     */
    public function getPriceOverrideThresholdProperty(): int
    {
        return app(SettingsService::class)->priceOverrideThreshold();
    }

    /**
     * Overrides that genuinely still need an owner decision — same rule
     * the Audit tab uses per row: owner self-overrides, already-approved/
     * rejected rows, and completed sales within the policy threshold are
     * all excluded. Drives the Overview tab's "needs approval" banner —
     * it should only show while this is non-empty.
     */
    public function getPendingPriceApprovalsProperty(): array
    {
        $threshold = $this->priceOverrideThreshold;

        $pending = collect($this->priceAuditLog)->filter(function (array $e) use ($threshold) {
            if (($e['seller_role'] ?? '') === 'owner') return false;
            if ($e['source'] === 'held' && $e['is_rejected']) return false;
            if ($e['is_approved']) return false;
            if ($e['source'] === 'sale' && $e['discount_pct'] <= $threshold) return false;
            return true;
        });

        return [
            'count'          => $pending->count(),
            'total_discount' => (int) $pending->sum('total_discount'),
        ];
    }

    // ─── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        return view('livewire.owner.reports.sales-analytics')
            ->layout('layouts.app');
    }
}