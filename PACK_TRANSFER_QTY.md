# Pack Transfer — Quantity Panel After Scan
## Claude Code Instructions

> Drop this file in your project root, then tell Claude Code:
> "Read PACK_TRANSFER_QTY.md and follow every step in order."

---

## Context

**PHP component (active):** `app/Livewire/WarehouseManager/Transfers/PackTransfer.php`
**Blade view:** `resources/views/livewire/warehouse-manager/transfers/pack-transfer.blade.php`

Read both files in full before writing a single line of code.

---

## What We Are Building

Currently, scanning a box code immediately assigns exactly 1 box.

New behaviour:
- Scan a box code → system identifies the **product** and opens a quantity panel
- The panel shows: product name, how many boxes already assigned, how many still needed
- User types a number (e.g. 3) and clicks "Add Boxes" → system auto-selects that many FIFO boxes from the warehouse for that product and adds them all
- Quantity is **progressive**: user can scan again later and add more on top
- The number entered can never exceed `boxes_requested - boxes_already_assigned` for that product
- After confirming, the panel closes and the scan input is cleared and focused so the user can scan the next product immediately
- "Continue Scanning" button dismisses the panel without adding anything

---

## STEP 1 — Add new properties to PackTransfer.php

Inside the class, after the existing public properties and before `mount()`, add:

```php
public bool    $showQuantityPanel      = false;
public ?string $pendingBoxCode         = null;
public ?int    $pendingProductId       = null;
public string  $pendingProductName     = '';
public int     $pendingQty             = 1;
public int     $pendingMaxQty          = 0;
public int     $pendingAlreadyAssigned = 0;
```

---

## STEP 2 — Replace scanBox() in PackTransfer.php

Find the existing `scanBox()` method and replace it entirely:

```php
public function scanBox(): void
{
    $boxCode = trim($this->scanInput);

    if (empty($boxCode)) {
        $this->addError('scanInput', 'Please enter a box code.');
        return;
    }

    // Find the scanned box in this warehouse
    $box = Box::where('box_code', $boxCode)
        ->where('location_type', 'warehouse')
        ->where('location_id', $this->transfer->from_warehouse_id)
        ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
        ->first();

    if (!$box) {
        $this->addError('scanInput', "Box '{$boxCode}' not found or not available in this warehouse.");
        $this->scanInput = '';
        return;
    }

    // Find the matching transfer item
    $transferItemId = null;
    foreach ($this->items as $itemId => $item) {
        if ($item['product_id'] == $box->product_id) {
            $transferItemId = $itemId;
            break;
        }
    }

    if (!$transferItemId) {
        $this->addError('scanInput', "Box '{$boxCode}' contains {$box->product->name}, which is not in this transfer.");
        $this->scanInput = '';
        return;
    }

    $item            = $this->items[$transferItemId];
    $ipb             = max(1, (int) $item['items_per_box']);
    $boxesRequested  = (int) round($item['quantity_requested'] / $ipb);
    $boxesAssigned   = (int) $item['boxes_assigned'];
    $remaining       = $boxesRequested - $boxesAssigned;

    if ($remaining <= 0) {
        $this->addError('scanInput', "All {$boxesRequested} requested boxes for {$item['product_name']} are already assigned.");
        $this->scanInput = '';
        return;
    }

    // Open quantity panel
    $this->pendingBoxCode          = $boxCode;
    $this->pendingProductId        = $box->product_id;
    $this->pendingProductName      = $item['product_name'];
    $this->pendingAlreadyAssigned  = $boxesAssigned;
    $this->pendingMaxQty           = $remaining;
    $this->pendingQty              = 1;
    $this->showQuantityPanel       = true;
    $this->scanInput               = '';
    $this->resetErrorBag();
}
```

---

## STEP 3 — Add two new methods to PackTransfer.php

Add these two methods immediately after `scanBox()`:

```php
public function confirmScannedQuantity(): void
{
    $qty = (int) $this->pendingQty;

    if ($qty < 1) {
        $this->addError('pendingQty', 'Quantity must be at least 1.');
        return;
    }

    if ($qty > $this->pendingMaxQty) {
        $this->addError('pendingQty', "Cannot exceed {$this->pendingMaxQty} box(es) remaining for this product.");
        return;
    }

    // Find the transfer item id
    $transferItemId = null;
    foreach ($this->items as $itemId => $item) {
        if ($item['product_id'] == $this->pendingProductId) {
            $transferItemId = $itemId;
            break;
        }
    }

    if (!$transferItemId) {
        $this->closeQuantityPanel();
        return;
    }

    // FIFO: get $qty available boxes for this product excluding already assigned
    $alreadyAssignedIds = array_keys($this->assignedBoxes);

    $boxes = Box::where('product_id', $this->pendingProductId)
        ->where('location_type', 'warehouse')
        ->where('location_id', $this->transfer->from_warehouse_id)
        ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
        ->whereNotIn('id', $alreadyAssignedIds)
        ->orderByRaw("CASE WHEN status = 'full' THEN 0 ELSE 1 END")
        ->orderBy('received_at', 'asc')
        ->limit($qty)
        ->get();

    if ($boxes->isEmpty()) {
        $this->addError('pendingQty', 'No available boxes found in warehouse for this product.');
        return;
    }

    $actualCount = $boxes->count();
    foreach ($boxes as $box) {
        $this->assignBox($box, $transferItemId);
    }

    $productName = $this->pendingProductName;
    $this->closeQuantityPanel();

    session()->flash('success', "{$actualCount} box(es) of '{$productName}' added to transfer.");
}

public function closeQuantityPanel(): void
{
    $this->showQuantityPanel      = false;
    $this->pendingBoxCode         = null;
    $this->pendingProductId       = null;
    $this->pendingProductName     = '';
    $this->pendingQty             = 1;
    $this->pendingMaxQty          = 0;
    $this->pendingAlreadyAssigned = 0;
    $this->resetErrorBag('pendingQty');
}
```

---

## STEP 4 — Update the blade view

Open `resources/views/livewire/warehouse-manager/transfers/pack-transfer.blade.php`.

Find the scan input section — the `<div>` that contains the input with
`wire:model="scanInput"` and the Scan button. Do not change anything inside it.

**Immediately after the closing tag of that scan input section**, insert this block:

```blade
@if($showQuantityPanel)
<div style="margin-top:16px;padding:18px 20px;background:var(--accent-dim);
            border:2px solid var(--accent);border-radius:14px">

    {{-- Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px">
        <div>
            <div style="font-size:14px;font-weight:800;color:var(--accent)">
                📦 {{ $pendingProductName }}
            </div>
            <div style="font-size:11px;color:var(--text-sub);margin-top:3px">
                {{ $pendingAlreadyAssigned }} already assigned
                &nbsp;·&nbsp;
                <span style="font-weight:700;color:var(--text)">
                    {{ $pendingMaxQty }} box{{ $pendingMaxQty === 1 ? '' : 'es' }} still needed
                </span>
            </div>
        </div>
        <button wire:click="closeQuantityPanel"
                style="background:none;border:none;font-size:22px;color:var(--text-dim);
                       cursor:pointer;line-height:1;padding:0 2px">×</button>
    </div>

    {{-- Quantity input --}}
    <div>
        <label style="display:block;font-size:11px;font-weight:700;color:var(--text-sub);
                      text-transform:uppercase;letter-spacing:.5px;margin-bottom:7px">
            How many boxes to add now?
        </label>
        <input wire:model.live="pendingQty"
               wire:keydown.enter="confirmScannedQuantity"
               type="number" min="1" max="{{ $pendingMaxQty }}"
               style="width:100%;padding:12px 14px;border:2px solid var(--accent);
                      border-radius:10px;font-size:24px;font-weight:800;text-align:center;
                      background:var(--surface);color:var(--text);font-family:var(--mono);
                      outline:none;box-sizing:border-box">
        @error('pendingQty')
            <div style="font-size:11px;color:var(--red);margin-top:5px">{{ $message }}</div>
        @enderror
        @php $afterAdd = max(0, $pendingMaxQty - (int) $pendingQty); @endphp
        <div style="font-size:11px;color:var(--text-dim);margin-top:6px;text-align:center">
            After adding: <strong>{{ $afterAdd }}</strong> box{{ $afterAdd === 1 ? '' : 'es' }} still needed
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:10px;margin-top:14px">
        <button wire:click="closeQuantityPanel"
                style="flex:1;padding:11px;border-radius:10px;
                       border:1.5px solid var(--border);background:var(--surface);
                       font-size:13px;font-weight:700;cursor:pointer;color:var(--text)">
            Continue Scanning
        </button>
        <button wire:click="confirmScannedQuantity"
                wire:loading.attr="disabled"
                wire:target="confirmScannedQuantity"
                style="flex:2;padding:11px;border-radius:10px;border:none;
                       background:var(--accent);color:#fff;
                       font-size:13px;font-weight:800;cursor:pointer;
                       box-shadow:0 4px 12px rgba(59,111,212,.3)">
            <span wire:loading.remove wire:target="confirmScannedQuantity">
                ✓ Add {{ $pendingQty }} Box{{ (int) $pendingQty === 1 ? '' : 'es' }}
            </span>
            <span wire:loading wire:target="confirmScannedQuantity">Adding…</span>
        </button>
    </div>
</div>
@endif
```

---

## STEP 5 — Clear caches

```bash
php artisan view:clear
php artisan cache:clear
```

---

## What NOT to touch

- Do not modify `assignBox()`, `removeBox()`, `addBoxToProduct()`, `ship()`, or any other existing methods
- Do not touch the ship modal, scanner session, or transporter select
- Do not run migrations
- Do not modify any other files

---

## Verification checklist

After changes are applied:

1. Open a pack transfer page for an approved transfer
2. Scan a valid box code → quantity panel should appear with product name and remaining count
3. Type `2` in the input → "After adding: X still needed" should update live
4. Click "Add 2 Boxes" → panel closes, 2 boxes appear in the assigned list, progress bar updates
5. Scan the same product again → panel re-opens with updated `already assigned` count
6. Try typing a number larger than remaining → error message should appear
7. Click "Continue Scanning" → panel closes, scan input is ready
