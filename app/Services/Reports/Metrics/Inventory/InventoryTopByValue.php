<?php
namespace App\Services\Reports\Metrics\Inventory;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class InventoryTopByValue extends Metric
{
    protected string $id          = 'inventory_top_by_value';
    protected string $label       = 'Top Products by Capital Value';
    protected string $description = 'Products with the most money tied up in stock';
    protected string $domain      = 'inventory';
    protected array  $viz         = ['table', 'bar_chart'];
    protected string $defaultViz  = 'table';
    protected bool   $usesDates   = false;
    protected string $locations   = 'any';
    protected string $good        = 'neutral';

    public function fetch(ReportContext $ctx): array
    {
        return $this->inventory()->getTopProductsByValue($ctx->location, 20);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline(array_sum(array_column($raw, 'purchase_value')), 'money', 'Stock at cost (listed products)')
            ->table([
                'product_name'   => ['Product', 'text'],
                'box_count'      => ['Boxes', 'count'],
                'location_count' => ['Locations', 'count'],
                'purchase_value' => ['Value at cost', 'money'],
                'retail_value'   => ['Value at price', 'money'],
            ], $raw, ['box_count', 'purchase_value', 'retail_value'])
            ->chart('product_name', ['purchase_value']);
    }
}
