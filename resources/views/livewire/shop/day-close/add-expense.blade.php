<div>
    @if (session()->has('success'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--green-dim);color:var(--green);">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    <div class="text-sm font-semibold mb-3" style="color:var(--text);">Record Expense</div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Category</label>
            <select wire:model="categoryId"
                    class="w-full px-3 py-2.5 rounded-lg text-sm"
                    style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                <option value="0">Select category…</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('categoryId') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Amount (RWF)</label>
            <input type="number"
                   wire:model="amount"
                   wire:keydown.enter="saveExpense"
                   min="1"
                   class="w-full px-3 py-2.5 rounded-lg text-sm"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);text-align:right;"
                   placeholder="0"
                   onfocus="this.style.borderColor='var(--accent)';if(this.value==='0')this.value='';"
                   onblur="this.style.borderColor='var(--border)';">
            @error('amount') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Description</label>
            <input type="text"
                   wire:model="description"
                   wire:keydown.enter="saveExpense"
                   class="w-full px-3 py-2.5 rounded-lg text-sm"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                   placeholder="What was this expense for?">
            @error('description') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Payment Method</label>
            <select wire:model="paymentMethod"
                    class="w-full px-3 py-2.5 rounded-lg text-sm"
                    style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                <option value="cash">Cash</option>
                <option value="mobile_money">Mobile Money</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Receipt Ref (optional)</label>
            <input type="text"
                   wire:model="receiptReference"
                   wire:keydown.enter="saveExpense"
                   class="w-full px-3 py-2.5 rounded-lg text-sm"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                   placeholder="Receipt or ref — press ↵ to save">
        </div>
    </div>
</div>
