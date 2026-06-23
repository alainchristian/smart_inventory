<div style="font-family:var(--font)">
<style>
/* ── KPI strip ───────────────────────────────────────── */
.em-kpis       { display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:24px }
.em-kpi        { background:var(--surface);border:none;border-radius:var(--r);
                 box-shadow:var(--shadow-card);
                 padding:22px 20px;display:flex;flex-direction:column;gap:16px;
                 transition:box-shadow var(--tr) }
.em-kpi:hover  { box-shadow:var(--shadow-card-hover) }
.em-kpi-row    { display:flex;align-items:center;gap:12px }
.em-kpi-icon   { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;
                 justify-content:center;flex-shrink:0 }
.em-kpi-body   { flex:1;min-width:0 }
.em-kpi-label  { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                 color:var(--text-dim);line-height:1.2 }
.em-kpi-val    { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;
                 line-height:1;flex-shrink:0 }

/* ── Filter bar ──────────────────────────────────────── */
.em-bar          { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px }
.em-search-wrap  { flex:1;min-width:200px;position:relative }
.em-search-icon  { position:absolute;left:11px;top:50%;transform:translateY(-50%);
                   width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.em-search       { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);
                   border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);
                   outline:none;box-sizing:border-box;font-family:var(--font);
                   transition:border-color var(--tr) }
.em-search:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.em-select       { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;
                   font-size:13px;background:var(--surface);color:var(--text);outline:none;
                   cursor:pointer;font-family:var(--font) }
.em-btn-new      { display:flex;align-items:center;gap:7px;padding:9px 18px;background:var(--accent);
                   color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;
                   cursor:pointer;font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);
                   transition:opacity var(--tr);white-space:nowrap }
.em-btn-new:hover { opacity:.88 }

/* ── Table ───────────────────────────────────────────── */
.em-table-wrap { background:var(--surface);border:none;border-radius:var(--r);overflow-x:auto;box-shadow:var(--shadow-card) }
.em-table      { width:100%;border-collapse:collapse;min-width:700px }
.em-table thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.em-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;
                     letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.em-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.em-table tbody tr:last-child { border-bottom:none }
.em-table tbody tr:hover { background:var(--surface2) }
.em-table tbody tr.inactive { opacity:.5 }
.em-table td  { padding:13px 16px;font-size:13px;vertical-align:middle }

/* Status badge */
.em-badge     { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;
                padding:3px 9px;border-radius:6px;white-space:nowrap }
.em-badge-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0 }

/* Row action buttons */
.em-action        { padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);
                    background:transparent;font-size:12px;font-weight:600;cursor:pointer;
                    font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap }
.em-action:hover  { border-color:var(--accent);color:var(--accent) }
.em-action.danger:hover  { border-color:var(--amber);color:var(--amber) }
.em-action.restore:hover { border-color:var(--green);color:var(--green) }
.em-action[disabled] { opacity:.4;pointer-events:none }

/* Inline confirm row */
.em-confirm-row  { background:rgba(217,119,6,.05) !important }
.em-confirm-wrap { display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap }
.em-confirm-warn { font-size:12px;color:var(--amber);margin-top:4px;font-weight:500 }
.em-confirm-yes  { padding:6px 16px;background:var(--amber);color:#fff;border:none;
                   border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font) }
.em-confirm-no   { padding:6px 14px;background:transparent;border:1.5px solid var(--border);
                   color:var(--text-sub);border-radius:8px;font-size:12px;font-weight:600;
                   cursor:pointer;font-family:var(--font) }

/* Empty state */
.em-empty       { padding:60px 20px;text-align:center }
.em-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.em-empty-sub   { font-size:13px;color:var(--text-dim) }

/* ── Drawer ──────────────────────────────────────────── */
.em-overlay     { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);
                  backdrop-filter:blur(2px) }
.em-drawer      { position:fixed;top:0;right:0;bottom:0;z-index:401;
                  width:500px;max-width:100vw;background:var(--surface);
                  border-left:1px solid var(--border);
                  box-shadow:-8px 0 40px rgba(26,31,54,.14);
                  display:flex;flex-direction:column;
                  transform:translateX(100%);
                  transition:transform .22s cubic-bezier(.4,0,.2,1) }
.em-drawer.open { transform:translateX(0) }
.em-drawer-head { display:flex;align-items:center;justify-content:space-between;
                  padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0 }
.em-drawer-title { font-size:16px;font-weight:800;color:var(--text) }
.em-drawer-close { width:32px;height:32px;border-radius:8px;border:none;
                   background:var(--surface2);color:var(--text-sub);cursor:pointer;
                   display:flex;align-items:center;justify-content:center;
                   transition:background var(--tr) }
.em-drawer-close:hover { background:var(--surface3) }
.em-drawer-body { flex:1;overflow-y:auto;padding:22px }
.em-drawer-foot { padding:16px 22px;border-top:1px solid var(--border);
                  display:flex;gap:10px;flex-shrink:0 }

/* Drawer form */
.em-field       { margin-bottom:18px }
.em-label       { display:block;font-size:12px;font-weight:700;color:var(--text-sub);
                  margin-bottom:6px;letter-spacing:.3px }
.em-label span  { color:var(--red) }
.em-input       { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;
                  font-size:14px;background:var(--surface);color:var(--text);outline:none;
                  box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.em-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.em-error       { font-size:11px;color:var(--red);margin-top:4px }

/* Toggle */
.em-toggle-row    { display:flex;align-items:center;justify-content:space-between;
                    padding:14px;background:var(--surface2);border-radius:10px;
                    border:1px solid var(--border) }
.em-toggle        { position:relative;width:42px;height:23px;flex-shrink:0;cursor:pointer }
.em-toggle input  { position:absolute;opacity:0;width:0;height:0 }
.em-toggle-track  { position:absolute;inset:0;border-radius:23px;background:var(--surface3);
                    border:1.5px solid var(--border);
                    transition:background var(--tr),border-color var(--tr) }
.em-toggle input:checked ~ .em-toggle-track { background:var(--green);border-color:var(--green) }
.em-toggle-knob   { position:absolute;top:2.5px;left:2.5px;width:18px;height:18px;border-radius:50%;
                    background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.18);
                    transition:transform var(--tr);pointer-events:none }
.em-toggle input:checked ~ .em-toggle-track .em-toggle-knob { transform:translateX(19px) }

/* Applies to Segmented Control */
.em-segment-wrap { display:flex;background:var(--surface2);border:1.5px solid var(--border);border-radius:9px;overflow:hidden;padding:2px }
.em-segment      { flex:1;text-align:center;padding:8px;font-size:13px;font-weight:600;color:var(--text-dim);cursor:pointer;border-radius:7px;transition:all .15s;user-select:none }
.em-segment.active { background:var(--surface);color:var(--text);box-shadow:0 1px 3px rgba(0,0,0,.08) }

.em-hide-mob { display:none !important }
.em-hide-sm  { display:none !important }

@media(max-width:768px) {
    .em-kpis { grid-template-columns:1fr 1fr;gap:8px }
    .em-kpi  { padding:12px 14px }
    .em-kpi-val { font-size:20px }
    .em-bar { flex-direction:column;align-items:stretch }
    .em-btn-new, .em-select { width:100%;justify-content:center }
    .em-drawer { width:100vw }
    .em-drawer-body { padding:16px }
    .em-drawer-foot { flex-direction:column }
    .em-table td, .em-table th { padding:10px 10px }
    .em-hide-mob { display:none !important }
}

@media(max-width:480px) {
    .em-kpis { grid-template-columns:1fr }
    .em-hide-sm { display:none !important }
    .em-action { padding:4px 8px;font-size:11px }
}
</style>

{{-- KPIs --}}
<div class="em-kpis">
    <div class="em-kpi">
        <div class="em-kpi-row">
            <div class="em-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <div class="em-kpi-body">
                <div class="em-kpi-label">Expense Categories</div>
            </div>
            <div class="em-kpi-val">{{ number_format($stats['total']) }}</div>
        </div>
    </div>
    <div class="em-kpi">
        <div class="em-kpi-row">
            <div class="em-kpi-icon" style="background:rgba(16,185,129,.15);color:var(--green)">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="em-kpi-body">
                <div class="em-kpi-label">Active Categories</div>
            </div>
            <div class="em-kpi-val">{{ number_format($stats['active']) }}</div>
        </div>
    </div>
</div>

{{-- Bar --}}
<div class="em-bar">
    <div class="em-search-wrap">
        <svg class="em-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" wire:model.live.debounce.300ms="search" class="em-search" placeholder="Search expense categories...">
    </div>
    
    <select wire:model.live="statusFilter" class="em-select">
        <option value="all">All Statuses</option>
        <option value="active">Active Only</option>
        <option value="inactive">Inactive Only</option>
    </select>

    <button class="em-btn-new" wire:click="openCreate">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Category
    </button>
</div>

{{-- Table --}}
<div class="em-table-wrap">
    <table class="em-table">
        <thead>
            <tr>
                <th>Category Name</th>
                <th class="em-hide-mob">Applies To</th>
                <th class="em-hide-sm">Expenses Recorded</th>
                <th>Status</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @if($confirmToggleId === $row->id || $confirmDeleteId === $row->id)
                    <tr class="em-confirm-row">
                        <td colspan="5">
                            <div class="em-confirm-wrap">
                                <div style="flex:1">
                                    <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:2px">
                                        @if($confirmDeleteId)
                                            Delete {{ $row->name }}?
                                        @else
                                            {{ $row->is_active ? 'Deactivate' : 'Activate' }} {{ $row->name }}?
                                        @endif
                                    </div>
                                    @if($confirmDeleteId)
                                        <div class="em-confirm-warn">This action cannot be undone.</div>
                                    @else
                                        @if($row->is_active)
                                            <div class="em-confirm-warn">This category will no longer be available for new expense entries.</div>
                                        @endif
                                    @endif
                                </div>
                                <div style="display:flex;gap:8px">
                                    @if($confirmDeleteId)
                                        <button class="em-confirm-yes" wire:click="deleteCategory">Yes, delete</button>
                                        <button class="em-confirm-no" wire:click="cancelDelete">Cancel</button>
                                    @else
                                        <button class="em-confirm-yes" wire:click="executeToggle">Yes, {{ $row->is_active ? 'deactivate' : 'activate' }}</button>
                                        <button class="em-confirm-no" wire:click="cancelToggle">Cancel</button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @else
                    <tr class="{{ $row->is_active ? '' : 'inactive' }}">
                        <td>
                            <div style="font-weight:600;color:var(--text)">
                                {{ $row->name }}
                                @if($row->name === 'Cash Shortage')
                                    <span style="background:var(--surface2);padding:2px 6px;border-radius:4px;font-size:10px;color:var(--text-dim);margin-left:6px;vertical-align:middle;text-transform:uppercase;font-weight:700">System</span>
                                @endif
                            </div>
                            @if($row->description)
                                <div style="font-size:11.5px;color:var(--text-dim);margin-top:3px;max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $row->description }}</div>
                            @endif
                        </td>
                        <td class="em-hide-mob">
                            @if($row->applies_to === 'both')
                                <span style="font-size:12px;color:var(--text-sub)">Shops & Warehouses</span>
                            @elseif($row->applies_to === 'shop')
                                <span style="font-size:12px;color:var(--text-sub)">Shops Only</span>
                            @elseif($row->applies_to === 'warehouse')
                                <span style="font-size:12px;color:var(--text-sub)">Warehouses Only</span>
                            @endif
                        </td>
                        <td class="em-hide-sm">
                            <div style="font-weight:600;color:var(--text-sub)">{{ number_format($row->expenses_count) }}</div>
                        </td>
                        <td>
                            @if($row->is_active)
                                <div class="em-badge" style="background:rgba(16,185,129,.15);color:var(--green)">
                                    <div class="em-badge-dot" style="background:var(--green)"></div>
                                    Active
                                </div>
                            @else
                                <div class="em-badge" style="background:var(--surface3);color:var(--text-dim)">
                                    <div class="em-badge-dot" style="background:var(--text-dim)"></div>
                                    Inactive
                                </div>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <button class="em-action" wire:click="openEdit({{ $row->id }})">Edit</button>
                                
                                @if($row->name !== 'Cash Shortage')
                                    @if($row->is_active)
                                        <button class="em-action danger" wire:click="confirmToggle({{ $row->id }})">Deactivate</button>
                                    @else
                                        <button class="em-action restore" wire:click="confirmToggle({{ $row->id }})">Activate</button>
                                    @endif
                                    <button class="em-action danger" wire:click="confirmDelete({{ $row->id }})">Delete</button>
                                @else
                                    <button class="em-action" disabled>Deactivate</button>
                                    <button class="em-action" disabled>Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="5">
                        <div class="em-empty">
                            <div class="em-empty-title">No expense categories found</div>
                            <div class="em-empty-sub">Get started by creating your first expense category.</div>
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
    <div class="em-overlay" wire:click="closeDrawer"></div>
    <div class="em-drawer open">
        <div class="em-drawer-head">
            <div class="em-drawer-title">{{ $isEditing ? 'Edit Expense Category' : 'New Expense Category' }}</div>
            <button class="em-drawer-close" wire:click="closeDrawer">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="em-drawer-body">
            <div class="em-field">
                <label class="em-label">Category Name <span>*</span></label>
                <input type="text" wire:model="form_name" class="em-input" placeholder="e.g. Office Supplies">
                @error('form_name') <div class="em-error">{{ $message }}</div> @enderror
            </div>

            <div class="em-field">
                <label class="em-label">Description</label>
                <textarea wire:model="form_description" class="em-input" style="resize:vertical;min-height:80px" placeholder="Details about this expense type..."></textarea>
                @error('form_description') <div class="em-error">{{ $message }}</div> @enderror
            </div>

            <div class="em-field">
                <label class="em-label">Applies To Location Type <span>*</span></label>
                <div class="em-segment-wrap">
                    <div class="em-segment {{ $form_applies_to === 'shop' ? 'active' : '' }}" wire:click="$set('form_applies_to', 'shop')">Shops Only</div>
                    <div class="em-segment {{ $form_applies_to === 'warehouse' ? 'active' : '' }}" wire:click="$set('form_applies_to', 'warehouse')">Warehouses Only</div>
                    <div class="em-segment {{ $form_applies_to === 'both' ? 'active' : '' }}" wire:click="$set('form_applies_to', 'both')">Both</div>
                </div>
                @error('form_applies_to') <div class="em-error">{{ $message }}</div> @enderror
            </div>

            @if($form_name !== 'Cash Shortage')
            <div class="em-toggle-row">
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text)">Active Status</div>
                    <div style="font-size:11.5px;color:var(--text-dim);margin-top:2px">Allow this category to be selected</div>
                </div>
                <label class="em-toggle">
                    <input type="checkbox" wire:model="form_is_active">
                    <div class="em-toggle-track"><div class="em-toggle-knob"></div></div>
                </label>
            </div>
            @endif
        </div>

        <div class="em-drawer-foot">
            <button class="em-btn-new" style="background:var(--surface2);color:var(--text);flex:1;box-shadow:none;border:1px solid var(--border);justify-content:center" wire:click="closeDrawer">Cancel</button>
            <button class="em-btn-new" style="flex:1;justify-content:center" wire:click="save">
                {{ $isEditing ? 'Save Changes' : 'Create Category' }}
            </button>
        </div>
    </div>
@endif

</div>
