<?php
namespace App\Services\Reports\Metrics\Inventory;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class InventoryCategoryConcentration extends Metric
{
    protected string $id          = 'inventory_category_concentration';
    protected string $label       = 'Inventory by Category';
    protected string $description = 'Stock value and share for each product category';
    protected string $domain      = 'inventory';
    protected array  $viz         = ['table', 'bar_chart'];
    protected string $defaultViz  = 'table';
    protected bool   $usesDates   = false;
    protected string $locations   = 'any';
    protected string $good        = 'neutral';

    public function fetch(ReportContext $ctx): array
    {
        return $this->inventory()->getCategoryConcentration($ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline(array_sum(array_column($raw, 'cost_value')), 'money', 'Stock at cost')
            ->table([
                'category_name' => ['Category', 'text'],
                'product_count' => ['Products', 'count'],
                'box_count'     => ['Boxes', 'count'],
                'cost_value'    => ['Value at cost', 'money'],
                'retail_value'  => ['Value at price', 'money'],
                'pct_of_total'  => ['Share', 'percent'],
            ], $raw, ['product_count', 'box_count', 'cost_value', 'retail_value'])
            ->chart('category_name', ['cost_value']);
    }

    public function insight(MetricResult $r): ?array
    {
        $top = collect($r->rows)->sortByDesc('cost_value')->first();
        if (! $top || ! $r->value()) return null;
        return (float) $top['pct_of_total'] >= 60
            ? self::warn("{$top['category_name']} holds " . self::pct($top['pct_of_total']) . ' of stock value. A lot rides on one category.')
            : self::neutral("{$top['category_name']} is the largest category at " . self::pct($top['pct_of_total']) . ' of stock value.');
    }
}
