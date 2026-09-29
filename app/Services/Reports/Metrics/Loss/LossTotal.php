<?php
namespace App\Services\Reports\Metrics\Loss;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class LossTotal extends Metric
{
    protected string $id          = 'loss_total';
    protected string $label       = 'Total Losses';
    protected string $description = 'Refunds on returns plus the value of damaged goods';
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
            ->headline((int) ($raw['total_loss'] ?? (($raw['total_refunds'] ?? 0) + ($raw['damaged_loss'] ?? 0))), 'money', 'Total losses')
            ->stat('Refunds', (int) ($raw['total_refunds'] ?? 0), 'money')
            ->stat('Damaged goods', (int) ($raw['damaged_loss'] ?? 0), 'money')
            ->stat('Returns', (int) ($raw['returns_count'] ?? 0), 'count')
            ->stat('Damage records', (int) ($raw['damaged_count'] ?? 0), 'count');
    }

    public function insight(MetricResult $r): ?array
    {
        if (! $r->value()) return self::ok('No losses: no refunds and no damaged goods.');
        return self::warn('Losses of ' . self::rwf($r->value()) . ': ' . self::rwf($r->stats[0]['value']) . ' in refunds and ' . self::rwf($r->stats[1]['value']) . ' in damaged goods.');
    }
}
