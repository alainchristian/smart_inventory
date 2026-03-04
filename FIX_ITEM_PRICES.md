# Fix Item Prices — Store Per-Item, Compute Per-Box
## Claude Code Instructions

> Drop in project root. Tell Claude Code:
> "Read FIX_ITEM_PRICES.md and follow every step in order."

---

## Design

| Column | Stored? | Value | Example (AF1, ipb=6) |
|---|---|---|---|
| `purchase_price` | ✅ stored | per item | 3,667 RWF |
| `selling_price` | ✅ stored | per item | 9,167 RWF |
| `box_selling_price` | ✅ stored | per box override | 55,000 RWF |
| `box_purchase_price` | ❌ computed | `purchase_price × items_per_box` | 22,002 RWF |

`box_selling_price` is only stored when the owner wants a specific full-box
price that differs from `selling_price × items_per_box`. Otherwise it is NULL
and the system computes it automatically.

---

## Step 1 — Fix selling_price rounding via migration

The previous migration used FLOOR division instead of ROUND.
This creates a 1 RWF error per item on 73 products.

Fix: recompute `selling_price` from `box_selling_price` using proper ROUND.

```bash
php artisan make:migration fix_selling_price_rounding --table=products
```

Replace the new migration file content with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Recompute selling_price using ROUND instead of FLOOR
        // Only for products that have a box_selling_price reference
        DB::statement('
            UPDATE products
            SET selling_price = ROUND(box_selling_price::numeric / items_per_box)
            WHERE box_selling_price IS NOT NULL
              AND deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        // Revert to FLOOR (previous state)
        DB::statement('
            UPDATE products
            SET selling_price = FLOOR(box_selling_price::numeric / items_per_box)
            WHERE box_selling_price IS NOT NULL
              AND deleted_at IS NULL
        ');
    }
};
```

```bash
php artisan migrate
```

---

## Step 2 — Add box_purchase_price as a computed column in the Product model

**Target:** `app/Models/Product.php`

Add this accessor after the existing accessors:

```php
/**
 * Box purchase price — always computed from item price.
 * Never stored: purchase_price × items_per_box.
 */
public function getBoxPurchasePriceAttribute(): int
{
    return $this->purchase_price * $this->items_per_box;
}

/**
 * Effective box selling price.
 * Uses the stored override if set, otherwise computes from item price.
 */
public function getEffectiveBoxSellingPriceAttribute(): int
{
    return $this->box_selling_price ?? ($this->selling_price * $this->items_per_box);
}
```

Also append these to the `$appends` array (add the array if it doesn't exist):

```php
protected $appends = [
    'box_purchase_price',
    'effective_box_selling_price',
];
```

---

## Step 3 — Verify dashboard SQL is correct

The dashboard SQL does:
```sql
SUM(boxes.items_remaining × products.selling_price)   -- retail
SUM(boxes.items_remaining × products.purchase_price)  -- cost
```

Since `selling_price` and `purchase_price` are now per-item prices, and
`items_remaining` is an item count, this multiplication is correct.
**No changes needed to dashboard SQL.**

Verify in tinker:

```bash
php artisan tinker --execute="
use App\Models\Box;

\$inv = Box::available()
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->selectRaw('
        SUM(boxes.items_remaining * products.purchase_price) as cost_value,
        SUM(boxes.items_remaining * products.selling_price)  as retail_value,
        SUM(boxes.items_remaining)                           as total_items
    ')
    ->first();

echo 'Cost value:   RWF ' . number_format(\$inv->cost_value)  . PHP_EOL;
echo 'Retail value: RWF ' . number_format(\$inv->retail_value) . PHP_EOL;
echo 'Total items:  '     . number_format(\$inv->total_items)  . PHP_EOL;
"
```

Expected:
```
Cost value:   RWF ~52,477,800
Retail value: RWF ~131,194,500
Total items:  48,996
```

---

## Step 4 — Fix POS to use computed box prices

**Target:** `app/Livewire/Shop/Sales/PointOfSale.php`

When selling a full box, the price should use `effective_box_selling_price`
not a raw multiply. Find any full-box price calculation:

```bash
grep -n "box_selling_price\|selling_price.*items_per_box\|items_per_box.*selling_price" \
  app/Livewire/Shop/Sales/PointOfSale.php
```

Replace any:
```php
$price = $product->selling_price * $product->items_per_box;
```
With:
```php
$price = $product->effective_box_selling_price;
```

Replace any:
```php
$price = $product->box_selling_price ?? ($product->selling_price * $product->items_per_box);
```
With:
```php
$price = $product->effective_box_selling_price;
```

---

## Step 5 — Fix Excel import to accept item prices

**Target:** `app/Livewire/Warehouse/Inventory/ReceiveBoxes.php`

The warehouse manager's CSV now contains **per-item prices**.
The import must save them directly without any multiplication or division.

Find the section that saves product prices during import. Ensure:

```php
'purchase_price'    => (int) $item['purchase_price'],   // per item, direct
'selling_price'     => (int) $item['selling_price'],    // per item, direct
'box_selling_price' => isset($item['box_selling_price']) && $item['box_selling_price']
                       ? (int) $item['box_selling_price']
                       : null,
```

Remove any `* items_per_box`, `/ items_per_box`, `* 100`, or `/ 100` around price saves.

---

## Step 6 — Fix product create/edit forms

**Target:** `app/Livewire/Owner/Products/CreateProduct.php`
**Target:** `app/Livewire/Owner/Products/EditProduct.php`

Ensure form labels and hints reflect per-item pricing. The Blade views should
show a helper text like:
```
"selling_price — price per individual item (box price is calculated automatically)"
```

In both files, prices must be saved as integers with no conversion:
```php
'purchase_price'    => (int) $this->purchasePrice,
'selling_price'     => (int) $this->sellingPrice,
'box_selling_price' => $this->boxSellingPrice ? (int) $this->boxSellingPrice : null,
```

In the blade views, add a computed preview line below the selling_price field:
```blade
@if($sellingPrice && $itemsPerBox)
  <p style="font-size:12px;color:var(--text-sub);margin-top:4px">
    Box price: RWF {{ number_format((int)$sellingPrice * (int)$itemsPerBox) }}
    @if($boxSellingPrice)
      · Override set: RWF {{ number_format((int)$boxSellingPrice) }}
    @endif
  </p>
@endif
```

---

## Step 7 — Update the purchase price upload tool

**Target:** `app/Livewire/Owner/Products/UploadPurchasePrices.php`

The CSV the owner uploads must now contain **per-item** purchase prices.
Update the template download to include a helper column showing the computed
box purchase price so the owner can verify:

Find the `downloadTemplate()` method. Update the CSV header and rows:

```php
fputcsv($handle, ['sku', 'purchase_price', 'computed_box_purchase_price', 'product_name_reference']);

foreach ($products as $product) {
    fputcsv($handle, [
        $product->sku,
        $product->purchase_price,
        $product->purchase_price * $product->items_per_box,  // computed, read-only reference
        $product->name,
    ]);
}
```

Add a note in the blade view that `purchase_price` is per item:
```blade
<code>purchase_price</code> — price per individual item (RWF)
```

---

## Step 8 — Clear caches

```bash
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear
```

---

## Summary

| | Before | After |
|---|---|---|
| `selling_price` | 9,166 (floor rounding) | 9,167 (round) |
| `purchase_price` | 3,666 (floor rounding) | 3,666 (unchanged — no reference) |
| `box_selling_price` | 55,000 (stored) | 55,000 (stored override) |
| `box_purchase_price` | does not exist | computed via model accessor |
| Dashboard retail | 131,177,082 | ~131,194,500 |
| POS full-box price | varies | `effective_box_selling_price` |
