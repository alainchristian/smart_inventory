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
