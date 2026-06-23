<div style="font-family:var(--font)">
<style>
/* ── KPI strip ───────────────────────────────────────── */
.cm-kpis       { display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:24px }
.cm-kpi        { background:var(--surface);border:none;border-radius:var(--r);
                 box-shadow:var(--shadow-card);
                 padding:22px 20px;display:flex;flex-direction:column;gap:16px;
                 transition:box-shadow var(--tr) }
.cm-kpi:hover  { box-shadow:var(--shadow-card-hover) }
.cm-kpi-row    { display:flex;align-items:center;gap:12px }
.cm-kpi-icon   { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;
                 justify-content:center;flex-shrink:0 }
.cm-kpi-body   { flex:1;min-width:0 }
.cm-kpi-label  { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                 color:var(--text-dim);line-height:1.2 }
.cm-kpi-val    { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;
                 line-height:1;flex-shrink:0 }

/* ── Filter bar ──────────────────────────────────────── */
.cm-bar          { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px }
.cm-search-wrap  { flex:1;min-width:200px;position:relative }
.cm-search-icon  { position:absolute;left:11px;top:50%;transform:translateY(-50%);
                   width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.cm-search       { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);
                   border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);
                   outline:none;box-sizing:border-box;font-family:var(--font);
                   transition:border-color var(--tr) }
.cm-search:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.cm-select       { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;
                   font-size:13px;background:var(--surface);color:var(--text);outline:none;
                   cursor:pointer;font-family:var(--font) }
.cm-btn-new      { display:flex;align-items:center;gap:7px;padding:9px 18px;background:var(--accent);
                   color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;
                   cursor:pointer;font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);
                   transition:opacity var(--tr);white-space:nowrap }
.cm-btn-new:hover { opacity:.88 }

/* ── Table ───────────────────────────────────────────── */
.cm-table-wrap { background:var(--surface);border:none;border-radius:var(--r);overflow-x:auto;box-shadow:var(--shadow-card) }
.cm-table      { width:100%;border-collapse:collapse;min-width:600px }
.cm-table thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.cm-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;
                     letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.cm-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.cm-table tbody tr:last-child { border-bottom:none }
.cm-table tbody tr:hover { background:var(--surface2) }
.cm-table tbody tr.inactive { opacity:.5 }
.cm-table td  { padding:13px 16px;font-size:13px;vertical-align:middle }

/* Status badge */
.cm-badge     { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;
                padding:3px 9px;border-radius:6px;white-space:nowrap }
.cm-badge-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0 }

/* Row action buttons */
.cm-action        { padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);
                    background:transparent;font-size:12px;font-weight:600;cursor:pointer;
                    font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap }
.cm-action:hover  { border-color:var(--accent);color:var(--accent) }
.cm-action.danger:hover  { border-color:var(--amber);color:var(--amber) }
.cm-action.restore:hover { border-color:var(--green);color:var(--green) }

/* Inline confirm row */
.cm-confirm-row  { background:rgba(217,119,6,.05) !important }
.cm-confirm-wrap { display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap }
.cm-confirm-warn { font-size:12px;color:var(--amber);margin-top:4px;font-weight:500 }
.cm-confirm-yes  { padding:6px 16px;background:var(--amber);color:#fff;border:none;
                   border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font) }
.cm-confirm-no   { padding:6px 14px;background:transparent;border:1.5px solid var(--border);
                   color:var(--text-sub);border-radius:8px;font-size:12px;font-weight:600;
                   cursor:pointer;font-family:var(--font) }

/* Empty state */
.cm-empty       { padding:60px 20px;text-align:center }
.cm-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.cm-empty-sub   { font-size:13px;color:var(--text-dim) }

/* ── Drawer ──────────────────────────────────────────── */
.cm-overlay     { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);
                  backdrop-filter:blur(2px) }
.cm-drawer      { position:fixed;top:0;right:0;bottom:0;z-index:401;
                  width:460px;max-width:100vw;background:var(--surface);
                  border-left:1px solid var(--border);
                  box-shadow:-8px 0 40px rgba(26,31,54,.14);
                  display:flex;flex-direction:column;
                  transform:translateX(100%);
                  transition:transform .22s cubic-bezier(.4,0,.2,1) }
.cm-drawer.open { transform:translateX(0) }
.cm-drawer-head { display:flex;align-items:center;justify-content:space-between;
                  padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0 }
.cm-drawer-title { font-size:16px;font-weight:800;color:var(--text) }
.cm-drawer-close { width:32px;height:32px;border-radius:8px;border:none;
                   background:var(--surface2);color:var(--text-sub);cursor:pointer;
                   display:flex;align-items:center;justify-content:center;
                   transition:background var(--tr) }
.cm-drawer-close:hover { background:var(--surface3) }
.cm-drawer-body { flex:1;overflow-y:auto;padding:22px }
.cm-drawer-foot { padding:16px 22px;border-top:1px solid var(--border);
                  display:flex;gap:10px;flex-shrink:0 }

/* Drawer form */
.cm-field       { margin-bottom:18px }
.cm-label       { display:block;font-size:12px;font-weight:700;color:var(--text-sub);
                  margin-bottom:6px;letter-spacing:.3px }
.cm-label span  { color:var(--red) }
.cm-input       { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;
                  font-size:14px;background:var(--surface);color:var(--text);outline:none;
                  box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.cm-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.cm-error       { font-size:11px;color:var(--red);margin-top:4px }

/* Toggle */
.cm-toggle-row    { display:flex;align-items:center;justify-content:space-between;
                    padding:14px;background:var(--surface2);border-radius:10px;
                    border:1px solid var(--border) }
.cm-toggle        { position:relative;width:42px;height:23px;flex-shrink:0;cursor:pointer }
.cm-toggle input  { position:absolute;opacity:0;width:0;height:0 }
.cm-toggle-track  { position:absolute;inset:0;border-radius:23px;background:var(--surface3);
                    border:1.5px solid var(--border);
                    transition:background var(--tr),border-color var(--tr) }
.cm-toggle input:checked ~ .cm-toggle-track { background:var(--green);border-color:var(--green) }
.cm-toggle-knob   { position:absolute;top:2.5px;left:2.5px;width:18px;height:18px;border-radius:50%;
                    background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.18);
                    transition:transform var(--tr);pointer-events:none }
.cm-toggle input:checked ~ .cm-toggle-track .cm-toggle-knob { transform:translateX(19px) }

.cm-hide-mob { display:none !important }
.cm-hide-sm  { display:none !important }

@media(max-width:768px) {
    .cm-kpis { grid-template-columns:1fr 1fr;gap:8px }
    .cm-kpi  { padding:12px 14px }
    .cm-kpi-val { font-size:20px }
    .cm-bar { flex-direction:column;align-items:stretch }
    .cm-btn-new, .cm-select { width:100%;justify-content:center }
    .cm-drawer { width:100vw }
    .cm-drawer-body { padding:16px }
    .cm-drawer-foot { flex-direction:column }
    .cm-table td, .cm-table th { padding:10px 10px }
    .cm-hide-mob { display:none !important }
}

@media(max-width:480px) {
    .cm-kpis { grid-template-columns:1fr }
    .cm-hide-sm { display:none !important }
    .cm-action { padding:4px 8px;font-size:11px }
}
</style>

{{-- KPIs --}}
<div class="cm-kpis">
    <div class="cm-kpi">
        <div class="cm-kpi-row">
            <div class="cm-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
            <div class="cm-kpi-body">
                <div class="cm-kpi-label">Total Categories</div>
            </div>
            <div class="cm-kpi-val">{{ number_format($stats['total']) }}</div>
        </div>
    </div>
    <div class="cm-kpi">
        <div class="cm-kpi-row">
            <div class="cm-kpi-icon" style="background:rgba(16,185,129,.15);color:var(--green)">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="cm-kpi-body">
                <div class="cm-kpi-label">Active Categories</div>
            </div>
            <div class="cm-kpi-val">{{ number_format($stats['active']) }}</div>
        </div>
    </div>
</div>

{{-- Bar --}}
<div class="cm-bar">
    <div class="cm-search-wrap">
        <svg class="cm-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" wire:model.live.debounce.300ms="search" class="cm-search" placeholder="Search categories...">
    </div>
    
    <select wire:model.live="statusFilter" class="cm-select">
        <option value="all">All Statuses</option>
        <option value="active">Active Only</option>
        <option value="inactive">Inactive Only</option>
    </select>

    <button class="cm-btn-new" wire:click="openCreate">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Category
    </button>
</div>

{{-- Table --}}
<div class="cm-table-wrap">
    <table class="cm-table">
        <thead>
            <tr>
                <th>Name</th>
                <th class="cm-hide-mob">Code</th>
                <th class="cm-hide-sm">Products</th>
                <th>Status</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @if($confirmToggleId === $row->id || $confirmDeleteId === $row->id)
                    <tr class="cm-confirm-row">
                        <td colspan="5">
                            <div class="cm-confirm-wrap">
                                <div style="flex:1">
                                    <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:2px">
                                        @if($confirmDeleteId)
                                            Delete {{ $row->name }}?
                                        @else
                                            {{ $row->is_active ? 'Deactivate' : 'Activate' }} {{ $row->name }}?
                                        @endif
                                    </div>
                                    @if($confirmDeleteId)
                                        <div class="cm-confirm-warn">This action cannot be undone.</div>
                                    @else
                                        @if($row->is_active && $row->products_count > 0)
                                            <div class="cm-confirm-warn">{{ $row->products_count }} product(s) are in this category. Deactivating will hide them from the active category filters.</div>
                                        @endif
                                    @endif
                                </div>
                                <div style="display:flex;gap:8px">
                                    @if($confirmDeleteId)
                                        <button class="cm-confirm-yes" wire:click="deleteCategory">Yes, delete it</button>
                                        <button class="cm-confirm-no" wire:click="cancelDelete">Cancel</button>
                                    @else
                                        <button class="cm-confirm-yes" wire:click="executeToggle">Yes, {{ $row->is_active ? 'deactivate' : 'activate' }}</button>
                                        <button class="cm-confirm-no" wire:click="cancelToggle">Cancel</button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @else
                    <tr class="{{ $row->is_active ? '' : 'inactive' }}">
                        <td>
                            <div style="font-weight:600;color:var(--text)">{{ $row->name }}</div>
                            @if($row->description)
                                <div style="font-size:11.5px;color:var(--text-dim);margin-top:3px;max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $row->description }}</div>
                            @endif
                        </td>
                        <td class="cm-hide-mob">
                            @if($row->code)
                                <span style="font-family:var(--mono);font-size:12px;color:var(--text-sub);background:var(--surface2);padding:2px 6px;border-radius:4px">{{ $row->code }}</span>
                            @else
                                <span style="color:var(--text-dim)">-</span>
                            @endif
                        </td>
                        <td class="cm-hide-sm">
                            <div style="font-weight:600;color:var(--text-sub)">{{ number_format($row->products_count) }}</div>
                        </td>
                        <td>
                            @if($row->is_active)
                                <div class="cm-badge" style="background:rgba(16,185,129,.15);color:var(--green)">
                                    <div class="cm-badge-dot" style="background:var(--green)"></div>
                                    Active
                                </div>
                            @else
                                <div class="cm-badge" style="background:var(--surface3);color:var(--text-dim)">
                                    <div class="cm-badge-dot" style="background:var(--text-dim)"></div>
                                    Inactive
                                </div>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <button class="cm-action" wire:click="openEdit({{ $row->id }})">Edit</button>
                                @if($row->is_active)
                                    <button class="cm-action danger" wire:click="confirmToggle({{ $row->id }})">Deactivate</button>
                                @else
                                    <button class="cm-action restore" wire:click="confirmToggle({{ $row->id }})">Activate</button>
                                @endif
                                <button class="cm-action danger" wire:click="confirmDelete({{ $row->id }})">Delete</button>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="5">
                        <div class="cm-empty">
                            <div class="cm-empty-title">No categories found</div>
                            <div class="cm-empty-sub">Get started by creating your first product category.</div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($rows->hasPages())
    <div style="margin-top:20px">
        {{ $rows->links() }}
    </div>
@endif

{{-- Drawer --}}
@if($showDrawer)
    <div class="cm-overlay" wire:click="closeDrawer"></div>
    <div class="cm-drawer open">
        <div class="cm-drawer-head">
            <div class="cm-drawer-title">{{ $isEditing ? 'Edit Category' : 'New Category' }}</div>
            <button class="cm-drawer-close" wire:click="closeDrawer">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="cm-drawer-body">
            <div class="cm-field">
                <label class="cm-label">Category Name <span>*</span></label>
                <input type="text" wire:model="form_name" class="cm-input" placeholder="e.g. Electronics">
                @error('form_name') <div class="cm-error">{{ $message }}</div> @enderror
            </div>

            <div class="cm-field">
                <label class="cm-label">Short Code</label>
                <input type="text" wire:model="form_code" class="cm-input" placeholder="e.g. ELEC">
                @error('form_code') <div class="cm-error">{{ $message }}</div> @enderror
            </div>

            <div class="cm-field">
                <label class="cm-label">Description</label>
                <textarea wire:model="form_description" class="cm-input" style="resize:vertical;min-height:80px" placeholder="Optional description..."></textarea>
                @error('form_description') <div class="cm-error">{{ $message }}</div> @enderror
            </div>

            <div class="cm-toggle-row">
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text)">Active Status</div>
                    <div style="font-size:11.5px;color:var(--text-dim);margin-top:2px">Allow products to be assigned</div>
                </div>
                <label class="cm-toggle">
                    <input type="checkbox" wire:model="form_is_active">
                    <div class="cm-toggle-track"><div class="cm-toggle-knob"></div></div>
                </label>
            </div>
        </div>

        <div class="cm-drawer-foot">
            <button class="cm-btn-new" style="background:var(--surface2);color:var(--text);flex:1;box-shadow:none;border:1px solid var(--border);justify-content:center" wire:click="closeDrawer">Cancel</button>
            <button class="cm-btn-new" style="flex:1;justify-content:center" wire:click="save">
                {{ $isEditing ? 'Save Changes' : 'Create Category' }}
            </button>
        </div>
    </div>
@endif

</div>
