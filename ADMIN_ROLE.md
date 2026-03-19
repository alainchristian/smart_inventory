# Admin Role + Force Password Change
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read ADMIN_ROLE.md and follow every step in order."

---

## Read these files first

```bash
cat app/Enums/UserRole.php
cat app/Models/User.php
cat app/Http/Middleware/CheckRole.php
cat app/Livewire/Owner/Users/UserList.php | head -80
cat resources/views/livewire/owner/users/user-list.blade.php | grep -A20 "role-card"
cat resources/views/livewire/layout/sidebar.blade.php | grep -n "isOwner\|isAdmin" | head -20
grep -n "CheckRole\|middleware" bootstrap/app.php
grep -n "users.index\|owner\." routes/web.php | head -20
```

---

## STEP 1 — Migration: add admin to enum + must_change_password column

**File:** Create `database/migrations/[timestamp]_add_admin_role_and_password_flag.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add admin to the PostgreSQL enum
        DB::statement("ALTER TYPE user_role ADD VALUE IF NOT EXISTS 'admin'");

        // Add must_change_password column
        if (!Schema::hasColumn('users', 'must_change_password')) {
            DB::statement("ALTER TABLE users ADD COLUMN must_change_password BOOLEAN NOT NULL DEFAULT false");
        }
    }

    public function down(): void
    {
        // PostgreSQL cannot remove enum values — skip rollback of enum
        // Remove column only
        if (Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('must_change_password');
            });
        }
    }
};
```

Run: `php artisan migrate`

---

## STEP 2 — Update UserRole enum

**File:** `app/Enums/UserRole.php`

Add the `ADMIN` case and update all match blocks:

```php
case ADMIN = 'admin';
```

Update `label()`:
```php
self::ADMIN => 'Admin',
```

Update `permissions()`:
```php
self::ADMIN => [
    'view_all_locations',
    'manage_users',
    'manage_owners',          // can create/edit/deactivate owner accounts
    'view_purchase_prices',
    'approve_price_overrides',
    'manage_products',
    'view_reports',
    'manage_settings',
    'view_all_activity_logs', // sees all logs including owner actions
    'manage_system',          // system-level settings
    'request_transfers',
    'receive_transfers',
    'create_sales',
    'process_returns',
    'view_shop_reports',
    'manage_warehouse_inventory',
    'approve_transfers',
    'scan_boxes',
    'view_warehouse_reports',
],
```

---

## STEP 3 — Update User model

**File:** `app/Models/User.php`

Add to `$casts`:
```php
'must_change_password' => 'boolean',
```

Add helper method after `isShopManager()`:
```php
public function isAdmin(): bool
{
    return $this->role === UserRole::ADMIN;
}

public function isSuperUser(): bool
{
    return $this->role === UserRole::ADMIN || $this->role === UserRole::OWNER;
}

public function canManageUser(User $target): bool
{
    // Admin can manage everyone including owners
    if ($this->isAdmin()) return true;

    // Owner can manage warehouse and shop managers, NOT other owners or admins
    if ($this->isOwner()) {
        return !$target->isOwner() && !$target->isAdmin();
    }

    return false;
}
```

Update `getDashboardRoute()`:
```php
return match($this->role) {
    UserRole::ADMIN            => route('owner.dashboard'),
    UserRole::OWNER            => route('owner.dashboard'),
    UserRole::WAREHOUSE_MANAGER => route('warehouse.dashboard'),
    UserRole::SHOP_MANAGER     => route('shop.dashboard'),
    default                    => route('login'),
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

Read the file first. Find the logic that checks the role.
Admin must pass any role check that owner passes.

Replace the core check logic with:

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    // Admin bypasses all role restrictions
    if ($user->isAdmin()) {
        return $next($request);
    }

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

class CheckPasswordChange extends Response
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

Fix the class — it should extend nothing special:

```php
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

Find the `withMiddleware` block. Add:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        // ... existing aliases ...
        'check.password.change' => \App\Http\Middleware\CheckPasswordChange::class,
    ]);

    // Append to web middleware group so it runs on every web request
    $middleware->appendToGroup('web', \App\Http\Middleware\CheckPasswordChange::class);
})
```

---

## STEP 7 — Add route + Livewire component for password change

**File:** `routes/web.php`

Add inside the `auth` middleware group (after the dashboard route):

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', function () {
        return view('auth.change-password');
    })->name('password.change');
});
```

**File:** `app/Livewire/Auth/ChangePassword.php`

```php
<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ChangePassword extends Component
{
    public string $password        = '';
    public string $password_confirmation = '';
    public bool   $show            = false;
    public bool   $show_confirm    = false;

    protected function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:password', // can't reuse same value easily
            ],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();
        $user->update([
            'password'             => Hash::make($this->password),
            'must_change_password' => false,
        ]);

        session()->flash('status', 'Password updated successfully. Welcome!');
        $this->redirect($user->getDashboardRoute(), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.change-password')
            ->layout('layouts.guest');
    }
}
```

**File:** `resources/views/livewire/auth/change-password.blade.php`

```blade
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:var(--bg);font-family:var(--font);padding:20px">

    <div style="width:100%;max-width:420px">

        {{-- Logo / title --}}
        <div style="text-align:center;margin-bottom:32px">
            <div style="font-size:24px;font-weight:800;color:var(--text);
                        letter-spacing:-.5px;margin-bottom:6px">
                Set Your Password
            </div>
            <div style="font-size:14px;color:var(--text-dim);line-height:1.5">
                Your account requires a new password before you can continue.
                Choose something strong and memorable.
            </div>
        </div>

        {{-- Card --}}
        <div style="background:var(--surface);border:1px solid var(--border);
                    border-radius:var(--r);padding:28px">

            @if(session('status'))
            <div style="padding:10px 14px;border-radius:var(--rx);margin-bottom:16px;
                        background:var(--green-dim);color:var(--green);
                        font-size:13px;font-weight:600">
                {{ session('status') }}
            </div>
            @endif

            {{-- New password --}}
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:13px;font-weight:600;
                              color:var(--text-sub);margin-bottom:6px">
                    New Password <span style="color:var(--red)">*</span>
                </label>
                <div style="position:relative">
                    <input wire:model="password"
                           type="{{ $show ? 'text' : 'password' }}"
                           style="width:100%;padding:10px 38px 10px 12px;
                                  border:1.5px solid var(--border);border-radius:9px;
                                  font-size:14px;background:var(--surface);
                                  color:var(--text);outline:none;box-sizing:border-box;
                                  font-family:var(--font)"
                           placeholder="Min. 8 characters"
                           autocomplete="new-password">
                    <button type="button" wire:click="$toggle('show')"
                            style="position:absolute;right:10px;top:50%;
                                   transform:translateY(-50%);background:none;
                                   border:none;cursor:pointer;color:var(--text-dim)">
                        <svg width="16" height="16" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            @if($show)
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8
                                     a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0
                                     0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                            @else
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                            @endif
                        </svg>
                    </button>
                </div>
                @error('password')
                <div style="font-size:11px;color:var(--red);margin-top:4px">
                    {{ $message }}
                </div>
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
                           style="width:100%;padding:10px 38px 10px 12px;
                                  border:1.5px solid var(--border);border-radius:9px;
                                  font-size:14px;background:var(--surface);
                                  color:var(--text);outline:none;box-sizing:border-box;
                                  font-family:var(--font)"
                           placeholder="Repeat your password"
                           autocomplete="new-password">
                    <button type="button" wire:click="$toggle('show_confirm')"
                            style="position:absolute;right:10px;top:50%;
                                   transform:translateY(-50%);background:none;
                                   border:none;cursor:pointer;color:var(--text-dim)">
                        <svg width="16" height="16" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            @if($show_confirm)
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8
                                     a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0
                                     0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                            @else
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                            @endif
                        </svg>
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
                           box-shadow:0 3px 10px rgba(59,111,212,.25)">
                <span wire:loading.remove wire:target="save">Set Password & Continue</span>
                <span wire:loading wire:target="save" style="display:none">Saving…</span>
            </button>

        </div>

        {{-- Signed in as --}}
        <div style="text-align:center;margin-top:16px;font-size:12px;
                    color:var(--text-dim)">
            Signed in as <strong>{{ auth()->user()->email }}</strong> ·
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();"
               style="color:var(--accent);text-decoration:none">
                Sign out
            </a>
        </div>

        <form id="logout-form" action="{{ route('logout') }}"
              method="POST" style="display:none">
            @csrf
        </form>

    </div>
</div>
```

**File:** `resources/views/auth/change-password.blade.php`

```blade
<x-app-layout>
    <livewire:auth.change-password />
</x-app-layout>
```

Wait — the change password page should use the guest layout (no sidebar).
Update the blade wrapper:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Password — Smart Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <livewire:auth.change-password />
</body>
</html>
```

---

## STEP 8 — Update UserList to show Admin role card + set must_change_password

**File:** `app/Livewire/Owner/Users/UserList.php`

In `mount()`, update the role guard — admin can also access this page:

```php
if (!auth()->user()->isOwner() && !auth()->user()->isAdmin()) {
    abort(403);
}
```

In `save()`, find where `$data` is assembled. Add:
```php
'must_change_password' => !$this->isEditing, // true on create, false on edit
```

In `save()`, find the ownership/self-deactivation check.
Add a guard so only admin can create/edit admin accounts:

```php
// Only admin can set role to admin
if ($this->form_role === 'admin' && !auth()->user()->isAdmin()) {
    $this->addError('form_role', 'Only an Admin can create another Admin account.');
    return;
}

// Owner cannot edit admin accounts
if ($this->isEditing) {
    $target = \App\Models\User::find($this->editingId);
    if ($target && $target->isAdmin() && !auth()->user()->isAdmin()) {
        $this->addError('form_role', 'You do not have permission to edit this account.');
        return;
    }
}
```

Also update the validation rules to include `admin` in role list:
```php
'form_role' => 'required|in:admin,owner,warehouse_manager,shop_manager',
```

---

## STEP 9 — Update UserList blade to show Admin role card

**File:** `resources/views/livewire/owner/users/user-list.blade.php`

Find the role cards section. Add the Admin card as the FIRST card,
before the Owner card:

```blade
{{-- Admin card — only shown if current user is admin --}}
@if(auth()->user()->isAdmin())
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
            System-level access. Can manage owners, view all activity logs,
            and access system settings. Cannot be managed by owners.
        </div>
    </div>
</div>
@endif
```

Also update the role badge color in the table rows:
Find the `$roleColor` match block and add:
```php
'admin' => ['bg'=>'var(--red-dim)', 'color'=>'var(--red)'],
```

And the `$avatarBg` match:
```php
'admin' => 'var(--red)',
```

---

## STEP 10 — Update sidebar to let admin access owner routes

**File:** `resources/views/livewire/layout/sidebar.blade.php`

Find every `@if(auth()->user()->isOwner())` check that guards the
owner menu. Change each one to:

```blade
@if(auth()->user()->isOwner() || auth()->user()->isAdmin())
```

This gives admin the full owner sidebar without adding new routes.

---

## STEP 11 — Clear and discover

```bash
php artisan livewire:discover
php artisan view:clear && php artisan cache:clear
```

---

## Do NOT touch

- Any existing authentication files (login, logout, session)
- Any transfer, sales, or POS components
- Any existing migrations

---

## Verification

**Admin role:**
1. Log in as an existing owner → go to /owner/users → "New User"
2. Admin role card is NOT visible (only admin can create admins)
3. Log in as admin → "New User" → Admin card IS visible
4. Admin can open and edit an owner account
5. Owner cannot open or edit an admin account (blocked with error)
6. Admin sees full owner sidebar including all reports and settings

**Force password change:**
7. Create any new user via the drawer → `must_change_password = true` in DB
8. Log in as that new user → immediately redirected to /change-password
9. Cannot navigate to any other page until password is set
10. Set a new password → redirected to correct dashboard for their role
11. Log in again → goes straight to dashboard (no redirect)
12. Edit an existing user → `must_change_password` is NOT reset to true
