<div>
<style>
.sh-metrics { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px; }
.sh-metric   { border-radius:12px;padding:14px 16px;background:var(--surface-raised);border:1px solid var(--border); }
.sh-sessions { display:flex;flex-direction:column;gap:10px; }
.sh-card     { border-radius:14px;overflow:hidden;border:1px solid var(--border); }
.sh-row5     { display:grid;grid-template-columns:repeat(5,1fr); }
.sh-detail3  { display:grid;grid-template-columns:repeat(3,1fr);gap:0; }
@media (max-width:640px) {
    .sh-metrics { grid-template-columns:repeat(2,1fr); }
    .sh-row5    { grid-template-columns:repeat(3,1fr); }
    .sh-row5 .sh-hide-mobile { display:none; }
    .sh-detail3 { grid-template-columns:1fr; }
}
</style>

    @if (session()->has('success'))
        <div style="margin-bottom:12px;padding:10px 14px;border-radius:10px;font-size:12px;
                    background:var(--green-dim);color:var(--green);border:1px solid var(--green);">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div style="margin-bottom:12px;padding:10px 14px;border-radius:10px;font-size:12px;
                    background:var(--red-dim);color:var(--red);border:1px solid var(--red);">{{ session('error') }}</div>
    @endif

    {{-- ── Summary strip ── --}}
    @if ($sessions->isNotEmpty())
        @php
            $col          = $sessions->getCollection();
            $pageSales    = $col->sum('total_sales');
            $pageExpenses = $col->sum('total_expenses');
            $pageWithdraw = $col->sum('total_withdrawals');
            $pageVariance = $col->sum('cash_variance');
        @endphp
        <div class="sh-metrics">
            <div class="sh-metric">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-faint);margin-bottom:5px;">Sessions</div>
                <div style="font-size:24px;font-weight:800;font-family:var(--font-mono);color:var(--text);">{{ $sessions->total() }}</div>
                <div style="font-size:11px;color:var(--text-faint);margin-top:2px;">all time</div>
            </div>
            <div class="sh-metric">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-faint);margin-bottom:5px;">Sales (page)</div>
                <div style="font-size:20px;font-weight:800;font-family:var(--font-mono);color:var(--green);">{{ number_format($pageSales) }}</div>
                <div style="font-size:11px;color:var(--text-faint);margin-top:2px;">RWF</div>
            </div>
            <div class="sh-metric">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-faint);margin-bottom:5px;">Paid Out (page)</div>
                <div style="font-size:20px;font-weight:800;font-family:var(--font-mono);color:var(--red);">{{ number_format($pageExpenses + $pageWithdraw) }}</div>
                <div style="font-size:11px;color:var(--text-faint);margin-top:2px;">expenses + withdrawals</div>
            </div>
            <div class="sh-metric">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-faint);margin-bottom:5px;">Net Variance (page)</div>
                <div style="font-size:20px;font-weight:800;font-family:var(--font-mono);
                            color:{{ $pageVariance < 0 ? 'var(--red)' : ($pageVariance > 0 ? 'var(--amber)' : 'var(--text-dim)') }};">
                    {{ $pageVariance >= 0 ? '+' : '' }}{{ number_format($pageVariance) }}
                </div>
                <div style="font-size:11px;color:var(--text-faint);margin-top:2px;">RWF</div>
            </div>
        </div>
    @endif

    {{-- ── Session cards ── --}}
    @if ($sessions->isEmpty())
        <div style="text-align:center;padding:48px 0;font-size:13px;color:var(--text-faint);
                    border-radius:14px;border:1px solid var(--border);background:var(--surface-raised);">
            No closed sessions yet
        </div>
    @else
        <div class="sh-sessions">
            @foreach ($sessions as $session)
                @php
                    $v   = $session->cash_variance ?? 0;
                    $net = ($session->total_sales ?? 0) - ($session->total_expenses ?? 0) - ($session->total_withdrawals ?? 0);
                    $vColor = $v === 0 ? 'var(--green)' : ($v > 0 ? 'var(--amber)' : 'var(--red)');
                    $vBg    = $v === 0 ? 'var(--green-dim)' : ($v > 0 ? 'var(--amber-dim)' : 'var(--red-dim)');
                    $isLocked = $session->isLocked();
                    $isClosed = $session->isClosed();
                @endphp

                <div class="sh-card">

                    {{-- ── Card header ── --}}
                    <div style="padding:12px 16px;background:var(--surface-raised);border-bottom:1px solid var(--border);
                                display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                            {{-- Date --}}
                            <div>
                                <div style="font-size:14px;font-weight:700;color:var(--text);">
                                    {{ $session->session_date->format('d M Y') }}
                                </div>
                                <div style="font-size:11px;color:var(--text-dim);margin-top:1px;">
                                    {{ $session->session_date->format('l') }}
                                    @if($session->opened_at)
                                        · {{ $session->opened_at->format('H:i') }}
                                        @if($session->closed_at) – {{ $session->closed_at->format('H:i') }}@endif
                                    @endif
                                    @if($session->openedBy) · {{ $session->openedBy->name }} @endif
                                </div>
                            </div>

                            {{-- Status badge --}}
                            @if ($isLocked)
                                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;
                                             font-size:10px;font-weight:700;background:var(--surface);color:var(--text-dim);border:1px solid var(--border);">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Locked
                                </span>
                            @else
                                <span style="padding:3px 10px;border-radius:999px;font-size:10px;font-weight:700;
                                             background:var(--amber-dim);color:var(--amber);">
                                    Closed
                                </span>
                            @endif

                            {{-- Variance badge --}}
                            <span style="padding:3px 10px;border-radius:999px;font-size:10px;font-weight:700;
                                         background:{{ $vBg }};color:{{ $vColor }};">
                                {{ $v >= 0 ? '+' : '' }}{{ number_format($v) }} RWF
                            </span>
                        </div>

                        {{-- Actions --}}
                        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                            @if ($isClosed && auth()->user()->isOwner())
                                <button wire:click="lockSession({{ $session->id }})"
                                        wire:confirm="Lock this session? It will become immutable."
                                        style="padding:5px 12px;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;
                                               background:var(--surface);color:var(--text-dim);border:1px solid var(--border);">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:3px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Lock
                                </button>
                            @endif
                            <button wire:click="toggleExpand({{ $session->id }})"
                                    style="padding:5px 14px;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;
                                           background:var(--accent-dim);color:var(--accent);border:1px solid var(--accent-dim);">
                                {{ $expandedId === $session->id ? 'Hide ▲' : 'Details ▾' }}
                            </button>
                        </div>
                    </div>

                    {{-- ── Metrics row ── --}}
                    <div class="sh-row5" style="background:var(--surface);border-bottom:1px solid var(--border);">
                        <div style="padding:11px 14px;border-right:1px solid var(--border);">
                            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.4px;color:var(--text-faint);margin-bottom:3px;">Sales</div>
                            <div style="font-size:13px;font-weight:700;font-family:var(--font-mono);color:var(--green);">{{ number_format($session->total_sales ?? 0) }}</div>
                        </div>
                        <div style="padding:11px 14px;border-right:1px solid var(--border);">
                            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.4px;color:var(--text-faint);margin-bottom:3px;">Expenses</div>
                            <div style="font-size:13px;font-weight:700;font-family:var(--font-mono);color:var(--red);">{{ number_format($session->total_expenses ?? 0) }}</div>
                        </div>
                        <div style="padding:11px 14px;border-right:1px solid var(--border);">
                            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.4px;color:var(--text-faint);margin-bottom:3px;">Withdrawals</div>
                            <div style="font-size:13px;font-weight:700;font-family:var(--font-mono);color:var(--accent);">{{ number_format($session->total_withdrawals ?? 0) }}</div>
                        </div>
                        <div class="sh-hide-mobile" style="padding:11px 14px;border-right:1px solid var(--border);">
                            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.4px;color:var(--text-faint);margin-bottom:3px;">Net</div>
                            <div style="font-size:13px;font-weight:700;font-family:var(--font-mono);color:var(--text);">{{ number_format($net) }}</div>
                        </div>
                        <div class="sh-hide-mobile" style="padding:11px 14px;">
                            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.4px;color:var(--text-faint);margin-bottom:3px;">Opening</div>
                            <div style="font-size:13px;font-weight:700;font-family:var(--font-mono);color:var(--text-dim);">{{ number_format($session->opening_balance ?? 0) }}</div>
                        </div>
                    </div>

                    {{-- ── Expanded detail ── --}}
                    @if ($expandedId === $session->id)
                        <div style="background:var(--surface);">

                            {{-- Timeline strip --}}
                            <div style="padding:12px 16px;border-bottom:1px solid var(--border);
                                        display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                                @if($session->opened_at)
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <div style="width:8px;height:8px;border-radius:50%;background:var(--green);flex-shrink:0;"></div>
                                        <span style="font-size:11px;color:var(--text-dim);">Opened</span>
                                        <span style="font-size:11px;font-weight:700;color:var(--text);">{{ $session->opened_at->format('H:i') }}</span>
                                        <span style="font-size:11px;color:var(--text-dim);">by {{ $session->openedBy->name ?? '—' }}</span>
                                    </div>
                                @endif
                                @if($session->closed_at)
                                    <div style="width:20px;height:1px;background:var(--border);flex-shrink:0;"></div>
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <div style="width:8px;height:8px;border-radius:50%;background:var(--amber);flex-shrink:0;"></div>
                                        <span style="font-size:11px;color:var(--text-dim);">Closed</span>
                                        <span style="font-size:11px;font-weight:700;color:var(--text);">{{ $session->closed_at->format('H:i') }}</span>
                                        <span style="font-size:11px;color:var(--text-dim);">by {{ $session->closedBy->name ?? '—' }}</span>
                                    </div>
                                @endif
                                @if($session->locked_at)
                                    <div style="width:20px;height:1px;background:var(--border);flex-shrink:0;"></div>
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <div style="width:8px;height:8px;border-radius:50%;background:var(--text-dim);flex-shrink:0;"></div>
                                        <span style="font-size:11px;color:var(--text-dim);">Locked</span>
                                        <span style="font-size:11px;font-weight:700;color:var(--text);">{{ $session->locked_at->format('d M H:i') }}</span>
                                        <span style="font-size:11px;color:var(--text-dim);">by {{ $session->lockedBy->name ?? '—' }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- 3-column detail --}}
                            <div class="sh-detail3">

                                {{-- Cash Reconciliation --}}
                                <div style="padding:16px 18px;border-right:1px solid var(--border);">
                                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;
                                                color:var(--text-dim);margin-bottom:12px;">Cash Reconciliation</div>
                                    <div style="display:flex;flex-direction:column;gap:6px;">
                                        @php
                                            $reconRows = [
                                                ['Opening balance',    $session->opening_balance ?? 0,       'var(--text-dim)', null],
                                                ['+ Cash sales',       $session->total_sales_cash ?? 0,      'var(--green)',    null],
                                                ['− Cash refunds',     $session->total_refunds_cash ?? 0,    'var(--red)',      null],
                                                ['− Cash expenses',    $session->total_expenses_cash ?? 0,   'var(--red)',      null],
                                                ['− Owner withdrawals',$session->total_withdrawals ?? 0,     'var(--accent)',   null],
                                            ];
                                        @endphp
                                        @foreach ($reconRows as [$lbl, $amt, $clr])
                                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                                <div style="display:flex;align-items:center;gap:6px;">
                                                    <div style="width:2px;height:12px;border-radius:1px;background:{{ $clr }};flex-shrink:0;"></div>
                                                    <span style="font-size:11px;color:var(--text-dim);">{{ $lbl }}</span>
                                                </div>
                                                <span style="font-size:11px;font-weight:600;font-family:var(--font-mono);color:{{ $clr }};">{{ number_format($amt) }}</span>
                                            </div>
                                        @endforeach
                                        <div style="margin-top:4px;padding-top:8px;border-top:1px solid var(--border);">
                                            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                                <span style="font-size:11px;font-weight:600;color:var(--text);">Expected cash</span>
                                                <span style="font-size:11px;font-weight:700;font-family:var(--font-mono);color:var(--accent);">{{ number_format($session->expected_cash ?? 0) }}</span>
                                            </div>
                                            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                                <span style="font-size:11px;color:var(--text-dim);">Actual counted</span>
                                                <span style="font-size:11px;font-weight:600;font-family:var(--font-mono);color:var(--text);">{{ number_format($session->actual_cash_counted ?? 0) }}</span>
                                            </div>
                                            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                                <span style="font-size:11px;color:var(--text-dim);">Cash to bank</span>
                                                <span style="font-size:11px;font-weight:600;font-family:var(--font-mono);color:var(--text-dim);">{{ number_format($session->cash_to_bank ?? 0) }}</span>
                                            </div>
                                            <div style="display:flex;justify-content:space-between;">
                                                <span style="font-size:11px;color:var(--text-dim);">Retained</span>
                                                <span style="font-size:11px;font-weight:600;font-family:var(--font-mono);color:var(--text);">{{ number_format($session->cash_retained ?? 0) }}</span>
                                            </div>
                                        </div>
                                        {{-- Variance highlight --}}
                                        @php $sv = $session->cash_variance ?? 0; @endphp
                                        <div style="margin-top:8px;padding:8px 10px;border-radius:8px;
                                                    background:{{ $sv === 0 ? 'var(--green-dim)' : ($sv > 0 ? 'var(--amber-dim)' : 'var(--red-dim)') }};
                                                    border:1px solid {{ $sv === 0 ? 'var(--green)' : ($sv > 0 ? 'var(--amber)' : 'var(--red)') }};
                                                    display:flex;justify-content:space-between;align-items:center;">
                                            <span style="font-size:10px;font-weight:700;text-transform:uppercase;
                                                         color:{{ $sv === 0 ? 'var(--green)' : ($sv > 0 ? 'var(--amber)' : 'var(--red)') }};">
                                                Variance
                                            </span>
                                            <span style="font-size:12px;font-weight:800;font-family:var(--font-mono);
                                                         color:{{ $sv === 0 ? 'var(--green)' : ($sv > 0 ? 'var(--amber)' : 'var(--red)') }};">
                                                {{ $sv >= 0 ? '+' : '' }}{{ number_format($sv) }} RWF
                                            </span>
                                        </div>
                                        @if ($session->notes)
                                            <div style="margin-top:8px;padding:8px 10px;border-radius:8px;font-size:11px;
                                                        background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">
                                                {{ $session->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Expenses --}}
                                <div style="padding:16px 18px;border-right:1px solid var(--border);">
                                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;
                                                color:var(--text-dim);margin-bottom:12px;">
                                        Expenses
                                        @if($session->expenses->count())
                                            <span style="font-weight:500;text-transform:none;letter-spacing:0;
                                                         padding:1px 6px;border-radius:4px;margin-left:4px;
                                                         background:var(--red-dim);color:var(--red);">{{ $session->expenses->count() }}</span>
                                        @endif
                                    </div>
                                    @forelse ($session->expenses as $expense)
                                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;
                                                    padding:7px 0;border-bottom:1px solid var(--border);">
                                            <div style="flex:1;min-width:0;">
                                                <div style="font-size:12px;font-weight:600;color:var(--text);">{{ $expense->category->name ?? '—' }}</div>
                                                @if($expense->description)
                                                    <div style="font-size:11px;color:var(--text-dim);margin-top:1px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $expense->description }}</div>
                                                @endif
                                            </div>
                                            <span style="font-size:12px;font-weight:700;font-family:var(--font-mono);color:var(--red);flex-shrink:0;">{{ number_format($expense->amount) }}</span>
                                        </div>
                                    @empty
                                        <div style="font-size:12px;color:var(--text-faint);padding:8px 0;">No expenses recorded</div>
                                    @endforelse
                                </div>

                                {{-- Owner Withdrawals --}}
                                <div style="padding:16px 18px;">
                                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;
                                                color:var(--text-dim);margin-bottom:12px;">
                                        Owner Withdrawals
                                        @if($session->ownerWithdrawals->count())
                                            <span style="font-weight:500;text-transform:none;letter-spacing:0;
                                                         padding:1px 6px;border-radius:4px;margin-left:4px;
                                                         background:var(--accent-dim);color:var(--accent);">{{ $session->ownerWithdrawals->count() }}</span>
                                        @endif
                                    </div>
                                    @forelse ($session->ownerWithdrawals as $w)
                                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;
                                                    padding:7px 0;border-bottom:1px solid var(--border);">
                                            <div style="flex:1;min-width:0;">
                                                <div style="font-size:12px;font-weight:600;color:var(--text);">{{ $w->reason }}</div>
                                                @if($w->recordedBy)
                                                    <div style="font-size:11px;color:var(--text-dim);margin-top:1px;">by {{ $w->recordedBy->name }}</div>
                                                @endif
                                            </div>
                                            <span style="font-size:12px;font-weight:700;font-family:var(--font-mono);color:var(--accent);flex-shrink:0;">{{ number_format($w->amount) }}</span>
                                        </div>
                                    @empty
                                        <div style="font-size:12px;color:var(--text-faint);padding:8px 0;">No withdrawals recorded</div>
                                    @endforelse
                                </div>

                            </div>{{-- /sh-detail3 --}}
                        </div>
                    @endif

                </div>{{-- /sh-card --}}
            @endforeach
        </div>

        <div style="margin-top:16px;">
            {{ $sessions->links() }}
        </div>
    @endif

</div>
