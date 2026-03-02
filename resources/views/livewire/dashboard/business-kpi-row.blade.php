{{--
    business-kpi-row.blade.php
    FIX #5: Sales headline now shows $sales['current'] (period-selected value)
            instead of hardcoded $sales['month'].
    FIX #6: Profit badge label distinguishes "Realised margin" from inventory markup.
            Inventory badge label shows "Potential markup" (not the same thing).
--}}
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

    {{-- FIX #5: headline = selected period value, not always month --}}
    <div class="bkpi-value">{{ number_format($sales['current']) }}</div>
    <div class="bkpi-meta">{{ number_format($sales['count']) }} transactions · RWF</div>

    {{-- Sub-row: fixed reference points (Today / Week / Month) --}}
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
      {{-- FIX #6: label clarifies this is realised margin on sales (not inventory markup) --}}
      <span class="bkpi-pct green" title="{{ $profit['margin_label'] }}">
        {{ $profit['margin_pct'] }}%
      </span>
    </div>

    <div class="bkpi-value" style="color:var(--green)">
      {{ number_format($profit['margin_rwf']) }}
    </div>
    {{-- FIX #6: explicit label so owner knows what the % means --}}
    <div class="bkpi-meta">{{ $profit['margin_label'] }} · RWF</div>

    {{-- Sub-row: Today / Week / Month profit breakdowns --}}
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
      {{-- FIX #6: label clarifies this is potential markup, not realised margin --}}
      <span class="bkpi-pct blue" title="{{ $inventory['markup_label'] }}">
        {{ $inventory['markup_pct'] }}%
      </span>
    </div>

    <div class="bkpi-value" style="display:flex;align-items:baseline;gap:6px;font-size:16px">
      <span>{{ number_format($inventory['cost']) }}</span>
      <span style="font-size:12px;color:var(--text-dim)">→</span>
      <span style="color:var(--text-sub);font-size:14px">{{ number_format($inventory['retail']) }}</span>
    </div>
    {{-- FIX #6: explicit label so owner knows what the % badge means --}}
    <div class="bkpi-meta">{{ $inventory['markup_label'] }} · Cost → Retail · RWF</div>

    {{-- Sub-row: Warehouse / Shop breakdown --}}
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
        <div style="font-size:11px;font-weight:700;color:{{ ($inventory['wh_items'] + $inventory['shop_items']) > 0 ? 'var(--blue)' : 'var(--text-dim)' }};font-family:var(--mono)">
          {{ number_format($inventory['wh_items'] + $inventory['shop_items']) }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Items</div>
      </div>
    </div>
  </div>

  {{-- Card 4: Locations --}}
  <div class="bkpi violet" style="animation:fadeUp .4s ease .20s both">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon violet">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
        </div>
        <span class="bkpi-name">Locations</span>
      </div>
    </div>
    <div style="display:flex;gap:16px;margin-top:16px">
      <div style="text-align:center;flex:1">
        <div style="font-size:22px;font-weight:800;color:var(--violet);font-family:var(--mono)">
          {{ $locations['warehouses'] }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:2px">Warehouses</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:22px;font-weight:800;color:var(--violet);font-family:var(--mono)">
          {{ $locations['shops'] }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:2px">Shops</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:22px;font-weight:800;color:var(--violet);font-family:var(--mono)">
          {{ $locations['users'] }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:2px">Users</div>
      </div>
    </div>
  </div>

</div>