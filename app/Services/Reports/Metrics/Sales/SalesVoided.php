<?php
namespace App\Services\Reports\Metrics\Sales;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class SalesVoided extends Metric
{
    protected string $id          = 'sales_voided';
    protected string $label       = 'Voided Sales';
    protected string $description = 'Number and value of cancelled sales';
    protected string $domain      = 'sales';
    protected array  $viz         = ['kpi_card', 'table'];
    protected string $good        = 'down';

    public function fetch(ReportContext $ctx): array
    {
        return $this->sales()->getVoidedSalesStats($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['voided_count'] ?? 0), 'count', 'Voided sales')
            ->stat('Value voided', (int) ($raw['voided_revenue'] ?? 0), 'money')
            ->stat('Void rate', (float) ($raw['void_rate'] ?? 0), 'percent');
    }

    public function insight(MetricResult $r): ?array
    {
        $cnt = (int) $r->value();
        if ($cnt === 0) return self::ok('No voided sales.');
        if ($cnt <= 3)  return self::warn("{$cnt} sale(s) voided. Check they were handled correctly.");
        return self::bad("{$cnt} voided sales. A high void rate can point to pricing errors or staff issues.");
    }
}
