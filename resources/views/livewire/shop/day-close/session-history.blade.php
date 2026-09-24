<div class="sh-page" style="font-family:var(--font)">
<style>
.sh-page { padding:0 0 80px; }

/* Header */
.sh-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap; }
.sh-head-l { display:flex;align-items:flex-start;gap:12px;min-width:0; }
.sh-back   { width:36px;height:36px;border-radius:var(--rsm);display:flex;align-items:center;justify-content:center;flex-shrink:0;
             background:var(--surface);color:var(--text-sub);box-shadow:var(--shadow-card);text-decoration:none;transition:all var(--tr); }
.sh-back:hover { box-shadow:var(--shadow-card-hover);color:var(--text); }
.sh-title  { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px; }
.sh-sub    { font-size:13px;color:var(--text-dim);margin:0; }
.sh-sub b  { color:var(--text-sub);font-weight:600; }
.sh-select { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);
             outline:none;cursor:pointer;font-family:var(--font); }
.sh-select:focus { border-color:var(--accent); }

/* KPIs (.iv-kpi anatomy) */
.sh-kpis      { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px; }
.sh-kpi       { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);padding:20px;display:flex;flex-direction:column;gap:14px;min-width:0;transition:box-shadow var(--tr); }
.sh-kpi:hover { box-shadow:var(--shadow-card-hover); }
.sh-kpi-row   { display:flex;align-items:center;gap:12px; }
.sh-kpi-icon  { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.sh-kpi-label { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);line-height:1.2; }
.sh-kpi-sub   { font-size:12px;color:var(--text-dim);margin-top:2px; }
.sh-kpi-val   { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;line-height:1;white-space:nowrap; }
.sh-kpi-unit  { font-size:12px;font-weight:500;color:var(--text-dim);margin-left:4px;letter-spacing:0; }
.sh-kpi-divider { height:1px;background:var(--border); }
.sh-kpi-stat  { display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid var(--border); }
.sh-kpi-stat:last-child { border-bottom:none; }
.sh-kpi-stat-l { font-size:11px;color:var(--text-dim);margin-right:8px; }
.sh-kpi-stat-v { font-size:13px;font-weight:700;font-family:var(--mono);color:var(--text-sub);white-space:nowrap; }

/* Table card */
.sh-card      { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0; }
.sh-card-head { padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap; }
.sh-card-title { font-size:13px;font-weight:700;color:var(--text); }
.sh-pills     { display:flex;gap:4px;overflow-x:auto;scrollbar-width:none;flex-wrap:nowrap; }
.sh-pills::-webkit-scrollbar { display:none; }
.sh-pill      { padding:5px 11px;border-radius:6px;font-size:12px;font-weight:600;border:1px solid transparent;background:transparent;color:var(--text-dim);
                cursor:pointer;white-space:nowrap;flex-shrink:0;transition:all var(--tr);font-family:var(--font); }
.sh-pill:hover  { background:var(--surface2);color:var(--text);border-color:var(--border); }
.sh-pill.active { background:var(--accent);color:#fff;border-color:var(--accent); }
.sh-pill-n    { font-family:var(--mono);font-size:11px;opacity:.75;margin-left:3px; }
.sh-scroll    { overflow-x:auto;-webkit-overflow-scrolling:touch; }
.sh-table     { width:100%;border-collapse:collapse;table-layout:fixed; }
.sh-table thead tr { border-bottom:2px solid var(--border); }
.sh-table th  { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap; }
.sh-table th.num, .sh-table td.num { text-align:right; }
.sh-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr);cursor:pointer; }
.sh-table tbody tr:hover, .sh-table tbody tr.active { background:var(--surface2); }
.sh-table td  { padding:12px 16px;font-size:13px;vertical-align:middle;color:var(--text-sub); }
.sh-table td.num { font-family:var(--mono);font-weight:600;white-space:nowrap;color:var(--text); }
.sh-table tfoot td { padding:12px 16px;font-size:13px;border-top:2px solid var(--border);font-weight:700;color:var(--text); }
.sh-table tfoot td.num { font-family:var(--mono); }
.sh-date      { font-weight:700;color:var(--text);white-space:nowrap; }
.sh-date-sub  { font-size:12px;color:var(--text-dim);margin-top:2px;white-space:nowrap; }
.sh-ellip     { overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.sh-badge     { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap; }
.sh-badge-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0; }
.sh-var       { display:inline-block;font-size:12px;font-weight:700;font-family:var(--mono);padding:2px 8px;border-radius:6px;white-space:nowrap; }
.sh-action    { padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;cursor:pointer;
                font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap;text-decoration:none;display:inline-block; }
.sh-action:hover { border-color:var(--accent);color:var(--accent); }
.sh-empty       { padding:56px 20px;text-align:center; }
.sh-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px; }
.sh-empty-sub   { font-size:13px;color:var(--text-dim); }
.sh-pager     { padding:12px 20px;border-top:1px solid var(--border); }
.sh-pager:empty { display:none; }

/* Detail drawer */
.sh-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px); }
.sh-drawer  { position:fixed;top:0;right:0;bottom:0;z-index:401;width:520px;max-width:100vw;background:var(--surface);border-left:1px solid var(--border);
              box-shadow:-8px 0 40px rgba(26,31,54,.14);display:flex;flex-direction:column;animation:sh-in .22s cubic-bezier(.4,0,.2,1); }
@keyframes sh-in { from { transform:translateX(100%) } to { transform:translateX(0) } }
.sh-d-head  { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0; }
.sh-d-title { font-size:16px;font-weight:800;color:var(--text);margin:0;display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
.sh-d-sub   { font-size:12px;color:var(--text-dim);margin-top:4px;line-height:1.5; }
.sh-d-close { width:32px;height:32px;border-radius:8px;border:none;background:var(--surface2);color:var(--text-sub);cursor:pointer;
              display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background var(--tr); }
.sh-d-close:hover { background:var(--surface3); }
.sh-d-body  { flex:1;overflow-y:auto;overscroll-behavior:contain; }
.sh-d-foot  { padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;align-items:center;gap:8px;flex-shrink:0;flex-wrap:wrap; }
.sh-verdict { margin:16px 22px 4px;padding:12px 14px;border-radius:var(--rsm);border:1px solid var(--border);border-left-width:3px;
              display:flex;justify-content:space-between;align-items:center;gap:12px; }
.sh-verdict-t { font-size:13px;font-weight:700; }
.sh-verdict-s { font-size:12px;color:var(--text-dim);margin-top:2px; }
.sh-verdict-v { font-size:16px;font-weight:800;font-family:var(--mono);white-space:nowrap; }
.sh-sec     { padding:10px 22px 6px;margin-top:10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--accent);
              display:flex;justify-content:space-between;gap:8px; }
.sh-sec span { color:var(--text-dim);letter-spacing:0;text-transform:none;font-weight:600;font-size:11px; }
.sh-row     { display:flex;justify-content:space-between;align-items:center;gap:12px;padding:8px 22px;border-bottom:1px solid var(--border);font-size:13px; }
.sh-row-l   { color:var(--text-sub);min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.sh-row-l small { color:var(--text-dim);font-size:12px; }
.sh-row-v   { font-family:var(--mono);font-weight:600;white-space:nowrap;color:var(--text); }
.sh-row.total { border-top:2px solid var(--border);border-bottom:none; }
.sh-row.total .sh-row-l { color:var(--text);font-weight:700; }
.sh-row.total .sh-row-v { font-size:15px;font-weight:800; }
.sh-none    { padding:8px 22px;font-size:12px;color:var(--text-dim); }
.sh-note    { margin:6px 22px 0;padding:10px 12px;border-left:3px solid var(--accent);font-size:13px;color:var(--text-sub);line-height:1.5; }
.sh-tag     { font-size:10px;font-weight:700;padding:1px 6px;border-radius:5px;background:var(--amber-dim);color:var(--amber);margin-right:4px; }
.sh-btn     { padding:9px 16px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);
              display:inline-flex;align-items:center;gap:6px;white-space:nowrap;text-decoration:none;border:1px solid var(--border);background:var(--surface);color:var(--text-sub); }
.sh-btn:hover { background:var(--surface2);color:var(--text); }
.sh-btn-primary { background:var(--accent);border-color:var(--accent);color:#fff;box-shadow:0 3px 10px rgba(59,111,212,.25); }
.sh-btn-primary:hover { background:var(--accent);color:#fff;opacity:.88; }
.sh-btn-dark { background:var(--text);border-color:var(--text);color:var(--surface); }
.sh-btn-dark:hover { background:var(--text);color:var(--surface);opacity:.88; }

@media (max-width:1100px) { .sh-kpis { grid-template-columns:1fr 1fr; } }
@media (max-width:768px) {
    .sh-title { font-size:19px; }
    .sh-kpi { padding:14px;gap:10px; }
    .sh-kpi-val { font-size:20px; }
    .sh-drawer { left:0;width:auto; }
}
@media (max-width:640px) {
    .sh-pill, .sh-action { min-height:30px !important;min-width:0 !important;padding:5px 10px !important; }
    .sh-back, .sh-d-close { min-height:0 !important;min-width:0 !important;padding:0 !important; }
    .sh-card-head { padding:12px 14px; }
    .sh-row, .sh-sec, .sh-none { padding-left:16px;padding-right:16px; }
    .sh-verdict, .sh-note { margin-left:16px;margin-right:16px; }
}
@media (max-width:480px) { .sh-kpis { grid-template-columns:1fr; } }
</style>

@php
    $fmtVar = fn ($v) => ($v > 0 ? '+' : ($v < 0 ? '−' : '')) . number_format(abs((int) $v));
    $varStyle = fn ($v) => $v < 0
        ? 'background:var(--red-dim);color:var(--red)'
        : ($v > 0 ? 'background:var(--amber-dim);color:var(--amber)' : 'background:var(--green-dim);color:var(--green)');
    $statusBadge = function ($s) {
        if ($s->isOpen())   return ['Open',   'var(--green-dim)',  'var(--green)'];
        if ($s->isLocked()) return ['Locked', 'var(--surface2)',   'var(--text-dim)'];
        return ['Closed', 'var(--accent-dim)', 'var(--accent)'];
    };
    $paidOut = (int) $stats->expenses + (int) $stats->withdrawals;
    $netVar  = (int) $stats->variance;
@endphp

{{-- ── Header ── --}}
<div class="sh-header">
    <div class="sh-head-l">
        <a href="{{ route('shop.day-close.index') }}" class="sh-back" aria-label="Back to register">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div style="min-width:0">
            <h1 class="sh-title">Session history</h1>
            <p class="sh-sub">
                @if ($shopName)<b>{{ $shopName }}</b> · @elseif ($isOwner)<b>All shops</b> · @endif
                {{ number_format($stats->sessions) }} {{ (int) $stats->sessions === 1 ? 'session' : 'sessions' }}
            </p>
        </div>
    </div>
    @if ($isOwner && $shops->count() > 1)
        <select class="sh-select" wire:model.live="shopId" aria-label="Filter by shop">
            <option value="">All shops</option>
            @foreach ($shops as $shop)
                <option value="{{ $shop->id }}">{{ $shop->name }}</option>
            @endforeach
        </select>
    @endif
</div>

@if ((int) $stats->sessions > 0)
{{-- ── KPIs ── --}}
<div class="sh-kpis">
    <div class="sh-kpi">
        <div class="sh-kpi-row">
            <div class="sh-kpi-icon" style="background:var(--accent-dim);color:var(--accent)"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg></div>
            <div><div class="sh-kpi-label">Sessions</div><div class="sh-kpi-sub">All time</div></div>
        </div>
        <div class="sh-kpi-val" style="color:var(--text)">{{ number_format($stats->sessions) }}</div>
        <div class="sh-kpi-divider"></div>
        <div>
            <div class="sh-kpi-stat"><span class="sh-kpi-stat-l">Open</span><span class="sh-kpi-stat-v" style="{{ $stats->open ? 'color:var(--green)' : '' }}">{{ $stats->open }}</span></div>
            <div class="sh-kpi-stat"><span class="sh-kpi-stat-l">Closed, awaiting lock</span><span class="sh-kpi-stat-v">{{ $stats->closed }}</span></div>
            <div class="sh-kpi-stat"><span class="sh-kpi-stat-l">Locked</span><span class="sh-kpi-stat-v">{{ $stats->locked }}</span></div>
        </div>
    </div>

    <div class="sh-kpi">
        <div class="sh-kpi-row">
            <div class="sh-kpi-icon" style="background:var(--success-dim);color:var(--success)"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
            <div><div class="sh-kpi-label">Sales</div><div class="sh-kpi-sub">Across all sessions</div></div>
        </div>
        <div class="sh-kpi-val" style="color:var(--text)">{{ number_format($stats->sales) }}<span class="sh-kpi-unit">RWF</span></div>
        <div class="sh-kpi-divider"></div>
        <div>
            <div class="sh-kpi-stat"><span class="sh-kpi-stat-l">Avg per session</span><span class="sh-kpi-stat-v">{{ number_format($stats->sessions ? $stats->sales / $stats->sessions : 0) }}</span></div>
        </div>
    </div>

    <div class="sh-kpi">
        <div class="sh-kpi-row">
            <div class="sh-kpi-icon" style="background:var(--red-dim);color:var(--red)"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg></div>
            <div><div class="sh-kpi-label">Paid out</div><div class="sh-kpi-sub">Expenses + withdrawals</div></div>
        </div>
        <div class="sh-kpi-val" style="color:{{ $paidOut ? 'var(--red)' : 'var(--text-dim)' }}">{{ number_format($paidOut) }}<span class="sh-kpi-unit">RWF</span></div>
        <div class="sh-kpi-divider"></div>
        <div>
            <div class="sh-kpi-stat"><span class="sh-kpi-stat-l">Expenses</span><span class="sh-kpi-stat-v">{{ number_format($stats->expenses) }}</span></div>
            <div class="sh-kpi-stat"><span class="sh-kpi-stat-l">Withdrawals</span><span class="sh-kpi-stat-v">{{ number_format($stats->withdrawals) }}</span></div>
        </div>
    </div>

    <div class="sh-kpi">
        <div class="sh-kpi-row">
            <div class="sh-kpi-icon" style="background:var(--amber-dim);color:var(--amber)"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg></div>
            <div><div class="sh-kpi-label">Net variance</div><div class="sh-kpi-sub">Counted vs expected cash</div></div>
        </div>
        <div class="sh-kpi-val" style="color:{{ $netVar < 0 ? 'var(--red)' : ($netVar > 0 ? 'var(--amber)' : 'var(--green)') }}">{{ $fmtVar($netVar) }}<span class="sh-kpi-unit">RWF</span></div>
        <div class="sh-kpi-divider"></div>
        <div>
            <div class="sh-kpi-stat"><span class="sh-kpi-stat-l">Balanced days</span><span class="sh-kpi-stat-v" style="color:var(--green)">{{ $stats->balanced }}</span></div>
            <div class="sh-kpi-stat"><span class="sh-kpi-stat-l">Short days</span><span class="sh-kpi-stat-v" style="{{ $stats->short ? 'color:var(--red)' : '' }}">{{ $stats->short }}</span></div>
            <div class="sh-kpi-stat"><span class="sh-kpi-stat-l">Over days</span><span class="sh-kpi-stat-v" style="{{ $stats->over ? 'color:var(--amber)' : '' }}">{{ $stats->over }}</span></div>
        </div>
    </div>
</div>
@endif

{{-- ── Sessions table ── --}}
<div class="sh-card">
    <div class="sh-card-head">
        <span class="sh-card-title">Sessions</span>
        <div class="sh-pills">
            @foreach (['all' => ['All', $stats->sessions], 'open' => ['Open', $stats->open], 'closed' => ['Closed', $stats->closed], 'locked' => ['Locked', $stats->locked]] as $key => $__p)
                <button type="button" class="sh-pill {{ $status === $key ? 'active' : '' }}" wire:click="setStatus('{{ $key }}')">
                    {{ $__p[0] }}<span class="sh-pill-n">{{ $__p[1] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    @if ($sessions->isEmpty())
        <div class="sh-empty">
            <div class="sh-empty-title">{{ $status === 'all' ? 'No sessions yet' : 'No ' . $status . ' sessions' }}</div>
            <div class="sh-empty-sub">{{ $status === 'all' ? 'Every day the register is opened appears here.' : 'Try another filter.' }}</div>
        </div>
    @else
        @php
            $cols = $showShopColumn
                ? [150, 170, 100, 130, 120, 120, 130, 110, 90]
                : [170, 100, 130, 120, 120, 130, 110, 90];
        @endphp
        <div class="sh-scroll">
            <table class="sh-table" style="min-width:{{ array_sum($cols) }}px">
                <colgroup>@foreach ($cols as $w)<col style="width:{{ $w }}px">@endforeach</colgroup>
                <thead>
                    <tr>
                        <th>Date</th>
                        @if ($showShopColumn)<th>Shop</th>@endif
                        <th>Status</th>
                        <th class="num">Sales</th>
                        <th class="num">Expenses</th>
                        <th class="num">Withdrawals</th>
                        <th class="num">Counted</th>
                        <th class="num">Variance</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sessions as $session)
                        @php
                            [$bLabel, $bBg, $bColor] = $statusBadge($session);
                            $v = (int) ($session->cash_variance ?? 0);
                        @endphp
                        <tr wire:key="sh-{{ $session->id }}" class="{{ $expandedId === $session->id ? 'active' : '' }}" wire:click="toggleExpand({{ $session->id }})">
                            <td>
                                <div class="sh-date">{{ $session->session_date->format('D, d M Y') }}</div>
                                <div class="sh-date-sub">
                                    {{ $session->opened_at ? local_time($session->opened_at)->format('H:i') : '—' }}@if ($session->closed_at) – {{ local_time($session->closed_at)->format('H:i') }}@endif
                                </div>
                            </td>
                            @if ($showShopColumn)<td><div class="sh-ellip" title="{{ $session->shop?->name }}">{{ $session->shop?->name ?? '—' }}</div></td>@endif
                            <td><span class="sh-badge" style="background:{{ $bBg }};color:{{ $bColor }}"><span class="sh-badge-dot" style="background:{{ $bColor }}"></span>{{ $bLabel }}</span></td>
                            @if ($session->isOpen())
                                <td class="num" colspan="3" style="color:var(--text-dim);font-family:var(--font);font-weight:500;font-size:12px" title="Totals are recorded when the register closes">Live — totals recorded at close</td>
                            @else
                                <td class="num">{{ number_format($session->total_sales ?? 0) }}</td>
                                <td class="num" style="{{ ($session->total_expenses ?? 0) ? '' : 'color:var(--text-dim)' }}">{{ number_format($session->total_expenses ?? 0) }}</td>
                                <td class="num" style="{{ ($session->total_withdrawals ?? 0) ? '' : 'color:var(--text-dim)' }}">{{ number_format($session->total_withdrawals ?? 0) }}</td>
                            @endif
                            <td class="num">{{ $session->isOpen() || $session->actual_cash_counted === null ? '—' : number_format($session->actual_cash_counted) }}</td>
                            <td class="num">
                                @if ($session->isOpen())
                                    <span style="color:var(--text-dim)">—</span>
                                @else
                                    <span class="sh-var" style="{{ $varStyle($v) }}">{{ $fmtVar($v) }}</span>
                                @endif
                            </td>
                            <td class="num" wire:click.stop>
                                @if ($session->isOpen())
                                    <a href="{{ route('shop.session.close', ['session' => $session->id]) }}" class="sh-action">Close</a>
                                @else
                                    <button type="button" class="sh-action" wire:click="toggleExpand({{ $session->id }})">View</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($sessions->hasPages())
            <div class="sh-pager">{{ $sessions->links() }}</div>
        @endif
    @endif
</div>

{{-- ── Session detail drawer ── --}}
@if ($expandedId && $expandedSession)
@php
    $sess   = $expandedSession;
    $sv     = (int) ($sess->cash_variance ?? 0);
    [$dLabel, $dBg, $dColor] = $statusBadge($sess);
    $expenses    = $sess->expenses->whereNull('deleted_at');
    $withdrawals = $sess->ownerWithdrawals->whereNull('deleted_at');
    $deposits    = $sess->bankDeposits->whereNull('deleted_at');
    $channels = array_filter([
        ['Cash',          $sess->total_sales_cash ?? 0,          'var(--green)'],
        ['Mobile money',  $sess->total_sales_momo ?? 0,          'var(--accent)'],
        ['Card',          $sess->total_sales_card ?? 0,          'var(--text-sub)'],
        ['Bank transfer', $sess->total_sales_bank_transfer ?? 0, 'var(--violet)'],
        ['Credit',        $sess->total_sales_credit ?? 0,        'var(--amber)'],
    ], fn ($c) => $c[1] > 0);
    $ledger = [
        ['Cash sales',        $sess->total_sales_cash ?? 0,       '+', 'var(--green)'],
        ['Cash repayments',   $sess->total_repayments_cash ?? 0,  '+', 'var(--green)'],
        ['Cash refunds',      $sess->total_refunds_cash ?? 0,     '−', 'var(--red)'],
        ['Cash expenses',     $sess->total_expenses_cash ?? 0,    '−', 'var(--red)'],
        ['Owner withdrawals', $sess->total_withdrawals_cash ?? 0, '−', 'var(--red)'],
        ['Deposited to bank', $sess->cash_deposits ?? 0,          '−', 'var(--red)'],
    ];
@endphp
<div x-data @keydown.escape.window="$wire.closeDetail()" wire:key="sh-drawer-{{ $sess->id }}">
    <div class="sh-overlay" wire:click="closeDetail"></div>
    <aside class="sh-drawer" role="dialog" aria-modal="true" aria-labelledby="sh-d-title">
        <div class="sh-d-head">
            <div style="min-width:0">
                <h2 class="sh-d-title" id="sh-d-title">
                    {{ $sess->session_date->format('l, d M Y') }}
                    <span class="sh-badge" style="background:{{ $dBg }};color:{{ $dColor }}"><span class="sh-badge-dot" style="background:{{ $dColor }}"></span>{{ $dLabel }}</span>
                </h2>
                <div class="sh-d-sub">
                    @if ($sess->shop){{ $sess->shop->name }} · @endif
                    @if ($sess->opened_at)Opened {{ local_time($sess->opened_at)->format('H:i') }}@if ($sess->openedBy) by {{ $sess->openedBy->name }}@endif @endif
                    @if ($sess->closed_at)<br>Closed {{ local_time($sess->closed_at)->format('H:i') }}@if ($sess->closedBy) by {{ $sess->closedBy->name }}@endif @endif
                    @if ($sess->locked_at)<br>Locked {{ local_time($sess->locked_at)->format('d M, H:i') }}@if ($sess->lockedBy) by {{ $sess->lockedBy->name }}@endif @endif
                </div>
            </div>
            <button type="button" class="sh-d-close" wire:click="closeDetail" aria-label="Close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="sh-d-body">
            @if ($sess->isOpen())
                <div class="sh-verdict" style="border-left-color:var(--green)">
                    <div><div class="sh-verdict-t" style="color:var(--green)">Still open</div><div class="sh-verdict-s">Sales and cash totals are recorded when the register closes.@if ($sess->session_date->isSameDay(business_today()) && ! $isOwner) <a href="{{ route('shop.day-close.index') }}" style="color:var(--accent);font-weight:600;text-decoration:none">See live figures →</a>@endif</div></div>
                </div>
            @elseif ($sv === 0)
                <div class="sh-verdict" style="border-left-color:var(--green)">
                    <div><div class="sh-verdict-t" style="color:var(--green)">Balanced</div><div class="sh-verdict-s">The counted cash matched the expected cash.</div></div>
                    <div class="sh-verdict-v" style="color:var(--green)">0</div>
                </div>
            @elseif ($sv < 0)
                <div class="sh-verdict" style="border-left-color:var(--red)">
                    <div><div class="sh-verdict-t" style="color:var(--red)">Short</div><div class="sh-verdict-s">Counted less than expected — recorded as a cash-shortage expense.</div></div>
                    <div class="sh-verdict-v" style="color:var(--red)">{{ $fmtVar($sv) }}</div>
                </div>
            @else
                <div class="sh-verdict" style="border-left-color:var(--amber)">
                    <div><div class="sh-verdict-t" style="color:var(--amber)">Over</div><div class="sh-verdict-s">Counted more than expected — the extra stayed in the drawer.</div></div>
                    <div class="sh-verdict-v" style="color:var(--amber)">{{ $fmtVar($sv) }}</div>
                </div>
            @endif

            @if ($sess->notes)
                <div class="sh-note">{{ $sess->notes }}</div>
            @endif

            @unless ($sess->isOpen())
                <div class="sh-sec">Cash drawer</div>
                <div class="sh-row"><span class="sh-row-l">Opening balance</span><span class="sh-row-v">{{ number_format($sess->opening_balance ?? 0) }}</span></div>
                @foreach ($ledger as $__l)
                    @if ($__l[1] > 0)
                        <div class="sh-row"><span class="sh-row-l">{{ $__l[0] }}</span><span class="sh-row-v" style="color:{{ $__l[3] }}">{{ $__l[2] }}{{ number_format($__l[1]) }}</span></div>
                    @endif
                @endforeach
                <div class="sh-row"><span class="sh-row-l" style="color:var(--text);font-weight:600">Expected</span><span class="sh-row-v">{{ number_format($sess->expected_cash ?? 0) }}</span></div>
                <div class="sh-row"><span class="sh-row-l" style="color:var(--text);font-weight:600">Counted</span><span class="sh-row-v">{{ $sess->actual_cash_counted !== null ? number_format($sess->actual_cash_counted) : '—' }}</span></div>
                @if (($sess->cash_to_owner_momo ?? 0) > 0)
                    <div class="sh-row"><span class="sh-row-l">Sent to owner (MoMo)@if ($sess->owner_momo_reference) <small>· {{ $sess->owner_momo_reference }}</small>@endif</span><span class="sh-row-v">−{{ number_format($sess->cash_to_owner_momo) }}</span></div>
                @endif
                <div class="sh-row total"><span class="sh-row-l">Retained in drawer</span><span class="sh-row-v">{{ number_format($sess->cash_retained ?? 0) }} <small style="font-size:11px;font-weight:500;color:var(--text-dim)">RWF</small></span></div>
            @endunless

            @unless ($sess->isOpen())
            <div class="sh-sec">Sales by channel <span>{{ $sess->transaction_count ?? 0 }} transactions</span></div>
            @forelse ($channels as $__c)
                <div class="sh-row"><span class="sh-row-l"><span class="sh-badge-dot" style="background:{{ $__c[2] }};display:inline-block;margin-right:8px"></span>{{ $__c[0] }}</span><span class="sh-row-v">{{ number_format($__c[1]) }}</span></div>
            @empty
                <div class="sh-none">No sales recorded.</div>
            @endforelse
            @if (count($channels))
                <div class="sh-row total"><span class="sh-row-l">Total sales</span><span class="sh-row-v">{{ number_format($sess->total_sales ?? 0) }} <small style="font-size:11px;font-weight:500;color:var(--text-dim)">RWF</small></span></div>
            @endif
            @if (($sess->total_repayments ?? 0) > 0)
                <div class="sh-row"><span class="sh-row-l">Credit repayments received</span><span class="sh-row-v" style="color:var(--green)">+{{ number_format($sess->total_repayments) }}</span></div>
            @endif
            @endunless

            <div class="sh-sec">Expenses <span>{{ $expenses->count() }} · {{ number_format($expenses->sum('amount')) }} RWF</span></div>
            @forelse ($expenses as $exp)
                <div class="sh-row">
                    <span class="sh-row-l" title="{{ $exp->description }}">
                        @if ($exp->is_system_generated)<span class="sh-tag">Auto</span>@endif{{ $exp->category->name ?? 'Expense' }}@if ($exp->description) <small>— {{ $exp->description }}</small>@endif
                    </span>
                    <span class="sh-row-v">{{ number_format($exp->amount) }}</span>
                </div>
            @empty
                <div class="sh-none">None recorded.</div>
            @endforelse

            <div class="sh-sec">Owner withdrawals <span>{{ $withdrawals->count() }} · {{ number_format($withdrawals->sum('amount')) }} RWF</span></div>
            @forelse ($withdrawals as $wd)
                <div class="sh-row">
                    <span class="sh-row-l" title="{{ $wd->reason }}">{{ $wd->reason ?: 'Owner withdrawal' }} <small>· {{ $wd->isCash() ? 'Cash' : 'MoMo' }}</small></span>
                    <span class="sh-row-v">{{ number_format($wd->amount) }}</span>
                </div>
            @empty
                <div class="sh-none">None recorded.</div>
            @endforelse

            @if ($deposits->isNotEmpty())
                <div class="sh-sec">Bank deposits <span>{{ $deposits->count() }} · {{ number_format($deposits->sum('amount')) }} RWF</span></div>
                @foreach ($deposits as $dep)
                    <div class="sh-row">
                        <span class="sh-row-l">{{ local_time($dep->deposited_at)?->format('H:i') }} <small>· {{ ($dep->source ?? 'cash') === 'mobile_money' ? 'MoMo' : 'Cash' }}@if ($dep->bank_reference) · {{ $dep->bank_reference }}@endif</small></span>
                        <span class="sh-row-v">{{ number_format($dep->amount) }}</span>
                    </div>
                @endforeach
            @endif
            <div style="height:16px"></div>
        </div>

        <div class="sh-d-foot" x-data="{ c:false }">
            @if ($sess->isOpen())
                <a href="{{ route('shop.session.close', ['session' => $sess->id]) }}" class="sh-btn sh-btn-primary">Close this register</a>
            @elseif ($isOwner && ! $sess->isLocked())
                <span x-show="c" x-cloak style="font-size:12px;color:var(--text-dim);margin-right:auto">Locking is permanent — records can't be corrected after.</span>
                <button type="button" class="sh-btn" x-show="!c" @click="c = true">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Lock session
                </button>
                <button type="button" class="sh-btn" x-show="c" x-cloak @click="c = false">Cancel</button>
                <button type="button" class="sh-btn sh-btn-dark" x-show="c" x-cloak wire:click="lockSession({{ $sess->id }})" @click="c = false">Lock permanently</button>
            @else
                <button type="button" class="sh-btn" wire:click="closeDetail">Done</button>
            @endif
        </div>
    </aside>
</div>
@endif
</div>
