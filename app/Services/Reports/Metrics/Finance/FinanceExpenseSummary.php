<?php
namespace App\Services\Reports\Metrics\Finance;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class FinanceExpenseSummary extends Metric
{
    protected string $id          = 'finance_expense_summary';
    protected string $label       = 'Expense Breakdown by Category';
    protected string $description = 'Operating expenses by category, with the cash / mobile money split';
    protected string $domain      = 'finance';
    protected array  $viz         = ['table', 'bar_chart', 'kpi_card'];
    protected string $defaultViz  = 'table';
    protected string $good        = 'down';

    public function fetch(ReportContext $ctx): array
    {
        return $this->finance()->getExpenseSummary($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        // The table used to render the summary keys as one row; the
        // per-category list lives under by_category.
        return MetricResult::make()
            ->headline((int) ($raw['total_expenses'] ?? 0), 'money', 'Expenses')
            ->stat('Cash', (int) ($raw['cash_expenses'] ?? 0), 'money')
            ->stat('Mobile money', (int) ($raw['momo_expenses'] ?? 0), 'money')
            ->stat('Entries', (int) ($raw['expense_count'] ?? 0), 'count')
            ->stat('Previous period', (int) ($raw['previous_total'] ?? 0), 'money')
            ->table([
                'category'     => ['Category', 'text'],
                'count'        => ['Entries', 'count'],
                'cash'         => ['Cash', 'money'],
                'momo'         => ['Mobile money', 'money'],
                'total'        => ['Total', 'money'],
                'pct_of_total' => ['Share', 'percent'],
            ], $raw['by_category'] ?? [], ['count', 'cash', 'momo', 'total'])
            ->chart('category', ['total']);
    }

    public function insight(MetricResult $r): ?array
    {
        $cur  = (int) $r->value();
        $prev = (int) $r->stats[3]['value'];
        if ($prev > 0) {
            $chg = round(($cur - $prev) / $prev * 100, 1);
            if ($chg > 20) return self::bad('Expenses up ' . self::pct($chg) . ' on the period before, at ' . self::rwf($cur) . '.');
            if ($chg > 0)  return self::warn('Expenses up ' . self::pct($chg) . ', at ' . self::rwf($cur) . '.');
            return self::ok('Expenses down ' . self::pct(abs($chg)) . ', at ' . self::rwf($cur) . '.');
        }
        return $cur ? self::neutral('Expenses of ' . self::rwf($cur) . ' this period.') : null;
    }
}
