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
