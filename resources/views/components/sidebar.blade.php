<div x-data="{ sidebarOpen: false }" @toggle-sidebar.window="sidebarOpen = !sidebarOpen">
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
    <aside class="fixed left-0 top-0 h-screen w-64 bg-white text-gray-900 z-40 transform transition-transform duration-300 lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div class="flex flex-col h-full">
        <!-- Sidebar Header with Logo -->
        <div class="h-16 px-4 flex items-center justify-between border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-gray-900 leading-tight">Smart Inventory</h1>
                    <p class="text-xs text-gray-500 leading-tight">Management System</p>
                </div>
            </div>

            <!-- Close button for mobile -->
            <button @click="sidebarOpen = false" class="lg:hidden p-1 text-gray-500 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Role/Location Badge -->
        <div class="px-4 py-2 bg-gray-50 border-b border-gray-100">
            @if(auth()->user()->isOwner())
                <div class="flex items-center space-x-2">
                    <span class="inline-flex px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded">Owner</span>
                    <span class="text-xs text-gray-500">Full System Access</span>
                </div>
            @elseif(auth()->user()->isWarehouseManager())
                <div class="flex items-center space-x-2">
                    <span class="inline-flex px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">Warehouse</span>
                    <span class="text-xs text-gray-700 truncate">{{ auth()->user()->location?->name ?? 'Manager' }}</span>
                </div>
            @elseif(auth()->user()->isShopManager())
                <div class="flex items-center space-x-2">
                    <span class="inline-flex px-2 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded">Shop</span>
                    <span class="text-xs text-gray-700 truncate">{{ auth()->user()->location?->name ?? 'Manager' }}</span>
                </div>
            @endif
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto p-3 pt-4" x-data="{
            openReports: {{ request()->routeIs('*.reports.*') ? 'true' : 'false' }},
            openInventory: {{ request()->routeIs('*.inventory.*') ? 'true' : 'false' }},
            openTransfers: {{ request()->routeIs('*.transfers.*') ? 'true' : 'false' }},
            openSales: {{ request()->routeIs('*.sales.*') ? 'true' : 'false' }},
            openReturns: {{ request()->routeIs('*.returns.*') ? 'true' : 'false' }}
        }">
            <div class="space-y-1">
                @if(auth()->user()->isOwner())
                    {{-- OWNER MENU --}}

                    <!-- Dashboard -->
                    <a href="{{ route('owner.dashboard') }}"
                       class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('owner.dashboard') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <!-- Products -->
                    <a href="{{ route('owner.products.index') }}"
                       class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('owner.products.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="font-medium">Products</span>
                    </a>

                    <!-- Locations Section -->
                    <div class="pt-2">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Locations</p>

                        <a href="{{ route('owner.warehouses.index') }}"
                           class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('owner.warehouses.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span class="font-medium">Warehouses</span>
                        </a>

                        <a href="{{ route('owner.shops.index') }}"
                           class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('owner.shops.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="font-medium">Shops</span>
                        </a>
                    </div>

                    <!-- Management Section -->
                    <div class="pt-2">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Management</p>

                        <a href="{{ route('owner.users.index') }}"
                           class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('owner.users.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span class="font-medium">Users</span>
                        </a>

                        <!-- Reports Dropdown -->
                        <div>
                            <button @click="openReports = !openReports"
                                    class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('owner.reports.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <span class="font-medium">Reports</span>
                                </div>
                                <svg class="w-4 h-4 transition-transform" :class="openReports ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="openReports" x-collapse class="ml-8 mt-1 space-y-1">
                                <a href="{{ route('owner.reports.inventory') }}"
                                   class="block px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('owner.reports.inventory') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                    Inventory
                                </a>
                                <a href="{{ route('owner.reports.sales') }}"
                                   class="block px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('owner.reports.sales') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                    Sales
                                </a>
                                <a href="{{ route('owner.reports.transfers') }}"
                                   class="block px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('owner.reports.transfers') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                    Transfers
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('owner.settings') }}"
                           class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('owner.settings') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="font-medium">Settings</span>
                        </a>
                    </div>

                @elseif(auth()->user()->isWarehouseManager())
                    {{-- WAREHOUSE MANAGER MENU --}}

                    <!-- Dashboard -->
                    <a href="{{ route('warehouse.dashboard') }}"
                       class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('warehouse.dashboard') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <!-- Inventory Section -->
                    <div class="pt-2">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Inventory</p>

                        <div>
                            <button @click="openInventory = !openInventory"
                                    class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('warehouse.inventory.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <span class="font-medium">Stock Management</span>
                                </div>
                                <svg class="w-4 h-4 transition-transform" :class="openInventory ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="openInventory" x-collapse class="ml-8 mt-1 space-y-1">
                                <a href="{{ route('warehouse.inventory.boxes') }}"
                                   class="block px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('warehouse.inventory.boxes') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                    Boxes
                                </a>
                                <a href="{{ route('warehouse.inventory.receive-boxes') }}"
                                   class="block px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('warehouse.inventory.receive-boxes') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                    Receive Boxes
                                </a>
                                <a href="{{ route('warehouse.inventory.stock-levels') }}"
                                   class="block px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('warehouse.inventory.stock-levels') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                    Stock Levels
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Operations Section -->
                    <div class="pt-2">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Operations</p>

                        <a href="{{ route('warehouse.transfers.index') }}"
                           class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('warehouse.transfers.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            <span class="font-medium">Transfers</span>
                        </a>

                        <!-- Reports Dropdown -->
                        <div>
                            <button @click="openReports = !openReports"
                                    class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('warehouse.reports.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <span class="font-medium">Reports</span>
                                </div>
                                <svg class="w-4 h-4 transition-transform" :class="openReports ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="openReports" x-collapse class="ml-8 mt-1 space-y-1">
                                <a href="{{ route('warehouse.reports.inventory') }}"
                                   class="block px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('warehouse.reports.inventory') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                    Inventory
                                </a>
                                <a href="{{ route('warehouse.reports.transfers') }}"
                                   class="block px-4 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('warehouse.reports.transfers') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                    Transfers
                                </a>
                            </div>
                        </div>
                    </div>

                @elseif(auth()->user()->isShopManager())
                    {{-- SHOP MANAGER MENU --}}

                    <!-- Dashboard -->
                    <a href="{{ route('shop.dashboard') }}"
                       class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('shop.dashboard') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <!-- Point of Sale (Highlighted) -->
                    <a href="{{ route('shop.pos') }}"
                       class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('shop.pos') ? 'bg-green-50 text-green-600 border-2 border-green-500 shadow-sm' : 'bg-green-50 text-green-600 hover:bg-green-100 border border-green-200' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="font-bold">Point of Sale</span>
                    </a>

                    <!-- Sales Section -->
                    <div class="pt-2">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Sales</p>

                        <a href="{{ route('shop.sales.index') }}"
                           class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('shop.sales.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span class="font-medium">Sales History</span>
                        </a>

                        <a href="{{ route('shop.returns.index') }}"
                           class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('shop.returns.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path>
                            </svg>
                            <span class="font-medium">Returns</span>
                        </a>
                    </div>

                    <!-- Inventory Section -->
                    <div class="pt-2">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Inventory</p>

                        <a href="{{ route('shop.inventory.stock') }}"
                           class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('shop.inventory.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <span class="font-medium">Stock Levels</span>
                        </a>

                        <a href="{{ route('shop.transfers.index') }}"
                           class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('shop.transfers.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            <span class="font-medium">Transfers</span>
                        </a>
                    </div>

                    <!-- Reports Section -->
                    <div class="pt-2">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Reports</p>

                        <a href="{{ route('shop.reports.sales') }}"
                           class="flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('shop.reports.*') ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span class="font-medium">Sales Reports</span>
                        </a>
                    </div>

                @endif
            </div>
        </nav>

        <!-- User Profile (Sticky Bottom) -->
        <div class="p-4 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
    </aside>
</div>
