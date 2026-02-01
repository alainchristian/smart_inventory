<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <nav class="fixed top-0 left-0 right-0 h-16 bg-white border-b border-gray-200 shadow-sm z-30 lg:left-64">
        <div class="h-full flex items-center justify-between px-4 lg:px-6 gap-4">
            <!-- Left: Mobile Menu Button -->
            <div class="flex items-center">
                <!-- Mobile Menu Toggle -->
                <button @click="$dispatch('toggle-sidebar')" class="lg:hidden p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Center: Extended Search Bar -->
            <div class="flex-1 max-w-2xl">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text"
                           placeholder="Search products, boxes, transfers, sales..."
                           class="w-full pl-12 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors">
                </div>
            </div>

            <!-- Right: Language + Notifications + User -->
            <div class="flex items-center space-x-2 lg:space-x-4">
                <!-- Language Selector -->
                <div x-data="{ open: false }" class="relative hidden xl:block">
                    <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 hover:bg-gray-50 rounded-lg transition-colors">
                        <img src="https://flagcdn.com/w20/us.png" alt="English" class="w-5 h-5">
                        <span class="text-sm font-medium text-gray-700">Eng (US)</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Language Dropdown -->
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                        <div class="py-2">
                            <a href="#" class="flex items-center space-x-3 px-4 py-2 hover:bg-gray-50">
                                <img src="https://flagcdn.com/w20/us.png" alt="English" class="w-5 h-5">
                                <span class="text-sm text-gray-700">English (US)</span>
                            </a>
                            <a href="#" class="flex items-center space-x-3 px-4 py-2 hover:bg-gray-50">
                                <img src="https://flagcdn.com/w20/fr.png" alt="French" class="w-5 h-5">
                                <span class="text-sm text-gray-700">Français</span>
                            </a>
                            <a href="#" class="flex items-center space-x-3 px-4 py-2 hover:bg-gray-50">
                                <img src="https://flagcdn.com/w20/rw.png" alt="Kinyarwanda" class="w-5 h-5">
                                <span class="text-sm text-gray-700">Kinyarwanda</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" class="relative p-2 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-orange-500 rounded-full"></span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div x-show="open" x-cloak
                         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                        <div class="p-4 border-b">
                            <h3 class="font-semibold text-gray-900">Notifications</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <a href="#" class="block px-4 py-3 hover:bg-gray-50 border-b">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Low Stock Alert</p>
                                        <p class="text-xs text-gray-600 mt-1">Product XYZ running low</p>
                                        <p class="text-xs text-gray-400 mt-1">2 minutes ago</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="p-3 border-t text-center">
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700">View All</a>
                        </div>
                    </div>
                </div>

                <!-- User Profile -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" class="flex items-center space-x-3 px-2 py-1 hover:bg-gray-50 rounded-lg transition-colors">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4F46E5&color=fff" 
                             alt="{{ auth()->user()->name }}" 
                             class="w-10 h-10 rounded-full">
                        <div class="hidden lg:block text-left">
                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->role?->label() ?? 'Admin' }}</p>
                        </div>
                        <svg class="hidden lg:block w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- User Dropdown -->
                    <div x-show="open" x-cloak
                         class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                        <div class="p-4 border-b">
                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="py-2">
                            <a href="{{ route('profile') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                My Profile
                            </a>
                        </div>
                        <div class="py-2 border-t">
                            <button wire:click="logout" class="w-full flex items-center space-x-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <style>
    [x-cloak] { display: none !important; }
    </style>
</div>