<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
        'left',
        'right',
        'depth',
        'is_active',
    ];

    protected $casts = [
        'left' => 'integer',
        'right' => 'integer',
        'depth' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Nested set operations
    public function getAncestors()
    {
        return Category::where('left', '<', $this->left)
            ->where('right', '>', $this->right)
            ->orderBy('left')
            ->get();
    }

    public function getDescendants()
    {
        return Category::where('left', '>', $this->left)
            ->where('right', '<', $this->right)
            ->orderBy('left')
            ->get();
    }

    public function isAncestorOf(Category $category): bool
    {
        return $this->left < $category->left && $this->right > $category->right;
    }

    public function isDescendantOf(Category $category): bool
    {
        return $this->left > $category->left && $this->right < $category->right;
    }

    // Scopes
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
