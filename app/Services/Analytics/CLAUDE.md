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
