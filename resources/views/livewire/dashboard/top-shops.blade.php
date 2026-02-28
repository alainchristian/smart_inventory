<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Top Performing Shops</div>
      <div class="card-subtitle">Ranked by sales volume</div>
    </div>
    <a href="{{ route('owner.shops.index') }}" class="card-btn">View All</a>
  </div>

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
    <span class="shop-ok-badge" style="font-size:10.5px;font-weight:600;padding:2px 7px;border-radius:10px;
                 background:var(--success-glow);color:var(--success)">OK</span>
  </div>
  @endforeach

  {{-- Stock fill per shop --}}
  <div class="stock-fill-section" style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
    <div class="card-subtitle" style="margin-bottom:10px">Stock fill per shop</div>
    @foreach($shops as $shop)
    <div class="stock-fill-row" style="display:flex;align-items:center;gap:10px;font-size:12px;margin-bottom:7px">
      <span class="stock-shop-name" style="width:130px;color:var(--text-sub);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
        {{ $shop['name'] }}
      </span>
      <div style="flex:1;height:4px;background:var(--surface3);border-radius:10px;overflow:hidden">
        <div style="height:100%;width:{{ $shop['fill_pct'] }}%;border-radius:10px;
                    background:{{ $shop['fill_pct'] >= 50 ? 'var(--success)' :
                                 ($shop['fill_pct'] >= 20 ? 'var(--amber)' : 'var(--red)') }}">
        </div>
      </div>
      <span class="stock-fill-pct" style="font-family:var(--mono);width:32px;text-align:right;font-weight:600;
                   color:{{ $shop['fill_pct'] >= 50 ? 'var(--success)' :
                           ($shop['fill_pct'] >= 20 ? 'var(--amber)' : 'var(--red)') }}">
        {{ $shop['fill_pct'] }}%
      </span>
      @if($shop['fill_pct'] < 20)
        <span class="stock-warning" style="color:var(--red);font-size:14px" title="Critical stock level">⚠</span>
      @endif
    </div>
    @endforeach
  </div>
</div>
