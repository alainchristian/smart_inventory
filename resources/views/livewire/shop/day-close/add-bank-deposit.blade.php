<div>
    @if (session()->has('success'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--green-dim);color:var(--green);">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    <div class="space-y-3">
        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Amount (RWF)</label>
            <input type="number"
                   wire:model.blur="amount"
                   min="1"
                   class="w-full px-3 py-2 rounded-lg text-sm"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                   placeholder="0">
            @error('amount') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
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
                class="w-full px-4 py-2 rounded-lg text-sm font-semibold"
                style="background:var(--accent);color:white;">
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
                            @if ($deposit->bank_reference)
                                <span class="ml-2" style="color:var(--text-dim);">Ref: {{ $deposit->bank_reference }}</span>
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
