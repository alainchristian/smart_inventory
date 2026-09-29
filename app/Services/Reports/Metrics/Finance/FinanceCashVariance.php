<?php
namespace App\Services\Reports\Metrics\Finance;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class FinanceCashVariance extends Metric
{
    protected string $id          = 'finance_cash_variance';
    protected string $label       = 'Cash Variance Summary';
    protected string $description = 'Cash shortages and surpluses found when registers were closed';
    protected string $domain      = 'finance';
    protected array  $viz         = ['kpi_card', 'table'];
    protected string $good        = 'down';

    public function fetch(ReportContext $ctx): array
    {
        return $this->finance()->getCashVarianceSummary($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['total_shortage'] ?? 0), 'money', 'Cash short')
            ->stat('Closed registers', (int) ($raw['total_sessions'] ?? 0), 'count')
            ->stat('Days short', (int) ($raw['sessions_with_shortage'] ?? 0), 'count')
            ->stat('Days over', (int) ($raw['sessions_with_surplus'] ?? 0), 'count')
            ->stat('Cash over', (int) ($raw['total_surplus'] ?? 0), 'money')
            ->table([
                'shop'     => ['Shop', 'text'],
                'sessions' => ['Closed registers', 'count'],
                'shortage' => ['Short', 'money'],
                'surplus'  => ['Over', 'money'],
            ], $raw['by_shop'] ?? [], ['sessions', 'shortage', 'surplus']);
    }

    public function insight(MetricResult $r): ?array
    {
        $short    = (int) $r->value();
        $sessions = (int) $r->stats[1]['value'];
        $total    = (int) $r->stats[0]['value'];
        if ($total === 0) return null;
        if ($short === 0) return self::ok("All {$total} closed registers balanced with no shortage.");
        $text = "{$sessions} of {$total} closed registers were short, " . self::rwf($short) . ' in total.';
        return $sessions === 1 ? self::warn($text) : self::bad($text);
    }
}
