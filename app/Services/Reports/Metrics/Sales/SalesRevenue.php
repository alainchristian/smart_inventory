<?php
namespace App\Services\Reports\Metrics\Sales;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class SalesRevenue extends Metric
{
    protected string $id          = 'sales_revenue';
    protected string $label       = 'Total Revenue';
    protected string $description = 'Revenue for the period, with growth against the period before';
    protected string $domain      = 'sales';
    protected array  $related     = ['sales_by_shop', 'sales_payment_methods', 'sales_top_products'];
    protected array  $viz         = ['kpi_card', 'bar_chart'];

    public function fetch(ReportContext $ctx): array
    {
        return $this->sales()->getRevenueKpis($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        $r = MetricResult::make()
            ->headline((int) ($raw['total_revenue'] ?? 0), 'money', 'Revenue')
            ->stat('Sales', (int) ($raw['transactions_count'] ?? 0), 'count')
            ->stat('Average sale', (int) ($raw['avg_transaction_value'] ?? 0), 'money')
            ->stat('Discounts given', (int) ($raw['total_discount'] ?? 0), 'money');

        if (($raw['growth_percentage'] ?? null) !== null) {
            $r->stat('Growth', (float) $raw['growth_percentage'], 'percent');
        }

        return $r->table(
            ['period' => ['Period', 'text'], 'transactions' => ['Sales', 'count'], 'revenue' => ['Revenue', 'money']],
            [
                ['period' => 'Previous period', 'revenue' => (int) ($raw['previous_revenue'] ?? 0), 'transactions' => (int) ($raw['previous_transactions'] ?? 0)],
                ['period' => 'This period', 'revenue' => (int) ($raw['total_revenue'] ?? 0), 'transactions' => (int) ($raw['transactions_count'] ?? 0)],
            ],
        )->chart('period', ['revenue']);
    }

    public function insight(MetricResult $r): ?array
    {
        $growth = collect($r->stats)->firstWhere('label', 'Growth')['value'] ?? null;
        $rev    = self::rwf($r->value());
        if ($growth === null) return $r->value() ? self::neutral("Revenue of {$rev} (no sales in the period before to compare with).") : null;
        $g = self::pct(abs($growth));
        if ($growth > 20)  return self::ok("Revenue up {$g} on the period before, at {$rev}.");
        if ($growth > 5)   return self::ok("Revenue grew {$g} to {$rev}.");
        if ($growth >= 0)  return self::neutral("Revenue steady at {$rev}.");
        if ($growth > -10) return self::warn("Revenue dipped {$g} on the period before. Worth checking why.");
        return self::bad("Revenue fell {$g} on the period before. Review sales activity and pricing.");
    }
}
