<?php
namespace App\Services\Reports\Metrics\Sales;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class SalesPaymentMethods extends Metric
{
    protected string $id          = 'sales_payment_methods';
    protected string $label       = 'Payment Method Breakdown';
    protected string $description = 'How customers paid: cash, mobile money, card, credit…';
    protected string $domain      = 'sales';
    protected array  $viz         = ['table', 'bar_chart'];
    protected string $defaultViz  = 'table';
    protected string $good        = 'neutral';

    public function fetch(ReportContext $ctx): array
    {
        return $this->sales()->getPaymentMethodBreakdown($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline(array_sum(array_column($raw, 'revenue')), 'money', 'Collected')
            ->table([
                'label'         => ['Method', 'text'],
                'count'         => ['Payments', 'count'],
                'revenue'       => ['Amount', 'money'],
                'revenue_share' => ['Share', 'percent'],
            ], $raw, ['count', 'revenue'])
            ->chart('label', ['revenue']);
    }

    public function insight(MetricResult $r): ?array
    {
        $top = collect($r->rows)->sortByDesc('revenue')->first();
        return $top && $r->value() ? self::neutral("{$top['label']} is the most used method, at " . self::pct($top['revenue_share']) . ' of the amount paid.') : null;
    }
}
