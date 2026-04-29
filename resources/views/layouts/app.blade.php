<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="background-color: var(--bg);">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Operations Centre</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    {{-- Chart.js loaded ONCE here in the head. --}}
    {{-- Loading it inside @push('scripts') or dynamically via createElement fails --}}
    {{-- with Livewire SPA navigation because @stack('scripts') does not re-execute --}}
    {{-- on navigate, and async script injection races against Alpine component init. --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    {{-- ApexCharts for sales analytics --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>

    <!-- Theme lock -->
    <script>
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

    <livewire:layout.sidebar />

    <div class="lg:ml-[var(--sidebar-width)]">
        <livewire:layout.topbar />
        <main class="min-h-screen" style="background-color: var(--bg); padding-top: var(--topbar-height); overflow-anchor: none;">
            <div class="p-2 sm:p-5 lg:p-8 xl:p-10">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts

    @stack('scripts')
</body>
</html>