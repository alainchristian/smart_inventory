<?php

namespace App\Livewire\Shop;

use App\Enums\LocationType;
use App\Models\Box;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StockLevels extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $statusFilter  = 'in_stock'; // in_stock | low | previously_stocked
    public int    $shopId;

    protected $queryString = [
        'search'       => ['except' => ''],
        'statusFilter' => ['except' => 'in_stock'],
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $this->shopId = $user->isOwner()
            ? (session('selected_shop_id') ?? $user->location_id)
            : $user->location_id;
    }

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function render()
    {
        // ── 1. Products with boxes currently at this shop ─────────────────
        $currentStockQuery = DB::table('boxes')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('boxes.location_type', LocationType::SHOP->value)
            ->where('boxes.location_id', $this->shopId)
            ->whereIn('boxes.status', ['full', 'partial'])
            ->where('boxes.items_remaining', '>', 0)
            ->whereNull('products.deleted_at')
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q2) use ($term) {
                    $q2->where('products.name',    'ilike', $term)
                       ->orWhere('products.sku',    'ilike', $term)
                       ->orWhere('products.barcode', 'ilike', $term);
                });
            })
            ->select(
                'products.id as product_id',
                'products.name',
                'products.sku',
                'products.barcode',
                'products.selling_price',
                'products.low_stock_threshold',
                'products.items_per_box',
                'categories.name as category_name',
                DB::raw("SUM(CASE WHEN boxes.status = 'full'    THEN 1 ELSE 0 END) as full_boxes"),
                DB::raw("SUM(CASE WHEN boxes.status = 'partial' THEN 1 ELSE 0 END) as partial_boxes"),
                DB::raw('COUNT(boxes.id) as total_boxes'),
                DB::raw('SUM(boxes.items_remaining) as total_items')
            )
            ->groupBy(
                'products.id', 'products.name', 'products.sku',
                'products.barcode', 'products.selling_price',
                'products.low_stock_threshold', 'products.items_per_box',
                'categories.name'
            );

        // Apply low stock filter
        if ($this->statusFilter === 'low') {
            $currentStockQuery->havingRaw(
                'SUM(boxes.items_remaining) <= products.low_stock_threshold'
            );
        }

        // ── 2. Summary KPIs (always across full shop, ignoring search) ────
        $kpis = DB::table('boxes')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->where('boxes.location_type', LocationType::SHOP->value)
            ->where('boxes.location_id', $this->shopId)
            ->whereIn('boxes.status', ['full', 'partial'])
            ->where('boxes.items_remaining', '>', 0)
            ->whereNull('products.deleted_at')
            ->selectRaw('
                COUNT(DISTINCT boxes.product_id) as product_count,
                COUNT(boxes.id) as total_boxes,
                SUM(boxes.items_remaining) as total_items,
                COUNT(CASE WHEN boxes.items_remaining <= products.low_stock_threshold
                           THEN 1 END) as low_stock_count
            ')
            ->first();

        // ── 3. Previously stocked products (transferred but now out) ──────
        $previouslyStocked = collect();
        if ($this->statusFilter === 'previously_stocked') {
            // Products that have been received at this shop via transfers
            // but currently have zero boxes here
            $currentProductIds = DB::table('boxes')
                ->where('location_type', LocationType::SHOP->value)
                ->where('location_id', $this->shopId)
                ->whereIn('status', ['full', 'partial'])
                ->where('items_remaining', '>', 0)
                ->pluck('product_id');

            $previouslyStocked = DB::table('transfer_boxes')
                ->join('transfers', 'transfer_boxes.transfer_id', '=', 'transfers.id')
                ->join('boxes', 'transfer_boxes.box_id', '=', 'boxes.id')
                ->join('products', 'boxes.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->where('transfers.to_shop_id', $this->shopId)
                ->where('transfers.status', 'received')
                ->whereNotIn('boxes.product_id', $currentProductIds)
                ->whereNull('products.deleted_at')
                ->when($this->search, function ($q) {
                    $term = '%' . $this->search . '%';
                    $q->where(function ($q2) use ($term) {
                        $q2->where('products.name',    'ilike', $term)
                           ->orWhere('products.sku',    'ilike', $term);
                    });
                })
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.sku',
                    'products.selling_price',
                    'products.items_per_box',
                    'categories.name as category_name',
                    DB::raw('MAX(transfers.received_at) as last_received_at')
                )
                ->groupBy(
                    'products.id', 'products.name', 'products.sku',
                    'products.selling_price', 'products.items_per_box',
                    'categories.name'
                )
                ->orderByDesc('last_received_at')
                ->limit(50)
                ->get();
        }

        // Paginate current stock
        $stockData = $currentStockQuery
            ->orderByRaw('SUM(boxes.items_remaining) / products.low_stock_threshold ASC')
            ->paginate(24);

        return view('livewire.shop.stock-levels', [
            'stockData'         => $stockData,
            'kpis'              => $kpis,
            'previouslyStocked' => $previouslyStocked,
        ]);
    }
}
