<div>
<style>
/* Product list — tables stay tables: every column is kept and the table
   scrolls sideways inside its card on narrow screens (min-width on .pl-table) */
.pl-table th, .pl-table td { white-space:nowrap; }
.pl-table td[colspan] { white-space:normal; }
@media(max-width:768px) {
  /* Tighten cell padding */
  .pl-table td, .pl-table th { padding:8px 10px !important; }
  /* Filter bar: stack search full-width */
  .pl-filters { flex-direction:column; align-items:stretch !important; }
  .pl-filters > div { min-width:0 !important; }
}
@media(max-width:640px) {
  /* Keep row buttons compact (global mobile CSS inflates every a/button to 44px) */
  .pl-td-act a, .pl-td-act button { min-height:28px !important; min-width:0 !important; padding:4px 10px !important; }
  .pl-table thead th button { min-height:0 !important; min-width:0 !important; padding:0 !important; }
  /* Don't let the product name column squash to a sliver (SKU was breaking per letter) */
  .pl-table tbody td:first-child { min-width:170px; }
  .pl-table tbody td:first-child > div:last-child { white-space:nowrap; }
}

/* Search suggestions dropdown */
.pl-suggest-panel {
  position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:30;
  background:var(--surface);border-radius:var(--rsm);box-shadow:var(--shadow-card-hover);
  max-height:320px;overflow-y:auto;
}
.pl-suggest-item {
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:9px 14px;cursor:pointer;border-bottom:1px solid var(--border);transition:background var(--tr);
}
.pl-suggest-item:last-child { border-bottom:none; }
.pl-suggest-item:hover { background:var(--surface2); }
.pl-suggest-main { font-size:13px;font-weight:700;color:var(--text); }
.pl-suggest-sub { display:flex;align-items:center;gap:6px;margin-top:3px; }
.pl-suggest-stat { font-size:11px;font-weight:600;color:var(--text-dim);white-space:nowrap;flex-shrink:0; }
.pl-suggest-empty { padding:14px;text-align:center;font-size:12px;color:var(--text-dim); }

/* Row selection + "Apply packs" */
.pl-chk { width:16px;height:16px;accent-color:var(--accent);cursor:pointer;vertical-align:middle }
.pl-selbar { display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-size:12px;font-weight:600;color:var(--text) }
.pl-btn { padding:6px 12px;border-radius:var(--rx);font-size:12px;font-weight:700;border:none;cursor:pointer;
          display:inline-flex;align-items:center;gap:5px;white-space:nowrap }
.pl-btn-primary { background:var(--accent);color:#fff }
.pl-btn-ghost   { background:var(--surface2);color:var(--text-sub) }
.pl-btn:disabled { opacity:.5;cursor:not-allowed }

.pl-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px) }
.pl-drawer { position:fixed;top:0;right:0;bottom:0;z-index:401;width:560px;max-width:100vw;background:var(--surface);
             border-left:1px solid var(--border);box-shadow:-8px 0 40px rgba(26,31,54,.14);display:flex;flex-direction:column;
             transform:translateX(100%);transition:transform .22s cubic-bezier(.4,0,.2,1) }
.pl-drawer.open { transform:translateX(0) }
.pl-drawer:not(.open) { box-shadow:none }
.pl-drawer-head { display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0 }
.pl-drawer-title { font-size:18px;font-weight:800;color:var(--text) }
.pl-drawer-sub { font-size:12px;color:var(--text-dim);margin-top:2px }
.pl-drawer-close { width:32px;height:32px;border-radius:8px;border:none;background:var(--surface2);color:var(--text-sub);cursor:pointer;
                   display:flex;align-items:center;justify-content:center }
.pl-drawer-body { flex:1;overflow-y:auto;padding:20px 22px }
.pl-drawer-foot { padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;flex-shrink:0 }
.pl-drawer-foot .pl-btn { flex:1;justify-content:center;padding:10px 14px;font-size:13px }
.pl-field { margin-bottom:18px }
.pl-field-label { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:8px }
.pl-hint { font-size:11px;color:var(--text-dim);margin-top:6px;line-height:1.5 }
.pl-chips { display:flex;flex-wrap:wrap;gap:6px }
.pl-chip { display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:20px;border:1.5px solid var(--border);
           font-size:12px;font-weight:600;color:var(--text-sub);cursor:pointer;user-select:none }
.pl-chip input { display:none }
.pl-chip.on { border-color:var(--accent);background:var(--accent-dim);color:var(--accent) }
.pl-seg { display:inline-flex;background:var(--surface2);border-radius:9px;padding:3px;gap:2px;flex-wrap:wrap }
.pl-seg label { padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;color:var(--text-sub);cursor:pointer }
.pl-seg input { display:none }
.pl-seg label.on { background:var(--surface);color:var(--text);box-shadow:var(--shadow-card) }
.pl-row2 { display:grid;grid-template-columns:minmax(0,1fr) 110px;gap:8px }
.pl-input { width:100%;padding:8px 11px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;background:var(--surface);color:var(--text);outline:none }
.pl-input:focus { border-color:var(--accent) }
.pl-error { font-size:12px;color:var(--red);margin-top:6px }
.pl-pv { width:100%;border-collapse:collapse;font-size:12px }
.pl-pv th { text-align:left;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-sub);padding:7px 8px;border-bottom:1px solid var(--border) }
.pl-pv td { padding:8px;border-bottom:1px solid var(--border);vertical-align:top;white-space:nowrap }
.pl-pv-add  { color:var(--green);font-weight:600 }
.pl-pv-skip { color:var(--text-dim) }
.pl-pv-warn { color:var(--amber);font-size:11px }
@media(max-width:640px) {
  .pl-drawer { left:0;width:auto }
  .pl-drawer-body { padding:16px }
  .pl-drawer-foot { padding:12px 16px }
}
</style>

  {{-- Flash messages --}}
  @if(session('success'))
    <div style="margin-bottom:12px;padding:10px 14px;border-radius:var(--r);
                background:var(--green-dim);color:var(--green);font-size:13px;font-weight:600;
                display:flex;align-items:center;gap:8px">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div style="margin-bottom:12px;padding:10px 14px;border-radius:var(--r);
                background:var(--red-dim);color:var(--red);font-size:13px;font-weight:600;
                display:flex;align-items:center;gap:8px">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
      {{ session('error') }}
    </div>
  @endif

  {{-- Filters bar --}}
  <div style="background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);
              padding:12px 14px;margin-bottom:12px">
    <div class="pl-filters" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">

      {{-- Search — with a suggestions dropdown on focus, still fully
           typeable (the dropdown just narrows live alongside the filter) --}}
      <div style="flex:1;min-width:180px" x-data="{ suggestOpen: false }" @click.outside="suggestOpen = false">
        <div style="font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                    color:var(--text-sub);margin-bottom:4px">Search</div>
        <div style="position:relative">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
               viewBox="0 0 24 24"
               style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:var(--text-dim)">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input wire:model.live.debounce.300ms="search"
                 type="text" placeholder="Name, SKU, barcode..." autocomplete="off"
                 style="width:100%;padding:6px 8px 6px 28px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:12px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'"
                 @focus="suggestOpen = true"
                 @keydown.escape="suggestOpen = false">

          <div class="pl-suggest-panel" x-show="suggestOpen" x-cloak style="display:none">
            @forelse($suggestions as $sug)
            <div class="pl-suggest-item"
                 wire:click="selectProductSuggestion({{ $sug->id }})"
                 @click="suggestOpen = false"
                 wire:key="pl-sugg-{{ $sug->id }}">
              <div style="min-width:0">
                <div class="pl-suggest-main">{{ $sug->name }}</div>
                <div class="pl-suggest-sub">
                  @if($sug->category_name)
                  <span style="font-size:10px;font-weight:600;padding:1px 6px;border-radius:8px;
                               background:var(--accent-dim);color:var(--accent)">{{ $sug->category_name }}</span>
                  @endif
                  @if($sug->sku)
                  <span style="font-family:var(--mono);font-size:10px;color:var(--text-dim)">{{ $sug->sku }}</span>
                  @endif
                </div>
              </div>
              <span class="pl-suggest-stat">{{ number_format($sug->total_boxes) }} {{ Str::plural('box', $sug->total_boxes) }}</span>
            </div>
            @empty
            <div class="pl-suggest-empty">No products match{{ $search ? " \"{$search}\"" : '' }}</div>
            @endforelse
          </div>
        </div>
      </div>

      {{-- Category --}}
      <div style="min-width:150px">
        <div style="font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                    color:var(--text-sub);margin-bottom:4px">Category</div>
        <select wire:model.live="categoryId"
                style="padding:6px 8px;border:1px solid var(--border);border-radius:var(--rx);
                       font-size:12px;background:var(--surface);color:var(--text);
                       outline:none;cursor:pointer;width:100%">
          <option value="">All categories</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>

      {{-- Toggle buttons --}}
      <div style="display:flex;gap:6px;align-items:flex-end;padding-bottom:1px;flex-wrap:wrap">
        <button wire:click="$toggle('activeOnly')"
                style="padding:6px 11px;border-radius:var(--rx);font-size:12px;font-weight:600;
                       cursor:pointer;border:1px solid var(--border);
                       background:{{ $activeOnly ? 'var(--accent)' : 'var(--surface)' }};
                       color:{{ $activeOnly ? '#fff' : 'var(--text-sub)' }}">
          Active only
        </button>
        <button wire:click="$toggle('lowStockOnly')"
                style="padding:6px 11px;border-radius:var(--rx);font-size:12px;font-weight:600;
                       cursor:pointer;border:1px solid {{ $lowStockOnly ? 'var(--amber)' : 'var(--border)' }};
                       background:{{ $lowStockOnly ? 'var(--amber)' : 'var(--surface)' }};
                       color:{{ $lowStockOnly ? '#fff' : 'var(--text-sub)' }}">
          &#9888; Low stock{{ $isOwner ? ' (all)' : '' }}
        </button>
        @if($search || $categoryId || !$activeOnly || $lowStockOnly)
          <button wire:click="clearFilters"
                  style="padding:6px 11px;border-radius:var(--rx);font-size:12px;font-weight:600;
                         cursor:pointer;border:1px solid var(--border);background:var(--surface2);
                         color:var(--text-sub)">
            Clear
          </button>
        @endif
      </div>

      @if($isOwner)
      <div style="margin-left:auto">
        <a href="{{ route('owner.products.create') }}"
           style="padding:6px 13px;border-radius:var(--rx);font-size:12px;font-weight:700;
                  background:var(--accent);color:#fff;text-decoration:none;
                  display:inline-flex;align-items:center;gap:5px;white-space:nowrap">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Add Product
        </a>
      </div>
      @endif

    </div>
  </div>

  {{-- Table card --}}
  <div style="background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);overflow:hidden">

    {{-- Count / period strip --}}
    <div style="padding:9px 14px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px">
      <span style="font-size:12px;color:var(--text-sub)">
        {{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }}
        @if($search) &mdash; matching &ldquo;{{ $search }}&rdquo; @endif
      </span>
      @if($isOwner && count($selected))
        <div class="pl-selbar">
          {{ count($selected) }} selected
          <button type="button" class="pl-btn pl-btn-primary" wire:click="openPacks">Apply packs</button>
          <button type="button" class="pl-btn pl-btn-ghost" wire:click="clearSelection">Clear</button>
        </div>
      @elseif($isOwner)
        <span style="font-size:11px;color:var(--text-dim)">
          Showing revenue &amp; sales for: <strong>{{ $periodLabel }}</strong>
        </span>
      @endif
    </div>

    {{-- Responsive wrapper --}}
    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
      <table class="pl-table" style="width:max-content;border-collapse:collapse;min-width:max(100%, {{ $isOwner ? '860px' : '600px' }})">
        <thead>
          <tr style="background:var(--bg)">

            @if($isOwner)
              @php $pageIds = collect($products->items())->pluck('id')->map(fn ($id) => (string) $id)->all(); @endphp
              <th style="padding:9px 4px 9px 14px;width:30px">
                <input type="checkbox" class="pl-chk" aria-label="Select every product on this page"
                       @checked($pageIds && ! array_diff($pageIds, array_map('strval', $selected)))
                       wire:click="toggleSelectPage(@js($pageIds))">
              </th>
            @endif

            <th style="padding:9px 12px;text-align:left">
              <button wire:click="sortBy('name')"
                      style="background:none;border:none;cursor:pointer;display:inline-flex;
                             align-items:center;gap:4px;font-size:10px;font-weight:700;
                             letter-spacing:.5px;text-transform:uppercase;color:var(--text-sub)">
                Product
                @if($sortBy === 'name')
                  <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                @endif
              </button>
            </th>

            <th style="padding:9px 12px;font-size:10px;font-weight:700;letter-spacing:.5px;
                        text-transform:uppercase;color:var(--text-sub);text-align:right;white-space:nowrap">
              Stock{{ $isOwner ? ' (all loc.)' : '' }}
            </th>

            <th class="pl-hide-mob" style="padding:9px 12px;font-size:10px;font-weight:700;letter-spacing:.5px;
                        text-transform:uppercase;color:var(--text-sub);text-align:left;white-space:nowrap">
              Category
            </th>

            @if($isOwner)
            <th class="pl-hide-tab" style="padding:9px 12px;text-align:right;white-space:nowrap">
              <button wire:click="sortBy('selling_price')"
                      style="background:none;border:none;cursor:pointer;display:inline-flex;
                             align-items:center;gap:4px;font-size:10px;font-weight:700;
                             letter-spacing:.5px;text-transform:uppercase;color:var(--text-sub)">
                Revenue
                @if($sortBy === 'selling_price')
                  <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                @endif
              </button>
            </th>
            <th class="pl-hide-tab" style="padding:9px 12px;font-size:10px;font-weight:700;letter-spacing:.5px;
                        text-transform:uppercase;color:var(--text-sub);text-align:right;white-space:nowrap">
              Units
            </th>
            @canany(['viewPurchasePrice'])
            <th class="pl-hide-tab" style="padding:9px 12px;font-size:10px;font-weight:700;letter-spacing:.5px;
                        text-transform:uppercase;color:var(--text-sub);text-align:right;white-space:nowrap">
              Margin
            </th>
            @endcanany
            <th class="pl-hide-tab" style="padding:9px 12px;font-size:10px;font-weight:700;letter-spacing:.5px;
                        text-transform:uppercase;color:var(--text-sub);text-align:center;white-space:nowrap">
              Override
            </th>
            @endif

            <th style="padding:9px 12px;font-size:10px;font-weight:700;letter-spacing:.5px;
                        text-transform:uppercase;color:var(--text-sub);text-align:center;white-space:nowrap">
              Status
            </th>

            <th style="padding:9px 12px;font-size:10px;font-weight:700;letter-spacing:.5px;
                        text-transform:uppercase;color:var(--text-sub);text-align:right">
              &nbsp;
            </th>

          </tr>
        </thead>
        <tbody>
          @forelse($products as $product)
          @php
            $stock       = $stockData[$product->id] ?? null;
            $sales       = $salesStats[$product->id] ?? null;
            $totalItems  = $stock ? (int)$stock->total_items : 0;
            $totalBoxes  = $stock ? (int)$stock->total_boxes : 0;
            $isLowStock  = $totalItems <= $product->low_stock_threshold;
            $isZeroStock = $totalItems === 0;
            $revenue     = $sales ? (int)$sales->revenue    : 0;
            $units       = $sales ? (int)$sales->units_sold : 0;
            $hasOverride = $sales && $sales->has_override;
            // Units shown as a box count only when this category is
            // full-box-only (every sale there is a whole box, so the
            // conversion is exact) — otherwise items, since individual
            // sales don't divide cleanly into boxes. Same rule as the
            // Boxes page's Sold column.
            $boxOnlySales = $product->category_id
              ? !$settings->categoryAllowsIndividualSales($product->category_id)
              : false;
            $unitsBoxes  = ($boxOnlySales && $product->items_per_box > 0)
              ? intdiv($units, $product->items_per_box)
              : 0;
            $marginPct   = ($product->selling_price > 0 && $product->purchase_price > 0)
              ? round(($product->selling_price - $product->purchase_price) / $product->selling_price * 100, 1)
              : null;
          @endphp
          <tr style="border-top:1px solid var(--border);cursor:pointer"
              onmouseover="this.style.background='var(--surface2)'"
              onmouseout="this.style.background='transparent'"
              wire:click="openDetail({{ $product->id }})"
          >

            @if($isOwner)
              <td style="padding:10px 4px 10px 14px" onclick="event.stopPropagation()">
                <input type="checkbox" class="pl-chk" value="{{ $product->id }}" wire:model.live="selected"
                       aria-label="Select {{ $product->name }}">
              </td>
            @endif

            {{-- Product --}}
            <td style="padding:10px 12px">
              <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $product->name }}</div>
              <div style="font-size:11px;font-family:var(--mono);color:var(--text-dim);margin-top:1px">
                {{ $product->sku }}
              </div>
            </td>

            {{-- Stock (2nd column: the figure you look for first) --}}
            <td style="padding:10px 12px;text-align:right">
              <div style="font-size:13px;font-weight:700;font-family:var(--mono);
                           color:{{ $isZeroStock ? 'var(--red)' : ($isLowStock ? 'var(--amber)' : 'var(--text)') }}">
                {{ number_format($totalBoxes) }} <span style="font-size:10px;font-weight:600">{{ Str::plural('box', $totalBoxes) }}</span>
              </div>
              <div style="font-size:10px;color:var(--text-dim);margin-top:1px;white-space:nowrap">
                {{ number_format($totalItems) }} items
                @if($isOwner && $stock)
                  &middot; {{ number_format($stock->warehouse_items) }}wh &middot; {{ number_format($stock->shop_items) }}sh
                @endif
              </div>
              @if($isZeroStock)
                <div style="font-size:9px;font-weight:700;color:var(--red);white-space:nowrap">OUT</div>
              @elseif($isLowStock)
                <div style="font-size:9px;font-weight:700;color:var(--amber);white-space:nowrap">LOW</div>
              @endif
            </td>

            {{-- Category --}}
            <td class="pl-hide-mob" style="padding:10px 12px">
              <span style="font-size:11px;font-weight:600;padding:2px 7px;border-radius:10px;
                           background:var(--accent-dim);color:var(--accent);white-space:nowrap">
                {{ $product->category->name ?? '--' }}
              </span>
            </td>

            @if($isOwner)
            {{-- Revenue --}}
            <td class="pl-hide-tab" style="padding:10px 12px;text-align:right">
              <div style="font-size:12px;font-weight:700;font-family:var(--mono);
                           color:{{ $revenue > 0 ? 'var(--text)' : 'var(--text-dim)' }}">
                @if($revenue > 0)
                  {{ number_format($revenue) }}
                @else
                  --
                @endif
              </div>
            </td>

            {{-- Units --}}
            <td class="pl-hide-tab" style="padding:10px 12px;text-align:right">
              <div style="font-size:12px;font-family:var(--mono);
                           color:{{ $units > 0 ? 'var(--text-sub)' : 'var(--text-dim)' }}">
                @if($units === 0)
                  --
                @elseif($boxOnlySales)
                  {{ number_format($unitsBoxes) }} <span style="font-size:10px">{{ Str::plural('box', $unitsBoxes) }}</span>
                @else
                  {{ number_format($units) }}
                @endif
              </div>
            </td>

            {{-- Margin --}}
            @canany(['viewPurchasePrice'])
            <td class="pl-hide-tab" style="padding:10px 12px;text-align:right">
              @if($marginPct !== null)
                <span style="font-size:11px;font-weight:700;padding:2px 6px;border-radius:8px;white-space:nowrap;
                             background:{{ $marginPct >= 20 ? 'var(--green-dim)' : ($marginPct >= 10 ? 'var(--accent-dim)' : 'var(--pink-dim)') }};
                             color:{{ $marginPct >= 20 ? 'var(--green)' : ($marginPct >= 10 ? 'var(--accent)' : 'var(--pink)') }}">
                  {{ $marginPct }}%
                </span>
              @else
                <span style="color:var(--text-dim);font-size:11px">--</span>
              @endif
            </td>
            @endcanany

            {{-- Override --}}
            <td class="pl-hide-tab" style="padding:10px 12px;text-align:center">
              @if($hasOverride)
                <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:8px;
                             background:var(--pink-dim);color:var(--pink);white-space:nowrap">&#9888; Yes</span>
              @else
                <span style="color:var(--text-dim);font-size:11px">--</span>
              @endif
            </td>
            @endif

            {{-- Status --}}
            <td class="pl-td-status" style="padding:10px 12px;text-align:center">
              <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;white-space:nowrap;
                           background:{{ $product->is_active ? 'var(--green-dim)' : 'var(--surface2)' }};
                           color:{{ $product->is_active ? 'var(--green)' : 'var(--text-dim)' }}">
                {{ $product->is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>

            {{-- Actions --}}
            <td class="pl-td-act" style="padding:10px 12px;text-align:right" wire:click.stop>
              <div style="display:flex;justify-content:flex-end;align-items:center;gap:5px;flex-wrap:nowrap">
                @if($isOwner)
                  {{-- Edit button - navigates to edit page --}}
                  <a href="{{ route('owner.products.edit', $product->id) }}"
                     style="font-size:11px;font-weight:600;padding:4px 10px;border-radius:var(--rx);
                            background:var(--surface2);color:var(--text-sub);text-decoration:none;
                            border:1px solid var(--border);white-space:nowrap;display:inline-flex;
                            align-items:center;gap:4px">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Edit
                  </a>

                  {{-- Toggle active/inactive --}}
                  <button wire:click="toggleActive({{ $product->id }})"
                          wire:loading.attr="disabled"
                          wire:target="toggleActive({{ $product->id }})"
                          style="font-size:11px;font-weight:600;padding:4px 10px;border-radius:var(--rx);
                                 border:1px solid var(--border);cursor:pointer;white-space:nowrap;
                                 background:{{ $product->is_active ? 'var(--red-dim)' : 'var(--green-dim)' }};
                                 color:{{ $product->is_active ? 'var(--red)' : 'var(--green)' }}">
                    <span wire:loading.remove wire:target="toggleActive({{ $product->id }})">
                      {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                    </span>
                    <span wire:loading wire:target="toggleActive({{ $product->id }})">...</span>
                  </button>
                @endif

                {{-- Detail button --}}
                <button wire:click="openDetail({{ $product->id }})"
                        style="font-size:11px;font-weight:600;padding:4px 10px;border-radius:var(--rx);
                               background:var(--accent-dim);color:var(--accent);border:none;
                               cursor:pointer;white-space:nowrap">
                  Detail
                </button>
              </div>
            </td>

          </tr>
          @empty
          <tr>
            <td colspan="{{ $isOwner ? 10 : 5 }}"
                style="padding:36px;text-align:center;color:var(--text-dim);font-size:13px">
              No products found
              @if($search || $categoryId || $lowStockOnly)
                &mdash; try adjusting your filters
              @endif
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($products->hasPages())
      <div style="padding:10px 14px;border-top:1px solid var(--border)">
        {{ $products->links() }}
      </div>
    @endif

  </div>{{-- /table card --}}

  {{-- ═══ Apply packs drawer (owner) ═══ --}}
  @if($isOwner)
    @if($showPacks)
      <div class="pl-overlay" wire:click="closePacks"></div>
    @endif
    <div class="pl-drawer {{ $showPacks ? 'open' : '' }}" aria-hidden="{{ $showPacks ? 'false' : 'true' }}">
      <div class="pl-drawer-head">
        <div>
          <div class="pl-drawer-title">Apply packs</div>
          <div class="pl-drawer-sub">{{ count($selected) }} {{ count($selected) === 1 ? 'product' : 'products' }} selected · stock stays counted in pieces</div>
        </div>
        <button type="button" class="pl-drawer-close" wire:click="closePacks" aria-label="Close">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <div class="pl-drawer-body">
        @if($showPacks)
          <div class="pl-field">
            <span class="pl-field-label">Packs</span>
            <div class="pl-chips">
              @foreach(\App\Models\ProductSellUnit::PRESETS as $key => $preset)
                <label class="pl-chip {{ in_array($key, $packKeys, true) ? 'on' : '' }}">
                  <input type="checkbox" value="{{ $key }}" wire:model.live="packKeys">
                  {{ $preset[0] }} <span style="font-family:var(--mono);opacity:.7">{{ $preset[1] ?? '½' }}</span>
                </label>
              @endforeach
            </div>
            <div class="pl-hint">Half box is half of each product's own box (only for even box sizes).</div>
            @error('packKeys') <div class="pl-error">{{ $message }}</div> @enderror
          </div>

          <div class="pl-field">
            <span class="pl-field-label">Other pack (optional)</span>
            <div class="pl-row2">
              <input type="text" class="pl-input" wire:model.live.debounce.400ms="customName" placeholder="Pack of {{ (int) $customSize >= 2 ? (int) $customSize : 'N' }}" aria-label="Pack name">
              <x-number-input wire:model.live.debounce.400ms="customSize" class="pl-input" placeholder="Pieces" aria-label="Pieces in the pack" />
            </div>
            @error('customSize') <div class="pl-error">{{ $message }}</div> @enderror
          </div>

          <div class="pl-field">
            <span class="pl-field-label">Price</span>
            <div class="pl-seg">
              <label class="{{ $packPrice === 'piece' ? 'on' : '' }}"><input type="radio" value="piece" wire:model.live="packPrice">Pieces × piece price</label>
              <label class="{{ $packPrice === 'discount' ? 'on' : '' }}"><input type="radio" value="discount" wire:model.live="packPrice">With a discount</label>
            </div>
            @if($packPrice === 'discount')
              <div style="display:flex;align-items:center;gap:8px;margin-top:10px;max-width:180px">
                <x-number-input wire:model.live.debounce.400ms="packDiscount" decimals="1" class="pl-input" aria-label="Discount percent" />
                <span style="font-size:13px;color:var(--text-sub)">% off</span>
              </div>
              @error('packDiscount') <div class="pl-error">{{ $message }}</div> @enderror
            @endif
            <div class="pl-hint">Never cheaper per piece than buying the box, never dearer than single pieces. Prices outside that are moved to the limit.</div>
          </div>

          <div class="pl-field">
            <span class="pl-field-label">If a product already has a pack of that size</span>
            <div class="pl-seg">
              <label class="{{ $packExisting === 'skip' ? 'on' : '' }}"><input type="radio" value="skip" wire:model.live="packExisting">Keep it</label>
              <label class="{{ $packExisting === 'replace' ? 'on' : '' }}"><input type="radio" value="replace" wire:model.live="packExisting">Replace name and price</label>
            </div>
          </div>

          <div class="pl-field">
            <span class="pl-field-label">Sell single pieces</span>
            <div class="pl-seg">
              <label class="{{ $packSingle === 'keep' ? 'on' : '' }}"><input type="radio" value="keep" wire:model.live="packSingle">Leave as is</label>
              <label class="{{ $packSingle === 'on' ? 'on' : '' }}"><input type="radio" value="on" wire:model.live="packSingle">On</label>
              <label class="{{ $packSingle === 'off' ? 'on' : '' }}"><input type="radio" value="off" wire:model.live="packSingle">Off (packs only)</label>
            </div>
          </div>

          @php $preview = $this->packPreview; @endphp
          @if($preview)
            <div class="pl-field" style="margin-bottom:0">
              <span class="pl-field-label">Preview</span>
              @if(collect($preview)->contains('sells_loose', false))
                <div class="pl-hint" style="margin:-4px 0 8px">Box-only category: the packs are saved, but the POS offers them only once the category is ticked in Settings → Sales.</div>
              @endif
              <div class="m-scroll" style="overflow-x:auto">
                <table class="pl-pv">
                  <thead><tr><th>Product</th><th>Packs</th></tr></thead>
                  <tbody>
                    @foreach($preview as $row)
                      <tr wire:key="pv-{{ $row['id'] }}">
                        <td>
                          <div style="font-weight:600;color:var(--text)">{{ $row['name'] }}</div>
                          <div style="font-size:11px;color:var(--text-dim)">Box of {{ $row['items_per_box'] }}</div>
                          @unless($row['sells_loose'])
                            <div class="pl-pv-warn" title="Not offered in the POS until the category is ticked in Settings → Sales">Box-only category</div>
                          @endunless
                        </td>
                        <td>
                          @foreach($row['add'] as $a)
                            <div class="pl-pv-add">
                              {{ $a['replaces'] ? 'Replace' : 'Add' }} {{ $a['name'] }} ({{ $a['size'] }}) · {{ number_format($a['price']) }} RWF
                            </div>
                          @endforeach
                          @foreach($row['skip'] as $k)
                            <div class="pl-pv-skip">Skip {{ $k['name'] }}: {{ $k['reason'] }}</div>
                          @endforeach
                          @if(! $row['add'] && ! $row['skip'])
                            <div class="pl-pv-skip">No packs chosen</div>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endif
        @endif
      </div>

      <div class="pl-drawer-foot">
        <button type="button" class="pl-btn pl-btn-ghost" wire:click="closePacks">Cancel</button>
        <button type="button" class="pl-btn pl-btn-primary" wire:click="applyPacks" wire:loading.attr="disabled" wire:target="applyPacks">Apply</button>
      </div>
    </div>
  @endif

</div>