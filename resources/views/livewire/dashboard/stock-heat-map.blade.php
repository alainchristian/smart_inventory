<style>
  /* Sticky product column — solid white so scrolled cells don't bleed through */
  .shm-sticky {
    background: var(--surface) !important;
    box-shadow: 3px 0 8px rgba(26,31,54,.07);
  }
  /* Mobile: narrow the product column and shrink cells */
  @media (max-width: 640px) {
    .shm-wrap   { padding: 0 10px 14px !important; }
    .shm-pcol   { max-width: 120px !important; min-width: 90px !important; }
    .shm-pname  { font-size: 11px !important; }
    .shm-rev    { display: none !important; }       /* hide revenue sub-line on mobile */
    .shm-cell   { min-height: 50px !important; padding: 5px 3px !important; }
    .shm-inner  { min-height: 50px !important; padding: 6px 4px !important; }
    .shm-num    { font-size: 16px !important; }
    .shm-colhdr { min-width: 72px !important; }
    .shm-badge  { width: 70px !important; padding: 4px 6px !important; }
  }
</style>

<div class="card" style="animation:fadeUp .4s ease .55s both">

  {{-- ── Header ── --}}
  <div class="card-header" style="align-items:flex-start;gap:16px;flex-wrap:wrap">
    <div style="flex:1;min-width:0">
      <div class="card-title">Stock Coverage Map</div>
      <div class="card-subtitle">Top 10 products by 30-day revenue &middot; shop cells show days of stock at current 14-day velocity</div>
    </div>

    {{-- Summary pills --}}
    @if(!empty($summary) && array_sum($summary) > 0)
    <div style="display:flex;gap:6px;flex-shrink:0;flex-wrap:wrap;align-items:center">
      @if($summary['crit'] > 0)
      <div style="display:inline-flex;align-items:center;gap:5px;
                  background:var(--red-dim);border:1px solid var(--red-glow);
                  padding:4px 10px;border-radius:20px">
        <span style="width:6px;height:6px;border-radius:50%;background:var(--red);display:inline-block;flex-shrink:0"></span>
        <span style="font-size:11px;font-weight:700;color:var(--red)">{{ $summary['crit'] }} Critical</span>
      </div>
      @endif
      @if($summary['warn'] > 0)
      <div style="display:inline-flex;align-items:center;gap:5px;
                  background:var(--amber-dim);border:1px solid var(--amber-glow);
                  padding:4px 10px;border-radius:20px">
        <span style="width:6px;height:6px;border-radius:50%;background:var(--amber);display:inline-block;flex-shrink:0"></span>
        <span style="font-size:11px;font-weight:700;color:var(--amber)">{{ $summary['warn'] }} Low</span>
      </div>
      @endif
      @if($summary['good'] > 0)
      <div style="display:inline-flex;align-items:center;gap:5px;
                  background:var(--green-dim);border:1px solid var(--green-glow);
                  padding:4px 10px;border-radius:20px">
        <span style="width:6px;height:6px;border-radius:50%;background:var(--green);display:inline-block;flex-shrink:0"></span>
        <span style="font-size:11px;font-weight:700;color:var(--green)">{{ $summary['good'] }} Healthy</span>
      </div>
      @endif
      @if($summary['overstock'] > 0)
      <div style="display:inline-flex;align-items:center;gap:5px;
                  background:rgba(56,189,248,.1);border:1px solid rgba(2,132,199,.2);
                  padding:4px 10px;border-radius:20px">
        <span style="width:6px;height:6px;border-radius:50%;background:#0284c7;display:inline-block;flex-shrink:0"></span>
        <span style="font-size:11px;font-weight:700;color:#0284c7">{{ $summary['overstock'] }} Overstock</span>
      </div>
      @endif
    </div>
    @endif
  </div>

  @if(empty($products) || empty($locations))
    <div style="padding:56px 20px;text-align:center">
      <div style="font-size:36px;margin-bottom:12px;opacity:.4">📊</div>
      <div style="font-size:13px;color:var(--text-dim)">No stock data available</div>
    </div>
  @else
    @php
      $warehouseCols  = collect($locations)->where('type', 'warehouse')->count();
      $shopCols       = collect($locations)->where('type', 'shop')->count();
      $totalLocations = count($locations);
    @endphp

    <div class="shm-wrap" style="overflow-x:auto;padding:0 20px 20px">
      <table style="width:100%;border-collapse:separate;border-spacing:0;font-size:12px;min-width:600px">

        {{-- ── Group label row (Warehouses / Shops) ── --}}
        <thead>
          <tr>
            <th style="padding:0 0 6px 0;min-width:200px"></th>
            @if($warehouseCols > 0)
            <th colspan="{{ $warehouseCols }}"
                style="text-align:center;padding:0 4px 6px;font-size:10px;font-weight:700;
                       color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em">
              Warehouses
            </th>
            @endif
            @if($shopCols > 0)
            <th colspan="{{ $shopCols }}"
                style="text-align:center;padding:0 4px 6px;font-size:10px;font-weight:700;
                       color:var(--green);text-transform:uppercase;letter-spacing:.1em;
                       {{ $warehouseCols > 0 ? 'border-left:2px solid var(--border);' : '' }}">
              Shops
            </th>
            @endif
          </tr>

          {{-- ── Column headers ── --}}
          <tr>
            <th class="shm-sticky"
                style="text-align:left;padding:8px 14px 14px;font-size:11px;font-weight:600;
                       color:var(--text-dim);white-space:nowrap;
                       position:sticky;left:0;z-index:20;
                       border-bottom:2px solid var(--border)">
              Product
              <span style="font-weight:400;font-size:10px;margin-left:6px;opacity:.6"># = revenue rank</span>
            </th>
            @foreach($locations as $index => $location)
              @php
                $isWarehouse  = $location['type'] === 'warehouse';
                $isFirstShop  = !$isWarehouse && $index === $warehouseCols;
              @endphp
              <th class="shm-colhdr"
                  style="text-align:center;padding:0 4px 14px;min-width:96px;
                         border-bottom:2px solid var(--border);
                         {{ $isFirstShop ? 'border-left:2px solid var(--border);' : '' }}"
                  title="{{ $location['name'] }}">
                <div class="shm-badge"
                     style="display:inline-flex;flex-direction:column;align-items:center;gap:3px;
                            background:{{ $isWarehouse ? 'var(--surface2)' : 'var(--green-dim)' }};
                            border:1px solid {{ $isWarehouse ? 'var(--border-hi)' : 'var(--green-glow)' }};
                            border-radius:8px;padding:6px 10px;width:88px;box-sizing:border-box">
                  <span style="font-size:10px;font-weight:700;
                               color:{{ $isWarehouse ? 'var(--text-sub)' : 'var(--green)' }};
                               white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
                               max-width:72px;text-align:center;display:block">
                    {{ \Illuminate\Support\Str::limit($location['name'], 14) }}
                  </span>
                  <span style="font-size:9px;color:var(--text-dim);font-weight:500;
                               text-transform:uppercase;letter-spacing:.06em">
                    {{ $isWarehouse ? 'wh' : 'shop' }}
                  </span>
                </div>
              </th>
            @endforeach
          </tr>
        </thead>

        {{-- ── Data rows ── --}}
        <tbody>
          @foreach($products as $pi => $product)
            @php $rank = $pi + 1; @endphp
            <tr>
              {{-- Product label --}}
              <td class="shm-sticky shm-pcol"
                  style="padding:5px 14px;font-size:12px;font-weight:600;color:var(--text);
                         white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;
                         position:sticky;left:0;z-index:10;
                         border-right:1px solid var(--border);border-bottom:1px solid var(--border);"
                  title="{{ $product['name'] }}{{ $product['revenue'] > 0 ? ' — ' . number_format($product['revenue']) . ' RWF (30d)' : '' }}">
                <div style="display:flex;align-items:center;gap:8px">
                  <span style="display:inline-flex;align-items:center;justify-content:center;
                               flex-shrink:0;width:20px;height:20px;border-radius:6px;
                               font-size:9px;font-weight:800;
                               background:{{ $rank <= 3 ? 'var(--accent-glow)' : 'var(--surface2)' }};
                               color:{{ $rank <= 3 ? 'var(--accent)' : 'var(--text-dim)' }};
                               border:1px solid {{ $rank <= 3 ? 'rgba(59,111,212,.2)' : 'var(--border)' }}">
                    {{ $rank }}
                  </span>
                  <span class="shm-pname" style="overflow:hidden;text-overflow:ellipsis;display:block">
                    {{ \Illuminate\Support\Str::limit($product['name'], 28) }}
                  </span>
                </div>
                @if($product['revenue'] > 0)
                  <div class="shm-rev"
                       style="font-size:9px;color:var(--text-dim);font-weight:500;
                              padding-left:28px;margin-top:1px;white-space:nowrap">
                    {{ number_format($product['revenue']) }} RWF / 30d
                  </div>
                @endif
              </td>

              {{-- Data cells --}}
              @foreach($matrix[$pi] as $index => $cell)
                @php
                  $isWarehouse = $locations[$index]['type'] === 'warehouse';
                  $isFirstShop = !$isWarehouse && $index === $warehouseCols;
                  $isEmpty     = $cell['boxes'] === 0;

                  [$bg, $textColor, $borderColor] = match(true) {
                      $isEmpty                     => ['var(--surface2)', 'var(--text-dim)', 'var(--border)'],
                      $cell['level'] === 'crit'    => ['var(--red-dim)', 'var(--red)', 'var(--red-glow)'],
                      $cell['level'] === 'warn'    => ['var(--amber-dim)', 'var(--amber)', 'var(--amber-glow)'],
                      $cell['level'] === 'good'    => ['var(--green-dim)', 'var(--green)', 'var(--green-glow)'],
                      $cell['level'] === 'overstock' => ['rgba(56,189,248,.1)', '#0284c7', 'rgba(2,132,199,.22)'],
                      default                      => ['var(--surface2)', 'var(--text-dim)', 'var(--border)'],
                  };
                @endphp
                <td class="shm-cell"
                    style="padding:5px 4px;vertical-align:top;border-bottom:1px solid var(--border);
                           {{ $isFirstShop ? 'border-left:2px solid var(--border);' : '' }}">
                  <div class="shm-inner"
                       style="background:{{ $bg }};border:1px solid {{ $borderColor }};
                              border-radius:8px;padding:8px 6px;min-height:64px;
                              display:flex;flex-direction:column;align-items:center;
                              justify-content:center;gap:2px">

                    @if($isEmpty)
                      {{-- Empty cell --}}
                      <span style="font-family:var(--mono);font-size:18px;color:var(--text-dim);
                                   opacity:.35;line-height:1">—</span>
                      <span style="font-size:9px;color:var(--text-dim);opacity:.5;font-weight:500">
                        {{ $isWarehouse ? 'empty' : 'no stock' }}
                      </span>

                    @elseif($isWarehouse)
                      {{-- Warehouse: box count + items --}}
                      <span class="shm-num" style="font-family:var(--mono);font-size:20px;font-weight:800;
                                   color:{{ $textColor }};line-height:1">{{ $cell['boxes'] }}</span>
                      <span style="font-size:9px;font-weight:700;color:{{ $textColor }};opacity:.7;
                                   text-transform:uppercase;letter-spacing:.05em">boxes</span>
                      @if($cell['items'] > 0)
                        <span style="font-size:9px;color:var(--text-dim);font-weight:500;margin-top:1px">
                          {{ number_format($cell['items']) }} items
                        </span>
                      @endif

                    @else
                      {{-- Shop: days remaining (primary) + boxes (secondary) --}}
                      @php
                        $dispDays = match(true) {
                            $cell['days'] === 999  => '∞',
                            $cell['days'] > 99     => '>99',
                            default                => (string) $cell['days'],
                        };
                      @endphp
                      <div style="display:flex;align-items:baseline;gap:1px">
                        <span class="shm-num" style="font-family:var(--mono);font-size:20px;font-weight:800;
                                     color:{{ $textColor }};line-height:1">{{ $dispDays }}</span>
                        @if($cell['days'] !== 999)
                          <span style="font-size:10px;font-weight:700;color:{{ $textColor }};opacity:.8">d</span>
                        @endif
                      </div>
                      <span style="font-size:9px;color:var(--text-dim);font-weight:500;white-space:nowrap">
                        {{ $cell['boxes'] }}b
                        @if($cell['items'] > 0) &middot; {{ number_format($cell['items']) }}i @endif
                      </span>

                    @endif

                    {{-- Incoming transfer badge --}}
                    @if($cell['incoming'] > 0)
                      <div style="display:inline-flex;align-items:center;gap:2px;margin-top:3px;
                                  background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.2);
                                  border-radius:4px;padding:1px 6px">
                        <span style="font-size:9px;font-weight:700;color:#6366f1">+{{ $cell['incoming'] }}</span>
                        <span style="font-size:9px">🚚</span>
                      </div>
                    @endif

                  </div>
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>

      {{-- ── Legend ── --}}
      <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;
                  margin-top:16px;padding-top:14px;border-top:1px solid var(--border)">
        <span style="font-size:10px;font-weight:700;color:var(--text-dim);
                     text-transform:uppercase;letter-spacing:.08em;flex-shrink:0">Legend</span>

        @foreach([
          ['Out of stock',    'var(--surface2)',          'var(--border)',              'var(--text-dim)'],
          ['Critical (<3d)',  'var(--red-dim)',            'var(--red-glow)',             'var(--red)'    ],
          ['Low (3–7d)',      'var(--amber-dim)',          'var(--amber-glow)',           'var(--amber)'  ],
          ['Healthy (7–21d)', 'var(--green-dim)',          'var(--green-glow)',           'var(--green)'  ],
          ['Overstock (>21d)','rgba(56,189,248,.1)',       'rgba(2,132,199,.22)',         '#0284c7'       ],
        ] as [$label, $ibg, $iborder, $icolor])
          <div style="display:flex;align-items:center;gap:6px">
            <div style="width:20px;height:14px;border-radius:4px;
                        background:{{ $ibg }};border:1px solid {{ $iborder }};flex-shrink:0"></div>
            <span style="font-size:10px;color:{{ $icolor }};font-weight:600;white-space:nowrap">{{ $label }}</span>
          </div>
        @endforeach

        <div style="display:flex;align-items:center;gap:6px;margin-left:auto">
          <div style="display:inline-flex;align-items:center;gap:3px;
                      background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);
                      border-radius:4px;padding:2px 7px">
            <span style="font-size:9px;font-weight:700;color:#6366f1">+N</span>
            <span style="font-size:9px">🚚</span>
          </div>
          <span style="font-size:10px;color:var(--text-dim);font-weight:500">Incoming transfer</span>
        </div>

        <div style="font-size:10px;color:var(--text-dim);font-weight:500;margin-left:4px">
          Shops: <strong style="color:var(--text-sub)">d</strong> = days remaining &middot;
          <strong style="color:var(--text-sub)">b</strong> = boxes &middot;
          <strong style="color:var(--text-sub)">i</strong> = items
        </div>
      </div>
    </div>
  @endif

</div>
