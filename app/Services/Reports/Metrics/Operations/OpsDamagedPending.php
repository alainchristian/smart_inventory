<?php
namespace App\Services\Reports\Metrics\Operations;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class OpsDamagedPending extends Metric
{
    protected string $id          = 'ops_damaged_pending';
    protected string $label       = 'Damaged Goods — No Decision';
    protected string $description = 'Damaged goods still waiting for a decision (repair, write off…)';
    protected string $domain      = 'operations';
    protected bool   $usesDates   = false;
    protected string $locations   = 'any';
    protected string $good        = 'down';
    protected ?string $periodNote = 'Everything still waiting now, whatever the report period.';

    public function fetch(ReportContext $ctx): array
    {
        $q = \App\Models\DamagedGood::query()->where('disposition', 'pending');
        if ($id = $ctx->shopId()) {
            $q->where('location_type', 'shop')->where('location_id', $id);
        } elseif ($id = $ctx->warehouseId()) {
            $q->where('location_type', 'warehouse')->where('location_id', $id);
        }

        $row = $q->selectRaw('COUNT(*) AS n, COALESCE(SUM(estimated_loss), 0) AS loss, MIN(recorded_at) AS oldest')->first();

        return ['count' => (int) $row->n, 'estimated_loss' => (int) $row->loss, 'oldest' => $row->oldest];
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        $r = MetricResult::make()
            ->headline((int) $raw['count'], 'count', 'Awaiting a decision')
            ->stat('Estimated loss', (int) ($raw['estimated_loss'] ?? 0), 'money');

        if (! empty($raw['oldest'])) {
            $r->stat('Oldest recorded', local_time($raw['oldest'])->toDateString(), 'date');
        }
        return $r;
    }

    public function insight(MetricResult $r): ?array
    {
        $cnt = (int) $r->value();
        return $cnt === 0
            ? self::ok('No damaged goods are waiting for a decision.')
            : self::warn("{$cnt} damaged item record(s) need a decision.");
    }
}
