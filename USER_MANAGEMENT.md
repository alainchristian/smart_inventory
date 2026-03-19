# User Management Module
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read USER_MANAGEMENT.md and follow every step in order."

---

## Read these files first — do not write any code yet

```bash
cat app/Models/User.php
cat app/Enums/UserRole.php
cat app/Enums/LocationType.php
cat app/Models/Shop.php
cat app/Models/Warehouse.php
cat resources/views/owner/users/index.blade.php 2>/dev/null || echo "FILE MISSING"
cat resources/css/app.css | grep -A3 "\.bkpi\b"
grep -n "owner.users" routes/web.php
```

Note the exact CSS variable names in use:
`--bg --surface --surface2 --surface3 --border --border-hi`
`--text --text-sub --text-dim --accent --accent-dim --accent-glow`
`--green --green-dim --red --red-dim --amber --amber-dim --violet --violet-dim`
`--font --mono --r --rx --rsm --tr`

---

## Architecture

Single Livewire component with an inline slide-in drawer panel.
No separate create/edit pages — everything happens on one screen.

```
owner.users.index  →  resources/views/owner/users/index.blade.php
                          └── <livewire:owner.users.user-list />
                                 app/Livewire/Owner/Users/UserList.php
                                 resources/views/livewire/owner/users/user-list.blade.php
```

---

## STEP 1 — Create the Livewire component

**File:** `app/Livewire/Owner/Users/UserList.php`

```php
<?php

namespace App\Livewire\Owner\Users;

use App\Enums\LocationType;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Shop;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    // ── Filters ───────────────────────────────────────────────────────────────
    public string $search      = '';
    public string $roleFilter  = 'all';   // all | owner | warehouse_manager | shop_manager
    public string $statusFilter = 'all';  // all | active | inactive

    // ── Drawer state ──────────────────────────────────────────────────────────
    public bool   $showDrawer = false;
    public bool   $isEditing  = false;
    public ?int   $editingId  = null;

    // ── Form fields ───────────────────────────────────────────────────────────
    public string $form_name         = '';
    public string $form_email        = '';
    public string $form_phone        = '';
    public string $form_role         = 'shop_manager';
    public string $form_location_type = '';
    public ?int   $form_location_id  = null;
    public string $form_password     = '';
    public bool   $form_is_active    = true;
    public bool   $form_show_password = false;

    // ── Confirmation ──────────────────────────────────────────────────────────
    public ?int  $confirmToggleId   = null;
    public bool  $confirmToggleActive = false; // current state (about to be reversed)
    public string $confirmToggleName = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'roleFilter'   => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
    ];

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        if (!auth()->user()->isOwner()) abort(403);
    }

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingRoleFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function updatedFormRole(): void
    {
        // Reset location when role changes
        $this->form_location_type = '';
        $this->form_location_id   = null;

        if ($this->form_role === 'warehouse_manager') {
            $this->form_location_type = 'warehouse';
        } elseif ($this->form_role === 'shop_manager') {
            $this->form_location_type = 'shop';
        }
    }

    // ── Drawer: Open for create ───────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing  = false;
        $this->editingId  = null;
        $this->showDrawer = true;
    }

    // ── Drawer: Open for edit ─────────────────────────────────────────────────

    public function openEdit(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->resetForm();
        $this->isEditing  = true;
        $this->editingId  = $userId;

        $this->form_name          = $user->name;
        $this->form_email         = $user->email;
        $this->form_phone         = $user->phone ?? '';
        $this->form_role          = $user->role->value;
        $this->form_location_type = $user->location_type?->value ?? '';
        $this->form_location_id   = $user->location_id;
        $this->form_is_active     = $user->is_active;
        $this->form_password      = '';

        $this->showDrawer = true;
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->resetForm();
    }

    // ── Save (create or update) ───────────────────────────────────────────────

    public function save(): void
    {
        $rules = [
            'form_name'  => 'required|string|min:2|max:100',
            'form_email' => 'required|email|max:150|unique:users,email'
                            . ($this->isEditing ? ",{$this->editingId}" : ''),
            'form_phone' => 'nullable|string|max:20',
            'form_role'  => 'required|in:owner,warehouse_manager,shop_manager',
            'form_is_active' => 'boolean',
        ];

        if (!$this->isEditing) {
            $rules['form_password'] = 'required|string|min:8';
        } else {
            $rules['form_password'] = 'nullable|string|min:8';
        }

        // Location required for non-owners
        if ($this->form_role !== 'owner') {
            $rules['form_location_id'] = 'required|integer';
        }

        $this->validate($rules, [
            'form_name.required'      => 'Full name is required.',
            'form_email.required'     => 'Email address is required.',
            'form_email.unique'       => 'This email is already in use.',
            'form_password.required'  => 'Password is required for new users.',
            'form_password.min'       => 'Password must be at least 8 characters.',
            'form_location_id.required' => 'Please assign a location for this role.',
        ]);

        $data = [
            'name'          => trim($this->form_name),
            'email'         => strtolower(trim($this->form_email)),
            'phone'         => $this->form_phone ?: null,
            'role'          => UserRole::from($this->form_role),
            'location_type' => $this->form_role !== 'owner'
                               ? LocationType::from($this->form_location_type)
                               : null,
            'location_id'   => $this->form_role !== 'owner'
                               ? $this->form_location_id
                               : null,
            'is_active'     => $this->form_is_active,
        ];

        if ($this->form_password) {
            $data['password'] = Hash::make($this->form_password);
        }

        if ($this->isEditing) {
            $user = User::findOrFail($this->editingId);

            // Prevent owner from deactivating themselves
            if ($user->id === auth()->id() && !$this->form_is_active) {
                $this->addError('form_is_active', 'You cannot deactivate your own account.');
                return;
            }

            $old = $user->only(['name','email','role','location_type','location_id','is_active']);
            $user->update($data);

            ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()->name,
                'action'            => 'updated',
                'entity_type'       => 'User',
                'entity_id'         => $user->id,
                'entity_identifier' => $user->email,
                'old_values'        => $old,
                'new_values'        => $user->fresh()->only(['name','email','role','location_type','location_id','is_active']),
                'ip_address'        => request()->ip(),
            ]);

            $this->dispatch('notification', ['type' => 'success', 'message' => 'User updated successfully.']);
        } else {
            $user = User::create($data);

            ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()->name,
                'action'            => 'created',
                'entity_type'       => 'User',
                'entity_id'         => $user->id,
                'entity_identifier' => $user->email,
                'old_values'        => null,
                'new_values'        => $user->only(['name','email','role','location_type','location_id']),
                'ip_address'        => request()->ip(),
            ]);

            $this->dispatch('notification', ['type' => 'success', 'message' => 'User created successfully.']);
        }

        $this->closeDrawer();
    }

    // ── Toggle active / inactive ──────────────────────────────────────────────

    public function confirmToggle(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            $this->dispatch('notification', [
                'type'    => 'error',
                'message' => 'You cannot deactivate your own account.',
            ]);
            return;
        }

        $this->confirmToggleId     = $userId;
        $this->confirmToggleActive = $user->is_active;
        $this->confirmToggleName   = $user->name;
    }

    public function executeToggle(): void
    {
        if (!$this->confirmToggleId) return;

        $user   = User::findOrFail($this->confirmToggleId);
        $newState = !$user->is_active;

        $user->update(['is_active' => $newState]);

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()->name,
            'action'            => $newState ? 'activated' : 'deactivated',
            'entity_type'       => 'User',
            'entity_id'         => $user->id,
            'entity_identifier' => $user->email,
            'old_values'        => ['is_active' => !$newState],
            'new_values'        => ['is_active' => $newState],
            'ip_address'        => request()->ip(),
        ]);

        $label = $newState ? 'activated' : 'deactivated';
        $this->dispatch('notification', [
            'type'    => $newState ? 'success' : 'warning',
            'message' => "{$user->name} has been {$label}.",
        ]);

        $this->cancelToggle();
    }

    public function cancelToggle(): void
    {
        $this->confirmToggleId   = null;
        $this->confirmToggleName = '';
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->form_name          = '';
        $this->form_email         = '';
        $this->form_phone         = '';
        $this->form_role          = 'shop_manager';
        $this->form_location_type = 'shop';
        $this->form_location_id   = null;
        $this->form_password      = '';
        $this->form_is_active     = true;
        $this->form_show_password = false;
        $this->resetValidation();
    }

    // ── Computed properties ───────────────────────────────────────────────────

    public function getShopsProperty()
    {
        return Shop::where('is_active', true)->orderBy('name')->get(['id','name']);
    }

    public function getWarehousesProperty()
    {
        return Warehouse::where('is_active', true)->orderBy('name')->get(['id','name']);
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $users = User::query()
            ->with('location')
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('name',  'ilike', "%{$this->search}%")
                       ->orWhere('email', 'ilike', "%{$this->search}%")
                       ->orWhere('phone', 'like',  "%{$this->search}%");
                });
            })
            ->when($this->roleFilter !== 'all', fn ($q) =>
                $q->where('role', $this->roleFilter)
            )
            ->when($this->statusFilter === 'active',   fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive',  fn ($q) => $q->where('is_active', false))
            ->orderByRaw("CASE WHEN is_active THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'total'     => User::count(),
            'active'    => User::where('is_active', true)->count(),
            'owners'    => User::where('role', 'owner')->count(),
            'warehouse' => User::where('role', 'warehouse_manager')->count(),
            'shop'      => User::where('role', 'shop_manager')->count(),
        ];

        return view('livewire.owner.users.user-list', compact('users', 'stats'));
    }
}
```

---

## STEP 2 — Create the blade view

**File:** `resources/views/livewire/owner/users/user-list.blade.php`

```blade
<div style="font-family:var(--font)" x-data="{ drawerOpen: @entangle('showDrawer') }">
<style>
/* ── KPI bar ─────────────────────────────────────── */
.um-kpis { display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:22px }
.um-kpi  { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:14px 18px }
.um-kpi-label { font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim);margin-bottom:5px }
.um-kpi-val   { font-size:22px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;color:var(--text);line-height:1 }
.um-kpi-sub   { font-size:11px;color:var(--text-dim);margin-top:3px }

/* ── Controls ────────────────────────────────────── */
.um-bar { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:18px }
.um-search-wrap { flex:1;min-width:200px;position:relative }
.um-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.um-search { width:100%;padding:9px 11px 9px 33px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.um-search:focus { border-color:var(--accent) }
.um-select { padding:8px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;cursor:pointer;font-family:var(--font) }
.um-btn-new { display:flex;align-items:center;gap:7px;padding:9px 18px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);transition:opacity var(--tr);white-space:nowrap }
.um-btn-new:hover { opacity:.88 }

/* ── Table ───────────────────────────────────────── */
.um-table-wrap { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden }
.um-table { width:100%;border-collapse:collapse;font-size:13px }
.um-table thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.um-table thead th { padding:10px 16px;text-align:left;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.um-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.um-table tbody tr:last-child { border-bottom:none }
.um-table tbody tr:hover { background:var(--surface2) }
.um-table tbody tr.inactive { opacity:.55 }
.um-table td { padding:12px 16px;vertical-align:middle }

/* Avatar */
.um-avatar { width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0 }

/* Role badge */
.um-role { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:700;white-space:nowrap }

/* Status dot */
.um-dot { width:7px;height:7px;border-radius:50%;flex-shrink:0 }

/* Action buttons */
.um-action { padding:5px 10px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:11px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap }
.um-action:hover { border-color:var(--accent);color:var(--accent) }
.um-action.danger:hover { border-color:var(--amber);color:var(--amber) }
.um-action.activate:hover { border-color:var(--green);color:var(--green) }

/* Confirm inline row */
.um-confirm-row { background:rgba(217,119,6,.05) !important }
.um-confirm-box { display:flex;align-items:center;gap:10px;flex-wrap:wrap }
.um-confirm-text { font-size:12px;color:var(--text-sub) }
.um-confirm-yes { padding:5px 14px;background:var(--amber);color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font) }
.um-confirm-no  { padding:5px 12px;background:transparent;border:1.5px solid var(--border);color:var(--text-sub);border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;font-family:var(--font) }

/* Empty state */
.um-empty { padding:60px 20px;text-align:center }
.um-empty-icon  { font-size:36px;margin-bottom:10px }
.um-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.um-empty-sub   { font-size:13px;color:var(--text-dim) }

/* ── Drawer overlay ──────────────────────────────── */
.um-overlay {
    position:fixed;inset:0;z-index:400;
    background:rgba(26,31,54,.45);
    backdrop-filter:blur(2px);
    transition:opacity .2s;
}
.um-drawer {
    position:fixed;top:0;right:0;bottom:0;z-index:401;
    width:480px;max-width:100vw;
    background:var(--surface);
    border-left:1px solid var(--border);
    box-shadow:-8px 0 40px rgba(26,31,54,.14);
    display:flex;flex-direction:column;
    transform:translateX(100%);
    transition:transform .22s cubic-bezier(.4,0,.2,1);
}
.um-drawer.open { transform:translateX(0) }

.um-drawer-head {
    display:flex;align-items:center;justify-content:space-between;
    padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0;
}
.um-drawer-title { font-size:16px;font-weight:800;color:var(--text) }
.um-drawer-close {
    width:32px;height:32px;border-radius:8px;border:none;
    background:var(--surface2);color:var(--text-sub);cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    transition:background var(--tr);
}
.um-drawer-close:hover { background:var(--surface3);color:var(--text) }

.um-drawer-body { flex:1;overflow-y:auto;padding:22px }
.um-drawer-foot { padding:16px 22px;border-top:1px solid var(--border);display:flex;gap:10px;flex-shrink:0 }

/* Form elements */
.um-field { margin-bottom:18px }
.um-field-label { display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:6px }
.um-field-label span { color:var(--red) }
.um-field-input {
    width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;
    font-size:13px;background:var(--surface);color:var(--text);outline:none;
    box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr);
}
.um-field-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.um-field-error { font-size:11px;color:var(--red);margin-top:4px }
.um-field-hint  { font-size:11px;color:var(--text-dim);margin-top:4px;line-height:1.5 }

/* Role cards */
.um-role-cards { display:flex;flex-direction:column;gap:8px }
.um-role-card {
    display:flex;align-items:flex-start;gap:12px;padding:12px 14px;
    border:2px solid var(--border);border-radius:10px;cursor:pointer;
    transition:all var(--tr);
}
.um-role-card:hover  { border-color:var(--border-hi);background:var(--surface2) }
.um-role-card.active { border-color:var(--accent);background:var(--accent-dim) }
.um-role-radio { width:16px;height:16px;border-radius:50%;border:2px solid var(--border);flex-shrink:0;margin-top:2px;display:flex;align-items:center;justify-content:center;transition:all var(--tr) }
.um-role-card.active .um-role-radio { border-color:var(--accent);background:var(--accent) }
.um-role-radio-dot { width:6px;height:6px;border-radius:50%;background:#fff }
.um-role-name { font-size:13px;font-weight:700;color:var(--text);margin-bottom:2px }
.um-role-desc { font-size:11px;color:var(--text-dim);line-height:1.5 }

/* Save button */
.um-save-btn {
    flex:1;padding:11px;background:var(--accent);color:#fff;border:none;
    border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;
    font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);
    transition:opacity var(--tr);
}
.um-save-btn:hover    { opacity:.88 }
.um-save-btn:disabled { opacity:.5;cursor:not-allowed }
.um-cancel-btn {
    padding:11px 20px;background:transparent;border:1.5px solid var(--border);
    color:var(--text-sub);border-radius:10px;font-size:13px;font-weight:600;
    cursor:pointer;font-family:var(--font);transition:all var(--tr);
}
.um-cancel-btn:hover { border-color:var(--border-hi);color:var(--text) }

/* Toggle switch */
.um-toggle { position:relative;width:42px;height:23px;flex-shrink:0;cursor:pointer }
.um-toggle input { position:absolute;opacity:0;width:0;height:0 }
.um-toggle-track { position:absolute;inset:0;border-radius:23px;background:var(--surface3);border:1.5px solid var(--border);transition:background var(--tr),border-color var(--tr) }
.um-toggle input:checked ~ .um-toggle-track { background:var(--green);border-color:var(--green) }
.um-toggle-knob { position:absolute;top:2.5px;left:2.5px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.18);transition:transform var(--tr);pointer-events:none }
.um-toggle input:checked ~ .um-toggle-track .um-toggle-knob { transform:translateX(19px) }

/* Password field */
.um-pw-wrap { position:relative }
.um-pw-toggle { position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-dim);padding:4px }

/* Mobile */
@media(max-width:640px) {
    .um-kpis { grid-template-columns:repeat(2,1fr) }
    .um-hide-mob { display:none !important }
    .um-bar { flex-direction:column;align-items:stretch }
    .um-table td,.um-table th { padding:9px 10px }
    .um-drawer { width:100vw }
}
</style>

{{-- ── Page header ─────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;
            gap:12px;margin-bottom:22px;flex-wrap:wrap">
    <div>
        <div style="font-size:22px;font-weight:800;color:var(--text);letter-spacing:-.4px">
            Team Members
        </div>
        <div style="font-size:13px;color:var(--text-dim);margin-top:3px">
            Manage who has access to Smart Inventory and what they can do
        </div>
    </div>
    <button wire:click="openCreate" class="um-btn-new">
        <svg width="14" height="14" fill="none" stroke="currentColor"
             stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New User
    </button>
</div>

{{-- ── KPI bar ──────────────────────────────────────────────────────── --}}
<div class="um-kpis">
    <div class="um-kpi">
        <div class="um-kpi-label">Total Users</div>
        <div class="um-kpi-val" style="color:var(--accent)">{{ $stats['total'] }}</div>
        <div class="um-kpi-sub">all roles</div>
    </div>
    <div class="um-kpi">
        <div class="um-kpi-label">Active</div>
        <div class="um-kpi-val" style="color:var(--green)">{{ $stats['active'] }}</div>
        <div class="um-kpi-sub">can log in</div>
    </div>
    <div class="um-kpi">
        <div class="um-kpi-label">Owners</div>
        <div class="um-kpi-val">{{ $stats['owners'] }}</div>
        <div class="um-kpi-sub">full access</div>
    </div>
    <div class="um-kpi">
        <div class="um-kpi-label">Warehouse</div>
        <div class="um-kpi-val">{{ $stats['warehouse'] }}</div>
        <div class="um-kpi-sub">managers</div>
    </div>
    <div class="um-kpi">
        <div class="um-kpi-label">Shop</div>
        <div class="um-kpi-val">{{ $stats['shop'] }}</div>
        <div class="um-kpi-sub">managers</div>
    </div>
</div>

{{-- ── Controls ─────────────────────────────────────────────────────── --}}
<div class="um-bar">
    <div class="um-search-wrap">
        <svg class="um-search-icon" fill="none" stroke="currentColor"
             stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input wire:model.live.debounce.300ms="search"
               class="um-search" type="text"
               placeholder="Search by name, email, or phone…">
    </div>
    <select wire:model.live="roleFilter" class="um-select">
        <option value="all">All Roles</option>
        <option value="owner">Owner</option>
        <option value="warehouse_manager">Warehouse Manager</option>
        <option value="shop_manager">Shop Manager</option>
    </select>
    <select wire:model.live="statusFilter" class="um-select">
        <option value="all">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>
</div>

{{-- ── Table ────────────────────────────────────────────────────────── --}}
<div class="um-table-wrap">
    <div style="overflow-x:auto">
    <table class="um-table">
        <thead>
            <tr>
                <th style="width:42px"></th>
                <th>Name</th>
                <th class="um-hide-mob">Email</th>
                <th>Role</th>
                <th class="um-hide-mob">Location</th>
                <th class="um-hide-mob">Last Login</th>
                <th style="width:8px">Status</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            @php
                $roleColor = match($user->role->value) {
                    'owner'             => ['bg'=>'var(--accent-dim)', 'color'=>'var(--accent)'],
                    'warehouse_manager' => ['bg'=>'var(--green-dim)',  'color'=>'var(--green)'],
                    'shop_manager'      => ['bg'=>'var(--violet-dim)', 'color'=>'var(--violet)'],
                    default             => ['bg'=>'var(--surface2)',   'color'=>'var(--text-sub)'],
                };
                $avatarBg = match($user->role->value) {
                    'owner'             => 'var(--accent)',
                    'warehouse_manager' => 'var(--green)',
                    'shop_manager'      => 'var(--violet)',
                    default             => 'var(--text-dim)',
                };
                $isMe = $user->id === auth()->id();
            @endphp

            {{-- Normal row --}}
            @if($confirmToggleId !== $user->id)
            <tr class="{{ !$user->is_active ? 'inactive' : '' }}">
                <td style="padding-left:16px">
                    <div class="um-avatar" style="background:{{ $avatarBg }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </td>
                <td>
                    <div style="font-weight:600;color:var(--text);font-size:13px">
                        {{ $user->name }}
                        @if($isMe)
                            <span style="font-size:10px;font-weight:700;padding:1px 6px;
                                         border-radius:20px;background:var(--accent-dim);
                                         color:var(--accent);margin-left:4px">You</span>
                        @endif
                    </div>
                    <div class="um-hide-mob" style="font-size:11px;color:var(--text-dim);
                                font-family:var(--mono);margin-top:1px">
                        {{ $user->phone ?? '—' }}
                    </div>
                </td>
                <td class="um-hide-mob" style="color:var(--text-sub);font-size:12px;
                            font-family:var(--mono)">
                    {{ $user->email }}
                </td>
                <td>
                    <span class="um-role"
                          style="background:{{ $roleColor['bg'] }};color:{{ $roleColor['color'] }}">
                        {{ $user->role->label() }}
                    </span>
                </td>
                <td class="um-hide-mob" style="font-size:12px;color:var(--text-sub)">
                    @if($user->location)
                        <div style="font-weight:600">{{ $user->location->name }}</div>
                        <div style="font-size:10px;color:var(--text-dim);margin-top:1px;text-transform:capitalize">
                            {{ $user->location_type?->value ?? '' }}
                        </div>
                    @elseif($user->isOwner())
                        <span style="color:var(--text-dim);font-size:11px">All locations</span>
                    @else
                        <span style="color:var(--amber);font-size:11px">⚠ Unassigned</span>
                    @endif
                </td>
                <td class="um-hide-mob" style="font-size:11px;color:var(--text-dim);
                            font-family:var(--mono)">
                    {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                </td>
                <td>
                    <div class="um-dot"
                         style="background:{{ $user->is_active ? 'var(--green)' : 'var(--text-dim)' }}">
                    </div>
                </td>
                <td style="text-align:right">
                    <div style="display:flex;gap:6px;justify-content:flex-end">
                        <button wire:click="openEdit({{ $user->id }})"
                                class="um-action">
                            Edit
                        </button>
                        @if(!$isMe)
                        <button wire:click="confirmToggle({{ $user->id }})"
                                class="um-action {{ $user->is_active ? 'danger' : 'activate' }}">
                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- Inline confirmation row --}}
            @else
            <tr class="um-confirm-row">
                <td colspan="8" style="padding:14px 16px">
                    <div class="um-confirm-box">
                        <span style="font-size:18px">
                            {{ $confirmToggleActive ? '⚠️' : '✅' }}
                        </span>
                        <span class="um-confirm-text">
                            @if($confirmToggleActive)
                                Deactivate <strong>{{ $confirmToggleName }}</strong>?
                                They will immediately lose access to the system.
                            @else
                                Reactivate <strong>{{ $confirmToggleName }}</strong>?
                                They will be able to log in again.
                            @endif
                        </span>
                        <button wire:click="executeToggle"
                                class="um-confirm-yes">
                            {{ $confirmToggleActive ? 'Yes, deactivate' : 'Yes, activate' }}
                        </button>
                        <button wire:click="cancelToggle"
                                class="um-confirm-no">Cancel</button>
                    </div>
                </td>
            </tr>
            @endif

            @empty
            <tr>
                <td colspan="8">
                    <div class="um-empty">
                        <div class="um-empty-icon">👥</div>
                        <div class="um-empty-title">
                            @if($search)
                                No users match "{{ $search }}"
                            @else
                                No users found
                            @endif
                        </div>
                        <div class="um-empty-sub">
                            @if(!$search)
                                Click "New User" to add your first team member.
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div style="padding:12px 16px;border-top:1px solid var(--border)">
        {{ $users->links() }}
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════
     DRAWER OVERLAY + PANEL
══════════════════════════════════════════════ --}}
@if($showDrawer)
<div class="um-overlay" wire:click="closeDrawer"></div>
@endif

<div class="um-drawer {{ $showDrawer ? 'open' : '' }}">
    {{-- Header --}}
    <div class="um-drawer-head">
        <div class="um-drawer-title">
            {{ $isEditing ? 'Edit User' : 'New User' }}
        </div>
        <button wire:click="closeDrawer" class="um-drawer-close">
            <svg width="16" height="16" fill="none" stroke="currentColor"
                 stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    {{-- Body --}}
    <div class="um-drawer-body">

        {{-- Name --}}
        <div class="um-field">
            <label class="um-field-label">Full Name <span>*</span></label>
            <input wire:model="form_name" type="text"
                   class="um-field-input" placeholder="e.g. Alice Uwimana"
                   autocomplete="off">
            @error('form_name')
                <div class="um-field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="um-field">
            <label class="um-field-label">Email Address <span>*</span></label>
            <input wire:model.blur="form_email" type="email"
                   class="um-field-input" placeholder="alice@business.com"
                   autocomplete="off">
            @error('form_email')
                <div class="um-field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Phone --}}
        <div class="um-field">
            <label class="um-field-label">Phone</label>
            <input wire:model="form_phone" type="text"
                   class="um-field-input" placeholder="+250 788 000 000">
        </div>

        {{-- Password --}}
        <div class="um-field">
            <label class="um-field-label">
                Password
                @if(!$isEditing) <span>*</span> @endif
            </label>
            <div class="um-pw-wrap">
                <input wire:model="form_password"
                       type="{{ $form_show_password ? 'text' : 'password' }}"
                       class="um-field-input"
                       style="padding-right:38px"
                       placeholder="{{ $isEditing ? 'Leave blank to keep current' : 'Min. 8 characters' }}"
                       autocomplete="new-password">
                <button type="button"
                        wire:click="$toggle('form_show_password')"
                        class="um-pw-toggle">
                    @if($form_show_password)
                        <svg width="15" height="15" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    @else
                        <svg width="15" height="15" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    @endif
                </button>
            </div>
            @error('form_password')
                <div class="um-field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Divider --}}
        <div style="border-top:1px solid var(--border);margin:20px 0"></div>

        {{-- Role --}}
        <div class="um-field">
            <label class="um-field-label">Role <span>*</span></label>
            <div class="um-role-cards">

                <div wire:click="$set('form_role','owner')"
                     class="um-role-card {{ $form_role === 'owner' ? 'active' : '' }}">
                    <div class="um-role-radio">
                        @if($form_role === 'owner')
                            <div class="um-role-radio-dot"></div>
                        @endif
                    </div>
                    <div>
                        <div class="um-role-name" style="color:var(--accent)">
                            👑 Owner
                        </div>
                        <div class="um-role-desc">
                            Full system access. Can view purchase prices, manage
                            all users, configure settings, and access all reports.
                            No location binding required.
                        </div>
                    </div>
                </div>

                <div wire:click="$set('form_role','warehouse_manager')"
                     class="um-role-card {{ $form_role === 'warehouse_manager' ? 'active' : '' }}">
                    <div class="um-role-radio">
                        @if($form_role === 'warehouse_manager')
                            <div class="um-role-radio-dot"></div>
                        @endif
                    </div>
                    <div>
                        <div class="um-role-name" style="color:var(--green)">
                            🏭 Warehouse Manager
                        </div>
                        <div class="um-role-desc">
                            Manages warehouse inventory. Can receive boxes, approve
                            and pack transfers. Cannot see purchase prices or reports.
                        </div>
                    </div>
                </div>

                <div wire:click="$set('form_role','shop_manager')"
                     class="um-role-card {{ $form_role === 'shop_manager' ? 'active' : '' }}">
                    <div class="um-role-radio">
                        @if($form_role === 'shop_manager')
                            <div class="um-role-radio-dot"></div>
                        @endif
                    </div>
                    <div>
                        <div class="um-role-name" style="color:var(--violet)">
                            🏪 Shop Manager
                        </div>
                        <div class="um-role-desc">
                            Operates the point of sale. Can request transfers,
                            process sales and returns. Access limited to assigned shop.
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Location (hidden for owner) --}}
        @if($form_role !== 'owner')
        <div class="um-field">
            <label class="um-field-label">
                Assigned {{ $form_role === 'warehouse_manager' ? 'Warehouse' : 'Shop' }}
                <span>*</span>
            </label>
            @if($form_role === 'warehouse_manager')
                <select wire:model="form_location_id" class="um-field-input">
                    <option value="">— Select a warehouse —</option>
                    @foreach($this->warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            @else
                <select wire:model="form_location_id" class="um-field-input">
                    <option value="">— Select a shop —</option>
                    @foreach($this->shops as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
            @endif
            @error('form_location_id')
                <div class="um-field-error">{{ $message }}</div>
            @enderror
            <div class="um-field-hint">
                This user will only see data for the assigned location.
            </div>
        </div>
        @endif

        {{-- Active toggle --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:14px;background:var(--surface2);border-radius:10px;
                    border:1px solid var(--border)">
            <div>
                <div style="font-size:13px;font-weight:600;color:var(--text)">
                    Account Active
                </div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                    Inactive users cannot log in to the system
                </div>
            </div>
            <label class="um-toggle">
                <input type="checkbox" wire:model="form_is_active">
                <div class="um-toggle-track">
                    <div class="um-toggle-knob"></div>
                </div>
            </label>
        </div>
        @error('form_is_active')
            <div class="um-field-error" style="margin-top:4px">{{ $message }}</div>
        @enderror

    </div>{{-- /drawer-body --}}

    {{-- Footer --}}
    <div class="um-drawer-foot">
        <button wire:click="closeDrawer" class="um-cancel-btn">Cancel</button>
        <button wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="um-save-btn">
            <span wire:loading.remove wire:target="save">
                {{ $isEditing ? 'Save Changes' : 'Create User' }}
            </span>
            <span wire:loading wire:target="save" style="display:none">Saving…</span>
        </button>
    </div>
</div>

</div>
```

---

## STEP 3 — Create the page wrapper blade

**File:** `resources/views/owner/users/index.blade.php`

```blade
<x-app-layout>
    <livewire:owner.users.user-list />
</x-app-layout>
```

---

## STEP 4 — Verify routes

```bash
php artisan route:list | grep "owner.users"
```

Routes should already exist from the original setup:
- `GET /owner/users` → `owner.users.index`
- `GET /owner/users/create` → `owner.users.create`
- `GET /owner/users/{user}/edit` → `owner.users.edit`

The create and edit routes are no longer needed since everything is inline,
but keep them in `routes/web.php` to avoid broken links.

---

## STEP 5 — Clear caches

```bash
php artisan view:clear && php artisan cache:clear
php artisan livewire:discover
```

---

## Do NOT touch

- `app/Models/User.php` — model is correct as-is
- `app/Enums/UserRole.php` — no changes needed
- Any authentication files
- Any migrations

---

## Verification

1. Visit `/owner/users` — table shows all users with KPI bar
2. Click "New User" — drawer slides in from right
3. Select "Warehouse Manager" role — warehouse dropdown appears
4. Select "Shop Manager" role — shop dropdown appears
5. Select "Owner" role — location selector hides entirely
6. Submit with missing required field — inline error appears
7. Submit valid form — drawer closes, user appears in table, success notification
8. Click "Edit" on a user — drawer opens pre-filled with their data
9. Click "Deactivate" — inline amber confirmation row replaces normal row
10. Confirm deactivation — row dims to 55% opacity, status dot goes grey
11. Try to deactivate yourself — blocked with error notification
12. Search by name — table filters live
13. Filter by role — table updates
14. On mobile — table collapses, drawer is full width
