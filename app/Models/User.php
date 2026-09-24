<?php

namespace App\Models;

use App\Enums\LocationType;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'location_type',
        'location_id',
        'is_active',
        'last_login_at',
        'must_change_password',
        'notifications_read_at',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'notifications_read_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'location_type' => LocationType::class,
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
    ];

    // Relationships
    public function location(): MorphTo
    {
        return $this->morphTo('location', 'location_type', 'location_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'location_id')
            ->where('location_type', LocationType::WAREHOUSE);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'location_id')
            ->where('location_type', LocationType::SHOP);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'requested_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'sold_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    // Authorization helpers

    /** Session key holding the owner's temporary "act as admin" flag. */
    public const ACTING_AS_ADMIN_SESSION_KEY = 'acting_as_admin';

    /** The stored role, ignoring any temporary session switch. */
    public function isRealOwner(): bool
    {
        return $this->role === UserRole::OWNER;
    }

    /**
     * Whether this user is a real owner currently acting as admin.
     * Only ever true for the logged-in user themselves, so checks against
     * other User records (e.g. canManageUser($target)) are never affected.
     */
    public function isActingAsAdmin(): bool
    {
        return $this->isRealOwner()
            && auth()->id() === $this->id
            && (bool) session(self::ACTING_AS_ADMIN_SESSION_KEY, false);
    }

    /** The role used for authorization: ADMIN while an owner is acting as admin, else the stored role. */
    public function effectiveRole(): UserRole
    {
        return $this->isActingAsAdmin() ? UserRole::ADMIN : $this->role;
    }

    public function isOwner(): bool
    {
        return $this->effectiveRole() === UserRole::OWNER;
    }

    public function isWarehouseManager(): bool
    {
        return $this->role === UserRole::WAREHOUSE_MANAGER;
    }

    public function isAdmin(): bool
    {
        return $this->effectiveRole() === UserRole::ADMIN;
    }

    public function isShopManager(): bool
    {
        return $this->role === UserRole::SHOP_MANAGER;
    }

    /** Admin or Owner — both have full system access. */
    public function isSuperUser(): bool
    {
        return $this->isAdmin() || $this->isOwner();
    }

    /**
     * Whether the current user can manage (create/edit/deactivate) the target user.
     *
     * Hierarchy:
     *   Owner   → can manage Admin, Warehouse Manager, Shop Manager (not other owners)
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

    public function canViewPurchasePrices(): bool
    {
        return $this->isOwner() || $this->isAdmin();
    }

    public function canApprovePriceOverrides(): bool
    {
        return $this->isOwner() || $this->isAdmin();
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->effectiveRole()->permissions());
    }

    public function canManageLocation(LocationType $locationType, int $locationId): bool
    {
        if ($this->isOwner() || $this->isAdmin()) {
            return true;
        }

        return $this->location_type === $locationType && $this->location_id === $locationId;
    }

    public function hasLocationAccess(LocationType $locationType, int $locationId): bool
    {
        // Owner and Admin have access to all locations
        if ($this->isOwner() || $this->isAdmin()) {
            return true;
        }

        // Check if user's assigned location matches
        return $this->location_type === $locationType
            && $this->location_id === $locationId;
    }

    // Update last login
    public function recordLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    // Get dashboard route based on role
    public function getDashboardRoute(): string
    {
        return match($this->role) {
            UserRole::ADMIN => route('owner.dashboard'),
            UserRole::OWNER => route('owner.dashboard'),
            UserRole::WAREHOUSE_MANAGER => route('warehouse.dashboard'),
            UserRole::SHOP_MANAGER => route('shop.dashboard'),
            default => route('login'),
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForLocation($query, LocationType $locationType, int $locationId)
    {
        return $query->where('location_type', $locationType)
            ->where('location_id', $locationId);
    }
}