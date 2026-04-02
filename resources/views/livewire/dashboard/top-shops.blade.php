<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Top Performing Shops</div>
      <div class="card-subtitle">Ranked by sales volume</div>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
      <div class="period-tabs">
        <button class="period-tab {{ $period === 'week' ? 'active' : '' }}"
                wire:click="setPeriod('week')">W</button>
        <button class="period-tab {{ $period === 'month' ? 'active' : '' }}"
                wire:click="setPeriod('month')">M</button>
        <button class="period-tab {{ $period === 'quarter' ? 'active' : '' }}"
                wire:click="setPeriod('quarter')">Q</button>
      </div>
      <a href="{{ route('owner.shops.index') }}" class="card-btn">View All</a>
    </div>
  </div>

  <div class="card-scroll">
  @foreach($shops as $shop)
  <div class="shop-row" style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border)">
    <div class="shop-rank {{ $shop['rank_css'] }}">{{ $shop['rank'] }}</div>
    <div style="flex:1;min-width:0">
      <div class="shop-name" style="font-weight:600;font-size:13px;color:var(--text)">{{ $shop['name'] }}</div>
      <div style="display:flex;align-items:center;gap:8px;margin-top:5px">
        <div style="flex:1;height:4px;background:var(--surface3);border-radius:10px;overflow:hidden">
          <div class="shop-bar-fill {{ $shop['rank_css'] }}"
               style="height:100%;width:{{ round($shop['revenue'] / $maxRevenue * 100) }}%;border-radius:10px">
          </div>
        </div>
        <span class="shop-revenue" style="font-size:11px;font-family:var(--mono);color:var(--text-sub)">
          {{ number_format($shop['revenue']) }}
        </span>
      </div>
    </div>
    @if($shop['revenue'] > 0)
      <span class="shop-ok-badge" style="font-size:10.5px;font-weight:600;padding:2px 7px;border-radius:10px;
                   background:var(--success-glow);color:var(--success)">OK</span>
    @else
      <span class="shop-ok-badge" style="font-size:10.5px;font-weight:600;padding:2px 7px;border-radius:10px;
                   background:var(--amber-dim);color:var(--amber)">Low</span>
    @endif
  </div>
  @endforeach

  {{-- Stock fill per shop (box count + low stock signal) --}}
  <div class="stock-fill-section" style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
    <div class="card-subtitle" style="margin-bottom:10px">Stock health per shop</div>
    @foreach($shops as $shop)
    @php
      $low = $shop['low_stock_products'];
      $stockColor = $low === 0 ? 'var(--success)' : ($low <= 2 ? 'var(--amber)' : 'var(--red)');
      $stockBg    = $low === 0 ? 'var(--success-glow)' : ($low <= 2 ? 'var(--amber-dim)' : 'var(--red-dim)');
    @endphp
    <div class="stock-fill-row" style="display:flex;align-items:center;gap:10px;font-size:12px;margin-bottom:8px">
      <span class="stock-shop-name" style="width:120px;color:var(--text-sub);overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
            title="{{ $shop['name'] }}">
        {{ $shop['name'] }}
      </span>
      <div style="flex:1;min-width:0">
        @if($low === 0)
          <span style="font-size:11px;font-weight:600;color:var(--success)">
            {{ $shop['total_boxes'] }} {{ $shop['total_boxes'] === 1 ? 'box' : 'boxes' }} &middot; All stocked
          </span>
        @else
          <span style="font-size:11px;font-weight:600;color:{{ $stockColor }}">
            {{ $shop['total_boxes'] }} {{ $shop['total_boxes'] === 1 ? 'box' : 'boxes' }}
            &middot; {{ $low }} {{ $low === 1 ? 'product needs' : 'products need' }} restocking
          </span>
        @endif
      </div>
      <span style="font-size:10.5px;font-weight:700;padding:2px 6px;border-radius:8px;
                   background:{{ $stockBg }};color:{{ $stockColor }};flex-shrink:0">
        {{ $low === 0 ? 'OK' : $low }}
      </span>
    </div>
    @endforeach
  </div>
  </div>
</div>
