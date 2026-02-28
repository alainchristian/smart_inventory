<div class="ops-kpi-grid">

  <div class="okpi" style="animation:fadeUp .4s ease .25s both">
    <div class="okpi-icon blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
      </svg>
    </div>
    <div class="okpi-body">
      <div class="okpi-value">{{ number_format($activeBoxes) }}</div>
      <div class="okpi-label">Active Boxes</div>
      <div class="okpi-sub" style="white-space:normal;overflow:visible">WH: {{ $warehouseBoxes }} &nbsp;·&nbsp; Shops: {{ $shopBoxes }}</div>
    </div>
    <span class="okpi-delta up">↑ 12%</span>
  </div>

  <div class="okpi" style="animation:fadeUp .4s ease .30s both">
    <div class="okpi-icon amber">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/>
        <polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>
      </svg>
    </div>
    <div class="okpi-body">
      <div class="okpi-value">{{ $activeTransfers }}</div>
      <div class="okpi-label">Active Transfers</div>
      <div class="okpi-sub" style="white-space:normal;overflow:visible">Transit: {{ $inTransitCount }} &nbsp;·&nbsp; Pending: {{ $pendingCount }}</div>
    </div>
    <span class="okpi-delta warn">{{ $pendingCount }} pending</span>
  </div>

  <div class="okpi" style="animation:fadeUp .4s ease .35s both">
    <div class="okpi-icon red">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
    </div>
    <div class="okpi-body">
      <div class="okpi-value">{{ $lowStockTotal }}</div>
      <div class="okpi-label">Low Stock Alerts</div>
      <div class="okpi-sub">{{ $lowStockCritical }} critical · {{ $lowStockTotal - $lowStockCritical }} warning</div>
    </div>
    <span class="okpi-delta down">↑ {{ $lowStockTotal }}</span>
  </div>

  <div class="okpi" style="animation:fadeUp .4s ease .40s both">
    <div class="okpi-icon green">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
        <line x1="1" y1="10" x2="23" y2="10"/>
      </svg>
    </div>
    <div class="okpi-body">
      <div class="okpi-value">{{ $todayCount }}</div>
      <div class="okpi-label">Today's Transactions</div>
      <div class="okpi-sub">{{ number_format($todayRevenue) }} RWF</div>
    </div>
    <span class="okpi-delta {{ $revenueGrowth >= 0 ? 'up' : 'down' }}">
      {{ $revenueGrowth >= 0 ? '↑' : '↓' }} {{ abs($revenueGrowth) }}%
    </span>
  </div>

</div>
