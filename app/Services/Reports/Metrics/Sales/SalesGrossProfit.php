<?php
namespace App\Services\Reports\Metrics\Sales;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class SalesGrossProfit extends Metric
{
    protected string $id          = 'sales_gross_profit';
    protected string $label       = 'Gross Profit & Margin';
    protected string $description = 'Revenue minus the cost of the goods sold, and the margin %';
    protected string $domain      = 'sales';

    public function fetch(ReportContext $ctx): array
    {
        return $this->sales()->getGrossProfitKpis($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['gross_profit'] ?? 0), 'money', 'Gross profit')
            ->stat('Margin', (float) ($raw['margin_pct'] ?? 0), 'percent')
            ->stat('Revenue', (int) ($raw['revenue'] ?? 0), 'money')
            ->stat('Cost of goods sold', (int) ($raw['total_cost'] ?? 0), 'money')
            ->stat('Returned', (int) ($raw['total_returned'] ?? 0), 'money');
    }

    public function insight(MetricResult $r): ?array
    {
        $m = (float) ($r->stats[0]['value'] ?? 0);
        if (! $r->value() && ! $m) return null;
        $p = self::pct($m);
        if ($m >= 35) return self::ok("Strong {$p} gross margin.");
        if ($m >= 20) return self::ok("Healthy {$p} gross margin on " . self::rwf($r->value()) . ' gross profit.');
        if ($m >= 10) return self::warn("Thin {$p} margin. Review supplier costs or prices.");
        return self::bad("Very low {$p} gross margin. Cost of goods and prices need a review.");
    }
}
