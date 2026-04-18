<div>
    @if (session()->has('success'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:var(--green-dim);color:var(--green);">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    {{-- Previous unclosed session warning --}}
    @if ($unclosedPrevious)
        <div class="mb-4 rounded-xl p-4 flex items-start gap-3" style="background:var(--amber-dim);border:1px solid var(--amber);">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--amber);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div class="flex-1">
                <div class="text-sm font-semibold" style="color:var(--amber);">Previous Session Not Closed</div>
                <div class="text-xs mt-1" style="color:var(--text-dim);">
                    Session for <strong>{{ $unclosedPrevious->session_date->format('d M Y') }}</strong> is still open.
                </div>
                <a href="{{ route('shop.session.close', ['session' => $unclosedPrevious->id]) }}"
                   class="inline-block mt-2 px-3 py-1 rounded text-xs font-semibold"
                   style="background:var(--amber);color:#1a1a1a;">
                    Close Previous Session
                </a>
            </div>
        </div>
    @endif

    @if ($todaySession && $todaySession->isOpen())
        {{-- Live summary card --}}
        <div class="rounded-xl p-5" style="background:var(--surface-raised);border:1px solid var(--border);" wire:poll.30s="refreshSummary">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-sm font-semibold" style="color:var(--text);">Today's Session</div>
                    <div class="text-xs" style="color:var(--text-dim);">{{ $todaySession->session_date->format('d M Y') }} · Opened by {{ $todaySession->openedBy->name ?? '—' }}</div>
                </div>
                <span class="px-2 py-1 rounded text-xs font-semibold" style="background:var(--green-dim);color:var(--green);">Open</span>
            </div>

            @if ($liveSummary)
                <div class="grid grid-cols-2 gap-3 mb-4 sm:grid-cols-4">
                    <div class="rounded-lg p-3 text-center" style="background:var(--surface);border:1px solid var(--border);">
                        <div class="text-xs mb-1" style="color:var(--text-dim);">Sales</div>
                        <div class="font-mono font-bold text-sm" style="color:var(--green);">{{ number_format($liveSummary['total_sales']) }}</div>
                        <div class="text-xs" style="color:var(--text-dim);">{{ $liveSummary['transaction_count'] }} tx</div>
                    </div>
                    <div class="rounded-lg p-3 text-center" style="background:var(--surface);border:1px solid var(--border);">
                        <div class="text-xs mb-1" style="color:var(--text-dim);">Expenses</div>
                        <div class="font-mono font-bold text-sm" style="color:var(--red);">{{ number_format($liveSummary['total_expenses']) }}</div>
                        <div class="text-xs" style="color:var(--text-dim);">{{ $liveSummary['expense_count'] }} items</div>
                    </div>
                    <div class="rounded-lg p-3 text-center" style="background:var(--surface);border:1px solid var(--border);">
                        <div class="text-xs mb-1" style="color:var(--text-dim);">Withdrawals</div>
                        <div class="font-mono font-bold text-sm" style="color:var(--accent);">{{ number_format($liveSummary['total_withdrawals']) }}</div>
                        <div class="text-xs" style="color:var(--text-dim);">{{ $liveSummary['withdrawal_count'] }} items</div>
                    </div>
                    <div class="rounded-lg p-3 text-center" style="background:var(--surface);border:1px solid var(--border);">
                        <div class="text-xs mb-1" style="color:var(--text-dim);">Expected Cash</div>
                        <div class="font-mono font-bold text-sm" style="color:var(--accent);">{{ number_format($liveSummary['expected_cash']) }}</div>
                        <div class="text-xs" style="color:var(--text-dim);">RWF</div>
                    </div>
                </div>
            @endif

            @if ($pendingRequestsCount > 0)
                <div class="mb-4 px-3 py-2 rounded-lg text-xs flex items-center gap-2" style="background:var(--amber-dim);color:var(--amber);">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    {{ $pendingRequestsCount }} pending warehouse expense request{{ $pendingRequestsCount !== 1 ? 's' : '' }}
                    <a href="{{ route('shop.session.requests') }}" class="underline ml-auto">Review</a>
                </div>
            @endif

            {{-- 4 prominent mid-day action buttons --}}
            <div class="grid grid-cols-2 gap-2 mb-3">
                <a href="{{ route('shop.expenses.add') }}"
                   class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg text-sm font-semibold"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--amber);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Expense
                </a>
                <a href="{{ route('shop.bank-deposits') }}"
                   class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg text-sm font-semibold"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--accent);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l9-3 9 3M3 6v12l9 3 9-3V6M12 3v18"/>
                    </svg>
                    Record Bank Deposit
                </a>
                <a href="{{ route('shop.withdrawals.add') }}"
                   class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg text-sm font-semibold"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--red);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Owner Withdrawal
                </a>
                <a href="{{ route('shop.session.close', ['session' => $todaySession->id]) }}"
                   class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg text-sm font-semibold"
                   style="background:var(--amber);color:#1a1a1a;">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Close Day
                </a>
            </div>
        </div>

    @elseif ($todaySession && ! $todaySession->isOpen())
        {{-- Session already closed today --}}
        <div class="rounded-xl p-5 text-center" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="w-10 h-10 rounded-full mx-auto mb-3 flex items-center justify-center" style="background:var(--surface);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--text-dim);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-sm font-semibold mb-1" style="color:var(--text);">Session Closed</div>
            <div class="text-xs mb-4" style="color:var(--text-dim);">Today's session has been closed and submitted.</div>
            <a href="{{ route('shop.session.history') }}" class="text-xs" style="color:var(--accent);">View session history →</a>
        </div>

    @else
        {{-- No session yet today — show open form --}}
        <div class="rounded-xl p-5" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-sm font-semibold mb-1" style="color:var(--text);">Open Today's Session</div>
            <div class="text-xs mb-4" style="color:var(--text-dim);">Enter the opening cash balance in the drawer to start the day.</div>

            @if (! $showOpenForm)
                <button wire:click="$set('showOpenForm', true)"
                        class="w-full px-4 py-2 rounded-lg text-sm font-semibold"
                        style="background:var(--accent);color:white;">
                    Open Day
                </button>
            @else
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Opening balance (RWF)</label>
                        <input type="number" wire:model="openingBalance" min="0"
                               class="w-full px-3 py-2 rounded-lg text-sm"
                               style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                               placeholder="0" autofocus>
                        @error('openingBalance') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="openDay"
                                wire:loading.attr="disabled"
                                class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold"
                                style="background:var(--green);color:white;">
                            <span wire:loading.remove wire:target="openDay">Open Day</span>
                            <span wire:loading wire:target="openDay" style="display:none;">Opening…</span>
                        </button>
                        <button wire:click="$set('showOpenForm', false)"
                                class="px-4 py-2 rounded-lg text-sm"
                                style="background:var(--surface);color:var(--text-dim);border:1px solid var(--border);">
                            Cancel
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
