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

---

## Report Library — 7 Additional Quick-Start Templates (added 2026-06-11)

Added 7 new entries to `app/Services/Reports/ReportTemplates.php` to broaden
the time-bound coverage of the Quick-Start strip. **No** changes to
`MetricRegistry`, `ReportRunner`, or any blade/Livewire component — these
templates compose existing metric blocks.

| key | name | date_range | block count |
| --- | --- | --- | --- |
| `daily_snapshot` | Daily Snapshot | today | 9 |
| `weekly_exec` | Weekly Executive Brief | week | 9 |
| `year_review` | Year in Review | year | 11 |
| `shop_compare` | Shop Head-to-Head | month | 8 |
| `cash_banking` | Cash & Banking Audit | month | 6 |
| `margin_pricing` | Margin & Pricing Health | month | 6 |
| `audit_pack` | Pre-Audit Compliance Pack | month | 8 |

Total template count is now 14 (7 existing + 7 new).

Rules followed when adding templates:
- All `metric_id`s must exist in `MetricRegistry::catalogue()`.
- Every `viz` value must be in that metric's `viz_options`.
- `color` uses CSS variable tokens only (`--accent`, `--green`, `--amber`, `--red`, `--violet`).
- Icons are inner-SVG path data sized for the `viewBox="0 0 24 24"` wrapper in `report-library.blade.php`.
- No `text_block` entries and no `comparison_mode` in the template config.

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

