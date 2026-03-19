# Dashboard — Fill Whitespace in Transfer Status & Stock Distribution
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read DASHBOARD_CARD_FILL.md and follow every step in order."

---

## Read these files first

```bash
cat app/Livewire/Dashboard/TransferStatus.php
cat resources/views/livewire/dashboard/transfer-status.blade.php
cat app/Livewire/Dashboard/StockDistribution.php
cat resources/views/livewire/dashboard/stock-distribution.blade.php
```

---

## What we are adding

**Transfer Status card** — show the `$recentTransfers` mini list already
loaded in the component. If it is empty, show a compact "No active
transfers" placeholder.

**Stock Distribution card** — add a per-location stats row below the
existing legend showing full boxes, partial boxes, and item count per
location. Requires a new computed property in the PHP component.

---

## STEP 1 — Confirm recentTransfers exists in PHP component

**File:** `app/Livewire/Dashboard/TransferStatus.php`

Check if `$recentTransfers` property and its loading code already exist.
If NOT, add the property after the existing properties:

```php
public array $recentTransfers = [];
```

And at the end of `loadData()`, add:

```php
$this->recentTransfers = \App\Models\Transfer::with(['fromWarehouse', 'toShop'])
    ->whereIn('status', ['pending', 'approved', 'in_transit', 'delivered'])
    ->orderByDesc('created_at')
    ->limit(3)
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

## STEP 2 — Add recent transfers to Transfer Status blade

**File:** `resources/views/livewire/dashboard/transfer-status.blade.php`

Find the closing `</div>` of the last status row (Delivered Today row).
Insert this block immediately after it, before the card's closing `</div>`:

```blade
{{-- Recent active transfers --}}
<div style="border-top:1px solid var(--border)">
    <div style="padding:10px 20px 4px;font-size:10px;font-weight:700;
                letter-spacing:.5px;text-transform:uppercase;
                color:var(--text-dim)">
        Recent Active
    </div>

    @forelse($recentTransfers as $t)
    @php
        $sc = match($t['status']) {
            'pending'    => ['bg'=>'var(--amber-dim)',  'c'=>'var(--amber)'],
            'approved'   => ['bg'=>'var(--accent-dim)', 'c'=>'var(--accent)'],
            'in_transit' => ['bg'=>'var(--violet-dim)', 'c'=>'var(--violet)'],
            'delivered'  => ['bg'=>'var(--green-dim)',  'c'=>'var(--green)'],
            default      => ['bg'=>'var(--surface2)',   'c'=>'var(--text-dim)'],
        };
    @endphp
    <a href="{{ route('owner.transfers.show', $t['id']) }}"
       style="display:flex;align-items:center;justify-content:space-between;
              padding:10px 20px;text-decoration:none;gap:10px;
              border-top:1px solid var(--border);
              transition:background var(--tr)"
       onmouseover="this.style.background='var(--surface2)'"
       onmouseout="this.style.background='transparent'">
        <div style="min-width:0;flex:1">
            <div style="font-size:12px;font-weight:600;color:var(--text);
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                {{ $t['from'] }}
                <span style="color:var(--text-dim)"> → </span>
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
            {{ ucfirst(str_replace('_',' ',$t['status'])) }}
        </span>
    </a>
    @empty
    <div style="padding:12px 20px;border-top:1px solid var(--border);
                font-size:12px;color:var(--text-dim);text-align:center">
        No active transfers right now
    </div>
    @endforelse
</div>
```

---

## STEP 3 — Add location detail stats to StockDistribution PHP

**File:** `app/Livewire/Dashboard/StockDistribution.php`

Add a new computed property after `getDamagedBoxesProperty()`:

```php
public function getLocationStatsProperty(): array
{
    return \Illuminate\Support\Facades\DB::table('boxes')
        ->join('warehouses', function ($j) {
            $j->on('boxes.location_id', '=', 'warehouses.id')
              ->where('boxes.location_type', '=', 'warehouse');
        })
        ->whereIn('boxes.status', ['full', 'partial'])
        ->where('boxes.items_remaining', '>', 0)
        ->selectRaw("
            warehouses.name as location_name,
            'warehouse' as location_type,
            SUM(CASE WHEN boxes.status = 'full' THEN 1 ELSE 0 END) as full_boxes,
            SUM(CASE WHEN boxes.status = 'partial' THEN 1 ELSE 0 END) as partial_boxes,
            SUM(boxes.items_remaining) as total_items
        ")
        ->groupBy('warehouses.name')
        ->unionAll(
            \Illuminate\Support\Facades\DB::table('boxes')
                ->join('shops', function ($j) {
                    $j->on('boxes.location_id', '=', 'shops.id')
                      ->where('boxes.location_type', '=', 'shop');
                })
                ->whereIn('boxes.status', ['full', 'partial'])
                ->where('boxes.items_remaining', '>', 0)
                ->selectRaw("
                    shops.name as location_name,
                    'shop' as location_type,
                    SUM(CASE WHEN boxes.status = 'full' THEN 1 ELSE 0 END) as full_boxes,
                    SUM(CASE WHEN boxes.status = 'partial' THEN 1 ELSE 0 END) as partial_boxes,
                    SUM(boxes.items_remaining) as total_items
                ")
                ->groupBy('shops.name')
        )
        ->orderByDesc('total_items')
        ->get()
        ->map(fn($r) => [
            'name'         => $r->location_name,
            'type'         => $r->location_type,
            'full_boxes'   => (int) $r->full_boxes,
            'partial_boxes'=> (int) $r->partial_boxes,
            'total_items'  => (int) $r->total_items,
        ])
        ->toArray();
}
```

---

## STEP 4 — Add location stats to Stock Distribution blade

**File:** `resources/views/livewire/dashboard/stock-distribution.blade.php`

Find the bottom of the blade — after the existing legend rows and the
damaged boxes line. Add this block before the closing `</div>` of the
card:

```blade
{{-- Per-location detail stats --}}
@if(count($this->locationStats) > 0)
<div style="border-top:1px solid var(--border);margin-top:12px;padding-top:12px">
    <div style="font-size:10px;font-weight:700;letter-spacing:.5px;
                text-transform:uppercase;color:var(--text-dim);
                padding:0 4px;margin-bottom:8px">
        Location Breakdown
    </div>
    @foreach($this->locationStats as $loc)
    <div style="padding:8px 4px;border-top:1px solid var(--border)">
        <div style="display:flex;align-items:center;justify-content:space-between;
                    margin-bottom:5px">
            <div style="display:flex;align-items:center;gap:6px;min-width:0">
                <div style="width:8px;height:8px;border-radius:50%;flex-shrink:0;
                            background:{{ $loc['type'] === 'warehouse' ? '#3b6fd4' : '#0e9e86' }}">
                </div>
                <span style="font-size:12px;font-weight:600;color:var(--text);
                             white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
                             max-width:130px">
                    {{ $loc['name'] }}
                </span>
                <span style="font-size:10px;font-weight:600;padding:1px 6px;
                             border-radius:10px;flex-shrink:0;
                             background:{{ $loc['type'] === 'warehouse' ? 'var(--accent-dim)' : 'var(--green-dim)' }};
                             color:{{ $loc['type'] === 'warehouse' ? 'var(--accent)' : 'var(--green)' }}">
                    {{ ucfirst($loc['type']) }}
                </span>
            </div>
            <span style="font-size:12px;font-weight:700;font-family:var(--mono);
                         color:var(--text);flex-shrink:0">
                {{ number_format($loc['total_items']) }} items
            </span>
        </div>
        <div style="display:flex;gap:10px">
            <span style="font-size:11px;color:var(--text-dim)">
                <span style="font-weight:700;color:var(--green)">
                    {{ $loc['full_boxes'] }}
                </span> full
            </span>
            <span style="font-size:11px;color:var(--text-dim)">
                <span style="font-weight:700;color:var(--amber)">
                    {{ $loc['partial_boxes'] }}
                </span> partial
            </span>
        </div>
    </div>
    @endforeach
</div>
@endif
```

---

## STEP 5 — Clear caches

```bash
php artisan view:clear && php artisan cache:clear
```

---

## Do NOT touch

- The donut chart canvas or its Alpine.js init code
- The existing legend rows
- Any other dashboard components

---

## Verification

1. Transfer Status card shows 4 status rows then "Recent Active" section
   with up to 3 transfers (from → to, age, status badge)
2. If no active transfers: shows "No active transfers right now"
3. Stock Distribution card shows donut + legend + "Location Breakdown"
   section with full/partial box counts and item totals per location
4. Both cards are visually taller and better balanced with Live Activity
5. All three cards align at the top with natural height
