# Fix Profit Margin Bug — 560% → Correct %
## Claude Code Instructions

> Drop in project root. Tell Claude Code:
> "Read FIX_PROFIT_MARGIN_BUG.md and follow every step in order."

---

## The Bug

The Profit card in the Business KPI Row shows **560%** margin — mathematically
impossible (profit > revenue). The root cause is a **unit mismatch** in the
margin SQL inside `app/Livewire/Dashboard/BusinessKpiRow.php`.

### How the data is stored

| Column | What it holds | Full-box sale example |
|--------|--------------|----------------------|
| `sale_items.actual_unit_price` | **BOX price** when `is_full_box = true`, **item price** when selling individually | 55,000 (full box) / 9,167 (single item) |
| `sale_items.quantity_sold` | Always **item count** (not box count) | 6 (for 1 box of 6) |
| `sale_items.line_total` | Always correct total revenue for that row | 55,000 |
| `products.purchase_price` | **Per-item cost** (since FIX_ITEM_PRICES migration) | 5,833 |

### What the current SQL computes (wrong)

```sql
SUM((sale_items.actual_unit_price - products.purchase_price) * sale_items.quantity_sold)
-- = (55,000 - 5,833) × 6
-- = 49,167 × 6
-- = 295,002   ← inflated by items_per_box factor
```

For a single-box sale of 55,000 RWF, this produces **295,002 margin** against
**55,000 revenue** = 536% margin. Nonsense.

### What it should compute

```
Margin = Revenue from sale_item − Cost of items sold
       = line_total − (purchase_price × quantity_sold)
       = 55,000 − (5,833 × 6)
       = 55,000 − 34,998
       = 20,002   ← correct
```

This works for **both** full-box and individual-item sales because `line_total`
is always the correct revenue regardless of sell mode.

---

## Step 1 — Diagnose first (run these in tinker)

```bash
php artisan tinker --execute="
// Check a recent sale_item to confirm the mismatch
\$si = App\Models\SaleItem::with('product')
    ->where('is_full_box', true)
    ->latest()
    ->first();

if (\$si) {
    echo '=== Full-box sale_item ===' . PHP_EOL;
    echo 'actual_unit_price : ' . number_format(\$si->actual_unit_price) . ' RWF' . PHP_EOL;
    echo 'quantity_sold     : ' . \$si->quantity_sold . ' items' . PHP_EOL;
    echo 'line_total        : ' . number_format(\$si->line_total) . ' RWF' . PHP_EOL;
    echo 'purchase_price    : ' . number_format(\$si->product->purchase_price) . ' RWF per item' . PHP_EOL;
    echo 'items_per_box     : ' . \$si->product->items_per_box . PHP_EOL;
    echo PHP_EOL;
    echo '--- Current (wrong) margin ---' . PHP_EOL;
    \$wrongMargin = (\$si->actual_unit_price - \$si->product->purchase_price) * \$si->quantity_sold;
    echo number_format(\$wrongMargin) . ' RWF' . PHP_EOL;
    echo PHP_EOL;
    echo '--- Correct margin ---' . PHP_EOL;
    \$correctMargin = \$si->line_total - (\$si->product->purchase_price * \$si->quantity_sold);
    echo number_format(\$correctMargin) . ' RWF' . PHP_EOL;
    echo 'Correct margin % of line_total: ' . round(\$correctMargin / \$si->line_total * 100, 1) . '%' . PHP_EOL;
} else {
    echo 'No full-box sale items found' . PHP_EOL;
}
"
```

The output should confirm that `actual_unit_price` on a full-box sale equals
the box price (e.g. 55,000) while `purchase_price` is a per-item cost (e.g. 5,833).

---

## Step 2 — Fix `app/Livewire/Dashboard/BusinessKpiRow.php`

There are **four margin queries** in `loadData()` — the period margin + three
sub-row reference points (today, week, month). Fix all four with the same change.

### Find and replace — period margin

**BEFORE:**
```php
$margin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->whereNull('sales.voided_at')
    ->whereBetween('sales.sale_date', [$start, $end])
    ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                    * sale_items.quantity_sold) as margin')
    ->value('margin') ?? 0);
```

**AFTER:**
```php
$margin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->whereNull('sales.voided_at')
    ->whereBetween('sales.sale_date', [$start, $end])
    ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
    ->value('margin') ?? 0);
```

### Find and replace — $todayMargin

**BEFORE:**
```php
$todayMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->whereNull('sales.voided_at')
    ->whereDate('sales.sale_date', today())
    ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                    * sale_items.quantity_sold) as margin')
    ->value('margin') ?? 0);
```

**AFTER:**
```php
$todayMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->whereNull('sales.voided_at')
    ->whereDate('sales.sale_date', today())
    ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
    ->value('margin') ?? 0);
```

### Find and replace — $weekMargin

**BEFORE:**
```php
$weekMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->whereNull('sales.voided_at')
    ->whereBetween('sales.sale_date', [now()->startOfWeek(), now()])
    ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                    * sale_items.quantity_sold) as margin')
    ->value('margin') ?? 0);
```

**AFTER:**
```php
$weekMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->whereNull('sales.voided_at')
    ->whereBetween('sales.sale_date', [now()->startOfWeek(), now()])
    ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
    ->value('margin') ?? 0);
```

### Find and replace — $monthMargin

**BEFORE:**
```php
$monthMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->whereNull('sales.voided_at')
    ->whereBetween('sales.sale_date', [now()->startOfMonth(), now()])
    ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                    * sale_items.quantity_sold) as margin')
    ->value('margin') ?? 0);
```

**AFTER:**
```php
$monthMargin = (SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->whereNull('sales.voided_at')
    ->whereBetween('sales.sale_date', [now()->startOfMonth(), now()])
    ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
    ->value('margin') ?? 0);
```

---

## Step 3 — Check for same bug in Sales Analytics report

```bash
grep -rn "actual_unit_price - products.purchase_price" app/ --include="*.php"
```

If any hits appear outside of `BusinessKpiRow.php` (e.g. in `SalesAnalytics.php`,
`TopShops.php`, any other Livewire component or controller), apply the same fix
to each one:

**Wrong pattern to replace everywhere:**
```sql
SUM((sale_items.actual_unit_price - products.purchase_price) * sale_items.quantity_sold)
```

**Correct pattern to use everywhere:**
```sql
SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold))
```

---

## Step 4 — Verify the fix with tinker

```bash
php artisan tinker --execute="
use App\Models\SaleItem;
use Carbon\Carbon;

// Run the corrected margin query for the current month
\$margin = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->whereNull('sales.voided_at')
    ->whereBetween('sales.sale_date', [now()->startOfMonth(), now()])
    ->selectRaw('SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as margin')
    ->value('margin') ?? 0;

\$revenue = App\Models\Sale::notVoided()
    ->whereBetween('sale_date', [now()->startOfMonth(), now()])
    ->sum('total');

echo 'Month revenue : RWF ' . number_format(\$revenue) . PHP_EOL;
echo 'Month margin  : RWF ' . number_format(\$margin) . PHP_EOL;
echo 'Margin %      : ' . (\$revenue > 0 ? round(\$margin / \$revenue * 100, 1) : 0) . '%' . PHP_EOL;
"
```

**Expected result:** Margin should be between 20%–45% for a shoe retail business.
If margin % is still > 100%, check that `purchase_price` values in the `products`
table are correct per-item RWF amounts (run Step 5).

---

## Step 5 — Sanity check product prices (only if Step 4 still looks wrong)

```bash
php artisan tinker --execute="
App\Models\Product::with('boxes')->get()->each(function(\$p) {
    \$totalItems = \$p->boxes->sum('items_remaining');
    echo \$p->name . PHP_EOL;
    echo '  purchase_price : RWF ' . number_format(\$p->purchase_price) . ' per item' . PHP_EOL;
    echo '  selling_price  : RWF ' . number_format(\$p->selling_price)  . ' per item' . PHP_EOL;
    echo '  items_per_box  : ' . \$p->items_per_box . PHP_EOL;
    echo '  box_cost       : RWF ' . number_format(\$p->purchase_price * \$p->items_per_box) . PHP_EOL;
    echo '  box_sell       : RWF ' . number_format(\$p->calculateBoxPrice()) . PHP_EOL;
    echo '  gross_margin % : ' . round((\$p->selling_price - \$p->purchase_price) / \$p->selling_price * 100, 1) . '%' . PHP_EOL;
    echo PHP_EOL;
});
"
```

For a shoe business in Rwanda, expect:
- `purchase_price` in range 3,000–30,000 RWF per pair
- `selling_price` in range 5,000–60,000 RWF per pair
- Gross margin between 25%–50%

If you see purchase_price values like `35000000` (too large) or `35` (too small),
the purchase_price column still has a unit problem — raise this as a separate task.

---

## Step 6 — Clear view cache and confirm in browser

```bash
php artisan view:clear
php artisan cache:clear
```

Reload the Owner Dashboard. The Profit card badge should now show a realistic
percentage (typically 25–45% for shoe retail, matching gross margin expectations).

---

## Summary

| Location | Change |
|----------|--------|
| `app/Livewire/Dashboard/BusinessKpiRow.php` | Replace margin SQL ×4 (period, today, week, month) |
| Any other file with the same wrong SQL | Same replacement |
| No schema changes, no migrations, no seeder changes needed | — |

**Root cause in one line:**  
`actual_unit_price` stores the **box price** for full-box sales, but the old SQL
multiplied it by `quantity_sold` (item count), inflating margin by `items_per_box`×.
Using `line_total` instead eliminates the ambiguity — it is always the correct
revenue figure for that sale_item row regardless of sell mode.
