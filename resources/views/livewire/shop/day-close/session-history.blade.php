<div>
    @if (session()->has('success'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:var(--green-dim);color:var(--green);">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    {{-- Page summary strip --}}
    @if ($sessions->isNotEmpty())
        @php
            $col = $sessions->getCollection();
            $pageSales    = $col->sum('total_sales');
            $pageExpenses = $col->sum('total_expenses');
            $pageWithdraw = $col->sum('total_withdrawals');
            $pageVariance = $col->sum('cash_variance');
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="text-xs mb-1" style="color:var(--text-dim);">Sessions</div>
                <div class="text-2xl font-bold font-mono" style="color:var(--text);">{{ $sessions->total() }}</div>
                <div class="text-xs mt-0.5" style="color:var(--text-dim);">all time</div>
            </div>
            <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="text-xs mb-1" style="color:var(--text-dim);">Sales (this page)</div>
                <div class="text-xl font-bold font-mono" style="color:var(--green);">{{ number_format($pageSales) }}</div>
                <div class="text-xs mt-0.5" style="color:var(--text-dim);">RWF</div>
            </div>
            <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="text-xs mb-1" style="color:var(--text-dim);">Paid Out (this page)</div>
                <div class="text-xl font-bold font-mono" style="color:var(--red);">{{ number_format($pageExpenses + $pageWithdraw) }}</div>
                <div class="text-xs mt-0.5" style="color:var(--text-dim);">expenses + withdrawals</div>
            </div>
            <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="text-xs mb-1" style="color:var(--text-dim);">Net Variance (this page)</div>
                <div class="text-xl font-bold font-mono"
                     style="{{ $pageVariance < 0 ? 'color:var(--red)' : ($pageVariance > 0 ? 'color:var(--amber)' : 'color:var(--text-dim)') }}">
                    {{ $pageVariance >= 0 ? '+' : '' }}{{ number_format($pageVariance) }}
                </div>
                <div class="text-xs mt-0.5" style="color:var(--text-dim);">RWF</div>
            </div>
        </div>
    @endif

    {{-- ── MOBILE: card layout ── --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($sessions as $session)
            @php $v = $session->cash_variance ?? 0; @endphp
            <div class="rounded-xl overflow-hidden" style="background:var(--surface-raised);border:1px solid var(--border);">

                {{-- Card header --}}
                <div class="flex items-start justify-between gap-3 px-4 pt-4 pb-3">
                    <div>
                        <div class="font-semibold text-base" style="color:var(--text);">{{ $session->session_date->format('d M Y') }}</div>
                        <div class="text-xs mt-0.5" style="color:var(--text-dim);">
                            {{ $session->session_date->format('l') }}
                            @if($session->opened_at)
                                · {{ $session->opened_at->format('H:i') }}
                                @if($session->closed_at) – {{ $session->closed_at->format('H:i') }} @endif
                            @endif
                        </div>
                        <div class="text-xs mt-0.5" style="color:var(--text-dim);">{{ $session->openedBy->name ?? '—' }}</div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold flex-shrink-0"
                          style="{{ $session->isLocked()
                              ? 'background:var(--surface);color:var(--text-dim);border:1px solid var(--border)'
                              : 'background:var(--green-dim);color:var(--green)' }}">
                        {{ ucfirst($session->status) }}
                    </span>
                </div>

                {{-- Metric grid --}}
                <div class="grid grid-cols-3" style="border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
                    <div class="px-3 py-3 text-center" style="border-right:1px solid var(--border);">
                        <div class="text-xs mb-1" style="color:var(--text-dim);">Sales</div>
                        <div class="font-mono font-semibold text-sm" style="color:var(--green);">{{ number_format($session->total_sales ?? 0) }}</div>
                    </div>
                    <div class="px-3 py-3 text-center" style="border-right:1px solid var(--border);">
                        <div class="text-xs mb-1" style="color:var(--text-dim);">Paid Out</div>
                        <div class="font-mono font-semibold text-sm" style="color:var(--red);">
                            {{ number_format(($session->total_expenses ?? 0) + ($session->total_withdrawals ?? 0)) }}
                        </div>
                    </div>
                    <div class="px-3 py-3 text-center">
                        <div class="text-xs mb-1" style="color:var(--text-dim);">Variance</div>
                        <div class="font-mono font-semibold text-sm"
                             style="{{ $v < 0 ? 'color:var(--red)' : ($v > 0 ? 'color:var(--amber)' : 'color:var(--text-dim)') }}">
                            {{ $v >= 0 ? '+' : '' }}{{ number_format($v) }}
                        </div>
                    </div>
                </div>

                {{-- Net + actions --}}
                <div class="flex items-center justify-between px-4 py-3">
                    @php $net = ($session->total_sales ?? 0) - ($session->total_expenses ?? 0) - ($session->total_withdrawals ?? 0); @endphp
                    <div class="text-xs" style="color:var(--text-dim);">
                        Net: <span class="font-mono font-semibold" style="color:var(--text);">{{ number_format($net) }} RWF</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($session->isClosed() && auth()->user()->isOwner())
                            <button wire:click="lockSession({{ $session->id }})"
                                    wire:confirm="Lock this session? It will become immutable."
                                    class="text-xs px-2.5 py-1.5 rounded-lg"
                                    style="color:var(--text-dim);border:1px solid var(--border);">
                                Lock
                            </button>
                        @endif
                        <button wire:click="toggleExpand({{ $session->id }})"
                                class="text-xs px-2.5 py-1.5 rounded-lg font-medium"
                                style="color:var(--accent);background:var(--accent-dim);">
                            {{ $expandedId === $session->id ? 'Hide ▲' : 'Details ▾' }}
                        </button>
                    </div>
                </div>

                {{-- Expanded detail (mobile) --}}
                @if ($expandedId === $session->id)
                    <div class="px-4 pb-4 space-y-4" style="border-top:1px solid var(--border);background:var(--surface);">

                        {{-- Timeline --}}
                        <div class="pt-3">
                            <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-dim);">Timeline</div>
                            <div class="space-y-1.5 text-xs">
                                @if($session->opened_at)
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:var(--green);"></span>
                                        <span style="color:var(--text-dim);">Opened</span>
                                        <span style="color:var(--text);">{{ $session->opened_at->format('H:i') }}</span>
                                        <span style="color:var(--text-dim);">· {{ $session->openedBy->name ?? '—' }}</span>
                                    </div>
                                @endif
                                @if($session->closed_at)
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:var(--amber);"></span>
                                        <span style="color:var(--text-dim);">Closed</span>
                                        <span style="color:var(--text);">{{ $session->closed_at->format('H:i') }}</span>
                                        <span style="color:var(--text-dim);">· {{ $session->closedBy->name ?? '—' }}</span>
                                    </div>
                                @endif
                                @if($session->locked_at)
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:var(--text-dim);"></span>
                                        <span style="color:var(--text-dim);">Locked</span>
                                        <span style="color:var(--text);">{{ $session->locked_at->format('d M H:i') }}</span>
                                        <span style="color:var(--text-dim);">· {{ $session->lockedBy->name ?? '—' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Cash reconciliation --}}
                        <div>
                            <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-dim);">Cash Reconciliation</div>
                            <div class="space-y-1.5 text-xs">
                                <div class="flex justify-between"><span style="color:var(--text-dim);">Opening balance</span><span class="font-mono">{{ number_format($session->opening_balance) }}</span></div>
                                <div class="flex justify-between"><span style="color:var(--text-dim);">+ Cash sales</span><span class="font-mono" style="color:var(--green);">{{ number_format($session->total_sales_cash ?? 0) }}</span></div>
                                <div class="flex justify-between"><span style="color:var(--text-dim);">− Cash refunds</span><span class="font-mono" style="color:var(--red);">{{ number_format($session->total_refunds_cash ?? 0) }}</span></div>
                                <div class="flex justify-between"><span style="color:var(--text-dim);">− Cash expenses</span><span class="font-mono" style="color:var(--red);">{{ number_format($session->total_expenses_cash ?? 0) }}</span></div>
                                <div class="flex justify-between"><span style="color:var(--text-dim);">− Withdrawals</span><span class="font-mono" style="color:var(--accent);">{{ number_format($session->total_withdrawals ?? 0) }}</span></div>
                                <div class="flex justify-between font-semibold pt-1.5" style="border-top:1px solid var(--border);">
                                    <span style="color:var(--text);">Expected cash</span>
                                    <span class="font-mono" style="color:var(--accent);">{{ number_format($session->expected_cash ?? 0) }}</span>
                                </div>
                                <div class="flex justify-between"><span style="color:var(--text);">Actual counted</span><span class="font-mono">{{ number_format($session->actual_cash_counted ?? 0) }}</span></div>
                                <div class="flex justify-between"><span style="color:var(--text);">Cash to bank</span><span class="font-mono">{{ number_format($session->cash_to_bank ?? 0) }}</span></div>
                                <div class="flex justify-between"><span style="color:var(--text);">Retained</span><span class="font-mono">{{ number_format($session->cash_retained ?? 0) }}</span></div>
                            </div>
                            @if ($session->notes)
                                <div class="mt-2.5 text-xs p-2.5 rounded-lg" style="background:var(--surface-raised);color:var(--text-dim);">{{ $session->notes }}</div>
                            @endif
                        </div>

                        {{-- Expenses --}}
                        @if ($session->expenses->isNotEmpty())
                            <div>
                                <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-dim);">Expenses ({{ $session->expenses->count() }})</div>
                                @foreach ($session->expenses as $expense)
                                    <div class="flex justify-between items-start text-xs py-1.5" style="border-bottom:1px solid var(--border);">
                                        <div>
                                            <span style="color:var(--text);">{{ $expense->category->name ?? '—' }}</span>
                                            @if($expense->description)
                                                <div class="mt-0.5" style="color:var(--text-dim);">{{ $expense->description }}</div>
                                            @endif
                                        </div>
                                        <span class="font-mono ml-4 flex-shrink-0" style="color:var(--red);">{{ number_format($expense->amount) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Withdrawals --}}
                        @if ($session->ownerWithdrawals->isNotEmpty())
                            <div>
                                <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-dim);">Owner Withdrawals</div>
                                @foreach ($session->ownerWithdrawals as $w)
                                    <div class="flex justify-between items-start text-xs py-1.5" style="border-bottom:1px solid var(--border);">
                                        <div>
                                            <span style="color:var(--text);">{{ $w->reason }}</span>
                                            @if($w->recordedBy)
                                                <div class="mt-0.5" style="color:var(--text-dim);">by {{ $w->recordedBy->name }}</div>
                                            @endif
                                        </div>
                                        <span class="font-mono ml-4 flex-shrink-0" style="color:var(--accent);">{{ number_format($w->amount) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-10 text-sm" style="color:var(--text-dim);">No closed sessions yet.</div>
        @endforelse
    </div>

    {{-- ── DESKTOP: table layout ── --}}
    <div class="hidden sm:block rounded-xl overflow-hidden" style="border:1px solid var(--border);">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <th class="text-left px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Date</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Sales</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Expenses</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Withdrawals</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Net</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Variance</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sessions as $session)
                    @php $v = $session->cash_variance ?? 0; @endphp
                    <tr style="border-bottom:1px solid var(--border);">
                        <td class="px-4 py-3">
                            <div class="font-medium" style="color:var(--text);">{{ $session->session_date->format('d M Y') }}</div>
                            <div class="text-xs mt-0.5" style="color:var(--text-dim);">
                                {{ $session->session_date->format('D') }}
                                @if($session->opened_at)
                                    · {{ $session->opened_at->format('H:i') }}
                                    @if($session->closed_at) – {{ $session->closed_at->format('H:i') }} @endif
                                @endif
                            </div>
                            <div class="text-xs" style="color:var(--text-dim);">{{ $session->openedBy->name ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3 text-right font-mono" style="color:var(--green);">{{ number_format($session->total_sales ?? 0) }}</td>
                        <td class="px-4 py-3 text-right font-mono" style="color:var(--red);">{{ number_format($session->total_expenses ?? 0) }}</td>
                        <td class="px-4 py-3 text-right font-mono" style="color:var(--accent);">{{ number_format($session->total_withdrawals ?? 0) }}</td>
                        <td class="px-4 py-3 text-right font-mono font-semibold" style="color:var(--text);">
                            @php $net = ($session->total_sales ?? 0) - ($session->total_expenses ?? 0) - ($session->total_withdrawals ?? 0); @endphp
                            {{ number_format($net) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-semibold"
                            style="{{ $v < 0 ? 'color:var(--red)' : ($v > 0 ? 'color:var(--amber)' : 'color:var(--text-dim)') }}">
                            {{ $v >= 0 ? '+' : '' }}{{ number_format($v) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold"
                                  style="{{ $session->isLocked()
                                      ? 'background:var(--surface);color:var(--text-dim);border:1px solid var(--border)'
                                      : 'background:var(--green-dim);color:var(--green)' }}">
                                {{ ucfirst($session->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 justify-end">
                                @if ($session->isClosed() && auth()->user()->isOwner())
                                    <button wire:click="lockSession({{ $session->id }})"
                                            wire:confirm="Lock this session? It will become immutable."
                                            class="text-xs px-2 py-1 rounded"
                                            style="color:var(--text-dim);border:1px solid var(--border);">
                                        Lock
                                    </button>
                                @endif
                                <button wire:click="toggleExpand({{ $session->id }})"
                                        class="text-xs px-2 py-1 rounded"
                                        style="color:var(--accent);border:1px solid var(--accent-dim);">
                                    {{ $expandedId === $session->id ? 'Hide ▲' : 'Details ▾' }}
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Expanded detail row (desktop) --}}
                    @if ($expandedId === $session->id)
                        <tr style="background:var(--surface);">
                            <td colspan="8" class="px-5 py-5">

                                {{-- Timeline strip --}}
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mb-5 pb-4" style="border-bottom:1px solid var(--border);">
                                    @if($session->opened_at)
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:var(--green);"></span>
                                            <span style="color:var(--text-dim);">Opened</span>
                                            <span class="font-medium" style="color:var(--text);">{{ $session->opened_at->format('H:i') }}</span>
                                            <span style="color:var(--text-dim);">by {{ $session->openedBy->name ?? '—' }}</span>
                                        </div>
                                    @endif
                                    @if($session->closed_at)
                                        <span style="color:var(--border);">|</span>
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:var(--amber);"></span>
                                            <span style="color:var(--text-dim);">Closed</span>
                                            <span class="font-medium" style="color:var(--text);">{{ $session->closed_at->format('H:i') }}</span>
                                            <span style="color:var(--text-dim);">by {{ $session->closedBy->name ?? '—' }}</span>
                                        </div>
                                    @endif
                                    @if($session->locked_at)
                                        <span style="color:var(--border);">|</span>
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:var(--text-dim);"></span>
                                            <span style="color:var(--text-dim);">Locked</span>
                                            <span class="font-medium" style="color:var(--text);">{{ $session->locked_at->format('d M Y H:i') }}</span>
                                            <span style="color:var(--text-dim);">by {{ $session->lockedBy->name ?? '—' }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Three columns: reconciliation | expenses | withdrawals --}}
                                <div class="grid gap-6 lg:grid-cols-3">

                                    {{-- Cash reconciliation --}}
                                    <div>
                                        <div class="text-xs font-semibold mb-3 uppercase tracking-wide" style="color:var(--text-dim);">Cash Reconciliation</div>
                                        <div class="space-y-1.5 text-xs">
                                            <div class="flex justify-between"><span style="color:var(--text-dim);">Opening balance</span><span class="font-mono" style="color:var(--text);">{{ number_format($session->opening_balance) }}</span></div>
                                            <div class="flex justify-between"><span style="color:var(--text-dim);">+ Cash sales</span><span class="font-mono" style="color:var(--green);">{{ number_format($session->total_sales_cash ?? 0) }}</span></div>
                                            <div class="flex justify-between"><span style="color:var(--text-dim);">− Cash refunds</span><span class="font-mono" style="color:var(--red);">{{ number_format($session->total_refunds_cash ?? 0) }}</span></div>
                                            <div class="flex justify-between"><span style="color:var(--text-dim);">− Cash expenses</span><span class="font-mono" style="color:var(--red);">{{ number_format($session->total_expenses_cash ?? 0) }}</span></div>
                                            <div class="flex justify-between"><span style="color:var(--text-dim);">− Owner withdrawals</span><span class="font-mono" style="color:var(--accent);">{{ number_format($session->total_withdrawals ?? 0) }}</span></div>
                                            <div class="flex justify-between font-semibold pt-2" style="border-top:1px solid var(--border);">
                                                <span style="color:var(--text);">Expected cash</span>
                                                <span class="font-mono" style="color:var(--accent);">{{ number_format($session->expected_cash ?? 0) }}</span>
                                            </div>
                                            <div class="flex justify-between"><span style="color:var(--text);">Actual counted</span><span class="font-mono">{{ number_format($session->actual_cash_counted ?? 0) }}</span></div>
                                            <div class="flex justify-between"><span style="color:var(--text);">Cash to bank</span><span class="font-mono">{{ number_format($session->cash_to_bank ?? 0) }}</span></div>
                                            <div class="flex justify-between"><span style="color:var(--text);">Retained</span><span class="font-mono">{{ number_format($session->cash_retained ?? 0) }}</span></div>
                                        </div>
                                        @if ($session->notes)
                                            <div class="mt-3 text-xs p-2.5 rounded-lg" style="background:var(--surface-raised);color:var(--text-dim);">{{ $session->notes }}</div>
                                        @endif
                                    </div>

                                    {{-- Expenses --}}
                                    <div>
                                        <div class="text-xs font-semibold mb-3 uppercase tracking-wide" style="color:var(--text-dim);">
                                            Expenses
                                            @if($session->expenses->count()) ({{ $session->expenses->count() }}) @endif
                                        </div>
                                        @forelse ($session->expenses as $expense)
                                            <div class="flex justify-between items-start text-xs py-1.5" style="border-bottom:1px solid var(--border);">
                                                <div class="flex-1 min-w-0 pr-3">
                                                    <div style="color:var(--text);">{{ $expense->category->name ?? '—' }}</div>
                                                    @if($expense->description)
                                                        <div class="mt-0.5 truncate" style="color:var(--text-dim);">{{ $expense->description }}</div>
                                                    @endif
                                                </div>
                                                <span class="font-mono flex-shrink-0" style="color:var(--red);">{{ number_format($expense->amount) }}</span>
                                            </div>
                                        @empty
                                            <div class="text-xs" style="color:var(--text-dim);">No expenses recorded</div>
                                        @endforelse
                                    </div>

                                    {{-- Withdrawals --}}
                                    <div>
                                        <div class="text-xs font-semibold mb-3 uppercase tracking-wide" style="color:var(--text-dim);">
                                            Owner Withdrawals
                                            @if($session->ownerWithdrawals->count()) ({{ $session->ownerWithdrawals->count() }}) @endif
                                        </div>
                                        @forelse ($session->ownerWithdrawals as $w)
                                            <div class="flex justify-between items-start text-xs py-1.5" style="border-bottom:1px solid var(--border);">
                                                <div class="flex-1 min-w-0 pr-3">
                                                    <div style="color:var(--text);">{{ $w->reason }}</div>
                                                    @if($w->recordedBy)
                                                        <div class="mt-0.5" style="color:var(--text-dim);">by {{ $w->recordedBy->name }}</div>
                                                    @endif
                                                </div>
                                                <span class="font-mono flex-shrink-0" style="color:var(--accent);">{{ number_format($w->amount) }}</span>
                                            </div>
                                        @empty
                                            <div class="text-xs" style="color:var(--text-dim);">No withdrawals recorded</div>
                                        @endforelse
                                    </div>

                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm" style="color:var(--text-dim);">No closed sessions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $sessions->links() }}
    </div>
</div>
