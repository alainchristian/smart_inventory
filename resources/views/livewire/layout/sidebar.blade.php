<div x-data="{ open: false }"
     @toggle-mobile-menu.window="open = !open"
     @close-mobile-menu.window="open = false"
     class="fixed left-0 top-0 h-screen bg-[var(--surface)] border-r border-[var(--border)] flex flex-col transition-transform duration-300 ease-in-out z-50
            -translate-x-full lg:translate-x-0"
     :class="{ 'translate-x-0': open }"
     style="width: var(--sidebar-width);">
    <!-- Logo & Branding -->
    <div class="p-5 border-b" style="border-color: var(--border);">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, var(--accent), #6b8dff);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="color: white;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <div class="text-[15px] font-bold" style="color: var(--text);">Smart Inventory</div>
                <div class="text-[12px]" style="color: var(--text-sub); font-family: var(--mono);">Operations Centre</div>
            </div>
        </div>
    </div>

    <!-- Role/Location Badge -->
    <div class="px-4 py-2 border-b" style="background: var(--surface2); border-color: var(--border);">
        @if(auth()->user()->isOwner())
            <div class="flex items-center space-x-2">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded" style="background: var(--accent-glow); color: var(--accent);">Owner</span>
                <span class="text-xs" style="color: var(--text-sub);">Full System Access</span>
            </div>
        @elseif(auth()->user()->isWarehouseManager())
            <div class="flex items-center space-x-2">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded" style="background: var(--green-glow); color: var(--green);">Warehouse</span>
                <span class="text-xs truncate" style="color: var(--text);">{{ auth()->user()->location?->name ?? 'Manager' }}</span>
            </div>
        @elseif(auth()->user()->isShopManager())
            <div class="flex items-center space-x-2">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded" style="background: rgba(139,92,246,.15); color: #8b5cf6;">Shop</span>
                <span class="text-xs truncate" style="color: var(--text);">{{ auth()->user()->location?->name ?? 'Manager' }}</span>
            </div>
        @endif
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto p-4 space-y-4" x-data="{
        openLocations: {{ request()->routeIs('owner.warehouses.*') || request()->routeIs('owner.shops.*') ? 'true' : 'false' }},
        openShopOps: {{ request()->routeIs('shop.*') && !request()->routeIs('shop.dashboard') ? 'true' : 'false' }},
        openWarehouseOps: {{ request()->routeIs('warehouse.*') && !request()->routeIs('warehouse.dashboard') ? 'true' : 'false' }},
        openReports: {{ request()->routeIs('*.reports.*') ? 'true' : 'false' }},
        openInventory: {{ request()->routeIs('*.inventory.*') ? 'true' : 'false' }},
        openShopTransfers: {{ request()->routeIs('shop.transfers.*') ? 'true' : 'false' }},
        openWarehouseTransfers: {{ request()->routeIs('warehouse.transfers.*') ? 'true' : 'false' }}
    }">
        @if(auth()->user()->isOwner())
            {{-- OWNER MENU --}}
            <!-- Overview Section -->
            <div>
                <div class="text-[13px] font-semibold text-[var(--text-dim)] uppercase tracking-wider mb-2 px-3">Overview</div>
                <div class="space-y-1">
                <a href="{{ route('owner.dashboard') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                          {{ request()->routeIs('owner.dashboard') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                    @if(request()->routeIs('owner.dashboard'))
                        <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                    @endif
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-[14px] font-medium">Dashboard</span>
                </a>

                <a href="{{ route('owner.products.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                          {{ request()->routeIs('owner.products.*') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                    @if(request()->routeIs('owner.products.*'))
                        <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                    @endif
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="text-[14px] font-medium">Products</span>
                </a>

                <!-- Locations (Collapsible) -->
                <div>
                    <button @click="openLocations = !openLocations"
                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition-all
                                   {{ request()->routeIs('owner.warehouses.*') || request()->routeIs('owner.shops.*') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-[14px] font-medium">Locations</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform" :class="openLocations ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openLocations" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="{{ route('owner.warehouses.index') }}" wire:navigate
                           class="block px-4 py-1.5 text-[13px] rounded-lg transition-colors
                                  {{ request()->routeIs('owner.warehouses.*') ? 'bg-[var(--accent-dim)] text-[var(--accent)]' : 'text-[var(--text-dim)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                            Warehouses
                        </a>
                        <a href="{{ route('owner.shops.index') }}" wire:navigate
                           class="block px-4 py-1.5 text-[13px] rounded-lg transition-colors
                                  {{ request()->routeIs('owner.shops.*') ? 'bg-[var(--accent-dim)] text-[var(--accent)]' : 'text-[var(--text-dim)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                            Shops
                        </a>
                    </div>
                </div>

                <a href="{{ route('owner.boxes.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                          {{ request()->routeIs('owner.boxes.*') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                    @if(request()->routeIs('owner.boxes.*'))
                        <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                    @endif
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="text-[14px] font-medium">All Boxes</span>
                </a>

                <!-- Reports (Collapsible) -->
                <div>
                    <button @click="openReports = !openReports"
                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition-all
                                   {{ request()->routeIs('owner.reports.*') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="text-[14px] font-medium">Reports</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform" :class="openReports ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openReports" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="{{ route('owner.reports.sales') }}" wire:navigate
                           class="block px-4 py-1.5 text-[13px] rounded-lg transition-colors
                                  {{ request()->routeIs('owner.reports.sales') ? 'bg-[var(--accent-dim)] text-[var(--accent)]' : 'text-[var(--text-dim)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                            Sales
                        </a>
                        <a href="{{ route('owner.reports.inventory') }}" wire:navigate
                           class="block px-4 py-1.5 text-[13px] rounded-lg transition-colors
                                  {{ request()->routeIs('owner.reports.inventory') ? 'bg-[var(--accent-dim)] text-[var(--accent)]' : 'text-[var(--text-dim)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                            Inventory
                        </a>
                        <a href="{{ route('owner.reports.losses') }}" wire:navigate
                           class="block px-4 py-1.5 text-[13px] rounded-lg transition-colors
                                  {{ request()->routeIs('owner.reports.losses') ? 'bg-[var(--accent-dim)] text-[var(--accent)]' : 'text-[var(--text-dim)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                            Losses
                        </a>
                        <a href="{{ route('owner.reports.transfers') }}" wire:navigate
                           class="block px-4 py-1.5 text-[13px] rounded-lg transition-colors
                                  {{ request()->routeIs('owner.reports.transfers') ? 'bg-[var(--accent-dim)] text-[var(--accent)]' : 'text-[var(--text-dim)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                            Transfers
                        </a>
                    </div>
                </div>

                <a href="{{ route('owner.users.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                          {{ request()->routeIs('owner.users.*') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                    @if(request()->routeIs('owner.users.*'))
                        <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                    @endif
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="text-[14px] font-medium">Users</span>
                </a>
            </div>

        @elseif(auth()->user()->isWarehouseManager())
            {{-- WAREHOUSE MANAGER MENU --}}
            <!-- Overview Section -->
            <div>
                <div class="text-[13px] font-semibold text-[var(--text-dim)] uppercase tracking-wider mb-2 px-3">Overview</div>
                <div class="space-y-1">
                    <a href="{{ route('warehouse.dashboard') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('warehouse.dashboard') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('warehouse.dashboard'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="text-[14px] font-medium">Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- Inventory Section -->
            <div>
                <div class="text-[13px] font-semibold text-[var(--text-dim)] uppercase tracking-wider mb-2 px-3">Inventory</div>
                <div class="space-y-1">
                    <a href="{{ route('warehouse.inventory.boxes') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('warehouse.inventory.boxes') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('warehouse.inventory.boxes'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="text-[14px] font-medium">Boxes</span>
                    </a>

                    <a href="{{ route('warehouse.inventory.receive-boxes') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('warehouse.inventory.receive-boxes') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('warehouse.inventory.receive-boxes'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="text-[14px] font-medium">Receive Boxes</span>
                    </a>

                    <a href="{{ route('warehouse.inventory.stock-levels') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('warehouse.inventory.stock-levels') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('warehouse.inventory.stock-levels'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="text-[14px] font-medium">Stock Levels</span>
                    </a>
                </div>
            </div>

            <!-- Operations Section -->
            <div>
                <div class="text-[13px] font-semibold text-[var(--text-dim)] uppercase tracking-wider mb-2 px-3">Operations</div>
                <div class="space-y-1">
                    <a href="{{ route('warehouse.transfers.index') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('warehouse.transfers.*') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('warehouse.transfers.*'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <span class="text-[14px] font-medium">Transfers</span>
                    </a>
                </div>
            </div>

        @elseif(auth()->user()->isShopManager())
            {{-- SHOP MANAGER MENU --}}
            <!-- Overview Section -->
            <div>
                <div class="text-[13px] font-semibold text-[var(--text-dim)] uppercase tracking-wider mb-2 px-3">Overview</div>
                <div class="space-y-1">
                    <a href="{{ route('shop.dashboard') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('shop.dashboard') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('shop.dashboard'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="text-[14px] font-medium">Dashboard</span>
                    </a>

                    <a href="{{ route('shop.pos') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('shop.pos') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('shop.pos'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="text-[14px] font-medium">Point of Sale</span>
                    </a>
                </div>
            </div>

            <!-- Inventory Section -->
            <div>
                <div class="text-[13px] font-semibold text-[var(--text-dim)] uppercase tracking-wider mb-2 px-3">Inventory</div>
                <div class="space-y-1">
                    <a href="{{ route('shop.inventory.stock') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('shop.inventory.*') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('shop.inventory.*'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="text-[14px] font-medium">Shop Stock</span>
                    </a>

                    <a href="{{ route('shop.transfers.index') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('shop.transfers.index') && !request()->routeIs('shop.transfers.request') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('shop.transfers.index') && !request()->routeIs('shop.transfers.request'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <span class="text-[14px] font-medium">Transfers</span>
                    </a>

                    <a href="{{ route('shop.transfers.request') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('shop.transfers.request') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('shop.transfers.request'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="text-[14px] font-medium">Request Transfer</span>
                    </a>
                </div>
            </div>

            <!-- Operations Section -->
            <div>
                <div class="text-[13px] font-semibold text-[var(--text-dim)] uppercase tracking-wider mb-2 px-3">Operations</div>
                <div class="space-y-1">
                    <a href="{{ route('shop.returns.index') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('shop.returns.*') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('shop.returns.*'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        <span class="text-[14px] font-medium">Returns</span>
                    </a>

                    <a href="{{ route('shop.damaged-goods.index') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all relative
                              {{ request()->routeIs('shop.damaged-goods.*') ? 'bg-[var(--accent-glow)] text-[var(--accent)]' : 'text-[var(--text-sub)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
                        @if(request()->routeIs('shop.damaged-goods.*'))
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-[var(--accent)] rounded-r"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="text-[14px] font-medium">Damaged Goods</span>
                    </a>
                </div>
            </div>
        @endif
    </nav>

    <!-- User Info & Logout -->
    <div class="border-t p-4" style="border-color: var(--border); background: var(--surface2);">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-white" style="background: linear-gradient(135deg, var(--accent), #6b8dff);">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold truncate" style="color: var(--text);">
                    {{ auth()->user()->name }}
                </div>
                <div class="text-xs truncate" style="color: var(--text-sub);">
                    {{ auth()->user()->email }}
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all hover:opacity-90"
                    style="background: var(--accent); color: white;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</div>
