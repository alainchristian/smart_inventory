# Pack Transfer Page — Modern Redesign + Scan Restore

## Claude Code Instructions

> Drop in project root and run: `claude`

\---

## Context

The file to replace is:

```
resources/views/livewire/warehouse-manager/transfers/pack-transfer.blade.php
```

The Livewire component driving it is:

```
app/Livewire/WarehouseManager/Transfers/PackTransfer.php
```

**Do NOT touch the PHP component.** Only replace the blade view.

\---

## What Was Broken

The warehouse manager pack-transfer page lost its scan functionality.
The PHP component has `scanBox()` (scans by box\_code) and the view must
wire up to it correctly. The restored blade must:

1. `wire:model="scanInput"` on the text input
2. `wire:keydown.enter="scanBox"` on the same input
3. `wire:click="scanBox"` on the Scan button
4. `wire:click="removeBox({{ $boxId }})"` on remove buttons
5. `wire:click="addBoxToProduct({{ $item\\\['product\\\_id'] }})"` on Add Box buttons
6. `wire:click="openShipModal"` on the Ship button
7. `wire:click="closeShipModal"` and `wire:click="ship"` in the modal
8. `wire:click="generateScannerSession"` on the phone scanner button
9. `wire:click="closeScannerSession"` on the close button
10. `wire:poll.2s="checkForScans"` div inside the QR card when active
11. `wire:model="transporterId"` on the transporter select

\---

## Step 1 — Replace the file

Copy the contents of `pack-transfer.blade.php` (provided in this repo alongside
this instruction file) into:

```
resources/views/livewire/warehouse-manager/transfers/pack-transfer.blade.php
```

\---

## Step 2 — Verify variable names match the component

Open `app/Livewire/WarehouseManager/Transfers/PackTransfer.php` and confirm:

|Blade uses|Component property/method|
|-|-|
|`$items`|`public array $items = \\\[]` ✓|
|`$assignedBoxes`|`public array $assignedBoxes = \\\[]` ✓|
|`$scanInput`|`public string $scanInput = ''` ✓|
|`$transporterId`|`public ?int $transporterId = null` ✓|
|`$showShipModal`|`public bool $showShipModal = false` ✓|
|`$scannerSession`|`public ?ScannerSession $scannerSession = null` ✓|
|`$showScannerQR`|`public bool $showScannerQR = false` ✓|
|`$phoneConnected`|`public bool $phoneConnected = false` ✓|
|`$availableBoxes`|passed from `render()` ✓|
|`$transporters`|passed from `render()` ✓|
|`scanBox()`|method ✓|
|`removeBox($boxId)`|method ✓|
|`addBoxToProduct($productId)`|method ✓|
|`openShipModal()`|method ✓|
|`closeShipModal()`|method ✓|
|`ship()`|method ✓|
|`generateScannerSession()`|method ✓|
|`closeScannerSession()`|method ✓|
|`checkForScans()`|method ✓|

If any name differs in the actual file, update the blade to match.

\---

## Step 3 — Check the QR code library

The QR section uses `{!! QrCode::size(148)->generate(...) !!}`.
Verify `simplesoftwareio/simple-qrcode` is installed:

```bash
composer show | grep qrcode
```

If not installed:

```bash
composer require simplesoftwareio/simple-qrcode
```

If you prefer to skip QR codes entirely, replace that block with:

```blade
<div style="width:148px;height:148px;background:var(--surface2);border-radius:8px;
            display:flex;align-items:center;justify-content:center;color:var(--text-sub);
            font-size:12px;text-align:center;padding:12px">
    QR unavailable — use manual code below
</div>
```

\---

## Step 4 — Clear caches and verify

```bash
php artisan view:clear
php artisan cache:clear
php artisan view:cache 2>\\\&1 | grep -i "error\\\\|exception" | head -20
```

Fix any errors before finishing.

\---

## What the redesign includes

* **Scan strip** — dark navy gradient bar, prominent mono input,
Enter key + Scan button both call `scanBox()`
* **Transfer items table** — progress bar per product showing
boxes\_assigned / boxes\_requested, Add Box button per row
* **Assigned boxes list** — box code, status chip (full/partial/damaged),
product name, item count, remove button
* **Available warehouse stock** — expandable per-product list of
the top 5 assignable boxes
* **Ship modal** — triggered by openShipModal(), shows summary,
transporter dropdown (wire:model="transporterId"), Confirm \& Ship button
* **Phone scanner QR card** — shown when showScannerQR is true,
wire:poll.2s="checkForScans" polling, connected/disconnected pill
* **Fully responsive** — stacks to single column at 900px,
scan field wraps at 600px, tables scroll horizontally on mobile

---

## Inventory Report (owner/reports/inventory) — Completed Rebuild

The following files were rebuilt as part of the inventory report upgrade:

- `app/Services/Analytics/InventoryAnalyticsService.php`
  — Bugs fixed in: getTopProductsByValue (grouping), calculateStockTurnover (warehouse filter), getAgingAnalysis (moved_at vs received_at)
  — New methods: getPortfolioFillRate, getVelocityClassification, getDaysOnHandPerProduct, getCategoryConcentration, getInventoryMovementTrend, getShrinkageStats

- `app/Livewire/Owner/Reports/InventoryValuation.php`
  — Added activeTab property (overview | valuation | health | replenishment)
  — Added setTab() action
  — Added urgencyFilter property for replenishment tab
  — Added computed properties for all new service methods
  — Auth guard now also accepts admin role

- `resources/views/livewire/owner/reports/inventory-valuation.blade.php`
  — Full rebuild: 4 tabs, 6 headline KPIs, Chart.js movement trend,
    ABC velocity classification, category concentration, aging analysis,
    expiry warning, replenishment urgency table with days-on-hand,
    dead stock capital lock section

Do NOT revert or partially edit these files without reading the inventory
report analysis notes in the project knowledge base.

---

## Custom Report Builder — Completed

**New route prefix:** `/owner/reports/custom`

### New files created

**Backend**
- `database/migrations/2026_03_20_104918_create_saved_reports_table.php` — JSONB config column
- `app/Models/SavedReport.php` — model with `resolvedConfig()` helper
- `app/Services/Reports/MetricRegistry.php` — catalogue of 31 metric blocks
- `app/Services/Reports/ReportRunner.php` — executes a config against analytics services
- `app/Http/Controllers/Owner/Reports/CustomReportController.php`

**Livewire**
- `app/Livewire/Owner/Reports/ReportLibrary.php` — list/manage saved reports
- `app/Livewire/Owner/Reports/ReportBuilder.php` — two-panel builder UI
- `app/Livewire/Owner/Reports/ReportViewer.php` — runs and renders a saved report

**Blades**
- `resources/views/owner/reports/custom/{library,builder,view}.blade.php`
- `resources/views/livewire/owner/reports/report-library.blade.php`
- `resources/views/livewire/owner/reports/report-builder.blade.php`
- `resources/views/livewire/owner/reports/report-viewer.blade.php`

### Architecture decisions

- No raw SQL exposed to users. All data comes from validated analytics service methods.
- Metric blocks are keyed by `metric_id` string. New blocks are added by adding an
  entry to `MetricRegistry::catalogue()` and a case to `ReportRunner::resolveBlock()`.
- `saved_reports.config` is JSONB so block order and visualization choices survive
  schema changes with no migration needed.
- `is_shared` flag makes reports visible to all owner/admin users without
  introducing a separate permissions system.
- `getPaymentMethods` does NOT exist — use `getPaymentMethodBreakdown` in SalesAnalyticsService.
- `getRevenueKpis` returns `total_revenue` and `transactions_count` (not `current`).

### To add a new metric block

1. Add entry to `MetricRegistry::catalogue()` with a unique `id`
2. Add a case to `ReportRunner::resolveBlock()` pointing to the analytics method
3. If the block uses a new viz type, add rendering logic in `report-viewer.blade.php`

---

## Expense Tracking & Day Close Module — Completed

**New route prefixes:** `/shop/day-close`, `/warehouse/expense-requests`, `/owner/finance`

### New database objects

- **Enums (PostgreSQL):** `daily_session_status` (open|closed|locked), `expense_payment_method` (cash|mobile_money|bank_transfer|other), `expense_request_status` (pending|approved|rejected|paid)
- **Tables:** `expense_categories` (seeded with 10 defaults), `daily_sessions`, `expenses` (soft deletes), `expense_requests`
- **Migrations:** `2026_04_16_000001` through `2026_04_16_000005`

### New models

- `app/Models/ExpenseCategory.php`
- `app/Models/DailySession.php` — scopes: open/closed/locked/forShop/forDate; helpers: isOpen/isClosed/isLocked/isEditable
- `app/Models/Expense.php` — SoftDeletes; belongs to DailySession, ExpenseCategory, User, ExpenseRequest
- `app/Models/ExpenseRequest.php` — static generateReference(); scopes: pending/forWarehouse/forShop

### New services

- `app/Services/DayClose/DailySessionService.php` — openSession, computeLiveSummary, closeSession (fires variance alert if |variance| > 5000 RWF), lockSession
- `app/Services/DayClose/ExpenseService.php` — addExpense (fires large-expense alert if > 50000 RWF), voidExpense
- `app/Services/DayClose/ExpenseRequestService.php` — createRequest, approveAndPay (requires open session today), rejectRequest

### New Livewire components

**Shop:**
- `app/Livewire/Shop/DayClose/OpenSession.php` → `livewire.shop.day-close.open-session`
- `app/Livewire/Shop/DayClose/AddExpense.php` → `livewire.shop.day-close.add-expense`
- `app/Livewire/Shop/DayClose/ExpenseList.php` → `livewire.shop.day-close.expense-list`
- `app/Livewire/Shop/DayClose/CloseWizard.php` → `livewire.shop.day-close.close-wizard` (4-step)
- `app/Livewire/Shop/DayClose/PendingRequests.php` → `livewire.shop.day-close.pending-requests`

**Warehouse:**
- `app/Livewire/Warehouse/ExpenseRequests/CreateRequest.php` → `livewire.warehouse.expense-requests.create-request`

**Owner:**
- `app/Livewire/Owner/Finance/DailyCloseReport.php` → `livewire.owner.finance.daily-close-report`
- `app/Livewire/Owner/Finance/FinanceOverview.php` → `livewire.owner.finance.finance-overview`

### Routes

| Route name | URL | View |
|---|---|---|
| `shop.day-close.index` | `/shop/day-close` | `shop.day-close.index` |
| `shop.day-close.close` | `/shop/day-close/close` | `shop.day-close.close` |
| `warehouse.expense-requests.index` | `/warehouse/expense-requests` | `warehouse.expense-requests.index` |
| `owner.finance.daily` | `/owner/finance/daily` | `owner.finance.daily` |
| `owner.finance.overview` | `/owner/finance/overview` | `owner.finance.overview` |

### Gates & Policies

Gates added to `AuthServiceProvider`: `open-daily-session`, `close-daily-session`, `create-expense-request`, `view-finance-reports`

Policies: `DailySessionPolicy` (lock → owner only), `ExpensePolicy` (void → owner or matching shop manager), `ExpenseRequestPolicy` (pay/reject → matching shop manager)

### Key business rules

- One session per shop per calendar date (unique DB index)
- Variance alert fires when `|actual_cash_counted - expected_cash| > 5000 RWF`
- Large-expense alert fires when single expense > 50000 RWF
- ExpenseRequest can only be paid if the target shop has an open session today
- Sessions become immutable once locked (owner action only)
- `expected_cash = opening_balance + cash_sales − cash_expenses_paid_in_cash`

---

## Expense Tracking & Day Close — v2 Additions (Owner Withdrawals + Session Gate)

### New database objects

- **Table:** `owner_withdrawals` (daily_session_id, shop_id, amount, reason, recorded_by, recorded_at, soft deletes)
- **Migrations:** `2026_04_16_000006_create_owner_withdrawals_table`, `2026_04_16_000007_add_refunds_withdrawals_to_daily_sessions`
- **New columns on `daily_sessions`:** `total_refunds_cash` (bigint nullable), `total_withdrawals` (bigint nullable)

### New model

- `app/Models/OwnerWithdrawal.php` — SoftDeletes; belongs to DailySession, Shop, User (recorded_by)
- Added `ownerWithdrawals(): HasMany` relationship to `DailySession`
- Added `scopeUserSelectable` to `ExpenseCategory` — excludes 'Cash Shortage', filters `is_active = true`, orders by `sort_order`

### New service

- `app/Services/DayClose/OwnerWithdrawalService.php` — `recordWithdrawal()`, `voidWithdrawal()`

### Session gate

- `app/Livewire/Concerns/RequiresOpenSession.php` — trait; `checkSession(int $shopId): bool`
  - Owners always bypass
  - State 'previous_open': any open session not from today → must close first
  - State 'no_session': no today session → must open
  - State 'session_closed': today session closed → no further activity
- `resources/views/components/session-gate-blocked.blade.php` — three visual states with action buttons
- Applied to: PointOfSale, ReturnList, ProcessReturn, ReceiveTransfer, DamagedGoodsList

### New Livewire components (shop)

- `SessionManager` — hub: live summary when open, open form when none, closed state; `wire:poll.30s`
- `AddWithdrawal` — records owner cash withdrawal; dispatches `withdrawal-added`
- `WithdrawalList` — sub-component for close wizard step 2; `voidWithdrawal()`
- `SessionActivityFeed` — merged feed of sales/returns/expenses/withdrawals with void buttons
- `SessionHistory` — paginated closed sessions; expandable detail; owner can lock

### Updated cash formula

```
expected_cash = opening_balance
              + cash_from_sale_payments        (sale_payments table, split-payment safe)
              + cash_repayments_collected      (credit_repayments WHERE payment_method='cash')
              − cash_refunds                   (returns WHERE refund_method='cash', is_exchange=false)
              − cash_expenses                  (expenses WHERE payment_method='cash', not voided)
              − owner_cash_withdrawals         (owner_withdrawals WHERE method='cash', not voided)
              − cash_bank_deposits             (bank_deposits WHERE source='cash', not voided)
```

### Updated close wizard

- Step 2: Section A (Operational Expenses) + Section B (Owner Withdrawals)
- Step 3: Full 5-line reconciliation card showing all deductions; variance with three states
- Step 4: `cashRetained` is read-only auto-computed (not an input); Close button uses `--amber`

### New routes

| Route name | URL |
|---|---|
| `shop.session.open` | `/shop/session/open` |
| `shop.session.close` | `/shop/session/close/{session?}` |
| `shop.session.history` | `/shop/session/history` |
| `shop.session.requests` | `/shop/session/requests` |

### Gates & Policies added

- Gates: `manage-daily-session` (shop_manager|owner), `lock-daily-session` (owner only)
- Policy: `OwnerWithdrawalPolicy` (void → owner or matching shop manager)
- Morph map: `'owner_withdrawal' => OwnerWithdrawal::class`

### Key rules

- `AddExpense` uses `ExpenseCategory::userSelectable()` — 'Cash Shortage' never appears in user-facing dropdowns
- `is_system_generated = true` expenses (Cash Shortage) cannot be voided
- `DailySessionService::computeLiveSummary()` uses `sale_payments` table (never `sales.payment_method`)
- Owners bypass session gate in all components (they can sell/return at any shop anytime)
- `computeLiveSummary()` includes cash_repayments as a positive drawer contribution (cash_repayments reduces outstanding credit, adds to drawer)
- Bank deposits (source='cash') reduce expected_cash at the time they are recorded, not at close time
- MoMo repayments, MoMo expenses, MoMo withdrawals, MoMo deposits: tracked in momo_available — do NOT affect expected_cash (drawer only)

## Credit Write-offs Module (added 2026-04-24)

### New table:
- `credit_writeoffs` — permanent records of owner write-off decisions
  Fields: customer_id, shop_id, amount, balance_before, balance_after, reason,
          written_off_by, written_off_at. No soft deletes.

### New column:
- `customers.last_repayment_at` — nullable timestamp, updated on every repayment

### New model:
- `app/Models/CreditWriteoff.php` — BelongsTo Customer, Shop, User(written_off_by)
- `Customer`: hasMany(CreditWriteoff::class) added; `last_repayment_at` in fillable/casts

### New service:
- `app/Services/Sales/CreditWriteoffService::writeoff()`
  Validates owner auth, amount within balance, updates customer.outstanding_balance,
  resolves all open customer alerts via markAsResolved(), writes ActivityLog.

### New setting:
- `overdue_credit_days` (integer, group: credit, default: 14)
  Configured in owner settings blade (Credit Policy section).
  Read via SettingsService::overdueCreditDays()

### New Livewire component:
- `app/Livewire/Owner/CreditWriteoffs.php` → `owner.credit-writeoffs`
  Two-step confirm UI. Owner-only (403 for others).
  Search by name/phone. Inline form per customer row.

### New route:
- `owner.credit.writeoffs` → GET /owner/credit/writeoffs

### Alert integration:
- `GenerateSystemAlerts`: new `generateOverdueCreditAlerts()` method
  Fires alerts for customers with no repayment in overdue_credit_days days.
  Severity: critical if balance >= 100,000 RWF, warning otherwise.
  Matches by title prefix 'Overdue Credit%' to avoid duplicates.

### OwnerActions dashboard:
- New section — overdue customers with no recent repayment
  Links to owner.credit.writeoffs route.

### Navigation:
- Finance sidebar section now includes "Credit Write-offs" link
  openFinance also triggers on owner.credit.* routes

### Key rules:
- Only owner can write off (hard abort(403) for others)
- Write-off is PERMANENT — no soft delete, no undo
- Write-off does NOT create an expense entry
- Write-off does NOT appear in daily session reports
- Partial write-offs supported — owner enters any amount up to full balance
- Two-step confirmation required before any write-off is submitted
- Alert model has no `type` field — use title matching for overdue credit alerts

## Finance Analytics Integration (added 2026-04-27)

### New service:
- `app/Services/Analytics/FinanceAnalyticsService.php`
  Methods: getExpenseSummary, getExpenseTrend, getWithdrawalSummary,
           getCashVarianceSummary, getNetOperatingResult
  Pattern: identical to SalesAnalyticsService (cacheTtl, location filter,
           integer casts, Cache::remember)

### New metric blocks (added to MetricRegistry):
- `finance_expense_summary`    — expenses by category, table + bar_chart
- `finance_expense_trend`      — daily expense line chart
- `finance_withdrawal_summary` — owner withdrawals KPI + table
- `finance_cash_variance`      — shortage/surplus summary KPI + table
- `finance_net_operating`      — true P&L: Revenue − COGS − Expenses, kpi_card

### New ReportRunner cases:
All 5 finance metrics now resolved in resolveBlock() via FinanceAnalyticsService.
FinanceAnalyticsService injected into ReportRunner constructor.

### New ReportTemplates entry:
`business_pl` — "Business P&L Summary" template using all 5 finance metrics
plus sales_revenue, sales_gross_profit, sales_revenue_trend, loss_total.

### FinanceOverview updated:
Now uses FinanceAnalyticsService for computed properties (expenseSummary,
withdrawalSummary, cashVariance, netResult) passed to view.
Net Operating Result strip added at top of /owner/finance/overview.

### Canonical P&L hierarchy (enforced everywhere — never conflate):
```
Gross Revenue      = sum of all sale line totals (before refunds)
Net Revenue        = Gross Revenue − Refunds
Gross Profit       = Net Revenue − COGS                  (SalesAnalyticsService::getGrossProfitKpis)
Operating Profit   = Net Revenue − Operational Expenses  (DailyCloseReport card; FinanceOverview table col)
Net Result         = Operating Profit − Owner Withdrawals (FinanceOverview table col; FinanceAnalyticsService::getNetOperatingResult)
```
- `finance_net_operating` metric block = Operating Profit (revenue − expenses, no COGS, no withdrawals)
- DailyCloseReport shows Operating Profit only (no COGS, no withdrawals — shop-level view)
- FinanceOverview table: Op. Profit column + Net Result column (two separate columns)
- IncomeStatement: all 5 lines, full drill-down

### Key technical notes:
- daily_sessions.session_date is DATE (not DATETIME) — no startOfDay/endOfDay needed
- expenses.payment_method is a PG enum — cast with ::text in raw SQL
- finance_net_operating uses sale_items + products for COGS (same as getGrossProfitKpis)
  to avoid inconsistency with SalesAnalyticsService calculations

---

## Finance & Reports Reconciliation (added 2026-04-29)

### What changed
- Canonical P&L hierarchy enforced across all three finance views (see section above)
- All finance blade files converted to CSS variable design system (zero hardcoded hex)
- Income Statement navigation link confirmed present in livewire layout sidebar
- OwnerActions panel: unclosed sessions section added as highest-priority (priority 0)
- FinanceOverview: added Cash Banked KPI, Expense Ratio KPI, cross-shop P&L comparison strip

### Data sources in FinanceOverview
- **KPI cards (Row 1):** `$netResult`, `$withdrawalSummary`, `$cashVariance` from `FinanceAnalyticsService` (cached, service layer) — used via `$svc*` blade variables with `$rows_col` fallback
- **Table rows:** `$rows_col` raw query (DailySessionService / direct DB) — may differ slightly from KPI cards due to cache TTL; this is intentional
- Never try to unify these into one source — the service provides cross-period aggregates; the table provides per-session drill-down

### OwnerActions section order (priority 0 = highest)
```
0 — Unclosed sessions (DailySession open + session_date < today)
1 — Return approvals above threshold
2 — Transfers with discrepancy
3 — Damaged goods pending > 3 days
4 — Customers over 90% credit limit
5 — Critical unresolved alerts
6 — Overdue credit customers (no repayment in overdue_credit_days)
```

### Design system rule (enforced 2026-04-29)
All finance blades must use CSS variables exclusively — no hardcoded hex anywhere.
Allowed palette: `--surface`, `--surface-raised`, `--border`, `--text`, `--text-dim`, `--text-faint`,
`--accent`, `--accent-dim`, `--red`, `--red-dim`, `--amber`, `--amber-dim`, `--green`, `--green-dim`.
Exception: Chart.js dataset color arrays inside `<script>` blocks may use hex or rgba.

---

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

## System Manager Page — completed 2026-05-14

### Route & files
- `owner.system` → GET `/owner/system` → `resources/views/owner/system.blade.php`
- Sidebar link: "System" (server icon, no warning icon) in `resources/views/livewire/layout/sidebar.blade.php`
- Livewire component: `app/Livewire/Owner/SystemManager.php`
- Blade: `resources/views/livewire/owner/system-manager.blade.php`

### Two tabs
- **Setup** — inline CRUD for Product Categories, Expense Categories, Transporters
  (no page navigation, no tinker needed for initial data entry)
- **Wipe** — selective data deletion with 14 checkbox groups

### Setup tab — inline CRUD rules
- Product Categories: name (required), code (optional), description (optional); toggle active/inactive; forceDelete blocked if products assigned
- Expense Categories: name (required), applies_to (shop|warehouse|both), description; toggle blocked for 'Cash Shortage'; delete blocked if expenses recorded
- Transporters: name (required), phone, company, vehicle number; delete blocked if transfer records exist
- Each list uses inline confirm-row pattern (no modal) — `$catConfirmDelete`, `$expCatConfirmDelete`, `$trConfirmDelete`

### Wipe tab — deletion rules
- 14 groups with FK-safe deletion order hardcoded in `executeWipe()` map
- Confirm bar: label "TYPE DELETE TO CONFIRM" + standalone monospace input + "Delete Selected Data" button
- Alpine `x-model` + `@input="$wire.set(...)"` used for instant reactivity (wire:model alone only syncs on blur)
- Button is pink/dim (var(--red-dim)) when inactive; solid red + white text when DELETE typed and groups selected
- Two-step: `requestWipe()` validates → sets `$showConfirm` → modal → `executeWipe()`
- `executeWipe()` runs inside `DB::transaction()`; users table: deletes all except current owner id
- FK-safe order: reports → logs → sessions → sales → returns → transfers → credit → boxes → customers → users → transporters → products → categories → locations

### Bug fixes in this session
- `/shop/credit-repayments` — fixed `Undefined variable $reason` by switching `@include` to `<x-session-gate-blocked :reason="..." />` (proper Blade component prop passing)
- Operations Centre widget — credit repayments were counted in "Gross Sales"; split `$in` into `$salesIn` + `$repaymentIn`; widget now shows "SALES" with "+X repaid" subtitle
- Session close wizard — bank/card repayments were not reflected in BANK balance; `DailySessionService::computeLiveSummary()` now adds `$bankRepayments` (bank_transfer + card credit repayments) to `bank_available` and returns `total_repayments_bank`

---

## Box-Centric Product Management & Owner Stock Intake — completed 2026-05-14

### What changed

**Owner is now solely responsible for the product catalogue and all stock intake.
Warehouse managers manage and ship existing boxes; they do not create products.**

### Pricing redesign (box-centric)

Products are priced at the **box level** in all forms. Per-item prices are derived automatically.

| Form field | DB column written | Formula |
|---|---|---|
| Box Purchase Price | `products.purchase_price` | `round(boxPurchasePrice / items_per_box)` |
| Box Selling Price | `products.selling_price` | `round(boxSellingPrice / items_per_box)` |
| Box Selling Price | `products.box_selling_price` | stored as-is (always populated now) |

On edit/load: form shows `purchase_price × items_per_box` and `box_selling_price ?? selling_price × items_per_box`.

**DB schema unchanged** — `purchase_price` and `selling_price` remain per-item integers. POS, analytics, and transfer logic are unaffected.

### Files changed

| File | Change |
|---|---|
| `resources/views/livewire/products/_form.blade.php` | Pricing card: Items/Box first, then Box Purchase Price + Box Selling Price (2-col). Per-item hint shown below each field. |
| `app/Livewire/Products/CreateProduct.php` | Props: `boxPurchasePrice`, `boxSellingPrice`. `save()` computes per-item. Flash has "Add stock →" link. |
| `app/Livewire/Products/EditProduct.php` | `mount()` converts DB per-item → box prices. `update()` same as create. |
| `app/Policies/BoxPolicy.php` | `create()` now returns `true` for owner and warehouse_manager (was always `false`). |
| `app/Livewire/Warehouse/Inventory/ReceiveBoxes.php` | `mount()` handles `?product_id=X` query string — pre-fills product + opens dropdown. Removed debug `\Log::info`. |

### Owner Stock Intake

New route for the owner to receive supplier stock directly into a warehouse.

| Item | Value |
|---|---|
| Route | `owner.inventory.receive` → `GET /owner/inventory/receive` |
| View (wrapper) | `resources/views/owner/inventory/receive.blade.php` |
| Livewire component | `<livewire:warehouse.inventory.receive-boxes />` (same as WM page) |
| Sidebar | "Receive Stock" link after "All Boxes" in owner nav |

The owner page embeds `App\Livewire\Warehouse\Inventory\ReceiveBoxes` — identical UI to `/warehouse/inventory/boxes/receive` (barcode scan, Excel import, product creation, recent boxes table). The warehouse route already allowed owners via `CheckRole::class . ':warehouse_manager,owner'`; `CheckLocation` passes owners through unconditionally.

After creating a product, the flash message includes a direct "Add stock →" link that pre-fills `?product_id=X` on the intake page.

### Key rules

- **Never enter per-item prices directly** in the product form — always enter box prices; the form computes per-item on save.
- `box_selling_price` is now **always set** when creating or editing a product (previously optional override). Existing products with `box_selling_price = null` still work via `effective_box_selling_price` accessor.
- The simple `App\Livewire\Inventory\Boxes\ReceiveBoxes` component still exists but is not used by any current page — do not route to it.
- `App\Livewire\Owner\Products\CreateProduct` is an older orphaned component — never route to it.

---

## Sales History Page — completed 2026-05-02

### Files
- `app/Livewire/Shop/Sales/SalesIndex.php` — component
- `resources/views/livewire/shop/sales/sales-index.blade.php` — Livewire template
- `resources/views/shop/sales/index.blade.php` — wrapper (CSS, `sli-` prefix)

### Route
- `shop.sales.index` → GET `/shop/sales`

### Features
- **KPI cards** (5): Total Revenue, Transactions, Avg. Transaction, Cash Collected, Credit Issued — all react to active date + payment filter
- **Filters**: Period (segmented pills, horizontal scroll) + Payment method (segmented pills, horizontal scroll on ≤660px)
- **Search**: sale number, customer name, customer phone (`ilike`)
- **Infinite scroll**: `$perPage` increments by 20 via `loadMore()`; Alpine `IntersectionObserver` sentinel with `rootMargin: '300px'`; no pagination component
- **Expandable rows**: items sold, payment breakdown, sale metadata, print receipt link
- **Sortable columns**: sale_number, sale_date, total

### Key rules
- `$sales` is a **Collection** (not LengthAwarePaginator) — use `$totalFiltered` for count, never `$sales->total()`
- Payment filter `credit` must use `where('has_credit', true)` — NOT `where('payment_method', 'credit')`
- `$summaryCash` query uses `sales.has_credit = true` condition when filter is `credit` (not `sales.payment_method`)
- `applyPaymentFilter()` is called on BOTH the main query and the summary base query so KPI cards always match table results
- Filter resets `$perPage = 20` in `updatingSearch`, `updatingDateFilter`, `updatingPaymentFilter`, `sort()`

---

## UI & Design System

Before creating or editing ANY blade view, Livewire template, or CSS,
read the full skill file at:

```
.claude/skills/ui-design.md
```

This is mandatory. It contains the existing design patterns, CSS class
conventions, color variables, component structures, and mobile breakpoints
that all pages must follow.
