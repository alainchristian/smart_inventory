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
                @if ($editingId === $expense->id)
                    {{-- Inline edit form (mobile) --}}
                    <div class="rounded-lg p-3" style="background:var(--surface2);border:1px solid var(--accent);">
                        <div class="space-y-2 mb-2">
                            <select wire:model="editCategoryId" class="w-full text-xs rounded px-2 py-1.5"
                                    style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                                <option value="0">— Category —</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <input wire:model="editDescription" type="text" placeholder="Description"
                                   class="w-full text-xs rounded px-2 py-1.5"
                                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);" />
                            <input wire:model="editAmount" type="number" placeholder="Amount (RWF)" min="1"
                                   class="w-full text-xs rounded px-2 py-1.5"
                                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);" />
                            <select wire:model="editPaymentMethod" class="w-full text-xs rounded px-2 py-1.5"
                                    style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="saveExpense"
                                    class="flex-1 text-xs py-1 rounded font-semibold"
                                    style="background:var(--accent);color:#fff;">Save</button>
                            <button wire:click="cancelEdit"
                                    class="text-xs px-3 py-1 rounded"
                                    style="background:var(--surface);border:1px solid var(--border);color:var(--text-dim);">Cancel</button>
                        </div>
                    </div>
                @else
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
                                @if (! ($expense->is_system_generated ?? false) && $session?->isEditable())
                                    <div class="flex gap-1">
                                        <button wire:click="editExpense({{ $expense->id }})"
                                                class="text-xs px-2 py-1 rounded"
                                                style="color:var(--accent);background:var(--accent-dim);">
                                            Edit
                                        </button>
                                        <button wire:click="voidExpense({{ $expense->id }})"
                                                wire:confirm="Void this expense?"
                                                class="text-xs px-2 py-1 rounded"
                                                style="color:var(--red);background:var(--red-dim);">
                                            Void
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
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
                        @if ($editingId === $expense->id)
                            <tr style="border-bottom:1px solid var(--border);background:var(--surface2);">
                                <td class="py-2 px-2" colspan="5">
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                                        <select wire:model="editCategoryId"
                                                style="font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text);">
                                            <option value="0">— Category —</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                        <input wire:model="editDescription" type="text" placeholder="Description"
                                               style="font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text);flex:1;min-width:120px;" />
                                        <input wire:model="editAmount" type="number" placeholder="Amount" min="1"
                                               style="font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text);width:110px;" />
                                        <select wire:model="editPaymentMethod"
                                                style="font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text);">
                                            <option value="cash">Cash</option>
                                            <option value="mobile_money">Mobile Money</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    @error('editAmount') <div style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</div> @enderror
                                    @error('editDescription') <div style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</div> @enderror
                                    @error('editCategoryId') <div style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</div> @enderror
                                </td>
                                <td class="py-2 px-2 text-right" style="white-space:nowrap;">
                                    <button wire:click="saveExpense"
                                            style="font-size:12px;padding:4px 10px;border-radius:6px;border:none;cursor:pointer;background:var(--accent);color:#fff;font-weight:700;margin-right:4px;">
                                        Save
                                    </button>
                                    <button wire:click="cancelEdit"
                                            style="font-size:12px;padding:4px 10px;border-radius:6px;cursor:pointer;border:1px solid var(--border);background:var(--surface);color:var(--text-dim);">
                                        Cancel
                                    </button>
                                </td>
                            </tr>
                        @else
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
                                <td class="py-2.5 px-2 text-right" style="white-space:nowrap;">
                                    @if (! ($expense->is_system_generated ?? false) && $session?->isEditable())
                                        <button wire:click="editExpense({{ $expense->id }})"
                                                style="font-size:11px;padding:3px 8px;border-radius:5px;cursor:pointer;
                                                       color:var(--accent);background:var(--accent-dim);border:none;margin-right:4px;">
                                            Edit
                                        </button>
                                        <button wire:click="voidExpense({{ $expense->id }})"
                                                wire:confirm="Void this expense?"
                                                style="font-size:11px;padding:3px 8px;border-radius:5px;cursor:pointer;
                                                       color:var(--red);background:var(--red-dim);border:none;">
                                            Void
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endif
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
