<?php
namespace App\Services\Reports\Metrics\Inventory;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class InventoryRetailValue extends Metric
{
    protected string $id          = 'inventory_retail_value';
    protected string $label       = 'Inventory Retail Value';
    protected string $description = 'Stock on hand valued at selling price';
    protected string $domain      = 'inventory';
    protected bool   $usesDates   = false;
    protected string $locations   = 'any';
    protected string $good        = 'neutral';

    public function fetch(ReportContext $ctx): array
    {
        return $this->inventory()->getInventoryKpis($ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['retail_value'] ?? 0), 'money', 'Stock at selling price')
            ->stat('At cost', (int) ($raw['purchase_value'] ?? 0), 'money')
            ->stat('Potential profit', (int) ($raw['potential_profit'] ?? 0), 'money')
            ->stat('Products', (int) ($raw['product_count'] ?? 0), 'count');
    }
}
