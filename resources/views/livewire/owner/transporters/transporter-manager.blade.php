<div style="font-family:var(--font)">
<style>
/* ── KPI strip ───────────────────────────────────────── */
.tm-kpis       { display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:24px }
.tm-kpi        { background:var(--surface);border:none;border-radius:var(--r);
                 box-shadow:var(--shadow-card);
                 padding:22px 20px;display:flex;flex-direction:column;gap:16px;
                 transition:box-shadow var(--tr) }
.tm-kpi:hover  { box-shadow:var(--shadow-card-hover) }
.tm-kpi-row    { display:flex;align-items:center;gap:12px }
.tm-kpi-icon   { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;
                 justify-content:center;flex-shrink:0 }
.tm-kpi-body   { flex:1;min-width:0 }
.tm-kpi-label  { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                 color:var(--text-dim);line-height:1.2 }
.tm-kpi-val    { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;
                 line-height:1;flex-shrink:0 }

/* ── Filter bar ──────────────────────────────────────── */
.tm-bar          { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px }
.tm-search-wrap  { flex:1;min-width:200px;position:relative }
.tm-search-icon  { position:absolute;left:11px;top:50%;transform:translateY(-50%);
                   width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.tm-search       { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);
                   border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);
                   outline:none;box-sizing:border-box;font-family:var(--font);
                   transition:border-color var(--tr) }
.tm-search:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.tm-select       { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;
                   font-size:13px;background:var(--surface);color:var(--text);outline:none;
                   cursor:pointer;font-family:var(--font) }
.tm-btn-new      { display:flex;align-items:center;gap:7px;padding:9px 18px;background:var(--accent);
                   color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;
                   cursor:pointer;font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);
                   transition:opacity var(--tr);white-space:nowrap }
.tm-btn-new:hover { opacity:.88 }

/* ── Table ───────────────────────────────────────────── */
.tm-table-wrap { background:var(--surface);border:none;border-radius:var(--r);overflow-x:auto;box-shadow:var(--shadow-card) }
.tm-table      { width:100%;border-collapse:collapse;min-width:700px }
.tm-table thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.tm-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;
                     letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.tm-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.tm-table tbody tr:last-child { border-bottom:none }
.tm-table tbody tr:hover { background:var(--surface2) }
.tm-table tbody tr.inactive { opacity:.5 }
.tm-table td  { padding:13px 16px;font-size:13px;vertical-align:middle }

/* Status badge */
.tm-badge     { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;
                padding:3px 9px;border-radius:6px;white-space:nowrap }
.tm-badge-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0 }

/* Row action buttons */
.tm-action        { padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);
                    background:transparent;font-size:12px;font-weight:600;cursor:pointer;
                    font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap }
.tm-action:hover  { border-color:var(--accent);color:var(--accent) }
.tm-action.danger:hover  { border-color:var(--amber);color:var(--amber) }
.tm-action.restore:hover { border-color:var(--green);color:var(--green) }

/* Inline confirm row */
.tm-confirm-row  { background:rgba(217,119,6,.05) !important }
.tm-confirm-wrap { display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap }
.tm-confirm-warn { font-size:12px;color:var(--amber);margin-top:4px;font-weight:500 }
.tm-confirm-yes  { padding:6px 16px;background:var(--amber);color:#fff;border:none;
                   border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font) }
.tm-confirm-no   { padding:6px 14px;background:transparent;border:1.5px solid var(--border);
                   color:var(--text-sub);border-radius:8px;font-size:12px;font-weight:600;
                   cursor:pointer;font-family:var(--font) }

/* Empty state */
.tm-empty       { padding:60px 20px;text-align:center }
.tm-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.tm-empty-sub   { font-size:13px;color:var(--text-dim) }

/* ── Drawer ──────────────────────────────────────────── */
.tm-overlay     { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);
                  backdrop-filter:blur(2px) }
.tm-drawer      { position:fixed;top:0;right:0;bottom:0;z-index:401;
                  width:500px;max-width:100vw;background:var(--surface);
                  border-left:1px solid var(--border);
                  box-shadow:-8px 0 40px rgba(26,31,54,.14);
                  display:flex;flex-direction:column;
                  transform:translateX(100%);
                  transition:transform .22s cubic-bezier(.4,0,.2,1) }
.tm-drawer.open { transform:translateX(0) }
.tm-drawer-head { display:flex;align-items:center;justify-content:space-between;
                  padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0 }
.tm-drawer-title { font-size:16px;font-weight:800;color:var(--text) }
.tm-drawer-close { width:32px;height:32px;border-radius:8px;border:none;
                   background:var(--surface2);color:var(--text-sub);cursor:pointer;
                   display:flex;align-items:center;justify-content:center;
                   transition:background var(--tr) }
.tm-drawer-close:hover { background:var(--surface3) }
.tm-drawer-body { flex:1;overflow-y:auto;padding:22px }
.tm-drawer-foot { padding:16px 22px;border-top:1px solid var(--border);
                  display:flex;gap:10px;flex-shrink:0 }

/* Drawer form */
.tm-field       { margin-bottom:18px }
.tm-field-row   { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px }
.tm-label       { display:block;font-size:12px;font-weight:700;color:var(--text-sub);
                  margin-bottom:6px;letter-spacing:.3px }
.tm-label span  { color:var(--red) }
.tm-input       { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;
                  font-size:14px;background:var(--surface);color:var(--text);outline:none;
                  box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.tm-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.tm-error       { font-size:11px;color:var(--red);margin-top:4px }

/* Toggle */
.tm-toggle-row    { display:flex;align-items:center;justify-content:space-between;
                    padding:14px;background:var(--surface2);border-radius:10px;
                    border:1px solid var(--border) }
.tm-toggle        { position:relative;width:42px;height:23px;flex-shrink:0;cursor:pointer }
.tm-toggle input  { position:absolute;opacity:0;width:0;height:0 }
.tm-toggle-track  { position:absolute;inset:0;border-radius:23px;background:var(--surface3);
                    border:1.5px solid var(--border);
                    transition:background var(--tr),border-color var(--tr) }
.tm-toggle input:checked ~ .tm-toggle-track { background:var(--green);border-color:var(--green) }
.tm-toggle-knob   { position:absolute;top:2.5px;left:2.5px;width:18px;height:18px;border-radius:50%;
                    background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.18);
                    transition:transform var(--tr);pointer-events:none }
.tm-toggle input:checked ~ .tm-toggle-track .tm-toggle-knob { transform:translateX(19px) }

.tm-hide-mob { display:none !important }
.tm-hide-sm  { display:none !important }

@media(max-width:768px) {
    .tm-kpis { grid-template-columns:1fr 1fr;gap:8px }
    .tm-kpi  { padding:12px 14px }
    .tm-kpi-val { font-size:20px }
    .tm-bar { flex-direction:column;align-items:stretch }
    .tm-btn-new, .tm-select { width:100%;justify-content:center }
    .tm-drawer { width:100vw }
    .tm-drawer-body { padding:16px }
    .tm-drawer-foot { flex-direction:column }
    .tm-table td, .tm-table th { padding:10px 10px }
    .tm-field-row { grid-template-columns:1fr }
    .tm-hide-mob { display:none !important }
}

@media(max-width:480px) {
    .tm-kpis { grid-template-columns:1fr }
    .tm-hide-sm { display:none !important }
    .tm-action { padding:4px 8px;font-size:11px }
}
</style>

{{-- KPIs --}}
<div class="tm-kpis">
    <div class="tm-kpi">
        <div class="tm-kpi-row">
            <div class="tm-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <div class="tm-kpi-body">
                <div class="tm-kpi-label">Total Transporters</div>
            </div>
            <div class="tm-kpi-val">{{ number_format($stats['total']) }}</div>
        </div>
    </div>
    <div class="tm-kpi">
        <div class="tm-kpi-row">
            <div class="tm-kpi-icon" style="background:rgba(16,185,129,.15);color:var(--green)">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="tm-kpi-body">
                <div class="tm-kpi-label">Active Transporters</div>
            </div>
            <div class="tm-kpi-val">{{ number_format($stats['active']) }}</div>
        </div>
    </div>
</div>

{{-- Bar --}}
<div class="tm-bar">
    <div class="tm-search-wrap">
        <svg class="tm-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" wire:model.live.debounce.300ms="search" class="tm-search" placeholder="Search transporters...">
    </div>
    
    <select wire:model.live="statusFilter" class="tm-select">
        <option value="all">All Statuses</option>
        <option value="active">Active Only</option>
        <option value="inactive">Inactive Only</option>
    </select>

    <button class="tm-btn-new" wire:click="openCreate">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Transporter
    </button>
</div>

{{-- Table --}}
<div class="tm-table-wrap">
    <table class="tm-table">
        <thead>
            <tr>
                <th>Driver Name</th>
                <th class="tm-hide-mob">Company</th>
                <th class="tm-hide-sm">Contact & Vehicle</th>
                <th class="tm-hide-mob">Transfers</th>
                <th>Status</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @if($confirmToggleId === $row->id || $confirmDeleteId === $row->id)
                    <tr class="tm-confirm-row">
                        <td colspan="6">
                            <div class="tm-confirm-wrap">
                                <div style="flex:1">
                                    <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:2px">
                                        @if($confirmDeleteId)
                                            Delete {{ $row->name }}?
                                        @else
                                            {{ $row->is_active ? 'Deactivate' : 'Activate' }} {{ $row->name }}?
                                        @endif
                                    </div>
                                    @if($confirmDeleteId)
                                        <div class="tm-confirm-warn">This action cannot be undone.</div>
                                    @else
                                        @if($row->is_active && $row->transfers_count > 0)
                                            <div class="tm-confirm-warn">This transporter has {{ $row->transfers_count }} transfer record(s). They will no longer be selectable for new transfers.</div>
                                        @endif
                                    @endif
                                </div>
                                <div style="display:flex;gap:8px">
                                    @if($confirmDeleteId)
                                        <button class="tm-confirm-yes" wire:click="deleteTransporter">Yes, delete</button>
                                        <button class="tm-confirm-no" wire:click="cancelDelete">Cancel</button>
                                    @else
                                        <button class="tm-confirm-yes" wire:click="executeToggle">Yes, {{ $row->is_active ? 'deactivate' : 'activate' }}</button>
                                        <button class="tm-confirm-no" wire:click="cancelToggle">Cancel</button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @else
                    <tr class="{{ $row->is_active ? '' : 'inactive' }}">
                        <td>
                            <div style="font-weight:600;color:var(--text)">{{ $row->name }}</div>
                            @if($row->notes)
                                <div style="font-size:11.5px;color:var(--text-dim);margin-top:3px;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $row->notes }}</div>
                            @endif
                        </td>
                        <td class="tm-hide-mob">
                            @if($row->company_name)
                                <div style="color:var(--text-sub)">{{ $row->company_name }}</div>
                            @else
                                <span style="color:var(--text-dim)">-</span>
                            @endif
                        </td>
                        <td class="tm-hide-sm">
                            <div style="display:flex;flex-direction:column;gap:3px">
                                @if($row->phone)
                                    <div style="font-size:12px;color:var(--text-sub)">
                                        <span style="color:var(--text-dim);margin-right:4px">Phone:</span>{{ $row->phone }}
                                    </div>
                                @endif
                                @if($row->vehicle_number)
                                    <div style="font-size:12px;color:var(--text-sub)">
                                        <span style="color:var(--text-dim);margin-right:4px">Vehicle:</span>{{ $row->vehicle_number }}
                                    </div>
                                @endif
                                @if(!$row->phone && !$row->vehicle_number)
                                    <span style="color:var(--text-dim)">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="tm-hide-mob">
                            <div style="font-weight:600;color:var(--text-sub)">{{ number_format($row->transfers_count) }}</div>
                        </td>
                        <td>
                            @if($row->is_active)
                                <div class="tm-badge" style="background:rgba(16,185,129,.15);color:var(--green)">
                                    <div class="tm-badge-dot" style="background:var(--green)"></div>
                                    Active
                                </div>
                            @else
                                <div class="tm-badge" style="background:var(--surface3);color:var(--text-dim)">
                                    <div class="tm-badge-dot" style="background:var(--text-dim)"></div>
                                    Inactive
                                </div>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <button class="tm-action" wire:click="openEdit({{ $row->id }})">Edit</button>
                                @if($row->is_active)
                                    <button class="tm-action danger" wire:click="confirmToggle({{ $row->id }})">Deactivate</button>
                                @else
                                    <button class="tm-action restore" wire:click="confirmToggle({{ $row->id }})">Activate</button>
                                @endif
                                <button class="tm-action danger" wire:click="confirmDelete({{ $row->id }})">Delete</button>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6">
                        <div class="tm-empty">
                            <div class="tm-empty-title">No transporters found</div>
                            <div class="tm-empty-sub">Get started by creating your first transporter or driver.</div>
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
    <div class="tm-overlay" wire:click="closeDrawer"></div>
    <div class="tm-drawer open">
        <div class="tm-drawer-head">
            <div class="tm-drawer-title">{{ $isEditing ? 'Edit Transporter' : 'New Transporter' }}</div>
            <button class="tm-drawer-close" wire:click="closeDrawer">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="tm-drawer-body">
            <div class="tm-field">
                <label class="tm-label">Driver / Main Contact Name <span>*</span></label>
                <input type="text" wire:model="form_name" class="tm-input" placeholder="e.g. John Doe">
                @error('form_name') <div class="tm-error">{{ $message }}</div> @enderror
            </div>

            <div class="tm-field">
                <label class="tm-label">Company Name</label>
                <input type="text" wire:model="form_company_name" class="tm-input" placeholder="e.g. Fast Logistics (optional)">
                @error('form_company_name') <div class="tm-error">{{ $message }}</div> @enderror
            </div>

            <div class="tm-field-row">
                <div>
                    <label class="tm-label">Phone Number</label>
                    <input type="text" wire:model="form_phone" class="tm-input" placeholder="Optional...">
                    @error('form_phone') <div class="tm-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="tm-label">Vehicle Number</label>
                    <input type="text" wire:model="form_vehicle_number" class="tm-input" placeholder="e.g. ABC-123">
                    @error('form_vehicle_number') <div class="tm-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="tm-field">
                <label class="tm-label">License Number</label>
                <input type="text" wire:model="form_license_number" class="tm-input" placeholder="Optional...">
                @error('form_license_number') <div class="tm-error">{{ $message }}</div> @enderror
            </div>

            <div class="tm-field">
                <label class="tm-label">Notes</label>
                <textarea wire:model="form_notes" class="tm-input" style="resize:vertical;min-height:80px" placeholder="Additional details..."></textarea>
                @error('form_notes') <div class="tm-error">{{ $message }}</div> @enderror
            </div>

            <div class="tm-toggle-row">
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text)">Active Status</div>
                    <div style="font-size:11.5px;color:var(--text-dim);margin-top:2px">Allow transporter to be selected for transfers</div>
                </div>
                <label class="tm-toggle">
                    <input type="checkbox" wire:model="form_is_active">
                    <div class="tm-toggle-track"><div class="tm-toggle-knob"></div></div>
                </label>
            </div>
        </div>

        <div class="tm-drawer-foot">
            <button class="tm-btn-new" style="background:var(--surface2);color:var(--text);flex:1;box-shadow:none;border:1px solid var(--border);justify-content:center" wire:click="closeDrawer">Cancel</button>
            <button class="tm-btn-new" style="flex:1;justify-content:center" wire:click="save">
                {{ $isEditing ? 'Save Changes' : 'Create Transporter' }}
            </button>
        </div>
    </div>
@endif

</div>
