<div>
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-3 rounded-xl text-sm" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    {{-- Step Indicator --}}
    <div class="flex items-center mb-6">
        @foreach ([1 => 'Sales', 2 => 'Movements', 3 => 'Cash Count', 4 => 'Close'] as $step => $label)
            <div class="flex items-center {{ $step < 4 ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center gap-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 transition-all"
                         style="{{ $currentStep === $step
                             ? 'background:var(--accent);color:white;box-shadow:0 0 0 3px var(--accent-dim);'
                             : ($currentStep > $step
                                 ? 'background:var(--green);color:white;'
                                 : 'background:var(--surface-raised);color:var(--text-dim);border:2px solid var(--border);') }}">
                        @if ($currentStep > $step)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            {{ $step }}
                        @endif
                    </div>
                    <span class="text-xs font-medium text-center"
                          style="{{ $currentStep === $step ? 'color:var(--accent);' : 'color:var(--text-faint);' }}">
                        {{ $label }}
                    </span>
                </div>
                @if ($step < 4)
                    <div class="flex-1 h-px mx-2 mb-4 transition-colors"
                         style="background:{{ $currentStep > $step ? 'var(--green)' : 'var(--border)' }};"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ── Step 1: Sales Review ── --}}
    @if ($currentStep === 1)
        <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
            <div class="px-4 py-3" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                <div class="text-sm font-semibold" style="color:var(--text);">Today's Sales</div>
            </div>

            @php
                $total = $summary['total_sales'] ?: 1;
                $channels = [
                    ['Cash',          $summary['total_sales_cash'],             'var(--green)',  'var(--green-dim)'],
                    ['Mobile Money',  $summary['total_sales_momo'],             'var(--accent)', 'var(--accent-dim)'],
                    ['Card',          $summary['total_sales_card'],             'var(--text)',   'var(--surface-overlay)'],
                    ['Bank Transfer', $summary['total_sales_bank_transfer'],    'var(--text)',   'var(--surface-overlay)'],
                    ['Credit',        $summary['total_sales_credit'],           'var(--amber)',  'var(--amber-dim)'],
                ];
            @endphp

            <div class="divide-y" style="--tw-divide-opacity:1;">
                @foreach ($channels as [$method, $amount, $color, $bg])
                    <div class="flex items-center px-4 py-3 gap-3" style="border-color:var(--border);">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm" style="color:var(--text);">{{ $method }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono font-semibold text-sm" style="color:{{ $color }};">
                                {{ number_format($amount) }} RWF
                            </div>
                            <div class="text-xs mt-0.5" style="color:var(--text-faint);">
                                {{ $summary['total_sales'] > 0 ? round($amount / $total * 100, 1) : 0 }}%
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-4 py-3 flex items-center justify-between" style="background:var(--surface-raised);border-top:2px solid var(--border);">
                <div>
                    <div class="text-sm font-bold" style="color:var(--text);">Total Sales</div>
                    <div class="text-xs mt-0.5" style="color:var(--text-dim);">{{ $summary['transaction_count'] }} transactions</div>
                </div>
                <div class="font-mono font-bold text-lg" style="color:var(--accent);">
                    {{ number_format($summary['total_sales']) }} RWF
                </div>
            </div>

            @if (($summary['total_refunds_cash'] ?? 0) > 0)
                <div class="px-4 py-2.5 flex items-center justify-between" style="background:var(--red-dim);border-top:1px solid var(--border);">
                    <div class="text-xs" style="color:var(--red);">Cash refunds deducted</div>
                    <div class="font-mono text-xs font-semibold" style="color:var(--red);">−{{ number_format($summary['total_refunds_cash']) }} RWF</div>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Step 2: Money Movements ── --}}
    @if ($currentStep === 2)
        <div class="space-y-4">

            {{-- Bank Deposits --}}
            <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3 flex items-center justify-between" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <div class="text-sm font-semibold" style="color:var(--text);">Bank Deposits</div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:var(--accent-dim);color:var(--accent);">
                        {{ number_format($summary['total_bank_deposits'] ?? 0) }} RWF · {{ $summary['bank_deposit_count'] ?? 0 }} deposit(s)
                    </span>
                </div>
                <div class="p-4" style="background:var(--surface);">
                    <livewire:shop.day-close.add-bank-deposit :dailySessionId="$dailySessionId" />
                </div>
            </div>

            {{-- Expenses --}}
            <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3 flex items-center justify-between" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <div class="text-sm font-semibold" style="color:var(--text);">Operational Expenses</div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:var(--red-dim);color:var(--red);">
                        {{ number_format($summary['total_expenses'] ?? 0) }} RWF
                    </span>
                </div>
                <div class="p-4" style="background:var(--surface);">
                    <livewire:shop.day-close.expense-list :dailySessionId="$dailySessionId" />
                    <div class="mt-4 pt-4" style="border-top:1px solid var(--border);">
                        <livewire:shop.day-close.add-expense :dailySessionId="$dailySessionId" />
                    </div>
                </div>
            </div>

            {{-- Owner Withdrawals --}}
            <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3 flex items-center justify-between" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <div class="text-sm font-semibold" style="color:var(--text);">Owner Withdrawals</div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:var(--amber-dim);color:var(--amber);">
                        {{ number_format($summary['total_withdrawals'] ?? 0) }} RWF
                    </span>
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

    {{-- ── Step 3: Cash Count ── --}}
    @if ($currentStep === 3)
        <div class="space-y-4">

            {{-- All sales by channel --}}
            <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <div class="text-sm font-semibold" style="color:var(--text);">Sales by Payment Channel</div>
                </div>
                <div class="divide-y" style="background:var(--surface);">
                    @php
                        $channels = [
                            ['Cash',          $summary['total_sales_cash'],          'var(--green)',  true,  null],
                            ['Mobile Money',  $summary['total_sales_momo'],          'var(--accent)', false, null],
                            ['Card',          $summary['total_sales_card'],          'var(--text)',   false, null],
                            ['Bank Transfer', $summary['total_sales_bank_transfer'], 'var(--text)',   false, null],
                            ['Credit',        $summary['total_sales_credit'],        'var(--amber)',  false, 'Owed by customers'],
                        ];
                    @endphp
                    @foreach ($channels as [$ch, $amt, $clr, $affectsCash, $sub])
                        <div class="flex items-center px-4 py-3 gap-3" style="border-color:var(--border);">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm" style="color:var(--text);">{{ $ch }}</div>
                                @if ($sub)
                                    <div class="text-xs mt-0.5" style="color:var(--amber);">{{ $sub }}</div>
                                @elseif (! $affectsCash)
                                    <div class="text-xs mt-0.5" style="color:var(--text-faint);">Not in cash drawer</div>
                                @else
                                    <div class="text-xs mt-0.5" style="color:var(--green);">Adds to cash drawer</div>
                                @endif
                            </div>
                            <div class="font-mono font-semibold text-sm" style="color:{{ $clr }};">
                                {{ number_format($amt) }} RWF
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-4 py-3 flex items-center justify-between" style="background:var(--surface-raised);border-top:2px solid var(--border);">
                    <div class="text-sm font-bold" style="color:var(--text);">Total</div>
                    <div class="font-mono font-bold text-base" style="color:var(--accent);">{{ number_format($summary['total_sales']) }} RWF</div>
                </div>
            </div>

            {{-- Cash drawer reconciliation --}}
            <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <div class="text-sm font-semibold" style="color:var(--text);">Cash Drawer Reconciliation</div>
                </div>
                <div class="divide-y" style="background:var(--surface);">
                    @php
                        $reconRows = [
                            ['Opening balance',              $session->opening_balance,                  null],
                            ['+ Cash sales',                 $summary['total_sales_cash'] ?? 0,          'plus'],
                            ['− Cash refunds',               $summary['total_refunds_cash'] ?? 0,        'minus'],
                            ['− Cash expenses',              $summary['total_expenses_cash'] ?? 0,       'minus'],
                            ['− Owner cash withdrawals',     $summary['total_withdrawals_cash'] ?? 0,    'minus'],
                            ['− Bank deposits',              $summary['total_bank_deposits'] ?? 0,       'minus'],
                        ];
                    @endphp
                    @foreach ($reconRows as [$lbl, $val, $sign])
                        <div class="flex items-center justify-between px-4 py-2.5 gap-3" style="border-color:var(--border);">
                            <div class="text-sm" style="color:var(--text-dim);">{{ $lbl }}</div>
                            <div class="font-mono text-sm font-medium"
                                 style="color:{{ $sign === 'plus' ? 'var(--green)' : ($sign === 'minus' ? 'var(--red)' : 'var(--text)') }};">
                                {{ $sign === 'plus' ? '+' : ($sign === 'minus' ? '−' : '') }}{{ number_format($val) }} RWF
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-4 py-3 flex items-center justify-between" style="background:var(--accent-dim);border-top:2px solid var(--border);">
                    <div class="text-sm font-bold" style="color:var(--text);">Expected cash in drawer</div>
                    <div class="font-mono font-bold text-lg" style="color:var(--accent);">{{ number_format($summary['expected_cash'] ?? 0) }} RWF</div>
                </div>
            </div>

            {{-- Actual cash input --}}
            <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
                <label class="block text-sm font-semibold mb-3" style="color:var(--text);">Count cash in the drawer</label>
                <input type="number"
                       wire:model.blur="actualCashCounted"
                       min="0"
                       class="w-full px-4 py-3 rounded-xl text-xl font-mono text-center"
                       style="background:var(--surface);border:2px solid var(--border);color:var(--text);"
                       placeholder="0">
                @error('actualCashCounted') <div class="text-xs mt-2" style="color:var(--red);">{{ $message }}</div> @enderror

                @if ($actualCashCounted > 0)
                    <div class="mt-3 px-4 py-3 rounded-xl text-sm font-semibold text-center"
                         style="{{ $cashVariance > 0
                             ? 'background:var(--amber-dim);color:var(--amber);border:1px solid var(--amber);'
                             : ($cashVariance < 0
                                 ? 'background:var(--red-dim);color:var(--red);border:1px solid var(--red);'
                                 : 'background:var(--green-dim);color:var(--green);border:1px solid var(--green);') }}">
                        @if ($cashVariance === 0)
                            ✓ Balanced — drawer matches expected
                        @elseif ($cashVariance > 0)
                            Surplus +{{ number_format($cashVariance) }} RWF
                        @else
                            Shortage −{{ number_format(abs($cashVariance)) }} RWF
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Step 4: Close Day ── --}}
    @if ($currentStep === 4)
        <div class="space-y-4">

            {{-- Summary --}}
            <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-4 py-3" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                    <div class="text-sm font-semibold" style="color:var(--text);">Session Summary</div>
                </div>
                <div class="divide-y" style="background:var(--surface);">
                    @php
                        $summaryRows = [
                            ['Total sales',          $summary['total_sales'] ?? 0,        'var(--green)',   null],
                            ['Cash refunds',         $summary['total_refunds_cash'] ?? 0, 'var(--red)',     '−'],
                            ['Operational expenses', $summary['total_expenses'] ?? 0,     'var(--red)',     '−'],
                            ['Owner withdrawals',    $summary['total_withdrawals'] ?? 0,  'var(--amber)',   '−'],
                            ['Bank deposits',        $summary['total_bank_deposits'] ?? 0,'var(--accent)',  '−'],
                        ];
                    @endphp
                    @foreach ($summaryRows as [$lbl, $val, $clr, $sign])
                        <div class="flex items-center justify-between px-4 py-2.5 gap-3" style="border-color:var(--border);">
                            <div class="text-sm" style="color:var(--text-dim);">{{ $lbl }}</div>
                            <div class="font-mono text-sm font-medium" style="color:{{ $clr }};">
                                {{ $sign }}{{ number_format($val) }} RWF
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="divide-y" style="background:var(--surface-raised);border-top:2px solid var(--border);">
                    <div class="flex items-center justify-between px-4 py-2.5 gap-3" style="border-color:var(--border);">
                        <div class="text-sm" style="color:var(--text-dim);">Expected cash</div>
                        <div class="font-mono text-sm font-semibold" style="color:var(--accent);">{{ number_format($summary['expected_cash'] ?? 0) }} RWF</div>
                    </div>
                    <div class="flex items-center justify-between px-4 py-2.5 gap-3" style="border-color:var(--border);">
                        <div class="text-sm" style="color:var(--text-dim);">Actual cash counted</div>
                        <div class="font-mono text-sm font-semibold" style="color:var(--text);">{{ number_format($actualCashCounted) }} RWF</div>
                    </div>
                    <div class="flex items-center justify-between px-4 py-2.5 gap-3">
                        <div class="text-sm" style="color:var(--text-dim);">Variance</div>
                        <div class="font-mono text-sm font-bold"
                             style="color:{{ $cashVariance > 0 ? 'var(--amber)' : ($cashVariance < 0 ? 'var(--red)' : 'var(--text-dim)') }};">
                            {{ $cashVariance >= 0 ? '+' : '' }}{{ number_format($cashVariance) }} RWF
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cash disposition --}}
            <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="text-sm font-semibold mb-4" style="color:var(--text);">Cash Disposition</div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Send to owner via MoMo (RWF)</label>
                        <input type="number" wire:model.blur="cashToOwnerMomo" min="0"
                               class="w-full px-4 py-3 rounded-xl text-base font-mono"
                               style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                               placeholder="0">
                        @error('cashToOwnerMomo') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
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
                        <span class="font-mono font-bold text-base" style="color:var(--text);">{{ number_format($cashRetained) }} RWF</span>
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

            {{-- Non-cash channel settlement --}}
            @php
                $ncChannels = [
                    ['Mobile Money',  'momoSettled',         'momoSettledRef',         $summary['total_sales_momo']          ?? 0, 'var(--accent)'],
                    ['Card',          'cardSettled',          'cardSettledRef',          $summary['total_sales_card']          ?? 0, 'var(--text)'],
                    ['Bank Transfer', 'bankTransferSettled',  'bankTransferSettledRef',  $summary['total_sales_bank_transfer'] ?? 0, 'var(--text)'],
                    ['Other',         'otherSettled',         'otherSettledRef',         $summary['total_sales_other']         ?? 0, 'var(--text)'],
                ];
                $creditSales = $summary['total_sales_credit'] ?? 0;
                $hasNonCash  = collect($ncChannels)->contains(fn ($c) => $c[3] > 0) || $creditSales > 0;
            @endphp
            @if ($hasNonCash)
                <div class="rounded-xl overflow-hidden" style="border:1px solid var(--border);">
                    <div class="px-4 py-3" style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                        <div class="text-sm font-semibold" style="color:var(--text);">Non-Cash Revenue Settlement</div>
                        <div class="text-xs mt-0.5" style="color:var(--text-dim);">Record how each non-cash channel's revenue was settled / transferred to the owner</div>
                    </div>
                    <div class="divide-y" style="background:var(--surface);">
                        @foreach ($ncChannels as [$label, $field, $refField, $total, $color])
                            @if ($total > 0)
                                <div class="px-4 py-3" style="border-color:var(--border);">
                                    <div class="flex items-center justify-between mb-2.5">
                                        <div class="text-sm font-medium" style="color:var(--text);">{{ $label }}</div>
                                        <div class="text-xs font-mono px-2 py-0.5 rounded" style="background:var(--surface-raised);color:{{ $color }};">
                                            {{ number_format($total) }} RWF in sales
                                        </div>
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

                        {{-- Credit: read-only informational row --}}
                        @if ($creditSales > 0)
                            <div class="px-4 py-3" style="border-color:var(--border);background:var(--amber-dim);">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="text-sm font-medium" style="color:var(--amber);">Credit Sales</div>
                                    <div class="text-xs font-mono px-2 py-0.5 rounded" style="background:var(--surface-raised);color:var(--amber);">
                                        {{ number_format($creditSales) }} RWF
                                    </div>
                                </div>
                                <div class="text-xs" style="color:var(--text-dim);">
                                    Owed by customers — tracked via credit accounts. No settlement needed here.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Navigation ── --}}
    <div class="mt-5 flex items-center gap-3">
        @if ($currentStep > 1)
            <button wire:click="prevStep"
                    class="px-5 py-3 rounded-xl text-sm font-medium flex-shrink-0"
                    style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">
                ← Back
            </button>
        @endif

        <div class="flex-1">
            @if ($currentStep < 4)
                <button wire:click="nextStep"
                        wire:key="btn-next-step"
                        class="w-full px-6 py-3 rounded-xl text-sm font-semibold"
                        style="background:var(--accent);color:white;">
                    Continue →
                </button>
            @else
                <button wire:click="submitClose"
                        wire:key="btn-submit-close"
                        wire:loading.attr="disabled"
                        wire:confirm="Close the day and submit? You can still re-open it for corrections until the owner locks the session."
                        class="w-full px-6 py-3 rounded-xl text-sm font-semibold"
                        style="background:var(--amber);color:#1a1a1a;">
                    <span wire:loading.remove wire:target="submitClose">Close Register & Submit</span>
                    <span wire:loading wire:target="submitClose" style="display:none;">Closing…</span>
                </button>
                <div class="text-xs text-center mt-2" style="color:var(--text-faint);">Re-openable until owner locks the session</div>
            @endif
        </div>
    </div>
</div>
