{{-- Breakdown panel partial — renders inline below a KPI card --}}
{{-- Variables: $bd (breakdown array), $metricId (string), $blockData (original block data array) --}}
@php
    $type = $bd['type'] ?? 'none';
    $fmt  = fn($v) => number_format((float)$v);
    $pct  = fn($v, $total) => $total > 0 ? round(($v / $total) * 100) : 0;
    $pill = function(int $days): string {
        if ($days <= 0)  return 'crit';
        if ($days <= 7)  return 'crit';
        if ($days <= 21) return 'warn';
        return 'ok';
    };
@endphp

@if ($type === 'error')
<div style="font-size:12px;color:var(--red)">Could not load breakdown.</div>

@elseif ($type === 'sales_summary')
@php
    $shops    = $bd['shops']    ?? [];
    $products = $bd['products'] ?? [];
    $methods  = $bd['methods']  ?? [];
    $shopTotal  = array_sum(array_column($shops, 'revenue'));
    $prodTotal  = array_sum(array_column($products, 'revenue'));
    $methTotal  = array_sum(array_column($methods, 'revenue'));
@endphp
@if (!empty($shops))
<div class="rv-bk-section">
    <div class="rv-bk-label">By Shop</div>
    @foreach ($shops as $row)
    @php $row = is_array($row) ? $row : (array)$row; $rev = $row['revenue'] ?? 0; $p = $pct($rev, $shopTotal); @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $row['shop_name'] ?? '—' }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%"></div></div>
        <span class="rv-bk-val">{{ $fmt($rev) }}</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@endif
@if (!empty($products))
<div class="rv-bk-section">
    <div class="rv-bk-label">Top Products</div>
    @foreach ($products as $row)
    @php $row = is_array($row) ? $row : (array)$row; $rev = $row['revenue'] ?? 0; $p = $pct($rev, $prodTotal); @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $row['product_name'] ?? '—' }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%"></div></div>
        <span class="rv-bk-val">{{ $fmt($rev) }}</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@endif
@if (!empty($methods))
<div class="rv-bk-section">
    <div class="rv-bk-label">Payment Methods</div>
    @foreach ($methods as $row)
    @php $row = is_array($row) ? $row : (array)$row; $rev = $row['revenue'] ?? 0; $p = $pct($rev, $methTotal); @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ ucfirst(str_replace('_',' ',$row['label'] ?? $row['method'] ?? '—')) }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%;background:var(--violet)"></div></div>
        <span class="rv-bk-val">{{ $fmt($rev) }}</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@endif

@elseif ($type === 'sales_margin')
@php
    $products = $bd['products'] ?? [];
    $types    = $bd['types']    ?? [];
    $prodTotal = array_sum(array_column($products, 'revenue'));
    $typeTotal = array_sum(array_column($types, 'revenue'));
@endphp
@if (!empty($products))
<div class="rv-bk-section">
    <div class="rv-bk-label">Top Products by Revenue</div>
    @foreach ($products as $row)
    @php $row = is_array($row) ? $row : (array)$row; $rev = $row['revenue'] ?? 0; $p = $pct($rev, $prodTotal); @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $row['product_name'] ?? '—' }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%"></div></div>
        <span class="rv-bk-val">{{ $fmt($rev) }}</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@endif
@if (!empty($types))
<div class="rv-bk-section">
    <div class="rv-bk-label">Sale Type Mix</div>
    @foreach ($types as $row)
    @php $row = is_array($row) ? $row : (array)$row; $rev = $row['revenue'] ?? 0; $p = $pct($rev, $typeTotal); @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ ucfirst(str_replace('_',' ',$row['sale_type'] ?? $row['type'] ?? '—')) }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%;background:var(--green)"></div></div>
        <span class="rv-bk-val">{{ $fmt($rev) }}</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@endif

@elseif ($type === 'inventory_categories')
@php
    $cats  = $bd['categories'] ?? [];
    $locs  = $bd['locations']  ?? [];
    $catTotal = array_sum(array_column($cats, 'cost_value'));
    $locTotal = array_sum(array_map(fn($r) => is_array($r) ? ($r['cost_value'] ?? 0) : (is_object($r) ? $r->cost_value ?? 0 : 0), $locs));
@endphp
@if (!empty($cats))
<div class="rv-bk-section">
    <div class="rv-bk-label">By Category (Cost Value)</div>
    @foreach ($cats as $row)
    @php $row = is_array($row) ? $row : (array)$row; $v = $row['cost_value'] ?? 0; $p = $pct($v, $catTotal); @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $row['category_name'] ?? '—' }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%;background:var(--green)"></div></div>
        <span class="rv-bk-val">{{ $fmt($v) }}</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@endif
@if (!empty($locs))
<div class="rv-bk-section">
    <div class="rv-bk-label">By Location</div>
    @foreach (array_slice($locs, 0, 5) as $row)
    @php $row = is_array($row) ? $row : (array)$row; $v = $row['cost_value'] ?? 0; $p = $pct($v, $locTotal); @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $row['location_name'] ?? ($row['name'] ?? '—') }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%;background:var(--violet)"></div></div>
        <span class="rv-bk-val">{{ $fmt($v) }}</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@endif

@elseif ($type === 'stock_health')
@php $items = $bd['items'] ?? []; @endphp
@if (!empty($items))
<div class="rv-bk-section">
    <div class="rv-bk-label">Products — Days of Stock Remaining</div>
    @foreach ($items as $row)
    @php
        $row  = is_array($row) ? $row : (array)$row;
        $days = (int)($row['days_on_hand'] ?? 0);
        $cls  = $pill($days);
    @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $row['product_name'] ?? '—' }}</span>
        <span class="rv-bk-pill {{ $cls }}">{{ $days > 0 ? $days . 'd' : 'Out' }}</span>
        <span class="rv-bk-val" style="color:var(--text-dim);font-size:11px">{{ number_format($row['boxes_available'] ?? 0) }} boxes</span>
    </div>
    @endforeach
</div>
@else
<div style="font-size:12.5px;color:var(--green);font-weight:600">All products have healthy stock levels.</div>
@endif

@elseif ($type === 'loss_detail')
@php
    $products = $bd['products'] ?? [];
    $reasons  = $bd['reasons']  ?? [];
    $lossTotal = array_sum(array_map(fn($r) => is_array($r) ? (($r['return_value'] ?? 0) + ($r['damaged_value'] ?? 0)) : 0, $products));
    $reasonTotal = array_sum(array_column($reasons, 'count'));
@endphp
@if (!empty($products))
<div class="rv-bk-section">
    <div class="rv-bk-label">Top Loss-Generating Products</div>
    @foreach ($products as $row)
    @php
        $row = is_array($row) ? $row : (array)$row;
        $v   = ($row['return_value'] ?? 0) + ($row['damaged_value'] ?? 0);
        $p   = $lossTotal > 0 ? round(($v / $lossTotal) * 100) : 0;
    @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $row['product_name'] ?? '—' }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%;background:var(--red)"></div></div>
        <span class="rv-bk-val">{{ $fmt($v) }}</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@endif
@if (!empty($reasons))
<div class="rv-bk-section">
    <div class="rv-bk-label">Return Reasons</div>
    @foreach ($reasons as $row)
    @php $row = is_array($row) ? $row : (array)$row; $n = $row['count'] ?? 0; $p = $reasonTotal > 0 ? round(($n / $reasonTotal) * 100) : 0; @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $row['reason'] ?? 'Unknown' }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%;background:var(--amber)"></div></div>
        <span class="rv-bk-val">{{ number_format($n) }}×</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@endif

@elseif ($type === 'critical_stock')
@php $items = $bd['items'] ?? []; @endphp
@if (!empty($items))
<div class="rv-bk-section">
    <div class="rv-bk-label">Critical Products</div>
    @foreach ($items as $row)
    @php $row = is_array($row) ? $row : (array)$row; $days = (int)($row['days_on_hand'] ?? 0); @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $row['product_name'] ?? '—' }}</span>
        <span class="rv-bk-pill crit">{{ $days > 0 ? $days . 'd left' : 'Out of stock' }}</span>
        <span class="rv-bk-val" style="color:var(--text-dim);font-size:11px">{{ number_format($row['boxes_available'] ?? 0) }} boxes</span>
    </div>
    @endforeach
</div>
@endif

@elseif ($type === 'finance_pl')
@php
    $net       = $bd['net']      ?? [];
    $expenses  = $bd['expenses'] ?? [];
    $revenue   = $net['total_revenue']   ?? $net['gross_revenue'] ?? 0;
    $cogs      = $net['cogs']            ?? 0;
    $grossProf = $net['gross_profit']    ?? 0;
    $expTotal  = $expenses['total_expenses'] ?? 0;
    $netResult = $net['net_result']      ?? 0;
    $cats      = $expenses['by_category'] ?? [];
    $maxLine   = max($revenue, 1);
@endphp
<div class="rv-bk-section">
    <div class="rv-bk-label">P&L Summary</div>
    @foreach ([
        ['Revenue',         $revenue,   'var(--accent)'],
        ['Cost of Goods',   -$cogs,     'var(--red)'],
        ['Gross Profit',    $grossProf, 'var(--green)'],
        ['Total Expenses',  -$expTotal, 'var(--amber)'],
        ['Net Result',      $netResult, $netResult >= 0 ? 'var(--green)' : 'var(--red)'],
    ] as [$label, $val, $color])
    <div class="rv-bk-row">
        <span class="rv-bk-name" style="{{ $label === 'Net Result' ? 'font-weight:700' : '' }}">{{ $label }}</span>
        <span class="rv-bk-val" style="color:{{ $color }}">{{ ($val < 0 ? '−' : '') }}{{ $fmt(abs($val)) }} RWF</span>
    </div>
    @endforeach
</div>
@if (!empty($cats))
<div class="rv-bk-section">
    <div class="rv-bk-label">Expenses by Category</div>
    @foreach (array_slice($cats, 0, 5) as $row)
    @php $row = is_array($row) ? $row : (array)$row; $v = $row['total'] ?? 0; $p = $row['pct_of_total'] ?? $pct($v, $expTotal); @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $row['category'] ?? '—' }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%;background:var(--amber)"></div></div>
        <span class="rv-bk-val">{{ $fmt($v) }}</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@endif

@elseif ($type === 'expense_categories')
@php
    $cats  = $bd['by_category'] ?? [];
    $total = (float)($bd['total'] ?? array_sum(array_column($cats, 'total')));
@endphp
@if (!empty($cats))
<div class="rv-bk-section">
    <div class="rv-bk-label">By Category</div>
    @foreach (array_slice($cats, 0, 6) as $row)
    @php $row = is_array($row) ? $row : (array)$row; $v = $row['total'] ?? 0; $p = $row['pct_of_total'] ?? $pct($v, $total); @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $row['category'] ?? '—' }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%;background:var(--amber)"></div></div>
        <span class="rv-bk-val">{{ $fmt($v) }}</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@else
<div style="font-size:12.5px;color:var(--text-dim)">No expense category data available.</div>
@endif

@elseif ($type === 'finance_cash')
@php
    $d        = $bd['existing'] ?? [];
    $shortage = (float)($d['total_shortage'] ?? 0);
    $surplus  = (float)($d['total_surplus']  ?? 0);
    $sessions = (int)($d['total_sessions'] ?? 0);
    $withShort= (int)($d['sessions_with_shortage'] ?? 0);
    $cashW    = (float)($d['cash_withdrawals'] ?? 0);
    $momoW    = (float)($d['momo_withdrawals'] ?? 0);
    $total    = (float)($d['total_withdrawals'] ?? ($shortage + $surplus));
@endphp
<div class="rv-bk-section">
    @if (isset($d['total_withdrawals']))
    <div class="rv-bk-label">Withdrawal Breakdown</div>
    @foreach ([['Cash', $cashW], ['Mobile Money', $momoW]] as [$label, $val])
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $label }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ $total > 0 ? min(round($val/$total*100),100) : 0 }}%;background:var(--violet)"></div></div>
        <span class="rv-bk-val">{{ $fmt($val) }} RWF</span>
        <span class="rv-bk-pct">{{ $total > 0 ? round($val/$total*100) : 0 }}%</span>
    </div>
    @endforeach
    @else
    <div class="rv-bk-label">Session Cash Summary</div>
    <div class="rv-bk-row">
        <span class="rv-bk-name">Sessions with shortage</span>
        <span class="rv-bk-val {{ $withShort > 0 ? 'rv-bk-pill crit' : 'rv-bk-pill ok' }}">{{ $withShort }} / {{ $sessions }}</span>
    </div>
    <div class="rv-bk-row">
        <span class="rv-bk-name">Total shortage</span>
        <span class="rv-bk-val" style="color:var(--red)">{{ $fmt($shortage) }} RWF</span>
    </div>
    @if ($surplus > 0)
    <div class="rv-bk-row">
        <span class="rv-bk-name">Total surplus</span>
        <span class="rv-bk-val" style="color:var(--green)">{{ $fmt($surplus) }} RWF</span>
    </div>
    @endif
    @endif
</div>

@elseif ($type === 'transfer_routes')
@php
    $routes = $bd['routes'] ?? [];
    $total  = array_sum(array_map(fn($r) => is_array($r) ? ($r['transfer_count'] ?? 0) : (is_object($r) ? $r->transfer_count ?? 0 : 0), $routes));
@endphp
@if (!empty($routes))
<div class="rv-bk-section">
    <div class="rv-bk-label">Transfer Routes</div>
    @foreach ($routes as $row)
    @php
        $row = is_array($row) ? $row : (array)$row;
        $n   = $row['transfer_count'] ?? 0;
        $p   = $total > 0 ? round(($n / $total) * 100) : 0;
        $from= $row['warehouse_name'] ?? '?';
        $to  = $row['shop_name']      ?? '?';
    @endphp
    <div class="rv-bk-row">
        <span class="rv-bk-name">{{ $from }} → {{ $to }}</span>
        <div class="rv-bk-bar-wrap"><div class="rv-bk-bar" style="width:{{ min($p,100) }}%;background:var(--violet)"></div></div>
        <span class="rv-bk-val">{{ number_format($n) }}</span>
        <span class="rv-bk-pct">{{ $p }}%</span>
    </div>
    @endforeach
</div>
@else
<div style="font-size:12.5px;color:var(--text-dim)">No route data available for this period.</div>
@endif

@endif
