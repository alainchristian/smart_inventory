<x-app-layout>
<style>
/* ── Layout ── */
.dc-index-grid  { display:grid; grid-template-columns:1fr 340px; gap:24px; align-items:start; }
.dc-page-outer  { padding:20px 0 80px; }
.dc-page-inner  { max-width:1100px; margin:0 auto; padding:0 16px; }
.dc-header      { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:28px; }
.dc-header-actions { display:flex; align-items:center; gap:8px; flex-shrink:0; }

/* ── Cards ── */
.dc-card-head     { padding:11px 14px; background:var(--surface2); border-bottom:1px solid var(--border); }
.dc-card-head-row { padding:11px 14px; background:var(--surface2); border-bottom:1px solid var(--border);
                    display:flex; align-items:center; justify-content:space-between; }
.dc-section-lbl   { display:flex; align-items:center; gap:8px; margin-bottom:12px; }
.dc-left-col      { display:flex; flex-direction:column; gap:20px; }
.dc-right-col     { display:flex; flex-direction:column; gap:16px; }

/* ── Quick links ── */
.dc-quick-link { display:flex;align-items:center;justify-content:space-between;width:100%;
                 padding:10px 14px;border-radius:10px;font-size:13px;font-weight:500;
                 background:var(--surface);border:1px solid var(--border);color:var(--text);
                 text-decoration:none;transition:border-color 0.15s; }
.dc-quick-link:hover { border-color:var(--accent); }

/* ── Responsive: tablet ── */
@media (max-width:860px) {
    .dc-index-grid { grid-template-columns:1fr; gap:18px; }
}

/* ── Responsive: mobile ── */
@media (max-width:640px) {
    .dc-page-outer        { padding:8px 0 60px; }
    .dc-page-inner        { padding:0 8px; }
    .dc-header            { margin-bottom:16px; gap:10px; flex-wrap:wrap; }
    .dc-header h1         { font-size:18px; }
    .dc-header-actions    { gap:6px; }
    .dc-header-hist-btn   { display:none; }   /* duplicate of Quick Actions > Session History */
    .dc-close-btn         { padding:8px 12px !important; font-size:12px !important; }
    .dc-index-grid        { gap:14px; }
    .dc-card-head         { padding:8px 10px; }
    .dc-card-head-row     { padding:8px 10px; }
    .dc-section-lbl       { margin-bottom:8px; }
    .dc-left-col          { gap:14px; }
    .dc-right-col         { gap:12px; }
    .dc-quick-link        { padding:9px 10px; font-size:12px; }
    .dc-snap-wrap         { border-radius:10px; }
    .dc-snap-label        { font-size:8px !important; }
    .dc-snap-num          { font-size:14px !important; }
    .dc-snap-sub          { font-size:8px !important; }
    .dc-snap-breakdown    { font-size:8px !important; }
}
</style>

<div class="dc-page-outer">
    <div class="dc-page-inner">

        {{-- ── Header ── --}}
        <div class="dc-header">
            <div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                    <h1 style="font-weight:800;color:var(--text);margin:0;">Cash & Daily Register</h1>
                    @php
                        $shopId      = auth()->user()->location_id;
                        $todaySess   = \App\Models\DailySession::forShop($shopId)
                            ->forDate(today()->toDateString())
                            ->first();
                        $sessStatus  = $todaySess?->status ?? null;
                    @endphp
                    @if ($sessStatus === 'open')
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;
                                     font-size:11px;font-weight:700;background:var(--green-dim);color:var(--green);border:1px solid var(--green);">
                            <span style="width:6px;height:6px;border-radius:50%;background:var(--green);
                                         animation:pulse 2s infinite;display:inline-block;"></span>
                            Live
                        </span>
                    @elseif ($sessStatus === 'closed')
                        <span style="padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;
                                     background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);">
                            Closed
                        </span>
                    @endif
                </div>
                <p style="font-size:13px;color:var(--text-dim);margin:0;">{{ now()->format('l, d F Y') }}</p>
            </div>
            <div class="dc-header-actions">
                <a href="{{ route('shop.session.history') }}"
                   class="dc-header-hist-btn"
                   style="padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;
                          background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);
                          text-decoration:none;transition:border-color 0.15s;"
                   onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
                    History
                </a>
                <a href="{{ route('shop.day-close.close') }}"
                   class="dc-close-btn"
                   style="padding:8px 16px;border-radius:10px;font-size:13px;font-weight:700;
                          background:var(--accent);color:white;text-decoration:none;
                          display:flex;align-items:center;gap:6px;">
                    Close Register
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- ── Main grid ── --}}
        <div class="dc-index-grid">

            {{-- LEFT: Session + Snapshot + Requests --}}
            <div class="dc-left-col">

                <livewire:shop.day-close.open-session />

                {{-- Snapshot — follows the session selected in Activity History --}}
                @php
                    $latestSess = \App\Models\DailySession::forShop($shopId)
                        ->orderByDesc('session_date')
                        ->orderByDesc('opened_at')
                        ->first();
                @endphp
                @if ($latestSess)
                    <div class="dc-snap-wrap" style="border-radius:14px;border:1px solid var(--border);overflow:hidden;">
                        <livewire:shop.day-close.session-snapshot :sessionId="$latestSess->id" />
                    </div>
                @endif

                {{-- Pending Warehouse Requests --}}
                <div>
                    <div class="dc-section-lbl">
                        <svg width="14" height="14" fill="none" stroke="var(--text-dim)" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                        </svg>
                        <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-dim);">Pending Warehouse Requests</span>
                    </div>
                    <livewire:shop.day-close.pending-requests />
                </div>
            </div>

            {{-- RIGHT: Sidebar --}}
            <div class="dc-right-col">

                {{-- Quick Actions --}}
                <div style="border-radius:14px;overflow:hidden;border:1px solid var(--border);">
                    <div class="dc-card-head">
                        <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-dim);">Quick Actions</span>
                    </div>
                    <div style="background:var(--surface);padding:10px;display:flex;flex-direction:column;gap:6px;">
                        <a href="{{ route('shop.day-close.close') }}" class="dc-quick-link">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:28px;height:28px;border-radius:8px;background:var(--accent-dim);
                                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="13" height="13" fill="none" stroke="var(--accent)" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                Close Today's Session
                            </div>
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color:var(--text-dim);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('shop.session.history') }}" class="dc-quick-link">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:28px;height:28px;border-radius:8px;background:var(--surface2);
                                            display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid var(--border);">
                                    <svg width="13" height="13" fill="none" stroke="var(--text-dim)" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                Session History
                            </div>
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color:var(--text-dim);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('shop.session.requests') }}" class="dc-quick-link">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:28px;height:28px;border-radius:8px;background:var(--amber-dim);
                                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="13" height="13" fill="none" stroke="var(--amber)" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                Expense Requests
                            </div>
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color:var(--text-dim);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Activity panel — session switcher + feed --}}
                @php $hasSessions = \App\Models\DailySession::where('shop_id', $shopId)->exists(); @endphp
                @if ($hasSessions)
                    <div style="border-radius:14px;overflow:hidden;border:1px solid var(--border);">
                        <div class="dc-card-head-row">
                            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-dim);">Activity History</span>
                            <span style="font-size:10px;color:var(--text-dim);">Last 10 sessions</span>
                        </div>
                        <div style="background:var(--surface);">
                            <livewire:shop.day-close.session-activity-panel :shopId="$shopId" />
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
</x-app-layout>
