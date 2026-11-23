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

    @if(session('status'))
        <div style="margin-top:14px;padding:9px 11px;border-radius:9px;background:#e8f7f3;color:#0b7a68;font-size:13px">{{ session('status') }}</div>
    @endif

    <div style="margin-top:18px;border-top:1px solid #e2e6f3;padding-top:14px">
        <p class="form-caption" style="margin-bottom:6px">{{ __('Trusted devices') }}</p>
        @forelse($devices as $d)
            <div style="padding:8px 0;border-bottom:1px solid #eef0f8;font-size:12px;line-height:1.45;color:#4a5372">
                <div style="color:#1c2440;font-weight:600">
                    {{ \Illuminate\Support\Str::limit($d->user_agent ?: __('Unknown browser'), 70) }}
                    @if($d->id === $thisDevice)<span style="color:#0e9e86"> · {{ __('this device') }}</span>@endif
                </div>
                <div>{{ __('Last used') }} {{ local_time($d->last_used_at)?->format('d M Y H:i') }} · {{ __('trusted until') }} {{ local_time($d->expires_at)?->format('d M Y') }}</div>
            </div>
        @empty
            <p style="margin:0;font-size:13px;color:#7a81a0;text-align:center">{{ __('No trusted devices. You are asked for a code at every sign-in.') }}</p>
        @endforelse

        @if($devices->isNotEmpty())
            <form method="POST" action="{{ route('two-factor.devices.revoke') }}" style="margin-top:10px">
                @csrf
                <button type="submit" style="width:100%;padding:10px;border:1.5px solid #e2e6f3;border-radius:10px;background:#fff;cursor:pointer;font-size:13px;font-weight:600;color:#e11d48;font-family:inherit">{{ __('Sign out all devices') }}</button>
            </form>
        @endif
    </div>

    <div style="margin-top:14px;text-align:center">
        <a href="{{ route('dashboard') }}" style="font-size:13px;color:#7a81a0">{{ __('Back') }}</a>
    </div>
</div>
