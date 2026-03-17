# Sales Analytics Ledger — Fix Credit Strip Card & Column Label
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read LEDGER_CREDIT_FIX.md and follow every step in order."

---

## Read this file first

```bash
cat resources/views/livewire/owner/reports/sales-analytics.blade.php
```

Focus on the `TAB: SALES LEDGER` section only.

---

## The problem

The "On Credit" strip card currently shows:

```php
collect($topP)->sum('credit_revenue')
```

This sums `sales.credit_amount` for the period — i.e. **gross credit given**
at the time each sale was made. It ignores repayments entirely. If a customer
bought 80,000 RWF on credit and already repaid 50,000 RWF, the card still
shows 80,000 RWF.

The correct source is `customers.outstanding_balance` — a denormalized column
that is decremented every time a repayment is recorded. It reflects the true
uncollected amount right now.

The product "Credit" column has a different but related problem: repayments
are recorded against the customer balance as a whole, not against specific
products. There is no way to know which product's credit a repayment cleared.
The column must stay as gross credit sales but must be labelled clearly so
the owner does not misread it as "still outstanding per product".

---

## STEP 1 — Fix the "On Credit" strip card

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

In the `TAB: SALES LEDGER` section, find the `@php` block at the top of the
tab that sets `$gp`, `$iss`, `$rev`, `$topP`. Add these two lines at the
end of that `@php` block (before `@endphp`):

```php
// True outstanding: read from customers.outstanding_balance (post-repayment)
$creditQuery = \App\Models\Customer::query();
if ($locationFilter !== 'all') {
    $shopId = (int) str_replace('shop:', '', $locationFilter);
    $creditQuery->where('shop_id', $shopId);
}
$trueOutstanding   = (int) $creditQuery->sum('outstanding_balance');
$totalCreditGiven  = (int) (clone $creditQuery)->sum('total_credit_given');
$totalCreditRepaid = (int) (clone $creditQuery)->sum('total_repaid');
$repaymentRate     = $totalCreditGiven > 0
    ? round(($totalCreditRepaid / $totalCreditGiven) * 100, 1)
    : 0;
```

Then find the fifth strip card item you added previously. It currently reads:

```php
['label'=>'On Credit',
 'value'=>number_format(collect($topP)->sum('credit_revenue')),
 'color'=>'var(--amber)',
 'sub'  => (...) . '% of revenue'],
```

Replace it entirely with:

```php
['label'=>'Outstanding Credit',
 'value'=>number_format($trueOutstanding),
 'color'=>'var(--amber)',
 'sub'  => number_format($totalCreditRepaid) . ' RWF repaid · ' . $repaymentRate . '% rate'],
```

---

## STEP 2 — Relabel the product column header

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Find the `<th>` for the credit column you added previously. It currently reads:

```html
<th style="...color:#d97706;...">Credit</th>
```

Change the text content from `Credit` to `Credit Sales` and add a small
tooltip-style sub-label so the owner understands this is gross:

```html
<th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;
           color:#d97706;letter-spacing:.5px;text-transform:uppercase;
           white-space:nowrap"
    title="Gross credit sales per product. Repayments are tracked at customer level and cannot be attributed to specific products.">
    Credit Sales
    <span style="display:block;font-size:9px;font-weight:400;color:var(--text-dim);
                 text-transform:none;letter-spacing:0">gross · pre-repayment</span>
</th>
```

---

## STEP 3 — Update the tfoot credit total label

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Find the `<tfoot>` credit cell you added previously. It shows
`$totalCredit` (the sum of `credit_revenue` from `$topP`). This is still
valid as a "total credit sales in period" figure. Keep the value but
change its title attribute so the footer is self-explanatory:

Find the tfoot credit `<td>` and add a sub-line under the number:

```blade
<td style="padding:10px 14px;text-align:right;font-size:12px;font-weight:700;
           font-family:var(--mono);white-space:nowrap;color:#d97706">
    @php $totalCredit = collect($topP)->sum('credit_revenue'); @endphp
    {{ $totalCredit > 0 ? number_format($totalCredit) : '—' }}
    @if($totalCredit > 0)
        <span style="display:block;font-size:10px;font-weight:400;
                     color:var(--text-dim);font-family:var(--font)">
            gross sales
        </span>
    @endif
</td>
```

---

## STEP 4 — Add a visual separator note below the strip cards

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Immediately after the closing `</div>` of the strip cards `@foreach` wrapper
(the `</div>` that closes `<div class="sa-strip-wrap" ...>`), add:

```blade
{{-- Credit footnote: explains gross vs net distinction --}}
@if($trueOutstanding > 0 || $totalCreditGiven > 0)
<div style="display:flex;align-items:center;gap:8px;padding:8px 14px;
            background:rgba(217,119,6,.06);border:1px solid rgba(217,119,6,.2);
            border-radius:8px;margin-bottom:16px;margin-top:-4px">
    <span style="font-size:14px;flex-shrink:0">ℹ️</span>
    <div style="font-size:11px;color:var(--text-sub);line-height:1.5">
        <strong style="color:#d97706">Outstanding Credit</strong> shows the actual
        uncollected balance across all customers after repayments
        ({{ number_format($totalCreditRepaid) }} RWF repaid · {{ $repaymentRate }}% rate).
        The <strong style="color:#d97706">Credit Sales</strong> column shows gross credit
        sold per product — repayments cannot be attributed to individual products.
    </div>
</div>
@endif
```

---

## STEP 5 — Clear caches

```bash
php artisan view:clear
php artisan cache:clear
```

---

## Do NOT touch

- `getTopProducts()` in `SalesAnalyticsService.php` — `credit_revenue` stays
  as gross credit sales, which is correct for the column purpose
- Any other tabs
- Any migrations

---

## Verification

1. Open Sales Analytics → Ledger tab
2. Strip card labelled "Outstanding Credit" should now show the value from
   `customers.outstanding_balance` (sum), not from sales
3. Its sub-label should show "X RWF repaid · Y% rate"
4. The product column header should read "Credit Sales" with
   "gross · pre-repayment" sub-label
5. The info bar below the strips explains both figures in plain language
6. Record a repayment in the shop credit repayments page, then come back
   to the ledger — the strip card value should decrease, but the product
   column values stay the same (they are historical sales, not balances)
