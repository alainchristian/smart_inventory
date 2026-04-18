<div>
    @if (session()->has('error'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    @if ($withdrawals->isEmpty())
        <div class="text-center py-4 text-sm" style="color:var(--text-dim);">No withdrawals recorded yet.</div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr style="border-bottom:1px solid var(--border);">
                    <th class="text-left pb-2 text-xs font-semibold" style="color:var(--text-dim);">Time</th>
                    <th class="text-left pb-2 text-xs font-semibold" style="color:var(--text-dim);">Reason</th>
                    <th class="text-right pb-2 text-xs font-semibold" style="color:var(--text-dim);">Amount</th>
                    <th class="pb-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($withdrawals as $withdrawal)
                    <tr style="border-bottom:1px solid var(--border);">
                        <td class="py-2 text-xs" style="color:var(--text-dim);">{{ $withdrawal->recorded_at->format('H:i') }}</td>
                        <td class="py-2" style="color:var(--text);">{{ $withdrawal->reason }}</td>
                        <td class="py-2 text-right font-mono" style="color:var(--accent);">{{ number_format($withdrawal->amount) }} RWF</td>
                        <td class="py-2 text-right">
                            <button wire:click="voidWithdrawal({{ $withdrawal->id }})"
                                    wire:confirm="Void this withdrawal? This cannot be undone."
                                    class="text-xs px-2 py-0.5 rounded"
                                    style="color:var(--red);border:1px solid var(--red-dim);">
                                Void
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
