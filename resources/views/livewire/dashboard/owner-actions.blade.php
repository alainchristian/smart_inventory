<div wire:poll.30000ms="loadActions"
     style="background:var(--surface);border:1px solid var(--border);
            border-radius:var(--r);overflow:hidden">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:15px;font-weight:700;color:var(--text);
                        display:flex;align-items:center;gap:8px">
                Requires Your Attention
                @if($totalActions > 0)
                <span style="font-size:11px;font-weight:700;padding:2px 8px;
                             border-radius:20px;background:var(--red-dim);
                             color:var(--red);font-family:var(--mono)">
                    {{ $totalActions }}
                </span>
                @endif
            </div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:2px">
                Items blocked waiting for owner action
            </div>
        </div>
        <button wire:click="loadActions"
                wire:loading.class="opacity-50"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);
                       background:var(--surface2);display:flex;align-items:center;
                       justify-content:center;cursor:pointer;color:var(--text-dim);
                       transition:all var(--tr)"
                title="Refresh">
            <svg width="13" height="13" fill="none" stroke="currentColor"
                 stroke-width="2" viewBox="0 0 24 24">
                <polyline points="1 4 1 10 7 10"/>
                <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
            </svg>
        </button>
    </div>

    {{-- Content --}}
    <div class="card-scroll">

        @if(empty($sections))
        {{-- All clear state --}}
        <div style="padding:48px 20px;text-align:center">
            <div style="font-size:36px;margin-bottom:12px">✅</div>
            <div style="font-size:15px;font-weight:700;color:var(--text-sub);
                        margin-bottom:6px">All clear</div>
            <div style="font-size:13px;color:var(--text-dim);line-height:1.5">
                No returns to approve, no discrepancies,
                no pending decisions. The business is running smoothly.
            </div>
        </div>

        @else
        @foreach($sections as $section)

        {{-- Section header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:10px 18px 6px;background:var(--surface2);
                    border-bottom:1px solid var(--border);
                    border-top:{{ !$loop->first ? '1px solid var(--border)' : 'none' }}">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:24px;height:24px;border-radius:6px;
                            background:{{ $section['bg'] }};
                            display:flex;align-items:center;justify-content:center">
                    @if($section['icon'] === 'rotate')
                    <svg width="12" height="12" fill="none" stroke="{{ $section['color'] }}"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <polyline points="1 4 1 10 7 10"/>
                        <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
                    </svg>
                    @elseif($section['icon'] === 'alert-triangle')
                    <svg width="12" height="12" fill="none" stroke="{{ $section['color'] }}"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    @elseif($section['icon'] === 'package-x')
                    <svg width="12" height="12" fill="none" stroke="{{ $section['color'] }}"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                        <line x1="9.5" y1="9.5" x2="14.5" y2="14.5"/>
                        <line x1="14.5" y1="9.5" x2="9.5" y2="14.5"/>
                    </svg>
                    @elseif($section['icon'] === 'credit-card')
                    <svg width="12" height="12" fill="none" stroke="{{ $section['color'] }}"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <rect x="1" y="4" width="22" height="16" rx="2"/>
                        <line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                    @elseif($section['icon'] === 'tag')
                    <svg width="12" height="12" fill="none" stroke="{{ $section['color'] }}"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                    @else
                    <svg width="12" height="12" fill="none" stroke="{{ $section['color'] }}"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 01-3.46 0"/>
                    </svg>
                    @endif
                </div>
                <span style="font-size:12px;font-weight:700;color:{{ $section['color'] }};
                             text-transform:uppercase;letter-spacing:.4px">
                    {{ $section['label'] }}
                </span>
            </div>
            <span style="font-size:11px;font-weight:700;font-family:var(--mono);
                         color:{{ $section['color'] }}">
                {{ $section['count'] }}
            </span>
        </div>

        {{-- Items --}}
        @foreach($section['items'] as $item)

        @if($section['type'] === 'held_approvals')
        {{-- Inline approve/reject card for held sales --}}
        <div style="padding:12px 18px;border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:8px">
                <div style="min-width:0;flex:1">
                    <div style="font-size:13px;font-weight:700;color:var(--text);
                                font-family:var(--mono)">
                        {{ $item['title'] }}
                    </div>
                    <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                        {{ $item['subtitle'] }}
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:12px;font-weight:700;font-family:var(--mono);
                                color:{{ $item['value_color'] }}">
                        {{ $item['value'] }}
                    </div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:2px">
                        {{ $item['age'] }}
                    </div>
                </div>
            </div>
            {{-- Cart preview --}}
            @if(!empty($item['cart_data']))
            <div style="background:var(--surface2);border-radius:6px;padding:7px 10px;
                        margin-bottom:8px;font-size:11px;color:var(--text-sub)">
                @foreach(array_slice($item['cart_data'], 0, 3) as $line)
                <div style="display:flex;justify-content:space-between;padding:2px 0">
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px">
                        {{ $line['product_name'] ?? '—' }}
                    </span>
                    <span style="flex-shrink:0;margin-left:8px;font-family:var(--mono);
                                 {{ !empty($line['price_modified']) ? 'color:var(--amber)' : '' }}">
                        @if(!empty($line['price_modified']))
                            <span style="text-decoration:line-through;opacity:.6">
                                {{ number_format($line['original_price'] ?? 0) }}
                            </span>
                            → {{ number_format($line['price']) }} RWF
                        @else
                            {{ number_format($line['price']) }} RWF
                        @endif
                    </span>
                </div>
                @endforeach
                @if(count($item['cart_data']) > 3)
                <div style="color:var(--text-dim);padding-top:2px">
                    + {{ count($item['cart_data']) - 3 }} more item(s)
                </div>
                @endif
            </div>
            @endif
            {{-- Approve / Reject buttons --}}
            <div style="display:flex;gap:8px">
                <button wire:click="approveHeldSale({{ $item['id'] }})"
                        wire:loading.attr="disabled"
                        style="flex:1;padding:6px 0;border-radius:7px;border:none;cursor:pointer;
                               background:var(--green);color:#fff;font-size:12px;font-weight:700">
                    Approve
                </button>
                <button wire:click="rejectHeldSale({{ $item['id'] }})"
                        wire:confirm="Reject {{ $item['title'] }}? The seller will be notified."
                        style="padding:6px 13px;border-radius:7px;cursor:pointer;
                               border:1px solid var(--red);background:var(--red-dim);
                               color:var(--red);font-size:12px;font-weight:700">
                    Reject
                </button>
            </div>
        </div>

        @else
        {{-- Standard link item --}}
        <a href="{{ $item['link'] }}"
           style="display:flex;align-items:flex-start;justify-content:space-between;
                  gap:12px;padding:11px 18px;border-bottom:1px solid var(--border);
                  text-decoration:none;transition:background var(--tr)"
           onmouseover="this.style.background='var(--surface2)'"
           onmouseout="this.style.background='transparent'">
            <div style="min-width:0;flex:1">
                <div style="font-size:13px;font-weight:600;color:var(--text);
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ $item['title'] }}
                </div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:2px;
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ $item['subtitle'] }}
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-size:12px;font-weight:700;font-family:var(--mono);
                            color:{{ $item['value_color'] }};white-space:nowrap">
                    {{ $item['value'] }}
                </div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:2px">
                    {{ $item['age'] }}
                </div>
            </div>
        </a>
        @endif

        @endforeach

        {{-- View all link (not for inline-action sections) --}}
        @if($section['count'] >= 5 && $section['type'] !== 'held_approvals')
        <a href="{{ $section['items'][0]['link'] }}"
           style="display:block;padding:8px 18px;font-size:12px;font-weight:600;
                  color:{{ $section['color'] }};text-decoration:none;
                  border-bottom:1px solid var(--border);
                  background:{{ $section['bg'] }};opacity:.8">
            View all {{ $section['label'] }} →
        </a>
        @endif

        @endforeach
        @endif

    </div>
</div>
