# SmartInventory — Remove Cents: Store Real RWF Everywhere
## Claude Code Instructions

> Drop in project root, then tell Claude Code:
> "Read REMOVE_CENTS.md and follow every step in order. Do not skip any step."

---

## The Problem

Prices are currently stored as **cents (RWF × 100)** in the database.  
e.g. RWF 55,000 → stored as `5,500,000`.  
This creates `/100` divisions and `*100` multiplications scattered across PHP, Blade, seeders, and services — causing inconsistencies and bugs.

## The Fix

Store **real RWF integers** directly.  
e.g. RWF 55,000 → stored as `55000`.  
Remove ALL `/100` and `*100` price conversions everywhere. Display with `number_format()` only.

---

## Step 0 — Audit (read before touching anything)

```bash
# Find every /100 related to price across the codebase
grep -rn "/ 100\|/100" app/ resources/ database/ --include="*.php" --include="*.blade.php" | grep -v ".git" | grep -v "vendor"

# Find every *100 related to price
grep -rn "\* 100\|*100" app/ resources/ database/ --include="*.php" --include="*.blade.php" | grep -v ".git" | grep -v "vendor"
```

Read the full output. Every occurrence must be addressed in this task.

---

## Step 1 — Database Migration: Update Column Comments

Create `database/migrations/TIMESTAMP_update_price_columns_to_rwf.php`:

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
        // Update column comments only — data was already re-seeded as real RWF
        // If you have existing cent data: UPDATE products SET purchase_price = purchase_price / 100, etc.
        
        DB::statement("COMMENT ON COLUMN products.purchase_price IS 'Price in RWF (whole number)'");
        DB::statement("COMMENT ON COLUMN products.selling_price IS 'Price per item in RWF (whole number)'");
        DB::statement("COMMENT ON COLUMN products.box_selling_price IS 'Full box price in RWF (whole number)'");
        DB::statement("COMMENT ON COLUMN sales.subtotal IS 'Amount in RWF'");
        DB::statement("COMMENT ON COLUMN sales.tax IS 'Tax amount in RWF'");
        DB::statement("COMMENT ON COLUMN sales.discount IS 'Discount amount in RWF'");
        DB::statement("COMMENT ON COLUMN sales.total IS 'Total amount in RWF'");
        DB::statement("COMMENT ON COLUMN sale_items.original_unit_price IS 'Price in RWF'");
        DB::statement("COMMENT ON COLUMN sale_items.actual_unit_price IS 'Price in RWF'");
        DB::statement("COMMENT ON COLUMN sale_items.line_total IS 'Amount in RWF'");
    }

    public function down(): void
    {
        // No destructive changes
    }
};
```

---

## Step 2 — `app/Models/Product.php`

Remove all `/100` accessors and `*100` mutators. Keep `calculateBoxPrice()` but fix it.

**Find and REMOVE these methods entirely:**
```php
// DELETE these accessors:
public function getPurchasePriceInDollarsAttribute(): float { ... }
public function getSellingPriceInDollarsAttribute(): float { ... }
public function getBoxSellingPriceInDollarsAttribute(): float { ... }

// DELETE these mutators:
public function setPurchasePriceInDollarsAttribute($value): void { ... }
public function setSellingPriceInDollarsAttribute($value): void { ... }
```

**Fix `calculateBoxPrice()`** — currently returns `$this->selling_price * $this->items_per_box` which is correct for RWF. Verify it does NOT divide:
```php
public function calculateBoxPrice(): int
{
    return $this->box_selling_price ?? ($this->selling_price * $this->items_per_box);
}
```

**Remove from `$casts`** — prices are stored as plain integers now, keep casts but remove `'integer'` if they are declared as `unsignedBigInteger` — actually keep integer casts, they are fine.

**Remove the `// Pricing (in cents...)` doc comment** and replace with:
```php
// Pricing (in RWF — whole number, no cents)
```

---

## Step 3 — `app/Models/Sale.php`

**Remove these accessors entirely:**
```php
public function getSubtotalInDollarsAttribute(): float { return $this->subtotal / 100; }
public function getTaxInDollarsAttribute(): float { return $this->tax / 100; }
public function getDiscountInDollarsAttribute(): float { return $this->discount / 100; }
public function getTotalInDollarsAttribute(): float { return $this->total / 100; }
```

---

## Step 4 — `app/Models/SaleItem.php`

**Remove these accessors entirely:**
```php
public function getOriginalUnitPriceInDollarsAttribute(): float { return $this->original_unit_price / 100; }
public function getActualUnitPriceInDollarsAttribute(): float { return $this->actual_unit_price / 100; }
public function getLineTotalInDollarsAttribute(): float { return $this->line_total / 100; }
```

The helper methods are fine — they work on raw values which are now direct RWF:
```php
public function getPriceDiscountAmount(): int { ... }     // keep
public function getPriceDiscountPercentage(): float { ... } // keep
```

---

## Step 5 — `app/Livewire/Dashboard/BusinessKpiRow.php`

Remove every `/ 100` from the `loadData()` method.

**Find all these patterns and remove `/ 100`:**

```php
// BEFORE:
$current  = Sale::notVoided()->whereBetween(...)->sum('total') / 100;
$previous = Sale::notVoided()->whereBetween(...)->sum('total') / 100;
$todayRev = Sale::notVoided()->whereDate(...)->sum('total') / 100;
$weekRev  = Sale::notVoided()->whereBetween(...)->sum('total') / 100;
$monthRev = Sale::notVoided()->whereBetween(...)->sum('total') / 100;
$margin   = (SaleItem::join(...)...->value('margin') ?? 0) / 100;
$todayMargin = (...->value('margin') ?? 0) / 100;
$weekMargin  = (...->value('margin') ?? 0) / 100;
$monthMargin = (...->value('margin') ?? 0) / 100;
$cost   = ($inv->cost_value   ?? 0) / 100;
$retail = ($inv->retail_value ?? 0) / 100;
$whRetail   = (...->value('v') ?? 0) / 100;
$shopRetail = (...->value('v') ?? 0) / 100;

// AFTER — remove the / 100 from every one:
$current  = Sale::notVoided()->whereBetween(...)->sum('total');
$previous = Sale::notVoided()->whereBetween(...)->sum('total');
$todayRev = Sale::notVoided()->whereDate(...)->sum('total');
$weekRev  = Sale::notVoided()->whereBetween(...)->sum('total');
$monthRev = Sale::notVoided()->whereBetween(...)->sum('total');
$margin   = SaleItem::join(...)...->value('margin') ?? 0;
$todayMargin = ...->value('margin') ?? 0;
$weekMargin  = ...->value('margin') ?? 0;
$monthMargin = ...->value('margin') ?? 0;
$cost   = $inv->cost_value   ?? 0;
$retail = $inv->retail_value ?? 0;
$whRetail   = ...->value('v') ?? 0;
$shopRetail = ...->value('v') ?? 0;
```

---

## Step 6 — `app/Livewire/Dashboard/TopShops.php`

```php
// BEFORE:
'revenue' => ($shop->revenue ?? 0) / 100,

// AFTER:
'revenue' => $shop->revenue ?? 0,
```

---

## Step 7 — `app/Livewire/Dashboard/KpiRow.php` (if it exists)

```php
// BEFORE:
'value' => $todayRevenue / 100,
'delta' => $delta / 100,

// AFTER:
'value' => $todayRevenue,
'delta' => $delta,
```

Search for any other `/ 100` in this file and remove them.

---

## Step 8 — `app/Livewire/Dashboard/OpsKpiRow.php`

Search for `/ 100` and remove. Run:
```bash
grep -n "/ 100\|/100" app/Livewire/Dashboard/OpsKpiRow.php
```
Remove every occurrence that relates to prices/revenue.

---

## Step 9 — `app/Livewire/Dashboard/SalesPerformance.php`

```bash
grep -n "/ 100\|/100" app/Livewire/Dashboard/SalesPerformance.php
```
Remove every `/ 100` from revenue/sales aggregations.

---

## Step 10 — `app/Livewire/Shop/Sales/PointOfSale.php`

This file has the most changes. Read it fully first:
```bash
cat app/Livewire/Shop/Sales/PointOfSale.php
```

**Changes to make:**

### `openCheckout()` method
```php
// BEFORE:
$this->amountReceived = $this->cartTotal / 100;

// AFTER:
$this->amountReceived = $this->cartTotal;
```

### `updatedAmountReceived()` method
```php
// BEFORE:
$this->changeAmount = max(0, $this->amountReceived - ($this->cartTotal / 100));

// AFTER:
$this->changeAmount = max(0, $this->amountReceived - $this->cartTotal);
```

### `applyPriceModification()` method
```php
// BEFORE:
$newPriceCents = $this->newPrice * 100;
$originalPrice = $this->cart[$index]['original_price'];
$percentageChange = (($originalPrice - $newPriceCents) / $originalPrice) * 100;
$requiresApproval = $percentageChange > 20;
$this->cart[$index]['price'] = $newPriceCents;
$this->cart[$index]['line_total'] = $newPriceCents * $this->cart[$index]['quantity'];

// AFTER:
$newPrice      = (int) $this->newPrice;   // already RWF
$originalPrice = $this->cart[$index]['original_price'];
$percentageChange = $originalPrice > 0
    ? (($originalPrice - $newPrice) / $originalPrice) * 100
    : 0;
$requiresApproval = $percentageChange > 20;
$this->cart[$index]['price']      = $newPrice;
$this->cart[$index]['line_total'] = $newPrice * $this->cart[$index]['quantity'];
```

### `completeSale()` — tax and discount
```php
// BEFORE:
'tax'      => (int) round($this->tax * 100),
'discount' => (int) round($this->discount * 100),
// ... repeated in completedSale array

// AFTER:
'tax'      => (int) $this->tax,
'discount' => (int) $this->discount,
```

### `confirmAddToCart()` — staging price (the stagingPrice is entered by user as RWF):
```php
// stagingPrice is already stored as RWF integer — no * 100 or / 100 needed.
// The line_total is: $this->stagingPrice * $this->stagingQty  (both already RWF)
// No changes needed IF no * 100 or / 100 exists here — verify by reading the method.
```

### `openAddModal()` / `openEditItem()` — check for any `/ 100` when loading stagingPrice:
```php
// BEFORE (if present):
$this->stagingPrice = $item['price'] / 100;
$this->stagingPrice = $product->selling_price / 100;
$this->stagingPrice = $product->calculateBoxPrice() / 100;

// AFTER:
$this->stagingPrice = $item['price'];
$this->stagingPrice = $product->selling_price;
$this->stagingPrice = $product->calculateBoxPrice();
```

### `calculateCartTotal()`:
```php
// cartTotal is sum of line_totals — already RWF, no change needed.
// BUT verify there is no / 100 here.
```

---

## Step 11 — `app/Livewire/Sales/PointOfSale.php` (old component, if still used)

```bash
grep -n "/ 100\|/100\|* 100\|*100" app/Livewire/Sales/PointOfSale.php 2>/dev/null
```

Apply same pattern:
- `$this->subtotal + $taxCents - $discountCents` where `$taxCents = (int) round($this->tax * 100)` → change to `$this->subtotal + $this->tax - $this->discount`
- Remove any `/ 100` from display values
- Remove `* 100` from tax/discount when passing to service

---

## Step 12 — `app/Livewire/Owner/Products/CreateProduct.php`

```php
// In save() method — BEFORE:
'purchase_price'   => round($this->purchasePrice * 100),
'selling_price'    => round($this->sellingPrice * 100),
'box_selling_price'=> $this->boxSellingPrice ? round($this->boxSellingPrice * 100) : null,

// AFTER (store as RWF directly):
'purchase_price'   => (int) $this->purchasePrice,
'selling_price'    => (int) $this->sellingPrice,
'box_selling_price'=> $this->boxSellingPrice ? (int) $this->boxSellingPrice : null,
```

Also in `mount()` — if there is any `/ 100` when initialising form fields, remove it:
```php
// BEFORE (if present):
$this->purchasePrice = $product->purchase_price / 100;

// AFTER:
$this->purchasePrice = $product->purchase_price;
```

---

## Step 13 — `app/Livewire/Owner/Products/EditProduct.php`

Same as CreateProduct:

```php
// In mount() — BEFORE:
$this->purchasePrice   = $product->purchase_price > 0 ? (string) ($product->purchase_price / 100) : '';
$this->sellingPrice    = $product->selling_price > 0  ? (string) ($product->selling_price / 100)  : '';
$this->boxSellingPrice = $product->box_selling_price  ? (string) ($product->box_selling_price / 100) : '';

// AFTER:
$this->purchasePrice   = $product->purchase_price > 0 ? (string) $product->purchase_price : '';
$this->sellingPrice    = $product->selling_price > 0  ? (string) $product->selling_price  : '';
$this->boxSellingPrice = $product->box_selling_price  ? (string) $product->box_selling_price : '';

// In update() — BEFORE:
'purchase_price'    => (int) round((float) $this->purchasePrice * 100),
'selling_price'     => (int) round((float) $this->sellingPrice * 100),
'box_selling_price' => $this->boxSellingPrice ? (int) round((float) $this->boxSellingPrice * 100) : null,

// AFTER:
'purchase_price'    => (int) $this->purchasePrice,
'selling_price'     => (int) $this->sellingPrice,
'box_selling_price' => $this->boxSellingPrice ? (int) $this->boxSellingPrice : null,
```

---

## Step 14 — All Blade Views: Remove `/100`

Run this to find all blade occurrences:
```bash
grep -rn "/ 100\|/100" resources/views/ --include="*.blade.php"
```

For every match, remove the `/ 100`. The value is already in RWF.

### `resources/views/livewire/owner/products/product-detail.blade.php`

```blade
{{-- BEFORE: --}}
{{ number_format($product->purchase_price / 100) }}
{{ number_format($product->selling_price / 100) }}
{{ number_format($product->box_selling_price / 100) }}
{{-- Margin calc: --}}
round(($product->selling_price - $product->purchase_price) / $product->selling_price * 100, 1)

{{-- AFTER: --}}
{{ number_format($product->purchase_price) }}
{{ number_format($product->selling_price) }}
{{ number_format($product->box_selling_price) }}
{{-- Margin calc unchanged — it's a percentage calc using the same units: --}}
round(($product->selling_price - $product->purchase_price) / $product->selling_price * 100, 1)
```

### `resources/views/livewire/shop/stock-levels.blade.php`

```blade
{{-- BEFORE: --}}
{{ number_format($data['product']->selling_price / 100) }}

{{-- AFTER: --}}
{{ number_format($data['product']->selling_price) }}
```

### `resources/views/livewire/shop/sales/point-of-sale.blade.php`

Every occurrence of `/ 100` in this file:

```blade
{{-- BEFORE: --}}
{{ number_format($cartTotal / 100) }}
{{ number_format($item->line_total / 100) }}
{{ number_format($item->actual_unit_price / 100) }}
{{ number_format($completedSale->subtotal / 100) }}
{{ number_format($completedSale->tax / 100) }}
{{ number_format($completedSale->discount / 100) }}
{{ number_format($completedSale->total / 100) }}
{{ number_format($completedSale['total'] ?? 0) / 100) }}

{{-- AFTER — remove all / 100: --}}
{{ number_format($cartTotal) }}
{{ number_format($item->line_total) }}
{{ number_format($item->actual_unit_price) }}
{{ number_format($completedSale->subtotal) }}
{{ number_format($completedSale->tax) }}
{{ number_format($completedSale->discount) }}
{{ number_format($completedSale->total) }}
{{ number_format($completedSale['total'] ?? 0) }}
```

### `resources/views/livewire/sales/point-of-sale.blade.php` (old blade)

```blade
{{-- BEFORE: --}}
{{ number_format(($item['quantity'] * $item['final_price']) / 100) }}
{{ number_format($this->total / 100) }}

{{-- AFTER: --}}
{{ number_format($item['quantity'] * $item['final_price']) }}
{{ number_format($this->total) }}
```

### `resources/views/livewire/owner/reports/sales-analytics.blade.php`

```blade
{{-- BEFORE: --}}
RWF {{ number_format($this->revenueKpis['avg_transaction_value'] / 100, 0) }}

{{-- AFTER: --}}
RWF {{ number_format($this->revenueKpis['avg_transaction_value'], 0) }}
```

### Dashboard blades

```bash
grep -rn "/ 100\|/100" resources/views/livewire/dashboard/ --include="*.blade.php"
```

Remove `/ 100` from every hit. The values passed from PHP are already RWF after Step 5–9.

---

## Step 15 — `database/seeders/RwandaShoeBusinessSeeder.php`

The seeder should already store RWF directly (e.g. `55000` not `5500000`).

Verify:
```bash
grep -n "purchase_price\|selling_price\|box_selling_price\|line_total\|subtotal\|total\b" database/seeders/RwandaShoeBusinessSeeder.php | head -30
```

Prices in the seeder should be:
- Nike AF1 sell: `55000` ✓ (not `5500000`)  
- School shoe sell: `20000` ✓  
- Socks 3-pack sell: `2500` ✓  

If any prices are still in the old `×100` format, divide them by 100.

Also verify sale creation:
```php
// Tax calculation in seedSales():
$tax = $addTax ? (int)round($subtotal * 0.18) : 0;  // ✓ correct — subtotal is RWF

// line_total:
$lineTotal = $actualPrice * $qtySold;  // ✓ correct — both already RWF
```

---

## Step 16 — `app/Livewire/Owner/Reports/SalesAnalytics.php` (if exists)

```bash
cat app/Livewire/Owner/Reports/SalesAnalytics.php 2>/dev/null | grep -n "/ 100\|/100"
```

Remove all `/ 100` from revenue, avg transaction, and profit calculations.

---

## Step 17 — `app/Services/Sales/SaleService.php`

This service works with raw values from the database — it does NOT divide or multiply by 100.  
**Verify it does NOT have any `/100` or `*100` for prices.** If it does, remove them.

```bash
grep -n "/ 100\|* 100" app/Services/Sales/SaleService.php
```

The service receives `$itemData['price']` from the POS component which is now raw RWF, stores it directly — no conversion needed.

---

## Step 18 — Any other Livewire components

```bash
grep -rn "/ 100\|/100" app/Livewire/ --include="*.php" | grep -v ".git"
```

For each hit, evaluate: is this dividing a price field by 100 for display? If yes, remove the `/ 100`. The value is already RWF.

Common files to check:
- `app/Livewire/Inventory/Boxes/ReceiveBoxes.php`
- `app/Livewire/Shop/Transfers/*.php`
- `app/Livewire/Owner/Transfers/*.php`
- `app/Livewire/Owner/Products/ProductList.php`

---

## Step 19 — Clear caches and verify

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear

# Re-seed with correct RWF data
php artisan migrate:fresh
php artisan db:seed --class=RwandaShoeBusinessSeeder

# Verify prices look correct
php artisan tinker --execute="
\$p = App\Models\Product::where('sku', 'NK-AF1-42')->first();
echo 'Selling price: RWF ' . number_format(\$p->selling_price) . PHP_EOL;
echo 'Purchase price: RWF ' . number_format(\$p->purchase_price) . PHP_EOL;
echo 'Box price: RWF ' . number_format(\$p->box_selling_price) . PHP_EOL;

\$sale = App\Models\Sale::latest()->first();
echo 'Latest sale total: RWF ' . number_format(\$sale->total) . PHP_EOL;
echo 'Latest sale subtotal: RWF ' . number_format(\$sale->subtotal) . PHP_EOL;
"
```

Expected output:
```
Selling price: RWF 55,000
Purchase price: RWF 35,000
Box price: RWF 315,000
Latest sale total: RWF [reasonable RWF amount, e.g. 165,000]
```

If you see numbers like `55,000,000` — prices are still in cents somewhere. If you see `550` — division is still happening.

---

## Complete Checklist

| File | Change | Done |
|------|--------|------|
| Migration | Add RWF comment | ☐ |
| `Product` model | Remove `/100` accessors + `*100` mutators | ☐ |
| `Sale` model | Remove `/100` accessors | ☐ |
| `SaleItem` model | Remove `/100` accessors | ☐ |
| `BusinessKpiRow.php` | Remove all `/ 100` from loadData() | ☐ |
| `TopShops.php` | Remove `/ 100` from revenue | ☐ |
| `KpiRow.php` | Remove `/ 100` from revenue | ☐ |
| `OpsKpiRow.php` | Remove any `/ 100` | ☐ |
| `SalesPerformance.php` | Remove any `/ 100` | ☐ |
| `Shop/Sales/PointOfSale.php` | Fix amountReceived, changeAmount, stagingPrice, tax/discount | ☐ |
| `Sales/PointOfSale.php` (old) | Remove `* 100` and `/ 100` | ☐ |
| `CreateProduct.php` | Remove `* 100` on save | ☐ |
| `EditProduct.php` | Remove `/ 100` on mount, `* 100` on save | ☐ |
| `product-detail.blade.php` | Remove `/ 100` from all prices | ☐ |
| `stock-levels.blade.php` | Remove `/ 100` | ☐ |
| `point-of-sale.blade.php` (shop) | Remove all `/ 100` | ☐ |
| `point-of-sale.blade.php` (old) | Remove all `/ 100` | ☐ |
| `sales-analytics.blade.php` | Remove `/ 100` | ☐ |
| Dashboard blades | Remove all `/ 100` | ☐ |
| `RwandaShoeBusinessSeeder.php` | Verify prices are plain RWF (not ×100) | ☐ |
| `SaleService.php` | Verify no `/ 100` or `* 100` | ☐ |
| All other Livewire PHP | Grep and fix remaining hits | ☐ |
