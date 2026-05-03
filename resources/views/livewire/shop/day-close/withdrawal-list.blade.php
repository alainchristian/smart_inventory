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
                    <th class="text-left pb-2 text-xs font-semibold" style="color:var(--text-dim);">Method</th>
                    <th class="text-right pb-2 text-xs font-semibold" style="color:var(--text-dim);">Amount</th>
                    <th class="pb-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($withdrawals as $withdrawal)
                    @if ($editingId === $withdrawal->id)
                        <tr style="border-bottom:1px solid var(--border);background:var(--surface2);">
                            <td colspan="4" class="py-2">
                                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                                    <input wire:model="editReason" type="text" placeholder="Reason"
                                           style="font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text);flex:1;min-width:120px;" />
                                    <input wire:model="editAmount" type="number" placeholder="Amount" min="1"
                                           style="font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text);width:110px;" />
                                    <select wire:model="editMethod"
                                            style="font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text);">
                                        <option value="cash">Cash</option>
                                        <option value="mobile_money">Mobile Money</option>
                                    </select>
                                    @if ($editMethod === 'mobile_money')
                                        <input wire:model="editMomoRef" type="text" placeholder="MoMo Reference"
                                               style="font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text);width:140px;" />
                                    @endif
                                </div>
                                @error('editAmount') <div style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</div> @enderror
                                @error('editReason') <div style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</div> @enderror
                            </td>
                            <td class="py-2 text-right" style="white-space:nowrap;">
                                <button wire:click="saveWithdrawal"
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
                            <td class="py-2 text-xs" style="color:var(--text-dim);">{{ $withdrawal->recorded_at->format('H:i') }}</td>
                            <td class="py-2" style="color:var(--text);">{{ $withdrawal->reason }}</td>
                            <td class="py-2 text-xs" style="color:var(--text-dim);">
                                {{ $withdrawal->method === 'mobile_money' ? 'MoMo' : 'Cash' }}
                            </td>
                            <td class="py-2 text-right font-mono" style="color:var(--accent);">{{ number_format($withdrawal->amount) }} RWF</td>
                            <td class="py-2 text-right" style="white-space:nowrap;">
                                @if ($session?->isEditable())
                                    <button wire:click="editWithdrawal({{ $withdrawal->id }})"
                                            style="font-size:11px;padding:3px 8px;border-radius:5px;cursor:pointer;
                                                   color:var(--accent);background:var(--accent-dim);border:none;margin-right:4px;">
                                        Edit
                                    </button>
                                    <button wire:click="voidWithdrawal({{ $withdrawal->id }})"
                                            wire:confirm="Void this withdrawal? This cannot be undone."
                                            style="font-size:11px;padding:3px 8px;border-radius:5px;cursor:pointer;
                                                   color:var(--red);border:1px solid var(--red-dim);background:transparent;">
                                        Void
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
</div>
