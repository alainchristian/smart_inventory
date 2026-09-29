<?php
namespace App\Services\Reports;

use App\Models\SavedReport;
use App\Models\User;

/**
 * A saved report run for one set of filters, with everything an export
 * needs to describe it (period, location, comparison, who and when).
 * The PDF, Excel and CSV exports all read this, so they always agree
 * with each other and with what the viewer showed.
 */
final class ReportDocument
{
    public readonly array $config;
    public readonly string $from;
    public readonly string $to;
    public readonly string $periodLabel;
    public readonly string $locationLabel;
    public readonly ?string $comparisonLabel;
    public readonly array $results;
    public readonly string $generatedAt;

    public function __construct(
        public readonly SavedReport $report,
        array $filters = [],
        public readonly ?User $generatedBy = null,
        ?array $results = null,
    ) {
        $runner = app(ReportRunner::class);
        $this->config = $runner->effectiveConfig($report->resolvedConfig(), $filters);
        [$this->from, $this->to] = $runner->resolveDates($this->config);

        $prior = ReportPeriod::prior($this->config['comparison_mode'] ?? 'none', $this->from, $this->to);

        $this->periodLabel     = ReportPeriod::label($this->from, $this->to);
        $this->locationLabel   = ReportContext::locationLabel($this->config['location_filter'] ?? 'all');
        $this->comparisonLabel = $prior ? ReportPeriod::label(...$prior) : null;
        $this->results         = $results ?? $runner->cached($report, $filters, recordHistory: false);
        $this->generatedAt     = business_now()->format('j M Y, H:i');
    }

    /** The filters, as the viewer and export URLs pass them */
    public function filters(): array
    {
        return [
            'date_range'      => 'custom',
            'date_from'       => $this->from,
            'date_to'         => $this->to,
            'location_filter' => $this->config['location_filter'] ?? 'all',
            'comparison_mode' => $this->config['comparison_mode'] ?? 'none',
        ];
    }

    /** Summary-card blocks (shown together at the top) */
    public function kpis(): array
    {
        return array_filter($this->results, fn ($e) => ($e['block']['viz'] ?? '') === 'kpi_card' && $e['result'] !== null);
    }

    /** Everything else, in report order */
    public function blocks(): array
    {
        return array_diff_key($this->results, $this->kpis());
    }

    /** "shop-review_1-sep-2026_to_29-sep-2026" */
    public function fileName(string $extension): string
    {
        $period = $this->from === $this->to ? $this->from : $this->from . '_to_' . $this->to;

        return (str($this->report->name)->slug()->limit(60, '')->value() ?: 'report') . '_' . $period . '.' . $extension;
    }

    /**
     * Up to 4 one-line findings: problems first, then warnings, then good news.
     *
     * @return list<array{title:string,text:string,tone:string}>
     */
    public static function findings(array $results): array
    {
        $order = ['bad' => 0, 'warn' => 1, 'good' => 2];

        return collect($results)
            ->filter(fn ($e) => isset($e['result']['insight']['tone'], $order[$e['result']['insight']['tone']]))
            ->map(fn ($e) => [
                'title' => $e['block']['title'] ?? ($e['meta']['label'] ?? ''),
                'text'  => $e['result']['insight']['text'],
                'tone'  => $e['result']['insight']['tone'],
            ])
            ->sortBy(fn ($f) => $order[$f['tone']])
            ->take(4)->values()->all();
    }
}
