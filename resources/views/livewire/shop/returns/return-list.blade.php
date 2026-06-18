@if($sessionBlocked)
    <x-session-gate-blocked
        :reason="$sessionBlockReason"
        :session-date="$blockedSessionDate"
        :session-id="$blockedSessionId"
    />
@else

@php
    $rlRefundCount   = max(0, ($kpiStats['total_returns'] ?? 0) - ($kpiStats['exchange_count'] ?? 0));
    $rlApprovedCount = max(0, ($kpiStats['total_returns'] ?? 0) - ($kpiStats['pending_approval'] ?? 0));
    $rlAvgRefund     = $rlRefundCount > 0 ? intval(($kpiStats['total_refunds'] ?? 0) / $rlRefundCount) : 0;
    $rlPendingPct    = ($kpiStats['total_returns'] ?? 0) > 0
                        ? round((($kpiStats['pending_approval'] ?? 0) / ($kpiStats['total_returns'] ?? 1)) * 100) : 0;
    $rlExchPct       = ($kpiStats['total_returns'] ?? 0) > 0
                        ? round((($kpiStats['exchange_count'] ?? 0) / ($kpiStats['total_returns'] ?? 1)) * 100) : 0;
@endphp

<div style="font-family:var(--font);padding-bottom:80px">
<style>
[x-cloak] { display:none !important }

/* ── KPI Cards ─────────────────────────────────────────────────────── */
.rl-kpis      { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px }
.rl-kpi       { background:var(--surface);border:none;border-radius:var(--r);
                box-shadow:var(--shadow-card);padding:22px 20px;
                display:flex;flex-direction:column;gap:16px;transition:box-shadow var(--tr) }
.rl-kpi:hover { box-shadow:var(--shadow-card-hover) }
.rl-kpi-row   { display:flex;align-items:center;gap:12px }
.rl-kpi-icon  { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;
                justify-content:center;flex-shrink:0 }
.rl-kpi-body  { flex:1;min-width:0 }
.rl-kpi-label { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                color:var(--text-dim);line-height:1.2 }
.rl-kpi-sub   { font-size:12px;color:var(--text-dim);margin-top:2px }
.rl-kpi-val   { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;line-height:1 }
.rl-kpi-bar   { height:3px;border-radius:3px }
.rl-kpi-div   { height:1px;background:var(--border) }
.rl-kpi-foot  { display:grid;grid-template-columns:repeat(3,1fr) }
.rl-kpi-stat  { display:flex;flex-direction:column;align-items:center;gap:3px;padding:4px 0 }
.rl-kpi-sv    { font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text-sub) }
.rl-kpi-sl    { font-size:10px;color:var(--text-dim);letter-spacing:.3px }

/* ── Header ─────────────────────────────────────────────────────────── */
.rl-hdr     { display:flex;align-items:flex-start;justify-content:space-between;
              gap:16px;margin-bottom:24px;flex-wrap:wrap }
.rl-hdr-ttl { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px;letter-spacing:-.3px }
.rl-hdr-sub { font-size:13px;color:var(--text-dim);margin:0 }

/* ── Filter Card ─────────────────────────────────────────────────────── */
.rl-flt-card  { background:var(--surface);border:none;border-radius:var(--r);
                box-shadow:var(--shadow-card);margin-bottom:20px;min-width:0;max-width:100% }
.rl-presets   { display:flex;gap:4px;overflow-x:auto;-webkit-overflow-scrolling:touch;
                padding:10px 14px;border-bottom:1px solid var(--border);
                scrollbar-width:none;flex-wrap:nowrap;min-width:0 }
.rl-presets::-webkit-scrollbar { display:none }
.rl-preset    { padding:5px 11px;border-radius:6px;font-size:12px;font-weight:600;
                border:1px solid transparent;background:transparent;color:var(--text-dim);
                cursor:pointer;white-space:nowrap;flex-shrink:0;transition:all var(--tr);
                font-family:var(--font) }
.rl-preset:hover  { background:var(--surface2);color:var(--text);border-color:var(--border) }
.rl-preset.active { background:var(--accent);color:#fff;border-color:var(--accent);
                    box-shadow:0 2px 8px rgba(0,0,0,.12) }
.rl-flt-row   { display:flex;align-items:center;flex-wrap:wrap }
.rl-flt-seg   { display:flex;align-items:center;gap:6px;padding:8px 14px;
                border-right:1px solid var(--border);flex-shrink:0 }
.rl-flt-seg:last-child { border-right:none }
.rl-flt-grow  { flex:1;min-width:200px }
.rl-date-in   { padding:0;border:none;background:transparent;color:var(--text);
                font-size:13px;font-weight:600;font-family:var(--font);
                cursor:pointer;width:110px;outline:none }
.rl-date-in:focus { color:var(--accent) }
.rl-flt-sel   { padding:0;border:none;background:transparent;color:var(--text);
                font-size:13px;font-weight:600;font-family:var(--font);
                cursor:pointer;outline:none;min-width:0 }
.rl-search    { width:100%;padding:0;border:none;background:transparent;color:var(--text);
                font-size:13px;font-family:var(--font);outline:none }
.rl-search::placeholder { color:var(--text-dim) }
.rl-reset-btn { padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);
                background:transparent;font-size:12px;font-weight:600;cursor:pointer;
                font-family:var(--font);color:var(--text-dim);transition:all var(--tr) }
.rl-reset-btn:hover { border-color:var(--accent);color:var(--accent) }

/* ── Table ──────────────────────────────────────────────────────────── */
.rl-tbl-wrap { background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card) }
.rl-tbl-scrl { overflow-x:auto;-webkit-overflow-scrolling:touch }
.rl-tbl-scrl::-webkit-scrollbar { height:6px }
.rl-tbl-scrl::-webkit-scrollbar-track { background:transparent }
.rl-tbl-scrl::-webkit-scrollbar-thumb { background:var(--border);border-radius:3px }
.rl-tbl-scrl::-webkit-scrollbar-thumb:hover { background:var(--text-dim) }
.rl-tbl      { width:100%;border-collapse:collapse;table-layout:fixed }
.rl-tbl thead tr { border-bottom:2px solid var(--border) }
.rl-tbl thead th { padding:10px 16px;text-align:left;font-size:10px;font-weight:700;
                   letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.rl-tbl tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr);cursor:pointer }
.rl-tbl tbody tr:last-child { border-bottom:none }
.rl-tbl tbody tr:hover  { background:var(--surface2) }
.rl-tbl tbody tr.rl-exp { background:var(--surface2) }
.rl-tbl td   { padding:13px 16px;font-size:13px;vertical-align:middle }

/* ── Badges & pills ─────────────────────────────────────────────────── */
.rl-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;
            padding:3px 9px;border-radius:6px;white-space:nowrap }
.rl-pill  { display:inline-flex;align-items:center;font-size:11px;font-weight:600;
            padding:3px 9px;border-radius:20px;white-space:nowrap }
.rl-mono  { font-family:var(--mono);font-weight:700 }

/* ── Action buttons ─────────────────────────────────────────────────── */
.rl-act-btn { width:26px;height:26px;border-radius:6px;border:1.5px solid var(--border);
              background:transparent;display:inline-flex;align-items:center;justify-content:center;
              cursor:pointer;color:var(--text-dim);transition:all var(--tr);flex-shrink:0 }
.rl-act-btn:hover { border-color:var(--accent);color:var(--accent) }
.rl-approve-btn { padding:4px 12px;border-radius:7px;border:1.5px solid var(--green);
                  background:var(--green-dim);color:var(--green);font-size:11px;font-weight:700;
                  cursor:pointer;font-family:var(--font);transition:all var(--tr);
                  white-space:nowrap;display:inline-block }
.rl-approve-btn:hover { background:var(--green);color:#fff }
.rl-btn-primary { padding:9px 18px;border-radius:var(--rsm);font-size:13px;font-weight:600;
                  cursor:pointer;font-family:var(--font);transition:all var(--tr);
                  display:inline-flex;align-items:center;gap:6px;
                  background:var(--accent);color:#fff;border:none;
                  box-shadow:0 3px 10px rgba(59,111,212,.25);text-decoration:none }
.rl-btn-primary:hover { opacity:.88 }
.rl-btn-amber   { background:var(--amber);box-shadow:0 3px 10px rgba(217,119,6,.25) }

/* ── Expand panel ───────────────────────────────────────────────────── */
.rl-exp-wrap { padding:0 16px 16px }
.rl-exp-panel { border-left:3px solid var(--accent);padding:16px 20px;background:var(--surface) }
.rl-exp-grid  { display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px }
.rl-exp-hd    { font-size:10px;font-weight:700;color:var(--text-dim);
                text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px }

/* ── Pending banner ─────────────────────────────────────────────────── */
.rl-banner { padding:12px 16px;border-radius:10px;background:var(--amber-dim);
             border:1px solid var(--amber);margin-bottom:20px;
             display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px }

/* ── Empty state ────────────────────────────────────────────────────── */
.rl-empty       { padding:60px 20px;text-align:center }
.rl-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.rl-empty-sub   { font-size:13px;color:var(--text-dim) }

/* ── Responsive ─────────────────────────────────────────────────────── */
@media(max-width:1024px) { .rl-kpis { grid-template-columns:repeat(2,1fr) } }
@media(max-width:640px)  {
    .rl-kpis { grid-template-columns:1fr }
    .rl-exp-grid { grid-template-columns:1fr }
    .rl-flt-seg { border-right:none;border-bottom:1px solid var(--border) }
    .rl-flt-seg:last-child { border-bottom:none }
}
</style>

{{-- Flash messages --}}
@if(session()->has('error'))
    <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;background:var(--red-dim);border:1px solid var(--red);font-size:13px;font-weight:600;color:var(--red)">{{ session('error') }}</div>
@endif
@if(session()->has('success'))
    <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;background:var(--green-dim);border:1px solid var(--green);font-size:13px;font-weight:600;color:var(--green)">{{ session('success') }}</div>
@endif

{{-- ── HEADER ──────────────────────────────────────────────────────── --}}
<div class="rl-hdr">
    <div>
        <h1 class="rl-hdr-ttl">Returns</h1>
        <p class="rl-hdr-sub">@if($isOwner) All shops · @endif Refunds &amp; exchanges</p>
    </div>
    @if(!$isOwner)
        <a href="{{ route('shop.returns.create') }}" class="rl-btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Process Return
        </a>
    @endif
</div>

{{-- ── FILTER BAR ───────────────────────────────────────────────────── --}}
<div class="rl-flt-card"
     x-data="{
         preset: 'last_30',
         setPreset(p) {
             const today = new Date();
             const fmt   = d => d.toISOString().split('T')[0];
             const sub   = n => { let d = new Date(today); d.setDate(d.getDate() - n); return d; };
             let from, to = fmt(today);
             if      (p === 'today')   { from = fmt(today); }
             else if (p === 'week')    { let d = new Date(today); d.setDate(d.getDate() - d.getDay() + 1); from = fmt(d); }
             else if (p === 'month')   { from = fmt(new Date(today.getFullYear(), today.getMonth(), 1)); }
             else if (p === 'last_30') { from = fmt(sub(29)); }
             else { this.preset = 'custom'; return; }
             this.preset = p;
             $wire.set('dateFrom', from);
             $wire.set('dateTo', to);
         }
     }">

    {{-- Period presets --}}
    <div class="rl-presets">
        <button class="rl-preset" :class="preset === 'today'   ? 'active' : ''" @click="setPreset('today')">Today</button>
        <button class="rl-preset" :class="preset === 'week'    ? 'active' : ''" @click="setPreset('week')">This Week</button>
        <button class="rl-preset" :class="preset === 'month'   ? 'active' : ''" @click="setPreset('month')">This Month</button>
        <button class="rl-preset" :class="preset === 'last_30' ? 'active' : ''" @click="setPreset('last_30')">Last 30 Days</button>
        <button class="rl-preset" :class="preset === 'custom'  ? 'active' : ''" @click="preset = 'custom'">Custom</button>
    </div>

    {{-- Filter row --}}
    <div class="rl-flt-row">

        {{-- Search --}}
        <div class="rl-flt-seg rl-flt-grow" style="gap:8px">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                 style="color:var(--text-dim);flex-shrink:0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" class="rl-search"
                   placeholder="Return number, customer name or phone..."
                   wire:model.live.debounce.300ms="search">
        </div>

        {{-- Date range --}}
        <div class="rl-flt-seg">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                 style="color:var(--text-dim);flex-shrink:0">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <input type="date" class="rl-date-in" wire:model.live="dateFrom" @change="preset = 'custom'">
            <span style="font-size:13px;color:var(--text-dim);flex-shrink:0">→</span>
            <input type="date" class="rl-date-in" wire:model.live="dateTo" @change="preset = 'custom'">
        </div>

        {{-- Status --}}
        <div class="rl-flt-seg">
            <select class="rl-flt-sel" wire:model.live="statusFilter">
                <option value="all">All Status</option>
                <option value="pending_approval">Pending Approval</option>
                <option value="approved">Approved</option>
            </select>
        </div>

        {{-- Type --}}
        <div class="rl-flt-seg">
            <select class="rl-flt-sel" wire:model.live="typeFilter">
                <option value="all">All Types</option>
                <option value="refund">Refunds</option>
                <option value="exchange">Exchanges</option>
            </select>
        </div>

        @if($isOwner)
        {{-- Shop --}}
        <div class="rl-flt-seg">
            <select class="rl-flt-sel" wire:model.live="shopFilter">
                <option value="all">All Shops</option>
                @foreach($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Reset --}}
        <div class="rl-flt-seg">
            <button class="rl-reset-btn" wire:click="resetFilters" @click="preset = 'last_30'">Reset</button>
        </div>

    </div>
</div>

{{-- ── KPI CARDS ─────────────────────────────────────────────────────── --}}
<div class="rl-kpis">

    {{-- 1: Total Returns --}}
    <div class="rl-kpi">
        <div class="rl-kpi-row">
            <div class="rl-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                </svg>
            </div>
            <div class="rl-kpi-body">
                <div class="rl-kpi-label">Total Returns</div>
                <div class="rl-kpi-sub">This period</div>
            </div>
        </div>
        <div class="rl-kpi-val" style="color:var(--accent)">{{ $kpiStats['total_returns'] ?? 0 }}</div>
        <div class="rl-kpi-div"></div>
        <div class="rl-kpi-foot">
            <div class="rl-kpi-stat">
                <span class="rl-kpi-sv">{{ $rlRefundCount }}</span>
                <span class="rl-kpi-sl">Refunds</span>
            </div>
            <div class="rl-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="rl-kpi-sv">{{ $kpiStats['exchange_count'] ?? 0 }}</span>
                <span class="rl-kpi-sl">Exchanges</span>
            </div>
            <div class="rl-kpi-stat">
                <span class="rl-kpi-sv" style="color:var(--green)">{{ $rlApprovedCount }}</span>
                <span class="rl-kpi-sl">Approved</span>
            </div>
        </div>
    </div>

    {{-- 2: Cash Refunded --}}
    <div class="rl-kpi">
        <div class="rl-kpi-row">
            <div class="rl-kpi-icon" style="background:var(--red-dim);color:var(--red)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="rl-kpi-body">
                <div class="rl-kpi-label">Cash Refunded</div>
                <div class="rl-kpi-sub">Total issued</div>
            </div>
        </div>
        <div class="rl-kpi-val" style="color:var(--red)">
            {{ number_format($kpiStats['total_refunds'] ?? 0) }}<span style="font-size:13px;font-weight:500;color:var(--text-dim);margin-left:3px">RWF</span>
        </div>
        <div class="rl-kpi-bar" style="background:var(--red-dim)">
            <div style="height:100%;border-radius:3px;background:var(--red);width:{{ ($kpiStats['total_refunds'] ?? 0) > 0 ? 100 : 0 }}%"></div>
        </div>
        <div class="rl-kpi-div"></div>
        <div class="rl-kpi-foot">
            <div class="rl-kpi-stat">
                <span class="rl-kpi-sv">{{ $rlRefundCount }}</span>
                <span class="rl-kpi-sl">Refunds</span>
            </div>
            <div class="rl-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="rl-kpi-sv">{{ $rlAvgRefund > 0 ? number_format($rlAvgRefund) : '—' }}</span>
                <span class="rl-kpi-sl">Avg/Refund</span>
            </div>
            <div class="rl-kpi-stat">
                <span class="rl-kpi-sv" style="color:var(--green)">{{ $rlApprovedCount }}</span>
                <span class="rl-kpi-sl">Approved</span>
            </div>
        </div>
    </div>

    {{-- 3: Exchanges --}}
    <div class="rl-kpi">
        <div class="rl-kpi-row">
            <div class="rl-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div class="rl-kpi-body">
                <div class="rl-kpi-label">Exchanges</div>
                <div class="rl-kpi-sub">No refund issued</div>
            </div>
        </div>
        <div class="rl-kpi-val" style="color:var(--violet)">{{ $kpiStats['exchange_count'] ?? 0 }}</div>
        <div class="rl-kpi-div"></div>
        <div class="rl-kpi-foot">
            <div class="rl-kpi-stat">
                <span class="rl-kpi-sv">{{ $rlExchPct }}%</span>
                <span class="rl-kpi-sl">Of Returns</span>
            </div>
            <div class="rl-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="rl-kpi-sv">{{ $rlRefundCount }}</span>
                <span class="rl-kpi-sl">Refunds</span>
            </div>
            <div class="rl-kpi-stat">
                <span class="rl-kpi-sv">{{ $kpiStats['total_returns'] ?? 0 }}</span>
                <span class="rl-kpi-sl">Total</span>
            </div>
        </div>
    </div>

    {{-- 4: Pending Approval --}}
    <div class="rl-kpi">
        <div class="rl-kpi-row">
            <div class="rl-kpi-icon" style="background:var(--amber-dim);color:var(--amber)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="rl-kpi-body">
                <div class="rl-kpi-label">Pending Approval</div>
                <div class="rl-kpi-sub">Awaiting owner</div>
            </div>
        </div>
        <div class="rl-kpi-val" style="color:var(--amber)">{{ $kpiStats['pending_approval'] ?? 0 }}</div>
        <div class="rl-kpi-div"></div>
        <div class="rl-kpi-foot">
            <div class="rl-kpi-stat">
                <span class="rl-kpi-sv" style="color:var(--amber)">{{ $kpiStats['pending_approval'] ?? 0 }}</span>
                <span class="rl-kpi-sl">Awaiting</span>
            </div>
            <div class="rl-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="rl-kpi-sv" style="color:var(--green)">{{ $rlApprovedCount }}</span>
                <span class="rl-kpi-sl">Approved</span>
            </div>
            <div class="rl-kpi-stat">
                <span class="rl-kpi-sv">{{ $rlPendingPct }}%</span>
                <span class="rl-kpi-sl">Pending Rate</span>
            </div>
        </div>
    </div>

</div>

{{-- ── PENDING APPROVALS BANNER ─────────────────────────────────────── --}}
@if($isOwner && ($kpiStats['pending_approval'] ?? 0) > 0)
    <div class="rl-banner">
        <div style="display:flex;align-items:center;gap:10px">
            <svg style="width:18px;height:18px;color:var(--amber);flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span style="font-size:13px;font-weight:600;color:var(--amber)">
                {{ $kpiStats['pending_approval'] }} return(s) waiting for your approval
            </span>
        </div>
        <button wire:click="$set('statusFilter','pending_approval')" class="rl-btn-primary rl-btn-amber">
            View Pending
        </button>
    </div>
@endif

{{-- ── RETURNS TABLE ────────────────────────────────────────────────── --}}
<div class="rl-tbl-wrap" x-data="{ expandedRow: null }">
    <div class="rl-tbl-scrl">
        <table class="rl-tbl" style="min-width:{{ $isOwner ? '950px' : '800px' }}">
            <colgroup>
                <col style="width:160px">
                @if($isOwner) <col style="width:130px"> @endif
                <col style="width:180px">
                <col style="width:100px">
                <col style="width:150px">
                <col style="width:70px">
                <col style="width:130px">
                <col style="width:{{ $isOwner ? '130px' : '150px' }}">
            </colgroup>
            <thead>
                <tr>
                    <th>Return #</th>
                    @if($isOwner) <th>Shop</th> @endif
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Refund</th>
                    <th>Items</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $return)
                    <tr :class="expandedRow === {{ $return->id }} ? 'rl-exp' : ''"
                        @click="expandedRow === {{ $return->id }} ? expandedRow = null : expandedRow = {{ $return->id }}">

                        <td>
                            <span class="rl-mono" style="font-size:12px;color:var(--text)">{{ $return->return_number }}</span>
                        </td>

                        @if($isOwner)
                            <td>
                                <span style="font-size:12px;color:var(--text-sub)">{{ $return->shop->name ?? '—' }}</span>
                            </td>
                        @endif

                        <td>
                            <div class="td-2l">
                                <div class="td-2l-main">{{ $return->customer_name ?? 'Walk-in' }}</div>
                                @if($return->customer_phone)
                                    <div class="td-2l-sub">{{ $return->customer_phone }}</div>
                                @endif
                            </div>
                        </td>

                        <td>
                            <span class="rl-badge" style="{{ $return->is_exchange ? 'background:var(--violet-dim);color:var(--violet)' : 'background:var(--green-dim);color:var(--green)' }}">
                                {{ $return->is_exchange ? 'Exchange' : 'Refund' }}
                            </span>
                        </td>

                        <td style="white-space:nowrap">
                            @if($return->is_exchange)
                                <span style="font-size:13px;color:var(--text-dim)">—</span>
                            @else
                                <span style="font-family:var(--mono);font-weight:700;color:var(--red)">
                                    {{ number_format($return->refund_amount) }}
                                    <span style="font-size:10px;color:var(--text-dim);font-weight:400;font-family:var(--font)">RWF</span>
                                </span>
                            @endif
                        </td>

                        <td>
                            <span class="rl-pill" style="background:var(--accent-dim);color:var(--accent)">
                                {{ $return->items->count() }}
                            </span>
                        </td>

                        <td>
                            <div class="td-2l">
                                <div class="td-2l-main">{{ $return->processed_at->format('d M Y') }}</div>
                                <div class="td-2l-sub">{{ $return->processed_at->format('H:i') }}</div>
                            </div>
                        </td>

                        <td>
                            <div @click.stop style="display:flex;align-items:center;gap:6px">
                                @if($isOwner && !$return->approved_at)
                                    <button class="rl-approve-btn"
                                            wire:click="approveReturn({{ $return->id }})"
                                            wire:confirm="Approve return {{ $return->return_number }}?"
                                            wire:loading.attr="disabled"
                                            wire:target="approveReturn({{ $return->id }})">
                                        <span wire:loading.remove wire:target="approveReturn({{ $return->id }})">Approve</span>
                                        <span wire:loading wire:target="approveReturn({{ $return->id }})" style="display:none">…</span>
                                    </button>
                                @elseif($return->approved_at)
                                    <span class="rl-badge" style="background:var(--green-dim);color:var(--green)">
                                        <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Approved
                                    </span>
                                @else
                                    <span class="rl-badge" style="background:var(--amber-dim);color:var(--amber)">Pending</span>
                                @endif

                                <button class="rl-act-btn"
                                        :style="expandedRow === {{ $return->id }} ? 'transform:rotate(180deg)' : ''"
                                        @click="expandedRow === {{ $return->id }} ? expandedRow = null : expandedRow = {{ $return->id }}">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Expand detail --}}
                    <tr x-show="expandedRow === {{ $return->id }}" x-cloak style="border-bottom:1px solid var(--border)">
                        <td colspan="{{ $isOwner ? '8' : '7' }}" style="padding:0">
                            <div class="rl-exp-wrap">
                                <div class="rl-exp-panel">
                                    <div class="rl-exp-grid">

                                        {{-- Returned Items --}}
                                        <div>
                                            <div class="rl-exp-hd">Returned Items</div>
                                            @foreach($return->items as $item)
                                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px">
                                                    <span style="color:var(--text-sub)">{{ $item->product->name ?? '—' }}</span>
                                                    <div style="text-align:right;flex-shrink:0">
                                                        <span style="color:var(--text);font-weight:600">×{{ $item->quantity_returned }}</span>
                                                        @if($item->quantity_damaged > 0)
                                                            <span style="color:var(--red);font-size:10px;margin-left:4px">({{ $item->quantity_damaged }} dmg)</span>
                                                        @endif
                                                        @if(!$return->is_exchange && $item->unit_price > 0)
                                                            <div style="font-size:10px;color:var(--text-dim);margin-top:1px">
                                                                = <span style="color:var(--green);font-weight:600">{{ number_format($item->refund_amount) }} RWF</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Details --}}
                                        <div>
                                            <div class="rl-exp-hd">Details</div>
                                            <div style="font-size:12px;color:var(--text-dim);line-height:2">
                                                <div>Reason:
                                                    <span style="color:var(--text);font-weight:600">
                                                        {{ ucfirst(str_replace('_', ' ', $return->reason->value ?? $return->reason)) }}
                                                    </span>
                                                </div>
                                                @if(!$return->is_exchange)
                                                    <div>Refund via:
                                                        <span style="color:var(--text);font-weight:600">
                                                            {{ ucfirst(str_replace('_', ' ', $return->refund_method ?? '—')) }}
                                                        </span>
                                                    </div>
                                                @endif
                                                @if($return->sale)
                                                    <div>Sale:
                                                        <span style="color:var(--accent);font-weight:600">{{ $return->sale->sale_number }}</span>
                                                    </div>
                                                @endif
                                                <div>Processed by:
                                                    <span style="color:var(--text);font-weight:600">{{ $return->processedBy->name ?? '—' }}</span>
                                                </div>
                                            </div>
                                            @if($return->notes)
                                                <div style="margin-top:8px;padding:8px 10px;border-left:3px solid var(--accent);
                                                            font-size:11px;color:var(--text-sub);line-height:1.6">
                                                    {{ $return->notes }}
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Approval --}}
                                        <div>
                                            <div class="rl-exp-hd">Approval Status</div>
                                            @if($return->approved_at)
                                                <div style="padding:10px 12px;border-radius:8px;background:var(--green-dim);border:1px solid var(--green)">
                                                    <div style="font-size:12px;font-weight:700;color:var(--green);display:flex;align-items:center;gap:6px">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Approved
                                                    </div>
                                                    <div style="font-size:11px;color:var(--text-dim);margin-top:4px">
                                                        By {{ $return->approvedBy->name ?? '—' }} · {{ $return->approved_at->format('d M Y') }}
                                                    </div>
                                                </div>
                                            @else
                                                <div style="padding:10px 12px;border-radius:8px;background:var(--amber-dim);border:1px solid var(--amber)">
                                                    <div style="font-size:12px;font-weight:600;color:var(--amber)">Pending approval</div>
                                                    @if($isOwner)
                                                        <button class="rl-approve-btn"
                                                                style="margin-top:8px;width:100%;padding:7px;display:block;text-align:center"
                                                                wire:click="approveReturn({{ $return->id }})"
                                                                wire:confirm="Approve this return?"
                                                                wire:loading.attr="disabled"
                                                                wire:target="approveReturn({{ $return->id }})">
                                                            <span wire:loading.remove wire:target="approveReturn({{ $return->id }})">Approve Now</span>
                                                            <span wire:loading wire:target="approveReturn({{ $return->id }})" style="display:none">Approving…</span>
                                                        </button>
                                                    @else
                                                        <div style="font-size:11px;color:var(--text-dim);margin-top:4px">Awaiting owner review</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="{{ $isOwner ? '8' : '7' }}">
                            <div class="rl-empty">
                                <div style="width:48px;height:48px;border-radius:12px;background:var(--accent-dim);
                                            display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--accent)">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                                    </svg>
                                </div>
                                <div class="rl-empty-title">No returns found</div>
                                <div class="rl-empty-sub">No returns match your current filters.</div>
                                @if(!$isOwner)
                                    <a href="{{ route('shop.returns.create') }}" class="rl-btn-primary" style="margin-top:16px">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Process Return
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($returns->hasPages())
        <div style="padding:14px 16px;border-top:1px solid var(--border)">
            {{ $returns->links() }}
        </div>
    @endif

</div>{{-- end table card --}}

</div>{{-- end page --}}
@endif
