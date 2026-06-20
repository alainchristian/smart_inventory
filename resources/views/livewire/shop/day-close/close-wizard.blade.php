<div>
<style>
/* ── Close Wizard — Mobile Responsiveness ── */

/* Progress bar: hide per-step labels at ≤500px (they overlap on narrow screens) */
@media (max-width: 500px) {
    .wiz-step-label  { display: none !important; }
    .wiz-progress    { margin-bottom: 1rem !important; }
    .wiz-step-circle { width: 32px !important; height: 32px !important; }
    .wiz-step-circle svg { width: 14px !important; height: 14px !important; }
}

/* Mobile step indicator: only shows when labels are hidden */
.wiz-mobile-step { display: none; }
@media (max-width: 500px) {
    .wiz-mobile-step { display: block; }
}

/* Step 2 summary strip: tighter at ≤500px */
@media (max-width: 500px) {
    .wiz-strip-card  { padding: 10px 8px !important; }
    .wiz-strip-icon  { display: none !important; }
    .wiz-strip-amt   { font-size: 15px !important; letter-spacing: 0 !important; }
    .wiz-strip-sub   { display: none !important; }
    .wiz-strip-label { letter-spacing: 0 !important; font-size: 10px !important; }
}

/* Navigation: stack + full-width buttons at ≤500px */
@media (max-width: 500px) {
    .wiz-nav             { flex-direction: column-reverse !important; gap: 10px !important; align-items: stretch !important; }
    .wiz-nav > div       { width: 100% !important; display: flex !important; flex-direction: column !important; }
    .wiz-nav > div > button,
    .wiz-nav > div > a   { width: 100% !important; justify-content: center !important; display: flex !important; align-items: center !important; }
    .wiz-nav-hint        { text-align: center !important; }
}

/* Cash count input: slightly smaller font on narrow screens */
@media (max-width: 400px) {
    .wiz-cash-input { font-size: 22px !important; padding: 12px 44px 12px 12px !important; }
}

/* Floating widget: smaller + shifted on mobile */
@media (max-width: 500px) {
    .wiz-float       { right: 8px !important; bottom: 12px !important; }
    .wiz-float-panel { width: 185px !important; }
}

/* ── Premium UI Design Skill additions ── */
.wiz-card { background:var(--surface); border:none; border-radius:var(--r, 12px); box-shadow:var(--shadow-card); }
.wiz-card-head { padding:14px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; }
.wiz-card-title { font-size:13px; font-weight:700; color:var(--text); margin:0; text-transform:uppercase; letter-spacing:0.6px; }


/* ── Premium KPI Cards ── */
.wiz-kpis { display:grid; gap:16px; margin-bottom:24px; }
.wiz-kpis-1 { grid-template-columns: 1fr; }
.wiz-kpis-3 { grid-template-columns: repeat(3, 1fr); }
.wiz-kpi { background:var(--surface); border:none; border-radius:var(--r, 12px); box-shadow:var(--shadow-card); padding:24px 24px; display:flex; flex-direction:column; gap:16px; transition:box-shadow var(--tr, 0.2s); }
.wiz-kpi:hover { box-shadow:var(--shadow-card-hover); }
.wiz-kpi-row { display:flex; align-items:center; gap:12px; }
.wiz-kpi-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.wiz-kpi-body { flex:1; min-width:0; }
.wiz-kpi-label { font-size:12px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:var(--text-dim); line-height:1.2; }
.wiz-kpi-sub { font-size:12px; color:var(--text-dim); margin-top:2px; }
.wiz-kpi-val { font-size:32px; font-weight:800; font-family:var(--mono, monospace); letter-spacing:-1px; line-height:1; color:var(--text); }
.wiz-kpi-bar { height:4px; border-radius:4px; background:var(--border); overflow:hidden; }
.wiz-kpi-divider { height:1px; background:var(--border); }
.wiz-kpi-footer { display:grid; grid-template-columns:repeat(3, 1fr); }
.wiz-kpi-stat { display:flex; flex-direction:column; align-items:center; gap:3px; padding:6px 0; }
.wiz-kpi-stat-v { font-size:14px; font-weight:800; font-family:var(--mono, monospace); color:var(--text-sub); }
.wiz-kpi-stat-l { font-size:10px; font-weight:600; color:var(--text-dim); text-transform:uppercase; letter-spacing:0.3px; }

@media(max-width:768px) {
    .wiz-kpis-3 { grid-template-columns: 1fr 1fr; }
    .wiz-kpi { padding:18px; gap:12px; }
    .wiz-kpi-val { font-size:26px; }
}
@media(max-width:500px) {
    .wiz-kpis-3 { grid-template-columns: 1fr; }
}

/* Tabs */
.wiz-tabs { display:grid; grid-template-columns:repeat(3, 1fr); background:var(--surface); box-shadow:var(--shadow-card); border-radius:var(--r, 12px); overflow:hidden; margin-bottom:24px; }
.wiz-tab { display:flex; align-items:center; justify-content:center; gap:6px; padding:12px 10px; border:none; border-radius:0; border-bottom:2.5px solid transparent; border-right:1px solid var(--border); cursor:pointer; font-size:12px; font-weight:600; font-family:var(--font); background:transparent; color:var(--text-dim); transition:all var(--tr); white-space:nowrap; }
.wiz-tab:last-child { border-right:none; }
.wiz-tab:hover { background:var(--surface2); color:var(--text); border-bottom-color:var(--border-hi); }
.wiz-tab.active { background:var(--accent-dim); color:var(--accent); border-bottom-color:var(--accent); }

/* Buttons */
.wiz-btn { padding:9px 18px; border-radius:var(--rsm, 8px); font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font); transition:all var(--tr); display:inline-flex; align-items:center; gap:6px; white-space:nowrap; }
.wiz-btn-primary { background:var(--accent); color:#fff; border:none; box-shadow:0 3px 10px rgba(59,111,212,.25); }
.wiz-btn-primary:hover { opacity:.88; }
.wiz-btn-ghost { background:var(--surface); color:var(--text-dim); border:1px solid var(--border); }
.wiz-btn-ghost:hover { background:var(--surface2); color:var(--text); }
.wiz-btn-amber { background:linear-gradient(135deg,#f59e0b,#d97706); color:#1a1a1a; border:none; font-weight:800; box-shadow:0 4px 14px rgba(245,158,11,0.35); }
.wiz-btn-amber:hover { opacity:.9; }

/* Cash Input focus ring */
.wiz-cash-input:focus { border-color:var(--accent) !important; box-shadow:0 0 0 3px var(--accent-dim); }

/* ──────────────────────────────────────────────────────────
   Extra-narrow fixes (313px phones) — CSS only, no logic
   ────────────────────────────────────────────────────────── */
@media (max-width:400px) {

    /* Step circles: slightly smaller to prevent squeezing connector lines */
    .wiz-step-circle { width:28px !important; height:28px !important; }

    /* All KPI cards: reduce padding + value font-size */
    .wiz-kpi { padding:12px !important; gap:10px !important; }
    .wiz-kpi-val { font-size:20px !important; }

    /* Tabs: tighter font so "Bank Deposits" fits in its ~100px cell */
    .wiz-tab { font-size:10px !important; padding:10px 6px !important; }

    /* Step 4 — Variance Hero:
       The inline row (icon+number on left, Expected/Counted on right) overflows.
       Stack to column so both sides get full width. */
    .wiz-variance-hero {
        flex-direction:column !important;
        padding:14px !important;
        gap:12px !important;
        align-items:flex-start !important;
    }
    .wiz-variance-hero .wiz-variance-right {
        text-align:left !important;
        width:100%;
        display:grid;
        grid-template-columns:1fr 1fr 1fr 1fr;
        gap:4px;
        align-items:center;
    }
    /* The big variance number: smaller on very narrow */
    .wiz-variance-num { font-size:28px !important; }

    /* Step 4 — Retained in Shop KPI:
       Row layout with 32px font overflows. Flip to column. */
    .wiz-retained-kpi {
        flex-direction:column !important;
        align-items:flex-start !important;
        padding:14px !important;
        gap:8px !important;
    }
    .wiz-retained-val { font-size:22px !important; letter-spacing:-0.5px !important; }

    /* Step 4 — Non-cash settlement channel grid:
       The 2-col Settled/Reference grid is too narrow (~130px each).
       Stack to single column. */
    .wiz-nc-row-grid { grid-template-columns:1fr !important; }

    /* Step 3 — Variance display row:
       Flex row with value on right overflows at 313px. */
    .wiz-variance-card-row {
        flex-direction:column !important;
        gap:8px !important;
        align-items:flex-start !important;
    }
    .wiz-variance-card-right { text-align:left !important; }

    /* Floating widget collapsed pill: tighter padding */
    .wiz-float-pill { padding:6px 10px !important; }
}
</style>
<div style="padding-bottom:100px;">

    @if (session()->has('error'))
        <div class="mb-4 px-4 py-3 rounded-xl text-sm" style="background:var(--red-dim);color:var(--red);border:1px solid var(--red);">
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Step Progress Bar ── --}}
    @php
        $steps = [1 => 'Sales', 2 => 'Movements', 3 => 'Cash Count', 4 => 'Close'];
        $stepIcons = [
            1 => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>',
            2 => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            3 => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
            4 => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ];
    @endphp
    <div class="mb-8 wiz-progress">
        <div class="flex items-center">
            @foreach ($steps as $n => $label)
                <div class="flex items-center {{ $n < 4 ? 'flex-1' : '' }}">
                    <div class="relative flex flex-col items-center">
                        {{-- Circle --}}
                        <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 flex-shrink-0 wiz-step-circle"
                             style="
                                @if ($currentStep > $n)
                                    background:var(--green);color:#fff;box-shadow:0 0 0 6px var(--green-dim), 0 4px 12px var(--green-glow);
                                @elseif ($currentStep === $n)
                                    background:var(--accent);color:#fff;box-shadow:0 0 0 6px var(--accent-dim), 0 4px 12px var(--accent-glow);
                                @else
                                    background:var(--surface);color:var(--text-dim);border:2px solid var(--border);box-shadow:var(--shadow-card);
                                @endif
                             ">
                            @if ($currentStep > $n)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">{!! $stepIcons[$n] !!}</svg>
                            @endif
                        </div>
                        {{-- Label --}}
                        <span class="absolute -bottom-5 text-xs font-semibold whitespace-nowrap wiz-step-label"
                              style="@if($currentStep === $n) color:var(--accent); @elseif($currentStep > $n) color:var(--green); @else color:var(--text-dim); @endif">
                            {{ $label }}
                        </span>
                    </div>
                    @if ($n < 4)
                        <div class="flex-1 h-0.5 mx-1 rounded-full transition-all duration-500"
                             style="background:{{ $currentStep > $n ? 'var(--green)' : 'var(--border)' }};"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Step Header ── --}}
    @php
        $sessionLabel = $session ? $session->session_date->format('d M Y') : 'today';
        $stepHeaders = [
            1 => ['Sales Review',    "Review all transactions recorded on {$sessionLabel} by payment channel."],
            2 => ['Money Movements', 'Record bank deposits, operational expenses, and owner withdrawals.'],
            3 => ['Cash Count',      'Count the physical cash in the drawer and reconcile against expected.'],
            4 => ['Close Day',       'Confirm disposition of funds and submit the day close.'],
        ];
        [$stepTitle, $stepSub] = $stepHeaders[$currentStep];
    @endphp
    <div class="mb-5 mt-8">
        <div class="wiz-mobile-step text-xs font-semibold mb-1" style="color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;">Step {{ $currentStep }} of 4</div>
        <h2 class="text-lg font-bold" style="color:var(--text);letter-spacing:-0.3px;">{{ $stepTitle }}</h2>
        <p class="text-sm mt-0.5" style="color:var(--text-dim);">{{ $stepSub }}</p>
    </div>

    {{-- ════════════════════════════════════════════
         STEP 1 — Sales Review
    ════════════════════════════════════════════ --}}
    @if ($currentStep === 1)
        @php
            $total    = max(1, $summary['total_sales'] ?? 0);
            $cardAmt  = $summary['total_sales_card'] ?? 0;
            $bankAmt  = $summary['total_sales_bank_transfer'] ?? 0;
            $channels = array_filter([
                ['Cash',          $summary['total_sales_cash']  ?? 0, '#10b981', '#d1fae5', true],
                ['Mobile Money',  $summary['total_sales_momo']  ?? 0, '#6366f1', '#e0e7ff', true],
                ['Card',          $cardAmt,                           '#64748b', '#f1f5f9', $settingAllowCard || $cardAmt > 0],
                ['Bank Transfer', $bankAmt,                           '#0ea5e9', '#e0f2fe', $settingAllowBankTransfer || $bankAmt > 0],
                ['Credit',        $summary['total_sales_credit'] ?? 0,'#f59e0b', '#fef3c7', true],
            ], fn($c) => $c[4]);
            $activeChannels = array_filter($channels, fn($c) => $c[1] > 0);
        @endphp

        {{-- Total sales KPI --}}
        <div class="wiz-kpis wiz-kpis-1">
            <div class="wiz-kpi" style="padding:24px 32px;">
                <div class="wiz-kpi-row">
                    <div class="wiz-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="wiz-kpi-body">
                        <div class="wiz-kpi-label" style="font-size:13px;letter-spacing:1px;">Total Sales Today</div>
                        <div class="wiz-kpi-sub">Gross revenue from all channels</div>
                    </div>
                </div>
                
                <div class="wiz-kpi-val" style="color:var(--green);font-size:42px;">
                    {{ number_format($summary['total_sales'] ?? 0) }}
                    <span style="font-size:16px;font-weight:600;color:var(--text-dim);margin-left:4px;">RWF</span>
                </div>
                
                @php $cashPct = $total > 0 ? round(($summary['total_sales_cash'] ?? 0) / $total * 100) : 0; @endphp
                <div class="wiz-kpi-bar" style="background:var(--surface2);">
                    <div style="height:100%;border-radius:4px;background:var(--green);width:{{ $cashPct }}%"></div>
                </div>
                
                <div class="wiz-kpi-divider"></div>
                
                <div class="wiz-kpi-footer">
                    <div class="wiz-kpi-stat">
                        <span class="wiz-kpi-stat-v">{{ $summary['transaction_count'] ?? 0 }}</span>
                        <span class="wiz-kpi-stat-l">Transactions</span>
                    </div>
                    <div class="wiz-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border);">
                        <span class="wiz-kpi-stat-v">{{ $cashPct }}%</span>
                        <span class="wiz-kpi-stat-l">Cash Ratio</span>
                    </div>
                    <div class="wiz-kpi-stat">
                        <span class="wiz-kpi-stat-v" style="color:var(--accent);">{{ number_format($summary['total_sales_momo'] ?? 0) }}</span>
                        <span class="wiz-kpi-stat-l">Mobile Money</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Channel breakdown --}}
        <div class="rounded-2xl overflow-hidden mb-4" style="border:none;box-shadow:var(--shadow-card);">
            <div class="wiz-card-head">
                <span class="wiz-card-title">By Payment Channel</span>
            </div>
            <div style="background:var(--surface);">
                @foreach ($channels as $__chRow)
                @php [$method, $amount, $color, $bg, $_show] = $__chRow; @endphp
                    @php $pct = $total > 0 ? round($amount / $total * 100, 1) : 0; @endphp
                    <div class="px-4 py-3" style="border-bottom:1px solid var(--border);">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $color }};"></div>
                                <span class="text-sm" style="color:var(--text);">{{ $method }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-mono font-semibold text-sm" style="color:{{ $amount > 0 ? $color : 'var(--text-dim)' }};">
                                    {{ number_format($amount) }} RWF
                                </span>
                                <span class="text-xs ml-1.5" style="color:var(--text-dim);">{{ $pct }}%</span>
                            </div>
                        </div>
                        <div class="h-1 rounded-full overflow-hidden" style="background:var(--border);">
                            <div class="h-full rounded-full transition-all" style="width:{{ $pct }}%;background:{{ $color }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Adjustments row --}}
        @php
            $hasCashRefunds  = ($summary['total_refunds_cash'] ?? 0) > 0;
            $hasRepayments   = ($summary['total_repayments']   ?? 0) > 0;
        @endphp
        @if ($hasCashRefunds || $hasRepayments)
            <div class="rounded-2xl overflow-hidden" style="border:none;box-shadow:var(--shadow-card);">
                <div class="wiz-card-head">
                    <span class="wiz-card-title">Adjustments</span>
                </div>
                <div style="background:var(--surface);">
                    @if ($hasCashRefunds)
                        <div class="flex items-center justify-between px-4 py-3" style="border-bottom:{{ $hasRepayments ? '1px solid var(--border)' : 'none' }};">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center" style="background:var(--red-dim);">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="color:var(--red);">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                </div>
                                <span class="text-sm" style="color:var(--text);">Cash refunds</span>
                            </div>
                            <span class="font-mono text-sm font-semibold" style="color:var(--red);">−{{ number_format($summary['total_refunds_cash']) }} RWF</span>
                        </div>
                    @endif
                    @if ($hasRepayments)
                        @php
                            $bankRep = $summary['total_repayments_bank'] ?? 0;
                        @endphp
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg flex items-center justify-center" style="background:var(--green-dim);">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="color:var(--green);">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium" style="color:var(--text);">Credit repayments received</span>
                                </div>
                                <span class="font-mono text-sm font-semibold" style="color:var(--green);">+{{ number_format($summary['total_repayments']) }} RWF</span>
                            </div>
                            <div class="ml-8 space-y-1">
                                @if (($summary['total_repayments_cash'] ?? 0) > 0)
                                    <div class="flex justify-between text-xs">
                                        <span style="color:var(--text-dim);">Cash</span>
                                        <span class="font-mono" style="color:var(--green);">+{{ number_format($summary['total_repayments_cash']) }} RWF</span>
                                    </div>
                                @endif
                                @if (($summary['total_repayments_momo'] ?? 0) > 0)
                                    <div class="flex justify-between text-xs">
                                        <span style="color:var(--text-dim);">Mobile Money</span>
                                        <span class="font-mono" style="color:#6366f1;">+{{ number_format($summary['total_repayments_momo']) }} RWF</span>
                                    </div>
                                @endif
                                @if ($bankRep > 0)
                                    <div class="flex justify-between text-xs">
                                        <span style="color:var(--text-dim);">Card / Bank Transfer</span>
                                        <span class="font-mono" style="color:var(--accent);">+{{ number_format($bankRep) }} RWF</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif

    {{-- ════════════════════════════════════════════
         STEP 2 — Money Movements
    ════════════════════════════════════════════ --}}
    @if ($currentStep === 2)
    <div x-data="{ activeTab: 'deposits' }">

        {{-- Summary strip --}}
        {{-- Summary KPI Cards --}}
        <div class="wiz-kpis wiz-kpis-3">
            {{-- Bank Deposits --}}
            <div @click="activeTab = 'deposits'" class="wiz-kpi" style="cursor:pointer;" :style="activeTab === 'deposits' ? 'box-shadow:0 0 0 3px var(--accent-dim), var(--shadow-card-hover); border-color:var(--accent);' : ''">
                <div class="wiz-kpi-row">
                    <div class="wiz-kpi-icon" style="background:var(--accent-dim);color:var(--accent);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l9-3 9 3M3 6v12l9 3 9-3V6M12 3v18"/></svg>
                    </div>
                    <div class="wiz-kpi-body">
                        <div class="wiz-kpi-label">Deposits</div>
                    </div>
                </div>
                <div class="wiz-kpi-val" style="color:var(--accent);">
                    {{ number_format($summary['total_bank_deposits'] ?? 0) }}
                </div>
                <div class="wiz-kpi-divider"></div>
                <div class="wiz-kpi-footer" style="grid-template-columns:1fr;">
                    <div class="wiz-kpi-stat" style="align-items:flex-start;padding:0;">
                        <span class="wiz-kpi-stat-v" style="font-size:13px;">{{ $summary['bank_deposit_count'] ?? 0 }} items</span>
                    </div>
                </div>
            </div>

            {{-- Expenses --}}
            <div @click="activeTab = 'expenses'" class="wiz-kpi" style="cursor:pointer;" :style="activeTab === 'expenses' ? 'box-shadow:0 0 0 3px var(--red-dim), var(--shadow-card-hover); border-color:var(--red);' : ''">
                <div class="wiz-kpi-row">
                    <div class="wiz-kpi-icon" style="background:var(--red-dim);color:var(--red);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </div>
                    <div class="wiz-kpi-body">
                        <div class="wiz-kpi-label">Expenses</div>
                    </div>
                </div>
                <div class="wiz-kpi-val" style="color:var(--red);">
                    {{ number_format($summary['total_expenses'] ?? 0) }}
                </div>
                <div class="wiz-kpi-divider"></div>
                <div class="wiz-kpi-footer" style="grid-template-columns:1fr;">
                    <div class="wiz-kpi-stat" style="align-items:flex-start;padding:0;">
                        <span class="wiz-kpi-stat-v" style="font-size:13px;">{{ $summary['expense_count'] ?? 0 }} items</span>
                    </div>
                </div>
            </div>

            {{-- Withdrawals --}}
            <div @click="activeTab = 'withdrawals'" class="wiz-kpi" style="cursor:pointer;" :style="activeTab === 'withdrawals' ? 'box-shadow:0 0 0 3px var(--amber-dim), var(--shadow-card-hover); border-color:var(--amber);' : ''">
                <div class="wiz-kpi-row">
                    <div class="wiz-kpi-icon" style="background:var(--amber-dim);color:var(--amber);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="wiz-kpi-body">
                        <div class="wiz-kpi-label">Withdrawals</div>
                    </div>
                </div>
                <div class="wiz-kpi-val" style="color:var(--amber);">
                    {{ number_format($summary['total_withdrawals'] ?? 0) }}
                </div>
                <div class="wiz-kpi-divider"></div>
                <div class="wiz-kpi-footer" style="grid-template-columns:1fr;">
                    <div class="wiz-kpi-stat" style="align-items:flex-start;padding:0;">
                        <span class="wiz-kpi-stat-v" style="font-size:13px;">{{ $summary['withdrawal_count'] ?? 0 }} items</span>
                    </div>
                </div>
            </div>
        </div>{{-- end summary KPI Cards --}}

        {{-- Tab content panel --}}
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--shadow-card);">

            {{-- Tab bar — explicit separators --}}
            <div class="wiz-tabs">
                <button type="button"
                        @click="activeTab = 'deposits'"
                        :class="{ 'active': activeTab === 'deposits' }"
                        class="wiz-tab">
                    Bank Deposits
                </button>
                <button type="button"
                        @click="activeTab = 'expenses'"
                        :class="{ 'active': activeTab === 'expenses' }"
                        class="wiz-tab">
                    Expenses
                </button>
                <button type="button"
                        @click="activeTab = 'withdrawals'"
                        :class="{ 'active': activeTab === 'withdrawals' }"
                        class="wiz-tab">
                    Withdrawals
                </button>
            </div>

            {{-- Deposits tab --}}
            <div x-show="activeTab === 'deposits'" x-cloak style="padding:20px;">
                <livewire:shop.day-close.add-bank-deposit :dailySessionId="$dailySessionId" />
            </div>

            {{-- Expenses tab --}}
            <div x-show="activeTab === 'expenses'" x-cloak style="padding:20px;">
                <livewire:shop.day-close.expense-list :dailySessionId="$dailySessionId" />
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
                    <livewire:shop.day-close.add-expense :dailySessionId="$dailySessionId" />
                </div>
            </div>

            {{-- Withdrawals tab --}}
            <div x-show="activeTab === 'withdrawals'" x-cloak style="padding:20px;">
                <livewire:shop.day-close.withdrawal-list :dailySessionId="$dailySessionId" />
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
                    <livewire:shop.day-close.add-withdrawal :dailySessionId="$dailySessionId" />
                </div>
            </div>

        </div>{{-- end tab content panel --}}

    </div>{{-- end step 2 wrapper --}}
    @endif

    {{-- ════════════════════════════════════════════
         STEP 3 — Cash Count
    ════════════════════════════════════════════ --}}
    @if ($currentStep === 3)
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;" class="cash-count-grid">
    <style>@media(max-width:640px){ .cash-count-grid{ grid-template-columns:1fr !important; } }
/* ── Premium UI Design Skill additions ── */
.wiz-card { background:var(--surface); border:none; border-radius:var(--r, 12px); box-shadow:var(--shadow-card); }
.wiz-card-head { padding:14px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; }
.wiz-card-title { font-size:13px; font-weight:700; color:var(--text); margin:0; text-transform:uppercase; letter-spacing:0.6px; }


/* ── Premium KPI Cards ── */
.wiz-kpis { display:grid; gap:16px; margin-bottom:24px; }
.wiz-kpis-1 { grid-template-columns: 1fr; }
.wiz-kpis-3 { grid-template-columns: repeat(3, 1fr); }
.wiz-kpi { background:var(--surface); border:none; border-radius:var(--r, 12px); box-shadow:var(--shadow-card); padding:24px 24px; display:flex; flex-direction:column; gap:16px; transition:box-shadow var(--tr, 0.2s); }
.wiz-kpi:hover { box-shadow:var(--shadow-card-hover); }
.wiz-kpi-row { display:flex; align-items:center; gap:12px; }
.wiz-kpi-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.wiz-kpi-body { flex:1; min-width:0; }
.wiz-kpi-label { font-size:12px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:var(--text-dim); line-height:1.2; }
.wiz-kpi-sub { font-size:12px; color:var(--text-dim); margin-top:2px; }
.wiz-kpi-val { font-size:32px; font-weight:800; font-family:var(--mono, monospace); letter-spacing:-1px; line-height:1; color:var(--text); }
.wiz-kpi-bar { height:4px; border-radius:4px; background:var(--border); overflow:hidden; }
.wiz-kpi-divider { height:1px; background:var(--border); }
.wiz-kpi-footer { display:grid; grid-template-columns:repeat(3, 1fr); }
.wiz-kpi-stat { display:flex; flex-direction:column; align-items:center; gap:3px; padding:6px 0; }
.wiz-kpi-stat-v { font-size:14px; font-weight:800; font-family:var(--mono, monospace); color:var(--text-sub); }
.wiz-kpi-stat-l { font-size:10px; font-weight:600; color:var(--text-dim); text-transform:uppercase; letter-spacing:0.3px; }

@media(max-width:768px) {
    .wiz-kpis-3 { grid-template-columns: 1fr 1fr; }
    .wiz-kpi { padding:18px; gap:12px; }
    .wiz-kpi-val { font-size:26px; }
}
@media(max-width:500px) {
    .wiz-kpis-3 { grid-template-columns: 1fr; }
}

/* Tabs */
.wiz-tabs { display:grid; grid-template-columns:repeat(3, 1fr); background:var(--surface); box-shadow:var(--shadow-card); border-radius:var(--r, 12px); overflow:hidden; margin-bottom:24px; }
.wiz-tab { display:flex; align-items:center; justify-content:center; gap:6px; padding:12px 10px; border:none; border-radius:0; border-bottom:2.5px solid transparent; border-right:1px solid var(--border); cursor:pointer; font-size:12px; font-weight:600; font-family:var(--font); background:transparent; color:var(--text-dim); transition:all var(--tr); white-space:nowrap; }
.wiz-tab:last-child { border-right:none; }
.wiz-tab:hover { background:var(--surface2); color:var(--text); border-bottom-color:var(--border-hi); }
.wiz-tab.active { background:var(--accent-dim); color:var(--accent); border-bottom-color:var(--accent); }

/* Buttons */
.wiz-btn { padding:9px 18px; border-radius:var(--rsm, 8px); font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font); transition:all var(--tr); display:inline-flex; align-items:center; gap:6px; white-space:nowrap; }
.wiz-btn-primary { background:var(--accent); color:#fff; border:none; box-shadow:0 3px 10px rgba(59,111,212,.25); }
.wiz-btn-primary:hover { opacity:.88; }
.wiz-btn-ghost { background:var(--surface); color:var(--text-dim); border:1px solid var(--border); }
.wiz-btn-ghost:hover { background:var(--surface2); color:var(--text); }
.wiz-btn-amber { background:linear-gradient(135deg,#f59e0b,#d97706); color:#1a1a1a; border:none; font-weight:800; box-shadow:0 4px 14px rgba(245,158,11,0.35); }
.wiz-btn-amber:hover { opacity:.9; }

/* Cash Input focus ring */
.wiz-cash-input:focus { border-color:var(--accent) !important; box-shadow:0 0 0 3px var(--accent-dim); }

</style>

        {{-- LEFT — Cash ledger --}}
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--shadow-card);">

            <div class="wiz-card-head">
                <span style="font-size:11px;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:0.7px;">Cash Drawer</span>
            </div>

            @php
                $reconLines = [
                    ['Opening balance',           $session->opening_balance,                null,  'var(--text-dim)'],
                    ['Cash sales',                $summary['total_sales_cash'] ?? 0,        '+',   'var(--green)'],
                    ['Repayments collected',      $summary['total_repayments_cash'] ?? 0,   '+',   'var(--green)'],
                    ['Refunds paid out',          $summary['total_refunds_cash'] ?? 0,      '−',   'var(--red)'],
                    ['Expenses (cash)',           $summary['total_expenses_cash'] ?? 0,     '−',   'var(--red)'],
                    ['Owner withdrawals (cash)',  $summary['total_withdrawals_cash'] ?? 0,  '−',   'var(--amber)'],
                    ['Deposited to bank',         $summary['cash_deposits'] ?? 0,           '−',   'var(--accent)'],
                ];
            @endphp

            <div>
                @foreach ($reconLines as $__rlRow)
                @php [$lbl, $val, $sign, $color] = $__rlRow; @endphp
                    @if ($val > 0 || $sign === null)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--border);">
                        <span style="font-size:13px;color:var(--text-dim);">{{ $lbl }}</span>
                        <span style="font-size:13px;font-weight:600;font-family:var(--mono);color:{{ $color }};white-space:nowrap;">
                            @if($sign) {{ $sign }} @endif{{ number_format($val) }} RWF
                        </span>
                    </div>
                    @endif
                @endforeach
            </div>

            <div style="padding:14px 16px;border-top:1px solid var(--border);background:var(--surface);">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:13px;font-weight:600;color:var(--text);">Expected cash</span>
                    <span style="font-size:22px;font-weight:800;font-family:var(--mono);color:var(--accent);letter-spacing:-0.5px;">
                        {{ number_format($summary['expected_cash'] ?? 0) }}
                        <span style="font-size:12px;font-weight:400;color:var(--text-dim);">RWF</span>
                    </span>
                </div>
            </div>

        </div>{{-- end left panel --}}

        {{-- RIGHT — Counting input --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow-card);">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:4px;">
                    Count the physical cash
                </label>
                <p style="font-size:12px;color:var(--text-dim);margin-bottom:16px;">
                    Enter the total cash in the drawer right now.
                </p>

                <div style="position:relative;">
                    <input type="number"
                           wire:model.live="actualCashCounted"
                           min="0"
                           placeholder="0"
                           class="wiz-cash-input"
                       style="width:100%;padding:16px 56px 16px 16px;border-radius:12px;font-size:28px;font-weight:800;font-family:var(--mono);text-align:right;background:var(--surface2);border:2px solid var(--border);color:var(--text);transition:border-color 0.2s;-moz-appearance:textfield;box-sizing:border-box;"
                           onfocus="this.style.borderColor='var(--accent)';"
                           onblur="this.style.borderColor='var(--border)';">
                    <span style="position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--text-dim);font-weight:500;pointer-events:none;">RWF</span>
                </div>

                @error('actualCashCounted')
                    <div style="font-size:12px;color:var(--red);margin-top:6px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Variance display — always visible, updates live --}}
            <div style="border-radius:14px;overflow:hidden;border:none;box-shadow:var(--shadow-card);">
                @php
                    $counted  = (int) $actualCashCounted;
                    $expected = $summary['expected_cash'] ?? 0;
                    $variance = $counted - $expected;
                    $hasInput = $actualCashCounted !== '';
                @endphp

                @if (!$hasInput)
                    <div style="padding:16px 20px;background:var(--surface2);text-align:center;">
                        <div style="font-size:12px;color:var(--text-dim);">
                            Enter the cash count above to see the variance
                        </div>
                    </div>
                @elseif ($variance === 0)
                    <div style="padding:16px 20px;background:var(--green-dim);border-color:var(--green);">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg style="width:16px;height:16px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size:14px;font-weight:700;color:var(--green);">Perfectly Balanced</div>
                                <div style="font-size:12px;color:var(--text-dim);">Drawer matches expected — no variance</div>
                            </div>
                        </div>
                    </div>
                @elseif ($variance > 0)
                    <div style="padding:16px 20px;background:var(--amber-dim);border-color:var(--amber);">
                        <div class="wiz-variance-card-row" style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--amber);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg style="width:16px;height:16px;color:#1a1a1a;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div style="font-size:14px;font-weight:700;color:var(--amber);">Surplus Detected</div>
                                    <div style="font-size:12px;color:var(--text-dim);">Extra cash will be retained in the drawer</div>
                                </div>
                            </div>
                            <div class="wiz-variance-card-right" style="text-align:right;flex-shrink:0;">
                                <div style="font-size:20px;font-weight:800;color:var(--amber);font-family:var(--mono);">+{{ number_format($variance) }}</div>
                                <div style="font-size:10px;color:var(--text-dim);">RWF over</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div style="padding:16px 20px;background:var(--red-dim);border-color:var(--red);">
                        <div class="wiz-variance-card-row" style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--red);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg style="width:16px;height:16px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div style="font-size:14px;font-weight:700;color:var(--red);">Shortage</div>
                                    <div style="font-size:12px;color:var(--text-dim);">Will be auto-recorded as a cash loss</div>
                                </div>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div style="font-size:20px;font-weight:800;color:var(--red);font-family:var(--mono);">−{{ number_format(abs($variance)) }}</div>
                                <div style="font-size:10px;color:var(--text-dim);">RWF short</div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>{{-- end variance display --}}

        </div>{{-- end right panel --}}

    </div>{{-- end step 3 grid --}}
    @endif

    {{-- ════════════════════════════════════════════
         STEP 4 — Close Day
    ════════════════════════════════════════════ --}}
    @if ($currentStep === 4)
        @php
            $v = $cashVariance;
            $vState = $v === 0 ? 'exact' : ($v > 0 ? 'over' : 'short');
            $vColor  = $vState === 'exact' ? 'var(--green)'       : ($vState === 'over' ? 'var(--amber)'     : 'var(--red)');
            $vBg     = $vState === 'exact' ? 'var(--green-dim)'   : ($vState === 'over' ? 'var(--amber-dim)' : 'var(--red-dim)');
            $vBorder = $vState === 'exact' ? 'var(--green)'       : ($vState === 'over' ? 'var(--amber)'     : 'var(--red)');
            $vLabel  = $vState === 'exact' ? 'Cash Balanced'      : ($vState === 'over' ? 'Cash Surplus'     : 'Cash Shortage');
            $vIcon   = $vState === 'exact'
                ? '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                : ($vState === 'over'
                    ? '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>');

            $nonCashSales = ($summary['total_sales_momo'] ?? 0)
                          + ($summary['total_sales_card'] ?? 0)
                          + ($summary['total_sales_bank_transfer'] ?? 0)
                          + ($summary['total_sales_credit'] ?? 0);

            $_ncCard = $summary['total_sales_card'] ?? 0;
            $_ncBank = $summary['total_sales_bank_transfer'] ?? 0;
            $nonCashChannels = array_filter([
                'Mobile Money'  => $summary['total_sales_momo'] ?? 0,
                'Card'          => ($settingAllowCard || $_ncCard > 0) ? $_ncCard : null,
                'Bank Transfer' => ($settingAllowBankTransfer || $_ncBank > 0) ? $_ncBank : null,
                'Credit'        => $summary['total_sales_credit'] ?? 0,
            ]);

            $inflows  = [
                ['Opening Balance',      $summary['opening_balance'] ?? 0,      '#94a3b8', null],
                ['Total Sales',          $summary['total_sales'] ?? 0,           '#10b981', '+'],
                ['Cash Repayments In',   $summary['total_repayments_cash'] ?? 0, '#10b981', '+'],
            ];
            $outflows = [
                ['Non-cash Collected',   $nonCashSales,                                       '#6366f1', '−'],
                ['Cash Refunds',         $summary['total_refunds_cash'] ?? 0,                 '#ef4444', '−'],
                ['Cash Expenses',        $summary['total_expenses_cash'] ?? 0,                '#ef4444', '−'],
                ['Cash Withdrawals',     $summary['total_withdrawals_cash'] ?? 0,             '#f59e0b', '−'],
                ['Cash Deposits to Bank',$summary['cash_deposits'] ?? 0,                      '#6366f1', '−'],
            ];

            $ncCard = $summary['total_sales_card'] ?? 0;
            $ncBank = $summary['total_sales_bank_transfer'] ?? 0;
            $ncChannels = array_filter([
                ['Mobile Money',  'momoSettled',         'momoSettledRef',         $summary['total_sales_momo'] ?? 0, '#6366f1', true],
                ['Card',          'cardSettled',          'cardSettledRef',         $ncCard,  '#64748b', $settingAllowCard || $ncCard > 0],
                ['Bank Transfer', 'bankTransferSettled',  'bankTransferSettledRef', $ncBank,  '#0ea5e9', $settingAllowBankTransfer || $ncBank > 0],
                ['Other',         'otherSettled',         'otherSettledRef',        $summary['total_sales_other'] ?? 0, '#94a3b8', true],
            ], fn($c) => $c[5]);
            $creditSales = $summary['total_sales_credit'] ?? 0;
            $hasNonCash  = collect($ncChannels)->contains(fn ($c) => $c[3] > 0) || $creditSales > 0;
        @endphp

        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- ── 1. VARIANCE HERO ── --}}
            <div class="wiz-kpi wiz-variance-hero" style="flex-direction:row; justify-content:space-between; align-items:center; padding:24px 32px; background:{{ $vBg }}; border:1.5px solid {{ $vBorder }};">
                <div style="display:flex;align-items:center;gap:20px;">
                    <div style="width:56px;height:56px;border-radius:16px;background:{{ $vColor }};opacity:0.15;display:flex;align-items:center;justify-content:center;flex-shrink:0;position:relative;">
                        <svg style="position:absolute;width:32px;height:32px;" fill="none" stroke="{{ $vColor }}" viewBox="0 0 24 24" stroke-width="2">
                            {!! $vIcon !!}
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:{{ $vColor }};margin-bottom:4px;">{{ $vLabel }}</div>
                        <div style="font-size:42px;font-weight:800;font-family:var(--mono);color:{{ $vColor }};line-height:1;letter-spacing:-1px;">
                            {{ $v >= 0 ? '+' : '' }}{{ number_format($v) }}
                            <span style="font-size:16px;font-weight:600;opacity:0.75;letter-spacing:0;"> RWF</span>
                        </div>
                    </div>
                </div>
                <div class="wiz-variance-right" style="text-align:right;flex-shrink:0;">
                    <div style="font-size:11px;color:{{ $vColor }};opacity:0.8;margin-bottom:4px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Expected</div>
                    <div style="font-size:18px;font-weight:800;font-family:var(--mono);color:{{ $vColor }};">{{ number_format($summary['expected_cash'] ?? 0) }}</div>
                    <div style="font-size:11px;color:{{ $vColor }};opacity:0.8;margin:8px 0 4px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Counted</div>
                    <div style="font-size:18px;font-weight:800;font-family:var(--mono);color:{{ $vColor }};">{{ number_format((int) $actualCashCounted) }}</div>
                </div>
            </div>
            </div>

            {{-- ── 3. CASH DISPOSITION ── --}}
            <div style="border-radius:16px;overflow:hidden;border:none;box-shadow:var(--shadow-card);">
                <div style="padding:14px 16px;border-bottom:1px solid var(--border);
                            display:flex;align-items:center;gap:8px;background:var(--surface);">
                    <svg style="width:14px;height:14px;" fill="none" stroke="var(--amber)" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-dim);">Cash Disposition</span>
                </div>

                <div style="background:var(--surface);padding:16px;display:flex;flex-direction:column;gap:14px;">

                    {{-- Transfer row: drawer → owner --}}
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                            <label style="font-size:12px;font-weight:600;color:var(--text-dim);">Send to Owner via MoMo</label>
                            <span style="font-size:10px;padding:2px 8px;border-radius:999px;
                                         background:var(--amber-dim);color:var(--amber);font-weight:600;">Optional</span>
                        </div>
                        <input type="number" wire:model.live="cashToOwnerMomo" min="0"
                               @input="$dispatch('momo-deduction-changed', { val: parseInt($event.target.value) || 0 })"
                               style="width:100%;padding:12px 16px;border-radius:10px;
                                      font-size:22px;font-weight:700;font-family:var(--mono);text-align:right;
                                      background:var(--surface);border:1.5px solid var(--border);
                                      color:var(--text);transition:border-color 0.2s;
                                      -moz-appearance:textfield;box-sizing:border-box;"
                               placeholder="0"
                               onfocus="this.style.borderColor='var(--amber)';"
                               onblur="this.style.borderColor='var(--border)';">
                        @error('cashToOwnerMomo')
                            <div style="font-size:11px;margin-top:4px;color:var(--red);">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ((int) $cashToOwnerMomo > 0)
                        <div>
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--text-dim);margin-bottom:6px;">MoMo Reference</label>
                            <input type="text" wire:model="ownerMomoReference"
                                   style="width:100%;padding:9px 12px;border-radius:8px;font-size:13px;font-family:var(--mono);
                                          background:var(--surface);border:1.5px solid var(--border);color:var(--text);box-sizing:border-box;"
                                   placeholder="Transaction ID or confirmation code">
                        </div>
                    @endif

                    {{-- Retained display --}}
                    <div class="wiz-kpi wiz-retained-kpi" style="padding:24px 32px; flex-direction:row; align-items:center; justify-content:space-between; margin-top:8px; margin-bottom:8px;">
                        <div>
                            <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-dim);margin-bottom:4px;">Retained in Shop</div>
                            <div style="font-size:13px;color:var(--text-dim);">Cash stays in the register</div>
                        </div>
                        <div class="wiz-retained-val" style="font-size:32px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;color:{{ $cashRetained >= 0 ? 'var(--text)' : 'var(--red)' }};">
                            {{ number_format($cashRetained) }}
                            <span style="font-size:16px;font-weight:600;color:var(--text-dim);"> RWF</span>
                        </div>
                    </div>

            {{-- ── 4. NON-CASH SETTLEMENT ── --}}
            @if ($hasNonCash)
                <div style="border-radius:16px;overflow:hidden;border:none;box-shadow:var(--shadow-card);">
                    <div style="padding:14px 16px;border-bottom:1px solid var(--border);background:var(--surface);">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px;">
                            <svg style="width:14px;height:14px;" fill="none" stroke="#6366f1" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-dim);">Non-Cash Settlement</span>
                        </div>
                        <div style="font-size:11px;color:var(--text-dim);padding-left:22px;">Confirm how each channel's collections were transferred to the owner</div>
                    </div>

                    <div style="background:var(--surface);">
                        @foreach ($ncChannels as $__ncRow)
                        @php [$label, $field, $refField, $total, $color, $_show] = $__ncRow; @endphp
                            @if ($total > 0)
                                <div style="padding:14px 16px;border-bottom:1px solid var(--border);">
                                    {{-- Channel header --}}
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div style="width:8px;height:8px;border-radius:50%;background:{{ $color }};flex-shrink:0;
                                                        box-shadow:0 0 0 3px {{ $color }}22;"></div>
                                            <span style="font-size:13px;font-weight:600;color:var(--text);">{{ $label }}</span>
                                        </div>
                                        <span style="font-size:11px;font-family:var(--mono);font-weight:700;
                                                     padding:3px 10px;border-radius:999px;
                                                     background:{{ $color }}18;color:{{ $color }};">
                                            {{ number_format($total) }} RWF
                                        </span>
                                    </div>
                                    {{-- Amount + Reference in compact 2-col --}}
                                    <div class="wiz-nc-row-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                        <div>
                                            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.4px;
                                                        color:var(--text-dim);margin-bottom:4px;">Settled (RWF)</div>
                                            <input type="number" wire:model.blur="{{ $field }}" min="0"
                                                   style="width:100%;padding:8px 10px;border-radius:8px;font-size:13px;font-weight:700;
                                                          font-family:var(--mono);text-align:right;
                                                          background:var(--surface);border:1.5px solid var(--border);
                                                          color:var(--text);box-sizing:border-box;-moz-appearance:textfield;"
                                                   placeholder="0"
                                                   onfocus="if(this.value==='0')this.value='';this.style.borderColor='{{ $color }}';"
                                                   onblur="if(this.value==='')this.value='0';this.style.borderColor='var(--border)';">
                                        </div>
                                        <div>
                                            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.4px;
                                                        color:var(--text-dim);margin-bottom:4px;">Reference</div>
                                            <input type="text" wire:model="{{ $refField }}"
                                                   style="width:100%;padding:8px 10px;border-radius:8px;font-size:12px;
                                                          background:var(--surface);border:1.5px solid var(--border);
                                                          color:var(--text);box-sizing:border-box;"
                                                   placeholder="Txn ID…">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if ($creditSales > 0)
                            <div style="padding:12px 16px;background:var(--amber-dim);display:flex;align-items:center;justify-content:space-between;gap:12px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:8px;height:8px;border-radius:50%;background:var(--amber);flex-shrink:0;"></div>
                                    <div>
                                        <div style="font-size:12px;font-weight:600;color:var(--amber);">Credit Sales</div>
                                        <div style="font-size:11px;color:var(--text-dim);margin-top:1px;">Tracked on customer accounts — no settlement needed</div>
                                    </div>
                                </div>
                                <span style="font-size:13px;font-family:var(--mono);font-weight:700;color:var(--amber);white-space:nowrap;">
                                    {{ number_format($creditSales) }} RWF
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    @endif

    {{-- ── Navigation ── --}}
    <div class="wiz-nav mt-8 pt-6 flex items-center justify-between" style="border-top:1px solid var(--border);">
        <div>
            @if ($currentStep > 1)
                <button wire:click="prevStep"
                        class="wiz-btn wiz-btn-ghost">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </button>
            @endif
        </div>

        <div>
            @if ($currentStep < 4)
                <button wire:click="nextStep"
                        wire:key="btn-next-step"
                        class="wiz-btn wiz-btn-primary" style="min-width:140px;">
                    <span>Continue</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @else
                <button wire:click="submitClose"
                        wire:key="btn-submit-close"
                        wire:loading.attr="disabled"
                        wire:confirm="Close the day and submit? You can re-open it for corrections until the owner locks the session."
                        class="wiz-btn wiz-btn-amber py-3.5 px-8 rounded-xl">
                    <span wire:loading.remove wire:target="submitClose">
                        <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Close Register & Submit
                    </span>
                    <span wire:loading wire:target="submitClose" style="display:none;">Closing…</span>
                </button>
                <div class="wiz-nav-hint text-right w-full mt-2">
                    <p class="text-xs inline-block" style="color:var(--text-dim);">Re-openable until owner locks the session</p>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ════════════════════════════════════════════
     FLOATING BALANCE WIDGET
     Fixed to bottom-right. Updates every time
     Livewire re-renders (reloadSummary events).
════════════════════════════════════════════ --}}
@php
    // Once the user has physically counted cash, use that as the base (it's the truth).
    // Subtract any amount being sent to owner via MoMo to show what will stay in the drawer.
    $cashBase    = (int) $actualCashCounted > 0 ? (int) $actualCashCounted : (int) ($summary['expected_cash'] ?? 0);
    $floatCash   = $cashBase - (int) $cashToOwnerMomo;
    $floatMomo   = $summary['momo_available']  ?? 0;
    $cashOk      = $floatCash >= 0;
    $momoOk      = $floatMomo >= 0;
@endphp
<div x-data="{
         open: window.innerWidth > 500,
         cashBase: {{ $cashBase }},
         momoDeduction: {{ (int) $cashToOwnerMomo }},
         momoBalance: {{ (int) $floatMomo }},
         get displayCash() { return this.cashBase - this.momoDeduction; }
     }"
     @momo-deduction-changed.window="momoDeduction = $event.detail.val"
     @balance-updated.window="cashBase = $event.detail.cashBase; momoBalance = $event.detail.momoBalance"
     class="wiz-float"
     style="position:fixed;bottom:24px;right:20px;z-index:999;">

    {{-- Collapsed pill --}}
    <div x-show="!open" x-cloak
         @click="open = true"
         class="wiz-float-pill"
         style="cursor:pointer;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
                background:rgba(var(--surface-rgb,255,255,255),0.9);
                border:1px solid var(--border);border-radius:999px;
                padding:8px 14px;box-shadow:0 8px 24px rgba(0,0,0,0.12);
                display:flex;align-items:center;gap:10px;">
        <span style="display:flex;align-items:center;gap:5px;">
            <span class="w-2 h-2 rounded-full inline-block" :style="displayCash >= 0 ? 'background:var(--green)' : 'background:var(--red)'"></span>
            <span class="font-mono text-xs font-bold" :style="displayCash >= 0 ? 'color:var(--green)' : 'color:var(--red)'" x-text="displayCash.toLocaleString()"></span>
        </span>
        <span style="color:var(--border);">|</span>
        <span style="display:flex;align-items:center;gap:5px;">
            <span class="w-2 h-2 rounded-full inline-block" :style="momoBalance >= 0 ? 'background:var(--accent)' : 'background:var(--red)'"></span>
            <span class="font-mono text-xs font-bold" :style="momoBalance >= 0 ? 'color:var(--accent)' : 'color:var(--red)'" x-text="momoBalance.toLocaleString()"></span>
        </span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="color:var(--text-dim);">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
        </svg>
    </div>

    {{-- Expanded panel --}}
    <div x-show="open"
         class="wiz-float-panel"
         style="width:200px;
                backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
                background:rgba(var(--surface-rgb,255,255,255),0.92);
                border:1px solid var(--border);border-radius:16px;
                box-shadow:0 8px 32px rgba(0,0,0,0.12);
                overflow:hidden;">

        {{-- Header --}}
        <div style="padding:9px 12px 8px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);">
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-dim);">Live Balances</span>
            <button @click="open = false" style="background:none;border:none;cursor:pointer;padding:2px;line-height:0;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="color:var(--text-dim);">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        {{-- Cash row --}}
        <div style="padding:9px 12px;border-bottom:1px solid var(--border);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <div style="width:24px;height:24px;border-radius:8px;background:{{ $cashOk ? 'var(--green-dim)' : 'var(--red-dim)' }};
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="12" height="12" fill="none" stroke="{{ $cashOk ? 'var(--green)' : 'var(--red)' }}" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span style="font-size:11px;font-weight:600;color:var(--text-dim);">Cash</span>
                </div>
                <span style="font-size:13px;font-weight:700;font-family:var(--font-mono,monospace);"
                      :style="displayCash >= 0 ? 'color:var(--green)' : 'color:var(--red)'"
                      x-text="displayCash.toLocaleString()"></span>
            </div>
            <div style="height:3px;border-radius:999px;background:var(--border);overflow:hidden;">
                @php $cashBarMax = max(1, ($session->opening_balance ?? 0) + ($summary['total_sales_cash'] ?? 0)); @endphp
                <div :style="`height:100%;border-radius:999px;transition:width 0.4s ease;background:${displayCash>=0?'var(--green)':'var(--red)'};width:${Math.min(100,Math.max(0,Math.round(displayCash/{{ $cashBarMax }}*100)))}%`"></div>
            </div>
        </div>

        {{-- MoMo row --}}
        <div style="padding:9px 12px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <div :style="momoBalance >= 0 ? 'width:24px;height:24px;border-radius:8px;background:var(--accent-dim);display:flex;align-items:center;justify-content:center;flex-shrink:0;' : 'width:24px;height:24px;border-radius:8px;background:var(--red-dim);display:flex;align-items:center;justify-content:center;flex-shrink:0;'">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="2"
                             :style="momoBalance >= 0 ? 'stroke:var(--accent)' : 'stroke:var(--red)'">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span style="font-size:11px;font-weight:600;color:var(--text-dim);">Mobile Money</span>
                </div>
                <span style="font-size:13px;font-weight:700;font-family:var(--font-mono,monospace);"
                      :style="momoBalance >= 0 ? 'color:var(--accent)' : 'color:var(--red)'"
                      x-text="momoBalance.toLocaleString()"></span>
            </div>
            <div style="height:3px;border-radius:999px;background:var(--border);overflow:hidden;">
                @php $momoBarMax = max(1, $summary['total_sales_momo'] ?? 1); @endphp
                <div :style="`height:100%;border-radius:999px;transition:width 0.5s ease;background:${momoBalance>=0?'var(--accent)':'var(--red)'};width:${Math.min(100,Math.max(0,Math.round(momoBalance/{{ $momoBarMax }}*100)))}%`"></div>
            </div>
        </div>

        {{-- Footer hint --}}
        <div style="padding:5px 12px 8px;text-align:center;">
            <span style="font-size:9px;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;">Updates as you record · RWF</span>
        </div>
    </div>
</div>
</div>
