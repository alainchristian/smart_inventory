<div>
<style>
/* ── KPI strip ── */
.sh-kpi-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--border);border-radius:14px;overflow:hidden;border:1px solid var(--border);margin-bottom:24px; }
.sh-kpi      { padding:16px 20px;background:var(--surface-raised); }
.sh-kpi-label{ font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-faint);margin-bottom:6px; }
.sh-kpi-val  { font-size:22px;font-weight:800;font-family:var(--font-mono);line-height:1; }
.sh-kpi-sub  { font-size:11px;color:var(--text-faint);margin-top:4px; }

/* ── Table wrapper ── */
.sh-table-wrap { border-radius:14px;border:1px solid var(--border);overflow:hidden; }
.sh-scroll     { overflow-x:auto;-webkit-overflow-scrolling:touch; }

/* ── Table ── */
.sh-table    { width:100%;border-collapse:collapse;min-width:720px; }
.sh-thead th { padding:10px 16px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;
               color:var(--text-faint);background:var(--surface-raised);text-align:left;white-space:nowrap;
               border-bottom:1px solid var(--border); }
.sh-thead th:not(:last-child) { border-right:1px solid var(--border); }
.sh-thead th.sh-num { text-align:right; }

/* ── Rows ── */
.sh-row      { border-bottom:1px solid var(--border);transition:background 0.12s; }
.sh-row:last-child { border-bottom:none; }
.sh-row:hover { background:var(--surface-raised); }
.sh-row.sh-open-row { background:color-mix(in srgb, var(--red) 5%, transparent); }
.sh-row.sh-open-row:hover { background:color-mix(in srgb, var(--red) 8%, transparent); }
.sh-row.sh-expanded { background:var(--surface-raised); }

.sh-td       { padding:13px 16px;font-size:13px;color:var(--text);vertical-align:middle; }
.sh-td:not(:last-child) { border-right:1px solid var(--border); }
.sh-td.sh-num { text-align:right; }

/* ── Status badges ── */
.sh-badge        { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;font-size:10px;font-weight:700;white-space:nowrap; }
.sh-badge-locked { background:var(--surface);color:var(--text-dim);border:1px solid var(--border); }
.sh-badge-closed { background:var(--amber-dim);color:var(--amber); }
.sh-badge-open   { background:var(--red-dim);color:var(--red);border:1px solid var(--red); }

/* ── Variance ── */
.sh-var-pos  { color:var(--amber);font-weight:700;font-family:var(--font-mono); }
.sh-var-neg  { color:var(--red);font-weight:700;font-family:var(--font-mono); }
.sh-var-zero { color:var(--green);font-weight:700;font-family:var(--font-mono); }

/* ── Action buttons ── */
.sh-btn        { display:inline-flex;align-items:center;gap:4px;padding:5px 12px;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;border:none;white-space:nowrap;text-decoration:none; }
.sh-btn-ghost  { background:var(--surface);color:var(--text-dim);border:1px solid var(--border); }
.sh-btn-accent { background:var(--accent-dim);color:var(--accent); }
.sh-btn-amber  { background:var(--amber);color:#1a1a1a;font-weight:700; }
.sh-btn-lock   { background:var(--surface);color:var(--text-dim);border:1px solid var(--border); }

/* ── Detail panel (OUTSIDE scroll, full width) ── */
.sh-detail-panel {
    border-top:1px solid var(--border);
    background:var(--surface);
}
.sh-timeline {
    display:flex;align-items:center;gap:14px;flex-wrap:wrap;
    padding:12px 20px;border-bottom:1px solid var(--border);
}
.sh-tl-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }
.sh-tl-sep { width:20px;height:1px;background:var(--border);flex-shrink:0; }

.sh-recon-grid { display:grid;grid-template-columns:repeat(3,1fr); }
.sh-recon-col  { padding:18px 20px; }
.sh-recon-col:not(:last-child) { border-right:1px solid var(--border); }
.sh-recon-title { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-dim);margin-bottom:14px; }
.sh-line-item  { display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid var(--border); }
.sh-line-item:last-child { border-bottom:none; }

/* ── Responsive ── */
@media (max-width:640px) {
    .sh-kpi-grid  { grid-template-columns:repeat(2,1fr); }
    .sh-recon-grid { grid-template-columns:1fr; }
    .sh-recon-col:not(:last-child) { border-right:none;border-bottom:1px solid var(--border); }
}
</style>

{{-- Flash messages --}}
@if (session()->has('success'))
    <div style="margin-bottom:14px;padding:10px 14px;border-radius:10px;font-size:12px;
                background:var(--green-dim);color:var(--green);border:1px solid var(--green);">{{ session('success') }}</div>
@endif
@if (session()->has('error'))
    <div style="margin-bottom:14px;padding:10px 14px;border-radius:10px;font-size:12px;
                background:var(--red-dim);color:var(--red);border:1px solid var(--red);">{{ session('error') }}</div>
@endif

{{-- ── KPI strip ── --}}
@if ($sessions->isNotEmpty())
    @php
        $col          = $sessions->getCollection();
        $pageSales    = $col->sum('total_sales');
        $pageExpenses = $col->sum('total_expenses');
        $pageWithdraw = $col->sum('total_withdrawals');
        $pageVariance = $col->sum('cash_variance');
        $varColor     = $pageVariance < 0 ? 'var(--red)' : ($pageVariance > 0 ? 'var(--amber)' : 'var(--green)');
    @endphp
    <div class="sh-kpi-grid">
        <div class="sh-kpi">
            <div class="sh-kpi-label">Total Sessions</div>
            <div class="sh-kpi-val" style="color:var(--text);">{{ $sessions->total() }}</div>
            <div class="sh-kpi-sub">all time</div>
        </div>
        <div class="sh-kpi">
            <div class="sh-kpi-label">Sales (page)</div>
            <div class="sh-kpi-val" style="color:var(--green);">{{ number_format($pageSales) }}</div>
            <div class="sh-kpi-sub">RWF</div>
        </div>
        <div class="sh-kpi">
            <div class="sh-kpi-label">Paid Out (page)</div>
            <div class="sh-kpi-val" style="color:var(--red);">{{ number_format($pageExpenses + $pageWithdraw) }}</div>
            <div class="sh-kpi-sub">expenses + withdrawals</div>
        </div>
        <div class="sh-kpi">
            <div class="sh-kpi-label">Net Variance</div>
            <div class="sh-kpi-val" style="color:{{ $varColor }};">{{ $pageVariance >= 0 ? '+' : '' }}{{ number_format($pageVariance) }}</div>
            <div class="sh-kpi-sub">RWF</div>
        </div>
    </div>
@endif

{{-- ── Empty state ── --}}
@if ($sessions->isEmpty())
    <div style="text-align:center;padding:60px 0;border-radius:14px;border:1px solid var(--border);background:var(--surface-raised);">
        <div style="font-size:32px;margin-bottom:10px;">📋</div>
        <div style="font-size:14px;font-weight:600;color:var(--text-dim);">No sessions yet</div>
        <div style="font-size:12px;color:var(--text-faint);margin-top:4px;">Closed sessions will appear here</div>
    </div>
@else

    @foreach ($sessions as $session)
        @php
            $v        = $session->cash_variance ?? 0;
            $isLocked = $session->isLocked();
            $isClosed = $session->isClosed();
            $isOpen   = $session->isOpen();
            $isExp    = $expandedId === $session->id;
        @endphp

        {{-- ── Per-session card: table + optional detail panel ── --}}
        <div class="sh-table-wrap" style="margin-bottom:10px;{{ $isOpen ? 'border-color:var(--red);' : '' }}">

            {{-- Scrollable table row --}}
            <div class="sh-scroll">
                <table class="sh-table">
                    @if ($loop->first)
                        <thead class="sh-thead">
                            <tr>
                                <th style="width:190px;">Date</th>
                                <th style="width:110px;">Status</th>
                                <th class="sh-num">Opening</th>
                                <th class="sh-num">Sales</th>
                                <th class="sh-num">Expenses</th>
                                <th class="sh-num">Withdrawals</th>
                                <th class="sh-num" style="width:130px;">Variance</th>
                                <th style="width:160px;text-align:center;">Actions</th>
                            </tr>
                        </thead>
                    @endif
                    <tbody>
                        <tr class="sh-row {{ $isOpen ? 'sh-open-row' : '' }} {{ $isExp ? 'sh-expanded' : '' }}">

                            {{-- Date --}}
                            <td class="sh-td">
                                <div style="font-weight:700;font-size:13px;color:var(--text);">
                                    {{ $session->session_date->format('d M Y') }}
                                </div>
                                <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">
                                    {{ $session->session_date->format('l') }}
                                    @if($session->opened_at)
                                        · {{ $session->opened_at->format('H:i') }}
                                        @if($session->closed_at)–{{ $session->closed_at->format('H:i') }}@endif
                                    @endif
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="sh-td">
                                @if ($isLocked)
                                    <span class="sh-badge sh-badge-locked">
                                        <svg width="9" height="9" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Locked
                                    </span>
                                @elseif ($isOpen)
                                    <span class="sh-badge sh-badge-open">● Not Closed</span>
                                @else
                                    <span class="sh-badge sh-badge-closed">Closed</span>
                                @endif
                            </td>

                            {{-- Numbers --}}
                            <td class="sh-td sh-num" style="color:var(--text-dim);font-family:var(--font-mono);">
                                {{ number_format($session->opening_balance ?? 0) }}
                            </td>
                            <td class="sh-td sh-num" style="color:var(--green);font-family:var(--font-mono);font-weight:700;">
                                {{ number_format($session->total_sales ?? 0) }}
                            </td>
                            <td class="sh-td sh-num" style="color:var(--red);font-family:var(--font-mono);">
                                {{ number_format($session->total_expenses ?? 0) }}
                            </td>
                            <td class="sh-td sh-num" style="color:var(--accent);font-family:var(--font-mono);">
                                {{ number_format($session->total_withdrawals ?? 0) }}
                            </td>

                            {{-- Variance --}}
                            <td class="sh-td sh-num">
                                @if ($isOpen)
                                    <span style="font-size:11px;color:var(--text-faint);">—</span>
                                @else
                                    <span class="{{ $v < 0 ? 'sh-var-neg' : ($v > 0 ? 'sh-var-pos' : 'sh-var-zero') }}">
                                        {{ $v >= 0 ? '+' : '' }}{{ number_format($v) }}
                                    </span>
                                    <div style="font-size:10px;color:var(--text-faint);margin-top:1px;">RWF</div>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="sh-td" style="text-align:center;">
                                <div style="display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">
                                    @if ($isOpen)
                                        <a href="{{ route('shop.session.close', ['session' => $session->id]) }}"
                                           class="sh-btn sh-btn-amber">
                                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Close
                                        </a>
                                    @else
                                        @if ($isClosed && auth()->user()->isOwner())
                                            <button wire:click="lockSession({{ $session->id }})"
                                                    wire:confirm="Lock this session? It will become immutable."
                                                    class="sh-btn sh-btn-lock">
                                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                                Lock
                                            </button>
                                        @endif
                                        <button wire:click="toggleExpand({{ $session->id }})"
                                                class="sh-btn sh-btn-accent">
                                            @if ($isExp)
                                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                </svg>
                                                Hide
                                            @else
                                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                                Details
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>{{-- /sh-scroll — detail panel is OUTSIDE, so it never scrolls horizontally --}}

            {{-- ── Detail panel: full width, not inside the scroll area ── --}}
            @if ($isExp)
                <div class="sh-detail-panel">

                    {{-- Timeline --}}
                    <div class="sh-timeline">
                        @if($session->opened_at)
                            <div style="display:flex;align-items:center;gap:6px;">
                                <div class="sh-tl-dot" style="background:var(--green);"></div>
                                <span style="font-size:11px;color:var(--text-dim);">Opened</span>
                                <span style="font-size:11px;font-weight:700;color:var(--text);">{{ $session->opened_at->format('H:i') }}</span>
                                <span style="font-size:11px;color:var(--text-dim);">by {{ $session->openedBy->name ?? '—' }}</span>
                            </div>
                        @endif
                        @if($session->closed_at)
                            <div class="sh-tl-sep"></div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <div class="sh-tl-dot" style="background:var(--amber);"></div>
                                <span style="font-size:11px;color:var(--text-dim);">Closed</span>
                                <span style="font-size:11px;font-weight:700;color:var(--text);">{{ $session->closed_at->format('H:i') }}</span>
                                <span style="font-size:11px;color:var(--text-dim);">by {{ $session->closedBy->name ?? '—' }}</span>
                            </div>
                        @endif
                        @if($session->locked_at)
                            <div class="sh-tl-sep"></div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <div class="sh-tl-dot" style="background:var(--text-dim);"></div>
                                <span style="font-size:11px;color:var(--text-dim);">Locked</span>
                                <span style="font-size:11px;font-weight:700;color:var(--text);">{{ $session->locked_at->format('d M H:i') }}</span>
                                <span style="font-size:11px;color:var(--text-dim);">by {{ $session->lockedBy->name ?? '—' }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- 3-col: Cash recon | Expenses | Withdrawals --}}
                    <div class="sh-recon-grid">

                        {{-- Cash Reconciliation --}}
                        <div class="sh-recon-col">
                            <div class="sh-recon-title">Cash Reconciliation</div>
                            @php
                                $reconRows = [
                                    ['Opening balance',     $session->opening_balance ?? 0,     'var(--text-dim)'],
                                    ['+ Cash sales',        $session->total_sales_cash ?? 0,    'var(--green)'],
                                    ['− Cash refunds',      $session->total_refunds_cash ?? 0,  'var(--red)'],
                                    ['− Cash expenses',     $session->total_expenses_cash ?? 0, 'var(--red)'],
                                    ['− Owner withdrawals', $session->total_withdrawals ?? 0,   'var(--accent)'],
                                ];
                                $sv = $session->cash_variance ?? 0;
                            @endphp
                            @foreach ($reconRows as $reconRow)
                            @php [$lbl, $amt, $clr] = $reconRow; @endphp
                                <div class="sh-line-item">
                                    <div style="display:flex;align-items:center;gap:7px;">
                                        <div style="width:2px;height:13px;border-radius:1px;background:{{ $clr }};flex-shrink:0;"></div>
                                        <span style="font-size:12px;color:var(--text-dim);">{{ $lbl }}</span>
                                    </div>
                                    <span style="font-size:12px;font-weight:600;font-family:var(--font-mono);color:{{ $clr }};">{{ number_format($amt) }}</span>
                                </div>
                            @endforeach

                            <div style="margin-top:10px;padding-top:10px;border-top:2px solid var(--border);display:flex;flex-direction:column;gap:5px;">
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="font-size:12px;font-weight:600;color:var(--text);">Expected cash</span>
                                    <span style="font-size:12px;font-weight:700;font-family:var(--font-mono);color:var(--accent);">{{ number_format($session->expected_cash ?? 0) }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="font-size:12px;color:var(--text-dim);">Actual counted</span>
                                    <span style="font-size:12px;font-weight:600;font-family:var(--font-mono);color:var(--text);">{{ number_format($session->actual_cash_counted ?? 0) }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="font-size:12px;color:var(--text-dim);">Cash to bank</span>
                                    <span style="font-size:12px;font-weight:600;font-family:var(--font-mono);color:var(--text-dim);">{{ number_format($session->cash_to_bank ?? 0) }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="font-size:12px;color:var(--text-dim);">Retained</span>
                                    <span style="font-size:12px;font-weight:600;font-family:var(--font-mono);color:var(--text);">{{ number_format($session->cash_retained ?? 0) }}</span>
                                </div>
                            </div>

                            <div style="margin-top:10px;padding:10px 12px;border-radius:9px;
                                        background:{{ $sv === 0 ? 'var(--green-dim)' : ($sv > 0 ? 'var(--amber-dim)' : 'var(--red-dim)') }};
                                        border:1px solid {{ $sv === 0 ? 'var(--green)' : ($sv > 0 ? 'var(--amber)' : 'var(--red)') }};
                                        display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;
                                             color:{{ $sv === 0 ? 'var(--green)' : ($sv > 0 ? 'var(--amber)' : 'var(--red)') }};">Variance</span>
                                <span style="font-size:13px;font-weight:800;font-family:var(--font-mono);
                                             color:{{ $sv === 0 ? 'var(--green)' : ($sv > 0 ? 'var(--amber)' : 'var(--red)') }};">
                                    {{ $sv >= 0 ? '+' : '' }}{{ number_format($sv) }} RWF
                                </span>
                            </div>

                            @if ($session->notes)
                                <div style="margin-top:8px;padding:9px 11px;border-radius:8px;font-size:11px;
                                            background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">
                                    {{ $session->notes }}
                                </div>
                            @endif
                        </div>

                        {{-- Expenses --}}
                        <div class="sh-recon-col">
                            <div class="sh-recon-title">
                                Expenses
                                @if($session->expenses->count())
                                    <span style="margin-left:5px;padding:1px 6px;border-radius:4px;font-size:10px;font-weight:500;
                                                 text-transform:none;letter-spacing:0;background:var(--red-dim);color:var(--red);">{{ $session->expenses->count() }}</span>
                                @endif
                            </div>
                            @forelse ($session->expenses as $expense)
                                <div class="sh-line-item">
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:12px;font-weight:600;color:var(--text);">{{ $expense->category->name ?? '—' }}</div>
                                        @if($expense->description)
                                            <div style="font-size:11px;color:var(--text-dim);margin-top:1px;
                                                        overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $expense->description }}</div>
                                        @endif
                                    </div>
                                    <span style="font-size:12px;font-weight:700;font-family:var(--font-mono);color:var(--red);flex-shrink:0;margin-left:10px;">{{ number_format($expense->amount) }}</span>
                                </div>
                            @empty
                                <div style="font-size:12px;color:var(--text-faint);padding:10px 0;">No expenses recorded</div>
                            @endforelse
                        </div>

                        {{-- Withdrawals --}}
                        <div class="sh-recon-col">
                            <div class="sh-recon-title">
                                Owner Withdrawals
                                @if($session->ownerWithdrawals->count())
                                    <span style="margin-left:5px;padding:1px 6px;border-radius:4px;font-size:10px;font-weight:500;
                                                 text-transform:none;letter-spacing:0;background:var(--accent-dim);color:var(--accent);">{{ $session->ownerWithdrawals->count() }}</span>
                                @endif
                            </div>
                            @forelse ($session->ownerWithdrawals as $w)
                                <div class="sh-line-item">
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:12px;font-weight:600;color:var(--text);">{{ $w->reason }}</div>
                                        @if($w->recordedBy)
                                            <div style="font-size:11px;color:var(--text-dim);margin-top:1px;">by {{ $w->recordedBy->name }}</div>
                                        @endif
                                    </div>
                                    <span style="font-size:12px;font-weight:700;font-family:var(--font-mono);color:var(--accent);flex-shrink:0;margin-left:10px;">{{ number_format($w->amount) }}</span>
                                </div>
                            @empty
                                <div style="font-size:12px;color:var(--text-faint);padding:10px 0;">No withdrawals recorded</div>
                            @endforelse
                        </div>

                    </div>{{-- /sh-recon-grid --}}
                </div>{{-- /sh-detail-panel --}}
            @endif

        </div>{{-- /sh-table-wrap --}}
    @endforeach

    <div style="margin-top:16px;">
        {{ $sessions->links() }}
    </div>

@endif

</div>
