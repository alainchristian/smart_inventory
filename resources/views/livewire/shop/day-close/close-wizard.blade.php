<div>
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    {{-- Step Indicator --}}
    <div class="flex items-center mb-8 sm:mb-10">
        @foreach ([1 => 'Sales', 2 => 'Movements', 3 => 'Cash Count', 4 => 'Close Register'] as $step => $label)
            <div class="flex items-center {{ $step < 4 ? 'flex-1' : '' }}">
                <div class="flex flex-col sm:flex-row items-center gap-1 sm:gap-2">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 transition-all"
                         style="{{ $currentStep === $step
                             ? 'background:var(--accent);color:white;box-shadow:0 0 0 4px var(--accent-dim);'
                             : ($currentStep > $step
                                 ? 'background:var(--green);color:white;'
                                 : 'background:var(--surface-raised);color:var(--text-dim);border:2px solid var(--border);') }}">
                        @if ($currentStep > $step)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            {{ $step }}
                        @endif
                    </div>
                    <span class="text-xs font-medium text-center hidden sm:block"
                          style="{{ $currentStep === $step ? 'color:var(--accent);' : 'color:var(--text-dim);' }}">
                        {{ $label }}
                    </span>
                    @if ($currentStep === $step)
                        <span class="text-xs font-medium sm:hidden" style="color:var(--accent);">{{ $label }}</span>
                    @endif
                </div>
                @if ($step < 4)
                    <div class="flex-1 h-px mx-2 sm:mx-3 transition-colors"
                         style="background:{{ $currentStep > $step ? 'var(--green)' : 'var(--border)' }};"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ── Step 1: Sales Review ── --}}
    @if ($currentStep === 1)
        <div class="rounded-xl p-5 sm:p-6" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-base font-semibold mb-1" style="color:var(--text);">Today's Sales</div>
            <div class="text-sm mb-5" style="color:var(--text-dim);">Figures are live from today's recorded transactions.</div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[300px]">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border);">
                            <th class="text-left pb-3 text-xs font-semibold" style="color:var(--text-dim);">Payment Method</th>
                            <th class="text-right pb-3 text-xs font-semibold" style="color:var(--text-dim);">Amount (RWF)</th>
                            <th class="text-right pb-3 text-xs font-semibold" style="color:var(--text-dim);">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = $summary['total_sales'] ?: 1;
                            $rows = [
                                'Cash'         => $summary['total_sales_cash'],
                                'Mobile Money' => $summary['total_sales_momo'],
                                'Card'         => $summary['total_sales_card'],
                                'Other'        => $summary['total_sales_other'],
                            ];
                        @endphp
                        @foreach ($rows as $method => $amount)
                            <tr style="border-bottom:1px solid var(--border);">
                                <td class="py-3" style="color:var(--text);">{{ $method }}</td>
                                <td class="py-3 text-right font-mono" style="color:var(--text);">{{ number_format($amount) }}</td>
                                <td class="py-3 text-right">
                                    <span class="text-xs px-2 py-0.5 rounded" style="background:var(--surface);color:var(--text-dim);">
                                        {{ $summary['total_sales'] > 0 ? round($amount / $total * 100, 1) : 0 }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid var(--border);">
                            <td class="pt-3 font-bold" style="color:var(--text);">Total Sales</td>
                            <td class="pt-3 text-right font-mono font-bold text-base" style="color:var(--accent);">{{ number_format($summary['total_sales']) }}</td>
                            <td class="pt-3 text-right text-xs" style="color:var(--text-dim);">{{ $summary['transaction_count'] }} tx</td>
                        </tr>
                        @if (($summary['total_refunds_cash'] ?? 0) > 0)
                            <tr>
                                <td class="pt-1.5 text-xs" style="color:var(--text-dim);">Cash refunds deducted</td>
                                <td class="pt-1.5 text-right font-mono text-xs" style="color:var(--red);">−{{ number_format($summary['total_refunds_cash']) }}</td>
                                <td></td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    {{-- ── Step 2: All Money Movements ── --}}
    @if ($currentStep === 2)
        <div class="space-y-4">
            {{-- Section A: Bank Deposits --}}
            <div class="rounded-xl p-5 sm:p-6" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-base font-semibold" style="color:var(--text);">Bank Deposits</div>
                    <span class="text-xs px-2 py-0.5 rounded font-medium" style="background:var(--accent-dim);color:var(--accent);">Reduces cash drawer</span>
                </div>
                <div class="text-xs mb-4" style="color:var(--text-dim);">Cash physically deposited to the bank during the day.</div>
                <livewire:shop.day-close.add-bank-deposit :dailySessionId="$dailySessionId" />
                <div class="mt-4 flex justify-between items-center text-sm pt-3" style="border-top:1px solid var(--border);">
                    <span style="color:var(--text-dim);">Total banked today</span>
                    <div class="text-right">
                        <div class="font-mono font-bold" style="color:var(--accent);">{{ number_format($summary['total_bank_deposits'] ?? 0) }} RWF</div>
                        <div class="text-xs" style="color:var(--text-dim);">{{ $summary['bank_deposit_count'] ?? 0 }} deposit(s)</div>
                    </div>
                </div>
            </div>

            {{-- Section B: Operational Expenses --}}
            <div class="rounded-xl p-5 sm:p-6" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="text-base font-semibold mb-1" style="color:var(--text);">Operational Expenses</div>
                <div class="text-xs mb-4" style="color:var(--text-dim);">You can still add or void expenses before closing.</div>
                <livewire:shop.day-close.expense-list :dailySessionId="$dailySessionId" />
                <div class="mt-4 pt-4" style="border-top:1px solid var(--border);">
                    <livewire:shop.day-close.add-expense :dailySessionId="$dailySessionId" />
                </div>
                <div class="mt-4 flex justify-between items-start text-sm pt-3" style="border-top:1px solid var(--border);">
                    <span style="color:var(--text-dim);">Total expenses</span>
                    <div class="text-right">
                        <div class="font-mono font-bold" style="color:var(--red);">{{ number_format($summary['total_expenses'] ?? 0) }} RWF</div>
                        <div class="text-xs mt-0.5" style="color:var(--text-dim);">cash: {{ number_format($summary['total_expenses_cash'] ?? 0) }} RWF | MoMo: {{ number_format($summary['total_expenses_momo'] ?? 0) }} RWF</div>
                    </div>
                </div>
            </div>

            {{-- Section C: Owner Withdrawals --}}
            <div class="rounded-xl p-5 sm:p-6" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="text-base font-semibold mb-3" style="color:var(--text);">Owner Withdrawals</div>
                <livewire:shop.day-close.withdrawal-list :dailySessionId="$dailySessionId" />
                <div class="mt-4 pt-4" style="border-top:1px solid var(--border);">
                    <livewire:shop.day-close.add-withdrawal :dailySessionId="$dailySessionId" />
                </div>
                <div class="mt-4 flex justify-between items-start text-sm pt-3" style="border-top:1px solid var(--border);">
                    <span style="color:var(--text-dim);">Total withdrawn</span>
                    <div class="text-right">
                        <div class="font-mono font-bold" style="color:var(--accent);">{{ number_format($summary['total_withdrawals'] ?? 0) }} RWF</div>
                        <div class="text-xs mt-0.5" style="color:var(--text-dim);">cash: {{ number_format($summary['total_withdrawals_cash'] ?? 0) }} RWF | MoMo: {{ number_format($summary['total_withdrawals_momo'] ?? 0) }} RWF</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 px-3 py-2.5 rounded-lg text-xs" style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">
            Only cash movements affect the expected cash count.
            MoMo expenses, MoMo withdrawals, and card/MoMo sales do not change what is physically in the drawer.
        </div>
    @endif

    {{-- ── Step 3: Cash Count ── --}}
    @if ($currentStep === 3)
        <div class="rounded-xl p-5 sm:p-6" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-base font-semibold mb-5" style="color:var(--text);">Cash Count</div>

            {{-- Reconciliation card --}}
            <div class="mb-6 rounded-xl p-4 sm:p-5" style="background:var(--surface);border:1px solid var(--border);">
                <div class="text-xs font-semibold uppercase tracking-wide mb-4" style="color:var(--text-dim);">Reconciliation</div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">Opening balance</span>
                        <span class="font-mono" style="color:var(--text);">{{ number_format($session->opening_balance) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">+ Cash sales <span class="text-xs">({{ $summary['transaction_count'] ?? 0 }} tx)</span></span>
                        <span class="font-mono font-medium" style="color:var(--green);">+ {{ number_format($summary['total_sales_cash'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">− Cash refunds</span>
                        <span class="font-mono font-medium" style="color:var(--red);">− {{ number_format($summary['total_refunds_cash'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">− Cash expenses <span class="text-xs">({{ $summary['expense_count'] ?? 0 }})</span></span>
                        <span class="font-mono font-medium" style="color:var(--red);">− {{ number_format($summary['total_expenses_cash'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">− Owner cash withdrawals <span class="text-xs">({{ $summary['withdrawal_count'] ?? 0 }})</span></span>
                        <span class="font-mono font-medium" style="color:var(--accent);">− {{ number_format($summary['total_withdrawals_cash'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">− Bank deposits <span class="text-xs">({{ $summary['bank_deposit_count'] ?? 0 }})</span></span>
                        <span class="font-mono font-medium" style="color:var(--accent);">− {{ number_format($summary['total_bank_deposits'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center pt-3" style="border-top:2px solid var(--border);">
                        <span class="font-bold" style="color:var(--text);">Expected cash in drawer</span>
                        <span class="font-mono font-bold text-lg" style="color:var(--accent);">{{ number_format($summary['expected_cash'] ?? 0) }} RWF</span>
                    </div>
                </div>
            </div>

            {{-- Actual cash input --}}
            <div class="mb-5">
                <label class="block text-sm font-medium mb-2" style="color:var(--text);">Actual cash counted in drawer (RWF)</label>
                <input type="number"
                       wire:model.blur="actualCashCounted"
                       min="0"
                       class="w-full px-4 py-3 rounded-lg text-lg"
                       style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                       placeholder="0">
                @error('actualCashCounted') <div class="text-xs mt-1.5" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>

            @if ($actualCashCounted > 0)
                <div class="px-4 py-4 rounded-xl text-sm font-semibold"
                     style="{{ $cashVariance > 0
                         ? 'background:var(--amber-dim);color:var(--amber);border:1px solid var(--amber);'
                         : ($cashVariance < 0
                             ? 'background:var(--red-dim);color:var(--red);border:1px solid var(--red);'
                             : 'background:var(--green-dim);color:var(--green);border:1px solid var(--green);') }}">
                    @if ($cashVariance === 0)
                        ✓ Balanced — cash in drawer matches expected
                    @elseif ($cashVariance > 0)
                        Surplus: +{{ number_format($cashVariance) }} RWF — extra cash found; will be retained
                    @else
                        ⚠ Shortage: −{{ number_format(abs($cashVariance)) }} RWF — shortfall will be recorded as a loss
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- ── Step 4: Close Day ── --}}
    @if ($currentStep === 4)
        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Summary panel --}}
            <div class="rounded-xl p-5 sm:p-6" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="text-base font-semibold mb-4" style="color:var(--text);">Summary</div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">Today's sales</span>
                        <span class="font-mono font-medium" style="color:var(--green);">{{ number_format($summary['total_sales'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">Cash refunds</span>
                        <span class="font-mono" style="color:var(--red);">−{{ number_format($summary['total_refunds_cash'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">Operational expenses</span>
                        <span class="font-mono" style="color:var(--red);">−{{ number_format($summary['total_expenses'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">Owner withdrawals</span>
                        <span class="font-mono" style="color:var(--accent);">−{{ number_format($summary['total_withdrawals'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">Bank deposits</span>
                        <span class="font-mono" style="color:var(--accent);">−{{ number_format($summary['total_bank_deposits'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center pt-3" style="border-top:1px solid var(--border);">
                        <span style="color:var(--text-dim);">Expected cash</span>
                        <span class="font-mono font-semibold" style="color:var(--accent);">{{ number_format($summary['expected_cash'] ?? 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">Actual cash counted</span>
                        <span class="font-mono font-semibold" style="color:var(--text);">{{ number_format($actualCashCounted) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color:var(--text-dim);">Variance</span>
                        <span class="font-mono font-semibold"
                              style="{{ $cashVariance > 0 ? 'color:var(--amber)' : ($cashVariance < 0 ? 'color:var(--red)' : 'color:var(--text-dim)') }}">
                            {{ $cashVariance >= 0 ? '+' : '' }}{{ number_format($cashVariance) }} RWF
                        </span>
                    </div>
                </div>
            </div>

            {{-- Disposition + submit panel --}}
            <div class="rounded-xl p-5 sm:p-6" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="text-base font-semibold mb-4" style="color:var(--text);">Cash Disposition</div>
                <div class="text-xs mb-4 px-3 py-2 rounded-lg" style="background:var(--accent-dim);color:var(--accent);">
                    Optionally send cash to the owner via Mobile Money, then retain the rest in the shop.
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:var(--text-dim);">Send to owner via MoMo (RWF)</label>
                        <input type="number" wire:model.blur="cashToOwnerMomo" min="0"
                               class="w-full px-4 py-3 rounded-lg text-base"
                               style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                               placeholder="0 — leave blank to retain all">
                        @error('cashToOwnerMomo') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
                    </div>

                    @if ($cashToOwnerMomo > 0)
                        <div>
                            <label class="block text-sm font-medium mb-1.5" style="color:var(--text-dim);">MoMo reference (optional)</label>
                            <input type="text" wire:model="ownerMomoReference"
                                   class="w-full px-4 py-3 rounded-lg text-sm"
                                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                                   placeholder="Transaction ID or confirmation code">
                        </div>
                    @endif

                    <div class="rounded-lg px-4 py-3 flex justify-between items-center" style="background:var(--surface);border:1px solid var(--border);">
                        <span class="text-sm" style="color:var(--text-dim);">Retained in shop</span>
                        <span class="font-mono font-bold text-base" style="color:var(--text);">{{ number_format($cashRetained) }} RWF</span>
                    </div>

                    <div class="flex justify-between text-xs font-medium px-1 pt-1" style="border-top:1px solid var(--border);">
                        <span style="color:var(--text-dim);">Allocated:</span>
                        <span class="font-mono" style="color:var(--green);">
                            {{ number_format($cashToOwnerMomo + $cashRetained) }} / {{ number_format($actualCashCounted) }} RWF
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:var(--text-dim);">Notes (optional)</label>
                        <textarea wire:model="notes" rows="3"
                                  class="w-full px-4 py-3 rounded-lg text-sm"
                                  style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                                  placeholder="Any notes for the owner…"></textarea>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Navigation ── --}}
    <div class="mt-6 sm:mt-8 flex justify-between items-center gap-4">
        <div>
            @if ($currentStep > 1)
                <button wire:click="prevStep"
                        class="px-5 py-2.5 rounded-lg text-sm font-medium"
                        style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">
                    ← Back
                </button>
            @endif
        </div>

        <div class="text-right">
            @if ($currentStep < 4)
                <button wire:click="nextStep"
                        wire:key="btn-next-step"
                        class="px-6 py-2.5 rounded-lg text-sm font-semibold"
                        style="background:var(--accent);color:white;">
                    Continue →
                </button>
            @else
                <button wire:click="submitClose"
                        wire:key="btn-submit-close"
                        wire:loading.attr="disabled"
                        wire:confirm="Close the day and submit? You can still re-open it for corrections until the owner locks the session."
                        class="px-6 py-2.5 rounded-lg text-sm font-semibold"
                        style="background:var(--amber);color:#1a1a1a;">
                    <span wire:loading.remove wire:target="submitClose">Close Register & Submit</span>
                    <span wire:loading wire:target="submitClose" style="display:none;">Closing…</span>
                </button>
                <div class="text-xs mt-1.5" style="color:var(--text-dim);">You can re-open for corrections until the owner locks the session.</div>
            @endif
        </div>
    </div>
</div>
