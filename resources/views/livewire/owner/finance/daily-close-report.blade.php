<div>
    {{-- Flash messages --}}
    @if (session()->has('success'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:var(--green-dim);color:var(--green);">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    {{-- Day navigation --}}
    <div class="mb-5 flex items-center gap-2">
        <button wire:click="previousDay"
                class="p-2 rounded-lg flex-shrink-0 transition-colors hover:opacity-80"
                style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        <input type="date" wire:model.live="reportDate"
               class="flex-1 sm:flex-none px-3 py-2 rounded-lg text-sm font-medium"
               style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text);"
               max="{{ today()->toDateString() }}">

        <button wire:click="nextDay"
                class="p-2 rounded-lg flex-shrink-0 transition-colors hover:opacity-80"
                style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);"
                @if(\Carbon\Carbon::parse($reportDate)->isToday()) disabled style="background:var(--surface-raised);color:var(--text-faint);border:1px solid var(--border);opacity:0.4;cursor:default;" @endif>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7 7 7" />
            </svg>
        </button>

        @unless(\Carbon\Carbon::parse($reportDate)->isToday())
            <button wire:click="goToToday"
                    class="px-3 py-2 rounded-lg text-xs font-medium flex-shrink-0 transition-colors hover:opacity-80"
                    style="background:var(--accent-dim);color:var(--accent);border:1px solid var(--accent);">
                Today
            </button>
        @endunless

        <div class="flex-1 text-right text-sm font-medium hidden sm:block" style="color:var(--text-dim);">
            {{ \Carbon\Carbon::parse($reportDate)->format('l, d M Y') }}
        </div>
    </div>

    @if ($sessions->isEmpty())
        <div class="text-center py-16 rounded-xl" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center" style="background:var(--surface-overlay);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" style="color:var(--text-faint);">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
                </svg>
            </div>
            <div class="text-sm font-medium" style="color:var(--text-dim);">No sessions found</div>
            <div class="text-xs mt-1" style="color:var(--text-faint);">{{ \Carbon\Carbon::parse($reportDate)->format('d M Y') }} had no activity</div>
        </div>
    @else

        {{-- Day summary strip --}}
        @php
            $dayRevenue     = $sessions->sum('total_sales');
            $dayExpenses    = $sessions->sum('total_expenses');
            $dayWithdrawals = $sessions->sum('total_withdrawals');
            $dayBanked      = $sessions->sum('total_bank_deposits');
            $dayVariance    = $sessions->sum('cash_variance');
            $totalSessions  = $sessions->count();
            $closedSessions = $sessions->whereIn('status', ['closed', 'locked'])->count();
            $allClosed      = $closedSessions === $totalSessions;
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            @foreach ([
                ['Revenue',     number_format($dayRevenue) . ' RWF',     'var(--green)'],
                ['Expenses',    number_format($dayExpenses) . ' RWF',    'var(--red)'],
                ['Withdrawals', number_format($dayWithdrawals) . ' RWF', 'var(--amber)'],
                ['Banked',      number_format($dayBanked) . ' RWF',      'var(--accent)'],
            ] as [$label, $value, $color])
                <div class="rounded-xl p-3" style="background:var(--surface-raised);border:1px solid var(--border);">
                    <div class="text-xs mb-1" style="color:var(--text-dim);">{{ $label }}</div>
                    <div class="font-mono font-bold text-sm" style="color:{{ $color }};">{{ $value }}</div>
                </div>
            @endforeach

            {{-- Variance (signed) --}}
            <div class="rounded-xl p-3" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="text-xs mb-1" style="color:var(--text-dim);">Variance</div>
                <div class="font-mono font-bold text-sm"
                     style="color:{{ $dayVariance > 0 ? 'var(--green)' : ($dayVariance < 0 ? 'var(--red)' : 'var(--text-dim)') }};">
                    {{ $dayVariance >= 0 ? '+' : '' }}{{ number_format($dayVariance) }} RWF
                </div>
            </div>

            {{-- Sessions / status --}}
            <div class="rounded-xl p-3" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="text-xs mb-1" style="color:var(--text-dim);">Sessions</div>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-sm" style="color:var(--text);">{{ $totalSessions }}</span>
                    @if ($allClosed)
                        <span class="text-xs px-1.5 py-0.5 rounded" style="background:var(--green-dim);color:var(--green);">All closed</span>
                    @else
                        <span class="text-xs px-1.5 py-0.5 rounded" style="background:var(--amber-dim);color:var(--amber);">{{ $closedSessions }}/{{ $totalSessions }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Session cards --}}
        <div class="space-y-4">
            @foreach ($sessions as $session)
                @php
                    $isExpanded = $expandedSessionId === $session->id;
                    $statusStyles = [
                        'open'   => 'background:var(--green-dim);color:var(--green);',
                        'closed' => 'background:var(--amber-dim);color:var(--amber);',
                        'locked' => 'background:var(--surface-overlay);color:var(--text-dim);border:1px solid var(--border);',
                    ];
                    $ss = $statusStyles[$session->status] ?? '';
                @endphp
                <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">

                    {{-- Card header --}}
                    <div class="p-4" style="background:var(--surface-raised);">
                        <div class="flex items-start justify-between gap-3">
                            {{-- Left: shop + status + times --}}
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <button wire:click="toggleExpand({{ $session->id }})"
                                        class="mt-0.5 p-1 rounded transition-colors hover:bg-[var(--surface2)]">
                                    <svg class="w-4 h-4 transition-transform {{ $isExpanded ? 'rotate-90' : '' }}"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                                         style="color:var(--text-dim);">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-semibold text-sm" style="color:var(--text);">{{ $session->shop->name ?? '—' }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded font-medium" style="{{ $ss }}">{{ ucfirst($session->status) }}</span>
                                    </div>
                                    <div class="text-xs mt-1 space-y-0.5" style="color:var(--text-dim);">
                                        <div>Opened {{ $session->opened_at->format('H:i') }} by {{ $session->openedBy->name ?? '—' }}</div>
                                        @if ($session->closed_at)
                                            <div>Closed {{ $session->closed_at->format('H:i') }} by {{ $session->closedBy->name ?? '—' }}</div>
                                        @endif
                                        @if ($session->locked_at)
                                            <div>Locked {{ $session->locked_at->format('H:i') }} by {{ $session->lockedBy->name ?? '—' }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Right: quick metrics + lock button --}}
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <div class="hidden sm:flex items-center gap-4">
                                    <div class="text-right">
                                        <div class="text-xs" style="color:var(--text-dim);">Revenue</div>
                                        <div class="font-mono font-semibold text-sm" style="color:var(--green);">{{ number_format($session->total_sales ?? 0) }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs" style="color:var(--text-dim);">Expenses</div>
                                        <div class="font-mono font-semibold text-sm" style="color:var(--text);">{{ number_format($session->total_expenses ?? 0) }}</div>
                                    </div>
                                    @if ($session->cash_variance !== null)
                                        <div class="text-right">
                                            <div class="text-xs" style="color:var(--text-dim);">Variance</div>
                                            <div class="font-mono font-semibold text-sm"
                                                 style="{{ $session->cash_variance > 0 ? 'color:var(--green)' : ($session->cash_variance < 0 ? 'color:var(--red)' : 'color:var(--text-dim)') }}">
                                                {{ $session->cash_variance >= 0 ? '+' : '' }}{{ number_format($session->cash_variance) }}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if ($session->status === 'closed')
                                    <button wire:click="lockSession({{ $session->id }})"
                                            wire:confirm="Lock this session? It cannot be edited after locking."
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors hover:opacity-80"
                                            style="background:var(--surface-overlay);color:var(--text-dim);border:1px solid var(--border);">
                                        Lock
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Mobile metric strip --}}
                        <div class="mt-3 grid grid-cols-3 gap-2 sm:hidden">
                            <div class="rounded-lg p-2 text-center" style="background:var(--surface);border:1px solid var(--border);">
                                <div class="text-xs" style="color:var(--text-faint);">Revenue</div>
                                <div class="font-mono text-xs font-semibold mt-0.5" style="color:var(--green);">{{ number_format($session->total_sales ?? 0) }}</div>
                            </div>
                            <div class="rounded-lg p-2 text-center" style="background:var(--surface);border:1px solid var(--border);">
                                <div class="text-xs" style="color:var(--text-faint);">Expenses</div>
                                <div class="font-mono text-xs font-semibold mt-0.5" style="color:var(--text);">{{ number_format($session->total_expenses ?? 0) }}</div>
                            </div>
                            <div class="rounded-lg p-2 text-center" style="background:var(--surface);border:1px solid var(--border);">
                                <div class="text-xs" style="color:var(--text-faint);">Variance</div>
                                <div class="font-mono text-xs font-semibold mt-0.5"
                                     style="{{ ($session->cash_variance ?? 0) > 0 ? 'color:var(--green)' : (($session->cash_variance ?? 0) < 0 ? 'color:var(--red)' : 'color:var(--text-dim)') }}">
                                    {{ ($session->cash_variance ?? 0) >= 0 ? '+' : '' }}{{ number_format($session->cash_variance ?? 0) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Expanded detail --}}
                    @if ($isExpanded)
                        <div class="p-4 space-y-5" style="background:var(--surface);border-top:1px solid var(--border);">

                            {{-- Timeline strip --}}
                            <div>
                                <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-faint);">Timeline</div>
                                <div class="flex items-start gap-0">
                                    {{-- Opened --}}
                                    <div class="flex flex-col items-center mr-3">
                                        <div class="w-3 h-3 rounded-full flex-shrink-0" style="background:var(--green);"></div>
                                        <div class="w-px flex-1 mt-1" style="background:var(--border);min-height:16px;"></div>
                                    </div>
                                    <div class="pb-3 flex-1">
                                        <div class="text-xs font-semibold" style="color:var(--green);">Opened</div>
                                        <div class="text-xs mt-0.5" style="color:var(--text-dim);">
                                            {{ $session->opened_at->format('H:i') }} · {{ $session->openedBy->name ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                                @if ($session->closed_at)
                                    <div class="flex items-start gap-0">
                                        <div class="flex flex-col items-center mr-3">
                                            <div class="w-3 h-3 rounded-full flex-shrink-0" style="background:var(--amber);"></div>
                                            @if ($session->locked_at)
                                                <div class="w-px flex-1 mt-1" style="background:var(--border);min-height:16px;"></div>
                                            @endif
                                        </div>
                                        <div class="{{ $session->locked_at ? 'pb-3' : '' }} flex-1">
                                            <div class="text-xs font-semibold" style="color:var(--amber);">Closed</div>
                                            <div class="text-xs mt-0.5" style="color:var(--text-dim);">
                                                {{ $session->closed_at->format('H:i') }} · {{ $session->closedBy->name ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if ($session->locked_at)
                                    <div class="flex items-start gap-0">
                                        <div class="flex flex-col items-center mr-3">
                                            <div class="w-3 h-3 rounded-full flex-shrink-0" style="background:var(--text-dim);"></div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-xs font-semibold" style="color:var(--text-dim);">Locked</div>
                                            <div class="text-xs mt-0.5" style="color:var(--text-faint);">
                                                {{ $session->locked_at->format('H:i') }} · {{ $session->lockedBy->name ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Revenue by Channel --}}
                            <div>
                                <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-faint);">Revenue by Channel</div>
                                <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                                    @php
                                        $revChannels = [
                                            ['Cash',          $session->total_sales_cash             ?? 0, 'var(--green)'],
                                            ['Mobile Money',  $session->total_sales_momo              ?? 0, 'var(--accent)'],
                                            ['Card',          $session->total_sales_card              ?? 0, 'var(--text)'],
                                            ['Bank Transfer', $session->total_sales_bank_transfer     ?? 0, 'var(--text)'],
                                            ['Credit',        $session->total_sales_credit            ?? 0, 'var(--amber)'],
                                        ];
                                        $revTotal = $session->total_sales ?? 0;
                                    @endphp
                                    @foreach ($revChannels as [$ch, $amt, $color])
                                        <div class="flex items-center justify-between px-3 py-2" style="border-bottom:1px solid var(--border);">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs" style="color:var(--text-dim);">{{ $ch }}</div>
                                                @if ($ch === 'Credit' && $amt > 0)
                                                    <div class="text-xs" style="color:var(--amber);">Owed by customers</div>
                                                @endif
                                            </div>
                                            <div class="font-mono text-xs font-semibold" style="color:{{ $color }};">{{ number_format($amt) }} RWF</div>
                                        </div>
                                    @endforeach
                                    <div class="flex items-center justify-between px-3 py-2" style="background:var(--surface-raised);border-top:2px solid var(--border);">
                                        <div class="text-xs font-bold" style="color:var(--text);">Total</div>
                                        <div class="font-mono text-xs font-bold" style="color:var(--accent);">{{ number_format($revTotal) }} RWF</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Cash reconciliation --}}
                            <div>
                                <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-faint);">Cash Reconciliation</div>
                                <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                                    @php
                                        $recon = [
                                            ['Opening balance',        $session->opening_balance ?? 0,        false],
                                            ['+ Cash sales',           $session->total_sales_cash ?? 0,       false],
                                            ['− Cash refunds',         $session->total_refunds_cash ?? 0,     true],
                                            ['− Cash expenses',        $session->total_expenses_cash ?? 0,    true],
                                            ['− Cash withdrawals',     $session->total_withdrawals_cash ?? 0, true],
                                            ['− Bank deposits',        $session->total_bank_deposits ?? 0,    true],
                                            ['= Expected cash',        $session->expected_cash ?? 0,          false],
                                            ['Actual cash counted',    $session->actual_cash_counted ?? 0,    false],
                                            ['Sent to owner via MoMo', $session->cash_to_owner_momo ?? 0,     false],
                                            ['Cash retained in shop',  $session->cash_retained ?? 0,          false],
                                        ];
                                    @endphp
                                    @foreach ($recon as $i => [$label, $amount, $isDeduc])
                                        <div class="flex items-center justify-between px-3 py-2 {{ $i % 2 === 0 ? '' : '' }}"
                                             style="{{ str_starts_with($label, '=') ? 'background:var(--surface-raised);border-top:1px solid var(--border);border-bottom:1px solid var(--border);' : 'border-bottom:1px solid var(--border);' }}">
                                            <div class="text-xs {{ str_starts_with($label, '=') ? 'font-semibold' : '' }}"
                                                 style="{{ str_starts_with($label, '=') ? 'color:var(--text);' : 'color:var(--text-dim);' }}">
                                                {{ $label }}
                                            </div>
                                            <div class="font-mono text-xs {{ str_starts_with($label, '=') ? 'font-bold' : 'font-medium' }}"
                                                 style="{{ $isDeduc ? 'color:var(--red);' : (str_starts_with($label, '=') ? 'color:var(--text);' : 'color:var(--text);') }}">
                                                {{ number_format($amount) }} RWF
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Variance row highlighted --}}
                                    @if ($session->cash_variance !== null)
                                        <div class="flex items-center justify-between px-3 py-2"
                                             style="background:{{ $session->cash_variance < 0 ? 'var(--red-dim)' : ($session->cash_variance > 0 ? 'var(--green-dim)' : 'var(--surface-raised)') }};">
                                            <div class="text-xs font-semibold" style="color:var(--text);">Variance</div>
                                            <div class="font-mono text-xs font-bold"
                                                 style="color:{{ $session->cash_variance > 0 ? 'var(--green)' : ($session->cash_variance < 0 ? 'var(--red)' : 'var(--text-dim)') }};">
                                                {{ $session->cash_variance >= 0 ? '+' : '' }}{{ number_format($session->cash_variance) }} RWF
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Non-cash channel settlements --}}
                            @php
                                $reportSettlements = [
                                    ['Mobile Money',  $session->total_sales_momo          ?? 0, $session->momo_settled          ?? 0, $session->momo_settled_ref,          'var(--accent)'],
                                    ['Card',          $session->total_sales_card          ?? 0, $session->card_settled          ?? 0, $session->card_settled_ref,          'var(--text)'],
                                    ['Bank Transfer', $session->total_sales_bank_transfer ?? 0, $session->bank_transfer_settled ?? 0, $session->bank_transfer_settled_ref, 'var(--text)'],
                                    ['Other',         $session->total_sales_other         ?? 0, $session->other_settled         ?? 0, $session->other_settled_ref,         'var(--text)'],
                                ];
                                $creditAmt        = $session->total_sales_credit ?? 0;
                                $hasAnyNonCash    = collect($reportSettlements)->contains(fn ($r) => $r[1] > 0) || $creditAmt > 0;
                            @endphp
                            @if ($hasAnyNonCash)
                                <div>
                                    <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-faint);">Non-Cash Channel Settlement</div>
                                    <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                                        @foreach ($reportSettlements as [$ch, $salesAmt, $settledAmt, $ref, $color])
                                            @if ($salesAmt > 0)
                                                <div class="px-3 py-2.5 flex items-start justify-between gap-3" style="border-bottom:1px solid var(--border);">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-xs font-medium" style="color:var(--text);">{{ $ch }}</div>
                                                        <div class="text-xs mt-0.5" style="color:var(--text-dim);">
                                                            Sales: {{ number_format($salesAmt) }} RWF
                                                            @if ($ref) · Ref: {{ $ref }} @endif
                                                        </div>
                                                    </div>
                                                    <div class="text-right flex-shrink-0">
                                                        <div class="font-mono text-xs font-semibold" style="color:{{ $color }};">
                                                            {{ number_format($settledAmt) }} RWF
                                                        </div>
                                                        @if ($settledAmt < $salesAmt)
                                                            <div class="text-xs mt-0.5" style="color:var(--red);">
                                                                −{{ number_format($salesAmt - $settledAmt) }} unaccounted
                                                            </div>
                                                        @elseif ($settledAmt > 0)
                                                            <div class="text-xs mt-0.5" style="color:var(--green);">Settled</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach

                                        {{-- Credit: informational only --}}
                                        @if ($creditAmt > 0)
                                            <div class="px-3 py-2.5 flex items-start justify-between gap-3" style="background:var(--amber-dim);">
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-xs font-medium" style="color:var(--amber);">Credit</div>
                                                    <div class="text-xs mt-0.5" style="color:var(--text-dim);">Owed by customers — tracked via credit accounts</div>
                                                </div>
                                                <div class="font-mono text-xs font-semibold flex-shrink-0" style="color:var(--amber);">{{ number_format($creditAmt) }} RWF</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Where is the Money? --}}
                            @if ($session->status !== 'open')
                                @php
                                    $whereRows = [
                                        ['Cash in drawer',      $session->actual_cash_counted ?? $session->expected_cash ?? 0, 'var(--green)'],
                                        ['MoMo (settled)',      $session->momo_settled          ?? 0, 'var(--accent)'],
                                        ['Card (settled)',      $session->card_settled          ?? 0, 'var(--text)'],
                                        ['Bank Transfer',       $session->bank_transfer_settled ?? 0, 'var(--text)'],
                                        ['Credit outstanding',  $session->total_sales_credit    ?? 0, 'var(--amber)'],
                                    ];
                                    $whereHasData = collect($whereRows)->contains(fn ($r) => $r[1] > 0);
                                @endphp
                                @if ($whereHasData)
                                    <div>
                                        <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-faint);">Where is the Money?</div>
                                        <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                                            @foreach ($whereRows as [$loc, $amt, $color])
                                                <div class="flex items-center justify-between px-3 py-2" style="border-bottom:1px solid var(--border);">
                                                    <div class="text-xs" style="color:var(--text-dim);">{{ $loc }}</div>
                                                    <div class="font-mono text-xs font-semibold" style="color:{{ $color }};">{{ number_format($amt) }} RWF</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif

                            {{-- Expenses + Withdrawals row --}}
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                                {{-- Expenses --}}
                                <div>
                                    <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-faint);">
                                        Expenses
                                        @if ($session->expenses->count() > 0)
                                            <span class="ml-1 px-1.5 py-0.5 rounded font-normal normal-case" style="background:var(--surface-raised);color:var(--text-dim);">{{ $session->expenses->count() }}</span>
                                        @endif
                                    </div>
                                    @if ($session->expenses->isEmpty())
                                        <div class="text-xs py-3 text-center rounded-lg" style="color:var(--text-faint);background:var(--surface-raised);border:1px solid var(--border);">No expenses recorded</div>
                                    @else
                                        <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                                            @foreach ($session->expenses as $exp)
                                                <div class="px-3 py-2 flex items-start justify-between gap-3" style="border-bottom:1px solid var(--border);">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-xs font-medium" style="color:var(--text);">{{ $exp->category->name ?? '—' }}</div>
                                                        @if ($exp->description)
                                                            <div class="text-xs mt-0.5 truncate" style="color:var(--text-dim);">{{ $exp->description }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="font-mono text-xs font-semibold flex-shrink-0" style="color:var(--red);">{{ number_format($exp->amount) }} RWF</div>
                                                </div>
                                            @endforeach
                                            <div class="px-3 py-2 flex items-center justify-between" style="background:var(--surface-raised);">
                                                <div class="text-xs font-semibold" style="color:var(--text-dim);">Total</div>
                                                <div class="font-mono text-xs font-bold" style="color:var(--red);">{{ number_format($session->expenses->sum('amount')) }} RWF</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Owner Withdrawals --}}
                                <div>
                                    <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-faint);">
                                        Owner Withdrawals
                                        @if ($session->ownerWithdrawals->count() > 0)
                                            <span class="ml-1 px-1.5 py-0.5 rounded font-normal normal-case" style="background:var(--surface-raised);color:var(--text-dim);">{{ $session->ownerWithdrawals->count() }}</span>
                                        @endif
                                    </div>
                                    @if ($session->ownerWithdrawals->isEmpty())
                                        <div class="text-xs py-3 text-center rounded-lg" style="color:var(--text-faint);background:var(--surface-raised);border:1px solid var(--border);">No withdrawals recorded</div>
                                    @else
                                        <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                                            @foreach ($session->ownerWithdrawals as $wd)
                                                <div class="px-3 py-2 flex items-start justify-between gap-3" style="border-bottom:1px solid var(--border);">
                                                    <div class="flex-1 min-w-0">
                                                        @if ($wd->reason)
                                                            <div class="text-xs font-medium" style="color:var(--text);">{{ $wd->reason }}</div>
                                                        @endif
                                                        <div class="text-xs mt-0.5" style="color:var(--text-dim);">
                                                            {{ $wd->created_at->format('H:i') }}
                                                            @if ($wd->recordedBy) · by {{ $wd->recordedBy->name }} @endif
                                                        </div>
                                                    </div>
                                                    <div class="font-mono text-xs font-semibold flex-shrink-0" style="color:var(--amber);">{{ number_format($wd->amount) }} RWF</div>
                                                </div>
                                            @endforeach
                                            <div class="px-3 py-2 flex items-center justify-between" style="background:var(--surface-raised);">
                                                <div class="text-xs font-semibold" style="color:var(--text-dim);">Total</div>
                                                <div class="font-mono text-xs font-bold" style="color:var(--amber);">{{ number_format($session->ownerWithdrawals->sum('amount')) }} RWF</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Bank Deposits --}}
                            @if ($session->bankDeposits->isNotEmpty())
                                <div>
                                    <div class="text-xs font-semibold mb-2 uppercase tracking-wide" style="color:var(--text-faint);">
                                        Bank Deposits
                                        <span class="ml-1 px-1.5 py-0.5 rounded font-normal normal-case" style="background:var(--surface-raised);color:var(--text-dim);">{{ $session->bankDeposits->count() }}</span>
                                    </div>
                                    <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                                        @foreach ($session->bankDeposits as $dep)
                                            <div class="px-3 py-2 flex items-start justify-between gap-3" style="border-bottom:1px solid var(--border);">
                                                <div class="flex-1 min-w-0">
                                                    @if ($dep->bank_reference)
                                                        <div class="text-xs font-medium" style="color:var(--text);">Ref: {{ $dep->bank_reference }}</div>
                                                    @endif
                                                    @if ($dep->notes)
                                                        <div class="text-xs mt-0.5 truncate" style="color:var(--text-dim);">{{ $dep->notes }}</div>
                                                    @endif
                                                    <div class="text-xs mt-0.5" style="color:var(--text-dim);">
                                                        {{ $dep->deposited_at->format('H:i') }}
                                                        @if ($dep->depositedBy) · by {{ $dep->depositedBy->name }} @endif
                                                    </div>
                                                </div>
                                                <div class="font-mono text-xs font-semibold flex-shrink-0" style="color:var(--accent);">{{ number_format($dep->amount) }} RWF</div>
                                            </div>
                                        @endforeach
                                        <div class="px-3 py-2 flex items-center justify-between" style="background:var(--surface-raised);">
                                            <div class="text-xs font-semibold" style="color:var(--text-dim);">Total</div>
                                            <div class="font-mono text-xs font-bold" style="color:var(--accent);">{{ number_format($session->bankDeposits->sum('amount')) }} RWF</div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($session->notes)
                                <div class="rounded-lg px-3 py-2 text-xs" style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text-dim);">
                                    <span class="font-semibold" style="color:var(--text);">Notes:</span> {{ $session->notes }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
