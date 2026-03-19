# Dashboard — Fixed Card Heights + Distinct Location Colors
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read DASHBOARD_FIXED_CARDS.md and follow every step in order."

---

## Read these files first

```bash
cat resources/views/livewire/dashboard/stock-distribution.blade.php
cat app/Livewire/Dashboard/StockDistribution.php
cat resources/css/app.css | grep -n "okpi\|bkpi\|row-ops\|row-trace\|row-sales" | head -30
```

---

## What we are fixing

1. All dashboard cards get a fixed height — content scrolls inside
   rather than pushing the layout around when shops are added/removed.
2. Stock Distribution donut uses a distinct color per location (not just
   warehouse=blue / shop=green) so adding more shops doesn't produce
   identical colors.

---

## STEP 1 — Add fixed-height card CSS

**File:** `resources/css/app.css`

Add this block. Place it after the existing `.bkpi` and `.okpi` rules:

```css
/* ═══════════════════════════════════════════
   DASHBOARD CARD FIXED HEIGHTS
   All cards have a fixed height. Content that
   overflows scrolls inside the card.
═══════════════════════════════════════════ */

/* Operations & Activity row — all 3 cards */
.row-ops-activity > * {
    height: 480px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* Attention & Alerts row — left panel and right stack */
.row-trace-alerts > * {
    height: 520px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.right-stack-panel > * {
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

/* Sales Performance & Top Shops row */
.row-sales-shops > * {
    height: 460px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* Scrollable inner areas — these classes must be added
   to the scrollable div inside each card blade view */
.card-scroll {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

/* Mobile — revert to natural height when stacked */
@media (max-width: 900px) {
    .row-ops-activity > *,
    .row-trace-alerts > *,
    .row-sales-shops > * {
        height: auto;
        min-height: 300px;
        max-height: 520px;
    }
    .right-stack-panel > * {
        height: auto;
        min-height: 200px;
        max-height: 400px;
    }
}

@media (max-width: 640px) {
    .row-ops-activity > *,
    .row-trace-alerts > *,
    .row-sales-shops > * {
        height: auto;
        min-height: 0;
        max-height: none;
    }
    .right-stack-panel > * {
        max-height: none;
    }
}
```

---

## STEP 2 — Add .card-scroll to scrollable inner divs

For each of these blade files, find the inner div that holds the
scrollable content (usually the one with `overflow-y:auto` or
`max-height`) and **add the class `card-scroll`** to it, removing
any inline `max-height` and `overflow-y` styles since the CSS class
now handles that.

Files to update:

```
resources/views/livewire/dashboard/activity-feed.blade.php
resources/views/livewire/dashboard/transfer-status.blade.php
resources/views/livewire/dashboard/stock-distribution.blade.php
resources/views/livewire/dashboard/owner-actions.blade.php
resources/views/livewire/dashboard/alerts-panel.blade.php
resources/views/livewire/dashboard/top-shops.blade.php
```

For each file: read it first, find the scrollable inner div,
add `card-scroll` class to it, remove inline `overflow-y:auto`
and `max-height` if present on that same div.

---

## STEP 3 — Generate distinct colors per location in PHP component

**File:** `app/Livewire/Dashboard/StockDistribution.php`

Replace the `getLocationColor()` method entirely:

```php
private function getLocationColor(string $locationType, int $locationId): string
{
    // Warehouses always get blue family
    $warehousePalette = [
        '#3b6fd4', '#1d4ed8', '#2563eb', '#1e40af', '#3730a3',
    ];

    // Shops get a varied palette so each shop has a distinct color
    $shopPalette = [
        '#0e9e86', // teal
        '#7c3aed', // violet
        '#d97706', // amber
        '#db2777', // pink
        '#0891b2', // cyan
        '#65a30d', // lime
        '#dc2626', // red
        '#9333ea', // purple
        '#0284c7', // sky
        '#16a34a', // green
    ];

    if ($locationType === 'warehouse') {
        // Cycle through blue family based on locationId
        return $warehousePalette[$locationId % count($warehousePalette)];
    }

    // Shops: use locationId to consistently pick a color
    return $shopPalette[$locationId % count($shopPalette)];
}
```

Then update the call site in `getStockDistributionProperty()`.
Find this line:
```php
'color' => $this->getLocationColor($item->location_type->value),
```

Replace with:
```php
'color' => $this->getLocationColor(
    $item->location_type->value,
    (int) $item->location_id
),
```

---

## STEP 4 — Ensure chart canvas uses the new colors

**File:** `resources/views/livewire/dashboard/stock-distribution.blade.php`

Find the Alpine.js `stockDistChart()` function (inside a `<script>` or
`@script` block). It builds the Chart.js dataset. Find the line that
sets `backgroundColor` for the donut segments.

It likely reads something like:
```javascript
backgroundColor: data.map(d => d.color),
```

If it does, no change needed — colors come from the PHP component.

If it has hardcoded colors like `['#3b6fd4', '#0e9e86']`, replace with:
```javascript
backgroundColor: data.map(d => d.color),
borderColor: data.map(d => d.color),
```

Also update the legend rendering in the blade to use `$loc['color']`
from the PHP-provided data rather than any match() based on type.

---

## STEP 5 — Make Transfer Status card flex-column internally

**File:** `resources/views/livewire/dashboard/transfer-status.blade.php`

The outermost wrapper div already has the fixed height from Step 1 CSS.
Now make its internal layout flex so header stays fixed and content scrolls.

Find the outermost wrapper `<div>` and ensure it has:
```
style="background:var(--surface);border:1px solid var(--border);
       border-radius:var(--r);overflow:hidden;
       display:flex;flex-direction:column;height:100%"
```

Find the div that wraps the 4 status rows + recent transfers section.
Add `class="card-scroll"` to it so it scrolls when content is tall.

Keep the card header (title + Manage link) outside the scroll area
so it stays pinned at the top.

---

## STEP 6 — Make Stock Distribution card flex-column internally

**File:** `resources/views/livewire/dashboard/stock-distribution.blade.php`

Ensure the outermost wrapper has:
```
display:flex;flex-direction:column;height:100%
```

The donut chart section should NOT scroll — it should be flex-shrink:0.
Find the donut canvas wrapper and add `style="flex-shrink:0"`.

The legend section (location list) should scroll. Find the div
wrapping the `@foreach($this->stockDistribution ...)` loop and add
`class="card-scroll"` to it.

---

## STEP 7 — Rebuild and clear

```bash
npm run build
php artisan view:clear && php artisan cache:clear
```

---

## Verification

1. Open dashboard — all cards in rows 3, 4, 5 are the same height
2. Add a new shop via /owner/shops → refresh dashboard → Stock
   Distribution card shows new shop with a distinct color, layout
   unchanged, legend scrolls inside the card
3. Stock Distribution donut: each location has its own color from
   the palette — no two shops share the same color (up to 10 shops)
4. Activity Feed with 12+ items scrolls inside its fixed card height
5. OwnerActions with many items scrolls inside its card height
6. On mobile (≤640px) cards return to natural height — no fixed height
7. On tablet (≤900px) cards have a max-height of 520px and scroll
