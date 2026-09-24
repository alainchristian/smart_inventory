<div style="font-family:var(--font)">
<style>
.wl-table { width:100%;border-collapse:collapse; }
.wl-table thead tr { border-bottom:2px solid var(--border); }
.wl-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap; }
.wl-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr); }
.wl-table tbody tr:hover { background:var(--surface2); }
.wl-table tbody tr.editing { background:var(--surface);box-shadow:inset 3px 0 0 var(--accent); }
.wl-table td { padding:11px 16px;font-size:13px;vertical-align:middle;color:var(--text-sub); }
.wl-table tfoot td { padding:12px 16px;font-size:13px;border-top:2px solid var(--border); }
.wl-time  { font-family:var(--mono);font-size:12px;color:var(--text-dim);white-space:nowrap; }
.wl-desc  { color:var(--text);max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.wl-amt   { text-align:right;font-family:var(--mono);font-weight:700;color:var(--text);white-space:nowrap; }
.wl-act   { text-align:right;white-space:nowrap;width:1%; }
.wl-action { padding:4px 10px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;cursor:pointer;
             font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap; }
.wl-action:hover { border-color:var(--accent);color:var(--accent); }
.wl-action.destroy:hover { border-color:var(--red);color:var(--red); }
.wl-action.yes { background:var(--red);border-color:var(--red);color:#fff; }
.wl-action.save { background:var(--accent);border-color:var(--accent);color:#fff; }
.wl-in    { padding:7px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;background:var(--surface);color:var(--text);outline:none;font-family:var(--font);box-sizing:border-box; }
.wl-in:focus { border-color:var(--accent); }
.wl-edit  { display:flex;gap:8px;flex-wrap:wrap;align-items:center; }
.wl-error { font-size:12px;color:var(--red);margin-top:5px; }
.wl-empty { padding:40px 20px;text-align:center;font-size:13px;color:var(--text-dim); }
@media (max-width:640px) {
    .wl-action { min-height:30px !important;min-width:0 !important;padding:4px 10px !important; }
    .wl-table td { padding:0 !important; }
    .wl-table tfoot td { padding:10px 14px !important; }
    .wl-table thead { display:none; }
    .wl-table tbody, .wl-table tfoot { display:block; }
    .wl-table tbody tr { display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:4px 10px;padding:10px 14px;align-items:center; }
    .wl-table tfoot tr { display:flex;justify-content:space-between; }
    .wl-table td { padding:0; }
    .wl-table tfoot td { padding:10px 14px; }
    .wl-hide-mob { display:none !important; }
    .wl-desc { max-width:none; }
}
</style>

@if ($withdrawals->isEmpty())
    <div class="wl-empty">No owner withdrawals recorded for this session.</div>
@else
    <table class="wl-table">
        <thead>
            <tr>
                <th style="width:70px">Time</th>
                <th>Reason</th>
                <th class="wl-hide-mob">Method</th>
                <th style="text-align:right">Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($withdrawals as $withdrawal)
                @if ($editingId === $withdrawal->id)
                    <tr class="editing" wire:key="wl-edit-{{ $withdrawal->id }}">
                        <td colspan="4" style="padding:12px 16px">
                            <div class="wl-edit">
                                <input wire:model="editReason" type="text" placeholder="Reason" class="wl-in" style="flex:1;min-width:140px">
                                <input wire:model="editAmount" type="number" min="1" placeholder="Amount" class="wl-in" style="width:120px;font-family:var(--mono);text-align:right">
                                <select wire:model.live="editMethod" class="wl-in">
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">MoMo</option>
                                </select>
                                @if ($editMethod === 'mobile_money')
                                    <input wire:model="editMomoRef" type="text" placeholder="MoMo reference" class="wl-in" style="width:150px;font-family:var(--mono)">
                                @endif
                            </div>
                            @error('editReason') <div class="wl-error">{{ $message }}</div> @enderror
                            @error('editAmount') <div class="wl-error">{{ $message }}</div> @enderror
                        </td>
                        <td class="wl-act" style="padding:12px 16px">
                            <button type="button" class="wl-action save" wire:click="saveWithdrawal">Save</button>
                            <button type="button" class="wl-action" wire:click="cancelEdit">Cancel</button>
                        </td>
                    </tr>
                @else
                    <tr wire:key="wl-{{ $withdrawal->id }}" x-data="{ c:false }">
                        <td class="wl-time wl-hide-mob">{{ local_time($withdrawal->recorded_at)->format('H:i') }}</td>
                        <td>
                            <div class="wl-desc" title="{{ $withdrawal->reason }}">{{ $withdrawal->reason }}</div>
                            @if ($withdrawal->momo_reference)
                                <div style="font-size:12px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">{{ $withdrawal->momo_reference }}</div>
                            @endif
                        </td>
                        <td class="wl-hide-mob">{{ $withdrawal->method === 'mobile_money' ? 'MoMo' : 'Cash' }}</td>
                        <td class="wl-amt">{{ number_format($withdrawal->amount) }}</td>
                        <td class="wl-act">
                            @if ($session?->isEditable())
                                <span x-show="!c" style="display:inline-flex;gap:4px">
                                    <button type="button" class="wl-action" wire:click="editWithdrawal({{ $withdrawal->id }})">Edit</button>
                                    <button type="button" class="wl-action destroy" @click="c = true">Void</button>
                                </span>
                                <span x-show="c" x-cloak style="display:inline-flex;gap:4px">
                                    <button type="button" class="wl-action yes" wire:click="voidWithdrawal({{ $withdrawal->id }})">Void</button>
                                    <button type="button" class="wl-action" @click="c = false">Keep</button>
                                </span>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="wl-hide-mob" style="font-weight:700;color:var(--text)">Total</td>
                <td class="wl-amt" style="font-size:14px">{{ number_format($withdrawals->sum('amount')) }} <span style="font-size:10px;font-weight:500;color:var(--text-dim)">RWF</span></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@endif
</div>
