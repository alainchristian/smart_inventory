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

    #[On('time-filter-changed')]
    public function refresh(array $payload): void
    {
        $this->period = $payload['period'] ?? 'today';
        $this->from   = $payload['from']   ?? null;
        $this->to     = $payload['to']     ?? null;
        $this->loadData();
    }

    private function loadData(): void
    {
        [$start, $end]         = $this->periodRange();
        [$prevStart, $prevEnd] = $this->previousRange();

        $current  = Sale::whereBetween('sale_date', [$start, $end])->sum('total') / 100;
        $previous = Sale::whereBetween('sale_date', [$prevStart, $prevEnd])->sum('total') / 100;

        $this->sales = [
            'today'   => Sale::whereDate('sale_date', today())->sum('total') / 100,
            'week'    => Sale::whereBetween('sale_date', [now()->startOfWeek(), now()])->sum('total') / 100,
            'month'   => Sale::whereBetween('sale_date', [now()->startOfMonth(), now()])->sum('total') / 100,
            'current' => $current,
            'growth'  => $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 0.0,
            'count'   => Sale::whereBetween('sale_date', [$start, $end])->count(),
        ];

        // Profit — uses purchase_price (owner-only field)
        $margin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;
        $revenue = $current ?: 1;

        // Today's profit
        $todayMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereDate('sales.sale_date', today())
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;

        // Week's profit
        $weekMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [now()->startOfWeek(), now()])
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;

        // Month's profit
        $monthMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [now()->startOfMonth(), now()])
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;

        $this->profit = [
            'today'      => $todayMargin,
            'week'       => $weekMargin,
            'month'      => $monthMargin,
            'margin_rwf' => $margin,
            'margin_pct' => round(($margin / $revenue) * 100, 1),
        ];

        // Inventory valuation — all available boxes
        $inv = Box::join('products', 'boxes.product_id', '=', 'products.id')
            ->where('boxes.items_remaining', '>', 0)
            ->selectRaw('SUM(boxes.items_remaining * products.purchase_price)  as cost_value,
                         SUM(boxes.items_remaining * products.selling_price)   as retail_value')
            ->first();
        $cost   = ($inv->cost_value   ?? 0) / 100;
        $retail = ($inv->retail_value ?? 0) / 100;

        // Warehouse inventory
        $whInv = (Box::join('products', 'boxes.product_id', '=', 'products.id')
            ->where('boxes.items_remaining', '>', 0)
            ->where('boxes.location_type', 'warehouse')
            ->selectRaw('SUM(boxes.items_remaining * products.selling_price) as retail_value')
            ->value('retail_value') ?? 0) / 100;

        // Shop inventory
        $shopInv = (Box::join('products', 'boxes.product_id', '=', 'products.id')
            ->where('boxes.items_remaining', '>', 0)
            ->where('boxes.location_type', 'shop')
            ->selectRaw('SUM(boxes.items_remaining * products.selling_price) as retail_value')
            ->value('retail_value') ?? 0) / 100;

        // Items count
        $whItems = Box::where('items_remaining', '>', 0)
            ->where('location_type', 'warehouse')
            ->sum('items_remaining');
        $shopItems = Box::where('items_remaining', '>', 0)
            ->where('location_type', 'shop')
            ->sum('items_remaining');

        $this->inventory = [
            'cost'       => $cost,
            'retail'     => $retail,
            'markup_pct' => $cost > 0 ? round((($retail - $cost) / $cost) * 100, 1) : 0,
            'warehouse'  => $whInv,
            'shop'       => $shopInv,
            'wh_items'   => $whItems,
            'shop_items' => $shopItems,
        ];

        $this->locations = [
            'warehouses' => Warehouse::count(),
            'shops'      => Shop::count(),
            'users'      => User::count(),
        ];
    }

    private function periodRange(): array
    {
        return match($this->period) {
            'today'   => [today(),                now()->endOfDay()],
            'week'    => [now()->startOfWeek(),   now()->endOfDay()],
            'month'   => [now()->startOfMonth(),  now()->endOfDay()],
            'quarter' => [now()->startOfQuarter(),now()->endOfDay()],
            'year'    => [now()->startOfYear(),   now()->endOfDay()],
            'custom'  => [$this->from ?? today(), $this->to ?? today()],
            default   => [today(),                now()->endOfDay()],
        };
    }

    private function previousRange(): array
    {
        [$start, $end] = $this->periodRange();
        $diff = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
        return [Carbon::parse($start)->subDays($diff), Carbon::parse($end)->subDays($diff)];
    }

    public function render()
    {
        return view('livewire.dashboard.business-kpi-row');
    }
}
