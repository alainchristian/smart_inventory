@once
@push('styles')
<style>
.is-page        { padding:28px 0 80px; }
.is-inner       { max-width:960px; margin:0 auto; padding:0 20px; }
.is-header      { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
.is-filters     { display:flex; flex-direction:column; gap:10px; margin-bottom:20px; }
.is-presets     { display:flex; gap:6px; overflow-x:auto; -webkit-overflow-scrolling:touch; padding-bottom:2px; }
.is-presets::-webkit-scrollbar { display:none; }
.is-preset-btn  { padding:6px 14px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;
                  border:1px solid var(--border); background:var(--surface-raised); color:var(--text-dim);
                  transition:all 0.15s; white-space:nowrap; flex-shrink:0; }
.is-preset-btn.active, .is-preset-btn:hover { border-color:var(--accent); color:var(--accent); background:var(--accent-dim); }
.is-filter-row  { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.is-date-input  { padding:7px 10px; border-radius:8px; font-size:12px; border:1px solid var(--border);
                  background:var(--surface-raised); color:var(--text); flex:1; min-width:120px; }
.is-shop-select { padding:7px 10px; border-radius:8px; font-size:12px; border:1px solid var(--border);
                  background:var(--surface-raised); color:var(--text); cursor:pointer; flex:1; min-width:140px; }
.is-apply-btn   { padding:7px 16px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer;
                  border:none; background:var(--accent); color:white; white-space:nowrap; flex-shrink:0; }
.is-card        { border-radius:14px; overflow:hidden; border:1px solid var(--border); background:var(--surface); }
.is-card-head   { padding:14px 20px; background:var(--surface-raised); border-bottom:1px solid var(--border);
                  display:flex; align-items:center; justify-content:space-between; gap:12px; }
.is-table       { width:100%; border-collapse:collapse; }
.is-table td    { padding:9px 20px; vertical-align:middle; }
.is-section-hd  { padding:8px 20px 4px; font-size:10px; font-weight:700; text-transform:uppercase;
                  letter-spacing:0.7px; color:var(--text-faint); background:var(--surface-raised);
                  border-top:1px solid var(--border); border-bottom:1px solid var(--border); }
.is-row-label   { font-size:13px; color:var(--text-dim); }
.is-row-indent  { padding-left:36px !important; }
.is-row-total   { font-size:13px; font-weight:700; color:var(--text); border-top:1px solid var(--border); background:var(--surface-raised); }
.is-row-result  { font-size:14px; font-weight:800; border-top:2px solid var(--border); background:var(--surface-raised); }
.is-mono        { font-family:var(--font-mono); font-weight:600; white-space:nowrap; }
.is-prev        { font-family:var(--font-mono); font-size:12px; color:var(--text-faint); white-space:nowrap; }
.is-col-num     { text-align:right; width:160px; }
.is-col-prev    { text-align:right; width:140px; }
.is-col-bar     { width:100px; padding-right:20px !important; }
.is-bar-wrap    { height:4px; background:var(--border); border-radius:2px; overflow:hidden; }
.is-bar-fill    { height:100%; border-radius:2px; transition:width 0.4s; }
.is-margin-badge { display:inline-flex; align-items:center; padding:2px 8px; border-radius:999px;
                   font-size:10px; font-weight:700; margin-left:8px; }
.is-delta       { display:inline-flex; align-items:center; gap:3px; font-size:10px; font-weight:700;
                  padding:1px 6px; border-radius:999px; margin-left:6px; white-space:nowrap; }
.is-delta-up    { background:var(--green-dim); color:var(--green); }
.is-delta-dn    { background:var(--red-dim,#fee2e2); color:var(--red); }
@media print {
    .is-no-print { display:none !important; }
    .is-page     { padding:0; }
    .is-inner    { max-width:100%; padding:0; }
    .is-card     { border:none; border-radius:0; }
}
@media (max-width:640px) {
    .is-page       { padding:14px 0 60px; }
    .is-inner      { padding:0 12px; }
    .is-header     { margin-bottom:14px; }
    .is-col-bar    { display:none; }
    .is-col-prev   { display:none; }
    .is-card-head  { padding:10px 14px; flex-wrap:wrap; gap:8px; }
    .is-table td   { padding:8px 12px; }
    .is-section-hd { padding:6px 12px 3px; }
    .is-row-indent { padding-left:22px !important; }
    .is-col-num    { width:120px; }
    .is-filter-row { flex-direction:column; align-items:stretch; }
    .is-date-input,
    .is-shop-select { min-width:0; width:100%; flex:none; box-sizing:border-box; }
    .is-apply-btn  { width:100%; text-align:center; }
    .is-date-sep   { display:none; }
}
</style>
@endpush
@endonce

<div class="is-page">

<div class="is-inner">

    {{-- ── Header ── --}}
    <div class="is-header">
        <div>
            <h1 style="font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px;">Income Statement</h1>
            <p style="font-size:13px;color:var(--text-dim);margin:0;">
                {{ $periodLabel }}
                @if ($locationFilter !== 'all')
                    &middot; {{ $shopName }}
                @endif
                &middot; <span style="font-family:var(--font-mono);">{{ number_format($statement['transaction_count'] ?? 0) }}</span> sales
            </p>
        </div>
        <div class="is-no-print" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <button onclick="window.print()"
                    style="padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;
                           border:1px solid var(--border);background:var(--surface-raised);color:var(--text-dim);
                           display:flex;align-items:center;gap:5px;">
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
            <input type="date" wire:model="dateFrom"
                   class="is-date-input"
                   onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'">
            <span class="is-date-sep" style="font-size:12px;color:var(--text-faint);flex-shrink:0;">to</span>
            <input type="date" wire:model="dateTo"
                   class="is-date-input"
                   onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'">
            <select wire:model.live="locationFilter" class="is-shop-select">
                <option value="all">All Shops</option>
                @foreach ($shops as $shop)
                    <option value="shop:{{ $shop['id'] }}">{{ $shop['name'] }}</option>
                @endforeach
            </select>
            <button wire:click="applyDates" class="is-apply-btn">Apply</button>
        </div>

    </div>

    {{-- ── Statement Card ── --}}
    @php
        $s       = $statement + [
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

        {{-- Card header --}}
        <div class="is-card-head">
            <div>
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-dim);">
                    Income Statement
                </div>
                <div style="font-size:12px;color:var(--text-faint);margin-top:2px;">
                    {{ $periodLabel }}
                    @if ($locationFilter !== 'all') &middot; {{ $shopName }} @endif
                </div>
            </div>
            {{-- Net result summary --}}
            @php
                $resultColor = $s['operating_profit'] >= 0 ? 'var(--green)' : 'var(--red)';
            @endphp
            <div style="text-align:right;">
                <div style="font-size:11px;color:var(--text-faint);margin-bottom:2px;">Net Profit</div>
                <div style="font-size:20px;font-weight:800;font-family:var(--font-mono);color:{{ $resultColor }};">
                    {{ $s['operating_profit'] < 0 ? '(' : '' }}{{ number_format(abs($s['operating_profit'])) }}{{ $s['operating_profit'] < 0 ? ')' : '' }}
                </div>
                <div style="font-size:11px;font-weight:600;color:{{ $resultColor }};">
                    {{ $s['operating_margin_pct'] }}% margin
                    @if ($s['prev_operating_profit'] != 0)
                        @php
                            $delta = round((($s['operating_profit'] - $s['prev_operating_profit']) / abs($s['prev_operating_profit'])) * 100, 1);
                            $deltaUp = $delta >= 0;
                        @endphp
                        <span class="is-delta {{ $deltaUp ? 'is-delta-up' : 'is-delta-dn' }}">
                            {{ $deltaUp ? '▲' : '▼' }} {{ abs($delta) }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Statement table --}}
        @if (! $hasData)
            <div style="padding:40px 20px;text-align:center;color:var(--text-faint);font-size:13px;">
                No activity recorded for this period.
            </div>
        @else
        <table class="is-table">

            {{-- Column headers --}}
            <thead>
                <tr style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <td style="padding:7px 20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-faint);">Line Item</td>
                    <td class="is-col-bar"></td>
                    <td class="is-col-num" style="padding:7px 20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-faint);text-align:right;">
                        {{ $periodLabel }}
                    </td>
                    <td class="is-col-prev" style="padding:7px 20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-faint);text-align:right;">
                        Prev. Period
                    </td>
                </tr>
            </thead>

            <tbody>

                {{-- ── REVENUE ── --}}
                <tr><td colspan="4" class="is-section-hd">Revenue</td></tr>

                <tr>
                    <td class="is-row-label is-row-indent">Gross Sales Revenue
                        <span style="font-size:11px;color:var(--text-faint);margin-left:4px;">
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
                <tr>
                    <td class="is-row-label is-row-indent" style="color:var(--red);">Less: Returns &amp; Refunds
                        @if ($s['return_count'] > 0)
                            <span style="font-size:11px;color:var(--text-faint);margin-left:4px;">({{ $s['return_count'] }})</span>
                        @endif
                    </td>
                    <td class="is-col-bar"></td>
                    <td class="is-col-num">
                        <span class="is-mono" style="color:var(--red);">
                            ({{ number_format($s['total_returns']) }})
                        </span>
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

                <tr>
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
                              style="background:{{ $s['gross_profit'] >= 0 ? 'var(--green-dim)' : 'var(--red-dim,#fee2e2)' }};
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
                <tr style="border-bottom:1px solid var(--border);" onmouseover="this.style.background='var(--surface-raised)'" onmouseout="this.style.background=''">
                    <td class="is-row-label is-row-indent">
                        {{ $exp['category'] }}
                        <span style="font-size:11px;color:var(--text-faint);margin-left:4px;">({{ $exp['count'] }})</span>
                        <span style="font-size:10px;color:var(--text-faint);margin-left:4px;">{{ $exp['pct_of_total'] }}%</span>
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
                <tr>
                    <td class="is-row-label is-row-indent" style="color:var(--text-faint);">No expenses recorded</td>
                    <td class="is-col-bar"></td>
                    <td class="is-col-num"><span class="is-mono" style="color:var(--text-faint);">0</span></td>
                    <td class="is-col-prev"><span class="is-prev">0</span></td>
                </tr>
                @endforelse

                <tr class="is-row-total">
                    <td>Total Operating Expenses</td>
                    <td class="is-col-bar"></td>
                    <td class="is-col-num">
                        <span class="is-mono" style="color:{{ $s['total_expenses'] > 0 ? 'var(--amber)' : 'var(--text-faint)' }};">
                            @if ($s['total_expenses'] > 0)({{ number_format($s['total_expenses']) }})@else 0 @endif
                        </span>
                    </td>
                    <td class="is-col-prev">
                        <span class="is-prev">
                            @if ($s['prev_total_expenses'] > 0)({{ number_format($s['prev_total_expenses']) }})@else 0 @endif
                        </span>
                    </td>
                </tr>

                {{-- ── NET PROFIT ── --}}
                @php $opColor = $s['operating_profit'] >= 0 ? 'var(--green)' : 'var(--red)'; @endphp
                <tr class="is-row-result" style="background:{{ $s['operating_profit'] >= 0 ? 'var(--green-dim)' : 'var(--red-dim,#fee2e2)' }};">
                    <td style="color:{{ $opColor }};">
                        Net Profit
                        <span class="is-margin-badge"
                              style="background:{{ $s['operating_profit'] >= 0 ? 'var(--green-dim)' : 'var(--red-dim,#fee2e2)' }};
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
        <div style="padding:10px 20px;background:var(--surface-raised);border-top:1px solid var(--border);
                    font-size:11px;color:var(--text-faint);display:flex;gap:16px;flex-wrap:wrap;">
            <span>Amounts in RWF</span>
            <span>COGS based on product purchase prices at time of sale</span>
            <span>Figures in parentheses ( ) are negative</span>
            @if ($locationFilter === 'all') <span>Consolidated across all shops</span> @endif
        </div>
    </div>

</div>
</div>
</div>
