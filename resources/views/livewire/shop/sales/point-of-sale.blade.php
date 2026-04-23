@if($sessionBlocked)
    <x-session-gate-blocked
        :reason="$sessionBlockReason"
        :session-date="$blockedSessionDate"
        :session-id="$blockedSessionId"
    />
@else
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

  {{-- Receipts link --}}
  <a href="{{ route('shop.receipts') }}"
     style="display:flex;align-items:center;gap:6px;padding:6px 11px;border-radius:8px;
            font-size:12px;font-weight:600;cursor:pointer;transition:.15s;text-decoration:none;
            background:var(--surface2);color:var(--text-sub);border:1px solid var(--border);"
     onmouseover="this.style.background='var(--accent-dim)';this.style.color='var(--accent)'"
     onmouseout="this.style.background='var(--surface2)';this.style.color='var(--text-sub)'">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <polyline points="6 9 6 2 18 2 18 9"/>
      <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
      <rect x="6" y="14" width="12" height="8"/>
    </svg>
    <span class="pos-scan-label">Receipts</span>
  </a>

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
        $savedCarts   = collect($heldSales)->where('needs_approval', false);
        $heldForApproval = collect($heldSales)->where('needs_approval', true);
        $anyApproved  = $heldForApproval->contains('is_approved', true);
        $savedCount   = $savedCarts->count();
        $heldCount    = $heldForApproval->count();
        // Strip colour: green if anything approved, blue if only saved carts, amber if pending approval
        $stripBg      = $anyApproved ? 'var(--green-glow)' : ($heldCount === 0 ? 'rgba(59,111,212,.06)' : 'var(--surface2)');
        $stripColor   = $anyApproved ? 'var(--green)' : ($heldCount === 0 ? '#3b6fd4' : 'var(--amber)');
    @endphp
    <div style="border-bottom:1px solid var(--border);flex-shrink:0">

      {{-- Strip header --}}
      <button wire:click="$toggle('showHeldPanel')"
              style="width:100%;padding:9px 14px;display:flex;align-items:center;justify-content:space-between;
                     background:{{ $stripBg }};border:none;cursor:pointer;text-align:left">
        <div style="display:flex;align-items:center;gap:8px">
          @if($anyApproved)
          <span style="position:relative;display:inline-flex">
            <span style="width:10px;height:10px;border-radius:50%;background:var(--green);display:block"></span>
            <span style="position:absolute;inset:0;border-radius:50%;background:var(--green);
                         animation:ping 1.2s cubic-bezier(0,0,.2,1) infinite;opacity:.6"></span>
          </span>
          @elseif($savedCount > 0 && $heldCount === 0)
          <svg width="13" height="13" fill="none" stroke="#3b6fd4" stroke-width="2" viewBox="0 0 24 24">
            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
          </svg>
          @else
          <svg width="13" height="13" fill="none" stroke="var(--amber)" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
          </svg>
          @endif
          <span style="font-size:12px;font-weight:700;color:{{ $stripColor }}">
            @if($anyApproved)
              Cart approved — ready to complete
            @elseif($savedCount > 0 && $heldCount === 0)
              {{ $savedCount }} saved {{ $savedCount === 1 ? 'cart' : 'carts' }} — click to resume
            @elseif($savedCount > 0)
              {{ $savedCount }} saved · {{ $heldCount }} awaiting approval
            @else
              {{ $heldCount }} {{ $heldCount === 1 ? 'cart' : 'carts' }} awaiting approval
            @endif
          </span>
        </div>
        <div style="display:flex;align-items:center;gap:6px">
          <span style="font-size:11px;font-weight:700;padding:2px 7px;border-radius:10px;
                       font-family:var(--mono);background:{{ $stripBg }};color:{{ $stripColor }}">
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
          @php
            $cardBg    = $h['is_approved'] ? 'var(--green-glow)' : ($h['needs_approval'] ? 'var(--surface2)' : 'rgba(59,111,212,.05)');
            $refColor  = $h['is_approved'] ? 'var(--green)' : ($h['needs_approval'] ? 'var(--text)' : '#3b6fd4');
          @endphp
          {{-- Card header with inline action buttons --}}
          <div style="padding:8px 10px;background:{{ $cardBg }};
                      display:flex;align-items:center;gap:8px">
            {{-- Left: reference + badge --}}
            <div style="display:flex;align-items:center;gap:6px;min-width:0;flex:1">
              <span style="font-size:12px;font-weight:800;font-family:var(--mono);color:{{ $refColor }};white-space:nowrap">
                {{ $h['reference'] }}
              </span>
              @if($h['is_approved'])
              <span style="font-size:10px;font-weight:700;padding:1px 6px;border-radius:5px;
                           background:var(--green-glow);color:var(--green);white-space:nowrap">
                ✓ Approved
              </span>
              @elseif(!$h['needs_approval'])
              <span style="font-size:10px;font-weight:700;padding:1px 6px;border-radius:5px;
                           background:rgba(59,111,212,.10);color:#3b6fd4;white-space:nowrap">
                Saved
              </span>
              @else
              <span style="font-size:10px;font-weight:700;padding:1px 6px;border-radius:5px;
                           background:var(--amber-dim);color:var(--amber);white-space:nowrap">
                Pending
              </span>
              @endif
              <span style="font-size:10px;color:var(--text-dim);white-space:nowrap">{{ $h['age'] }}</span>
            </div>
            {{-- Right: Resume + Discard buttons always visible --}}
            <button wire:click="resumeHeldSale({{ $h['id'] }})"
                    style="padding:5px 12px;border-radius:7px;border:none;cursor:pointer;
                           font-size:11px;font-weight:700;color:#fff;white-space:nowrap;
                           background:{{ $h['is_approved'] ? 'var(--green)' : ($h['needs_approval'] ? 'var(--accent)' : '#3b6fd4') }}">
              @if($h['is_approved']) ▶ Complete
              @elseif(!$h['needs_approval']) ▶ Resume
              @else ▶ Resume
              @endif
            </button>
            <button wire:click="discardHeldSale({{ $h['id'] }})"
                    wire:confirm="Discard {{ $h['reference'] }}?"
                    style="padding:5px 9px;border-radius:7px;border:1px solid var(--border);
                           background:transparent;color:var(--text-dim);cursor:pointer;font-size:11px">
              ✕
            </button>
          </div>

          {{-- Meta + total --}}
          <div style="padding:6px 12px;display:flex;align-items:center;justify-content:space-between;
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
          <div style="padding:5px 12px">
            @foreach($h['cart_preview'] as $ci)
            <div style="display:flex;align-items:center;justify-content:space-between;
                        padding:2px 0;font-size:11px">
              <div style="display:flex;align-items:center;gap:5px;min-width:0;flex:1">
                @if($ci['modified'])
                <span style="width:5px;height:5px;border-radius:50%;background:var(--amber);flex-shrink:0"></span>
                @endif
                <span style="color:var(--text-sub);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $ci['name'] }}</span>
                <span style="color:var(--text-dim);flex-shrink:0">× {{ $ci['qty'] }}{{ $ci['is_full_box'] ? ' box' : '' }}</span>
              </div>
              <span style="flex-shrink:0;margin-left:8px;font-family:var(--mono);color:var(--text-sub)">
                {{ number_format($ci['price']) }}
              </span>
            </div>
            @endforeach
            @if($h['cart_extra'] > 0)
            <div style="font-size:10px;color:var(--text-dim);padding-top:1px">+ {{ $h['cart_extra'] }} more</div>
            @endif
          </div>
          @endif

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

      @if(!empty($cart))
      <button wire:click="saveCart"
              style="width:100%;padding:10px 16px;margin-bottom:8px;
                     background:var(--surface2);color:var(--text-sub);
                     border:1.5px solid var(--border);border-radius:12px;
                     font-size:13px;font-weight:700;cursor:pointer;
                     display:flex;align-items:center;justify-content:center;gap:7px;
                     font-family:var(--font);transition:.15s">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/>
          <polyline points="7 3 7 8 15 8"/>
        </svg>
        Save Cart for Later
      </button>
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
      @if(!empty($cart))
      <button wire:click="saveCart" @click="cartOpen = false"
              style="width:100%;padding:12px;margin-bottom:8px;
                     background:var(--surface2);color:var(--text-sub);
                     border:1.5px solid var(--border);border-radius:12px;
                     font-size:14px;font-weight:700;cursor:pointer;font-family:var(--font)">
        Save Cart for Later
      </button>
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
@php
  $stagingFullBoxes  = $stagingStock['full_boxes'] ?? 0;
  $stagingPartBoxes  = $stagingStock['partial_boxes'] ?? 0;
  $stagingTotalBoxes = $stagingFullBoxes + $stagingPartBoxes;
  $stagingTotalItems = $stagingStock['total_items'] ?? 0;
  $inStock           = $stagingTotalBoxes > 0;
@endphp
<style>
/* ── Staging modal ────────────────────────────── */
.sm-overlay {
    position:fixed;inset:0;z-index:600;display:flex;align-items:center;
    justify-content:center;background:rgba(10,14,35,.6);backdrop-filter:blur(6px);
    padding:16px;
}
.sm-card {
    background:var(--surface);border:1px solid var(--border);border-radius:18px;
    width:480px;max-width:100%;max-height:92vh;display:flex;flex-direction:column;
    box-shadow:0 24px 64px rgba(0,0,0,.32);overflow:hidden;
}
/* Header */
.sm-head {
    display:flex;align-items:center;justify-content:space-between;
    padding:18px 20px 14px;border-bottom:1px solid var(--border);flex-shrink:0;
}
.sm-title { font-size:16px;font-weight:800;color:var(--text);line-height:1.2 }
.sm-subtitle { font-size:12px;color:var(--text-sub);margin-top:3px;
               white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:320px }
.sm-close {
    width:30px;height:30px;border-radius:8px;background:var(--surface2);
    border:1px solid var(--border);cursor:pointer;display:grid;place-items:center;
    color:var(--text-dim);flex-shrink:0;transition:all var(--tr);
}
.sm-close:hover { background:var(--border);color:var(--text) }

/* Scrollable body */
.sm-body { padding:18px 20px;overflow-y:auto;flex:1 }

/* Info strip */
.sm-info {
    display:flex;align-items:center;justify-content:space-between;
    background:var(--bg);border:1px solid var(--border);border-radius:11px;
    padding:11px 14px;margin-bottom:16px;gap:12px;
}
.sm-info-left { min-width:0 }
.sm-info-sku { font-size:10px;font-family:var(--mono);color:var(--text-dim);
               margin-bottom:2px;text-transform:uppercase;letter-spacing:.4px }
.sm-info-cat { font-size:12px;color:var(--text-sub) }
.sm-info-right { text-align:right;flex-shrink:0 }
.sm-info-label { font-size:10px;color:var(--text-dim);margin-bottom:3px }
.sm-stock-num { font-size:19px;font-weight:800;font-family:var(--mono);line-height:1 }
.sm-stock-detail { font-size:10px;color:var(--text-dim);margin-top:2px }

/* Mode toggle */
.sm-modes { display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px }
.sm-mode-btn {
    padding:12px 10px 11px;border-radius:11px;cursor:pointer;text-align:center;
    border:2px solid var(--border);background:var(--bg);transition:all .12s;
}
.sm-mode-btn.active { border-color:var(--accent);background:var(--surface2) }
.sm-mode-btn:not(.active):hover { border-color:var(--accent);opacity:.8 }
.sm-mode-btn.disabled { opacity:.4;cursor:not-allowed;pointer-events:none }
.sm-mode-icon { font-size:20px;margin-bottom:5px;line-height:1 }
.sm-mode-name { font-size:13px;font-weight:700;color:var(--text-sub);transition:color .12s }
.sm-mode-btn.active .sm-mode-name { color:var(--accent) }
.sm-mode-meta { font-size:10px;color:var(--text-dim);margin-top:2px }
.sm-mode-price { font-size:13px;font-weight:800;font-family:var(--mono);
                 color:var(--accent);margin-top:6px }

/* Qty + price row */
.sm-fields { display:grid;grid-template-columns:140px 1fr;gap:12px;margin-bottom:14px;align-items:start }

/* Field label */
.sm-label {
    font-size:10px;font-weight:700;color:var(--text-dim);
    text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px;
    display:flex;align-items:center;justify-content:space-between;min-height:18px;
}
.sm-modified-badge {
    font-size:10px;font-weight:700;color:var(--amber);
    background:rgba(245,158,11,.12);padding:2px 7px;border-radius:5px;
    transition:opacity .15s;
}

/* Qty stepper */
.sm-stepper { display:flex;align-items:center;gap:0;border:2px solid var(--accent);
              border-radius:10px;overflow:hidden;background:var(--surface) }
.sm-step-btn {
    width:36px;height:44px;background:transparent;border:none;cursor:pointer;
    font-size:18px;color:var(--accent);display:grid;place-items:center;
    transition:background var(--tr);flex-shrink:0;
}
.sm-step-btn:hover { background:var(--surface2) }
.sm-qty-input {
    flex:1;border:none;padding:0;text-align:center;font-size:20px;font-weight:800;
    font-family:var(--mono);color:var(--text);background:transparent;outline:none;
    min-width:0;height:44px;
}

/* Price input */
.sm-price-wrap { position:relative }
.sm-price-prefix {
    position:absolute;left:11px;top:50%;transform:translateY(-50%);
    font-size:10px;font-weight:700;color:var(--text-dim);pointer-events:none;
    font-family:var(--mono);
}
.sm-price-input {
    width:100%;box-sizing:border-box;padding:10px 12px 10px 38px;
    border:2px solid var(--border);border-radius:10px;
    font-size:20px;font-weight:800;font-family:var(--mono);
    color:var(--text);background:var(--surface);outline:none;
    transition:border-color .15s;height:48px;
}
.sm-price-input:focus { border-color:var(--accent) }
.sm-price-input.modified { border-color:var(--amber) }
.sm-price-locked {
    height:48px;padding:0 14px;background:var(--bg);border:2px solid var(--border);
    border-radius:10px;font-family:var(--mono);font-size:14px;font-weight:700;
    color:var(--text-sub);display:flex;align-items:center;justify-content:space-between;
}
.sm-price-locked-hint { font-size:10px;color:var(--text-dim) }

/* Reason field — always rendered, shown/hidden via visibility */
.sm-reason-wrap { margin-top:8px;overflow:hidden;transition:max-height .15s,opacity .15s }
.sm-reason-wrap.hidden { max-height:0;opacity:0;pointer-events:none }
.sm-reason-wrap.visible { max-height:60px;opacity:1 }
.sm-reason-input {
    width:100%;box-sizing:border-box;padding:9px 12px;
    border:2px solid var(--amber);border-radius:9px;font-size:13px;
    background:var(--surface);color:var(--text);outline:none;font-family:var(--font);
}

/* Total bar */
.sm-total {
    display:flex;align-items:center;justify-content:space-between;
    border-radius:11px;padding:13px 16px;margin-bottom:18px;
    border:1.5px solid var(--border);background:var(--bg);
}
.sm-total.modified { border-color:var(--amber);background:rgba(245,158,11,.06) }
.sm-total-label { font-size:12px;color:var(--text-sub) }
.sm-total-amount { font-size:24px;font-weight:800;font-family:var(--mono);
                   color:var(--accent);line-height:1 }
.sm-total.modified .sm-total-amount { color:var(--amber) }
.sm-total-unit { font-size:11px;font-weight:600;color:var(--text-dim);margin-left:4px }

/* Footer */
.sm-footer {
    display:grid;grid-template-columns:auto 1fr;gap:9px;
    padding:14px 20px;border-top:1px solid var(--border);flex-shrink:0;
    background:var(--surface);
}
.sm-cancel-btn {
    padding:0 22px;height:46px;background:transparent;color:var(--text-sub);
    border:1.5px solid var(--border);border-radius:11px;font-size:14px;
    font-weight:700;cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap;
}
.sm-cancel-btn:hover { border-color:var(--accent);color:var(--accent) }
.sm-confirm-btn {
    height:46px;background:var(--accent);color:#fff;border:none;border-radius:11px;
    font-size:14px;font-weight:800;cursor:pointer;font-family:var(--font);
    display:flex;align-items:center;justify-content:center;gap:7px;
    box-shadow:0 4px 14px rgba(59,111,212,.28);transition:opacity var(--tr);
}
.sm-confirm-btn:hover { opacity:.9 }
.sm-confirm-btn:disabled { opacity:.55;cursor:not-allowed }
</style>

<div class="sm-overlay">
  <div class="sm-card">

    {{-- Header --}}
    <div class="sm-head">
      <div style="min-width:0">
        <div class="sm-title">{{ $stagingCartIndex !== null ? 'Edit Cart Item' : 'Add to Cart' }}</div>
        <div class="sm-subtitle">{{ $stagingProduct['name'] }}</div>
      </div>
      <button wire:click="closeAddModal" class="sm-close">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    {{-- Scrollable body --}}
    <div class="sm-body">

      {{-- Product info strip --}}
      <div class="sm-info">
        <div class="sm-info-left">
          <div class="sm-info-sku">{{ $stagingProduct['sku'] }}</div>
          <div class="sm-info-cat">{{ $stagingProduct['category'] ?? '—' }}</div>
        </div>
        <div class="sm-info-right">
          <div class="sm-info-label">In stock at this shop</div>
          <div class="sm-stock-num" style="color:{{ $inStock ? 'var(--green)' : 'var(--red)' }}">
            {{ $stagingTotalBoxes }}
            <span style="font-size:11px;font-weight:600;color:var(--text-dim)">
              {{ $stagingTotalBoxes === 1 ? 'box' : 'boxes' }}
            </span>
          </div>
          <div class="sm-stock-detail">
            {{ $stagingFullBoxes }} full · {{ $stagingPartBoxes }} partial · {{ $stagingTotalItems }} items
          </div>
        </div>
      </div>

      {{-- Sell mode toggle --}}
      <div class="sm-modes">
        @if($stagingProduct['has_full_box'] ?? false)
        <button wire:click="$set('stagingMode','box')"
                class="sm-mode-btn {{ $stagingMode === 'box' ? 'active' : '' }}">
          <div class="sm-mode-icon">📦</div>
          <div class="sm-mode-name">Full Box</div>
          <div class="sm-mode-meta">{{ $stagingProduct['items_per_box'] }} items / box</div>
          <div class="sm-mode-price">{{ number_format($stagingProduct['box_price']) }} RWF</div>
        </button>
        @else
        <div class="sm-mode-btn" style="opacity:.45;cursor:not-allowed;pointer-events:none">
          <div class="sm-mode-icon">📦</div>
          <div class="sm-mode-name">Full Box</div>
          <div class="sm-mode-meta" style="color:var(--red)">No full boxes</div>
          <div class="sm-mode-price" style="color:var(--text-dim)">{{ number_format($stagingProduct['box_price']) }} RWF</div>
        </div>
        @endif

        @if($stagingProduct['individual_sale_allowed'] ?? true)
        <button wire:click="$set('stagingMode','item')"
                class="sm-mode-btn {{ $stagingMode === 'item' ? 'active' : '' }}">
          <div class="sm-mode-icon">🏷</div>
          <div class="sm-mode-name">Individual Items</div>
          <div class="sm-mode-meta">per item</div>
          <div class="sm-mode-price">{{ number_format($stagingProduct['selling_price']) }} RWF</div>
        </button>
        @else
        <div class="sm-mode-btn disabled">
          <div class="sm-mode-icon">🏷</div>
          <div class="sm-mode-name">Individual Items</div>
          <div class="sm-mode-meta" style="color:var(--text-dim)">Not available</div>
        </div>
        @if(!($stagingProduct['individual_sale_allowed'] ?? true) && $stagingMode === 'item')
        <span wire:init="$set('stagingMode', 'box')"></span>
        @endif
        @endif
      </div>

      {{-- Quantity + Price row --}}
      <div class="sm-fields">

        {{-- Quantity --}}
        <div>
          <div class="sm-label">Qty</div>
          <div class="sm-stepper">
            <button wire:click="$set('stagingQty', max(1, stagingQty - 1))" class="sm-step-btn">&minus;</button>
            <input wire:model.live="stagingQty" type="number" min="1" class="sm-qty-input">
            <button wire:click="$set('stagingQty', stagingQty + 1)" class="sm-step-btn">+</button>
          </div>
        </div>

        {{-- Unit price --}}
        <div>
          <div class="sm-label">
            <span>Unit Price</span>
            {{-- Badge area always rendered — opacity prevents layout jump --}}
            <span class="sm-modified-badge"
                  style="opacity:{{ $stagingPriceModified ? '1' : '0' }}">MODIFIED</span>
          </div>

          @if($settingAllowPriceOverride)
          <div class="sm-price-wrap">
            <span class="sm-price-prefix">RWF</span>
            {{--
              wire:model.lazy — only syncs on blur (focus-out).
              This prevents per-keystroke re-renders that caused the field to jump.
            --}}
            <input wire:model.lazy="stagingPrice" type="number" min="0"
                   class="sm-price-input {{ $stagingPriceModified ? 'modified' : '' }}">
          </div>
          {{-- Reason field: always in DOM, shown/hidden via CSS to avoid layout shift --}}
          <div class="sm-reason-wrap {{ $stagingPriceModified ? 'visible' : 'hidden' }}">
            <input wire:model.live="stagingPriceReason" type="text"
                   class="sm-reason-input"
                   placeholder="Reason for price change (required)…">
          </div>
          @else
          <div class="sm-price-locked">
            <span>{{ number_format($stagingPrice) }} RWF</span>
            <span class="sm-price-locked-hint">locked by owner</span>
          </div>
          @endif
        </div>
      </div>

      {{-- Line total --}}
      <div class="sm-total {{ $stagingPriceModified ? 'modified' : '' }}">
        <div>
          <div class="sm-total-label">Line total</div>
          <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
            {{ $stagingQty }} × {{ number_format($stagingPrice) }} RWF
          </div>
        </div>
        <div>
          <span class="sm-total-amount">{{ number_format($stagingQty * $stagingPrice) }}</span>
          <span class="sm-total-unit">RWF</span>
        </div>
      </div>

    </div>{{-- end sm-body --}}

    {{-- Footer --}}
    <div class="sm-footer">
      <button wire:click="closeAddModal" class="sm-cancel-btn">Cancel</button>
      <button wire:click="confirmAddToCart"
              wire:loading.attr="disabled"
              class="sm-confirm-btn">
        <span wire:loading.remove style="display:flex;align-items:center;gap:6px">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
          </svg>
          {{ $stagingCartIndex !== null ? 'Update Cart' : 'Add to Cart' }}
        </span>
        <span wire:loading style="display:none;font-size:13px">Adding…</span>
      </button>
    </div>

  </div>
</div>
@endif

{{-- ══════════════════════════════════════════════
     CHECKOUT MODAL
══════════════════════════════════════════════ --}}
@if($showCheckoutModal)
<style>
/* ── Checkout modal ──────────────────────────── */
.co-overlay {
    position:fixed;inset:0;z-index:600;display:flex;align-items:center;
    justify-content:center;background:rgba(10,14,35,.62);backdrop-filter:blur(6px);
    padding:12px;
}
.co-card {
    background:var(--surface);border:1px solid var(--border);border-radius:18px;
    width:720px;max-width:100%;max-height:94vh;display:flex;flex-direction:column;
    box-shadow:0 28px 72px rgba(0,0,0,.32);overflow:hidden;
}

/* Header */
.co-head {
    display:flex;align-items:center;justify-content:space-between;
    padding:18px 22px 14px;border-bottom:1px solid var(--border);flex-shrink:0;
}
.co-title { font-size:17px;font-weight:800;color:var(--text);line-height:1.2 }
.co-subtitle { font-size:12px;color:var(--text-sub);margin-top:2px }
.co-close {
    width:32px;height:32px;border-radius:8px;background:var(--surface2);
    border:1px solid var(--border);cursor:pointer;display:grid;place-items:center;
    color:var(--text-dim);flex-shrink:0;transition:all var(--tr);
}
.co-close:hover { background:var(--border);color:var(--text) }

/* Scrollable body */
.co-body { display:grid;grid-template-columns:1fr 1fr;flex:1;overflow:hidden;min-height:0 }

/* Column shared */
.co-col { padding:18px 20px;overflow-y:auto }
.co-col-left { border-right:1px solid var(--border) }

/* Section label */
.co-section-label {
    font-size:10px;font-weight:800;color:var(--text-dim);
    text-transform:uppercase;letter-spacing:.7px;margin-bottom:10px;
}

/* Order summary card */
.co-order-card {
    background:var(--bg);border:1px solid var(--border);border-radius:12px;
    padding:14px 16px;margin-bottom:18px;
}
.co-order-row {
    display:flex;justify-content:space-between;align-items:baseline;
    gap:8px;padding:4px 0;border-bottom:1px solid var(--border);
}
.co-order-row:last-of-type { border-bottom:none }
.co-order-name { font-size:12px;color:var(--text-sub);min-width:0;flex:1 }
.co-order-amount { font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text);flex-shrink:0 }
.co-order-total {
    display:flex;justify-content:space-between;align-items:center;
    padding-top:10px;border-top:2px solid var(--border);margin-top:6px;
}
.co-order-total-label { font-size:13px;font-weight:700;color:var(--text) }
.co-order-total-amt { font-size:26px;font-weight:800;font-family:var(--mono);color:var(--accent);line-height:1 }
.co-order-total-ruf { font-size:12px;font-weight:600;color:var(--text-dim);margin-left:4px }

/* Customer section */
.co-customer-selected {
    display:flex;align-items:center;justify-content:space-between;
    padding:11px 14px;background:var(--surface2);border:1.5px solid var(--accent);
    border-radius:11px;gap:10px;
}
.co-customer-name { font-size:13px;font-weight:700;color:var(--text);margin-bottom:3px }
.co-customer-phone { font-size:11px;color:var(--text-sub);font-family:var(--mono) }
.co-customer-credit {
    display:inline-block;margin-left:8px;padding:1px 7px;
    background:rgba(245,158,11,.12);color:var(--amber);
    border-radius:5px;font-weight:700;font-size:10px;
}
.co-customer-clear {
    width:26px;height:26px;border-radius:50%;border:none;
    background:rgba(239,68,68,.1);color:var(--red);
    cursor:pointer;display:grid;place-items:center;font-size:16px;
    flex-shrink:0;line-height:1;padding:0;transition:background var(--tr);
}
.co-customer-clear:hover { background:rgba(239,68,68,.2) }

/* New customer form */
.co-new-cust-form {
    background:var(--bg);border:1px solid var(--border);border-radius:11px;padding:14px;
}
.co-new-cust-title { font-size:13px;font-weight:800;color:var(--text);margin-bottom:12px }
.co-field-label { display:block;font-size:10px;font-weight:700;color:var(--text-dim);
                  text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px }
.co-input {
    width:100%;box-sizing:border-box;padding:9px 11px;
    border:1.5px solid var(--border);border-radius:9px;
    font-size:13px;background:var(--surface);color:var(--text);
    outline:none;font-family:var(--font);transition:border-color var(--tr);
}
.co-input:focus { border-color:var(--accent) }
.co-input.mono { font-family:var(--mono) }
.co-new-cust-btns { display:flex;gap:8px;margin-top:10px }
.co-btn-secondary {
    flex:1;padding:9px;border-radius:9px;border:1.5px solid var(--border);
    background:transparent;font-size:12px;font-weight:600;cursor:pointer;
    color:var(--text-sub);font-family:var(--font);transition:all var(--tr);
}
.co-btn-secondary:hover { border-color:var(--accent);color:var(--accent) }
.co-btn-primary {
    flex:1;padding:9px;border-radius:9px;border:none;
    background:var(--accent);color:#fff;font-size:12px;font-weight:700;
    cursor:pointer;font-family:var(--font);transition:opacity var(--tr);
}
.co-btn-primary:hover { opacity:.88 }

/* Customer search */
.co-search-wrap { position:relative }
.co-search-input {
    width:100%;box-sizing:border-box;padding:9px 11px;
    border:1.5px solid var(--border);border-radius:10px;
    font-size:13px;background:var(--surface);color:var(--text);
    outline:none;transition:border-color var(--tr);font-family:var(--font);
}
.co-search-input:focus { border-color:var(--accent) }
.co-search-dropdown {
    position:absolute;top:calc(100% + 5px);left:0;right:0;z-index:20;
    background:var(--surface);border:1.5px solid var(--border);border-radius:11px;
    box-shadow:0 10px 30px rgba(0,0,0,.18);max-height:200px;overflow-y:auto;
}
.co-search-result {
    width:100%;padding:10px 13px;text-align:left;border:none;background:transparent;
    cursor:pointer;border-bottom:1px solid var(--border);transition:background var(--tr);
    font-family:var(--font);display:block;
}
.co-search-result:last-child { border-bottom:none }
.co-search-result:hover { background:var(--surface2) }
.co-search-result-name { font-size:13px;font-weight:700;color:var(--text);margin-bottom:2px }
.co-search-result-phone { font-size:11px;color:var(--text-dim);font-family:var(--mono) }
.co-search-result-credit { margin-left:6px;color:var(--amber);font-weight:700 }
.co-new-cust-btn {
    width:100%;margin-top:8px;padding:8px;border-radius:10px;
    border:1.5px dashed var(--border);background:transparent;
    font-size:12px;font-weight:700;cursor:pointer;color:var(--text-dim);
    transition:all var(--tr);font-family:var(--font);
}
.co-new-cust-btn:hover { border-color:var(--accent);color:var(--accent) }

/* Credit warning */
.co-credit-warning {
    padding:10px 13px;background:rgba(245,158,11,.08);
    border:1.5px solid var(--amber);border-radius:10px;margin-bottom:12px;
}
.co-credit-warning-title { font-size:11px;font-weight:800;color:var(--amber);margin-bottom:2px }
.co-credit-warning-msg { font-size:12px;color:var(--text-sub) }

/* Payment channels — redesigned */
.co-pay-list { border:1px solid var(--border);border-radius:13px;overflow:hidden;margin-bottom:14px }
.co-pay-row {
    display:flex;align-items:center;gap:11px;padding:9px 13px;
    background:var(--surface);border-bottom:1px solid var(--border);
    transition:background .12s;
}
.co-pay-row:last-child { border-bottom:none }
.co-pay-row.is-active { background:var(--surface-raised) }
.co-pay-row.is-overalloc .co-pay-amount { border-color:var(--red) }
.co-pay-icon {
    width:30px;height:30px;border-radius:8px;display:grid;place-items:center;
    font-size:14px;flex-shrink:0;
}
.co-pay-meta { flex:1;min-width:0 }
.co-pay-label {
    font-size:10px;font-weight:700;color:var(--text-dim);
    text-transform:uppercase;letter-spacing:.5px;
    display:flex;align-items:center;gap:5px;line-height:1;
}
.co-pay-hint { font-size:9px;color:var(--amber);font-weight:600;text-transform:none;letter-spacing:0 }
.co-pay-amount-wrap { position:relative;width:130px;flex-shrink:0 }
.co-pay-amount {
    width:100%;box-sizing:border-box;padding:7px 36px 7px 10px;
    border:1.5px solid var(--border);border-radius:8px;
    background:var(--surface);color:var(--text);font-size:14px;
    font-family:var(--mono);outline:none;transition:border-color .15s;text-align:right;
}
.co-pay-amount:focus { border-color:var(--accent);background:var(--surface-raised) }
.co-pay-amount:disabled { opacity:.4;cursor:not-allowed }
.co-pay-amount-unit {
    position:absolute;right:9px;top:50%;transform:translateY(-50%);
    font-size:10px;font-weight:700;color:var(--text-faint);pointer-events:none;
}
/* Cash row — auto-computed display */
.co-cash-row {
    display:flex;align-items:center;gap:11px;padding:10px 13px;
    background:var(--surface-raised);border-top:2px solid var(--border);
}
.co-cash-display {
    width:130px;flex-shrink:0;padding:7px 10px;
    border:1.5px solid var(--green);border-radius:8px;
    background:rgba(16,185,129,.06);color:var(--green);
    font-size:14px;font-family:var(--mono);font-weight:700;
    text-align:right;display:flex;align-items:center;justify-content:space-between;
    gap:4px;
}
.co-cash-badge {
    font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;
    background:var(--green);color:#fff;padding:2px 5px;border-radius:4px;
    flex-shrink:0;
}
/* Reference field */
.co-pay-ref-wrap { overflow:hidden;transition:max-height .15s,opacity .15s,margin-top .15s }
.co-pay-ref-wrap.hidden { max-height:0;opacity:0;margin-top:0 }
.co-pay-ref-wrap.visible { max-height:40px;opacity:1;margin-top:5px }
.co-pay-ref {
    width:100%;box-sizing:border-box;padding:5px 9px;
    border:1.5px solid var(--border);border-radius:7px;
    background:var(--surface);color:var(--text);font-size:11px;
    font-family:var(--mono);outline:none;transition:border-color var(--tr);
}
.co-pay-ref:focus { border-color:var(--accent) }
/* Balance progress strip */
.co-bal-strip {
    border:1px solid var(--border);border-radius:11px;
    overflow:hidden;margin-bottom:13px;padding:11px 13px;
    background:var(--bg);
}
.co-bal-strip-nums { display:flex;align-items:baseline;justify-content:space-between;margin-bottom:8px }
.co-bal-total { font-size:24px;font-weight:800;font-family:var(--mono);color:var(--text);line-height:1 }
.co-bal-unit { font-size:11px;font-weight:600;color:var(--text-dim);margin-left:3px }
.co-bal-status { font-size:12px;font-weight:700 }
.co-bal-bar-wrap { height:5px;border-radius:99px;background:var(--border);overflow:hidden }
.co-bal-bar { height:100%;border-radius:99px;transition:width .2s,background .2s }

/* Notes */
.co-notes {
    width:100%;box-sizing:border-box;padding:9px 11px;
    border:1.5px solid var(--border);border-radius:10px;
    font-size:13px;background:var(--surface);color:var(--text);
    outline:none;resize:none;font-family:var(--font);
    transition:border-color var(--tr);
}
.co-notes:focus { border-color:var(--accent) }

/* Complete button */
.co-complete-btn {
    width:100%;height:50px;
    background:var(--green);color:#fff;border:none;border-radius:13px;
    font-size:16px;font-weight:800;cursor:pointer;font-family:var(--font);
    display:flex;align-items:center;justify-content:center;gap:8px;
    box-shadow:0 5px 18px rgba(34,197,94,.30);transition:opacity var(--tr);
}
.co-complete-btn:hover:not(:disabled) { opacity:.92 }
.co-complete-btn:disabled { opacity:.5;cursor:not-allowed }

/* Responsive */
@media (max-width:680px) {
    .co-body { grid-template-columns:1fr;overflow-y:auto }
    .co-col-left { border-right:none;border-bottom:1px solid var(--border) }
    .co-col { overflow-y:visible }
}
</style>

<div class="co-overlay">
  <div class="co-card">

    {{-- Header --}}
    <div class="co-head">
      <div>
        <div class="co-title">Checkout</div>
        <div class="co-subtitle">Review order and process payment</div>
      </div>
      <button wire:click="$set('showCheckoutModal', false)" class="co-close">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    {{-- Two-column body --}}
    <div class="co-body">

      {{-- LEFT: Order summary + Customer --}}
      <div class="co-col co-col-left">

        {{-- Order summary --}}
        <div class="co-section-label">Order Summary</div>
        <div class="co-order-card">
          @foreach($cart as $item)
          <div class="co-order-row">
            <span class="co-order-name">
              {{ $item['product_name'] }}
              @if($item['is_full_box'])
                <span style="color:var(--text-dim)">× {{ $item['quantity'] }} {{ $item['quantity'] === 1 ? 'box' : 'boxes' }}</span>
              @else
                <span style="color:var(--text-dim)">× {{ $item['quantity'] }} items</span>
              @endif
              @if(!empty($item['price_modified']))
                <span style="font-size:10px;color:var(--amber);font-weight:700;margin-left:4px">↓</span>
              @endif
            </span>
            <span class="co-order-amount">{{ number_format($item['line_total']) }}</span>
          </div>
          @endforeach
          <div class="co-order-total">
            <span class="co-order-total-label">Total</span>
            <div>
              <span class="co-order-total-amt">{{ number_format($cartTotal) }}</span>
              <span class="co-order-total-ruf">RWF</span>
            </div>
          </div>
        </div>

        {{-- Customer --}}
        <div class="co-section-label">Customer</div>

        @if($selectedCustomerId)
        <div class="co-customer-selected">
          <div style="min-width:0">
            <div class="co-customer-name">{{ $selectedCustomerName }}</div>
            <div class="co-customer-phone">
              {{ $selectedCustomerPhone }}
              @if($selectedCustomerOutstandingBalance > 0)
              <span class="co-customer-credit">
                Credit: {{ number_format($selectedCustomerOutstandingBalance) }} RWF
              </span>
              @endif
            </div>
          </div>
          <button wire:click="clearCustomer" class="co-customer-clear" title="Remove customer">×</button>
        </div>

        @elseif($showNewCustomerForm)
        <div class="co-new-cust-form">
          <div class="co-new-cust-title">Register New Customer</div>
          <div style="display:grid;gap:9px">
            <input wire:model="newCustomerName" type="text" placeholder="Full name *" class="co-input">
            <input wire:model="newCustomerPhone" type="text" placeholder="+250… (phone) *" class="co-input mono">
            <input wire:model="newCustomerEmail" type="email" placeholder="email@example.com (optional)" class="co-input">
          </div>
          <div class="co-new-cust-btns">
            <button wire:click="cancelNewCustomer" class="co-btn-secondary">Cancel</button>
            <button wire:click="saveNewCustomer" class="co-btn-primary">Save &amp; Select</button>
          </div>
        </div>

        @else
        <div class="co-search-wrap">
          <input wire:model.live="customerSearch" type="text"
                 placeholder="Search by name or phone…"
                 class="co-search-input">
          @if($showCustomerSearch && count($customerResults) > 0)
          <div class="co-search-dropdown">
            @foreach($customerResults as $customer)
            <button wire:click="selectCustomer({{ $customer['id'] }})" class="co-search-result" type="button">
              <div class="co-search-result-name">{{ $customer['name'] }}</div>
              <div class="co-search-result-phone">
                {{ $customer['phone'] }}
                @if($customer['outstanding_balance'] > 0)
                <span class="co-search-result-credit">
                  Credit: {{ number_format($customer['outstanding_balance']) }}
                </span>
                @endif
              </div>
            </button>
            @endforeach
          </div>
          @endif
        </div>
        <button wire:click="showCreateCustomerForm" type="button" class="co-new-cust-btn">
          + Register New Customer
        </button>
        @endif

      </div>{{-- /co-col-left --}}

      {{-- RIGHT: Payment + Notes --}}
      <div class="co-col"
           x-data="{
               total:  {{ (int) $cartTotal }},
               card:   null,
               momo:   null,
               bank:   null,
               credit: null,
               get c()        { return Number(this.card)   || 0 },
               get m()        { return Number(this.momo)   || 0 },
               get b()        { return Number(this.bank)   || 0 },
               get cr()       { return Number(this.credit) || 0 },
               get nonCash()  { return this.c + this.m + this.b + this.cr },
               get cash()     { return Math.max(0, this.total - this.nonCash) },
               get fillPct()  { return this.total > 0 ? Math.min(100, Math.round(this.nonCash / this.total * 100)) : 0 },
               get isOver()   { return this.nonCash > this.total },
               get isOk()     { return !this.isOver },
               submit() {
                   $wire.checkout(this.c, this.m, this.b, this.cr);
               }
           }">

        <div class="co-section-label">Payment</div>

        {{-- Balance strip --}}
        <div class="co-bal-strip">
          <div class="co-bal-strip-nums">
            <div>
              <span class="co-bal-total">{{ number_format($cartTotal) }}</span>
              <span class="co-bal-unit">RWF</span>
            </div>
            <div class="co-bal-status"
                 :style="isOver ? 'color:var(--red)' : 'color:var(--green)'">
              <span x-show="isOk">✓ Balanced</span>
              <span x-show="isOver" x-cloak>⚠ Over-allocated</span>
            </div>
          </div>
          <div class="co-bal-bar-wrap">
            <div class="co-bal-bar"
                 :style="`width:${fillPct}%;background:${isOver ? 'var(--red)' : (fillPct >= 100 ? 'var(--green)' : 'var(--accent)')}`">
            </div>
          </div>
        </div>

        {{-- Credit warning --}}
        <div style="{{ $creditWarningVisible ? '' : 'display:none' }}">
          <div class="co-credit-warning">
            <div class="co-credit-warning-title">Outstanding Balance</div>
            <div class="co-credit-warning-msg">{{ $creditWarningMessage ?? '' }}</div>
          </div>
        </div>

        {{-- Payment channels (non-cash, user-entered) --}}
        <div class="co-pay-list">

          {{-- Card --}}
          @if($settingAllowCardPayment)
          <div class="co-pay-row" :class="card > 0 ? 'is-active' : ''">
            <div class="co-pay-icon" style="background:rgba(59,130,246,.12)">
              <svg width="14" height="14" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div class="co-pay-meta">
              <div class="co-pay-label">Card</div>
              <div class="co-pay-ref-wrap" :class="card > 0 ? 'visible' : 'hidden'">
                <input wire:model="payRef_card" type="text" placeholder="Reference (optional)" class="co-pay-ref">
              </div>
            </div>
            <div class="co-pay-amount-wrap">
              <input x-model.number="card" type="number" min="0" placeholder="0"
                     class="co-pay-amount">
              <span class="co-pay-amount-unit">RWF</span>
            </div>
          </div>
          @endif

          {{-- Mobile Money --}}
          <div class="co-pay-row" :class="momo > 0 ? 'is-active' : ''">
            <div class="co-pay-icon" style="background:rgba(16,185,129,.12)">
              <svg width="14" height="14" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18" stroke-linecap="round" stroke-width="2.5"/></svg>
            </div>
            <div class="co-pay-meta">
              <div class="co-pay-label">Mobile Money</div>
              <div class="co-pay-ref-wrap" :class="momo > 0 ? 'visible' : 'hidden'">
                <input wire:model="payRef_mobile_money" type="text" placeholder="Reference (optional)" class="co-pay-ref">
              </div>
            </div>
            <div class="co-pay-amount-wrap">
              <input x-model.number="momo" type="number" min="0" placeholder="0"
                     class="co-pay-amount">
              <span class="co-pay-amount-unit">RWF</span>
            </div>
          </div>

          {{-- Bank Transfer --}}
          @if($settingAllowBankTransfer)
          <div class="co-pay-row" :class="bank > 0 ? 'is-active' : ''">
            <div class="co-pay-icon" style="background:rgba(99,102,241,.12)">
              <svg width="14" height="14" fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10h16v11H4zM2 7l10-4 10 4M8 14v3m4-3v3m4-3v3"/></svg>
            </div>
            <div class="co-pay-meta">
              <div class="co-pay-label">Bank Transfer</div>
              <div class="co-pay-ref-wrap" :class="bank > 0 ? 'visible' : 'hidden'">
                <input wire:model="payRef_bank_transfer" type="text" placeholder="Reference (optional)" class="co-pay-ref">
              </div>
            </div>
            <div class="co-pay-amount-wrap">
              <input x-model.number="bank" type="number" min="0" placeholder="0"
                     class="co-pay-amount">
              <span class="co-pay-amount-unit">RWF</span>
            </div>
          </div>
          @endif

          {{-- Credit --}}
          @if($settingAllowCreditSales)
          <div class="co-pay-row" :class="credit > 0 ? 'is-active' : ''">
            <div class="co-pay-icon" style="background:rgba(245,158,11,.12)">
              <svg width="14" height="14" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="co-pay-meta">
              <div class="co-pay-label">
                Credit
                @if(!$selectedCustomerId && $settingCreditRequiresCustomer)
                  <span class="co-pay-hint">select customer first</span>
                @endif
              </div>
            </div>
            <div class="co-pay-amount-wrap">
              <input x-model.number="credit" type="number" min="0" placeholder="0"
                     class="co-pay-amount"
                     :disabled="{{ $settingCreditRequiresCustomer ? 'true' : 'false' }} && !$wire.selectedCustomerId"
                     :style="credit > 0 ? 'border-color:var(--amber);' : ''">
              <span class="co-pay-amount-unit">RWF</span>
            </div>
          </div>
          @endif

        </div>{{-- /co-pay-list --}}

        {{-- Cash (auto-computed, pinned at bottom of list) --}}
        <div class="co-cash-row" style="border:1px solid var(--border);border-radius:13px;margin-bottom:14px;">
          <div class="co-pay-icon" style="background:rgba(16,185,129,.12)">
            <svg width="14" height="14" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M6 10h.01M18 14h.01"/></svg>
          </div>
          <div class="co-pay-meta">
            <div class="co-pay-label" style="color:var(--green);">Cash</div>
            <div style="font-size:10px;color:var(--text-faint);margin-top:1px;">Remainder — auto-calculated</div>
          </div>
          <div class="co-cash-display" :style="cash < 0 ? 'border-color:var(--red);color:var(--red);background:rgba(239,68,68,.06)' : ''">
            <span x-text="new Intl.NumberFormat().format(cash)" style="font-size:14px;"></span>
            <span class="co-cash-badge" :style="cash > 0 ? '' : 'background:var(--text-faint)'">AUTO</span>
          </div>
        </div>

        {{-- Over-allocation error --}}
        <div x-show="isOver" x-cloak
             style="margin-bottom:12px;padding:9px 12px;background:rgba(239,68,68,.07);border:1.5px solid var(--red);border-radius:10px;font-size:12px;color:var(--red);">
          <strong>Over-allocated:</strong> non-cash methods exceed the total by
          <span x-text="new Intl.NumberFormat().format(nonCash - total)"></span> RWF.
          Reduce one of the amounts above.
        </div>

        {{-- Notes --}}
        <div class="co-section-label">Notes <span style="font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></div>
        <textarea wire:model="notes" rows="2" placeholder="Sale notes…" class="co-notes"></textarea>

        {{-- Footer: Complete Sale (inside the Alpine scope so we can use isOver) --}}
        <div style="padding-top:12px;">
          <button @click="submit()"
                  wire:loading.attr="disabled"
                  :disabled="isOver"
                  class="co-complete-btn"
                  :style="isOver ? 'opacity:.4;cursor:not-allowed;box-shadow:none' : ''">
            <span wire:loading.remove style="display:flex;align-items:center;gap:7px">
              <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
              Complete Sale — {{ number_format($cartTotal) }} RWF
            </span>
            <span wire:loading style="display:none;font-size:14px">Processing…</span>
          </button>
        </div>

      </div>{{-- /co-col right --}}
    </div>{{-- /co-body --}}

  </div>
</div>
@endif

{{-- ══════════════════════════════════════════════
     RECEIPT MODAL
══════════════════════════════════════════════ --}}
@if($showReceiptModal && $completedSale)
<style>
/* ── Receipt modal (screen) ──────────────────── */
.rc-overlay {
    position:fixed;inset:0;z-index:700;display:flex;align-items:center;
    justify-content:center;background:rgba(10,14,35,.68);backdrop-filter:blur(6px);
    padding:16px;
}
.rc-card {
    background:var(--surface);border:1px solid var(--border);border-radius:18px;
    width:440px;max-width:100%;max-height:92vh;display:flex;flex-direction:column;
    box-shadow:0 28px 72px rgba(0,0,0,.36);overflow:hidden;
}

/* Success banner */
.rc-banner {
    background:var(--green);padding:24px 24px 20px;text-align:center;flex-shrink:0;
    position:relative;
}
.rc-banner-icon {
    width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.2);
    display:grid;place-items:center;margin:0 auto 12px;
}
.rc-banner-title { font-size:20px;font-weight:800;color:#fff;letter-spacing:-.3px }
.rc-banner-num { font-size:11px;color:rgba(255,255,255,.75);margin-top:5px;font-family:var(--mono) }

/* Scrollable receipt body */
.rc-body { overflow-y:auto;flex:1;padding:0 }

/* The inner printable area — used both on screen and in print */
.rc-receipt {
    padding:18px 20px;
}

/* Shop header on receipt */
.rc-shop-name { font-size:14px;font-weight:800;color:var(--text);margin-bottom:2px }
.rc-shop-sub  { font-size:11px;color:var(--text-dim) }

/* Dotted divider */
.rc-divider {
    border:none;border-top:1.5px dashed var(--border);
    margin:12px 0;
}

/* Items list */
.rc-items { margin:0 0 4px }
.rc-item-row {
    display:flex;justify-content:space-between;align-items:baseline;
    gap:8px;padding:4px 0;
}
.rc-item-name { font-size:12px;color:var(--text-sub);flex:1;min-width:0 }
.rc-item-meta { font-size:10px;color:var(--text-dim);margin-top:1px }
.rc-item-total { font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text);flex-shrink:0 }
.rc-item-modified { font-size:10px;color:var(--amber);font-style:italic }

/* Total row */
.rc-total-row {
    display:flex;justify-content:space-between;align-items:center;
    padding:10px 0 0;
}
.rc-total-label { font-size:14px;font-weight:700;color:var(--text) }
.rc-total-amount { font-size:26px;font-weight:800;font-family:var(--mono);color:var(--green);line-height:1 }
.rc-total-currency { font-size:12px;font-weight:600;color:var(--text-dim);margin-left:3px }

/* Payment breakdown */
.rc-payments {
    background:var(--bg);border:1px solid var(--border);border-radius:10px;
    padding:10px 12px;margin:12px 0;
}
.rc-pay-section-label {
    font-size:9px;font-weight:800;color:var(--text-dim);
    text-transform:uppercase;letter-spacing:.7px;margin-bottom:8px;
}
.rc-pay-row {
    display:flex;justify-content:space-between;align-items:center;padding:3px 0;
}
.rc-pay-method { font-size:11px;color:var(--text-sub) }
.rc-pay-ref { font-size:10px;color:var(--text-dim);font-family:var(--mono);margin-left:6px }
.rc-pay-amount { font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text) }
.rc-pay-amount.credit { color:var(--amber) }

/* Credit badge */
.rc-credit-badge {
    display:flex;align-items:center;justify-content:space-between;
    background:rgba(245,158,11,.08);border:1.5px solid var(--amber);
    border-radius:9px;padding:9px 12px;margin:10px 0;
}
.rc-credit-label { font-size:11px;font-weight:700;color:var(--amber) }
.rc-credit-amount { font-size:13px;font-weight:800;font-family:var(--mono);color:var(--amber) }

/* Customer info */
.rc-customer {
    display:flex;align-items:center;gap:8px;
    padding:8px 0;font-size:11px;color:var(--text-sub);
}

/* Notes */
.rc-notes {
    font-size:11px;color:var(--text-dim);font-style:italic;
    padding:6px 0;border-top:1px solid var(--border);margin-top:4px;
}

/* Footer meta */
.rc-meta {
    display:flex;justify-content:space-between;align-items:center;
    font-size:10px;color:var(--text-dim);margin-top:8px;font-family:var(--mono);
}

/* Actions */
.rc-actions {
    display:grid;grid-template-columns:1fr 1fr 1.4fr;gap:8px;
    padding:14px 20px;border-top:1px solid var(--border);flex-shrink:0;
    background:var(--surface);
}
.rc-print-btn {
    height:44px;background:transparent;color:var(--text-sub);
    border:1.5px solid var(--border);border-radius:11px;
    font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font);
    display:flex;align-items:center;justify-content:center;gap:6px;
    transition:all var(--tr);
}
.rc-print-btn:hover { border-color:var(--accent);color:var(--accent) }
.rc-new-btn {
    height:44px;background:var(--accent);color:#fff;border:none;border-radius:11px;
    font-size:13px;font-weight:800;cursor:pointer;font-family:var(--font);
    display:flex;align-items:center;justify-content:center;gap:7px;
    box-shadow:0 4px 14px rgba(59,111,212,.25);transition:opacity var(--tr);
}
.rc-new-btn:hover { opacity:.9 }

/* ── Print styles ────────────────────────────── */
@media print {
    /* Hide everything on the page */
    body > * { visibility:hidden !important }
    /* Show only the receipt print area */
    #receipt-print-area, #receipt-print-area * { visibility:visible !important }
    #receipt-print-area {
        position:fixed !important;
        top:0 !important; left:0 !important;
        width:76mm !important;
        padding:4mm !important;
        background:#fff !important;
        color:#000 !important;
    }
    @page { size:80mm auto; margin:0 }
    /* Override CSS variables for print (force light) */
    #receipt-print-area { --text:#000;--text-sub:#333;--text-dim:#555;--border:#ddd;
                          --bg:#f9f9f9;--surface:#fff;--green:#1a7a5e;--amber:#b45309;--accent:#2c5cbd }
    .rc-banner { display:none !important }
    .rc-actions { display:none !important }
    .rc-receipt { padding:0 !important }
    .rc-total-amount { font-size:18px !important }
}
</style>

{{-- JS: open isolated print window --}}
<script>
document.addEventListener('livewire:initialized', function () {
    Livewire.on('open-print-window', function (params) {
        window.open(params.url, '_blank', 'width=420,height=700,menubar=no,toolbar=no,scrollbars=yes');
    });
}, { once: true });
</script>

<div class="rc-overlay">
  <div class="rc-card">

    {{-- Success banner --}}
    <div class="rc-banner">
      <div class="rc-banner-icon">
        <svg width="26" height="26" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <div class="rc-banner-title">Sale Complete!</div>
      <div class="rc-banner-num">{{ $completedSale->sale_number }}</div>
    </div>

    {{-- Scrollable body --}}
    <div class="rc-body">

      {{-- Printable receipt area --}}
      <div class="rc-receipt" id="receipt-print-area">

        {{-- Shop header (visible on print) --}}
        <div style="text-align:center;padding-bottom:10px">
          <div class="rc-shop-name">{{ $completedSale->shop->name ?? config('app.name') }}</div>
          <div class="rc-shop-sub">Point of Sale Receipt</div>
        </div>

        <hr class="rc-divider">

        {{-- Items — grouped by product+price so qty is aggregated --}}
        @php
          $rcGrouped = $completedSale->items
            ->groupBy(fn($i) => $i->product_id.'_'.$i->actual_unit_price.'_'.($i->is_full_box?'b':'i'))
            ->map(fn($g) => (object)[
              'name'     => $g->first()->product->name ?? '—',
              'qty'      => $g->sum('quantity_sold'),
              'price'    => $g->first()->actual_unit_price,
              'total'    => $g->sum('line_total'),
              'full_box' => $g->first()->is_full_box,
              'modified' => $g->contains('price_was_modified', true),
              'orig'     => $g->first()->original_unit_price,
            ])->values();
        @endphp
        <div class="rc-items">
          @foreach($rcGrouped as $item)
          <div class="rc-item-row">
            <div style="flex:1;min-width:0">
              <div class="rc-item-name">{{ $item->name }}</div>
              <div class="rc-item-meta">
                {{ $item->qty }} {{ $item->full_box ? ($item->qty === 1 ? 'box' : 'boxes') : 'items' }}
                × {{ number_format($item->price) }} RWF
                @if($item->modified)
                  <span class="rc-item-modified">(was {{ number_format($item->orig) }})</span>
                @endif
              </div>
            </div>
            <div class="rc-item-total">{{ number_format($item->total) }}</div>
          </div>
          @endforeach
        </div>

        <hr class="rc-divider">

        {{-- Total --}}
        <div class="rc-total-row">
          <span class="rc-total-label">Total</span>
          <div>
            <span class="rc-total-amount">{{ number_format($completedSale->total) }}</span>
            <span class="rc-total-currency">RWF</span>
          </div>
        </div>

        {{-- Payment breakdown --}}
        @if($completedSale->payments && $completedSale->payments->count() > 0)
        <div class="rc-payments">
          <div class="rc-pay-section-label">Paid via</div>
          @foreach($completedSale->payments as $payment)
          <div class="rc-pay-row">
            <span class="rc-pay-method">
              {{ match($payment->payment_method->value) {
                'cash'          => '💵 Cash',
                'card'          => '💳 Card',
                'mobile_money'  => '📱 Mobile Money',
                'bank_transfer' => '🏦 Bank Transfer',
                'credit'        => '📋 Credit',
                default         => ucfirst($payment->payment_method->value)
              } }}
              @if($payment->reference)
              <span class="rc-pay-ref">({{ $payment->reference }})</span>
              @endif
            </span>
            <span class="rc-pay-amount {{ $payment->payment_method->value === 'credit' ? 'credit' : '' }}">
              {{ number_format($payment->amount) }}
            </span>
          </div>
          @endforeach
        </div>
        @endif

        {{-- Credit outstanding highlight --}}
        @if($completedSale->has_credit && $completedSale->credit_amount > 0)
        <div class="rc-credit-badge">
          <span class="rc-credit-label">Credit recorded</span>
          <span class="rc-credit-amount">{{ number_format($completedSale->credit_amount) }} RWF</span>
        </div>
        @endif

        {{-- Customer --}}
        @if($completedSale->customer_name)
        <hr class="rc-divider">
        <div class="rc-customer">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>{{ $completedSale->customer_name }}</span>
          @if($completedSale->customer_phone)
          <span style="color:var(--text-dim)">· {{ $completedSale->customer_phone }}</span>
          @endif
        </div>
        @endif

        {{-- Notes --}}
        @if($completedSale->notes)
        <div class="rc-notes">Note: {{ $completedSale->notes }}</div>
        @endif

        {{-- Footer meta --}}
        <hr class="rc-divider">
        <div class="rc-meta">
          <span>{{ ($completedSale->sale_date ?? $completedSale->created_at)->format('d M Y H:i') }}</span>
          <span>{{ $completedSale->soldBy->name ?? '—' }}</span>
        </div>
        <div style="text-align:center;font-size:10px;color:var(--text-dim);margin-top:10px;padding-bottom:4px">
          Thank you for your business
        </div>

      </div>{{-- /receipt-print-area --}}
    </div>{{-- /rc-body --}}

    {{-- Actions --}}
    <div class="rc-actions">
      {{-- View Receipt --}}
      <a href="{{ route('shop.receipt.print', $completedSale->id) }}" target="_blank"
         class="rc-print-btn" style="text-decoration:none">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
        View
      </a>
      {{-- Print Receipt --}}
      <button wire:click="printReceipt" class="rc-print-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polyline points="6 9 6 2 18 2 18 9"/>
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
          <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Print
      </button>
      {{-- New Sale --}}
      <button wire:click="closeReceipt" class="rc-new-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
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
@endif
