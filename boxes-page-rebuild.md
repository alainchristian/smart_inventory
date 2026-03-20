# Boxes Page — Full Rebuild

> Read this file in full before writing any code.

---

## Why a full rebuild, not incremental edits

The current page is a single-purpose lookup table. The box is the primary tracked
physical unit in the entire system — every receipt, transfer, sale, and damage event
flows through a box — but none of that lifecycle is visible. The rebuild makes
`/owner/boxes` the owner's primary inventory command centre.

**Zero schema changes required.** All data is already in the database.

---

## Files to read before starting

Read every one of these in full. Do not guess at existing patterns.

```
app/Livewire/Inventory/Boxes/BoxList.php
resources/views/livewire/inventory/boxes/box-list.blade.php
app/Models/Box.php
app/Models/BoxMovement.php
app/Enums/BoxStatus.php
app/Enums/LocationType.php
resources/views/livewire/owner/products/product-detail.blade.php
app/Livewire/Owner/Products/ProductDetail.php
resources/views/livewire/products/product-list.blade.php
```

These files establish the design system, CSS variable tokens, drawer pattern,
table pattern, and KPI card pattern that this rebuild must match exactly.

---

## Design system rules (mandatory — do not invent new patterns)

```
Surface:   var(--surface)  var(--surface2)  var(--surface3)
Border:    var(--border)
Text:      var(--text)  var(--text-sub)  var(--text-dim)
Accent:    var(--accent)  var(--accent-dim)
Success:   var(--success)  var(--success-glow)
Warn:      var(--warn)  var(--warn-glow)
Amber:     var(--amber)  var(--amber-dim)
Danger:    var(--danger)  var(--danger-glow)
Red:       var(--red)  var(--red-dim)
Violet:    var(--violet)  var(--violet-dim)
Radius:    var(--r)  var(--rsm)
```

KPI card shell: `background:var(--surface); border:1px solid var(--border); border-radius:var(--r); overflow:hidden`
Sub-row strip: `background:var(--surface2); border-top:1px solid var(--border); padding:8px 16px`
Section label: `font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--text-sub)`
Drawer pattern: match `product-detail.blade.php` exactly — fixed right-side panel, backdrop, close button

---

## Files to modify

| File | What changes |
|------|-------------|
| `app/Livewire/Inventory/Boxes/BoxList.php` | Extend with sort, financial stats, stagnant count, new data for drawer |
| `resources/views/livewire/inventory/boxes/box-list.blade.php` | Full rebuild — new KPI strip, new table, drawer |
| *(new)* `app/Livewire/Inventory/Boxes/BoxDetail.php` | New Livewire component — box detail drawer |
| *(new)* `resources/views/livewire/inventory/boxes/box-detail.blade.php` | Drawer blade |

---

## Part 1 — Extend `BoxList.php`

### 1A — Add sort state

Add these public properties alongside the existing ones:

```php
public string $sortBy        = 'received_at';
public string $sortDirection = 'desc';
```

Add these to `$queryString`:

```php
'sortBy'        => ['except' => 'received_at'],
'sortDirection' => ['except' => 'desc'],
```

Add a `sortBy()` method (rename the variable to avoid collision with the property):

```php
public function sortColumn(string $field): void
{
    if ($this->sortBy === $field) {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortBy        = $field;
        $this->sortDirection = 'desc';
    }
    $this->resetPage();
}
```

Replace the fixed `->orderBy('created_at', 'desc')` in the query with:

```php
->orderBy($this->sortBy, $this->sortDirection)
```

Allowed sort fields (validate against this list in `sortColumn()` to prevent SQL injection):

```php
$allowed = ['received_at', 'items_remaining', 'status', 'expiry_date', 'cost_value'];
if (!in_array($field, $allowed)) return;
```

For `cost_value` sorting, add a `selectRaw` sub-expression to the query:

```php
->when($this->sortBy === 'cost_value', function ($q) {
    $q->join('products as p_sort', 'boxes.product_id', '=', 'p_sort.id')
      ->orderByRaw('(boxes.items_remaining * p_sort.purchase_price) ' . $this->sortDirection);
})
->when($this->sortBy !== 'cost_value', function ($q) {
    $q->orderBy('boxes.' . $this->sortBy, $this->sortDirection);
})
```

### 1B — Replace the cached stats with live enriched stats

The current stats block is cached and only counts boxes. Replace it entirely
with a single un-cached DB query that also computes financial and fill data.
The stats are only used for the KPI strip at the top, so performance is fine.

```php
// Build the base stats query respecting the current location filter
$statsQuery = DB::table('boxes')
    ->join('products', 'boxes.product_id', '=', 'products.id')
    ->when($this->locationType && $this->locationId, fn($q) =>
        $q->where('boxes.location_type', $this->locationType)
          ->where('boxes.location_id', $this->locationId)
    );

$stats = (clone $statsQuery)->selectRaw("
    COUNT(*) as total,
    SUM(CASE WHEN boxes.status = 'full'    THEN 1 ELSE 0 END) as full_count,
    SUM(CASE WHEN boxes.status = 'partial' THEN 1 ELSE 0 END) as partial_count,
    SUM(CASE WHEN boxes.status = 'empty'   THEN 1 ELSE 0 END) as empty_count,
    SUM(CASE WHEN boxes.status = 'damaged' THEN 1 ELSE 0 END) as damaged_count,
    SUM(boxes.items_remaining) as total_items,
    SUM(boxes.items_total)     as total_capacity,
    SUM(CASE WHEN boxes.status IN ('full','partial') AND boxes.items_remaining > 0
             THEN boxes.items_remaining * products.purchase_price ELSE 0 END) as cost_value,
    SUM(CASE WHEN boxes.status IN ('full','partial') AND boxes.items_remaining > 0
             THEN boxes.items_remaining * products.selling_price  ELSE 0 END) as retail_value,
    SUM(CASE WHEN boxes.expiry_date IS NOT NULL
             AND boxes.expiry_date <= NOW() + INTERVAL '30 days'
             AND boxes.expiry_date >= NOW()
             THEN 1 ELSE 0 END) as expiring_soon
")->first();

// Fill rate: only across sellable (full/partial) boxes
$fillableBases = (clone $statsQuery)
    ->whereIn('boxes.status', ['full', 'partial'])
    ->selectRaw('SUM(boxes.items_remaining) as remaining, SUM(boxes.items_total) as total')
    ->first();

$fillRate = ($fillableBases->total > 0)
    ? round(($fillableBases->remaining / $fillableBases->total) * 100, 1)
    : null;

// Stagnant boxes: sellable boxes with no box_movement in the last 30 days
// Use a subquery for efficiency
$stagnantCount = DB::table('boxes')
    ->when($this->locationType && $this->locationId, fn($q) =>
        $q->where('location_type', $this->locationType)
          ->where('location_id', $this->locationId)
    )
    ->whereIn('status', ['full', 'partial'])
    ->where('items_remaining', '>', 0)
    ->whereNotExists(function ($q) {
        $q->select(DB::raw(1))
          ->from('box_movements')
          ->whereColumn('box_movements.box_id', 'boxes.id')
          ->where('box_movements.moved_at', '>=', now()->subDays(30));
    })
    ->count();
```

Pass to view (replace the existing `'stats' => $stats` line):

```php
'stats'         => $stats,
'fillRate'      => $fillRate,
'stagnantCount' => $stagnantCount,
```

Also add these to the view pass-through (needed for drawer enrichment):
```php
'isOwner' => auth()->user()->isOwner() || auth()->user()->isAdmin(),
```

---

## Part 2 — Rebuild `box-list.blade.php`

Read the current blade first. Then replace the entire contents with the structure below.
Keep all existing `wire:` directives working — do not break the filter controls.

### 2A — Page header

```blade
<div class="dashboard-page-header">
    <div>
        <h1>Boxes</h1>
        <p>Physical inventory — lifecycle, location, and value of every box</p>
    </div>
</div>
```

### 2B — KPI strip (7 cards, replace the existing 5)

Render a responsive CSS grid of cards. Use `display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; margin-bottom:20px`.

**Card 1 — Sellable Boxes**
Main value: `$stats->full_count + $stats->partial_count`
Sub-row: `{{ $stats->full_count }} full · {{ $stats->partial_count }} partial`
Icon: box, accent colour

**Card 2 — Fill Rate**
Main value: `{{ $fillRate ?? '—' }}%`
Sub-row: `{{ number_format($stats->total_items) }} items of {{ number_format($stats->total_capacity) }} capacity`
Colour the main value: green ≥ 70%, amber 40–69%, red < 40%
If `$fillRate === null` show "No stock"

**Card 3 — Cost Value** *(show only if `$isOwner`)*
Main value: `{{ number_format($stats->cost_value) }} RWF`
Sub-row: Retail upside: `{{ number_format($stats->retail_value - $stats->cost_value) }} RWF`
Icon: currency, accent colour

**Card 4 — Retail Value** *(show only if `$isOwner`)*
Main value: `{{ number_format($stats->retail_value) }} RWF`
Sub-row: `{{ number_format($stats->total_items) }} sellable items`
Icon: tag, success colour

**Card 5 — Stagnant Boxes**
Main value: `{{ $stagnantCount }}`
Sub-row: `No movement in 30+ days`
Colour: amber if > 0, success if 0
Badge: "Action needed" (amber) / "All moving" (success)

**Card 6 — Damaged**
Main value: `{{ $stats->damaged_count }}`
Sub-row: `Awaiting disposition`
Colour: red if > 0, success if 0
Clicking this card sets `wire:click="$set('status', 'damaged')"` to filter the table

**Card 7 — Expiring Soon**
Main value: `{{ $stats->expiring_soon }}`
Sub-row: `Within 30 days`
Colour: red if > 0, success if 0
Clicking this card sets `wire:click="$set('expiringOnly', true)"` to activate the filter

### 2C — Filter bar

Keep all existing filter controls (search, location type/id selects, product select,
status select, expiry toggle). Add two new controls:

**Sort by** — a `<select>` bound to `wire:model.live="sortBy"`:
- Options: `received_at` = "Date received", `items_remaining` = "Items remaining",
  `cost_value` = "Cost value", `expiry_date` = "Expiry date", `status` = "Status"

**Sort direction** — a toggle button that calls `wire:click="sortColumn($sortBy)"` or
simply use a `wire:model.live="sortDirection"` select with "Newest first / Oldest first".

### 2D — Table rebuild

Replace the existing table with this column set:

| Column | Content | Owner only | Sortable |
|--------|---------|------------|----------|
| Box Code | Monospace chip, clickable → opens drawer | No | No |
| Product | Product name + category badge | No | No |
| Location | Location type icon + name | No | No |
| Status | Coloured chip (full/partial/empty/damaged) | No | Yes |
| Fill | Mini progress bar + `items_remaining / items_total` | No | Yes (`items_remaining`) |
| Age | Days since `received_at`, colour banded | No | Yes (`received_at`) |
| Expiry | Date + colour chip (red/amber/green/none) | No | Yes (`expiry_date`) |
| Cost Value | `items_remaining × purchase_price` RWF | Yes | Yes (`cost_value`) |
| Batch | `batch_number` if set, else `—` | No | No |
| Action | "View" button → opens drawer | No | No |

**Fill bar implementation** (inline in the `<td>`):
```blade
@php
    $fillPct = $box->items_total > 0
        ? round(($box->items_remaining / $box->items_total) * 100)
        : 0;
    $fillColor = $fillPct >= 60 ? 'var(--success)' : ($fillPct >= 20 ? 'var(--amber)' : 'var(--danger)');
@endphp
<div style="display:flex;align-items:center;gap:8px">
    <div style="flex:1;height:5px;background:var(--surface3);border-radius:3px;min-width:50px">
        <div style="height:100%;width:{{ $fillPct }}%;background:{{ $fillColor }};border-radius:3px"></div>
    </div>
    <span style="font-size:12px;color:var(--text-sub);white-space:nowrap">
        {{ $box->items_remaining }}/{{ $box->items_total }}
    </span>
</div>
```

**Age implementation** (inline in the `<td>`):
```blade
@php
    $ageDays = $box->received_at ? (int) $box->received_at->diffInDays(now()) : null;
    $ageColor = $ageDays === null ? 'var(--text-dim)'
              : ($ageDays <= 30  ? 'var(--success)'
              : ($ageDays <= 90  ? 'var(--amber)'
              :                    'var(--danger)'));
@endphp
<span style="font-size:13px;font-weight:600;color:{{ $ageColor }}">
    {{ $ageDays !== null ? $ageDays . 'd' : '—' }}
</span>
```

**Expiry implementation** (inline in the `<td>`):
```blade
@if($box->expiry_date)
    @php
        $daysToExpiry = (int) now()->diffInDays($box->expiry_date, false);
        $expColor = $daysToExpiry <= 7  ? 'var(--danger)'
                  : ($daysToExpiry <= 30 ? 'var(--amber)' : 'var(--success)');
    @endphp
    <span style="font-size:12px;font-weight:600;color:{{ $expColor }};
                 background:{{ $expColor }}-glow;padding:2px 7px;border-radius:12px">
        {{ $box->expiry_date->format('d M Y') }}
    </span>
@else
    <span style="color:var(--text-dim);font-size:13px">—</span>
@endif
```

**Sortable column header** pattern — copy from `product-list.blade.php` sort headers:
```blade
<th wire:click="sortColumn('received_at')" style="cursor:pointer;...">
    Date Received
    @if($sortBy === 'received_at')
        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
    @endif
</th>
```

**Row click to open drawer**:
Each row should have `wire:click="$dispatch('open-box-detail', {boxId: {{ $box->id }}})"` on
the box code cell and the "View" button. This dispatches to the `BoxDetail` component.

### 2E — Mount the drawer component

At the very bottom of the blade, outside the table:

```blade
<livewire:inventory.boxes.box-detail />
```

---

## Part 3 — Create `BoxDetail.php`

Create `app/Livewire/Inventory/Boxes/BoxDetail.php`:

```php
<?php

namespace App\Livewire\Inventory\Boxes;

use App\Models\Box;
use App\Models\BoxMovement;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class BoxDetail extends Component
{
    public ?int $boxId = null;

    #[On('open-box-detail')]
    public function openFor(int $boxId): void
    {
        $this->boxId = $boxId;
    }

    public function close(): void
    {
        $this->boxId = null;
    }

    public function render()
    {
        if (!$this->boxId) {
            return view('livewire.inventory.boxes.box-detail', [
                'box'          => null,
                'movements'    => [],
                'transfers'    => [],
                'ageDays'      => null,
                'costValue'    => 0,
                'retailValue'  => 0,
                'warehouses'   => collect(),
                'shops'        => collect(),
                'isOwner'      => false,
            ]);
        }

        $box = Box::with([
            'product.category',
            'location',
            'receivedBy',
        ])->findOrFail($this->boxId);

        $isOwner = auth()->user()->isOwner() || auth()->user()->isAdmin();

        // ── Movement timeline ──────────────────────────────────────────────
        $warehouses = Warehouse::pluck('name', 'id');
        $shops      = Shop::pluck('name', 'id');

        $movements = BoxMovement::with('movedBy')
            ->where('box_id', $this->boxId)
            ->orderByDesc('moved_at')
            ->limit(20)
            ->get()
            ->map(fn ($m) => [
                'date'      => $m->moved_at ? $m->moved_at->format('d M Y, H:i') : '—',
                'relative'  => $m->moved_at ? $m->moved_at->diffForHumans() : '—',
                'type'      => $m->movement_type,
                'from'      => $this->locationLabel($m->from_location_type?->value, $m->from_location_id, $warehouses, $shops),
                'to'        => $this->locationLabel($m->to_location_type?->value, $m->to_location_id, $warehouses, $shops),
                'items'     => $m->items_moved ?? 0,
                'moved_by'  => $m->movedBy?->name ?? '—',
                'reason'    => $m->reason ?? '—',
            ])
            ->toArray();

        // ── Transfer history ───────────────────────────────────────────────
        $transfers = DB::table('transfer_boxes')
            ->join('transfers', 'transfer_boxes.transfer_id', '=', 'transfers.id')
            ->leftJoin('warehouses as wh', 'transfers.from_warehouse_id', '=', 'wh.id')
            ->leftJoin('shops as sh', 'transfers.to_shop_id', '=', 'sh.id')
            ->where('transfer_boxes.box_id', $this->boxId)
            ->selectRaw("
                transfers.id,
                transfers.transfer_number,
                transfers.status,
                transfers.has_discrepancy,
                transfers.shipped_at,
                transfers.received_at,
                transfer_boxes.scanned_out_at,
                transfer_boxes.scanned_in_at,
                transfer_boxes.is_received,
                transfer_boxes.is_damaged,
                wh.name as warehouse_name,
                sh.name as shop_name
            ")
            ->orderByDesc('transfers.created_at')
            ->get()
            ->toArray();

        // ── Financial (owner only) ─────────────────────────────────────────
        $costValue   = 0;
        $retailValue = 0;
        if ($isOwner && $box->product) {
            $costValue   = $box->items_remaining * $box->product->purchase_price;
            $retailValue = $box->items_remaining * $box->product->selling_price;
        }

        // ── Age ────────────────────────────────────────────────────────────
        $ageDays = $box->received_at
            ? (int) $box->received_at->diffInDays(now())
            : null;

        return view('livewire.inventory.boxes.box-detail', [
            'box'         => $box,
            'movements'   => $movements,
            'transfers'   => $transfers,
            'ageDays'     => $ageDays,
            'costValue'   => $costValue,
            'retailValue' => $retailValue,
            'warehouses'  => $warehouses,
            'shops'       => $shops,
            'isOwner'     => $isOwner,
        ]);
    }

    private function locationLabel(?string $type, ?int $id, $warehouses, $shops): string
    {
        if (!$type || !$id) return '—';
        return $type === 'warehouse'
            ? ($warehouses[$id] ?? 'Warehouse #' . $id)
            : ($shops[$id]      ?? 'Shop #'      . $id);
    }
}
```

---

## Part 4 — Create `box-detail.blade.php`

Create `resources/views/livewire/inventory/boxes/box-detail.blade.php`.

Match the exact drawer pattern from `product-detail.blade.php` — the outer
backdrop + right panel structure, the close button, and the scroll behaviour.
The drawer should open when `$box` is not null and close when it is null.

### Section layout inside the drawer

**Header** — box code in large monospace font, status chip, product name.
A close button (×) top right calling `wire:click="close"`.

**Identity grid** — 2-column grid of label+value pairs:
- Product / `$box->product->name`
- Category / `$box->product->category->name ?? '—'`
- SKU / `$box->product->sku`
- Location / location type icon + `$box->location->name ?? '—'`
- Received by / `$box->receivedBy->name ?? '—'`
- Received at / `$box->received_at?->format('d M Y, H:i') ?? '—'`
- Batch number / `$box->batch_number ?? '—'`
- Expiry date / date with colour (red/amber/green) or '—'
- Age / `$ageDays` days with colour banding (same logic as table)

**Fill bar** — prominent horizontal bar: `items_remaining / items_total`,
large numbers on either side, percentage in the centre. Same colour logic as table.

**Financial block** — owner only, styled card:
```
Cost value:   X,XXX RWF     (items_remaining × purchase_price)
Retail value: X,XXX RWF     (items_remaining × selling_price)
Gross upside: X,XXX RWF     (retail - cost)
```

**Movement timeline** — section label "Movement History".
Render as a vertical timeline list. Each entry:
- Relative date on the left (e.g. "3 days ago")
- Movement type chip: `received` = accent, `transfer` = blue, `consumption` = amber, `damage` = red
- From → To (show `—` if null, i.e. initial receipt has no "from")
- Items moved count
- Moved by name + reason (smaller, `var(--text-sub)`)

If `$movements` is empty: "No movement records found."

**Transfer history** — section label "Transfer History".
Render as a compact list. Each entry:
- Transfer number (bold)
- Route: warehouse → shop
- Status chip
- Discrepancy badge (red "!" if `has_discrepancy`)
- Scanned out / scanned in timestamps
- `is_damaged` warning if true

If `$transfers` is empty: "This box has not been part of any transfer."

**Damage notes** — only shown if `$box->damage_notes`:
```blade
@if($box->damage_notes)
<div style="background:var(--danger-glow);border:1px solid var(--danger);
            border-radius:var(--rsm);padding:12px 16px;margin-top:16px">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.7px;color:var(--danger);margin-bottom:6px">
        Damage Notes
    </div>
    <p style="font-size:13px;color:var(--text);margin:0">{{ $box->damage_notes }}</p>
</div>
@endif
```

---

## Part 5 — Verification

```bash
php artisan view:clear
php artisan cache:clear
php artisan view:cache 2>&1 | grep -i "error\|exception" | head -20
```

Verify the new component is registered (Livewire auto-discovers it if
the class is in `app/Livewire/Inventory/Boxes/BoxDetail.php`):

```bash
php artisan livewire:discover 2>&1
```

Then open `/owner/boxes` and confirm:
1. The 7 KPI cards render with correct values
2. Clicking any box row opens the drawer
3. The movement timeline and transfer history show correct data
4. Sorting the table by each column works
5. The fill bar renders correctly in every row
6. The financial columns are hidden for non-owner users

---

## Summary

| What | Files |
|------|-------|
| Sortable table + enriched stats | `BoxList.php` |
| 7 KPI cards + new table columns + drawer trigger | `box-list.blade.php` |
| Box lifecycle data computation | `BoxDetail.php` (new) |
| Full chain-of-custody drawer | `box-detail.blade.php` (new) |
