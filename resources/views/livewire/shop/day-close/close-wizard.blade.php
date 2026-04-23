<div>
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
    <div class="mb-8">
        <div class="flex items-center">
            @foreach ($steps as $n => $label)
                <div class="flex items-center {{ $n < 4 ? 'flex-1' : '' }}">
                    <div class="relative flex flex-col items-center">
                        {{-- Circle --}}
                        <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 flex-shrink-0"
                             style="
                                @if ($currentStep > $n)
                                    background:var(--green);color:#fff;box-shadow:0 0 0 4px var(--green-dim);
                                @elseif ($currentStep === $n)
                                    background:var(--accent);color:#fff;box-shadow:0 0 0 4px var(--accent-dim);
                                @else
                                    background:var(--surface-raised);color:var(--text-faint);border:2px solid var(--border);
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
                        <span class="absolute -bottom-5 text-xs font-semibold whitespace-nowrap"
                              style="@if($currentStep === $n) color:var(--accent); @elseif($currentStep > $n) color:var(--green); @else color:var(--text-faint); @endif">
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
        $stepHeaders = [
            1 => ['Sales Review',    'Review all transactions recorded today by payment channel.'],
            2 => ['Money Movements', 'Record bank deposits, operational expenses, and owner withdrawals.'],
            3 => ['Cash Count',      'Count the physical cash in the drawer and reconcile against expected.'],
            4 => ['Close Day',       'Confirm disposition of funds and submit the day close.'],
        ];
        [$stepTitle, $stepSub] = $stepHeaders[$currentStep];
    @endphp
    <div class="mb-5 mt-8">
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

        {{-- Total sales hero --}}
        <div class="rounded-2xl mb-4" style="border:1px solid var(--border);background:var(--surface-raised);">
            <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid var(--border);">
                <div>
                    <div class="text-xs font-semibold mb-1" style="color:var(--text-faint);text-transform:uppercase;letter-spacing:0.8px;">Total Sales Today</div>
                    <div class="font-mono font-bold" style="font-size:28px;letter-spacing:-1px;color:var(--text);">
                        {{ number_format($summary['total_sales'] ?? 0) }}
                        <span style="font-size:13px;color:var(--text-dim);font-weight:500;">RWF</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold" style="color:var(--text-faint);">{{ $summary['transaction_count'] ?? 0 }}</div>
                    <div class="text-xs mt-0.5" style="color:var(--text-faint);">transactions</div>
                </div>
            </div>
            {{-- Mini channel dots --}}
            <div class="flex px-5 py-3 gap-4">
                @foreach ([['Cash',$summary['total_sales_cash']??0,'#10b981'],['MoMo',$summary['total_sales_momo']??0,'#6366f1'],['Card',$summary['total_sales_card']??0,'#64748b'],['Credit',$summary['total_sales_credit']??0,'#f59e0b']] as [$lbl,$amt,$clr])
                    @if ($amt > 0)
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:{{ $clr }};"></span>
                            <span class="text-xs" style="color:var(--text-dim);">{{ $lbl }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Channel breakdown --}}
        <div class="rounded-2xl overflow-hidden mb-4" style="border:1px solid var(--border);">
            <div class="px-4 py-3" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                <span class="text-xs font-semibold" style="color:var(--text-dim);text-transform:uppercase;letter-spacing:0.6px;">By Payment Channel</span>
            </div>
            <div style="background:var(--surface);">
                @foreach ($channels as [$method, $amount, $color, $bg, $_show])
                    @php $pct = $total > 0 ? round($amount / $total * 100, 1) : 0; @endphp
                    <div class="px-4 py-3" style="border-bottom:1px solid var(--border);">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $color }};"></div>
                                <span class="text-sm" style="color:var(--text);">{{ $method }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-mono font-semibold text-sm" style="color:{{ $amount > 0 ? $color : 'var(--text-faint)' }};">
                                    {{ number_format($amount) }} RWF
                                </span>
                                <span class="text-xs ml-1.5" style="color:var(--text-faint);">{{ $pct }}%</span>
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
            <div class="rounded-2xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <span class="text-xs font-semibold" style="color:var(--text-dim);text-transform:uppercase;letter-spacing:0.6px;">Adjustments</span>
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
                            $otherRep = ($summary['total_repayments'] ?? 0) - ($summary['total_repayments_cash'] ?? 0) - ($summary['total_repayments_momo'] ?? 0);
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
                                @if ($otherRep > 0)
                                    <div class="flex justify-between text-xs">
                                        <span style="color:var(--text-dim);">Card / Bank</span>
                                        <span class="font-mono" style="color:var(--text-dim);">{{ number_format($otherRep) }} RWF</span>
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
        <div class="space-y-4">

            {{-- Quick balance summary bar --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl p-3" style="background:var(--green-dim);border:1px solid var(--green);">
                    <div class="text-xs font-medium mb-0.5" style="color:var(--green);opacity:0.75;">Cash Available</div>
                    <div class="font-mono font-bold text-base" style="color:var(--green);">{{ number_format($summary['expected_cash'] ?? 0) }} <span style="font-size:10px;opacity:0.7;">RWF</span></div>
                </div>
                <div class="rounded-xl p-3" style="background:var(--accent-dim);border:1px solid var(--accent);">
                    <div class="text-xs font-medium mb-0.5" style="color:var(--accent);opacity:0.75;">MoMo Available</div>
                    <div class="font-mono font-bold text-base" style="color:var(--accent);">{{ number_format($summary['momo_available'] ?? 0) }} <span style="font-size:10px;opacity:0.7;">RWF</span></div>
                </div>
            </div>

            {{-- Bank Deposits --}}
            <div class="rounded-2xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3.5 flex items-center justify-between" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:var(--accent-dim);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color:var(--accent);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold" style="color:var(--text);">Bank Deposits</div>
                            <div class="text-xs" style="color:var(--text-faint);">{{ $summary['bank_deposit_count'] ?? 0 }} recorded</div>
                        </div>
                    </div>
                    @if (($summary['total_bank_deposits'] ?? 0) > 0)
                        <span class="text-xs font-mono font-semibold px-2.5 py-1 rounded-full" style="background:var(--accent-dim);color:var(--accent);">
                            {{ number_format($summary['total_bank_deposits']) }} RWF
                        </span>
                    @endif
                </div>
                <div class="p-4" style="background:var(--surface);">
                    <livewire:shop.day-close.add-bank-deposit :dailySessionId="$dailySessionId" />
                </div>
            </div>

            {{-- Expenses --}}
            <div class="rounded-2xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3.5 flex items-center justify-between" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:var(--red-dim);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color:var(--red);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold" style="color:var(--text);">Operational Expenses</div>
                            <div class="text-xs" style="color:var(--text-faint);">{{ $summary['expense_count'] ?? 0 }} recorded</div>
                        </div>
                    </div>
                    @if (($summary['total_expenses'] ?? 0) > 0)
                        <span class="text-xs font-mono font-semibold px-2.5 py-1 rounded-full" style="background:var(--red-dim);color:var(--red);">
                            {{ number_format($summary['total_expenses']) }} RWF
                        </span>
                    @endif
                </div>
                <div class="p-4" style="background:var(--surface);">
                    <livewire:shop.day-close.expense-list :dailySessionId="$dailySessionId" />
                    <div class="mt-4 pt-4" style="border-top:1px solid var(--border);">
                        <livewire:shop.day-close.add-expense :dailySessionId="$dailySessionId" />
                    </div>
                </div>
            </div>

            {{-- Owner Withdrawals --}}
            <div class="rounded-2xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3.5 flex items-center justify-between" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:var(--amber-dim);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color:var(--amber);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold" style="color:var(--text);">Owner Withdrawals</div>
                            <div class="text-xs" style="color:var(--text-faint);">{{ $summary['withdrawal_count'] ?? 0 }} recorded</div>
                        </div>
                    </div>
                    @if (($summary['total_withdrawals'] ?? 0) > 0)
                        <span class="text-xs font-mono font-semibold px-2.5 py-1 rounded-full" style="background:var(--amber-dim);color:var(--amber);">
                            {{ number_format($summary['total_withdrawals']) }} RWF
                        </span>
                    @endif
                </div>
                <div class="p-4" style="background:var(--surface);">
                    <livewire:shop.day-close.withdrawal-list :dailySessionId="$dailySessionId" />
                    <div class="mt-4 pt-4" style="border-top:1px solid var(--border);">
                        <livewire:shop.day-close.add-withdrawal :dailySessionId="$dailySessionId" />
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════
         STEP 3 — Cash Count
    ════════════════════════════════════════════ --}}
    @if ($currentStep === 3)
        <div class="space-y-4">

            {{-- Cash drawer formula --}}
            <div class="rounded-2xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3.5" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <span class="text-xs font-semibold" style="color:var(--text-dim);text-transform:uppercase;letter-spacing:0.6px;">Cash Drawer Formula</span>
                </div>
                @php
                    $reconRows = [
                        ['Opening balance',            $session->opening_balance,                    null,    null],
                        ['Cash sales',                 $summary['total_sales_cash'] ?? 0,            'plus',  '#10b981'],
                        ['Cash repayments received',   $summary['total_repayments_cash'] ?? 0,       'plus',  '#10b981'],
                        ['Cash refunds paid out',      $summary['total_refunds_cash'] ?? 0,          'minus', '#ef4444'],
                        ['Cash expenses paid',         $summary['total_expenses_cash'] ?? 0,         'minus', '#ef4444'],
                        ['Owner cash withdrawals',     $summary['total_withdrawals_cash'] ?? 0,      'minus', '#f59e0b'],
                        ['Cash deposits to bank',      $summary['cash_deposits'] ?? 0,               'minus', '#6366f1'],
                    ];
                @endphp
                <div style="background:var(--surface);">
                    @foreach ($reconRows as [$lbl, $val, $sign, $clr])
                        @if ($val > 0 || $sign === null)
                            <div class="flex items-center justify-between px-4 py-2.5" style="border-bottom:1px solid var(--border);">
                                <div class="flex items-center gap-2">
                                    @if ($sign === 'plus')
                                        <span class="text-xs font-bold w-4 text-center" style="color:{{ $clr }};">+</span>
                                    @elseif ($sign === 'minus')
                                        <span class="text-xs font-bold w-4 text-center" style="color:{{ $clr }};">−</span>
                                    @else
                                        <span class="text-xs w-4 text-center" style="color:var(--text-faint);">=</span>
                                    @endif
                                    <span class="text-sm" style="color:{{ $sign ? 'var(--text-dim)' : 'var(--text)' }};">{{ $lbl }}</span>
                                </div>
                                <span class="font-mono text-sm font-{{ $sign ? 'medium' : 'semibold' }}"
                                      style="color:{{ $clr ?? 'var(--text)' }};">
                                    {{ number_format($val) }} RWF
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="flex items-center justify-between px-4 py-4" style="background:var(--accent-dim);border-top:2px solid var(--accent);">
                    <span class="text-sm font-bold" style="color:var(--text);">Expected in drawer</span>
                    <span class="font-mono font-bold text-xl" style="color:var(--accent);">{{ number_format($summary['expected_cash'] ?? 0) }} RWF</span>
                </div>
            </div>

            {{-- Actual cash input --}}
            <div class="rounded-2xl p-5" style="background:var(--surface-raised);border:1px solid var(--border);">
                <label class="block text-sm font-semibold mb-1" style="color:var(--text);">Count the physical cash</label>
                <p class="text-xs mb-4" style="color:var(--text-faint);">Enter the total amount of cash physically present in the drawer right now.</p>
                <input type="number"
                       wire:model.blur="actualCashCounted"
                       min="0"
                       class="w-full px-4 py-4 rounded-xl text-2xl font-mono text-center"
                       style="background:var(--surface);border:2px solid var(--border);color:var(--text);transition:border-color 0.2s;"
                       placeholder="0"
                       onfocus="this.style.borderColor='var(--accent)'"
                       onblur="this.style.borderColor='var(--border)'">
                @error('actualCashCounted')
                    <div class="text-xs mt-2" style="color:var(--red);">{{ $message }}</div>
                @enderror

                @if ($actualCashCounted > 0)
                    <div class="mt-4 rounded-xl overflow-hidden">
                        <div class="px-4 py-3 flex items-center gap-3"
                             style="{{ $cashVariance === 0
                                 ? 'background:var(--green-dim);border:1px solid var(--green);'
                                 : ($cashVariance > 0
                                     ? 'background:var(--amber-dim);border:1px solid var(--amber);'
                                     : 'background:var(--red-dim);border:1px solid var(--red);') }}
                                border-radius:12px;">
                            <div class="flex-1">
                                <div class="text-sm font-bold"
                                     style="color:{{ $cashVariance === 0 ? 'var(--green)' : ($cashVariance > 0 ? 'var(--amber)' : 'var(--red)') }};">
                                    @if ($cashVariance === 0) ✓ Balanced
                                    @elseif ($cashVariance > 0) Surplus
                                    @else Shortage
                                    @endif
                                </div>
                                <div class="text-xs mt-0.5" style="color:var(--text-dim);">
                                    @if ($cashVariance === 0) Drawer matches expected — great!
                                    @elseif ($cashVariance > 0) More cash than expected — please review
                                    @else Less cash than expected — please review
                                    @endif
                                </div>
                            </div>
                            <div class="font-mono font-bold text-lg"
                                 style="color:{{ $cashVariance === 0 ? 'var(--green)' : ($cashVariance > 0 ? 'var(--amber)' : 'var(--red)') }};">
                                {{ $cashVariance >= 0 ? '+' : '' }}{{ number_format($cashVariance) }} RWF
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sales by channel (reference) --}}
            <details class="rounded-2xl overflow-hidden" style="border:1px solid var(--border);">
                <summary class="px-4 py-3 cursor-pointer select-none flex items-center justify-between" style="background:var(--surface-raised);">
                    <span class="text-xs font-semibold" style="color:var(--text-dim);text-transform:uppercase;letter-spacing:0.6px;">Sales by Channel (reference)</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color:var(--text-faint);">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <div style="background:var(--surface);">
                    @php
                        $cardRef = $summary['total_sales_card'] ?? 0;
                        $bankRef = $summary['total_sales_bank_transfer'] ?? 0;
                        $channelRef = array_filter([
                            ['Cash',          $summary['total_sales_cash'] ?? 0, '#10b981', 'Adds to cash drawer',   true],
                            ['Mobile Money',  $summary['total_sales_momo'] ?? 0, '#6366f1', 'Not in cash drawer',    true],
                            ['Card',          $cardRef,                          '#64748b', 'Not in cash drawer',    $settingAllowCard || $cardRef > 0],
                            ['Bank Transfer', $bankRef,                          '#0ea5e9', 'Not in cash drawer',    $settingAllowBankTransfer || $bankRef > 0],
                            ['Credit',        $summary['total_sales_credit'] ?? 0,'#f59e0b','Owed by customers',    true],
                        ], fn($c) => $c[4]);
                    @endphp
                    @foreach ($channelRef as [$ch, $amt, $clr, $note, $_show])
                        <div class="flex items-center justify-between px-4 py-2.5" style="border-bottom:1px solid var(--border);">
                            <div>
                                <span class="text-sm" style="color:var(--text);">{{ $ch }}</span>
                                <span class="text-xs ml-2" style="color:var(--text-faint);">{{ $note }}</span>
                            </div>
                            <span class="font-mono text-sm font-medium" style="color:{{ $amt > 0 ? $clr : 'var(--text-faint)' }};">
                                {{ number_format($amt) }} RWF
                            </span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between px-4 py-3" style="background:var(--surface-raised);">
                        <span class="text-sm font-bold" style="color:var(--text);">Total</span>
                        <span class="font-mono text-sm font-bold" style="color:var(--accent);">{{ number_format($summary['total_sales'] ?? 0) }} RWF</span>
                    </div>
                </div>
            </details>
        </div>
    @endif

    {{-- ════════════════════════════════════════════
         STEP 4 — Close Day
    ════════════════════════════════════════════ --}}
    @if ($currentStep === 4)
        <div class="space-y-4">

            {{-- Session summary card --}}
            <div class="rounded-2xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3.5" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <span class="text-xs font-semibold" style="color:var(--text-dim);text-transform:uppercase;letter-spacing:0.6px;">Session Summary</span>
                </div>
                @php
                    $nonCashSales = ($summary['total_sales_momo'] ?? 0)
                                  + ($summary['total_sales_card'] ?? 0)
                                  + ($summary['total_sales_bank_transfer'] ?? 0)
                                  + ($summary['total_sales_credit'] ?? 0);

                    $nonCashChannels = array_filter([
                        'Mobile Money'  => $summary['total_sales_momo'] ?? 0,
                        'Card'          => $summary['total_sales_card'] ?? 0,
                        'Bank Transfer' => $summary['total_sales_bank_transfer'] ?? 0,
                        'Credit'        => $summary['total_sales_credit'] ?? 0,
                    ]);

                    $summaryRows = [
                        ['Opening Balance',           $summary['opening_balance'] ?? 0,         'var(--text-dim)', null],
                        ['Total Sales',               $summary['total_sales'] ?? 0,              '#10b981',         '+'],
                        ['Cash Repayments In',        $summary['total_repayments_cash'] ?? 0,    '#10b981',         '+'],
                        ['Non-cash Collected',        $nonCashSales,                             '#6366f1',         '−'],
                        ['Cash Refunds',              $summary['total_refunds_cash'] ?? 0,       '#ef4444',         '−'],
                        ['Cash Expenses',             $summary['total_expenses_cash'] ?? 0,      '#ef4444',         '−'],
                        ['Cash Withdrawals',          $summary['total_withdrawals_cash'] ?? 0,   '#f59e0b',         '−'],
                        ['Cash Deposits to Bank',     $summary['cash_deposits'] ?? 0,            '#6366f1',         '−'],
                    ];
                @endphp
                <div style="background:var(--surface);">
                    @foreach ($summaryRows as [$lbl, $val, $clr, $sign])
                        @if ($val > 0 || $sign === null)
                            <div class="flex items-center justify-between px-4 py-2.5" style="border-bottom:1px solid var(--border);">
                                <span class="text-sm" style="color:var(--text-dim);">{{ $lbl }}</span>
                                <span class="font-mono text-sm font-medium" style="color:{{ $clr }};">
                                    {{ $sign }}{{ number_format($val) }} RWF
                                </span>
                            </div>
                            {{-- Non-cash channel breakdown (sub-rows) --}}
                            @if ($lbl === 'Non-cash Collected' && count($nonCashChannels) > 1)
                                @foreach ($nonCashChannels as $chName => $chAmt)
                                    <div class="flex items-center justify-between px-4 py-1.5" style="border-bottom:1px solid var(--border);background:var(--surface-raised);">
                                        <span class="text-xs" style="color:var(--text-faint);padding-left:12px;">↳ {{ $chName }}</span>
                                        <span class="font-mono text-xs" style="color:var(--text-faint);">{{ number_format($chAmt) }} RWF</span>
                                    </div>
                                @endforeach
                            @endif
                        @endif
                    @endforeach
                </div>
                <div style="background:var(--surface-raised);border-top:2px solid var(--border);">
                    <div class="flex items-center justify-between px-4 py-2.5" style="border-bottom:1px solid var(--border);">
                        <span class="text-sm" style="color:var(--text-dim);">Expected cash</span>
                        <span class="font-mono text-sm font-semibold" style="color:var(--accent);">{{ number_format($summary['expected_cash'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-2.5" style="border-bottom:1px solid var(--border);">
                        <span class="text-sm" style="color:var(--text-dim);">Actual cash counted</span>
                        <span class="font-mono text-sm font-semibold" style="color:var(--text);">{{ number_format($actualCashCounted) }} RWF</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm font-semibold" style="color:var(--text);">Variance</span>
                        <span class="font-mono text-sm font-bold px-3 py-1 rounded-lg"
                              style="background:{{ $cashVariance === 0 ? 'var(--green-dim)' : ($cashVariance > 0 ? 'var(--amber-dim)' : 'var(--red-dim)') }};
                                     color:{{ $cashVariance === 0 ? 'var(--green)' : ($cashVariance > 0 ? 'var(--amber)' : 'var(--red)') }};">
                            {{ $cashVariance >= 0 ? '+' : '' }}{{ number_format($cashVariance) }} RWF
                        </span>
                    </div>
                </div>
            </div>

            {{-- Cash Disposition --}}
            <div class="rounded-2xl p-5" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:var(--amber-dim);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color:var(--amber);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold" style="color:var(--text);">Cash Disposition</span>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Send to owner via MoMo (RWF)</label>
                        <input type="number" wire:model.blur="cashToOwnerMomo" min="0"
                               @input="$dispatch('momo-deduction-changed', { val: parseInt($event.target.value) || 0 })"
                               class="w-full px-4 py-3 rounded-xl text-base font-mono"
                               style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                               placeholder="0">
                        @error('cashToOwnerMomo')
                            <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ($cashToOwnerMomo > 0)
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">MoMo reference (optional)</label>
                            <input type="text" wire:model="ownerMomoReference"
                                   class="w-full px-4 py-3 rounded-xl text-sm font-mono"
                                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                                   placeholder="Transaction ID or confirmation code">
                        </div>
                    @endif

                    <div class="flex items-center justify-between px-4 py-3 rounded-xl" style="background:var(--surface);border:1px solid var(--border);">
                        <span class="text-sm" style="color:var(--text-dim);">Retained in shop</span>
                        <span class="font-mono font-bold text-lg" style="color:var(--text);">{{ number_format($cashRetained) }} RWF</span>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Notes (optional)</label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full px-4 py-2.5 rounded-xl text-sm"
                                  style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                                  placeholder="Any notes for the owner…"></textarea>
                    </div>
                </div>
            </div>

            {{-- Non-cash settlement --}}
            @php
                $ncCard = $summary['total_sales_card'] ?? 0;
                $ncBank = $summary['total_sales_bank_transfer'] ?? 0;
                $ncChannels = array_filter([
                    ['Mobile Money',  'momoSettled',        'momoSettledRef',        $summary['total_sales_momo'] ?? 0, '#6366f1', true],
                    ['Card',          'cardSettled',        'cardSettledRef',        $ncCard,                          '#64748b', $settingAllowCard || $ncCard > 0],
                    ['Bank Transfer', 'bankTransferSettled','bankTransferSettledRef',$ncBank,                          '#0ea5e9', $settingAllowBankTransfer || $ncBank > 0],
                    ['Other',         'otherSettled',       'otherSettledRef',       $summary['total_sales_other'] ?? 0,'#94a3b8', true],
                ], fn($c) => $c[5]);
                $creditSales = $summary['total_sales_credit'] ?? 0;
                $hasNonCash  = collect($ncChannels)->contains(fn ($c) => $c[3] > 0) || $creditSales > 0;
            @endphp
            @if ($hasNonCash)
                <div class="rounded-2xl overflow-hidden" style="border:1px solid var(--border);">
                    <div class="px-4 py-3.5" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                        <div class="text-sm font-semibold" style="color:var(--text);">Non-Cash Settlement</div>
                        <div class="text-xs mt-0.5" style="color:var(--text-dim);">Record how each channel's revenue was settled or transferred to the owner</div>
                    </div>
                    <div style="background:var(--surface);">
                        @foreach ($ncChannels as [$label, $field, $refField, $total, $color, $_show])
                            @if ($total > 0)
                                <div class="px-4 py-3.5" style="border-bottom:1px solid var(--border);">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full" style="background:{{ $color }};"></div>
                                            <span class="text-sm font-medium" style="color:var(--text);">{{ $label }}</span>
                                        </div>
                                        <span class="text-xs font-mono px-2 py-0.5 rounded-full" style="background:var(--surface-raised);color:{{ $color }};">
                                            {{ number_format($total) }} RWF in sales
                                        </span>
                                    </div>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Amount settled / transferred (RWF)</label>
                                            <input type="number" wire:model.blur="{{ $field }}" min="0"
                                                   class="w-full px-3 py-2.5 rounded-lg text-sm font-mono"
                                                   style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text);"
                                                   placeholder="0">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Reference (optional)</label>
                                            <input type="text" wire:model="{{ $refField }}"
                                                   class="w-full px-3 py-2.5 rounded-lg text-sm"
                                                   style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text);"
                                                   placeholder="Transaction ID, confirmation code…">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        @if ($creditSales > 0)
                            <div class="px-4 py-3.5" style="background:var(--amber-dim);">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full" style="background:#f59e0b;"></div>
                                        <span class="text-sm font-medium" style="color:var(--amber);">Credit Sales</span>
                                    </div>
                                    <span class="font-mono text-sm font-semibold" style="color:var(--amber);">{{ number_format($creditSales) }} RWF</span>
                                </div>
                                <p class="text-xs ml-4" style="color:var(--text-dim);">Owed by customers — tracked via credit accounts. No settlement needed here.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Navigation ── --}}
    <div class="mt-6 flex items-center gap-3">
        @if ($currentStep > 1)
            <button wire:click="prevStep"
                    class="flex items-center gap-1.5 px-5 py-3 rounded-xl text-sm font-medium flex-shrink-0 transition-opacity hover:opacity-80"
                    style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </button>
        @endif

        <div class="flex-1">
            @if ($currentStep < 4)
                <button wire:click="nextStep"
                        wire:key="btn-next-step"
                        class="w-full flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold transition-opacity hover:opacity-90"
                        style="background:var(--accent);color:white;">
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
                        class="w-full flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl text-sm font-bold transition-opacity hover:opacity-90"
                        style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#1a1a1a;box-shadow:0 4px 14px rgba(245,158,11,0.35);">
                    <span wire:loading.remove wire:target="submitClose">
                        <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Close Register & Submit
                    </span>
                    <span wire:loading wire:target="submitClose" style="display:none;">Closing…</span>
                </button>
                <p class="text-xs text-center mt-2" style="color:var(--text-faint);">Re-openable until owner locks the session</p>
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
    $cashBase    = $actualCashCounted > 0 ? (int) $actualCashCounted : (int) ($summary['expected_cash'] ?? 0);
    $floatCash   = $cashBase - (int) ($cashToOwnerMomo ?? 0);
    $floatMomo   = $summary['momo_available']  ?? 0;
    $cashOk      = $floatCash >= 0;
    $momoOk      = $floatMomo >= 0;
@endphp
<div x-data="{
         open: true,
         cashBase: {{ $cashBase }},
         momoDeduction: {{ (int) ($cashToOwnerMomo ?? 0) }},
         get displayCash() { return this.cashBase - this.momoDeduction; }
     }"
     @momo-deduction-changed.window="momoDeduction = $event.detail.val"
     style="position:fixed;bottom:24px;right:20px;z-index:999;">

    {{-- Collapsed pill --}}
    <div x-show="!open" x-cloak
         @click="open = true"
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
            <span class="w-2 h-2 rounded-full inline-block" style="background:{{ $momoOk ? 'var(--accent)' : 'var(--red)' }};"></span>
            <span class="font-mono text-xs font-bold" style="color:{{ $momoOk ? 'var(--accent)' : 'var(--red)' }};">{{ number_format($floatMomo) }}</span>
        </span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="color:var(--text-faint);">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
        </svg>
    </div>

    {{-- Expanded panel --}}
    <div x-show="open"
         style="width:220px;
                backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
                background:rgba(var(--surface-rgb,255,255,255),0.92);
                border:1px solid var(--border);border-radius:20px;
                box-shadow:0 12px 40px rgba(0,0,0,0.14);
                overflow:hidden;">

        {{-- Header --}}
        <div style="padding:12px 14px 10px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);">
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-dim);">Live Balances</span>
            <button @click="open = false" style="background:none;border:none;cursor:pointer;padding:2px;line-height:0;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="color:var(--text-faint);">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        {{-- Cash row --}}
        <div style="padding:12px 14px;border-bottom:1px solid var(--border);">
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
        <div style="padding:12px 14px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <div style="width:24px;height:24px;border-radius:8px;background:{{ $momoOk ? 'var(--accent-dim)' : 'var(--red-dim)' }};
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="12" height="12" fill="none" stroke="{{ $momoOk ? 'var(--accent)' : 'var(--red)' }}" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span style="font-size:11px;font-weight:600;color:var(--text-dim);">Mobile Money</span>
                </div>
                <span style="font-size:13px;font-weight:700;font-family:var(--font-mono,monospace);color:{{ $momoOk ? 'var(--accent)' : 'var(--red)' }};">
                    {{ number_format($floatMomo) }}
                </span>
            </div>
            <div style="height:3px;border-radius:999px;background:var(--border);overflow:hidden;">
                @php $momoFillPct = $floatMomo > 0 ? min(100, round($floatMomo / max(1, $summary['total_sales_momo'] ?? 1) * 100)) : 0; @endphp
                <div style="height:100%;border-radius:999px;width:{{ $momoFillPct }}%;background:{{ $momoOk ? 'var(--accent)' : 'var(--red)' }};transition:width 0.5s ease;"></div>
            </div>
        </div>

        {{-- Footer hint --}}
        <div style="padding:6px 14px 10px;text-align:center;">
            <span style="font-size:9px;color:var(--text-faint);text-transform:uppercase;letter-spacing:0.5px;">Updates as you record · RWF</span>
        </div>
    </div>
</div>
</div>
