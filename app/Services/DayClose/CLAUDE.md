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
