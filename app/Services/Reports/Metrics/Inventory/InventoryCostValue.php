<?php
namespace App\Services\Reports\Metrics\Inventory;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class InventoryCostValue extends Metric
{
    protected string $id          = 'inventory_cost_value';
    protected string $label       = 'Inventory Cost Value';
    protected string $description = 'What the stock on hand cost you (capital tied up in stock)';
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
            ->headline((int) ($raw['purchase_value'] ?? 0), 'money', 'Stock at cost')
            ->stat('At selling price', (int) ($raw['retail_value'] ?? 0), 'money')
            ->stat('Potential profit', (int) ($raw['potential_profit'] ?? 0), 'money')
            ->stat('Products', (int) ($raw['product_count'] ?? 0), 'count')
            ->stat('Boxes (full / opened)', (int) ($raw['box_full_count'] ?? 0) + (int) ($raw['box_partial_count'] ?? 0), 'count');
    }

    public function insight(MetricResult $r): ?array
    {
        return $r->value() ? self::neutral('Stock cost ' . self::rwf($r->value()) . ' and would bring in ' . self::rwf($r->stats[1]['value']) . ' more than that if sold at full price.') : null;
    }
}
