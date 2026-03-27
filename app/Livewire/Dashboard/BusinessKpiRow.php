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
    public array   $sales          = [];
    public array   $profit         = [];
    public array   $inventory      = [];
    public array   $locations      = [];
    public array   $salesSparkline  = [];
    public array   $profitSparkline = [];

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
        $current  = Sale::notVoided()->whereBetween('sale_date', [$start, $end])->sum('total');
        $previous = Sale::notVoided()->whereBetween('sale_date', [$prevStart, $prevEnd])->sum('total');

        // Always-visible sub-row reference points (not period-dependent)
        $todayRev = Sale::notVoided()->whereDate('sale_date', today())->sum('total');
        $weekRev  = Sale::notVoided()->whereBetween('sale_date', [now()->startOfWeek(), now()])->sum('total');
        $monthRev = Sale::notVoided()->whereBetween('sale_date', [now()->startOfMonth(), now()])->sum('total');

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
            ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
            ->value('margin') ?? 0);

        // Profit sub-row reference points
        $todayMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereDate('sales.sale_date', today())
            ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
            ->value('margin') ?? 0);

        $weekMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [now()->startOfWeek(), now()])
            ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
            ->value('margin') ?? 0);

        $monthMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [now()->startOfMonth(), now()])
            ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
            ->value('margin') ?? 0);

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

        $cost   = ($inv->cost_value   ?? 0);
        $retail = ($inv->retail_value ?? 0);

        $whRetail = (int) (Box::available()
            ->where('boxes.location_type', 'warehouse')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('SUM(boxes.items_remaining * products.selling_price) AS v')
            ->value('v') ?? 0);

        $shopRetail = (int) (Box::available()
            ->where('boxes.location_type', 'shop')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('SUM(boxes.items_remaining * products.selling_price) AS v')
            ->value('v') ?? 0);

        $whItems   = Box::available()->where('location_type', 'warehouse')->sum('items_remaining');
        $shopItems = Box::available()->where('location_type', 'shop')->sum('items_remaining');

        $whBoxes    = Box::available()->where('location_type', 'warehouse')->count();
        $shopBoxes  = Box::available()->where('location_type', 'shop')->count();
        $totalBoxes = $whBoxes + $shopBoxes;

        $fillStats = Box::available()
            ->selectRaw('SUM(items_remaining) as remaining, SUM(items_total) as capacity')
            ->first();
        $fillRate = ($fillStats && $fillStats->capacity > 0)
            ? round(($fillStats->remaining / $fillStats->capacity) * 100, 1)
            : 0;

        $this->inventory = [
            'cost'         => $cost,
            'retail'       => $retail,
            'markup_pct'   => $cost > 0 ? round((($retail - $cost) / $cost) * 100, 1) : 0,
            'markup_label' => 'Potential markup',
            'warehouse'    => $whRetail,
            'shop'         => $shopRetail,
            'wh_items'     => $whItems,
            'shop_items'   => $shopItems,
            'wh_boxes'     => $whBoxes,
            'shop_boxes'   => $shopBoxes,
            'total_boxes'  => $totalBoxes,
            'fill_rate'    => $fillRate,
        ];

        $this->locations = [
            'warehouses'        => Warehouse::count(),
            'shops'             => Shop::count(),
            'users'             => User::count(),
            'active_warehouses' => Warehouse::where('is_active', true)->count(),
            'active_shops'      => Shop::where('is_active', true)->count(),
        ];

        $this->salesSparkline  = $this->generateSalesSparkline();
        $this->profitSparkline = $this->generateProfitSparkline();
    }

    private function generateSalesSparkline(int $days = 7): array
    {
        $rows = Sale::notVoided()
            ->whereBetween('sale_date', [now()->subDays($days - 1)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(sale_date) as day, SUM(total) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $key      = now()->subDays($i)->format('Y-m-d');
            $result[] = (float) ($rows[$key] ?? 0);
        }
        return $result;
    }

    private function generateProfitSparkline(int $days = 7): array
    {
        $rows = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sale_date', [now()->subDays($days - 1)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(sales.sale_date) as day, SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('margin', 'day');

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $key      = now()->subDays($i)->format('Y-m-d');
            $result[] = (float) ($rows[$key] ?? 0);
        }
        return $result;
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