<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'phone',
        'manager_name',
        'default_warehouse_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_warehouse_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'location_id')
            ->where('location_type', 'shop');
    }

    public function boxes(): MorphMany
    {
        return $this->morphMany(Box::class, 'location');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_shop_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function damagedGoods(): MorphMany
    {
        return $this->morphMany(DamagedGood::class, 'location');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
