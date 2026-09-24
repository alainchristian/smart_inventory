<div style="font-family:var(--font)">
<style>
.aw-field     { margin-bottom:18px; }
.aw-row       { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px; }
.aw-label     { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px; }
.aw-label span { color:var(--red); }
.aw-label em  { font-style:normal;font-weight:500;color:var(--text-dim); }
.aw-input     { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);
                outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr); }
.aw-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.aw-money     { position:relative; }
.aw-money .aw-input { font-family:var(--mono);font-size:16px;font-weight:700;text-align:right;padding-right:48px;-moz-appearance:textfield; }
.aw-money-u   { position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--text-dim);pointer-events:none; }
.aw-hint      { font-size:12px;color:var(--text-dim);margin-top:6px; }
.aw-error     { font-size:12px;color:var(--red);margin-top:5px; }
.aw-total     { display:flex;justify-content:space-between;padding:10px 0 0;margin-bottom:14px;font-size:13px;color:var(--text-sub); }
.aw-actions   { display:flex;justify-content:flex-end;gap:8px;padding-top:16px;margin-top:4px;border-top:1px solid var(--border); }
.aw-btn       { padding:10px 18px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);
                display:inline-flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap; }
.aw-btn-ghost { background:var(--surface);color:var(--text-sub);border:1px solid var(--border); }
.aw-btn-ghost:hover { background:var(--surface2);color:var(--text); }
.aw-btn-primary { background:var(--accent);color:#fff;border:1px solid var(--accent);box-shadow:0 3px 10px rgba(59,111,212,.25); }
.aw-btn-primary:hover { opacity:.88; }
.aw-btn-primary:disabled { opacity:.5;cursor:not-allowed; }
@keyframes aw-spin { to { transform:rotate(360deg) } }
@media (max-width:480px) {
    .aw-row { grid-template-columns:1fr; }
    .aw-actions { flex-direction:column-reverse; }
    .aw-btn { width:100%; }
}
</style>

<form wire:submit="saveWithdrawal">
    <div class="aw-row">
        <div>
            <label class="aw-label" for="aw-cash-{{ $this->getId() }}">From cash</label>
            <div class="aw-money">
                <input id="aw-cash-{{ $this->getId() }}" type="number" min="0" inputmode="numeric" wire:model.live.debounce.400ms="cashAmount" placeholder="0" class="aw-input">
                <span class="aw-money-u">RWF</span>
            </div>
            <div class="aw-hint">{{ number_format($summary['expected_cash']) }} in drawer</div>
            @error('cashAmount') <div class="aw-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="aw-label" for="aw-momo-{{ $this->getId() }}">From MoMo</label>
            <div class="aw-money">
                <input id="aw-momo-{{ $this->getId() }}" type="number" min="0" inputmode="numeric" wire:model.live.debounce.400ms="momoAmount" placeholder="0" class="aw-input">
                <span class="aw-money-u">RWF</span>
            </div>
            <div class="aw-hint">{{ number_format($summary['momo_available']) }} available</div>
            @error('momoAmount') <div class="aw-error">{{ $message }}</div> @enderror
        </div>
    </div>

    @if ((int) $momoAmount > 0)
        <div class="aw-field">
            <label class="aw-label" for="aw-ref-{{ $this->getId() }}">MoMo reference <em>(optional)</em></label>
            <input id="aw-ref-{{ $this->getId() }}" type="text" wire:model="momoReference" placeholder="Transaction ID or phone number" class="aw-input" style="font-family:var(--mono)">
            @error('momoReference') <div class="aw-error">{{ $message }}</div> @enderror
        </div>
    @endif

    <div class="aw-field">
        <label class="aw-label" for="aw-reason-{{ $this->getId() }}">Reason <span>*</span></label>
        <input id="aw-reason-{{ $this->getId() }}" type="text" wire:model="reason" placeholder="e.g. school fees, personal use" class="aw-input">
        @error('reason') <div class="aw-error">{{ $message }}</div> @enderror
    </div>

    @if ((int) $cashAmount > 0 && (int) $momoAmount > 0)
        <div class="aw-total">
            <span>Total withdrawal</span>
            <b style="font-family:var(--mono);color:var(--text)">{{ number_format((int) $cashAmount + (int) $momoAmount) }} RWF</b>
        </div>
    @endif

    <div class="aw-actions">
        @if ($inDrawer)
            <button type="button" class="aw-btn aw-btn-ghost" @click="$dispatch('dc-record-close')">Cancel</button>
        @endif
        <button type="submit" class="aw-btn aw-btn-primary" wire:loading.attr="disabled" wire:target="saveWithdrawal">
            <span wire:loading.remove wire:target="saveWithdrawal">Save withdrawal</span>
            <span wire:loading.flex wire:target="saveWithdrawal" style="align-items:center;gap:6px">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:aw-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                Saving…
            </span>
        </button>
    </div>
</form>
</div>
