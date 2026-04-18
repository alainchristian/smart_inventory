<div>
    @if (session()->has('error'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    @if ($expenses->isEmpty())
        <div class="text-center py-8 text-sm" style="color:var(--text-dim);">No expenses recorded yet.</div>
    @else
        {{-- Mobile: card layout --}}
        <div class="space-y-2 sm:hidden">
            @foreach ($expenses as $expense)
                <div class="rounded-lg p-3" style="background:var(--surface);border:1px solid var(--border);">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs px-2 py-0.5 rounded" style="background:var(--accent-dim);color:var(--accent);">
                                    {{ $expense->category->name ?? '—' }}
                                </span>
                                <span class="text-xs" style="color:var(--text-dim);">{{ $expense->recorded_at->format('H:i') }}</span>
                            </div>
                            <div class="text-sm mt-1" style="color:var(--text);">{{ $expense->description }}</div>
                            <div class="text-xs mt-0.5" style="color:var(--text-dim);">{{ str_replace('_', ' ', ucfirst($expense->payment_method)) }}</div>
                        </div>
                        <div class="flex flex-col items-end gap-2 flex-shrink-0">
                            <span class="font-mono font-semibold text-sm" style="color:var(--text);">{{ number_format($expense->amount) }}</span>
                            @if (! ($expense->is_system_generated ?? false))
                                <button wire:click="voidExpense({{ $expense->id }})"
                                        wire:confirm="Void this expense?"
                                        class="text-xs px-2 py-1 rounded"
                                        style="color:var(--red);background:var(--red-dim);">
                                    Void
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="flex justify-between text-sm font-semibold pt-1 px-1">
                <span style="color:var(--text-dim);">Total</span>
                <span class="font-mono" style="color:var(--text);">{{ number_format($expenses->sum('amount')) }} RWF</span>
            </div>
        </div>

        {{-- Desktop: table layout --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--border);">
                        <th class="text-left pb-2 px-2 text-xs font-semibold" style="color:var(--text-dim);">Category</th>
                        <th class="text-left pb-2 px-2 text-xs font-semibold" style="color:var(--text-dim);">Description</th>
                        <th class="text-left pb-2 px-2 text-xs font-semibold" style="color:var(--text-dim);">Method</th>
                        <th class="text-right pb-2 px-2 text-xs font-semibold" style="color:var(--text-dim);">Amount</th>
                        <th class="text-right pb-2 px-2 text-xs font-semibold" style="color:var(--text-dim);">Time</th>
                        <th class="pb-2 px-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenses as $expense)
                        <tr style="border-bottom:1px solid var(--border);">
                            <td class="py-2.5 px-2">
                                <span class="text-xs px-2 py-0.5 rounded" style="background:var(--accent-dim);color:var(--accent);">
                                    {{ $expense->category->name ?? '—' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-2 text-xs" style="color:var(--text);">{{ $expense->description }}</td>
                            <td class="py-2.5 px-2">
                                <span class="text-xs" style="color:var(--text-dim);">
                                    {{ str_replace('_', ' ', ucfirst($expense->payment_method)) }}
                                </span>
                            </td>
                            <td class="py-2.5 px-2 text-right font-mono text-xs font-semibold" style="color:var(--text);">
                                {{ number_format($expense->amount) }} RWF
                            </td>
                            <td class="py-2.5 px-2 text-right text-xs" style="color:var(--text-dim);">
                                {{ $expense->recorded_at->format('H:i') }}
                            </td>
                            <td class="py-2.5 px-2 text-right">
                                @if (! ($expense->is_system_generated ?? false))
                                    <button wire:click="voidExpense({{ $expense->id }})"
                                            wire:confirm="Void this expense?"
                                            class="text-xs px-2 py-1 rounded"
                                            style="color:var(--red);background:var(--red-dim);">
                                        Void
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid var(--border);">
                        <td colspan="3" class="pt-2.5 px-2 text-xs font-semibold" style="color:var(--text-dim);">Total</td>
                        <td class="pt-2.5 px-2 text-right font-mono text-sm font-bold" style="color:var(--text);">
                            {{ number_format($expenses->sum('amount')) }} RWF
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
