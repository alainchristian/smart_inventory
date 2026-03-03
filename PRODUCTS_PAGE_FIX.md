# SmartInventory — Products Page Fix + Size Increase
## Claude Code Instructions

> Drop this file in the project root, then run:
> `"Read PRODUCTS_PAGE_FIX.md and follow every step in order."`

---

## Ground Rules

- **Read every target file before editing** — understand existing structure first
- **No new files unless explicitly stated** — update existing files only
- **Run `npm run build` after all CSS changes** to compile assets
- **Steps must run in order** — CSS first, then PHP, then Blade

---

## Step 0 — Pre-Flight

```bash
# Confirm real file paths before editing anything
cat resources/css/app.css | grep -n "sidebar-width\|topbar-height\|biz-kpi-grid\|dashboard-page-header\|\.card\b" | head -40
cat resources/views/layouts/app.blade.php
cat resources/views/owner/products/index.blade.php
cat app/Livewire/Dashboard/TimeFilter.php | grep -A5 "dispatchFilter\|dispatch("
cat app/Livewire/Owner/Products/ProductKpiRow.php | head -30
cat app/Livewire/Products/ProductList.php | head -30
cat app/Providers/AuthServiceProvider.php | grep -A3 "Gate::"
```

---

## Step 1 — CSS: Increase Page Size + Fix Layout Variables

**Target:** `resources/css/app.css`

### 1a — Find and update the `:root {}` block

Find these variables and change their values:

```css
/* BEFORE → AFTER */
--sidebar-width: 248px;   →   --sidebar-width: 260px;
--topbar-height: 80px;    →   --topbar-height: 64px;

/* Also update the alias variables if they exist */
--sidebar-w: 248px;       →   --sidebar-w: 260px;
--topbar-h:  62px;        →   --topbar-h:  64px;
```

### 1b — Find and update main content padding

Find this rule in app.css (or in layouts/app.blade.php — see Step 5):

```css
/* Find: */
.content { padding: 20px 24px; }
/* Or any rule targeting main content padding */
```

In `resources/views/layouts/app.blade.php`, find:
```html
<div class="p-4 sm:p-5 lg:p-7">
```
Replace with:
```html
<div class="p-5 sm:p-6 lg:p-8 xl:p-10">
```

### 1c — Find `.biz-kpi-grid` and `.ops-kpi-grid` — increase gap and card size

Find:
```css
.biz-kpi-grid, .ops-kpi-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 14px; margin-bottom: 14px;
}
```
Replace with:
```css
.biz-kpi-grid, .ops-kpi-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 18px; margin-bottom: 18px;
}
```

### 1d — Find `.bkpi` card — increase padding and value size

Find:
```css
.bkpi {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--r); padding: 18px 20px;
```
Replace `padding: 18px 20px` with `padding: 22px 24px`.

Find:
```css
.bkpi-value { font-size: 26px; font-weight: 700; letter-spacing: -1px; line-height: 1.1; color: var(--text); }
```
Replace `font-size: 26px` with `font-size: 30px`.

Find:
```css
.bkpi-icon { width: 34px; height: 34px; border-radius: 9px; display: grid; place-items: center; }
.bkpi-icon svg { width: 17px; height: 17px; }
```
Replace with:
```css
.bkpi-icon { width: 40px; height: 40px; border-radius: 10px; display: grid; place-items: center; }
.bkpi-icon svg { width: 20px; height: 20px; }
```

Find:
```css
.bkpi-name { font-size: 11px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--text-sub); }
```
Replace `font-size: 11px` with `font-size: 12px`.

Find:
```css
.bkpi-meta { font-size: 11px; color: var(--text-dim); margin-top: 5px; font-family: var(--mono); }
```
Replace `font-size: 11px` with `font-size: 12px`.

### 1e — Find `.okpi` card — increase size

Find:
```css
.okpi {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--r); padding: 16px 18px;
```
Replace `padding: 16px 18px` with `padding: 20px 22px`.

Find:
```css
.okpi-icon { width: 44px; height: 44px; border-radius: 11px; display: grid; place-items: center; flex-shrink: 0; }
.okpi-icon svg { width: 20px; height: 20px; }
```
Replace with:
```css
.okpi-icon { width: 52px; height: 52px; border-radius: 13px; display: grid; place-items: center; flex-shrink: 0; }
.okpi-icon svg { width: 24px; height: 24px; }
```

Find `.okpi-value` font size and increase:
```css
.okpi-value { font-size: ...
```
Set to `font-size: 26px`.

### 1f — Find `.card` base — increase padding

Find:
```css
.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 18px 20px;
```
Replace `padding: 18px 20px` with `padding: 22px 24px`.

Find:
```css
.card-title { font-size: 15px; font-weight: 700; ...
```
Replace `font-size: 15px` with `font-size: 16px`.

### 1g — Find `.dashboard-page-header` — increase heading size

Find:
```css
.dashboard-page-header h1 {
  font-size: 22px; font-weight: 700; letter-spacing: -.5px;
```
Replace `font-size: 22px` with `font-size: 26px`.

Find:
```css
.dashboard-page-header p {
  font-size: 12.5px; color: var(--text-sub); margin-top: 4px;
```
Replace `font-size: 12.5px` with `font-size: 14px`.

### 1h — Find `.row-sales-shops` and layout rows — increase gaps

Find:
```css
.row-sales-shops   { display: grid; grid-template-columns: 1fr 380px; gap: 14px; margin-bottom: 22px; }
.row-ops-activity  { display: grid; grid-template-columns: 1fr 1fr 300px; gap: 14px; margin-bottom: 22px; }
.row-trace-alerts  { display: grid; grid-template-columns: 1fr 340px; gap: 14px; }
```
Replace with:
```css
.row-sales-shops   { display: grid; grid-template-columns: 1fr 420px; gap: 18px; margin-bottom: 26px; }
.row-ops-activity  { display: grid; grid-template-columns: 1fr 1fr 320px; gap: 18px; margin-bottom: 26px; }
.row-trace-alerts  { display: grid; grid-template-columns: 1fr 360px; gap: 18px; }
```

### 1i — Find `.section-label` — increase size

Find:
```css
.section-label {
  font-size: 10.5px; font-weight: 600; letter-spacing: .8px;
  text-transform: uppercase; color: var(--text-dim);
  margin-bottom: 12px; margin-top: 8px;
```
Replace with:
```css
.section-label {
  font-size: 11.5px; font-weight: 700; letter-spacing: .8px;
  text-transform: uppercase; color: var(--text-dim);
  margin-bottom: 14px; margin-top: 20px;
```

---

## Step 2 — Fix: Products Page KPI Row Not Showing

**Target:** `resources/views/owner/products/index.blade.php`

Read the current file. If it contains `@canany` wrapping the entire KPI row section (not just card 4), remove that wrapper. The KPI row must always render for the owner route. Card 4 (Best Margin) is already gated inside its own blade via `@canany(['viewPurchasePrice'])`.

The file should look exactly like this — **replace the entire file content**:

```blade
<x-app-layout>

  {{-- Page header --}}
  <div class="dashboard-page-header">
    <div>
      <h1>Products</h1>
      <p>Catalog management, stock health, pricing intelligence</p>
    </div>
    <livewire:dashboard.time-filter />
  </div>

  {{-- KPI row - always visible to owner --}}
  {{-- Card 4 (Best Margin) is self-gated inside product-kpi-row.blade.php --}}
  <div class="section-label">Catalog Overview</div>
  <livewire:owner.products.product-kpi-row />

  {{-- Product table --}}
  <div class="section-label">Product List</div>
  <livewire:products.product-list />

  {{-- Detail drawer - floats above, self-contained --}}
  <livewire:owner.products.product-detail />

</x-app-layout>
```

---

## Step 3 — Fix: Time Filter Period Not Propagating to Products Page

**Target:** `app/Livewire/Owner/Products/ProductKpiRow.php`

Read the current file. Find the `#[On('time-filter-changed')]` listener method. 

Check `app/Livewire/Dashboard/TimeFilter.php` to confirm how it dispatches:
```php
$this->dispatch('time-filter-changed', [
    'period' => $this->activePeriod,
    'from'   => $this->customFrom,
    'to'     => $this->customTo,
]);
```
It dispatches an **array payload** (single array argument), not named parameters.

The listener signature must match. If `ProductKpiRow.php` has:
```php
public function refresh(string $period, ?string $from = null, ?string $to = null): void
```
Replace with:
```php
#[On('time-filter-changed')]
public function refresh(array $payload): void
{
    $this->period = $payload['period'] ?? 'month';
    $this->from   = $payload['from']   ?? null;
    $this->to     = $payload['to']     ?? null;
}
```

**Target:** `app/Livewire/Products/ProductList.php`

Same fix. Find the `#[On('time-filter-changed')]` listener. If it uses named string parameters, replace with:
```php
#[On('time-filter-changed')]
public function refreshPeriod(array $payload): void
{
    $this->period = $payload['period'] ?? 'month';
    $this->from   = $payload['from']   ?? null;
    $this->to     = $payload['to']     ?? null;
    $this->resetPage();
}
```

---

## Step 4 — Fix: Product Detail Drawer Chart Not Rendering

**Target:** `resources/views/livewire/owner/products/product-detail.blade.php`

Read the current file. Find the chart initialisation `<script>` tag. 

**Problem:** Inline `<script>` tags inside Livewire 3 component views are unreliable — Livewire morphing can silently drop them during re-renders.

**Fix:** Replace the bare `<script>...</script>` block (that contains the `initProductChart` / Chart.js logic) with the Livewire 3 `@script` / `@endscript` directive wrapper. This tells Livewire to always execute it after DOM updates.

Find the script block — it will look like one of these patterns:
```html
<script>
  (function() {
    var canvas = document.getElementById('pdChart-...
```
OR:
```html
<script>
  Alpine.data('productDetailDrawer', () => ({
```

**Wrap the entire script content** with `@script` / `@endscript`:

```blade
@script
<script>
  Alpine.data('productDetailDrawer', () => ({
    open: false,

    initDrawer() {
      const drawer = this.$refs.drawer;
      if (drawer) drawer.scrollTop = 0;

      requestAnimationFrame(() => {
        this.open = true;
        this.$nextTick(() => { initProductChart(); });
      });
    }
  }));

  function initProductChart() {
    const canvas = document.getElementById('pdChart-{{ $product->id }}');
    if (!canvas) return;

    const existing = Chart.getChart(canvas);
    if (existing) existing.destroy();
    if (canvas._chartInstance) {
      canvas._chartInstance.destroy();
      delete canvas._chartInstance;
    }

    const chartData = @json($chartData);
    if (!chartData) return;

    canvas._chartInstance = new Chart(canvas, {
      type: 'bar',
      data: {
        labels: chartData.labels,
        datasets: [
          {
            label: 'Revenue (RWF)',
            data: chartData.revenue,
            backgroundColor: 'rgba(59,111,212,.15)',
            borderColor: 'rgba(59,111,212,.65)',
            borderWidth: 1.5,
            borderRadius: 3,
            yAxisID: 'y',
          },
          {
            label: 'Units',
            data: chartData.units,
            type: 'line',
            borderColor: 'rgba(14,158,134,.75)',
            backgroundColor: 'transparent',
            borderWidth: 2,
            pointRadius: 2,
            tension: 0.3,
            yAxisID: 'y1',
          }
        ]
      },
      options: {
        animation: false,
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { mode: 'index', intersect: false }
        },
        scales: {
          x: {
            ticks: { color: '#a8aec8', font: { size: 9 }, maxTicksLimit: 10 },
            grid:  { color: '#e2e6f3', drawBorder: false },
          },
          y: {
            position: 'left',
            ticks: {
              color: '#a8aec8', font: { size: 9 },
              callback: v => v >= 1000 ? (v/1000).toFixed(0)+'K' : v
            },
            grid: { color: '#e2e6f3', drawBorder: false },
          },
          y1: {
            position: 'right',
            ticks: { color: 'rgba(14,158,134,.7)', font: { size: 9 } },
            grid:  { drawOnChartArea: false },
          }
        }
      }
    });
  }
</script>
@endscript
```

Also ensure the drawer's root Alpine element uses `x-data="productDetailDrawer()"` and `x-init="initDrawer()"`, and has `wire:ignore` on the canvas container.

Also make the drawer **header sticky** so it doesn't scroll away. Find the header div inside the drawer and add `position:sticky;top:0;z-index:10;background:var(--surface)` to its inline style.

Also **reset scroll on open**: inside `initDrawer()`, make sure `this.$refs.drawer.scrollTop = 0` runs before the animation.

---

## Step 5 — Increase Main Content Area Width

**Target:** `resources/views/layouts/app.blade.php`

Read the file. Find the main content wrapper and page content padding div.

Find:
```html
<div class="p-4 sm:p-5 lg:p-7">
```
Replace with:
```html
<div class="p-5 sm:p-6 lg:p-8 xl:p-10">
```

If the file uses `max-w-*` anywhere on the main content container, remove it so the page uses full width.

---

## Step 6 — Increase Table and Content Typography

**Target:** `resources/css/app.css`

Find the base `html` font-size:
```css
html {
    font-size: 16px;
}
```
Keep at 16px (do not reduce).

Find `body` font-size:
```css
body {
    ...
    font-size: 0.9375rem; /* 15px */
```
Replace `0.9375rem` with `1rem` (16px).

At the **end of `app.css`**, append these new rules (do not duplicate if already present):

```css
/* ═══════════════════════════════════
   PRODUCTS PAGE — Sizing overrides
═══════════════════════════════════ */

/* Make product list table text larger */
.product-table td,
.product-table th {
  font-size: 13.5px;
}

/* Larger filter inputs */
.product-filters input,
.product-filters select {
  font-size: 13px;
  padding: 8px 12px;
}

/* Sidebar slightly wider for readability */
.sidebar,
aside {
  min-width: 260px;
}

/* Topbar height decrease for more content space */
:root {
  --topbar-height: 64px;
  --topbar-h: 64px;
}

/* Page content: bump up minimum readable font */
main table td { font-size: 13.5px; }
main table th { font-size: 11px; }

/* KPI card min height for breathing room */
.bkpi { min-height: 140px; }
.okpi { min-height: 80px; }
```

---

## Step 7 — Rebuild Assets

```bash
npm run build
```

Confirm output includes `app.css` with no errors.

---

## Step 8 — Verify

```bash
# Check no syntax errors in PHP files
php artisan route:list | grep owner
php artisan config:clear && php artisan view:clear && php artisan cache:clear

# Confirm listener signatures match TimeFilter dispatch format
grep -n "array \$payload\|string \$period" app/Livewire/Owner/Products/ProductKpiRow.php
grep -n "array \$payload\|string \$period" app/Livewire/Products/ProductList.php
grep -n "dispatchFilter\|dispatch(" app/Livewire/Dashboard/TimeFilter.php

# Confirm no @canany wrapping the full KPI row in products index
grep -n "canany\|section-label\|product-kpi-row" resources/views/owner/products/index.blade.php

# Confirm @script directive is in the detail blade
grep -n "@script\|@endscript" resources/views/livewire/owner/products/product-detail.blade.php
```

---

## Summary of Changes

| # | File | Change |
|---|------|--------|
| 1 | `resources/css/app.css` | Increase padding, font sizes, gaps, card sizes throughout |
| 2 | `resources/views/layouts/app.blade.php` | Increase content padding `p-8 xl:p-10` |
| 3 | `resources/views/owner/products/index.blade.php` | Remove `@canany` gate from KPI row section |
| 4 | `app/Livewire/Owner/Products/ProductKpiRow.php` | Fix listener: `array $payload` not named string params |
| 5 | `app/Livewire/Products/ProductList.php` | Fix listener: `array $payload` not named string params |
| 6 | `resources/views/livewire/owner/products/product-detail.blade.php` | Wrap chart script in `@script`/`@endscript`, sticky header, scroll reset |
