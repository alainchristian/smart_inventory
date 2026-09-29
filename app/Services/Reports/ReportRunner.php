<?php
namespace App\Services\Reports;

use App\Services\Reports\Metrics\Metric;

/**
 * Runs a saved report config block by block.
 *
 * Each result entry (keyed by block id) is:
 *   block  — the block's config
 *   meta   — the metric's catalogue entry
 *   result — MetricResult::toArray(); what the renderers should read
 *   data   — the raw service output, still read by the old viewer
 *            (dropped when the viewer is rebuilt, phase 3)
 *   ran_at
 */
class ReportRunner
{
    public function __construct(private MetricRegistry $registry) {}

    /**
     * Report-level [from, to] (business-tz Y-m-d) for a config.
     */
    public function resolveDates(array $config): array
    {
        return ReportPeriod::resolve($config['date_range'] ?? 'month', $config['date_from'] ?? null, $config['date_to'] ?? null);
    }

    public function resolvePriorDates(array $config, string $currentFrom, string $currentTo): array
    {
        return ReportPeriod::prior($config['comparison_mode'] ?? 'prior_period', $currentFrom, $currentTo)
            ?? ReportPeriod::prior('prior_period', $currentFrom, $currentTo);
    }

    /**
     * Viewer filters (date_range, date_from, date_to, location_filter,
     * comparison_mode) override the saved defaults for this run only.
     */
    public function effectiveConfig(array $config, array $filters = []): array
    {
        $allowed = array_intersect_key($filters, array_flip(['date_range', 'date_from', 'date_to', 'location_filter', 'comparison_mode']));
        $config  = array_merge($config, array_filter($allowed, fn ($v) => $v !== null && $v !== ''));
        $config['location_filter'] = ReportContext::normaliseLocation($config['location_filter'] ?? 'all');

        return $config;
    }

    public function run(array $config, ?int $reportId = null, bool $writeHistory = true, array $filters = []): array
    {
        $startTime = microtime(true);
        $config    = $this->effectiveConfig($config, $filters);
        [$dateFrom, $dateTo] = $this->resolveDates($config);
        $comparison = $config['comparison_mode'] ?? 'none';
        $results    = [];

        foreach (\App\Models\SavedReport::withUniqueBlockIds($config['blocks'] ?? []) as $block) {
            $metricId = $block['metric_id'] ?? null;
            if (! $metricId) continue;

            if ($metricId === 'text_block') {
                $results[$block['id']] = [
                    'block'  => $block,
                    'meta'   => MetricRegistry::TEXT_BLOCK,
                    'result' => null,
                    'data'   => [],
                    'ran_at' => now()->toDateTimeString(),
                ];
                continue;
            }

            $metric = $this->registry->metric($metricId);
            if (! $metric) {
                $results[$block['id']] = [
                    'block'  => $block,
                    'meta'   => ['id' => $metricId, 'label' => $block['title'] ?? $metricId, 'viz_options' => [], 'default_viz' => 'kpi_card'],
                    'result' => MetricResult::failed('This block is no longer available. Remove it from the report.')->toArray(),
                    'data'   => ['error' => 'This block is no longer available.'],
                    'ran_at' => now()->toDateTimeString(),
                ];
                continue;
            }

            [$raw, $result] = $this->runBlock($metric, $block, $dateFrom, $dateTo, $config['location_filter'], $comparison);

            $results[$block['id']] = [
                'block'  => $block,
                'meta'   => $metric->meta(),
                'result' => $result->toArray(),
                'data'   => $raw,
                'ran_at' => now()->toDateTimeString(),
            ];
        }

        if ($reportId && $writeHistory) {
            $this->recordRun($reportId, $config, $results, (int) round((microtime(true) - $startTime) * 1000));
        }

        return $results;
    }

    /** @return array{0: array, 1: MetricResult} */
    public function runBlock(Metric $metric, array $block, string $dateFrom, string $dateTo, string $location, string $comparison = 'none'): array
    {
        // Per-block overrides
        if (! empty($block['date_range_override'])) {
            [$dateFrom, $dateTo] = ReportPeriod::resolve(
                $block['date_range_override'],
                $block['date_from_override'] ?? null,
                $block['date_to_override'] ?? null,
            );
        }
        if (! empty($block['location_filter_override'])) {
            $location = ReportContext::normaliseLocation($block['location_filter_override']);
        }

        [$location, $scopeNote] = $this->scopeLocation($metric, $location);
        $ctx = new ReportContext($dateFrom, $dateTo, $location);

        try {
            [$raw, $result] = $metric->evaluate($ctx);
        } catch (\Throwable $e) {
            report($e);
            return [['error' => "Couldn't load this block."], MetricResult::failed("Couldn't load this block. Try again, or remove it if it keeps failing.")];
        }

        if ($scopeNote) {
            $result->note($scopeNote);
        }
        if ($note = $metric->periodNote()) {
            $result->note($note);
        }

        // Block options: sort / limit (on the result, and on the raw rows the old viewer reads)
        $opts  = $block['block_options'] ?? [];
        $limit = isset($opts['limit']) && is_numeric($opts['limit']) ? (int) $opts['limit'] : null;
        $result->sortAndLimit($opts['sort_by'] ?? null, $opts['sort_direction'] ?? 'desc', $limit);
        $raw = $this->legacyBlockOptions($opts, $raw);

        // Comparison with an earlier period (only for figures that depend on the period)
        if ($comparison !== 'none' && $metric->usesDates() && $result->value() !== null) {
            $prior = ReportPeriod::prior($comparison, $dateFrom, $dateTo);
            if ($prior) {
                try {
                    [$priorRaw, $priorResult] = $metric->evaluate($ctx->withDates(...$prior));
                    $result->comparison = $this->compare($metric, $result->value(), $priorResult->value(), $prior);
                    $raw['_comparison'] = $priorRaw;
                    $raw['_comparison_period'] = $prior[0] . ' – ' . $prior[1];
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        $result->status = $this->thresholdStatus($metric, $result->value(), $block);

        return [$raw, $result];
    }

    /**
     * Some figures can't be split the way the filter asks: company-wide
     * ones ignore the location, shop-only ones can't filter by warehouse.
     * Fall back to all locations and say so, instead of silently
     * showing everything under a warehouse's name.
     *
     * @return array{0: string, 1: ?string}
     */
    private function scopeLocation(Metric $metric, string $location): array
    {
        if ($location === 'all') {
            return ['all', null];
        }

        return match ($metric->locations()) {
            'none' => ['all', 'Covers every location. This figure isn\'t split by location.'],
            'shop' => str_starts_with($location, 'warehouse:')
                ? ['all', 'Shows all shops. This figure is recorded per shop, so it can\'t be filtered by warehouse.']
                : [$location, null],
            default => [$location, null],
        };
    }

    private function compare(Metric $metric, int|float $current, int|float|null $previous, array $period): array
    {
        $pct  = $previous ? round(($current - $previous) / abs($previous) * 100, 1) : null;
        $good = match (true) {
            $pct === null || $pct == 0.0         => null,
            $metric->goodDirection() === 'up'    => $pct > 0,
            $metric->goodDirection() === 'down'  => $pct < 0,
            default                              => null,
        };

        return [
            'value'  => $previous,
            'period' => ReportPeriod::label(...$period),
            'from'   => $period[0],
            'to'     => $period[1],
            'pct'    => $pct,
            'good'   => $good,
        ];
    }

    /**
     * ok | warn | crit from the block's thresholds. For figures that
     * should go up (revenue, margin) crossing a threshold means falling
     * to or below it; for everything else, rising to or above it.
     */
    public function thresholdStatus(Metric $metric, int|float|null $value, array $block): ?string
    {
        $warn = $block['threshold_warning'] ?? null;
        $crit = $block['threshold_critical'] ?? null;
        if ($value === null || ($warn === null && $crit === null)) {
            return null;
        }

        $crossed = $metric->goodDirection() === 'up'
            ? fn ($t) => $t !== null && $value <= $t
            : fn ($t) => $t !== null && $value >= $t;

        return match (true) {
            $crossed($crit) => 'crit',
            $crossed($warn) => 'warn',
            default         => 'ok',
        };
    }

    private function legacyBlockOptions(array $options, array $data): array
    {
        if (empty($options) || ! isset($data[0]) || ! is_array($data[0])) {
            return $data;
        }

        $rows = collect($data);
        if (! empty($options['sort_by'])) {
            $rows = ($options['sort_direction'] ?? 'desc') === 'asc'
                ? $rows->sortBy($options['sort_by'])
                : $rows->sortByDesc($options['sort_by']);
        }
        if (! empty($options['limit']) && is_numeric($options['limit'])) {
            $rows = $rows->take((int) $options['limit']);
        }

        return $rows->values()->toArray();
    }

    private function recordRun(int $reportId, array $config, array $results, int $durationMs): void
    {
        \App\Models\ReportRunHistory::create([
            'report_id'       => $reportId,
            'run_by'          => auth()->id() ?? \App\Models\SavedReport::whereKey($reportId)->value('created_by'),
            'run_at'          => now(),
            'config_snapshot' => $config,
            'results'         => $results,
            'duration_ms'     => $durationMs,
            'was_scheduled'   => false,
        ]);

        \App\Models\SavedReport::find($reportId)?->cacheResults($results);

        // Keep the last 12 runs
        $keep = \App\Models\ReportRunHistory::where('report_id', $reportId)
            ->orderByDesc('run_at')->orderByDesc('id')->limit(12)->pluck('id');
        \App\Models\ReportRunHistory::where('report_id', $reportId)->whereNotIn('id', $keep)->delete();
    }
}
