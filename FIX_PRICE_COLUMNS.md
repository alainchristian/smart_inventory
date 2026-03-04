# Fix Price Columns — Move Box Prices to Correct Columns
## Claude Code Instructions

---

## The Problem

All 267 products have prices stored in the wrong columns:

| Column          | Current (wrong)         | Should be              |
|-----------------|-------------------------|------------------------|
| `selling_price`     | box price (e.g. 55,000) | item price (e.g. 9,167) |
| `box_selling_price` | NULL                    | box price (e.g. 55,000) |
| `purchase_price`    | box price (e.g. 22,000) | item price (e.g. 3,667) |

The system was designed so that:
- `selling_price` = price per individual item
- `box_selling_price` = price per full box
- `purchase_price` = cost per individual item

Dashboard SQL does `SUM(items_remaining × selling_price)` which only works
correctly when `selling_price` is an item price. Currently it is inflated
by `items_per_box` because a box price is stored there instead.

---

## Step 1 — Create a migration

```bash
php artisan make:migration fix_product_price_columns --table=products
```

Open the newly created migration file and replace its content with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Move box price into box_selling_price, convert both price columns to per-item
        DB::statement('
            UPDATE products SET
                box_selling_price = selling_price,
                selling_price     = ROUND(selling_price  / items_per_box),
                purchase_price    = ROUND(purchase_price / items_per_box)
            WHERE deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        // Reverse: convert item prices back to box prices
        DB::statement('
            UPDATE products SET
                selling_price     = box_selling_price,
                purchase_price    = ROUND(purchase_price * items_per_box),
                box_selling_price = NULL
            WHERE deleted_at IS NULL
        ');
    }
};
```

Run it:

```bash
php artisan migrate
```

---

## Step 2 — Verify the migration result in tinker

```bash
php artisan tinker --execute="
use App\Models\Product;

\$p = Product::where('sku', 'NK-AF1-37')->first();
echo 'SKU:               ' . \$p->sku . PHP_EOL;
echo 'items_per_box:     ' . \$p->items_per_box . PHP_EOL;
echo 'purchase_price:    ' . number_format(\$p->purchase_price)    . ' (per item)' . PHP_EOL;
echo 'selling_price:     ' . number_format(\$p->selling_price)     . ' (per item)' . PHP_EOL;
echo 'box_selling_price: ' . number_format(\$p->box_selling_price) . ' (per box)'  . PHP_EOL;
echo PHP_EOL;
echo 'Check: selling_price × items_per_box = ' . number_format(\$p->selling_price * \$p->items_per_box) . ' (should equal box_selling_price)' . PHP_EOL;
"
```

Expected output:
```
SKU:               NK-AF1-37
items_per_box:     6
purchase_price:    3,667 (per item)
selling_price:     9,167 (per item)
box_selling_price: 55,000 (per box)

Check: selling_price × items_per_box = 55,002 (should equal box_selling_price)
```

---

## Step 3 — Remove any /items_per_box workarounds

If `FIX_BOX_PRICE_VALUATION.md` was already applied, the dashboard SQL now has
`/ products.items_per_box` in the valuation queries — that must be removed since
`selling_price` is now already an item price.

Search for and remove these:

```bash
grep -rn "items_per_box" app/Http/Controllers/ app/Livewire/ app/Services/ --include="*.php"
```

For every hit that looks like:
```php
SUM(boxes.items_remaining * products.selling_price  / products.items_per_box)
SUM(boxes.items_remaining * products.purchase_price / products.items_per_box)
```

Remove the `/ products.items_per_box` so it becomes:
```php
SUM(boxes.items_remaining * products.selling_price)
SUM(boxes.items_remaining * products.purchase_price)
```

---

## Step 4 — Remove any remaining /100 divisions

```bash
grep -rn "/ 100\b" \
  app/Http/Controllers/Owner/DashboardController.php \
  app/Http/Controllers/ShopManager/DashboardController.php \
  app/Livewire/Dashboard/BusinessKpiRow.php \
  app/Services/Analytics/InventoryAnalyticsService.php \
  --include="*.php"
```

Remove every `/ 100` that divides a price or revenue value. Prices are now
stored as whole RWF integers — no division by 100 is needed anywhere.

---

## Step 5 — Fix the Product create/edit forms

Now that `selling_price` = item price and `box_selling_price` = box price,
the product forms need to reflect this correctly.

### `app/Livewire/Owner/Products/CreateProduct.php`

Read the file. Find where prices are saved. Ensure:
```php
'purchase_price'    => (int) $this->purchasePrice,    // item price
'selling_price'     => (int) $this->sellingPrice,     // item price
'box_selling_price' => $this->boxSellingPrice         // box price (optional)
                       ? (int) $this->boxSellingPrice
                       : null,
```

Remove any `* 100` or `/ 100` around these.

### `app/Livewire/Owner/Products/EditProduct.php`

Read the file. In `mount()`, ensure the form is populated with:
```php
$this->purchasePrice  = $product->purchase_price;    // item price
$this->sellingPrice   = $product->selling_price;     // item price
$this->boxSellingPrice = $product->box_selling_price; // box price
```

Remove any `/ 100` or `* 100` around these.

---

## Step 6 — Fix the Excel import (ReceiveBoxes)

**Target:** `app/Livewire/Warehouse/Inventory/ReceiveBoxes.php`

The Excel import currently reads `selling_price` from the CSV and saves it directly.
Since the CSV will now always contain BOX prices (that's how the business thinks),
the importer must convert on save:

Find where products are created/updated from the Excel import.
When saving `selling_price`, divide by `items_per_box`:

```php
// When creating/updating product from Excel:
'selling_price'     => (int) round($item['selling_price']  / $item['items_per_box']),
'purchase_price'    => (int) round($item['purchase_price'] / $item['items_per_box']),
'box_selling_price' => (int) $item['selling_price'],   // store the original box price
```

This means the warehouse manager can keep uploading CSVs with box prices
(which is how they think about pricing) and the system will store them correctly.

---

## Step 7 — Fix the POS to use box_selling_price for full-box sales

**Target:** `app/Livewire/Shop/Sales/PointOfSale.php`

When a sale is `is_full_box = true`, the price should come from `box_selling_price`,
not `selling_price × items_per_box`.

Find where box price is calculated. Look for any `selling_price * items_per_box` pattern:

```bash
grep -n "selling_price.*items_per_box\|items_per_box.*selling_price" \
  app/Livewire/Shop/Sales/PointOfSale.php
```

Replace any:
```php
$price = $product->selling_price * $product->items_per_box;
```
with:
```php
$price = $product->box_selling_price ?? ($product->selling_price * $product->items_per_box);
```

---

## Step 8 — Verify dashboard totals in tinker

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

echo 'Cost value:   RWF ' . number_format(\$inv->cost_value)   . PHP_EOL;
echo 'Retail value: RWF ' . number_format(\$inv->retail_value) . PHP_EOL;
echo 'Total items:  '     . number_format(\$inv->total_items)  . PHP_EOL;
"
```

**Expected results (matching the owner spreadsheet):**
```
Cost value:   RWF 59,311,200
Retail value: RWF 131,194,500
Total items:  48,996
```

---

## Step 9 — Clear all caches

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan optimize:clear
```

---

## Summary

| What changed | Before | After |
|---|---|---|
| `selling_price` column | Box price (55,000) | Item price (9,167) |
| `box_selling_price` column | NULL | Box price (55,000) |
| `purchase_price` column | Box price (22,000) | Item price (3,667) |
| Dashboard cost value | 362,311,200 (wrong) | 59,311,200 (correct) |
| Dashboard retail value | 905,778,000 (wrong) | 131,194,500 (correct) |
| POS full-box sale price | calculated on the fly | from `box_selling_price` |
| Excel import | saves CSV price as-is | converts box→item, stores box separately |
