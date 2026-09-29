<?php
namespace App\Services\Reports\Metrics\Sales;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class SalesTopProducts extends Metric
{
    protected string $id          = 'sales_top_products';
    protected string $label       = 'Top Products by Revenue';
    protected string $description = 'Best-selling products, with gross profit and margin';
    protected string $domain      = 'sales';
    protected array  $viz         = ['table', 'bar_chart'];
    protected string $defaultViz  = 'table';
    protected string $good        = 'neutral';

    public function fetch(ReportContext $ctx): array
    {
        return $this->sales()->getTopProducts($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        // The service keeps full-box and loose sales of a product as separate
        // rows; without a label the same product looked listed twice.
        $names = array_count_values(array_column($raw, 'product_name'));
        $raw = array_map(function ($row) use ($names) {
            if (($names[$row['product_name']] ?? 0) > 1 && array_key_exists('is_full_box', $row)) {
                $row['product_name'] .= $row['is_full_box'] ? ' (by box)' : ' (loose)';
            }
            return $row;
        }, $raw);

        return MetricResult::make()
            ->headline(array_sum(array_column($raw, 'revenue')), 'money', 'Revenue (listed products)')
            ->table([
                'product_name'  => ['Product', 'text'],
                'quantity_sold' => ['Items sold', 'count'],
                'revenue'       => ['Revenue', 'money'],
                'gross_profit'  => ['Gross profit', 'money'],
                'margin_pct'    => ['Margin', 'percent'],
                'revenue_share' => ['Share', 'percent'],
            ], $raw, ['quantity_sold', 'revenue', 'gross_profit'])
            ->chart('product_name', ['revenue']);
    }

    public function insight(MetricResult $r): ?array
    {
        $top = $r->rows[0] ?? null;
        return $top ? self::neutral("Top seller: {$top['product_name']} with " . self::rwf($top['revenue']) . '. Keep it in stock.') : null;
    }
}
