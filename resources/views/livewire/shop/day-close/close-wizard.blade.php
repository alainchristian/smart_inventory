<div class="cw-page" style="font-family:var(--font)">
<style>
.cw-page { padding:0 0 80px; }

/* Header */
.cw-header { display:flex;align-items:flex-start;gap:12px;margin-bottom:20px; }
.cw-back   { width:36px;height:36px;border-radius:var(--rsm);display:flex;align-items:center;justify-content:center;flex-shrink:0;
             background:var(--surface);color:var(--text-sub);box-shadow:var(--shadow-card);text-decoration:none;transition:all var(--tr); }
.cw-back:hover { box-shadow:var(--shadow-card-hover);color:var(--text); }
.cw-title  { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
.cw-sub    { font-size:13px;color:var(--text-dim);margin:0; }
.cw-badge  { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap; }

/* Stepper */
.cw-steps  { display:flex;align-items:center;gap:8px;background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);padding:14px 20px;margin-bottom:20px; }
.cw-step   { display:flex;align-items:center;gap:8px;border:none;background:transparent;padding:0;font-family:var(--font);cursor:default;flex-shrink:0; }
.cw-step.done { cursor:pointer; }
.cw-step-n { width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;
             font-family:var(--mono);flex-shrink:0;border:1.5px solid var(--border);color:var(--text-dim);background:var(--surface);transition:all var(--tr); }
.cw-step.current .cw-step-n { background:var(--accent);border-color:var(--accent);color:#fff; }
.cw-step.done .cw-step-n    { background:var(--green-dim);border-color:var(--green);color:var(--green); }
.cw-step-l { font-size:13px;font-weight:600;color:var(--text-dim);white-space:nowrap; }
.cw-step.current .cw-step-l { color:var(--text); }
.cw-step.done:hover .cw-step-l { color:var(--accent); }
.cw-line   { flex:1;height:1.5px;background:var(--border);min-width:12px; }
.cw-line.done { background:var(--green); }

/* Layout */
.cw-grid  { display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:20px;align-items:start; }
.cw-main  { display:flex;flex-direction:column;gap:16px;min-width:0; }
.cw-rail  { position:sticky;top:calc(var(--topbar-height) + 16px); }

/* Cards */
.cw-card       { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0; }
.cw-card-head  { padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px; }
.cw-card-title { font-size:13px;font-weight:700;color:var(--text);margin:0; }
.cw-card-sub   { font-size:12px;color:var(--text-dim);margin-top:2px; }
.cw-card-body  { padding:18px 20px; }
.cw-section-hd { padding:10px 20px 6px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--accent);border-top:1px solid var(--border); }

/* Rows (ledger + summary) */
.cw-row    { display:flex;justify-content:space-between;align-items:center;gap:12px;padding:9px 20px;border-bottom:1px solid var(--border);font-size:13px; }
.cw-row:last-child { border-bottom:none; }
.cw-row-l  { color:var(--text-sub);display:flex;align-items:center;gap:8px;min-width:0; }
.cw-row-v  { font-family:var(--mono);font-weight:600;white-space:nowrap;color:var(--text); }
.cw-total  { display:flex;justify-content:space-between;align-items:baseline;gap:12px;padding:14px 20px;border-top:2px solid var(--border); }
.cw-total-l { font-size:13px;font-weight:700;color:var(--text); }
.cw-total-v { font-size:20px;font-weight:800;font-family:var(--mono);letter-spacing:-.5px;white-space:nowrap; }
.cw-unit   { font-size:11px;font-weight:500;color:var(--text-dim);margin-left:3px;letter-spacing:0; }
.cw-dot    { width:8px;height:8px;border-radius:50%;flex-shrink:0; }

/* Step 1 */
.cw-bar      { display:flex;height:8px;border-radius:4px;overflow:hidden;background:var(--surface2);gap:2px; }
.cw-bar > div { height:100%; }
.cw-big      { font-size:26px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;color:var(--text);line-height:1;white-space:nowrap; }
.cw-pct      { font-size:12px;color:var(--text-dim);font-family:var(--mono);width:44px;text-align:right;display:inline-block; }

/* Pill tabs (step 2) */
.cw-tabs     { display:flex;gap:4px;flex-wrap:wrap; }
.cw-tab      { display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:9px;border:1.5px solid var(--border);cursor:pointer;font-size:13px;
               font-weight:600;font-family:var(--font);background:var(--surface);color:var(--text-dim);transition:all var(--tr);white-space:nowrap; }
.cw-tab:hover  { border-color:var(--accent);color:var(--accent); }
.cw-tab.active { background:var(--accent);border-color:var(--accent);color:#fff; }
.cw-tab-count  { font-size:11px;font-weight:700;padding:1px 7px;border-radius:20px;background:rgba(255,255,255,.22);font-family:var(--mono); }
.cw-tab:not(.active) .cw-tab-count { background:var(--surface2);color:var(--text-dim); }

/* Buttons */
.cw-btn         { padding:9px 18px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);
                  display:inline-flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap;text-decoration:none;line-height:1.2; }
.cw-btn-primary { background:var(--accent);color:#fff;border:1px solid var(--accent);box-shadow:0 3px 10px rgba(59,111,212,.25); }
.cw-btn-primary:hover { opacity:.88; }
.cw-btn-primary:disabled { opacity:.5;cursor:not-allowed; }
.cw-btn-ghost   { background:var(--surface);color:var(--text-sub);border:1px solid var(--border); }
.cw-btn-ghost:hover { background:var(--surface2);color:var(--text); }
.cw-btn-sm      { padding:5px 11px;font-size:12px; }
.cw-nav         { display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:4px; }

/* Inputs */
.cw-label  { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px; }
.cw-label em { font-style:normal;font-weight:500;color:var(--text-dim); }
.cw-input  { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);
             outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr); }
.cw-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.cw-money  { position:relative; }
.cw-money .cw-input { font-family:var(--mono);font-weight:700;text-align:right;padding-right:52px;-moz-appearance:textfield; }
.cw-money-u { position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--text-dim);pointer-events:none; }
.cw-num    { padding:7px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;background:var(--surface);color:var(--text);outline:none;
             box-sizing:border-box;font-family:var(--mono);text-align:right;width:100%;-moz-appearance:textfield;transition:border-color var(--tr); }
.cw-num:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.cw-error  { font-size:12px;color:var(--red);margin-top:6px; }
.cw-hint   { font-size:12px;color:var(--text-dim);margin-top:6px;line-height:1.5; }

/* Status line (variance) */
.cw-status   { margin-top:16px;padding:12px 14px;border-radius:var(--rsm);border:1px solid var(--border);border-left-width:3px;display:flex;justify-content:space-between;align-items:center;gap:12px; }
.cw-status-t { font-size:13px;font-weight:700; }
.cw-status-s { font-size:12px;color:var(--text-dim);margin-top:2px; }
.cw-status-v { font-size:16px;font-weight:800;font-family:var(--mono);white-space:nowrap; }

/* Settlement table (step 4) */
.cw-table { width:100%;border-collapse:collapse; }
.cw-table thead tr { border-bottom:2px solid var(--border); }
.cw-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap; }
.cw-table td { padding:10px 16px;font-size:13px;vertical-align:middle;border-bottom:1px solid var(--border);color:var(--text-sub); }
.cw-table tbody tr:last-child td { border-bottom:none; }

/* Rail */
.cw-var-pill { display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;padding:3px 9px;border-radius:6px;font-family:var(--mono);white-space:nowrap; }

/* Mobile bottom bar */
.cw-mbar { display:none; }

/* Modals */
.cw-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;padding:16px; }
.cw-modal   { background:var(--surface);border-radius:var(--r);box-shadow:0 24px 60px rgba(26,31,54,.25);width:100%;max-width:440px;max-height:calc(100vh - 32px);display:flex;flex-direction:column; }
.cw-modal-head  { padding:20px 22px 0;flex-shrink:0; }
.cw-modal-title { font-size:16px;font-weight:800;color:var(--text);margin:0; }
.cw-modal-sub   { font-size:13px;color:var(--text-dim);margin:4px 0 0;line-height:1.5; }
.cw-modal-body  { padding:14px 0;overflow-y:auto; }
.cw-modal-foot  { padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px;flex-shrink:0; }
.cw-modal .cw-row { padding-left:22px;padding-right:22px; }
.cw-den-grid { display:grid;grid-template-columns:72px 16px minmax(0,1fr) 110px;gap:8px 10px;align-items:center;padding:0 22px; }
.cw-den-v    { font-family:var(--mono);font-weight:700;color:var(--text);font-size:14px;text-align:right; }
.cw-den-x    { color:var(--text-dim);font-size:12px;text-align:center; }
.cw-den-s    { font-family:var(--mono);font-size:13px;color:var(--text-sub);text-align:right;white-space:nowrap; }
.cw-den-hd   { grid-column:1 / -1;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--accent);padding-top:6px; }

@keyframes cw-spin { to { transform:rotate(360deg) } }

@media (max-width:1100px) {
    .cw-grid { grid-template-columns:1fr; }
    .cw-rail { display:none; }
    .cw-page { padding-bottom:96px; }
    .cw-mbar { display:grid;grid-template-columns:repeat(3,1fr);position:fixed;left:0;right:0;bottom:0;z-index:20;background:var(--surface);
               border-top:2px solid var(--border);box-shadow:0 -4px 16px rgba(26,31,54,.08); }
    .cw-mbar > div { padding:10px 16px;border-right:1px solid var(--border);min-width:0; }
    .cw-mbar > div:last-child { border-right:none; }
    .cw-mbar-l { font-size:11px;color:var(--text-dim); }
    .cw-mbar-v { font-size:14px;font-weight:800;font-family:var(--mono);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
}
@media (max-width:768px) {
    .cw-count-grid { grid-template-columns:1fr !important; }
    .cw-title { font-size:19px; }
}
@media (max-width:640px) {
    .cw-step { min-height:0 !important;min-width:0 !important;padding:0 !important; }
    .cw-btn-sm { min-height:32px !important;padding:5px 11px !important; }
    .cw-tab { min-height:36px !important;padding:7px 12px !important; }
    .cw-back { min-height:0 !important;min-width:0 !important;padding:0 !important; }
    .cw-table td { padding:0 !important; }
}
@media (max-width:600px) {
    .cw-steps { padding:12px 14px;gap:6px; }
    .cw-step:not(.current) .cw-step-l { display:none; }
    .cw-card-head, .cw-card-body, .cw-row, .cw-total, .cw-section-hd { padding-left:14px;padding-right:14px; }
    .cw-nav .cw-btn { flex:1; }
    .cw-table thead { display:none; }
    .cw-table, .cw-table tbody { display:block; }
    .cw-table tbody tr { display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:12px 14px;border-bottom:1px solid var(--border); }
    .cw-table td { padding:0;border:none; }
    .cw-table td.cw-nc-name { grid-column:1 / -1;display:flex;justify-content:space-between; }
    .cw-den-grid { grid-template-columns:60px 12px minmax(0,1fr) 90px;padding:0 16px; }
}
</style>

@php
    $s          = $summary;
    $expected   = (int) ($s['expected_cash'] ?? 0);
    $hasCount   = $actualCashCounted !== '';
    $counted    = (int) $actualCashCounted;
    $variance   = $counted - $expected;
    $toOwner    = (int) $cashToOwnerMomo;
    $retained   = max(0, $counted - $toOwner);
    $vColor     = ! $hasCount ? 'var(--text-dim)' : ($variance === 0 ? 'var(--green)' : ($variance > 0 ? 'var(--amber)' : 'var(--red)'));
    $vDim       = ! $hasCount ? 'var(--surface2)' : ($variance === 0 ? 'var(--green-dim)' : ($variance > 0 ? 'var(--amber-dim)' : 'var(--red-dim)'));
    $vText      = ! $hasCount ? '—' : (($variance > 0 ? '+' : ($variance < 0 ? '−' : '')) . number_format(abs($variance)));
    $isToday    = $session && $session->session_date->isSameDay(business_today());
    $dateLabel  = $session ? $session->session_date->format('l, d M Y') : '';

    $cardAmt = $s['total_sales_card'] ?? 0;
    $bankAmt = $s['total_sales_bank_transfer'] ?? 0;
    $channels = array_values(array_filter([
        ['Cash',          $s['total_sales_cash'] ?? 0,   'var(--green)',    true],
        ['Mobile money',  $s['total_sales_momo'] ?? 0,   'var(--accent)',   true],
        ['Card',          $cardAmt,                      'var(--text-sub)', $settingAllowCard || $cardAmt > 0],
        ['Bank transfer', $bankAmt,                      'var(--violet)',   $settingAllowBankTransfer || $bankAmt > 0],
        ['Credit',        $s['total_sales_credit'] ?? 0, 'var(--amber)',    true],
        ['Other',         $s['total_sales_other'] ?? 0,  'var(--text-dim)', ($s['total_sales_other'] ?? 0) > 0],
    ], fn ($c) => $c[3]));

    $ledger = [
        ['Cash sales',          $s['total_sales_cash'] ?? 0,        '+', 'var(--green)'],
        ['Cash repayments',     $s['total_repayments_cash'] ?? 0,   '+', 'var(--green)'],
        ['Cash refunds',        $s['total_refunds_cash'] ?? 0,      '−', 'var(--red)'],
        ['Cash expenses',       $s['total_expenses_cash'] ?? 0,     '−', 'var(--red)'],
        ['Owner withdrawals',   $s['total_withdrawals_cash'] ?? 0,  '−', 'var(--red)'],
        ['Deposited to bank',   $s['cash_deposits'] ?? 0,           '−', 'var(--red)'],
    ];

    $ncChannels = array_values(array_filter([
        ['Mobile money',  'momoSettled',         'momoSettledRef',         $s['total_sales_momo'] ?? 0,  'var(--accent)',   true],
        ['Card',          'cardSettled',         'cardSettledRef',         $cardAmt,                     'var(--text-sub)', $settingAllowCard || $cardAmt > 0],
        ['Bank transfer', 'bankTransferSettled', 'bankTransferSettledRef', $bankAmt,                     'var(--violet)',   $settingAllowBankTransfer || $bankAmt > 0],
        ['Other',         'otherSettled',        'otherSettledRef',        $s['total_sales_other'] ?? 0, 'var(--text-dim)', true],
    ], fn ($c) => $c[5] && $c[3] > 0));
    $creditSales  = $s['total_sales_credit'] ?? 0;
    $nonCashSales = ($s['total_sales_momo'] ?? 0) + $cardAmt + $bankAmt + ($s['total_sales_other'] ?? 0);
    $settledTotal = (int) $momoSettled + (int) $cardSettled + (int) $bankTransferSettled + (int) $otherSettled;

    $steps = [1 => 'Sales', 2 => 'Movements', 3 => 'Cash count', 4 => 'Confirm'];
@endphp

{{-- ── Header ── --}}
<div class="cw-header">
    <a href="{{ route('shop.day-close.index') }}" class="cw-back" aria-label="Back to register">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div style="min-width:0">
        <h1 class="cw-title">
            Close register
            @if ($session && ! $isToday)
                <span class="cw-badge" style="background:var(--amber-dim);color:var(--amber)">Previous day</span>
            @endif
        </h1>
        <p class="cw-sub">
            {{ $dateLabel }}
            @if ($session?->opened_at) · Opened {{ local_time($session->opened_at)->format('H:i') }}@if($session->openedBy) by {{ $session->openedBy->name }}@endif @endif
        </p>
    </div>
</div>

{{-- ── Stepper ── --}}
<nav class="cw-steps" aria-label="Progress">
    @foreach ($steps as $n => $label)
        @php $state = $n < $currentStep ? 'done' : ($n === $currentStep ? 'current' : ''); @endphp
        <button type="button" class="cw-step {{ $state }}"
                @if ($state === 'done') wire:click="goToStep({{ $n }})" title="Back to {{ $label }}" @else disabled @endif
                @if ($state === 'current') aria-current="step" @endif>
            <span class="cw-step-n">
                @if ($state === 'done')
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                @else
                    {{ $n }}
                @endif
            </span>
            <span class="cw-step-l">{{ $label }}</span>
        </button>
        @if ($n < 4)<span class="cw-line {{ $n < $currentStep ? 'done' : '' }}"></span>@endif
    @endforeach
</nav>

<div class="cw-grid">
<div class="cw-main">

{{-- ════════ STEP 1 — Sales ════════ --}}
@if ($currentStep === 1)
    @php $total = max(1, $s['total_sales'] ?? 0); @endphp
    <div class="cw-card">
        <div class="cw-card-head">
            <div>
                <div class="cw-card-title">Sales review</div>
                <div class="cw-card-sub">{{ $s['transaction_count'] ?? 0 }} {{ ($s['transaction_count'] ?? 0) === 1 ? 'transaction' : 'transactions' }} by payment channel</div>
            </div>
        </div>
        <div class="cw-card-body">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;margin-bottom:14px;flex-wrap:wrap">
                <div>
                    <div style="font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px">Total sales</div>
                    <div class="cw-big">{{ number_format($s['total_sales'] ?? 0) }}<span class="cw-unit" style="font-size:13px">RWF</span></div>
                </div>
            </div>
            @if (($s['total_sales'] ?? 0) > 0)
                <div class="cw-bar" role="img" aria-label="Sales split by channel">
                    @foreach ($channels as $__c)
                        @if ($__c[1] > 0)
                            <div style="width:{{ round($__c[1] / $total * 100, 2) }}%;background:{{ $__c[2] }}" title="{{ $__c[0] }}"></div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
        <div style="border-top:1px solid var(--border)">
            @foreach ($channels as $__c)
                @php [$cName, $cAmt, $cColor] = $__c; @endphp
                <div class="cw-row">
                    <span class="cw-row-l"><span class="cw-dot" style="background:{{ $cColor }}"></span>{{ $cName }}</span>
                    <span>
                        <span class="cw-row-v" style="{{ $cAmt > 0 ? '' : 'color:var(--text-dim)' }}">{{ number_format($cAmt) }}</span>
                        <span class="cw-pct">{{ ($s['total_sales'] ?? 0) > 0 ? round($cAmt / $total * 100) : 0 }}%</span>
                    </span>
                </div>
            @endforeach
        </div>

        @php
            $refunds    = $s['total_refunds_cash'] ?? 0;
            $repayments = $s['total_repayments'] ?? 0;
        @endphp
        @if ($refunds > 0 || $repayments > 0)
            <div class="cw-section-hd">Not counted in sales</div>
            @if ($repayments > 0)
                <div class="cw-row">
                    <span class="cw-row-l">Credit repayments received</span>
                    <span class="cw-row-v" style="color:var(--green)">+{{ number_format($repayments) }}</span>
                </div>
                @foreach ([['Cash', $s['total_repayments_cash'] ?? 0], ['Mobile money', $s['total_repayments_momo'] ?? 0], ['Bank / card', $s['total_repayments_bank'] ?? 0]] as $__r)
                    @if ($__r[1] > 0)
                        <div class="cw-row" style="padding-left:36px;font-size:12px">
                            <span class="cw-row-l" style="color:var(--text-dim)">{{ $__r[0] }}</span>
                            <span class="cw-row-v" style="font-weight:500;color:var(--text-sub)">{{ number_format($__r[1]) }}</span>
                        </div>
                    @endif
                @endforeach
            @endif
            @if ($refunds > 0)
                <div class="cw-row">
                    <span class="cw-row-l">Cash refunds paid out</span>
                    <span class="cw-row-v" style="color:var(--red)">−{{ number_format($refunds) }}</span>
                </div>
            @endif
        @endif
    </div>
@endif

{{-- ════════ STEP 2 — Movements ════════ --}}
@if ($currentStep === 2)
    @php
        $tabs = [
            'expense'    => ['Expenses',      $s['expense_count'] ?? 0,      $s['total_expenses'] ?? 0,      'Operational costs paid during the day'],
            'withdrawal' => ['Withdrawals',   $s['withdrawal_count'] ?? 0,   $s['total_withdrawals'] ?? 0,   'Cash or MoMo taken by the owner'],
            'deposit'    => ['Bank deposits', $s['bank_deposit_count'] ?? 0, $s['total_bank_deposits'] ?? 0, 'Money moved into the bank'],
        ];
    @endphp
    <div x-data="{ tab: 'expense' }" style="display:flex;flex-direction:column;gap:12px">
        <div class="cw-tabs" role="tablist">
            @foreach ($tabs as $key => $__t)
                <button type="button" role="tab" class="cw-tab" :class="{ active: tab === '{{ $key }}' }" @click="tab = '{{ $key }}'">
                    {{ $__t[0] }} <span class="cw-tab-count">{{ $__t[1] }}</span>
                </button>
            @endforeach
        </div>

        @foreach ($tabs as $key => $__t)
            <div class="cw-card" x-show="tab === '{{ $key }}'" @if ($key !== 'expense') x-cloak @endif>
                <div class="cw-card-head">
                    <div>
                        <div class="cw-card-title">{{ $__t[0] }} · <span style="font-family:var(--mono)">{{ number_format($__t[2]) }}</span> <span style="font-size:11px;font-weight:500;color:var(--text-dim)">RWF</span></div>
                        <div class="cw-card-sub">{{ $__t[3] }}</div>
                    </div>
                    <button type="button" class="cw-btn cw-btn-ghost cw-btn-sm" @click="$dispatch('dc-record', { type: '{{ $key }}' })">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                        Add
                    </button>
                </div>
                @if ($key === 'expense')
                    <livewire:shop.day-close.expense-list :dailySessionId="$dailySessionId" :key="'cw-el-'.$dailySessionId" />
                @elseif ($key === 'withdrawal')
                    <livewire:shop.day-close.withdrawal-list :dailySessionId="$dailySessionId" :key="'cw-wl-'.$dailySessionId" />
                @else
                    <livewire:shop.day-close.deposit-list :dailySessionId="$dailySessionId" :key="'cw-dl-'.$dailySessionId" />
                @endif
            </div>
        @endforeach
        <p class="cw-hint" style="margin:0">Nothing to add? Continue — these can still be recorded later from the register until the day is closed.</p>
    </div>
@endif

{{-- ════════ STEP 3 — Cash count ════════ --}}
@if ($currentStep === 3)
    <div class="cw-count-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start">
        <div class="cw-card">
            <div class="cw-card-head">
                <div><div class="cw-card-title">Expected cash</div><div class="cw-card-sub">What the drawer should hold</div></div>
            </div>
            <div>
                <div class="cw-row"><span class="cw-row-l">Opening balance</span><span class="cw-row-v">{{ number_format($s['opening_balance'] ?? 0) }}</span></div>
                @foreach ($ledger as $__l)
                    @if ($__l[1] > 0)
                        <div class="cw-row"><span class="cw-row-l">{{ $__l[0] }}</span><span class="cw-row-v" style="color:{{ $__l[3] }}">{{ $__l[2] }}{{ number_format($__l[1]) }}</span></div>
                    @endif
                @endforeach
            </div>
            <div class="cw-total">
                <span class="cw-total-l">Expected in drawer</span>
                <span class="cw-total-v" style="color:var(--green)">{{ number_format($expected) }}<span class="cw-unit">RWF</span></span>
            </div>
        </div>

        <div class="cw-card">
            <div class="cw-card-head">
                <div><div class="cw-card-title">Count the drawer</div><div class="cw-card-sub">Total physical cash right now</div></div>
                <button type="button" class="cw-btn cw-btn-ghost cw-btn-sm" @click="$dispatch('cw-denoms')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    By denomination
                </button>
            </div>
            <div class="cw-card-body">
                <label class="cw-label" for="cw-counted">Cash counted</label>
                <div class="cw-money">
                    <input id="cw-counted" type="number" min="0" inputmode="numeric" class="cw-input" style="font-size:22px;padding-top:12px;padding-bottom:12px"
                           wire:model.live.debounce.300ms="actualCashCounted" placeholder="0">
                    <span class="cw-money-u">RWF</span>
                </div>
                @error('actualCashCounted') <div class="cw-error">Enter the cash you counted (0 or more).</div> @enderror

                @if (! $hasCount)
                    <div class="cw-status" style="border-left-color:var(--border)">
                        <div><div class="cw-status-s" style="margin:0">Enter the count to see if the drawer balances.</div></div>
                    </div>
                @elseif ($variance === 0)
                    <div class="cw-status" style="border-left-color:var(--green)">
                        <div><div class="cw-status-t" style="color:var(--green)">Balanced</div><div class="cw-status-s">The drawer matches the expected cash.</div></div>
                        <div class="cw-status-v" style="color:var(--green)">0</div>
                    </div>
                @elseif ($variance > 0)
                    <div class="cw-status" style="border-left-color:var(--amber)">
                        <div><div class="cw-status-t" style="color:var(--amber)">Surplus</div><div class="cw-status-s">Extra cash stays in the drawer.</div></div>
                        <div class="cw-status-v" style="color:var(--amber)">+{{ number_format($variance) }}</div>
                    </div>
                @else
                    <div class="cw-status" style="border-left-color:var(--red)">
                        <div><div class="cw-status-t" style="color:var(--red)">Short</div><div class="cw-status-s">Recorded automatically as a cash-shortage expense.</div></div>
                        <div class="cw-status-v" style="color:var(--red)">−{{ number_format(abs($variance)) }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- ════════ STEP 4 — Confirm ════════ --}}
@if ($currentStep === 4)
    <div class="cw-card">
        <div class="cw-card-head">
            <div><div class="cw-card-title">Cash disposition</div><div class="cw-card-sub">Where the counted cash goes tonight</div></div>
        </div>
        <div class="cw-row"><span class="cw-row-l">Cash counted</span><span class="cw-row-v">{{ number_format($counted) }}<span class="cw-unit">RWF</span></span></div>
        <div class="cw-card-body" style="border-bottom:1px solid var(--border)">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px">
                <div>
                    <label class="cw-label" for="cw-to-owner">Send to owner via MoMo <em>(optional)</em></label>
                    <div class="cw-money">
                        <input id="cw-to-owner" type="number" min="0" inputmode="numeric" class="cw-input" wire:model.live.debounce.300ms="cashToOwnerMomo" placeholder="0">
                        <span class="cw-money-u">RWF</span>
                    </div>
                    @error('cashToOwnerMomo') <div class="cw-error">{{ $message }}</div> @enderror
                </div>
                @if ($toOwner > 0)
                    <div>
                        <label class="cw-label" for="cw-owner-ref">MoMo reference</label>
                        <input id="cw-owner-ref" type="text" class="cw-input" style="font-family:var(--mono)" wire:model.blur="ownerMomoReference" placeholder="Transaction ID">
                    </div>
                @endif
            </div>
        </div>
        <div class="cw-total">
            <span class="cw-total-l">Retained in drawer</span>
            <span class="cw-total-v" style="color:var(--text)">{{ number_format($retained) }}<span class="cw-unit">RWF</span></span>
        </div>
    </div>

    @if (count($ncChannels) || $creditSales > 0)
        <div class="cw-card">
            <div class="cw-card-head">
                <div><div class="cw-card-title">Non-cash settlement</div><div class="cw-card-sub">Confirm what each channel actually passed on to the owner</div></div>
            </div>
            @if (count($ncChannels))
                <table class="cw-table">
                    <thead>
                        <tr><th>Channel</th><th style="text-align:right">Collected</th><th style="width:150px">Settled</th><th style="width:170px">Reference</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($ncChannels as $__n)
                            @php [$nLabel, $nField, $nRef, $nTotal, $nColor] = $__n; @endphp
                            <tr wire:key="nc-{{ $nField }}">
                                <td class="cw-nc-name">
                                    <span class="cw-row-l"><span class="cw-dot" style="background:{{ $nColor }}"></span><span style="color:var(--text);font-weight:600">{{ $nLabel }}</span></span>
                                </td>
                                <td style="text-align:right" class="cw-row-v">{{ number_format($nTotal) }}</td>
                                <td><input type="number" min="0" class="cw-num" wire:model.blur="{{ $nField }}" aria-label="{{ $nLabel }} settled"></td>
                                <td><input type="text" class="cw-num" style="text-align:left;font-size:13px" wire:model.blur="{{ $nRef }}" placeholder="Txn ID" aria-label="{{ $nLabel }} reference"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if ($creditSales > 0)
                <div class="cw-row" style="border-top:1px solid var(--border)">
                    <span class="cw-row-l"><span class="cw-dot" style="background:var(--amber)"></span>Credit sales — tracked on customer accounts</span>
                    <span class="cw-row-v" style="color:var(--amber)">{{ number_format($creditSales) }}</span>
                </div>
            @endif
        </div>
    @endif

    <div class="cw-card">
        <div class="cw-card-body">
            <label class="cw-label" for="cw-notes">Closing notes <em>(optional)</em></label>
            <textarea id="cw-notes" rows="3" class="cw-input" style="resize:vertical" wire:model.blur="notes"
                      placeholder="Anything the owner should know about today — e.g. why the drawer is short"></textarea>
        </div>
    </div>
@endif

    {{-- ── Navigation ── --}}
    <div class="cw-nav">
        <div>
            @if ($currentStep > 1)
                <button type="button" class="cw-btn cw-btn-ghost" wire:click="prevStep">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>
            @endif
        </div>
        @if ($currentStep < 4)
            <button type="button" class="cw-btn cw-btn-primary" wire:click="nextStep" wire:key="cw-next">
                Continue
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        @else
            <button type="button" class="cw-btn cw-btn-primary" wire:click="openConfirm" wire:key="cw-review">
                Review &amp; close
            </button>
        @endif
    </div>
</div>{{-- /cw-main --}}

{{-- ── Summary rail ── --}}
<aside class="cw-rail">
    <div class="cw-card">
        <div class="cw-card-head">
            <div><div class="cw-card-title">Closing summary</div><div class="cw-card-sub">Updates as you go</div></div>
        </div>
        <div class="cw-row"><span class="cw-row-l">Expected cash</span><span class="cw-row-v">{{ number_format($expected) }}</span></div>
        <div class="cw-row"><span class="cw-row-l">Counted</span><span class="cw-row-v" style="{{ $hasCount ? '' : 'color:var(--text-dim)' }}">{{ $hasCount ? number_format($counted) : '—' }}</span></div>
        <div class="cw-row">
            <span class="cw-row-l">Variance</span>
            <span class="cw-var-pill" style="background:{{ $vDim }};color:{{ $vColor }}">{{ $vText }}</span>
        </div>
        <div class="cw-section-hd">Disposition</div>
        <div class="cw-row"><span class="cw-row-l">To owner (MoMo)</span><span class="cw-row-v">{{ number_format($toOwner) }}</span></div>
        <div class="cw-row"><span class="cw-row-l">Retained</span><span class="cw-row-v">{{ $hasCount ? number_format($retained) : '—' }}</span></div>
        <div class="cw-section-hd">Other channels</div>
        <div class="cw-row"><span class="cw-row-l">Non-cash sales</span><span class="cw-row-v">{{ number_format($nonCashSales) }}</span></div>
        <div class="cw-row"><span class="cw-row-l">MoMo available</span><span class="cw-row-v" style="color:{{ ($s['momo_available'] ?? 0) >= 0 ? 'var(--text)' : 'var(--red)' }}">{{ number_format($s['momo_available'] ?? 0) }}</span></div>
        @if ($creditSales > 0)
            <div class="cw-row"><span class="cw-row-l">Credit extended</span><span class="cw-row-v" style="color:var(--amber)">{{ number_format($creditSales) }}</span></div>
        @endif
    </div>
</aside>
</div>{{-- /cw-grid --}}

{{-- ── Mobile summary bar ── --}}
<div class="cw-mbar" aria-hidden="true">
    <div><div class="cw-mbar-l">Expected</div><div class="cw-mbar-v">{{ number_format($expected) }}</div></div>
    <div><div class="cw-mbar-l">Counted</div><div class="cw-mbar-v" style="{{ $hasCount ? '' : 'color:var(--text-dim)' }}">{{ $hasCount ? number_format($counted) : '—' }}</div></div>
    <div><div class="cw-mbar-l">Variance</div><div class="cw-mbar-v" style="color:{{ $vColor }}">{{ $vText }}</div></div>
</div>

{{-- ── Record drawer (step 2 "Add" buttons) ── --}}
@if ($dailySessionId)
    @include('livewire.shop.day-close.partials.record-drawer', ['dailySessionId' => $dailySessionId])
@endif

{{-- ── Count by denomination ── --}}
<div wire:ignore
     x-data="{
        open: false,
        notes: [5000, 2000, 1000, 500],
        coins: [100, 50, 20, 10, 5, 1],
        c: {},
        get total() { let t = 0; for (const k in this.c) { t += Number(k) * (parseInt(this.c[k]) || 0); } return t; },
        fmt(v) { return Number(v).toLocaleString('en-US'); },
        apply() { $wire.set('actualCashCounted', String(this.total)); this.open = false; }
     }"
     @cw-denoms.window="open = true; setTimeout(() => $el.querySelector('.cw-modal input')?.focus(), 60)"
     @keydown.escape.window="open = false">
    <div class="cw-overlay" x-show="open" x-cloak x-transition.opacity.duration.150ms @click.self="open = false">
        <div class="cw-modal" role="dialog" aria-modal="true" aria-labelledby="cw-den-title">
            <div class="cw-modal-head">
                <h2 class="cw-modal-title" id="cw-den-title">Count by denomination</h2>
                <p class="cw-modal-sub">Enter how many of each note and coin are in the drawer.</p>
            </div>
            <div class="cw-modal-body">
                <div class="cw-den-grid">
                    <div class="cw-den-hd">Notes</div>
                    <template x-for="d in notes" :key="'n' + d">
                        <div style="display:contents">
                            <span class="cw-den-v" x-text="fmt(d)"></span>
                            <span class="cw-den-x">×</span>
                            <input type="number" min="0" inputmode="numeric" class="cw-num" placeholder="0" x-model="c[d]" :aria-label="fmt(d) + ' RWF notes'">
                            <span class="cw-den-s" x-text="fmt(d * (parseInt(c[d]) || 0))"></span>
                        </div>
                    </template>
                    <div class="cw-den-hd" style="padding-top:12px">Coins</div>
                    <template x-for="d in coins" :key="'c' + d">
                        <div style="display:contents">
                            <span class="cw-den-v" x-text="fmt(d)"></span>
                            <span class="cw-den-x">×</span>
                            <input type="number" min="0" inputmode="numeric" class="cw-num" placeholder="0" x-model="c[d]" :aria-label="fmt(d) + ' RWF coins'">
                            <span class="cw-den-s" x-text="fmt(d * (parseInt(c[d]) || 0))"></span>
                        </div>
                    </template>
                </div>
            </div>
            <div class="cw-total" style="padding-left:22px;padding-right:22px">
                <span class="cw-total-l">Total counted</span>
                <span class="cw-total-v" style="color:var(--text)"><span x-text="fmt(total)"></span><span class="cw-unit">RWF</span></span>
            </div>
            <div class="cw-modal-foot">
                <button type="button" class="cw-btn cw-btn-ghost" style="margin-right:auto" @click="c = {}">Clear</button>
                <button type="button" class="cw-btn cw-btn-ghost" @click="open = false">Cancel</button>
                <button type="button" class="cw-btn cw-btn-primary" @click="apply()">Use this total</button>
            </div>
        </div>
    </div>
</div>

{{-- ── Final confirmation ── --}}
@if ($showConfirm)
    <div class="cw-overlay" wire:key="cw-confirm" x-data @keydown.escape.window="$wire.set('showConfirm', false)" @click.self="$wire.set('showConfirm', false)">
        <div class="cw-modal" role="dialog" aria-modal="true" aria-labelledby="cw-confirm-title">
            <div class="cw-modal-head">
                <h2 class="cw-modal-title" id="cw-confirm-title">Close the register?</h2>
                <p class="cw-modal-sub">{{ $dateLabel }}. You can re-open it for corrections until the owner locks the session.</p>
            </div>
            <div class="cw-modal-body">
                <div class="cw-row"><span class="cw-row-l">Expected cash</span><span class="cw-row-v">{{ number_format($expected) }}</span></div>
                <div class="cw-row"><span class="cw-row-l">Counted</span><span class="cw-row-v">{{ number_format($counted) }}</span></div>
                <div class="cw-row"><span class="cw-row-l">Variance</span><span class="cw-var-pill" style="background:{{ $vDim }};color:{{ $vColor }}">{{ $vText }}</span></div>
                <div class="cw-row"><span class="cw-row-l">Sent to owner (MoMo)</span><span class="cw-row-v">{{ number_format($toOwner) }}</span></div>
                <div class="cw-row"><span class="cw-row-l">Retained in drawer</span><span class="cw-row-v">{{ number_format($retained) }}</span></div>
                @if ($nonCashSales > 0)
                    <div class="cw-row"><span class="cw-row-l">Non-cash settled</span><span class="cw-row-v">{{ number_format($settledTotal) }} <span style="color:var(--text-dim);font-weight:500">/ {{ number_format($nonCashSales) }}</span></span></div>
                @endif
                @if ($variance < 0)
                    <p class="cw-hint" style="padding:8px 22px 0;margin:0;color:var(--red)">The shortage of {{ number_format(abs($variance)) }} RWF will be recorded as a cash-shortage expense.</p>
                @endif
            </div>
            <div class="cw-modal-foot">
                <button type="button" class="cw-btn cw-btn-ghost" wire:click="$set('showConfirm', false)">Cancel</button>
                <button type="button" class="cw-btn cw-btn-primary" wire:click="submitClose" wire:loading.attr="disabled" wire:target="submitClose">
                    <span wire:loading.remove wire:target="submitClose">Close register</span>
                    <span wire:loading.flex wire:target="submitClose" style="align-items:center;gap:6px">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:cw-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                        Closing…
                    </span>
                </button>
            </div>
        </div>
    </div>
@endif
</div>
