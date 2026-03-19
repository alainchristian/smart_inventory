# Dashboard — Mobile Fix, Text Size, Box-Centric Stock Distribution
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read DASHBOARD_POLISH.md and follow every step in order."

---

## Read these files first

```bash
cat resources/css/app.css | grep -n "row-sales-shops\|row-ops-activity\|bkpi-value\|okpi-value\|section-label" | head -30
cat resources/views/livewire/dashboard/stock-distribution.blade.php
cat resources/views/livewire/dashboard/transfer-status.blade.php
```

---

## STEP 1 — Fix mobile responsiveness for Sales Performance & Shop Rankings

**File:** `resources/css/app.css`

Find the block that contains `.row-sales-shops` and the responsive
breakpoints. Ensure these rules exist and are correct. Add or replace:

```css
/* Row 3: Sales + Top Shops */
.row-sales-shops {
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 18px;
    margin-bottom: 26px;
}

/* Row 4: Ops Activity */
.row-ops-activity {
    display: grid;
    grid-template-columns: 1fr 1fr 300px;
    gap: 18px;
    margin-bottom: 26px;
    align-items: start;
}

/* Row 5: Trace + Alerts */
.row-trace-alerts {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 18px;
}

@media (max-width: 1200px) {
    .row-sales-shops  { grid-template-columns: 1fr; }
    .row-ops-activity { grid-template-columns: 1fr 1fr; }
    .row-ops-activity > *:last-child { grid-column: 1 / -1; }
    .row-trace-alerts { grid-template-columns: 1fr; }
}

@media (max-width: 900px) {
    .row-ops-activity { grid-template-columns: 1fr; }
    .biz-kpi-grid, .ops-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .row-sales-shops,
    .row-ops-activity,
    .row-trace-alerts { grid-template-columns: 1fr; gap: 14px; }
    .biz-kpi-grid, .ops-kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .right-stack-panel { flex-direction: column; }
}
```

---

## STEP 2 — Increase text sizes globally in the dashboard

**File:** `resources/css/app.css`

Find and update these font-size values:

```css
/* KPI cards */
.bkpi-value  → font-size: 28px
.bkpi-meta   → font-size: 12px
.bkpi-name   → font-size: 13px

/* Ops KPI cards */
.okpi-value  → font-size: 24px
.okpi-label  → font-size: 13px
.okpi-sub    → font-size: 12px

/* Section labels */
.section-label → font-size: 12px

/* Card titles */
.card-title    → font-size: 16px
.card-subtitle → font-size: 13px
```

---

## STEP 3 — Rewrite Stock Distribution blade (box-centric, larger text)

**File:** `resources/views/livewire/dashboard/stock-distribution.blade.php`

Find the legend section — the `@foreach($this->stockDistribution as $location)` loop
that shows location name, type badge, count and percentage.

Replace that `@foreach` loop and surrounding wrapper entirely with:

```blade
<div style="margin-top:12px">
    @foreach($this->stockDistribution as $loc)
    @php
        $pct = $this->totalBoxes > 0
            ? round(($loc['box_count'] / $this->totalBoxes) * 100)
            : 0;
    @endphp
    <div style="display:flex;align-items:center;gap:10px;
                padding:10px 0;border-bottom:1px solid var(--border)">

        {{-- Color dot --}}
        <div style="width:10px;height:10px;border-radius:50%;
                    flex-shrink:0;background:{{ $loc['color'] }}">
        </div>

        {{-- Name + type --}}
        <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;color:var(--text);
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                {{ $loc['location_name'] }}
            </div>
            <div style="font-size:11px;color:var(--text-dim);margin-top:1px;
                        text-transform:capitalize">
                {{ $loc['location_type'] }}
            </div>
        </div>

        {{-- Box count — PRIMARY --}}
        <div style="text-align:right;flex-shrink:0">
            <div style="font-size:18px;font-weight:800;font-family:var(--mono);
                        letter-spacing:-0.5px;color:var(--text)">
                {{ number_format($loc['box_count']) }}
            </div>
            <div style="font-size:11px;color:var(--text-dim)">
                boxes · {{ $pct }}%
            </div>
        </div>

    </div>
    @endforeach

    {{-- Damaged boxes --}}
    @if($this->damagedBoxes > 0)
    <div style="display:flex;align-items:center;gap:10px;padding:10px 0">
        <div style="width:10px;height:10px;border-radius:50%;
                    flex-shrink:0;background:var(--red-dim);
                    border:1.5px solid var(--red)">
        </div>
        <div style="flex:1;font-size:13px;color:var(--text-dim)">
            Damaged (not sellable)
        </div>
        <div style="font-size:16px;font-weight:700;font-family:var(--mono);
                    color:var(--red)">
            {{ $this->damagedBoxes }}
        </div>
    </div>
    @else
    <div style="display:flex;align-items:center;gap:10px;padding:10px 0">
        <div style="width:10px;height:10px;border-radius:50%;
                    flex-shrink:0;background:var(--surface3)">
        </div>
        <div style="flex:1;font-size:13px;color:var(--text-dim)">
            Damaged boxes (not sellable)
        </div>
        <div style="font-size:13px;font-weight:600;color:var(--text-dim)">
            None
        </div>
    </div>
    @endif
</div>
```

Then find the Location Breakdown section added by the previous
instruction (DASHBOARD_CARD_FILL.md). Remove it entirely — the
improved legend above already shows the box counts prominently,
making the duplicate breakdown section redundant.

---

## STEP 4 — Increase text sizes in Transfer Status blade

**File:** `resources/views/livewire/dashboard/transfer-status.blade.php`

Make these targeted size increases:

Find the title div (`Transfer Status`):
- Change `font-size:15px` to `font-size:17px`

Find the subtitle div (`Live pipeline`):
- Change `font-size:12px` to `font-size:13px`

Find each status row label (`.ts-label` style or inline label text):
- Change `font-size:13px` to `font-size:14px`

Find each status row subtitle (e.g. "Awaiting warehouse review"):
- Change `font-size:11px` to `font-size:12px`

Find the count circle:
- Change `font-size:13px` to `font-size:14px`

Find the "Recent Active" section label:
- Change `font-size:10px` to `font-size:11px`

Find each recent transfer name:
- Change `font-size:12px` to `font-size:13px`

Find each recent transfer age:
- Change `font-size:11px` to `font-size:12px`

---

## STEP 5 — Increase donut chart centre text

**File:** `resources/views/livewire/dashboard/stock-distribution.blade.php`

Find the centre text inside the donut chart. It has two elements:
- The total number (e.g. `1,800`)
- The label (`Sellable Boxes`)

Update them:
```html
{{-- Total number --}}
<div id="stockDistTotal" style="font-size:28px;font-weight:800;
     letter-spacing:-1px;color:var(--text)">
    {{ number_format($this->totalBoxes) }}
</div>
{{-- Label --}}
<div style="font-size:13px;margin-top:2px;color:var(--text-sub);
            font-weight:600">
    Sellable Boxes
</div>
```

---

## STEP 6 — Rebuild and clear

```bash
npm run build
php artisan view:clear && php artisan cache:clear
```

---

## Verification

**Mobile (≤640px):**
- Sales Performance + Top Shops stack vertically
- All 3 Operations cards stack vertically
- KPI rows become 2-column grids

**Tablet (641–1200px):**
- Sales + Top Shops stack (chart gets full width)
- Operations = 2 columns, Stock Distribution spans full width

**Desktop (>1200px):**
- All rows show at designed column counts

**Text sizes:**
- KPI values are large and readable at 28px
- Card subtitles readable at 13px
- Section labels visible at 12px

**Stock Distribution:**
- Box count is the dominant number (18px bold) per location
- Percentage is secondary (11px below)
- Location name and type are clearly readable
- Damaged boxes section shown with red indicator
- No duplicate "Location Breakdown" section
