<div style="font-family:var(--font)">
<style>
.ae-field     { margin-bottom:18px; }
.ae-label     { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px; }
.ae-label span { color:var(--red); }
.ae-label em  { font-style:normal;font-weight:500;color:var(--text-dim); }
.ae-input     { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);
                outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr); }
.ae-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.ae-money     { position:relative; }
.ae-money .ae-input { font-family:var(--mono);font-size:18px;font-weight:700;text-align:right;padding-right:52px;-moz-appearance:textfield; }
.ae-money-u   { position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--text-dim);pointer-events:none; }
.ae-seg       { display:grid;grid-template-columns:repeat(4,1fr);gap:4px;padding:3px;border-radius:10px;border:1.5px solid var(--border); }
.ae-seg button { padding:7px 4px;border:none;border-radius:7px;background:transparent;font-size:12px;font-weight:600;color:var(--text-dim);
                 cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap; }
.ae-seg button:hover  { color:var(--text);background:var(--surface2); }
.ae-seg button.active { background:var(--accent);color:#fff; }
.ae-hint      { font-size:12px;color:var(--text-dim);margin-top:6px; }
.ae-error     { font-size:12px;color:var(--red);margin-top:5px; }
.ae-actions   { display:flex;justify-content:flex-end;gap:8px;padding-top:16px;margin-top:4px;border-top:1px solid var(--border); }
.ae-btn       { padding:10px 18px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);
                display:inline-flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap; }
.ae-btn-ghost { background:var(--surface);color:var(--text-sub);border:1px solid var(--border); }
.ae-btn-ghost:hover { background:var(--surface2);color:var(--text); }
.ae-btn-primary { background:var(--accent);color:#fff;border:1px solid var(--accent);box-shadow:0 3px 10px rgba(59,111,212,.25); }
.ae-btn-primary:hover { opacity:.88; }
.ae-btn-primary:disabled { opacity:.5;cursor:not-allowed; }
@keyframes ae-spin { to { transform:rotate(360deg) } }
@media (max-width:640px) {
    .ae-seg button { min-height:34px !important;min-width:0 !important;padding:7px 4px !important; }
}
@media (max-width:480px) {
    .ae-actions { flex-direction:column-reverse; }
    .ae-btn { width:100%; }
}
</style>

<form wire:submit="saveExpense">
    <div class="ae-field">
        <label class="ae-label" for="ae-cat-{{ $this->getId() }}">Category <span>*</span></label>
        <select id="ae-cat-{{ $this->getId() }}" wire:model="categoryId" class="ae-input">
            <option value="0">Select a category…</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
        @error('categoryId') <div class="ae-error">Choose a category.</div> @enderror
    </div>

    <div class="ae-field">
        <label class="ae-label" for="ae-amt-{{ $this->getId() }}">Amount <span>*</span></label>
        <div class="ae-money">
            <input id="ae-amt-{{ $this->getId() }}" type="number" min="1" inputmode="numeric" wire:model="amount" placeholder="0" class="ae-input">
            <span class="ae-money-u">RWF</span>
        </div>
        @error('amount') <div class="ae-error">{{ $message }}</div> @enderror
    </div>

    <div class="ae-field">
        <label class="ae-label">Paid with</label>
        <div class="ae-seg">
            @foreach (['cash' => 'Cash', 'mobile_money' => 'MoMo', 'bank_transfer' => 'Bank', 'other' => 'Other'] as $key => $label)
                <button type="button" class="{{ $paymentMethod === $key ? 'active' : '' }}" wire:click="$set('paymentMethod', '{{ $key }}')">{{ $label }}</button>
            @endforeach
        </div>
        @php
            $avail = match ($paymentMethod) {
                'cash'          => ['Cash in drawer', $summary['expected_cash']],
                'mobile_money'  => ['MoMo available', $summary['momo_available']],
                'bank_transfer' => ['Bank available', $summary['bank_available']],
                default         => null,
            };
        @endphp
        @if ($avail)
            <div class="ae-hint">{{ $avail[0] }}: <b style="font-family:var(--mono);color:{{ $avail[1] > 0 ? 'var(--text-sub)' : 'var(--red)' }}">{{ number_format($avail[1]) }} RWF</b></div>
        @endif
    </div>

    <div class="ae-field">
        <label class="ae-label" for="ae-desc-{{ $this->getId() }}">Description <span>*</span></label>
        <input id="ae-desc-{{ $this->getId() }}" type="text" wire:model="description" placeholder="What was this for?" class="ae-input">
        @error('description') <div class="ae-error">{{ $message }}</div> @enderror
    </div>

    <div class="ae-field">
        <label class="ae-label" for="ae-ref-{{ $this->getId() }}">Receipt reference <em>(optional)</em></label>
        <input id="ae-ref-{{ $this->getId() }}" type="text" wire:model="receiptReference" placeholder="Receipt or invoice number" class="ae-input" style="font-family:var(--mono)">
    </div>

    <div class="ae-actions">
        @if ($inDrawer)
            <button type="button" class="ae-btn ae-btn-ghost" @click="$dispatch('dc-record-close')">Cancel</button>
        @endif
        <button type="submit" class="ae-btn ae-btn-primary" wire:loading.attr="disabled" wire:target="saveExpense">
            <span wire:loading.remove wire:target="saveExpense">Save expense</span>
            <span wire:loading.flex wire:target="saveExpense" style="align-items:center;gap:6px">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:ae-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                Saving…
            </span>
        </button>
    </div>
</form>
</div>
