<?php

use App\Livewire\Actions\Logout;
use App\Models\Alert;
use App\Models\Product;
use App\Models\Box;
use App\Models\Transfer;
use App\Enums\TransferStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component
{
    public $notifications = [];
    public $unreadCount = 0;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    #[On('notification-updated')]
    #[On('alert-created')]
    #[On('stock-updated')]
    #[On('transfer-updated')]
    public function loadNotifications(): void
    {
        $notifications = collect();

        // Get all unresolved alerts for the user from database
        $userAlerts = Alert::forUser(auth()->id())
            ->unresolved()
            ->notDismissed()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($userAlerts as $alert) {
            $colorMap = [
                'CRITICAL' => 'red',
                'WARNING' => 'orange',
                'INFO' => 'blue',
            ];

            $notifications->push([
                'id' => 'alert-' . $alert->id,
                'type' => 'alert',
                'icon' => 'alert',
                'color' => $colorMap[$alert->severity->name] ?? 'gray',
                'title' => $alert->title,
                'message' => $alert->message,
                'time' => $alert->created_at->diffForHumans(),
                'url' => $alert->action_url,
                'is_read' => $alert->is_read,
            ]);
        }

        // Check for low stock products
        $lowStockProducts = Product::active()
            ->with('boxes')
            ->get()
            ->filter(function ($product) {
                $totalStock = $product->boxes()
                    ->whereIn('status', ['full', 'partial'])
                    ->sum('items_remaining');
                return $totalStock <= $product->low_stock_threshold && $totalStock > 0;
            })
            ->take(5);

        foreach ($lowStockProducts as $product) {
            $totalStock = $product->boxes()
                ->whereIn('status', ['full', 'partial'])
                ->sum('items_remaining');

            $notifications->push([
                'id' => 'low-stock-' . $product->id,
                'type' => 'low_stock',
                'icon' => 'package',
                'color' => 'orange',
                'title' => 'Low Stock Alert',
                'message' => $product->name . ' is running low (' . $totalStock . ' items remaining)',
                'time' => 'Just now',
                'url' => '#',
                'is_read' => false,
            ]);
        }

        // Check for expiring products
        $expiringBoxes = Box::whereIn('status', ['full', 'partial'])
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now())
            ->with('product')
            ->orderBy('expiry_date', 'asc')
            ->limit(5)
            ->get();

        foreach ($expiringBoxes as $box) {
            $daysUntilExpiry = now()->diffInDays($box->expiry_date);
            $notifications->push([
                'id' => 'expiring-' . $box->id,
                'type' => 'expiring',
                'icon' => 'clock',
                'color' => 'yellow',
                'title' => 'Product Expiring Soon',
                'message' => $box->product->name . ' (Box ' . $box->box_code . ') expires in ' . $daysUntilExpiry . ' days',
                'time' => $daysUntilExpiry . ' days',
                'url' => '#',
                'is_read' => false,
            ]);
        }

        // Check for pending transfers
        $pendingTransfers = Transfer::where('status', TransferStatus::PENDING)
            ->with(['toShop', 'requestedBy'])
            ->orderBy('requested_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($pendingTransfers as $transfer) {
            $notifications->push([
                'id' => 'transfer-' . $transfer->id,
                'type' => 'transfer',
                'icon' => 'truck',
                'color' => 'blue',
                'title' => 'Pending Transfer',
                'message' => 'Transfer ' . $transfer->transfer_number . ' to ' . $transfer->toShop->name . ' awaiting approval',
                'time' => $transfer->requested_at->diffForHumans(),
                'url' => '#',
                'is_read' => false,
            ]);
        }

        $this->notifications = $notifications->take(10)->toArray();
        $this->unreadCount = $notifications->where('is_read', false)->count();
    }

    public function markAllAsRead(): void
    {
        // Mark all user's unread alerts as read
        Alert::forUser(auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $this->loadNotifications();
        $this->dispatch('notification-updated');
    }

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div wire:poll.30s="loadNotifications">
    <nav class="fixed top-0 left-0 right-0 h-16 bg-white border-b border-gray-200 shadow-sm z-30 lg:left-64">
        <div class="h-full flex items-center justify-between px-4 lg:px-6 gap-3">
            <!-- Left: Mobile Menu Button -->
            <div class="flex items-center lg:hidden">
                <!-- Mobile Menu Toggle -->
                <button @click="$dispatch('toggle-sidebar')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors -ml-2">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Center: Extended Search Bar -->
            <div class="flex-1 max-w-2xl">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 lg:pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text"
                           placeholder="Search products, boxes, transfers..."
                           class="w-full pl-10 lg:pl-12 pr-4 py-2 lg:py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors placeholder:text-gray-400">
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
                        @if($unreadCount > 0)
                        <span class="absolute top-1 right-1 w-5 h-5 bg-orange-500 rounded-full flex items-center justify-center">
                            <span class="text-white text-xs font-bold">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        </span>
                        @endif
                    </button>

                    <!-- Notifications Dropdown -->
                    <div x-show="open" x-cloak
                         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                        <div class="p-4 border-b flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">Notifications</h3>
                            @if($unreadCount > 0)
                            <button wire:click="markAllAsRead" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                Mark all read
                            </button>
                            @endif
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            @forelse($notifications as $notification)
                            <a href="{{ $notification['url'] ?? '#' }}"
                               class="block px-4 py-3 hover:bg-gray-50 border-b transition-colors {{ !$notification['is_read'] ? 'bg-blue-50' : '' }}">
                                <div class="flex items-start space-x-3">
                                    <!-- Icon based on notification type -->
                                    @php
                                        $iconColors = [
                                            'red' => 'bg-red-100 text-red-600',
                                            'orange' => 'bg-orange-100 text-orange-600',
                                            'yellow' => 'bg-yellow-100 text-yellow-600',
                                            'blue' => 'bg-blue-100 text-blue-600',
                                            'green' => 'bg-green-100 text-green-600',
                                        ];
                                        $colorClass = $iconColors[$notification['color']] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <div class="w-10 h-10 {{ $colorClass }} rounded-lg flex items-center justify-center flex-shrink-0">
                                        @if($notification['icon'] === 'alert')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        @elseif($notification['icon'] === 'package')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        @elseif($notification['icon'] === 'clock')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        @elseif($notification['icon'] === 'truck')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $notification['title'] }}</p>
                                        <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ $notification['message'] }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $notification['time'] }}</p>
                                    </div>
                                    @if(!$notification['is_read'])
                                    <div class="w-2 h-2 bg-blue-600 rounded-full flex-shrink-0 mt-2"></div>
                                    @endif
                                </div>
                            </a>
                            @empty
                            <div class="px-4 py-8 text-center">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-gray-500 font-medium">All caught up!</p>
                                <p class="text-xs text-gray-400 mt-1">No new notifications</p>
                            </div>
                            @endforelse
                        </div>
                        @if(count($notifications) > 0)
                        <div class="p-3 border-t text-center">
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700">View All Notifications</a>
                        </div>
                        @endif
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
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    </style>
</div>