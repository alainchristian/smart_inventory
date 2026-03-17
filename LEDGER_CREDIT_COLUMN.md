# Sales Analytics Ledger — Add Credit Column & Strip Card
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read LEDGER_CREDIT_COLUMN.md and follow every step in order."

---

## Read these files first — do not write any code yet

```bash
cat app/Services/Analytics/SalesAnalyticsService.php
cat resources/views/livewire/owner/reports/sales-analytics.blade.php
```

Focus on:
1. The `getTopProducts()` method in the service — note the existing `selectRaw` columns
2. The `TAB: SALES LEDGER` section in the blade — note the summary strip array and the ledger table columns

---

## What we are adding

**Two additions to the Ledger tab only:**

1. **A fifth strip card** — "On Credit" showing total revenue taken on
   credit in the period, with a sub-label showing what % of gross revenue
   that represents.

2. **A new "Credit" column in the product ledger table** — for each product
   row, show how much of that product's revenue was sold on credit. Styled
   in amber to signal it as a risk/attention metric.

---

## STEP 1 — Add credit_revenue to getTopProducts() in SalesAnalyticsService.php

**File:** `app/Services/Analytics/SalesAnalyticsService.php`

Find the `getTopProducts()` method. Inside its `selectRaw(...)` call,
find the last column before the closing quote. It looks like:

```php
->selectRaw('
    products.id,
    products.name,
    MIN(products.selling_price) as selling_price,
    MIN(products.purchase_price) as purchase_price,
    SUM(sale_items.quantity_sold) as quantity_sold,
    SUM(sale_items.line_total) as revenue,
    COUNT(DISTINCT sales.id) as transaction_count,
    SUM(sale_items.line_total) / NULLIF(SUM(sale_items.quantity_sold), 0) as avg_selling_price,
    SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as gross_profit
')
```

Add one more column at the end, before the closing quote:

```sql
    SUM(CASE WHEN sales.has_credit = true
        THEN sale_items.line_total ELSE 0 END) as credit_revenue
```

So the full selectRaw becomes:

```php
->selectRaw('
    products.id,
    products.name,
    MIN(products.selling_price) as selling_price,
    MIN(products.purchase_price) as purchase_price,
    SUM(sale_items.quantity_sold) as quantity_sold,
    SUM(sale_items.line_total) as revenue,
    COUNT(DISTINCT sales.id) as transaction_count,
    SUM(sale_items.line_total) / NULLIF(SUM(sale_items.quantity_sold), 0) as avg_selling_price,
    SUM(sale_items.line_total - (products.purchase_price * sale_items.quantity_sold)) as gross_profit,
    SUM(CASE WHEN sales.has_credit = true
        THEN sale_items.line_total ELSE 0 END) as credit_revenue
')
```

Then find the `->map(fn ($item) => [` block that builds the return array.
It ends with `'revenue_share' => ...`. Add one more key after it:

```php
'credit_revenue' => (int) $item->credit_revenue,
'credit_pct'     => $item->revenue > 0
    ? round(($item->credit_revenue / $item->revenue) * 100, 1)
    : 0,
```

---

## STEP 2 — Add the "On Credit" strip card to the blade

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Find the `TAB: SALES LEDGER` section. Inside it, find the `@php` block
that sets `$gp`, `$iss`, `$rev`, `$topP` variables, and the summary strip
`@foreach` array. It currently has four items:

```php
@foreach([
    ['label'=>'Gross Revenue',  'value'=>..., 'color'=>'var(--accent)',  'sub'=>...],
    ['label'=>'Total Cost',     'value'=>..., 'color'=>'var(--text-sub)','sub'=>...],
    ['label'=>'Gross Profit',   'value'=>..., 'color'=>'var(--green)',   'sub'=>...],
    ['label'=>'Items Sold',     'value'=>..., 'color'=>'var(--violet)',  'sub'=>...],
] as $strip)
```

Add a fifth item at the end of the array, before `] as $strip)`:

```php
    ['label'=>'On Credit',
     'value'=>number_format(collect($topP)->sum('credit_revenue')),
     'color'=>'var(--amber)',
     'sub'  => (
         $gp['revenue'] > 0
             ? round((collect($topP)->sum('credit_revenue') / $gp['revenue']) * 100, 1)
             : 0
     ) . '% of revenue'],
```

---

## STEP 3 — Add the Credit column to the ledger table header

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

In the `TAB: SALES LEDGER` section, find the `<table>` with class
`sa-ledger-table`. Inside its `<thead><tr>`, find the last `<th>` which
is the "Share" or "Margin" column. After that last `<th>`, add:

```html
<th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;
           color:#d97706;letter-spacing:.5px;text-transform:uppercase;
           white-space:nowrap">Credit</th>
```

---

## STEP 4 — Add the Credit cell to each ledger table body row

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

In the same `sa-ledger-table`, find the `@foreach($topP as $i => $p)` loop.
Inside each `<tr>`, find the last `<td>` (the Share or Margin cell).
After that last `<td>`, add:

```blade
<td style="padding:10px 14px;text-align:right;font-family:var(--mono);
           font-size:12px;white-space:nowrap;
           color:{{ $p['credit_revenue'] > 0 ? '#d97706' : 'var(--text-dim)' }}">
    @if($p['credit_revenue'] > 0)
        {{ number_format($p['credit_revenue']) }}
        <span style="font-size:10px;color:#d97706;margin-left:2px">
            {{ $p['credit_pct'] }}%
        </span>
    @else
        <span style="color:var(--text-dim)">—</span>
    @endif
</td>
```

---

## STEP 5 — Add the Credit cell to the ledger table footer (totals row)

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Find the `<tfoot>` of the `sa-ledger-table`. It has a row with totals for
each column. Find the last `<td>` in that row and add one more after it:

```blade
<td style="padding:10px 14px;text-align:right;font-size:12px;font-weight:700;
           font-family:var(--mono);white-space:nowrap;color:#d97706">
    @php $totalCredit = collect($topP)->sum('credit_revenue'); @endphp
    {{ $totalCredit > 0 ? number_format($totalCredit) : '—' }}
</td>
```

---

## STEP 6 — Add `credit_revenue` to the `<colgroup>` width definition

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Find the `<colgroup>` inside the `sa-ledger-table`. Add one more `<col>`
at the end matching the width of the other numeric columns:

```html
<col style="width:120px">
```

---

## STEP 7 — Clear caches

```bash
php artisan view:clear
php artisan cache:clear
```

---

## Do NOT touch

- Any other tabs (Overview, Audit, Sellers, Payments, Credit tab)
- The `$this->grossProfitKpis` computed property or service method
- Any other method in `SalesAnalyticsService.php`
- Any migration files
- The `getTopProducts()` cache key — it will naturally cache-bust because
  the query content changed

---

## Verification

1. Open Sales Analytics → Ledger tab
2. Strip row should now have 5 cards: Gross Revenue, Total Cost,
   Gross Profit, Items Sold, **On Credit** (amber)
3. The "On Credit" card should show the total credit amount for the period
   and its % of gross revenue
4. The product table should have a new **Credit** column (amber header)
5. Products with zero credit sales show "—" in that column
6. Products with credit sales show the amount and % in amber
7. The totals footer row should show the grand total credit amount
