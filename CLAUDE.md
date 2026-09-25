## UI & Design System

Before creating or editing ANY blade view, Livewire template, or CSS,
read the full skill file at:

```
.claude/skills/ui-design.md
```

This is mandatory. It contains the existing design patterns, CSS class
conventions, color variables, component structures, and mobile breakpoints
that all pages must follow.

---

## Security Hardening Pass (2026-08-12)

Ran through a 7-point hardening checklist:

1. **Mass assignment audit** — clean, no fix needed. `User::$fillable`
   includes `role`/`location_type`/`location_id`, and `Product::$fillable`
   includes `purchase_price`/`is_active`, but a full-codebase search found
   `$request->all()` used only inside `Validator::make()` calls
   (`ScannerController.php`) — never passed to a model create/update/fill.
   Every `Model::create($data)`/`->update($data)` site builds `$data` as an
   explicit hand-listed array (reference pattern:
   `app/Livewire/Owner/Users/UserList.php::save()`). Latent risk noted for
   the future: these fields being fillable means any new code path that
   swaps an explicit array for `$request->all()`/`$request->validated()`
   without dropping `role`/`purchase_price` would reopen this.
2. **Login rate limiting** — confirmed active, no fix needed. App uses
   Livewire Volt for auth (not Breeze's `AuthenticatedSessionController`).
   `app/Livewire/Forms/LoginForm.php::ensureIsNotRateLimited()` /
   `authenticate()` implement standard 5-attempts-per-key throttling,
   unmodified.
3. **Session regeneration on login** — confirmed active, no fix needed.
   `resources/views/livewire/pages/auth/login.blade.php` calls
   `Session::regenerate()` immediately after successful
   `$this->form->authenticate()`, before redirect.
4. **CSV export formula injection** — fixed. Added `csv_safe()` to
   `app/helpers.php` (prefixes a leading `=`/`+`/`-`/`@` with `'`).
   Applied to `user_name` and `entity_identifier` in
   `app/Livewire/Owner/ActivityLogs.php::exportCsv()` — the only *live,
   reachable* CSV export of free-text data.
   `app/Livewire/Owner/Products/UploadPurchasePrices.php` has the same
   unsanitized pattern but is confirmed orphaned (see #5) — left
   untouched since the code is unreachable.
   `ReceiveBoxes::downloadTemplate()` only writes a static template, not
   vulnerable.
5. **Defense-in-depth authorization on the product/stock upload flow** —
   fixed, with a scope correction. `UploadPurchasePrices.php` confirmed
   orphaned (no route anywhere reaches it; not referenced in the "Box-Centric
   Product Management & Owner Stock Intake" section above) — left untouched
   per instruction. The actual live entry point is
   `app/Livewire/Warehouse/Inventory/ReceiveBoxes.php`, reached via
   `owner.inventory.receive` and the original warehouse route, both gated
   `CheckRole:warehouse_manager,owner` — **not owner-only**, contrary to the
   checklist's assumption. Added a method-level guard
   (`isOwner() || isWarehouseManager()`) to `createBoxes()` and
   `confirmExcelImport()`, matching the route's actual shared-role model
   instead of copying `EditProduct::update()`'s stricter owner-only check
   (which would have locked out legitimate warehouse manager stock
   receiving). Verified against seeded users of all three roles that the
   guard allows owner + warehouse_manager and denies shop_manager.
6. **ActivityLog sensitive field redaction** — no active leak found (`User`
   doesn't use the `Auditable` trait, so `password`/`remember_token` are
   never auto-captured; the only UI rendering `old_values`/`new_values`
   diffs is behind `CheckRole:owner` middleware, matching the
   `viewPurchasePrice` gate's owner-only intent). Added defense-in-depth
   anyway: `AuditLogger::log()` now redacts `password`/`remember_token` via
   `Arr::except()` before every `ActivityLog::create()` call, regardless of
   caller (covers both the automatic `Auditable` trait path and manual
   call sites). `purchase_price` deliberately **not** blocklisted — it's
   legitimate owner-facing audit data today; revisit only if a
   non-owner-facing ActivityLog view is ever built.
7. **`composer audit`** — 53 advisories across 16 packages, low to
   critical. Notable: `phpoffice/phpspreadsheet` has two **critical**
   advisories (CVE-2026-34084 SSRF/RCE via user-controlled filename in
   `IOFactory::load`, and CVE-2026-45034, a patch bypass for the same) —
   relevant since this package drives the Excel import in
   `ReceiveBoxes.php`. Also multiple high/medium advisories in
   `league/commonmark`, `guzzlehttp/guzzle`/`psr7`, and a CRLF-injection
   advisory in `laravel/framework`. Reported only, per instructions — no
   upgrades bundled into this pass; treat as a separate deliberate task.

---

## Price Override Governance Unification + Report Consistency (2026-08-20)

### Problem found
Price-override approval was split across **three independent, duplicated
implementations** with no shared reason-capture and no single owner
destination:
1. `App\Livewire\Dashboard\OwnerActions` — inline approve/reject widget on
   the owner dashboard (reject had no reason field, just `wire:confirm`).
2. `App\Livewire\Layout\Topbar` — a full modal on the notification bell,
   duplicating #1 almost exactly (reject hardcoded `'Rejected by owner'`,
   never asked the user for a reason).
3. `App\Livewire\Owner\Reports\SalesAnalytics` Audit tab — approve-only,
   only actioned completed `Sale.has_price_override` rows, never showed
   the pending `HeldSale` queue at all.
Additionally, completed-sale overrides (below the hold threshold) never
fired an `Alert` at all — only the pre-checkout `HeldSale` path did, and
that alert linked to the generic owner dashboard, not any audit view.

### Fix — Sales Analytics → Audit tab is now the single Price Audit module
- `SalesAnalyticsService::getPriceAuditLog()` now unions pending `HeldSale`
  rows (source `'held'`) with completed `Sale` override rows (source
  `'sale'`) into one array, sorted by date. Cache key/TTL unchanged.
- `SalesAnalytics.php` gained `approveHeldSale()` / `openRejectHeldModal()`
  / `closeRejectHeldModal()` / `rejectHeldSale()` (reason required, reused
  pattern from `ReviewTransfer::reject()`), alongside the existing
  `approvePriceOverride()` (now delegates to
  `SaleService::approvePriceOverride()` instead of duplicating the update
  inline). Completed sales stay approve-only — the sale already happened,
  there's nothing to "reject".
- `OwnerActions.php` and `Topbar.php` **no longer implement** approve/reject
  — both `approveHeldSale`/`rejectHeldSale` methods and the Topbar's entire
  approval modal were deleted. Both surfaces now only **link** to
  `route('owner.reports.sales') . '?activeTab=audit'`.
- `SaleService::notifyPriceOverride()` (new, private) fires an `Alert` for
  every completed sale with `has_price_override = true`, called from all
  three sale-creation paths (`createSale`, `createWarehouseSale`,
  `createMixedSale`). `SaleService::approvePriceOverride()` now resolves
  the matching `Alert` on approval.
- Every price-override `Alert` (both the `HeldSale` one from
  `UnifiedPos::holdSale()` and the new `Sale` one) now points
  `action_url` at the Audit tab, never `owner.dashboard`.
- **Do not** re-add approve/reject UI to `OwnerActions` or `Topbar` — if a
  quicker path is wanted, make it navigate to the Audit tab, not duplicate
  the action.

### Sellers tab reorganized (`sales-analytics.blade.php`)
- New computed property `SalesAnalytics::getSellersByShopProperty()`
  groups the existing flat `sellerPerformance` array by shop (shop
  subtotal + its sellers, both revenue-ranked). `sellerPerformance` itself
  is untouched — `exportSellersCsv()` still needs the flat shape.
- The seller table now renders shop header/subtotal rows with sellers
  nested beneath, instead of one flat table with a redundant per-row Shop
  column.
- The customer/returns KPI row that used to sit under the Sellers table
  (Known Customers, Repeat Rate, Returns, Refunded Amount) was mismatched
  with the tab's own data. Returns/Refunded were dropped as duplicates of
  the Overview tab's existing "Net Revenue" card; Known Customers/Repeat
  Rate moved to the Overview tab's KPI grid instead. The Sellers tab's
  Customer Analysis / Returns detail tables stay (still useful drill-down),
  just without the redundant KPI strip above them.

### KPI card structure standardized on `.iv-kpi` (inventory-valuation)
`customer-credit-report.blade.php` (`.bkpi` → `.cc-kpi`),
`payment-methods-report.blade.php` (`.bkpi` → `.pm-kpi`), and
`report-viewer.blade.php`'s dynamic `kpi_card` block renderer (`.rv-kpi-*`)
were converted to the same card/row/icon/body/divider/footer anatomy as
`.iv-kpi`/`.sa-kpi`/`.fo-kpi`/`.la-kpi`/`.tp-kpi`. Hardcoded hex colors in
the converted cards were replaced with CSS variables. `report-viewer`'s
footer now renders 0–3 stats depending on what the underlying metric block
actually has (no hardcoded 3-column grid) since its data shape is dynamic
per `MetricRegistry` entry. `product-kpi-row.blade.php` (`.bkpi`, product
pages) was intentionally left alone — not a report page, out of scope.

---

## Correction pass on the above (2026-08-20, same day)

Two real bugs and one wrong assumption from the work above were caught by
manual review and fixed:

1. **Broken page layout (sidebar overlapping all content).** Editing
   `topbar.blade.php`'s pending-action link condition (`@if($action['route'])`
   → `@if(!empty($action['url']) || $action['route'])`) without updating the
   matching **closing**-tag condition a few lines below left a `<a href="...">`
   closed by `</div>` for the "Price Override Approvals" bell item. That
   markup renders unconditionally on every page load (only hidden by Alpine
   `x-show`, never removed from the DOM), so the mismatched tag corrupted the
   DOM tree on **every page**, not just the notification dropdown. Lesson:
   when changing an `@if` that opens an HTML tag, always grep for and update
   its matching closing `@if`/`@elseif` a few lines down — they're easy to
   miss because they're not adjacent in the source.

2. **KPI footer was never actually consistent with `.iv-kpi`.** The initial
   pass assumed `.sa-kpi` (and `.fo-kpi`/`.la-kpi`/`.tp-kpi`) already matched
   `.iv-kpi` because the *class names* lined up (row/icon/body/label/val/
   divider/footer/stat/stat-v/stat-l). They didn't: `.iv-kpi-footer` is a
   **vertical list** (`flex-direction:column`, each `.iv-kpi-stat` a
   `justify-content:space-between` row with label left / value right,
   separated by `border-bottom`), while the others used a **3-column grid**
   (`display:grid;grid-template-columns:repeat(3,1fr)`, centered text). Fixed
   by changing `.xx-kpi-footer`/`.xx-kpi-stat` CSS to the vertical-list
   pattern across `sa-`, `fo-`, `la-`, `tp-`, `cc-`, `pm-`, `rv-` — and, since
   the markup order in every file is `<span class="xx-kpi-stat-v">` then
   `<span class="xx-kpi-stat-l">` (value before label), used
   `flex-direction:row-reverse` on `.xx-kpi-stat` rather than touching every
   individual card's markup (dozens of blocks) — this reverses only the
   *visual* order so the label still renders on the left. Also stripped the
   now-stale `style="border-left:...;border-right:..."` inline dividers that
   existed only for the old 3-column grid's middle cell. **The ui-design.md
   skill file's own generic KPI template also shows the 3-column grid** —
   it's stale versus the actual `.iv-kpi` implementation; trust the real
   inventory-valuation.blade.php code over the skill doc for this pattern.

3. **Wrong assumption about which price overrides need owner action.**
   Completed sales with `has_price_override = true` were being treated as
   "pending approval" (Alert fired, dashboard/bell badge counted them,
   Audit tab showed a Pending+Approve state) whenever `price_override_approved_at`
   was null — regardless of how small the override was. The actual business
   rule: `price_override_threshold` (Settings → Price Override) is what
   decides whether an override needs owner action at all. A sale can only
   **complete directly** (never becoming a `HeldSale`) when its override is
   at-or-below the threshold — `UnifiedPos::completeSale()` forces the seller
   into "Hold for Approval" instead whenever any item exceeds it. So every
   completed `Sale.has_price_override` is, by construction, already within
   policy and needs zero owner action — only pending `HeldSale` rows (which
   by definition exceeded the threshold) are real pending approvals. Fixed:
   - `SaleService` no longer fires an `Alert` for completed-sale overrides
     (removed `notifyPriceOverride()` and its 3 call sites) — only
     `UnifiedPos::holdSale()`'s alert (for actual holds) remains.
   - Topbar bell and `OwnerActions` dashboard widget no longer count/list
     completed-sale overrides as pending actions — only `HeldSale::pendingApproval()`.
   - Price Audit tab: a `source === 'sale'` row with `discount_pct <=`
     `SettingsService::priceOverrideThreshold()` renders a neutral
     "Override — No Action Needed" pill (no button) instead of
     Pending+Approve. The rare edge case where a completed sale's pct
     somehow exceeds the threshold (a path that bypassed the normal guard)
     still shows real Pending+Approve — the check isn't source-based, it's
     threshold-based, so it stays correct even if that edge case occurs.
     `SalesAnalytics::getPriceOverrideThresholdProperty()` exposes the
     setting to the Blade template.
   - `HeldSale` rows are unaffected — they always need real action, since
     becoming a hold in the first place already means they were over threshold.

**Operational note:** `php artisan view:cache`/`view:clear` only affects
compiled Blade views — it does **not** reset PHP opcache. If a code change
to a `.php` class (not a `.blade.php` file) doesn't appear to take effect
against the running `php artisan serve` dev server (e.g. a
`PropertyNotFoundException` for a method that demonstrably exists via
`php artisan tinker`), the dev server process itself is holding a stale
opcache and needs to be restarted — clearing view cache alone won't fix it.

---

## POS price-override threshold bypass + broken toast (2026-08-20, same day)

### Bug 1 — silent bypass of the price-override threshold (UnifiedPos)

`app/Livewire/Shop/Sales/UnifiedPos.php::openEditItem()` has two branches
depending on the cart line's source. The `shop` branch correctly re-fetches
the product's real catalog prices from the DB before reopening the edit
modal. The `warehouse` branch did not — it set
`stagingProduct['selling_price']`/`['box_price']` to `$item['price']`,
i.e. **the cart line's own already-discounted price**, instead of the true
catalog price from `$this->warehouseStock`.

Effect: adding a warehouse item with an above-threshold override correctly
set `requires_owner_approval = true` (via `openAddItem()`, which was always
correct). But if the seller then reopened that same cart line via the
pencil/edit icon and saved again — even with no further changes — the
discount-vs-original recomputation in the shared save path (~line 745)
compared the discounted price against itself (0% diff), silently clearing
`requires_owner_approval` back to `false`. `openCheckout()`/`completeSale()`
then let the sale go straight through with **no hold, no owner approval,
no error** — a full bypass of the `price_override_threshold` business
setting for any warehouse-sourced cart line that got re-edited.
`shop`-sourced lines were never affected (their edit path was already correct).

Fixed by pulling the true prices from `$this->warehouseStock` (falling back
to a fresh `Product::find()` lookup if the product isn't in the current
stock list) instead of `$item['price']`.

### Bug 2 — the warning toast for this exact case rendered blank

Even with Bug 1 fixed, `openCheckout()`'s block (`$this->dispatch('notification', ['type' => 'warning', 'message' => '...'])`)
produced no visible feedback. Root cause: Livewire wraps a single
positional array argument to `dispatch()` as `event.detail = [{...}]` (an
array containing the assoc array), not `event.detail = {...}` directly.
`unified-pos.blade.php`'s local toast handler read
`$event.detail.message`/`$event.detail.type` straight off the array, which
don't exist on an array — so every toast on this page rendered with
`msg: undefined, type: undefined` (blank text, default blue instead of the
intended color). Fixed by changing the handler to
`toast($event.detail)` and unwrapping `Array.isArray(detail) ? detail[0] : detail`
inside the `toast()` function itself.

**This local toast stack only exists on two pages**:
`unified-pos.blade.php` (live, `/shop/pos`) and the orphaned
`point-of-sale.blade.php` (unreferenced by any route — left untouched, same
convention as other orphaned components noted elsewhere in this file).
**No other page in the app has any listener for `dispatch('notification', ...)`
at all** — `layouts/app.blade.php` has no global toast/flash handler, and no
other blade file implements one locally. Every `$this->dispatch('notification', ...)`
call outside these two POS pages (there are many — `OwnerActions`,
`SalesAnalytics`, etc.) is dispatched into the void with zero visual
feedback today. This is a known, larger, pre-existing gap — flagged here
but not fixed, since building an app-wide toast system is a separate,
substantial task, not a bug-fix-sized change.

### Verification method

Reproducing this interactively is awkward (toasts are ephemeral, ~3.8s
lifetime, and login credentials for test users aren't something Claude
should type into a login form). Verified instead via
`mcp__claude-in-chrome__javascript_tool`: attached a raw
`window.addEventListener('notification', ...)` to capture `event.detail`
directly (confirmed the array-wrapping), then called
`Livewire.all().find(c => c.name === 'shop.sales.unified-pos').$wire.openCheckout()`
and inspected `Alpine.$data(toastContainerEl).toasts` immediately after —
confirmed `msg`/`type` were `undefined` before the fix and correctly
populated after.

---

## Global toast system + seller notifications + Audit Trail table cleanup (2026-08-20, same day)

### App-wide toast system (closes the gap noted above)

Moved the toast stack out of `unified-pos.blade.php` and into
`layouts/app.blade.php` (inside `<body>`, before `@yield`/sidebar) so
**every** `$this->dispatch('notification', ['type' => .., 'message' => ..])`
call app-wide now renders a toast, not just the two POS pages. Same
array-unwrapping fix as before (`Array.isArray(detail) ? detail[0] : detail`).
Positioned at `top:calc(var(--topbar-height) + 12px)` instead of a hardcoded
`72px` so it sits just under the topbar consistently regardless of any
future topbar height change. `unified-pos.blade.php`'s local copy was
deleted — **do not add a local toast stack back into any page**; it would
double-fire alongside the global one since both listen on `window`.
`point-of-sale.blade.php` (orphaned, unreferenced by any route) still has
its own local copy — left alone, harmless since it's dead code.

### Seller notification on HeldSale approve/reject

`SalesAnalytics::approveHeldSale()`/`rejectHeldSale()` already wrote
`ActivityLog` rows (`action: held_sale_approved`/`held_sale_rejected`,
`entity_type: 'HeldSale'`) — that part pre-dated this change. What was
missing: the seller never saw them anywhere. Fixed by wiring these into the
existing Topbar "Activity" notification feed (the same mechanism that
already notifies shop managers of `transfer_approved`/`transfer_rejected`):

- `ActivityLog::humanLabel()` / `colorKey()` — added
  `held_sale_approved` (green, "Price Override Approved") /
  `held_sale_rejected` (red, "Price Override Rejected").
- `ActivityLog::iconKey()` — `entity_type === 'HeldSale'` → `'tag'`.
  `topbar.blade.php`'s icon `@if` chain got a matching `'tag'` SVG case
  (same price-tag path already used in `owner-actions.blade.php`).
- `ActivityLog::actionUrl()` — `HeldSale` entity → `route('shop.pos')` for
  shop managers (they can see/resume held sales from the POS page itself;
  there's no dedicated held-sale detail page), `owner.reports.sales?activeTab=audit`
  otherwise.
- `Topbar::notifiableActions()` — added the two new action strings.
- `Topbar::getActivityNotificationsProperty()` — the `isShopManager()`
  branch used to be a single AND-chain scoped only to Transfers for that
  shop. Restructured to `where(fn($q) => $q->where(transfer conditions)->orWhere(heldsale conditions))`
  so a seller sees both their shop's transfer updates AND decisions on
  **their own** holds (`HeldSale::where('seller_id', $user->id)`) — never
  other sellers' holds.

Verified live (logged in as a shop_manager test user, not by typing an
owner password): the bell's Activity tab correctly showed "Price Override
Rejected · Jean-Pierre Habimana · HOLD-0001", tag icon, red, clickable
through to `/shop/pos`, for a hold that user's own account had submitted.

### Price Audit Trail table (`sales-analytics.blade.php`, Audit tab)

- `.sa-tbl thead tr { background:var(--bg) }` and `.sa-tbl tfoot tr { background:var(--bg) }`
  removed — matches the ui-design.md rule ("never background on thead tr")
  and how `.iv-table` (inventory report) already does it; this class is
  shared across every table on this page (Ledger/Sellers/Payments/Credit
  tabs too), so the cleanup applies everywhere at once.
- Audit table `<colgroup>` rebalanced: Shop/Seller 140→175px and Approved
  165→180px (both were visibly cramped/wrapping), taking the difference
  from Reason 165→130px (now holds less content, see next point) and small
  trims off Qty/Original/Margin. `min-width` 1360→1350.
- The rejected-hold reason used to be duplicated: once under "Reason" as
  `Rejected: {{ reason }}`, again implied by "Rejected by X" under
  "Approved". Removed it from the Reason column; it now shows as a small
  sub-line directly under the "Rejected by X" pill in the Approved column
  where it actually belongs contextually.

Not independently re-screenshotted after this pass (would have required
logging in as the owner, and typing that password isn't something Claude
does) — verified via successful `php artisan view:cache` compilation and
direct review of the resulting markup/CSS against the design-system rules
cited above.

---

## Correction: Approved-column overflow + unneeded approval button (2026-08-20, same day)

The "not independently re-screenshotted" caveat above bit us: the Approved
column fix shipped with a real bug, caught by the user.

### Approved column pill overflow

`white-space:normal` on the status pills (`Rejected by X`, `Owner`, approver
name) technically "worked" but only in the sense that the browser now had
room to wrap — except `table-layout:fixed` + a fixed `<col>` width don't
auto-clip an inline child that doesn't wrap, so a long pill like "Rejected
by Jean-Pierre Habimana" visually spilled into the Reason column next to it
instead of staying inside its own cell. Fix this time: **widen the column
enough that the pill fits on one line** instead of forcing a wrap (see
colgroup below). Reverted `white-space:normal`/`max-width:0` back to the
pills' normal `nowrap` sizing now that the column is wide enough.

Final `sa-audit-tbl` colgroup: Date&Time 130 · Sale#/Product 210 ·
Shop/Seller 160 · Qty 130 · Original 90 · Actual 95 · Discount 100 ·
Margin 80 · Reason 145 · Approved 280 (`min-width:1420px`).

### Reason column content moved back

Also reverted the previous session's decision to show the rejected-hold
reason as a sub-line under the "Rejected by X" pill in the Approved column.
The user pointed out Approved was now carrying content that belongs in
Reason, while Reason sat empty. Rejection reason now renders in the Reason
`<td>` itself (in red, replacing the original price-change reason for that
row — a rejected hold's relevant "reason" *is* why it was rejected, not the
seller's original justification for the discount).

### Checkout modal — "Submit for Owner Approval" showing when it shouldn't

Real bug, not just cosmetic. `unified-pos.blade.php`'s checkout modal
decided whether to show the "Submit for Owner Approval" button using
`collect($cart)->contains(fn($i) => !empty($i['price_modified']))` — **any**
price modification, regardless of size. But `openCheckout()`/`completeSale()`
gate on `requires_owner_approval` (the `price_override_threshold`-based
flag) — a completely different, stricter condition. Net effect: a seller
who made a small, within-policy price tweak would see "Complete Sale" *and*
"Submit for Owner Approval" both offered, implying an approval step that
was never actually required (and clicking it would `holdSale()` a
perfectly completable sale for no reason). Fixed by changing the condition
to `collect($cart)->contains('requires_owner_approval', true)` — the exact
same check the completion path already uses. Verified live: a ~5% discount
(well under the default 20% threshold) now opens the checkout modal
directly with "Cancel — back to cart" as the secondary action, no false
"needs approval" prompt.

Checked the adjacent edge case this raised (resuming an already-*approved*
held sale — does the modal wrongly show the button again since the cart
still carries the old `requires_owner_approval: true` from hold time?): no,
`resumeHeldSale()` already explicitly clears that flag to `false` on every
cart item when `$held->isApproved()`. No fix needed there — confirmed by
reading the code, not by clicking through the resume flow.

---

## Fixed: `sale_items` full-box price storage convention (2026-08-20, same day)

### The bug

For full-box (`is_full_box = true`) sale line items, `original_unit_price`/
`actual_unit_price` are supposed to be **box-total** prices — matching
`line_total`'s own scale — per the convention `createSale()` and
`createMixedSale()`'s shop-item branch already used, and per an explicit
comment already sitting in `ProcessReturn.php`
("For full-box sales: actual_unit_price = box price, not per-item").

Two live write paths didn't follow it: `createWarehouseSale()` and
`createMixedSale()`'s **warehouse**-item branch both stored a **per-item**
price (`round($boxPrice / items_per_box)`) in these same two columns, while
`line_total` on that identical row stayed box-total. Confirmed empirically
against real seeded data (not just by reading code) via `php artisan tinker`
— every existing full-box, warehouse-sourced `sale_items` row had
`actual_unit_price * items_per_box ≈ line_total`, never `actual_unit_price == line_total`
like the shop-sourced convention requires.

Knock-on effects, all from the same root cause:
- **Price Audit Trail** (`SalesAnalyticsService::getPriceAuditLog()`): its
  discount-amount and discount-% math (`$item->line_total + $item->total_discount`)
  mixed a box-total field with a per-item one for these rows, understating
  both the displayed discount and the "% off" by roughly a factor of
  `items_per_box` (e.g. a real ~49% discount showed as "7.4% off").
- **Sale detail / receipt views** (`Sale::groupedItems()`,
  `owner/sales/show.blade.php`'s inline duplicate of the same grouping):
  routed `actual_unit_price`/`original_unit_price` through
  `Product::displayUnitPrice()`, which assumes a per-item input and
  multiplies by `items_per_box` for box display. For warehouse-sourced
  full-box lines this happened to *cancel out* the write-side bug and looked
  fine; it was silently primed to double-count the box price the moment a
  **shop**-sourced full-box sale (box-total, correct) went through the same
  path — none existed in the seeded data yet to prove it, but the code path
  was live and the logic was wrong regardless.
- **`ProcessReturn.php`**: already assumed box-total (per its own comment)
  and was therefore silently computing wrong `boxPrice`/`itemPrice` for
  warehouse-sourced full-box returns before this fix. Needed no code change
  — fixing the write side fixed it automatically.

### The fix

1. `SaleService::createWarehouseSale()` and `createMixedSale()`'s
   warehouse-item branch now store `original_unit_price = $product->calculateBoxPrice()`
   and `actual_unit_price = $boxPrice` (both box-total), matching every
   other full-box write path.
2. `Sale::groupedItems()` and `owner/sales/show.blade.php` no longer call
   `displayUnitPrice()` on `sale_items.actual_unit_price`/`original_unit_price`
   — those are already at the right scale now, use them directly.
   **`Product::displayUnitPrice()` itself was intentionally left unchanged**
   — `SalesAnalyticsService::getTopProducts()`'s `avg_selling_price` computes
   a genuine per-item value independently (`SUM(line_total) / SUM(quantity_sold)`,
   scale-invariant to box/item mode) and still needs the conversion; that
   call site is correct as-is.
3. **One-time historical data correction**, run via `php artisan tinker`
   (not a migration file — this was a bug-fix data correction against dev
   seed data, not a schema change):
   ```sql
   UPDATE sale_items
   SET actual_unit_price = sale_items.line_total,
       original_unit_price = sale_items.original_unit_price * products.items_per_box
   FROM products
   WHERE sale_items.product_id = products.id
     AND sale_items.is_full_box = true
     AND sale_items.actual_unit_price != sale_items.line_total
   ```
   Fixed 46 rows. Ran `php artisan cache:clear` afterward since
   `getPriceAuditLog()`/related analytics are `Cache::remember`-wrapped and
   would otherwise keep serving the pre-fix numbers until TTL expiry.
   **If this ever needs re-running** (e.g. after restoring an older DB
   dump), the `actual_unit_price != line_total` condition is what makes it
   safe to run repeatedly — already-correct rows (any shop-sourced full-box
   line, or anything already fixed) are no-ops.

### Verification

Confirmed via `php artisan tinker` against real rows before and after (not
just formula tracing), and live in the browser: Sales History's expandable
row for a real sale now shows "Nike Air Max Size 42 · Qty 2 · Unit Price
920,000 RWF · Line Total 1,840,000 RWF" (920,000 × 2 = 1,840,000, correct
box price) — previously this would have shown a per-item price that,
multiplied by qty, wouldn't reconcile with the line total at all. Did not
re-verify the Price Audit Trail's corrected percentages against a live
owner screenshot for the same reason noted twice above (would require
logging in as the owner).

---

## "Requires refresh" — tightened wire:poll intervals (2026-08-20, same day)

User asked why UI updates need a manual page refresh, and whether that can
be fixed. **Decision (confirmed with user before implementing): faster
polling, not WebSockets.** Livewire already makes same-page actions
reactive without a refresh (that's how it works); the actual gap is
cross-session staleness — e.g. the owner approves a held sale, but the
seller's already-open tab doesn't know until the next poll or a manual
reload. Considered Laravel Reverb (true instant push) but it requires
running a persistent WebSocket server process alongside PHP, Echo on the
frontend, and broadcasting from every relevant action — too much new
infrastructure for this app's actual scale. The existing app already had
26 files using `wire:poll` at staggered intervals (29s/30s/31s/37s — looks
intentional, avoids every component hitting the server in the same tick);
extended that same pattern rather than introducing something new.

**Changed** (all in the direction of "shorter", nothing removed):
- `topbar.blade.php` notification bell: 60s → **15s** (this is what
  surfaces the "your held sale was approved/rejected" notification — the
  most user-facing case from this session's work)
- `owner-actions.blade.php` (Owner Actions dashboard widget): 30s → 20s
- `sales-analytics.blade.php` (Price Audit tab, root wrapper): 60s → 20s,
  plus the "Live · 60s" label next to the date filter updated to match
- `owner/dashboard.blade.php`'s `business-kpi-row`: 60s → 25s
- `payment-methods-report.blade.php` / `customer-credit-report.blade.php`:
  60s → 30s (lower priority, not part of the approval workflow — kept
  slower deliberately, see tradeoff below), "auto-refreshes every 60s"
  labels updated to match

**Deliberately did not** add polling to dashboard widgets that don't
currently have any (`sales-performance`, `top-shops`,
`revenue-by-category`, `business-snapshot`, `top-performing-shops`,
`expenses-breakdown`, `recent-transactions`, `business-insights` on the
owner dashboard) — each `wire:poll` is a separate background request per
open tab; adding 8 more polling loops on top of shortening existing ones
would meaningfully increase idle server load, which undercuts the whole
reason polling was chosen over Reverb in the first place. If any of these
specifically need to feel live, tighten that one component rather than
blanket-adding poll everywhere.

**Verification note:** could not empirically time a poll firing via
browser automation — the CDP-controlled tab reports
`document.visibilityState: 'hidden'` / never becomes the OS-focused window,
and browsers throttle/suspend JS timers (including Livewire's poll
mechanism) in non-visible tabs. Confirmed instead that (a) the
`wire:poll.15s`-style attributes are actually present in the rendered DOM
(the code change took effect, not just edited-but-uncompiled), (b) no
console errors, and (c) `wire:poll` itself is an extensively-proven
existing pattern in this app (25+ prior usages), not something novel being
introduced. A real user with the tab actually open/visible does not hit
this throttling.


---

### Daily Report — data extension (DAILY_REPORT_EXT_01)
- computeRangeSummary(): additive only. New keys: repayments/expenses split by method, expenses_by_category, withdrawals(+detailed), bank deposits(+detailed), refunds(+detailed). New row columns on all_sales (cash/momo/card/bank_transfer/credit, is_split, has_price_override, sale_time), expenses_detailed (recorded_by_name; payment_method was already selected), repayments_by_customer (cash/momo/bank), deposits_detailed (deposited_by_name).
- New DailySessionService methods: getCashReconciliation(), computeComparisonTotals(), getReportChecks().
- Verification answers:
  - V1 consumers of computeRangeSummary(): Owner\Reports\DailyReportController.php:34, Shop\DailyReportController.php:32, Livewire Owner\Reports\DailyReport.php:124, Livewire Shop\Reports\DailyReport.php:97 (definition DailySessionService.php:206). No others.
  - V2 computeLiveSummary() (DailySessionService.php:78-196) is read-only (selects/sums only, no status check), so it can be called for closed sessions. expected_cash = opening_balance + cash sale_payments + cash credit_repayments (shop+day window, method 'cash') − cash returns (refund_method='cash', is_exchange=false) − cash expenses − cash withdrawals − cash bank deposits (source='cash'). MoMo/bank never affect it.
  - V3 expense scope: by daily_sessions.session_date (not expenses.recorded_at); is_system_generated (Cash Shortage) rows ARE included, and computeLiveSummary() does the same via $session->expenses(). New expense figures reuse expensesDetailed rows, so they match total_expenses by construction. "bank" = every non-cash/non-momo method (bank_transfer + other), so cash+momo+bank always equals the total (live summary's total_expenses_bank is bank_transfer only).
  - V4 credit_repayments: payment_method (PG enum payment_method), customer_id, shop_id, daily_session_id (nullable, added later), repayment_date (UTC timestamp). Range summary scopes repayments by repayment_date window, not by session. In use: cash, mobile_money, bank_transfer. "bank" = NOT IN (cash, mobile_money) so parts sum to total (live summary counts bank_transfer+card).
  - V5 bank_deposits.source is a string: 'cash' | 'mobile_money'; date column deposited_at, linked via daily_session_id (report scopes by session_date). owner_withdrawals.method is PG enum withdrawal_method: cash | mobile_money; date column recorded_at, linked via daily_session_id (scoped by session_date).
  - V6 returns: refund_amount, refund_method (nullable string), is_exchange, shop_id, processed_at (UTC). total_refunds_cash on sessions = refund_method='cash' AND is_exchange=false in the processed_at window. New total_refunds/refunds_detailed exclude exchanges likewise; refunds_detailed.date is the business-timezone date.
  - V7 price override: sales.has_price_override (bool; also sale_items.price_was_modified per line).
  - V8 sales has NO daily_session_id column, so sales can't be "unlinked" and the unlinked_sales check is intentionally not implemented.
- Characterization test proves existing keys unchanged. Queries single-shop day for computeRangeSummary(): 16 → 18 (+owner_withdrawals, +returns; splits/categories derived in PHP from already-loaded rows).
- Tests (tests/Feature/Reports/DailyReportDataTest.php) run against the configured Postgres DB (phpunit.xml defines no test DB; native enums rule out sqlite). They use DatabaseTransactions on a fresh shop + 2020-03-10, never RefreshDatabase — do not switch it.
- Follow-ups: dedicated test database in phpunit.xml/.env.testing; no new indexes needed at current scale (owner_withdrawals/bank_deposits/expenses join via daily_session_id, all indexed); getReportChecks stored_vs_live runs computeLiveSummary per closed session (capped at 62); factories were not created (raw inserts in the test) — ShopFactory is an empty stub.

### Daily Report — screen extension (DAILY_REPORT_EXT_02)
- Owner (odr-) and shop (dr-) Daily Report now show: checks banner + panel, comparison deltas (same weekday last week / previous period), full cash reconciliation (notebook order, other_adjustments line), repayments by method, expenses by category + method column, owner withdrawals, bank deposits, refunds, per-method columns on All Sales.
- New Livewire computed props: reconciliation, comparison, checks. Business Position block still hidden (owner request).

### Daily Report — PDF (DAILY_REPORT_EXT_03)
- Added server-side PDF (dompdf 3.1.4 via barryvdh/laravel-dompdf) alongside the unchanged browser print: routes owner.reports.daily.pdf (`/owner/reports/daily/pdf`), shop.reports.daily.pdf (`/shop/reports/daily/pdf`) → DailyReportController::pdf() in both controllers; shared template resources/views/pdf/daily-report.blade.php (tables-only layout, A4 portrait, 14mm margins; Summary always, Transactions appendix on a new page). "Download PDF" button (`odr-`/`dr-btn-secondary`) next to Print on both Livewire screens.
- Both controllers now build data in private buildReportData(); print() output unchanged — verified byte-for-byte (5 print variants, owner all/shop/transactions + shop summary/transactions, time frozen, snapshots before vs after the refactor were `cmp`-identical) and guarded by DailyReportPdfTest (same view name + view data keys, no new content leaks into print). buildReportData() now also returns reconciliation, comparison, checks, generatedBy (print ignores them).
- Owner `shop` param now validated (`all` | `shop:<digits>`, regex; 404 on unknown id — previously an unknown id printed "Unknown Shop"). Shop controller still uses auth()->user()->location_id and ignores any shop param. Transactions PDF capped at 31 days (validation error on `view`); Summary has no cap.
- P1 CSS var support: YES. dompdf 3.1.4 resolves custom properties: Css/Style.php `parse_var()` (~l.1401, incl. fallback + nested var), `is_custom_property()` (~l.1016). Proven empirically: `:root{--tk:#0e9e86}` + `color:var(--tk)` / `background-color:var(--tk)` / `border-bottom:1px solid var(--bd)` / `var(--missing,#112233)` all emitted the correct PDF colour operators (0.055 0.620 0.525 rg, 0.886 0.902 0.953 RG, 0.067 0.133 0.200 rg). So the template has a `:root` token block (the only hex in that file, values copied from P3); no config/report_pdf.php palette needed. Only solid tokens are used (no rgba `-dim` tokens).
- P2 Fonts: dompdf bundled DejaVu Sans / Sans Mono / Serif + the 14 core PDF fonts. DM Sans is NOT registered (no storage/fonts dir, no installed-fonts.json) → template uses DejaVu Sans (covers "−" U+2212 and accents; verified rendering). `enable_font_subsetting` is set true per-PDF (config default false): ~880 KB → ~30 KB per file.
- P3 Tokens (light theme): resources/css/app.css `:root` (lines ~11-40): --surface #ffffff, --surface2 #f0f2f8, --border #e2e6f3, --text #1a1f36, --text-sub #4a5372, --text-dim #7a81a0, --accent #3b6fd4, --green #0e9e86, --amber #d97706, --red #e11d48.
- P4 Logging: `App\Services\AuditLogger::log([...])` is the write path (fields: action, module, entity_type, entity_id, entity_identifier, details(array cast), old/new_values, status, severity; actor/user_id/name/role snapshot, session_id, device_fingerprint, ip_address, user_agent auto-captured). IP = `request()->ip()` (no dedicated IP helper exists); NOTE bootstrap/app.php:32 `trustProxies(at: '*', headers: X_FORWARDED_FOR|HOST|PORT|PROTO|AWS_ELB)` trusts X-Forwarded-For from ANY sender, so a client can spoof ip_address in the audit log unless the app is only reachable via a real proxy — flagged, not changed here. Downloads log action `report_pdf_downloaded`, module `reports`, entity_type `DailyReport`, details {date_from, date_to, shop_id|null, view, provisional}.
- P5 Shop route: routes/web.php `Route::get('/daily/print', [DailyReportController::class, 'print'])->name('daily.print');` inside `Route::prefix('reports')->name('reports.')` within the shop group `Route::middleware(['auth', CheckRole::class . ':shop_manager,owner', CheckLocation::class])->prefix('shop')` (the route group admits owners, but the controller itself aborts 403 unless isShopManager()). Owner route sits in `middleware(['auth', CheckRole::class . ':owner'])->prefix('owner')`. Both anchors matched exactly; pdf routes inserted directly after each.
- Key figures deltas: comparison data only exists for sales, cash, momo, credit, repayments, expenses — Bank, Owner withdrawals and Cash difference cells show no delta line. "Bank" = total − cash − MoMo − credit (so cells add up; includes card/other, labelled when non-zero). No cost/purchase price/gross profit appears anywhere in the PDF.
- Downloads logged as report_pdf_downloaded.

---

## Cash Register, Close Register & Session History redesign (2026-09-24)

User feedback: the day-close pages "looked AI-generated" — oversized
numbers (42px heroes), full-width buttons, duplicated controls, inline
forms. All three pages were rebuilt on the ui-design.md system.

### Cash Register (`shop.day-close.index` / `shop.session.open`)
- New `App\Livewire\Shop\DayClose\Register` (+ `register.blade.php`,
  prefix `dc-`) owns the whole page: today's session state, the
  open-register **modal** (was an inline form), 4 `.iv-kpi`-anatomy KPI
  cards, the cash-drawer ledger, and a closed-day summary. The wrapper view
  `shop/day-close/index.blade.php` is now just `<livewire:…register />`.
- Header has exactly one primary action (Open / Close register / Daily
  report), a `Record ▾` menu, and History. Don't re-add a Quick Actions
  sidebar or duplicate Close buttons — that duplication was the complaint.
- `OpenSession`, `SessionSnapshot`, `SessionActivityPanel` are now
  **orphaned** (no view references them) — left in place per the
  orphaned-code convention.
- `session-activity-feed` now renders its own card: filter pills
  (client-side Alpine), table, two-step inline Void (no `wire:confirm`).
- `pending-requests`: compact rows; Pay opens a confirm modal
  (`confirmPay`/`payRequest`, `$payingId`), Reject opens a reason modal.

### Record drawer (`livewire/shop/day-close/partials/record-drawer.blade.php`)
- Slide-in drawer holding `add-expense` / `add-withdrawal` /
  `add-bank-deposit`. Open from anywhere with
  `$dispatch('dc-record', { type: 'expense'|'withdrawal'|'deposit' })`;
  closes on the child's `expense-added`/`withdrawal-added`/`deposit-added`
  event or `dc-record-close`. Included on the Register page and in the
  Close wizard (step 2 "Add" buttons).
- The three add components got `public bool $inDrawer` (shows Cancel),
  toasts instead of `session()->flash`, and `#[On]` listeners so their
  "available balance" hints stay fresh.
- The deposit list moved out of `AddBankDeposit` into a new
  `DepositList` component (owns `voidDeposit`).
- Standalone pages (`/shop/expenses/add`, `/shop/withdrawals/add`,
  `/shop/bank-deposits`) still work and reuse the same components.

### Close Register wizard (`close-wizard.blade.php`, prefix `cw-`)
- Rewritten from scratch (was 1,116 lines with a stray `</div>` breaking
  step-4 nesting, the whole CSS block pasted twice, and a floating widget
  using a non-existent `--surface-rgb`). Header now lives in the component;
  `shop/day-close/close.blade.php` is a thin wrapper.
- Layout: compact numbered stepper (completed steps clickable →
  `CloseWizard::goToStep()`, backwards only) + sticky "Closing summary"
  rail (becomes a fixed bottom bar ≤1100px).
- Step 3 has a **Count by denomination** modal (Alpine-only, `wire:ignore`;
  notes 5000/2000/1000/500, coins 100/50/20/10/5/1) that writes the total
  via `$wire.set('actualCashCounted', …)`.
- Step 4 adds the **closing notes** textarea — `CloseWizard::$notes` was
  always submitted to `closeSession()` but had no input before.
- Final submit is a confirmation **modal** (`openConfirm()` →
  `$showConfirm`), not `wire:confirm`. `validateDisposition()` treats an
  empty "send to owner" as 0 (it used to fail `required`).

### Session History (`session-history.blade.php`, prefix `sh-`)
- KPIs aggregate over **all** sessions in scope (one `filter (where …)`
  query), not just the current page. Status filter pills; detail is a
  slide-in drawer (was a centered modal); owner Lock uses a two-step
  inline confirm.
- Bugs fixed: (1) the open-session "Close" link used
  `shop.day-close.close?session=` — that route ignores the param and
  closes *today's* session; now `shop.session.close`. (2) Owner shop
  filter read `request()->query('shop_id')`, lost on every Livewire
  request; now a `#[Url(as:'shop_id')]` property. (3) Detail lookup is
  scoped to the manager's shop (previously any session id could be opened).
- Open sessions show "Live — totals recorded at close": `daily_sessions`
  total columns are only populated on close, so they read 0 while open.

### Gotchas found along the way
- **Global mobile touch-target CSS** (`app.css`, `@media (max-width:640px)`):
  `button, a { min-height/min-width:44px }` plus
  `button:not(.w-9)…{ padding:.625rem 1rem }` (specificity 0,4,1) and
  `table td { padding-left/right:.75rem !important }`. These silently
  inflate compact controls and card-transformed table cells on phones.
  Each component overrides them locally with scoped `!important` rules
  inside its own ≤640px block — do the same for any new compact control.
- Fixed-position drawers must not use `width:100vw` on mobile (it
  includes the scrollbar → 10px off-screen); use `left:0;width:auto`.
- `User::shop()` adds a `location_type` constraint the `shops` table
  doesn't have — use `Shop::find($user->location_id)` instead.
- Blade `@foreach ($x as [$a, $b])` destructuring is avoided in this
  codebase; destructure in a `@php` line inside the loop.

---

## Test database — tests must never touch `smart_inventory` (2026-09-24)

**Incident:** running the whole suite (`php artisan test`) while
phpunit.xml had no test DB let the stock Breeze tests (`RefreshDatabase` →
`migrate:fresh`) **wipe every table in the dev database**. Restored only
partially with `php artisan db:seed` (BootstrapSeeder: users, shops,
warehouse, settings). Products, sales, sessions, customers and
transfers were lost; there was no backup.

**Now:**
- `phpunit.xml` forces `DB_CONNECTION=pgsql`,
  `DB_DATABASE=smart_inventory_test` (`force="true"`).
- `tests/TestCase.php::setUpTraits()` throws before any DB trait runs
  unless the database name ends in `_test`. Don't remove it.
- The app itself (`.env`) still uses `smart_inventory`.
- To migrate the test DB manually:
  `DB_DATABASE=smart_inventory_test php artisan migrate --force`
  (confirm the target with tinker `DB::connection()->getDatabaseName()`).
- 9 stale Breeze tests (Auth/*, ExampleTest, ProfileTest) fail for
  pre-existing reasons (e.g. no `password_reset_tokens` table) — not
  regressions.
- Day-close coverage: `tests/Feature/DayClose/RegisterRedesignTest.php`
  (open → record → count → confirm → close) and `SessionHistoryTest.php`.

**Verification method used for UI work without typing passwords:** a
temporary local-only route calling `Auth::onceUsingId($shopManagerId)`
inside `DB::beginTransaction()`/`rollBack()`, rendering the page to HTML
(sample rows inserted, then rolled back). Screenshots only — follow-up
Livewire requests run as whoever the browser session is. Always delete
the route afterwards and check `git diff routes/web.php` is empty.

---

## Per-shop customer credit (2026-09-24)

**Rule: credit belongs to the shop that gave it.** Before this, a customer
had one global balance and a shop manager could only see/collect credit
from customers *registered* at their shop (`customers.shop_id`) — so a
customer registered at Remera who took credit at Nyamirambo was collectable
only at Remera, and the cash landed in the wrong register.

### Data model
- `customer_shop_balances` (one row per customer × shop): `total_credit_given`,
  `total_repaid`, `total_written_off`, `outstanding_balance`,
  `last_credit_at`, `last_repayment_at`. Model `CustomerShopBalance`,
  `Customer::shopBalances()`.
- `customers.total_credit_given / total_repaid / outstanding_balance` are
  kept as the **sum across shops** — company-wide views (dashboards,
  OwnerActions, GenerateSystemAlerts overdue alerts, owner Customers list,
  POS credit-limit check) read them unchanged. `customers.shop_id` now only
  means "registered at", never "owes".
- **Only write path: `App\Services\Sales\CustomerCreditLedger`**
  (`extend` / `repay` / `writeOff` / `reverse`), which locks the row and
  re-derives the customer totals. Never update balances directly.
- Migration `2026_09_24_000001` backfilled from history (credit sale
  payments by `sales.shop_id`, repayments/write-offs by their `shop_id`),
  reconciled so each customer's rows sum to their prior
  `outstanding_balance`. Anything unattributable shows as
  `unassigned_receivables` in the Daily Report (normally 0).
- `CreditService` / `CustomerCreditAccount` are dead legacy code (never
  called; reference a non-existent `credit_account_id`) — left in place.

### Behaviour (decided with the user)
- **Repay only at the lending shop.** `CreditRepayments` for a shop manager
  lists/limits by that shop's ledger row (ledger figures aliased over the
  customer's so the blade is unchanged). The owner sees company-wide
  balances with an "Owes Remera X · Kimironko Y" breakdown and cannot
  record repayments (no register) — "Collected at shop".
- **POS warns, doesn't block**: `UnifiedPos` / `WarehouseSale` credit
  warning comes from `CustomerCreditLedger::describeOwed()`
  ("Owes this shop … · Owes Kimironko …"). `PointOfSale` is orphaned — untouched.
- **Write-offs are per shop**: `CreditWriteoffs` lists one row per
  customer × shop; `CreditWriteoffService::writeoff($customer, $shopId, …)`.
- **Reports**: Customer Credit report's shop filter and the Daily Report's
  `outstanding_receivables` / `customers_owing_count` use the ledger
  (credit that shop gave), not `customers.shop_id`.
- **Voids/returns reduce debt**: `SaleService::voidSale()` reverses the
  sale's credit portion (note: `voidSale` currently has no UI caller).
  Returns get a **"Reduce debt"** refund method (`refund_method =
  'credit_balance'`), offered only when the linked sale was on credit and
  the customer still owes that shop; applied in `ReturnService::approveReturn()`
  (so large returns only reduce debt once the owner approves). It never
  touches the cash drawer (Daily Report counts only `refund_method = 'cash'`).
  The older `store_credit` option is unimplemented elsewhere — left as is.

Tests: `tests/Feature/Credit/PerShopCreditTest.php` (incl. re-running the
backfill migration against legacy-shaped history).

---

## Warehouse Fulfillment redesign (2026-09-25)

Same principles as the day-close redesign, applied to
`warehouse.sales.fulfillment` (`FulfillmentQueue`, prefix `fq-`).

- **Header in the component** (back button, title, warehouse), pill tabs
  Pending (count) / History. Wrapper view is now just the Livewire tag.
- **3 KPI cards** (`FulfillmentQueue::getStatsProperty()`, always over ALL
  pending orders, never the filtered list): awaiting dispatch (+ boxes,
  balance due), oldest waiting (+ >30 min / >2 h counts), dispatched today
  (+ boxes, transporter vs pickup). On phones (≤640px) the cards move
  *below* the list via flex `order` — the work comes first.
- **Pending orders are compact list rows** (`partials/pending-row.blade.php`)
  inside one card with search + period presets in the card head. Actions:
  icon button for the picking slip + small primary "Dispatch".
- **Dispatch confirmation is a modal** (was an inline strip in the card):
  box list, recipient name, 140px signature pad (canvas 880×280 for crisp
  strokes), balance-due warning. `render()` loads `$confirmingSale`.
- **Scan mode**: one scan card; non-pending lookups (dispatched /
  cancelled / not found) render as left-border notices
  (`partials/lookup-result.blade.php`) instead of solid green/red fills.
- **History**: fixed-layout table (who it was handed to, confirmed by);
  row click opens a **detail drawer** (`$historySale`) with the signature,
  instead of an expanded table row. History only queries when its tab is open.
- Signature-pad / code-format JS stays in the root view's `@script`
  (moving `@script` into an included partial breaks Livewire responses —
  see the comment in the view).

### Payment status is never shown to warehouse staff (2026-09-25, user request)
The redesign first added "Balance due" badges/KPI/modal warning/drawer
row (and fixed a bug where credit counted as paid). The user then asked
that fulfillment NOT mention outstanding balances at all — all of it was
removed along with `isPaidInFull()`; the KPI line now shows transporter vs
pickup split instead. The picking/dispatch slip (`receipt/print.blade.php`
with `$hideAmounts`) no longer shows the "Prices are not shown on this
slip…" note — prices/payments are withheld silently. Don't re-add payment
or credit info to warehouse screens or the slip.

### Signature on pickup is a business setting
`fulfillment_require_signature` (boolean, default **true**, group
fulfillment; migration `2026_09_25_000001`, `SettingsService::fulfillmentRequireSignature()`),
toggle in owner Settings → Fulfillment. Off → the modal hides the
signature pad and `markFulfilled()` accepts no signature
(`fulfillment_signature` stays null); the recipient NAME is always
required. `markFulfilled()` re-reads the setting server-side so a stale
page can't skip a signature that has since become required.

### Bugs fixed
- Authorization errors used `session()->flash()`, which this page never
  rendered — now toasts. Dispatch success also toasts.
- `fulfillment_confirmed` ActivityLog rows now include `user_name`.

Tests: `tests/Feature/Warehouse/FulfillmentQueueTest.php`.

### Live Transactions launcher moved into the topbar (2026-09-25)
The owner's Live Transactions button (`transactions/live-feed`) used to be
a fixed 54px FAB at bottom-right, covering the right edge of page content
(e.g. the last Dispatch button on Fulfillment). It is now a 36px button
next to the notification bell: live-feed renders it with
`@teleport('#lf-launcher')` into a placeholder in `layout/topbar.blade.php`.
The placeholder has `wire:ignore` — without it the topbar's 15s poll
re-render would wipe the teleported button. Verified: one button after
re-rendering both components; clicking still opens the drawer.

**Blade trap (caused a ~2 min app-wide outage while doing this):** Blade
compiles `@directive` text even inside HTML `<!-- -->` comments. An HTML
comment containing the word `@teleport-ed` compiled to a broken
`@teleport` call and 500'd the topbar — i.e. every page. Use `{{-- --}}`
comments in Blade, and never write `@word` in an HTML comment.

---

## Individual-item sales per category (2026-09-25)

**Rule (opt-in):** owner Settings → Sales: master switch
`allow_individual_item_sales` + ticked `individual_sale_category_ids`.
Only ticked categories can be sold by loose item; every other category is
sold by **full box only**. Nothing ticked = everything by box (this used to
mean "all categories" — migration `2026_09_25_000002` ticked every existing
category for shops in that state, so behaviour didn't change on upgrade).
Ticking a parent category covers its subcategories.

**One implementation:** `SettingsService::categoryAllowsIndividualSales()`
— used by `UnifiedPos` (open product, edit cart line, add to cart) and by
`SaleService::assertLooseItemsAllowed()` (server-side guard at the start of
`createSale()` / `createMixedSale()`, throws `DomainException`, shown to the
seller as a toast). Don't re-implement the check inline.

**Fixed along the way:**
- Editing a cart line hard-coded `individual_sale_allowed = true`, so a
  box-only product could be switched to items via the pencil icon.
- When a shop had only opened (partial) boxes, item sales were forced ON
  regardless of category. Now a box-only category with only opened boxes
  left is **blocked** in the POS ("Only opened boxes of X are left, and Y is
  sold by full box only") — the owner decides what to do with that stock.
- `confirmAddToCart()` re-reads the product's category from the DB instead
  of trusting the client-mutable `stagingProduct` flag.
- `PointOfSale` (orphaned) still has its own old inline logic — untouched.

Tests: `tests/Feature/Sales/IndividualItemSalesTest.php`.

### Loose items from WAREHOUSE stock (2026-09-25, user chose this)
Ticked categories can now be sold by item from warehouse stock too (the
POS used to force warehouse lines to full boxes).
- **POS**: warehouse products get the same Full Box / Individual Items
  switch (same `categoryAllowsIndividualSales()` rule). Warehouse stock
  now counts `full_boxes` = SEALED boxes only (it used to count opened
  ones too); tiles show "N items" when no sealed box is left. A box-only
  category with only opened warehouse boxes is blocked, like the shop.
- **Sale** (`SaleService::sellWarehouseLooseItems()` inside
  `createMixedSale()`): takes items from already-OPENED boxes first, then
  the oldest sealed box; sale_items `is_full_box = false`; logged as
  `direct_sale` BoxMovements; stock leaves at sale time like box lines.
- **Latent bug fixed**: full-box warehouse sales (both `createWarehouseSale()`
  and `createMixedSale()`) selected `status IN (full, partial)` and would
  sell an opened box at the full box price. Now `status = 'full'` only.
- **Fulfillment**: one sale line is no longer always one box. Use
  `FulfillmentQueue::packList()` / `packTotals()` / `packLabel()` /
  `packQty()` — rows, KPIs ("To pick", "Handed over"), modal and drawer
  show e.g. "2 boxes + 5 items". Picking slip already rendered item lines
  as "pc" via `Sale::groupedItems()`.
- Transfers already move opened boxes as-is (items_remaining preserved).
- `WarehouseSale` page (`createWarehouseSale`) is still full-box only.

Tests: `tests/Feature/Sales/WarehouseItemSalesTest.php`.

### Cart: boxes AND loose items of one product (2026-09-25)
`UnifiedPos` used to key cart lines by product + source only, and a tile
tap on a product already in the cart opened that line for editing — so
switching to "Individual Items" REPLACED the boxes. Now:
- A tile tap always stages a NEW entry; `confirmAddToCart()` merges it into
  an existing line with the same product + source + mode + unit price
  (`findCartLine()`), adding quantities. Price is in the key so merging
  never re-prices quantities already confirmed at another price.
- The pencil (`openEditItem`) edits that line; switching it into a mode
  that already has a same-price line folds the two together.
- Stock is checked across ALL lines of the product+source
  (`cartStockError()`): box lines ≤ sealed boxes, and
  boxes × items_per_box + loose items ≤ items in stock.
- `SaleService` processes lines in cart order; the whole-cart check
  guarantees an item line opening a sealed box still leaves enough sealed
  boxes for the box line (tested with the item line first).
Tests: `tests/Feature/Sales/MixedModeCartTest.php`.
