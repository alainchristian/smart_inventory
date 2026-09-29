                {{-- Own component (Layout\NotificationBell): the 15s poll re-renders only this,
                     and the lists below render only while the dropdown is open ($panelOpen). --}}
                <div x-data="{ open: false, tab: 'activity' }"
                     x-init="$watch('open', v => { if (v) { tab = {{ $this->unreadActivityCount > 0 ? "'activity'" : ($this->totalPendingActions > 0 ? "'actions'" : "'activity'") }}; $wire.openPanel(); } else { $wire.closePanel(); } })"
                     class="relative" wire:poll.15s>
                    <button @click="open = !open" class="relative w-9 h-9 flex items-center justify-center rounded-lg transition-all"
                            style="background: var(--surface2); border: 1px solid var(--border); color: var(--text-sub);"
                            onmouseover="this.style.background='var(--surface3)'; this.style.color='var(--text)';"
                            onmouseout="this.style.background='var(--surface2)'; this.style.color='var(--text-sub)';">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($this->unreadNotificationsCount > 0)
                            <div class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center text-[10px] font-bold border-2"
                                 style="background: var(--red); color: white; border-color: var(--surface);">
                                {{ $this->unreadNotificationsCount > 9 ? '9+' : $this->unreadNotificationsCount }}
                            </div>
                        @endif
                    </button>

                    <!-- Notifications Dropdown -->
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="tb-notif-dd absolute right-0 mt-2 w-80 sm:w-96 rounded-xl shadow-xl border overflow-hidden"
                         style="background: var(--surface); border-color: var(--border); z-index: 100;"
                         x-cloak>

                        <!-- Tab bar -->
                        <div class="flex border-b" style="border-color: var(--border);">
                            <button @click="tab = 'activity'"
                                    class="flex-1 flex items-center justify-center gap-1.5 py-3 text-[12px] font-600 border-b-2 transition-all"
                                    :style="tab === 'activity'
                                        ? 'border-color: var(--accent); color: var(--accent); font-weight:700;'
                                        : 'border-color: transparent; color: var(--text-dim); font-weight:500;'">
                                {{ __('Activity') }}
                                @if($this->unreadActivityCount > 0)
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                                          style="background: var(--red); color: #fff; line-height:1.2;">
                                        {{ $this->unreadActivityCount > 9 ? '9+' : $this->unreadActivityCount }}
                                    </span>
                                @endif
                            </button>
                            @if(Auth::check() && (Auth::user()->isOwner() || Auth::user()->isAdmin()))
                            <button @click="tab = 'actions'"
                                    class="flex-1 flex items-center justify-center gap-1.5 py-3 text-[12px] border-b-2 transition-all"
                                    :style="tab === 'actions'
                                        ? 'border-color: var(--accent); color: var(--accent); font-weight:700;'
                                        : 'border-color: transparent; color: var(--text-dim); font-weight:500;'">
                                {{ __('Actions') }}
                                @if($this->totalPendingActions > 0)
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                                          style="background: var(--amber); color: #fff; line-height:1.2;">
                                        {{ $this->totalPendingActions > 9 ? '9+' : $this->totalPendingActions }}
                                    </span>
                                @endif
                            </button>
                            @endif
                        </div>

                        @if(!$panelOpen)
                            <div class="p-8 text-center text-[12px]" style="color:var(--text-dim);">{{ __('Loading…') }}</div>
                        @else
                        <!-- ── Activity tab ── -->
                        <div x-show="tab === 'activity'" class="max-h-[420px] overflow-y-auto">
                            @php $notifications = $this->activityNotifications; @endphp
                            @forelse($notifications as $notif)
                            @php
                                $nColors = match($notif['color']) {
                                    'red'    => ['bg' => 'var(--red-dim)',    'ic' => 'var(--red)'],
                                    'green'  => ['bg' => 'var(--green-dim)', 'ic' => 'var(--green)'],
                                    'amber'  => ['bg' => 'var(--amber-dim)', 'ic' => 'var(--amber)'],
                                    default  => ['bg' => 'var(--accent-dim)','ic' => 'var(--accent)'],
                                };
                            @endphp
                            @if($notif['url'])
                                <a href="{{ $notif['url'] }}"
                                   class="flex items-start gap-3 px-4 py-3 border-b transition-all"
                                   style="border-color: var(--border); {{ $notif['unread'] ? 'background: var(--accent-dim);' : '' }}"
                                   onmouseover="this.style.background='var(--surface2)'"
                                   onmouseout="this.style.background='{{ $notif['unread'] ? 'var(--accent-dim)' : 'transparent' }}'">
                            @else
                                <div class="flex items-start gap-3 px-4 py-3 border-b"
                                     style="border-color: var(--border); {{ $notif['unread'] ? 'background: var(--accent-dim);' : '' }}">
                            @endif
                                    {{-- Icon --}}
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                                         style="background: {{ $nColors['bg'] }}">
                                        <svg class="w-[15px] h-[15px]" fill="none" stroke="{{ $nColors['ic'] }}" viewBox="0 0 24 24" stroke-width="2">
                                            @if($notif['icon'] === 'transfer')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            @elseif($notif['icon'] === 'sale')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            @elseif($notif['icon'] === 'session')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            @elseif($notif['icon'] === 'return')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            @elseif($notif['icon'] === 'expense')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @elseif($notif['icon'] === 'tag')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                                                <circle cx="7" cy="7" r="1.5" fill="{{ $nColors['ic'] }}" stroke="none"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @endif
                                        </svg>
                                    </div>
                                    {{-- Text --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[13px] font-600" style="color:var(--text); font-weight: {{ $notif['unread'] ? '700' : '500' }};">
                                                {{ $notif['label'] }}
                                            </span>
                                            <span class="text-[11px] flex-shrink-0" style="color:var(--text-dim);">{{ $notif['age'] }}</span>
                                        </div>
                                        <p class="text-[12px] mt-0.5 truncate" style="color:var(--text-dim);">{{ $notif['subtitle'] }}</p>
                                    </div>
                                    @if($notif['url'])
                                    <svg class="w-3.5 h-3.5 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color:var(--text-dim)">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    @endif
                            @if($notif['url'])
                                </a>
                            @else
                                </div>
                            @endif
                            @empty
                                <div class="p-8 text-center">
                                    <svg class="w-10 h-10 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--text-sub);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    <p class="text-[13px] font-medium" style="color:var(--text-dim);">{{ __('No recent activity') }}</p>
                                    <p class="text-[12px] mt-1" style="color:var(--text-dim);">{{ __('Actions from the last 7 days appear here') }}</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- ── Actions tab (owner/admin) ── -->
                        @if(Auth::check() && (Auth::user()->isOwner() || Auth::user()->isAdmin()))
                        <div x-show="tab === 'actions'" class="max-h-[420px] overflow-y-auto">
                            @forelse($this->pendingActions as $action)
                                @if($action['count'] > 0)
                                    @php
                                        $aColors = match($action['color']) {
                                            'red'    => ['bg' => 'var(--red-dim)',    'ic' => 'var(--red)'],
                                            'amber'  => ['bg' => 'var(--amber-dim)', 'ic' => 'var(--amber)'],
                                            'orange' => ['bg' => 'var(--amber-dim)', 'ic' => 'var(--amber)'],
                                            default  => ['bg' => 'var(--accent-dim)','ic' => 'var(--accent)'],
                                        };
                                    @endphp
                                    @if(!empty($action['url']) || $action['route'])
                                        <a href="{{ $action['url'] ?? route($action['route']) }}"
                                           class="flex items-center justify-between p-3.5 border-b transition-all"
                                           style="border-color: var(--border);"
                                           onmouseover="this.style.background='var(--surface2)'"
                                           onmouseout="this.style.background='transparent'">
                                    @else
                                        <div class="flex items-center justify-between p-3.5 border-b" style="border-color: var(--border);">
                                    @endif
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                                     style="background: {{ $aColors['bg'] }}">
                                                    <svg class="w-[15px] h-[15px]" fill="none" stroke="{{ $aColors['ic'] }}" viewBox="0 0 24 24" stroke-width="2">
                                                        @if($action['icon'] === 'clock')
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        @elseif($action['icon'] === 'alert')
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                        @elseif($action['icon'] === 'box')
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                        @elseif($action['icon'] === 'tag')
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                                                            <circle cx="7" cy="7" r="1.5" fill="{{ $aColors['ic'] }}" stroke="none"/>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                        @endif
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="text-[13px] font-semibold" style="color: var(--text);">{{ $action['label'] }}</h4>
                                                    <p class="text-[12px] mt-0.5" style="color: var(--text-dim);">
                                                        {{ $action['count'] }} {{ $action['type'] === 'price_approval' ? ($action['count'] === 1 ? __('sale') : __('sales')) : __('pending') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: var(--text-dim);">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                            </svg>
                                    @if(!empty($action['url']) || $action['route'])
                                        </a>
                                    @else
                                        </div>
                                    @endif
                                @endif
                            @empty
                                <div class="p-8 text-center">
                                    <svg class="w-10 h-10 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--text-sub);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-[13px] font-medium" style="color:var(--text-dim);">{{ __('All caught up!') }}</p>
                                    <p class="text-[12px] mt-1" style="color:var(--text-dim);">{{ __('No pending actions') }}</p>
                                </div>
                            @endforelse
                        </div>
                        @endif
                        @endif {{-- $panelOpen --}}

                    </div>
                </div>
