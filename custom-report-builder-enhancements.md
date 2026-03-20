# Custom Report Builder — Feature Enhancements

> Read this entire file before writing a single line of code.
> Complete every step in written order. Do not skip ahead.
> This file assumes `custom-report-builder.md` has already been implemented.
> Verify the base builder exists before starting:
>   - `app/Models/SavedReport.php`
>   - `app/Services/Reports/MetricRegistry.php`
>   - `app/Services/Reports/ReportRunner.php`
>   - `app/Livewire/Owner/Reports/ReportBuilder.php`
>   - `app/Livewire/Owner/Reports/ReportViewer.php`
> If any are missing, implement `custom-report-builder.md` first.

---

## Files to read before starting

```
app/Models/SavedReport.php
app/Services/Reports/MetricRegistry.php
app/Services/Reports/ReportRunner.php
app/Livewire/Owner/Reports/ReportBuilder.php
app/Livewire/Owner/Reports/ReportViewer.php
resources/views/livewire/owner/reports/report-builder.blade.php
resources/views/livewire/owner/reports/report-viewer.blade.php
resources/views/livewire/owner/reports/report-library.blade.php
bootstrap/app.php
resources/views/livewire/layout/sidebar.blade.php
```

## Design tokens — never use hardcoded colours

```
var(--surface)  var(--surface2)  var(--surface3)  var(--border)
var(--text)  var(--text-sub)  var(--text-dim)
var(--accent)  var(--accent-dim)
var(--success)  var(--success-glow)
var(--warn)  var(--warn-glow)
var(--amber)  var(--amber-dim)
var(--danger)  var(--danger-glow)
var(--red)  var(--red-dim)
var(--violet)  var(--violet-dim)
var(--r)  var(--rsm)
```

---

## PHASE 1 — Critical Features (implement these first)

---

## Step 1 — Schema additions

Run `php artisan make:migration enhance_saved_reports_and_add_report_tables`
and fill it with this content:

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── 1. Add caching + scheduling + pinning to saved_reports ───────────
        Schema::table('saved_reports', function (Blueprint $table) {
            $table->jsonb('last_results')->nullable()->after('config');
            $table->timestamp('results_cached_at')->nullable()->after('last_results');
            $table->timestamp('results_stale_at')->nullable()->after('results_cached_at');
            $table->string('schedule_cron', 100)->nullable()->after('results_stale_at');
            $table->jsonb('schedule_recipients')->nullable()->after('schedule_cron');
            $table->timestamp('last_scheduled_run_at')->nullable()->after('schedule_recipients');
            $table->boolean('pinned_to_dashboard')->default(false)->after('last_scheduled_run_at');
            $table->unsignedTinyInteger('dashboard_position')->nullable()->after('pinned_to_dashboard');
        });

        // ── 2. report_run_history ─────────────────────────────────────────────
        Schema::create('report_run_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('saved_reports')->cascadeOnDelete();
            $table->foreignId('run_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('run_at');
            $table->jsonb('config_snapshot');          // snapshot of config at run time
            $table->jsonb('results')->nullable();      // full results array
            $table->unsignedInteger('duration_ms')->default(0);
            $table->boolean('was_scheduled')->default(false);
            $table->timestamps();

            $table->index(['report_id', 'run_at']);
        });

        // ── 3. report_annotations ────────────────────────────────────────────
        Schema::create('report_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('saved_reports')->cascadeOnDelete();
            $table->foreignId('run_history_id')->nullable()->constrained('report_run_history')->nullOnDelete();
            $table->string('block_id', 60)->nullable(); // null = report-level annotation
            $table->text('note');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['report_id', 'run_history_id']);
        });

        // ── 4. report_view_log ────────────────────────────────────────────────
        Schema::create('report_view_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('saved_reports')->cascadeOnDelete();
            $table->foreignId('viewed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('viewed_at');
            $table->boolean('was_run')->default(false);

            $table->index(['report_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_view_log');
        Schema::dropIfExists('report_annotations');
        Schema::dropIfExists('report_run_history');
        Schema::table('saved_reports', function (Blueprint $table) {
            $table->dropColumn([
                'last_results', 'results_cached_at', 'results_stale_at',
                'schedule_cron', 'schedule_recipients', 'last_scheduled_run_at',
                'pinned_to_dashboard', 'dashboard_position',
            ]);
        });
    }
};
```

```bash
php artisan migrate
```

---

## Step 2 — Update SavedReport model

Open `app/Models/SavedReport.php`. Add the new columns to `$fillable` and `$casts`,
and add two new helper methods:

```php
// Add to $fillable:
'last_results', 'results_cached_at', 'results_stale_at',
'schedule_cron', 'schedule_recipients', 'last_scheduled_run_at',
'pinned_to_dashboard', 'dashboard_position',

// Add to $casts:
'last_results'          => 'array',
'results_cached_at'     => 'datetime',
'results_stale_at'      => 'datetime',
'schedule_recipients'   => 'array',
'last_scheduled_run_at' => 'datetime',
'pinned_to_dashboard'   => 'boolean',

// Add these methods:
public function hasFreshCache(): bool
{
    return $this->results_stale_at !== null
        && $this->results_stale_at->isFuture()
        && $this->last_results !== null;
}

public function cacheResults(array $results, int $ttlHours = 4): void
{
    $this->update([
        'last_results'      => $results,
        'results_cached_at' => now(),
        'results_stale_at'  => now()->addHours($ttlHours),
        'last_run_at'       => now(),
        'run_count'         => $this->run_count + 1,
    ]);
}
```

Add two new relationships at the bottom of the model:

```php
public function runHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(ReportRunHistory::class, 'report_id')
                ->orderByDesc('run_at');
}

public function annotations(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(ReportAnnotation::class, 'report_id')
                ->orderByDesc('created_at');
}
```

---

## Step 3 — Create new models

### ReportRunHistory

Create `app/Models/ReportRunHistory.php`:

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportRunHistory extends Model
{
    protected $table = 'report_run_history';

    protected $fillable = [
        'report_id', 'run_by', 'run_at', 'config_snapshot',
        'results', 'duration_ms', 'was_scheduled',
    ];

    protected $casts = [
        'config_snapshot' => 'array',
        'results'         => 'array',
        'run_at'          => 'datetime',
        'was_scheduled'   => 'boolean',
        'duration_ms'     => 'integer',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(SavedReport::class, 'report_id');
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'run_by');
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(ReportAnnotation::class, 'run_history_id');
    }
}
```

### ReportAnnotation

Create `app/Models/ReportAnnotation.php`:

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportAnnotation extends Model
{
    protected $fillable = [
        'report_id', 'run_history_id', 'block_id', 'note', 'created_by',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(SavedReport::class, 'report_id');
    }

    public function runHistory(): BelongsTo
    {
        return $this->belongsTo(ReportRunHistory::class, 'run_history_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
```

---

## Step 4 — Update ReportRunner to write history and cache

Open `app/Services/Reports/ReportRunner.php`. Update the `run()` method:

```php
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
        $history = \App\Models\ReportRunHistory::create([
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
```

Add these two new private methods to `ReportRunner`:

```php
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
        'prior_year'   => [
            $from->copy()->subYear()->toDateString(),
            $to->copy()->subYear()->toDateString(),
        ],
        default        => [  // prior_period
            $from->copy()->subDays($days)->toDateString(),
            $to->copy()->subDays($days)->toDateString(),
        ],
    };
}
```

---

## Step 5 — Export: CSV and PDF

### ExportReportAction service

Create `app/Services/Reports/ExportReportAction.php`:

```php
<?php
namespace App\Services\Reports;

use App\Models\SavedReport;

class ExportReportAction
{
    /**
     * Generate CSV from run results.
     * Returns the CSV string content.
     */
    public function toCsv(SavedReport $report, array $results): string
    {
        $lines = [];
        $config = $report->resolvedConfig();
        [$dateFrom, $dateTo] = app(ReportRunner::class)->resolveDates($config);

        // Header
        $lines[] = '"' . $report->name . '"';
        $lines[] = '"Period: ' . $dateFrom . ' to ' . $dateTo . '"';
        $lines[] = '"Generated: ' . now()->format('d M Y H:i') . '"';
        $lines[] = '';

        foreach ($results as $blockResult) {
            if (isset($blockResult['data']['error'])) continue;
            $block = $blockResult['block'];
            $meta  = $blockResult['meta'];
            $data  = $blockResult['data'];
            $viz   = $block['viz'] ?? $meta['default_viz'];
            $title = $block['title'] ?? $meta['label'];

            $lines[] = '"' . strtoupper($title) . '"';

            if ($viz === 'kpi_card') {
                // KPI blocks: one row per key-value
                foreach ($data as $key => $value) {
                    if (str_starts_with($key, '_')) continue; // skip internal keys
                    if (is_numeric($value) || is_string($value)) {
                        $lines[] = '"' . $key . '","' . $value . '"';
                    }
                }
            } elseif ($viz === 'text') {
                $lines[] = '"' . ($block['content'] ?? '') . '"';
            } else {
                // Table/chart blocks: rows
                $rows = is_array($data) && isset($data[0]) ? $data : [];
                if (!empty($rows)) {
                    // Header row
                    $lines[] = implode(',', array_map(fn($k) => '"' . $k . '"', array_keys($rows[0])));
                    foreach ($rows as $row) {
                        $lines[] = implode(',', array_map(fn($v) =>
                            '"' . str_replace('"', '""', (string)($v ?? '')) . '"'
                        , $row));
                    }
                }
            }
            $lines[] = '';
        }

        return implode("\r\n", $lines);
    }

    /**
     * Generate a simple HTML string suitable for browser print-to-PDF.
     * Returns full HTML document.
     */
    public function toPrintHtml(SavedReport $report, array $results): string
    {
        $config = $report->resolvedConfig();
        $runner = app(ReportRunner::class);
        [$dateFrom, $dateTo] = $runner->resolveDates($config);

        ob_start();
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        echo '<title>' . htmlspecialchars($report->name) . '</title>';
        echo '<style>
            body{font-family:sans-serif;font-size:13px;color:#111;padding:24px;max-width:960px;margin:0 auto}
            h1{font-size:20px;margin:0 0 4px}
            .meta{font-size:12px;color:#666;margin-bottom:24px}
            .block{margin-bottom:24px;page-break-inside:avoid}
            .block-title{font-size:14px;font-weight:700;text-transform:uppercase;
                         letter-spacing:.5px;color:#333;border-bottom:2px solid #111;
                         padding-bottom:4px;margin-bottom:12px}
            table{width:100%;border-collapse:collapse;font-size:12px}
            th{background:#f0f0f0;text-align:left;padding:6px 10px;font-weight:700;border:1px solid #ddd}
            td{padding:5px 10px;border:1px solid #ddd}
            tr:nth-child(even) td{background:#fafafa}
            .kpi{font-size:28px;font-weight:800;color:#111}
            .kpi-label{font-size:11px;text-transform:uppercase;color:#666;letter-spacing:.6px}
            .text-block{background:#f9f9f9;border-left:3px solid #ccc;padding:12px 16px;
                        font-size:13px;line-height:1.6;white-space:pre-wrap}
            @media print{.no-print{display:none}}
        </style></head><body>';

        echo '<h1>' . htmlspecialchars($report->name) . '</h1>';
        echo '<div class="meta">Period: ' . $dateFrom . ' – ' . $dateTo;
        echo ' &nbsp;·&nbsp; Generated: ' . now()->format('d M Y H:i');
        if ($report->description) {
            echo ' &nbsp;·&nbsp; ' . htmlspecialchars($report->description);
        }
        echo '</div>';

        foreach ($results as $blockResult) {
            $block = $blockResult['block'];
            $meta  = $blockResult['meta'];
            $data  = $blockResult['data'];
            $viz   = $block['viz'] ?? $meta['default_viz'] ?? 'kpi_card';
            $title = $block['title'] ?? $meta['label'] ?? '';

            if (isset($data['error'])) {
                echo '<div class="block"><div class="block-title">' . htmlspecialchars($title) . '</div>';
                echo '<p style="color:red">Error: ' . htmlspecialchars($data['error']) . '</p></div>';
                continue;
            }

            // Check show_if_nonzero
            if (!empty($block['show_if_nonzero'])) {
                $firstNum = collect($data)->first(fn($v) => is_numeric($v) && !str_starts_with(array_search($v, $data) ?: '', '_'));
                if ($firstNum == 0 || $firstNum === null) continue;
            }

            if ($viz === 'text') {
                echo '<div class="block">';
                if ($title) echo '<div class="block-title">' . htmlspecialchars($title) . '</div>';
                echo '<div class="text-block">' . htmlspecialchars($block['content'] ?? '') . '</div>';
                echo '</div>';
                continue;
            }

            echo '<div class="block"><div class="block-title">' . htmlspecialchars($title) . '</div>';

            if ($viz === 'kpi_card') {
                // Show first numeric key as headline
                $mainValue = null;
                $mainKey   = null;
                foreach ($data as $k => $v) {
                    if (str_starts_with($k, '_')) continue;
                    if (is_numeric($v)) { $mainKey = $k; $mainValue = $v; break; }
                }
                if ($mainValue !== null) {
                    echo '<div class="kpi">' . number_format($mainValue) . '</div>';
                    echo '<div class="kpi-label">' . htmlspecialchars(str_replace('_', ' ', $mainKey)) . '</div>';
                }
                // Sub values as small table
                $others = array_filter($data, fn($k) => !str_starts_with($k, '_') && $k !== $mainKey, ARRAY_FILTER_USE_KEY);
                if (!empty($others)) {
                    echo '<table style="margin-top:8px"><tbody>';
                    foreach ($others as $k => $v) {
                        if (is_scalar($v)) {
                            echo '<tr><td>' . htmlspecialchars(str_replace('_', ' ', $k)) . '</td><td>' . htmlspecialchars((string)$v) . '</td></tr>';
                        }
                    }
                    echo '</tbody></table>';
                }
            } elseif (is_array($data) && isset($data[0]) && is_array($data[0])) {
                echo '<table><thead><tr>';
                foreach (array_keys($data[0]) as $col) {
                    if (str_starts_with($col, '_')) continue;
                    echo '<th>' . htmlspecialchars(str_replace('_', ' ', $col)) . '</th>';
                }
                echo '</tr></thead><tbody>';
                foreach ($data as $row) {
                    echo '<tr>';
                    foreach ($row as $col => $val) {
                        if (str_starts_with($col, '_')) continue;
                        echo '<td>' . htmlspecialchars(is_array($val) ? json_encode($val) : (string)($val ?? '')) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }

            echo '</div>';
        }

        echo '</body></html>';
        return ob_get_clean();
    }
}
```

### Add export actions to ReportViewer

Open `app/Livewire/Owner/Reports/ReportViewer.php`.

Add a `run_history_id` property to track the current run. Then add these two methods:

```php
public int $currentRunHistoryId = 0;

public function run(): void
{
    $report = SavedReport::findOrFail($this->reportId);

    // Log view
    \App\Models\ReportViewLog::create([  // see Note below
        'report_id' => $this->reportId,
        'viewed_by' => auth()->id(),
        'viewed_at' => now(),
        'was_run'   => true,
    ]);

    $this->isRunning = true;
    $runner = app(\App\Services\Reports\ReportRunner::class);
    $this->results  = $runner->run($report->resolvedConfig(), $this->reportId, true);
    $this->hasRun   = true;
    $this->isRunning = false;

    // Track latest run history id
    $latest = $report->runHistory()->first();
    $this->currentRunHistoryId = $latest?->id ?? 0;
}

public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
{
    $report  = SavedReport::findOrFail($this->reportId);
    $results = $this->hasRun ? $this->results : ($report->last_results ?? []);
    $csv = app(\App\Services\Reports\ExportReportAction::class)->toCsv($report, $results);

    return response()->streamDownload(function () use ($csv) {
        echo $csv;
    }, str($report->name)->slug() . '-' . now()->format('Y-m-d') . '.csv', [
        'Content-Type' => 'text/csv',
    ]);
}

public function exportPrint(): void
{
    // Dispatch a browser event to open the print HTML in a new tab.
    // The viewer blade will handle this via a Livewire event.
    $this->dispatch('open-print-view', reportId: $this->reportId);
}
```

**Note on ReportViewLog:** Create a minimal model `app/Models/ReportViewLog.php`:

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReportViewLog extends Model
{
    protected $table = 'report_view_log';
    public $timestamps = false;
    protected $fillable = ['report_id', 'viewed_by', 'viewed_at', 'was_run'];
    protected $casts = ['viewed_at' => 'datetime', 'was_run' => 'boolean'];
}
```

### Add print route

Open `routes/web.php`. Inside the owner reports route group, add:

```php
Route::get('/owner/reports/custom/{report}/print', function (\App\Models\SavedReport $report) {
    $user = auth()->user();
    if ($report->created_by !== $user->id && !$report->is_shared) abort(403);
    $results = $report->last_results ?? [];
    $html = app(\App\Services\Reports\ExportReportAction::class)->toPrintHtml($report, $results);
    return response($html)->header('Content-Type', 'text/html');
})->name('owner.reports.custom.print')->middleware('role:owner');
```

### Update viewer blade

Open `resources/views/livewire/owner/reports/report-viewer.blade.php`.

In the header area, add export buttons next to "Run Report":

```blade
{{-- Export buttons (only show if has results) --}}
@if($hasRun || $report->last_results)
<div style="display:flex;align-items:center;gap:8px">
    <button wire:click="exportCsv"
            style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
                   background:var(--surface);border:1px solid var(--border);
                   border-radius:var(--rsm);font-size:13px;font-weight:600;
                   color:var(--text);cursor:pointer">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
        </svg>
        CSV
    </button>
    <a href="{{ route('owner.reports.custom.print', $report->id) }}" target="_blank"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
              background:var(--surface);border:1px solid var(--border);
              border-radius:var(--rsm);font-size:13px;font-weight:600;
              color:var(--text);text-decoration:none">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
            <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Print / PDF
    </a>
</div>
@endif
```

---

## Step 6 — Cached Results Display

Update `ReportViewer::render()` to load cached results on mount:

```php
public function mount(int $reportId): void
{
    $this->reportId = $reportId;

    // Log page view (not a run)
    \App\Models\ReportViewLog::create([
        'report_id' => $reportId,
        'viewed_by' => auth()->id(),
        'viewed_at' => now(),
        'was_run'   => false,
    ]);

    // Show cached results immediately if available
    $report = \App\Models\SavedReport::find($reportId);
    if ($report && $report->hasFreshCache()) {
        $this->results = $report->last_results;
        $this->hasRun  = true;
    }
}
```

In the viewer blade, add an "As of" badge near the run button:

```blade
@if($report->results_cached_at && $hasRun)
<div style="font-size:12px;color:var(--text-sub);display:flex;align-items:center;gap:4px">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
    </svg>
    Data as of {{ $report->results_cached_at->diffForHumans() }}
    @if($report->results_stale_at?->isPast())
        <span style="color:var(--amber)">(stale)</span>
    @endif
</div>
@endif
```

---

## Step 7 — Per-Block Filter Overrides

### Builder changes

Open `app/Livewire/Owner/Reports/ReportBuilder.php`.

Add a method to update block overrides:

```php
public function updateBlockOverride(string $blockId, string $field, mixed $value): void
{
    $this->canvas = collect($this->canvas)->map(function ($b) use ($blockId, $field, $value) {
        if ($b['id'] !== $blockId) return $b;
        return array_merge($b, [$field => $value ?: null]);
    })->toArray();
}
```

In the builder blade, on each canvas block card, add a collapsible "Override filters" section
that appears when the user clicks a small "⚙ Override" toggle:

```blade
{{-- Per-block override section --}}
<div x-data="{ open: !!block.location_filter_override || !!block.date_range_override }">
    <button @click="open = !open"
            style="font-size:11px;color:var(--text-sub);background:none;border:none;
                   cursor:pointer;padding:4px 0;display:flex;align-items:center;gap:4px">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 105 19.07M22 2L17 7"/>
        </svg>
        Override filters
    </button>

    <div x-show="open" style="margin-top:8px;padding:10px;background:var(--surface2);
                               border-radius:var(--rsm);border:1px solid var(--border)">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <div>
                <label style="font-size:11px;color:var(--text-sub)">Location</label>
                <select wire:change="updateBlockOverride('{{ $block['id'] }}', 'location_filter_override', $event.target.value)"
                        style="width:100%;padding:4px 8px;background:var(--surface);border:1px solid var(--border);
                               border-radius:var(--rsm);font-size:12px;color:var(--text)">
                    <option value="">Same as report</option>
                    <option value="all" @selected(($block['location_filter_override'] ?? '') === 'all')>All</option>
                    <option value="warehouses" @selected(($block['location_filter_override'] ?? '') === 'warehouses')>All Warehouses</option>
                    <option value="shops" @selected(($block['location_filter_override'] ?? '') === 'shops')>All Shops</option>
                    @foreach($warehouses as $wh)
                        <option value="warehouse:{{ $wh->id }}"
                            @selected(($block['location_filter_override'] ?? '') === 'warehouse:'.$wh->id)>
                            {{ $wh->name }}
                        </option>
                    @endforeach
                    @foreach($shops as $sh)
                        <option value="shop:{{ $sh->id }}"
                            @selected(($block['location_filter_override'] ?? '') === 'shop:'.$sh->id)>
                            {{ $sh->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:var(--text-sub)">Date range</label>
                <select wire:change="updateBlockOverride('{{ $block['id'] }}', 'date_range_override', $event.target.value)"
                        style="width:100%;padding:4px 8px;background:var(--surface);border:1px solid var(--border);
                               border-radius:var(--rsm);font-size:12px;color:var(--text)">
                    <option value="">Same as report</option>
                    <option value="today" @selected(($block['date_range_override'] ?? '') === 'today')>Today</option>
                    <option value="week" @selected(($block['date_range_override'] ?? '') === 'week')>This week</option>
                    <option value="month" @selected(($block['date_range_override'] ?? '') === 'month')>This month</option>
                    <option value="quarter" @selected(($block['date_range_override'] ?? '') === 'quarter')>This quarter</option>
                    <option value="year" @selected(($block['date_range_override'] ?? '') === 'year')>This year</option>
                </select>
            </div>
        </div>
    </div>
</div>
```

---

## Step 8 — Report Templates

Create `app/Services/Reports/ReportTemplates.php`:

```php
<?php
namespace App\Services\Reports;

class ReportTemplates
{
    public static function all(): array
    {
        return [
            [
                'id'          => 'daily_ops',
                'name'        => 'Daily Operations Scorecard',
                'description' => 'Quick morning check: revenue, transactions, stock alerts, pending work',
                'icon'        => '📊',
                'config' => [
                    'date_range' => 'today', 'location_filter' => 'all', 'blocks' => [
                        ['id' => 't1', 'metric_id' => 'sales_revenue',       'title' => "Today's Revenue",       'width' => 'half', 'viz' => 'kpi_card', 'position' => 0],
                        ['id' => 't2', 'metric_id' => 'sales_transaction_count', 'title' => 'Transactions',      'width' => 'half', 'viz' => 'kpi_card', 'position' => 1],
                        ['id' => 't3', 'metric_id' => 'ops_low_stock_count', 'title' => 'Low Stock Products',    'width' => 'half', 'viz' => 'kpi_card', 'position' => 2],
                        ['id' => 't4', 'metric_id' => 'transfers_kpis',      'title' => 'Transfer Performance',  'width' => 'half', 'viz' => 'kpi_card', 'position' => 3],
                        ['id' => 't5', 'metric_id' => 'ops_damaged_pending', 'title' => 'Damaged — No Decision', 'width' => 'half', 'viz' => 'kpi_card', 'position' => 4, 'show_if_nonzero' => true],
                    ],
                ],
            ],
            [
                'id'          => 'monthly_financial',
                'name'        => 'Monthly Financial Summary',
                'description' => 'Revenue, margin, inventory value, and shrinkage for the month',
                'icon'        => '💰',
                'config' => [
                    'date_range' => 'month', 'location_filter' => 'all',
                    'comparison_mode' => 'prior_period',
                    'blocks' => [
                        ['id' => 't1', 'metric_id' => 'sales_revenue',             'title' => 'Total Revenue',          'width' => 'half', 'viz' => 'kpi_card', 'position' => 0],
                        ['id' => 't2', 'metric_id' => 'sales_gross_profit',        'title' => 'Gross Profit',           'width' => 'half', 'viz' => 'kpi_card', 'position' => 1],
                        ['id' => 't3', 'metric_id' => 'inventory_cost_value',      'title' => 'Stock at Cost',          'width' => 'half', 'viz' => 'kpi_card', 'position' => 2],
                        ['id' => 't4', 'metric_id' => 'loss_shrinkage',            'title' => 'Shrinkage Rate',         'width' => 'half', 'viz' => 'kpi_card', 'position' => 3],
                        ['id' => 't5', 'metric_id' => 'sales_top_products',        'title' => 'Top 10 Products',        'width' => 'full', 'viz' => 'table',    'position' => 4, 'block_options' => ['limit' => 10]],
                    ],
                ],
            ],
            [
                'id'          => 'shop_comparison',
                'name'        => 'Shop Performance Comparison',
                'description' => 'Revenue, returns, and basket size across all shops',
                'icon'        => '🏪',
                'config' => [
                    'date_range' => 'month', 'location_filter' => 'all', 'blocks' => [
                        ['id' => 't1', 'metric_id' => 'sales_by_shop',       'title' => 'Revenue by Shop',    'width' => 'full', 'viz' => 'bar_chart', 'position' => 0],
                        ['id' => 't2', 'metric_id' => 'loss_return_rate',    'title' => 'Return Rate',        'width' => 'half', 'viz' => 'kpi_card',  'position' => 1],
                        ['id' => 't3', 'metric_id' => 'sales_avg_basket',    'title' => 'Avg Basket Value',   'width' => 'half', 'viz' => 'kpi_card',  'position' => 2],
                        ['id' => 't4', 'metric_id' => 'sales_payment_methods', 'title' => 'Payment Methods', 'width' => 'full', 'viz' => 'table',     'position' => 3],
                    ],
                ],
            ],
            [
                'id'          => 'inventory_health',
                'name'        => 'Inventory Health Check',
                'description' => 'Fill rate, ABC classification, aging, dead stock, and expiry',
                'icon'        => '📦',
                'config' => [
                    'date_range' => 'month', 'location_filter' => 'all', 'blocks' => [
                        ['id' => 't1', 'metric_id' => 'inventory_fill_rate',   'title' => 'Portfolio Fill Rate',    'width' => 'half', 'viz' => 'kpi_card', 'position' => 0],
                        ['id' => 't2', 'metric_id' => 'inventory_dead_stock',  'title' => 'Dead Stock',             'width' => 'half', 'viz' => 'kpi_card', 'position' => 1],
                        ['id' => 't3', 'metric_id' => 'inventory_abc_summary', 'title' => 'ABC Classification',    'width' => 'full', 'viz' => 'table',    'position' => 2],
                        ['id' => 't4', 'metric_id' => 'inventory_aging',       'title' => 'Stock Aging',            'width' => 'full', 'viz' => 'table',    'position' => 3],
                    ],
                ],
            ],
            [
                'id'          => 'weekly_transfers',
                'name'        => 'Weekly Transfer Review',
                'description' => 'Transfer KPIs, discrepancies, and volume by route',
                'icon'        => '🚚',
                'config' => [
                    'date_range' => 'week', 'location_filter' => 'all', 'blocks' => [
                        ['id' => 't1', 'metric_id' => 'transfers_kpis',          'title' => 'Transfer Performance', 'width' => 'full', 'viz' => 'kpi_card', 'position' => 0],
                        ['id' => 't2', 'metric_id' => 'transfers_discrepancies', 'title' => 'Discrepancies',       'width' => 'half', 'viz' => 'kpi_card', 'position' => 1, 'show_if_nonzero' => true],
                        ['id' => 't3', 'metric_id' => 'transfers_routes',        'title' => 'Volume by Route',     'width' => 'full', 'viz' => 'table',    'position' => 2],
                    ],
                ],
            ],
            [
                'id'          => 'replenishment',
                'name'        => 'Stock Replenishment Brief',
                'description' => 'Products running low — sorted by urgency',
                'icon'        => '🔄',
                'config' => [
                    'date_range' => 'month', 'location_filter' => 'all', 'blocks' => [
                        ['id' => 't1', 'metric_id' => 'replenishment_critical',     'title' => 'Critical (≤7 days)',    'width' => 'half', 'viz' => 'kpi_card', 'position' => 0],
                        ['id' => 't2', 'metric_id' => 'ops_low_stock_count',        'title' => 'Below Threshold',       'width' => 'half', 'viz' => 'kpi_card', 'position' => 1],
                        ['id' => 't3', 'metric_id' => 'replenishment_days_on_hand', 'title' => 'Days on Hand',         'width' => 'full', 'viz' => 'table',    'position' => 2],
                    ],
                ],
            ],
        ];
    }

    public static function find(string $id): ?array
    {
        return collect(static::all())->firstWhere('id', $id);
    }
}
```

### Wire templates into the builder

Open `app/Livewire/Owner/Reports/ReportBuilder.php`. Add:

```php
public function loadTemplate(string $templateId): void
{
    $template = \App\Services\Reports\ReportTemplates::find($templateId);
    if (!$template) return;

    $c = $template['config'];
    $this->dateRange      = $c['date_range'] ?? 'month';
    $this->locationFilter = $c['location_filter'] ?? 'all';
    $this->canvas         = $c['blocks'] ?? [];
    if (!$this->reportName) {
        $this->reportName = $template['name'];
    }
    if (!$this->reportDescription && !empty($template['description'])) {
        $this->reportDescription = $template['description'];
    }
}
```

In the builder blade, add a "Start from template" row **above** the two-panel builder.
Only show it when `$canvas` is empty:

```blade
@if(empty($canvas) && !$editingReportId)
<div style="margin-bottom:20px">
    <div class="section-label">Start from a template</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:10px">
        @foreach(\App\Services\Reports\ReportTemplates::all() as $tmpl)
        <button wire:click="loadTemplate('{{ $tmpl['id'] }}')"
                style="text-align:left;padding:14px 16px;background:var(--surface);
                       border:1px solid var(--border);border-radius:var(--r);cursor:pointer;
                       transition:border-color .15s"
                onmouseover="this.style.borderColor='var(--accent)'"
                onmouseout="this.style.borderColor='var(--border)'">
            <div style="font-size:22px;margin-bottom:6px">{{ $tmpl['icon'] }}</div>
            <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $tmpl['name'] }}</div>
            <div style="font-size:12px;color:var(--text-sub);margin-top:3px;line-height:1.4">{{ $tmpl['description'] }}</div>
        </button>
        @endforeach
        <button wire:click="$set('showTemplatePrompt', false)"
                style="text-align:left;padding:14px 16px;background:var(--surface2);
                       border:1.5px dashed var(--border);border-radius:var(--r);cursor:pointer">
            <div style="font-size:22px;margin-bottom:6px">✦</div>
            <div style="font-size:13px;font-weight:700;color:var(--text)">Start blank</div>
            <div style="font-size:12px;color:var(--text-sub);margin-top:3px">Build from scratch</div>
        </button>
    </div>
</div>
@endif
```

---

## PHASE 2 — High Value Features

---

## Step 9 — Text / Narrative Blocks

### Add text_block to MetricRegistry

Open `app/Services/Reports/MetricRegistry.php`. Add this entry to the
`catalogue()` array at the top of the list, or add a separate method
`formattingBlocks()` that returns it and merge it in `catalogue()`:

```php
[
    'id'           => 'text_block',
    'label'        => 'Text / Heading',
    'description'  => 'Add a heading, paragraph, divider, or annotation to your report',
    'domain'       => 'formatting',
    'viz_options'  => ['text'],
    'default_viz'  => 'text',
    'needs_dates'  => false,
    'needs_location' => false,
],
```

### Builder: add content field for text blocks

In `ReportBuilder::addBlock()`, when `$metricId === 'text_block'`, add a `content` key:

```php
if ($metricId === 'text_block') {
    $this->canvas[] = [
        'id'        => 'b' . now()->timestamp . '_' . count($this->canvas),
        'metric_id' => 'text_block',
        'title'     => '',
        'content'   => '',        // ← text content field
        'width'     => 'full',
        'viz'       => 'text',
        'position'  => count($this->canvas),
    ];
    return;
}
```

Add a method to update block content:

```php
public function updateBlockContent(string $blockId, string $content): void
{
    $this->canvas = collect($this->canvas)->map(fn ($b) =>
        $b['id'] === $blockId ? array_merge($b, ['content' => $content]) : $b
    )->toArray();
}
```

In the builder blade, for text blocks, show a `<textarea>` instead of the
viz/width controls:

```blade
@if($block['metric_id'] === 'text_block')
    <textarea wire:change="updateBlockContent('{{ $block['id'] }}', $event.target.value)"
              placeholder="Enter heading, paragraph, or note..."
              style="width:100%;min-height:80px;padding:8px;background:var(--surface);
                     border:1px solid var(--border);border-radius:var(--rsm);
                     font-size:13px;color:var(--text);resize:vertical">{{ $block['content'] ?? '' }}</textarea>
@endif
```

In the viewer blade, for `viz === 'text'`:

```blade
@elseif($viz === 'text')
    @php $content = $blockResult['block']['content'] ?? ''; @endphp
    @if($content)
        <div style="padding:14px 18px;background:var(--surface2);border-left:3px solid var(--accent);
                    border-radius:var(--rsm);font-size:14px;color:var(--text);line-height:1.7;
                    white-space:pre-wrap">{{ $content }}</div>
    @else
        <div style="padding:14px;color:var(--text-dim);font-style:italic">
            (Empty text block — edit report to add content)
        </div>
    @endif
```

---

## Step 10 — Period Comparison Mode

### Builder: add comparison_mode control

Open the builder blade. In the global filters bar, add a new select after the date range:

```blade
<div style="display:flex;flex-direction:column;gap:4px">
    <label style="font-size:11px;font-weight:700;color:var(--text-sub);text-transform:uppercase;
                  letter-spacing:.6px">Compare to</label>
    <select wire:model.live="comparisonMode"
            style="padding:8px 12px;background:var(--surface);border:1px solid var(--border);
                   border-radius:var(--rsm);font-size:13px;color:var(--text)">
        <option value="none">No comparison</option>
        <option value="prior_period">Prior period</option>
        <option value="prior_year">Same period last year</option>
    </select>
</div>
```

Add `public string $comparisonMode = 'none';` to `ReportBuilder`. Include it in
the `save()` config:

```php
$config = [
    'date_range'      => $this->dateRange,
    'date_from'       => $this->dateFrom,
    'date_to'         => $this->dateTo,
    'location_filter' => $this->locationFilter,
    'comparison_mode' => $this->comparisonMode,   // ← add this
    'blocks'          => array_values($this->canvas),
];
```

Also add `$this->comparisonMode = $config['comparison_mode'] ?? 'none';`
when loading an existing report in `mount()`.

### Viewer: show comparison delta

In the viewer blade, for `kpi_card` blocks, after rendering the main value,
check for `_comparison` data and show a delta chip:

```blade
@if(!empty($blockResult['data']['_comparison']))
    @php
        $curr = collect($blockResult['data'])->filter(fn($v,$k) => !str_starts_with($k,'_') && is_numeric($v))->first();
        $prev = collect($blockResult['data']['_comparison'])->filter(fn($v,$k) => !str_starts_with($k,'_') && is_numeric($v))->first();
        $delta = ($prev && $prev > 0) ? round((($curr - $prev) / $prev) * 100, 1) : null;
    @endphp
    @if($delta !== null)
        @php
            $deltaColor = $delta >= 0 ? 'var(--success)' : 'var(--danger)';
            $deltaGlow  = $delta >= 0 ? 'var(--success-glow)' : 'var(--danger-glow)';
        @endphp
        <span style="font-size:12px;font-weight:700;color:{{ $deltaColor }};
                     background:{{ $deltaGlow }};padding:2px 8px;border-radius:12px">
            {{ $delta >= 0 ? '+' : '' }}{{ $delta }}% vs {{ $blockResult['data']['_comparison_period'] }}
        </span>
    @endif
@endif
```

---

## Step 11 — Scorecard Thresholds

### Builder: threshold controls per block

Add a `thresholds` section to each canvas block (collapsible, for kpi_card viz only):

```blade
@if(($block['viz'] ?? '') === 'kpi_card')
<div x-data="{ open: false }" style="margin-top:8px">
    <button @click="open = !open"
            style="font-size:11px;color:var(--text-sub);background:none;border:none;cursor:pointer">
        ◈ Scorecard thresholds
    </button>
    <div x-show="open" style="margin-top:6px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
        @foreach(['green' => 'var(--success)', 'amber' => 'var(--amber)', 'red' => 'var(--danger)'] as $tier => $color)
        <div>
            <label style="font-size:10px;color:{{ $color }};font-weight:700">{{ strtoupper($tier) }} ≥</label>
            <input type="number"
                   wire:change="updateBlockOverride('{{ $block['id'] }}', 'threshold_{{ $tier }}', $event.target.value)"
                   value="{{ $block['threshold_' . $tier] ?? '' }}"
                   placeholder="—"
                   style="width:100%;padding:3px 6px;background:var(--surface);border:1px solid var(--border);
                          border-radius:var(--rsm);font-size:12px;color:var(--text)">
        </div>
        @endforeach
    </div>
</div>
@endif
```

### Viewer: scorecard status dot

In the viewer blade, for each `kpi_card` block, compute a status:

```blade
@php
    $thGreen = $blockResult['block']['threshold_green'] ?? null;
    $thAmber = $blockResult['block']['threshold_amber'] ?? null;
    $thRed   = $blockResult['block']['threshold_red'] ?? null;
    $mainNum = collect($blockResult['data'])->first(fn($v,$k) => !str_starts_with($k,'_') && is_numeric($v));
    $statusColor = null;
    if ($mainNum !== null && ($thGreen || $thAmber || $thRed)) {
        if ($thGreen && $mainNum >= $thGreen)       $statusColor = 'var(--success)';
        elseif ($thAmber && $mainNum >= $thAmber)   $statusColor = 'var(--amber)';
        elseif ($thRed !== null)                    $statusColor = 'var(--danger)';
    }
@endphp
@if($statusColor)
    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;
                 background:{{ $statusColor }};flex-shrink:0" title="Threshold status"></span>
@endif
```

Add a scorecard summary banner at the top of the viewer results area:

```blade
@php
    // Compute overall scorecard health
    $allStatuses = [];
    foreach($results as $r) {
        if (isset($r['block']['threshold_red']) || isset($r['block']['threshold_amber']) || isset($r['block']['threshold_green'])) {
            $mn = collect($r['data'])->first(fn($v,$k) => !str_starts_with($k,'_') && is_numeric($v));
            if ($mn !== null) {
                if (isset($r['block']['threshold_green']) && $mn >= $r['block']['threshold_green']) $allStatuses[] = 'green';
                elseif (isset($r['block']['threshold_amber']) && $mn >= $r['block']['threshold_amber']) $allStatuses[] = 'amber';
                else $allStatuses[] = 'red';
            }
        }
    }
    $redCount   = count(array_filter($allStatuses, fn($s) => $s === 'red'));
    $amberCount = count(array_filter($allStatuses, fn($s) => $s === 'amber'));
@endphp
@if(!empty($allStatuses))
    @if($redCount > 0)
        <div style="padding:12px 18px;background:var(--danger-glow);border:1px solid var(--danger);
                    border-radius:var(--r);margin-bottom:16px;font-weight:700;color:var(--danger)">
            ⚠ {{ $redCount }} metric{{ $redCount > 1 ? 's' : '' }} below threshold — action needed
        </div>
    @elseif($amberCount > 0)
        <div style="padding:12px 18px;background:var(--amber-dim);border:1px solid var(--amber);
                    border-radius:var(--r);margin-bottom:16px;font-weight:700;color:var(--amber)">
            ◑ {{ $amberCount }} metric{{ $amberCount > 1 ? 's' : '' }} approaching threshold
        </div>
    @else
        <div style="padding:12px 18px;background:var(--success-glow);border:1px solid var(--success);
                    border-radius:var(--r);margin-bottom:16px;font-weight:700;color:var(--success)">
            ✓ All metrics within healthy thresholds
        </div>
    @endif
@endif
```

---

## Step 12 — Block Data Controls (Sort, Limit, Filter)

### Builder: block options panel

Add a `updateBlockOption` method to `ReportBuilder`:

```php
public function updateBlockOption(string $blockId, string $key, mixed $value): void
{
    $this->canvas = collect($this->canvas)->map(function ($b) use ($blockId, $key, $value) {
        if ($b['id'] !== $blockId) return $b;
        $options        = $b['block_options'] ?? [];
        $options[$key]  = $value ?: null;
        return array_merge($b, ['block_options' => $options]);
    })->toArray();
}
```

In the builder blade, for table and chart blocks, add a "Customize data" section:

```blade
@php
    $isTableLike = in_array($block['viz'] ?? '', ['table', 'bar_chart', 'line_chart']);
@endphp
@if($isTableLike)
<div x-data="{ open: false }" style="margin-top:8px">
    <button @click="open = !open"
            style="font-size:11px;color:var(--text-sub);background:none;border:none;cursor:pointer">
        ⊞ Customize data
    </button>
    <div x-show="open" style="margin-top:6px;display:grid;grid-template-columns:1fr 1fr;gap:6px">
        <div>
            <label style="font-size:11px;color:var(--text-sub)">Max rows</label>
            <input type="number" min="1" max="100"
                   wire:change="updateBlockOption('{{ $block['id'] }}', 'limit', $event.target.value)"
                   value="{{ $block['block_options']['limit'] ?? '' }}"
                   placeholder="All"
                   style="width:100%;padding:4px 8px;background:var(--surface);border:1px solid var(--border);
                          border-radius:var(--rsm);font-size:12px;color:var(--text)">
        </div>
        <div>
            <label style="font-size:11px;color:var(--text-sub)">Sort direction</label>
            <select wire:change="updateBlockOption('{{ $block['id'] }}', 'sort_direction', $event.target.value)"
                    style="width:100%;padding:4px 8px;background:var(--surface);border:1px solid var(--border);
                           border-radius:var(--rsm);font-size:12px;color:var(--text)">
                <option value="desc" @selected(($block['block_options']['sort_direction'] ?? 'desc') === 'desc')>Highest first</option>
                <option value="asc"  @selected(($block['block_options']['sort_direction'] ?? '') === 'asc')>Lowest first</option>
            </select>
        </div>
    </div>
</div>
@endif
```

---

## Step 13 — Run History Drawer

### Viewer additions

Open `app/Livewire/Owner/Reports/ReportViewer.php`. Add:

```php
public bool  $showHistory = false;
public int   $viewingHistoryId = 0;
public array $historyResults   = [];

public function toggleHistory(): void
{
    $this->showHistory = !$this->showHistory;
}

public function viewHistoryRun(int $historyId): void
{
    $run = \App\Models\ReportRunHistory::findOrFail($historyId);
    if ($run->report_id !== $this->reportId) abort(403);
    $this->historyResults    = $run->results ?? [];
    $this->viewingHistoryId  = $historyId;
    $this->results           = $this->historyResults;
    $this->hasRun            = true;
    $this->showHistory       = false;
}
```

In the viewer blade, add a "History" button in the header:

```blade
<button wire:click="toggleHistory"
        style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;
               background:var(--surface);border:1px solid var(--border);border-radius:var(--rsm);
               font-size:13px;font-weight:600;color:var(--text);cursor:pointer">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
    </svg>
    History
</button>
```

Add a history drawer at the bottom of the blade (match product-detail drawer pattern):

```blade
@if($showHistory)
<div style="position:fixed;inset:0;z-index:50;display:flex">
    <div wire:click="toggleHistory" style="flex:1;background:rgba(0,0,0,.4)"></div>
    <div style="width:400px;background:var(--surface);border-left:1px solid var(--border);
                overflow-y:auto;padding:24px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <span style="font-size:16px;font-weight:800;color:var(--text)">Run History</span>
            <button wire:click="toggleHistory" style="background:none;border:none;cursor:pointer;
                    color:var(--text-sub);font-size:20px">×</button>
        </div>

        @php $history = \App\Models\ReportRunHistory::where('report_id', $reportId)->orderByDesc('run_at')->limit(12)->get(); @endphp

        @forelse($history as $run)
        <div style="padding:12px;background:var(--surface2);border:1px solid var(--border);
                    border-radius:var(--rsm);margin-bottom:8px">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--text)">
                        {{ $run->run_at->format('d M Y, H:i') }}
                    </div>
                    <div style="font-size:12px;color:var(--text-sub);margin-top:2px">
                        By {{ $run->runner?->name ?? 'System' }} · {{ $run->duration_ms }}ms
                        @if($run->was_scheduled) · Scheduled @endif
                    </div>
                </div>
                <button wire:click="viewHistoryRun({{ $run->id }})"
                        style="padding:5px 12px;background:var(--accent);color:#fff;
                               border:none;border-radius:var(--rsm);font-size:12px;
                               font-weight:700;cursor:pointer">
                    View
                </button>
            </div>
        </div>
        @empty
        <p style="color:var(--text-sub);font-size:13px">No run history yet.</p>
        @endforelse
    </div>
</div>
@endif
```

---

## Step 14 — Annotations

### Viewer: annotation icons per block

Open `app/Livewire/Owner/Reports/ReportViewer.php`. Add:

```php
public string $annotatingBlockId  = '';
public string $annotationText     = '';
public bool   $showAnnotationForm = false;

public function openAnnotation(string $blockId): void
{
    $this->annotatingBlockId  = $blockId;
    $this->annotationText     = '';
    $this->showAnnotationForm = true;
}

public function saveAnnotation(): void
{
    if (!trim($this->annotationText)) return;

    \App\Models\ReportAnnotation::create([
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
```

In the viewer blade, on each rendered block, add a small annotation button in the block header:

```blade
<button wire:click="openAnnotation('{{ $blockResult['block']['id'] }}')"
        title="Add note"
        style="background:none;border:none;cursor:pointer;color:var(--text-dim);
               padding:2px 4px;border-radius:4px"
        onmouseover="this.style.color='var(--accent)'"
        onmouseout="this.style.color='var(--text-dim)'">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
    </svg>
</button>
```

Also load and display existing annotations per block:

```blade
@php
    $blockAnnotations = $currentRunHistoryId
        ? \App\Models\ReportAnnotation::where('report_id', $reportId)
            ->where('run_history_id', $currentRunHistoryId)
            ->where('block_id', $blockResult['block']['id'])
            ->with('author')->get()
        : collect();
@endphp
@foreach($blockAnnotations as $ann)
<div style="margin-top:8px;padding:8px 12px;background:var(--violet-dim);
            border-left:3px solid var(--violet);border-radius:var(--rsm)">
    <div style="font-size:12px;color:var(--text);line-height:1.5">{{ $ann->note }}</div>
    <div style="font-size:11px;color:var(--text-sub);margin-top:3px">
        — {{ $ann->author?->name ?? 'You' }}, {{ $ann->created_at->diffForHumans() }}
    </div>
</div>
@endforeach
```

Add annotation modal at bottom of viewer blade:

```blade
@if($showAnnotationForm)
<div style="position:fixed;inset:0;z-index:60;display:flex;align-items:center;
            justify-content:center;background:rgba(0,0,0,.5)">
    <div style="width:400px;background:var(--surface);border-radius:var(--r);padding:24px">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;color:var(--text)">
            Add note to block
        </div>
        <textarea wire:model="annotationText" rows="4"
                  placeholder="Enter your observation or note..."
                  style="width:100%;padding:10px;background:var(--surface2);
                         border:1px solid var(--border);border-radius:var(--rsm);
                         font-size:13px;color:var(--text);resize:vertical"></textarea>
        <div style="display:flex;gap:8px;margin-top:12px;justify-content:flex-end">
            <button wire:click="$set('showAnnotationForm', false)"
                    style="padding:8px 16px;background:var(--surface2);border:1px solid var(--border);
                           border-radius:var(--rsm);font-size:13px;cursor:pointer">Cancel</button>
            <button wire:click="saveAnnotation"
                    style="padding:8px 16px;background:var(--accent);color:#fff;border:none;
                           border-radius:var(--rsm);font-size:13px;font-weight:700;cursor:pointer">
                Save note
            </button>
        </div>
    </div>
</div>
@endif
```

---

## Step 15 — Scheduled Delivery

### Artisan command

Create `app/Console/Commands/RunScheduledReports.php`:

```php
<?php
namespace App\Console\Commands;

use App\Models\SavedReport;
use App\Services\Reports\ReportRunner;
use App\Services\Reports\ExportReportAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RunScheduledReports extends Command
{
    protected $signature   = 'reports:run-scheduled';
    protected $description = 'Run all saved reports that are due for scheduled delivery';

    public function handle(): int
    {
        $reports = SavedReport::whereNotNull('schedule_cron')
            ->whereNotNull('schedule_recipients')
            ->whereNull('deleted_at')
            ->get();

        foreach ($reports as $report) {
            if (!$this->isDue($report)) continue;

            $this->info("Running: {$report->name}");

            try {
                $runner  = app(ReportRunner::class);
                $results = $runner->run($report->resolvedConfig(), $report->id, false);

                // Write history as scheduled run
                \App\Models\ReportRunHistory::create([
                    'report_id'       => $report->id,
                    'run_by'          => $report->created_by,
                    'run_at'          => now(),
                    'config_snapshot' => $report->resolvedConfig(),
                    'results'         => $results,
                    'duration_ms'     => 0,
                    'was_scheduled'   => true,
                ]);

                $report->cacheResults($results);
                $report->update(['last_scheduled_run_at' => now()]);

                // Send email
                $exporter  = app(ExportReportAction::class);
                $csv       = $exporter->toCsv($report, $results);
                $recipients = $report->schedule_recipients ?? [];

                foreach ($recipients as $email) {
                    Mail::raw(
                        "Please find the scheduled report \"{$report->name}\" attached.\n\nGenerated: " . now()->format('d M Y H:i'),
                        function ($m) use ($email, $report, $csv) {
                            $m->to($email)
                              ->subject("[Smart Inventory] {$report->name} — " . now()->format('d M Y'))
                              ->attachData($csv,
                                  str($report->name)->slug() . '-' . now()->format('Y-m-d') . '.csv',
                                  ['mime' => 'text/csv']);
                        }
                    );
                }

                $this->info("  ✓ Sent to " . count($recipients) . " recipient(s)");
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed: " . $e->getMessage());
            }
        }

        return 0;
    }

    private function isDue(SavedReport $report): bool
    {
        if (!$report->schedule_cron) return false;

        try {
            $cron     = new \Cron\CronExpression($report->schedule_cron);
            $lastRun  = $report->last_scheduled_run_at ?? now()->subYear();
            return $cron->isDue() || $cron->getNextRunDate($lastRun) <= now();
        } catch (\Throwable) {
            return false;
        }
    }
}
```

Register the command in `bootstrap/app.php` inside `withSchedule()`:

```php
$schedule->command('reports:run-scheduled')->hourly();
```

Also check if `cron/cron-expression` is installed:

```bash
composer show | grep cron
```

If not installed:

```bash
composer require dragonmantank/cron-expression
```

### Builder: schedule section

Add `public string $scheduleCron = '';` and `public string $scheduleRecipients = '';`
to `ReportBuilder`. Include them in `save()`:

```php
'schedule_cron'       => $this->scheduleCron ?: null,
'schedule_recipients' => $this->scheduleRecipients
    ? array_map('trim', explode(',', $this->scheduleRecipients))
    : null,
```

In the builder blade, after the is_shared toggle, add a collapsible Schedule section:

```blade
<div x-data="{ open: !!@js($scheduleCron) }">
    <button @click="open = !open" type="button"
            style="font-size:13px;font-weight:600;color:var(--text-sub);background:none;
                   border:none;cursor:pointer;padding:4px 0">
        🕐 Scheduled delivery (optional)
    </button>

    <div x-show="open" style="margin-top:10px;padding:14px;background:var(--surface2);
                               border-radius:var(--rsm);border:1px solid var(--border)">
        <div style="display:grid;gap:10px">
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-sub)">Frequency</label>
                <select wire:model.live="scheduleCron"
                        style="width:100%;margin-top:4px;padding:8px;background:var(--surface);
                               border:1px solid var(--border);border-radius:var(--rsm);
                               font-size:13px;color:var(--text)">
                    <option value="">No schedule</option>
                    <option value="0 8 * * *">Daily at 8am</option>
                    <option value="0 8 * * 1">Every Monday at 8am</option>
                    <option value="0 8 1 * *">1st of every month at 8am</option>
                    <option value="0 8 * * 5">Every Friday at 8am</option>
                </select>
            </div>
            @if($scheduleCron)
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-sub)">
                    Recipient emails (comma-separated)
                </label>
                <input type="text" wire:model="scheduleRecipients"
                       placeholder="owner@example.com, manager@example.com"
                       style="width:100%;margin-top:4px;padding:8px;background:var(--surface);
                              border:1px solid var(--border);border-radius:var(--rsm);
                              font-size:13px;color:var(--text)">
            </div>
            @endif
        </div>
    </div>
</div>
```

---

## Step 16 — Dashboard Pinning

Open `app/Livewire/Owner/Reports/ReportViewer.php`. Add:

```php
public function togglePin(): void
{
    $report = SavedReport::findOrFail($this->reportId);
    if ($report->created_by !== auth()->id()) abort(403);

    if ($report->pinned_to_dashboard) {
        $report->update(['pinned_to_dashboard' => false, 'dashboard_position' => null]);
        session()->flash('success', 'Report unpinned from dashboard.');
    } else {
        // Allow max 3 pinned
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
```

In the viewer blade header, add a pin button:

```blade
@php $isPinned = $report->pinned_to_dashboard; @endphp
<button wire:click="togglePin"
        style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;
               background:{{ $isPinned ? 'var(--accent-dim)' : 'var(--surface)' }};
               border:1px solid {{ $isPinned ? 'var(--accent)' : 'var(--border)' }};
               border-radius:var(--rsm);font-size:13px;font-weight:600;
               color:{{ $isPinned ? 'var(--accent)' : 'var(--text)' }};cursor:pointer">
    {{ $isPinned ? '📌 Pinned' : '📌 Pin to Dashboard' }}
</button>
```

### Dashboard integration

Open the owner dashboard blade (`resources/views/livewire/owner/dashboard.blade.php`
or wherever the main owner dashboard renders). Add a "Pinned Reports" section
immediately after the existing KPI rows, before activity feed:

```blade
@php
    $pinnedReports = \App\Models\SavedReport::where('pinned_to_dashboard', true)
        ->whereNull('deleted_at')
        ->orderBy('dashboard_position')
        ->limit(3)
        ->get();
@endphp

@if($pinnedReports->isNotEmpty())
<div class="section-label">Pinned Reports</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px;margin-bottom:20px">
    @foreach($pinnedReports as $pr)
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:16px 18px">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:10px">
            <div>
                <div style="font-size:14px;font-weight:700;color:var(--text)">{{ $pr->name }}</div>
                @if($pr->results_cached_at)
                <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                    Updated {{ $pr->results_cached_at->diffForHumans() }}
                </div>
                @endif
            </div>
            <a href="{{ route('owner.reports.custom.view', $pr->id) }}"
               style="font-size:12px;font-weight:700;color:var(--accent);text-decoration:none;
                      white-space:nowrap">View →</a>
        </div>

        @php $cached = $pr->last_results ?? []; @endphp
        @if(!empty($cached))
            {{-- Show first 2 KPI blocks from cached results --}}
            @php $kpiBlocks = collect($cached)->filter(fn($r) => ($r['block']['viz'] ?? '') === 'kpi_card')->take(2); @endphp
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                @foreach($kpiBlocks as $blockResult)
                    @php
                        $num = collect($blockResult['data'])->first(fn($v,$k) => !str_starts_with($k,'_') && is_numeric($v));
                    @endphp
                    <div style="padding:8px;background:var(--surface2);border-radius:var(--rsm)">
                        <div style="font-size:18px;font-weight:800;color:var(--text)">
                            {{ is_numeric($num) ? number_format($num) : '—' }}
                        </div>
                        <div style="font-size:11px;color:var(--text-sub)">{{ $blockResult['block']['title'] ?? '' }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="font-size:12px;color:var(--text-dim)">
                No data yet — <a href="{{ route('owner.reports.custom.view', $pr->id) }}"
                                  style="color:var(--accent)">run report</a>
            </div>
        @endif
    </div>
    @endforeach
</div>
@endif
```

---

## Step 17 — Conditional Block Visibility + show_if_nonzero

In the viewer blade, wrap each block's render with:

```blade
@php
    $shouldHide = !empty($blockResult['block']['show_if_nonzero'])
        && collect($blockResult['data'])->filter(fn($v,$k) => !str_starts_with($k,'_') && is_numeric($v))->first() == 0;
@endphp
@if(!$shouldHide)
    {{-- ... existing block render ... --}}
@endif
```

In the builder blade, on each canvas block card footer, add a small toggle:

```blade
<label style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-sub);cursor:pointer">
    <input type="checkbox"
           wire:change="updateBlockOverride('{{ $block['id'] }}', 'show_if_nonzero', $event.target.checked)"
           @checked(!empty($block['show_if_nonzero']))>
    Hide if zero
</label>
```

---

## Step 18 — Library enhancements

Open `resources/views/livewire/owner/reports/report-library.blade.php`.
In each report card, add access and run stats derived from the new tables:

```blade
@php
    $viewCount = \App\Models\ReportViewLog::where('report_id', $report->id)
        ->where('viewed_at', '>=', now()->subDays(30))->count();
    $lastViewer = \App\Models\ReportViewLog::where('report_id', $report->id)
        ->orderByDesc('viewed_at')->first();
@endphp
<div style="font-size:11px;color:var(--text-dim);margin-top:6px">
    Viewed {{ $viewCount }}× this month
    @if($report->is_shared && $lastViewer)
        · Last by {{ \App\Models\User::find($lastViewer->viewed_by)?->name ?? 'Unknown' }}
    @endif
    @if($report->schedule_cron)
        · 🕐 Scheduled
    @endif
    @if($report->pinned_to_dashboard)
        · 📌 Pinned
    @endif
</div>
```

---

## Step 19 — Verification

Run in order and fix every error before moving to the next:

```bash
php artisan migrate
php artisan view:clear && php artisan cache:clear
php artisan view:cache 2>&1 | grep -i "error\|exception" | head -30
```

Verify all new classes load:

```bash
php artisan tinker --execute="
    app(\App\Services\Reports\ReportTemplates::class)::all();
    app(\App\Services\Reports\ExportReportAction::class);
    echo 'All OK';
"
```

Verify command runs without error:

```bash
php artisan reports:run-scheduled --help
```

Open the browser and test:

1. `/owner/reports/custom/builder` — templates grid appears, clicking one pre-fills canvas
2. Add a text block — editable content area appears in canvas
3. Enable "Compare to: Prior period" — runs correctly with delta chips in viewer
4. Export CSV from a run report — downloads valid CSV
5. Print/PDF link — opens printable HTML in new tab
6. Add annotation to a block — saved and re-displayed on reload
7. History button — shows previous runs, clicking "View" restores results
8. Pin a report — appears in Dashboard pinned section
9. Scorecard thresholds — green/amber/red dot appears; banner shows status

---

## Step 20 — Update CLAUDE.md

After all verification passes, open `CLAUDE.md` and append:

```markdown
---

## Custom Report Builder — Enhancements (Phase 2)

All features from `custom-report-builder-enhancements.md` have been applied.

### Schema changes (all additive, no destructive changes)
- `saved_reports` — added: last_results, results_cached_at, results_stale_at,
  schedule_cron, schedule_recipients, last_scheduled_run_at,
  pinned_to_dashboard, dashboard_position
- New table: `report_run_history` — full run results stored, 12-run cap per report
- New table: `report_annotations` — per-block notes tied to a run
- New table: `report_view_log` — who viewed what and when

### New models
- `ReportRunHistory` — run history with config snapshot and results
- `ReportAnnotation` — block/run-level notes by any owner/admin
- `ReportViewLog` — access tracking for shared reports

### New services
- `ExportReportAction` — CSV and print-HTML export from run results
- `ReportTemplates` — 6 pre-built starter configs (no DB table needed)

### New artisan command
- `reports:run-scheduled` — runs on `schedule()->hourly()`, sends CSV via email

### ReportRunner changes
- `run()` now accepts `$reportId` and `$writeHistory`, writes history + cache
- Per-block `location_filter_override` and `date_range_override` respected
- Period comparison via `comparison_mode` in config (prior_period | prior_year)
- `applyBlockOptions()` applies sort/limit to collection-type blocks
- `text_block` metric_id passes through without querying analytics

### Key design rules going forward
- To add a new block: (1) MetricRegistry entry, (2) ReportRunner match case
- To add a new template: add entry to ReportTemplates::all() — no migration needed
- `show_if_nonzero` is a block-level flag rendered in blade, not in runner
- Dashboard pin is max 3 reports; enforced in `togglePin()`
- Run history is pruned to 12 rows per report after every run
```
