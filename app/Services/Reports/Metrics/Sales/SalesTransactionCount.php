<?php
namespace App\Services\Reports\Metrics\Sales;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class SalesTransactionCount extends Metric
{
    protected string $id          = 'sales_transaction_count';
    protected string $label       = 'Transaction Count';
    protected string $description = 'Number of completed sales';
    protected string $domain      = 'sales';

    public function fetch(ReportContext $ctx): array
    {
        return $this->sales()->getRevenueKpis($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['transactions_count'] ?? 0), 'count', 'Sales')
            ->stat('Previous period', (int) ($raw['previous_transactions'] ?? 0), 'count')
            ->stat('Average sale', (int) ($raw['avg_transaction_value'] ?? 0), 'money');
    }

    public function insight(MetricResult $r): ?array
    {
        $cnt  = (int) $r->value();
        $prev = (int) $r->stats[0]['value'];
        if ($prev > 0) {
            $chg = round(($cnt - $prev) / $prev * 100, 1);
            return $chg >= 0
                ? self::ok('Sales up ' . self::pct($chg) . " on the period before ({$cnt} vs {$prev}).")
                : self::warn('Sales down ' . self::pct(abs($chg)) . " on the period before ({$cnt} vs {$prev}).");
        }
        return $cnt ? self::neutral(number_format($cnt) . ' sales this period.') : null;
    }
}
