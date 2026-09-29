<?php
namespace App\Services\Reports\Metrics\Loss;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class LossShrinkage extends Metric
{
    protected string $id          = 'loss_shrinkage';
    protected string $label       = 'Shrinkage Rate';
    protected string $description = 'Items damaged as a % of items received, over the last 90 days';
    protected string $domain      = 'loss';
    protected bool   $usesDates   = false;
    protected string $locations   = 'any';
    protected string $good        = 'down';
    protected ?string $periodNote = 'Always the last 90 days, whatever the report period.';

    public function fetch(ReportContext $ctx): array
    {
        return $this->inventory()->getShrinkageStats($ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((float) ($raw['shrinkage_pct'] ?? 0), 'percent', 'Shrinkage')
            ->stat('Items received', (int) ($raw['items_received_90d'] ?? 0), 'count')
            ->stat('Items damaged', (int) ($raw['items_damaged_90d'] ?? 0), 'count')
            ->stat('Estimated loss', (int) ($raw['estimated_loss'] ?? 0), 'money');
    }

    public function insight(MetricResult $r): ?array
    {
        $s = (float) $r->value();
        $p = number_format($s, 2) . '%';
        if ($s < 0.5) return self::ok("Only {$p} shrinkage.");
        if ($s < 2)   return self::warn("{$p} shrinkage ({$r->stats[1]['value']} items damaged). Review handling.");
        return self::bad("High {$p} shrinkage. Storage, handling and security need a review.");
    }
}
