<?php
namespace App\Services\Reports\Metrics;

use App\Services\Analytics\FinanceAnalyticsService;
use App\Services\Analytics\InventoryAnalyticsService;
use App\Services\Analytics\LossAnalyticsService;
use App\Services\Analytics\SalesAnalyticsService;
use App\Services\Analytics\TransferAnalyticsService;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

/**
 * One block a custom report can contain.
 *
 * A metric wraps an existing analytics service call (fetch) and turns
 * its raw array into a MetricResult (present). The runner handles
 * dates, location fallbacks, comparison, thresholds, sorting and errors,
 * so subclasses only describe their data.
 *
 * To add one: subclass, fill the properties, implement fetch() and
 * present(), and list the class in MetricRegistry::METRICS.
 */
abstract class Metric
{
    /** Stored in saved_reports.config — never rename an existing id */
    protected string $id;
    protected string $label;
    protected string $description;
    /** sales | inventory | replenishment | loss | transfers | operations | finance | content */
    protected string $domain;
    /** kpi_card | table | bar_chart | line_chart | text */
    protected array  $viz = ['kpi_card'];
    protected string $defaultViz = 'kpi_card';
    /** false = a snapshot of now; the report period doesn't apply */
    protected bool   $usesDates = true;
    /** none = company-wide only; shop = shops only; any = shops and warehouses */
    protected string $locations = 'shop';
    /** Which way the headline should move: up | down | neutral */
    protected string $good = 'up';
    /** Metric ids shown (top 5 rows) in this block's "Details" sheet */
    protected array $related = [];
    /** Shown when $usesDates is false: what time span the figure covers instead */
    protected ?string $periodNote = "Stock as it is now. The report period doesn't apply.";

    abstract public function fetch(ReportContext $ctx): array;

    abstract public function present(array $raw, ReportContext $ctx): MetricResult;

    /** One plain-English sentence about the result, or null */
    public function insight(MetricResult $r): ?array
    {
        return null;
    }

    /**
     * @return array{0: array, 1: MetricResult} raw service output (still
     *   read by the old viewer until the phase-3 rebuild) and the result
     */
    public function evaluate(ReportContext $ctx): array
    {
        $raw    = $this->fetch($ctx);
        $result = $this->present($raw, $ctx);
        $result->insight($this->insight($result));

        return [$raw, $result];
    }

    public function id(): string            { return $this->id; }
    public function usesDates(): bool       { return $this->usesDates; }
    public function locations(): string     { return $this->locations; }
    public function goodDirection(): string { return $this->good; }
    public function periodNote(): ?string   { return $this->usesDates ? null : $this->periodNote; }
    public function related(): array        { return $this->related; }

    /** Same keys the old catalogue had, plus locations / good / uses_dates */
    public function meta(): array
    {
        return [
            'id'             => $this->id,
            'label'          => $this->label,
            'description'    => $this->description,
            'domain'         => $this->domain,
            'viz_options'    => $this->viz,
            'default_viz'    => $this->defaultViz,
            'needs_dates'    => $this->usesDates,
            'needs_location' => $this->locations !== 'none',
            'locations'      => $this->locations,
            'good'           => $this->good,
        ];
    }

    // ── helpers for subclasses ──────────────────────────────────────────

    protected function sales(): SalesAnalyticsService         { return app(SalesAnalyticsService::class); }
    protected function inventory(): InventoryAnalyticsService { return app(InventoryAnalyticsService::class); }
    protected function loss(): LossAnalyticsService           { return app(LossAnalyticsService::class); }
    protected function transfers(): TransferAnalyticsService  { return app(TransferAnalyticsService::class); }
    protected function finance(): FinanceAnalyticsService     { return app(FinanceAnalyticsService::class); }

    protected static function ok(string $text): array      { return ['text' => $text, 'tone' => 'good']; }
    protected static function warn(string $text): array    { return ['text' => $text, 'tone' => 'warn']; }
    protected static function bad(string $text): array     { return ['text' => $text, 'tone' => 'bad']; }
    protected static function neutral(string $text): array { return ['text' => $text, 'tone' => 'neutral']; }

    protected static function rwf(int|float|null $v): string
    {
        return number_format((float) $v) . ' RWF';
    }

    protected static function pct(int|float|null $v): string
    {
        return number_format((float) $v, 1) . '%';
    }
}
