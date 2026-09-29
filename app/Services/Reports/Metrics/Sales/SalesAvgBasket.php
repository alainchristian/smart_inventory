<?php
namespace App\Services\Reports\Metrics\Sales;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class SalesAvgBasket extends Metric
{
    protected string $id          = 'sales_avg_basket';
    protected string $label       = 'Average Basket Value';
    protected string $description = 'Average revenue per sale';
    protected string $domain      = 'sales';

    public function fetch(ReportContext $ctx): array
    {
        return $this->sales()->getRevenueKpis($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['avg_transaction_value'] ?? 0), 'money', 'Average sale')
            ->stat('Sales', (int) ($raw['transactions_count'] ?? 0), 'count')
            ->stat('Revenue', (int) ($raw['total_revenue'] ?? 0), 'money');
    }

    public function insight(MetricResult $r): ?array
    {
        $cnt = (int) $r->stats[0]['value'];
        return $cnt > 0 ? self::neutral("Across {$cnt} sales, the average sale was " . self::rwf($r->value()) . '.') : null;
    }
}
