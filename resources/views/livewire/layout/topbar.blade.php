<div class="fixed top-0 lg:left-[var(--sidebar-width)] left-0 right-0 border-b" style="background: var(--surface); border-color: var(--border); z-index: 60; height: var(--topbar-height);">
    <div class="px-3 sm:px-4 lg:px-6 h-full flex items-center w-full">
        <div class="flex items-center justify-between gap-3 sm:gap-4 lg:gap-6 w-full">
            <!-- Left: Mobile Menu + Page Title -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <!-- Hamburger Menu (Mobile Only) -->
                <button @click="$dispatch('toggle-mobile-menu')"
                        class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg transition-all"
                        style="background: var(--surface2); border: 1px solid var(--border); color: var(--text-sub);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Page Title -->
                <div>
                    <h1 class="text-[15px] sm:text-[17px] font-bold" style="color: var(--text);" data-page-title>{{ $pageTitle }}</h1>
                    <div class="hidden sm:flex items-center gap-1.5 text-[12px] mt-0.5" style="color: var(--text-dim); font-family: var(--mono);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $currentDate }}</span>
                    </div>
                </div>
            </div>

            <!-- Center: Global Search (Hidden on Mobile) -->
            <div class="hidden md:flex flex-1 max-w-xl">
                <div class="relative w-full">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: var(--text-dim);">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="Search boxes, products, transfers..."
                        class="w-full h-10 pl-10 pr-16 rounded-lg text-[14px] border focus:outline-none transition-all"
                        style="background: var(--surface2); border-color: var(--border); color: var(--text);"
                    />
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[11px] border" style="background: var(--surface3); border-color: var(--border); color: var(--text-dim); font-family: var(--mono);">
                        <span>⌘K</span>
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 flex-shrink-0">
                <!-- Notifications Bell -->
                <div x-data="{ open: false }" class="relative" wire:poll.15s>
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
                         class="absolute right-0 mt-2 w-80 sm:w-96 rounded-xl shadow-xl border overflow-hidden"
                         style="background: var(--surface); border-color: var(--border); z-index: 100;"
                         x-cloak>
                        <!-- Header -->
                        <div class="p-4 border-b" style="border-color: var(--border);">
                            <div class="flex items-center justify-between">
                                <h3 class="text-[15px] font-bold" style="color: var(--text);">Pending Actions</h3>
                                @if($this->totalPendingActions > 0)
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full" style="background: var(--red-glow); color: var(--red);">
                                        {{ $this->totalPendingActions }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions List -->
                        <div class="max-h-96 overflow-y-auto">
                            @if(Auth::check() && Auth::user()->isOwner())
                                @forelse($this->pendingActions as $action)
                                    @if($action['count'] > 0)
                                        @php
                                            $colorStyles = match($action['color']) {
                                                'red' => ['bg' => 'var(--red-glow)', 'text' => 'var(--red)'],
                                                'amber' => ['bg' => 'var(--amber-glow)', 'text' => 'var(--amber)'],
                                                'orange' => ['bg' => 'var(--orange-glow)', 'text' => 'var(--orange)'],
                                                default => ['bg' => 'var(--accent-glow)', 'text' => 'var(--accent)'],
                                            };
                                        @endphp
                                        @if($action['route'])
                                            <a href="{{ route($action['route']) }}"
                                               class="block p-3.5 border-b transition-all"
                                               style="border-color: var(--border);"
                                               onmouseover="this.style.background='var(--surface2)'"
                                               onmouseout="this.style.background='transparent'">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                                             style="background: {{ $colorStyles['bg'] }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="{{ $colorStyles['text'] }}" viewBox="0 0 24 24" stroke-width="2">
                                                                @if($action['icon'] === 'clock')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                @elseif($action['icon'] === 'alert')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                                @elseif($action['icon'] === 'box')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                                @elseif($action['icon'] === 'tag')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                                                                    <circle cx="7" cy="7" r="1.5" fill="currentColor" stroke="none"/>
                                                                @else
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                                @endif
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h4 class="text-[13px] font-semibold" style="color: var(--text);">{{ $action['label'] }}</h4>
                                                            <p class="text-[12px] mt-0.5" style="color: var(--text-sub);">{{ $action['count'] }} pending</p>
                                                        </div>
                                                    </div>
                                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: var(--text-dim);">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </div>
                                            </a>
                                        @elseif(!empty($action['modal']))
                                            {{-- Clickable button that opens an inline modal --}}
                                            <button type="button"
                                                    @click="open = false; $wire.openApprovalModal()"
                                                    class="w-full p-3.5 border-b text-left transition-all"
                                                    style="border-color: var(--border); background: transparent; cursor: pointer;"
                                                    onmouseover="this.style.background='var(--surface2)'"
                                                    onmouseout="this.style.background='transparent'">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                                             style="background: {{ $colorStyles['bg'] }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="{{ $colorStyles['text'] }}" viewBox="0 0 24 24" stroke-width="2">
                                                                @if($action['icon'] === 'clock')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                @elseif($action['icon'] === 'alert')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                                @elseif($action['icon'] === 'box')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                                @elseif($action['icon'] === 'tag')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                                                                    <circle cx="7" cy="7" r="1.5" fill="currentColor" stroke="none"/>
                                                                @else
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                                @endif
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h4 class="text-[13px] font-semibold" style="color: var(--text);">{{ $action['label'] }}</h4>
                                                            <p class="text-[12px] mt-0.5" style="color: var(--text-sub);">{{ $action['count'] }} {{ $action['count'] === 1 ? 'sale' : 'sales' }} — tap to review</p>
                                                        </div>
                                                    </div>
                                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: var(--text-dim);">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </div>
                                            </button>
                                        @else
                                            <div class="p-3.5 border-b" style="border-color: var(--border);">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                                                             style="background: {{ $colorStyles['bg'] }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="{{ $colorStyles['text'] }}" viewBox="0 0 24 24" stroke-width="2">
                                                                @if($action['icon'] === 'clock')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                @elseif($action['icon'] === 'alert')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                                @elseif($action['icon'] === 'box')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                                @elseif($action['icon'] === 'tag')
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                                                                    <circle cx="7" cy="7" r="1.5" fill="currentColor" stroke="none"/>
                                                                @else
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                                @endif
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h4 class="text-[13px] font-semibold" style="color: var(--text);">{{ $action['label'] }}</h4>
                                                            <p class="text-[12px] mt-0.5" style="color: var(--text-sub);">{{ $action['count'] }} pending</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @empty
                                    <div class="p-8 text-center">
                                        <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--text-sub);">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-[13px] font-medium" style="color: var(--text-dim);">All caught up!</p>
                                        <p class="text-[12px] mt-1" style="color: var(--text-dim);">No pending actions</p>
                                    </div>
                                @endforelse
                            @else
                                <div class="p-8 text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--text-sub);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-[13px] font-medium" style="color: var(--text-dim);">No notifications</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- User Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 sm:gap-2.5 h-9 px-2 sm:px-3 rounded-lg transition-all"
                            style="background: var(--surface2); border: 1px solid var(--border);"
                            onmouseover="this.style.background='var(--surface3)';"
                            onmouseout="this.style.background='var(--surface2)';">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold" style="background: var(--accent); color: white;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden sm:inline text-[14px] font-medium" style="color: var(--text);">{{ explode(' ', auth()->user()->name)[0] }}</span>
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: var(--text-dim);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-64 rounded-xl shadow-xl border overflow-hidden"
                         style="background: var(--surface); border-color: var(--border); z-index: 100;"
                         x-cloak>
                        <!-- User Info -->
                        <div class="p-4 border-b" style="border-color: var(--border);">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold" style="background: var(--accent); color: white;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[15px] font-semibold truncate" style="color: var(--text);">{{ auth()->user()->name }}</p>
                                    <p class="text-[13px] truncate mt-0.5" style="color: var(--text-sub);">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                            <div class="mt-2.5">
                                @if(auth()->user()->isOwner())
                                    <span class="inline-flex px-2 py-1 text-[10px] font-bold rounded-full" style="background: var(--accent-glow); color: var(--accent);">OWNER</span>
                                @elseif(auth()->user()->isWarehouseManager())
                                    <span class="inline-flex px-2 py-1 text-[10px] font-bold rounded-full" style="background: var(--green-glow); color: var(--green);">WAREHOUSE MANAGER</span>
                                @elseif(auth()->user()->isShopManager())
                                    <span class="inline-flex px-2 py-1 text-[10px] font-bold rounded-full" style="background: var(--violet); color: white;">SHOP MANAGER</span>
                                @endif
                                @if(auth()->user()->location)
                                    <span class="inline-flex px-2 py-1 text-[10px] font-medium ml-1.5" style="color: var(--text-sub);">{{ auth()->user()->location->name }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="p-2">
                            <!-- Profile -->
                            <a href="{{ route('profile') }}" wire:navigate
                               class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-all"
                               style="color: var(--text-sub);"
                               onmouseover="this.style.background='var(--surface2)'; this.style.color='var(--text)';"
                               onmouseout="this.style.background='transparent'; this.style.color='var(--text-sub)';">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-[14px] font-medium">Profile Settings</span>
                            </a>

                            @if(auth()->user()->isOwner())
                            <!-- Business Settings (Owner only) -->
                            <a href="{{ route('owner.settings') }}" wire:navigate
                               class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-all"
                               style="color: var(--text-sub);"
                               onmouseover="this.style.background='var(--surface2)'; this.style.color='var(--text)';"
                               onmouseout="this.style.background='transparent'; this.style.color='var(--text-sub)';">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-[14px] font-medium">Business Settings</span>
                            </a>
                            @endif

                            @if(auth()->user()->isOwner())
                            <!-- System Settings (Owner only) -->
                            <a href="{{ route('owner.users.index') }}" wire:navigate
                               class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-all"
                               style="color: var(--text-sub);"
                               onmouseover="this.style.background='var(--surface2)'; this.style.color='var(--text)';"
                               onmouseout="this.style.background='transparent'; this.style.color='var(--text-sub)';">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-[14px] font-medium">System Settings</span>
                            </a>
                            @endif

                            <!-- Account Info -->
                            <div class="px-3 py-2 my-1">
                                <div class="text-[11px] font-semibold uppercase tracking-wide mb-1.5" style="color: var(--text-dim);">Account Info</div>
                                <div class="space-y-1 text-[13px]" style="color: var(--text-sub);">
                                    <div class="flex justify-between">
                                        <span>Member since</span>
                                        <span class="font-medium" style="color: var(--text);">{{ auth()->user()->created_at->format('M Y') }}</span>
                                    </div>
                                    @if(auth()->user()->location)
                                    <div class="flex justify-between">
                                        <span>Location</span>
                                        <span class="font-medium" style="color: var(--text);">{{ auth()->user()->location->name }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="my-2 border-t" style="border-color: var(--border);"></div>

                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-all text-left"
                                        style="color: var(--text-sub);"
                                        onmouseover="this.style.background='var(--red-dim)'; this.style.color='var(--red)';"
                                        onmouseout="this.style.background='transparent'; this.style.color='var(--text-sub)';">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span class="text-[14px] font-medium">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Price Override Approval Modal ─────────────────────────────────────── --}}
    @if($showApprovalModal)
    <div x-data
         x-init="document.body.style.overflow='hidden'"
         x-destroy="document.body.style.overflow=''"
         style="position:fixed;inset:0;z-index:300;display:flex;align-items:flex-start;
                justify-content:center;padding:60px 16px 16px;
                background:rgba(0,0,0,.55);backdrop-filter:blur(3px)">

        <div @click.away="$wire.showApprovalModal = false"
             style="background:var(--surface);border:1px solid var(--border);border-radius:16px;
                    width:100%;max-width:580px;max-height:85vh;display:flex;flex-direction:column;
                    box-shadow:0 24px 64px rgba(0,0,0,.4)">

            {{-- Modal header --}}
            <div style="padding:18px 20px;border-bottom:1px solid var(--border);
                        display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:34px;height:34px;border-radius:9px;background:var(--amber-dim);
                                display:flex;align-items:center;justify-content:center">
                        <svg width="16" height="16" fill="none" stroke="var(--amber)" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                            <circle cx="7" cy="7" r="1" fill="var(--amber)" stroke="none"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:15px;font-weight:700;color:var(--text)">Price Override Approvals</div>
                        <div style="font-size:12px;color:var(--text-dim);margin-top:1px">
                            {{ count($pendingHeldSales) }} {{ count($pendingHeldSales) === 1 ? 'sale' : 'sales' }} waiting for your decision
                        </div>
                    </div>
                </div>
                <button wire:click="$set('showApprovalModal', false)"
                        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);
                               background:var(--surface2);display:flex;align-items:center;justify-content:center;
                               cursor:pointer;color:var(--text-dim)">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            {{-- Modal body (scrollable) --}}
            <div style="overflow-y:auto;flex:1;padding:12px">
                @forelse($pendingHeldSales as $h)
                <div wire:key="modal-held-{{ $h['id'] }}"
                     style="border:1px solid var(--border);border-radius:12px;
                            margin-bottom:10px;overflow:hidden">

                    {{-- Card header --}}
                    <div style="padding:12px 14px;background:var(--surface2);
                                display:flex;align-items:center;justify-content:space-between">
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-size:13px;font-weight:800;font-family:var(--mono);color:var(--amber)">
                                {{ $h['reference'] }}
                            </span>
                            <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:5px;
                                         background:var(--amber-dim);color:var(--amber)">
                                Pending
                            </span>
                        </div>
                        <span style="font-size:11px;color:var(--text-dim)">{{ $h['age'] }}</span>
                    </div>

                    {{-- Card meta --}}
                    <div style="padding:10px 14px;border-bottom:1px solid var(--border);
                                display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                        <div style="display:flex;align-items:center;gap:5px">
                            <svg width="12" height="12" fill="none" stroke="var(--text-dim)" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                            <span style="font-size:12px;color:var(--text-sub)">{{ $h['shop'] }}</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:5px">
                            <svg width="12" height="12" fill="none" stroke="var(--text-dim)" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span style="font-size:12px;color:var(--text-sub)">{{ $h['seller'] }}</span>
                        </div>
                        <div style="margin-left:auto;text-align:right">
                            <span style="font-size:13px;font-weight:700;font-family:var(--mono);color:var(--text)">
                                {{ number_format($h['cart_total']) }} RWF
                            </span>
                            <span style="font-size:11px;color:var(--text-dim);margin-left:4px">
                                · {{ $h['item_count'] }} item(s)
                            </span>
                        </div>
                    </div>

                    {{-- Cart preview --}}
                    @php $items = array_slice($h['cart_data'], 0, 4); @endphp
                    @if(!empty($items))
                    <div style="padding:8px 14px;border-bottom:1px solid var(--border)">
                        @foreach($items as $item)
                        <div style="display:flex;align-items:center;justify-content:space-between;
                                    padding:5px 0;border-bottom:1px solid var(--border);font-size:12px"
                             style="border-bottom:{{ !$loop->last ? '1px solid var(--border)' : 'none' }}">
                            <div style="display:flex;align-items:center;gap:6px;min-width:0;flex:1">
                                @if(!empty($item['price_modified']))
                                <span style="width:6px;height:6px;border-radius:50%;background:var(--amber);flex-shrink:0"></span>
                                @endif
                                <span style="color:var(--text-sub);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    {{ $item['product_name'] }}
                                </span>
                                <span style="font-size:11px;color:var(--text-dim);flex-shrink:0">
                                    × {{ $item['quantity'] }}{{ $item['is_full_box'] ? ' box' : '' }}
                                </span>
                            </div>
                            <div style="text-align:right;flex-shrink:0;margin-left:10px">
                                @if(!empty($item['price_modified']))
                                <span style="font-size:11px;color:var(--text-dim);text-decoration:line-through;margin-right:4px;font-family:var(--mono)">
                                    {{ number_format($item['original_price']) }}
                                </span>
                                <span style="font-family:var(--mono);font-weight:700;color:var(--amber)">
                                    {{ number_format($item['price']) }}
                                </span>
                                @else
                                <span style="font-family:var(--mono);color:var(--text-sub)">
                                    {{ number_format($item['price']) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        @if(count($h['cart_data']) > 4)
                        <div style="font-size:11px;color:var(--text-dim);padding-top:5px">
                            + {{ count($h['cart_data']) - 4 }} more item(s)
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Approve / Reject --}}
                    <div style="padding:10px 14px;display:flex;gap:8px">
                        <button wire:click="approveHeldSale({{ $h['id'] }})"
                                wire:loading.attr="disabled"
                                wire:target="approveHeldSale({{ $h['id'] }})"
                                style="flex:1;padding:8px 0;border-radius:8px;border:none;cursor:pointer;
                                       background:var(--green);color:#fff;font-size:13px;font-weight:700;
                                       transition:opacity .15s"
                                wire:loading.class="opacity-50">
                            <span wire:loading.remove wire:target="approveHeldSale({{ $h['id'] }})">✓ Approve</span>
                            <span wire:loading wire:target="approveHeldSale({{ $h['id'] }})" style="display:none">Approving…</span>
                        </button>
                        <button wire:click="rejectHeldSale({{ $h['id'] }})"
                                wire:confirm="Reject {{ $h['reference'] }}? The seller will be notified."
                                wire:loading.attr="disabled"
                                style="padding:8px 16px;border-radius:8px;cursor:pointer;
                                       border:1.5px solid var(--red);background:var(--red-dim);
                                       color:var(--red);font-size:13px;font-weight:700">
                            Reject
                        </button>
                    </div>

                </div>
                @empty
                <div style="padding:40px;text-align:center;color:var(--text-dim)">
                    <div style="font-size:32px;margin-bottom:10px">✓</div>
                    <div style="font-size:14px;font-weight:600">All approvals handled</div>
                </div>
                @endforelse
            </div>

            {{-- Modal footer --}}
            <div style="padding:12px 16px;border-top:1px solid var(--border);flex-shrink:0;
                        display:flex;align-items:center;justify-content:space-between">
                <a href="{{ route('owner.reports.sales') }}?activeTab=audit"
                   style="font-size:12px;font-weight:600;color:var(--accent);text-decoration:none">
                    View Price Audit Trail →
                </a>
                <button wire:click="$set('showApprovalModal', false)"
                        style="font-size:12px;font-weight:600;padding:6px 14px;border-radius:8px;
                               border:1px solid var(--border);background:var(--surface2);
                               color:var(--text-sub);cursor:pointer">
                    Close
                </button>
            </div>

        </div>
    </div>
    @endif

</div>
