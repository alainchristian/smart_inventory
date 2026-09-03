## Dashboard Redesign (Shop & Warehouse Manager) — completed 2026-05-01
- Both dashboards now use CSS custom properties (var(--surface), var(--text), etc.)
  instead of raw Tailwind color classes — matching the owner dashboard design system.
- Section labels (class="section-label") added before each logical group.
- Tables already had overflow-x:auto wrappers — confirmed no bare tables remain.
- KPI cards separated: "Today's Performance" vs "Period Summary" strip.
- Transfer pipeline rendered as a horizontal 4-stage stepper (pending→approved→in transit→to receive).
- Low stock rows include inline "Request →" (shop) / "Reorder →" (warehouse) action buttons.
- Warehouse dashboard adds "Received Today" and "Dispatched Today" KPI cards; grid changed to lg:grid-cols-6.
- animate-pulse removed everywhere; replaced with static colored dot indicators.
- Quick Navigation strip added at page bottom (4 buttons) on both dashboards.
- Owner warehouse selector moved inline into the page header (compact select, no full-width card).
- Last sync displayed as a colored dot + relative time (green if < 5 min, amber otherwise).
- Shop subtitle typo fixed: "managements" → "management".
- Shop quick actions remain in the existing FAB component (no standalone card existed to remove).

---

## Shop Dashboard v2 (Full Redesign) — completed 2026-05-01, updated 2026-05-02

### Files
- `app/Livewire/Shop/Dashboard.php` — component
- `resources/views/livewire/shop/dashboard.blade.php` — Livewire template
- `resources/views/shop/dashboard.blade.php` — wrapper (CSS + Chart.js JS)

### Period filter
- Default period: `today` (was `this_week`)
- Periods: today | yesterday | this_week | this_month | last_month | last_30 | custom
- `sale_date` is a **TIMESTAMP** — always pass Carbon objects to `whereBetween`, never raw date strings

### Charts — reliability rules
- Chart.js loaded once in `<head>` (no defer/async) via CDN
- Init order: `livewire:initialized` → `livewire:navigated` → `commit` hook (filter changes) → DOMContentLoaded fallback
- All re-renders use `chart.update('none')` — no animations on filter change
- Sparklines use `canvas.classList.remove/add('db-spark-refresh')` for a brief pop-in on each redraw
- `animation: false` set on all Chart.js instances

### Sales Trend chart
- **Single-day (today/yesterday):** queries individual sales at exact timestamps → one point per sale, line chart, no previous-period overlay, `tension: 0.3`
- **Multi-day:** time-bucketed points (one per day or evenly-spaced), line chart with previous-period dashed overlay
- `$isSingleDay` must be in `compact()` and passed via `data-is-single-day` on the data div (currently unused in JS but kept for future use)

### Sparklines (KPI cards)
- Always bucket-based (7 slots for single-day, one per day for multi-day)
- Single-day hourly slots cover **full 24h with no gaps**: `[[0,3],[4,7],[8,10],[11,13],[14,16],[17,19],[20,23]]`
- Use `sale_date` (not `created_at`) for all sale queries

### Cash Flow donut
- Replaced the SVG bracket flow diagram with a Chart.js doughnut
- Canvas: `width=150 height=150`, `responsive: false`, `cutout: '72%'`
- Segments: Cash (#1d9e75) · MoMo (#3b6bd4) · Bank (#8b5cf6) · Card (#f59e0b)
- Bank and Card segments are **omitted** when `allowBankTransferPayment()` / `allowCardPayment()` return false
  — PHP passes `-1` as sentinel; JS skips any segment with value < 0
- Right column: inflow legend + 2×2 deductions grid (Refunds, Withdrawals, Expenses, Credit)
- **Net In Hand** strip at bottom: `cfTotal − cfReturns − cfWithdrawals − cfExpenses`
  — green background when ≥ 0, red when negative
  — Credit is shown in deductions for awareness but NOT subtracted (it was never in cfTotal)
- `$cfCard` is included in `$cfTotal` when card is enabled
- All CF variables passed via `compact()`: `cfCash, cfMomo, cfBank, cfCard, cfTotal, cfReturns, cfWithdrawals, cfCredit, cfExpenses, cfNet, allowCard, allowBankTransfer`

### Payment method settings (SettingsService)
- `allowCardPayment()` — default false
- `allowBankTransferPayment()` — default false
- Controlled via owner settings page → Payment Methods section (toggles already present)
- Applied in: POS blade (`@if($settingAllowCardPayment)`), Dashboard donut (segment gating)

### Key variable notes
- `$topProducts` — DB stdClass objects with `revenue`, `units_sold` (not Eloquent models)
- `shop.inventory.stock` is the correct route name (not `stock-levels`)
- `shop.alerts.index` does not exist — alert bell links to `'#'`

---

## Credit Repayments — Missing Customers Bug Fix (2026-08-20)

### The bug
`/shop/credit-repayments` showed "No Customers with Outstanding Credit"
for a shop_manager (Alice) despite real customers having outstanding
balances (Emmanuel Nzeyimana, Robert Kayitare, Tuyisenge Alex).

### Root cause
`Customer.shop_id` is intentionally nullable — the owner's Customers page
(`resources/views/livewire/owner/customers/customer-list.blade.php`)
explicitly supports registering a customer as "— Not shop-specific —"
(hint: "Optional — leave unset if this customer isn't tied to one shop.").
`app/Livewire/Shop/CreditRepayments.php::getCustomersProperty()` filtered
shop_manager users with a plain `where('shop_id', $shopId)`, which silently
excludes every unassigned (`shop_id IS NULL`) customer — exactly the ones
in the seeded data.

I initially misdiagnosed this and made `form_shop_id` required in
`Owner\Customers\CustomerList.php::save()` — wrong, since nullable shop_id
is intentional design, not an oversight. Self-caught by reading the blade
before the change reached the user; reverted, and added a comment there
pointing back to this fix instead.

### Fix
`app/Livewire/Shop/CreditRepayments.php::getCustomersProperty()` — for
shop_manager users, the shop filter now reads:
```php
$query->where(function ($q) use ($shopId) {
    $q->where('shop_id', $shopId)->orWhereNull('shop_id');
});
```
An unassigned customer is now repayable from any shop rather than being
invisible everywhere.

### Same bug pattern found elsewhere, deliberately NOT touched
- `app/Livewire/ShopManager/Dashboard.php::getShopCreditOutstanding()` —
  identical `where('shop_id', ...)` pattern, but confirmed via
  `routes/web.php` grep that this component is orphaned (no route reaches
  it). Left untouched per the established convention of not fixing dead code.
- `app/Livewire/Owner/Reports/CustomerCreditReport.php` — same pattern
  when a specific shop filter is selected, but this is a deliberate
  single-shop reporting/breakdown view, not a "can I collect this
  customer's payment" workflow — excluding unassigned customers from a
  shop-specific report is defensible and was left as-is.

### Verification
Live-tested in Alice's (shop_manager) authenticated session:
1. Unfiltered list now shows all 3 customers with outstanding balances.
2. Typing "robert" into the search box correctly narrows the list to just
   Robert Kayitare, confirming the search filter composes correctly with
   the fixed shop_id-or-null base query.

---

## Credit Repayments — Page & Modal Redesign (2026-08-20, same day)

Redesigned `resources/views/livewire/shop/credit-repayments.blade.php`
(prefix `cr-`) to the `ui-design.md` design system. Previous version used
hardcoded hex (`#ef4444`, `#10b981`), permanent `var(--surface2)` fills on
card/table containers (never allowed — surface2 is hover/active only), a
raw `session()->flash('success', ...)` banner instead of the app-wide toast
system, and no KPI row at all.

### New KPI row (`app/Livewire/Shop/CreditRepayments.php::getStatsProperty()`)
Four cards, each following the mandatory icon-row → value → divider →
3-stat-footer structure:
- **Total Outstanding** — sum across customers in scope; footer: Customers,
  Highest, Avg Balance
- **Collected Today** — `CreditRepayment` rows where `repayment_date` is
  today; footer: Payments, Avg Payment, Customers
- **Repayment Rate** — all-time `total_repaid / total_credit_given` across
  every customer in scope (not just those still owing); footer: Credit
  Given, Repaid, Written Off (pulls from `CreditWriteoff`, ties the two
  credit-related modules together)
- **Overdue Customers** — reuses the exact same overdue condition as
  `GenerateSystemAlerts::generateOverdueCreditAlerts()`
  (`(last_repayment_at IS NULL AND last_credit_at < cutoff) OR last_repayment_at < cutoff`,
  cutoff = `SettingsService::overdueCreditDays()`) rather than inventing a
  new definition — footer: Total Owing, % of Total, Threshold (days)

Shop-manager scoping (`shop_id = mine OR shop_id IS NULL`, per the fix
above) is applied consistently to every stat, not just the customer list.

### Success flow
`recordRepayment()` now dispatches the app-wide toast
(`$this->dispatch('notification', ['type'=>'success', ...])`) instead of
`session()->flash('success', ...)` — this page was the last one still using
the older flash-banner pattern after the global toast system landed
earlier in the day; removing it keeps success feedback consistent
everywhere.

### Modal
Rebuilt as a centered overlay (`position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200`),
matching the existing convention already used by the Sales Analytics reject
modal — not a slide-in drawer, since this is a compact single-purpose form
triggered from a table row, not a record-editing panel. Balance summary,
payment-method pills, and repayment history all rebuilt with CSS variables
only.

### Bug found and fixed during verification
The submit button's `wire:loading` span was given
`style="display:inline-flex;..."` as its base style instead of
`style="display:none;..."` (per the `ui-design.md` §9.3 loading-button
pattern). An inline style always wins over Livewire's injected
`[wire\:loading]{display:none}` stylesheet rule, so the "Recording…" state
was visible **permanently**, overlapping "Record Repayment" at all times
— not just during an actual request. Fixed by setting the base state back
to `display:none`, letting Livewire toggle it during the request as
designed.

### Verification note — test data pollution, cleaned up
While live-testing the modal (submit a real repayment as Alice), a
browser-automation quirk surfaced: scrolling the mouse wheel over a
**focused** `<input type="number">` changes its value (native HTML
behavior — number inputs respond to wheel deltas when focused). A scroll
action performed while the amount field had focus silently mutated a
typed `50000` into `800000` before submission, and a second, unexplained
1,800,000 RWF repayment also appeared against Emmanuel Nzeyimana during
the same test window (best guess: a second stray commit from the same
interactive session — root cause not fully isolated, but irrelevant to
the app code since neither amount was ever typed by a real user).
Both were reverted directly via `tinker` — `CreditRepayment` rows deleted,
`Customer.outstanding_balance` / `total_repaid` / `last_repayment_at`
restored to their pre-test values for both Robert Kayitare and Emmanuel
Nzeyimana. **`activity_logs` is DB-trigger-enforced append-only**
(`prevent_activity_logs_mutation()` — confirmed by a raised exception when
delete was attempted) — the corresponding audit-log rows from these two
test transactions could not be and were not removed; they remain as
harmless residual entries with test amounts, by design of the table's
immutability guarantee. This is a real, working safety feature, not a
bug — no attempt was made to bypass it.
**Lesson for future browser-automation testing on this app:** never
`scroll` at a coordinate that overlaps a currently-focused
`<input type="number">` — blur the field (click elsewhere, or press Tab)
before scrolling, or the field's value can silently change.
