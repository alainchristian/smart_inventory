<?php
namespace App\Services\Reports\Metrics\Inventory;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class InventoryAbcSummary extends Metric
{
    protected string $id          = 'inventory_abc_summary';
    protected string $label       = 'ABC Classification';
    protected string $description = 'Products in stock grouped by how much of recent revenue they bring in';
    protected string $domain      = 'inventory';
    protected array  $viz         = ['table', 'kpi_card'];
    protected string $defaultViz  = 'table';
    protected bool   $usesDates   = false;
    protected string $locations   = 'any';
    protected string $good        = 'neutral';
    protected ?string $periodNote = 'Stock as it is now; revenue share uses sales from the last 90 days.';

    private const CLASSES = [
        'A'    => 'A: top sellers',
        'B'    => 'B: steady sellers',
        'C'    => 'C: slow sellers',
        'Dead' => 'Not selling',
    ];

    public function fetch(ReportContext $ctx): array
    {
        return $this->inventory()->getVelocityClassification($ctx->location);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        $summary = $raw['summary'] ?? [];
        $rows = [];
        foreach (self::CLASSES as $key => $label) {
            $products = $raw[$key] ?? [];
            $rows[] = [
                'class'       => $label,
                'products'    => (int) ($summary["{$key}_count"] ?? count($products)),
                'cost_value'  => (int) ($summary["{$key}_cost_value"] ?? array_sum(array_column($products, 'cost_value'))),
                'revenue_pct' => round(array_sum(array_column($products, 'revenue_pct')), 1),
                'examples'    => implode(', ', array_slice(array_column($products, 'product_name'), 0, 3)),
            ];
        }

        return MetricResult::make()
            ->headline($rows[0]['products'], 'count', 'Class A products')
            ->stat('Class B', $rows[1]['products'], 'count')
            ->stat('Class C', $rows[2]['products'], 'count')
            ->stat('Not selling', $rows[3]['products'], 'count')
            ->table([
                'class'       => ['Class', 'text'],
                'products'    => ['Products', 'count'],
                'cost_value'  => ['Stock at cost', 'money'],
                'revenue_pct' => ['Share of revenue', 'percent'],
                'examples'    => ['Examples', 'text'],
            ], $rows, ['products', 'cost_value']);
    }

    public function insight(MetricResult $r): ?array
    {
        $total = array_sum(array_column($r->rows, 'products'));
        if ($total === 0) return null;
        $a = (int) $r->value();
        return self::neutral("{$a} of {$total} products " . ($a === 1 ? 'is' : 'are') . " class A and bring in most of the revenue. Don't let them run out.");
    }
}
