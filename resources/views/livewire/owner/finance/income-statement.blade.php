<div style="font-family:var(--font)">
<style>
.is-page        { padding:0 0 80px; }
.is-header      { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap; }
.is-header-title { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px; }
.is-header-sub   { font-size:13px;color:var(--text-dim);margin:0; }

/* ── Filter bar ── */
.is-filters     { background:var(--surface);border:none;border-radius:var(--r);
                  box-shadow:var(--shadow-card);margin-bottom:20px;
                  min-width:0;max-width:100%; }
.is-presets     { display:flex;gap:4px;overflow-x:auto;-webkit-overflow-scrolling:touch;
                  padding:10px 14px;border-bottom:1px solid var(--border);
                  scrollbar-width:none;flex-wrap:nowrap;min-width:0; }
.is-presets::-webkit-scrollbar { display:none; }
.is-preset-btn  { padding:5px 11px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;
                  border:1px solid transparent;background:transparent;color:var(--text-dim);
                  transition:all var(--tr);white-space:nowrap;flex-shrink:0;font-family:var(--font); }
.is-preset-btn:hover  { background:var(--surface2);color:var(--text);border-color:var(--border); }
.is-preset-btn.active { background:var(--accent);color:#fff;border-color:var(--accent);
                        box-shadow:0 2px 8px rgba(0,0,0,.12); }
.is-filter-row  { display:flex;gap:0;align-items:center; }
.is-filter-seg  { display:flex;align-items:center;gap:6px;padding:8px 14px;
                  border-right:1px solid var(--border);flex-shrink:0; }
.is-filter-seg-grow { flex:1;min-width:0; }
.is-filter-seg:last-child { border-right:none; }
.is-date-input  { padding:0;border:none;background:transparent;color:var(--text);
                  font-size:13px;font-weight:600;font-family:var(--font);cursor:pointer;
                  width:110px;outline:none; }
.is-date-input::-webkit-calendar-picker-indicator { opacity:0.4;cursor:pointer;margin-left:2px; }
.is-date-input:focus { color:var(--accent); }
.is-shop-select { padding:0;border:none;background:transparent;color:var(--text);
                  font-size:13px;font-weight:600;font-family:var(--font);cursor:pointer;
                  outline:none;flex:1;min-width:0; }
.is-shop-select:focus { color:var(--accent); }
.is-seg-label   { font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;
                  color:var(--text-dim);white-space:nowrap; }

/* ── Statement card ── */
.is-card        { border-radius:var(--r);border:none;background:var(--surface);
                  box-shadow:var(--shadow-card); }
.is-card-head   { padding:16px 20px;border-bottom:1px solid var(--border);
                  display:flex;align-items:center;justify-content:space-between;gap:12px; }
.is-card-head-label { font-size:11px;font-weight:700;text-transform:uppercase;
                      letter-spacing:0.6px;color:var(--text);margin-bottom:2px; }
.is-card-head-sub   { font-size:12px;color:var(--text-dim); }

/* ── Table ── */
.is-table       { width:100%;border-collapse:collapse; }
.is-table td    { padding:9px 20px;vertical-align:middle; }
.is-section-hd  { padding:10px 20px 6px;font-size:10px;font-weight:700;text-transform:uppercase;
                  letter-spacing:0.7px;color:var(--accent);
                  border-top:1px solid var(--border); }
.is-row-label   { font-size:13px;color:var(--text-sub); }
.is-row-indent  { padding-left:36px !important; }
.is-row-body    { border-bottom:1px solid var(--border); }
.is-row-body:hover { background:var(--surface2); }
.is-row-total   { font-size:13px;font-weight:700;color:var(--text);
                  border-top:1px solid var(--border);border-bottom:1px solid var(--border); }
.is-row-result  { font-size:14px;font-weight:800;border-top:2px solid var(--border); }
.is-mono        { font-family:var(--mono);font-weight:600;white-space:nowrap; }
.is-prev        { font-family:var(--mono);font-size:12px;color:var(--text-dim);white-space:nowrap; }
.is-col-num     { text-align:right;width:160px; }
.is-col-prev    { text-align:right;width:140px; }
.is-col-bar     { width:100px;padding-right:20px !important; }
.is-bar-wrap    { height:4px;background:var(--border);border-radius:2px;overflow:hidden; }
.is-bar-fill    { height:100%;border-radius:2px;transition:width 0.4s; }
.is-margin-badge { display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;
                   font-size:10px;font-weight:700;margin-left:8px; }
.is-delta       { display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;
                  padding:1px 6px;border-radius:999px;margin-left:6px;white-space:nowrap; }
.is-delta-up    { background:var(--green-dim);color:var(--green); }
.is-delta-dn    { background:var(--red-dim);color:var(--red); }

/* ── Print action button ── */
.is-print-btn   { padding:7px 14px;border-radius:var(--rsm);font-size:12px;font-weight:600;
                  cursor:pointer;border:1px solid var(--border);background:var(--surface2);
                  color:var(--text-dim);display:flex;align-items:center;gap:5px;
                  transition:all var(--tr);font-family:var(--font); }
.is-print-btn:hover { background:var(--surface3);color:var(--text); }

/* ── Print ── */
@media print {
    .is-no-print,
    .is-presets,
    .is-filter-row,
    .is-header > div:last-child { display:none !important; }
    .is-page  { padding:0 !important; }
    .is-card  { border:none !important;border-radius:0 !important;box-shadow:none !important; }
    .is-print-only { display:block !important; }
    .is-table { page-break-inside:auto; }
    .is-table tr { page-break-inside:avoid;page-break-after:auto; }
    .is-section-hd { page-break-after:avoid; }
    * { font-family:'Georgia',serif !important; }
    .is-col-num { font-family:'Courier New',monospace !important; }
}

/* ── Responsive ── */
@media(max-width:640px) {
    .is-header     { margin-bottom:14px; }
    .is-col-bar    { display:none; }
    .is-col-prev   { display:table-cell;min-width:90px; }
    .is-col-prev .is-delta { display:none; }
    .is-card-head  { padding:10px 14px;flex-wrap:wrap;gap:8px; }
    .is-table td   { padding:8px 12px; }
    .is-section-hd { padding:6px 12px 3px; }
    .is-row-indent { padding-left:22px !important; }
    .is-col-num    { width:120px; }
    .is-filter-row { flex-wrap:wrap; }
    .is-filter-seg { border-right:none;border-bottom:1px solid var(--border);width:100%; }
    .is-filter-seg:last-child { border-bottom:none; }
    .is-date-input { width:auto; }
}
</style>

<div class="is-page">

    {{-- ── Header ── --}}
    <div class="is-header">
        <div>
            <h1 class="is-header-title">Income Statement</h1>
            <p class="is-header-sub">
                {{ $periodLabel }}
                @if ($locationFilter !== 'all')
                    &middot; {{ $shopName }}
                @endif
                &middot; <span style="font-family:var(--mono);">{{ number_format($statement['transaction_count'] ?? 0) }}</span> sales
            </p>
        </div>
        <div class="is-no-print" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <button onclick="window.print()" class="is-print-btn">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-5a1 1 0 00-1-1H9a1 1 0 00-1 1v5a1 1 0 001 1zm1-9V5a1 1 0 011-1h2l2 2v3"/>
                </svg>
                Print
            </button>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="is-no-print is-filters">

        {{-- Period presets --}}
        <div class="is-presets">
            @foreach ([
                'this_month' => 'This Month',
                'last_month' => 'Last Month',
                'this_year'  => 'This Year',
                'last_year'  => 'Last Year',
                'this_week'  => 'This Week',
                'custom'     => 'Custom',
            ] as $key => $label)
                <button wire:click="setPreset('{{ $key }}')"
                        class="is-preset-btn {{ $period === $key ? 'active' : '' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Date range + shop filter --}}
        <div class="is-filter-row">
            <div class="is-filter-seg">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     stroke-width="2" style="flex-shrink:0;color:var(--text-dim);">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                <input type="date" wire:model="dateFrom" class="is-date-input">
                <span style="font-size:13px;color:var(--text-dim);flex-shrink:0;">→</span>
                <input type="date" wire:model="dateTo" class="is-date-input">
            </div>
            <div class="is-filter-seg is-filter-seg-grow">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     stroke-width="2" style="flex-shrink:0;color:var(--text-dim);">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    <polyline stroke-linecap="round" stroke-linejoin="round" points="9 22 9 12 15 12 15 22"/>
                </svg>
                <select wire:model.live="locationFilter" class="is-shop-select">
                    <option value="all">All Shops</option>
                    @foreach ($shops as $shop)
                        <option value="shop:{{ $shop['id'] }}">{{ $shop['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>

    {{-- ── Statement Card ── --}}
    @php
        $s = $statement + [
            'gross_revenue' => 0, 'total_returns' => 0, 'net_revenue' => 0,
            'total_cost' => 0, 'gross_profit' => 0, 'gross_margin_pct' => 0,
            'expenses_by_category' => [], 'total_expenses' => 0,
            'operating_profit' => 0, 'operating_margin_pct' => 0,
            'total_withdrawals' => 0, 'net_result' => 0, 'net_margin_pct' => 0,
            'transaction_count' => 0, 'return_count' => 0,
            'prev_gross_revenue' => 0, 'prev_total_returns' => 0, 'prev_net_revenue' => 0,
            'prev_total_cost' => 0, 'prev_gross_profit' => 0, 'prev_total_expenses' => 0,
            'prev_operating_profit' => 0, 'prev_total_withdrawals' => 0, 'prev_net_result' => 0,
        ];
        $hasData = $s['gross_revenue'] > 0 || $s['total_expenses'] > 0;
        $maxRev  = max($s['net_revenue'], 1);
    @endphp

    <div class="is-card">

        {{-- Print header — hidden on screen --}}
        <div class="is-print-only" style="display:none;padding:20px 24px 16px;border-bottom:2px solid var(--border);">
            <div style="font-size:20px;font-weight:700;margin-bottom:4px;">Income Statement</div>
            <div style="font-size:13px;margin-bottom:2px;">
                Period: {{ $periodLabel }}
                @if($locationFilter !== 'all') · {{ $shopName }} @endif
            </div>
            <div style="font-size:12px;">
                Generated: {{ now()->format('d M Y, H:i') }}
                · {{ number_format($statement['transaction_count'] ?? 0) }} transactions
            </div>
        </div>

        {{-- Card header --}}
        @php $opColor = $s['operating_profit'] >= 0 ? 'var(--green)' : 'var(--red)'; @endphp
        <div class="is-card-head">
            <div>
                <div class="is-card-head-label">Income Statement</div>
                <div class="is-card-head-sub">
                    {{ $periodLabel }}
                    @if ($locationFilter !== 'all') &middot; {{ $shopName }} @endif
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:11px;color:var(--text-dim);margin-bottom:2px;">Operating Profit</div>
                <div style="font-size:20px;font-weight:800;font-family:var(--mono);color:{{ $opColor }};">
                    {{ $s['operating_profit'] < 0 ? '(' : '' }}{{ number_format(abs($s['operating_profit'])) }}{{ $s['operating_profit'] < 0 ? ')' : '' }}
                </div>
                <div style="font-size:11px;font-weight:600;color:{{ $opColor }};">
                    {{ $s['operating_margin_pct'] }}% margin
                    @if ($s['prev_operating_profit'] != 0)
                        @php
                            $delta   = round((($s['operating_profit'] - $s['prev_operating_profit']) / abs($s['prev_operating_profit'])) * 100, 1);
                            $deltaUp = $delta >= 0;
                        @endphp
                        <span class="is-delta {{ $deltaUp ? 'is-delta-up' : 'is-delta-dn' }}">
                            {{ $deltaUp ? '▲' : '▼' }} {{ abs($delta) }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Empty state --}}
        @if (! $hasData)
            <div style="padding:48px 20px;text-align:center;color:var(--text-dim);font-size:13px;">
                No activity recorded for this period.
            </div>
        @else

        <table class="is-table">

            {{-- Column headers --}}
            <thead>
                <tr style="border-bottom:2px solid var(--border);">
                    <td style="padding:7px 20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-dim);">Line Item</td>
                    <td class="is-col-bar"></td>
                    <td class="is-col-num" style="padding:7px 20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-dim);text-align:right;">
                        {{ $periodLabel }}
                    </td>
                    <td class="is-col-prev" style="padding:7px 20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-dim);text-align:right;">
                        Prior Period
                        <div style="font-size:9px;font-weight:400;color:var(--text-dim);margin-top:1px;text-transform:none;letter-spacing:0;">
                            {{ $priorPeriodLabel }}
                        </div>
                    </td>
                </tr>
            </thead>

            <tbody>

                {{-- ── REVENUE ── --}}
                <tr><td colspan="4" class="is-section-hd">Revenue</td></tr>

                <tr class="is-row-body">
                    <td class="is-row-label is-row-indent">
                        Gross Sales Revenue
                        <span style="font-size:11px;color:var(--text-dim);margin-left:4px;">
                            ({{ number_format($s['transaction_count']) }} {{ $s['transaction_count'] === 1 ? 'sale' : 'sales' }})
                        </span>
                    </td>
                    <td class="is-col-bar">
                        <div class="is-bar-wrap">
                            <div class="is-bar-fill" style="width:100%;background:var(--accent-dim);"></div>
                        </div>
                    </td>
                    <td class="is-col-num"><span class="is-mono">{{ number_format($s['gross_revenue']) }}</span></td>
                    <td class="is-col-prev"><span class="is-prev">{{ number_format($s['prev_gross_revenue']) }}</span></td>
                </tr>

                @if ($s['total_returns'] > 0 || $s['prev_total_returns'] > 0)
                <tr class="is-row-body">
                    <td class="is-row-label is-row-indent" style="color:var(--red);">
                        Less: Returns &amp; Refunds
                        @if ($s['return_count'] > 0)
                            <span style="font-size:11px;color:var(--text-dim);margin-left:4px;">({{ $s['return_count'] }})</span>
                        @endif
                    </td>
                    <td class="is-col-bar"></td>
                    <td class="is-col-num">
                        <span class="is-mono" style="color:var(--red);">({{ number_format($s['total_returns']) }})</span>
                    </td>
                    <td class="is-col-prev"><span class="is-prev">({{ number_format($s['prev_total_returns']) }})</span></td>
                </tr>
                @endif

                <tr class="is-row-total">
                    <td>Net Revenue</td>
                    <td class="is-col-bar"></td>
                    <td class="is-col-num">
                        <span class="is-mono" style="color:var(--accent);">{{ number_format($s['net_revenue']) }}</span>
                    </td>
                    <td class="is-col-prev"><span class="is-prev">{{ number_format($s['prev_net_revenue']) }}</span></td>
                </tr>

                {{-- ── COST OF GOODS SOLD ── --}}
                <tr><td colspan="4" class="is-section-hd">Cost of Goods Sold</td></tr>

                <tr class="is-row-body">
                    <td class="is-row-label is-row-indent">Cost of Products Sold (COGS)</td>
                    <td class="is-col-bar">
                        @if ($s['net_revenue'] > 0)
                        <div class="is-bar-wrap">
                            <div class="is-bar-fill"
                                 style="width:{{ min(100, round(($s['total_cost'] / $maxRev) * 100)) }}%;background:var(--red);opacity:0.5;">
                            </div>
                        </div>
                        @endif
                    </td>
                    <td class="is-col-num">
                        <span class="is-mono" style="color:var(--red);">({{ number_format($s['total_cost']) }})</span>
                    </td>
                    <td class="is-col-prev"><span class="is-prev">({{ number_format($s['prev_total_cost']) }})</span></td>
                </tr>

                {{-- ── GROSS PROFIT ── --}}
                @php $gpColor = $s['gross_profit'] >= 0 ? 'var(--green)' : 'var(--red)'; @endphp
                <tr class="is-row-total">
                    <td>
                        Gross Profit
                        <span class="is-margin-badge"
                              style="background:{{ $s['gross_profit'] >= 0 ? 'var(--green-dim)' : 'var(--red-dim)' }};
                                     color:{{ $gpColor }};">
                            {{ $s['gross_margin_pct'] }}% margin
                        </span>
                    </td>
                    <td class="is-col-bar">
                        @if ($s['net_revenue'] > 0)
                        <div class="is-bar-wrap">
                            <div class="is-bar-fill"
                                 style="width:{{ min(100, max(0, round(($s['gross_profit'] / $maxRev) * 100))) }}%;background:var(--green);">
                            </div>
                        </div>
                        @endif
                    </td>
                    <td class="is-col-num">
                        <span class="is-mono" style="color:{{ $gpColor }};">
                            {{ $s['gross_profit'] < 0 ? '(' : '' }}{{ number_format(abs($s['gross_profit'])) }}{{ $s['gross_profit'] < 0 ? ')' : '' }}
                        </span>
                    </td>
                    <td class="is-col-prev">
                        <span class="is-prev">{{ $s['prev_gross_profit'] < 0 ? '(' : '' }}{{ number_format(abs($s['prev_gross_profit'])) }}{{ $s['prev_gross_profit'] < 0 ? ')' : '' }}</span>
                    </td>
                </tr>

                {{-- ── OPERATING EXPENSES ── --}}
                <tr><td colspan="4" class="is-section-hd">Operating Expenses</td></tr>

                @forelse ($s['expenses_by_category'] as $exp)
                <tr class="is-row-body">
                    <td class="is-row-label is-row-indent">
                        {{ $exp['category'] }}
                        <span style="font-size:11px;color:var(--text-dim);margin-left:4px;">({{ $exp['count'] }})</span>
                        <span style="font-size:10px;color:var(--text-dim);margin-left:4px;">{{ $exp['pct_of_total'] }}%</span>
                    </td>
                    <td class="is-col-bar">
                        <div class="is-bar-wrap">
                            <div class="is-bar-fill"
                                 style="width:{{ $exp['pct_of_total'] }}%;background:var(--amber);opacity:0.7;">
                            </div>
                        </div>
                    </td>
                    <td class="is-col-num">
                        <span class="is-mono" style="color:var(--amber);">({{ number_format($exp['total']) }})</span>
                    </td>
                    <td class="is-col-prev"><span class="is-prev">—</span></td>
                </tr>
                @empty
                <tr class="is-row-body">
                    <td class="is-row-label is-row-indent" style="color:var(--text-dim);">No expenses recorded</td>
                    <td class="is-col-bar"></td>
                    <td class="is-col-num"><span class="is-mono" style="color:var(--text-dim);">0</span></td>
                    <td class="is-col-prev"><span class="is-prev">0</span></td>
                </tr>
                @endforelse

                <tr class="is-row-total">
                    <td>Total Operating Expenses</td>
                    <td class="is-col-bar"></td>
                    <td class="is-col-num">
                        <span class="is-mono" style="color:{{ $s['total_expenses'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }};">
                            @if ($s['total_expenses'] > 0)({{ number_format($s['total_expenses']) }})@else 0 @endif
                        </span>
                    </td>
                    <td class="is-col-prev">
                        <span class="is-prev">
                            @if ($s['prev_total_expenses'] > 0)({{ number_format($s['prev_total_expenses']) }})@else 0 @endif
                        </span>
                    </td>
                </tr>

                {{-- ── OPERATING PROFIT ── --}}
                <tr class="is-row-result" style="background:{{ $s['operating_profit'] >= 0 ? 'var(--green-dim)' : 'var(--red-dim)' }};">
                    <td style="color:{{ $opColor }};">
                        Operating Profit
                        <span class="is-margin-badge"
                              style="background:{{ $s['operating_profit'] >= 0 ? 'var(--green-dim)' : 'var(--red-dim)' }};
                                     color:{{ $opColor }};border:1px solid {{ $opColor }};">
                            {{ $s['operating_margin_pct'] }}% margin
                        </span>
                    </td>
                    <td class="is-col-bar">
                        @if ($s['net_revenue'] > 0)
                        <div class="is-bar-wrap">
                            <div class="is-bar-fill"
                                 style="width:{{ min(100, max(0, round(($s['operating_profit'] / $maxRev) * 100))) }}%;
                                        background:{{ $s['operating_profit'] >= 0 ? 'var(--green)' : 'var(--red)' }};">
                            </div>
                        </div>
                        @endif
                    </td>
                    <td class="is-col-num">
                        <span class="is-mono" style="color:{{ $opColor }};font-size:16px;">
                            {{ $s['operating_profit'] < 0 ? '(' : '' }}{{ number_format(abs($s['operating_profit'])) }}{{ $s['operating_profit'] < 0 ? ')' : '' }}
                        </span>
                    </td>
                    <td class="is-col-prev">
                        <span class="is-prev" style="color:{{ $s['prev_operating_profit'] >= 0 ? 'var(--text-dim)' : 'var(--red)' }};">
                            {{ $s['prev_operating_profit'] < 0 ? '(' : '' }}{{ number_format(abs($s['prev_operating_profit'])) }}{{ $s['prev_operating_profit'] < 0 ? ')' : '' }}
                        </span>
                    </td>
                </tr>

            </tbody>
        </table>
        @endif

        {{-- Footer note --}}
        <div style="padding:10px 20px;border-top:1px solid var(--border);
                    font-size:11px;color:var(--text-dim);display:flex;gap:16px;flex-wrap:wrap;">
            <span>Amounts in RWF</span>
            <span>COGS based on product purchase prices at time of sale</span>
            <span>Figures in parentheses ( ) are negative</span>
            @if ($locationFilter === 'all') <span>Consolidated across all shops</span> @endif
        </div>

    </div>

</div>
</div>
