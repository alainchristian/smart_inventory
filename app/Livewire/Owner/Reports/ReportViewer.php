<?php
namespace App\Livewire\Owner\Reports;

use App\Models\ReportRunHistory;
use App\Models\ReportAnnotation;
use App\Models\ReportViewLog;
use App\Models\SavedReport;
use App\Models\Shop;
use App\Models\Warehouse;
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
