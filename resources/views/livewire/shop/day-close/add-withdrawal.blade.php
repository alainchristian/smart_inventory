<div>
    @if (session()->has('success'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--green-dim);color:var(--green);">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    <div class="space-y-3">
        {{-- Two amount fields side by side --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Cash (RWF)</label>
                <input type="number"
                       wire:model.blur="cashAmount"
                       min="0"
                       class="w-full px-3 py-2 rounded-lg text-sm"
                       style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                       placeholder="0">
                @error('cashAmount') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Mobile Money (RWF)</label>
                <input type="number"
                       wire:model.blur="momoAmount"
                       min="0"
                       class="w-full px-3 py-2 rounded-lg text-sm"
                       style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                       placeholder="0">
                @error('momoAmount') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>
        </div>

        @if ($momoAmount > 0)
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">MoMo Reference (optional)</label>
                <input type="text"
                       wire:model="momoReference"
                       class="w-full px-3 py-2 rounded-lg text-sm"
                       style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                       placeholder="Transaction ID or phone number">
                @error('momoReference') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>
        @endif

        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Reason (required)</label>
            <textarea wire:model="reason"
                      rows="2"
                      class="w-full px-3 py-2 rounded-lg text-sm"
                      style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                      placeholder="e.g. school fees, personal use…"></textarea>
            @error('reason') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
        </div>

        @if ($cashAmount > 0 || $momoAmount > 0)
            <div class="flex justify-between text-xs px-1" style="color:var(--text-dim);">
                <span>Total</span>
                <span class="font-mono font-semibold" style="color:var(--text);">
                    {{ number_format($cashAmount + $momoAmount) }} RWF
                </span>
            </div>
        @endif

        <button wire:click="saveWithdrawal"
                wire:loading.attr="disabled"
                class="w-full px-4 py-2 rounded-lg text-sm font-semibold"
                style="background:var(--accent);color:white;">
            <span wire:loading.remove wire:target="saveWithdrawal">Record Withdrawal</span>
            <span wire:loading wire:target="saveWithdrawal" style="display:none;">Recording…</span>
        </button>
    </div>
</div>
