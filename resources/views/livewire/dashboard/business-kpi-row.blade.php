@php
if (!function_exists('kpiSparkPts')) {
    function kpiSparkPts(array $data, int $w = 80, int $h = 32): string {
        $data = array_values($data ?: [0]);
        $max  = max(array_merge([1], $data));
        $min  = min($data);
        $rng  = max($max - $min, 1);
        $n    = count($data);
        $pts  = [];
        foreach ($data as $i => $v) {
            $x      = $n > 1 ? round($i / ($n - 1) * $w, 1) : $w / 2;
            $y      = round($h - (($v - $min) / $rng) * ($h - 4) - 2, 1);
            $pts[]  = $x . ',' . $y;
        }
        return implode(' ', $pts);
    }
}

$spSales   = !empty($salesSparkline)   ? $salesSparkline   : array_fill(0, 7, 0);
$spProfit  = !empty($profitSparkline)  ? $profitSparkline  : array_fill(0, 7, 0);
$spExp     = !empty($expenseSparkline) ? $expenseSparkline : array_fill(0, 7, 0);
$spCredit  = !empty($creditSparkline)  ? $creditSparkline  : array_fill(0, 7, 0);
// Net profit sparkline = gross profit − expenses (mirrors the KPI card calculation)
$minLen = min(count($spProfit), count($spExp));
$spNet  = array_map(fn($p,$e) => $p - $e, array_slice($spProfit,0,$minLen), array_slice($spExp,0,$minLen));

$salesGrowth = $sales['growth']    ?? 0;
$expGrowth   = $expenses['growth'] ?? 0;
$netOp       = $expenses['net_op'] ?? 0;
$netPct      = ($sales['current'] ?? 0) > 0 ? round($netOp / $sales['current'] * 100, 1) : 0;

$periodLabel = match($period ?? 'today') {
    'today'   => 'vs yesterday',
    'week'    => 'vs last 7 days',
    'month'   => 'vs last month',
    'quarter' => 'vs last quarter',
    'year'    => 'vs last year',
    default   => 'vs previous period',
};
@endphp

<div class="kpi5-grid">

  {{-- ① Total Revenue --}}
  <div class="kpi5-card">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
      <div class="kpi5-icon" style="background:rgba(59,111,212,.12)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b6fd4" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8M12 11v5M8 14v2M4 20h16a2 2 0 002-2V6a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <span class="kpi5-label">Total Revenue</span>
    </div>
    <div style="margin-bottom:14px">
      <span class="kpi5-value">{{ number_format($sales['current'] ?? 0) }}</span>
      <span class="kpi5-unit"> RWF</span>
    </div>
    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:8px">
      <div>
        <span class="kpi5-change {{ $salesGrowth >= 0 ? 'up' : 'down' }}">
          {{ $salesGrowth >= 0 ? '↑' : '↓' }} {{ abs($salesGrowth) }}%
        </span>
        <div class="kpi5-period">{{ $periodLabel }}</div>
      </div>
      <svg viewBox="0 0 80 32" width="80" height="32" style="flex-shrink:0;overflow:visible;margin-bottom:2px">
        <polyline fill="none" stroke="#3b6fd4" stroke-width="1.8"
                  stroke-linecap="round" stroke-linejoin="round"
                  points="{{ kpiSparkPts($spSales) }}"/>
      </svg>
    </div>
  </div>

  {{-- ② Gross Profit --}}
  <div class="kpi5-card">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
      <div class="kpi5-icon" style="background:rgba(14,158,134,.12)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0e9e86" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
      </div>
      <span class="kpi5-label">Gross Profit</span>
    </div>
    <div style="margin-bottom:14px">
      <span class="kpi5-value" style="color:#0e9e86">{{ number_format($profit['margin_rwf'] ?? 0) }}</span>
      <span class="kpi5-unit"> RWF</span>
    </div>
    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:8px">
      <div>
        <span class="kpi5-change up">↑ {{ $profit['margin_pct'] ?? 0 }}%</span>
        <div class="kpi5-period">margin</div>
      </div>
      <svg viewBox="0 0 80 32" width="80" height="32" style="flex-shrink:0;overflow:visible;margin-bottom:2px">
        <polyline fill="none" stroke="#0e9e86" stroke-width="1.8"
                  stroke-linecap="round" stroke-linejoin="round"
                  points="{{ kpiSparkPts($spProfit) }}"/>
      </svg>
    </div>
  </div>

  {{-- ③ Total Expenses --}}
  <div class="kpi5-card">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
      <div class="kpi5-icon" style="background:rgba(249,115,22,.12)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <span class="kpi5-label">Total Expenses</span>
    </div>
    <div style="margin-bottom:14px">
      <span class="kpi5-value" style="color:#f97316">{{ number_format($expenses['current'] ?? 0) }}</span>
      <span class="kpi5-unit"> RWF</span>
    </div>
    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:8px">
      <div>
        <span class="kpi5-change {{ $expGrowth <= 0 ? 'up' : 'down' }}">
          {{ $expGrowth <= 0 ? '↓' : '↑' }} {{ abs($expGrowth) }}%
        </span>
        <div class="kpi5-period">{{ $periodLabel }}</div>
      </div>
      <svg viewBox="0 0 80 32" width="80" height="32" style="flex-shrink:0;overflow:visible;margin-bottom:2px">
        <polyline fill="none" stroke="#f97316" stroke-width="1.8"
                  stroke-linecap="round" stroke-linejoin="round"
                  points="{{ kpiSparkPts($spExp) }}"/>
      </svg>
    </div>
  </div>

  {{-- ④ Net Profit --}}
  @php $netColor = $netOp >= 0 ? '#8b5cf6' : '#e11d48'; $netBg = $netOp >= 0 ? 'rgba(139,92,246,.12)' : 'rgba(225,29,72,.12)'; @endphp
  <div class="kpi5-card">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
      <div class="kpi5-icon" style="background:{{ $netBg }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $netColor }}" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
      </div>
      <span class="kpi5-label">Net Profit</span>
    </div>
    <div style="margin-bottom:14px">
      <span class="kpi5-value" style="color:{{ $netColor }}">{{ number_format($netOp) }}</span>
      <span class="kpi5-unit"> RWF</span>
    </div>
    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:8px">
      <div>
        <span class="kpi5-change {{ $netPct >= 0 ? 'up' : 'down' }}">
          {{ $netPct >= 0 ? '↑' : '↓' }} {{ abs($netPct) }}%
        </span>
        <div class="kpi5-period">{{ $periodLabel }}</div>
      </div>
      <svg viewBox="0 0 80 32" width="80" height="32" style="flex-shrink:0;overflow:visible;margin-bottom:2px">
        <polyline fill="none" stroke="{{ $netColor }}" stroke-width="1.8"
                  stroke-linecap="round" stroke-linejoin="round"
                  points="{{ kpiSparkPts($spNet) }}"/>
      </svg>
    </div>
  </div>

  {{-- ⑤ Receivables --}}
  <div class="kpi5-card">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
      <div class="kpi5-icon" style="background:rgba(59,111,212,.12)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b6fd4" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
      </div>
      <span class="kpi5-label">Receivables</span>
    </div>
    <div style="margin-bottom:14px">
      <span class="kpi5-value" style="color:#8b5cf6">{{ number_format($credit['outstanding'] ?? 0) }}</span>
      <span class="kpi5-unit"> RWF</span>
    </div>
    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:8px">
      <div>
        @if(($credit['outstanding'] ?? 0) > 0)
          <div style="display:flex;align-items:center;gap:4px">
            <span style="font-size:13px">⚠</span>
            <span style="font-size:12px;font-weight:700;color:#f97316">Pending</span>
          </div>
          <div class="kpi5-period">{{ $credit['count'] ?? 0 }} customers</div>
        @else
          <span style="font-size:12px;font-weight:700;color:#0e9e86">✓ All clear</span>
          <div class="kpi5-period">no outstanding</div>
        @endif
      </div>
      <svg viewBox="0 0 80 32" width="80" height="32" style="flex-shrink:0;overflow:visible;margin-bottom:2px">
        <polyline fill="none" stroke="#8b5cf6" stroke-width="1.8"
                  stroke-linecap="round" stroke-linejoin="round"
                  points="{{ kpiSparkPts($spCredit) }}"/>
      </svg>
    </div>
  </div>

</div>
