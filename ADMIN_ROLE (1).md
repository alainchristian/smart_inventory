# Admin Role + Force Password Change
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read ADMIN_ROLE.md and follow every step in order."

---

## Hierarchy (read before touching any code)

```
Owner       — supreme authority, owns the business
  └── Admin     — system delegate, manages ops on owner's behalf
        └── Warehouse Manager
        └── Shop Manager
```

**Hard rules encoded in this implementation:**
- Only Owner can create / edit / deactivate Admin accounts
- Admin can NEVER create, edit, or deactivate Owner accounts
- Admin can manage Warehouse Managers and Shop Managers
- Admin sees everything Owner sees (dashboard, reports, prices)
- Owner always sees all Admin activity in activity_logs
- If owner fires admin → owner deactivates the account from /owner/users
- Admin can never lock Owner out of the system

---

## Read these files first

```bash
cat app/Enums/UserRole.php
cat app/Models/User.php
cat app/Http/Middleware/CheckRole.php
cat app/Livewire/Owner/Users/UserList.php | head -100
cat resources/views/livewire/owner/users/user-list.blade.php | grep -n "role-card\|um-role" | head -20
cat resources/views/livewire/layout/sidebar.blade.php | grep -n "isOwner" | head -20
grep -n "CheckRole\|withMiddleware\|alias" bootstrap/app.php
grep -rn "password.change\|change-password" routes/ 2>/dev/null
```

---

## STEP 1 — Migration: add admin enum value + must_change_password column

**Create:** `database/migrations/[timestamp]_add_admin_role_and_password_flag.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL: add value to existing enum type
        DB::statement("ALTER TYPE user_role ADD VALUE IF NOT EXISTS 'admin'");

        // Add password-change flag — default false for existing users
        if (!Schema::hasColumn('users', 'must_change_password')) {
            DB::statement(
                "ALTER TABLE users ADD COLUMN must_change_password BOOLEAN NOT NULL DEFAULT false"
            );
        }
    }

    public function down(): void
    {
        // Cannot remove a PostgreSQL enum value — skip that rollback
        if (Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', fn($t) => $t->dropColumn('must_change_password'));
        }
    }
};
```

Run: `php artisan migrate`

---

## STEP 2 — Update UserRole enum

**File:** `app/Enums/UserRole.php`

Add case after `OWNER`:
```php
case ADMIN = 'admin';
```

Update `label()`:
```php
self::ADMIN => 'Admin',
```

Update `permissions()` — add new case (place before WAREHOUSE_MANAGER):
```php
self::ADMIN => [
    // Full visibility (same as owner)
    'view_all_locations',
    'view_purchase_prices',
    'approve_price_overrides',
    'view_reports',
    'manage_settings',
    'view_all_activity_logs',

    // User management — warehouse/shop managers only, NOT owners
    'manage_users',

    // Inventory & operations
    'manage_products',
    'manage_warehouse_inventory',
    'approve_transfers',
    'scan_boxes',
    'view_warehouse_reports',
    'request_transfers',
    'receive_transfers',
    'create_sales',
    'process_returns',
    'view_shop_reports',
],
```

---

## STEP 3 — Update User model

**File:** `app/Models/User.php`

Add `'must_change_password' => 'boolean'` to `$casts`.

Add these methods after `isShopManager()`:

```php
public function isAdmin(): bool
{
    return $this->role === UserRole::ADMIN;
}

/**
 * Whether the current user can manage (create/edit/deactivate) the target user.
 *
 * Hierarchy:
 *   Owner   → can manage Admin, Warehouse Manager, Shop Manager
 *   Admin   → can manage Warehouse Manager, Shop Manager only
 *   Others  → cannot manage anyone
 */
public function canManageUser(self $target): bool
{
    // Nobody manages themselves through this (handled separately)
    if ($this->id === $target->id) return false;

    if ($this->isOwner()) {
        // Owner manages everyone except other owners
        return !$target->isOwner();
    }

    if ($this->isAdmin()) {
        // Admin manages only warehouse and shop managers — never owners or other admins
        return $target->isWarehouseManager() || $target->isShopManager();
    }

    return false;
}
```

Update `getDashboardRoute()` to include Admin:
```php
return match($this->role) {
    UserRole::ADMIN             => route('owner.dashboard'),
    UserRole::OWNER             => route('owner.dashboard'),
    UserRole::WAREHOUSE_MANAGER => route('warehouse.dashboard'),
    UserRole::SHOP_MANAGER      => route('shop.dashboard'),
    default                     => route('login'),
};
```

Update `canViewPurchasePrices()`:
```php
public function canViewPurchasePrices(): bool
{
    return $this->isOwner() || $this->isAdmin();
}
```

---

## STEP 4 — Update CheckRole middleware

**File:** `app/Http/Middleware/CheckRole.php`

Read the file. Find the role-checking logic. Replace the core with:

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    // Owner and Admin both pass the 'owner' role check
    // (Admin uses all owner routes — no separate route prefix needed)
    if ($user->isOwner() || $user->isAdmin()) {
        // Check if any required role is 'owner' — both pass
        if (in_array('owner', $roles)) {
            return $next($request);
        }
    }

    // Check exact role match
    foreach ($roles as $role) {
        if ($user->role->value === $role) {
            return $next($request);
        }
    }

    abort(403, 'Unauthorized.');
}
```

---

## STEP 5 — Create CheckPasswordChange middleware

**File:** `app/Http/Middleware/CheckPasswordChange.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            auth()->check() &&
            auth()->user()->must_change_password &&
            !$request->routeIs('password.change') &&
            !$request->routeIs('logout') &&
            !$request->routeIs('livewire.*')
        ) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
```

---

## STEP 6 — Register middleware in bootstrap/app.php

**File:** `bootstrap/app.php`

Find the `withMiddleware` call. Add the alias and append to web group:

```php
->withMiddleware(function (Middleware $middleware) {
    // Keep any existing aliases here, just add this one:
    $middleware->alias([
        'check.password.change' => \App\Http\Middleware\CheckPasswordChange::class,
    ]);

    // Runs on every authenticated web request
    $middleware->appendToGroup('web', \App\Http\Middleware\CheckPasswordChange::class);
})
```

---

## STEP 7 — Add route + Livewire component for password change

**File:** `routes/web.php`

Inside the `auth` middleware group, add:

```php
Route::get('/change-password', function () {
    return view('auth.change-password');
})->name('password.change');
```

**File:** `app/Livewire/Auth/ChangePassword.php`

```php
<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ChangePassword extends Component
{
    public string $password              = '';
    public string $password_confirmation = '';
    public bool   $show                  = false;
    public bool   $show_confirm          = false;

    public function save(): void
    {
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'Passwords do not match.',
            'password.min'       => 'Password must be at least 8 characters.',
        ]);

        $user = auth()->user();

        $user->update([
            'password'             => Hash::make($this->password),
            'must_change_password' => false,
        ]);

        $this->redirect($user->getDashboardRoute(), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }
}
```

**File:** `resources/views/auth/change-password.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Password — Smart Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="margin:0;background:var(--bg)">
    <livewire:auth.change-password />
    @livewireScripts
</body>
</html>
```

**File:** `resources/views/livewire/auth/change-password.blade.php`

```blade
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:var(--bg);font-family:var(--font);padding:20px">
<div style="width:100%;max-width:420px">

    {{-- Header --}}
    <div style="text-align:center;margin-bottom:28px">
        <div style="font-size:24px;font-weight:800;color:var(--text);
                    letter-spacing:-.5px;margin-bottom:6px">
            Set Your Password
        </div>
        <div style="font-size:14px;color:var(--text-dim);line-height:1.6">
            Your account was just created. Choose a strong password
            to secure your access before continuing.
        </div>
    </div>

    {{-- Card --}}
    <div style="background:var(--surface);border:1px solid var(--border);
                border-radius:var(--r);padding:28px">

        {{-- New password --}}
        <div style="margin-bottom:16px">
            <label style="display:block;font-size:13px;font-weight:600;
                          color:var(--text-sub);margin-bottom:6px">
                New Password <span style="color:var(--red)">*</span>
            </label>
            <div style="position:relative">
                <input wire:model="password"
                       type="{{ $show ? 'text' : 'password' }}"
                       style="width:100%;padding:10px 40px 10px 12px;
                              border:1.5px solid var(--border);border-radius:9px;
                              font-size:14px;background:var(--surface);color:var(--text);
                              outline:none;box-sizing:border-box;font-family:var(--font);
                              transition:border-color .15s"
                       placeholder="Min. 8 characters"
                       autocomplete="new-password">
                <button type="button" wire:click="$toggle('show')"
                        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                               background:none;border:none;cursor:pointer;color:var(--text-dim)">
                    @if($show)
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                    @else
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    @endif
                </button>
            </div>
            @error('password')
            <div style="font-size:11px;color:var(--red);margin-top:4px">{{ $message }}</div>
            @enderror
        </div>

        {{-- Confirm password --}}
        <div style="margin-bottom:24px">
            <label style="display:block;font-size:13px;font-weight:600;
                          color:var(--text-sub);margin-bottom:6px">
                Confirm Password <span style="color:var(--red)">*</span>
            </label>
            <div style="position:relative">
                <input wire:model="password_confirmation"
                       type="{{ $show_confirm ? 'text' : 'password' }}"
                       style="width:100%;padding:10px 40px 10px 12px;
                              border:1.5px solid var(--border);border-radius:9px;
                              font-size:14px;background:var(--surface);color:var(--text);
                              outline:none;box-sizing:border-box;font-family:var(--font);
                              transition:border-color .15s"
                       placeholder="Repeat your password"
                       autocomplete="new-password">
                <button type="button" wire:click="$toggle('show_confirm')"
                        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                               background:none;border:none;cursor:pointer;color:var(--text-dim)">
                    @if($show_confirm)
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                    @else
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    @endif
                </button>
            </div>
        </div>

        {{-- Submit --}}
        <button wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                style="width:100%;padding:12px;background:var(--accent);color:#fff;
                       border:none;border-radius:10px;font-size:14px;font-weight:700;
                       cursor:pointer;font-family:var(--font);
                       box-shadow:0 3px 10px rgba(59,111,212,.25);transition:opacity .15s">
            <span wire:loading.remove wire:target="save">Set Password & Continue</span>
            <span wire:loading wire:target="save" style="display:none">Saving…</span>
        </button>

    </div>

    {{-- Footer --}}
    <div style="text-align:center;margin-top:16px;font-size:12px;color:var(--text-dim)">
        Signed in as <strong style="color:var(--text-sub)">{{ auth()->user()->email }}</strong> ·
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault();document.getElementById('cp-logout').submit();"
           style="color:var(--accent);text-decoration:none;font-weight:600">
            Sign out
        </a>
    </div>
    <form id="cp-logout" action="{{ route('logout') }}" method="POST" style="display:none">
        @csrf
    </form>

</div>
</div>
```

---

## STEP 8 — Update UserList PHP: hierarchy guards + must_change_password

**File:** `app/Livewire/Owner/Users/UserList.php`

**8a** — Update `mount()` access check:
```php
// Both owner and admin can access user management
if (!auth()->user()->isOwner() && !auth()->user()->isAdmin()) {
    abort(403);
}
```

**8b** — Update validation to include `admin` in allowed roles:
```php
'form_role' => 'required|in:admin,owner,warehouse_manager,shop_manager',
```

**8c** — In `save()`, add hierarchy enforcement BEFORE the `$data` array.
Insert after `$this->validate(...)`:

```php
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
    $target = \App\Models\User::find($this->editingId);
    if ($target && ($target->isOwner() || $target->isAdmin())) {
        $this->dispatch('notification', [
            'type'    => 'error',
            'message' => 'You do not have permission to edit this account.',
        ]);
        $this->closeDrawer();
        return;
    }
}
```

**8d** — In `save()` `$data` array, add:
```php
// New users must change password on first login
// Editing an existing user does NOT reset this flag
'must_change_password' => $this->isEditing ? $this->getExistingFlag() : true,
```

Add helper method at bottom of class:
```php
private function getExistingFlag(): bool
{
    if (!$this->editingId) return false;
    return (bool) \App\Models\User::find($this->editingId)?->must_change_password;
}
```

**8e** — Update `confirmToggle()` to block admin from toggling owner/admin accounts:
```php
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
```

---

## STEP 9 — Update UserList blade: Admin role card + correct visibility

**File:** `resources/views/livewire/owner/users/user-list.blade.php`

**9a** — Find the role cards section. Add the Admin card as the first card.
Wrap it so ONLY owners see it:

```blade
{{-- Admin card: visible to owner only --}}
@if(auth()->user()->isOwner())
<div wire:click="$set('form_role','admin')"
     class="um-role-card {{ $form_role === 'admin' ? 'active' : '' }}">
    <div class="um-role-radio">
        @if($form_role === 'admin')
            <div class="um-role-radio-dot"></div>
        @endif
    </div>
    <div>
        <div class="um-role-name" style="color:var(--red)">
            🔐 Admin
        </div>
        <div class="um-role-desc">
            Full system access. Can manage warehouse and shop managers,
            view all reports and prices. Cannot manage Owner accounts.
            Managed by Owner only.
        </div>
    </div>
</div>
@endif
```

**9b** — Also wrap the Owner role card so admin cannot create owners:

```blade
@if(auth()->user()->isOwner())
<div wire:click="$set('form_role','owner')"
     class="um-role-card {{ $form_role === 'owner' ? 'active' : '' }}">
    ...existing owner card content...
</div>
@endif
```

**9c** — Update the role badge `$roleColor` in the table row `@php` block:
```php
'admin' => ['bg' => 'var(--red-dim)', 'color' => 'var(--red)'],
```

**9d** — Update `$avatarBg`:
```php
'admin' => 'var(--red)',
```

---

## STEP 10 — Update sidebar for Admin access

**File:** `resources/views/livewire/layout/sidebar.blade.php`

Find every occurrence of:
```blade
@if(auth()->user()->isOwner())
```

That wraps the owner navigation menu. Change each one to:
```blade
@if(auth()->user()->isOwner() || auth()->user()->isAdmin())
```

Do NOT change any other conditional — only the ones guarding
the owner navigation section.

---

## STEP 11 — Clear and discover

```bash
php artisan livewire:discover
php artisan view:clear && php artisan cache:clear
```

---

## Verification

**Hierarchy enforcement:**
1. Log in as Owner → New User → Admin card visible, Owner card visible
2. Log in as Admin → New User → Admin card NOT visible, Owner card NOT visible
3. Admin can create Warehouse/Shop Manager accounts
4. Admin tries to open edit drawer on an Owner → blocked, notification shown
5. Admin tries to deactivate an Owner → blocked, notification shown
6. Owner deactivates Admin → works, admin loses access immediately

**Force password change:**
7. Owner creates any new user → `must_change_password = true` in DB
8. New user logs in → redirected to /change-password immediately
9. New user cannot reach any other URL until password is set
10. New user sets password → lands on correct dashboard for their role
11. New user logs out and back in → goes straight to dashboard
12. Owner edits existing user → `must_change_password` flag unchanged
