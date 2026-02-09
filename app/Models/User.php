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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'location_type' => LocationType::class,
        'is_active' => 'boolean',
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
    public function isOwner(): bool
    {
        return $this->role === UserRole::OWNER;
    }

    public function isWarehouseManager(): bool
    {
        return $this->role === UserRole::WAREHOUSE_MANAGER;
    }

    public function isShopManager(): bool
    {
        return $this->role === UserRole::SHOP_MANAGER;
    }

    public function canViewPurchasePrices(): bool
    {
        return $this->isOwner();
    }

    public function canApprovePriceOverrides(): bool
    {
        return $this->isOwner();
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->role->permissions());
    }

    public function canManageLocation(LocationType $locationType, int $locationId): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return $this->location_type === $locationType && $this->location_id === $locationId;
    }

    public function hasLocationAccess(LocationType $locationType, int $locationId): bool
    {
        // Owner has access to all locations
        if ($this->isOwner()) {
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