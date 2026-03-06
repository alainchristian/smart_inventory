# SmartInventory — Transfer Module Full Repair & Redesign
## Claude Code Instructions

> Drop in project root, tell Claude Code:
> "Read TRANSFER_MODULE_FIX.md and follow every step in order. Do not skip any step."

---

## What You Are Fixing (Read This First)

The transfer module has the following confirmed problems:

| # | Problem | Affects |
|---|---------|---------|
| 1 | `approve()` button silent — `wire:model.live` disables button during typing | Warehouse Manager |
| 2 | Boxes Requested input pre-fills as 0/empty — float stored as PHP array value | Warehouse Manager |
| 3 | Two competing `PackTransfer` PHP components — unclear which the route uses | Warehouse Manager |
| 4 | `TransferPolicy::receive()` requires `DELIVERED` but component allows `IN_TRANSIT` | Shop Manager |
| 5 | `TransferStatus::color()` returns bare word (`'yellow'`) not a CSS utility class | All pages |
| 6 | Pack Transfer page uses old Tailwind classes, not design system CSS vars | Warehouse Manager |
| 7 | Receive Transfer page uses old Tailwind classes, not design system CSS vars | Shop Manager |

The fix goes in this order: **logic bugs first → policy fix → design redesign**.

---

## Ground Rules

- **DO NOT change any PHP method signatures** — only fix logic bugs inside existing methods
- **DO NOT rename any Livewire properties** — wire: bindings depend on exact names
- Design system vars: `--accent --surface --surface2 --surface3 --border --border-hi --text --text-sub --text-dim --green --red --amber --violet --success --pink --font --mono --r --rsm --tr`
- After ALL steps: run `php artisan view:clear && php artisan cache:clear`

---

## STEP 0 — Discovery (MANDATORY — read everything before touching anything)

```bash
# 1. See which livewire component the warehouse pack page actually loads
cat resources/views/warehouse/transfers/pack.blade.php

# 2. Read BOTH PackTransfer PHP components in full
cat app/Livewire/WarehouseManager/Transfers/PackTransfer.php
cat app/Livewire/Inventory/Transfers/PackTransfer.php

# 3. Read the ReviewTransfer component
cat app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php

# 4. Read the ReceiveTransfer component (shop)
cat app/Livewire/Shop/Transfers/ReceiveTransfer.php

# 5. Check the policy
cat app/Policies/TransferPolicy.php

# 6. Check TransferStatus enum
cat app/Enums/TransferStatus.php

# 7. Check what Livewire components are registered
cat config/livewire.php 2>/dev/null || grep -r "PackTransfer\|ReviewTransfer\|ReceiveTransfer" bootstrap/

# 8. Check git log for last known working commits on transfer files
git log --oneline -20
git log --oneline -- app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php | head -5
git log --oneline -- app/Livewire/WarehouseManager/Transfers/PackTransfer.php | head -5
```

**Record your findings before proceeding.**

---

## STEP 1 — Fix: ReviewTransfer approve button + boxes_requested value

**Target:** `app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php`

### 1a — Fix `mount()`: cast boxes_requested to integer

Find inside the `foreach ($transfer->items as $item)` loop:

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
    'id'               => $item->id,
    'product_id'       => $item->product_id,
    'product_name'     => $product->name,
    'items_per_box'    => (int) $product->items_per_box,
    'boxes_requested'  => $boxesRequested,
    'quantity_requested' => (int) $item->quantity_requested,
];
```

### 1b — Fix `approve()`: use explicit int cast before all comparisons

Find the validation loop:
```php
if (!isset($item['boxes_requested']) || $item['boxes_requested'] < 1) {
```
Replace with:
```php
$boxesVal = (int) ($item['boxes_requested'] ?? 0);
if ($boxesVal < 1) {
```

In the SAME loop, find the stock comparison:
```php
if ($item['boxes_requested'] > $availableBoxes) {
```
Replace with:
```php
if ($boxesVal > $availableBoxes) {
```

In the DB update loop below, find:
```php
$newQuantity = $item['boxes_requested'] * $product->items_per_box;
```
Replace with:
```php
$newQuantity = (int) ($item['boxes_requested'] ?? 0) * (int) $product->items_per_box;
```

**Target:** `resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php`

### 1c — Fix `wire:model.live` → `wire:model`

Find every occurrence of:
```
wire:model.live="items.
```
Replace ALL with:
```
wire:model="items.
```

Verify after:
```bash
grep -n "wire:model.live" resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php
# Must return NOTHING
```

---

## STEP 2 — Fix: TransferPolicy receive() status check

**Target:** `app/Policies/TransferPolicy.php`

Find the `receive()` method:
```php
public function receive(User $user, Transfer $transfer): bool
{
    // Must be delivered
    if ($transfer->status !== TransferStatus::DELIVERED) {
        return false;
    }
```

Replace the status check with:
```php
public function receive(User $user, Transfer $transfer): bool
{
    // Must be in_transit or delivered (shop can receive either way)
    if (!in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED])) {
        return false;
    }
```

This matches what `App\Livewire\Shop\Transfers\ReceiveTransfer::mount()` already does.

---

## STEP 3 — Fix: Consolidate duplicate PackTransfer components

From Step 0 discovery, you found two PHP PackTransfer classes:
- `App\Livewire\WarehouseManager\Transfers\PackTransfer` (manual box assignment via UI buttons)
- `App\Livewire\Inventory\Transfers\PackTransfer` (barcode/product scan based, newer)

And the pack page blade at `resources/views/warehouse/transfers/pack.blade.php` uses one of them.

**Decision rule:**
- If `resources/views/warehouse/transfers/pack.blade.php` uses `livewire:inventory.transfers.pack-transfer` → the `WarehouseManager` version is dead code
- If it uses `livewire:warehouse-manager.transfers.pack-transfer` → the `Inventory` version is the newer unused one

**Action:**
1. Read `resources/views/warehouse/transfers/pack.blade.php` to determine which component is wired
2. The component that IS wired to the route is the **canonical one** — do not touch its PHP
3. The component that is NOT wired is orphaned — add a comment at the top of its PHP file:
   ```php
   // ORPHANED: This component is not currently wired to any route.
   // The active PackTransfer is App\Livewire\[other namespace]\PackTransfer
   // DO NOT delete until confirmed unused.
   ```
4. From this point forward, all blade redesign work (Step 5) targets only the ACTIVE component's blade view

---

## STEP 4 — Fix: TransferStatus color() method

**Target:** `app/Enums/TransferStatus.php`

The current `color()` method returns bare color names like `'yellow'`, `'green'` which are inconsistent with the design system. Update it to return Tailwind badge classes AND a method for CSS-var-based colors:

Find the existing `color()` method and replace entirely:

```php
public function color(): string
{
    return match($this) {
        self::PENDING    => 'bg-amber-100 text-amber-800 border border-amber-200',
        self::APPROVED   => 'bg-blue-100 text-blue-800 border border-blue-200',
        self::REJECTED   => 'bg-red-100 text-red-800 border border-red-200',
        self::IN_TRANSIT => 'bg-violet-100 text-violet-800 border border-violet-200',
        self::DELIVERED  => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
        self::RECEIVED   => 'bg-green-100 text-green-800 border border-green-200',
        self::CANCELLED  => 'bg-gray-100 text-gray-600 border border-gray-200',
    };
}

public function cssColor(): string
{
    return match($this) {
        self::PENDING    => 'var(--amber)',
        self::APPROVED   => 'var(--accent)',
        self::REJECTED   => 'var(--red)',
        self::IN_TRANSIT => 'var(--violet)',
        self::DELIVERED  => '#0ea5e9',
        self::RECEIVED   => 'var(--green)',
        self::CANCELLED  => 'var(--text-dim)',
    };
}
```

Then search all blade files for `$transfer->status->color()` and verify the surrounding markup still works with the Tailwind class string. The places that use it are:
- `resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php`
- Any transfers list blade

If a blade does `class="{{ $transfer->status->color() }}"` that's correct. If it does `class="bg-{{ $transfer->status->color() }}-100"` that must be changed to `class="{{ $transfer->status->color() }}"`.

```bash
grep -rn "status->color()" resources/views/
```

Fix any broken usage you find.

---

## STEP 5 — Redesign: Pack Transfer Page (Warehouse Manager)

**Target:** Only the blade view for the ACTIVE PackTransfer component (identified in Step 3).

### 5a — Read the active PHP component in full first

```bash
# Replace with the correct path from Step 3
cat app/Livewire/WarehouseManager/Transfers/PackTransfer.php
# OR
cat app/Livewire/Inventory/Transfers/PackTransfer.php
```

Note ALL public properties, method names, and what render() passes to the view.

### 5b — Rewrite the blade

Replace the entire blade file content with the following design-system-aligned version.
**Adjust wire: bindings to match the actual PHP properties/methods you found in 5a.**

```blade
@php use App\Enums\TransferStatus; @endphp

<style>
/* ── Pack Transfer — Design System ── */
.pt-wrap { display:flex;flex-direction:column;gap:20px;font-family:var(--font); }

.pt-card {
    background:var(--surface);border:1px solid var(--border);
    border-radius:12px;overflow:hidden;
}
.pt-card-head {
    padding:16px 22px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;gap:12px;
}
.pt-card-title { font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-sub); }
.pt-card-body  { padding:22px; }

/* Route strip */
.pt-route {
    display:flex;align-items:center;
    background:var(--surface2);border-radius:10px;
    padding:14px 18px;border:1px solid var(--border);gap:0;
}
.pt-route-node  { flex:1; }
.pt-route-label { font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim); }
.pt-route-name  { font-size:14px;font-weight:700;color:var(--text);margin-top:3px; }
.pt-route-arrow {
    width:34px;height:34px;border-radius:50%;
    background:var(--accent-dim);color:var(--accent);
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}

/* Scan bar */
.pt-scan-bar {
    display:flex;gap:10px;align-items:center;
    padding:18px;background:var(--surface2);border-radius:10px;
    border:1.5px dashed var(--border-hi);
}
.pt-scan-input {
    flex:1;padding:11px 14px;
    background:var(--surface);color:var(--text);
    border:1.5px solid var(--border-hi);border-radius:8px;
    font-size:15px;font-family:var(--mono);font-weight:600;outline:none;
    transition:border-color var(--tr),box-shadow var(--tr);
}
.pt-scan-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow); }
.pt-scan-btn {
    padding:11px 20px;background:var(--accent);color:#fff;
    border:none;border-radius:8px;font-size:14px;font-weight:700;
    font-family:var(--font);cursor:pointer;white-space:nowrap;
    transition:background var(--tr);
}
.pt-scan-btn:hover { background:#2d5dbf; }

/* Product summary rows */
.pt-product-row {
    border:1.5px solid var(--border);border-radius:10px;overflow:hidden;
}
.pt-product-head {
    display:flex;align-items:center;justify-content:space-between;
    padding:12px 16px;background:var(--surface2);border-bottom:1px solid var(--border);
}
.pt-product-name { font-size:14px;font-weight:700;color:var(--text); }
.pt-progress-wrap {
    display:flex;align-items:center;gap:10px;
    padding:14px 16px;
}
.pt-progress-bar-bg {
    flex:1;height:8px;border-radius:999px;background:var(--surface3);overflow:hidden;
}
.pt-progress-bar { height:100%;border-radius:999px;transition:width .4s var(--ease); }
.pt-progress-bar.complete { background:var(--green); }
.pt-progress-bar.partial  { background:var(--amber); }
.pt-progress-bar.empty    { background:var(--surface3); }
.pt-progress-label { font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text-sub);white-space:nowrap; }

/* Packed boxes list */
.pt-box-item {
    display:flex;align-items:center;gap:12px;
    padding:10px 16px;border-bottom:1px solid var(--border);
}
.pt-box-item:last-child { border-bottom:none; }
.pt-box-code {
    font-size:12px;font-family:var(--mono);font-weight:700;
    color:var(--accent);background:var(--accent-dim);
    padding:3px 10px;border-radius:6px;white-space:nowrap;
}
.pt-box-product { font-size:13px;color:var(--text);font-weight:600;flex:1; }
.pt-box-items   { font-size:12px;color:var(--text-dim);font-family:var(--mono); }

/* Flash */
.pt-flash {
    display:flex;align-items:flex-start;gap:10px;
    padding:12px 16px;border-radius:10px;border:1px solid;font-size:14px;
}
.pt-flash.success { background:var(--success-dim);border-color:rgba(22,163,74,.25);color:#14532d; }
.pt-flash.error   { background:var(--red-dim);    border-color:rgba(225,29,72,.25); color:#7f1d1d; }

/* Transporter select */
.pt-select {
    width:100%;padding:11px 14px;
    background:var(--surface);color:var(--text);
    border:1.5px solid var(--border-hi);border-radius:8px;
    font-size:14px;font-family:var(--font);outline:none;
    transition:border-color var(--tr),box-shadow var(--tr);
}
.pt-select:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow); }

/* Buttons */
.pt-btn {
    display:inline-flex;align-items:center;justify-content:center;gap:8px;
    padding:11px 24px;border-radius:9px;border:none;cursor:pointer;
    font-size:14px;font-weight:700;font-family:var(--font);
    transition:background var(--tr),box-shadow var(--tr),transform var(--tr),opacity var(--tr);
}
.pt-btn:active { transform:scale(.97); }
.pt-btn:disabled { opacity:.4;cursor:not-allowed;transform:none; }
.pt-btn-primary {
    background:var(--accent);color:#fff;
    box-shadow:0 2px 8px var(--accent-glow);width:100%;
}
.pt-btn-primary:hover:not(:disabled) { background:#2d5dbf;box-shadow:0 4px 14px var(--accent-glow); }

/* Pending confirmation card */
.pt-confirm-card {
    background:var(--amber-dim);border:1.5px solid rgba(217,119,6,.3);
    border-radius:10px;padding:16px 18px;
    display:flex;flex-direction:column;gap:12px;
}
.pt-confirm-title { font-size:14px;font-weight:700;color:var(--amber); }
.pt-qty-row { display:flex;align-items:center;gap:10px; }
.pt-qty-input {
    width:80px;padding:9px 12px;text-align:center;
    background:var(--surface);color:var(--text);
    border:1.5px solid var(--border-hi);border-radius:8px;
    font-size:16px;font-weight:800;font-family:var(--mono);outline:none;
}
.pt-qty-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow); }
.pt-btn-confirm {
    padding:9px 18px;background:var(--amber);color:#fff;
    border:none;border-radius:8px;font-size:13px;font-weight:700;
    font-family:var(--font);cursor:pointer;transition:background var(--tr);
}
.pt-btn-confirm:hover { background:#b45309; }
.pt-btn-cancel-sm {
    padding:9px 14px;background:var(--surface3);color:var(--text-sub);
    border:1px solid var(--border);border-radius:8px;font-size:13px;
    font-weight:600;font-family:var(--font);cursor:pointer;transition:background var(--tr);
}
.pt-btn-cancel-sm:hover { background:var(--border); }

/* Modal */
.pt-modal-overlay {
    position:fixed;inset:0;z-index:50;
    background:rgba(10,14,26,.6);backdrop-filter:blur(4px);
    display:flex;align-items:center;justify-content:center;padding:20px;
    animation:ptFadeIn .15s ease;
}
@keyframes ptFadeIn { from{opacity:0} to{opacity:1} }
.pt-modal {
    background:var(--surface);border:1px solid var(--border);
    border-radius:14px;width:100%;max-width:480px;
    box-shadow:0 24px 60px rgba(0,0,0,.18);
    animation:ptSlideUp .2s var(--ease);
}
@keyframes ptSlideUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
.pt-modal-head {
    display:flex;align-items:center;justify-content:space-between;
    padding:18px 22px;border-bottom:1px solid var(--border);
}
.pt-modal-title { font-size:16px;font-weight:800;color:var(--text); }
.pt-modal-body  { padding:22px;display:flex;flex-direction:column;gap:16px; }
.pt-modal-foot  {
    display:flex;align-items:center;justify-content:flex-end;gap:10px;
    padding:16px 22px;border-top:1px solid var(--border);
}
.pt-modal-close {
    width:32px;height:32px;border-radius:8px;
    background:var(--surface2);border:1px solid var(--border);
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;color:var(--text-sub);transition:background var(--tr);
}
.pt-modal-close:hover { background:var(--surface3); }

.pt-field-label {
    font-size:11px;font-weight:700;letter-spacing:.5px;
    text-transform:uppercase;color:var(--text-sub);margin-bottom:6px;display:block;
}
.pt-field-error { font-size:12px;color:var(--red);margin-top:4px;font-weight:600; }

@media(max-width:640px) {
    .pt-btn-primary { font-size:15px; }
}
@keyframes spin { to { transform:rotate(360deg) } }
</style>

<div class="pt-wrap">

    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="pt-flash success">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:2px">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @foreach(['scan_success','scan_error','error'] as $flashKey)
        @if(session()->has($flashKey))
            <div class="pt-flash {{ str_contains($flashKey,'error') ? 'error' : 'success' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:2px">
                    @if(str_contains($flashKey,'error'))
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @endif
                </svg>
                {{ session($flashKey) }}
            </div>
        @endif
    @endforeach

    {{-- Transfer Header --}}
    <div class="pt-card">
        <div class="pt-card-head">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span style="font-size:20px;font-weight:800;color:var(--text)">{{ $transfer->transfer_number }}</span>
                <span style="font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;
                             background:var(--accent-dim);color:var(--accent);border:1px solid rgba(59,111,212,.2)">
                    {{ $transfer->status->label() }}
                </span>
            </div>
            <span style="font-size:12px;color:var(--text-dim)">{{ $transfer->requested_at?->format('M d, Y') }}</span>
        </div>
        <div class="pt-card-body">
            <div class="pt-route">
                <div class="pt-route-node">
                    <div class="pt-route-label">From Warehouse</div>
                    <div class="pt-route-name">{{ $transfer->fromWarehouse->name }}</div>
                </div>
                <div class="pt-route-arrow">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>
                <div class="pt-route-node" style="text-align:right">
                    <div class="pt-route-label">To Shop</div>
                    <div class="pt-route-name">{{ $transfer->toShop->name }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── RENDER BASED ON WHICH COMPONENT IS ACTIVE (from Step 3) ──
         If using App\Livewire\Inventory\Transfers\PackTransfer (scan by product barcode):
         Use $packingSummary, $pendingBarcode, $pendingProductName, $pendingAvailableCount,
         $scanQuantity, $packedBoxes, $transporter_id, wire:click="scanProduct", wire:click="confirmQuantity"

         If using App\Livewire\WarehouseManager\Transfers\PackTransfer (manual box UI):
         Use $items, $assignedBoxes, $availableBoxes, $transporterId,
         wire:click="addBoxToProduct($productId)", wire:click="ship", $showShipModal
    ──}}

    {{-- Scan Section (adjust method name per Step 3 discovery) --}}
    <div class="pt-card">
        <div class="pt-card-head">
            <span class="pt-card-title">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="display:inline;vertical-align:-2px;margin-right:5px">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                Scan Box / Product Barcode
            </span>
            <span style="font-size:12px;color:var(--text-dim)">Scan product barcode to pack boxes</span>
        </div>
        <div class="pt-card-body">
            <div class="pt-scan-bar">
                <svg width="20" height="20" fill="none" stroke="var(--text-dim)" viewBox="0 0 24 24" stroke-width="2" style="flex-shrink:0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <input type="text"
                       wire:model="scanInput"
                       wire:keydown.enter="scanProduct"
                       class="pt-scan-input"
                       placeholder="Enter barcode and press Enter…"
                       autofocus>
                <button type="button" wire:click="scanProduct" class="pt-scan-btn">
                    Scan
                </button>
            </div>

            {{-- Pending confirmation (shown after scanning a product barcode) --}}
            @if($pendingBarcode ?? false)
                <div class="pt-confirm-card" style="margin-top:16px">
                    <div class="pt-confirm-title">
                        Confirm: {{ $pendingProductName }} — {{ $pendingAvailableCount }} box(es) available
                    </div>
                    <div class="pt-qty-row">
                        <span style="font-size:13px;color:var(--text-sub);font-weight:600">Quantity:</span>
                        <input type="number"
                               wire:model="scanQuantity"
                               min="1"
                               max="{{ $pendingAvailableCount }}"
                               class="pt-qty-input">
                        <button type="button" wire:click="confirmQuantity" class="pt-btn-confirm">
                            ✓ Confirm
                        </button>
                        <button type="button" wire:click="cancelPending" class="pt-btn-cancel-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Packing Progress per Product --}}
    @if(isset($packingSummary) && count($packingSummary) > 0)
    <div class="pt-card">
        <div class="pt-card-head">
            <span class="pt-card-title">Packing Progress</span>
        </div>
        <div class="pt-card-body" style="display:flex;flex-direction:column;gap:12px">
            @foreach($packingSummary as $summary)
                @php
                    $pct = $summary['boxes_needed'] > 0
                        ? min(100, round($summary['boxes_packed'] / $summary['boxes_needed'] * 100))
                        : 0;
                    $barClass = $summary['complete'] ? 'complete' : ($summary['boxes_packed'] > 0 ? 'partial' : 'empty');
                @endphp
                <div class="pt-product-row">
                    <div class="pt-product-head">
                        <span class="pt-product-name">{{ $summary['product_name'] }}</span>
                        <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim);
                                     background:var(--surface3);padding:2px 8px;border-radius:5px">
                            {{ $summary['barcode'] }}
                        </span>
                    </div>
                    <div class="pt-progress-wrap">
                        <div class="pt-progress-bar-bg">
                            <div class="pt-progress-bar {{ $barClass }}" style="width:{{ $pct }}%"></div>
                        </div>
                        <span class="pt-progress-label
                            {{ $summary['complete'] ? 'color:var(--green)' : '' }}">
                            {{ $summary['boxes_packed'] }} / {{ $summary['boxes_needed'] }} boxes
                        </span>
                        @if($summary['complete'])
                            <svg width="16" height="16" fill="none" stroke="var(--green)" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Packed Boxes List --}}
    @if(isset($packedBoxes) && count($packedBoxes) > 0)
    <div class="pt-card">
        <div class="pt-card-head">
            <span class="pt-card-title">Packed Boxes</span>
            <span style="font-size:12px;font-weight:700;font-family:var(--mono);
                         background:var(--accent-dim);color:var(--accent);padding:3px 10px;border-radius:6px">
                {{ count($packedBoxes) }} boxes
            </span>
        </div>
        <div style="divide-y:var(--border)">
            @foreach($packedBoxes as $box)
                <div class="pt-box-item">
                    <span class="pt-box-code">{{ $box['box_code'] }}</span>
                    <span class="pt-box-product">{{ $box['product_name'] }}</span>
                    <span class="pt-box-items">{{ $box['items'] }} items</span>
                    @if($box['scanned_out'] ?? false)
                        <svg width="14" height="14" fill="none" stroke="var(--green)" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Transporter + Ship --}}
    <div class="pt-card">
        <div class="pt-card-head">
            <span class="pt-card-title">Ship Transfer</span>
        </div>
        <div class="pt-card-body" style="display:flex;flex-direction:column;gap:16px">
            <div>
                <label class="pt-field-label">
                    Transporter <span style="color:var(--red)">*</span>
                </label>
                <select wire:model="transporter_id" class="pt-select">
                    <option value="">Select transporter…</option>
                    @foreach($transporters as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->name }}{{ $t->vehicle_number ? ' — ' . $t->vehicle_number : '' }}
                        </option>
                    @endforeach
                </select>
                @error('transporter_id')
                    <span class="pt-field-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="button"
                    wire:click="shipTransfer"
                    @if(empty($packedBoxes ?? [])) disabled @endif
                    class="pt-btn pt-btn-primary"
                    wire:loading.attr="disabled"
                    wire:target="shipTransfer">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"
                     wire:loading.remove wire:target="shipTransfer">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     wire:loading wire:target="shipTransfer"
                     style="animation:spin 1s linear infinite">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"
                            stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round"/>
                </svg>
                <span wire:loading.remove wire:target="shipTransfer">Ship Transfer</span>
                <span wire:loading wire:target="shipTransfer">Shipping…</span>
            </button>
            <p style="font-size:12px;color:var(--text-dim);text-align:center;margin-top:-8px">
                Partial shipments are allowed. Pack at least one box before shipping.
            </p>
        </div>
    </div>

</div>
```

**IMPORTANT after writing this blade:** Re-read the active PHP component render() method. Check if it passes `$packedBoxes`, `$packingSummary`, `$pendingBarcode`, `$transporters`. If any of those variable names differ, update the blade to match the PHP exactly. Do NOT change the PHP.

---

## STEP 6 — Redesign: Receive Transfer Page (Shop Manager)

**Target:** `resources/views/livewire/shop/transfers/receive-transfer.blade.php`

### 6a — Read the PHP component first

```bash
cat app/Livewire/Shop/Transfers/ReceiveTransfer.php
```

Note: `scanBox()`, `confirmQuantity()`, `cancelPending()`, `completeReceipt()`, `toggleDamaged($boxId)`, `updateDamageNotes($boxId, $notes)`, `removeScannedBox($boxId)`, `$scannedBoxes`, `$pendingBarcode`, `$pendingProductName`, `$pendingAvailableCount`, `$scanQuantity`, `$scanInput`

### 6b — Rewrite the blade

Replace the entire file content:

```blade
@php use App\Enums\TransferStatus; @endphp

<style>
/* ── Receive Transfer — Design System ── */
.rv-wrap { display:flex;flex-direction:column;gap:20px;font-family:var(--font); }

.rv-card {
    background:var(--surface);border:1px solid var(--border);
    border-radius:12px;overflow:hidden;
}
.rv-card-head {
    padding:16px 22px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;gap:12px;
}
.rv-card-title { font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-sub); }
.rv-card-body  { padding:22px; }

/* Route */
.rv-route {
    display:flex;align-items:center;
    background:var(--surface2);border-radius:10px;
    padding:14px 18px;border:1px solid var(--border);
}
.rv-route-node  { flex:1; }
.rv-route-label { font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim); }
.rv-route-name  { font-size:14px;font-weight:700;color:var(--text);margin-top:3px; }
.rv-route-arrow {
    width:34px;height:34px;border-radius:50%;
    background:var(--green-dim);color:var(--green);
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}

/* Scan */
.rv-scan-bar {
    display:flex;gap:10px;align-items:center;
    padding:18px;background:var(--surface2);border-radius:10px;
    border:1.5px dashed var(--border-hi);
}
.rv-scan-input {
    flex:1;padding:11px 14px;
    background:var(--surface);color:var(--text);
    border:1.5px solid var(--border-hi);border-radius:8px;
    font-size:15px;font-family:var(--mono);font-weight:600;outline:none;
    transition:border-color var(--tr),box-shadow var(--tr);
}
.rv-scan-input:focus { border-color:var(--green);box-shadow:0 0 0 3px var(--green-glow); }
.rv-scan-btn {
    padding:11px 20px;background:var(--green);color:#fff;
    border:none;border-radius:8px;font-size:14px;font-weight:700;
    font-family:var(--font);cursor:pointer;transition:background var(--tr);
}
.rv-scan-btn:hover { background:#0a7a67; }

/* Confirm card */
.rv-confirm-card {
    background:var(--accent-dim);border:1.5px solid rgba(59,111,212,.3);
    border-radius:10px;padding:16px 18px;
    display:flex;flex-direction:column;gap:12px;margin-top:16px;
}
.rv-confirm-title { font-size:14px;font-weight:700;color:var(--accent); }
.rv-qty-row { display:flex;align-items:center;gap:10px; }
.rv-qty-input {
    width:80px;padding:9px 12px;text-align:center;
    background:var(--surface);color:var(--text);
    border:1.5px solid var(--border-hi);border-radius:8px;
    font-size:16px;font-weight:800;font-family:var(--mono);outline:none;
}
.rv-qty-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow); }
.rv-btn-sm {
    padding:9px 16px;border:none;border-radius:8px;
    font-size:13px;font-weight:700;font-family:var(--font);cursor:pointer;
    transition:background var(--tr);
}
.rv-btn-confirm { background:var(--accent);color:#fff; }
.rv-btn-confirm:hover { background:#2d5dbf; }
.rv-btn-cancel  { background:var(--surface3);color:var(--text-sub);border:1px solid var(--border); }
.rv-btn-cancel:hover { background:var(--border); }

/* Scanned boxes list */
.rv-box-row {
    border:1.5px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:10px;
}
.rv-box-row.is-damaged { border-color:rgba(225,29,72,.4); }
.rv-box-head {
    display:flex;align-items:center;gap:10px;padding:12px 16px;
    background:var(--surface2);border-bottom:1px solid var(--border);flex-wrap:wrap;
}
.rv-box-code {
    font-size:12px;font-family:var(--mono);font-weight:700;
    color:var(--accent);background:var(--accent-dim);
    padding:3px 10px;border-radius:6px;
}
.rv-box-product { font-size:13px;font-weight:700;color:var(--text);flex:1; }
.rv-box-actions { display:flex;align-items:center;gap:8px;padding:10px 16px;flex-wrap:wrap; }
.rv-dmg-btn {
    padding:6px 14px;border-radius:7px;border:none;cursor:pointer;
    font-size:12px;font-weight:700;font-family:var(--font);transition:background var(--tr);
}
.rv-dmg-btn.off { background:var(--surface3);color:var(--text-sub);border:1px solid var(--border); }
.rv-dmg-btn.on  { background:var(--red-dim);color:var(--red);border:1px solid rgba(225,29,72,.3); }
.rv-dmg-notes {
    flex:1;min-width:200px;padding:7px 12px;
    background:var(--surface);color:var(--text);
    border:1.5px solid rgba(225,29,72,.4);border-radius:7px;
    font-size:13px;font-family:var(--font);outline:none;
}
.rv-remove-btn {
    padding:6px 12px;background:transparent;color:var(--red);
    border:1px solid rgba(225,29,72,.25);border-radius:7px;
    font-size:12px;font-weight:700;cursor:pointer;transition:background var(--tr);
}
.rv-remove-btn:hover { background:var(--red-dim); }

/* Summary bar */
.rv-summary-bar {
    display:grid;grid-template-columns:repeat(3,1fr);gap:12px;
    padding:16px 22px;background:var(--surface2);border-top:1px solid var(--border);
}
.rv-stat { display:flex;flex-direction:column;gap:3px;align-items:center;text-align:center; }
.rv-stat-value { font-size:22px;font-weight:800;font-family:var(--mono);color:var(--text); }
.rv-stat-label { font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim); }

/* Flash */
.rv-flash {
    display:flex;align-items:flex-start;gap:10px;
    padding:12px 16px;border-radius:10px;border:1px solid;font-size:14px;
}
.rv-flash.success { background:var(--success-dim);border-color:rgba(22,163,74,.25);color:#14532d; }
.rv-flash.error   { background:var(--red-dim);    border-color:rgba(225,29,72,.25); color:#7f1d1d; }
.rv-flash.info    { background:var(--accent-dim); border-color:rgba(59,111,212,.25);color:#1e3a8a; }

/* Complete button */
.rv-complete-btn {
    display:flex;align-items:center;justify-content:center;gap:10px;
    width:100%;padding:13px 24px;
    background:var(--green);color:#fff;
    border:none;border-radius:9px;cursor:pointer;
    font-size:15px;font-weight:800;font-family:var(--font);
    box-shadow:0 2px 8px var(--green-glow);
    transition:background var(--tr),box-shadow var(--tr),opacity var(--tr);
}
.rv-complete-btn:hover:not(:disabled) { background:#0a7a67;box-shadow:0 4px 14px var(--green-glow); }
.rv-complete-btn:disabled { opacity:.4;cursor:not-allowed; }
@keyframes spin { to { transform:rotate(360deg) } }
</style>

<div class="rv-wrap">

    {{-- Flash --}}
    @foreach(['success','error','scan_success','scan_error','info'] as $fk)
        @if(session()->has($fk))
            <div class="rv-flash {{ str_contains($fk,'error') ? 'error' : (str_contains($fk,'info') ? 'info' : 'success') }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:2px">
                    @if(str_contains($fk,'error'))
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @endif
                </svg>
                {{ session($fk) }}
            </div>
        @endif
    @endforeach

    {{-- Header --}}
    <div class="rv-card">
        <div class="rv-card-head">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span style="font-size:20px;font-weight:800;color:var(--text)">{{ $transfer->transfer_number }}</span>
                <span style="font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;
                             background:var(--green-dim);color:var(--green);border:1px solid rgba(14,158,134,.2)">
                    Receiving
                </span>
            </div>
        </div>
        <div class="rv-card-body">
            <div class="rv-route">
                <div class="rv-route-node">
                    <div class="rv-route-label">From Warehouse</div>
                    <div class="rv-route-name">{{ $transfer->fromWarehouse->name }}</div>
                </div>
                <div class="rv-route-arrow">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>
                <div class="rv-route-node" style="text-align:right">
                    <div class="rv-route-label">To Shop</div>
                    <div class="rv-route-name">{{ $transfer->toShop->name }}</div>
                </div>
            </div>
            <div style="display:flex;gap:16px;margin-top:14px;flex-wrap:wrap">
                <div>
                    <span style="font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim)">Boxes Shipped</span>
                    <div style="font-size:18px;font-weight:800;font-family:var(--mono);color:var(--text)">{{ $transfer->boxes()->count() }}</div>
                </div>
                <div>
                    <span style="font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim)">Scanned So Far</span>
                    <div style="font-size:18px;font-weight:800;font-family:var(--mono);color:var(--green)">{{ count($scannedBoxes) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scan Section --}}
    <div class="rv-card">
        <div class="rv-card-head">
            <span class="rv-card-title">Scan Boxes</span>
            <span style="font-size:12px;color:var(--text-dim)">Scan box code or product barcode</span>
        </div>
        <div class="rv-card-body">
            <div class="rv-scan-bar">
                <svg width="20" height="20" fill="none" stroke="var(--text-dim)" viewBox="0 0 24 24" stroke-width="2" style="flex-shrink:0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <input type="text"
                       wire:model="scanInput"
                       wire:keydown.enter="scanBox"
                       class="rv-scan-input"
                       placeholder="Enter box code or product barcode…"
                       autofocus>
                <button type="button" wire:click="scanBox" class="rv-scan-btn">
                    Scan
                </button>
            </div>

            @if($pendingBarcode ?? false)
                <div class="rv-confirm-card">
                    <div class="rv-confirm-title">
                        {{ $pendingProductName }} — {{ $pendingAvailableCount }} box(es) to receive
                    </div>
                    <div class="rv-qty-row">
                        <span style="font-size:13px;color:var(--text-sub);font-weight:600">Quantity:</span>
                        <input type="number"
                               wire:model="scanQuantity"
                               min="1"
                               max="{{ $pendingAvailableCount }}"
                               class="rv-qty-input">
                        <button type="button" wire:click="confirmQuantity" class="rv-btn-sm rv-btn-confirm">
                            ✓ Confirm
                        </button>
                        <button type="button" wire:click="cancelPending" class="rv-btn-sm rv-btn-cancel">
                            Cancel
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Scanned Boxes --}}
    @if(count($scannedBoxes) > 0)
    <div class="rv-card">
        <div class="rv-card-head">
            <span class="rv-card-title">Scanned Boxes</span>
            <span style="font-size:12px;font-weight:700;font-family:var(--mono);
                         background:var(--green-dim);color:var(--green);padding:3px 10px;border-radius:6px">
                {{ count($scannedBoxes) }} scanned
            </span>
        </div>
        <div class="rv-card-body" style="display:flex;flex-direction:column;gap:10px">
            @foreach($scannedBoxes as $boxId => $box)
                <div class="rv-box-row {{ ($box['is_damaged'] ?? false) ? 'is-damaged' : '' }}">
                    <div class="rv-box-head">
                        <span class="rv-box-code">{{ $box['box_code'] }}</span>
                        <span class="rv-box-product">{{ $box['product_name'] }}</span>
                        <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">
                            {{ $box['items_remaining'] ?? '?' }} items
                        </span>
                    </div>
                    <div class="rv-box-actions">
                        <button type="button"
                                wire:click="toggleDamaged({{ $boxId }})"
                                class="rv-dmg-btn {{ ($box['is_damaged'] ?? false) ? 'on' : 'off' }}">
                            {{ ($box['is_damaged'] ?? false) ? '⚠ Damaged' : 'Mark Damaged' }}
                        </button>
                        @if($box['is_damaged'] ?? false)
                            <input type="text"
                                   wire:model.blur="scannedBoxes.{{ $boxId }}.damage_notes"
                                   placeholder="Describe damage…"
                                   class="rv-dmg-notes">
                        @endif
                        <button type="button"
                                wire:click="removeScannedBox({{ $boxId }})"
                                class="rv-remove-btn">
                            Remove
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rv-summary-bar">
            <div class="rv-stat">
                <span class="rv-stat-value">{{ $transfer->boxes()->count() }}</span>
                <span class="rv-stat-label">Expected</span>
            </div>
            <div class="rv-stat">
                <span class="rv-stat-value" style="color:var(--green)">{{ count($scannedBoxes) }}</span>
                <span class="rv-stat-label">Scanned</span>
            </div>
            <div class="rv-stat">
                <span class="rv-stat-value" style="{{ collect($scannedBoxes)->where('is_damaged',true)->count() > 0 ? 'color:var(--red)' : '' }}">
                    {{ collect($scannedBoxes)->where('is_damaged',true)->count() }}
                </span>
                <span class="rv-stat-label">Damaged</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Complete Receipt --}}
    <div class="rv-card">
        <div class="rv-card-body">
            @if(count($scannedBoxes) < $transfer->boxes()->count())
                <div style="padding:12px 14px;background:var(--amber-dim);border:1px solid rgba(217,119,6,.25);
                            border-radius:8px;margin-bottom:14px;font-size:13px;color:var(--amber);font-weight:600">
                    ⚠ {{ $transfer->boxes()->count() - count($scannedBoxes) }} box(es) not yet scanned.
                    Partial deliveries are allowed and will be recorded as discrepancies.
                </div>
            @endif

            <button type="button"
                    wire:click="completeReceipt"
                    @if(empty($scannedBoxes)) disabled @endif
                    class="rv-complete-btn"
                    wire:loading.attr="disabled"
                    wire:target="completeReceipt">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"
                     wire:loading.remove wire:target="completeReceipt">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     wire:loading wire:target="completeReceipt"
                     style="animation:spin 1s linear infinite">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"
                            stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round"/>
                </svg>
                <span wire:loading.remove wire:target="completeReceipt">Complete Receiving</span>
                <span wire:loading wire:target="completeReceipt">Processing…</span>
            </button>
        </div>
    </div>

</div>
```

---

## STEP 7 — Verify all wire: bindings match actual PHP

```bash
# Pack Transfer — check method names
grep -n "public function " app/Livewire/WarehouseManager/Transfers/PackTransfer.php
grep -n "public function " app/Livewire/Inventory/Transfers/PackTransfer.php

# Receive Transfer — check method names
grep -n "public function " app/Livewire/Shop/Transfers/ReceiveTransfer.php

# Check property names in Receive
grep -n "public " app/Livewire/Shop/Transfers/ReceiveTransfer.php | grep -v "function"
```

If any method or property name in the blade does NOT match the PHP, fix the blade to match the PHP. Never change the PHP to match the blade.

---

## STEP 8 — Clear all caches

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## STEP 9 — Smoke test checklist

### Review Transfer (Warehouse Manager)
- [ ] `/warehouse/transfers/{pending_id}` — boxes requested show correct integer (not 0)
- [ ] Edit a box quantity → Approve button stays clickable while typing
- [ ] Click **Approve Transfer** → redirects to index with success flash
- [ ] Click **Reject Request** → modal opens → fill reason → click Reject → redirects

### Pack Transfer (Warehouse Manager)
- [ ] `/warehouse/transfers/{approved_id}/pack` loads without error
- [ ] Scan a product barcode → confirm card appears
- [ ] Confirm quantity → box appears in Packed Boxes list
- [ ] Select transporter → click Ship → redirects to index

### Receive Transfer (Shop Manager)
- [ ] `/shop/transfers/{in_transit_id}/receive` loads without 403 error
- [ ] Scan box code → box appears in scanned list
- [ ] Mark as damaged → damage notes input appears
- [ ] Partial receipt warning shows if not all boxes scanned
- [ ] Complete Receiving → redirects with success

### Policy check
```bash
php artisan tinker
# Run this to verify policy:
# $transfer = App\Models\Transfer::where('status','in_transit')->first();
# $user = App\Models\User::where('role','shop_manager')->first();
# $user->can('receive', $transfer); // must return true
```
