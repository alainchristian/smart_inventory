<?php
namespace App\Services\Reports\Metrics\Operations;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class OpsLowStockCount extends Metric
{
    protected string $id          = 'ops_low_stock_count';
    protected string $label       = 'Low Stock Products';
    protected string $description = 'Products at or below their low-stock level';
    protected string $domain      = 'operations';
    protected bool   $usesDates   = false;
    protected string $locations   = 'any';
    protected string $good        = 'down';

    public function fetch(ReportContext $ctx): array
    {
        return $this->inventory()->getStockHealth($ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['low_stock_count'] ?? 0), 'count', 'Products low on stock')
            ->stat('Not selling (90 days)', (int) ($raw['dead_stock_count'] ?? 0), 'count');
    }

    public function insight(MetricResult $r): ?array
    {
        $low = (int) $r->value();
        if ($low === 0) return self::ok('No product is low on stock.');
        if ($low <= 5)  return self::warn("{$low} product(s) are low on stock. Plan a restock.");
        return self::bad("{$low} products are low on stock. Restock before they run out.");
    }
}
