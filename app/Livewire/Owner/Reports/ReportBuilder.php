<?php
namespace App\Livewire\Owner\Reports;

use App\Models\SavedReport;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Reports\MetricRegistry;
use Livewire\Component;

class ReportBuilder extends Component
{
    // ─── Report meta ───────────────────────────────────────────────────────
    public string  $reportName        = '';
    public string  $reportDescription = '';
    public bool    $isShared          = false;

    // ─── Global filters ────────────────────────────────────────────────────
    public string  $dateRange      = 'month';
    public ?string $dateFrom       = null;
    public ?string $dateTo         = null;
    public string  $locationFilter = 'all';

    // ─── Comparison ────────────────────────────────────────────────────────
    public string  $comparisonMode = 'none';  // none | prior_period | prior_year

    // ─── Canvas (ordered array of block configs) ───────────────────────────
    public array   $canvas = [];   // each item: {id, metric_id, title, width, viz, ...}

    // ─── Catalogue search ──────────────────────────────────────────────────
    public string  $catalogueSearch = '';
    public string  $catalogueDomain = 'all';

    // ─── Edit context ──────────────────────────────────────────────────────
    public ?int    $editingReportId = null;

    // ─── Schedule ──────────────────────────────────────────────────────────
    public string  $scheduleCron       = '';
    public string  $scheduleRecipients = '';  // comma-separated emails

    public function mount(?int $reportId = null): void
    {
        if (! auth()->user()->isOwner() && ! auth()->user()->isAdmin()) abort(403);

        if ($reportId) {
            $report = SavedReport::findOrFail($reportId);
            if ($report->created_by !== auth()->id()) abort(403);
            $this->editingReportId      = $report->id;
            $this->reportName           = $report->name;
            $this->reportDescription    = $report->description ?? '';
            $this->isShared             = $report->is_shared;
            $config                     = $report->resolvedConfig();
            $this->dateRange            = $config['date_range'];
            $this->dateFrom             = $config['date_from'];
            $this->dateTo               = $config['date_to'];
            $this->locationFilter       = $config['location_filter'];
            $this->comparisonMode       = $config['comparison_mode'] ?? 'none';
            $this->canvas               = $config['blocks'];
            $this->scheduleCron         = $report->schedule_cron ?? '';
            $this->scheduleRecipients   = implode(', ', $report->schedule_recipients ?? []);
        }
    }

    public function addBlock(string $metricId): void
    {
        $meta = app(MetricRegistry::class)->find($metricId);
        if (! $meta) return;

        $block = [
            'id'             => 'b' . now()->timestamp . '_' . count($this->canvas),
            'metric_id'      => $metricId,
            'title'          => $meta['label'],
            'width'          => 'half',
            'viz'            => $meta['default_viz'],
            'position'       => count($this->canvas),
            'show_if_nonzero' => false,
        ];

        // Extra defaults for text blocks
        if ($metricId === 'text_block') {
            $block['content'] = '';
            $block['width']   = 'full';
        }

        $this->canvas[] = $block;
    }

    public function removeBlock(string $blockId): void
    {
        $this->canvas = collect($this->canvas)
            ->filter(fn ($b) => $b['id'] !== $blockId)
            ->values()
            ->toArray();
    }

    public function moveBlockUp(string $blockId): void
    {
        $idx = collect($this->canvas)->search(fn ($b) => $b['id'] === $blockId);
        if ($idx > 0) {
            [$this->canvas[$idx], $this->canvas[$idx - 1]] =
                [$this->canvas[$idx - 1], $this->canvas[$idx]];
        }
    }

    public function moveBlockDown(string $blockId): void
    {
        $idx = collect($this->canvas)->search(fn ($b) => $b['id'] === $blockId);
        if ($idx !== false && $idx < count($this->canvas) - 1) {
            [$this->canvas[$idx], $this->canvas[$idx + 1]] =
                [$this->canvas[$idx + 1], $this->canvas[$idx]];
        }
    }

    public function updateBlockWidth(string $blockId, string $width): void
    {
        $this->canvas = collect($this->canvas)->map(fn ($b) =>
            $b['id'] === $blockId ? array_merge($b, ['width' => $width]) : $b
        )->toArray();
    }

    public function updateBlockViz(string $blockId, string $viz): void
    {
        $this->canvas = collect($this->canvas)->map(fn ($b) =>
            $b['id'] === $blockId ? array_merge($b, ['viz' => $viz]) : $b
        )->toArray();
    }

    public function updateBlockTitle(string $blockId, string $title): void
    {
        $this->canvas = collect($this->canvas)->map(fn ($b) =>
            $b['id'] === $blockId ? array_merge($b, ['title' => $title]) : $b
        )->toArray();
    }

    /** Step 7: Per-block location/date overrides */
    public function updateBlockOverride(string $blockId, string $field, string $value): void
    {
        $allowed = ['location_filter_override', 'date_range_override', 'date_from_override', 'date_to_override'];
        if (!in_array($field, $allowed)) return;
        $this->canvas = collect($this->canvas)->map(fn ($b) =>
            $b['id'] === $blockId ? array_merge($b, [$field => $value ?: null]) : $b
        )->toArray();
    }

    /** Step 9: Update text block body */
    public function updateBlockContent(string $blockId, string $content): void
    {
        $this->canvas = collect($this->canvas)->map(fn ($b) =>
            $b['id'] === $blockId ? array_merge($b, ['content' => $content]) : $b
        )->toArray();
    }

    /** Step 11: Scorecard threshold */
    public function updateBlockThreshold(string $blockId, string $field, string $value): void
    {
        $allowed = ['threshold_warning', 'threshold_critical'];
        if (!in_array($field, $allowed)) return;
        $this->canvas = collect($this->canvas)->map(fn ($b) =>
            $b['id'] === $blockId ? array_merge($b, [$field => $value === '' ? null : (float)$value]) : $b
        )->toArray();
    }

    /** Step 12: Block data controls (sort/limit) */
    public function updateBlockOption(string $blockId, string $field, string $value): void
    {
        $allowed = ['sort_by', 'sort_direction', 'limit'];
        if (!in_array($field, $allowed)) return;
        $this->canvas = collect($this->canvas)->map(function ($b) use ($blockId, $field, $value) {
            if ($b['id'] !== $blockId) return $b;
            $opts = $b['block_options'] ?? [];
            if ($value === '') {
                unset($opts[$field]);
            } else {
                $opts[$field] = $value;
            }
            return array_merge($b, ['block_options' => $opts]);
        })->toArray();
    }

    /** Step 17: Conditional block visibility */
    public function updateBlockShowIfNonzero(string $blockId, bool $value): void
    {
        $this->canvas = collect($this->canvas)->map(fn ($b) =>
            $b['id'] === $blockId ? array_merge($b, ['show_if_nonzero' => $value]) : $b
        )->toArray();
    }

    /** Step 8: Load template from ReportTemplates service */
    public function loadTemplate(string $templateKey): void
    {
        $templates = app(\App\Services\Reports\ReportTemplates::class)->get($templateKey);
        if (!$templates) return;

        $this->reportName        = $templates['name'];
        $this->reportDescription = $templates['description'] ?? '';
        $this->dateRange         = $templates['date_range'] ?? 'month';
        $this->locationFilter    = $templates['location_filter'] ?? 'all';
        $this->canvas            = [];

        foreach ($templates['blocks'] as $blockDef) {
            $meta = app(MetricRegistry::class)->find($blockDef['metric_id']);
            if (!$meta) continue;
            $this->canvas[] = array_merge([
                'id'             => 'b' . now()->timestamp . '_' . count($this->canvas),
                'title'          => $meta['label'],
                'width'          => 'half',
                'viz'            => $meta['default_viz'],
                'position'       => count($this->canvas),
                'show_if_nonzero' => false,
            ], $blockDef);
        }
    }

    public function save(): void
    {
        $this->validate([
            'reportName' => 'required|string|min:2|max:120',
            'canvas'     => 'array|min:1',
        ], [
            'reportName.required' => 'Give the report a name.',
            'canvas.min'          => 'Add at least one metric block before saving.',
        ]);

        $config = [
            'date_range'      => $this->dateRange,
            'date_from'       => $this->dateFrom,
            'date_to'         => $this->dateTo,
            'location_filter' => $this->locationFilter,
            'comparison_mode' => $this->comparisonMode,
            'blocks'          => array_values($this->canvas),
        ];

        // Parse schedule recipients
        $recipients = array_filter(array_map('trim', explode(',', $this->scheduleRecipients)));

        if ($this->editingReportId) {
            $report = SavedReport::findOrFail($this->editingReportId);
            $report->update([
                'name'                => trim($this->reportName),
                'description'         => trim($this->reportDescription) ?: null,
                'is_shared'           => $this->isShared,
                'config'              => $config,
                'schedule_cron'       => trim($this->scheduleCron) ?: null,
                'schedule_recipients' => empty($recipients) ? null : array_values($recipients),
            ]);
        } else {
            $report = SavedReport::create([
                'name'                => trim($this->reportName),
                'description'         => trim($this->reportDescription) ?: null,
                'created_by'          => auth()->id(),
                'is_shared'           => $this->isShared,
                'config'              => $config,
                'schedule_cron'       => trim($this->scheduleCron) ?: null,
                'schedule_recipients' => empty($recipients) ? null : array_values($recipients),
            ]);
        }

        session()->flash('success', 'Report saved.');
        $this->redirect(route('owner.reports.custom.view', $report->id));
    }

    public function getCatalogueProperty(): array
    {
        $registry = app(MetricRegistry::class);
        $all      = collect($registry->catalogue());

        if ($this->catalogueSearch) {
            $term = strtolower($this->catalogueSearch);
            $all  = $all->filter(fn ($m) =>
                str_contains(strtolower($m['label']), $term) ||
                str_contains(strtolower($m['description']), $term)
            );
        }

        if ($this->catalogueDomain !== 'all') {
            $all = $all->filter(fn ($m) => $m['domain'] === $this->catalogueDomain);
        }

        return $all->groupBy('domain')->toArray();
    }

    public function getAddedMetricIdsProperty(): array
    {
        return array_column($this->canvas, 'metric_id');
    }

    // Flat catalogue keyed by metric_id for easy viz_options lookup in blade
    public function getFlatCatalogueProperty(): array
    {
        return collect(app(MetricRegistry::class)->catalogue())
            ->keyBy('id')
            ->toArray();
    }

    public function getWarehousesProperty() { return Warehouse::orderBy('name')->get(); }
    public function getShopsProperty()      { return Shop::orderBy('name')->get(); }

    public function render()
    {
        return view('livewire.owner.reports.report-builder', [
            'catalogue'      => $this->catalogue,
            'flatCatalogue'  => $this->flatCatalogue,
            'addedMetricIds' => $this->addedMetricIds,
            'warehouses'     => $this->warehouses,
            'shops'          => $this->shops,
        ]);
    }
}
