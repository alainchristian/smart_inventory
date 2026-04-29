@props(['reason', 'sessionDate' => null, 'sessionId' => null])

<div class="flex justify-center py-8">
    <div class="w-full rounded-xl p-6" style="max-width:480px;background:var(--surface2);border:1px solid var(--border);">

        @if ($reason === 'no_session')
            {{-- Amber clock icon --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:var(--amber-dim);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--amber);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-sm" style="color:var(--text);">No Active Session</div>
                    <div class="text-xs" style="color:var(--text-dim);">Day not opened yet</div>
                </div>
            </div>
            <p class="text-sm mb-5" style="color:var(--text-dim);">
                Open today's session before recording any activity.
                Sales, returns, transfers and expenses are all unavailable
                until the day is opened.
            </p>
            <a href="{{ route('shop.session.open') }}"
               class="block w-full text-center px-4 py-2 rounded-lg text-sm font-semibold"
               style="background:var(--accent);color:white;">
                Open Today's Session
            </a>

        @elseif ($reason === 'previous_open')
            {{-- Amber warning icon --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:var(--amber-dim);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--amber);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-sm" style="color:var(--text);">Previous Session Not Closed</div>
                    <div class="text-xs" style="color:var(--amber);">Action required</div>
                </div>
            </div>
            <p class="text-sm mb-5" style="color:var(--text-dim);">
                The session for <strong style="color:var(--text);">{{ $sessionDate }}</strong> is still open.
                You must close it before starting a new day.
            </p>
            <a href="{{ route('shop.session.close', ['session' => $sessionId]) }}"
               class="block w-full text-center px-4 py-2 rounded-lg text-sm font-semibold"
               style="background:var(--amber);color:#1a1a1a;">
                Close {{ $sessionDate }} Session
            </a>

        @elseif ($reason === 'session_closed')
            {{-- Neutral lock icon --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:var(--surface);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--text-dim);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-sm" style="color:var(--text);">Today's Session Is Closed</div>
                    <div class="text-xs" style="color:var(--text-dim);">No further activity permitted</div>
                </div>
            </div>
            <p class="text-sm" style="color:var(--text-dim);">
                Today's session has been closed. No further activity
                can be recorded. Review history or wait for tomorrow.
            </p>
        @endif

    </div>
</div>
