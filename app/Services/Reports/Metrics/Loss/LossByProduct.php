<?php
namespace App\Services\Reports\Metrics\Loss;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class LossByProduct extends Metric
{
    protected string $id          = 'loss_by_product';
    protected string $label       = 'Problem Products (Returns + Damage)';
    protected string $description = 'Products losing the most money to returns and damage';
    protected string $domain      = 'loss';
    protected array  $viz         = ['table', 'bar_chart'];
    protected string $defaultViz  = 'table';
    protected string $good        = 'down';

    public function fetch(ReportContext $ctx): array
    {
        return $this->loss()->getProblemProducts($ctx->from, $ctx->to, $ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline(array_sum(array_column($raw, 'total_loss')), 'money', 'Losses (listed products)')
            ->table([
                'product_name'  => ['Product', 'text'],
                'return_count'  => ['Returns', 'count'],
                'refund_amount' => ['Refunded', 'money'],
                'damage_count'  => ['Damage records', 'count'],
                'damage_loss'   => ['Damage loss', 'money'],
                'total_loss'    => ['Total loss', 'money'],
            ], $raw, ['return_count', 'refund_amount', 'damage_count', 'damage_loss', 'total_loss'])
            ->chart('product_name', ['total_loss']);
    }

    public function insight(MetricResult $r): ?array
    {
        $top = $r->rows[0] ?? null;
        return $top ? self::warn("{$top['product_name']} lost the most: " . self::rwf($top['total_loss']) . '.') : self::ok('No product had returns or damage.');
    }
}
