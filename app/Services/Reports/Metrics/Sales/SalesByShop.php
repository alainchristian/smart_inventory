<?php
namespace App\Services\Reports\Metrics\Sales;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class SalesByShop extends Metric
{
    protected string $id          = 'sales_by_shop';
    protected string $label       = 'Revenue by Shop';
    protected string $description = 'Revenue, sales and share of total for each shop';
    protected string $domain      = 'sales';
    protected array  $viz         = ['bar_chart', 'table'];
    protected string $defaultViz  = 'bar_chart';
    protected string $good        = 'neutral';

    public function fetch(ReportContext $ctx): array
    {
        $rows = $this->sales()->getShopPerformance($ctx->from, $ctx->to);
        if ($shopId = $ctx->shopId()) {
            $rows = array_values(array_filter($rows, fn ($r) => (int) $r['shop_id'] === $shopId));
        }
        return $rows;
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        $r = MetricResult::make()
            ->headline(array_sum(array_column($raw, 'revenue')), 'money', 'Revenue')
            ->table([
                'shop_name'       => ['Shop', 'text'],
                'transactions'    => ['Sales', 'count'],
                'avg_transaction' => ['Average sale', 'money'],
                'revenue'         => ['Revenue', 'money'],
                'revenue_share'   => ['Share', 'percent'],
                'growth'          => ['Growth', 'percent'],
            ], $raw, ['transactions', 'revenue'])
            ->chart('shop_name', ['revenue']);

        if ($ctx->shopId()) {
            $r->note('Share is of all shops together.');
        }
        return $r;
    }

    public function insight(MetricResult $r): ?array
    {
        if (count($r->rows) < 2 || ! $r->value()) return null;
        $top = collect($r->rows)->sortByDesc('revenue')->first();
        return self::neutral("{$top['shop_name']} brought in " . self::pct($top['revenue_share']) . ' of revenue, the most of any shop.');
    }
}
