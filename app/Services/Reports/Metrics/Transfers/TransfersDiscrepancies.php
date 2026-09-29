<?php
namespace App\Services\Reports\Metrics\Transfers;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class TransfersDiscrepancies extends Metric
{
    protected string $id          = 'transfers_discrepancies';
    protected string $label       = 'Transfer Discrepancies';
    protected string $description = 'Transfers received with missing or damaged boxes';
    protected string $domain      = 'transfers';
    protected array  $viz         = ['table', 'kpi_card'];
    protected string $defaultViz  = 'table';
    protected string $locations   = 'none';
    protected string $good        = 'down';

    public function fetch(ReportContext $ctx): array
    {
        return $this->transfers()->getRecentDiscrepancies($ctx->from, $ctx->to, 20);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline(count($raw), 'count', 'Transfers with a discrepancy')
            ->table([
                'transfer_number'   => ['Transfer', 'text'],
                'from_warehouse'    => ['From', 'text'],
                'to_shop'           => ['To', 'text'],
                'received_at'       => ['Received', 'datetime'],
                'discrepancy_notes' => ['Notes', 'text'],
            ], $raw);
    }

    public function insight(MetricResult $r): ?array
    {
        $cnt = (int) $r->value();
        return $cnt === 0
            ? self::ok('Every transfer arrived as packed.')
            : self::warn("{$cnt} transfer(s) arrived with a discrepancy. Reconcile them before stock records drift.");
    }
}
