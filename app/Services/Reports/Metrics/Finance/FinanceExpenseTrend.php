<?php
namespace App\Services\Reports\Metrics\Finance;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class FinanceExpenseTrend extends Metric
{
    protected string $id          = 'finance_expense_trend';
    protected string $label       = 'Expense Trend Over Time';
    protected string $description = 'Operating expenses day by day';
    protected string $domain      = 'finance';
    protected array  $viz         = ['line_chart', 'bar_chart', 'table'];
    protected string $defaultViz  = 'line_chart';
    protected string $good        = 'down';

    public function fetch(ReportContext $ctx): array
    {
        return $this->finance()->getExpenseTrend($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        $total = array_sum(array_column($raw, 'total_expenses'));
        $days  = (int) \Carbon\Carbon::parse($ctx->from)->diffInDays(\Carbon\Carbon::parse($ctx->to)) + 1;

        return MetricResult::make()
            ->headline($total, 'money', 'Expenses')
            ->stat('Daily average', (int) round($total / max($days, 1)), 'money')
            ->stat('Days with expenses', count($raw), 'count')
            ->table([
                'date'           => ['Date', 'date'],
                'cash_expenses'  => ['Cash', 'money'],
                'momo_expenses'  => ['Mobile money', 'money'],
                'total_expenses' => ['Total', 'money'],
            ], $raw, ['cash_expenses', 'momo_expenses', 'total_expenses'])
            ->chart('date', ['total_expenses']);
    }
}
