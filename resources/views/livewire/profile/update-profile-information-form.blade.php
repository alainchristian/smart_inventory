<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<div style="font-family:var(--font)">
<style>
.pin-card       { background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card);
                  height:100%;display:flex;flex-direction:column }
.pin-card-head  { padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px }
.pin-icon       { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.pin-title      { font-size:15px;font-weight:800;color:var(--text);margin:0 }
.pin-sub        { font-size:12px;color:var(--text-dim);margin-top:2px }
.pin-card-body  { padding:22px;flex:1;display:flex;flex-direction:column }
.pin-card-body form { flex:1;display:flex;flex-direction:column }
.pin-fields     { flex:1 }
.pin-field      { margin-bottom:18px }
.pin-field:last-of-type { margin-bottom:0 }
.pin-label      { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px }
.pin-input      { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;
                  font-size:14px;background:var(--surface);color:var(--text);outline:none;
                  box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.pin-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.pin-error      { font-size:11px;color:var(--red);margin-top:6px }
.pin-verify     { margin-top:10px;padding:12px 14px;background:var(--amber-dim);border-radius:9px;border:1px solid var(--amber) }
.pin-verify-txt { font-size:13px;color:var(--text-sub);margin:0 }
.pin-verify-btn { background:none;border:none;padding:0;color:var(--amber);font-weight:700;font-size:13px;
                 text-decoration:underline;cursor:pointer;font-family:var(--font) }
.pin-verify-sent { font-size:12px;font-weight:600;color:var(--green);margin-top:8px }
.pin-actions    { display:flex;align-items:center;gap:14px;margin-top:22px }
.pin-btn-primary { padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;
                  font-family:var(--font);transition:opacity var(--tr);display:inline-flex;align-items:center;
                  gap:8px;background:var(--accent);color:#fff;border:none;box-shadow:0 3px 10px rgba(59,111,212,.25) }
.pin-btn-primary:hover { opacity:.88 }
.pin-btn-primary:disabled { opacity:.6;cursor:not-allowed }
.pin-saved      { display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;
                  color:var(--green);background:var(--green-dim);padding:5px 10px;border-radius:20px }
@keyframes pin-spin { to { transform:rotate(360deg) } }
</style>

<div class="pin-card">
    <div class="pin-card-head">
        <div class="pin-icon" style="background:var(--accent-dim);color:var(--accent)">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <div>
            <h2 class="pin-title">{{ __('Profile Information') }}</h2>
            <p class="pin-sub">{{ __("Update your account's profile information and email address.") }}</p>
        </div>
    </div>

    <div class="pin-card-body">
        <form wire:submit="updateProfileInformation">
            <div class="pin-fields">
                <div class="pin-field">
                    <label for="name" class="pin-label">{{ __('Name') }}</label>
                    <input wire:model="name" id="name" name="name" type="text" class="pin-input" required autofocus autocomplete="name">
                    @error('name') <div class="pin-error">{{ $message }}</div> @enderror
                </div>

                <div class="pin-field">
                    <label for="email" class="pin-label">{{ __('Email') }}</label>
                    <input wire:model="email" id="email" name="email" type="email" class="pin-input" required autocomplete="username">
                    @error('email') <div class="pin-error">{{ $message }}</div> @enderror

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                        <div class="pin-verify">
                            <p class="pin-verify-txt">
                                {{ __('Your email address is unverified.') }}
                                <button wire:click.prevent="sendVerification" class="pin-verify-btn">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="pin-verify-sent">{{ __('A new verification link has been sent to your email address.') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="pin-actions">
                <button type="submit" class="pin-btn-primary" wire:loading.attr="disabled" wire:target="updateProfileInformation">
                    <span wire:loading.remove wire:target="updateProfileInformation">{{ __('Save') }}</span>
                    <span wire:loading wire:target="updateProfileInformation" style="display:none;align-items:center;gap:8px">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                             style="animation:pin-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                        {{ __('Saving…') }}
                    </span>
                </button>

                <x-action-message class="pin-saved" on="profile-updated">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </div>
</div>
</div>
