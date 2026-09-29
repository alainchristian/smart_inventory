<?php
namespace App\Services\Reports\Metrics\Inventory;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class InventoryAging extends Metric
{
    protected string $id          = 'inventory_aging';
    protected string $label       = 'Stock Aging Analysis';
    protected string $description = 'Boxes grouped by how long they have been in stock';
    protected string $domain      = 'inventory';
    protected array  $viz         = ['table', 'bar_chart'];
    protected string $defaultViz  = 'table';
    protected bool   $usesDates   = false;
    protected string $locations   = 'any';
    protected string $good        = 'neutral';

    public function fetch(ReportContext $ctx): array
    {
        return $this->inventory()->getAgingAnalysis($ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline(array_sum(array_column($raw, 'value')), 'money', 'Stock at cost')
            ->table([
                'age_bracket' => ['Age', 'text'],
                'box_count'   => ['Boxes', 'count'],
                'value'       => ['Value at cost', 'money'],
            ], $raw, ['box_count', 'value'])
            ->chart('age_bracket', ['value']);
    }

    public function insight(MetricResult $r): ?array
    {
        $old = collect($r->rows)->filter(fn ($row) => str_starts_with((string) $row['age_bracket'], '90'))->sum('value');
        if (! $r->value()) return null;
        return $old > 0
            ? self::warn(self::rwf($old) . ' of stock has been held for over 90 days.')
            : self::ok('No stock older than 90 days.');
    }
}
