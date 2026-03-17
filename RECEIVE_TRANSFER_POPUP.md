# Receive Transfer — Quantity Popup (Shop Manager)
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read RECEIVE_TRANSFER_POPUP.md and follow every step in order."

---

## Target files

```
app/Livewire/Shop/Transfers/ReceiveTransfer.php
resources/views/livewire/shop/transfers/receive-transfer.blade.php
```

Read both files in full before writing any code.

---

## What we are changing

Currently `scanBox()` in the shop ReceiveTransfer component auto-confirms
1 box immediately when a product barcode is scanned. We want the same
Enter-to-confirm popup that was built for the pack transfer:

- Scan a box code or product barcode → popup appears
- Shows product name, how many boxes already scanned, how many still expected
- Single number input pre-filled with `1`, auto-focused and auto-selected
- No buttons — press **Enter** to confirm, **Escape** to cancel
- Changing the number live-updates "after adding: X still needed"
- After confirming, popup closes and scan input is re-focused automatically
- Number can never exceed remaining un-scanned boxes for that product

---

## STEP 1 — Add new properties to ReceiveTransfer.php

After the existing public properties and before `mount()`, add:

```php
public bool    $showQuantityPanel      = false;
public ?string $pendingBoxCode         = null;
public ?int    $pendingProductId       = null;
public string  $pendingProductName     = '';
public int     $pendingQty             = 1;
public int     $pendingMaxQty          = 0;
public int     $pendingAlreadyScanned  = 0;
```

---

## STEP 2 — Replace scanBox() in ReceiveTransfer.php

Find the existing `scanBox()` method and replace it entirely:

```php
public function scanBox(): void
{
    $input = trim($this->scanInput);

    if (empty($input)) {
        return;
    }

    $alreadyScannedBoxIds = array_keys($this->scannedBoxes);

    // --- Try as individual box_code first ---
    $transferBox = $this->transfer->boxes()
        ->whereHas('box', fn ($q) => $q->where('box_code', $input))
        ->where('is_received', false)
        ->whereNotIn('box_id', $alreadyScannedBoxIds)
        ->with('box.product')
        ->first();

    if ($transferBox) {
        // Single specific box scanned — open popup defaulting to 1
        $product   = $transferBox->box->product;
        $productId = $product->id;

        $alreadyScanned = collect($this->scannedBoxes)
            ->where('product_id', $productId)
            ->count();

        $remaining = $this->transfer->boxes()
            ->whereHas('box', fn ($q) => $q->where('product_id', $productId))
            ->where('is_received', false)
            ->whereNotIn('box_id', $alreadyScannedBoxIds)
            ->count();

        $this->pendingBoxCode        = $input;
        $this->pendingProductId      = $productId;
        $this->pendingProductName    = $product->name;
        $this->pendingAlreadyScanned = $alreadyScanned;
        $this->pendingMaxQty         = $remaining;
        $this->pendingQty            = 1;
        $this->showQuantityPanel     = true;
        $this->scanInput             = '';
        $this->resetErrorBag();
        return;
    }

    // --- Try as product barcode ---
    $product = \App\Models\Product::where('barcode', $input)->first();

    if ($product) {
        $available = $this->transfer->boxes()
            ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
            ->where('is_received', false)
            ->whereNotIn('box_id', $alreadyScannedBoxIds)
            ->count();

        if ($available === 0) {
            session()->flash('scan_error', "All boxes of {$product->name} already scanned.");
            $this->scanInput = '';
            return;
        }

        $alreadyScanned = collect($this->scannedBoxes)
            ->where('product_id', $product->id)
            ->count();

        $this->pendingBoxCode        = $input;
        $this->pendingProductId      = $product->id;
        $this->pendingProductName    = $product->name;
        $this->pendingAlreadyScanned = $alreadyScanned;
        $this->pendingMaxQty         = $available;
        $this->pendingQty            = 1;
        $this->showQuantityPanel     = true;
        $this->scanInput             = '';
        $this->resetErrorBag();
        return;
    }

    // Nothing matched
    session()->flash('scan_error', "Not found: {$input}");
    $this->dispatch('scan-error', message: "Not found: {$input}");
    $this->scanInput = '';
}
```

---

## STEP 3 — Add three new methods to ReceiveTransfer.php

Add these after `scanBox()`:

```php
public function updatedPendingQty(): void
{
    $qty = (int) $this->pendingQty;
    if ($qty < 1) {
        $this->pendingQty = 1;
    } elseif ($qty > $this->pendingMaxQty) {
        $this->pendingQty = $this->pendingMaxQty;
    }
}

public function confirmScannedQuantity(): void
{
    $qty = (int) $this->pendingQty;

    if ($qty < 1) {
        $this->addError('pendingQty', 'Quantity must be at least 1.');
        return;
    }

    if ($qty > $this->pendingMaxQty) {
        $this->addError('pendingQty', "Cannot exceed {$this->pendingMaxQty} box(es) for this product.");
        return;
    }

    $alreadyScannedBoxIds = array_keys($this->scannedBoxes);

    // Find $qty un-received TransferBox rows for this product
    $transferBoxes = $this->transfer->boxes()
        ->whereHas('box', fn ($q) => $q->where('product_id', $this->pendingProductId))
        ->where('is_received', false)
        ->whereNotIn('box_id', $alreadyScannedBoxIds)
        ->with('box.product')
        ->limit($qty)
        ->get();

    if ($transferBoxes->isEmpty()) {
        $this->addError('pendingQty', 'No boxes found to confirm.');
        return;
    }

    foreach ($transferBoxes as $tb) {
        $this->confirmSingleBox($tb);
    }

    $productName = $this->pendingProductName;
    $count       = $transferBoxes->count();
    $this->closeQuantityPanel();

    session()->flash('scan_success', "{$count} box(es) of '{$productName}' confirmed.");
    $this->dispatch('quantity-confirmed');
}

public function closeQuantityPanel(): void
{
    $this->showQuantityPanel     = false;
    $this->pendingBoxCode        = null;
    $this->pendingProductId      = null;
    $this->pendingProductName    = '';
    $this->pendingQty            = 1;
    $this->pendingMaxQty         = 0;
    $this->pendingAlreadyScanned = 0;
    $this->resetErrorBag('pendingQty');
}
```

Also update `confirmSingleBox()` to store `product_id` in the scanned box
array so the count-per-product logic above works. Find `confirmSingleBox()`
and add `'product_id' => $transferBox->box->product_id,` to the array:

```php
private function confirmSingleBox(TransferBox $transferBox): void
{
    if (isset($this->scannedBoxes[$transferBox->box_id])) {
        return;
    }

    $this->scannedBoxes[$transferBox->box_id] = [
        'box_id'      => $transferBox->box_id,
        'box_code'    => $transferBox->box->box_code,
        'product_id'  => $transferBox->box->product_id,
        'product_name'=> $transferBox->box->product->name,
        'items'       => $transferBox->box->items_remaining,
        'is_damaged'  => false,
        'damage_notes'=> null,
    ];

    session()->flash('scan_success', "Box confirmed: {$transferBox->box->product->name}");
    $this->dispatch('scan-success', message: "Confirmed: {$transferBox->box->product->name}");
}
```

---

## STEP 4 — Update the blade view

Open `resources/views/livewire/shop/transfers/receive-transfer.blade.php`.

Find the scan input section — the element containing
`wire:model="scanInput"` and `wire:keydown.enter="scanBox"`.
Do not change anything inside it.

**Immediately after the closing tag of that scan input section**, insert:

```blade
{{-- ── Quantity Popup ─────────────────────────────────── --}}
@if($showQuantityPanel)
<div
    x-data
    x-on:keydown.escape.window="$wire.closeQuantityPanel()"
    style="position:fixed;inset:0;z-index:900;display:flex;align-items:center;
           justify-content:center;background:rgba(15,18,36,.55);backdrop-filter:blur(3px)"
>
    <div style="background:var(--surface);border-radius:18px;width:340px;max-width:92vw;
                padding:28px 28px 24px;box-shadow:0 24px 64px rgba(0,0,0,.26);
                border:1px solid var(--border)"
         x-on:click.stop>

        {{-- Product name --}}
        <div style="font-size:16px;font-weight:800;color:var(--text);
                    margin-bottom:4px;text-align:center">
            📦 {{ $pendingProductName }}
        </div>

        {{-- Progress subtitle --}}
        <div style="font-size:12px;color:var(--text-sub);text-align:center;margin-bottom:20px">
            {{ $pendingAlreadyScanned }} already scanned
            &nbsp;·&nbsp;
            <strong style="color:var(--text)">
                {{ $pendingMaxQty }} box{{ $pendingMaxQty === 1 ? '' : 'es' }} remaining
            </strong>
        </div>

        {{-- Number input --}}
        <input
            wire:model.live="pendingQty"
            wire:keydown.enter="confirmScannedQuantity"
            x-on:keydown.escape.stop="$wire.closeQuantityPanel()"
            type="number"
            min="1"
            max="{{ $pendingMaxQty }}"
            x-init="$nextTick(() => $el.select())"
            style="width:100%;padding:14px;border:2px solid var(--accent);
                   border-radius:12px;font-size:32px;font-weight:800;text-align:center;
                   background:var(--surface);color:var(--text);font-family:var(--mono);
                   outline:none;box-sizing:border-box;display:block"
        >

        @error('pendingQty')
            <div style="font-size:11px;color:var(--red);margin-top:6px;text-align:center">
                {{ $message }}
            </div>
        @enderror

        {{-- Live indicator --}}
        @php $afterAdd = max(0, $pendingMaxQty - (int) $pendingQty); @endphp
        <div style="font-size:11px;color:var(--text-dim);margin-top:10px;text-align:center">
            After confirming:
            <strong style="color:{{ $afterAdd === 0 ? 'var(--green)' : 'var(--text)' }}">
                {{ $afterAdd }} box{{ $afterAdd === 1 ? '' : 'es' }} still needed
            </strong>
        </div>

        {{-- Hint --}}
        <div style="font-size:10px;color:var(--text-dim);text-align:center;margin-top:14px;
                    padding-top:14px;border-top:1px solid var(--border)">
            Press <kbd style="background:var(--surface2);border:1px solid var(--border);
                              border-radius:4px;padding:1px 5px;font-size:10px">Enter</kbd>
            to confirm &nbsp;·&nbsp;
            <kbd style="background:var(--surface2);border:1px solid var(--border);
                        border-radius:4px;padding:1px 5px;font-size:10px">Esc</kbd>
            to cancel
        </div>
    </div>
</div>
@endif
```

---

## STEP 5 — Add JS re-focus listener to the blade

Find the closing `</div>` of the root Livewire component div (very bottom
of the blade). Just before it, add:

```blade
@push('scripts')
<script>
window.addEventListener('quantity-confirmed', () => {
    setTimeout(() => {
        const scanInput = document.querySelector('[wire\\:model="scanInput"]');
        if (scanInput) {
            scanInput.focus();
            scanInput.select();
        }
    }, 80);
});
</script>
@endpush
```

If `@push('scripts')` already exists in the file, add the listener
inside the existing script block instead of creating a new one.

---

## STEP 6 — Clear caches

```bash
php artisan view:clear
php artisan cache:clear
```

---

## Do NOT touch

- `completeReceipt()`, `markAsDamaged()`, `updateDamageNotes()`,
  `removeScannedBox()` or any other existing methods
- The scanner session / QR code section
- The scanned boxes list with damage marking
- Any migration files
- `app/Livewire/Inventory/Transfers/ReceiveTransfer.php` (different component)

---

## Verification checklist

1. Load receive transfer page for an in-transit or delivered transfer
2. Scan a product barcode → popup appears with product name and remaining count
3. Press Enter → 1 box confirmed, popup closes, scan input re-focused
4. Scan same product again → popup shows updated "already scanned" count
5. Type `3`, press Enter → 3 boxes confirmed at once
6. Try typing more than remaining → value is clamped automatically
7. Press Escape → popup closes, nothing added, scan input focused
8. All boxes scanned → scan that product again → error flash, no popup
