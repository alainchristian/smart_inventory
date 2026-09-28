<div style="font-family:var(--font);display:contents">
@if ($canReopen)
<style>
.ro-btn        { padding:9px 16px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);
                 display:inline-flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap; }
.ro-btn-ghost  { background:var(--surface);color:var(--text-sub);border:1px solid var(--border); }
.ro-btn-ghost:hover { background:var(--surface2);color:var(--text); }
.ro-btn-primary { background:var(--accent);color:#fff;border:1px solid var(--accent);box-shadow:0 3px 10px rgba(59,111,212,.25); }
.ro-btn-primary:hover { opacity:.88; }
.ro-btn-primary:disabled { opacity:.5;cursor:not-allowed; }
.ro-overlay    { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;padding:16px; }
.ro-modal      { background:var(--surface);border-radius:var(--r);box-shadow:0 24px 60px rgba(26,31,54,.25);width:100%;max-width:440px;text-align:left; }
.ro-modal-head { padding:20px 22px 0; }
.ro-modal-title { font-size:16px;font-weight:800;color:var(--text);margin:0; }
.ro-modal-sub  { font-size:13px;color:var(--text-dim);margin:4px 0 0;line-height:1.5;white-space:normal; }
.ro-modal-body { padding:18px 22px; }
.ro-modal-foot { padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px; }
.ro-label      { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px; }
.ro-input      { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);
                 outline:none;box-sizing:border-box;font-family:var(--font);resize:vertical; }
.ro-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.ro-error      { font-size:12px;color:var(--red);margin-top:5px; }
.ro-list       { margin:12px 0 0;padding-left:18px;font-size:12px;color:var(--text-dim);line-height:1.6; }
@keyframes ro-spin { to { transform:rotate(360deg) } }
@media (max-width:640px) {
    .ro-modal-foot .ro-btn { flex:1; }
}
</style>

<button type="button" class="ro-btn ro-btn-ghost" wire:click="$set('showModal', true)">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
    Re-open
</button>

@if ($showModal)
    <div class="ro-overlay" wire:key="ro-modal" x-data @keydown.escape.window="$wire.set('showModal', false)" @click.self="$wire.set('showModal', false)">
        <div class="ro-modal" role="dialog" aria-modal="true" aria-labelledby="ro-title-{{ $sessionId }}">
            <div class="ro-modal-head">
                <h2 class="ro-modal-title" id="ro-title-{{ $sessionId }}">Re-open {{ $session->session_date->format('d M Y') }}?</h2>
                <p class="ro-modal-sub">Use this to fix a mistake in the day's records. The owner is notified with your reason.</p>
            </div>
            <form class="ro-modal-body" wire:submit="reopen" id="ro-form-{{ $sessionId }}">
                <label class="ro-label" for="ro-reason-{{ $sessionId }}">Reason <span style="color:var(--red)">*</span></label>
                <textarea id="ro-reason-{{ $sessionId }}" rows="3" class="ro-input" wire:model="reason"
                          placeholder="e.g. An expense was recorded twice"></textarea>
                @error('reason') <div class="ro-error">{{ $message }}</div> @enderror
                <ul class="ro-list">
                    <li>You'll count the cash and close the register again.</li>
                    @if ((int) $session->cash_variance < 0)
                        <li>The {{ number_format(abs((int) $session->cash_variance)) }} RWF shortage expense is removed — it's recorded again if the drawer is still short.</li>
                    @endif
                </ul>
            </form>
            <div class="ro-modal-foot">
                <button type="button" class="ro-btn ro-btn-ghost" wire:click="$set('showModal', false)">Cancel</button>
                <button type="submit" form="ro-form-{{ $sessionId }}" class="ro-btn ro-btn-primary" wire:loading.attr="disabled" wire:target="reopen">
                    <span wire:loading.remove wire:target="reopen">Re-open register</span>
                    <span wire:loading.flex wire:target="reopen" style="align-items:center;gap:6px">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:ro-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                        Re-opening…
                    </span>
                </button>
            </div>
        </div>
    </div>
@endif
@endif
</div>
