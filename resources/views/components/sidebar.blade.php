@props(['activePage' => ''])

<div x-data="{ sidebarOpen: false }" @toggle-mobile-menu.window="sidebarOpen = !sidebarOpen">
    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen"
         x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 h-screen z-40 transform transition-transform duration-300 lg:translate-x-0 border-r"
           style="width: var(--sidebar-width); background: var(--surface); border-color: var(--border);"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="flex flex-col h-full">
            <!-- Sidebar Header with Logo -->
            <div class="px-4 flex items-center justify-between border-b" style="height: var(--topbar-height); border-color: var(--border);">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, var(--accent), #6b8dff);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="color: white;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-sm font-bold leading-tight" style="color: var(--text);">Smart Inventory</h1>
                        <p class="text-[10px] leading-tight" style="color: var(--text-dim);">Management System</p>
                    </div>
                </div>

                <!-- Close button for mobile -->
                <button @click="sidebarOpen = false" class="lg:hidden p-1 transition-colors" style="color: var(--text-sub);"
                        onmouseover="this.style.color='var(--text)';" onmouseout="this.style.color='var(--text-sub)';">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Role/Location Badge -->
            <div class="px-4 py-2.5 border-b" style="background: var(--surface2); border-color: var(--border);">
                @if(auth()->user()->isOwner())
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded" style="background: var(--accent-glow); color: var(--accent);">OWNER</span>
                        <span class="text-[11px]" style="color: var(--text-sub);">Full System Access</span>
                    </div>
                @elseif(auth()->user()->isWarehouseManager())
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded" style="background: var(--green-glow); color: var(--green);">WAREHOUSE</span>
                        <span class="text-[11px] truncate" style="color: var(--text-sub);">{{ auth()->user()->location?->name ?? 'Manager' }}</span>
                    </div>
                @elseif(auth()->user()->isShopManager())
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded" style="background: rgba(139,92,246,0.15); color: var(--violet);">SHOP</span>
                        <span class="text-[11px] truncate" style="color: var(--text-sub);">{{ auth()->user()->location?->name ?? 'Manager' }}</span>
                    </div>
                @endif
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto p-3 pt-4" x-data="{
                openLocations: {{ request()->routeIs('owner.warehouses.*') || request()->routeIs('owner.shops.*') ? 'true' : 'false' }},
                openShopOps: {{ request()->routeIs('shop.*') ? 'true' : 'false' }},
                openWarehouseOps: {{ request()->routeIs('warehouse.*') ? 'true' : 'false' }},
                openManagement: {{ request()->routeIs('owner.users.*') || request()->routeIs('*.reports.*') || request()->routeIs('owner.settings') ? 'true' : 'false' }},
                openReports: {{ request()->routeIs('*.reports.*') ? 'true' : 'false' }},
                openInventory: {{ request()->routeIs('*.inventory.*') ? 'true' : 'false' }},
                openShopTransfers: {{ request()->routeIs('shop.transfers.*') ? 'true' : 'false' }},
                openWarehouseTransfers: {{ request()->routeIs('warehouse.transfers.*') ? 'true' : 'false' }},
                openSales: {{ request()->routeIs('*.sales.*') ? 'true' : 'false' }},
                openReturns: {{ request()->routeIs('*.returns.*') ? 'true' : 'false' }},
                openTransfers: {{ request()->routeIs('*.transfers.*') && !request()->routeIs('shop.sales.*') ? 'true' : 'false' }}
            }">
                <div class="space-y-1">
                    @if(auth()->user()->isOwner())
                        {{-- OWNER MENU --}}

                        <!-- Dashboard -->
                        <a href="{{ route('owner.dashboard') }}"
                           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-all {{ request()->routeIs('owner.dashboard') ? 'border' : '' }}"
                           style="{{ request()->routeIs('owner.dashboard') ? 'background: var(--accent-glow); color: var(--accent); border-color: var(--accent);' : 'color: var(--text-sub);' }}"
                           onmouseover="if (!this.classList.contains('border')) { this.style.background='var(--surface2)'; this.style.color='var(--text)'; }"
                           onmouseout="if (!this.classList.contains('border')) { this.style.background='transparent'; this.style.color='var(--text-sub)'; }">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span class="text-[13px] font-medium">Dashboard</span>
                        </a>

                        <!-- Products -->
                        <a href="{{ route('owner.products.index') }}"
                           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition-all {{ request()->routeIs('owner.products.*') ? 'border' : '' }}"
                           style="{{ request()->routeIs('owner.products.*') ? 'background: var(--accent-glow); color: var(--accent); border-color: var(--accent);' : 'color: var(--text-sub);' }}"
                           onmouseover="if (!this.classList.contains('border')) { this.style.background='var(--surface2)'; this.style.color='var(--text)'; }"
                           onmouseout="if (!this.classList.contains('border')) { this.style.background='transparent'; this.style.color='var(--text-sub)'; }">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <span class="text-[13px] font-medium">Products</span>
                        </a>

                        <!-- Locations (Collapsible) -->
                        <div class="pt-1.5">
                            <button @click="openLocations = !openLocations"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition-all {{ request()->routeIs('owner.warehouses.*') || request()->routeIs('owner.shops.*') ? 'border' : '' }}"
                                    style="{{ request()->routeIs('owner.warehouses.*') || request()->routeIs('owner.shops.*') ? 'background: var(--accent-glow); color: var(--accent); border-color: var(--accent);' : 'color: var(--text-sub);' }}"
                                    onmouseover="if (!this.classList.contains('border')) { this.style.background='var(--surface2)'; this.style.color='var(--text)'; }"
                                    onmouseout="if (!this.classList.contains('border')) { this.style.background='transparent'; this.style.color='var(--text-sub)'; }">
                                <div class="flex items-center space-x-2.5">
                                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-[13px] font-medium">Locations</span>
                                </div>
                                <svg class="w-3.5 h-3.5 transition-transform" :class="openLocations ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="openLocations" x-collapse class="ml-7 mt-1 space-y-0.5">
                                <a href="{{ route('owner.warehouses.index') }}"
                                   class="block px-3 py-1.5 text-[12.5px] rounded-lg transition-colors"
                                   style="{{ request()->routeIs('owner.warehouses.*') ? 'background: var(--accent-dim); color: var(--accent);' : 'color: var(--text-dim);' }}"
                                   onmouseover="if (!'{{ request()->routeIs('owner.warehouses.*') }}') { this.style.background='var(--surface2)'; this.style.color='var(--text-sub)'; }"
                                   onmouseout="if (!'{{ request()->routeIs('owner.warehouses.*') }}') { this.style.background='transparent'; this.style.color='var(--text-dim)'; }">
                                    Warehouses
                                </a>
                                <a href="{{ route('owner.shops.index') }}"
                                   class="block px-3 py-1.5 text-[12.5px] rounded-lg transition-colors"
                                   style="{{ request()->routeIs('owner.shops.*') ? 'background: var(--accent-dim); color: var(--accent);' : 'color: var(--text-dim);' }}"
                                   onmouseover="if (!'{{ request()->routeIs('owner.shops.*') }}') { this.style.background='var(--surface2)'; this.style.color='var(--text-sub)'; }"
                                   onmouseout="if (!'{{ request()->routeIs('owner.shops.*') }}') { this.style.background='transparent'; this.style.color='var(--text-dim)'; }">
                                    Shops
                                </a>
                            </div>
                        </div>

                        <!-- Management -->
                        <div class="pt-1.5">
                            <button @click="openManagement = !openManagement"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition-all {{ request()->routeIs('owner.users.*') || request()->routeIs('*.reports.*') || request()->routeIs('owner.settings') ? 'border' : '' }}"
                                    style="{{ request()->routeIs('owner.users.*') || request()->routeIs('*.reports.*') || request()->routeIs('owner.settings') ? 'background: var(--accent-glow); color: var(--accent); border-color: var(--accent);' : 'color: var(--text-sub);' }}"
                                    onmouseover="if (!this.classList.contains('border')) { this.style.background='var(--surface2)'; this.style.color='var(--text)'; }"
                                    onmouseout="if (!this.classList.contains('border')) { this.style.background='transparent'; this.style.color='var(--text-sub)'; }">
                                <div class="flex items-center space-x-2.5">
                                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-[13px] font-medium">Management</span>
                                </div>
                                <svg class="w-3.5 h-3.5 transition-transform" :class="openManagement ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="openManagement" x-collapse class="ml-7 mt-1 space-y-0.5">
                                <a href="{{ route('owner.users.index') }}"
                                   class="block px-3 py-1.5 text-[12.5px] rounded-lg transition-colors"
                                   style="{{ request()->routeIs('owner.users.*') ? 'background: var(--accent-dim); color: var(--accent);' : 'color: var(--text-dim);' }}"
                                   onmouseover="if (!'{{ request()->routeIs('owner.users.*') }}') { this.style.background='var(--surface2)'; this.style.color='var(--text-sub)'; }"
                                   onmouseout="if (!'{{ request()->routeIs('owner.users.*') }}') { this.style.background='transparent'; this.style.color='var(--text-dim)'; }">
                                    Users
                                </a>

                                <!-- Reports Submenu -->
                                <div>
                                    <button @click="openReports = !openReports"
                                            class="w-full flex items-center justify-between px-3 py-1.5 text-[12.5px] rounded-lg transition-colors"
                                            style="{{ request()->routeIs('*.reports.*') ? 'background: var(--accent-dim); color: var(--accent);' : 'color: var(--text-dim);' }}"
                                            onmouseover="if (!'{{ request()->routeIs('*.reports.*') }}') { this.style.background='var(--surface2)'; this.style.color='var(--text-sub)'; }"
                                            onmouseout="if (!'{{ request()->routeIs('*.reports.*') }}') { this.style.background='transparent'; this.style.color='var(--text-dim)'; }">
                                        <span>Reports</span>
                                        <svg class="w-3 h-3 transition-transform" :class="openReports ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="openReports" x-collapse class="ml-4 mt-0.5 space-y-0.5">
                                        <a href="{{ route('owner.reports.sales') }}"
                                           class="block px-3 py-1 text-[11.5px] rounded-lg transition-colors"
                                           style="{{ request()->routeIs('owner.reports.sales') ? 'color: var(--accent);' : 'color: var(--text-dim);' }}"
                                           onmouseover="if (!'{{ request()->routeIs('owner.reports.sales') }}') { this.style.color='var(--text-sub)'; }"
                                           onmouseout="if (!'{{ request()->routeIs('owner.reports.sales') }}') { this.style.color='var(--text-dim)'; }">
                                            Sales
                                        </a>
                                        <a href="{{ route('owner.reports.inventory') }}"
                                           class="block px-3 py-1 text-[11.5px] rounded-lg transition-colors"
                                           style="{{ request()->routeIs('owner.reports.inventory') ? 'color: var(--accent);' : 'color: var(--text-dim);' }}"
                                           onmouseover="if (!'{{ request()->routeIs('owner.reports.inventory') }}') { this.style.color='var(--text-sub)'; }"
                                           onmouseout="if (!'{{ request()->routeIs('owner.reports.inventory') }}') { this.style.color='var(--text-dim)'; }">
                                            Inventory
                                        </a>
                                        <a href="{{ route('owner.reports.transfers') }}"
                                           class="block px-3 py-1 text-[11.5px] rounded-lg transition-colors"
                                           style="{{ request()->routeIs('owner.reports.transfers') ? 'color: var(--accent);' : 'color: var(--text-dim);' }}"
                                           onmouseover="if (!'{{ request()->routeIs('owner.reports.transfers') }}') { this.style.color='var(--text-sub)'; }"
                                           onmouseout="if (!'{{ request()->routeIs('owner.reports.transfers') }}') { this.style.color='var(--text-dim)'; }">
                                            Transfers
                                        </a>
                                        <a href="{{ route('owner.reports.losses') }}"
                                           class="block px-3 py-1 text-[11.5px] rounded-lg transition-colors"
                                           style="{{ request()->routeIs('owner.reports.losses') ? 'color: var(--accent);' : 'color: var(--text-dim);' }}"
                                           onmouseover="if (!'{{ request()->routeIs('owner.reports.losses') }}') { this.style.color='var(--text-sub)'; }"
                                           onmouseout="if (!'{{ request()->routeIs('owner.reports.losses') }}') { this.style.color='var(--text-dim)'; }">
                                            Losses
                                        </a>
                                    </div>
                                </div>

                                <a href="{{ route('owner.settings') }}"
                                   class="block px-3 py-1.5 text-[12.5px] rounded-lg transition-colors"
                                   style="{{ request()->routeIs('owner.settings') ? 'background: var(--accent-dim); color: var(--accent);' : 'color: var(--text-dim);' }}"
                                   onmouseover="if (!'{{ request()->routeIs('owner.settings') }}') { this.style.background='var(--surface2)'; this.style.color='var(--text-sub)'; }"
                                   onmouseout="if (!'{{ request()->routeIs('owner.settings') }}') { this.style.background='transparent'; this.style.color='var(--text-dim)'; }">
                                    Settings
                                </a>
                            </div>
                        </div>

                    @endif
                </div>
            </nav>

            <!-- User Profile (Sticky Bottom) -->
            <div class="p-3.5 border-t" style="background: var(--surface2); border-color: var(--border);" x-data="{ open: false }">
                <!-- User Info -->
                <button @click="open = !open" class="w-full flex items-center space-x-2.5 p-2.5 rounded-lg transition-all mb-2"
                        style="background: var(--surface3);"
                        onmouseover="this.style.background='var(--surface)';"
                        onmouseout="this.style.background='var(--surface3)';">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold" style="background: var(--accent); color: white;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0 text-left">
                        <p class="text-[12.5px] font-semibold truncate" style="color: var(--text);">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] truncate" style="color: var(--text-dim);">{{ auth()->user()->email }}</p>
                    </div>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: var(--text-dim);">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open"
                     x-collapse
                     class="space-y-1">
                    <!-- Role Badge -->
                    <div class="px-2.5 py-1.5">
                        @if(auth()->user()->isOwner())
                            <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded" style="background: var(--accent-glow); color: var(--accent);">OWNER</span>
                            <span class="text-[11px] ml-1.5" style="color: var(--text-dim);">Full Access</span>
                        @elseif(auth()->user()->isWarehouseManager())
                            <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded" style="background: var(--green-glow); color: var(--green);">WAREHOUSE</span>
                            <span class="text-[11px] ml-1.5" style="color: var(--text-dim);">{{ auth()->user()->location?->name ?? 'Manager' }}</span>
                        @elseif(auth()->user()->isShopManager())
                            <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded" style="background: rgba(139,92,246,0.15); color: var(--violet);">SHOP</span>
                            <span class="text-[11px] ml-1.5" style="color: var(--text-dim);">{{ auth()->user()->location?->name ?? 'Manager' }}</span>
                        @endif
                    </div>

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center space-x-2 px-2.5 py-2 rounded-lg transition-all font-medium"
                                style="color: var(--text-sub);"
                                onmouseover="this.style.background='var(--red-dim)'; this.style.color='var(--red)';"
                                onmouseout="this.style.background='transparent'; this.style.color='var(--text-sub)';">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="text-[12.5px]">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>
</div>
