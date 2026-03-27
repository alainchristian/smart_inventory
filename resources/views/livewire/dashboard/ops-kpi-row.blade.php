<div class="ops-strip">

  {{-- 1: Sellable Boxes --}}
  <div class="ops-strip-item" style="animation:fadeUp .3s ease .20s both">
    <div class="ops-strip-icon" style="background:var(--accent-dim)">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
           stroke="var(--accent)" stroke-width="2" stroke-linecap="round"
           stroke-linejoin="round">
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
        <line x1="12" y1="22.08" x2="12" y2="12"/>
      </svg>
    </div>
    <div>
      <div class="ops-strip-value">{{ number_format($sellableBoxes) }}</div>
      <div class="ops-strip-label">Sellable Boxes</div>
      <div class="ops-strip-sub">
        WH {{ $warehouseBoxes }} · Shops {{ $shopBoxes }}
        @if($damagedBoxes > 0)
          <span style="color:var(--red)"> · {{ $damagedBoxes }} dmg</span>
        @endif
      </div>
    </div>
  </div>

  {{-- 2: Active Transfers --}}
  <div class="ops-strip-item" style="animation:fadeUp .3s ease .25s both">
    <div class="ops-strip-icon" style="background:var(--amber-dim)">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
           stroke="var(--amber)" stroke-width="2" stroke-linecap="round"
           stroke-linejoin="round">
        <polyline points="17 1 21 5 17 9"/>
        <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
        <polyline points="7 23 3 19 7 15"/>
        <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
      </svg>
    </div>
    <div>
      <div class="ops-strip-value">{{ $activeTransfers }}</div>
      <div class="ops-strip-label">Active Transfers</div>
      <div class="ops-strip-sub">
        {{ $pendingCount }} pending · {{ $inTransitCount }} in transit
      </div>
    </div>
  </div>

  {{-- 3: Low Stock Alerts --}}
  <div class="ops-strip-item" style="animation:fadeUp .3s ease .30s both">
    <div class="ops-strip-icon" style="background:var(--red-dim)">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
           stroke="var(--red)" stroke-width="2" stroke-linecap="round"
           stroke-linejoin="round">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
    </div>
    <div>
      <div class="ops-strip-value" style="{{ $lowStockCritical > 0 ? 'color:var(--red)' : '' }}">
        {{ $lowStockTotal }}
      </div>
      <div class="ops-strip-label">Low Stock Alerts</div>
      <div class="ops-strip-sub">
        {{ $lowStockCritical }} critical · {{ $lowStockTotal - $lowStockCritical }} warning
      </div>
    </div>
  </div>

  {{-- 4: Fulfillment Rate --}}
  <div class="ops-strip-item" style="animation:fadeUp .3s ease .35s both">
    <div class="ops-strip-icon" style="background:var(--green-dim)">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
           stroke="var(--green)" stroke-width="2" stroke-linecap="round"
           stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
    </div>
    <div>
      <div class="ops-strip-value" style="color:{{ $fulfillmentRate >= 95 ? 'var(--green)' : 'var(--amber)' }}">
        {{ $fulfillmentRate }}%
      </div>
      <div class="ops-strip-label">Fulfillment Rate</div>
      <div class="ops-strip-sub">
        <span style="color:{{ $fulfillmentRate >= 95 ? 'var(--green)' : 'var(--amber)' }}">
          {{ $fulfillmentRate >= 95 ? 'On target' : 'Below target' }}
        </span>
        &nbsp;&middot; Transfer accuracy
      </div>
    </div>
  </div>

</div>
