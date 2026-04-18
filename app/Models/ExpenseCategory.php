<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'applies_to',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForLocation($query, string $type)
    {
        return $query->where(function ($q) use ($type) {
            $q->where('applies_to', $type)->orWhere('applies_to', 'both');
        });
    }

    // Excludes system-only 'Cash Shortage' — use this for all user-facing dropdowns
    public function scopeUserSelectable($query)
    {
        return $query->where('name', '!=', 'Cash Shortage')->where('is_active', true)->orderBy('sort_order');
    }
}
