<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="background-color: var(--surface);">
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
<body style="background-color: var(--surface); font-family: var(--font);"
      x-data="{ mobileMenuOpen: false }"
      @toggle-mobile-menu.window="mobileMenuOpen = !mobileMenuOpen"
      @close-mobile-menu.window="mobileMenuOpen = false">

    {{-- Global toast stack — catches every $this->dispatch('notification', ['type'=>..,'message'=>..])
         call app-wide. Livewire sends a single positional array arg as
         event.detail = [{ type, message }] (array-wrapped), not a plain
         object — unwrap it or the toast renders blank/undefined. --}}
    <div x-data="{
            toasts: [],
            toast(detail) {
              const d = Array.isArray(detail) ? detail[0] : detail;
              const id = Date.now() + Math.random();
              this.toasts.push({ id, msg: d?.message ?? '', type: d?.type ?? 'info' });
              setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 3800);
            }
          }"
         @notification.window="toast($event.detail)"
         style="position:fixed;top:calc(var(--topbar-height) + 12px);right:16px;z-index:9000;
                display:flex;flex-direction:column;gap:7px;pointer-events:none;max-width:calc(100vw - 32px)">
        <template x-for="t in toasts" :key="t.id">
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 :style="`pointer-events:auto;padding:10px 14px;border-radius:8px;font-size:13px;font-weight:600;
                    font-family:var(--font);box-shadow:0 4px 16px rgba(26,31,54,.15);max-width:360px;
                    background:${t.type==='success'?'var(--green)':t.type==='error'?'var(--red)':t.type==='warning'?'var(--amber)':'var(--accent)'};color:#fff`"
                 x-text="t.msg">
            </div>
        </template>
    </div>

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
        <main class="min-h-screen" style="background-color: var(--surface); padding-top: var(--topbar-height); overflow-anchor: none;">
            @if (session('error'))
            <div style="background:var(--red-dim);border-bottom:1px solid var(--red);padding:10px 20px;
                        display:flex;align-items:center;gap:10px;font-size:13px;color:var(--red)">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
            @endif
            <div class="p-4 sm:p-5 lg:p-8 xl:p-10">
                {{ $slot }}
            </div>
        </main>
    </div>

    <livewire:transactions.live-feed />

    @livewireScripts

    @stack('scripts')

    <script>
    // Redirect to login silently on session expiry (419) instead of showing error dialogs
    document.addEventListener('livewire:init', function () {
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419) {
                    preventDefault();
                    window.location.href = '{{ route('login') }}';
                }
            });
        });
    });
    </script>
</body>
</html>