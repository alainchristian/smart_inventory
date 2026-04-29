<div>
    {{-- ── Session Dropdown Selector ── --}}
    <div style="padding:10px 10px 0;" x-data="{ open: false }" @click.outside="open = false">

        {{-- Trigger button --}}
        <button @click="open = !open"
                style="width:100%;display:flex;align-items:center;justify-content:space-between;
                       padding:9px 12px;border-radius:10px;border:1.5px solid var(--border);
                       background:var(--surface2);cursor:pointer;transition:border-color 0.15s;"
                onmouseover="this.style.borderColor='var(--accent)'"
                onmouseout="this.style.borderColor='var(--border)'">
            <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                @if ($selectedSession)
                    @if ($selectedSession->isOpen())
                        <span style="width:7px;height:7px;border-radius:50%;background:var(--green);
                                     flex-shrink:0;animation:pulse 2s infinite;"></span>
                    @else
                        <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;
                                     background:{{ $selectedSession->isLocked() ? '#94a3b8' : 'var(--amber)' }};"></span>
                    @endif
                    <span style="font-size:12px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $selectedSession->session_date->isToday() ? 'Today' : $selectedSession->session_date->format('d M Y') }}
                        <span style="font-weight:400;color:var(--text-dim);font-size:11px;margin-left:4px;">
                            {{ $selectedSession->opened_at?->format('H:i') }}{{ $selectedSession->closed_at ? ' – ' . $selectedSession->closed_at->format('H:i') : '' }}
                        </span>
                    </span>
                    @if ($selectedSession->isOpen())
                        <span style="font-size:9px;padding:1px 6px;border-radius:999px;flex-shrink:0;
                                     background:var(--green-dim);color:var(--green);font-weight:700;">Live</span>
                    @endif
                @else
                    <span style="font-size:12px;color:var(--text-dim);">Select a session…</span>
                @endif
            </div>
            <svg class="transition-transform duration-200" :class="{ 'rotate-180': open }"
                 style="width:14px;height:14px;color:var(--text-dim);flex-shrink:0;"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        {{-- Dropdown panel --}}
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             style="position:relative;z-index:20;margin-top:4px;border-radius:12px;
                    border:1px solid var(--border);background:var(--surface);
                    box-shadow:0 8px 24px rgba(0,0,0,0.12);overflow:hidden;">

            {{-- Search --}}
            <div style="padding:8px 8px 6px;border-bottom:1px solid var(--border);background:var(--surface2);">
                <div style="position:relative;">
                    <svg style="position:absolute;left:9px;top:50%;transform:translateY(-50%);
                                width:13px;height:13px;color:var(--text-dim);"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                    <input wire:model.live.debounce.250ms="search"
                           type="text"
                           placeholder="Search by date (e.g. Apr, 2025)…"
                           style="width:100%;padding:7px 10px 7px 28px;border-radius:8px;font-size:12px;
                                  background:var(--surface);border:1px solid var(--border);color:var(--text);
                                  box-sizing:border-box;outline:none;"
                           onfocus="this.style.borderColor='var(--accent)'"
                           onblur="this.style.borderColor='var(--border)'">
                </div>
            </div>

            {{-- Session list --}}
            <div style="max-height:280px;overflow-y:auto;-webkit-overflow-scrolling:touch;">
                @if ($sessions->isEmpty())
                    <div style="padding:20px;text-align:center;font-size:12px;color:var(--text-dim);">No sessions found.</div>
                @else
                    @php $currentMonth = null; @endphp
                    @foreach ($sessions as $sess)
                        @php
                            $month      = $sess->session_date->format('M Y');
                            $isSelected = $viewingSessionId === $sess->id;
                            $isOpen     = $sess->isOpen();
                            $isToday    = $sess->session_date->isToday();
                        @endphp

                        {{-- Month group header --}}
                        @if ($month !== $currentMonth)
                            @php $currentMonth = $month; @endphp
                            <div style="padding:6px 12px 3px;font-size:10px;font-weight:700;text-transform:uppercase;
                                        letter-spacing:0.7px;color:var(--text-dim);background:var(--surface2);
                                        border-bottom:1px solid var(--border);position:sticky;top:0;">
                                {{ $month }}
                            </div>
                        @endif

                        {{-- Session row --}}
                        <button wire:click="selectSession({{ $sess->id }})"
                                @click="open = false; window.dispatchEvent(new CustomEvent('session-selected', { detail: { sessionId: {{ $sess->id }} } }))"
                                style="width:100%;display:flex;align-items:center;justify-content:space-between;
                                       padding:9px 12px;border:none;cursor:pointer;text-align:left;
                                       background:{{ $isSelected ? 'var(--accent-dim)' : 'transparent' }};
                                       border-left:3px solid {{ $isSelected ? 'var(--accent)' : 'transparent' }};
                                       transition:background 0.1s;"
                                onmouseover="if(!{{ $isSelected ? 'true' : 'false' }}) this.style.background='var(--surface2)'"
                                onmouseout="if(!{{ $isSelected ? 'true' : 'false' }}) this.style.background='transparent'">

                            <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                                @if ($isOpen)
                                    <span style="width:6px;height:6px;border-radius:50%;background:var(--green);
                                                 flex-shrink:0;animation:pulse 2s infinite;"></span>
                                @else
                                    <span style="width:6px;height:6px;border-radius:50%;flex-shrink:0;
                                                 background:{{ $sess->isLocked() ? '#94a3b8' : '#f59e0b' }};"></span>
                                @endif
                                <div style="min-width:0;">
                                    <div style="font-size:12px;font-weight:{{ $isSelected ? '700' : '500' }};
                                                color:{{ $isSelected ? 'var(--accent)' : 'var(--text)' }};">
                                        {{ $isToday ? 'Today' : $sess->session_date->format('d') }}
                                        <span style="color:var(--text-dim);font-weight:400;">
                                            {{ $sess->session_date->format('D') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                @if ($isOpen)
                                    <span style="font-size:9px;padding:1px 6px;border-radius:999px;
                                                 background:var(--green-dim);color:var(--green);font-weight:700;">Live</span>
                                @elseif ($sess->isLocked())
                                    <span style="font-size:9px;padding:1px 6px;border-radius:999px;
                                                 background:var(--surface2);color:#94a3b8;border:1px solid var(--border);">Locked</span>
                                @else
                                    <span style="font-size:10px;color:var(--text-dim);">
                                        {{ $sess->opened_at?->format('H:i') }}
                                    </span>
                                @endif
                            </div>
                        </button>
                    @endforeach

                    @if ($sessions->count() === 60)
                        <div style="padding:8px 12px;text-align:center;font-size:11px;color:var(--text-dim);
                                    border-top:1px solid var(--border);">
                            Showing 60 most recent — search to narrow down
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- ── Activity Feed ── --}}
    @if ($viewingSessionId)
        <div style="margin-top:8px;border-top:1px solid var(--border);padding:10px;">
            <livewire:shop.day-close.session-activity-feed
                :dailySessionId="$viewingSessionId"
                :wire:key="'feed-' . $viewingSessionId" />
        </div>
    @endif
</div>
