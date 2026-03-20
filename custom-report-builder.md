# Custom Report Builder — Metric Block System

> Read this entire file before writing a single line of code.
> Complete every step in the written order. Do not skip ahead.
> When all steps pass verification, update CLAUDE.md as described in Step 9.

---

## What you are building

A system that lets the owner and admin compose, save, and re-run custom reports
by combining pre-defined metric blocks from a catalogue of ~30 blocks backed
by the existing analytics services. No raw SQL is exposed to the user.

### Three pages

| URL | Purpose |
|-----|---------|
| `/owner/reports/custom` | Report library — list of saved reports |
| `/owner/reports/custom/builder` | Builder — compose a new report |
| `/owner/reports/custom/{id}` | Viewer — run and display a saved report |

### Files to read before starting

Read every one of these in full. Do not guess at patterns.

```
app/Services/Analytics/SalesAnalyticsService.php
app/Services/Analytics/InventoryAnalyticsService.php
app/Services/Analytics/LossAnalyticsService.php
app/Services/Analytics/TransferAnalyticsService.php
app/Livewire/Owner/Reports/SalesAnalytics.php
resources/views/livewire/owner/reports/sales-analytics.blade.php
resources/views/livewire/products/product-list.blade.php
routes/web.php
resources/views/livewire/layout/sidebar.blade.php
```

### Design system — use only these tokens, never hardcoded colours

```
var(--surface)  var(--surface2)  var(--surface3)
var(--border)
var(--text)  var(--text-sub)  var(--text-dim)
var(--accent)  var(--accent-dim)
var(--success)  var(--success-glow)
var(--warn)     var(--warn-glow)
var(--amber)    var(--amber-dim)
var(--danger)   var(--danger-glow)
var(--red)      var(--red-dim)
var(--violet)   var(--violet-dim)
var(--r)  var(--rsm)
```

---

## Step 1 — Migration

Create `database/migrations/TIMESTAMP_create_saved_reports_table.php`.
Use `php artisan make:migration create_saved_reports_table` to generate the file,
then replace its content:

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saved_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_shared')->default(false);   // shared = visible to all owner/admin users
            $table->jsonb('config');                         // full report definition (see spec below)
            $table->timestamp('last_run_at')->nullable();
            $table->unsignedInteger('run_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['created_by', 'deleted_at']);
            $table->index(['is_shared', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_reports');
    }
};
```

Run it:
```bash
php artisan migrate
```

### Config JSONB schema (document in a comment inside the migration)

```json
{
  "date_range": "month",
  "date_from": null,
  "date_to": null,
  "location_filter": "all",
  "blocks": [
    {
      "id": "b1",
      "metric_id": "sales_revenue",
      "title": "Total Revenue",
      "width": "half",
      "viz": "kpi_card",
      "position": 0
    }
  ]
}
```

---

## Step 2 — SavedReport Model

Create `app/Models/SavedReport.php`:

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavedReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'created_by',
        'is_shared', 'config', 'last_run_at', 'run_count',
    ];

    protected $casts = [
        'config'       => 'array',
        'is_shared'    => 'boolean',
        'last_run_at'  => 'datetime',
        'run_count'    => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Returns config with safe defaults applied */
    public function resolvedConfig(): array
    {
        return array_merge([
            'date_range'      => 'month',
            'date_from'       => null,
            'date_to'         => null,
            'location_filter' => 'all',
            'blocks'          => [],
        ], $this->config ?? []);
    }

    public function blockCount(): int
    {
        return count($this->resolvedConfig()['blocks'] ?? []);
    }
}
```

---

## Step 3 — MetricRegistry Service

Create `app/Services/Reports/MetricRegistry.php`.

This is the authoritative catalogue. Every block in it maps a `metric_id` to
the analytics service method that provides its data. The registry also declares
supported visualization types and the human-readable label and description.

```php
<?php
namespace App\Services\Reports;

class MetricRegistry
{
    /**
     * Returns the full catalogue of available metric blocks.
     *
     * Each entry:
     *   id          — unique string key, referenced in saved_reports.config
     *   label       — display name in builder catalogue
     *   description — one-line explanation shown in builder
     *   domain      — grouping: sales | inventory | replenishment | loss | transfers | operations
     *   viz_options — supported visualization types for this block
     *   default_viz — which viz to select by default
     *   needs_dates — true = block needs dateFrom/dateTo to run
     *   needs_location — true = block accepts a locationFilter
     */
    public function catalogue(): array
    {
        return [
            // ── SALES ──────────────────────────────────────────────────────
            [
                'id'           => 'sales_revenue',
                'label'        => 'Total Revenue',
                'description'  => 'Revenue for the selected period with growth vs prior period',
                'domain'       => 'sales',
                'viz_options'  => ['kpi_card', 'bar_chart'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            [
                'id'           => 'sales_gross_profit',
                'label'        => 'Gross Profit & Margin',
                'description'  => 'Gross profit RWF and margin % for the period',
                'domain'       => 'sales',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            [
                'id'           => 'sales_transaction_count',
                'label'        => 'Transaction Count',
                'description'  => 'Number of completed sales transactions',
                'domain'       => 'sales',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            [
                'id'           => 'sales_avg_basket',
                'label'        => 'Average Basket Value',
                'description'  => 'Average revenue per transaction',
                'domain'       => 'sales',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            [
                'id'           => 'sales_by_shop',
                'label'        => 'Revenue by Shop',
                'description'  => 'Revenue breakdown per shop for the period',
                'domain'       => 'sales',
                'viz_options'  => ['bar_chart', 'table'],
                'default_viz'  => 'bar_chart',
                'needs_dates'  => true,
                'needs_location' => false,
            ],
            [
                'id'           => 'sales_top_products',
                'label'        => 'Top Products by Revenue',
                'description'  => 'Products ranked by revenue for the period',
                'domain'       => 'sales',
                'viz_options'  => ['table', 'bar_chart'],
                'default_viz'  => 'table',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            [
                'id'           => 'sales_payment_methods',
                'label'        => 'Payment Method Breakdown',
                'description'  => 'Revenue split by payment method',
                'domain'       => 'sales',
                'viz_options'  => ['table', 'bar_chart'],
                'default_viz'  => 'table',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            [
                'id'           => 'sales_revenue_trend',
                'label'        => 'Revenue Trend',
                'description'  => 'Daily or weekly revenue chart for the period',
                'domain'       => 'sales',
                'viz_options'  => ['line_chart', 'bar_chart'],
                'default_viz'  => 'line_chart',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            [
                'id'           => 'sales_voided',
                'label'        => 'Voided Sales',
                'description'  => 'Count and value of voided transactions',
                'domain'       => 'sales',
                'viz_options'  => ['kpi_card', 'table'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            // ── INVENTORY ──────────────────────────────────────────────────
            [
                'id'           => 'inventory_cost_value',
                'label'        => 'Inventory Cost Value',
                'description'  => 'Total capital invested in current stock',
                'domain'       => 'inventory',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            [
                'id'           => 'inventory_retail_value',
                'label'        => 'Inventory Retail Value',
                'description'  => 'Total stock valued at selling price',
                'domain'       => 'inventory',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            [
                'id'           => 'inventory_fill_rate',
                'label'        => 'Portfolio Fill Rate',
                'description'  => 'Items remaining as % of total box capacity',
                'domain'       => 'inventory',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            [
                'id'           => 'inventory_aging',
                'label'        => 'Stock Aging Analysis',
                'description'  => 'Boxes grouped by age bracket (0-30, 31-60, 61-90, 90+ days)',
                'domain'       => 'inventory',
                'viz_options'  => ['table', 'bar_chart'],
                'default_viz'  => 'table',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            [
                'id'           => 'inventory_dead_stock',
                'label'        => 'Dead Stock',
                'description'  => 'Products with stock but no sales in 90 days',
                'domain'       => 'inventory',
                'viz_options'  => ['kpi_card', 'table'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            [
                'id'           => 'inventory_abc_summary',
                'label'        => 'ABC Classification',
                'description'  => 'Products classified as A/B/C/Dead movers by 90-day revenue',
                'domain'       => 'inventory',
                'viz_options'  => ['table', 'kpi_card'],
                'default_viz'  => 'table',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            [
                'id'           => 'inventory_top_by_value',
                'label'        => 'Top Products by Capital Value',
                'description'  => 'Products ranked by capital locked in current stock',
                'domain'       => 'inventory',
                'viz_options'  => ['table'],
                'default_viz'  => 'table',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            [
                'id'           => 'inventory_category_concentration',
                'label'        => 'Inventory by Category',
                'description'  => 'Stock value and % share per product category',
                'domain'       => 'inventory',
                'viz_options'  => ['table', 'bar_chart'],
                'default_viz'  => 'table',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            [
                'id'           => 'inventory_by_location',
                'label'        => 'Stock Value by Location',
                'description'  => 'Inventory value split across all warehouses and shops',
                'domain'       => 'inventory',
                'viz_options'  => ['table', 'bar_chart'],
                'default_viz'  => 'table',
                'needs_dates'  => false,
                'needs_location' => false,
            ],
            // ── REPLENISHMENT ──────────────────────────────────────────────
            [
                'id'           => 'replenishment_critical',
                'label'        => 'Critical Stock (≤7 days)',
                'description'  => 'Products with 7 or fewer days of stock at current velocity',
                'domain'       => 'replenishment',
                'viz_options'  => ['kpi_card', 'table'],
                'default_viz'  => 'table',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            [
                'id'           => 'replenishment_days_on_hand',
                'label'        => 'Days on Hand per Product',
                'description'  => 'Estimated days of stock remaining based on 30-day sales velocity',
                'domain'       => 'replenishment',
                'viz_options'  => ['table'],
                'default_viz'  => 'table',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            // ── LOSS ───────────────────────────────────────────────────────
            [
                'id'           => 'loss_total',
                'label'        => 'Total Losses',
                'description'  => 'Combined refunds and damaged goods loss for the period',
                'domain'       => 'loss',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            [
                'id'           => 'loss_return_rate',
                'label'        => 'Return Rate',
                'description'  => 'Returns as a percentage of sales transactions',
                'domain'       => 'loss',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            [
                'id'           => 'loss_damaged_value',
                'label'        => 'Damaged Goods Loss',
                'description'  => 'Estimated value of damaged goods recorded in the period',
                'domain'       => 'loss',
                'viz_options'  => ['kpi_card', 'table'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            [
                'id'           => 'loss_shrinkage',
                'label'        => 'Shrinkage Rate',
                'description'  => 'Damaged items as % of total items received in 90 days',
                'domain'       => 'loss',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            [
                'id'           => 'loss_by_product',
                'label'        => 'Problem Products (Returns + Damage)',
                'description'  => 'Products with the most returns and damage events',
                'domain'       => 'loss',
                'viz_options'  => ['table'],
                'default_viz'  => 'table',
                'needs_dates'  => true,
                'needs_location' => true,
            ],
            // ── TRANSFERS ──────────────────────────────────────────────────
            [
                'id'           => 'transfers_kpis',
                'label'        => 'Transfer Performance KPIs',
                'description'  => 'Count, avg completion time, and discrepancy rate',
                'domain'       => 'transfers',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => true,
                'needs_location' => false,
            ],
            [
                'id'           => 'transfers_discrepancies',
                'label'        => 'Transfer Discrepancies',
                'description'  => 'Transfers received with quantity or damage discrepancies',
                'domain'       => 'transfers',
                'viz_options'  => ['kpi_card', 'table'],
                'default_viz'  => 'table',
                'needs_dates'  => true,
                'needs_location' => false,
            ],
            [
                'id'           => 'transfers_routes',
                'label'        => 'Transfer Volume by Route',
                'description'  => 'Volume of transfers per warehouse-to-shop route',
                'domain'       => 'transfers',
                'viz_options'  => ['table', 'bar_chart'],
                'default_viz'  => 'table',
                'needs_dates'  => true,
                'needs_location' => false,
            ],
            // ── OPERATIONS ─────────────────────────────────────────────────
            [
                'id'           => 'ops_low_stock_count',
                'label'        => 'Low Stock Products',
                'description'  => 'Count of products below their reorder threshold',
                'domain'       => 'operations',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
            [
                'id'           => 'ops_damaged_pending',
                'label'        => 'Damaged Goods — No Decision',
                'description'  => 'Damaged goods records with no disposition decision',
                'domain'       => 'operations',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => false,
                'needs_location' => false,
            ],
            [
                'id'           => 'ops_stock_turnover',
                'label'        => 'Stock Turnover Ratio',
                'description'  => 'Annual COGS divided by average inventory value',
                'domain'       => 'operations',
                'viz_options'  => ['kpi_card'],
                'default_viz'  => 'kpi_card',
                'needs_dates'  => false,
                'needs_location' => true,
            ],
        ];
    }

    public function find(string $id): ?array
    {
        return collect($this->catalogue())->firstWhere('id', $id);
    }

    public function byDomain(): array
    {
        return collect($this->catalogue())
            ->groupBy('domain')
            ->toArray();
    }
}
```

---

## Step 4 — ReportRunner Service

Create `app/Services/Reports/ReportRunner.php`.

This service takes a saved report config and executes each block
by calling the correct analytics service method. It returns an array
of resolved block data ready for rendering.

```php
<?php
namespace App\Services\Reports;

use App\Services\Analytics\SalesAnalyticsService;
use App\Services\Analytics\InventoryAnalyticsService;
use App\Services\Analytics\LossAnalyticsService;
use App\Services\Analytics\TransferAnalyticsService;
use Carbon\Carbon;

class ReportRunner
{
    public function __construct(
        private SalesAnalyticsService     $sales,
        private InventoryAnalyticsService $inventory,
        private LossAnalyticsService      $loss,
        private TransferAnalyticsService  $transfers,
        private MetricRegistry            $registry,
    ) {}

    /**
     * Resolve dates from the config's date_range setting.
     * Returns [$dateFrom, $dateTo] as Y-m-d strings.
     */
    public function resolveDates(array $config): array
    {
        if ($config['date_range'] === 'custom' && $config['date_from'] && $config['date_to']) {
            return [$config['date_from'], $config['date_to']];
        }
        $to = now()->toDateString();
        $from = match ($config['date_range'] ?? 'month') {
            'today'   => now()->toDateString(),
            'week'    => now()->startOfWeek()->toDateString(),
            'month'   => now()->startOfMonth()->toDateString(),
            'quarter' => now()->startOfQuarter()->toDateString(),
            'year'    => now()->startOfYear()->toDateString(),
            default   => now()->startOfMonth()->toDateString(),
        };
        return [$from, $to];
    }

    /**
     * Run all blocks in the config and return resolved data.
     * Returns array keyed by block id => ['meta' => [...], 'data' => [...]]
     */
    public function run(array $config): array
    {
        [$dateFrom, $dateTo] = $this->resolveDates($config);
        $locationFilter = $config['location_filter'] ?? 'all';
        $blocks         = $config['blocks'] ?? [];
        $results        = [];

        foreach ($blocks as $block) {
            $metricId = $block['metric_id'] ?? null;
            if (! $metricId) continue;

            $meta = $this->registry->find($metricId);
            if (! $meta) continue;

            try {
                $data = $this->resolveBlock($metricId, $dateFrom, $dateTo, $locationFilter);
            } catch (\Throwable $e) {
                $data = ['error' => $e->getMessage()];
            }

            $results[$block['id']] = [
                'block'  => $block,
                'meta'   => $meta,
                'data'   => $data,
                'ran_at' => now()->toDateTimeString(),
            ];
        }

        return $results;
    }

    private function resolveBlock(
        string $metricId,
        string $dateFrom,
        string $dateTo,
        string $locationFilter,
    ): array {
        return match ($metricId) {
            // ── Sales ──────────────────────────────────────────────────────
            'sales_revenue'          => $this->sales->getRevenueKpis($dateFrom, $dateTo, $locationFilter),
            'sales_gross_profit'     => $this->sales->getGrossProfitKpis($dateFrom, $dateTo, $locationFilter),
            'sales_transaction_count'=> $this->sales->getItemsSoldKpi($dateFrom, $dateTo, $locationFilter),
            'sales_avg_basket'       => $this->wrapAvgBasket($dateFrom, $dateTo, $locationFilter),
            'sales_by_shop'          => $this->sales->getShopPerformance($dateFrom, $dateTo),
            'sales_top_products'     => $this->sales->getTopProducts($dateFrom, $dateTo, $locationFilter),
            'sales_payment_methods'  => $this->sales->getPaymentMethods($dateFrom, $dateTo, $locationFilter),
            'sales_revenue_trend'    => $this->sales->getRevenueTrend($dateFrom, $dateTo, $locationFilter),
            'sales_voided'           => $this->sales->getVoidedSalesStats($dateFrom, $dateTo, $locationFilter),

            // ── Inventory ──────────────────────────────────────────────────
            'inventory_cost_value'          => $this->inventory->getInventoryKpis($locationFilter),
            'inventory_retail_value'        => $this->inventory->getInventoryKpis($locationFilter),
            'inventory_fill_rate'           => ['fill_rate' => $this->inventory->getPortfolioFillRate($locationFilter)],
            'inventory_aging'               => $this->inventory->getAgingAnalysis($locationFilter),
            'inventory_dead_stock'          => $this->inventory->getStockHealth($locationFilter),
            'inventory_abc_summary'         => $this->inventory->getVelocityClassification($locationFilter),
            'inventory_top_by_value'        => $this->inventory->getTopProductsByValue($locationFilter, 20),
            'inventory_category_concentration' => $this->inventory->getCategoryConcentration($locationFilter),
            'inventory_by_location'         => $this->inventory->getInventoryByLocation(),

            // ── Replenishment ──────────────────────────────────────────────
            'replenishment_critical'     => $this->wrapCriticalOnly($locationFilter),
            'replenishment_days_on_hand' => $this->inventory->getDaysOnHandPerProduct($locationFilter, 50),

            // ── Loss ───────────────────────────────────────────────────────
            'loss_total'         => $this->loss->getLossKpis($dateFrom, $dateTo, $locationFilter),
            'loss_return_rate'   => $this->loss->getLossKpis($dateFrom, $dateTo, $locationFilter),
            'loss_damaged_value' => $this->loss->getLossKpis($dateFrom, $dateTo, $locationFilter),
            'loss_shrinkage'     => $this->inventory->getShrinkageStats($locationFilter),
            'loss_by_product'    => $this->loss->getProblemProducts($dateFrom, $dateTo, $locationFilter),

            // ── Transfers ──────────────────────────────────────────────────
            'transfers_kpis'          => $this->transfers->getTransferKpis($dateFrom, $dateTo, null),
            'transfers_discrepancies' => $this->transfers->getRecentDiscrepancies($dateFrom, $dateTo, 20),
            'transfers_routes'        => $this->transfers->getTransferRoutes($dateFrom, $dateTo),

            // ── Operations ─────────────────────────────────────────────────
            'ops_low_stock_count'  => $this->inventory->getStockHealth($locationFilter),
            'ops_damaged_pending'  => ['count' => \App\Models\DamagedGood::where('disposition', 'pending')->whereNull('deleted_at')->count()],
            'ops_stock_turnover'   => ['turnover_rate' => $this->inventory->calculateStockTurnover($locationFilter)],

            default => throw new \InvalidArgumentException("Unknown metric: {$metricId}"),
        };
    }

    private function wrapAvgBasket(string $from, string $to, string $loc): array
    {
        $revenue = $this->sales->getRevenueKpis($from, $to, $loc);
        $count   = $this->sales->getItemsSoldKpi($from, $to, $loc);
        $txCount = $count['transaction_count'] ?? 1;
        return [
            'avg_basket' => $txCount > 0
                ? round(($revenue['current'] ?? 0) / $txCount)
                : 0,
        ];
    }

    private function wrapCriticalOnly(string $loc): array
    {
        $all = $this->inventory->getDaysOnHandPerProduct($loc, 200);
        return array_filter($all, fn ($p) => $p['is_critical'] === true);
    }
}
```

**Important:** Several analytics service methods may not exist yet
(e.g. `getPortfolioFillRate`, `getCategoryConcentration`, `getShrinkageStats`,
`getDaysOnHandPerProduct`, `getVelocityClassification`).
These are defined in the inventory-report-rebuild.md instruction file.
If that file has already been applied, these methods will exist.
If not, check whether the methods are present and add any missing ones to
`InventoryAnalyticsService` before proceeding.

Also check whether `SalesAnalyticsService` has a `getPaymentMethods` method
(it may be named `getPaymentMethodBreakdown`). Read the service file and use
the correct method name.

---

## Step 5 — Routes

Open `routes/web.php`. Find the owner report routes block. Add three new routes
inside the same `role:owner` middleware group, after the existing report routes:

```php
// Custom report builder
Route::get('/owner/reports/custom',          [\App\Http\Controllers\Owner\Reports\CustomReportController::class, 'library'])->name('owner.reports.custom.library');
Route::get('/owner/reports/custom/builder',  [\App\Http\Controllers\Owner\Reports\CustomReportController::class, 'builder'])->name('owner.reports.custom.builder');
Route::get('/owner/reports/custom/{report}', [\App\Http\Controllers\Owner\Reports\CustomReportController::class, 'view'])->name('owner.reports.custom.view');
```

Create `app/Http/Controllers/Owner/Reports/CustomReportController.php`:

```php
<?php
namespace App\Http\Controllers\Owner\Reports;

use App\Http\Controllers\Controller;
use App\Models\SavedReport;
use Illuminate\Http\Request;

class CustomReportController extends Controller
{
    public function library()
    {
        return view('owner.reports.custom.library');
    }

    public function builder()
    {
        return view('owner.reports.custom.builder');
    }

    public function view(SavedReport $report)
    {
        // Authorization: owner/admin can see own reports; shared reports visible to all owner/admins
        $user = auth()->user();
        if ($report->created_by !== $user->id && ! $report->is_shared) {
            abort(403);
        }
        return view('owner.reports.custom.view', compact('report'));
    }
}
```

---

## Step 6 — Livewire Components

### 6A — ReportLibrary

Create `app/Livewire/Owner/Reports/ReportLibrary.php`:

```php
<?php
namespace App\Livewire\Owner\Reports;

use App\Models\SavedReport;
use Livewire\Component;
use Livewire\WithPagination;

class ReportLibrary extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all'; // all | mine | shared

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilter(): void  { $this->resetPage(); }

    public function deleteReport(int $id): void
    {
        $report = SavedReport::findOrFail($id);
        if ($report->created_by !== auth()->id()) abort(403);
        $report->delete();
        session()->flash('success', 'Report deleted.');
    }

    public function duplicateReport(int $id): void
    {
        $source = SavedReport::findOrFail($id);
        SavedReport::create([
            'name'       => $source->name . ' (copy)',
            'description'=> $source->description,
            'created_by' => auth()->id(),
            'is_shared'  => false,
            'config'     => $source->config,
        ]);
        session()->flash('success', 'Report duplicated.');
    }

    public function render()
    {
        $user  = auth()->user();
        $query = SavedReport::with('creator')->whereNull('deleted_at');

        if ($this->filter === 'mine') {
            $query->where('created_by', $user->id);
        } elseif ($this->filter === 'shared') {
            $query->where('is_shared', true);
        } else {
            // All reports the user can see: own + shared
            $query->where(fn ($q) =>
                $q->where('created_by', $user->id)
                  ->orWhere('is_shared', true)
            );
        }

        if ($this->search) {
            $query->where('name', 'ilike', '%' . $this->search . '%');
        }

        $reports = $query->orderByDesc('last_run_at')->paginate(20);

        return view('livewire.owner.reports.report-library', [
            'reports' => $reports,
        ]);
    }
}
```

### 6B — ReportBuilder

Create `app/Livewire/Owner/Reports/ReportBuilder.php`:

```php
<?php
namespace App\Livewire\Owner\Reports;

use App\Models\SavedReport;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Reports\MetricRegistry;
use Livewire\Component;

class ReportBuilder extends Component
{
    // ─── Report meta ───────────────────────────────────────────────────────
    public string  $reportName        = '';
    public string  $reportDescription = '';
    public bool    $isShared          = false;

    // ─── Global filters ────────────────────────────────────────────────────
    public string  $dateRange      = 'month';
    public ?string $dateFrom       = null;
    public ?string $dateTo         = null;
    public string  $locationFilter = 'all';

    // ─── Canvas (ordered array of block configs) ───────────────────────────
    public array   $canvas = [];   // each item: {id, metric_id, title, width, viz}

    // ─── Catalogue search ──────────────────────────────────────────────────
    public string  $catalogueSearch = '';
    public string  $catalogueDomain = 'all';

    // ─── Edit context ──────────────────────────────────────────────────────
    public ?int    $editingReportId = null;   // set when editing an existing report

    public function mount(?int $reportId = null): void
    {
        if (! auth()->user()->isOwner() && ! auth()->user()->isAdmin()) abort(403);

        if ($reportId) {
            $report = SavedReport::findOrFail($reportId);
            if ($report->created_by !== auth()->id()) abort(403);
            $this->editingReportId  = $report->id;
            $this->reportName       = $report->name;
            $this->reportDescription = $report->description ?? '';
            $this->isShared         = $report->is_shared;
            $config                 = $report->resolvedConfig();
            $this->dateRange        = $config['date_range'];
            $this->dateFrom         = $config['date_from'];
            $this->dateTo           = $config['date_to'];
            $this->locationFilter   = $config['location_filter'];
            $this->canvas           = $config['blocks'];
        }
    }

    public function addBlock(string $metricId): void
    {
        $meta = app(MetricRegistry::class)->find($metricId);
        if (! $meta) return;

        $this->canvas[] = [
            'id'        => 'b' . now()->timestamp . '_' . count($this->canvas),
            'metric_id' => $metricId,
            'title'     => $meta['label'],
            'width'     => 'half',
            'viz'       => $meta['default_viz'],
            'position'  => count($this->canvas),
        ];
    }

    public function removeBlock(string $blockId): void
    {
        $this->canvas = collect($this->canvas)
            ->filter(fn ($b) => $b['id'] !== $blockId)
            ->values()
            ->toArray();
    }

    public function moveBlockUp(string $blockId): void
    {
        $idx = collect($this->canvas)->search(fn ($b) => $b['id'] === $blockId);
        if ($idx > 0) {
            [$this->canvas[$idx], $this->canvas[$idx - 1]] =
                [$this->canvas[$idx - 1], $this->canvas[$idx]];
        }
    }

    public function moveBlockDown(string $blockId): void
    {
        $idx = collect($this->canvas)->search(fn ($b) => $b['id'] === $blockId);
        if ($idx !== false && $idx < count($this->canvas) - 1) {
            [$this->canvas[$idx], $this->canvas[$idx + 1]] =
                [$this->canvas[$idx + 1], $this->canvas[$idx]];
        }
    }

    public function updateBlockWidth(string $blockId, string $width): void
    {
        $this->canvas = collect($this->canvas)->map(fn ($b) =>
            $b['id'] === $blockId ? array_merge($b, ['width' => $width]) : $b
        )->toArray();
    }

    public function updateBlockViz(string $blockId, string $viz): void
    {
        $this->canvas = collect($this->canvas)->map(fn ($b) =>
            $b['id'] === $blockId ? array_merge($b, ['viz' => $viz]) : $b
        )->toArray();
    }

    public function updateBlockTitle(string $blockId, string $title): void
    {
        $this->canvas = collect($this->canvas)->map(fn ($b) =>
            $b['id'] === $blockId ? array_merge($b, ['title' => $title]) : $b
        )->toArray();
    }

    public function save(): void
    {
        $this->validate([
            'reportName' => 'required|string|min:2|max:120',
            'canvas'     => 'array|min:1',
        ], [
            'reportName.required' => 'Give the report a name.',
            'canvas.min'          => 'Add at least one metric block before saving.',
        ]);

        $config = [
            'date_range'      => $this->dateRange,
            'date_from'       => $this->dateFrom,
            'date_to'         => $this->dateTo,
            'location_filter' => $this->locationFilter,
            'blocks'          => array_values($this->canvas),
        ];

        if ($this->editingReportId) {
            $report = SavedReport::findOrFail($this->editingReportId);
            $report->update([
                'name'        => trim($this->reportName),
                'description' => trim($this->reportDescription) ?: null,
                'is_shared'   => $this->isShared,
                'config'      => $config,
            ]);
        } else {
            $report = SavedReport::create([
                'name'        => trim($this->reportName),
                'description' => trim($this->reportDescription) ?: null,
                'created_by'  => auth()->id(),
                'is_shared'   => $this->isShared,
                'config'      => $config,
            ]);
        }

        session()->flash('success', 'Report saved.');
        return redirect()->route('owner.reports.custom.view', $report->id);
    }

    public function getCatalogueProperty(): array
    {
        $registry = app(MetricRegistry::class);
        $all      = collect($registry->catalogue());

        if ($this->catalogueSearch) {
            $term = strtolower($this->catalogueSearch);
            $all  = $all->filter(fn ($m) =>
                str_contains(strtolower($m['label']), $term) ||
                str_contains(strtolower($m['description']), $term)
            );
        }

        if ($this->catalogueDomain !== 'all') {
            $all = $all->filter(fn ($m) => $m['domain'] === $this->catalogueDomain);
        }

        return $all->groupBy('domain')->toArray();
    }

    public function getAddedMetricIdsProperty(): array
    {
        return array_column($this->canvas, 'metric_id');
    }

    public function getWarehousesProperty() { return Warehouse::orderBy('name')->get(); }
    public function getShopsProperty()      { return Shop::orderBy('name')->get(); }

    public function render()
    {
        return view('livewire.owner.reports.report-builder');
    }
}
```

### 6C — ReportViewer

Create `app/Livewire/Owner/Reports/ReportViewer.php`:

```php
<?php
namespace App\Livewire\Owner\Reports;

use App\Models\SavedReport;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\Reports\ReportRunner;
use Livewire\Component;

class ReportViewer extends Component
{
    public int   $reportId;
    public bool  $isRunning = false;
    public array $results   = [];
    public bool  $hasRun    = false;

    public function mount(int $reportId): void
    {
        $this->reportId = $reportId;
    }

    public function run(): void
    {
        $report = SavedReport::findOrFail($this->reportId);
        $this->isRunning = true;

        $runner        = app(ReportRunner::class);
        $this->results = $runner->run($report->resolvedConfig());
        $this->hasRun  = true;
        $this->isRunning = false;

        $report->update([
            'last_run_at' => now(),
            'run_count'   => $report->run_count + 1,
        ]);
    }

    public function render()
    {
        $report = SavedReport::with('creator')->findOrFail($this->reportId);
        return view('livewire.owner.reports.report-viewer', [
            'report'     => $report,
            'warehouses' => Warehouse::pluck('name', 'id'),
            'shops'      => Shop::pluck('name', 'id'),
        ]);
    }
}
```

---

## Step 7 — Blade Views

### 7A — Page wrapper views

Create `resources/views/owner/reports/custom/library.blade.php`:
```blade
<x-app-layout>
    <livewire:owner.reports.report-library />
</x-app-layout>
```

Create `resources/views/owner/reports/custom/builder.blade.php`:
```blade
<x-app-layout>
    <livewire:owner.reports.report-builder />
</x-app-layout>
```

Create `resources/views/owner/reports/custom/view.blade.php`:
```blade
<x-app-layout>
    <livewire:owner.reports.report-viewer :report-id="$report->id" />
</x-app-layout>
```

### 7B — Report Library blade

Create `resources/views/livewire/owner/reports/report-library.blade.php`.

Read the existing report pages for the header + filter bar pattern.
Then build this structure:

**Page header:**
```blade
<div class="dashboard-page-header">
    <div>
        <h1>Custom Reports</h1>
        <p>Build, save, and re-run your own report combinations</p>
    </div>
    <a href="{{ route('owner.reports.custom.builder') }}"
       style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;
              background:var(--accent);color:#fff;border-radius:var(--r);
              font-size:14px;font-weight:700;text-decoration:none">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New Report
    </a>
</div>
```

**Filter bar** — search input + three filter tabs (All / Mine / Shared),
bound to `wire:model.live="search"` and `wire:click="$set('filter','...')"`.

**Report cards grid** — `display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:14px`

Each report card: `background:var(--surface); border:1px solid var(--border); border-radius:var(--r); padding:18px 20px`
- Report name in bold
- Description (if any) in `var(--text-sub)`
- Metrics used: show count of blocks with a small chip
- Created by + last run date + run count in small text
- Shared badge if `is_shared`
- Three action buttons: "Run" (primary, links to view), "Edit" (links to builder with `?reportId=`), "Duplicate" + "Delete" (icon buttons)

**Empty state** when no reports: centered illustration, "No reports yet. Build your first one.", "New Report" button.

### 7C — Report Builder blade

Create `resources/views/livewire/owner/reports/report-builder.blade.php`.

This is the most complex blade. Build it as a **two-panel layout**:

```
┌─────────────────────────┬──────────────────────────────────────────┐
│  METRIC CATALOGUE       │  CANVAS                                  │
│  (left, ~340px fixed)   │  (right, fills remaining width)          │
│                         │                                          │
│  [Search metrics...]    │  Report name: ________________           │
│                         │  Date range: [Month ▼]  Location: [All▼] │
│  SALES                  │                                          │
│  • Total Revenue    [+] │  ┌──────────────┐ ┌──────────────┐      │
│  • Gross Profit     [+] │  │ Total Revenue│ │ Gross Profit │      │
│  • Top Products     [+] │  │  [half]  ↑↓  │ │  [half]  ↑↓ │      │
│                         │  └──────────────┘ └──────────────┘      │
│  INVENTORY              │                                          │
│  • Cost Value       [+] │  ┌─────────────────────────────────┐    │
│  ...                    │  │  Revenue Trend (line)  [full] ↑↓ │   │
│                         │  └─────────────────────────────────┘    │
│                         │                                          │
│                         │  [Save Report]                           │
└─────────────────────────┴──────────────────────────────────────────┘
```

**Left panel — Catalogue:**
- Search: `wire:model.live="catalogueSearch"`
- Domain tabs: `wire:click="$set('catalogueDomain', '...')"` for all/sales/inventory/replenishment/loss/transfers/operations
- For each metric block: label + description + an "Add" button `wire:click="addBlock('{{ $metric['id'] }}')"`.
  If the metric is already on the canvas, show a green ✓ checkmark instead of the Add button.

**Right panel — Canvas:**

Top section: report name input, description input, date range select, location filter select,
is_shared toggle, all wired with `wire:model`.

Canvas blocks — for each block in `$canvas`:
- A card with: editable title (inline `wire:model`), width toggle (half/full), viz selector,
  up/down arrow buttons (`wire:click="moveBlockUp('...')"` / `moveBlockDown`),
  remove button (`wire:click="removeBlock('...')"`)
- The viz selector shows only the `viz_options` for that metric_id.
  Look up the meta in the registry via `$catalogue` (you can pass the full catalogue to the blade
  as a flat array keyed by metric_id for easy lookup)

If canvas is empty: a large dashed placeholder "Add blocks from the catalogue on the left".

Save button at bottom: `wire:click="save"`. Show validation errors if any.

### 7D — Report Viewer blade

Create `resources/views/livewire/owner/reports/report-viewer.blade.php`.

**Header:**
- Report name + description
- Created by / last run at / run count meta
- "Run Report" button: `wire:click="run"` with loading state `wire:loading`
- "Edit" button linking to builder with `?reportId={{ $report->id }}`
- Resolved date range + location filter shown as chips

**Before first run:** a centered "Click Run Report to generate results" placeholder.

**After run — results grid:**

Render blocks in order using their `width` (half = 50%, full = 100%).
Use `display:flex; flex-wrap:wrap; gap:16px` with each half block at `width:calc(50% - 8px)` and
full block at `width:100%`.

For each resolved block, render based on the `viz` type:

**`kpi_card`** — a card showing the main value from the data array.
The rendering logic must extract the right key based on `metric_id`:
- `sales_revenue` → show `current` as main value, `growth_pct` as delta chip
- `sales_gross_profit` → show `gross_profit` + `margin_pct`
- `inventory_cost_value` → show `purchase_value`
- `inventory_retail_value` → show `retail_value`
- `inventory_fill_rate` → show `fill_rate`%
- `loss_total` → show total of `total_refunds` + `damaged_loss`
- `loss_return_rate` → show `return_rate`%
- `ops_stock_turnover` → show `turnover_rate`×
- `ops_damaged_pending` → show `count`
- `ops_low_stock_count` → show `low_stock_count`
- For all others, show the first numeric value found in the data array.

Use a `@php` helper at top of the blade:
```blade
@php
function extractKpiValue(string $metricId, array $data): string {
    return match($metricId) {
        'sales_revenue'          => number_format($data['current'] ?? 0) . ' RWF',
        'sales_gross_profit'     => number_format($data['gross_profit'] ?? 0) . ' RWF',
        'sales_transaction_count'=> number_format($data['transaction_count'] ?? 0),
        'sales_avg_basket'       => number_format($data['avg_basket'] ?? 0) . ' RWF',
        'inventory_cost_value'   => number_format($data['purchase_value'] ?? 0) . ' RWF',
        'inventory_retail_value' => number_format($data['retail_value'] ?? 0) . ' RWF',
        'inventory_fill_rate'    => ($data['fill_rate'] ?? '—') . '%',
        'inventory_dead_stock'   => ($data['dead_stock_count'] ?? 0) . ' products',
        'loss_total'             => number_format(($data['total_refunds'] ?? 0) + ($data['damaged_loss'] ?? 0)) . ' RWF',
        'loss_return_rate'       => round($data['return_rate'] ?? 0, 1) . '%',
        'loss_shrinkage'         => round($data['shrinkage_pct'] ?? 0, 2) . '%',
        'ops_stock_turnover'     => round($data['turnover_rate'] ?? 0, 2) . '×',
        'ops_damaged_pending'    => ($data['count'] ?? 0) . ' items',
        'ops_low_stock_count'    => ($data['low_stock_count'] ?? 0) . ' products',
        default                  => collect($data)->first(fn($v) => is_numeric($v)) ?? '—',
    };
}
@endphp
```

**`table`** — a `<table>` rendered from the data array. The table must handle
both flat arrays (single row of values) and collections (multiple rows).
If the data is a collection of rows, render one row per item with column headers
derived from the keys of the first item. If the data is a flat associative array,
render it as a two-column key/value table.

**`bar_chart`** and **`line_chart`** — render a `<canvas>` element with a
unique ID (`chart_{{ $blockResult['block']['id'] }}`), and include a `<script>`
block that initialises Chart.js. The data shape varies by metric — read the
analytics service return values and extract `labels` and `datasets` accordingly.
For blocks without a natural time series (e.g. `sales_by_shop`), use shop names
as labels. Copy the Chart.js initialisation pattern from `sales-analytics.blade.php`.

**Error block** — if `$blockResult['data']['error']` is set, show a red alert
card with the error message and a note "This block failed to run."

---

## Step 8 — Register Livewire Components & Update Sidebar

### 8A — Register components (Livewire 3 auto-discovers by namespace)

Verify auto-discovery is working:
```bash
php artisan livewire:discover 2>&1 | grep -i "ReportLibrary\|ReportBuilder\|ReportViewer"
```

If not auto-discovered, register manually in `app/Providers/AppServiceProvider.php`:
```php
use Livewire\Livewire;
Livewire::component('owner.reports.report-library', \App\Livewire\Owner\Reports\ReportLibrary::class);
Livewire::component('owner.reports.report-builder', \App\Livewire\Owner\Reports\ReportBuilder::class);
Livewire::component('owner.reports.report-viewer',  \App\Livewire\Owner\Reports\ReportViewer::class);
```

### 8B — Add sidebar link

Open `resources/views/livewire/layout/sidebar.blade.php`.
Find the Reports section in the sidebar (look for existing report links).
Add a new link for Custom Reports in the same style as the adjacent links:

```blade
{{-- Custom Reports --}}
<a href="{{ route('owner.reports.custom.library') }}"
   class="{{ request()->routeIs('owner.reports.custom.*') ? 'active' : '' }}">
    {{-- Use the same icon + text pattern as the adjacent sidebar items --}}
    <svg ...>...</svg>  {{-- copy any appropriate icon from adjacent items --}}
    <span>Custom Reports</span>
</a>
```

Match the exact CSS classes and icon size used by adjacent sidebar report links.

---

## Step 9 — Verification

Run in order, fix any error before proceeding to the next:

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear

php artisan view:cache 2>&1 | grep -i "error\|exception" | head -20
```

Verify the new service classes load:
```bash
php artisan tinker --execute="
    app(\App\Services\Reports\MetricRegistry::class)->catalogue();
    echo 'Registry OK — ' . count(app(\App\Services\Reports\MetricRegistry::class)->catalogue()) . ' blocks';
"
```

Open these URLs in the browser and confirm they render without errors:
1. `/owner/reports/custom` — library page (empty state if no reports yet)
2. `/owner/reports/custom/builder` — builder page with two-panel layout
3. Build a report with 2–3 blocks, save it, confirm redirect to viewer
4. Click "Run Report" on the viewer and confirm blocks render
5. Return to library and confirm the saved report appears

---

## Step 10 — Update CLAUDE.md

After all verification passes, open `CLAUDE.md` in the project root and
append this block at the end without modifying anything else:

```markdown
---

## Custom Report Builder — Completed

**New route prefix:** `/owner/reports/custom`

### New files created

**Backend**
- `database/migrations/..._create_saved_reports_table.php` — JSONB config column
- `app/Models/SavedReport.php` — model with `resolvedConfig()` helper
- `app/Services/Reports/MetricRegistry.php` — catalogue of ~30 metric blocks
- `app/Services/Reports/ReportRunner.php` — executes a config against analytics services
- `app/Http/Controllers/Owner/Reports/CustomReportController.php`

**Livewire**
- `app/Livewire/Owner/Reports/ReportLibrary.php` — list/manage saved reports
- `app/Livewire/Owner/Reports/ReportBuilder.php` — two-panel builder UI
- `app/Livewire/Owner/Reports/ReportViewer.php` — runs and renders a saved report

**Blades**
- `resources/views/owner/reports/custom/{library,builder,view}.blade.php`
- `resources/views/livewire/owner/reports/report-library.blade.php`
- `resources/views/livewire/owner/reports/report-builder.blade.php`
- `resources/views/livewire/owner/reports/report-viewer.blade.php`

### Architecture decisions

- No raw SQL exposed to users. All data comes from validated analytics service methods.
- Metric blocks are keyed by `metric_id` string. New blocks are added by adding an
  entry to `MetricRegistry::catalogue()` and a case to `ReportRunner::resolveBlock()`.
- `saved_reports.config` is JSONB so block order and visualization choices survive
  schema changes with no migration needed.
- `is_shared` flag makes reports visible to all owner/admin users without
  introducing a separate permissions system.

### To add a new metric block

1. Add entry to `MetricRegistry::catalogue()` with a unique `id`
2. Add a case to `ReportRunner::resolveBlock()` pointing to the analytics method
3. If the block uses a new viz type, add rendering logic in `report-viewer.blade.php`
```
