<?php
namespace App\Services\Reports\Metrics\Transfers;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class TransfersRoutes extends Metric
{
    protected string $id          = 'transfers_routes';
    protected string $label       = 'Transfer Volume by Route';
    protected string $description = 'Transfers on each warehouse-to-shop route';
    protected string $domain      = 'transfers';
    protected array  $viz         = ['table', 'bar_chart'];
    protected string $defaultViz  = 'table';
    protected string $locations   = 'any';
    protected string $good        = 'neutral';

    public function fetch(ReportContext $ctx): array
    {
        $rows = $this->transfers()->getTransferRoutes($ctx->from, $ctx->to);
        if ($id = $ctx->shopId()) {
            $rows = array_filter($rows, fn ($r) => (int) $r['shop_id'] === $id);
        } elseif ($id = $ctx->warehouseId()) {
            $rows = array_filter($rows, fn ($r) => (int) $r['warehouse_id'] === $id);
        }
        return array_values($rows);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        $rows = array_map(fn ($r) => [
            'route'             => $r['warehouse_name'] . ' → ' . $r['shop_name'],
            'transfer_count'    => (int) $r['transfer_count'],
            'discrepancy_count' => (int) $r['discrepancy_count'],
        ], $raw);

        return MetricResult::make()
            ->headline(array_sum(array_column($rows, 'transfer_count')), 'count', 'Transfers')
            ->table([
                'route'             => ['Route', 'text'],
                'transfer_count'    => ['Transfers', 'count'],
                'discrepancy_count' => ['With a discrepancy', 'count'],
            ], $rows, ['transfer_count', 'discrepancy_count'])
            ->chart('route', ['transfer_count']);
    }
}
