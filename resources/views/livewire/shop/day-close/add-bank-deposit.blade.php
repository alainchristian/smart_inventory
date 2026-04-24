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

    {{-- Add deposit form --}}
    <div style="margin-bottom:16px;">

        {{-- Large amount input --}}
        <div style="margin-bottom:10px;">
            <input type="number"
                   wire:model="amount"
                   wire:keydown.enter="saveDeposit"
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

        {{-- Source toggle + reference in one row --}}
        <div style="display:flex;gap:8px;align-items:center;">
            <div style="display:flex;border-radius:8px;overflow:hidden;border:1px solid var(--border);flex-shrink:0;">
                <button type="button"
                        wire:click="$set('source', 'cash')"
                        style="padding:7px 12px;font-size:11px;font-weight:600;border:none;cursor:pointer;font-family:var(--font);
                               {{ $source === 'cash' ? 'background:var(--accent);color:#fff;' : 'background:var(--surface);color:var(--text-dim);' }}">
                    Cash
                </button>
                <button type="button"
                        wire:click="$set('source', 'mobile_money')"
                        style="padding:7px 12px;font-size:11px;font-weight:600;border:none;border-left:1px solid var(--border);cursor:pointer;font-family:var(--font);
                               {{ $source === 'mobile_money' ? 'background:var(--accent);color:#fff;' : 'background:var(--surface);color:var(--text-dim);' }}">
                    MoMo
                </button>
            </div>
            <input type="text"
                   wire:model="bankReference"
                   wire:keydown.enter="saveDeposit"
                   placeholder="Slip / ref — press ↵ to save"
                   style="flex:1;padding:7px 12px;border-radius:8px;font-size:12px;
                          background:var(--surface);border:1px solid var(--border);
                          color:var(--text);box-sizing:border-box;">
        </div>

    </div>

    {{-- Deposits list --}}
    @if ($deposits->count() > 0)
        <div style="border-top:1px solid var(--border);padding-top:14px;">
            <div style="font-size:11px;font-weight:700;color:var(--text-dim);
                        text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
                Recorded Today
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                @foreach ($deposits as $deposit)
                    <div style="display:flex;align-items:center;justify-content:space-between;
                                padding:8px 12px;border-radius:8px;
                                background:var(--surface);border:1px solid var(--border);">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700;
                                         background:{{ ($deposit->source ?? 'cash') === 'mobile_money' ? 'var(--accent-dim)' : 'var(--green-dim)' }};
                                         color:{{ ($deposit->source ?? 'cash') === 'mobile_money' ? 'var(--accent)' : 'var(--green)' }};">
                                {{ ($deposit->source ?? 'cash') === 'mobile_money' ? 'MoMo' : 'Cash' }}
                            </span>
                            <span style="font-family:var(--font-mono);font-size:13px;font-weight:700;color:var(--text);">
                                {{ number_format($deposit->amount) }} RWF
                            </span>
                            @if($deposit->bank_reference)
                                <span style="font-size:11px;color:var(--text-dim);">
                                    Ref: {{ $deposit->bank_reference }}
                                </span>
                            @endif
                            <span style="font-size:11px;color:var(--text-faint);">
                                {{ $deposit->deposited_at->format('H:i') }}
                            </span>
                        </div>
                        <button wire:click="voidDeposit({{ $deposit->id }})"
                                wire:confirm="Void this deposit of {{ number_format($deposit->amount) }} RWF?"
                                style="padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;
                                       border:1px solid var(--red);color:var(--red);
                                       background:var(--red-dim);cursor:pointer;">
                            Void
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
