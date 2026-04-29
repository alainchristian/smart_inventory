<?php
namespace App\Services\Reports;

use App\Services\Analytics\SalesAnalyticsService;
use App\Services\Analytics\InventoryAnalyticsService;
use App\Services\Analytics\LossAnalyticsService;
use App\Services\Analytics\TransferAnalyticsService;
use App\Services\Analytics\FinanceAnalyticsService;

class ReportRunner
{
    public function __construct(
        private SalesAnalyticsService     $sales,
        private InventoryAnalyticsService $inventory,
        private LossAnalyticsService      $loss,
        private TransferAnalyticsService  $transfers,
        private FinanceAnalyticsService   $finance,
        private MetricRegistry            $registry,
    ) {}

    /**
     * Resolve dates from the config's date_range setting.
     * Returns [$dateFrom, $dateTo] as Y-m-d strings.
     */
    public function resolveDates(array $config): array
    {
        if ($config['date_range'] === 'custom' && $config['date_from'] && $config['date_to']) {
            return [$config['date_from'], $config['date_to']];
        }
        $to = now()->toDateString();
        $from = match ($config['date_range'] ?? 'month') {
            'today'   => now()->toDateString(),
            'week'    => now()->startOfWeek()->toDateString(),
            'month'   => now()->startOfMonth()->toDateString(),
            'quarter' => now()->startOfQuarter()->toDateString(),
            'year'    => now()->startOfYear()->toDateString(),
            default   => now()->startOfMonth()->toDateString(),
        };
        return [$from, $to];
    }

    /**
     * Run all blocks in the config and return resolved data.
     * Returns array keyed by block id => ['block' => [...], 'meta' => [...], 'data' => [...]]
     */
    public function run(array $config, ?int $reportId = null, bool $writeHistory = true): array
    {
        $startTime = microtime(true);
        [$dateFrom, $dateTo] = $this->resolveDates($config);
        $locationFilter = $config['location_filter'] ?? 'all';
        $blocks  = $config['blocks'] ?? [];
        $results = [];

        foreach ($blocks as $block) {
            $metricId = $block['metric_id'] ?? null;
            if ($metricId === 'text_block') {
                // Text blocks have no data — pass through as-is
                $results[$block['id']] = [
                    'block' => $block,
                    'meta'  => ['id' => 'text_block', 'label' => 'Text', 'viz_options' => ['text'], 'default_viz' => 'text'],
                    'data'  => [],
                ];
                continue;
            }
            if (! $metricId) continue;
            $meta = $this->registry->find($metricId);
            if (! $meta) continue;

            // Per-block filter overrides
            $effectiveLocation = $block['location_filter_override'] ?? $locationFilter;
            $effectiveDateFrom = $dateFrom;
            $effectiveDateTo   = $dateTo;
            if (! empty($block['date_range_override'])) {
                [$effectiveDateFrom, $effectiveDateTo] = $this->resolveDates([
                    'date_range' => $block['date_range_override'],
                    'date_from'  => $block['date_from_override'] ?? null,
                    'date_to'    => $block['date_to_override'] ?? null,
                ]);
            }

            try {
                $data = $this->resolveBlock($metricId, $effectiveDateFrom, $effectiveDateTo, $effectiveLocation);

                // Apply block-level options (sort, limit, filter)
                $data = $this->applyBlockOptions($block, $data);

                // Period comparison
                if (! empty($config['comparison_mode']) && $config['comparison_mode'] !== 'none') {
                    [$priorFrom, $priorTo] = $this->resolvePriorDates($config, $effectiveDateFrom, $effectiveDateTo);
                    try {
                        $priorData = $this->resolveBlock($metricId, $priorFrom, $priorTo, $effectiveLocation);
                        $data['_comparison'] = $priorData;
                        $data['_comparison_period'] = $priorFrom . ' – ' . $priorTo;
                    } catch (\Throwable) {
                        $data['_comparison'] = null;
                    }
                }
            } catch (\Throwable $e) {
                $data = ['error' => $e->getMessage()];
            }

            $results[$block['id']] = [
                'block'  => $block,
                'meta'   => $meta,
                'data'   => $data,
                'ran_at' => now()->toDateTimeString(),
            ];
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Write run history and update cache
        if ($reportId && $writeHistory) {
            \App\Models\ReportRunHistory::create([
                'report_id'       => $reportId,
                'run_by'          => auth()->id() ?? 0,
                'run_at'          => now(),
                'config_snapshot' => $config,
                'results'         => $results,
                'duration_ms'     => $durationMs,
                'was_scheduled'   => false,
            ]);

            // Cache in saved_reports
            $report = \App\Models\SavedReport::find($reportId);
            $report?->cacheResults($results);

            // Prune old history (keep last 12)
            \App\Models\ReportRunHistory::where('report_id', $reportId)
                ->orderByDesc('run_at')
                ->skip(12)
                ->take(PHP_INT_MAX)
                ->delete();
        }

        return $results;
    }

    private function resolveBlock(
        string $metricId,
        string $dateFrom,
        string $dateTo,
        string $locationFilter,
    ): array {
        return match ($metricId) {
            // ── Sales ──────────────────────────────────────────────────────
            'sales_revenue'           => $this->sales->getRevenueKpis($dateFrom, $dateTo, $locationFilter),
            'sales_gross_profit'      => $this->sales->getGrossProfitKpis($dateFrom, $dateTo, $locationFilter),
            'sales_transaction_count' => $this->sales->getRevenueKpis($dateFrom, $dateTo, $locationFilter),
            'sales_avg_basket'        => $this->wrapAvgBasket($dateFrom, $dateTo, $locationFilter),
            'sales_by_shop'           => $this->sales->getShopPerformance($dateFrom, $dateTo),
            'sales_top_products'      => $this->sales->getTopProducts($dateFrom, $dateTo, $locationFilter),
            'sales_payment_methods'   => $this->sales->getPaymentMethodBreakdown($dateFrom, $dateTo, $locationFilter),
            'sales_revenue_trend'     => $this->sales->getRevenueTrend($dateFrom, $dateTo, $locationFilter),
            'sales_voided'            => $this->sales->getVoidedSalesStats($dateFrom, $dateTo, $locationFilter),

            // ── Inventory ──────────────────────────────────────────────────
            'inventory_cost_value'             => $this->inventory->getInventoryKpis($locationFilter),
            'inventory_retail_value'           => $this->inventory->getInventoryKpis($locationFilter),
            'inventory_fill_rate'              => ['fill_rate' => $this->inventory->getPortfolioFillRate($locationFilter)],
            'inventory_aging'                  => $this->inventory->getAgingAnalysis($locationFilter),
            'inventory_dead_stock'             => $this->inventory->getStockHealth($locationFilter),
            'inventory_abc_summary'            => $this->inventory->getVelocityClassification($locationFilter),
            'inventory_top_by_value'           => $this->inventory->getTopProductsByValue($locationFilter, 20),
            'inventory_category_concentration' => $this->inventory->getCategoryConcentration($locationFilter),
            'inventory_by_location'            => $this->inventory->getInventoryByLocation(),

            // ── Replenishment ──────────────────────────────────────────────
            'replenishment_critical'     => $this->wrapCriticalOnly($locationFilter),
            'replenishment_days_on_hand' => $this->inventory->getDaysOnHandPerProduct($locationFilter, 50),

            // ── Loss ───────────────────────────────────────────────────────
            'loss_total'         => $this->loss->getLossKpis($dateFrom, $dateTo, $locationFilter),
            'loss_return_rate'   => $this->loss->getLossKpis($dateFrom, $dateTo, $locationFilter),
            'loss_damaged_value' => $this->loss->getLossKpis($dateFrom, $dateTo, $locationFilter),
            'loss_shrinkage'     => $this->inventory->getShrinkageStats($locationFilter),
            'loss_by_product'    => $this->loss->getProblemProducts($dateFrom, $dateTo, $locationFilter),

            // ── Transfers ──────────────────────────────────────────────────
            'transfers_kpis'          => $this->transfers->getTransferKpis($dateFrom, $dateTo, null),
            'transfers_discrepancies' => $this->transfers->getRecentDiscrepancies($dateFrom, $dateTo, 20),
            'transfers_routes'        => $this->transfers->getTransferRoutes($dateFrom, $dateTo),

            // ── Operations ─────────────────────────────────────────────────
            'ops_low_stock_count'  => $this->inventory->getStockHealth($locationFilter),
            'ops_damaged_pending'  => ['count' => \App\Models\DamagedGood::where('disposition', 'pending')->whereNull('deleted_at')->count()],
            'ops_stock_turnover'   => ['turnover_rate' => $this->inventory->calculateStockTurnover($locationFilter)],

            // ── Finance ────────────────────────────────────────────────────
            'finance_expense_summary'    => $this->finance->getExpenseSummary($dateFrom, $dateTo, $locationFilter),
            'finance_expense_trend'      => $this->finance->getExpenseTrend($dateFrom, $dateTo, $locationFilter),
            'finance_withdrawal_summary' => $this->finance->getWithdrawalSummary($dateFrom, $dateTo, $locationFilter),
            'finance_cash_variance'      => $this->finance->getCashVarianceSummary($dateFrom, $dateTo, $locationFilter),
            'finance_net_operating'      => $this->finance->getNetOperatingResult($dateFrom, $dateTo, $locationFilter),

            default => throw new \InvalidArgumentException("Unknown metric: {$metricId}"),
        };
    }

    private function wrapAvgBasket(string $from, string $to, string $loc): array
    {
        $revenue = $this->sales->getRevenueKpis($from, $to, $loc);
        $txCount = $revenue['transactions_count'] ?? 1;
        return [
            'avg_basket' => $txCount > 0
                ? round(($revenue['total_revenue'] ?? 0) / $txCount)
                : 0,
        ];
    }

    private function wrapCriticalOnly(string $loc): array
    {
        $all = $this->inventory->getDaysOnHandPerProduct($loc, 200);
        return array_values(array_filter($all, fn ($p) => $p['is_critical'] === true));
    }

    private function applyBlockOptions(array $block, array $data): array
    {
        $options = $block['block_options'] ?? [];
        if (empty($options) || !is_array($data)) return $data;

        // Detect if data is a collection of rows (numeric keys, each item is an array)
        $isCollection = isset($data[0]) && is_array($data[0]);
        if (!$isCollection) return $data;

        $collection = collect($data);

        // Sort
        if (!empty($options['sort_by'])) {
            $dir = $options['sort_direction'] ?? 'desc';
            $collection = $dir === 'asc'
                ? $collection->sortBy($options['sort_by'])
                : $collection->sortByDesc($options['sort_by']);
        }

        // Limit
        if (!empty($options['limit']) && is_numeric($options['limit'])) {
            $collection = $collection->take((int) $options['limit']);
        }

        return $collection->values()->toArray();
    }

    private function resolvePriorDates(array $config, string $currentFrom, string $currentTo): array
    {
        $mode = $config['comparison_mode'] ?? 'prior_period';
        $from = \Carbon\Carbon::parse($currentFrom);
        $to   = \Carbon\Carbon::parse($currentTo);
        $days = max($from->diffInDays($to), 1);

        return match ($mode) {
            'prior_year' => [
                $from->copy()->subYear()->toDateString(),
                $to->copy()->subYear()->toDateString(),
            ],
            default => [  // prior_period
                $from->copy()->subDays($days)->toDateString(),
                $to->copy()->subDays($days)->toDateString(),
            ],
        };
    }
}
