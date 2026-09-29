<?php
namespace App\Livewire\Owner\Reports;

use App\Models\SavedReport;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Reports\MetricRegistry;
use App\Services\Reports\ReportContext;
use App\Services\Reports\ReportFormat;
use App\Services\Reports\ReportPeriod;
use App\Services\Reports\ReportRunner;
use App\Services\Reports\ReportSchedule;
use App\Services\Reports\ReportTemplates;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Build or edit a custom report: pick blocks from the catalogue, order
 * them, set each one up in a drawer, and see a live preview of every
 * block for the report's default period.
 *
 * Everything the browser sends is re-checked in sanitiseBlock() /
 * save(): canvas is a public array, so its contents can't be trusted.
 */
class ReportBuilder extends Component
{
    public const MAX_BLOCKS = 40;

    // Report
    public string  $reportName        = '';
    public string  $reportDescription = '';
    public bool    $isShared          = false;
    public string  $dateRange         = 'month';
    public ?string $dateFrom          = null;
    public ?string $dateTo            = null;
    public string  $locationFilter    = 'all';
    public string  $comparisonMode    = 'none';

    // Email schedule ('' frequency = off); see ReportSchedule
    public string  $scheduleFrequency  = '';
    public int     $scheduleDay        = 1;
    public string  $scheduleTime       = '08:00';
    public string  $scheduleFormat     = 'pdf';
    public string  $scheduleRecipients = '';
    /** The schedule as saved, to keep its "since" when only the format or recipients change */
    #[Locked]
    public ?array  $savedSchedule      = null;

    /** Ordered blocks */
    public array   $canvas = [];

    // Catalogue
    public string  $catalogueSearch = '';
    public string  $catalogueDomain = 'all';
    public bool    $showCatalogue   = false;   // phone bottom sheet

    // Block drawer: $edit is a copy of the selected block, written back by updatedEdit()
    public string  $selectedBlockId = '';
    public array   $edit = [];

    /** Set by wire:init so the builder paints before previews run */
    public bool    $previewsReady = false;

    // Locked: a tampered id must not let one owner overwrite another's report
    #[Locked]
    public ?int    $editingReportId = null;

    public function mount(?int $reportId = null): void
    {
        abort_unless(auth()->user()->isOwner() || auth()->user()->isAdmin(), 403);

        if ($reportId) {
            $report = SavedReport::findOrFail($reportId);
            abort_unless($report->created_by === auth()->id(), 403);

            $config = $report->resolvedConfig();
            $this->editingReportId    = $report->id;
            $this->reportName         = $report->name;
            $this->reportDescription  = $report->description ?? '';
            $this->isShared           = (bool) $report->is_shared;
            $this->dateRange          = array_key_exists($config['date_range'], ReportPeriod::PRESETS) ? $config['date_range'] : 'month';
            $this->dateFrom           = $config['date_from'];
            $this->dateTo             = $config['date_to'];
            $this->locationFilter     = ReportContext::normaliseLocation($config['location_filter']);
            $this->comparisonMode     = array_key_exists($config['comparison_mode'] ?? '', ReportPeriod::COMPARISONS) ? $config['comparison_mode'] : 'none';
            $this->canvas             = array_values(array_filter(array_map(fn ($b) => $this->sanitiseBlock($b), $config['blocks'])));
            $this->scheduleRecipients = implode(', ', $report->schedule_recipients ?? []);
            if ($s = ReportSchedule::fromArray($report->schedule ?? ReportSchedule::fromCron($report->schedule_cron))) {
                $this->scheduleFrequency = $s->frequency;
                $this->scheduleDay       = max(1, $s->day);
                $this->scheduleTime      = $s->time;
                $this->scheduleFormat    = $s->format;
                $this->savedSchedule     = $report->schedule ? $s->toArray() : null;
            }
        } elseif ($key = request()->query('template')) {
            $this->loadTemplate((string) $key);
        }
    }

    public function loadPreviews(): void
    {
        $this->previewsReady = true;
    }

    // ── Canvas ───────────────────────────────────────────────────────────

    public function addBlock(string $metricId): void
    {
        $meta = app(MetricRegistry::class)->find($metricId);
        if (! $meta) return;
        if (count($this->canvas) >= self::MAX_BLOCKS) {
            $this->toast('warning', 'A report can have up to ' . self::MAX_BLOCKS . ' blocks.');
            return;
        }

        $block = $this->sanitiseBlock([
            'id'        => $this->newId(),
            'metric_id' => $metricId,
            'title'     => $meta['label'],
            'viz'       => $meta['default_viz'],
            'width'     => $metricId === 'text_block' || $meta['default_viz'] !== 'kpi_card' ? 'full' : 'half',
        ]);
        $this->canvas[] = $block;
        $this->showCatalogue = false;

        if ($metricId === 'text_block') {
            $this->selectBlock($block['id']);   // nothing to show until it has text
        } else {
            $this->toast('success', "Added {$meta['label']}.");
        }
    }

    public function duplicateBlock(string $blockId): void
    {
        $i = $this->indexOf($blockId);
        if ($i === null || count($this->canvas) >= self::MAX_BLOCKS) return;

        $copy = array_merge($this->canvas[$i], ['id' => $this->newId(), 'title' => Str::limit($this->canvas[$i]['title'] . ' (copy)', 120, '')]);
        array_splice($this->canvas, $i + 1, 0, [$copy]);
    }

    public function removeBlock(string $blockId): void
    {
        $this->canvas = array_values(array_filter($this->canvas, fn ($b) => $b['id'] !== $blockId));
        if ($this->selectedBlockId === $blockId) {
            $this->closeBlock();
        }
    }

    public function moveBlock(string $blockId, int $step): void
    {
        $i = $this->indexOf($blockId);
        $j = $i === null ? null : $i + ($step < 0 ? -1 : 1);
        if ($i === null || $j < 0 || $j >= count($this->canvas)) return;

        [$this->canvas[$i], $this->canvas[$j]] = [$this->canvas[$j], $this->canvas[$i]];
    }

    /** From Sortable.js; unknown ids are ignored, missing ones kept at the end */
    public function reorderBlocks(array $orderedIds): void
    {
        $byId = collect($this->canvas)->keyBy('id');
        $ordered = collect($orderedIds)->map(fn ($id) => (string) $id)->unique()->filter(fn ($id) => $byId->has($id))
            ->map(fn ($id) => $byId[$id])->values();
        $rest = $byId->except($ordered->pluck('id'))->values();
        $this->canvas = $ordered->merge($rest)->all();
    }

    /** The canvas can be set directly from the browser: clean it before anything renders it */
    public function updatedCanvas(): void
    {
        $blocks = array_values(array_filter(array_map(
            fn ($b) => is_array($b) ? $this->sanitiseBlock($b, keepEmptyTitle: true) : null,
            (array) $this->canvas
        )));
        $this->canvas = array_slice(SavedReport::withUniqueBlockIds($blocks), 0, self::MAX_BLOCKS);
    }

    public function clearCanvas(): void
    {
        $this->canvas = [];
        $this->closeBlock();
    }

    // ── Block drawer ─────────────────────────────────────────────────────

    public function selectBlock(string $blockId): void
    {
        $i = $this->indexOf($blockId);
        if ($i === null) return;

        $b = $this->canvas[$i];
        $this->selectedBlockId = $blockId;
        $this->edit = [
            'title'              => $b['title'] ?? '',
            'viz'                => $b['viz'] ?? '',
            'width'              => $b['width'] ?? 'half',
            'content'            => $b['content'] ?? '',
            'sort_by'            => $b['block_options']['sort_by'] ?? '',
            'sort_direction'     => $b['block_options']['sort_direction'] ?? 'desc',
            'limit'              => (string) ($b['block_options']['limit'] ?? ''),
            'threshold_warning'  => isset($b['threshold_warning']) ? (string) $b['threshold_warning'] : '',
            'threshold_critical' => isset($b['threshold_critical']) ? (string) $b['threshold_critical'] : '',
            'date_range'         => $b['date_range_override'] ?? '',
            'date_from'          => $b['date_from_override'] ?? '',
            'date_to'            => $b['date_to_override'] ?? '',
            'location'           => $b['location_filter_override'] ?? '',
        ];
    }

    public function closeBlock(): void
    {
        $this->selectedBlockId = '';
        $this->edit = [];
    }

    /** Every change in the drawer is written straight back to the block */
    public function updatedEdit(): void
    {
        $i = $this->indexOf($this->selectedBlockId);
        if ($i === null) return;

        $e = $this->edit;
        $b = $this->canvas[$i];
        $b['title']   = (string) ($e['title'] ?? '');
        $meta = app(MetricRegistry::class)->find($b['metric_id']);
        if (in_array($e['viz'] ?? null, $meta['viz_options'] ?? [], true)) {
            $b['viz'] = $e['viz'];
        } else {
            $this->edit['viz'] = $b['viz'];   // refuse a display this metric doesn't offer
        }
        $b['width']   = (string) ($e['width'] ?? 'half');
        $b['content'] = (string) ($e['content'] ?? '');
        $b['block_options'] = [
            'sort_by'        => $e['sort_by'] ?? '',
            'sort_direction' => $e['sort_direction'] ?? 'desc',
            'limit'          => $e['limit'] ?? '',
        ];
        $b['threshold_warning']        = $e['threshold_warning'] ?? '';
        $b['threshold_critical']       = $e['threshold_critical'] ?? '';
        $b['date_range_override']      = $e['date_range'] ?? '';
        $b['date_from_override']       = $e['date_from'] ?? '';
        $b['date_to_override']         = $e['date_to'] ?? '';
        $b['location_filter_override'] = $e['location'] ?? '';

        $clean = $this->sanitiseBlock($b, keepEmptyTitle: true);
        if ($clean) {
            $this->canvas[$i] = $clean;
        }
    }

    // ── Templates ────────────────────────────────────────────────────────

    public function loadTemplate(string $key): void
    {
        $template = app(ReportTemplates::class)->get($key);
        if (! $template) return;

        $this->reportName        = $template['name'];
        $this->reportDescription = $template['description'] ?? '';
        $this->dateRange         = array_key_exists($template['date_range'] ?? '', ReportPeriod::PRESETS) ? $template['date_range'] : 'month';
        $this->locationFilter    = ReportContext::normaliseLocation($template['location_filter'] ?? 'all');
        $this->canvas            = [];

        foreach ($template['blocks'] as $def) {
            $meta = app(MetricRegistry::class)->find($def['metric_id']);
            if (! $meta) continue;
            $block = $this->sanitiseBlock(array_merge(['id' => $this->newId(), 'title' => $meta['label']], $def));
            if ($block) $this->canvas[] = $block;
        }
        $this->closeBlock();
    }

    // ── Save ─────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->reportName = trim($this->reportName);

        $this->validate([
            'reportName'        => 'required|string|min:2|max:120',
            'reportDescription' => 'nullable|string|max:500',
            'dateRange'         => 'required|in:' . implode(',', array_keys(ReportPeriod::PRESETS)),
            'dateFrom'          => 'nullable|required_if:dateRange,custom|date_format:Y-m-d',
            'dateTo'            => 'nullable|required_if:dateRange,custom|date_format:Y-m-d|after_or_equal:dateFrom',
            'comparisonMode'    => 'required|in:' . implode(',', array_keys(ReportPeriod::COMPARISONS)),
            'canvas'            => 'array|min:1|max:' . self::MAX_BLOCKS,
            'scheduleFrequency' => 'nullable|in:' . implode(',', array_keys(ReportSchedule::FREQUENCIES)),
            'scheduleDay'       => 'integer|min:1|max:' . ($this->scheduleFrequency === 'weekly' ? 7 : ReportSchedule::MAX_MONTH_DAY),
            'scheduleTime'      => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'scheduleFormat'    => 'in:' . implode(',', array_keys(ReportSchedule::FORMATS)),
        ], [
            'scheduleTime.regex'    => 'Pick a time.',
            'reportName.required'   => 'Give the report a name.',
            'reportName.min'        => 'Give the report a name.',
            'canvas.min'            => 'Add at least one block before saving.',
            'dateFrom.required_if'  => 'Pick the start date.',
            'dateTo.required_if'    => 'Pick the end date.',
            'dateTo.after_or_equal' => 'The end date is before the start date.',
        ]);

        $recipients = array_values(array_unique(array_filter(array_map(
            fn ($e) => mb_strtolower(trim($e)), preg_split('/[,;\s]+/', $this->scheduleRecipients)
        ))));
        foreach ($recipients as $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addError('scheduleRecipients', "{$email} isn't a valid email address.");
                return;
            }
        }
        if ($this->scheduleFrequency !== '' && $recipients === []) {
            $this->addError('scheduleRecipients', 'Add at least one email address, or turn the schedule off.');
            return;
        }
        if (count($recipients) > 10) {
            $this->addError('scheduleRecipients', 'Up to 10 email addresses.');
            return;
        }

        $blocks = SavedReport::withUniqueBlockIds(array_values(array_filter(array_map(fn ($b) => $this->sanitiseBlock((array) $b), $this->canvas))));
        if ($blocks === []) {
            $this->addError('canvas', 'Add at least one block before saving.');
            return;
        }

        $attributes = [
            'name'                => $this->reportName,
            'description'         => trim($this->reportDescription) ?: null,
            'is_shared'           => $this->isShared,
            'config'              => [
                'date_range'      => $this->dateRange,
                'date_from'       => $this->dateRange === 'custom' ? $this->dateFrom : null,
                'date_to'         => $this->dateRange === 'custom' ? $this->dateTo : null,
                'location_filter' => ReportContext::normaliseLocation($this->locationFilter),
                'comparison_mode' => $this->comparisonMode,
                'blocks'          => $blocks,
            ],
            'schedule'            => $this->buildSchedule(),
            'schedule_cron'       => null,   // replaced by `schedule`
            'schedule_recipients' => $this->scheduleFrequency === '' ? null : $recipients,
        ];

        if ($this->editingReportId) {
            $report = SavedReport::findOrFail($this->editingReportId);
            abort_unless($report->created_by === auth()->id(), 403);
            $report->update($attributes);
        } else {
            $report = SavedReport::create($attributes + ['created_by' => auth()->id()]);
        }

        session()->flash('success', 'Report saved.');
        $this->redirect(route('owner.reports.custom.view', $report->id), navigate: true);
    }

    // ── Schedule ─────────────────────────────────────────────────────────

    /**
     * The schedule to store. Changing when it runs starts it afresh (no
     * email for a time that has already passed); changing only the format
     * or recipients keeps the original start.
     */
    private function buildSchedule(): ?array
    {
        if ($this->scheduleFrequency === '') {
            return null;
        }

        $new = ReportSchedule::fromArray([
            'frequency' => $this->scheduleFrequency,
            'day'       => $this->scheduleDay,
            'time'      => $this->scheduleTime,
            'format'    => $this->scheduleFormat,
            'since'     => now()->utc()->toIso8601String(),
        ])->toArray();

        $old = $this->savedSchedule;
        if ($old && [$old['frequency'], $old['day'], $old['time']] === [$new['frequency'], $new['day'], $new['time']]) {
            $new['since'] = $old['since'];
        }

        return $new;
    }

    public function updatedScheduleFrequency(): void
    {
        if ($this->scheduleFrequency === 'weekly' && $this->scheduleDay > 7) {
            $this->scheduleDay = 1;
        }
    }

    /** "Mon 6 Oct at 08:00" for the picker */
    #[Computed]
    public function nextSend(): ?string
    {
        $s = ReportSchedule::fromArray([
            'frequency' => $this->scheduleFrequency, 'day' => $this->scheduleDay,
            'time' => $this->scheduleTime, 'format' => $this->scheduleFormat,
        ]);

        return $s?->nextOccurrence()->format('D j M \a\t H:i');
    }

    // ── Sanitising ───────────────────────────────────────────────────────

    /**
     * A block reduced to known keys with valid values, or null when its
     * metric doesn't exist. Settings that can't apply to the metric
     * (a period on a stock snapshot, a location on a company-wide figure,
     * thresholds on a table) are dropped.
     */
    private function sanitiseBlock(array $b, bool $keepEmptyTitle = false): ?array
    {
        $meta = app(MetricRegistry::class)->find((string) ($b['metric_id'] ?? ''));
        if (! $meta) return null;

        $id = (string) ($b['id'] ?? '');
        $title = Str::limit(trim((string) ($b['title'] ?? '')), 120, '');

        $out = [
            'id'        => preg_match('/^[A-Za-z0-9_]{1,60}$/', $id) ? $id : $this->newId(),
            'metric_id' => $meta['id'],
            'title'     => $title !== '' || $keepEmptyTitle ? $title : $meta['label'],
            'viz'       => in_array($b['viz'] ?? null, $meta['viz_options'], true) ? $b['viz'] : $meta['default_viz'],
            'width'     => in_array($b['width'] ?? null, ['half', 'full'], true) ? $b['width'] : 'half',
        ];

        if ($meta['id'] === 'text_block') {
            $out['width']   = 'full';
            $out['content'] = Str::limit((string) ($b['content'] ?? ''), 5000, '');
            return $out;
        }

        // Period override (only for figures that depend on the period)
        $range = (string) ($b['date_range_override'] ?? '');
        if ($meta['needs_dates'] && array_key_exists($range, ReportPeriod::PRESETS)) {
            $isDate = fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
            if ($range !== 'custom') {
                $out['date_range_override'] = $range;
            } elseif ($isDate($b['date_from_override'] ?? null) && $isDate($b['date_to_override'] ?? null)) {
                $out['date_range_override'] = 'custom';
                [$out['date_from_override'], $out['date_to_override']] = ReportPeriod::resolve('custom', $b['date_from_override'], $b['date_to_override']);
            } else {
                // custom picked but dates not filled in yet: keep the choice so the drawer shows the date fields
                $out['date_range_override'] = 'custom';
                $out['date_from_override']  = $isDate($b['date_from_override'] ?? null) ? $b['date_from_override'] : null;
                $out['date_to_override']    = $isDate($b['date_to_override'] ?? null) ? $b['date_to_override'] : null;
            }
        }

        // Location override
        $loc = (string) ($b['location_filter_override'] ?? '');
        if ($loc !== '' && $meta['locations'] !== 'none') {
            $loc = ReportContext::normaliseLocation($loc);
            if ($loc !== 'all' && ! ($meta['locations'] === 'shop' && str_starts_with($loc, 'warehouse:'))) {
                $out['location_filter_override'] = $loc;
            }
        }

        // Thresholds (summary cards only)
        if ($out['viz'] === 'kpi_card') {
            foreach (['threshold_warning', 'threshold_critical'] as $k) {
                if (isset($b[$k]) && is_numeric($b[$k])) {
                    $out[$k] = (float) $b[$k];
                }
            }
        }

        // Sort / limit (tables and charts)
        if ($out['viz'] !== 'kpi_card') {
            $o = (array) ($b['block_options'] ?? []);
            $opts = [];
            if (isset($o['sort_by']) && preg_match('/^[a-z0-9_]{1,60}$/', (string) $o['sort_by'])) {
                $opts['sort_by'] = $o['sort_by'];
                $opts['sort_direction'] = ($o['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
            }
            if (isset($o['limit']) && is_numeric($o['limit']) && (int) $o['limit'] >= 1) {
                $opts['limit'] = min((int) $o['limit'], 100);
            }
            if ($opts) {
                $out['block_options'] = $opts;
            }
        }

        return $out;
    }

    private function newId(): string
    {
        return 'b' . strtolower((string) Str::ulid());
    }

    private function indexOf(string $blockId): ?int
    {
        foreach ($this->canvas as $i => $b) {
            if (($b['id'] ?? null) === $blockId) return $i;
        }
        return null;
    }

    private function toast(string $type, string $message): void
    {
        $this->dispatch('notification', ['type' => $type, 'message' => $message]);
    }

    // ── View data ────────────────────────────────────────────────────────

    /** Report-level [from, to] used for previews */
    private function previewDates(): array
    {
        $isDate = fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
        if ($this->dateRange === 'custom' && ! ($isDate($this->dateFrom) && $isDate($this->dateTo))) {
            return ReportPeriod::resolve('month');
        }
        return ReportPeriod::resolve($this->dateRange, $this->dateFrom, $this->dateTo);
    }

    /**
     * One small result per block for the canvas cards, each cached by
     * its own settings, so editing one block re-runs only that block.
     */
    #[Computed]
    public function previews(): array
    {
        if (! $this->previewsReady) return [];

        [$from, $to] = $this->previewDates();
        $location = ReportContext::normaliseLocation($this->locationFilter);
        $registry = app(MetricRegistry::class);
        $runner   = app(ReportRunner::class);
        $ttl      = $to >= business_today()->toDateString() ? 300 : 3600;

        $out = [];
        foreach ($this->canvas as $block) {
            $metric = $registry->metric($block['metric_id'] ?? '');
            if (! $metric) continue;

            $settings = array_diff_key($block, array_flip(['id', 'title', 'width', 'content']));
            $key = 'custom_report_preview:' . md5(json_encode([$settings, $from, $to, $location]));

            $out[$block['id']] = Cache::remember($key, $ttl, function () use ($runner, $metric, $block, $from, $to, $location) {
                [, $r] = $runner->runBlock($metric, $block, $from, $to, $location);
                $h = $r->headline;
                return [
                    'error'    => $r->error,
                    'headline' => $h ? ReportFormat::withUnit($h['value'], $h['type']) : null,
                    'label'    => $h['label'] ?? '',
                    'rows'     => count($r->rows),
                    'columns'  => array_map(fn ($c) => ['key' => $c['key'], 'label' => $c['label']], $r->columns),
                    'notes'    => $r->notes,
                ];
            });
        }

        return $out;
    }

    #[Computed]
    public function catalogue(): array
    {
        $all = collect(app(MetricRegistry::class)->catalogue());

        if ($term = mb_strtolower(trim($this->catalogueSearch))) {
            $all = $all->filter(fn ($m) => str_contains(mb_strtolower($m['label']), $term) || str_contains(mb_strtolower($m['description']), $term));
        }
        if ($this->catalogueDomain !== 'all') {
            $all = $all->filter(fn ($m) => $m['domain'] === $this->catalogueDomain);
        }

        return $all->groupBy('domain')->toArray();
    }

    public function render()
    {
        $selected = null;
        if ($this->selectedBlockId !== '' && ($i = $this->indexOf($this->selectedBlockId)) !== null) {
            $selected = $this->canvas[$i];
        }

        return view('livewire.owner.reports.report-builder', [
            'catalogueGroups' => $this->catalogue,
            'flat'            => collect(app(MetricRegistry::class)->catalogue())->keyBy('id')->all(),
            'domains'         => collect(app(MetricRegistry::class)->catalogue())->pluck('domain')->unique()->values()->all(),
            'usedIds'         => array_count_values(array_column($this->canvas, 'metric_id')),
            'templates'       => app(ReportTemplates::class)->list(),
            'shops'           => Shop::orderBy('name')->pluck('name', 'id'),
            'warehouses'      => Warehouse::orderBy('name')->pluck('name', 'id'),
            'selected'        => $selected,
            'previewPeriod'   => ReportPeriod::label(...$this->previewDates()),
        ]);
    }
}
