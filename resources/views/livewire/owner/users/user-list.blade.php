<div style="font-family:var(--font)" x-data="{ drawerOpen: @entangle('showDrawer') }">
<style>
/* ── KPI bar ─────────────────────────────────────── */
.um-kpis { display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:22px }
.um-kpi  { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:14px 18px }
.um-kpi-label { font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim);margin-bottom:5px }
.um-kpi-val   { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;color:var(--text);line-height:1 }
.um-kpi-sub   { font-size:12px;color:var(--text-dim);margin-top:3px }

/* ── Controls ────────────────────────────────────── */
.um-bar { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:18px }
.um-search-wrap { flex:1;min-width:200px;position:relative }
.um-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.um-search { width:100%;padding:9px 11px 9px 33px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.um-search:focus { border-color:var(--accent) }
.um-select { padding:8px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;cursor:pointer;font-family:var(--font) }
.um-btn-new { display:flex;align-items:center;gap:7px;padding:9px 18px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);transition:opacity var(--tr);white-space:nowrap }
.um-btn-new:hover { opacity:.88 }

/* ── Table ───────────────────────────────────────── */
.um-table-wrap { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden }
.um-table { width:100%;border-collapse:collapse;font-size:13px }
.um-table thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.um-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.um-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.um-table tbody tr:last-child { border-bottom:none }
.um-table tbody tr:hover { background:var(--surface2) }
.um-table tbody tr.inactive { opacity:.55 }
.um-table td { padding:12px 16px;vertical-align:middle;font-size:14px }

/* Avatar */
.um-avatar { width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0 }

/* Role badge */
.um-role { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap }

/* Status dot */
.um-dot { width:7px;height:7px;border-radius:50%;flex-shrink:0 }

/* Action buttons */
.um-action { padding:5px 10px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap }
.um-action:hover { border-color:var(--accent);color:var(--accent) }
.um-action.danger:hover { border-color:var(--amber);color:var(--amber) }
.um-action.activate:hover { border-color:var(--green);color:var(--green) }

/* Confirm inline row */
.um-confirm-row { background:rgba(217,119,6,.05) !important }
.um-confirm-box { display:flex;align-items:center;gap:10px;flex-wrap:wrap }
.um-confirm-text { font-size:12px;color:var(--text-sub) }
.um-confirm-yes { padding:5px 14px;background:var(--amber);color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font) }
.um-confirm-no  { padding:5px 12px;background:transparent;border:1.5px solid var(--border);color:var(--text-sub);border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;font-family:var(--font) }

/* Empty state */
.um-empty { padding:60px 20px;text-align:center }
.um-empty-icon  { font-size:36px;margin-bottom:10px }
.um-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.um-empty-sub   { font-size:13px;color:var(--text-dim) }

/* ── Drawer overlay ──────────────────────────────── */
.um-overlay {
    position:fixed;inset:0;z-index:400;
    background:rgba(26,31,54,.45);
    backdrop-filter:blur(2px);
    transition:opacity .2s;
}
.um-drawer {
    position:fixed;top:0;right:0;bottom:0;z-index:401;
    width:480px;max-width:100vw;
    background:var(--surface);
    border-left:1px solid var(--border);
    box-shadow:-8px 0 40px rgba(26,31,54,.14);
    display:flex;flex-direction:column;
    transform:translateX(100%);
    transition:transform .22s cubic-bezier(.4,0,.2,1);
}
.um-drawer.open { transform:translateX(0) }

.um-drawer-head {
    display:flex;align-items:center;justify-content:space-between;
    padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0;
}
.um-drawer-title { font-size:18px;font-weight:800;color:var(--text) }
.um-drawer-close {
    width:32px;height:32px;border-radius:8px;border:none;
    background:var(--surface2);color:var(--text-sub);cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    transition:background var(--tr);
}
.um-drawer-close:hover { background:var(--surface3);color:var(--text) }

.um-drawer-body { flex:1;overflow-y:auto;padding:22px }
.um-drawer-foot { padding:16px 22px;border-top:1px solid var(--border);display:flex;gap:10px;flex-shrink:0 }

/* Form elements */
.um-field { margin-bottom:18px }
.um-field-label { display:block;font-size:13px;font-weight:600;color:var(--text-sub);margin-bottom:6px }
.um-field-label span { color:var(--red) }
.um-field-input {
    width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;
    font-size:14px;background:var(--surface);color:var(--text);outline:none;
    box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr);
}
.um-field-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.um-field-error { font-size:11px;color:var(--red);margin-top:4px }
.um-field-hint  { font-size:11px;color:var(--text-dim);margin-top:4px;line-height:1.5 }

/* Role cards */
.um-role-cards { display:flex;flex-direction:column;gap:8px }
.um-role-card {
    display:flex;align-items:flex-start;gap:12px;padding:12px 14px;
    border:2px solid var(--border);border-radius:10px;cursor:pointer;
    transition:all var(--tr);
}
.um-role-card:hover  { border-color:var(--border-hi);background:var(--surface2) }
.um-role-card.active { border-color:var(--accent);background:var(--accent-dim) }
.um-role-radio { width:16px;height:16px;border-radius:50%;border:2px solid var(--border);flex-shrink:0;margin-top:2px;display:flex;align-items:center;justify-content:center;transition:all var(--tr) }
.um-role-card.active .um-role-radio { border-color:var(--accent);background:var(--accent) }
.um-role-radio-dot { width:6px;height:6px;border-radius:50%;background:#fff }
.um-role-name { font-size:14px;font-weight:700;color:var(--text);margin-bottom:2px }
.um-role-desc { font-size:12px;color:var(--text-dim);line-height:1.5 }

/* Save button */
.um-save-btn {
    flex:1;padding:11px;background:var(--accent);color:#fff;border:none;
    border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;
    font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);
    transition:opacity var(--tr);
}
.um-save-btn:hover    { opacity:.88 }
.um-save-btn:disabled { opacity:.5;cursor:not-allowed }
.um-cancel-btn {
    padding:11px 20px;background:transparent;border:1.5px solid var(--border);
    color:var(--text-sub);border-radius:10px;font-size:14px;font-weight:600;
    cursor:pointer;font-family:var(--font);transition:all var(--tr);
}
.um-cancel-btn:hover { border-color:var(--border-hi);color:var(--text) }

/* Toggle switch */
.um-toggle { position:relative;width:42px;height:23px;flex-shrink:0;cursor:pointer }
.um-toggle input { position:absolute;opacity:0;width:0;height:0 }
.um-toggle-track { position:absolute;inset:0;border-radius:23px;background:var(--surface3);border:1.5px solid var(--border);transition:background var(--tr),border-color var(--tr) }
.um-toggle input:checked ~ .um-toggle-track { background:var(--green);border-color:var(--green) }
.um-toggle-knob { position:absolute;top:2.5px;left:2.5px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.18);transition:transform var(--tr);pointer-events:none }
.um-toggle input:checked ~ .um-toggle-track .um-toggle-knob { transform:translateX(19px) }

/* Password field */
.um-pw-wrap { position:relative }
.um-pw-toggle { position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-dim);padding:4px }

/* Mobile */
@media(max-width:640px) {
    .um-kpis { grid-template-columns:repeat(2,1fr);gap:8px }
    .um-kpi  { padding:10px 12px }
    .um-kpi-val { font-size:20px }
    .um-hide-mob { display:none !important }
    .um-bar  { flex-direction:column;align-items:stretch }
    .um-select { width:100% }
    .um-table td,.um-table th { padding:10px 12px }
    .um-drawer { width:100vw;max-width:100vw }
    .um-drawer-body { padding:16px }
    .um-drawer-foot { padding:12px 16px;flex-direction:column }
    .um-save-btn   { width:100%;padding:13px }
    .um-cancel-btn { width:100%;padding:13px;text-align:center }
    .um-role-card  { padding:10px 12px }
    .um-confirm-box { flex-direction:column;align-items:flex-start;gap:8px }
    .um-btn-new { width:100%;justify-content:center }
    .um-show-mob { display:block !important }
}

@keyframes um-spin { to { transform:rotate(360deg) } }
</style>

{{-- ── Page header ─────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;
            gap:12px;margin-bottom:22px;flex-wrap:wrap">
    <div>
        <div style="font-size:26px;font-weight:800;color:var(--text);letter-spacing:-.4px">
            Team Members
        </div>
        <div style="font-size:14px;color:var(--text-dim);margin-top:3px">
            Manage who has access to Smart Inventory and what they can do
        </div>
    </div>
    <button wire:click="openCreate" class="um-btn-new">
        <svg width="14" height="14" fill="none" stroke="currentColor"
             stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New User
    </button>
</div>

{{-- ── KPI bar ──────────────────────────────────────────────────────── --}}
<div class="um-kpis">
    <div class="um-kpi">
        <div class="um-kpi-label">Total Users</div>
        <div class="um-kpi-val" style="color:var(--accent)">{{ $stats['total'] }}</div>
        <div class="um-kpi-sub">all roles</div>
    </div>
    <div class="um-kpi">
        <div class="um-kpi-label">Active</div>
        <div class="um-kpi-val" style="color:var(--green)">{{ $stats['active'] }}</div>
        <div class="um-kpi-sub">can log in</div>
    </div>
    <div class="um-kpi">
        <div class="um-kpi-label">Owners</div>
        <div class="um-kpi-val">{{ $stats['owners'] }}</div>
        <div class="um-kpi-sub">full access</div>
    </div>
    <div class="um-kpi">
        <div class="um-kpi-label">Warehouse</div>
        <div class="um-kpi-val">{{ $stats['warehouse'] }}</div>
        <div class="um-kpi-sub">managers</div>
    </div>
    <div class="um-kpi">
        <div class="um-kpi-label">Shop</div>
        <div class="um-kpi-val">{{ $stats['shop'] }}</div>
        <div class="um-kpi-sub">managers</div>
    </div>
</div>

{{-- ── Controls ─────────────────────────────────────────────────────── --}}
<div class="um-bar">
    <div class="um-search-wrap">
        <svg class="um-search-icon" fill="none" stroke="currentColor"
             stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input wire:model.live.debounce.300ms="search"
               class="um-search" type="text"
               placeholder="Search by name, email, or phone…">
    </div>
    <select wire:model.live="roleFilter" class="um-select">
        <option value="all">All Roles</option>
        <option value="owner">Owner</option>
        <option value="warehouse_manager">Warehouse Manager</option>
        <option value="shop_manager">Shop Manager</option>
    </select>
    <select wire:model.live="statusFilter" class="um-select">
        <option value="all">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>
</div>

{{-- ── Table ────────────────────────────────────────────────────────── --}}
<div class="um-table-wrap">
    <div style="overflow-x:auto">
    <table class="um-table">
        <thead>
            <tr>
                <th style="width:42px"></th>
                <th>Name</th>
                <th class="um-hide-mob">Email</th>
                <th>Role</th>
                <th class="um-hide-mob">Location</th>
                <th class="um-hide-mob">Last Login</th>
                <th style="width:8px">Status</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            @php
                $roleColor = match($user->role->value) {
                    'owner'             => ['bg'=>'var(--accent-dim)', 'color'=>'var(--accent)'],
                    'warehouse_manager' => ['bg'=>'var(--green-dim)',  'color'=>'var(--green)'],
                    'shop_manager'      => ['bg'=>'var(--violet-dim)', 'color'=>'var(--violet)'],
                    default             => ['bg'=>'var(--surface2)',   'color'=>'var(--text-sub)'],
                };
                $avatarBg = match($user->role->value) {
                    'owner'             => 'var(--accent)',
                    'warehouse_manager' => 'var(--green)',
                    'shop_manager'      => 'var(--violet)',
                    default             => 'var(--text-dim)',
                };
                $isMe = $user->id === auth()->id();
            @endphp

            {{-- Normal row --}}
            @if($confirmToggleId !== $user->id)
            <tr class="{{ !$user->is_active ? 'inactive' : '' }}">
                <td style="padding-left:16px">
                    <div class="um-avatar" style="background:{{ $avatarBg }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </td>
                <td>
                    <div style="font-weight:600;color:var(--text);font-size:13px">
                        {{ $user->name }}
                        @if($isMe)
                            <span style="font-size:10px;font-weight:700;padding:1px 6px;
                                         border-radius:20px;background:var(--accent-dim);
                                         color:var(--accent);margin-left:4px">You</span>
                        @endif
                    </div>
                    <div class="um-hide-mob" style="font-size:11px;color:var(--text-dim);
                                font-family:var(--mono);margin-top:1px">
                        {{ $user->phone ?? '—' }}
                    </div>
                    <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);
                                margin-top:2px;display:none" class="um-show-mob">
                        {{ $user->email }}
                    </div>
                </td>
                <td class="um-hide-mob" style="color:var(--text-sub);font-size:12px;
                            font-family:var(--mono)">
                    {{ $user->email }}
                </td>
                <td>
                    <span class="um-role"
                          style="background:{{ $roleColor['bg'] }};color:{{ $roleColor['color'] }}">
                        {{ $user->role->label() }}
                    </span>
                </td>
                <td class="um-hide-mob" style="font-size:12px;color:var(--text-sub)">
                    @if($user->location)
                        <div style="font-weight:600">{{ $user->location->name }}</div>
                        <div style="font-size:10px;color:var(--text-dim);margin-top:1px;text-transform:capitalize">
                            {{ $user->location_type?->value ?? '' }}
                        </div>
                    @elseif($user->isOwner())
                        <span style="color:var(--text-dim);font-size:11px">All locations</span>
                    @else
                        <span style="color:var(--amber);font-size:11px">⚠ Unassigned</span>
                    @endif
                </td>
                <td class="um-hide-mob" style="font-size:11px;color:var(--text-dim);
                            font-family:var(--mono)">
                    {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                </td>
                <td>
                    <div class="um-dot"
                         style="background:{{ $user->is_active ? 'var(--green)' : 'var(--text-dim)' }}">
                    </div>
                </td>
                <td style="text-align:right">
                    <div style="display:flex;gap:6px;justify-content:flex-end">
                        <button wire:click="openEdit({{ $user->id }})"
                                class="um-action">
                            Edit
                        </button>
                        @if(!$isMe)
                        <button wire:click="confirmToggle({{ $user->id }})"
                                class="um-action {{ $user->is_active ? 'danger' : 'activate' }}">
                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- Inline confirmation row --}}
            @else
            <tr class="um-confirm-row">
                <td colspan="8" style="padding:14px 16px">
                    <div class="um-confirm-box">
                        <span style="font-size:18px">
                            {{ $confirmToggleActive ? '⚠️' : '✅' }}
                        </span>
                        <span class="um-confirm-text">
                            @if($confirmToggleActive)
                                Deactivate <strong>{{ $confirmToggleName }}</strong>?
                                They will immediately lose access to the system.
                            @else
                                Reactivate <strong>{{ $confirmToggleName }}</strong>?
                                They will be able to log in again.
                            @endif
                        </span>
                        <button wire:click="executeToggle"
                                class="um-confirm-yes">
                            {{ $confirmToggleActive ? 'Yes, deactivate' : 'Yes, activate' }}
                        </button>
                        <button wire:click="cancelToggle"
                                class="um-confirm-no">Cancel</button>
                    </div>
                </td>
            </tr>
            @endif

            @empty
            <tr>
                <td colspan="8">
                    <div class="um-empty">
                        <div class="um-empty-icon">👥</div>
                        <div class="um-empty-title">
                            @if($search)
                                No users match "{{ $search }}"
                            @else
                                No users found
                            @endif
                        </div>
                        <div class="um-empty-sub">
                            @if(!$search)
                                Click "New User" to add your first team member.
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div style="padding:12px 16px;border-top:1px solid var(--border)">
        {{ $users->links() }}
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════
     DRAWER OVERLAY + PANEL
══════════════════════════════════════════════ --}}
@if($showDrawer)
<div class="um-overlay" wire:click="closeDrawer"></div>
@endif

<div class="um-drawer {{ $showDrawer ? 'open' : '' }}">
    {{-- Header --}}
    <div class="um-drawer-head">
        <div class="um-drawer-title">
            {{ $isEditing ? 'Edit User' : 'New User' }}
        </div>
        <button wire:click="closeDrawer" class="um-drawer-close">
            <svg width="16" height="16" fill="none" stroke="currentColor"
                 stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    {{-- Body --}}
    <div class="um-drawer-body">

        {{-- Name --}}
        <div class="um-field">
            <label class="um-field-label">Full Name <span>*</span></label>
            <input wire:model="form_name" type="text"
                   class="um-field-input" placeholder="e.g. Alice Uwimana"
                   autocomplete="off">
            @error('form_name')
                <div class="um-field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="um-field">
            <label class="um-field-label">Email Address <span>*</span></label>
            <input wire:model.blur="form_email" type="email"
                   class="um-field-input" placeholder="alice@business.com"
                   autocomplete="off">
            @error('form_email')
                <div class="um-field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Phone --}}
        <div class="um-field">
            <label class="um-field-label">Phone</label>
            <input wire:model="form_phone" type="text"
                   class="um-field-input" placeholder="+250 788 000 000">
        </div>

        {{-- Password --}}
        <div class="um-field">
            <label class="um-field-label">
                Password
                @if(!$isEditing) <span>*</span> @endif
            </label>
            <div class="um-pw-wrap">
                <input wire:model="form_password"
                       type="{{ $form_show_password ? 'text' : 'password' }}"
                       class="um-field-input"
                       style="padding-right:38px"
                       placeholder="{{ $isEditing ? 'Leave blank to keep current' : 'Min. 8 characters' }}"
                       autocomplete="new-password">
                <button type="button"
                        wire:click="$toggle('form_show_password')"
                        class="um-pw-toggle">
                    @if($form_show_password)
                        <svg width="15" height="15" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    @else
                        <svg width="15" height="15" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    @endif
                </button>
            </div>
            @error('form_password')
                <div class="um-field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Divider --}}
        <div style="border-top:1px solid var(--border);margin:20px 0"></div>

        {{-- Role --}}
        <div class="um-field">
            <label class="um-field-label">Role <span>*</span></label>
            <div class="um-role-cards">

                <div wire:click="$set('form_role','owner')"
                     class="um-role-card {{ $form_role === 'owner' ? 'active' : '' }}">
                    <div class="um-role-radio">
                        @if($form_role === 'owner')
                            <div class="um-role-radio-dot"></div>
                        @endif
                    </div>
                    <div>
                        <div class="um-role-name" style="color:var(--accent)">
                            👑 Owner
                        </div>
                        <div class="um-role-desc">
                            Full system access. Can view purchase prices, manage
                            all users, configure settings, and access all reports.
                            No location binding required.
                        </div>
                    </div>
                </div>

                <div wire:click="$set('form_role','warehouse_manager')"
                     class="um-role-card {{ $form_role === 'warehouse_manager' ? 'active' : '' }}">
                    <div class="um-role-radio">
                        @if($form_role === 'warehouse_manager')
                            <div class="um-role-radio-dot"></div>
                        @endif
                    </div>
                    <div>
                        <div class="um-role-name" style="color:var(--green)">
                            🏭 Warehouse Manager
                        </div>
                        <div class="um-role-desc">
                            Manages warehouse inventory. Can receive boxes, approve
                            and pack transfers. Cannot see purchase prices or reports.
                        </div>
                    </div>
                </div>

                <div wire:click="$set('form_role','shop_manager')"
                     class="um-role-card {{ $form_role === 'shop_manager' ? 'active' : '' }}">
                    <div class="um-role-radio">
                        @if($form_role === 'shop_manager')
                            <div class="um-role-radio-dot"></div>
                        @endif
                    </div>
                    <div>
                        <div class="um-role-name" style="color:var(--violet)">
                            🏪 Shop Manager
                        </div>
                        <div class="um-role-desc">
                            Operates the point of sale. Can request transfers,
                            process sales and returns. Access limited to assigned shop.
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Location (hidden for owner) --}}
        @if($form_role !== 'owner')
        <div class="um-field">
            <label class="um-field-label">
                Assigned {{ $form_role === 'warehouse_manager' ? 'Warehouse' : 'Shop' }}
                <span>*</span>
            </label>
            @if($form_role === 'warehouse_manager')
                <select wire:model="form_location_id" class="um-field-input">
                    <option value="">— Select a warehouse —</option>
                    @foreach($this->warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            @else
                <select wire:model="form_location_id" class="um-field-input">
                    <option value="">— Select a shop —</option>
                    @foreach($this->shops as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
            @endif
            @error('form_location_id')
                <div class="um-field-error">{{ $message }}</div>
            @enderror
            <div class="um-field-hint">
                This user will only see data for the assigned location.
            </div>
        </div>
        @endif

        {{-- Active toggle --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:14px;background:var(--surface2);border-radius:10px;
                    border:1px solid var(--border)">
            <div>
                <div style="font-size:13px;font-weight:600;color:var(--text)">
                    Account Active
                </div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                    Inactive users cannot log in to the system
                </div>
            </div>
            <label class="um-toggle">
                <input type="checkbox" wire:model="form_is_active">
                <div class="um-toggle-track">
                    <div class="um-toggle-knob"></div>
                </div>
            </label>
        </div>
        @error('form_is_active')
            <div class="um-field-error" style="margin-top:4px">{{ $message }}</div>
        @enderror

    </div>{{-- /drawer-body --}}

    {{-- Footer --}}
    <div class="um-drawer-foot">
        <button wire:click="closeDrawer" class="um-cancel-btn">Cancel</button>
        <button wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="um-save-btn">
            <span wire:loading.remove wire:target="save">
                {{ $isEditing ? 'Save Changes' : 'Create User' }}
            </span>
            <span wire:loading wire:target="save"
                  style="display:none;align-items:center;gap:8px;justify-content:center">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     style="animation:um-spin 1s linear infinite;flex-shrink:0">
                    <path d="M21 12a9 9 0 11-6.219-8.56"/>
                </svg>
                Saving…
            </span>
        </button>
    </div>
</div>

</div>
