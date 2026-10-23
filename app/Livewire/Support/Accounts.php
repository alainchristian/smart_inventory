<?php

namespace App\Livewire\Support;

use App\Enums\LocationType;
use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Platform-support console: manage every business account, owners included.
 * Support accounts themselves are never listed or editable here — they are
 * created on the server with `php artisan super:create`.
 *
 * Every change is written to the audit log as "Platform support" so the owner
 * can always see what was done to their accounts.
 */
class Accounts extends Component
{
    use WithPagination;

    private const ASSIGNABLE_ROLES = ['owner', 'admin', 'warehouse_manager', 'shop_manager'];

    #[Url(as: 'q', except: '')]
    public string $search = '';
    #[Url(as: 'role', except: 'all')]
    public string $roleFilter = 'all';
    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    public bool $showDrawer = false;
    public ?int $editingId  = null;

    public string $form_name          = '';
    public string $form_email         = '';
    public string $form_phone         = '';
    public string $form_role          = 'shop_manager';
    public string $form_location_type = 'shop';
    public ?int   $form_location_id   = null;
    public string $form_password      = '';
    public bool   $form_is_active     = true;

    public ?int $confirmToggleId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingRoleFilter(): void   { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function updatedFormRole(): void
    {
        $this->form_location_id   = null;
        $this->form_location_type = match ($this->form_role) {
            'warehouse_manager' => 'warehouse',
            'shop_manager'      => 'shop',
            default             => '',
        };
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showDrawer = true;
    }

    public function openEdit(int $userId): void
    {
        $user = $this->account($userId);

        $this->resetForm();
        $this->editingId          = $user->id;
        $this->form_name          = $user->name;
        $this->form_email         = $user->email;
        $this->form_phone         = $user->phone ?? '';
        $this->form_role          = $user->role->value;
        $this->form_location_type = $user->location_type?->value ?? '';
        $this->form_location_id   = $user->location_id;
        $this->form_is_active     = (bool) $user->is_active;
        $this->showDrawer         = true;
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->resetForm();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $editing = $this->editingId !== null;

        $rules = [
            'form_name'      => 'required|string|min:2|max:100',
            'form_email'     => 'required|email|max:150|unique:users,email' . ($editing ? ",{$this->editingId}" : ''),
            'form_phone'     => 'nullable|string|max:20',
            'form_role'      => 'required|in:' . implode(',', self::ASSIGNABLE_ROLES),
            'form_is_active' => 'boolean',
            'form_password'  => ($editing ? 'nullable' : 'required') . '|string|min:8',
        ];
        $needsLocation = in_array($this->form_role, ['warehouse_manager', 'shop_manager'], true);
        if ($needsLocation) {
            $rules['form_location_id'] = 'required|integer';
        }
        $this->validate($rules, [
            'form_password.required'    => 'A temporary password is required for a new account.',
            'form_location_id.required' => 'Please assign a location for this role.',
            'form_email.unique'         => 'This email is already in use.',
        ]);

        $data = [
            'name'          => trim($this->form_name),
            'email'         => strtolower(trim($this->form_email)),
            'phone'         => $this->form_phone ?: null,
            'role'          => UserRole::from($this->form_role),
            'location_type' => $needsLocation ? LocationType::from($this->form_location_type) : null,
            'location_id'   => $needsLocation ? $this->form_location_id : null,
            'is_active'     => $this->form_is_active,
        ];

        $fields = ['name', 'email', 'phone', 'role', 'location_type', 'location_id', 'is_active'];

        if ($editing) {
            $user = $this->account($this->editingId);

            if (! $this->keepsAnActiveOwner($user, $data['role'], $data['is_active'])) {
                $this->addError('form_role', 'The business must keep at least one active owner.');
                return;
            }

            $old = $user->only($fields);
            if ($this->form_password !== '') {
                $data['password']             = Hash::make($this->form_password);
                $data['must_change_password'] = true;
            }
            $user->update($data);

            AuditLogger::log([
                'action'            => 'support_user_updated',
                'module'            => 'users',
                'entity'            => $user,
                'entity_identifier' => $user->email,
                'old_values'        => $old,
                'new_values'        => $user->fresh()->only($fields),
                'severity'          => $old['role'] !== $user->role ? 'warning' : 'info',
            ]);

            if ($this->form_password !== '') {
                AuditLogger::log([
                    'action'            => 'support_password_reset',
                    'module'            => 'users',
                    'entity'            => $user,
                    'entity_identifier' => $user->email,
                    'severity'          => 'warning',
                ]);
            }

            $this->toast('success', 'Account updated.');
        } else {
            $data['password']             = Hash::make($this->form_password);
            $data['must_change_password'] = true;
            $user = User::create($data);

            AuditLogger::log([
                'action'            => 'support_user_created',
                'module'            => 'users',
                'entity'            => $user,
                'entity_identifier' => $user->email,
                'new_values'        => $user->only($fields),
            ]);

            $this->toast('success', 'Account created.');
        }

        $this->closeDrawer();
    }

    public function confirmToggle(int $userId): void
    {
        $this->confirmToggleId = $this->account($userId)->id;
    }

    public function cancelToggle(): void
    {
        $this->confirmToggleId = null;
    }

    public function executeToggle(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $user     = $this->account((int) $this->confirmToggleId);
        $newState = ! $user->is_active;

        if (! $this->keepsAnActiveOwner($user, $user->role, $newState)) {
            $this->toast('error', 'The business must keep at least one active owner.');
            $this->cancelToggle();
            return;
        }

        $user->update(['is_active' => $newState]);

        AuditLogger::log([
            'action'            => $newState ? 'support_user_activated' : 'support_user_deactivated',
            'module'            => 'users',
            'entity'            => $user,
            'entity_identifier' => $user->email,
            'old_values'        => ['is_active' => ! $newState],
            'new_values'        => ['is_active' => $newState],
            'severity'          => $newState ? 'info' : 'warning',
        ]);

        $this->toast($newState ? 'success' : 'warning', $user->name . ($newState ? ' is active again.' : ' has been deactivated.'));
        $this->cancelToggle();
    }

    public function getShopsProperty()
    {
        return Shop::where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    public function getWarehousesProperty()
    {
        return Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    public function render()
    {
        $users = User::visibleToBusiness()
            ->with('location')
            ->when($this->search !== '', function ($q) {
                $like = '%' . addcslashes($this->search, '%_\\') . '%';
                $q->where(fn ($w) => $w->where('name', 'ilike', $like)
                    ->orWhere('email', 'ilike', $like)
                    ->orWhere('phone', 'like', $like));
            })
            ->when(in_array($this->roleFilter, self::ASSIGNABLE_ROLES, true), fn ($q) => $q->where('role', $this->roleFilter))
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderByRaw('CASE WHEN is_active THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.support.accounts', ['users' => $users]);
    }

    /** Only business accounts can be loaded here — never a support account. */
    private function account(int $id): User
    {
        return User::visibleToBusiness()->findOrFail($id);
    }

    /** True unless this change would leave the business without an active owner. */
    private function keepsAnActiveOwner(User $user, UserRole $newRole, bool $newActive): bool
    {
        $staysActiveOwner = $newRole === UserRole::OWNER && $newActive;
        if ($staysActiveOwner || $user->role !== UserRole::OWNER || ! $user->is_active) {
            return true;
        }

        return User::where('role', UserRole::OWNER->value)->where('is_active', true)->where('id', '!=', $user->id)->exists();
    }

    private function toast(string $type, string $message): void
    {
        $this->dispatch('notification', ['type' => $type, 'message' => $message]);
    }

    private function resetForm(): void
    {
        $this->editingId          = null;
        $this->form_name          = '';
        $this->form_email         = '';
        $this->form_phone         = '';
        $this->form_role          = 'shop_manager';
        $this->form_location_type = 'shop';
        $this->form_location_id   = null;
        $this->form_password      = '';
        $this->form_is_active     = true;
        $this->resetValidation();
    }
}
