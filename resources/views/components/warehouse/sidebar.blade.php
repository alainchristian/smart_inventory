<aside class="fixed left-0 top-0 z-40 h-screen w-64 border-r border-gray-200 bg-white pt-14 transition-transform">
    <div class="h-full flex flex-col">
        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto px-3 scrollbar-hide">
            <!-- User Profile Section -->
            <div class="mb-4 border-b border-gray-200 pb-4 mt-4">
                <div class="flex items-center space-x-3 rounded-lg bg-gradient-to-r from-amber-50 to-orange-50 p-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white font-bold text-lg shadow-md flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-600">Warehouse Manager</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <ul class="space-y-1 font-medium pb-4">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('warehouse.dashboard') }}" class="flex items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100 {{ request()->routeIs('warehouse.dashboard') ? 'bg-amber-50 text-amber-700' : '' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('warehouse.dashboard') ? 'text-amber-700' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>

                <!-- Inventory Management -->
                <li>
                    <button type="button" class="flex w-full items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100" onclick="toggleSubmenu('inventory-submenu')">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="ml-3 flex-1 whitespace-nowrap text-left">Inventory</span>
                        <svg class="h-4 w-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <ul id="inventory-submenu" class="hidden space-y-1 py-2">
                        <li>
                            <a href="{{ route('warehouse.inventory.boxes') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                                All Boxes
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('warehouse.inventory.receive-boxes') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                                Receive Boxes
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('warehouse.inventory.stock-levels') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                                Stock Levels
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Transfers -->
                <li>
                    <button type="button" class="flex w-full items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100" onclick="toggleSubmenu('transfers-submenu')">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <span class="ml-3 flex-1 whitespace-nowrap text-left">Transfers</span>
                        <svg class="h-4 w-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <ul id="transfers-submenu" class="hidden space-y-1 py-2">
                        <li>
                            <a href="{{ route('warehouse.transfers.index') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                                All Transfers
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Reports -->
                <li>
                    <button type="button" class="flex w-full items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100" onclick="toggleSubmenu('reports-submenu')">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="ml-3 flex-1 whitespace-nowrap text-left">Reports</span>
                        <svg class="h-4 w-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <ul id="reports-submenu" class="hidden space-y-1 py-2">
                        <li>
                            <a href="{{ route('warehouse.reports.inventory') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                                Inventory Report
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('warehouse.reports.transfers') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                                Transfers Report
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <!-- Fixed Logout Button at Bottom -->
        <div class="border-t border-gray-200 bg-white px-3 py-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center rounded-lg px-3 py-2.5 text-red-600 hover:bg-red-50 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="ml-3 font-medium">Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<style>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>

<script>
function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    const button = submenu.previousElementSibling;
    const arrow = button.querySelector('svg:last-child');

    if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
    } else {
        submenu.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
}
</script>
