<aside class="fixed left-0 top-0 z-40 h-screen w-64 border-r border-gray-200 bg-white pt-14 transition-transform">
    <div class="h-full flex flex-col">
        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto px-3 scrollbar-hide">
            <!-- User Profile Section -->
            <div class="mb-4 border-b border-gray-200 pb-4 mt-4">
                <div class="flex items-center space-x-3 rounded-lg bg-gradient-to-r from-green-50 to-emerald-50 p-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white font-bold text-lg shadow-md flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-600">Shop Manager</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <ul class="space-y-1 font-medium pb-4">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('shop.dashboard') }}" class="flex items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100 {{ request()->routeIs('shop.dashboard') ? 'bg-green-50 text-green-700' : '' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('shop.dashboard') ? 'text-green-700' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>

                <!-- Point of Sale -->
                <li>
                    <a href="{{ route('shop.pos') }}" class="flex items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100 {{ request()->routeIs('shop.pos') ? 'bg-green-50 text-green-700' : '' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('shop.pos') ? 'text-green-700' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <span class="ml-3">Point of Sale</span>
                    </a>
                </li>

                <!-- Sales -->
                <li>
                    <button type="button" class="flex w-full items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100" onclick="toggleSubmenu('sales-submenu')">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="ml-3 flex-1 whitespace-nowrap text-left">Sales</span>
                        <svg class="h-4 w-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <ul id="sales-submenu" class="hidden space-y-1 py-2">
                        <li>
                            <a href="{{ route('shop.sales.index') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                                All Sales
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
                            <a href="{{ route('shop.transfers.index') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                                All Transfers
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('shop.transfers.request') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                                Request Transfer
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Returns -->
                <li>
                    <button type="button" class="flex w-full items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100" onclick="toggleSubmenu('returns-submenu')">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                        <span class="ml-3 flex-1 whitespace-nowrap text-left">Returns</span>
                        <svg class="h-4 w-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <ul id="returns-submenu" class="hidden space-y-1 py-2">
                        <li>
                            <a href="{{ route('shop.returns.index') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                                All Returns
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('shop.returns.create') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                                Process Return
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Inventory -->
                <li>
                    <a href="{{ route('shop.inventory.stock') }}" class="flex items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="ml-3">Stock Levels</span>
                    </a>
                </li>

                <!-- Reports -->
                <li>
                    <a href="{{ route('shop.reports.sales') }}" class="flex items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="ml-3">Sales Report</span>
                    </a>
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
