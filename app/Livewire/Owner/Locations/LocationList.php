<?php

namespace App\Livewire\Owner\Locations;

use App\Models\ActivityLog;
use App\Models\Box;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LocationList extends Component
{
    use WithPagination;

    // ── Tab ───────────────────────────────────────────────────────────────────
    public string $activeTab = 'warehouses'; // warehouses | shops

    // ── Filters ───────────────────────────────────────────────────────────────
    public string $search       = '';
    public string $statusFilter = 'all'; // all | active | inactive

    // ── Drawer ────────────────────────────────────────────────────────────────
    public bool  $showDrawer = false;
    public bool  $isEditing  = false;
    public ?int  $editingId  = null;

    // ── Shared form fields ────────────────────────────────────────────────────
    public string $form_name         = '';
    public string $form_code         = '';
    public string $form_address      = '';
    public string $form_city         = '';
    public string $form_phone        = '';
    public bool   $form_is_active    = true;

    // ── Shop-only field ───────────────────────────────────────────────────────
    public ?int   $form_default_warehouse_id = null;

    // ── Toggle confirmation ───────────────────────────────────────────────────
    public ?int   $confirmToggleId     = null;
    public bool   $confirmToggleActive = false;
    public string $confirmToggleName   = '';
    public string $confirmToggleWarning = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(string $tab = 'warehouses'): void
    {
        if (!auth()->user()->isOwner()) abort(403);
        $this->activeTab = $tab;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->search    = '';
        $this->statusFilter = 'all';
        $this->closeDrawer();
        $this->resetPage();
    }

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    // ── Drawer open ───────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing  = false;
        $this->editingId  = null;
        $this->showDrawer = true;
    }

    public function openEdit(int $id): void
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->editingId = $id;

        if ($this->activeTab === 'warehouses') {
            $loc = Warehouse::findOrFail($id);
        } else {
            $loc = Shop::findOrFail($id);
        }

        $this->form_name      = $loc->name;
        $this->form_code      = $loc->code;
        $this->form_address   = $loc->address ?? '';
        $this->form_city      = $loc->city ?? '';
        $this->form_phone     = $loc->phone ?? '';
        $this->form_is_active = $loc->is_active;

        if ($this->activeTab === 'shops') {
            $this->form_default_warehouse_id = $loc->default_warehouse_id;
        }

        $this->showDrawer = true;
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->resetForm();
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function save(): void
    {
        $table = $this->activeTab === 'warehouses' ? 'warehouses' : 'shops';
        $uniqueCodeRule = 'required|string|max:20|unique:' . $table . ',code'
            . ($this->isEditing ? ",{$this->editingId}" : '');

        $rules = [
            'form_name'      => 'required|string|min:2|max:100',
            'form_code'      => $uniqueCodeRule,
            'form_address'   => 'nullable|string|max:200',
            'form_city'      => 'nullable|string|max:100',
            'form_phone'     => 'nullable|string|max:20',
            'form_is_active' => 'boolean',
        ];

        if ($this->activeTab === 'shops') {
            $rules['form_default_warehouse_id'] = 'required|exists:warehouses,id';
        }

        $this->validate($rules, [
            'form_name.required'                  => 'Location name is required.',
            'form_code.required'                  => 'A short code is required.',
            'form_code.unique'                    => 'This code is already taken.',
            'form_default_warehouse_id.required'  => 'Please select a default warehouse.',
        ]);

        $data = [
            'name'      => trim($this->form_name),
            'code'      => strtoupper(trim($this->form_code)),
            'address'   => $this->form_address ?: null,
            'city'      => $this->form_city ?: null,
            'phone'     => $this->form_phone ?: null,
            'is_active' => $this->form_is_active,
        ];

        if ($this->activeTab === 'shops') {
            $data['default_warehouse_id'] = $this->form_default_warehouse_id;
        }

        if ($this->activeTab === 'warehouses') {
            if ($this->isEditing) {
                $loc = Warehouse::findOrFail($this->editingId);
                $old = $loc->only(['name','code','is_active']);
                $loc->update($data);
                $action = 'updated';
                $msg    = 'Warehouse updated successfully.';
            } else {
                $loc    = Warehouse::create($data);
                $old    = null;
                $action = 'created';
                $msg    = 'Warehouse created successfully.';
            }
            $entityType = 'Warehouse';
        } else {
            if ($this->isEditing) {
                $loc = Shop::findOrFail($this->editingId);
                $old = $loc->only(['name','code','is_active']);
                $loc->update($data);
                $action = 'updated';
                $msg    = 'Shop updated successfully.';
            } else {
                $loc    = Shop::create($data);
                $old    = null;
                $action = 'created';
                $msg    = 'Shop created successfully.';
            }
            $entityType = 'Shop';
        }

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()->name,
            'action'            => $action,
            'entity_type'       => $entityType,
            'entity_id'         => $loc->id,
            'entity_identifier' => $loc->code,
            'old_values'        => $old,
            'new_values'        => $loc->fresh()->only(['name','code','is_active']),
            'ip_address'        => request()->ip(),
        ]);

        $this->dispatch('notification', ['type' => 'success', 'message' => $msg]);
        $this->closeDrawer();
    }

    // ── Toggle active ─────────────────────────────────────────────────────────

    public function confirmToggle(int $id): void
    {
        if ($this->activeTab === 'warehouses') {
            $loc = Warehouse::findOrFail($id);
        } else {
            $loc = Shop::findOrFail($id);
        }

        // Check for blocking conditions
        $warning = '';

        if ($loc->is_active) {
            // Check for active transfers
            if ($this->activeTab === 'warehouses') {
                $activeTransfers = Transfer::where('from_warehouse_id', $id)
                    ->whereIn('status', ['pending','approved','in_transit','delivered'])
                    ->count();
                if ($activeTransfers > 0) {
                    $warning = "{$activeTransfers} active transfer(s) in progress from this warehouse.";
                }
            } else {
                $activeTransfers = Transfer::where('to_shop_id', $id)
                    ->whereIn('status', ['pending','approved','in_transit','delivered'])
                    ->count();
                if ($activeTransfers > 0) {
                    $warning = "{$activeTransfers} active transfer(s) in progress to this shop.";
                }
            }

            // Check for assigned users
            $assignedUsers = User::where('location_id', $id)
                ->where('location_type', $this->activeTab === 'warehouses' ? 'warehouse' : 'shop')
                ->where('is_active', true)
                ->count();
            if ($assignedUsers > 0) {
                $warning .= ($warning ? ' ' : '') .
                    "{$assignedUsers} active user(s) are assigned to this location.";
            }
        }

        $this->confirmToggleId      = $id;
        $this->confirmToggleActive  = $loc->is_active;
        $this->confirmToggleName    = $loc->name;
        $this->confirmToggleWarning = $warning;
    }

    public function executeToggle(): void
    {
        if (!$this->confirmToggleId) return;

        if ($this->activeTab === 'warehouses') {
            $loc = Warehouse::findOrFail($this->confirmToggleId);
        } else {
            $loc = Shop::findOrFail($this->confirmToggleId);
        }

        $newState = !$loc->is_active;
        $loc->update(['is_active' => $newState]);

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()->name,
            'action'            => $newState ? 'activated' : 'deactivated',
            'entity_type'       => $this->activeTab === 'warehouses' ? 'Warehouse' : 'Shop',
            'entity_id'         => $loc->id,
            'entity_identifier' => $loc->code,
            'old_values'        => ['is_active' => !$newState],
            'new_values'        => ['is_active' => $newState],
            'ip_address'        => request()->ip(),
        ]);

        $label = $newState ? 'activated' : 'deactivated';
        $this->dispatch('notification', [
            'type'    => $newState ? 'success' : 'warning',
            'message' => "{$loc->name} has been {$label}.",
        ]);

        $this->cancelToggle();
    }

    public function cancelToggle(): void
    {
        $this->confirmToggleId      = null;
        $this->confirmToggleName    = '';
        $this->confirmToggleWarning = '';
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->form_name                 = '';
        $this->form_code                 = '';
        $this->form_address              = '';
        $this->form_city                 = '';
        $this->form_phone                = '';
        $this->form_is_active            = true;
        $this->form_default_warehouse_id = null;
        $this->resetValidation();
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getActiveWarehousesProperty()
    {
        return Warehouse::where('is_active', true)->orderBy('name')->get(['id','name']);
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        // Stats (always both, for tab badges)
        $stats = [
            'warehouses_total'  => Warehouse::count(),
            'warehouses_active' => Warehouse::where('is_active', true)->count(),
            'shops_total'       => Shop::count(),
            'shops_active'      => Shop::where('is_active', true)->count(),
        ];

        if ($this->activeTab === 'warehouses') {
            $rows = Warehouse::query()
                ->withCount([
                    'users as manager_count' => fn($q) => $q->where('is_active', true),
                    'transfersFrom as active_transfers' => fn($q) =>
                        $q->whereIn('status', ['pending','approved','in_transit','delivered']),
                ])
                ->addSelect(DB::raw(
                    '(SELECT COUNT(*) FROM boxes
                      WHERE boxes.location_type = \'warehouse\'
                      AND boxes.location_id = warehouses.id
                      AND boxes.status IN (\'full\',\'partial\')
                      AND boxes.items_remaining > 0) as box_count'
                ))
                ->when($this->search, fn($q) =>
                    $q->where(fn($q2) =>
                        $q2->where('name',    'ilike', "%{$this->search}%")
                           ->orWhere('code',  'ilike', "%{$this->search}%")
                           ->orWhere('city',  'ilike', "%{$this->search}%")
                    )
                )
                ->when($this->statusFilter === 'active',   fn($q) => $q->where('is_active', true))
                ->when($this->statusFilter === 'inactive', fn($q) => $q->where('is_active', false))
                ->orderByRaw('is_active DESC, name ASC')
                ->paginate(20);
        } else {
            $rows = Shop::query()
                ->with('defaultWarehouse')
                ->withCount([
                    'users as manager_count' => fn($q) => $q->where('is_active', true),
                ])
                ->addSelect(DB::raw(
                    '(SELECT COUNT(*) FROM boxes
                      WHERE boxes.location_type = \'shop\'
                      AND boxes.location_id = shops.id
                      AND boxes.status IN (\'full\',\'partial\')
                      AND boxes.items_remaining > 0) as box_count'
                ))
                ->addSelect(DB::raw(
                    '(SELECT COUNT(*) FROM sales
                      WHERE sales.shop_id = shops.id
                      AND sales.voided_at IS NULL
                      AND DATE(sales.sale_date) = CURRENT_DATE) as sales_today'
                ))
                ->when($this->search, fn($q) =>
                    $q->where(fn($q2) =>
                        $q2->where('name',   'ilike', "%{$this->search}%")
                           ->orWhere('code', 'ilike', "%{$this->search}%")
                           ->orWhere('city', 'ilike', "%{$this->search}%")
                    )
                )
                ->when($this->statusFilter === 'active',   fn($q) => $q->where('is_active', true))
                ->when($this->statusFilter === 'inactive', fn($q) => $q->where('is_active', false))
                ->orderByRaw('is_active DESC, name ASC')
                ->paginate(20);
        }

        return view('livewire.owner.locations.location-list', compact('rows', 'stats'));
    }
}
