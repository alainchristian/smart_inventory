<?php
namespace App\Services\Reports\Metrics\Inventory;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class InventoryDeadStock extends Metric
{
    protected string $id          = 'inventory_dead_stock';
    protected string $label       = 'Dead Stock';
    protected string $description = 'Products in stock with no sales in the last 90 days';
    protected string $domain      = 'inventory';
    protected array  $related     = ['inventory_abc_summary'];
    protected array  $viz         = ['kpi_card', 'table'];
    protected bool   $usesDates   = false;
    protected string $locations   = 'any';
    protected string $good        = 'down';
    protected ?string $periodNote = 'Stock as it is now; "not selling" means no sales in the last 90 days.';

    public function fetch(ReportContext $ctx): array
    {
        return $this->inventory()->getStockHealth($ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['dead_stock_count'] ?? 0), 'count', 'Products not selling')
            ->stat('Low on stock', (int) ($raw['low_stock_count'] ?? 0), 'count');
    }

    public function insight(MetricResult $r): ?array
    {
        $dead = (int) $r->value();
        if ($dead === 0) return self::ok('Every product in stock has sold in the last 90 days.');
        if ($dead <= 3)  return self::warn("{$dead} product(s) haven't sold in 90 days. Consider a markdown or moving them.");
        return self::bad("{$dead} products haven't sold in 90 days. That stock is tying up cash.");
    }
}
