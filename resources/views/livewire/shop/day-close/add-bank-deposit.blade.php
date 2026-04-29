<div>
    @if (session()->has('success'))
        <div style="margin-bottom:12px;padding:8px 12px;border-radius:8px;font-size:12px;
                    background:var(--green-dim);color:var(--green);border:1px solid var(--green);">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div style="margin-bottom:12px;padding:8px 12px;border-radius:8px;font-size:12px;
                    background:var(--red-dim);color:var(--red);border:1px solid var(--red);">
            {{ session('error') }}
        </div>
    @endif

    {{-- Amount --}}
    <div style="margin-bottom:10px;">
        <label style="display:block;font-size:11px;font-weight:600;color:var(--text-dim);margin-bottom:5px;">
            Amount (RWF)
        </label>
        <input type="number"
               wire:model="amount"
               inputmode="decimal"
               enterkeyhint="next"
               min="1"
               placeholder="0"
               style="width:100%;padding:12px 14px;border-radius:10px;
                      font-size:22px;font-weight:700;font-family:var(--font-mono);
                      text-align:right;background:var(--surface);
                      border:1.5px solid var(--border);color:var(--text);
                      transition:border-color 0.15s;box-sizing:border-box;
                      -moz-appearance:textfield;"
               onfocus="this.style.borderColor='var(--accent)';"
               onblur="this.style.borderColor='var(--border)';">
        @error('amount')
            <div style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</div>
        @enderror
    </div>

    {{-- Source toggle + reference --}}
    <div style="display:flex;gap:8px;align-items:flex-start;flex-wrap:wrap;">
        <div>
            <div style="display:flex;border-radius:8px;overflow:hidden;border:1px solid var(--border);">
                <button type="button"
                        wire:click="$set('source', 'cash')"
                        style="padding:9px 16px;font-size:12px;font-weight:600;border:none;cursor:pointer;font-family:var(--font);
                               {{ $source === 'cash' ? 'background:var(--accent);color:#fff;' : 'background:var(--surface);color:var(--text-dim);' }}">
                    Cash
                </button>
                <button type="button"
                        wire:click="$set('source', 'mobile_money')"
                        style="padding:9px 16px;font-size:12px;font-weight:600;border:none;border-left:1px solid var(--border);cursor:pointer;font-family:var(--font);
                               {{ $source === 'mobile_money' ? 'background:var(--accent);color:#fff;' : 'background:var(--surface);color:var(--text-dim);' }}">
                    MoMo
                </button>
            </div>
            @php
                $sourceAvail = $source === 'cash' ? $summary['expected_cash'] : $summary['momo_available'];
                $sourceColor = $source === 'cash' ? 'var(--green)' : '#0ea5e9';
            @endphp
            <div style="font-size:10px;margin-top:4px;color:{{ $sourceAvail > 0 ? $sourceColor : 'var(--red)' }};">
                {{ number_format($sourceAvail) }} RWF available
            </div>
        </div>
        <input type="text"
               wire:model="bankReference"
               wire:keydown.enter="saveDeposit"
               enterkeyhint="done"
               placeholder="Slip / ref number"
               style="flex:1;min-width:120px;padding:9px 12px;border-radius:8px;font-size:13px;
                      background:var(--surface);border:1px solid var(--border);
                      color:var(--text);box-sizing:border-box;">
    </div>

    {{-- Save button --}}
    <div style="margin-top:14px;">
        <button wire:click="saveDeposit"
                wire:loading.attr="disabled"
                style="width:100%;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:700;
                       background:var(--accent);color:white;border:none;cursor:pointer;
                       display:flex;align-items:center;justify-content:center;gap:8px;
                       transition:opacity 0.15s;"
                onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span wire:loading.remove wire:target="saveDeposit">Record Deposit</span>
            <span wire:loading wire:target="saveDeposit" style="display:none;">Saving…</span>
        </button>
    </div>

    {{-- Deposits list --}}
    @if ($deposits->count() > 0)
        <div style="border-top:1px solid var(--border);padding-top:14px;margin-top:16px;">
            <div style="font-size:11px;font-weight:700;color:var(--text-dim);
                        text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
                Recorded Today
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                @foreach ($deposits as $deposit)
                    <div style="display:flex;align-items:center;justify-content:space-between;
                                padding:8px 12px;border-radius:8px;
                                background:var(--surface);border:1px solid var(--border);">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700;
                                         background:{{ ($deposit->source ?? 'cash') === 'mobile_money' ? 'var(--accent-dim)' : 'var(--green-dim)' }};
                                         color:{{ ($deposit->source ?? 'cash') === 'mobile_money' ? 'var(--accent)' : 'var(--green)' }};">
                                {{ ($deposit->source ?? 'cash') === 'mobile_money' ? 'MoMo' : 'Cash' }}
                            </span>
                            <span style="font-family:var(--font-mono);font-size:13px;font-weight:700;color:var(--text);">
                                {{ number_format($deposit->amount) }} RWF
                            </span>
                            @if($deposit->bank_reference)
                                <span style="font-size:11px;color:var(--text-dim);">{{ $deposit->bank_reference }}</span>
                            @endif
                            <span style="font-size:11px;color:var(--text-faint);">
                                {{ $deposit->deposited_at->format('H:i') }}
                            </span>
                        </div>
                        <button wire:click="voidDeposit({{ $deposit->id }})"
                                wire:confirm="Void this deposit of {{ number_format($deposit->amount) }} RWF?"
                                style="padding:5px 12px;border-radius:6px;font-size:11px;font-weight:600;
                                       border:1px solid var(--red);color:var(--red);
                                       background:var(--red-dim);cursor:pointer;flex-shrink:0;margin-left:8px;">
                            Void
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
