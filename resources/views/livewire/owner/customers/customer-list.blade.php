<div style="font-family:var(--font)" x-data="{ drawerOpen: @entangle('showDrawer') }">
<style>
.cu-page   { padding:0 0 80px; }

/* ── KPI bar ─────────────────────────────────────── */
.cu-kpis      { display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:22px;max-width:480px }
.cu-kpi       { background:var(--surface);border:none;border-radius:var(--r);
                box-shadow:var(--shadow-card);padding:22px 20px;
                display:flex;flex-direction:column;gap:16px;transition:box-shadow var(--tr) }
.cu-kpi:hover { box-shadow:var(--shadow-card-hover) }
.cu-kpi-row   { display:flex;align-items:center;gap:12px }
.cu-kpi-icon  { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;
                justify-content:center;flex-shrink:0 }
.cu-kpi-body  { flex:1;min-width:0 }
.cu-kpi-label { font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim);line-height:1.2 }
.cu-kpi-val   { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;color:var(--text);line-height:1;flex-shrink:0 }
.cu-kpi-sub   { font-size:12px;color:var(--text-dim);margin-top:2px }

.cu-header { display:flex;align-items:flex-start;justify-content:space-between;
             gap:16px;margin-bottom:22px;flex-wrap:wrap; }
.cu-header-title { font-size:26px;font-weight:800;color:var(--text);letter-spacing:-.4px;margin:0 0 3px; }
.cu-header-sub   { font-size:14px;color:var(--text-dim);margin:0; }

.cu-btn-new { display:flex;align-items:center;gap:7px;padding:9px 18px;background:var(--accent);color:#fff;
              border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);
              box-shadow:0 3px 10px rgba(59,111,212,.25);transition:opacity var(--tr);white-space:nowrap }
.cu-btn-new:hover { opacity:.88; }

.cu-bar         { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px }
.cu-search-wrap { flex:1;min-width:220px;position:relative }
.cu-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);
                  width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.cu-search      { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);
                  border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);
                  outline:none;box-sizing:border-box;font-family:var(--font);
                  transition:border-color var(--tr) }
.cu-search:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }

.cu-toggle-btn  { padding:9px 14px;border-radius:10px;font-size:12px;font-weight:600;
                  cursor:pointer;font-family:var(--font);border:1.5px solid var(--border);
                  background:var(--surface);color:var(--text-dim);transition:all var(--tr);
                  white-space:nowrap; }
.cu-toggle-btn.active { background:var(--amber-dim);border-color:var(--amber);color:var(--amber); }

.cu-table-wrap { background:var(--surface);border:none;border-radius:var(--r);
                 box-shadow:var(--shadow-card); }
.cu-table { width:100%;border-collapse:collapse; }
.cu-table thead tr { border-bottom:2px solid var(--border); }
.cu-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;
                     letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);
                     white-space:nowrap; }
.cu-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr); }
.cu-table tbody tr:last-child { border-bottom:none; }
.cu-table tbody tr:hover  { background:var(--surface2); }
.cu-table td { padding:13px 16px;font-size:13px;vertical-align:middle; }

.cu-action { padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);
             background:transparent;font-size:12px;font-weight:600;cursor:pointer;
             font-family:var(--font);color:var(--text-sub);transition:all var(--tr);
             white-space:nowrap; }
.cu-action:hover { border-color:var(--accent);color:var(--accent) }

.cu-badge     { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;
                padding:3px 9px;border-radius:6px;white-space:nowrap }
.cu-badge-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0 }

.cu-empty       { padding:60px 20px;text-align:center; }
.cu-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px; }
.cu-empty-sub   { font-size:13px;color:var(--text-dim); }

@media(max-width:768px) {
    .cu-hide-mob { display:none !important }
    .cu-kpis { grid-template-columns:1fr 1fr; max-width:100%; }
}

/* ── Drawer ──────────────────────────────────────── */
.cu-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px); }
.cu-drawer  { position:fixed;top:0;right:0;bottom:0;z-index:401;
              width:460px;max-width:100vw;background:var(--surface);
              border-left:1px solid var(--border);
              box-shadow:-8px 0 40px rgba(26,31,54,.14);
              display:flex;flex-direction:column;
              transform:translateX(100%);transition:transform .22s cubic-bezier(.4,0,.2,1) }
.cu-drawer.open { transform:translateX(0) }
.cu-drawer-head  { display:flex;align-items:center;justify-content:space-between;
                   padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0 }
.cu-drawer-title { font-size:16px;font-weight:800;color:var(--text) }
.cu-drawer-close { width:32px;height:32px;border-radius:8px;border:none;
                   background:var(--surface2);color:var(--text-sub);cursor:pointer;
                   display:flex;align-items:center;justify-content:center;transition:background var(--tr) }
.cu-drawer-close:hover { background:var(--surface3) }
.cu-drawer-body { flex:1;overflow-y:auto;padding:22px }
.cu-drawer-foot { padding:16px 22px;border-top:1px solid var(--border);display:flex;gap:10px;flex-shrink:0 }

.cu-field     { margin-bottom:18px }
.cu-label     { display:block;font-size:12px;font-weight:700;color:var(--text-sub);
                margin-bottom:6px;letter-spacing:.3px }
.cu-label span { color:var(--red) }
.cu-input     { width:100%;padding:10px 12px;border:1.5px solid var(--border);
                border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);
                outline:none;box-sizing:border-box;font-family:var(--font);
                transition:border-color var(--tr) }
.cu-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.cu-error { font-size:11px;color:var(--red);margin-top:4px }
.cu-hint  { font-size:11px;color:var(--text-dim);margin-top:4px;line-height:1.5 }

.cu-save-btn    { flex:1;padding:12px;background:var(--accent);color:#fff;border:none;
                  border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;
                  font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);
                  transition:opacity var(--tr) }
.cu-save-btn:hover    { opacity:.88 }
.cu-save-btn:disabled { opacity:.5;cursor:not-allowed }
.cu-cancel-btn  { padding:12px 20px;background:transparent;border:1.5px solid var(--border);
                  color:var(--text-sub);border-radius:10px;font-size:14px;font-weight:600;
                  cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.cu-cancel-btn:hover { border-color:var(--border-hi);color:var(--text) }

.cu-credit-box { background:var(--surface2);border-radius:10px;border:1px solid var(--border);padding:14px;margin-bottom:18px }
.cu-credit-row { display:flex;justify-content:space-between;align-items:center;padding:5px 0;font-size:12px }
.cu-credit-label { color:var(--text-dim) }
.cu-credit-val    { font-family:var(--mono);font-weight:700;color:var(--text) }

@media(max-width:768px) {
    .cu-drawer { width:100vw }
    .cu-drawer-body { padding:16px }
    .cu-drawer-foot { flex-direction:column }
}
@keyframes cu-spin { to { transform:rotate(360deg) } }
</style>

<div class="cu-page">
    <div class="cu-header">
        <div>
            <h1 class="cu-header-title">Customers</h1>
            <p class="cu-header-sub">Register and manage customer records</p>
        </div>
        <button wire:click="openCreate" class="cu-btn-new">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
            Register Customer
        </button>
    </div>

    <div class="cu-kpis">
        <div class="cu-kpi">
            <div class="cu-kpi-row">
                <div class="cu-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <div class="cu-kpi-body">
                    <div class="cu-kpi-label">Total Customers</div>
                    <div class="cu-kpi-sub">registered</div>
                </div>
                <div class="cu-kpi-val" style="color:var(--accent)">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="cu-kpi">
            <div class="cu-kpi-row">
                <div class="cu-kpi-icon" style="background:var(--amber-dim);color:var(--amber)">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <div class="cu-kpi-body">
                    <div class="cu-kpi-label">With Balance</div>
                    <div class="cu-kpi-sub">owe credit</div>
                </div>
                <div class="cu-kpi-val" style="color:var(--amber)">{{ $stats['outstanding'] }}</div>
            </div>
        </div>
    </div>

    <div class="cu-bar">
        <div class="cu-search-wrap">
            <svg class="cu-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.3-4.3"/>
            </svg>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Search by name or phone…" class="cu-search">
        </div>
        <button type="button" wire:click="$set('outstandingOnly', {{ $outstandingOnly ? 'false' : 'true' }})"
                class="cu-toggle-btn {{ $outstandingOnly ? 'active' : '' }}">
            Has outstanding balance
        </button>
        @if($search || $outstandingOnly)
            <button type="button" wire:click="clearFilters" class="cu-toggle-btn">Clear</button>
        @endif
    </div>

    <div class="cu-table-wrap">
        @if($customers->count() > 0)
            <div style="overflow-x:auto">
                <table class="cu-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th class="cu-hide-mob">Email</th>
                            <th class="cu-hide-mob">Shop</th>
                            <th style="text-align:right">Outstanding</th>
                            <th class="cu-hide-mob">Registered By</th>
                            <th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr wire:key="customer-{{ $customer->id }}">
                                <td style="font-weight:600;color:var(--text)">{{ $customer->name }}</td>
                                <td style="font-family:var(--mono)">{{ $customer->phone }}</td>
                                <td class="cu-hide-mob" style="color:var(--text-dim)">{{ $customer->email ?: '—' }}</td>
                                <td class="cu-hide-mob" style="color:var(--text-dim)">{{ $customer->shop?->name ?? 'Unassigned' }}</td>
                                <td style="text-align:right;white-space:nowrap">
                                    @if($customer->outstanding_balance > 0)
                                        <span class="cu-badge" style="background:var(--amber-dim);color:var(--amber)">
                                            <span class="cu-badge-dot" style="background:var(--amber)"></span>
                                            {{ number_format($customer->outstanding_balance) }} RWF
                                        </span>
                                    @else
                                        <span style="color:var(--text-dim);font-family:var(--mono)">0 RWF</span>
                                    @endif
                                </td>
                                <td class="cu-hide-mob" style="color:var(--text-dim)">{{ $customer->registeredBy?->name ?? '—' }}</td>
                                <td style="text-align:right">
                                    <button wire:click="openEdit({{ $customer->id }})" class="cu-action">Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:14px 16px;border-top:1px solid var(--border)">
                {{ $customers->links() }}
            </div>
        @else
            <div class="cu-empty">
                <div class="cu-empty-title">No customers found</div>
                <div class="cu-empty-sub">
                    @if($search || $outstandingOnly)
                        Try adjusting your search or filters.
                    @else
                        Register the first customer to get started.
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════
     DRAWER OVERLAY + PANEL
══════════════════════════════════════════════ --}}
@if($showDrawer)
<div class="cu-overlay" wire:click="closeDrawer"></div>
@endif

<div class="cu-drawer {{ $showDrawer ? 'open' : '' }}">
    <div class="cu-drawer-head">
        <div class="cu-drawer-title">{{ $isEditing ? 'Edit Customer' : 'Register Customer' }}</div>
        <button wire:click="closeDrawer" class="cu-drawer-close">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="cu-drawer-body">

        @if($isEditing)
            @php($editingCustomer = \App\Models\Customer::find($editingId))
            @if($editingCustomer)
            <div class="cu-credit-box">
                <div class="cu-credit-row">
                    <span class="cu-credit-label">Outstanding Balance</span>
                    <span class="cu-credit-val" style="color:{{ $editingCustomer->outstanding_balance > 0 ? 'var(--amber)' : 'var(--text)' }}">
                        {{ number_format($editingCustomer->outstanding_balance) }} RWF
                    </span>
                </div>
                <div class="cu-credit-row">
                    <span class="cu-credit-label">Total Credit Given</span>
                    <span class="cu-credit-val">{{ number_format($editingCustomer->total_credit_given) }} RWF</span>
                </div>
                <div class="cu-credit-row">
                    <span class="cu-credit-label">Registered By</span>
                    <span class="cu-credit-val" style="font-family:var(--font);font-weight:600">{{ $editingCustomer->registeredBy?->name ?? '—' }}</span>
                </div>
            </div>
            @endif
        @endif

        <div class="cu-field">
            <label class="cu-label">Full Name <span>*</span></label>
            <input type="text" wire:model="form_name" class="cu-input" placeholder="e.g. Jean Mugisha" autocomplete="off">
            @error('form_name') <div class="cu-error">{{ $message }}</div> @enderror
        </div>

        <div class="cu-field">
            <label class="cu-label">Phone Number <span>*</span></label>
            <input type="text" wire:model="form_phone" class="cu-input" placeholder="+250788123456" autocomplete="off">
            @error('form_phone') <div class="cu-error">{{ $message }}</div> @enderror
        </div>

        <div class="cu-field">
            <label class="cu-label">Email</label>
            <input type="email" wire:model="form_email" class="cu-input" placeholder="optional" autocomplete="off">
            @error('form_email') <div class="cu-error">{{ $message }}</div> @enderror
        </div>

        <div class="cu-field">
            <label class="cu-label">Associated Shop</label>
            <select wire:model="form_shop_id" class="cu-input">
                <option value="">— Not shop-specific —</option>
                @foreach($this->shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
            <div class="cu-hint">Optional — leave unset if this customer isn't tied to one shop.</div>
            @error('form_shop_id') <div class="cu-error">{{ $message }}</div> @enderror
        </div>

        <div class="cu-field">
            <label class="cu-label">Notes</label>
            <textarea wire:model="form_notes" class="cu-input" rows="3" placeholder="optional"></textarea>
            @error('form_notes') <div class="cu-error">{{ $message }}</div> @enderror
        </div>

    </div>

    <div class="cu-drawer-foot">
        <button wire:click="closeDrawer" class="cu-cancel-btn">Cancel</button>
        <button wire:click="save" wire:loading.attr="disabled" wire:target="save" class="cu-save-btn">
            <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Save Changes' : 'Register Customer' }}</span>
            <span wire:loading wire:target="save" style="display:none;align-items:center;gap:8px;justify-content:center">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                     style="animation:cu-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                Saving…
            </span>
        </button>
    </div>
</div>

</div>
