# Locations Management Module
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read LOCATIONS_MANAGEMENT.md and follow every step in order."

---

## Read these files first — do not write any code yet

```bash
cat app/Models/Warehouse.php
cat app/Models/Shop.php
cat app/Models/User.php | grep -A5 "fillable"
grep -n "warehouses\|shops" routes/web.php
cat resources/views/owner/warehouses/index.blade.php 2>/dev/null || echo "MISSING"
cat resources/views/owner/shops/index.blade.php 2>/dev/null || echo "MISSING"
```

---

## Architecture

One Livewire component handles both warehouses and shops via an `$activeTab`
property. Both `/owner/warehouses` and `/owner/shops` load the same component
with a different initial tab. One drawer handles both create and edit for
whichever tab is active.

```
owner.warehouses.index  →  resources/views/owner/warehouses/index.blade.php
                               └── <livewire:owner.locations.location-list tab="warehouses" />

owner.shops.index  →  resources/views/owner/shops/index.blade.php
                          └── <livewire:owner.locations.location-list tab="shops" />

Both load:  app/Livewire/Owner/Locations/LocationList.php
            resources/views/livewire/owner/locations/location-list.blade.php
```

---

## STEP 1 — Update routes to pass tab parameter

**File:** `routes/web.php`

Find the warehouse and shop location routes. Replace them:

```php
Route::prefix('warehouses')->name('warehouses.')->group(function () {
    Route::get('/', function () {
        return view('owner.warehouses.index');
    })->name('index');
});

Route::prefix('shops')->name('shops.')->group(function () {
    Route::get('/', function () {
        return view('owner.shops.index');
    })->name('index');
});
```

Remove the `/create` routes for both — creation is handled inline.

---

## STEP 2 — Create the Livewire component

**File:** `app/Livewire/Owner/Locations/LocationList.php`

```php
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
                    'boxes  as box_count'    => fn($q) => $q->whereIn('status', ['full','partial'])
                                                            ->where('items_remaining', '>', 0),
                    'transfersFrom as active_transfers' => fn($q) =>
                        $q->whereIn('status', ['pending','approved','in_transit','delivered']),
                ])
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
                    'boxes  as box_count'    => fn($q) => $q->whereIn('status', ['full','partial'])
                                                            ->where('items_remaining', '>', 0),
                    'sales  as sales_today'  => fn($q) =>
                        $q->whereNull('voided_at')->whereDate('sale_date', today()),
                ])
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
```

---

## STEP 3 — Create the blade view

**File:** `resources/views/livewire/owner/locations/location-list.blade.php`

```blade
<div style="font-family:var(--font)">
<style>
/* ── KPI strip ───────────────────────────────────── */
.lm-kpis { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:22px }
.lm-kpi  { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:14px 18px }
.lm-kpi-label { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);margin-bottom:5px }
.lm-kpi-val   { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;line-height:1 }
.lm-kpi-sub   { font-size:12px;color:var(--text-dim);margin-top:3px }

/* ── Tab bar ─────────────────────────────────────── */
.lm-tabs { display:flex;gap:4px;background:var(--surface2);border-radius:12px;padding:3px;
           border:1px solid var(--border);margin-bottom:18px;width:fit-content }
.lm-tab  { display:flex;align-items:center;gap:8px;padding:9px 20px;border-radius:10px;
           border:none;cursor:pointer;font-size:14px;font-weight:600;font-family:var(--font);
           background:transparent;color:var(--text-sub);transition:all var(--tr);white-space:nowrap }
.lm-tab.active { background:var(--surface);color:var(--text);
                 box-shadow:0 1px 6px rgba(26,31,54,.12) }
.lm-tab-count  { font-size:11px;font-weight:700;padding:1px 7px;border-radius:20px;
                 background:var(--surface3);color:var(--text-dim);font-family:var(--mono) }
.lm-tab.active .lm-tab-count { background:var(--accent-dim);color:var(--accent) }

/* ── Controls ────────────────────────────────────── */
.lm-bar { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:18px }
.lm-search-wrap { flex:1;min-width:200px;position:relative }
.lm-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);
                  width:15px;height:15px;color:var(--text-dim);pointer-events:none }
.lm-search { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);
             border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);
             outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.lm-search:focus { border-color:var(--accent) }
.lm-select { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;
             font-size:13px;background:var(--surface);color:var(--text);outline:none;cursor:pointer;
             font-family:var(--font) }
.lm-btn-new { display:flex;align-items:center;gap:7px;padding:9px 18px;background:var(--accent);
              color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;
              font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);
              transition:opacity var(--tr);white-space:nowrap }
.lm-btn-new:hover { opacity:.88 }

/* ── Table ───────────────────────────────────────── */
.lm-table-wrap { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden }
.lm-table { width:100%;border-collapse:collapse }
.lm-table thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.lm-table thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:700;
                     letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.lm-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.lm-table tbody tr:last-child { border-bottom:none }
.lm-table tbody tr:hover { background:var(--surface2) }
.lm-table tbody tr.inactive { opacity:.5 }
.lm-table td { padding:13px 16px;font-size:14px;vertical-align:middle }

/* Location icon circle */
.lm-icon { width:38px;height:38px;border-radius:10px;display:flex;align-items:center;
           justify-content:center;font-size:16px;flex-shrink:0 }

/* Stats pills */
.lm-stat { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;
           padding:2px 8px;border-radius:20px;white-space:nowrap }

/* Status dot */
.lm-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0 }

/* Action buttons */
.lm-action { padding:6px 12px;border-radius:8px;border:1.5px solid var(--border);
             background:transparent;font-size:12px;font-weight:600;cursor:pointer;
             font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap }
.lm-action:hover       { border-color:var(--accent);color:var(--accent) }
.lm-action.danger:hover  { border-color:var(--amber);color:var(--amber) }
.lm-action.restore:hover { border-color:var(--green);color:var(--green) }

/* Confirm row */
.lm-confirm-row { background:rgba(217,119,6,.05) !important }
.lm-confirm-wrap { display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap }
.lm-confirm-warning { font-size:12px;color:var(--amber);margin-top:4px;font-weight:500 }
.lm-confirm-yes { padding:6px 16px;background:var(--amber);color:#fff;border:none;
                  border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font) }
.lm-confirm-no  { padding:6px 14px;background:transparent;border:1.5px solid var(--border);
                  color:var(--text-sub);border-radius:8px;font-size:13px;font-weight:600;
                  cursor:pointer;font-family:var(--font) }

/* Empty */
.lm-empty { padding:60px 20px;text-align:center }
.lm-empty-icon  { font-size:40px;margin-bottom:12px }
.lm-empty-title { font-size:16px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.lm-empty-sub   { font-size:13px;color:var(--text-dim) }

/* ── Drawer ──────────────────────────────────────── */
.lm-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);
              backdrop-filter:blur(2px) }
.lm-drawer  { position:fixed;top:0;right:0;bottom:0;z-index:401;
              width:460px;max-width:100vw;background:var(--surface);
              border-left:1px solid var(--border);
              box-shadow:-8px 0 40px rgba(26,31,54,.14);
              display:flex;flex-direction:column;
              transform:translateX(100%);
              transition:transform .22s cubic-bezier(.4,0,.2,1) }
.lm-drawer.open { transform:translateX(0) }
.lm-drawer-head { display:flex;align-items:center;justify-content:space-between;
                  padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0 }
.lm-drawer-title { font-size:17px;font-weight:800;color:var(--text) }
.lm-drawer-close { width:32px;height:32px;border-radius:8px;border:none;
                   background:var(--surface2);color:var(--text-sub);cursor:pointer;
                   display:flex;align-items:center;justify-content:center;
                   transition:background var(--tr) }
.lm-drawer-close:hover { background:var(--surface3) }
.lm-drawer-body { flex:1;overflow-y:auto;padding:22px }
.lm-drawer-foot { padding:16px 22px;border-top:1px solid var(--border);
                  display:flex;gap:10px;flex-shrink:0 }

/* Form */
.lm-field { margin-bottom:18px }
.lm-field-row { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px }
.lm-label { display:block;font-size:13px;font-weight:600;color:var(--text-sub);margin-bottom:6px }
.lm-label span { color:var(--red) }
.lm-input { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;
            font-size:14px;background:var(--surface);color:var(--text);outline:none;
            box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.lm-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.lm-error { font-size:11px;color:var(--red);margin-top:4px }
.lm-hint  { font-size:11px;color:var(--text-dim);margin-top:4px;line-height:1.5 }

/* Active toggle row */
.lm-toggle-row { display:flex;align-items:center;justify-content:space-between;
                 padding:14px;background:var(--surface2);border-radius:10px;
                 border:1px solid var(--border) }
.lm-toggle { position:relative;width:42px;height:23px;flex-shrink:0;cursor:pointer }
.lm-toggle input { position:absolute;opacity:0;width:0;height:0 }
.lm-toggle-track { position:absolute;inset:0;border-radius:23px;background:var(--surface3);
                   border:1.5px solid var(--border);transition:background var(--tr),border-color var(--tr) }
.lm-toggle input:checked ~ .lm-toggle-track { background:var(--green);border-color:var(--green) }
.lm-toggle-knob { position:absolute;top:2.5px;left:2.5px;width:18px;height:18px;border-radius:50%;
                  background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.18);
                  transition:transform var(--tr);pointer-events:none }
.lm-toggle input:checked ~ .lm-toggle-track .lm-toggle-knob { transform:translateX(19px) }

/* Save buttons */
.lm-save-btn { flex:1;padding:12px;background:var(--accent);color:#fff;border:none;
               border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;
               font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);
               transition:opacity var(--tr) }
.lm-save-btn:hover    { opacity:.88 }
.lm-save-btn:disabled { opacity:.5;cursor:not-allowed }
.lm-cancel-btn { padding:12px 20px;background:transparent;border:1.5px solid var(--border);
                 color:var(--text-sub);border-radius:10px;font-size:14px;font-weight:600;
                 cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.lm-cancel-btn:hover { border-color:var(--border-hi);color:var(--text) }

@keyframes lm-spin { to { transform:rotate(360deg) } }

/* Mobile */
@media(max-width:640px) {
    .lm-kpis { grid-template-columns:1fr 1fr;gap:8px }
    .lm-kpi  { padding:10px 12px }
    .lm-kpi-val { font-size:20px }
    .lm-hide-mob { display:none !important }
    .lm-bar  { flex-direction:column;align-items:stretch }
    .lm-select,.lm-btn-new { width:100%;justify-content:center }
    .lm-table td,.lm-table th { padding:10px 10px }
    .lm-drawer { width:100vw }
    .lm-drawer-body { padding:16px }
    .lm-drawer-foot { flex-direction:column }
    .lm-save-btn,.lm-cancel-btn { width:100%;text-align:center }
    .lm-field-row { grid-template-columns:1fr }
}
</style>

{{-- ── Page header ──────────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;
            gap:12px;margin-bottom:22px;flex-wrap:wrap">
    <div>
        <div style="font-size:26px;font-weight:800;color:var(--text);letter-spacing:-.4px">
            Locations
        </div>
        <div style="font-size:14px;color:var(--text-dim);margin-top:3px">
            Manage warehouses and retail shops across your business
        </div>
    </div>
    <button wire:click="openCreate" class="lm-btn-new">
        <svg width="15" height="15" fill="none" stroke="currentColor"
             stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New {{ $activeTab === 'warehouses' ? 'Warehouse' : 'Shop' }}
    </button>
</div>

{{-- ── KPI bar ───────────────────────────────────────────────────────────── --}}
<div class="lm-kpis">
    <div class="lm-kpi">
        <div class="lm-kpi-label">Warehouses</div>
        <div class="lm-kpi-val" style="color:var(--green)">{{ $stats['warehouses_active'] }}</div>
        <div class="lm-kpi-sub">of {{ $stats['warehouses_total'] }} active</div>
    </div>
    <div class="lm-kpi">
        <div class="lm-kpi-label">Shops</div>
        <div class="lm-kpi-val" style="color:var(--accent)">{{ $stats['shops_active'] }}</div>
        <div class="lm-kpi-sub">of {{ $stats['shops_total'] }} active</div>
    </div>
    <div class="lm-kpi">
        <div class="lm-kpi-label">Total Locations</div>
        <div class="lm-kpi-val">{{ $stats['warehouses_total'] + $stats['shops_total'] }}</div>
        <div class="lm-kpi-sub">across the business</div>
    </div>
    <div class="lm-kpi">
        <div class="lm-kpi-label">Active Total</div>
        <div class="lm-kpi-val" style="color:var(--green)">
            {{ $stats['warehouses_active'] + $stats['shops_active'] }}
        </div>
        <div class="lm-kpi-sub">warehouses + shops</div>
    </div>
</div>

{{-- ── Tabs ─────────────────────────────────────────────────────────────── --}}
<div class="lm-tabs">
    <button wire:click="setTab('warehouses')"
            class="lm-tab {{ $activeTab === 'warehouses' ? 'active' : '' }}">
        🏭 Warehouses
        <span class="lm-tab-count">{{ $stats['warehouses_total'] }}</span>
    </button>
    <button wire:click="setTab('shops')"
            class="lm-tab {{ $activeTab === 'shops' ? 'active' : '' }}">
        🏪 Shops
        <span class="lm-tab-count">{{ $stats['shops_total'] }}</span>
    </button>
</div>

{{-- ── Controls ─────────────────────────────────────────────────────────── --}}
<div class="lm-bar">
    <div class="lm-search-wrap">
        <svg class="lm-search-icon" fill="none" stroke="currentColor"
             stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input wire:model.live.debounce.300ms="search"
               class="lm-search" type="text"
               placeholder="Search by name, code, or city…">
    </div>
    <select wire:model.live="statusFilter" class="lm-select">
        <option value="all">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>
</div>

{{-- ══════════════════════════════════════════════
     WAREHOUSES TABLE
══════════════════════════════════════════════ --}}
@if($activeTab === 'warehouses')
<div class="lm-table-wrap">
    <div style="overflow-x:auto">
    <table class="lm-table">
        <thead>
            <tr>
                <th style="width:50px"></th>
                <th>Name & Code</th>
                <th class="lm-hide-mob">City</th>
                <th class="lm-hide-mob">Phone</th>
                <th>Managers</th>
                <th>Boxes</th>
                <th class="lm-hide-mob">Transfers</th>
                <th style="width:8px">Status</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            @if($confirmToggleId === $row->id)
            <tr class="lm-confirm-row">
                <td colspan="9" style="padding:14px 16px">
                    <div class="lm-confirm-wrap">
                        <span style="font-size:20px">⚠️</span>
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600;color:var(--text)">
                                {{ $confirmToggleActive ? 'Deactivate' : 'Reactivate' }}
                                <strong>{{ $confirmToggleName }}</strong>?
                            </div>
                            @if($confirmToggleWarning)
                            <div class="lm-confirm-warning">
                                ⚠ {{ $confirmToggleWarning }}
                            </div>
                            @endif
                        </div>
                        <button wire:click="executeToggle" class="lm-confirm-yes">
                            {{ $confirmToggleActive ? 'Yes, deactivate' : 'Yes, activate' }}
                        </button>
                        <button wire:click="cancelToggle" class="lm-confirm-no">Cancel</button>
                    </div>
                </td>
            </tr>
            @else
            <tr class="{{ !$row->is_active ? 'inactive' : '' }}">
                <td style="padding-left:14px">
                    <div class="lm-icon" style="background:var(--green-dim)">🏭</div>
                </td>
                <td>
                    <div style="font-weight:700;color:var(--text)">{{ $row->name }}</div>
                    <div style="font-size:11px;font-family:var(--mono);color:var(--text-dim);margin-top:1px">
                        {{ $row->code }}
                    </div>
                </td>
                <td class="lm-hide-mob" style="color:var(--text-sub)">
                    {{ $row->city ?? '—' }}
                </td>
                <td class="lm-hide-mob" style="font-size:13px;font-family:var(--mono);color:var(--text-sub)">
                    {{ $row->phone ?? '—' }}
                </td>
                <td>
                    <span class="lm-stat"
                          style="background:var(--accent-dim);color:var(--accent)">
                        {{ $row->manager_count }}
                        {{ Str::plural('manager', $row->manager_count) }}
                    </span>
                </td>
                <td>
                    <span class="lm-stat"
                          style="background:var(--surface2);color:var(--text-sub)">
                        {{ number_format($row->box_count) }} boxes
                    </span>
                </td>
                <td class="lm-hide-mob">
                    @if($row->active_transfers > 0)
                    <span class="lm-stat"
                          style="background:var(--amber-dim);color:var(--amber)">
                        {{ $row->active_transfers }} active
                    </span>
                    @else
                    <span style="font-size:13px;color:var(--text-dim)">—</span>
                    @endif
                </td>
                <td>
                    <div class="lm-dot"
                         style="background:{{ $row->is_active ? 'var(--green)' : 'var(--text-dim)' }}">
                    </div>
                </td>
                <td style="text-align:right">
                    <div style="display:flex;gap:6px;justify-content:flex-end">
                        <button wire:click="openEdit({{ $row->id }})" class="lm-action">
                            Edit
                        </button>
                        <button wire:click="confirmToggle({{ $row->id }})"
                                class="lm-action {{ $row->is_active ? 'danger' : 'restore' }}">
                            {{ $row->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </td>
            </tr>
            @endif
            @empty
            <tr><td colspan="9">
                <div class="lm-empty">
                    <div class="lm-empty-icon">🏭</div>
                    <div class="lm-empty-title">
                        {{ $search ? 'No warehouses match "' . $search . '"' : 'No warehouses yet' }}
                    </div>
                    <div class="lm-empty-sub">
                        {{ !$search ? 'Click "New Warehouse" to add your first storage location.' : '' }}
                    </div>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($rows->hasPages())
    <div style="padding:12px 16px;border-top:1px solid var(--border)">{{ $rows->links() }}</div>
    @endif
</div>

{{-- ══════════════════════════════════════════════
     SHOPS TABLE
══════════════════════════════════════════════ --}}
@else
<div class="lm-table-wrap">
    <div style="overflow-x:auto">
    <table class="lm-table">
        <thead>
            <tr>
                <th style="width:50px"></th>
                <th>Name & Code</th>
                <th class="lm-hide-mob">City</th>
                <th class="lm-hide-mob">Default Warehouse</th>
                <th>Managers</th>
                <th>Boxes</th>
                <th class="lm-hide-mob">Sales Today</th>
                <th style="width:8px">Status</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            @if($confirmToggleId === $row->id)
            <tr class="lm-confirm-row">
                <td colspan="9" style="padding:14px 16px">
                    <div class="lm-confirm-wrap">
                        <span style="font-size:20px">⚠️</span>
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600;color:var(--text)">
                                {{ $confirmToggleActive ? 'Deactivate' : 'Reactivate' }}
                                <strong>{{ $confirmToggleName }}</strong>?
                            </div>
                            @if($confirmToggleWarning)
                            <div class="lm-confirm-warning">
                                ⚠ {{ $confirmToggleWarning }}
                            </div>
                            @endif
                        </div>
                        <button wire:click="executeToggle" class="lm-confirm-yes">
                            {{ $confirmToggleActive ? 'Yes, deactivate' : 'Yes, activate' }}
                        </button>
                        <button wire:click="cancelToggle" class="lm-confirm-no">Cancel</button>
                    </div>
                </td>
            </tr>
            @else
            <tr class="{{ !$row->is_active ? 'inactive' : '' }}">
                <td style="padding-left:14px">
                    <div class="lm-icon" style="background:var(--accent-dim)">🏪</div>
                </td>
                <td>
                    <div style="font-weight:700;color:var(--text)">{{ $row->name }}</div>
                    <div style="font-size:11px;font-family:var(--mono);color:var(--text-dim);margin-top:1px">
                        {{ $row->code }}
                    </div>
                </td>
                <td class="lm-hide-mob" style="color:var(--text-sub)">
                    {{ $row->city ?? '—' }}
                </td>
                <td class="lm-hide-mob" style="font-size:13px;color:var(--text-sub)">
                    {{ $row->defaultWarehouse?->name ?? '—' }}
                </td>
                <td>
                    <span class="lm-stat"
                          style="background:var(--violet-dim);color:var(--violet)">
                        {{ $row->manager_count }}
                        {{ Str::plural('manager', $row->manager_count) }}
                    </span>
                </td>
                <td>
                    <span class="lm-stat"
                          style="background:var(--surface2);color:var(--text-sub)">
                        {{ number_format($row->box_count) }} boxes
                    </span>
                </td>
                <td class="lm-hide-mob">
                    @if($row->sales_today > 0)
                    <span class="lm-stat"
                          style="background:var(--green-dim);color:var(--green)">
                        {{ $row->sales_today }} sales
                    </span>
                    @else
                    <span style="font-size:13px;color:var(--text-dim)">—</span>
                    @endif
                </td>
                <td>
                    <div class="lm-dot"
                         style="background:{{ $row->is_active ? 'var(--green)' : 'var(--text-dim)' }}">
                    </div>
                </td>
                <td style="text-align:right">
                    <div style="display:flex;gap:6px;justify-content:flex-end">
                        <button wire:click="openEdit({{ $row->id }})" class="lm-action">
                            Edit
                        </button>
                        <button wire:click="confirmToggle({{ $row->id }})"
                                class="lm-action {{ $row->is_active ? 'danger' : 'restore' }}">
                            {{ $row->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </td>
            </tr>
            @endif
            @empty
            <tr><td colspan="9">
                <div class="lm-empty">
                    <div class="lm-empty-icon">🏪</div>
                    <div class="lm-empty-title">
                        {{ $search ? 'No shops match "' . $search . '"' : 'No shops yet' }}
                    </div>
                    <div class="lm-empty-sub">
                        {{ !$search ? 'Click "New Shop" to add your first retail location.' : '' }}
                    </div>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($rows->hasPages())
    <div style="padding:12px 16px;border-top:1px solid var(--border)">{{ $rows->links() }}</div>
    @endif
</div>
@endif

{{-- ══════════════════════════════════════════════
     DRAWER
══════════════════════════════════════════════ --}}
@if($showDrawer)
<div class="lm-overlay" wire:click="closeDrawer"></div>
@endif

<div class="lm-drawer {{ $showDrawer ? 'open' : '' }}">
    <div class="lm-drawer-head">
        <div class="lm-drawer-title">
            @if($isEditing)
                Edit {{ $activeTab === 'warehouses' ? 'Warehouse' : 'Shop' }}
            @else
                New {{ $activeTab === 'warehouses' ? 'Warehouse' : 'Shop' }}
            @endif
        </div>
        <button wire:click="closeDrawer" class="lm-drawer-close">
            <svg width="16" height="16" fill="none" stroke="currentColor"
                 stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="lm-drawer-body">

        {{-- Name + Code --}}
        <div class="lm-field-row">
            <div>
                <label class="lm-label">Name <span>*</span></label>
                <input wire:model="form_name" type="text" class="lm-input"
                       placeholder="{{ $activeTab === 'warehouses' ? 'e.g. Main Warehouse' : 'e.g. Remera Shop' }}">
                @error('form_name') <div class="lm-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="lm-label">Code <span>*</span></label>
                <input wire:model="form_code" type="text" class="lm-input"
                       placeholder="{{ $activeTab === 'warehouses' ? 'e.g. WH-001' : 'e.g. SHOP-REM' }}"
                       style="text-transform:uppercase">
                @error('form_code') <div class="lm-error">{{ $message }}</div> @enderror
                <div class="lm-hint">Short unique identifier. Uppercase recommended.</div>
            </div>
        </div>

        {{-- Address --}}
        <div class="lm-field">
            <label class="lm-label">Street Address</label>
            <input wire:model="form_address" type="text" class="lm-input"
                   placeholder="e.g. KG 9 Ave, Remera">
        </div>

        {{-- City + Phone --}}
        <div class="lm-field-row">
            <div>
                <label class="lm-label">City</label>
                <input wire:model="form_city" type="text" class="lm-input"
                       placeholder="e.g. Kigali">
            </div>
            <div>
                <label class="lm-label">Phone</label>
                <input wire:model="form_phone" type="text" class="lm-input"
                       placeholder="+250 788 000 000">
            </div>
        </div>

        {{-- Default Warehouse (shops only) --}}
        @if($activeTab === 'shops')
        <div class="lm-field">
            <label class="lm-label">Default Warehouse <span>*</span></label>
            <select wire:model="form_default_warehouse_id" class="lm-input">
                <option value="">— Select warehouse —</option>
                @foreach($this->activeWarehouses as $wh)
                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
            </select>
            @error('form_default_warehouse_id')
                <div class="lm-error">{{ $message }}</div>
            @enderror
            <div class="lm-hint">
                Transfer requests from this shop will default to this warehouse.
            </div>
        </div>
        @endif

        {{-- Divider --}}
        <div style="border-top:1px solid var(--border);margin:20px 0"></div>

        {{-- Active toggle --}}
        <div class="lm-toggle-row">
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--text)">Location Active</div>
                <div style="font-size:12px;color:var(--text-dim);margin-top:2px">
                    Inactive locations cannot receive stock or process sales
                </div>
            </div>
            <label class="lm-toggle">
                <input type="checkbox" wire:model="form_is_active">
                <div class="lm-toggle-track"><div class="lm-toggle-knob"></div></div>
            </label>
        </div>

    </div>

    <div class="lm-drawer-foot">
        <button wire:click="closeDrawer" class="lm-cancel-btn">Cancel</button>
        <button wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="lm-save-btn">
            <span wire:loading.remove wire:target="save">
                {{ $isEditing ? 'Save Changes' : 'Create ' . ($activeTab === 'warehouses' ? 'Warehouse' : 'Shop') }}
            </span>
            <span wire:loading wire:target="save"
                  style="display:none;align-items:center;gap:8px;justify-content:center">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     style="animation:lm-spin 1s linear infinite">
                    <path d="M21 12a9 9 0 11-6.219-8.56"/>
                </svg>
                Saving…
            </span>
        </button>
    </div>
</div>

</div>
```

---

## STEP 4 — Create page wrapper blades

**File:** `resources/views/owner/warehouses/index.blade.php`

```blade
<x-app-layout>
    <livewire:owner.locations.location-list tab="warehouses" />
</x-app-layout>
```

**File:** `resources/views/owner/shops/index.blade.php`

```blade
<x-app-layout>
    <livewire:owner.locations.location-list tab="shops" />
</x-app-layout>
```

---

## STEP 5 — Ensure directories exist

```bash
mkdir -p resources/views/owner/warehouses
mkdir -p resources/views/owner/shops
mkdir -p resources/views/livewire/owner/locations
mkdir -p app/Livewire/Owner/Locations
```

---

## STEP 6 — Clear and discover

```bash
php artisan livewire:discover
php artisan view:clear && php artisan cache:clear
```

---

## Do NOT touch

- `app/Models/Warehouse.php` and `app/Models/Shop.php` — correct as-is
- Any migrations
- Transfer or sales components

---

## Verification

1. Visit `/owner/warehouses` — warehouses tab active, table shows all warehouses
2. Click "Shops" tab — shops table loads, URL stays the same
3. Visit `/owner/shops` directly — loads with shops tab active
4. Click "New Warehouse" — drawer slides in with Name/Code/Address/City/Phone fields
5. Switch to Shops tab, click "New Shop" — drawer shows same fields plus Default Warehouse dropdown
6. Submit with blank name — inline error appears
7. Submit with duplicate code — inline error appears
8. Create a warehouse — appears in table, drawer closes, success notification
9. Edit a warehouse — drawer opens pre-filled
10. Click "Deactivate" on a warehouse with active transfers — warning text shows in confirmation row
11. KPI bar counts update correctly
12. On mobile: city and transfer columns hide, drawer goes full width
