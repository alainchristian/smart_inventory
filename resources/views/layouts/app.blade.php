<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="background-color: var(--bg);">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Operations Centre</title>

    <!-- Google Fonts - DM Sans & DM Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Theme Initialization Script -->
    <script>
        // Lock to light theme
        (function() {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        })();
    </script>
</head>
<body style="background-color: var(--bg); font-family: var(--font);"
      x-data="{ mobileMenuOpen: false }"
      @toggle-mobile-menu.window="mobileMenuOpen = !mobileMenuOpen"
      @close-mobile-menu.window="mobileMenuOpen = false">
    <!-- Mobile Menu Overlay -->
    <div x-show="mobileMenuOpen"
         x-cloak
         @click="mobileMenuOpen = false; $dispatch('close-mobile-menu')"
         class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <!-- Sidebar Component -->
    <livewire:layout.sidebar />

    <!-- Main Content Wrapper -->
    <div class="lg:ml-[var(--sidebar-width)]">
        <!-- Top Navigation Bar -->
        <livewire:layout.topbar />

        <!-- Page Content -->
        <main class="min-h-screen" style="background-color: var(--bg); padding-top: var(--topbar-height);">
            <div class="p-4 sm:p-5 lg:p-7">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts

    <!-- Additional Scripts (Chart.js, etc.) -->
    @stack('scripts')
</body>
</html>
