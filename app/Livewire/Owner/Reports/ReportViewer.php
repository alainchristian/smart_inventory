<?php
namespace App\Livewire\Owner\Reports;

use App\Models\ReportRunHistory;
use App\Models\ReportAnnotation;
use App\Models\ReportViewLog;
use App\Models\SavedReport;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Analytics\FinanceAnalyticsService;
use App\Services\Analytics\InventoryAnalyticsService;
use App\Services\Analytics\LossAnalyticsService;
use App\Services\Analytics\SalesAnalyticsService;
use App\Services\Analytics\TransferAnalyticsService;
use App\Services\Reports\ReportRunner;
use App\Services\Reports\ExportReportAction;
use Livewire\Component;

class ReportViewer extends Component
{
    public int   $reportId;
    public bool  $isRunning = false;
    public array $results   = [];
    public bool  $hasRun    = false;
    public int   $currentRunHistoryId = 0;

    // History drawer
    public bool  $showHistory      = false;
    public int   $viewingHistoryId = 0;
    public array $historyResults   = [];

    // Annotations
    public string $annotatingBlockId  = '';
    public string $annotationText     = '';
    public bool   $showAnnotationForm = false;

    // Breakdowns (lazy-loaded per block)
    public array  $breakdowns       = [];
    public string $openBreakdownId  = '';

    public function mount(int $reportId): void
    {
        $this->reportId = $reportId;
        if (! auth()->user()->isOwner() && ! auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Log page view
        ReportViewLog::create([
            'report_id' => $reportId,
            'viewed_by' => auth()->id(),
            'viewed_at' => now(),
            'was_run'   => false,
        ]);

        // Show cached results immediately if available
        $report = SavedReport::find($reportId);
        if ($report && $report->hasFreshCache()) {
            $this->results = $report->last_results;
            $this->hasRun  = true;
        }
    }

    public function run(): void
    {
        $report = SavedReport::findOrFail($this->reportId);

        ReportViewLog::create([
            'report_id' => $this->reportId,
            'viewed_by' => auth()->id(),
            'viewed_at' => now(),
            'was_run'   => true,
        ]);

        $this->isRunning = true;
        $runner          = app(ReportRunner::class);
        $this->results   = $runner->run($report->resolvedConfig(), $this->reportId, true);
        $this->hasRun    = true;
        $this->isRunning = false;

        // Track latest run history id
        $latest = $report->runHistory()->first();
        $this->currentRunHistoryId = $latest?->id ?? 0;
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $report  = SavedReport::findOrFail($this->reportId);
        $results = $this->hasRun ? $this->results : ($report->last_results ?? []);
        $csv     = app(ExportReportAction::class)->toCsv($report, $results);

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, str($report->name)->slug() . '-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPrint(): void
    {
        $this->dispatch('open-print-view', reportId: $this->reportId);
    }

    public function togglePin(): void
    {
        $report = SavedReport::findOrFail($this->reportId);
        if ($report->created_by !== auth()->id()) abort(403);

        if ($report->pinned_to_dashboard) {
            $report->update(['pinned_to_dashboard' => false, 'dashboard_position' => null]);
            session()->flash('success', 'Report unpinned from dashboard.');
        } else {
            $pinnedCount = SavedReport::where('pinned_to_dashboard', true)->count();
            if ($pinnedCount >= 3) {
                session()->flash('error', 'Maximum 3 reports can be pinned. Unpin one first.');
                return;
            }
            $maxPos = SavedReport::where('pinned_to_dashboard', true)->max('dashboard_position') ?? 0;
            $report->update([
                'pinned_to_dashboard' => true,
                'dashboard_position'  => $maxPos + 1,
            ]);
            session()->flash('success', 'Report pinned to dashboard.');
        }
    }

    // ── History drawer ───────────────────────────────────────────────────────

    public function toggleHistory(): void
    {
        $this->showHistory = !$this->showHistory;
    }

    public function viewHistoryRun(int $historyId): void
    {
        $run = ReportRunHistory::findOrFail($historyId);
        if ($run->report_id !== $this->reportId) abort(403);
        $this->historyResults      = $run->results ?? [];
        $this->viewingHistoryId    = $historyId;
        $this->currentRunHistoryId = $historyId;
        $this->results             = $this->historyResults;
        $this->hasRun              = true;
        $this->showHistory         = false;
    }

    // ── Annotations ──────────────────────────────────────────────────────────

    public function openAnnotation(string $blockId): void
    {
        $this->annotatingBlockId  = $blockId;
        $this->annotationText     = '';
        $this->showAnnotationForm = true;
    }

    public function saveAnnotation(): void
    {
        if (!trim($this->annotationText)) return;

        ReportAnnotation::create([
            'report_id'      => $this->reportId,
            'run_history_id' => $this->currentRunHistoryId ?: null,
            'block_id'       => $this->annotatingBlockId ?: null,
            'note'           => trim($this->annotationText),
            'created_by'     => auth()->id(),
        ]);

        $this->showAnnotationForm = false;
        $this->annotationText     = '';
        $this->annotatingBlockId  = '';
    }

    // ── Breakdowns ───────────────────────────────────────────────────────────

    public function loadBreakdown(string $blockId): void
    {
        if ($this->openBreakdownId === $blockId) {
            $this->openBreakdownId = '';
            return;
        }
        $this->openBreakdownId = $blockId;

        if (isset($this->breakdowns[$blockId])) return;

        $blockResult = $this->results[$blockId] ?? null;
        if (! $blockResult) return;

        $block    = $blockResult['block'];
        $metricId = $block['metric_id'] ?? '';
        $data     = is_array($blockResult['data'] ?? null) ? $blockResult['data'] : [];

        $report = SavedReport::find($this->reportId);
        $config = $report->resolvedConfig();
        $runner = app(ReportRunner::class);

        [$from, $to] = $runner->resolveDates(
            ! empty($block['date_range_override'])
                ? ['date_range' => $block['date_range_override'], 'date_from' => null, 'date_to' => null]
                : $config
        );
        $loc = $block['location_filter_override'] ?? $config['location_filter'] ?? 'all';

        try {
            $this->breakdowns[$blockId] = $this->resolveBreakdown($metricId, $data, $from, $to, $loc);
        } catch (\Throwable $e) {
            $this->breakdowns[$blockId] = ['type' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function resolveBreakdown(string $metricId, array $existing, string $from, string $to, string $loc): array
    {
        $sales     = app(SalesAnalyticsService::class);
        $inventory = app(InventoryAnalyticsService::class);
        $loss      = app(LossAnalyticsService::class);
        $finance   = app(FinanceAnalyticsService::class);
        $transfers = app(TransferAnalyticsService::class);

        return match (true) {

            in_array($metricId, ['sales_revenue', 'sales_transaction_count']) => [
                'type'     => 'sales_summary',
                'shops'    => array_slice($sales->getShopPerformance($from, $to), 0, 5),
                'products' => array_slice($sales->getTopProducts($from, $to, $loc, 5), 0, 5),
                'methods'  => $sales->getPaymentMethodBreakdown($from, $to, $loc),
            ],

            in_array($metricId, ['sales_gross_profit', 'sales_avg_basket', 'sales_voided']) => [
                'type'     => 'sales_margin',
                'products' => array_slice($sales->getTopProducts($from, $to, $loc, 5), 0, 5),
                'types'    => $sales->getSaleTypeBreakdown($from, $to, $loc),
            ],

            in_array($metricId, ['inventory_cost_value', 'inventory_retail_value']) => [
                'type'       => 'inventory_categories',
                'categories' => array_slice($inventory->getCategoryConcentration($loc), 0, 5),
                'locations'  => (function () use ($inventory) {
                    $d = $inventory->getInventoryByLocation();
                    return collect(array_merge($d['warehouses'] ?? [], $d['shops'] ?? []))
                        ->map(fn ($r) => array_merge($r, ['cost_value' => $r['value'] ?? 0]))
                        ->sortByDesc('cost_value')
                        ->values()
                        ->toArray();
                })(),
            ],

            in_array($metricId, ['inventory_fill_rate', 'ops_low_stock_count', 'inventory_dead_stock']) => [
                'type'  => 'stock_health',
                'items' => array_slice($inventory->getDaysOnHandPerProduct($loc, 10), 0, 8),
            ],

            in_array($metricId, ['loss_total', 'loss_return_rate', 'loss_damaged_value', 'loss_shrinkage']) => [
                'type'     => 'loss_detail',
                'products' => array_slice($loss->getProblemProducts($from, $to, $loc, 5), 0, 5),
                'reasons'  => $loss->getReturnReasonBreakdown($from, $to, $loc),
            ],

            $metricId === 'replenishment_critical' => [
                'type'  => 'critical_stock',
                'items' => array_slice($existing, 0, 8),
            ],

            $metricId === 'finance_net_operating' => [
                'type'     => 'finance_pl',
                'expenses' => $finance->getExpenseSummary($from, $to, $loc),
                'net'      => $existing,
            ],

            in_array($metricId, ['finance_expense_summary', 'finance_expense_trend']) => [
                'type'       => 'expense_categories',
                'by_category'=> $existing['by_category'] ?? [],
                'total'      => $existing['total_expenses'] ?? 0,
            ],

            in_array($metricId, ['finance_cash_variance', 'finance_withdrawal_summary']) => [
                'type'     => 'finance_cash',
                'existing' => $existing,
            ],

            in_array($metricId, ['transfers_kpis', 'transfers_discrepancies']) => [
                'type'   => 'transfer_routes',
                'routes' => array_slice($transfers->getTransferRoutes($from, $to), 0, 6),
            ],

            default => ['type' => 'none'],
        };
    }

    public function render()
    {
        $report = SavedReport::with('creator')->findOrFail($this->reportId);
        return view('livewire.owner.reports.report-viewer', [
            'report'     => $report,
            'warehouses' => Warehouse::pluck('name', 'id'),
            'shops'      => Shop::pluck('name', 'id'),
        ]);
    }
}
