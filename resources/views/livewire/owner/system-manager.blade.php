<div style="font-family:var(--font)">
<style>
.sm-tabs       { display:flex;gap:4px;margin-bottom:28px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:4px }
.sm-tab        { flex:1;display:flex;align-items:center;justify-content:center;gap:7px;padding:9px 18px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;border:none;background:transparent;color:var(--text-dim);transition:all .15s;font-family:var(--font) }
.sm-tab.active { background:var(--accent);color:#fff;box-shadow:0 2px 10px rgba(59,111,212,.25) }
.sm-tab svg    { width:15px;height:15px;flex-shrink:0 }

.sm-section         { margin-bottom:28px }
.sm-section-head    { display:flex;align-items:center;justify-content:space-between;margin-bottom:14px }
.sm-section-title   { font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-dim) }
.sm-card            { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden }
.sm-table-wrap      { overflow-x:auto;-webkit-overflow-scrolling:touch }

.sm-table           { width:100%;border-collapse:collapse;font-size:13px;min-width:420px }
.sm-table thead tr  { border-bottom:1px solid var(--border);background:var(--bg) }
.sm-table thead th  { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.sm-table tbody tr  { border-bottom:1px solid var(--border);transition:background .12s }
.sm-table tbody tr:last-child { border-bottom:none }
.sm-table tbody tr:hover { background:var(--surface2) }
.sm-table td        { padding:12px 16px;vertical-align:middle }

.sm-badge           { display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700 }
.sm-btn             { display:inline-flex;align-items:center;gap:5px;padding:6px 13px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:transparent;color:var(--text-dim);font-family:var(--font);transition:all .12s;white-space:nowrap }
.sm-btn:hover       { border-color:var(--accent);color:var(--accent) }
.sm-btn.danger:hover { border-color:var(--red);color:var(--red) }
.sm-btn.primary     { background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 2px 8px rgba(59,111,212,.2) }
.sm-btn.primary:hover { opacity:.88 }
.sm-btn.sm          { padding:4px 10px;font-size:11px }

.sm-form            { background:var(--surface2);border-top:1px solid var(--border);padding:16px }
.sm-form-grid       { display:grid;gap:10px }
.sm-form-grid.cols2 { grid-template-columns:1fr 1fr }
.sm-form-grid.cols3 { grid-template-columns:1fr 1fr 1fr }
.sm-label           { display:block;font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px }
.sm-input           { width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:var(--surface);color:var(--text);outline:none;font-family:var(--font);box-sizing:border-box;transition:border-color .12s }
.sm-input:focus     { border-color:var(--accent) }
.sm-select          { width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:var(--surface);color:var(--text);outline:none;font-family:var(--font);box-sizing:border-box;cursor:pointer }
.sm-error           { font-size:11.5px;color:var(--red);margin-top:4px }

.sm-empty           { padding:40px 20px;text-align:center;color:var(--text-dim);font-size:13px }

.sm-confirm-row     { background:rgba(217,119,6,.05) !important }
.sm-confirm-inline  { display:flex;align-items:center;gap:8px;flex-wrap:wrap }
.sm-yes             { padding:4px 12px;background:var(--red);color:#fff;border:none;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;font-family:var(--font) }
.sm-no              { padding:4px 10px;background:transparent;border:1.5px solid var(--border);color:var(--text-dim);border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;font-family:var(--font) }

/* Wipe grid */
.sm-wipe-grid   { display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:20px }
.sm-wipe-card   { background:var(--surface);border:1.5px solid var(--border);border-radius:12px;padding:14px 16px;cursor:pointer;transition:border-color .15s,background .15s;position:relative }
.sm-wipe-card:hover  { border-color:var(--accent) }
.sm-wipe-card.checked { border-color:var(--red);background:rgba(239,68,68,.04) }
.sm-wipe-card input[type=checkbox] { position:absolute;top:14px;right:14px;width:16px;height:16px;accent-color:var(--red);cursor:pointer }
.sm-wipe-label  { font-size:13px;font-weight:700;color:var(--text);margin-bottom:3px;padding-right:24px }
.sm-wipe-desc   { font-size:11.5px;color:var(--text-dim);line-height:1.45 }

.sm-wipe-actions { background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px }
.sm-confirm-bar  { display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:14px }
.sm-confirm-input { padding:8px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:var(--surface2);color:var(--text);font-family:monospace;outline:none;min-width:160px;box-sizing:border-box;transition:border-color .12s }
.sm-confirm-input:focus { border-color:var(--red) }

.sm-modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9998;display:flex;align-items:center;justify-content:center;padding:24px }
.sm-modal         { background:var(--surface);border:1.5px solid var(--red);border-radius:16px;padding:36px;max-width:420px;width:100%;text-align:center }

@media(max-width:640px) {
    .sm-form-grid.cols2, .sm-form-grid.cols3 { grid-template-columns:1fr }
    .sm-wipe-grid { grid-template-columns:1fr 1fr }
    .sm-confirm-bar { flex-direction:column;align-items:stretch }
    .sm-tabs { flex-direction:column }
    .sm-section-head { flex-wrap:wrap;gap:8px }
    .sm-section-title { font-size:12px }
}
@media(max-width:400px) {
    .sm-wipe-grid { grid-template-columns:1fr }
}
</style>

{{-- Tab bar --}}
<div class="sm-tabs">
    <button class="sm-tab {{ $activeTab === 'setup' ? 'active' : '' }}" wire:click="$set('activeTab','setup')">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 7a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
        Setup & Data Entry
    </button>
    <button class="sm-tab {{ $activeTab === 'wipe' ? 'active' : '' }}" wire:click="$set('activeTab','wipe')">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        Data Management
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════
     SETUP TAB
══════════════════════════════════════════════════════════ --}}
@if($activeTab === 'setup')
<style>
.sm-launchpad { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:16px; }
.sm-launch-card { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:20px; display:flex; flex-direction:column; text-decoration:none; transition:all .15s; }
.sm-launch-card:hover { border-color:var(--accent); transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,0.05); }
.sm-launch-head { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
.sm-launch-icon { width:40px; height:40px; border-radius:10px; background:var(--surface2); display:flex; align-items:center; justify-content:center; color:var(--text-dim); }
.sm-launch-card:hover .sm-launch-icon { background:var(--accent-dim); color:var(--accent); }
.sm-launch-title { font-size:14px; font-weight:700; color:var(--text); }
.sm-launch-desc { font-size:13px; color:var(--text-dim); line-height:1.4; margin-bottom:16px; flex:1; }
.sm-launch-stat { display:inline-flex; align-items:center; background:var(--surface2); padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; color:var(--text); align-self:flex-start; }
</style>

<div class="sm-launchpad">
    <!-- Categories -->
    <a href="{{ route('owner.categories.index') }}" wire:navigate class="sm-launch-card">
        <div class="sm-launch-head">
            <div class="sm-launch-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div class="sm-launch-title">Product Categories</div>
        </div>
        <div class="sm-launch-desc">Manage the hierarchy and grouping of your products across all locations.</div>
        <div class="sm-launch-stat">{{ $totalCategories }} Categories</div>
    </a>

    <!-- Expense Categories -->
    <a href="{{ route('owner.expense-categories.index') }}" wire:navigate class="sm-launch-card">
        <div class="sm-launch-head">
            <div class="sm-launch-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="sm-launch-title">Expense Categories</div>
        </div>
        <div class="sm-launch-desc">Categorize store and warehouse expenses for detailed financial reporting.</div>
        <div class="sm-launch-stat">{{ $totalExpCategories }} Expense Types</div>
    </a>

    <!-- Transporters -->
    <a href="{{ route('owner.transporters.index') }}" wire:navigate class="sm-launch-card">
        <div class="sm-launch-head">
            <div class="sm-launch-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
            </div>
            <div class="sm-launch-title">Transporters</div>
        </div>
        <div class="sm-launch-desc">Manage delivery drivers and transport companies moving your stock.</div>
        <div class="sm-launch-stat">{{ $totalTransporters }} Transporters</div>
    </a>

    <!-- System Settings -->
    <a href="{{ route('owner.settings') }}" wire:navigate class="sm-launch-card">
        <div class="sm-launch-head">
            <div class="sm-launch-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="sm-launch-title">Business Settings</div>
        </div>
        <div class="sm-launch-desc">Configure business-wide settings like currency and company information.</div>
        <div class="sm-launch-stat">System Config</div>
    </a>
</div>
@endif {{-- end setup tab --}}

{{-- ══════════════════════════════════════════════════════════
     DATA MANAGEMENT TAB
══════════════════════════════════════════════════════════ --}}
@if($activeTab === 'wipe')

@if($wipeDone)
    <div style="background:var(--surface);border:1.5px solid var(--green);border-radius:14px;padding:36px;text-align:center;max-width:480px;margin:0 auto">
        <div style="width:52px;height:52px;border-radius:50%;background:var(--green-dim);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--green)"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3 style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:8px">Data wiped successfully</h3>
        <p style="font-size:13px;color:var(--text-dim);margin-bottom:20px">The selected data groups have been permanently removed.</p>
        <button class="sm-btn primary" wire:click="$set('wipeDone',false)">Back to Data Management</button>
    </div>
@else

    @if($wipeError)
        <div style="background:var(--red-dim);border:1px solid var(--red);border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:var(--red)">
            {{ $wipeError }}
        </div>
    @endif

    <div style="margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
        <p style="font-size:13px;color:var(--text-dim);margin:0">Select the data groups you want to permanently delete, then confirm below.</p>
        <button class="sm-btn sm" wire:click="toggleAll">
            {{ count($selected) === count($this->groups()) ? 'Deselect All' : 'Select All' }}
        </button>
    </div>

    <div class="sm-wipe-grid">
        @foreach($this->groups() as $key => $group)
            <label class="sm-wipe-card {{ in_array($key, $selected) ? 'checked' : '' }}"
                   wire:click="$set('selected', array_values({{ in_array($key, $selected) ? 'array_diff' : 'array_merge' }}({{ json_encode($selected) }}, ['{{ $key }}'])))">
                <input type="checkbox" wire:model="selected" value="{{ $key }}" onclick="event.stopPropagation()" />
                <div class="sm-wipe-label">{{ $group['label'] }}</div>
                <div class="sm-wipe-desc">{{ $group['desc'] }}</div>
            </label>
        @endforeach
    </div>

    <div class="sm-wipe-actions">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px">
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:2px">
                    {{ count($selected) }} group{{ count($selected) === 1 ? '' : 's' }} selected
                </div>
                <div style="font-size:12px;color:var(--text-dim)">This action is permanent and cannot be undone.</div>
            </div>
            @if(count($selected) > 0)
                <span class="sm-badge" style="background:var(--red-dim);color:var(--red)">
                    {{ implode(', ', array_map(fn($k) => $this->groups()[$k]['label'] ?? $k, $selected)) }}
                </span>
            @endif
        </div>

        @error('wipe') <p class="sm-error" style="margin-bottom:10px">{{ $message }}</p> @enderror

        <div x-data="{ ct: '' }" style="margin-top:20px">
            <p style="font-size:11px;font-weight:700;letter-spacing:.08em;color:var(--text-dim);margin-bottom:10px;text-transform:uppercase">
                Type <span style="color:var(--red);font-family:monospace;letter-spacing:.05em">DELETE</span> to confirm
            </p>
            <div style="display:flex;align-items:center;gap:12px">
                <input type="text"
                       x-model="ct"
                       wire:model="confirmText"
                       autocomplete="off" spellcheck="false"
                       placeholder="DELETE"
                       @input="$wire.set('confirmText', $event.target.value)"
                       style="width:140px;padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;background:var(--surface);font-size:14px;font-family:monospace;color:var(--text);outline:none;letter-spacing:.5px;transition:border-color .15s"
                       :style="ct === 'DELETE' ? 'border-color:var(--red)' : ''" />
                <button
                    wire:click="requestWipe"
                    :disabled="ct !== 'DELETE' || {{ count($selected) }} === 0"
                    :style="(ct === 'DELETE' && {{ count($selected) }} > 0)
                        ? 'background:var(--red);color:#fff;cursor:pointer;opacity:1'
                        : 'background:var(--red-dim);color:var(--red);cursor:not-allowed;opacity:.7'"
                    style="padding:11px 22px;border:none;border-radius:10px;font-size:13px;font-weight:700;font-family:var(--font);transition:background .2s,color .2s,opacity .2s;white-space:nowrap">
                    Delete Selected Data
                </button>
            </div>
        </div>
    </div>
@endif

{{-- Confirm modal --}}
@if($showConfirm)
    <div class="sm-modal-overlay">
        <div class="sm-modal">
            <div style="width:52px;height:52px;border-radius:50%;background:var(--red-dim);display:flex;align-items:center;justify-content:center;margin:0 auto 18px">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--red)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <h3 style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:10px">This cannot be undone</h3>
            <p style="font-size:13px;color:var(--text-dim);line-height:1.6;margin-bottom:24px">
                <strong style="color:var(--text)">{{ count($selected) }} data group{{ count($selected) === 1 ? '' : 's' }}</strong> will be permanently erased from the database.
            </p>
            <div style="display:flex;gap:10px;justify-content:center">
                <button wire:click="cancelWipe" style="padding:9px 20px;border-radius:9px;background:var(--surface2);color:var(--text);border:1px solid var(--border);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font)">
                    Cancel
                </button>
                <button wire:click="executeWipe" wire:loading.attr="disabled" style="padding:9px 20px;border-radius:9px;background:var(--red);color:#fff;border:none;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);display:inline-flex;align-items:center;gap:7px">
                    <span wire:loading.remove wire:target="executeWipe">Yes, Delete Now</span>
                    <span wire:loading wire:target="executeWipe" style="display:none">Deleting…</span>
                </button>
            </div>
        </div>
    </div>
@endif

@endif {{-- end wipe tab --}}

</div>
