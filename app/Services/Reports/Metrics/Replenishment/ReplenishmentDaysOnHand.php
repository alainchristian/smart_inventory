<?php
namespace App\Services\Reports\Metrics\Replenishment;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class ReplenishmentDaysOnHand extends Metric
{
    protected string $id          = 'replenishment_days_on_hand';
    protected string $label       = 'Days on Hand per Product';
    protected string $description = 'How many days each product\'s stock will last at the recent selling rate';
    protected string $domain      = 'replenishment';
    protected array  $viz         = ['table'];
    protected string $defaultViz  = 'table';
    protected bool   $usesDates   = false;
    protected string $good        = 'down';
    protected ?string $periodNote = 'Stock as it is now, and the average selling rate over the last 30 days.';

    public function fetch(ReportContext $ctx): array
    {
        return $this->inventory()->getDaysOnHandPerProduct($ctx->location, 50);
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        $rows = array_map(fn ($p) => $p + [
            'status' => ($p['is_critical'] ?? false) ? 'Critical' : (($p['is_low'] ?? false) ? 'Low' : 'OK'),
        ], $raw);

        return MetricResult::make()
            ->headline(count(array_filter($rows, fn ($p) => ($p['days_on_hand'] ?? 999) < 7)), 'count', 'Under a week of stock')
            ->table([
                'product_name'    => ['Product', 'text'],
                'box_count'       => ['Boxes left', 'count'],
                'avg_daily_sales' => ['Sold per day', 'count'],
                'days_on_hand'    => ['Days left', 'days'],
                'status'          => ['Status', 'text'],
            ], $rows);
    }

    public function insight(MetricResult $r): ?array
    {
        $urgent = (int) $r->value();
        if (! $r->rows) return null;
        if ($urgent === 0) return self::ok('Every listed product has more than a week of stock.');
        $min = collect($r->rows)->sortBy('days_on_hand')->first();
        return self::bad("{$urgent} product(s) have under a week of stock. Most urgent: {$min['product_name']} ({$min['days_on_hand']} days).");
    }
}
