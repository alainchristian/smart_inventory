{{--
  Point of Sale — Professional Redesign
  Mobile-first: bottom-sheet cart on small screens, FAB toggle.
  Desktop: split layout (1fr / 380px).
  All wire: bindings match app/Livewire/Shop/Sales/PointOfSale.php exactly.
--}}
<div class="pos-root"
  x-data="{
    cartOpen: false,
    toasts: [],
    toast(msg, type) {
      const id = Date.now() + Math.random();
      this.toasts.push({ id, msg, type });
      setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 3800);
    }
  }"
  @notification.window="toast($event.detail.message, $event.detail.type)"
  style="height:100vh;display:flex;flex-direction:column;background:#f0f2f7;overflow:hidden;position:relative">

{{-- ─── TOAST STACK ─── --}}
<div style="position:fixed;top:72px;right:16px;z-index:9000;display:flex;flex-direction:column;gap:7px;pointer-events:none">
  <template x-for="t in toasts" :key="t.id">
    <div x-show="true"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-end="opacity-0"
         :style="`display:flex;align-items:center;gap:9px;padding:11px 16px;border-radius:12px;
                  font-size:13px;font-weight:600;pointer-events:auto;
                  box-shadow:0 4px 24px rgba(0,0,0,.16);min-width:220px;max-width:320px;
                  background:${t.type==='error'?'#e63946':t.type==='warning'?'#f59e0b':'#0e9e86'};color:#fff`">
      <span x-text="t.type==='error'?'✕':t.type==='warning'?'⚠':'✓'"
            style="width:18px;height:18px;border-radius:50%;background:rgba(255,255,255,.22);
                   display:grid;place-items:center;flex-shrink:0;font-size:11px;font-weight:800"></span>
      <span x-text="t.msg" style="flex:1;line-height:1.4"></span>
    </div>
  </template>
</div>

{{-- ─── OWNER: SHOP SELECTION MODAL ─── --}}
@if($isOwner && $showShopSelectionModal)
<div style="position:fixed;inset:0;z-index:800;display:flex;align-items:center;justify-content:center;
            background:rgba(15,20,40,.6);backdrop-filter:blur(6px)">
  <div style="background:#fff;border-radius:20px;padding:32px 28px;width:440px;
              max-width:94vw;box-shadow:0 32px 80px rgba(0,0,0,.25)">
    <div style="text-align:center;margin-bottom:24px">
      <div style="width:60px;height:60px;border-radius:16px;background:#eef2ff;
                  display:grid;place-items:center;margin:0 auto 16px">
        <svg width="28" height="28" fill="none" stroke="#3b6fd4" stroke-width="2" viewBox="0 0 24 24">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
      </div>
      <div style="font-size:20px;font-weight:800;color:#1a1f36;letter-spacing:-.4px">Select Shop</div>
      <div style="font-size:13px;color:#6b7494;margin-top:6px">Choose which shop to operate</div>
    </div>
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px">
      @foreach($availableShops as $shop)
      <button wire:click="$set('shopId', {{ $shop['id'] }})"
              style="padding:14px 16px;border-radius:14px;text-align:left;cursor:pointer;transition:.15s;
                     border:2px solid {{ $shopId == $shop['id'] ? '#3b6fd4' : '#e2e6f3' }};
                     background:{{ $shopId == $shop['id'] ? '#eef2ff' : '#f8f9fc' }}">
        <div style="font-size:14px;font-weight:700;color:{{ $shopId == $shop['id'] ? '#3b6fd4' : '#1a1f36' }}">
          {{ $shop['name'] }}
        </div>
        @if(isset($shop['address']))
        <div style="font-size:12px;color:#a8aec8;margin-top:3px">{{ $shop['address'] }}</div>
        @endif
      </button>
      @endforeach
    </div>
    <button wire:click="$set('showShopSelectionModal', false)"
            style="width:100%;padding:14px;background:linear-gradient(135deg,#3b6fd4,#6b8dff);
                   color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;
                   box-shadow:0 4px 16px rgba(59,111,212,.35)">
      Open POS →
    </button>
  </div>
</div>
@endif

@if(!$isOwner || $shopId)

{{-- ══════════════════════════════════════════════
     POS HEADER BAR
══════════════════════════════════════════════ --}}
<div class="pos-header"
     style="background:var(--surface);height:54px;display:flex;align-items:center;padding:0 16px;gap:12px;
            flex-shrink:0;z-index:50;border-bottom:1.5px solid var(--border);
            box-shadow:0 1px 6px rgba(26,31,54,.07)">

  {{-- Shop chip --}}
  <div style="display:flex;align-items:center;gap:8px">
    <div style="width:30px;height:30px;border-radius:8px;background:var(--accent-dim);
                display:grid;place-items:center;flex-shrink:0">
      <svg width="14" height="14" fill="none" stroke="var(--accent)" stroke-width="2.5" viewBox="0 0 24 24">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
      </svg>
    </div>
    <span class="pos-shop-name" style="font-size:14px;font-weight:800;color:var(--text);letter-spacing:-.2px">
      {{ $shopName ?? 'POS' }}
    </span>
    @if($isOwner)
    <button wire:click="$set('showShopSelectionModal', true)"
            style="font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;cursor:pointer;
                   background:var(--accent-dim);color:var(--accent);border:1px solid transparent">
      Switch
    </button>
    @endif
  </div>

  <div style="width:1px;height:24px;background:var(--border)"></div>

  {{-- Live clock --}}
  <div x-data="{t:''}"
       x-init="t=new Date().toLocaleTimeString('en-RW',{hour:'2-digit',minute:'2-digit'});setInterval(()=>{const n=new Date();t=n.toLocaleTimeString('en-RW',{hour:'2-digit',minute:'2-digit'})},10000)"
       class="pos-clock"
       style="font-size:13px;font-weight:600;color:var(--text-sub);font-family:var(--mono)"
       x-text="t"></div>

  <div style="flex:1"></div>

  {{-- Cart summary chip (desktop) --}}
  @if(!empty($cart))
  <div class="pos-cart-chip"
       style="display:flex;align-items:center;gap:7px;background:var(--accent);
              color:#fff;padding:5px 13px;border-radius:22px">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
      <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
      <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
    </svg>
    <span style="font-size:12px;font-weight:700;font-family:var(--mono)">
      {{ count($cart) }} · {{ number_format($cartTotal) }} RWF
    </span>
  </div>
  @endif

  {{-- Phone scanner toggle --}}
  <button wire:click="{{ $showScannerPanel ? 'disablePhoneScanner' : 'enablePhoneScanner' }}"
          style="display:flex;align-items:center;gap:6px;padding:6px 11px;border-radius:8px;
                 font-size:12px;font-weight:600;cursor:pointer;transition:.15s;
                 background:{{ $showScannerPanel ? 'var(--green)' : 'var(--surface2)' }};
                 color:{{ $showScannerPanel ? '#fff' : 'var(--text-sub)' }};
                 border:1px solid {{ $showScannerPanel ? 'var(--green)' : 'var(--border)' }}">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <rect x="3" y="3" width="5" height="5"/><rect x="16" y="3" width="5" height="5"/>
      <rect x="3" y="16" width="5" height="5"/><path d="M21 16h-3v3"/><path d="M21 21v-2"/><path d="M16 16h2"/>
    </svg>
    <span class="pos-scan-label">{{ $showScannerPanel ? 'Scanner On' : 'Phone Scan' }}</span>
  </button>

</div>

{{-- ══════════════════════════════════════════════
     MAIN SPLIT LAYOUT
══════════════════════════════════════════════ --}}
<div style="flex:1;display:grid;grid-template-columns:1fr 380px;overflow:hidden;min-height:0"
     class="pos-split">

  {{-- ════════════════════════════════
       LEFT — PRODUCT FINDER
  ════════════════════════════════ --}}
  <div style="display:flex;flex-direction:column;overflow:hidden;background:#f0f2f7">

    {{-- Search + Barcode toolbar --}}
    <div style="padding:12px 14px;background:#fff;border-bottom:1px solid #e8ebf4;flex-shrink:0;
                box-shadow:0 1px 4px rgba(26,31,54,.05)">
      <div style="display:flex;gap:8px;align-items:stretch">

        {{-- Product search --}}
        <div style="flex:1;position:relative">
          <svg width="15" height="15" fill="none" stroke="#a8aec8" stroke-width="2" viewBox="0 0 24 24"
               style="position:absolute;left:11px;top:50%;transform:translateY(-50%);pointer-events:none">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input wire:model.live="searchQuery"
                 wire:focus="loadAvailableProducts"
                 type="text"
                 placeholder="Search by name, SKU or barcode..."
                 autocomplete="off"
                 style="width:100%;padding:10px 34px 10px 34px;border:1.5px solid #e2e6f3;
                        border-radius:10px;font-size:13px;background:#fff;
                        color:#1a1f36;outline:none;box-sizing:border-box;font-family:var(--font);
                        transition:border-color .15s"
                 onfocus="this.style.borderColor='#3b6fd4'"
                 onblur="this.style.borderColor='#e2e6f3'">
          @if($searchQuery)
          <button wire:click="closeSearch"
                  style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                         background:none;border:none;cursor:pointer;color:#a8aec8;padding:2px;
                         display:grid;place-items:center">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
          @endif

          {{-- Dropdown results --}}
          @if($showSearchResults)
          <div style="position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:400;
                      background:#fff;border:1.5px solid #e2e6f3;
                      border-radius:14px;box-shadow:0 12px 40px rgba(26,31,54,.18);
                      max-height:320px;overflow-y:auto">
            @if(count($searchResults) > 0)
            <div style="padding:8px 12px;font-size:10px;font-weight:700;letter-spacing:.6px;
                        text-transform:uppercase;color:#a8aec8;
                        border-bottom:1px solid #e8ebf4;background:#fafbfd;border-radius:14px 14px 0 0">
              {{ count($searchResults) }} result{{ count($searchResults) !== 1 ? 's' : '' }} — tap to add
            </div>
            @foreach($searchResults as $result)
            <button wire:click="selectProduct({{ $result['id'] }})"
                    style="width:100%;padding:11px 14px;background:none;border:none;
                           border-bottom:1px solid #f0f2f7;cursor:pointer;
                           display:flex;align-items:center;gap:11px;text-align:left;transition:.1s"
                    onmouseover="this.style.background='#f8f9fc'"
                    onmouseout="this.style.background='none'">
              <span style="width:9px;height:9px;border-radius:50%;flex-shrink:0;
                           background:{{ $result['has_stock'] ? '#0e9e86' : '#e63946' }}"></span>
              <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:#1a1f36;
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  {{ $result['name'] }}
                </div>
                <div style="font-size:11px;color:#a8aec8;margin-top:1px;font-family:var(--mono)">
                  {{ $result['sku'] }}
                  @if($result['category']) · {{ $result['category'] }} @endif
                </div>
              </div>
              <div style="text-align:right;flex-shrink:0">
                <div style="font-size:13px;font-weight:700;color:#3b6fd4;font-family:var(--mono)">
                  {{ $result['selling_price_display'] }}
                </div>
                @php $rBoxes = ($result['stock']['full_boxes'] ?? 0) + ($result['stock']['partial_boxes'] ?? 0); @endphp
                <div style="font-size:10px;font-weight:700;margin-top:2px;
                            color:{{ $result['has_stock'] ? '#0e9e86' : '#e63946' }}">
                  {{ $rBoxes }}&nbsp;{{ $rBoxes === 1 ? 'box' : 'boxes' }}
                </div>
              </div>
            </button>
            @endforeach
            @else
            <div style="padding:22px;text-align:center;color:#a8aec8;font-size:13px">
              No products match "{{ $searchQuery }}"
            </div>
            @endif
          </div>
          @endif
        </div>

        {{-- Barcode input --}}
        <div style="position:relative">
          <svg width="13" height="13" fill="none" stroke="#a8aec8" stroke-width="2" viewBox="0 0 24 24"
               style="position:absolute;left:9px;top:50%;transform:translateY(-50%);pointer-events:none">
            <path d="M2 3h2v18H2zM7 3h1v18H7zM11 3h2v18h-2zM15 3h1v18h-1zM18 3h2v18h-2z"/>
          </svg>
          <input wire:model.live="barcodeInput"
                 type="text"
                 placeholder="Scan..."
                 autocomplete="off"
                 style="width:120px;padding:10px 10px 10px 28px;border:1.5px solid #e2e6f3;
                        border-radius:10px;font-size:12px;background:#fff;
                        color:#1a1f36;outline:none;font-family:var(--mono);transition:border-color .15s"
                 onfocus="this.style.borderColor='#0e9e86'"
                 onblur="this.style.borderColor='#e2e6f3'">
        </div>

      </div>

      {{-- Phone scanner QR panel --}}
      @if($showScannerPanel && $scannerSession)
      <div wire:poll.2000ms
           style="margin-top:10px;padding:12px 14px;background:#f0fdf9;
                  border:1.5px solid #0e9e86;border-radius:12px;
                  display:flex;align-items:center;gap:14px">
        @php $qrAvailable = class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class); @endphp
        @if($qrAvailable)
        <div style="flex-shrink:0;background:#fff;padding:5px;border-radius:8px;
                    box-shadow:0 2px 8px rgba(14,158,134,.15)">
          {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(64)->generate(route('scanner.mobile') . '?session=' . $scannerSession->session_code) !!}
        </div>
        @endif
        <div>
          <div style="font-size:12px;font-weight:800;color:#0e9e86;margin-bottom:3px">
            Phone Scanner Active
          </div>
          <div style="font-size:11px;color:#6b7494;margin-bottom:2px">
            Code: <span style="font-family:var(--mono);font-weight:700;color:#1a1f36">{{ $scannerSession->session_code }}</span>
          </div>
          <div style="font-size:10px;color:#a8aec8">
            {{ route('scanner.mobile') }}?session={{ $scannerSession->session_code }}
          </div>
        </div>
      </div>
      @endif
    </div>

    {{-- ── Parked Carts panel ── --}}
    @if(!empty($heldSales))
    @php
        $anyApproved  = collect($heldSales)->contains('is_approved', true);
        $pendingCount = collect($heldSales)->where('is_approved', false)->count();
    @endphp
    <div style="border-bottom:1px solid var(--border);flex-shrink:0">

      {{-- Strip header --}}
      <button wire:click="$toggle('showHeldPanel')"
              style="width:100%;padding:9px 14px;display:flex;align-items:center;justify-content:space-between;
                     background:{{ $anyApproved ? 'var(--green-glow)' : 'var(--surface2)' }};
                     border:none;cursor:pointer;text-align:left">
        <div style="display:flex;align-items:center;gap:8px">
          {{-- Pulsing dot when approved --}}
          @if($anyApproved)
          <span style="position:relative;display:inline-flex">
            <span style="width:10px;height:10px;border-radius:50%;background:var(--green);display:block"></span>
            <span style="position:absolute;inset:0;border-radius:50%;background:var(--green);
                         animation:ping 1.2s cubic-bezier(0,0,.2,1) infinite;opacity:.6"></span>
          </span>
          @else
          <svg width="13" height="13" fill="none" stroke="var(--amber)" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
          </svg>
          @endif
          <span style="font-size:12px;font-weight:700;
                       color:{{ $anyApproved ? 'var(--green)' : 'var(--amber)' }}">
            @if($anyApproved)
              Approved — ready to resume
            @else
              {{ count($heldSales) }} parked {{ count($heldSales) === 1 ? 'cart' : 'carts' }} · awaiting approval
            @endif
          </span>
        </div>
        <div style="display:flex;align-items:center;gap:6px">
          <span style="font-size:11px;font-weight:700;padding:2px 7px;border-radius:10px;
                       font-family:var(--mono);
                       background:{{ $anyApproved ? 'var(--green-glow)' : 'var(--amber-dim)' }};
                       color:{{ $anyApproved ? 'var(--green)' : 'var(--amber)' }}">
            {{ count($heldSales) }}
          </span>
          <svg width="12" height="12" fill="none" stroke="var(--text-dim)" stroke-width="2" viewBox="0 0 24 24"
               style="transition:transform .2s;{{ $showHeldPanel ? 'transform:rotate(180deg)' : '' }}">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </div>
      </button>

      {{-- Expanded panel --}}
      @if($showHeldPanel)
      <div style="padding:8px 10px 10px;display:flex;flex-direction:column;gap:8px;
                  max-height:340px;overflow-y:auto;background:var(--bg)">
        @foreach($heldSales as $h)
        <div wire:key="held-{{ $h['id'] }}"
             style="border-radius:11px;overflow:hidden;
                    border:1.5px solid {{ $h['is_approved'] ? 'var(--green)' : 'var(--border)' }};
                    background:var(--surface)">

          {{-- Card header --}}
          <div style="padding:9px 12px;background:{{ $h['is_approved'] ? 'var(--green-glow)' : 'var(--surface2)' }};
                      display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:7px">
              <span style="font-size:12px;font-weight:800;font-family:var(--mono);
                           color:{{ $h['is_approved'] ? 'var(--green)' : 'var(--text)' }}">
                {{ $h['reference'] }}
              </span>
              @if($h['is_approved'])
              <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:5px;
                           background:var(--green-glow);color:var(--green)">
                ✓ {{ $h['approved_by'] }}
              </span>
              @else
              <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:5px;
                           background:var(--amber-dim);color:var(--amber)">
                Pending
              </span>
              @endif
            </div>
            <span style="font-size:10px;color:var(--text-dim)">{{ $h['age'] }}</span>
          </div>

          {{-- Meta + total --}}
          <div style="padding:7px 12px;display:flex;align-items:center;justify-content:space-between;
                      border-bottom:1px solid var(--border)">
            <div style="font-size:11px;color:var(--text-dim)">
              {{ $h['seller_name'] }}
              @if($h['customer_name'])
              · <span style="color:var(--text-sub)">{{ $h['customer_name'] }}</span>
              @endif
            </div>
            <span style="font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text)">
              {{ number_format($h['cart_total']) }} RWF
            </span>
          </div>

          {{-- Cart preview --}}
          @if(!empty($h['cart_preview']))
          <div style="padding:6px 12px;border-bottom:1px solid var(--border)">
            @foreach($h['cart_preview'] as $ci)
            <div style="display:flex;align-items:center;justify-content:space-between;
                        padding:3px 0;font-size:11px">
              <div style="display:flex;align-items:center;gap:5px;min-width:0;flex:1">
                @if($ci['modified'])
                <span style="width:5px;height:5px;border-radius:50%;background:var(--amber);
                             flex-shrink:0"></span>
                @endif
                <span style="color:var(--text-sub);overflow:hidden;text-overflow:ellipsis;
                             white-space:nowrap">{{ $ci['name'] }}</span>
                <span style="color:var(--text-dim);flex-shrink:0">
                  × {{ $ci['qty'] }}{{ $ci['is_full_box'] ? ' box' : '' }}
                </span>
              </div>
              <div style="flex-shrink:0;margin-left:8px;font-family:var(--mono)">
                @if($ci['modified'])
                <span style="color:var(--text-dim);text-decoration:line-through;font-size:10px;margin-right:3px">
                  {{ number_format($ci['original_price']) }}
                </span>
                <span style="color:var(--amber);font-weight:700">{{ number_format($ci['price']) }}</span>
                @else
                <span style="color:var(--text-sub)">{{ number_format($ci['price']) }}</span>
                @endif
              </div>
            </div>
            @endforeach
            @if($h['cart_extra'] > 0)
            <div style="font-size:10px;color:var(--text-dim);padding-top:2px">
              + {{ $h['cart_extra'] }} more
            </div>
            @endif
          </div>
          @endif

          {{-- Actions --}}
          <div style="padding:8px 10px;display:flex;gap:6px">
            <button wire:click="resumeHeldSale({{ $h['id'] }})"
                    style="flex:1;padding:7px 0;border-radius:8px;border:none;cursor:pointer;
                           font-size:12px;font-weight:700;color:#fff;
                           background:{{ $h['is_approved'] ? 'var(--green)' : 'var(--accent)' }}">
              {{ $h['is_approved'] ? '▶ Resume & Complete' : '▶ Resume' }}
            </button>
            <button wire:click="discardHeldSale({{ $h['id'] }})"
                    wire:confirm="Discard {{ $h['reference'] }}?"
                    style="padding:7px 11px;border-radius:8px;border:1px solid var(--border);
                           background:var(--surface2);color:var(--text-dim);cursor:pointer;
                           font-size:12px;font-weight:600">
              ✕
            </button>
          </div>

        </div>
        @endforeach
      </div>
      @endif

    </div>
    @endif

    {{-- Polling when there are pending (unapproved) held sales --}}
    @if(collect($heldSales)->contains('is_approved', false))
    <div wire:poll.5000ms="checkApprovals" style="display:none"></div>
    @endif

    <style>
    @keyframes ping {
        75%, 100% { transform: scale(2); opacity: 0; }
    }
    </style>

    {{-- Product grid area --}}
    <div style="flex:1;overflow-y:auto;padding:14px">

      @if(count($allStockProducts) > 0)
      <div class="pos-product-grid">
        @foreach($allStockProducts as $p)
        @php
          $fullBoxes  = $p['stock']['full_boxes'] ?? 0;
          $partBoxes  = $p['stock']['partial_boxes'] ?? 0;
          $totalBoxes = $fullBoxes + $partBoxes;
          $stockItems = $p['stock']['total_items'] ?? 0;
          $stockColor = $totalBoxes > 3 ? '#0e9e86' : ($totalBoxes >= 1 ? '#f59e0b' : '#e63946');
          $stockBg    = $totalBoxes > 3 ? '#f0fdf9' : ($totalBoxes >= 1 ? '#fffbeb' : '#fff5f5');
        @endphp
        <button wire:click="selectProduct({{ $p['id'] }})"
                class="pos-tile"
                style="background:#fff;border:1.5px solid #e2e6f3;
                       border-radius:14px;padding:14px 13px;text-align:left;cursor:pointer;
                       display:flex;flex-direction:column;gap:7px;transition:.14s;width:100%;
                       position:relative;overflow:hidden;box-shadow:0 1px 4px rgba(26,31,54,.04)"
                onmouseover="this.style.borderColor='#3b6fd4';this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(59,111,212,.12)'"
                onmouseout="this.style.borderColor='#e2e6f3';this.style.transform='translateY(0)';this.style.boxShadow='0 1px 4px rgba(26,31,54,.04)'">
          {{-- Stock level accent bar --}}
          <div style="position:absolute;top:0;left:0;right:0;height:3px;
                      background:{{ $stockColor }};opacity:0.7;border-radius:14px 14px 0 0"></div>
          {{-- Category chip --}}
          @if($p['category'])
          <span style="font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                       color:#a8aec8;background:#f0f2f7;padding:2px 7px;
                       border-radius:5px;align-self:flex-start;margin-top:4px">
            {{ $p['category'] }}
          </span>
          @else
          <span style="height:18px;display:block;margin-top:4px"></span>
          @endif
          <div style="font-size:13px;font-weight:700;color:#1a1f36;line-height:1.35;
                      display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
            {{ $p['name'] }}
          </div>
          <div style="font-size:10px;color:#a8aec8;font-family:var(--mono)">{{ $p['sku'] }}</div>
          <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-top:auto">
            <span style="font-size:15px;font-weight:800;color:#3b6fd4;font-family:var(--mono);line-height:1">
              {{ number_format($p['selling_price']) }}
              <span style="font-size:9px;font-weight:600;color:#a8aec8">RWF</span>
            </span>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:2px">
              <span style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:7px;
                           background:{{ $stockBg }};color:{{ $stockColor }}">
                {{ $totalBoxes }}&nbsp;{{ $totalBoxes === 1 ? 'box' : 'boxes' }}
              </span>
              <span style="font-size:9px;color:#a8aec8">{{ $stockItems }}&nbsp;items</span>
            </div>
          </div>
        </button>
        @endforeach
      </div>
      @else
      {{-- Empty state --}}
      <div style="height:100%;min-height:260px;display:flex;flex-direction:column;align-items:center;
                  justify-content:center;text-align:center;color:#a8aec8;padding:40px 20px">
        <div style="width:72px;height:72px;border-radius:20px;background:#fff;
                    display:grid;place-items:center;margin-bottom:20px;
                    border:2px dashed #e2e6f3;box-shadow:0 2px 8px rgba(26,31,54,.05)">
          <svg width="34" height="34" fill="none" stroke="#c8cde0" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
        </div>
        <div style="font-size:16px;font-weight:700;color:#6b7494;margin-bottom:9px">
          Ready to sell
        </div>
        <div style="font-size:13px;color:#a8aec8;line-height:1.7">
          Click the search box to browse products,<br>or scan a barcode to add instantly
        </div>
      </div>
      @endif

    </div>
  </div>

  {{-- ════════════════════════════════
       RIGHT — CART (desktop panel)
  ════════════════════════════════ --}}
  <div class="pos-cart-panel"
       style="display:flex;flex-direction:column;background:#fff;
              border-left:1.5px solid #e2e6f3;overflow:hidden">

    {{-- Cart header --}}
    <div style="padding:14px 16px 12px;border-bottom:1px solid #e8ebf4;flex-shrink:0;
                background:#fff">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:9px">
          <div style="width:32px;height:32px;border-radius:9px;background:#f0f2f7;
                      display:grid;place-items:center">
            <svg width="15" height="15" fill="none" stroke="#6b7494" stroke-width="2.5" viewBox="0 0 24 24">
              <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
          </div>
          <span style="font-size:15px;font-weight:800;color:#1a1f36">Cart</span>
          @if(!empty($cart))
          <span style="background:#3b6fd4;color:#fff;font-size:10px;font-weight:800;
                       padding:2px 8px;border-radius:10px;letter-spacing:.2px">
            {{ count($cart) }}
          </span>
          @endif
        </div>
        @if(!empty($cart))
        <button wire:click="clearCart"
                wire:confirm="Clear all items from cart?"
                style="font-size:11px;font-weight:700;color:#e63946;background:#fff5f5;
                       border:1px solid rgba(230,57,70,.2);padding:4px 10px;border-radius:7px;cursor:pointer">
          Clear all
        </button>
        @endif
      </div>
    </div>

    {{-- Approval-needed banner --}}
    @if(collect($cart)->contains('requires_owner_approval', true) && !$resumingFromHeld)
    <div style="margin:0;padding:10px 14px;background:#fffbeb;border-bottom:1.5px solid #f59e0b;
                flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:8px">
      <div style="display:flex;align-items:center;gap:7px">
        <svg width="14" height="14" fill="none" stroke="#b45309" stroke-width="2" viewBox="0 0 24 24">
          <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <span style="font-size:11px;font-weight:700;color:#b45309">
          Price override needs owner approval
        </span>
      </div>
      <button wire:click="holdSale"
              style="padding:5px 11px;border-radius:7px;border:none;cursor:pointer;
                     background:#0e9e86;color:#fff;font-size:11px;font-weight:700;white-space:nowrap">
        Hold for Approval
      </button>
    </div>
    @endif

    {{-- Resuming banner --}}
    @if($resumingFromHeld)
    @php $resumingRef = collect($heldSales)->firstWhere('id', $resumingFromHeld)['reference'] ?? 'held sale'; @endphp
    <div style="margin:0;padding:10px 14px;background:#f0fdf9;border-bottom:1.5px solid #0e9e86;
                flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:8px">
      <div style="display:flex;align-items:center;gap:7px">
        <svg width="14" height="14" fill="none" stroke="#0e9e86" stroke-width="2" viewBox="0 0 24 24">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
          <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <span style="font-size:11px;font-weight:700;color:#0e9e86">
          Resuming {{ $resumingRef }} — Price override approved
        </span>
      </div>
      <button wire:click="holdSale"
              style="padding:5px 11px;border-radius:7px;border:1px solid #0e9e86;cursor:pointer;
                     background:#fff;color:#0e9e86;font-size:11px;font-weight:700;white-space:nowrap">
        Park Back
      </button>
    </div>
    @endif

    {{-- Cart items list --}}
    <div style="flex:1;overflow-y:auto;padding:10px 12px 6px">
      @forelse($cart as $index => $item)
      <div wire:key="ci-{{ $index }}"
           style="background:#f8f9fc;border:1.5px solid #e8ebf4;
                  border-radius:12px;padding:11px 12px;margin-bottom:8px">
        {{-- Name row --}}
        <div style="display:flex;align-items:flex-start;gap:7px;margin-bottom:7px">
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;color:#1a1f36;
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              {{ $item['product_name'] }}
            </div>
            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:4px">
              <span style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;
                           background:{{ $item['is_full_box'] ? '#eef2ff' : '#f0fdf9' }};
                           color:{{ $item['is_full_box'] ? '#3b6fd4' : '#0e9e86' }}">
                {{ $item['is_full_box'] ? 'BOX' : 'ITEMS' }}
              </span>
              @if(!empty($item['price_modified']))
              <span style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;
                           background:#fffbeb;color:#f59e0b">MODIFIED</span>
              @endif
              @if(!empty($item['requires_owner_approval']))
              <span style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;
                           background:#fff5f5;color:#e63946">APPROVAL</span>
              @endif
            </div>
          </div>
          <div style="display:flex;gap:4px;flex-shrink:0">
            <button wire:click="openEditItem({{ $index }})"
                    style="width:28px;height:28px;border-radius:7px;background:#eef2ff;
                           border:none;cursor:pointer;display:grid;place-items:center;color:#3b6fd4">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
            </button>
            <button wire:click="removeCartItem({{ $index }})"
                    style="width:28px;height:28px;border-radius:7px;background:#fff5f5;
                           border:none;cursor:pointer;display:grid;place-items:center;color:#e63946">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
              </svg>
            </button>
          </div>
        </div>
        {{-- Qty · price · line total --}}
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span style="font-size:11px;color:#a8aec8">
            @if($item['is_full_box'])
              {{ $item['quantity'] }}&nbsp;{{ $item['quantity'] === 1 ? 'box' : 'boxes' }}
              @if(!empty($item['items_per_box']))&nbsp;({{ $item['quantity'] * $item['items_per_box'] }}&nbsp;items)@endif
              &nbsp;·&nbsp;{{ number_format($item['price']) }}&nbsp;RWF/box
            @else
              {{ $item['quantity'] }}&nbsp;items&nbsp;×&nbsp;{{ number_format($item['price']) }}
            @endif
          </span>
          <span style="font-size:15px;font-weight:800;color:#1a1f36;font-family:var(--mono)">
            {{ number_format($item['line_total']) }}
            <span style="font-size:9px;font-weight:600;color:#a8aec8">RWF</span>
          </span>
        </div>
      </div>
      @empty
      <div style="min-height:180px;display:flex;flex-direction:column;
                  align-items:center;justify-content:center;padding:24px;text-align:center">
        <svg width="40" height="40" fill="none" stroke="#d8dce8" stroke-width="1.5" viewBox="0 0 24 24"
             style="margin-bottom:14px">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <div style="font-size:13px;font-weight:700;color:#a8aec8">Cart is empty</div>
        <div style="font-size:12px;color:#c8cde0;margin-top:5px">Tap a product to add it</div>
      </div>
      @endforelse
    </div>

    {{-- Cart footer --}}
    <div style="border-top:1.5px solid #e8ebf4;padding:14px 16px 16px;flex-shrink:0;background:#fff">
      @if(!empty($cart))
      <div style="margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px">
          <span style="font-size:12px;color:#6b7494">Subtotal</span>
          <span style="font-size:12px;font-family:var(--mono);color:#1a1f36">
            {{ number_format($cartTotal) }} RWF
          </span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;
                    padding-top:10px;border-top:1px solid #e8ebf4">
          <span style="font-size:14px;font-weight:700;color:#1a1f36">Total</span>
          <span style="font-size:26px;font-weight:800;color:#3b6fd4;font-family:var(--mono);line-height:1">
            {{ number_format($cartTotal) }}<span style="font-size:12px;font-weight:600;color:#6b7494"> RWF</span>
          </span>
        </div>
      </div>
      @endif

      <button wire:click="openCheckout"
              @if(empty($cart)) disabled @endif
              style="width:100%;padding:14px 16px;
                     background:{{ empty($cart) ? '#f0f2f7' : 'linear-gradient(135deg,#3b6fd4,#6b8dff)' }};
                     color:{{ empty($cart) ? '#a8aec8' : '#fff' }};
                     border:none;border-radius:12px;font-size:15px;font-weight:800;
                     cursor:{{ empty($cart) ? 'not-allowed' : 'pointer' }};
                     display:flex;align-items:center;justify-content:center;gap:9px;
                     font-family:var(--font);
                     box-shadow:{{ empty($cart) ? 'none' : '0 4px 18px rgba(59,111,212,.35)' }};
                     transition:.15s">
        @if(empty($cart))
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
          </svg>
          Add items to checkout
        @else
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
          </svg>
          Checkout — {{ number_format($cartTotal) }} RWF
        @endif
      </button>
    </div>

  </div>
</div>{{-- /pos-split --}}

{{-- ════════════════════════════════
     MOBILE CART FAB
════════════════════════════════ --}}
<button class="pos-cart-fab"
        @click="cartOpen = true"
        style="display:none;position:fixed;bottom:24px;right:20px;z-index:500;
               background:linear-gradient(135deg,#3b6fd4,#6b8dff);color:#fff;
               border:none;border-radius:50px;padding:14px 22px;
               font-size:14px;font-weight:800;cursor:pointer;
               box-shadow:0 8px 28px rgba(59,111,212,.45);
               align-items:center;gap:10px;font-family:var(--font)">
  <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
  </svg>
  <span>
    @if(!empty($cart))
      {{ count($cart) }} item{{ count($cart) !== 1 ? 's' : '' }}
      · {{ number_format($cartTotal) }} RWF
    @else
      Cart
    @endif
  </span>
  @if(!empty($cart))
  <span style="background:rgba(255,255,255,.25);border-radius:50%;
               width:22px;height:22px;font-size:11px;font-weight:800;
               display:grid;place-items:center;flex-shrink:0">
    {{ count($cart) }}
  </span>
  @endif
</button>

{{-- ════════════════════════════════
     MOBILE CART DRAWER
════════════════════════════════ --}}
<div class="pos-cart-drawer-overlay"
     x-show="cartOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0"
     @click.self="cartOpen = false"
     style="position:fixed;inset:0;z-index:600;
            background:rgba(15,20,40,.5);backdrop-filter:blur(3px)">

  <div class="pos-cart-drawer"
       x-show="cartOpen"
       x-transition:enter="transition ease-out duration-250"
       x-transition:enter-start="transform translate-y-full"
       x-transition:enter-end="transform translate-y-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-end="transform translate-y-full"
       style="position:absolute;bottom:0;left:0;right:0;background:#fff;
              border-radius:24px 24px 0 0;max-height:88vh;
              display:flex;flex-direction:column;
              box-shadow:0 -8px 40px rgba(15,20,40,.2)">

    {{-- Drawer handle --}}
    <div style="padding:14px 0 8px;display:flex;justify-content:center;flex-shrink:0">
      <div style="width:44px;height:5px;border-radius:3px;background:#e2e6f3"></div>
    </div>

    {{-- Drawer header --}}
    <div style="padding:0 18px 14px;display:flex;align-items:center;justify-content:space-between;
                border-bottom:1px solid #e8ebf4;flex-shrink:0">
      <div style="display:flex;align-items:center;gap:8px">
        <span style="font-size:17px;font-weight:800;color:#1a1f36">Cart</span>
        @if(!empty($cart))
        <span style="background:#3b6fd4;color:#fff;font-size:10px;font-weight:800;
                     padding:2px 8px;border-radius:10px">{{ count($cart) }}</span>
        @endif
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        @if(!empty($cart))
        <button wire:click="clearCart" wire:confirm="Clear all items from cart?"
                style="font-size:11px;font-weight:700;color:#e63946;background:#fff5f5;
                       border:1px solid rgba(230,57,70,.2);padding:4px 10px;border-radius:7px;cursor:pointer">
          Clear
        </button>
        @endif
        <button @click="cartOpen = false"
                style="width:32px;height:32px;border-radius:9px;background:#f0f2f7;
                       border:none;cursor:pointer;display:grid;place-items:center;color:#6b7494">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
    </div>

    {{-- Drawer cart items --}}
    <div style="flex:1;overflow-y:auto;padding:12px 18px 6px">
      @forelse($cart as $index => $item)
      <div wire:key="mob-ci-{{ $index }}"
           style="background:#f8f9fc;border:1.5px solid #e8ebf4;
                  border-radius:12px;padding:11px 12px;margin-bottom:8px">
        <div style="display:flex;align-items:flex-start;gap:7px;margin-bottom:7px">
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;color:#1a1f36;
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              {{ $item['product_name'] }}
            </div>
            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:4px">
              <span style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;
                           background:{{ $item['is_full_box'] ? '#eef2ff' : '#f0fdf9' }};
                           color:{{ $item['is_full_box'] ? '#3b6fd4' : '#0e9e86' }}">
                {{ $item['is_full_box'] ? 'BOX' : 'ITEMS' }}
              </span>
              @if(!empty($item['price_modified']))
              <span style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;
                           background:#fffbeb;color:#f59e0b">MODIFIED</span>
              @endif
            </div>
          </div>
          <div style="display:flex;gap:4px;flex-shrink:0">
            <button wire:click="openEditItem({{ $index }})" @click="cartOpen = false"
                    style="width:28px;height:28px;border-radius:7px;background:#eef2ff;
                           border:none;cursor:pointer;display:grid;place-items:center;color:#3b6fd4">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
            </button>
            <button wire:click="removeCartItem({{ $index }})"
                    style="width:28px;height:28px;border-radius:7px;background:#fff5f5;
                           border:none;cursor:pointer;display:grid;place-items:center;color:#e63946">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
              </svg>
            </button>
          </div>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span style="font-size:11px;color:#a8aec8">
            @if($item['is_full_box'])
              {{ $item['quantity'] }}&nbsp;{{ $item['quantity'] === 1 ? 'box' : 'boxes' }}
              @if(!empty($item['items_per_box']))&nbsp;({{ $item['quantity'] * $item['items_per_box'] }}&nbsp;items)@endif
              &nbsp;·&nbsp;{{ number_format($item['price']) }}&nbsp;RWF/box
            @else
              {{ $item['quantity'] }}&nbsp;items&nbsp;×&nbsp;{{ number_format($item['price']) }}
            @endif
          </span>
          <span style="font-size:15px;font-weight:800;color:#1a1f36;font-family:var(--mono)">
            {{ number_format($item['line_total']) }}
            <span style="font-size:9px;color:#a8aec8">RWF</span>
          </span>
        </div>
      </div>
      @empty
      <div style="padding:32px 0;text-align:center;color:#a8aec8">
        <svg width="40" height="40" fill="none" stroke="#d8dce8" stroke-width="1.5" viewBox="0 0 24 24"
             style="margin:0 auto 12px;display:block">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <div style="font-size:14px;font-weight:700">Cart is empty</div>
        <div style="font-size:12px;margin-top:5px">Tap a product to add it</div>
      </div>
      @endforelse
    </div>

    {{-- Drawer footer --}}
    <div style="border-top:1.5px solid #e8ebf4;padding:14px 18px;background:#fff;flex-shrink:0">
      @if(!empty($cart))
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
        <span style="font-size:15px;font-weight:700;color:#1a1f36">Total</span>
        <span style="font-size:28px;font-weight:800;color:#3b6fd4;font-family:var(--mono);line-height:1">
          {{ number_format($cartTotal) }}<span style="font-size:12px;color:#6b7494"> RWF</span>
        </span>
      </div>
      @endif
      <button wire:click="openCheckout" @click="cartOpen = false"
              @if(empty($cart)) disabled @endif
              style="width:100%;padding:16px;
                     background:{{ empty($cart) ? '#f0f2f7' : 'linear-gradient(135deg,#3b6fd4,#6b8dff)' }};
                     color:{{ empty($cart) ? '#a8aec8' : '#fff' }};
                     border:none;border-radius:14px;font-size:16px;font-weight:800;
                     cursor:{{ empty($cart) ? 'not-allowed' : 'pointer' }};
                     box-shadow:{{ empty($cart) ? 'none' : '0 4px 20px rgba(59,111,212,.40)' }};
                     font-family:var(--font)">
        @if(empty($cart)) Add items to checkout
        @else Checkout — {{ number_format($cartTotal) }} RWF
        @endif
      </button>
    </div>

  </div>
</div>

@endif{{-- end shop guard --}}

{{-- ══════════════════════════════════════════════
     STAGING MODAL (Add / Edit item)
══════════════════════════════════════════════ --}}
@if($showAddModal && $stagingProduct)
<div style="position:fixed;inset:0;z-index:600;display:flex;align-items:center;
            justify-content:center;background:rgba(15,20,40,.55);backdrop-filter:blur(4px);padding:16px">
  <div style="background:#fff;border-radius:20px;width:500px;max-width:100%;
              max-height:94vh;overflow-y:auto;box-shadow:0 28px 72px rgba(0,0,0,.28)">

    {{-- Modal header --}}
    <div style="padding:20px 22px 16px;border-bottom:1px solid #e8ebf4;
                display:flex;align-items:flex-start;justify-content:space-between;
                position:sticky;top:0;background:#fff;z-index:2;
                border-radius:20px 20px 0 0">
      <div>
        <div style="font-size:17px;font-weight:800;color:#1a1f36">
          {{ $stagingCartIndex !== null ? 'Edit Cart Item' : 'Add to Cart' }}
        </div>
        <div style="font-size:12px;color:#6b7494;margin-top:3px">
          {{ $stagingProduct['name'] }}
        </div>
      </div>
      <button wire:click="closeAddModal"
              style="width:32px;height:32px;border-radius:9px;background:#f0f2f7;
                     border:none;cursor:pointer;display:grid;place-items:center;color:#6b7494">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <div style="padding:20px 22px">

      {{-- Product info strip --}}
      <div style="background:#f8f9fc;border:1.5px solid #e8ebf4;border-radius:12px;
                  padding:13px 16px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center">
        <div>
          <div style="font-size:11px;font-family:var(--mono);color:#a8aec8;margin-bottom:3px">{{ $stagingProduct['sku'] }}</div>
          <div style="font-size:12px;color:#6b7494">{{ $stagingProduct['category'] ?? '—' }}</div>
        </div>
        @php
          $stagingFullBoxes  = $stagingStock['full_boxes'] ?? 0;
          $stagingPartBoxes  = $stagingStock['partial_boxes'] ?? 0;
          $stagingTotalBoxes = $stagingFullBoxes + $stagingPartBoxes;
          $stagingTotalItems = $stagingStock['total_items'] ?? 0;
        @endphp
        <div style="text-align:right">
          <div style="font-size:10px;color:#a8aec8;margin-bottom:3px">In stock at this shop</div>
          <div style="font-size:20px;font-weight:800;font-family:var(--mono);
                      color:{{ $stagingTotalBoxes > 0 ? '#0e9e86' : '#e63946' }};line-height:1">
            {{ $stagingTotalBoxes }}
            <span style="font-size:11px;font-weight:600;color:#a8aec8">{{ $stagingTotalBoxes === 1 ? 'box' : 'boxes' }}</span>
          </div>
          <div style="font-size:10px;color:#a8aec8;margin-top:2px">
            {{ $stagingFullBoxes }}&nbsp;full&nbsp;·&nbsp;{{ $stagingPartBoxes }}&nbsp;partial&nbsp;·&nbsp;{{ $stagingTotalItems }}&nbsp;items
          </div>
        </div>
      </div>

      {{-- Sell mode toggle --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-bottom:20px">
        <button wire:click="$set('stagingMode','box')"
                style="padding:15px 12px;border-radius:12px;cursor:pointer;text-align:center;
                       border:2px solid {{ $stagingMode==='box' ? '#3b6fd4' : '#e2e6f3' }};
                       background:{{ $stagingMode==='box' ? '#eef2ff' : '#f8f9fc' }};transition:.13s">
          <div style="font-size:24px;margin-bottom:6px">📦</div>
          <div style="font-size:13px;font-weight:700;color:{{ $stagingMode==='box' ? '#3b6fd4' : '#6b7494' }}">
            Full Box
          </div>
          <div style="font-size:10px;color:#a8aec8;margin-top:2px">
            {{ $stagingProduct['items_per_box'] }} items / box
          </div>
          <div style="font-size:14px;font-weight:800;color:#3b6fd4;font-family:var(--mono);margin-top:7px">
            {{ number_format($stagingProduct['box_price']) }} RWF
          </div>
        </button>
        @if($stagingProduct['individual_sale_allowed'] ?? true)
        <button wire:click="$set('stagingMode','item')"
                style="padding:15px 12px;border-radius:12px;cursor:pointer;text-align:center;
                       border:2px solid {{ $stagingMode==='item' ? '#3b6fd4' : '#e2e6f3' }};
                       background:{{ $stagingMode==='item' ? '#eef2ff' : '#f8f9fc' }};transition:.13s">
          <div style="font-size:24px;margin-bottom:6px">🏷</div>
          <div style="font-size:13px;font-weight:700;color:{{ $stagingMode==='item' ? '#3b6fd4' : '#6b7494' }}">
            Individual Items
          </div>
          <div style="font-size:10px;color:#a8aec8;margin-top:2px">per item</div>
          <div style="font-size:14px;font-weight:800;color:#3b6fd4;font-family:var(--mono);margin-top:7px">
            {{ number_format($stagingProduct['selling_price']) }} RWF
          </div>
        </button>
        @else
        <div style="padding:15px 12px;border-radius:12px;text-align:center;
                    border:2px solid #e2e6f3;background:#f8f9fc;opacity:.45;cursor:not-allowed">
          <div style="font-size:24px;margin-bottom:6px">🏷</div>
          <div style="font-size:13px;font-weight:700;color:#a8aec8">Individual Items</div>
          <div style="font-size:10px;color:#c8cde0;margin-top:5px">Not allowed for this category</div>
        </div>
        @if(!($stagingProduct['individual_sale_allowed'] ?? true) && $stagingMode === 'item')
        <span wire:init="$set('stagingMode', 'box')"></span>
        @endif
        @endif
      </div>

      {{-- Quantity stepper --}}
      <div style="margin-bottom:18px">
        <label style="display:block;font-size:12px;font-weight:700;color:#6b7494;margin-bottom:8px;
                      text-transform:uppercase;letter-spacing:.5px">Quantity</label>
        <div style="display:flex;align-items:center;gap:12px">
          <button wire:click="$set('stagingQty', max(1, stagingQty - 1))"
                  style="width:42px;height:42px;border-radius:10px;background:#f0f2f7;
                         border:1.5px solid #e2e6f3;cursor:pointer;font-size:22px;
                         display:grid;place-items:center;color:#6b7494;flex-shrink:0">
            &minus;
          </button>
          <input wire:model.live="stagingQty" type="number" min="1"
                 style="flex:1;padding:10px;text-align:center;border:2px solid #3b6fd4;
                        border-radius:10px;font-size:24px;font-weight:800;
                        background:#fff;color:#1a1f36;font-family:var(--mono);outline:none">
          <button wire:click="$set('stagingQty', stagingQty + 1)"
                  style="width:42px;height:42px;border-radius:10px;background:#f0f2f7;
                         border:1.5px solid #e2e6f3;cursor:pointer;font-size:22px;
                         display:grid;place-items:center;color:#6b7494;flex-shrink:0">
            +
          </button>
        </div>
      </div>

      {{-- Unit price --}}
      @if($settingAllowPriceOverride)
      <div style="margin-bottom:18px">
        <label style="display:flex;justify-content:space-between;align-items:center;
                      font-size:12px;font-weight:700;color:#6b7494;margin-bottom:8px;
                      text-transform:uppercase;letter-spacing:.5px">
          <span>Unit Price (RWF)</span>
          @if($stagingPriceModified)
          <span style="font-size:10px;font-weight:700;color:#f59e0b;background:#fffbeb;
                       padding:2px 7px;border-radius:5px">MODIFIED</span>
          @endif
        </label>
        <div style="position:relative">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                       font-size:11px;font-weight:700;color:#a8aec8">RWF</span>
          <input wire:model.live="stagingPrice" type="number" min="0"
                 style="width:100%;padding:11px 12px 11px 44px;box-sizing:border-box;
                        border:2px solid {{ $stagingPriceModified ? '#f59e0b' : '#e2e6f3' }};
                        border-radius:10px;font-size:20px;font-weight:800;
                        background:{{ $stagingPriceModified ? '#fffbeb' : '#fff' }};
                        color:#1a1f36;font-family:var(--mono);outline:none;transition:.13s">
        </div>
        @if($stagingPriceModified)
        <div style="margin-top:8px">
          <input wire:model.live="stagingPriceReason" type="text"
                 placeholder="Reason for price change (required)..."
                 style="width:100%;padding:9px 12px;box-sizing:border-box;
                        border:1.5px solid #f59e0b;border-radius:9px;font-size:13px;
                        background:#fff;color:#1a1f36;outline:none">
        </div>
        @endif
      </div>
      @else
      <div style="margin-bottom:18px">
        <label style="display:block;font-size:12px;font-weight:700;color:#6b7494;margin-bottom:8px;
                      text-transform:uppercase;letter-spacing:.5px">Unit Price (RWF)</label>
        <div style="padding:11px 14px;background:#f8f9fc;border:1.5px solid #e8ebf4;
                    border-radius:10px;font-family:var(--mono);font-size:14px;font-weight:700;
                    color:#6b7494;display:flex;align-items:center;justify-content:space-between">
          <span>{{ number_format($stagingPrice) }} RWF</span>
          <span style="font-size:10px;color:#c8cde0">locked by owner</span>
        </div>
      </div>
      @endif

      {{-- Line total preview --}}
      <div style="background:{{ $stagingPriceModified ? '#fffbeb' : '#eef2ff' }};
                  border-radius:12px;padding:14px 18px;margin-bottom:22px;
                  display:flex;justify-content:space-between;align-items:center;
                  border:1.5px solid {{ $stagingPriceModified ? '#f59e0b' : 'rgba(59,111,212,.2)' }}">
        <span style="font-size:13px;color:#6b7494">
          {{ $stagingQty }} × {{ number_format($stagingPrice) }} RWF
        </span>
        <span style="font-size:26px;font-weight:800;font-family:var(--mono);
                     color:{{ $stagingPriceModified ? '#f59e0b' : '#3b6fd4' }};line-height:1">
          {{ number_format(($stagingQty * $stagingPrice)) }}
          <span style="font-size:12px;font-weight:600;color:#a8aec8">RWF</span>
        </span>
      </div>

      {{-- Buttons --}}
      <div style="display:grid;grid-template-columns:auto 1fr;gap:9px">
        <button wire:click="closeAddModal"
                style="padding:13px 22px;background:#f0f2f7;color:#6b7494;
                       border:1.5px solid #e2e6f3;border-radius:12px;font-size:14px;
                       font-weight:700;cursor:pointer">
          Cancel
        </button>
        <button wire:click="confirmAddToCart"
                wire:loading.attr="disabled"
                style="padding:13px 20px;background:linear-gradient(135deg,#3b6fd4,#6b8dff);
                       color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:800;
                       cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;
                       box-shadow:0 4px 16px rgba(59,111,212,.32);font-family:var(--font)">
          <span wire:loading.remove>
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                 viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:5px">
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
            justify-content:center;background:rgba(15,20,40,.6);backdrop-filter:blur(5px);padding:12px">
  <div style="background:#fff;border-radius:20px;width:720px;max-width:100%;
              max-height:96vh;overflow-y:auto;box-shadow:0 32px 80px rgba(0,0,0,.28)">

    {{-- Header --}}
    <div style="padding:20px 24px 16px;border-bottom:1px solid #e8ebf4;
                display:flex;align-items:center;justify-content:space-between;
                position:sticky;top:0;background:#fff;z-index:2;border-radius:20px 20px 0 0">
      <div>
        <div style="font-size:19px;font-weight:800;color:#1a1f36">Checkout</div>
        <div style="font-size:12px;color:#6b7494;margin-top:2px">Review order and process payment</div>
      </div>
      <button wire:click="$set('showCheckoutModal', false)"
              style="width:34px;height:34px;border-radius:10px;background:#f0f2f7;
                     border:none;cursor:pointer;display:grid;place-items:center;color:#6b7494">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    {{-- Two-column layout on desktop --}}
    <div class="checkout-grid">

      {{-- LEFT: Order + Customer --}}
      <div style="padding:22px 24px;border-right:1px solid #e8ebf4">

        {{-- Order recap --}}
        <div style="background:#eef2ff;border:1.5px solid rgba(59,111,212,.18);
                    border-radius:14px;padding:16px 18px;margin-bottom:22px">
          <div style="font-size:10px;font-weight:800;letter-spacing:.7px;text-transform:uppercase;
                      color:#3b6fd4;margin-bottom:12px">Order Summary</div>
          @foreach($cart as $item)
          <div style="display:flex;justify-content:space-between;margin-bottom:6px">
            <span style="font-size:12px;color:#6b7494">
              {{ $item['product_name'] }}
              @if($item['is_full_box'])
                × {{ $item['quantity'] }}&nbsp;{{ $item['quantity'] === 1 ? 'box' : 'boxes' }}
              @else
                × {{ $item['quantity'] }}&nbsp;items
              @endif
            </span>
            <span style="font-size:12px;font-weight:700;font-family:var(--mono);color:#1a1f36">
              {{ number_format($item['line_total']) }}
            </span>
          </div>
          @endforeach
          <div style="display:flex;justify-content:space-between;align-items:center;
                      padding-top:10px;border-top:1px solid rgba(59,111,212,.15);margin-top:6px">
            <span style="font-size:14px;font-weight:700;color:#1a1f36">Total</span>
            <span style="font-size:28px;font-weight:800;color:#3b6fd4;font-family:var(--mono);line-height:1">
              {{ number_format($cartTotal) }}<span style="font-size:13px;font-weight:600;color:#6b7494"> RWF</span>
            </span>
          </div>
        </div>

        {{-- Customer Selection --}}
        <div>
          <div style="font-size:11px;font-weight:800;color:#a8aec8;text-transform:uppercase;
                      letter-spacing:.6px;margin-bottom:10px">Customer</div>

          @if($selectedCustomerId)
          <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;
                      background:#eef2ff;border:1.5px solid rgba(59,111,212,.2);border-radius:12px">
            <div>
              <div style="font-size:14px;font-weight:700;color:#1a1f36;margin-bottom:3px">
                {{ $selectedCustomerName }}
              </div>
              <div style="font-size:12px;color:#6b7494;font-family:var(--mono)">
                {{ $selectedCustomerPhone }}
                @if($selectedCustomerOutstandingBalance > 0)
                <span style="margin-left:8px;padding:2px 7px;background:#fffbeb;
                             color:#f59e0b;border-radius:6px;font-weight:700">
                  Credit: {{ number_format($selectedCustomerOutstandingBalance) }} RWF
                </span>
                @endif
              </div>
            </div>
            <button wire:click="clearCustomer"
                    style="width:28px;height:28px;border-radius:50%;border:none;background:#fff5f5;
                           color:#e63946;cursor:pointer;font-size:18px;display:grid;place-items:center;
                           line-height:1;padding:0">×</button>
          </div>
          @elseif($showNewCustomerForm)
          <div style="background:#f8f9fc;border:1.5px solid #e2e6f3;border-radius:12px;padding:16px">
            <div style="font-size:13px;font-weight:800;color:#1a1f36;margin-bottom:12px">
              Register New Customer
            </div>
            <div style="display:grid;gap:10px">
              <div>
                <label style="display:block;font-size:11px;font-weight:600;color:#6b7494;margin-bottom:5px">
                  Name *
                </label>
                <input wire:model="newCustomerName" type="text" placeholder="Full name"
                    style="width:100%;padding:9px 12px;border:1.5px solid #e2e6f3;border-radius:9px;
                           background:#fff;color:#1a1f36;font-size:13px;box-sizing:border-box;outline:none">
              </div>
              <div>
                <label style="display:block;font-size:11px;font-weight:600;color:#6b7494;margin-bottom:5px">
                  Phone *
                </label>
                <input wire:model="newCustomerPhone" type="text" placeholder="+250..."
                    style="width:100%;padding:9px 12px;border:1.5px solid #e2e6f3;border-radius:9px;
                           background:#fff;color:#1a1f36;font-size:13px;box-sizing:border-box;
                           font-family:var(--mono);outline:none">
              </div>
              <div>
                <label style="display:block;font-size:11px;font-weight:600;color:#6b7494;margin-bottom:5px">
                  Email
                </label>
                <input wire:model="newCustomerEmail" type="email" placeholder="email@example.com"
                    style="width:100%;padding:9px 12px;border:1.5px solid #e2e6f3;border-radius:9px;
                           background:#fff;color:#1a1f36;font-size:13px;box-sizing:border-box;outline:none">
              </div>
              <div style="display:flex;gap:8px;margin-top:2px">
                <button wire:click="cancelNewCustomer"
                    style="flex:1;padding:9px;border-radius:9px;border:1.5px solid #e2e6f3;
                           background:#fff;font-size:13px;font-weight:600;cursor:pointer;color:#6b7494">
                  Cancel
                </button>
                <button wire:click="saveNewCustomer"
                    style="flex:1;padding:9px;border-radius:9px;border:none;
                           background:#3b6fd4;color:#fff;font-size:13px;font-weight:700;cursor:pointer">
                  Save &amp; Select
                </button>
              </div>
            </div>
          </div>
          @else
          <div style="position:relative">
            <input wire:model.live="customerSearch" type="text" placeholder="Search by name or phone..."
                onfocus="@this.set('showCustomerSearch', true)"
                style="width:100%;padding:10px 12px;border:1.5px solid #e2e6f3;
                       border-radius:10px;font-size:13px;background:#fff;
                       color:#1a1f36;outline:none;box-sizing:border-box;transition:.13s"
                onfocus="this.style.borderColor='#3b6fd4'"
                onblur="this.style.borderColor='#e2e6f3'">
            @if($showCustomerSearch && count($customerResults) > 0)
            <div style="position:absolute;top:100%;left:0;right:0;margin-top:5px;
                        background:#fff;border:1.5px solid #e2e6f3;border-radius:12px;
                        box-shadow:0 10px 30px rgba(0,0,0,.14);max-height:200px;overflow-y:auto;z-index:10">
              @foreach($customerResults as $customer)
              <button wire:click="selectCustomer({{ $customer['id'] }})" type="button"
                  style="width:100%;padding:11px 14px;text-align:left;border:none;background:transparent;
                         cursor:pointer;border-bottom:1px solid #f0f2f7;transition:.1s"
                  onmouseover="this.style.background='#f8f9fc'"
                  onmouseout="this.style.background='transparent'">
                <div style="font-size:13px;font-weight:700;color:#1a1f36;margin-bottom:2px">
                  {{ $customer['name'] }}
                </div>
                <div style="font-size:11px;color:#a8aec8;font-family:var(--mono)">
                  {{ $customer['phone'] }}
                  @if($customer['outstanding_balance'] > 0)
                  <span style="margin-left:6px;color:#f59e0b;font-weight:700">
                    Credit: {{ number_format($customer['outstanding_balance']) }}
                  </span>
                  @endif
                </div>
              </button>
              @endforeach
            </div>
            @endif
          </div>
          <button wire:click="showCreateCustomerForm" type="button"
              style="width:100%;margin-top:9px;padding:9px;border-radius:10px;
                     border:1.5px dashed #d8dce8;background:transparent;
                     font-size:12px;font-weight:700;cursor:pointer;color:#a8aec8;transition:.13s"
              onmouseover="this.style.borderColor='#3b6fd4';this.style.color='#3b6fd4'"
              onmouseout="this.style.borderColor='#d8dce8';this.style.color='#a8aec8'">
            + Register New Customer
          </button>
          @endif
        </div>

      </div>{{-- /checkout left --}}

      {{-- RIGHT: Payment + Notes + Complete --}}
      <div style="padding:22px 24px">

        {{-- Payment section --}}
        <div style="margin-bottom:20px">
          <div style="font-size:11px;font-weight:800;color:#a8aec8;text-transform:uppercase;
                      letter-spacing:.6px;margin-bottom:10px">Payment</div>

          {{-- Balance summary bar --}}
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;
                      background:#f8f9fc;border:1.5px solid #e8ebf4;border-radius:12px;
                      overflow:hidden;margin-bottom:12px">
            <div style="padding:10px 12px;text-align:center">
              <div style="font-size:10px;color:#a8aec8;margin-bottom:3px">Total</div>
              <div style="font-size:14px;font-weight:800;font-family:var(--mono);color:#1a1f36">
                {{ number_format($cartTotal) }}
              </div>
            </div>
            <div style="padding:10px 12px;text-align:center;border-left:1px solid #e8ebf4;border-right:1px solid #e8ebf4">
              <div style="font-size:10px;color:#a8aec8;margin-bottom:3px">Allocated</div>
              <div style="font-size:14px;font-weight:800;font-family:var(--mono);
                          color:{{ $this->totalAllocated >= $cartTotal ? '#0e9e86' : '#1a1f36' }}">
                {{ number_format($this->totalAllocated) }}
              </div>
            </div>
            <div style="padding:10px 12px;text-align:center">
              <div style="font-size:10px;color:#a8aec8;margin-bottom:3px">Remaining</div>
              <div style="font-size:14px;font-weight:800;font-family:var(--mono);
                          color:{{ $this->remainingBalance === 0 ? '#0e9e86' : '#e63946' }}">
                {{ $this->remainingBalance === 0 ? '✓ 0' : number_format($this->remainingBalance) }}
              </div>
            </div>
          </div>

          {{-- Credit warning --}}
          @if($creditWarningVisible)
          <div style="padding:11px 14px;background:#fffbeb;border:1.5px solid #f59e0b;
                      border-radius:10px;margin-bottom:12px">
            <div style="font-size:11px;font-weight:800;color:#f59e0b;margin-bottom:3px">
              Outstanding Balance
            </div>
            <div style="font-size:12px;color:#6b7494">{{ $creditWarningMessage }}</div>
          </div>
          @endif

          {{-- Payment channel rows --}}
          <div style="border:1.5px solid #e2e6f3;border-radius:12px;overflow:hidden">
            {{-- Cash --}}
            <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid #e8ebf4;background:#fff">
              <div style="width:34px;height:34px;border-radius:9px;background:#f0fdf9;
                          display:grid;place-items:center;flex-shrink:0;font-size:16px">💵</div>
              <div style="flex:1">
                <div style="font-size:11px;font-weight:700;color:#6b7494;margin-bottom:4px">Cash</div>
                <input wire:model.blur="payAmt_cash" type="number" min="0" placeholder="0"
                    style="width:100%;padding:7px 10px;border:1.5px solid #e2e6f3;border-radius:8px;
                           background:#fff;color:#1a1f36;font-size:14px;font-family:var(--mono);
                           outline:none;transition:.13s"
                    onfocus="this.style.borderColor='#0e9e86'"
                    onblur="this.style.borderColor='#e2e6f3'">
              </div>
            </div>
            {{-- Card --}}
            <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid #e8ebf4;background:#fff">
              <div style="width:34px;height:34px;border-radius:9px;background:#eef2ff;
                          display:grid;place-items:center;flex-shrink:0;font-size:16px">💳</div>
              <div style="flex:1">
                <div style="font-size:11px;font-weight:700;color:#6b7494;margin-bottom:4px">Card</div>
                <input wire:model.blur="payAmt_card" type="number" min="0" placeholder="0"
                    style="width:100%;padding:7px 10px;border:1.5px solid #e2e6f3;border-radius:8px;
                           background:#fff;color:#1a1f36;font-size:14px;font-family:var(--mono);
                           outline:none;transition:.13s"
                    onfocus="this.style.borderColor='#3b6fd4'"
                    onblur="this.style.borderColor='#e2e6f3'">
                @if($payAmt_card > 0)
                <input wire:model="payRef_card" type="text" placeholder="Reference (optional)"
                    style="width:100%;margin-top:5px;padding:6px 10px;border:1px solid #e2e6f3;border-radius:7px;
                           background:#fff;color:#1a1f36;font-size:11px;outline:none">
                @endif
              </div>
            </div>
            {{-- Mobile Money --}}
            <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid #e8ebf4;background:#fff">
              <div style="width:34px;height:34px;border-radius:9px;background:#f0fdf9;
                          display:grid;place-items:center;flex-shrink:0;font-size:16px">📱</div>
              <div style="flex:1">
                <div style="font-size:11px;font-weight:700;color:#6b7494;margin-bottom:4px">Mobile Money</div>
                <input wire:model.blur="payAmt_mobile_money" type="number" min="0" placeholder="0"
                    style="width:100%;padding:7px 10px;border:1.5px solid #e2e6f3;border-radius:8px;
                           background:#fff;color:#1a1f36;font-size:14px;font-family:var(--mono);
                           outline:none;transition:.13s"
                    onfocus="this.style.borderColor='#0e9e86'"
                    onblur="this.style.borderColor='#e2e6f3'">
                @if($payAmt_mobile_money > 0)
                <input wire:model="payRef_mobile_money" type="text" placeholder="Reference (optional)"
                    style="width:100%;margin-top:5px;padding:6px 10px;border:1px solid #e2e6f3;border-radius:7px;
                           background:#fff;color:#1a1f36;font-size:11px;outline:none">
                @endif
              </div>
            </div>
            {{-- Bank Transfer --}}
            <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid #e8ebf4;background:#fff">
              <div style="width:34px;height:34px;border-radius:9px;background:#eef2ff;
                          display:grid;place-items:center;flex-shrink:0;font-size:16px">🏦</div>
              <div style="flex:1">
                <div style="font-size:11px;font-weight:700;color:#6b7494;margin-bottom:4px">Bank Transfer</div>
                <input wire:model.blur="payAmt_bank_transfer" type="number" min="0" placeholder="0"
                    style="width:100%;padding:7px 10px;border:1.5px solid #e2e6f3;border-radius:8px;
                           background:#fff;color:#1a1f36;font-size:14px;font-family:var(--mono);
                           outline:none;transition:.13s"
                    onfocus="this.style.borderColor='#3b6fd4'"
                    onblur="this.style.borderColor='#e2e6f3'">
                @if($payAmt_bank_transfer > 0)
                <input wire:model="payRef_bank_transfer" type="text" placeholder="Reference (optional)"
                    style="width:100%;margin-top:5px;padding:6px 10px;border:1px solid #e2e6f3;border-radius:7px;
                           background:#fff;color:#1a1f36;font-size:11px;outline:none">
                @endif
              </div>
            </div>
            {{-- Credit --}}
            @if($settingAllowCreditSales)
            <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;
                        background:{{ $payAmt_credit > 0 ? '#fffbeb' : '#fff' }}">
              <div style="width:34px;height:34px;border-radius:9px;background:#fffbeb;
                          display:grid;place-items:center;flex-shrink:0;font-size:16px">📋</div>
              <div style="flex:1">
                <div style="font-size:11px;font-weight:700;color:#6b7494;margin-bottom:4px">
                  Credit
                  @if(!$selectedCustomerId && $settingCreditRequiresCustomer)
                  <span style="color:#f59e0b;font-weight:600;font-size:10px"> · select customer first</span>
                  @endif
                </div>
                <input wire:model.blur="payAmt_credit" type="number" min="0" placeholder="0"
                    {{ ($settingCreditRequiresCustomer && !$selectedCustomerId) ? 'disabled' : '' }}
                    style="width:100%;padding:7px 10px;border:1.5px solid {{ $payAmt_credit > 0 ? '#f59e0b' : '#e2e6f3' }};
                           border-radius:8px;background:#fff;color:#1a1f36;font-size:14px;
                           font-family:var(--mono);outline:none;transition:.13s">
              </div>
            </div>
            @endif
          </div>
        </div>

        {{-- Notes --}}
        <div style="margin-bottom:20px">
          <label style="display:block;font-size:11px;font-weight:800;color:#a8aec8;
                        text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px">
            Notes <span style="font-weight:400;text-transform:none">(optional)</span>
          </label>
          <textarea wire:model="notes" rows="2" placeholder="Sale notes..."
                    style="width:100%;padding:10px 12px;border:1.5px solid #e2e6f3;
                           border-radius:10px;font-size:13px;background:#fff;
                           color:#1a1f36;outline:none;resize:none;box-sizing:border-box;
                           font-family:var(--font);transition:.13s"
                    onfocus="this.style.borderColor='#3b6fd4'"
                    onblur="this.style.borderColor='#e2e6f3'"></textarea>
        </div>

        {{-- Complete Sale --}}
        <button wire:click="completeSale"
                wire:loading.attr="disabled"
                style="width:100%;padding:16px;background:linear-gradient(135deg,#0e9e86,#16c49a);
                       color:#fff;border:none;border-radius:14px;font-size:16px;font-weight:800;
                       cursor:pointer;display:flex;align-items:center;justify-content:center;gap:9px;
                       box-shadow:0 6px 22px rgba(14,158,134,.40);font-family:var(--font)">
          <span wire:loading.remove>
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5"
                 viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            Complete Sale
          </span>
          <span wire:loading>Processing...</span>
        </button>

      </div>{{-- /checkout right --}}

    </div>{{-- /checkout-grid --}}

  </div>
</div>
@endif

{{-- ══════════════════════════════════════════════
     RECEIPT MODAL
══════════════════════════════════════════════ --}}
@if($showReceiptModal && $completedSale)
<div style="position:fixed;inset:0;z-index:700;display:flex;align-items:center;
            justify-content:center;background:rgba(15,20,40,.65);backdrop-filter:blur(6px);padding:16px">
  <div style="background:#fff;border-radius:22px;width:420px;max-width:100%;
              box-shadow:0 32px 80px rgba(0,0,0,.30);overflow:hidden">

    {{-- Green success banner --}}
    <div style="background:linear-gradient(135deg,#0e9e86,#16c49a);padding:30px;text-align:center">
      <div style="width:60px;height:60px;border-radius:50%;background:rgba(255,255,255,.22);
                  display:grid;place-items:center;margin:0 auto 14px">
        <svg width="30" height="30" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <div style="font-size:22px;font-weight:800;color:#fff;letter-spacing:-.3px">Sale Complete!</div>
      <div style="font-size:12px;color:rgba(255,255,255,.8);margin-top:6px;font-family:var(--mono)">
        {{ $completedSale['sale_number'] ?? '' }}
      </div>
    </div>

    {{-- Receipt lines --}}
    <div style="padding:22px 24px">
      @foreach(($completedSale['items'] ?? []) as $item)
      <div style="display:flex;justify-content:space-between;margin-bottom:7px">
        <span style="font-size:12px;color:#6b7494">
          {{ $item['product_name'] ?? $item['name'] ?? '' }} × {{ $item['quantity'] }}
        </span>
        <span style="font-size:12px;font-weight:700;font-family:var(--mono);color:#1a1f36">
          {{ number_format(($item['line_total'] ?? 0)) }}
        </span>
      </div>
      @endforeach

      <div style="border-top:2px solid #e8ebf4;margin:14px 0 12px;padding-top:14px;
                  display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:15px;font-weight:700;color:#1a1f36">Total Paid</span>
        <span style="font-size:28px;font-weight:800;font-family:var(--mono);color:#0e9e86;line-height:1">
          {{ number_format(($completedSale['total'] ?? 0)) }}
          <span style="font-size:13px;font-weight:600;color:#a8aec8">RWF</span>
        </span>
      </div>

      {{-- Payment breakdown --}}
      @if($completedSale->payments && $completedSale->payments->count() > 0)
      <div style="margin:14px 0;padding:12px 14px;background:#f8f9fc;border-radius:10px">
        <div style="font-size:10px;font-weight:800;color:#a8aec8;text-transform:uppercase;
                    letter-spacing:.6px;margin-bottom:10px">Payment Breakdown</div>
        @foreach($completedSale->payments as $payment)
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <span style="font-size:12px;color:#6b7494">
            {{ match($payment->payment_method->value) {
              'cash' => '💵 Cash',
              'card' => '💳 Card',
              'mobile_money' => '📱 Mobile Money',
              'bank_transfer' => '🏦 Bank Transfer',
              'credit' => '📋 Credit',
              default => $payment->payment_method->label()
            } }}
            @if($payment->reference)
            <span style="color:#c8cde0;font-family:var(--mono)"> ({{ $payment->reference }})</span>
            @endif
          </span>
          <span style="font-size:13px;font-weight:700;font-family:var(--mono);
                       color:{{ $payment->payment_method->value === 'credit' ? '#f59e0b' : '#1a1f36' }}">
            {{ number_format($payment->amount) }}
          </span>
        </div>
        @endforeach
      </div>
      @endif

      <div style="font-size:12px;color:#c8cde0;text-align:center;margin-bottom:20px">
        {{ now()->format('d M Y H:i') }}
      </div>

      <button wire:click="$set('showReceiptModal', false)"
              style="width:100%;padding:15px;background:linear-gradient(135deg,#3b6fd4,#6b8dff);
                     color:#fff;border:none;border-radius:14px;font-size:16px;font-weight:800;
                     cursor:pointer;box-shadow:0 4px 18px rgba(59,111,212,.32);font-family:var(--font)">
        New Sale
      </button>
    </div>

  </div>
</div>
@endif

{{-- ─── Responsive styles ─── --}}
<style>
.pos-product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(158px, 1fr));
  gap: 11px;
}

.checkout-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
}

/*
 * Overlay: hidden by default (desktop).
 * Alpine x-show manages visibility by toggling inline display:none.
 * The mobile media query below (no !important) allows the overlay to show
 * when Alpine removes the inline style (x-show=true).
 * On desktop, this base rule re-hides it if Alpine ever removes the inline style.
 */
.pos-cart-drawer-overlay { display: none; }

/* ── Tablet: narrow right cart panel ── */
@media (max-width: 1100px) {
  .pos-split { grid-template-columns: 1fr 320px !important; }
}

/* ── Mobile: single column, hide cart panel, show FAB + drawer ── */
@media (max-width: 860px) {
  .pos-split {
    grid-template-columns: 1fr !important;
    overflow: auto !important;
  }
  .pos-root {
    height: auto !important;
    min-height: 100vh;
    overflow: auto !important;
  }
  .pos-cart-panel { display: none !important; }
  .pos-cart-fab   { display: flex !important; }
  /* No !important here — Alpine's inline display:none (x-show=false) wins over this.
     When Alpine sets x-show=true it removes the inline style, letting this rule show the overlay. */
  .pos-cart-drawer-overlay { display: block; }
  .pos-cart-chip  { display: none !important; }
  .pos-scan-label { display: none; }
  .pos-shop-name  { max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .pos-clock      { display: none; }
  .pos-product-grid {
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 9px;
  }
  .checkout-grid {
    grid-template-columns: 1fr;
  }
  .checkout-grid > div:first-child {
    border-right: none !important;
    border-bottom: 1px solid #e8ebf4;
  }
}

/* ── Very small phones ── */
@media (max-width: 400px) {
  .pos-product-grid {
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }
  .pos-tile { padding: 11px 10px !important; }
}
</style>

</div>{{-- /pos-root --}}
