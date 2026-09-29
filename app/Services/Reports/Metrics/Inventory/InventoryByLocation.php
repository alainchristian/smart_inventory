<?php
namespace App\Services\Reports\Metrics\Inventory;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class InventoryByLocation extends Metric
{
    protected string $id          = 'inventory_by_location';
    protected string $label       = 'Stock Value by Location';
    protected string $description = 'Stock value at cost in each warehouse and shop';
    protected string $domain      = 'inventory';
    protected array  $viz         = ['table', 'bar_chart'];
    protected string $defaultViz  = 'table';
    protected bool   $usesDates   = false;
    protected string $locations   = 'none';
    protected string $good        = 'neutral';

    public function fetch(ReportContext $ctx): array
    {
        return $this->inventory()->getInventoryByLocation();
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        // The service returns {warehouses: [...], shops: [...]}; the old
        // viewer expected a flat list, so this block used to render empty.
        $rows = array_map(fn ($r) => [
            'location_name' => $r['location_name'],
            'location_type' => $r['location_type'] === 'warehouse' ? 'Warehouse' : 'Shop',
            'box_count'     => (int) $r['box_count'],
            'value'         => (int) $r['value'],
        ], array_merge($raw['warehouses'] ?? [], $raw['shops'] ?? []));

        $total = array_sum(array_column($rows, 'value'));
        foreach ($rows as &$row) {
            $row['share'] = $total > 0 ? round($row['value'] / $total * 100, 1) : 0;
        }
        unset($row);

        return MetricResult::make()
            ->headline($total, 'money', 'Stock at cost')
            ->table([
                'location_name' => ['Location', 'text'],
                'location_type' => ['Type', 'text'],
                'box_count'     => ['Boxes', 'count'],
                'value'         => ['Value at cost', 'money'],
                'share'         => ['Share', 'percent'],
            ], $rows, ['box_count', 'value'])
            ->chart('location_name', ['value']);
    }
}
