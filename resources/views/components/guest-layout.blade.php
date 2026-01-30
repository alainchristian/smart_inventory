<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-50 via-white to-indigo-50">
            <div class="text-center">
                <a href="/" wire:navigate class="block">
                    <x-application-logo class="w-32 h-32 mx-auto mb-4" />
                </a>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">Smart Inventory</h1>
                <p class="text-sm text-gray-600">Manage your inventory efficiently</p>
            </div>

            <div class="w-full sm:max-w-md mt-8 px-6 py-8 bg-white shadow-xl overflow-hidden sm:rounded-xl border border-gray-100">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
