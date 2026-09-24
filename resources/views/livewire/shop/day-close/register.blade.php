<div class="dc-page" style="font-family:var(--font)"
     x-data="{ openModal: $wire.entangle('showOpenModal') }"
     @if($session?->isOpen()) wire:poll.30s @endif>
<style>
.dc-page { padding:0 0 80px; }

/* Header */
.dc-header       { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap; }
.dc-header-title { font-size:22px;font-weight:800;color:var(--text);margin:0 0 6px;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
.dc-header-sub   { font-size:13px;color:var(--text-dim);margin:0; }
.dc-header-sub b { color:var(--text-sub);font-weight:600; }
.dc-actions      { display:flex;gap:8px;align-items:center;flex-wrap:wrap; }

/* Badge */
.dc-badge     { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap; }
.dc-badge-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0; }

/* Buttons */
.dc-btn         { padding:9px 16px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);
                  transition:all var(--tr);display:inline-flex;align-items:center;gap:6px;white-space:nowrap;text-decoration:none;line-height:1.2; }
.dc-btn-primary { background:var(--accent);color:#fff;border:1px solid var(--accent);box-shadow:0 3px 10px rgba(59,111,212,.25); }
.dc-btn-primary:hover { opacity:.88; }
.dc-btn-primary:disabled { opacity:.5;cursor:not-allowed; }
.dc-btn-ghost   { background:var(--surface);color:var(--text-sub);border:1px solid var(--border); }
.dc-btn-ghost:hover { background:var(--surface2);color:var(--text); }
.dc-btn-sm      { padding:5px 11px;font-size:12px; }

/* Record menu */
.dc-menu-wrap { position:relative; }
.dc-menu      { position:absolute;right:0;top:calc(100% + 6px);z-index:30;min-width:270px;background:var(--surface);
                border-radius:10px;box-shadow:0 10px 30px rgba(26,31,54,.16),0 0 0 1px var(--border);padding:6px; }
.dc-menu-item { display:flex;align-items:center;gap:10px;width:100%;padding:9px 10px;border:none;background:transparent;
                border-radius:7px;cursor:pointer;font-family:var(--font);text-align:left;transition:background var(--tr); }
.dc-menu-item:hover { background:var(--surface2); }
.dc-menu-ico  { width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.dc-menu-t    { font-size:13px;font-weight:600;color:var(--text); }
.dc-menu-s    { font-size:11px;color:var(--text-dim);margin-top:1px; }

/* Notice */
.dc-notice { display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:10px 14px;margin-bottom:16px;
             background:var(--surface);border-radius:var(--rsm);box-shadow:var(--shadow-card);border-left:3px solid var(--amber);
             font-size:13px;color:var(--text-sub); }
.dc-notice a { color:var(--amber);font-weight:700;text-decoration:none;margin-left:auto;white-space:nowrap; }
.dc-notice a:hover { text-decoration:underline; }

/* KPI cards — .iv-kpi anatomy (vertical-list footer) */
.dc-kpis      { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px; }
.dc-kpi       { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);padding:20px;
                display:flex;flex-direction:column;gap:14px;transition:box-shadow var(--tr);min-width:0; }
.dc-kpi:hover { box-shadow:var(--shadow-card-hover); }
.dc-kpi-row   { display:flex;align-items:center;gap:12px; }
.dc-kpi-icon  { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.dc-kpi-body  { flex:1;min-width:0; }
.dc-kpi-label { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);line-height:1.2; }
.dc-kpi-sub   { font-size:12px;color:var(--text-dim);margin-top:2px; }
.dc-kpi-val   { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;line-height:1;white-space:nowrap; }
.dc-kpi-unit  { font-size:12px;font-weight:500;color:var(--text-dim);margin-left:4px;letter-spacing:0; }
.dc-kpi-divider { height:1px;background:var(--border); }
.dc-kpi-footer  { display:flex;flex-direction:column; }
.dc-kpi-stat    { display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid var(--border);min-width:0; }
.dc-kpi-stat:last-child { border-bottom:none; }
.dc-kpi-stat-l  { font-size:11px;color:var(--text-dim);flex-shrink:0;margin-right:8px; }
.dc-kpi-stat-v  { font-size:13px;font-weight:700;font-family:var(--mono);color:var(--text-sub);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }

/* Main grid */
.dc-grid  { display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:20px;align-items:start; }
.dc-side  { display:flex;flex-direction:column;gap:20px;min-width:0; }

/* Cards */
.dc-card       { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0; }
.dc-card-head  { padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px; }
.dc-card-title { font-size:13px;font-weight:700;color:var(--text);margin:0; }
.dc-card-sub   { font-size:12px;color:var(--text-dim);margin-top:2px; }

/* Ledger */
.dc-ledger     { padding:6px 20px 4px; }
.dc-led-row    { display:flex;justify-content:space-between;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px; }
.dc-led-row:last-child { border-bottom:none; }
.dc-led-l      { color:var(--text-sub); }
.dc-led-v      { font-family:var(--mono);font-weight:600;white-space:nowrap; }
.dc-led-total  { display:flex;justify-content:space-between;align-items:baseline;gap:12px;padding:14px 20px;border-top:2px solid var(--border); }
.dc-led-total-l { font-size:13px;font-weight:700;color:var(--text); }
.dc-led-total-v { font-size:20px;font-weight:800;font-family:var(--mono);color:var(--green);letter-spacing:-.5px;white-space:nowrap; }
.dc-led-note   { margin:0 20px 16px;padding:8px 12px;border-radius:var(--rsm);border-left:3px solid var(--amber);
                 display:flex;justify-content:space-between;gap:10px;font-size:12px;color:var(--text-sub); }

/* Empty / closed state */
.dc-empty       { padding:48px 24px;text-align:center; }
.dc-empty-icon  { width:48px;height:48px;border-radius:12px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center; }
.dc-empty-title { font-size:16px;font-weight:800;color:var(--text);margin-bottom:6px; }
.dc-empty-sub   { font-size:13px;color:var(--text-dim);margin:0 auto 20px;max-width:420px;line-height:1.5; }
.dc-closed-stats { display:grid;grid-template-columns:repeat(4,1fr);border-top:1px solid var(--border); }
.dc-closed-stat  { padding:16px 20px;border-right:1px solid var(--border);min-width:0; }
.dc-closed-stat:last-child { border-right:none; }
.dc-closed-l { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);margin-bottom:6px; }
.dc-closed-v { font-size:18px;font-weight:800;font-family:var(--mono);color:var(--text);white-space:nowrap; }

/* Modal */
.dc-overlay    { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px);
                 display:flex;align-items:center;justify-content:center;padding:16px; }
.dc-modal      { background:var(--surface);border-radius:var(--r);box-shadow:0 24px 60px rgba(26,31,54,.25);width:100%;max-width:420px; }
.dc-modal-head { padding:20px 22px 0; }
.dc-modal-title { font-size:16px;font-weight:800;color:var(--text);margin:0; }
.dc-modal-sub  { font-size:13px;color:var(--text-dim);margin:4px 0 0;line-height:1.5; }
.dc-modal-body { padding:18px 22px; }
.dc-modal-foot { padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px; }
.dc-label      { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px; }
.dc-money      { position:relative; }
.dc-money input { width:100%;padding:11px 52px 11px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:20px;font-weight:700;
                  font-family:var(--mono);text-align:right;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;
                  transition:border-color var(--tr);-moz-appearance:textfield; }
.dc-money input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.dc-money span  { position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--text-dim);pointer-events:none; }
.dc-chip       { display:inline-flex;align-items:center;gap:4px;margin-top:10px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;
                 border:1px solid var(--border);background:var(--surface);color:var(--text-sub);cursor:pointer;font-family:var(--font);transition:all var(--tr); }
.dc-chip:hover { border-color:var(--accent);color:var(--accent); }
.dc-error      { font-size:12px;color:var(--red);margin-top:6px; }
.dc-hint       { font-size:12px;color:var(--text-dim);margin-top:8px;line-height:1.5; }

@keyframes dc-spin { to { transform:rotate(360deg) } }

@media (max-width:1100px) {
    .dc-kpis { grid-template-columns:1fr 1fr; }
    .dc-grid { grid-template-columns:1fr; }
}
@media (max-width:768px) {
    .dc-header-title { font-size:19px; }
    .dc-kpis { gap:10px; }
    .dc-kpi  { padding:14px;gap:10px; }
    .dc-kpi-val { font-size:20px; }
    .dc-closed-stats { grid-template-columns:1fr 1fr; }
    .dc-closed-stat:nth-child(2) { border-right:none; }
    .dc-closed-stat:nth-child(-n+2) { border-bottom:1px solid var(--border); }
    .dc-actions { width:100%;flex-wrap:nowrap; }
    .dc-actions .dc-btn-primary { margin-left:auto; }
}
@media (max-width:640px) {
    .dc-menu-item { min-height:0 !important;padding:9px 10px !important; }
}
@media (max-width:480px) {
    .dc-kpis { grid-template-columns:1fr; }
    .dc-card-head, .dc-ledger, .dc-led-total { padding-left:14px;padding-right:14px; }
    .dc-menu { right:auto;left:0; }
    .dc-hide-xs { display:none; }
    .dc-btn { padding:9px 12px; }
}
</style>

@php
    $isOpen   = $session?->isOpen();
    $isClosed = $session && ! $isOpen;
@endphp

{{-- ── Header ── --}}
<div class="dc-header">
    <div>
        <h1 class="dc-header-title">
            Cash Register
            @if ($isOpen)
                <span class="dc-badge" style="background:var(--green-dim);color:var(--green)"><span class="dc-badge-dot" style="background:var(--green)"></span>Open</span>
            @elseif ($isClosed)
                <span class="dc-badge" style="background:var(--surface2);color:var(--text-sub)"><span class="dc-badge-dot" style="background:var(--text-dim)"></span>{{ $session->isLocked() ? 'Locked' : 'Closed' }}</span>
            @else
                <span class="dc-badge" style="background:var(--amber-dim);color:var(--amber)"><span class="dc-badge-dot" style="background:var(--amber)"></span>Not opened</span>
            @endif
        </h1>
        <p class="dc-header-sub">
            @if ($shopName)<b>{{ $shopName }}</b> · @endif{{ business_today()->format('l, d M Y') }}
            @if ($isOpen)
                · Opened {{ local_time($session->opened_at)->format('H:i') }}@if($session->openedBy) by {{ $session->openedBy->name }}@endif
            @elseif ($isClosed && $session->closed_at)
                · Closed {{ local_time($session->closed_at)->format('H:i') }}@if($session->closedBy) by {{ $session->closedBy->name }}@endif
            @endif
        </p>
    </div>

    <div class="dc-actions">
        <a href="{{ route('shop.session.history') }}" class="dc-btn dc-btn-ghost" title="Session history" aria-label="Session history">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="dc-hide-xs">History</span>
        </a>

        @if ($isOpen)
            <div class="dc-menu-wrap" x-data="{ m:false }" @click.outside="m=false" @keydown.escape.window="m=false">
                <button type="button" class="dc-btn dc-btn-ghost" @click="m=!m" :aria-expanded="m">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Record
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="dc-menu" x-show="m" x-cloak x-transition.opacity.duration.100ms>
                    <button type="button" class="dc-menu-item" @click="m=false;$dispatch('dc-record',{type:'expense'})">
                        <span class="dc-menu-ico" style="background:var(--red-dim);color:var(--red)"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg></span>
                        <span><span class="dc-menu-t">Expense</span><div class="dc-menu-s">Operational cost paid today</div></span>
                    </button>
                    <button type="button" class="dc-menu-item" @click="m=false;$dispatch('dc-record',{type:'withdrawal'})">
                        <span class="dc-menu-ico" style="background:var(--amber-dim);color:var(--amber)"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span>
                        <span><span class="dc-menu-t">Owner withdrawal</span><div class="dc-menu-s">Cash or MoMo taken by the owner</div></span>
                    </button>
                    <button type="button" class="dc-menu-item" @click="m=false;$dispatch('dc-record',{type:'deposit'})">
                        <span class="dc-menu-ico" style="background:var(--violet-dim);color:var(--violet)"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l9-3 9 3M3 6v12l9 3 9-3V6M12 3v18"/></svg></span>
                        <span><span class="dc-menu-t">Bank deposit</span><div class="dc-menu-s">Cash or MoMo moved to the bank</div></span>
                    </button>
                </div>
            </div>
            <a href="{{ route('shop.session.close', ['session' => $session->id]) }}" class="dc-btn dc-btn-primary">
                Close register
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        @elseif ($isClosed)
            <a href="{{ route('shop.reports.daily') }}" class="dc-btn dc-btn-primary">Daily report</a>
        @else
            <button type="button" class="dc-btn dc-btn-primary" @click="openModal = true">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                Open register
            </button>
        @endif
    </div>
</div>

@if (session()->has('error'))
    <div class="dc-notice" style="border-left-color:var(--red)">{{ session('error') }}</div>
@endif

{{-- ── Older session left open ── --}}
@if ($blocker)
    <div class="dc-notice">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--amber);flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        <span>The register for <b style="color:var(--text)">{{ $blocker->session_date->format('d M Y') }}</b> was never closed.</span>
        <a href="{{ route('shop.session.close', ['session' => $blocker->id]) }}">Close it now →</a>
    </div>
@endif

@if ($isOpen && $summary)
    @php
        $s          = $summary;
        $cashIn     = $s['total_sales_cash'] + ($s['total_repayments_cash'] ?? 0);
        $cashOut    = ($s['total_expenses_cash'] ?? 0) + ($s['total_withdrawals_cash'] ?? 0) + ($s['cash_deposits'] ?? 0) + ($s['total_refunds_cash'] ?? 0);
        $moneyOut   = $s['total_expenses'] + $s['total_withdrawals'] + $s['total_bank_deposits'];
    @endphp

    {{-- ── KPIs ── --}}
    <div class="dc-kpis">
        <div class="dc-kpi">
            <div class="dc-kpi-row">
                <div class="dc-kpi-icon" style="background:var(--green-dim);color:var(--green)"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                <div class="dc-kpi-body"><div class="dc-kpi-label">Cash in drawer</div><div class="dc-kpi-sub">Expected right now</div></div>
            </div>
            <div class="dc-kpi-val" style="color:var(--green)">{{ number_format($s['expected_cash']) }}<span class="dc-kpi-unit">RWF</span></div>
            <div class="dc-kpi-divider"></div>
            <div class="dc-kpi-footer">
                <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">Opening</span><span class="dc-kpi-stat-v">{{ number_format($s['opening_balance']) }}</span></div>
                <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">Cash in</span><span class="dc-kpi-stat-v" style="color:var(--green)">+{{ number_format($cashIn) }}</span></div>
                <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">Cash out</span><span class="dc-kpi-stat-v" style="color:var(--red)">−{{ number_format($cashOut) }}</span></div>
            </div>
        </div>

        <div class="dc-kpi">
            <div class="dc-kpi-row">
                <div class="dc-kpi-icon" style="background:var(--success-dim);color:var(--success)"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
                <div class="dc-kpi-body"><div class="dc-kpi-label">Sales today</div><div class="dc-kpi-sub">{{ $s['transaction_count'] }} {{ $s['transaction_count'] === 1 ? 'transaction' : 'transactions' }}</div></div>
            </div>
            <div class="dc-kpi-val" style="color:var(--text)">{{ number_format($s['total_sales']) }}<span class="dc-kpi-unit">RWF</span></div>
            <div class="dc-kpi-divider"></div>
            <div class="dc-kpi-footer">
                <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">Cash</span><span class="dc-kpi-stat-v">{{ number_format($s['total_sales_cash']) }}</span></div>
                <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">MoMo</span><span class="dc-kpi-stat-v">{{ number_format($s['total_sales_momo']) }}</span></div>
                <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">Credit</span><span class="dc-kpi-stat-v" style="{{ $s['total_sales_credit'] > 0 ? 'color:var(--amber)' : '' }}">{{ number_format($s['total_sales_credit']) }}</span></div>
            </div>
        </div>

        <div class="dc-kpi">
            <div class="dc-kpi-row">
                <div class="dc-kpi-icon" style="background:var(--accent-dim);color:var(--accent)"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></div>
                <div class="dc-kpi-body"><div class="dc-kpi-label">Mobile money</div><div class="dc-kpi-sub">Available balance</div></div>
            </div>
            <div class="dc-kpi-val" style="color:{{ $s['momo_available'] >= 0 ? 'var(--accent)' : 'var(--red)' }}">{{ number_format($s['momo_available']) }}<span class="dc-kpi-unit">RWF</span></div>
            <div class="dc-kpi-divider"></div>
            <div class="dc-kpi-footer">
                <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">MoMo in</span><span class="dc-kpi-stat-v">{{ number_format($s['total_sales_momo'] + ($s['total_repayments_momo'] ?? 0)) }}</span></div>
                <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">MoMo out</span><span class="dc-kpi-stat-v">{{ number_format(($s['total_expenses_momo'] ?? 0) + ($s['total_withdrawals_momo'] ?? 0) + ($s['momo_deposits'] ?? 0)) }}</span></div>
                @if ($allowBank)
                    <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">Bank available</span><span class="dc-kpi-stat-v" style="color:var(--violet)">{{ number_format($s['bank_available']) }}</span></div>
                @else
                    <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">Repayments</span><span class="dc-kpi-stat-v">{{ number_format($s['total_repayments_momo'] ?? 0) }}</span></div>
                @endif
            </div>
        </div>

        <div class="dc-kpi">
            <div class="dc-kpi-row">
                <div class="dc-kpi-icon" style="background:var(--red-dim);color:var(--red)"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg></div>
                <div class="dc-kpi-body"><div class="dc-kpi-label">Money out</div><div class="dc-kpi-sub">All channels</div></div>
            </div>
            <div class="dc-kpi-val" style="color:{{ $moneyOut > 0 ? 'var(--red)' : 'var(--text-dim)' }}">{{ number_format($moneyOut) }}<span class="dc-kpi-unit">RWF</span></div>
            <div class="dc-kpi-divider"></div>
            <div class="dc-kpi-footer">
                <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">Expenses · {{ $s['expense_count'] }}</span><span class="dc-kpi-stat-v">{{ number_format($s['total_expenses']) }}</span></div>
                <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">Withdrawals · {{ $s['withdrawal_count'] }}</span><span class="dc-kpi-stat-v">{{ number_format($s['total_withdrawals']) }}</span></div>
                <div class="dc-kpi-stat"><span class="dc-kpi-stat-l">Bank deposits · {{ $s['bank_deposit_count'] }}</span><span class="dc-kpi-stat-v">{{ number_format($s['total_bank_deposits']) }}</span></div>
            </div>
        </div>
    </div>

    {{-- ── Main grid ── --}}
    <div class="dc-grid">
        <livewire:shop.day-close.session-activity-feed :dailySessionId="$session->id" :key="'feed-'.$session->id" />

        <div class="dc-side">
            {{-- Cash drawer ledger --}}
            @php
                $ledger = [
                    ['Cash sales',         $s['total_sales_cash'],               '+', 'var(--green)'],
                    ['Cash repayments',    $s['total_repayments_cash'] ?? 0,     '+', 'var(--green)'],
                    ['Cash refunds',       $s['total_refunds_cash'] ?? 0,        '−', 'var(--red)'],
                    ['Cash expenses',      $s['total_expenses_cash'] ?? 0,       '−', 'var(--red)'],
                    ['Owner withdrawals',  $s['total_withdrawals_cash'] ?? 0,    '−', 'var(--red)'],
                    ['Deposited to bank',  $s['cash_deposits'] ?? 0,             '−', 'var(--red)'],
                ];
            @endphp
            <div class="dc-card">
                <div class="dc-card-head">
                    <div><div class="dc-card-title">Cash drawer</div><div class="dc-card-sub">How the expected cash is made up</div></div>
                </div>
                <div class="dc-ledger">
                    <div class="dc-led-row">
                        <span class="dc-led-l">Opening balance</span>
                        <span class="dc-led-v" style="color:var(--text)">{{ number_format($s['opening_balance']) }}</span>
                    </div>
                    @foreach ($ledger as $__row)
                        @php [$lbl, $val, $sign, $color] = $__row; @endphp
                        @if ($val > 0)
                            <div class="dc-led-row">
                                <span class="dc-led-l">{{ $lbl }}</span>
                                <span class="dc-led-v" style="color:{{ $color }}">{{ $sign }}{{ number_format($val) }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="dc-led-total">
                    <span class="dc-led-total-l">Expected in drawer</span>
                    <span class="dc-led-total-v">{{ number_format($s['expected_cash']) }} <span style="font-size:12px;font-weight:500;color:var(--text-dim)">RWF</span></span>
                </div>
                @if ($s['total_sales_credit'] > 0)
                    <div class="dc-led-note">
                        <span>Credit extended — not in drawer</span>
                        <span style="font-family:var(--mono);font-weight:700;color:var(--amber)">{{ number_format($s['total_sales_credit']) }}</span>
                    </div>
                @endif
            </div>

            <livewire:shop.day-close.pending-requests />
        </div>
    </div>

    @include('livewire.shop.day-close.partials.record-drawer', ['dailySessionId' => $session->id])

@elseif ($isClosed)
    <div class="dc-card">
        <div class="dc-empty" style="padding-bottom:32px">
            <div class="dc-empty-icon" style="background:var(--green-dim);color:var(--green)"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div class="dc-empty-title">Today's register is closed</div>
            <p class="dc-empty-sub" style="margin-bottom:0">The day has been counted and submitted. It can be re-opened for corrections until the owner locks it.</p>
        </div>
        @php $var = (int) $session->cash_variance; @endphp
        <div class="dc-closed-stats">
            <div class="dc-closed-stat"><div class="dc-closed-l">Cash counted</div><div class="dc-closed-v">{{ number_format($session->actual_cash_counted) }}</div></div>
            <div class="dc-closed-stat"><div class="dc-closed-l">Variance</div><div class="dc-closed-v" style="color:{{ $var === 0 ? 'var(--green)' : ($var > 0 ? 'var(--amber)' : 'var(--red)') }}">{{ $var > 0 ? '+' : ($var < 0 ? '−' : '') }}{{ number_format(abs($var)) }}</div></div>
            <div class="dc-closed-stat"><div class="dc-closed-l">Sent to owner</div><div class="dc-closed-v">{{ number_format($session->cash_to_owner_momo) }}</div></div>
            <div class="dc-closed-stat"><div class="dc-closed-l">Retained</div><div class="dc-closed-v">{{ number_format($session->cash_retained) }}</div></div>
        </div>
    </div>

@else
    <div class="dc-card" style="margin-bottom:20px">
        <div class="dc-empty">
            <div class="dc-empty-icon" style="background:var(--accent-dim);color:var(--accent)"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></div>
            <div class="dc-empty-title">The register isn't open yet</div>
            <p class="dc-empty-sub">Count the cash in the drawer and open the register to start recording today's sales, expenses and cash movements.</p>
            <button type="button" class="dc-btn dc-btn-primary" @click="openModal = true">Open register</button>
        </div>
    </div>

    @if ($pendingCount > 0)
        <div style="max-width:560px">
            <livewire:shop.day-close.pending-requests />
        </div>
    @endif
@endif

{{-- ── Open register modal ── --}}
@if (! $session)
    <div class="dc-overlay" x-show="openModal" x-cloak x-transition.opacity.duration.150ms
         @keydown.escape.window="openModal = false" @click.self="openModal = false">
        <div class="dc-modal" role="dialog" aria-modal="true" aria-labelledby="dc-open-title">
            <div class="dc-modal-head">
                <h2 class="dc-modal-title" id="dc-open-title">Open register</h2>
                <p class="dc-modal-sub">{{ business_today()->format('l, d M Y') }} — enter the cash in the drawer before the first sale.</p>
            </div>
            <form class="dc-modal-body" wire:submit="openRegister" id="dc-open-form">
                <label class="dc-label" for="dc-opening">Opening cash</label>
                <div class="dc-money">
                    <input id="dc-opening" type="number" min="0" inputmode="numeric" wire:model="openingBalance" placeholder="0"
                           x-effect="if (openModal) $nextTick(() => $el.focus())">
                    <span>RWF</span>
                </div>
                @error('openingBalance') <div class="dc-error">{{ $message }}</div> @enderror
                @if ($suggestedBalance !== null && (string) $suggestedBalance !== (string) $openingBalance)
                    <button type="button" class="dc-chip" wire:click="$set('openingBalance', '{{ $suggestedBalance }}')">
                        Use {{ number_format($suggestedBalance) }} retained on {{ $suggestedFrom }}
                    </button>
                @endif
            </form>
            <div class="dc-modal-foot">
                <button type="button" class="dc-btn dc-btn-ghost" @click="openModal = false">Cancel</button>
                <button type="submit" form="dc-open-form" class="dc-btn dc-btn-primary" wire:loading.attr="disabled" wire:target="openRegister">
                    <span wire:loading.remove wire:target="openRegister">Open register</span>
                    <span wire:loading.flex wire:target="openRegister" style="align-items:center;gap:6px">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:dc-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                        Opening…
                    </span>
                </button>
            </div>
        </div>
    </div>
@endif
</div>
