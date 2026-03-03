<div>

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
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);
              padding:12px 14px;margin-bottom:12px">
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">

      {{-- Search --}}
      <div style="flex:1;min-width:180px">
        <div style="font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                    color:var(--text-sub);margin-bottom:4px">Search</div>
        <div style="position:relative">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
               viewBox="0 0 24 24"
               style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:var(--text-dim)">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input wire:model.live.debounce.300ms="search"
                 type="text" placeholder="Name, SKU, barcode..."
                 style="width:100%;padding:6px 8px 6px 28px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:12px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
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
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">

    {{-- Count / period strip --}}
    <div style="padding:9px 14px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px">
      <span style="font-size:12px;color:var(--text-sub)">
        {{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }}
        @if($search) &mdash; matching &ldquo;{{ $search }}&rdquo; @endif
      </span>
      @if($isOwner)
        <span style="font-size:11px;color:var(--text-dim)">
          Showing revenue &amp; sales for: <strong>{{ $periodLabel }}</strong>
        </span>
      @endif
    </div>

    {{-- Responsive wrapper --}}
    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
      <table style="width:100%;border-collapse:collapse;min-width:{{ $isOwner ? '860px' : '600px' }}">
        <thead>
          <tr style="background:var(--surface2)">

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
                        text-transform:uppercase;color:var(--text-sub);text-align:left;white-space:nowrap">
              Category
            </th>

            <th style="padding:9px 12px;font-size:10px;font-weight:700;letter-spacing:.5px;
                        text-transform:uppercase;color:var(--text-sub);text-align:right;white-space:nowrap">
              Stock{{ $isOwner ? ' (all loc.)' : '' }}
            </th>

            @if($isOwner)
            <th style="padding:9px 12px;text-align:right;white-space:nowrap">
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
            <th style="padding:9px 12px;font-size:10px;font-weight:700;letter-spacing:.5px;
                        text-transform:uppercase;color:var(--text-sub);text-align:right;white-space:nowrap">
              Units
            </th>
            @canany(['viewPurchasePrice'])
            <th style="padding:9px 12px;font-size:10px;font-weight:700;letter-spacing:.5px;
                        text-transform:uppercase;color:var(--text-sub);text-align:right;white-space:nowrap">
              Margin
            </th>
            @endcanany
            <th style="padding:9px 12px;font-size:10px;font-weight:700;letter-spacing:.5px;
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
            $isLowStock  = $totalItems <= $product->low_stock_threshold;
            $isZeroStock = $totalItems === 0;
            $revenue     = $sales ? (int)$sales->revenue    : 0;
            $units       = $sales ? (int)$sales->units_sold : 0;
            $hasOverride = $sales && $sales->has_override;
            $marginPct   = ($product->selling_price > 0 && $product->purchase_price > 0)
              ? round(($product->selling_price - $product->purchase_price) / $product->selling_price * 100, 1)
              : null;
          @endphp
          <tr style="border-top:1px solid var(--border);cursor:pointer"
              onmouseover="this.style.background='var(--surface2)'"
              onmouseout="this.style.background='transparent'"
              wire:click="openDetail({{ $product->id }})"
          >

            {{-- Product --}}
            <td style="padding:10px 12px">
              <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $product->name }}</div>
              <div style="font-size:11px;font-family:var(--mono);color:var(--text-dim);margin-top:1px">
                {{ $product->sku }}
              </div>
            </td>

            {{-- Category --}}
            <td style="padding:10px 12px">
              <span style="font-size:11px;font-weight:600;padding:2px 7px;border-radius:10px;
                           background:var(--accent-dim);color:var(--accent);white-space:nowrap">
                {{ $product->category->name ?? '--' }}
              </span>
            </td>

            {{-- Stock --}}
            <td style="padding:10px 12px;text-align:right">
              <div style="font-size:13px;font-weight:700;font-family:var(--mono);
                           color:{{ $isZeroStock ? 'var(--red)' : ($isLowStock ? 'var(--amber)' : 'var(--text)') }}">
                {{ number_format($totalItems) }}
              </div>
              @if($isOwner && $stock)
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px;white-space:nowrap">
                  {{ number_format($stock->warehouse_items) }}wh &middot; {{ number_format($stock->shop_items) }}sh
                </div>
              @endif
              @if($isZeroStock)
                <div style="font-size:9px;font-weight:700;color:var(--red);white-space:nowrap">OUT</div>
              @elseif($isLowStock)
                <div style="font-size:9px;font-weight:700;color:var(--amber);white-space:nowrap">LOW</div>
              @endif
            </td>

            @if($isOwner)
            {{-- Revenue --}}
            <td style="padding:10px 12px;text-align:right">
              <div style="font-size:12px;font-weight:700;font-family:var(--mono);
                           color:{{ $revenue > 0 ? 'var(--text)' : 'var(--text-dim)' }}">
                @if($revenue > 0)
                  {{ $revenue >= 1000000 ? number_format($revenue/1000000,1).'M' : number_format($revenue/1000,0).'K' }}
                @else
                  --
                @endif
              </div>
            </td>

            {{-- Units --}}
            <td style="padding:10px 12px;text-align:right">
              <div style="font-size:12px;font-family:var(--mono);
                           color:{{ $units > 0 ? 'var(--text-sub)' : 'var(--text-dim)' }}">
                {{ $units > 0 ? number_format($units) : '--' }}
              </div>
            </td>

            {{-- Margin --}}
            @canany(['viewPurchasePrice'])
            <td style="padding:10px 12px;text-align:right">
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
            <td style="padding:10px 12px;text-align:center">
              @if($hasOverride)
                <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:8px;
                             background:var(--pink-dim);color:var(--pink);white-space:nowrap">&#9888; Yes</span>
              @else
                <span style="color:var(--text-dim);font-size:11px">--</span>
              @endif
            </td>
            @endif

            {{-- Status --}}
            <td style="padding:10px 12px;text-align:center">
              <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;white-space:nowrap;
                           background:{{ $product->is_active ? 'var(--green-dim)' : 'var(--surface2)' }};
                           color:{{ $product->is_active ? 'var(--green)' : 'var(--text-dim)' }}">
                {{ $product->is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>

            {{-- Actions --}}
            <td style="padding:10px 12px;text-align:right" wire:click.stop>
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
            <td colspan="{{ $isOwner ? 9 : 5 }}"
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

</div>