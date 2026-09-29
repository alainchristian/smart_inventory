<?php
namespace App\Services\Reports\Metrics\Finance;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class FinanceWithdrawalSummary extends Metric
{
    protected string $id          = 'finance_withdrawal_summary';
    protected string $label       = 'Owner Withdrawals';
    protected string $description = 'Money the owner took out, by shop and by cash / mobile money';
    protected string $domain      = 'finance';
    protected array  $viz         = ['kpi_card', 'table'];
    protected string $good        = 'neutral';

    public function fetch(ReportContext $ctx): array
    {
        return $this->finance()->getWithdrawalSummary($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['total_withdrawals'] ?? 0), 'money', 'Withdrawn')
            ->stat('Cash', (int) ($raw['cash_withdrawals'] ?? 0), 'money')
            ->stat('Mobile money', (int) ($raw['momo_withdrawals'] ?? 0), 'money')
            ->stat('Withdrawals', (int) ($raw['withdrawal_count'] ?? 0), 'count')
            ->table([
                'shop'  => ['Shop', 'text'],
                'count' => ['Withdrawals', 'count'],
                'total' => ['Amount', 'money'],
            ], $raw['by_shop'] ?? [], ['count', 'total']);
    }

    public function insight(MetricResult $r): ?array
    {
        return $r->value()
            ? self::neutral(self::rwf($r->value()) . ' withdrawn, ' . self::rwf($r->stats[0]['value']) . ' of it in cash.')
            : null;
    }
}
