# SmartInventory — Fix Inventory Valuation (Box Price ÷ items_per_box)
## Claude Code Instructions

> Drop in project root. Tell Claude Code:
> "Read FIX_BOX_PRICE_VALUATION.md and follow every step in order."

---

## The Bug

`selling_price` and `purchase_price` on the `products` table are **BOX prices**.
`boxes.items_remaining` is an **item count** (e.g. 120 items = 20 full boxes of 6).

Every valuation query does:
```sql
SUM(boxes.items_remaining * products.selling_price)
-- = 120 items × 55,000 box price = 6,600,000  ← WRONG (6× too high)
```

The correct calculation is:
```sql
SUM(boxes.items_remaining * products.selling_price / products.items_per_box)
-- = 120 items × 55,000 / 6 = 1,100,000  ← CORRECT (20 boxes × 55,000)
```

**Proof from real data:**
- Correct retail value:  131,194,500 RWF  (owner's spreadsheet: box_price × boxes)
- Dashboard shows:       905,778,000 RWF  (inflated by avg items_per_box ≈ 6.9×)
- Correct cost value:     52,477,800 RWF
- Dashboard shows:       362,311,200 RWF  (inflated by same factor)

---

## Step 0 — Read the files first

```bash
grep -n "items_remaining.*selling_price\|selling_price.*items_remaining\|items_remaining.*purchase_price\|purchase_price.*items_remaining" \
  app/Http/Controllers/Owner/DashboardController.php \
  app/Livewire/Dashboard/BusinessKpiRow.php \
  app/Services/Analytics/InventoryAnalyticsService.php
```

---

## Step 1 — Fix `app/Http/Controllers/Owner/DashboardController.php`

### 1a — Inventory valuation block

Find:
```php
$inv = Box::available()
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->selectRaw('
        SUM(boxes.items_remaining * products.purchase_price) AS cost_value,
        SUM(boxes.items_remaining * products.selling_price)  AS retail_value,
        SUM(boxes.items_remaining)                           AS total_items
    ')
    ->first();
```

Replace with:
```php
$inv = Box::available()
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->selectRaw('
        SUM(boxes.items_remaining * products.purchase_price / products.items_per_box) AS cost_value,
        SUM(boxes.items_remaining * products.selling_price  / products.items_per_box) AS retail_value,
        SUM(boxes.items_remaining)                                                     AS total_items
    ')
    ->first();
```

### 1b — Remove the remaining `/100` divisions immediately after

Find:
```php
$stats['inventory_value']  = ($inv->cost_value   ?? 0) / 100;
$stats['retail_value']     = ($inv->retail_value ?? 0) / 100;
```

Replace with:
```php
$stats['inventory_value']  = (int) ($inv->cost_value   ?? 0);
$stats['retail_value']     = (int) ($inv->retail_value ?? 0);
```

### 1c — Sales chart loop — remove `/100`

Find all occurrences of `->sum('total') / 100` in this file and remove the `/ 100`:
```bash
grep -n "sum('total') / 100\|sum(\"total\") / 100" app/Http/Controllers/Owner/DashboardController.php
```

For every hit, remove `/ 100`. Sales totals are already stored in RWF.

---

## Step 2 — Fix `app/Livewire/Dashboard/BusinessKpiRow.php`

### 2a — Main inventory valuation query

Find:
```php
$inv = Box::join('products', 'boxes.product_id', '=', 'products.id')
    ->where('boxes.status', '!=', 'disposed')
    ->selectRaw('SUM(boxes.items_remaining * products.purchase_price)  as cost_value,
                 SUM(boxes.items_remaining * products.selling_price)   as retail_value')
    ->first();
```

Replace with:
```php
$inv = Box::join('products', 'boxes.product_id', '=', 'products.id')
    ->where('boxes.status', '!=', 'disposed')
    ->selectRaw('
        SUM(boxes.items_remaining * products.purchase_price / products.items_per_box) as cost_value,
        SUM(boxes.items_remaining * products.selling_price  / products.items_per_box) as retail_value
    ')
    ->first();
```

### 2b — Warehouse retail and shop retail queries

Find (two separate queries):
```php
$whRetail = (Box::available()
    ->where('boxes.location_type', 'warehouse')
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->selectRaw('SUM(boxes.items_remaining * products.selling_price) AS v')
    ->value('v') ?? 0) / 100;

$shopRetail = (Box::available()
    ->where('boxes.location_type', 'shop')
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->selectRaw('SUM(boxes.items_remaining * products.selling_price) AS v')
    ->value('v') ?? 0) / 100;
```

Replace with:
```php
$whRetail = (int) (Box::available()
    ->where('boxes.location_type', 'warehouse')
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->selectRaw('SUM(boxes.items_remaining * products.selling_price / products.items_per_box) AS v')
    ->value('v') ?? 0);

$shopRetail = (int) (Box::available()
    ->where('boxes.location_type', 'shop')
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->selectRaw('SUM(boxes.items_remaining * products.selling_price / products.items_per_box) AS v')
    ->value('v') ?? 0);
```

### 2c — Cost and retail in the main inventory block

Find:
```php
$cost   = ($inv->cost_value   ?? 0) / 100;
$retail = ($inv->retail_value ?? 0) / 100;
```

Replace with:
```php
$cost   = (int) ($inv->cost_value   ?? 0);
$retail = (int) ($inv->retail_value ?? 0);
```

### 2d — Revenue / profit / sales — remove all remaining `/100`

```bash
grep -n "/ 100\|/100" app/Livewire/Dashboard/BusinessKpiRow.php
```

For every remaining hit in this file, remove the `/ 100`. Sales totals are in RWF.

---

## Step 3 — Fix `app/Services/Analytics/InventoryAnalyticsService.php`

### 3a — Main KPI query

Find:
```php
->selectRaw('
    SUM(boxes.items_remaining * products.purchase_price) as purchase_value,
    SUM(boxes.items_remaining * products.selling_price) as retail_value,
    COUNT(DISTINCT boxes.product_id) as product_count
');
```

Replace with:
```php
->selectRaw('
    SUM(boxes.items_remaining * products.purchase_price / products.items_per_box) as purchase_value,
    SUM(boxes.items_remaining * products.selling_price  / products.items_per_box) as retail_value,
    COUNT(DISTINCT boxes.product_id) as product_count
');
```

### 3b — Location breakdown queries (warehouses and shops)

Find both occurrences of:
```php
->selectRaw('
    ...
    SUM(boxes.items_remaining * products.purchase_price) as value,
    ...
')
```

Replace each with:
```php
->selectRaw('
    ...
    SUM(boxes.items_remaining * products.purchase_price / products.items_per_box) as value,
    ...
')
```

---

## Step 4 — Fix `app/Http/Controllers/ShopManager/DashboardController.php`

Find all `->sum('total') / 100` occurrences and remove `/ 100`:

```bash
grep -n "sum('total') / 100" app/Http/Controllers/ShopManager/DashboardController.php
```

Remove every `/ 100` — sales totals are stored in RWF directly.

---

## Step 5 — Search for any other valuation queries

```bash
grep -rn "items_remaining.*selling_price\|items_remaining.*purchase_price" app/ --include="*.php"
```

For every hit found, apply the same fix: add `/ products.items_per_box` after the price column.

Also check for any remaining `/100` on revenue/price fields:
```bash
grep -rn "/ 100\b" app/Http/Controllers/ app/Livewire/Dashboard/ app/Services/Analytics/ --include="*.php"
```

Remove every hit that is dividing a price or revenue value by 100.

---

## Step 6 — Clear caches and verify

```bash
php artisan view:clear && php artisan cache:clear && php artisan config:clear
```

Then verify with tinker:
```bash
php artisan tinker --execute="
use App\Models\Box;

\$inv = Box::available()
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->selectRaw('
        SUM(boxes.items_remaining * products.purchase_price / products.items_per_box) as cost_value,
        SUM(boxes.items_remaining * products.selling_price  / products.items_per_box) as retail_value,
        SUM(boxes.items_remaining) as total_items
    ')
    ->first();

echo 'Cost value:   RWF ' . number_format(\$inv->cost_value)   . PHP_EOL;
echo 'Retail value: RWF ' . number_format(\$inv->retail_value) . PHP_EOL;
echo 'Total items:  '     . number_format(\$inv->total_items)  . PHP_EOL;
"
```

**Expected results (matching the owner's spreadsheet):**
```
Cost value:   RWF 52,477,800
Retail value: RWF 131,194,500
Total items:  48,996
```

If you still see ~362 million or ~905 million → the `/ products.items_per_box` was not applied.
If you see numbers that are 100× too small → a `/100` was left in unexpectedly.

---

## Summary

| File | Fix |
|---|---|
| `DashboardController.php` (Owner) | Add `/ products.items_per_box` to both price columns in valuation SQL; remove `/100` from sales |
| `BusinessKpiRow.php` | Same fix in 3 separate queries (main + warehouse + shop breakdown); remove remaining `/100` |
| `InventoryAnalyticsService.php` | Same fix in main KPI query + location breakdown queries |
| `DashboardController.php` (ShopManager) | Remove `/100` from sales sum calls |

**Do NOT change:** `boxes.items_remaining` stays as-is. `products.items_per_box` stays as-is. Only add `/ products.items_per_box` after the price column in every SUM.
