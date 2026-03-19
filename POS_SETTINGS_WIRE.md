# POS — Wire All Settings
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read POS_SETTINGS_WIRE.md and follow every step in order."

---

## Read these files first

```bash
cat app/Livewire/Shop/Sales/PointOfSale.php
cat resources/views/livewire/shop/sales/point-of-sale.blade.php
cat app/Services/SettingsService.php
```

---

## Overview of changes

| Setting | PHP change | Blade change |
|---|---|---|
| `allow_individual_item_sales` + `individual_sale_category_ids` | `openAddModal()` sets flag | staging modal hides item tab |
| `allow_price_override` | `confirmAddToCart()` blocks | staging modal hides price field |
| `price_override_threshold` | replace hardcoded 20% in two places | warning text uses real value |
| `allow_credit_sales` | `openCheckout()` zeros credit | checkout hides credit row |
| `credit_requires_customer` | `updatedPayAmtCredit()` hard blocks | inline hint text |
| `max_credit_per_customer` | `updatedPayAmtCredit()` checks limit | warning message |

---

## STEP 1 — Add settings properties to the PHP component

**File:** `app/Livewire/Shop/Sales/PointOfSale.php`

Find the properties block near the top of the class (after the payment
properties). Add these new properties:

```php
// ── Settings (loaded once on mount) ──────────────────────────────────────
public bool  $settingAllowIndividualSales    = true;
public array $settingIndividualCategoryIds   = [];
public bool  $settingAllowPriceOverride      = true;
public int   $settingPriceOverrideThreshold  = 20;
public bool  $settingAllowCreditSales        = true;
public bool  $settingCreditRequiresCustomer  = true;
public int   $settingMaxCreditPerCustomer    = 0;
```

---

## STEP 2 — Load settings in mount()

**File:** `app/Livewire/Shop/Sales/PointOfSale.php`

Find the `mount()` method. At the very end of the method, just before the
closing `}`, add:

```php
// Load operational settings
$settings = app(\App\Services\SettingsService::class);
$this->settingAllowIndividualSales   = $settings->allowIndividualItemSales();
$this->settingIndividualCategoryIds  = $settings->individualSaleCategoryIds();
$this->settingAllowPriceOverride     = $settings->allowPriceOverride();
$this->settingPriceOverrideThreshold = $settings->priceOverrideThreshold();
$this->settingAllowCreditSales       = $settings->allowCreditSales();
$this->settingCreditRequiresCustomer = $settings->creditRequiresCustomer();
$this->settingMaxCreditPerCustomer   = $settings->maxCreditPerCustomer();
```

---

## STEP 3 — Add category-level individual sale check to openAddModal()

**File:** `app/Livewire/Shop/Sales/PointOfSale.php`

Find the `private function openAddModal(Product $product, array $stock)` method.

After the line that sets `$this->stagingProduct = [...]`, add:

```php
// Determine if individual item sales are allowed for this product's category
$categoryId = $product->category_id;
$individualAllowed = $this->settingAllowIndividualSales && (
    empty($this->settingIndividualCategoryIds)
    || in_array($categoryId, $this->settingIndividualCategoryIds)
);
$this->stagingProduct['individual_sale_allowed'] = $individualAllowed;
```

Also make sure `'category_id' => $product->category_id` is included in the
`$this->stagingProduct = [...]` array. If it is missing, add it.

---

## STEP 4 — Replace hardcoded 20% threshold in confirmAddToCart()

**File:** `app/Livewire/Shop/Sales/PointOfSale.php`

Find `confirmAddToCart()` (also may be called `commitStagingToCart()`).
Inside it, find this line:

```php
$cartItem['requires_owner_approval'] = $percentageChange > 20;
```

Replace with:

```php
$cartItem['requires_owner_approval'] = $percentageChange > $this->settingPriceOverrideThreshold;
```

Also find where price modification is checked and `allow_price_override`
is relevant. At the TOP of `confirmAddToCart()`, add:

```php
// Block individual item sales if setting disallows it
if (!$this->stagingProduct['individual_sale_allowed'] && $this->stagingMode === 'item') {
    $this->dispatch('notification', [
        'type'    => 'error',
        'message' => 'Individual item sales are not allowed for this product category',
    ]);
    return;
}

// Block price override if setting disallows it
if (!$this->settingAllowPriceOverride && $this->stagingPriceModified) {
    $this->dispatch('notification', [
        'type'    => 'error',
        'message' => 'Price modifications are not allowed',
    ]);
    return;
}
```

---

## STEP 5 — Replace hardcoded 20% in applyPriceModification()

**File:** `app/Livewire/Shop/Sales/PointOfSale.php`

Find `applyPriceModification()`. Inside it, find:

```php
$requiresApproval = $percentageChange > 20;
```

Replace with:

```php
$requiresApproval = $percentageChange > $this->settingPriceOverrideThreshold;
```

Also find the notification that says `'Price change >20% requires owner approval'`
and update it:

```php
'message' => 'Price change >' . $this->settingPriceOverrideThreshold . '% requires owner approval'
```

---

## STEP 6 — Upgrade updatedPayAmtCredit() with settings checks

**File:** `app/Livewire/Shop/Sales/PointOfSale.php`

Find `updatedPayAmtCredit()`. Replace the entire method:

```php
public function updatedPayAmtCredit()
{
    if ($this->payAmt_credit <= 0) {
        $this->creditWarningVisible = false;
        $this->creditWarningMessage = '';
        return;
    }

    // Hard block: credit sales disabled by owner
    if (!$this->settingAllowCreditSales) {
        $this->payAmt_credit = 0;
        $this->dispatch('notification', [
            'type'    => 'error',
            'message' => 'Credit sales are disabled by the owner',
        ]);
        return;
    }

    // Hard block: customer required for credit
    if ($this->settingCreditRequiresCustomer && !$this->selectedCustomerId) {
        $this->payAmt_credit = 0;
        $this->dispatch('notification', [
            'type'    => 'warning',
            'message' => 'A registered customer must be selected before using credit',
        ]);
        return;
    }

    // Hard block: max credit per customer
    if ($this->settingMaxCreditPerCustomer > 0 && $this->selectedCustomerId) {
        $customer = \App\Models\Customer::find($this->selectedCustomerId);
        if ($customer) {
            $projectedBalance = $customer->outstanding_balance + $this->payAmt_credit;
            if ($projectedBalance > $this->settingMaxCreditPerCustomer) {
                $remaining = max(0, $this->settingMaxCreditPerCustomer - $customer->outstanding_balance);
                $this->payAmt_credit = $remaining;
                $this->dispatch('notification', [
                    'type'    => 'warning',
                    'message' => 'Credit limit reached. Maximum remaining credit for this customer: '
                        . number_format($remaining) . ' RWF',
                ]);
            }
        }
    }

    $this->evaluateCreditWarning();
}
```

---

## STEP 7 — Upgrade openCheckout() to zero credit when disabled

**File:** `app/Livewire/Shop/Sales/PointOfSale.php`

Find `openCheckout()`. After the line `$this->payAmt_credit = 0;` in the
reset block, add:

```php
// Enforce settings
if (!$this->settingAllowCreditSales) {
    $this->payAmt_credit = 0;
}
```

---

## STEP 8 — Upgrade completeSale() with settings guards

**File:** `app/Livewire/Shop/Sales/PointOfSale.php`

Find `completeSale()`. Find the existing credit check:

```php
// If credit is used, require customer selection
if ($this->payAmt_credit > 0 && !$this->selectedCustomerId) {
```

Replace the entire block (from that `if` to its closing `}` and `return;`)
with the upgraded version:

```php
// Belt-and-braces: credit disabled by setting
if ($this->payAmt_credit > 0 && !$this->settingAllowCreditSales) {
    $this->dispatch('notification', [
        'type' => 'error', 'message' => 'Credit sales are disabled',
    ]);
    return;
}

// Belt-and-braces: customer required for credit
if ($this->payAmt_credit > 0
    && $this->settingCreditRequiresCustomer
    && !$this->selectedCustomerId) {
    $this->dispatch('notification', [
        'type' => 'error',
        'message' => 'A registered customer must be selected for credit sales',
    ]);
    return;
}

// Belt-and-braces: max credit per customer
if ($this->payAmt_credit > 0
    && $this->settingMaxCreditPerCustomer > 0
    && $this->selectedCustomerId) {
    $customer = \App\Models\Customer::find($this->selectedCustomerId);
    if ($customer) {
        $projected = $customer->outstanding_balance + $this->payAmt_credit;
        if ($projected > $this->settingMaxCreditPerCustomer) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'This sale would exceed the customer\'s credit limit of '
                    . number_format($this->settingMaxCreditPerCustomer) . ' RWF',
            ]);
            return;
        }
    }
}
```

---

## STEP 9 — Blade: hide individual item tab when not allowed

**File:** `resources/views/livewire/shop/sales/point-of-sale.blade.php`

Find the staging modal section (`@if($showAddModal && $stagingProduct)`).
Inside it, find the mode selector — the two buttons for "Full Box" and
"Individual Items" (they use `wire:click="$set('stagingMode','box')"` and
`wire:click="$set('stagingMode','item')"`).

Wrap the entire "Individual Items" button with:

```blade
@if($stagingProduct['individual_sale_allowed'] ?? true)
    {{-- ... existing individual items button ... --}}
@else
    {{-- Disabled state --}}
    <div style="padding:14px 12px;border-radius:10px;border:2px solid var(--border);
                background:var(--surface2);opacity:.45;cursor:not-allowed;text-align:center">
        <div style="font-size:22px;margin-bottom:4px">🏷</div>
        <div style="font-size:13px;font-weight:700;color:var(--text-dim)">
            Individual Items
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:4px">
            Not allowed for this category
        </div>
    </div>
@endif
```

Also, if `$stagingMode` is forced to `'box'` when individual sales are blocked,
add a `wire:init` to the modal wrapper to force box mode:

```blade
@if(!($stagingProduct['individual_sale_allowed'] ?? true) && $stagingMode === 'item')
    {{-- Auto-correct mode if somehow stuck on item --}}
    <span wire:init="$set('stagingMode', 'box')"></span>
@endif
```

---

## STEP 10 — Blade: hide price field when override is disabled

**File:** `resources/views/livewire/shop/sales/point-of-sale.blade.php`

In the staging modal, find the price input field (it has `wire:model="stagingPrice"`).
Wrap the entire price editing section with:

```blade
@if($settingAllowPriceOverride)
    {{-- ... existing price input ... --}}
@else
    <div style="padding:9px 12px;background:var(--surface2);border:1px solid var(--border);
                border-radius:8px;font-family:var(--mono);font-size:13px;color:var(--text-sub)">
        {{ number_format($stagingPrice) }} RWF
        <span style="font-size:10px;color:var(--text-dim);margin-left:6px">
            · price locked by owner
        </span>
    </div>
@endif
```

---

## STEP 11 — Blade: hide credit row when disabled

**File:** `resources/views/livewire/shop/sales/point-of-sale.blade.php`

Find the credit payment row in the checkout modal. It starts with:

```blade
{{-- Credit --}}
<div style="display:flex;align-items:center;gap:10px;padding:8px 12px;
```

And contains `wire:model.blur="payAmt_credit"`.

Wrap the entire credit `<div>` block with:

```blade
@if($settingAllowCreditSales)
    {{-- ... existing credit row ... --}}
@endif
```

---

## STEP 12 — Blade: update credit hint text when customer required

**File:** `resources/views/livewire/shop/sales/point-of-sale.blade.php`

Inside the credit row (now wrapped in the `@if`), find the hint text:

```blade
@if(!$selectedCustomerId)
  <span style="color:var(--amber);font-size:10px">(select customer first)</span>
@endif
```

Replace with:

```blade
@if(!$selectedCustomerId && $settingCreditRequiresCustomer)
  <span style="color:var(--amber);font-size:10px">· select customer first</span>
@elseif(!$selectedCustomerId && !$settingCreditRequiresCustomer)
  <span style="color:var(--text-dim);font-size:10px">· no customer required</span>
@endif
```

Also update the `disabled` attribute on the credit input:

```blade
{{-- Before --}}
{{ !$selectedCustomerId ? 'disabled' : '' }}

{{-- After --}}
{{ ($settingCreditRequiresCustomer && !$selectedCustomerId) ? 'disabled' : '' }}
```

---

## STEP 13 — Clear caches

```bash
php artisan view:clear && php artisan cache:clear
```

---

## Do NOT touch

- `SaleService::createSale()` — no changes needed there
- Any other Livewire components
- Any migrations

---

## Verification

Run these scenarios after deploying:

**Individual items blocked:**
1. In Settings → Sales Rules, turn OFF "Allow individual item sales" → Save
2. Open POS → select a product → staging modal should show item tab as disabled
3. Turn back ON, but restrict to one category only → products in other categories show disabled tab

**Price override blocked:**
1. In Settings → Price Override, turn OFF "Allow sellers to modify prices" → Save
2. Open POS → select a product → price field is replaced with read-only display

**Price threshold:**
1. Set threshold to 10% → Save
2. In POS staging modal, change price by 15% → "requires owner approval" badge appears
3. Change by 5% → no approval required

**Credit disabled:**
1. In Settings → Credit Policy, turn OFF "Allow credit sales" → Save
2. Open POS checkout → credit row is completely gone

**Credit requires customer:**
1. Turn ON credit, turn ON "Require registered customer" → Save
2. Open POS checkout → try to enter credit amount without customer → reset to 0 with warning

**Max credit limit:**
1. Set max credit to 50,000 RWF → Save
2. Select a customer who already owes 40,000 RWF
3. Try to enter 20,000 RWF as credit → capped at 10,000 with warning
