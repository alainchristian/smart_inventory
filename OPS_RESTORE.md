# Operations & Activity — Restore Original 3-Column Layout
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read OPS_RESTORE.md and follow every step in order."

---

## Read these files first

```bash
cat resources/views/owner/dashboard.blade.php | grep -A10 "Operations"
cat resources/css/app.css | grep -A8 "row-ops-activity\|oa-grid"
```

---

## STEP 1 — Restore the dashboard blade to 3 components

**File:** `resources/views/owner/dashboard.blade.php`

Find the Operations & Activity section. Replace whatever wrapper
currently holds the components with:

```blade
{{-- Row 4: Transfers + Activity + Stock distribution --}}
<div class="section-label">Operations &amp; Activity</div>
<div class="row-ops-activity">
    <livewire:dashboard.transfer-status />
    <livewire:dashboard.activity-feed />
    <livewire:dashboard.stock-distribution />
</div>
```

---

## STEP 2 — Restore the CSS grid rule

**File:** `resources/css/app.css`

Find `.oa-grid` and any `.oa-grid > *` rules added recently.
Delete them entirely.

Find `.row-ops-activity` and make sure it reads:

```css
.row-ops-activity {
    display: grid;
    grid-template-columns: 1fr 1fr 300px;
    gap: 18px;
    margin-bottom: 26px;
    align-items: start;
}
```

If `.row-ops-activity` does not exist, add it.

Add the mobile breakpoint right after:

```css
@media (max-width: 900px) {
    .row-ops-activity {
        grid-template-columns: 1fr 1fr;
    }
}
@media (max-width: 640px) {
    .row-ops-activity {
        grid-template-columns: 1fr;
    }
}
```

---

## STEP 3 — Restore Transfer Status blade to its clean original

**File:** `resources/views/livewire/dashboard/transfer-status.blade.php`

Replace the entire file with:

```blade
<div style="background:var(--surface);border:1px solid var(--border);
            border-radius:var(--r);overflow:hidden">

    {{-- Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;
                padding:18px 20px;border-bottom:1px solid var(--border)">
        <div>
            <div style="font-size:15px;font-weight:700;color:var(--text)">
                Transfer Status
            </div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:2px">
                Live pipeline
            </div>
        </div>
        <a href="{{ route('owner.transfers.index') }}"
           style="font-size:12px;font-weight:600;color:var(--accent);
                  text-decoration:none;padding:4px 10px;border-radius:7px;
                  background:var(--accent-dim)">
            Manage
        </a>
    </div>

    {{-- Status rows --}}
    @php
        $statuses = [
            [
                'label'    => 'Pending Approval',
                'sub'      => 'Awaiting warehouse review',
                'count'    => $pendingApproval,
                'color'    => 'var(--amber)',
                'bg'       => 'var(--amber-dim)',
                'icon'     => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'link'     => route('owner.transfers.index') . '?status=pending',
            ],
            [
                'label'    => 'In Transit',
                'sub'      => 'On the way to shops',
                'count'    => $inTransit,
                'color'    => 'var(--accent)',
                'bg'       => 'var(--accent-dim)',
                'icon'     => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10',
                'link'     => route('owner.transfers.index') . '?status=in_transit',
            ],
            [
                'label'    => 'Discrepancies',
                'sub'      => 'Missing or extra boxes found',
                'count'    => $discrepancies,
                'color'    => 'var(--red)',
                'bg'       => 'var(--red-dim)',
                'icon'     => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'link'     => route('owner.transfers.index'),
            ],
            [
                'label'    => 'Delivered Today',
                'sub'      => 'Successfully received',
                'count'    => $deliveredToday,
                'color'    => 'var(--green)',
                'bg'       => 'var(--green-dim)',
                'icon'     => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'link'     => route('owner.transfers.index') . '?status=delivered',
            ],
        ];
    @endphp

    <div>
        @foreach($statuses as $s)
        <a href="{{ $s['link'] }}"
           style="display:flex;align-items:center;gap:14px;
                  padding:14px 20px;text-decoration:none;
                  border-bottom:1px solid var(--border);
                  transition:background var(--tr)"
           onmouseover="this.style.background='var(--surface2)'"
           onmouseout="this.style.background='transparent'">

            {{-- Icon --}}
            <div style="width:36px;height:36px;border-radius:10px;flex-shrink:0;
                        background:{{ $s['bg'] }};
                        display:flex;align-items:center;justify-content:center">
                <svg width="16" height="16" fill="none" stroke="{{ $s['color'] }}"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="{{ $s['icon'] }}"/>
                </svg>
            </div>

            {{-- Label --}}
            <div style="flex:1;min-width:0">
                <div style="font-size:14px;font-weight:600;color:var(--text)">
                    {{ $s['label'] }}
                </div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:1px">
                    {{ $s['sub'] }}
                </div>
            </div>

            {{-- Count --}}
            <div style="width:28px;height:28px;border-radius:50%;flex-shrink:0;
                        background:{{ $s['bg'] }};
                        display:flex;align-items:center;justify-content:center;
                        font-size:13px;font-weight:800;font-family:var(--mono);
                        color:{{ $s['color'] }}">
                {{ $s['count'] }}
            </div>

        </a>
        @endforeach
    </div>

</div>
```

---

## STEP 4 — Restore Activity Feed scroll

**File:** `resources/views/livewire/dashboard/activity-feed.blade.php`

Find the scrollable inner div. Make sure it has:
```
style="overflow-y:auto;max-height:400px"
```

Remove `flex:1` and `min-height:0` if they were added.

---

## STEP 5 — Rebuild and clear

```bash
npm run build
php artisan view:clear && php artisan cache:clear
```

---

## Verification

Operations & Activity shows 3 equal-width cards side by side:
- Transfer Status (icon rows with counts)
- Live Activity (scrollable feed)
- Stock Distribution (donut chart)

On tablet (≤900px): 2 columns.
On mobile (≤640px): 1 column stacked.
