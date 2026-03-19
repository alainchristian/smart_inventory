# Operations & Activity — Equal Height Fix
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read OPS_EQUAL_HEIGHT.md and follow every step in order."

---

## The fix

Both cards get the same fixed height (500px). The Activity Feed scrolls
inside. The Transfer Pipeline fills naturally — no flex, no stretch.

---

## STEP 1 — Add card height CSS

**File:** `resources/css/app.css`

Find `.oa-grid` and ensure it reads:
```css
.oa-grid {
    display: grid;
    grid-template-columns: 2fr 3fr;
    gap: 20px;
    margin-bottom: 26px;
    align-items: start;
}
```

Then add immediately after:
```css
.oa-grid > * {
    height: 500px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
@media (max-width: 640px) {
    .oa-grid > * { height: auto; overflow: visible; }
}
```

Run `npm run build`.

---

## STEP 2 — Make Activity Feed scroll inside its card

**File:** `resources/views/livewire/dashboard/activity-feed.blade.php`

Find the inner scrollable div (the one with `overflow-y:auto`
and a `max-height`). Replace its style with:
```
style="flex:1;overflow-y:auto;min-height:0"
```

Remove any `max-height` value — the card height now controls it.

---

## STEP 3 — Make Transfer Pipeline fill its card

**File:** `resources/views/livewire/dashboard/transfer-status.blade.php`

On the outermost wrapper div add `display:flex;flex-direction:column;height:100%`.

On the `.ts-rows` div add `style="flex:1"` so the rows expand and
push the footer to the bottom.

---

## STEP 4 — Clear

```bash
php artisan view:clear && php artisan cache:clear
```

---

## Verification

Both cards are exactly 500px tall. Activity Feed scrolls inside.
Transfer Pipeline has its footer pinned to the bottom.
On mobile both cards return to natural height and stack.
