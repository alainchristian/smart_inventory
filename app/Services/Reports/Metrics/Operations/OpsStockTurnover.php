<?php
namespace App\Services\Reports\Metrics\Operations;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\MetricResult;
use App\Services\Reports\ReportContext;

class OpsStockTurnover extends Metric
{
    protected string $id          = 'ops_stock_turnover';
    protected string $label       = 'Stock Turnover Ratio';
    protected string $description = 'How many times a year the stock sells through (cost of goods ÷ average stock)';
    protected string $domain      = 'operations';
    protected bool   $usesDates   = false;
    protected string $locations   = 'shop';
    protected ?string $periodNote = 'Based on the last 12 months, whatever the report period.';

    public function fetch(ReportContext $ctx): array
    {
        return ['turnover_rate' => $this->inventory()->calculateStockTurnover($ctx->location)];
    }

    public function present(array $raw, ReportContext $ctx): MetricResult
    {
        $rate = round((float) ($raw['turnover_rate'] ?? 0), 2);
        $r = MetricResult::make()->headline($rate, 'ratio', 'Turnover per year');
        if ($rate > 0) {
            $r->stat('Days to sell through', (int) round(365 / $rate), 'days');
        }
        return $r;
    }

    public function insight(MetricResult $r): ?array
    {
        $rate = (float) $r->value();
        if ($rate <= 0) return null;
        $days = (int) round(365 / $rate);
        if ($rate >= 4) return self::ok("Stock sells through about every {$days} days.");
        if ($rate >= 2) return self::neutral("Stock sells through about every {$days} days.");
        return self::warn("Stock takes about {$days} days to sell through. Money stays tied up in stock for a long time.");
    }
}
