<?php
namespace App\Services\Reports\Metrics\Inventory;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class InventoryFillRate extends Metric
{
    protected string $id          = 'inventory_fill_rate';
    protected string $label       = 'Portfolio Fill Rate';
    protected string $description = 'Items left in boxes as a % of the boxes\' full capacity';
    protected string $domain      = 'inventory';
    protected bool   $usesDates   = false;
    protected string $locations   = 'any';

    public function fetch(ReportContext $ctx): array
    {
        return ['fill_rate' => $this->inventory()->getPortfolioFillRate($ctx->location)];
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()->headline((float) ($raw['fill_rate'] ?? 0), 'percent', 'Fill rate');
    }

    public function insight(MetricResult $r): ?array
    {
        $rate = (float) $r->value();
        $p = self::pct($rate);
        if ($rate >= 90) return self::ok("{$p} fill rate: most boxes are still full or nearly full.");
        if ($rate >= 70) return self::warn("{$p} fill rate: many boxes are part-used.");
        return self::bad("Low {$p} fill rate: much of the stock is in opened, part-used boxes.");
    }
}
