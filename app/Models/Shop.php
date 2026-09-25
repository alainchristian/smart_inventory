<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'sells_all_categories',
        'is_active',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'sells_all_categories' => 'boolean',
    ];

    /** Per-instance memo for sellableCategoryIds() (false = not computed yet). */
    private array|null|false $sellableCache = false;

    // ── What this shop sells ─────────────────────────────────────────────
    // THE rule for shop specialisation: requests, the POS (shop + warehouse
    // stock), the Warehouse Sale page and SaleService/TransferService all
    // go through sellsCategory()/sellableCategoryIds(). Don't re-implement.

    /** Categories ticked for this shop (only meaningful when not sells_all_categories). */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'shop_categories')->withTimestamps();
    }

    /**
     * Category ids this shop may request/sell, including every subcategory
     * of a ticked parent. null = general store (everything).
     */
    public function sellableCategoryIds(): ?array
    {
        if ($this->sellableCache !== false) {
            return $this->sellableCache;
        }
        if ($this->sells_all_categories ?? true) {
            return $this->sellableCache = null;
        }

        $ids      = $this->categories()->pluck('categories.id')->map(fn ($i) => (int) $i)->all();
        $children = Category::query()->whereNotNull('parent_id')->get(['id', 'parent_id'])->groupBy('parent_id');

        $queue = $ids;
        while ($queue) {
            $parent = array_shift($queue);
            foreach ($children->get($parent, collect()) as $child) {
                if (! in_array((int) $child->id, $ids, true)) {
                    $ids[]   = (int) $child->id;
                    $queue[] = (int) $child->id;
                }
            }
        }

        return $this->sellableCache = $ids;
    }

    public function sellsCategory(?int $categoryId): bool
    {
        $ids = $this->sellableCategoryIds();

        return $ids === null || ($categoryId !== null && in_array($categoryId, $ids, true));
    }

    public function sellsProduct(Product|int $product): bool
    {
        $categoryId = $product instanceof Product
            ? $product->category_id
            : Product::whereKey($product)->value('category_id');

        return $this->sellsCategory($categoryId !== null ? (int) $categoryId : null);
    }

    /** Short description for UIs, e.g. "All categories" or "Footwear, Bags". */
    public function sellsLabel(): string
    {
        return ($this->sells_all_categories ?? true)
            ? 'All categories'
            : ($this->categories()->orderBy('name')->pluck('name')->implode(', ') ?: 'Nothing selected');
    }

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
