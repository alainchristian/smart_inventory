<?php
namespace App\Livewire\Owner\Reports;

use App\Models\ReportRunHistory;
use App\Models\ReportViewLog;
use App\Models\SavedReport;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Reports\ExportReportAction;
use App\Services\Reports\MetricRegistry;
use App\Services\Reports\ReportContext;
use App\Services\Reports\ReportPeriod;
use App\Services\Reports\ReportRunner;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Runs a saved report for the filters in the bar (period, location,
 * comparison). The saved config only provides the defaults; changing a
 * filter never changes the report. Results are cached per filter set,
 * never held in Livewire state.
 */
class ReportViewer extends Component
{
    #[Locked]
    public int $reportId;

    // Filters (in the URL so a filtered view can be shared or reloaded)
    #[Url(as: 'period')]
    public string $preset = '';
    #[Url(as: 'from')]
    public string $dateFrom = '';
    #[Url(as: 'to')]
    public string $dateTo = '';
    #[Url(as: 'loc')]
    public string $location = '';
    #[Url(as: 'compare')]
    public string $comparison = '';

    /** Set by wire:init so the page paints before the report runs */
    public bool $ready = false;

    public bool $showHistory = false;

    /** Block whose "Details" sheet is open */
    public string $detailBlockId = '';

    public function mount(int $reportId): void
    {
        $user = auth()->user();
        abort_unless($user->isOwner() || $user->isAdmin(), 403);

        $this->reportId = $reportId;
        $config = $this->report()->resolvedConfig();

        // URL values win; anything missing or invalid falls back to the saved defaults
        if (! array_key_exists($this->preset, ReportPeriod::PRESETS)) {
            $this->preset = array_key_exists($config['date_range'], ReportPeriod::PRESETS) ? $config['date_range'] : 'month';
            $this->dateFrom = (string) ($config['date_from'] ?? '');
            $this->dateTo   = (string) ($config['date_to'] ?? '');
        }
        if ($this->location === '') {
            $this->location = $config['location_filter'] ?? 'all';
        }
        $this->location = ReportContext::normaliseLocation($this->location);
        if (! array_key_exists($this->comparison, ReportPeriod::COMPARISONS)) {
            $this->comparison = $config['comparison_mode'] ?? 'none';
        }
        $this->syncDates();

        ReportViewLog::create([
            'report_id' => $reportId,
            'viewed_by' => $user->id,
            'viewed_at' => now(),
            'was_run'   => false,
        ]);
        if ($msg = session('success')) {   // e.g. "Report saved." from the builder
            $this->dispatch('notification', ['type' => 'success', 'message' => $msg]);
        }
    }

    public function load(): void
    {
        $this->ready = true;
    }

    // ── Filters ──────────────────────────────────────────────────────────

    public function setPreset(string $preset): void
    {
        if (! array_key_exists($preset, ReportPeriod::PRESETS) || $preset === 'custom') {
            return;
        }
        $this->preset = $preset;
        $this->syncDates();
        $this->closeDetails();
    }

    public function updatedDateFrom(): void { $this->preset = 'custom'; $this->syncDates(); $this->closeDetails(); }
    public function updatedDateTo(): void   { $this->preset = 'custom'; $this->syncDates(); $this->closeDetails(); }

    public function updatedLocation(): void
    {
        $this->location = ReportContext::normaliseLocation($this->location);
        $this->closeDetails();
    }

    public function updatedComparison(): void
    {
        if (! array_key_exists($this->comparison, ReportPeriod::COMPARISONS)) {
            $this->comparison = 'none';
        }
    }

    public function resetFilters(): void
    {
        $this->preset = $this->dateFrom = $this->dateTo = $this->location = $this->comparison = '';
        $this->mountDefaults();
        $this->closeDetails();
    }

    private function mountDefaults(): void
    {
        $config = $this->report()->resolvedConfig();
        $this->preset     = array_key_exists($config['date_range'], ReportPeriod::PRESETS) ? $config['date_range'] : 'month';
        $this->dateFrom   = (string) ($config['date_from'] ?? '');
        $this->dateTo     = (string) ($config['date_to'] ?? '');
        $this->location   = ReportContext::normaliseLocation($config['location_filter'] ?? 'all');
        $this->comparison = $config['comparison_mode'] ?? 'none';
        $this->syncDates();
    }

    /** Presets fill the dates; custom keeps valid typed dates and resolves the rest */
    private function syncDates(): void
    {
        $valid = fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;

        if ($this->preset === 'custom' && $valid($this->dateFrom) && $valid($this->dateTo)) {
            [$this->dateFrom, $this->dateTo] = ReportPeriod::resolve('custom', $this->dateFrom, $this->dateTo);
            return;
        }
        if ($this->preset === 'custom') {
            $this->preset = 'month';
        }
        [$this->dateFrom, $this->dateTo] = ReportPeriod::resolve($this->preset);
    }

    protected function filters(): array
    {
        return [
            'date_range'      => 'custom',   // the dates below are already resolved
            'date_from'       => $this->dateFrom,
            'date_to'         => $this->dateTo,
            'location_filter' => $this->location,
            'comparison_mode' => $this->comparison,
        ];
    }

    #[Computed]
    public function isDefaultView(): bool
    {
        $config = $this->report()->resolvedConfig();
        [$from, $to] = app(ReportRunner::class)->resolveDates($config);

        return $from === $this->dateFrom && $to === $this->dateTo
            && ReportContext::normaliseLocation($config['location_filter'] ?? 'all') === $this->location
            && ($config['comparison_mode'] ?? 'none') === $this->comparison;
    }

    // ── Results ──────────────────────────────────────────────────────────

    private function cacheKey(): string
    {
        $report = $this->report();

        return 'custom_report:' . $report->id . ':' . md5(json_encode([$report->updated_at?->timestamp, $report->config, $this->filters()]));
    }

    /** Short cache while the period includes today, since today's numbers still move */
    private function cacheTtl(): int
    {
        return $this->dateTo >= business_today()->toDateString() ? 300 : 3600;
    }

    #[Computed]
    public function results(): array
    {
        if (! $this->ready) {
            return [];
        }

        return Cache::remember($this->cacheKey(), $this->cacheTtl(), function () {
            return app(ReportRunner::class)->run($this->report()->resolvedConfig(), $this->reportId, true, $this->filters());
        });
    }

    /** Re-run now, skipping the cache */
    public function refresh(): void
    {
        Cache::forget($this->cacheKey());
        unset($this->results);
        ReportViewLog::create([
            'report_id' => $this->reportId,
            'viewed_by' => auth()->id(),
            'viewed_at' => now(),
            'was_run'   => true,
        ]);
        $this->dispatch('notification', ['type' => 'success', 'message' => 'Report refreshed.']);
    }

    // ── Details sheet (related blocks for one KPI) ───────────────────────

    public function openDetails(string $blockId): void
    {
        $this->detailBlockId = $blockId;
    }

    public function closeDetails(): void
    {
        $this->detailBlockId = '';
    }

    #[Computed]
    public function details(): array
    {
        $entry = $this->results[$this->detailBlockId] ?? null;
        $metric = $entry ? app(MetricRegistry::class)->metric($entry['block']['metric_id'] ?? '') : null;
        if (! $metric || ! $metric->related()) {
            return [];
        }

        $block = $entry['block'];

        return Cache::remember($this->cacheKey() . ':details:' . $this->detailBlockId, $this->cacheTtl(), function () use ($metric, $block) {
            $registry = app(MetricRegistry::class);
            $runner   = app(ReportRunner::class);
            $out = [];
            foreach ($metric->related() as $id) {
                $related = $registry->metric($id);
                if (! $related) continue;
                [, $result] = $runner->runBlock($related, [
                    'id'                       => $id,
                    'date_range_override'      => $block['date_range_override'] ?? null,
                    'date_from_override'       => $block['date_from_override'] ?? null,
                    'date_to_override'         => $block['date_to_override'] ?? null,
                    'location_filter_override' => $block['location_filter_override'] ?? null,
                    'block_options'            => ['limit' => 5],
                ], $this->dateFrom, $this->dateTo, $this->location);
                $out[] = ['title' => $related->meta()['label'], 'result' => $result->toArray()];
            }
            return $out;
        });
    }

    // ── History ─────────────────────────────────────────────────────────

    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;
    }

    /** Show the report again with the filters an earlier run used */
    public function applyHistoryRun(int $historyId): void
    {
        $run = ReportRunHistory::where('report_id', $this->reportId)->findOrFail($historyId);
        $cfg = $run->config_snapshot ?? [];
        [$from, $to] = ReportPeriod::resolve($cfg['date_range'] ?? 'month', $cfg['date_from'] ?? null, $cfg['date_to'] ?? null);

        $this->preset     = 'custom';
        $this->dateFrom   = $from;
        $this->dateTo     = $to;
        $this->location   = ReportContext::normaliseLocation($cfg['location_filter'] ?? 'all');
        $this->comparison = array_key_exists($cfg['comparison_mode'] ?? '', ReportPeriod::COMPARISONS) ? $cfg['comparison_mode'] : 'none';
        $this->syncDates();
        $this->showHistory = false;
        $this->closeDetails();
    }

    #[Computed]
    public function history()
    {
        return ReportRunHistory::with('runner:id,name')
            ->where('report_id', $this->reportId)
            ->orderByDesc('run_at')->orderByDesc('id')
            ->limit(12)->get();
    }

    // ── Export ───────────────────────────────────────────────────────────

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $report = $this->report();
        $config = app(ReportRunner::class)->effectiveConfig($report->resolvedConfig(), $this->filters());
        $csv    = app(ExportReportAction::class)->toCsv($report, $this->results, $config);

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, str($report->name)->slug() . '-' . $this->dateFrom . '-to-' . $this->dateTo . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    private ?SavedReport $reportCache = null;

    protected function report(): SavedReport
    {
        $this->reportCache ??= SavedReport::with('creator:id,name')->findOrFail($this->reportId);
        abort_unless($this->reportCache->isVisibleTo(auth()->user()), 403);

        return $this->reportCache;
    }

    public function render()
    {
        $results = $this->results;
        $kpis = $blocks = [];
        foreach ($results as $id => $entry) {
            if (($entry['block']['viz'] ?? '') === 'kpi_card' && $entry['result'] !== null) {
                $kpis[$id] = $entry;
            } else {
                $blocks[$id] = $entry;
            }
        }

        // Key findings: problems first, then good news; at most 4
        $tones = ['bad' => 0, 'warn' => 1, 'good' => 2];
        $findings = collect($results)
            ->filter(fn ($e) => isset($e['result']['insight']['tone'], $tones[$e['result']['insight']['tone']]))
            ->map(fn ($e) => ['title' => $e['block']['title'] ?? '', 'text' => $e['result']['insight']['text'], 'tone' => $e['result']['insight']['tone']])
            ->sortBy(fn ($f) => $tones[$f['tone']])
            ->take(4)->values()->all();

        $comparisonPeriod = $this->comparison !== 'none'
            ? ReportPeriod::prior($this->comparison, $this->dateFrom, $this->dateTo)
            : null;

        return view('livewire.owner.reports.report-viewer', [
            'report'           => $this->report(),
            'kpis'             => $kpis,
            'blocks'           => $blocks,
            'findings'         => $findings,
            'periodLabel'      => ReportPeriod::label($this->dateFrom, $this->dateTo),
            'locationLabel'    => ReportContext::locationLabel($this->location),
            'comparisonLabel'  => $comparisonPeriod ? ReportPeriod::label(...$comparisonPeriod) : null,
            'blockCount'       => $this->report()->blockCount(),
            'shops'            => Shop::orderBy('name')->pluck('name', 'id'),
            'warehouses'       => Warehouse::orderBy('name')->pluck('name', 'id'),
            'presets'          => array_diff_key(ReportPeriod::PRESETS, ['custom' => true]),
            'printUrl'         => route('owner.reports.custom.print', $this->reportId) . '?' . http_build_query($this->filters()),
            'canEdit'          => $this->report()->created_by === auth()->id(),
        ]);
    }
}
