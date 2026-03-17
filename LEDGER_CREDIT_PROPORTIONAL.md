# Sales Analytics Ledger — Fix Credit Revenue Attribution
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read LEDGER_CREDIT_PROPORTIONAL.md and follow every step in order."

---

## Read these files first before writing any code

```bash
cat app/Services/Analytics/SalesAnalyticsService.php
cat resources/views/livewire/owner/reports/sales-analytics.blade.php
```

---

## Root Cause

The current `credit_revenue` formula in `getTopProducts()`:

```sql
SUM(CASE WHEN sales.has_credit = true
    THEN sale_items.line_total ELSE 0 END) as credit_revenue
```

Takes the **entire line total** for any sale that has credit, even if credit
was only a fraction of the payment. A sale of 500,000 RWF where only 100,000
was paid on credit still contributes the full 500,000 to `credit_revenue`.

That is why the column total (2,760,000) is far larger than the strip card
outstanding (454,000) and total repaid (600,000) combined.

The correct formula distributes `sales.credit_amount` proportionally across
each line item based on its weight in the sale total.

---

## STEP 1 — Fix credit_revenue formula in SalesAnalyticsService.php

**File:** `app/Services/Analytics/SalesAnalyticsService.php`

Find the `getTopProducts()` method. Inside the `selectRaw(...)` call, find:

```sql
SUM(CASE WHEN sales.has_credit = true
    THEN sale_items.line_total ELSE 0 END) as credit_revenue
```

Replace it with:

```sql
SUM(
    CASE WHEN sales.has_credit = true AND sales.total > 0
    THEN ROUND(
        sale_items.line_total::numeric
        / NULLIF(sales.total::numeric, 0)
        * sales.credit_amount::numeric
    )
    ELSE 0 END
) as credit_revenue
```

This means: for each line item, its share of the credit is
`(line_total / sale_total) × credit_amount`.

Example: 300,000 item in a 500,000 sale where 200,000 was on credit
→ contributes `(300,000 / 500,000) × 200,000 = 120,000` to credit_revenue.

The `NULLIF(..., 0)` prevents division by zero for any edge-case zero-total
sale. The `ROUND(...)` keeps the result as a clean integer.

The rest of the `selectRaw` stays exactly as-is. Only this one formula changes.

---

## STEP 2 — Fix the strip card @php block in the blade

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Find the `TAB: SALES LEDGER` section. Inside it, find the `@php` block
that was added by LEDGER_CREDIT_FIX.md. It looks like:

```php
// True outstanding: read from customers.outstanding_balance (post-repayment)
$creditQuery = \App\Models\Customer::query();
...
$trueOutstanding   = ...
$totalCreditGiven  = ...
$totalCreditRepaid = ...
$repaymentRate     = ...
```

Replace that entire block with:

```php
// ── Credit figures for the Ledger strip ──────────────────────────────────
// Period credit: what was sold on credit in the selected date range
// (matches the sum of credit_revenue column across all product rows)
$periodCreditQ = \App\Models\Sale::whereNull('voided_at')
    ->where('has_credit', true)
    ->whereBetween('sale_date', [$dateFrom, $dateTo]);
if ($locationFilter !== 'all') {
    $lcShopId = (int) str_replace('shop:', '', $locationFilter);
    $periodCreditQ->where('shop_id', $lcShopId);
}
$periodCreditGiven = (int) $periodCreditQ->sum('credit_amount');

// All-time outstanding: current unpaid balance across all customers
$custQ = \App\Models\Customer::query();
if ($locationFilter !== 'all') {
    $custQ->where('shop_id', $lcShopId);
}
$trueOutstanding   = (int) (clone $custQ)->sum('outstanding_balance');
$totalCreditRepaid = (int) (clone $custQ)->sum('total_repaid');
$totalCreditGiven  = (int) (clone $custQ)->sum('total_credit_given');
$repaymentRate     = $totalCreditGiven > 0
    ? round(($totalCreditRepaid / $totalCreditGiven) * 100, 1)
    : 0;
```

---

## STEP 3 — Fix the strip card display values

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Find the fifth strip card item in the summary strip `@foreach` array.
It currently looks like:

```php
['label'=>'Outstanding Credit',
 'value'=>number_format($trueOutstanding),
 'color'=>'var(--amber)',
 'sub'  => number_format($totalCreditRepaid) . ' RWF repaid · ' . $repaymentRate . '% rate'],
```

Replace it with:

```php
['label'=>'Credit Sales',
 'value'=>number_format($periodCreditGiven),
 'color'=>'var(--amber)',
 'sub'  => number_format($trueOutstanding) . ' still outstanding'],
```

The strip card now shows **credit given in the period** (matches the column
sum) and the sub-label shows the true outstanding balance (all-time, after
repayments).

---

## STEP 4 — Fix the info bar text

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Find the info bar added by LEDGER_CREDIT_FIX.md. It starts with:
```blade
@if($trueOutstanding > 0 || $totalCreditGiven > 0)
<div style="...background:rgba(217,119,6,.06)...">
```

Replace the entire inner text content of that div with:

```blade
<span style="font-size:14px;flex-shrink:0">ℹ️</span>
<div style="font-size:11px;color:var(--text-sub);line-height:1.5">
    <strong style="color:#d97706">Credit Sales</strong>
    ({{ number_format($periodCreditGiven) }} RWF) = credit portion of sales
    in this period, distributed proportionally across products.
    &nbsp;·&nbsp;
    <strong style="color:#d97706">Still Outstanding</strong>
    ({{ number_format($trueOutstanding) }} RWF) = current unpaid balance
    across all customers after {{ number_format($totalCreditRepaid) }} RWF
    repaid ({{ $repaymentRate }}% repayment rate, all time).
</div>
```

---

## STEP 5 — Clear caches

```bash
php artisan view:clear
php artisan cache:clear
```

---

## Do NOT touch

- Any other method in `SalesAnalyticsService.php`
- Any other tabs (Overview, Audit, Sellers, Payments, Credit)
- The `credit_revenue` key name in the returned array — it still exists,
  just calculated correctly now
- Any migration files

---

## Verification

After the fix, these three numbers must be consistent:

1. **Strip card "Credit Sales"** = sum of `sales.credit_amount` for the period
2. **Product column total (tfoot)** = sum of all `credit_revenue` values
   across all product rows = should equal (1) within rounding
3. **Sub-label "still outstanding"** = `customers.outstanding_balance` sum
   = less than (1) because some credit has been repaid

Quick DB check you can run in tinker to verify:

```php
// This should match the strip card value
\App\Models\Sale::whereNull('voided_at')
    ->where('has_credit', true)
    ->sum('credit_amount');

// This should match the sub-label
\App\Models\Customer::sum('outstanding_balance');

// The gap between the two = total repaid
\App\Models\Customer::sum('total_repaid');
```
