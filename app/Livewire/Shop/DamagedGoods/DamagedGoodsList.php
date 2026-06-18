<?php

namespace App\Livewire\Shop\DamagedGoods;

use App\Enums\DispositionType;
use App\Enums\LocationType;
use App\Models\DamagedGood;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class DamagedGoodsList extends Component
{
    use WithPagination;
    use \App\Livewire\Concerns\RequiresOpenSession;

    public ?int    $locationId   = null;
    public ?string $locationType = null; // stored as string ('shop'|'warehouse'|null)
    public string  $locationName = '';
    public bool    $isOwner      = false;

    // Filters
    public string $dispositionFilter = 'all';
    public string $locationFilter    = 'all';
    public string $dateFrom          = '';
    public string $dateTo            = '';
    public string $search            = '';

    // Disposition decision modal
    public bool                $showDispositionModal  = false;
    public ?DamagedGood        $selectedDamagedGood   = null;
    public string              $dispositionDecision   = '';
    public string              $dispositionNotes      = '';

    // Manual record form
    public bool   $showRecordForm       = false;
    public string $recordProductSearch  = '';
    public ?int   $recordProductId      = null;
    public string $recordProductName    = '';
    public array  $recordProductResults = [];
    public bool   $showProductDropdown  = false;
    public string $recordQuantity       = '';
    public string $recordEstimatedLoss  = '';
    public string $recordDescription    = '';
    public string $recordLocationId     = ''; // for owner: selected location

    protected $queryString = [
        'dispositionFilter' => ['except' => 'all'],
        'locationFilter'    => ['except' => 'all'],
        'dateFrom'          => ['except' => ''],
        'dateTo'            => ['except' => ''],
        'search'            => ['except' => ''],
    ];

    public function mount(): void
    {
        $shopId = auth()->user()->location_id;
        if (!$this->checkSession($shopId)) {
            return;
        }

        $user = auth()->user();

        if (!$user->isShopManager() && !$user->isOwner() && !$user->isWarehouseManager()) {
            abort(403, 'You do not have permission to access damaged goods.');
        }

        $this->isOwner = $user->isOwner();

        if ($this->isOwner) {
            $this->locationId   = null;
            $this->locationType = null;
            $this->locationName = 'All Locations';
        } elseif ($user->isWarehouseManager()) {
            $this->locationId   = $user->location_id;
            $this->locationType = LocationType::WAREHOUSE->value;
            $warehouse = Warehouse::find($this->locationId);
            $this->locationName = $warehouse->name ?? 'Unknown Warehouse';
        } else {
            $this->locationId   = $user->location_id;
            $this->locationType = LocationType::SHOP->value;
            $shop = Shop::find($this->locationId);
            $this->locationName = $shop->name ?? 'Unknown Shop';
        }

        if (empty($this->dateFrom)) {
            $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        }
        if (empty($this->dateTo)) {
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    // ── Filter actions ──────────────────────────────────────────────────────────

    public function setDispositionFilter(string $value): void
    {
        $this->dispositionFilter = $value;
        $this->resetPage();
    }

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingLocationFilter(): void { $this->resetPage(); }
    public function updatingDateFrom(): void     { $this->resetPage(); }
    public function updatingDateTo(): void       { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->dispositionFilter = 'all';
        $this->locationFilter    = 'all';
        $this->dateFrom          = now()->subDays(30)->format('Y-m-d');
        $this->dateTo            = now()->format('Y-m-d');
        $this->search            = '';
        $this->resetPage();
    }

    // ── Disposition modal ────────────────────────────────────────────────────────

    public function openDispositionModal(int $damagedGoodId): void
    {
        $this->selectedDamagedGood = DamagedGood::with('product')->findOrFail($damagedGoodId);
        $this->dispositionDecision = '';
        $this->dispositionNotes    = '';
        $this->showDispositionModal = true;
    }

    public function closeDispositionModal(): void
    {
        $this->showDispositionModal = false;
        $this->selectedDamagedGood = null;
        $this->dispositionDecision = '';
        $this->dispositionNotes    = '';
    }

    public function saveDisposition(): void
    {
        $this->validate([
            'dispositionDecision' => 'required|in:return_to_supplier,dispose,discount_sale,write_off,repair',
            'dispositionNotes'    => 'nullable|string|max:1000',
        ]);

        if (!$this->selectedDamagedGood) {
            session()->flash('error', 'No damaged good selected.');
            return;
        }

        try {
            $user = auth()->user();

            $this->selectedDamagedGood->update([
                'disposition'              => DispositionType::from($this->dispositionDecision),
                'disposition_decided_by'   => $user->id,
                'disposition_decided_at'   => now(),
                'disposition_notes'        => $this->dispositionNotes,
            ]);

            ActivityLog::create([
                'user_id'           => $user->id,
                'user_name'         => $user->name,
                'action'            => 'disposition_decided',
                'entity_type'       => 'DamagedGood',
                'entity_id'         => $this->selectedDamagedGood->id,
                'entity_identifier' => $this->selectedDamagedGood->damage_reference,
                'old_values'        => ['disposition' => DispositionType::PENDING->value],
                'new_values'        => ['disposition' => $this->dispositionDecision, 'notes' => $this->dispositionNotes],
                'ip_address'        => request()->ip(),
                'user_agent'        => request()->header('User-Agent'),
            ]);

            session()->flash('success', 'Disposition saved.');
            $this->closeDispositionModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed: ' . $e->getMessage());
        }
    }

    // ── Manual record form ───────────────────────────────────────────────────────

    public function openRecordForm(): void
    {
        $this->showRecordForm = true;
        $this->resetRecordForm();
    }

    public function closeRecordForm(): void
    {
        $this->showRecordForm = false;
        $this->resetRecordForm();
    }

    protected function resetRecordForm(): void
    {
        $this->recordProductSearch  = '';
        $this->recordProductId      = null;
        $this->recordProductName    = '';
        $this->recordProductResults = [];
        $this->showProductDropdown  = false;
        $this->recordQuantity       = '';
        $this->recordEstimatedLoss  = '';
        $this->recordDescription    = '';
        $this->recordLocationId     = '';
    }

    public function searchRecordProduct(): void
    {
        if (strlen($this->recordProductSearch) < 2) {
            $this->recordProductResults = [];
            $this->showProductDropdown  = false;
            return;
        }

        $this->recordProductResults = Product::where('name', 'ilike', '%' . $this->recordProductSearch . '%')
            ->orWhere('sku', 'ilike', '%' . $this->recordProductSearch . '%')
            ->limit(8)
            ->get(['id', 'name', 'sku'])
            ->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku ?? ''])
            ->toArray();

        $this->showProductDropdown = count($this->recordProductResults) > 0;
    }

    public function selectRecordProduct(int $id, string $name): void
    {
        $this->recordProductId     = $id;
        $this->recordProductName   = $name;
        $this->recordProductSearch = $name;
        $this->showProductDropdown = false;
        $this->recordProductResults = [];
    }

    public function saveRecord(): void
    {
        $rules = [
            'recordProductId'     => 'required|integer|exists:products,id',
            'recordQuantity'      => 'required|integer|min:1',
            'recordEstimatedLoss' => 'required|integer|min:0',
            'recordDescription'   => 'nullable|string|max:1000',
        ];

        if ($this->isOwner) {
            $rules['recordLocationId'] = 'required|integer';
        }

        $this->validate($rules);

        try {
            $user = auth()->user();

            // Resolve location for owner
            $locType = $this->locationType ? LocationType::from($this->locationType) : null;
            $locId   = $this->locationId;

            if ($this->isOwner && $this->recordLocationId) {
                // Owner picks a specific shop
                $locType = LocationType::SHOP;
                $locId   = (int) $this->recordLocationId;
            }

            // Generate reference: DG-YYYY-NNNN
            $lastRef = DamagedGood::latest('id')->value('damage_reference') ?? '';
            $nextNum = 1;
            if ($lastRef && preg_match('/(\d+)$/', $lastRef, $m)) {
                $nextNum = (int) $m[1] + 1;
            }
            $ref = 'DG-' . now()->format('Y') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            DamagedGood::create([
                'damage_reference'   => $ref,
                'source_type'        => 'manual',
                'product_id'         => $this->recordProductId,
                'quantity_damaged'   => (int) $this->recordQuantity,
                'location_type'      => $locType,
                'location_id'        => $locId,
                'disposition'        => DispositionType::PENDING,
                'damage_description' => $this->recordDescription ?: null,
                'estimated_loss'     => (int) $this->recordEstimatedLoss,
                'recorded_by'        => $user->id,
                'recorded_at'        => now(),
            ]);

            ActivityLog::create([
                'user_id'           => $user->id,
                'user_name'         => $user->name,
                'action'            => 'damaged_good_recorded',
                'entity_type'       => 'DamagedGood',
                'entity_identifier' => $ref,
                'new_values'        => [
                    'product_id'       => $this->recordProductId,
                    'quantity_damaged' => $this->recordQuantity,
                    'estimated_loss'   => $this->recordEstimatedLoss,
                    'source'           => 'manual',
                ],
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->header('User-Agent'),
            ]);

            session()->flash('success', "Damage record {$ref} created.");
            $this->closeRecordForm();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to record: ' . $e->getMessage());
        }
    }

    // ── KPI stats ────────────────────────────────────────────────────────────────

    protected function getKpiStats(): array
    {
        $q = DamagedGood::query();

        if ($this->isOwner) {
            if ($this->locationFilter !== 'all') {
                $q->where('location_id', (int) $this->locationFilter);
            }
        } else {
            $q->where('location_type', $this->locationType)
              ->where('location_id', $this->locationId);
        }

        if ($this->dateFrom) {
            $q->whereDate('recorded_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $q->whereDate('recorded_at', '<=', $this->dateTo);
        }

        return [
            'total_damaged'  => (clone $q)->count(),
            'pending_count'  => (clone $q)->pendingDisposition()->count(),
            'total_quantity' => (clone $q)->sum('quantity_damaged'),
            'total_loss'     => (clone $q)->sum('estimated_loss'),
        ];
    }

    // ── Render ───────────────────────────────────────────────────────────────────

    public function render()
    {
        $query = DamagedGood::query()
            ->with(['product', 'recordedBy', 'dispositionDecidedBy'])
            ->latest('recorded_at');

        if ($this->isOwner) {
            if ($this->locationFilter !== 'all') {
                $query->where('location_id', (int) $this->locationFilter);
            }
        } else {
            $query->where('location_type', $this->locationType)
                  ->where('location_id', $this->locationId);
        }

        if ($this->dispositionFilter !== 'all') {
            $query->byDisposition(DispositionType::from($this->dispositionFilter));
        }

        if ($this->dateFrom) {
            $query->whereDate('recorded_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('recorded_at', '<=', $this->dateTo);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('damage_reference', 'ilike', '%' . $this->search . '%')
                  ->orWhere('damage_description', 'ilike', '%' . $this->search . '%')
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'ilike', '%' . $this->search . '%'));
            });
        }

        $damagedGoods = $query->paginate(20);

        $locations = $this->isOwner
            ? collect()
                ->merge(Shop::orderBy('name')->get()->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'type' => 'Shop']))
                ->merge(Warehouse::orderBy('name')->get()->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'type' => 'Warehouse']))
            : collect();

        // Shops for owner record form
        $shops = $this->isOwner
            ? Shop::where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('livewire.shop.damaged-goods.damaged-goods-list', [
            'damagedGoods'    => $damagedGoods,
            'kpiStats'        => $this->getKpiStats(),
            'locations'       => $locations,
            'shops'           => $shops,
            'dispositionTypes' => DispositionType::cases(),
        ]);
    }
}
