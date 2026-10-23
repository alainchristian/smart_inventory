<div style="font-family:var(--font)" x-data="{ drawerOpen: @entangle('showDrawer') }">
<style>
.sp-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap }
.sp-title { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px }
.sp-sub { font-size:13px;color:var(--text-dim);margin:0 }
.sp-note { display:flex;gap:10px;align-items:flex-start;padding:12px 16px;margin-bottom:16px;border-radius:var(--r);box-shadow:var(--shadow-card);border-left:3px solid var(--amber);font-size:12px;color:var(--text-sub);line-height:1.5 }

.sp-bar { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px }
.sp-search-wrap { flex:1;min-width:200px;position:relative }
.sp-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.sp-search { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.sp-search:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.sp-select { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;cursor:pointer;font-family:var(--font) }

.sp-btn-primary { display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:var(--accent);color:#fff;border:none;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);transition:opacity var(--tr);white-space:nowrap }
.sp-btn-primary:hover { opacity:.88 }
.sp-action { padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;cursor:pointer;font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap }
a.sp-action { display:inline-block;text-decoration:none }
.sp-action:hover { border-color:var(--accent);color:var(--accent) }
.sp-action.danger:hover { border-color:var(--amber);color:var(--amber) }
.sp-action.restore:hover { border-color:var(--green);color:var(--green) }

.sp-card { background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card) }
.sp-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch }
.sp-table { width:100%;border-collapse:collapse;min-width:820px }
.sp-table thead tr { border-bottom:2px solid var(--border) }
.sp-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.sp-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.sp-table tbody tr:last-child { border-bottom:none }
.sp-table tbody tr:hover { background:var(--surface2) }
.sp-table tbody tr.inactive { opacity:.55 }
.sp-table td { padding:12px 16px;font-size:13px;vertical-align:middle;white-space:nowrap;color:var(--text-sub) }
.sp-name { font-weight:700;color:var(--text) }
.sp-meta { font-size:12px;color:var(--text-dim) }
.sp-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap }
.sp-badge-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0 }
.sp-confirm-row { background:var(--amber-dim) }
.sp-confirm-wrap { display:flex;align-items:center;gap:12px;flex-wrap:wrap;font-size:13px;color:var(--text) }
.sp-confirm-yes { padding:6px 16px;background:var(--amber);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font) }
.sp-confirm-no { padding:6px 14px;background:transparent;border:1.5px solid var(--border);color:var(--text-sub);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;font-family:var(--font) }
.sp-empty { padding:60px 20px;text-align:center }
.sp-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.sp-empty-sub { font-size:13px;color:var(--text-dim) }

.sp-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px) }
.sp-drawer { position:fixed;top:0;right:0;bottom:0;z-index:401;width:460px;max-width:100vw;background:var(--surface);border-left:1px solid var(--border);box-shadow:-8px 0 40px rgba(26,31,54,.14);display:flex;flex-direction:column;transform:translateX(100%);visibility:hidden;transition:transform .22s cubic-bezier(.4,0,.2,1),visibility .22s }
.sp-drawer.open { transform:translateX(0);visibility:visible;box-shadow:-8px 0 40px rgba(26,31,54,.14) }
.sp-drawer-head { display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0 }
.sp-drawer-title { font-size:16px;font-weight:800;color:var(--text) }
.sp-drawer-close { width:32px;height:32px;border-radius:8px;border:none;background:var(--surface2);color:var(--text-sub);cursor:pointer;display:flex;align-items:center;justify-content:center }
.sp-drawer-body { flex:1;overflow-y:auto;padding:22px }
.sp-drawer-foot { padding:16px 22px;border-top:1px solid var(--border);display:flex;gap:10px;flex-shrink:0 }
.sp-field { margin-bottom:18px }
.sp-label { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px }
.sp-label span { color:var(--red) }
.sp-input { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.sp-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.sp-error { font-size:11px;color:var(--red);margin-top:4px }
.sp-hint { font-size:11px;color:var(--text-dim);margin-top:4px;line-height:1.5 }
.sp-warn { padding:10px 12px;border-radius:9px;background:var(--amber-dim);color:var(--amber);font-size:12px;font-weight:600;margin-bottom:18px;line-height:1.5 }
.sp-save { flex:1;padding:12px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);transition:opacity var(--tr) }
.sp-save:hover { opacity:.88 }
.sp-save:disabled { opacity:.5;cursor:not-allowed }
.sp-cancel { padding:12px 20px;background:transparent;border:1.5px solid var(--border);color:var(--text-sub);border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;font-family:var(--font) }
.sp-cancel:hover { border-color:var(--border-hi);color:var(--text) }

@media(max-width:768px) {
    .sp-drawer { left:0;width:auto }
    .sp-drawer-body { padding:16px }
    .sp-drawer-foot > * { flex:1 }
    input.sp-input, .sp-select, .sp-search { font-size:16px }
}
</style>

@php
    $roleStyle = [
        'owner'             => ['var(--accent-dim)', 'var(--accent)'],
        'admin'             => ['var(--red-dim)', 'var(--red)'],
        'warehouse_manager' => ['var(--green-dim)', 'var(--green)'],
        'shop_manager'      => ['var(--violet-dim)', 'var(--violet)'],
    ];
@endphp

<div class="sp-header m-page-head">
    <div>
        <h1 class="sp-title m-dup-title">Accounts</h1>
        <p class="sp-sub">Every business account, owners included. Platform-support access.</p>
    </div>
    <button type="button" class="sp-btn-primary" wire:click="openCreate">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        New account
    </button>
</div>

<div class="sp-note">
    <svg width="16" height="16" fill="none" stroke="var(--amber)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
    <div>This console changes accounts only. It cannot sell, move stock or touch money. Every change here is recorded in the owner's Activity Logs as <strong>Platform support</strong>.</div>
</div>

<div class="sp-bar">
    <div class="sp-search-wrap">
        <svg class="sp-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
        <input type="text" class="sp-search" wire:model.live.debounce.300ms="search" placeholder="Search name, email or phone…">
    </div>
    <select class="sp-select" wire:model.live="roleFilter">
        <option value="all">All roles</option>
        <option value="owner">Owner</option>
        <option value="admin">Admin</option>
        <option value="warehouse_manager">Warehouse Manager</option>
        <option value="shop_manager">Shop Manager</option>
    </select>
    <select class="sp-select" wire:model.live="statusFilter">
        <option value="all">Any status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>
</div>

<div class="sp-card">
    <div class="sp-scroll m-scroll">
        <table class="sp-table m-sticky-first">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Location</th>
                    <th>Phone</th>
                    <th>Last sign-in</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr wire:key="sp-u-{{ $u->id }}" class="{{ $u->is_active ? '' : 'inactive' }}">
                        <td>
                            <div class="sp-name">{{ $u->name }}</div>
                            <div class="sp-meta">{{ $u->email }}</div>
                        </td>
                        <td>
                            <span class="sp-badge" style="background:{{ $roleStyle[$u->role->value][0] ?? 'var(--surface2)' }};color:{{ $roleStyle[$u->role->value][1] ?? 'var(--text-sub)' }}">{{ $u->role->label() }}</span>
                        </td>
                        <td>{{ $u->location?->name ?? '—' }}</td>
                        <td>{{ $u->phone ?: '—' }}</td>
                        <td>{{ $u->last_login_at ? local_time($u->last_login_at)->format('d M Y H:i') : 'Never' }}</td>
                        <td>
                            @if($u->is_active)
                                <span class="sp-badge" style="background:var(--green-dim);color:var(--green)"><span class="sp-badge-dot" style="background:var(--green)"></span> Active</span>
                            @else
                                <span class="sp-badge" style="background:var(--surface2);color:var(--text-dim)"><span class="sp-badge-dot" style="background:var(--text-dim)"></span> Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            @if($u->is_active)
                                <form method="POST" action="{{ route('support.view-as.start', $u->id) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="sp-action" title="See this account's screens. Nothing can be changed.">View as</button>
                                </form>
                            @endif
                            <a href="{{ route('support.activity-logs', ['filterUser' => $u->id]) }}" wire:navigate class="sp-action" title="Everything {{ $u->name }} has done">Activity</a>
                            <button type="button" class="sp-action" wire:click="openEdit({{ $u->id }})">Edit</button>
                            <button type="button" class="sp-action {{ $u->is_active ? 'danger' : 'restore' }}" wire:click="confirmToggle({{ $u->id }})">{{ $u->is_active ? 'Deactivate' : 'Activate' }}</button>
                        </td>
                    </tr>
                    @if($confirmToggleId === $u->id)
                        <tr wire:key="sp-c-{{ $u->id }}" class="sp-confirm-row">
                            <td colspan="7">
                                <div class="sp-confirm-wrap">
                                    <span>{{ $u->is_active ? 'Deactivate' : 'Activate' }} <strong>{{ $u->name }}</strong>?
                                        @if($u->is_active) They are signed out of new requests straight away. @endif</span>
                                    <button type="button" class="sp-confirm-yes" wire:click="executeToggle">Yes, {{ $u->is_active ? 'deactivate' : 'activate' }}</button>
                                    <button type="button" class="sp-confirm-no" wire:click="cancelToggle">Cancel</button>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="7">
                        <div class="sp-empty">
                            <div class="sp-empty-title">No accounts match</div>
                            <div class="sp-empty-sub">Try a different search or filter.</div>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:16px">{{ $users->links() }}</div>

{{-- Drawer --}}
<div class="sp-overlay" x-show="drawerOpen" x-cloak x-transition.opacity wire:click="closeDrawer"></div>
<div class="sp-drawer {{ $showDrawer ? 'open' : '' }}" role="dialog" aria-modal="true">
    <div class="sp-drawer-head">
        <div class="sp-drawer-title">{{ $editingId ? 'Edit account' : 'New account' }}</div>
        <button type="button" class="sp-drawer-close" wire:click="closeDrawer" aria-label="Close">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>
    <div class="sp-drawer-body">
        @if($editingId && $form_role === 'owner')
            <div class="sp-warn">You are editing an owner account. Changes to the email or password can lock the owner out, so tell them first.</div>
        @endif

        <div class="sp-field">
            <label class="sp-label">Full name <span>*</span></label>
            <input type="text" class="sp-input" wire:model="form_name">
            @error('form_name')<div class="sp-error">{{ $message }}</div>@enderror
        </div>
        <div class="sp-field">
            <label class="sp-label">Email <span>*</span></label>
            <input type="email" class="sp-input" wire:model="form_email">
            @error('form_email')<div class="sp-error">{{ $message }}</div>@enderror
        </div>
        <div class="sp-field">
            <label class="sp-label">Phone</label>
            <input type="text" class="sp-input" wire:model="form_phone">
            @error('form_phone')<div class="sp-error">{{ $message }}</div>@enderror
        </div>
        <div class="sp-field">
            <label class="sp-label">Role <span>*</span></label>
            <select class="sp-input" wire:model.live="form_role">
                <option value="owner">Owner</option>
                <option value="admin">Admin</option>
                <option value="warehouse_manager">Warehouse Manager</option>
                <option value="shop_manager">Shop Manager</option>
            </select>
            @error('form_role')<div class="sp-error">{{ $message }}</div>@enderror
        </div>

        @if($form_role === 'shop_manager')
            <div class="sp-field">
                <label class="sp-label">Shop <span>*</span></label>
                <select class="sp-input" wire:model="form_location_id">
                    <option value="">Choose a shop…</option>
                    @foreach($this->shops as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
                @error('form_location_id')<div class="sp-error">{{ $message }}</div>@enderror
            </div>
        @elseif($form_role === 'warehouse_manager')
            <div class="sp-field">
                <label class="sp-label">Warehouse <span>*</span></label>
                <select class="sp-input" wire:model="form_location_id">
                    <option value="">Choose a warehouse…</option>
                    @foreach($this->warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                </select>
                @error('form_location_id')<div class="sp-error">{{ $message }}</div>@enderror
            </div>
        @endif

        <div class="sp-field">
            <label class="sp-label">{{ $editingId ? 'Reset password' : 'Temporary password' }} @unless($editingId)<span>*</span>@endunless</label>
            <input type="text" class="sp-input" wire:model="form_password" autocomplete="off" placeholder="{{ $editingId ? 'Leave empty to keep the current one' : 'At least 8 characters' }}">
            <div class="sp-hint">The person must choose a new password the next time they sign in.</div>
            @error('form_password')<div class="sp-error">{{ $message }}</div>@enderror
        </div>

        <div class="sp-field">
            <label class="sp-label">Status</label>
            <select class="sp-input" wire:model="form_is_active">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            @error('form_is_active')<div class="sp-error">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="sp-drawer-foot">
        <button type="button" class="sp-cancel" wire:click="closeDrawer">Cancel</button>
        <button type="button" class="sp-save" wire:click="save" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save changes' : 'Create account' }}</span>
            <span wire:loading wire:target="save" style="display:none">Saving…</span>
        </button>
    </div>
</div>
</div>
