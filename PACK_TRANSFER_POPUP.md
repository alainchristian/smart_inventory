# Pack Transfer — Quantity Popup Redesign
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read PACK_TRANSFER_POPUP.md and follow every step in order."

---

## Target files

Run these first to confirm which component and blade are active:

```bash
grep -n "confirmScannedQuantity\|closeQuantityPanel\|showQuantityPanel" \
  app/Livewire/Inventory/Transfers/PackTransfer.php
```

Then read both files in full before touching anything:

```bash
cat app/Livewire/Inventory/Transfers/PackTransfer.php
```

Get the blade path from the `render()` method return value, then:

```bash
cat <blade-path-from-render>
```

---

## What we are changing

The current quantity panel is an inline block below the scan input.
Replace it with a **centered overlay popup** with these rules:

- Popup appears immediately after a valid scan
- Shows product name and remaining boxes needed
- Single number input, pre-filled with `1`, auto-focused
- **No buttons at all**
- Pressing **Enter** confirms the current number, closes the popup,
  assigns the FIFO boxes, and if boxes are still needed for any product
  the scan input is re-focused automatically
- Changing the number live-updates an "after adding: X still needed" line
- Pressing **Escape** closes the popup without adding anything

---

## STEP 1 — PHP: add updatedPendingQty validation

In `app/Livewire/Inventory/Transfers/PackTransfer.php`, add this method
anywhere after the existing properties and before `mount()` or after
`closeQuantityPanel()`:

```php
public function updatedPendingQty(): void
{
    // Clamp to valid range in real time
    $qty = (int) $this->pendingQty;
    if ($qty < 1) {
        $this->pendingQty = 1;
    } elseif ($qty > $this->pendingMaxQty) {
        $this->pendingQty = $this->pendingMaxQty;
    }
}
```

---

## STEP 2 — PHP: update confirmScannedQuantity() to dispatch focus event

Find the existing `confirmScannedQuantity()` method. At the very end of
the method, just before the closing brace `}`, find the line:

```php
session()->flash('success', "{$actualCount} box(es) of '{$productName}' added to transfer.");
```

Replace that one line with:

```php
session()->flash('success', "{$actualCount} box(es) of '{$productName}' added.");
$this->dispatch('quantity-confirmed');
```

---

## STEP 3 — Blade: replace the entire @if($showQuantityPanel) block

Find the entire block starting with `@if($showQuantityPanel)` and ending
with the matching `@endif`. Delete it completely and replace with this:

```blade
{{-- ── Quantity Popup ─────────────────────────────────────────── --}}
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

        {{-- Still needed subtitle --}}
        <div style="font-size:12px;color:var(--text-sub);text-align:center;margin-bottom:20px">
            {{ $pendingAlreadyAssigned }} assigned
            &nbsp;·&nbsp;
            <strong style="color:var(--text)">
                {{ $pendingMaxQty }} box{{ $pendingMaxQty === 1 ? '' : 'es' }} needed
            </strong>
        </div>

        {{-- Number input — auto-focused, Enter confirms --}}
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

        {{-- Live "after adding" indicator --}}
        @php $afterAdd = max(0, $pendingMaxQty - (int) $pendingQty); @endphp
        <div style="font-size:11px;color:var(--text-dim);margin-top:10px;text-align:center">
            After adding:
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

## STEP 4 — Blade: add JS listener for auto re-focus after confirmation

Find the `@push('scripts')` or `<script>` section at the bottom of the
blade file. If there is already a `<script>` block, add the listener
inside it. If there is no script block, add one before the final `</div>`
of the component.

Add this inside the script tag:

```javascript
window.addEventListener('quantity-confirmed', () => {
    // Re-focus the scan input after popup closes
    setTimeout(() => {
        const scanInput = document.querySelector('[wire\\:model="scanInput"], [wire\\:model\\.live="scanInput"]');
        if (scanInput) {
            scanInput.focus();
            scanInput.select();
        }
    }, 80);
});
```

---

## STEP 5 — Clear caches

```bash
php artisan view:clear
php artisan cache:clear
```

---

## Do NOT touch

- `assignBox()`, `removeBox()`, `ship()`, `openShipModal()` or any other methods
- The scanner session / QR code section
- The transporter select
- Any migration files

---

## Verification

1. Load the pack transfer page for an approved transfer
2. Scan a valid box code → centered overlay popup appears, number input shows `1`, is auto-selected
3. Press Enter immediately → 1 box added, popup closes, scan input re-focused
4. Scan again, type `3`, press Enter → 3 boxes added at once
5. Scan when all boxes assigned → error message shown, no popup
6. Press Escape while popup is open → popup closes, nothing added, scan input re-focused
