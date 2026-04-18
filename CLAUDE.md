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
              + cash_from_sale_payments   (sale_payments table, split-payment safe)
              − cash_refunds              (returns with refund_method='cash' and is_exchange=false)
              − cash_expenses             (expenses with payment_method='cash', not voided)
              − owner_withdrawals         (not voided)
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
