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
