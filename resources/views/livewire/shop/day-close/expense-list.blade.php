<div style="font-family:var(--font)">
<style>
.el-table { width:100%;border-collapse:collapse; }
.el-table thead tr { border-bottom:2px solid var(--border); }
.el-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap; }
.el-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr); }
.el-table tbody tr:hover { background:var(--surface2); }
.el-table tbody tr.editing { background:var(--surface);box-shadow:inset 3px 0 0 var(--accent); }
.el-table td { padding:11px 16px;font-size:13px;vertical-align:middle;color:var(--text-sub); }
.el-table tfoot td { padding:12px 16px;font-size:13px;border-top:2px solid var(--border); }
.el-time  { font-family:var(--mono);font-size:12px;color:var(--text-dim);white-space:nowrap; }
.el-cat   { display:inline-block;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;background:var(--surface2);color:var(--text-sub);white-space:nowrap; }
.el-desc  { color:var(--text);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.el-amt   { text-align:right;font-family:var(--mono);font-weight:700;color:var(--text);white-space:nowrap; }
.el-act   { text-align:right;white-space:nowrap;width:1%; }
.el-action { padding:4px 10px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;cursor:pointer;
             font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap; }
.el-action:hover { border-color:var(--accent);color:var(--accent); }
.el-action.destroy:hover { border-color:var(--red);color:var(--red); }
.el-action.yes { background:var(--red);border-color:var(--red);color:#fff; }
.el-action.save { background:var(--accent);border-color:var(--accent);color:#fff; }
.el-in    { padding:7px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;background:var(--surface);color:var(--text);outline:none;font-family:var(--font);box-sizing:border-box; }
.el-in:focus { border-color:var(--accent); }
.el-edit  { display:flex;gap:8px;flex-wrap:wrap;align-items:center; }
.el-error { font-size:12px;color:var(--red);margin-top:5px; }
.el-empty { padding:40px 20px;text-align:center;font-size:13px;color:var(--text-dim); }
@media (max-width:640px) {
    .el-action { min-height:30px !important;min-width:0 !important;padding:4px 10px !important; }
    .el-table td { padding:0 !important; }
    .el-table tfoot td { padding:10px 14px !important; }
    .el-table thead { display:none; }
    .el-table tbody, .el-table tfoot { display:block; }
    .el-table tbody tr { display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:4px 10px;padding:10px 14px;align-items:center; }
    .el-table tfoot tr { display:flex;justify-content:space-between; }
    .el-table td { padding:0; }
    .el-table tfoot td { padding:10px 14px; }
    .el-hide-mob { display:none !important; }
    .el-desc { max-width:none; }
}
</style>

@if ($expenses->isEmpty())
    <div class="el-empty">No expenses recorded for this session.</div>
@else
    <table class="el-table">
        <thead>
            <tr>
                <th style="width:70px">Time</th>
                <th>Description</th>
                <th class="el-hide-mob">Method</th>
                <th style="text-align:right">Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $expense)
                @if ($editingId === $expense->id)
                    <tr class="editing" wire:key="el-edit-{{ $expense->id }}">
                        <td colspan="4" style="padding:12px 16px">
                            <div class="el-edit">
                                <select wire:model="editCategoryId" class="el-in">
                                    <option value="0">Category…</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <input wire:model="editDescription" type="text" placeholder="Description" class="el-in" style="flex:1;min-width:140px">
                                <input wire:model="editAmount" type="number" min="1" placeholder="Amount" class="el-in" style="width:120px;font-family:var(--mono);text-align:right">
                                <select wire:model="editPaymentMethod" class="el-in">
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">MoMo</option>
                                    <option value="bank_transfer">Bank</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            @error('editCategoryId') <div class="el-error">Choose a category.</div> @enderror
                            @error('editDescription') <div class="el-error">{{ $message }}</div> @enderror
                            @error('editAmount') <div class="el-error">{{ $message }}</div> @enderror
                        </td>
                        <td class="el-act" style="padding:12px 16px">
                            <button type="button" class="el-action save" wire:click="saveExpense">Save</button>
                            <button type="button" class="el-action" wire:click="cancelEdit">Cancel</button>
                        </td>
                    </tr>
                @else
                    @php $editable = ! $expense->is_system_generated && $session?->isEditable(); @endphp
                    <tr wire:key="el-{{ $expense->id }}" x-data="{ c:false }">
                        <td class="el-time el-hide-mob">{{ local_time($expense->recorded_at)->format('H:i') }}</td>
                        <td>
                            <div class="el-desc" title="{{ $expense->description }}">{{ $expense->description ?: '—' }}</div>
                            <div style="margin-top:3px"><span class="el-cat">{{ $expense->is_system_generated ? 'Cash shortage (auto)' : ($expense->category->name ?? '—') }}</span></div>
                        </td>
                        <td class="el-hide-mob" style="white-space:nowrap">{{ ['cash' => 'Cash', 'mobile_money' => 'MoMo', 'bank_transfer' => 'Bank', 'other' => 'Other'][$expense->payment_method] ?? ucfirst($expense->payment_method) }}</td>
                        <td class="el-amt">{{ number_format($expense->amount) }}</td>
                        <td class="el-act">
                            @if ($editable)
                                <span x-show="!c" style="display:inline-flex;gap:4px">
                                    <button type="button" class="el-action" wire:click="editExpense({{ $expense->id }})">Edit</button>
                                    <button type="button" class="el-action destroy" @click="c = true">Void</button>
                                </span>
                                <span x-show="c" x-cloak style="display:inline-flex;gap:4px">
                                    <button type="button" class="el-action yes" wire:click="voidExpense({{ $expense->id }})">Void</button>
                                    <button type="button" class="el-action" @click="c = false">Keep</button>
                                </span>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="el-hide-mob" style="font-weight:700;color:var(--text)">Total</td>
                <td class="el-amt" style="font-size:14px">{{ number_format($expenses->sum('amount')) }} <span style="font-size:10px;font-weight:500;color:var(--text-dim)">RWF</span></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@endif
</div>
