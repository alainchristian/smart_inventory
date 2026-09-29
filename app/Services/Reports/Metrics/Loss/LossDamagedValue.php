<?php
namespace App\Services\Reports\Metrics\Loss;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class LossDamagedValue extends Metric
{
    protected string $id          = 'loss_damaged_value';
    protected string $label       = 'Damaged Goods Loss';
    protected string $description = 'Estimated value of goods recorded as damaged';
    protected string $domain      = 'loss';
    protected array  $viz         = ['kpi_card', 'table'];
    protected string $good        = 'down';

    public function fetch(ReportContext $ctx): array
    {
        return $this->loss()->getLossKpis($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['damaged_loss'] ?? 0), 'money', 'Damaged goods')
            ->stat('Damage records', (int) ($raw['damaged_count'] ?? 0), 'count');
    }

    public function insight(MetricResult $r): ?array
    {
        return $r->value()
            ? self::warn(self::rwf($r->value()) . ' lost to damaged goods. Review handling and storage.')
            : self::ok('No damaged goods recorded.');
    }
}
