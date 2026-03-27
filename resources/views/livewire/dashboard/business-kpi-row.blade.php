{{--
    business-kpi-row.blade.php
    Card 4 (Locations) updated to match Image 2 design:
    - Location pin icon (replaces house icon)
    - "Active" badge in header (replaces empty space)
    - Large headline total (warehouses + shops)
    - "Total locations &middot; Network" subtitle line
    - Smaller coloured sub-stats (Warehouses / Shops / Users)
      matching the pattern of the other three cards
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

    <div class="bkpi-value">{{ number_format($sales['current']) }}</div>
    <div class="bkpi-meta">{{ number_format($sales['count']) }} transactions &middot; RWF</div>

    @php
      $spData  = !empty($salesSparkline) ? $salesSparkline : array_fill(0, 7, 0);
      $spMax   = max(max($spData), 1);
      $spMin   = min($spData);
      $spRange = max($spMax - $spMin, 1);
      $spXs   = [0, 13.3, 26.6, 40, 53.3, 66.6, 80];
      $spPts  = '';
      foreach ($spData as $i => $v) {
          $y = 22 - round((($v - $spMin) / $spRange) * 20);
          $spPts .= $spXs[$i] . ',' . $y . ' ';
      }
      $spPoly = trim($spPts);
      // Closed polygon for fill: extend to bottom corners
      $spFill = $spPoly . ' 80,24 0,24';
    @endphp
    <svg viewBox="0 0 80 24" width="80" height="24"
         style="margin-top:8px;margin-bottom:2px;display:block;overflow:visible">
      <defs>
        <linearGradient id="spark-fill-sales" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="var(--pink)" stop-opacity="0.18"/>
          <stop offset="100%" stop-color="var(--pink)" stop-opacity="0"/>
        </linearGradient>
      </defs>
      <polygon fill="url(#spark-fill-sales)" points="{{ $spFill }}"/>
      <polyline fill="none" stroke="var(--pink)" stroke-width="1.5"
                stroke-linecap="round" stroke-linejoin="round"
                points="{{ $spPoly }}"/>
    </svg>

    <div style="display:flex;gap:16px;margin-top:4px;padding-top:10px;border-top:1px solid var(--border)">
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
      <span class="bkpi-pct green" title="{{ $profit['margin_label'] }}">
        {{ $profit['margin_pct'] }}%
      </span>
    </div>

    <div class="bkpi-value" style="color:var(--green)">
      {{ number_format($profit['margin_rwf']) }}
    </div>
    <div class="bkpi-meta">{{ $profit['margin_label'] }} &middot; RWF</div>

    @php
      $spData2  = !empty($profitSparkline) ? $profitSparkline : array_fill(0, 7, 0);
      $spMax2   = max(max($spData2), 1);
      $spMin2   = min($spData2);
      $spRange2 = max($spMax2 - $spMin2, 1);
      $spXs2    = [0, 13.3, 26.6, 40, 53.3, 66.6, 80];
      $spPts2   = '';
      foreach ($spData2 as $i2 => $v2) {
          $y2 = 22 - round((($v2 - $spMin2) / $spRange2) * 20);
          $spPts2 .= $spXs2[$i2] . ',' . $y2 . ' ';
      }
      $spPoly2 = trim($spPts2);
      $spFill2 = $spPoly2 . ' 80,24 0,24';
    @endphp
    <svg viewBox="0 0 80 24" width="80" height="24"
         style="margin-top:8px;margin-bottom:2px;display:block;overflow:visible">
      <defs>
        <linearGradient id="spark-fill-profit" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="var(--green)" stop-opacity="0.18"/>
          <stop offset="100%" stop-color="var(--green)" stop-opacity="0"/>
        </linearGradient>
      </defs>
      <polygon fill="url(#spark-fill-profit)" points="{{ $spFill2 }}"/>
      <polyline fill="none" stroke="var(--green)" stroke-width="1.5"
                stroke-linecap="round" stroke-linejoin="round"
                points="{{ $spPoly2 }}"/>
    </svg>

    <div style="display:flex;gap:16px;margin-top:4px;padding-top:10px;border-top:1px solid var(--border)">
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

  @php
  if (!function_exists('shortNumber')) {
      function shortNumber($n) {
          $n = (float) $n;
          if ($n >= 1000000000) return number_format($n / 1000000000, 1) . 'B';
          if ($n >= 1000000)    return number_format($n / 1000000, 1) . 'M';
          if ($n >= 1000)       return number_format($n / 1000, 1) . 'K';
          return number_format($n);
      }
  }
  @endphp

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
      <span class="bkpi-pct blue" title="{{ $inventory['markup_label'] }}">
        {{ $inventory['markup_pct'] }}%
      </span>
    </div>

    <div class="bkpi-value">{{ shortNumber($inventory['cost']) }} → {{ shortNumber($inventory['retail']) }}</div>
    <div class="bkpi-meta">Cost → Retail value &middot; RWF</div>

    <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
      <div style="text-align:center;flex:1">
        <div style="font-size:11px;font-weight:700;color:var(--accent);font-family:var(--mono)">
          {{ number_format($inventory['wh_boxes']) }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">WH Boxes</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:11px;font-weight:700;color:var(--accent);font-family:var(--mono)">
          {{ number_format($inventory['shop_boxes']) }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Shop Boxes</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:11px;font-weight:700;color:var(--accent);font-family:var(--mono)">
          {{ number_format($inventory['total_boxes']) }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Total Boxes</div>
      </div>
    </div>

    {{-- Fill rate mini-bar --}}
    <div style="margin-top:10px;padding-top:8px;border-top:1px solid var(--border)">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
        <span style="font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px">Box fill rate</span>
        <span style="font-size:11px;font-weight:700;font-family:var(--mono);color:var(--accent)">{{ $inventory['fill_rate'] ?? '—' }}%</span>
      </div>
      <div style="height:4px;background:var(--surface2);border-radius:2px;overflow:hidden">
        <div style="height:100%;width:{{ min($inventory['fill_rate'] ?? 0, 100) }}%;background:var(--accent);border-radius:2px;transition:width .3s"></div>
      </div>
    </div>
  </div>

  {{-- Card 4: Locations - updated to match Image 2 --}}
  <div class="bkpi violet" style="animation:fadeUp .4s ease .20s both">

    {{-- Header row: pin icon + label + "Active" badge --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px">
        <div class="bkpi-icon violet">
          {{-- Location pin (replaces old house icon to match Image 2) --}}
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
        </div>
        <span class="bkpi-name">Locations</span>
      </div>
      {{-- "Active" badge matches the pill style of other cards' % badges --}}
      <span class="bkpi-pct violet">Active</span>
    </div>

    {{-- Headline: total (warehouses + shops), matching the large number style of other cards --}}
    <div class="bkpi-value">{{ $locations['warehouses'] + $locations['shops'] }}</div>
    <div class="bkpi-meta">Total locations &middot; Network</div>

    {{-- Sub-stats row: three coloured figures, same pattern as Sales / Profit / Inventory --}}
    <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
      <div style="text-align:center;flex:1">
        <div style="font-size:11px;font-weight:700;color:var(--violet);font-family:var(--mono)">
          {{ $locations['warehouses'] }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Warehouses</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:11px;font-weight:700;color:var(--green);font-family:var(--mono)">
          {{ $locations['shops'] }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Shops</div>
      </div>
      <div style="text-align:center;flex:1">
        <div style="font-size:11px;font-weight:700;color:var(--accent);font-family:var(--mono)">
          {{ $locations['users'] }}
        </div>
        <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Users</div>
      </div>
    </div>

    {{-- Network health mini-row --}}
    <div style="margin-top:10px;padding-top:8px;border-top:1px solid var(--border);display:flex;align-items:center;gap:8px">
      @if($locations['warehouses'] + $locations['shops'] === $locations['active_warehouses'] + $locations['active_shops'])
        <div style="width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 6px var(--green);flex-shrink:0"></div>
        <span style="font-size:11px;color:var(--text-sub)">All locations operational</span>
      @else
        <div style="width:8px;height:8px;border-radius:50%;background:var(--amber);box-shadow:0 0 6px var(--amber);flex-shrink:0"></div>
        <span style="font-size:11px;color:var(--text-sub)">{{ ($locations['warehouses'] + $locations['shops']) - ($locations['active_warehouses'] + $locations['active_shops']) }} location(s) inactive</span>
      @endif
    </div>

  </div>

</div>