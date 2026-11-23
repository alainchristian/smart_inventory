{{-- Signed-in owner / admin: get a fresh set of recovery codes (needs a current code from the app). --}}
<div>
    <p class="form-caption">{{ __('Recovery codes') }}</p>
    <p style="margin:0 0 14px;font-size:13px;line-height:1.55;color:#4a5372;text-align:center">
        {{ __('You have :n unused recovery codes. Make a new set if you lost them or used most of them. The old codes stop working.', ['n' => $left]) }}
    </p>

    <form method="POST" action="{{ route('two-factor.regenerate') }}" novalidate>
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
                <input id="code" name="code" type="text" autofocus required inputmode="numeric" maxlength="7"
                       class="field-input @error('code') has-error @enderror"
                       placeholder="123 456" autocomplete="one-time-code" spellcheck="false">
            </div>
            @error('code')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn-login">{{ __('Enter the code from your app to make new codes') }}</button>
    </form>

    <div style="margin-top:14px;text-align:center">
        <a href="{{ route('dashboard') }}" style="font-size:13px;color:#7a81a0">{{ __('Back') }}</a>
    </div>
</div>
