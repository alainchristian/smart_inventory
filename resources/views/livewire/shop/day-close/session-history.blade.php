<div>
<style>
/* ── KPI strip ── */
.sh-kpis { display:grid;grid-template-columns:repeat(4,1fr);gap:0;background:white;
           border-radius:12px;overflow:hidden;border:1px solid var(--border);
           box-shadow:0 1px 4px rgba(0,0,0,0.05);margin-bottom:12px; }
.sh-kpi  { padding:10px 14px;background:white;border-right:1px solid var(--border); }
.sh-kpi:last-child { border-right:none; }
.sh-kpi-lbl { font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-dim);margin-bottom:3px; }
.sh-kpi-val { font-size:17px;font-weight:800;font-family:var(--mono);line-height:1; }
.sh-kpi-sub { font-size:10px;color:var(--text-dim);margin-top:2px; }

/* ── Table ── */
.fo-table-wrap   { border:1px solid var(--border);border-radius:12px;overflow:hidden;background:white;box-shadow:0 1px 4px rgba(0,0,0,0.05);margin-bottom:10px; }
.fo-table-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:thin;scrollbar-color:var(--border) transparent; }
.fo-table-scroll::-webkit-scrollbar { height:4px; }
.fo-table-scroll::-webkit-scrollbar-thumb { background:var(--border);border-radius:2px; }
.fo-table { width:100%;border-collapse:collapse;min-width:780px;font-size:12px;background:white; }
.fo-table thead tr { background:var(--surface2);border-bottom:1px solid var(--border); }
.fo-table thead th {
    padding:8px 12px;font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:0.6px;color:var(--text-dim);text-align:left;white-space:nowrap;
}
.fo-table thead th.fo-num { text-align:right; }
.fo-table tbody tr { border-bottom:1px solid var(--border);transition:background 0.08s;cursor:pointer;background:white; }
.fo-table tbody tr:last-child { border-bottom:none; }
.fo-table tbody tr:hover { background:var(--surface2); }
.fo-table tbody tr.sh-row-active { background:var(--surface2); }
.fo-table td { padding:10px 12px;color:var(--text);vertical-align:middle; }
.fo-table td.fo-num { text-align:right;font-family:var(--mono);font-weight:700;white-space:nowrap;font-size:13px; }
.fo-table tfoot tr { background:var(--surface2);border-top:1px solid var(--border); }
.fo-table tfoot td { padding:8px 12px;font-size:12px;font-weight:700;font-family:var(--mono); }

/* ── Status badges ── */
.sh-badge { display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:999px;font-size:10px;font-weight:700;white-space:nowrap; }
.sh-badge-locked { background:var(--surface);color:var(--text-dim);border:1px solid var(--border); }
.sh-badge-closed { background:var(--amber-dim);color:var(--amber); }
.sh-badge-open   { background:var(--red-dim);color:var(--red); }

/* ── Variance chips ── */
.sh-var-pos  { display:inline-block;padding:2px 7px;border-radius:5px;font-family:var(--mono);font-size:11px;font-weight:700;background:var(--amber-dim);color:var(--amber); }
.sh-var-neg  { display:inline-block;padding:2px 7px;border-radius:5px;font-family:var(--mono);font-size:11px;font-weight:700;background:var(--red-dim);color:var(--red); }
.sh-var-zero { display:inline-block;padding:2px 7px;border-radius:5px;font-family:var(--mono);font-size:11px;font-weight:700;background:var(--green-dim);color:var(--green); }

/* ── Detail button ── */
.fo-detail-btn {
    padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;
    border:1px solid var(--accent);color:var(--accent);background:var(--accent-dim);
    cursor:pointer;font-family:var(--font);white-space:nowrap;
}

/* ── Modal ── */
.fo-modal-wrap {
    position:fixed;inset:0;z-index:100;
    display:flex;align-items:center;justify-content:center;padding:20px;
    background:rgba(10,15,30,0.5);backdrop-filter:blur(3px);
}
.fo-modal {
    background:var(--surface);border-radius:16px;
    box-shadow:0 24px 80px rgba(0,0,0,0.25);
    width:100%;max-width:900px;max-height:88vh;
    display:flex;flex-direction:column;overflow:hidden;
}
.fo-modal-header {
    display:flex;align-items:center;justify-content:space-between;gap:12px;
    padding:14px 20px;border-bottom:1px solid var(--border);background:var(--surface);flex-shrink:0;
}
.fo-modal-body { overflow-y:auto;flex:1;overscroll-behavior:contain;background:var(--surface); }
.fo-modal-close {
    display:flex;align-items:center;justify-content:center;
    width:30px;height:30px;border-radius:8px;border:1px solid var(--border);
    background:var(--surface2);color:var(--text-dim);font-size:20px;
    cursor:pointer;font-family:var(--font);line-height:1;flex-shrink:0;transition:all 0.15s;
}
.fo-modal-close:hover { border-color:var(--red);color:var(--red); }

/* ── Verdict + formula ── */
.fo-verdict {
    display:flex;align-items:center;gap:10px;padding:10px 20px;
    font-size:12px;font-weight:600;border-bottom:1px solid var(--border);flex-shrink:0;
}
.fo-verdict-ok   { background:var(--green-dim);color:var(--green); }
.fo-verdict-err  { background:var(--red-dim);color:var(--red); }
.fo-verdict-warn { background:var(--amber-dim);color:var(--amber); }
.fo-verdict-seal { background:var(--surface2);color:var(--text-dim); }
.fo-verdict-live { background:var(--accent-dim);color:var(--accent); }
.fo-recon-strip {
    padding:10px 20px 12px;background:var(--surface2);border-bottom:1px solid var(--border);
    overflow-x:auto;-webkit-overflow-scrolling:touch;
}
.fo-recon-strip::-webkit-scrollbar { height:3px; }
.fo-recon-strip::-webkit-scrollbar-thumb { background:var(--border);border-radius:2px; }
.fo-recon-eq { display:flex;align-items:flex-end;gap:6px;min-width:max-content; }
.fo-recon-item { display:flex;flex-direction:column;align-items:center;gap:2px; }
.fo-recon-label { font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;color:var(--text-dim); }
.fo-recon-val   { font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text); }
.fo-recon-op    { font-size:14px;font-weight:600;color:var(--text-dim);padding-bottom:2px;flex-shrink:0; }
.fo-recon-eq-sign { font-size:16px;font-weight:700;padding-bottom:2px;flex-shrink:0; }

/* ── 3-col detail grid ── */
.fo-expanded-detail { display:grid;grid-template-columns:1fr 1fr 1fr;gap:0; }
.fo-exp-col { padding:20px 22px; }
.fo-exp-col:not(:last-child) { border-right:1px solid var(--border); }
.fo-exp-col-title { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-dim);margin-bottom:10px; }
.fo-exp-line {
    display:flex;justify-content:space-between;align-items:baseline;
    font-size:12px;padding:5px 0;border-bottom:1px solid var(--border);gap:12px;
}
.fo-exp-line:last-child { border-bottom:none; }
.fo-exp-line-label { color:var(--text-dim);overflow:hidden;text-overflow:ellipsis; }
.fo-exp-line-val { font-family:var(--mono);font-weight:600;white-space:nowrap;flex-shrink:0; }

/* Timeline */
.sh-timeline {
    display:flex;align-items:center;padding:11px 20px;border-bottom:1px solid var(--border);
    gap:6px;overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;flex-wrap:wrap;
    background:var(--surface2);flex-shrink:0;
}
.sh-timeline::-webkit-scrollbar { display:none; }
.sh-tl-node { display:flex;align-items:center;gap:6px;white-space:nowrap;flex-shrink:0; }
.sh-tl-dot  { width:7px;height:7px;border-radius:50%;flex-shrink:0; }
.sh-tl-sep  { width:24px;height:1px;background:var(--border);flex-shrink:0; }

/* Variance alert inside 3-col */
.fo-variance-alert {
    display:flex;justify-content:space-between;align-items:baseline;
    padding:6px 10px;margin:3px -10px;border-radius:6px;font-size:12px;
}

/* Lock button */
.sh-btn-lock {
    display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;
    font-size:11px;font-weight:600;cursor:pointer;
    background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);
    font-family:var(--font);transition:opacity 0.12s;
}
.sh-btn-lock:hover { opacity:0.8; }

/* ── Empty ── */
.sh-empty { text-align:center;padding:56px 20px;border-radius:14px;border:1px solid var(--border);background:white;box-shadow:0 1px 4px rgba(0,0,0,0.05); }

/* ── Responsive ── */
@media (max-width:640px) {
    .sh-kpis { grid-template-columns:repeat(2,1fr); }
    .sh-kpi  { padding:8px 10px; }
    .sh-kpi:nth-child(even) { border-right:none; }
    .sh-kpi:nth-child(1),.sh-kpi:nth-child(2) { border-bottom:1px solid var(--border); }
    .sh-kpi-val { font-size:15px; }
    .fo-expanded-detail { grid-template-columns:1fr; }
    .fo-exp-col { border-right:none !important;padding:14px 16px; }
    .fo-exp-col:not(:last-child) { border-right:none;border-bottom:1px solid var(--border); }
    .fo-modal-wrap { padding:0;align-items:flex-end; }
    .fo-modal { border-radius:16px 16px 0 0;max-height:90vh;max-width:100%; }
    .fo-modal-header { padding:12px 16px; }
}
</style>

{{-- Flash --}}
@if (session()->has('success'))
    <div style="margin-bottom:14px;padding:10px 14px;border-radius:10px;font-size:12px;
                background:var(--green-dim);color:var(--green);border:1px solid var(--green);">{{ session('success') }}</div>
@endif
@if (session()->has('error'))
    <div style="margin-bottom:14px;padding:10px 14px;border-radius:10px;font-size:12px;
                background:var(--red-dim);color:var(--red);border:1px solid var(--red);">{{ session('error') }}</div>
@endif

{{-- KPI strip --}}
@if ($sessions->isNotEmpty())
@php
    $col          = $sessions->getCollection();
    $pageSales    = $col->sum('total_sales');
    $pageExpenses = $col->sum('total_expenses');
    $pageWithdraw = $col->sum('total_withdrawals');
    $pageVariance = $col->sum('cash_variance');
    $varC = $pageVariance < 0 ? 'var(--red)' : ($pageVariance > 0 ? 'var(--amber)' : 'var(--green)');
@endphp
<div class="sh-kpis">
    <div class="sh-kpi">
        <div class="sh-kpi-lbl">Total Sessions</div>
        <div class="sh-kpi-val" style="color:var(--text);">{{ $sessions->total() }}</div>
        <div class="sh-kpi-sub">all time</div>
    </div>
    <div class="sh-kpi">
        <div class="sh-kpi-lbl">Revenue</div>
        <div class="sh-kpi-val" style="color:var(--green);">{{ number_format($pageSales) }}</div>
        <div class="sh-kpi-sub">RWF · this page</div>
    </div>
    <div class="sh-kpi">
        <div class="sh-kpi-lbl">Paid Out</div>
        <div class="sh-kpi-val" style="color:var(--red);">{{ number_format($pageExpenses + $pageWithdraw) }}</div>
        <div class="sh-kpi-sub">exp + withdrawals</div>
    </div>
    <div class="sh-kpi">
        <div class="sh-kpi-lbl">Net Variance</div>
        <div class="sh-kpi-val" style="color:{{ $varC }};">{{ $pageVariance >= 0 ? '+' : '' }}{{ number_format($pageVariance) }}</div>
        <div class="sh-kpi-sub">RWF · this page</div>
    </div>
</div>
@endif

{{-- Empty --}}
@if ($sessions->isEmpty())
    <div class="sh-empty">
        <div style="width:46px;height:46px;border-radius:12px;background:var(--surface);border:1px solid var(--border);
                    display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <svg width="20" height="20" fill="none" stroke="var(--text-dim)" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px;">No sessions yet</div>
        <div style="font-size:12px;color:var(--text-dim);">Closed sessions will appear here.</div>
    </div>
@else

{{-- ── Sessions table ── --}}
@php
    $col          = $sessions->getCollection();
    $totalSales   = $col->sum('total_sales');
    $totalExp     = $col->sum('total_expenses');
    $totalWd      = $col->sum('total_withdrawals');
    $totalVar     = $col->sum('cash_variance');
    $totalOpening = $col->sum('opening_balance');
@endphp

<div class="fo-table-wrap">
    <div class="fo-table-scroll">
        <table class="fo-table">
            <thead>
                <tr>
                    <th style="min-width:160px;">Date</th>
                    <th style="min-width:100px;">Status</th>
                    <th class="fo-num" style="min-width:110px;">Opening</th>
                    <th class="fo-num" style="min-width:110px;color:var(--green);">Sales</th>
                    <th class="fo-num" style="min-width:110px;color:var(--red);">Expenses</th>
                    <th class="fo-num" style="min-width:120px;color:var(--amber);">Withdrawals</th>
                    <th class="fo-num" style="min-width:110px;">Variance</th>
                    <th style="min-width:130px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sessions as $session)
                @php
                    $v      = $session->cash_variance ?? 0;
                    $isLock = $session->isLocked();
                    $isOpen = $session->isOpen();
                    $isExp  = $expandedId === $session->id;
                @endphp
                <tr class="{{ $isExp ? 'sh-row-active' : '' }}"
                    wire:click="toggleExpand({{ $session->id }})">

                    {{-- Date --}}
                    <td style="white-space:nowrap;">
                        <div style="font-weight:700;font-size:13px;color:var(--text);">
                            {{ $session->session_date->format('d M Y') }}
                        </div>
                        <div style="font-size:11px;color:var(--text-dim);margin-top:1px;">
                            {{ $session->session_date->format('D') }}
                            @if ($session->opened_at)
                                · {{ $session->opened_at->format('H:i') }}
                                @if ($session->closed_at) –{{ $session->closed_at->format('H:i') }} @endif
                            @endif
                        </div>
                    </td>

                    {{-- Status --}}
                    <td>
                        @if ($isLock)
                            <span class="sh-badge sh-badge-locked">
                                <svg width="8" height="8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Locked
                            </span>
                        @elseif ($isOpen)
                            <span class="sh-badge sh-badge-open">● Open</span>
                        @else
                            <span class="sh-badge sh-badge-closed">✓ Closed</span>
                        @endif
                    </td>

                    {{-- Opening --}}
                    <td class="fo-num" style="color:var(--text-dim);">
                        {{ number_format($session->opening_balance ?? 0) }}
                    </td>

                    {{-- Sales --}}
                    <td class="fo-num" style="color:{{ ($session->total_sales ?? 0) > 0 ? 'var(--green)' : 'var(--text-dim)' }};">
                        {{ number_format($session->total_sales ?? 0) }}
                    </td>

                    {{-- Expenses --}}
                    <td class="fo-num" style="color:{{ ($session->total_expenses ?? 0) > 0 ? 'var(--red)' : 'var(--text-dim)' }};">
                        {{ number_format($session->total_expenses ?? 0) }}
                    </td>

                    {{-- Withdrawals --}}
                    <td class="fo-num" style="color:{{ ($session->total_withdrawals ?? 0) > 0 ? 'var(--amber)' : 'var(--text-dim)' }};">
                        {{ number_format($session->total_withdrawals ?? 0) }}
                    </td>

                    {{-- Variance --}}
                    <td class="fo-num">
                        @if ($isOpen)
                            <span style="font-size:11px;color:var(--text-dim);">—</span>
                        @else
                            <span class="{{ $v < 0 ? 'sh-var-neg' : ($v > 0 ? 'sh-var-pos' : 'sh-var-zero') }}">
                                {{ $v >= 0 ? '+' : '' }}{{ number_format($v) }}
                            </span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td style="text-align:center;" wire:click.stop="">
                        <div style="display:flex;align-items:center;justify-content:center;gap:6px;">
                            @if ($isOpen)
                                <a href="{{ route('shop.day-close.close', ['session' => $session->id]) }}"
                                   style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;
                                          background:var(--amber);color:#1a1a1a;text-decoration:none;white-space:nowrap;">
                                    Close
                                </a>
                            @else
                                @if ($isLock === false && auth()->user()->isOwner())
                                    <button wire:click="lockSession({{ $session->id }})"
                                            wire:confirm="Lock this session permanently? This cannot be undone."
                                            class="sh-btn-lock">
                                        <svg width="9" height="9" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Lock
                                    </button>
                                @endif
                                <button class="fo-detail-btn"
                                        wire:click="toggleExpand({{ $session->id }})">
                                    Details
                                </button>
                            @endif
                        </div>
                    </td>

                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="font-size:11px;font-weight:600;color:var(--text-dim);font-family:var(--font);">
                        Page totals · {{ $sessions->count() }} session{{ $sessions->count() !== 1 ? 's' : '' }}
                    </td>
                    <td class="fo-num" style="color:var(--text-dim);">{{ number_format($totalOpening) }}</td>
                    <td class="fo-num" style="color:var(--green);">{{ number_format($totalSales) }}</td>
                    <td class="fo-num" style="color:var(--red);">{{ number_format($totalExp) }}</td>
                    <td class="fo-num" style="color:var(--amber);">{{ number_format($totalWd) }}</td>
                    <td class="fo-num" style="color:{{ $totalVar < 0 ? 'var(--red)' : ($totalVar > 0 ? 'var(--amber)' : 'var(--text-dim)') }};">
                        {{ $totalVar >= 0 ? '+' : '' }}{{ number_format($totalVar) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Pagination --}}
<div>{{ $sessions->links() }}</div>

@endif {{-- sessions not empty --}}

{{-- ── Session detail modal ── --}}
@if ($expandedId && $expandedSession)
@php
    $sess   = $expandedSession;
    $sv     = $sess->cash_variance ?? 0;
    $isOpen = $sess->isOpen();
    $isLock = $sess->isLocked();
@endphp
<div class="fo-modal-wrap" wire:click="toggleExpand({{ $expandedId }})">
    <div class="fo-modal" wire:click.stop>

        {{-- Header --}}
        <div class="fo-modal-header">
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--text);letter-spacing:-0.2px;">
                    {{ $sess->session_date->format('d M Y') }}
                    @if ($sess->shop) · {{ $sess->shop->name }} @endif
                </div>
                <div style="font-size:12px;color:var(--text-dim);margin-top:4px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span>{{ $sess->session_date->format('l') }}</span>
                    @if ($sess->opened_at)
                        <span>·</span>
                        <span>Opened {{ $sess->opened_at->format('H:i') }}</span>
                    @endif
                    @if ($sess->closed_at)
                        <span>–</span>
                        <span>Closed {{ $sess->closed_at->format('H:i') }}</span>
                    @endif
                </div>
            </div>
            <button class="fo-modal-close" wire:click="toggleExpand({{ $expandedId }})">×</button>
        </div>

        {{-- Verdict banner --}}
        @if ($isOpen)
            <div class="fo-verdict fo-verdict-live">
                <svg style="width:13px;height:13px;flex-shrink:0;" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="6"/></svg>
                Session still open — figures are live and may change
            </div>
        @elseif ($sv < 0)
            <div class="fo-verdict fo-verdict-err">
                <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                Cash shortage of {{ number_format(abs($sv)) }} RWF — counted less than expected
            </div>
        @elseif ($sv > 0)
            <div class="fo-verdict fo-verdict-warn">
                <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                Cash surplus of {{ number_format($sv) }} RWF — counted more than expected
            </div>
        @elseif ($isLock)
            <div class="fo-verdict fo-verdict-seal">
                🔒 Session sealed and balanced — records are immutable
            </div>
        @else
            <div class="fo-verdict fo-verdict-ok">
                <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
                Cash balanced — all money accounted for
            </div>
        @endif

        {{-- Timeline strip --}}
        <div class="sh-timeline">
            @if ($sess->opened_at)
                <div class="sh-tl-node">
                    <div class="sh-tl-dot" style="background:var(--green);"></div>
                    <span style="font-size:11px;color:var(--text-dim);">Opened</span>
                    <span style="font-size:11px;font-weight:700;color:var(--text);">{{ $sess->opened_at->format('H:i') }}</span>
                    @if ($sess->openedBy)
                        <span style="font-size:11px;color:var(--text-dim);">by {{ $sess->openedBy->name }}</span>
                    @endif
                </div>
            @endif
            @if ($sess->closed_at)
                <div class="sh-tl-sep"></div>
                <div class="sh-tl-node">
                    <div class="sh-tl-dot" style="background:var(--amber);"></div>
                    <span style="font-size:11px;color:var(--text-dim);">Closed</span>
                    <span style="font-size:11px;font-weight:700;color:var(--text);">{{ $sess->closed_at->format('H:i') }}</span>
                    @if ($sess->closedBy)
                        <span style="font-size:11px;color:var(--text-dim);">by {{ $sess->closedBy->name }}</span>
                    @endif
                </div>
            @endif
            @if ($sess->locked_at)
                <div class="sh-tl-sep"></div>
                <div class="sh-tl-node">
                    <div class="sh-tl-dot" style="background:var(--text-dim);"></div>
                    <span style="font-size:11px;color:var(--text-dim);">Locked</span>
                    <span style="font-size:11px;font-weight:700;color:var(--text);">{{ $sess->locked_at->format('d M · H:i') }}</span>
                    @if ($sess->lockedBy)
                        <span style="font-size:11px;color:var(--text-dim);">by {{ $sess->lockedBy->name }}</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Cash drawer formula strip (closed sessions only) --}}
        @if (!$isOpen)
        @php
            $fCashSales = $sess->total_sales_cash      ?? 0;
            $fCashRep   = $sess->total_repayments_cash  ?? 0;
            $fCashRef   = $sess->total_refunds_cash     ?? 0;
            $fCashExp   = $sess->total_expenses_cash    ?? 0;
            $fCashWd    = $sess->total_withdrawals_cash ?? 0;
            $fCashDep   = $sess->cash_deposits          ?? 0;
            $fExpected  = $sess->expected_cash          ?? 0;
            $fCounted   = $sess->actual_cash_counted;
            $fOpening   = $sess->opening_balance        ?? 0;
        @endphp
        <div class="fo-recon-strip">
            <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-dim);margin-bottom:8px;">
                Cash drawer formula
            </div>
            <div class="fo-recon-eq">
                <div class="fo-recon-item">
                    <span class="fo-recon-label">Opening</span>
                    <span class="fo-recon-val">{{ number_format($fOpening) }}</span>
                </div>
                @if ($fCashSales > 0)
                    <span class="fo-recon-op" style="color:var(--accent);">+</span>
                    <div class="fo-recon-item">
                        <span class="fo-recon-label">Cash Sales</span>
                        <span class="fo-recon-val" style="color:var(--accent);">{{ number_format($fCashSales) }}</span>
                    </div>
                @endif
                @if ($fCashRep > 0)
                    <span class="fo-recon-op" style="color:var(--accent);">+</span>
                    <div class="fo-recon-item">
                        <span class="fo-recon-label">Repayments</span>
                        <span class="fo-recon-val" style="color:var(--accent);">{{ number_format($fCashRep) }}</span>
                    </div>
                @endif
                @if ($fCashRef > 0)
                    <span class="fo-recon-op" style="color:var(--amber);">−</span>
                    <div class="fo-recon-item">
                        <span class="fo-recon-label">Refunds</span>
                        <span class="fo-recon-val" style="color:var(--amber);">{{ number_format($fCashRef) }}</span>
                    </div>
                @endif
                @if ($fCashExp > 0)
                    <span class="fo-recon-op" style="color:var(--red);">−</span>
                    <div class="fo-recon-item">
                        <span class="fo-recon-label">Expenses</span>
                        <span class="fo-recon-val" style="color:var(--red);">{{ number_format($fCashExp) }}</span>
                    </div>
                @endif
                @if ($fCashWd > 0)
                    <span class="fo-recon-op" style="color:var(--amber);">−</span>
                    <div class="fo-recon-item">
                        <span class="fo-recon-label">Withdrawals</span>
                        <span class="fo-recon-val" style="color:var(--amber);">{{ number_format($fCashWd) }}</span>
                    </div>
                @endif
                @if ($fCashDep > 0)
                    <span class="fo-recon-op" style="color:var(--accent);">−</span>
                    <div class="fo-recon-item">
                        <span class="fo-recon-label">Banked</span>
                        <span class="fo-recon-val" style="color:var(--accent);">{{ number_format($fCashDep) }}</span>
                    </div>
                @endif
                <span class="fo-recon-eq-sign" style="color:var(--text-dim);">=</span>
                <div class="fo-recon-item">
                    <span class="fo-recon-label">Expected</span>
                    <span class="fo-recon-val" style="font-size:13px;">{{ number_format($fExpected) }}</span>
                </div>
                <span class="fo-recon-eq-sign"
                      style="color:{{ $sv === 0 ? 'var(--green)' : ($sv < 0 ? 'var(--red)' : 'var(--amber)') }};">
                    {{ $sv === 0 ? '=' : '≠' }}
                </span>
                <div class="fo-recon-item">
                    <span class="fo-recon-label">Counted</span>
                    <span class="fo-recon-val" style="font-size:13px;
                          color:{{ $sv === 0 ? 'var(--green)' : ($sv < 0 ? 'var(--red)' : 'var(--amber)') }};">
                        {{ $fCounted !== null ? number_format($fCounted) : '—' }}
                    </span>
                </div>
                @if ($sv !== 0)
                    <span class="fo-recon-eq-sign" style="color:var(--text-dim);">·</span>
                    <div class="fo-recon-item">
                        <span class="fo-recon-label">{{ $sv < 0 ? 'Shortage' : 'Surplus' }}</span>
                        <span class="fo-recon-val" style="font-size:13px;font-weight:800;
                              color:{{ $sv < 0 ? 'var(--red)' : 'var(--amber)' }};">
                            {{ ($sv > 0 ? '+' : '') . number_format($sv) }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ── 3-col detail body ── --}}
        <div class="fo-modal-body">
            <div class="fo-expanded-detail">

                {{-- Revenue by Channel --}}
                <div class="fo-exp-col">
                    <div class="fo-exp-col-title">Revenue by Channel</div>
                    @foreach([
                        ['Cash',          $sess->total_sales_cash          ?? 0, 'var(--green)'],
                        ['Mobile Money',  $sess->total_sales_momo          ?? 0, 'var(--accent)'],
                        ['Card',          $sess->total_sales_card          ?? 0, 'var(--accent)'],
                        ['Credit',        $sess->total_sales_credit        ?? 0, 'var(--amber)'],
                        ['Bank Transfer', $sess->total_sales_bank_transfer ?? 0, 'var(--accent)'],
                    ] as [$ch, $chv, $chc])
                    @if ($chv > 0)
                    <div class="fo-exp-line">
                        <span class="fo-exp-line-label">{{ $ch }}</span>
                        <span class="fo-exp-line-val" style="color:{{ $chc }};">{{ number_format($chv) }}</span>
                    </div>
                    @endif
                    @endforeach
                    <div class="fo-exp-line" style="border-top:1.5px solid var(--border);margin-top:4px;">
                        <span style="font-weight:700;color:var(--text);">Total Sales</span>
                        <span class="fo-exp-line-val" style="color:var(--green);font-weight:700;font-size:13px;">{{ number_format($sess->total_sales ?? 0) }}</span>
                    </div>
                    @if (($sess->total_repayments_cash ?? 0) > 0 || ($sess->total_repayments_momo ?? 0) > 0)
                    <div class="fo-exp-col-title" style="margin-top:16px;">Credit Repayments</div>
                    @if (($sess->total_repayments_cash ?? 0) > 0)
                    <div class="fo-exp-line">
                        <span class="fo-exp-line-label">Cash</span>
                        <span class="fo-exp-line-val" style="color:var(--green);">{{ number_format($sess->total_repayments_cash) }}</span>
                    </div>
                    @endif
                    @if (($sess->total_repayments_momo ?? 0) > 0)
                    <div class="fo-exp-line">
                        <span class="fo-exp-line-label">Mobile Money</span>
                        <span class="fo-exp-line-val" style="color:var(--accent);">{{ number_format($sess->total_repayments_momo) }}</span>
                    </div>
                    @endif
                    @endif
                </div>

                {{-- Expenses + Withdrawals --}}
                <div class="fo-exp-col">
                    <div class="fo-exp-col-title">
                        Expenses
                        @if (isset($sess->expenses) && $sess->expenses->whereNull('deleted_at')->count())
                            <span style="margin-left:4px;padding:1px 6px;border-radius:4px;font-size:10px;
                                         background:var(--red-dim);color:var(--red);text-transform:none;letter-spacing:0;">
                                {{ $sess->expenses->whereNull('deleted_at')->count() }}
                            </span>
                        @endif
                    </div>
                    @if (isset($sess->expenses))
                    @forelse ($sess->expenses->whereNull('deleted_at') as $exp)
                    <div class="fo-exp-line">
                        <span class="fo-exp-line-label">
                            @if ($exp->is_system_generated)
                                <span style="font-size:9px;padding:1px 4px;border-radius:3px;
                                             background:var(--amber-dim);color:var(--amber);margin-right:3px;">auto</span>
                            @endif
                            {{ $exp->category->name ?? '—' }}
                            @if ($exp->description)
                                <span style="opacity:0.7;"> — {{ Str::limit($exp->description, 18) }}</span>
                            @endif
                        </span>
                        <span class="fo-exp-line-val" style="color:var(--red);">{{ number_format($exp->amount) }}</span>
                    </div>
                    @empty
                    <div style="font-size:11px;color:var(--text-dim);padding:6px 0;">None recorded</div>
                    @endforelse
                    @if ($sess->expenses->whereNull('deleted_at')->count() > 0)
                    <div class="fo-exp-line" style="border-top:1.5px solid var(--border);margin-top:4px;">
                        <span style="font-weight:700;color:var(--text);">Total</span>
                        <span class="fo-exp-line-val" style="color:var(--red);font-weight:700;">{{ number_format($sess->total_expenses ?? 0) }}</span>
                    </div>
                    @endif
                    @endif

                    <div class="fo-exp-col-title" style="margin-top:18px;">
                        Owner Withdrawals
                        @if (isset($sess->ownerWithdrawals) && $sess->ownerWithdrawals->whereNull('deleted_at')->count())
                            <span style="margin-left:4px;padding:1px 6px;border-radius:4px;font-size:10px;
                                         background:var(--amber-dim);color:var(--amber);text-transform:none;letter-spacing:0;">
                                {{ $sess->ownerWithdrawals->whereNull('deleted_at')->count() }}
                            </span>
                        @endif
                    </div>
                    @if (isset($sess->ownerWithdrawals))
                    @forelse ($sess->ownerWithdrawals->whereNull('deleted_at') as $wd)
                    <div class="fo-exp-line">
                        <span class="fo-exp-line-label">
                            {{ Str::limit($wd->reason ?? 'Owner draw', 22) }}
                            <span style="opacity:0.7;font-size:10px;"> ({{ ucfirst($wd->isCash() ? 'Cash' : 'MoMo') }})</span>
                        </span>
                        <span class="fo-exp-line-val" style="color:var(--amber);">{{ number_format($wd->amount) }}</span>
                    </div>
                    @empty
                    <div style="font-size:11px;color:var(--text-dim);padding:6px 0;">None recorded</div>
                    @endforelse
                    @if ($sess->ownerWithdrawals->whereNull('deleted_at')->count() > 0)
                    <div class="fo-exp-line" style="border-top:1.5px solid var(--border);margin-top:4px;">
                        <span style="font-weight:700;color:var(--text);">Total</span>
                        <span class="fo-exp-line-val" style="color:var(--amber);font-weight:700;">{{ number_format($sess->total_withdrawals ?? 0) }}</span>
                    </div>
                    @endif
                    @endif
                </div>

                {{-- Cash Reconciliation --}}
                <div class="fo-exp-col">
                    <div class="fo-exp-col-title">Cash Reconciliation</div>
                    @foreach([
                        ['Opening balance', $sess->opening_balance     ?? null, 'var(--text-dim)', false],
                        ['Expected cash',   $sess->expected_cash       ?? null, 'var(--text)',     false],
                        ['Counted',         $sess->actual_cash_counted ?? null, 'var(--text)',     false],
                        ['Variance',        $sv, $sv < 0 ? 'var(--red)' : ($sv > 0 ? 'var(--amber)' : 'var(--text-dim)'), $sv !== 0],
                    ] as [$rl, $rlv, $rlc, $isAlert])
                        @if ($isAlert)
                            <div class="fo-variance-alert"
                                 style="background:{{ $sv < 0 ? 'var(--red-dim)' : 'var(--amber-dim)' }};">
                                <span style="font-weight:700;color:{{ $rlc }};">{{ $rl }}</span>
                                <span style="font-weight:700;font-family:var(--mono);font-size:13px;color:{{ $rlc }};">
                                    {{ ($sv > 0 ? '+' : '') . number_format($sv) }}
                                    <span style="font-size:10px;font-weight:500;"> RWF</span>
                                </span>
                            </div>
                        @else
                            <div class="fo-exp-line">
                                <span class="fo-exp-line-label">{{ $rl }}</span>
                                <span class="fo-exp-line-val" style="color:{{ $rlc }};">
                                    {{ $rlv !== null ? number_format($rlv) : '—' }}
                                    @if ($rlv !== null) <span style="font-size:10px;font-weight:400;color:var(--text-dim);"> RWF</span> @endif
                                </span>
                            </div>
                        @endif
                    @endforeach

                    @if (($sess->cash_to_owner_momo ?? 0) > 0)
                    <div class="fo-exp-line">
                        <span class="fo-exp-line-label">→ MoMo to owner</span>
                        <span class="fo-exp-line-val" style="color:var(--accent);">{{ number_format($sess->cash_to_owner_momo) }} <span style="font-size:10px;font-weight:400;color:var(--text-dim);">RWF</span></span>
                    </div>
                    @endif

                    @if (($sess->total_bank_deposits ?? 0) > 0)
                    <div class="fo-exp-line">
                        <span class="fo-exp-line-label">Banked</span>
                        <span class="fo-exp-line-val" style="color:var(--accent);">{{ number_format($sess->total_bank_deposits) }} <span style="font-size:10px;font-weight:400;color:var(--text-dim);">RWF</span></span>
                    </div>
                    @endif

                    <div class="fo-exp-line" style="border-top:1.5px solid var(--border);margin-top:4px;">
                        <span style="font-weight:700;color:var(--text);">Retained in till</span>
                        <span class="fo-exp-line-val" style="color:var(--green);font-weight:700;font-size:13px;">
                            {{ number_format($sess->cash_retained ?? 0) }} <span style="font-size:10px;font-weight:400;color:var(--text-dim);">RWF</span>
                        </span>
                    </div>

                    {{-- Bank deposits list --}}
                    @if (isset($sess->bankDeposits) && $sess->bankDeposits->isNotEmpty())
                    <div class="fo-exp-col-title" style="margin-top:16px;">Bank Deposits</div>
                    @foreach ($sess->bankDeposits as $dep)
                    <div class="fo-exp-line">
                        <span class="fo-exp-line-label">{{ $dep->deposited_at?->format('H:i') ?? '—' }}</span>
                        <span class="fo-exp-line-val" style="color:var(--accent);">{{ number_format($dep->amount) }} <span style="font-size:10px;font-weight:400;color:var(--text-dim);">RWF</span></span>
                    </div>
                    @endforeach
                    @endif

                    {{-- Notes --}}
                    @if ($sess->notes)
                    <div style="margin-top:12px;padding:8px 11px;border-radius:8px;font-size:11px;font-style:italic;
                                background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);">
                        "{{ $sess->notes }}"
                    </div>
                    @endif

                    {{-- Locked stamp --}}
                    @if ($isLock)
                    <div style="margin-top:12px;padding:8px 10px;border-radius:7px;
                                background:var(--surface2);border:1px solid var(--border);">
                        <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;color:var(--text-dim);">Locked by</div>
                        <div style="font-size:12px;color:var(--text);margin-top:3px;">
                            {{ $sess->lockedBy->name ?? '—' }} · {{ $sess->locked_at?->format('d M Y H:i') }}
                        </div>
                    </div>
                    @endif
                </div>

            </div>{{-- /fo-expanded-detail --}}
        </div>{{-- /fo-modal-body --}}

    </div>{{-- /fo-modal --}}
</div>{{-- /fo-modal-wrap --}}
@endif

</div>
