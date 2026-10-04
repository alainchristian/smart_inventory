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
   **Update 2026-09-27:** `phpoffice/phpspreadsheet` 1.30.2 → 1.30.7, a
   patch release inside maatwebsite/excel's `^1.30.0`; only composer.lock
   changed. This clears all 9 of its advisories: the two criticals, 5 high
   (DoS / SSRF via crafted files) and 2 medium (XSS in the HTML writer,
   which isn't used here). Exposure was lower than the CVE titles suggest,
   because ReceiveBoxes reads the upload from its server temp path, not a
   user-given filename. The crafted-file DoS issues did apply to uploads,
   though only owners and warehouse managers can upload. Covered by
   `tests/Feature/Inventory/ExcelImportTest.php`, which writes real
   xlsx/xls/csv files, uploads them through ReceiveBoxes and checks the
   preview. `composer audit` still reports 50 advisories in 17 other
   packages (laravel/framework, guzzle, league/commonmark, …); those are
   still open.

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
- **Global mobile touch-target CSS — removed 2026-09-28 (Phase 1 below).**
  `app.css` used to force every `button`/`a` to 44×44px with 10×16px padding
  at ≤640px, and many components still carry scoped `min-height:0 !important`
  overrides against it. Those are now harmless leftovers; don't add new ones.
  `table td { padding-left/right:.75rem !important }` at ≤640px still applies.
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

---

## Shop specialisation & Return to warehouse (2026-09-25)

### Rule
Each shop either sells **All categories** (`shops.sells_all_categories`, default
true — every existing shop migrated as a general store) or **only ticked
categories** (`shop_categories` pivot). A ticked category covers all its
sub-categories. Owner decisions: the same rule applies to everyone (no owner
override), and stock already at a shop outside its categories is **blocked
from sale too**, not just from requests.

- `Shop::sellableCategoryIds()` (null = general store; otherwise ticked and
  descendant ids, memoised per instance), `sellsCategory()`, `sellsProduct()`,
  `sellsLabel()`. Products with no category are never sellable at a
  specialised shop.
- Enforced server-side in `TransferService::createTransferRequest()` and
  `SaleService::assertShopSellsProducts()`, which is called from `createSale`,
  `createMixedSale` and `createWarehouseSale`. Both throw `DomainException`.
- Lists are filtered in RequestTransfer (products for the destination shop),
  UnifiedPos (`loadShopStock`/`loadWarehouseStock`, and `openProductModal`
  refuses), WarehouseSale, and StockLevels (main, KPI and low-stock queries
  exclude it; a separate "Not sold at this shop" card links to returns).
- Owner → Locations → shop drawer: "Sells" segmented field with category
  chips. Saving warns via toast if the shop still holds out-of-category boxes.

### Return to warehouse (the way out for stranded stock)
Transfers only go warehouse→shop, so blocking stranded stock needed a
reverse flow. `StockReturnService`:
- `send()` is used by the shop manager (own shop) or the owner. It goes to
  the shop's default warehouse, opened boxes first, and sets boxes to the
  new `box_status` value **`in_transit`**. That takes them off sale
  everywhere with no query changes, since every stock query filters
  full/partial. Numbers are `RTW-000001`.
- `receive()` is used by that warehouse's manager or the owner. Per box:
  received (moves to the warehouse, `previous_status` restored), damaged
  (moves, DAMAGED), or missing (DAMAGED, stays recorded at the shop). Any
  non-received box sets `has_discrepancy`, and notes are required.
- `cancel()` is used by the shop while the return is in transit and restores
  `previous_status`.
- BoxMovements are `return_sent`, `return_to_warehouse`, `return_missing` and
  `return_cancelled`. Activity actions are `stock_return_sent`, `_received`
  and `_cancelled`.
- Pages:
  - `shop.transfers.returns` (`/shop/transfers/returns`, prefix `sr-`; the
    owner picks the shop via `?shop=`)
  - `warehouse.stock-returns` (`/warehouse/stock-returns`, prefix `wr-`)
  - sidebar links for shop, warehouse and owner
- Migration 000004 uses `$withinTransaction = false`, because Postgres
  `ALTER TYPE … ADD VALUE` can't run inside a transaction.

### Tests
`tests/Feature/Inventory/ShopSpecialisationTest.php` (10 tests). Livewire's
`assertDispatched('notification', fn…)` closure only inspects the *first*
event of that name. To assert on a second toast, check
`$component->effects['dispatches']` directly.

### Known gap (pre-existing, not fixed)
Boxes packed onto a warehouse→shop transfer stay sellable at the warehouse
until the shop receives them. `in_transit` could close this too, but
TransferService wasn't changed in this pass.

### Sub-categories in Owner → Product Categories (2026-09-25)
`CategoryManager` gained a "Parent Category" select (`form_parent_id`).
Before this, `categories.parent_id` existed but nothing in the UI could set it.
- It blocks loops: a category can't go under itself or its own
  sub-categories, and those aren't offered as parents.
- A category that has sub-categories can't be deleted.
- The list is in tree order (paginated in PHP) with indented children and
  an "N sub-categories" pill. The unused nested-set columns
  `left`/`right`/`depth` are still not maintained.
- Bug fixed: `categories.code` is NOT NULL + unique, but the form treated
  Short Code as optional and saved null, so a blank code made category
  creation fail. A blank code is now derived from the name
  (`codeFromName()`, e.g. `BAGS-ACCESSORIES`, `-2` suffix on clash), and a
  typed code is validated as unique.
- `ShopSpecialisationSeeder` (demo data: Remera → Footwear, Nyamirambo →
  Bags & Accessories + Household, Kimironko → all, plus stranded boxes) is
  idempotent.
Tests: `tests/Feature/Inventory/CategoryParentTest.php`.

### Packed transfer boxes are held (2026-09-26)
This closes the gap noted above. Boxes packed onto a warehouse→shop
transfer used to stay `full`/`partial` at the warehouse until the shop
received them. That meant they could be sold there, or packed onto a
*second* transfer, since `packBoxesByProductBarcode()` only excluded boxes
already on the same transfer.
- **Packing:** `TransferService::holdBox()` sets packed boxes to
  `in_transit`. It stores the old status in the new
  `transfer_boxes.box_status_before` column. All pack paths do this:
  `packBoxesByProductBarcode` (the live pack page), `packBoxByBoxCode` and
  `assignBoxesToTransfer`.
- **Receipt:** received boxes move to the shop, and `releaseBox()` restores
  their old status. Damaged boxes become `damaged` (unchanged). Boxes that
  never arrived go back on sale at the warehouse, as before; the transfer
  keeps `has_discrepancy`. Note this differs from stock returns, where a
  missing box is marked damaged.
- **Cancel:** `cancelTransfer()` releases un-received boxes before
  deleting the TransferBox rows. Before this, the unassign condition
  checked `$transfer->status` right after setting it to CANCELLED.
- **Migration `2026_09_26_000001`:** backfills boxes on transfers that are
  approved, in transit or delivered and not yet received. Tested by
  re-running `up()` inside the test transaction.
- **Gotcha:** `Collection::each()` stops at the first callback that
  returns `false`. `fn ($x) => $cond && $this->voidMethod()` returns false,
  so it silently processed only one item. Use a block closure.
- **Reporting side effect:** stock valuation (`Box::available()`) counts
  only full/partial boxes, so boxes on the road (transfers and returns)
  are not in anyone's stock value until they arrive.
Tests: `tests/Feature/Inventory/TransferHoldTest.php`.

---

## Selling loose in packs — sell units (2026-09-26)

**Rule (decided with the user):** counted packs only (pair, half-dozen,
dozen, gross, pack of N). No weight or volume. Each pack has its own price,
pre-filled as size × piece price, and the owner can change it. A per-product
switch, "Sell single pieces", decides whether one piece at a time is also
allowed. The category rule (Settings → Sales, `categoryAllowsIndividualSales`)
still gates all loose selling.

**Stock stays in pieces.**
- `product_sell_units` holds product, name, size (≥2 and < items_per_box,
  unique per product) and price.
- `products.sell_single_pieces` defaults to true.
- A pack line takes `qty × size` pieces from boxes, like any loose line.

**`sale_items` convention for pack lines.** It matches full-box lines,
where prices are per box.
- `quantity_sold` is still in PIECES.
- `actual_unit_price` / `original_unit_price` are per PACK.
- `sell_unit_name` / `sell_unit_size` record the pack.
- A pack spanning boxes splits `line_total` by pieces with cumulative
  rounding, so the rows add up exactly.
- Use `SaleItem::isPackLine()` and `pricePerPiece()` (line_total ÷ pieces).
  Never read `actual_unit_price` as a piece price on a pack line.
- Consumers updated:
  - `Sale::groupedItems()` adds `unit_name`, `unit_size` and `qty_label`
    ("3 Dozen"); the owner sale detail now uses it instead of its own copy
  - receipt / picking slip, which also prints the piece count
  - POS receipt modal, Sales History
  - `ProcessReturn`, which refunds per piece from the line
  - Price Audit discount SQL, which divides by `sell_unit_size`, plus a
    "3 Dozen (36 items)" display

**Server side.** `SaleService::resolveLooseUnit()` looks up the unit by
product + size from the DB. It throws `DomainException` if single pieces
are off or the pack doesn't exist. Only `createMixedSale()` (UnifiedPos)
needs it: `createSale()` is only called from the orphaned PointOfSale
pages, and `createWarehouseSale()` sells full boxes only.

**POS (`UnifiedPos`).**
- `stagingUnitSize` (1 = single piece) with a unit picker. The "Loose"
  toggle is labelled with the pack name when it's the only option.
- Cart lines carry `unit_size` / `unit_name`.
- `findCartLine()` keys on unit too, so boxes + dozens + pieces of one
  product are separate lines.
- `cartStockError()` counts pack lines in pieces.
- `confirmAddToCart()` re-reads the units from the DB.
- The price-override threshold compares against the pack's own price.
- Held carts from before this change have no `unit_size` and are treated
  as single pieces.

**Product form.** Trait `App\Livewire\Products\Concerns\ManagesSellUnits`
(Create/EditProduct) drives the "Selling Loose" card. `saveSellUnits()`
rewrites the rows wholesale; unit ids are never referenced.

**Not done (from the plan):**
- showing stock as "3 boxes + 5 dozen + 4 pcs"
- entering a box size as "12 dozen" when receiving stock

Tests: `tests/Feature/Sales/SellUnitsTest.php`.

### Review fixes (2026-10-04)
- **Shop box lines took opened boxes (money bug).** `createMixedSale()` /
  `createSale()` drew box lines from full + partial boxes FIFO. With an older
  opened box, 1 box sold at 11,000 became 2 rows (5 + 19 pieces) each at the
  box price, a 22,000 total. Now `SaleService::shopBoxesForSale()`: box lines
  take whole **sealed** boxes only (refused if too few); loose lines take
  opened boxes first, like the warehouse path. Both lock rows. Dev data had
  no rows hit by it; production wasn't checked.
- **Returns of loose / pack lines defaulted to "Full Box(es)".** That was
  1 box = 72 soaps for a 1-dozen sale. `ProcessReturn` now defaults to the
  way the line was sold. `boxes_sold = intdiv(pieces, ipb)`, so it can be 0,
  and "Full Box(es)" is disabled then. The line reads "1 Dozen (12 pieces)
  sold". Piece refunds are a share of `line_total`
  (`ProcessReturn::itemRefund()`), so a whole dozen refunds exactly its
  price, not 417 × 12.
- **POS loose stock** used `total_items`, which counts every status
  (damaged, in transit). `getCurrentStock()` / `stockSummaryFor()` gained
  `sellable_items` (full + partial); the POS uses it
  (`UnifiedPos::sellableShopStock()`).
- **Single-piece price is its own field** (`ManagesSellUnits::$singlePiecePrice`).
  Before, the form always saved `selling_price = box ÷ ipb`, so a piece
  premium was wiped on every save. Blank = the box rate. It may not be
  below the box rate.
- **Price ladder (blocking):** per piece, a pack costs ≥ the box rate and
  ≤ the single-piece price (never below the exact box rate × size, so
  rounding can't forbid it). One implementation:
  `ProductSellUnit::problemFor()`, used by the form and the bulk tool.
  Existing packs that break it still sell; the form refuses to save until
  re-priced. On 2026-10-04 that was Dinner Plate Dozen (16,500 < 17,000) and
  Drinking Glass Dozen (8,800 < 9,000).
- **Half box preset** (`ProductSellUnit::presetSize()`): half of the
  product's own box, only for even boxes; not offered twice when it equals
  another preset's size.
- **Bulk "Apply packs"** (owner, Products list): tick rows, then a drawer
  with preset chips + one custom pack, price "pieces × piece price" or "%
  off" (clamped to the ladder), keep / replace a pack of the same size, and
  the single-pieces switch. The preview lists each product's adds / skips
  with the reason. `App\Services\Products\SellUnitBulkApplier`
  (`preview()` / `apply()`): one transaction, one `sell_units_bulk_applied`
  audit entry, owner / admin only. Products in box-only categories are
  updated but flagged ("Box-only category"), like the form allows.
Tests: `SellUnitsTest.php` (box lines, opened-first, damaged stock, returns,
ladder, Half box), `tests/Feature/Products/SellUnitBulkApplierTest.php`.

---

## Responsiveness pass (2026-09-26)

47 views were fixed for phone and tablet across the owner, shop and
warehouse pages and the app shell, using CSS in each view's own style block.

**User rule: tables stay tables.** On phones a table keeps every column and
scrolls sideways inside its card (`overflow-x:auto`, a sensible
`min-width` or `table-layout:fixed` + colgroup). Never collapse a table
into stacked cards or hide columns at any width. Every `*-hide-*` column
rule was then removed on 2026-09-27:
- box list, box show, shop stock, categories, expense categories,
  transporters, reprint search, return to warehouse, session activity
  feed, income statement
- the older phone card layouts (day-close deposit/expense/withdrawal
  lists, close-wizard step 4, activity feed, reprint search, return to
  warehouse) became scrolling tables
- pattern on phones: an `.xx-scroll { overflow-x:auto }` wrapper plus
  `table { width:max-content; min-width:100% }` and
  `th, td { white-space:nowrap }`

Categories, expense categories and transporters used to hide some columns
even on desktop (Code, Products, Company, Contact, Transfers); they now
show them.

**Shared fixes (app.css, needs `npm run build`):**
- dashboard `.card-header` stays a wrapping row
- `.card-btn` / `.snap-panel-link` are exempt from the ≤640px 44px
  touch-target inflation
- `.db-period-controls` wraps an extra control (shop / status select)
  onto its own row instead of overlapping the dates

**Shell:**
- topbar title truncates on phones
- bell dropdown is viewport-fixed at ≤640px
- mobile sidebar uses `100dvh`

**Screenshot method, no passwords:** headless Chrome on Windows won't lay
out below ~500px, so `--window-size=390,…` silently renders a wider page
and crops it. Render the page in a 390px `<iframe>` inside a wrapper HTML
file instead, with a separate `--user-data-dir` per run so parallel runs
don't collide. Auth came from a temporary local-only `?__as=<userId>`
middleware (`Auth::onceUsingId`, loopback `REMOTE_ADDR` only). It was
deleted after the pass; re-create it only for a review and remove it
afterwards.

**Routes whose views didn't exist (500) — resolved 2026-09-27:**
- `owner.returns.index` and `owner.damaged-goods.index` are linked from
  the owner dashboard's OwnerActions widget. They now have wrapper views
  (`resources/views/owner/{returns,damaged-goods}/index.blade.php`)
  embedding the shop components, which already had an owner mode: all
  shops, approve returns, decide the disposition. Owners bypass the
  open-register gate.
- Removed, because nothing linked to them: `owner.users.create` /
  `owner.users.edit` (users use the drawer), `owner.returns.show`,
  `owner.damaged-goods.show`, `shop.sales.show`, `products.index` /
  `products.show`, `warehouse.reports.inventory` /
  `warehouse.reports.transfers`. The orphaned
  `Owner\Products\CreateProduct` still calls `route('products.index')`;
  it is unreachable and left as is.
- Test: `tests/Feature/Owner/OwnerReturnsDamagedPagesTest.php`.

**Still open:**
- unscoped global `table{display:block}` rules at ≤600px in the shop and
  warehouse transfers wrappers

### Topbar page titles (2026-09-27)
The layout renders `<livewire:layout.topbar />` without a title, so every
page used to read "Dashboard". Three pages patched it with a
DOMContentLoaded script, which doesn't fire on wire:navigate.
- `Topbar::TITLES` maps route name → title, and `mount()` resolves it (an
  explicit `pageTitle` still wins; unknown routes fall back to
  "Dashboard"). The JS patches were removed.
- **When you add a page, add its route to `Topbar::TITLES`.**
  `tests/Feature/Layout/TopbarTitleTest.php` fails for any owner / shop /
  warehouse GET route missing from it. Print, PDF, receipt, delivery-note
  and picking-slip pages are excluded, since they don't render the topbar.

---

## Close-flow feedback round (2026-09-28)

- **Managers can reopen their own closed day** (`DailySessionService::reopenSession($session, $user, $reason)`,
  UI `Shop\DayClose\ReopenSession` on the Register's closed state and the
  Session History drawer). Rules: own shop only, reason required (≥5 chars),
  not locked, and only the shop's **most recent** session — the next day's
  opening cash comes from this day's retained cash, so once a later day exists
  only the owner can reopen (owner: any unlocked day, reason optional).
  Reopening soft-deletes the auto "Cash Shortage" expense (re-close books it
  again if still short — previously a stale one survived and skewed the
  re-close), resolves that session's open shortage/surplus alerts, logs the
  previous close's figures in `details.previous_close`, and alerts the owner
  when a manager did it. Tests: `tests/Feature/DayClose/ReopenSessionTest.php`.
- **Non-cash settlement**: Close wizard step 4 shows a Difference column;
  any channel where settled ≠ collected makes closing notes required
  (`CloseWizard::hasSettlementDifference()`).
- **Expense description** defaults to the category name (`AddExpense::updatedCategoryId`,
  swaps only the prefix on category change).
- **Shop dashboard** counts expenses/withdrawals by their session's
  `session_date`, not `created_at`/`recorded_at` (owner FinanceAnalyticsService
  withdrawals still use `recorded_at` — not changed).
- **Price Audit**: `total_discount`/`discount_pct` stay positive-below-list,
  negative-above-list (markup); new keys `price_change`, `change_pct`,
  `discount_amount`, `markup_amount`, `direction`, `max_discount_pct`
  (`SalesAnalyticsService::LINE_LIST_DIFF_SQL`). Approval compares the largest
  per-line discount to the threshold, like UnifiedPos; markups never need it.
- **Tables never wrap cell text** app-wide (nowrap + horizontal scroll in the
  card); drawer/modal Save+Cancel pairs stay side by side on phones.

---

## Owner mobile redesign — Phase 0 bug fixes (2026-09-28)

A mobile review of every owner page (plan agreed with the user: shared mobile
styles go in `app.css`, the global ≤640px 44px touch-target rule gets replaced)
turned up these bugs, fixed before any redesign work:

- **Payment Methods report** divided every amount by 100 (8 places, cents
  assumption) — RWF is stored in whole francs. It also filtered the UTC
  `sale_date` with plain date strings, dropping the whole last day; now uses
  business-timezone bounds (`PaymentMethodsReport::bounds()`).
- **Dashboard widgets** (all 9 that listen to `time-filter-changed`) built
  their ranges with `today()`/`now()` in UTC, so every "day" ran 02:00–01:59
  Kigali time. They now use `Dashboard\Concerns\ResolvesBusinessPeriod`.
  **New period-filtered widgets must use this trait.** It returns
  `Illuminate\Support\Carbon` (a subclass), so widgets may type-hint either
  Carbon class — returning base `Carbon\Carbon` 500'd the owner dashboard
  (`SalesPerformance::loadByDay`). `tests/Feature/Owner/DashboardWidgetsRenderTest.php`
  renders every widget for every preset.
  **Date rules (a second bug from the same change):** `businessPeriodRange()`
  gives UTC bounds for timestamp columns; `businessPeriodDates()` gives the
  local days for DATE columns (`daily_sessions.session_date`) and chart
  buckets; `localDate($utc)` converts. Never `->toDateString()` a UTC bound —
  00:00 Kigali is 22:00 UTC the day before, which made "Yesterday" count the
  previous register day's expenses (owner saw 150,000 spent on a day with no
  expenses). Never pass a business-tz Carbon to a query — Laravel binds its
  wall-clock time. `SalesPerformance` (trend chart) now groups sales by their
  local date/week/month, shows a single day by hour, follows the filter's
  default (Today), and loads on page open: `wire:init` is on the component's
  root — on the `<livewire:>` tag it never reached the page, so the chart sat
  as a skeleton until the period was changed. `TopShops` also
  defaulted to 'month' while the filter showed Today. `BusinessKpiRow`'s
  always-visible today/week/month figures use business bounds too.
- **Products** (`Products\ProductList`, `Owner\Products\ProductKpiRow`): default
  period label 'month' while data was Today; ranges now business-tz; labels
  mapped ("Last 30 Days", not "Last_30"). `Owner\Products\ProductList` is orphaned.
- **Sales Analytics peak hour** grouped by the UTC hour.
- **UTC times shown raw** on owner sale detail, transfer detail, write-offs,
  customer credit and payment methods lists — now `local_time()`.
- Receive Stock upload zone (`display:block`), dashboard `.row-trend-shops` /
  `.row-bottom-four` fixed heights reset at ≤640px (app.css), an unscoped
  `table{display:block}` removed from the owner transfers list (the shop /
  warehouse transfer views still have theirs — later phase), and closed
  slide-in drawers no longer cast their shadow onto the page edge.
Tests: `tests/Feature/Reports/MobilePhase0FixesTest.php`.

## Owner mobile redesign — Phase 1: shared foundation (2026-09-28)

- **44px rule removed** from `app.css`. Controls size themselves; small
  icon-only buttons add `m-tap` (invisible 44px hit area). Verified with
  before/after 390px screenshots of 11 pages across owner / shop / warehouse:
  no layout breakage, compact controls simply stop being inflated.
- **Shared mobile building blocks** (`m-` classes, end of `app.css`),
  documented in `.claude/skills/ui-design.md` §22: `m-kpis` (2-up compact KPI
  grid, `m-kpis-strip` for counts), `m-seg`, `m-filter-bar` /
  `m-filter-toggle` / `m-filter-panel` (inline on desktop, bottom sheet on
  phones), `m-sheet*`, `m-actions` (sticky Cancel+Save), `m-scroll` (edge
  shadows), `m-sticky-first`, `m-empty`, `m-page-head` / `m-dup-title`,
  `m-only` / `m-hide`, plus `--m-fs-*` / `--m-s1..3` scale variables.
  Decision (user): mobile patterns are shared globals, not per-page copies.
- **Gotcha found while building it:** a "show on phones" helper must never
  set `display:… !important` — it beats Alpine's `x-show` inline
  `display:none` and flattens flex/grid elements. `m-only` therefore only
  hides on desktop; `m-hide` only hides on phones.
- ui-design.md fixes: §4.2 no longer drops KPIs to one per row on phones,
  §10.5 card-transform tables marked retired (tables stay tables), §14 drawer
  footer stays a row on phones.
- Global toasts moved into `.app-toasts` (app.css): bottom-centre on phones.
- Pages don't use the `m-` classes yet — that's Phase 3 (rollout).

---

## Daily Report: money traceability + inventory snapshot (2026-09-28)

- **Audit:** within each day the cash reconciliation balances (other_adjustments
  and variances all 0). The gap was BETWEEN days: Nyamirambo's 26 Sep register
  opened with 760,000 while 25 Sep kept 11,055,000 — 25 Sep was closed 39 s after
  26 Sep was opened, so the suggested float came from 24 Sep. 10,295,000 RWF
  untraced; no check caught it.
- New report check `opening_carryover` (critical, `getReportChecks`): a session's
  opening balance must equal the same shop's previous closed session's
  `cash_retained`. Tests: `tests/Feature/Reports/OpeningCarryoverCheckTest.php`.
- **Rule (user decision):** `DailySessionService::openSession()` refuses while an
  earlier day for the shop is still open; the Register shows "Close {date} first"
  instead of Open register (was a warning only).
- **Inventory snapshot** (owner + shop Daily Report, print pages, PDF):
  `InventoryAnalyticsService::getStockSnapshot($shopId, $withCost)`. Same
  valuation as the Inventory report (items_remaining × per-item price over
  full + partial boxes); they must stay equal. Opened boxes are ordinary stock
  (loose sales, exchanges, damage…): shown, never flagged.
  - **Screen:** partial `livewire/reports/partials/inventory-snapshot.blade.php`
    (prefix `isn-`). One half-width card with **no KPI cards**, included as
    the LAST child of the report grid. The grid is `grid-auto-flow:dense`, so
    the card drops into whichever row had only one card (e.g. next to
    "Distribution by Payment Channel"). It is one table: full-width
    single-cell group rows ("By location" for owner all-shops, "By category"),
    a Total row, and a footnote (products, damaged, in transit). Keep it five
    columns so it fits half a card; cost is a sub-line under the value.
  - **Print:** `reports/partials/inventory-snapshot-print.blade.php`, full
    width (`span-2`), because half a print page is too narrow. `variant`
    owner/shop picks the heading style.
  - **PDF (dompdf):** "Inventory snapshot" section after Refunds, retail only.
    The PDF still never shows cost.
  - Both controllers' `buildReportData()` pass `inventorySnapshot`.
  - Cost appears only for the owner with profit on (screen `showProfit`,
    print `profit=1`). Shop managers never get cost.
  Tests: `tests/Feature/Reports/InventorySnapshotTest.php`.


---

## Production speed pass (2026-09-29)

Production: alinox.org on Railway behind Cloudflare, users in Rwanda.

**Measuring.** `App\Http\Middleware\ServerTiming` (first in the `web` group)
adds `Server-Timing: app;dur=…, db;dur=…;desc="N queries"` to every
response, including Livewire updates (DevTools → Network → Timing). Requests
slower than 1 s are logged as `Slow request` with route and query count
(`railway logs … | grep "Slow request"`). In tinker the `app` figure is
wrong, because artisan also defines `LARAVEL_START`.

**Infrastructure** (no code):
- App, Postgres and scheduler moved us-west2 → europe-west4 (Amsterdam) with
  `railway scale --service X eu-west=1 us-west=0`. The Postgres volume
  migration took about 2 min. Backups taken first: `D:\backups\alinox\`
  (production is PG 18; use `docker run postgres:18 pg_dump`).
- `DB_PERSISTENT=true` (new `config/database.php` pgsql `options`) reuses
  the connection across FrankenPHP requests. `/login` db time went 40 → 4 ms.
  `DB_SSLMODE` is now configurable (default `prefer`, unchanged).
- `LOG_LEVEL=warning`.

**Code — rules to keep:**
- **Query counts must not grow with the period or the catalogue.**
  - `BusinessKpiRow` computes each metric's windows and sparkline buckets
    in one `SUM(CASE WHEN col BETWEEN ? AND ?)` query (`windowSums()`).
    That's 14 queries for any period, down from 39–105.
    `DashboardWidgetsRenderTest` caps it at 15.
  - For stock of many products use `Product::stockSummaryFor()` (one
    query). Same rules as `getCurrentStock()` / `isLowStock()`, and
    `StockSummaryTest` checks them against each other.
    `getCurrentStock()` is for single products only.
- **Chart.js / ApexCharts are bundled** (`resources/js/charts.js`, a Vite
  entry loaded as a deferred module). Don't add CDN script tags back.
  Livewire starts on DOMContentLoaded, after deferred modules, so `@script`
  chart code is safe. Never call `Chart` from an inline script that runs
  while the page parses.
- **Fonts are self-hosted** via `@fontsource` (imported in `app.js`). Don't
  add Google Fonts links.
- **Notification bell is its own component** (`Layout\NotificationBell`).
  Its 15 s poll renders only the badge while closed; the lists render
  after `openPanel()`. The poll payload went from ~92 KB to 5 KB.
  **Don't put a `wire:poll` on Topbar**: it re-renders the whole topbar.
- **Polls that usually find nothing must `skipRender()`**: see
  `UnifiedPos::checkForScans()` / `checkApprovals()` (`PosPollingTest`).

**Decided against:** persisting the sidebar with `@persist`. It renders in
about 11 ms and 2.5 KB gzipped per page, but its active-link / open-group
state lives in 106 server-side `routeIs()` checks. Moving those to the
client isn't worth the breakage risk.

**Browser-check gotcha:** the automated Chrome tab often reports
`visibilityState: hidden`, which pauses `requestAnimationFrame`. Charts that
draw via rAF (sales-performance) then look "not drawn" until the tab is
visible. Take a screenshot (it brings the tab forward) before concluding a
chart is broken. Hidden iframes have the same problem.

**Still open:** Cloudflare cache rule for `/build/*` (Edge + Browser TTL
1 year) is a dashboard setting, not code. Assets currently get Cloudflare's
default 4 h `max-age`.

---

## Summary cards unified on the Finance Overview card (2026-09-29)

User asked for every summary-card row to look and behave like Finance
Overview's. **The card is now shared:** `.ui-kpis` / `.ui-kpi*` in
`resources/css/app.css` (a copy of `.fo-kpi`: icon + label/sub, big mono
value, divider, footer of label-left/value-right rows; markup is value
then label, `row-reverse` flips it — never put the label first). Optional
`.ui-kpi-bar` is a 3px track under the value. Grid is `--kpi-cols` (default
4) columns, `--kpi-cols-sm` (default 2) at ≤900px, and `.m-kpis` gives the
2-up compact phone layout. Pages that stack full-width cards on phones
instead add `.ui-kpis-compact` (≤768px: 14px padding, 20px value). The
family is named `ui-kpi-*`, not `kpi-*`, because the `m-kpis` phone rules
match `[class*="-kpi-icon"]` etc. Column counts: keep the page's old grid
class next to `ui-kpis` as a hook and set the variables in the page's own
CSS (e.g. Users `.um-kpis { --kpi-cols:5 }`, Register
`@media (max-width:480px) { .dc-kpis { --kpi-cols-sm:1 } }`), never inline —
an inline value beats the page's media queries. Pages with 2 cards keep 4
columns so every card is the same width. `.ui-kpi-sub` never wraps (it
ends in an ellipsis), so keep subtitles short. **New summary cards use
`ui-kpi`; don't add another per-page copy of the card CSS.**

- **Moved to `ui-kpi` (page-local KPI CSS deleted):** Credit Repayments,
  Warehouse Stock Levels (old 3-column footer), and — with new footers,
  stats agreed with the user — Users, Locations, Shop Stock, Returns,
  Damaged Goods, Daily Close report (gained icons), Customers, Categories,
  Expense Categories, Transporters. `m-kpis-strip` was dropped from these
  (it hides footers).
- **Pages that already looked like it, now on `ui-kpi` too** (their CSS
  copies deleted): Finance Overview, Inventory Valuation, Sales Analytics,
  Loss Analysis, Transfer Performance, Customer Credit, Payment Methods,
  day-close Register, Session History, Fulfillment. Register / Session
  History / Fulfillment / Inventory Valuation had label-first stat rows;
  they were swapped to value-first. Fulfillment keeps its page extras (3
  columns down to phones, cards below the list on phones via `order`, long
  figures wrap inside their cell at ≤768px).
- **Report Viewer (`rv-kpi-*`) deliberately not converted:** its KPI is the
  body of a report block inside `rv-block-card` (28px sans value, top-aligned
  icon, margin-based spacing), not a standalone summary card. Wrapping it in
  `.ui-kpi` would nest a card inside a card.
- **Products and Boxes** (`ProductKpiRow`, `BoxList`, formerly `bkpi`) moved
  to `ui-kpi` in a follow-up the same day. Filler footers replaced: Price
  Overrides → lines changed / discounts / markups; Best Margin → product /
  avg margin / priced below cost; Damaged → intact / damage rate / used up;
  Expiring Soon → already expired / later than 30 days / no expiry date.
  Damaged and Expiring Soon stay clickable filters (the card has a `title`
  tooltip; the subtitle no longer says "click to filter"). Boxes' item and
  expiry figures count sellable (full/partial) boxes only, so the footers
  add up to Sellable Boxes. The `.bkpi` and `.biz-kpi-grid` CSS was deleted
  (`.ops-kpi-grid` is also unused, left in place).
- **Deliberately different, untouched (user decision):** owner dashboard
  `BusinessKpiRow` (`kpi5-`, sparklines), shop / warehouse dashboards
  (`db-kpi`, sparklines), Sales History (`sli-kpi`, bars), Credit
  Write-offs, Settings status strip.
- Footer figures come from one aggregate query per page where possible
  (`COUNT(*) FILTER (WHERE …)`), e.g. `UserList`, `ReturnList::getKpiStats()`,
  `DamagedGoodsList::getKpiStats()`, `CustomerList`. Month windows use
  `business_today()->startOfMonth()->utc()` for timestamps and the local
  date string for `daily_sessions.session_date`.
- Returns have no reject action, so the Pending Approval card shows
  "Pending refund value" instead of a rejected count.
- Transporter "deliveries" = transfers with `shipped_at` + warehouse sales
  with `fulfillment_confirmed_at`.
- `.claude/skills/ui-design.md` is not in the repo checkout, so its KPI
  section wasn't updated.

**Checked in a browser (same day, local session).** Every page above was
rendered as its real role (owner / shop manager / warehouse manager) at
1440px and 390px with the temporary `?__as=` middleware (deleted after).
Pages moved in the second pass were pixel-diffed before vs after. Bugs
found and fixed:
- The move to `ui-kpi` had also deleted Warehouse Stock Levels' table,
  badge, empty-state and pagination CSS (the inventory table rendered
  unstyled). Restored. When deleting a page's KPI CSS, delete only the
  `-kpi` rules.
- Boxes' Damaged subtitle wrapped and pushed its value down; the expiry
  footer didn't add up to Sellable Boxes (it counted damaged / in-transit
  boxes).
- Shop Stock's "In stock" footer row counted healthy products only; now
  labelled "Healthy".
Known leftovers: Warehouse Stock Levels' Total Items footer repeats the
full / partial box counts from the Total Boxes card (the component has no
per-status item counts). Accepted size changes from unifying: Customer
Credit / Payment Methods cards ~13px shorter, Loss / Transfer ~27px taller,
Register / Session History / Fulfillment ~10px taller, and "RWF" units no
longer inherit −1px letter-spacing.

Tests run on `smart_inventory_test` (51 passed): DailyCloseBalance,
PerShopCredit, CategoryParent, ShopSpecialisation, MobilePhase0Fixes,
FulfillmentQueue, RegisterRedesign, SessionHistory, DashboardWidgetsRender.
`php artisan view:cache` compiles every view.

**Screenshot gotchas:** `php artisan serve` runs one PHP worker by default,
and several headless Chromes with `wire:poll` pages queue behind each other
for minutes. Start it with `PHP_CLI_SERVER_WORKERS=4` and wrap each Chrome in
`timeout`. Pages behind the open-register gate were rendered through a
temporary route that opens today's session inside a transaction and rolls
back. Livewire's follow-up requests then see no session and re-render the
gate, so keep `--virtual-time-budget` short (~2.5s). A blank 390px frame
usually means a follow-up Livewire request failed auth (Livewire's error
overlay), so put `__as` in the URL so the referer carries it.

---

## Custom Reports rebuild — Phase 1: bug fixes (2026-09-29)

Full plan (7 phases, agreed with the user): metric classes with one typed
result shape, viewer filter bar + `ui-kpi` summary, rebuilt builder and
library, PDF/Excel/CSV export, and a friendly schedule picker that emails
the PDF. Pin-to-dashboard and block notes are being dropped from the UI.
Phase 1 fixed the existing code only:

- **Edit was broken.** Links went to `builder?reportId=X`, which Livewire
  never read, so Edit opened an empty builder and Save made a copy. Now
  `owner.reports.custom.edit` (`/owner/reports/custom/{report}/edit`); the
  old query link redirects to it.
- **Access:** `SavedReport::isVisibleTo()` (creator or shared) guards view,
  print, the viewer's `mount()` and duplicate. Edit/save is creator-only
  and re-checked in `save()`. `ReportBuilder::$editingReportId` and
  `ReportViewer::$reportId` are `#[Locked]`.
- **Dates:** `ReportRunner::resolveDates()` uses `business_today()` (UTC
  `now()` made "Today" yesterday before 02:00 Kigali). The prior period
  shifts by the inclusive length, so it no longer overlaps the current
  period's first day. The breakdown panel now passes the block's custom
  date override.
- **Block ids** are `'b' . ulid`. Old reports could hold duplicate ids
  (`'b'.timestamp.'_'.count` collided after remove + add), which made one
  block's results overwrite another's. `resolvedConfig()` de-duplicates them
  with a `_2` suffix via `SavedReport::withUniqueBlockIds()`.
- **Wipe tools** (`WipeTransactionalData`, `DangerZone`, `SystemManager`)
  named `report_run_histories` / `report_view_logs`. The tables are
  `report_run_history` / `report_view_log`, and errors were swallowed.
- **CSV:** every cell is quoted, and text goes through `csv_safe()`.
  Numeric strings are left alone.
- `report-viewer.blade.php` declares global functions inside the view.
  They are now wrapped in `function_exists` (a second render in one process
  was a fatal "cannot redeclare"). Phase 3 moves them into metric classes.

Tests: `tests/Feature/Reports/CustomReports/CustomReportsPhase1Test.php`.

## Custom Reports rebuild — Phase 2: metric contract (2026-09-29)

Replaces `app/Services/Reports/CLAUDE.md`, which described the old
`ReportRunner::resolveBlock()` match.

- **One class per metric** in `app/Services/Reports/Metrics/{Domain}/`,
  extending `Metrics\Metric`. `fetch(ReportContext)` wraps an analytics
  service call. `present($raw, $ctx)` returns a `MetricResult`:
  - `headline` {value, type, label}, `stats[]`
  - typed `columns[]` + `rows[]` (+ `totals`), chart `series`
  - `notes[]`, `insight`, and runner-set `comparison` / `status` / `error`
  - value types: money | count | percent | ratio | days | date | datetime | text | bool
  Every renderer (screen, PDF, Excel, CSV) should read this, never the raw
  service arrays. `ReportFormat` formats values for people.
- Each metric declares:
  - `$usesDates` (false = a snapshot; no comparison)
  - `$locations`: none | shop | any. The runner falls back to all locations
    and adds a note when a filter can't apply, e.g. a warehouse on a
    shop-only sales figure. Before, it silently showed everything.
  - `$good`: up | down | neutral. Colours the comparison, and sets which
    way thresholds trip: 'up' trips at or below the threshold.
  - `$periodNote`: what span a snapshot / rolling figure covers
- **Add a metric:** write the class and list it in
  `MetricRegistry::METRICS`. `MetricContractTest` then checks it on every
  location kind. **Never rename an id**: `saved_reports.config` stores them,
  and the test pins the list.
- `ReportPeriod`: business-tz presets (today, yesterday, week, last_week,
  month, last_month, last_30, quarter, year, custom), `prior()` and
  `label()`. `last_month` was offered in the builder but never handled
  (it ran as this month).
- `ReportRunner::run($config, $reportId, $writeHistory, $filters)`.
  `$filters` (period / location / comparison) override the saved defaults
  for one run; that's for the phase-3 viewer filter bar. Each result entry
  has `result` (MetricResult array) and still `data` (raw), because the
  current viewer, CSV and print read `data` until phases 3 and 6. Errors are
  logged, and the page gets a generic message (it used to show the
  exception text).
- **Bugs this fixed (old viewer rendered them wrong or empty):**
  - `inventory_abc_summary` and `inventory_by_location` return grouped
    objects, not row lists
  - `finance_expense_summary` as a table showed the summary keys instead of
    `by_category`
  - the ABC insight read a non-existent `classification` key
  - snapshot metrics were "compared" with themselves
  - history pruning used `skip()->delete()`
- `ops_stock_turnover` is shop-only: the service can't compute it per
  warehouse and returned 0.

Tests: `tests/Feature/Reports/CustomReports/{MetricContractTest,ReportRunnerTest}.php`.

## Custom Reports rebuild — Phase 3: report page (2026-09-29)

`ReportViewer` + `report-viewer.blade.php` (prefix `rv-`) rewritten.
- **Filter bar:** period presets, dates, location, comparison. State lives
  in the URL (`?period=&from=&to=&loc=&compare=`, validated; invalid values
  fall back to the saved defaults). Changing a filter never changes the
  saved report, and "Reset to saved view" restores the defaults. On phones
  the filters are a bottom sheet (`m-filter-panel`).
- **Results are never public Livewire state.** They come from `#[Computed]
  results()`, which reads `Cache` keyed by report config + filters (5 min
  if the period includes today, else 1 h). `wire:init="load"` paints the
  page before running. A cache miss is a run and writes one history row;
  Refresh forgets the cache.
- **Layout:**
  - key findings (bad → warn → good, max 4)
  - every `kpi_card` block as shared `.ui-kpi` cards. The comparison badge
    is coloured by the metric's `good` direction; threshold status shows as
    a dot; notes are a tooltip.
  - tables / charts / text in a 2-column grid. Tables go through
    `partials/result-table.blade.php` (typed columns, totals, `m-scroll`).
- **Charts:** Chart.js from the bundle, drawn from `data-chart` on the
  wrapper, `wire:ignore` canvas, redrawn on commit. Colours come from CSS
  tokens, so they're no longer black. Long category lists become
  horizontal bars. Don't use `@json([...])` with a nested array in an
  attribute: Blade mis-parses it, so use `{{ json_encode(...) }}`.
- **Details sheet** (`m-sheet`) per KPI: all its figures plus the top 5 rows
  of the metric's `$related` metrics. It replaces the old "What's behind
  this?" panel, whose partial was deleted. Pin and notes buttons are gone.
- **History drawer:** last 12 runs with period, location, who ran it and
  headline figures. Choosing one re-applies its filters.
- **Storage** (migration `2026_09_29_000001`): `report_run_history.summary`
  (headlines only) replaces `results`, and `config_snapshot` drops
  `blocks`. `saved_reports.last_results` is no longer written, because
  `markRun()` only counts runs. Old copies were nulled. The scheduled
  command uses `ReportRunner::recordRun(..., scheduled: true)`.
- **Exports:** CSV is rebuilt from `MetricResult` (typed headers, totals,
  notes, `csv_safe`). It used to crash with comparison on, because
  `_comparison` keys were pushed into list-shaped data; the runner now adds
  them only to keyed arrays. Print takes the viewer's filters from the
  query string instead of the stored `last_results`.
- **Top products** labels "(by box)" / "(loose)" when a product has both
  kinds of rows. The sales service keeps them apart, so the same product
  looked listed twice.
- Checked in headless Chrome at 1440px and 390px as the owner, with the
  temporary `?__as=` middleware and a temporary sample report (both
  removed). The 390px wrapper must be served from the app's own origin: a
  `file://` wrapper makes the iframe cross-site, the session cookie isn't
  sent, and `wire:init` never loads.

Tests: `tests/Feature/Reports/CustomReports/ReportViewerTest.php`.

## Custom Reports rebuild — Phase 4: builder (2026-09-29)

`ReportBuilder` + `report-builder.blade.php` (prefix `rb-`) rewritten.
- **Layout:** report settings card, then a two-pane page:
  - catalogue on the left, sticky (`partials/builder-catalogue.blade.php`:
    search, domain pills, display chips, "Added" count, "Ignores period")
  - block list on the right
  Below 900px the catalogue becomes a bottom sheet ("Add block"). An empty
  report shows the 14 templates inline (the modal is gone).
- **Blocks:** drag handle (Sortable.js, now bundled from npm in `app.js` as
  `window.Sortable`, no CDN), move up/down, duplicate, remove, and a
  **live preview** of the headline figure or row count.
  - `#[Computed] previews()`, cached per block settings + default
    period/location
  - `wire:init="loadPreviews"`
  - editing one block re-runs only that block
- **Block drawer** (click a block):
  - title, "Show as", width (not for summary cards)
  - sort by / order / top N. The column list comes from the preview, so
    there's no more typing internal keys.
  - alert levels (summary cards; the hint says "falls to or below" or
    "rises to or above" from the metric's `good`)
  - period and location for this block only. Disabled with the reason
    when they can't apply: `period_note`, "Always covers every location",
    or no warehouses for shop-only figures.
  - `$edit` is bound with `wire:model`; `updatedEdit()` writes it back
    through `sanitiseBlock()`
- **Sanitising:** `canvas` is public, so the browser can send anything.
  `updatedCanvas()` and `save()` rebuild every block from known keys:
  - valid metric, viz and width; title ≤120, text ≤5000
  - overrides only where the metric supports them
  - thresholds only on cards
  - sort key `[a-z0-9_]`, limit 1–100
  Max 40 blocks. Save also validates the custom dates, cron and each
  recipient email.
- Save redirects to the viewer (navigate); the viewer turns
  `session('success')` into a toast.
- Screenshots (1440 / 390, drawer opened through `$wire.selectBlock` from a
  same-origin wrapper page) found the phone header showing Cancel/Save
  twice next to the sticky `m-actions`. The cause: an inline
  `display:flex` beat the ≤640px hide rule. Fixed.

Tests: `tests/Feature/Reports/CustomReports/ReportBuilderTest.php`.

## Custom Reports rebuild — Phase 5: library (2026-09-29)

`ReportLibrary` + `report-library.blade.php` (prefix `rl-`) rewritten.
- Header with one "New report" button, then a "Start from a template" card
  showing 4 templates ("Show all 14" expands, Alpine).
- Toolbar: pill tabs All / Mine / Shared with me, with counts from one
  `COUNT(*) FILTER` query; search over name and description (LIKE wildcards
  escaped, so "%" is literal); sort Recently run (never-run last) / Most
  run / Newest / Name. Search, tab and sort are in the URL.
- **Table** (tables stay tables: `m-scroll` + `m-sticky-first`):
  - report name and description, blocks, default period as a label
    ("Last month", not `last_month`) + "Emailed" badge
  - owner ("You" / name) + "Shared" badge
  - last run (relative, exact time on hover), runs
  - Open, then icon buttons: Edit, Make a copy, Share, Delete. Edit /
    share / delete are creator-only in the UI and the component.
  - Row actions are inline icons, not a dropdown, because a menu inside
    the horizontally scrolling table gets clipped.
- Delete uses the inline confirm row (§13; no `wire:confirm`), and warns
  when the report is shared. Toasts replace `session()->flash`, which the
  page never showed.
- "Make a copy" works for any report you can see (not hidden ones). The
  copy is yours and private.
- **Cache bug found here (phase 3 code):** `SavedReport::markRun()` saved
  the report and so bumped `updated_at`, and the viewer's cache key
  included `updated_at`. Every run invalidated its own cache, so each later
  click (filters, history, details) re-ran the whole report and wrote
  another history row. `markRun()` no longer touches timestamps, and the
  key uses the config itself. It showed up as a flaky test, only when the
  run crossed a second boundary. `ReportViewerTest::
  test_a_run_does_not_invalidate_its_own_cache` reproduces it by moving the
  clock; it fails without the fix.

Tests: `tests/Feature/Reports/CustomReports/ReportLibraryTest.php`.

## Custom Reports rebuild — Phase 6: exports (2026-09-29)

- **One source for every export:** `ReportDocument` (report + filters →
  effective config, period / location / comparison labels, results,
  generated by / at, `kpis()`, `blocks()`, `fileName()`,
  `static findings()`).
  - Results come from `ReportRunner::cached($report, $filters,
    recordHistory: false)`, the same cache the viewer fills, so a
    download right after viewing doesn't re-run. Downloads never count as
    runs.
  - The viewer now uses `cached()` / `forget()` too; the key logic lives
    in the runner only.
- **`ReportExporter::render($doc, pdf|xlsx|csv)`** returns the file's
  bytes; downloads and (phase 7) scheduled emails use it.
- **Route** `owner.reports.custom.export` (`/owner/reports/custom/{report}/export/{pdf|xlsx|csv}`,
  filters in the query string) → `CustomReportController::export()`:
  - visibility check, then an `AuditLogger` entry
    `report_{format}_downloaded` (entity `SavedReport`, details: dates,
    location, comparison)
  - the old `custom.print` URL redirects to the PDF with the same query
    (browser print page and `ExportReportAction::toPrintHtml()` removed)
  - the viewer's Export menu has plain links (PDF / Excel / CSV), not
    Livewire calls
- **PDF** (`resources/views/pdf/custom-report.blade.php`, A4 portrait,
  DejaVu Sans, subsetting, "Page X of Y" via canvas):
  - letterhead: tenant, name, description, then period / location /
    compared with / generated by
  - key findings with tone strips, summary cards 3 per row with the
    comparison line and 3 stats
  - blocks: typed tables with totals, charts as bar tables (dense rows
    when there are more than 15), text blocks with line breaks kept;
    insight and notes under each block
  - **dompdf gotcha:** `var()` works for `color` but NOT for inline
    `background`. The bars and tone strips drew nothing until they used
    literal hex (the `$bg` map in the template, same values as the tokens).
- **Excel** (`app/Exports/CustomReport/`):
  - `Summary` sheet: report details, then one row per headline and stat
    with a unit column, previous period and change %, plus key findings
  - one `TableSheet` per table / chart block. Summary cards stay on the
    Summary sheet even when their metric has a small table.
  - sheet names are ≤31 chars, stripped of `[]:*?/\` and unique
  - number formats by type (money `#,##0`, percent `0.0"%"`, ratio,
    days); dates are real Excel dates (`d mmm yyyy`); bold header and
    total row; frozen header
  - **`WithStrictNullComparison` is required.** Without it Laravel Excel
    writes every 0 as an empty cell.
- **CSV:** unchanged (`ExportReportAction::toCsv`), plus a UTF-8 BOM so
  Excel shows "–" and "×" correctly.

Tests: `tests/Feature/Reports/CustomReports/ReportExportTest.php` (the
workbook is read back with PhpSpreadsheet; the PDF template is checked as
HTML).

## Custom Reports rebuild — Phase 7: emailed reports (2026-09-29)

**What was broken:**
- the schedule was a free-text cron field
- the command ran hourly but `isDue('now')` only matched minute 0, so
  most crons never fired, and one that had never run fired immediately
- the email was plain text with only a link

**Now:**
- **`saved_reports.schedule`** (jsonb; migration `2026_09_29_000002`
  converts crons that fit daily / weekly / monthly at a fixed time) holds
  `{frequency, day, time, format: pdf|xlsx, since}`. `schedule_cron` is no
  longer written or read (kept for reference); recipients stay in
  `schedule_recipients`.
- **`ReportSchedule`:** business-tz `latestOccurrence()` /
  `nextOccurrence()`, `label()` ("Every Monday at 08:00 · PDF"),
  `isDue($lastRunAt)` and `fromCron()`.
  - Due when the latest scheduled moment is later than both the last send
    and `since`. So saving a schedule never sends straight away, and a
    missed run (server down) is sent once when it catches up, never
    repeated.
  - Monthly days are 1–28, so every month has them.
  - Gotcha: `startOfMonth()` resets the time. `nextOccurrence()` for
    monthly lost the hour until `setTime()` was added (caught by a test).
- `SavedReport::emailSchedule()` returns null unless there are recipients
  and a valid schedule; the library badge and viewer pill use it.
- **Command `reports:run-scheduled`** (every 15 min, `withoutOverlapping`;
  `--report=ID --force` to send one now):
  - skips reports whose owner's account is inactive
  - runs the saved defaults and records history via
    `recordRun(..., scheduled: true)`, which doesn't bump `run_count`
  - builds `ReportDocument` + `ReportExporter` file
  - marks `last_scheduled_run_at` **before** mailing, so a mail failure
    can't cause repeats
  - emails each recipient separately; failures are logged per address
- **`App\Mail\ScheduledReportMail`** + `emails/scheduled-report.blade.php`:
  - body has the tenant, name, period / location / comparison, up to 8
    headline figures with coloured change %, key findings, an "Open the
    report" button, and a footer naming the schedule
  - the file is attached. Inline hex styles (email clients ignore CSS
    variables).
  - Gotcha: a Mailable's public properties override view data of the same
    name. `$format` hid the "a PDF" label, so the view variable is
    `$attachedAs`.
- **Builder:** "Email this report" row with an Off / Daily / Weekly /
  Monthly segment, weekday or day-of-month select, time (15-min steps),
  PDF / Excel, and recipients. Recipients are lower-cased, de-duplicated,
  validated, max 10, and required when on. The row shows "Next email Mon
  5 Oct at 07:30 (Kigali time)". Changing when it runs resets `since`;
  changing only the format or recipients keeps it.
- **Production:** real emails need the `MAIL_*` settings on Railway (local
  `.env` uses `MAIL_MAILER=log`) and the scheduler service running
  `schedule:run`.

Tests: `tests/Feature/Reports/CustomReports/ScheduledReportsTest.php`.

**The rebuild is complete** (phases 1–7, all agreed with the user). The
whole suite is in `tests/Feature/Reports/CustomReports/`; run it on the
test DB only.

---

## Number inputs: `<x-number-input>` / `<x-money-input>` (2026-09-30)

User request: every money input fills from the right and adds thousand
separators automatically; then the same for every other numeric field.
**No live view uses `<input type="number">` any more**: money fields use
`<x-money-input>`, everything else `<x-number-input>`. `NumberInputTest`
fails on any `type="number"` outside the orphaned pages.

- `components/number-input.blade.php` is the implementation. It renders
  `<input type="text" inputmode="numeric|decimal" x-data="numberInput({...})"
  x-modelable="numValue">`. `money-input` forwards to it and only adds the
  `money-input` class, so money fields are easy to find. The binding stays
  on the tag (`wire:model…`, `x-model…`); `type` / `min` / `step` are
  dropped.
- Options:
  - `decimals="2"`: report alert levels
  - `signed`: allow a leading "-"
  - `max`: clamped while typing (return and stock quantities). `min` is
    left to validation, because typing 12 passes through 1.
  - `align="center"`: steppers
- **Debounce:** x-modelable bypasses x-model's own debounce. So
  `wire:model.live.debounce.Nms` is rendered as a plain `wire:model`, and
  the input calls `$wire.$commit()` once typing pauses: one request per
  burst, not per key. The value is on `$wire` immediately, so a Save
  clicked meanwhile still sends it. Plain `.live` keeps Livewire's own
  150 ms.
- `Alpine.data('numberInput')` in `resources/js/app.js`:
  - The box shows "12,500"; the model receives the **string** "12500"
    ('' when empty). That is the same value a number input sent, so no PHP
    rules or casts changed. "12500.00" from decimal casts shows as "12,500"
    in integer fields. Max 12 integer digits.
  - The caret keeps the same number of digits to its right. On blur a
    half-typed "12." tidies to "12".
  - It overrides `el._x_forceModelUpdate`. Otherwise x-model writes the raw
    "12500" back into the box on every model change, and re-formatting it
    threw the caret to the end.
  - It fires `change` on blur itself. Browsers skip the native `change`
    once a script has rewritten the value, which broke `wire:model.lazy`.
  - A lone "0" (int properties default to it) is selected on focus, so
    typing replaces it.
  - Without a model (`value="…"` + `x-on:change`), it starts from the
    markup's value. **JS reading `$event.target.value` gets "1,200"**:
    strip commas (see review-transfer).
- CSS: `input.num-input[type="text"][inputmode]` in `app.css`
  (right-aligned, mono, tabular digits), specific enough to beat a page's
  `.xx-input` without `!important`. `.num-center` centres; an inline
  `text-align` still wins.
- **Blade on component tags:** a bare `:attr` is PHP, so Alpine bindings
  are `x-bind:attr` and `x-on:event`. `@if … disabled @endif` inside a tag
  doesn't work; use `:disabled="$phpBool"`.
- Converted:
  - 37 money fields: POS, warehouse sale, credit repayments, write-offs,
    register / record drawer / close wizard, product and pack prices,
    receive stock, damaged goods, expense request, Settings
  - 31 other numbers: quantities, steppers, scan quantities, items per
    box, stock thresholds, denominations, days, override %, lead time,
    report alert levels
- Tested in Chrome on scratch pages covering every binding style
  (`.live` / `.live.debounce` / `.blur` / `.lazy` / Alpine `x-model` /
  no model, decimals, signed, max) and on real pages as owner and shop
  manager at 1440px and 390px. Test: `tests/Feature/Ui/NumberInputTest.php`.

---

## Transfer module redesign (2026-09-30)

Review of all 12 transfer screens (owner / shop / warehouse, 1440px and
390px). Plan agreed with the user: 1 bug fixes → 2 shared foundation →
3 lists as tables → 4 one detail layout (+ owner approve / reject) →
5 one scan screen for pack and receive → 6 request form + delivery note.
One commit per phase.

### Phase 1 — bug fixes
- **Request notes were being overwritten.** `approveTransfer($t, $note)`,
  `rejectTransfer()` and `cancelTransfer()` wrote into `transfers.notes`,
  destroying the shop's request note. They now write the new
  `transfers.review_notes` (approval note, rejection or cancellation
  reason); `notes` is only ever the shop's request. Migration
  `2026_09_30_000001` backfills rejected rows (notes → review_notes) and
  splits cancelled "…\n\nCancelled: reason" rows; its `backfill()` is
  idempotent. Approved rows can't be recovered. Readers updated: review
  page, shop view, owner detail, delivery note.
- Owner detail flagged every in-transit transfer as a discrepancy
  (received < shipped before anything was received). Only counts once
  the transfer is `received`. Damage notes now show on the boxes table.
- Warehouse "View Details" opened `ReviewTransfer`, which redirected away
  for any non-pending transfer. `mount()` now allows every status;
  approve / reject re-check the status.
- Warehouse list clipped the destination shop mid-word (ellipsis doesn't
  work on text inside a flex node).
- Receive on phones: the scan input needed `min-width:0` (the Receive
  button was pushed off-screen); box rows no longer wrap word by word.
- Delivery note: dates were raw UTC (now `local_time()`); product summary
  compared boxes requested with *items* shipped (now boxes requested /
  boxes shipped / items shipped); box status is the box's state on this
  transfer (packed / in transit / received / damaged + note), not its
  current stock status; damaged boxes are excluded from the item total.
- Tests: `tests/Feature/Transfers/TransferFixesTest.php`.
- Note: the receive screen already had Mark damaged / Remove per scanned
  box (they only appear after a scan).

### Phase 2 — shared parts (`resources/views/components/transfers/`)
- `<x-transfers.header title sub back mono dupTitle>` (+ `actions` / `meta`
  slots), `<x-transfers.status :status>`, `<x-transfers.route from to>`,
  `<x-transfers.timeline :transfer>`. All include `<x-transfers.styles />`,
  which prints the shared `tf-` CSS once per page (`@once`): header,
  `tf-btn*`, badges, route, timeline. Page-specific CSS stays in each page.
- `TransferStatus::tone()` → token name (pending amber, approved accent,
  in transit violet, delivered pink, received green, rejected red,
  cancelled text-dim); `cssColor()` now derives from it (the delivered hex
  is gone). The old Tailwind `color()` is left for unconverted callers.
- `Transfer::timeline()` → steps with done / current / todo / stopped, who
  and when. "Packed" is done only once shipped (`packed_at` = packing
  started). Rejected / cancelled end in a red stopped step.
- Wrapper views lose their copied CSS in the phase that rebuilds their
  page, so each commit stays shippable.
- Tests: `tests/Feature/Transfers/TransferComponentsTest.php`.

### Phase 3 — one list for all three roles
- `App\Livewire\Transfers\Concerns\ListsTransfers` + view
  `livewire/transfers/transfers-list.blade.php` (prefix `tfl-`, row partial
  `partials/list-row`). `Owner|Shop|WarehouseManager\Transfers\TransfersList`
  only supply `role()` (owner | shop | warehouse) and `scope()`. The three
  old list views were deleted; the index wrappers are just the component tag.
- URL filters: `?status=` (owner dashboard links `pending` / `discrepancy`
  still work), `?q=` (transfer #, shop, product name / SKU / barcode; LIKE
  wildcards escaped), `?period=` (on requested_at, business tz), `?shop=`.
- Tab counts come from one `count(*) filter (where …)` query and follow the
  search / period / shop filters; tabs run in flow order (pending →
  received, then rejected, cancelled; owner and warehouse also get
  Discrepancies). Summary cards (`ui-kpi`) always cover all of the role's
  transfers: owner 4 (awaiting approval, on the road, received this month,
  discrepancies), warehouse 3 (to approve, to pack, on the road), shop 3
  (awaiting approval, arriving, received this month).
- Warehouse: a "Needs you" table (pending → Review, approved → Pack /
  Continue packing, oldest first) above "Other transfers" when no filter is
  set; the main table then leaves those out.
- Rows: the shop is the main line (owner sees "from <warehouse>" under it);
  on phones the status badge rides in the frozen first column.
- `Transfer::lastEvent()` gives the "Last update" label + time.
- Tests: `tests/Feature/Transfers/TransferListTest.php`.

### Phase 4 — one detail page (+ owner approve / reject)
- `App\Livewire\Transfers\TransferDetail` + `livewire/transfers/transfer-detail.blade.php`
  (prefix `tfd-`) serve `owner|shop|warehouse.transfers.show`; the three
  wrappers are just the component tag. Deleted: the owner's plain-Blade
  show page, `Shop\Transfers\ViewTransfer`, `WarehouseManager\Transfers\ReviewTransfer`
  (+ views).
- Role comes from the user (owner/admin → owner; warehouse manager must own
  the source warehouse; shop manager must own the destination shop → 403).
- Layout: header (number, status, route, one primary action per role +
  delivery note), an alert only when useful (rejected / cancelled reason,
  discrepancy counts, waiting for approval, arrived-to-scan), progress
  timeline, items table (+ Damaged column after a discrepant receipt),
  boxes table with per-box state and damage notes, side cards for notes
  (request + approval note, labelled) and transporter.
- **Owners can now approve / reject** (before, only warehouse managers
  could; the owner's page had no actions). Pending + owner/warehouse: the
  items table becomes "Review quantities" with warehouse stock and an
  editable box count (`<x-number-input max=stock>`), an optional note for
  the shop (→ `review_notes`), Reject opens an `m-sheet` asking the reason
  (required). After approval the page stays and shows the next step
  (warehouse: Pack transfer).
- Gotcha: `validate()` clears the error bag, so run it before adding
  manual errors (`approve()` validates the note first, then quantities).
- Shop: "Receive boxes" (primary) and "Mark as arrived" (in transit).
- Tests: `tests/Feature/Transfers/TransferDetailTest.php`.

### Phase 5 — one scan layout for pack and receive
- Both views rebuilt on shared parts: `<x-transfers.scanner action label
  placeholder button>` (Enter / button → the component's scan method,
  refocus on `quantity-confirmed`, inline feedback from the
  `scan_success` / `scan_error` / `error` / `info` flashes) and
  `<x-transfers.scan-styles />` (`tfs-`). Layout: header → scanner →
  per-product progress table (unfinished first; count in the 2nd column
  for phones) → boxes tables → sticky action bar with counts. Quantity
  prompt and confirmations are `m-sheet`s. PHP scan logic unchanged; the
  pack / receive wrappers are just the component tag (the receive wrapper's
  190-line style block is gone).
- **Pack:** new `TransferService::unpackBox($transfer, $boxId)` (approved
  only): releases the box hold, lowers `quantity_shipped`, deletes the
  TransferBox, clears `packed_at` when nothing is left, logs
  `transfer_box_unpacked`. `PackTransfer::removeBox()` calls it.
  `openShip()` checks transporter + at least one box, then a sheet lists
  products packed short ("Ship anyway"). Ship lands on the detail page.
- **Receive:** `openComplete()` checks at least one scan and a note on
  every damaged box, then a summary sheet (into stock / damaged / missing:
  unscanned boxes are recorded missing). Completing lands on the detail page.
- Bug fixed: both mounts used `redirect()->route(...)->dispatch()`, which
  doesn't exist on Livewire's redirector — opening pack for a shipped
  transfer (or receive for a finished one) crashed. Now `$this->redirectRoute()`
  to the transfer's detail page.
- Tests: `tests/Feature/Transfers/TransferScanTest.php`.

### Phase 6 — request form + delivery note
- Request form (`request-transfer.blade.php`, prefix `rf-`, wrapper is the
  component tag): products are a table (name · SKU · category · items/box,
  "In warehouse" as "N sealed · M opened", new **"In your shop"** column
  from one grouped `boxes` query → `shopStock`, Add). One sticky "Your
  request" card: from / to, basket with steppers (`<x-number-input
  align=center>`), totals (products / boxes / items), note, Send. After
  sending it opens the new transfer's detail page (was the list).
- Blade trap: `word@if(...)` is not a directive (Blade reads it like an
  email address), so its `@endif` closes nothing and the view 500s. Keep a
  space before `@`, or use an inline `{{ cond ? … : '' }}`.
- Delivery note: one print scale (13px body, 22px brand, 11px labels, light
  table heads instead of black bars), copied list-page CSS removed, narrow
  screens stack and scroll the manifest; brand and footer use
  `config('tenant.name')` (was hard-coded "New Shoes Ltd"); footer
  "Printed" time is local.
- Tests: `tests/Feature/Transfers/TransferRequestTest.php`.

**The transfer redesign is complete** (phases 1–6). Suite:
`tests/Feature/Transfers/` (35 tests). Every transfer screen was checked as
owner / shop manager / warehouse manager at 1440px and 390px through a
temporary local-only route that builds sample transfers in a rolled-back
transaction (removed after each phase). Left alone: the orphaned
`WarehouseManager\Transfers\PackTransfer` (+ its CLAUDE.md), the
`components/inventory/transfers/⚡*` files, and the Transfer Performance
report and stock-return pages (out of scope).

---

## Transfer process (2026-10-01) — the rules every transfer follows

Agreed with the user after the redesign: CLAUDE.md had no end-to-end
process, steps had no actor, the shop's numbers were overwritten, lost
boxes went back on sale. **This section is the process; keep it current.**

```
pending → approved → (packing) → ready → in_transit → delivered → received → closed
   ↘ rejected     ↘ cancelled (any time before dispatch)       ↘ discrepancy → resolved → closed
```

| Step (service method) | Who (relative to the transfer) | From | Records |
|---|---|---|---|
| request `createTransferRequest` | shop (or owner) | — | requested_by/at, needed_by, notes |
| approve `approveTransfer($t, $note, [item_id => boxes])` | owner, warehouse | pending | reviewed_by/at, review_notes, **quantity_approved** (quantity_requested never changes; 0 drops a product) |
| reject `rejectTransfer` | owner, warehouse | pending | reviewed_by/at, review_notes |
| pack (boxes) `packBoxesByProductBarcode` / `unpackBox` | owner, warehouse | approved | packed_by/at = packing *started*; never more than approved |
| finish packing `finishPacking($t, [item_id => reason])` | owner, warehouse | approved → **ready** | packing_done_by/at; `short_reason` required for every product packed short |
| reopen packing `reopenPacking` | owner, warehouse | ready → approved | clears packing_done + short reasons |
| dispatch `dispatch($t, $transporterId, $handover)` | owner, warehouse | ready (approved = finishes packing on the way) | shipped_by/at, transporter, handed_to_name, handover_signature, transporter_instructions, expected_arrival_at |
| arrive `markAsDelivered` | **shop** | in_transit | delivered_by/at |
| receive `receiveTransfer($t, $boxes, $receipt)` | shop | delivered (in_transit = arrives first) | received_by/at, received_by_name, receipt_signature; closed_at when nothing is missing / damaged |
| cancel `cancelTransfer` | owner, warehouse (pending / approved / ready); shop only its own pending | | cancelled_by/at, review_notes |

- **One rules layer:** `TransferService::STEPS` (step → allowed statuses,
  roles). `roleFor($user, $t)` = owner/admin → owner, warehouse manager of
  the source warehouse → warehouse, shop manager of the destination shop →
  shop. `can($step, $t, $user)` drives which buttons a screen shows;
  `assertCan()` guards every method (throws `DomainException`). Never
  change a transfer's status anywhere else.
- **History:** every step calls `record()` → `transfer_events` (action,
  from/to status, user, note, meta). `Transfer::events()` is the trail.
  Migration `2026_10_01_000001` backfilled events from old timestamps.
- `TransferItem::boxesToSend()` = approved ?? requested — packing, lists
  and dashboards count this.
- Status `ready` (migration `2026_10_01_000002`, `ALTER TYPE … ADD VALUE`
  outside a transaction).

### Phase 1 (data model + rules) — done
- Columns: transfers needed_by, packing_done_at/by, shipped_by,
  handed_to_name, handover_signature, transporter_instructions,
  expected_arrival_at, delivered_by, received_by_name, receipt_signature,
  cancelled_at/by, closed_at; transfer_items quantity_approved,
  short_reason; transfer_boxes resolution, resolved_by/at,
  resolution_notes (used in phase 5). Signatures are `$hidden` on Transfer.
- Bugs fixed on the way: `packBoxByBoxCode()` compared items to boxes;
  `markAsShipped()` didn't check the status; shipping and arrival weren't
  logged; `roleFor` must compare location ids as ints (users.location_id
  isn't cast).
- Tests that took steps as the wrong person were corrected (the warehouse
  can't confirm a shop's arrival any more). Tests:
  `tests/Feature/Transfers/TransferProcessTest.php`.

### Phase 2 (request + approval) — done
- Request form: optional "Needed by" date (`RequestTransfer::$neededBy`,
  not in the past; recorded on the `requested` event). Shown in the detail
  header ("overdue" in red once past and not received) and in the list's
  Requested column ("late").
- Detail: approve / reject / arrive visibility comes from
  `TransferService::can()`; a Cancel button ("Withdraw request" for the
  shop, only while pending) opens an `m-sheet` asking the reason; packed
  boxes go back into warehouse stock (cancelTransfer releases them).
- Requested vs approved: the review table already shows both; after
  approval the items table has Requested and Approved columns, plus the
  short reason under Packed.
- Tests: `tests/Feature/Transfers/TransferRequestApprovalTest.php`.

### Phase 3 (packing + dispatch) — done
- Pack screen ends with **"Packing done"** (`PackTransfer::openFinish` /
  `finishPacking`): an `m-sheet` asks a reason for every product packed
  short (`shortReasons[item_id]`), then the transfer is `ready` and the
  page goes to the transfer. Shipping no longer happens on the pack screen.
- **Dispatch** is on the detail page (warehouse / owner, status ready):
  `TransferDetail::openDispatch` / `dispatchTransfer` — transporter (pick
  or type; new names are created), "handed to" (required), expected
  arrival (`datetime-local`, business tz → UTC), instructions, and the
  driver's signature via the new **`<x-signature-pad model=…>`**
  (`Alpine.data('signaturePad')` in app.js; PNG data URL). Required unless
  owner Settings → "Require the driver's signature on transfers" is off
  (`transfer_require_signature`, default on, migration `2026_10_01_000003`,
  `SettingsService::transferRequireSignature()`). After dispatch the
  delivery note opens in a new tab and a banner offers "Print delivery
  note" (pop-ups may be blocked). "Back to packing" reopens a ready transfer.
- Warehouse "Needs you" now also lists ready transfers (→ Dispatch).
- Documents: `TransferDocumentController` (`deliveryNote`, `pickingList`),
  allowed only for people on the transfer (`roleFor`; the old closures let
  any warehouse manager open any delivery note). Routes:
  owner/warehouse `…/delivery-note`, `…/picking-list`; shop
  `shop.transfers.delivery-note` (new). Shared print CSS:
  `transfers/partials/print-styles.blade.php`.
- **Picking list** (`transfers/picking-list.blade.php`): approved / packed /
  to pick per product and the oldest available box codes (the boxes
  packing takes), checkboxes, notes, picked-by / checked-by lines.
- **Delivery note**: dated trail with who (requested, approved, packed,
  dispatched, expected, arrived, received), receiver contact (shop
  manager_name + phone) and needed-by, "For the transporter" (instructions,
  expected arrival, handed to), captured signatures on the signature lines.
- Tests: `tests/Feature/Transfers/TransferDispatchTest.php`.

### Phase 4 (arrival + receipt) — done
- Arrival already records who (`delivered_by`, phase 1).
- Receive completion sheet asks **"Received by"** (defaults to the
  signed-in user) and a **signature** (`<x-signature-pad
  model="receiptSignature">`, required while `transfer_require_signature`
  is on — the same setting covers both hand-overs). Stored as
  `received_by_name` / `receipt_signature` via `receiveTransfer(…, $receipt)`.
- **Goods Received Note** (`transfers/received-note.blade.php`,
  `TransferDocumentController::receivedNote`, routes
  `owner|warehouse|shop.transfers.received-note`, 404 before receipt):
  dispatched / arrived / received with who, counts (shipped, into stock,
  damaged, missing), by product (approved, shipped, into stock, damaged,
  missing + packed-short reason), every box's outcome (+ damage note,
  resolution once phase 5 resolves it), transporter and receiver
  signatures. Linked from the transfer page once received.

### Phase 5 (discrepancies) — done
- **Damaged on arrival:** the box moves to the shop, status `damaged`, and
  becomes a `DamagedGood` (`source_type = transfer`, `source_id` = transfer,
  box, items × selling price, disposition pending → the owner decides in
  Damaged Goods). The transfer box gets `resolution = damaged_goods`.
- **Missing on arrival:** the box **stays held** (`in_transit`, off sale
  everywhere — it used to go straight back on sale at the warehouse, i.e.
  phantom stock). The transfer stays open (`closed_at` null) until each one
  is resolved by owner / warehouse with a note —
  `TransferService::resolveBox($t, $boxId, found|lost|received_late, $note)`:
  - found → `releaseBox` (back on sale at the warehouse)
  - lost → box `damaged` + `DamagedGood` (`source_type = transfer_lost`,
    disposition write_off decided now, description names the transporter
    and driver) — counts in Loss Analysis (it includes every non-return source)
  - received_late → moved into the shop's stock, marked received,
    `quantity_received` updated
  Each writes an `issue_resolved` event; the last one closes the transfer
  (`closed` event). `Transfer::openIssues()` = unresolved missing / damaged boxes.
- UI: transfer page "To resolve" card (Found at warehouse / Arrived late /
  Lost → `m-sheet` with a required note; read-only for the shop); box
  states show the resolution and its note. Lists: "To resolve" tab
  (received and not closed) for owner / warehouse, rows show "To resolve"
  + a Resolve button, and the warehouse "Needs you" includes them.
- Migration `2026_10_01_000004` marks pre-existing received transfers'
  missing boxes `found` (they were already put back on sale) and damaged
  ones `damaged_goods`, and closes those transfers.
- Tests: `tests/Feature/Transfers/TransferDiscrepancyTest.php`;
  `TransferHoldTest` updated to the new hold-until-resolved rule.
- Known, unrelated: `ReportExportTest::test_excel_workbook_has_summary_and_table_sheets`
  fails on the 1st of a month (the "this month" trend has one day and no
  Total row) — fails the same without these changes.

### Phase 6 (notifications + overdue alerts) — done
- `TransferService::record()` writes the ActivityLog entries for steps
  nothing else logged (`LOGGED_STEPS`: transfer_ready, transfer_dispatched
  — details carry expected_arrival_at, transfer_arrived,
  transfer_cancelled, transfer_issue_resolved, transfer_closed), and any
  status change resolves that transfer's overdue alerts (`DELAY_ALERTS`).
- **Who hears what:** `NotificationBell::TRANSFER_AUDIENCE` — warehouse:
  requested, approved, rejected, cancelled, arrived, received,
  discrepancy (its own warehouse); shop: approved, rejected, ready,
  **dispatched** (subtitle "expected Tue 14:00"), cancelled, resolved,
  closed (its own shop); owner: every step by someone else. Never your
  own actions. New ActivityLog labels / colours (violet for on-the-road
  steps; the bell view knows `violet`).
- Removed the dead "Transfer Received with Issues" alert in
  ReceiveTransfer (`Warehouse` has no `manager` relation, so it never fired);
  the bell's transfer_discrepancy replaces it.
- **Overdue alerts** (`alerts:generate`, every 5 min,
  `generateTransferDelayAlerts`): Transfer Not Packed (approved > pack
  hours), Transfer Waiting for Transporter (ready > pack hours), Transfer
  Overdue in Transit (critical: past expected_arrival_at, or > transit
  hours without one), Transfer Not Received (arrived > receive hours). One
  open alert per transfer + title, link to the owner's transfer page (the
  old pending alert linked to `#`). Hours are owner settings
  (`transfer_alert_pack_hours` 24, `transfer_alert_transit_hours` 48,
  `transfer_alert_receive_hours` 12; migration `2026_10_01_000005`;
  Settings page next to the signature toggle).
- Tests: `tests/Feature/Transfers/TransferNotificationTest.php`.

### Phase 7 (timeline, report, docs) — done
- `Transfer::timeline()` has 7 steps: Requested → Approved → Packed (who
  finished packing; while packing, who started) → Dispatched ("To
  <driver>") → Arrived (who confirmed; while on the road, the expected
  time) → Received (received_by_name) → **Closed** ("Missing boxes to
  resolve" until then).
- Transfer page **History** card: every `transfer_events` row (label from
  the action + meta: quantities changed, box removed, packed short,
  dispatched to …, received (n damaged, n missing), box: resolution,
  closed), with who, when and the note.
- Transfer Performance report: new **Transporters** tab
  (`TransferAnalyticsService::getTransporterPerformance`, cached 15 min):
  KPIs request → received, on the road (dispatch → arrival), on time (vs
  expected_arrival_at); per transporter: transfers, boxes, avg time on the
  road, on time %, damaged, lost, loss rate.
- Tests: `tests/Feature/Transfers/TransferReportTest.php`.

**The transfer process is complete** (phases 1–7, agreed with the user).
Suite: `tests/Feature/Transfers/` — run on the test DB only. Any new
transfer behaviour goes through `TransferService` (STEPS / assertCan /
record) and belongs in the table at the top of this section.

### Products without a barcode (2026-10-01)
Many products have no barcode, so they couldn't be packed or received in
bulk. Now:
- Pack: the scanner also accepts a **box label** (box code → packs that
  exact box via `packBoxByBoxCode`), and every unfinished product row has a
  **Pack** button (`PackTransfer::packProduct($id)` → same quantity
  prompt). `TransferService::packBoxesForProduct($t, $productId, $qty)` is
  the real method; `packBoxesByProductBarcode()` resolves the barcode and
  calls it.
- Receive: box labels already worked; unfinished rows get a **Receive**
  button (`ReceiveTransfer::receiveProduct($id)` → `openReceiveFor()`).
- Barcode columns and the picking list say "No barcode" instead of blank.

### Dispatch sheet: transporter is a dropdown (2026-10-01)
The transporter was a free-text field with a hidden datalist, so people
typed the driver into "Handed to" and left Transporter empty (the error
then sat above the visible part of the sheet). Now `transporterChoice`
is a select of active transporters (name · vehicle · company) plus "Not in
the list — type a name" (`'new'` → `transporterName`, created on dispatch).
Choosing one fills "Handed to" with its name (editable). A failed dispatch
dispatches `tfd-invalid` and the sheet scrolls to the first error.
