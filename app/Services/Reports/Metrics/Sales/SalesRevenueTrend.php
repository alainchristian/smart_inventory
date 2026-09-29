<?php
namespace App\Services\Reports\Metrics\Sales;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class SalesRevenueTrend extends Metric
{
    protected string $id          = 'sales_revenue_trend';
    protected string $label       = 'Revenue Trend';
    protected string $description = 'Revenue day by day across the period';
    protected string $domain      = 'sales';
    protected array  $viz         = ['line_chart', 'bar_chart', 'table'];
    protected string $defaultViz  = 'line_chart';

    public function fetch(ReportContext $ctx): array
    {
        return $this->sales()->getRevenueTrend($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        $total = array_sum(array_column($raw, 'revenue'));

        return MetricResult::make()
            ->headline($total, 'money', 'Revenue')
            ->stat('Daily average', (int) round($total / max(count($raw), 1)), 'money')
            ->stat('Best day', (int) max(array_column($raw, 'revenue') ?: [0]), 'money')
            ->table([
                'date'         => ['Date', 'date'],
                'transactions' => ['Sales', 'count'],
                'revenue'      => ['Revenue', 'money'],
            ], $raw, ['transactions', 'revenue'])
            ->chart('date', ['revenue']);
    }
}
