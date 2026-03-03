<div class="biz-kpi-grid">

  {{-- Card 1: Active Products --}}
  <div class="bkpi blue" style="animation:fadeUp .4s ease .05s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
          </svg>
        </div>
        <span class="bkpi-name">Products</span>
      </div>
      <span class="bkpi-pct blue">{{ $totalActive }} active</span>
    </div>
    <div class="bkpi-value">{{ number_format($totalProducts) }}</div>
    <div class="bkpi-meta">Total in catalog &middot; all categories</div>
    <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
      <div style="text-align:center;flex:1">
        <div style="font-size:13px;font-weight:700;color:var(--green);font-family:var(--mono)">{{ $totalActive }}</div>
        <div style="font-size:12px;color:var(--text-dim);margin-top:1px">Active</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:13px;font-weight:700;color:{{ $totalInactive > 0 ? 'var(--amber)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ $totalInactive }}</div>
        <div style="font-size:12px;color:var(--text-dim);margin-top:1px">Inactive</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:13px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">
          {{ $totalProducts > 0 ? round($totalActive / $totalProducts * 100) : 0 }}%
        </div>
        <div style="font-size:12px;color:var(--text-dim);margin-top:1px">Coverage</div>
      </div>
    </div>
  </div>

  {{-- Card 2: Low Stock --}}
  <div class="bkpi {{ $lowStockCount > 0 ? 'pink' : 'green' }}" style="animation:fadeUp .4s ease .10s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon {{ $lowStockCount > 0 ? 'pink' : 'green' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
        </div>
        <span class="bkpi-name">Low Stock</span>
      </div>
      @if($lowStockCount > 0)
        <span class="bkpi-pct down">Alert</span>
      @else
        <span class="bkpi-pct up">All OK</span>
      @endif
    </div>
    <div class="bkpi-value" style="{{ $lowStockCount > 0 ? 'color:var(--pink)' : 'color:var(--green)' }}">{{ $lowStockCount }}</div>
    <div class="bkpi-meta">Products below threshold &middot; all locations</div>
    <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
      <div style="text-align:center;flex:1">
        <div style="font-size:13px;font-weight:700;color:{{ $lowStockCount > 0 ? 'var(--amber)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ $lowStockCount }}</div>
        <div style="font-size:12px;color:var(--text-dim);margin-top:1px">Low</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:13px;font-weight:700;color:{{ $zeroStockCount > 0 ? 'var(--red)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ $zeroStockCount }}</div>
        <div style="font-size:12px;color:var(--text-dim);margin-top:1px">Out of stock</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:13px;font-weight:700;color:var(--green);font-family:var(--mono)">{{ max(0, $totalActive - $lowStockCount) }}</div>
        <div style="font-size:12px;color:var(--text-dim);margin-top:1px">Healthy</div>
      </div>
    </div>
  </div>

  {{-- Card 3: Price Overrides --}}
  <div class="bkpi {{ $priceOverrideCount > 0 ? 'pink' : 'green' }}" style="animation:fadeUp .4s ease .15s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon {{ $priceOverrideCount > 0 ? 'pink' : 'green' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
        </div>
        <span class="bkpi-name">Price Overrides</span>
      </div>
      <span class="bkpi-pct {{ $priceOverrideCount > 0 ? 'down' : 'up' }}">{{ ucfirst($periodLabel) }}</span>
    </div>
    <div class="bkpi-value" style="{{ $priceOverrideCount > 0 ? 'color:var(--pink)' : 'color:var(--green)' }}">{{ $priceOverrideCount }}</div>
    <div class="bkpi-meta">Products with modified prices &middot; {{ $periodLabel }}</div>
    <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
      @if($priceOverrideCount > 0)
        <div style="font-size:13px;color:var(--pink);font-weight:600">
          &#9888; Review price changes in detail panel
        </div>
      @else
        <div style="font-size:13px;color:var(--green);font-weight:600">
          &#10003; No unauthorised price changes this {{ $periodLabel }}
        </div>
      @endif
    </div>
  </div>

  {{-- Card 4: Best Margin (owner-only) --}}
  @can('viewPurchasePrice')
  <div class="bkpi violet" style="animation:fadeUp .4s ease .20s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon violet">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
            <polyline points="17 6 23 6 23 12"/>
          </svg>
        </div>
        <span class="bkpi-name">Best Margin</span>
      </div>
      <span class="bkpi-pct violet">Owner</span>
    </div>
    @if($bestMarginPct !== null)
      <div class="bkpi-value" style="color:var(--violet)">{{ $bestMarginPct }}%</div>
      <div class="bkpi-meta" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $bestMarginName }}">
        {{ $bestMarginName }}
      </div>
      <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
        <div style="text-align:center;flex:1">
          <div style="font-size:13px;font-weight:700;color:var(--violet);font-family:var(--mono)">{{ $bestMarginPct }}%</div>
          <div style="font-size:12px;color:var(--text-dim);margin-top:1px">Best</div>
        </div>
        <div style="text-align:center;flex:1">
          <div style="font-size:13px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ $totalActive }}</div>
          <div style="font-size:12px;color:var(--text-dim);margin-top:1px">Products</div>
        </div>
        <div style="text-align:center;flex:1">
          <div style="font-size:13px;font-weight:700;color:var(--green);font-family:var(--mono)">Active</div>
          <div style="font-size:12px;color:var(--text-dim);margin-top:1px">Catalog</div>
        </div>
      </div>
    @else
      <div class="bkpi-value" style="color:var(--text-dim)">--</div>
      <div class="bkpi-meta">No pricing data available</div>
      <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
        <div style="text-align:center;flex:1">
          <div style="font-size:13px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">--</div>
          <div style="font-size:12px;color:var(--text-dim);margin-top:1px">No data</div>
        </div>
      </div>
    @endif
  </div>
  @endcan

</div>