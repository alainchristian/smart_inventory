<div>
    @if (session()->has('success'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--green-dim);color:var(--green);">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    <div class="space-y-3">

        {{-- Source toggle --}}
        <div>
            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Deposit from</label>
            <div class="flex rounded-lg overflow-hidden" style="border:1px solid var(--border);">
                <button type="button"
                        wire:click="$set('source', 'cash')"
                        class="flex-1 px-3 py-2 text-xs font-semibold transition-colors"
                        style="{{ $source === 'cash'
                            ? 'background:var(--accent);color:#fff;'
                            : 'background:var(--surface);color:var(--text-dim);' }}">
                    Cash drawer
                </button>
                <button type="button"
                        wire:click="$set('source', 'mobile_money')"
                        class="flex-1 px-3 py-2 text-xs font-semibold transition-colors"
                        style="border-left:1px solid var(--border);{{ $source === 'mobile_money'
                            ? 'background:var(--accent);color:#fff;'
                            : 'background:var(--surface);color:var(--text-dim);' }}">
                    Mobile Money
                </button>
            </div>
        </div>

        {{-- Available balance indicator --}}
        @php
            $availableBalance = $source === 'cash'
                ? ($summary['expected_cash'] ?? 0)
                : ($summary['momo_available'] ?? 0);
            $balanceLabel = $source === 'cash' ? 'Available cash' : 'Available MoMo';
            $isOverdrawn  = $availableBalance <= 0;
        @endphp
        <div class="flex items-center justify-between px-3 py-2 rounded-lg text-xs"
             style="background:{{ $isOverdrawn ? 'var(--red-dim)' : 'var(--green-dim)' }};
                    color:{{ $isOverdrawn ? 'var(--red)' : 'var(--green)' }};
                    border:1px solid {{ $isOverdrawn ? 'var(--red)' : 'var(--green)' }};
                    opacity:0.85;">
            <span>{{ $balanceLabel }}</span>
            <span class="font-mono font-semibold">{{ number_format($availableBalance) }} RWF</span>
        </div>

        {{-- Amount --}}
        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Amount (RWF)</label>
            <input type="number"
                   wire:model.blur="amount"
                   min="1"
                   max="{{ max(0, $availableBalance) }}"
                   class="w-full px-3 py-2 rounded-lg text-sm"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                   placeholder="0">
            @error('amount') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            @if($amount > 0 && $amount > $availableBalance)
                <div class="text-xs mt-1" style="color:var(--red);">
                    Exceeds available {{ $source === 'cash' ? 'cash' : 'MoMo' }} balance of {{ number_format($availableBalance) }} RWF
                </div>
            @endif
        </div>

        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Bank Reference (optional)</label>
            <input type="text"
                   wire:model="bankReference"
                   class="w-full px-3 py-2 rounded-lg text-sm"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                   placeholder="e.g. slip number, teller ref…">
            @error('bankReference') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Notes (optional)</label>
            <textarea wire:model="notes"
                      rows="2"
                      class="w-full px-3 py-2 rounded-lg text-sm"
                      style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                      placeholder="Any additional notes…"></textarea>
            @error('notes') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
        </div>

        <button wire:click="saveDeposit"
                wire:loading.attr="disabled"
                @if($isOverdrawn || ($amount > 0 && $amount > $availableBalance)) disabled @endif
                class="w-full px-4 py-2 rounded-lg text-sm font-semibold transition-opacity"
                style="background:var(--accent);color:white;{{ ($isOverdrawn || ($amount > 0 && $amount > $availableBalance)) ? 'opacity:0.4;cursor:not-allowed;' : '' }}">
            <span wire:loading.remove wire:target="saveDeposit">Record Deposit</span>
            <span wire:loading wire:target="saveDeposit" style="display:none;">Recording…</span>
        </button>
    </div>

    @if ($deposits->count() > 0)
        <div class="mt-4">
            <div class="text-xs font-semibold mb-2" style="color:var(--text-dim);">Today's Deposits</div>
            <div class="space-y-2">
                @foreach ($deposits as $deposit)
                    <div class="flex items-center justify-between px-3 py-2 rounded-lg text-xs"
                         style="background:var(--surface2);border:1px solid var(--border);">
                        <div>
                            <span class="font-mono font-semibold">{{ number_format($deposit->amount) }} RWF</span>
                            <span class="ml-2 px-1.5 py-0.5 rounded text-xs font-medium"
                                  style="background:{{ ($deposit->source ?? 'cash') === 'mobile_money' ? 'var(--accent-dim)' : 'var(--green-dim)' }};
                                         color:{{ ($deposit->source ?? 'cash') === 'mobile_money' ? 'var(--accent)' : 'var(--green)' }};">
                                {{ ($deposit->source ?? 'cash') === 'mobile_money' ? 'MoMo' : 'Cash' }}
                            </span>
                            @if ($deposit->bank_reference)
                                <span class="ml-1" style="color:var(--text-dim);">Ref: {{ $deposit->bank_reference }}</span>
                            @endif
                            <div style="color:var(--text-dim);">{{ $deposit->deposited_at->format('H:i') }} · {{ $deposit->depositedBy?->name }}</div>
                        </div>
                        <button wire:click="voidDeposit({{ $deposit->id }})"
                                wire:confirm="Void this deposit of {{ number_format($deposit->amount) }} RWF?"
                                class="text-xs px-2 py-1 rounded"
                                style="color:var(--red);background:var(--red-dim);">
                            Void
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
