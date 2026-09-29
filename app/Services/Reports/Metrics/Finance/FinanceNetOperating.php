<?php
namespace App\Services\Reports\Metrics\Finance;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class FinanceNetOperating extends Metric
{
    protected string $id          = 'finance_net_operating';
    protected string $label       = 'Net Operating Result';
    protected string $description = 'Revenue minus cost of goods minus operating expenses';
    protected string $domain      = 'finance';

    public function fetch(ReportContext $ctx): array
    {
        return $this->finance()->getNetOperatingResult($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['net_result'] ?? 0), 'money', 'Net result')
            ->stat('Net margin', (float) ($raw['net_margin_pct'] ?? 0), 'percent')
            ->stat('Revenue', (int) ($raw['revenue'] ?? 0), 'money')
            ->stat('Cost of goods sold', (int) ($raw['total_cost'] ?? 0), 'money')
            ->stat('Gross profit', (int) ($raw['gross_profit'] ?? 0), 'money')
            ->stat('Operating expenses', (int) ($raw['total_expenses'] ?? 0), 'money')
            ->table([
                'line'   => ['', 'text'],
                'amount' => ['Amount', 'money'],
            ], [
                ['line' => 'Revenue', 'amount' => (int) ($raw['revenue'] ?? 0)],
                ['line' => 'Cost of goods sold', 'amount' => -(int) ($raw['total_cost'] ?? 0)],
                ['line' => 'Gross profit', 'amount' => (int) ($raw['gross_profit'] ?? 0)],
                ['line' => 'Operating expenses', 'amount' => -(int) ($raw['total_expenses'] ?? 0)],
                ['line' => 'Net result', 'amount' => (int) ($raw['net_result'] ?? 0)],
            ]);
    }

    public function insight(MetricResult $r): ?array
    {
        $net = (int) $r->value();
        $m   = (float) $r->stats[0]['value'];
        if ($net === 0 && ! $r->stats[1]['value']) return null;
        if ($net > 0 && $m >= 15) return self::ok('Profitable: ' . self::rwf($net) . ' net, a ' . self::pct($m) . ' net margin.');
        if ($net > 0) return self::warn('Just profitable at ' . self::rwf($net) . ' (' . self::pct($m) . ' margin). Watch expenses.');
        return self::bad('A loss of ' . self::rwf(abs($net)) . ': costs and expenses were more than revenue.');
    }
}
