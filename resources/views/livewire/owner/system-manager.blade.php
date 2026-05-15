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

{{-- Product Categories ─────────────────────────── --}}
<div class="sm-section">
    <div class="sm-section-head">
        <span class="sm-section-title">Product Categories</span>
        <button class="sm-btn primary" wire:click="$toggle('showCategoryForm')">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Category
        </button>
    </div>

    @error('catDelete') <p class="sm-error" style="margin-bottom:10px">{{ $message }}</p> @enderror

    <div class="sm-card">
        @if($showCategoryForm)
            <div class="sm-form">
                <div class="sm-form-grid cols3" style="margin-bottom:10px">
                    <div>
                        <label class="sm-label">Name *</label>
                        <input class="sm-input" type="text" wire:model="catName" placeholder="e.g. Electronics" />
                        @error('catName') <p class="sm-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="sm-label">Code</label>
                        <input class="sm-input" type="text" wire:model="catCode" placeholder="e.g. ELEC" />
                    </div>
                    <div>
                        <label class="sm-label">Description</label>
                        <input class="sm-input" type="text" wire:model="catDescription" placeholder="Optional" />
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button class="sm-btn primary" wire:click="saveProdCategory">Save Category</button>
                    <button class="sm-btn" wire:click="$set('showCategoryForm',false)">Cancel</button>
                </div>
            </div>
        @endif

        @if($prodCategories->isEmpty())
            <div class="sm-empty">No product categories yet. Add one above.</div>
        @else
            <div class="sm-table-wrap">
            <table class="sm-table">
                <thead><tr>
                    <th>Name</th><th>Code</th><th>Status</th><th style="text-align:right">Actions</th>
                </tr></thead>
                <tbody>
                @foreach($prodCategories as $cat)
                    @if($catConfirmDelete === $cat->id)
                        <tr class="sm-confirm-row">
                            <td colspan="4">
                                <div class="sm-confirm-inline">
                                    <span style="font-size:12px;color:var(--text-dim)">Delete <strong style="color:var(--text)">{{ $cat->name }}</strong>? This cannot be undone.</span>
                                    <button class="sm-yes" wire:click="deleteProdCategory">Yes, Delete</button>
                                    <button class="sm-no" wire:click="$set('catConfirmDelete',null)">Cancel</button>
                                </div>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td style="font-weight:600;color:var(--text)">{{ $cat->name }}</td>
                            <td style="color:var(--text-dim);font-family:var(--mono);font-size:12px">{{ $cat->code ?? '—' }}</td>
                            <td>
                                @if($cat->is_active)
                                    <span class="sm-badge" style="background:var(--green-dim);color:var(--green)">Active</span>
                                @else
                                    <span class="sm-badge" style="background:var(--surface2);color:var(--text-dim)">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align:right">
                                <div style="display:flex;gap:6px;justify-content:flex-end">
                                    <button class="sm-btn sm" wire:click="toggleProdCategory({{ $cat->id }})">
                                        {{ $cat->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button class="sm-btn sm danger" wire:click="confirmDeleteProdCategory({{ $cat->id }})">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>

{{-- Expense Categories ─────────────────────────── --}}
<div class="sm-section">
    <div class="sm-section-head">
        <span class="sm-section-title">Expense Categories</span>
        <button class="sm-btn primary" wire:click="$toggle('showExpCatForm')">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Category
        </button>
    </div>

    @error('expCatDelete') <p class="sm-error" style="margin-bottom:10px">{{ $message }}</p> @enderror

    <div class="sm-card">
        @if($showExpCatForm)
            <div class="sm-form">
                <div class="sm-form-grid cols3" style="margin-bottom:10px">
                    <div>
                        <label class="sm-label">Name *</label>
                        <input class="sm-input" type="text" wire:model="expCatName" placeholder="e.g. Utilities" />
                        @error('expCatName') <p class="sm-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="sm-label">Applies To</label>
                        <select class="sm-select" wire:model="expCatAppliesTo">
                            <option value="shop">Shops only</option>
                            <option value="warehouse">Warehouses only</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    <div>
                        <label class="sm-label">Description</label>
                        <input class="sm-input" type="text" wire:model="expCatDescription" placeholder="Optional" />
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button class="sm-btn primary" wire:click="saveExpenseCategory">Save Category</button>
                    <button class="sm-btn" wire:click="$set('showExpCatForm',false)">Cancel</button>
                </div>
            </div>
        @endif

        @if($expCategories->isEmpty())
            <div class="sm-empty">No expense categories yet.</div>
        @else
            <div class="sm-table-wrap">
            <table class="sm-table">
                <thead><tr>
                    <th>Name</th><th>Applies To</th><th>Status</th><th style="text-align:right">Actions</th>
                </tr></thead>
                <tbody>
                @foreach($expCategories as $cat)
                    @if($expCatConfirmDelete === $cat->id)
                        <tr class="sm-confirm-row">
                            <td colspan="4">
                                <div class="sm-confirm-inline">
                                    <span style="font-size:12px;color:var(--text-dim)">Delete <strong style="color:var(--text)">{{ $cat->name }}</strong>?</span>
                                    <button class="sm-yes" wire:click="deleteExpenseCategory">Yes, Delete</button>
                                    <button class="sm-no" wire:click="$set('expCatConfirmDelete',null)">Cancel</button>
                                </div>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td>
                                <span style="font-weight:600;color:var(--text)">{{ $cat->name }}</span>
                                @if($cat->name === 'Cash Shortage')
                                    <span class="sm-badge" style="background:var(--surface2);color:var(--text-dim);margin-left:6px">System</span>
                                @endif
                            </td>
                            <td>
                                <span class="sm-badge" style="background:var(--accent-dim);color:var(--accent)">
                                    {{ ucfirst($cat->applies_to) }}
                                </span>
                            </td>
                            <td>
                                @if($cat->is_active)
                                    <span class="sm-badge" style="background:var(--green-dim);color:var(--green)">Active</span>
                                @else
                                    <span class="sm-badge" style="background:var(--surface2);color:var(--text-dim)">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align:right">
                                @if($cat->name !== 'Cash Shortage')
                                    <div style="display:flex;gap:6px;justify-content:flex-end">
                                        <button class="sm-btn sm" wire:click="toggleExpenseCategory({{ $cat->id }})">
                                            {{ $cat->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        <button class="sm-btn sm danger" wire:click="confirmDeleteExpCat({{ $cat->id }})">Delete</button>
                                    </div>
                                @else
                                    <span style="font-size:11px;color:var(--text-dim)">Protected</span>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>

{{-- Transporters ───────────────────────────────── --}}
<div class="sm-section">
    <div class="sm-section-head">
        <span class="sm-section-title">Transporters</span>
        <button class="sm-btn primary" wire:click="$toggle('showTransporterForm')">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Transporter
        </button>
    </div>

    @error('trDelete') <p class="sm-error" style="margin-bottom:10px">{{ $message }}</p> @enderror

    <div class="sm-card">
        @if($showTransporterForm)
            <div class="sm-form">
                <div class="sm-form-grid cols2" style="margin-bottom:10px">
                    <div>
                        <label class="sm-label">Full Name *</label>
                        <input class="sm-input" type="text" wire:model="trName" placeholder="Driver name" />
                        @error('trName') <p class="sm-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="sm-label">Phone</label>
                        <input class="sm-input" type="text" wire:model="trPhone" placeholder="Phone number" />
                    </div>
                    <div>
                        <label class="sm-label">Company</label>
                        <input class="sm-input" type="text" wire:model="trCompany" placeholder="Company name (optional)" />
                    </div>
                    <div>
                        <label class="sm-label">Vehicle / Plate</label>
                        <input class="sm-input" type="text" wire:model="trVehicle" placeholder="Vehicle number (optional)" />
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button class="sm-btn primary" wire:click="saveTransporter">Save Transporter</button>
                    <button class="sm-btn" wire:click="$set('showTransporterForm',false)">Cancel</button>
                </div>
            </div>
        @endif

        @if($transporters->isEmpty())
            <div class="sm-empty">No transporters yet. Add one above.</div>
        @else
            <div class="sm-table-wrap">
            <table class="sm-table">
                <thead><tr>
                    <th>Name</th><th>Company</th><th>Phone</th><th>Vehicle</th><th style="text-align:right">Actions</th>
                </tr></thead>
                <tbody>
                @foreach($transporters as $tr)
                    @if($trConfirmDelete === $tr->id)
                        <tr class="sm-confirm-row">
                            <td colspan="5">
                                <div class="sm-confirm-inline">
                                    <span style="font-size:12px;color:var(--text-dim)">Delete <strong style="color:var(--text)">{{ $tr->name }}</strong>?</span>
                                    <button class="sm-yes" wire:click="deleteTransporter">Yes, Delete</button>
                                    <button class="sm-no" wire:click="$set('trConfirmDelete',null)">Cancel</button>
                                </div>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td style="font-weight:600;color:var(--text)">{{ $tr->name }}</td>
                            <td style="color:var(--text-dim)">{{ $tr->company_name ?? '—' }}</td>
                            <td style="font-family:var(--mono);font-size:12px;color:var(--text-dim)">{{ $tr->phone ?? '—' }}</td>
                            <td style="font-family:var(--mono);font-size:12px;color:var(--text-dim)">{{ $tr->vehicle_number ?? '—' }}</td>
                            <td style="text-align:right">
                                <button class="sm-btn sm danger" wire:click="confirmDeleteTransporter({{ $tr->id }})">Delete</button>
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
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
