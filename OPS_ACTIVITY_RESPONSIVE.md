# Dashboard — Operations & Activity Responsiveness
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read OPS_ACTIVITY_RESPONSIVE.md and follow every step in order."

---

## Read these files first

```bash
cat resources/views/owner/dashboard.blade.php | grep -A10 "Operations"
cat resources/views/livewire/dashboard/transfer-status.blade.php
cat resources/views/livewire/dashboard/activity-feed.blade.php
cat app/Livewire/Dashboard/TransferStatus.php
cat resources/css/app.css | grep -A5 "row-ops-activity"
```

---

## The problem

The Operations & Activity section has two components that need to sit
side by side on desktop and stack cleanly on mobile. The current grid
is either a 3-column CSS class (built when Stock Distribution existed)
or an inline style that has no mobile breakpoint.

The goal:
- **Desktop (>1024px):** Transfer Pipeline left (40%) | Activity Feed right (60%)
- **Tablet (640–1024px):** same 40/60 but tighter padding
- **Mobile (<640px):** fully stacked — Transfer Pipeline first, Activity Feed below

---

## STEP 1 — Fix the wrapper in dashboard.blade.php

**File:** `resources/views/owner/dashboard.blade.php`

Find the Operations & Activity section. Replace whatever wrapper div
holds the two components with:

```blade
<div class="oa-grid">
    <livewire:dashboard.transfer-status />
    <livewire:dashboard.activity-feed />
</div>
```

---

## STEP 2 — Add the responsive CSS to app.css

**File:** `resources/css/app.css`

Find any existing `.row-ops-activity` rules and replace them entirely.
Then append the following at the end of the file (or replace
`.row-ops-activity` if it exists):

```css
/* ═══════════════════════════════════════════════════
   OPERATIONS & ACTIVITY — Responsive Grid
═══════════════════════════════════════════════════ */

.oa-grid {
    display: grid;
    grid-template-columns: 2fr 3fr;
    gap: 20px;
    margin-bottom: 26px;
    align-items: start;
}

@media (max-width: 1024px) {
    .oa-grid {
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
}

@media (max-width: 640px) {
    .oa-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }
}
```

Then run `npm run build`.

---

## STEP 3 — Rewrite transfer-status blade

**File:** `resources/views/livewire/dashboard/transfer-status.blade.php`

The PHP component (`app/Livewire/Dashboard/TransferStatus.php`) has
these properties — use them exactly:
- `$pendingApproval`
- `$inTransit`
- `$discrepancies`
- `$deliveredToday`

Replace the entire file:

```blade
<div style="background:var(--surface);border:1px solid var(--border);
            border-radius:var(--r);overflow:hidden">
<style>
.ts-head {
    display:flex;align-items:center;justify-content:space-between;
    padding:16px 20px;border-bottom:1px solid var(--border);
}
.ts-title { font-size:15px;font-weight:700;color:var(--text) }
.ts-link  {
    font-size:12px;font-weight:600;color:var(--accent);
    text-decoration:none;padding:4px 10px;border-radius:7px;
    background:var(--accent-dim);transition:opacity var(--tr);
}
.ts-link:hover { opacity:.8 }

/* Status rows */
.ts-rows { padding:8px 0 }
.ts-row  {
    display:flex;align-items:center;gap:12px;
    padding:11px 20px;transition:background var(--tr);
    cursor:default;
}
.ts-row:hover { background:var(--surface2) }
.ts-row:not(:last-child) { border-bottom:1px solid var(--border) }

.ts-dot  {
    width:10px;height:10px;border-radius:50%;flex-shrink:0;
}
.ts-label {
    flex:1;font-size:13px;font-weight:500;color:var(--text-sub);
    white-space:nowrap;
}
.ts-count {
    font-size:18px;font-weight:800;font-family:var(--mono);
    letter-spacing:-0.5px;min-width:28px;text-align:right;
}
.ts-bar-wrap {
    width:80px;height:5px;background:var(--surface2);
    border-radius:4px;overflow:hidden;flex-shrink:0;
}
.ts-bar-fill { height:100%;border-radius:4px;transition:width .4s ease }

/* Total footer */
.ts-footer {
    display:flex;align-items:center;justify-content:space-between;
    padding:12px 20px;border-top:2px solid var(--border);
    background:var(--surface2);
}
.ts-total-label { font-size:11px;font-weight:700;letter-spacing:.5px;
                  text-transform:uppercase;color:var(--text-dim) }
.ts-total-val   { font-size:20px;font-weight:800;font-family:var(--mono);
                  color:var(--text);letter-spacing:-0.5px }

/* Warning badge */
.ts-warn {
    font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;
    background:var(--red-dim);color:var(--red);font-family:var(--mono);
    white-space:nowrap;
}

/* Mobile: hide bar on very small screens */
@media(max-width:400px) {
    .ts-bar-wrap { display:none }
}
</style>

{{-- Header --}}
<div class="ts-head">
    <div class="ts-title">Transfer Pipeline</div>
    <a href="{{ route('owner.transfers.index') }}" class="ts-link">
        View All →
    </a>
</div>

{{-- Status rows --}}
@php
    $total = $pendingApproval + $inTransit + $discrepancies + $deliveredToday;
    $rows  = [
        [
            'label'   => 'Pending Approval',
            'count'   => $pendingApproval,
            'color'   => 'var(--amber)',
            'bg'      => 'var(--amber)',
            'link'    => route('owner.transfers.index') . '?status=pending',
        ],
        [
            'label'   => 'In Transit',
            'count'   => $inTransit,
            'color'   => 'var(--violet)',
            'bg'      => 'var(--violet)',
            'link'    => route('owner.transfers.index') . '?status=in_transit',
        ],
        [
            'label'   => 'Discrepancies',
            'count'   => $discrepancies,
            'color'   => 'var(--red)',
            'bg'      => 'var(--red)',
            'link'    => route('owner.transfers.index') . '?status=discrepancy',
        ],
        [
            'label'   => 'Delivered Today',
            'count'   => $deliveredToday,
            'color'   => 'var(--green)',
            'bg'      => 'var(--green)',
            'link'    => route('owner.transfers.index') . '?status=delivered',
        ],
    ];
@endphp

<div class="ts-rows">
    @foreach($rows as $row)
    <a href="{{ $row['link'] }}"
       class="ts-row"
       style="text-decoration:none"
       onmouseover="this.style.background='var(--surface2)'"
       onmouseout="this.style.background='transparent'">

        <div class="ts-dot" style="background:{{ $row['bg'] }}"></div>

        <span class="ts-label">{{ $row['label'] }}</span>

        {{-- Warning badge for discrepancies --}}
        @if($row['label'] === 'Discrepancies' && $row['count'] > 0)
            <span class="ts-warn">needs review</span>
        @endif

        {{-- Progress bar --}}
        <div class="ts-bar-wrap">
            <div class="ts-bar-fill"
                 style="width:{{ $total > 0 ? round(($row['count'] / $total) * 100) : 0 }}%;
                        background:{{ $row['bg'] }}">
            </div>
        </div>

        <span class="ts-count" style="color:{{ $row['color'] }}">
            {{ $row['count'] }}
        </span>

    </a>
    @endforeach
</div>

{{-- Footer total --}}
<div class="ts-footer">
    <span class="ts-total-label">Active Transfers</span>
    <span class="ts-total-val">{{ $total }}</span>
</div>

</div>
```

---

## STEP 4 — Make activity-feed blade responsive

**File:** `resources/views/livewire/dashboard/activity-feed.blade.php`

Read the file first. Find the outermost wrapper `<div>` that contains
the card. It likely uses a fixed `max-height` or `height` in inline styles.

Make two targeted changes:

**4a** — Find the outermost wrapper div. Add or update its style to include:
```
min-height: 0;
```
This prevents it from collapsing on mobile when the grid stacks.

**4b** — Find the scrollable inner div (the one with `overflow-y:auto`
and a `max-height`). Change its `max-height` so it adapts:

If it currently has a fixed pixel value like `max-height:420px` or
`max-height:480px`, change it to:

```
max-height: min(480px, 60vh)
```

This ensures it never exceeds 60% of the viewport height on mobile,
preventing the activity list from dominating a small screen.

**4c** — Find any inline `height:100%` on the card root element.
If present, change it to `height:auto` — fixed heights on Livewire
roots cause problems when the grid collapses to single column.

---

## STEP 5 — Clear caches and rebuild

```bash
npm run build
php artisan view:clear && php artisan cache:clear
```

---

## Do NOT touch

- `app/Livewire/Dashboard/TransferStatus.php` — PHP is correct, no changes
- `app/Livewire/Dashboard/ActivityFeed.php` — PHP is correct, no changes
- Any other dashboard sections

---

## Verification

**Desktop (≥1025px):**
- Transfer Pipeline occupies left ~40% of the section
- Activity Feed occupies right ~60%
- Both cards have equal top alignment
- Transfer Pipeline shows 4 status rows with progress bars and a total footer

**Tablet (641–1024px):**
- Grid switches to 1fr 1fr (equal columns)
- Both cards shrink proportionally, content still readable

**Mobile (≤640px):**
- Grid stacks to single column
- Transfer Pipeline appears first (full width)
- Activity Feed appears below (full width)
- Activity Feed scroll area is limited to 60vh so it doesn't push
  content below the fold
- Progress bars hide on screens ≤400px (too narrow)

**Each transfer status row:**
- Colored dot · label · optional warning badge · progress bar · count
- Entire row is clickable, links to the transfers list filtered by that status
- Discrepancy rows show a red "needs review" badge when count > 0
