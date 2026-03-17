# Fix Outstanding Credit Strip Card Value
## Claude Code Instructions

> Tell Claude Code: "Read STRIP_CARD_FIX.md and apply the change."

---

## One change only

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Run this first to see the current state:

```bash
grep -n "Outstanding Credit\|periodCreditGiven\|trueOutstanding" \
  resources/views/livewire/owner/reports/sales-analytics.blade.php
```

Find the strip card array item that has `'label'=>'Outstanding Credit'`
or `'label'=>'Credit Sales'` inside the `TAB: SALES LEDGER` section.

That item has a `'value'` key. Whatever it currently shows, change it so
the array item reads exactly:

```php
['label' => 'Outstanding Credit',
 'value' => number_format($trueOutstanding),
 'color' => 'var(--amber)',
 'sub'   => number_format($totalCreditGiven) . ' given · '
          . number_format($totalCreditRepaid) . ' repaid · '
          . $repaymentRate . '%'],
```

Then run:

```bash
php artisan view:clear && php artisan cache:clear
```

Confirm by running the grep again and showing the matching lines.
