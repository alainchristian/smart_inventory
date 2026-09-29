<?php
namespace App\Services\Reports\Metrics\Replenishment;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class ReplenishmentCritical extends Metric
{
    protected string $id          = 'replenishment_critical';
    protected string $label       = 'Critical Stock (≤7 days)';
    protected string $description = 'Products with a week or less of stock left at the recent selling rate';
    protected string $domain      = 'replenishment';
    protected array  $viz         = ['table', 'kpi_card'];
    protected string $defaultViz  = 'table';
    protected bool   $usesDates   = false;
    protected string $good        = 'down';
    protected ?string $periodNote = 'Stock as it is now, and the average selling rate over the last 30 days.';

    public function fetch(ReportContext $ctx): array
    {
        $all = $this->inventory()->getDaysOnHandPerProduct($ctx->location, 200);
        return array_values(array_filter($all, fn ($p) => ($p['is_critical'] ?? false) === true));
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline(count($raw), 'count', 'Products running out')
            ->table([
                'product_name'    => ['Product', 'text'],
                'box_count'       => ['Boxes left', 'count'],
                'avg_daily_sales' => ['Sold per day', 'count'],
                'days_on_hand'    => ['Days left', 'days'],
            ], $raw);
    }

    public function insight(MetricResult $r): ?array
    {
        $cnt = (int) $r->value();
        if ($cnt === 0) return self::ok('No product is down to its last week of stock.');
        $first = $r->rows[0]['product_name'] ?? '';
        return self::bad($cnt === 1
            ? "{$first} has a week or less of stock left. Reorder now."
            : "{$cnt} products have a week or less of stock left. Reorder now.");
    }
}
