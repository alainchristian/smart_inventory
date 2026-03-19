# Operations & Activity — Fix Height Mismatch
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read OPS_ACTIVITY_FIX.md and follow every step in order."

---

## Read these files first

```bash
cat resources/views/livewire/dashboard/transfer-status.blade.php
cat resources/views/livewire/dashboard/activity-feed.blade.php
cat app/Livewire/Dashboard/TransferStatus.php
cat resources/css/app.css | grep -A8 "oa-grid"
```

---

## The problem

`align-items: stretch` forces both cards to the same height, but the
Activity Feed has 12+ items while Transfer Pipeline has only 4 rows —
resulting in a massive empty void in the left card.

The correct approach: let cards be their natural height, but fill the
Transfer Pipeline card with a mini recent-transfers list so both cards
have similar content density.

---

## STEP 1 — Revert align-items in app.css

**File:** `resources/css/app.css`

Find the `.oa-grid` rule. Change:
```css
align-items: stretch;
```
Back to:
```css
align-items: start;
```

Also remove any `.oa-grid > *` rule with `height: auto !important`
if it was added previously.

---

## STEP 2 — Remove flex layout from Transfer Pipeline card

**File:** `resources/views/livewire/dashboard/transfer-status.blade.php`

Find the outermost wrapper `<div>` of the card. Remove these properties
from its inline style if they were added:
- `height:100%`
- `display:flex`
- `flex-direction:column`

Find the `.ts-rows` div. Remove `flex:1` from its style if present.

---

## STEP 3 — Remove flex layout from Activity Feed card

**File:** `resources/views/livewire/dashboard/activity-feed.blade.php`

Find the outermost wrapper `<div>`. Remove if added:
- `height:100%`
- `display:flex`
- `flex-direction:column`

Find the scrollable inner div (the one with `overflow-y:auto`).
Restore it to just `overflow-y:auto` with a `max-height` value.
Remove `min-height:0` and `flex:1` if they were added.

---

## STEP 4 — Add recentTransfers to the PHP component

**File:** `app/Livewire/Dashboard/TransferStatus.php`

Add a new public property after the existing properties:

```php
public array $recentTransfers = [];
```

Find the `loadData()` method. At the very end of the method, before the
closing `}`, add:

```php
$this->recentTransfers = \App\Models\Transfer::with(['fromWarehouse', 'toShop'])
    ->whereIn('status', ['pending', 'approved', 'in_transit', 'delivered'])
    ->orderByDesc('created_at')
    ->limit(4)
    ->get()
    ->map(fn($t) => [
        'id'     => $t->id,
        'from'   => $t->fromWarehouse?->name ?? '—',
        'to'     => $t->toShop?->name ?? '—',
        'status' => $t->status->value,
        'age'    => $t->created_at->diffForHumans(),
    ])->toArray();
```

---

## STEP 5 — Add recent transfers list to the blade

**File:** `resources/views/livewire/dashboard/transfer-status.blade.php`

Find the `.ts-footer` div (the ACTIVE TRANSFERS bar at the bottom of
the card). Insert the following block **immediately before** that footer
div:

```blade
@if(count($recentTransfers) > 0)
<div style="border-top:1px solid var(--border)">

    <div style="padding:10px 20px 4px;font-size:10px;font-weight:700;
                letter-spacing:.5px;text-transform:uppercase;
                color:var(--text-dim)">
        Recent Active
    </div>

    @foreach($recentTransfers as $t)
    @php
        $sc = match($t['status']) {
            'pending'    => ['bg' => 'var(--amber-dim)',  'c' => 'var(--amber)'],
            'approved'   => ['bg' => 'var(--accent-dim)', 'c' => 'var(--accent)'],
            'in_transit' => ['bg' => 'var(--violet-dim)', 'c' => 'var(--violet)'],
            'delivered'  => ['bg' => 'var(--green-dim)',  'c' => 'var(--green)'],
            default      => ['bg' => 'var(--surface2)',   'c' => 'var(--text-dim)'],
        };
    @endphp
    <a href="{{ route('owner.transfers.show', $t['id']) }}"
       style="display:flex;align-items:center;justify-content:space-between;
              padding:9px 20px;text-decoration:none;gap:10px;
              border-top:1px solid var(--border);
              transition:background var(--tr)"
       onmouseover="this.style.background='var(--surface2)'"
       onmouseout="this.style.background='transparent'">

        <div style="min-width:0;flex:1">
            <div style="font-size:12px;font-weight:600;color:var(--text);
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                {{ $t['from'] }}
                <span style="color:var(--text-dim);font-weight:400"> → </span>
                {{ $t['to'] }}
            </div>
            <div style="font-size:11px;color:var(--text-dim);
                        font-family:var(--mono);margin-top:1px">
                {{ $t['age'] }}
            </div>
        </div>

        <span style="font-size:10px;font-weight:700;padding:2px 8px;
                     border-radius:20px;white-space:nowrap;flex-shrink:0;
                     background:{{ $sc['bg'] }};color:{{ $sc['c'] }}">
            {{ ucfirst(str_replace('_', ' ', $t['status'])) }}
        </span>

    </a>
    @endforeach

</div>
@endif
```

---

## STEP 6 — Rebuild and clear

```bash
npm run build
php artisan view:clear && php artisan cache:clear
```

---

## Do NOT touch

- Any other dashboard sections
- `app/Livewire/Dashboard/ActivityFeed.php`

---

## Verification

1. Open the owner dashboard — Operations & Activity section
2. Transfer Pipeline card shows: 4 status rows, then a "Recent Active"
   mini list of up to 4 transfers (route + age + status badge),
   then the ACTIVE TRANSFERS footer
3. Activity Feed card shows its natural scrollable list
4. Both cards start at the same top edge, each as tall as their content
5. No empty void in the Transfer Pipeline card
6. On mobile (≤640px) both cards stack full width at natural height
7. Each transfer row in the mini list is clickable and links to the
   correct transfer detail page
