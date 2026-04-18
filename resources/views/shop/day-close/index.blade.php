<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="mb-6 sm:mb-8 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold" style="color:var(--text);">Cash & Daily Register</h1>
                    <p class="mt-1 text-sm" style="color:var(--text-dim);">Manage today's session, expenses and withdrawals</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('shop.session.history') }}"
                       class="px-3 py-2 sm:px-4 rounded-lg text-sm font-semibold"
                       style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">
                        History
                    </a>
                    <a href="{{ route('shop.day-close.close') }}"
                       class="px-3 py-2 sm:px-4 rounded-lg text-sm font-semibold"
                       style="background:var(--accent);color:white;">
                        Close Register →
                    </a>
                </div>
            </div>

            {{-- Two-column layout on large screens --}}
            <div class="grid gap-6 lg:grid-cols-3">

                {{-- Main column --}}
                <div class="lg:col-span-2 space-y-6">
                    <livewire:shop.day-close.open-session />

                    <div>
                        <div class="text-sm font-semibold mb-3" style="color:var(--text);">Pending Warehouse Requests</div>
                        <livewire:shop.day-close.pending-requests />
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-4">
                    <div class="rounded-xl p-5" style="background:var(--surface-raised);border:1px solid var(--border);">
                        <div class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:var(--text-dim);">Today</div>
                        <div class="text-2xl font-bold mt-2" style="color:var(--text);">{{ now()->format('d M Y') }}</div>
                        <div class="text-sm mt-0.5" style="color:var(--text-dim);">{{ now()->format('l') }}</div>
                    </div>

                    <div class="rounded-xl p-5" style="background:var(--surface-raised);border:1px solid var(--border);">
                        <div class="text-xs font-semibold uppercase tracking-wide mb-3" style="color:var(--text-dim);">Quick Links</div>
                        <div class="space-y-2">
                            <a href="{{ route('shop.day-close.close') }}"
                               class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm"
                               style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                                <span>Close Today's Session</span>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--text-dim);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="{{ route('shop.session.history') }}"
                               class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm"
                               style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                                <span>Session History</span>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--text-dim);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="{{ route('shop.session.requests') }}"
                               class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm"
                               style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                                <span>Expense Requests</span>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--text-dim);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
