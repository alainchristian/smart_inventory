<x-app-layout>
    <div class="py-2 sm:py-6">
        <div class="max-w-2xl mx-auto px-0 sm:px-4 lg:px-8">
            <div class="mb-4 sm:mb-6 flex items-center gap-3">
                <a href="{{ route('shop.session.open') }}"
                   class="p-2 rounded-lg transition-colors"
                   style="background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold" style="color:var(--text);">Record Bank Deposit</h1>
                    <p class="mt-0.5 text-xs" style="color:var(--text-dim);">Cash deposited to the bank mid-session. Reduces expected cash in drawer.</p>
                </div>
            </div>

            @php
                $user = auth()->user();
                $shopId = $user->isOwner() ? null : $user->location_id;
                $activeSession = $shopId
                    ? \App\Models\DailySession::forShop($shopId)
                        ->forDate(today()->toDateString())
                        ->where('status', 'open')
                        ->first()
                    : null;
            @endphp

            @if ($activeSession)
                <div class="rounded-xl p-5 sm:p-6" style="background:var(--surface2);border:1px solid var(--border);">
                    <livewire:shop.day-close.add-bank-deposit :dailySessionId="$activeSession->id" />
                </div>

                @if ($activeSession->bankDeposits()->whereNull('deleted_at')->sum('amount') > 0)
                    <div class="mt-4 px-4 py-3 rounded-lg text-sm font-medium"
                         style="background:var(--accent-dim);color:var(--accent);border:1px solid var(--accent);">
                        Total banked today:
                        <span class="font-mono font-bold">
                            {{ number_format($activeSession->bankDeposits()->whereNull('deleted_at')->sum('amount')) }} RWF
                        </span>
                        across {{ $activeSession->bankDeposits()->whereNull('deleted_at')->count() }} deposit(s)
                    </div>
                @endif
            @else
                <div class="rounded-xl p-8 text-center" style="background:var(--surface2);border:1px solid var(--border);">
                    <div class="w-12 h-12 rounded-full mx-auto mb-4 flex items-center justify-center" style="background:var(--amber-dim);">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--amber);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div class="text-sm font-semibold mb-2" style="color:var(--text);">No Active Session</div>
                    <div class="text-xs mb-4" style="color:var(--text-dim);">Open today's session before recording bank deposits.</div>
                    <a href="{{ route('shop.session.open') }}"
                       class="inline-block px-4 py-2 rounded-lg text-sm font-semibold"
                       style="background:var(--accent);color:white;">
                        Open Today's Session
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
