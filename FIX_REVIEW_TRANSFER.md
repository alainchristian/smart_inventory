# SmartInventory — Fix: Review Transfer Approve Button + Boxes Requested Value
## Claude Code Instructions

> Drop in project root, tell Claude Code:
> "Read FIX_REVIEW_TRANSFER.md and follow every step in order."

---

## Root Cause Analysis

**Bug 1 — Approve button does nothing:**
The blade uses `wire:model.live` on every boxes input. This fires a Livewire network
request on EVERY keystroke. The button has `wire:loading.attr="disabled"` — so any
in-flight `wire:model.live` request disables the Approve button. A user who types a
number and quickly clicks Approve hits a disabled button and nothing happens.
Fix: change `wire:model.live` → `wire:model` (deferred, only syncs on next action).

**Bug 2 — Boxes requested shows 0 / empty:**
`mount()` stores `boxes_requested` as a PHP float (e.g. `2.0` from division
`quantity_requested / items_per_box`). Livewire serializes public arrays to JSON —
a float `2.0` becomes the string `"2"` on re-hydration. The `approve()` validation
then checks `< 1` against the string `"2"` which works, but the input's
`wire:model` binding may not correctly pre-fill a float. Casting to `(int)` at
mount time fixes both the display and the serialization.

---

## Step 0 — Read files first (MANDATORY)

```bash
cat app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php
cat resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php
```

---

## Step 1 — Fix `ReviewTransfer.php` (TWO edits)

**Target:** `app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php`

### Edit 1 — Cast `boxes_requested` to int in `mount()`

Find this block inside the `foreach ($transfer->items as $item)` loop in `mount()`:

```php
$boxesRequested = $item->quantity_requested / $product->items_per_box;

$this->items[] = [
    'id' => $item->id,
    'product_id' => $item->product_id,
    'product_name' => $product->name,
    'items_per_box' => $product->items_per_box,
    'boxes_requested' => $boxesRequested,
    'quantity_requested' => $item->quantity_requested,
];
```

Replace with:

```php
$boxesRequested = (int) round($item->quantity_requested / max(1, $product->items_per_box));

$this->items[] = [
    'id' => $item->id,
    'product_id' => $item->product_id,
    'product_name' => $product->name,
    'items_per_box' => (int) $product->items_per_box,
    'boxes_requested' => $boxesRequested,
    'quantity_requested' => (int) $item->quantity_requested,
];
```

**Why:** `(int) round(...)` ensures Livewire serializes a clean integer, not a float.
`max(1, ...)` prevents division by zero if `items_per_box` is somehow 0.

### Edit 2 — Cast `boxes_requested` to int inside `approve()` before validation

Find the validation loop inside `approve()`:

```php
foreach ($this->items as $index => $item) {
    if (!isset($item['boxes_requested']) || $item['boxes_requested'] < 1) {
```

Replace with:

```php
foreach ($this->items as $index => $item) {
    $boxesVal = (int) ($item['boxes_requested'] ?? 0);
    if ($boxesVal < 1) {
```

Then in the SAME loop, find the stock check and the DB update — update them to use `$boxesVal`:

```php
// Old stock check:
if ($item['boxes_requested'] > $availableBoxes) {
// New:
if ($boxesVal > $availableBoxes) {
```

And in the DB update loop below, find:
```php
$newQuantity = $item['boxes_requested'] * $product->items_per_box;
```
Replace with:
```php
$newQuantity = (int) ($item['boxes_requested'] ?? 0) * (int) $product->items_per_box;
```

---

## Step 2 — Fix the Blade (ONE edit: `wire:model.live` → `wire:model`)

**Target:** `resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php`

Find every occurrence of:
```
wire:model.live="items.
```

Replace ALL of them with:
```
wire:model="items.
```

There should be exactly **one** such occurrence on the boxes input field.

> **Why this fixes the approve button:** `wire:model` (deferred) only syncs data
> to the server when the next Livewire action fires (i.e., when the user clicks
> Approve). This means `wire:loading` is never active while the user is just
> typing, so the Approve button is never incorrectly disabled.

---

## Step 3 — Clear caches

```bash
php artisan view:clear
php artisan cache:clear
```

---

## Step 4 — Verify

```bash
# Confirm the cast is in mount()
grep -n "int.*round\|boxes_requested" app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php

# Confirm wire:model.live is gone from the blade
grep -n "wire:model.live" resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php
# Should return NOTHING (empty output = fixed)

# Confirm wire:model is still present for the input
grep -n 'wire:model="items\.' resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php
```

---

## Step 5 — Smoke test checklist

- [ ] Open a PENDING transfer review page
- [ ] "Boxes Requested" input shows the correct pre-filled integer (e.g. `2`, not `0` or empty)
- [ ] Edit a boxes value (e.g. change 2 → 3) — Approve button stays clickable while typing
- [ ] Click **Approve Transfer** — should redirect to transfers index with success flash
- [ ] Click **Reject Request** — modal opens, fill reason, click Reject — redirects with success flash
- [ ] If warehouse has insufficient stock, validation error appears on the specific row
