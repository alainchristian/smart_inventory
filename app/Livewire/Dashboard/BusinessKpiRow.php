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

        // ─── FIX #2 — notVoided() on every sale query ────────────────────────
        $current  = Sale::notVoided()->whereBetween('sale_date', [$start, $end])->sum('total') / 100;
        $previous = Sale::notVoided()->whereBetween('sale_date', [$prevStart, $prevEnd])->sum('total') / 100;

        $this->sales = [
            // Fixed sub-period breakdowns also use notVoided()
            'today'   => Sale::notVoided()->whereDate('sale_date', today())->sum('total') / 100,
            'week'    => Sale::notVoided()->whereBetween('sale_date', [now()->startOfWeek(), now()])->sum('total') / 100,
            'month'   => Sale::notVoided()->whereBetween('sale_date', [now()->startOfMonth(), now()])->sum('total') / 100,
            'current' => $current,
            'growth'  => $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 0.0,
            'count'   => Sale::notVoided()->whereBetween('sale_date', [$start, $end])->count(),
        ];

        // ─── FIX #2 — Profit: add whereNull('sales.voided_at') to all joins ──
        // FIX #6 — margin_pct guarded against zero revenue
        $margin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')                          // ← void guard
            ->whereBetween('sales.sale_date', [$start, $end])
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;

        $todayMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')                          // ← void guard
            ->whereDate('sales.sale_date', today())
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;

        $weekMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')                          // ← void guard
            ->whereBetween('sales.sale_date', [now()->startOfWeek(), now()])
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;

        $monthMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')                          // ← void guard
            ->whereBetween('sales.sale_date', [now()->startOfMonth(), now()])
            ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                            * sale_items.quantity_sold) as margin')
            ->value('margin') ?? 0) / 100;

        // FIX #6 — guard zero-revenue: show 0% instead of nonsense value
        $this->profit = [
            'today'       => $todayMargin,
            'week'        => $weekMargin,
            'month'       => $monthMargin,
            'margin_rwf'  => $margin,
            // "Realised margin" — profit on sales in the selected period
            'margin_pct'  => $current > 0 ? round(($margin / $current) * 100, 1) : 0,
            'margin_label'=> 'Realised margin',
        ];

        // ─── FIX #1 — Inventory: unified Box::available() scope ──────────────
        // Box::available() ≡ status IN ('full','partial') AND items_remaining > 0
        // Same filter used in DashboardController for the static Inventory Health section.
        $inv = Box::available()
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('
                SUM(boxes.items_remaining * products.purchase_price) AS cost_value,
                SUM(boxes.items_remaining * products.selling_price)  AS retail_value
            ')
            ->first();

        $cost   = ($inv->cost_value   ?? 0) / 100;
        $retail = ($inv->retail_value ?? 0) / 100;

        // Warehouse sub-total (retail) — also using available()
        $whInv = (Box::available()
            ->where('boxes.location_type', 'warehouse')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('SUM(boxes.items_remaining * products.selling_price) AS retail_value')
            ->value('retail_value') ?? 0) / 100;

        // Shop sub-total (retail) — also using available()
        $shopInv = (Box::available()
            ->where('boxes.location_type', 'shop')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('SUM(boxes.items_remaining * products.selling_price) AS retail_value')
            ->value('retail_value') ?? 0) / 100;

        // Item counts from the same available() filter
        $whItems   = Box::available()->where('location_type', 'warehouse')->sum('items_remaining');
        $shopItems = Box::available()->where('location_type', 'shop')->sum('items_remaining');

        $this->inventory = [
            'cost'        => $cost,
            'retail'      => $retail,
            // FIX #6 — labelled "Potential markup" to distinguish from realised margin
            'markup_pct'  => $cost > 0 ? round((($retail - $cost) / $cost) * 100, 1) : 0,
            'markup_label'=> 'Potential markup',
            'warehouse'   => $whInv,
            'shop'        => $shopInv,
            'wh_items'    => $whItems,
            'shop_items'  => $shopItems,
        ];

        $this->locations = [
            'warehouses' => Warehouse::count(),
            'shops'      => Shop::count(),
            'users'      => User::count(),
        ];
    }

    // ─── Period helpers ───────────────────────────────────────────────────────

    private function periodRange(): array
    {
        return match ($this->period) {
            'today'   => [today(),                 now()->endOfDay()],
            'week'    => [now()->startOfWeek(),    now()->endOfDay()],
            'month'   => [now()->startOfMonth(),   now()->endOfDay()],
            'quarter' => [now()->startOfQuarter(), now()->endOfDay()],
            'year'    => [now()->startOfYear(),    now()->endOfDay()],
            'custom'  => [
                Carbon::parse($this->from ?? today())->startOfDay(),
                Carbon::parse($this->to   ?? today())->endOfDay(),
            ],
            default   => [today(), now()->endOfDay()],
        };
    }

    private function previousRange(): array
    {
        [$start, $end] = $this->periodRange();
        $diff = $start->diffInDays($end) + 1;
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