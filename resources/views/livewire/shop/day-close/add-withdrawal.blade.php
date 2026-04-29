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
                       inputmode="decimal"
                       enterkeyhint="next"
                       min="0"
                       class="w-full px-3 py-2 rounded-lg text-sm"
                       style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);text-align:right;"
                       placeholder="0"
                       onfocus="this.style.borderColor='var(--accent)';if(this.value==='0')this.value='';"
                       onblur="this.style.borderColor='var(--border)';if(this.value==='')this.value='';">
                <div style="font-size:10px;margin-top:3px;color:{{ $summary['expected_cash'] > 0 ? 'var(--green)' : 'var(--text-faint)' }};">
                    {{ number_format($summary['expected_cash']) }} RWF available
                </div>
                @error('cashAmount') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Mobile Money (RWF)</label>
                <input type="number"
                       wire:model="momoAmount"
                       inputmode="decimal"
                       enterkeyhint="next"
                       min="0"
                       class="w-full px-3 py-2 rounded-lg text-sm"
                       style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);text-align:right;"
                       placeholder="0"
                       onfocus="this.style.borderColor='var(--accent)';if(this.value==='0')this.value='';"
                       onblur="this.style.borderColor='var(--border)';if(this.value==='')this.value='';">
                <div style="font-size:10px;margin-top:3px;color:{{ $summary['momo_available'] > 0 ? '#0ea5e9' : 'var(--text-faint)' }};">
                    {{ number_format($summary['momo_available']) }} RWF available
                </div>
                @error('momoAmount') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>
        </div>

        @if (($momoAmount ?? 0) > 0)
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">MoMo Reference <span style="opacity:.5;font-weight:400;">(optional)</span></label>
                <input type="text"
                       wire:model="momoReference"
                       enterkeyhint="next"
                       class="w-full px-3 py-2 rounded-lg text-sm"
                       style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                       placeholder="Transaction ID or phone number">
                @error('momoReference') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>
        @endif

        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Reason</label>
            <input type="text"
                   wire:model="reason"
                   wire:keydown.enter="saveWithdrawal"
                   enterkeyhint="done"
                   class="w-full px-3 py-2 rounded-lg text-sm"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                   placeholder="e.g. school fees, personal use">
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

        {{-- Save button --}}
        <button wire:click="saveWithdrawal"
                wire:loading.attr="disabled"
                style="width:100%;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:700;
                       background:var(--amber);color:#1a1a1a;border:none;cursor:pointer;
                       display:flex;align-items:center;justify-content:center;gap:8px;
                       transition:opacity 0.15s;"
                onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span wire:loading.remove wire:target="saveWithdrawal">Record Withdrawal</span>
            <span wire:loading wire:target="saveWithdrawal" style="display:none;">Saving…</span>
        </button>
    </div>
</div>
