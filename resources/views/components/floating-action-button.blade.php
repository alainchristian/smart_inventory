{{--
    Professional Floating Action Button Component

    Usage:
    <x-floating-action-button>
        <x-slot name="actions">
            <a href="..." class="fab-action">
                <div class="fab-action-icon bg-blue-500">
                    <svg>...</svg>
                </div>
                <div class="fab-action-content">
                    <p class="fab-action-title">Title</p>
                    <p class="fab-action-description">Description</p>
                </div>
            </a>
        </x-slot>
    </x-floating-action-button>
--}}

<div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50">
    <!-- Quick Actions Menu (appears above FAB) -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform translate-y-8 scale-95"
         @click.away="open = false"
         x-cloak
         class="absolute bottom-20 right-0 mb-3 w-72 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden backdrop-blur-sm">

        <!-- Header with Gradient -->
        <div class="px-5 py-4 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 relative overflow-hidden">
            <div class="absolute inset-0 bg-white opacity-10"></div>
            <div class="relative">
                <h3 class="text-base font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Quick Actions
                </h3>
                <p class="text-xs text-blue-100 mt-1">Frequently used features</p>
            </div>
        </div>

        <!-- Actions List -->
        <div class="py-2 max-h-96 overflow-y-auto">
            {{ $actions }}
        </div>

        <!-- Footer Tip (optional) -->
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
            <p class="text-xs text-gray-500 text-center">
                <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Click outside to close
            </p>
        </div>
    </div>

    <!-- FAB Button with Enhanced Design -->
    <button @click="open = !open"
            class="relative w-16 h-16 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-700 hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 text-white rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-105 active:scale-95 transition-all duration-200 flex items-center justify-center group overflow-hidden">

        <!-- Ripple Effect Background -->
        <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-200 rounded-full"></div>

        <!-- Plus Icon with Rotation -->
        <svg class="w-7 h-7 transition-transform duration-300 relative z-10"
             :class="{ 'rotate-45': open }"
             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
        </svg>

        <!-- Pulse Ring Animation -->
        <span class="absolute inset-0 rounded-full bg-blue-400 opacity-0 group-hover:opacity-30 animate-ping"></span>
    </button>

    <!-- Tooltip on Hover -->
    <div x-show="!open"
         class="absolute bottom-20 right-2 px-3 py-2 bg-gray-900 text-white text-xs font-medium rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap"
         x-transition>
        Quick Actions
        <div class="absolute bottom-0 right-6 transform translate-y-1/2 rotate-45 w-2 h-2 bg-gray-900"></div>
    </div>
</div>

<style>
    @keyframes ping {
        75%, 100% {
            transform: scale(1.5);
            opacity: 0;
        }
    }

    .animate-ping {
        animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
    }

    /* Custom scrollbar for actions list */
    .fab-actions-list::-webkit-scrollbar {
        width: 4px;
    }

    .fab-actions-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .fab-actions-list::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 2px;
    }

    .fab-actions-list::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
</style>
