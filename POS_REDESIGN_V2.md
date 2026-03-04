# SmartInventory — Point of Sale Modern Redesign
## Claude Code Instructions

> Drop in project root, then tell Claude Code:
> "Read POS_REDESIGN_V2.md and follow every step in order."

---

## Critical Rules
- **DO NOT touch `app/Livewire/Shop/Sales/PointOfSale.php`** — preserve ALL logic
- **ONLY rewrite** `resources/views/livewire/shop/sales/point-of-sale.blade.php`
- Every `wire:` binding must match the EXACT method/property names from the PHP file
- Design system: CSS vars `--accent --surface --surface2 --surface3 --border --border-hi --text --text-sub --text-dim --green --red --amber --violet --pink --r --rx --rsm --font --mono`
- Run `php artisan view:clear && php artisan cache:clear` after

---

## Step 0 — Read First (MANDATORY)

```bash
# Read the ENTIRE component before writing anything
cat app/Livewire/Shop/Sales/PointOfSale.php

# Check QR package
composer show | grep qrcode
grep -r "QrCode\|simple-qrcode" composer.json

# Check POS page wrapper
cat resources/views/shop/pos.blade.php
```

**Key method and property names to note:**
- Add to cart: `confirmAddToCart()` (NOT `commitStagingToCart`)
- Remove item: `removeCartItem($index)` (NOT `removeFromCart`)
- Cart item name key: `product_name` (NOT `name`)
- Receipt modal: `$showReceiptModal` (NOT `$showReceipt`)
- Checkout open: `openCheckout()`
- Complete sale: `completeSale()`
- Edit item: `openEditItem($index)`
- Clear cart: `clearCart()`
- Search close: `closeSearch()`
- Product select: `selectProduct($productId)`
- Load products on focus: `loadAvailableProducts()`
- Barcode field: `$barcodeInput` / `wire:model.live="barcodeInput"`
- Scanner toggle: `enablePhoneScanner()` / `disablePhoneScanner()`
- Price modal: `openPriceModal($index)` / `applyPriceModification()`

---

## Step 1 — Add POS Full-Height CSS

**Target:** `resources/css/app.css` — append at the very end:

```css
/* ═══════════════════════════════════════
   POS PAGE — Full viewport, no padding
═══════════════════════════════════════ */
body:has(.pos-root) > div > main > div {
  padding: 0 !important;
}
body:has(.pos-root) {
  overflow: hidden;
}
```

Run `npm run build` after.

---

## Step 2 — Full Blade Rewrite

**Target:** `resources/views/livewire/shop/sales/point-of-sale.blade.php`

Replace the **entire** file with the content below. Read Step 0 first to confirm all method names match.

```blade
{{--
  Point of Sale — Modern Redesign
  Blade-only rewrite. PHP component is untouched.
  All wire: bindings match app/Livewire/Shop/Sales/PointOfSale.php exactly.
--}}
<div class="pos-root"
  x-data="{
    toasts: [],
    toast(msg, type) {
      const id = Date.now() + Math.random();
      this.toasts.push({ id, msg, type });
      setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 3800);
    }
  }"
  @notification.window="toast($event.detail.message, $event.detail.type)"
  style="height:100vh;display:flex;flex-direction:column;background:var(--surface2);overflow:hidden;position:relative">

{{-- ─── TOAST STACK ─── --}}
<div style="position:fixed;top:72px;right:16px;z-index:9000;display:flex;flex-direction:column;gap:7px;pointer-events:none">
  <template x-for="t in toasts" :key="t.id">
    <div x-show="true"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translateX-2"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-end="opacity-0"
         :style="`display:flex;align-items:center;gap:9px;padding:10px 16px;border-radius:10px;
                  font-size:13px;font-weight:600;pointer-events:auto;
                  box-shadow:0 4px 20px rgba(0,0,0,.13);min-width:200px;
                  background:${t.type==='error'?'var(--red)':t.type==='warning'?'var(--amber)':'var(--green)'};
                  color:#fff`">
      <span x-text="t.type==='error'?'✕':t.type==='warning'?'⚠':'✓'" style="font-size:15px;flex-shrink:0"></span>
      <span x-text="t.msg" style="flex:1"></span>
    </div>
  </template>
</div>

{{-- ─── OWNER: SHOP SELECTION MODAL ─── --}}
@if($isOwner && $showShopSelectionModal)
<div style="position:fixed;inset:0;z-index:800;display:flex;align-items:center;justify-content:center;
            background:rgba(26,31,54,.55);backdrop-filter:blur(4px)">
  <div style="background:var(--surface);border-radius:18px;padding:32px 28px;width:420px;
              max-width:94vw;box-shadow:0 24px 60px rgba(0,0,0,.22)">
    <div style="text-align:center;margin-bottom:22px">
      <div style="width:56px;height:56px;border-radius:14px;background:var(--accent-dim);
                  display:grid;place-items:center;margin:0 auto 14px">
        <svg width="26" height="26" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
      </div>
      <div style="font-size:19px;font-weight:800;color:var(--text);letter-spacing:-.3px">Select Shop</div>
      <div style="font-size:13px;color:var(--text-sub);margin-top:5px">Choose which shop to operate</div>
    </div>
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px">
      @foreach($availableShops as $shop)
      <button wire:click="$set('shopId', {{ $shop['id'] }})"
              style="padding:14px 16px;border-radius:12px;text-align:left;cursor:pointer;
                     border:2px solid {{ $shopId == $shop['id'] ? 'var(--accent)' : 'var(--border)' }};
                     background:{{ $shopId == $shop['id'] ? 'var(--accent-dim)' : 'var(--surface2)' }}">
        <div style="font-size:14px;font-weight:700;color:{{ $shopId == $shop['id'] ? 'var(--accent)' : 'var(--text)' }}">
          {{ $shop['name'] }}
        </div>
        @if(isset($shop['address']))
        <div style="font-size:12px;color:var(--text-dim);margin-top:2px">{{ $shop['address'] }}</div>
        @endif
      </button>
      @endforeach
    </div>
    <button wire:click="$set('showShopSelectionModal', false)"
            style="width:100%;padding:13px;background:var(--accent);color:#fff;border:none;
                   border-radius:var(--rx);font-size:15px;font-weight:700;cursor:pointer">
      Open POS →
    </button>
  </div>
</div>
@endif

@if(!$isOwner || $shopId)

{{-- ══════════════════════════════════════════════
     POS HEADER BAR
══════════════════════════════════════════════ --}}
<div style="background:var(--surface);border-bottom:1.5px solid var(--border);
            height:52px;display:flex;align-items:center;padding:0 18px;gap:14px;
            flex-shrink:0;z-index:50;box-shadow:0 1px 8px rgba(26,31,54,.06)">

  {{-- Shop chip --}}
  <div style="display:flex;align-items:center;gap:7px">
    <div style="width:28px;height:28px;border-radius:7px;background:var(--accent-dim);
                display:grid;place-items:center;flex-shrink:0">
      <svg width="13" height="13" fill="none" stroke="var(--accent)" stroke-width="2.5" viewBox="0 0 24 24">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
      </svg>
    </div>
    <span style="font-size:14px;font-weight:800;color:var(--text)">{{ $shopName ?? 'POS' }}</span>
    @if($isOwner)
    <button wire:click="$set('showShopSelectionModal', true)"
            style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:5px;cursor:pointer;
                   background:var(--accent-dim);color:var(--accent);border:none">
      Switch
    </button>
    @endif
  </div>

  <div style="width:1px;height:24px;background:var(--border)"></div>

  {{-- Live clock --}}
  <div x-data="{t:''}" x-init="setInterval(()=>{const n=new Date();t=n.toLocaleTimeString('en-RW',{hour:'2-digit',minute:'2-digit'})},1000)"
       style="font-size:13px;font-weight:600;color:var(--text-sub);font-family:var(--mono)" x-text="t"></div>

  <div style="flex:1"></div>

  {{-- Cart count badge (when cart has items) --}}
  @if(!empty($cart))
  <div style="display:flex;align-items:center;gap:6px;background:var(--accent);
              color:#fff;padding:4px 12px;border-radius:20px">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
      <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
      <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
    </svg>
    <span style="font-size:12px;font-weight:700;font-family:var(--mono)">
      {{ count($cart) }} · {{ number_format($cartTotal / 100) }} RWF
    </span>
  </div>
  @endif

  {{-- Phone scanner toggle --}}
  <button wire:click="{{ $showScannerPanel ? 'disablePhoneScanner' : 'enablePhoneScanner' }}"
          style="display:flex;align-items:center;gap:5px;padding:6px 11px;border-radius:8px;
                 font-size:12px;font-weight:600;cursor:pointer;transition:.15s;
                 background:{{ $showScannerPanel ? 'var(--green)' : 'var(--surface2)' }};
                 color:{{ $showScannerPanel ? '#fff' : 'var(--text-sub)' }};
                 border:1px solid {{ $showScannerPanel ? 'var(--green)' : 'var(--border)' }}">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <rect x="3" y="3" width="5" height="5"/><rect x="16" y="3" width="5" height="5"/>
      <rect x="3" y="16" width="5" height="5"/><path d="M21 16h-3v3"/><path d="M21 21v-2"/><path d="M16 16h2"/>
    </svg>
    {{ $showScannerPanel ? 'Scanner On' : 'Phone Scan' }}
  </button>
</div>

{{-- ══════════════════════════════════════════════
     MAIN SPLIT LAYOUT
══════════════════════════════════════════════ --}}
<div style="flex:1;display:grid;grid-template-columns:1fr 360px;overflow:hidden;min-height:0"
     class="pos-split">

  {{-- ════════════════════════════════
       LEFT — PRODUCT FINDER
  ════════════════════════════════ --}}
  <div style="display:flex;flex-direction:column;overflow:hidden;background:var(--surface2)">

    {{-- Search + Barcode toolbar --}}
    <div style="padding:12px 14px;background:var(--surface);border-bottom:1px solid var(--border);flex-shrink:0">
      <div style="display:flex;gap:8px;align-items:stretch">

        {{-- Product search --}}
        <div style="flex:1;position:relative" x-data="{focused:false}">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
               style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                      color:var(--text-dim);pointer-events:none;flex-shrink:0">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input wire:model.live="searchQuery"
                 wire:focus="loadAvailableProducts"
                 type="text"
                 placeholder="Search product by name, SKU or barcode..."
                 autocomplete="off"
                 style="width:100%;padding:9px 32px 9px 32px;border:1.5px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font)"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
          @if($searchQuery)
          <button wire:click="closeSearch"
                  style="position:absolute;right:9px;top:50%;transform:translateY(-50%);
                         background:none;border:none;cursor:pointer;color:var(--text-dim);padding:2px">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
          @endif

          {{-- Dropdown results --}}
          @if($showSearchResults)
          <div style="position:absolute;top:calc(100% + 5px);left:0;right:0;z-index:400;
                      background:var(--surface);border:1.5px solid var(--border);
                      border-radius:12px;box-shadow:0 8px 32px rgba(26,31,54,.15);
                      max-height:320px;overflow-y:auto">
            @if(count($searchResults) > 0)
            <div style="padding:6px 10px;font-size:10px;font-weight:700;letter-spacing:.5px;
                        text-transform:uppercase;color:var(--text-dim);
                        border-bottom:1px solid var(--border);sticky:top-0;background:var(--surface)">
              {{ count($searchResults) }} results — click to add
            </div>
            @foreach($searchResults as $result)
            <button wire:click="selectProduct({{ $result['id'] }})"
                    style="width:100%;padding:10px 12px;background:none;border:none;
                           border-bottom:1px solid var(--border);cursor:pointer;
                           display:flex;align-items:center;gap:10px;text-align:left"
                    onmouseover="this.style.background='var(--surface2)'"
                    onmouseout="this.style.background='none'">
              <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;
                           background:{{ $result['has_stock'] ? 'var(--green)' : 'var(--red)' }}"></span>
              <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:var(--text);
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  {{ $result['name'] }}
                </div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:1px;font-family:var(--mono)">
                  {{ $result['sku'] }}
                  @if($result['category']) · {{ $result['category'] }} @endif
                </div>
              </div>
              <div style="text-align:right;flex-shrink:0">
                <div style="font-size:13px;font-weight:700;color:var(--accent);font-family:var(--mono)">
                  {{ $result['selling_price_display'] }}
                </div>
                <div style="font-size:10px;font-weight:600;
                            color:{{ $result['has_stock'] ? 'var(--green)' : 'var(--red)' }}">
                  {{ $result['stock']['total_items'] }} items
                </div>
              </div>
            </button>
            @endforeach
            @else
            <div style="padding:18px;text-align:center;color:var(--text-dim);font-size:13px">
              No products match "{{ $searchQuery }}"
            </div>
            @endif
          </div>
          @endif
        </div>

        {{-- Barcode input --}}
        <div style="position:relative">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
               style="position:absolute;left:8px;top:50%;transform:translateY(-50%);
                      color:var(--text-dim);pointer-events:none">
            <path d="M2 3h2v18H2zM7 3h1v18H7zM11 3h2v18h-2zM15 3h1v18h-1zM18 3h2v18h-2zM22 3h0v18h0z"/>
          </svg>
          <input wire:model.live="barcodeInput"
                 type="text"
                 placeholder="Scan..."
                 autocomplete="off"
                 style="width:130px;padding:9px 10px 9px 26px;border:1.5px solid var(--border);
                        border-radius:var(--rx);font-size:12px;background:var(--surface);
                        color:var(--text);outline:none;font-family:var(--mono)"
                 onfocus="this.style.borderColor='var(--green)'"
                 onblur="this.style.borderColor='var(--border)'">
        </div>

      </div>

      {{-- Phone scanner QR panel --}}
      @if($showScannerPanel && $scannerSession)
      <div wire:poll.2000ms
           style="margin-top:10px;padding:11px 13px;background:var(--green-dim);
                  border:1px solid var(--green);border-radius:var(--rx);
                  display:flex;align-items:center;gap:12px">
        @php
          $qrAvailable = class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class);
        @endphp
        @if($qrAvailable)
        <div style="flex-shrink:0;background:#fff;padding:4px;border-radius:6px">
          {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(route('scanner.mobile') . '?session=' . $scannerSession->session_code) !!}
        </div>
        @endif
        <div>
          <div style="font-size:12px;font-weight:700;color:var(--green)">
            📱 Phone Scanner Active
          </div>
          <div style="font-size:11px;color:var(--text-sub);margin-top:2px">
            Code: <span style="font-family:var(--mono);font-weight:700">{{ $scannerSession->session_code }}</span>
          </div>
          <div style="font-size:10px;color:var(--text-dim);margin-top:2px">
            {{ route('scanner.mobile') }}?session={{ $scannerSession->session_code }}
          </div>
        </div>
      </div>
      @endif
    </div>

    {{-- Product grid area --}}
    <div style="flex:1;overflow-y:auto;padding:14px">

      @if(count($allStockProducts) > 0)
      {{-- Product tiles --}}
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px">
        @foreach($allStockProducts as $p)
        <button wire:click="selectProduct({{ $p['id'] }})"
                style="background:var(--surface);border:1.5px solid var(--border);
                       border-radius:12px;padding:14px 12px;text-align:left;cursor:pointer;
                       display:flex;flex-direction:column;gap:6px;
                       transition:border-color .14s,box-shadow .14s;width:100%;
                       position:relative;overflow:hidden"
                onmouseover="this.style.borderColor='var(--accent)';this.style.boxShadow='0 2px 14px rgba(59,111,212,.10)'"
                onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
          {{-- Stock dot --}}
          <span style="position:absolute;top:10px;right:10px;width:7px;height:7px;border-radius:50%;
                       background:{{ $p['has_stock'] ? 'var(--green)' : 'var(--red)' }}"></span>
          {{-- Category chip --}}
          @if($p['category'])
          <span style="font-size:9px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;
                       color:var(--text-dim);background:var(--surface2);padding:2px 6px;
                       border-radius:5px;align-self:flex-start">
            {{ $p['category'] }}
          </span>
          @endif
          <div style="font-size:13px;font-weight:700;color:var(--text);line-height:1.3;
                      display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
            {{ $p['name'] }}
          </div>
          <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono)">{{ $p['sku'] }}</div>
          <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-top:2px">
            <span style="font-size:14px;font-weight:800;color:var(--accent);font-family:var(--mono)">
              {{ number_format($p['selling_price'] / 100) }}
            </span>
            <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:6px;
                         background:{{ $p['stock']['total_items'] > 0 ? 'var(--green-dim)' : 'var(--red-dim)' }};
                         color:{{ $p['stock']['total_items'] > 0 ? 'var(--green)' : 'var(--red)' }}">
              {{ $p['stock']['total_items'] }}
            </span>
          </div>
        </button>
        @endforeach
      </div>
      @else
      {{-- Empty / initial state --}}
      <div style="height:100%;display:flex;flex-direction:column;align-items:center;
                  justify-content:center;text-align:center;color:var(--text-dim);padding:40px 20px">
        <div style="width:70px;height:70px;border-radius:18px;background:var(--surface);
                    display:grid;place-items:center;margin-bottom:18px;
                    border:2px dashed var(--border)">
          <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
        </div>
        <div style="font-size:16px;font-weight:700;color:var(--text-sub);margin-bottom:8px">
          Ready to sell
        </div>
        <div style="font-size:13px;line-height:1.6">
          Click the search box to load products,<br>
          or scan a barcode to add instantly
        </div>
      </div>
      @endif

    </div>
  </div>

  {{-- ════════════════════════════════
       RIGHT — CART
  ════════════════════════════════ --}}
  <div style="display:flex;flex-direction:column;background:var(--surface);
              border-left:1.5px solid var(--border);overflow:hidden">

    {{-- Cart header --}}
    <div style="padding:12px 14px 10px;border-bottom:1px solid var(--border);flex-shrink:0">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:8px">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
               style="color:var(--text-sub)">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
          </svg>
          <span style="font-size:14px;font-weight:800;color:var(--text)">Cart</span>
          @if(!empty($cart))
          <span style="background:var(--accent);color:#fff;font-size:10px;font-weight:700;
                       padding:2px 7px;border-radius:10px">{{ count($cart) }}</span>
          @endif
        </div>
        @if(!empty($cart))
        <button wire:click="clearCart"
                wire:confirm="Clear all items from cart?"
                style="font-size:11px;font-weight:600;color:var(--red);background:var(--red-dim);
                       border:none;padding:3px 9px;border-radius:6px;cursor:pointer">
          Clear
        </button>
        @endif
      </div>
    </div>

    {{-- Cart items list --}}
    <div style="flex:1;overflow-y:auto;padding:10px 10px 4px">
      @forelse($cart as $index => $item)
      <div wire:key="ci-{{ $index }}"
           style="background:var(--surface2);border:1px solid var(--border);
                  border-radius:10px;padding:10px 11px;margin-bottom:8px">
        {{-- Name row --}}
        <div style="display:flex;align-items:flex-start;gap:6px;margin-bottom:6px">
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;color:var(--text);
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              {{ $item['product_name'] }}
            </div>
            {{-- badges --}}
            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:3px">
              <span style="font-size:9px;font-weight:700;padding:1px 5px;border-radius:4px;
                           background:{{ $item['is_full_box'] ? 'var(--accent-dim)' : 'var(--green-dim)' }};
                           color:{{ $item['is_full_box'] ? 'var(--accent)' : 'var(--green)' }}">
                {{ $item['is_full_box'] ? '📦 BOX' : '🏷 ITEMS' }}
              </span>
              @if(!empty($item['price_modified']))
              <span style="font-size:9px;font-weight:700;padding:1px 5px;border-radius:4px;
                           background:var(--amber-dim);color:var(--amber)">⚡ MODIFIED</span>
              @endif
              @if(!empty($item['requires_owner_approval']))
              <span style="font-size:9px;font-weight:700;padding:1px 5px;border-radius:4px;
                           background:var(--red-dim);color:var(--red)">⚠ APPROVAL</span>
              @endif
            </div>
          </div>
          {{-- Edit + Remove --}}
          <div style="display:flex;gap:4px;flex-shrink:0">
            <button wire:click="openEditItem({{ $index }})"
                    style="width:27px;height:27px;border-radius:6px;background:var(--accent-dim);
                           border:none;cursor:pointer;display:grid;place-items:center;color:var(--accent)">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
            </button>
            <button wire:click="removeCartItem({{ $index }})"
                    style="width:27px;height:27px;border-radius:6px;background:var(--red-dim);
                           border:none;cursor:pointer;display:grid;place-items:center;color:var(--red)">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
              </svg>
            </button>
          </div>
        </div>
        {{-- Qty · price · line total --}}
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span style="font-size:11px;color:var(--text-dim)">
            {{ $item['quantity'] }} × {{ number_format($item['price'] / 100) }} RWF
          </span>
          <span style="font-size:14px;font-weight:800;color:var(--text);font-family:var(--mono)">
            {{ number_format($item['line_total'] / 100) }}
          </span>
        </div>
      </div>
      @empty
      <div style="height:100%;min-height:160px;display:flex;flex-direction:column;
                  align-items:center;justify-content:center;padding:24px;text-align:center">
        <svg width="38" height="38" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
             style="color:var(--border);margin-bottom:12px">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <div style="font-size:13px;font-weight:600;color:var(--text-sub)">Cart empty</div>
        <div style="font-size:12px;color:var(--text-dim);margin-top:4px">Add products from the left</div>
      </div>
      @endforelse
    </div>

    {{-- Cart footer --}}
    <div style="border-top:1.5px solid var(--border);padding:12px 14px 14px;flex-shrink:0">
      @if(!empty($cart))
      <div style="margin-bottom:12px">
        <div style="display:flex;justify-content:space-between;margin-bottom:3px">
          <span style="font-size:12px;color:var(--text-sub)">Subtotal</span>
          <span style="font-size:12px;font-family:var(--mono);color:var(--text)">
            {{ number_format($cartTotal / 100) }} RWF
          </span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;
                    padding-top:8px;border-top:1px solid var(--border)">
          <span style="font-size:15px;font-weight:700;color:var(--text)">Total</span>
          <span style="font-size:24px;font-weight:800;color:var(--accent);font-family:var(--mono)">
            {{ number_format($cartTotal / 100) }}<span style="font-size:12px;font-weight:600"> RWF</span>
          </span>
        </div>
      </div>
      @endif

      <button wire:click="openCheckout"
              @if(empty($cart)) disabled @endif
              style="width:100%;padding:13px 16px;
                     background:{{ empty($cart) ? 'var(--surface3)' : 'linear-gradient(135deg,var(--accent),#6b8dff)' }};
                     color:{{ empty($cart) ? 'var(--text-dim)' : '#fff' }};
                     border:none;border-radius:10px;font-size:15px;font-weight:800;
                     cursor:{{ empty($cart) ? 'not-allowed' : 'pointer' }};
                     display:flex;align-items:center;justify-content:center;gap:8px;
                     font-family:var(--font);box-shadow:{{ empty($cart) ? 'none' : '0 4px 16px rgba(59,111,212,.35)' }};
                     transition:.15s">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
        @if(empty($cart))
          Add items to checkout
        @else
          Checkout — {{ number_format($cartTotal / 100) }} RWF
        @endif
      </button>
    </div>

  </div>
</div>{{-- /pos-split --}}

@endif{{-- end shop guard --}}

{{-- ══════════════════════════════════════════════
     STAGING MODAL (Add / Edit item)
══════════════════════════════════════════════ --}}
@if($showAddModal && $stagingProduct)
<div style="position:fixed;inset:0;z-index:600;display:flex;align-items:center;
            justify-content:center;background:rgba(26,31,54,.52);backdrop-filter:blur(3px)">
  <div style="background:var(--surface);border-radius:18px;width:500px;max-width:96vw;
              max-height:94vh;overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.22)">

    {{-- Modal header --}}
    <div style="padding:18px 22px 14px;border-bottom:1px solid var(--border);
                display:flex;align-items:flex-start;justify-content:space-between;
                position:sticky;top:0;background:var(--surface);z-index:2">
      <div>
        <div style="font-size:17px;font-weight:800;color:var(--text)">
          {{ $stagingCartIndex !== null ? 'Edit Cart Item' : 'Add to Cart' }}
        </div>
        <div style="font-size:12px;color:var(--text-sub);margin-top:2px">
          {{ $stagingProduct['name'] }}
        </div>
      </div>
      <button wire:click="closeAddModal"
              style="width:30px;height:30px;border-radius:8px;background:var(--surface2);
                     border:1px solid var(--border);cursor:pointer;display:grid;place-items:center">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <div style="padding:20px 22px">

      {{-- Product info strip --}}
      <div style="background:var(--surface2);border-radius:10px;padding:12px 14px;
                  margin-bottom:18px;display:flex;justify-content:space-between;align-items:center">
        <div>
          <div style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $stagingProduct['sku'] }}</div>
          <div style="font-size:11px;color:var(--text-sub);margin-top:2px">{{ $stagingProduct['category'] ?? '—' }}</div>
        </div>
        <div style="text-align:right">
          <div style="font-size:10px;color:var(--text-dim);margin-bottom:2px">In stock at this shop</div>
          <div style="font-size:18px;font-weight:800;font-family:var(--mono);
                      color:{{ ($stagingStock['total_items'] ?? 0) > 0 ? 'var(--green)' : 'var(--red)' }}">
            {{ $stagingStock['total_items'] ?? 0 }}
            <span style="font-size:11px;font-weight:600">items</span>
          </div>
          <div style="font-size:10px;color:var(--text-dim)">
            {{ $stagingStock['full_boxes'] ?? 0 }} full · {{ $stagingStock['partial_boxes'] ?? 0 }} partial
          </div>
        </div>
      </div>

      {{-- Sell mode toggle --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px">
        <button wire:click="$set('stagingMode','box')"
                style="padding:14px 12px;border-radius:10px;border:2px solid {{ $stagingMode==='box' ? 'var(--accent)' : 'var(--border)' }};
                       background:{{ $stagingMode==='box' ? 'var(--accent-dim)' : 'var(--surface2)' }};cursor:pointer">
          <div style="text-align:center">
            <div style="font-size:22px;margin-bottom:4px">📦</div>
            <div style="font-size:13px;font-weight:700;color:{{ $stagingMode==='box' ? 'var(--accent)' : 'var(--text-sub)' }}">
              Full Box
            </div>
            <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
              {{ $stagingProduct['items_per_box'] }} items / box
            </div>
            <div style="font-size:13px;font-weight:700;color:var(--accent);font-family:var(--mono);margin-top:6px">
              {{ number_format($stagingProduct['box_price'] / 100) }} RWF
            </div>
          </div>
        </button>
        <button wire:click="$set('stagingMode','item')"
                style="padding:14px 12px;border-radius:10px;border:2px solid {{ $stagingMode==='item' ? 'var(--accent)' : 'var(--border)' }};
                       background:{{ $stagingMode==='item' ? 'var(--accent-dim)' : 'var(--surface2)' }};cursor:pointer">
          <div style="text-align:center">
            <div style="font-size:22px;margin-bottom:4px">🏷</div>
            <div style="font-size:13px;font-weight:700;color:{{ $stagingMode==='item' ? 'var(--accent)' : 'var(--text-sub)' }}">
              Individual Items
            </div>
            <div style="font-size:11px;color:var(--text-dim);margin-top:2px">per item</div>
            <div style="font-size:13px;font-weight:700;color:var(--accent);font-family:var(--mono);margin-top:6px">
              {{ number_format($stagingProduct['selling_price'] / 100) }} RWF
            </div>
          </div>
        </button>
      </div>

      {{-- Quantity stepper --}}
      <div style="margin-bottom:16px">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:7px">
          Quantity
        </label>
        <div style="display:flex;align-items:center;gap:10px">
          <button wire:click="$set('stagingQty', max(1, stagingQty - 1))"
                  style="width:38px;height:38px;border-radius:9px;background:var(--surface2);
                         border:1.5px solid var(--border);cursor:pointer;font-size:20px;
                         display:grid;place-items:center;color:var(--text-sub);flex-shrink:0">
            &minus;
          </button>
          <input wire:model.live="stagingQty" type="number" min="1"
                 style="flex:1;padding:8px;text-align:center;border:2px solid var(--accent);
                        border-radius:9px;font-size:22px;font-weight:800;
                        background:var(--surface);color:var(--text);font-family:var(--mono);
                        outline:none">
          <button wire:click="$set('stagingQty', stagingQty + 1)"
                  style="width:38px;height:38px;border-radius:9px;background:var(--surface2);
                         border:1.5px solid var(--border);cursor:pointer;font-size:20px;
                         display:grid;place-items:center;color:var(--text-sub);flex-shrink:0">
            +
          </button>
        </div>
      </div>

      {{-- Unit price --}}
      <div style="margin-bottom:16px">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:7px;
                      display:flex;justify-content:space-between;align-items:center">
          <span>Unit Price (RWF)</span>
          @if($stagingPriceModified)
          <span style="font-size:10px;font-weight:700;color:var(--amber);background:var(--amber-dim);
                       padding:1px 6px;border-radius:4px">MODIFIED</span>
          @endif
        </label>
        <div style="position:relative">
          <span style="position:absolute;left:11px;top:50%;transform:translateY(-50%);
                       font-size:11px;font-weight:700;color:var(--text-dim)">RWF</span>
          <input wire:model.live="stagingPrice" type="number" min="0"
                 style="width:100%;padding:11px 12px 11px 42px;box-sizing:border-box;
                        border:2px solid {{ $stagingPriceModified ? 'var(--amber)' : 'var(--border)' }};
                        border-radius:9px;font-size:18px;font-weight:800;
                        background:{{ $stagingPriceModified ? 'var(--amber-dim)' : 'var(--surface)' }};
                        color:var(--text);font-family:var(--mono);outline:none">
        </div>
        @if($stagingPriceModified)
        <div style="margin-top:7px">
          <input wire:model.live="stagingPriceReason" type="text"
                 placeholder="Reason for price change (required)..."
                 style="width:100%;padding:9px 12px;box-sizing:border-box;
                        border:1.5px solid var(--amber);border-radius:8px;font-size:13px;
                        background:var(--surface);color:var(--text);outline:none">
        </div>
        @endif
      </div>

      {{-- Line total preview --}}
      <div style="background:{{ $stagingPriceModified ? 'var(--amber-dim)' : 'var(--accent-dim)' }};
                  border-radius:10px;padding:13px 16px;margin-bottom:20px;
                  display:flex;justify-content:space-between;align-items:center;
                  border:1px solid {{ $stagingPriceModified ? 'var(--amber)' : 'rgba(59,111,212,.2)' }}">
        <span style="font-size:13px;color:var(--text-sub)">
          {{ $stagingQty }} × {{ number_format($stagingPrice / 100) }} RWF
        </span>
        <span style="font-size:22px;font-weight:800;font-family:var(--mono);
                     color:{{ $stagingPriceModified ? 'var(--amber)' : 'var(--accent)' }}">
          {{ number_format(($stagingQty * $stagingPrice) / 100) }}
          <span style="font-size:12px;font-weight:600">RWF</span>
        </span>
      </div>

      {{-- Buttons --}}
      <div style="display:grid;grid-template-columns:auto 1fr;gap:8px">
        <button wire:click="closeAddModal"
                style="padding:12px 20px;background:var(--surface2);color:var(--text-sub);
                       border:1.5px solid var(--border);border-radius:10px;font-size:14px;
                       font-weight:600;cursor:pointer">
          Cancel
        </button>
        <button wire:click="confirmAddToCart"
                wire:loading.attr="disabled"
                style="padding:12px 20px;background:linear-gradient(135deg,var(--accent),#6b8dff);
                       color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:800;
                       cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;
                       box-shadow:0 3px 12px rgba(59,111,212,.30)">
          <span wire:loading.remove>
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:5px">
              <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            {{ $stagingCartIndex !== null ? 'Update Cart' : 'Add to Cart' }}
          </span>
          <span wire:loading style="font-size:13px">Adding...</span>
        </button>
      </div>

    </div>
  </div>
</div>
@endif

{{-- ══════════════════════════════════════════════
     CHECKOUT MODAL
══════════════════════════════════════════════ --}}
@if($showCheckoutModal)
<div style="position:fixed;inset:0;z-index:600;display:flex;align-items:center;
            justify-content:center;background:rgba(26,31,54,.55);backdrop-filter:blur(4px)">
  <div style="background:var(--surface);border-radius:18px;width:520px;max-width:96vw;
              max-height:94vh;overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.24)">

    {{-- Header --}}
    <div style="padding:18px 22px 14px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between;
                position:sticky;top:0;background:var(--surface);z-index:2">
      <div style="font-size:17px;font-weight:800;color:var(--text)">Checkout</div>
      <button wire:click="$set('showCheckoutModal', false)"
              style="width:30px;height:30px;border-radius:8px;background:var(--surface2);
                     border:1px solid var(--border);cursor:pointer;display:grid;place-items:center">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <div style="padding:20px 22px">

      {{-- Order recap --}}
      <div style="background:var(--accent-dim);border:1px solid rgba(59,111,212,.18);
                  border-radius:12px;padding:14px 16px;margin-bottom:20px">
        <div style="font-size:10px;font-weight:800;letter-spacing:.7px;text-transform:uppercase;
                    color:var(--accent);margin-bottom:10px">Order Summary</div>
        @foreach($cart as $item)
        <div style="display:flex;justify-content:space-between;margin-bottom:5px">
          <span style="font-size:12px;color:var(--text-sub)">
            {{ $item['product_name'] }} × {{ $item['quantity'] }}
          </span>
          <span style="font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text)">
            {{ number_format($item['line_total'] / 100) }}
          </span>
        </div>
        @endforeach
        <div style="display:flex;justify-content:space-between;align-items:center;
                    padding-top:10px;border-top:1px solid rgba(59,111,212,.18);margin-top:8px">
          <span style="font-size:15px;font-weight:700;color:var(--text)">Total</span>
          <span style="font-size:26px;font-weight:800;color:var(--accent);font-family:var(--mono)">
            {{ number_format($cartTotal / 100) }}<span style="font-size:13px;font-weight:600"> RWF</span>
          </span>
        </div>
      </div>

      {{-- Payment method --}}
      <div style="margin-bottom:18px">
        <label style="display:block;font-size:12px;font-weight:700;color:var(--text-sub);
                      margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">
          Payment Method
        </label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
          @foreach(['cash'=>['💵','Cash'],'mobile_money'=>['📱','Mobile Money'],'card'=>['💳','Card'],'bank_transfer'=>['🏦','Bank Transfer']] as $val=>$d)
          <button wire:click="$set('paymentMethod','{{ $val }}')"
                  style="padding:12px 10px;border-radius:10px;cursor:pointer;
                         border:2px solid {{ $paymentMethod===$val ? 'var(--accent)' : 'var(--border)' }};
                         background:{{ $paymentMethod===$val ? 'var(--accent-dim)' : 'var(--surface2)' }};
                         text-align:center;transition:.12s">
            <div style="font-size:18px;margin-bottom:4px">{{ $d[0] }}</div>
            <div style="font-size:12px;font-weight:700;
                        color:{{ $paymentMethod===$val ? 'var(--accent)' : 'var(--text-sub)' }}">
              {{ $d[1] }}
            </div>
          </button>
          @endforeach
        </div>
      </div>

      {{-- Cash: amount received + change --}}
      @if($paymentMethod === 'cash')
      <div style="margin-bottom:16px">
        <label style="display:block;font-size:12px;font-weight:700;color:var(--text-sub);
                      margin-bottom:7px;text-transform:uppercase;letter-spacing:.5px">
          Amount Received
        </label>
        <div style="position:relative">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                       font-size:12px;font-weight:700;color:var(--text-dim)">RWF</span>
          <input wire:model.live="amountReceived" type="number" min="0" step="100"
                 style="width:100%;padding:13px 14px 13px 46px;box-sizing:border-box;
                        border:2px solid var(--border);border-radius:10px;
                        font-size:24px;font-weight:800;text-align:right;
                        background:var(--surface);color:var(--text);font-family:var(--mono);outline:none"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
        </div>
        @if($changeAmount > 0)
        <div style="margin-top:10px;padding:12px 14px;background:var(--green-dim);
                    border:1.5px solid var(--green);border-radius:10px;
                    display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:13px;font-weight:700;color:var(--green)">Change to return</span>
          <span style="font-size:22px;font-weight:800;color:var(--green);font-family:var(--mono)">
            {{ number_format($changeAmount) }} RWF
          </span>
        </div>
        @endif
      </div>
      @endif

      {{-- Customer info --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
        <div>
          <label style="display:block;font-size:11px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Customer Name <span style="color:var(--text-dim);font-weight:400">(optional)</span>
          </label>
          <input wire:model="customerName" type="text" placeholder="Name..."
                 style="width:100%;padding:9px 11px;border:1.5px solid var(--border);
                        border-radius:8px;font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
        </div>
        <div>
          <label style="display:block;font-size:11px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Phone <span style="color:var(--text-dim);font-weight:400">(optional)</span>
          </label>
          <input wire:model="customerPhone" type="text" placeholder="+250..."
                 style="width:100%;padding:9px 11px;border:1.5px solid var(--border);
                        border-radius:8px;font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
        </div>
      </div>

      {{-- Notes --}}
      <div style="margin-bottom:20px">
        <label style="display:block;font-size:11px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
          Notes <span style="color:var(--text-dim);font-weight:400">(optional)</span>
        </label>
        <textarea wire:model="notes" rows="2" placeholder="Sale notes..."
                  style="width:100%;padding:9px 11px;border:1.5px solid var(--border);
                         border-radius:8px;font-size:13px;background:var(--surface);
                         color:var(--text);outline:none;resize:none;box-sizing:border-box"
                  onfocus="this.style.borderColor='var(--accent)'"
                  onblur="this.style.borderColor='var(--border)'"></textarea>
      </div>

      {{-- Complete sale --}}
      <button wire:click="completeSale"
              wire:loading.attr="disabled"
              style="width:100%;padding:15px;background:linear-gradient(135deg,#0e9e86,#16c49a);
                     color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:800;
                     cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;
                     box-shadow:0 4px 18px rgba(14,158,134,.35);font-family:var(--font)">
        <span wire:loading.remove>
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
               style="display:inline;vertical-align:middle;margin-right:6px">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Complete Sale
        </span>
        <span wire:loading>Processing...</span>
      </button>

    </div>
  </div>
</div>
@endif

{{-- ══════════════════════════════════════════════
     RECEIPT MODAL
══════════════════════════════════════════════ --}}
@if($showReceiptModal && $completedSale)
<div style="position:fixed;inset:0;z-index:700;display:flex;align-items:center;
            justify-content:center;background:rgba(26,31,54,.60);backdrop-filter:blur(5px)">
  <div style="background:var(--surface);border-radius:18px;width:400px;max-width:96vw;
              box-shadow:0 28px 70px rgba(0,0,0,.26);overflow:hidden">

    {{-- Green success banner --}}
    <div style="background:linear-gradient(135deg,#0e9e86,#16c49a);padding:26px;text-align:center">
      <div style="width:54px;height:54px;border-radius:50%;background:rgba(255,255,255,.22);
                  display:grid;place-items:center;margin:0 auto 12px">
        <svg width="28" height="28" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <div style="font-size:20px;font-weight:800;color:#fff">Sale Complete!</div>
      <div style="font-size:12px;color:rgba(255,255,255,.8);margin-top:5px;font-family:var(--mono)">
        {{ $completedSale['sale_number'] ?? '' }}
      </div>
    </div>

    {{-- Receipt lines --}}
    <div style="padding:20px 22px">
      @foreach(($completedSale['items'] ?? []) as $item)
      <div style="display:flex;justify-content:space-between;margin-bottom:6px">
        <span style="font-size:12px;color:var(--text-sub)">
          {{ $item['product_name'] ?? $item['name'] ?? '' }} × {{ $item['quantity'] }}
        </span>
        <span style="font-size:12px;font-weight:700;font-family:var(--mono)">
          {{ number_format(($item['line_total'] ?? 0) / 100) }}
        </span>
      </div>
      @endforeach

      <div style="border-top:2px solid var(--border);margin:12px 0 10px;padding-top:12px;
                  display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:15px;font-weight:700;color:var(--text)">Total Paid</span>
        <span style="font-size:26px;font-weight:800;font-family:var(--mono);color:var(--green)">
          {{ number_format(($completedSale['total'] ?? 0) / 100) }}
          <span style="font-size:13px;font-weight:600">RWF</span>
        </span>
      </div>

      <div style="font-size:12px;color:var(--text-dim);text-align:center;margin-bottom:20px">
        {{ ucwords(str_replace('_',' ', $completedSale['payment_method'] ?? '')) }}
        · {{ now()->format('d M Y H:i') }}
      </div>

      <button wire:click="$set('showReceiptModal', false)"
              style="width:100%;padding:13px;background:var(--accent);color:#fff;
                     border:none;border-radius:10px;font-size:15px;font-weight:800;
                     cursor:pointer;box-shadow:0 3px 14px rgba(59,111,212,.30)">
        ✓ New Sale
      </button>
    </div>

  </div>
</div>
@endif

{{-- Responsive --}}
<style>
@media (max-width: 860px) {
  .pos-split { grid-template-columns: 1fr !important; overflow: auto !important; }
  .pos-root  { height: auto !important; overflow: auto !important; }
}
</style>

</div>{{-- /pos-root --}}
```

---

## Step 3 — Verify Method Names Match

After writing the blade, run:

```bash
# Confirm each wire:click method exists in the PHP component
grep -n "public function " app/Livewire/Shop/Sales/PointOfSale.php | grep -E "confirmAddToCart|removeCartItem|openEditItem|clearCart|closeSearch|openCheckout|completeSale|loadAvailableProducts|selectProduct|enablePhoneScanner|disablePhoneScanner|closeAddModal"

# Confirm property names
grep -n "public " app/Livewire/Shop/Sales/PointOfSale.php | grep -E "showReceiptModal|showAddModal|showCheckoutModal|stagingMode|stagingQty|stagingPrice|stagingPriceModified|cartTotal|showScannerPanel"
```

If `showReceiptModal` does not exist and instead `showReceipt` is the property name — find all occurrences of `showReceiptModal` in the blade and replace with `showReceipt`.

---

## Step 4 — Clear and Build

```bash
php artisan view:clear
php artisan cache:clear
npm run build
```

---

## What Was Redesigned

| Area | Before | After |
|------|--------|-------|
| Layout | Scrollable single column | Split pane — products left, cart always-visible right |
| Product tiles | Hidden until searched | Load on focus, shown as clickable grid cards |
| Search dropdown | Basic list | Colour-coded stock dots, SKU, category, price, item count |
| Staging modal | Horizontal layout, plain fields | Mode cards (box / item), quantity stepper, live line-total |
| Cart items | Plain rows | Badges for BOX/ITEMS/MODIFIED/APPROVAL, inline edit + remove |
| Checkout modal | Dropdown for payment | 4-button payment picker, large cash input with change display |
| Receipt modal | Simple success | Green gradient header, line items, one-tap "New Sale" |
| Notifications | Session flash | Alpine toast stack (real-time, top-right, colour-coded) |
| Scanner panel | Inline section | Topbar toggle, compact QR strip below search bar |
