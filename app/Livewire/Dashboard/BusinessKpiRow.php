<?php

namespace App\Livewire\Dashboard;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Box;
use App\Models\Warehouse;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;

class BusinessKpiRow extends Component
{
    public string  $period = 'today';
    public ?string $from   = null;
    public ?string $to     = null;
    public array   $sales     = [];
    public array   $profit    = [];
    public array   $inventory = [];
    public array   $locations = [];

    public function mount(): void
    {
        $this->loadData();
    }

    // Livewire 3: each named dispatch argument maps to a typed parameter.
    // Do NOT use (array $payload) — that receives the entire array as one blob.
    #[On('time-filter-changed')]
    public function refresh(string $period, ?string $from = null, ?string $to = null): void
    {
        $this->period = $period;
        $this->from   = $from;
        $this->to     = $to;
        $this->loadData();
    }

    private function loadData(): void
    {
        [$start, $end]         = $this->periodRange();
        [$prevStart, $prevEnd] = $this->previousRange();

        // Revenue for selected period and comparison period
        $current  = Sale::notVoided()->whereBetween('sale_date', [$start, $end])->sum('total') / 100;
        $previous = Sale::notVoided()->whereBetween('sale_date', [$prevStart, $prevEnd])->sum('total') / 100;

        // Always-visible sub-row reference points (not period-dependent)
        $todayRev = Sale::notVoided()->whereDate('sale_date', today())->sum('total') / 100;
        $weekRev  = Sale::notVoided()->whereBetween('sale_date', [now()->startOfWeek(), now()])->sum('total') / 100;
        $monthRev = Sale::notVoided()->whereBetween('sale_date', [now()->startOfMonth(), now()])->sum('total') / 100;

        $this->sales = [
            'today'   => $todayRev,
            'week'    => $weekRev,
            'month'   => $monthRev,
            'current' => $current,
            'growth'  => $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 0.0,
            'count'   => Sale::notVoided()->whereBetween('sale_date', [$start, $end])->count(),
        ];

        // Profit margin for the selected period
        $margin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;

        // Profit sub-row reference points
        $todayMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereDate('sales.sale_date', today())
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;

        $weekMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [now()->startOfWeek(), now()])
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;

        $monthMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [now()->startOfMonth(), now()])
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;

        $this->profit = [
            'today'        => $todayMargin,
            'week'         => $weekMargin,
            'month'        => $monthMargin,
            'margin_rwf'   => $margin,
            'margin_pct'   => $current > 0 ? round(($margin / $current) * 100, 1) : 0,
            'margin_label' => 'Realised margin',
        ];

        // Inventory: Box::available() = status IN (full, partial) AND items_remaining > 0
        // This is the single source of truth used across all dashboard sections.
        $inv = Box::available()
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('
                SUM(boxes.items_remaining * products.purchase_price) AS cost_value,
                SUM(boxes.items_remaining * products.selling_price)  AS retail_value
            ')
            ->first();

        $cost   = ($inv->cost_value   ?? 0) / 100;
        $retail = ($inv->retail_value ?? 0) / 100;

        $whRetail = (Box::available()
            ->where('boxes.location_type', 'warehouse')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('SUM(boxes.items_remaining * products.selling_price) AS v')
            ->value('v') ?? 0) / 100;

        $shopRetail = (Box::available()
            ->where('boxes.location_type', 'shop')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('SUM(boxes.items_remaining * products.selling_price) AS v')
            ->value('v') ?? 0) / 100;

        $whItems   = Box::available()->where('location_type', 'warehouse')->sum('items_remaining');
        $shopItems = Box::available()->where('location_type', 'shop')->sum('items_remaining');

        $this->inventory = [
            'cost'         => $cost,
            'retail'       => $retail,
            'markup_pct'   => $cost > 0 ? round((($retail - $cost) / $cost) * 100, 1) : 0,
            'markup_label' => 'Potential markup',
            'warehouse'    => $whRetail,
            'shop'         => $shopRetail,
            'wh_items'     => $whItems,
            'shop_items'   => $shopItems,
        ];

        $this->locations = [
            'warehouses' => Warehouse::count(),
            'shops'      => Shop::count(),
            'users'      => User::count(),
        ];
    }

    private function periodRange(): array
    {
        return match ($this->period) {
            'today'   => [today()->startOfDay(),   now()->endOfDay()],
            'week'    => [now()->startOfWeek(),     now()->endOfDay()],
            'month'   => [now()->startOfMonth(),    now()->endOfDay()],
            'quarter' => [now()->startOfQuarter(),  now()->endOfDay()],
            'year'    => [now()->startOfYear(),     now()->endOfDay()],
            'custom'  => [
                Carbon::parse($this->from ?? today())->startOfDay(),
                Carbon::parse($this->to   ?? today())->endOfDay(),
            ],
            default   => [today()->startOfDay(), now()->endOfDay()],
        };
    }

    private function previousRange(): array
    {
        [$start, $end] = $this->periodRange();
        $diff = max($start->diffInDays($end), 1);
        return [
            $start->copy()->subDays($diff),
            $end->copy()->subDays($diff),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.business-kpi-row');
    }
}