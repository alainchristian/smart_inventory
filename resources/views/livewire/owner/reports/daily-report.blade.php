<div style="font-family:var(--font)">
<style>
/* ── Owner Daily Report ── odr- ──────────────────────────────────── */

.odr-header       { display:flex;align-items:flex-start;justify-content:space-between;
                   gap:16px;margin-bottom:24px;flex-wrap:wrap; }
.odr-header-title { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px; }
.odr-header-sub   { font-size:13px;color:var(--text-dim);margin:0; }

/* Filters */
.odr-filters     { background:var(--surface);border:none;border-radius:var(--r);
                  box-shadow:var(--shadow-card);margin-bottom:20px;
                  min-width:0;max-width:100%; }
.odr-presets-row { display:flex;gap:4px;overflow-x:auto;-webkit-overflow-scrolling:touch;
                  padding:10px 14px;border-bottom:1px solid var(--border);
                  scrollbar-width:none;flex-wrap:nowrap;min-width:0; }
.odr-presets-row::-webkit-scrollbar { display:none; }
.odr-preset-btn  { padding:5px 11px;border-radius:6px;font-size:12px;font-weight:600;
                  border:1px solid transparent;background:transparent;color:var(--text-dim);
                  cursor:pointer;white-space:nowrap;flex-shrink:0;transition:all var(--tr);
                  font-family:var(--font); }
.odr-preset-btn:hover  { background:var(--surface2);color:var(--text);border-color:var(--border); }
.odr-preset-btn.active { background:var(--accent);color:#fff;border-color:var(--accent);
                        box-shadow:0 2px 8px rgba(0,0,0,.12); }
.odr-filter-row  { display:flex;align-items:center;flex-wrap:wrap; }
.odr-filter-seg  { display:flex;align-items:center;gap:6px;padding:8px 14px;
                  border-right:1px solid var(--border);flex-shrink:0; }
.odr-filter-seg:last-child { border-right:none; }
.odr-date-input  { padding:0;border:none;background:transparent;color:var(--text);
                  font-size:13px;font-weight:600;font-family:var(--font);
                  cursor:pointer;width:130px;outline:none; }
.odr-date-input:focus { color:var(--accent); }
.odr-loc-select:focus { box-shadow:none;border:none;outline:none; }
.odr-loc-select  { padding:0;border:none;background:transparent;color:var(--text);
                  font-size:13px;font-weight:600;font-family:var(--font);
                  cursor:pointer;outline:none; }

/* Report grid — pairs narrower tables side by side on wide screens to cut
   down on dead white space; wide/many-column tables span both columns. */
.odr-grid    { display:grid;grid-template-columns:repeat(2, minmax(0,1fr));gap:20px;margin-bottom:20px;
              grid-auto-flow:dense; }
.odr-span-2  { grid-column:span 2; }

@media(max-width:900px) {
    .odr-grid   { grid-template-columns:1fr; }
    .odr-span-2 { grid-column:span 1; }
}

/* Tables */
.odr-table-wrap { background:var(--surface);border:none;border-radius:var(--r);
                 box-shadow:var(--shadow-card);min-width:0; }
.odr-table-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch; }
.odr-table { width:100%;border-collapse:collapse; }
.odr-table thead tr { border-bottom:2px solid var(--border); }
.odr-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;
                     letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);
                     white-space:nowrap; }
.odr-table tbody tr { border-bottom:1px solid var(--border); }
.odr-table tbody tr:last-child { border-bottom:none; }
.odr-table td { padding:13px 16px;font-size:13px;vertical-align:middle; }
.odr-table tbody tr.odr-total-row td { font-weight:700;border-top:2px solid var(--border); }
.odr-empty       { padding:40px 20px;text-align:center; }
.odr-empty-title { font-size:14px;font-weight:700;color:var(--text-sub);margin-bottom:4px; }
.odr-empty-sub   { font-size:12px;color:var(--text-dim); }

/* View mode toggle — Summary (totals & breakdowns) vs Transactions (every
   individual sale/expense line). */    

/* Financial table section dividers (Summary: Revenue / Cost & Profit / Expenses) */
.odr-table td.odr-section-hd { padding:10px 16px 6px;font-size:10px;font-weight:700;
                 text-transform:uppercase;letter-spacing:0.7px;
                 color:var(--accent);border-top:1px solid var(--border); }

.odr-icon-toggle { width:28px;height:28px;border-radius:8px;border:1px solid var(--border);background:var(--surface);
                  color:var(--text-dim);display:inline-flex;align-items:center;justify-content:center;
                  cursor:pointer;flex-shrink:0;transition:all var(--tr); }
.odr-icon-toggle:hover  { background:var(--surface2);color:var(--text); }
.odr-icon-toggle.active { background:var(--accent-dim);border-color:var(--accent);color:var(--accent); }
/* Header actions — segmented view switch + one primary action */
.odr-view-tabs { display:inline-flex;gap:2px;padding:3px;border-radius:10px;
                 background:var(--bg);border:1px solid var(--border);flex-shrink:0; }
.odr-view-tab  { padding:6px 16px;border-radius:7px;border:none;background:transparent;cursor:pointer;
                 font-size:13px;font-weight:600;font-family:var(--font);color:var(--text-dim);
                 transition:all var(--tr);white-space:nowrap; }
.odr-view-tab:hover  { color:var(--text); }
.odr-view-tab.active { background:var(--surface);color:var(--text);box-shadow:0 1px 3px rgba(26,31,54,.12), 0 0 0 1px var(--border); }
.odr-btn-primary, .odr-btn-secondary { height:36px;padding:0 14px;border-radius:9px;font-size:13px;font-weight:600;
                 cursor:pointer;font-family:var(--font);transition:all var(--tr);box-sizing:border-box;
                 display:inline-flex;align-items:center;gap:7px;white-space:nowrap;text-decoration:none; }
.odr-btn-primary   { background:var(--accent);color:#fff;border:1px solid var(--accent); }
.odr-btn-primary:hover { opacity:.9; }
.odr-btn-secondary { background:var(--surface);color:var(--text-sub);border:1px solid var(--border); }
.odr-btn-secondary:hover { background:var(--surface2);color:var(--text);border-color:var(--border-hi); }
.odr-actions { display:flex;gap:10px;align-items:center;flex-wrap:wrap; }
.odr-actions-sep { width:1px;height:22px;background:var(--border); }
/* EXT_02 additions — checks, deltas, reconciliation, per-method columns */
.odr-banner { border-radius:var(--r);padding:14px 18px;margin-bottom:20px;min-width:0;
             border-left:4px solid var(--amber);background:var(--amber-dim); }
.odr-banner.odr-banner-red { border-left-color:var(--red);background:var(--red-dim); }
.odr-banner-title { font-size:13px;font-weight:700;color:var(--amber); }
.odr-banner-red .odr-banner-title { color:var(--red); }
.odr-banner-note  { font-size:12px;color:var(--text-sub);margin-top:2px; }
.odr-banner-list  { margin:8px 0 0;padding-left:18px;font-size:12px;color:var(--text-sub);
                   line-height:1.6;overflow-wrap:anywhere; }
.odr-pill { display:inline-flex;align-items:center;font-size:11px;font-weight:700;
           padding:2px 8px;border-radius:6px;white-space:nowrap; }
.odr-pill-green { background:var(--green-dim);color:var(--green); }
.odr-pill-amber { background:var(--amber-dim);color:var(--amber); }
.odr-pill-red   { background:var(--red-dim);color:var(--red); }
.odr-pill-dim   { background:var(--surface2);color:var(--text-dim); }
.odr-panel-sub  { font-size:12px;color:var(--text-dim);margin-top:2px; }
.odr-delta      { font-size:11px;font-family:var(--mono);margin-top:2px;white-space:nowrap; }
.odr-num        { text-align:right;font-family:var(--mono);white-space:nowrap; }
.odr-rc-block + .odr-rc-block { border-top:2px solid var(--border); }
.odr-rc-head    { display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:12px 20px;
                 border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--text); }
.odr-rc-live    { font-size:12px;font-weight:500;color:var(--text-dim); }
.odr-msg        { white-space:normal;min-width:260px; }  
</style>

@php
    $isAllShops = $locationFilter === 'all';
    $isSingleDay = $dateFrom === $dateTo;

    $channels = [
        ['label' => 'Cash',          'amount' => $summary['total_sales_cash']],
        ['label' => 'Mobile Money',  'amount' => $summary['total_sales_momo']],
    ];
    if ($settingAllowCard) {
        $channels[] = ['label' => 'Card', 'amount' => $summary['total_sales_card']];
    }
    if ($settingAllowBankTransfer) {
        $channels[] = ['label' => 'Bank Transfer', 'amount' => $summary['total_sales_bank_transfer']];
    }
    $channels[] = ['label' => 'Credit (not yet collected)', 'amount' => $summary['total_sales_credit'], 'uncollected' => true];
    if ($summary['total_sales_other'] > 0) {
        $channels[] = ['label' => 'Other', 'amount' => $summary['total_sales_other']];
    }

    // One "<channel> — by Customer" table per payment method — each only
    // renders when it has rows, so a disabled or unused channel simply
    // doesn't appear on the report.
    $paymentByCustomerTables = [
        ['label' => 'Cash — by Customer',          'data' => $summary['cash_by_customer']],
        ['label' => 'Mobile Money — by Customer',  'data' => $summary['momo_by_customer']],
        ['label' => 'Card — by Customer',          'data' => $summary['card_by_customer']],
        ['label' => 'Bank Transfer — by Customer', 'data' => $summary['bank_by_customer']],
    ];
@endphp

<div class="odr-header">
    <div>
        <h1 class="odr-header-title">Daily Report</h1>
        <p class="odr-header-sub">{{ $this->activeDateRangeLabel }} · {{ $this->selectedShopName }} · {{ $summary['transaction_count'] }} {{ Str::plural('transaction', $summary['transaction_count']) }}</p>
    </div>
    <div class="odr-actions">
        <div class="odr-view-tabs">
            <button class="odr-view-tab {{ $viewMode === 'summary' ? 'active' : '' }}" wire:click="setViewMode('summary')">Summary</button>
            <button class="odr-view-tab {{ $viewMode === 'transactions' ? 'active' : '' }}" wire:click="setViewMode('transactions')">Transactions</button>
        </div>
        <span class="odr-actions-sep"></span>
        <a class="odr-btn-secondary" href="{{ route('owner.reports.daily.print', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'shop' => $locationFilter, 'view' => $viewMode, 'profit' => $showProfit ? 1 : 0]) }}" target="_blank" rel="noopener">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print Report
        </a>
        <a class="odr-btn-primary" href="{{ route('owner.reports.daily.pdf', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'shop' => $locationFilter, 'view' => $viewMode]) }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download PDF
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="odr-filters">
    <div class="odr-presets-row">
        @foreach(['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_quarter' => 'This Quarter', 'this_year' => 'This Year'] as $key => $label)
            <button class="odr-preset-btn {{ $preset === $key ? 'active' : '' }}" wire:click="setPreset('{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>
    <div class="odr-filter-row">
        <div class="odr-filter-seg">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--text-dim)">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <input type="date" wire:model.live="dateFrom" class="odr-date-input">
            <span style="font-size:13px;color:var(--text-dim);flex-shrink:0;">→</span>
            <input type="date" wire:model.live="dateTo" class="odr-date-input">
        </div>
        <div class="odr-filter-seg">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--text-dim)">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            <select wire:model.live="locationFilter" class="odr-loc-select">
                <option value="all">All Shops</option>
                @foreach($this->shops as $shop)
                    <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- Data-quality checks banner (EXT_02) --}}
@php
    $odrAttn        = collect($checks)->filter(fn ($c) => in_array($c['severity'], ['critical', 'warning'], true))->values();
    $odrAnyCritical = $odrAttn->contains('severity', 'critical');
    $odrProvisional = $odrAttn->contains('code', 'session_open');
@endphp
@if($odrAttn->isNotEmpty())
<div class="odr-banner {{ $odrAnyCritical ? 'odr-banner-red' : '' }}">
    <div class="odr-banner-title">{{ $odrAttn->count() }} {{ Str::plural('item', $odrAttn->count()) }} {{ $odrAttn->count() === 1 ? 'needs' : 'need' }} attention</div>
    @if($odrProvisional)<div class="odr-banner-note">Figures are provisional until all sessions are closed</div>@endif
    <ul class="odr-banner-list">
        @foreach($odrAttn->take(5) as $c)
        <li>{{ $c['message'] }}@if(!empty($c['shop_name'])) — {{ $c['shop_name'] }}@endif@if(!empty($c['date'])) ({{ \Carbon\Carbon::parse($c['date'])->format('d M Y') }})@endif</li>
        @endforeach
    </ul>
    @if($odrAttn->count() > 5)<div class="odr-banner-note">+ {{ $odrAttn->count() - 5 }} more — see Checks at the bottom.</div>@endif
</div>
@endif

<div class="section-label">As of Right Now — Not Affected by the Date Filter</div>
<div class="odr-grid">

{{-- Business Position (Cash on Hand, Outstanding Receivables) — cash-only:
     MoMo/Bank have no persisted opening balance or physical-count step the
     way cash does, so there's no real "balance" for them to report here.
     See the Cash Register table below for their period movement instead. --}}
<div class="odr-table-wrap odr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Business Position</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Metric</th>
                <th style="text-align:right">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Cash on Hand</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:var(--green)">{{ number_format($position['cash']) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
            <tr>
                <td>Outstanding Receivables</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:var(--amber)">{{ number_format($summary['outstanding_receivables']) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
            @if(! $isAllShops && ($summary['unassigned_receivables'] ?? 0) > 0)
            <tr>
                <td style="color:var(--text-dim)">Customers not tied to a shop <span style="font-size:11px">(not included above)</span></td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:var(--text-dim)">{{ number_format($summary['unassigned_receivables']) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
            @endif
        </tbody>
    </table>
    </div>
    @if($position['stale'])
    <div style="margin:0 20px 14px;padding:10px 14px;border-radius:8px;border-left:3px solid var(--amber);background:var(--amber-dim);color:var(--amber);font-size:11.5px">
        <strong>⚠ Includes an unreconciled session.</strong>
        @if(isset($position['stale_shops']))
            {{ $position['stale_shops']->map(fn ($s) => $s['shop_name'] . ' (open since ' . $s['as_of']->format('d M Y') . ', ' . (int) floor($s['as_of']->diffInDays(business_today())) . ' days)')->join(', ') }}
        @else
            Open since {{ $position['as_of']->format('d M Y') }} ({{ (int) floor($position['as_of']->diffInDays(business_today())) }} days) — nobody has closed/counted this drawer since.
        @endif
        Cash on Hand above includes this session's live, uncounted figure.
    </div>
    @endif
</div>

</div>
<div class="section-label">{{ $this->activeDateRangeLabel }} — {{ $this->selectedShopName }}</div>
<div class="odr-grid">

@if($viewMode === 'summary')
{{-- Summary --}}
<div class="odr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Summary</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Metric</th>
                <th style="text-align:right">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Boxes Sold</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700">{{ number_format($summary['total_boxes_sold']) }}</span></td>
            </tr>
            <tr><td colspan="2" class="odr-section-hd">Revenue</td></tr>
            <tr>
                <td>Total Sales</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:var(--green)">{{ number_format($summary['total_sales']) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
            <tr>
                <td>— of which Total Credits</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:var(--amber)">{{ number_format($summary['total_sales_credit']) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
            <tr>
                <td>Credit Repayments Received</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:var(--green)">{{ number_format($summary['total_repayments']) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
            <tr><td colspan="2" class="odr-section-hd">Expenses</td></tr>
            <tr>
                <td>Total Expenses</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:var(--red)">−{{ number_format($summary['total_expenses']) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
            <tr>
                <td>Owner Withdrawals</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:var(--red)">−{{ number_format($summary['total_withdrawals']) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
            @php $balance = (int) $summary['total_sales'] - (int) $summary['total_sales_credit'] + (int) $summary['total_repayments'] - (int) $summary['total_expenses'] - (int) $summary['total_withdrawals']; @endphp
            <tr class="odr-total-row">
                <td>Balance</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);font-weight:700;color:{{ $balance >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $balance < 0 ? '−' : '' }}{{ number_format(abs($balance)) }}</span> <span style="font-size:10px;color:var(--text-dim)">RWF</span></td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

{{-- Boxes Sold by Product (owner/admin: small eye toggle adds cost + profit per product) --}}
<div class="odr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px">
        <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $showProfit ? 'Sold by Product' : 'Boxes Sold by Product' }}</div>
        <button type="button" class="odr-icon-toggle {{ $showProfit ? 'active' : '' }}" wire:click="toggleProfit" title="{{ $showProfit ? 'Hide profit' : 'Show profit' }}" aria-label="{{ $showProfit ? 'Hide profit' : 'Show profit' }}" aria-pressed="{{ $showProfit ? 'true' : 'false' }}">
            @if($showProfit)
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            @else
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            @endif
        </button>
    </div>
    @if($showProfit && $profitByProduct->isNotEmpty())
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align:right">Boxes</th>
                <th style="text-align:right">Amount</th>
                <th style="text-align:right">Cost</th>
                <th style="text-align:right">Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($profitByProduct as $row)
            <tr>
                <td>{{ $row->product_name }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->boxes) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->revenue) }}</td>
                <td style="text-align:right;font-family:var(--mono);color:var(--text-dim)">{{ number_format($row->cost) }}</td>
                <td style="text-align:right;font-family:var(--mono);font-weight:700;color:{{ $row->profit >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $row->profit < 0 ? '−' : '' }}{{ number_format(abs($row->profit)) }}</td>
            </tr>
            @endforeach
            @php $odrPTotal = (int) $profitByProduct->sum('profit'); @endphp
            <tr class="odr-total-row">
                <td>Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($profitByProduct->sum('boxes')) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($profitByProduct->sum('revenue')) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($profitByProduct->sum('cost')) }}</td>
                <td style="text-align:right;font-family:var(--mono);color:{{ $odrPTotal >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $odrPTotal < 0 ? '−' : '' }}{{ number_format(abs($odrPTotal)) }}</td>
            </tr>
        </tbody>
    </table>
    </div>
    @elseif(!$showProfit && count($summary['boxes_by_product']) > 0)
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align:right">Boxes Sold</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['boxes_by_product'] as $row)
            <tr>
                <td>{{ $row->product_name }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->boxes) }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td>Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_boxes_sold']) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($summary['boxes_by_product'])->sum('amount')) }} RWF</td>
            </tr>
        </tbody>
    </table>
    </div>
    @else
    <div class="odr-empty">
        <div class="odr-empty-title">No boxes sold in this period</div>
        <div class="odr-empty-sub">Try a different date range.</div>
    </div>
    @endif
</div>
@endif

@if($viewMode === 'transactions')
{{-- All Sales --}}
<div class="odr-table-wrap odr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">All Sales</div>
    </div>
    @if(count($summary['all_sales']) > 0)
    @php
        $odrSaleCard = $settingAllowCard || collect($summary['all_sales'])->sum('card') > 0;
        $odrSaleBank = $settingAllowBankTransfer || collect($summary['all_sales'])->sum('bank_transfer') > 0;
    @endphp
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Sale #</th>
                @if($isAllShops)<th>Shop</th>@endif
                <th>Date</th>
                <th>Customer</th>
                <th style="text-align:right">Cash</th>
                <th style="text-align:right">MoMo</th>
                @if($odrSaleCard)<th style="text-align:right">Card</th>@endif
                @if($odrSaleBank)<th style="text-align:right">Bank</th>@endif
                <th style="text-align:right">Credit</th>
                <th style="text-align:right">Boxes</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['all_sales'] as $row)
            <tr>
                <td style="font-family:var(--mono)">{{ $row->sale_number }}@if($row->has_price_override) <span class="odr-pill odr-pill-amber" style="margin-left:6px">price changed</span>@endif</td>
                @if($isAllShops)<td style="color:var(--text-dim)">{{ $row->shop_name }}</td>@endif
                <td style="color:var(--text-dim)">{{ \Carbon\Carbon::parse($row->sale_date)->format('d M Y') }}</td>
                <td>{{ $row->customer_name ?? '—' }}</td>
                <td class="odr-num">{{ $row->cash ? number_format($row->cash) : '—' }}</td>
                <td class="odr-num">{{ $row->momo ? number_format($row->momo) : '—' }}</td>
                @if($odrSaleCard)<td class="odr-num">{{ $row->card ? number_format($row->card) : '—' }}</td>@endif
                @if($odrSaleBank)<td class="odr-num">{{ $row->bank_transfer ? number_format($row->bank_transfer) : '—' }}</td>@endif
                <td class="odr-num">{{ $row->credit ? number_format($row->credit) : '—' }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->boxes) }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono)">{{ number_format($row->total) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td colspan="{{ $isAllShops ? 4 : 3 }}">Grand Total</td>
                <td class="odr-num">{{ number_format(collect($summary['all_sales'])->sum('cash')) }}</td>
                <td class="odr-num">{{ number_format(collect($summary['all_sales'])->sum('momo')) }}</td>
                @if($odrSaleCard)<td class="odr-num">{{ number_format(collect($summary['all_sales'])->sum('card')) }}</td>@endif
                @if($odrSaleBank)<td class="odr-num">{{ number_format(collect($summary['all_sales'])->sum('bank_transfer')) }}</td>@endif
                <td class="odr-num">{{ number_format(collect($summary['all_sales'])->sum('credit')) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($summary['all_sales'])->sum('boxes')) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($summary['all_sales'])->sum('total')) }} RWF</td>
            </tr>
        </tbody>
    </table>
    </div>
    @else
    <div class="odr-empty">
        <div class="odr-empty-title">No sales in this period</div>
        <div class="odr-empty-sub">Try a different date range.</div>
    </div>
    @endif
</div>
@endif

@if($viewMode === 'summary')
{{-- Cash Register (Opening/Closing Balance) --}}
<div class="odr-table-wrap {{ ($isAllShops || !$isSingleDay) ? 'odr-span-2' : '' }}">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Cash Register</div>
    </div>
    @if($cashRegister->isEmpty())
    <div class="odr-empty">
        <div class="odr-empty-title">No cash session opened {{ $isSingleDay ? 'on this date' : 'in this period' }}</div>
        <div class="odr-empty-sub">Try a different date range.</div>
    </div>
    @elseif($isAllShops)
    {{-- One row per shop: opening = its first session in range, closing = its last --}}
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Shop</th>
                <th style="text-align:right">Opening</th>
                <th style="text-align:right">Closing</th>
                <th style="text-align:right">Variance</th>
                <th style="text-align:right">MoMo Movement</th>
                <th style="text-align:right">Bank Movement</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashRegister as $row)
            <tr>
                <td>{{ $row->shop_name }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->opening) }}</td>
                <td style="text-align:right;font-family:var(--mono)">
                    {{ number_format($row->closing) }}{{ $row->is_open ? ' *' : '' }}
                </td>
                <td style="text-align:right;font-family:var(--mono);color:{{ $row->variance < 0 ? 'var(--red)' : ($row->variance > 0 ? 'var(--amber)' : 'var(--green)') }}">
                    {{ $row->variance > 0 ? '+' : '' }}{{ number_format($row->variance) }}
                </td>
                <td style="text-align:right;font-family:var(--mono);color:var(--text-dim)">{{ number_format($row->momo) }}</td>
                <td style="text-align:right;font-family:var(--mono);color:var(--text-dim)">{{ number_format($row->bank) }}</td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td colspan="3">Total Variance</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($cashRegister->sum('variance')) }} RWF</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($cashRegister->sum('momo')) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($cashRegister->sum('bank')) }}</td>
            </tr>
        </tbody>
    </table>
    </div>
    @if($cashRegister->contains('is_open', true))
    <div style="padding:8px 20px 14px;font-size:11px;color:var(--text-dim)">* still open — closing shown is a live figure, not a final count. Opening is that shop's first session in range; closing is its last. MoMo/Bank Movement is net inflow minus outflow summed across the range — not an account balance (this system doesn't track those separately from cash).</div>
    @endif
    @elseif($isSingleDay)
    @php $day = $cashRegister->first(); @endphp
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Metric</th>
                <th style="text-align:right">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Opening Balance</td>
                <td style="text-align:right;font-family:var(--mono);font-weight:700">{{ number_format($day->opening) }} RWF</td>
            </tr>
            <tr>
                <td>Closing Balance{{ $day->is_open ? ' (as of now)' : '' }}</td>
                <td style="text-align:right;font-family:var(--mono);font-weight:700">{{ number_format($day->closing) }} RWF</td>
            </tr>
            @unless($day->is_open)
            <tr>
                <td>Variance</td>
                <td style="text-align:right;font-family:var(--mono);font-weight:700;color:{{ $day->variance < 0 ? 'var(--red)' : ($day->variance > 0 ? 'var(--amber)' : 'var(--green)') }}">
                    {{ $day->variance > 0 ? '+' : '' }}{{ number_format($day->variance) }} RWF
                </td>
            </tr>
            @endunless
            <tr>
                <td>MoMo Movement</td>
                <td style="text-align:right;font-family:var(--mono);font-weight:700;color:var(--accent)">{{ number_format($day->momo) }} RWF</td>
            </tr>
            <tr>
                <td>Bank Movement</td>
                <td style="text-align:right;font-family:var(--mono);font-weight:700;color:var(--accent)">{{ number_format($day->bank) }} RWF</td>
            </tr>
        </tbody>
    </table>
    </div>
    @else
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Date</th>
                <th style="text-align:right">Opening</th>
                <th style="text-align:right">Closing</th>
                <th style="text-align:right">Variance</th>
                <th style="text-align:right">MoMo Movement</th>
                <th style="text-align:right">Bank Movement</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashRegister as $day)
            <tr>
                <td>{{ $day->date->format('d M Y') }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($day->opening) }}</td>
                <td style="text-align:right;font-family:var(--mono)">
                    {{ number_format($day->closing) }}{{ $day->is_open ? ' *' : '' }}
                </td>
                <td style="text-align:right;font-family:var(--mono);color:{{ $day->is_open ? 'var(--text-dim)' : ($day->variance < 0 ? 'var(--red)' : ($day->variance > 0 ? 'var(--amber)' : 'var(--green)')) }}">
                    @if($day->is_open) — @else {{ $day->variance > 0 ? '+' : '' }}{{ number_format($day->variance) }} @endif
                </td>
                <td style="text-align:right;font-family:var(--mono);color:var(--text-dim)">{{ number_format($day->momo) }}</td>
                <td style="text-align:right;font-family:var(--mono);color:var(--text-dim)">{{ number_format($day->bank) }}</td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td colspan="3">Total Variance</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($cashRegister->whereNotNull('variance')->sum('variance')) }} RWF</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($cashRegister->sum('momo')) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($cashRegister->sum('bank')) }}</td>
            </tr>
        </tbody>
    </table>
    </div>
    @if($cashRegister->contains('is_open', true))
    <div style="padding:8px 20px 14px;font-size:11px;color:var(--text-dim)">* still open — closing shown is a live figure, not a final count</div>
    @endif
    @endif
</div>

{{-- Cash reconciliation (EXT_02) --}}
@if($reconciliation->isNotEmpty())
<div class="odr-table-wrap odr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Cash Reconciliation</div>
    </div>
    @if($isSingleDay)
    @foreach($reconciliation as $rc)
    @php
        $odrRcOpen = $rc->status === 'open';
        $odrRcCol  = $rc->variance === null ? 'var(--text-dim)' : ($rc->variance < 0 ? 'var(--red)' : ($rc->variance > 0 ? 'var(--amber)' : 'var(--green)'));
    @endphp
    <div class="odr-rc-block">
        <div class="odr-rc-head">
            <span>{{ $rc->shop_name }}</span>
            <span class="odr-pill odr-pill-{{ $odrRcOpen ? 'amber' : 'green' }}">{{ ucfirst($rc->status) }}</span>
            <span class="odr-rc-live">{{ $odrRcOpen ? 'live' : 'closed' }}</span>
        </div>
        <div class="odr-table-scroll">
        <table class="odr-table">
            <tbody>
                <tr><td>Opening cash</td><td class="odr-num">{{ number_format($rc->opening) }}</td></tr>
                <tr><td>+ Cash sales</td><td class="odr-num">{{ number_format($rc->cash_sales) }}</td></tr>
                <tr><td>+ Credit repaid in cash</td><td class="odr-num">{{ number_format($rc->cash_repayments) }}</td></tr>
                @if($rc->cash_refunds)<tr><td>− Cash refunds</td><td class="odr-num">{{ number_format($rc->cash_refunds) }}</td></tr>@endif
                <tr><td>− Cash expenses</td><td class="odr-num">{{ number_format($rc->cash_expenses) }}</td></tr>
                @if($rc->cash_withdrawals)<tr><td>− Owner withdrawals (cash)</td><td class="odr-num">{{ number_format($rc->cash_withdrawals) }}</td></tr>@endif
                @if($rc->cash_deposits)<tr><td>− Cash deposited to bank</td><td class="odr-num">{{ number_format($rc->cash_deposits) }}</td></tr>@endif
                @if($rc->other_adjustments)<tr><td style="color:var(--amber)">± Other adjustments</td><td class="odr-num" style="color:var(--amber)">{{ $rc->other_adjustments > 0 ? '+' : '−' }}{{ number_format(abs($rc->other_adjustments)) }}</td></tr>@endif
                <tr class="odr-total-row"><td>= Expected cash</td><td class="odr-num">{{ number_format($rc->expected) }}</td></tr>
                <tr><td>Counted cash</td><td class="odr-num">{{ $rc->counted !== null ? number_format($rc->counted) : 'Session open' }}</td></tr>
                <tr><td style="font-weight:700">Difference</td><td class="odr-num" style="font-weight:700;color:{{ $odrRcCol }}">{{ $rc->variance === null ? '—' : ($rc->variance > 0 ? '+' : '') . number_format($rc->variance) }}</td></tr>
            </tbody>
        </table>
        </div>
    </div>
    @endforeach
    @else
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Date</th>
                @if($isAllShops)<th>Shop</th>@endif
                <th style="text-align:right">Opening</th>
                <th style="text-align:right">Cash in</th>
                <th style="text-align:right">Cash out</th>
                <th style="text-align:right">Expected</th>
                <th style="text-align:right">Counted</th>
                <th style="text-align:right">Difference</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reconciliation as $rc)
            <tr>
                <td style="color:var(--text-dim)">{{ \Carbon\Carbon::parse($rc->date)->format('d M Y') }}</td>
                @if($isAllShops)<td style="color:var(--text-dim)">{{ $rc->shop_name }}</td>@endif
                <td class="odr-num">{{ number_format($rc->opening) }}</td>
                <td class="odr-num">{{ number_format($rc->cash_sales + $rc->cash_repayments) }}</td>
                <td class="odr-num">{{ number_format($rc->cash_refunds + $rc->cash_expenses + $rc->cash_withdrawals + $rc->cash_deposits) }}</td>
                <td class="odr-num">{{ number_format($rc->expected) }}</td>
                <td class="odr-num">{{ $rc->counted !== null ? number_format($rc->counted) : 'Open' }}</td>
                <td class="odr-num" style="color:{{ $rc->variance === null ? 'var(--text-dim)' : ($rc->variance < 0 ? 'var(--red)' : ($rc->variance > 0 ? 'var(--amber)' : 'var(--green)')) }}">{{ $rc->variance === null ? '—' : ($rc->variance > 0 ? '+' : '') . number_format($rc->variance) }}</td>
            </tr>
            @endforeach
            @php $odrRcDiff = (int) $reconciliation->whereNotNull('variance')->sum('variance'); @endphp
            <tr class="odr-total-row">
                <td colspan="{{ $isAllShops ? 3 : 2 }}">Total</td>
                <td class="odr-num">{{ number_format($reconciliation->sum(fn ($r) => $r->cash_sales + $r->cash_repayments)) }}</td>
                <td class="odr-num">{{ number_format($reconciliation->sum(fn ($r) => $r->cash_refunds + $r->cash_expenses + $r->cash_withdrawals + $r->cash_deposits)) }}</td>
                <td colspan="2"></td>
                <td class="odr-num" style="color:{{ $odrRcDiff < 0 ? 'var(--red)' : ($odrRcDiff > 0 ? 'var(--amber)' : 'var(--green)') }}">{{ ($odrRcDiff > 0 ? '+' : '') . number_format($odrRcDiff) }}</td>
            </tr>
        </tbody>
    </table>
    </div>
    @endif
</div>
@endif

{{-- Payment channel distribution --}}
<div class="odr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Distribution by Payment Channel</div>
    </div>
    @if($summary['total_sales'] > 0)
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Channel</th>
                <th style="text-align:right">Amount</th>
                <th style="text-align:right">Share</th>
            </tr>
        </thead>
        <tbody>
            @foreach($channels as $channel)
            <tr style="{{ !empty($channel['uncollected']) ? 'font-style:italic' : '' }}">
                <td style="{{ !empty($channel['uncollected']) ? 'color:var(--text-dim)' : '' }}">{{ $channel['label'] }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono);{{ !empty($channel['uncollected']) ? 'color:var(--text-dim)' : '' }}">{{ number_format($channel['amount']) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
                <td style="text-align:right;font-family:var(--mono);color:var(--text-dim)">
                    {{ number_format($summary['total_sales'] > 0 ? ($channel['amount'] / $summary['total_sales']) * 100 : 0, 1) }}%
                </td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td>Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_sales']) }} RWF</td>
                <td style="text-align:right;font-family:var(--mono)">100.0%</td>
            </tr>
        </tbody>
    </table>
    </div>
    @else
    <div class="odr-empty">
        <div class="odr-empty-title">No sales in this period</div>
        <div class="odr-empty-sub">Try a different date range.</div>
    </div>
    @endif
</div>
{{-- Credit repaid by method (EXT_02) --}}
@if($summary['total_repayments'] > 0)
<div class="odr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Credit repaid by method</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead><tr><th>Method</th><th style="text-align:right">Amount</th></tr></thead>
        <tbody>
            <tr><td>Cash</td><td class="odr-num">{{ number_format($summary['total_repayments_cash']) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></td></tr>
            <tr><td>Mobile Money</td><td class="odr-num">{{ number_format($summary['total_repayments_momo']) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></td></tr>
            <tr><td>Bank</td><td class="odr-num">{{ number_format($summary['total_repayments_bank']) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></td></tr>
            <tr class="odr-total-row"><td>Total</td><td class="odr-num">{{ number_format($summary['total_repayments']) }} RWF</td></tr>
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Expenses by category (EXT_02) --}}
@if(count($summary['expenses_by_category']) > 0)
@php $odrShowBankCol = collect($summary['expenses_by_category'])->sum('bank') > 0; @endphp
<div class="odr-table-wrap odr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Expenses by category</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Category</th>
                <th style="text-align:right">Items</th>
                <th style="text-align:right">Cash</th>
                <th style="text-align:right">MoMo</th>
                @if($odrShowBankCol)<th style="text-align:right">Bank</th>@endif
                <th style="text-align:right">Total</th>
                <th style="text-align:right">Share</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['expenses_by_category'] as $row)
            <tr>
                <td>{{ $row->category }}</td>
                <td class="odr-num">{{ number_format($row->count) }}</td>
                <td class="odr-num">{{ number_format($row->cash) }}</td>
                <td class="odr-num">{{ number_format($row->momo) }}</td>
                @if($odrShowBankCol)<td class="odr-num">{{ number_format($row->bank) }}</td>@endif
                <td class="odr-num" style="color:var(--red)">{{ number_format($row->total) }}</td>
                <td class="odr-num" style="color:var(--text-dim)">{{ number_format($summary['total_expenses'] > 0 ? ($row->total / $summary['total_expenses']) * 100 : 0, 1) }}%</td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td>Total</td>
                <td class="odr-num">{{ number_format(collect($summary['expenses_by_category'])->sum('count')) }}</td>
                <td class="odr-num">{{ number_format($summary['total_expenses_cash']) }}</td>
                <td class="odr-num">{{ number_format($summary['total_expenses_momo']) }}</td>
                @if($odrShowBankCol)<td class="odr-num">{{ number_format($summary['total_expenses_bank']) }}</td>@endif
                <td class="odr-num">{{ number_format($summary['total_expenses']) }}</td>
                <td class="odr-num">100.0%</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Owner withdrawals (EXT_02) --}}
@if($summary['total_withdrawals'] > 0)
<div class="odr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Owner withdrawals</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead><tr><th>Method</th><th style="text-align:right">Amount</th></tr></thead>
        <tbody>
            <tr><td>Cash</td><td class="odr-num">{{ number_format($summary['total_withdrawals_cash']) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></td></tr>
            <tr><td>MoMo</td><td class="odr-num">{{ number_format($summary['total_withdrawals_momo']) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></td></tr>
            <tr class="odr-total-row"><td>Total</td><td class="odr-num">{{ number_format($summary['total_withdrawals']) }} RWF</td></tr>
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Cash deposited to bank (EXT_02) --}}
@if($summary['total_bank_deposits'] > 0)
<div class="odr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Cash deposited to bank</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead><tr><th>Source</th><th style="text-align:right">Amount</th></tr></thead>
        <tbody>
            <tr><td>From cash</td><td class="odr-num">{{ number_format($summary['bank_deposits_from_cash']) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></td></tr>
            <tr><td>From MoMo</td><td class="odr-num">{{ number_format($summary['bank_deposits_from_momo']) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></td></tr>
            <tr class="odr-total-row"><td>Total</td><td class="odr-num">{{ number_format($summary['total_bank_deposits']) }} RWF</td></tr>
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Refunds (EXT_02) --}}
@if($summary['total_refunds'] > 0)
<div class="odr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Refunds</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead><tr><th>Method</th><th style="text-align:right">Amount</th></tr></thead>
        <tbody>
            <tr><td>Cash</td><td class="odr-num">{{ number_format($summary['total_refunds_cash']) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></td></tr>
            @if($summary['total_refunds'] > $summary['total_refunds_cash'])
            <tr><td>Other methods</td><td class="odr-num">{{ number_format($summary['total_refunds'] - $summary['total_refunds_cash']) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></td></tr>
            @endif
            <tr class="odr-total-row"><td>Total</td><td class="odr-num">{{ number_format($summary['total_refunds']) }} RWF</td></tr>
        </tbody>
    </table>
    </div>
</div>
@endif

@endif

@if($viewMode === 'transactions')
{{-- Payment method breakdowns — one per channel, only when it has data --}}
@foreach($paymentByCustomerTables as $table)
    @if(count($table['data']) > 0)
    <div class="odr-table-wrap">
        <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
            <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $table['label'] }}</div>
        </div>
        <div class="odr-table-scroll">
        <table class="odr-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th style="text-align:right">Sales</th>
                    <th style="text-align:right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($table['data'] as $row)
                <tr>
                    <td>{{ $row->customer_name }}</td>
                    <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->sales_count) }}</td>
                    <td style="text-align:right;white-space:nowrap">
                        <span style="font-family:var(--mono)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                    </td>
                </tr>
                @endforeach
                <tr class="odr-total-row">
                    <td>Total</td>
                    <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($table['data'])->sum('sales_count')) }}</td>
                    <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($table['data'])->sum('amount')) }} RWF</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
    @endif
@endforeach

{{-- Detailed Credits (Amadeni — new credit issued) --}}
@if(count($summary['credits_by_customer']) > 0)
<div class="odr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Detailed Credits — by Customer</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th style="text-align:right">Sales</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['credits_by_customer'] as $row)
            <tr>
                <td>{{ $row->customer_name }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->sales_count) }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono);color:var(--amber)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td>Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($summary['credits_by_customer'])->sum('sales_count')) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_sales_credit']) }} RWF</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Credit Repayments (ABISHYUVE — repayments received) --}}
@if(count($summary['repayments_by_customer']) > 0)
<div class="odr-table-wrap">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Credit Repayments — by Customer</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th style="text-align:right">Repayments</th>
                <th style="text-align:right">Cash</th>
                <th style="text-align:right">MoMo</th>
                <th style="text-align:right">Bank</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['repayments_by_customer'] as $row)
            <tr>
                <td>{{ $row->customer_name }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($row->repayment_count) }}</td>
                <td class="odr-num">{{ $row->cash ? number_format($row->cash) : '—' }}</td>
                <td class="odr-num">{{ $row->momo ? number_format($row->momo) : '—' }}</td>
                <td class="odr-num">{{ $row->bank ? number_format($row->bank) : '—' }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono);color:var(--green)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td>Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format(collect($summary['repayments_by_customer'])->sum('repayment_count')) }}</td>
                <td class="odr-num">{{ number_format($summary['total_repayments_cash']) }}</td>
                <td class="odr-num">{{ number_format($summary['total_repayments_momo']) }}</td>
                <td class="odr-num">{{ number_format($summary['total_repayments_bank']) }}</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_repayments']) }} RWF</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Bank Deposits — cash/MoMo physically moved into the bank account. Already
     reflected in Cash Register's Bank Movement net figure; this is the
     itemized detail behind that number. --}}
@if(count($summary['deposits_detailed']) > 0)
<div class="odr-table-wrap odr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Bank Deposits</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Date</th>
                @if($isAllShops)<th>Shop</th>@endif
                <th>Source</th>
                <th>Reference</th>
                <th>Notes</th>
                <th style="text-align:right">Amount</th>
                <th>Deposited by</th>
            </tr>
        </thead>
        <tbody>
            @php
                $odrDepositSourceLabels = ['cash' => 'Cash', 'mobile_money' => 'MoMo'];
            @endphp
            @foreach($summary['deposits_detailed'] as $row)
            <tr>
                <td style="color:var(--text-dim)">{{ \Carbon\Carbon::parse($row->session_date)->format('d M Y') }}</td>
                @if($isAllShops)<td style="color:var(--text-dim)">{{ $row->shop_name }}</td>@endif
                <td>{{ $odrDepositSourceLabels[$row->source] ?? ucfirst($row->source) }}</td>
                <td style="color:var(--text-dim)">{{ $row->bank_reference ?: '—' }}</td>
                <td style="color:var(--text-dim)">{{ $row->notes ?: '—' }}</td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono);color:var(--accent)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
                <td style="color:var(--text-dim)">{{ $row->deposited_by_name ?: '—' }}</td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td colspan="{{ $isAllShops ? 5 : 4 }}">Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_deposits']) }} RWF</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Detailed Expenses --}}
<div class="odr-table-wrap odr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Detailed Expenses</div>
    </div>
    @if(count($summary['expenses_detailed']) > 0)
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Date</th>
                @if($isAllShops)<th>Shop</th>@endif
                <th>Category</th>
                <th>Description</th>
                <th>Payment Method</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $odrPayMethodLabels = ['cash' => 'Cash', 'mobile_money' => 'MoMo', 'bank_transfer' => 'Bank', 'other' => 'Other'];
            @endphp
            @foreach($summary['expenses_detailed'] as $row)
            <tr>
                <td style="color:var(--text-dim)">{{ \Carbon\Carbon::parse($row->session_date)->format('d M Y') }}</td>
                @if($isAllShops)<td style="color:var(--text-dim)">{{ $row->shop_name }}</td>@endif
                <td>{{ $row->category }}</td>
                <td style="color:var(--text-dim)">{{ $row->description ?: '—' }}</td>
                <td><span class="odr-pill odr-pill-dim">{{ $odrPayMethodLabels[$row->payment_method] ?? ucfirst($row->payment_method) }}</span></td>
                <td style="text-align:right;white-space:nowrap">
                    <span style="font-family:var(--mono);color:var(--red)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span>
                </td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td colspan="{{ $isAllShops ? 5 : 4 }}">Total</td>
                <td style="text-align:right;font-family:var(--mono)">{{ number_format($summary['total_expenses']) }} RWF</td>
            </tr>
        </tbody>
    </table>
    </div>
    @else
    <div class="odr-empty">
        <div class="odr-empty-title">No expenses in this period</div>
        <div class="odr-empty-sub">Try a different date range.</div>
    </div>
    @endif
</div>
{{-- Owner withdrawals — itemized (EXT_02) --}}
@if(count($summary['withdrawals_detailed']) > 0)
<div class="odr-table-wrap odr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Owner Withdrawals</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Date</th>
                @if($isAllShops)<th>Shop</th>@endif
                <th>Reason</th>
                <th>Method</th>
                <th style="text-align:right">Amount</th>
                <th>Recorded by</th>
            </tr>
        </thead>
        <tbody>
            @php $odrWdLabels = ['cash' => 'Cash', 'mobile_money' => 'MoMo']; @endphp
            @foreach($summary['withdrawals_detailed'] as $row)
            <tr>
                <td style="color:var(--text-dim)">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                @if($isAllShops)<td style="color:var(--text-dim)">{{ $row->shop_name }}</td>@endif
                <td>{{ $row->reason ?: '—' }}</td>
                <td><span class="odr-pill odr-pill-dim">{{ $odrWdLabels[(string) $row->method] ?? ucfirst((string) $row->method) }}</span></td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);color:var(--red)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span></td>
                <td style="color:var(--text-dim)">{{ $row->recorded_by_name ?: '—' }}</td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td colspan="{{ $isAllShops ? 4 : 3 }}">Total</td>
                <td class="odr-num">{{ number_format($summary['total_withdrawals']) }} RWF</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Refunds — itemized (EXT_02) --}}
@if(count($summary['refunds_detailed']) > 0)
<div class="odr-table-wrap odr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Refunds</div>
    </div>
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Date</th>
                @if($isAllShops)<th>Shop</th>@endif
                <th>Return #</th>
                <th>Customer</th>
                <th>Method</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $odrRfLabels = ['cash' => 'Cash', 'mobile_money' => 'MoMo', 'bank_transfer' => 'Bank']; @endphp
            @foreach($summary['refunds_detailed'] as $row)
            <tr>
                <td style="color:var(--text-dim)">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                @if($isAllShops)<td style="color:var(--text-dim)">{{ $row->shop_name }}</td>@endif
                <td style="font-family:var(--mono)">{{ $row->return_number }}</td>
                <td>{{ $row->customer_name ?: '—' }}</td>
                <td>@if($row->method)<span class="odr-pill odr-pill-dim">{{ $odrRfLabels[$row->method] ?? ucfirst($row->method) }}</span>@else — @endif</td>
                <td style="text-align:right;white-space:nowrap"><span style="font-family:var(--mono);color:var(--red)">{{ number_format($row->amount) }} <span style="font-size:10px;color:var(--text-dim)">RWF</span></span></td>
            </tr>
            @endforeach
            <tr class="odr-total-row">
                <td colspan="{{ $isAllShops ? 5 : 4 }}">Total</td>
                <td class="odr-num">{{ number_format($summary['total_refunds']) }} RWF</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>
@endif
@endif

{{-- Checks (EXT_02) — always rendered; empty state when everything passes --}}
<div class="odr-table-wrap odr-span-2">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <div style="font-size:13px;font-weight:700;color:var(--text)">Checks</div>
    </div>
    @if(count($checks) > 0)
    @php
        $odrSevOrder  = ['critical' => 0, 'warning' => 1, 'info' => 2];
        $odrSevSorted = collect($checks)->sortBy(fn ($c) => $odrSevOrder[$c['severity']] ?? 3)->values();
        $odrSevPill   = ['critical' => ['red', 'Critical'], 'warning' => ['amber', 'Warning'], 'info' => ['dim', 'Info']];
    @endphp
    <div class="odr-table-scroll">
    <table class="odr-table">
        <thead>
            <tr>
                <th>Severity</th>
                <th>Message</th>
                @if($isAllShops)<th>Shop</th>@endif
                <th>Date</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($odrSevSorted as $c)
            <tr>
                <td><span class="odr-pill odr-pill-{{ $odrSevPill[$c['severity']][0] ?? 'dim' }}">{{ $odrSevPill[$c['severity']][1] ?? ucfirst($c['severity']) }}</span></td>
                <td class="odr-msg">{{ $c['message'] }}</td>
                @if($isAllShops)<td style="color:var(--text-dim)">{{ $c['shop_name'] ?? '' }}</td>@endif
                <td style="color:var(--text-dim);white-space:nowrap">{{ !empty($c['date']) ? \Carbon\Carbon::parse($c['date'])->format('d M Y') : '' }}</td>
                <td class="odr-num">@if($c['amount'] !== null){{ ($c['amount'] > 0 ? '+' : '') . number_format($c['amount']) }}@endif</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @else
    <div class="odr-empty">
        <div class="odr-empty-title">All checks passed for this period</div>
    </div>
    @endif
</div>

</div>
</div>
