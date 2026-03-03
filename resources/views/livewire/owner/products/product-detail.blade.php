<div>

@if($product)
  <style>
    /* Drawer Overlay */
    .pd-overlay {
      position: fixed; inset: 0; z-index: 999;
      display: flex; justify-content: flex-end; overflow: hidden;
    }
    .pd-scrim {
      position: absolute; inset: 0; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(2px);
    }
    .pd-drawer {
      position: relative; width: 680px; max-width: 92vw; height: 100vh;
      background: var(--surface); display: flex; flex-direction: column;
      box-shadow: -4px 0 24px rgba(0, 0, 0, 0.1);
      transform: translateX(100%); transition: transform 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    .pd-drawer.open { transform: translateX(0); }

    /* Typography & Hierarchy */
    .pd-header { 
      padding: 24px 32px 20px; border-bottom: 1px solid var(--border); 
      display: flex; justify-content: space-between; align-items: flex-start;
      background: var(--surface); z-index: 10; flex-shrink: 0;
    }
    .pd-title { 
      font-size: 20px; font-weight: 600; color: var(--text); 
      margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; 
      letter-spacing: -0.4px;
    }
    .pd-tags { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .pd-tag { 
      font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 4px; 
      text-transform: uppercase; letter-spacing: 0.5px; 
    }
    .pd-tag.category { background: var(--surface2); color: var(--text-sub); border: 1px solid var(--border); }
    .pd-tag.inactive { background: var(--red-dim); color: var(--red); }
    .pd-meta-text { font-size: 12px; font-family: var(--mono); color: var(--text-sub); }

    .pd-close-btn { 
      width: 32px; height: 32px; border: 1px solid var(--border); background: var(--surface); 
      border-radius: 6px; display: grid; place-items: center; color: var(--text-sub); 
      cursor: pointer; transition: all 0.15s; flex-shrink: 0;
    }
    .pd-close-btn:hover { background: var(--surface2); color: var(--text); }

    /* Pricing Strip - Data Dense */
    .pd-pricing-strip { 
      display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 16px; 
      padding: 16px 32px; background: var(--surface2); border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }
    .pd-price-item { display: flex; flex-direction: column; gap: 4px; }
    .pd-price-label { 
      font-size: 10px; font-weight: 600; color: var(--text-sub); 
      text-transform: uppercase; letter-spacing: 0.5px; 
    }
    .pd-price-value { font-size: 15px; font-weight: 600; color: var(--text); font-family: var(--mono); }
    .pd-price-value.highlight { color: var(--violet); }

    /* Body */
    .pd-body { flex: 1; overflow-y: auto; padding: 24px 32px; display: flex; flex-direction: column; gap: 36px; }
    
    /* Section Shared */
    .pd-section-header { margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
    .pd-section-title { 
      font-size: 13px; font-weight: 600; color: var(--text); 
      text-transform: uppercase; letter-spacing: 0.6px;
    }
    .pd-badge { font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: 600; }

    /* Tables */
    .pd-table-wrap { border: 1px solid var(--border); border-radius: 6px; overflow: hidden; background: var(--surface); }
    .pd-table { width: 100%; border-collapse: collapse; text-align: left; }
    .pd-table th { 
      background: var(--surface2); padding: 12px 16px; font-size: 11px; font-weight: 600; 
      color: var(--text-sub); text-transform: uppercase; border-bottom: 1px solid var(--border); 
      letter-spacing: 0.5px;
    }
    .pd-table td { 
      padding: 12px 16px; font-size: 13px; color: var(--text); 
      border-bottom: 1px solid var(--border); vertical-align: middle; 
    }
    .pd-table tbody tr:last-child td { border-bottom: none; }
    .pd-table tbody tr:hover { background: var(--surface2); }
    
    .pd-text-right { text-align: right; }
    .pd-center { text-align: center; }
    .pd-mono { font-family: var(--mono); }
    
    /* Progress Bars */
    .pd-loc-name { font-weight: 500; font-size: 13px; }
    .pd-progress-bg { height: 4px; background: var(--surface3); border-radius: 2px; overflow: hidden; margin-top: 8px; }
    .pd-progress-bar { height: 100%; background: var(--accent); border-radius: 2px; }
    .pd-progress-bar.green { background: var(--green); }

    /* Movement list */
    .pd-move-item { display: flex; align-items: center; gap: 16px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .pd-move-item:last-child { border-bottom: none; }
    .pd-move-type { 
      flex-shrink: 0; font-size: 10px; font-weight: 600; text-transform: uppercase; 
      width: 80px; text-align: left; letter-spacing: 0.5px;
    }
    .pd-move-type.transfer { color: var(--accent); }
    .pd-move-type.received { color: var(--green); }
    .pd-move-type.consumption { color: var(--pink); }
    .pd-move-type.return { color: var(--violet); }
    
    /* Empty & Success states */
    .pd-empty { 
      padding: 32px; text-align: center; color: var(--text-sub); font-size: 13px; 
      background: var(--surface2); border: 1px dashed var(--border); border-radius: 6px; 
    }
    .pd-success-banner { 
      padding: 14px 20px; background: var(--green-dim); color: var(--green); 
      font-size: 13px; font-weight: 500; border-left: 3px solid var(--green); 
      display: flex; align-items: center; gap: 10px; border-radius: 0 4px 4px 0;
    }
  </style>

  <div class="pd-overlay" x-data="productDetailDrawer()" x-init="initDrawer()">
    <div class="pd-scrim" wire:click="close" x-show="open" x-transition.opacity></div>

    <div class="pd-drawer" x-ref="drawer" :class="{ 'open': open }">
      {{-- Header --}}
      <div class="pd-header">
        <div style="min-width:0; flex:1; padding-right:16px;">
          <div class="pd-title" title="{{ $product->name }}">{{ $product->name }}</div>
          <div class="pd-tags">
            <span class="pd-meta-text">{{ $product->sku }}</span>
            @if($product->barcode)
              <span class="pd-meta-text">&middot; {{ $product->barcode }}</span>
            @endif
            <span class="pd-tag category">{{ $product->category->name ?? 'No category' }}</span>
            @if(!$product->is_active)
              <span class="pd-tag inactive">INACTIVE</span>
            @endif
          </div>
        </div>
        <button wire:click="close" class="pd-close-btn" aria-label="Close">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      {{-- Pricing strip (owner-only) --}}
      @canany(['viewPurchasePrice'])
      @php
        $marginPct = $product->selling_price > 0
          ? round(($product->selling_price - $product->purchase_price) / $product->selling_price * 100, 1)
          : 0;
      @endphp
      <div class="pd-pricing-strip">
        <div class="pd-price-item">
          <span class="pd-price-label">Purchase</span>
          <span class="pd-price-value">{{ number_format($product->purchase_price / 100) }} <span style="font-size:11px;color:var(--text-dim)">RWF</span></span>
        </div>
        <div class="pd-price-item">
          <span class="pd-price-label">Selling</span>
          <span class="pd-price-value">{{ number_format($product->selling_price / 100) }} <span style="font-size:11px;color:var(--text-dim)">RWF</span></span>
        </div>
        @if($product->box_selling_price)
        <div class="pd-price-item">
          <span class="pd-price-label">Box Price</span>
          <span class="pd-price-value">{{ number_format($product->box_selling_price / 100) }} <span style="font-size:11px;color:var(--text-dim)">RWF</span></span>
        </div>
        @endif
        <div class="pd-price-item">
          <span class="pd-price-label">Margin</span>
          <span class="pd-price-value highlight">{{ $marginPct }}%</span>
        </div>
        <div class="pd-price-item">
          <span class="pd-price-label">Items / Box</span>
          <span class="pd-price-value">{{ $product->items_per_box }}</span>
        </div>
        <div class="pd-price-item">
          <span class="pd-price-label">Low Stock</span>
          <span class="pd-price-value" style="{{ $product->total_items <= $product->low_stock_threshold ? 'color:var(--red)' : '' }}">{{ $product->low_stock_threshold }}</span>
        </div>
      </div>
      @endcanany

      {{-- Scrollable body --}}
      <div class="pd-body" id="pd-body">

        {{-- Section 1: 30-day revenue chart --}}
        <div>
          <div class="pd-section-header">
            <div class="pd-section-title">30-Day Revenue Trend</div>
          </div>
          <div wire:ignore style="height:180px; width:100%; position:relative;">
            <canvas id="pdChart-{{ $product->id }}" style="width:100%; height:180px; display:block;"></canvas>
          </div>
        </div>

        {{-- Section 2: Stock by location --}}
        <div>
          <div class="pd-section-header">
            <div class="pd-section-title">Stock by Location</div>
          </div>
          @if(count($stockByLoc) > 0)
            <div class="pd-table-wrap">
              <table class="pd-table">
                <thead>
                  <tr>
                    <th>Location</th>
                    <th class="pd-text-right">Boxes</th>
                    <th class="pd-text-right">Items</th>
                  </tr>
                </thead>
                <tbody>
                  @php $totalItems = array_sum(array_column($stockByLoc, 'items')); @endphp
                  @foreach($stockByLoc as $loc)
                  <tr>
                    <td>
                      <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span class="pd-loc-name">{{ $loc['name'] }}</span>
                        <span style="font-size:10px; font-weight:600; padding: 2px 6px; border-radius: 4px; 
                                     background: {{ $loc['type'] === 'warehouse' ? 'var(--accent-dim)' : 'var(--green-dim)' }}; 
                                     color: {{ $loc['type'] === 'warehouse' ? 'var(--accent)' : 'var(--green)' }}; text-transform:uppercase;">
                          {{ $loc['type'] }}
                        </span>
                      </div>
                      <div class="pd-progress-bg">
                        <div class="pd-progress-bar {{ $loc['type'] === 'warehouse' ? '' : 'green' }}" 
                             style="width: {{ $totalItems > 0 ? round($loc['items'] / $totalItems * 100) : 0 }}%"></div>
                      </div>
                    </td>
                    <td class="pd-text-right pd-mono" style="color:var(--text-sub);">
                      {{ $loc['boxes'] }}
                    </td>
                    <td class="pd-text-right pd-mono" style="font-weight:600; color:var(--text);">
                      {{ number_format($loc['items']) }}
                    </td>
                  </tr>
                  @endforeach
                  <tr style="background:var(--surface2);">
                    <td style="font-weight:600;">Total Inventory</td>
                    <td class="pd-text-right pd-mono" style="color:var(--text-sub); font-weight:600;">
                      {{ array_sum(array_column($stockByLoc, 'boxes')) }}
                    </td>
                    <td class="pd-text-right pd-mono" style="color:var(--accent); font-weight:700;">
                      {{ number_format(array_sum(array_column($stockByLoc, 'items'))) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          @else
            <div class="pd-empty">No stock currently available at any location.</div>
          @endif
        </div>

        {{-- Section 3: Price modification log --}}
        <div>
          <div class="pd-section-header">
            <div class="pd-section-title">Price Override Log</div>
            @if(count($overrideLog) > 0)
              <span class="pd-badge" style="background:var(--red-dim); color:var(--red);">{{ count($overrideLog) }}</span>
            @endif
          </div>
          @if(count($overrideLog) > 0)
            <div class="pd-table-wrap">
              <table class="pd-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Shop</th>
                    <th class="pd-text-right">Original</th>
                    <th class="pd-text-right">Actual</th>
                    <th class="pd-text-right">Diff</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($overrideLog as $entry)
                  <tr title="{{ $entry['sold_by'] }} | {{ $entry['reason'] }} | Ref: {{ $entry['reference'] }}">
                    <td style="font-size:12px; color:var(--text-sub);">{{ $entry['date'] }}</td>
                    <td style="font-weight:500;">{{ $entry['shop'] }}</td>
                    <td class="pd-text-right pd-mono" style="color:var(--text-sub);">{{ $entry['original'] }}</td>
                    <td class="pd-text-right pd-mono" style="color:var(--red); font-weight:600;">{{ $entry['actual'] }}</td>
                    <td class="pd-text-right">
                      <span class="pd-tag" style="background:var(--red-dim); color:var(--red); padding: 3px 6px;">-{{ $entry['diff_pct'] }}%</span>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="pd-success-banner">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
              No unapproved price modifications recorded for this product.
            </div>
          @endif
        </div>

        {{-- Section 4: Recent box movements --}}
        <div>
          <div class="pd-section-header">
            <div class="pd-section-title">Recent Box Movements</div>
          </div>
          @if(count($recentMoves) > 0)
            <div style="border:1px solid var(--border); border-radius:6px; padding: 0 16px; background:var(--surface);">
              @foreach($recentMoves as $move)
              <div class="pd-move-item">
                <span class="pd-move-type {{ $move['type'] }}">{{ $move['type'] }}</span>
                <span class="pd-mono" style="font-size:12px; color:var(--text); font-weight:500;">
                  {{ $move['box_code'] }}
                </span>
                <span style="font-size:12.5px; color:var(--text-sub); flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                  {{ $move['from'] }} &rarr; {{ $move['to'] }}
                </span>
                @if($move['items'])
                  <span class="pd-mono" style="font-size:12px; color:var(--text-sub);">
                    {{ $move['items'] }}
                  </span>
                @endif
                <span style="font-size:11.5px; color:var(--text-dim); text-align:right; min-width:80px;">
                  {{ $move['date'] }}
                </span>
              </div>
              @endforeach
            </div>
          @else
            <div class="pd-empty">No recent box movements recorded.</div>
          @endif
        </div>

      </div>{{-- /body --}}
    </div>{{-- /drawer --}}
  </div>{{-- /overlay --}}

  {{-- Use @script so Livewire 3 executes this reliably after DOM update --}}
  @script
  <script>
    Alpine.data('productDetailDrawer', () => ({
      open: false,

      initDrawer() {
        const drawer = this.$refs.drawer;
        if (drawer) drawer.scrollTop = 0;

        requestAnimationFrame(() => {
          this.open = true;
          this.$nextTick(() => {
            initProductChart();
          });
        });
      }
    }));

    function initProductChart() {
      const canvas = document.getElementById('pdChart-{{ $product->id }}');
      if (!canvas) return;

      const existing = Chart.getChart(canvas);
      if (existing) existing.destroy();
      if (canvas._chartInstance) {
        canvas._chartInstance.destroy();
        delete canvas._chartInstance;
      }

      const chartData = @json($chartData);
      if (!chartData) return;

      canvas._chartInstance = new Chart(canvas, {
        type: 'bar',
        data: {
          labels: chartData.labels,
          datasets: [
            {
              label: 'Revenue (RWF)',
              data: chartData.revenue,
              backgroundColor: 'rgba(59,111,212,.15)',
              borderColor: 'rgba(59,111,212,.65)',
              borderWidth: 1.5,
              borderRadius: 3,
              yAxisID: 'y',
            },
            {
              label: 'Units',
              data: chartData.units,
              type: 'line',
              borderColor: 'rgba(14,158,134,.75)',
              backgroundColor: 'transparent',
              borderWidth: 2,
              pointRadius: 2,
              tension: 0.3,
              yAxisID: 'y1',
            }
          ]
        },
        options: {
          animation: false,
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
          scales: {
            x: {
              ticks: { color: '#a8aec8', font: { size: 10 }, maxTicksLimit: 10 },
              grid:  { color: '#e2e6f3', drawBorder: false },
            },
            y: {
              position: 'left',
              ticks: { color: '#a8aec8', font: { size: 10 },
                       callback: v => v >= 1000 ? (v/1000).toFixed(0)+'K' : v },
              grid:  { color: '#e2e6f3', drawBorder: false },
            },
            y1: {
              position: 'right',
              ticks: { color: 'rgba(14,158,134,.7)', font: { size: 10 } },
              grid:  { drawOnChartArea: false },
            }
          }
        }
      });
    }
  </script>
  @endscript
@endif
</div>