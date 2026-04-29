<div wire:poll.30s="refresh">
    @if (session()->has('error'))
        <div style="margin-bottom:8px;padding:8px 10px;border-radius:8px;font-size:11px;
                    background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    @if ($activities->isEmpty())
        <div style="text-align:center;padding:24px 0;font-size:12px;color:var(--text-faint);">
            No activity yet today
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:2px;">
            @foreach ($activities as $item)
                @php
                    $isIn  = in_array($item['type'], ['sale', 'repayment']);
                    $typeStyles = match($item['type']) {
                        'sale'         => ['bg' => 'var(--green-dim)',  'color' => 'var(--green)',  'label' => 'Sale'],
                        'repayment'    => ['bg' => 'var(--accent-dim)', 'color' => 'var(--accent)', 'label' => 'Repayment'],
                        'return'       => ['bg' => 'var(--red-dim)',    'color' => 'var(--red)',    'label' => 'Return'],
                        'expense'      => ['bg' => 'var(--amber-dim)',  'color' => 'var(--amber)',  'label' => 'Expense'],
                        'bank_deposit' => ['bg' => 'var(--accent-dim)', 'color' => 'var(--accent)', 'label' => 'Deposit'],
                        'withdrawal'   => ['bg' => 'var(--surface-raised)', 'color' => 'var(--text-dim)', 'label' => 'Withdrawal'],
                        default        => ['bg' => 'var(--surface-raised)', 'color' => 'var(--text-dim)', 'label' => ucfirst($item['type'])],
                    };
                @endphp
                <div style="display:flex;align-items:center;gap:8px;padding:7px 8px;border-radius:8px;
                            background:var(--surface-raised);">

                    {{-- Type pill --}}
                    <span style="padding:2px 7px;border-radius:5px;font-size:10px;font-weight:700;flex-shrink:0;
                                 background:{{ $typeStyles['bg'] }};color:{{ $typeStyles['color'] }};">
                        {{ $typeStyles['label'] }}
                    </span>

                    {{-- Time --}}
                    <span style="font-size:10px;font-family:var(--font-mono);color:var(--text-faint);flex-shrink:0;">
                        {{ $item['time']?->format('H:i') }}
                    </span>

                    {{-- Label --}}
                    <span style="font-size:12px;color:var(--text);flex:1;min-width:0;overflow:hidden;
                                 text-overflow:ellipsis;white-space:nowrap;{{ $item['system'] ? 'font-style:italic;color:var(--text-dim);' : '' }}">
                        @if ($item['system'])
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:3px;color:var(--text-dim);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        @endif
                        {{ $item['label'] }}
                    </span>

                    {{-- Amount --}}
                    <span style="font-size:12px;font-weight:700;font-family:var(--font-mono);flex-shrink:0;
                                 color:{{ $isIn ? 'var(--green)' : 'var(--red)' }};">
                        {{ $isIn ? '+' : '−' }}{{ number_format($item['amount']) }}
                    </span>

                    {{-- Void --}}
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
                                style="flex-shrink:0;padding:2px 7px;border-radius:5px;font-size:10px;font-weight:600;
                                       background:var(--red-dim);color:var(--red);border:1px solid var(--red-dim);cursor:pointer;">
                            ×
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
