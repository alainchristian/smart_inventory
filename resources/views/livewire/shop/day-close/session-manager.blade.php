<div>
    @if (session()->has('success'))
        <div class="mb-4 px-4 py-3 rounded-xl text-sm flex items-center gap-2" style="background:var(--green-dim);color:var(--green);">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-3 rounded-xl text-sm" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    {{-- Previous unclosed session warning --}}
    @if ($unclosedPrevious)
        <div class="mb-4 rounded-xl p-4 flex items-start gap-3" style="background:var(--amber-dim);border:1px solid var(--amber);">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--amber);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold" style="color:var(--amber);">Previous Session Not Closed</div>
                <div class="text-xs mt-1" style="color:var(--text-dim);">
                    Session for <strong>{{ $unclosedPrevious->session_date->format('d M Y') }}</strong> is still open.
                </div>
                <a href="{{ route('shop.session.close', ['session' => $unclosedPrevious->id]) }}"
                   class="inline-flex items-center gap-1.5 mt-2 px-3 py-1.5 rounded-lg text-xs font-semibold"
                   style="background:var(--amber);color:#1a1a1a;">
                    Close Previous Session →
                </a>
            </div>
        </div>
    @endif

    @if ($todaySession && $todaySession->isOpen())
        <div wire:poll.30s="refreshSummary">

            {{-- Session status bar --}}
            <div class="rounded-xl px-4 py-3 mb-3 flex items-center justify-between gap-3" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="min-w-0">
                    <div class="text-xs" style="color:var(--text-dim);">
                        {{ $todaySession->session_date->format('d M Y') }}
                        @if ($todaySession->openedBy)
                            · Opened by {{ $todaySession->openedBy->name }}
                        @endif
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold flex-shrink-0" style="background:var(--green-dim);color:var(--green);">● Live</span>
            </div>

            @if ($liveSummary)
                {{-- Metrics grid --}}
                <div class="space-y-3 mb-3">

                    {{-- Sales card — full width, shows channel breakdown --}}
                    <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <div class="text-xs font-medium mb-1" style="color:var(--text-dim);">Total Sales</div>
                                <div class="font-mono font-bold text-2xl leading-none" style="color:var(--green);">
                                    {{ number_format($liveSummary['total_sales']) }}
                                    <span class="text-sm font-normal" style="color:var(--text-dim);">RWF</span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-xs" style="color:var(--text-faint);">{{ $liveSummary['transaction_count'] }} transactions</div>
                            </div>
                        </div>
                        {{-- Channel pills --}}
                        <div class="flex flex-wrap gap-2">
                            @if ($liveSummary['total_sales_cash'] > 0)
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg" style="background:var(--green-dim);">
                                    <span class="text-xs font-medium" style="color:var(--green);">Cash</span>
                                    <span class="text-xs font-mono font-bold" style="color:var(--green);">{{ number_format($liveSummary['total_sales_cash']) }}</span>
                                </div>
                            @endif
                            @if ($liveSummary['total_sales_momo'] > 0)
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg" style="background:var(--accent-dim);">
                                    <span class="text-xs font-medium" style="color:var(--accent);">MoMo</span>
                                    <span class="text-xs font-mono font-bold" style="color:var(--accent);">{{ number_format($liveSummary['total_sales_momo']) }}</span>
                                </div>
                            @endif
                            @if ($liveSummary['total_sales_card'] > 0)
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg" style="background:var(--surface-overlay);">
                                    <span class="text-xs font-medium" style="color:var(--text-dim);">Card</span>
                                    <span class="text-xs font-mono font-bold" style="color:var(--text);">{{ number_format($liveSummary['total_sales_card']) }}</span>
                                </div>
                            @endif
                            @if (($liveSummary['total_sales_bank_transfer'] ?? 0) > 0)
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg" style="background:var(--surface-overlay);">
                                    <span class="text-xs font-medium" style="color:var(--text-dim);">Bank Txfr</span>
                                    <span class="text-xs font-mono font-bold" style="color:var(--text);">{{ number_format($liveSummary['total_sales_bank_transfer']) }}</span>
                                </div>
                            @endif
                            @if (($liveSummary['total_sales_credit'] ?? 0) > 0)
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg" style="background:var(--amber-dim);">
                                    <span class="text-xs font-medium" style="color:var(--amber);">Credit</span>
                                    <span class="text-xs font-mono font-bold" style="color:var(--amber);">{{ number_format($liveSummary['total_sales_credit']) }}</span>
                                </div>
                            @endif
                            @if (($liveSummary['total_repayments_cash'] ?? 0) > 0)
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg" style="background:var(--accent-dim);">
                                    <span class="text-xs font-medium" style="color:var(--accent);">Repaid</span>
                                    <span class="text-xs font-mono font-bold" style="color:var(--accent);">{{ number_format($liveSummary['total_repayments_cash']) }}</span>
                                </div>
                            @endif
                            @if (($liveSummary['total_sales_other'] ?? 0) > 0)
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg" style="background:var(--surface-overlay);">
                                    <span class="text-xs font-medium" style="color:var(--text-dim);">Other</span>
                                    <span class="text-xs font-mono font-bold" style="color:var(--text);">{{ number_format($liveSummary['total_sales_other']) }}</span>
                                </div>
                            @endif
                            @if ($liveSummary['total_sales'] === 0)
                                <span class="text-xs" style="color:var(--text-faint);">No sales yet</span>
                            @endif
                        </div>
                    </div>

                    {{-- Secondary metrics row --}}
                    <div class="grid gap-3" style="grid-template-columns: repeat({{ ($liveSummary['total_repayments'] ?? 0) > 0 ? 4 : 3 }}, 1fr);">
                        <div class="rounded-xl p-3 text-center" style="background:var(--surface-raised);border:1px solid var(--border);">
                            <div class="text-xs font-medium mb-1" style="color:var(--text-dim);">Cash in Drawer</div>
                            <div class="font-mono font-bold text-base" style="color:var(--accent);">{{ number_format($liveSummary['expected_cash']) }}</div>
                            <div class="text-xs mt-0.5" style="color:var(--text-faint);">Expected</div>
                        </div>
                        <div class="rounded-xl p-3 text-center" style="background:var(--surface-raised);border:1px solid var(--border);">
                            <div class="text-xs font-medium mb-1" style="color:var(--text-dim);">Expenses</div>
                            <div class="font-mono font-bold text-base" style="color:var(--red);">{{ number_format($liveSummary['total_expenses']) }}</div>
                            <div class="text-xs mt-0.5" style="color:var(--text-faint);">{{ $liveSummary['expense_count'] }} items</div>
                        </div>
                        <div class="rounded-xl p-3 text-center" style="background:var(--surface-raised);border:1px solid var(--border);">
                            <div class="text-xs font-medium mb-1" style="color:var(--text-dim);">Withdrawn</div>
                            <div class="font-mono font-bold text-base" style="color:var(--amber);">{{ number_format($liveSummary['total_withdrawals']) }}</div>
                            <div class="text-xs mt-0.5" style="color:var(--text-faint);">{{ $liveSummary['withdrawal_count'] }} items</div>
                        </div>
                        @if (($liveSummary['total_repayments'] ?? 0) > 0)
                            <div class="rounded-xl p-3 text-center" style="background:var(--surface-raised);border:1px solid var(--border);">
                                <div class="text-xs font-medium mb-1" style="color:var(--text-dim);">Repayments</div>
                                <div class="font-mono font-bold text-base" style="color:var(--accent);">{{ number_format($liveSummary['total_repayments']) }}</div>
                                <div class="text-xs mt-0.5" style="color:var(--text-faint);">Credit collected</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Pending requests --}}
            @if ($pendingRequestsCount > 0)
                <div class="mb-3 px-4 py-3 rounded-xl flex items-center gap-3" style="background:var(--amber-dim);border:1px solid var(--amber);">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--amber);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="text-xs flex-1" style="color:var(--amber);">
                        {{ $pendingRequestsCount }} pending expense request{{ $pendingRequestsCount !== 1 ? 's' : '' }}
                    </span>
                    <a href="{{ route('shop.session.requests') }}" class="text-xs font-semibold" style="color:var(--amber);">Review →</a>
                </div>
            @endif

            {{-- Action buttons --}}
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('shop.expenses.add') }}"
                   class="flex items-center gap-2 px-3 py-3 rounded-xl font-semibold transition-opacity hover:opacity-80"
                   style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text);">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--amber-dim);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--amber);" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </span>
                    <span class="text-sm">Add Expense</span>
                </a>

                <a href="{{ route('shop.bank-deposits') }}"
                   class="flex items-center gap-2 px-3 py-3 rounded-xl font-semibold transition-opacity hover:opacity-80"
                   style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text);">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--accent-dim);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--accent);" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l9-3 9 3M3 6v12l9 3 9-3V6M12 3v18"/>
                        </svg>
                    </span>
                    <span class="text-sm">Bank Deposit</span>
                </a>

                <a href="{{ route('shop.withdrawals.add') }}"
                   class="flex items-center gap-2 px-3 py-3 rounded-xl font-semibold transition-opacity hover:opacity-80"
                   style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text);">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--red-dim);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--red);" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span class="text-sm">Withdrawal</span>
                </a>

                <a href="{{ route('shop.session.close', ['session' => $todaySession->id]) }}"
                   class="flex items-center gap-2 px-3 py-3 rounded-xl font-bold transition-opacity hover:opacity-80"
                   style="background:var(--amber);color:#1a1a1a;">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(0,0,0,0.15);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span class="text-sm">Close Day</span>
                </a>
            </div>

        </div>

    @elseif ($todaySession && ! $todaySession->isOpen())
        <div class="rounded-xl p-6 text-center" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center" style="background:var(--green-dim);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--green);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-sm font-semibold mb-1" style="color:var(--text);">Session Closed</div>
            <div class="text-xs mb-4" style="color:var(--text-dim);">Today's session has been closed and submitted.</div>
            <a href="{{ route('shop.session.history') }}" class="text-xs font-semibold" style="color:var(--accent);">View session history →</a>
        </div>

    @else
        <div class="rounded-xl p-5" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-sm font-semibold mb-4" style="color:var(--text);">Open Today's Session</div>

            @if (! $showOpenForm)
                <button wire:click="$set('showOpenForm', true)"
                        class="w-full px-4 py-3 rounded-xl text-sm font-semibold"
                        style="background:var(--accent);color:white;">
                    Open Day
                </button>
            @else
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Opening cash balance (RWF)</label>
                        <input type="number" wire:model="openingBalance" min="0"
                               class="w-full px-4 py-3 rounded-lg text-base"
                               style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                               placeholder="0" autofocus>
                        @error('openingBalance') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
                        @if ($openingBalanceHint)
                            <div class="text-xs mt-1" style="color:var(--text-faint);">{{ $openingBalanceHint }}</div>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="openDay"
                                wire:loading.attr="disabled"
                                class="flex-1 px-4 py-3 rounded-xl text-sm font-semibold"
                                style="background:var(--green);color:white;">
                            <span wire:loading.remove wire:target="openDay">Open Day</span>
                            <span wire:loading wire:target="openDay" style="display:none;">Opening…</span>
                        </button>
                        <button wire:click="$set('showOpenForm', false)"
                                class="px-4 py-3 rounded-xl text-sm"
                                style="background:var(--surface);color:var(--text-dim);border:1px solid var(--border);">
                            Cancel
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
