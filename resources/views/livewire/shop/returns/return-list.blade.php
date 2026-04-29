@if($sessionBlocked)
    <x-session-gate-blocked
        :reason="$sessionBlockReason"
        :session-date="$blockedSessionDate"
        :session-id="$blockedSessionId"
    />
@else
<div style="font-family:var(--font);color:var(--text);">

    {{-- Flash messages --}}
    @if (session()->has('error'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;
                    background:var(--red-dim);border:1px solid var(--red);
                    font-size:13px;font-weight:600;color:var(--red);">
            {{ session('error') }}
        </div>
    @endif
    @if (session()->has('success'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;
                    background:var(--green-dim);border:1px solid var(--green);
                    font-size:13px;font-weight:600;color:var(--green);">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── HEADER ──────────────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div>
            <h1 style="font-size:22px;font-weight:800;color:var(--text);margin:0;">Returns</h1>
            <p style="font-size:13px;color:var(--text-dim);margin:4px 0 0;">
                @if ($isOwner) All shops · @endif
                Refunds and exchanges
            </p>
        </div>
        @if (!$isOwner)
            <a href="{{ route('shop.returns.create') }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;
                      border-radius:10px;background:var(--accent);color:white;font-size:13px;
                      font-weight:700;text-decoration:none;font-family:var(--font);">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Process Return
            </a>
        @endif
    </div>

    {{-- ── KPI STRIP ────────────────────────────────────────────────────── --}}
    <div class="rl-kpi" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
        @foreach ([
            ['Total Returns',    $kpiStats['total_returns']    ?? 0, null,  'var(--text)'],
            ['Cash Refunds',     $kpiStats['total_refunds']    ?? 0, 'RWF', 'var(--red)'],
            ['Exchanges',        $kpiStats['exchange_count']   ?? 0, null,  'var(--accent)'],
            ['Pending Approval', $kpiStats['pending_approval'] ?? 0, null,  'var(--amber)'],
        ] as [$label, $value, $unit, $color])
            <div style="background:var(--surface2);border:1px solid var(--border);
                        border-radius:12px;padding:14px 16px;">
                <div style="font-size:11px;font-weight:600;color:var(--text-dim);
                            text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
                    {{ $label }}
                </div>
                <div style="font-size:22px;font-weight:800;color:{{ $color }};
                            font-family:var(--mono);letter-spacing:-0.5px;">
                    {{ $unit === 'RWF' ? number_format($value) : $value }}
                    @if ($unit)
                        <span style="font-size:12px;font-weight:400;color:var(--text-dim);">
                            {{ $unit }}
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── PENDING APPROVALS BANNER (owner only) ───────────────────────── --}}
    @if ($isOwner && ($kpiStats['pending_approval'] ?? 0) > 0)
        <div style="padding:12px 16px;border-radius:10px;background:var(--amber-dim);
                    border:1px solid var(--amber);margin-bottom:16px;
                    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg style="width:18px;height:18px;color:var(--amber);flex-shrink:0;"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span style="font-size:13px;font-weight:600;color:var(--amber);">
                    {{ $kpiStats['pending_approval'] }} return(s) waiting for your approval
                </span>
            </div>
            <button wire:click="$set('statusFilter', 'pending_approval')"
                    style="font-size:12px;font-weight:600;color:var(--amber);
                           background:transparent;border:1.5px solid var(--amber);
                           border-radius:6px;padding:5px 12px;cursor:pointer;font-family:var(--font);">
                View Pending
            </button>
        </div>
    @endif

    {{-- ── FILTER BAR ───────────────────────────────────────────────────── --}}
    <div class="rl-filters" style="display:grid;
                grid-template-columns:2fr 1fr 1fr 1fr{{ $isOwner ? ' 1fr' : '' }};
                gap:10px;margin-bottom:16px;align-items:end;">

        <div style="position:relative;">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Return number, customer..."
                   style="width:100%;padding:9px 12px 9px 36px;border-radius:8px;font-size:13px;
                          background:var(--surface2);border:1px solid var(--border);
                          color:var(--text);box-sizing:border-box;transition:border-color 0.15s;"
                   onfocus="this.style.borderColor='var(--accent)';"
                   onblur="this.style.borderColor='var(--border)';">
            <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                        width:15px;height:15px;color:var(--text-dim);" fill="none"
                 stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <select wire:model.live="statusFilter"
                style="padding:9px 12px;border-radius:8px;font-size:12px;font-weight:600;
                       background:var(--surface2);border:1px solid var(--border);
                       color:var(--text);cursor:pointer;font-family:var(--font);
                       transition:border-color 0.15s;"
                onfocus="this.style.borderColor='var(--accent)';"
                onblur="this.style.borderColor='var(--border)';">
            <option value="all">All Status</option>
            <option value="pending_approval">Pending Approval</option>
            <option value="approved">Approved</option>
        </select>

        <select wire:model.live="typeFilter"
                style="padding:9px 12px;border-radius:8px;font-size:12px;font-weight:600;
                       background:var(--surface2);border:1px solid var(--border);
                       color:var(--text);cursor:pointer;font-family:var(--font);
                       transition:border-color 0.15s;"
                onfocus="this.style.borderColor='var(--accent)';"
                onblur="this.style.borderColor='var(--border)';">
            <option value="all">All Types</option>
            <option value="refund">Refunds</option>
            <option value="exchange">Exchanges</option>
        </select>

        <input type="date"
               wire:model.live="dateFrom"
               style="padding:9px 12px;border-radius:8px;font-size:12px;
                      background:var(--surface2);border:1px solid var(--border);
                      color:var(--text);transition:border-color 0.15s;"
               onfocus="this.style.borderColor='var(--accent)';"
               onblur="this.style.borderColor='var(--border)';">

        @if ($isOwner)
            <select wire:model.live="shopFilter"
                    style="padding:9px 12px;border-radius:8px;font-size:12px;font-weight:600;
                           background:var(--surface2);border:1px solid var(--border);
                           color:var(--text);cursor:pointer;font-family:var(--font);
                           transition:border-color 0.15s;"
                    onfocus="this.style.borderColor='var(--accent)';"
                    onblur="this.style.borderColor='var(--border)';">
                <option value="all">All Shops</option>
                @foreach ($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- ── RETURNS TABLE ────────────────────────────────────────────────── --}}
    <div style="background:var(--surface2);border:1px solid var(--border);
                border-radius:14px;overflow:hidden;"
         x-data="{ expandedRow: null }">

        {{-- Column header --}}
        <div class="rl-row-grid" style="display:grid;
                    grid-template-columns:140px {{ $isOwner ? '120px ' : '' }}1fr 90px 110px 70px 80px 110px;
                    padding:10px 16px;border-bottom:2px solid var(--border);
                    background:var(--surface);">
            @foreach (array_filter([
                'Return #',
                $isOwner ? 'Shop' : null,
                'Customer',
                'Type',
                'Refund',
                'Items',
                'Date',
                'Actions',
            ]) as $col)
                <div style="font-size:10px;font-weight:700;color:var(--text-dim);
                            text-transform:uppercase;letter-spacing:0.5px;">
                    {{ $col }}
                </div>
            @endforeach
        </div>

        {{-- Rows --}}
        @forelse ($returns as $return)
            @php
                $reasonStyles = [
                    'defective'        => 'background:var(--red-dim);color:var(--red);',
                    'damaged'          => 'background:var(--red-dim);color:var(--red);',
                    'wrong_item'       => 'background:var(--amber-dim);color:var(--amber);',
                    'expired'          => 'background:var(--surface);color:var(--text-dim);border:1px solid var(--border);',
                    'customer_request' => 'background:var(--accent-dim);color:var(--accent);',
                    'other'            => 'background:var(--surface);color:var(--text-dim);border:1px solid var(--border);',
                ];
            @endphp

            <div style="border-bottom:1px solid var(--border);">

                {{-- Main row --}}
                <div class="rl-row-grid" style="display:grid;
                            grid-template-columns:140px {{ $isOwner ? '120px ' : '' }}1fr 90px 110px 70px 80px 110px;
                            padding:12px 16px;cursor:pointer;transition:background 0.1s;"
                     onmouseover="this.style.background='var(--surface)';"
                     onmouseout="this.style.background='transparent';"
                     @click="expandedRow === {{ $return->id }} ? expandedRow = null : expandedRow = {{ $return->id }}">

                    {{-- Return number --}}
                    <div style="font-size:12px;font-weight:700;color:var(--text);
                                font-family:var(--mono);">
                        {{ $return->return_number }}
                    </div>

                    {{-- Shop (owner only) --}}
                    @if ($isOwner)
                        <div style="font-size:12px;color:var(--text-dim);">
                            {{ $return->shop->name ?? '—' }}
                        </div>
                    @endif

                    {{-- Customer --}}
                    <div>
                        <div style="font-size:12px;font-weight:600;color:var(--text);">
                            {{ $return->customer_name ?? 'Walk-in' }}
                        </div>
                        @if ($return->customer_phone)
                            <div style="font-size:10px;color:var(--text-dim);margin-top:1px;">
                                {{ $return->customer_phone }}
                            </div>
                        @endif
                    </div>

                    {{-- Type badge --}}
                    <div>
                        <span style="padding:3px 8px;border-radius:5px;font-size:10px;font-weight:700;
                                     {{ $return->is_exchange
                                         ? 'background:var(--accent-dim);color:var(--accent);'
                                         : 'background:var(--green-dim);color:var(--green);' }}">
                            {{ $return->is_exchange ? 'Exchange' : 'Refund' }}
                        </span>
                    </div>

                    {{-- Refund amount --}}
                    <div style="font-size:12px;font-weight:700;
                                color:{{ $return->is_exchange ? 'var(--text-dim)' : 'var(--red)' }};
                                font-family:var(--mono);">
                        @if ($return->is_exchange)
                            —
                        @else
                            {{ number_format($return->refund_amount) }}
                            <span style="font-size:10px;font-weight:400;font-family:var(--font);
                                         color:var(--text-dim);">RWF</span>
                        @endif
                    </div>

                    {{-- Items --}}
                    <div style="font-size:12px;color:var(--text-dim);">
                        {{ $return->items->count() }}
                    </div>

                    {{-- Date --}}
                    <div>
                        <div style="font-size:11px;color:var(--text);">
                            {{ $return->processed_at->format('d M Y') }}
                        </div>
                        <div style="font-size:10px;color:var(--text-dim);margin-top:1px;">
                            {{ $return->processed_at->format('H:i') }}
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div @click.stop style="display:flex;align-items:center;gap:6px;">
                        @if ($isOwner && !$return->approved_at)
                            <button wire:click="approveReturn({{ $return->id }})"
                                    wire:confirm="Approve return {{ $return->return_number }}?"
                                    wire:loading.attr="disabled"
                                    wire:target="approveReturn({{ $return->id }})"
                                    style="padding:5px 10px;border-radius:6px;font-size:11px;
                                           font-weight:700;border:1.5px solid var(--green);
                                           color:var(--green);background:var(--green-dim);
                                           cursor:pointer;font-family:var(--font);">
                                <span wire:loading.remove wire:target="approveReturn({{ $return->id }})">Approve</span>
                                <span wire:loading wire:target="approveReturn({{ $return->id }})" style="display:none;">…</span>
                            </button>
                        @elseif ($return->approved_at)
                            <span style="font-size:10px;font-weight:600;color:var(--green);">
                                ✓ Approved
                            </span>
                        @else
                            <span style="font-size:10px;color:var(--text-dim);">
                                Pending
                            </span>
                        @endif
                        <button @click="expandedRow === {{ $return->id }} ? expandedRow = null : expandedRow = {{ $return->id }}"
                                style="width:24px;height:24px;border-radius:6px;border:1px solid var(--border);
                                       background:transparent;display:flex;align-items:center;justify-content:center;
                                       cursor:pointer;color:var(--text-dim);">
                            <svg style="width:12px;height:12px;transition:transform 0.2s;"
                                 :style="expandedRow === {{ $return->id }} ? 'transform:rotate(180deg)' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                </div>{{-- end main row --}}

                {{-- Expanded detail --}}
                <div x-show="expandedRow === {{ $return->id }}"
                     x-cloak
                     style="padding:14px 16px 16px;background:var(--surface);
                            border-top:1px solid var(--border);">

                    <div class="rl-detail" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">

                        {{-- Items breakdown --}}
                        <div>
                            <div style="font-size:10px;font-weight:700;color:var(--text-dim);
                                        text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
                                Returned Items
                            </div>
                            @foreach ($return->items as $item)
                                <div style="display:flex;justify-content:space-between;
                                            padding:6px 0;border-bottom:1px solid var(--border);
                                            font-size:12px;">
                                    <span style="color:var(--text);">
                                        {{ $item->product->name ?? '—' }}
                                    </span>
                                    <div style="text-align:right;flex-shrink:0;">
                                        <span style="color:var(--text);font-weight:600;">
                                            ×{{ $item->quantity_returned }}
                                        </span>
                                        @if ($item->quantity_damaged > 0)
                                            <span style="color:var(--red);font-size:10px;margin-left:4px;">
                                                ({{ $item->quantity_damaged }} dmg)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Return info --}}
                        <div>
                            <div style="font-size:10px;font-weight:700;color:var(--text-dim);
                                        text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
                                Details
                            </div>
                            <div style="font-size:12px;color:var(--text-dim);line-height:1.8;">
                                <div>Reason:
                                    <span style="color:var(--text);font-weight:600;">
                                        {{ ucfirst(str_replace('_', ' ', $return->reason->value ?? $return->reason)) }}
                                    </span>
                                </div>
                                @if (!$return->is_exchange)
                                    <div>Refund via:
                                        <span style="color:var(--text);font-weight:600;">
                                            {{ ucfirst(str_replace('_', ' ', $return->refund_method ?? '—')) }}
                                        </span>
                                    </div>
                                @endif
                                @if ($return->sale)
                                    <div>Sale:
                                        <span style="color:var(--accent);font-weight:600;">
                                            {{ $return->sale->sale_number }}
                                        </span>
                                    </div>
                                @endif
                                <div>By:
                                    <span style="color:var(--text);font-weight:600;">
                                        {{ $return->processedBy->name ?? '—' }}
                                    </span>
                                </div>
                                @if ($return->notes)
                                    <div style="margin-top:6px;padding:6px 8px;border-radius:6px;
                                                background:var(--surface2);color:var(--text-dim);
                                                font-size:11px;line-height:1.5;">
                                        {{ $return->notes }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Approval status --}}
                        <div>
                            <div style="font-size:10px;font-weight:700;color:var(--text-dim);
                                        text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
                                Approval
                            </div>
                            @if ($return->approved_at)
                                <div style="padding:10px 12px;border-radius:8px;
                                            background:var(--green-dim);border:1px solid var(--green);">
                                    <div style="font-size:12px;font-weight:700;color:var(--green);">
                                        ✓ Approved
                                    </div>
                                    <div style="font-size:11px;color:var(--text-dim);margin-top:3px;">
                                        By {{ $return->approvedBy->name ?? '—' }}
                                        · {{ $return->approved_at->format('d M Y') }}
                                    </div>
                                </div>
                            @else
                                <div style="padding:10px 12px;border-radius:8px;
                                            background:var(--amber-dim);border:1px solid var(--amber);">
                                    <div style="font-size:12px;font-weight:600;color:var(--amber);">
                                        Pending approval
                                    </div>
                                    @if ($isOwner)
                                        <button wire:click="approveReturn({{ $return->id }})"
                                                wire:confirm="Approve this return?"
                                                wire:loading.attr="disabled"
                                                wire:target="approveReturn({{ $return->id }})"
                                                style="margin-top:8px;width:100%;padding:7px;
                                                       border-radius:6px;font-size:11px;font-weight:700;
                                                       border:1.5px solid var(--green);color:var(--green);
                                                       background:var(--green-dim);cursor:pointer;
                                                       font-family:var(--font);">
                                            <span wire:loading.remove wire:target="approveReturn({{ $return->id }})">Approve Now</span>
                                            <span wire:loading wire:target="approveReturn({{ $return->id }})" style="display:none;">Approving…</span>
                                        </button>
                                    @else
                                        <div style="font-size:11px;color:var(--text-dim);margin-top:3px;">
                                            Awaiting owner review
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

            </div>{{-- end row wrapper --}}

        @empty
            <div style="padding:48px;text-align:center;">
                <div style="font-size:32px;margin-bottom:12px;">📦</div>
                <div style="font-size:14px;font-weight:600;color:var(--text);">No returns found</div>
                <div style="font-size:12px;color:var(--text-dim);margin-top:4px;">
                    No returns match your current filters.
                </div>
                @if (!$isOwner)
                    <a href="{{ route('shop.returns.create') }}"
                       style="display:inline-flex;align-items:center;gap:6px;margin-top:16px;
                              padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;
                              background:var(--accent);color:white;text-decoration:none;">
                        <svg style="width:13px;height:13px;" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Process Return
                    </a>
                @endif
            </div>
        @endforelse

        {{-- Pagination --}}
        @if ($returns->hasPages())
            <div style="padding:14px 16px;border-top:1px solid var(--border);">
                {{ $returns->links() }}
            </div>
        @endif

    </div>{{-- end table container --}}

    <style>
        [x-cloak] { display: none !important; }

        @media (max-width: 1024px) {
            .rl-kpi { grid-template-columns: repeat(2,1fr) !important; }
            .rl-filters { grid-template-columns: 1fr 1fr !important; }
            .rl-row-grid { grid-template-columns: 130px 1fr 90px 110px 80px 110px !important; }
        }
        @media (max-width: 640px) {
            .rl-kpi { grid-template-columns: repeat(2,1fr) !important; }
            .rl-filters { grid-template-columns: 1fr !important; }
            .rl-row-grid { grid-template-columns: 120px 1fr 90px 80px !important; }
            .rl-detail { grid-template-columns: 1fr !important; }
        }
    </style>

</div>
@endif
