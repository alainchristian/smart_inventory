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
