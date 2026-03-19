<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:var(--bg);font-family:var(--font);padding:20px">
<div style="width:100%;max-width:420px">

    {{-- Header --}}
    <div style="text-align:center;margin-bottom:28px">
        <div style="font-size:24px;font-weight:800;color:var(--text);
                    letter-spacing:-.5px;margin-bottom:6px">
            Set Your Password
        </div>
        <div style="font-size:14px;color:var(--text-dim);line-height:1.6">
            Your account was just created. Choose a strong password
            to secure your access before continuing.
        </div>
    </div>

    {{-- Card --}}
    <div style="background:var(--surface);border:1px solid var(--border);
                border-radius:var(--r);padding:28px">

        {{-- New password --}}
        <div style="margin-bottom:16px">
            <label style="display:block;font-size:13px;font-weight:600;
                          color:var(--text-sub);margin-bottom:6px">
                New Password <span style="color:var(--red)">*</span>
            </label>
            <div style="position:relative">
                <input wire:model="password"
                       type="{{ $show ? 'text' : 'password' }}"
                       style="width:100%;padding:10px 40px 10px 12px;
                              border:1.5px solid var(--border);border-radius:9px;
                              font-size:14px;background:var(--surface);color:var(--text);
                              outline:none;box-sizing:border-box;font-family:var(--font);
                              transition:border-color .15s"
                       placeholder="Min. 8 characters"
                       autocomplete="new-password">
                <button type="button" wire:click="$toggle('show')"
                        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                               background:none;border:none;cursor:pointer;color:var(--text-dim)">
                    @if($show)
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                    @else
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    @endif
                </button>
            </div>
            @error('password')
            <div style="font-size:11px;color:var(--red);margin-top:4px">{{ $message }}</div>
            @enderror
        </div>

        {{-- Confirm password --}}
        <div style="margin-bottom:24px">
            <label style="display:block;font-size:13px;font-weight:600;
                          color:var(--text-sub);margin-bottom:6px">
                Confirm Password <span style="color:var(--red)">*</span>
            </label>
            <div style="position:relative">
                <input wire:model="password_confirmation"
                       type="{{ $show_confirm ? 'text' : 'password' }}"
                       style="width:100%;padding:10px 40px 10px 12px;
                              border:1.5px solid var(--border);border-radius:9px;
                              font-size:14px;background:var(--surface);color:var(--text);
                              outline:none;box-sizing:border-box;font-family:var(--font);
                              transition:border-color .15s"
                       placeholder="Repeat your password"
                       autocomplete="new-password">
                <button type="button" wire:click="$toggle('show_confirm')"
                        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                               background:none;border:none;cursor:pointer;color:var(--text-dim)">
                    @if($show_confirm)
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                    @else
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    @endif
                </button>
            </div>
        </div>

        {{-- Submit --}}
        <button wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                style="width:100%;padding:12px;background:var(--accent);color:#fff;
                       border:none;border-radius:10px;font-size:14px;font-weight:700;
                       cursor:pointer;font-family:var(--font);
                       box-shadow:0 3px 10px rgba(59,111,212,.25);transition:opacity .15s">
            <span wire:loading.remove wire:target="save">Set Password & Continue</span>
            <span wire:loading wire:target="save" style="display:none">Saving…</span>
        </button>

    </div>

    {{-- Footer --}}
    <div style="text-align:center;margin-top:16px;font-size:12px;color:var(--text-dim)">
        Signed in as <strong style="color:var(--text-sub)">{{ auth()->user()->email }}</strong> ·
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault();document.getElementById('cp-logout').submit();"
           style="color:var(--accent);text-decoration:none;font-weight:600">
            Sign out
        </a>
    </div>
    <form id="cp-logout" action="{{ route('logout') }}" method="POST" style="display:none">
        @csrf
    </form>

</div>
</div>
