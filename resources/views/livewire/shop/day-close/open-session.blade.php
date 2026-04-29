<div>
<style>
.dc-sess-body { padding:16px 20px; }
.dc-sess-head { padding:14px 18px; }
.dc-sess-form { padding:20px; }
@media (max-width:640px) {
    .dc-sess-body { padding:11px 14px; }
    .dc-sess-head { padding:9px 12px; }
    .dc-sess-form { padding:14px; }
}
</style>
    @if ($blockerSession)
        {{-- ── Informational: old session still open, but does NOT block today ── --}}
        <div style="margin-bottom:12px;border-radius:12px;border:1px solid var(--amber);background:var(--amber-dim);padding:12px 14px;
                    display:flex;align-items:flex-start;gap:10px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                 style="color:var(--amber);flex-shrink:0;margin-top:1px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div style="flex:1;min-width:0;">
                <span style="font-size:12px;font-weight:700;color:var(--amber);">Unclosed session from {{ $blockerSession->session_date->format('d M Y') }}</span>
                <span style="font-size:12px;color:var(--text-dim);"> — remember to close it when you get a chance.</span>
                <a href="{{ route('shop.day-close.close', ['session' => $blockerSession->id]) }}"
                   style="display:inline-block;margin-top:4px;font-size:12px;font-weight:600;color:var(--amber);text-decoration:underline;">
                    Close it now →
                </a>
            </div>
        </div>
    @endif

    @if ($todaySession)
        {{-- ── Session Active ── --}}
        <div style="border-radius:16px;overflow:hidden;border:1px solid var(--green);background:var(--green-dim);">
            <div class="dc-sess-body" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:12px;background:var(--green);
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:var(--green);">Session Active</div>
                        <div style="font-size:12px;color:var(--text-dim);margin-top:1px;">
                            Opened at {{ $todaySession->opened_at->format('H:i') }}
                            · <span style="font-family:var(--mono);font-weight:600;color:var(--text);">{{ number_format($todaySession->opening_balance) }} RWF</span> opening balance
                        </div>
                    </div>
                </div>
                <span style="font-size:11px;padding:4px 12px;border-radius:999px;font-weight:700;
                             background:var(--green);color:white;white-space:nowrap;">
                    {{ $todaySession->opened_at->diffForHumans(null, true) }} ago
                </span>
            </div>
        </div>
    @else
        {{-- ── No Session ── --}}
        @if ($errorMessage)
            <div style="margin-bottom:10px;padding:10px 14px;border-radius:10px;font-size:13px;
                        background:var(--red-dim);color:var(--red);border:1px solid var(--red);">
                {{ $errorMessage }}
            </div>
        @endif
        <div style="border-radius:16px;overflow:hidden;border:1px solid var(--border);">
            <div class="dc-sess-head" style="background:var(--surface2);border-bottom:1px solid var(--border);
                        display:flex;align-items:center;gap:8px;">
                <div style="width:8px;height:8px;border-radius:50%;background:var(--amber);"></div>
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-dim);">No Active Session</span>
            </div>
            <div class="dc-sess-form" style="background:var(--surface);">
                <p style="font-size:13px;color:var(--text-dim);margin:0 0 18px;">
                    Open today's session to start recording sales, expenses, and cash movements.
                </p>
                <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                    <div style="flex:1;min-width:160px;">
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                                      letter-spacing:0.5px;color:var(--text-dim);margin-bottom:6px;">
                            Opening Cash Balance (RWF)
                        </label>
                        <input type="number"
                               wire:model="openingBalance"
                               value="{{ $openingBalance ?: '' }}"
                               min="0"
                               style="width:100%;padding:10px 14px;border-radius:10px;font-size:18px;font-weight:700;
                                      font-family:var(--mono);text-align:right;background:var(--surface2);
                                      border:1.5px solid var(--border);color:var(--text);box-sizing:border-box;
                                      -moz-appearance:textfield;"
                               placeholder="0"
                               onfocus="this.style.borderColor='var(--accent)';"
                               onblur="this.style.borderColor='var(--border)';">
                        @error('openingBalance')
                            <div style="font-size:11px;margin-top:4px;color:var(--red);">{{ $message }}</div>
                        @enderror
                        @if ($openingBalanceHint)
                            <div style="display:flex;align-items:center;gap:8px;margin-top:6px;flex-wrap:wrap;">
                                <span style="font-size:11px;color:var(--text-dim);">{{ $openingBalanceHint }}</span>
                                @if ($suggestedBalance !== null && $openingBalance !== $suggestedBalance)
                                    <button type="button"
                                            wire:click="$set('openingBalance', {{ $suggestedBalance }})"
                                            style="font-size:11px;padding:2px 8px;border-radius:6px;border:1px solid var(--accent);
                                                   background:var(--accent-dim);color:var(--accent);cursor:pointer;">
                                        Use {{ number_format($suggestedBalance) }}
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                    <button wire:click="openDay"
                            wire:loading.attr="disabled"
                            style="padding:10px 22px;border-radius:10px;font-size:13px;font-weight:700;
                                   background:var(--accent);color:white;border:none;cursor:pointer;
                                   display:flex;align-items:center;gap:6px;white-space:nowrap;flex-shrink:0;">
                        <span wire:loading.remove wire:target="openDay">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                            Open Session
                        </span>
                        <span wire:loading wire:target="openDay" style="display:none;">Opening…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
