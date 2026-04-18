<div>
    @if (session()->has('success'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:var(--green-dim);color:var(--green);">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:var(--red-dim);color:var(--red);">
            {{ session('error') }}
        </div>
    @endif

    @if ($todaySession)
        {{-- Session already open --}}
        <div class="rounded-xl p-5 sm:p-6 flex items-center gap-4" style="background:var(--green-dim);border:1px solid var(--green);">
            <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0" style="background:var(--green);color:white;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <div class="font-semibold" style="color:var(--green);">Session Active</div>
                <div class="text-sm mt-0.5" style="color:var(--text-dim);">
                    Opened at {{ $todaySession->opened_at->format('H:i') }}
                    · Opening balance: <span class="font-mono font-medium">{{ number_format($todaySession->opening_balance) }} RWF</span>
                </div>
            </div>
        </div>
    @else
        {{-- No session yet --}}
        <div class="rounded-xl p-5 sm:p-6" style="background:var(--amber-dim);border:1px solid var(--amber);">
            <div class="flex items-start gap-3 mb-5">
                <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5" style="background:var(--amber);color:white;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-semibold" style="color:var(--amber);">No Active Session</div>
                    <div class="text-sm mt-0.5" style="color:var(--text-dim);">Open today's session to start recording expenses and close cash at end of day.</div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1.5" style="color:var(--text-dim);">Opening Cash Balance (RWF)</label>
                    <input type="number"
                           wire:model="openingBalance"
                           min="0"
                           class="w-full px-4 py-3 rounded-lg text-base"
                           style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                           placeholder="0">
                    @error('openingBalance')
                        <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div>
                    @enderror
                </div>
                <button wire:click="openDay"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-6 py-3 rounded-lg text-sm font-semibold flex-shrink-0"
                        style="background:var(--accent);color:white;">
                    <span wire:loading.remove wire:target="openDay">Open Today's Session</span>
                    <span wire:loading wire:target="openDay" style="display:none;">Opening…</span>
                </button>
            </div>
        </div>
    @endif
</div>
