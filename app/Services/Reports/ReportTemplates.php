<?php
namespace App\Services\Reports;

class ReportTemplates
{
    private array $templates = [
        'monthly_ops' => [
            'name'            => 'Monthly Operations Overview',
            'description'     => 'Key KPIs for a monthly operations review',
            'date_range'      => 'month',
            'location_filter' => 'all',
            'blocks' => [
                ['metric_id' => 'sales_revenue',          'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'sales_gross_profit',     'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'sales_transaction_count','width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'sales_avg_basket',       'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'sales_by_shop',          'width' => 'full', 'viz' => 'bar_chart'],
                ['metric_id' => 'inventory_fill_rate',    'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'ops_low_stock_count',    'width' => 'half', 'viz' => 'kpi_card'],
            ],
        ],
        'inventory_health' => [
            'name'            => 'Inventory Health Report',
            'description'     => 'Deep-dive into stock levels, aging, and dead stock',
            'date_range'      => 'month',
            'location_filter' => 'all',
            'blocks' => [
                ['metric_id' => 'inventory_cost_value',            'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'inventory_retail_value',          'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'inventory_fill_rate',             'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'inventory_dead_stock',            'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'inventory_aging',                 'width' => 'full', 'viz' => 'table'],
                ['metric_id' => 'inventory_abc_summary',           'width' => 'full', 'viz' => 'table'],
                ['metric_id' => 'inventory_category_concentration','width' => 'full', 'viz' => 'bar_chart'],
            ],
        ],
        'sales_performance' => [
            'name'            => 'Sales Performance Dashboard',
            'description'     => 'Revenue trends, top products, and payment breakdown',
            'date_range'      => 'month',
            'location_filter' => 'all',
            'blocks' => [
                ['metric_id' => 'sales_revenue',         'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'sales_gross_profit',    'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'sales_revenue_trend',   'width' => 'full', 'viz' => 'line_chart'],
                ['metric_id' => 'sales_top_products',    'width' => 'half', 'viz' => 'table'],
                ['metric_id' => 'sales_payment_methods', 'width' => 'half', 'viz' => 'table'],
                ['metric_id' => 'sales_by_shop',         'width' => 'full', 'viz' => 'bar_chart'],
            ],
        ],
        'loss_audit' => [
            'name'            => 'Loss & Returns Audit',
            'description'     => 'Returns, damaged goods, shrinkage analysis',
            'date_range'      => 'month',
            'location_filter' => 'all',
            'blocks' => [
                ['metric_id' => 'loss_total',         'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'loss_return_rate',   'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'loss_damaged_value', 'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'loss_shrinkage',     'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'loss_by_product',    'width' => 'full', 'viz' => 'table'],
                ['metric_id' => 'ops_damaged_pending','width' => 'half', 'viz' => 'kpi_card'],
            ],
        ],
        'replenishment' => [
            'name'            => 'Replenishment Priorities',
            'description'     => 'Critical stock and days-on-hand by product',
            'date_range'      => 'month',
            'location_filter' => 'all',
            'blocks' => [
                ['metric_id' => 'replenishment_critical',     'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'ops_low_stock_count',        'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'replenishment_days_on_hand', 'width' => 'full', 'viz' => 'table'],
            ],
        ],
        'transfer_ops' => [
            'name'            => 'Transfer Operations Review',
            'description'     => 'Transfer volume, routes, and discrepancies',
            'date_range'      => 'month',
            'location_filter' => 'all',
            'blocks' => [
                ['metric_id' => 'transfers_kpis',          'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'transfers_discrepancies', 'width' => 'half', 'viz' => 'kpi_card'],
                ['metric_id' => 'transfers_routes',        'width' => 'full', 'viz' => 'table'],
            ],
        ],
        'business_pl' => [
            'name'            => 'Business P&L Summary',
            'description'     => 'True profit and loss: Revenue, Cost of Goods, Expenses, and Net Result',
            'date_range'      => 'month',
            'location_filter' => 'all',
            'blocks'          => [
                ['metric_id' => 'finance_net_operating',      'width' => 'full',  'viz' => 'kpi_card'],
                ['metric_id' => 'sales_revenue',              'width' => 'half',  'viz' => 'kpi_card'],
                ['metric_id' => 'sales_gross_profit',         'width' => 'half',  'viz' => 'kpi_card'],
                ['metric_id' => 'finance_expense_summary',    'width' => 'half',  'viz' => 'bar_chart'],
                ['metric_id' => 'finance_withdrawal_summary', 'width' => 'half',  'viz' => 'kpi_card'],
                ['metric_id' => 'sales_revenue_trend',        'width' => 'full',  'viz' => 'line_chart'],
                ['metric_id' => 'finance_expense_trend',      'width' => 'full',  'viz' => 'line_chart'],
                ['metric_id' => 'finance_cash_variance',      'width' => 'half',  'viz' => 'table'],
                ['metric_id' => 'loss_total',                 'width' => 'half',  'viz' => 'kpi_card'],
            ],
        ],
    ];

    /** Return all template keys with name and description */
    public function list(): array
    {
        return collect($this->templates)
            ->map(fn($t, $k) => ['key' => $k, 'name' => $t['name'], 'description' => $t['description']])
            ->values()
            ->toArray();
    }

    /** Return a single template by key, or null */
    public function get(string $key): ?array
    {
        return $this->templates[$key] ?? null;
    }
}
