<div class="fixed top-0 lg:left-[var(--sidebar-width)] left-0 right-0" style="background: var(--surface); z-index: 60; height: var(--topbar-height);">
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
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="relative w-9 h-9 flex items-center justify-center rounded-lg transition-all"
                            style="background: var(--surface2); border: 1px solid var(--border); color: var(--text-sub);"
                            onmouseover="this.style.background='var(--surface3)'; this.style.color='var(--text)';"
                            onmouseout="this.style.background='var(--surface2)'; this.style.color='var(--text-sub)';">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($this->totalPendingActions > 0)
                            <div class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center text-[10px] font-bold border-2"
                                 style="background: var(--red); color: white; border-color: var(--surface);">
                                {{ $this->totalPendingActions > 9 ? '9+' : $this->totalPendingActions }}
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
                         class="absolute right-0 mt-2 w-80 rounded-xl shadow-xl border overflow-hidden"
                         style="background: var(--surface); border-color: var(--border); z-index: 100;"
                         x-cloak>
                        <!-- Header -->
                        <div class="p-4 border-b" style="border-color: var(--border);">
                            <div class="flex items-center justify-between">
                                <h3 class="text-[15px] font-bold" style="color: var(--text);">Requires Attention</h3>
                                @if($this->totalPendingActions > 0)
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full" style="background: var(--red-glow); color: var(--red);">
                                        {{ $this->totalPendingActions }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Pending Actions List -->
                        <div class="max-h-96 overflow-y-auto">
                            @php $hasActions = false; @endphp
                            @foreach($this->pendingActions as $action)
                                @if($action['count'] > 0)
                                    @php
                                        $hasActions = true;
                                        $colorMap = [
                                            'amber' => ['bg' => 'var(--amber-glow)', 'text' => 'var(--amber)', 'icon' => 'var(--amber)'],
                                            'red' => ['bg' => 'var(--red-glow)', 'text' => 'var(--red)', 'icon' => 'var(--red)'],
                                            'orange' => ['bg' => 'rgba(251,146,60,.14)', 'text' => '#ea580c', 'icon' => '#ea580c'],
                                        ];
                                        $colors = $colorMap[$action['color']] ?? $colorMap['red'];
                                    @endphp
                                    <a href="{{ $action['route'] ? route($action['route']) : '#' }}"
                                       class="block p-4 border-b transition-all"
                                       style="border-color: var(--border);"
                                       onmouseover="this.style.background='var(--surface2)'"
                                       onmouseout="this.style.background='transparent'">
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                                                 style="background: {{ $colors['bg'] }}">
                                                @if($action['icon'] === 'clock')
                                                    <svg class="w-5 h-5" fill="none" stroke="{{ $colors['icon'] }}" viewBox="0 0 24 24" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                                    </svg>
                                                @elseif($action['icon'] === 'alert')
                                                    <svg class="w-5 h-5" fill="none" stroke="{{ $colors['icon'] }}" viewBox="0 0 24 24" stroke-width="2">
                                                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                                    </svg>
                                                @elseif($action['icon'] === 'box')
                                                    <svg class="w-5 h-5" fill="none" stroke="{{ $colors['icon'] }}" viewBox="0 0 24 24" stroke-width="2">
                                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="{{ $colors['icon'] }}" viewBox="0 0 24 24" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-1">
                                                    <p class="text-[13px] font-semibold truncate" style="color: var(--text);">{{ $action['label'] }}</p>
                                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full flex-shrink-0 ml-2"
                                                          style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};">
                                                        {{ $action['count'] }}
                                                    </span>
                                                </div>
                                                <p class="text-[12px]" style="color: var(--text-sub);">
                                                    @if($action['type'] === 'transfer_approval')
                                                        Pending warehouse review
                                                    @elseif($action['type'] === 'discrepancy')
                                                        Missing or extra boxes found
                                                    @elseif($action['type'] === 'damaged_goods')
                                                        Awaiting disposition decision
                                                    @else
                                                        Needs immediate attention
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                @endif
                            @endforeach

                            @if(!$hasActions)
                                <div class="p-8 text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--text-sub);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-[13px] font-medium" style="color: var(--text-dim);">All caught up!</p>
                                    <p class="text-[12px] mt-1" style="color: var(--text-dim);">No pending actions</p>
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
</div>
