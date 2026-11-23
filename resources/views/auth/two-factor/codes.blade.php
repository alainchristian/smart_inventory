{{-- Recovery codes: shown once, right after setup. --}}
<div x-data="{ copied: false, copy() { navigator.clipboard?.writeText(@js(implode("\n", $codes))).then(() => this.copied = true) } }">
    <p class="form-caption">{{ __('Save your recovery codes') }}</p>
    <p style="margin:0 0 14px;font-size:13px;line-height:1.55;color:#4a5372;text-align:center">
        {{ __('If you lose your phone, each of these codes lets you sign in once. They are shown only now. Keep them somewhere safe and private, not in this browser.') }}
    </p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px">
        @foreach($codes as $code)
            <div style="padding:9px 10px;border:1px solid #e2e6f3;border-radius:9px;text-align:center;font-family:'DM Mono',monospace;font-size:14px;color:#1c2440;background:#fff">{{ $code }}</div>
        @endforeach
    </div>

    <div style="display:flex;gap:8px;margin-bottom:12px">
        <button type="button" @click="copy()"
                style="flex:1;padding:10px;border:1.5px solid #e2e6f3;border-radius:10px;background:#fff;cursor:pointer;font-size:13px;font-weight:600;color:#4a5372;font-family:inherit">
            <span x-show="!copied">{{ __('Copy codes') }}</span><span x-show="copied" x-cloak>{{ __('Copied') }}</span>
        </button>
        <button type="button" onclick="window.print()"
                style="flex:1;padding:10px;border:1.5px solid #e2e6f3;border-radius:10px;background:#fff;cursor:pointer;font-size:13px;font-weight:600;color:#4a5372;font-family:inherit">{{ __('Print') }}</button>
    </div>

    <form method="POST" action="{{ route('two-factor.codes.done') }}" x-data="{ saved: false }">
        @csrf
        <label style="display:flex;gap:8px;align-items:flex-start;margin-bottom:12px;font-size:13px;line-height:1.45;color:#4a5372;cursor:pointer">
            <input type="checkbox" x-model="saved" style="margin-top:3px">
            <span>{{ __('I saved these codes somewhere other than this phone, such as a password manager or on paper.') }}</span>
        </label>
        <button type="submit" class="btn-login" :disabled="!saved" :style="saved ? '' : 'opacity:.5;cursor:not-allowed'">{{ __('Continue') }}</button>
    </form>
</div>
