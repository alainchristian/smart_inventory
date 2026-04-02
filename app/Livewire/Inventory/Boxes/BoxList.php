<?php

namespace App\Livewire\Inventory\Boxes;

use App\Enums\BoxStatus;
use App\Models\Box;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BoxList extends Component
{
    use WithPagination;

    public string  $search        = '';
    public ?string $locationType  = null;
    public ?int    $locationId    = null;
    public ?int    $productId     = null;
    public ?string $status        = null;
    public bool    $expiringOnly  = false;
    public int     $expiringDays  = 30;
    public string  $sortBy        = 'received_at';
    public string  $sortDirection = 'desc';

    protected $queryString = [
        'search'        => ['except' => ''],
        'locationType'  => ['except' => null],
        'locationId'    => ['except' => null],
        'productId'     => ['except' => null],
        'status'        => ['except' => null],
        'sortBy'        => ['except' => 'received_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $user = auth()->user();

        if (!$user->isOwner() && !$user->isAdmin()) {
            abort(403);
        }
    }

    public function updatingLocationType(): void
    {
        $this->locationId = null;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingProductId(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function sortColumn(string $field): void
    {
        $allowed = ['received_at', 'items_remaining', 'status', 'expiry_date', 'cost_value'];
        if (!in_array($field, $allowed)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy        = $field;
            $this->sortDirection = 'desc';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'productId', 'status', 'expiringOnly']);
        $this->resetPage();
    }

    public function render()
    {
        // ── Main query ─────────────────────────────────────────────────────
        $query = Box::query()
            ->with(['product.category', 'location', 'receivedBy'])
            ->where('status', '!=', 'empty')   // empty = consumed; exclude from inventory view
            ->when($this->search, fn ($q) =>
                $q->where('box_code', 'ILIKE', "%{$this->search}%")
            )
            ->when($this->locationType && $this->locationId, fn ($q) =>
                $q->where('location_type', $this->locationType)
                  ->where('location_id', $this->locationId)
            )
            ->when($this->locationType && !$this->locationId, fn ($q) =>
                $q->where('location_type', $this->locationType)
            )
            ->when($this->productId, fn ($q) =>
                $q->where('product_id', $this->productId)
            )
            ->when($this->status, fn ($q) =>
                $q->where('status', $this->status)
            )
            ->when($this->expiringOnly, fn ($q) =>
                $q->expiringSoon($this->expiringDays)
            )
            ->when($this->sortBy === 'cost_value', fn ($q) =>
                $q->join('products as p_sort', 'boxes.product_id', '=', 'p_sort.id')
                  ->orderByRaw('(boxes.items_remaining * p_sort.purchase_price) ' . $this->sortDirection)
                  ->select('boxes.*')
            )
            ->when($this->sortBy !== 'cost_value', fn ($q) =>
                $q->orderBy('boxes.' . $this->sortBy, $this->sortDirection)
            );

        // ── Enriched stats ─────────────────────────────────────────────────
        $statsQuery = DB::table('boxes')
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->when($this->locationType && $this->locationId, fn ($q) =>
                $q->where('boxes.location_type', $this->locationType)
                  ->where('boxes.location_id', $this->locationId)
            )
            ->when($this->locationType && !$this->locationId, fn ($q) =>
                $q->where('boxes.location_type', $this->locationType)
            );

        $stats = (clone $statsQuery)->selectRaw("
            COUNT(*)                                                          AS total,
            SUM(CASE WHEN boxes.status = 'full'    THEN 1 ELSE 0 END)        AS full_count,
            SUM(CASE WHEN boxes.status = 'partial' THEN 1 ELSE 0 END)        AS partial_count,
            SUM(CASE WHEN boxes.status = 'empty'   THEN 1 ELSE 0 END)        AS empty_count,
            SUM(CASE WHEN boxes.status = 'damaged' THEN 1 ELSE 0 END)        AS damaged_count,
            SUM(CASE WHEN boxes.status != 'empty' THEN boxes.items_remaining ELSE 0 END) AS total_items,
            SUM(CASE WHEN boxes.status != 'empty' THEN boxes.items_total     ELSE 0 END) AS total_capacity,
            SUM(CASE WHEN boxes.status IN ('full','partial') AND boxes.items_remaining > 0
                     THEN boxes.items_remaining * products.purchase_price ELSE 0 END) AS cost_value,
            SUM(CASE WHEN boxes.status IN ('full','partial') AND boxes.items_remaining > 0
                     THEN boxes.items_remaining * products.selling_price  ELSE 0 END) AS retail_value,
            SUM(CASE WHEN boxes.status != 'empty'
                     AND boxes.expiry_date IS NOT NULL
                     AND boxes.expiry_date <= NOW() + INTERVAL '30 days'
                     AND boxes.expiry_date >= NOW()
                     THEN 1 ELSE 0 END)                                        AS expiring_soon
        ")->first();

        $fillableBases = (clone $statsQuery)
            ->whereIn('boxes.status', ['full', 'partial'])
            ->selectRaw('SUM(boxes.items_remaining) as remaining, SUM(boxes.items_total) as total')
            ->first();

        $fillRate = ($fillableBases->total > 0)
            ? round(($fillableBases->remaining / $fillableBases->total) * 100, 1)
            : null;

        $stagnantCount = DB::table('boxes')
            ->when($this->locationType && $this->locationId, fn ($q) =>
                $q->where('location_type', $this->locationType)
                  ->where('location_id', $this->locationId)
            )
            ->when($this->locationType && !$this->locationId, fn ($q) =>
                $q->where('location_type', $this->locationType)
            )
            ->whereIn('status', ['full', 'partial'])
            ->where('items_remaining', '>', 0)
            ->whereNotExists(fn ($q) =>
                $q->select(DB::raw(1))
                  ->from('box_movements')
                  ->whereColumn('box_movements.box_id', 'boxes.id')
                  ->where('box_movements.moved_at', '>=', now()->subDays(30))
            )
            ->count();

        return view('livewire.inventory.boxes.box-list', [
            'boxes'         => $query->paginate(50),
            'stats'         => $stats,
            'fillRate'      => $fillRate,
            'stagnantCount' => $stagnantCount,
            'isOwner'       => auth()->user()->isOwner() || auth()->user()->isAdmin(),
            'products'      => Product::active()->orderBy('name')->get(),
            'warehouses'    => Warehouse::active()->orderBy('name')->get(),
            'shops'         => Shop::active()->orderBy('name')->get(),
            'statuses'      => array_filter(BoxStatus::cases(), fn ($s) => $s !== BoxStatus::EMPTY),
        ]);
    }
}
