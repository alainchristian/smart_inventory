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
                   inputmode="decimal"
                   enterkeyhint="next"
                   min="1"
                   class="w-full px-3 py-2.5 rounded-lg text-sm"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);text-align:right;"
                   placeholder="0"
                   onfocus="this.style.borderColor='var(--accent)';if(this.value==='0')this.value='';"
                   onblur="this.style.borderColor='var(--border)';">
            @error('amount') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Description <span style="opacity:.5;font-weight:400;">(optional)</span></label>
            <input type="text"
                   wire:model="description"
                   enterkeyhint="next"
                   class="w-full px-3 py-2.5 rounded-lg text-sm"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                   placeholder="What was this expense for?">
            @error('description') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Payment Method</label>
            <select wire:model.live="paymentMethod"
                    class="w-full px-3 py-2.5 rounded-lg text-sm"
                    style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                <option value="cash">Cash</option>
                <option value="mobile_money">Mobile Money</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="other">Other</option>
            </select>
            @php
                $avail = match($paymentMethod) {
                    'cash'          => ['label' => 'Cash in drawer',  'amount' => $summary['expected_cash'],  'color' => 'var(--green)'],
                    'mobile_money'  => ['label' => 'MoMo available',  'amount' => $summary['momo_available'], 'color' => '#0ea5e9'],
                    'bank_transfer' => ['label' => 'Bank available',   'amount' => $summary['bank_available'], 'color' => '#7c3aed'],
                    default         => null,
                };
            @endphp
            @if ($avail)
                <div style="margin-top:4px;font-size:11px;color:{{ $avail['amount'] > 0 ? $avail['color'] : 'var(--red)' }};">
                    {{ $avail['label'] }}: <strong>{{ number_format($avail['amount']) }} RWF</strong>
                </div>
            @endif
        </div>

        <div>
            <label class="block text-xs font-medium mb-1.5" style="color:var(--text-dim);">Receipt Ref <span style="opacity:.5;font-weight:400;">(optional)</span></label>
            <input type="text"
                   wire:model="receiptReference"
                   wire:keydown.enter="saveExpense"
                   enterkeyhint="done"
                   class="w-full px-3 py-2.5 rounded-lg text-sm"
                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                   placeholder="Receipt or reference number">
        </div>
    </div>

    {{-- Save button — primary action, full width on mobile --}}
    <div class="mt-4">
        <button wire:click="saveExpense"
                wire:loading.attr="disabled"
                style="width:100%;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:700;
                       background:var(--accent);color:white;border:none;cursor:pointer;
                       display:flex;align-items:center;justify-content:center;gap:8px;
                       transition:opacity 0.15s;"
                onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span wire:loading.remove wire:target="saveExpense">Add Expense</span>
            <span wire:loading wire:target="saveExpense" style="display:none;">Saving…</span>
        </button>
    </div>
</div>
