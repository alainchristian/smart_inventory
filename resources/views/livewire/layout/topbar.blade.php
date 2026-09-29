<div class="fixed top-0 lg:left-[var(--sidebar-width)] left-0 right-0 border-b" style="background: var(--surface); border-color: var(--border); z-index: 60; height: var(--topbar-height);">
    <style>
    /* Phones/tablets: let the title shrink + truncate so the right-hand icons always fit */
    @media (max-width: 767px) {
        .tb-left { flex-shrink: 1; min-width: 0; }
    }
    /* Phones: the 320px bell dropdown anchored to the bell ran off the left edge — pin it to the viewport instead */
    @media (max-width: 640px) {
        .tb-notif-dd { position: fixed; left: 12px; right: 12px; width: auto; top: calc(var(--topbar-height) - 4px); margin-top: 0; }
    }
    </style>
    <div class="px-3 sm:px-4 lg:px-6 h-full flex items-center w-full">
        <div class="flex items-center justify-between gap-3 sm:gap-4 lg:gap-6 w-full">
            <!-- Left: Mobile Menu + Page Title -->
            <div class="tb-left flex items-center gap-3 flex-shrink-0">
                <!-- Hamburger Menu (Mobile Only) -->
                <button @click="$dispatch('toggle-mobile-menu')"
                        class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg transition-all"
                        style="background: var(--surface2); border: 1px solid var(--border); color: var(--text-sub);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Page Title -->
                <div class="min-w-0">
                    <h1 class="truncate text-[15px] sm:text-[17px] font-bold" style="color: var(--text);" data-page-title>{{ $pageTitle }}</h1>
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
                        placeholder="{{ __('Search boxes, products, transfers...') }}"
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

                {{-- Live Transactions launcher: the button lives in
                     transactions/live-feed (owner only) and is teleported in
                     here. wire:ignore stops any re-render of this component from
                     wiping the teleported button. --}}
                <div id="lf-launcher" wire:ignore style="display:contents"></div>

                <!-- Notifications Bell (own component: its 15s poll re-renders only the bell) -->
                <livewire:layout.notification-bell />

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
                                    <span class="inline-flex px-2 py-1 text-[10px] font-bold rounded-full" style="background: var(--accent-glow); color: var(--accent);">{{ __('OWNER') }}</span>
                                @elseif(auth()->user()->isAdmin())
                                    <span class="inline-flex px-2 py-1 text-[10px] font-bold rounded-full" style="background: var(--red-dim); color: var(--red);">{{ __('ADMIN') }}</span>
                                @elseif(auth()->user()->isWarehouseManager())
                                    <span class="inline-flex px-2 py-1 text-[10px] font-bold rounded-full" style="background: var(--green-glow); color: var(--green);">{{ __('WAREHOUSE MANAGER') }}</span>
                                @elseif(auth()->user()->isShopManager())
                                    <span class="inline-flex px-2 py-1 text-[10px] font-bold rounded-full" style="background: var(--violet); color: white;">{{ __('SHOP MANAGER') }}</span>
                                @endif
                                @if(auth()->user()->location)
                                    <span class="inline-flex px-2 py-1 text-[10px] font-medium ml-1.5" style="color: var(--text-sub);">{{ auth()->user()->location->name }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="p-2">
                            @if(multilingual_enabled())
                            <!-- Language -->
                            <div class="w-full flex items-center gap-2.5 px-3 py-2.5">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: var(--text-sub); flex-shrink:0;">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path stroke-linecap="round" d="M2 12h20M12 2a15.3 15.3 0 010 20 15.3 15.3 0 010-20z"/>
                                </svg>
                                <span class="text-[14px] font-medium" style="color: var(--text-sub); flex:1">{{ __('Language') }}</span>
                                <div class="flex items-center gap-1">
                                    <form method="POST" action="{{ route('locale.set', 'en') }}">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 rounded text-[11px] font-bold" style="background: {{ app()->getLocale() === 'en' ? 'var(--accent)' : 'var(--surface2)' }}; color: {{ app()->getLocale() === 'en' ? '#fff' : 'var(--text-sub)' }};">EN</button>
                                    </form>
                                    <form method="POST" action="{{ route('locale.set', 'rw') }}">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 rounded text-[11px] font-bold" style="background: {{ app()->getLocale() === 'rw' ? 'var(--accent)' : 'var(--surface2)' }}; color: {{ app()->getLocale() === 'rw' ? '#fff' : 'var(--text-sub)' }};">RW</button>
                                    </form>
                                </div>
                            </div>
                            @endif

                            <!-- Profile -->
                            <a href="{{ route('profile') }}" wire:navigate
                               class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-all"
                               style="color: var(--text-sub);"
                               onmouseover="this.style.background='var(--surface2)'; this.style.color='var(--text)';"
                               onmouseout="this.style.background='transparent'; this.style.color='var(--text-sub)';">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-[14px] font-medium">{{ __('Profile Settings') }}</span>
                            </a>

                            @if(auth()->user()->isRealOwner())
                            <!-- Switch between Owner and Admin view (real owners only) -->
                            <button type="button" wire:click="toggleAdminMode" wire:loading.attr="disabled" wire:target="toggleAdminMode"
                                    class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-all text-left"
                                    style="color: var(--text-sub);"
                                    onmouseover="this.style.background='var(--surface2)'; this.style.color='var(--text)';"
                                    onmouseout="this.style.background='transparent'; this.style.color='var(--text-sub)';">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4-4m-4 4l4 4"/>
                                </svg>
                                <span class="text-[14px] font-medium">
                                    {{ auth()->user()->isActingAsAdmin() ? __('Back to Owner view') : __('Switch to Admin view') }}
                                </span>
                            </button>
                            @endif

                            @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                            <!-- Business Settings (Owner / Admin) -->
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
                                <span class="text-[14px] font-medium">{{ __('Business Settings') }}</span>
                            </a>
                            @endif

                            @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                            <!-- System Settings (Owner / Admin) -->
                            <a href="{{ route('owner.users.index') }}" wire:navigate
                               class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-all"
                               style="color: var(--text-sub);"
                               onmouseover="this.style.background='var(--surface2)'; this.style.color='var(--text)';"
                               onmouseout="this.style.background='transparent'; this.style.color='var(--text-sub)';">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-[14px] font-medium">{{ __('System Settings') }}</span>
                            </a>
                            @endif

                            <!-- Account Info -->
                            <div class="px-3 py-2 my-1">
                                <div class="text-[11px] font-semibold uppercase tracking-wide mb-1.5" style="color: var(--text-dim);">{{ __('Account Info') }}</div>
                                <div class="space-y-1 text-[13px]" style="color: var(--text-sub);">
                                    <div class="flex justify-between">
                                        <span>{{ __('Member since') }}</span>
                                        <span class="font-medium" style="color: var(--text);">{{ auth()->user()->created_at->translatedFormat('M Y') }}</span>
                                    </div>
                                    @if(auth()->user()->location)
                                    <div class="flex justify-between">
                                        <span>{{ __('Location') }}</span>
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
                                    <span class="text-[14px] font-medium">{{ __('Logout') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
