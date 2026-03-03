<?php

namespace App\Livewire\Owner\Products;

use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductKpiRow extends Component
{
    public string  $period = 'month';
    public ?string $from   = null;
    public ?string $to     = null;

    // Match the named-parameter format that TimeFilter.php dispatches:
    // $this->dispatch('time-filter-changed', period: ..., from: ..., to: ...)
    #[On('time-filter-changed')]
    public function refresh(string $period, ?string $from = null, ?string $to = null): void
    {
        $this->period = $period;
        $this->from   = $from;
        $this->to     = $to;
    }

    private function periodRange(): array
    {
        return match ($this->period) {
            'today'   => [today(),                      now()->endOfDay()],
            'week'    => [now()->startOfWeek(),          now()->endOfDay()],
            'quarter' => [now()->startOfQuarter(),       now()->endOfDay()],
            'year'    => [now()->startOfYear(),          now()->endOfDay()],
            'custom'  => [$this->from ?? today(),        $this->to ?? now()->endOfDay()],
            default   => [now()->startOfMonth(),         now()->endOfDay()],
        };
    }

    public function render()
    {
        [$start, $end] = $this->periodRange();

        // 1. Active / inactive counts
        $totalActive   = Product::where('is_active', true)->count();
        $totalInactive = Product::where('is_active', false)->count();

        // 2. Low stock: products whose TOTAL items across all locations <= threshold
        $allProducts = Product::where('is_active', true)
            ->withSum(['boxes as total_items' => fn ($q) =>
                $q->whereIn('status', ['full', 'partial'])
                  ->where('items_remaining', '>', 0)
            ], 'items_remaining')
            ->get();

        $lowStockCount  = $allProducts->filter(fn ($p) => ($p->total_items ?? 0) <= $p->low_stock_threshold)->count();
        $zeroStockCount = $allProducts->filter(fn ($p) => ($p->total_items ?? 0) === 0)->count();

        // 3. Price overrides: distinct products with modified price in period
        $priceOverrideCount = SaleItem::where('price_was_modified', true)
            ->whereHas('sale', fn ($q) => $q
                ->whereNull('voided_at')
                ->whereNull('deleted_at')
                ->whereBetween('sale_date', [$start, $end])
            )
            ->distinct('product_id')
            ->count('product_id');

        // 4. Best margin product (owner-only - purchase_price visible)
        $bestMarginProduct = Product::where('is_active', true)
            ->where('purchase_price', '>', 0)
            ->whereColumn('selling_price', '>', 'purchase_price')
            ->orderByRaw('(selling_price - purchase_price)::float / NULLIF(selling_price, 0) DESC')
            ->first();

        $bestMarginPct  = null;
        $bestMarginName = null;
        if ($bestMarginProduct) {
            $bestMarginPct  = round(
                ($bestMarginProduct->selling_price - $bestMarginProduct->purchase_price)
                / $bestMarginProduct->selling_price * 100, 1
            );
            $bestMarginName = $bestMarginProduct->name;
        }

        return view('livewire.owner.products.product-kpi-row', [
            'totalActive'        => $totalActive,
            'totalInactive'      => $totalInactive,
            'totalProducts'      => $totalActive + $totalInactive,
            'lowStockCount'      => $lowStockCount,
            'zeroStockCount'     => $zeroStockCount,
            'priceOverrideCount' => $priceOverrideCount,
            'bestMarginPct'      => $bestMarginPct,
            'bestMarginName'     => $bestMarginName,
            'periodLabel'        => ucfirst($this->period),
        ]);
    }
}