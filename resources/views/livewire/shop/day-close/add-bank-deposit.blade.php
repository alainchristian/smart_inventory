<div style="font-family:var(--font)">
<style>
.ad-field     { margin-bottom:18px; }
.ad-label     { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px; }
.ad-label span { color:var(--red); }
.ad-label em  { font-style:normal;font-weight:500;color:var(--text-dim); }
.ad-input     { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);
                outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr); }
.ad-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.ad-money     { position:relative; }
.ad-money .ad-input { font-family:var(--mono);font-size:18px;font-weight:700;text-align:right;padding-right:52px;-moz-appearance:textfield; }
.ad-money-u   { position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--text-dim);pointer-events:none; }
.ad-seg       { display:grid;grid-template-columns:1fr 1fr;gap:4px;padding:3px;border-radius:10px;border:1.5px solid var(--border); }
.ad-seg button { padding:7px 4px;border:none;border-radius:7px;background:transparent;font-size:12px;font-weight:600;color:var(--text-dim);
                 cursor:pointer;font-family:var(--font);transition:all var(--tr); }
.ad-seg button:hover  { color:var(--text);background:var(--surface2); }
.ad-seg button.active { background:var(--accent);color:#fff; }
.ad-hint      { font-size:12px;color:var(--text-dim);margin-top:6px; }
.ad-error     { font-size:12px;color:var(--red);margin-top:5px; }
.ad-actions   { display:flex;justify-content:flex-end;gap:8px;padding-top:16px;margin-top:4px;border-top:1px solid var(--border); }
.ad-btn       { padding:10px 18px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);
                display:inline-flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap; }
.ad-btn-ghost { background:var(--surface);color:var(--text-sub);border:1px solid var(--border); }
.ad-btn-ghost:hover { background:var(--surface2);color:var(--text); }
.ad-btn-primary { background:var(--accent);color:#fff;border:1px solid var(--accent);box-shadow:0 3px 10px rgba(59,111,212,.25); }
.ad-btn-primary:hover { opacity:.88; }
.ad-btn-primary:disabled { opacity:.5;cursor:not-allowed; }
@keyframes ad-spin { to { transform:rotate(360deg) } }
@media (max-width:640px) {
    .ad-seg button { min-height:34px !important;min-width:0 !important;padding:7px 4px !important; }
}
@media (max-width:480px) {
    .ad-actions { flex-direction:column-reverse; }
    .ad-btn { width:100%; }
}
</style>

<form wire:submit="saveDeposit">
    <div class="ad-field">
        <label class="ad-label">Deposit from</label>
        <div class="ad-seg">
            <button type="button" class="{{ $source === 'cash' ? 'active' : '' }}" wire:click="$set('source', 'cash')">Cash drawer</button>
            <button type="button" class="{{ $source === 'mobile_money' ? 'active' : '' }}" wire:click="$set('source', 'mobile_money')">Mobile money</button>
        </div>
        @php $sourceAvail = $source === 'cash' ? $summary['expected_cash'] : $summary['momo_available']; @endphp
        <div class="ad-hint">{{ $source === 'cash' ? 'Cash in drawer' : 'MoMo available' }}: <b style="font-family:var(--mono);color:{{ $sourceAvail > 0 ? 'var(--text-sub)' : 'var(--red)' }}">{{ number_format($sourceAvail) }} RWF</b></div>
    </div>

    <div class="ad-field">
        <label class="ad-label" for="ad-amt-{{ $this->getId() }}">Amount <span>*</span></label>
        <div class="ad-money">
            <input id="ad-amt-{{ $this->getId() }}" type="number" min="1" inputmode="numeric" wire:model="amount" placeholder="0" class="ad-input">
            <span class="ad-money-u">RWF</span>
        </div>
        @error('amount') <div class="ad-error">{{ $message }}</div> @enderror
    </div>

    <div class="ad-field">
        <label class="ad-label" for="ad-ref-{{ $this->getId() }}">Bank slip / reference <em>(optional)</em></label>
        <input id="ad-ref-{{ $this->getId() }}" type="text" wire:model="bankReference" placeholder="Slip or reference number" class="ad-input" style="font-family:var(--mono)">
        @error('bankReference') <div class="ad-error">{{ $message }}</div> @enderror
    </div>

    <div class="ad-field">
        <label class="ad-label" for="ad-notes-{{ $this->getId() }}">Notes <em>(optional)</em></label>
        <input id="ad-notes-{{ $this->getId() }}" type="text" wire:model="notes" placeholder="Bank, branch, who deposited…" class="ad-input">
        @error('notes') <div class="ad-error">{{ $message }}</div> @enderror
    </div>

    <div class="ad-actions">
        @if ($inDrawer)
            <button type="button" class="ad-btn ad-btn-ghost" @click="$dispatch('dc-record-close')">Cancel</button>
        @endif
        <button type="submit" class="ad-btn ad-btn-primary" wire:loading.attr="disabled" wire:target="saveDeposit">
            <span wire:loading.remove wire:target="saveDeposit">Save deposit</span>
            <span wire:loading.flex wire:target="saveDeposit" style="align-items:center;gap:6px">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:ad-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                Saving…
            </span>
        </button>
    </div>
</form>
</div>
