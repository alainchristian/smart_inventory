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
            'color'           => '--accent',
            'icon'            => '<rect x="18" y="3" width="4" height="18" rx="1"/><rect x="10" y="8" width="4" height="13" rx="1"/><rect x="2" y="13" width="4" height="8" rx="1"/>',
            'metrics_preview' => ['Revenue', 'Gross Profit', 'Sales by Shop'],
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
            'color'           => '--green',
            'icon'            => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
            'metrics_preview' => ['Stock Value', 'Fill Rate', 'Dead Stock'],
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
            'color'           => '--accent',
            'icon'            => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
            'metrics_preview' => ['Revenue Trend', 'Top Products', 'Payment Mix'],
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
            'color'           => '--red',
            'icon'            => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            'metrics_preview' => ['Total Loss', 'Return Rate', 'Problem Products'],
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
            'color'           => '--amber',
            'icon'            => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>',
            'metrics_preview' => ['Critical Items', 'Low Stock', 'Days on Hand'],
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
            'color'           => '--violet',
            'icon'            => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>',
            'metrics_preview' => ['Transfer KPIs', 'Discrepancies', 'Routes'],
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
            'color'           => '--green',
            'icon'            => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>',
            'metrics_preview' => ['Net Result', 'Revenue', 'Expenses'],
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

    /** Return all template keys with display metadata */
    public function list(): array
    {
        return collect($this->templates)
            ->map(fn ($t, $k) => [
                'key'             => $k,
                'name'            => $t['name'],
                'description'     => $t['description'],
                'color'           => $t['color'],
                'icon'            => $t['icon'],
                'metrics_preview' => $t['metrics_preview'],
                'block_count'     => count($t['blocks']),
            ])
            ->values()
            ->toArray();
    }

    /** Return a single template by key, or null */
    public function get(string $key): ?array
    {
        return $this->templates[$key] ?? null;
    }
}
