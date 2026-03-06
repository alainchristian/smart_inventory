# SmartInventory — Fix: Review Transfer Page Visible Bugs
## Claude Code Instructions

> Drop in project root, tell Claude Code:
> "Read FIX_REVIEW_TRANSFER_BUGS.md and follow every step in order."

---

## What Is Broken (Confirmed from Screenshot)

| Bug | What you see | Root cause |
|-----|-------------|------------|
| 1 | **Boxes Requested = 0** (should be 7) | `mount()` computes `42 / 6 = 7.0` (float), Livewire serializes it to JSON, re-hydrates as `"7"` string or drops to 0 when bound to an `<input type="number">` |
| 2 | **Total Items = 42 even though Boxes = 0** | Blade computes total items from `$item['quantity_requested']` (raw DB integer = 42) instead of `$requestedBoxes * $item['items_per_box']`, so the two columns use different data sources |
| 3 | **Stock bar fill is wrong** | Progress bar `width` is computed from `$requestedBoxes` which is 0, giving 0% — inconsistent with Total Items showing 42 |

The underlying cause of all three: `boxes_requested` is stored as a PHP **float** in the component's public array. Livewire serialises public properties to JSON between requests; a float `7.0` in PHP becomes `7` in JSON but then binds to the input as a **string** `"7"`, and the `@php` block in the blade evaluates it differently depending on whether it's reading from wire:model (string "7" or 0) or from `$item['quantity_requested']` (int 42).

---

## Step 0 — Read both files first (MANDATORY)

```bash
cat app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php
cat resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php
```

---

## Step 1 — Fix `ReviewTransfer.php`: cast everything to int in mount()

**Target:** `app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php`

Find the `foreach ($transfer->items as $item)` loop inside `mount()`:

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
$itemsPerBox   = max(1, (int) $product->items_per_box);
$boxesRequested = (int) round($item->quantity_requested / $itemsPerBox);

$this->items[] = [
    'id'                => $item->id,
    'product_id'        => $item->product_id,
    'product_name'      => $product->name,
    'items_per_box'     => $itemsPerBox,
    'boxes_requested'   => $boxesRequested,          // always int, never float
    'quantity_requested'=> (int) $item->quantity_requested,
];
```

**Why `max(1, ...)`**: prevents division by zero if a product has `items_per_box = 0`.
**Why `(int) round(...)`**: `round()` returns float, the `(int)` cast makes it a clean integer that Livewire serializes as `7` not `7.0`.

### Also fix `approve()` to cast before comparisons

Inside `approve()`, find the validation loop and replace:

```php
if (!isset($item['boxes_requested']) || $item['boxes_requested'] < 1) {
```

With:

```php
$boxesVal = (int) ($item['boxes_requested'] ?? 0);
if ($boxesVal < 1) {
```

And the stock check — replace:

```php
if ($item['boxes_requested'] > $availableBoxes) {
```

With:

```php
if ($boxesVal > $availableBoxes) {
```

And the DB update — replace:

```php
$newQuantity = $item['boxes_requested'] * $product->items_per_box;
```

With:

```php
$newQuantity = (int) ($item['boxes_requested'] ?? 0) * (int) $product->items_per_box;
```

---

## Step 2 — Fix the Blade: make Total Items consistent with Boxes Requested

**Target:** `resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php`

### 2a — Fix the `@php` block inside the `@foreach`

Find the `@php` block inside the `@foreach($items as $index => $item)` loop. It will look something like:

```php
@php
    $stock          = $stockLevels[$item['product_id']] ?? null;
    $availableBoxes = $stock ? $stock['total_boxes'] : 0;
    $requestedBoxes = $item['boxes_requested'];
    $exceedsStock   = $requestedBoxes > $availableBoxes;
    $totalItems     = $requestedBoxes * $item['items_per_box'];
@endphp
```

Replace with:

```php
@php
    $stock          = $stockLevels[$item['product_id']] ?? null;
    $availableBoxes = $stock ? (int) $stock['total_boxes'] : 0;
    $requestedBoxes = (int) ($item['boxes_requested'] ?? 0);   // explicit int — single source of truth
    $exceedsStock   = $requestedBoxes > $availableBoxes && $requestedBoxes > 0;
    $totalItems     = $requestedBoxes * (int) $item['items_per_box'];  // always derived from boxes, never from quantity_requested
    $stockPct       = ($availableBoxes > 0 && $requestedBoxes > 0)
                        ? min(100, (int) round($requestedBoxes / $availableBoxes * 100))
                        : 0;
@endphp
```

**Key change**: `$totalItems` now always equals `$requestedBoxes × items_per_box`. It never reads `$item['quantity_requested']` directly. Once `boxes_requested` is fixed to be 7 (from Step 1), `Total Items` will correctly show `7 × 6 = 42`.

### 2b — Fix `wire:model.live` → `wire:model`

Find:
```
wire:model.live="items.
```

Replace ALL occurrences with:
```
wire:model="items.
```

`wire:model.live` fires a Livewire roundtrip on every keystroke, which triggers `wire:loading` and silently **disables the Approve button** while the user is typing. Changing to deferred `wire:model` means the sync only happens when the user clicks Approve — which is exactly what we want.

### 2c — Verify Total Items display is NOT using `$item['quantity_requested']`

Search for any place in the blade that outputs quantity_requested directly:

```bash
grep -n "quantity_requested" resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php
```

If you find any `{{ $item['quantity_requested'] }}` being rendered as "Total Items" — replace it to use `$totalItems` (which is now computed as `$requestedBoxes × items_per_box`).

---

## Step 3 — Clear caches

```bash
php artisan view:clear
php artisan cache:clear
```

---

## Step 4 — Verify

```bash
# 1. Confirm float is gone — should show (int) round
grep -A3 "boxesRequested" app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php

# 2. Confirm wire:model.live is gone
grep -n "wire:model.live" resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php
# Must return NOTHING

# 3. Confirm totalItems uses requestedBoxes, not quantity_requested
grep -n "totalItems" resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php
```

---

## Step 5 — Expected result after fix

Open `/warehouse/transfers/1` (the TR-2026-03-00001 transfer):

| Field | Before fix | After fix |
|-------|-----------|-----------|
| Boxes Requested input | 0 | **7** |
| Total Items | 42 (wrong source) | **42** (correct: 7 × 6) |
| Stock bar | Wrong fill % | **Correct: 7/12 = 58%** |
| Approve button | Silent / does nothing | **Works, fires approve()** |
| Stock warning | Missing (0 < 12 = no warning) | Correct (7 < 12 = no warning, green) |

If the shop had requested MORE than available (e.g. 15 boxes when only 12 exist):
- Boxes input shows 15
- Stock bar fills red and overflows
- Warning chip appears: "Requested 15 boxes but only 12 available"
- Approve button shows validation error
