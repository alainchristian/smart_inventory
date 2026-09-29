<?php
namespace App\Services\Reports;

use App\Services\Reports\Metrics\Metric;
use App\Services\Reports\Metrics\Finance;
use App\Services\Reports\Metrics\Inventory;
use App\Services\Reports\Metrics\Loss;
use App\Services\Reports\Metrics\Operations;
use App\Services\Reports\Metrics\Replenishment;
use App\Services\Reports\Metrics\Sales;
use App\Services\Reports\Metrics\Transfers;

/**
 * Every block a custom report can contain, in catalogue order. Metric
 * ids are stored in saved_reports.config, so never rename one.
 */
class MetricRegistry
{
    public const METRICS = [
        Sales\SalesRevenue::class,
        Sales\SalesGrossProfit::class,
        Sales\SalesTransactionCount::class,
        Sales\SalesAvgBasket::class,
        Sales\SalesByShop::class,
        Sales\SalesTopProducts::class,
        Sales\SalesPaymentMethods::class,
        Sales\SalesRevenueTrend::class,
        Sales\SalesVoided::class,

        Inventory\InventoryCostValue::class,
        Inventory\InventoryRetailValue::class,
        Inventory\InventoryFillRate::class,
        Inventory\InventoryAging::class,
        Inventory\InventoryDeadStock::class,
        Inventory\InventoryAbcSummary::class,
        Inventory\InventoryTopByValue::class,
        Inventory\InventoryCategoryConcentration::class,
        Inventory\InventoryByLocation::class,

        Replenishment\ReplenishmentCritical::class,
        Replenishment\ReplenishmentDaysOnHand::class,

        Loss\LossTotal::class,
        Loss\LossReturnRate::class,
        Loss\LossDamagedValue::class,
        Loss\LossShrinkage::class,
        Loss\LossByProduct::class,

        Transfers\TransfersKpis::class,
        Transfers\TransfersDiscrepancies::class,
        Transfers\TransfersRoutes::class,

        Operations\OpsLowStockCount::class,
        Operations\OpsDamagedPending::class,
        Operations\OpsStockTurnover::class,

        Finance\FinanceExpenseSummary::class,
        Finance\FinanceExpenseTrend::class,
        Finance\FinanceWithdrawalSummary::class,
        Finance\FinanceCashVariance::class,
        Finance\FinanceNetOperating::class,
    ];

    /** Free text; not a Metric because it has no data */
    public const TEXT_BLOCK = [
        'id'             => 'text_block',
        'label'          => 'Text / Narrative',
        'description'    => 'Free text: add context, a heading or notes',
        'domain'         => 'content',
        'viz_options'    => ['text'],
        'default_viz'    => 'text',
        'needs_dates'    => false,
        'needs_location' => false,
        'locations'      => 'none',
        'good'           => 'neutral',
    ];

    /** @var array<string, Metric>|null */
    private ?array $metrics = null;

    /** @return array<string, Metric> keyed by id */
    public function metrics(): array
    {
        if ($this->metrics === null) {
            $this->metrics = [];
            foreach (self::METRICS as $class) {
                $m = app($class);
                $this->metrics[$m->id()] = $m;
            }
        }

        return $this->metrics;
    }

    public function metric(string $id): ?Metric
    {
        return $this->metrics()[$id] ?? null;
    }

    /** Catalogue entries (meta arrays), text block last */
    public function catalogue(): array
    {
        return [
            ...array_values(array_map(fn (Metric $m) => $m->meta(), $this->metrics())),
            self::TEXT_BLOCK,
        ];
    }

    public function find(string $id): ?array
    {
        if ($id === 'text_block') {
            return self::TEXT_BLOCK;
        }

        return $this->metric($id)?->meta();
    }

    public function byDomain(): array
    {
        return collect($this->catalogue())->groupBy('domain')->toArray();
    }
}
