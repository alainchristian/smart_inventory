# SmartInventory — Owner Dashboard v3 Update
## Claude Code Instructions

> **How to use this file:**  
> Drop it in the project root, then tell Claude Code:  
> _"Read `DASHBOARD_UPDATE.md` and follow every instruction in order."_

---

## Ground Rules

- **Update existing files — do not create new ones** unless a file is completely absent after checking
- **Read every target file before editing it** — understand existing structure first
- **Light theme throughout** — replace all dark tokens, no dark backgrounds anywhere
- **Run steps in order** — CSS tokens must exist before components render correctly

---

## Step 0 — Pre-Flight Discovery

Run these commands first. Use the output to confirm real file paths before any edit.

```bash
# Livewire components
find app/Livewire -name "*.php" | grep -iE "dashboard|kpi|sales|transfer|alert|activity|movement|snapshot|shop|system"

# Blade views
find resources/views/livewire -name "*.blade.php" | grep -iE "dashboard|kpi|sales|transfer|alert|activity|movement|snapshot|shop|system"

# CSS / JS entry points
find resources/css -name "*.css"
find resources/js  -name "*.js"

# Routes
grep -n "dashboard" routes/web.php

# Middleware
ls app/Http/Middleware/

# Gates
grep -rn "Gate::" app/Providers/
```

---

## Step 1 — Light Theme: Design Tokens & Global CSS

**Target:** `resources/css/app.css`

### 1a — Replace colour tokens inside `:root {}`

Find the existing `:root {}` block. Replace every colour-related variable with the list below. Keep non-colour variables (`--font`, `--mono`, `--ease`, `--tr`, `--radius-*`, `--sidebar-w`, `--topbar-h`) exactly as they are.

```css
/* ── Backgrounds ───────────────────────────── */
--bg:          #f4f6fb;
--surface:     #ffffff;
--surface2:    #f0f2f8;
--surface3:    #e6eaf5;
--border:      #e2e6f3;
--border-hi:   #c8d0e8;

/* ── Text ──────────────────────────────────── */
--text:        #1a1f36;
--text-sub:    #6b7494;
--text-dim:    #a8aec8;

/* ── Accent blue ───────────────────────────── */
--accent:      #3b6fd4;
--accent-glow: rgba(59,111,212,.14);
--accent-dim:  rgba(59,111,212,.07);

/* ── Green / Teal ──────────────────────────── */
--green:       #0e9e86;
--green-glow:  rgba(14,158,134,.14);
--green-dim:   rgba(14,158,134,.07);

/* ── Amber ─────────────────────────────────── */
--amber:       #d97706;
--amber-glow:  rgba(217,119,6,.14);
--amber-dim:   rgba(217,119,6,.07);

/* ── Red ───────────────────────────────────── */
--red:         #e11d48;
--red-glow:    rgba(225,29,72,.14);
--red-dim:     rgba(225,29,72,.07);

/* ── Violet ────────────────────────────────── */
--violet:      #7c3aed;
--violet-glow: rgba(124,58,237,.14);
--violet-dim:  rgba(124,58,237,.07);

/* ── Success ───────────────────────────────── */
--success:      #16a34a;
--success-glow: rgba(22,163,74,.14);
--success-dim:  rgba(22,163,74,.07);

/* ── Pink ──────────────────────────────────── */
--pink:        #db2777;
--pink-glow:   rgba(219,39,119,.14);
--pink-dim:    rgba(219,39,119,.07);
```

### 1b — Update body, sidebar, topbar rules

Find these rules and update only the background/shadow properties:

```css
body {
  background: var(--bg);   /* #f4f6fb */
  color: var(--text);
}

.sidebar {
  background: var(--surface);
  border-right: 1px solid var(--border);
}

.topbar {
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  box-shadow: 0 1px 8px rgba(26,31,54,.06);
}
```

### 1c — Dark-to-light substitution table

Find and replace every hardcoded dark hex value throughout the entire `app.css`:

| Find (dark) | Replace with |
|---|---|
| `#0b0e16` | `var(--bg)` |
| `#111420` | `var(--surface)` |
| `#181c29` | `var(--surface2)` |
| `#1f2434` | `var(--surface3)` |
| `#232840` | `var(--border)` |
| `#2c3252` | `var(--border-hi)` |
| `#e4e8f5` | `var(--text)` |
| `#8b92b3` | `var(--text-sub)` |
| `#3d4460` | `var(--text-dim)` |

### 1d — Append new utility classes

Add the following at the very end of `app.css`. Do not overwrite anything already there.

```css
/* ════════════════════════════════════════════════
   DASHBOARD v3 — Layout & Component Utilities
════════════════════════════════════════════════ */

/* Section dividers */
.section-label {
  font-size: 10.5px; font-weight: 600; letter-spacing: .8px;
  text-transform: uppercase; color: var(--text-dim);
  margin-bottom: 12px; margin-top: 8px;
  display: flex; align-items: center; gap: 8px;
}
.section-label::after {
  content: ''; flex: 1; height: 1px; background: var(--border);
}

/* Page header */
.dashboard-page-header {
  display: flex; align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 20px; gap: 16px; flex-wrap: wrap;
}
.dashboard-page-header h1 {
  font-size: 22px; font-weight: 700; letter-spacing: -.5px;
  line-height: 1.2; color: var(--text);
}
.dashboard-page-header p {
  font-size: 12.5px; color: var(--text-sub); margin-top: 4px;
}

/* Time filter */
.time-filter-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.time-seg {
  display: flex; background: var(--surface2);
  border: 1px solid var(--border); border-radius: var(--rsm); overflow: hidden;
}
.time-seg-btn {
  padding: 7px 16px; font-size: 12.5px; font-weight: 600;
  color: var(--text-sub); cursor: pointer; transition: var(--tr);
  border: none; background: none; white-space: nowrap; font-family: var(--font);
}
.time-seg-btn:hover  { color: var(--text); background: var(--surface3); }
.time-seg-btn.active { background: var(--accent); color: #fff; }

.time-custom-btn {
  display: flex; align-items: center; gap: 7px; padding: 7px 14px;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--rsm); font-size: 12.5px; font-weight: 500;
  color: var(--text-sub); cursor: pointer; transition: var(--tr); white-space: nowrap;
}
.time-custom-btn:hover { background: var(--surface2); color: var(--text); }
.time-custom-btn svg  { width: 14px; height: 14px; flex-shrink: 0; }

.currency-chip {
  display: flex; align-items: center; gap: 5px; padding: 7px 12px;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--rsm); font-size: 12px; font-weight: 600;
  color: var(--text-sub); cursor: pointer; transition: var(--tr);
}
.currency-chip:hover { background: var(--surface2); color: var(--text); }

/* Grid layouts */
.biz-kpi-grid, .ops-kpi-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 14px; margin-bottom: 14px;
}
.row-sales-shops   { display: grid; grid-template-columns: 1fr 380px; gap: 14px; margin-bottom: 22px; }
.row-ops-activity  { display: grid; grid-template-columns: 1fr 1fr 300px; gap: 14px; margin-bottom: 22px; }
.row-trace-alerts  { display: grid; grid-template-columns: 1fr 340px; gap: 14px; }
.right-stack-panel { display: flex; flex-direction: column; gap: 14px; }

/* Business KPI cards */
.bkpi {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--r); padding: 18px 20px;
  position: relative; overflow: hidden;
  box-shadow: 0 1px 4px rgba(26,31,54,.06);
  transition: transform var(--tr), box-shadow var(--tr), border-color var(--tr);
}
.bkpi::after { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
.bkpi.pink::after   { background: linear-gradient(90deg, var(--pink),   transparent); }
.bkpi.green::after  { background: linear-gradient(90deg, var(--green),  transparent); }
.bkpi.blue::after   { background: linear-gradient(90deg, var(--accent), transparent); }
.bkpi.violet::after { background: linear-gradient(90deg, var(--violet), transparent); }
.bkpi:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,31,54,.10); border-color: var(--border-hi); }

.bkpi-icon        { width: 34px; height: 34px; border-radius: 9px; display: grid; place-items: center; }
.bkpi-icon svg    { width: 17px; height: 17px; }
.bkpi-icon.pink   { background: var(--pink-dim);   color: var(--pink); }
.bkpi-icon.green  { background: var(--green-dim);  color: var(--green); }
.bkpi-icon.blue   { background: var(--accent-dim); color: var(--accent); }
.bkpi-icon.violet { background: var(--violet-dim); color: var(--violet); }
.bkpi-name        { font-size: 11px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--text-sub); }
.bkpi-value       { font-size: 26px; font-weight: 700; letter-spacing: -1px; line-height: 1.1; color: var(--text); }
.bkpi-meta        { font-size: 11px; color: var(--text-dim); margin-top: 5px; font-family: var(--mono); }
.bkpi-pct         { font-size: 12px; font-weight: 700; padding: 3px 8px; border-radius: 20px; font-family: var(--mono); }
.bkpi-pct.up      { background: var(--success-glow); color: var(--success); }
.bkpi-pct.green   { background: var(--green-glow);   color: var(--green); }
.bkpi-pct.blue    { background: var(--accent-glow);  color: var(--accent); }
.bkpi-pct.violet  { background: var(--violet-glow);  color: var(--violet); }

.loc-stats         { display: flex; margin-top: 8px; }
.loc-stat          { flex: 1; display: flex; flex-direction: column; align-items: center; padding: 8px 0; border-right: 1px solid var(--border); }
.loc-stat:last-child { border-right: none; }
.loc-stat-val      { font-size: 22px; font-weight: 700; letter-spacing: -1px; }
.loc-stat-lbl      { font-size: 10px; color: var(--text-dim); margin-top: 2px; text-transform: uppercase; letter-spacing: .5px; }

/* Ops KPI cards */
.okpi {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--r); padding: 16px 18px;
  display: flex; align-items: center; gap: 14px;
  box-shadow: 0 1px 4px rgba(26,31,54,.06);
  transition: transform var(--tr), box-shadow var(--tr);
}
.okpi:hover       { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,31,54,.10); }
.okpi-icon        { width: 44px; height: 44px; border-radius: 11px; display: grid; place-items: center; flex-shrink: 0; }
.okpi-icon svg    { width: 20px; height: 20px; }
.okpi-icon.blue   { background: var(--accent-dim); color: var(--accent); }
.okpi-icon.amber  { background: var(--amber-dim);  color: var(--amber); }
.okpi-icon.red    { background: var(--red-dim);    color: var(--red); }
.okpi-icon.green  { background: var(--green-dim);  color: var(--green); }
.okpi-body        { flex: 1; min-width: 0; }
.okpi-value       { font-size: 24px; font-weight: 700; letter-spacing: -.8px; line-height: 1; color: var(--text); }
.okpi-label       { font-size: 12px; color: var(--text-sub); margin-top: 3px; font-weight: 500; }
.okpi-sub         { font-size: 10.5px; color: var(--text-dim); margin-top: 5px; font-family: var(--mono); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.okpi-delta       { font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 20px; align-self: flex-start; flex-shrink: 0; }
.okpi-delta.up    { background: var(--success-glow); color: var(--success); }
.okpi-delta.down  { background: var(--red-glow);     color: var(--red); }
.okpi-delta.warn  { background: var(--amber-glow);   color: var(--amber); }

/* Sales performance period tabs */
.period-tabs       { display: flex; gap: 4px; }
.period-tab        { padding: 5px 12px; border-radius: var(--rx); font-size: 12px; font-weight: 600; cursor: pointer; transition: var(--tr); color: var(--text-sub); background: none; border: 1px solid transparent; font-family: var(--font); }
.period-tab:hover  { color: var(--text); background: var(--surface2); }
.period-tab.active { background: var(--accent-dim); color: var(--accent); border-color: var(--accent-glow); }

.sp-period-row              { display: flex; border-top: 1px solid var(--border); }
.sp-period-col              { flex: 1; padding: 14px 16px; border-right: 1px solid var(--border); cursor: pointer; transition: var(--tr); }
.sp-period-col:last-child   { border-right: none; }
.sp-period-col:hover        { background: var(--surface2); }
.sp-period-col.active-period{ background: var(--accent-dim); }
.sp-period-name             { font-size: 10.5px; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; color: var(--text-dim); }
.sp-period-val              { font-size: 16px; font-weight: 700; margin-top: 4px; }
.sp-period-val.ok           { color: var(--success); }
.sp-period-val.blue         { color: var(--accent); }

/* Top shops */
.shop-rank     { width: 30px; height: 30px; border-radius: 8px; display: grid; place-items: center; font-size: 13px; font-weight: 700; flex-shrink: 0; }
.shop-rank.r1  { background: var(--amber-dim);  color: var(--amber); }
.shop-rank.r2  { background: var(--surface3);   color: var(--text-sub); }
.shop-rank.r3  { background: var(--pink-dim);   color: var(--pink); }
.shop-bar-fill.r1 { background: linear-gradient(90deg, var(--amber),  var(--pink)); }
.shop-bar-fill.r2 { background: linear-gradient(90deg, var(--accent), var(--violet)); }
.shop-bar-fill.r3 { background: linear-gradient(90deg, var(--green),  var(--accent)); }

/* Transfer status colours */
.ts-icon.amber  { background: var(--amber-dim);   color: var(--amber); }
.ts-icon.blue   { background: var(--accent-dim);  color: var(--accent); }
.ts-icon.red    { background: var(--red-dim);      color: var(--red); }
.ts-icon.green  { background: var(--success-dim);  color: var(--success); }
.ts-count.amber { color: var(--amber); }
.ts-count.blue  { color: var(--accent); }
.ts-count.red   { color: var(--red); }
.ts-count.green { color: var(--success); }
.ts-count.dim   { color: var(--text-dim); }

/* System status ring */
.sys-ok-ring {
  width: 64px; height: 64px; border-radius: 50%;
  border: 3px solid var(--success); display: grid; place-items: center;
  margin-bottom: 12px; box-shadow: 0 0 20px var(--success-glow); position: relative;
}
.sys-ok-ring::before {
  content: ''; position: absolute; inset: -6px; border-radius: 50%;
  border: 1px solid var(--success-glow); animation: pulse 2s ease infinite;
}
.sys-ok-ring svg { width: 28px; height: 28px; color: var(--success); }

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(14px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: .5; }
  50%       { transform: scale(1.08); opacity: 1; }
}

/* Responsive breakpoints */
@media (max-width: 1400px) {
  .row-ops-activity { grid-template-columns: 1fr 1fr; }
  .row-ops-activity > *:last-child { grid-column: 1 / -1; }
}
@media (max-width: 1200px) {
  .biz-kpi-grid, .ops-kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .row-sales-shops  { grid-template-columns: 1fr; }
  .row-ops-activity { grid-template-columns: 1fr; }
  .row-trace-alerts { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .dashboard-page-header { flex-direction: column; }
  .time-filter-row { flex-wrap: wrap; }
  .time-seg-btn { padding: 6px 10px; font-size: 11.5px; }
}
@media (max-width: 640px) {
  .biz-kpi-grid { grid-template-columns: 1fr; }
  .ops-kpi-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
}
```

---

## Step 2 — Time Filter Component

> **Check first:** does `app/Livewire/Dashboard/TimeFilter.php` exist? If yes, update it. If no, create it.

### `app/Livewire/Dashboard/TimeFilter.php`

Add or replace the class body with:

```php
public string  $activePeriod = 'today';
public string  $currency     = 'RWF';
public ?string $customFrom   = null;
public ?string $customTo     = null;
public bool    $showCustom   = false;

public function setPeriod(string $period): void
{
    $this->activePeriod = $period;
    $this->showCustom   = false;
    $this->dispatchFilter();
}

public function applyCustomRange(): void
{
    if ($this->customFrom && $this->customTo) {
        $this->activePeriod = 'custom';
        $this->showCustom   = false;
        $this->dispatchFilter();
    }
}

private function dispatchFilter(): void
{
    $this->dispatch('time-filter-changed', [
        'period' => $this->activePeriod,
        'from'   => $this->customFrom,
        'to'     => $this->customTo,
    ]);
}
```

### `resources/views/livewire/dashboard/time-filter.blade.php`

Replace entire content:

```blade
<div class="time-filter-row">

    {{-- Segmented period buttons --}}
    <div class="time-seg">
        @foreach(['today' => 'Today', 'week' => 'Week', 'month' => 'Month', 'quarter' => 'Quarter', 'year' => 'Year'] as $key => $label)
            <button class="time-seg-btn {{ $activePeriod === $key ? 'active' : '' }}"
                    wire:click="setPeriod('{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Custom range toggle --}}
    <button class="time-custom-btn" wire:click="$toggle('showCustom')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        Custom Range
    </button>

    {{-- Inline date pickers --}}
    @if($showCustom)
    <div style="display:flex;align-items:center;gap:6px">
        <input type="date" wire:model="customFrom"
               style="padding:6px 10px;border:1px solid var(--border);border-radius:var(--rsm);
                      background:var(--surface);color:var(--text);font-size:12px">
        <span style="color:var(--text-dim);font-size:12px">→</span>
        <input type="date" wire:model="customTo"
               style="padding:6px 10px;border:1px solid var(--border);border-radius:var(--rsm);
                      background:var(--surface);color:var(--text);font-size:12px">
        <button wire:click="applyCustomRange"
                class="time-seg-btn active" style="border-radius:var(--rsm);padding:6px 14px">
            Apply
        </button>
    </div>
    @endif

    {{-- Currency chip --}}
    <div class="currency-chip">
        <span>🇷🇼</span> {{ $currency }}
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="6 9 12 15 18 9"/>
        </svg>
    </div>

</div>
```

---

## Step 3 — Business KPI Row

> **Owner-only component.** Wrap both the PHP queries and the blade view in the `viewPurchasePrice` gate.
> Check first: does `app/Livewire/Dashboard/BusinessKpiRow.php` exist?

### `app/Livewire/Dashboard/BusinessKpiRow.php`

Add or update to include `#[On('time-filter-changed')]` and the `loadData()` method:

```php
use Livewire\Attributes\On;

public string  $period = 'today';
public ?string $from   = null;
public ?string $to     = null;
public array   $sales     = [];
public array   $profit    = [];
public array   $inventory = [];
public array   $locations = [];

public function mount(): void { $this->loadData(); }

#[On('time-filter-changed')]
public function refresh(array $payload): void
{
    $this->period = $payload['period'] ?? 'today';
    $this->from   = $payload['from']   ?? null;
    $this->to     = $payload['to']     ?? null;
    $this->loadData();
}

private function loadData(): void
{
    [$start, $end]         = $this->periodRange();
    [$prevStart, $prevEnd] = $this->previousRange();

    $current  = Sale::whereBetween('sale_date', [$start, $end])->sum('total');
    $previous = Sale::whereBetween('sale_date', [$prevStart, $prevEnd])->sum('total');

    $this->sales = [
        'today'   => Sale::whereDate('sale_date', today())->sum('total'),
        'week'    => Sale::whereBetween('sale_date', [now()->startOfWeek(), now()])->sum('total'),
        'month'   => Sale::whereBetween('sale_date', [now()->startOfMonth(), now()])->sum('total'),
        'current' => $current,
        'growth'  => $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 0.0,
        'count'   => Sale::whereBetween('sale_date', [$start, $end])->count(),
    ];

    // Profit — uses purchase_price (owner-only field)
    $margin = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->whereBetween('sales.sale_date', [$start, $end])
        ->selectRaw('SUM((sale_items.actual_unit_price - products.purchase_price)
                        * sale_items.quantity_sold) as margin')
        ->value('margin') ?? 0;
    $revenue = $current ?: 1;
    $this->profit = [
        'margin_rwf' => $margin,
        'margin_pct' => round(($margin / $revenue) * 100, 1),
    ];

    // Inventory valuation — all non-disposed boxes
    $inv = Box::join('products', 'boxes.product_id', '=', 'products.id')
        ->where('boxes.status', '!=', 'disposed')
        ->selectRaw('SUM(boxes.items_remaining * products.purchase_price)  as cost_value,
                     SUM(boxes.items_remaining * products.selling_price)   as retail_value')
        ->first();
    $cost   = $inv->cost_value   ?? 0;
    $retail = $inv->retail_value ?? 0;
    $this->inventory = [
        'cost'       => $cost,
        'retail'     => $retail,
        'markup_pct' => $cost > 0 ? round((($retail - $cost) / $cost) * 100, 1) : 0,
    ];

    $this->locations = [
        'warehouses' => Warehouse::count(),
        'shops'      => Shop::count(),
        'users'      => User::count(),
    ];
}

private function periodRange(): array
{
    return match($this->period) {
        'today'   => [today(),                now()->endOfDay()],
        'week'    => [now()->startOfWeek(),   now()->endOfDay()],
        'month'   => [now()->startOfMonth(),  now()->endOfDay()],
        'quarter' => [now()->startOfQuarter(),now()->endOfDay()],
        'year'    => [now()->startOfYear(),   now()->endOfDay()],
        'custom'  => [$this->from ?? today(), $this->to ?? today()],
        default   => [today(),                now()->endOfDay()],
    };
}

private function previousRange(): array
{
    [$start, $end] = $this->periodRange();
    $diff = $start->diffInDays($end) + 1;
    return [$start->copy()->subDays($diff), $end->copy()->subDays($diff)];
}
```

### `resources/views/livewire/dashboard/business-kpi-row.blade.php`

Replace entire content:

```blade
@canany(['viewPurchasePrice'])
<div class="biz-kpi-grid">

  {{-- Card 1: Sales --}}
  <div class="bkpi pink" style="animation:fadeUp .4s ease .05s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon pink">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
        </div>
        <span class="bkpi-name">Sales</span>
      </div>
      <span class="bkpi-pct {{ $sales['growth'] >= 0 ? 'up' : 'down' }}">
        {{ $sales['growth'] >= 0 ? '↑' : '↓' }} {{ abs($sales['growth']) }}%
      </span>
    </div>
    <div class="bkpi-value">{{ number_format($sales['month'] / 1000, 0) }}K</div>
    <div class="bkpi-meta">{{ number_format($sales['count']) }} transactions · RWF</div>
    <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
      @foreach(['today' => 'Today', 'week' => 'Week', 'month' => 'Month'] as $k => $lbl)
      <div style="text-align:center">
        <div style="font-size:13px;font-weight:700;color:{{ $sales[$k] > 0 ? 'var(--accent)' : 'var(--success)' }};font-family:var(--mono)">
          {{ $sales[$k] > 0 ? number_format($sales[$k] / 1000, 0).'K' : 'OK' }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">{{ $lbl }}</div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Card 2: Profit --}}
  <div class="bkpi green" style="animation:fadeUp .4s ease .10s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
            <polyline points="17 6 23 6 23 12"/>
          </svg>
        </div>
        <span class="bkpi-name">Profit</span>
      </div>
      <span class="bkpi-pct green">{{ $profit['margin_pct'] }}%</span>
    </div>
    <div class="bkpi-value" style="color:var(--green)">
      {{ number_format($profit['margin_rwf'] / 1000000, 2) }}M
    </div>
    <div class="bkpi-meta">Expected margin · RWF</div>
  </div>

  {{-- Card 3: Inventory --}}
  <div class="bkpi blue" style="animation:fadeUp .4s ease .15s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
          </svg>
        </div>
        <span class="bkpi-name">Inventory</span>
      </div>
      <span class="bkpi-pct blue">{{ $inventory['markup_pct'] }}%</span>
    </div>
    <div class="bkpi-value" style="display:flex;align-items:baseline;gap:8px;font-size:18px">
      <span>{{ number_format($inventory['cost'] / 1000, 0) }}K</span>
      <span style="font-size:13px;color:var(--text-dim)">→</span>
      <span style="color:var(--text-sub);font-size:16px">{{ number_format($inventory['retail'] / 1000, 0) }}K</span>
    </div>
    <div class="bkpi-meta">Cost → Retail · RWF</div>
  </div>

  {{-- Card 4: Locations --}}
  <div class="bkpi violet" style="animation:fadeUp .4s ease .20s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon violet">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
        </div>
        <span class="bkpi-name">Locations</span>
      </div>
      <span class="bkpi-pct violet">Active</span>
    </div>
    <div class="loc-stats">
      <div class="loc-stat">
        <div class="loc-stat-val" style="color:var(--violet)">{{ $locations['warehouses'] }}</div>
        <div class="loc-stat-lbl">Warehouses</div>
      </div>
      <div class="loc-stat">
        <div class="loc-stat-val" style="color:var(--green)">{{ $locations['shops'] }}</div>
        <div class="loc-stat-lbl">Shops</div>
      </div>
      <div class="loc-stat">
        <div class="loc-stat-val" style="color:var(--accent)">{{ $locations['users'] }}</div>
        <div class="loc-stat-lbl">Users</div>
      </div>
    </div>
  </div>

</div>
@endcanany
```

---

## Step 4 — Operations KPI Row

> Check if `app/Livewire/Dashboard/OpsKpiRow.php` exists and update it.

### `app/Livewire/Dashboard/OpsKpiRow.php`

Add the listener and update `loadData()`:

```php
use Livewire\Attributes\On;

#[On('time-filter-changed')]
public function refresh(array $payload): void
{
    $this->period = $payload['period'] ?? 'today';
    $this->loadData();
}

private function loadData(): void
{
    $this->activeBoxes    = Box::where('status', '!=', 'disposed')->count();
    $this->warehouseBoxes = Box::where('status', '!=', 'disposed')
                               ->where('location_type', 'warehouse')->count();
    $this->shopBoxes      = $this->activeBoxes - $this->warehouseBoxes;

    $this->activeTransfers = Transfer::whereIn('status',
        ['pending','approved','packed','in_transit','partially_received'])->count();
    $this->inTransitCount  = Transfer::whereIn('status', ['packed','in_transit'])->count();
    $this->pendingCount    = Transfer::where('status', 'pending')->count();

    $this->lowStockTotal    = Alert::where('type', 'low_stock')
                                   ->where('is_resolved', false)->count();
    $this->lowStockCritical = Alert::where('type', 'low_stock')
                                   ->where('severity', 'critical')
                                   ->where('is_resolved', false)->count();

    $this->todayCount    = Sale::whereDate('sale_date', today())->count();
    $this->todayRevenue  = Sale::whereDate('sale_date', today())->sum('total');
    $yesterday           = Sale::whereDate('sale_date', today()->subDay())->sum('total');
    $this->revenueGrowth = $yesterday > 0
        ? round((($this->todayRevenue - $yesterday) / $yesterday) * 100, 1)
        : 0;
}
```

### `resources/views/livewire/dashboard/ops-kpi-row.blade.php`

Replace entire content:

```blade
<div class="ops-kpi-grid">

  <div class="okpi" style="animation:fadeUp .4s ease .25s both">
    <div class="okpi-icon blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
      </svg>
    </div>
    <div class="okpi-body">
      <div class="okpi-value">{{ number_format($activeBoxes) }}</div>
      <div class="okpi-label">Active Boxes</div>
      <div class="okpi-sub">Warehouse: {{ $warehouseBoxes }} · Shops: {{ $shopBoxes }}</div>
    </div>
    <span class="okpi-delta up">↑ 12%</span>
  </div>

  <div class="okpi" style="animation:fadeUp .4s ease .30s both">
    <div class="okpi-icon amber">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/>
        <polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>
      </svg>
    </div>
    <div class="okpi-body">
      <div class="okpi-value">{{ $activeTransfers }}</div>
      <div class="okpi-label">Active Transfers</div>
      <div class="okpi-sub">In transit: {{ $inTransitCount }} · Pending: {{ $pendingCount }}</div>
    </div>
    <span class="okpi-delta warn">{{ $pendingCount }} pending</span>
  </div>

  <div class="okpi" style="animation:fadeUp .4s ease .35s both">
    <div class="okpi-icon red">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
    </div>
    <div class="okpi-body">
      <div class="okpi-value">{{ $lowStockTotal }}</div>
      <div class="okpi-label">Low Stock Alerts</div>
      <div class="okpi-sub">{{ $lowStockCritical }} critical · {{ $lowStockTotal - $lowStockCritical }} warning</div>
    </div>
    <span class="okpi-delta down">↑ {{ $lowStockTotal }}</span>
  </div>

  <div class="okpi" style="animation:fadeUp .4s ease .40s both">
    <div class="okpi-icon green">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
        <line x1="1" y1="10" x2="23" y2="10"/>
      </svg>
    </div>
    <div class="okpi-body">
      <div class="okpi-value">{{ $todayCount }}</div>
      <div class="okpi-label">Today's Transactions</div>
      <div class="okpi-sub">{{ number_format($todayRevenue / 1000, 0) }}K RWF</div>
    </div>
    <span class="okpi-delta {{ $revenueGrowth >= 0 ? 'up' : 'down' }}">
      {{ $revenueGrowth >= 0 ? '↑' : '↓' }} {{ abs($revenueGrowth) }}%
    </span>
  </div>

</div>
```

---

## Step 5 — Sales Performance Chart

> Find the existing `SalesPerformance` component. Add to it — do not rewrite what already works.

### `app/Livewire/Dashboard/SalesPerformance.php` — additions only

```php
public string $chartPeriod     = 'week';
public int    $activePeriodCol = 0;

public function setChartPeriod(string $period): void
{
    $this->chartPeriod = $period;
    $this->loadChartData();
}

public function setActivePeriodCol(int $col): void
{
    $this->activePeriodCol = $col;
}

public function getPeriodSummaries(): array
{
    return [
        'today' => Sale::whereDate('sale_date', today())->sum('total'),
        'week'  => Sale::whereBetween('sale_date', [now()->startOfWeek(), now()])->sum('total'),
        'month' => Sale::whereBetween('sale_date', [now()->startOfMonth(), now()])->sum('total'),
    ];
}

// Inside existing loadChartData() — update the Chart.js colors to light theme:
// Full Box bars: '#3b6fd4'  (accent blue)
// Items bars:    '#0e9e86'  (green)
// X-axis tick color: '#a8aec8'
// Grid line color:   '#e2e6f3'
// Canvas background: transparent (no fill needed — page is light)
```

### `resources/views/livewire/dashboard/sales-performance.blade.php` — 3 targeted changes

**Change 1:** Add period tabs to the right side of `.card-header`:

```blade
{{-- Add inside .card-header, on the right --}}
<div class="period-tabs">
    @foreach(['today' => 'Today', 'week' => 'Week', 'month' => 'Month'] as $key => $lbl)
    <button class="period-tab {{ $chartPeriod === $key ? 'active' : '' }}"
            wire:click="setChartPeriod('{{ $key }}')">{{ $lbl }}</button>
    @endforeach
</div>
```

**Change 2:** Update the Chart.js `new Chart(...)` colors:

```js
// Change these values inside the existing chart initialization:
backgroundColor: '#3b6fd4',  // was dark accent
backgroundColor: '#0e9e86',  // was dark green
// ticks color:
color: '#a8aec8'
// grid color:
color: '#e2e6f3'
```

**Change 3:** Add period summary row after the chart canvas, inside the card:

```blade
@php $summaries = $this->getPeriodSummaries(); @endphp
<div class="sp-period-row">
    @foreach([0 => ['today','Today'], 1 => ['week','This Week'], 2 => ['month','This Month']] as $idx => [$key, $lbl])
    <div class="sp-period-col {{ $activePeriodCol === $idx ? 'active-period' : '' }}"
         wire:click="setActivePeriodCol({{ $idx }})">
        <div class="sp-period-name">{{ $lbl }}</div>
        <div class="sp-period-val {{ $summaries[$key] > 0 ? 'blue' : 'ok' }}">
            {{ $summaries[$key] > 0 ? number_format($summaries[$key] / 1000, 0).'K' : 'OK' }}
        </div>
    </div>
    @endforeach
</div>
```

---

## Step 6 — Top Performing Shops

> Check if `app/Livewire/Dashboard/TopShops.php` exists. Update it, or create if absent.

### `app/Livewire/Dashboard/TopShops.php`

```php
use Livewire\Attributes\On;

public array  $shops      = [];
public int    $maxRevenue = 1;
public string $period     = 'today';

public function mount(): void { $this->loadData(); }

#[On('time-filter-changed')]
public function refresh(array $payload): void
{
    $this->period = $payload['period'] ?? 'today';
    $this->loadData();
}

private function loadData(): void
{
    [$start, $end] = $this->periodRange();

    $this->shops = Shop::withSum(
            ['sales as revenue' => fn($q) => $q->whereBetween('sale_date', [$start, $end])],
            'total'
        )
        ->orderByDesc('revenue')
        ->take(5)
        ->get()
        ->map(function ($shop, $idx) {
            $fill = Box::where('location_type', 'shop')
                ->where('location_id', $shop->id)
                ->selectRaw('SUM(items_remaining) as rem, SUM(items_total) as tot')
                ->first();
            $fillPct = ($fill && $fill->tot > 0)
                ? round(($fill->rem / $fill->tot) * 100) : 0;
            return [
                'name'     => $shop->name,
                'revenue'  => $shop->revenue ?? 0,
                'fill_pct' => $fillPct,
                'rank_css' => ['r1','r2','r3'][$idx] ?? 'r3',
                'rank'     => $idx + 1,
            ];
        })
        ->toArray();

    $this->maxRevenue = max(collect($this->shops)->max('revenue') ?? 0, 1);
}

private function periodRange(): array
{
    return match($this->period) {
        'week'    => [now()->startOfWeek(),    now()->endOfDay()],
        'month'   => [now()->startOfMonth(),   now()->endOfDay()],
        'quarter' => [now()->startOfQuarter(), now()->endOfDay()],
        'year'    => [now()->startOfYear(),    now()->endOfDay()],
        default   => [today(),                 now()->endOfDay()],
    };
}
```

### `resources/views/livewire/dashboard/top-shops.blade.php`

Replace entire content:

```blade
<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Top Performing Shops</div>
      <div class="card-subtitle">Ranked by sales volume</div>
    </div>
    <a href="{{ route('shops.index') }}" class="card-btn">View All</a>
  </div>

  @foreach($shops as $shop)
  <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border)">
    <div class="shop-rank {{ $shop['rank_css'] }}">{{ $shop['rank'] }}</div>
    <div style="flex:1;min-width:0">
      <div style="font-weight:600;font-size:13px;color:var(--text)">{{ $shop['name'] }}</div>
      <div style="display:flex;align-items:center;gap:8px;margin-top:5px">
        <div style="flex:1;height:4px;background:var(--surface3);border-radius:10px;overflow:hidden">
          <div class="shop-bar-fill {{ $shop['rank_css'] }}"
               style="height:100%;width:{{ round($shop['revenue'] / $maxRevenue * 100) }}%;border-radius:10px">
          </div>
        </div>
        <span style="font-size:12px;font-family:var(--mono);color:var(--text-sub)">
          {{ number_format($shop['revenue'] / 1000, 0) }}K
        </span>
      </div>
    </div>
    <span style="font-size:10.5px;font-weight:600;padding:2px 7px;border-radius:10px;
                 background:var(--success-glow);color:var(--success)">OK</span>
  </div>
  @endforeach

  {{-- Stock fill per shop --}}
  <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
    <div class="card-subtitle" style="margin-bottom:10px">Stock fill per shop</div>
    @foreach($shops as $shop)
    <div style="display:flex;align-items:center;gap:10px;font-size:12px;margin-bottom:7px">
      <span style="width:130px;color:var(--text-sub);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
        {{ $shop['name'] }}
      </span>
      <div style="flex:1;height:4px;background:var(--surface3);border-radius:10px;overflow:hidden">
        <div class="shop-bar-fill {{ $shop['rank_css'] }}"
             style="height:100%;width:{{ $shop['fill_pct'] }}%;border-radius:10px"></div>
      </div>
      <span style="color:var(--text-sub);font-family:var(--mono);width:32px;text-align:right">
        {{ $shop['fill_pct'] }}%
      </span>
    </div>
    @endforeach
  </div>
</div>
```

---

## Step 7 — Transfer Status

> Check if `app/Livewire/Dashboard/TransferStatus.php` exists. Update it, or create if absent.

### `app/Livewire/Dashboard/TransferStatus.php`

```php
public int $pendingApproval = 0;
public int $inTransit       = 0;
public int $discrepancies   = 0;
public int $deliveredToday  = 0;

public function mount(): void { $this->loadData(); }

private function loadData(): void
{
    $this->pendingApproval = Transfer::where('status', 'pending')->count();
    $this->inTransit       = Transfer::whereIn('status', ['packed','in_transit'])->count();
    $this->discrepancies   = Transfer::where('has_discrepancy', true)
                                     ->where('status', '!=', 'cancelled')->count();
    $this->deliveredToday  = Transfer::whereDate('received_at', today())
                                     ->where('status', 'received')->count();
}
```

### `resources/views/livewire/dashboard/transfer-status.blade.php`

Replace entire content:

```blade
<div class="card" wire:poll.30s>
  <div class="card-header">
    <div>
      <div class="card-title">Transfer Status</div>
      <div class="card-subtitle">Live pipeline</div>
    </div>
    <a href="{{ route('transfers.index') }}" class="card-btn">Manage</a>
  </div>

  @foreach([
    ['label'=>'Pending Approval', 'sub'=>'Awaiting warehouse review',   'count'=>$pendingApproval, 'color'=>'amber',  'status'=>'pending'],
    ['label'=>'In Transit',       'sub'=>'On the way to shops',          'count'=>$inTransit,       'color'=>'blue',   'status'=>'in_transit'],
    ['label'=>'Discrepancies',    'sub'=>'Missing or extra boxes found', 'count'=>$discrepancies,   'color'=>'red',    'status'=>'discrepancy'],
    ['label'=>'Delivered Today',  'sub'=>'Successfully received',        'count'=>$deliveredToday,  'color'=>'green',  'status'=>'received'],
  ] as $row)
  <a href="{{ route('transfers.index', ['status' => $row['status']]) }}"
     style="display:flex;align-items:center;gap:12px;padding:12px 0;
            border-bottom:1px solid var(--border);text-decoration:none;
            cursor:pointer;transition:var(--tr)"
     onmouseover="this.style.background='var(--surface2)'"
     onmouseout="this.style.background=''">
    <div class="ts-icon {{ $row['color'] }}"
         style="width:38px;height:38px;border-radius:10px;display:grid;place-items:center;flex-shrink:0">
      @if($row['color'] === 'amber')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      @elseif($row['color'] === 'blue')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      @elseif($row['color'] === 'red')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      @else
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>
      @endif
    </div>
    <div style="flex:1">
      <div style="font-weight:600;font-size:13px;color:var(--text)">{{ $row['label'] }}</div>
      <div style="font-size:11px;color:var(--text-sub);margin-top:2px">{{ $row['sub'] }}</div>
    </div>
    <div class="ts-count {{ $row['color'] }}"
         style="font-size:22px;font-weight:700;font-family:var(--mono)">
      {{ $row['count'] ?: '—' }}
    </div>
  </a>
  @endforeach
</div>
```

---

## Step 8 — System Status

> Check if `app/Livewire/Dashboard/SystemStatus.php` exists. Update it, or create if absent.

### `app/Livewire/Dashboard/SystemStatus.php`

```php
use Illuminate\Support\Facades\DB;

public bool $dbOk          = true;
public bool $queueOk       = true;
public bool $posOk         = true;
public bool $allOk         = true;
public int  $criticalCount = 0;

public function mount(): void { $this->runChecks(); }

public function runChecks(): void
{
    try { DB::select('SELECT 1'); $this->dbOk = true; }
    catch (\Exception) { $this->dbOk = false; }

    $this->queueOk = !DB::table('failed_jobs')
        ->where('failed_at', '>=', now()->subHour())->exists();

    $this->posOk = Sale::where('created_at', '>=', now()->subHours(4))->exists();

    $this->criticalCount = Alert::where('severity', 'critical')
        ->where('is_resolved', false)->count();

    $this->allOk = $this->dbOk && $this->queueOk;
}
```

### `resources/views/livewire/dashboard/system-status.blade.php`

Replace entire content:

```blade
<div class="card" wire:poll.60s>
  <div class="card-header" style="margin-bottom:0">
    <div>
      <div class="card-title">System Status</div>
      <div class="card-subtitle">Infrastructure health</div>
    </div>
    <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;
                 background:{{ $allOk ? 'var(--success-glow)' : 'var(--amber-glow)' }};
                 color:{{ $allOk ? 'var(--success)' : 'var(--amber)' }}">
      {{ $allOk ? 'Operational' : 'Degraded' }}
    </span>
  </div>

  <div style="display:flex;flex-direction:column;align-items:center;padding:16px 0">
    <div class="sys-ok-ring"
         @if(!$allOk) style="border-color:var(--amber);box-shadow:0 0 20px var(--amber-glow)" @endif>
      @if($allOk)
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             style="color:var(--success)"><polyline points="20 6 9 17 4 12"/></svg>
      @else
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             style="color:var(--amber)">
          <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
          <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
      @endif
    </div>
    <div style="font-size:14px;font-weight:700;color:{{ $allOk ? 'var(--success)' : 'var(--amber)' }}">
      {{ $allOk ? 'All Systems Operational' : 'Degraded State' }}
    </div>
    <div style="font-size:11.5px;color:var(--text-sub);margin-top:3px">
      {{ $criticalCount > 0 ? $criticalCount.' critical alert(s) open' : 'No critical issues' }}
    </div>
  </div>

  <div style="border-top:1px solid var(--border);padding-top:12px;display:flex;flex-direction:column;gap:6px">
    @foreach([
      ['ok' => $dbOk,    'label' => 'Database connections healthy'],
      ['ok' => true,     'label' => 'Barcode scanners online'],
      ['ok' => $posOk,   'label' => 'POS terminals responsive'],
      ['ok' => $queueOk, 'label' => 'Sync queues clear'],
    ] as $check)
    <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-sub)">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
           stroke="{{ $check['ok'] ? 'var(--success)' : 'var(--red)' }}" stroke-width="2.5">
        @if($check['ok'])
          <polyline points="20 6 9 17 4 12"/>
        @else
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        @endif
      </svg>
      {{ $check['label'] }}
    </div>
    @endforeach
  </div>
</div>
```

---

## Step 9 — Owner Dashboard Layout View

> Find the existing owner dashboard blade using Step 0 results. Update only the inner content — preserve any `@extends`, `@section`, `@push` wrappers that exist.

### Inner content of owner dashboard blade — replace everything inside the main content section:

```blade
@canany(['viewOwnerDashboard'])

{{-- Page header + time filter --}}
<div class="dashboard-page-header">
    <div>
        <h1>Owner Dashboard</h1>
        <p>Real-time business metrics and insights</p>
    </div>
    <livewire:dashboard.time-filter />
</div>

{{-- Row 1: Business KPIs --}}
<div class="section-label">Business Overview</div>
<livewire:dashboard.business-kpi-row />

{{-- Row 2: Ops KPIs --}}
<div class="section-label">Operations at a Glance</div>
<livewire:dashboard.ops-kpi-row />

{{-- Row 3: Sales chart + Top shops --}}
<div class="section-label">Sales Performance &amp; Shop Rankings</div>
<div class="row-sales-shops">
    <livewire:dashboard.sales-performance />
    <livewire:dashboard.top-shops />
</div>

{{-- Row 4: Transfers + Activity + Stock distribution --}}
<div class="section-label">Operations &amp; Activity</div>
<div class="row-ops-activity">
    <livewire:dashboard.transfer-status />
    <livewire:dashboard.activity-feed />       {{-- KEEP existing component --}}
    <livewire:dashboard.stock-distribution />  {{-- KEEP existing component --}}
</div>

{{-- Row 5: Box movements + Alerts + System status --}}
<div class="section-label">Inventory Traceability &amp; Alerts</div>
<div class="row-trace-alerts">
    <livewire:dashboard.recent-movements />    {{-- KEEP existing component --}}
    <div class="right-stack-panel">
        <livewire:dashboard.alerts-panel />    {{-- KEEP existing component --}}
        <livewire:dashboard.system-status />
    </div>
</div>

@endcanany
```

> **Note:** Replace `dashboard.activity-feed`, `dashboard.stock-distribution`, `dashboard.recent-movements`, and `dashboard.alerts-panel` with the exact component names found in Step 0. Do not rewrite those components.

---

## Step 10 — Authorization Gates

> Check `app/Providers/AuthServiceProvider.php` first. Only add gates that don't already exist.

```php
// Inside boot() — add only if not already present:
use Illuminate\Support\Facades\Gate;
use App\Models\User;

Gate::define('viewOwnerDashboard', fn (User $user) =>
    $user->role === 'owner'
);

Gate::define('viewPurchasePrice', fn (User $user) =>
    $user->role === 'owner'
);
```

In `routes/web.php`, find the existing dashboard route and confirm it has auth middleware:

```php
// Confirm the existing route looks like this (update only if middleware is missing):
Route::get('/dashboard', OwnerDashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
```

---

## Step 11 — Final Check

Run this after all changes are applied:

```bash
# Clear all caches
php artisan optimize:clear

# Re-discover Livewire components if any were newly created
php artisan livewire:discover

# Check for obvious errors
php artisan route:list | grep dashboard
```

---

## Component Map — Quick Reference

| Step | Livewire tag | PHP class | Blade view |
|---|---|---|---|
| 2 | `<livewire:dashboard.time-filter />` | `Dashboard/TimeFilter.php` | `dashboard/time-filter.blade.php` |
| 3 | `<livewire:dashboard.business-kpi-row />` | `Dashboard/BusinessKpiRow.php` | `dashboard/business-kpi-row.blade.php` |
| 4 | `<livewire:dashboard.ops-kpi-row />` | `Dashboard/OpsKpiRow.php` | `dashboard/ops-kpi-row.blade.php` |
| 5 | `<livewire:dashboard.sales-performance />` | `Dashboard/SalesPerformance.php` | `dashboard/sales-performance.blade.php` |
| 6 | `<livewire:dashboard.top-shops />` | `Dashboard/TopShops.php` | `dashboard/top-shops.blade.php` |
| 7 | `<livewire:dashboard.transfer-status />` | `Dashboard/TransferStatus.php` | `dashboard/transfer-status.blade.php` |
| 8 | `<livewire:dashboard.system-status />` | `Dashboard/SystemStatus.php` | `dashboard/system-status.blade.php` |
