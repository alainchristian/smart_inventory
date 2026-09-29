<?php
namespace App\Services\Reports\Metrics\Transfers;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class TransfersKpis extends Metric
{
    protected string $id          = 'transfers_kpis';
    protected string $label       = 'Transfer Performance KPIs';
    protected string $description = 'Number of transfers, time to complete and discrepancy rate';
    protected string $domain      = 'transfers';
    protected array  $related     = ['transfers_routes', 'transfers_discrepancies'];
    protected string $locations   = 'none';
    protected string $good        = 'neutral';

    public function fetch(ReportContext $ctx): array
    {
        return $this->transfers()->getTransferKpis($ctx->from, $ctx->to, null);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        return MetricResult::make()
            ->headline((int) ($raw['total_transfers'] ?? 0), 'count', 'Transfers')
            ->stat('Average hours to complete', round((float) ($raw['avg_completion_hours'] ?? 0), 1), 'count')
            ->stat('With a discrepancy', (int) ($raw['discrepancy_count'] ?? 0), 'count')
            ->stat('Discrepancy rate', (float) ($raw['discrepancy_rate'] ?? 0), 'percent')
            ->stat('In transit now', (int) ($raw['in_transit_count'] ?? 0), 'count');
    }

    public function insight(MetricResult $r): ?array
    {
        $total = (int) $r->value();
        $dr    = (float) $r->stats[2]['value'];
        if ($total === 0) return self::neutral('No transfers in this period.');
        if ($dr < 2)  return self::ok("{$total} transfers, only " . self::pct($dr) . ' with a discrepancy.');
        if ($dr < 10) return self::warn(self::pct($dr) . " of {$total} transfers had a discrepancy. Review packing.");
        return self::bad(self::pct($dr) . ' of transfers had a discrepancy. Packing and shipping need attention.');
    }
}
