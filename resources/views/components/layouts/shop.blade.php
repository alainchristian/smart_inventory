<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Smart Inventory') }} - Shop Manager</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-50" x-data="{ notificationsOpen: false, profileOpen: false }">
        <!-- Top Navigation Bar -->
        <nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 shadow-sm">
            <div class="px-4 lg:px-6">
                <div class="flex items-center justify-between h-16">
                    <!-- Left: Logo -->
                    <a href="{{ route('shop.dashboard') }}" class="flex items-center space-x-3 flex-shrink-0 min-w-[200px]">
                        <x-application-logo class="h-8 w-8" />
                        <span class="text-xl font-bold text-green-600">Smart Inventory</span>
                    </a>

                    <!-- Center: Search -->
                    <div class="flex-1 max-w-2xl mx-6">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                            <input type="text"
                                   placeholder="Search products, transfers, sales..."
                                   class="block w-full pl-10 pr-4 py-2.5 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all">
                        </div>
                    </div>

                    <!-- Right: Notifications + Profile -->
                    <div class="flex items-center space-x-4 flex-shrink-0">
                        <!-- Notifications -->
                        <div class="relative">
                            <button @click="notificationsOpen = !notificationsOpen; profileOpen = false"
                                    type="button"
                                    class="p-2 text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 relative transition-all duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>

                            <!-- Notifications Dropdown -->
                            <div x-show="notificationsOpen"
                                 @click.away="notificationsOpen = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-auto right-0 top-full mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50 overflow-hidden"
                                 style="display: none;">
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    <!-- Sample Notifications -->
                                    <a href="#" class="block p-4 hover:bg-gray-50 transition-colors duration-200">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 w-2 h-2 mt-2 bg-blue-500 rounded-full"></div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900">Low Stock Alert</p>
                                                <p class="text-xs text-gray-600 mt-1">Product XYZ is running low in your shop</p>
                                                <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="#" class="block p-4 hover:bg-gray-50 transition-colors duration-200">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 w-2 h-2 mt-2 bg-green-500 rounded-full"></div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900">Transfer Arrived</p>
                                                <p class="text-xs text-gray-600 mt-1">Transfer #1234 has arrived at your shop</p>
                                                <p class="text-xs text-gray-400 mt-1">5 hours ago</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="p-3 bg-gray-50 border-t border-gray-200">
                                    <a href="#" class="block text-center text-sm font-medium text-green-600 hover:text-green-700">View All Notifications</a>
                                </div>
                            </div>
                        </div>

                        <!-- Profile -->
                        <div class="relative">
                            <button @click="profileOpen = !profileOpen; notificationsOpen = false"
                                    type="button"
                                    class="flex items-center gap-2 rounded-lg hover:bg-gray-50 p-1 transition-all duration-200">
                                <span class="text-sm font-medium text-gray-700 hidden lg:block">{{ auth()->user()->name }}</span>
                                <div class="relative">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white font-semibold text-sm shadow-md">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </div>
                                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></span>
                                </div>
                            </button>

                            <!-- Profile Dropdown -->
                            <div x-show="profileOpen"
                                 @click.away="profileOpen = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-auto right-0 top-full mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50 overflow-hidden"
                                 style="display: none;">
                                <!-- User Info Header -->
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ auth()->user()->email }}</p>
                                </div>

                                <!-- Menu Items -->
                                <div class="py-2">
                                    <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Profile
                                    </a>
                                    <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Settings
                                    </a>
                                </div>

                                <!-- Logout -->
                                <div class="border-t border-gray-200">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar -->
        <x-shop.sidebar />

        <!-- Main Content -->
        <div class="p-4 sm:ml-64">
            <div class="mt-14">
                @if (isset($header))
                    <header class="mb-6">
                        <div class="flex items-center justify-between">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
