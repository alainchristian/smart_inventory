<div>
    @if (session()->has('error'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    @if ($activities->isEmpty())
        <div class="text-center py-6 text-sm" style="color:var(--text-dim);">No activity recorded yet.</div>
    @else
        <div class="space-y-1">
            @foreach ($activities as $item)
                <div class="flex items-center gap-3 px-3 py-2 rounded-lg" style="background:var(--surface);">
                    {{-- Type badge --}}
                    <span class="px-2 py-0.5 rounded text-xs font-semibold flex-shrink-0"
                          style="
                            @if ($item['type'] === 'sale') background:var(--green-dim);color:var(--green);
                            @elseif ($item['type'] === 'return') background:var(--red-dim);color:var(--red);
                            @elseif ($item['type'] === 'expense') background:var(--amber-dim);color:var(--amber);
                            @elseif ($item['type'] === 'bank_deposit') background:var(--accent-dim);color:var(--accent);
                            @else background:var(--surface2);color:var(--text-dim);
                            @endif
                          ">
                        @if ($item['type'] === 'bank_deposit') Deposit
                        @elseif ($item['type'] === 'withdrawal') Withdrawal
                        @else {{ ucfirst($item['type']) }}
                        @endif
                    </span>

                    {{-- Time --}}
                    <span class="text-xs flex-shrink-0" style="color:var(--text-dim);">
                        {{ $item['time']?->format('H:i') }}
                    </span>

                    {{-- Label --}}
                    <span class="text-sm flex-1 truncate {{ $item['system'] ? 'italic' : '' }}" style="color:var(--text);">
                        @if ($item['system'])
                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--text-dim);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        @endif
                        {{ $item['label'] }}
                    </span>

                    {{-- Amount --}}
                    <span class="font-mono text-sm font-semibold flex-shrink-0"
                          style="{{ in_array($item['type'], ['sale']) ? 'color:var(--green)' : 'color:var(--red)' }}">
                        {{ in_array($item['type'], ['sale']) ? '+' : '-' }}{{ number_format($item['amount']) }}
                    </span>

                    {{-- Void button --}}
                    @if ($item['voidable'])
                        @php
                            $voidMethod = match($item['type']) {
                                'expense'      => 'voidExpense',
                                'bank_deposit' => 'voidDeposit',
                                default        => 'voidWithdrawal',
                            };
                        @endphp
                        <button wire:click="{{ $voidMethod }}({{ $item['id'] }})"
                                wire:confirm="Void this entry? This cannot be undone."
                                class="text-xs flex-shrink-0 px-2 py-0.5 rounded"
                                style="color:var(--red);border:1px solid var(--red-dim);">
                            Void
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
