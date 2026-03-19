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
        if (!auth()->user()->isOwner() && !auth()->user()->isAdmin()) abort(403);
    }

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingRoleFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function updatedFormRole(): void
    {
        $this->form_location_id = null;

        $this->form_location_type = match($this->form_role) {
            'warehouse_manager' => 'warehouse',
            'shop_manager'      => 'shop',
            default             => '',   // owner / admin → no location
        };
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
            'form_role'  => 'required|in:admin,owner,warehouse_manager,shop_manager',
            'form_is_active' => 'boolean',
        ];

        if (!$this->isEditing) {
            $rules['form_password'] = 'required|string|min:8';
        } else {
            $rules['form_password'] = 'nullable|string|min:8';
        }

        // Location required for non-owners and non-admins
        if ($this->form_role !== 'owner' && $this->form_role !== 'admin') {
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

        $currentUser = auth()->user();

        // Only Owner can create or assign the Admin role
        if ($this->form_role === 'admin' && !$currentUser->isOwner()) {
            $this->addError('form_role', 'Only an Owner can create Admin accounts.');
            return;
        }

        // Only Owner can create or assign the Owner role
        if ($this->form_role === 'owner' && !$currentUser->isOwner()) {
            $this->addError('form_role', 'Only an Owner can create Owner accounts.');
            return;
        }

        // Admin cannot edit Owner or Admin accounts
        if ($this->isEditing && $currentUser->isAdmin()) {
            $target = User::find($this->editingId);
            if ($target && ($target->isOwner() || $target->isAdmin())) {
                $this->dispatch('notification', [
                    'type'    => 'error',
                    'message' => 'You do not have permission to edit this account.',
                ]);
                $this->closeDrawer();
                return;
            }
        }

        $isAdminOrOwnerRole = in_array($this->form_role, ['admin', 'owner']);

        $data = [
            'name'          => trim($this->form_name),
            'email'         => strtolower(trim($this->form_email)),
            'phone'         => $this->form_phone ?: null,
            'role'          => UserRole::from($this->form_role),
            'location_type' => (!$isAdminOrOwnerRole && $this->form_location_type !== '')
                               ? LocationType::from($this->form_location_type)
                               : null,
            'location_id'   => !$isAdminOrOwnerRole
                               ? $this->form_location_id
                               : null,
            'is_active'     => $this->form_is_active,
        ];

        // New users must change password on first login
        // Editing an existing user does NOT reset this flag
        $data['must_change_password'] = $this->isEditing ? $this->getExistingFlag() : true;

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
        $user        = User::findOrFail($userId);
        $currentUser = auth()->user();

        if ($user->id === auth()->id()) {
            $this->dispatch('notification', [
                'type'    => 'error',
                'message' => 'You cannot deactivate your own account.',
            ]);
            return;
        }

        // Admin cannot deactivate owners or other admins
        if ($currentUser->isAdmin() && ($user->isOwner() || $user->isAdmin())) {
            $this->dispatch('notification', [
                'type'    => 'error',
                'message' => 'Admins cannot deactivate Owner or Admin accounts.',
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

    private function getExistingFlag(): bool
    {
        if (!$this->editingId) return false;
        return (bool) User::find($this->editingId)?->must_change_password;
    }

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
