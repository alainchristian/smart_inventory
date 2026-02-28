<div class="biz-kpi-grid">

  {{-- Card 1: Sales --}}
  <div class="bkpi pink" style="animation:fadeUp .4s ease .05s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon pink">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
        </div>
        <span class="bkpi-name">Sales</span>
      </div>
      <span class="bkpi-pct {{ $sales['growth'] >= 0 ? 'up' : 'down' }}">
        {{ $sales['growth'] >= 0 ? '↑' : '↓' }} {{ abs($sales['growth']) }}%
      </span>
    </div>
    <div class="bkpi-value">{{ number_format($sales['month']) }}</div>
    <div class="bkpi-meta">{{ number_format($sales['count']) }} transactions · RWF</div>
    <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
      @foreach(['today' => 'Today', 'week' => 'Week', 'month' => 'Month'] as $k => $lbl)
      <div style="text-align:center;flex:1">
        <div style="font-size:11px;font-weight:700;color:{{ $sales[$k] > 0 ? 'var(--accent)' : 'var(--text-dim)' }};font-family:var(--mono)">
          {{ $sales[$k] > 0 ? number_format($sales[$k]) : '0' }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">{{ $lbl }}</div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Card 2: Profit --}}
  <div class="bkpi green" style="animation:fadeUp .4s ease .10s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
            <polyline points="17 6 23 6 23 12"/>
          </svg>
        </div>
        <span class="bkpi-name">Profit</span>
      </div>
      <span class="bkpi-pct green">{{ $profit['margin_pct'] }}%</span>
    </div>
    <div class="bkpi-value" style="color:var(--green)">
      {{ number_format($profit['margin_rwf']) }}
    </div>
    <div class="bkpi-meta">Expected margin · RWF</div>
    <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
      @foreach(['today' => 'Today', 'week' => 'Week', 'month' => 'Month'] as $k => $lbl)
      <div style="text-align:center;flex:1">
        <div style="font-size:11px;font-weight:700;color:{{ $profit[$k] > 0 ? 'var(--green)' : 'var(--text-dim)' }};font-family:var(--mono)">
          {{ $profit[$k] > 0 ? number_format($profit[$k]) : '0' }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">{{ $lbl }}</div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Card 3: Inventory --}}
  <div class="bkpi blue" style="animation:fadeUp .4s ease .15s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
          </svg>
        </div>
        <span class="bkpi-name">Inventory</span>
      </div>
      <span class="bkpi-pct blue">{{ $inventory['markup_pct'] }}%</span>
    </div>
    <div class="bkpi-value" style="display:flex;align-items:baseline;gap:6px;font-size:16px">
      <span>{{ number_format($inventory['cost']) }}</span>
      <span style="font-size:12px;color:var(--text-dim)">→</span>
      <span style="color:var(--text-sub);font-size:14px">{{ number_format($inventory['retail']) }}</span>
    </div>
    <div class="bkpi-meta">Cost → Retail · RWF</div>
    <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
      <div style="text-align:center;flex:1">
        <div style="font-size:11px;font-weight:700;color:{{ $inventory['warehouse'] > 0 ? 'var(--blue)' : 'var(--text-dim)' }};font-family:var(--mono)">
          {{ $inventory['warehouse'] > 0 ? number_format($inventory['warehouse']) : '0' }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Warehouse</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:11px;font-weight:700;color:{{ $inventory['shop'] > 0 ? 'var(--blue)' : 'var(--text-dim)' }};font-family:var(--mono)">
          {{ $inventory['shop'] > 0 ? number_format($inventory['shop']) : '0' }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Shops</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:11px;font-weight:700;color:{{ ($inventory['wh_items'] + $inventory['shop_items']) > 0 ? 'var(--accent)' : 'var(--text-dim)' }};font-family:var(--mono)">
          {{ number_format($inventory['wh_items'] + $inventory['shop_items']) }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Items</div>
      </div>
    </div>
  </div>

  {{-- Card 4: Locations --}}
  <div class="bkpi violet" style="animation:fadeUp .4s ease .20s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon violet">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
        </div>
        <span class="bkpi-name">Locations</span>
      </div>
      <span class="bkpi-pct violet">Active</span>
    </div>
    <div class="bkpi-value">{{ $locations['warehouses'] + $locations['shops'] }}</div>
    <div class="bkpi-meta">Total locations · Network</div>
    <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
      <div style="text-align:center;flex:1">
        <div style="font-size:13px;font-weight:700;color:var(--violet);font-family:var(--mono)">
          {{ $locations['warehouses'] }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Warehouses</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:13px;font-weight:700;color:var(--green);font-family:var(--mono)">
          {{ $locations['shops'] }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Shops</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:13px;font-weight:700;color:var(--accent);font-family:var(--mono)">
          {{ $locations['users'] }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Users</div>
      </div>
    </div>
  </div>

</div>
