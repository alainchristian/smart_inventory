<div class="card" style="animation:fadeUp .4s ease .55s both">

  <div class="card-header">
    <div>
      <div class="card-title">Stock Heat Map</div>
      <div class="card-subtitle">Boxes by product &times; location</div>
    </div>
  </div>

  @if(empty($products) || empty($locations))
    <div style="padding:40px 20px;text-align:center">
      <div style="font-size:13px;color:var(--text-dim)">No stock data available</div>
    </div>
  @else
    <div style="overflow-x:auto;padding:0 16px 16px">
      <table style="width:100%;border-collapse:separate;border-spacing:3px;font-size:12px">
        <thead>
          <tr>
            <th style="text-align:left;padding:6px 8px;font-size:11px;color:var(--text-dim);font-weight:600;min-width:120px">Product</th>
            @foreach($locations as $loc)
              <th style="text-align:center;padding:6px 4px;font-size:10px;color:var(--text-dim);font-weight:600;white-space:nowrap;max-width:80px;overflow:hidden;text-overflow:ellipsis"
                  title="{{ $loc['name'] }}">
                {{ \Illuminate\Support\Str::limit($loc['name'], 12) }}
              </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($products as $pi => $product)
            <tr>
              <td style="padding:6px 8px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px"
                  title="{{ $product['name'] }}">
                {{ \Illuminate\Support\Str::limit($product['name'], 20) }}
              </td>
              @foreach($matrix[$pi] as $li => $cell)
                @php
                  $bg = match($cell['level']) {
                      'good' => 'var(--green-dim)',
                      'warn' => 'var(--amber-dim)',
                      'crit' => $cell['boxes'] === 0 ? 'var(--surface2)' : 'var(--red-dim)',
                  };
                  $color = match($cell['level']) {
                      'good' => 'var(--green)',
                      'warn' => 'var(--amber)',
                      'crit' => $cell['boxes'] === 0 ? 'var(--text-dim)' : 'var(--red)',
                  };
                @endphp
                <td style="text-align:center;padding:5px 4px;background:{{ $bg }};border-radius:4px;font-family:var(--mono);font-weight:700;font-size:11px;color:{{ $color }};min-width:48px"
                    title="{{ $product['name'] }} @ {{ $locations[$li]['name'] }}: {{ $cell['boxes'] }} boxes">
                  {{ $cell['boxes'] > 0 ? $cell['boxes'] : '—' }}
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>

      {{-- Legend --}}
      <div style="display:flex;align-items:center;gap:16px;margin-top:12px;padding-top:10px;border-top:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:5px">
          <div style="width:12px;height:12px;border-radius:3px;background:var(--green-dim);border:1px solid var(--green)"></div>
          <span style="font-size:10px;color:var(--text-dim)">Healthy (&ge;70%)</span>
        </div>
        <div style="display:flex;align-items:center;gap:5px">
          <div style="width:12px;height:12px;border-radius:3px;background:var(--amber-dim);border:1px solid var(--amber)"></div>
          <span style="font-size:10px;color:var(--text-dim)">Low (30–69%)</span>
        </div>
        <div style="display:flex;align-items:center;gap:5px">
          <div style="width:12px;height:12px;border-radius:3px;background:var(--red-dim);border:1px solid var(--red)"></div>
          <span style="font-size:10px;color:var(--text-dim)">Critical (&lt;30%)</span>
        </div>
        <div style="display:flex;align-items:center;gap:5px">
          <div style="width:12px;height:12px;border-radius:3px;background:var(--surface2)"></div>
          <span style="font-size:10px;color:var(--text-dim)">No stock</span>
        </div>
      </div>
    </div>
  @endif

</div>
