<?php

namespace App\Livewire\Inventory\Boxes;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use App\Models\Box;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;

class BoxList extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $locationType = null;
    public ?int $locationId = null;
    public ?int $productId = null;
    public ?string $status = null;
    public bool $expiringOnly = false;
    public int $expiringDays = 30;

    protected $queryString = [
        'search' => ['except' => ''],
        'locationType' => ['except' => null],
        'locationId' => ['except' => null],
        'productId' => ['except' => null],
        'status' => ['except' => null],
    ];

    public function mount()
    {
        $user = auth()->user();

        // Auto-set location for non-owners
        if ($user->isWarehouseManager()) {
            $this->locationType = LocationType::WAREHOUSE->value;
            $this->locationId = $user->location_id;
        } elseif ($user->isShopManager()) {
            $this->locationType = LocationType::SHOP->value;
            $this->locationId = $user->location_id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingProductId()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'productId', 'status', 'expiringOnly']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Box::query()
            ->with(['product.category', 'location', 'receivedBy'])
            ->when($this->search, function ($q) {
                $q->where('box_code', 'ILIKE', "%{$this->search}%");
            })
            ->when($this->locationType && $this->locationId, function ($q) {
                $q->where('location_type', $this->locationType)
                  ->where('location_id', $this->locationId);
            })
            ->when($this->productId, function ($q) {
                $q->where('product_id', $this->productId);
            })
            ->when($this->status, function ($q) {
                $q->where('status', $this->status);
            })
            ->when($this->expiringOnly, function ($q) {
                $q->expiringSoon($this->expiringDays);
            })
            ->orderBy('created_at', 'desc');

        // Cache summary statistics
        $stats = cache()->remember(
            "box-stats-{$this->locationType}-{$this->locationId}-" . now()->format('Y-m-d-H'),
            3600,
            function () {
                $baseQuery = Box::query();

                if ($this->locationType && $this->locationId) {
                    $baseQuery->where('location_type', $this->locationType)
                             ->where('location_id', $this->locationId);
                }

                return [
                    'total' => (clone $baseQuery)->count(),
                    'full' => (clone $baseQuery)->where('status', BoxStatus::FULL)->count(),
                    'partial' => (clone $baseQuery)->where('status', BoxStatus::PARTIAL)->count(),
                    'empty' => (clone $baseQuery)->where('status', BoxStatus::EMPTY)->count(),
                    'damaged' => (clone $baseQuery)->where('status', BoxStatus::DAMAGED)->count(),
                    'total_items' => (clone $baseQuery)->sum('items_remaining'),
                ];
            }
        );

        return view('livewire.inventory.boxes.box-list', [
            'boxes' => $query->paginate(50),
            'stats' => $stats,
            'products' => Product::active()->orderBy('name')->get(),
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
            'shops' => Shop::active()->orderBy('name')->get(),
            'statuses' => BoxStatus::cases(),
        ]);
    }
}
