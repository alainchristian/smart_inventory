<aside class="fixed left-0 top-0 z-40 h-screen w-64 border-r border-gray-200 bg-white pt-16 transition-transform">
    <div class="h-full flex flex-col">
        <div class="flex-1 overflow-y-auto px-3 scrollbar-hide">
            <div class="mb-4 border-b border-gray-200 pb-4 mt-4">
                <div class="flex items-center space-x-3 rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 p-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold text-lg shadow-md flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-600">System Owner</p>
                    </div>
                </div>
            </div>

            <ul class="space-y-1 font-medium pb-4">
            <li>
                <a href="{{ route('owner.dashboard') }}" class="flex items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100 {{ request()->routeIs('owner.dashboard') ? 'bg-blue-50 text-blue-700' : '' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('owner.dashboard') ? 'text-blue-700' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="ml-3">Dashboard</span>
                </a>
            </li>

            <li>
                <button type="button" class="flex w-full items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100" onclick="toggleSubmenu('users-submenu')">
                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="ml-3 flex-1 whitespace-nowrap text-left">User Management</span>
                    <svg class="h-4 w-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <ul id="users-submenu" class="hidden space-y-1 py-2">
                    <li>
                        <a href="{{ route('owner.users.index') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                            All Users
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.users.create') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                            Add New User
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <button type="button" class="flex w-full items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100" onclick="toggleSubmenu('locations-submenu')">
                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="ml-3 flex-1 whitespace-nowrap text-left">Locations</span>
                    <svg class="h-4 w-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <ul id="locations-submenu" class="hidden space-y-1 py-2">
                    <li>
                        <a href="{{ route('owner.warehouses.index') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                            Warehouses
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.warehouses.create') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                            Add Warehouse
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.shops.index') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                            Shops
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.shops.create') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                            Add Shop
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <button type="button" class="flex w-full items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100" onclick="toggleSubmenu('products-submenu')">
                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span class="ml-3 flex-1 whitespace-nowrap text-left">Products</span>
                    <svg class="h-4 w-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <ul id="products-submenu" class="hidden space-y-1 py-2">
                    <li>
                        <a href="{{ route('owner.products.index') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                            All Products
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.products.create') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                            Add Product
                        </a>
                    </li>
                </ul>
            </li>

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
                        <a href="{{ route('owner.reports.inventory') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                            Inventory Report
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.reports.sales') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                            Sales Report
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('owner.reports.transfers') }}" class="flex items-center rounded-lg px-3 py-2 pl-11 text-gray-700 hover:bg-gray-100">
                            Transfers Report
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="{{ route('owner.settings') }}" class="flex items-center rounded-lg px-3 py-2.5 text-gray-900 hover:bg-gray-100">
                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="ml-3">Settings</span>
                </a>
            </li>
        </ul>
        </div>

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