<?php

namespace App\Livewire\Shop\DamagedGoods;

use App\Enums\DispositionType;
use App\Enums\LocationType;
use App\Models\DamagedGood;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class DamagedGoodsList extends Component
{
    use WithPagination;

    public $locationId;
    public $locationType;
    public $locationName;
    public $isOwner = false;

    // Filters
    public $dispositionFilter = 'all'; // all, pending, disposed, return_to_supplier, etc.
    public $locationFilter = 'all'; // all, or specific location_id (owner only)
    public $dateFrom = '';
    public $dateTo = '';
    public $search = '';

    // Disposition decision
    public $showDispositionModal = false;
    public $selectedDamagedGood = null;
    public $dispositionDecision = '';
    public $dispositionNotes = '';

    protected $queryString = [
        'dispositionFilter' => ['except' => 'all'],
        'locationFilter' => ['except' => 'all'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $user = auth()->user();

        // Check authorization
        if (!$user->isShopManager() && !$user->isOwner() && !$user->isWarehouseManager()) {
            abort(403, 'You do not have permission to access damaged goods.');
        }

        // Determine location and permissions
        $this->isOwner = $user->isOwner();

        if ($this->isOwner) {
            // Owner sees all locations
            $this->locationId = null;
            $this->locationType = null;
            $this->locationName = 'All Locations';
        } elseif ($user->isWarehouseManager()) {
            // Warehouse manager sees their warehouse
            $this->locationId = $user->location_id;
            $this->locationType = LocationType::WAREHOUSE;
            $warehouse = Warehouse::find($this->locationId);
            $this->locationName = $warehouse->name ?? 'Unknown Warehouse';
        } else {
            // Shop manager sees their shop
            $this->locationId = $user->location_id;
            $this->locationType = LocationType::SHOP;
            $shop = Shop::find($this->locationId);
            $this->locationName = $shop->name ?? 'Unknown Shop';
        }

        // Default date range to last 30 days
        if (empty($this->dateFrom)) {
            $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        }
        if (empty($this->dateTo)) {
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDispositionFilter()
    {
        $this->resetPage();
    }

    public function updatingLocationFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->dispositionFilter = 'all';
        $this->locationFilter = 'all';
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->search = '';
        $this->resetPage();
    }

    protected function getKpiStats(): array
    {
        $baseQuery = DamagedGood::query();

        // Apply location filter
        if ($this->isOwner) {
            // Owner view: Apply location filter if set
            if ($this->locationFilter !== 'all') {
                $baseQuery->where('location_id', $this->locationFilter);
            }
        } else {
            // Shop/Warehouse manager view: Only their location
            $baseQuery->where('location_type', $this->locationType)
                     ->where('location_id', $this->locationId);
        }

        // Apply date range to KPIs
        if ($this->dateFrom) {
            $baseQuery->whereDate('recorded_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $baseQuery->whereDate('recorded_at', '<=', $this->dateTo);
        }

        return [
            'total_damaged' => (clone $baseQuery)->count(),
            'pending_count' => (clone $baseQuery)->pendingDisposition()->count(),
            'total_quantity' => (clone $baseQuery)->sum('quantity_damaged'),
            'total_loss' => (clone $baseQuery)->sum('estimated_loss'),
        ];
    }

    public function openDispositionModal($damagedGoodId)
    {
        $this->selectedDamagedGood = DamagedGood::with('product')->findOrFail($damagedGoodId);
        $this->dispositionDecision = '';
        $this->dispositionNotes = '';
        $this->showDispositionModal = true;
    }

    public function closeDispositionModal()
    {
        $this->showDispositionModal = false;
        $this->selectedDamagedGood = null;
        $this->dispositionDecision = '';
        $this->dispositionNotes = '';
    }

    public function saveDisposition()
    {
        $this->validate([
            'dispositionDecision' => 'required|in:return_to_supplier,dispose,discount_sale,write_off,repair',
            'dispositionNotes' => 'nullable|string|max:1000',
        ]);

        if (!$this->selectedDamagedGood) {
            session()->flash('error', 'No damaged good selected.');
            return;
        }

        try {
            $user = auth()->user();

            // Update disposition
            $this->selectedDamagedGood->update([
                'disposition' => DispositionType::from($this->dispositionDecision),
                'disposition_decided_by' => $user->id,
                'disposition_decided_at' => now(),
                'disposition_notes' => $this->dispositionNotes,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'disposition_decided',
                'entity_type' => 'DamagedGood',
                'entity_id' => $this->selectedDamagedGood->id,
                'entity_identifier' => $this->selectedDamagedGood->damage_reference,
                'old_values' => [
                    'disposition' => DispositionType::PENDING->value,
                ],
                'new_values' => [
                    'disposition' => $this->dispositionDecision,
                    'disposition_notes' => $this->dispositionNotes,
                ],
                'details' => [
                    'product_id' => $this->selectedDamagedGood->product_id,
                    'quantity_damaged' => $this->selectedDamagedGood->quantity_damaged,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            session()->flash('success', "Disposition decision saved successfully.");
            $this->closeDispositionModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save disposition: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = DamagedGood::query()
            ->with(['product', 'recordedBy', 'dispositionDecidedBy'])
            ->latest('recorded_at');

        // Apply location filter
        if ($this->isOwner) {
            // Owner view: Apply location filter if set
            if ($this->locationFilter !== 'all') {
                $query->where('location_id', $this->locationFilter);
            }
            // Otherwise show all locations
        } else {
            // Shop/Warehouse manager view: Only their location
            $query->where('location_type', $this->locationType)
                 ->where('location_id', $this->locationId);
        }

        // Apply disposition filter
        if ($this->dispositionFilter !== 'all') {
            $disposition = DispositionType::from($this->dispositionFilter);
            $query->byDisposition($disposition);
        }

        // Apply date range filter
        if ($this->dateFrom) {
            $query->whereDate('recorded_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('recorded_at', '<=', $this->dateTo);
        }

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('damage_reference', 'like', '%' . $this->search . '%')
                  ->orWhere('damage_description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('product', function ($productQuery) {
                      $productQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $damagedGoods = $query->paginate(20);

        // Get locations list for owner filter
        $locations = $this->isOwner
            ? collect()
                ->merge(Shop::orderBy('name')->get()->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'type' => 'Shop']))
                ->merge(Warehouse::orderBy('name')->get()->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'type' => 'Warehouse']))
            : collect();

        return view('livewire.shop.damaged-goods.damaged-goods-list', [
            'damagedGoods' => $damagedGoods,
            'kpiStats' => $this->getKpiStats(),
            'locations' => $locations,
            'dispositionTypes' => DispositionType::cases(),
        ]);
    }
}
