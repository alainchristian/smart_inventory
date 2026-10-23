{{-- Two-step sign-in: the code from the authenticator app (or a one-time recovery code). --}}
<div x-data="{ recovery: {{ old('recovery') ? 'true' : 'false' }} }">
    <div class="form-avatar-wrap">
        <span class="form-avatar-line"></span>
        <div class="form-avatar-circle">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2l8 3v6c0 5-3.4 9.3-8 11-4.6-1.7-8-6-8-11V5l8-3z"/>
                <path d="M9 12l2 2 4-4"/>
            </svg>
        </div>
        <span class="form-avatar-line"></span>
    </div>
    <p class="form-caption" x-text="recovery ? '{{ __('Enter a recovery code') }}' : '{{ __('Enter the 6-digit code from your authenticator app') }}'"></p>

    <form method="POST" action="{{ route('two-factor.verify') }}" novalidate>
        @csrf
        <input type="hidden" name="recovery" :value="recovery ? 1 : 0">

        <div class="field-group">
            <label for="code" class="sr-only">Code</label>
            <div class="field-input-wrap">
                <span class="field-icon-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </span>
                <input id="code" name="code" type="text" autofocus required
                       class="field-input @error('code') has-error @enderror"
                       :inputmode="recovery ? 'text' : 'numeric'"
                       :maxlength="recovery ? 16 : 7"
                       :placeholder="recovery ? 'xxxxx-xxxxx' : '123 456'"
                       autocomplete="one-time-code" autocapitalize="off" spellcheck="false">
            </div>
            @error('code')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-login">{{ __('Verify') }}</button>
    </form>

    <div style="margin-top:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
        <button type="button" @click="recovery = !recovery"
                style="background:none;border:none;padding:0;cursor:pointer;font-size:13px;font-weight:600;color:#3b6fd4;font-family:inherit">
            <span x-show="!recovery">{{ __('Use a recovery code') }}</span>
            <span x-show="recovery" x-cloak>{{ __('Use my authenticator app') }}</span>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background:none;border:none;padding:0;cursor:pointer;font-size:13px;color:#7a81a0;font-family:inherit">{{ __('Sign out') }}</button>
        </form>
    </div>

    <div class="form-divider"></div>
    <div class="form-footer">{{ __('Signed in as :email', ['email' => $user->email]) }}</div>
</div>
