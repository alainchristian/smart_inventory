<?php
namespace App\Services\Reports\Metrics\Loss;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class LossReturnRate extends Metric
{
    protected string $id          = 'loss_return_rate';
    protected string $label       = 'Return Rate';
    protected string $description = 'Returns as a percentage of sales';
    protected string $domain      = 'loss';
    protected array  $related     = ['loss_by_product'];
    protected string $good        = 'down';

    public function fetch(ReportContext $ctx): array
    {
        return $this->loss()->getLossKpis($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((float) ($raw['return_rate'] ?? 0), 'percent', 'Return rate')
            ->stat('Returns', (int) ($raw['returns_count'] ?? 0), 'count')
            ->stat('Refunded', (int) ($raw['total_refunds'] ?? 0), 'money');
    }

    public function insight(MetricResult $r): ?array
    {
        $rate = (float) $r->value();
        $cnt  = (int) $r->stats[0]['value'];
        $p    = self::pct($rate);
        if ($rate < 2) return self::ok("Low {$p} return rate.");
        if ($rate < 5) return self::warn("{$p} return rate ({$cnt} returns). Keep an eye on it.");
        return self::bad("High {$p} return rate ({$cnt} returns). Check product quality and what customers were told.");
    }
}
