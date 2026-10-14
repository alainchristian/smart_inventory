<div style="font-family:var(--font)">
<style>
.oc-page   { padding:0 0 80px; }
.oc-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:22px;flex-wrap:wrap; }
.oc-header-title { font-size:26px;font-weight:800;color:var(--text);letter-spacing:-.4px;margin:0 0 3px; }
.oc-header-sub   { font-size:14px;color:var(--text-dim);margin:0;max-width:640px;line-height:1.5; }
.oc-header-actions { display:flex;gap:8px;align-items:center;flex-wrap:wrap; }

.oc-btn         { padding:9px 16px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;
                  font-family:var(--font);transition:all var(--tr);display:inline-flex;align-items:center;gap:7px;white-space:nowrap; }
.oc-btn-primary { background:var(--accent);color:#fff;border:none;box-shadow:0 3px 10px rgba(59,111,212,.25); }
.oc-btn-primary:hover { opacity:.88; }
.oc-btn-ghost   { background:var(--surface);color:var(--text-sub);border:1px solid var(--border); }
.oc-btn-ghost:hover { background:var(--surface2);color:var(--text); }
.oc-btn:disabled { opacity:.5;cursor:not-allowed; }

.oc-kpis { --kpi-cols:3; }

.oc-bar         { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px }
.oc-search-wrap { flex:1;min-width:220px;position:relative }
.oc-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.oc-search      { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;
                  background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.oc-search:focus, .oc-select:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.oc-select      { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);
                  color:var(--text);outline:none;cursor:pointer;font-family:var(--font) }

.oc-card  { background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card); }
.oc-table { width:100%;border-collapse:collapse; }
.oc-table thead tr { border-bottom:2px solid var(--border); }
.oc-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim); }
.oc-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr); }
.oc-table tbody tr:last-child { border-bottom:none; }
.oc-table tbody tr:hover { background:var(--surface2); }
.oc-table tbody tr.oc-removed { opacity:.55; }
.oc-table td { padding:12px 16px;font-size:13px;vertical-align:middle;color:var(--text-sub); }
.oc-table th, .oc-table td { white-space:nowrap; }
.oc-num  { text-align:right;font-family:var(--mono); }
.oc-main { font-weight:600;color:var(--text); }
.oc-sub  { font-size:11px;color:var(--text-dim);margin-top:2px; }

.oc-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap }
.oc-action { padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;
             cursor:pointer;font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap }
.oc-action:hover { border-color:var(--red);color:var(--red) }

.oc-confirm-row  { background:rgba(217,119,6,.05) !important }
.oc-confirm-wrap { display:flex;align-items:flex-start;gap:10px;flex-wrap:wrap;white-space:normal }
.oc-confirm-text { font-size:12px;color:var(--amber);font-weight:600;padding-top:8px }
.oc-confirm-yes  { padding:8px 16px;background:var(--amber);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font) }
.oc-confirm-no   { padding:8px 14px;background:transparent;border:1.5px solid var(--border);color:var(--text-sub);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;font-family:var(--font) }

.oc-empty       { padding:60px 20px;text-align:center; }
.oc-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px; }
.oc-empty-sub   { font-size:13px;color:var(--text-dim);line-height:1.6; }

/* ── Drawers ── */
.oc-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px); }
.oc-drawer  { position:fixed;top:0;right:0;bottom:0;z-index:401;width:480px;max-width:100vw;background:var(--surface);
              border-left:1px solid var(--border);box-shadow:-8px 0 40px rgba(26,31,54,.14);display:flex;flex-direction:column;
              transform:translateX(100%);transition:transform .22s cubic-bezier(.4,0,.2,1) }
.oc-drawer.wide { width:min(980px, 100vw); }
.oc-drawer.open { transform:translateX(0) }
.oc-drawer:not(.open) { box-shadow:none }
.oc-drawer-head  { display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0 }
.oc-drawer-title { font-size:16px;font-weight:800;color:var(--text) }
.oc-drawer-sub   { font-size:12px;color:var(--text-dim);margin-top:2px }
.oc-drawer-close { width:32px;height:32px;border-radius:8px;border:none;background:var(--surface2);color:var(--text-sub);cursor:pointer;
                   display:flex;align-items:center;justify-content:center;transition:background var(--tr) }
.oc-drawer-close:hover { background:var(--surface3) }
.oc-drawer-body { flex:1;overflow-y:auto;padding:22px }
.oc-drawer-foot { padding:16px 22px;border-top:1px solid var(--border);display:flex;gap:10px;flex-shrink:0;align-items:center }

.oc-field     { margin-bottom:18px }
.oc-field-row { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px }
.oc-field-row .oc-field { margin-bottom:0 }
.oc-label     { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px }
.oc-label span { color:var(--red) }
.oc-input     { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);
                color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.oc-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.oc-error { font-size:11px;color:var(--red);margin-top:4px }
.oc-hint  { font-size:11px;color:var(--text-dim);margin-top:4px;line-height:1.5 }
.oc-match { border-left:3px solid var(--green);padding:8px 12px;margin-top:8px;font-size:12px;color:var(--text-sub);line-height:1.5 }
.oc-match.new { border-left-color:var(--accent) }
.oc-note  { border-left:3px solid var(--accent);padding:10px 14px;font-size:12px;color:var(--text-sub);line-height:1.6;margin-bottom:18px }

.oc-save-btn   { flex:1;padding:12px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;
                 cursor:pointer;font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);transition:opacity var(--tr) }
.oc-save-btn:hover { opacity:.88 }
.oc-save-btn:disabled { opacity:.5;cursor:not-allowed }
.oc-cancel-btn { padding:12px 20px;background:transparent;border:1.5px solid var(--border);color:var(--text-sub);border-radius:10px;
                 font-size:14px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.oc-cancel-btn:hover { border-color:var(--border-hi);color:var(--text) }

.oc-steps { display:flex;flex-direction:column;gap:10px;margin-bottom:18px }
.oc-step  { display:flex;gap:12px;align-items:flex-start;font-size:13px;color:var(--text-sub);line-height:1.5 }
.oc-step-n { width:22px;height:22px;border-radius:50%;background:var(--accent-dim);color:var(--accent);font-size:11px;font-weight:800;
             display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.oc-drop { display:block;border:1.5px dashed var(--border-hi);border-radius:var(--r);padding:22px;text-align:center;cursor:pointer;
           color:var(--text-dim);font-size:13px;transition:border-color var(--tr) }
.oc-drop:hover { border-color:var(--accent);color:var(--accent) }
.oc-sum  { display:flex;gap:18px;flex-wrap:wrap;font-size:12px;color:var(--text-dim);margin:16px 0 10px }
.oc-sum b { font-family:var(--mono);color:var(--text);font-size:13px }
.oc-prev-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch;border:1px solid var(--border);border-radius:var(--rsm) }
.oc-prev .oc-err { color:var(--red);font-size:12px;white-space:normal;min-width:220px }

@keyframes oc-spin { to { transform:rotate(360deg) } }

@media(max-width:768px) {
    .oc-drawer, .oc-drawer.wide { left:0;width:auto;max-width:none }
    .oc-drawer-body { padding:16px }
    .oc-cancel-btn, .oc-save-btn { flex:1;text-align:center }
    .oc-field-row { grid-template-columns:1fr }
    .oc-field-row .oc-field { margin-bottom:18px }
    .oc-field-row { margin-bottom:0 }
}
@media(max-width:640px) {
    .oc-header-title { font-size:22px }
    .oc-header-actions { width:100% }
    .oc-header-actions .oc-btn { flex:1;justify-content:center }
    .oc-search-wrap { min-width:0;flex-basis:100% }
    .oc-select { flex:1 }
    .oc-input { font-size:16px }
}
</style>

<div class="oc-page">
    <div class="oc-header m-page-head">
        <div>
            <h1 class="oc-header-title m-dup-title">Old credit</h1>
            <p class="oc-header-sub">What customers owed before the system was in use. Each amount is added to what the customer owes the shop that gave it, and is repaid there like any credit. It doesn't count as sales.</p>
        </div>
        <div class="oc-header-actions">
            <button type="button" wire:click="openImport" class="oc-btn oc-btn-ghost">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12M7 9l5-5 5 5"/></svg>
                Upload Excel
            </button>
            <button type="button" wire:click="openForm" class="oc-btn oc-btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add old credit
            </button>
        </div>
    </div>

    @php($s = $this->stats)
    <div class="ui-kpis oc-kpis m-kpis">
        <div class="ui-kpi">
            <div class="ui-kpi-row">
                <div class="ui-kpi-icon" style="background:var(--amber-dim);color:var(--amber)">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1m9-5a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ui-kpi-body">
                    <div class="ui-kpi-label">Old credit entered</div>
                    <div class="ui-kpi-sub">before the system</div>
                </div>
            </div>
            <div class="ui-kpi-val" style="color:var(--amber)">{{ number_format($s['amount']) }}<span class="ui-kpi-unit">RWF</span></div>
            <div class="ui-kpi-divider"></div>
            <div class="ui-kpi-footer">
                <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ number_format($s['entries']) }}</span><span class="ui-kpi-stat-l">Entries</span></div>
                <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ number_format($s['from_excel']) }}</span><span class="ui-kpi-stat-l">From Excel</span></div>
                <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ number_format($s['shops']) }}</span><span class="ui-kpi-stat-l">Shops</span></div>
            </div>
        </div>
        <div class="ui-kpi">
            <div class="ui-kpi-row">
                <div class="ui-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <div class="ui-kpi-body">
                    <div class="ui-kpi-label">Customers</div>
                    <div class="ui-kpi-sub">with old credit</div>
                </div>
            </div>
            <div class="ui-kpi-val" style="color:var(--accent)">{{ number_format($s['customers']) }}</div>
            <div class="ui-kpi-divider"></div>
            <div class="ui-kpi-footer">
                <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ number_format($s['customers'] - $s['created']) }}</span><span class="ui-kpi-stat-l">Already registered</span></div>
                <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ number_format($s['created']) }}</span><span class="ui-kpi-stat-l">Registered from old credit</span></div>
            </div>
        </div>
        <div class="ui-kpi">
            <div class="ui-kpi-row">
                <div class="ui-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ui-kpi-body">
                    <div class="ui-kpi-label">Still owed</div>
                    <div class="ui-kpi-sub">by these customers, at these shops</div>
                </div>
            </div>
            <div class="ui-kpi-val" style="color:var(--text)">{{ number_format($s['still_owed']) }}<span class="ui-kpi-unit">RWF</span></div>
            <div class="ui-kpi-divider"></div>
            <div class="ui-kpi-footer">
                <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ number_format(max(0, $s['amount'] - $s['still_owed'])) }}</span><span class="ui-kpi-stat-l">Collected or written off</span></div>
                <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ $s['amount'] > 0 ? round(max(0, $s['amount'] - $s['still_owed']) / $s['amount'] * 100) : 0 }}%</span><span class="ui-kpi-stat-l">Cleared</span></div>
            </div>
        </div>
    </div>

    <div class="oc-bar">
        <div class="oc-search-wrap">
            <svg class="oc-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.3-4.3"/></svg>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Search customer name or phone…" class="oc-search">
        </div>
        <select wire:model.live="shopFilter" class="oc-select">
            <option value="">All shops</option>
            @foreach($this->shops as $shop)
                <option value="{{ $shop->id }}">{{ $shop->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="oc-card">
        @if($entries->count())
            <div class="m-scroll">
                <table class="oc-table m-sticky-first">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th style="text-align:right">Amount</th>
                            <th>Shop</th>
                            <th>Taken on</th>
                            <th>Reference / notes</th>
                            <th>Entered</th>
                            <th style="text-align:right"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($entries as $e)
                        <tr wire:key="oc-{{ $e->id }}" class="{{ $e->removed_at ? 'oc-removed' : '' }}">
                            <td>
                                <div class="oc-main">{{ $e->customer?->name ?? '—' }}</div>
                                <div class="oc-sub" style="font-family:var(--mono)">{{ $e->customer?->phone }}
                                    @if($e->customer_created) · <span style="color:var(--accent);font-family:var(--font)">registered here</span>@endif
                                </div>
                            </td>
                            <td class="oc-num" style="font-weight:700;color:var(--text)">
                                @if($e->removed_at)<s>{{ number_format($e->amount) }}</s>@else{{ number_format($e->amount) }}@endif
                                <span style="font-size:10px;color:var(--text-dim)">RWF</span>
                            </td>
                            <td>{{ $e->shop?->name }}</td>
                            <td>{{ $e->taken_on->format('d M Y') }}</td>
                            <td>
                                <div>{{ $e->reference ?: '—' }}</div>
                                @if($e->notes)<div class="oc-sub" title="{{ $e->notes }}">{{ \Illuminate\Support\Str::limit($e->notes, 40) }}</div>@endif
                            </td>
                            <td>
                                <div>{{ local_time($e->created_at)->format('d M Y, H:i') }}</div>
                                <div class="oc-sub">{{ $e->recordedBy?->name ?? '—' }} · {{ $e->source === 'excel' ? 'Excel' : 'typed' }}</div>
                            </td>
                            <td style="text-align:right">
                                @if($e->removed_at)
                                    <span class="oc-badge" style="background:var(--red-dim);color:var(--red)" title="{{ $e->remove_reason }}">Removed {{ local_time($e->removed_at)->format('d M') }}</span>
                                @elseif($removingId !== $e->id)
                                    <button type="button" wire:click="askRemove({{ $e->id }})" class="oc-action">Remove</button>
                                @endif
                            </td>
                        </tr>
                        @if($removingId === $e->id)
                            <tr class="oc-confirm-row" wire:key="oc-confirm-{{ $e->id }}">
                                <td colspan="7">
                                    <div class="oc-confirm-wrap">
                                        <span class="oc-confirm-text">Remove {{ number_format($e->amount) }} RWF from {{ $e->customer?->name }}'s balance?</span>
                                        <div style="flex:1;min-width:220px">
                                            <input type="text" wire:model="removeReason" class="oc-input" placeholder="Why? e.g. typed the wrong amount" style="padding:7px 10px;font-size:13px">
                                            @error('removeReason')<div class="oc-error">{{ $message }}</div>@enderror
                                        </div>
                                        <button type="button" wire:click="remove" wire:loading.attr="disabled" class="oc-confirm-yes">Remove</button>
                                        <button type="button" wire:click="cancelRemove" class="oc-confirm-no">Keep</button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($entries->hasPages())
                <div style="padding:14px 16px;border-top:1px solid var(--border)">{{ $entries->links() }}</div>
            @endif
        @else
            <div class="oc-empty">
                <div class="oc-empty-title">{{ $search || $shopFilter ? 'No entries match' : 'No old credit entered yet' }}</div>
                <div class="oc-empty-sub">
                    @if($search || $shopFilter)
                        Try another name, phone or shop.
                    @else
                        Add debts one by one, or upload them all from Excel.<br>Customers who aren't registered yet are registered with their phone and name.
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ══ Add one ══ --}}
@if($showForm)<div class="oc-overlay" wire:click="closeForm"></div>@endif
<div class="oc-drawer {{ $showForm ? 'open' : '' }}" role="dialog" aria-label="Add old credit">
    <div class="oc-drawer-head">
        <div>
            <div class="oc-drawer-title">Add old credit</div>
            <div class="oc-drawer-sub">A debt from before the system</div>
        </div>
        <button type="button" wire:click="closeForm" class="oc-drawer-close m-tap" aria-label="Close">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="oc-drawer-body">
        @if($showForm)
        <div class="oc-field">
            <label class="oc-label">Customer phone <span>*</span></label>
            <input type="text" wire:model.live.debounce.400ms="phone" class="oc-input" placeholder="07XXXXXXXX" inputmode="tel" autocomplete="off" style="font-family:var(--mono)">
            @error('phone')<div class="oc-error">{{ $message }}</div>@enderror
            @if($m = $this->phoneMatch)
                <div class="oc-match">
                    <strong style="color:var(--text)">{{ $m['name'] }}</strong> is already registered — the credit is added to this customer.
                    @if($m['owes'])<br>{{ $m['owes'] }}@endif
                </div>
            @elseif(\App\Services\Sales\OpeningCreditService::normalisePhone($phone))
                <div class="oc-match new">New customer — they'll be registered with this phone and the name below.</div>
            @endif
        </div>

        @if(! $this->phoneMatch)
        <div class="oc-field">
            <label class="oc-label">Customer name <span>*</span></label>
            <input type="text" wire:model="name" class="oc-input" placeholder="e.g. Jean Mugisha" autocomplete="off">
            @error('name')<div class="oc-error">{{ $message }}</div>@enderror
        </div>
        @endif

        <div class="oc-field">
            <label class="oc-label">Shop that gave the credit <span>*</span></label>
            <select wire:model="shopId" class="oc-input">
                <option value="">Choose a shop…</option>
                @foreach($this->shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
            <div class="oc-hint">The customer repays it at this shop.</div>
            @error('shopId')<div class="oc-error">{{ $message }}</div>@enderror
        </div>

        <div class="oc-field-row">
            <div class="oc-field">
                <label class="oc-label">Amount still owed (RWF) <span>*</span></label>
                <x-money-input wire:model="amount" class="oc-input" placeholder="0" />
                @error('amount')<div class="oc-error">{{ $message }}</div>@enderror
            </div>
            <div class="oc-field">
                <label class="oc-label">Date taken</label>
                <input type="date" wire:model="takenOn" class="oc-input" max="{{ business_today()->toDateString() }}">
                <div class="oc-hint">Blank = today. Overdue alerts count from this date.</div>
                @error('takenOn')<div class="oc-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="oc-field">
            <label class="oc-label">Reference</label>
            <input type="text" wire:model="reference" class="oc-input" placeholder="optional — e.g. notebook page 12" maxlength="100">
        </div>
        <div class="oc-field">
            <label class="oc-label">Notes</label>
            <textarea wire:model="notes" class="oc-input" rows="2" placeholder="optional" maxlength="500"></textarea>
        </div>
        @endif
    </div>
    <div class="oc-drawer-foot">
        <button type="button" wire:click="closeForm" class="oc-cancel-btn">Cancel</button>
        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="oc-save-btn">
            <span wire:loading.remove wire:target="save">Add credit</span>
            <span wire:loading wire:target="save" style="display:none">Saving…</span>
        </button>
    </div>
</div>

{{-- ══ Upload ══ --}}
@if($showImport)<div class="oc-overlay" wire:click="closeImport"></div>@endif
<div class="oc-drawer wide {{ $showImport ? 'open' : '' }}" role="dialog" aria-label="Upload old credit">
    <div class="oc-drawer-head">
        <div>
            <div class="oc-drawer-title">Upload old credit from Excel</div>
            <div class="oc-drawer-sub">Check the rows, then add them</div>
        </div>
        <button type="button" wire:click="closeImport" class="oc-drawer-close m-tap" aria-label="Close">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="oc-drawer-body">
        @if($showImport)
        <div class="oc-steps">
            <div class="oc-step"><span class="oc-step-n">1</span>
                <div>Download the template and fill in one row per debt: phone, customer name, shop, amount still owed, date taken.
                    <div style="margin-top:6px"><button type="button" wire:click="downloadTemplate" class="oc-btn oc-btn-ghost" style="padding:6px 12px;font-size:12px">Download template (.xlsx)</button></div>
                </div>
            </div>
            <div class="oc-step"><span class="oc-step-n">2</span><div>Upload it. Nothing is saved yet — you see every row first.</div></div>
            <div class="oc-step"><span class="oc-step-n">3</span><div>Add the rows that are ready. Rows with a problem are skipped; fix them in the sheet and upload again (rows already added are recognised and not added twice).</div></div>
        </div>

        <label class="oc-drop">
            <input type="file" wire:model="excelFile" accept=".xlsx,.xls,.csv" style="display:none">
            <span wire:loading.remove wire:target="excelFile">
                @if($excelFile) {{ $excelFile->getClientOriginalName() }} — choose another file @else Choose the Excel file (.xlsx, .xls or .csv) @endif
            </span>
            <span wire:loading wire:target="excelFile">Reading the file…</span>
        </label>
        @error('excelFile')<div class="oc-error" style="margin-top:8px">{{ $message }}</div>@enderror

        @if($preview)
            @php($t = $this->previewTotals)
            <div class="oc-sum">
                <span><b>{{ $t['ok'] }}</b> ready</span>
                <span><b>{{ number_format($t['amount']) }}</b> RWF</span>
                <span><b>{{ $t['new'] }}</b> new {{ \Illuminate\Support\Str::plural('customer', $t['new']) }}</span>
                @if($t['problems'])<span style="color:var(--red)"><b style="color:var(--red)">{{ $t['problems'] }}</b> with a problem (skipped)</span>@endif
            </div>
            <div class="oc-prev-scroll">
                <table class="oc-table oc-prev">
                    <thead>
                        <tr><th>Row</th><th>Check</th><th>Phone</th><th>Customer</th><th>Shop</th><th style="text-align:right">Amount</th><th>Taken on</th></tr>
                    </thead>
                    <tbody>
                    @foreach($preview as $r)
                        <tr wire:key="pv-{{ $r['row'] }}">
                            <td style="color:var(--text-dim);font-family:var(--mono)">{{ $r['row'] }}</td>
                            <td class="oc-err">
                                @if($r['errors'])
                                    {{ implode(' ', $r['errors']) }}
                                @else
                                    <span style="color:var(--green);font-weight:700">Ready</span>
                                @endif
                            </td>
                            <td style="font-family:var(--mono)">{{ $r['phone'] }}</td>
                            <td>
                                <span class="oc-main">{{ $r['name'] ?: '—' }}</span>
                                @if(! $r['errors'])
                                    @if($r['is_new_customer'])
                                        <span class="oc-badge" style="background:var(--accent-dim);color:var(--accent);margin-left:6px">New</span>
                                    @elseif($r['name_differs'])
                                        <div class="oc-sub">Registered under this name; the sheet's name is ignored</div>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $r['shop'] ?: '—' }}</td>
                            <td class="oc-num">{{ $r['amount'] !== null ? number_format($r['amount']) : '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($r['taken_on'])->format('d M Y') }}@if(! $r['date_given'])<span class="oc-sub"> (blank → today)</span>@endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @endif
    </div>
    <div class="oc-drawer-foot">
        <button type="button" wire:click="closeImport" class="oc-cancel-btn">Cancel</button>
        @php($ready = $preview ? $this->previewTotals['ok'] : 0)
        <button type="button" wire:click="confirmImport" wire:loading.attr="disabled" wire:target="confirmImport,excelFile"
                class="oc-save-btn" @disabled(! $ready)>
            <span wire:loading.remove wire:target="confirmImport">{{ $ready ? 'Add ' . $ready . ' ' . \Illuminate\Support\Str::plural('row', $ready) : 'Add rows' }}</span>
            <span wire:loading wire:target="confirmImport" style="display:none">Adding…</span>
        </button>
    </div>
</div>
</div>
