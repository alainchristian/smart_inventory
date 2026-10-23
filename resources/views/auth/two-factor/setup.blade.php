{{-- First sign-in for platform support: link an authenticator app. --}}
<div>
    <p class="form-caption">{{ __('Set up two-step verification') }}</p>
    <p style="margin:0 0 14px;font-size:13px;line-height:1.55;color:#4a5372;text-align:center">
        {{ __('This account can manage every user, so it needs a second step. Scan the code with an authenticator app (Google Authenticator, Microsoft Authenticator or Authy), then enter the 6 digits it shows.') }}
    </p>

    <div style="display:flex;justify-content:center;margin-bottom:10px">
        <div style="padding:8px;background:#fff;border:1px solid #e2e6f3;border-radius:12px;line-height:0">{!! $qr !!}</div>
    </div>

    <details style="margin-bottom:16px;text-align:center">
        <summary style="cursor:pointer;font-size:12px;color:#7a81a0">{{ __("Can't scan? Type the key instead") }}</summary>
        <div style="margin-top:8px;font-family:'DM Mono',monospace;font-size:14px;letter-spacing:.5px;color:#1c2440;word-break:break-all">{{ $manual }}</div>
    </details>

    <form method="POST" action="{{ route('two-factor.confirm') }}" novalidate>
        @csrf
        <div class="field-group">
            <label for="code" class="sr-only">Code</label>
            <div class="field-input-wrap">
                <span class="field-icon-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </span>
                <input id="code" name="code" type="text" inputmode="numeric" maxlength="7" autofocus required
                       class="field-input @error('code') has-error @enderror"
                       placeholder="123 456" autocomplete="one-time-code">
            </div>
            @error('code')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn-login">{{ __('Turn on and continue') }}</button>
    </form>

    <div style="margin-top:14px;text-align:center">
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" style="background:none;border:none;padding:0;cursor:pointer;font-size:13px;color:#7a81a0;font-family:inherit">{{ __('Sign out') }}</button>
        </form>
    </div>

    <div class="form-divider"></div>
    <div class="form-footer">{{ __('Signed in as :email', ['email' => $user->email]) }}</div>
</div>
