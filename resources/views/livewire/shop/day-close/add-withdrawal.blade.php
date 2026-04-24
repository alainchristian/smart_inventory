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
                       wire:model="cashAmount"
                       wire:keydown.enter="saveWithdrawal"
                       min="0"
                       class="w-full px-3 py-2 rounded-lg text-sm"
                       style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);text-align:right;"
                       placeholder="0"
                       onfocus="this.style.borderColor='var(--accent)';if(this.value==='0')this.value='';"
                       onblur="this.style.borderColor='var(--border)';if(this.value==='')this.value='';">
                @error('cashAmount') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Mobile Money (RWF)</label>
                <input type="number"
                       wire:model="momoAmount"
                       wire:keydown.enter="saveWithdrawal"
                       min="0"
                       class="w-full px-3 py-2 rounded-lg text-sm"
                       style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);text-align:right;"
                       placeholder="0"
                       onfocus="this.style.borderColor='var(--accent)';if(this.value==='0')this.value='';"
                       onblur="this.style.borderColor='var(--border)';if(this.value==='')this.value='';">
                @error('momoAmount') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>
        </div>

        @if (($momoAmount ?? 0) > 0)
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">MoMo Reference (optional)</label>
                <input type="text"
                       wire:model="momoReference"
                       wire:keydown.enter="saveWithdrawal"
                       class="w-full px-3 py-2 rounded-lg text-sm"
                       style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                       placeholder="Transaction ID or phone number">
                @error('momoReference') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>
        @endif

        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Reason (required)</label>
            <input type="text"
                   wire:model="reason"
                   wire:keydown.enter="saveWithdrawal"
                   class="w-full px-3 py-2 rounded-lg text-sm"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                   placeholder="e.g. school fees, personal use — press ↵ to save">
            @error('reason') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
        </div>

        @if ((int) $cashAmount > 0 || (int) $momoAmount > 0)
            <div class="flex justify-between text-xs px-1" style="color:var(--text-dim);">
                <span>Total</span>
                <span class="font-mono font-semibold" style="color:var(--text);">
                    {{ number_format((int) $cashAmount + (int) $momoAmount) }} RWF
                </span>
            </div>
        @endif
    </div>
</div>
