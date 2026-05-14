@php $isOwner = auth()->user()?->isOwner(); @endphp
<div @if($isOwner) wire:poll.20s="poll" @endif
     style="position:fixed;bottom:0;right:0;width:0;height:0;
            pointer-events:none;overflow:visible;z-index:180;">
@if ($isOwner)

{{-- ══════════════════════════════════════════════════════════════════════
     FAB
══════════════════════════════════════════════════════════════════════ --}}
<button wire:click="open"
        title="Live Transactions"
        class="lf-fab"
        style="border-radius:50%;background:var(--accent);border:none;cursor:pointer;
               display:flex;align-items:center;justify-content:center;
               box-shadow:0 4px 24px rgba(0,0,0,0.22);
               transition:transform 0.15s,box-shadow 0.15s;"
        onmouseenter="this.style.transform='scale(1.08)';this.style.boxShadow='0 6px 32px rgba(0,0,0,0.30)'"
        onmouseleave="this.style.transform='scale(1)';this.style.boxShadow='0 4px 24px rgba(0,0,0,0.22)'"
        onpointerdown="this.style.transform='scale(0.95)'"
        onpointerup="this.style.transform='scale(1)'"
        ontouchstart=""
        >

    <svg width="22" height="22" viewBox="0 0 24 24" fill="white">
        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
    </svg>

    @if ($unread > 0)
        <span style="position:absolute;top:-5px;right:-5px;
                     min-width:20px;height:20px;border-radius:10px;
                     background:var(--red);color:#fff;
                     font-size:10px;font-weight:800;
                     display:flex;align-items:center;justify-content:center;
                     padding:0 4px;border:2px solid #fff;
                     animation:lf-badge-pulse 2s ease-in-out infinite;">
            {{ $unread > 99 ? '99+' : $unread }}
        </span>
    @endif
</button>

{{-- ══════════════════════════════════════════════════════════════════════
     Overlay + Drawer
══════════════════════════════════════════════════════════════════════ --}}
@if ($isOpen)

{{-- Overlay --}}
<div wire:click="close"
     style="position:fixed;inset:0;z-index:181;background:rgba(0,0,0,0.4);
            pointer-events:auto;animation:lf-fade 0.18s ease;"
     aria-hidden="true"></div>

{{-- Drawer shell --}}
<div class="lf-drawer"
     style="display:flex;flex-direction:column;overflow:hidden;
            background:var(--surface2);">

    <div class="lf-drag-handle"></div>

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div style="flex-shrink:0;">

        {{-- Title row --}}
        <div style="padding:16px 20px 14px;display:flex;align-items:center;
                    justify-content:space-between;gap:12px;">
            <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                <div style="width:34px;height:34px;border-radius:10px;
                            background:var(--accent);flex-shrink:0;
                            display:flex;align-items:center;justify-content:center;
                            box-shadow:0 2px 10px rgba(99,102,241,0.30);">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="white">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                </div>
                <div style="min-width:0;">
                    <div style="font-size:14px;font-weight:700;color:var(--text);line-height:1.25;">
                        Operations Centre
                    </div>
                    <div style="font-size:11px;color:var(--text-faint);
                                display:flex;align-items:center;gap:5px;margin-top:1px;">
                        <span style="width:5px;height:5px;border-radius:50%;background:#10b981;
                                     flex-shrink:0;animation:lf-live-pulse 2s ease-in-out infinite;"></span>
                        Live · every 20s
                    </div>
                </div>
            </div>
            <button wire:click="close"
                    style="width:28px;height:28px;border-radius:7px;border:none;
                           background:var(--surface);cursor:pointer;flex-shrink:0;
                           display:flex;align-items:center;justify-content:center;
                           color:var(--text-faint);transition:background 0.1s;"
                    onmouseenter="this.style.background='var(--surface-raised)'"
                    onmouseleave="this.style.background='var(--surface)'">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Tab bar — underline style --}}
        <div style="display:flex;padding:0 20px;border-bottom:1px solid var(--border);">
            @php
                $tabs = [
                    'transactions' => ['label' => 'Money',     'path' => 'M13 2L3 14h9l-1 8 10-12h-9l1-8z', 'fill' => true],
                    'movements'    => ['label' => 'Movements', 'path' => 'M5 12h14M12 5l7 7-7 7',           'fill' => false],
                    'stock'        => ['label' => 'Stock',     'path' => 'M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z', 'fill' => false],
                ];
            @endphp
            @foreach ($tabs as $key => $tab)
                @php $isActive = $activeTab === $key; @endphp
                <button wire:click="setTab('{{ $key }}')"
                        class="lf-tab-btn"
                        style="flex:1;padding:11px 2px 12px;font-size:12px;font-weight:600;
                               border:none;border-bottom:2px solid {{ $isActive ? 'var(--accent)' : 'transparent' }};
                               background:transparent;cursor:pointer;margin-bottom:-1px;
                               display:flex;align-items:center;justify-content:center;gap:5px;
                               color:{{ $isActive ? 'var(--accent)' : 'var(--text-dim)' }};
                               transition:color 0.15s,border-color 0.15s;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                         stroke="{{ $isActive ? 'var(--accent)' : 'currentColor' }}"
                         stroke-width="{{ $tab['fill'] ? '0' : '2' }}"
                         stroke-linecap="round" stroke-linejoin="round">
                        @if ($tab['fill'])
                            <path fill="{{ $isActive ? 'var(--accent)' : 'currentColor' }}" d="{{ $tab['path'] }}"/>
                        @else
                            <path d="{{ $tab['path'] }}"/>
                        @endif
                    </svg>
                    {{ $tab['label'] }}
                    @if ($key === 'movements' && count($movements) > 0)
                        <span style="min-width:16px;height:16px;border-radius:8px;padding:0 4px;
                                     font-size:9px;font-weight:700;line-height:16px;text-align:center;
                                     background:{{ $isActive ? 'var(--accent)' : 'var(--accent-dim)' }};
                                     color:{{ $isActive ? 'white' : 'var(--accent)' }};">
                            {{ count($movements) }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Money tab controls --}}
        @if ($activeTab === 'transactions')

        {{-- Active date range indicator --}}
        @php
            [$_rFrom, $_rTo] = match ($period) {
                'yesterday'  => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
                'this_week'  => [now()->startOfWeek(), now()->endOfDay()],
                'this_month' => [now()->startOfMonth(), now()->endOfDay()],
                'last_30'    => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
                'custom'     => [
                    $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->startOfDay(),
                    $dateTo   ? \Carbon\Carbon::parse($dateTo)->endOfDay()     : now()->endOfDay(),
                ],
                default      => [now()->startOfDay(), now()->endOfDay()],
            };
            $_rangeStr = $_rFrom->isSameDay($_rTo)
                ? $_rFrom->format('M j, Y')
                : $_rFrom->format('M j') . ' – ' . $_rTo->format('M j, Y');
        @endphp
        <div style="display:flex;align-items:center;gap:6px;
                    padding:10px 16px 6px;">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                 stroke="var(--text-faint)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span style="font-size:11px;font-weight:600;color:var(--text-dim);">{{ $_rangeStr }}</span>
        </div>

        {{-- Period pills --}}
        <div style="display:flex;gap:2px;padding:0 16px 8px;overflow-x:auto;
                    scrollbar-width:none;-webkit-overflow-scrolling:touch;"
             class="lf-no-scroll">
            @foreach (['today'=>'Today','yesterday'=>'Yesterday','this_week'=>'This Week','this_month'=>'This Month','last_30'=>'Last 30','custom'=>'Custom'] as $key => $label)
                <button wire:click="setPeriod('{{ $key }}')"
                        class="lf-period-btn"
                        style="padding:6px 12px;border-radius:6px;font-size:11px;font-weight:600;
                               white-space:nowrap;cursor:pointer;flex-shrink:0;border:none;
                               background:{{ $period === $key ? 'var(--accent)' : 'transparent' }};
                               color:{{ $period === $key ? 'white' : 'var(--text-dim)' }};
                               transition:all 0.12s;">
                    @if ($key === 'custom')
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                             style="display:inline;vertical-align:-1px;margin-right:2px;">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    @endif
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Custom date inputs --}}
        @if ($period === 'custom')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:0 16px 10px;">
            <div>
                <label style="display:block;font-size:9px;font-weight:700;color:var(--text-faint);
                              text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">From</label>
                <input type="date" wire:model.live="dateFrom"
                       style="width:100%;padding:7px 10px;border-radius:8px;font-size:12px;
                              border:1.5px solid {{ $dateFrom ? 'var(--accent)' : 'var(--border)' }};
                              background:var(--surface);color:var(--text);
                              outline:none;font-family:var(--mono);"
                       max="{{ now()->format('Y-m-d') }}">
            </div>
            <div>
                <label style="display:block;font-size:9px;font-weight:700;color:var(--text-faint);
                              text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">To</label>
                <input type="date" wire:model.live="dateTo"
                       style="width:100%;padding:7px 10px;border-radius:8px;font-size:12px;
                              border:1.5px solid {{ $dateTo ? 'var(--accent)' : 'var(--border)' }};
                              background:var(--surface);color:var(--text);
                              outline:none;font-family:var(--mono);"
                       max="{{ now()->format('Y-m-d') }}"
                       min="{{ $dateFrom ?: '' }}">
            </div>
        </div>
        @endif

        {{-- Direction filter --}}
        <div style="display:flex;align-items:center;padding:0 16px 8px;gap:0;border-bottom:1px solid var(--border);">
            @foreach (['all'=>['l'=>'All','i'=>'≡'],'in'=>['l'=>'Income','i'=>'↑'],'out'=>['l'=>'Expenses','i'=>'↓'],'transfer'=>['l'=>'Transfers','i'=>'⇄']] as $key => $f)
                @php $fActive = $filter === $key; @endphp
                <button wire:click="setFilter('{{ $key }}')"
                        class="lf-filter-btn"
                        style="flex:1;padding:8px 3px;font-size:11px;font-weight:600;border:none;
                               cursor:pointer;white-space:nowrap;border-radius:6px;
                               background:{{ $fActive ? 'var(--accent-dim)' : 'transparent' }};
                               color:{{ $fActive ? 'var(--accent)' : 'var(--text-dim)' }};
                               transition:all 0.12s;">
                    {{ $f['i'] }} {{ $f['l'] }}
                </button>
            @endforeach
        </div>

        @endif {{-- /transactions controls --}}
    </div>

    {{-- ── Content area ────────────────────────────────────────────────── --}}
    <div style="flex:1;overflow-y:auto;padding:0 16px 32px;overscroll-behavior:contain;">

    @if ($activeTab === 'transactions')

        {{-- KPI cards --}}
        @php
            $pIn          = $periodTotals['in']           ?? 0;
            $pSalesIn     = $periodTotals['sales_in']     ?? 0;
            $pRepaymentIn = $periodTotals['repayment_in'] ?? 0;
            $pOut         = $periodTotals['out']          ?? 0;
            $pTransfer    = $periodTotals['transfer']     ?? 0;
            $pWdr         = $periodTotals['withdrawal']   ?? 0;
            $pDep         = $periodTotals['deposit']      ?? 0;
            $pNetCash     = $periodTotals['net_cash']     ?? ($pIn - $pOut - $pTransfer);
            $cashPos      = $pNetCash >= 0;
        @endphp
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;
                    padding:8px 0 4px;">

            <div style="background:var(--surface);border:1px solid var(--border);
                        border-radius:12px;padding:12px 11px 11px;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                    <div style="width:26px;height:26px;border-radius:7px;background:#ecfdf5;
                                flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none"
                             stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="2,12 6,7 10,9 14,4"/>
                            <polyline points="11,4 14,4 14,7"/>
                        </svg>
                    </div>
                    <span style="font-size:8px;font-weight:700;color:var(--text-dim);
                                 text-transform:uppercase;letter-spacing:0.5px;">Sales</span>
                </div>
                <div style="font-size:13px;font-weight:800;color:#10b981;
                            font-family:var(--mono);line-height:1.1;margin-bottom:4px;">
                    RWF {{ number_format($pSalesIn) }}
                </div>
                <div style="font-size:9px;color:var(--text-faint);">
                    @if ($pRepaymentIn > 0)
                        +{{ number_format($pRepaymentIn) }} repaid
                    @else
                        From transactions
                    @endif
                </div>
            </div>

            <div style="background:var(--surface);border:1px solid var(--border);
                        border-radius:12px;padding:12px 11px 11px;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                    <div style="width:26px;height:26px;border-radius:7px;background:#fff1f2;
                                flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                             stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2c0 4-5 6-5 11a5 5 0 0010 0c0-5-5-7-5-11z"/>
                            <path d="M10 15a2 2 0 004 0"/>
                        </svg>
                    </div>
                    <span style="font-size:8px;font-weight:700;color:var(--text-dim);
                                 text-transform:uppercase;letter-spacing:0.5px;">Expenses</span>
                </div>
                <div style="font-size:13px;font-weight:800;color:#ef4444;
                            font-family:var(--mono);line-height:1.1;margin-bottom:4px;">
                    RWF {{ number_format($pOut) }}
                </div>
                <div style="font-size:9px;color:var(--text-faint);">Costs · Refunds</div>
            </div>

            <div style="background:var(--surface);border:1px solid var(--border);
                        border-radius:12px;padding:12px 11px 11px;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                    <div style="width:26px;height:26px;border-radius:7px;background:#eff6ff;
                                flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                             stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="6" width="20" height="14" rx="2"/>
                            <path d="M2 10h20"/>
                            <circle cx="7" cy="15" r="1" fill="#3b82f6" stroke="none"/>
                        </svg>
                    </div>
                    <span style="font-size:8px;font-weight:700;color:var(--text-dim);
                                 text-transform:uppercase;letter-spacing:0.5px;">Net Cash</span>
                </div>
                <div style="font-size:13px;font-weight:800;
                            color:{{ $cashPos ? '#3b82f6' : '#ef4444' }};
                            font-family:var(--mono);line-height:1.1;margin-bottom:4px;">
                    {{ $cashPos ? '' : '−' }}RWF {{ number_format(abs($pNetCash)) }}
                </div>
                <div style="font-size:9px;color:var(--text-faint);line-height:1.7;">
                    <div>Wdr: {{ $pWdr > 0 ? 'RWF '.number_format($pWdr) : '—' }}</div>
                    <div>Dep: {{ $pDep > 0 ? 'RWF '.number_format($pDep) : '—' }}</div>
                </div>
            </div>
        </div>

        @if (count($transactions) === 0)
            <div style="text-align:center;padding:64px 20px;color:var(--text-dim);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                     style="margin:0 auto 12px;opacity:0.25;display:block;">
                    <path fill="currentColor" d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
                <div style="font-size:14px;font-weight:600;margin-bottom:4px;">No transactions</div>
                <div style="font-size:12px;opacity:0.7;">Nothing recorded for this period yet</div>
            </div>
        @else
            @php
                $now        = now();
                $prevDate   = null;
                $newCutoff  = $prevOpenedAt ? \Carbon\Carbon::parse($prevOpenedAt) : null;

                $typeCfg = [
                    // Income (direction: in)
                    'sale'       => ['label'=>'Sale',       'abbr'=>'SAL', 'color'=>'var(--green)', 'bg'=>'var(--green-dim)', 'sign'=>'+'],
                    'repayment'  => ['label'=>'Repayment',  'abbr'=>'REP', 'color'=>'#0ea5e9',      'bg'=>'#e0f2fe',         'sign'=>'+'],
                    // Expenses (direction: out)
                    'return'     => ['label'=>'Refund',     'abbr'=>'REF', 'color'=>'var(--red)',   'bg'=>'var(--red-dim)',   'sign'=>'−'],
                    'expense'    => ['label'=>'Expense',    'abbr'=>'EXP', 'color'=>'#f97316',      'bg'=>'#fff7ed',         'sign'=>'−'],
                    // Transfers (direction: transfer — not expenses, not income)
                    'withdrawal' => ['label'=>'Withdrawal', 'abbr'=>'WDR', 'color'=>'#8b5cf6',      'bg'=>'#f5f3ff',         'sign'=>'⇄'],
                    'deposit'    => ['label'=>'Bank Dep.',  'abbr'=>'DEP', 'color'=>'#8b5cf6',      'bg'=>'#f5f3ff',         'sign'=>'⇄'],
                ];
            @endphp

            @foreach ($transactions as $tx)
                @php
                    $happenedAt = \Carbon\Carbon::parse($tx['happened_at']);
                    $dateLabel  = $happenedAt->isToday()
                        ? 'Today'
                        : ($happenedAt->isYesterday() ? 'Yesterday' : $happenedAt->format('d M Y'));
                    $showDate   = $dateLabel !== $prevDate;
                    $prevDate   = $dateLabel;

                    $diff = $now->diffInSeconds($happenedAt);
                    if ($diff < 60)        $rel = 'Just now';
                    elseif ($diff < 3600)  $rel = floor($diff / 60) . 'm ago';
                    elseif ($diff < 86400) $rel = floor($diff / 3600) . 'h ago';
                    else                   $rel = $happenedAt->format('d M, H:i');

                    $isNew = $newCutoff && $happenedAt->gt($newCutoff);
                    $cfg   = $typeCfg[$tx['type']] ?? ['label'=>ucfirst($tx['type']),'abbr'=>'???','color'=>'var(--text-dim)','bg'=>'var(--surface)','sign'=>''];
                    $sign  = $cfg['sign'];
                @endphp

                {{-- Date separator --}}
                @if ($showDate)
                    <div style="font-size:10px;font-weight:700;color:var(--text-dim);
                                text-transform:uppercase;letter-spacing:0.6px;
                                padding:14px 0 6px;{{ $loop->first ? 'padding-top:14px;' : '' }}">
                        {{ $dateLabel }}
                    </div>
                @endif

                {{-- Transaction row --}}
                <div style="display:flex;align-items:center;gap:10px;
                            padding:10px 10px;border-radius:10px;margin-bottom:3px;
                            background:{{ $isNew ? 'var(--accent-dim)' : 'var(--surface)' }};
                            border:1px solid {{ $isNew ? 'var(--accent)' : 'var(--border)' }};
                            transition:background 0.1s;position:relative;"
                     onmouseenter="this.style.background='var(--surface-raised)';this.style.borderColor='var(--border)'"
                     onmouseleave="this.style.background='{{ $isNew ? 'var(--accent-dim)' : 'var(--surface)' }}';this.style.borderColor='{{ $isNew ? 'var(--accent)' : 'var(--border)' }}'">

                    {{-- NEW dot --}}
                    @if ($isNew)
                        <div style="position:absolute;top:8px;left:8px;
                                    width:6px;height:6px;border-radius:50%;
                                    background:var(--accent);"></div>
                    @endif

                    {{-- Type badge --}}
                    <div style="width:38px;height:38px;border-radius:10px;flex-shrink:0;
                                background:{{ $cfg['bg'] }};
                                display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:9px;font-weight:800;color:{{ $cfg['color'] }};
                                     letter-spacing:-0.3px;">
                            {{ $cfg['abbr'] }}
                        </span>
                    </div>

                    {{-- Details --}}
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:5px;margin-bottom:2px;flex-wrap:wrap;">
                            <span style="font-size:12px;font-weight:700;color:var(--text);
                                         white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
                                         max-width:160px;">
                                {{ $tx['reference'] }}
                            </span>
                            <span style="font-size:9px;padding:1px 6px;border-radius:4px;
                                         background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};
                                         font-weight:700;flex-shrink:0;white-space:nowrap;">
                                {{ $cfg['label'] }}
                            </span>
                            @if ($isNew)
                                <span style="font-size:9px;padding:1px 5px;border-radius:4px;
                                             background:var(--accent);color:white;
                                             font-weight:700;flex-shrink:0;">
                                    NEW
                                </span>
                            @endif
                        </div>

                        <div style="font-size:11px;color:var(--text-dim);
                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <span style="font-weight:600;">{{ $tx['shop_name'] ?? '—' }}</span>
                            @if ($tx['customer'])
                                · {{ $tx['customer'] }}
                            @endif
                            @if ($tx['description'])
                                · <em>{{ Str::limit($tx['description'], 24) }}</em>
                            @endif
                        </div>

                        <div style="font-size:10px;color:var(--text-faint);margin-top:1px;">
                            {{ $tx['actor'] ?? '—' }}
                            · {{ $rel }}
                            @if ($tx['method'])
                                · <span style="text-transform:capitalize;">
                                    {{ str_replace('_', ' ', $tx['method']) }}
                                  </span>
                            @endif
                        </div>
                    </div>

                    {{-- Amount --}}
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:14px;font-weight:800;font-family:var(--mono);
                                    color:{{ $cfg['color'] }};">
                            {{ $sign }}{{ number_format($tx['amount']) }}
                        </div>
                        <div style="font-size:9px;color:var(--text-faint);font-weight:500;">RWF</div>
                    </div>
                </div>
            @endforeach

            <div style="text-align:center;padding:20px 0 0;font-size:11px;color:var(--text-faint);">
                Showing {{ count($transactions) }} transactions
            </div>
        @endif

    @elseif ($activeTab === 'movements')

        @php
            $now       = now();
            $newCutoff = $prevOpenedAt ? \Carbon\Carbon::parse($prevOpenedAt) : null;

            $moveCfg = [
                'transfer'   => ['label' => 'Transfer',   'color' => 'var(--accent)', 'bg' => 'var(--accent-dim)'],
                'sale'       => ['label' => 'Sale Out',   'color' => '#10b981',       'bg' => '#ecfdf5'],
                'return'     => ['label' => 'Return',     'color' => '#f97316',       'bg' => '#fff7ed'],
                'damage'     => ['label' => 'Damaged',    'color' => 'var(--red)',    'bg' => 'var(--red-dim)'],
                'adjustment' => ['label' => 'Adjustment', 'color' => '#8b5cf6',       'bg' => '#f5f3ff'],
            ];

            $typeIcons = [
                'transfer'   => 'M5 12h14M12 5l7 7-7 7',
                'sale'       => 'M12 5v14M5 12l7 7 7-7',
                'return'     => 'M9 14l-4-4 4-4M5 10h11a4 4 0 010 8h-1',
                'damage'     => 'M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
                'adjustment' => 'M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z',
            ];
        @endphp

        @if (count($movements) === 0)
            <div style="text-align:center;padding:64px 20px;color:var(--text-dim);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                     style="margin:0 auto 12px;opacity:0.25;display:block;"
                     stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13" rx="2"/>
                    <path d="M16 8h4l3 3v5h-7V8z"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/>
                    <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
                <div style="font-size:14px;font-weight:600;margin-bottom:4px;">No movements</div>
                <div style="font-size:12px;opacity:0.7;">No box activity in the last 24 hours</div>
            </div>
        @else
            <div style="padding-top:14px;">
                <div style="font-size:10px;font-weight:600;color:var(--text-faint);margin-bottom:10px;
                            text-align:right;">Last 24 hours · {{ count($movements) }} movements</div>

                @foreach ($movements as $mv)
                    @php
                        $movedAt  = \Carbon\Carbon::parse($mv['moved_at']);
                        $diff     = $now->diffInSeconds($movedAt);
                        if ($diff < 60)        $rel = 'Just now';
                        elseif ($diff < 3600)  $rel = floor($diff / 60) . 'm ago';
                        elseif ($diff < 86400) $rel = floor($diff / 3600) . 'h ago';
                        else                   $rel = $movedAt->format('d M, H:i');

                        $isNew = $newCutoff && $movedAt->gt($newCutoff);
                        $type  = $mv['movement_type'];
                        $cfg   = $moveCfg[$type] ?? ['label' => ucfirst($type), 'color' => 'var(--text-dim)', 'bg' => 'var(--surface)'];
                        $icon  = $typeIcons[$type] ?? 'M5 12h14';

                        $fromLabel = $mv['from_location'] ?? ($mv['from_type'] === 'warehouse' ? 'Warehouse' : ($mv['from_type'] === 'shop' ? 'Shop' : 'Origin'));
                        $toLabel   = $mv['to_location']   ?? ($mv['to_type']   === 'warehouse' ? 'Warehouse' : ($mv['to_type']   === 'shop' ? 'Shop'      : 'Destination'));
                    @endphp

                    <div style="display:flex;align-items:flex-start;gap:10px;
                                padding:10px 10px;border-radius:10px;margin-bottom:3px;
                                background:{{ $isNew ? 'var(--accent-dim)' : 'var(--surface)' }};
                                border:1px solid {{ $isNew ? 'var(--accent)' : 'var(--border)' }};
                                position:relative;">

                        @if ($isNew)
                            <div style="position:absolute;top:8px;left:8px;width:6px;height:6px;
                                        border-radius:50%;background:var(--accent);"></div>
                        @endif

                        {{-- Type badge --}}
                        <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;
                                    background:{{ $cfg['bg'] }};
                                    display:flex;align-items:center;justify-content:center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="{{ $cfg['color'] }}" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="{{ $icon }}"/>
                            </svg>
                        </div>

                        {{-- Details --}}
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:5px;margin-bottom:3px;flex-wrap:wrap;">
                                <span style="font-size:12px;font-weight:700;color:var(--text);
                                             font-family:var(--mono);white-space:nowrap;">
                                    {{ $mv['box_code'] }}
                                </span>
                                <span style="font-size:9px;padding:1px 6px;border-radius:4px;
                                             background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};
                                             font-weight:700;white-space:nowrap;">
                                    {{ $cfg['label'] }}
                                </span>
                                @if ($isNew)
                                    <span style="font-size:9px;padding:1px 5px;border-radius:4px;
                                                 background:var(--accent);color:white;font-weight:700;">NEW</span>
                                @endif
                            </div>

                            {{-- Product --}}
                            <div style="font-size:11px;color:var(--text-dim);margin-bottom:2px;
                                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $mv['product_name'] ?? '—' }}
                                @if ($mv['items_moved'])
                                    · <strong style="color:var(--text);">{{ number_format($mv['items_moved']) }}</strong> items
                                @endif
                            </div>

                            {{-- From → To --}}
                            <div style="font-size:10px;color:var(--text-faint);
                                        display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                @if ($mv['from_location'])
                                    <span style="background:var(--surface2);border:1px solid var(--border);
                                                 border-radius:4px;padding:0 5px;line-height:16px;white-space:nowrap;">
                                        {{ $mv['from_location'] }}
                                    </span>
                                    <span style="color:var(--text-faint);">→</span>
                                @endif
                                @if ($mv['to_location'])
                                    <span style="background:var(--surface2);border:1px solid var(--border);
                                                 border-radius:4px;padding:0 5px;line-height:16px;white-space:nowrap;">
                                        {{ $mv['to_location'] }}
                                    </span>
                                @endif
                            </div>

                            {{-- Actor + time --}}
                            <div style="font-size:10px;color:var(--text-faint);margin-top:2px;">
                                {{ $mv['moved_by'] }} · {{ $rel }}
                                @if ($mv['reason'])
                                    · <em>{{ Str::limit($mv['reason'], 28) }}</em>
                                @endif
                            </div>
                        </div>

                        {{-- Box status chip --}}
                        @php
                            $statusColor = match($mv['box_status']) {
                                'full'    => ['c' => '#10b981', 'b' => '#ecfdf5'],
                                'partial' => ['c' => '#f59e0b', 'b' => '#fefce8'],
                                'damaged' => ['c' => 'var(--red)', 'b' => 'var(--red-dim)'],
                                'empty'   => ['c' => 'var(--text-faint)', 'b' => 'var(--surface2)'],
                                default   => ['c' => 'var(--text-dim)', 'b' => 'var(--surface)'],
                            };
                        @endphp
                        <div style="flex-shrink:0;text-align:right;">
                            <span style="font-size:9px;padding:2px 6px;border-radius:5px;font-weight:700;
                                         background:{{ $statusColor['b'] }};color:{{ $statusColor['c'] }};">
                                {{ ucfirst($mv['box_status']) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    @else {{-- stock tab --}}

        {{-- ── Warehouses ──────────────────────────────────────────────────── --}}
        <div style="padding-top:16px;">
            <div style="display:flex;align-items:center;gap:7px;margin-bottom:10px;">
                <div style="width:20px;height:20px;border-radius:5px;background:var(--accent-dim);
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                         stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <span style="font-size:10px;font-weight:700;color:var(--text-dim);
                             text-transform:uppercase;letter-spacing:0.6px;">Warehouses</span>
            </div>

            @forelse ($stockData['warehouses'] as $wh)
                @php $whLow = $wh['box_count'] <= 5; @endphp
                <div style="display:flex;align-items:center;gap:10px;
                            padding:11px 12px;border-radius:10px;margin-bottom:4px;
                            background:var(--surface);border:1px solid var(--border);">
                    <div style="width:32px;height:32px;border-radius:8px;
                                background:var(--accent-dim);flex-shrink:0;
                                display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:9px;font-weight:800;color:var(--accent);letter-spacing:-0.3px;">
                            {{ strtoupper(substr($wh['code'] ?? $wh['name'], 0, 3)) }}
                        </span>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;font-weight:700;color:var(--text);
                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $wh['name'] }}
                        </div>
                        <div style="font-size:10px;color:var(--text-dim);margin-top:1px;">
                            {{ number_format($wh['items_total']) }} items in stock
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:18px;font-weight:800;font-family:var(--mono);
                                    color:{{ $whLow ? 'var(--amber)' : 'var(--text)' }};line-height:1;">
                            {{ $wh['box_count'] }}
                        </div>
                        <div style="font-size:9px;color:var(--text-faint);">boxes</div>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:24px;color:var(--text-faint);font-size:12px;">
                    No active warehouses
                </div>
            @endforelse
        </div>

        {{-- ── Shops ───────────────────────────────────────────────────────── --}}
        <div style="padding-top:20px;">
            <div style="display:flex;align-items:center;gap:7px;margin-bottom:10px;">
                <div style="width:20px;height:20px;border-radius:5px;background:#eff6ff;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                         stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                </div>
                <span style="font-size:10px;font-weight:700;color:var(--text-dim);
                             text-transform:uppercase;letter-spacing:0.6px;">Shops</span>
            </div>

            @forelse ($stockData['shops'] as $shop)
                @php $shopLow = $shop['box_count'] <= 3; @endphp
                <div style="display:flex;align-items:center;gap:10px;
                            padding:11px 12px;border-radius:10px;margin-bottom:4px;
                            background:var(--surface);border:1px solid var(--border);">
                    <div style="width:32px;height:32px;border-radius:8px;
                                background:#eff6ff;flex-shrink:0;
                                display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:9px;font-weight:800;color:#3b82f6;letter-spacing:-0.3px;">
                            {{ strtoupper(substr($shop['code'] ?? $shop['name'], 0, 3)) }}
                        </span>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;font-weight:700;color:var(--text);
                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $shop['name'] }}
                        </div>
                        <div style="font-size:10px;color:var(--text-dim);margin-top:1px;">
                            {{ number_format($shop['items_total']) }} items in stock
                            @if ($shopLow && $shop['box_count'] > 0)
                                · <span style="color:var(--amber);font-weight:600;">Low stock</span>
                            @elseif ($shop['box_count'] === 0)
                                · <span style="color:var(--red);font-weight:600;">Out of stock</span>
                            @endif
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:18px;font-weight:800;font-family:var(--mono);
                                    color:{{ $shop['box_count'] === 0 ? 'var(--red)' : ($shopLow ? 'var(--amber)' : 'var(--text)') }};
                                    line-height:1;">
                            {{ $shop['box_count'] }}
                        </div>
                        <div style="font-size:9px;color:var(--text-faint);">boxes</div>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:24px;color:var(--text-faint);font-size:12px;">
                    No active shops
                </div>
            @endforelse
        </div>

    @endif {{-- /tab content --}}

    </div>

</div>{{-- /drawer --}}
@endif{{-- /isOpen --}}

{{-- ══════════════════════════════════════════════════════════════════════
     Styles
══════════════════════════════════════════════════════════════════════ --}}
<style>
    /* ── Keyframes ──────────────────────────────────────────────────── */
    @keyframes lf-badge-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.5); }
        50%       { box-shadow: 0 0 0 6px rgba(239,68,68,0); }
    }
    @keyframes lf-live-pulse {
        0%, 100% { opacity:1; transform:scale(1); }
        50%       { opacity:0.4; transform:scale(0.75); }
    }
    @keyframes lf-fade    { from{opacity:0} to{opacity:1} }
    @keyframes lf-slide-in { from{transform:translateX(105%)} to{transform:translateX(0)} }
    @keyframes lf-slide-up { from{transform:translateY(100%)} to{transform:translateY(0)} }

    /* ── Scrollbar ──────────────────────────────────────────────────── */
    .lf-no-scroll::-webkit-scrollbar { display:none; }

    /* ── FAB ────────────────────────────────────────────────────────── */
    .lf-fab {
        position: fixed;
        bottom: 28px;
        right: 28px;
        z-index: 180;
        width: 54px;
        height: 54px;
        pointer-events: auto;
    }

    /* ── Drawer — desktop (right panel, below topbar) ───────────────── */
    .lf-drawer {
        position: fixed;
        top: var(--topbar-height, 64px);
        right: 0;
        bottom: 0;
        width: 480px;
        z-index: 182;
        pointer-events: auto;
        box-shadow: -6px 0 48px rgba(0,0,0,0.16);
        animation: lf-slide-in 0.22s cubic-bezier(0.22,1,0.36,1);
    }

    /* ── Drag handle — hidden on desktop ────────────────────────────── */
    .lf-drag-handle { display: none; }

    /* ── Tablet: narrower drawer ────────────────────────────────────── */
    @media (max-width: 1024px) and (min-width: 769px) {
        .lf-drawer { width: 420px; }
    }

    /* ── Mobile & small tablet: bottom sheet ────────────────────────── */
    @media (max-width: 768px) {
        .lf-fab {
            bottom: 20px;
            right: 16px;
            width: 48px;
            height: 48px;
        }

        .lf-drawer {
            top: auto;
            right: 0;
            bottom: 0;
            left: 0;
            width: 100%;
            max-height: 88vh;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -8px 48px rgba(0,0,0,0.20);
            animation: lf-slide-up 0.28s cubic-bezier(0.22,1,0.36,1);
        }

        .lf-drag-handle {
            display: block;
            width: 36px;
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            margin: 10px auto 2px;
            flex-shrink: 0;
        }

        /* Larger touch targets */
        .lf-tab-btn    { min-height: 44px !important; font-size: 13px !important; }
        .lf-period-btn { min-height: 36px !important; padding: 7px 13px !important; font-size: 12px !important; }
        .lf-filter-btn { min-height: 40px !important; padding: 10px 3px !important; font-size: 12px !important; }
    }

    /* ── Small phones ───────────────────────────────────────────────── */
    @media (max-width: 390px) {
        .lf-drawer { max-height: 92vh; }
        .lf-tab-btn { font-size: 11px !important; gap: 3px !important; }
    }
</style>

@endif{{-- /isOwner --}}
</div>{{-- /root --}}
