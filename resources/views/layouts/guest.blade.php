<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="background-color: var(--bg);">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

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
    <body style="background-color: var(--bg); font-family: var(--font);">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="background-color: var(--bg);">
            <div>
                <a href="/" wire:navigate>
                    <div class="w-16 h-16 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, var(--accent), #6b8dff);">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="color: white;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 shadow-xl overflow-hidden sm:rounded-xl border" style="background: var(--surface); border-color: var(--border);">
                {{ $slot }}
            </div>
        </div>

        @livewireScripts
    </body>
</html>
